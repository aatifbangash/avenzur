<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pharmacist extends MY_Controller
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
        $this->load->admin_model('pharmacist_model');
    }

    /**
     * Main Pharmacist Management View
     */
    public function index()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => '#', 'page' => 'Pharmacist Management']
        ];
        $meta = ['page_title' => 'Pharmacist Management', 'bc' => $bc];
        $this->page_construct('pharmacist/index', $meta, $this->data);
    }

    /**
     * AJAX: Get all pharmacies
     */
    public function get_pharmacies()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $pharmacies = $this->pharmacist_model->get_all_pharmacies();

        $this->sma->send_json([
            'success' => true,
            'pharmacies' => $pharmacies
        ]);
    }

    /**
     * AJAX: Get branches by pharmacy ID
     */
    public function get_branches_by_pharmacy()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $pharmacy_id = $this->input->post('pharmacy_id');

        if (!$pharmacy_id) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Pharmacy ID is required'
            ]);
            return;
        }

        $branches = $this->pharmacist_model->get_branches_by_pharmacy($pharmacy_id);

        $this->sma->send_json([
            'success' => true,
            'branches' => $branches
        ]);
    }

    /**
     * AJAX: Get pharmacists by branch code
     */
    public function get_pharmacists_by_branch()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $branch_id = $this->input->post('branch_id');

        if (!$branch_id) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Branch ID is required'
            ]);
            return;
        }

        $pharmacists = $this->pharmacist_model->get_pharmacists_by_branch($branch_id);

        $this->sma->send_json([
            'success' => true,
            'pharmacists' => $pharmacists
        ]);
    }

    /**
     * Add Incentive for Pharmacist
     */
    public function add_incentive($pharmacist_id = null)
    {
        if (!$pharmacist_id) {
            $this->session->set_flashdata('error', 'Pharmacist ID is required');
            admin_redirect('pharmacist');
        }

        $pharmacist = $this->pharmacist_model->get_pharmacist_by_id($pharmacist_id);
        
        if (!$pharmacist) {
            $this->session->set_flashdata('error', 'Pharmacist not found');
            admin_redirect('pharmacist');
        }

        // Check if pharmacist already has an incentive
        $existing_incentives = $this->pharmacist_model->get_pharmacist_incentives($pharmacist_id);
        
        if (!empty($existing_incentives)) {
            $this->session->set_flashdata('error', 'This pharmacist already has an incentive. Please edit the existing one instead.');
            admin_redirect('pharmacist/edit_incentive/' . $pharmacist_id);
            return;
        }

        $this->data['pharmacist'] = $pharmacist;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('pharmacist'), 'page' => 'Pharmacist Management'],
            ['link' => '#', 'page' => 'Add Incentive']
        ];
        $meta = ['page_title' => 'Add Incentive', 'bc' => $bc];
        $this->page_construct('pharmacist/add_incentive', $meta, $this->data);
    }

    /**
     * Edit Incentive for Pharmacist
     * Can accept either incentive_id or pharmacist_id
     * If pharmacist_id is provided, loads the most recent incentive
     */
    public function edit_incentive($id = null)
    {
        if (!$id) {
            $this->session->set_flashdata('error', 'ID is required');
            admin_redirect('pharmacist');
        }

        // Check if this is a pharmacist_id or incentive_id
        // First, try to get as incentive_id
        $incentive = $this->pharmacist_model->get_incentive_by_id($id);
        
        // If not found, assume it's a pharmacist_id and get their most recent incentive
        if (!$incentive) {
            $incentives = $this->pharmacist_model->get_pharmacist_incentives($id);
            
            if (empty($incentives)) {
                $this->session->set_flashdata('error', 'No incentives found for this pharmacist. Please add an incentive first.');
                admin_redirect('pharmacist');
            }
            
            // Get the most recent incentive
            $incentive = $this->pharmacist_model->get_incentive_by_id($incentives[0]->id);
        }
        
        if (!$incentive) {
            $this->session->set_flashdata('error', 'Incentive not found');
            admin_redirect('pharmacist');
        }

        // Get pharmacist details
        $pharmacist = $this->pharmacist_model->get_pharmacist_by_id($incentive->pharmacist_id);
        
        if (!$pharmacist) {
            $this->session->set_flashdata('error', 'Pharmacist not found');
            admin_redirect('pharmacist');
        }

        // Get incentive items
        $items = $this->pharmacist_model->get_incentive_items($incentive->id);

        $this->data['incentive'] = $incentive;
        $this->data['pharmacist'] = $pharmacist;
        $this->data['items'] = $items;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        
        $bc = [
            ['link' => admin_url(), 'page' => lang('home')],
            ['link' => admin_url('pharmacist'), 'page' => 'Pharmacist Management'],
            ['link' => '#', 'page' => 'Edit Incentive']
        ];
        $meta = ['page_title' => 'Edit Incentive', 'bc' => $bc];
        $this->page_construct('pharmacist/edit_incentive', $meta, $this->data);
    }

    /**
     * AJAX: Search products for incentive
     */
    public function suggestions()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $term = $this->input->get('term', TRUE);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed  = $this->sma->analyze_term($term);
        $sr        = $analyzed['term'];
        $limit     = 10;

        $this->load->admin_model('products_model');
        $rows = $this->products_model->getProductNames($sr, $limit);

        if ($rows) {
            $c = str_replace(".", "", microtime(true));
            $r = 0;
            foreach ($rows as $row) {
                $c++;
                $option = [
                    'id'   => ($row->id . '_' . $c),
                    'item_id' => $row->id,
                    'label' => $row->name . ' (' . $row->code . ')',
                    'value' => $row->name,
                    'code' => $row->code,
                    'name' => $row->name,
                ];
                $options[] = $option;
                $r++;
            }
            echo json_encode($options);
        } else {
            echo json_encode([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    /**
     * AJAX: Get product details for incentive
     */
    public function get_product_details()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $product_id = $this->input->post('product_id');

        if (!$product_id) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Product ID is required'
            ]);
            return;
        }

        $this->load->admin_model('products_model');
        $product = $this->products_model->getProductByID($product_id);

        if (!$product) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Product not found'
            ]);
            return;
        }

        // Get latest batch number and expiry date from purchase items
        $purchase_item = $this->pharmacist_model->get_latest_purchase_item($product_id);
        
        if ($purchase_item) {
            $product->batch_number = $purchase_item->batchno;
            $product->expiry_date = $purchase_item->expiry;
        } else {
            $product->batch_number = '';
            $product->expiry_date = '';
        }

        $this->sma->send_json([
            'success' => true,
            'product' => $product
        ]);
    }

    /**
     * AJAX: Get product batches by product code
     */
    public function get_product_batches()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $product_code = $this->input->get('product_code');
        $warehouse_id = $this->input->get('warehouse_id');

        if (!$product_code) {
            echo json_encode([]);
            return;
        }

        // Get product by code
        $this->load->admin_model('products_model');
        $this->db->select('id, code, name');
        $this->db->from('sma_products');
        $this->db->where('code', $product_code);
        $product = $this->db->get()->row();

        if (!$product) {
            echo json_encode([]);
            return;
        }

        // Get all batches for this product from inventory movements
        $this->db->select("
            im.avz_item_code,
            im.product_id,
            pr.name as product_name,
            pr.code as product_code,
            im.batch_number as batchno,
            im.expiry_date as expiry,
            p.supplier_id,
            p.supplier,
            SUM(IFNULL(im.quantity, 0)) as total_quantity
        ", false);
        $this->db->from('sma_inventory_movements im');
        $this->db->join('sma_purchases p', 'p.id = im.reference_id AND im.type = "purchase"', 'left');
        $this->db->join('sma_products pr', 'pr.id = im.product_id', 'left');
        
        if ($warehouse_id) {
            $this->db->where('im.location_id', $warehouse_id);
        }
        
        $this->db->where('im.product_id', $product->id);
        $this->db->group_by(['im.avz_item_code', 'im.batch_number', 'im.expiry_date']);
        $this->db->having('total_quantity !=', 0);
        $this->db->order_by('im.expiry_date', 'ASC');
        
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $rows = $query->result();
            $data = [];
            
            foreach ($rows as $row) {
                $data[] = [
                    'row' => $row,
                    'total_quantity' => $row->total_quantity
                ];
            }
            
            echo json_encode($data);
        } else {
            echo json_encode([]);
        }
    }

    /**
     * AJAX: Save pharmacist incentive
     */
    public function save_incentive()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Get POST data
        $pharmacist_id = $this->input->post('pharmacist_id');
        $branch_code = $this->input->post('branch_code');
        $warehouse_id = $this->input->post('warehouse_id');
        $items = $this->input->post('items'); // Array of incentive items

        // Validate required fields
        if (!$pharmacist_id || empty($items)) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Pharmacist ID and items are required'
            ]);
            return;
        }

        // Validate items array
        if (!is_array($items) || count($items) == 0) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'At least one item is required'
            ]);
            return;
        }

        // Start transaction
        $this->db->trans_start();

        try {
            // Prepare incentive header data
            $incentive_data = [
                'pharmacist_id' => $pharmacist_id,
                'branch_code' => $branch_code,
                'warehouse_id' => $warehouse_id,
                'status' => 'active',
                'created_by' => $this->session->userdata('user_id'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Insert incentive header
            $incentive_id = $this->pharmacist_model->save_incentive($incentive_data);

            if (!$incentive_id) {
                throw new Exception('Failed to save incentive header');
            }

            // Prepare incentive items
            $incentive_items = [];
            foreach ($items as $item) {
                // Validate item data
                if (!isset($item['product_id']) || !isset($item['incentive_percentage'])) {
                    continue; // Skip invalid items
                }

                $incentive_items[] = [
                    'incentive_id' => $incentive_id,
                    'product_id' => $item['product_id'],
                    'batch_number' => isset($item['batch_number']) ? $item['batch_number'] : null,
                    'expiry_date' => isset($item['expiry_date']) ? $item['expiry_date'] : null,
                    'supplier_id' => isset($item['supplier_id']) ? $item['supplier_id'] : null,
                    'incentive_percentage' => $item['incentive_percentage'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            // Insert incentive items
            if (!empty($incentive_items)) {
                $items_saved = $this->pharmacist_model->save_incentive_items($incentive_items);
                
                if (!$items_saved) {
                    throw new Exception('Failed to save incentive items');
                }
            }

            // Complete transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $this->sma->send_json([
                'success' => true,
                'message' => 'Incentive saved successfully',
                'incentive_id' => $incentive_id
            ]);

        } catch (Exception $e) {
            // Rollback on error
            $this->db->trans_rollback();
            
            $this->sma->send_json([
                'success' => false,
                'message' => 'Failed to save incentive: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Update pharmacist incentive
     */
    public function update_incentive()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        // Get POST data
        $incentive_id = $this->input->post('incentive_id');
        $pharmacist_id = $this->input->post('pharmacist_id');
        $branch_code = $this->input->post('branch_code');
        $warehouse_id = $this->input->post('warehouse_id');
        $items = $this->input->post('items'); // Array of incentive items

        // Validate required fields
        if (!$incentive_id || !$pharmacist_id || empty($items)) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Incentive ID, Pharmacist ID and items are required'
            ]);
            return;
        }

        // Validate items array
        if (!is_array($items) || count($items) == 0) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'At least one item is required'
            ]);
            return;
        }

        // Start transaction
        $this->db->trans_start();

        try {
            // Prepare incentive header update data
            $incentive_data = [
                'pharmacist_id' => $pharmacist_id,
                'branch_code' => $branch_code,
                'warehouse_id' => $warehouse_id,
                'status' => 'active',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Update incentive header
            $updated = $this->pharmacist_model->update_incentive($incentive_id, $incentive_data);

            if (!$updated) {
                throw new Exception('Failed to update incentive header');
            }

            // Delete existing items
            $this->pharmacist_model->delete_incentive_items($incentive_id);

            // Prepare new incentive items
            $incentive_items = [];
            foreach ($items as $item) {
                // Validate item data
                if (!isset($item['product_id']) || !isset($item['incentive_percentage'])) {
                    continue; // Skip invalid items
                }

                $incentive_items[] = [
                    'incentive_id' => $incentive_id,
                    'product_id' => $item['product_id'],
                    'batch_number' => isset($item['batch_number']) ? $item['batch_number'] : null,
                    'expiry_date' => isset($item['expiry_date']) ? $item['expiry_date'] : null,
                    'supplier_id' => isset($item['supplier_id']) ? $item['supplier_id'] : null,
                    'incentive_percentage' => $item['incentive_percentage'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }

            // Insert new incentive items
            if (!empty($incentive_items)) {
                $items_saved = $this->pharmacist_model->save_incentive_items($incentive_items);
                
                if (!$items_saved) {
                    throw new Exception('Failed to save incentive items');
                }
            }

            // Complete transaction
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed');
            }

            $this->sma->send_json([
                'success' => true,
                'message' => 'Incentive updated successfully',
                'incentive_id' => $incentive_id
            ]);

        } catch (Exception $e) {
            // Rollback on error
            $this->db->trans_rollback();
            
            $this->sma->send_json([
                'success' => false,
                'message' => 'Failed to update incentive: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download pharmacist incentive as CSV
     */
    public function download_incentive_csv($pharmacist_id = null)
    {
        if (!$pharmacist_id) {
            $this->session->set_flashdata('error', 'Pharmacist ID is required');
            admin_redirect('pharmacist');
        }

        // Get pharmacist details
        $pharmacist = $this->pharmacist_model->get_pharmacist_by_id($pharmacist_id);
        
        if (!$pharmacist) {
            $this->session->set_flashdata('error', 'Pharmacist not found');
            admin_redirect('pharmacist');
        }

        // Get pharmacist's most recent incentive
        $incentives = $this->pharmacist_model->get_pharmacist_incentives($pharmacist_id);
        
        if (empty($incentives)) {
            $this->session->set_flashdata('error', 'No incentives found for this pharmacist');
            admin_redirect('pharmacist');
        }

        $incentive = $this->pharmacist_model->get_incentive_by_id($incentives[0]->id);
        $items = $this->pharmacist_model->get_incentive_items($incentives[0]->id);

        if (empty($items)) {
            $this->session->set_flashdata('error', 'No items found in this incentive');
            admin_redirect('pharmacist');
        }

        // Prepare CSV filename
        $filename = 'pharmacist_incentive_' . $pharmacist->username . '_' . date('Y-m-d') . '.csv';

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Create file pointer connected to output stream
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Add CSV headers
        fputcsv($output, [
            'Product Code',
            'Product Name',
            'Batch Number',
            'Expiry Date',
            'Supplier ID',
            'Incentive %'
        ]);

        // Add data rows
        foreach ($items as $item) {
            fputcsv($output, [
                $item->product_code,
                $item->product_name,
                $item->batch_number ?: '',
                $item->expiry_date ?: '',
                $item->supplier_id ?: '',
                $item->incentive_percentage
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * AJAX: Check if pharmacist has incentive
     */
    public function has_incentive()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $pharmacist_id = $this->input->post('pharmacist_id');

        if (!$pharmacist_id) {
            $this->sma->send_json([
                'success' => false,
                'has_incentive' => false
            ]);
            return;
        }

        $incentives = $this->pharmacist_model->get_pharmacist_incentives($pharmacist_id);

        $this->sma->send_json([
            'success' => true,
            'has_incentive' => !empty($incentives),
            'incentive_count' => count($incentives)
        ]);
    }

    /**
     * AJAX: Delete pharmacist incentive
     */
    public function delete_incentive()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $pharmacist_id = $this->input->post('pharmacist_id');

        if (!$pharmacist_id) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Pharmacist ID is required'
            ]);
            return;
        }

        // Get pharmacist's incentives
        $incentives = $this->pharmacist_model->get_pharmacist_incentives($pharmacist_id);

        if (empty($incentives)) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'No incentive found for this pharmacist'
            ]);
            return;
        }

        // Delete the incentive (will cascade delete items)
        $deleted = $this->pharmacist_model->delete_incentive($incentives[0]->id);

        if ($deleted) {
            $this->sma->send_json([
                'success' => true,
                'message' => 'Incentive deleted successfully'
            ]);
        } else {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Failed to delete incentive'
            ]);
        }
    }

    /**
     * AJAX: Get the appropriate warehouse ID for product search
     * If pharmacist's warehouse is a branch, return the parent pharmacy warehouse_id
     * Otherwise, return the original warehouse_id
     */
    public function get_warehouse_for_search()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $warehouse_id = $this->input->post('warehouse_id');

        if (!$warehouse_id) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Warehouse ID is required'
            ]);
            return;
        }

        // Get warehouse details
        $this->db->select('id, warehouse_type, parent_id');
        $this->db->from('warehouses');
        $this->db->where('id', $warehouse_id);
        $warehouse = $this->db->get()->row();

        if (!$warehouse) {
            $this->sma->send_json([
                'success' => false,
                'message' => 'Warehouse not found'
            ]);
            return;
        }

        // If warehouse type is "branch", get the parent pharmacy warehouse_id
        if ($warehouse->warehouse_type === 'branch' && $warehouse->parent_id) {
            // Get parent warehouse details
            $this->db->select('id');
            $this->db->from('warehouses');
            $this->db->where('id', $warehouse->parent_id);
            $parent_warehouse = $this->db->get()->row();

            if ($parent_warehouse) {
                $this->sma->send_json([
                    'success' => true,
                    'warehouse_id' => $parent_warehouse->id,
                    'original_warehouse_id' => $warehouse_id,
                    'warehouse_type' => 'branch',
                    'message' => 'Using parent pharmacy warehouse for search'
                ]);
                return;
            }
        }

        // If warehouse type is "pharmacy" or parent not found, use original warehouse_id
        $this->sma->send_json([
            'success' => true,
            'warehouse_id' => $warehouse_id,
            'warehouse_type' => $warehouse->warehouse_type,
            'message' => 'Using original warehouse for search'
        ]);
    }
}
