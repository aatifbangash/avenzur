# Stored Procedure: sp_get_accounts_dashboard - Troubleshooting Guide

## Overview

The `sp_get_accounts_dashboard` stored procedure returns 7 result sets with comprehensive financial dashboard data. It's called from the Accounts Dashboard controller.

## Procedure Details

### Location

```sql
Database: rawabi_jeddah (or your configured database)
Name: sp_get_accounts_dashboard
Type: Stored Procedure
```

### Parameters

```sql
IN p_report_type VARCHAR(20)  -- Valid values: 'ytd', 'monthly', 'today'
IN p_reference_date DATE      -- Format: YYYY-MM-DD
```

### Return Value

Returns 7 consecutive result sets:

| #   | Name               | Description                  | Key Columns                                                                   |
| --- | ------------------ | ---------------------------- | ----------------------------------------------------------------------------- |
| 1   | sales_summary      | Sales data grouped by period | period, total_gross_sales, total_net_sales, sale_count                        |
| 2   | collection_summary | Collections by period        | period, total_collection, cash_collection, card_collection, cheque_collection |
| 3   | purchase_summary   | Purchases by period          | period, total_purchase, net_purchase, purchase_count                          |
| 4   | purchase_per_item  | Top 5 purchase items         | product_id, product_code, total_quantity, total_amount, avg_unit_cost         |
| 5   | expiry_report      | Expiring products            | product_name, expiry_date, days_to_expiry, expiring_quantity, potential_loss  |
| 6   | customer_summary   | Customer activity            | period, unique_customers, total_transactions, total_sales                     |
| 7   | overall_summary    | KPI totals (1 row)           | total_gross_sales, total_collection, total_purchase, unique_customers         |

## Installation

### Step 1: Create Migration Directory

```bash
mkdir -p /path/to/app/migrations/accounts_dashboard
```

### Step 2: Copy SQL File

The migration file is located at:

```
app/migrations/accounts_dashboard/001_create_sp_get_accounts_dashboard.sql
```

### Step 3: Execute SQL

Run the SQL migration file in your database:

```sql
-- Option 1: MySQL CLI
mysql -u username -p database_name < app/migrations/accounts_dashboard/001_create_sp_get_accounts_dashboard.sql

-- Option 2: PhpMyAdmin
1. Go to SQL tab
2. Paste the contents of the SQL file
3. Click Execute

-- Option 3: CodeIgniter CLI (if available)
php index.php migrate
```

### Step 4: Verify Installation

```sql
-- Check if procedure exists
SHOW PROCEDURE STATUS WHERE Name = 'sp_get_accounts_dashboard';

-- View procedure code
SHOW CREATE PROCEDURE sp_get_accounts_dashboard;
```

## Table Requirements

The stored procedure requires these tables to exist:

### Required Tables

1. **sma_sales** - Sales transactions

   - Columns: id, date, grand_total, total_net, sale_status, customer_id

2. **sma_payments** - Payment records

   - Columns: id, date, amount, paid_by

3. **sma_purchases** - Purchase transactions

   - Columns: id, date, grand_total, total_net_purchase, status

4. **sma_purchase_items** - Purchase line items

   - Columns: product_id, product_code, product_name, quantity, subtotal, net_unit_cost, purchase_id, expiry, quantity_balance, warehouse_id

5. **sma_products** - Product master data
   - Columns: id, name

## Common Issues & Solutions

### Issue 1: "Unknown procedure 'sp_get_accounts_dashboard'"

**Cause**: Stored procedure not created or wrong database selected

**Solution**:

```sql
-- Verify procedure exists
USE your_database_name;
SHOW PROCEDURE STATUS WHERE Name = 'sp_get_accounts_dashboard';

-- If not found, run the migration:
SOURCE app/migrations/accounts_dashboard/001_create_sp_get_accounts_dashboard.sql;
```

### Issue 2: "Unknown column 's.total_net' in 'field list'"

**Cause**: Column `total_net` doesn't exist in `sma_sales` table

**Solution**:

```sql
-- Check sales table columns
DESCRIBE sma_sales;

-- If column missing, check the actual column name:
-- It might be: total_discount, net_total, or similar
-- Update the SP accordingly

-- Alternative: Create the column if needed
ALTER TABLE sma_sales ADD COLUMN total_net DECIMAL(25,4) DEFAULT 0;
```

### Issue 3: "Unknown column 'total_net_purchase' in 'field list'"

**Cause**: Column doesn't exist in `sma_purchases` table

**Solution**:

```sql
-- Check purchases table columns
DESCRIBE sma_purchases;

-- Add column if needed
ALTER TABLE sma_purchases ADD COLUMN total_net_purchase DECIMAL(25,4) DEFAULT 0;
```

### Issue 4: "Out of range value for column"

**Cause**: Data values exceed DECIMAL(25,4) range

**Solution**:

```sql
-- Increase decimal precision:
-- Modify your procedure to use DECIMAL(30,4) instead of DECIMAL(25,4)
-- Or clean data to remove extreme values
```

### Issue 5: "Procedure executes but returns no data"

**Cause**: Date ranges don't match any data

**Solution**:

