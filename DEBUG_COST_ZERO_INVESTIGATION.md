# DEBUG: Cost Showing Zero - Investigation & Fix

**Issue:** Dashboard shows cost = 0 even after migration  
**Status:** 🔍 INVESTIGATING  
**User Report:** "Still the cost is showing zero... That won't be the case.. can you check please"

---

## 🔍 Diagnostic Queries

### Query 1: Check sma_purchases Table Exists & Has Data

```sql
-- Check table structure
DESCRIBE sma_purchases;

-- Check data exists
SELECT COUNT(*) as total_purchases FROM sma_purchases;

-- Check October 2025 data
SELECT
    COUNT(*) as oct_2025_purchases,
    SUM(grand_total) as total_amount,
    MIN(DATE(date)) as first_date,
    MAX(DATE(date)) as last_date
FROM sma_purchases
WHERE YEAR(date) = 2025 AND MONTH(date) = 10;

-- Check by warehouse
SELECT
    warehouse_id,
    COUNT(*) as purchase_count,
    SUM(grand_total) as warehouse_total
FROM sma_purchases
WHERE YEAR(date) = 2025 AND MONTH(date) = 10
GROUP BY warehouse_id
ORDER BY warehouse_total DESC;
```

**Expected Result:** Should show purchase data for October 2025 with amounts > 0

---

### Query 2: Check sma_sales Table Has Data

```sql
-- Check October 2025 sales
SELECT
    COUNT(*) as oct_2025_sales,
    SUM(grand_total) as total_amount
FROM sma_sales
WHERE YEAR(date) = 2025 AND MONTH(date) = 10
  AND sale_status IN ('completed', 'completed_partial');

-- Check by warehouse
SELECT
    warehouse_id,
    COUNT(*) as sales_count,
    SUM(grand_total) as warehouse_total
FROM sma_sales
WHERE YEAR(date) = 2025 AND MONTH(date) = 10
  AND sale_status IN ('completed', 'completed_partial')
GROUP BY warehouse_id
ORDER BY warehouse_total DESC;
```

**Expected Result:** Should show sales data for October 2025

---

### Query 3: Check Helper Views

```sql
-- Check view_sales_monthly
SELECT * FROM view_sales_monthly
WHERE period = '2025-10'
ORDER BY warehouse_id;

-- Check view_purchases_monthly
SELECT * FROM view_purchases_monthly
WHERE period = '2025-10'
ORDER BY warehouse_id;
```

**Expected Result:** Should show aggregated data from both views

---

### Query 4: Check Main View

```sql
-- Check view_cost_center_pharmacy
SELECT * FROM view_cost_center_pharmacy
WHERE period = '2025-10'
ORDER BY warehouse_id;

-- Check the calculations
SELECT
    pharmacy_name,
    period,
    kpi_total_revenue,
    kpi_total_cost,
    kpi_profit_loss,
    kpi_profit_margin_pct
FROM view_cost_center_pharmacy
WHERE period = '2025-10'
ORDER BY pharmacy_name;
```

**Expected Result:** Should show revenue and cost values

---

## 🎯 Possible Root Causes

### Cause 1: sma_purchases Table is Empty ❌

**Symptom:** `COUNT(*)` from sma_purchases for Oct 2025 = 0  
**Solution:** Need to load sample purchase data or check data loading process

### Cause 2: Date Format Mismatch 🚫

**Symptom:** `YEAR(date) = 2025 AND MONTH(date) = 10` returns no rows  
**Reason:** Possible date column is not DATETIME/TIMESTAMP  
**Solution:** Check actual date format in sma_purchases

### Cause 3: warehouse_id NULL or Mismatch ⚠️

**Symptom:** warehouse_id values don't match between tables  
**Reason:** warehouse_id might be NULL in sma_purchases  
**Solution:** Check warehouse_id values in all tables

### Cause 4: View Query Has LEFT JOIN Issue 🔗

