<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Site extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->admin_model('Inventory_model');
    }

    public function calculateAVCost($product_id, $warehouse_id, $batch_no, $net_unit_price, $unit_price, $quantity, $product_name, $option_id, $item_quantity)
    {
        $product       = $this->getProductByID($product_id);
        $real_item_qty = $quantity;
        $wp_details    = $this->getWarehouseProduct($warehouse_id, $product_id);
        $con           = $wp_details ? $wp_details->avg_cost : $product->cost;
        $tax_rate      = $this->getTaxRateByID($product->tax_rate);
        $ctax          = $this->calculateTax($product, $tax_rate, $con);
        if ($product->tax_method) {
            $avg_net_unit_cost = $con;
            $avg_unit_cost     = ($con + $ctax['amount']);
        } else {
            $avg_unit_cost     = $con;
            $avg_net_unit_cost = ($con - $ctax['amount']);
        }

        if ($pis = $this->getPurchasedItemsWithBatch($product_id, $warehouse_id, $batch_no, $option_id)) {
            $cost_row    = [];
            $quantity    = $item_quantity;
            $balance_qty = $quantity;
            foreach ($pis as $pi) {
                if (!empty($pi) && $pi->quantity_balance != 0 && $balance_qty <= $quantity && $quantity != 0) {
                    if ($pi->quantity_balance >= $quantity && $quantity != 0) {
                        $balance_qty = $pi->quantity_balance - $quantity;
                        $cost_row    = ['date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_id' => $pi->purchase_id, 'transfer_id' => $pi->transfer_id, 'purchase_item_id' => $pi->id, 'quantity' => $quantity, 'batch_no' => $pi->batchno, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id];
                        $quantity    = 0;
                    } elseif ($quantity != 0) {
                        $quantity    = $quantity - $pi->quantity_balance;
                        $balance_qty = $quantity;
                        $cost_row    = ['date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_id' => $pi->purchase_id, 'transfer_id' => $pi->transfer_id, 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'batch_no' => $pi->batchno, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id];
                    }
                }
                if (empty($cost_row)) {
                    break;
                }
                $cost[] = $cost_row;
                if ($quantity == 0) {
                    break;
                }
            }
        }
        if ($quantity > 0 && !$this->Settings->overselling) {
            $this->session->set_flashdata('error', sprintf(lang('quantity_out_of_stock_for_%s'), $product_name));
            redirect($_SERVER['HTTP_REFERER']);
        } elseif ($quantity != 0) {
            $cost[] = ['date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $quantity, 'purchase_net_unit_cost' => $avg_net_unit_cost, 'purchase_unit_cost' => $avg_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => (0 - $quantity), 'overselling' => 1, 'inventory' => 1];
            $cost[] = ['pi_overselling' => 1, 'product_id' => $product_id, 'quantity_balance' => (0 - $quantity), 'warehouse_id' => $warehouse_id, 'option_id' => $option_id];
        }
        return $cost;
    }

    public function calculateCost($product_id, $warehouse_id, $net_unit_price, $unit_price, $quantity, $product_name, $option_id, $item_quantity)
    {
        $pis           = $this->getPurchasedItems($product_id, $warehouse_id, $option_id);
        $real_item_qty = $quantity;
        $quantity      = $item_quantity;
        $balance_qty   = $quantity;
        foreach ($pis as $pi) {
            $cost_row = null;
            if (!empty($pi) && $balance_qty <= $quantity && $quantity != 0) {
                $purchase_unit_cost = $pi->base_unit_cost ?? ($pi->unit_cost ?? ($pi->net_unit_cost + ($pi->item_tax / $pi->quantity)));
                if ($pi->quantity_balance >= $quantity && $quantity != 0) {
                    $balance_qty = $pi->quantity_balance - $quantity;
                    $cost_row    = ['date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_id' => $pi->purchase_id, 'transfer_id' => $pi->transfer_id, 'purchase_item_id' => $pi->id, 'quantity' => $quantity, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => $balance_qty, 'inventory' => 1, 'option_id' => $option_id];
                    $quantity    = 0;
                } elseif ($quantity != 0) {
                    $quantity    = $quantity - $pi->quantity_balance;
                    $balance_qty = $quantity;
                    $cost_row    = ['date' => date('Y-m-d'), 'product_id' => $product_id, 'sale_item_id' => 'sale_items.id', 'purchase_id' => $pi->purchase_id, 'transfer_id' => $pi->transfer_id, 'purchase_item_id' => $pi->id, 'quantity' => $pi->quantity_balance, 'purchase_net_unit_cost' => $pi->net_unit_cost, 'purchase_unit_cost' => $purchase_unit_cost, 'sale_net_unit_price' => $net_unit_price, 'sale_unit_price' => $unit_price, 'quantity_balance' => 0, 'inventory' => 1, 'option_id' => $option_id];
                }
            }
            $cost[] = $cost_row;
            if ($quantity == 0) {
                break;
            }
        }
        // get quantity from inventory movement
       
        // $quantity = $this->Inventory_model->get_current_stock($product_id, $warehouse_id);
       
        // if ($quantity <= 0) {
        //     $this->session->set_flashdata('error', sprintf(lang('quantity_out_of_stock_for_%s'), ($pi->product_name ?? $product_name)));
        //     redirect($_SERVER['HTTP_REFERER']);
        // }

        return $cost;
    }

    public function calculateDiscount($discount = null, $amount = 0, $order = null)
    {
        if ($discount && ($order || $this->Settings->product_discount)) {
            $discount = $discount.'%';
            $dpos = strpos($discount, '%');
            if ($dpos !== false) {
                $pds = explode('%', $discount);
                return $this->sma->formatDecimal(((($this->sma->formatDecimal($amount)) * (float) ($pds[0])) / 100), 4);
            }
            return $this->sma->formatDecimal($discount, 4);
        }
        return 0;
    }

    public function calculateOrderTax($order_tax_id = null, $amount = 0)
    {
        if ($this->Settings->tax2 != 0 && $order_tax_id) {
            if ($order_tax_details = $this->site->getTaxRateByID($order_tax_id)) {
                if ($order_tax_details->type == 1) {
                    return $this->sma->formatDecimal((($amount * $order_tax_details->rate) / 100), 4);
                }
                return $this->sma->formatDecimal($order_tax_details->rate, 4);
            }
        }
        return 0;
    }

    public function calculateTax($product_details = null, $tax_details = null, $custom_value = null, $c_on = null)
    {
        $value      = $custom_value ? $custom_value : (($c_on == 'cost') ? $product_details->cost : $product_details->price);
        $tax_amount = 0;
        $tax        = 0;
        if ($tax_details && $tax_details->type == 1 && $tax_details->rate != 0) {
            if ($product_details && $product_details->tax_method == 1) {
                $tax_amount = $this->sma->formatDecimal((($value) * $tax_details->rate) / 100, 4);
                $tax        = $this->sma->formatDecimal($tax_details->rate, 0) . '%';
            } else {
                $tax_amount = $this->sma->formatDecimal((($value) * $tax_details->rate) / (100 + $tax_details->rate), 4);
                $tax        = $this->sma->formatDecimal($tax_details->rate, 0) . '%';
            }
        } elseif ($tax_details && $tax_details->type == 2) {
            $tax_amount = $this->sma->formatDecimal($tax_details->rate);
            $tax        = $this->sma->formatDecimal($tax_details->rate, 0);
        }
        return ['id' => $tax_details ? $tax_details->id : null, 'tax' => $tax, 'amount' => $tax_amount];
    }

    public function check_customer_deposit($customer_id, $amount)
    {
        $customer = $this->getCompanyByID($customer_id);
        return $customer->deposit_amount >= $amount;
    }

    public function checkOverSold($product_id, $warehouse_id, $option_id = null)
    {
        $clause = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id];
        if ($os = $this->getPurchasedItem($clause)) {
            if ($os->quantity_balance < 0) {
                if ($pis = $this->getPurchasedItems($product_id, $warehouse_id, $option_id, true)) {
                    $quantity = $os->quantity_balance;
                    foreach ($pis as $pi) {
                        if ($pi->quantity_balance >= (0 - $quantity) && $quantity != 0) {
                            $balance = $pi->quantity_balance + $quantity;
                            $this->db->update('purchase_items', ['quantity_balance' => $balance], ['id' => $pi->id]);
                            $quantity = 0;
                            break;
                        } elseif ($quantity != 0) {
                            $quantity = $quantity + $pi->quantity_balance;
                            $this->db->update('purchase_items', ['quantity_balance' => 0], ['id' => $pi->id]);
                        }
                    }
                    $this->db->update('purchase_items', ['quantity_balance' => $quantity], ['id' => $os->id]);
                }
            }
        }
    }

    public function checkPermissions()
    {
        $q = $this->db->get_where('permissions', ['group_id' => $this->session->userdata('group_id')], 1);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }

    public function checkSlug($slug, $type = null)
    {
        if (!$type) {
            return $this->db->get_where('products', ['slug' => $slug], 1)->row();
        } elseif ($type == 'category') {
            return $this->db->get_where('categories', ['slug' => $slug], 1)->row();
        } elseif ($type == 'brand') {
            return $this->db->get_where('brands', ['slug' => $slug], 1)->row();
        }
        return false;
    }

    public function convertToBase($unit, $value)
    {
        switch ($unit->operator) {
            case '*':
                return $value / $unit->operation_value;
                break;
            case '/':
                return $value * $unit->operation_value;
                break;
            case '+':
                return $value - $unit->operation_value;
                break;
            case '-':
                return $value + $unit->operation_value;
                break;
            default:
                return $value;
        }
    }

    public function convertToUnit($unit, $value)
    {
        switch ($unit->operator) {
            case '*':
                return $value * $unit->operation_value;
                break;
            case '/':
                return $value / $unit->operation_value;
                break;
            case '+':
                return $value + $unit->operation_value;
                break;
            case '-':
                return $value - $unit->operation_value;
                break;
            default:
                return $value;
        }
    }

    public function costing($items)
    {
        $citems = [];
        foreach ($items as $item) {
            $option            = (isset($item['option_id']) && !empty($item['option_id']) && $item['option_id'] != 'null' && $item['option_id'] != 'false') ? $item['option_id'] : '';
            $pr                = $this->getProductByID($item['product_id']);
            $item['option_id'] = $option;
            if ($pr && $pr->type == 'standard') {
                if (isset($citems['p' . $item['product_id'] . 'o' . $item['option_id']])) {
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] += $item['quantity'];
                } else {
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']]              = $item;
                    $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'] = $item['quantity'];
                }
            } elseif ($pr && $pr->type == 'combo') {
                $wh          = $this->Settings->overselling ? null : $item['warehouse_id'];
                $combo_items = $this->getProductComboItems($item['product_id'], $wh);
                foreach ($combo_items as $combo_item) {
                    if ($combo_item->type == 'standard') {
                        if (isset($citems['p' . $combo_item->id . 'o' . $item['option_id']])) {
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']]['aquantity'] += ($combo_item->qty * $item['quantity']);
                        } else {
                            $cpr = $this->getProductByID($combo_item->id);
                            if ($cpr->tax_rate) {
                                $cpr_tax = $this->getTaxRateByID($cpr->tax_rate);
                                if ($cpr->tax_method) {
                                    $item_tax       = $this->sma->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / (100 + $cpr_tax->rate));
                                    $net_unit_price = $combo_item->unit_price - $item_tax;
                                    $unit_price     = $combo_item->unit_price;
                                } else {
                                    $item_tax       = $this->sma->formatDecimal((($combo_item->unit_price) * $cpr_tax->rate) / 100);
                                    $net_unit_price = $combo_item->unit_price;
                                    $unit_price     = $combo_item->unit_price + $item_tax;
                                }
                            } else {
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price     = $combo_item->unit_price;
                            }
                            $cproduct                                                              = ['product_id' => $combo_item->id, 'product_name' => $cpr->name, 'product_type' => $combo_item->type, 'quantity' => ($combo_item->qty * $item['quantity']), 'net_unit_price' => $net_unit_price, 'unit_price' => $unit_price, 'warehouse_id' => $item['warehouse_id'], 'item_tax' => $item_tax, 'tax_rate_id' => $cpr->tax_rate, 'tax' => ($cpr_tax->type == 1 ? $cpr_tax->rate . '%' : $cpr_tax->rate), 'option_id' => null, 'product_unit_id' => $cpr->unit];
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']]              = $cproduct;
                            $citems['p' . $combo_item->id . 'o' . $item['option_id']]['aquantity'] = ($combo_item->qty * $item['quantity']);
                        }
                    }
                }
            }
        }
        // $this->sma->print_arrays($combo_items, $citems);
        $cost = [];
        foreach ($citems as $item) {  
            $item['aquantity'] = $citems['p' . $item['product_id'] . 'o' . $item['option_id']]['aquantity'];
            $cost[] = $this->item_costing($item, true); 
        }
        
        return $cost;
    }

    public function get_expiring_qty_alerts()
    {
        $date = date('Y-m-d', strtotime('+3 months'));
        $this->db->select('COUNT(*) as alert_num')
        ->where('expiry !=', null)->where('expiry !=', '0000-00-00')
        ->where('quantity_balance >', 0)
        ->where('expiry <', $date);
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $res = $q->row();
            return (int) $res->alert_num;
        }
        return false;
    }

    public function get_setting()
    {
        $q = $this->db->get('settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function get_shop_payment_alerts()
    {
        $this->db->where('shop', 1)->where('attachment !=', null)->where('payment_status !=', 'paid');
        return $this->db->count_all_results('sales');
    }

    public function get_shop_sale_alerts()
    {
        $this->db->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
        ->where('sales.shop', 1)->where('sales.sale_status', 'completed')->where('sales.payment_status', 'paid')
        ->group_start()->where('deliveries.status !=', 'delivered')->or_where('deliveries.status IS NULL', null)->group_end();
        return $this->db->count_all_results('sales');
    }

    public function get_total_qty_alerts()
    {
        $this->db->group_start()->where('quantity <= alert_quantity', null, false)->or_where('quantity', null)->group_end()->where('track_quantity', 1);
        return $this->db->count_all_results('products');
    }

    public function getAddressByID($id)
    {
        return $this->db->get_where('addresses', ['id' => $id], 1)->row();
    }

    public function getAllBaseUnits()
    {
        $q = $this->db->get_where('units', ['base_unit' => null]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllBrands()
    {
        $q = $this->db->get('brands');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllCategories()
    {
        $this->db->where('parent_id', null)->or_where('parent_id', 0)->order_by('name');
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllCompanies($group_name)
    {
        $q = $this->db->get_where('companies', ['group_name' => $group_name]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllParentCompanies($group_name)
    {
        $q = $this->db->get_where('companies', ['group_name' => $group_name, 'level' => 1]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllChildCompanies($group_name)
    {
        $q = $this->db->get_where('companies', ['group_name' => $group_name, 'level' => 2]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllCurrencies()
    {
        $q = $this->db->get('currencies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllReturnItems($return_id)
    {
        $q = $this->db->get_where('return_items', ['return_id' => $return_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllReturnSupplierItems($return_id)
    {
        $q = $this->db->get_where('return_supplier_items', ['return_id' => $return_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPurchaseItems($purchase_id)
    {
        $q = $this->db->get_where('purchase_items', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllSaleItems($sale_id)
    {
        $q = $this->db->get_where('sale_items', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllTaxRates()
    {
        $q = $this->db->get('tax_rates');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllPharmacies()
    {
        $q = $this->db->get_where('warehouses', ['warehouse_type' => 'pharmacy']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getMainWarehouse()
    {
        $q = $this->db->get_where('warehouses', ['warehouse_type' => 'warehouse', 'goods_in_transit' => 0]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllWarehouses()
    {
        $q = $this->db->get('warehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAvgCost($item_batchno, $item_id){
        $avgCostQuery = "SELECT 
                    SUM(iv.quantity * iv.net_unit_cost) / SUM(iv.quantity) AS average_cost
                 FROM 
                    sma_inventory_movements iv
                 WHERE 
                    iv.product_id = '{$item_id}' 
                    AND iv.batch_number = '{$item_batchno}' 
                    AND iv.type IN ('purchase', 'adjustment_increase')";
        $avgCost = $this->db->query($avgCostQuery);
        //echo $this->db->last_query(); exit;
        $avgObj = $avgCost->row();
        if($avgObj){
            $average_cost = $avgObj->average_cost;
        }else{
            $average_cost = 0;
        }
        
        return $average_cost;
    }

    public function getRealAvgCost($item_batchno, $item_id){
        $avgCostQuery = "SELECT 
                    SUM(iv.quantity * iv.real_unit_cost) / SUM(iv.quantity) AS real_average_cost
                 FROM 
                    sma_inventory_movements iv
                 WHERE 
                    iv.product_id = '{$item_id}' 
                    AND iv.batch_number = '{$item_batchno}' 
                    AND iv.type IN ('purchase', 'adjustment_increase')";
        $avgCost = $this->db->query($avgCostQuery);
        $avgObj = $avgCost->row();
        if($avgObj){
            $average_cost = $avgObj->real_average_cost;
        }else{
            $average_cost = 0;
        }
        
        return $average_cost;
    }

    public function getAllCouriers()
    {
        $q = $this->db->get('sma_courier');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllShelf($id)
    {
        $q = $this->db->get_where('warehouse_shelf', ['warehouse_id' => $id]);
        if ($q->num_rows() > 0) {
            return $q->result_array();
        }
        return false;
    }

public function getallCountry()
{
    $this->db->order_by('name', 'ASC');
    $q = $this->db->get('countries');
    if ($q->num_rows() > 0) {
        foreach (($q->result()) as $row) {
            $data[] = $row;
        }
        return $data;
    }
    return false;
}

public function logVisitor() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $bots = [
        'Googlebot', 'Bingbot', 'Slurp', 'DuckDuckBot', 'Baiduspider',
        'YandexBot', 'Sogou', 'Exabot', 'facebot', 'ia_archiver'
    ];

    $isBot = false;
    
    foreach ($bots as $bot) {
        if (stripos($userAgent, $bot) !== false) {
            $isBot = true;
        }
    }

    $location = @file_get_contents("http://ip-api.com/json/{$ip}");
    
    if ($location) {
        $locationData = json_decode($location, true);
        $location = $locationData['city'] . ', ' . $locationData['regionName'] . ', ' . $locationData['country'];
    }else{
        $location = 'Unknown';
    }
    
    $landingUrl = $_SERVER['REQUEST_URI'];
    $accessTime = date('Y-m-d H:i:s');

    $this->db->insert('user_logs', [
        'ip_address' => $ip,
        'location' => $location,
        'is_bot' => $isBot,
        'user_agent' => $userAgent,
        'landing_url' => $landingUrl,
        'access_time' => $accessTime,
    ]);
}

 public function getallWCountry()
    {
        $q = $this->db->get('warehouses_country');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getAttachments($id, $type)
    {
        return $this->db->get_where('attachments', ['subject_id' => $id, 'subject_type' => $type])->result();
    }

    public function getBrandByID($id)
    {
        $q = $this->db->get_where('brands', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSMSServiceByName($name)
    {
        $q = $this->db->get_where('sms_services', ['name' => $name], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCountryByName($name)
    {
        $this->db->like('name', $name);
        $q = $this->db->get('countries', 1);

        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getCourierById($id)
    {
        $q = $this->db->get_where('courier', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCategoryByID($id)
    {
        $q = $this->db->get_where('categories', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCompanyByID($id)
    {
        $q = $this->db->get_where('companies', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCurrencyByCode($code)
    {
        $q = $this->db->get_where('currencies', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCustomerGroupByID($id)
    {
        $q = $this->db->get_where('customer_groups', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDateFormat($id)
    {
        $q = $this->db->get_where('date_format', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGiftCardByID($id)
    {
        $q = $this->db->get_where('gift_cards', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getGiftCardByNO($no)
    {
        $q = $this->db->get_where('gift_cards', ['card_no' => $no], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getNotifications()
    {
        $date = date('Y-m-d H:i:s', time());
        $this->db->where('from_date <=', $date);
        $this->db->where('till_date >=', $date);
        if (!$this->Owner) {
            if ($this->Supplier) {
                $this->db->where('scope', 4);
            } elseif ($this->Customer) {
                $this->db->where('scope', 1)->or_where('scope', 3);
            } elseif (!$this->Customer && !$this->Supplier) {
                $this->db->where('scope', 2)->or_where('scope', 3);
            }
        }
        $q = $this->db->get('notifications');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPriceGroupByID($id)
    {
        $q = $this->db->get_where('price_groups', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByCode($code)
    {
        $q = $this->db->get_where('products', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductByID($id)
    {
        $q = $this->db->get_where('products', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductComboItems($pid, $warehouse_id = null)
    {
        $this->db->select('products.id as id, combo_items.item_code as code, combo_items.quantity as qty, products.name as name, products.type as type, combo_items.unit_price as unit_price, warehouses_products.quantity as quantity')
            ->join('products', 'products.code=combo_items.item_code', 'left')
            ->join('warehouses_products', 'warehouses_products.product_id=products.id', 'left')
            ->group_by('combo_items.id');
        if ($warehouse_id) {
            $this->db->where('warehouses_products.warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('combo_items', ['combo_items.product_id' => $pid]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
        return false;
    }

    public function getProductGroupPrice($product_id, $group_id)
    {
        $q = $this->db->get_where('product_prices', ['price_group_id' => $group_id, 'product_id' => $product_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getProductVariants($product_id)
    {
        $q = $this->db->get_where('product_variants', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchaseByID($id)
    {
        $q = $this->db->get_where('purchases', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasedItem($clause)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        if (!isset($clause['option_id']) || empty($clause['option_id'])) {
            $this->db->group_start()->where('option_id', null)->or_where('option_id', 0)->group_end();
        }
        $q = $this->db->get_where('purchase_items', $clause);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getPurchasedItemsWithBatch($product_id, $warehouse_id, $batch_no, $option_id = null, $nonPurchased = false)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        $this->db->select('id, purchase_id, transfer_id, quantity, quantity_balance, batchno, net_unit_cost, unit_cost, item_tax, base_unit_cost,expiry');
        $this->db->where('product_id', $product_id)->where('warehouse_id', $warehouse_id)->where('batchno', $batch_no)->where('quantity_balance !=', 0); 
        if (!isset($option_id) || empty($option_id)) {
            $this->db->group_start()->where('option_id', null)->or_where('option_id', 0)->group_end();
        } else {
            $this->db->where('option_id', $option_id);
        }
        if ($nonPurchased) {
            $this->db->group_start()->where('purchase_id !=', null)->or_where('transfer_id !=', null)->group_end();
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $this->db->group_by('id');
        // $this->db->order_by('date', $orderby);
        $this->db->order_by('expiry', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchasedItems($product_id, $warehouse_id, $option_id = null, $nonPurchased = false)
    {
        $orderby = empty($this->Settings->accounting_method) ? 'asc' : 'desc';
        $this->db->select('id, purchase_id, transfer_id, quantity, quantity_balance, serial_number, batchno, net_unit_cost, unit_cost, item_tax, base_unit_cost,expiry');
        $this->db->where('product_id', $product_id)
        //->where('warehouse_id', $warehouse_id)
        ->where('quantity_balance !=', 0)
        ->where('quantity >', 0)
        ->where("batchno !=", null);
        /*if (!isset($option_id) || empty($option_id)) {
            $this->db->group_start()->where('option_id', null)->or_where('option_id', 0)->group_end();
        } else {
            $this->db->where('option_id', $option_id);
        }*/
        if ($nonPurchased) {
            $this->db->group_start()->where('purchase_id !=', null)->or_where('transfer_id !=', null)->group_end();
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $this->db->group_by('id');
        // $this->db->order_by('date', $orderby);
        $this->db->order_by('expiry', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $q = $this->db->get('purchase_items');
        //echo $this->db->last_query();exit;

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getPurchasePayments($purchase_id)
    {
        $q = $this->db->get_where('payments', ['purchase_id' => $purchase_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getRandomReference($len = 12)
    {
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result .= mt_rand(0, 9);
        }

        if ($this->getSaleByReference($result)) {
            $this->getRandomReference();
        }

        return $result;
    }

    public function getReference($field)
    {
        $q = $this->db->get_where('order_ref', ['ref_id' => '1'], 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            switch ($field) {
                case 'so':
                    $prefix = $this->Settings->sales_prefix;
                    break;
                case 'pos':
                    $prefix = isset($this->Settings->sales_prefix) ? $this->Settings->sales_prefix . '/POS' : '';
                    break;
                case 'qu':
                    $prefix = $this->Settings->quote_prefix;
                    break;
                case 'po':
                    $prefix = $this->Settings->purchase_prefix;
                    break;
                case 'to':
                    $prefix = $this->Settings->transfer_prefix;
                    break;
                case 'do':
                    $prefix = $this->Settings->delivery_prefix;
                    break;
                case 'pay':
                    $prefix = $this->Settings->payment_prefix;
                    break;
                case 'ppay':
                    $prefix = $this->Settings->ppayment_prefix;
                    break;
                case 'ex':
                    $prefix = $this->Settings->expense_prefix;
                    break;
                case 're':
                    $prefix = $this->Settings->return_prefix;
                    break;
                case 'rep':
                    $prefix = $this->Settings->returnp_prefix;
                    break;
                case 'qa':
                    $prefix = $this->Settings->qa_prefix;
                    break;
                default:
                    $prefix = '';
            }

            $ref_no = $prefix;

            if ($this->Settings->reference_format == 1) {
                $ref_no .= date('Y') . '/' . sprintf('%04s', $ref->{$field});
            } elseif ($this->Settings->reference_format == 2) {
                $ref_no .= date('Y') . '/' . date('m') . '/' . sprintf('%04s', $ref->{$field});
            } elseif ($this->Settings->reference_format == 3) {
                $ref_no .= sprintf('%04s', $ref->{$field});
            } else {
                $ref_no .= $this->getRandomReference();
            }

            return $ref_no;
        }
        return false;
    }

    public function getSaleByID($id)
    {
        $q = $this->db->get_where('sales', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSaleByReference($ref)
    {
        $this->db->like('reference_no', $ref, 'both');
        $q = $this->db->get('sales', 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSalePayments($sale_id)
    {
        $q = $this->db->get_where('payments', ['sale_id' => $sale_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSmsSettings()
    {
        $q = $this->db->get('sms_settings');
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getSubCategories($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get('categories');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getSubSpecialities($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get('specialities');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getSubTopics($parent_id)
    {
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get('topics');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getTaxRateByID($id)
    {
        $q = $this->db->get_where('tax_rates', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUnitByID($id)
    {
        $q = $this->db->get_where('units', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUnitsByBUID($base_unit)
    {
        $this->db->where('id', $base_unit)->or_where('base_unit', $base_unit)
        ->group_by('id')->order_by('id asc');
        $q = $this->db->get('units');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUpcomingEvents()
    {
        $dt = date('Y-m-d');
        $this->db->where('start >=', $dt)->order_by('start')->limit(5);
        if ($this->Settings->restrict_calendar) {
            $this->db->where('user_id', $this->session->userdata('user_id'));
        }

        $q = $this->db->get('calendar');

        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUserFromCompany($id = null)
    {
        if (!$id) {
            $id = $this->session->userdata('company_id');
        }
        $q = $this->db->get_where('companies', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUserByWarehouseID($id = null)
    {
        $q = $this->db->get_where('users', ['warehouse_id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAdjustmentStore($id = null)
    {
        $q = $this->db->get_where('warehouses', ['code' => 'adj'], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getCompanyLedgers()
    {
        $this->db
            ->select('sma_accounts_ledgers.*')
            ->from('sma_accounts_ledgers')
            ->order_by('sma_accounts_ledgers.name asc');
        $q = $this->db->get();
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data_res[] = $row;
            }
        } else {
            $data_res = array();
        }

        return $data_res;
    }

    public function getUser($id = null)
    {
        if (!$id) {
            $id = $this->session->userdata('user_id');
        }
        $q = $this->db->get_where('users', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUsersByGroupAndLocation($warehouse,$pharmacist_group){
        $q = $this->db->get_where('users', ['warehouse_id' => $warehouse, 'group_id' => $pharmacist_group]);
        if ($q->num_rows() > 0) {
            $ids = [];
            foreach (($q->result()) as $row) {
                $ids[] = $row->id;
            }
            return implode(',', $ids);
        }
        return false;
    }

    public function getUserGroupByName($name)
    {
        if($name){
            $q = $this->db->get_where('groups', ['name' => $name]);
        }
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUsersByGroup($group_id){
        if($group_id){
            $q = $this->db->get_where('users', ['group_id' => $group_id]);
        }
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getUserGroup($user_id = false)
    {
        if (!$user_id) {
            $user_id = $this->session->userdata('user_id');
        }
        $group_id = $this->getUserGroupID($user_id);
        $q        = $this->db->get_where('groups', ['id' => $group_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getUserGroupID($user_id = false)
    {
        $user = $this->getUser($user_id);
        return $user->group_id;
    }

    public function getWarehouseByID($id)
    {
        $q = $this->db->get_where('warehouses', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getExpiryFromBatch($product_id, $batchno, $warehouse_id){
        $q = $this->db->get_where('sma_purchase_items', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'batchno' => $batchno], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false; 
    }

    public function getGoodsTrasitWareHouse()
    {
        $q = $this->db->get_where('warehouses', ['goods_in_transit' => 1], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseProduct($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getWarehouseProducts($product_id, $batch_no = null, $warehouse_id = null)
    {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }

        if ($batch_no) {
            $this->db->where('batchno', $batch_no);
        }
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getWarehouseProductsVariants($option_id, $warehouse_id = null)
    {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('warehouses_products_variants', ['option_id' => $option_id]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function item_costing($item, $pi = null)
    {

        $item_quantity = $pi ? $item['aquantity'] : $item['quantity'];
        if (!isset($item['option_id']) || empty($item['option_id']) || $item['option_id'] == 'null') {
            $item['option_id'] = null;
        }
        if ($this->Settings->accounting_method != 2 && !$this->Settings->overselling) {
            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $unit                   = $this->getUnitByID($item['product_unit_id']);
                    $item['net_unit_price'] = $this->convertToBase($unit, $item['net_unit_price']);
                    $item['unit_price']     = $this->convertToBase($unit, $item['unit_price']);
                    $cost                   = $this->calculateCost($item['product_id'], $item['warehouse_id'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->getTaxRateByID($pr->tax_rate);
                            if ($pr->tax_method) {
                                $item_tax       = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / (100 + $pr_tax->rate));
                                $net_unit_price = $combo_item->unit_price - $item_tax;
                                $unit_price     = $combo_item->unit_price;
                            } else {
                                $item_tax       = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / 100);
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price     = $combo_item->unit_price + $item_tax;
                            }
                        } else {
                            $net_unit_price = $combo_item->unit_price;
                            $unit_price     = $combo_item->unit_price;
                        }
                        if ($pr->type == 'standard') {
                            $cost[] = $this->calculateCost($pr->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $pr->name, null, ($combo_item->qty * $item_quantity));
                        } else {
                            $cost[] = [['date' => date('Y-m-d'), 'product_id' => $pr->id, 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => ($combo_item->qty * $item['quantity']), 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $combo_item->unit_price, 'sale_unit_price' => $combo_item->unit_price, 'quantity_balance' => (0 - $item_quantity), 'inventory' => null]];
                        }
                    }
                } else {
                    $cost = [['date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'batchno' => $item['batch_no'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => (0 - $item_quantity), 'inventory' => null]];
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = [['date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'batchno' => $item['batch_no'], 'sale_unit_price' => $item['unit_price'], 'quantity_balance' => null, 'inventory' => null]];
            }
        } else {
            if ($this->getProductByID($item['product_id'])) {
                if ($item['product_type'] == 'standard') {
                    $cost = $this->calculateAVCost($item['product_id'], $item['warehouse_id'], $item['batch_no'], $item['net_unit_price'], $item['unit_price'], $item['quantity'], $item['product_name'], $item['option_id'], $item_quantity);
                } elseif ($item['product_type'] == 'combo') {
                    $combo_items = $this->getProductComboItems($item['product_id'], $item['warehouse_id']);
                    foreach ($combo_items as $combo_item) {
                        $pr = $this->getProductByCode($combo_item->code);
                        if ($pr->tax_rate) {
                            $pr_tax = $this->getTaxRateByID($pr->tax_rate);
                            if ($pr->tax_method) {
                                $item_tax       = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / (100 + $pr_tax->rate));
                                $net_unit_price = $combo_item->unit_price - $item_tax;
                                $unit_price     = $combo_item->unit_price;
                            } else {
                                $item_tax       = $this->sma->formatDecimal((($combo_item->unit_price) * $pr_tax->rate) / 100);
                                $net_unit_price = $combo_item->unit_price;
                                $unit_price     = $combo_item->unit_price + $item_tax;
                            }
                        } else {
                            $net_unit_price = $combo_item->unit_price;
                            $unit_price     = $combo_item->unit_price;
                        }
                        $cost[] = $this->calculateAVCost($combo_item->id, $item['warehouse_id'], $net_unit_price, $unit_price, ($combo_item->qty * $item['quantity']), $item['product_name'], $item['option_id'], ($combo_item->qty * $item_quantity));
                    }
                } else {
                    $cost = [['date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'batchno' => $item['batch_no'], 'quantity_balance' => null, 'inventory' => null]];
                }
            } elseif ($item['product_type'] == 'manual') {
                $cost = [['date' => date('Y-m-d'), 'product_id' => $item['product_id'], 'sale_item_id' => 'sale_items.id', 'purchase_item_id' => null, 'quantity' => $item['quantity'], 'purchase_net_unit_cost' => 0, 'purchase_unit_cost' => 0, 'sale_net_unit_price' => $item['net_unit_price'], 'sale_unit_price' => $item['unit_price'], 'batchno' => $item['batch_no'], 'quantity_balance' => null, 'inventory' => null]];
            }
        }
        
        return $cost;
    }

    public function log(string $name, array $model)
    {
        $detail = $name . ' is being deleted by ' . $this->session->userdata('username') . ' (User Id: ' . $this->session->userdata('user_id') . ')';
        $this->db->insert('logs', ['detail' => $detail, 'model' => json_encode($model)]);
    }

    public function modal_js()
    {
        return '<script type="text/javascript">' . file_get_contents($this->data['assets'] . 'js/modal.js') . '</script>';
    }

    public function setAdjustmentPurchaseItem($clause, $qty, $avz_item_code){
        if ($product = $this->getProductByID($clause['product_id'])) {
            $vat = $clause['vat'];
            unset($clause['vat']);

            if ($pi = $this->getPurchasedItem($clause)) {
                if ($pi->quantity_balance > 0) {
                    $quantity_balance = $pi->quantity_balance + $qty;
                    log_message('error', 'More than zero: ' . $quantity_balance . ' = ' . $pi->quantity_balance . ' + ' . $qty . ' PI: ' . print_r($pi, true));
                } else {
                    $quantity_balance = $pi->quantity_balance + $qty;
                    log_message('error', 'Less than zero: ' . $quantity_balance . ' = ' . $pi->quantity_balance . ' + ' . $qty . ' PI: ' . print_r($pi, true));
                }
                return $this->db->update('purchase_items', ['quantity_balance' => $quantity_balance], ['id' => $pi->id]);
            }

            $unit                        = $this->getUnitByID($product->unit);
            $clause['product_unit_id']   = $product->unit;
            $clause['product_unit_code'] = $unit->code;
            $clause['product_code']      = $product->code;
            $clause['product_name']      = $product->name;
            $clause['purchase_id']       = $clause['transfer_id']       = $clause['item_tax']       = null;
            $clause['net_unit_cost']     = $clause['real_unit_cost']     = $clause['unit_cost'];
            $clause['quantity_balance']  = $clause['quantity']  = $clause['unit_quantity']  = $clause['quantity_received']  = $qty;
            $clause['subtotal']          = ($clause['net_unit_cost'] * $qty);
            $clause['avz_item_code']     = $avz_item_code;
            
            if (isset($vat) && $vat != 0) {
                $tax_details           = $this->site->getTaxRateByID($vat);
                $ctax                  = $this->calculateTax($product, $tax_details, $clause['net_unit_cost']);
                $item_tax              = $clause['item_tax']              = $ctax['amount'];
                $tax                   = $clause['tax']                   = $ctax['tax'];
                $clause['tax_rate_id'] = $tax_details->id;
                if ($product->tax_method != 1) {
                    $clause['net_unit_cost'] = $clause['net_unit_cost'] - $item_tax;
                    $clause['unit_cost']     = $clause['net_unit_cost'];
                } else {
                    $clause['net_unit_cost'] = $clause['net_unit_cost'];
                    $clause['unit_cost']     = $clause['net_unit_cost'] + $item_tax;
                }
                $pr_item_tax = $this->sma->formatDecimal($item_tax * $clause['unit_quantity'], 4);
                if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                    $clause['gst']  = $gst_data['gst'];
                    $clause['cgst'] = $gst_data['cgst'];
                    $clause['sgst'] = $gst_data['sgst'];
                    $clause['igst'] = $gst_data['igst'];
                }
                $clause['subtotal'] = (($clause['net_unit_cost'] * $clause['unit_quantity']) + $pr_item_tax);
            }
            $clause['status']    = 'received';
            $clause['date']      = date('Y-m-d');
            $clause['option_id'] = !empty($clause['option_id']) && is_numeric($clause['option_id']) ? $clause['option_id'] : null;
            log_message('error', 'Why else: ' . print_r($clause, true));
            
            return $this->db->insert('purchase_items', $clause);
        }
        return false;
    }

    public function setPurchaseItem($clause, $qty, $type = null)
    {
        if ($product = $this->getProductByID($clause['product_id'])) {
            if ($pi = $this->getPurchasedItem($clause) && $type != 'supplier') {
                if ($pi->quantity_balance > 0) {
                    $quantity_balance = $pi->quantity_balance + $qty;
                    log_message('error', 'More than zero: ' . $quantity_balance . ' = ' . $pi->quantity_balance . ' + ' . $qty . ' PI: ' . print_r($pi, true));
                } else {
                    $quantity_balance = $pi->quantity_balance + $qty;
                    log_message('error', 'Less than zero: ' . $quantity_balance . ' = ' . $pi->quantity_balance . ' + ' . $qty . ' PI: ' . print_r($pi, true));
                }
                return $this->db->update('purchase_items', ['quantity_balance' => $quantity_balance], ['id' => $pi->id]);
            }
            $unit                        = $this->getUnitByID($product->unit);
            $clause['product_unit_id']   = $product->unit;
            $clause['product_unit_code'] = $unit->code;
            $clause['product_code']      = $product->code;
            $clause['product_name']      = $product->name;
            $clause['purchase_id']       = $clause['transfer_id']       = $clause['item_tax']       = null;
            $clause['net_unit_cost']     = $clause['real_unit_cost']     = $clause['unit_cost']     = $product->cost;
            $clause['quantity_balance']  = $clause['quantity']  = $clause['unit_quantity']  = $clause['quantity_received']  = $qty;
            $clause['subtotal']          = ($product->cost * $qty);
            if (isset($product->tax_rate) && $product->tax_rate != 0) {
                $tax_details           = $this->site->getTaxRateByID($product->tax_rate);
                $ctax                  = $this->calculateTax($product, $tax_details, $product->cost);
                $item_tax              = $clause['item_tax']              = $ctax['amount'];
                $tax                   = $clause['tax']                   = $ctax['tax'];
                $clause['tax_rate_id'] = $tax_details->id;
                if ($product->tax_method != 1) {
                    $clause['net_unit_cost'] = $product->cost - $item_tax;
                    $clause['unit_cost']     = $product->cost;
                } else {
                    $clause['net_unit_cost'] = $product->cost;
                    $clause['unit_cost']     = $product->cost + $item_tax;
                }
                $pr_item_tax = $this->sma->formatDecimal($item_tax * $clause['unit_quantity'], 4);
                if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                    $clause['gst']  = $gst_data['gst'];
                    $clause['cgst'] = $gst_data['cgst'];
                    $clause['sgst'] = $gst_data['sgst'];
                    $clause['igst'] = $gst_data['igst'];
                }
                $clause['subtotal'] = (($clause['net_unit_cost'] * $clause['unit_quantity']) + $pr_item_tax);
            }
            $clause['status']    = 'received';
            $clause['date']      = date('Y-m-d');
            $clause['option_id'] = !empty($clause['option_id']) && is_numeric($clause['option_id']) ? $clause['option_id'] : null;
            log_message('error', 'Why else: ' . print_r($clause, true));
            return $this->db->insert('purchase_items', $clause);
        }
        return false;
    }

    public function syncProductQty($product_id, $warehouse_id, $batchno)
    {
        $balance_qty    = $this->getBalanceQuantity($product_id);
        $wh_old_balance_qty = $this->getBalanceQuantity($product_id, $batchno, $warehouse_id);
        //$wh_sale_qty = $this->getSaleQuantity($product_id, $warehouse_id);
        //$wh_return_qty = $this->getCustomerReturnsQuantity($product_id, $warehouse_id);
        
        //$wh_balance_qty = $wh_old_balance_qty - $wh_sale_qty + $wh_return_qty;
        $wh_balance_qty = $wh_old_balance_qty;

        if ($this->db->update('products', ['quantity' => $balance_qty], ['id' => $product_id])) {
            if ($this->getWarehouseProducts($product_id, $batchno, $warehouse_id)) {
                $this->db->update('warehouses_products', ['quantity' => $wh_balance_qty], ['product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'batchno' => $batchno]);
            } else {
                if (!$wh_balance_qty) {
                    $wh_balance_qty = 0;
                }
                $product = $this->site->getProductByID($product_id);
                if ($product) {
                    $this->db->insert('warehouses_products', ['quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'avg_cost' => $product->cost, 'batchno' => $batchno]);
                } else {
                    $this->db->insert('warehouses_products', ['quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'batchno' => $batchno]);
                }
            }

            $purchaseObj = $this->getExpiryFromBatch($product_id, $batchno, $warehouse_id);
            $this->db->update('warehouses_products', ['expiry' => $purchaseObj->expiry], ['product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'batchno' => $batchno]);

            return true;
        }
        return false;
    }

    public function syncPurchaseItems($data = [])
    {
        if (!empty($data)) {
            foreach ($data as $items) {
                foreach ($items as $item) {
                    if (isset($item['pi_overselling'])) {
                        unset($item['pi_overselling']);
                        $option_id = (isset($item['option_id']) && !empty($item['option_id'])) ? $item['option_id'] : null;
                        $clause    = ['purchase_id' => null, 'transfer_id' => null, 'product_id' => $item['product_id'], 'warehouse_id' => $item['warehouse_id'], 'batchno' => $item['batch_no'], 'option_id' => $option_id];
                        
                        if ($pi = $this->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance + $item['quantity_balance'];
                            $this->db->update('purchase_items', ['quantity_balance' => $quantity_balance], ['id' => $pi->id]);
                        } else {
                            $clause['quantity']         = 0;
                            $clause['item_tax']         = 0;
                            $clause['quantity_balance'] = $item['quantity_balance'];
                            $clause['status']           = 'received';
                            $clause['option_id']        = !empty($clause['option_id']) && is_numeric($clause['option_id']) ? $clause['option_id'] : null;
                            $this->db->insert('purchase_items', $clause);
                        }
                    } elseif (!isset($item['overselling']) || empty($item['overselling'])) {
                        if ($item['inventory']) {
                            $this->db->update('purchase_items', ['quantity_balance' => $item['quantity_balance']], ['id' => $item['purchase_item_id']]);
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function addProdQuantityOnholdRequest($sale_id, $items){

        if(isset($items) && !empty($items)) {

            foreach ($items as $item) {
                $virtual_pharmacy = 0;
                $query = $this->db->select('warehouses_products.*')
                        ->from('warehouses_products')
                        ->where('warehouses_products.warehouse_id', 6)
                        ->where('warehouses_products.product_id', $item->product_id);
                $result = $query->get();
                if ($result->num_rows() > 0) {
                    $virtual_pharmacy = 1;
                }
                
                $this->db->insert('product_qty_onhold_request',
                 ['sale_id' => $sale_id, 
                 'product_id' => $item->product_id, 
                 'quantity' => intval($item->quantity), 
                 'product_code' => $item->product_code,
                 'product_name' => $item->product_name,
                 'customer_id' => $item->customer_id,
                 'customer_name' => $item->customer_name,
                 'status' => 'onhold',
                 'virtual_pharmacy' => $virtual_pharmacy,
                 'date_created' => date('Y-m-d H:i:s')
                ]);
            }
        }
    }

    public function syncPurchasePayments($id)
    {
        $purchase = $this->getPurchaseByID($id);
        $paid     = 0;
        if ($payments = $this->getPurchasePayments($id)) {
            foreach ($payments as $payment) {
                $paid += $payment->amount;
            }
        }

        $payment_status = $paid <= 0 ? 'pending' : $purchase->payment_status;
        if ($this->sma->formatDecimal($purchase->grand_total) > $this->sma->formatDecimal($paid) && $paid > 0) {
            $payment_status = 'partial';
        } elseif ($this->sma->formatDecimal($purchase->grand_total) <= $this->sma->formatDecimal($paid)) {
            $payment_status = 'paid';
        }

        if ($this->db->update('purchases', ['paid' => $paid, 'payment_status' => $payment_status], ['id' => $id])) {
            return true;
        }

        return false;
    }

    public function syncQuantityReturn($return_id, $product_id = null)
    {
        if ($return_id) {
            $warehouses = $this->getAllWarehouses();
            $return_items = $this->getAllReturnItems($return_id);
            foreach ($return_items as $item) {
                if ($item->product_type == 'standard') {
                    $this->syncProductQty($item->product_id, $item->warehouse_id, $item->batch_no);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                } elseif ($item->product_type == 'combo') {
                    $wh          = $this->Settings->overselling ? null : $item->warehouse_id;
                    $combo_items = $this->getProductComboItems($item->product_id, $wh);
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') {
                            $this->syncProductQty($combo_item->id, $item->warehouse_id, $item->batch_no);
                        }
                    }
                }
            }
        }
    }

    public function syncQuantityReturnSupplier($return_id, $product_id = null)
    {
        if ($return_id) {
            $warehouses = $this->getAllWarehouses();
            $return_items = $this->getAllReturnSupplierItems($return_id);
            foreach ($return_items as $item) {
                if ($item->product_type == 'standard') {
                    $this->syncProductQty($item->product_id, $item->warehouse_id, $item->batch_no);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                } elseif ($item->product_type == 'combo') {
                    $wh          = $this->Settings->overselling ? null : $item->warehouse_id;
                    $combo_items = $this->getProductComboItems($item->product_id, $wh);
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') {
                            $this->syncProductQty($combo_item->id, $item->warehouse_id, $item->batch_no);
                        }
                    }
                }
            }
        }
    }

    public function syncQuantity($sale_id = null, $purchase_id = null, $oitems = null, $product_id = null, $batch_no = null)
    {
        if ($sale_id) {
            $sale_items = $this->getAllSaleItems($sale_id);
            foreach ($sale_items as $item) {
                if ($item->product_type == 'standard') {
                    $this->syncProductQty($item->product_id, $item->warehouse_id, $item->batch_no);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                } elseif ($item->product_type == 'combo') {
                    $wh          = $this->Settings->overselling ? null : $item->warehouse_id;
                    $combo_items = $this->getProductComboItems($item->product_id, $wh);
                    foreach ($combo_items as $combo_item) {
                        if ($combo_item->type == 'standard') {
                            $this->syncProductQty($combo_item->id, $item->warehouse_id, $item->batch_no);
                        }
                    }
                }
            }
        } elseif ($purchase_id) {
            $purchase_items = $this->getAllPurchaseItems($purchase_id);
            foreach ($purchase_items as $item) {
                $this->syncProductQty($item->product_id, $item->warehouse_id, $item->batchno);
                $this->checkOverSold($item->product_id, $item->warehouse_id);
                if (isset($item->option_id) && !empty($item->option_id)) {
                    $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    $this->checkOverSold($item->product_id, $item->warehouse_id, $item->option_id);
                }
            }
        } elseif ($oitems) {
            foreach ($oitems as $item) {
                if (isset($item->product_type)) {
                    if ($item->product_type == 'standard') {
                        $this->syncProductQty($item->product_id, $item->warehouse_id, $item->batchno);
                        if (isset($item->option_id) && !empty($item->option_id)) {
                            $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                        }
                    } elseif ($item->product_type == 'combo') {
                        $combo_items = $this->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if ($combo_item->type == 'standard') {
                                $this->syncProductQty($combo_item->id, $item->warehouse_id, $item->batchno);
                            }
                        }
                    }
                } else {
                    $this->syncProductQty($item->product_id, $item->warehouse_id, $item->batchno);
                    if (isset($item->option_id) && !empty($item->option_id)) {
                        $this->syncVariantQty($item->option_id, $item->warehouse_id, $item->product_id);
                    }
                }
            }
        } elseif ($product_id) {
            $warehouses = $this->getAllWarehouses();
            foreach ($warehouses as $warehouse) {
                $this->syncProductQty($product_id, $warehouse->id, $batch_no);
                $this->checkOverSold($product_id, $warehouse->id);
                $quantity           = 0;
                $warehouse_products = $this->getWarehouseProducts($product_id);
                foreach ($warehouse_products as $product) {
                    $quantity += $product->quantity;
                }
                $this->db->update('products', ['quantity' => $quantity], ['id' => $product_id]);
                if ($product_variants = $this->getProductVariants($product_id)) {
                    foreach ($product_variants as $pv) {
                        $this->syncVariantQty($pv->id, $warehouse->id, $product_id);
                        $this->checkOverSold($product_id, $warehouse->id, $pv->id);
                        $quantity           = 0;
                        $warehouse_variants = $this->getWarehouseProductsVariants($pv->id);
                        foreach ($warehouse_variants as $variant) {
                            $quantity += $variant->quantity;
                        }
                        $this->db->update('product_variants', ['quantity' => $quantity], ['id' => $pv->id]);
                    }
                }
            }
        }
    }

    public function syncSalePayments($id)
    {
        $sale = $this->getSaleByID($id);
        if ($payments = $this->getSalePayments($id)) {
            $paid        = 0;
            $grand_total = $sale->grand_total + $sale->rounding;
            foreach ($payments as $payment) {
                $paid += $payment->amount;
            }

            $payment_status = $paid == 0 ? 'pending' : $sale->payment_status;
            if ($this->sma->formatDecimal($grand_total) == 0 || $this->sma->formatDecimal($grand_total) <= $this->sma->formatDecimal($paid)) {
                $payment_status = 'paid';
            } elseif ($sale->due_date <= date('Y-m-d') && !$sale->sale_id && $sale->sale_status == 'completed') {
                $payment_status = 'due';
            } elseif ($paid != 0) {
                $payment_status = 'partial';
            }

            if ($this->db->update('sales', ['paid' => $paid, 'payment_status' => $payment_status], ['id' => $id])) {
                return true;
            }
        } else {
            //$payment_status = ($sale->due_date <= date('Y-m-d')) ? 'due' : 'pending';
            //if ($this->db->update('sales', ['paid' => 0, 'payment_status' => $payment_status], ['id' => $id])) {
            //    return true;
            //}
            return true;
        }

        return false;
    }

    public function syncVariantQty($variant_id, $warehouse_id, $product_id = null)
    {
        $balance_qty    = $this->getBalanceVariantQuantity($variant_id);
        $wh_balance_qty = $this->getBalanceVariantQuantity($variant_id, $warehouse_id);
        if ($this->db->update('product_variants', ['quantity' => $balance_qty], ['id' => $variant_id])) {
            if ($this->getWarehouseProductsVariants($variant_id, $warehouse_id)) {
                $this->db->update('warehouses_products_variants', ['quantity' => $wh_balance_qty], ['option_id' => $variant_id, 'warehouse_id' => $warehouse_id]);
            } else {
                if ($wh_balance_qty) {
                    $this->db->insert('warehouses_products_variants', ['quantity' => $wh_balance_qty, 'option_id' => $variant_id, 'warehouse_id' => $warehouse_id, 'product_id' => $product_id]);
                }
            }
            return true;
        }
        return false;
    }

    public function updateInvoiceStatus()
    {
        $date = date('Y-m-d');
        $q    = $this->db->get_where('invoices', ['status' => 'unpaid']);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if ($row->due_date < $date) {
                    $this->db->update('invoices', ['status' => 'due'], ['id' => $row->id]);
                }
            }
            $this->db->update('settings', ['update' => $date], ['setting_id' => '1']);
            return true;
        }
    }

    public function updateReference($field)
    {
        $q = $this->db->get_where('order_ref', ['ref_id' => '1'], 1);
        if ($q->num_rows() > 0) {
            $ref = $q->row();
            $this->db->update('order_ref', [$field => $ref->{$field} + 1], ['ref_id' => '1']);
            return true;
        }
        return false;
    }

    private function getCustomerReturnsQuantity($product_id, $warehouse_id = null)
    {
        $this->db->select('SUM(COALESCE(sma_return_items.quantity, 0)) as stock', false)
                 ->from('sma_returns')
                 ->join('sma_return_items', 'sma_returns.id = sma_return_items.return_id')
                 ->where('sma_return_items.product_id', $product_id);

        if ($warehouse_id) {
            $this->db->where('sma_returns.warehouse_id', $warehouse_id);
        }
        $q =  $this->db->get();
        if($q !== false)
        {
            if ($q->num_rows() > 0) {
                $data = $q->row();
                return $data->stock;
            } 
        }
        
        return 0;
    }

    private function getSaleQuantity($product_id, $warehouse_id = null)
    {
        $this->db->select('SUM(COALESCE(sma_sale_items.quantity, 0)) as stock', false)
                 ->from('sma_sales')
                 ->join('sma_sale_items', 'sma_sales.id = sma_sale_items.sale_id')
                 //->where('sale_status', 'completed')
                 ->where('sma_sale_items.product_id', $product_id);

        if ($warehouse_id) {
            $this->db->where('sma_sales.warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('sale_status', 'completed')->group_end();
        $q =  $this->db->get();
        if($q !== false)
        {
            if ($q->num_rows() > 0) {
                $data = $q->row();
                return $data->stock;
            } 
        }
        
        return 0;
    }

    private function getBalanceQuantity($product_id, $batch_no = null, $warehouse_id = null)
    {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', false);
        $this->db->where('product_id', $product_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        if ($batch_no) {
            $this->db->where('batchno', $batch_no);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    private function getBalanceVariantQuantity($variant_id, $warehouse_id = null)
    {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', false);
        $this->db->where('option_id', $variant_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('status', 'received')->or_where('status', 'partial')->group_end();
        $q = $this->db->get('purchase_items');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    public function getProductSupplierBatchesData($product_id, $warehouse, $supplier_id)
    {  
         
        $this->db->select(' inv.product_id, inv.batch_number as batchno ,SUM(inv.quantity) as quantity, 
        inv.location_id as warehouse_id, inv.net_unit_cost, inv.real_unit_cost, inv.expiry_date as expiry,item.expiry,
        item.unit_cost,item.real_unit_cost,item.sale_price,item.discount, item.discount1, item.discount2, item.main_net,
         item.tax_rate_id, item.tax

        ');
        $this->db->from('sma_inventory_movements inv'); 
        $this->db->join('sma_purchase_items item', 'item.batchno=inv.batch_number AND item.product_id=inv.product_id');  
        $this->db->join('purchases po', 'po.id=item.purchase_id  AND po.supplier_id='.$supplier_id);   
        $this->db->where('inv.location_id',$warehouse);
        $this->db->where('inv.product_id',$product_id); 
        $this->db->group_by('inv.batch_number'); 
        $this->db->having('SUM(inv.quantity)>=0'); 
	    $query = $this->db->get();
        // echo $this->db->last_query(); exit; 
        if($query->num_rows() > 0){
            foreach (($query->result()) as $row) {

                $query = $this->db->get_where('sma_purchase_items', ['product_id' => $product_id, 'warehouse_id' => $warehouse, 'batchno' => $row->batchno, 'sale_price >' => 0], 1);
                if ($query->num_rows() > 0) {
                    $rowp = $query->row();
                    $batch_sale_price = $rowp->sale_price;
                }else{
                    $batch_sale_price = 0;
                }
                $row->batch_sale_price = $batch_sale_price;
                $data[] = $row; 
            }
          //  echo '<pre>'; print_r($data); exit;  
          return $data; 
         }  
        return false;
    } 

    public function getProductSupplierBatchesData_BK($product_id, $warehouse, $supplier_id)
    {  
         
        $this->db->select(' inv.product_id, inv.batch_number as batchno ,SUM(inv.quantity) as quantity, inv.location_id as warehouse_id, inv.net_unit_cost, inv.real_unit_cost, wp.rack, wp.avg_cost, inv.expiry_date as expiry, wp.purchase_cost');
        $this->db->from('sma_inventory_movements inv');
        $this->db->join('warehouses_products wp', 'wp.batchno=inv.batch_number AND  wp.product_id= inv.product_id and wp.warehouse_id=inv.location_id', 'LEFT'); 
        $this->db->join('purchases po', 'po.id=inv.reference_id AND inv.type="purchase" AND  po.supplier_id='.$supplier_id);   
        $this->db->where('inv.location_id',$warehouse);
        $this->db->where('inv.product_id',$product_id); 
        $this->db->group_by('inv.batch_number'); 
        $this->db->having('SUM(inv.quantity)>=0'); 
	    $query = $this->db->get();
        //echo $this->db->last_query(); exit; 
        if($query->num_rows() > 0){
            foreach (($query->result()) as $row) {

                $query = $this->db->get_where('sma_purchase_items', ['product_id' => $product_id, 'warehouse_id' => $warehouse, 'batchno' => $row->batchno, 'sale_price >' => 0], 1);
                if ($query->num_rows() > 0) {
                    $rowp = $query->row();
                    $batch_sale_price = $rowp->sale_price;
                }else{
                    $batch_sale_price = 0;
                }
                $row->batch_sale_price = $batch_sale_price;
                $data[] = $row; 
            }
          //  echo '<pre>'; print_r($data); exit;  
          return $data; 
         }  
        return false;
    } 

     public function getProductBatchesData($product_id, $warehouse)
    {  
         
        $this->db->select(' inv.product_id, inv.batch_number as batchno ,SUM(inv.quantity) as quantity, inv.net_unit_cost, inv.location_id as warehouse_id, wp.rack, wp.avg_cost, inv.expiry_date as expiry, wp.purchase_cost');
        $this->db->from('sma_inventory_movements inv');
        $this->db->join('warehouses_products wp', 'wp.batchno=inv.batch_number AND  wp.product_id= inv.product_id and wp.warehouse_id=inv.location_id', 'LEFT');   
        $this->db->where('inv.location_id',$warehouse);
        $this->db->where('inv.product_id',$product_id); 
        $this->db->group_by('inv.batch_number, inv.expiry_date'); 
        $this->db->having('SUM(inv.quantity)>=0'); 
	    $query = $this->db->get();
        // echo $this->db->last_query();exit; 
        if($query->num_rows() > 0){
            foreach (($query->result()) as $row) {

                $query = $this->db->get_where('sma_purchase_items', ['product_id' => $product_id, 'warehouse_id' => $warehouse, 'batchno' => $row->batchno, 'sale_price >' => 0], 1);
                if ($query->num_rows() > 0) {
                    $rowp = $query->row();
                    $batch_sale_price = $rowp->sale_price;
                }else{
                    $batch_sale_price = 0;
                }
                $row->batch_sale_price = $batch_sale_price;
                $data[] = $row; 
            }
          //  echo '<pre>'; print_r($data); exit;  
          return $data; 
         }  
        return false;
    } 

    public function getProductBatchesData__BK($product_id, $warehouse)
    {
        //$q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse]);
        $q = $this->db->get_where('warehouses_products', ['product_id' => $product_id, 'warehouse_id' => $warehouse]);  
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {

                $query = $this->db->get_where('sma_purchase_items', ['product_id' => $product_id, 'warehouse_id' => $warehouse, 'batchno' => $row->batchno, 'sale_price >' => 0], 1);
                if ($query->num_rows() > 0) {
                    $rowp = $query->row();
                    $batch_sale_price = $rowp->sale_price;
                }else{
                    $batch_sale_price = 0;
                }

                //if($this->db->platform == 'pharma'){
                   $total_batch_quantity = $this->Inventory_model->get_current_stock( $product_id, $warehouse, $row->batchno);
                   $row->quantity = $total_batch_quantity;
                // }

                $row->batch_sale_price = $batch_sale_price;
                $data[] = $row;
            }
            // echo '<pre>'; print_r($data); exit; 
            return $data;
        }
        return false;
    }

    public function getAllDepartments()
    {
        $q = $this->db->get('departments');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getAllEmployees()
    {
        $q = $this->db->get('employees');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getCompanyByParentCode($parent_id)
    {
        $q = $this->db->get_where('companies', ['parent_code' => $parent_id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function deleteAccountingEntry($id, $type){

        if($type == 'purchase'){ 
            $q = $this->db->get_where('sma_accounts_entries', ['pid' => $id], 1);
        }else if($type == 'sale'){
            $q = $this->db->get_where('sma_accounts_entries', ['sid' => $id], 1);
        }else if($type == 'transfer'){
            $q = $this->db->get_where('sma_accounts_entries', ['tid' => $id], 1);
        }else if($type == 'return_supplier'){
            $q = $this->db->get_where('sma_accounts_entries', ['rsid' => $id], 1);
        }else if($type == 'return_customer'){
            $q = $this->db->get_where('sma_accounts_entries', ['rid' => $id], 1);
        }

        if ($q->num_rows() > 0) {
            $result = $q->row();

            if ($this->db->delete('sma_accounts_entries', ['id' => $result->id])) {
                $this->db->delete('sma_accounts_entryitems', ['entry_id' => $result->id]);
                return true;
            }
        }else{
            return false;
        }
        
    }

    public function getJournalEntryByTypeId($type='', $type_id)
    {
        if($type == '' || $type_id == '') {
            return false;
        }
        if($type == 'purchase') {
            $this->db->where('pid',$type_id); 
            $q = $this->db->get_where('sma_accounts_entries', ['pid' => $type_id], 1);
        }
        if($type == 'return_supplier') {
            $this->db->where('rsid',$type_id); 
            $q = $this->db->get_where('sma_accounts_entries', ['rsid' => $type_id], 1);
        }
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getAllPharmacists(){
        $q = $this->db->get_where('users', ['group_id' => 8]);
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
}
