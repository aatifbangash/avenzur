# Finance Dashboard - Implementation & Usage Guide

## Quick Start

### Access the Dashboard

```
URL: /admin/accounts_dashboard
```

### Prerequisites

1. ✅ Accounts_dashboard_model created
2. ✅ Accounts_dashboard controller created
3. ✅ Modern dashboard view created
4. ✅ ECharts library (CDN)
5. ✅ Database stored procedure: `sp_get_accounts_dashboard()`

## Component Breakdown

### 1. Dashboard Controller

**File**: `/app/controllers/admin/Accounts_dashboard.php`

**Key Methods**:

```php
- index()                          // Load dashboard view
- get_data()                       // AJAX: Fetch all dashboard data
- get_purchase_items_expanded()    // AJAX: Paginated purchase items
- export()                         // AJAX: Export data
- get_summary_stats()              // AJAX: KPI statistics
```

### 2. Dashboard Model

**File**: `/app/models/admin/Accounts_dashboard_model.php`

**Key Methods**:

```php
- get_dashboard_data($type, $date)        // Call stored procedure
- get_purchase_items_expanded($type, ...) // Get purchase items with pagination
- get_customer_details($ids)              // Get customer info
- get_date_conditions($type, $date)       // Helper: Calculate date ranges
```

### 3. Dashboard View

**File**: `/themes/blue/admin/views/finance/accounts_dashboard.php`

**Structure**:

- CSS: Horizon UI design system (inline)
- HTML: Semantic structure with data containers
- JavaScript: ECharts initialization and data rendering

## Data Flow

### Page Load

```
1. User accesses /admin/accounts_dashboard
2. Controller::index() loads view
3. View renders with skeleton loaders
4. JavaScript DOMContentLoaded event fires
5. updateDashboard() called with default params
```

### Data Fetch

```
1. updateDashboard() triggered by filter change
2. Fetch GET /admin/accounts_dashboard/get_data
3. Controller validates params
4. Model calls stored procedure
5. Returns JSON with 7 datasets
6. JavaScript renders all components
```

### Data Sets

```json
{
  "success": true,
  "data": {
    "sales_summary": [...],           // Sales by branch
    "collection_summary": [...],      // Collections by period
    "purchase_summary": [...],        // Purchases by period
    "purchase_per_item": [...],       // Purchase items detailed
    "expiry_report": [...],           // Expiry information
    "customer_summary": [...],        // Customer data
    "overall_summary": {...}          // KPI totals
  },
  "report_type": "ytd",
  "reference_date": "2025-10-30"
}
```

## Configuration

### Filter Options

**Report Types**:

- `ytd` - Year to Date (Jan 1 to reference date)
- `monthly` - Current month only
- `today` - Current day only

**Date Selection**:

- Default: Today's date
- Format: YYYY-MM-DD
- Validation: Client-side format check + server-side strtotime

### Chart Configuration

#### Sales Trend Chart

```javascript
{
  type: 'line',
  smooth: true,
  areaStyle: { gradient: '0-1' },
  series: ['Gross Sales', 'Net Sales']
}
```

#### Collection Trend Chart

```javascript
{
  type: 'line',
  smooth: true,
  areaStyle: { filled },
  series: ['Collections']
}
```

#### Purchase Chart

```javascript
{
  type: 'bar',
  barRadius: [8, 8, 0, 0],
  series: ['Total Purchases']
}
```

#### Revenue Distribution Chart

```javascript
{
  type: 'pie',
  radius: '50%',
  series: ['Sales', 'Collections', 'Purchases']
}
```

#### Purchase Items Chart

```javascript
{
  type: 'barH' (horizontal),
  top: 10 items,
  sorted: by amount DESC
}
```

#### Customer Credit Chart

```javascript
{
  type: 'bar',
  grouped: true,
  series: ['Credit Limit', 'Balance']
}
```

## Styling Reference

### Color Scheme

```css
Primary:     #1a73e8 (Blue)
Success:     #05cd99 (Green)
Error:       #f34235 (Red)
Warning:     #ff9a56 (Orange)
Secondary:   #6c5ce7 (Purple)
```

### Responsive Breakpoints

```css
Mobile:        320px - 767px
Tablet:        768px - 1023px
Desktop:       1024px - 1919px
Large Desktop: 1920px+
```

### Z-Index Stack

```css
Tooltip:       9999
Modal:         9900
Dropdown:      100
Card Hover:    10
Content:       1
```

## JavaScript Functions

### Core Functions

#### `updateDashboard()`

Fetch data and render all components

```javascript
// Triggers on:
- Page load
- Report type change
- Date picker change
```

#### `renderKPICards()`

Render metric cards from summary data

```javascript
// Creates 5 cards:
- Total Sales
- Total Collections
- Total Purchases
- Net Sales
- Total Profit
```

#### `renderCharts()`

Initialize all ECharts

```javascript
// Creates 6 charts:
- Sales Trend
- Collection Trend
- Purchase Summary
- Revenue Distribution
- Purchase Items
- Customer Credit
```

#### `renderTables()`

Populate HTML tables with data

```javascript
// Tables:
- Sales Summary
- Purchase Items
```

### Utility Functions

#### `formatCurrency(value)`

Format number as currency

```javascript
Input:  1234567
Output: $1,234,567 SAR
```

#### `formatCurrencyShort(value)`

Format with K/M notation

```javascript
Input:  1234567
Output: 1.2M
```

#### `exportData(format)`

Export as JSON or CSV

```javascript
// Formats:
-"json" - "csv";
```

#### `sortTable(tableId, columnIndex)`

Sort table column ascending/descending

```javascript
// Toggles on multiple clicks
```

#### `filterTable(tableId, searchTerm)`

Filter table by search term

