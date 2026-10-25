-- Migration to add bank_charge_vat field to payment_reference table
-- This adds a column to store the calculated VAT amount on bank charges

ALTER TABLE `payment_reference` 
ADD COLUMN `bank_charge_vat` DECIMAL(15,4) DEFAULT 0.0000 AFTER `bank_charges`;