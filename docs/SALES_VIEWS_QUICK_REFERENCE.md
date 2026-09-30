# Sales Views - Quick Reference

## What Was Created

### 3 New Aggregate Tables

```
sma_sales_aggregates          (daily sales metrics)
sma_purchases_aggregates      (daily purchase metrics)
sma_sales_aggregates_hourly   (hourly for real-time)
```

### 4 New Views

```
view_sales_per_pharmacy       (pharmacy-level sales)
view_sales_per_branch         (branch-level sales)
view_purchases_per_pharmacy   (pharmacy-level costs)
view_purchases_per_branch     (branch-level costs)
```

### 1 New ETL Script

```
etl_sales_aggregates.php      (populates aggregates daily)
```

---

## Time Periods Available

Each view includes metrics for:

| Period            | Columns                                                                                  | Use Case                       |
| ----------------- | ---------------------------------------------------------------------------------------- | ------------------------------ |
| **Today**         | `today_sales_amount`, `today_sales_count`                                                | Daily performance              |
| **Current Month** | `current_month_sales_amount`, `current_month_sales_count`, `current_month_daily_average` | Monthly targets, daily average |
| **Year-to-Date**  | `ytd_sales_amount`, `ytd_sales_count`, `ytd_daily_average`                               | Annual performance             |
| **Previous Day**  | `previous_day_sales_amount`, `previous_day_sales_count`                                  | Trend comparison               |
| **Trends**        | `today_vs_yesterday_pct`                                                                 | % change analysis              |

---

## Hierarchy Structure

### Pharmacy Level

```
Pharmacy (warehouse_id = parent)
├── Branch 1 (warehouse_id = child)
├── Branch 2 (warehouse_id = child)
└── Branch N (warehouse_id = child)
```

**Query:** `view_sales_per_pharmacy` → Shows pharmacy + all branches

### Branch Level

```
Branch (warehouse_id = leaf)
└── Parent Pharmacy (pharmacy_id = parent)
```

**Query:** `view_sales_per_branch` → Shows branch + parent link

---

## Quick Queries

### 1. Today's Sales by Pharmacy

```sql
SELECT pharmacy_name, today_sales_amount, branch_count
FROM view_sales_per_pharmacy
ORDER BY today_sales_amount DESC;
```

### 2. Current Month Performance by Branch

```sql
SELECT branch_name, pharmacy_name,
       current_month_sales_amount,
       current_month_daily_average
FROM view_sales_per_branch
WHERE pharmacy_id = 1
ORDER BY current_month_sales_amount DESC;
```

### 3. YTD Sales vs Daily Average

```sql
SELECT pharmacy_name,
       ytd_sales_amount,
       ytd_daily_average,
       (ytd_sales_amount / ytd_daily_average) AS days_of_sales
FROM view_sales_per_pharmacy
ORDER BY ytd_sales_amount DESC;
```

### 4. Today vs Yesterday Trend

```sql
SELECT branch_name,
       today_sales_amount,
       previous_day_sales_amount,
       today_vs_yesterday_pct AS pct_change
FROM view_sales_per_branch
WHERE today_vs_yesterday_pct < -10  -- Down more than 10%
ORDER BY today_vs_yesterday_pct ASC;
```

### 5. Pharmacy Profitability (Today)

```sql
SELECT
    p.pharmacy_name,
    COALESCE(s.today_sales_amount, 0) AS today_sales,
    COALESCE(c.today_cost_amount, 0) AS today_cost,
    COALESCE(s.today_sales_amount, 0) - COALESCE(c.today_cost_amount, 0) AS today_profit,
    ROUND(
        (COALESCE(s.today_sales_amount, 0) - COALESCE(c.today_cost_amount, 0))
        / NULLIF(COALESCE(s.today_sales_amount, 0), 0) * 100, 2
    ) AS profit_margin_pct
FROM sma_dim_pharmacy p
LEFT JOIN view_sales_per_pharmacy s ON p.warehouse_id = s.warehouse_id
LEFT JOIN view_purchases_per_pharmacy c ON p.warehouse_id = c.warehouse_id
ORDER BY today_profit DESC;
```

### 6. Real-Time Hourly Sales (Today)

```sql
SELECT
    aggregate_hour,
    SUM(hour_sales_amount) AS hour_sales,
    SUM(today_sales_amount) AS running_total
FROM sma_sales_aggregates_hourly
WHERE aggregate_date = CURDATE()
GROUP BY aggregate_hour
ORDER BY aggregate_hour;
```

---

## Column Mapping

### Sales Metrics

