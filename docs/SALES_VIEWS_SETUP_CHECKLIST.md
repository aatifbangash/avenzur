# Sales Views Setup Checklist

## Phase 1: Database Migrations ✓ READY

### Step 1: Run Migration 012 (Create Tables)

```bash
mysql -u root -p avenzur_pharmacy < /path/to/app/migrations/cost-center/012_create_sales_aggregates_tables.sql
```

**Verify:**

```sql
SHOW TABLES LIKE 'sma_sales%';
SHOW TABLES LIKE 'sma_purchases%';
```

Expected output:

```
sma_sales_aggregates
sma_sales_aggregates_hourly
sma_purchases_aggregates
etl_sales_aggregates_log
```

### Step 2: Run Migration 013 (Create Views)

```bash
mysql -u root -p avenzur_pharmacy < /path/to/app/migrations/cost-center/013_create_sales_views_pharmacy_branch.sql
```

**Verify:**

```sql
SHOW VIEWS LIKE 'view_sales%';
SHOW VIEWS LIKE 'view_purchases%';
```

Expected output:

```
view_sales_per_pharmacy
view_sales_per_branch
view_purchases_per_pharmacy
view_purchases_per_branch
```

---

## Phase 2: ETL Setup ✓ READY

### Step 3: Place ETL Script

```bash
cp etl_sales_aggregates.php /var/www/avenzur/database/scripts/
chmod +x /var/www/avenzur/database/scripts/etl_sales_aggregates.php
```

### Step 4: Test ETL Script

```bash
# Test today's run (no data yet)
php /var/www/avenzur/database/scripts/etl_sales_aggregates.php

# Check output for:
# - "Sales Aggregates ETL Pipeline Started"
# - "✓ Updated X warehouse sales records"
# - "✓ Sales Aggregates ETL completed successfully"
```

### Step 5: Backfill Historical Data

```bash
# Backfill last 30 days
php /var/www/avenzur/database/scripts/etl_sales_aggregates.php backfill \
    $(date -d '30 days ago' +%Y-%m-%d) \
    $(date +%Y-%m-%d)

# Or specific date
php /var/www/avenzur/database/scripts/etl_sales_aggregates.php backfill 2025-01-01 2025-10-26
```

**Monitor Progress:**

```sql
SELECT
    MAX(aggregate_date) as latest_date,
    COUNT(*) as total_records,
    SUM(today_sales_amount) as total_sales
FROM sma_sales_aggregates;
```

### Step 6: Setup Cron Jobs

**Add to /etc/crontab or user crontab:**

#### Real-Time Updates (Every 15 minutes)

```bash
*/15 * * * * /usr/bin/php /var/www/avenzur/database/scripts/etl_sales_aggregates.php >> /var/log/avenzur/etl_sales.log 2>&1
```

#### Daily Backfill (2 AM)

```bash
0 2 * * * /usr/bin/php /var/www/avenzur/database/scripts/etl_sales_aggregates.php backfill $(date -d 'yesterday' +\%Y-\%m-\%d) $(date +\%Y-\%m-\%d) >> /var/log/avenzur/etl_sales.log 2>&1
```

**Verify Cron:**

```bash
crontab -l | grep etl_sales
```

---

## Phase 3: Verification ✓ READY

### Step 7: Verify Data in Views

**Check Pharmacy View:**

```sql
SELECT
    pharmacy_name,
    today_sales_amount,
    current_month_sales_amount,
    ytd_sales_amount,
    branch_count
FROM view_sales_per_pharmacy
WHERE today_sales_amount > 0
LIMIT 5;
```

**Check Branch View:**

```sql
SELECT
    branch_name,
    pharmacy_name,
    today_sales_amount,
    current_month_sales_amount,
    ytd_sales_amount
FROM view_sales_per_branch
WHERE today_sales_amount > 0
LIMIT 5;
```

**Check Hourly Data:**

```sql
SELECT
    aggregate_hour,
    SUM(hour_sales_amount) as hour_sales,
    SUM(today_sales_amount) as running_total
FROM sma_sales_aggregates_hourly
WHERE aggregate_date = CURDATE()
GROUP BY aggregate_hour;
```

### Step 8: Check ETL Logs

**Query Log Table:**

```sql
SELECT
    aggregate_date,
    status,
    rows_processed,
    duration_seconds,
    error_message
FROM etl_sales_aggregates_log
ORDER BY aggregate_date DESC
LIMIT 10;
```

**File Logs:**

```bash
tail -50 /var/log/avenzur/etl_sales.log
```

---

## Phase 4: Integration ✓ READY

### Step 9: Update Cost Center (Optional)

You can now join with cost center views:

```sql
-- Extended Cost Center with Daily Sales
SELECT
    cc.pharmacy_name,
    cc.kpi_total_revenue,              -- Monthly from cost center
    sp.today_sales_amount,             -- Daily from new views
    sp.current_month_sales_amount,
    sp.ytd_sales_amount,
    cc.kpi_profit_loss
FROM view_cost_center_pharmacy cc
LEFT JOIN view_sales_per_pharmacy sp ON cc.warehouse_id = sp.warehouse_id;
```

