-- ============================================
-- Wasfaty Mock Integration - Complete Setup
-- Created: November 5, 2025
-- Description: Complete database setup for Wasfaty prescription integration
-- ============================================

-- Step 1: Create wasfaty_prescriptions table
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

-- Step 2: Create wasfaty_prescription_items table
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

-- Step 3: Add Wasfaty tracking columns to sma_sales table
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

-- Step 4: Insert test prescription data
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
-- Verification Queries
-- ============================================

SELECT 'Wasfaty Tables Created Successfully!' AS status;

SELECT
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME
FROM information_schema.TABLES
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME IN (
        'sma_wasfaty_prescriptions',
        'sma_wasfaty_prescription_items'
    );

-- Show sma_sales columns added
SELECT 'Columns in sma_sales:' AS info;

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
    );

-- Show test prescription
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

-- Show prescription items with product details
SELECT 'Prescription Items:' AS info;

SELECT
    i.medicine_id,
    i.medicine_name,
    i.quantity,
    i.dosage,
    i.duration_days,
    i.total_quantity as 'Total_Qty',
    p.code as product_code,
    (
        SELECT COALESCE(SUM(quantity), 0)
        FROM sma_inventory_movements
        WHERE
            product_id = i.medicine_id
    ) as available_stock
FROM
    sma_wasfaty_prescription_items i
    JOIN sma_wasfaty_prescriptions pr ON i.prescription_id = pr.id
    LEFT JOIN sma_products p ON i.medicine_id = p.id
WHERE
    pr.prescription_code = '190583';

-- Show inventory for test products
SELECT 'Available Inventory for Test Products:' AS info;

SELECT im.product_id, p.name as product_name, im.batch_number, im.quantity, im.expiry_date, im.net_unit_sale as price
FROM
    sma_inventory_movements im
    JOIN sma_products p ON im.product_id = p.id
WHERE
    im.product_id IN (@ product1_id, @ product2_id)
    AND im.quantity > 0
    AND (
        im.expiry_date IS NULL
        OR im.expiry_date > CURDATE ()
    )
ORDER BY im.product_id, im.expiry_date ASC;

SELECT 'Setup Complete! Test with: Phone: 0554712269, Code: 190583' AS final_status;