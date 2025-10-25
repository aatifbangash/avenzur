<?php
/**
 * Cost Center Model
 * 
 * Purpose: Handle all cost center data operations
 * - Fetch pharmacy and branch KPIs
 * - Drill-down analytics
 * - Time series data
 * - Cost breakdowns
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Cost_center_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all pharmacies with KPIs for a given period
     * 
     * @param string $period YYYY-MM format
     * @param string $sort_by revenue|profit|margin|cost
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_pharmacies_with_kpis($period = null, $sort_by = 'revenue', $limit = 100, $offset = 0) {
        if (!$period) {
            $period = date('Y-m');
        }

        $sort_column = 'kpi_total_revenue';
        switch ($sort_by) {
            case 'profit':
                $sort_column = 'kpi_profit_loss DESC';
                break;
            case 'margin':
                $sort_column = 'kpi_profit_margin_pct DESC';
                break;
            case 'cost':
                $sort_column = 'kpi_total_cost DESC';
                break;
            default:
                $sort_column = 'kpi_total_revenue DESC';
        }

        $query = "
            SELECT 
                pharmacy_id,
                warehouse_id,
                pharmacy_name,
                pharmacy_code,
                period,
                kpi_total_revenue,
                kpi_total_cost,
                kpi_profit_loss,
                kpi_profit_margin_pct,
                kpi_cost_ratio_pct,
                branch_count,
                last_updated
            FROM view_cost_center_pharmacy
            WHERE period = ?
            ORDER BY $sort_column
            LIMIT ? OFFSET ?
        ";

        $result = $this->db->query($query, [$period, $limit, $offset]);
        return $result->result_array();
    }

    /**
     * Get pharmacy details with all branches
     * 
     * @param int $pharmacy_id
     * @param string $period YYYY-MM format
     * @return array
     */
    public function get_pharmacy_with_branches($pharmacy_id, $period = null) {
        if (!$period) {
            $period = date('Y-m');
        }

        // Get pharmacy header
        $pharmacy_query = "
            SELECT 
                pharmacy_id,
                warehouse_id,
                pharmacy_name,
                pharmacy_code,
                period,
                kpi_total_revenue,
                kpi_total_cost,
                kpi_profit_loss,
                kpi_profit_margin_pct,
                kpi_cost_ratio_pct,
                branch_count,
                last_updated
            FROM view_cost_center_pharmacy
            WHERE pharmacy_id = ? AND period = ?
        ";

        $pharmacy_result = $this->db->query($pharmacy_query, [$pharmacy_id, $period]);
        $pharmacy = $pharmacy_result->row_array();

        // Get branches for this pharmacy
        $branches_query = "
            SELECT 
                branch_id,
                warehouse_id,
                branch_name,
                branch_code,
                period,
                kpi_total_revenue,
                kpi_total_cost,
                kpi_profit_loss,
                kpi_profit_margin_pct,
                kpi_cost_ratio_pct,
                last_updated
            FROM view_cost_center_branch
            WHERE pharmacy_id = ? AND period = ?
            ORDER BY kpi_total_revenue DESC
        ";

        $branches_result = $this->db->query($branches_query, [$pharmacy_id, $period]);
        $branches = $branches_result->result_array();

        return [
            'pharmacy' => $pharmacy,
            'branches' => $branches
        ];
    }

    /**
     * Get branch detail with cost breakdown
     * 
     * @param int $branch_id
     * @param string $period YYYY-MM format
     * @return array
     */
    public function get_branch_detail($branch_id, $period = null) {
        if (!$period) {
            $period = date('Y-m');
        }

        $query = "
            SELECT 
                branch_id,
                warehouse_id,
                branch_name,
                branch_code,
                pharmacy_id,
                pharmacy_name,
                period,
                kpi_total_revenue,
                kpi_cogs,
                kpi_inventory_movement,
                kpi_operational,
                kpi_total_cost,
                kpi_profit_loss,
                kpi_profit_margin_pct,
                kpi_cost_ratio_pct,
                last_updated
            FROM view_cost_center_branch
            WHERE branch_id = ? AND period = ?
        ";

        $result = $this->db->query($query, [$branch_id, $period]);
        return $result->row_array();
    }

    /**
     * Get time series data for trend analysis
     * 
     * @param int $branch_id or warehouse_id
     * @param int $months Number of months to retrieve
     * @param string $level 'pharmacy' or 'branch'
     * @return array
     */
    public function get_timeseries_data($entity_id, $months = 12, $level = 'branch') {
        if ($level === 'branch') {
            $query = "
                SELECT 
                    branch_id,
                    branch_name,
                    period,
                    kpi_total_revenue AS revenue,
                    kpi_total_cost AS cost,
                    kpi_profit_loss AS profit,
                    kpi_profit_margin_pct AS margin_pct,
                    last_updated
                FROM view_cost_center_branch
                WHERE branch_id = ?
                ORDER BY period DESC
                LIMIT ?
            ";
        } else {
            $query = "
                SELECT 
                    pharmacy_id,
                    pharmacy_name,
                    period,
                    kpi_total_revenue AS revenue,
                    kpi_total_cost AS cost,
                    kpi_profit_loss AS profit,
                    kpi_profit_margin_pct AS margin_pct,
                    last_updated
                FROM view_cost_center_pharmacy
                WHERE pharmacy_id = ?
                ORDER BY period DESC
                LIMIT ?
            ";
        }

        $result = $this->db->query($query, [$entity_id, $months]);
        return array_reverse($result->result_array()); // Reverse to get ascending order
    }

    /**
     * Get summary statistics for dashboard
     * 
     * @param string $period YYYY-MM format
     * @return array
     */
    public function get_summary_stats($period = null) {
        if (!$period) {
            $period = date('Y-m');
        }

        $query = "
            SELECT 
                level,
                entity_name,
                period,
                kpi_total_revenue,
                kpi_total_cost,
                kpi_profit_loss,
                kpi_profit_margin_pct,
                entity_count,
                last_updated
            FROM view_cost_center_summary
            WHERE period = ?
        ";

        $result = $this->db->query($query, [$period]);
        return $result->row_array();
    }

    /**
     * Get monthly periods available in fact table
     * 
     * @param int $limit
     * @return array
     */
    public function get_available_periods($limit = 24) {
        $query = "
            SELECT DISTINCT 
                CONCAT(period_year, '-', LPAD(period_month, 2, '0')) AS period,
                period_year,
                period_month
            FROM sma_fact_cost_center
            ORDER BY period_year DESC, period_month DESC
            LIMIT ?
        ";

        $result = $this->db->query($query, [$limit]);
        return $result->result_array();
    }

    /**
     * Get pharmacy count
     * 
     * @return int
     */
    public function get_pharmacy_count() {
        return $this->db->count_all('sma_dim_pharmacy');
    }

    /**
     * Get branch count
     * 
     * @return int
     */
    public function get_branch_count() {
        return $this->db->count_all('sma_dim_branch');
    }

    /**
     * Validate pharmacy exists and is accessible
     * 
     * @param int $pharmacy_id
     * @return boolean
     */
    public function pharmacy_exists($pharmacy_id) {
        $query = "SELECT 1 FROM sma_dim_pharmacy WHERE pharmacy_id = ? AND is_active = 1";
        $result = $this->db->query($query, [$pharmacy_id]);
        return $result->num_rows() > 0;
    }

    /**
     * Validate branch exists and is accessible
     * 
     * @param int $branch_id
     * @return boolean
     */
    public function branch_exists($branch_id) {
        $query = "SELECT 1 FROM sma_dim_branch WHERE branch_id = ? AND is_active = 1";
        $result = $this->db->query($query, [$branch_id]);
        return $result->num_rows() > 0;
    }

    /**
     * Get breakdown by category (COGS vs Movement vs Operational)
     * 
     * @param int $branch_id
     * @param string $period YYYY-MM format
     * @return array
     */
    public function get_cost_breakdown($branch_id, $period = null) {
        if (!$period) {
            $period = date('Y-m');
        }

        $query = "
            SELECT 
                'COGS' AS category,
                SUM(kpi_cogs) AS amount
            FROM view_cost_center_branch
            WHERE branch_id = ? AND period = ?
            
            UNION ALL
            
            SELECT 
                'Inventory Movement' AS category,
                SUM(kpi_inventory_movement) AS amount
            FROM view_cost_center_branch
            WHERE branch_id = ? AND period = ?
            
            UNION ALL
            
            SELECT 
                'Operational' AS category,
                SUM(kpi_operational) AS amount
            FROM view_cost_center_branch
            WHERE branch_id = ? AND period = ?
        ";

        $result = $this->db->query($query, [
            $branch_id, $period,
            $branch_id, $period,
            $branch_id, $period
        ]);
        return $result->result_array();
    }

    /**
     * Get ETL status and last run
     * 
     * @return array
     */
    public function get_etl_status() {
        $query = "
            SELECT 
                process_name,
                start_time,
                end_time,
                status,
                rows_processed,
                error_message,
                duration_seconds
            FROM sma_etl_audit_log
            WHERE process_name = 'sp_populate_fact_cost_center'
            ORDER BY start_time DESC
            LIMIT 5
        ";

        $result = $this->db->query($query);
        return $result->result_array();
    }
}
