<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration 319: Create ETL Pipeline & Indexes
 * 
 * Creates:
 * - sma_etl_audit_log: ETL execution tracking
 * - Indexes for performance optimization
 * 
 * Date: 2025-10-25
 * Purpose: Automated data warehouse population and optimization
 */

class Migration_Update319 extends CI_Migration {

    public function up() {
        // ============================================================
        // Create ETL Logging Table
        // ============================================================
        $this->db->query("
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // ============================================================
        // Create Performance Indexes
        // ============================================================
        // Indexes on fact table
        $this->db->query("
            ALTER TABLE `sma_fact_cost_center`
            ADD INDEX idx_pharmacy_id (pharmacy_id),
            ADD INDEX idx_branch_id (branch_id),
            ADD INDEX idx_warehouse_period (warehouse_id, period_year, period_month)
        ");

        // Indexes on dimension tables
        $this->db->query("
            ALTER TABLE `sma_dim_pharmacy`
            ADD INDEX idx_country_id (country_id)
        ");

        $this->db->query("
            ALTER TABLE `sma_dim_branch`
            ADD INDEX idx_country_id (country_id)
        ");

        // Indexes on source tables for ETL joins
        $this->db->query("
            ALTER TABLE `sma_sales`
            ADD INDEX idx_warehouse_date (warehouse_id, DATE(date)) USING BTREE
        ");

        $this->db->query("
            ALTER TABLE `sma_purchases`
            ADD INDEX idx_warehouse_date (warehouse_id, DATE(purchase_date)) USING BTREE
        ");

        log_message('info', 'Migration 319: ETL Pipeline and indexes created successfully');
        return true;
    }

    public function down() {
        $this->db->query("DROP TABLE IF EXISTS `sma_etl_audit_log`");
        
        // Drop indexes
        $this->db->query("ALTER TABLE `sma_fact_cost_center` DROP INDEX idx_pharmacy_id");
        $this->db->query("ALTER TABLE `sma_fact_cost_center` DROP INDEX idx_branch_id");
        $this->db->query("ALTER TABLE `sma_fact_cost_center` DROP INDEX idx_warehouse_period");
        
        $this->db->query("ALTER TABLE `sma_dim_pharmacy` DROP INDEX idx_country_id");
        $this->db->query("ALTER TABLE `sma_dim_branch` DROP INDEX idx_country_id");
        
        log_message('info', 'Migration 319: ETL Pipeline and indexes rolled back');
        return true;
    }
}
