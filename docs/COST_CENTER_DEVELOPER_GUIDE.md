# Cost Center Dashboard - Developer Reference Guide

**Date:** October 28, 2025  
**Version:** 1.0  
**Audience:** Developers & Maintainers

---

## Quick Start for Developers

### Understanding the Architecture

The Cost Center Dashboard consists of three layers:

```
┌─────────────────────────────────┐
│   VIEW LAYER (Frontend)          │
│   - Horizon UI Components        │
│   - ECharts Visualizations       │
│   - Responsive Grid Layout       │
└──────────────┬──────────────────┘
               │
┌──────────────▼──────────────────┐
│  CONTROLLER LAYER (Logic)        │
│  - Cost_center.php::dashboard()  │
│  - Data orchestration            │
│  - Error handling                │
└──────────────┬──────────────────┘
               │
┌──────────────▼──────────────────┐
│   MODEL LAYER (Data)             │
│  - Cost_center_model.php         │
│  - Database queries              │
│  - Business logic                │
└─────────────────────────────────┘
```

---

## Model Layer Deep Dive

### Method 1: `get_company_summary_metrics()`

**Location:** `app/models/admin/Cost_center_model.php` (Line ~950)

**Method Signature:**

```php
public function get_company_summary_metrics($period = null)
```

**How It Works:**

1. **Input Validation**

   ```php
   if (!$period) {
       $period = date('Y-m');  // Default to current month
   }
   ```

2. **Period Parsing**

   ```php
   $period_parts = explode('-', $period);  // "2025-10" → [2025, 10]
   $period_year = (int)$period_parts[0];
   $period_month = (int)$period_parts[1];
   ```

3. **Query Construction**

   - Uses `SUM()` aggregations for all metrics
   - `COALESCE()` to handle NULL values (default to 0)
   - `CASE` statements for percentage calculations
   - `ROUND()` for consistent decimal places

4. **Key Calculations**

   ```php
   // Total Margin = Total Revenue - Total Costs
   SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost))

   // Margin Percentage
   ((SUM(revenue) - SUM(costs)) / SUM(revenue)) * 100

   // Cost Percentages
   (SUM(cost_category) / SUM(revenue)) * 100
   ```

5. **Returns**
   - Single row array with 20+ metric fields
   - NULL values converted to 0
   - Decimals rounded to 2 places
   - Includes metadata (period, last_updated)

**Usage Example:**

```php
$metrics = $this->cost_center->get_company_summary_metrics('2025-10');

// Access individual metrics
$total_sales = $metrics['total_sales'];
$profit_margin = $metrics['margin_percentage'];
$customer_count = $metrics['total_customers'];

// Use in calculations
if ($metrics['total_sales'] > 0) {
    $margin_percentage = ($metrics['total_margin'] / $metrics['total_sales']) * 100;
}
```

**SQL Query Pattern:**

```sql
SELECT
    COALESCE(SUM(...), 0) AS metric_name,  -- Aggregation
    CASE WHEN ... THEN ... ELSE ... END AS percentage,  -- Calculation
    ...
FROM sma_fact_cost_center fcc
WHERE fcc.period_year = ? AND fcc.period_month = ?
```

---

### Method 2: `get_best_moving_products()`

**Location:** `app/models/admin/Cost_center_model.php` (Line ~1052)

**Method Signature:**

```php
public function get_best_moving_products(
    $level = 'company',      // 'company', 'pharmacy', 'branch'
    $warehouse_id = null,    // Required for pharmacy/branch
    $period = null,          // YYYY-MM format
    $limit = 5               // Number of products to return
)
```

**How It Works:**

1. **Input Validation**

   ```php
   if (!in_array($level, ['company', 'pharmacy', 'branch'])) {
       $level = 'company';  // Default
   }
   if (!$period) {
       $period = date('Y-m');
   }
   ```

