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


public function get_hierarchical_analytics($period_type = 'today', $target_month = null, $warehouse_id = null, $level = 'company') {
        // Validate level
        $valid_levels = ['company', 'pharmacy', 'branch'];
        if (!in_array($level, $valid_levels)) {
            return ['error' => 'Invalid level. Must be: company, pharmacy, or branch'];
        }
        
        // Validate period_type
        $valid_periods = ['today', 'monthly', 'ytd'];
        if (!in_array($period_type, $valid_periods)) {
            return ['error' => 'Invalid period type. Must be: today, monthly, or ytd'];
        }
        
        // Set default target_month if monthly and not provided
        if ($period_type === 'monthly' && empty($target_month)) {
            $target_month = date('Y-m');
        }
        
        // Prepare parameters
        $params = [
            $period_type,
            $target_month,
            $warehouse_id,
            $level
        ];
        
        try {
            // Call stored procedure
            $query = "CALL sp_get_sales_analytics_hierarchical(?, ?, ?, ?)";
            
            // Execute procedure and get results
            $result = $this->db->query($query, $params);
            
            // Get summary (first result set)
            $summary = $result->row();
            
            // Get mysqli connection for multi-result handling
            $mysqli = $this->db->conn_id;
            $best_products = [];
            
            // Fetch next result sets
            if ($mysqli->more_results()) {
                $mysqli->next_result();
                $result_products = $mysqli->store_result();
                
                if ($result_products) {
                    while ($row = $result_products->fetch_object()) {
                        $best_products[] = $row;
                    }
                    $result_products->free();
                }
                
                // Continue consuming remaining result sets to prevent "Commands out of sync" error
                while ($mysqli->more_results()) {
                    $mysqli->next_result();
                    $temp_result = $mysqli->store_result();
                    if ($temp_result) {
                        $temp_result->free();
                    }
                }
            }
            
            return [
                'success' => true,
                'summary' => $summary,
                'best_products' => $best_products
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Sales Analytics Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get company-wide analytics (all warehouses)
     * 
     * @param string $period_type 'today', 'monthly', 'ytd'
     * @param string|null $target_month 'YYYY-MM' format
     * @return array
     */
    public function get_company_analytics($period_type = 'today', $target_month = null) {
        return $this->get_hierarchical_analytics($period_type, $target_month, null, 'company');
    }
    
    /**
     * Get pharmacy-level analytics (aggregate of all branches)
     * 
     * @param int $pharmacy_id Pharmacy warehouse ID
     * @param string $period_type 'today', 'monthly', 'ytd'
     * @param string|null $target_month 'YYYY-MM' format
     * @return array
     */
    public function get_pharmacy_analytics($pharmacy_id, $period_type = 'today', $target_month = null) {
        return $this->get_hierarchical_analytics($period_type, $target_month, $pharmacy_id, 'pharmacy');
    }
    
    /**
     * Get branch-level analytics (single warehouse)
     * 
     * @param int $branch_id Branch warehouse ID
     * @param string $period_type 'today', 'monthly', 'ytd'
     * @param string|null $target_month 'YYYY-MM' format
     * @return array
     */
    public function get_branch_analytics($branch_id, $period_type = 'today', $target_month = null) {
        return $this->get_hierarchical_analytics($period_type, $target_month, $branch_id, 'branch');
    }
    
    /**
     * Get list of all pharmacies (parent warehouses)
     * 
     * @return array
     */
    public function get_pharmacies() {
        $this->db->select('id, code, name, warehouse_type');
        $this->db->from('sma_warehouses');
        $this->db->where('parent_id IS NULL');
        $this->db->or_where('parent_id', 0);
        $query = $this->db->get();
        
        return $query->result();
    }
    
    /**
     * Get list of branches under a pharmacy
     * 
     * @param int $pharmacy_id Pharmacy warehouse ID
     * @return array
     */
    public function get_branches($pharmacy_id) {
        $this->db->select('id, code, name, warehouse_type, parent_id');
        $this->db->from('sma_warehouses');
        $this->db->where('parent_id', $pharmacy_id);
        $query = $this->db->get();
        
        return $query->result();
    }

    /**
     * Get all warehouse groups (top-level warehouses)
     * 
     * @return array
     */
    public function get_warehouse_groups() {
        $this->db->select('id, code, name, warehouse_type');
        $this->db->from('sma_warehouses');
        $this->db->where('warehouse_type', 'warehouse');
        $this->db->where('(parent_id IS NULL OR parent_id = 0)', NULL, FALSE);
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        
        return $query->result();
    }

    /**
     * Get all pharmacies under a warehouse group
     * 
     * @param int $warehouse_id Warehouse group ID
     * @return array
     */
    public function get_pharmacies_by_warehouse($warehouse_id) {
        $this->db->select('id, code, name, warehouse_type, parent_id');
        $this->db->from('sma_warehouses');
        $this->db->where('warehouse_type', 'pharmacy');
        $this->db->where('parent_id', $warehouse_id);
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        
        return $query->result();
    }

    /**
     * Get all pharmacies (warehouse_type = 'pharmacy')
     * No filtering by parent - returns ALL pharmacies
     * 
     * @return array
     */
    public function get_all_pharmacies() {
        $this->db->select('id, code, name, warehouse_type, parent_id');
        $this->db->from('sma_warehouses');
        $this->db->where('warehouse_type', 'pharmacy');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        
        return $query->result();
    }

    /**
     * Get all branches under a pharmacy
     * Uses parent_id to find branches where parent_id = pharmacy_id
     * 
     * @param int $pharmacy_id Pharmacy ID
     * @return array
     */
    public function get_branches_by_pharmacy($pharmacy_id) {
        $this->db->select('id, code, name, warehouse_type, parent_id');
        $this->db->from('sma_warehouses');
        $this->db->where('warehouse_type', 'branch');
        $this->db->where('parent_id', $pharmacy_id);
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        
        return $query->result();
    }

    /**
     * Get combined warehouse and pharmacy hierarchy for dropdown
     * Returns structure: warehouses with their child pharmacies
     * 
     * Format:
     * [
     *   { id: 1, name: 'Warehouse A', warehouse_type: 'warehouse', parent_id: null, children: [...pharmacies] },
     *   { id: 10, name: 'Pharmacy A', warehouse_type: 'pharmacy', parent_id: null, children: [] },
     *   ...
     * ]
     * 
     * @return array Hierarchical structure for dropdown rendering
     */
    public function get_warehouse_pharmacy_hierarchy() {
        // Get all warehouses and pharmacies
        $this->db->select('id, code, name, warehouse_type, parent_id');
        $this->db->from('sma_warehouses');
        $this->db->where_in('warehouse_type', ['warehouse', 'pharmacy']);
        $this->db->order_by('warehouse_type', 'ASC');
        $this->db->order_by('parent_id', 'ASC');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        $all_items = $query->result_array();
        
        // Build hierarchy structure
        $hierarchy = [];
        $pharmacies_by_parent = [];
        
        // Group pharmacies by parent_id
        foreach ($all_items as $item) {
            if ($item['warehouse_type'] === 'pharmacy') {
                $parent = $item['parent_id'] ?? 0;
                if (!isset($pharmacies_by_parent[$parent])) {
                    $pharmacies_by_parent[$parent] = [];
                }
                $pharmacies_by_parent[$parent][] = $item;
            }
        }
        
        // Build final hierarchy
        foreach ($all_items as $item) {
            if ($item['warehouse_type'] === 'warehouse') {
                // Add warehouse with its children
                $item['children'] = $pharmacies_by_parent[$item['id']] ?? [];
                $hierarchy[] = $item;
            } elseif ($item['warehouse_type'] === 'pharmacy' && ($item['parent_id'] === null || $item['parent_id'] === 0)) {
                // Add standalone pharmacies (no parent)
                $item['children'] = [];
                $hierarchy[] = $item;
            }
        }
        
        return $hierarchy;
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
    public function get_pharmacy_with_branches($warehouse_id, $period = null) {
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
            WHERE warehouse_id = ? AND period = ?
        ";

        $pharmacy_result = $this->db->query($pharmacy_query, [$warehouse_id, $period]);
        $pharmacy = $pharmacy_result->row_array();
        
        // Get branches for this pharmacy using warehouse_id (natural key)
        // FIXED: Using warehouse_id (parent's warehouse_id) instead of pharmacy_id (surrogate)
        // This query works with both pre and post-migration database states
        $period_parts = explode('-', $period);
        $period_year = $period_parts[0];
        $period_month = $period_parts[1];
        
        $branches_query = "
            SELECT 
                db.warehouse_id,
                db.branch_name,
                db.branch_code,
                COALESCE(SUM(fcf.total_revenue), 0) AS kpi_total_revenue,
                COALESCE(SUM(fcf.total_cogs), 0) AS kpi_total_cost,
                COALESCE(SUM(fcf.total_revenue - fcf.total_cogs), 0) AS kpi_profit_loss,
                CASE 
                    WHEN COALESCE(SUM(fcf.total_revenue), 0) = 0 THEN 0
                    ELSE ROUND(
                        ((COALESCE(SUM(fcf.total_revenue), 0) - COALESCE(SUM(fcf.total_cogs), 0)) 
                         / COALESCE(SUM(fcf.total_revenue), 0)) * 100, 2
                    )
                END AS kpi_profit_margin_pct,
                CASE 
                    WHEN COALESCE(SUM(fcf.total_revenue), 0) = 0 THEN 0
                    ELSE ROUND(
                        (COALESCE(SUM(fcf.total_cogs), 0) / COALESCE(SUM(fcf.total_revenue), 0)) * 100, 2
                    )
                END AS kpi_cost_ratio_pct,
                NOW() AS last_updated
            FROM sma_dim_branch db
            INNER JOIN sma_warehouses w ON db.warehouse_id = w.id
            LEFT JOIN sma_fact_cost_center fcf 
                ON db.warehouse_id = fcf.warehouse_id 
                AND fcf.period_year = ? 
                AND fcf.period_month = ?
            WHERE w.parent_id = ?
            AND db.is_active = 1
            GROUP BY db.warehouse_id, db.branch_name, db.branch_code
            ORDER BY kpi_total_revenue DESC
        ";

        $branches_result = $this->db->query($branches_query, [$period_year, $period_month, $warehouse_id]);
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
                    warehouse_id,
                    branch_name,
                    period,
                    kpi_total_revenue AS revenue,
                    kpi_total_cost AS cost,
                    kpi_profit_loss AS profit,
                    kpi_profit_margin_pct AS margin_pct,
                    last_updated
                FROM view_cost_center_branch
                WHERE warehouse_id = ?
                ORDER BY period DESC
                LIMIT ?
            ";
        } else {
            // FIXED: Using warehouse_id (natural key) instead of pharmacy_id (surrogate)
            $query = "
                SELECT 
                    warehouse_id,
                    pharmacy_name,
                    period,
                    kpi_total_revenue AS revenue,
                    kpi_total_cost AS cost,
                    kpi_profit_loss AS profit,
                    kpi_profit_margin_pct AS margin_pct,
                    last_updated
                FROM view_cost_center_pharmacy
                WHERE warehouse_id = ?
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
     * Get available periods with special date options
     * 
     * Special periods: 'today', 'ytd' (Year to Date)
     * Regular periods: 'YYYY-MM' format from database
     * 
     * @param int $limit Number of historical months to retrieve
     * @return array Array with period options
     */
    public function get_available_periods($limit = 24) {
        // Start with special date options
        $periods = [
            [
                'period' => 'today',
                'period_year' => date('Y'),
                'period_month' => date('m'),
                'label' => 'Today',
                'is_special' => true
            ],
            [
                'period' => 'ytd',
                'period_year' => date('Y'),
                'period_month' => null,
                'label' => 'Year to Date',
                'is_special' => true
            ]
        ];
        
        // Get historical monthly data
        $query = "
            SELECT DISTINCT 
                CONCAT(period_year, '-', LPAD(period_month, 2, '0')) AS period,
                period_year,
                period_month,
                NULL as label,
                FALSE as is_special
            FROM sma_fact_cost_center
            ORDER BY period_year DESC, period_month DESC
            LIMIT ?
        ";

        $result = $this->db->query($query, [$limit]);
        $monthly_periods = $result->result_array();
        
        // Merge special periods with historical data
        return array_merge($periods, $monthly_periods);
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
        $query = "SELECT 1 FROM sma_dim_pharmacy WHERE warehouse_id = ?";
        $result = $this->db->query($query, [$pharmacy_id]);
        
        return $result->num_rows() > 0;
    }

    /**
     * Get basic pharmacy info from dimension table
     * Used when no transaction data exists for the period
     * 
     * FIXED: Using warehouse_id (natural key) instead of pharmacy_id (surrogate)
     * @param int $warehouse_id
     * @return array|null
     */
    public function get_pharmacy_info($warehouse_id) {
        $query = "
            SELECT 
                warehouse_id,
                pharmacy_name,
                pharmacy_code
            FROM sma_dim_pharmacy
            WHERE warehouse_id = ? AND is_active = 1
            LIMIT 1
        ";
        
        $result = $this->db->query($query, [$warehouse_id]);
        return $result->num_rows() > 0 ? $result->row_array() : null;
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

    /**
     * Calculate Gross and Net Profit Margins
     * Gross: (Revenue - COGS) / Revenue * 100
     * Net: (Revenue - COGS - Inventory - Operational) / Revenue * 100
     * 
     * @param int $pharmacy_id or null for company-level
     * @param string $period YYYY-MM format
     * @return array [gross_margin, net_margin]
     */
    public function get_profit_margins_both_types($pharmacy_id = null, $period = null) {
        if (!$period) {
            $period = date('Y-m');
        }

        if ($pharmacy_id) {
            // Pharmacy-level margins
            $query = "
                SELECT 
                    SUM(total_revenue) AS total_revenue,
                    SUM(total_cogs) AS total_cogs,
                    SUM(inventory_movement_cost) AS inventory_movement,
                    SUM(operational_cost) AS operational_cost
                FROM sma_fact_cost_center
                WHERE pharmacy_id = ? 
                AND CONCAT(period_year, '-', LPAD(period_month, 2, '0')) = ?
            ";
            $result = $this->db->query($query, [$pharmacy_id, $period]);
        } else {
            // Company-level margins (all pharmacies)
            $query = "
                SELECT 
                    SUM(total_revenue) AS total_revenue,
                    SUM(total_cogs) AS total_cogs,
                    SUM(inventory_movement_cost) AS inventory_movement,
                    SUM(operational_cost) AS operational_cost
                FROM sma_fact_cost_center
                WHERE CONCAT(period_year, '-', LPAD(period_month, 2, '0')) = ?
            ";
            $result = $this->db->query($query, [$period]);
        }

        $row = $result->row_array();

        if (!$row || $row['total_revenue'] == 0) {
            return ['gross_margin' => 0, 'net_margin' => 0];
        }

        // Calculate margins
        $gross_margin = (($row['total_revenue'] - $row['total_cogs']) / $row['total_revenue']) * 100;
        $net_margin = (($row['total_revenue'] - $row['total_cogs'] - $row['inventory_movement'] - $row['operational_cost']) / $row['total_revenue']) * 100;

        return [
            'gross_margin' => round($gross_margin, 2),
            'net_margin' => round($net_margin, 2),
            'revenue' => $row['total_revenue'],
            'cogs' => $row['total_cogs'],
            'inventory_movement' => $row['inventory_movement'],
            'operational_cost' => $row['operational_cost']
        ];
    }

    /**
     * Get pharmacy weekly and monthly trends
     * 
     * @param int $pharmacy_id
     * @param int $months Number of months to retrieve
     * @return array ['weekly' => [...], 'monthly' => [...]]
     */
    public function get_pharmacy_trends($pharmacy_id, $months = 12) {
        // Monthly trends
        $monthly_query = "
            SELECT 
                CONCAT(period_year, '-', LPAD(period_month, 2, '0')) AS period,
                DATE(CONCAT(period_year, '-', LPAD(period_month, 2, '0'), '-01')) AS period_date,
                SUM(total_revenue) AS revenue,
                SUM(total_cogs) AS cogs,
                SUM(inventory_movement_cost) AS inventory,
                SUM(operational_cost) AS operational,
                ROUND(((SUM(total_revenue) - SUM(total_cogs)) / SUM(total_revenue)) * 100, 2) AS gross_margin,
                ROUND(((SUM(total_revenue) - SUM(total_cogs) - SUM(inventory_movement_cost) - SUM(operational_cost)) / SUM(total_revenue)) * 100, 2) AS net_margin
            FROM sma_fact_cost_center
            WHERE pharmacy_id = ?
            GROUP BY period_year, period_month
            ORDER BY period_year DESC, period_month DESC
            LIMIT ?
        ";

        // Weekly trends (last 12 weeks)
        $weekly_query = "
            SELECT 
                DATE_FORMAT(transaction_date, '%Y-W%u') AS week,
                DATE(DATE_SUB(transaction_date, INTERVAL DAYOFWEEK(transaction_date)-1 DAY)) AS week_start,
                SUM(total_revenue) AS revenue,
                SUM(total_cogs) AS cogs,
                SUM(inventory_movement_cost) AS inventory,
                SUM(operational_cost) AS operational,
                ROUND(((SUM(total_revenue) - SUM(total_cogs)) / SUM(total_revenue)) * 100, 2) AS gross_margin,
                ROUND(((SUM(total_revenue) - SUM(total_cogs) - SUM(inventory_movement_cost) - SUM(operational_cost)) / SUM(total_revenue)) * 100, 2) AS net_margin
            FROM sma_fact_cost_center
            WHERE pharmacy_id = ?
            AND transaction_date >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
            GROUP BY YEARWEEK(transaction_date)
            ORDER BY week_start DESC
        ";

        $monthly_result = $this->db->query($monthly_query, [$pharmacy_id, $months]);
        $weekly_result = $this->db->query($weekly_query, [$pharmacy_id]);

        return [
            'monthly' => array_reverse($monthly_result->result_array()),
            'weekly' => array_reverse($weekly_result->result_array())
        ];
    }

    /**
     * Get branch weekly and monthly trends
     * 
     * @param int $branch_id
     * @param int $months Number of months to retrieve
     * @return array ['weekly' => [...], 'monthly' => [...]]
     */
    public function get_branch_trends($branch_id, $months = 12) {
        // Monthly trends
        $monthly_query = "
            SELECT 
                CONCAT(period_year, '-', LPAD(period_month, 2, '0')) AS period,
                DATE(CONCAT(period_year, '-', LPAD(period_month, 2, '0'), '-01')) AS period_date,
                SUM(total_revenue) AS revenue,
                SUM(total_cogs) AS cogs,
                SUM(inventory_movement_cost) AS inventory,
                SUM(operational_cost) AS operational,
                ROUND(((SUM(total_revenue) - SUM(total_cogs)) / SUM(total_revenue)) * 100, 2) AS gross_margin,
                ROUND(((SUM(total_revenue) - SUM(total_cogs) - SUM(inventory_movement_cost) - SUM(operational_cost)) / SUM(total_revenue)) * 100, 2) AS net_margin
            FROM sma_fact_cost_center
            WHERE branch_id = ?
            GROUP BY period_year, period_month
            ORDER BY period_year DESC, period_month DESC
            LIMIT ?
        ";

        // Weekly trends (last 12 weeks)
        $weekly_query = "
            SELECT 
                DATE_FORMAT(transaction_date, '%Y-W%u') AS week,
                DATE(DATE_SUB(transaction_date, INTERVAL DAYOFWEEK(transaction_date)-1 DAY)) AS week_start,
                SUM(total_revenue) AS revenue,
                SUM(total_cogs) AS cogs,
                SUM(inventory_movement_cost) AS inventory,
                SUM(operational_cost) AS operational,
                ROUND(((SUM(total_revenue) - SUM(total_cogs)) / SUM(total_revenue)) * 100, 2) AS gross_margin,
                ROUND(((SUM(total_revenue) - SUM(total_cogs) - SUM(inventory_movement_cost) - SUM(operational_cost)) / SUM(total_revenue)) * 100, 2) AS net_margin
            FROM sma_fact_cost_center
            WHERE branch_id = ?
            AND transaction_date >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
            GROUP BY YEARWEEK(transaction_date)
            ORDER BY week_start DESC
        ";

        $monthly_result = $this->db->query($monthly_query, [$branch_id, $months]);
        $weekly_result = $this->db->query($weekly_query, [$branch_id]);

        return [
            'monthly' => array_reverse($monthly_result->result_array()),
            'weekly' => array_reverse($weekly_result->result_array())
        ];
    }

    /**
     * Calculate Health Score for Pharmacy/Branch
     * Green: Margin >= 30, Yellow: 20-29.99, Red: < 20
     * 
     * @param float $margin_percentage
     * @param float $revenue
     * @return array [status, color, description]
     */
    public function calculate_health_score($margin_percentage, $revenue = 0) {
        $status = 'red';
        $color = '#EF4444';
        $description = 'Critical - Below 20% margin';

        if ($margin_percentage >= 30) {
            $status = 'green';
            $color = '#10B981';
            $description = 'Healthy - Above 30% margin';
        } elseif ($margin_percentage >= 20) {
            $status = 'yellow';
            $color = '#F59E0B';
            $description = 'Caution - 20-30% margin';
        }

        return [
            'status' => $status,
            'color' => $color,
            'description' => $description,
            'margin' => $margin_percentage,
            'badge_class' => 'badge-' . $status
        ];
    }

    /**
     * Get detailed cost breakdown for pharmacy
     * Separated: COGS and Expired Items (Inventory Movement)
     * 
     * @param int $pharmacy_id
     * @param string $period YYYY-MM format
     * @return array
     */
    public function get_cost_breakdown_detailed($pharmacy_id, $period = null) {
        if (!$period) {
            $period = date('Y-m');
        }

        $query = "
            SELECT 
                SUM(total_cogs) AS cogs,
                SUM(inventory_movement_cost) AS expired_items,
                SUM(operational_cost) AS operational,
                SUM(total_revenue) AS revenue,
                ROUND(((SUM(total_cogs) + SUM(inventory_movement_cost) + SUM(operational_cost)) / SUM(total_revenue)) * 100, 2) AS total_cost_pct
            FROM sma_fact_cost_center
            WHERE pharmacy_id = ?
            AND CONCAT(period_year, '-', LPAD(period_month, 2, '0')) = ?
        ";

        $result = $this->db->query($query, [$pharmacy_id, $period]);
        return $result->row_array();
    }

    /**
     * Get all pharmacies with health scores and margins
     * Uses sma_warehouses table directly (warehouse_type='pharmacy')
     * 
     * @param string $period YYYY-MM format
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_pharmacies_with_health_scores($period = null, $limit = 100, $offset = 0) {
        if (!$period) {
            $period = date('Y-m');
        }

        // Query that joins warehouses (pharmacies + central warehouses) with cost center facts
        // Now includes central warehouses (type='warehouse' with parent_id IS NULL)
        $query = "
            SELECT 
                w.id AS pharmacy_id,
                w.code AS pharmacy_code,
                w.name AS pharmacy_name,
                w.warehouse_type,
                CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,
                COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,
                COALESCE(SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost), 0) AS kpi_total_cost,
                COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)), 0) AS kpi_profit_loss,
                CASE 
                    WHEN COALESCE(SUM(fcc.total_revenue), 0) = 0 THEN 0
                    ELSE ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                CASE 
                    WHEN COALESCE(SUM(fcc.total_revenue), 0) = 0 THEN 0
                    ELSE ROUND((SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost) / SUM(fcc.total_revenue)) * 100, 2)
                END AS kpi_cost_ratio_pct,
                COALESCE(COUNT(DISTINCT db.id), 0) AS branch_count,
                MAX(fcc.updated_at) AS last_updated,
                CASE 
                    WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 30 THEN '✓ Healthy'
                    WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 20 THEN '⚠ Monitor'
                    ELSE '✗ Low'
                END AS health_status,
                CASE 
                    WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 30 THEN '#10B981'
                    WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 20 THEN '#F59E0B'
                    ELSE '#EF4444'
                END AS health_color
            FROM sma_warehouses w
            LEFT JOIN sma_fact_cost_center fcc ON w.id = fcc.warehouse_id AND CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) = ?
            LEFT JOIN sma_warehouses db ON db.warehouse_type = 'branch' AND db.parent_id = w.id
            WHERE (w.warehouse_type = 'pharmacy') OR (w.warehouse_type = 'warehouse' AND w.parent_id IS NULL)
            GROUP BY w.id, w.code, w.name, fcc.period_year, fcc.period_month
            ORDER BY kpi_total_revenue DESC
            LIMIT ? OFFSET ?
        ";

        $result = $this->db->query($query, [$period, $limit, $offset]);
        return $result->result_array();
    }

    /**
     * Get all branches with health scores and margins
     * Uses sma_warehouses table directly (warehouse_type='branch')
     * 
     * @param string $period YYYY-MM format
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_branches_with_health_scores($period = null, $limit = 100, $offset = 0) {
        if (!$period) {
            $period = date('Y-m');
        }

        // Query that joins branches with pharmacies and cost center facts
        $query = "
            SELECT 
                b.id AS branch_id,
                b.code AS branch_code,
                b.name AS branch_name,
                b.warehouse_type,
                p.id AS pharmacy_id,
                p.code AS pharmacy_code,
                p.name AS pharmacy_name,
                CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,
                COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,
                COALESCE(SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost), 0) AS kpi_total_cost,
                COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)), 0) AS kpi_profit_loss,
                CASE 
                    WHEN COALESCE(SUM(fcc.total_revenue), 0) = 0 THEN 0
                    ELSE ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                CASE 
                    WHEN COALESCE(SUM(fcc.total_revenue), 0) = 0 THEN 0
                    ELSE ROUND((SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost) / SUM(fcc.total_revenue)) * 100, 2)
                END AS kpi_cost_ratio_pct,
                MAX(fcc.updated_at) AS last_updated,
                CASE 
                    WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 30 THEN '✓ Healthy'
                    WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 20 THEN '⚠ Monitor'
                    ELSE '✗ Low'
                END AS health_status,
                CASE 
                    WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 30 THEN '#10B981'
                    WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 20 THEN '#F59E0B'
                    ELSE '#EF4444'
                END AS health_color
            FROM sma_warehouses b
            LEFT JOIN sma_warehouses p ON b.parent_id = p.id AND p.warehouse_type = 'pharmacy'
            LEFT JOIN sma_fact_cost_center fcc ON b.id = fcc.warehouse_id AND CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) = ?
            WHERE b.warehouse_type = 'branch'
            GROUP BY b.id, b.code, b.name, p.id, p.code, p.name, fcc.period_year, fcc.period_month
            ORDER BY kpi_total_revenue DESC
            LIMIT ? OFFSET ?
        ";

        $result = $this->db->query($query, [$period, $limit, $offset]);
        return $result->result_array();
    }

    /**
     * Get specific pharmacy data with KPIs
     * 
     * @param int $pharmacy_id Pharmacy warehouse ID
     * @param string $period YYYY-MM format
     * @return array
     */
    public function get_pharmacy_detail($pharmacy_id = null, $period = null) {
        if (!$period) {
            $period = date('Y-m');
        }

        $query = "
            SELECT 
                w.id AS pharmacy_id,
                w.code AS pharmacy_code,
                w.name AS pharmacy_name,
                w.warehouse_type,
                CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,
                COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,
                COALESCE(SUM(fcc.total_cogs), 0) AS kpi_cogs,
                COALESCE(SUM(fcc.inventory_movement_cost), 0) AS kpi_inventory_movement,
                COALESCE(SUM(fcc.operational_cost), 0) AS kpi_operational_cost,
                COALESCE(SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost), 0) AS kpi_total_cost,
                COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)), 0) AS kpi_profit_loss,
                CASE 
                    WHEN COALESCE(SUM(fcc.total_revenue), 0) = 0 THEN 0
                    ELSE ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue)) * 100, 2)
                END AS kpi_profit_margin_pct,
                CASE 
                    WHEN COALESCE(SUM(fcc.total_revenue), 0) = 0 THEN 0
                    ELSE ROUND((SUM(fcc.total_cogs) / SUM(fcc.total_revenue)) * 100, 2)
                END AS gross_margin_pct,
                CASE 
                    WHEN COALESCE(SUM(fcc.total_revenue), 0) = 0 THEN 0
                    ELSE ROUND(((SUM(fcc.total_revenue) - SUM(fcc.total_cogs)) / SUM(fcc.total_revenue)) * 100, 2)
                END AS net_margin_pct,
                COALESCE(COUNT(DISTINCT db.id), 0) AS branch_count,
                MAX(fcc.updated_at) AS last_updated
            FROM sma_warehouses w
            LEFT JOIN sma_fact_cost_center fcc ON w.id = fcc.warehouse_id AND CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) = ?
            LEFT JOIN sma_warehouses db ON db.warehouse_type = 'branch' AND db.parent_id = w.id
            WHERE w.warehouse_type = 'pharmacy' AND w.id = ?
            GROUP BY w.id, w.code, w.name, fcc.period_year, fcc.period_month
        ";

        $result = $this->db->query($query, [$period, $pharmacy_id]);
        return $result->row_array();
    }

    /**
     * Get Company-Level Summary Metrics Using Stored Procedure
     * 
     * Wrapper for sp_get_sales_analytics_hierarchical at company level.
     * Returns summary metrics for the entire company.
     * 
     * @param string $period_type 'today', 'monthly', 'ytd' (default: 'monthly')
     * @param string|null $target_month YYYY-MM format (default: current month)
     * @return array Summary metrics containing: total_sales, total_margin, total_customers, total_items_sold, etc.
     */
    public function get_company_summary_metrics($period_type = 'monthly', $target_month = null) {
        $result = $this->get_hierarchical_analytics($period_type, $target_month, null, 'company');
        
        if ($result['success'] && isset($result['summary'])) {
            return $result['summary'];
        }
        
        return null;
    }

    /**
     * Get Best Moving Products Using Stored Procedure
     * 
     * Wrapper for sp_get_sales_analytics_hierarchical to get best products.
     * Returns top products based on sales volume.
     * 
     * @param string $level 'company', 'pharmacy', or 'branch'
     * @param int|null $warehouse_id Warehouse ID (required for pharmacy/branch levels)
     * @param string $period_type 'today', 'monthly', 'ytd'
     * @param string|null $target_month YYYY-MM format
     * @return array Array of best products with sales metrics
     */
    public function get_best_moving_products($level = 'company', $warehouse_id = null, $period_type = 'monthly', $target_month = null) {
        $result = $this->get_hierarchical_analytics($period_type, $target_month, $warehouse_id, $level);
        
        if ($result['success'] && isset($result['best_products'])) {
            return $result['best_products'];
        }
        
        return [];
    }
}
