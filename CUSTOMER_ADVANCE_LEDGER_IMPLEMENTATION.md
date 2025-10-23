# Customer Advance Ledger Implementation

## Summary

This document outlines the implementation of customer advance ledger functionality, allowing the system to track and manage advance payments received from customers.

## Changes Made

### 1. Database Migration

**File**: `/db/migrations/add_customer_advance_ledger_to_settings.sql`

- Created migration file to add `customer_advance_ledger` column to `sma_settings` table
- Column type: INT(11) NULL DEFAULT NULL
- Column stores the ledger ID where customer advance amounts will be parked

**To apply migration**:

```sql
ALTER TABLE `sma_settings`
ADD COLUMN `customer_advance_ledger` INT(11) NULL DEFAULT NULL
COMMENT 'Customer Advance Ledger Account ID - used for advance payments from customers'
AFTER `supplier_advance_ledger`;
```

---

### 2. System Settings - Backend (Controller)

**File**: `/app/controllers/admin/System_settings.php`
**Method**: `add_ledgers()`

**Changes**:

- Added validation rule for `customer_advance_ledger` field (required, numeric)
- Added POST handling to capture `customer_advance_ledger` value
- Included `customer_advance_ledger` in the data array sent to model for saving

---

### 3. System Settings - Frontend (View)

**File**: `/themes/blue/admin/views/settings/add_ledgers.php`

**Changes**:

- Added new dropdown field for "Customer Advance Ledger" selection
- Field appears after "Supplier Advance Ledger" field
- Uses same ledger options ($LO) as other ledger dropdowns

---

### 4. Customer Payment Processing - Backend

**File**: `/app/controllers/admin/Customers.php`

#### A. Updated `payment_from_customer()` Method

**Key Changes**:

1. **Pure Advance Payment (No Invoices)**:

   - Validates that `customer_advance_ledger` is configured before processing
   - Uses customer advance ledger instead of regular ledger for pure advance payments
   - Creates journal entry with transaction type `customeradvance`
   - Shows success message: "Pure advance payment received Successfully!"

2. **Invoice Payment with Advance (Excess Payment)**:
   - Calculates if payment exceeds total due amount
   - Validates that `customer_advance_ledger` is configured if advance detected
   - Splits payment into:
     - **Invoice amount**: Allocated to specific invoices
     - **Advance amount**: Parked in customer advance ledger
   - Creates separate journal entries for each portion
   - Adds note to payment describing the split

**Split Logic**:

```php
$invoice_amount = array_sum($payments_array);
$advance_amount = $payment_total - $invoice_amount;
```

#### B. Added `get_customer_advance_balance()` Method

- AJAX endpoint to retrieve customer's current advance balance
- Returns JSON with:
  - `advance_balance`: Current advance amount
  - `advance_ledger_configured`: Whether ledger is set up
  - `customer_id`: ID of the customer
  - `advance_ledger`: Ledger ID

#### C. Added `getCustomerAdvanceBalance()` Private Method

- Queries `sma_accounts_entryitems` joined with `sma_accounts_entries`
- Filters by:
  - Ledger ID = customer_advance_ledger
  - Customer ID
  - Not deleted entries
- Calculates balance as: `Credit Total - Debit Total`
- For customer advance:
  - **Credit** = Advance received from customer
  - **Debit** = Advance utilized/settled

---

### 5. Customer Payment Form - Frontend

**File**: `/themes/blue/admin/views/customers/add_payment.php`

#### JavaScript Changes:

1. **Added `loadCustomerAdvanceBalance()` Function**:

   - Makes AJAX call to `customers/get_customer_advance_balance`
   - Displays advance balance in colored alert box:
     - **Green alert**: Shows available advance if > 0
     - **Blue alert**: Shows zero balance if configured
     - **No display**: If ledger not configured

2. **Updated Customer Selection Handler**:
   - Calls `loadCustomerAdvanceBalance()` when customer is selected
   - Also loads on page load if customer already selected

