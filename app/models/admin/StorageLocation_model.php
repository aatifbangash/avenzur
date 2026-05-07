<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class StorageLocation_model extends CI_Model {

    // Insert new location (rack/level/shelf/box)
    public function insert($data){
        $this->db->insert('storage_locations', $data);
        return $this->db->insert_id();
    }

    // Get location by ID
    public function get($id){
        return $this->db->get_where('storage_locations', ['id'=>$id])->row();
    }

    // Get all children locations under a parent
    public function get_children($parent_id, $warehouse_id = null){
        $where = ['parent_id' => $parent_id];
        if ($warehouse_id) {
            $where['warehouse_id'] = $warehouse_id;
        }
        return $this->db->get_where('storage_locations', $where)->result();
    }

    // Get all empty locations (no product assigned)
    public function get_empty_locations($type = null, $warehouse_id = null){
        $this->db->select('l.*')
                 ->from('storage_locations l')
                 ->join('product_locations p', 'p.storage_location_id = l.id', 'left')
                 ->where('p.id IS NULL');
        if($type){
            $this->db->where('l.type', $type);
        }
        if($warehouse_id){
            $this->db->where('l.warehouse_id', $warehouse_id);
        }
        return $this->db->get()->result();
    }

    // Get full hierarchy for specific warehouse
    public function get_hierarchy($parent_id = null, $warehouse_id = null){
        $where = ['parent_id' => $parent_id];
        if ($warehouse_id) {
            $where['warehouse_id'] = $warehouse_id;
        }
        $locations = $this->db->get_where('storage_locations', $where)->result();
        $result = [];
        foreach($locations as $loc){
            $children = $this->get_hierarchy($loc->id, $warehouse_id);
            $result[] = [
                'id' => $loc->id,
                'name' => $loc->name,
                'type' => $loc->type,
                'warehouse_id' => $loc->warehouse_id,
                'children' => $children
            ];
        }
        return $result;
    }

    // Get hierarchy with item counts and capacity details for specific warehouse
    public function get_hierarchy_with_details($parent_id = null, $warehouse_id = null){
        $where = ['parent_id' => $parent_id];
        if ($warehouse_id) {
            $where['warehouse_id'] = $warehouse_id;
        }
        $locations = $this->db->get_where('storage_locations', $where)->result();
        $result = [];
        foreach($locations as $loc){
            $children = $this->get_hierarchy_with_details($loc->id, $warehouse_id);
            
            // Get item count for this location
            $this->db->select('COUNT(*) as item_count, COALESCE(SUM(quantity), 0) as total_quantity')
                     ->from('product_locations')
                     ->where('storage_location_id', $loc->id);
            $stats = $this->db->get()->row();
            
            // Get capacity (default to 100 if not set)
            $capacity = isset($loc->capacity) && $loc->capacity > 0 ? $loc->capacity : 100;
            $used = $stats->total_quantity;
            $available = $capacity - $used;
            $percent_used = $capacity > 0 ? round(($used / $capacity) * 100) : 0;
            
            $result[] = [
                'id' => $loc->id,
                'name' => $loc->name,
                'type' => $loc->type,
                'warehouse_id' => $loc->warehouse_id,
                'children' => $children,
                'item_count' => (int)$stats->item_count,
                'total_quantity' => (int)$stats->total_quantity,
                'capacity' => $capacity,
                'capacity_used' => $used,
                'capacity_available' => $available,
                'capacity_percent' => $percent_used,
                'is_full' => $used >= $capacity
            ];
        }
        return $result;
    }
}