2. **Dynamic WHERE Clause Building**

   ```php
   $where_clause = "fcc.period_year = ? AND fcc.period_month = ?";
   $params = [$period_year, $period_month];

   if ($level === 'pharmacy' && $warehouse_id) {
       $where_clause .= " AND fcc.pharmacy_id = ?";
       $params[] = $warehouse_id;
   } elseif ($level === 'branch' && $warehouse_id) {
       $where_clause .= " AND fcc.branch_id = ?";
       $params[] = $warehouse_id;
   }
   ```

3. **Multi-Table Join**

   ```php
   // Joins:
   sma_fact_cost_center  ← Main transaction/cost data
   ↓
   sma_products          ← Product information
   ↓
   sma_categories        ← Category information (LEFT JOIN)
   ```

4. **Aggregation & Sorting**

   ```php
   GROUP BY sp.id, sp.code, sp.name, sp.unit, sp.category_id, sc.name
   ORDER BY total_units_sold DESC  // Sort by volume
   LIMIT ?                         // Limit to N products
   ```

5. **Calculations**

   ```php
   // Average sale per unit
   SUM(total_revenue) / COUNT(DISTINCT transaction_id)

   // Margin percentage
   (SUM(total_margin) / SUM(total_revenue)) * 100

   // Customer & warehouse counts
   COUNT(DISTINCT customer_id)
   COUNT(DISTINCT warehouse_id)
   ```

6. **Returns**
   - Array of products (default 5 max)
   - Sorted by sales volume (highest first)
   - Includes aggregated metrics
   - Can be paginated or filtered further

**Usage Examples:**

```php
// Company level - Top 5 products
$best_products = $this->cost_center->get_best_moving_products(
    'company',     // All warehouses
    null,          // No warehouse filter
    '2025-10',     // October 2025
    5              // Top 5
);

// Pharmacy level - Top 3 products for pharmacy ID 15
$pharmacy_products = $this->cost_center->get_best_moving_products(
    'pharmacy',    // Pharmacy level
    15,            // Pharmacy warehouse ID
    '2025-10',     // October 2025
    3              // Top 3
);

// Branch level - Top 10 products for branch ID 25
$branch_products = $this->cost_center->get_best_moving_products(
    'branch',      // Branch level
    25,            // Branch warehouse ID
    '2025-10',     // October 2025
    10             // Top 10
);

// Iterate through results
foreach ($best_products as $product) {
    echo $product['product_name'];      // Product name
    echo $product['total_units_sold'];  // Units sold
    echo $product['total_sales'];       // Sales amount
    echo $product['margin_percentage']; // Profit margin %
}
```

---

## Controller Layer Deep Dive

### Updating `Cost_center.php::dashboard()`

**Location:** `app/controllers/admin/Cost_center.php` (Line ~35)

**Method Segment - Data Fetching:**

```php
public function dashboard() {
    try {
        // 1. Get period from query string or use current
        $period = $this->input->get('period') ?: date('Y-m');

        // 2. Validate period format
        if (!$this->_validate_period($period)) {
            $period = date('Y-m');
        }

        // 3. NEW: Fetch company-level metrics
        error_log('[COST_CENTER] Fetching company-level summary metrics');
        $company_metrics = $this->cost_center->get_company_summary_metrics($period);
        error_log('[COST_CENTER] Company metrics retrieved: Sales=' . ($company_metrics['total_sales'] ?? 0));

        // 4. NEW: Fetch best moving products
        error_log('[COST_CENTER] Fetching best moving products (Top 5)');
        $best_products = $this->cost_center->get_best_moving_products('company', null, $period, 5);
        error_log('[COST_CENTER] Best products retrieved: ' . count($best_products ?? []) . ' records');

        // 5. Prepare view data with new variables
        $view_data = array_merge($this->data, [
            'page_title' => 'Cost Center Dashboard',
            'period' => $period,
            'company_metrics' => $company_metrics,  // NEW
            'best_products' => $best_products,      // NEW
            // ... other existing data
        ]);

        // 6. Load views
        $this->load->view($this->theme . 'header', $view_data);
        $this->load->view($this->theme . 'cost_center/cost_center_dashboard_modern', $view_data);
        $this->load->view($this->theme . 'footer', $view_data);

    } catch (Exception $e) {
        log_message('error', 'Error: ' . $e->getMessage());
        show_error('Error loading dashboard', 500);
    }
}
```

