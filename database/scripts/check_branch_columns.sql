-- Check if sma_dim_branch table has pharmacy_warehouse_id column
-- This will help diagnose the runtime error

-- Step 1: Show table structure for sma_dim_branch
DESC sma_dim_branch;

-- Step 2: Show all columns in sma_dim_branch
SELECT
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_KEY
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'sma_dim_branch'
ORDER BY ORDINAL_POSITION;

-- Step 3: Check if pharmacy_warehouse_id column exists
SELECT COUNT(*) AS column_exists
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'sma_dim_branch'
    AND COLUMN_NAME = 'pharmacy_warehouse_id';

-- Step 4: Show column info for pharmacy_warehouse_id if it exists
SELECT
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_KEY,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'sma_dim_branch'
    AND COLUMN_NAME = 'pharmacy_warehouse_id';

-- Step 5: Check how many branches exist and their structure
SELECT COUNT(*) AS branch_count FROM sma_dim_branch LIMIT 1;

-- Step 6: Show sample branch records
SELECT * FROM sma_dim_branch LIMIT 5;