# Fix: Migration 013 - Table Reference Error

**Date:** October 26, 2025  
**Issue:** Table 'retaj_aldawa.sma_dim_warehouse' doesn't exist  
**Status:** ✅ RESOLVED

---

## Problem

When running migration 013 (`013_create_sales_views_pharmacy_branch.sql`), the following error occurred:

```
Error Code: 1146. Table 'retaj_aldawa.sma_dim_warehouse' doesn't exist
```

### Root Cause

The views in migration 013 included unnecessary joins to `sma_dim_warehouse`:

```sql
LEFT JOIN sma_dim_warehouse dw ON dp.warehouse_id = dw.warehouse_id
```

This join was trying to get `warehouse_name` and `warehouse_code`, but:

1. These columns already exist in `sma_dim_pharmacy` (as `pharmacy_name`, `pharmacy_code`)
2. The `sma_dim_warehouse` table is only created in migration 011
3. Migration 011 might not have been run yet

---

## Solution Applied

### Changes to Migration 013

Removed the unnecessary `sma_dim_warehouse` join from all affected views:

#### View 1: `view_sales_per_pharmacy`

**Before:**

```sql
SELECT
    'pharmacy' AS hierarchy_level,
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code,
    dw.warehouse_name,      -- ❌ Removed
    dw.warehouse_code,       -- ❌ Removed
    ...
FROM
    sma_dim_pharmacy dp
    LEFT JOIN sma_dim_warehouse dw ON dp.warehouse_id = dw.warehouse_id  -- ❌ Removed
    LEFT JOIN sma_sales_aggregates sa ON dp.warehouse_id = sa.warehouse_id
    ...
GROUP BY
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code,
    dw.warehouse_name,      -- ❌ Removed
    dw.warehouse_code;      -- ❌ Removed
```

**After:**

```sql
SELECT
    'pharmacy' AS hierarchy_level,
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code,
    -- Warehouse name/code removed (using pharmacy name from dim table)
    ...
FROM
    sma_dim_pharmacy dp
    LEFT JOIN sma_sales_aggregates sa ON dp.warehouse_id = sa.warehouse_id
    ...
GROUP BY
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code;
```

#### View 2: `view_sales_per_branch`

