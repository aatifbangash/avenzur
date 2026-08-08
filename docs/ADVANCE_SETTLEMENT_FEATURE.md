# Supplier Advance Settlement Feature

## Overview

This feature allows settling supplier invoices using available advance payments, with proper accounting entries and real-time calculation.

## How It Works

### 1. **Display Available Advance**

When you select a supplier in the payment screen, the system automatically:

- Fetches and displays the available advance balance for that supplier
- Shows it in a dedicated "Available Advance" field
- Updates in real-time when supplier changes

### 2. **Advance Adjustment Calculation**

The system calculates how much advance can be used based on:

```
Shortage Amount = Total Invoice Amount - Payment Amount Entered
Advance to Use = Minimum of (Available Advance, Shortage Amount)
```

**Example:**

- Total Invoice Amount: $1,000
- Payment Amount Entered: $500
- Shortage: $500
- Available Advance: $700
- **Advance Used**: $500 (minimum of shortage and available)
- **Total Settlement**: $500 (cash) + $500 (advance) = $1,000

### 3. **Visual Display**

The invoice table shows:

- List of pending invoices with due amounts
- **"Available Advance to Adjust"** row (blue background):
  - Shows total available advance
  - Shows calculated adjustable amount based on shortage
- Real-time updates when payment amount changes

### 4. **Settlement Process**

1. Select supplier → Advance balance loads automatically
2. Select invoices to pay → Total due calculated
3. Enter payment amount → System calculates shortage
4. Check "Settle with Advance" → System shows breakdown:
   - Cash Payment: [amount entered]
   - Advance Settlement: [calculated from shortage]
   - Total Settlement: [cash + advance]
5. Submit → Creates proper accounting entries

## Accounting Entries Created

### Scenario A: Settling Invoices with Advance

**When**: Payment amount < Total due, and "Settle with Advance" is checked

**Example**: Invoice $1,000, Payment $500, Advance Available $700

**Journal Entries Created**:

1. **Cash Payment Entry** (Reference: XXXX-CASH)

   - Debit: Supplier Ledger Account - $500
   - Credit: Bank/Payment Account - $500

2. **Advance Settlement Entry** (Reference: XXXX-ADV)
   - Debit: Supplier Advance Ledger - $500 (reduces advance balance)
   - Credit: Supplier Ledger Account - $500 (reduces liability)

**Payment Reference**: Records total $1,000 ($500 cash + $500 advance)

### Scenario B: Creating New Advance Payment

**When**: Payment amount > Total due

**Example**: Invoice $1,000, Payment $1,500

**Journal Entries Created**:

1. **Invoice Payment Entry** (Reference: XXXX)

   - Debit: Supplier Ledger Account - $1,000
   - Credit: Bank/Payment Account - $1,000

2. **Advance Payment Entry** (Reference: XXXX-ADV)
   - Debit: Supplier Advance Ledger - $500 (increases advance balance)
   - Credit: Bank/Payment Account - $500

**Payment Reference**: Two separate records for $1,000 and $500

### Scenario C: Regular Payment (No Advance)

**When**: "Settle with Advance" not checked

**Journal Entry**:

- Debit: Supplier Ledger Account
- Credit: Bank/Payment Account

## Configuration Required

### Supplier Advance Ledger Setup

1. Go to Settings → Accounting Settings
2. Configure "Supplier Advance Ledger" account
3. This ledger will track all advance payments to suppliers

**Important**: Without this configuration, the advance settlement feature will not work.

## Database Changes

### Query Logic

The system calculates advance balance using:

```sql
SELECT
    COALESCE(SUM(CASE WHEN dc = 'C' THEN amount ELSE 0 END), 0) as credits,
    COALESCE(SUM(CASE WHEN dc = 'D' THEN amount ELSE 0 END), 0) as debits
FROM sma_accounts_entries e
JOIN sma_accounts_entryitems ei ON e.id = ei.entry_id
WHERE (e.supplier_id = ? OR e.contact_id = ?)
  AND ei.ledger_id = ?
  AND e.deleted = 0
```

