<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Organization Setup Controller
 * 
 * Handles organization hierarchy setup including:
 * - Pharmacy Groups (Companies)
 * - Pharmacies
 * - Branches
 * - Warehouses
 * 
 * Location: Settings → Setup Organization
 */
class Organization_setup extends MY_Controller
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
     * Pharmacy Hierarchy Setup - Main view
     * Displays UI for managing pharmacies, branches, and warehouses
     */
    public function pharmacy_hierarchy()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('system_settings'), 'page' => lang('settings')],
            ['link' => '#', 'page' => lang('pharmacy_hierarchy_setup')]
        ];
        $meta = ['page_title' => lang('pharmacy_hierarchy_setup'), 'bc' => $bc];
        $this->page_construct('settings/pharmacy_hierarchy', $meta, $this->data);
    }

    /**
     * API: Get all pharmacy groups for dropdown selection
     */
    public function get_pharmacy_groups()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $query = "SELECT id, code, name FROM loyalty_pharmacy_groups ORDER BY name ASC";
        $groups = $this->db->query($query)->result_array();

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
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $group_id = $this->input->get('group_id');
        
        if (!$group_id) {
            $this->sma->send_json(['success' => false, 'message' => 'Pharmacy group ID required']);
            return;
        }

        // Get pharmacies from loyalty_pharmacies with warehouse details
        $query = "SELECT 
                    lp.id, 
                    lp.code, 
                    lp.name, 
                    lp.external_id,
                    COALESCE(sw.address, '') as address,
                    COALESCE(sw.phone, '') as phone,
                    COALESCE(sw.warehouse_type, 'pharmacy') as warehouse_type
                  FROM loyalty_pharmacies lp
                  LEFT JOIN sma_warehouses sw ON lp.external_id = sw.id
                  WHERE lp.pharmacy_group_id = ?
                  ORDER BY lp.name ASC";
        $pharmacies = $this->db->query($query, [$group_id])->result_array();

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
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Get all pharmacies from loyalty_pharmacies with warehouse details
        $query = "SELECT 
                    lp.id,
                    lp.code,
                    lp.name,
                    sw.address,
                    sw.phone
                  FROM loyalty_pharmacies lp
                  LEFT JOIN sma_warehouses sw ON lp.external_id = sw.id
                  ORDER BY lp.name ASC";
        $pharmacies = $this->db->query($query)->result_array();

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
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $pharmacy_id = $this->input->get('pharmacy_id');
        
        if (!$pharmacy_id) {
            $this->sma->send_json(['success' => false, 'message' => 'Pharmacy ID required']);
            return;
        }

        // Get branches with pharmacy and warehouse details
        $query = "SELECT 
                    lb.id,
                    lb.code,
                    lb.name,
                    lp.name as pharmacy_name,
                    COALESCE(sw.address, '') as address,
                    COALESCE(sw.phone, '') as phone
                  FROM loyalty_branches lb
                  LEFT JOIN loyalty_pharmacies lp ON lb.pharmacy_id = lp.id
                  LEFT JOIN sma_warehouses sw ON lb.external_id = sw.id
                  WHERE lb.pharmacy_id = ?
                  ORDER BY lb.name ASC";
        $branches = $this->db->query($query, [$pharmacy_id])->result_array();

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
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Get all pharmacy groups with their pharmacies and branches
        $query_groups = "SELECT id, code, name FROM loyalty_pharmacy_groups ORDER BY name ASC";
        $groups = $this->db->query($query_groups)->result_array();

        $hierarchy = [];

        foreach ($groups as $group) {
            $group_data = $group;

            // Get pharmacies for this group directly from loyalty_pharmacies
            $query_pharmacies = "SELECT id, code, name FROM loyalty_pharmacies
                                 WHERE pharmacy_group_id = ?
                                 ORDER BY name ASC";
            $pharmacies = $this->db->query($query_pharmacies, [$group['id']])->result_array();

            $group_data['pharmacies'] = [];

            foreach ($pharmacies as $pharmacy) {
                $pharmacy_data = $pharmacy;

                // Get branches for this pharmacy directly from loyalty_branches
                $query_branches = "SELECT id, code, name FROM loyalty_branches
                                   WHERE pharmacy_id = ?
                                   ORDER BY name ASC";
                $branches = $this->db->query($query_branches, [$pharmacy['id']])->result_array();

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
    public function add_pharmacy()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->form_validation->set_rules('pharmacy_group_id', 'Pharmacy Group', 'required');
        $this->form_validation->set_rules('code', 'Pharmacy Code', 'required|is_unique[sma_warehouses.code]');
        $this->form_validation->set_rules('name', 'Pharmacy Name', 'required');
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
            // STEP 1: Create pharmacy warehouse entry in sma_warehouses first
            $pharmacy_warehouse_data = [
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'address' => $this->input->post('address'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email') ?: '',
                'warehouse_type' => 'pharmacy',
                'country' => 8,
                'parent_id' => null
            ];

            $this->db->insert('sma_warehouses', $pharmacy_warehouse_data);
            $pharmacy_warehouse_id = $this->db->insert_id();
            
            // Verify warehouse was created
            if (!$pharmacy_warehouse_id) {
                throw new Exception('Failed to create pharmacy warehouse');
            }

            // STEP 2: Create loyalty_pharmacies entry using the warehouse ID
            $loyalty_pharmacy_data = [
                'id' => $this->sma->generateUUIDv4(),
                'pharmacy_group_id' => $this->input->post('pharmacy_group_id'),
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'external_id' => $pharmacy_warehouse_id,  // Link to warehouse
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->query("INSERT INTO loyalty_pharmacies (id, pharmacy_group_id, name, code, external_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                $loyalty_pharmacy_data['id'],
                $loyalty_pharmacy_data['pharmacy_group_id'],
                $loyalty_pharmacy_data['name'],
                $loyalty_pharmacy_data['code'],
                $loyalty_pharmacy_data['external_id'],
                $loyalty_pharmacy_data['created_at'],
                $loyalty_pharmacy_data['updated_at']
            ]);

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
                    'pharmacy_id' => $pharmacy_warehouse_id
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
     * API: Get pharmacy details for editing
     */
    public function get_pharmacy_details()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->get('id');
        
        if (!$id) {
            $this->sma->send_json(['success' => false, 'message' => 'Pharmacy ID required']);
            return;
        }

        // Get pharmacy from loyalty_pharmacies with warehouse details
        $query = "SELECT 
                    lp.id, 
                    lp.code, 
                    lp.name,
                    lp.pharmacy_group_id,
                    lp.external_id,
                    COALESCE(sw.address, '') as address,
                    COALESCE(sw.phone, '') as phone,
                    COALESCE(sw.email, '') as email
                  FROM loyalty_pharmacies lp
                  LEFT JOIN sma_warehouses sw ON lp.external_id = sw.id
                  WHERE lp.id = ?";
        $pharmacy = $this->db->query($query, [$id])->row_array();

        log_message('debug', 'Get pharmacy details query result: ' . json_encode($pharmacy));

        if (!$pharmacy) {
            $this->sma->send_json(['success' => false, 'message' => 'Pharmacy not found']);
            return;
        }

        $this->sma->send_json([
            'success' => true,
            'data' => $pharmacy
        ]);
    }

    /**
     * API: Update pharmacy
     * Updates entries in: sma_warehouses (pharmacy), loyalty_pharmacies
     */
    public function update_pharmacy()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $pharmacy_id = $this->input->post('id');
        if (!$pharmacy_id) {
            $this->sma->send_json(['success' => false, 'message' => 'Pharmacy ID required']);
            return;
        }

        $this->form_validation->set_rules('pharmacy_group_id', 'Pharmacy Group', 'required');
        $this->form_validation->set_rules('code', 'Pharmacy Code', 'required');
        $this->form_validation->set_rules('name', 'Pharmacy Name', 'required');
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
            // 1. Get existing pharmacy to find warehouse ID
            $query = "SELECT external_id FROM loyalty_pharmacies WHERE id = ?";
            $existing = $this->db->query($query, [$pharmacy_id])->row_array();
            
            if (!$existing) {
                throw new Exception('Pharmacy not found');
            }

            $warehouse_id = $existing['external_id'];

            // 2. Update pharmacy warehouse entry
            $pharmacy_warehouse_data = [
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'address' => $this->input->post('address'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email') ?: ''
            ];

            $this->db->where('id', $warehouse_id);
            $this->db->update('sma_warehouses', $pharmacy_warehouse_data);

            // 3. Update loyalty_pharmacies entry
            $this->db->query("UPDATE loyalty_pharmacies SET 
                                pharmacy_group_id = ?, 
                                name = ?, 
                                code = ?, 
                                updated_at = ?
                              WHERE id = ?", [
                $this->input->post('pharmacy_group_id'),
                $this->input->post('name'),
                $this->input->post('code'),
                date('Y-m-d H:i:s'),
                $pharmacy_id
            ]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $this->sma->send_json([
                    'success' => false,
                    'message' => 'Failed to update pharmacy. Please try again.'
                ]);
                return;
            }

            $this->sma->send_json([
                'success' => true,
                'message' => 'Pharmacy updated successfully'
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
    public function add_branch()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $this->form_validation->set_rules('pharmacy_id', 'Pharmacy', 'required');
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
            // 1. Get the pharmacy code from loyalty_pharmacies so we can find its warehouse ID
            $pharmacy_info = $this->db->query(
                "SELECT code FROM loyalty_pharmacies WHERE id = ?",
                [$this->input->post('pharmacy_id')]
            )->row();

            if (!$pharmacy_info) {
                throw new Exception('Pharmacy not found');
            }

            // 2. Get the pharmacy warehouse ID
            $pharmacy_warehouse = $this->db->query(
                "SELECT id FROM sma_warehouses WHERE code = ? AND warehouse_type = 'pharmacy'",
                [$pharmacy_info->code]
            )->row();

            if (!$pharmacy_warehouse) {
                throw new Exception('Pharmacy warehouse not found');
            }

            // 3. Create branch warehouse entry (child of pharmacy warehouse)
            $branch_warehouse_data = [
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'address' => $this->input->post('address'),
                'phone' => $this->input->post('phone'),
                'email' => $this->input->post('email') ?: '',
                'warehouse_type' => 'branch',
                'country' => 8,
                'parent_id' => $pharmacy_warehouse->id
            ];

            $this->db->insert('sma_warehouses', $branch_warehouse_data);
            $branch_warehouse_id = $this->db->insert_id();

            // 4. Create loyalty_branches entry
            // Note: loyalty_branches doesn't have warehouse_id column, stores separate from warehouses
            $loyalty_branch_data = [
                'id' => $this->sma->generateUUIDv4(),
                'pharmacy_id' => $this->input->post('pharmacy_id'),
                'code' => $this->input->post('code'),
                'name' => $this->input->post('name'),
                'external_id' => $branch_warehouse_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->query("INSERT INTO loyalty_branches (id, pharmacy_id, code, name, external_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)", [
                $loyalty_branch_data['id'],
                $loyalty_branch_data['pharmacy_id'],
                $loyalty_branch_data['code'],
                $loyalty_branch_data['name'],
                $loyalty_branch_data['external_id'],
                $loyalty_branch_data['created_at'],
                $loyalty_branch_data['updated_at']
            ]);

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
            // 1. Get pharmacy details
            $pharmacy = $this->db->query("SELECT id, external_id, code FROM loyalty_pharmacies WHERE id = ?", [$id])->row();

            if (!$pharmacy) {
                throw new Exception('Pharmacy not found');
            }

            $pharmacy_warehouse_id = $pharmacy->external_id;

            // 2. First, delete loyalty_branches (they reference this pharmacy via pharmacy_id)
            // This must happen BEFORE deleting the pharmacy from loyalty_pharmacies due to foreign keys
            $this->db->query("DELETE FROM loyalty_branches WHERE pharmacy_id = ?", [$id]);

            // 3. Delete branch warehouses from sma_warehouses
            $this->db->query(
                "DELETE FROM sma_warehouses WHERE parent_id = ? AND warehouse_type = 'branch'",
                [$pharmacy_warehouse_id]
            );

            // 4. Delete the pharmacy from loyalty_pharmacies
            $this->db->query("DELETE FROM loyalty_pharmacies WHERE id = ?", [$id]);

            // 5. Delete the pharmacy warehouse from sma_warehouses
            $this->db->query("DELETE FROM sma_warehouses WHERE id = ?", [$pharmacy_warehouse_id]);

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                throw new Exception('Transaction failed');
            }

            $this->sma->send_json([
                'success' => true,
                'message' => 'Pharmacy deleted successfully'
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Delete pharmacy error: ' . $e->getMessage());
            $this->sma->send_json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API: Get branch details for editing
     */
    public function get_branch_details()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->get('id');
        if (!$id) {
            $this->sma->send_json(['success' => false, 'message' => 'Branch ID required']);
            return;
        }

        // Get branch details from loyalty_branches and warehouse info
        $query = "SELECT 
                    lb.id,
                    lb.code,
                    lb.name,
                    lb.pharmacy_id,
                    lp.name as pharmacy_name,
                    COALESCE(sw.address, '') as address,
                    COALESCE(sw.phone, '') as phone,
                    COALESCE(sw.email, '') as email
                  FROM loyalty_branches lb
                  LEFT JOIN loyalty_pharmacies lp ON lb.pharmacy_id = lp.id
                  LEFT JOIN sma_warehouses sw ON lb.external_id = sw.id
                  WHERE lb.id = ?";
        
        $branch = $this->db->query($query, [$id])->row_array();

        if (!$branch) {
            $this->sma->send_json(['success' => false, 'message' => 'Branch not found']);
            return;
        }

        $this->sma->send_json([
            'success' => true,
            'data' => $branch
        ]);
    }

    /**
     * API: Update branch
     */
    public function update_branch()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $id = $this->input->post('id');
        $pharmacy_id = $this->input->post('pharmacy_id');
        $code = $this->input->post('code');
        $name = $this->input->post('name');
        $address = $this->input->post('address');
        $phone = $this->input->post('phone');
        $email = $this->input->post('email');

        // Validate required fields
        if (!$id || !$pharmacy_id || !$code || !$name || !$address || !$phone) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'All required fields must be filled'
            ]);
            return;
        }

        $this->db->trans_start();

        try {
            // 1. Get current branch details to find external_id
            $branch = $this->db->query("SELECT external_id FROM loyalty_branches WHERE id = ?", [$id])->row();

            if (!$branch) {
                throw new Exception('Branch not found');
            }

            // 2. Update loyalty_branches table
            $this->db->query("UPDATE loyalty_branches 
                              SET code = ?, name = ?, pharmacy_id = ?
                              WHERE id = ?", 
                             [$code, $name, $pharmacy_id, $id]);

            // 3. Update warehouse (sma_warehouses) table
            if ($branch->external_id) {
                $this->db->query("UPDATE sma_warehouses 
                                  SET code = ?, name = ?, address = ?, phone = ?, email = ?
                                  WHERE id = ?", 
                                 [$code, $name, $address, $phone, $email, $branch->external_id]);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === false) {
                $this->sma->send_json([
                    'success' => false,
                    'message' => 'Failed to update branch.'
                ]);
                return;
            }

            $this->sma->send_json([
                'success' => true,
                'message' => 'Branch updated successfully'
            ]);

        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Update branch error: ' . $e->getMessage());
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
            // 1. Get branch code before deletion
            $branch = $this->db->query("SELECT code FROM loyalty_branches WHERE id = ?", [$id])->row();

            if (!$branch) {
                throw new Exception('Branch not found');
            }

            // 2. Delete branch from loyalty_branches table
            $this->db->query("DELETE FROM loyalty_branches WHERE id = ?", [$id]);

            // 3. Delete corresponding warehouse entry (branch warehouse)
            $this->db->query("DELETE FROM sma_warehouses WHERE code = ? AND warehouse_type = 'branch'", [$branch->code]);

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
