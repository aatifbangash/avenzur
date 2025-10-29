# Cascading Warehouse Hierarchy - Visual Guide & Quick Start

**Last Updated:** October 2025  
**Status:** ✅ Ready for Testing

---

## Visual Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│        PERFORMANCE DASHBOARD - CASCADING HIERARCHY              │
└─────────────────────────────────────────────────────────────────┘

STEP 1: Company Level (Default View)
┌───────────────────────────────────────────────────────────────┐
│ Period: [Today ▼]  Warehouse: [-- All Warehouses -- ▼]       │
│         Pharmacy: [-- Select Pharmacy -- ▼]                  │
│                                      [Apply Filters]         │
└───────────────────────────────────────────────────────────────┘
└─→ Shows company-wide KPIs for entire organization


STEP 2: After Selecting Warehouse (e.g., "Main Warehouse")
┌───────────────────────────────────────────────────────────────┐
│ Period: [Today ▼]  Warehouse: [Main Warehouse ▼]             │
│         Pharmacy: [-- Select Pharmacy -- ▼]                  │
│                                      [Apply Filters]         │
└───────────────────────────────────────────────────────────────┘
│
├─→ Pharmacy dropdown now shows:
│   ├─ North Pharmacy
│   ├─ South Pharmacy
│   └─ East Pharmacy
│
└─→ Dashboard shows company metrics filtered by Main Warehouse


STEP 3: After Selecting Pharmacy (e.g., "North Pharmacy")
┌───────────────────────────────────────────────────────────────┐
│ Period: [Today ▼]  Warehouse: [Main Warehouse ▼]             │
│         Pharmacy: [North Pharmacy ▼]  Branch: [-- Select Branch -- ▼]
│                                      [Apply Filters]         │
└───────────────────────────────────────────────────────────────┘
│
├─→ Branch dropdown now shows:
│   ├─ Branch A
│   ├─ Branch B
│   └─ Branch C
│
├─→ Dashboard shows PHARMACY-LEVEL metrics
│
└─→ Branch Performance Table appears below KPI cards:
    ┌─────────────────────────────────────────────────────────┐
    │ Branch Performance                                      │
    ├─────────────────────────────────────────────────────────┤
    │ Branch      │ Revenue │ Net Rev │ Profit │ Margin │View │
    ├─────────────────────────────────────────────────────────┤
    │ Branch A    │ 50,000  │ 45,000  │ 5,000  │ 10%    │View │
    │ Branch B    │ 75,000  │ 68,000  │ 7,000  │ 9.3%   │View │
    │ Branch C    │ 60,000  │ 54,000  │ 6,000  │ 10%    │View │
    └─────────────────────────────────────────────────────────┘


STEP 4: After Clicking "View" on Branch (e.g., Branch A)
┌───────────────────────────────────────────────────────────────┐
│ Period: [Today ▼]  Warehouse: [Main Warehouse ▼]             │
│         Pharmacy: [North Pharmacy ▼]  Branch: [Branch A ▼]   │
│                                      [Apply Filters]         │
└───────────────────────────────────────────────────────────────┘
│
├─→ Dashboard shows BRANCH-LEVEL metrics
│
├─→ All KPI cards now show Branch A's specific performance
│
└─→ Best Products table shows top 5 products sold at Branch A
```

---

## URL Parameter Reference

### What Appears in URL After Each Selection

```
Company Level (Default)
URL: /admin/cost_center/performance?period=today
└─→ Shows: Company-wide metrics for all warehouses

Warehouse Selected
URL: /admin/cost_center/performance?period=today&warehouse_id=1
└─→ Shows: Company metrics filtered by warehouse
└─→ Pharmacy dropdown populated with warehouse's pharmacies

Pharmacy Selected
URL: /admin/cost_center/performance?period=today&warehouse_id=2
└─→ Note: warehouse_id = pharmacy_id (parameter reuse)
└─→ Shows: Pharmacy-level metrics
└─→ Branch dropdown visible with branch list
└─→ Branch table visible with sales data

Branch Selected
URL: /admin/cost_center/performance?period=today&level=branch&warehouse_id=5
└─→ Note: level=branch + warehouse_id=branch_id
└─→ Shows: Branch-level metrics
└─→ Zoom into specific branch performance
```

---

## Data Structure in Dropdowns

```
WAREHOUSE GROUPS DROPDOWN (Level 1)
├─ Main Warehouse (id: 1)
├─ Secondary Warehouse (id: 2)
└─ Distribution Center (id: 3)

