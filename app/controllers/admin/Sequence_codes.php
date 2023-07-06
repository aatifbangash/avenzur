<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Sequence_codes extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->admin_model('companies_model');
        $this->load->admin_model('products_model');
        $this->load->admin_model('sales_model');
        $this->load->admin_model('purchases_model');
        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
    }


    public function updatecustomers()
    {
        // 	sma_companies
        $this->db->select('id, group_name');
        $this->db->where('group_name', 'customer');
        $this->db->where('sequence_code IS NULL');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('sma_companies');
        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {

                $code = $this->sequenceCode->generate('CUS', 5);
                $id = $row->id;
                $this->db->update('sma_companies', ['sequence_code' => $code], ['id' => $id]);
            }
        }
        echo "Done";
    }

    public function updatesuppliers()
    {
        // 	sma_companies
        $this->db->select('id, group_name');
        $this->db->where('group_name', 'supplier');
        $this->db->where('sequence_code IS NULL');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('sma_companies');
        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {

                $code = $this->sequenceCode->generate('SUP', 5);
                $id = $row->id;
                $this->db->update('sma_companies', ['sequence_code' => $code], ['id' => $id]);
            }
        }
        echo "Done";
    }

    public function updateproducts()
    {
        // 	sma_products
        $this->db->select('id');
        $this->db->where('sequence_code IS NULL');
        $this->db->or_where('sequence_code = ""');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('sma_products');
        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {

                $code = $this->sequenceCode->generate('PRD', 5);
                $id = $row->id;
                $this->db->update('sma_products', ['sequence_code' => $code], ['id' => $id]);
            }
        }
        echo "Done";
    }

    public function updatesales()
    {
        // 	sma_sales
        $this->db->select('id');
        $this->db->where('sequence_code IS NULL');
        $this->db->or_where('sequence_code = ""');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('sma_sales');
        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {

                $code = $this->sequenceCode->generate('SL', 5);
                $id = $row->id;
                $this->db->update('sma_sales', ['sequence_code' => $code], ['id' => $id]);
            }
        }
        echo "Done";
    }

    public function updatepurchases()
    {
        // 	sma_purchases
        $this->db->select('id');
        $this->db->where('sequence_code IS NULL');
        $this->db->or_where('sequence_code = ""');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('sma_purchases');
        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {

                $code = $this->sequenceCode->generate('PR', 5);
                $id = $row->id;
                $this->db->update('sma_purchases', ['sequence_code' => $code], ['id' => $id]);
            }
        }
        echo "Done";
    }
}
