-- ============================================================
-- Cost Center Migration: Create ETL Audit Log & Indexes
-- ============================================================
-- File: 003_create_etl_audit_log.sql
-- Purpose: Create ETL pipeline audit logging and performance indexes
-- Date: 2025-10-25
--
-- Creates:
-- - sma_etl_audit_log: Track ETL pipeline execution
-- - Performance indexes on fact table and dimension tables
-- ============================================================

-- ============================================================
-- Create ETL Logging Table
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_etl_audit_log` (
    `log_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `process_name` VARCHAR(100) NOT NULL,
    `start_time` TIMESTAMP NOT NULL,
    `end_time` TIMESTAMP NULL,
    `status` ENUM('STARTED', 'COMPLETED', 'FAILED', 'PARTIAL') DEFAULT 'STARTED',
    `rows_processed` INT(11) DEFAULT 0,
    `rows_inserted` INT(11) DEFAULT 0,
    `rows_updated` INT(11) DEFAULT 0,
    `error_message` TEXT,
    `duration_seconds` INT(11),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    KEY `idx_process_date` (`process_name`, `start_time`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Create Additional Performance Indexes
-- ============================================================

-- Indexes on sma_fact_cost_center (additional to primary key)
ALTER TABLE `sma_fact_cost_center` ADD KEY `idx_warehouse_id` (`warehouse_id`);

ALTER TABLE `sma_fact_cost_center` ADD KEY `idx_pharmacy_id` (`pharmacy_id`);

ALTER TABLE `sma_fact_cost_center` ADD KEY `idx_branch_id` (`branch_id`);

ALTER TABLE `sma_fact_cost_center` ADD KEY `idx_period` (`period_year`, `period_month`);

-- Indexes on sma_dim_pharmacy
ALTER TABLE `sma_dim_pharmacy` ADD KEY `idx_is_active` (`is_active`);

-- Indexes on sma_dim_branch
ALTER TABLE `sma_dim_branch` ADD KEY `idx_is_active` (`is_active`);

-- ============================================================
-- Completion Message
-- ============================================================
-- Migration 003 completed successfully.
-- Tables created:
-- - sma_etl_audit_log (ready for ETL tracking)
-- Indexes created for optimal performance
-- ============================================================