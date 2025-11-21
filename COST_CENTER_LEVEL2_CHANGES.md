# Cost Center Filtering Changes - Level 2 with Code-Name Format

## Summary of Changes

This document outlines the changes made to filter cost centers to show **Level 2 only** and display them in **Code-Name** format (e.g., `CC001-Main Pharmacy Operations`).

## Files Modified

### 1. Model: `/app/models/admin/Cost_center_model.php`

**Method:** `get_cost_centers_by_warehouse($warehouse_id, $is_admin = false)`

**Changes:**

- Added filter `AND cc.cost_center_level = 2` to WHERE clauses
- Added new field `CONCAT(cc.cost_center_code, '-', cc.cost_center_name) as cost_center_display`
- Updated ORDER BY to sort by `cc.cost_center_code ASC, cc.cost_center_name ASC`

**Before:**

```sql
WHERE (w.id = {$warehouse_id} OR w.parent_id = {$warehouse_id})
```

**After:**

```sql
WHERE (w.id = {$warehouse_id} OR w.parent_id = {$warehouse_id}) AND cc.cost_center_level = 2
```

### 2. View: `/themes/blue/admin/views/purchases/add.php`

**Location:** AJAX success handler (around line 855)

**Changes:**

- Updated JavaScript to use `cost_center_display` field instead of `cost_center_name`
- Added fallback to manually construct Code-Name format if needed

**Before:**

```javascript
var ccName = costCenter.cost_center_name || costCenter.name || "Unknown";
cost_center_dropdown.append(
	'<option value="' + costCenter.cost_center_id + '">' + ccName + "</option>"
);
```

**After:**

```javascript
var ccDisplay =
	costCenter.cost_center_display ||
	costCenter.cost_center_code + "-" + costCenter.cost_center_name ||
	"Unknown";
cost_center_dropdown.append(
	'<option value="' + costCenter.cost_center_id + '">' + ccDisplay + "</option>"
);
```

## Expected Behavior

1. **Level Filtering:** Only cost centers with `cost_center_level = 2` will be shown
2. **Display Format:** Dropdown options will show in format: `CC001-Main Pharmacy Operations`
3. **Sorting:** Results sorted by cost center code first, then name
4. **Warehouse Context:** Still respects warehouse hierarchy (parent/child relationships)

## Testing

Use the following test files to verify functionality:

- `test_level2_cost_centers.php` - Database query testing
- `test_cost_center_dropdown.html` - Frontend dropdown testing

## Database Query Reference

The complete filtered query now looks like:

```sql
SELECT
    cc.cost_center_id,
    cc.cost_center_code,
    cc.cost_center_name,
    CONCAT(cc.cost_center_code, '-', cc.cost_center_name) as cost_center_display,
    cc.cost_center_level,
    cc.parent_cost_center_id,
    w.id as entity_id,
    w.name as entity_name,
    w.code as entity_code,
    CASE
        WHEN w.warehouse_type = 'warehouse' THEN 'pharmacy'
        ELSE w.warehouse_type
    END as entity_type,
    w.warehouse_type,
    w.parent_id
FROM sma_cost_centers cc
INNER JOIN sma_warehouses w ON cc.entity_id = w.id
WHERE (w.id = {warehouse_id} OR w.parent_id = {warehouse_id}) AND cc.cost_center_level = 2
ORDER BY
    cc.cost_center_code ASC,
    cc.cost_center_name ASC
```

## JSON Response Format

The AJAX endpoint now returns:

```json
{
	"success": true,
	"warehouse_id": 1,
	"cost_centers": [
		{
			"cost_center_id": "1",
			"cost_center_code": "CC001",
			"cost_center_name": "Main Pharmacy Operations",
			"cost_center_display": "CC001-Main Pharmacy Operations",
			"cost_center_level": 2,
			"entity_id": "1",
			"entity_name": "Main Pharmacy"
		}
	],
	"count": 1
}
```

## Notes

- The controller in `/app/controllers/admin/Purchases.php` already has the correct `get_cost_centers_by_warehouse()` method
- The Cost_center_model is loaded in the Purchases controller constructor
- Authentication and CSRF protection are already implemented
- The changes maintain backward compatibility while improving the display format
