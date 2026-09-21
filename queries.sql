CREATE TABLE `sma_purchase_requisitions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pr_number` VARCHAR(50) NOT NULL,
  `requested_by` INT(11) UNSIGNED NOT NULL,
  `warehouse_id` INT(11) UNSIGNED NOT NULL,
  `status` ENUM('draft', 'submitted', 'approved', 'rejected', 'closed') DEFAULT 'draft',
  `remarks` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_pr_number` (`pr_number`),
  KEY `idx_requested_by` (`requested_by`),
  KEY `idx_warehouse_id` (`warehouse_id`)
) DEFAULT CHARSET=utf8mb4;



CREATE TABLE `sma_purchase_requisition_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `requisition_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `quantity` DECIMAL(10,5) NOT NULL DEFAULT 0,
  `remarks` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_requisition_id` (`requisition_id`),
  KEY `idx_product_id` (`product_id`)
) DEFAULT CHARSET=utf8mb4;