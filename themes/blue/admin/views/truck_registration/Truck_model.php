<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Truck_model extends CI_Model
{

	public function addTruck($data,$purchaseId)
    {
      
         $save = $this->db->insert('sma_truck_registration', $data);
         if($save){
            $updatedStatus = array('status'=>'arrived');
            $this->db->update('purchases', $updatedStatus, ['id' => $purchaseId]);
            return $data;
         }
        
    }

    public function getReferenceNo()
    {
        $q = $this->db->get_where('purchases',['status' => 'ordered']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }

    }

    public function deleteDeal($id)
    {
        if ($this->db->delete('truck_registration', ['id' => $id])) {
            return true;
        }
        return false;
    }

       public function getTruckById($id)
    {
        $q = $this->db->get_where('truck_registration', ['id' => $id]);

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

     public function updateTruck($id, $data,$purchaseIdOld,$purchaseId)
    {
        if ($this->db->update('truck_registration', $data, ['id' => $id])) {
            if($purchaseIdOld !=$purchaseId){
            $updatedStatus = array('status'=>'arrived');
            $this->db->update('purchases', $updatedStatus, ['id' => $purchaseId]);

            $updatedStatusOld = array('status'=>'ordered');
            $this->db->update('purchases', $updatedStatusOld, ['id' => $purchaseIdOld]);
            }
            return true;
        }
        return false;
    }

    public function getPurchaseDiscount($sid)
    {
        $q = $this->db->get_where('deals', ['supplier_id' => $sid]);
        if ($q->num_rows() > 0) {
            return $q->row()->discount_purchase_order;
        }
        return 0;
    }

}