```javascript
// Real-time filtering
```

## Error Handling

### Client-Side Validation

```javascript
- Date format check: /^\d{4}-\d{2}-\d{2}$/
- Report type check: ['ytd', 'monthly', 'today']
- Pagination bounds: 1-500 items
```

### Server-Side Validation

```php
- Date validation with strtotime()
- Report type whitelist check
- Pagination bounds enforcement
- Input sanitization via CodeIgniter
```

### Error Display

```javascript
- Console logging: All errors
- User notification: Alert boxes (upgrade to toast)
- Chart fallback: Show "No data available"
- Table fallback: Empty state message
```

## Performance Tips

### Optimization Techniques

1. **Chart Disposal**: Previous charts disposed before new render
2. **Debounced Resize**: Window resize limited to animation frame
3. **Lazy Loading**: Charts only render when visible
4. **Data Caching**: Store last fetch to avoid redundant calls
5. **Table Pagination**: Show 20 items instead of all

### Load Time Targets

- Dashboard load: < 2 seconds
- Data fetch: < 500ms
- Chart render: < 300ms
- Table render: < 200ms

### Bundle Size

- HTML/CSS/JS: ~45KB
- ECharts: ~100KB
- Total: ~145KB (typical page)

## Customization Guide

### Changing Colors

**Update CSS Variables**:

```css
:root {
	--horizon-primary: #1a73e8; /* Change here */
	--horizon-success: #05cd99; /* Change here */
	--horizon-error: #f34235; /* Change here */
}
```

**Update Chart Colors**:

```javascript
// In each chart render function
color: ['#1a73e8', '#05cd99'],  // Change these
```

### Adding New Metrics

**Add to KPI Cards**:

```javascript
// In renderKPICards()
kpiData.push({
	label: "New Metric",
	value: formatCurrency(summary.new_field || 0),
	icon: "📊",
	color: "blue",
	trend: "+5.2%",
});
```

**Add to Summary**:

```php
// In model get_dashboard_data()
$results['new_summary'] = $query->result_array();
```

### Adding New Charts

**Create Chart Container**:

```html
<div class="chart-container">
	<div class="chart-header">
		<h3 class="chart-title">New Chart Title</h3>
		<p class="chart-subtitle">Subtitle here</p>
	</div>
	<div id="newChart" class="chart-content">...</div>
</div>
```

**Render Chart**:

```javascript
function renderNewChart(data) {
	const chartDom = document.getElementById("newChart");
	const chart = echarts.init(chartDom);

	const option = {
		/* chart config */
	};
	chart.setOption(option);

	charts.newChart = chart;
}
```

## Security Considerations

### CSRF Protection

✅ CodeIgniter built-in CSRF tokens on forms

### XSS Prevention

✅ Proper escaping in JavaScript rendering
✅ No direct DOM manipulation with user input
✅ Use textContent over innerHTML where possible

### SQL Injection Prevention

✅ Prepared statements in model
✅ CodeIgniter Query Builder used

### Input Validation

✅ Client-side: Format checks
✅ Server-side: Type and range validation
✅ Whitelist approach: Only allowed values

## Testing Scenarios

### Functional Tests

```javascript
1. Load dashboard - should show skeleton loaders
2. Change report type - should update all charts
3. Change date - should update all data
4. Click sort - should sort table
5. Type search - should filter table
6. Click export - should download file
7. Click refresh - should reload data
```

### Responsive Tests

```javascript
1. Resize to 320px - should stack cards vertically
2. Resize to 768px - should show 2-column layout
3. Resize to 1024px - should show full layout
4. Charts should resize automatically
5. Tables should scroll horizontally on small screens
```

### Data Tests

```javascript
1. Empty data set - should show "No data"
2. Zero values - should display 0 SAR
3. Negative values - should show as losses
4. Large values - should format correctly (1.2M)
5. Missing fields - should handle gracefully
```

### Browser Tests

```javascript
1. Chrome - full functionality
2. Firefox - full functionality
3. Safari - full functionality
4. Edge - full functionality
5. Mobile Chrome - touch interactions
6. Mobile Safari - touch interactions
```

## Troubleshooting

### Chart Not Rendering

1. Check browser console for errors
2. Verify ECharts library loaded: `window.echarts` exists
3. Check chart div exists in DOM
4. Verify data passed to chart is array
5. Clear browser cache and reload

### Data Not Loading

1. Check Network tab in DevTools
2. Verify API endpoint: `/admin/accounts_dashboard/get_data`
3. Check controller loaded admin_model correctly
4. Verify stored procedure exists and returns data
5. Check for PHP errors in response

### Export Not Working

1. Check export endpoint: `/admin/accounts_dashboard/export`
2. Verify format parameter: 'csv' or 'json'
3. Check server PHP memory limit
4. Verify file write permissions
5. Check browser download settings

### Table Not Sorting

1. Verify table headers have onclick handlers
2. Check console for JavaScript errors
3. Verify table rows exist before sort
4. Check column index is correct
5. Verify data in cells is sortable

### Performance Issues

1. Check browser Performance tab
2. Monitor JavaScript execution time
3. Profile ECharts rendering
4. Check DOM size (too many elements?)
5. Monitor network requests

## Support & Maintenance

### Regular Tasks

- Monitor dashboard performance metrics
- Review user feedback and logs
- Update ECharts library quarterly
- Test across browsers monthly
- Backup dashboard configurations

### Known Issues

- None documented yet

### Future Improvements

1. PDF export capability
2. Scheduled email reports
3. Dashboard customization per user
4. Real-time WebSocket updates
5. Advanced drill-down analytics

---

**Last Updated**: October 30, 2025
**Version**: 1.0 - Initial Release
**Maintained By**: Development Team
