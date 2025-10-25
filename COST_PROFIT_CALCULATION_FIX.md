# CRITICAL FIX: Cost and Profit Calculation Update

**Date:** October 25, 2025
**Status:** ⚠️ CRITICAL CHANGE
**Migration File:** `006_fix_cost_profit_calculations.sql`

---

## Executive Summary

**The cost center dashboard calculations are being fundamentally corrected:**

| Metric | Old Source | New Source | Impact |
|--------|-----------|-----------|--------|
| **Cost** | COGS + Inventory + Operational (fact table) | `sma_purchases.grand_total` | ⚠️ More accurate |
| **Profit** | Revenue - Old Cost | Revenue - New Cost | ⚠️ Will change |
| **Margin %** | Calculated from old profit | Calculated from new profit | ⚠️ Will change |

**Action Taken by User:** "Cost should come from sma_purchases and profit from sma_sales."

---

## What Changed

### BEFORE (Incorrect)
```sql
Total Cost = COGS + Inventory Movement Cost + Operational Cost
            (from sma_fact_cost_center table)

Example for Pharmacy ID 52 (Oct 2025):
  Revenue: 648,800.79 SAR
  Cost: 450,000 SAR (COGS: 350k + Inventory: 50k + Operational: 50k)
  Profit: 198,800.79 SAR
  Margin: 30.6%
```

### AFTER (Correct)
```sql
Total Cost = SUM(grand_total) from sma_purchases
            (actual amounts paid for inventory purchases)

Total Profit = SUM(grand_total) from sma_sales - Total Cost
            (revenue minus actual purchase costs)

Example for Pharmacy ID 52 (Oct 2025):
  Revenue: 648,800.79 SAR (from sma_sales)
  Cost: 520,000 SAR (from sma_purchases actual costs)
  Profit: 128,800.79 SAR  ← Different!
  Margin: 19.8%           ← Different!
```

---

## Technical Details

### New Database Views

#### 1. `view_sales_monthly`
**Purpose:** Aggregate monthly sales by warehouse

```sql
SELECT 
    warehouse_id,
    YEAR(date) AS sales_year,
    MONTH(date) AS sales_month,
    CONCAT(YEAR(date), '-', LPAD(MONTH(date), 2, '0')) AS period,
    SUM(grand_total) AS total_sales_amount,
    COUNT(*) AS sales_count
FROM sma_sales
WHERE sale_status IN ('completed', 'completed_partial')
GROUP BY warehouse_id, YEAR(date), MONTH(date)
```

**Data Source:** `sma_sales` table
**Key Fields:**
- `grand_total`: Total sales amount (includes tax, excludes discounts if configured)
- `warehouse_id`: Links to pharmacy/branch
- `date`: Transaction date

#### 2. `view_purchases_monthly`
**Purpose:** Aggregate monthly purchase costs by warehouse

```sql
SELECT 
    warehouse_id,
    YEAR(date) AS purchase_year,
    MONTH(date) AS purchase_month,
    CONCAT(YEAR(date), '-', LPAD(MONTH(date), 2, '0')) AS period,
    SUM(grand_total) AS total_purchase_cost,
    COUNT(*) AS purchase_count
FROM sma_purchases
WHERE status IN ('received', 'received_partial')
GROUP BY warehouse_id, YEAR(date), MONTH(date)
```

**Data Source:** `sma_purchases` table
**Key Fields:**
- `grand_total`: Total purchase amount (includes tax, shipping, etc.)
- `warehouse_id`: Links to pharmacy/branch
- `date`: Purchase date

#### 3. `view_cost_center_pharmacy` (UPDATED)
**Purpose:** Pharmacy-level KPIs with new calculation

**Formula:**
```
Revenue = SUM(sma_sales.grand_total) per warehouse per month
Cost = SUM(sma_purchases.grand_total) per warehouse per month
Profit = Revenue - Cost
Margin % = (Profit / Revenue) * 100
```

