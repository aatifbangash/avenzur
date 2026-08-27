<?php
/**
 * Migration Template: Add New KPI to Cost Center Module
 * 
 * Usage Instructions:
 * 1. Copy this file to: app/migrations/
 * 2. Rename to: XXX_add_new_kpi.php (where XXX = next migration number)
 * 3. Replace [KPI_NAME] and [KPI_COLUMN] with your KPI details
 * 4. Run migration: http://domain/admin/migrate (or CLI)
 * 5. Update etl_cost_center.php if needed
 * 
 * Example: Adding "discount_rate_pct"
 * - [KPI_NAME] = "discount_rate_pct"
 * - [KPI_COLUMN] = "discount_amount"
 * - [CALCULATION] = "SUM(f.discount_amount) / SUM(f.total_revenue) * 100"
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_new_kpi extends CI_Migration {

    public function up() {
        // ============================================================
        // STEP 1: Add column to fact table (if pulling from source data)
        // ============================================================
        
        // Only add if you're tracking a new source metric
        // Remove this section if using existing columns
        if (!$this->db->field_exists('[KPI_COLUMN]', 'fact_cost_center')) {
            $this->dbforge->add_column('fact_cost_center', array(
                '[KPI_COLUMN]' => array(
                    'type'       => 'DECIMAL',
                    'constraint' => '18,2',
                    'default'    => 0,
                    'null'       => FALSE,
                    'comment'    => 'Source data for [KPI_NAME] calculation'
                )
            ));
            log_message('info', 'Added column [KPI_COLUMN] to fact_cost_center');
        }

        // ============================================================
        // STEP 2: Add GENERATED column for derived KPI (optional)
        // ============================================================
        
        // Use this if KPI is calculated from existing columns
        // This is the PREFERRED approach (auto-calculated, always fresh)
        
        $this->db->query("
            ALTER TABLE `fact_cost_center` ADD COLUMN `kpi_[KPI_NAME]_generated` DECIMAL(10,2) 
            GENERATED ALWAYS AS (
                CASE 
                    WHEN [DENOMINATOR_COLUMN] > 0 
                    THEN [NUMERATOR_COLUMN] / [DENOMINATOR_COLUMN] * 100
                    ELSE 0 
                END
            ) STORED
            COMMENT 'Auto-calculated [KPI_NAME] from existing columns'
        ");
        log_message('info', 'Added GENERATED column for [KPI_NAME]');

        // ============================================================
        // STEP 3: Add index for performance (if frequently used)
        // ============================================================
        
        $this->db->query("
            ALTER TABLE `fact_cost_center` 
            ADD INDEX `idx_kpi_[KPI_NAME]` (`kpi_[KPI_NAME]_generated`)
        ");
        log_message('info', 'Added index on [KPI_NAME]');

        // ============================================================
        // STEP 4: Update view_cost_center_pharmacy
        // ============================================================
        
        $this->db->query("
            CREATE OR REPLACE VIEW `view_cost_center_pharmacy` AS
            SELECT 
                dp.pharmacy_id,
                dp.warehouse_id,
                dp.pharmacy_name,
                dp.pharmacy_code,
                f.period_year,
                f.period_month,
                CONCAT(f.period_year, '-', LPAD(f.period_month, 2, '0')) AS period,
                
                -- Existing KPIs
                COALESCE(SUM(f.total_revenue), 0) AS kpi_total_revenue,
                COALESCE(SUM(f.total_cogs), 0) AS kpi_cogs,
                COALESCE(SUM(f.inventory_movement_cost), 0) AS kpi_inventory_movement,
                COALESCE(SUM(f.operational_cost), 0) AS kpi_operational,
                COALESCE(SUM(f.total_cost), 0) AS kpi_total_cost,
                COALESCE(SUM(f.total_revenue) - SUM(f.total_cost), 0) AS kpi_profit_loss,
                CASE 
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND(((SUM(f.total_revenue) - SUM(f.total_cost)) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                CASE 
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND((SUM(f.total_cost) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_cost_ratio_pct,
                
                -- NEW KPI: [KPI_NAME]
                ROUND(AVG(f.kpi_[KPI_NAME]_generated), 2) AS kpi_[KPI_NAME],
                
                -- Additional metrics
                (SELECT COUNT(*) FROM dim_branch WHERE pharmacy_id = dp.pharmacy_id) AS branch_count,
                COUNT(DISTINCT f.transaction_date) AS days_active,
                MAX(f.updated_at) AS last_updated
            
            FROM fact_cost_center f
            INNER JOIN dim_pharmacy dp ON f.warehouse_id = dp.warehouse_id
            WHERE f.warehouse_type IN ('pharmacy', 'mainwarehouse')
            GROUP BY dp.pharmacy_id, dp.warehouse_id, dp.pharmacy_name, dp.pharmacy_code, 
                     f.period_year, f.period_month
            ORDER BY f.period_year DESC, f.period_month DESC
        ");
        log_message('info', 'Updated view_cost_center_pharmacy with [KPI_NAME]');

        // ============================================================
        // STEP 5: Update view_cost_center_branch
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
                f.period_year,
                f.period_month,
                CONCAT(f.period_year, '-', LPAD(f.period_month, 2, '0')) AS period,
                
                -- Existing KPIs
                COALESCE(SUM(f.total_revenue), 0) AS kpi_total_revenue,
                COALESCE(SUM(f.total_cogs), 0) AS kpi_cogs,
                COALESCE(SUM(f.inventory_movement_cost), 0) AS kpi_inventory_movement,
                COALESCE(SUM(f.operational_cost), 0) AS kpi_operational,
                COALESCE(SUM(f.total_cost), 0) AS kpi_total_cost,
                COALESCE(SUM(f.total_revenue) - SUM(f.total_cost), 0) AS kpi_profit_loss,
                CASE 
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND(((SUM(f.total_revenue) - SUM(f.total_cost)) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                CASE 
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND((SUM(f.total_cost) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_cost_ratio_pct,
                
                -- NEW KPI: [KPI_NAME]
                ROUND(AVG(f.kpi_[KPI_NAME]_generated), 2) AS kpi_[KPI_NAME],
                
                -- Additional metrics
                COUNT(DISTINCT f.transaction_date) AS days_active,
                MAX(f.updated_at) AS last_updated
            
            FROM fact_cost_center f
            INNER JOIN dim_branch db ON f.warehouse_id = db.warehouse_id
            INNER JOIN dim_pharmacy dp ON db.pharmacy_id = dp.pharmacy_id
            WHERE f.warehouse_type = 'branch'
            GROUP BY db.branch_id, db.warehouse_id, db.branch_name, db.branch_code,
                     dp.pharmacy_id, dp.pharmacy_name, f.period_year, f.period_month
            ORDER BY f.period_year DESC, f.period_month DESC
        ");
        log_message('info', 'Updated view_cost_center_branch with [KPI_NAME]');

        // ============================================================
        // STEP 6: Update view_cost_center_summary
        // ============================================================
        
        $this->db->query("
            CREATE OR REPLACE VIEW `view_cost_center_summary` AS
            SELECT 
                'COMPANY' AS hierarchy_level,
                'COMPANY' AS hierarchy_id,
                'Company' AS hierarchy_name,
                NULL AS parent_id,
                f.period_year,
                f.period_month,
                CONCAT(f.period_year, '-', LPAD(f.period_month, 2, '0')) AS period,
                
                -- Existing KPIs
                COALESCE(SUM(f.total_revenue), 0) AS kpi_total_revenue,
                COALESCE(SUM(f.total_cost), 0) AS kpi_total_cost,
                COALESCE(SUM(f.total_revenue) - SUM(f.total_cost), 0) AS kpi_profit_loss,
                CASE 
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND(((SUM(f.total_revenue) - SUM(f.total_cost)) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                CASE 
                    WHEN SUM(f.total_revenue) = 0 THEN 0
                    ELSE ROUND((SUM(f.total_cost) / SUM(f.total_revenue)) * 100, 2)
                END AS kpi_cost_ratio_pct,
                
                -- NEW KPI: [KPI_NAME]
                ROUND(AVG(f.kpi_[KPI_NAME]_generated), 2) AS kpi_[KPI_NAME],
                
                COUNT(DISTINCT f.warehouse_id) AS entity_count,
                MAX(f.updated_at) AS last_updated
            
            FROM fact_cost_center f
            GROUP BY f.period_year, f.period_month
            ORDER BY f.period_year DESC, f.period_month DESC
        ");
        log_message('info', 'Updated view_cost_center_summary with [KPI_NAME]');

        // ============================================================
        // STEP 7: Log migration success
        // ============================================================
        
        log_message('info', '✓ Migration complete: Added KPI [KPI_NAME]');
    }

    public function down() {
        // Rollback changes
        
        // Drop GENERATED column
        $this->db->query("ALTER TABLE `fact_cost_center` DROP COLUMN `kpi_[KPI_NAME]_generated`");
        
        // Drop regular column (if added)
        if ($this->db->field_exists('[KPI_COLUMN]', 'fact_cost_center')) {
            $this->db->query("ALTER TABLE `fact_cost_center` DROP COLUMN `[KPI_COLUMN]`");
        }
        
        // Drop index
        $this->db->query("ALTER TABLE `fact_cost_center` DROP INDEX `idx_kpi_[KPI_NAME]`");
        
        // Recreate views without new KPI (previous versions)
        // ... (restore previous view SQL)
        
        log_message('info', '↺ Migration rolled back: Removed KPI [KPI_NAME]');
    }
}

/**
 * ============================================================
 * HOW TO USE THIS TEMPLATE
 * ============================================================
 * 
 * Example 1: Add "Inventory Turnover Ratio"
 * 
 * Find & Replace:
 * - [KPI_NAME] → "inventory_turnover_ratio"
 * - [KPI_COLUMN] → "inventory_turnover_ratio" (if new source column)
 * - [NUMERATOR_COLUMN] → "total_cogs"
 * - [DENOMINATOR_COLUMN] → "inventory_movement_cost"
 * 
 * SQL Generated:
 * 
 *   ALTER TABLE `fact_cost_center` ADD COLUMN `kpi_inventory_turnover_ratio_generated` DECIMAL(10,2) 
 *   GENERATED ALWAYS AS (
 *       CASE 
 *           WHEN inventory_movement_cost > 0 
 *           THEN total_cogs / inventory_movement_cost * 100
 *           ELSE 0 
 *       END
 *   ) STORED
 * 
 * 
 * Example 2: Add "Discount Rate %"
 * 
 * Find & Replace:
 * - [KPI_NAME] → "discount_rate_pct"
 * - [KPI_COLUMN] → "total_discount"
 * - [NUMERATOR_COLUMN] → "total_discount"
 * - [DENOMINATOR_COLUMN] → "total_revenue"
 * 
 * 
 * ============================================================
 * THEN IN YOUR PHP CODE:
 * ============================================================
 * 
 * 1. Helper function:
 *    
 *    if (!function_exists('format_inventory_turnover_ratio')) {
 *        function format_inventory_turnover_ratio($ratio) {
 *            return number_format($ratio, 2) . 'x per month';
 *        }
 *    }
 * 
 * 2. Add to dashboard view:
 *    
 *    <td><?php echo format_inventory_turnover_ratio($pharmacy['kpi_inventory_turnover_ratio']); ?></td>
 * 
 * 3. Model automatically includes new KPI:
 *    
 *    $pharmacies = $this->cost_center->get_pharmacies_with_kpis();
 *    // Returns: $pharmacies[0]['kpi_inventory_turnover_ratio'] = 2.35
 * 
 * 
 * ============================================================
 * RUN MIGRATION
 * ============================================================
 * 
 * Web:     http://domain/admin/migrate
 * Or CLI:  php index.php migrate
 * 
 * Check logs for success: tail -f app/logs/migrate_*.log
 */
?>
