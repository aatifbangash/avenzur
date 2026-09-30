-- Update external_id fields with corresponding sma_warehouses IDs
-- This links loyalty tables to warehouse table

-- Step 1: Update external_id in loyalty_pharmacies
UPDATE loyalty_pharmacies lp
SET
    external_id = (
        SELECT sw.id
        FROM sma_warehouses sw
        WHERE
            sw.code = lp.code
            AND sw.warehouse_type = 'pharmacy'
        LIMIT 1
    )
WHERE
    external_id IS NULL;

-- Step 2: Update external_id in loyalty_branches
UPDATE loyalty_branches lb
SET
    external_id = (
        SELECT sw.id
        FROM sma_warehouses sw
        WHERE
            sw.code = lb.code
            AND sw.warehouse_type = 'branch'
        LIMIT 1
    )
WHERE
    external_id IS NULL;

-- Step 3: Update external_id in loyalty_pharmacy_groups
-- Groups don't have direct warehouse representation, but we can link to their first pharmacy warehouse
UPDATE loyalty_pharmacy_groups lpg
SET
    external_id = (
        SELECT MIN(sw.id)
        FROM
            sma_warehouses sw
            INNER JOIN loyalty_pharmacies lp ON sw.code = lp.code
            AND sw.warehouse_type = 'pharmacy'
        WHERE
            lp.pharmacy_group_id = lpg.id
        LIMIT 1
    )
WHERE
    external_id IS NULL;

-- Step 4: Verify the updates
SELECT '';

SELECT 'Loyalty Pharmacy Groups - External ID Mapping:' as report;

SELECT id, code, name, external_id
FROM loyalty_pharmacy_groups
ORDER BY code;

SELECT '';

SELECT 'Loyalty Pharmacies - External ID Mapping:' as report;

SELECT
    id,
    code,
    name,
    pharmacy_group_id,
    external_id
FROM loyalty_pharmacies
ORDER BY code;

SELECT '';

SELECT 'Loyalty Branches - External ID Mapping:' as report;

SELECT
    id,
    code,
    name,
    pharmacy_id,
    external_id
FROM loyalty_branches
ORDER BY code;

-- Step 5: Verify all external_ids are populated
SELECT '';

SELECT 'Verification - External IDs populated:' as report;

SELECT
    'Groups' as entity,
    COUNT(*) as total,
    SUM(
        CASE
            WHEN external_id IS NOT NULL THEN 1
            ELSE 0
        END
    ) as with_external_id
FROM loyalty_pharmacy_groups
UNION ALL
SELECT 'Pharmacies', COUNT(*), SUM(
        CASE
            WHEN external_id IS NOT NULL THEN 1
            ELSE 0
        END
    )
FROM loyalty_pharmacies
UNION ALL
SELECT 'Branches', COUNT(*), SUM(
        CASE
            WHEN external_id IS NOT NULL THEN 1
            ELSE 0
        END
    )
FROM loyalty_branches;

-- Step 6: Cross-reference validation
SELECT '';

SELECT 'Validation - Pharmacies with warehouse references:' as report;

SELECT
    lp.code,
    lp.name,
    lp.external_id,
    sw.id as warehouse_id,
    sw.name as warehouse_name
FROM
    loyalty_pharmacies lp
    LEFT JOIN sma_warehouses sw ON lp.external_id = sw.id
ORDER BY lp.code;

SELECT '';

SELECT 'Validation - Branches with warehouse references:' as report;

SELECT
    lb.code,
    lb.name,
    lb.external_id,
    sw.id as warehouse_id,
    sw.name as warehouse_name
FROM
    loyalty_branches lb
    LEFT JOIN sma_warehouses sw ON lb.external_id = sw.id
ORDER BY lb.code;