**Error Handling Pattern:**

```php
// Log all steps
error_log('[COST_CENTER] Starting operation');

try {
    // Perform operation
    $result = $this->cost_center->get_company_summary_metrics($period);

    // Log success
    error_log('[COST_CENTER] Operation successful: ' . json_encode($result));

    // Use result
    $view_data['data'] = $result;

} catch (Exception $e) {
    // Log error with full context
    error_log('[COST_CENTER] Error: ' . $e->getMessage());
    error_log('[COST_CENTER] Stack: ' . $e->getTraceAsString());

    // Show user-friendly error
    show_error('Error loading dashboard: ' . $e->getMessage(), 500);
}
```

---

## View Layer Deep Dive

### Section 1: Company Metrics Cards

**Location:** `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php` (Line ~708)

**HTML Structure:**

```html
<div style="margin-top: 40px; padding-bottom: 20px;">
	<h2 style="...">
		<i class="fa fa-bar-chart"></i>
		Company Performance Summary
	</h2>
	<div id="companyMetricsContainer" class="kpi-cards-grid">
		<!-- Cards rendered by JavaScript -->
	</div>
</div>
```

**JavaScript Rendering:**

```javascript
function renderCompanyMetrics() {
	const container = document.getElementById("companyMetricsContainer");
	const metrics = dashboardData.companyMetrics;

	// Define card specifications
	const cards = [
		{
			label: "Total Sales",
			value: metrics.total_sales || 0,
			icon: "💰",
			color: "blue",
			isCurrency: true,
		},
		// ... more cards
	];

	// Render each card
	container.innerHTML = cards
		.map((card) => {
			return `
            <div class="metric-card">
                <div class="metric-card-header">
                    <div>
                        <div class="metric-card-label">${card.label}</div>
                        <div class="metric-card-value">${formatValue(
													card.value,
													card.isCurrency
												)}</div>
                    </div>
                    <div class="metric-card-icon ${card.color}">${
				card.icon
			}</div>
                </div>
            </div>
        `;
		})
		.join("");
}
```

**CSS Classes Used:**

- `metric-card` - Card container
- `metric-card-header` - Card header flex layout
- `metric-card-label` - Smaller metric name
- `metric-card-value` - Large metric value
- `metric-card-icon` - Icon container with color variant

---

### Section 2: Best Products Table

**Location:** `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php` (Line ~750)

**HTML Structure:**

```html
<div style="margin-top: 40px;">
	<h2><i class="fa fa-fire"></i> Best Moving Products (Top 5)</h2>
	<div class="table-section">
		<div class="table-header-bar">
			<h3 class="table-title">Top 5 Products by Sales Volume</h3>
		</div>
		<div class="table-wrapper">
			<table class="data-table" id="bestProductsTable">
				<!-- Table headers and rows rendered by JavaScript -->
			</table>
		</div>
	</div>
</div>
```

**JavaScript Rendering:**

```javascript
function renderBestProductsTable() {
	const container = document.getElementById("bestProductsTableBody");
	const products = dashboardData.bestProducts || [];

	// Handle empty state
	if (products.length === 0) {
		container.innerHTML = '<tr><td colspan="9">No products found</td></tr>';
		return;
	}

	// Render each product row
	container.innerHTML = products
		.map((product) => {
			return `
            <tr>
                <td><strong>${product.product_code}</strong></td>
                <td>${product.product_name}</td>
                <td><span class="badge">${product.category_name}</span></td>
                <td class="table-currency">${formatNumber(
									product.total_units_sold
								)}</td>
                <td class="table-currency" style="color: #05cd99;">${formatCurrency(
									product.total_sales
								)}</td>
                <td class="table-currency">${formatCurrency(
									product.total_margin
								)}</td>
                <td class="table-percentage" style="color: #f59e0b;">${product.margin_percentage.toFixed(
									2
								)}%</td>
                <td class="table-currency">${formatCurrency(
									product.avg_sale_per_unit
								)}</td>
                <td style="text-align: center;">${formatNumber(
									product.customer_count
								)}</td>
            </tr>
        `;
		})
		.join("");
}
```

