# Cost Center Pre-selection Test Guide

## Implementation Summary

I've implemented the following changes to ensure the cost center selected during purchase creation is pre-selected in the edit page:

### 1. **HTML Pre-selection**

The cost center dropdown now includes a pre-selected option if the purchase has a cost_center_id:

```html
<?php if (!empty($purchase->cost_center_id)) { ?>
<option value="<?= $purchase->cost_center_id ?>" selected="selected">
	Loading current cost center...
</option>
<?php } ?>
```

### 2. **JavaScript Improvements**

- Enhanced the restoration logic to use both localStorage and the purchase record
- Added fallback mechanism to ensure cost center is set
- Added timeout to handle async loading of cost center options
- Improved option existence checking before setting value

### 3. **Multiple Restoration Methods**

#### Method 1: Direct from Purchase Record

```javascript
var saved_cost_center = localStorage.getItem('pocost_center') || '<?= $purchase->cost_center_id ?? '' ?>';
```

#### Method 2: localStorage Initialization

```javascript
if (pocost_center = '<?= $purchase->cost_center_id ?? '' ?>') {
    localStorage.setItem('pocost_center', pocost_center);
}
```

#### Method 3: Delayed Setting

```javascript
setTimeout(function () {
	if (
		$("#cost_center_id").find('option[value="' + current_cost_center + '"]')
			.length > 0
	) {
		$("#cost_center_id").val(current_cost_center);
	}
}, 500);
```

## Testing Instructions

### Test Case 1: Edit Existing Purchase with Cost Center

1. Go to any existing purchase that was created with a cost center
2. Click "Edit" on that purchase
3. **Expected**: The cost center dropdown should show the correct cost center pre-selected
4. **Verify**: The option should display in "CODE-NAME" format

### Test Case 2: Edit Purchase without Cost Center

1. Edit a purchase that was created before cost center was implemented
2. **Expected**: Dropdown shows "Select Cost Center" with no pre-selection
3. Select a warehouse and choose a cost center
4. **Expected**: Selected cost center should be saved when purchase is updated

### Test Case 3: Warehouse Change

1. Edit a purchase with existing cost center
2. Change the warehouse
3. **Expected**: Cost centers reload for new warehouse
4. **Expected**: If previous cost center is available for new warehouse, it remains selected

## Debug Information

### Check Purchase Record:

```sql
SELECT id, reference_no, warehouse_id, cost_center_id, supplier_id
FROM sma_purchases
WHERE cost_center_id IS NOT NULL
LIMIT 5;
```

### Browser Console Commands:

```javascript
// Check if cost center value is available
console.log(
	"Purchase Cost Center:",
	'<?= $purchase->cost_center_id ?? "None" ?>'
);

// Check localStorage
console.log("LocalStorage Cost Center:", localStorage.getItem("pocost_center"));

// Check dropdown current value
console.log("Dropdown Value:", $("#cost_center_id").val());

// Check available options
console.log(
	"Available Options:",
	$("#cost_center_id option")
		.map(function () {
			return this.value + ": " + this.text;
		})
		.get()
);
```

## Expected Results

1. **Immediate Pre-selection**: Cost center should be visible as selected when page loads
2. **Correct Display**: Should show in "CODE-NAME" format after warehouse loads options
3. **Persistence**: Selected cost center should remain after warehouse changes (if compatible)
4. **Saving**: Updated cost center should persist when purchase is saved

The implementation uses multiple fallback mechanisms to ensure reliability across different scenarios and timing conditions.
