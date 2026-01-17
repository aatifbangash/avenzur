# Cost Center Module - Practical KPI Examples

**Ready-to-Use Examples for Adding Common KPIs**

---

## Example 1: Stock-Out Rate %

**Definition:** Percentage of products that went out of stock during the period

**Use Case:** Identify branches with poor inventory management

---

### Database Changes

```sql
-- Step 1: Add source column to fact table (if tracking stock-outs)
ALTER TABLE fact_cost_center ADD COLUMN stockout_events INT DEFAULT 0;

-- Step 2: Add derived KPI column (GENERATED)
ALTER TABLE fact_cost_center ADD COLUMN kpi_stockout_rate_pct DECIMAL(10,2)
GENERATED ALWAYS AS (
    CASE
        WHEN stockout_events > 0 THEN
            ROUND((stockout_events / 30) * 100, 2)  -- Assume 30-day period
        ELSE 0
    END
) STORED;

-- Step 3: Add index
ALTER TABLE fact_cost_center ADD INDEX idx_stockout_rate (kpi_stockout_rate_pct);

-- Step 4: Update view
CREATE OR REPLACE VIEW view_cost_center_pharmacy AS
SELECT
    -- ... existing columns ...
    ROUND(AVG(f.kpi_stockout_rate_pct), 2) AS kpi_stockout_rate_pct,
    -- ... rest of columns ...
FROM fact_cost_center f
INNER JOIN dim_pharmacy dp ON f.warehouse_id = dp.warehouse_id
GROUP BY dp.pharmacy_id, dp.warehouse_id, ...;
```

### Backend - Model Method

```php
// app/models/admin/Cost_center_model.php

// Already works! Just add to sorting options:
switch ($sort_by) {
    case 'stockout_rate':
        $sort_column = 'kpi_stockout_rate_pct DESC';  // Lower is better
        break;
}
```

### Backend - Helper Functions

```php
// app/helpers/cost_center_helper.php

if (!function_exists('format_stockout_rate')) {
    function format_stockout_rate($rate) {
        return number_format($rate, 1) . '% stockouts';
    }
}

if (!function_exists('get_stockout_status')) {
    function get_stockout_status($rate) {
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
                'text' => '✗ Critical',
                'badge' => 'danger',
                'class' => 'text-danger'
            ];
        }
    }
}

if (!function_exists('get_stockout_advice')) {
    function get_stockout_advice($rate) {
        if ($rate > 5) {
            return 'High stock-out rate. Review reorder points and safety stock levels.';
        } elseif ($rate > 2) {
            return 'Moderate stock-outs. Consider increasing inventory buffers.';
        } else {
            return 'Good inventory management. Maintain current reorder policies.';
        }
    }
}
```

### Frontend - Dashboard View

```php
<!-- app/views/admin/cost_center/cost_center_dashboard.php -->

<!-- Add to KPI Cards Section -->
<div class="col-md-3 mb-3">
    <div class="card border-left-info shadow">
        <div class="card-body">
            <div class="text-info font-weight-bold text-uppercase mb-1">
                <i class="fas fa-exclamation-triangle"></i> Stock-Out Rate
            </div>
            <h3 class="h4 font-weight-bold mb-0">
                <?php
                    $rate = $summary['kpi_stockout_rate_pct'] ?? 0;
                    echo format_stockout_rate($rate);
                ?>
            </h3>
            <?php
                $status = get_stockout_status($rate);
                echo '<div class="mt-2">';
                echo '<small class="' . $status['class'] . '">' . $status['text'] . '</small>';
                echo '<p class="text-muted small mt-2">' . get_stockout_advice($rate) . '</p>';
                echo '</div>';
            ?>
        </div>
    </div>
</div>

<!-- Add to Pharmacy Table -->
<th class="text-right">Stock-Out Rate</th>
<!-- ... in tbody ... -->
<td class="text-right">
    <?php
        $rate = $pharmacy['kpi_stockout_rate_pct'] ?? 0;
        $status = get_stockout_status($rate);
        echo '<span class="badge badge-' . $status['badge'] . '">'
            . format_stockout_rate($rate)
            . '</span>';
    ?>
</td>
```

### Frontend - Chart Addition

```php
<!-- Add to trend chart in dashboard -->
<script>
function initializeTrendChart() {
    const ctx = document.getElementById('trendChart').getContext('2d');

    const chartData = {
        labels: ['Day 1', 'Day 2', 'Day 3', ...], // 30 days
        datasets: [
            // Existing Revenue dataset
            {
                label: 'Revenue (SAR)',
                data: [50000, 55000, 48000, ...],
                borderColor: '#3498db',
                // ... existing config ...
            },
            // NEW: Stock-Out Rate
            {
                label: 'Stock-Out Rate (%)',
                data: [1.2, 1.5, 2.1, ...],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                yAxisID: 'y1'  // Secondary Y-axis
            }
        ],
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Revenue (SAR)' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Stock-Out Rate (%)' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    };

    new Chart(ctx, { type: 'line', data: chartData, options: chartData.options });
}
</script>
```

