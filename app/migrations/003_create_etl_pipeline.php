<?php
/**
 * Migration: Create ETL Pipeline & Performance Indexes
 * 
 * Creates:
 * - Stored procedure: sp_populate_fact_cost_center (Daily ETL)
 * - Stored procedure: sp_calculate_kpis (KPI calculation)
 * - Performance indexes on fact table and source tables
 * - ETL logging table
 * 
 * Date: 2025-10-25
 * Purpose: Automated data warehouse population and optimization
 */

class Migration_003_create_etl_pipeline extends CI_Migration {

    public function up() {
        // ============================================================
        // Create ETL Logging Table
        // ============================================================
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `etl_audit_log` (
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
        ");

        // ============================================================
        // Create Stored Procedure: sp_populate_fact_cost_center
        // ============================================================
        $this->db->query("
            CREATE PROCEDURE IF NOT EXISTS `sp_populate_fact_cost_center`(
                IN p_transaction_date DATE
            )
            READS SQL DATA
            MODIFIES SQL DATA
            BEGIN
                DECLARE v_log_id BIGINT;
                DECLARE v_rows_processed INT DEFAULT 0;
                DECLARE v_start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
                
                -- Start logging
                INSERT INTO etl_audit_log (process_name, start_time, status)
                VALUES ('sp_populate_fact_cost_center', CURRENT_TIMESTAMP, 'STARTED')
                SET v_log_id = LAST_INSERT_ID();
                
                BEGIN
                    -- Delete existing data for the date (safe to re-run)
                    DELETE FROM fact_cost_center 
                    WHERE DATE(transaction_date) = p_transaction_date;
                    
                    -- Insert aggregated sales revenue
                    INSERT INTO fact_cost_center (
                        warehouse_id, warehouse_name, warehouse_type, 
                        pharmacy_id, pharmacy_name, branch_id, branch_name,
                        parent_warehouse_id, transaction_date, period_year, period_month,
                        total_revenue
                    )
                    SELECT 
                        w.id,
                        w.name,
                        COALESCE(w.warehouse_type, 'warehouse'),
                        CASE WHEN w.warehouse_type = 'branch' THEN w.parent_id ELSE NULL END,
                        CASE WHEN w.warehouse_type = 'branch' THEN pw.name ELSE NULL END,
                        CASE WHEN w.warehouse_type = 'branch' THEN w.id ELSE NULL END,
                        CASE WHEN w.warehouse_type = 'branch' THEN w.name ELSE NULL END,
                        w.parent_id,
                        DATE(s.date),
                        YEAR(s.date),
                        MONTH(s.date),
                        COALESCE(SUM(s.grand_total), 0)
                    FROM sma_sales s
                    LEFT JOIN sma_warehouses w ON s.warehouse_id = w.id
                    LEFT JOIN sma_warehouses pw ON w.parent_id = pw.id
                    WHERE DATE(s.date) = p_transaction_date
                    AND s.sale_status = 'completed'
                    GROUP BY w.id, w.name, w.warehouse_type, w.parent_id, DATE(s.date)
                    ON DUPLICATE KEY UPDATE
                        total_revenue = VALUES(total_revenue);
                    
                    -- Update with purchase costs (COGS)
                    INSERT INTO fact_cost_center (
                        warehouse_id, warehouse_name, warehouse_type,
                        pharmacy_id, pharmacy_name, branch_id, branch_name,
                        parent_warehouse_id, transaction_date, period_year, period_month,
                        total_cogs
                    )
                    SELECT 
                        w.id,
                        w.name,
                        COALESCE(w.warehouse_type, 'warehouse'),
                        CASE WHEN w.warehouse_type = 'branch' THEN w.parent_id ELSE NULL END,
                        CASE WHEN w.warehouse_type = 'branch' THEN pw.name ELSE NULL END,
                        CASE WHEN w.warehouse_type = 'branch' THEN w.id ELSE NULL END,
                        CASE WHEN w.warehouse_type = 'branch' THEN w.name ELSE NULL END,
                        w.parent_id,
                        DATE(p.date),
                        YEAR(p.date),
                        MONTH(p.date),
                        COALESCE(SUM(p.grand_total), 0)
                    FROM sma_purchases p
                    LEFT JOIN sma_warehouses w ON p.warehouse_id = w.id
                    LEFT JOIN sma_warehouses pw ON w.parent_id = pw.id
                    WHERE DATE(p.date) = p_transaction_date
                    AND p.status = 'received'
                    GROUP BY w.id, w.name, w.warehouse_type, w.parent_id, DATE(p.date)
                    ON DUPLICATE KEY UPDATE
                        total_cogs = VALUES(total_cogs);
                    
                    -- Update with operational costs (shipping, surcharge from purchases)
                    UPDATE fact_cost_center f
                    SET f.operational_cost = (
                        SELECT COALESCE(SUM(p.shipping + COALESCE(p.surcharge, 0)), 0)
                        FROM sma_purchases p
                        LEFT JOIN sma_warehouses w ON p.warehouse_id = w.id
                        WHERE w.id = f.warehouse_id
                        AND DATE(p.date) = p_transaction_date
                        AND p.status = 'received'
                    )
                    WHERE DATE(f.transaction_date) = p_transaction_date;
                    
                    -- Update inventory movement costs (if table exists)
                    -- This assumes sma_inventory_movement table has transfer costs
                    
                    -- Update ETL log
                    UPDATE etl_audit_log 
                    SET end_time = CURRENT_TIMESTAMP,
                        status = 'COMPLETED',
                        rows_processed = (SELECT COUNT(*) FROM fact_cost_center WHERE DATE(transaction_date) = p_transaction_date),
                        duration_seconds = TIMESTAMPDIFF(SECOND, v_start_time, CURRENT_TIMESTAMP)
                    WHERE log_id = v_log_id;
                    
                END;
            END
        ");

        // ============================================================
        // Create Stored Procedure: sp_backfill_fact_cost_center
        // ============================================================
        $this->db->query("
            CREATE PROCEDURE IF NOT EXISTS `sp_backfill_fact_cost_center`(
                IN p_start_date DATE,
                IN p_end_date DATE
            )
            READS SQL DATA
            MODIFIES SQL DATA
            BEGIN
                DECLARE v_current_date DATE;
                SET v_current_date = p_start_date;
                
                WHILE v_current_date <= p_end_date DO
                    CALL sp_populate_fact_cost_center(v_current_date);
                    SET v_current_date = DATE_ADD(v_current_date, INTERVAL 1 DAY);
                END WHILE;
            END
        ");

        // ============================================================
        // Create Performance Indexes
        // ============================================================
        
        // Indexes on fact_cost_center (already in main table creation)
        
        // Indexes on sma_sales for ETL performance
        $this->db->query("
            ALTER TABLE sma_sales 
            ADD KEY IF NOT EXISTS `idx_warehouse_date` (`warehouse_id`, `date`),
            ADD KEY IF NOT EXISTS `idx_sale_status` (`sale_status`),
            ADD KEY IF NOT EXISTS `idx_date_status` (`date`, `sale_status`)
        ");

        // Indexes on sma_purchases for ETL performance
        $this->db->query("
            ALTER TABLE sma_purchases 
            ADD KEY IF NOT EXISTS `idx_warehouse_date` (`warehouse_id`, `date`),
            ADD KEY IF NOT EXISTS `idx_purchase_status` (`status`),
            ADD KEY IF NOT EXISTS `idx_date_status` (`date`, `status`)
        ");

        // Indexes on sma_warehouses for hierarchy queries
        $this->db->query("
            ALTER TABLE sma_warehouses 
            ADD KEY IF NOT EXISTS `idx_warehouse_type` (`warehouse_type`),
            ADD KEY IF NOT EXISTS `idx_parent_id` (`parent_id`),
            ADD KEY IF NOT EXISTS `idx_type_parent` (`warehouse_type`, `parent_id`)
        ");

        log_message('info', 'Migration 003: ETL Pipeline & Indexes created successfully');
        return true;
    }

    public function down() {
        // Drop procedures
        $this->db->query("DROP PROCEDURE IF EXISTS `sp_backfill_fact_cost_center`");
        $this->db->query("DROP PROCEDURE IF EXISTS `sp_populate_fact_cost_center`");
        
        // Drop ETL log table
        $this->db->query("DROP TABLE IF EXISTS `etl_audit_log`");
        
        // Remove indexes from source tables
        $this->db->query("ALTER TABLE sma_sales DROP KEY IF EXISTS `idx_warehouse_date`, DROP KEY IF EXISTS `idx_sale_status`, DROP KEY IF EXISTS `idx_date_status`");
        $this->db->query("ALTER TABLE sma_purchases DROP KEY IF EXISTS `idx_warehouse_date`, DROP KEY IF EXISTS `idx_purchase_status`, DROP KEY IF EXISTS `idx_date_status`");
        $this->db->query("ALTER TABLE sma_warehouses DROP KEY IF EXISTS `idx_warehouse_type`, DROP KEY IF EXISTS `idx_parent_id`, DROP KEY IF EXISTS `idx_type_parent`");
        
        log_message('info', 'Migration 003: ETL Pipeline & Indexes rolled back');
        return true;
    }
}
