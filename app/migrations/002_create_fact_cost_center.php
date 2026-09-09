<?php
/**
 * Migration: Create Cost Center Fact Table & KPI Views
 * 
 * Creates:
 * - fact_cost_center: Denormalized fact table with all cost components
 * - view_cost_center_pharmacy: Monthly KPI aggregates at pharmacy level
 * - view_cost_center_branch: Monthly KPI aggregates at branch level
 * 
 * Date: 2025-10-25
 * Purpose: Aggregated cost and revenue data for analysis
 */

class Migration_002_create_fact_cost_center extends CI_Migration {

    public function up() {
        // ============================================================
        // Create fact_cost_center table (Main Fact Table)
        // ============================================================
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `fact_cost_center` (
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
                `total_cost` DECIMAL(18,2) GENERATED ALWAYS AS (total_cogs + inventory_movement_cost + operational_cost) STORED,
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // ============================================================
        // Create view_cost_center_pharmacy (Pharmacy Level KPIs)
        // ============================================================
        $this->db->query("
            CREATE OR REPLACE VIEW `view_cost_center_pharmacy` AS
            SELECT 
                dp.pharmacy_id,
                dp.warehouse_id,
                dp.pharmacy_name,
                dp.pharmacy_code,
                CONCAT(YEAR(f.transaction_date), '-', LPAD(MONTH(f.transaction_date), 2, '0')) AS period,
                COUNT(DISTINCT f.transaction_date) AS days_active,
                SUM(f.total_revenue) AS kpi_total_revenue,
                SUM(f.total_cogs) AS kpi_cogs,
                SUM(f.inventory_movement_cost) AS kpi_inventory_movement,
                SUM(f.operational_cost) AS kpi_operational,
                SUM(f.total_cost) AS kpi_total_cost,
                (SUM(f.total_revenue) - SUM(f.total_cost)) AS kpi_profit_loss,
                CASE 
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND(((SUM(f.total_revenue) - SUM(f.total_cost)) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                CASE 
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND((SUM(f.total_cost) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_cost_ratio_pct,
                (SELECT COUNT(*) FROM dim_branch WHERE pharmacy_id = dp.pharmacy_id) AS branch_count,
                MAX(f.updated_at) AS last_updated
            FROM fact_cost_center f
            INNER JOIN dim_pharmacy dp ON f.warehouse_id = dp.warehouse_id
            WHERE f.warehouse_type IN ('pharmacy', 'mainwarehouse')
            GROUP BY dp.pharmacy_id, dp.warehouse_id, dp.pharmacy_name, dp.pharmacy_code, 
                     YEAR(f.transaction_date), MONTH(f.transaction_date)
            ORDER BY period DESC, kpi_total_revenue DESC;
        ");

        // ============================================================
        // Create view_cost_center_branch (Branch Level KPIs)
        // ============================================================
        $this->db->query("
            CREATE OR REPLACE VIEW `view_cost_center_branch` AS
            SELECT 
                db.branch_id,
                db.warehouse_id,
                db.branch_name,
                db.branch_code,
                dp.pharmacy_id,
                dp.pharmacy_name,
                CONCAT(YEAR(f.transaction_date), '-', LPAD(MONTH(f.transaction_date), 2, '0')) AS period,
                COUNT(DISTINCT f.transaction_date) AS days_active,
                SUM(f.total_revenue) AS kpi_total_revenue,
                SUM(f.total_cogs) AS kpi_cogs,
                SUM(f.inventory_movement_cost) AS kpi_inventory_movement,
                SUM(f.operational_cost) AS kpi_operational,
                SUM(f.total_cost) AS kpi_total_cost,
                (SUM(f.total_revenue) - SUM(f.total_cost)) AS kpi_profit_loss,
                CASE 
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND(((SUM(f.total_revenue) - SUM(f.total_cost)) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                CASE 
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND((SUM(f.total_cost) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_cost_ratio_pct,
                MAX(f.updated_at) AS last_updated
            FROM fact_cost_center f
            INNER JOIN dim_branch db ON f.warehouse_id = db.warehouse_id
            INNER JOIN dim_pharmacy dp ON db.pharmacy_id = dp.pharmacy_id
            WHERE f.warehouse_type = 'branch'
            GROUP BY db.branch_id, db.warehouse_id, db.branch_name, db.branch_code,
                     dp.pharmacy_id, dp.pharmacy_name,
                     YEAR(f.transaction_date), MONTH(f.transaction_date)
            ORDER BY period DESC, kpi_total_revenue DESC;
        ");

        // ============================================================
        // Create view_cost_center_summary (Company Level Overview)
        // ============================================================
        $this->db->query("
            CREATE OR REPLACE VIEW `view_cost_center_summary` AS
            SELECT 
                'COMPANY' AS level,
                NULL AS entity_id,
                'Company Total' AS entity_name,
                CONCAT(YEAR(transaction_date), '-', LPAD(MONTH(transaction_date), 2, '0')) AS period,
                SUM(total_revenue) AS kpi_total_revenue,
                SUM(total_cost) AS kpi_total_cost,
                (SUM(total_revenue) - SUM(total_cost)) AS kpi_profit_loss,
                CASE 
                    WHEN SUM(total_revenue) = 0 THEN 0
                    ELSE ROUND(((SUM(total_revenue) - SUM(total_cost)) / SUM(total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                COUNT(DISTINCT warehouse_id) AS entity_count,
                MAX(updated_at) AS last_updated
            FROM fact_cost_center
            GROUP BY YEAR(transaction_date), MONTH(transaction_date)
            ORDER BY period DESC;
        ");

        log_message('info', 'Migration 002: Fact Table & KPI Views created successfully');
        return true;
    }

    public function down() {
        $this->db->query("DROP VIEW IF EXISTS `view_cost_center_summary`");
        $this->db->query("DROP VIEW IF EXISTS `view_cost_center_branch`");
        $this->db->query("DROP VIEW IF EXISTS `view_cost_center_pharmacy`");
        $this->db->query("DROP TABLE IF EXISTS `fact_cost_center`");
        
        log_message('info', 'Migration 002: Fact Table & KPI Views rolled back');
        return true;
    }
}