**Sorting Implementation:**

```javascript
function sortProductTable(column) {
	// Toggle sort direction
	productSort.direction =
		productSort.column === column && productSort.direction === "DESC"
			? "ASC"
			: "DESC";
	productSort.column = column;

	// Sort the data
	productTableData.sort((a, b) => {
		let aVal = a[column];
		let bVal = b[column];

		// Handle numeric comparison
		if (typeof aVal === "number" && typeof bVal === "number") {
			return productSort.direction === "DESC" ? bVal - aVal : aVal - bVal;
		}

		// Handle string comparison
		aVal = String(aVal).toLowerCase();
		bVal = String(bVal).toLowerCase();
		return productSort.direction === "DESC"
			? bVal.localeCompare(aVal)
			: aVal.localeCompare(bVal);
	});

	// Re-render table
	renderBestProductsTable();
}
```

---

## Extending the Implementation

### Adding a New Metric to Company Summary

1. **Add to SQL Query** (Model)

   ```php
   // In get_company_summary_metrics(), add to SELECT:
   COALESCE(SUM(fcc.new_column), 0) AS new_metric,
   ```

2. **Update Query Aggregation** (if calculated)

   ```php
   // Add calculation if needed
   CASE WHEN SUM(fcc.total_revenue) = 0 THEN 0
        ELSE ROUND((SUM(fcc.new_column) / SUM(fcc.total_revenue)) * 100, 2)
   END AS new_metric_percentage,
   ```

3. **Add Card to View** (View)
   ```javascript
   // In renderCompanyMetrics():
   {
       label: 'New Metric Name',
       value: metrics.new_metric || 0,
       icon: '📊',
       color: 'blue',
       isCurrency: true/false
   }
   ```

### Adding Drill-Down Filtering

1. **Update Model Method**

   ```php
   public function get_best_moving_products($level = 'company', $warehouse_id = null, ...) {
       // Already supports $level and $warehouse_id parameters
       // Just need to pass them from controller
   }
   ```

2. **Update Controller**

   ```php
   $filtered_warehouse_id = $this->input->get('warehouse_id');
   $filtered_level = $this->input->get('level') ?? 'company';

   $best_products = $this->cost_center->get_best_moving_products(
       $filtered_level,
       $filtered_warehouse_id,
       $period,
       5
   );
   ```

3. **Update View**
   ```javascript
   // Add dropdown filter
   function handleLevelFilter(level) {
   	const warehouseId = document.getElementById("warehouseFilter").value;
   	// Reload dashboard with filters
   	window.location.href = `?period=${dashboardData.currentPeriod}&level=${level}&warehouse_id=${warehouseId}`;
   }
   ```

---

## Debugging Guide

### Issue: Metrics Show as 0

**Debug Steps:**

```javascript
// In browser console (F12)
console.log("Dashboard Data:", dashboardData);
console.log("Company Metrics:", dashboardData.companyMetrics);

// Check if data exists
if (dashboardData.companyMetrics.total_sales === 0) {
	console.warn("Total sales is 0 - check if data exists for period");
}
```

**Check Database:**

```sql
-- Count records for period
SELECT COUNT(*) as count FROM sma_fact_cost_center
WHERE period_year = 2025 AND period_month = 10;

-- Show sample records
SELECT * FROM sma_fact_cost_center
WHERE period_year = 2025 AND period_month = 10
LIMIT 5;
```

### Issue: Best Products Not Showing

**Debug Steps:**

```javascript
// Check if products loaded
console.log("Best Products:", dashboardData.bestProducts);
console.log("Products Count:", dashboardData.bestProducts.length);

// Check products array
if (dashboardData.bestProducts.length === 0) {
	console.warn("No products found - check if products table populated");
}
```

**Check Database:**

```sql
-- Verify products exist in fact table
SELECT DISTINCT fcc.product_id
FROM sma_fact_cost_center fcc
WHERE fcc.period_year = 2025 AND fcc.period_month = 10
LIMIT 10;

-- Verify products exist in products table
SELECT * FROM sma_products
WHERE id IN (...from above...);
```

