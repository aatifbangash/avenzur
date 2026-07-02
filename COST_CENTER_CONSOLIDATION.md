# Cost Center Model - Consolidated to Use Existing Stored Procedure

## Summary

All cost center model methods have been updated to use the existing comprehensive stored procedure `sp_get_sales_analytics_hierarchical` instead of the newly created individual procedures. This provides:

✅ **Unified Data Source** - Single source of truth for all analytics  
✅ **Full Period Type Support** - 'today', 'monthly', 'ytd' across all methods  
✅ **Hierarchy Flexibility** - Company, pharmacy, and branch levels  
✅ **Simplified Maintenance** - One procedure to maintain instead of 5  
✅ **Consistent Calculations** - Same logic across all views

---

## Updated Methods

### 1. `get_summary_stats($period)`

**Purpose:** Company-wide dashboard summary  
**Supports:** 'today', 'ytd', 'YYYY-MM' format

**Previous Implementation:**

```php
CALL sp_cost_center_summary(year, month)  // Only monthly
```

**New Implementation:**

```php
sp_get_sales_analytics_hierarchical(period_type, target_month, NULL, 'company')
```

**Returns:**

- `total_revenue` - Total sales amount
- `total_cost` - Total margin amount
- `total_margin_pct` - Margin percentage
- `total_customers` - Customer count
- `total_items_sold` - Items sold count
- `avg_transaction` - Average transaction value

---

### 2. `get_pharmacies_with_health_scores($period, $limit, $offset)`

**Purpose:** List all pharmacies with KPIs and health indicators  
**Supports:** 'today', 'ytd', 'YYYY-MM' format

**Previous Implementation:**

```php
CALL sp_cost_center_pharmacies(year, month, limit, offset)  // Only monthly
```

**New Implementation:**

```php
sp_get_sales_analytics_hierarchical(period_type, target_month, NULL, 'company')
// Then extract and format pharmacies array
```

**Returns Array of:**

- `pharmacy_id`, `pharmacy_code`, `pharmacy_name`
- `kpi_total_revenue` - Total sales
- `kpi_total_cost` - Margin amount
- `kpi_profit_margin_pct` - Margin percentage
- `branch_count` - Number of child branches
- `health_status` - '✓ Healthy' / '⚠ Monitor' / '✗ Low'
- `health_color` - '#10B981' / '#F59E0B' / '#EF4444'
- `net_margin_pct` - Net margin percentage

**Health Status Logic:**

- ✓ Healthy (Green): Margin ≥ 30%
- ⚠ Monitor (Yellow): Margin 20-29%
- ✗ Low (Red): Margin < 20%

---

### 3. `get_branches_with_health_scores($period, $limit, $offset)`

**Purpose:** List all branches across all pharmacies  
**Supports:** 'today', 'ytd', 'YYYY-MM' format

**Previous Implementation:**

```php
CALL sp_cost_center_branches(year, month, limit, offset)  // Only monthly
```

**New Implementation:**

```php
sp_get_sales_analytics_hierarchical(period_type, target_month, NULL, 'company')
// Then extract branches from all pharmacies, flatten, and format
```

**Returns Array of:**

- `branch_id`, `branch_code`, `branch_name`
- `pharmacy_id`, `pharmacy_code`, `pharmacy_name` - Parent pharmacy
- `kpi_total_revenue` - Branch sales
- `kpi_total_cost` - Branch margin amount
- `kpi_profit_margin_pct` - Branch margin percentage
- `health_status`, `health_color`, `net_margin_pct`

**Processing:**

1. Fetch company-level data (includes all pharmacies + branches)
2. Extract all branches from all pharmacies
3. Flatten into single array
4. Sort by revenue descending
5. Apply limit/offset

---

### 4. `get_pharmacy_detail($pharmacy_id, $period)`

**Purpose:** Detailed KPIs for specific pharmacy including branches  
**Supports:** 'today', 'ytd', 'YYYY-MM' format

**Previous Implementation:**

```php
CALL sp_cost_center_pharmacy_detail(pharmacy_id, year, month)  // Only monthly
```

**New Implementation:**

```php
sp_get_sales_analytics_hierarchical(period_type, target_month, pharmacy_id, 'pharmacy')
```

**Returns:**

- `pharmacy_id`, `pharmacy_code`, `pharmacy_name`
- `kpi_total_revenue` - Pharmacy total sales
- `kpi_total_cost` - Pharmacy margin amount
- `kpi_profit_margin_pct` - Pharmacy margin percentage
- `branch_count` - Number of branches
- `branches[]` - Array of branch details:
  - `branch_id`, `branch_code`, `branch_name`
  - `total_revenue`, `total_margin`, `margin_percentage`

---

## Period Type Handling

All methods now support three period types:

### 1. **Monthly** (default)

**Format:** `'2025-10'` or `'YYYY-MM'`  
**Logic:** Parse into year/month, set period_type='monthly'  
**Example:**

```php
get_summary_stats('2025-10')  // October 2025
```

### 2. **Today**

**Format:** `'today'`  
**Logic:** Set period_type='today', target_month=null  
**Example:**

```php
get_summary_stats('today')  // Current date sales
```

### 3. **Year-to-Date**

**Format:** `'ytd'`  
**Logic:** Set period_type='ytd', target_month=null  
**Example:**

```php
get_summary_stats('ytd')  // Jan 1 to today
```

