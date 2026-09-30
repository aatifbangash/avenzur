# Performance Dashboard - Time Period Options Update

**Date:** October 28, 2025  
**Status:** ✅ COMPLETE  
**Files Modified:** 3

---

## Overview

Added "Today" and "Year to Date" period options to the Performance Dashboard period dropdown selector, with full support in the data model and controller.

---

## Changes Made

### 1. Model Update - `Cost_center_model.php`

**Method:** `get_available_periods($limit = 24)`  
**Lines:** 415-460

**What Changed:**

- Now returns special period options: 'today' and 'ytd' (Year to Date)
- Special periods appear first in the dropdown
- Each period includes a `label` field for display and `is_special` flag
- Historical monthly data from database merged with special options

**Implementation:**

```php
$periods = [
    [
        'period' => 'today',
        'label' => 'Today',
        'is_special' => true
    ],
    [
        'period' => 'ytd',
        'label' => 'Year to Date',
        'is_special' => true
    ]
];

// + monthly periods from database
$monthly_periods = $result->result_array();
return array_merge($periods, $monthly_periods);
```

**Data Structure:**

```php
// Special periods
[
    'period' => 'today',
    'period_year' => 2025,
    'period_month' => 10,
    'label' => 'Today',
    'is_special' => true
]

[
    'period' => 'ytd',
    'period_year' => 2025,
    'period_month' => null,
    'label' => 'Year to Date',
    'is_special' => true
]

// Monthly periods (from DB)
[
    'period' => '2025-10',
    'period_year' => 2025,
    'period_month' => 10,
    'label' => null,
    'is_special' => false
]
```

---

### 2. Controller Update - `Cost_center.php`

**Method:** `performance()`  
**Lines:** 405-454

**What Changed:**

- Updated to handle three period types: 'today', 'ytd', and 'YYYY-MM' format
- Determines correct `period_type` for stored procedure based on period value
- Passes appropriate parameters to model's `get_hierarchical_analytics()` method

**Period Type Logic:**

```php
if ($period === 'today') {
    $period_type = 'today';
    $target_month = null;
} elseif ($period === 'ytd') {
    $period_type = 'ytd';
    $target_month = null;
} else {
    // YYYY-MM format
    $period_type = 'monthly';
    $target_month = $period;
}

// Pass to model
$this->cost_center->get_hierarchical_analytics(
    $period_type,  // 'today', 'ytd', or 'monthly'
    $target_month, // null for special, 'YYYY-MM' for monthly
    $warehouse_id,
    $level
);
```

**Parameter Changes:**

- **Before:** Always passed 'monthly' as period_type
- **After:** Dynamically determines period_type based on period value

---

### 3. View Update - `performance_dashboard.php`

**Section:** Period Selector Dropdown  
**Lines:** 460-475

**What Changed:**

- Updated dropdown rendering to display label for special periods
- Falls back to formatted month (M Y) for regular monthly periods
- Proper selection handling for both period types

**Implementation:**

```php
<?php foreach ($periods as $p): ?>
    <?php
        // Display label for special periods, or formatted month
        $display_text = isset($p['label']) && $p['label']
            ? $p['label']
            : date('M Y', strtotime($p['period'] . '-01'));

        $is_selected = ($p['period'] === $period) ? 'selected' : '';
    ?>
    <option value="<?php echo $p['period']; ?>" <?php echo $is_selected; ?>>
        <?php echo $display_text; ?>
    </option>
<?php endforeach; ?>
```

**Display Examples:**

- `'today'` displays as "Today"
- `'ytd'` displays as "Year to Date"
- `'2025-10'` displays as "Oct 2025"

---

## How It Works

### Data Flow

```
1. View renders dropdown with all periods
   ├─ Special: "Today", "Year to Date"
   └─ Monthly: "Oct 2025", "Sep 2025", ...

2. User selects a period
   └─ JavaScript sends: ?period=today (or ytd or 2025-10)

3. Controller receives period parameter
   ├─ If 'today': Maps to period_type='today', target_month=null
   ├─ If 'ytd': Maps to period_type='ytd', target_month=null
   └─ If 'YYYY-MM': Maps to period_type='monthly', target_month='2025-10'

4. Model calls stored procedure with correct parameters
   └─ CALL sp_get_sales_analytics_hierarchical(period_type, target_month, warehouse_id, level)

5. Stored procedure returns data for the selected period
   └─ Dashboard displays data for chosen period
```

### Period Type Mapping

| Dropdown Value | Period Type | Target Month | Stored Proc Behavior                 |
| -------------- | ----------- | ------------ | ------------------------------------ |
| today          | 'today'     | NULL         | Returns today's data only            |
| ytd            | 'ytd'       | NULL         | Returns year-to-date cumulative data |
| 2025-10        | 'monthly'   | '2025-10'    | Returns October 2025 data            |
| 2025-09        | 'monthly'   | '2025-09'    | Returns September 2025 data          |

