-- Create Delivery Table for Packaging Multiple Invoices and Driver Assignment
-- This table manages delivery packages that contain multiple invoices and track driver assignments

CREATE TABLE IF NOT EXISTS `sma_deliveries` (
  `id` INT  NOT NULL AUTO_INCREMENT,
  `date_string` datetime DEFAULT NULL,
  `driver_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `truck_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `total_items_in_delivery_package` INT NOT NULL DEFAULT '0',
  `out_time` datetime DEFAULT NULL,
  `odometer` INT DEFAULT NULL,
  `total_refrigerated_items` INT NOT NULL DEFAULT '0',
  `assigned_by` INT DEFAULT NULL COMMENT 'User ID who assigned the delivery',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_driver_name` (`driver_name`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_assigned_by` (`assigned_by`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Create Delivery Items Mapping Table
-- This table maps invoices to deliveries (many-to-many relationship)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sma_delivery_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `delivery_id` INT NOT NULL,
  `invoice_id` INT NOT NULL,
  `quantity_items` INT NOT NULL DEFAULT '0',
  `refrigerated_items` INT NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_delivery_invoice` (`delivery_id`, `invoice_id`),
  KEY `idx_delivery_id` (`delivery_id`),
  KEY `idx_invoice_id` (`invoice_id`),
  CONSTRAINT `fk_delivery_items_delivery` FOREIGN KEY (`delivery_id`) REFERENCES `sma_deliveries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Create Delivery Print Logs Table
-- This table tracks when deliveries are printed for audit and reference
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sma_delivery_prints` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `delivery_id` INT NOT NULL,
  `printed_by` INT DEFAULT NULL COMMENT 'User ID who printed the delivery',
  `printed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `print_count` INT NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_delivery_id` (`delivery_id`),
  KEY `idx_printed_at` (`printed_at`),
  CONSTRAINT `fk_delivery_prints_delivery` FOREIGN KEY (`delivery_id`) REFERENCES `sma_deliveries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;