<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pay_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addReferrer($data)
    {
        if ($this->db->insert('referrer', $data)) {
            return true;
        }
        return false;
    }

    public function addPayment($data)
    {
        if ($this->db->insert('payments', $data)) {
            $this->site->updateReference('pay');
            return true;
        }
        return false;
    }

    public function getCompanyByID($id)
    {
        return $this->db->get_where('companies', ['id' => $id])->row();
    }

    public function getCompanyAddress($id)
    {
        return $this->db->get_where('sma_addresses', ['company_id' => $id])->row();
    }

    public function getAddressByID($id)
    {
        return $this->db->get_where('addresses', ['id' => $id], 1)->row();
    }
    
    public function updateRefundStatus($rid,$data)
    {
        $this->db->update('refund', $data, ['id' => $rid]);
    }

    public function getPaymentByID($id)
    {
        return $this->db->get_where('payments', ['id' => $id])->row();
    }

    public function getDirectPaySettings()
    {
        return $this->db->get_where('sma_directpay', ['id' => 1])->row();
    }

    public function getPaypalSettings()
    {
        return $this->db->get_where('paypal', ['id' => 1])->row();
    }

    public function getCurrencyByCode($code)
    {
        return $this->db->get_where('currencies', ['code' => $code], 1)->row();
    }

    public function getSaleByID($id)
    {
        return $this->db->get_where('sales', ['id' => $id])->row();
    }

    public function getSaleItems($sale_id)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant, sales.customer_id as customer_id, sales.customer as customer_name ')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->join('sales', 'sales.id=sale_items.sale_id', 'left')
            ->where('sales.id', $sale_id)->group_by('sale_items.id')->order_by('id', 'asc');
        return $this->db->get('sale_items')->result();
    }

    public function getSettings()
    {
        return $this->db->get('settings')->row();
    }

    public function getSkrillSettings()
    {
        return $this->db->get_where('skrill', ['id' => 1])->row();
    }

    public function updatePayment($id, $status, $note = null){
        $this->db->update('sales', ['payment_status' => $status, 'note' => $note], ['id' => $id]);
    }

    public function updateStatus($id, $status, $note = null)
    {
        $sale  = $this->getSaleByID($id);
        $items = $this->getSaleItems($id);
        if ($note) {
            $note = $sale->note . '<p>' . $note . '</p>';
        }
        $cost = [];
        if ($status == 'completed' && $status != $sale->sale_status) {
            /*foreach ($items as $item) {
                $items_array[] = (array) $item;
            }
            $cost = $this->site->costing($items_array);*/
        }

        if ($this->db->update('sales', ['sale_status' => $status, 'note' => $note], ['id' => $id])) {
            if ($status == 'completed' && $status != $sale->sale_status) {
                /*foreach ($items as $item) {
                    $item = (array) $item;
                    if ($this->site->getProductByID($item['product_id'])) {
                        $item_costs = $this->site->item_costing($item);
                        foreach ($item_costs as $item_cost) {
                            $item_cost['sale_item_id'] = $item['id'];
                            $item_cost['sale_id']      = $id;
                            $item_cost['date']         = date('Y-m-d', strtotime($sale->date));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        }
                    }
                }*/
            }

            // Deduct from balance quantity except for ecommerce sales
            if (!empty($cost) && $sale->shop == 0) {
                $this->site->syncPurchaseItems($cost);
            }
            /* COMMENTED QTY DEDUCTION FROM HERE. 
            INSTEAD ADD QTY ONHOLD REQUEST TO PHARMACY FOR QTY RELEASE AFTER POS
            AS QTY WILL BE ADJUSTED THROUGH POS */
            //$this->site->syncQuantity($id);
            $this->site->addProdQuantityOnholdRequest($id, $items);
            $this->site->syncSalePayments($id);
            $this->sma->update_award_points($sale->grand_total, $sale->customer_id);
            return true;
        }
        return false;
    }
}
