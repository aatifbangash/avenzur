<?php
/**
 * Cost Center API Controller
 * 
 * Endpoints:
 * GET  /api/v1/cost-center/pharmacies
 * GET  /api/v1/cost-center/pharmacies/{id}/branches
 * GET  /api/v1/cost-center/branches/{id}/detail
 * GET  /api/v1/cost-center/branches/{id}/timeseries
 * GET  /api/v1/cost-center/summary
 * 
 * Date: 2025-10-25
 */

require_once APPPATH . 'controllers/api/v1/Base_api.php';

class Cost_center extends Base_api {

    private $pagination_limit = 100;

    public function __construct() {
        parent::__construct();
        $this->load->model('admin/Cost_center_model', 'cost_center');
    }

    /**
     * GET /api/v1/cost-center/pharmacies
     * 
     * Get all pharmacies with KPIs
     * 
     * Query Parameters:
     * - period: YYYY-MM (default: current month)
     * - sort_by: revenue|profit|margin|cost (default: revenue)
     * - limit: number of records (default: 100)
     * - offset: pagination offset (default: 0)
     */
    public function pharmacies_get() {
        try {
            $period = $this->get('period') ?: date('Y-m');
            $sort_by = $this->get('sort_by') ?: 'revenue';
            $limit = min((int)$this->get('limit', true) ?: 100, 500);
            $offset = (int)$this->get('offset', true) ?: 0;

            // Validate period format
            if (!$this->_validate_period($period)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid period format. Use YYYY-MM',
                    'status' => 400
                ]);
            }

            $data = $this->cost_center->get_pharmacies_with_kpis($period, $sort_by, $limit, $offset);
            $total = $this->cost_center->get_pharmacy_count();

