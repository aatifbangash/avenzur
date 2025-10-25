# Pharmacy Hierarchy Setup UI - Documentation

**Version:** 1.0  
**Date:** October 24, 2025  
**Location:** Settings → Setup Organization → Pharmacy Hierarchy Setup

---

## Overview

The Pharmacy Hierarchy Setup UI provides a modern, tabbed interface for managing the complete organizational hierarchy:

```
Pharmacy Group (Company)
  ↓
Pharmacy
  ├── Main Warehouse
  └── Sub-Warehouses
  ↓
Branch
  ├── Warehouse(s)
```

---

## UI Components

### 1. **Tab Navigation**

Three main tabs organize the setup workflow:

- **Pharmacies Tab** - Add and manage pharmacies within a pharmacy group
- **Branches Tab** - Add and manage branches within pharmacies
- **Hierarchy View Tab** - Visualize the complete organization structure

---

### 2. **Pharmacies Tab**

**Purpose:** Create and manage pharmacy locations

**Components:**

- **Pharmacy Group Selector** (Dropdown)

  - Loads all pharmacy groups from `loyalty_pharmacy_group` table
  - Filtering table updates based on selection
  - AJAX endpoint: `GET /admin/loyalty/get_pharmacy_groups`

- **Add Pharmacy Button** (Primary)

  - Opens modal form for new pharmacy creation
  - Form Title: "Add Pharmacy"

- **Pharmacies Table**
  - Columns: ID | Code | Name | Address | Phone | Warehouse Type | Actions
  - Real-time filtering based on selected pharmacy group
  - Edit/Delete action buttons
  - AJAX endpoint: `GET /admin/loyalty/get_pharmacies?group_id={id}`

**Database Operations:**

- Creates entry in `sma_warehouses` (warehouse_type = "pharmacy", parent_id = NULL)
- Creates entry in `loyalty_pharmacies`
- Creates entry in `sma_warehouses` (warehouse_type = "mainwarehouse", parent_id = pharmacy_id)

---

### 3. **Branches Tab**

**Purpose:** Create and manage branches under pharmacies

**Components:**

- **Pharmacy Selector** (Dropdown)

  - Loads all pharmacies
  - Filters branches table
  - AJAX endpoint: `GET /admin/loyalty/get_all_pharmacies`

- **Add Branch Button** (Primary)

  - Opens modal form for new branch creation
  - Form Title: "Add Branch"

- **Branches Table**
  - Columns: ID | Code | Name | Pharmacy | Address | Phone | Actions
  - Real-time filtering based on selected pharmacy
  - Edit/Delete action buttons
  - AJAX endpoint: `GET /admin/loyalty/get_branches?pharmacy_id={id}`

**Database Operations:**

- Creates entry in `sma_warehouses` (warehouse_type = "branch", parent_id = pharmacy_id)
- Creates entry in `loyalty_branches`

---

### 4. **Hierarchy View Tab**

**Purpose:** Visualize the complete organizational structure

**Components:**

- **Hierarchy Tree Visualization**
  - Shows Pharmacy Group → Pharmacy → Branch tree structure
  - Color-coded nodes:
    - Pharmacy: Purple gradient
    - Branch: Pink gradient
  - Expandable/collapsible sections
  - Responsive layout

**Data Source:**

- AJAX endpoint: `GET /admin/loyalty/get_hierarchy_tree`

---

## Modal Forms

### Add Pharmacy Modal

**Title:** "Add Pharmacy" (with hospital icon)

**Fields:**

1. **Pharmacy Group** \* (Dropdown, Required)

   - Label: "Select parent company"
   - Populated from `loyalty_pharmacy_group`
   - AJAX: `GET /admin/loyalty/get_pharmacy_groups`

2. **Pharmacy Code** \* (Text Input, Required)

   - Placeholder: "PHARM001"
   - Validation: Unique in `sma_warehouses.code`
   - Help Text: "Unique code for this pharmacy"

3. **Pharmacy Name** \* (Text Input, Required)

   - Placeholder: "Enter pharmacy name"

4. **Address** \* (Textarea, Required)

   - Placeholder: "Enter complete address"
   - Rows: 3

