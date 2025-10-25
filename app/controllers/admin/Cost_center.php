<?php
/**
 * Cost Center Controller
 * 
 * Purpose: Display cost center dashboard and drill-down views
 * Views managed by this controller:
 * - cost_center_dashboard.php - Main dashboard
 * - cost_center_pharmacy.php - Pharmacy detail view
 * - cost_center_branch.php - Branch detail view
 * 
 * Date: 2025-10-25
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Cost_center extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $url = "admin/login";
            if ($this->input->server('QUERY_STRING')) {
                $url = $url . '?' . $this->input->server('QUERY_STRING') . '&redirect=' . $this->uri->uri_string();
            }
            $this->sma->md($url);
        }
        $this->load->model('admin/Cost_center_model', 'cost_center');
        $this->load->helper('url');
    }

    /**
     * Dashboard - Main cost center overview
     * 
     * GET /admin/cost_center/dashboard
     * Query params: period (YYYY-MM)
     */
    public function dashboard() {
        try {
            // DEBUG: Start logging
            error_log('[COST_CENTER] Dashboard method started');
            
            $period = $this->input->get('period') ?: date('Y-m');
            error_log('[COST_CENTER] Period: ' . $period);
            
            // Validate period format
            if (!$this->_validate_period($period)) {
                $period = date('Y-m');
                error_log('[COST_CENTER] Period invalid, using current: ' . $period);
            }

            // Fetch data
            error_log('[COST_CENTER] Fetching summary stats for period: ' . $period);
            $summary = $this->cost_center->get_summary_stats($period);
            error_log('[COST_CENTER] Summary stats retrieved: ' . json_encode($summary));
            
            error_log('[COST_CENTER] Fetching pharmacies with KPIs');
            $pharmacies = $this->cost_center->get_pharmacies_with_kpis($period, 'revenue', 100, 0);
            error_log('[COST_CENTER] Pharmacies retrieved: ' . count($pharmacies ?? []) . ' records');
            
            error_log('[COST_CENTER] Fetching available periods');
            $periods = $this->cost_center->get_available_periods(24);
            error_log('[COST_CENTER] Periods retrieved: ' . count($periods ?? []) . ' records');

            // Prepare view data - merge with $this->data for layout/assets
            $view_data = array_merge($this->data, [
                'page_title' => 'Cost Center Dashboard',
                'period' => $period,
                'summary' => $summary,
                'pharmacies' => $pharmacies,
                'periods' => $periods,
            ]);

            error_log('[COST_CENTER] About to render view');
            
            // Load template header
            $this->load->view($this->theme . 'header', $view_data);
            
            // Load main dashboard view
            $this->load->view($this->theme . 'cost_center/cost_center_dashboard', $view_data);
            
            // Load template footer
            $this->load->view($this->theme . 'footer', $view_data);
            
            error_log('[COST_CENTER] Dashboard rendered successfully');

        } catch (Exception $e) {
            $error_msg = 'Cost Center Dashboard Error: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ', File: ' . $e->getFile() . ')';
            error_log('[COST_CENTER] ' . $error_msg);
            error_log('[COST_CENTER] Stack trace: ' . $e->getTraceAsString());
            log_message('error', $error_msg);
            show_error('Error loading dashboard: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Pharmacy detail - Shows pharmacy with all branches
     * 
     * GET /admin/cost_center/pharmacy/{pharmacy_id}
     * Query params: period (YYYY-MM)
     */
    public function pharmacy($pharmacy_id = null) {
        try {
            if (!$pharmacy_id) {
                show_error('Pharmacy ID is required', 400);
            }

            // Validate pharmacy exists
            if (!$this->cost_center->pharmacy_exists($pharmacy_id)) {
                show_error('Pharmacy not found', 404);
            }

            $period = $this->input->get('period') ?: date('Y-m');

            // Validate period format
            if (!$this->_validate_period($period)) {
                $period = date('Y-m');
            }

            // Fetch data
            $pharmacy_data = $this->cost_center->get_pharmacy_with_branches($pharmacy_id, $period);
            $periods = $this->cost_center->get_available_periods(24);

            if (!$pharmacy_data['pharmacy']) {
                show_error('No data available for selected period', 404);
            }

            // Prepare view data - merge with $this->data for layout/assets
            $view_data = array_merge($this->data, [
                'page_title' => $pharmacy_data['pharmacy']['pharmacy_name'] . ' - Cost Center',
                'period' => $period,
                'pharmacy' => $pharmacy_data['pharmacy'],
                'branches' => $pharmacy_data['branches'],
                'periods' => $periods,
            ]);

            // Load template header
            $this->load->view($this->theme . 'header', $view_data);
            
            // Load pharmacy detail view
            $this->load->view($this->theme . 'cost_center/cost_center_pharmacy', $view_data);
            
            // Load template footer
            $this->load->view($this->theme . 'footer', $view_data);

        } catch (Exception $e) {
            log_message('error', 'Cost Center Pharmacy Error: ' . $e->getMessage());
            show_error('Error loading pharmacy detail: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Branch detail - Shows branch with cost breakdown
     * 
     * GET /admin/cost_center/branch/{branch_id}
     * Query params: period (YYYY-MM)
     */
    public function branch($branch_id = null) {
        try {
            if (!$branch_id) {
                show_error('Branch ID is required', 400);
            }

            // Validate branch exists
            if (!$this->cost_center->branch_exists($branch_id)) {
                show_error('Branch not found', 404);
            }

            $period = $this->input->get('period') ?: date('Y-m');

            // Validate period format
            if (!$this->_validate_period($period)) {
                $period = date('Y-m');
            }

            // Fetch data
            $branch = $this->cost_center->get_branch_detail($branch_id, $period);
            $timeseries = $this->cost_center->get_timeseries_data($branch_id, 12, 'branch');
            $breakdown = $this->cost_center->get_cost_breakdown($branch_id, $period);
            $periods = $this->cost_center->get_available_periods(24);

            if (!$branch) {
                show_error('No data available for selected period', 404);
            }

            // Prepare view data - merge with $this->data for layout/assets
            $view_data = array_merge($this->data, [
                'page_title' => $branch['branch_name'] . ' - Cost Center',
                'period' => $period,
                'branch' => $branch,
                'timeseries' => $timeseries,
                'breakdown' => $breakdown,
                'periods' => $periods,
            ]);

            // Load template header
            $this->load->view($this->theme . 'header', $view_data);
            
            // Load branch detail view
            $this->load->view($this->theme . 'cost_center/cost_center_branch', $view_data);
            
            // Load template footer
            $this->load->view($this->theme . 'footer', $view_data);

        } catch (Exception $e) {
            log_message('error', 'Cost Center Branch Error: ' . $e->getMessage());
            show_error('Error loading branch detail: ' . $e->getMessage(), 500);
        }
    }

    /**
     * AJAX endpoint to get pharmacies data for table
     * 
     * GET /admin/cost_center/get_pharmacies
     * Query params: period, sort_by, page, limit
     */
    public function get_pharmacies() {
        $period = $this->input->get('period') ?: date('Y-m');
        $sort_by = $this->input->get('sort_by') ?: 'revenue';
        $page = (int)$this->input->get('page', true) ?: 1;
        $limit = (int)$this->input->get('limit', true) ?: 20;
        $offset = ($page - 1) * $limit;

        // Validate inputs
        if (!$this->_validate_period($period)) {
            return $this->response_json([
                'success' => false,
                'message' => 'Invalid period format'
            ], 400);
        }

        $pharmacies = $this->cost_center->get_pharmacies_with_kpis($period, $sort_by, $limit, $offset);

        return $this->response_json([
            'success' => true,
            'data' => $pharmacies,
            'pagination' => [
                'page' => $page,
                'limit' => $limit
            ]
        ]);
    }

    /**
     * AJAX endpoint to get branch timeseries data
     * 
     * GET /admin/cost_center/get_timeseries
     * Query params: branch_id, months
     */
    public function get_timeseries() {
        $branch_id = (int)$this->input->get('branch_id', true);
        $months = (int)$this->input->get('months', true) ?: 12;

        if (!$branch_id) {
            return $this->response_json([
                'success' => false,
                'message' => 'Branch ID is required'
            ], 400);
        }

        $timeseries = $this->cost_center->get_timeseries_data($branch_id, $months, 'branch');

        return $this->response_json([
            'success' => true,
            'data' => $timeseries
        ]);
    }

    /**
     * Helper: Validate period format (YYYY-MM)
     */
    private function _validate_period($period) {
        return preg_match('/^\d{4}-\d{2}$/', $period) && 
               checkdate((int)substr($period, 5, 2), 1, (int)substr($period, 0, 4));
    }

    /**
     * Helper: Return JSON response
     */
    private function response_json($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}
