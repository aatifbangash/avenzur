<?php defined('BASEPATH') or exit('No direct script access allowed');

class Customer_saleman_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addCustomerSaleman($data = [])
    {
        if ($this->db->insert('sma_customer_saleman', $data)) {
            return $this->db->insert_id();
        }
        return false;
    }
}