PHARMACY DROPDOWN (Level 2 - Cascades from Warehouse)
├ When no warehouse selected:
│  ├─ North Pharmacy (id: 10, parent: null)
│  ├─ South Pharmacy (id: 11, parent: null)
│  └─ East Pharmacy (id: 12, parent: null)
│
└ When "Main Warehouse" selected:
   ├─ North Pharmacy (id: 10, parent: 1)
   └─ South Pharmacy (id: 11, parent: 1)

BRANCH DROPDOWN (Level 3 - Cascades from Pharmacy)
└ When "North Pharmacy" selected:
   ├─ Branch A (warehouse_id: 20)
   ├─ Branch B (warehouse_id: 21)
   └─ Branch C (warehouse_id: 22)
```

---

## JavaScript Event Flow

```
USER SELECTS WAREHOUSE
    ↓
JavaScript: warehouseSelect.onChange()
    ↓
Reset pharmacy dropdown value = ""
Reset branch dropdown value = ""
    ↓
Trigger: applyFiltersBtn.click()
    ↓
Build URL: ?period=today&warehouse_id=1
    ↓
Window redirect → Controller reloads view
    ↓
View renders with pharmacy list for warehouse_id=1
═════════════════════════════════════════════════════

USER SELECTS PHARMACY
    ↓
JavaScript: pharmacySelect.onChange()
    ↓
Reset branch dropdown value = ""
    ↓
Trigger: applyFiltersBtn.click()
    ↓
Build URL: ?period=today&warehouse_id=2 (where 2 is pharmacy_id)
    ↓
Window redirect → Controller reloads view
    ↓
Controller calls: get_pharmacy_with_branches(2, period)
    ↓
View renders with:
  ├─ Pharmacy-level KPI metrics
  ├─ Branch dropdown with branch list
  └─ Branch performance table
═════════════════════════════════════════════════════

USER SELECTS BRANCH
    ↓
JavaScript: branchSelect.onChange()
    ↓
Trigger: applyFiltersBtn.click()
    ↓
Build URL: ?period=today&level=branch&warehouse_id=5
    ↓
Window redirect → Controller reloads view
    ↓
View renders BRANCH-LEVEL dashboard
```

---

## Code Changes Summary

### ✅ Model: Added 3 Methods

```php
// Get all warehouse groups (top level)
get_warehouse_groups()

// Get pharmacies under specific warehouse
get_pharmacies_by_warehouse($warehouse_id)

