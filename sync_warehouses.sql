-- Sync sma_warehouses table with loyalty data
-- This script creates warehouse entries for each pharmacy and branch from loyalty tables
-- Note: sma_warehouses uses auto-increment integer IDs, not UUIDs

-- Step 1: Create pharmacy warehouses (type = 'pharmacy')
-- Check first if pharmacy codes already exist in warehouses
INSERT INTO
    sma_warehouses (
        code,
        name,
        address,
        warehouse_type,
        country,
        parent_id
    )
SELECT
    lp.code,
    lp.name,
    '' as address,
    'pharmacy' as warehouse_type,
    1 as country,
    NULL as parent_id
FROM loyalty_pharmacies lp
WHERE
    lp.code NOT IN (
        SELECT code
        FROM sma_warehouses
        WHERE
            warehouse_type = 'pharmacy'
    );

-- Step 2: Create branch warehouses (type = 'branch')
-- These will be linked to their pharmacy parent
INSERT INTO
    sma_warehouses (
        code,
        name,
        address,
        warehouse_type,
        country,
        parent_id
    )
SELECT
    lb.code,
    lb.name,
    '' as address,
    'branch' as warehouse_type,
    1 as country,
    sw.id as parent_id
FROM
    loyalty_branches lb
    LEFT JOIN loyalty_pharmacies lp ON lb.pharmacy_id = lp.id
    LEFT JOIN sma_warehouses sw ON lp.code = sw.code
    AND sw.warehouse_type = 'pharmacy'
WHERE
    lb.code NOT IN (
        SELECT code
        FROM sma_warehouses
        WHERE
            warehouse_type = 'branch'
    );

-- Step 3: Verify sync results
SELECT 'Loyalty Data Summary' as report;

SELECT CONCAT('Pharmacy Groups: ', COUNT(*)) as summary
FROM loyalty_pharmacy_groups
UNION ALL
SELECT CONCAT('Pharmacies: ', COUNT(*))
FROM loyalty_pharmacies
UNION ALL
SELECT CONCAT('Branches: ', COUNT(*))
FROM loyalty_branches;

SELECT '';

SELECT 'Warehouse Sync Summary' as report;

SELECT CONCAT(
        'Pharmacy Warehouses: ', COUNT(*)
    ) as summary
FROM sma_warehouses
WHERE
    warehouse_type = 'pharmacy'
UNION ALL
SELECT CONCAT(
        'Branch Warehouses: ', COUNT(*)
    )
FROM sma_warehouses
WHERE
    warehouse_type = 'branch'
UNION ALL
SELECT CONCAT(
        'Total Warehouses: ', COUNT(*)
    )
FROM sma_warehouses;

-- Step 4: Show synced pharmacy warehouses
SELECT '' as spacer;

SELECT 'Pharmacy Warehouses (Synced):' as detail;

SELECT id, code, name, warehouse_type
FROM sma_warehouses
WHERE
    warehouse_type = 'pharmacy'
ORDER BY name;

-- Step 5: Show synced branch warehouses
SELECT '' as spacer;

SELECT 'Branch Warehouses (Synced):' as detail;

SELECT sw.id, sw.code, sw.name, sw.warehouse_type, sw.parent_id, (
        SELECT name
        FROM sma_warehouses
        WHERE
            id = sw.parent_id
    ) as parent_name
FROM sma_warehouses sw
WHERE
    sw.warehouse_type = 'branch'
ORDER BY sw.parent_id, sw.name;