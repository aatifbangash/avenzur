<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProductLocation_model extends CI_Model {

    // Assign product to a location
    public function insert($data){
        $this->db->insert('product_locations', $data);
        return $this->db->insert_id();
    }

    // Get product location
    public function get_location($product_id){
        $this->db->select('pl.*, sl.name as location_name, sl.type as location_type')
                 ->from('product_locations pl')
                 ->join('storage_locations sl', 'sl.id = pl.storage_location_id')
                 ->where('pl.product_id', $product_id);
        return $this->db->get()->row();
    }

    // Check if product already exists in location
    public function exists_in_location($product_id, $location_id){
        $this->db->where(['product_id'=>$product_id, 'storage_location_id'=>$location_id]);
        return $this->db->get('product_locations')->row();
    }
}
