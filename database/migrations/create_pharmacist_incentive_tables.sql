-- Migration: Create Pharmacist Incentive Tables
-- Date: 2025-11-10
-- Description: Creates two tables for managing pharmacist incentives
--   1. pharmacist_incentive: Header table with pharmacist info
--   2. pharmacist_incentive_items: Line items with product/batch details

-- Table 1: Pharmacist Incentive Header
CREATE TABLE IF NOT EXISTS `pharmacist_incentive` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `pharmacist_id` INT(11) NOT NULL COMMENT 'Foreign key to sma_users (group_id=8)',
    `branch_code` VARCHAR(50) DEFAULT NULL COMMENT 'Branch code from loyalty_branches',
    `warehouse_id` INT(11) DEFAULT NULL COMMENT 'Warehouse ID from sma_warehouses',
    `status` ENUM('active', 'inactive', 'archived') DEFAULT 'active',
    `notes` TEXT DEFAULT NULL,
    `created_by` INT(11) DEFAULT NULL COMMENT 'User who created this incentive',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_pharmacist_id` (`pharmacist_id`),
    KEY `idx_branch_code` (`branch_code`),
    KEY `idx_warehouse_id` (`warehouse_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Pharmacist incentive header records';

-- Table 2: Pharmacist Incentive Items (Line Items)
CREATE TABLE IF NOT EXISTS `pharmacist_incentive_items` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `incentive_id` INT(11) NOT NULL COMMENT 'Foreign key to pharmacist_incentive',
    `product_id` INT(11) NOT NULL COMMENT 'Foreign key to sma_products',
    `batch_number` VARCHAR(50) DEFAULT NULL COMMENT 'Batch number from inventory',
    `expiry_date` DATE DEFAULT NULL COMMENT 'Expiry date from inventory',
    `supplier_id` INT(11) DEFAULT NULL COMMENT 'Supplier ID from purchase',
    `incentive_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Incentive percentage (0-100)',
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_incentive_id` (`incentive_id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_batch_number` (`batch_number`),
    KEY `idx_expiry_date` (`expiry_date`),
    KEY `idx_supplier_id` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Pharmacist incentive line items - minimal normalized data';

-- Add indexes for better query performance
CREATE INDEX `idx_pharmacist_branch` ON `pharmacist_incentive` (`pharmacist_id`, `branch_code`);
CREATE INDEX `idx_incentive_product` ON `pharmacist_incentive_items` (`incentive_id`, `product_id`);
CREATE INDEX `idx_product_batch` ON `pharmacist_incentive_items` (`product_id`, `batch_number`, `expiry_date`);
