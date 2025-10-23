# Excess Payment as Advance Feature

## Overview

When a customer payment exceeds the total invoice amount, the excess amount is automatically parked as customer advance. This advance can later be used to settle future invoices.

## How It Works

### 1. **UI Display**

- When you enter a payment amount greater than the total invoice amount, a new row appears in the payment table
- This row shows: **"Excess Payment (Will be parked as Advance): XXX.XX"**
- The row has a yellow background (`#fff3cd`) to make it visually distinct
- The excess amount updates dynamically as you change the payment amount

### 2. **Calculation Logic**

```
Excess Amount = Payment Amount - Total Invoice Due Amount

Example:
- Total Invoices Due: 1000
- Payment Entered: 1500
- Excess (Advance): 500
```

### 3. **Payment Processing**

When the payment is submitted:

#### A. Invoice Payment Distribution

The system first distributes the payment to cover the invoices:

- Each invoice receives up to its due amount
- Payments are distributed in the order invoices appear

#### B. Advance Payment Recording

The excess amount is:

1. Created as a separate payment reference with suffix `-ADV`
2. Recorded in the payment history with note "(Advance)"
3. Posted to accounting with proper journal entries:
   - **Debit**: Bank/Payment Account (asset increases)
   - **Credit**: Customer Advance Ledger (liability increases)

### 4. **Accounting Entries**

For a payment of 1500 against invoices totaling 1000:

**Entry 1: Invoice Payment (1000)**

```
Date: [Payment Date]
Reference: [REF-NO]-CASH
Type: customerpayment

Debit:  Bank Account         1000
Credit: Customer Ledger      1000
```

**Entry 2: Advance Payment (500)**

```
Date: [Payment Date]
Reference: [REF-NO]-ADV
Type: customeradvance

Debit:  Bank Account                1000
Credit: Customer Advance Ledger     500
```

### 5. **Example Scenarios**

#### Scenario 1: Simple Excess Payment

- Customer: ABC Company
- Invoice #001: Due Amount = 800
- Invoice #002: Due Amount = 200
- Total Due: 1000
- Payment Entered: 1500

**Result:**

- Invoice #001 paid: 800 (fully settled)
- Invoice #002 paid: 200 (fully settled)
- Advance parked: 500 (available for future invoices)

#### Scenario 2: Partial Invoice Payment + Excess

- Invoice #001: Due Amount = 500
- Invoice #002: Due Amount = 300
- Total Due: 800
- Payment Entered: 1000
- Payment Distribution:
  - Invoice #001: 500 (user enters this)
  - Invoice #002: 200 (user enters this)
  - Total Invoice Payment: 700

**Result:**

- Invoice #001 paid: 500 (fully settled)
- Invoice #002 paid: 200 (partially settled, 100 still due)
- Advance parked: 300 (1000 - 700)

#### Scenario 3: Pure Advance Payment

- No invoices selected (or all payment fields = 0)
- Payment Entered: 2000

**Result:**

- No invoice payments
- Advance parked: 2000 (full amount goes to advance)

### 6. **Requirements**

For excess payment to be parked as advance:

1. **Customer Advance Ledger must be configured** in System Settings
2. Payment amount must exceed total invoice amount
3. If ledger is not configured, system shows error: "Payment amount exceeds total due amount. Please configure Customer Advance Ledger in settings to allow advance payments."

### 7. **UI Features**

#### Real-time Updates

- Excess amount updates immediately when payment amount changes
- Row appears/disappears based on whether there's excess
- Color coding:
  - Yellow background for excess advance row
  - Blue background for advance settlement row
  - White background for regular invoice rows

#### Visual Indicators

The payment table shows:

```
+----------+---------------+-----------+-----------+----------+
| Date     | Reference     | Orig Amt  | Amt Due   | Payment  |
+----------+---------------+-----------+-----------+----------+
| 01/01/24 | INV-001       | 1000.00   | 1000.00   | 1000.00  |
| 01/02/24 | INV-002       | 500.00    | 500.00    | 500.00   |
+----------+---------------+-----------+-----------+----------+
| Totals:                   | 1500.00   | 1500.00   | 1500.00  |
+----------+---------------+-----------+-----------+----------+
| Excess Payment (Will be parked as Advance):         | 500.00   |
+----------+---------------+-----------+-----------+----------+
```

### 8. **Integration with Advance Settlement**

The parked advance can later be used:

1. Navigate to customer payment screen
2. Select the customer
3. Available advance balance displays
4. Check "Settle with Advance" in the invoice table
5. System automatically uses advance to cover invoice shortage

### 9. **Validation**

System validates:

- Payment amount cannot exceed individual invoice due amount (when manually entering per invoice)
- Total invoice payments cannot exceed payment amount entered
- Customer advance ledger must be configured for excess payments
- All amounts must be positive numbers

### 10. **Payment Reference Tracking**

Each payment creates a reference record:

- Main payment reference: For invoice payments
- Advance payment reference: For excess amount (with `-ADV` suffix)
- Both linked to their respective journal entries
- Visible in customer payment history

## Benefits

1. **Flexibility**: Accept any payment amount from customers
2. **Automatic Handling**: System automatically parks excess as advance
3. **Clear Visibility**: Visual indicators show exactly what happens
4. **Future Use**: Parked advance ready for future invoice settlement
5. **Proper Accounting**: Correct double-entry bookkeeping maintained
6. **Audit Trail**: Complete tracking of advance creation and usage

## Technical Implementation

### Files Modified

1. `/themes/blue/admin/views/customers/add_payment.php`
   - Added excess advance row display
   - Added `updateExcessAdvanceRow()` function
   - Added form submission handler for checkbox state

### Backend Logic (Already Implemented)

The backend in `/app/controllers/admin/Customers.php` already handles:

- Calculation of excess amount: `$advance_payment = $cash_payment - $total_invoice_payment`
- Validation of customer advance ledger configuration
- Creation of separate payment reference for advance
- Journal entry creation via `convert_customer_payment_advance()`

### JavaScript Functions

- `updateExcessAdvanceRow()`: Updates/removes excess advance display
- `updateAdvanceSettlementCalculation()`: Recalculates when payment changes
- Form submit handler: Passes checkbox state to backend

## Testing Checklist

- [ ] Enter payment > total due → excess row appears
- [ ] Change payment to < total due → excess row disappears
- [ ] Submit payment with excess → advance created correctly
- [ ] Check customer advance balance → shows correct amount
- [ ] Use parked advance for future invoice → settles correctly
- [ ] Verify journal entries → correct debit/credit entries
- [ ] Test without advance ledger configured → proper error message