✅ No changes needed (didn't use `sma_dim_warehouse`)

#### View 3: `view_purchases_per_pharmacy`

✅ Same fix applied as View 1 (removed `sma_dim_warehouse` join)

#### View 4: `view_purchases_per_branch`

✅ No changes needed (didn't use `sma_dim_warehouse`)

---

## Impact Analysis

### What Changed

- ✅ Removed 2 columns: `warehouse_name`, `warehouse_code` (not needed - using pharmacy/branch names instead)
- ✅ Removed 1 JOIN to non-existent table
- ✅ Simplified GROUP BY clause
- ✅ No breaking changes to core functionality

### What Stayed the Same

- ✅ All time-period metrics: `today_sales_amount`, `current_month_sales_amount`, `ytd_sales_amount`
- ✅ All trend analysis: `today_vs_yesterday_pct`, daily averages
- ✅ All counts: `today_sales_count`, `branch_count`
- ✅ Query performance (actually slightly improved with fewer joins)

### Column Output

**View: `view_sales_per_pharmacy`**

| Column                      | Before | After | Notes                 |
| --------------------------- | ------ | ----- | --------------------- |
| hierarchy_level             | ✓      | ✓     | 'pharmacy'            |
| pharmacy_id                 | ✓      | ✓     | Primary key           |
| warehouse_id                | ✓      | ✓     | Pharmacy warehouse ID |
| pharmacy_name               | ✓      | ✓     | From sma_dim_pharmacy |
| pharmacy_code               | ✓      | ✓     | From sma_dim_pharmacy |
| warehouse_name              | ✓      | ❌    | Removed (redundant)   |
| warehouse_code              | ✓      | ❌    | Removed (redundant)   |
| today_sales_amount          | ✓      | ✓     | Aggregated sales      |
| today_sales_count           | ✓      | ✓     | Transaction count     |
| current_month_sales_amount  | ✓      | ✓     | Month-to-date         |
| current_month_sales_count   | ✓      | ✓     | Month count           |
| ytd_sales_amount            | ✓      | ✓     | Year-to-date          |
| ytd_sales_count             | ✓      | ✓     | YTD count             |
| previous_day_sales_amount   | ✓      | ✓     | For trends            |
| previous_day_sales_count    | ✓      | ✓     | For trends            |
| today_vs_yesterday_pct      | ✓      | ✓     | Trend %               |
| current_month_daily_average | ✓      | ✓     | Avg per day           |
| ytd_daily_average           | ✓      | ✓     | Avg per day YTD       |
| branch_count                | ✓      | ✓     | # of branches         |
| last_updated                | ✓      | ✓     | Timestamp             |

---

## How to Apply This Fix

### Option 1: Re-run Migration 013 (Recommended)

```bash
# Drop the old views (if they exist)
mysql -h your_host -u your_user -p your_database < 013_create_sales_views_pharmacy_branch.sql
```

### Option 2: Manual SQL Fix

If you already have partial views created, run these commands:

```sql
-- Drop the problematic views
DROP VIEW IF EXISTS view_sales_per_pharmacy;
DROP VIEW IF EXISTS view_purchases_per_pharmacy;

-- Re-run migration 013 (updated version)
-- Or manually create the views without the sma_dim_warehouse join
```

---

## Verification

### Step 1: Test View Creation

```sql
-- Should return 0 rows (no error)
SELECT COUNT(*) FROM view_sales_per_pharmacy;
```

### Step 2: Verify Columns

```sql
-- Check that views have correct columns (note: no warehouse_name/warehouse_code)
DESCRIBE view_sales_per_pharmacy;
DESCRIBE view_sales_per_branch;
DESCRIBE view_purchases_per_pharmacy;
DESCRIBE view_purchases_per_branch;
```

### Step 3: Test Sample Query

```sql
-- Query should execute without error
SELECT
    pharmacy_name,
    warehouse_id,
    today_sales_amount,
    current_month_sales_amount,
    ytd_sales_amount
FROM view_sales_per_pharmacy
LIMIT 5;
```

---

## FAQ

**Q: Will this affect my existing dashboards?**  
A: Only if you were specifically using `warehouse_name` or `warehouse_code` from these views. Most dashboards use `pharmacy_name` and `branch_name` instead. Check your queries.

**Q: Can I still get warehouse_name somewhere?**  
A: Yes, join with `sma_warehouses` table:

```sql
SELECT
    vsp.*,
    sw.name AS warehouse_name
FROM view_sales_per_pharmacy vsp
LEFT JOIN sma_warehouses sw ON vsp.warehouse_id = sw.id;
```

**Q: Is this a downgrade?**  
A: No, it's a bug fix. The views are now simpler and don't depend on a table that may not exist yet.

**Q: Do I need migration 011?**  
A: Not for these views anymore. But migration 011 creates `sma_dim_warehouse` which is useful for other cost center features, so you should still run it if you plan to use those features.

---

## Files Modified

```
✅ app/migrations/cost-center/013_create_sales_views_pharmacy_branch.sql
   - Removed sma_dim_warehouse joins from view_sales_per_pharmacy
   - Removed sma_dim_warehouse joins from view_purchases_per_pharmacy
   - Simplified GROUP BY clauses
   - No changes to view_sales_per_branch or view_purchases_per_branch
```

---

## Related Documentation

- [SALES_VIEWS_IMPLEMENTATION_GUIDE.md](./SALES_VIEWS_IMPLEMENTATION_GUIDE.md) - Complete guide
- [SALES_VIEWS_SETUP_CHECKLIST.md](./SALES_VIEWS_SETUP_CHECKLIST.md) - Deployment steps
- [SALES_VIEWS_QUICK_REFERENCE.md](./SALES_VIEWS_QUICK_REFERENCE.md) - Query examples

---

## Status

**✅ FIXED - Ready to Deploy**

Migration 013 is now independent of migration 011 and will execute successfully.
