<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Stock_request_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    
    
    public function getProductNamesPos($term, $limit = 5)
    {
        $this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ')
            ->where("type != 'combo' AND "
                . '(' . $this->db->dbprefix('products') . ".name LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
                concat(" . $this->db->dbprefix('products') . ".name, ' (', code, ')') LIKE '%" . $term . "%')");
                
                
                
        /*$this->db->join('product_variants', 'product_variants.product_id=products.id', 'left')
            ->where('' . $this->db->dbprefix('product_variants') . '.name', null)
            ->group_by('products.id')->limit($limit);*/
            
            
         $this->db->group_by('products.id')->limit($limit);   
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}