---

## Example 2: Customer Return Rate %

**Definition:** Percentage of sales returned by customers

**Use Case:** Quality control and customer satisfaction metric

---

### Database Changes

```sql
-- Step 1: Add source columns
ALTER TABLE fact_cost_center ADD COLUMN
    total_sales_items INT DEFAULT 0,      -- Total items sold
    total_returned_items INT DEFAULT 0;   -- Total items returned

-- Step 2: Add derived KPI
ALTER TABLE fact_cost_center ADD COLUMN kpi_return_rate_pct DECIMAL(10,2)
GENERATED ALWAYS AS (
    CASE
        WHEN total_sales_items > 0 THEN
            ROUND((total_returned_items / total_sales_items) * 100, 2)
        ELSE 0
    END
) STORED;

-- Step 3: Update views to include new KPI
CREATE OR REPLACE VIEW view_cost_center_pharmacy AS
SELECT
    -- ... existing columns ...
    ROUND(AVG(f.kpi_return_rate_pct), 2) AS kpi_return_rate_pct,
    SUM(f.total_returned_items) AS returned_items_total,
    -- ... rest of columns ...
```

### Backend - Helper Functions

```php
if (!function_exists('format_return_rate')) {
    function format_return_rate($rate) {
        return number_format($rate, 2) . '% returns';
    }
}

if (!function_exists('get_return_rate_status')) {
    function get_return_rate_status($rate) {
        if ($rate < 2) {
            return [
                'status' => 'success',
                'text' => '✓ Low Returns',
                'class' => 'text-success'
            ];
        } elseif ($rate < 5) {
            return [
                'status' => 'warning',
                'text' => '⚠ Moderate',
                'class' => 'text-warning'
            ];
        } else {
            return [
                'status' => 'danger',
                'text' => '✗ High Returns',
                'class' => 'text-danger'
            ];
        }
    }
}
```

### Frontend - Add to Dashboard

```php
<div class="col-md-3 mb-3">
    <div class="card border-left-danger shadow">
        <div class="card-body">
            <div class="text-danger font-weight-bold text-uppercase mb-1">
                <i class="fas fa-undo"></i> Return Rate
            </div>
            <h3 class="h4 font-weight-bold mb-0">
                <?php echo format_return_rate($summary['kpi_return_rate_pct'] ?? 0); ?>
            </h3>
            <?php
                $status = get_return_rate_status($summary['kpi_return_rate_pct'] ?? 0);
                echo '<small class="' . $status['class'] . '">' . $status['text'] . '</small>';
            ?>
        </div>
    </div>
</div>
```

---

## Example 3: Average Transaction Value

**Definition:** Average value per transaction (revenue / transaction count)

**Use Case:** Customer spending patterns and upselling metrics

---

### Database Changes

```sql
-- Step 1: Add source column
ALTER TABLE fact_cost_center ADD COLUMN total_transactions INT DEFAULT 0;

-- Step 2: Add derived KPI (uses existing revenue column)
ALTER TABLE fact_cost_center ADD COLUMN kpi_avg_transaction_value DECIMAL(18,2)
GENERATED ALWAYS AS (
    CASE
        WHEN total_transactions > 0 THEN
            total_revenue / total_transactions
        ELSE 0
    END
) STORED;

-- Step 3: Update views
CREATE OR REPLACE VIEW view_cost_center_pharmacy AS
SELECT
    -- ... existing columns ...
    ROUND(AVG(f.kpi_avg_transaction_value), 2) AS kpi_avg_transaction_value,
    SUM(f.total_transactions) AS total_transactions_count,
    -- ... rest of columns ...
```

### Backend - Helper Functions

```php
if (!function_exists('format_avg_transaction')) {
    function format_avg_transaction($value) {
        return 'SAR ' . number_format($value, 2);
    }
}

if (!function_exists('get_avg_transaction_status')) {
    function get_avg_transaction_status($value, $benchmark = 100) {
        $variance = (($value - $benchmark) / $benchmark) * 100;

        if ($variance > 10) {
            return [
                'status' => 'success',
                'text' => '✓ Above Target',
                'class' => 'text-success'
            ];
        } elseif ($variance > -5) {
            return [
                'status' => 'warning',
                'text' => '⚠ On Target',
                'class' => 'text-warning'
            ];
        } else {
            return [
                'status' => 'danger',
                'text' => '✗ Below Target',
                'class' => 'text-danger'
            ];
        }
    }
}
```