**Balance** = Credits (advances received) - Debits (advances used)

### Payment Reference Table

- Stores total settlement amount (cash + advance)
- Links to journal entries via `journal_id` field
- Reference number includes suffix (-CASH, -ADV) for split payments

### Invoice Payment Tracking

- Each invoice payment amount recorded in purchase table
- Links to payment reference for audit trail
- Sum of individual payments = total settlement amount

## User Interface Changes

### Add Payment Screen (`suppliers/add_payment.php`)

**New Fields**:

- "Available Advance" (read-only, auto-populated)
- "Settle with Advance" checkbox (when applicable)
- Payment breakdown display showing cash vs advance

**Invoice Table Enhancement**:

- New row: "Available Advance to Adjust"
  - Shows available advance balance
  - Shows calculated adjustable amount
  - Updates in real-time

**Real-time Calculations**:

- onChange events for payment amount field
- onChange events for individual invoice payments
- Automatic recalculation of shortage and advance usage

### Payment List Screen (`suppliers/list_payments.php`)

**Enhanced Display**:

- Shows payment composition with colored badges
- Parses settlement notes to extract cash and advance amounts
- Example: "Settlement: Cash: $500.00 | Advance: $500.00 | Total: $1,000.00"

## Error Handling

### Advance Balance Loading

- If AJAX call fails, displays "Error loading advance balance"
- Console logs include detailed error information
- Supports both `supplier_id` and `contact_id` database fields

### Payment Validation

- Checks if supplier advance ledger is configured
- Validates payment amounts before processing
- Shows user-friendly error messages

### Database Field Compatibility

- Tries `supplier_id` field first
- Falls back to `contact_id` field if needed
- Ensures compatibility with different database schemas

## Testing Checklist

- [ ] Select supplier → Advance balance displays correctly
- [ ] Enter payment < total due → Shortage calculated correctly
- [ ] Check "Settle with Advance" → Breakdown shows cash + advance
- [ ] Submit payment → Database entries created correctly
- [ ] Verify advance balance decreases after settlement
- [ ] Check payment list shows settlement details
- [ ] Test with payment > total due → New advance created
- [ ] Test with no available advance → Feature disabled
- [ ] Test with advance ledger not configured → Error message shown

## Files Modified

1. **Controller**: `/app/controllers/admin/Suppliers.php`

   - `get_supplier_advance_balance()` - AJAX endpoint
   - `getSupplierAdvanceBalance()` - Calculation method
   - `create_advance_settlement_entry()` - Accounting entry creation
   - `add_payment()` - Enhanced payment processing

2. **View**: `/themes/blue/admin/views/suppliers/add_payment.php`

   - `loadSupplierAdvanceBalance()` - Fetch advance via AJAX
   - `updateAdvanceSettlementCalculation()` - Calculate settlement amounts
   - `loadInvoices()` - Display invoices with advance row
   - Event handlers for real-time updates

3. **View**: `/themes/blue/admin/views/suppliers/list_payments.php`
   - Enhanced display with payment breakdown badges

## Technical Notes

### Double-Entry Bookkeeping

- All transactions maintain balanced debits and credits
- Advance ledger: Credits increase balance, Debits decrease balance
- Supplier ledger: Debits increase liability, Credits decrease liability

### Reference Number Format

- Regular payment: `REF-001`
- Cash portion of settlement: `REF-001-CASH`
- Advance settlement: `REF-001-ADV`
- New advance payment: `REF-001-ADV`

### Data Integrity

- Payment reference stores total settlement amount
- Individual invoice payments sum to total settlement
- Journal entries balance (total debits = total credits)
- Advance balance calculation excludes deleted entries

## Future Enhancements (Optional)

1. **Partial Advance Settlement**: Allow user to choose how much advance to use (currently uses maximum possible)
2. **Advance Settlement Report**: Show history of all advance settlements for a supplier
3. **Multi-Currency Support**: Handle advance in different currencies
4. **Advance Expiry**: Set expiration dates for advance payments
5. **Approval Workflow**: Require approval for large advance settlements
