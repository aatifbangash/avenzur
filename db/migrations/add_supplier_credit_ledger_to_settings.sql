-- Migration: Add supplier_advance_ledger to sma_settings table
-- Date: 2025-10-14
-- Description: Adds supplier_advance_ledger column to store the default supplier advance ledger account ID

-- Add the column
ALTER TABLE `sma_settings` 
ADD COLUMN `supplier_advance_ledger` INT(11) NULL DEFAULT NULL 
COMMENT 'Supplier Advance Ledger Account ID' 
AFTER `ledger_account`;

-- Optional: Add index for better performance
-- CREATE INDEX `idx_supplier_advance_ledger` ON `sma_settings` (`supplier_advance_ledger`);

-- Verify the column was added
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
-- FROM INFORMATION_SCHEMA.COLUMNS
-- WHERE TABLE_NAME = 'sma_settings' AND COLUMN_NAME = 'supplier_advance_ledger';