# Cost Center Module - Deployment Guide

**Complete deployment instructions for the Cost Center module**

## Pre-Deployment Checklist

- [ ] All migrations created and ready
- [ ] API controller tested locally
- [ ] Views tested in browser
- [ ] Database backups created
- [ ] Staging environment prepared
- [ ] Team notified of deployment

## Phase 1: Database Deployment (5-10 minutes)

### Step 1.1: Backup Database

```bash
# Using mysqldump
mysqldump -u root -p pharmacy_db > pharmacy_db_backup_$(date +%Y%m%d_%H%M%S).sql

# Or using your hosting control panel
# Download database backup before proceeding
```

### Step 1.2: Create Database Tables (Dimensions)

Navigate to your database management tool (phpMyAdmin, MySQL Workbench, etc.) and execute:

**File:** `app/migrations/001_create_cost_center_dimensions.php`

**Extract and run the SQL:**

```sql
-- Creates dim_pharmacy, dim_branch, dim_date tables
-- Create indexes for performance
-- Estimated time: < 1 minute
-- Safe: Uses CREATE TABLE IF NOT EXISTS
```

**Verification:**

```sql
-- Verify tables created
SHOW TABLES LIKE 'dim_%';
-- Expected: dim_pharmacy, dim_branch, dim_date

-- Check row counts
SELECT COUNT(*) FROM dim_pharmacy;
SELECT COUNT(*) FROM dim_branch;
SELECT COUNT(*) FROM dim_date;
```

### Step 1.3: Create Fact Table and Views

**File:** `app/migrations/002_create_fact_cost_center.php`

**Extract and run the SQL:**

```sql
-- Creates fact_cost_center table with:
--   - Daily aggregated revenue, cost, profit
--   - Warehouse hierarchy information
--   - Performance indexes
-- Creates 3 views:
--   - view_cost_center_pharmacy
--   - view_cost_center_branch
--   - view_cost_center_summary
-- Estimated time: 1-2 minutes
```

**Verification:**

```sql
-- Verify table and views created
SHOW TABLES LIKE 'fact_cost_center';
SHOW TABLES LIKE 'view_cost_center%';

-- Test view query
SELECT * FROM view_cost_center_pharmacy LIMIT 1;
SELECT * FROM view_cost_center_branch LIMIT 1;
```

### Step 1.4: Create ETL Infrastructure

**File:** `app/migrations/003_create_etl_pipeline.php`

**Extract and run the SQL:**

```sql
-- Creates etl_audit_log table
-- Creates stored procedures:
--   - sp_populate_fact_cost_center
--   - sp_backfill_fact_cost_center
-- Creates performance indexes
-- Estimated time: 1 minute
```

**Verification:**

```sql
-- Verify stored procedures
SHOW PROCEDURE STATUS LIKE 'sp_%cost_center%';

-- Check etl_audit_log table
SELECT * FROM etl_audit_log ORDER BY start_time DESC LIMIT 1;
```

### Step 1.5: Populate Historical Data

**Important:** Do this BEFORE going live!

```bash
# Navigate to project directory
cd /path/to/avenzur

# Run backfill ETL for 9 months of data
php database/scripts/etl_cost_center.php backfill 2025-01-01 2025-10-25

# Watch for SUCCESS message:
# "ETL Completed - X rows inserted"
```

**Verify ETL completed:**

```sql
-- Check data was inserted
SELECT COUNT(*) FROM fact_cost_center;
-- Expected: > 0 rows

-- Check latest ETL run
SELECT * FROM etl_audit_log ORDER BY start_time DESC LIMIT 1;
-- Expected: status = 'SUCCESS'

-- Verify view data
SELECT COUNT(*) FROM view_cost_center_pharmacy;
SELECT SUM(kpi_total_revenue) FROM view_cost_center_pharmacy;
```

## Phase 2: Code Deployment (5 minutes)

### Step 2.1: Copy Files to Server

```bash
# Copy controller
scp app/controllers/admin/Cost_center.php user@server:/path/to/app/controllers/admin/

# Copy views
scp -r themes/default/views/admin/cost_center/ user@server:/path/to/themes/default/views/admin/

# Copy helper
scp app/helpers/cost_center_helper.php user@server:/path/to/app/helpers/

# Or if local:
cp app/controllers/admin/Cost_center.php /var/www/html/app/controllers/admin/
cp -r themes/default/views/admin/cost_center/ /var/www/html/themes/default/views/admin/
cp app/helpers/cost_center_helper.php /var/www/html/app/helpers/
```

### Step 2.2: Update CodeIgniter Configuration

**File:** `app/config/autoload.php`

Add to helpers array:

```php
$autoload['helpers'] = array(..., 'cost_center_helper');
```

**File:** `app/config/routes.php`

Add routes:

```php
// Cost Center Routes
$route['admin/cost_center/dashboard'] = 'admin/cost_center/dashboard';
$route['admin/cost_center/pharmacy/(:num)'] = 'admin/cost_center/pharmacy/$1';
$route['admin/cost_center/branch/(:num)'] = 'admin/cost_center/branch/$1';
$route['admin/cost_center/get_pharmacies'] = 'admin/cost_center/get_pharmacies';
$route['admin/cost_center/get_timeseries'] = 'admin/cost_center/get_timeseries';
```

