-- Migration: Add additional_discount field to sma_sales table
-- Date: 2025-01-25
-- Purpose: Allow adding extra discount to individual invoices

-- Add additional_discount column
ALTER TABLE `sma_sales` 
ADD COLUMN `additional_discount` DECIMAL(25,4) DEFAULT 0.0000 
AFTER `order_discount`;

-- Add comment for documentation
ALTER TABLE `sma_sales` 
MODIFY COLUMN `additional_discount` DECIMAL(25,4) DEFAULT 0.0000 
COMMENT 'Additional discount that can be applied to invoice after creation';