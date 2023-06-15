<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pay_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addPayment($data)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $data["business_id"] = $business_id;
        if ($this->db->insert('payments', $data)) {
            $this->site->updateReference('pay');
            return true;
        }
        return false;
    }

    public function getCompanyByID($id)
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where("business_id", $business_id);
        return $this->db->get_where('companies', ['id' => $id])->row();
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
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        return $this->db->get_where('sma_directpay', ['business_id' => $business_id])->row();
    }

    public function getPaypalSettings()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        return $this->db->get_where('paypal', ['business_id' => $business_id])->row();
    }

    public function getSaleByID($id)
    {
        return $this->db->get_where('sales', ['id' => $id])->row();
    }

    public function getSaleItems($sale_id)
    {
        $this->db->select('sale_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, products.image, products.details as details, product_variants.name as variant')
            ->join('products', 'products.id=sale_items.product_id', 'left')
            ->join('product_variants', 'product_variants.id=sale_items.option_id', 'left')
            ->join('tax_rates', 'tax_rates.id=sale_items.tax_rate_id', 'left')
            ->where('sale_id', $sale_id)->group_by('sale_items.id')->order_by('id', 'asc');
        return $this->db->get('sale_items')->result();
    }

    public function getSettings()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        $this->db->where('business_id', $business_id);
        return $this->db->get('settings')->row();
    }

    public function getSkrillSettings()
    {
        $business_id = $this->session->userdata['business_id'];  //TAG:-replaced
        return $this->db->get_where('skrill', ['business_id' => $business_id])->row();
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
            foreach ($items as $item) {
                $items_array[] = (array) $item;
            }
            $cost = $this->site->costing($items_array);
        }

        if ($this->db->update('sales', ['sale_status' => $status, 'note' => $note], ['id' => $id])) {
            if ($status == 'completed' && $status != $sale->sale_status) {
                foreach ($items as $item) {
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
                }
            }

            if (!empty($cost)) {
                $this->site->syncPurchaseItems($cost);
            }
            $this->site->syncQuantity($id);
            $this->site->syncSalePayments($id);
            $this->sma->update_award_points($sale->grand_total, $sale->customer_id);
            return true;
        }
        return false;
    }
}