### Frontend - Dashboard Card

```php
<div class="col-md-3 mb-3">
    <div class="card border-left-success shadow">
        <div class="card-body">
            <div class="text-success font-weight-bold text-uppercase mb-1">
                <i class="fas fa-shopping-cart"></i> Avg Transaction
            </div>
            <h3 class="h4 font-weight-bold mb-0">
                <?php echo format_avg_transaction($summary['kpi_avg_transaction_value'] ?? 0); ?>
            </h3>
            <?php
                $status = get_avg_transaction_status($summary['kpi_avg_transaction_value'] ?? 0, 150);
                echo '<small class="' . $status['class'] . '">' . $status['text'] . '</small>';
            ?>
        </div>
    </div>
</div>
```

---

## Example 4: Same-Day Delivery Rate %

**Definition:** Percentage of orders delivered same day

**Use Case:** Logistics performance and customer satisfaction

---

### Database Changes

```sql
-- Step 1: Add source columns
ALTER TABLE fact_cost_center ADD COLUMN
    orders_same_day INT DEFAULT 0,
    total_orders INT DEFAULT 0;

-- Step 2: Add derived KPI
ALTER TABLE fact_cost_center ADD COLUMN kpi_same_day_delivery_pct DECIMAL(10,2)
GENERATED ALWAYS AS (
    CASE
        WHEN total_orders > 0 THEN
            ROUND((orders_same_day / total_orders) * 100, 2)
        ELSE 0
    END
) STORED;

-- Step 3: Update views
CREATE OR REPLACE VIEW view_cost_center_pharmacy AS
SELECT
    -- ... existing columns ...
    ROUND(AVG(f.kpi_same_day_delivery_pct), 2) AS kpi_same_day_delivery_pct,
    SUM(f.orders_same_day) AS orders_same_day_total,
    SUM(f.total_orders) AS total_orders_count,
    -- ... rest of columns ...
```

---

## Example 5: Profit Margin by Category

**Definition:** Profit margin for each product category

**Use Case:** Identify high/low margin categories

---

### Database Changes

```sql
-- This is more complex - need product_category in fact table

-- Step 1: Add dimension
ALTER TABLE fact_cost_center ADD COLUMN product_category VARCHAR(100);

-- Step 2: Create category-level view
CREATE VIEW view_cost_center_category AS
SELECT
    f.product_category,
    f.period_year,
    f.period_month,
    SUM(f.total_revenue) AS kpi_total_revenue,
    SUM(f.total_cost) AS kpi_total_cost,
    CASE
        WHEN SUM(f.total_revenue) = 0 THEN 0
        ELSE ROUND(((SUM(f.total_revenue) - SUM(f.total_cost)) / SUM(f.total_revenue)) * 100, 2)
    END AS kpi_profit_margin_pct
FROM fact_cost_center f
WHERE f.product_category IS NOT NULL
GROUP BY f.product_category, f.period_year, f.period_month;
```

### Backend - New Model Method

```php
public function get_categories_with_kpis($period = null) {
    $this->db->select('
        product_category,
        kpi_total_revenue,
        kpi_total_cost,
        kpi_profit_margin_pct
    ');
    $this->db->from('view_cost_center_category');

    if ($period) {
        $this->db->where('CONCAT(period_year, "-", LPAD(period_month, 2, "0"))', $period);
    }

    $this->db->order_by('kpi_profit_margin_pct', 'DESC');

    $query = $this->db->get();
    return $query->result_array();
}
```

### Frontend - New View/Chart

```php
<!-- New page: cost_center_categories.php -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Profit Margin by Category</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
function initializeCategoryChart() {
    const categories = <?php echo json_encode($categories); ?>;

    const ctx = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categories.map(c => c.product_category),
            datasets: [{
                label: 'Profit Margin %',
                data: categories.map(c => parseFloat(c.kpi_profit_margin_pct)),
                backgroundColor: categories.map(c =>
                    c.kpi_profit_margin_pct >= 35 ? '#10B981' :
                    c.kpi_profit_margin_pct >= 25 ? '#F59E0B' :
                    '#EF4444'
                )
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
}

initializeCategoryChart();
</script>
```

---

## Example 6: Prescription Fill Time (Minutes)

**Definition:** Average time to fill a prescription from receipt to delivery

**Use Case:** Pharmacy operational efficiency

---

### Database Changes

```sql
-- Add to fact_cost_center
ALTER TABLE fact_cost_center ADD COLUMN
    total_prescriptions INT DEFAULT 0,
    total_fill_time_minutes INT DEFAULT 0;

-- Derived KPI
ALTER TABLE fact_cost_center ADD COLUMN kpi_avg_fill_time_minutes DECIMAL(10,2)
GENERATED ALWAYS AS (
    CASE
        WHEN total_prescriptions > 0 THEN
            total_fill_time_minutes / total_prescriptions
        ELSE 0
    END
) STORED;
```

