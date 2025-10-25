<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration 320: Populate Cost Center Fact Table with Sample Data
 * 
 * Loads sample data from:
 * - sma_sales (revenue data)
 * - sma_purchases (cost data)
 * 
 * Date: 2025-10-25
 * Purpose: Initialize fact table with data for testing and reporting
 */

class Migration_Update320 extends CI_Migration {

    public function up() {
        // ============================================================
        // Populate Date Dimension (Last 2 years)
        // ============================================================
        $this->db->query("
            INSERT IGNORE INTO `sma_dim_date` (
                `date`, `day_of_week`, `day_name`, `day_of_month`, `month`, `month_name`,
                `quarter`, `year`, `week_of_year`, `is_weekday`, `is_holiday`
            )
            WITH RECURSIVE DateRange AS (
                SELECT DATE_SUB(CURDATE(), INTERVAL 2 YEAR) AS dt
                UNION ALL
                SELECT DATE_ADD(dt, INTERVAL 1 DAY)
                FROM DateRange
                WHERE dt < CURDATE() + INTERVAL 1 YEAR
            )
            SELECT 
                dt,
                DAYOFWEEK(dt) - 1,
                DAYNAME(dt),
                DAYOFMONTH(dt),
                MONTH(dt),
                MONTHNAME(dt),
                QUARTER(dt),
                YEAR(dt),
                WEEK(dt),
                CASE WHEN DAYOFWEEK(dt) BETWEEN 2 AND 6 THEN 1 ELSE 0 END,
                0
            FROM DateRange;
        ");

        // ============================================================
        // Populate Fact Table with Sales Revenue (Last 90 Days)
        // ============================================================
        $this->db->query("
            INSERT INTO `sma_fact_cost_center` (
                `warehouse_id`, `warehouse_name`, `warehouse_type`,
                `pharmacy_id`, `pharmacy_name`, `branch_id`, `branch_name`,
                `parent_warehouse_id`, `transaction_date`, `period_year`, `period_month`,
                `total_revenue`
            )
            SELECT 
                w.id,
                w.name,
                COALESCE(w.warehouse_type, 'pharmacy'),
                NULL,
                NULL,
                CASE WHEN w.parent_id IS NOT NULL THEN w.id ELSE NULL END,
                CASE WHEN w.parent_id IS NOT NULL THEN w.name ELSE NULL END,
                w.parent_id,
                DATE(s.date),
                YEAR(s.date),
                MONTH(s.date),
                COALESCE(SUM(s.grand_total), 0)
            FROM sma_sales s
            LEFT JOIN sma_warehouses w ON s.warehouse_id = w.id
            WHERE s.date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            AND s.sale_status = 'completed'
            GROUP BY w.id, w.name, w.warehouse_type, w.parent_id, DATE(s.date)
            ON DUPLICATE KEY UPDATE
                total_revenue = GREATEST(total_revenue, VALUES(total_revenue))
        ");

        // ============================================================
        // Populate Fact Table with Purchase Costs (Last 90 Days)
        // ============================================================
        $this->db->query("
            INSERT INTO `sma_fact_cost_center` (
                `warehouse_id`, `warehouse_name`, `warehouse_type`,
                `pharmacy_id`, `pharmacy_name`, `branch_id`, `branch_name`,
                `parent_warehouse_id`, `transaction_date`, `period_year`, `period_month`,
                `total_cogs`
            )
            SELECT 
                w.id,
                w.name,
                COALESCE(w.warehouse_type, 'pharmacy'),
                NULL,
                NULL,
                CASE WHEN w.parent_id IS NOT NULL THEN w.id ELSE NULL END,
                CASE WHEN w.parent_id IS NOT NULL THEN w.name ELSE NULL END,
                w.parent_id,
                DATE(p.purchase_date),
                YEAR(p.purchase_date),
                MONTH(p.purchase_date),
                COALESCE(SUM(p.total_cost), 0)
            FROM sma_purchases p
            LEFT JOIN sma_warehouses w ON p.warehouse_id = w.id
            WHERE p.purchase_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
            AND p.purchase_status = 'completed'
            GROUP BY w.id, w.name, w.warehouse_type, w.parent_id, DATE(p.purchase_date)
            ON DUPLICATE KEY UPDATE
                total_cogs = GREATEST(total_cogs, VALUES(total_cogs))
        ");

        log_message('info', 'Migration 320: Sample data loaded into fact table successfully');
        return true;
    }

    public function down() {
        // Optional: Clear sample data
        $this->db->query("DELETE FROM `sma_fact_cost_center` WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)");
        $this->db->query("DELETE FROM `sma_dim_date` WHERE year >= YEAR(DATE_SUB(CURDATE(), INTERVAL 2 YEAR))");
        
        log_message('info', 'Migration 320: Sample data rolled back');
        return true;
    }
}
