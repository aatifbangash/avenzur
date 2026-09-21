-- ============================================
-- WASFATY INTEGRATION - COMPLETE DEPLOYMENT SCRIPT
-- Created: November 5, 2025
-- Version: 3.0
-- Purpose: Single script to set up complete Wasfaty integration
-- ============================================
--
-- This script performs:
-- 1. Creates Wasfaty prescription tables (sma_wasfaty_prescriptions, sma_wasfaty_prescription_items)
-- 2. Adds Wasfaty tracking columns to sma_sales (source, prescription_code, customer_type)
-- 3. Populates dimension tables (sma_dim_pharmacy, sma_dim_branch)
-- 4. Inserts test prescription data
-- 5. Verifies setup
--
-- Usage:
--   mysql -h HOST -u USER -pPASSWORD DATABASE < wasfaty_deployment.sql
--
-- ============================================

-- ============================================
-- STEP 1: CREATE WASFATY TABLES
-- ============================================

DROP TABLE IF EXISTS `sma_wasfaty_prescription_items`;

DROP TABLE IF EXISTS `sma_wasfaty_prescriptions`;

CREATE TABLE `sma_wasfaty_prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prescription_code` varchar(20) NOT NULL,
  `patient_phone` varchar(15) NOT NULL,
  `customer_type` enum('REGULAR','SILVER','GOLD','PLATINUM') DEFAULT 'REGULAR',
  `fetched_at` datetime DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `status` enum('PENDING','DISPENSED','CANCELLED') DEFAULT 'PENDING',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prescription_code` (`prescription_code`),
  KEY `patient_phone` (`patient_phone`),
  KEY `order_id` (`order_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Wasfaty prescription tracking table';

CREATE TABLE `sma_wasfaty_prescription_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prescription_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `duration_days` int(11) NOT NULL,
  `total_quantity` int(11) AS (`quantity` * `duration_days`) STORED,
  PRIMARY KEY (`id`),
  KEY `prescription_id` (`prescription_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `fk_sma_wasfaty_prescription_items_prescription` 
    FOREIGN KEY (`prescription_id`) 
    REFERENCES `sma_wasfaty_prescriptions` (`id`) 
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Wasfaty prescription line items';

-- ============================================
-- STEP 2: ADD WASFATY COLUMNS TO SMA_SALES
-- ============================================

-- Check and add source column
SELECT COUNT(*) INTO @ source_exists
FROM information_schema.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'sma_sales'
    AND COLUMN_NAME = 'source';

SET
    @ sql1 = IF (
        @ source_exists = 0,
        'ALTER TABLE sma_sales ADD COLUMN source ENUM("MANUAL","WASFATY","ONLINE","MOBILE") DEFAULT "MANUAL" AFTER id',
        'SELECT "source column already exists" as message'
    );

PREPARE stmt1 FROM @ sql1;

EXECUTE stmt1;

DEALLOCATE PREPARE stmt1;

-- Check and add prescription_code column
SELECT COUNT(*) INTO @ prescription_code_exists
FROM information_schema.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'sma_sales'
    AND COLUMN_NAME = 'prescription_code';

SET
    @ sql2 = IF (
        @ prescription_code_exists = 0,
        'ALTER TABLE sma_sales ADD COLUMN prescription_code VARCHAR(20) DEFAULT NULL AFTER source, ADD INDEX idx_prescription_code (prescription_code)',
        'SELECT "prescription_code column already exists" as message'
    );

PREPARE stmt2 FROM @ sql2;

EXECUTE stmt2;

DEALLOCATE PREPARE stmt2;

-- Check and add customer_type column
SELECT COUNT(*) INTO @ customer_type_exists
FROM information_schema.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'sma_sales'
    AND COLUMN_NAME = 'customer_type';

SET
    @ sql3 = IF (
        @ customer_type_exists = 0,
        'ALTER TABLE sma_sales ADD COLUMN customer_type ENUM("REGULAR","SILVER","GOLD","PLATINUM") DEFAULT "REGULAR" AFTER customer_id',
        'SELECT "customer_type column already exists" as message'
    );

PREPARE stmt3 FROM @ sql3;

EXECUTE stmt3;

DEALLOCATE PREPARE stmt3;

-- ============================================
-- STEP 3: POPULATE DIMENSION TABLES (ETL)
-- ============================================

-- Populate sma_dim_pharmacy from sma_warehouses
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

-- Populate sma_dim_branch from sma_warehouses
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

