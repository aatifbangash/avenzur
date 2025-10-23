# Role-Based Menu Structure Implementation

## Overview

This document outlines the reorganized menu structure for the Avenzur pharmacy management system, grouped by user roles with their related reports integrated into each functional area.

## Roles and Permissions

### 1. Admin Role

- **Access**: All modules and features
- **Permission Check**: `$Owner || $Admin`
- **Description**: Full system access including settings, user management, and all operational modules

### 2. Accounts & Finance Role

- **Permission Field**: `$GP['accounts_finance']`
- **Modules**:
  - Finance/Accounts Management
  - Chart of Accounts
  - Journal Entries
  - Payments (Both Customer and Supplier)
  - Financial Reports
- **Reports Included**:
  - Balance Sheet
  - Financial Position
  - VAT Reports (Purchase & Sale)
  - Customer/Supplier Aging
  - Customer/Supplier Trial Balance
  - Customer/Supplier Statements
  - General Ledger Trial Balance
  - General Ledger Statement

### 3. Warehouse Role

- **Permission Field**: `$GP['warehouse_manager']`
- **Modules**:
  - Products Management
  - Stock Adjustments
  - Transfers
  - Stock Requests
  - Inventory Management
- **Reports Included**:
  - Item Movement Report
  - Inventory Trial Balance
  - Stock Reports
  - Supplier Stock Report
  - Inventory Ageing Report
  - Transfer Items Report

### 4. Operations Role

- **Permission Field**: `$GP['operations_manager']`
- **Modules**:
  - Purchase Requisitions
  - Purchases
  - Returns (Customer & Supplier)
  - Supplier Management
  - Supplier Payments
  - Truck Registration
- **Reports Included**:
  - Daily Purchase Report
  - Supplier Statement
  - Supplier Trial Balance
  - VAT Purchase Report
  - Supplier Aging Report

### 5. Sales Role

- **Permission Field**: `$GP['sales_manager']`
- **Modules**:
  - Sales Management
  - POS Sales
  - Quotes
  - Sales Processing
- **Reports Included**:
  - Sales Reports
  - Pharmacy Collections
  - Sales by Category
  - Sales by Items
  - Pharmacist Commission
  - Close Register Date Wise
  - POS Sales Reports
  - VAT Sale Report

### 6. Customer Management Role

- **Permission Field**: `$GP['customer_manager']`
- **Modules**:
  - Customer Management
  - Customer Payments
  - Customer Service
- **Reports Included**:
  - Customer Statement
  - Customer Trial Balance
  - Customer Aging Report
  - VAT Sale Report

### 7. Pharmacist Role

- **Permission Field**: `$GP['stock_pharmacist']` (already exists)
- **Modules**:
  - Stock Requests (Limited)
  - POS Access
  - Inventory Check
  - Product View (Read-only)
- **Reports Included**:
  - Stock Requests
  - Inventory Check

---

## Complete Menu Structure

### Dashboard

- **Visible to**: All roles
- **Route**: `admin/welcome`

### 1. Warehouse Management

**Visible to**: Admin, Warehouse Role
**Permission Check**: `$Owner || $Admin || (isset($GP['warehouse_manager']) && $GP['warehouse_manager'])`

#### Sub-menus:

- **Products**

  - List Products
  - Add Product
  - Import Products
  - Print Barcode Label
  - Quantity Adjustments
  - Add Adjustment

- **Transfers**

  - List Transfers
  - Add Transfer

- **Stock Requests**

  - Inventory Check
  - Opened PR (if stock_request_view permission)
  - List Purchase Requests (if stock_request_view permission)

- **Inventory Reports** (Section Header)
  - Item Movement Report
  - Inventory Trial Balance
  - Stock Report
  - Supplier Stock Report
  - Inventory Ageing Report
  - Transfer Items Report

---

### 2. Operations

**Visible to**: Admin, Operations Role
**Permission Check**: `$Owner || $Admin || (isset($GP['operations_manager']) && $GP['operations_manager'])`

#### Sub-menus:

- **Purchase Requisition**

  - List Purchase Requisitions
  - Create Purchase Requisition

- **Purchases**

  - List Purchases
  - Add Purchase

- **Returns**

  - List Returns (Customer)
  - Add Return (Customer)
  - List Returns (Supplier)
  - Add Return (Supplier)

- **Suppliers**

  - List Suppliers
  - Add Supplier

- **Supplier Payments**

  - Add Supplier Payment
  - List Supplier Payments

- **Truck Registration** (if permission)

  - List Truck Registration
  - Add Truck Registration

- **Operations Reports** (Section Header)
  - Daily Purchase Report
  - Supplier Statement
  - Supplier Trial Balance
  - VAT Purchase Report (Invoice)
  - Supplier Aging Report

---

### 3. Sales & POS

**Visible to**: Admin, Sales Role
**Permission Check**: `$Owner || $Admin || (isset($GP['sales_manager']) && $GP['sales_manager'])`

#### Sub-menus:

- **Sales**

  - List Sales
  - POS Sales (if POS enabled)
  - POS Sales Date Wise (if POS enabled)
  - Add Sale

