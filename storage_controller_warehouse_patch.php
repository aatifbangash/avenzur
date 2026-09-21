<?php
/*
 * PATCH FILE: Storage.php Controller Methods for Multi-Warehouse Support
 * 
 * Instructions:
 * 1. This file contains updated methods for app/controllers/admin/Storage.php
 * 2. Replace the corresponding methods in your Storage.php file with these versions
 * 3. Each method now supports warehouse_id filtering
 * 
 * Methods Included:
 * - lookup_ajax() - Updated
 * - get_location_recommendations() - Updated with warehouse filter
 * - get_product_history() - Updated with warehouse filter
 * - get_category_history() - Updated with warehouse filter
 * - generate_allocation() - Updated with warehouse filter
 */

// ====================================================================================
// METHOD: lookup_ajax()
// CHANGE: Get warehouse_id from product and pass to recommendations
// ====================================================================================

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

// ====================================================================================
// METHOD: get_location_recommendations()
// CHANGE: Added $warehouse_id parameter and filters
// ====================================================================================

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

// ====================================================================================
// METHOD: get_product_history()
// CHANGE: Added $warehouse_id parameter and JOIN filter
// ====================================================================================

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

// ====================================================================================
// METHOD: get_category_history()
// CHANGE: Added $warehouse_id parameter and JOIN filter
// ====================================================================================

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

// ====================================================================================
// METHOD: generate_allocation()
// CHANGE: Added warehouse_id filter for available storage locations
// ====================================================================================

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

    // Get unallocated products grouped by category (for specified warehouse)
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

    // Smart allocation algorithm (unchanged logic, but uses warehouse-filtered locations)
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
                'allocations' => $allocations,
                'total_allocations' => count($allocations)
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

?>
