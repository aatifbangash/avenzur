-- ============================================================================
-- Role-Based Menu Permissions - Database Migration Script
-- Purpose: Add new role permission fields to support reorganized menu structure
-- Date: October 16, 2025
-- ============================================================================

USE allinthisnet_pharmacy;

-- Add new role-based permission columns to sma_permissions table
ALTER TABLE `sma_permissions` 
ADD COLUMN `warehouse_manager` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Full access to Warehouse Management module including products, stock, transfers, inventory reports' AFTER `accountant`,
ADD COLUMN `operations_manager` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Full access to Operations module including purchases, requisitions, returns, suppliers' AFTER `warehouse_manager`,
ADD COLUMN `sales_manager` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Full access to Sales & POS module including sales, quotes, sales reports' AFTER `operations_manager`,
ADD COLUMN `customer_manager` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Full access to Customer Management including customers, payments, customer reports' AFTER `sales_manager`,
ADD COLUMN `accounts_finance` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Full access to Accounts & Finance module including financial reports, payments' AFTER `customer_manager`;

-- Verify the changes
SELECT
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = 'allinthisnet_pharmacy'
    AND TABLE_NAME = 'sma_permissions'
    AND COLUMN_NAME IN (
        'warehouse_manager',
        'operations_manager',
        'sales_manager',
        'customer_manager',
        'accounts_finance',
        'accountant',
        'stock_pharmacist'
    );

-- ============================================================================
-- Optional: Create default role groups with appropriate permissions
-- ============================================================================

-- Example: Update existing Accountant group to have accounts_finance permission
-- UPDATE sma_permissions
-- SET accounts_finance = 1
-- WHERE accountant = 1;

-- Example: Set warehouse manager permissions for warehouse staff group
-- UPDATE sma_permissions
-- SET warehouse_manager = 1
-- WHERE group_id = [YOUR_WAREHOUSE_STAFF_GROUP_ID];

-- Example: Set operations manager permissions
-- UPDATE sma_permissions
-- SET operations_manager = 1
-- WHERE group_id = [YOUR_OPERATIONS_GROUP_ID];

-- Example: Set sales manager permissions
-- UPDATE sma_permissions
-- SET sales_manager = 1
-- WHERE group_id = [YOUR_SALES_GROUP_ID];

-- Example: Set customer manager permissions
-- UPDATE sma_permissions
-- SET customer_manager = 1
-- WHERE group_id = [YOUR_CUSTOMER_SERVICE_GROUP_ID];

-- ============================================================================
-- Notes:
-- ============================================================================
-- 1. The 'accountant' field already exists and is used for Finance role
-- 2. The 'stock_pharmacist' field already exists for Pharmacist role
-- 3. Admin/Owner roles ($Owner || $Admin) automatically have all permissions
-- 4. Users can have multiple role permissions enabled for cross-functional access
-- 5. After running this script, configure permissions in:
--    System Settings > Group Permissions
-- ============================================================================