            return $this->response([
                'success' => true,
                'data' => $data,
                'period' => $period,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'pages' => ceil($total / $limit)
                ],
                'timestamp' => date('Y-m-d\TH:i:s\Z'),
                'status' => 200
            ]);

        } catch (Exception $e) {
            log_message('error', 'Cost Center API - Pharmacies: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error fetching pharmacies',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * GET /api/v1/cost-center/pharmacies/{id}/branches
     * 
     * Get pharmacy detail with all branches (drill-down)
     * 
     * URL Parameters:
     * - id: pharmacy_id
     * 
     * Query Parameters:
     * - period: YYYY-MM (default: current month)
     */
    public function pharmacy_branches_get($pharmacy_id = null) {
        try {
            if (!$pharmacy_id) {
                return $this->response([
                    'success' => false,
                    'message' => 'Pharmacy ID is required',
                    'status' => 400
                ]);
            }

            // Validate pharmacy exists
            if (!$this->cost_center->pharmacy_exists($pharmacy_id)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Pharmacy not found',
                    'status' => 404
                ]);
            }

            $period = $this->get('period') ?: date('Y-m');

            // Validate period format
            if (!$this->_validate_period($period)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid period format. Use YYYY-MM',
                    'status' => 400
                ]);
            }

            $result = $this->cost_center->get_pharmacy_with_branches($pharmacy_id, $period);

            if (!$result['pharmacy']) {
                return $this->response([
                    'success' => false,
                    'message' => 'No data available for selected period',
                    'status' => 404
                ]);
            }

            return $this->response([
                'success' => true,
                'pharmacy' => $result['pharmacy'],
                'branches' => $result['branches'],
                'period' => $period,
                'branch_count' => count($result['branches']),
                'timestamp' => date('Y-m-d\TH:i:s\Z'),
                'status' => 200
            ]);

        } catch (Exception $e) {
            log_message('error', 'Cost Center API - Pharmacy Branches: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error fetching pharmacy branches',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * GET /api/v1/cost-center/branches/{id}/detail
     * 
     * Get branch detail with cost breakdown
     * 
     * URL Parameters:
     * - id: branch_id
     * 
     * Query Parameters:
     * - period: YYYY-MM (default: current month)
     */
    public function branch_detail_get($branch_id = null) {
        try {
            if (!$branch_id) {
                return $this->response([
                    'success' => false,
                    'message' => 'Branch ID is required',
                    'status' => 400
                ]);
            }

            // Validate branch exists
            if (!$this->cost_center->branch_exists($branch_id)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Branch not found',
                    'status' => 404
                ]);
            }

            $period = $this->get('period') ?: date('Y-m');

            // Validate period format
            if (!$this->_validate_period($period)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid period format. Use YYYY-MM',
                    'status' => 400
                ]);
            }

            $branch = $this->cost_center->get_branch_detail($branch_id, $period);

            if (!$branch) {
                return $this->response([
                    'success' => false,
                    'message' => 'No data available for selected period',
                    'status' => 404
                ]);
            }

            // Get cost breakdown
            $breakdown = $this->cost_center->get_cost_breakdown($branch_id, $period);

            return $this->response([
                'success' => true,
                'branch_id' => $branch_id,
                'branch_name' => $branch['branch_name'],
                'branch_code' => $branch['branch_code'],
                'pharmacy_id' => $branch['pharmacy_id'],
                'pharmacy_name' => $branch['pharmacy_name'],
                'period' => $period,
                'kpi_total_revenue' => (float)$branch['kpi_total_revenue'],
                'cost_breakdown' => [
                    'cogs' => (float)$branch['kpi_cogs'],
                    'inventory_movement' => (float)$branch['kpi_inventory_movement'],
                    'operational' => (float)$branch['kpi_operational'],
                    'total_cost' => (float)$branch['kpi_total_cost']
                ],
                'kpi_profit_loss' => (float)$branch['kpi_profit_loss'],
                'kpi_profit_margin_pct' => (float)$branch['kpi_profit_margin_pct'],
                'kpi_cost_ratio_pct' => (float)$branch['kpi_cost_ratio_pct'],
                'timestamp' => date('Y-m-d\TH:i:s\Z'),
                'status' => 200
            ]);

        } catch (Exception $e) {
            log_message('error', 'Cost Center API - Branch Detail: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error fetching branch detail',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * GET /api/v1/cost-center/branches/{id}/timeseries
     * 
     * Get time series data for trend analysis
     * 
     * URL Parameters:
     * - id: branch_id
     * 
     * Query Parameters:
     * - months: number of months to retrieve (default: 12)
     */
    public function branch_timeseries_get($branch_id = null) {
        try {
            if (!$branch_id) {
                return $this->response([
                    'success' => false,
                    'message' => 'Branch ID is required',
                    'status' => 400
                ]);
            }

            // Validate branch exists
            if (!$this->cost_center->branch_exists($branch_id)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Branch not found',
                    'status' => 404
                ]);
            }

            $months = min((int)$this->get('months', true) ?: 12, 60);

            $data = $this->cost_center->get_timeseries_data($branch_id, $months, 'branch');

            return $this->response([
                'success' => true,
                'branch_id' => $branch_id,
                'months' => $months,
                'data' => array_map(function($item) {
                    return [
                        'period' => $item['period'],
                        'revenue' => (float)$item['revenue'],
                        'cost' => (float)$item['cost'],
                        'profit' => (float)$item['profit'],
                        'margin_pct' => (float)$item['margin_pct']
                    ];
                }, $data),
                'timestamp' => date('Y-m-d\TH:i:s\Z'),
                'status' => 200
            ]);

        } catch (Exception $e) {
            log_message('error', 'Cost Center API - Timeseries: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error fetching timeseries data',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * GET /api/v1/cost-center/summary
     * 
     * Get company-level summary statistics
     * 
     * Query Parameters:
     * - period: YYYY-MM (default: current month)
     */
    public function summary_get() {
        try {
            $period = $this->get('period') ?: date('Y-m');

            // Validate period format
            if (!$this->_validate_period($period)) {
                return $this->response([
                    'success' => false,
                    'message' => 'Invalid period format. Use YYYY-MM',
                    'status' => 400
                ]);
            }

            $summary = $this->cost_center->get_summary_stats($period);
            $periods = $this->cost_center->get_available_periods(12);

            if (!$summary) {
                return $this->response([
                    'success' => false,
                    'message' => 'No data available for selected period',
                    'status' => 404
                ]);
            }

            return $this->response([
                'success' => true,
                'summary' => [
                    'period' => $summary['period'],
                    'total_revenue' => (float)$summary['kpi_total_revenue'],
                    'total_cost' => (float)$summary['kpi_total_cost'],
                    'profit' => (float)$summary['kpi_profit_loss'],
                    'profit_margin_pct' => (float)$summary['kpi_profit_margin_pct'],
                    'pharmacy_count' => (int)$summary['entity_count'],
                    'last_updated' => $summary['last_updated']
                ],
                'available_periods' => $periods,
                'timestamp' => date('Y-m-d\TH:i:s\Z'),
                'status' => 200
            ]);

        } catch (Exception $e) {
            log_message('error', 'Cost Center API - Summary: ' . $e->getMessage());
            return $this->response([
                'success' => false,
                'message' => 'Error fetching summary',
                'error' => $e->getMessage(),
                'status' => 500
            ]);
        }
    }

    /**
     * Validate period format (YYYY-MM)
     */
    private function _validate_period($period) {
        return preg_match('/^\d{4}-\d{2}$/', $period) && 
               checkdate((int)substr($period, 5, 2), 1, (int)substr($period, 0, 4));
    }
}
