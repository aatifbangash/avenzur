<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Stock_request_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('ion_auth');
    }

    public function addPurchaseRequest($data, $items, $warehouse_id, $fromdate, $todate){
        if(!empty($fromdate)){
            $fromdate = date("Y-m-d", strtotime(str_replace("/", "-", $fromdate)));
        }
        if(!empty($todate)){
            $todate = date("Y-m-d", strtotime(str_replace("/", "-", $todate)));
        }
        $this->db->trans_start();
        if ($this->db->insert('purchase_requests', $data)) {
            $request_id = $this->db->insert_id();

            foreach ($items as $item) {
                $item['purchase_request_id'] = $request_id;
                $this->db->insert('purchase_request_items', $item);
            }
            
            if($warehouse_id == null || $warehouse_id == 'null'){
                $this->db->where('status', 'pending');
                if(!empty($fromdate)){
                    $this->db->where('date >=', $fromdate);
                }
                if(!empty($todate)){
                    $this->db->where('date <=', $todate);
                }
                $this->db->update('stock_requests', ['purchase_request_id' => $request_id, 'status' => 'completed']);
                //$this->db->update('stock_requests', ['purchase_request_id' => $request_id, 'status' => 'completed'], ['status' => 'pending']);
            }else{
                $this->db->where('status', 'pending');
                $this->db->where('warehouse_id', $warehouse_id);
                if(!empty($fromdate)){
                    $this->db->where('date >=', $fromdate);
                }
                if(!empty($todate)){
                    $this->db->where('date <=', $todate);
                }
                $this->db->update('stock_requests', ['purchase_request_id' => $request_id, 'status' => 'completed']);
                //$this->db->update('stock_requests', ['purchase_request_id' => $request_id, 'status' => 'completed'], ['status' => 'pending', 'warehouse_id' => $warehouse_id]);
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

    public function editPurchaseRequest($req_id, $data, $items, $warehouse_id, $fromdate, $todate){
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

    public function deleteInventoryCheckRequest($req_id){
        $this->db->delete('inventory_check_requests', ['id' => $req_id]);

        $this->db->delete('inventory_check_items', ['inv_check_id' => $req_id]);
    }

    public function updateAdjustmentStatus($req_id){
        $this->db->update('inventory_check_requests', ['status' => 'adjusted'], ['id' => $req_id]);
    }

    public function createInventoryCheckReport($inventory_check_report_data){
        $this->db->insert_batch('inventory_check_report', $inventory_check_report_data);
    }

    /*public function getInventoryCheck($req_id, $location_id) {
        $this->db
            ->select('
                im.avz_item_code as avz_code,
                IFNULL(ci.quantity, 0) as quantity,
                im.quantity as system_quantity,
                im.batch_number,
                im.expiry_date,
                im.product_id,
                im.net_unit_cost,
                im.net_unit_sale,
                im.real_unit_cost,
                im.real_unit_sale,
                p.tax_rate,
                p.name as product_name,
                p.code as product_code,
                p.unit
            ', false)
            ->from('sma_inventory_movements im')
            ->join(
                'sma_inventory_check_items ci',
                'im.avz_item_code = ci.avz_code AND ci.inv_check_id = ' . $this->db->escape($req_id),
                'left'
            )
            ->join('sma_products p', 'p.id = im.product_id', 'left')
            ->where('im.location_id', $location_id)
            ->where('(IFNULL(ci.quantity, 0) > 0 OR im.quantity > 0)', null, false)
            ->order_by('ci.quantity', 'desc');
    
        $q = $this->db->get();
        $data_res = [];
    
        if ($q && $q->num_rows() > 0) {
            $raw_data = $q->result();
    
            // Aggregate by avz_item_code
            foreach ($raw_data as $row) {
                $key = $row->avz_code;
    
                if (!isset($data_res[$key])) {
                    $data_res[$key] = (object) [
                        'avz_code'        => $row->avz_code,
                        'quantity'        => $row->quantity, // from check_items (static)
                        'system_quantity' => $row->system_quantity,
                        'batch_number'    => $row->batch_number, // you can overwrite or collect all
                        'expiry_date'     => $row->expiry_date,
                        'product_id'      => $row->product_id,
                        'net_unit_cost'   => $row->net_unit_cost,
                        'net_unit_sale'   => $row->net_unit_sale,
                        'real_unit_cost'  => $row->real_unit_cost,
                        'real_unit_sale'  => $row->real_unit_sale,
                        'tax_rate'        => $row->tax_rate,
                        'product_name'    => $row->product_name,
                        'product_code'    => $row->product_code,
                        'unit'            => $row->unit,
                    ];
                } else {
                    // Aggregate system quantity (sum of im.quantity)
                    $data_res[$key]->system_quantity += $row->system_quantity;
                }
            }
    
            // Reindex as simple array
            $data_res = array_values($data_res);
        }
    
        return $data_res;
    }*/
    
    public function getInventoryCheckByBatch($req_id, $location_id)
    {
        $data_res = [];

        $sql = "
        SELECT
            COALESCE(sys.product_id, chk.product_id) AS product_id,
            COALESCE(sys.batch_number, chk.batch_number) AS batch_number,
            COALESCE(sys.expiry_date, chk.expiry_date) AS expiry_date,

            IFNULL(chk.excel_quantity, 0) AS quantity,
            IFNULL(sys.system_quantity, 0) AS system_quantity,

            sys.avz_code,
            sys.net_unit_cost,
            sys.net_unit_sale,
            sys.real_unit_cost,
            sys.real_unit_sale,

            p.tax_rate,
            p.name AS product_name,
            p.code AS product_code,
            p.item_code,
            p.unit

        FROM
        (
            SELECT
                product_id,
                batch_number,
                expiry_date,
                SUM(quantity) AS excel_quantity
            FROM sma_inventory_check_items
            WHERE inv_check_id = ?
            GROUP BY product_id, batch_number, expiry_date
        ) chk

        LEFT JOIN
        (
            SELECT
                product_id,
                batch_number,
                expiry_date,
                SUM(quantity) AS system_quantity,
                MAX(avz_item_code) AS avz_code,
                MAX(net_unit_cost) AS net_unit_cost,
                MAX(net_unit_sale) AS net_unit_sale,
                MAX(real_unit_cost) AS real_unit_cost,
                MAX(real_unit_sale) AS real_unit_sale
            FROM sma_inventory_movements
            WHERE location_id = ?
            GROUP BY product_id, batch_number, expiry_date
        ) sys
        ON chk.product_id = sys.product_id
        AND chk.batch_number = sys.batch_number
        AND (
                (chk.expiry_date IS NULL AND sys.expiry_date IS NULL)
                OR chk.expiry_date = sys.expiry_date
            )

        LEFT JOIN sma_products p
            ON p.id = COALESCE(sys.product_id, chk.product_id)

        HAVING (quantity <> 0 OR system_quantity <> 0)
        ORDER BY product_name ASC, batch_number ASC
        ";

        $query = $this->db->query($sql, [$req_id, $location_id]);
        $result = $query->result();

        return $result;
    }

    public function getInventoryCheck($req_id, $location_id){
        $response = array();
        
        $this->db
        ->select('
                im.avz_item_code as avz_code,
                IFNULL(ci.quantity, 0) as quantity,
                SUM(im.quantity) as system_quantity,
                im.batch_number,
                im.expiry_date,
                im.product_id,
                im.net_unit_cost,
                im.net_unit_sale,
                im.real_unit_cost,
                im.real_unit_sale,
                p.tax_rate,
                p.name as product_name,
                p.code as product_code,
                p.unit
            ', false)
            ->from('sma_inventory_movements im')
            ->join('sma_inventory_check_items ci', 'im.avz_item_code = ci.avz_code AND ci.inv_check_id = '.$this->db->escape($req_id), 'left')
            ->join('sma_products p', 'p.id = im.product_id', 'left')
            ->where('im.location_id', $location_id)
            ->group_by('im.avz_item_code')
            ->having('quantity > 0 OR system_quantity > 0')
            ->order_by('quantity', 'desc');

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

    public function getInventoryCheckReportById($req_id){
        $response = array();
        $this->db
                ->select('sma_inventory_check_report.*')
                ->from('sma_inventory_check_report')
                ->where('sma_inventory_check_report.inv_check_id',$req_id);

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

    public function getInventoryCheckRequestById($req_id){
        $response = array();
        $this->db
                ->select('sma_inventory_check_requests.*, sma_warehouses.name')
                ->from('sma_inventory_check_requests')
                ->join('sma_warehouses', 'sma_inventory_check_requests.location_id = sma_warehouses.id', 'left')
                ->where('sma_inventory_check_requests.id',$req_id)
                ->group_by('sma_inventory_check_requests.id');

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

    public function getInventoryCheckRequests(){
        $response = array();
        $this->db
                ->select('sma_inventory_check_requests.*, sma_warehouses.name')
                ->from('sma_inventory_check_requests')
                ->join('sma_warehouses', 'sma_inventory_check_requests.location_id = sma_warehouses.id', 'left')
                ->group_by('sma_inventory_check_requests.id');

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
        $user_group_id = $this->ion_auth->user()->row()->group_id;
        $user_group_obj = $this->db->select('*')->from('sma_groups')->where('id', $user_group_id)->limit(1)->get();
        $user_group_arr = array();

        if ($user_group_obj->num_rows() > 0) {
            foreach (($user_group_obj->result()) as $row) {
                $user_group_arr[] = $row;
            }
        }

        if($user_group_arr[0]->name == 'purchasemanager'){
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
        if(!empty($fromdate)){
            $fromdate = date("Y-m-d", strtotime(str_replace("/", "-", $fromdate)));
        }
        if(!empty($todate)){
            $todate = date("Y-m-d", strtotime(str_replace("/", "-", $todate)));
        }
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
                    $this->db->where('sma_stock_requests.date <=', $todate);
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
                ->select('sma_products.id, sma_products.name, sma_products.code, sma_products.cost, SUM(sma_inventory_movements.quantity) As available_stock')
                ->select('(SELECT SUM(sma_sale_items.quantity) 
                          FROM sma_sale_items
                          INNER JOIN sma_sales ON sma_sale_items.sale_id = sma_sales.id
                          WHERE sma_sale_items.product_id = sma_products.id
                          AND sma_sale_items.warehouse_id = '.$warehouse_id.'
                          AND sma_sales.date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)) AS avg_last_3_months_sales', false)
                ->from('sma_inventory_movements')
                ->join('sma_products', 'sma_products.id = sma_inventory_movements.product_id', 'left')
                ->where('sma_inventory_movements.location_id', $warehouse_id)
                ->group_by('sma_inventory_movements.product_id');
        }else{
            $this->db
                ->select('sma_products.id, sma_products.name, sma_products.code, sma_products.cost, SUM(sma_inventory_movements.quantity) As available_stock')
                ->select('(SELECT SUM(sma_sale_items.quantity) 
                          FROM sma_sale_items
                          INNER JOIN sma_sales ON sma_sale_items.sale_id = sma_sales.id
                          WHERE sma_sale_items.product_id = sma_products.id
                          AND sma_sale_items.warehouse_id = '.$warehouse_id.'
                          AND sma_sales.date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)) AS avg_last_3_months_sales', false)
                ->from('sma_inventory_movements')
                ->join('sma_products', 'sma_products.id = sma_inventory_movements.product_id', 'left')
                ->where('sma_inventory_movements.location_id', $warehouse_id)
                ->where_in('sma_inventory_movements.product_id', $product_ids)
                ->group_by('sma_inventory_movements.product_id');
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