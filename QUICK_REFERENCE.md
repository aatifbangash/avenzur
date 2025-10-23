# Quick Reference: Role-Based Menu Organization

## Role Assignments

| Role              | Permission Field                   | Primary Functions             | Key Reports                |
| ----------------- | ---------------------------------- | ----------------------------- | -------------------------- |
| **Admin**         | `$Owner \|\| $Admin`               | Everything                    | All Reports                |
| **Warehouse**     | `warehouse_manager`                | Products, Stock, Transfers    | Inventory, Stock Reports   |
| **Operations**    | `operations_manager`               | Purchases, Returns, Suppliers | Purchase, Supplier Reports |
| **Sales**         | `sales_manager`                    | Sales, POS, Quotes            | Sales, Collection Reports  |
| **Customer Mgmt** | `customer_manager`                 | Customers, Payments           | Customer Reports           |
| **Finance**       | `accounts_finance` or `accountant` | Accounting, Payments          | Financial Reports          |
| **Pharmacist**    | `stock_pharmacist`                 | Stock Requests, POS           | Limited Access             |

---

## Menu Permission Patterns

### Warehouse Management Menu

```php
<?php if ($Owner || $Admin || (isset($GP['warehouse_manager']) && $GP['warehouse_manager'])) { ?>
    <!-- Warehouse menu items -->
<?php } ?>
```

### Operations Menu

```php
<?php if ($Owner || $Admin || (isset($GP['operations_manager']) && $GP['operations_manager'])) { ?>
    <!-- Operations menu items -->
<?php } ?>
```

### Sales Menu

```php
<?php if ($Owner || $Admin || (isset($GP['sales_manager']) && $GP['sales_manager'])) { ?>
    <!-- Sales menu items -->
<?php } ?>
```

### Customer Management Menu

```php
<?php if ($Owner || $Admin || (isset($GP['customer_manager']) && $GP['customer_manager'])) { ?>
    <!-- Customer menu items -->
<?php } ?>
```

### Finance Menu

```php
<?php if ($Owner || $Admin || (isset($GP['accountant']) && $GP['accountant'])) { ?>
    <!-- Finance menu items -->
<?php } ?>
```

### Pharmacist Menu

```php
<?php if (isset($GP['stock_pharmacist']) && $GP['stock_pharmacist']) { ?>
    <!-- Pharmacist menu items -->
<?php } ?>
```

---

## Report Groupings

### Inventory Reports → Warehouse Management

- Item Movement Report
- Inventory Trial Balance
- Stock Report
- Supplier Stock Report
- Inventory Ageing Report
- Transfer Items Report

### Operations Reports → Operations

- Daily Purchase Report
- Supplier Statement
- Supplier Trial Balance
- VAT Purchase Report
- Supplier Aging Report

### Sales Reports → Sales & POS

- Pharmacy Collections
- Sales by Category
- Sales by Items
- Pharmacist Commission
- Close Register Date Wise
- VAT Sale Report

### Customer Reports → Customer Management

- Customer Statement
- Customer Trial Balance
- Customer Aging Report

### Financial Reports → Accounts & Finance

- Balance Sheet
- Financial Position
- General Ledger Trial Balance
- General Ledger Statement
- Total Income Report
- All VAT Reports
- All Aging Reports
- All Trial Balance Reports

---

## Common Permission Checks

```php
// Check if user has access to products
if ($Owner || $Admin || (isset($GP['warehouse_manager']) && $GP['warehouse_manager']))

// Check if user can manage purchases
if ($Owner || $Admin || (isset($GP['operations_manager']) && $GP['operations_manager']))

// Check if user can process sales
if ($Owner || $Admin || (isset($GP['sales_manager']) && $GP['sales_manager']))

// Check if user can manage customers
if ($Owner || $Admin || (isset($GP['customer_manager']) && $GP['customer_manager']))

// Check if user can access finance
if ($Owner || $Admin || (isset($GP['accountant']) && $GP['accountant']))

// Check if user is pharmacist
if (isset($GP['stock_pharmacist']) && $GP['stock_pharmacist'])
```

---

## Menu Structure Snippet Example

