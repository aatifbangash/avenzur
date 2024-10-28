<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Inventory_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    // Function to add an inventory movement for any operation, explicitly including location
    public function add_movement($product_id, $batch_no, $type, $quantity, $location_id, $reference_id=null, $net_unit_cost=null, $expiry_date=null, $net_unit_sale=null, $real_unit_cost=null, $avz_item_code=null, $bonus=null, $customer_id=null)
    {
        $quantity = abs($quantity);

        $data = array(
            'product_id' => $product_id,
            'batch_number' => $batch_no,
            'type' => $type,
            'quantity' => ($type === 'sale' || $type === 'pos' || $type === 'return_to_supplier' || $type === 'transfer_out' || $type === 'adjustment_decrease') ? -$quantity : $quantity,
            'location_id' => $location_id,
            'reference_id' => $reference_id,
            'net_unit_cost' => $net_unit_cost,
            'expiry_date' => $expiry_date,
            'net_unit_sale' => $net_unit_sale,
            'real_unit_cost' => $real_unit_cost,
            'avz_item_code' => $avz_item_code,
            'bonus' => $bonus,
            'customer_id' => $customer_id
        );
        if ($this->db->insert('sma_inventory_movements', $data)) {
           // echo "insrted";
        }else{
           // echo "not inseted";
        }
    }
    
    public function update_movement($product_id, $batch_no, $type, $quantity, $location_id, $reference_id=null, $net_unit_cost=null, $expiry_date=null, $net_unit_sale=null, $real_unit_cost=null)
    {
        $data = array(
            'product_id' => $product_id,
            'batch_number' => $batch_no,
            'type' => $type,
            'quantity' => ($type === 'sale' || $type === 'return_to_supplier' || $type === 'transfer_out' || $type === 'adjustment_decrease') ? -$quantity : $quantity,
            'location_id' => $location_id,
            'reference_id' => $reference_id,
            'net_unit_cost' => $net_unit_cost,
            'expiry_date' => $expiry_date,
            'net_unit_sale' => $net_unit_sale,
            'real_unit_cost' => $real_unit_cost
        );
        $this->db->update('inventory_movements',$data, ['product_id' => $product_id, 'batch_number' => $batch_no, 'type' => $type, 'location_id' => $location_id]);
    }

    // Function to calculate current onhold stock for a specific product at a given location
    public function get_onhold_stock($product_id){
        $this->db->select('SUM(quantity) as total_quantity');
        $this->db->from('product_qty_onhold_request');
        $this->db->where('product_id', $product_id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            return $result->total_quantity;
        }
        return 0; // Return 0 if no movements found
    }

    // Function to calculate current stock for a specific product at a given location
    public function get_current_stock($product_id, $location_id, $batch_no = null)
    {
       
        $this->db->select('SUM(quantity) as total_quantity');
        $this->db->from('inventory_movements');
        $this->db->where('product_id', $product_id);

        if($batch_no != null){
            $this->db->where('batch_number', $batch_no);
        }

        if($location_id != 'null'){
            $this->db->where('location_id', $location_id);
        }
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $result = $query->row();
            return $result->total_quantity;
        }
        return 0; // Return 0 if no movements found
    }
}