```sql
-- Debug date calculations:
SET @ref_date = '2025-10-30';
SET @year_start = DATE_FORMAT(@ref_date, '%Y-01-01');
SET @month_start = DATE_FORMAT(@ref_date, '%Y-%m-01');

SELECT @year_start, @month_start, @ref_date;

-- Check if data exists in tables:
SELECT COUNT(*) FROM sma_sales WHERE DATE(date) >= @year_start;
SELECT COUNT(*) FROM sma_purchases WHERE DATE(date) >= @year_start;
```

## Column Mapping Verification

If you're getting column errors, verify your actual table columns:

```sql
-- Sales table
SELECT * FROM sma_sales LIMIT 1;
-- Look for: id, date, grand_total, total_net, sale_status, customer_id

-- Purchases table
SELECT * FROM sma_purchases LIMIT 1;
-- Look for: id, date, grand_total, total_net_purchase, status

-- Purchase items table
SELECT * FROM sma_purchase_items LIMIT 1;
-- Look for: product_id, product_code, product_name, quantity, subtotal, net_unit_cost, expiry, quantity_balance
```

## Performance Optimization

### Add Indexes for Better Performance

```sql
-- Sales table indexes
CREATE INDEX idx_sales_date ON sma_sales(date);
CREATE INDEX idx_sales_status ON sma_sales(sale_status);
CREATE INDEX idx_sales_customer ON sma_sales(customer_id);

-- Purchases table indexes
CREATE INDEX idx_purchases_date ON sma_purchases(date);
CREATE INDEX idx_purchases_status ON sma_purchases(status);

-- Purchase items indexes
CREATE INDEX idx_purchase_items_expiry ON sma_purchase_items(expiry);
CREATE INDEX idx_purchase_items_product ON sma_purchase_items(product_id);

-- Payments table indexes
CREATE INDEX idx_payments_date ON sma_payments(date);
```

## Testing the Procedure

### Test Case 1: Year to Date

```sql
CALL sp_get_accounts_dashboard('ytd', '2025-10-30');
-- Should return data from 2025-01-01 to 2025-10-30
```

### Test Case 2: Monthly

```sql
CALL sp_get_accounts_dashboard('monthly', '2025-10-30');
-- Should return data from 2025-10-01 to 2025-10-31
```

### Test Case 3: Daily

```sql
CALL sp_get_accounts_dashboard('today', '2025-10-30');
-- Should return data only for 2025-10-30
```

### Test Individual Result Sets

```sql
-- If procedure has errors, test individual SELECT statements:

-- Test 1: Sales
SELECT SUM(grand_total) FROM sma_sales
WHERE DATE(date) BETWEEN '2025-01-01' AND '2025-10-30'
AND sale_status != 'returned';

-- Test 2: Collections
SELECT SUM(amount) FROM sma_payments
WHERE DATE(date) BETWEEN '2025-01-01' AND '2025-10-30';

-- Test 3: Purchases
SELECT SUM(grand_total) FROM sma_purchases
WHERE DATE(date) BETWEEN '2025-01-01' AND '2025-10-30'
AND status != 'returned';
```

## Modifying the Procedure

### If Column Names Are Different

Edit the stored procedure to match your actual column names:

```sql
-- Example: If your sales table has 'net_amount' instead of 'total_net':
DELIMITER $$
CREATE PROCEDURE sp_get_accounts_dashboard(...) BEGIN
    -- Change this line:
    -- SUM(s.total_net) AS net_sales
    -- To this:
    -- SUM(s.net_amount) AS net_sales
END$$
DELIMITER ;
```

### If Tables Have Different Names

Update all table references:

```sql
-- Replace sma_sales with your actual table name
-- Replace sma_purchases with your actual table name
-- Replace sma_payments with your actual table name
-- Replace sma_purchase_items with your actual table name
-- Replace sma_products with your actual table name
```

## Backup & Recovery

### Backup Procedure

```sql
-- Export procedure definition
SHOW CREATE PROCEDURE sp_get_accounts_dashboard;
-- Copy the output to a safe location
```

### Restore Procedure

```sql
-- If procedure is deleted or corrupted:
-- Re-run the migration file
SOURCE app/migrations/accounts_dashboard/001_create_sp_get_accounts_dashboard.sql;
```

## Logs & Debugging

### Enable Query Logging (MySQL 5.7+)

```sql
-- Enable general query log
SET GLOBAL general_log = 'ON';

-- Set log file location
SET GLOBAL log_output = 'FILE';
SET GLOBAL general_log_file='/var/log/mysql/general.log';

-- View log
TAIL -f /var/log/mysql/general.log

-- Disable when done
SET GLOBAL general_log = 'OFF';
```

### Check Application Logs

Location: `/app/logs/log-YYYY-MM-DD.php`

Look for entries like:

```
Query error: Unknown column '...' in 'field list'
```

## Contact & Support

- **Documentation**: See FINANCE_DASHBOARD_GUIDE.md
- **Model**: app/models/admin/Accounts_dashboard_model.php
- **Controller**: app/controllers/admin/Accounts_dashboard.php
- **View**: themes/blue/admin/views/finance/accounts_dashboard.php

---

**Last Updated**: October 30, 2025
**Version**: 1.0
**Status**: Production Ready