```php
<!-- WAREHOUSE MANAGEMENT SECTION -->
<?php if ($Owner || $Admin || (isset($GP['warehouse_manager']) && $GP['warehouse_manager'])) { ?>
<li class="mm_warehouse">
    <a class="dropmenu" href="#">
        <i class="fa fa-barcode"></i>
        <span class="text"> <?= lang('Warehouse Management'); ?> </span>
        <span class="chevron closed"></span>
    </a>
    <ul>
        <!-- Products -->
        <li id="products_index">
            <a href="<?= admin_url('products'); ?>">
                <i class="fa fa-list"></i>
                <span class="text"> <?= lang('list_products'); ?></span>
            </a>
        </li>

        <!-- Reports Section -->
        <li class="divider"></li>
        <li class="mm_submenu_header">
            <span class="text"><?= lang('Inventory Reports'); ?></span>
        </li>
        <li id="reports_stock">
            <a href="<?= admin_url('reports/stock'); ?>">
                <i class="fa fa-bar-chart-o"></i>
                <span class="text"> <?= lang('Stock Report'); ?></span>
            </a>
        </li>
    </ul>
</li>
<?php } ?>
```

---

## Database Query for User Permissions

```sql
-- Check which permissions a user's group has
SELECT p.*, g.name as group_name
FROM sma_permissions p
JOIN sma_groups g ON p.group_id = g.id
WHERE p.group_id = (SELECT group_id FROM sma_users WHERE id = [USER_ID]);

-- List all users with specific role
SELECT u.username, u.email, g.name as group_name
FROM sma_users u
JOIN sma_groups g ON u.group_id = g.id
JOIN sma_permissions p ON g.id = p.group_id
WHERE p.warehouse_manager = 1;
```

---

## Files Modified/Created

| File                                               | Status         | Purpose              |
| -------------------------------------------------- | -------------- | -------------------- |
| `ROLE_BASED_MENU_STRUCTURE.md`                     | ✅ Created     | Complete blueprint   |
| `IMPLEMENTATION_SUMMARY.md`                        | ✅ Created     | Implementation guide |
| `QUICK_REFERENCE.md`                               | ✅ Created     | This file            |
| `db/migrations/add_role_based_permissions.sql`     | ✅ Created     | Database changes     |
| `themes/blue/admin/views/header.php`               | 🔄 In Progress | Menu reorganization  |
| `themes/blue/admin/views/settings/permissions.php` | ⏳ Pending     | Add role checkboxes  |
| `app/language/english/admin/sma_lang.php`          | ⏳ Pending     | Add translations     |
| `assets/custom/custom.css`                         | ⏳ Pending     | Menu styling         |

---

## Quick Commands

```bash
# Navigate to project
cd /Users/rajivepai/Projects/Avenzur/V2/avenzur

# Run database migration
mysql -u root -p allinthisnet_pharmacy < db/migrations/add_role_based_permissions.sql

# Backup header file
cp themes/blue/admin/views/header.php themes/blue/admin/views/header.php.backup

# Check git status
git status

# Create branch for changes
git checkout -b feature/role-based-menus
```

---

## Testing Users to Create

1. **Warehouse Manager Test User**

   - Group: Warehouse Staff
   - Permission: `warehouse_manager = 1`
   - Should see: Products, Transfers, Stock Requests, Inventory Reports

2. **Operations Manager Test User**

   - Group: Operations Staff
   - Permission: `operations_manager = 1`
   - Should see: Purchases, Returns, Suppliers, Operations Reports

3. **Sales Manager Test User**

   - Group: Sales Staff
   - Permission: `sales_manager = 1`
   - Should see: Sales, POS, Quotes, Sales Reports

4. **Pharmacist Test User**

   - Group: Pharmacist
   - Permission: `stock_pharmacist = 1`
   - Should see: Limited Stock Requests, POS

5. **Accountant Test User**
   - Group: Accountant
   - Permission: `accountant = 1`
   - Should see: Finance, All Financial Reports

---

## Icons Reference

| Module     | Icon Class       | Color Suggestion |
| ---------- | ---------------- | ---------------- |
| Warehouse  | `fa-barcode`     | Blue             |
| Operations | `fa-star`        | Orange           |
| Sales      | `fa-heart`       | Red              |
| Customer   | `fa-users`       | Green            |
| Finance    | `fa-money`       | Dark Green       |
| Pharmacist | `fa-star-o`      | Purple           |
| Reports    | `fa-bar-chart-o` | Light Blue       |
