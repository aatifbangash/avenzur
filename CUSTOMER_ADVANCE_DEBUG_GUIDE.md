# Customer Advance Balance - Debugging Guide

## Current Issue

Customer advance balance is not displaying when selecting a customer on the payment form.

## Debugging Steps

### Step 1: Check Browser Console

1. Open the customer payment page: `admin/customers/payment_from_customer`
2. Open browser Developer Tools (F12)
3. Go to the Console tab
4. Select a customer
5. Look for these console messages:

**Expected Console Output:**

```
Customer Advance Ledger Configured: true (or false)
loadCustomerAdvanceBalance called with customer_id: [ID]
AJAX URL: http://yoursite.com/admin/customers/get_customer_advance_balance?customer_id=[ID]
AJAX Response: {advance_balance: 0, advance_ledger_configured: true, ...}
Advance Balance: 0
Ledger Configured: true
```

**If you see errors:**

- Note the exact error message
- Check if AJAX call is failing (Network tab)

### Step 2: Test AJAX Endpoint Directly

Open this URL in your browser (replace `[customer_id]` with actual ID):

```
http://yoursite.com/admin/customers/get_customer_advance_balance?customer_id=1
```

**Expected Response:**

```json
{
	"advance_balance": 0,
	"advance_ledger_configured": true,
	"customer_id": "1",
	"advance_ledger": "123"
}
```

**If you get an error:**

- Check if the route exists
- Verify customer ID is valid
- Check error logs

### Step 3: Verify Database Migration

Run this SQL query to check if column exists:

```sql
SHOW COLUMNS FROM sma_settings LIKE 'customer_advance_ledger';
```

**Expected Result:**
Should show one row with column name `customer_advance_ledger`

**If empty:**
Run the migration:

```sql
ALTER TABLE `sma_settings`
ADD COLUMN `customer_advance_ledger` INT(11) NULL DEFAULT NULL
COMMENT 'Customer Advance Ledger Account ID - used for advance payments from customers'
AFTER `supplier_advance_ledger`;
```

### Step 4: Verify Ledger Configuration

1. Go to: **System Settings → Accounts Ledgers**
2. Check if "Customer Advance Ledger" field has a value
3. If empty, select a ledger and save

**SQL Check:**

```sql
SELECT customer_advance_ledger FROM sma_settings WHERE setting_id = 1;
```

**Expected:**

- Should return a number (ledger ID)
- If NULL, configure it in the settings

### Step 5: Check if Customer Has Advance

To manually create a test advance payment:

```sql
-- Check current balance
SELECT
    COALESCE(SUM(CASE WHEN ei.dc = "C" THEN ei.amount ELSE 0 END), 0) as credit_total,
    COALESCE(SUM(CASE WHEN ei.dc = "D" THEN ei.amount ELSE 0 END), 0) as debit_total
FROM sma_accounts_entryitems ei
JOIN sma_accounts_entries e ON e.id = ei.entry_id
WHERE ei.ledger_id = [your_customer_advance_ledger_id]
  AND e.customer_id = [your_customer_id];
```

### Step 6: Verify HTML Element Exists

Check page source for:

```html
<div id="customer-advance-balance"></div>
```

**Location:** Should be right after the customer dropdown field

## Common Issues & Solutions

### Issue 1: "customer_advance_ledger is undefined"

**Solution:** Controller not passing variable to view

```php
// In Customers.php, payment_from_customer() method
$this->data['customer_advance_ledger'] = $customer_advance_ledger;
```

### Issue 2: AJAX call returns 404

**Solution:** Route not found

- Verify method exists in Customers.php
- Check if method is public (not private/protected)
- Clear application cache

### Issue 3: Balance is 0 but should have value

**Solution:** Check accounting entries

- Verify ledger_id in query matches configured ledger
- Check customer_id in sma_accounts_entries table
- Ensure transaction_type is 'customeradvance'

### Issue 4: No console output at all

**Solution:** JavaScript not loading

- Check page source for `loadCustomerAdvanceBalance` function
- Verify no JavaScript errors on page load
- Check if jQuery is loaded

### Issue 5: "advance_ledger_configured" is false

**Solution:** Ledger not configured

- Go to System Settings → Accounts Ledgers
- Set "Customer Advance Ledger" field
- Save settings

## Quick Test Procedure

1. **Create test advance payment:**

   - Go to: Customers → Add Payment
   - Select a customer (don't select any invoices)
   - Enter amount: 1000
   - Submit

2. **Verify payment created:**

   - Check if success message appears
   - Go to Customer Payments list
   - Verify payment record exists

3. **Check advance balance shows:**
   - Go to: Customers → Add Payment
   - Select the same customer
   - Should see: "Available Advance: 1000.00"

## Files to Check

1. **Controller:** `/app/controllers/admin/Customers.php`

   - Method: `payment_from_customer()` (line ~388)
   - Method: `get_customer_advance_balance()` (line ~1500)

2. **View:** `/themes/blue/admin/views/customers/add_payment.php`

   - Variable: `customer_advance_ledger_configured` (line ~4)
   - Function: `loadCustomerAdvanceBalance()` (line ~146)
   - HTML: `<div id="customer-advance-balance">` (line ~272)

3. **Settings:** Database table `sma_settings`
   - Column: `customer_advance_ledger`

## Next Steps Based on Console Output

Share the console output from Step 1, and I can provide specific fix.