### Step 2.3: Verify Chart.js Dependency

Check that Chart.js is available in your project:

**Location:** `assets/js/plugins/chart.min.js`

If missing, download from CDN:

```bash
cd assets/js/plugins/
curl -O https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js
# Or
wget https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js
```

Or include from CDN in base template:

```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
```

### Step 2.4: Clear Application Cache

```bash
# Clear CodeIgniter cache
rm -rf app/cache/*

# Clear browser cache (user-side, no action needed)
```

## Phase 3: Configuration Deployment (5-10 minutes)

### Step 3.1: Add Menu Item

Update your admin menu configuration to include Cost Center link:

**Example location:** `app/config/admin_menu.php` or similar

```php
[
    'label' => 'Cost Center',
    'url' => site_url('admin/cost_center/dashboard'),
    'icon' => 'fas fa-chart-pie',
    'permission' => 'view_financials',
    'submenu' => [
        [
            'label' => 'Dashboard',
            'url' => site_url('admin/cost_center/dashboard'),
            'permission' => 'view_financials'
        ],
    ]
]
```

### Step 3.2: Update .htaccess (if needed)

Ensure your URL rewriting allows new routes:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

### Step 3.3: Set File Permissions

```bash
# Ensure proper permissions
chmod 755 app/controllers/admin/Cost_center.php
chmod 755 app/helpers/cost_center_helper.php
chmod 755 app/models/admin/Cost_center_model.php
chmod -R 755 themes/default/views/admin/cost_center/
chmod 755 database/scripts/etl_cost_center.php
```

## Phase 4: ETL Cron Job Setup (5 minutes)

### Step 4.1: Create Cron Job for Daily ETL

```bash
# Edit crontab
crontab -e

# Add line for daily ETL at 2 AM
0 2 * * * /usr/bin/php /path/to/database/scripts/etl_cost_center.php today >> /var/log/cost_center_etl.log 2>&1

# For weekly backfill (Sunday 3 AM) - optional
0 3 * * 0 /usr/bin/php /path/to/database/scripts/etl_cost_center.php backfill $(date -d '7 days ago' +%Y-%m-%d) $(date +%Y-%m-%d) >> /var/log/cost_center_etl.log 2>&1
```

### Step 4.2: Verify Cron Setup

```bash
# List active cron jobs
crontab -l

# Check log file exists
tail -f /var/log/cost_center_etl.log

# Manually test ETL runs correctly
php /path/to/database/scripts/etl_cost_center.php today
# Expected output: "ETL Completed - X rows inserted"
```

## Phase 5: Testing & Validation (10-15 minutes)

### Step 5.1: Smoke Test

1. **Open Dashboard**

   ```
   http://your-domain/admin/cost_center/dashboard
   ```

   Expected: Dashboard loads without errors, shows KPI cards

2. **Check KPI Cards**

   - Verify 4 KPI cards display (Revenue, Cost, Profit, Margin %)
   - Check numbers are formatted correctly (comma separator)
   - Verify colors are correct (Primary, Danger, Success, Warning)

3. **Check Period Selector**

   - Click period dropdown
   - Should show last 24 months
   - Select different period
   - Dashboard should refresh with new data

4. **Check Pharmacy Table**

   - Verify columns are visible (Name, Revenue, Cost, Profit, Margin, Branches, Action)
   - Verify data rows display
   - Click sorting buttons (By Revenue, By Profit)
   - Click View button → Navigate to pharmacy detail

5. **Check Charts**
   - Trend chart should render
   - Hover tooltip should show data
   - No JavaScript errors in console

### Step 5.2: Drill-Down Test

1. **Navigate to Pharmacy Detail**

   ```
   http://your-domain/admin/cost_center/pharmacy/1?period=2025-10
   ```

   Expected: Pharmacy metrics and branch list display

2. **Verify Pharmacy Data**

   - Breadcrumb shows correct path
   - Metrics display correctly
   - Branch table shows all branches
   - Back button returns to dashboard

3. **Navigate to Branch Detail**

   - Click branch in table → Navigate to detail
   - Expected: Branch metrics, cost breakdown chart, trend chart

4. **Verify Branch Data**
   - Cost breakdown chart displays
   - 12-month trend chart shows data
   - Cost categories table shows breakdown
   - Back buttons work

### Step 5.3: Data Validation

```sql
-- Verify data accuracy

-- Check total revenue in view matches aggregated sales
SELECT SUM(kpi_total_revenue) FROM view_cost_center_pharmacy;
-- Compare with: SELECT SUM(grand_total) FROM sma_sale WHERE status='completed';

-- Check profit calculation
-- For each row: profit = revenue - cost (should be true)

-- Check branch totals sum to pharmacy total
-- For each pharmacy: SUM(branch_profits) should equal pharmacy_profit
```

### Step 5.4: Performance Test

Use browser DevTools Lighthouse:

