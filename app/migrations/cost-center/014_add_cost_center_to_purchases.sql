-- Migration: Add cost_center_id to sma_purchases table
-- This allows tracking which cost center each purchase belongs to
-- Run this SQL in your database to add the cost_center_id column

-- Step 1: Add cost_center_id column to sma_purchases table
ALTER TABLE `sma_purchases`
ADD COLUMN `cost_center_id` INT(11) NULL DEFAULT NULL COMMENT 'Foreign key to sma_cost_centers table' AFTER `warehouse_id`;

-- Step 2: Add index for better query performance
CREATE INDEX `idx_purchases_cost_center` ON `sma_purchases` (`cost_center_id`);

-- Step 3: Add index for combined warehouse and cost center queries
CREATE INDEX `idx_purchases_warehouse_cost_center` ON `sma_purchases` (
    `warehouse_id`,
    `cost_center_id`
);

-- Step 4: Add foreign key constraint (optional - only if you want strict referential integrity)
-- Uncomment the following lines if you want to enforce foreign key constraints:
-- ALTER TABLE `sma_purchases`
-- ADD CONSTRAINT `fk_purchases_cost_center`
-- FOREIGN KEY (`cost_center_id`) REFERENCES `sma_cost_centers`(`cost_center_id`)
-- ON DELETE SET NULL ON UPDATE CASCADE;