- **today_sales_amount** → SAR amount sold today
- **today_sales_count** → Number of transactions today
- **current_month_sales_amount** → Total sales from 1st to today of month
- **current_month_sales_count** → Number of transactions
- **ytd_sales_amount** → Total sales from Jan 1 to today
- **ytd_sales_count** → Transaction count YTD

### Purchase/Cost Metrics

- **today_cost_amount** → Purchase cost today
- **current_month_cost_amount** → Purchase cost for current month
- **ytd_cost_amount** → Purchase cost YTD

### Averages (Calculated)

- **current_month_daily_average** = current_month_sales_amount / day_of_month
- **ytd_daily_average** = ytd_sales_amount / day_of_year
- **today_vs_yesterday_pct** = ((today - yesterday) / yesterday) \* 100

### Hierarchy

- **pharmacy_id** → Parent pharmacy identifier
- **pharmacy_warehouse_id** → Pharmacy's warehouse_id
- **branch_id** → Branch identifier
- **warehouse_id** → Branch's warehouse_id

---

## ETL Schedule

### Real-Time Updates

```bash
*/15 * * * * /usr/bin/php /path/to/etl_sales_aggregates.php
```

Runs every 15 minutes for up-to-date hourly metrics

### Daily Backfill

```bash
0 2 * * * /usr/bin/php /path/to/etl_sales_aggregates.php backfill $(date -d yesterday +\%Y-\%m-\%d) $(date +\%Y-\%m-\%d)
```

Runs at 2 AM daily to ensure all data is up-to-date

### Initial Setup

```bash
php etl_sales_aggregates.php backfill 2025-01-01 2025-10-26
```

---

## Data Flow

```
sma_sales (transactions)
    ↓
etl_sales_aggregates.php
    ↓
sma_sales_aggregates (daily metrics)
    ├─ today_sales_amount
    ├─ current_month_sales_amount
    ├─ ytd_sales_amount
    └─ previous_day_sales_amount
    ↓
view_sales_per_pharmacy
view_sales_per_branch
```

---

## Cost Center Integration

### Before (Monthly Only)

- view_cost_center_pharmacy (monthly KPIs)
- view_cost_center_branch (monthly KPIs)

### After (Daily + Monthly)

```sql
SELECT
    cc.pharmacy_id,
    cc.kpi_total_revenue,              -- Monthly
    sp.today_sales_amount,             -- Daily
    sp.current_month_sales_amount,     -- Monthly (progressive)
    sp.ytd_sales_amount,               -- Annual
    cc.kpi_profit_loss                 -- Monthly
FROM view_cost_center_pharmacy cc
LEFT JOIN view_sales_per_pharmacy sp ON cc.warehouse_id = sp.warehouse_id;
```

---

## Verification Queries

### Check if ETL Ran

```sql
SELECT MAX(aggregate_date), COUNT(*) FROM sma_sales_aggregates;
```

### Check Today's Data

```sql
SELECT * FROM view_sales_per_pharmacy
WHERE today_sales_amount > 0;
```

### Check for Missing Warehouses

```sql
SELECT w.id, w.name
FROM sma_warehouses w
WHERE w.id NOT IN (
    SELECT DISTINCT warehouse_id FROM sma_sales_aggregates
    WHERE aggregate_date = CURDATE()
);
```

### Verify Trends

```sql
SELECT pharmacy_name,
       today_vs_yesterday_pct,
       today_sales_amount,
       previous_day_sales_amount
FROM view_sales_per_pharmacy
WHERE today_sales_amount > 0
ORDER BY today_vs_yesterday_pct DESC;
```

---

## Notes

- All time periods are **calendar-based** (not rolling)

  - Today = current calendar day
  - Current Month = 1st to today of this month
  - YTD = Jan 1 to today of this year

- **Aggregates are warehouse-scoped** (pharmacy or branch)

  - Pharmacy aggregates include ALL branches
  - Branch aggregates include ONLY that branch

- **ETL uses ON DUPLICATE KEY UPDATE**

  - Safe to re-run multiple times
  - Latest run wins
  - Idempotent operations

- **Hourly data kept for 90 days**
  - Daily data kept indefinitely
  - Configure via maintenance scripts

---

## Files Reference

| File                                         | Purpose            |
| -------------------------------------------- | ------------------ |
| `012_create_sales_aggregates_tables.sql`     | Creates tables     |
| `013_create_sales_views_pharmacy_branch.sql` | Creates views      |
| `etl_sales_aggregates.php`                   | ETL script         |
| `SALES_VIEWS_IMPLEMENTATION_GUIDE.md`        | Full documentation |