1. Open Dashboard
2. Press F12 → Lighthouse tab
3. Run audit
4. Target metrics:
   - First Contentful Paint: < 2s
   - Largest Contentful Paint: < 3s
   - Cumulative Layout Shift: < 0.1

## Phase 6: Production Deployment (5 minutes)

### Step 6.1: Schedule Deployment Window

- Choose low-traffic time (off-hours)
- Notify all users of temporary unavailability
- Have rollback plan ready

### Step 6.2: Execute Deployment

1. Backup production database (again)
2. Copy all files to production
3. Run database migrations
4. Populate ETL data
5. Update configuration files
6. Clear caches
7. Verify access

### Step 6.3: Post-Deployment Verification

```bash
# Check error logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
tail -f app/logs/*.log

# Monitor for errors
# No 404, 500, or permission errors should appear
```

### Step 6.4: Notify Team

Send communication to stakeholders:

- Cost Center module is live
- URL: http://your-domain/admin/cost_center/dashboard
- Documentation: /docs/COST_CENTER_IMPLEMENTATION.md
- Support contact for issues

## Phase 7: Rollback Plan

If issues occur, rollback is straightforward:

### Rollback Steps

1. **Restore Database**

   ```bash
   mysql -u root -p pharmacy_db < pharmacy_db_backup_20251025_143000.sql
   ```

2. **Restore Code**

   ```bash
   git checkout HEAD -- app/controllers/admin/Cost_center.php
   git checkout HEAD -- themes/default/views/admin/cost_center/
   git checkout HEAD -- app/helpers/cost_center_helper.php
   ```

3. **Remove Menu Item**

   - Edit admin menu configuration
   - Remove Cost Center entry

4. **Clear Cache**

   ```bash
   rm -rf app/cache/*
   ```

5. **Verify**
   - Confirm admin interface loads
   - No errors in logs

## Monitoring After Deployment

### Daily Checks

- Monitor error logs for exceptions
- Check ETL logs for successful runs
- Verify data accuracy with spot checks

### Weekly Checks

- Performance review (response times)
- User feedback collection
- Data accuracy audit

### Monthly Checks

- Database maintenance (analyze, optimize)
- Backup verification
- Feature request compilation

## Documentation Locations

- **Architecture:** `/docs/COST_CENTER_IMPLEMENTATION.md`
- **Phase 3 Complete:** `/docs/COST_CENTER_PHASE3_COMPLETE.md`
- **This Guide:** `/docs/COST_CENTER_DEPLOYMENT.md`
- **API Docs:** `/docs/COST_CENTER_API.md` (if created)

## Support Contacts

| Role      | Contact | Escalation |
| --------- | ------- | ---------- |
| Developer | [Name]  | [Email]    |
| DevOps    | [Name]  | [Email]    |
| Finance   | [Name]  | [Email]    |

## Deployment Checklist - Final Review

**Database:**

- [ ] Backup created
- [ ] All migrations executed successfully
- [ ] Stored procedures installed
- [ ] Historical data populated via ETL
- [ ] Views accessible and returning data

**Code:**

- [ ] All files copied to server
- [ ] Configuration updated
- [ ] Routes configured
- [ ] Helper loaded
- [ ] Chart.js available

**Configuration:**

- [ ] Menu item added
- [ ] File permissions set correctly
- [ ] Cron job configured
- [ ] Error logs monitored

**Testing:**

- [ ] Dashboard loads without errors
- [ ] Data displays correctly
- [ ] Charts render
- [ ] Drill-down navigation works
- [ ] Period selector functional
- [ ] No JavaScript errors

**Production:**

- [ ] Go-live approval obtained
- [ ] Team notified
- [ ] Rollback plan ready
- [ ] Monitoring active

---

## Estimated Timeline

| Phase             | Duration       | Status |
| ----------------- | -------------- | ------ |
| Database          | 10 min         |        |
| Code              | 5 min          |        |
| Configuration     | 10 min         |        |
| ETL Setup         | 5 min          |        |
| Testing           | 15 min         |        |
| Production Deploy | 5 min          |        |
| Monitoring        | Ongoing        |        |
| **TOTAL**         | **50 minutes** |        |

## Common Issues & Solutions

**Issue:** "Cannot find module 'chart.js'"

- **Solution:** Verify Chart.js is in assets/js/plugins/ or loaded from CDN

**Issue:** Views return 404

- **Solution:** Verify routes configured in config/routes.php

**Issue:** No data in dashboard

- **Solution:** Verify ETL ran successfully: `SELECT * FROM etl_audit_log ORDER BY start_time DESC LIMIT 1;`

**Issue:** Dashboard loads slowly\*\*

- **Solution:** Check database indexes exist: `SHOW INDEX FROM fact_cost_center;`

**Issue:** Permission denied errors

- **Solution:** Verify file permissions: `chmod 755 /path/to/file`

---

**Deployment Guide Version 1.0**
**Created: 2025-10-25**
**Status: Ready for Production**

---

Next Steps After Successful Deployment:

1. Gather user feedback
2. Monitor performance metrics
3. Plan Phase 4 (Integration Testing) for quality assurance
4. Prepare Phase 5 (Performance Optimization) based on production data
