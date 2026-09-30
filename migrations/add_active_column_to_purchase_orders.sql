-- Migration to add active column to purchase_orders table for soft delete functionality
-- Date: 2025-11-18

ALTER TABLE `sma_purchase_orders` 
ADD COLUMN `active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=deleted' AFTER `grand_deal_discount`;

-- Update existing records to active
UPDATE `sma_purchase_orders` SET `active` = 1 WHERE `active` IS NULL;

-- Add index for better query performance
CREATE INDEX `idx_active` ON `sma_purchase_orders` (`active`);
