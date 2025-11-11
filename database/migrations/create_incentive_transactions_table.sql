-- Migration: Create incentive_transactions table
-- Purpose: Track pharmacist incentives earned per sale transaction
-- Date: 2025-11-10

CREATE TABLE IF NOT EXISTS `incentive_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pharmacist_id` int(11) NOT NULL COMMENT 'FK to sma_users',
  `sale_id` int(11) DEFAULT NULL COMMENT 'FK to sma_sales',
  `branch_id` int(11) DEFAULT NULL COMMENT 'FK to sma_warehouse',
  `product_id` int(11) NOT NULL COMMENT 'FK to sma_products',
  `batch_number` varchar(50) DEFAULT NULL,
  `qty_sold` decimal(15,4) NOT NULL,
  `sale_price` decimal(25,4) NOT NULL COMMENT 'Price per unit',
  `incentive_percentage` decimal(5,2) NOT NULL COMMENT 'Percentage earned',
  `incentive_amount` decimal(25,4) NOT NULL COMMENT 'sale_price * qty_sold * incentive_percentage / 100',
  `transaction_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pharmacist_id` (`pharmacist_id`),
  KEY `idx_sale_id` (`sale_id`),
  KEY `idx_branch_id` (`branch_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Pharmacist incentive earnings per transaction';
