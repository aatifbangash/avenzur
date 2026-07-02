# Cost Center Module - Developer Quick Reference

**Fast reference for developers adding features or KPIs**

---

## File Locations

```
Database:    app/migrations/
Backend:     app/models/admin/Cost_center_model.php
Backend:     app/controllers/admin/Cost_center.php
Frontend:    app/views/admin/cost_center/*.php
Helpers:     app/helpers/cost_center_helper.php
ETL:         database/scripts/etl_cost_center.php
Docs:        docs/COST_CENTER_*.md
```

---

## Adding a New KPI - 5 Minute Checklist

### Step 1: Database (2 min)

```sql
-- Create migration file: app/migrations/00X_add_kpi_name.php
-- Copy template from: app/migrations/004_add_new_kpi_template.php

-- Key SQL to add:
ALTER TABLE fact_cost_center
ADD COLUMN kpi_name DECIMAL(10,2)
GENERATED ALWAYS AS (calculation...) STORED;
```

### Step 2: Update Views (1 min)

```sql
-- In migration: ALTER VIEW view_cost_center_pharmacy AS
SELECT
    ...,
    ROUND(AVG(f.kpi_name), 2) AS kpi_name,
    ...
FROM fact_cost_center f
```

### Step 3: Create Helper (1 min)

```php
// In app/helpers/cost_center_helper.php

if (!function_exists('format_kpi_name')) {
    function format_kpi_name($value) {
        return number_format($value, 2) . ' units';
    }
}
```

### Step 4: Add to Frontend (1 min)

```php
<!-- In cost_center_dashboard.php or table view -->
<td><?php echo format_kpi_name($pharmacy['kpi_name']); ?></td>
```

### Step 5: Test

```bash
# Run migration
http://domain/admin/migrate

# Check dashboard shows new KPI
http://domain/admin/cost_center/dashboard
```

---

## API Endpoints

### Get Company Summary

```
GET /admin/cost_center/dashboard
Response: {
    "success": true,
    "data": {
        "total_revenue": 50000,
        "total_cost": 30000,
        "total_profit": 20000,
        "profit_margin_pct": 40.00,
        "cost_ratio_pct": 60.00
    }
}
```

### Get Pharmacy List

```
GET /admin/cost_center/ajax/pharmacy-kpis
Query Params: period=YYYY-MM, sort_by=revenue|profit|margin|cost
Response: [
    {
        "pharmacy_id": 1,
        "pharmacy_name": "Pharmacy A",
        "kpi_total_revenue": 50000,
        "kpi_total_cost": 30000,
        "kpi_profit_loss": 20000,
        "kpi_profit_margin_pct": 40.00,
        "kpi_cost_ratio_pct": 60.00,
        "branch_count": 3
    }
]
```

### Get Pharmacy Detail

```
GET /admin/cost_center/pharmacy/{pharmacy_id}
Response: {
    "success": true,
    "pharmacy": {...},
    "branches": [...]
}
```

### Get Branch Detail

```
GET /admin/cost_center/branch/{branch_id}/detail
Response: {
    "success": true,
    "data": {
        "branch": {...},
        "cost_breakdown": {...},
        "kpis": {...}
    }
}
```

---

## Database Schema Quick Ref

### Fact Table

```sql
fact_cost_center (
    warehouse_id,           -- Branch/Pharmacy/Company ID
    warehouse_name,         -- Name
    warehouse_type,         -- 'pharmacy', 'branch', 'company'
    pharmacy_id,            -- Parent pharmacy
    parent_warehouse_id,    -- Parent warehouse
    transaction_date,       -- Daily aggregation
    period_year,
    period_month,
    -- Metrics:
    total_revenue,          -- From sma_sale
    total_cogs,             -- From sma_purchases
    inventory_movement_cost,-- From sma_inventory_movement
    operational_cost,       -- Shipping, handling, etc.
    total_cost,             -- Computed
    -- Add new KPIs as columns (GENERATED ALWAYS)
    kpi_your_metric DECIMAL(10,2) GENERATED...
)
```

### Views