5. **Phone** \* (Tel Input, Required)

   - Placeholder: "Enter phone number"

6. **Email** (Email Input, Optional)
   - Placeholder: "Enter email"

**Main Warehouse Section:**

- **Title:** "Main Warehouse" (collapsible panel)
- **Description:** "One main warehouse is automatically created for this pharmacy"

7. **Warehouse Code** \* (Text Input, Required)

   - Placeholder: "WH001"
   - Validation: Unique in `sma_warehouses.code`
   - Help Text: "Unique code for main warehouse"

8. **Warehouse Name** \* (Text Input, Required)

   - Placeholder: "Enter warehouse name"

9. **Warehouse Type** (Hidden Field)
   - Value: "mainwarehouse"

**Actions:**

- Cancel Button (Secondary)
- Add Pharmacy Button (Primary)

**API Endpoint:**

```
POST /admin/loyalty/add_pharmacy_setup
```

---

### Add Branch Modal

**Title:** "Add Branch" (with map-marker icon)

**Fields:**

1. **Pharmacy** \* (Dropdown, Required)

   - Label: "Select parent pharmacy"
   - Populated from all pharmacies
   - AJAX: `GET /admin/loyalty/get_all_pharmacies`

2. **Branch Code** \* (Text Input, Required)

   - Placeholder: "BR001"
   - Validation: Unique in `sma_warehouses.code`
   - Help Text: "Unique code for this branch"

3. **Branch Name** \* (Text Input, Required)

   - Placeholder: "Enter branch name"

4. **Address** \* (Textarea, Required)

   - Placeholder: "Enter complete address"
   - Rows: 3

5. **Phone** \* (Tel Input, Required)

   - Placeholder: "Enter phone number"

6. **Email** (Email Input, Optional)
   - Placeholder: "Enter email"

**Actions:**

- Cancel Button (Secondary)
- Add Branch Button (Primary)

**API Endpoint:**

```
POST /admin/loyalty/add_branch_setup
```

---

## API Endpoints

### View & Navigation Endpoints

```
GET /admin/loyalty/pharmacy_setup
├─ Renders the main pharmacy setup page
└─ Breadcrumb: Home > Loyalty > Pharmacy Hierarchy Setup
```

### AJAX Data Endpoints

```
GET /admin/loyalty/get_pharmacy_groups
├─ Response: { success: true, data: [{ id, code, name }, ...] }
└─ Used by: Pharmacy dropdown selectors

GET /admin/loyalty/get_pharmacies?group_id={id}
├─ Response: { success: true, data: [{ id, code, name, address, phone, warehouse_type }, ...] }
└─ Used by: Pharmacies table filtering

GET /admin/loyalty/get_all_pharmacies
├─ Response: { success: true, data: [{ id, code, name }, ...] }
└─ Used by: Branch pharmacy selector, Branch dropdown in Branches tab

GET /admin/loyalty/get_branches?pharmacy_id={id}
├─ Response: { success: true, data: [{ id, code, name, address, phone, warehouse_type, pharmacy_name }, ...] }
└─ Used by: Branches table filtering

GET /admin/loyalty/get_hierarchy_tree
├─ Response: { success: true, data: [
│   {
│     id, code, name,
│     pharmacies: [
│       {
│         id, code, name,
│         branches: [{ id, code, name }, ...]
│       }, ...
│     ]
│   }, ...
│ ]}
└─ Used by: Hierarchy visualization

POST /admin/loyalty/add_pharmacy_setup
├─ Data: {
│   pharmacy_group_id: int,
│   code: string,
│   name: string,
│   address: string,
│   phone: string,
│   email: string (optional),
│   warehouse_code: string,
│   warehouse_name: string,
│   warehouse_type: "mainwarehouse"
│ }
├─ Response: { success: true/false, message: string, data: { pharmacy_id, warehouse_id } }
└─ Creates: sma_warehouses (pharmacy), loyalty_pharmacies, sma_warehouses (mainwarehouse)

POST /admin/loyalty/add_branch_setup
├─ Data: {
│   pharmacy_id: int,
│   code: string,
│   name: string,
│   address: string,
│   phone: string,
│   email: string (optional)
│ }
├─ Response: { success: true/false, message: string, data: { branch_id } }
└─ Creates: sma_warehouses (branch), loyalty_branches

POST /admin/loyalty/delete_pharmacy
├─ Data: { id: int }
├─ Response: { success: true/false, message: string }
└─ Deletes: sma_warehouses (pharmacy), loyalty_pharmacies

POST /admin/loyalty/delete_branch
├─ Data: { id: int }
├─ Response: { success: true/false, message: string }
└─ Deletes: sma_warehouses (branch), loyalty_branches
```

