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
            
            error_log('[COST_CENTER] Fetching pharmacies with health scores and margins');
            $pharmacies = $this->cost_center->get_pharmacies_with_health_scores($period, 100, 0);
            error_log('[COST_CENTER] Pharmacies retrieved: ' . count($pharmacies ?? []) . ' records');
            
            error_log('[COST_CENTER] Fetching branches with health scores and margins');
            $branches = $this->cost_center->get_branches_with_health_scores($period, 100, 0);
            error_log('[COST_CENTER] Branches retrieved: ' . count($branches ?? []) . ' records');
            
            error_log('[COST_CENTER] Fetching profit margins (both types)');
            $margins = $this->cost_center->get_profit_margins_both_types(null, $period);
            error_log('[COST_CENTER] Margins retrieved: Gross=' . $margins['gross_margin'] . '%, Net=' . $margins['net_margin'] . '%');
            
            error_log('[COST_CENTER] Fetching profit margin trends');
            $margin_trends_monthly = [];
            $margin_trends_weekly = [];
            
            // Get company-level trends by aggregating top pharmacies
            if (!empty($pharmacies)) {
                // For trends, we can get company-wide data
                $trend_data = $this->cost_center->get_pharmacy_trends($pharmacies[0]['pharmacy_id'] ?? null, 12);
                $margin_trends_monthly = isset($trend_data['monthly']) ? $trend_data['monthly'] : [];
                $margin_trends_weekly = isset($trend_data['weekly']) ? $trend_data['weekly'] : [];
                error_log('[COST_CENTER] Trends retrieved: Monthly=' . count($margin_trends_monthly) . ', Weekly=' . count($margin_trends_weekly));
            }
            
            error_log('[COST_CENTER] Calculating health scores for pharmacies');
            foreach ($pharmacies as &$pharmacy) {
                $health = $this->cost_center->calculate_health_score($pharmacy['net_margin_pct'], $pharmacy['kpi_total_revenue']);
                $pharmacy['health_status'] = $health['status'];
                $pharmacy['health_color'] = $health['color'];
                $pharmacy['health_description'] = $health['description'];
            }
            
            error_log('[COST_CENTER] Fetching available periods');
            $periods = $this->cost_center->get_available_periods(24);
            error_log('[COST_CENTER] Periods retrieved: ' . count($periods ?? []) . ' records');

            // Fetch company-level summary metrics using stored procedure
            error_log('[COST_CENTER] Fetching company-level summary metrics from sp_get_sales_analytics_hierarchical');
            $company_metrics = $this->cost_center->get_company_summary_metrics('monthly', $period);
            error_log('[COST_CENTER] Company metrics retrieved: ' . json_encode($company_metrics));

            // Fetch best moving products using stored procedure
            error_log('[COST_CENTER] Fetching best moving products from stored procedure');
            $best_products = $this->cost_center->get_best_moving_products('company', null, 'monthly', $period);
            error_log('[COST_CENTER] Best products retrieved: ' . count($best_products ?? []) . ' records');

            // Prepare view data - merge with $this->data for layout/assets
            $view_data = array_merge($this->data, [
                'page_title' => 'Cost Center Dashboard',
                'period' => $period,
                'summary' => $summary,
                'margins' => $margins,
                'company_metrics' => $company_metrics,
                'best_products' => $best_products,
                'pharmacies' => $pharmacies,
                'branches' => $branches,
                'margin_trends_monthly' => $margin_trends_monthly,
                'margin_trends_weekly' => $margin_trends_weekly,
                'periods' => $periods,
            ]);

            error_log('[COST_CENTER] About to render view');
            
            // Load template header
            $this->load->view($this->theme . 'header', $view_data);
            
            // Load modern dashboard view (Horizon UI with ECharts)
            $this->load->view($this->theme . 'cost_center/cost_center_dashboard_modern', $view_data);
            
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
                show_error('Pharmacy not found!', 404);
            }
             
            $period = $this->input->get('period') ?: date('Y-m');

            // Validate period format
            if (!$this->_validate_period($period)) {
                $period = date('Y-m');
            }

            // Fetch data
            error_log('[PHARMACY_DETAIL] Fetching pharmacy data for ID: ' . $pharmacy_id . ', Period: ' . $period);
            $pharmacy_data = $this->cost_center->get_pharmacy_with_branches($pharmacy_id, $period);
            error_log('[PHARMACY_DETAIL] Pharmacy data returned: ' . json_encode($pharmacy_data));
            $is_empty_data = false;
            
            // If no transaction data exists, initialize empty record
            if (!$pharmacy_data['pharmacy']) {
                error_log('[PHARMACY_DETAIL] No pharmacy data found, initializing empty record');
                $pharmacy_data = $this->_init_empty_pharmacy_data($pharmacy_id, $period);
                $is_empty_data = true;
            } else {
                error_log('[PHARMACY_DETAIL] Pharmacy data found: ' . json_encode($pharmacy_data['pharmacy']));
                error_log('[PHARMACY_DETAIL] Branches count: ' . count($pharmacy_data['branches']));
            }
            
            $periods = $this->cost_center->get_available_periods(24);
            
            // Get profit margins and trends for pharmacy
            $pharmacy_margins = $this->cost_center->get_profit_margins_both_types($pharmacy_id, $period);
            $pharmacy_trends = $this->cost_center->get_pharmacy_trends($pharmacy_id, 12);
            $cost_breakdown = $this->cost_center->get_cost_breakdown_detailed($pharmacy_id, $period);

            // Add health score to pharmacy
            $health = $this->cost_center->calculate_health_score($pharmacy_data['pharmacy']['kpi_profit_margin_pct'] ?? 0);
            $pharmacy_data['pharmacy']['health_status'] = $health['status'];
            $pharmacy_data['pharmacy']['health_color'] = $health['color'];
            $pharmacy_data['pharmacy']['health_description'] = $health['description'];
            $pharmacy_data['pharmacy']['is_empty_data'] = $is_empty_data;
            
            // Add health scores to branches
            foreach ($pharmacy_data['branches'] as &$branch) {
                $branch_health = $this->cost_center->calculate_health_score($branch['kpi_profit_margin_pct'] ?? 0);
                $branch['health_status'] = $branch_health['status'];
                $branch['health_color'] = $branch_health['color'];
                $branch['health_description'] = $branch_health['description'];
            }

            // Prepare view data - merge with $this->data for layout/assets
            $view_data = array_merge($this->data, [
                'page_title' => $pharmacy_data['pharmacy']['pharmacy_name'] . ' - Cost Center',
                'period' => $period,
                'pharmacy' => $pharmacy_data['pharmacy'],
                'branches' => $pharmacy_data['branches'],
                'pharmacy_margins' => $pharmacy_margins,
                'pharmacy_trends' => $pharmacy_trends,
                'cost_breakdown' => $cost_breakdown,
                'periods' => $periods,
            ]);

            // Load template header
            $this->load->view($this->theme . 'header', $view_data);
            
            // Load pharmacy detail view (modern Horizon UI)
            $this->load->view($this->theme . 'cost_center/cost_center_pharmacy_modern', $view_data);
            
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
            
            // Get profit margins and trends for branch
            $branch_margins = $this->cost_center->get_profit_margins_both_types(null, $period); // Company-level for reference
            $branch_trends = $this->cost_center->get_branch_trends($branch_id, 12);

            if (!$branch) {
                show_error('No data available for selected period', 404);
            }

            // Add health score to branch
            $health = $this->cost_center->calculate_health_score($branch['kpi_profit_margin_pct']);
            $branch['health_status'] = $health['status'];
            $branch['health_color'] = $health['color'];
            $branch['health_description'] = $health['description'];

            // Prepare view data - merge with $this->data for layout/assets
            $view_data = array_merge($this->data, [
                'page_title' => $branch['branch_name'] . ' - Cost Center',
                'period' => $period,
                'branch' => $branch,
                'timeseries' => $timeseries,
                'breakdown' => $breakdown,
                'branch_margins' => $branch_margins,
                'branch_trends' => $branch_trends,
                'periods' => $periods,
            ]);

            // Load template header
            $this->load->view($this->theme . 'header', $view_data);
            
            // Load branch detail view (modern Horizon UI)
            $this->load->view($this->theme . 'cost_center/cost_center_branch_modern', $view_data);
            
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

    /**
     * Helper: Initialize empty pharmacy data structure for periods with no transactions
     * 
     * @param int $pharmacy_id
     * @param string $period YYYY-MM
     * @return array
     * @throws Exception
     */
    private function _init_empty_pharmacy_data($pharmacy_id, $period) {
        $pharmacy_info = $this->cost_center->get_pharmacy_info($pharmacy_id);
        
        if (!$pharmacy_info) {
            show_error('Pharmacy not found', 404);
        }
        
        return [
            'pharmacy' => [
                'pharmacy_id' => $pharmacy_id,
                'warehouse_id' => $pharmacy_info['warehouse_id'],
                'pharmacy_name' => $pharmacy_info['pharmacy_name'],
                'pharmacy_code' => $pharmacy_info['pharmacy_code'],
                'period' => $period,
                'kpi_total_revenue' => 0,
                'kpi_total_cost' => 0,
                'kpi_profit_loss' => 0,
                'kpi_profit_margin_pct' => 0,
                'kpi_cost_ratio_pct' => 0,
                'branch_count' => 0,
                'last_updated' => date('Y-m-d H:i:s'),
            ],
            'branches' => []
        ];
    }

    /**
     * Performance Dashboard - Company-level metrics and best-moving products
     * 
     * GET /admin/cost_center/performance
     * Query params: period (YYYY-MM, 'today', or 'ytd'), level (company/pharmacy/branch), warehouse_id (for pharmacy/branch)
     */
    public function performance() {
        try {
            // DEBUG: Start logging
            error_log('[COST_CENTER_PERFORMANCE] Performance dashboard method started');
            
            $period = $this->input->get('period') ?: 'today';
            $level = $this->input->get('level') ?: 'company';
            $warehouse_id = $this->input->get('warehouse_id') ?: null;
            
            error_log('[COST_CENTER_PERFORMANCE] Period: ' . $period . ', Level: ' . $level . ', Warehouse: ' . $warehouse_id);
            
            // Determine period_type and target_month for stored procedure
            $period_type = 'monthly';  // default
            $target_month = null;
            
            if ($period === 'today') {
                $period_type = 'today';
                $target_month = null;
            } elseif ($period === 'ytd') {
                $period_type = 'ytd';
                $target_month = null;
            } else {
                // Validate YYYY-MM format
                if (!$this->_validate_period($period)) {
                    $period = date('Y-m');
                    error_log('[COST_CENTER_PERFORMANCE] Period invalid, using current: ' . $period);
                }
                $period_type = 'monthly';
                $target_month = $period;
            }

            // Validate level
            $valid_levels = ['company', 'pharmacy', 'branch'];
            if (!in_array($level, $valid_levels)) {
                $level = 'company';
                error_log('[COST_CENTER_PERFORMANCE] Level invalid, using company');
            }

            // Fetch company-level summary metrics using stored procedure
            error_log('[COST_CENTER_PERFORMANCE] Fetching summary metrics for period_type: ' . $period_type . ', target_month: ' . ($target_month ?: 'null') . ', level: ' . $level);
            $summary_metrics = $this->cost_center->get_hierarchical_analytics($period_type, $target_month, $warehouse_id, $level);
            
            if (!$summary_metrics['success']) {
                error_log('[COST_CENTER_PERFORMANCE] Error fetching metrics: ' . $summary_metrics['error']);
                show_error('Error loading performance data: ' . $summary_metrics['error'], 500);
                return;
            }

            $company_metrics = $summary_metrics['summary'];
            $best_products = $summary_metrics['best_products'] ?? [];
            
            error_log('[COST_CENTER_PERFORMANCE] Metrics retrieved: ' . json_encode($company_metrics));
            error_log('[COST_CENTER_PERFORMANCE] Best products retrieved: ' . count($best_products) . ' records');

            // Get level label for display
            $level_labels = [
                'company' => 'Company Performance',
                'pharmacy' => 'Pharmacy Performance',
                'branch' => 'Branch Performance'
            ];
            $level_label = $level_labels[$level] ?? 'Company Performance';

            // Fetch available periods
            error_log('[COST_CENTER_PERFORMANCE] Fetching available periods');
            $periods = $this->cost_center->get_available_periods(24);
            error_log('[COST_CENTER_PERFORMANCE] Periods retrieved: ' . count($periods ?? []) . ' records');

            // Fetch combined warehouse and pharmacy hierarchy for dropdown
            error_log('[COST_CENTER_PERFORMANCE] Fetching combined warehouse/pharmacy hierarchy');
            $warehouse_pharmacy_hierarchy = $this->cost_center->get_warehouse_pharmacy_hierarchy();
            error_log('[COST_CENTER_PERFORMANCE] Hierarchy retrieved: ' . count($warehouse_pharmacy_hierarchy ?? []) . ' records');

            // Get selection from URL parameter (can be warehouse_id or pharmacy_id)
            $selected_entity_id = $this->input->get('entity_id') ?: null;
            $selected_entity_type = null;
            
            $branches = [];
            $branches_with_sales = [];
            $pharmacy_metrics = null;
            
            // STEP 1: Determine entity type and fetch appropriate data
            if ($selected_entity_id) {
                error_log('[COST_CENTER_PERFORMANCE] Selected entity ID: ' . $selected_entity_id);
                
                // Determine if selected entity is warehouse or pharmacy
                $this->db->select('warehouse_type, parent_id');
                $this->db->from('sma_warehouses');
                $this->db->where('id', $selected_entity_id);
                $entity_result = $this->db->get();
                
                if ($entity_result->num_rows() > 0) {
                    $entity = $entity_result->row();
                    $selected_entity_type = $entity->warehouse_type;
                    error_log('[COST_CENTER_PERFORMANCE] Entity type: ' . $selected_entity_type);
                    
                    // If it's a pharmacy, fetch branches and call analytics
                    if ($selected_entity_type === 'pharmacy') {
                        error_log('[COST_CENTER_PERFORMANCE] Processing pharmacy selection: ' . $selected_entity_id);
                        
                        // Fetch branches under this pharmacy (using parent_id)
                        $branches = $this->cost_center->get_branches_by_pharmacy($selected_entity_id);
                        error_log('[COST_CENTER_PERFORMANCE] Branches retrieved: ' . count($branches ?? []) . ' records');
                        
                        // Fetch branches with sales data
                        $period_format = ($period === 'today' ? date('Y-m') : ($period === 'ytd' ? date('Y-m') : $period));
                        $pharmacy_data = $this->cost_center->get_pharmacy_with_branches($selected_entity_id, $period_format);
                        $branches_with_sales = $pharmacy_data['branches'] ?? [];
                        error_log('[COST_CENTER_PERFORMANCE] Branches with sales retrieved: ' . count($branches_with_sales ?? []) . ' records');
                        
                        // Call get_hierarchical_analytics with pharmacy level to get pharmacy-specific metrics
                        error_log('[COST_CENTER_PERFORMANCE] Calling get_hierarchical_analytics for pharmacy level, warehouse_id=' . $selected_entity_id);
                        $pharmacy_analytics = $this->cost_center->get_hierarchical_analytics(
                            $period === 'today' ? 'today' : ($period === 'ytd' ? 'ytd' : 'monthly'),
                            $period_format,
                            $selected_entity_id,
                            'pharmacy'
                        );
                        
                        if ($pharmacy_analytics['success']) {
                            error_log('[COST_CENTER_PERFORMANCE] Pharmacy metrics retrieved successfully');
                            $company_metrics = $pharmacy_analytics['summary'];
                            $best_products = $pharmacy_analytics['best_products'];
                            $pharmacy_metrics = $company_metrics;
                            error_log('[COST_CENTER_PERFORMANCE] Pharmacy metrics: revenue=' . ($company_metrics->total_gross_sales ?? 'N/A'));
                        } else {
                            error_log('[COST_CENTER_PERFORMANCE] Failed to retrieve pharmacy metrics: ' . ($pharmacy_analytics['error'] ?? 'Unknown error'));
                        }
                        
                        // Update level to 'pharmacy'
                        $level = 'pharmacy';
                        $level_label = 'Pharmacy Performance';
                        $warehouse_id = $selected_entity_id;
                    }
                    // If warehouse, keep level as company but filter by warehouse
                    elseif ($selected_entity_type === 'warehouse') {
                        error_log('[COST_CENTER_PERFORMANCE] Processing warehouse selection: ' . $selected_entity_id);
                        $level = 'company';
                        $warehouse_id = $selected_entity_id;
                    }
                }
            }


            // Prepare view data - merge with $this->data for layout/assets
            $view_data = array_merge($this->data, [
                'page_title' => $level_label,
                'level' => $level,
                'warehouse_id' => $warehouse_id,
                'selected_entity_id' => $selected_entity_id,
                'selected_entity_type' => $selected_entity_type,
                'period' => $period,
                'summary_metrics' => $company_metrics,
                'best_products' => $best_products,
                'periods' => $periods,
                'warehouse_pharmacy_hierarchy' => $warehouse_pharmacy_hierarchy,
                'branches' => $branches,
                'branches_with_sales' => $branches_with_sales,
                'level_label' => $level_label
            ]);

            error_log('[COST_CENTER_PERFORMANCE] About to render performance view');
            
            // Load template header
            $this->load->view($this->theme . 'header', $view_data);
            
            // Load performance dashboard view
            $this->load->view($this->theme . 'cost_center/performance_dashboard', $view_data);
            
            // Load template footer
            $this->load->view($this->theme . 'footer', $view_data);
            
            error_log('[COST_CENTER_PERFORMANCE] Performance dashboard rendered successfully');

        } catch (Exception $e) {
            $error_msg = 'Performance Dashboard Error: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ', File: ' . $e->getFile() . ')';
            error_log('[COST_CENTER_PERFORMANCE] ' . $error_msg);
            error_log('[COST_CENTER_PERFORMANCE] Stack trace: ' . $e->getTraceAsString());
            log_message('error', $error_msg);
            show_error('Error loading performance dashboard: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Cost Center Management - Main management view
     * 
     * GET /admin/cost_center/management
     * Displays interface for managing cost centers for existing pharmacies and branches
     */
    public function management() {
        try {
            error_log('[COST_CENTER_MANAGEMENT] Management method started');
            
            // Fetch all pharmacies and branches for cost center management
            $pharmacies = $this->cost_center->get_all_pharmacies_for_cost_center();
            $cost_centers = $this->cost_center->get_all_cost_centers();
            
            // Group cost centers by entity
            $cost_centers_by_entity = [];
            foreach ($cost_centers as $cc) {
                $cost_centers_by_entity[$cc['entity_id']][] = $cc;
            }

            // Prepare view data
            $view_data = array_merge($this->data, [
                'page_title' => 'Cost Center Management',
                'pharmacies' => $pharmacies,
                'cost_centers' => $cost_centers,
                'cost_centers_by_entity' => $cost_centers_by_entity,
            ]);

            error_log('[COST_CENTER_MANAGEMENT] About to render management view');
            
            // Load template header
            $this->load->view($this->theme . 'header', $view_data);
            
            // Load cost center management view
            $this->load->view($this->theme . 'cost_center/cost_center_management', $view_data);
            
            // Load template footer
            $this->load->view($this->theme . 'footer', $view_data);
            
            error_log('[COST_CENTER_MANAGEMENT] Management view rendered successfully');

        } catch (Exception $e) {
            $error_msg = 'Cost Center Management Error: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ', File: ' . $e->getFile() . ')';
            error_log('[COST_CENTER_MANAGEMENT] ' . $error_msg);
            log_message('error', $error_msg);
            show_error('Error loading cost center management: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Add new cost center
     * 
     * POST /admin/cost_center/add_cost_center
     */
    public function add_cost_center() {
        // Debug logging
        error_log('[COST_CENTER_DEBUG] add_cost_center method called');
        error_log('[COST_CENTER_DEBUG] Request method: ' . $this->input->server('REQUEST_METHOD'));
        error_log('[COST_CENTER_DEBUG] Is AJAX: ' . ($this->input->is_ajax_request() ? 'YES' : 'NO'));
        error_log('[COST_CENTER_DEBUG] POST data: ' . json_encode($this->input->post()));
        
        // Temporarily disabled for debugging
        // if (!$this->input->is_ajax_request()) {
        //     show_404();
        // }

        $this->load->library('form_validation');
        
        // Validation rules
        $this->form_validation->set_rules('cost_center_code', 'Cost Center Code', 'required|is_unique[sma_cost_centers.cost_center_code]');
        $this->form_validation->set_rules('cost_center_name', 'Cost Center Name', 'required');
        $this->form_validation->set_rules('cost_center_level', 'Cost Center Level', 'required|in_list[1,2]');
        $this->form_validation->set_rules('entity_id', 'Entity ID', 'required|integer');

        if (!$this->form_validation->run()) {
            $this->sma->send_json([
                'success' => false,
                'message' => validation_errors()
            ]);
            return;
        }

        try {
            $data = [
                'cost_center_code' => $this->input->post('cost_center_code'),
                'cost_center_name' => $this->input->post('cost_center_name'),
                'cost_center_level' => (int)$this->input->post('cost_center_level'),
                'entity_id' => (int)$this->input->post('entity_id'),
                'parent_cost_center_id' => $this->input->post('parent_cost_center_id') ?: null,
                'description' => $this->input->post('description') ?: '',
                'is_active' => 1
            ];

            $cost_center_id = $this->cost_center->add_cost_center($data);

            if ($cost_center_id) {
                $this->sma->send_json([
                    'success' => true,
                    'message' => 'Cost center added successfully',
                    'cost_center_id' => $cost_center_id
                ]);
            } else {
                throw new Exception('Failed to create cost center');
            }

        } catch (Exception $e) {
            error_log('[COST_CENTER] Add cost center error: ' . $e->getMessage());
            $this->sma->send_json([
                'success' => false,
                'message' => 'Error creating cost center: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update existing cost center
     * 
     * POST /admin/cost_center/update_cost_center
     */
    public function update_cost_center() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->load->library('form_validation');
        
        // Validation rules
        $this->form_validation->set_rules('cost_center_id', 'Cost Center ID', 'required|integer');
        $this->form_validation->set_rules('cost_center_code', 'Cost Center Code', 'required');
        $this->form_validation->set_rules('cost_center_name', 'Cost Center Name', 'required');
        $this->form_validation->set_rules('cost_center_level', 'Cost Center Level', 'required|in_list[1,2]');

        if (!$this->form_validation->run()) {
            $this->sma->send_json([
                'success' => false,
                'message' => validation_errors()
            ]);
            return;
        }

        try {
            $cost_center_id = $this->input->post('cost_center_id');
            $data = [
                'cost_center_code' => $this->input->post('cost_center_code'),
                'cost_center_name' => $this->input->post('cost_center_name'),
                'cost_center_level' => (int)$this->input->post('cost_center_level'),
                'parent_cost_center_id' => $this->input->post('parent_cost_center_id') ?: null,
                'description' => $this->input->post('description') ?: '',
                'is_active' => (int)$this->input->post('is_active')
            ];

            $result = $this->cost_center->update_cost_center($cost_center_id, $data);

            if ($result) {
                $this->sma->send_json([
                    'success' => true,
                    'message' => 'Cost center updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update cost center');
            }

        } catch (Exception $e) {
            error_log('[COST_CENTER] Update cost center error: ' . $e->getMessage());
            $this->sma->send_json([
                'success' => false,
                'message' => 'Error updating cost center: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete cost center
     * 
     * POST /admin/cost_center/delete_cost_center
     */
    public function delete_cost_center() {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $cost_center_id = $this->input->post('cost_center_id');

        if (!$cost_center_id) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Cost center ID is required'
            ]);
            return;
        }

        try {
            $result = $this->cost_center->delete_cost_center($cost_center_id);

            if ($result) {
                $this->sma->send_json([
                    'success' => true,
                    'message' => 'Cost center deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete cost center');
            }

        } catch (Exception $e) {
            error_log('[COST_CENTER] Delete cost center error: ' . $e->getMessage());
            $this->sma->send_json([
                'success' => false,
                'message' => 'Error deleting cost center: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get cost centers for a specific entity (AJAX)
     * 
     * GET /admin/cost_center/get_entity_cost_centers/{entity_id}
     */
    public function get_entity_cost_centers($entity_id = null) {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!$entity_id) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Entity ID is required'
            ]);
            return;
        }

        try {
            $cost_centers = $this->cost_center->get_cost_centers_by_entity($entity_id);
            
            $this->sma->send_json([
                'success' => true,
                'cost_centers' => $cost_centers
            ]);

        } catch (Exception $e) {
            error_log('[COST_CENTER] Get entity cost centers error: ' . $e->getMessage());
            $this->sma->send_json([
                'success' => false,
                'message' => 'Error fetching cost centers: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get cost center by ID (AJAX)
     * 
     * GET /admin/cost_center/get_cost_center_by_id/{cost_center_id}
     */
    public function get_cost_center_by_id($cost_center_id = null) {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!$cost_center_id) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Cost Center ID is required'
            ]);
            return;
        }

        try {
            $cost_center = $this->cost_center->get_cost_center_by_id($cost_center_id);
            
            if ($cost_center) {
                $this->sma->send_json([
                    'success' => true,
                    'cost_center' => $cost_center
                ]);
            } else {
                $this->sma->send_json([
                    'success' => false,
                    'message' => 'Cost center not found'
                ]);
            }

        } catch (Exception $e) {
            error_log('[COST_CENTER] Get cost center by ID error: ' . $e->getMessage());
            $this->sma->send_json([
                'success' => false,
                'message' => 'Error fetching cost center: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test CSRF token (temporary debug method)
     */
    public function test_csrf() {
        if (!$this->input->is_ajax_request()) {
            show_error('This endpoint requires AJAX', 404);
        }

        $this->sma->send_json([
            'success' => true,
            'message' => 'CSRF token is working correctly!',
            'csrf_token_name' => $this->security->get_csrf_token_name(),
            'csrf_hash' => $this->security->get_csrf_hash(),
            'post_data' => $this->input->post()
        ]);
    }

    /**
     * Get parent cost centers for an entity (AJAX)
     * 
     * GET /admin/cost_center/get_parent_cost_centers/{entity_id}/{level}
     */
    public function get_parent_cost_centers($entity_id = null, $level = 1) {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        if (!$entity_id) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Entity ID is required'
            ]);
            return;
        }

        try {
            $cost_centers = $this->cost_center->get_parent_cost_centers($entity_id, $level);
            
            $this->sma->send_json([
                'success' => true,
                'cost_centers' => $cost_centers
            ]);

        } catch (Exception $e) {
            error_log('[COST_CENTER] Get parent cost centers error: ' . $e->getMessage());
            $this->sma->send_json([
                'success' => false,
                'message' => 'Error fetching parent cost centers: ' . $e->getMessage()
            ]);
        }
    }
}
