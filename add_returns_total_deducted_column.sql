-- Add returns_total_deducted column to sma_sales table
ALTER TABLE sma_sales 
ADD COLUMN returns_total_deducted DECIMAL(25,4) DEFAULT 0 AFTER additional_discount;
