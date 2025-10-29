# Pharma Group Feature - Quick Reference

## What Was Built

A complete Pharma Group management system under Settings → Organization Setup with:

- ✅ Add new pharmacy groups
- ✅ Edit existing groups
- ✅ Delete groups (with cascading deletes)
- ✅ Responsive UI with modals
- ✅ Real-time AJAX operations
- ✅ Database transactions for data integrity

## File Summary

### Controllers

**File**: `/app/controllers/admin/Organization_setup.php`

- `add_pharma_group()` - Create pharma group with 3-table insert
- `get_pharma_group_details()` - Fetch single group details
- `update_pharma_group()` - Update group across tables
- `delete_pharma_group()` - Delete with cascade

### Models

**File**: `/app/models/admin/Loyalty_model.php`

- `insertPharmGroup()` - 3-table transaction insert
- `getPharmGroup()` - Get single group
- `getAllPharmGroups()` - Get all groups
- `updatePharmGroup()` - Update operation
- `deletePharmGroup()` - Delete with cascades
- `generateUUID()` - UUID v4 generator

### Views

**File**: `/themes/blue/admin/views/settings/pharmacy_hierarchy.php`

- New "Pharma Groups" tab (first tab)
- Add Pharma Group modal with form
- Edit Pharma Group modal with form
- Pharma Groups table with actions
- JavaScript handlers for all operations

## Database Tables Affected

```
┌─────────────────────────────────────┐
│ sma_warehouses                      │
│ warehouse_type = 'pharmaGroup'      │
└─────────────────────────────────────┘
           ↓ external_id FK
┌─────────────────────────────────────┐
│ loyalty_companies                   │
│ Stores company/group record         │
└─────────────────────────────────────┘
           ↓ company_id FK
┌─────────────────────────────────────┐
│ loyalty_pharmacy_groups             │
│ Links company to external warehouse │
└─────────────────────────────────────┘
```

## How to Use

### 1. Create a Pharma Group

```bash
POST /admin/organization_setup/add_pharma_group
Params:
  - code: "PG-001"
  - name: "Main Pharmacy Group"
  - address: "123 Main St"
  - phone: "+966 50 123 4567"
  - email: "info@group.com" (optional)
  - country: 8 (default)
```

### 2. View Pharma Groups

```bash
GET /admin/organization_setup/get_pharmacy_groups
Returns array of all pharma groups
```

### 3. Get Group Details

```bash
GET /admin/organization_setup/get_pharma_group_details?id=UUID
Returns single group details with warehouse info
```

### 4. Update Pharma Group

```bash
POST /admin/organization_setup/update_pharma_group
Params: id, code, name, address, phone, email (optional), country
```

### 5. Delete Pharma Group

```bash
POST /admin/organization_setup/delete_pharma_group
Params: id
Note: Deletes group AND all pharmacies/branches under it
```

## Frontend Usage

### JavaScript Functions

```javascript
// Load all groups into table
loadPharmaGroups();

// Edit a specific group
editPharmaGroup(id);

// Delete with confirmation
deletePharmaGroup(id, name);
```

### Form Handling

```javascript
// Add form automatically submits via AJAX
$('#form_add_pharma_group').on('submit', ...)

// Edit form automatically submits via AJAX
$('#form_edit_pharma_group').on('submit', ...)
```

## UI/UX Features

- 🎨 Gradient headers (green for add, blue for edit)
- 📱 Responsive modals (95% width, max 1200px)
- ✨ Animated alerts (SweetAlert2 integration)
- 🔄 Real-time table updates
- ⚠️ Confirmation dialogs before delete
- 📋 Clean two-column form layout
- 🎯 Inline edit/delete buttons

## Validation

### Server-side Rules

- `code` - required, unique in sma_warehouses
- `name` - required, unique in loyalty_pharmacy_groups
- `address` - required
- `phone` - required
- `email` - valid email format (if provided)

### Validation in Model

```php
// Check for duplicates before insert
$existing = $this->db->select('id')
    ->from('sma_warehouses')
    ->where('code', $code)
    ->get()->row();
```

## Transaction Handling

All operations wrapped in database transactions:

```php
$this->db->trans_start();
// Step 1: Insert warehouse
// Step 2: Insert company
// Step 3: Insert group
$this->db->trans_complete();

if ($this->db->trans_status() === false) {
    return error;
}
```

## Error Responses

### Success

```json
{
	"success": true,
	"message": "Pharmacy Group created successfully",
	"data": {
		"pharmacy_group_id": "uuid-value",
		"company_id": "uuid-value",
		"warehouse_id": 123
	}
}
```

### Error

```json
{
	"success": false,
	"message": "Pharmacy group code already exists"
}
```

## Key Implementation Details

1. **Three-Table Pattern**: Warehouse + Company + Group

   - Maintains both operational (warehouse) and loyalty (company/group) data
   - Enables future integration with budgeting and loyalty features

2. **UUID Generation**: Custom UUID v4 generator

   - Loyalty tables use UUID for distributed IDs
   - Maintains data consistency

3. **Cascade Deletes**: Maintains referential integrity

   - Deleting group deletes pharmacies, branches, warehouses
   - All done in single transaction

4. **AJAX-First**: All operations via AJAX

   - Page never reloads
   - Real-time updates
   - Better UX

5. **Responsive Design**: Works on all devices
   - Mobile-optimized modals
   - Touch-friendly buttons
   - Readable tables

## Testing Commands

```bash
# Check syntax
php -l app/controllers/admin/Organization_setup.php
php -l app/models/admin/Loyalty_model.php

# View changes
git diff app/controllers/admin/Organization_setup.php
git diff app/models/admin/Loyalty_model.php
git diff themes/blue/admin/views/settings/pharmacy_hierarchy.php
```

## Troubleshooting

### Groups not loading

- Check browser console for AJAX errors
- Verify `get_pharmacy_groups` endpoint is accessible
- Check database connection

### Form validation failing

- Verify all required fields are filled
- Check for duplicate code/name
- Review validation errors in response

### Delete not working

- Confirm group ID is valid
- Check CSRF token is correct
- Review server logs for transaction errors

### Modal not showing

- Verify Bootstrap is loaded
- Check for JavaScript errors in console
- Ensure modal IDs match button data-target

## Integration Points

- **Pharmacies**: Use pharma_group_id for hierarchy
- **Budgets**: Can allocate to pharma group level
- **Users**: Can assign to pharma groups
- **Loyalty Programs**: Group-level configurations
- **Reports**: Filter by pharma group

## Future Development

- [ ] Bulk operations
- [ ] Import/export CSV
- [ ] Advanced filtering
- [ ] Audit logging
- [ ] Approval workflows
- [ ] Sub-groups support
- [ ] Group-level budgets
- [ ] Permission integration

---

**Status**: ✅ Complete  
**Last Updated**: October 29, 2025  
**Version**: 1.0  
**Tested On**: Chrome, Firefox, Safari (Latest)
