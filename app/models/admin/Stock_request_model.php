<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Stock_request_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addPurchaseRequest($data, $items, $warehouse_id){
        $this->db->trans_start();
        if ($this->db->insert('purchase_requests', $data)) {
            $request_id = $this->db->insert_id();

            foreach ($items as $item) {
                $item['purchase_request_id'] = $request_id;
                $this->db->insert('purchase_request_items', $item);
            }
            
            if($warehouse_id == null || $warehouse_id == 'null'){
                $this->db->update('stock_requests', ['purchase_request_id' => $request_id, 'status' => 'completed'], ['status' => 'pending']);
            }else{
                $this->db->update('stock_requests', ['purchase_request_id' => $request_id, 'status' => 'completed'], ['status' => 'pending', 'warehouse_id' => $warehouse_id]);
            }
        }
        
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the purchase request (stock_request_model.php)');
        } else {
            return true;
        }
        return false;
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

    public function editPurchaseRequest($req_id, $data, $items, $warehouse_id){
        $this->db->trans_start();

        $this->db->delete('sma_purchase_requests', ['id' => $req_id]);

        $this->db->delete('sma_purchase_request_items', ['purchase_request_id' => $req_id]);

        if ($this->db->insert('purchase_requests', $data)) {
            $request_id = $this->db->insert_id();

            foreach ($items as $item) {
                $item['purchase_request_id'] = $request_id;
                $this->db->insert('sma_purchase_request_items', $item);
            }

            if($warehouse_id == null){
                $this->db->update('stock_requests', ['purchase_request_id' => $request_id, 'status' => 'completed'], ['purchase_request_id' => $req_id]);
            }else{
                $this->db->update('stock_requests', ['purchase_request_id' => $request_id, 'status' => 'completed'], ['purchase_request_id' => $req_id, 'warehouse_id' => $warehouse_id]);
            }
            
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while edit the stock request (stock_request_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function editStockRequest($req_id, $data, $items){
        $this->db->trans_start();

        $this->db->delete('sma_stock_requests', ['id' => $req_id]);

        $this->db->delete('sma_stock_request_items', ['stock_request_id' => $req_id]);

        if ($this->db->insert('stock_requests', $data)) {
            $request_id = $this->db->insert_id();

            foreach ($items as $item) {
                $item['stock_request_id'] = $request_id;
                $this->db->insert('stock_request_items', $item);
            }
        }

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while edit the stock request (stock_request_model.php)');
        } else {
            return true;
        }
        return false;
    }

    public function delete_purchase($request_id){
        $this->db->trans_start();

        $this->db->delete('sma_purchase_requests', ['id' => $request_id]);

        $this->db->delete('sma_purchase_request_items', ['purchase_request_id' => $request_id]);

        $this->db->update('stock_requests', ['status' => 'deleted'], ['purchase_request_id' => $request_id]);

        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while deleting the stock request (stock_request_model.php)');
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

    public function getPurchaseRequests(){
        $response = array();
        $this->db
                ->select('sma_purchase_requests.id, sma_purchase_requests.status, sma_purchase_requests.date, SUM(sma_purchase_request_items.required_stock) AS req_stock')
                ->from('sma_purchase_requests')
                ->join('sma_purchase_request_items', 'sma_purchase_request_items.purchase_request_id = sma_purchase_requests.id', 'left')
                ->group_by('sma_purchase_requests.id');

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

    public function getStockRequests($warehouse_id){
        $response = array();
        if($this->Owner || $this->Admin){
            $this->db
                ->select('sma_stock_requests.id, sma_stock_requests.warehouse_id, sma_stock_requests.status, sma_stock_requests.date, sma_warehouses.name as warehouse, SUM(sma_stock_request_items.required_stock) AS req_stock')
                ->from('sma_stock_requests')
                ->join('sma_warehouses', 'sma_warehouses.id = sma_stock_requests.warehouse_id', 'left')
                ->join('sma_stock_request_items', 'sma_stock_request_items.stock_request_id = sma_stock_requests.id', 'left')
                ->group_by('sma_stock_requests.id');
        }else{
            $this->db
                ->select('sma_stock_requests.id, sma_stock_requests.warehouse_id, sma_stock_requests.status, sma_stock_requests.date, sma_warehouses.name as warehouse, SUM(sma_stock_request_items.required_stock) AS req_stock')
                ->from('sma_stock_requests')
                ->join('sma_warehouses', 'sma_warehouses.id = sma_stock_requests.warehouse_id', 'left')
                ->join('sma_stock_request_items', 'sma_stock_request_items.stock_request_id = sma_stock_requests.id', 'left')
                ->where('sma_stock_requests.warehouse_id',$warehouse_id)
                ->group_by('sma_stock_requests.id');
        }

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

    public function getPurchaseRequestItems($request_id){
        $response = array();
        $this->db
                ->select('sma_products.id, sma_products.name, sma_products.code, sma_products.cost, sma_purchase_request_items.available_stock as total_warehouses_quantity, sma_purchase_request_items.avg_stock as total_avg_stock, sma_purchase_request_items.required_stock as qreq, sma_purchase_request_items.months')
                ->from('sma_purchase_request_items')
                ->join('sma_products', 'sma_products.id = sma_purchase_request_items.product_id', 'left')
                ->where('sma_purchase_request_items.purchase_request_id',$request_id);
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

    public function getStockRequestItems($request_id){
        $response = array();
        $this->db
                ->select('sma_products.id, sma_products.name, sma_products.code, sma_products.cost, sma_stock_request_items.available_stock, sma_stock_request_items.avg_stock, sma_stock_request_items.required_stock')
                ->from('sma_stock_request_items')
                ->join('sma_products', 'sma_products.id = sma_stock_request_items.product_id', 'left')
                ->where('sma_stock_request_items.stock_request_id',$request_id);
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
    
    public function getCurrentPR($warehouse_id, $fromdate, $todate){
        $fromdate = date("Y-m-d", strtotime(str_replace("/", "-", $fromdate)));
        $todate = date("Y-m-d", strtotime(str_replace("/", "-", $todate)));
        $response = array();
        if($warehouse_id == null || $warehouse_id == 'null'){
            $this->db
                ->select('sma_products.id, sma_products.name, sma_products.code, sma_products.cost, SUM(sma_stock_request_items.required_stock) As total_req_stock, SUM(sma_stock_request_items.avg_stock) As total_avg_stock')
                ->select('(SELECT SUM(sma_warehouses_products.quantity)
                         FROM sma_warehouses_products
                         WHERE sma_warehouses_products.product_id = sma_products.id
                         GROUP BY sma_warehouses_products.product_id
                ) AS total_warehouses_quantity')
                ->from('sma_stock_requests')
                ->join('sma_stock_request_items', 'sma_stock_request_items.stock_request_id = sma_stock_requests.id')
                ->join('sma_products', 'sma_products.id = sma_stock_request_items.product_id', 'left')
                ->where('sma_stock_requests.status', 'pending');

                if(!empty($fromdate)){
                    $this->db->where('sma_stock_requests.date >=', $fromdate);
                }

                if(!empty($todate)){
                    $this->db->where('sma_stock_requests.date <=', $todate);
                }

                $this->db->group_by('sma_stock_request_items.product_id');
        }else{
            $this->db
                ->select('sma_products.id, sma_products.name, sma_products.code, sma_products.cost, SUM(sma_stock_request_items.required_stock) As total_req_stock, SUM(sma_stock_request_items.avg_stock) As total_avg_stock')
                ->select('(SELECT SUM(sma_warehouses_products.quantity)
                         FROM sma_warehouses_products
                         WHERE sma_warehouses_products.product_id = sma_products.id
                         GROUP BY sma_warehouses_products.product_id
                ) AS total_warehouses_quantity')
                ->from('sma_stock_requests')
                ->join('sma_stock_request_items', 'sma_stock_request_items.stock_request_id = sma_stock_requests.id')
                ->join('sma_products', 'sma_products.id = sma_stock_request_items.product_id', 'left')
                ->where('sma_stock_requests.status', 'pending')
                ->where('sma_stock_requests.warehouse_id', $warehouse_id);

                if(!empty($fromdate)){
                    $this->db->where('sma_stock_requests.date >=', $fromdate);
                }

                if(!empty($todate)){
                    $this->db->where('sma_stock_requests.date >=', $todate);
                }

                $this->db->group_by('sma_stock_request_items.product_id');
        }
        
        $q = $this->db->get();
        if(!empty($q)){
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $row->qreq =  ($row->total_avg_stock * 1) - ($row->total_warehouses_quantity);
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

    public function getStockForPharmacy($warehouse_id, $product_ids){
        
        $response = array();

        if(!$product_ids){
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
        }else{
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
                ->where_in('sma_warehouses_products.product_id', $product_ids)
                ->group_by('sma_warehouses_products.product_id');
        }

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