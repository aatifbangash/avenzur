<?php defined('BASEPATH') or exit('No direct script access allowed');

class Salesman_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all salesmen
     * 
     * @return array Array of objects containing id and name
     */
    public function getAllSalesmen()
    {
        $this->db->select('id, name');
        $this->db->from('sma_sales_man');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        
        return $query->result();
    }
}