-- Add customer_discount_ledger field to sma_settings table
ALTER TABLE sma_settings
ADD COLUMN customer_discount_ledger INT(11) DEFAULT NULL
AFTER customer_advance_ledger;