```sql
view_cost_center_pharmacy       -- Aggregated at pharmacy level, monthly
view_cost_center_branch         -- Aggregated at branch level, monthly
view_cost_center_summary        -- Aggregated at company level, monthly
```

---

## Common Code Patterns

### Model: Get KPIs for Dashboard

```php
$this->load->model('admin/Cost_center_model');
$kpis = $this->Cost_center_model->get_pharmacies_with_kpis(
    $period = '2025-10',
    $sort_by = 'profit',
    $limit = 100,
    $offset = 0
);
```

### Controller: Return JSON

```php
$this->load->model('admin/Cost_center_model');
$data = $this->Cost_center_model->get_pharmacies_with_kpis();

$this->output
    ->set_content_type('application/json')
    ->set_output(json_encode([
        'success' => true,
        'data' => $data
    ]));
```

### View: Display KPI Card

```php
<div class="card border-left-success">
    <div class="card-body">
        <h3><?php echo format_currency($summary['kpi_total_revenue']); ?></h3>
        <small><?php echo format_percentage($summary['kpi_profit_margin_pct']); ?>%</small>
    </div>
</div>
```

### Helper: Format Value

```php
function format_new_kpi($value) {
    if (is_null($value) || $value === '') return 'N/A';
    return number_format($value, 2);
}

function get_new_kpi_status($value) {
    if ($value >= 35) return ['text' => 'Good', 'class' => 'text-success'];
    if ($value >= 25) return ['text' => 'Fair', 'class' => 'text-warning'];
    return ['text' => 'Poor', 'class' => 'text-danger'];
}
```

---

## Troubleshooting

### Problem: KPI showing NULL

**Solution:**

```sql
-- Check column exists
DESCRIBE fact_cost_center;

-- Check view includes column
SHOW CREATE VIEW view_cost_center_pharmacy;

-- Ensure COALESCE in calculation
SELECT COALESCE(AVG(kpi_name), 0) AS kpi_name ...
```

### Problem: Chart not updating

**Solution:**

```javascript
// Check data is passed to template
console.log(<?php echo json_encode($chart_data); ?>);

// Reinitialize chart on data change
$('#period-select').on('change', function() {
    location.reload();  // Simple refresh
    // Or use AJAX to reload chart only
});
```

### Problem: Query slow after adding KPI

**Solution:**

```sql
-- Check for missing index
ALTER TABLE fact_cost_center
ADD INDEX idx_kpi_lookup (warehouse_id, transaction_date, kpi_name);

-- Verify index is used
EXPLAIN SELECT ... WHERE warehouse_id = 1;
```

### Problem: Migration fails

**Solution:**

```php
// Reduce migration to individual steps:
// 1. Add column only
ALTER TABLE ... ADD COLUMN ...

// 2. Update view separately
CREATE OR REPLACE VIEW ...

// 3. Add index separately
ALTER TABLE ... ADD INDEX ...

// Use run migrations individually if script is too long
```

---

## Performance Tips

### When Adding KPIs

1. Use GENERATED ALWAYS columns (auto-calculated)
2. Add DECIMAL(10,2) for consistency
3. Use COALESCE(..., 0) for NULL handling
4. Add index if frequently sorted (optional)

### For Queries

```sql
-- GOOD: Uses index
SELECT * FROM view_cost_center_pharmacy
WHERE warehouse_id = 1 AND period = '2025-10';

-- SLOW: Full table scan
SELECT * FROM view_cost_center_pharmacy
WHERE YEAR(period) = 2025;

-- BETTER: Use indexed columns
WHERE period >= '2025-01' AND period <= '2025-12'
```

### For Views

```sql
-- GOOD: Aggregates before selecting
SELECT warehouse_id, SUM(revenue) FROM fact_cost_center GROUP BY warehouse_id;

-- SLOW: No aggregation
SELECT warehouse_id, revenue FROM fact_cost_center;
```

---

## Useful Queries

### Verify Data

```sql
-- Check fact table has today's data
SELECT COUNT(*) FROM fact_cost_center
WHERE transaction_date = CURDATE();

-- Check all pharmacies represented
SELECT COUNT(DISTINCT warehouse_id) FROM fact_cost_center;

-- Check for NULL values
SELECT * FROM view_cost_center_pharmacy
WHERE kpi_total_revenue IS NULL;
```

