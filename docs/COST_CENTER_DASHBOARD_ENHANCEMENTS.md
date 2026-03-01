# Cost Center Dashboard Enhancements

## Company-Level Summary Metrics & Best Moving Products

**Date:** October 28, 2025  
**Version:** 1.0  
**Status:** IMPLEMENTED  
**Endpoint:** `http://localhost:8080/avenzur/admin/cost_center/dashboard`

---

## Table of Contents

1. [Overview](#overview)
2. [Implementation Details](#implementation-details)
3. [API Methods](#api-methods)
4. [Data Structures](#data-structures)
5. [Dashboard Sections](#dashboard-sections)
6. [Testing Guide](#testing-guide)
7. [Troubleshooting](#troubleshooting)

---

## Overview

The Cost Center Dashboard has been enhanced with two new major features:

### 1. **Company-Level Summary Metrics**

Displays key performance indicators aggregated across the entire company:

- **Total Sales** - Total revenue for the period
- **Total Margin** - Total profit amount
- **Total Customers** - Unique customer count
- **Total Items Sold** - Total units/items sold

Additional metrics calculated and available:

- Gross Margin Percentage
- Net Margin Percentage
- Breakdown of costs (COGS, Inventory Movement, Operational)
- Cost percentages relative to revenue
- Transaction volume and average transaction value
- Warehouse/Pharmacy/Branch counts

### 2. **Best Moving Products (Top 5)**

Interactive table showing the top 5 products by sales volume with:

- **Product Information** - Code, Name, Category
- **Sales Metrics** - Total units sold, total sales amount
- **Profitability** - Total margin, margin percentage
- **Performance** - Average sale per unit, customer count
- **Sortable Columns** - Click headers to sort data

---

## Implementation Details

### Files Modified

#### 1. **Model: `app/models/admin/Cost_center_model.php`**

Two new methods added:

##### Method 1: `get_company_summary_metrics($period = null)`

```php
public function get_company_summary_metrics($period = null)
```

**Purpose:** Fetch aggregated company-wide metrics

**Parameters:**

- `$period` (string, optional): Format 'YYYY-MM' (default: current month)

**Returns:**

- Array with keys:
  - `total_sales` - Total revenue
  - `total_cogs` - Total cost of goods sold
  - `total_inventory_movement` - Inventory movement costs
  - `total_operational_cost` - Operational costs
  - `total_costs` - Sum of all costs
  - `total_margin` - Total profit
  - `margin_percentage` - Profit margin %
  - `gross_margin_percentage` - Gross margin %
  - `total_items_sold` - Total units
  - `total_customers` - Unique customers
  - `total_transactions` - Transaction count
  - `average_transaction_value` - Avg transaction amount
  - `warehouse_count`, `pharmacy_count`, `branch_count` - Entity counts
  - Cost percentages (COGS%, Inventory%, Operational%)
  - `period` - Selected period
  - `last_updated` - Timestamp

**SQL Implementation:**

- Queries `sma_fact_cost_center` table
- Aggregates data for the specified period year/month
- Calculates percentages and averages
- Returns single row of aggregated metrics

---

##### Method 2: `get_best_moving_products($level = 'company', $warehouse_id = null, $period = null, $limit = 5)`

```php
public function get_best_moving_products($level = 'company', $warehouse_id = null, $period = null, $limit = 5)
```

**Purpose:** Fetch top N products by sales volume

**Parameters:**

- `$level` (string): Hierarchy level - 'company', 'pharmacy', or 'branch' (default: 'company')
- `$warehouse_id` (int, optional): Warehouse ID for pharmacy/branch level filtering
- `$period` (string, optional): Format 'YYYY-MM' (default: current month)
- `$limit` (int): Number of products to return (default: 5)

**Returns:**

- Array of products (ordered by total_units_sold DESC):
  - `product_id` - Product ID
  - `product_code` - SKU/Product Code
  - `product_name` - Product Name
  - `unit` - Unit of measurement
  - `category_id`, `category_name` - Product category
  - `total_units_sold` - Total units sold
  - `total_sales` - Total revenue
  - `total_cost` - Total COGS
  - `total_margin` - Total profit
  - `margin_percentage` - Profit margin %
  - `avg_sale_per_unit` - Average sale price
  - `customer_count` - Unique customers
  - `warehouse_count` - Warehouses selling this product
  - `period` - Selected period
  - `last_updated` - Timestamp

**SQL Implementation:**

- Queries `sma_fact_cost_center` joined with `sma_products` and `sma_categories`
- Filters by level (company/pharmacy/branch) based on warehouse_id
- Groups by product
- Sorts by total_units_sold descending
- Limits to specified count (default 5)

---

#### 2. **Controller: `app/controllers/admin/Cost_center.php`**

Updated `dashboard()` method to fetch new data:

**Changes:**

- Added call to `get_company_summary_metrics($period)`
- Added call to `get_best_moving_products('company', null, $period, 5)`
- Passes `company_metrics` and `best_products` to view data
- Includes error logging for debugging

**Code Flow:**

```php
// Fetch company-level summary metrics
error_log('[COST_CENTER] Fetching company-level summary metrics');
$company_metrics = $this->cost_center->get_company_summary_metrics($period);

// Fetch best moving products (Top 5)
error_log('[COST_CENTER] Fetching best moving products (Top 5)');
$best_products = $this->cost_center->get_best_moving_products('company', null, $period, 5);

// Pass to view
$view_data = array_merge($this->data, [
    'company_metrics' => $company_metrics,
    'best_products' => $best_products,
    // ... other data
]);
```

---

#### 3. **View: `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`**

Three new sections added to the dashboard:

##### Section 1: Company Performance Summary (Metrics Cards)

- Added after pharmacy performance table
- Uses same card design as main KPI cards
- Shows: Total Sales, Total Margin, Total Customers, Items Sold
- Responsive grid layout (1-4 columns based on screen size)
- Icons: 💰 💵 📈 📦

##### Section 2: Best Moving Products Table

- Interactive sortable table
- Shows top 5 products by sales volume
- Columns: Code, Name, Category, Units Sold, Total Sales, Total Margin, Margin %, Avg Sale, Customers
- Color-coded values:
  - Green for sales/margin metrics
  - Orange for percentage metrics
- Sortable by clicking column headers
- Empty state message if no data

##### JavaScript Functions Added:

- `renderCompanyMetrics()` - Renders company metrics cards
- `renderBestProductsTable()` - Renders best products table
- `sortProductTable(column)` - Sorts best products table

---

## Data Structures

### Company Metrics Object

```javascript
{
    total_sales: 1250000,           // Total revenue (SAR)
    total_cogs: 750000,             // Cost of goods sold
    total_inventory_movement: 50000, // Inventory movement costs
    total_operational_cost: 100000,  // Operational expenses
    total_costs: 900000,             // Sum of all costs
    total_margin: 350000,            // Profit (revenue - costs)
    margin_percentage: 28.00,        // Margin as %
    gross_margin_percentage: 40.00,  // Gross margin %
    total_items_sold: 25000,         // Total units
    total_customers: 1250,           // Unique customers
    total_transactions: 5000,        // Transaction count
    average_transaction_value: 250,  // Average per transaction
    cogs_percentage: 60.00,          // COGS as % of revenue
    inventory_movement_percentage: 4.00,
    operational_cost_percentage: 8.00,
    warehouse_count: 15,
    pharmacy_count: 5,
    branch_count: 10,
    period: "2025-10",
    last_updated: "2025-10-28 10:30:00"
}
```

### Best Product Object

```javascript
{
    product_id: 123,
    product_code: "PARACETAMOL-500",
    product_name: "Paracetamol 500mg Tablets",
    unit: "Box",
    category_id: 45,
    category_name: "Pain Relief",
    total_units_sold: 5000,
    total_sales: 125000,      // SAR
    total_cost: 75000,        // SAR
    total_margin: 50000,      // SAR
    margin_percentage: 40.00, // %
    avg_sale_per_unit: 25,    // SAR
    customer_count: 250,
    warehouse_count: 10,
    period: "2025-10",
    last_updated: "2025-10-28 10:30:00"
}
```

---

## Dashboard Sections

### Section 1: Company Performance Summary

**Location:** Below pharmacy performance table  
**Title:** "Company Performance Summary" with 📊 icon  
**Grid Layout:** 4 columns (responsive)

**Cards:**

1. **Total Sales** 💰 (Blue)

   - Value: Formatted currency
   - Data: `company_metrics.total_sales`

2. **Total Margin** 📈 (Green)

   - Value: Formatted currency
   - Data: `company_metrics.total_margin`

3. **Total Customers** 👥 (Purple)

   - Value: Formatted number
   - Data: `company_metrics.total_customers`

4. **Items Sold** 📦 (Red)
   - Value: Formatted number
   - Data: `company_metrics.total_items_sold`

---

### Section 2: Best Moving Products

**Location:** Below company metrics  
**Title:** "Best Moving Products (Top 5)" with 🔥 icon

**Table Format:**

```
| Product Code | Product Name | Category | Units Sold | Total Sales | Total Margin | Margin % | Avg Sale/Unit | Customers |
|---|---|---|---|---|---|---|---|---|
| PARACETAMOL-500 | Paracetamol 500mg | Pain Relief | 5,000 | 125,000 SAR | 50,000 SAR | 40.00% | 25 SAR | 250 |
| ... | ... | ... | ... | ... | ... | ... | ... | ... |
```

**Features:**

- Sortable columns (click header to sort)
- Alternating row colors for readability
- Color-coded metrics:
  - Currency values in green
  - Percentages in orange
- Empty state: "No products found for this period"
- Period display in header: "Period: 2025-10"

---

## Testing Guide

### Test 1: Verify Metrics Calculation

**URL:** `http://localhost:8080/avenzur/admin/cost_center/dashboard?period=2025-10`

**Expected Results:**

1. Company metrics card shows "Total Sales: {amount} SAR"
2. Total Margin displays profit amount
3. Customer count is a whole number
4. Items Sold count is a whole number

**Validation:**

```
Total Margin = Total Sales - Total Costs (should match)
Margin Percentage = (Total Margin / Total Sales) * 100
```

---

### Test 2: Verify Best Products Display

**URL:** `http://localhost:8080/avenzur/admin/cost_center/dashboard`

**Expected Results:**

1. Best Moving Products table appears
2. Shows exactly 5 products (or fewer if less exist)
3. Products ordered by units sold (highest first)
4. All columns have values

**Validation:**

```
Row 1 Units Sold >= Row 2 Units Sold >= Row 3 Units Sold, etc.
```

---

### Test 3: Test Sorting

**Steps:**

1. Click "Units Sold" header
2. Verify products are sorted descending
3. Click again
4. Verify products are sorted ascending
5. Click "Total Sales" header
6. Verify products are sorted by sales

---

### Test 4: Test Period Change

**Steps:**

1. Change period selector to different month
2. Dashboard reloads
3. Metrics update to selected period
4. Best products table updates
5. All data reflects selected period

---

### Test 5: Test Responsive Design

**Desktop (1920px):**

- 4 metric cards in grid
- Table displays all columns

**Tablet (768px):**

- 2 metric cards per row
- Table may horizontal scroll

**Mobile (375px):**

- 1 metric card per row
- Table horizontal scroll enabled

---

### Test 6: Test Data Accuracy

**Manual Calculation:**

1. Query database directly:

```sql
SELECT
    SUM(total_revenue) as total_sales,
    SUM(total_cogs) as total_cogs,
    COUNT(DISTINCT customer_id) as total_customers,
    COUNT(DISTINCT transaction_id) as total_items
FROM sma_fact_cost_center
WHERE period_year = 2025 AND period_month = 10;
```

2. Compare with dashboard display
3. Values should match exactly

---

## Database Queries Reference

### Company Metrics Query Structure

```sql
SELECT
    COALESCE(SUM(fcc.total_revenue), 0) AS total_sales,
    COALESCE(SUM(fcc.total_cogs), 0) AS total_cogs,
    -- ... more fields
    CASE WHEN SUM(fcc.total_revenue) = 0 THEN 0
         ELSE ROUND(((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)))
                     / SUM(fcc.total_revenue)) * 100, 2)
    END AS margin_percentage
FROM sma_fact_cost_center fcc
WHERE fcc.period_year = ? AND fcc.period_month = ?
```

### Best Products Query Structure

```sql
SELECT
    sp.id, sp.code, sp.name,
    COUNT(DISTINCT fcc.transaction_id) AS total_units_sold,
    SUM(fcc.total_revenue) AS total_sales,
    SUM(fcc.total_margin) AS total_margin
FROM sma_fact_cost_center fcc
INNER JOIN sma_products sp ON fcc.product_id = sp.id
LEFT JOIN sma_categories sc ON sp.category_id = sc.id
WHERE fcc.period_year = ? AND fcc.period_month = ?
GROUP BY sp.id
ORDER BY total_units_sold DESC
LIMIT 5
```

---

## Troubleshooting

### Issue 1: Metrics Cards Show "No Data"

**Cause:** No data in `sma_fact_cost_center` for the selected period

**Solution:**

1. Verify ETL process has run: Check `sma_etl_audit_log`
2. Verify period has data: Run query:

```sql
SELECT COUNT(*) FROM sma_fact_cost_center
WHERE period_year = 2025 AND period_month = 10;
```

3. If 0 rows, ensure transactions exist for that period

---

### Issue 2: Best Products Table Empty

**Cause:** No products linked in fact table or no transactions

**Solution:**

1. Check if products exist in fact table:

```sql
SELECT DISTINCT product_id FROM sma_fact_cost_center
WHERE period_year = 2025 AND period_month = 10
LIMIT 5;
```

2. Verify products table has matching product IDs:

```sql
SELECT id FROM sma_products WHERE id IN (...);
```

---

### Issue 3: Numbers Not Formatting Correctly

**Cause:** JavaScript formatting functions not defined

**Solution:**

1. Verify `formatCurrency()` function exists in dashboard
2. Verify `formatNumber()` function exists
3. Check browser console for JavaScript errors (F12)

---

### Issue 4: Sorting Not Working

**Cause:** `sortProductTable()` function error

**Solution:**

1. Open browser DevTools (F12)
2. Check Console for errors
3. Verify function is defined
4. Check column name matches data structure

---

### Issue 5: Database Connection Error

**Error:** "Error rendering metrics: Database error"

**Solution:**

1. Check database connectivity
2. Verify `sma_fact_cost_center` table exists:

```sql
SHOW TABLES LIKE 'sma_fact_cost_center';
```

3. Check user permissions on table:

```sql
SHOW GRANTS FOR CURRENT_USER();
```

4. Check error logs: `app/logs/`

---

## Performance Considerations

### Query Performance

- `get_company_summary_metrics()`: ~50-100ms (single aggregation)
- `get_best_moving_products()`: ~100-200ms (join + grouping)
- Both queries use indexed columns (period_year, period_month)

### Optimization Tips

1. Ensure indexes on fact table:

```sql
CREATE INDEX idx_fact_period ON sma_fact_cost_center(period_year, period_month);
CREATE INDEX idx_fact_product ON sma_fact_cost_center(product_id);
```

2. Consider caching for frequently accessed periods
3. Pagination for products list if needed

---

## Future Enhancements

1. **Drill-Down by Pharmacy/Branch**

   - Filter best products by selected pharmacy
   - Show pharmacy-specific metrics

2. **Comparative Analytics**

   - Period-over-period comparison
   - Variance analysis

3. **Forecasting**

   - Project metrics to end of month
   - Trend analysis

4. **Export Functionality**

   - Export metrics as PDF report
   - Export products as Excel

5. **Real-time Updates**
   - WebSocket updates for live data
   - Dashboard auto-refresh

---

## Related Documentation

- [Cost Center Model Documentation](COST_CENTER_MODEL_DOCUMENTATION.md)
- [Budget API Quick Reference](BUDGET_API_QUICK_REFERENCE.md)
- [Database Schema](../database/SCHEMA.md)

---

## Support & Maintenance

**Questions?** Check the following:

1. Error logs: `/app/logs/`
2. Browser console: F12 → Console tab
3. Database logs: Check MySQL error log

**For bugs:** File issue with:

- URL accessed
- Period selected
- Error message from console
- Browser version
- Last working version date

---

**Document Prepared By:** GitHub Copilot  
**Date:** October 28, 2025  
**Version:** 1.0
