# Advance Settlement Distribution Fix

## Problem Statement

When settling supplier invoices with advance payment, the advance amount was not being properly distributed to the invoices. The accounting entries were created, but the invoices remained unpaid.

### Example of the Issue

**Scenario:**

- Invoice Amount: 112
- Cash Payment Entered: 88
- Shortage: 24
- Available Advance: 500
- User checks "Settle with Advance"

**What Was Happening (WRONG):**

- Invoice paid amount: 88 only
- Invoice due remaining: 24
- Advance settlement journal entry created for 24
- But the 24 was NOT actually applied to the invoice
- Invoice shows as partially paid instead of fully paid

**What Should Happen (CORRECT):**

- Invoice paid amount: 112 (88 cash + 24 advance)
- Invoice due remaining: 0
- Advance balance decreases by 24
- Proper accounting entries created

## Root Cause

The original code only applied the cash payment amounts from `$payments_array` to invoices:

```php
for ($i = 0; $i < count($payments_array); $i++) {
    $payment_amount = $payments_array[$i];  // Only cash amount
    $this->update_purchase_order($item_id, $payment_amount);  // Missing advance portion!
}
```

The `$advance_settlement_amount` was calculated and used for journal entries, but was **never distributed to the actual invoices**.

## Solution Implemented

### 1. Distribute Advance to Invoices

The fix distributes the advance settlement amount to invoices that have shortages:

```php
$remaining_advance = $advance_settlement_amount;

for ($i = 0; $i < count($payments_array); $i++) {
    $cash_payment_for_invoice = $payments_array[$i];
    $invoice_shortage = $due_amount - $cash_payment_for_invoice;

    // Use advance to cover shortage
    $advance_for_this_invoice = 0;
    if ($remaining_advance > 0 && $invoice_shortage > 0) {
        $advance_for_this_invoice = min($remaining_advance, $invoice_shortage);
        $remaining_advance -= $advance_for_this_invoice;
    }

    // Total payment = cash + advance
    $total_payment_for_invoice = $cash_payment_for_invoice + $advance_for_this_invoice;

    // Update invoice with FULL payment amount
    $this->update_purchase_order($item_id, $total_payment_for_invoice);
}
```

### 2. Record Separate Payment Entries

For audit trail and transparency, we now create separate payment records:

```php
// Record cash payment
if ($cash_payment_for_invoice > 0) {
    $this->make_supplier_payment($item_id, $cash_payment_for_invoice,
        $reference_no, $date, $note . ' (Cash)', $combined_payment_id);
}

// Record advance settlement payment
if ($advance_for_this_invoice > 0) {
    $this->make_supplier_payment($item_id, $advance_for_this_invoice,
        $reference_no . '-ADV', $date, $note . ' (Advance Settlement)', $combined_payment_id);
}
```

### 3. Distribution Logic

**Sequential Distribution:**
Advance is distributed to invoices in the order they appear, covering shortages until the advance is exhausted.

**Example with Multiple Invoices:**

| Invoice | Due Amount | Cash Paid | Shortage | Advance Used | Total Paid | Remaining Due |
| ------- | ---------- | --------- | -------- | ------------ | ---------- | ------------- |
| INV-001 | 112        | 88        | 24       | 24           | 112        | 0             |
| INV-002 | 200        | 150       | 50       | 50           | 200        | 0             |
| INV-003 | 150        | 100       | 50       | 26           | 126        | 24            |

- Total Shortage: 124
- Available Advance: 100
- Advance Fully Used: 100 (24 + 50 + 26)
- Remaining Advance: 0

## Accounting Flow

### Complete Settlement Process

**Step 1: User Input**

- Selects invoices
- Enters cash payment amounts
- Checks "Settle with Advance"

**Step 2: Calculation**

```
Total Due = Sum of invoice due amounts
Total Cash = User entered payment amount
Shortage = Total Due - Total Cash
Advance to Use = min(Available Advance, Shortage)
```

**Step 3: Apply Payments to Invoices**

- Loop through each invoice
- Calculate shortage for each invoice
- Allocate advance to cover shortage
- Update invoice with (cash + advance)
- Create payment records for both portions

**Step 4: Create Journal Entries**

**Cash Payment Entry (REF-CASH):**

```
Debit: Supplier Account (reduces liability)
Credit: Bank Account (cash out)
```

**Advance Settlement Entry (REF-ADV):**

