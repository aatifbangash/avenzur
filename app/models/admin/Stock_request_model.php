<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Stock_request_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addStockRequest($data, $items){
        $this->db->trans_start();
        if ($this->db->insert('stock_requests', $data)) {
            $request_id = $this->db->insert_id();

            foreach ($items as $item) {
                $item['stock_request_id'] = $request_id;
                $this->db->insert('stock_request_items', $item);
            }
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the stock request (stock_request_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function delete($request_id){
        $this->db->trans_start();

        $this->db->delete('sma_stock_requests', ['id' => $request_id]);

        $this->db->delete('sma_stock_request_items', ['stock_request_id' => $request_id]);

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while deleting the stock request (stock_request_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function getStockRequests($warehouse_id){
        $response = array();
        $this->db
                ->select('sma_stock_requests.id, sma_stock_requests.warehouse_id, sma_stock_requests.status, sma_stock_requests.date, sma_warehouses.name as warehouse, SUM(sma_stock_request_items.required_stock) AS req_stock')
                ->from('sma_stock_requests')
                ->join('sma_warehouses', 'sma_warehouses.id = sma_stock_requests.warehouse_id', 'left')
                ->join('sma_stock_request_items', 'sma_stock_request_items.stock_request_id = sma_stock_requests.id', 'left')
                ->group_by('sma_stock_requests.id');

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