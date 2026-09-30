<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Loyalty extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            admin_redirect('login');
        }

        if ($this->Customer || $this->Supplier) {
            redirect('/');
        }

        $this->load->library('form_validation');
        $this->load->admin_model('loyalty_model');
    }

    /**
     * Loyalty Dashboard (Default View)
     */
    public function index()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => '#', 'page' => lang('Loyalty Dashboard')]
        ];
        $meta = ['page_title' => lang('Loyalty Dashboard'), 'bc' => $bc];
        $this->page_construct('loyalty/dashboard', $meta, $this->data);
    }

    /**
     * Loyalty Dashboard (Alias to index for menu link compatibility)
     */
    public function dashboard()
    {
        $this->index();
    }

    /**
     * Loyalty Rules Management
     */
    public function rules()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => '#', 'page' => lang('Loyalty Rules')]
        ];
        $meta = ['page_title' => lang('Loyalty Rules'), 'bc' => $bc];
        $this->data['company_id'] = $this->loyalty_model->getCompanyId();

        $this->page_construct('loyalty/rules', $meta, $this->data);
    }

    /**
     * API: Get all loyalty rules
     */
    public function get_rules()
    {
        // Fetch rules from external API
        $result = $this->call_loyalty_api('/api/v1/rules', 'GET');

        if ($result['success']) {
            // Transform API response to frontend format if needed
            $rules = $this->transform_rules_from_api($result['data']);
            
            $this->sma->send_json([
                'success' => true,
                'rules' => $rules
            ]);
        } else {
            $this->sma->send_json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to fetch rules',
                'rules' => []
            ]);
        }
    }

    /**
     * API: Get single rule by ID
     */
    public function get_rule($id)
    {
        if (!$id) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Rule ID is required'
            ]);
            return;
        }

        // Fetch single rule from external API
        $result = $this->call_loyalty_api('/api/v1/rules/' . $id, 'GET');

        if ($result['success']) {
            $rule = $this->transform_rule_from_api($result['data']);
            
            $this->sma->send_json([
                'success' => true,
                'rule' => $rule
            ]);
        } else {
            $this->sma->send_json([
                'success' => false,
                'message' => $result['message'] ?? 'Rule not found'
            ]);
        }
    }

    /**
     * Transform rules array from API format to frontend format
     */
    private function transform_rules_from_api($apiRules)
    {
        if (empty($apiRules) || !is_array($apiRules)) {
            return [];
        }

        $rules = [];
        foreach ($apiRules as $apiRule) {
            $rules[] = $this->transform_rule_from_api($apiRule);
        }

        return $rules;
    }

    /**
     * Transform single rule from API format to frontend format
     */
    private function transform_rule_from_api($apiRule)
    {
        return [
            'id' => $apiRule['id'] ?? null,
            'name' => $apiRule['name'] ?? '',
            'description' => $apiRule['description'] ?? '',
            'hierarchy_level' => strtolower($apiRule['scope']['level'] ?? 'company'),
            'hierarchy_node_id' => $apiRule['scope']['scopeId'] ?? '',
            'action_type' => $apiRule['action']['type'] ?? '',
            'action_value' => $this->extract_action_value($apiRule['action'] ?? []),
            'priority' => $apiRule['priority'] ?? 5,
            'status' => $apiRule['status'] ?? 1,
            'start_date' => $this->format_date_from_iso($apiRule['validFrom'] ?? null),
            'end_date' => $this->format_date_from_iso($apiRule['validUntil'] ?? null),
            'conditions' => $apiRule['conditions'] ?? [],
            'created_at' => $apiRule['createdAt'] ?? null,
            'updated_at' => $apiRule['updatedAt'] ?? null
        ];
    }

    /**
     * Extract action value from API action object
     */
    private function extract_action_value($action)
    {
        if (empty($action['value'])) {
            return null;
        }

        $value = $action['value'];

        if (isset($value['percentageValue'])) {
            return $value['percentageValue'];
        }
        if (isset($value['numberValue'])) {
            return $value['numberValue'];
        }
        if (isset($value['stringValue'])) {
            return is_array($value['stringValue']) ? implode(',', $value['stringValue']) : $value['stringValue'];
        }

        return null;
    }

    /**
     * Format ISO date to standard format
     */
    private function format_date_from_iso($isoDate)
    {
        if (empty($isoDate)) {
            return null;
        }

        try {
            $dt = new DateTime($isoDate);
            return $dt->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * API: Save rule (Create or Update)
     */
    public function save_rule()
    {
        // Log the request
        log_message('debug', 'Loyalty save_rule called');
        
        // Get JSON input
        $input = file_get_contents('php://input');
        log_message('debug', 'Raw input: ' . $input);
        
        $data = json_decode($input, true);

        if (!$data) {
            log_message('error', 'Invalid JSON data received');
            $this->sma->send_json([
                'success' => false,
                'message' => 'Invalid JSON data'
            ]);
            return;
        }

        // Validate required fields
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('rule_name', 'Rule Name', 'required');
        $this->form_validation->set_rules('hierarchy_level', 'Hierarchy Level', 'required');
        $this->form_validation->set_rules('action_type', 'Action Type', 'required');

        if ($this->form_validation->run() == false) {
            log_message('error', 'Validation failed: ' . validation_errors());
            $this->sma->send_json([
                'success' => false,
                'message' => validation_errors()
            ]);
            return;
        }

        // Transform data to match external API format
        $apiPayload = $this->transform_to_api_format($data);
        log_message('debug', 'API Payload: ' . json_encode($apiPayload));

        // Call external Loyalty Rules API
        $result = $this->call_loyalty_api('/api/v1/rules', 'POST', $apiPayload);
        log_message('debug', 'API Result: ' . json_encode($result));

        if ($result['success']) {
            $this->sma->send_json([
                'success' => true,
                'message' => 'Rule saved successfully',
                'rule' => $result['data']
            ]);
        } else {
            $this->sma->send_json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to save rule',
                'debug' => $result
            ]);
        }
    }

    /**
     * Transform form data to external API format
     */
    private function transform_to_api_format($formData)
    {
        // Build scope object
        $scope = [
            'level' => strtoupper($formData['hierarchy_level'] ?? 'COMPANY'),
            'scopeId' => $formData['hierarchy_node_id'] ?? 'company-001'
        ];

        // Transform conditions array
        $conditions = [];
        if (!empty($formData['conditions']) && is_array($formData['conditions'])) {
            foreach ($formData['conditions'] as $condition) {
                $transformedCondition = $this->transform_condition($condition);
                if ($transformedCondition) {
                    $conditions[] = $transformedCondition;
                }
            }
        }

        // Transform actions array
        $actions = [];
        if (!empty($formData['actions']) && is_array($formData['actions'])) {
            foreach ($formData['actions'] as $action) {
                $transformedAction = $this->transform_action($action);
                if ($transformedAction) {
                    $actions[] = $transformedAction;
                }
            }
        }

        // Infer rule type from first action
        $ruleType = $this->infer_rule_type($formData);

        // Build final API payload
        $payload = [
            'name' => $formData['rule_name'],
            'description' => $formData['description'] ?? '',
            'ruleType' => $ruleType,
            'scope' => $scope,
            'conditions' => $conditions,
            'actions' => $actions
            // Constraints commented out for now
            // 'constraints' => [
            //     'usageLimit' => (int)($formData['usage_limit'] ?? 0),
            //     'perCustomerLimit' => (int)($formData['per_customer_limit'] ?? 0),
            //     'validFrom' => $this->format_iso_date($formData['start_date'] ?? null),
            //     'validUntil' => $this->format_iso_date($formData['end_date'] ?? null)
            // ]
        ];

        return $payload;
    }

    /**
     * Transform condition to API format
     */
    private function transform_condition($condition)
    {
        if (empty($condition['type']) || empty($condition['value'])) {
            return null;
        }

        $transformedCondition = [
            'type' => $condition['type'],
            'operator' => $this->get_condition_operator($condition),
            'value' => $this->transform_condition_value($condition)
        ];

        return $transformedCondition;
    }

    /**
     * Get operator for condition based on type
     */
    private function get_condition_operator($condition)
    {
        $type = $condition['type'];
        $operator = $condition['operator'] ?? null;

        // Map specific operators based on condition type
        switch ($type) {
            case 'CUSTOMER_TIER':
            case 'CATEGORY':
                return 'IN'; // For arrays, use IN operator
                
            case 'PURCHASE_AMOUNT':
            case 'CLV':
            case 'FREQUENCY':
                // Use provided operator or default to '>='
                return $operator ?? '>=';
                
            case 'TIME_BASED':
                return 'EQUALS';
                
            default:
                return $operator ?? 'EQUALS';
        }
    }

    /**
     * Transform condition value based on type
     */
    private function transform_condition_value($condition)
    {
        $type = $condition['type'];
        $value = $condition['value'];

        switch ($type) {
            case 'PURCHASE_AMOUNT':
            case 'CLV':
                // Simple number value
                return ['numberValue' => (float)$value];

            case 'FREQUENCY':
                // Number value (period can be added to metadata if needed)
                return ['numberValue' => (int)$value];

            case 'CATEGORY':
                // String array for categories
                $categories = is_array($value) ? $value : array_map('trim', explode(',', $value));
                return ['stringValue' => $categories];

            case 'CUSTOMER_TIER':
                // String array for tiers (e.g., ["GOLD", "PLATINUM"])
                $tiers = is_array($value) ? $value : [$value];
                return ['stringValue' => $tiers];

            case 'TIME_BASED':
                // String value for time-based conditions
                return ['stringValue' => $value];

            case 'INVENTORY':
            case 'WEATHER':
            case 'CUSTOM':
                // String or mixed values
                return ['stringValue' => $value];

            default:
                // Default to string value
                return ['stringValue' => is_array($value) ? $value : (string)$value];
        }
    }

    /**
     * Transform action to API format
     */
    private function transform_action($action)
    {
        if (empty($action['type'])) {
            return null;
        }

        $actionType = $action['type'];

        return [
            'type' => $actionType,
            'value' => $this->transform_action_value($actionType, $action)
        ];
    }

    /**
     * Transform action value based on type
     */
    private function transform_action_value($actionType, $action)
    {
        switch ($actionType) {
            case 'DISCOUNT_PERCENTAGE':
                return ['percentageValue' => (float)($action['value'] ?? 0)];

            case 'DISCOUNT_FIXED':
                return ['numberValue' => (float)($action['value'] ?? 0)];

            case 'DISCOUNT_BOGO':
                return [
                    'metadata' => [
                        'buyQty' => (int)($action['buy_quantity'] ?? 1),
                        'getQty' => (int)($action['get_quantity'] ?? 1)
                    ]
                ];

            case 'LOYALTY_POINTS':
                return ['numberValue' => (int)($action['value'] ?? 0)];

            case 'TIER_UPGRADE':
            case 'FREE_ITEM':
            case 'NOTIFICATION':
                return ['stringValue' => $action['value'] ?? ''];

            case 'CUSTOM_ACTION':
                $metadata = [];
                if (!empty($action['custom_metadata'])) {
                    $metadata = json_decode($action['custom_metadata'], true) ?? [];
                }
                return [
                    'stringValue' => $action['value'] ?? '',
                    'metadata' => $metadata
                ];

            default:
                return ['stringValue' => $action['value'] ?? ''];
        }
    }

    /**
     * Infer rule type from actions
     */
    private function infer_rule_type($formData)
    {
        // If explicitly provided
        if (!empty($formData['rule_type'])) {
            return strtoupper($formData['rule_type']);
        }

        // Infer from first action
        if (!empty($formData['actions']) && is_array($formData['actions'])) {
            $firstAction = $formData['actions'][0];
            $actionType = $firstAction['type'] ?? '';

            switch ($actionType) {
                case 'DISCOUNT_PERCENTAGE':
                case 'DISCOUNT_FIXED':
                case 'DISCOUNT_BOGO':
                    return 'DISCOUNT';
                case 'LOYALTY_POINTS':
                    return 'LOYALTY';
                case 'TIER_UPGRADE':
                    return 'TIER';
                case 'FREE_ITEM':
                    return 'PROMOTION';
                case 'NOTIFICATION':
                    return 'NOTIFICATION';
                case 'CUSTOM_ACTION':
                    return 'CUSTOM';
                default:
                    return 'GENERAL';
            }
        }

        return 'GENERAL';
    }

    /**
     * Get rule type from action type (deprecated - use infer_rule_type)
     */
    private function get_rule_type($actionType)
    {
        if (strpos($actionType, 'DISCOUNT') !== false) {
            return 'DISCOUNT';
        }
        if ($actionType === 'LOYALTY_POINTS') {
            return 'POINTS';
        }
        if ($actionType === 'TIER_UPGRADE') {
            return 'TIER';
        }
        return 'CUSTOM';
    }

    /**
     * Format date to ISO 8601
     */
    private function format_iso_date($date)
    {
        if (empty($date)) {
            return null;
        }
        
        try {
            $dt = new DateTime($date);
            return $dt->format('Y-m-d\TH:i:s\Z');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Call external Loyalty API
     */
    private function call_loyalty_api($endpoint, $method = 'GET', $data = null)
    {
        $url = LOYALTY_API_URL . $endpoint;
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, LOYALTY_API_TIMEOUT);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'message' => 'API Connection Error: ' . $error
            ];
        }

        $responseData = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $responseData
            ];
        } else {
            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'API Error (HTTP ' . $httpCode . ')',
                'data' => $responseData
            ];
        }
    }

    /**
     * API: Delete loyalty rule
     */
    public function delete_rule($id = null)
    {
        if (!$this->input->is_ajax_request() && !$id) {
            $this->sma->send_json(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        if (!$id) {
            $this->sma->send_json(['success' => false, 'message' => 'Rule ID required']);
            return;
        }

        // Call external API to delete rule
        $result = $this->call_loyalty_api('/api/v1/rules/' . $id, 'DELETE');

        if ($result['success']) {
            $this->sma->send_json([
                'success' => true,
                'message' => 'Rule deleted successfully'
            ]);
        } else {
            $this->sma->send_json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to delete rule'
            ]);
        }
    }

    /**
     * API: Get hierarchy nodes based on level
     */
    public function get_hierarchy_nodes($level)
    {
        $nodes = [];

        switch (strtoupper($level)) {
            case 'PHARMA_GROUP':
                // Disable prefix for loyalty tables
                $this->db->dbprefix = '';
                $nodes = $this->db->select('id, name')
                    ->from('loyalty_pharmacy_groups')
                    ->order_by('name', 'ASC')
                    ->get()
                    ->result_array();
                // Restore prefix
                $this->db->dbprefix = 'sma_';
                break;

            case 'PHARMACY':
                // Query from loyalty_pharmacies table joined with warehouses
                $this->db->dbprefix = '';
                $nodes = $this->db->select('lp.id, w.name, w.id as warehouse_id')
                    ->from('loyalty_pharmacies lp')
                    ->join('sma_warehouses w', 'lp.warehouse_id = w.id', 'left')
                    ->order_by('w.name', 'ASC')
                    ->get()
                    ->result_array();
                $this->db->dbprefix = 'sma_';
                
                // Format to return warehouse_id as id
                $nodes = array_map(function($node) {
                    return [
                        'id' => $node['warehouse_id'],
                        'name' => $node['name']
                    ];
                }, $nodes);
                break;

            case 'BRANCH':
                // Query from loyalty_branches table joined with warehouses
                $this->db->dbprefix = '';
                $nodes = $this->db->select('lb.id, w.name, w.id as warehouse_id')
                    ->from('loyalty_branches lb')
                    ->join('sma_warehouses w', 'lb.warehouse_id = w.id', 'left')
                    ->order_by('w.name', 'ASC')
                    ->get()
                    ->result_array();
                $this->db->dbprefix = 'sma_';
                
                // Format to return warehouse_id as id
                $nodes = array_map(function($node) {
                    return [
                        'id' => $node['warehouse_id'],
                        'name' => $node['name']
                    ];
                }, $nodes);
                break;

            default:
                $nodes = [];
        }

        $this->sma->send_json([
            'success' => true,
            'nodes' => $nodes
        ]);
    }

    /**
     * Budget Setup (Multi-level: Company/Pharmacy Group/Pharmacy/Branch)
     */
    public function budget_setup()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => '#', 'page' => lang('Budget Setup')]
        ];
        $meta = ['page_title' => lang('Budget Setup'), 'bc' => $bc];
        $this->page_construct('loyalty/budget_setup', $meta, $this->data);
    }

    /**
     * Budget Dashboard - Main budgeting UI with tracking, forecasting, and compliance
     */
    public function budget()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => '#', 'page' => lang('Budget')]
        ];
        $meta = ['page_title' => lang('Budget Management'), 'bc' => $bc];
        $this->page_construct('loyalty/budget', $meta, $this->data);
    }

    /**
     * Budget Allocation - Allocate budgets from parent to children hierarchy
     */
    public function budget_allocation()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['company_id'] = $this->loyalty_model->getCompanyId();

        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => admin_url('loyalty/budget_definition'), 'page' => lang('Budget Definition')],
            ['link' => '#', 'page' => lang('Budget Allocation')]
        ];
        $meta = ['page_title' => lang('Budget Allocation'), 'bc' => $bc];
        $this->page_construct('loyalty/budget_allocation', $meta, $this->data);
    }

    /**
     * API Endpoint to get budget status
     */
    public function get_budget_status()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        
        $data = $this->loyalty_model->getBudgetStatus($scopeLevel, $scopeId);
        $this->sma->send_json($data);
    }

    /**
     * API Endpoint to get burn rate
     */
    public function get_burn_rate()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        
        $data = $this->loyalty_model->getBurnRate($scopeLevel, $scopeId);
        $this->sma->send_json($data);
    }

    /**
     * API Endpoint to get budget projections
     */
    public function get_projections()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        
        $data = $this->loyalty_model->getBudgetProjections($scopeLevel, $scopeId);
        $this->sma->send_json($data);
    }

    /**
     * API Endpoint to get budget alerts
     */
    public function get_alerts()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        
        $data = $this->loyalty_model->getBudgetAlerts($scopeLevel, $scopeId);
        $this->sma->send_json($data);
    }

    /**
     * API Endpoint to get spending trend
     */
    public function get_spending_trend()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        $period = $this->input->get('period') ?: 'monthly';
        
        $data = $this->loyalty_model->getSpendingTrend($scopeLevel, $scopeId, $period);
        $this->sma->send_json($data);
    }

    /**
     * API Endpoint to get budget summary
     */
    public function get_summary()
    {
        $scopeLevel = $this->input->get('scopeLevel') ?: 'company';
        $scopeId = $this->input->get('scopeId') ?: 1;
        
        $data = $this->loyalty_model->getBudgetSummary($scopeLevel, $scopeId);
        $this->sma->send_json($data);
    }

    /**
     * Pharmacy Hierarchy Setup - UI for managing pharmacies, branches, and warehouses
     * Located under Settings -> Setup Organization
     */
    public function pharmacy_setup()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => '#', 'page' => lang('Pharmacy Hierarchy Setup')]
        ];
        $meta = ['page_title' => lang('Pharmacy Hierarchy Setup'), 'bc' => $bc];
        $this->page_construct('loyalty/pharmacy_setup', $meta, $this->data);
    }

    /**
     * API: Get all pharmacy groups for dropdown selection
     */
    public function get_pharmacy_groups()
    {
        $groups = $this->db->select('id, code, name')
            ->from('loyalty_pharmacy_groups')
            ->order_by('name', 'ASC')
            ->get()
            ->result_array();

        $this->sma->send_json([
            'success' => true,
            'data' => $groups
        ]);
    }

    /**
     * API: Get pharmacies by pharmacy group
     */
    public function get_pharmacies()
    {
        $group_id = $this->input->get('group_id');
        
        if (!$group_id) {
            $this->sma->send_json(['success' => false, 'message' => 'Pharmacy group ID required']);
            return;
        }

        $pharmacies = $this->db->select('sw.id, sw.code, sw.name, sw.address, sw.phone, sw.warehouse_type')
            ->from('sma_warehouses sw')
            ->join('loyalty_pharmacies lp', 'sw.id = lp.warehouse_id', 'left')
            ->where('lp.pharmacy_group_id', $group_id)
            ->where('sw.warehouse_type', 'pharmacy')
            ->order_by('sw.name', 'ASC')
            ->get()
            ->result_array();

        $this->sma->send_json([
            'success' => true,
            'data' => $pharmacies
        ]);
    }

    /**
     * API: Get all pharmacies (for dropdown in branches tab)
     */
    public function get_all_pharmacies()
    {
        $pharmacies = $this->db->select('sw.id, sw.code, sw.name')
            ->from('sma_warehouses sw')
            ->where('sw.warehouse_type', 'pharmacy')
            ->order_by('sw.name', 'ASC')
            ->get()
            ->result_array();

        $this->sma->send_json([
            'success' => true,
            'data' => $pharmacies
        ]);
    }

    /**
     * API: Get branches by pharmacy
     */
    public function get_branches()
    {
        $pharmacy_id = $this->input->get('pharmacy_id');
        
        if (!$pharmacy_id) {
            $this->sma->send_json(['success' => false, 'message' => 'Pharmacy ID required']);
            return;
        }

        $branches = $this->db->select('sw.id, sw.code, sw.name, sw.address, sw.phone, sw.warehouse_type, parent.name as pharmacy_name')
            ->from('sma_warehouses sw')
            ->join('sma_warehouses parent', 'sw.parent_id = parent.id', 'left')
            ->join('loyalty_branches lb', 'sw.id = lb.warehouse_id', 'left')
            ->where('lb.pharmacy_id', $pharmacy_id)
            ->where('sw.warehouse_type', 'branch')
            ->order_by('sw.name', 'ASC')
            ->get()
            ->result_array();

        $this->sma->send_json([
            'success' => true,
            'data' => $branches
        ]);
    }

    /**
     * API: Get hierarchy tree for visualization
     */
    public function get_hierarchy_tree()
    {
        // Get all pharmacy groups with their pharmacies and branches
        $groups = $this->db->select('id, code, name')
            ->from('loyalty_pharmacy_groups')
            ->order_by('name', 'ASC')
            ->get()
            ->result_array();

        $hierarchy = [];

        foreach ($groups as $group) {
            $group_data = $group;

            // Get pharmacies for this group
            $pharmacies = $this->db->select('sw.id, sw.code, sw.name')
                ->from('sma_warehouses sw')
                ->join('loyalty_pharmacies lp', 'sw.id = lp.warehouse_id', 'left')
                ->where('lp.pharmacy_group_id', $group['id'])
                ->where('sw.warehouse_type', 'pharmacy')
                ->get()
                ->result_array();

            $group_data['pharmacies'] = [];

            foreach ($pharmacies as $pharmacy) {
                $pharmacy_data = $pharmacy;

                // Get branches for this pharmacy
                $branches = $this->db->select('sw.id, sw.code, sw.name')
                    ->from('sma_warehouses sw')
                    ->join('loyalty_branches lb', 'sw.id = lb.warehouse_id', 'left')
                    ->where('lb.pharmacy_id', $pharmacy['id'])
                    ->where('sw.warehouse_type', 'branch')
                    ->get()
                    ->result_array();

                $pharmacy_data['branches'] = $branches;
                $group_data['pharmacies'][] = $pharmacy_data;
            }

            $hierarchy[] = $group_data;
        }

        $this->sma->send_json([
            'success' => true,
            'data' => $hierarchy
        ]);
    }

    /**
     * API: Add pharmacy with warehouse
     * Creates entries in: sma_warehouses (pharmacy), loyalty_pharmacies, sma_warehouses (mainwarehouse)
     */
    public function add_pharmacy_setup()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->form_validation->set_rules('pharmacy_group_id', 'Pharmacy Group', 'required|numeric');
        $this->form_validation->set_rules('code', 'Pharmacy Code', 'required|is_unique[sma_warehouses.code]');
        $this->form_validation->set_rules('name', 'Pharmacy Name', 'required');
        $this->form_validation->set_rules('address', 'Address', 'required');
        $this->form_validation->set_rules('phone', 'Phone', 'required');
        $this->form_validation->set_rules('warehouse_code', 'Warehouse Code', 'required|is_unique[sma_warehouses.code]');
        $this->form_validation->set_rules('warehouse_name', 'Warehouse Name', 'required');

        if (!$this->form_validation->run()) {
            $this->sma->send_json([
                'success' => false,
                'message' => validation_errors()
            ]);
            return;
        }

        $this->db->trans_start();

        try {
            // 1. Create pharmacy warehouse entry (parent warehouse)
            $pharmacy_warehouse_data = [
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'address' => $this->input->post('address'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email') ?: '',
                'warehouse_type' => 'pharmacy',
                'country' => 8, // Default to Saudi Arabia, can be made dynamic
                'parent_id' => null
            ];

            $this->db->insert('sma_warehouses', $pharmacy_warehouse_data);
            $pharmacy_warehouse_id = $this->db->insert_id();

            // 2. Create main warehouse entry (child of pharmacy)
            $main_warehouse_data = [
                'code' => $this->input->post('warehouse_code'),
                'name' => $this->input->post('warehouse_name'),
                'address' => $this->input->post('address'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email') ?: '',
                'warehouse_type' => 'mainwarehouse',
                'country' => 8,
                'parent_id' => $pharmacy_warehouse_id
            ];

            $this->db->insert('sma_warehouses', $main_warehouse_data);
            $main_warehouse_id = $this->db->insert_id();

            // 3. Create loyalty_pharmacies entry
            $loyalty_pharmacy_data = [
                'pharmacy_group_id' => $this->input->post('pharmacy_group_id'),
                'warehouse_id' => $pharmacy_warehouse_id,
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('loyalty_pharmacies', $loyalty_pharmacy_data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $this->sma->send_json([
                    'success' => false,
                    'message' => 'Failed to create pharmacy. Please try again.'
                ]);
                return;
            }

            $this->sma->send_json([
                'success' => true,
                'message' => 'Pharmacy created successfully',
                'data' => [
                    'pharmacy_id' => $pharmacy_warehouse_id,
                    'warehouse_id' => $main_warehouse_id
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->sma->send_json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Add branch
     * Creates entries in: sma_warehouses (branch), loyalty_branches
     */
    public function add_branch_setup()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->form_validation->set_rules('pharmacy_id', 'Pharmacy', 'required|numeric');
        $this->form_validation->set_rules('code', 'Branch Code', 'required|is_unique[sma_warehouses.code]');
        $this->form_validation->set_rules('name', 'Branch Name', 'required');
        $this->form_validation->set_rules('address', 'Address', 'required');
        $this->form_validation->set_rules('phone', 'Phone', 'required');

        if (!$this->form_validation->run()) {
            $this->sma->send_json([
                'success' => false,
                'message' => validation_errors()
            ]);
            return;
        }

        $this->db->trans_start();

        try {
            // 1. Create branch warehouse entry (child of pharmacy)
            $branch_warehouse_data = [
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'address' => $this->input->post('address'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email') ?: '',
                'warehouse_type' => 'branch',
                'country' => 8,
                'parent_id' => $this->input->post('pharmacy_id')
            ];

            $this->db->insert('sma_warehouses', $branch_warehouse_data);
            $branch_warehouse_id = $this->db->insert_id();

            // 2. Create loyalty_branches entry
            $loyalty_branch_data = [
                'pharmacy_id' => $this->input->post('pharmacy_id'),
                'warehouse_id' => $branch_warehouse_id,
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert('loyalty_branches', $loyalty_branch_data);

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $this->sma->send_json([
                    'success' => false,
                    'message' => 'Failed to create branch. Please try again.'
                ]);
                return;
            }

            $this->sma->send_json([
                'success' => true,
                'message' => 'Branch created successfully',
                'data' => [
                    'branch_id' => $branch_warehouse_id
                ]
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->sma->send_json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Delete pharmacy
     */
    public function delete_pharmacy()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id');

        if (!$id) {
            $this->sma->send_json(['success' => false, 'message' => 'Pharmacy ID required']);
            return;
        }

        $this->db->trans_start();

        try {
            // Delete related records
            $this->db->delete('loyalty_pharmacies', ['warehouse_id' => $id]);
            $this->db->delete('sma_warehouses', ['id' => $id]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $this->sma->send_json([
                    'success' => false,
                    'message' => 'Failed to delete pharmacy.'
                ]);
                return;
            }

            $this->sma->send_json([
                'success' => true,
                'message' => 'Pharmacy deleted successfully'
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->sma->send_json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Delete branch
     */
    public function delete_branch()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id');

        if (!$id) {
            $this->sma->send_json(['success' => false, 'message' => 'Branch ID required']);
            return;
        }

        $this->db->trans_start();

        try {
            // Delete related records
            $this->db->delete('loyalty_branches', ['warehouse_id' => $id]);
            $this->db->delete('sma_warehouses', ['id' => $id]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $this->sma->send_json([
                    'success' => false,
                    'message' => 'Failed to delete branch.'
                ]);
                return;
            }

            $this->sma->send_json([
                'success' => true,
                'message' => 'Branch deleted successfully'
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->sma->send_json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Budget Definition View
     */
    public function budget_definition()
    {
        $this->data['allocations'] = $this->loyalty_model->getAllBudgetAllocations();
        $this->data['summary'] = $this->loyalty_model->getBudgetSummary('company', 1);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['company_id'] = $this->loyalty_model->getCompanyId();
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => '#', 'page' => lang('Budget Definition')]
        ];
        $meta = ['page_title' => lang('Budget Definition'), 'bc' => $bc];
        $this->page_construct('loyalty/budget_definition', $meta, $this->data);
    }

    /**
     * Save Budget Allocation
     * TODO: Implement proper budget allocation save logic once API is ready
     */
    public function save_budget()
    {
        // For now, just redirect back to budget_definition
        $this->session->set_flashdata('info', 'Budget save functionality will be available soon');
        redirect('admin/loyalty/budget_definition');
    }

    /**
     * Budget Distribution View
     */
    public function budget_distribution($allocationId = null)
    {
        // Use sample data from model
        $this->data['allocations'] = $this->loyalty_model->getAllBudgetAllocations();
        $this->data['distributions'] = $this->loyalty_model->getPharmacyBreakdown('month');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
                $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => '#', 'page' => lang('Budget Distribution')]
        ];
        $meta = ['page_title' => lang('Budget Distribution'), 'bc' => $bc];
        $this->page_construct('loyalty/budget_distribution', $meta, $this->data);
    }

    /**
     * Save Budget Distribution
     * TODO: Implement proper distribution save logic once API is ready
     */
    public function save_distribution()
    {
        if (!$this->input->is_ajax_request()) {
            $this->sma->send_json(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        // For now, just return success
        $this->sma->send_json(['success' => true, 'message' => 'Distribution save functionality will be available soon']);
    }

    /**
     * Rules Management View
     */
    public function rules_management()
    {
        // Display sample rules data
        $this->data['rules'] = [
            ['id' => 1, 'rule_name' => 'Loyalty Points 5%', 'rule_code' => 'POINTS_5', 'rule_type' => 'loyalty_points', 'priority' => 1, 'status' => 'active'],
            ['id' => 2, 'rule_name' => 'Seasonal Discount', 'rule_code' => 'SEASONAL', 'rule_type' => 'promotion_discount', 'priority' => 2, 'status' => 'active'],
        ];
        $this->data['warehouses'] = $this->db->get('sma_warehouses')->result_array();
        $this->data['loyalty_stages'] = [];
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => '#', 'page' => lang('Rules Management')]
        ];
        $meta = ['page_title' => lang('Rules Management'), 'bc' => $bc];
        $this->page_construct('loyalty/rules_management', $meta, $this->data);
    }

    /**
     * Burn Rate Dashboard
     */
    public function burn_rate()
    {
        $period = $this->input->get('period') ?? 'week';

        $this->data['summary'] = $this->loyalty_model->getBurnRateSummary($period);
        $this->data['daily_burn_data'] = $this->loyalty_model->getDailyBurnTrendData($period);
        $this->data['burn_rate_trend'] = $this->loyalty_model->getBurnRateTrendData($period);
        $this->data['pharmacy_breakdown'] = $this->loyalty_model->getPharmacyBreakdown($period);
        $this->data['forecast_data'] = $this->loyalty_model->getForecastData($period);
        $this->data['alerts'] = $this->loyalty_model->getActiveAlerts();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => '#', 'page' => lang('Burn Rate Dashboard')]
        ];
        $meta = ['page_title' => lang('Burn Rate Dashboard'), 'bc' => $bc];
        $this->page_construct('loyalty/burn_rate_dashboard', $meta, $this->data);
    }

    /**
     * Pharmacy Detail View
     */
    public function pharmacy_detail($pharmacyId = null)
    {
        if (!$pharmacyId) {
            redirect('admin/loyalty/burn_rate');
        }

        $this->data['pharmacy'] = $this->db->where('id', $pharmacyId)->get('sma_warehouses')->row_array();
        if (!$this->data['pharmacy']) {
            $this->session->set_flashdata('error', 'Pharmacy not found');
            redirect('admin/loyalty/burn_rate');
        }

        $this->data['spending_history'] = $this->loyalty_model->getPharmacySpendingHistory($pharmacyId);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('loyalty'), 'page' => lang('Loyalty')],
            ['link' => admin_url('loyalty/burn_rate'), 'page' => lang('Burn Rate Dashboard')],
            ['link' => '#', 'page' => $this->data['pharmacy']['warehouse_name']]
        ];
        $meta = ['page_title' => $this->data['pharmacy']['warehouse_name'], 'bc' => $bc];
        $this->page_construct('loyalty/pharmacy_detail', $meta, $this->data);
    }
}