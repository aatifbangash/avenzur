-- ============================================================
-- Cost Center Migration: Create Sales & Purchases Aggregates
-- ============================================================
-- File: 012_create_sales_aggregates_tables.sql
-- Purpose: Create separate daily aggregate tables for sales metrics
--          with time-period breakdowns (today, current month, YTD)
-- Date: 2025-10-26
--
-- Creates:
-- - sma_sales_aggregates: Daily sales metrics (today, month, YTD)
-- - sma_purchases_aggregates: Daily purchase/cost metrics (today, month, YTD)
-- - sma_sales_aggregates_hourly: Hourly granularity for real-time dashboard
-- ============================================================

-- ============================================================
-- TABLE 1: sma_sales_aggregates
-- Purpose: Daily sales metrics aggregated by warehouse
-- Key Metrics:
--   - today_sales: Sales for calendar today
--   - current_month_sales: Sales from 1st to today of current month
--   - ytd_sales: Sales from Jan 1 to today of current year
--   - previous_day_sales: Sales for yesterday (for trend analysis)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_sales_aggregates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `warehouse_id` INT NOT NULL,
    `aggregate_date` DATE NOT NULL,
    `aggregate_year` INT NOT NULL,
    `aggregate_month` INT NOT NULL,

-- Today's Sales (calendar today)
`today_sales_amount` DECIMAL(12, 2) DEFAULT 0,
    `today_sales_count` INT DEFAULT 0,

-- Current Month Sales (1st to today)
`current_month_sales_amount` DECIMAL(12, 2) DEFAULT 0,
    `current_month_sales_count` INT DEFAULT 0,
    `current_month_start_date` DATE,

-- Year-to-Date Sales (Jan 1 to today)
`ytd_sales_amount` DECIMAL(12, 2) DEFAULT 0,
    `ytd_sales_count` INT DEFAULT 0,
    `ytd_start_date` DATE,

-- Previous Day (for trend)
`previous_day_sales_amount` DECIMAL(12, 2) DEFAULT 0,
    `previous_day_sales_count` INT DEFAULT 0,

-- Metadata
`last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

-- Indexes
UNIQUE KEY `uk_warehouse_date` (`warehouse_id`, `aggregate_date`),
    KEY `idx_aggregate_date` (`aggregate_date`),
    KEY `idx_warehouse_month` (`warehouse_id`, `aggregate_year`, `aggregate_month`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `sma_warehouses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 2: sma_purchases_aggregates
-- Purpose: Daily purchase/cost metrics aggregated by warehouse
-- Key Metrics:
--   - today_cost: Purchase cost for calendar today
--   - current_month_cost: Purchase cost from 1st to today of current month
--   - ytd_cost: Purchase cost from Jan 1 to today of current year
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_purchases_aggregates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `warehouse_id` INT NOT NULL,
    `aggregate_date` DATE NOT NULL,
    `aggregate_year` INT NOT NULL,
    `aggregate_month` INT NOT NULL,

-- Today's Cost (calendar today)
`today_cost_amount` DECIMAL(12, 2) DEFAULT 0,
    `today_cost_count` INT DEFAULT 0,

-- Current Month Cost (1st to today)
`current_month_cost_amount` DECIMAL(12, 2) DEFAULT 0,
    `current_month_cost_count` INT DEFAULT 0,
    `current_month_start_date` DATE,

-- Year-to-Date Cost (Jan 1 to today)
`ytd_cost_amount` DECIMAL(12, 2) DEFAULT 0,
    `ytd_cost_count` INT DEFAULT 0,
    `ytd_start_date` DATE,

-- Previous Day (for trend)
`previous_day_cost_amount` DECIMAL(12, 2) DEFAULT 0,
    `previous_day_cost_count` INT DEFAULT 0,

-- Metadata
`last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

-- Indexes
UNIQUE KEY `uk_warehouse_date` (`warehouse_id`, `aggregate_date`),
    KEY `idx_aggregate_date` (`aggregate_date`),
    KEY `idx_warehouse_month` (`warehouse_id`, `aggregate_year`, `aggregate_month`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `sma_warehouses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 3: sma_sales_aggregates_hourly
-- Purpose: Hourly granularity for real-time dashboard
-- Use Case: Real-time cost center dashboard updates
-- Retention: Keep last 90 days
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_sales_aggregates_hourly` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `warehouse_id` INT NOT NULL,
    `aggregate_date` DATE NOT NULL,
    `aggregate_hour` INT NOT NULL COMMENT '0-23 for hour of day',
    `aggregate_datetime` DATETIME NOT NULL,

-- Hourly Metrics
`hour_sales_amount` DECIMAL(12, 2) DEFAULT 0,
    `hour_sales_count` INT DEFAULT 0,

-- Running Totals
`today_sales_amount` DECIMAL(12, 2) DEFAULT 0,
    `today_sales_count` INT DEFAULT 0,

-- Metadata
`last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

-- Indexes
UNIQUE KEY `uk_warehouse_datetime` (`warehouse_id`, `aggregate_datetime`),
    KEY `idx_aggregate_date` (`aggregate_date`),
    KEY `idx_warehouse_today` (`warehouse_id`, `aggregate_date`),
    FOREIGN KEY (`warehouse_id`) REFERENCES `sma_warehouses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Create audit table for aggregate updates
-- ============================================================

CREATE TABLE IF NOT EXISTS `etl_sales_aggregates_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `process_name` VARCHAR(100) DEFAULT 'etl_sales_aggregates',
    `aggregate_date` DATE,
    `start_time` TIMESTAMP,
    `end_time` TIMESTAMP,
    `status` ENUM('STARTED', 'COMPLETED', 'FAILED') DEFAULT 'STARTED',
    `rows_processed` INT,
    `rows_updated` INT,
    `duration_seconds` INT,
    `error_message` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    KEY `idx_status` (`status`),
    KEY `idx_date` (`aggregate_date`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Migration 012 completed successfully.
-- Tables created:
-- - sma_sales_aggregates (daily sales metrics)
-- - sma_purchases_aggregates (daily purchase metrics)
-- - sma_sales_aggregates_hourly (hourly real-time metrics)
-- - etl_sales_aggregates_log (audit trail)
-- ============================================================