-- ============================================
-- STEP 4: INSERT TEST PRESCRIPTION DATA
-- ============================================

-- Find products with sufficient stock
SELECT p.id INTO @ product1_id
FROM sma_products p
WHERE (
        SELECT COALESCE(SUM(quantity), 0)
        FROM sma_inventory_movements
        WHERE
            product_id = p.id
    ) >= 15
ORDER BY p.id ASC
LIMIT 1;

SELECT p.id INTO @ product2_id
FROM sma_products p
WHERE (
        SELECT COALESCE(SUM(quantity), 0)
        FROM sma_inventory_movements
        WHERE
            product_id = p.id
    ) >= 6
    AND p.id != @ product1_id
ORDER BY p.id ASC
LIMIT 1;

-- Get product names
SELECT name INTO @ product1_name
FROM sma_products
WHERE
    id = @ product1_id;

SELECT name INTO @ product2_name
FROM sma_products
WHERE
    id = @ product2_id;

-- Insert test prescription
INSERT INTO
    sma_wasfaty_prescriptions (
        prescription_code,
        patient_phone,
        customer_type,
        status
    )
VALUES (
        '190583',
        '0554712269',
        'GOLD',
        'PENDING'
    );

-- Get the prescription ID
SELECT id INTO @ prescription_id
FROM sma_wasfaty_prescriptions
WHERE
    prescription_code = '190583';

-- Insert prescription items
INSERT INTO
    sma_wasfaty_prescription_items (
        prescription_id,
        medicine_id,
        medicine_name,
        quantity,
        dosage,
        duration_days
    )
VALUES (
        @ prescription_id,
        @ product1_id,
        @ product1_name,
        3,
        '5ml twice daily',
        5
    ),
    (
        @ prescription_id,
        @ product2_id,
        @ product2_name,
        2,
        '1 tablet three times daily',
        3
    );

-- ============================================
-- STEP 5: VERIFICATION QUERIES
-- ============================================

SELECT '========================================' AS '';

SELECT 'WASFATY INTEGRATION DEPLOYMENT COMPLETE' AS status;

SELECT '========================================' AS '';

-- Verify tables created
SELECT 'Wasfaty Tables:' AS info;

SELECT
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME
FROM information_schema.TABLES
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME LIKE 'sma_wasfaty%'
ORDER BY TABLE_NAME;

-- Verify sma_sales columns
SELECT '' AS '';

SELECT 'SMA_Sales Columns Added:' AS info;

SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME = 'sma_sales'
    AND COLUMN_NAME IN (
        'source',
        'prescription_code',
        'customer_type'
    )
ORDER BY ORDINAL_POSITION;

-- Verify dimension tables
SELECT '' AS '';

SELECT 'Dimension Tables Populated:' AS info;

SELECT 'sma_dim_pharmacy' AS table_name, COUNT(*) AS record_count
FROM sma_dim_pharmacy
UNION ALL
SELECT 'sma_dim_branch', COUNT(*)
FROM sma_dim_branch;

-- Verify test prescription
SELECT '' AS '';

SELECT 'Test Prescription Data:' AS info;

SELECT p.id, p.prescription_code, p.patient_phone, p.customer_type, p.status, (
        SELECT COUNT(*)
        FROM sma_wasfaty_prescription_items
        WHERE
            prescription_id = p.id
    ) as item_count
FROM sma_wasfaty_prescriptions p
WHERE
    p.prescription_code = '190583';

-- Verify prescription items
SELECT '' AS '';

SELECT 'Prescription Items:' AS info;

SELECT
    i.medicine_id,
    i.medicine_name,
    i.quantity,
    i.dosage,
    i.duration_days,
    i.total_quantity as total_qty,
    (
        SELECT COALESCE(SUM(quantity), 0)
        FROM sma_inventory_movements
        WHERE
            product_id = i.medicine_id
    ) as available_stock
FROM
    sma_wasfaty_prescription_items i
    JOIN sma_wasfaty_prescriptions pr ON i.prescription_id = pr.id
WHERE
    pr.prescription_code = '190583';

SELECT '' AS '';

SELECT '========================================' AS '';

SELECT 'READY FOR TESTING!' AS status;

SELECT 'Test Credentials:' AS info;

SELECT 'Phone: 0554712269' AS credential_1;

SELECT 'Code: 190583' AS credential_2;

SELECT 'Customer Type: GOLD (15% discount)' AS credential_3;

SELECT '========================================' AS '';

-- ============================================
-- END OF DEPLOYMENT SCRIPT
-- ============================================