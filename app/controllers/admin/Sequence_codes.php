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
        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
    }


    public function updatecustomers()
    {
        // 	sma_companies
        $this->db->select('id, group_name');
        $this->db->where('group_name', 'customer');
        $this->db->where('code IS NULL');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('sma_companies');
        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {

                $code = $this->sequenceCode->generate('CUS', 5);
                $id = $row->id;
                $this->db->update('sma_companies', ['code' => $code], ['id' => $id]);
            }
        }
        echo "Done";
    }

    public function updatesuppliers()
    {
        // 	sma_companies
        $this->db->select('id, group_name');
        $this->db->where('group_name', 'supplier');
        $this->db->where('code IS NULL');
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('sma_companies');
        if ($query->num_rows() > 0) {
            foreach (($query->result()) as $row) {

                $code = $this->sequenceCode->generate('SUP', 5);
                $id = $row->id;
                $this->db->update('sma_companies', ['code' => $code], ['id' => $id]);
            }
        }
        echo "Done";
    }
}
