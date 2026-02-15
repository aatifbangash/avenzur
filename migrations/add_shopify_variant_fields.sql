-- Migration: Add Shopify-style fields to products and variants
-- Date: 2026-02-11
-- Purpose: Support Shopify-like product and variant management

-- Add missing fields to sma_products
ALTER TABLE `sma_products` 
ADD COLUMN IF NOT EXISTS `vendor` VARCHAR(255) DEFAULT NULL COMMENT 'Brand/Manufacturer/Vendor' AFTER `brand`,
ADD COLUMN IF NOT EXISTS `product_type` VARCHAR(100) DEFAULT NULL COMMENT 'Custom product type/category' AFTER `vendor`,
ADD COLUMN IF NOT EXISTS `tags` TEXT DEFAULT NULL COMMENT 'Comma-separated searchable tags' AFTER `product_type`,
ADD COLUMN IF NOT EXISTS `handle` VARCHAR(255) DEFAULT NULL COMMENT 'URL-friendly identifier' AFTER `slug`,
ADD COLUMN IF NOT EXISTS `published_at` DATETIME DEFAULT NULL COMMENT 'Publication timestamp' AFTER `hide`,
ADD COLUMN IF NOT EXISTS `status` ENUM('active','draft','archived') DEFAULT 'draft' COMMENT 'Product status' AFTER `published_at`,
ADD COLUMN IF NOT EXISTS `seo_title` VARCHAR(255) DEFAULT NULL COMMENT 'SEO page title' AFTER `status`,
ADD COLUMN IF NOT EXISTS `seo_description` TEXT DEFAULT NULL COMMENT 'SEO meta description' AFTER `seo_title`,
ADD COLUMN IF NOT EXISTS `compare_at_price` DECIMAL(25,4) DEFAULT NULL COMMENT 'Original price for discount display' AFTER `price`,
ADD COLUMN IF NOT EXISTS `requires_shipping` TINYINT(1) DEFAULT 1 COMMENT 'Product needs shipping' AFTER `weight`,
ADD COLUMN IF NOT EXISTS `taxable` TINYINT(1) DEFAULT 1 COMMENT 'Product is taxable' AFTER `tax_method`,
ADD COLUMN IF NOT EXISTS `options_json` TEXT DEFAULT NULL COMMENT 'JSON array of product options' AFTER `tags`,
ADD COLUMN IF NOT EXISTS `name_ar` VARCHAR(255) DEFAULT NULL COMMENT 'Arabic product name' AFTER `name`;

-- Add index on handle for SEO URLs
ALTER TABLE `sma_products` ADD INDEX IF NOT EXISTS `idx_handle` (`handle`);
ALTER TABLE `sma_products` ADD INDEX IF NOT EXISTS `idx_status` (`status`);
ALTER TABLE `sma_products` ADD INDEX IF NOT EXISTS `idx_vendor` (`vendor`);

-- Enhance sma_product_variants table
ALTER TABLE `sma_product_variants`
ADD COLUMN IF NOT EXISTS `sku` VARCHAR(100) DEFAULT NULL COMMENT 'Variant SKU' AFTER `name`,
ADD COLUMN IF NOT EXISTS `barcode` VARCHAR(100) DEFAULT NULL COMMENT 'Variant barcode' AFTER `sku`,
ADD COLUMN IF NOT EXISTS `option1` VARCHAR(100) DEFAULT NULL COMMENT 'First option value (e.g., Size: Small)' AFTER `barcode`,
ADD COLUMN IF NOT EXISTS `option2` VARCHAR(100) DEFAULT NULL COMMENT 'Second option value (e.g., Color: Red)' AFTER `option1`,
ADD COLUMN IF NOT EXISTS `option3` VARCHAR(100) DEFAULT NULL COMMENT 'Third option value (e.g., Material: Cotton)' AFTER `option2`,
ADD COLUMN IF NOT EXISTS `compare_at_price` DECIMAL(25,4) DEFAULT NULL COMMENT 'Original price for discount' AFTER `price`,
ADD COLUMN IF NOT EXISTS `weight` DECIMAL(10,4) DEFAULT NULL COMMENT 'Variant weight' AFTER `compare_at_price`,
ADD COLUMN IF NOT EXISTS `weight_unit` VARCHAR(10) DEFAULT 'kg' COMMENT 'Weight unit (kg, g, lb, oz)' AFTER `weight`,
ADD COLUMN IF NOT EXISTS `image` VARCHAR(255) DEFAULT NULL COMMENT 'Variant-specific image' AFTER `weight_unit`,
ADD COLUMN IF NOT EXISTS `position` INT DEFAULT 0 COMMENT 'Display order' AFTER `image`,
ADD COLUMN IF NOT EXISTS `taxable` TINYINT(1) DEFAULT 1 COMMENT 'Variant is taxable' AFTER `position`,
ADD COLUMN IF NOT EXISTS `requires_shipping` TINYINT(1) DEFAULT 1 COMMENT 'Variant needs shipping' AFTER `taxable`,
ADD COLUMN IF NOT EXISTS `inventory_management` TINYINT(1) DEFAULT 1 COMMENT 'Track inventory for this variant' AFTER `requires_shipping`,
ADD COLUMN IF NOT EXISTS `inventory_policy` ENUM('deny','continue') DEFAULT 'deny' COMMENT 'Allow overselling' AFTER `inventory_management`,
ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `inventory_policy`,
ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Add indexes for variant lookups
ALTER TABLE `sma_product_variants` ADD INDEX IF NOT EXISTS `idx_sku` (`sku`);
ALTER TABLE `sma_product_variants` ADD INDEX IF NOT EXISTS `idx_barcode` (`barcode`);
ALTER TABLE `sma_product_variants` ADD INDEX IF NOT EXISTS `idx_product_options` (`product_id`, `option1`, `option2`, `option3`);

-- Create table for product collections (like Shopify collections)
CREATE TABLE IF NOT EXISTS `sma_product_collections` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `slug` VARCHAR(255) DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','draft') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create junction table for product-collection relationships
CREATE TABLE IF NOT EXISTS `sma_product_collection_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `collection_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `position` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection_product` (`collection_id`, `product_id`),
  KEY `idx_collection` (`collection_id`),
  KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comments to tables
ALTER TABLE `sma_products` COMMENT = 'Main products table with Shopify-compatible fields';
ALTER TABLE `sma_product_variants` COMMENT = 'Product variants with Shopify-style options';
ALTER TABLE `sma_product_collections` COMMENT = 'Product collections (like Shopify collections)';