### Issue: Formatting Not Working

**Check Formatting Functions:**

```javascript
// In console
typeof formatCurrency; // should be 'function'
typeof formatNumber; // should be 'function'

// Test formatting
formatCurrency(1000, false); // Should return "1,000.00 SAR"
formatNumber(1000); // Should return "1,000"
```

---

## Performance Optimization

### Query Optimization

**Index Creation:**

```sql
-- Create indexes for common queries
CREATE INDEX idx_fact_period ON sma_fact_cost_center(period_year, period_month);
CREATE INDEX idx_fact_product ON sma_fact_cost_center(product_id);
CREATE INDEX idx_product_category ON sma_products(category_id);
```

**Check Query Performance:**

```sql
-- Show query execution time
EXPLAIN SELECT ... FROM sma_fact_cost_center WHERE period_year = ? AND period_month = ?;

-- Profile query
SET profiling=1;
-- Run query
SET profiling=0;
SHOW PROFILES;
SHOW PROFILE FOR QUERY 1;
```

### Caching Strategy

**Implement Caching:**

```php
// In model method
$cache_key = 'company_metrics_' . $period;
$cached_data = $this->cache->get($cache_key);

if ($cached_data) {
    return $cached_data;
}

// Fetch from database
$result = ...; // Query

// Cache for 1 hour
$this->cache->save($cache_key, $result, 3600);

return $result;
```

---

## Testing Guide for Developers

### Unit Testing Model Methods

```php
// test_cost_center_model.php
public function test_get_company_summary_metrics() {
    $this->load->model('admin/Cost_center_model', 'cost_center');

    // Test with current period
    $result = $this->cost_center->get_company_summary_metrics();
    $this->assertIsArray($result);
    $this->assertArrayHasKey('total_sales', $result);

    // Test with specific period
    $result = $this->cost_center->get_company_summary_metrics('2025-10');
    $this->assertIsArray($result);

    // Verify calculations
    $this->assertEquals(
        $result['total_margin'],
        $result['total_sales'] - $result['total_costs']
    );
}

public function test_get_best_moving_products() {
    $this->load->model('admin/Cost_center_model', 'cost_center');

    // Test company level
    $result = $this->cost_center->get_best_moving_products('company', null, '2025-10', 5);
    $this->assertIsArray($result);
    $this->assertLessThanOrEqual(5, count($result));

    // Verify sorting
    for ($i = 0; $i < count($result) - 1; $i++) {
        $this->assertGreaterThanOrEqual(
            $result[$i + 1]['total_units_sold'],
            $result[$i]['total_units_sold']
        );
    }
}
```

### Integration Testing

```php
// Test full dashboard flow
public function test_dashboard_integration() {
    // Request dashboard
    $response = $this->get('admin/cost_center/dashboard?period=2025-10');

    // Check response status
    $this->assertEqual($response->code, 200);

    // Check data in response
    $this->assertStringContains('Company Performance Summary', $response->body);
    $this->assertStringContains('Best Moving Products', $response->body);

    // Verify JSON data is valid
    preg_match('/dashboardData = ({.*?});/', $response->body, $matches);
    $data = json_decode($matches[1], true);
    $this->assertArrayHasKey('companyMetrics', $data);
    $this->assertArrayHasKey('bestProducts', $data);
}
```

---

## Version History

| Version | Date       | Changes                             |
| ------- | ---------- | ----------------------------------- |
| 1.0     | 2025-10-28 | Initial implementation              |
| -       | -          | Added get_company_summary_metrics() |
| -       | -          | Added get_best_moving_products()    |
| -       | -          | Added dashboard sections            |
| -       | -          | Added sorting functionality         |

---

## Support & Questions

For questions about implementation:

1. Check relevant docs in `/docs/` folder
2. Review method PHPDoc comments
3. Check browser console for errors
4. Review server logs in `/app/logs/`
5. Test queries directly in MySQL

---

**Document Version:** 1.0  
**Last Updated:** October 28, 2025  
**Maintained By:** Development Team
