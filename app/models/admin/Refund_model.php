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
     /*$this->db->select('*')
     ->from('sales')
     ->join('sma_users', 'sales.customer_id = sma_users.id')
     ->join('refund', 'sma_users.id = refund.user_id')
     ->where('refund.id ', $id);*/

      $this->db->select('sma_sales.*, sma_refund.user_id')
      ->from('sma_refund')
      ->join('sma_users', 'sma_refund.user_id = sma_users.id')
      ->join('sma_companies', 'sma_users.company_id = sma_companies.id')
      ->join('sma_sales', 'sma_companies.company_id = sma_sales.customer_id');

      $query = $this->db->get();
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