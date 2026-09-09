<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_contract_deals_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function count_deals($filters = [])
    {
        $this->db->from('purchase_contract_deals');
        if (!empty($filters['from'])) {
            $this->db->where('date >=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $this->db->where('date <=', $filters['to']);
        }
        return $this->db->count_all_results();
    }

    // public function get_deals($filters = [], $limit = 100, $offset = 0)
    // {
    //     if (!empty($filters['from'])) {
    //         $this->db->where('date >=', $filters['from']);
    //     }
    //     if (!empty($filters['to'])) {
    //         $this->db->where('date <=', $filters['to']);
    //     }
    //     $this->db->order_by('id', 'desc');
    //     $q = $this->db->get('purchase_contract_deals', $limit, $offset);
    //     if ($q->num_rows() > 0) {
    //         return $q->result();
    //     }
    //     return [];
    // }

    public function get_deals($filters = [], $limit = 100, $offset = 0)
    {
        $this->db->select('purchase_contract_deals.*, companies.name as supplier_name');
        $this->db->from('purchase_contract_deals');
        $this->db->join('companies', 'companies.id = purchase_contract_deals.supplier_id', 'left');

        if (!empty($filters['from'])) {
            $this->db->where('purchase_contract_deals.date >=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $this->db->where('purchase_contract_deals.date <=', $filters['to']);
        }

        $this->db->order_by('purchase_contract_deals.id', 'desc');
        $this->db->limit($limit, $offset);

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }


    public function addDeal($data = [], $items = [])
    {
        $this->db->trans_start();
        if ($this->db->insert('purchase_contract_deals', $data)) {
            $deal_id = $this->db->insert_id();
            foreach ($items as $itemJson) {
                $item = json_decode($itemJson, true);
                $item['deal_id'] = $deal_id;
                $this->db->insert('purchase_contract_deal_items', $item);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return false;
        }
        return $deal_id;
    }

    public function updateDeal($id, $data = [], $items = [])
    {
        $this->db->trans_start();
        $this->db->update('purchase_contract_deals', $data, ['id' => $id]);
        // simple strategy: delete existing items and re-insert
        $this->db->delete('purchase_contract_deal_items', ['deal_id' => $id]);
        foreach ($items as $item) {
            $item['deal_id'] = $id;
            $this->db->insert('purchase_contract_deal_items', $item);
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    public function getDealByID($id)
    {
        $this->db->select('purchase_contract_deals.*, companies.name as supplier_name');
        $this->db->from('purchase_contract_deals');
        $this->db->join('companies', 'companies.id = purchase_contract_deals.supplier_id', 'left');
        $this->db->where('purchase_contract_deals.id', $id);
        $this->db->limit(1);

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

    public function getDealItems($deal_id)
    {
        $this->db->select('purchase_contract_deal_items.*, products.name as product_name');
        $this->db->from('purchase_contract_deal_items');
        $this->db->join('products', 'products.id = purchase_contract_deal_items.item_id', 'left');
        $this->db->where('purchase_contract_deal_items.deal_id', $deal_id);

        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return [];
    }

    public function getActiveDealsForSupplierProduct($supplier_id, $product_id)
    {
        $today = date('Y-m-d');
        $this->db->select('purchase_contract_deals.*, purchase_contract_deal_items.*');
        $this->db->from('purchase_contract_deals');
        $this->db->join('purchase_contract_deal_items', 'purchase_contract_deals.id = purchase_contract_deal_items.deal_id', 'inner');
        $this->db->where('purchase_contract_deals.supplier_id', $supplier_id);
        $this->db->where('purchase_contract_deal_items.item_id', $product_id);
        $this->db->order_by('purchase_contract_deal_items.id', 'asc');
        $this->db->limit(1);
        $q = $this->db->get();

        if ($q->num_rows() > 0) {
            return $q->result()[0];
        }
        return [];
    }


    public function deleteDeal($id)
    {
        $this->db->trans_start();
        $this->db->delete('purchase_contract_deal_items', ['deal_id' => $id]);
        $this->db->delete('purchase_contract_deals', ['id' => $id]);
        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
