-- Migration to add vat_ledger_id field to settings table
-- This adds a column to store the VAT ledger for bank charges

ALTER TABLE `sma_settings` 
ADD COLUMN `vat_ledger_id` INT(11) DEFAULT NULL AFTER `supplier_advance_ledger`;