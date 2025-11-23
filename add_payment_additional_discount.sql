-- Migration: Add additional_discount field to sma_payments table
-- Date: 2025-01-25
-- Purpose: Allow recording additional discount when collecting payment from customers

-- Add additional_discount column to payments table
ALTER TABLE `sma_payments` 
ADD COLUMN `additional_discount` DECIMAL(25,4) DEFAULT 0.0000 
AFTER `amount`;

-- Add comment for documentation
ALTER TABLE `sma_payments` 
MODIFY COLUMN `additional_discount` DECIMAL(25,4) DEFAULT 0.0000 
COMMENT 'Additional discount applied when collecting payment';