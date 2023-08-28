<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Stock_request_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getStockForPharmacy($warehouse_id){
        
        $response = array();
        $this->db
                ->select('sma_products.id, sma_products.name, sma_products.code, sma_products.cost, SUM(sma_warehouses_products.quantity) As available_stock')
                ->select('(SELECT SUM(sma_sale_items.quantity) 
                          FROM sma_sale_items
                          INNER JOIN sma_sales ON sma_sale_items.sale_id = sma_sales.id
                          WHERE sma_sale_items.product_id = sma_products.id
                          AND sma_sale_items.warehouse_id = '.$warehouse_id.'
                          AND sma_sales.date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)) AS avg_last_3_months_sales', false)
                ->from('sma_warehouses_products')
                ->join('sma_products', 'sma_products.id = sma_warehouses_products.product_id', 'left')
                ->where('sma_warehouses_products.warehouse_id', $warehouse_id)
                ->group_by('sma_warehouses_products.product_id');
        $q = $this->db->get();
        if(!empty($q)){
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data_res[] = $row;
                }
            } else {
                $data_res = array();
            }
        }else{
            $data_res = array();
        }
        
        return $data_res;

        /*$this->db->select('' . $this->db->dbprefix('products') . '.id, code, ' . $this->db->dbprefix('products') . '.name as name, ' . $this->db->dbprefix('products') . '.price as price, ')
            ->where("type != 'combo'");

        $this->db->group_by('products.id')->limit($limit);   
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;*/
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