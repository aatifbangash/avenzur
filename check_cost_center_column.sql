-- Check if cost_center_id column exists in sma_purchases table
SHOW COLUMNS FROM sma_purchases LIKE 'cost_center_id';

-- If the above query returns empty results, the column doesn't exist
-- Run this to add it:
-- ALTER TABLE `sma_purchases` ADD COLUMN `cost_center_id` INT(11) NULL DEFAULT NULL COMMENT 'Foreign key to sma_cost_centers table' AFTER `warehouse_id`;

-- Sample purchases data to check current state
SELECT id, reference_no, warehouse_id FROM sma_purchases LIMIT 3;

-- Available cost centers
SELECT
    cost_center_id,
    name,
    warehouse_id
FROM sma_cost_centers
LIMIT 5;