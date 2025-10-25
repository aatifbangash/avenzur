# Cost Center Dashboard - Quick Reference Card

## 📍 Navigation

| Component       | URL                                | File                        |
| --------------- | ---------------------------------- | --------------------------- |
| Dashboard       | `/admin/cost_center/dashboard`     | `cost_center_dashboard.php` |
| Pharmacy Detail | `/admin/cost_center/pharmacy/{id}` | `cost_center_pharmacy.php`  |
| Branch Detail   | `/admin/cost_center/branch/{id}`   | `cost_center_branch.php`    |

## 📊 Database

| Table/View                  | Purpose            | Key Columns                                               |
| --------------------------- | ------------------ | --------------------------------------------------------- |
| `sma_fact_cost_center`      | Daily transactions | warehouse_id, transaction_date, total_revenue, total_cogs |
| `sma_dim_pharmacy`          | Pharmacy master    | warehouse_id, warehouse_name, parent_warehouse_id         |
| `sma_dim_branch`            | Branch master      | warehouse_id, warehouse_name, pharmacy_id                 |
| `view_cost_center_pharmacy` | Pharmacy KPIs      | kpi_total_revenue, kpi_profit_loss, kpi_profit_margin_pct |
| `view_cost_center_branch`   | Branch KPIs        | Same + pharmacy_id                                        |
| `view_cost_center_summary`  | Company KPIs       | level, kpi_total_revenue, entity_count                    |

## 🔧 Key Methods

### Controller Methods

```php
// Main dashboard
$this->cost_center->dashboard()

// Pharmacy detail with branches
$this->cost_center->pharmacy($pharmacy_id)

// Branch detail with breakdown
$this->cost_center->branch($branch_id)

// API endpoints (AJAX)
$this->cost_center->get_pharmacies()
$this->cost_center->get_branches($pharmacy_id)
$this->cost_center->get_timeseries_data($entity_id, $months)
```

### Model Methods

```php
// Get data
$this->cost_center->get_summary_stats($period)
$this->cost_center->get_pharmacies_with_kpis($period, $sort_by)
$this->cost_center->get_pharmacy_with_branches($pharmacy_id, $period)
$this->cost_center->get_available_periods($limit)

// Validation
$this->cost_center->pharmacy_exists($pharmacy_id)
$this->cost_center->branch_exists($branch_id)
```

## 📱 Frontend Components

### View Variables Available

```php
// All views have access to:
$assets              // Base URL to theme assets
$Settings            // Site configuration
$period              // Current period (YYYY-MM)
$summary             // Company KPIs
$pharmacies          // Pharmacy array
$branches            // Branch array
$periods             // Available periods dropdown
```

### JavaScript Functions

```javascript
// Period selector
changePeriod(element); // Call: onchange="changePeriod(this)"

// Navigation
goToPharmacy(pharmacyId); // Navigate to pharmacy detail
// Call: onclick="goToPharmacy(id)"

// Table operations
sortTable(sortBy); // Sort pharmacy table
// Call: onclick="sortTable('revenue')"

// Charts
downloadChart(chartId); // Export chart as PNG
// Call: onclick="downloadChart('trendChart')"
initializeTrendChart(); // Initialize ECharts
// Runs automatically on page load
```

## 🎨 CSS Classes

```css
/* Cards */
.border-left-primary    /* Blue left border - primary */
/* Blue left border - primary */
.border-left-danger     /* Red left border - danger */
.border-left-success    /* Green left border - success */
.border-left-warning    /* Yellow left border - warning */

/* Responsive */
.container-fluid        /* Full width */
.col-md-3              /* 4 columns on desktop */
.col-md-6              /* 2 columns on desktop */
.col-12                /* Full width on mobile */

/* Utilities */
.mt-4                  /* Margin top (1rem) */
.mb-4                  /* Margin bottom (1rem) */
.text-right            /* Right align */
.text-dark             /* Dark gray text */
.text-muted            /* Light gray text */
.cursor-pointer; /* Hover effects */
```

