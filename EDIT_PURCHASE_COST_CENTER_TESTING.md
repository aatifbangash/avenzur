# Edit Purchase Cost Center Integration - Testing Notes

## Changes Made

### 1. Controller Changes (/app/controllers/admin/Purchases.php)

#### Added cost_center_id validation to edit method:
```php
$this->form_validation->set_rules('cost_center_id', 'Cost Center', 'required|is_natural_no_zero');
```

#### Added cost_center_id to data array being saved:
```php
'cost_center_id' => $this->input->post('cost_center_id'),
```

### 2. View Changes (/themes/blue/admin/views/purchases/edit.php)

#### Added Cost Center dropdown after warehouse dropdown:
```html
<div class="col-md-4">
    <div class="form-group">
        <label for="cost_center_id">Cost Center *</label>
        <select name="cost_center_id" id="cost_center_id" class="form-control select2" required="required" style="width:100%;" data-placeholder="Select Cost Center">
            <option value="">Select Cost Center</option>
        </select>
    </div>
</div>
```

#### Added localStorage for cost center:
```javascript
localStorage.setItem('pocost_center', '<?= $inv->cost_center_id ?? '' ?>');
```

#### Added complete AJAX functionality for cost center dropdown:
- Warehouse change handler to load cost centers
- Level 2 filtering (same as add purchase)
- Code-Name display format  
- Error handling and loading states
- localStorage integration

## Testing Checklist

### To Test:
1. **Load Edit Purchase page** - Check if cost center dropdown appears
2. **Warehouse selection** - Verify cost centers load when warehouse is selected  
3. **Cost center options** - Confirm only Level 2 cost centers show in Code-Name format
4. **Current cost center** - Verify existing cost_center_id from purchase record is pre-selected
5. **Form submission** - Test if cost_center_id saves correctly when editing purchase
6. **Validation** - Verify cost center is required for form submission

### Expected Behavior:
- Cost center dropdown appears after warehouse dropdown in edit form
- When warehouse is selected, cost centers load via AJAX
- Only Level 2 cost centers displayed in "CODE-NAME" format
- If purchase already has cost_center_id, it should be pre-selected
- Form requires cost center selection before submission
- Cost center value is saved when purchase is updated

### Test URL:
Access any existing purchase edit page like:
`/admin/purchases/edit/[PURCHASE_ID]`

### Database Check:
After editing and saving, verify:
```sql
SELECT id, reference_no, warehouse_id, cost_center_id 
FROM sma_purchases 
WHERE id = [PURCHASE_ID];
```

The cost_center_id field should contain the selected cost center ID.

## Notes:
- Uses the same get_cost_centers_by_warehouse AJAX endpoint as Add Purchase
- Maintains compatibility with existing edit purchase functionality
- Level 2 filtering and Code-Name format consistent with Add Purchase
- CSRF token handled properly for security