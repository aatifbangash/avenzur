<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Quotation_analyzer extends MY_Controller {


    public function __construct() 
{

 
    parent::__construct();
    
    
    // Check if user is logged in
    if (!$this->loggedIn) {
        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        $url = "admin/login";
        if ($this->input->server('QUERY_STRING')) {
            $url = $url . '?' . $this->input->server('QUERY_STRING') . '&redirect=' . $this->uri->uri_string();
        }
        $this->sma->md($url);
    }

    // Check if user is Customer, redirect if true
    if ($this->Customer) {
        $this->session->set_flashdata('warning', lang('access_denied')); 
        redirect($_SERVER['HTTP_REFERER']);
    }

    // Load required models and libraries
    $this->load->admin_model('purchase_requisition_model');
    $this->load->admin_model('companies_model');
    $this->load->library('form_validation');
    
    // Load language file 
    // $this->lang->admin_load('quotation_analyzer', $this->Settings->user_language);
}

    public function index()
    {
        $this->data['purchase_requisitions'] = $this->purchase_requisition_model->get_all();
        $meta = ['page_title' => 'Quotation Analyzer', 'bc' => [['link' => '#', 'page' => 'Quotation Analyzer']]];
        $this->page_construct('quotation_analyzer/index', $meta, $this->data);
    }

    public function get_pr_details($pr_id)
    {
        if (!$pr_id) {
            $this->output->set_status_header(400)->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'PR ID is required']));
            return;
        }

        // Get PR details
        $pr = $this->purchase_requisition_model->get_by_id($pr_id);
        if (!$pr) {
            $this->output->set_status_header(404)->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'PR not found']));
            return;
        }

        // Get PR items
        $items = $this->purchase_requisition_model->get_items($pr_id);

        // Get supplier responses
        $supplier_responses = $this->db->select('psr.*, c.name as supplier_name, c.name as contact_person')
            ->from('purchase_requisition_supplier_response psr')
            ->join('companies c', 'c.id = psr.supplier_id')
            ->where('psr.pr_id', $pr_id)
            ->get()->result();

        // Get response items for each supplier
        foreach ($supplier_responses as &$response) {
            $response->items = $this->db->select('psri.*, pri.product_id, p.name as product_name, pri.quantity')
                ->from('purchase_requisition_supplier_response_items psri')
                ->join('purchase_requisition_items pri', 'pri.id = psri.pr_item_id')
                ->join('products p', 'p.id = pri.product_id')
                ->where('psri.response_id', $response->id)
                ->get()->result();
        }

        $data = [
            'pr' => $pr,
            'items' => $items,
            'supplier_responses' => $supplier_responses
        ];

        $this->output->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}