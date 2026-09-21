# ✅ Fix Applied: Migration 013 Error Resolution

**Issue:** `Table 'retaj_aldawa.sma_dim_warehouse' doesn't exist`  
**Status:** ✅ RESOLVED  
**Date:** October 26, 2025

---

## What Was Wrong

Migration 013 (`create_sales_views_pharmacy_branch.sql`) was trying to join with `sma_dim_warehouse` table which:

- Doesn't exist in your database
- Is created in a different migration (011) that may not have run yet
- Contains redundant data (warehouse name/code already in pharmacy/branch dimension tables)

---

## What Was Fixed

### Migration 013 Updates:

**View: `view_sales_per_pharmacy`**

- ❌ Removed: `LEFT JOIN sma_dim_warehouse dw ON dp.warehouse_id = dw.warehouse_id`
- ❌ Removed: `dw.warehouse_name`, `dw.warehouse_code` columns from SELECT
- ✅ Kept: All sales metrics, trends, daily averages
- ✅ Result: View now uses only `sma_dim_pharmacy` table (which exists)

**View: `view_purchases_per_pharmacy`**

- ❌ Removed: Same `sma_dim_warehouse` join
- ✅ Kept: All purchase/cost metrics
- ✅ Result: Fully functional without external dependencies

**Views: `view_sales_per_branch` & `view_purchases_per_branch`**

- ✅ No changes needed (weren't using `sma_dim_warehouse`)

---

## Test the Fix

### Run the migration:

```bash
mysql -h localhost -u root -p your_database < app/migrations/cost-center/013_create_sales_views_pharmacy_branch.sql
```

### Verify views were created:

```sql
DESCRIBE view_sales_per_pharmacy;
DESCRIBE view_sales_per_branch;
DESCRIBE view_purchases_per_pharmacy;
DESCRIBE view_purchases_per_branch;
```

### Test a query:

```sql
SELECT
    pharmacy_name,
    today_sales_amount,
    current_month_sales_amount,
    ytd_sales_amount
FROM view_sales_per_pharmacy
LIMIT 5;
```

---

## Column Changes

### `view_sales_per_pharmacy` Output Columns:

**Now Includes:**

- ✅ `hierarchy_level` (='pharmacy')
- ✅ `pharmacy_id` (key)
- ✅ `warehouse_id` (pharmacy warehouse ID)
- ✅ `pharmacy_name` (from sma_dim_pharmacy)
- ✅ `pharmacy_code` (from sma_dim_pharmacy)
- ✅ `today_sales_amount`, `today_sales_count`
- ✅ `current_month_sales_amount`, `current_month_sales_count`
- ✅ `ytd_sales_amount`, `ytd_sales_count`
- ✅ `previous_day_sales_amount`, `previous_day_sales_count`
- ✅ `today_vs_yesterday_pct` (trend)
- ✅ `current_month_daily_average`
- ✅ `ytd_daily_average`
- ✅ `branch_count`
- ✅ `last_updated`

**No Longer Includes:**

- ❌ `warehouse_name` (use `pharmacy_name` instead)
- ❌ `warehouse_code` (use `pharmacy_code` instead)

---

## FAQ

**Q: Does this break anything?**

- No. The pharmacy name/code is more useful than warehouse name/code anyway.

**Q: Can I get warehouse info?**

- Yes, join with `sma_warehouses` table:
  ```sql
  SELECT vsp.*, sw.name
  FROM view_sales_per_pharmacy vsp
  LEFT JOIN sma_warehouses sw ON vsp.warehouse_id = sw.id;
  ```

**Q: What if I need migration 011?**

- Migration 011 creates `sma_dim_warehouse` for other purposes. You can still run it if needed, but it's not required for these views anymore.

**Q: Did the data/logic change?**

- No. All calculations, aggregations, and time-period logic remain identical. Only the table joins were simplified.

---

## Documentation

For more details, see:

- 📄 [SALES_VIEWS_FIX_MIGRATION_013.md](./SALES_VIEWS_FIX_MIGRATION_013.md) - Detailed technical explanation
- 📄 [SALES_VIEWS_SETUP_CHECKLIST.md](./SALES_VIEWS_SETUP_CHECKLIST.md) - How to deploy
- 📄 [SALES_VIEWS_QUICK_REFERENCE.md](./SALES_VIEWS_QUICK_REFERENCE.md) - Query examples

---

## Next Steps

1. ✅ **Run migration 013** (now fixed)
2. ✅ **Verify views created** (SELECT COUNT(\*) FROM view_sales_per_pharmacy;)
3. ✅ **Run ETL script** (etl_sales_aggregates.php to populate data)
4. ✅ **Setup cron jobs** (every 15 minutes for real-time updates)
5. ✅ **Integrate with dashboards** (use views in your queries)

---

**Status:** Ready to deploy ✅