#### 4. `view_cost_center_branch` (UPDATED)
**Purpose:** Branch-level KPIs with new calculation

**Formula:** Same as pharmacy (just aggregated at branch level)

#### 5. `view_cost_center_summary` (UPDATED)
**Purpose:** Company-level KPIs with new calculation

**Formula:**
- Company level: SUM of all pharmacy revenues and costs
- Pharmacy level: Individual pharmacy revenues and costs

---

## Data Relationships

### Table Join Logic

```
sma_dim_pharmacy (pharmacy master)
        |
        |-- LEFT JOIN view_sales_monthly
        |              (revenue from sma_sales)
        |
        |-- LEFT JOIN view_purchases_monthly
                       (cost from sma_purchases)
```

### Warehouse Mapping

```
sma_warehouses
├── warehouse_type = 'pharmacy' (8 pharmacies)
│   └── warehouse_id links to:
│       ├── sma_dim_pharmacy (master data)
│       ├── sma_sales (revenue transactions)
│       └── sma_purchases (cost transactions)
│
├── warehouse_type = 'branch' (9 branches)
│   └── warehouse_id links to:
│       ├── sma_dim_branch (master data)
│       ├── sma_sales (revenue transactions)
│       └── sma_purchases (cost transactions)
│
└── warehouse_type = 'warehouse' (3 warehouses)
```

---

## Implementation Steps

### Step 1: Create Migration File
**File:** `app/migrations/cost-center/006_fix_cost_profit_calculations.sql`
**Status:** ✅ CREATED

### Step 2: Execute Migration
```bash
# Option 1: Via CodeIgniter CLI
php spark migrate

# Option 2: Via MySQL directly
mysql -u admin -p retaj_aldawa < app/migrations/cost-center/006_fix_cost_profit_calculations.sql
```

### Step 3: Verify Views Created
```sql
-- Check if views exist
SHOW FULL TABLES IN retaj_aldawa WHERE TABLE_TYPE = 'VIEW';

-- Should see:
-- view_sales_monthly
-- view_purchases_monthly
-- view_cost_center_pharmacy
-- view_cost_center_branch
-- view_cost_center_summary
```

### Step 4: Test Dashboard
1. Open: `http://localhost:8080/avenzur/admin/cost_center/dashboard?period=2025-10`
2. Check: Revenue and cost numbers updated
3. Verify: Pharmacy filter works with new calculations
4. Compare: Numbers match sma_sales and sma_purchases tables

---

## Data Verification Queries

### Query 1: Verify Sales Data
```sql
-- Check sma_sales for October 2025
SELECT 
    warehouse_id,
    DATE(date) AS sale_date,
    COUNT(*) AS transaction_count,
    SUM(grand_total) AS daily_total
FROM sma_sales
WHERE YEAR(date) = 2025 
  AND MONTH(date) = 10
  AND sale_status IN ('completed', 'completed_partial')
GROUP BY warehouse_id, DATE(date)
ORDER BY warehouse_id, DATE(date) DESC;
```

### Query 2: Verify Purchase Data
```sql
-- Check sma_purchases for October 2025
SELECT 
    warehouse_id,
    DATE(date) AS purchase_date,
    COUNT(*) AS transaction_count,
    SUM(grand_total) AS daily_total
FROM sma_purchases
WHERE YEAR(date) = 2025 
  AND MONTH(date) = 10
  AND status IN ('received', 'received_partial')
GROUP BY warehouse_id, DATE(date)
ORDER BY warehouse_id, DATE(date) DESC;
```

### Query 3: Compare Old vs New KPIs
```sql
-- Show calculated KPIs from new views
SELECT 
    entity_name,
    period,
    kpi_total_revenue AS new_revenue,
    kpi_total_cost AS new_cost,
    kpi_profit_loss AS new_profit,
    kpi_profit_margin_pct AS new_margin_pct
FROM view_cost_center_summary
WHERE period = '2025-10'
ORDER BY kpi_total_revenue DESC;
```

