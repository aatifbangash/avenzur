<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_requisition_model extends CI_Model {

    public function create_requisition($data, $items)
    {
        //print_r($items);exit;
        $this->db->trans_start();
        $this->db->insert('sma_purchase_requisitions', $data);
        $requisition_id = $this->db->insert_id();

        foreach ($items as &$item) {
            $item['requisition_id'] = $requisition_id;
        }

        $this->db->insert_batch('sma_purchase_requisition_items', $items);
        $this->db->trans_complete();

        return $this->db->trans_status() ? $requisition_id : false;
    }

    public function update_requisition($id, $data, $items)
{
    $this->db->trans_start();

    // Update main requisition
    $this->db->where('id', $id);
    $this->db->update('purchase_requisitions', $data);

    // Remove old items and re-insert new ones
    $this->db->where('requisition_id', $id);
    $this->db->delete('purchase_requisition_items');

    if (!empty($items)) {
        foreach ($items as $item) {
            $item_data = [
                'requisition_id' => $id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'remarks' => isset($item['remarks']) ? $item['remarks'] : null,
            ];
            $this->db->insert('purchase_requisition_items', $item_data);
        }
    }

    $this->db->trans_complete();

    return $this->db->trans_status();
}

    public function get_requisition($id)
    {
        $req = $this->db->get_where('purchase_requisitions', ['id' => $id])->row();
        if ($req) {
            //$req->items = $this->db->get_where('purchase_requisition_items', ['requisition_id' => $id])->result();
            $this->db->select('pri.*, p.name as product_name, p.code as product_code , p.cost, p.price, p.tax_rate as tax_rate');
            $this->db->from('purchase_requisition_items as pri');
            $this->db->join('products as p', 'p.id = pri.product_id', 'left');
            $this->db->where('pri.requisition_id', $id);
            $req->items = $this->db->get()->result();

        }
        return $req;
    }

     public function get_all() {
        $this->db->select('pr.*, u.username as requested_by_name, w.name as warehouse_name');
        $this->db->from( 'sma_purchase_requisitions pr');
        $this->db->join('sma_users u', 'u.id = pr.requested_by', 'left');
        $this->db->join('sma_warehouses w', 'w.id = pr.warehouse_id', 'left');
        $this->db->order_by('pr.created_at', 'DESC');

        $query = $this->db->get();
        return $query->result(); // returns array of objects
    }

    /**
     * Get a single requisition by ID
     */
    public function get_by_id($id) {
        $this->db->select('pr.*, u.username as requested_by_name, w.name as warehouse_name');
        $this->db->from('purchase_requisitions pr');
        $this->db->join('users u', 'u.id = pr.requested_by', 'left');
        $this->db->join('warehouses w', 'w.id = pr.warehouse_id', 'left');
        $this->db->where('pr.id', $id);
        $query = $this->db->get();
        return $query->row();
    }

    public function get_items($requisition_id)
{
    return $this->db
        ->select('pri.*, p.name as product_name')
        ->from('purchase_requisition_items pri')
        ->join('products p', 'p.id = pri.product_id', 'left')
        ->where('pri.requisition_id', $requisition_id)
        ->get()->result();
}

    public function getSupplierById($id)
    {
        return $this->db->get_where('companies', ['id' => $id])->row();
    }

    public function log_pr_sent($data)
    {
        return $this->db->insert('purchase_requisition_supplier', $data);
    }


}
