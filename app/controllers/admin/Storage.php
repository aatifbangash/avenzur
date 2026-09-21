<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Storage extends MY_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->admin_model('StorageLocation_model');
        $this->load->admin_model('ProductLocation_model');
        $this->load->admin_model('products_model'); // Assume you have a products model
        $this->load->library('form_validation');
        // /$this->load->database();
    }

    // ----------------------------
    // View all storage locations
    // ----------------------------
    public function index(){
        //$this->sma->checkPermissions();
        
        // Get warehouse_id from query parameter or session/user default
        $warehouse_id = $this->input->get('warehouse_id') ?: $this->session->userdata('warehouse_id');
        
        // Get all warehouses for dropdown
        $this->data['warehouses'] = $this->get_warehouses();
        $this->data['selected_warehouse'] = $warehouse_id;
        
        // Get hierarchy filtered by warehouse
        $this->data['hierarchy'] = $this->StorageLocation_model->get_hierarchy_with_details(null, $warehouse_id);

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->load->helper('string');
        $value = random_string('alnum', 20);
        $this->session->set_userdata('user_csrf', $value);
        $this->data['csrf'] = $this->session->userdata('user_csrf');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('storage'), 'page' => lang('storage_locations')], ['link' => '#', 'page' => lang('add_storage_location')]];
        $meta = ['page_title' => lang('storage_locations'), 'bc' => $bc];
        $this->page_construct('storage/index', $meta, $this->data);
    }

    // ----------------------------
    // Add new location or assign product
    // ----------------------------
    public function add(){
        //$this->sma->checkPermissions();

        if($this->input->post()){
            $type = $this->input->post('type');
            $name = $this->input->post('name');
            $parent_id = $this->input->post('parent_id') ?: null;
            $warehouse_id = $this->input->post('warehouse_id');
            $product_id = $this->input->post('product_id');
            $quantity = $this->input->post('quantity') ?: 0;
            $capacity = $this->input->post('capacity') ?: null;

            $this->form_validation->set_rules('name', 'Name', 'required');
            $this->form_validation->set_rules('type', 'Type', 'required|in_list[rack,level,shelf,box]');
            $this->form_validation->set_rules('warehouse_id', 'Warehouse', 'required');

            if($this->form_validation->run() == true){
                // Add storage location
                $location_id = $this->StorageLocation_model->insert([
                    'name'=>$name,
                    'type'=>$type,
                    'parent_id'=>$parent_id,
                    'warehouse_id'=>$warehouse_id,
                    'capacity'=> $capacity
                ]);

                // Assign product if provided
                if($product_id){
                    $this->ProductLocation_model->insert([
                        'product_id'=>$product_id,
                        'storage_location_id'=>$location_id,
                        'quantity'=>$quantity
                    ]);
                }

                $this->session->set_flashdata('message', 'Storage location added successfully.');
                redirect(admin_url('storage?warehouse_id=' . $warehouse_id));
            }
        }

        // Get warehouse_id from query or session
        $warehouse_id = $this->input->get('warehouse_id') ?: $this->session->userdata('warehouse_id');
        
        // Get all warehouses for dropdown
        $this->data['warehouses'] = $this->get_warehouses();
        $this->data['selected_warehouse'] = $warehouse_id;
        
        // Get hierarchy for dropdowns filtered by warehouse
        $this->data['hierarchy'] = $this->StorageLocation_model->get_hierarchy(null, $warehouse_id);
        $this->data['products'] = $this->products_model->getAllProducts();
        $this->page_construct('storage/add', ['page_title'=>'Add Storage Location'], $this->data);
    }

    // ----------------------------
    // AJAX lookup product by avz_code
    // ----------------------------

    public function lookup(){
        $this->sma->checkPermissions();
        
        // Get all warehouses
        $warehouses = $this->db->select('id, code, name')->get('sma_warehouses')->result();
        
        // Get selected warehouse from query param or session
        $selected_warehouse = $this->input->get('warehouse_id') 
            ? $this->input->get('warehouse_id') 
            : $this->session->userdata('storage_warehouse_id');
        
        // If no selection and only one warehouse, auto-select it
        if (!$selected_warehouse && count($warehouses) == 1) {
            $selected_warehouse = $warehouses[0]->id;
        }
        
        // Store selected warehouse in session
        if ($selected_warehouse) {
            $this->session->set_userdata('storage_warehouse_id', $selected_warehouse);
        }
        
        $this->data['warehouses'] = $warehouses;
        $this->data['selected_warehouse'] = $selected_warehouse;
        $this->data['error'] = "";
        $this->page_construct('storage/lookup', ['page_title' => 'Product Lookup'], $this->data);
    }

    public function lookup_ajax(){
        $avz_code = $this->input->post('avz_code');

        $movement = $this->db->get_where('sma_inventory_movements', ['avz_item_code'=>$avz_code])->row();
        if(!$movement){
            echo json_encode(['status'=>'error','msg'=>'Movement not found']);
            return;
        }

        $product = $this->db->get_where('sma_products', ['id'=>$movement->product_id])->row();
        if(!$product){
            echo json_encode(['status'=>'error','msg'=>'Product not found']);
            return;
        }
        
        $location = $this->ProductLocation_model->get_location($product->id);
        
        // Get warehouse_id from movement or product's default warehouse
        $warehouse_id = $movement->warehouse_id ?? null;
        
        // Get intelligent recommendations based on history and category (warehouse-filtered)
        $recommendations = $this->get_location_recommendations($product->id, $product->category_id, $warehouse_id);

        echo json_encode([
            'status'=>'success',
            'product'=>$product,
            'current_location'=>$location,
            'warehouse_id'=>$warehouse_id,
            'recommendations'=>$recommendations
        ]);
    }

    // ----------------------------
    // Get intelligent location recommendations
    // ----------------------------
    private function get_location_recommendations($product_id, $category_id = null, $warehouse_id = null){
        $recommendations = [];

        // Priority 1: Check if THIS specific product was stored before (in same warehouse)
        $product_history = $this->get_product_history($product_id, $warehouse_id);
        
        if(!empty($product_history)){
            foreach($product_history as $history){
                $capacity = $this->check_location_capacity($history->storage_location_id);
                
                $full_path = $this->get_location_full_path($history->storage_location_id);
                
                $recommendations[] = [
                    'id' => $history->storage_location_id,
                    'location_name' => $history->location_name,
                    'location_type' => $history->location_type,
                    'full_path' => $full_path,
                    'reason' => 'Same product stored here before',
                    'priority' => 1,
                    'times_used' => $history->times_used,
                    'last_used' => $history->last_used,
                    'capacity_available' => $capacity['available'],
                    'capacity_used' => $capacity['used'],
                    'capacity_total' => $capacity['total'],
                    'is_full' => $capacity['is_full']
                ];
            }
        }

        // Priority 2: Check if products from SAME CATEGORY were stored (in same warehouse)
        if($category_id){
            $category_history = $this->get_category_history($category_id, $product_id, $warehouse_id);
            
            if(!empty($category_history)){
                foreach($category_history as $cat_history){
                    // Skip if already recommended
                    $already_recommended = false;
                    foreach($recommendations as $rec){
                        if($rec['id'] == $cat_history->storage_location_id){
                            $already_recommended = true;
                            break;
                        }
                    }
                    
                    if($already_recommended) continue;
                    
                    $capacity = $this->check_location_capacity($cat_history->storage_location_id);
                    $full_path = $this->get_location_full_path($cat_history->storage_location_id);
                    
                    $recommendations[] = [
                        'id' => $cat_history->storage_location_id,
                        'location_name' => $cat_history->location_name,
                        'location_type' => $cat_history->location_type,
                        'full_path' => $full_path,
                        'reason' => 'Same category products stored here',
                        'priority' => 2,
                        'times_used' => $cat_history->times_used,
                        'last_used' => $cat_history->last_used,
                        'capacity_available' => $capacity['available'],
                        'capacity_used' => $capacity['used'],
                        'capacity_total' => $capacity['total'],
                        'is_full' => $capacity['is_full']
                    ];
                }
            }
        }

        // Priority 3: Get empty/available locations (in same warehouse)
        $empty_locations = $this->StorageLocation_model->get_empty_locations('box', $warehouse_id); // Only get box level
        
        foreach($empty_locations as $empty){
            // Skip if already recommended
            $already_recommended = false;
            foreach($recommendations as $rec){
                if($rec['id'] == $empty->id){
                    $already_recommended = true;
                    break;
                }
            }
            
            if($already_recommended) continue;
            
            $full_path = $this->get_location_full_path($empty->id);
            
            $recommendations[] = [
                'id' => $empty->id,
                'location_name' => $empty->name,
                'location_type' => $empty->type,
                'full_path' => $full_path,
                'reason' => 'Available empty location',
                'priority' => 3,
                'times_used' => 0,
                'last_used' => null,
                'capacity_available' => 100, // Empty location
                'capacity_used' => 0,
                'capacity_total' => 100,
                'is_full' => false
            ];
        }

        // Sort by priority (lower number = higher priority) and availability
        usort($recommendations, function($a, $b){
            // First sort by priority
            if($a['priority'] != $b['priority']){
                return $a['priority'] - $b['priority'];
            }
            
            // Then by availability (not full locations first)
            if($a['is_full'] != $b['is_full']){
                return $a['is_full'] ? 1 : -1;
            }
            
            // Then by times used (more used = better)
            return $b['times_used'] - $a['times_used'];
        });

        return $recommendations;
    }

    // ----------------------------
    // Get product storage history
    // ----------------------------
    private function get_product_history($product_id, $warehouse_id = null){
        $this->db->select('pl.storage_location_id, sl.name as location_name, sl.type as location_type, 
                          COUNT(*) as times_used, MAX(pl.created_at) as last_used')
                 ->from('sma_product_locations pl')
                 ->join('sma_storage_locations sl', 'sl.id = pl.storage_location_id')
                 ->where('pl.product_id', $product_id);
        
        // Filter by warehouse if provided
        if($warehouse_id){
            $this->db->where('sl.warehouse_id', $warehouse_id);
        }
        
        $this->db->group_by('pl.storage_location_id')
                 ->order_by('times_used', 'DESC')
                 ->limit(3); // Top 3 most used locations
        
        return $this->db->get()->result();
    }

    // ----------------------------
    // Get category storage history
    // ----------------------------
    private function get_category_history($category_id, $exclude_product_id = null, $warehouse_id = null){
        $this->db->select('pl.storage_location_id, sl.name as location_name, sl.type as location_type, 
                          COUNT(*) as times_used, MAX(pl.created_at) as last_used')
                 ->from('sma_product_locations pl')
                 ->join('sma_storage_locations sl', 'sl.id = pl.storage_location_id')
                 ->join('sma_products p', 'p.id = pl.product_id')
                 ->where('p.category_id', $category_id);
        
        if($exclude_product_id){
            $this->db->where('pl.product_id !=', $exclude_product_id);
        }
        
        // Filter by warehouse if provided
        if($warehouse_id){
            $this->db->where('sl.warehouse_id', $warehouse_id);
        }
        
        $this->db->group_by('pl.storage_location_id')
                 ->order_by('times_used', 'DESC')
                 ->limit(5); // Top 5 locations for this category
        
        return $this->db->get()->result();
    }

    // ----------------------------
    // Check location capacity (hierarchical - includes child locations)
    // ----------------------------
    private function check_location_capacity($location_id){
        // Get location details
        $location = $this->StorageLocation_model->get($location_id);
        
        if (!$location) {
            return [
                'total' => 0,
                'used' => 0,
                'available' => 0,
                'is_full' => true
            ];
        }
        
        // Get capacity from sma_storage_locations table (which is updated by assign_product)
        $total_capacity = isset($location->capacity_total) ? (int)$location->capacity_total : 100;
        $used_capacity = isset($location->capacity_used) ? (int)$location->capacity_used : 0;
        
        // If capacity_total is not set, default to 100
        if ($total_capacity <= 0) {
            $total_capacity = 100;
        }
        
        // Calculate available
        $available = $total_capacity - $used_capacity;
        
        return [
            'total' => $total_capacity,
            'used' => $used_capacity,
            'available' => max(0, $available), // Prevent negative
            'is_full' => ($available <= 0)
        ];
    }

    // ----------------------------
    // Get full location path (Rack A > Level 1 > Shelf A > Box 3)
    // ----------------------------
    private function get_location_full_path($location_id){
        $path = [];
        $current_id = $location_id;
        
        while($current_id){
            $location = $this->StorageLocation_model->get($current_id);
            if(!$location) break;
            
            // Add to beginning of path
            array_unshift($path, [
                'type' => ucfirst($location->type),
                'name' => $location->name
            ]);
            
            $current_id = $location->parent_id;
        }
        
        // Format as string: "Rack A > Level 1 > Shelf A > Box 3"
        $path_string = '';
        foreach($path as $index => $segment){
            if($index > 0) $path_string .= ' > ';
            $path_string .= $segment['type'] . ' ' . $segment['name'];
        }
        
        return $path_string;
    }


    // ----------------------------
    // AJAX assign product to storage location
    // ----------------------------
    public function assign_product(){
        $product_id = $this->input->post('product_id');
        $storage_location_id = $this->input->post('storage_location_id');
        $quantity = $this->input->post('quantity') ?: 1;

        if(!$product_id || !$storage_location_id){
            echo json_encode(['status'=>'error', 'msg'=>'Missing required parameters']);
            return;
        }

        // Check if product exists
        $product = $this->db->get_where('sma_products', ['id'=>$product_id])->row();
        if(!$product){
            echo json_encode(['status'=>'error', 'msg'=>'Product not found']);
            return;
        }

        // Check if storage location exists
        $location = $this->StorageLocation_model->get($storage_location_id);
        if(!$location){
            echo json_encode(['status'=>'error', 'msg'=>'Storage location not found']);
            return;
        }

        // Check capacity - ensure location has enough space
        $capacity_available = $location->capacity_total - $location->capacity_used;
        if($quantity > $capacity_available){
            echo json_encode([
                'status'=>'error', 
                'msg'=>'Insufficient capacity. Available: ' . $capacity_available . ' units, Requested: ' . $quantity . ' units'
            ]);
            return;
        }

        // Check if product is already assigned to a location
        $existing = $this->ProductLocation_model->get_location($product_id);
        
        // Start transaction
        $this->db->trans_start();
        
        if($existing){
            // Product is moving from one location to another
            $old_location_id = $existing->storage_location_id;
            $old_quantity = $existing->quantity;
            
            // Update product location assignment
            $this->db->where('product_id', $product_id);
            $this->db->update('product_locations', [
                'storage_location_id' => $storage_location_id,
                'quantity' => $quantity,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Decrease capacity from old location (free up space)
            if($old_location_id != $storage_location_id){
                $this->db->query(
                    "UPDATE sma_storage_locations 
                     SET capacity_used = GREATEST(0, capacity_used - ?) 
                     WHERE id = ?",
                    [$old_quantity, $old_location_id]
                );
            }
            
            // Increase capacity in new location
            $capacity_change = $quantity;
            if($old_location_id == $storage_location_id){
                // Same location, just updating quantity
                $capacity_change = $quantity - $old_quantity;
            }
            
            $this->db->query(
                "UPDATE sma_storage_locations 
                 SET capacity_used = capacity_used + ? 
                 WHERE id = ?",
                [$capacity_change, $storage_location_id]
            );
            
            $msg = ($old_location_id == $storage_location_id) 
                ? 'Product quantity updated in ' . $location->name
                : 'Product moved from ' . $existing->location_name . ' to ' . $location->name;
            
            echo json_encode([
                'status'=>'success', 
                'msg'=>$msg
            ]);
        } else {
            // New assignment - product not previously assigned
            
            // Insert new location assignment
            $this->ProductLocation_model->insert([
                'product_id' => $product_id,
                'storage_location_id' => $storage_location_id,
                'quantity' => $quantity,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Increase capacity used in the storage location
            $this->db->query(
                "UPDATE sma_storage_locations 
                 SET capacity_used = capacity_used + ? 
                 WHERE id = ?",
                [$quantity, $storage_location_id]
            );
            
            echo json_encode([
                'status'=>'success', 
                'msg'=>'Product assigned to ' . $location->name . ' successfully'
            ]);
        }
        
        // Complete transaction
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            echo json_encode([
                'status'=>'error', 
                'msg'=>'Failed to update capacity. Please try again.'
            ]);
        }
    }

    // ----------------------------
    // AJAX: Get location details with products
    // ----------------------------
    public function get_location_details() {
        $location_id = $this->input->post('location_id');
        
        if (!$location_id) {
            echo json_encode(['status' => 'error', 'msg' => 'Location ID required']);
            return;
        }

        // Get location info
        $location = $this->StorageLocation_model->get($location_id);
        if (!$location) {
            echo json_encode(['status' => 'error', 'msg' => 'Location not found']);
            return;
        }

        // Get full path
        $full_path = $this->get_location_full_path($location_id);

        // Get capacity info
        $capacity_info = $this->check_location_capacity($location_id);
        
        // Get products stored in this location
        $this->db->select('pl.*, p.code, p.name as product_name, p.image, c.name as category_name')
                 ->from('sma_product_locations pl')
                 ->join('sma_products p', 'pl.product_id = p.id', 'left')
                 ->join('sma_categories c', 'p.category_id = c.id', 'left')
                 ->where('pl.storage_location_id', $location_id)
                 ->order_by('pl.created_at', 'DESC');
        
        $products = $this->db->get()->result();

        // Format products for display
        $formatted_products = [];
        foreach ($products as $product) {
            $formatted_products[] = [
                'id' => $product->product_id,
                'code' => $product->code,
                'name' => $product->product_name,
                'category' => $product->category_name ?? 'N/A',
                'quantity' => $product->quantity,
                'image' => $product->image,
                'stored_at' => date('M d, Y H:i', strtotime($product->created_at))
            ];
        }

        echo json_encode([
            'status' => 'success',
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'type' => $location->type,
                'full_path' => $full_path,
                'capacity_total' => $capacity_info['total'],
                'capacity_used' => $capacity_info['used'],
                'capacity_available' => $capacity_info['available'],
                'capacity_percent' => $capacity_info['total'] > 0 ? round(($capacity_info['used'] / $capacity_info['total']) * 100) : 0,
                'is_full' => $capacity_info['is_full']
            ],
            'products' => $formatted_products,
            'total_products' => count($formatted_products)
        ]);
    }

    // ----------------------------
    // Initial Shelving - Auto Allocation Screen
    // ----------------------------
    public function initial_shelving() {
        $bc = [
            ['link' => base_url(), 'page' => lang('home')], 
            ['link' => admin_url('storage'), 'page' => lang('storage_locations')], 
            ['link' => '#', 'page' => 'Initial Shelving']
        ];
        $meta = ['page_title' => 'Initial Shelving - Auto Allocation', 'bc' => $bc];
        
        $this->data['warehouses'] = $this->get_warehouses();
        $this->data['error'] = $this->session->flashdata('error');
        $this->data['message'] = $this->session->flashdata('message');
        
        $this->page_construct('storage/initial_shelving', $meta, $this->data);
    }

    // ----------------------------
    // Get unallocated products for shelving
    // ----------------------------
    public function get_unallocated_products() {
        $warehouse_id = $this->input->post('warehouse_id');
        
        if (!$warehouse_id) {
            echo json_encode(['status' => 'error', 'msg' => 'Warehouse is required']);
            return;
        }

        // Get products from purchase_items that are NOT in product_locations
        $this->db->select('
            pi.product_id,
            pi.product_code,
            pi.product_name,
            p.category_id,
            c.name as category_name,
            p.image,
            SUM(pi.quantity_balance) as total_quantity
        ')
        ->from('purchase_items pi')
        ->join('products p', 'p.id = pi.product_id', 'left')
        ->join('categories c', 'c.id = p.category_id', 'left')
        ->where('pi.warehouse_id', $warehouse_id)
        ->where('pi.quantity_balance >', 0)
        ->where('pi.product_id NOT IN (SELECT DISTINCT product_id FROM sma_product_locations)', NULL, FALSE)
        ->group_by('pi.product_id')
        ->order_by('c.name', 'ASC')
        ->order_by('pi.product_name', 'ASC');

        $products = $this->db->get()->result();

        echo json_encode([
            'status' => 'success',
            'products' => $products,
            'total' => count($products)
        ]);
    }

    // ----------------------------
    // Generate intelligent storage allocation
    // ----------------------------
    public function generate_allocation() {
        $warehouse_id = $this->input->post('warehouse_id');
        
        if (!$warehouse_id) {
            echo json_encode(['status' => 'error', 'msg' => 'Warehouse is required']);
            return;
        }

        // Get all available storage locations (boxes with capacity) IN SPECIFIED WAREHOUSE
        $available_locations = $this->db->select('
            sl.id,
            sl.name,
            sl.parent_id,
            sl.warehouse_id,
            sl.capacity,
            COALESCE(SUM(pl.quantity), 0) as used_capacity
        ')
        ->from('storage_locations sl')
        ->join('product_locations pl', 'pl.storage_location_id = sl.id', 'left')
        ->where('sl.type', 'box')
        ->where('sl.warehouse_id', $warehouse_id)  // IMPORTANT: Filter by warehouse
        ->group_by('sl.id')
        ->having('sl.capacity > used_capacity OR used_capacity = 0')
        ->get()->result();

        if (empty($available_locations)) {
            echo json_encode([
                'status' => 'error', 
                'msg' => 'No available storage locations found in this warehouse. Please create storage structure first.'
            ]);
            return;
        }

        // Get unallocated products grouped by category
        $this->db->select('
            pi.product_id,
            pi.product_code,
            pi.product_name,
            p.category_id,
            c.name as category_name,
            p.image,
            SUM(pi.quantity_balance) as total_quantity
        ')
        ->from('purchase_items pi')
        ->join('products p', 'p.id = pi.product_id', 'left')
        ->join('categories c', 'c.id = p.category_id', 'left')
        ->where('pi.warehouse_id', $warehouse_id)
        ->where('pi.quantity_balance >', 0)
        ->where('pi.product_id NOT IN (SELECT DISTINCT product_id FROM sma_product_locations)', NULL, FALSE)
        ->group_by('pi.product_id')
        ->order_by('c.name', 'ASC')
        ->order_by('pi.product_name', 'ASC');

        $products = $this->db->get()->result();

        if (empty($products)) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'No unallocated products found in this warehouse.'
            ]);
            return;
        }

        // Smart allocation algorithm
        $allocations = [];
        $location_index = 0;
        $current_location = $available_locations[$location_index];
        $current_location->available = $current_location->capacity - $current_location->used_capacity;

        foreach ($products as $product) {
            $remaining_qty = $product->total_quantity;

            while ($remaining_qty > 0 && $location_index < count($available_locations)) {
                // Calculate how much can fit in current location
                $can_fit = min($remaining_qty, $current_location->available);

                if ($can_fit > 0) {
                    // Get full path for this location
                    $full_path = $this->get_location_full_path($current_location->id);

                    $allocations[] = [
                        'product_id' => $product->product_id,
                        'product_code' => $product->product_code,
                        'product_name' => $product->product_name,
                        'category_id' => $product->category_id,
                        'category_name' => $product->category_name ?? 'Uncategorized',
                        'image' => $product->image,
                        'quantity' => $can_fit,
                        'storage_location_id' => $current_location->id,
                        'storage_location_name' => $current_location->name,
                        'storage_path' => $full_path,
                        'warehouse_id' => $current_location->warehouse_id,
                        'status' => 'pending'
                    ];

                    $remaining_qty -= $can_fit;
                    $current_location->available -= $can_fit;
                }

                // If current location is full or no more space, move to next
                if ($current_location->available <= 0 || $remaining_qty > 0) {
                    $location_index++;
                    if ($location_index < count($available_locations)) {
                        $current_location = $available_locations[$location_index];
                        $current_location->available = $current_location->capacity - $current_location->used_capacity;
                    }
                }
            }

            if ($remaining_qty > 0) {
                // Couldn't fit all products - out of storage space
                echo json_encode([
                    'status' => 'warning',
                    'msg' => 'Not enough storage capacity for all products. Some products may not be allocated.',
                    'allocations' => $allocations
                ]);
                return;
            }
        }

        echo json_encode([
            'status' => 'success',
            'allocations' => $allocations,
            'total_allocations' => count($allocations),
            'warehouse_id' => $warehouse_id
        ]);
    }

    // ----------------------------
    // Confirm single allocation
    // ----------------------------
    public function confirm_allocation() {
        $allocation = $this->input->post('allocation');
        
        if (!$allocation) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid allocation data']);
            return;
        }

        // Check if already allocated
        $exists = $this->db->where('product_id', $allocation['product_id'])
                          ->where('storage_location_id', $allocation['storage_location_id'])
                          ->get('product_locations')
                          ->row();

        if ($exists) {
            // Update quantity
            $this->db->where('id', $exists->id)
                     ->update('product_locations', [
                         'quantity' => $exists->quantity + $allocation['quantity'],
                         'updated_at' => date('Y-m-d H:i:s')
                     ]);
        } else {
            // Insert new allocation
            $this->db->insert('product_locations', [
                'product_id' => $allocation['product_id'],
                'storage_location_id' => $allocation['storage_location_id'],
                'quantity' => $allocation['quantity'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        echo json_encode([
            'status' => 'success',
            'msg' => 'Allocation confirmed successfully'
        ]);
    }

    // ----------------------------
    // Confirm all allocations at once
    // ----------------------------
    public function confirm_all_allocations() {
        $allocations = $this->input->post('allocations');
        
        if (!$allocations || !is_array($allocations)) {
            echo json_encode(['status' => 'error', 'msg' => 'Invalid allocations data']);
            return;
        }

        $this->db->trans_start();

        $success_count = 0;
        foreach ($allocations as $allocation) {
            // Check if already allocated
            $exists = $this->db->where('product_id', $allocation['product_id'])
                              ->where('storage_location_id', $allocation['storage_location_id'])
                              ->get('product_locations')
                              ->row();

            if ($exists) {
                // Update quantity
                $this->db->where('id', $exists->id)
                         ->update('product_locations', [
                             'quantity' => $exists->quantity + $allocation['quantity'],
                             'updated_at' => date('Y-m-d H:i:s')
                         ]);
            } else {
                // Insert new allocation
                $this->db->insert('product_locations', [
                    'product_id' => $allocation['product_id'],
                    'storage_location_id' => $allocation['storage_location_id'],
                    'quantity' => $allocation['quantity'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            $success_count++;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'Failed to confirm allocations. Please try again.'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'msg' => "Successfully confirmed {$success_count} allocations",
                'count' => $success_count
            ]);
        }
    }

    // ----------------------------
    // Helper: Get warehouses
    // ----------------------------
    private function get_warehouses() {
        return $this->db->select('id, name, code')
                        ->from('warehouses')
                        //->where('active', 1)
                        ->get()
                        ->result();
    }

}
