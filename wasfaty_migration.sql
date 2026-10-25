-- ============================================
-- Wasfaty Mock Integration - Database Setup
-- Created: November 5, 2025
-- Description: Tables for mock Wasfaty prescription integration
-- ============================================

-- Add Wasfaty prescription tracking table
CREATE TABLE IF NOT EXISTS `wasfaty_prescriptions` (
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

-- Add Wasfaty prescription items table
CREATE TABLE IF NOT EXISTS `wasfaty_prescription_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prescription_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `medicine_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `duration_days` int(11) NOT NULL,
  `total_quantity` int(11) GENERATED ALWAYS AS (`quantity` * `duration_days`) STORED,
  PRIMARY KEY (`id`),
  KEY `prescription_id` (`prescription_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `fk_wasfaty_prescription_items_prescription` 
    FOREIGN KEY (`prescription_id`) 
    REFERENCES `wasfaty_prescriptions` (`id`) 
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Wasfaty prescription line items';

-- Check if orders table exists and add Wasfaty tracking columns
-- Note: Using stored procedure to safely add columns if they don't exist

DELIMITER $$

DROP PROCEDURE IF EXISTS add_wasfaty_columns$$

CREATE PROCEDURE add_wasfaty_columns()
BEGIN
    -- Add source column if it doesn't exist
    IF NOT EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'sma_sales' 
        AND COLUMN_NAME = 'source'
    ) THEN
        ALTER TABLE `sma_sales` 
        ADD COLUMN `source` enum('MANUAL','WASFATY','ONLINE','MOBILE') DEFAULT 'MANUAL' AFTER `id`;
    END IF;
    
    -- Add prescription_code column if it doesn't exist
    IF NOT EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'sma_sales' 
        AND COLUMN_NAME = 'prescription_code'
    ) THEN
        ALTER TABLE `sma_sales` 
        ADD COLUMN `prescription_code` varchar(20) DEFAULT NULL AFTER `source`;
    END IF;
    
    -- Add customer_type column if it doesn't exist
    IF NOT EXISTS(
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'sma_sales' 
        AND COLUMN_NAME = 'customer_type'
    ) THEN
        ALTER TABLE `sma_sales` 
        ADD COLUMN `customer_type` enum('REGULAR','SILVER','GOLD','PLATINUM') DEFAULT 'REGULAR' AFTER `customer_id`;
    END IF;
END$$

DELIMITER;

-- Execute the procedure
CALL add_wasfaty_columns ();

-- Drop the procedure after use
DROP PROCEDURE IF EXISTS add_wasfaty_columns;

-- Note: If your database uses a different prefix than 'sma_', replace it above

-- ============================================
-- Mock Test Data
-- ============================================
-- Note: Using existing sma_inventory_movements table for batch tracking

-- Insert sample prescription for testing
INSERT INTO `wasfaty_prescriptions`
(`prescription_code`, `patient_phone`, `customer_type`, `status`)
VALUES
('190583', '0554712260', 'GOLD', 'PENDING')
ON DUPLICATE KEY UPDATE 
  `patient_phone` = VALUES(`patient_phone`),
  `customer_type` = VALUES(`customer_type`),
  `status` = VALUES(`status`);

-- Get the prescription ID
SET @prescription_id = (SELECT id FROM `wasfaty_prescriptions` WHERE `prescription_code` = '190583');

-- Insert prescription items
-- Note: Replace medicine_id values with actual product IDs from your database
-- These are placeholder values - you'll need to update them with real product IDs

-- Example: EXYLIN 100ML SYRUP (update medicine_id to match your products)
INSERT INTO `wasfaty_prescription_items`
(`prescription_id`, `medicine_id`, `medicine_name`, `quantity`, `dosage`, `duration_days`)
SELECT @prescription_id, 1, 'EXYLIN 100ML SYRUP', 3, '5ml twice daily', 5
WHERE NOT EXISTS (
  SELECT 1 FROM `wasfaty_prescription_items` 
  WHERE `prescription_id` = @prescription_id AND `medicine_name` = 'EXYLIN 100ML SYRUP'
);

-- Example: Panadol Cold Flu (update medicine_id to match your products)
INSERT INTO `wasfaty_prescription_items`
(`prescription_id`, `medicine_id`, `medicine_name`, `quantity`, `dosage`, `duration_days`)
SELECT @prescription_id, 2, 'Panadol Cold Flu 24Cap (Green)', 3, '1 tablet three times daily', 5
WHERE NOT EXISTS (
  SELECT 1 FROM `wasfaty_prescription_items` 
  WHERE `prescription_id` = @prescription_id AND `medicine_name` = 'Panadol Cold Flu 24Cap (Green)'
);

-- ============================================
-- Note: Batch/Inventory tracking uses existing sma_inventory_movements table
-- No need to insert batch data - the system will use existing inventory records
-- Make sure the products (medicine_id 1 and 2) exist in sma_products
-- and have sufficient quantity in sma_inventory_movements
-- ============================================

-- ============================================
-- Verification Queries
-- ============================================

-- Verify tables created
SELECT 'Tables Created Successfully' AS status;

SELECT TABLE_NAME, TABLE_ROWS
FROM information_schema.TABLES
WHERE
    TABLE_SCHEMA = DATABASE ()
    AND TABLE_NAME IN (
        'wasfaty_prescriptions',
        'wasfaty_prescription_items'
    );

-- Verify test data
SELECT 'Test Prescription:' AS info, p.*, (
        SELECT COUNT(*)
        FROM wasfaty_prescription_items
        WHERE
            prescription_id = p.id
    ) as item_count
FROM wasfaty_prescriptions p
WHERE
    prescription_code = '190583';

-- Show prescription items with calculated totals
SELECT pi.medicine_name, pi.quantity, pi.dosage, pi.duration_days, pi.total_quantity as 'Total Qty (Qty × Days)', pi.medicine_id
FROM
    wasfaty_prescription_items pi
    JOIN wasfaty_prescriptions p ON pi.prescription_id = p.id
WHERE
    p.prescription_code = '190583';

-- Check if products exist in inventory
SELECT p.id, p.name, p.code, (
        SELECT SUM(quantity)
        FROM sma_inventory_movements
        WHERE
            product_id = p.id
    ) as total_stock
FROM sma_products p
WHERE
    p.id IN (1, 2);

-- Show available inventory movements (batches) for test products
SELECT
    product_id,
    batch_no,
    quantity,
    expiry,
    price
FROM sma_inventory_movements
WHERE
    product_id IN (1, 2)
    AND quantity > 0
    AND (
        expiry IS NULL
        OR expiry > CURDATE ()
    )
ORDER BY product_id, expiry ASC
LIMIT 10;