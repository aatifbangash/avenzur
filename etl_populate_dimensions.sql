-- ============================================
-- ETL: Populate Dimension Tables
-- Created: November 5, 2025
-- Description: Populate dim_pharmacy and dim_branch from sma_warehouses
-- ============================================

-- ============================================================
-- 1. POPULATE DIM_PHARMACY FROM SMA_WAREHOUSES
-- ============================================================
-- Insert parent warehouses (pharmacies) into dim_pharmacy

INSERT IGNORE INTO sma_dim_pharmacy (
    warehouse_id,
    pharmacy_name,
    pharmacy_code,
    address,
    phone,
    email,
    country_id,
    is_active
)
SELECT w.id, w.name, w.code, w.address, w.phone, w.email, w.country, 1
FROM sma_warehouses w
WHERE
    w.parent_id IS NULL ON DUPLICATE KEY
UPDATE pharmacy_name =
VALUES (pharmacy_name),
    address =
VALUES (address),
    phone =
VALUES (phone),
    email =
VALUES (email),
    country_id =
VALUES (country_id);

-- ============================================================
-- 2. POPULATE DIM_BRANCH FROM SMA_WAREHOUSES
-- ============================================================
-- Insert child warehouses (branches) into dim_branch

INSERT IGNORE INTO sma_dim_branch (
    warehouse_id,
    pharmacy_id,
    pharmacy_warehouse_id,
    branch_name,
    branch_code,
    address,
    phone,
    email,
    country_id,
    is_active
)
SELECT b.id, dp.pharmacy_id, b.parent_id, b.name, b.code, b.address, b.phone, b.email, b.country, 1
FROM
    sma_warehouses b
    LEFT JOIN sma_dim_pharmacy dp ON b.parent_id = dp.warehouse_id
WHERE
    b.parent_id IS NOT NULL ON DUPLICATE KEY
UPDATE branch_name =
VALUES (branch_name),
    address =
VALUES (address),
    phone =
VALUES (phone),
    email =
VALUES (email),
    country_id =
VALUES (country_id);

-- ============================================================
-- 3. VERIFICATION QUERIES
-- ============================================================

SELECT 'ETL Dimension Tables - Load Summary' AS summary;

-- Count of pharmacies
SELECT 'Pharmacies Loaded' AS entity, COUNT(*) AS record_count
FROM sma_dim_pharmacy;

-- Count of branches
SELECT 'Branches Loaded' AS entity, COUNT(*) AS record_count
FROM sma_dim_branch;

-- Show pharmacy details
SELECT '' AS blank;

SELECT 'Pharmacy Details:' AS info;

SELECT
    pharmacy_id,
    warehouse_id,
    pharmacy_name,
    pharmacy_code,
    phone,
    is_active
FROM sma_dim_pharmacy
ORDER BY pharmacy_id;

-- Show branch details
SELECT '' AS blank;

SELECT 'Branch Details:' AS info;

SELECT
    branch_id,
    warehouse_id,
    pharmacy_id,
    pharmacy_warehouse_id,
    branch_name,
    branch_code,
    phone,
    is_active
FROM sma_dim_branch
ORDER BY pharmacy_id, branch_id;

-- Show hierarchy relationship
SELECT '' AS blank;

SELECT 'Pharmacy-Branch Hierarchy:' AS info;

SELECT p.pharmacy_name AS 'Pharmacy', p.warehouse_id AS 'Pharmacy_WH_ID', COUNT(b.branch_id) AS 'Branch_Count', GROUP_CONCAT (
        b.branch_name
        ORDER BY b.branch_name SEPARATOR ', '
    ) AS 'Branches'
FROM
    sma_dim_pharmacy p
    LEFT JOIN sma_dim_branch b ON p.pharmacy_id = b.pharmacy_id
GROUP BY
    p.pharmacy_id,
    p.pharmacy_name,
    p.warehouse_id
ORDER BY p.pharmacy_name;

SELECT 'ETL Complete!' AS final_status;