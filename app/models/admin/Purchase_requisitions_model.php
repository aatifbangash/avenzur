<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_requisitions_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addRequisition($data, $items)
    {
        $this->db->trans_start();

        if ($this->db->insert('purchase_requisitions', $data)) {
            $requisition_id = $this->db->insert_id();

            foreach ($items as $item) {
                $item['requisition_id'] = $requisition_id;
                $this->db->insert('purchase_requisition_items', $item);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function getRequisitionByID($id)
    {
        return $this->db->get_where('purchase_requisitions', ['id' => $id])->row();
    }

    public function getAllRequisitionItems($requisition_id)
    {
        return $this->db->get_where('purchase_requisition_items', ['requisition_id' => $requisition_id])->result();
    }
}