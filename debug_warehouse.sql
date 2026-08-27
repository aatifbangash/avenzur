-- Check the structure of sma_warehouses
DESCRIBE sma_warehouses;

-- Check data in sma_warehouses
SELECT * FROM sma_warehouses WHERE warehouse_type = 'pharmacy' LIMIT 1\G

-- Check if there are NULL values
SELECT
    id,
    code,
    name,
    address,
    phone,
    email
FROM sma_warehouses
WHERE
    warehouse_type = 'pharmacy'
LIMIT 5;