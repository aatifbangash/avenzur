<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Inventory_api extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function countInventory($filters = []){
        if ($filters['warehouse_id']) {
            $this->db->where('location_id', $filters['warehouse_id']);
        }
        $this->db->from('inventory_movements');
        return $this->db->count_all_results();
    }

    public function getInventory($filters = []){
        $this->db->select("id, product_id, batch_number, movement_date, type, quantity, location_id, net_unit_cost, net_unit_sale, expiry_date, reference_id, avz_item_code, bonus, real_unit_cost, real_unit_sale, customer_id");

        if ($filters['warehouse_id']) {
            $this->db->where('location_id', $filters['warehouse_id']);
        }

        //$this->db->order_by($filters['order_by'][0], $filters['order_by'][1] ? $filters['order_by'][1] : 'asc');
        $this->db->limit($filters['limit'], ($filters['start'] - 1));

        return $this->db->get('inventory_movements')->result();
    }
}
