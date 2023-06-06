<?php

defined('BASEPATH') or exit('No direct script access allowed');

class refund_model extends CI_Model
{
     protected $table = 'refund';

     
    public function __construct()
    {
        parent::__construct();
    }


 public function display_data()
    {
        $this->db->select('*')
     ->from('sales')
     ->join('refund', 'sales.customer_id = refund.user_id') ;
     //->where('refund.id ', $id);

$query = $this->db->get();
          // $query = $this->db->get('refund');
        return $query->result();
        
    }
      public function deleteRefund($id){
          if ($this->db->delete('refund', ['id' => $id])) {
            return true;
        }
        return false;
      }
       public function cancelRefund($id){
                   
    // $this->db->where('id', $id);
   
     
          if ($this->db->where('id', $id))
          {
           $this->db->set('refund_status','cancel');   
            $this->db->update('refund');
           return true;
        }
        return false;
      }
	
    
}