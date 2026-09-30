-- Migration: Add customer_advance_ledger to sma_settings table
-- Date: 2025-10-15
-- Description: Adds customer_advance_ledger column to store the default customer advance ledger account ID
--              This ledger will be used to park advance payments received from customers

-- Add the column
ALTER TABLE `sma_settings` 
ADD COLUMN `customer_advance_ledger` INT(11) NULL DEFAULT NULL 
COMMENT 'Customer Advance Ledger Account ID - used for advance payments from customers' 
AFTER `supplier_advance_ledger`;

-- Optional: Add index for better performance
-- CREATE INDEX `idx_customer_advance_ledger` ON `sma_settings` (`customer_advance_ledger`);

-- Verify the column was added
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
-- FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_NAME = 'sma_settings' AND COLUMN_NAME = 'customer_advance_ledger';