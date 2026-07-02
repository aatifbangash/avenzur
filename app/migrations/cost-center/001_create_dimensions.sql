-- ============================================================
-- Cost Center Migration: Create Dimension Tables
-- ============================================================
-- File: 001_create_dimensions.sql
-- Purpose: Create dimension tables for Cost Center hierarchy
-- Date: 2025-10-25
--
-- Creates:
-- - sma_dim_pharmacy: Master pharmacy dimensions
-- - sma_dim_branch: Master branch dimensions with pharmacy parent reference
-- - sma_dim_date: Time dimension table for efficient time-based queries
-- ============================================================

-- ============================================================
-- Create dim_pharmacy table (Pharmacy Level Master)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_dim_pharmacy` (
    `pharmacy_id` INT(11) NOT NULL AUTO_INCREMENT,
    `warehouse_id` INT(11) NOT NULL UNIQUE,
    `pharmacy_name` VARCHAR(255) NOT NULL,
    `pharmacy_code` VARCHAR(50) NOT NULL UNIQUE,
    `address` VARCHAR(500),
    `phone` VARCHAR(55),
    `email` VARCHAR(100),
    `country_id` INT(11),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`pharmacy_id`),
    KEY `idx_warehouse_id` (`warehouse_id`),
    KEY `idx_pharmacy_code` (`pharmacy_code`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Populate dim_pharmacy from sma_warehouses (pharmacy type)
-- ============================================================
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
    w.parent_id IS NULL;

-- ============================================================
-- Create dim_branch table (Branch Level Master)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_dim_branch` (
    `branch_id` INT(11) NOT NULL AUTO_INCREMENT,
    `warehouse_id` INT(11) NOT NULL UNIQUE,
    `pharmacy_id` INT(11) NOT NULL,
    `pharmacy_warehouse_id` INT(11) NOT NULL,
    `branch_name` VARCHAR(255) NOT NULL,
    `branch_code` VARCHAR(50) NOT NULL UNIQUE,
    `address` VARCHAR(500),
    `phone` VARCHAR(55),
    `email` VARCHAR(100),
    `country_id` INT(11),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`branch_id`),
    KEY `idx_warehouse_id` (`warehouse_id`),
    KEY `idx_pharmacy_id` (`pharmacy_id`),
    KEY `idx_pharmacy_warehouse_id` (`pharmacy_warehouse_id`),
    KEY `idx_branch_code` (`branch_code`),
    KEY `idx_is_active` (`is_active`),
    FOREIGN KEY (`pharmacy_id`) REFERENCES `sma_dim_pharmacy` (`pharmacy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Populate dim_branch from sma_warehouses (branch type)
-- ============================================================
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
    b.parent_id IS NOT NULL;

-- ============================================================
-- Create dim_date table (Time Dimension)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_dim_date` (
    `date_id` INT(11) NOT NULL AUTO_INCREMENT,
    `date` DATE NOT NULL UNIQUE,
    `day_of_week` INT(2),
    `day_name` VARCHAR(10),
    `day_of_month` INT(2),
    `month` INT(2),
    `month_name` VARCHAR(10),
    `quarter` INT(1),
    `year` INT(4),
    `week_of_year` INT(2),
    `is_weekday` TINYINT(1),
    `is_holiday` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`date_id`),
    KEY `idx_date` (`date`),
    KEY `idx_year_month` (`year`, `month`),
    KEY `idx_quarter` (`year`, `quarter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Completion Message
-- ============================================================
-- Migration 001 completed successfully.
-- Tables created:
-- - sma_dim_pharmacy (with populated data)
-- - sma_dim_branch (with populated data and FK constraint)
-- - sma_dim_date (ready for data)
-- ============================================================