# Customer Payment - Additional Discount & Credit Period Implementation

## Overview

Added two new fields to the customer payment invoice listing:

1. **Additional Discount Input** - Editable field to apply extra discount when collecting payment
2. **Credit Period Remaining** - Display field showing days until due date (red if overdue)

## Key Implementation Details

### Database Changes

**File:** `add_payment_additional_discount.sql`

Added `additional_discount` column to `sma_payments` table:

```sql
ALTER TABLE `sma_payments`
ADD COLUMN `additional_discount` DECIMAL(25,4) DEFAULT 0.0000
AFTER `amount`;
```

**Important:** Additional discount is stored in the **payments table**, NOT the sales table. This ensures sale records remain unchanged while tracking discounts given during payment collection.

### View Changes

**File:** `themes/blue/admin/views/customers/add_payment.php`

#### Table Structure Updated:

- Added 2 new columns between "Amt. Due." and "Payment"
- New column headers:
  - "Additional Discount"
  - "Credit Period"

#### JavaScript Enhancements:

1. **Credit Period Calculation:**

   - Compares invoice `due_date` with current date
   - Shows days remaining in normal color
   - Shows **"X days overdue"** in red/bold if past due
   - Shows **"Due today"** in orange/bold if due today
   - Shows "N/A" if no due date set

2. **Additional Discount Input:**

   - Number input field (step 0.01, min 0)
   - 100px width for compact display
   - Name: `additional_discount[]` (array indexed by invoice)
   - Default value: 0.00

3. **Table Layout Adjustments:**
   - Updated totals row colspan
   - Updated advance adjustment row colspan
   - Updated excess payment row colspan

### Controller Changes

**File:** `app/controllers/admin/Customers.php`

#### Modified `payment_from_customer()` method:

1. **Retrieve Additional Discount:**

```php
$additional_discount_array = $this->input->post('additional_discount');
```

2. **Updated `make_customer_payment()` signature:**

```php
public function make_customer_payment($id, $amount, $reference_no, $date, $note, $payment_id, $additional_discount = 0)
```

3. **Pass Additional Discount to Payments:**
   - For cash payments: extracts discount from array by index
   - For advance settlement: extracts discount from array by index
   - Defaults to 0 if not set

### Model Changes

**File:** `app/models/admin/Sales_model.php`

#### Updated `getPendingInvoicesByCustomer()`:

Added explicit SELECT to include `due_date`:

```php
$this->db->select('id, date, reference_no, customer_id, customer, grand_total, paid, payment_status, due_date');
```

This ensures `due_date` is available to JavaScript for credit period calculation.

## Data Flow

### Payment Processing Flow:

1. **User selects customer** → Loads pending invoices with due dates
2. **JavaScript calculates credit period** for each invoice
3. **User enters:**
   - Payment amount per invoice
   - Additional discount per invoice (optional)
4. **Form submits** with arrays:
   - `payment_amount[]`
   - `additional_discount[]`
   - `item_id[]`
5. **Controller processes** each invoice:
   - Retrieves additional discount from array by index
   - Creates payment record with discount value
6. **Database stores:**
   - Payment in `sma_payments` table
   - With `additional_discount` field populated

### Credit Period Display Logic:

```javascript
if (due_date) {
    days_diff = (due_date - today) in days

    if (days_diff < 0) → "X days overdue" (RED)
    else if (days_diff === 0) → "Due today" (ORANGE)
    else → "X days remaining" (NORMAL)
} else {
    → "N/A" (GRAY)
}
```

## Column Order in Payment Table

| #   | Column                  | Type        | Description               |
| --- | ----------------------- | ----------- | ------------------------- |
| 1   | Date                    | Display     | Invoice date              |
| 2   | Reference no            | Display     | Invoice reference         |
| 3   | Orig. Amt.              | Display     | Original invoice amount   |
| 4   | Amt. Due                | Display     | Current due amount        |
| 5   | **Additional Discount** | **Input**   | **New editable field**    |
| 6   | **Credit Period**       | **Display** | **New calculated field**  |
| 7   | Payment                 | Input       | Payment amount to collect |

## Key Design Decisions

### 1. Why Store in Payments Table?

- **Preserves sale integrity**: Original sale amounts unchanged
- **Audit trail**: Each payment records its own discount
- **Flexibility**: Different discounts for different payment installments
- **Accounting clarity**: Discount tied to payment transaction

### 2. Credit Period Calculation

