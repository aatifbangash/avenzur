<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration 318: Create Cost Center Fact Table & KPI Views
 * 
 * Creates:
 * - sma_fact_cost_center: Denormalized fact table with all cost components
 * - sma_view_cost_center_pharmacy: Monthly KPI aggregates at pharmacy level
 * - sma_view_cost_center_branch: Monthly KPI aggregates at branch level
 * - sma_view_cost_center_summary: Company level overview
 * 
 * Date: 2025-10-25
 * Purpose: Aggregated cost and revenue data for analysis
 */

class Migration_Update318 extends CI_Migration {

    public function up() {
        // ============================================================
        // Create fact_cost_center table (Main Fact Table)
        // ============================================================
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `sma_fact_cost_center` (
                `fact_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
                `warehouse_id` INT(11) NOT NULL,
                `warehouse_name` VARCHAR(255) NOT NULL,
                `warehouse_type` VARCHAR(25) NOT NULL,
                `pharmacy_id` INT(11),
                `pharmacy_name` VARCHAR(255),
                `branch_id` INT(11),
                `branch_name` VARCHAR(255),
                `parent_warehouse_id` INT(11),
                `transaction_date` DATE NOT NULL,
                `period_year` INT(4) NOT NULL,
                `period_month` INT(2) NOT NULL,
                `total_revenue` DECIMAL(18,2) DEFAULT 0.00,
                `total_cogs` DECIMAL(18,2) DEFAULT 0.00,
                `inventory_movement_cost` DECIMAL(18,2) DEFAULT 0.00,
                `operational_cost` DECIMAL(18,2) DEFAULT 0.00,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`fact_id`),
                UNIQUE KEY `uk_warehouse_date` (`warehouse_id`, `transaction_date`),
                KEY `idx_warehouse_type` (`warehouse_type`),
                KEY `idx_transaction_date` (`transaction_date`),
                KEY `idx_period_year_month` (`period_year`, `period_month`),
                KEY `idx_warehouse_transaction` (`warehouse_id`, `transaction_date`),
                KEY `idx_pharmacy_date` (`pharmacy_id`, `transaction_date`),
                KEY `idx_branch_date` (`branch_id`, `transaction_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        log_message('info', 'Migration 318: Fact Table created successfully');
        return true;
    }

    public function down() {
        $this->db->query("DROP TABLE IF EXISTS `sma_fact_cost_center`");
        
        log_message('info', 'Migration 318: Fact Table rolled back');
        return true;
    }
}