### Aggregate Stats

```sql
-- Company summary
SELECT
    SUM(kpi_total_revenue) AS total_revenue,
    SUM(kpi_total_cost) AS total_cost,
    COUNT(DISTINCT warehouse_id) AS pharmacy_count
FROM view_cost_center_pharmacy;

-- Pharmacy comparison
SELECT
    pharmacy_name,
    kpi_total_revenue,
    kpi_profit_margin_pct
FROM view_cost_center_pharmacy
ORDER BY kpi_profit_margin_pct DESC;
```

### Debug ETL

```sql
-- Check when data was last updated
SELECT MAX(updated_at) FROM fact_cost_center;

-- Check audit trail
SELECT * FROM audit_log
WHERE entity = 'fact_cost_center'
ORDER BY created_at DESC LIMIT 10;
```

---

## Testing

### Test New KPI

```php
// Test that KPI is calculated
$pharmacies = $this->cost_center->get_pharmacies_with_kpis('2025-10');
$this->assertEquals($pharmacies[0]['kpi_new_metric'] > 0, true);

// Test formatting
$formatted = format_new_kpi(42.567);
$this->assertEquals($formatted, '42.57');

// Test API response
$response = $this->get('/admin/cost_center/dashboard');
$this->assertArrayHasKey('kpi_new_metric', $response['data']);
```

### Performance Test

```php
// Measure query time
$start = microtime(true);
$result = $this->cost_center->get_pharmacies_with_kpis('2025-10');
$time = microtime(true) - $start;
$this->assertLessThan(1, $time);  // Should be < 1 second
```

---

## Deployment Checklist

- [ ] All migrations created and tested locally
- [ ] New KPI added to all 3 views
- [ ] Helper functions created
- [ ] Frontend HTML updated
- [ ] Dashboard tested in browser
- [ ] All 5 API endpoints working
- [ ] No console errors (F12)
- [ ] Database query < 500ms (tested)
- [ ] Documentation updated
- [ ] Changelog entry added
- [ ] Ready for production deploy

---

## Useful Resources

**Documentation:**

- COST_CENTER_KPI_EXTENSIBILITY.md (How to add KPIs)
- COST_CENTER_KPI_PRACTICAL_EXAMPLES.md (Real examples)
- COST_CENTER_IMPLEMENTATION.md (Architecture)

**Code:**

- app/migrations/004_add_new_kpi_template.php (Template)
- app/models/admin/Cost_center_model.php (Model methods)
- app/controllers/admin/Cost_center.php (API endpoints)

**Database:**

- app/migrations/001_create_dimensions.php (Schema)
- app/migrations/002_create_fact_cost_center.php (Views)
- app/migrations/003_create_etl_pipeline.php (ETL)

---

## Contact & Support

For questions or issues:

1. Check COST_CENTER_KPI_EXTENSIBILITY.md
2. Review COST_CENTER_KPI_PRACTICAL_EXAMPLES.md for examples
3. Check database logs: `tail -f app/logs/database_*.log`
4. Check application logs: `tail -f app/logs/log_*.log`

---

**Quick Answer Lookup:**

| Question                               | Answer                                                    | Time             |
| -------------------------------------- | --------------------------------------------------------- | ---------------- |
| How do I add a new KPI?                | See COST_CENTER_KPI_EXTENSIBILITY.md                      | 30-50 min        |
| How do I add a new KPI sorting option? | See Backend → Cost_center_model.php line ~50              | 2 min            |
| What's the database schema?            | See app/migrations/001_create_dimensions.php              | Reference        |
| How do I run the ETL?                  | See database/scripts/etl_cost_center.php                  | Run command      |
| Where do I add a dashboard card?       | See app/views/admin/cost_center/cost_center_dashboard.php | 5 min            |
| How do I test the API?                 | GET /admin/cost_center/dashboard                          | Test immediately |
| How do I debug a slow query?           | Run EXPLAIN on the query                                  | Check indexes    |

---

**You're ready to extend the Cost Center Module! 🚀**