```
Debit: Supplier Advance Ledger (reduces advance balance)
Credit: Supplier Account (reduces liability)
```

**Step 5: Update Payment Reference**

- Stores total settlement amount (cash + advance)
- Links to journal entries
- Links to individual invoice payments

## Database Updates

### purchases Table

Each invoice's `paid` amount is updated with the **total payment** (cash + advance):

```sql
UPDATE purchases
SET paid = paid + (cash_payment + advance_payment)
WHERE id = invoice_id
```

### payments Table

Two records created per invoice (if both cash and advance used):

**Record 1 - Cash:**

- `purchase_id`: Invoice ID
- `amount`: Cash payment amount
- `reference_no`: REF-001
- `note`: "Payment note (Cash)"
- `payment_id`: Links to payment_reference

**Record 2 - Advance:**

- `purchase_id`: Invoice ID
- `amount`: Advance settlement amount
- `reference_no`: REF-001-ADV
- `note`: "Payment note (Advance Settlement)"
- `payment_id`: Links to payment_reference

### sma_payment_reference Table

- `amount`: Total settlement (cash + advance)
- `reference_no`: REF-001
- `journal_id`: Links to journal entry

### sma_accounts_entries & sma_accounts_entryitems Tables

Two journal entries created:

**Entry 1 (REF-001-CASH):**

- Transaction type: 'supplierpayment'
- Dr/Cr Total: Cash amount + bank charges

**Entry 2 (REF-001-ADV):**

- Transaction type: 'advancesettlement'
- Dr/Cr Total: Advance settlement amount

## Testing Scenarios

### Scenario 1: Single Invoice, Partial Payment

```
Invoice: 1000
Cash Paid: 600
Available Advance: 500
Settle with Advance: ✓

Expected Result:
- Advance Used: 400 (covers shortage)
- Invoice Paid: 1000 (600 + 400)
- Invoice Due: 0
- Remaining Advance: 100
```

### Scenario 2: Multiple Invoices

```
Invoice 1: 500, Cash: 300
Invoice 2: 800, Cash: 500
Available Advance: 600
Settle with Advance: ✓

Expected Result:
- Invoice 1: Paid 500 (300 cash + 200 advance), Due: 0
- Invoice 2: Paid 900 (500 cash + 400 advance), Due: -100
- Total Advance Used: 600
- Remaining Advance: 0
```

### Scenario 3: Advance Insufficient

```
Invoice: 1000
Cash Paid: 400
Available Advance: 200
Settle with Advance: ✓

Expected Result:
- Invoice Paid: 600 (400 cash + 200 advance)
- Invoice Due: 400
- Remaining Advance: 0
- Partial payment recorded
```

### Scenario 4: No Advance Needed

```
Invoice: 1000
Cash Paid: 1000
Available Advance: 500
Settle with Advance: ✓

Expected Result:
- Advance Used: 0 (no shortage)
- Invoice Paid: 1000 (all cash)
- Invoice Due: 0
- Remaining Advance: 500 (unchanged)
```

## Benefits

1. **Accurate Invoice Status**: Invoices show correct paid/due amounts
2. **Proper Advance Tracking**: Advance balance decreases correctly
3. **Complete Audit Trail**: Separate payment records for cash and advance
4. **Correct Accounting**: Journal entries match actual payment distribution
5. **Transparent Reporting**: Users can see exactly how payment was split

## Files Modified

**Controller**: `/app/controllers/admin/Suppliers.php`

- Modified `add_payment()` method
- Enhanced invoice payment loop (lines 920-955)
- Added advance distribution logic
- Created separate payment records for cash and advance portions

## Verification Checklist

- [ ] Invoice paid amount = cash + advance
- [ ] Invoice due = 0 when fully paid with advance
- [ ] Advance balance decreases by amount used
- [ ] Two payment records created (cash + advance)
- [ ] Journal entries match payment distribution
- [ ] Payment reference shows total settlement amount
- [ ] Multi-invoice scenarios distribute advance correctly
- [ ] Insufficient advance scenario handles gracefully
- [ ] Payment list shows breakdown (cash vs advance)

## Migration Notes

**Existing Data**: No migration needed for existing payments. This fix only affects new payments going forward.

**Backward Compatibility**: The fix maintains compatibility with:

- Regular payments (no advance)
- Pure advance payments (no invoices)
- Mixed scenarios (some invoices + new advance)