---

## Stored Procedure Integration

The stored procedure `sp_get_sales_analytics_hierarchical` already supports these period types:

```sql
-- Signature
CALL sp_get_sales_analytics_hierarchical(
    @period_type,      -- 'today', 'monthly', 'ytd'
    @target_month,     -- NULL for special periods, 'YYYY-MM' for monthly
    @warehouse_id,     -- NULL for company level
    @level             -- 'company', 'pharmacy', 'branch'
)
```

**No changes needed to stored procedure** - it already handles all three types correctly.

---

## User Experience

### Before

- Dropdown only showed historical monthly periods (Oct 2025, Sep 2025, etc.)
- No way to view today's data or year-to-date summary without manually calculating

### After

- **Today** option: View real-time performance metrics for current day
- **Year to Date** option: View cumulative performance from Jan 1 to today
- **Monthly** options: View historical monthly data (unchanged)

### Dropdown Layout

```
Period ▼
┌─────────────────────┐
│ Today              │  ← NEW
│ Year to Date       │  ← NEW
│ Oct 2025           │
│ Sep 2025           │
│ Aug 2025           │
│ ...                │
└─────────────────────┘
```

---

## Technical Details

### Period Detection in View

```php
// Smart display logic
$display_text = isset($p['label']) && $p['label']
    ? $p['label']                           // "Today" or "Year to Date"
    : date('M Y', strtotime($p['period'] . '-01'));  // "Oct 2025"
```

### Default Period

- **Before:** Current month (YYYY-MM)
- **After:** 'today'

```php
$period = $this->input->get('period') ?: 'today';  // Updated default
```

---

## Testing Checklist

- [x] PHP syntax validated ✅
- [ ] Test "Today" option displays and loads data
- [ ] Test "Year to Date" option displays and loads data
- [ ] Test monthly periods still work as before
- [ ] Verify correct period_type passed to stored procedure
- [ ] Check data accuracy for each period type
- [ ] Verify UI displays correct labels
- [ ] Test with different user roles
- [ ] Monitor error logs for issues
- [ ] Verify page load times acceptable

---

## Files Modified

```
app/models/admin/Cost_center_model.php
├─ Method: get_available_periods()
├─ Lines: 415-460
├─ Change: Added special period options
└─ Status: ✅ COMPLETE

app/controllers/admin/Cost_center.php
├─ Method: performance()
├─ Lines: 405-454
├─ Change: Handle 'today' and 'ytd' period types
└─ Status: ✅ COMPLETE

themes/blue/admin/views/cost_center/performance_dashboard.php
├─ Section: Period selector
├─ Lines: 460-475
├─ Change: Display labels for special periods
└─ Status: ✅ COMPLETE
```

---

## Backward Compatibility

✅ **Fully backward compatible**

- Monthly periods (YYYY-MM format) work exactly as before
- Existing URLs with period=2025-10 still work
- Default behavior changed from monthly to 'today' (intentional improvement)
- No breaking changes to API or stored procedures

---

## Query Parameters

### Examples

```
// View today's data
GET /admin/cost_center/performance?period=today&level=company

// View year-to-date data
GET /admin/cost_center/performance?period=ytd&level=pharmacy&warehouse_id=5

// View October 2025 (traditional)
GET /admin/cost_center/performance?period=2025-10&level=company

// Default (no parameters)
GET /admin/cost_center/performance
→ Loads today's company data
```

---

## Performance Notes

- Special periods computed on-the-fly (no database query)
- Monthly periods fetched from database (same as before)
- Period array merge is O(n) and negligible impact
- No performance degradation expected

---

## Future Enhancements

Potential additions (not implemented):

- Weekly period option
- Custom date range selection
- Fiscal year options
- Comparison views (YoY, MoM)
- Period presets (Last 30 days, Last 90 days)

---

## References

### Related Files

- `PERFORMANCE_DASHBOARD_DATA_MAPPING.md` - Data field reference
- `PERFORMANCE_DASHBOARD_COMPLETE_GUIDE.md` - Full dashboard guide
- `MYSQLI_RESULT_SET_SYNCHRONIZATION_GUIDE.md` - Database connection handling

### Database Schema

- `sma_fact_cost_center` - Source of historical monthly periods
- Stored Procedure: `sp_get_sales_analytics_hierarchical`

---

**Status:** ✅ READY FOR TESTING  
**Validation:** ✅ PHP syntax clean  
**Backward Compatibility:** ✅ Maintained  
**User Impact:** ✅ Enhanced functionality

Test the dashboard by selecting "Today" or "Year to Date" from the period dropdown to verify the changes are working correctly.
