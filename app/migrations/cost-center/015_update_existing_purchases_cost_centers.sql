-- OPTIONAL: Update existing purchases with cost centers based on warehouse
-- Run this AFTER adding the cost_center_id column to assign cost centers to existing purchases

-- This query will assign cost centers to existing purchases based on their warehouse
-- It matches purchases to cost centers where the warehouse matches the entity_id in cost centers

UPDATE sma_purchases p
SET
    p.cost_center_id = (
        SELECT cc.cost_center_id
        FROM sma_cost_centers cc
        WHERE
            cc.entity_id = p.warehouse_id
        LIMIT 1
    )
WHERE
    p.cost_center_id IS NULL
    AND EXISTS (
        SELECT 1
        FROM sma_cost_centers cc
        WHERE
            cc.entity_id = p.warehouse_id
    );

-- Check how many purchases were updated
SELECT
    COUNT(*) as total_purchases,
    COUNT(cost_center_id) as purchases_with_cost_center,
    COUNT(*) - COUNT(cost_center_id) as purchases_without_cost_center
FROM sma_purchases;