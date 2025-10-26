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
        $this->load->admin_model('ployalty_model');
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
        $this->page_construct('loyalty/rules', $meta, $this->data);
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
}
