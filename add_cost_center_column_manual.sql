-- Add cost_center_id column to sma_purchases table
-- Run this SQL if the PHP setup script fails

-- Check if the column already exists
SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'sma_purchases'
    AND COLUMN_NAME = 'cost_center_id';

-- If the above query returns no results, run the following:

-- Add cost_center_id column
ALTER TABLE sma_purchases
ADD COLUMN cost_center_id INT(11) NULL DEFAULT NULL COMMENT 'Foreign key to cost centers table'
AFTER warehouse_id;

-- Add index for performance
CREATE INDEX idx_purchases_cost_center ON sma_purchases (cost_center_id);

-- Verify the column was added
DESCRIBE sma_purchases;

-- Test query to see purchase data with new column
SELECT
    id,
    reference_no,
    warehouse_id,
    cost_center_id
FROM sma_purchases
LIMIT 3;