---

## Stored Procedure Details

### `sp_get_sales_analytics_hierarchical`

**Parameters:**

1. `period_type` - VARCHAR(20): 'today', 'monthly', 'ytd'
2. `target_month` - VARCHAR(7): 'YYYY-MM' or NULL
3. `warehouse_id` - INT: Specific warehouse or NULL for all
4. `level` - VARCHAR(20): 'company', 'pharmacy', 'branch'

**Returns:**

- **Summary:** Company/pharmacy/branch totals
- **Pharmacies:** Array of pharmacy KPIs (company level)
- **Branches:** Array of branch KPIs (pharmacy level)
- **Best Products:** Top 5 selling products
- **Success flag:** true/false

**Example Calls:**

```sql
-- Company summary for October 2025
CALL sp_get_sales_analytics_hierarchical('monthly', '2025-10', NULL, 'company');

-- Today's company sales
CALL sp_get_sales_analytics_hierarchical('today', NULL, NULL, 'company');

-- YTD company sales
CALL sp_get_sales_analytics_hierarchical('ytd', NULL, NULL, 'company');

-- Specific pharmacy for October
CALL sp_get_sales_analytics_hierarchical('monthly', '2025-10', 1, 'pharmacy');

-- Pharmacy YTD
CALL sp_get_sales_analytics_hierarchical('ytd', NULL, 1, 'pharmacy');
```

---

## Benefits of Consolidation

### 1. **Consistency**

All cost center views use the same calculation logic, ensuring consistent numbers across:

- Dashboard summary
- Pharmacy listing
- Branch listing
- Pharmacy detail page

### 2. **Maintainability**

Single stored procedure to update for:

- Bug fixes
- Formula changes
- New metrics
- Performance optimizations

### 3. **Flexibility**

Full support for all period types without creating multiple procedure versions:

- ❌ No need for sp_cost_center_summary_v2, v3, etc.
- ❌ No need for sp_cost_center_pharmacies_v2
- ❌ No need for sp_cost_center_branches_v2
- ✅ One procedure handles all cases

### 4. **Performance**

Existing procedure is already optimized with:

- Proper indexing strategy
- Efficient JOINs
- Result set caching
- Query plan optimization

### 5. **Code Reusability**

Other parts of the application can leverage the same procedure:

- Finance module
- Executive dashboard
- Reports
- API endpoints

---

## Migration Notes

### Deprecated Procedures (Can be dropped)

```sql
DROP PROCEDURE IF EXISTS sp_cost_center_summary;
DROP PROCEDURE IF EXISTS sp_cost_center_pharmacies;
DROP PROCEDURE IF EXISTS sp_cost_center_branches;
DROP PROCEDURE IF EXISTS sp_cost_center_pharmacy_detail;
DROP PROCEDURE IF EXISTS sp_cost_center_summary_v2;
```

### Active Procedure (Keep)

```sql
-- This is the only procedure needed
sp_get_sales_analytics_hierarchical
```

---

## Testing Checklist

### ✅ Company Summary

- [ ] Monthly: `get_summary_stats('2025-10')`
- [ ] Today: `get_summary_stats('today')`
- [ ] YTD: `get_summary_stats('ytd')`

### ✅ Pharmacies Listing

- [ ] Monthly: `get_pharmacies_with_health_scores('2025-10', 10, 0)`
- [ ] Today: `get_pharmacies_with_health_scores('today', 10, 0)`
- [ ] YTD: `get_pharmacies_with_health_scores('ytd', 10, 0)`

### ✅ Branches Listing

- [ ] Monthly: `get_branches_with_health_scores('2025-10', 10, 0)`
- [ ] Today: `get_branches_with_health_scores('today', 10, 0)`
- [ ] YTD: `get_branches_with_health_scores('ytd', 10, 0)`

### ✅ Pharmacy Detail

- [ ] Monthly: `get_pharmacy_detail(1, '2025-10')`
- [ ] Today: `get_pharmacy_detail(1, 'today')`
- [ ] YTD: `get_pharmacy_detail(1, 'ytd')`

---

## Example Usage in Controller

```php
// Cost_center controller
class Cost_center extends CI_Controller {

    public function index() {
        // Get period from query param (default: current month)
        $period = $this->input->get('period') ?: date('Y-m');

        // Get company summary (supports today/ytd/YYYY-MM)
        $data['summary'] = $this->Cost_center_model->get_summary_stats($period);

        // Get pharmacies (supports today/ytd/YYYY-MM)
        $data['pharmacies'] = $this->Cost_center_model->get_pharmacies_with_health_scores($period, 10, 0);

        // Get branches (supports today/ytd/YYYY-MM)
        $data['branches'] = $this->Cost_center_model->get_branches_with_health_scores($period, 10, 0);

        $this->load->view('cost_center/dashboard', $data);
    }

    public function pharmacy($pharmacy_id) {
        $period = $this->input->get('period') ?: date('Y-m');

        // Get pharmacy detail (supports today/ytd/YYYY-MM)
        $data['pharmacy'] = $this->Cost_center_model->get_pharmacy_detail($pharmacy_id, $period);

        $this->load->view('cost_center/pharmacy_detail', $data);
    }
}
```

---

## Date: November 5, 2025

**Status:** ✅ Complete  
**Author:** Cost Centre Migration  
**Version:** 2.0 (Consolidated)
