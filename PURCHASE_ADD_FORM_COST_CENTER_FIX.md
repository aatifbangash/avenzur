# Purchase Add Form Cost Center Fix

## Issue

The cost center dropdown in the purchase add form (`/admin/purchases/add`) was not showing any cost centers because the JavaScript was expecting the old API response format but we had updated the API endpoint to return a new format.

## Problem Details

### Old API Response (Expected by Add Form):

```json
{
	"status": "success",
	"data": [{ "id": "1", "text": "Cost Center Name with Details" }]
}
```

### New API Response (What We Changed to):

```json
{
	"success": true,
	"cost_centers": [
		{
			"cost_center_id": "1",
			"cost_center_name": "Cost Center Name",
			"cost_center_code": "CC001",
			"entity_type": "pharmacy",
			"entity_name": "Main Pharmacy"
		}
	],
	"message": "Cost centers loaded successfully"
}
```

## Solution Applied

### Updated Add Form JavaScript

1. **Changed Response Handling**: Updated the AJAX success handler to use the new response format
2. **Simplified Display**: Show only the cost center name (like in edit form), not the complex text with entity details
3. **Added Debug Logging**: Console logs to help troubleshoot issues
4. **Improved Error Handling**: Better error messages and debugging information

### Key Changes in `/themes/blue/admin/views/purchases/add.php`:

```javascript
// OLD CODE:
if (response.status === "success" && response.data.length > 0) {
	$.each(response.data, function (index, item) {
		cost_center_dropdown.append(
			'<option value="' + item.id + '">' + item.text + "</option>"
		);
	});
}

// NEW CODE:
if (
	response.success &&
	response.cost_centers &&
	response.cost_centers.length > 0
) {
	$.each(response.cost_centers, function (index, costCenter) {
		var ccName = costCenter.cost_center_name || costCenter.name || "Unknown";
		cost_center_dropdown.append(
			'<option value="' +
				costCenter.cost_center_id +
				'">' +
				ccName +
				"</option>"
		);
	});
}
```

## Expected Results

✅ **Cost center dropdown shows list of cost centers**  
✅ **Displays simple cost center names (not complex details)**  
✅ **Filters by selected warehouse**  
✅ **Maintains localStorage persistence**  
✅ **Consistent with edit form behavior**

## Testing Steps

1. **Open purchase add form**: Navigate to `/admin/purchases/add`
2. **Select a warehouse**: Choose any warehouse from dropdown
3. **Check cost center dropdown**: Should populate with cost center names
4. **Change warehouse**: Cost centers should update accordingly
5. **Check browser console**: Should see debug logs showing successful API calls

## Debug Information

The fix includes console logging to help diagnose issues:

- `console.log('Cost center AJAX response:', response)` - Shows API response
- `console.log('XHR response:', xhr.responseText)` - Shows error details if AJAX fails

## Status

✅ **FIXED** - Purchase add form cost center dropdown now works correctly and shows simple cost center names like the edit form.