## 📈 Chart Configuration

**Chart Type:** ECharts Line Chart
**Library:** `echarts.min.js` (1.03 MB)
**Location:** `/themes/blue/admin/assets/js/echarts.min.js`
**Series:** Revenue (Blue), Cost (Red)
**Y-axis:** Amount in SAR
**Responsive:** Auto-resize on window resize

## 🔐 Authentication

All methods require login. Verify in constructor:

```php
if (!$this->loggedIn) {
    $this->session->set_userdata('requested_page', $this->uri->uri_string());
    $this->sma->md("admin/login");  // Redirect to login
}
```

## ⚙️ Configuration

**Period Format:** `YYYY-MM` (e.g., "2025-10")
**Currency:** SAR (Saudi Riyal)
**Number Format:** 0,000.00 (comma separator, 2 decimals)
**Date Format:** "M Y" (e.g., "Oct 2025")

## 🐛 Debugging

```php
// Enable debug logging in controller
error_log('[COST_CENTER] Your message here');

// Check logs
tail -f /app/logs/log-*.php
grep COST_CENTER /app/logs/log-*.php
```

## 📋 Common Tasks

### Add New KPI Card

1. Update `get_summary_stats()` in model to include new field
2. Add new card HTML in view (copy existing card)
3. Update card data variable

### Change Chart Type

1. Edit `initializeTrendChart()` function
2. Change `type: 'line'` to `'bar'`, `'pie'`, etc.
3. Update series configuration

### Add New Period Filter

1. Update `get_available_periods()` in model
2. Database must have data for that period
3. Period selector will auto-update

### Add New Column to Table

1. Add column header in view table
2. Add cell data in table row loop
3. No model changes needed

## 🚀 Deployment

```bash
# 1. Apply migrations
./spark migrate --namespace Migrations

# 2. Verify database views
mysql -u admin -p retaj_aldawa < 005_create_views.sql

# 3. Clear browser cache
# Ctrl+Shift+Delete (Chrome/Firefox)
# Cmd+Shift+Delete (Mac)

# 4. Test dashboard
http://localhost:8080/avenzur/admin/cost_center/dashboard

# 5. Verify no console errors
# Open DevTools: F12 or Cmd+Option+I
```

## 📞 Quick Fixes

| Error                  | Fix                                                                                      |
| ---------------------- | ---------------------------------------------------------------------------------------- |
| Chart not rendering    | Check if ECharts loaded: `console.log(echarts)`                                          |
| Data empty             | Verify period has data: `SELECT * FROM view_cost_center_pharmacy WHERE period='2025-10'` |
| CSS not applied        | Hard refresh: Ctrl+Shift+R                                                               |
| 404 on drill-down      | Verify pharmacy/branch ID in URL                                                         |
| Period selector broken | Check `onchange="changePeriod(this)"` handler                                            |

## 📊 Query Examples

```sql
-- Check total revenue by pharmacy
SELECT pharmacy_name, SUM(kpi_total_revenue)
FROM view_cost_center_pharmacy
GROUP BY pharmacy_name;

-- Check data by period
SELECT DISTINCT period FROM view_cost_center_pharmacy
ORDER BY period DESC;

-- Check branch count
SELECT COUNT(*) FROM sma_dim_branch;

-- Check ETL execution
SELECT * FROM sma_etl_audit_log ORDER BY executed_at DESC LIMIT 5;
```

## 📁 File Structure Quick Reference

```
Controller: /app/controllers/admin/Cost_center.php
Model:      /app/models/admin/Cost_center_model.php
Views:      /themes/blue/admin/views/cost_center/*.php
Assets:     /themes/blue/admin/assets/js/echarts.min.js
Migrations: /app/migrations/cost-center/*.sql
Logs:       /app/logs/log-*.php
```

---

**Last Updated:** October 25, 2025  
**For:** Development Team  
**Project:** Cost Center Dashboard v1.0
