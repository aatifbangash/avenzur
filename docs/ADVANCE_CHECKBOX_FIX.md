# Advance Settlement Checkbox Fix

## Issue

The "Settle with Advance" checkbox had the following problems:

1. It was located outside the invoice table, not next to the advance information
2. Once checked, the advance adjustment amount would always show even when unchecked
3. Not responsive to checkbox state changes

## Changes Made

### 1. Moved Checkbox to Table Row

**Location**: `themes/blue/admin/views/suppliers/add_payment.php`

The checkbox is now displayed directly in the advance adjustment row within the invoice table:

- **Before**: Checkbox was in a separate column outside the table
- **After**: Checkbox is in the third column of the advance adjustment row, right next to the available advance amount

### 2. Enhanced Responsive Behavior

Added event handlers to make the checkbox properly responsive:

```javascript
// Event handler for the checkbox in the table
$(document).on("change", "#settle-with-advance-table", function (e) {
	// Sync with original checkbox
	$("#settle-with-advance").prop("checked", $(this).is(":checked"));
	updateAdvanceSettlementCalculation();
});
```

### 3. Fixed Amount Display Logic

Modified the calculation to properly reset to 0 when unchecked:

```javascript
// In loadInvoices function
var is_checked = $("#settle-with-advance").is(":checked");
var adjustable_amount =
	is_checked && shortage > 0 ? Math.min(shortage, current_advance_balance) : 0;
```

### 4. Synchronized Checkbox States

- The original checkbox (now hidden) and the table checkbox are synchronized
- When one changes, the other updates automatically
- Both trigger the same calculation update

## UI Changes

### Advance Adjustment Row (Blue Background)

```
| Available Advance to Adjust: 500.00 | [✓] Settle with Advance | 300.00 |
```

**Columns**:

1. **Column 1-2 (colspan=2)**: Shows "Available Advance to Adjust: X.XX" with green amount
2. **Column 3**: Contains the checkbox with label "Settle with Advance"
3. **Column 4**: Shows the calculated adjustment amount (updates based on checkbox state)

## Behavior

### When Checkbox is Checked

- Calculates shortage: `Total Invoice Amount - Payment Entered`
- Displays adjustable amount: `min(Available Advance, Shortage)`
- Amount shows how much advance will be used

### When Checkbox is Unchecked

- Adjustable amount resets to **0.00**
- Advance is not used in the settlement
- Payment is processed as regular cash payment only

### Real-time Updates

The adjustment amount updates automatically when:

- Checkbox is toggled
- Payment amount changes
- Individual invoice payment amounts change
- Supplier selection changes

## Technical Details

### Hidden Original Checkbox

The original checkbox is kept but hidden (`display: none`) to maintain form submission compatibility:

```html
<div class="col-md-4" style="display: none;">
	<input
		type="checkbox"
		id="settle-with-advance"
		name="settle_with_advance"
		value="1"
		disabled
	/>
</div>
```

### New Table Checkbox

Created dynamically in the invoice table:

```javascript
advance_adjust_html +=
	'<td><label class="checkbox-inline" style="margin-top: 5px;">';
advance_adjust_html +=
	'<input type="checkbox" id="settle-with-advance-table" style="margin-right: 5px;"> ';
advance_adjust_html += "Settle with Advance</label></td>";
```

### Checkbox Synchronization

Both checkboxes stay in sync through event handlers:

- User clicks table checkbox → Hidden checkbox updates → Calculation runs
- Hidden checkbox changes (programmatically) → Table checkbox updates → Calculation runs

## Testing Checklist

- [x] Checkbox appears in the advance adjustment row
- [x] Checkbox is next to available advance amount
- [x] When checked, shows calculated adjustment amount
- [x] When unchecked, adjustment amount resets to 0.00
- [x] Amount updates when payment amount changes
- [x] Amount updates when invoice payments change
- [x] Checkbox disabled when no advance available
- [x] Checkbox disabled when advance ledger not configured
- [x] Form submission includes checkbox value (settle_with_advance)

## Files Modified

1. **View**: `/themes/blue/admin/views/suppliers/add_payment.php`
   - Modified advance adjustment row creation in `loadInvoices()`
   - Added checkbox to table row (column 3)
   - Changed colspan from 3 to 2 for first columns
   - Added event handler for `#settle-with-advance-table`
   - Updated checkbox sync logic
   - Hidden original checkbox section
   - Enhanced `updateAdvanceSettlementCalculation()` to respect checkbox state
   - Modified `updateAdvanceRowInTable()` to update amount based on checkbox

## User Experience Improvements

1. **Better Visual Context**: Checkbox is now directly adjacent to the advance information
2. **Clearer Intent**: Users can see available advance and toggle settlement in one place
3. **Immediate Feedback**: Amount adjusts instantly when checkbox state changes
4. **Intuitive Layout**: All advance-related information is in one table row
5. **Responsive**: Amount properly resets when unchecked (was a bug before)

## Example Scenario

**Invoice Total**: $1,000  
**Payment Entered**: $600  
**Available Advance**: $500

**When Checkbox Checked**:

- Shortage: $1,000 - $600 = $400
- Adjustable Amount: min($500, $400) = **$400**
- Total Settlement: $600 (cash) + $400 (advance) = $1,000

**When Checkbox Unchecked**:

- Adjustable Amount: **$0**
- Total Settlement: $600 (cash only)

## Notes

- The hidden checkbox maintains backward compatibility with form submission
- Table checkbox is created dynamically only when advance is available
- Both checkboxes are disabled when advance ledger is not configured
- Event handlers use jQuery's delegated events for dynamic elements
