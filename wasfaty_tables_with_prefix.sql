-- Wasfaty Tables with SMA Prefix
-- This follows the existing database convention

-- Create sma_wasfaty_prescriptions table
CREATE TABLE IF NOT EXISTS `sma_wasfaty_prescriptions` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create sma_wasfaty_prescription_items table
CREATE TABLE IF NOT EXISTS `sma_wasfaty_prescription_items` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert test prescription
INSERT INTO `sma_wasfaty_prescriptions`
(`prescription_code`, `patient_phone`, `customer_type`, `status`)
VALUES
('190583', '0554712260', 'GOLD', 'PENDING')
ON DUPLICATE KEY UPDATE 
  `patient_phone` = VALUES(`patient_phone`),
  `customer_type` = VALUES(`customer_type`),
  `status` = VALUES(`status`);

-- Get the prescription ID
SET @prescription_id = (SELECT id FROM `sma_wasfaty_prescriptions` WHERE `prescription_code` = '190583');

-- Insert prescription items (using placeholder product IDs 1 and 2)
INSERT INTO `sma_wasfaty_prescription_items`
(`prescription_id`, `medicine_id`, `medicine_name`, `quantity`, `dosage`, `duration_days`)
SELECT @prescription_id, 1, 'EXYLIN 100ML SYRUP', 3, '5ml twice daily', 5
WHERE NOT EXISTS (
  SELECT 1 FROM `sma_wasfaty_prescription_items` 
  WHERE `prescription_id` = @prescription_id AND `medicine_name` = 'EXYLIN 100ML SYRUP'
);

INSERT INTO `sma_wasfaty_prescription_items`
(`prescription_id`, `medicine_id`, `medicine_name`, `quantity`, `dosage`, `duration_days`)
SELECT @prescription_id, 2, 'Panadol Cold Flu 24Cap (Green)', 3, '1 tablet three times daily', 5
WHERE NOT EXISTS (
  SELECT 1 FROM `sma_wasfaty_prescription_items` 
  WHERE `prescription_id` = @prescription_id AND `medicine_name` = 'Panadol Cold Flu 24Cap (Green)'
);

-- Verify
SELECT 'Tables created with sma_ prefix successfully!' AS status;

SELECT *
FROM sma_wasfaty_prescriptions
WHERE
    prescription_code = '190583';

SELECT *
FROM sma_wasfaty_prescription_items
WHERE
    prescription_id = @ prescription_id;