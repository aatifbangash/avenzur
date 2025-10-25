# FIX: Cost Showing Zero - Troubleshooting Guide

**Issue:** Cost values showing as 0 after migration  
**Root Cause:** To be determined by running diagnostics  
**Status:** 🔧 FIXING

---

## 🚀 QUICK FIX (Try This First)

### Step 1: Use the V2 Migration (Simpler Version)

```bash
# Drop the old views and apply the corrected v2 migration
mysql -u admin -p retaj_aldawa < app/migrations/cost-center/006_fix_cost_profit_calculations_v2.sql
```

### Step 2: Run These Verification Queries

**Query A: Check if purchases data exists**

```sql
SELECT
    COUNT(*) as total_purchases,
    SUM(grand_total) as total_amount,
    MIN(DATE(date)) as first_date,
    MAX(DATE(date)) as last_date
FROM sma_purchases;

-- Expected: Should show some purchase data
```

**Query B: Check October 2025 purchases specifically**

```sql
SELECT
    warehouse_id,
    COUNT(*) as purchase_count,
    SUM(grand_total) as warehouse_total
FROM sma_purchases
WHERE YEAR(date) = 2025 AND MONTH(date) = 10
GROUP BY warehouse_id
ORDER BY warehouse_total DESC;

-- Expected: Should show results with warehouse_id and totals
```

**Query C: Check the helper view**

```sql
SELECT * FROM view_purchases_monthly
WHERE period = '2025-10'
ORDER BY warehouse_id;

-- Expected: Should show warehouse_id, period, total_purchase_cost
```

**Query D: Check the main pharmacy view**

```sql
SELECT
    pharmacy_name,
    period,
    kpi_total_revenue,
    kpi_total_cost,
    kpi_profit_loss
FROM view_cost_center_pharmacy
WHERE period = '2025-10'
ORDER BY kpi_total_revenue DESC;

-- Expected: Should show cost > 0
```

---

## 🔍 DETAILED INVESTIGATION

### Issue 1: Purchases Table is Empty or No Oct 2025 Data

**Test:**

```sql
-- Check if purchases table has any data
SELECT COUNT(*) FROM sma_purchases;

-- Check dates range
SELECT MIN(DATE(date)), MAX(DATE(date)) FROM sma_purchases;

-- Check October 2025 specifically
SELECT COUNT(*) FROM sma_purchases
WHERE YEAR(date) = 2025 AND MONTH(date) = 10;
```

**If count = 0:**

- **Cause:** sma_purchases table is empty OR no October 2025 data exists
- **Action:**
  1. Check if there's data in other months: `SELECT DISTINCT YEAR(date), MONTH(date) FROM sma_purchases ORDER BY date DESC LIMIT 5;`
  2. Use a different period that has data: `SELECT DISTINCT CONCAT(YEAR(date), '-', LPAD(MONTH(date), 2, '0')) FROM sma_purchases ORDER BY date DESC;`
  3. Or load sample purchase data into October 2025

---

### Issue 2: Warehouse IDs Don't Match

**Test:**

```sql
-- Get unique warehouse_ids from purchases
SELECT DISTINCT warehouse_id FROM sma_purchases
WHERE YEAR(date) = 2025 AND MONTH(date) = 10;

-- Get unique warehouse_ids from pharmacy dimension
SELECT DISTINCT warehouse_id FROM sma_dim_pharmacy WHERE is_active = 1;

-- Find mismatches
SELECT DISTINCT sp.warehouse_id
FROM sma_purchases sp
WHERE YEAR(sp.date) = 2025 AND MONTH(sp.date) = 10
  AND sp.warehouse_id NOT IN (SELECT warehouse_id FROM sma_dim_pharmacy WHERE is_active = 1);
```

**If there are mismatches:**

- **Cause:** warehouse_id values don't align between tables
- **Action:**
  1. Check `sma_warehouses` table to see what warehouse_ids should be
  2. Verify `sma_dim_pharmacy` is populated correctly
  3. Check if warehouse_id is NULL in sma_purchases

---

### Issue 3: NULL Warehouse IDs

**Test:**

```sql
-- Check for NULL warehouse_ids
SELECT COUNT(*) as null_count
FROM sma_purchases
WHERE warehouse_id IS NULL
  AND YEAR(date) = 2025
  AND MONTH(date) = 10;

-- If > 0, show some examples
SELECT * FROM sma_purchases
WHERE warehouse_id IS NULL
  AND YEAR(date) = 2025
  AND MONTH(date) = 10
LIMIT 5;
```

**If count > 0:**

- **Cause:** Purchase records don't have warehouse_id populated
- **Action:**
  1. Populate missing warehouse_ids from supplier or warehouse table
  2. Or filter out NULL warehouse_ids in view (already done in v2)

---

### Issue 4: Sales Data Exists But Purchases Don't

**Test:**

```sql
-- Check sales data
SELECT
    COUNT(*) as sales_count,
    SUM(grand_total) as sales_total
FROM sma_sales
WHERE YEAR(date) = 2025 AND MONTH(date) = 10
  AND warehouse_id IS NOT NULL;

-- Check purchases data
SELECT
    COUNT(*) as purchase_count,
    SUM(grand_total) as purchase_total
FROM sma_purchases
WHERE YEAR(date) = 2025 AND MONTH(date) = 10
  AND warehouse_id IS NOT NULL;
```

**If sales > 0 but purchases = 0:**