### Backend - Helper

```php
if (!function_exists('format_fill_time')) {
    function format_fill_time($minutes) {
        if ($minutes < 1) {
            return '< 1 min';
        }
        return number_format($minutes, 0) . ' min';
    }
}

if (!function_exists('get_fill_time_status')) {
    function get_fill_time_status($minutes) {
        // Faster fill time is better
        if ($minutes <= 15) {
            return ['status' => 'success', 'text' => '✓ Excellent'];
        } elseif ($minutes <= 30) {
            return ['status' => 'warning', 'text' => '⚠ Acceptable'];
        } else {
            return ['status' => 'danger', 'text' => '✗ Slow'];
        }
    }
}
```

---

## Adding Multiple KPIs at Once

If you want to add several KPIs together (e.g., for Phase 5), create a single migration:

```php
// app/migrations/005_add_all_new_kpis.php

public function up() {
    // 1. Stock-Out Rate
    $this->db->query("ALTER TABLE fact_cost_center ADD COLUMN stockout_events INT DEFAULT 0");
    $this->db->query("ALTER TABLE fact_cost_center ADD COLUMN kpi_stockout_rate_pct...");

    // 2. Return Rate
    $this->db->query("ALTER TABLE fact_cost_center ADD COLUMN total_returned_items INT...");
    $this->db->query("ALTER TABLE fact_cost_center ADD COLUMN kpi_return_rate_pct...");

    // 3. Avg Transaction
    $this->db->query("ALTER TABLE fact_cost_center ADD COLUMN total_transactions INT...");
    $this->db->query("ALTER TABLE fact_cost_center ADD COLUMN kpi_avg_transaction_value...");

    // 4. Update ALL views once with all new KPIs
    $this->db->query("CREATE OR REPLACE VIEW view_cost_center_pharmacy AS SELECT ... [all new KPIs]");

    log_message('info', '✓ Added 3 new KPIs');
}
```

---

## Troubleshooting

### Issue: "Can't create this table. A trigger was activated before the creation of this new table"

**Solution:** GENERATED columns sometimes conflict with triggers. Drop triggers first:

```sql
DROP TRIGGER IF EXISTS tr_fact_cost_center_before_update;
-- Recreate migration
-- Re-add trigger
```

### Issue: Query too slow after adding KPI

**Solution:** Add composite index:

```sql
ALTER TABLE fact_cost_center ADD INDEX idx_kpi_lookup
(warehouse_id, transaction_date, kpi_new_metric);
```

### Issue: View returns NULL for new KPI

**Solution:** Check NULL handling:

```sql
-- Should use COALESCE
ROUND(AVG(COALESCE(f.kpi_new_metric, 0)), 2) AS kpi_new_metric
```

---

## Quick Reference: KPI Templates

| KPI                     | Formula                                  | Effort      | Usefulness  |
| ----------------------- | ---------------------------------------- | ----------- | ----------- |
| Stock-Out Rate          | (stockout_events / 30) × 100             | ⭐ Low      | ⭐⭐⭐ High |
| Return Rate             | (returned_items / sold_items) × 100      | ⭐ Low      | ⭐⭐⭐ High |
| Avg Transaction Value   | revenue / transaction_count              | ⭐ Low      | ⭐⭐ Medium |
| Same-Day Delivery       | (same_day_orders / total_orders) × 100   | ⭐⭐ Medium | ⭐⭐ Medium |
| Fill Time               | total_fill_minutes / prescription_count  | ⭐⭐ Medium | ⭐⭐⭐ High |
| Category Margin         | category_profit / category_revenue × 100 | ⭐⭐ Medium | ⭐⭐ Medium |
| Inventory Turnover      | COGS / avg_inventory                     | ⭐⭐ Medium | ⭐⭐⭐ High |
| Customer Lifetime Value | total_customer_spend / customer_visits   | ⭐⭐⭐ High | ⭐⭐⭐ High |

---

## Next Steps

1. **Choose KPI:** Pick one from examples above
2. **Create Migration:** Copy template from `004_add_new_kpi_template.php`
3. **Run Migration:** Execute migration via web interface
4. **Add Helpers:** Create formatting functions
5. **Update Dashboard:** Add card or table column
6. **Test:** Check dashboard displays new KPI
7. **Monitor:** Watch performance for 7 days
8. **Iterate:** Add more KPIs as needed

---

**Ready to add your first KPI? Start with Stock-Out Rate - it's the simplest!**