#### HTML Changes:

- Added `<div id="customer-advance-balance"></div>` below customer selection
- Displays dynamically updated advance balance information

---

## Accounting Entry Flow

### Scenario 1: Pure Advance Payment (No Invoices Selected)

**Example**: Customer pays 1000 with no invoices

| Account                    | Debit | Credit |
| -------------------------- | ----- | ------ |
| Payment Ledger (Bank/Cash) | 1000  | -      |
| Customer Advance Ledger    | -     | 1000   |

**Entry Type**: `customeradvance`

---

### Scenario 2: Invoice Payment Only (No Advance)

**Example**: Customer pays 800 for 800 worth of invoices

| Account                      | Debit | Credit |
| ---------------------------- | ----- | ------ |
| Payment Ledger (Bank/Cash)   | 800   | -      |
| Customer Accounts Receivable | -     | 800    |

**Entry Type**: `customerpayment`

---

### Scenario 3: Mixed Payment (Invoice + Advance)

**Example**: Customer pays 1200 for 800 worth of invoices

**Invoice Portion (800)**:
| Account | Debit | Credit |
|---------|-------|--------|
| Payment Ledger (Bank/Cash) | 800 | - |
| Customer Accounts Receivable | - | 800 |

**Advance Portion (400)**:
| Account | Debit | Credit |
|---------|-------|--------|
| Payment Ledger (Bank/Cash) | 400 | - |
| Customer Advance Ledger | - | 400 |

**Two journal entries created with references**:

- Main reference: `PMC-{reference_no}`
- Advance reference: `PMC-{reference_no}-ADV`

---

## User Interface Flow

1. **Configure Ledger** (One-time setup):

   - Navigate to: System Settings → Accounts Ledgers
   - Set "Customer Advance Ledger" field
   - Save settings

2. **Make Customer Payment**:

   - Navigate to: Customers → Add Payment
   - Select customer
   - **Advance balance displays automatically** (if any)
   - Enter payment amount
   - Select invoices to pay (or leave empty for pure advance)
   - If payment > invoice total: Advance is automatically detected and parked

3. **View Payment**:
   - Payment reference shows total amount
   - Individual payments show invoice allocations
   - Note indicates split if advance was included

---

## Validation & Error Handling

1. **Ledger Not Configured**:

   - Error: "Cannot process advance payment. Customer Advance Ledger is not configured in system settings."
   - Prevents advance payments until ledger is set up

2. **Payment Exceeds Due**:

   - Without ledger: Shows error message
   - With ledger: Automatically splits into invoice + advance

3. **Individual Invoice Validation**:
   - Each invoice payment cannot exceed its due amount
   - Total payment can exceed total due (becomes advance)

---

## Benefits

1. **Accurate Accounting**:

   - Separates regular payments from advance payments
   - Clear audit trail for advance amounts

2. **Financial Visibility**:

   - Real-time advance balance display
   - Easy tracking of customer pre-payments

3. **Flexible Payment Handling**:

   - Accept pure advance payments
   - Accept excess payments (auto-split)
   - Maintain proper ledger separation

4. **Future Extension Ready**:
   - Advance balance can be used for automatic application to future invoices
   - Settlement functionality can be added later

---

## Testing Checklist

- [ ] Run database migration
- [ ] Configure customer advance ledger in settings
- [ ] Test pure advance payment (no invoices)
- [ ] Test normal invoice payment (no advance)
- [ ] Test mixed payment (invoice + advance)
- [ ] Verify advance balance displays correctly
- [ ] Verify accounting entries are created properly
- [ ] Test with ledger not configured (should show error)
- [ ] Verify advance balance calculation is accurate

---

## Notes

- Pattern follows the existing supplier advance ledger implementation
- Customer advance balance is **view-only** on payment screen (no auto-apply)
- Future enhancement: Add functionality to apply existing advance to new invoices
- Advance balance query uses `customer_id` field in `sma_accounts_entries` table