**Symptom:** View exists but returns empty results  
**Reason:** LEFT JOIN not properly matching on period  
**Solution:** Fix JOIN condition in view

### Cause 5: Migration Not Executed 🛑

**Symptom:** Old views still active, not new views  
**Reason:** Migration file created but not executed  
**Solution:** Execute migration SQL

---

## 🔧 Step-by-Step Investigation

### Step 1: Verify Migration Was Applied

```bash
# Check if views exist
mysql -u admin -p retaj_aldawa -e "SHOW FULL TABLES WHERE TABLE_TYPE = 'VIEW';" | grep view_

# Should show:
# view_sales_monthly
# view_purchases_monthly
# view_cost_center_pharmacy
# view_cost_center_branch
# view_cost_center_summary
```

### Step 2: Check Raw Data in sma_purchases

```sql
-- See actual purchase records
SELECT
    id,
    DATE(date) as purchase_date,
    warehouse_id,
    grand_total,
    status,
    payment_status
FROM sma_purchases
WHERE YEAR(date) = 2025 AND MONTH(date) = 10
LIMIT 10;
```

### Step 3: Check Warehouse ID Mapping

```sql
-- See what warehouse_ids exist in purchases vs dim_pharmacy
SELECT DISTINCT sp.warehouse_id
FROM sma_purchases sp
WHERE YEAR(sp.date) = 2025 AND MONTH(sp.date) = 10;

-- Compare with pharmacy dimension
SELECT DISTINCT warehouse_id
FROM sma_dim_pharmacy
WHERE is_active = 1;

-- Check for mismatches
SELECT DISTINCT sp.warehouse_id
FROM sma_purchases sp
WHERE YEAR(sp.date) = 2025
  AND MONTH(sp.date) = 10
  AND sp.warehouse_id NOT IN (SELECT warehouse_id FROM sma_dim_pharmacy);
```

### Step 4: Test View Query Manually

```sql
-- Test the purchase aggregation manually
SELECT
    warehouse_id,
    YEAR(date) AS purchase_year,
    MONTH(date) AS purchase_month,
    CONCAT(YEAR(date), '-', LPAD(MONTH(date), 2, '0')) AS period,
    SUM(grand_total) AS total_purchase_cost
FROM sma_purchases
WHERE YEAR(date) = 2025
  AND MONTH(date) = 10
  AND status IN ('received', 'received_partial')
GROUP BY warehouse_id, YEAR(date), MONTH(date);

-- Should return rows with total_purchase_cost > 0
```

---

## 🔨 Potential Fixes

### Fix 1: If Migration Not Applied

```bash
# Execute the migration
mysql -u admin -p retaj_aldawa < app/migrations/cost-center/006_fix_cost_profit_calculations.sql
```

### Fix 2: If Date Format is Wrong

Check what date format is in sma_purchases:

```sql
SELECT date, DATE(date), YEAR(date), MONTH(date)
FROM sma_purchases
LIMIT 5;
```

If it's a string, convert it:

```sql
-- If date column is VARCHAR/string
SELECT
    STR_TO_DATE(date, '%Y-%m-%d') as converted_date,
    YEAR(STR_TO_DATE(date, '%Y-%m-%d')) as year
FROM sma_purchases
LIMIT 5;
```

### Fix 3: If warehouse_id is NULL

```sql
-- Check for NULL warehouse_ids
SELECT COUNT(*)
FROM sma_purchases
WHERE warehouse_id IS NULL
  AND YEAR(date) = 2025
  AND MONTH(date) = 10;

-- If result > 0, warehouse_id needs to be populated
```

### Fix 4: Update View with Correct Period Matching

If date format is different, update the view:

