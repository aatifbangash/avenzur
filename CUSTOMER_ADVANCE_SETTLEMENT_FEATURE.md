# Customer Advance Settlement Feature

## Overview

This feature allows settling customer invoices using available advance payments, with proper accounting entries and real-time calculation. It mirrors the supplier advance settlement functionality.

## How It Works

### 1. **Display Available Advance**

When you select a customer in the payment screen, the system automatically:

- Fetches and displays the available advance balance for that customer
- Shows it in a dedicated "Available Advance" field
- Updates in real-time when customer changes

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

1. Select customer → Advance balance loads automatically
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

   - Debit: Bank/Payment Account - $500
   - Credit: Customer Ledger Account - $500

2. **Advance Settlement Entry** (Reference: XXXX-ADV)
   - Debit: Customer Advance Ledger - $500 (reduces advance balance)
   - Credit: Customer Ledger Account - $500 (reduces receivable)

**Payment Reference**: Records total $1,000 ($500 cash + $500 advance)

### Scenario B: Creating New Advance Payment

**When**: Payment amount > Total due

**Example**: Invoice $1,000, Payment $1,500

**Journal Entries Created**:

1. **Invoice Payment Entry** (Reference: XXXX)

   - Debit: Bank/Payment Account - $1,000
   - Credit: Customer Ledger Account - $1,000

2. **Advance Payment Entry** (Reference: XXXX-ADV)
   - Debit: Bank/Payment Account - $500
   - Credit: Customer Advance Ledger - $500 (increases advance balance)

**Payment Reference**: Two separate records for $1,000 and $500

### Scenario C: Regular Payment (No Advance)

**When**: "Settle with Advance" not checked

**Journal Entry**:

- Debit: Bank/Payment Account
- Credit: Customer Ledger Account

## Configuration Required

### Customer Advance Ledger Setup

1. Go to Settings → Accounting Settings
2. Configure "Customer Advance Ledger" account
3. This ledger will track all advance payments from customers

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
WHERE e.customer_id = ?
  AND ei.ledger_id = ?
  AND (e.deleted = 0 OR e.deleted IS NULL)
```

**Balance** = Credits (advances received) - Debits (advances used)

### Payment Reference Table

- Stores total settlement amount (cash + advance)
- Links to journal entries via `journal_id` field
- Reference number includes suffix (-CASH, -ADV) for split payments

### Invoice Payment Tracking

- Each invoice payment amount recorded in sales table
- Links to payment reference for audit trail
- Sum of individual payments = total settlement amount

## User Interface Changes

### Add Payment Screen (`customers/add_payment.php`)

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

## Error Handling

### Advance Balance Loading

- If AJAX call fails, displays "Error loading advance balance"
- Console logs include detailed error information
- Handles both positive and negative balances (customer owes vs has advance)

### Payment Validation

- Checks if customer advance ledger is configured
- Validates payment amounts before processing
- Shows user-friendly error messages

## Testing Scenarios

### Scenario 1: Full Settlement with Advance

```
Invoice: 1000
Cash Paid: 500
Available Advance: 500
Settle with Advance: ✓

Expected Result:
- Advance Used: 500
- Invoice Fully Paid: 1000 (500 cash + 500 advance)
- Invoice Due: 0
- Remaining Advance: 0
```

### Scenario 2: Partial Settlement with Advance

```
Invoice: 1000
Cash Paid: 700
Available Advance: 500
Settle with Advance: ✓

Expected Result:
- Advance Used: 300 (only shortage amount)
- Invoice Fully Paid: 1000 (700 cash + 300 advance)
- Invoice Due: 0
- Remaining Advance: 200
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

## Files Modified

1. **Controller**: `/app/controllers/admin/Customers.php`

   - `get_customer_advance_balance()` - AJAX endpoint
   - `getCustomerAdvanceBalance()` - Calculation method
   - `create_customer_advance_settlement_entry()` - Accounting entry creation
   - `payment_from_customer()` - Enhanced payment processing
   - `convert_customer_payment_advance()` - Pure advance payment accounting

2. **View**: `/themes/blue/admin/views/customers/add_payment.php`
   - `loadCustomerAdvanceBalance()` - Fetch advance via AJAX
   - `updateAdvanceSettlementCalculation()` - Calculate settlement amounts
   - `loadInvoices()` - Display invoices with advance row
   - Event handlers for real-time updates

## Technical Notes

### Double-Entry Bookkeeping

- All transactions maintain balanced debits and credits
- Customer advance ledger: Credits increase balance, Debits decrease balance
- Customer ledger: Debits increase receivable, Credits decrease receivable

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

## Benefits

1. **Accurate Invoice Status**: Invoices show correct paid/due amounts
2. **Proper Advance Tracking**: Advance balance decreases correctly
3. **Complete Audit Trail**: Separate payment records for cash and advance
4. **Correct Accounting**: Journal entries match actual payment distribution
5. **Transparent Reporting**: Users can see exactly how payment was split
6. **Negative Balance Handling**: Properly displays when customer owes money

## Difference from Supplier Implementation

The customer advance settlement is the mirror opposite of supplier:

**Supplier Advance** (We paid supplier in advance):

- Advance is an **asset** (we're owed goods/services)
- Credit increases advance balance
- Debit decreases advance balance

**Customer Advance** (Customer paid us in advance):

- Advance is a **liability** (we owe goods/services)
- Credit increases advance balance
- Debit decreases advance balance when used

## Bug Fixes Included

### Fixed: Negative Balance Display

**Problem**: When a pure advance payment was recorded with the accounting entry direction swapped, the balance showed as negative (-2000 instead of +2000).

**Solution**: Created separate `convert_customer_payment_advance()` method with correct accounting entries:

- Debit: Bank/Payment Account (asset increases)
- Credit: Customer Advance Ledger (liability increases)

### Fixed: Advance Balance Calculation

**Problem**: Balance query needed to handle customer_id field properly.

**Solution**: Updated `getCustomerAdvanceBalance()` to:

- Use proper JOIN conditions
- Handle deleted entries
- Calculate as `Credit - Debit` for correct liability tracking
