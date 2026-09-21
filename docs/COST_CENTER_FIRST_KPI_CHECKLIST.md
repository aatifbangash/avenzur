# Cost Center Module - First KPI Addition Checklist

**Complete Walkthrough: Adding Your First New KPI (Stock-Out Rate %)**

This guide walks you through adding a new KPI step-by-step with copy-paste code.

---

## Prerequisite Check

- [ ] Access to phpMyAdmin or database client
- [ ] CodeIgniter admin dashboard access
- [ ] Text editor (VS Code, Sublime, etc.)
- [ ] 30-50 minutes of focused time

---

## Step 1: Create Migration File

### 1.1 Create File

**Path:** `/app/migrations/005_add_stockout_rate_kpi.php`

**Copy this entire file:**

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_stockout_rate_kpi extends CI_Migration {

    public function up() {
        // ============================================================
        // Step 1: Add source column to fact_cost_center
        // ============================================================

        log_message('info', '[Migration] Adding stockout_events column...');

        $this->db->query("
            ALTER TABLE `fact_cost_center` ADD COLUMN `stockout_events` INT DEFAULT 0
            COMMENT 'Number of stock-out events for the period'
        ");

        // ============================================================
        // Step 2: Add GENERATED column for KPI calculation
        // ============================================================

        log_message('info', '[Migration] Adding kpi_stockout_rate_pct GENERATED column...');

        $this->db->query("
            ALTER TABLE `fact_cost_center` ADD COLUMN `kpi_stockout_rate_pct` DECIMAL(10,2)
            GENERATED ALWAYS AS (
                CASE
                    WHEN stockout_events > 0 THEN
                        ROUND((stockout_events / 30) * 100, 2)
                    ELSE 0
                END
            ) STORED
            COMMENT 'Stock-out rate percentage (auto-calculated)'
        ");

        // ============================================================
        // Step 3: Add index for performance
        // ============================================================

        log_message('info', '[Migration] Adding index...');

        $this->db->query("
            ALTER TABLE `fact_cost_center`
            ADD INDEX `idx_stockout_rate` (`kpi_stockout_rate_pct`)
        ");

        // ============================================================
        // Step 4: Update view_cost_center_pharmacy
        // ============================================================

        log_message('info', '[Migration] Updating view_cost_center_pharmacy...');

        $this->db->query("
            CREATE OR REPLACE VIEW `view_cost_center_pharmacy` AS
            SELECT
                dp.pharmacy_id,
                dp.warehouse_id,
                dp.pharmacy_name,
                dp.pharmacy_code,
                f.period_year,
                f.period_month,
                CONCAT(f.period_year, '-', LPAD(f.period_month, 2, '0')) AS period,

                -- Existing KPIs
                COALESCE(SUM(f.total_revenue), 0) AS kpi_total_revenue,
                COALESCE(SUM(f.total_cogs), 0) AS kpi_cogs,
                COALESCE(SUM(f.inventory_movement_cost), 0) AS kpi_inventory_movement,
                COALESCE(SUM(f.operational_cost), 0) AS kpi_operational,
                COALESCE(SUM(f.total_cost), 0) AS kpi_total_cost,
                COALESCE(SUM(f.total_revenue) - SUM(f.total_cost), 0) AS kpi_profit_loss,
                CASE
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND(((SUM(f.total_revenue) - SUM(f.total_cost)) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                CASE
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND((SUM(f.total_cost) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_cost_ratio_pct,

                -- NEW KPI: Stock-Out Rate
                ROUND(AVG(f.kpi_stockout_rate_pct), 2) AS kpi_stockout_rate_pct,

                -- Additional metrics
                (SELECT COUNT(*) FROM dim_branch WHERE pharmacy_id = dp.pharmacy_id) AS branch_count,
                COUNT(DISTINCT f.transaction_date) AS days_active,
                MAX(f.updated_at) AS last_updated

            FROM fact_cost_center f
            INNER JOIN dim_pharmacy dp ON f.warehouse_id = dp.warehouse_id
            WHERE f.warehouse_type IN ('pharmacy', 'mainwarehouse')
            GROUP BY dp.pharmacy_id, dp.warehouse_id, dp.pharmacy_name, dp.pharmacy_code,
                     f.period_year, f.period_month
            ORDER BY f.period_year DESC, f.period_month DESC
        ");

        // ============================================================
        // Step 5: Update view_cost_center_branch
        // ============================================================

        log_message('info', '[Migration] Updating view_cost_center_branch...');

        $this->db->query("
            CREATE OR REPLACE VIEW `view_cost_center_branch` AS
            SELECT
                db.branch_id,
                db.warehouse_id,
                db.branch_name,
                db.branch_code,
                dp.pharmacy_id,
                dp.pharmacy_name,
                f.period_year,
                f.period_month,
                CONCAT(f.period_year, '-', LPAD(f.period_month, 2, '0')) AS period,

                -- Existing KPIs
                COALESCE(SUM(f.total_revenue), 0) AS kpi_total_revenue,
                COALESCE(SUM(f.total_cogs), 0) AS kpi_cogs,
                COALESCE(SUM(f.inventory_movement_cost), 0) AS kpi_inventory_movement,
                COALESCE(SUM(f.operational_cost), 0) AS kpi_operational,
                COALESCE(SUM(f.total_cost), 0) AS kpi_total_cost,
                COALESCE(SUM(f.total_revenue) - SUM(f.total_cost), 0) AS kpi_profit_loss,
                CASE
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND(((SUM(f.total_revenue) - SUM(f.total_cost)) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                CASE
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND((SUM(f.total_cost) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_cost_ratio_pct,

                -- NEW KPI: Stock-Out Rate
                ROUND(AVG(f.kpi_stockout_rate_pct), 2) AS kpi_stockout_rate_pct,

                -- Additional metrics
                COUNT(DISTINCT f.transaction_date) AS days_active,
                MAX(f.updated_at) AS last_updated

            FROM fact_cost_center f
            INNER JOIN dim_branch db ON f.warehouse_id = db.warehouse_id
            INNER JOIN dim_pharmacy dp ON db.pharmacy_id = dp.pharmacy_id
            WHERE f.warehouse_type = 'branch'
            GROUP BY db.branch_id, db.warehouse_id, db.branch_name, db.branch_code,
                     dp.pharmacy_id, dp.pharmacy_name, f.period_year, f.period_month
            ORDER BY f.period_year DESC, f.period_month DESC
        ");

        // ============================================================
        // Step 6: Update view_cost_center_summary
        // ============================================================

        log_message('info', '[Migration] Updating view_cost_center_summary...');

        $this->db->query("
            CREATE OR REPLACE VIEW `view_cost_center_summary` AS
            SELECT
                'COMPANY' AS hierarchy_level,
                'COMPANY' AS hierarchy_id,
                'Company' AS hierarchy_name,
                NULL AS parent_id,
                f.period_year,
                f.period_month,
                CONCAT(f.period_year, '-', LPAD(f.period_month, 2, '0')) AS period,

                -- Existing KPIs
                COALESCE(SUM(f.total_revenue), 0) AS kpi_total_revenue,
                COALESCE(SUM(f.total_cost), 0) AS kpi_total_cost,
                COALESCE(SUM(f.total_revenue) - SUM(f.total_cost), 0) AS kpi_profit_loss,
                CASE
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND(((SUM(f.total_revenue) - SUM(f.total_cost)) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                CASE
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND((SUM(f.total_cost) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_cost_ratio_pct,

                -- NEW KPI: Stock-Out Rate
                ROUND(AVG(f.kpi_stockout_rate_pct), 2) AS kpi_stockout_rate_pct,

                COUNT(DISTINCT f.warehouse_id) AS entity_count,
                MAX(f.updated_at) AS last_updated

            FROM fact_cost_center f
            GROUP BY f.period_year, f.period_month
            ORDER BY f.period_year DESC, f.period_month DESC
        ");

        // ============================================================
        // Step 7: Log success
        // ============================================================

        log_message('info', '✓ Migration complete: Added Stock-Out Rate KPI');
    }

    public function down() {
        log_message('info', '[Migration] Rolling back Stock-Out Rate KPI...');

        // Drop GENERATED column
        $this->db->query("ALTER TABLE `fact_cost_center` DROP COLUMN `kpi_stockout_rate_pct`");

        // Drop source column
        $this->db->query("ALTER TABLE `fact_cost_center` DROP COLUMN `stockout_events`");

        // Drop index
        $this->db->query("ALTER TABLE `fact_cost_center` DROP INDEX `idx_stockout_rate`");

        // Restore previous view versions (simplified - see migration 002 for full definition)
        // In production, you'd restore the exact previous view definition

        log_message('info', '↺ Migration rolled back: Stock-Out Rate KPI removed');
    }
}
?>
```

### 1.2 Verify File

- [ ] File saved as: `/app/migrations/005_add_stockout_rate_kpi.php`
- [ ] No syntax errors (check opening/closing braces)

---

## Step 2: Add Helper Functions

### 2.1 Open File

**Path:** `/app/helpers/cost_center_helper.php`

### 2.2 Add Functions at End of File

```php
// ============================================================
// Stock-Out Rate KPI Functions
// ============================================================

if (!function_exists('format_stockout_rate')) {
    /**
     * Format stock-out rate for display
     *
     * @param float $rate The stock-out rate percentage
     * @return string Formatted string like "2.5% stockouts"
     */
    function format_stockout_rate($rate) {
        if (is_null($rate) || $rate === '') {
            return 'N/A';
        }
        return number_format($rate, 1) . '% stockouts';
    }
}

if (!function_exists('get_stockout_status')) {
    /**
     * Get status badge for stock-out rate
     *
     * Lower rates are better (fewer stock-outs)
     * - 0-2%: Excellent (green)
     * - 2-5%: Acceptable (yellow)
     * - >5%: High (red)
     *
     * @param float $rate The stock-out rate percentage
     * @return array Status info: ['status' => 'success|warning|danger', 'text' => '...', 'class' => '...']
     */
    function get_stockout_status($rate) {
        if (is_null($rate) || $rate === '') {
            return [
                'status' => 'info',
                'text' => 'No Data',
                'badge' => 'info',
                'class' => 'text-info'
            ];
        }

        if ($rate <= 2) {
            return [
                'status' => 'success',
                'text' => '✓ Excellent',
                'badge' => 'success',
                'class' => 'text-success'
            ];
        } elseif ($rate <= 5) {
            return [
                'status' => 'warning',
                'text' => '⚠ Acceptable',
                'badge' => 'warning',
                'class' => 'text-warning'
            ];
        } else {
            return [
                'status' => 'danger',
                'text' => '✗ High',
                'badge' => 'danger',
                'class' => 'text-danger'
            ];
        }
    }
}

if (!function_exists('get_stockout_advice')) {
    /**
     * Get actionable advice based on stock-out rate
     *
     * @param float $rate The stock-out rate percentage
     * @return string Advice message
     */
    function get_stockout_advice($rate) {
        if ($rate > 5) {
            return 'High stock-out rate detected. Review reorder points and increase safety stock levels.';
        } elseif ($rate > 2) {
            return 'Moderate stock-outs. Consider increasing inventory buffers for popular items.';
        } else {
            return 'Excellent inventory management. Maintain current reorder policies.';
        }
    }
}
?>
```

### 2.2 Verify

- [ ] Functions added at end of file (before closing `?>`)
- [ ] No syntax errors
- [ ] Helper file saved

---

## Step 3: Update Dashboard View

### 3.1 Open File

**Path:** `/app/views/admin/cost_center/cost_center_dashboard.php`

### 3.2 Find KPI Cards Section

Look for the section with existing KPI cards (contains "kpi_total_revenue", "kpi_total_cost", etc.)

```php
<!-- Find this section -->
<div class="row">
    <div class="col-md-3 mb-3">
        <!-- Revenue Card -->
    </div>
    <div class="col-md-3 mb-3">
        <!-- Cost Card -->
    </div>
    <div class="col-md-3 mb-3">
        <!-- Profit Card -->
    </div>
    <div class="col-md-3 mb-3">
        <!-- Margin Card -->
    </div>
</div>
```

### 3.3 Add New Card After Last Card

```php
<!-- NEW: Stock-Out Rate Card -->
<div class="col-md-3 mb-3">
    <div class="card border-left-info shadow h-100 py-2">
        <div class="card-body">
            <div class="text-info font-weight-bold text-uppercase mb-1">
                <i class="fas fa-exclamation-triangle"></i> Stock-Out Rate
            </div>
            <h3 class="h4 font-weight-bold mb-0">
                <?php
                    $rate = isset($summary['kpi_stockout_rate_pct']) ? $summary['kpi_stockout_rate_pct'] : 0;
                    echo format_stockout_rate($rate);
                ?>
            </h3>
            <div class="mt-2">
                <?php
                    $status = get_stockout_status($rate);
                    echo '<small class="' . $status['class'] . '">' . $status['text'] . '</small>';
                    echo '<p class="text-muted small mt-2">' . get_stockout_advice($rate) . '</p>';
                ?>
            </div>
        </div>
    </div>
</div>
```

### 3.4 Verify

- [ ] Code added to dashboard view
- [ ] Proper indentation matches existing cards
- [ ] File saved

---

## Step 4: Update Pharmacy Table (Optional)

### 4.1 Open File

**Path:** `/app/views/admin/cost_center/cost_center_pharmacy.php`

### 4.2 Find Table Header

Look for the table header section:

```php
<table class="table">
    <thead>
        <tr>
            <th>Pharmacy Name</th>
            <th>Code</th>
            <th class="text-right">Revenue</th>
            <th class="text-right">Cost</th>
            <th class="text-right">Profit</th>
            <th class="text-right">Margin %</th>
        </tr>
    </thead>
```

### 4.3 Add Column Header

```php
<th class="text-right">Stock-Out %</th>
```

### 4.4 Find Table Body

```php
<tbody>
    <?php foreach ($branches as $branch): ?>
    <tr>
        <td><?php echo $branch['branch_name']; ?></td>
        <!-- ... other columns ... -->
    </tr>
    <?php endforeach; ?>
</tbody>
```

### 4.5 Add Column Data

```php
<td class="text-right">
    <?php
        $rate = isset($branch['kpi_stockout_rate_pct']) ? $branch['kpi_stockout_rate_pct'] : 0;
        $status = get_stockout_status($rate);
        echo '<span class="badge badge-' . $status['badge'] . '">'
            . format_stockout_rate($rate)
            . '</span>';
    ?>
</td>
```

---

## Step 5: Run Migration

### 5.1 Via Web Interface (Recommended)

1. **Navigate to:** `http://your-domain/admin/migrate`
2. **Click:** "Run Migrations" button
3. **Check:** For success message "✓ Migration complete"
4. **Look for:** Log line with "Added Stock-Out Rate KPI"

### 5.2 Via Command Line (Alternative)

```bash
cd /path/to/project
php index.php migrate
```

### 5.3 Verify Success

Check logs:

```bash
tail -f app/logs/log_*.log | grep "Stock-Out"
```

Expected output:

```
✓ Migration complete: Added Stock-Out Rate KPI
```

---

## Step 6: Verify Database Changes

### 6.1 Check Column Added

In phpMyAdmin or SQL client:

```sql
-- Should show new column
DESCRIBE fact_cost_center;

-- Look for: kpi_stockout_rate_pct DECIMAL(10,2)
-- Look for: stockout_events INT
```

### 6.2 Check View Updated

```sql
-- Should include new KPI
SHOW CREATE VIEW view_cost_center_pharmacy;

-- Look for: kpi_stockout_rate_pct
```

### 6.3 Check Index Added

```sql
-- Should show index
SHOW INDEXES FROM fact_cost_center;

-- Look for: idx_stockout_rate
```

---

## Step 7: Test Dashboard

### 7.1 Navigate to Dashboard

`http://your-domain/admin/cost_center/dashboard`

### 7.2 Check for New Card

Look for new **Stock-Out Rate** card:

- [ ] Card appears in top row
- [ ] Shows percentage value
- [ ] Shows status badge (Excellent/Acceptable/High)
- [ ] Shows advice text

### 7.3 Check for Console Errors

- [ ] Open browser console (F12)
- [ ] No red error messages
- [ ] Check "Network" tab: All requests return 200 status

---

## Step 8: Populate Test Data (Optional)

To see the KPI display actual values (not zeros):

### 8.1 Via phpMyAdmin

```sql
-- Add test stock-out events
UPDATE fact_cost_center
SET stockout_events = 2
WHERE warehouse_type = 'pharmacy'
LIMIT 10;

-- View should auto-calculate kpi_stockout_rate_pct
SELECT warehouse_id, stockout_events, kpi_stockout_rate_pct
FROM fact_cost_center
WHERE stockout_events > 0
LIMIT 5;
```

### 8.2 Refresh Dashboard

Refresh browser: `http://your-domain/admin/cost_center/dashboard`

Stock-Out Rate should now show values (e.g., 2.5%)

---

## Troubleshooting

### Issue: Migration Fails

**Error:** "Can't create this table"

**Solution:**

1. Check migration has no syntax errors
2. Verify MySQL version supports GENERATED columns (MySQL 5.7+)
3. Try rolling back: `php index.php migrate 4`

### Issue: New KPI Shows NULL

**Error:** Card shows "N/A"

**Solution:**

1. Check data exists: `SELECT * FROM fact_cost_center LIMIT 1;`
2. Check view runs: `SELECT * FROM view_cost_center_pharmacy LIMIT 1;`
3. Check helper function: Verify `format_stockout_rate()` exists

### Issue: Card Not Appearing

**Error:** Stock-Out Rate card not visible

**Solution:**

1. Check HTML was added to dashboard view
2. Verify no syntax errors in view file
3. Clear browser cache (Ctrl+Shift+Delete)
4. Refresh page

### Issue: Console Errors

**Error:** JavaScript errors in browser console

**Solution:**

1. Check helper function is loaded (view has: `<?php load_helper('cost_center'); ?>`)
2. Check variable names match: `$summary['kpi_stockout_rate_pct']`
3. Check quotes are matching: `'kpi_stockout_rate_pct'` not `kpi_stockout_rate_pct`

---

## Success Checklist

- [ ] Migration file created at `/app/migrations/005_add_stockout_rate_kpi.php`
- [ ] Helper functions added to `/app/helpers/cost_center_helper.php`
- [ ] Dashboard card added to `/app/views/admin/cost_center/cost_center_dashboard.php`
- [ ] (Optional) Table column added to `/app/views/admin/cost_center/cost_center_pharmacy.php`
- [ ] Migration executed successfully
- [ ] Database shows new columns and view
- [ ] Dashboard displays new KPI card
- [ ] No console errors
- [ ] Browser shows Stock-Out Rate percentage

---

## What's Next?

### Try Another KPI

Now that you've added Stock-Out Rate, try adding:

1. **Return Rate %** (Similar approach, very easy)
2. **Discount Rate %** (Follow same pattern)
3. **Inventory Turnover Ratio** (Slightly more complex)

See `COST_CENTER_KPI_PRACTICAL_EXAMPLES.md` for complete examples.

### Monitor Performance

After adding KPI:

1. Check dashboard load time (should be < 2s)
2. Monitor database logs for slow queries
3. Add more data to fact_cost_center (1 year of history)
4. Verify performance stays sub-second

### Add Sorting Support (Advanced)

To add sorting by Stock-Out Rate in the controller:

```php
// In app/controllers/admin/Cost_center.php
// Add to the switch statement in ajax_pharmacy_kpis():

case 'stockout_rate':
    $sort_column = 'kpi_stockout_rate_pct DESC';
    break;
```

---

## Support

If you get stuck:

1. **Check this file** for troubleshooting section
2. **Review COST_CENTER_KPI_EXTENSIBILITY.md** for detailed explanations
3. **Check database logs** for SQL errors
4. **Check application logs** for PHP errors

---

**Congratulations! You've successfully added your first new KPI! 🎉**

Next KPI will be even faster. The system is designed for this!
