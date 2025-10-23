# Customer Advance Balance Display - Fix Applied

## Issue

The customer advance balance was not showing on the `customers/payment_from_customer` page.

## Root Cause

The controller was not passing the `customer_advance_ledger` configuration to the view, so the JavaScript didn't know whether the feature was enabled.

## Fix Applied

### 1. Controller Update (`Customers.php`)

**File**: `/app/controllers/admin/Customers.php`
**Method**: `payment_from_customer()`

Added the following code to pass the customer advance ledger setting to the view:

```php
} else {
    // Check if customer_advance_ledger is configured in settings
    $settings = $this->Settings;

    $customer_advance_ledger = isset($settings->customer_advance_ledger) && !empty($settings->customer_advance_ledger)
                             ? $settings->customer_advance_ledger
                             : null;

    $this->data['customers']  = $this->site->getAllCompanies('customer');
    $this->data['warehouses'] = $this->site->getAllWarehouses();
    $this->data['customer_advance_ledger'] = $customer_advance_ledger;  // ← NEW LINE
    $this->page_construct('customers/add_payment', $meta, $this->data);
}
```

### 2. View Update (`add_payment.php`)

**File**: `/themes/blue/admin/views/customers/add_payment.php`

Added JavaScript variable at the top of the script section:

```javascript
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    // Customer Advance Ledger configuration
    var customer_advance_ledger_configured = <?= $customer_advance_ledger ? 'true' : 'false' ?>;  // ← NEW LINE

    var payment_amount = 0;
```

## How It Works Now

1. **Controller** retrieves `customer_advance_ledger` from settings
2. **Controller** passes it to view via `$this->data['customer_advance_ledger']`
3. **View** converts PHP variable to JavaScript: `customer_advance_ledger_configured`
4. **JavaScript** function `loadCustomerAdvanceBalance()` makes AJAX call
5. **AJAX endpoint** (`get_customer_advance_balance`) returns balance
6. **Display** shows green alert with balance or blue alert with zero

## Test Steps

1. **Ensure database migration is applied**:

   ```sql
   ALTER TABLE `sma_settings`
   ADD COLUMN `customer_advance_ledger` INT(11) NULL DEFAULT NULL
   AFTER `supplier_advance_ledger`;
   ```

2. **Configure the ledger**:

   - Go to: System Settings → Accounts Ledgers
   - Set "Customer Advance Ledger" field to appropriate ledger
   - Click "Update Settings"

3. **Test the display**:

   - Navigate to: Customers → Add Payment
   - Select a customer
   - You should see one of these:
     - **Green alert**: "Available Advance: XXX.XX" (if balance > 0)
     - **Blue alert**: "Advance Balance: 0.00" (if balance = 0)
     - **Nothing**: If ledger not configured

4. **Test advance payment**:
   - Enter amount > 0 without selecting invoices
   - Submit → Should create pure advance payment
   - Go back and select same customer
   - Should now show the advance balance

## Files Modified

1. `/app/controllers/admin/Customers.php`
2. `/themes/blue/admin/views/customers/add_payment.php`

## Status

✅ **FIXED** - Customer advance balance should now display correctly on the payment form.
