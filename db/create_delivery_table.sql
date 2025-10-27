-- ============================================================================
-- Modify Existing sma_deliveries Table
-- This table manages delivery packages that contain multiple invoices and track driver assignments
-- ============================================================================

-- Add columns if they don't exist
ALTER TABLE `sma_deliveries` 
ADD COLUMN IF NOT EXISTS `date_string` datetime DEFAULT NULL AFTER `id`,
ADD COLUMN IF NOT EXISTS `driver_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `date_string`,
ADD COLUMN IF NOT EXISTS `truck_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `driver_name`,
ADD COLUMN IF NOT EXISTS `status` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' AFTER `truck_number`,
ADD COLUMN IF NOT EXISTS `total_items_in_delivery_package` INT NOT NULL DEFAULT '0' AFTER `status`,
ADD COLUMN IF NOT EXISTS `out_time` datetime DEFAULT NULL AFTER `total_items_in_delivery_package`,
ADD COLUMN IF NOT EXISTS `odometer` INT DEFAULT NULL AFTER `out_time`,
ADD COLUMN IF NOT EXISTS `total_refrigerated_items` INT NOT NULL DEFAULT '0' AFTER `odometer`,
ADD COLUMN IF NOT EXISTS `assigned_by` INT DEFAULT NULL COMMENT 'User ID who assigned the delivery' AFTER `total_refrigerated_items`,
ADD COLUMN IF NOT EXISTS `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `assigned_by`,
ADD COLUMN IF NOT EXISTS `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Add indexes if they don't exist
ALTER TABLE `sma_deliveries` 
ADD INDEX IF NOT EXISTS `idx_status` (`status`),
ADD INDEX IF NOT EXISTS `idx_driver_name` (`driver_name`),
ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`),
ADD INDEX IF NOT EXISTS `idx_assigned_by` (`assigned_by`);

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