- **Quotes**

  - List Quotes
  - Add Quote

- **Sales Reports** (Section Header)
  - Pharmacy Collections
  - Sales by Category
  - Sales by Items
  - Pharmacist Commission
  - Transfer Items Report
  - Close Register Date Wise
  - VAT Sale Report (Invoice)

---

### 4. Customer Management

**Visible to**: Admin, Customer Management Role
**Permission Check**: `$Owner || $Admin || (isset($GP['customer_manager']) && $GP['customer_manager'])`

#### Sub-menus:

- **Customers**

  - List Customers
  - Add Customer

- **Customer Payments**

  - Add Customer Payment
  - List Customer Payments

- **Customer Reports** (Section Header)
  - VAT Sale Report
  - Customer Trial Balance
  - Customer Statement
  - Customer Aging Report

---

### 5. Accounts & Finance

**Visible to**: Admin, Accounts & Finance Role
**Permission Check**: `$Owner || $Admin || (isset($GP['accountant']) && $GP['accountant'])`
_Note: accountant permission already exists_

#### Sub-menus:

- **Finance**

  - Accounts Settings
  - Chart of Accounts
  - Entries

- **Financial Reports** (Section Header)

  - Balance Sheet
  - Financial Position
  - General Ledger Trial Balance
  - General Ledger Statement
  - Total Income Report

- **Payment Management**

  - All Supplier Payments (view/manage)
  - All Customer Payments (view/manage)

- **Accounting Reports** (Section Header)
  - Supplier Trial Balance
  - Supplier Statement
  - Supplier Aging
  - Customer Trial Balance
  - Customer Statement
  - Customer Aging
  - VAT Purchase Report
  - VAT Sale Report

---

### 6. Pharmacist (Limited Access)

**Visible to**: Pharmacist Role
**Permission Check**: `isset($GP['stock_pharmacist']) && $GP['stock_pharmacist']`

#### Sub-menus:

- **Stock Requests**

  - Inventory Check
  - New Stock Request
  - List Stock Requests (My Requests)

- **POS** (if enabled)
  - POS Interface

---

### 7. People & Users (Admin Only)

**Visible to**: Admin Only
**Permission Check**: `$Owner || $Admin`

#### Sub-menus:

- **Users** (Owner only)

  - List Users
  - New User
  - List Billers
  - Add Biller

- **Customers**

  - List Customers
  - Add Customer

- **Suppliers**

  - List Suppliers
  - Add Supplier

- **Employees**
  - List Employees
  - Add Employee

---

### 8. Settings (Admin Only)

**Visible to**: Admin/Owner Only
**Permission Check**: `$Owner`

#### Sub-menus:

- Warehouses
- Departments
- Customer Groups
- Categories
- Email Templates
- Group Permissions
- Site Logs

---

### 9. Calendar

**Visible to**: Admin
**Permission Check**: `$Owner || $Admin`

---

### 10. Notifications

**Visible to**: Admin
**Permission Check**: `$Owner || $Admin`

#### Sub-menus:

- System Notifications
- List Rasd Notifications

---

## Database Changes Required

### New Permission Fields to Add

```sql
-- Add new role-based permission fields to sma_permissions table
ALTER TABLE `sma_permissions`
ADD `warehouse_manager` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Warehouse Management Role' AFTER `accountant`,
ADD `operations_manager` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Operations Management Role' AFTER `warehouse_manager`,
ADD `sales_manager` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Sales Management Role' AFTER `operations_manager`,
ADD `customer_manager` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Customer Management Role' AFTER `sales_manager`,
ADD `accounts_finance` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Accounts & Finance Role' AFTER `customer_manager`;

-- Note: stock_pharmacist and accountant fields already exist
```

---

## Implementation Notes

1. **Backward Compatibility**: The existing permission system (`$GP` array) is preserved. New role permissions are additive.

2. **Admin Override**: `$Owner || $Admin` conditions always grant access, maintaining administrative control.

3. **Multiple Roles**: A user can have multiple role permissions enabled, allowing flexible access control.

4. **Reports Integration**: Reports are now grouped within their functional areas instead of being in a separate Reports menu.

5. **CSS Styling**: Section headers within dropdowns may need CSS styling for visual separation:

```css
.mm_submenu_header {
	background-color: #f5f5f5;
	margin-top: 10px;
	padding: 5px 0;
}
.divider {
	height: 1px;
	background-color: #e5e5e5;
	margin: 5px 0;
}
```

---

## Migration Steps

1. **Backup**: Create backup of current `header.php` file
2. **Database**: Run SQL to add new permission fields
3. **Update Menu**: Implement new menu structure in `header.php`
4. **Configure Permissions**: Use System Settings > Group Permissions to assign new roles
5. **Test**: Test each role's menu visibility
6. **Adjust**: Fine-tune based on user feedback

---

## Benefits of This Structure

1. **Role-Focused**: Users see only what's relevant to their job function
2. **Integrated Reports**: Reports are contextually placed with their related functions
3. **Reduced Clutter**: Smaller, focused menus instead of one large menu
4. **Scalable**: Easy to add new features to appropriate role sections
5. **Intuitive**: Users can find features logically grouped by business function
