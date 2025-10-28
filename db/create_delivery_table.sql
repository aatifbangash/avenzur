-- ============================================================================
-- Modify Existing sma_deliveries Table
-- This table manages delivery packages that contain multiple invoices and track driver assignments
-- ============================================================================

-- Add columns to existing table
ALTER TABLE `sma_deliveries` 
ADD COLUMN `date_string` datetime DEFAULT NULL AFTER `id`;

-- ALTER TABLE `sma_deliveries`
-- ADD COLUMN `driver_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `date_string`;

-- ALTER TABLE `sma_deliveries`
-- ADD COLUMN `truck_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `driver_name`;

ALTER TABLE `sma_deliveries` 
ADD COLUMN `status` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' AFTER `truck_number`;

ALTER TABLE `sma_deliveries` 
ADD COLUMN `total_items_in_delivery_package` int(11) NOT NULL DEFAULT '0' AFTER `status`;

ALTER TABLE `sma_deliveries` 
ADD COLUMN `out_time` datetime DEFAULT NULL AFTER `total_items_in_delivery_package`;

ALTER TABLE `sma_deliveries` 
ADD COLUMN `odometer` int(11) DEFAULT NULL AFTER `out_time`;

ALTER TABLE `sma_deliveries` 
ADD COLUMN `total_refrigerated_items` int(11) NOT NULL DEFAULT '0' AFTER `odometer`;

ALTER TABLE `sma_deliveries`
  ADD COLUMN `driver_id` INT COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `status`;

ALTER TABLE `sma_deliveries` 
ADD COLUMN `assigned_by` int(11) DEFAULT NULL COMMENT 'User ID who assigned the delivery' AFTER `total_refrigerated_items`;

ALTER TABLE `sma_deliveries` 
ADD COLUMN `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `assigned_by`;

ALTER TABLE `sma_deliveries` 
ADD COLUMN `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Add indexes to the table
ALTER TABLE `sma_deliveries` ADD INDEX `idx_status` (`status`);

ALTER TABLE `sma_deliveries` 
ADD INDEX `idx_driver_name` (`driver_name`);

ALTER TABLE `sma_deliveries` 
ADD INDEX `idx_created_at` (`created_at`);

ALTER TABLE `sma_deliveries` 
ADD INDEX `idx_assigned_by` (`assigned_by`);

-- ============================================================================
-- Create Delivery Items Mapping Table (if not exists)
-- This table maps invoices to deliveries (many-to-many relationship)
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sma_delivery_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `quantity_items` int(11) NOT NULL DEFAULT '0',
  `refrigerated_items` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_delivery_invoice` (`delivery_id`, `invoice_id`),
  KEY `idx_delivery_id` (`delivery_id`),
  KEY `idx_invoice_id` (`invoice_id`),
  CONSTRAINT `fk_delivery_items_delivery` FOREIGN KEY (`delivery_id`) REFERENCES `sma_deliveries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Create Delivery Print Logs Table (if not exists)
-- This table tracks when deliveries are printed for audit and reference
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sma_delivery_prints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) NOT NULL,
  `printed_by` int(11) DEFAULT NULL COMMENT 'User ID who printed the delivery',
  `printed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `print_count` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_delivery_id` (`delivery_id`),
  KEY `idx_printed_at` (`printed_at`),
  CONSTRAINT `fk_delivery_prints_delivery` FOREIGN KEY (`delivery_id`) REFERENCES `sma_deliveries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Create Delivery Audit Logs Table (if not exists)
-- This table tracks all actions performed on deliveries for audit purposes
-- ============================================================================

CREATE TABLE IF NOT EXISTS `sma_delivery_audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delivery_id` int(11) NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `done_by` int(11) DEFAULT NULL COMMENT 'User ID who performed the action',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_delivery_id` (`delivery_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_audit_logs_delivery` FOREIGN KEY (`delivery_id`) REFERENCES `sma_deliveries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;