// Get all pharmacies (fallback when no warehouse selected)
get_all_pharmacies()
```

### ✅ Controller: Updated performance() Method

- Added warehouse group fetching
- Added cascading pharmacy logic
- Added branch data loading when pharmacy selected
- Passed view_data with all three levels

### ✅ View: Added 3 Components

1. **Warehouse Dropdown** - Level 1 selector
2. **Pharmacy Dropdown** - Level 2 cascading selector
3. **Branch Sales Table** - Conditional display below KPI cards

### ✅ JavaScript: 4 Event Handlers

- Warehouse change → Reset pharmacy/branch, apply
- Pharmacy change → Reset branch, apply
- Branch change → Apply
- Apply button → Build intelligent URL

### ✅ CSS: New Button Styling

- `.btn-branch-view` - Styled "View" button for branch navigation

---

## Testing Scenarios

### Scenario 1: Company Level View

1. Open Performance Dashboard
2. Period: Select "Today"
3. Warehouse: Leave as "-- All Warehouses --"
4. Click "Apply Filters"

**Expected Result:**

- Dashboard shows company-wide KPIs
- All warehouse groups in warehouse dropdown
- Pharmacy dropdown shows all pharmacies (no parent)
- No branch dropdown visible
- No branch table visible

### Scenario 2: Filter by Warehouse

1. Start at Company Level
2. Select "Main Warehouse" from dropdown
3. System auto-triggers "Apply Filters"

**Expected Result:**

- Dashboard refreshes
- Warehouse dropdown shows "Main Warehouse" selected
- Pharmacy dropdown updates to show only Main Warehouse's pharmacies
- Branch dropdown hidden
- Branch table hidden

### Scenario 3: Drill Down to Pharmacy

1. Warehouse: "Main Warehouse" (selected)
2. Select "North Pharmacy" from Pharmacy dropdown
3. System auto-triggers "Apply Filters"

**Expected Result:**

- Dashboard shows pharmacy-level metrics
- Warehouse dropdown: "Main Warehouse" (selected)
- Pharmacy dropdown: "North Pharmacy" (selected)
- Branch dropdown: Visible with 3 branches
- Branch Performance Table: Visible with branch metrics

### Scenario 4: Navigate to Branch

1. Pharmacy: "North Pharmacy" (selected)
2. Branch Performance Table visible
3. Click "View" button on "Branch A" row

**Expected Result:**

- Dashboard navigates to branch-level view
- Warehouse dropdown: "Main Warehouse" (selected)
- Pharmacy dropdown: "North Pharmacy" (selected)
- Branch dropdown: "Branch A" (selected)
- Dashboard shows Branch A's specific metrics

### Scenario 5: Change Period While in Pharmacy

1. At Pharmacy level with pharmacy selected
2. Change Period from "Today" to "This Month"
3. System auto-triggers "Apply Filters"

**Expected Result:**

- Dashboard refreshes with new period
- All selections maintained (warehouse, pharmacy, branch)
- Branch table updates with month's data
- KPI cards update with month's data

---

## Database Schema Reference

### sma_warehouses Table Hierarchy

```
id  | name                | warehouse_type | parent_id | code
────┼─────────────────────┼────────────────┼───────────┼────────
1   | Main Warehouse      | warehouse      | NULL      | WH-001
2   | Secondary Warehouse | warehouse      | NULL      | WH-002
────┼─────────────────────┼────────────────┼───────────┼────────
10  | North Pharmacy      | pharmacy       | 1         | PH-001
11  | South Pharmacy      | pharmacy       | 1         | PH-002
12  | East Pharmacy       | pharmacy       | 2         | PH-003
────┼─────────────────────┼────────────────┼───────────┼────────
20  | Branch A            | branch         | 10        | BR-001
21  | Branch B            | branch         | 10        | BR-002
22  | Branch C            | branch         | 12        | BR-003
```

---

## Troubleshooting

### Issue: Pharmacy dropdown is empty

**Cause:** Warehouse selected but no pharmacies with matching parent_id
**Fix:**

- Verify sma_warehouses table has pharmacy records with correct parent_id
- Verify parent_id matches warehouse selection

### Issue: Branch dropdown appears but branch table doesn't

**Cause:** `$branches_with_sales` not populated in controller
**Fix:**

- Verify `get_pharmacy_with_branches()` method is returning data
- Check that pharmacy_id parameter is correctly passed
- Verify stored procedure `sp_get_sales_analytics_hierarchical` is working

### Issue: "View" button link shows wrong URL

**Cause:** PHP variable $period not correctly passed to template
**Fix:**

- Verify $period is set in controller before passing to view
- Check $period format (should be YYYY-MM)

### Issue: Selections don't persist after "Apply Filters"

**Cause:** JavaScript not setting correct dropdown values from URL parameters
**Fix:**

- Verify $selected_warehouse_id and $selected_pharmacy_id are passed from controller
- Check PHP isset() checks in dropdown <option selected>

---

## Performance Notes

**Efficient Queries:**

- Uses existing stored procedure (sp_get_sales_analytics_hierarchical)
- Warehouse/pharmacy queries use indexed parent_id column
- No N+1 query patterns

**Caching Opportunities:**

- Cache warehouse_groups list (rarely changes)
- Cache pharmacies by warehouse (moderate change)
- Don't cache branch data (changes frequently with sales)

**Expected Load Times:**

- Warehouse selection: ~500ms (re-renders pharmacy dropdown)
- Pharmacy selection: ~1000ms (calls stored procedure for branches)
- Branch selection: ~500ms (updates dashboard metrics)

---

## Future Enhancements

**Phase 2 (Optional):**

- AJAX-based cascading (no full page reload)
- Search/autocomplete for large dropdown lists
- Branch comparison view (side-by-side metrics)
- Export branch performance data

**Phase 3 (Optional):**

- Mobile-optimized collapsible selectors
- Breadcrumb navigation
- Favorite/bookmark views
- Custom period ranges

---

## Quick Reference Commands

**Testing PHP Syntax:**

```bash
php -l themes/blue/admin/views/cost_center/performance_dashboard.php
php -l app/controllers/admin/Cost_center.php
php -l app/models/admin/Cost_center_model.php
```

**Checking Database:**

```bash
# Check warehouse hierarchy
SELECT id, name, warehouse_type, parent_id FROM sma_warehouses
WHERE warehouse_type IN ('warehouse', 'pharmacy', 'branch')
ORDER BY parent_id, id;

# Check specific warehouse's pharmacies
SELECT * FROM sma_warehouses
WHERE parent_id = 1 AND warehouse_type = 'pharmacy';
```

---

**Ready for Testing!** ✅

All components are in place and validated. You can now:

1. Open the Performance Dashboard
2. Test each cascading level
3. Verify branch sales table displays correctly
4. Click "View" buttons to navigate to branch dashboards