---

## Database Tables

### sma_warehouses

Extended with new fields:

```sql
ALTER TABLE `sma_warehouses` ADD COLUMN `parent_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `sma_warehouses` ADD COLUMN `warehouse_type` VARCHAR(25) DEFAULT NULL;
-- warehouse_type values: 'warehouse', 'pharmacy', 'mainwarehouse', 'branch'
```

### loyalty_pharmacy_groups

(Already exists - selects from this)

### loyalty_pharmacies

New table to link pharmacies to groups:

```sql
CREATE TABLE `loyalty_pharmacies` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `pharmacy_group_id` INT(11) NOT NULL,
  `warehouse_id` INT(11) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`pharmacy_group_id`) REFERENCES `loyalty_pharmacy_groups`(`id`),
  FOREIGN KEY (`warehouse_id`) REFERENCES `sma_warehouses`(`id`)
);
```

### loyalty_branches

New table to link branches to pharmacies:

```sql
CREATE TABLE `loyalty_branches` (
  `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `pharmacy_id` INT(11) NOT NULL,
  `warehouse_id` INT(11) NOT NULL,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`pharmacy_id`) REFERENCES `sma_warehouses`(`id`),
  FOREIGN KEY (`warehouse_id`) REFERENCES `sma_warehouses`(`id`)
);
```

---

## Language Keys Needed

Add these to language files:

```php
$lang['pharmacy_hierarchy_setup'] = 'Pharmacy Hierarchy Setup';
$lang['manage_pharmacies'] = 'Manage Pharmacies';
$lang['pharmacy_description'] = 'Add and manage pharmacy locations within your pharmacy group.';
$lang['manage_branches'] = 'Manage Branches';
$lang['branch_description'] = 'Add and manage branches under pharmacies.';
$lang['hierarchy_view'] = 'Hierarchy View';
$lang['organization_hierarchy'] = 'Organization Hierarchy';
$lang['hierarchy_view_description'] = 'Visual representation of your organization structure.';
$lang['click_node_to_view_details'] = 'Click on any node to view details';
$lang['select_pharmacy_group'] = 'Select Pharmacy Group';
$lang['add_pharmacy'] = 'Add Pharmacy';
$lang['pharmacy'] = 'Pharmacy';
$lang['select_pharmacy'] = 'Select Pharmacy';
$lang['pharmacy_group'] = 'Pharmacy Group';
$lang['pharmacy_code'] = 'Pharmacy Code';
$lang['pharmacy_name'] = 'Pharmacy Name';
$lang['enter_pharmacy_name'] = 'Enter pharmacy name';
$lang['enter_pharmacy_info'] = 'Enter pharmacy information';
$lang['select_parent_company'] = 'Select parent company/pharmacy group';
$lang['main_warehouse'] = 'Main Warehouse';
$lang['main_warehouse_description'] = 'One main warehouse is automatically created for this pharmacy. You can add more sub-warehouses later.';
$lang['warehouse_code'] = 'Warehouse Code';
$lang['warehouse_name'] = 'Warehouse Name';
$lang['enter_warehouse_name'] = 'Enter warehouse name';
$lang['unique_warehouse_code'] = 'Unique code for main warehouse';
$lang['add_branch'] = 'Add Branch';
$lang['branch'] = 'Branch';
$lang['branch_code'] = 'Branch Code';
$lang['branch_name'] = 'Branch Name';
$lang['enter_branch_name'] = 'Enter branch name';
$lang['enter_branch_info'] = 'Enter branch information';
$lang['select_parent_pharmacy'] = 'Select parent pharmacy';
$lang['pharmacies'] = 'Pharmacies';
$lang['branches'] = 'Branches';
$lang['hierarchy_view'] = 'Hierarchy View';
$lang['warehouse_type'] = 'Warehouse Type';
$lang['no_hierarchy_data'] = 'No hierarchy data available. Add pharmacies and branches to get started.';
$lang['confirm_delete'] = 'Are you sure you want to delete this item?';
```

---

## User Flow

### Add New Pharmacy

1. User navigates to Settings → Loyalty → Pharmacy Hierarchy Setup
2. Selects "Pharmacies" tab
3. Selects pharmacy group from dropdown
4. Clicks "Add Pharmacy" button
5. Modal form appears with fields:
   - Pharmacy Code, Name, Address, Phone, Email
   - Main Warehouse Code, Name
6. User fills form and submits
7. System creates:
   - `sma_warehouses` entry (pharmacy)
   - `loyalty_pharmacies` entry
   - `sma_warehouses` entry (mainwarehouse as child)
8. Success notification appears
9. Pharmacies table updates
10. Hierarchy view refreshes

### Add New Branch

1. User navigates to Settings → Loyalty → Pharmacy Hierarchy Setup
2. Selects "Branches" tab
3. Selects pharmacy from dropdown
4. Clicks "Add Branch" button
5. Modal form appears with fields:
   - Branch Code, Name, Address, Phone, Email
   - Parent Pharmacy (pre-filled)
6. User fills form and submits
7. System creates:
   - `sma_warehouses` entry (branch with parent_id = pharmacy)
   - `loyalty_branches` entry
8. Success notification appears
9. Branches table updates
10. Hierarchy view refreshes

### View Hierarchy

1. User navigates to Settings → Loyalty → Pharmacy Hierarchy Setup
2. Selects "Hierarchy View" tab
3. System fetches complete hierarchy tree
4. Visual tree displays:
   - Pharmacy Groups (color-coded)
   - Pharmacies under each group
   - Branches under each pharmacy

---

## Frontend Technologies Used

- **Framework:** Bootstrap 3
- **UI Library:** AdminLTE
- **Dropdowns:** Select2
- **Forms:** Bootstrap form validation
- **Icons:** FontAwesome
- **AJAX:** jQuery

---

## Styling & Design

- **Color Scheme:**

  - Primary: #3c8dbc (Pharmacy)
  - Info: #0097bc (Branch)
  - Danger: #dd4b39 (Delete)

- **Responsive:**

  - Desktop: Full multi-column layout
  - Tablet: 1-column layout
  - Mobile: Stack layout with smaller fonts

- **Modern UI Elements:**
  - Card-based layout with rounded corners
  - Smooth transitions and hover effects
  - Collapsible panels
  - Icon-based action buttons
  - Status badges for warehouse types

---

## Error Handling

- Form validation errors displayed inline
- AJAX errors shown in notification/alert
- Transaction rollback on database errors
- User-friendly error messages
- Duplicate code validation before insert

---

## Security Considerations

- CSRF token validation (handled by CodeIgniter)
- AJAX request verification (`is_ajax_request()`)
- Form validation on server-side
- Input sanitization via CodeIgniter
- Authorization check in controller constructor
- Database transactions for data consistency

---

## Future Enhancements

1. **Edit Pharmacy/Branch** - Inline editing or modal form
2. **Bulk Operations** - Import/export from CSV
3. **Warehouse Management** - Add/remove sub-warehouses
4. **Advanced Hierarchy Visualization** - Interactive ECharts tree
5. **Audit Trail** - Track all changes with user info
6. **Permissions** - Role-based access control
7. **Multi-currency Support** - Different currencies per pharmacy
8. **KPI Dashboard** - Pharmacy performance metrics

---

## Testing Checklist

- [ ] Create pharmacy with valid data
- [ ] Create pharmacy with duplicate code (should fail)
- [ ] Create branch under pharmacy
- [ ] Delete pharmacy and verify cascade delete
- [ ] Delete branch and verify data cleanup
- [ ] Test dropdown filtering
- [ ] Test form validation messages
- [ ] Test AJAX error handling
- [ ] Test responsive layout on mobile
- [ ] Test hierarchy tree visualization
- [ ] Test browser compatibility (Chrome, Firefox, Safari)
- [ ] Test data persistence after page refresh

---

**End of Documentation**