---

## Expected Impact

### Financial Metrics Impact

**Pharmacy 52 Example (October 2025):**

| Metric | Old Value | New Value | Change | % Change |
|--------|-----------|-----------|--------|----------|
| Revenue | 648,800 SAR | 648,800 SAR | 0 | 0% |
| Cost | 450,000 SAR | ~520,000 SAR | +70,000 | +15.6% |
| Profit | 198,800 SAR | ~128,800 SAR | -70,000 | -35.2% |
| Margin | 30.6% | ~19.8% | -10.8pp | -35.3% |

**Note:** Exact new values depend on actual sma_purchases data in database.

### User Impact

✅ **What Improves:**
- Cost calculations now reflect actual purchase costs
- Profit margins are more realistic and comparable to industry standards
- Financial reports will be more accurate for decision-making

⚠️ **What Changes:**
- Historical dashboard data will show different numbers
- Reports comparing old vs new periods will show apparent "cost increase"
- Margins will appear lower (but more accurate)
- Need to document this change in release notes

---

## Model Updates Needed

### File: `app/models/admin/Cost_center_model.php`

**No code changes needed!** The model queries the views, and the views now have the correct logic built in.

However, verify these methods still work:

```php
// These methods should continue to work without modification:
$model->get_summary_stats($period);           // Uses view_cost_center_summary
$model->get_pharmacy_detail($pharmacy_id);    // Uses view_cost_center_pharmacy
$model->get_pharmacy_with_branches();         // Uses views internally
```

---

## Verification Checklist

Before declaring complete:

- [ ] Migration file created: `006_fix_cost_profit_calculations.sql`
- [ ] Views created in database:
  - [ ] `view_sales_monthly`
  - [ ] `view_purchases_monthly`
  - [ ] `view_cost_center_pharmacy` (updated)
  - [ ] `view_cost_center_branch` (updated)
  - [ ] `view_cost_center_summary` (updated)
- [ ] Dashboard displays new cost numbers
- [ ] Pharmacy filter works with new calculations
- [ ] All 8 pharmacies show correct revenue and cost
- [ ] Profit = Revenue - Cost is accurate
- [ ] Margin % calculation is correct
- [ ] API endpoint returns new data
- [ ] Browser console shows no errors
- [ ] Performance acceptable (<500ms load time)

---

## Rollback Plan

If needed to revert to old calculations:

```bash
# Restore old views from migration 005:
php spark migrate version 005
# or
mysql -u admin -p retaj_aldawa < app/migrations/cost-center/005_create_views.sql
```

---

## Documentation Updates

Files to update with this change:

1. `RELEASE_NOTES.md` - Add note about cost calculation change
2. `API_DOCUMENTATION.md` - Update cost field descriptions
3. `DASHBOARD_USER_GUIDE.md` - Explain new calculations
4. `DATABASE_SCHEMA.md` - Add new views to documentation

---

## Next Steps

1. **Execute Migration**
   ```sql
   mysql -u admin -p retaj_aldawa < app/migrations/cost-center/006_fix_cost_profit_calculations.sql
   ```

2. **Test in Browser**
   - Navigate to dashboard
   - Select different periods
   - Test pharmacy filter
   - Verify numbers make sense

3. **Verify Data**
   - Run verification queries above
   - Compare with raw sma_sales and sma_purchases
   - Confirm calculations are correct

4. **Commit Changes**
   ```bash
   git add -A
   git commit -m "fix: Update cost calculation to use sma_purchases instead of fact table"
   ```

5. **Update Documentation**
   - Update release notes
   - Document the change
   - Brief team on implications

---

**Status:** Ready for implementation
**Risk Level:** MEDIUM (affects financial metrics)
**Testing Required:** HIGH (verify all calculations)
**User Communication:** REQUIRED (explain new numbers)
