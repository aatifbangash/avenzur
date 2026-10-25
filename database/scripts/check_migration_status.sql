-- Diagnostic script to check migration status
-- This will tell us if migrations 010 and 011 have been applied

-- Check if backup tables exist (indicates migration was attempted)
SELECT 'backup_dim_branch' AS table_name, COUNT(*) AS exists
FROM INFORMATION_SCHEMA.TABLES
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'backup_dim_branch'
UNION ALL
SELECT 'backup_dim_pharmacy' AS table_name, COUNT(*)
FROM INFORMATION_SCHEMA.TABLES
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'backup_dim_pharmacy';

-- Check current dimension table structure
SELECT
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_KEY,
    ORDINAL_POSITION
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME IN (
        'sma_dim_branch',
        'sma_dim_pharmacy'
    )
ORDER BY TABLE_NAME, ORDINAL_POSITION;

-- Check fact table structure to see what it stores
SELECT
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_KEY
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'sma_fact_cost_center'
ORDER BY ORDINAL_POSITION;

-- List all views related to cost center
SELECT TABLE_NAME AS view_name
FROM INFORMATION_SCHEMA.TABLES
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_TYPE = 'VIEW'
    AND TABLE_NAME LIKE '%cost_center%'
ORDER BY TABLE_NAME;

-- Check if migration 010 was run (check by looking for warehouse_id as primary key in dim_pharmacy)
SELECT
    'sma_dim_pharmacy' AS table_name,
    COUNT(
        CASE
            WHEN COLUMN_NAME = 'warehouse_id'
            AND COLUMN_KEY = 'PRI' THEN 1
        END
    ) AS has_warehouse_pk,
    COUNT(
        CASE
            WHEN COLUMN_NAME = 'pharmacy_id'
            AND COLUMN_KEY = 'PRI' THEN 1
        END
    ) AS has_pharmacy_pk
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'sma_dim_pharmacy';

-- List all foreign key constraints on dim_branch to understand relationships
SELECT
    CONSTRAINT_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'sma_dim_branch'
    AND REFERENCED_TABLE_NAME IS NOT NULL;