### Step 10: Testing Queries

Run all test queries from `SALES_VIEWS_QUICK_REFERENCE.md`:

- [ ] Today's Sales by Pharmacy
- [ ] Current Month Performance by Branch
- [ ] YTD Sales vs Daily Average
- [ ] Today vs Yesterday Trend
- [ ] Pharmacy Profitability
- [ ] Real-Time Hourly Sales

---

## Phase 5: Monitoring & Maintenance

### Step 11: Setup Monitoring

**Monitor ETL Health (Weekly):**

```sql
-- Check for failed runs
SELECT COUNT(*) as failed_runs
FROM etl_sales_aggregates_log
WHERE status = 'FAILED'
AND aggregate_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY);

-- Alert if > 0
```

**Monitor Data Freshness (Daily):**

```sql
-- Check if today's data is present
SELECT
    COUNT(*) as today_warehouses,
    SUM(today_sales_amount) as today_sales
FROM sma_sales_aggregates
WHERE aggregate_date = CURDATE();
```

### Step 12: Maintenance Schedule

**Weekly:**

- [ ] Verify ETL runs completed successfully
- [ ] Check for any error messages

**Monthly:**

- [ ] Verify aggregate accuracy against raw sales table
- [ ] Check table sizes

**Quarterly:**

- [ ] Review performance metrics
- [ ] Consider archiving old hourly data

---

## Troubleshooting

### Issue: "Table doesn't exist" when running ETL

**Solution:**

- Verify migration 012 ran: `SHOW TABLES LIKE 'sma_sales_aggregates';`
- Re-run: `mysql -u root -p avenzur_pharmacy < 012_create_sales_aggregates_tables.sql`

### Issue: Views showing no data

**Causes:**

1. ETL hasn't run yet → Run `php etl_sales_aggregates.php backfill`
2. No sales data → Check `SELECT COUNT(*) FROM sma_sales;`
3. Dimension tables empty → Check `sma_dim_pharmacy`, `sma_dim_branch`

### Issue: ETL script errors

**Check:**

```bash
php /var/www/avenzur/database/scripts/etl_sales_aggregates.php
# Look for detailed error messages
```

**Common errors:**

- "Connection failed" → Check database credentials
- "Prepare failed" → Check SQL syntax (run migration again)
- "Warehouse not found" → Check `sma_warehouses` table

### Issue: Cron not running

**Check:**

```bash
# Verify crontab entry
crontab -l

# Check cron logs
grep CRON /var/log/syslog | tail -20

# Manually test script
/usr/bin/php /var/www/avenzur/database/scripts/etl_sales_aggregates.php
```

---

## Rollback Plan

If you need to remove this implementation:

```bash
# 1. Remove cron jobs
crontab -e
# Delete ETL lines

# 2. Drop views
mysql -u root -p avenzur_pharmacy -e "
    DROP VIEW IF EXISTS view_sales_per_pharmacy;
    DROP VIEW IF EXISTS view_sales_per_branch;
    DROP VIEW IF EXISTS view_purchases_per_pharmacy;
    DROP VIEW IF EXISTS view_purchases_per_branch;
"

# 3. Drop tables (WARNING: Data loss!)
mysql -u root -p avenzur_pharmacy -e "
    DROP TABLE IF EXISTS sma_sales_aggregates_hourly;
    DROP TABLE IF EXISTS sma_sales_aggregates;
    DROP TABLE IF EXISTS sma_purchases_aggregates;
    DROP TABLE IF EXISTS etl_sales_aggregates_log;
"

# 4. Remove ETL script
rm /var/www/avenzur/database/scripts/etl_sales_aggregates.php
```

---

## Success Criteria

- ✅ Migrations 012 & 013 executed without errors
- ✅ Tables created: `sma_sales_aggregates`, `sma_purchases_aggregates`, `sma_sales_aggregates_hourly`
- ✅ Views created: `view_sales_per_pharmacy`, `view_sales_per_branch`, `view_purchases_per_pharmacy`, `view_purchases_per_branch`
- ✅ ETL script runs successfully with `php etl_sales_aggregates.php`
- ✅ Cron jobs configured and running
- ✅ Data populates after first ETL run
- ✅ All test queries return data
- ✅ Trends show correctly (today vs yesterday)

---

## Timeline

| Phase     | Tasks               | Est. Time       |
| --------- | ------------------- | --------------- |
| Phase 1   | Run 2 migrations    | 5 minutes       |
| Phase 2   | Setup ETL & cron    | 15 minutes      |
| Phase 3   | Verify data         | 10 minutes      |
| Phase 4   | Integration testing | 20 minutes      |
| Phase 5   | Monitoring setup    | 15 minutes      |
| **Total** |                     | **~60 minutes** |

---

## Support

For issues or questions:

1. Check `SALES_VIEWS_IMPLEMENTATION_GUIDE.md` for details
2. Check `SALES_VIEWS_QUICK_REFERENCE.md` for query examples
3. Review troubleshooting section above
4. Check ETL logs: `/var/log/avenzur/etl_sales.log`
