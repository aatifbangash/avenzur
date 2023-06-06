<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Deals_model extends CI_Model
{

	public function addDeal($data)
    {
        return $this->db->insert('sma_deals', $data);
    }

    public function getAllSuppliers()
    {
    	$sups = $this->db->select('supplier_id')
    	         ->get('deals')->result_array();

    	$data1 = array();
    	if(count($sups) > 0)
    	{
	    		foreach ($sups as $sup) 
		    	{
		    	      $data1[] = $sup['supplier_id'];   
		    	 }         
		        $q = $this->db->where_not_in('id', $data1)
		        			->get_where('companies', ['group_name' => 'supplier']);
		}else{

			$q = $this->db->get_where('companies', ['group_name' => 'supplier']);

		}
    	
        
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function deleteDeal($id)
    {
        if ($this->db->delete('deals', ['id' => $id])) {
            return true;
        }
        return false;
    }

       public function getDealById($id)
    {
        $q = $this->db->get_where('deals', ['id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

     public function updateDeal($id, $data)
    {
        if ($this->db->update('deals', $data, ['id' => $id])) {
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