- **Client-side calculation**: Reduces server load
- **Real-time display**: Immediate visual feedback
- **Color coding**: Red for overdue ensures high visibility
- **No data storage**: Calculated on-the-fly from existing `due_date`

### 3. Array-Based Submission

- Multiple invoices can be paid simultaneously
- Each invoice has its own discount value
- Arrays indexed consistently across all fields

## Testing Checklist

- [ ] Run SQL migration: `add_payment_additional_discount.sql`
- [ ] Access: http://localhost:8080/avenzur/admin/customers/payment_from_customer
- [ ] Select a customer with pending invoices
- [ ] Verify table shows:
  - [ ] Additional Discount input column
  - [ ] Credit Period display column
- [ ] Test credit period display:
  - [ ] Overdue invoices show red "X days overdue"
  - [ ] Due today shows orange "Due today"
  - [ ] Future dates show "X days remaining"
  - [ ] No due date shows "N/A"
- [ ] Enter additional discount values
- [ ] Submit payment
- [ ] Verify in `sma_payments` table:
  - [ ] `additional_discount` field populated correctly
  - [ ] Payment amount separate from discount

## SQL Migration Command

```bash
# Find correct database name first
docker exec -i avenzur_db mysql -uroot -ppass123 -e "SHOW DATABASES;"

# Run migration (replace 'retaj_aldawa' with your database name)
docker exec -i avenzur_db mysql -uroot -ppass123 retaj_aldawa < add_payment_additional_discount.sql
```

## Database Schema

### Before:

```sql
CREATE TABLE `sma_payments` (
  `id` int(11) NOT NULL,
  `date` timestamp NULL DEFAULT current_timestamp(),
  `sale_id` int(11) DEFAULT NULL,
  ...
  `amount` decimal(25,4) NOT NULL,
  `currency` varchar(3) DEFAULT NULL,
  ...
);
```

### After:

```sql
CREATE TABLE `sma_payments` (
  `id` int(11) NOT NULL,
  `date` timestamp NULL DEFAULT current_timestamp(),
  `sale_id` int(11) DEFAULT NULL,
  ...
  `amount` decimal(25,4) NOT NULL,
  `additional_discount` decimal(25,4) DEFAULT 0.0000,  -- NEW
  `currency` varchar(3) DEFAULT NULL,
  ...
);
```

## Files Modified

1. ✅ `add_payment_additional_discount.sql` - Database migration
2. ✅ `themes/blue/admin/views/customers/add_payment.php` - View with new columns
3. ✅ `app/controllers/admin/Customers.php` - Controller payment processing
4. ✅ `app/models/admin/Sales_model.php` - Model with due_date in query

## Usage Example

### Scenario:

Customer has 3 pending invoices:

- Invoice A: 1000 SAR due, 5 days overdue
- Invoice B: 500 SAR due, 10 days remaining
- Invoice C: 750 SAR due, no due date

### Display:

| Date       | Ref     | Orig | Due  | Add. Discount | Credit Period            | Payment |
| ---------- | ------- | ---- | ---- | ------------- | ------------------------ | ------- |
| 2025-11-13 | INV-001 | 1000 | 1000 | [input: 50]   | **5 days overdue** (red) | 950     |
| 2025-11-08 | INV-002 | 500  | 500  | [input: 0]    | 10 days remaining        | 500     |
| 2025-11-01 | INV-003 | 750  | 750  | [input: 25]   | N/A                      | 725     |

### Result:

- Payment amount entered: 2175 SAR
- Additional discounts: 75 SAR total (50 + 0 + 25)
- Database records:
  - Payment for INV-001: amount=950, additional_discount=50
  - Payment for INV-002: amount=500, additional_discount=0
  - Payment for INV-003: amount=725, additional_discount=25

## Benefits

1. **Clear visibility**: Staff see overdue status immediately
2. **Flexible discounts**: Can offer discounts at payment time
3. **Data integrity**: Original invoices unchanged
4. **Audit trail**: All discounts tracked in payment records
5. **Better cash flow**: Incentivize prompt payment with discounts
6. **Customer satisfaction**: Transparent about credit periods

## Future Enhancements

- [ ] Add permission control for discount entry
- [ ] Set maximum discount percentage limit
- [ ] Auto-calculate discount based on early payment
- [ ] Show discount impact on final amount
- [ ] Export report showing discounts given
- [ ] Track discount by user/date for analysis
- [ ] Add approval workflow for large discounts
