-- Check loyalty_branches structure and constraints
SHOW CREATE TABLE loyalty_branches\G

-- Check loyalty_pharmacies structure and constraints
SHOW CREATE TABLE loyalty_pharmacies\G

-- Check sma_warehouses structure and constraints
SHOW CREATE TABLE sma_warehouses\G

-- Get all foreign keys for loyalty tables
SELECT
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    TABLE_SCHEMA = 'retaj_aldawa'
    AND (
        TABLE_NAME LIKE 'loyalty_%'
        OR TABLE_NAME = 'sma_warehouses'
    )
ORDER BY TABLE_NAME, CONSTRAINT_NAME;