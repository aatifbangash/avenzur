-- Migration for Pharmacy Hierarchy Setup
-- Date: 2025-10-24
-- Purpose: Create loyalty_pharmacies and loyalty_branches tables, add parent_id to sma_warehouses

-- Add parent_id column to sma_warehouses if it doesn't exist
ALTER TABLE `sma_warehouses`
ADD COLUMN `parent_id` INT(11) NULL DEFAULT NULL COMMENT 'Parent warehouse ID for hierarchy' AFTER `warehouse_type`;

-- Create loyalty_pharmacies table
CREATE TABLE IF NOT EXISTS `loyalty_pharmacies` (
    `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
    `pharmacy_group_id` INT(11) NOT NULL COMMENT 'Reference to pharmacy group',
    `warehouse_id` INT(11) NOT NULL COMMENT 'Reference to pharmacy warehouse',
    `code` VARCHAR(50) NOT NULL COMMENT 'Unique pharmacy code' UNIQUE,
    `name` VARCHAR(255) NOT NULL COMMENT 'Pharmacy name',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Update timestamp',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_pharmacy_code` (`code`),
    KEY `fk_pharmacy_group` (`pharmacy_group_id`),
    KEY `fk_warehouse` (`warehouse_id`),
    CONSTRAINT `fk_loyalty_pharmacies_group` FOREIGN KEY (`pharmacy_group_id`) REFERENCES `loyalty_pharmacy_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_loyalty_pharmacies_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `sma_warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Pharmacy locations linked to pharmacy groups';

-- Create loyalty_branches table
CREATE TABLE IF NOT EXISTS `loyalty_branches` (
    `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique identifier',
    `pharmacy_id` INT(11) NOT NULL COMMENT 'Reference to pharmacy warehouse',
    `warehouse_id` INT(11) NOT NULL COMMENT 'Reference to branch warehouse',
    `code` VARCHAR(50) NOT NULL COMMENT 'Unique branch code' UNIQUE,
    `name` VARCHAR(255) NOT NULL COMMENT 'Branch name',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation timestamp',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Update timestamp',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_branch_code` (`code`),
    KEY `fk_pharmacy` (`pharmacy_id`),
    KEY `fk_warehouse` (`warehouse_id`),
    CONSTRAINT `fk_loyalty_branches_pharmacy` FOREIGN KEY (`pharmacy_id`) REFERENCES `sma_warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_loyalty_branches_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `sma_warehouses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'Branch locations linked to pharmacies';

-- Add indexes for better query performance
CREATE INDEX idx_sma_warehouses_parent_id ON `sma_warehouses` (`parent_id`);

CREATE INDEX idx_sma_warehouses_warehouse_type ON `sma_warehouses` (`warehouse_type`);

CREATE INDEX idx_loyalty_pharmacies_group_id ON `loyalty_pharmacies` (`pharmacy_group_id`);

CREATE INDEX idx_loyalty_pharmacies_warehouse_id ON `loyalty_pharmacies` (`warehouse_id`);

CREATE INDEX idx_loyalty_branches_pharmacy_id ON `loyalty_branches` (`pharmacy_id`);

CREATE INDEX idx_loyalty_branches_warehouse_id ON `loyalty_branches` (`warehouse_id`);