```sql
-- This might be needed in migration 006:
DROP VIEW IF EXISTS `view_purchases_monthly`;

CREATE VIEW `view_purchases_monthly` AS
SELECT
    sp.warehouse_id,
    DATE_FORMAT(sp.date, '%Y') AS purchase_year,
    DATE_FORMAT(sp.date, '%m') AS purchase_month,
    DATE_FORMAT(sp.date, '%Y-%m') AS period,
    SUM(sp.grand_total) AS total_purchase_cost,
    COUNT(DISTINCT sp.id) AS purchase_count,
    MAX(sp.date) AS last_purchase_date
FROM sma_purchases sp
WHERE sp.status IN ('received', 'received_partial')
  AND sp.payment_status NOT IN ('draft', 'void', 'cancelled')
GROUP BY
    sp.warehouse_id,
    DATE_FORMAT(sp.date, '%Y'),
    DATE_FORMAT(sp.date, '%m');
```

### Fix 5: Check Pharmacy Links

```sql
-- Check if pharmacies are properly linked
SELECT
    dp.warehouse_id,
    dp.pharmacy_name,
    COUNT(sp.id) as purchase_count,
    SUM(sp.grand_total) as total_cost
FROM sma_dim_pharmacy dp
LEFT JOIN sma_purchases sp ON dp.warehouse_id = sp.warehouse_id
    AND YEAR(sp.date) = 2025
    AND MONTH(sp.date) = 10
WHERE dp.is_active = 1
GROUP BY dp.warehouse_id, dp.pharmacy_name;
```

---

## 📝 Investigation Checklist

Run these in order and note the results:

```sql
-- 1. Check views exist
SHOW FULL TABLES IN retaj_aldawa WHERE TABLE_TYPE = 'VIEW';
-- Expected: 5 views including view_purchases_monthly

-- 2. Check raw purchase data
SELECT COUNT(*) FROM sma_purchases
WHERE YEAR(date) = 2025 AND MONTH(date) = 10;
-- Expected: > 0

-- 3. Check helper view
SELECT * FROM view_purchases_monthly WHERE period = '2025-10';
-- Expected: rows with warehouse_id and total_purchase_cost

-- 4. Check final view
SELECT * FROM view_cost_center_pharmacy WHERE period = '2025-10';
-- Expected: kpi_total_cost > 0

-- 5. Test calculation step by step
SELECT
    warehouse_id,
    SUM(grand_total) as calculated_cost
FROM sma_purchases
WHERE YEAR(date) = 2025 AND MONTH(date) = 10
GROUP BY warehouse_id;
-- Expected: results match what view returns
```

---

## 🚨 Critical Questions to Answer

1. **Is the migration actually applied?**

   - Check: `SHOW FULL TABLES WHERE TABLE_TYPE = 'VIEW'` includes view_purchases_monthly

2. **Does sma_purchases have October 2025 data?**

   - Check: `SELECT COUNT(*) FROM sma_purchases WHERE YEAR(date)=2025 AND MONTH(date)=10`

3. **Are warehouse_ids populated?**

   - Check: `SELECT DISTINCT warehouse_id FROM sma_purchases WHERE date LIKE '2025-10%'`

4. **Is the date format correct?**

   - Check: `SELECT YEAR(date), MONTH(date) FROM sma_purchases LIMIT 5`

5. **Are the statuses matching?**
   - Check: `SELECT DISTINCT status FROM sma_purchases`

---

## Next Actions

1. **Run diagnostic queries** above to identify root cause
2. **Document findings** in this file
3. **Apply appropriate fix** based on root cause
4. **Verify cost now shows** > 0
5. **Test dashboard** again

---

## Document Your Findings Here

**What you discovered:**

```
[Run the diagnostic queries and paste results here]
```

**Root cause identified:**

```
[Which cause was it? 1-5 above?]
```

**Fix applied:**

```
[What did you do to fix it?]
```

**Result after fix:**

```
[Cost value now showing: ______]
```

---

**Status:** 🔍 INVESTIGATING  
**Next:** Execute diagnostic queries and identify root cause
