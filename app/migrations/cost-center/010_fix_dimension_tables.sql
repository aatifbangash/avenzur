-- ============================================================
-- Cost Center Migration: Fix Dimension Tables
-- ============================================================
-- File: 010_fix_dimension_tables.sql
-- Purpose: Fix the pharmacy_id vs warehouse_id confusion
--          Use warehouse_id as primary key (natural key)
--          Remove surrogate pharmacy_id to avoid confusion
-- Date: 2025-10-26
--
-- CHANGES:
-- 1. Drop old dimension tables with incorrect structure
-- 2. Recreate sma_dim_pharmacy using warehouse_id as PK
-- 3. Recreate sma_dim_branch using warehouse_id as PK
-- 4. Repopulate from sma_warehouses with correct hierarchy
-- 5. Update views to use warehouse_id instead of pharmacy_id
-- ============================================================

-- ============================================================
-- STEP 1: Save existing data to temporary tables (for reference)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_dim_pharmacy_old` LIKE sma_dim_pharmacy;

INSERT INTO sma_dim_pharmacy_old SELECT * FROM sma_dim_pharmacy;

CREATE TABLE IF NOT EXISTS `sma_dim_branch_old` LIKE sma_dim_branch;

INSERT INTO sma_dim_branch_old SELECT * FROM sma_dim_branch;

-- ============================================================
-- STEP 2: Drop existing dimension tables
-- ============================================================
DROP TABLE IF EXISTS `sma_dim_branch`;

DROP TABLE IF EXISTS `sma_dim_pharmacy`;

-- ============================================================
-- STEP 3: Create sma_dim_pharmacy (Fixed - warehouse_id as PK)
-- ============================================================
-- PURPOSE: Master pharmacy dimensions linked to sma_warehouses
-- KEY CHANGE: Using warehouse_id (natural key) instead of pharmacy_id (surrogate)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_dim_pharmacy` (
    `warehouse_id` INT(11) NOT NULL,
    `pharmacy_name` VARCHAR(255) NOT NULL,
    `pharmacy_code` VARCHAR(50) NOT NULL UNIQUE,
    `address` VARCHAR(500),
    `phone` VARCHAR(55),
    `email` VARCHAR(100),
    `country_id` INT(11),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`warehouse_id`),
    KEY `idx_pharmacy_code` (`pharmacy_code`),
    KEY `idx_is_active` (`is_active`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `sma_warehouses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- STEP 4: Populate sma_dim_pharmacy from sma_warehouses
-- ============================================================
-- Pharmacy records are warehouses with warehouse_type = 'warehouse' AND parent_id IS NULL
-- ============================================================
INSERT INTO
    sma_dim_pharmacy (
        warehouse_id,
        pharmacy_name,
        pharmacy_code,
        address,
        phone,
        email,
        country_id,
        is_active
    )
SELECT
    w.id AS warehouse_id,
    w.name AS pharmacy_name,
    w.code AS pharmacy_code,
    w.address,
    w.phone,
    w.email,
    w.country AS country_id,
    1 AS is_active
FROM sma_warehouses w
WHERE
    w.warehouse_type IN ('warehouse', NULL)
    AND w.parent_id IS NULL ON DUPLICATE KEY
UPDATE pharmacy_name =
VALUES (pharmacy_name),
    address =
VALUES (address),
    phone =
VALUES (phone),
    email =
VALUES (email),
    updated_at = CURRENT_TIMESTAMP;

-- ============================================================
-- STEP 5: Create sma_dim_branch (Fixed - warehouse_id as PK)
-- ============================================================
-- PURPOSE: Master branch dimensions linked to pharmacies
-- KEY CHANGES:
-- 1. Using warehouse_id (natural key) as PK for branch
-- 2. Using pharmacy_warehouse_id to reference parent pharmacy's warehouse_id (not surrogate pharmacy_id)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_dim_branch` (
    `warehouse_id` INT(11) NOT NULL,
    `pharmacy_warehouse_id` INT(11) NOT NULL,
    `branch_name` VARCHAR(255) NOT NULL,
    `branch_code` VARCHAR(50) NOT NULL UNIQUE,
    `address` VARCHAR(500),
    `phone` VARCHAR(55),
    `email` VARCHAR(100),
    `country_id` INT(11),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`warehouse_id`),
    KEY `idx_pharmacy_warehouse_id` (`pharmacy_warehouse_id`),
    KEY `idx_branch_code` (`branch_code`),
    KEY `idx_is_active` (`is_active`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `sma_warehouses` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`pharmacy_warehouse_id`) REFERENCES `sma_dim_pharmacy` (`warehouse_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- STEP 6: Populate sma_dim_branch from sma_warehouses
-- ============================================================
-- Branch records are warehouses with warehouse_type = 'branch' AND parent_id IS NOT NULL
-- parent_id points to the parent pharmacy's warehouse_id
-- ============================================================
INSERT INTO
    sma_dim_branch (
        warehouse_id,
        pharmacy_warehouse_id,
        branch_name,
        branch_code,
        address,
        phone,
        email,
        country_id,
        is_active
    )
SELECT
    b.id AS warehouse_id,
    b.parent_id AS pharmacy_warehouse_id,
    b.name AS branch_name,
    b.code AS branch_code,
    b.address,
    b.phone,
    b.email,
    b.country AS country_id,
    1 AS is_active
FROM sma_warehouses b
WHERE
    b.warehouse_type = 'branch'
    AND b.parent_id IS NOT NULL
    AND EXISTS (
        SELECT 1
        FROM sma_dim_pharmacy dp
        WHERE
            dp.warehouse_id = b.parent_id
    ) ON DUPLICATE KEY
UPDATE pharmacy_warehouse_id =
VALUES (pharmacy_warehouse_id),
    branch_name =
VALUES (branch_name),
    address =
VALUES (address),
    phone =
VALUES (phone),
    email =
VALUES (email),
    updated_at = CURRENT_TIMESTAMP;

-- ============================================================
-- STEP 7: Verify data integrity
-- ============================================================
-- Show summary of newly created dimensions
-- ============================================================
SELECT
    'Pharmacies' as entity_type,
    COUNT(*) as count,
    COUNT(DISTINCT warehouse_id) as unique_warehouses
FROM sma_dim_pharmacy
WHERE
    is_active = 1
UNION ALL
SELECT
    'Branches' as entity_type,
    COUNT(*) as count,
    COUNT(DISTINCT warehouse_id) as unique_warehouses
FROM sma_dim_branch
WHERE
    is_active = 1;

-- Show any branches without valid parent pharmacies (data quality check)
SELECT b.warehouse_id, b.branch_code, b.branch_name, b.pharmacy_warehouse_id, 'ERROR: Parent pharmacy not found' as issue
FROM
    sma_dim_branch b
    LEFT JOIN sma_dim_pharmacy p ON b.pharmacy_warehouse_id = p.warehouse_id
WHERE
    p.warehouse_id IS NULL;

-- ============================================================
-- STEP 8: Migration notes
-- ============================================================
-- BEFORE: sma_dim_pharmacy.pharmacy_id (surrogate) → referenced in views
-- AFTER:  sma_dim_pharmacy.warehouse_id (natural) → referenced in views
--
-- BEFORE: sma_dim_branch.pharmacy_id → FK to sma_dim_pharmacy.pharmacy_id (surrogate)
-- AFTER:  sma_dim_branch.pharmacy_warehouse_id → FK to sma_dim_pharmacy.warehouse_id (natural)
--
-- IMPACT: All queries in Cost_center_model.php must be updated to use warehouse_id
--         View queries must be updated to use warehouse_id instead of pharmacy_id
-- ============================================================

-- Record migration completion
INSERT INTO
    sma_migrations (name, batch)
VALUES (
        '010_fix_dimension_tables',
        (
            SELECT MAX(batch)
            FROM sma_migrations
        ) + 1
    ) ON DUPLICATE KEY
UPDATE batch =
VALUES (batch);