- **Cause:** Sales exist but no purchases recorded for that period
- **Action:**
  1. Either the business doesn't record purchases in the system
  2. Or purchases are in a different table (check sma_purchase_items)
  3. Or need to populate purchases from another source (invoice, supplier, etc)

---

## 🔧 SOLUTIONS

### Solution 1: Use Different Period with Data

If October 2025 has no purchase data, try another month:

```sql
-- Find months with data
SELECT
    CONCAT(YEAR(date), '-', LPAD(MONTH(date), 2, '0')) as period,
    COUNT(*) as purchase_count,
    SUM(grand_total) as total
FROM sma_purchases
GROUP BY YEAR(date), MONTH(date)
ORDER BY date DESC
LIMIT 10;

-- Then use one of those periods in dashboard
-- http://localhost:8080/avenzur/admin/cost_center/dashboard?period=2023-01
```

### Solution 2: Populate Sample Purchase Data for October 2025

If purchases don't exist, load sample data:

```sql
-- Insert sample purchase records for October 2025
INSERT INTO sma_purchases (
    reference_no, date, supplier_id, supplier, warehouse_id,
    note, total, grand_total, status, payment_status, created_by
) VALUES
-- Pharmacy 52 (warehouse_id = 52)
('PUR-2025-10-001', '2025-10-05 10:00:00', 57, 'PHARMA INDUSTRIES', 52, 'Sample', 150000, 150000, 'received', 'paid', 1),
('PUR-2025-10-002', '2025-10-15 14:30:00', 57, 'PHARMA INDUSTRIES', 52, 'Sample', 180000, 180000, 'received', 'paid', 1),
('PUR-2025-10-003', '2025-10-25 09:15:00', 57, 'PHARMA INDUSTRIES', 52, 'Sample', 190000, 190000, 'received', 'pending', 1),
-- Pharmacy 49 (warehouse_id = X, check sma_warehouses for correct id)
('PUR-2025-10-004', '2025-10-08 11:00:00', 57, 'PHARMA INDUSTRIES', 49, 'Sample', 120000, 120000, 'received', 'paid', 1),
('PUR-2025-10-005', '2025-10-18 15:45:00', 57, 'PHARMA INDUSTRIES', 49, 'Sample', 130000, 130000, 'received', 'paid', 1);
```

### Solution 3: Check If Data is in Different Table Format

Maybe purchases are stored as individual line items, not as totals:

```sql
-- Check sma_purchase_items
SELECT
    warehouse_id,
    COUNT(*) as item_count,
    SUM(quantity * net_unit_cost) as calculated_total
FROM sma_purchase_items
WHERE YEAR(date) = 2025 AND MONTH(date) = 10
GROUP BY warehouse_id;

-- If this has data, might need to sum from here instead
```

### Solution 4: Create a Corrected View Using Purchase Items

If purchase amounts are in purchase_items instead of purchases header:

```sql
-- Use this alternative approach
DROP VIEW IF EXISTS `view_purchases_monthly`;

CREATE VIEW `view_purchases_monthly` AS
SELECT
    spi.warehouse_id,
    YEAR(spi.date) AS purchase_year,
    MONTH(spi.date) AS purchase_month,
    CONCAT(YEAR(spi.date), '-', LPAD(MONTH(spi.date), 2, '0')) AS period,
    SUM(spi.subtotal) AS total_purchase_cost,
    COUNT(DISTINCT spi.purchase_id) AS purchase_count
FROM sma_purchase_items spi
WHERE spi.warehouse_id IS NOT NULL
GROUP BY
    spi.warehouse_id,
    YEAR(spi.date),
    MONTH(spi.date);
```

---

## 📋 DIAGNOSTIC CHECKLIST

Run these in order and document results:

```
1. Check sma_purchases table exists and has data:
   SELECT COUNT(*) FROM sma_purchases;
   Result: ____

2. Check October 2025 purchase data:
   SELECT COUNT(*) FROM sma_purchases WHERE YEAR(date)=2025 AND MONTH(date)=10;
   Result: ____

3. Check view_purchases_monthly view exists:
   SHOW TABLES WHERE TABLE_NAME = 'view_purchases_monthly';
   Result: ____

4. Check view_purchases_monthly has data:
   SELECT COUNT(*) FROM view_purchases_monthly WHERE period = '2025-10';
   Result: ____

5. Check view_cost_center_pharmacy shows cost:
   SELECT kpi_total_cost FROM view_cost_center_pharmacy WHERE period = '2025-10' LIMIT 1;
   Result: ____

6. If cost is still 0, manually calculate:
   SELECT warehouse_id, SUM(grand_total) FROM sma_purchases
   WHERE YEAR(date)=2025 AND MONTH(date)=10 GROUP BY warehouse_id;
   Result: ____
```

---

## 🎯 NEXT STEPS

1. **Execute v2 migration:**

   ```bash
   mysql -u admin -p retaj_aldawa < app/migrations/cost-center/006_fix_cost_profit_calculations_v2.sql
   ```

2. **Run diagnostic Query B above** to check if October 2025 purchase data exists

3. **If no data:**

   - Either load sample data (Solution 2)
   - Or test with a period that has data (Solution 1)

4. **If data exists but cost still 0:**

   - Run remaining diagnostics to identify exact issue
   - Document findings and apply appropriate solution

5. **Test dashboard** and verify cost now shows > 0

---

**Status:** 🔧 READY TO FIX  
**Action:** Run diagnostic queries to identify root cause
