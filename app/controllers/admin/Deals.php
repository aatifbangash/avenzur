<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Deals extends MY_Controller
{
    public function __construct() {
        parent::__construct(); 
        $this->load->admin_model('deals_model');
        $this->load->library('form_validation');
    }

    public function index() 
    {

    	$bc                     = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Deals')]];
        $meta                   = ['page_title' => lang('Deals'), 'bc' => $bc];
        $this->page_construct('deals/index', $meta, $this->data);
    } 

    public function add()
    {

    	$this->form_validation->set_rules('deal_no', lang('product_cost'), 'required');
    	$this->form_validation->set_rules('supplier', lang('product_cost'), 'required');
    	$this->form_validation->set_rules('ddate', lang('product_cost'), 'required');
    	$this->form_validation->set_rules('pdiscount', lang('product_cost'), 'required');
    	$this->form_validation->set_rules('salesval', lang('product_cost'), 'required');
    	$this->form_validation->set_rules('pdiscountporder', lang('product_cost'), 'required');

    	if ($this->form_validation->run() == true) 
    	{
    		$supplier_id = $this->input->post('supplier');
    		$supplier_details = $this->site->getCompanyByID($supplier_id);

    		 $data     = [
                'deal_no'            	  => $this->input->post('deal_no'),
                'supplier_id' 			  => $this->input->post('supplier'),
                'supplier_name'           => $supplier_details->company,
                'date'              	  => $this->sma->fld(trim($this->input->post('ddate'))),
                'discount_sale_val'       => $this->input->post('pdiscount'),
                'sales_val'       		  => $this->input->post('salesval'),
                'discount_purchase_order' => $this->input->post('pdiscountporder')
            ];

            $this->deals_model->addDeal($data);

            $this->session->set_flashdata('message', lang('Deal added'));
            admin_redirect('deals');

    	}else
    	{
    		$this->data['suppliers']  = $this->deals_model->getAllSuppliers();
    		$this->data['dealnumber']   = '';
    		$this->data['selctedsupplier']   = '';

    		 $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('deals'), 'page' => lang('Deals')], ['link' => '#', 'page' => lang('Add Deals')]];
            $meta               = ['page_title' => lang('Add Deaks'), 'bc' => $bc];
            $this->page_construct('deals/add', $meta, $this->data);
    	}

    	
    }

    public function getDeals()
    {
    	$edit_link        = anchor('admin/deals/edit/$1', '<i class="fa fa-edit"></i> ' . lang('Edit Deals '));
    	 $delete_link      = "<a href='#' class='po' title='<b>" . $this->lang->line('Delete Deal') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('deals/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('Delete Deals') . '</a>';
    	 $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';
    	$this->load->library('datatables');
            $this->datatables
            	->select("{$this->db->dbprefix('deals')}.id as dealid,{$this->db->dbprefix('deals')}.deal_no as deal_no,{$this->db->dbprefix('deals')}.supplier_name as supplier_name,{$this->db->dbprefix('deals')}.date as date,{$this->db->dbprefix('deals')}.discount_sale_val as discount_sale_val, {$this->db->dbprefix('deals')}.sales_val as sales_val, {$this->db->dbprefix('deals')}.discount_purchase_order as discount_purchase_order " )
                ->from('sma_deals');
               

        $this->datatables->add_column('Actions', $action, 'dealid');
        echo $this->datatables->generate();        
        
    }

     public function delete($id = null)
    {
        //$this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->deals_model->deleteDeal($id)) {
            
                $this->sma->send_json(['error' => 0, 'msg' => lang('Deal Deleted')]);
                $this->session->set_flashdata('message', lang('Deal Deleted'));
            admin_redirect('deals');
        }
    }

    public function edit($id = null)
    {
        
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }

        $this->form_validation->set_rules('deal_no', lang('product_cost'), 'required');
    	$this->form_validation->set_rules('supplier', lang('product_cost'), 'required');
    	$this->form_validation->set_rules('ddate', lang('product_cost'), 'required');
    	$this->form_validation->set_rules('pdiscount', lang('product_cost'), 'required');
    	$this->form_validation->set_rules('salesval', lang('product_cost'), 'required');
    	$this->form_validation->set_rules('pdiscountporder', lang('product_cost'), 'required');

    	if ($this->form_validation->run() == true) 
    	{
    		$supplier_id = $this->input->post('supplier');
    		$supplier_details = $this->site->getCompanyByID($supplier_id);

    		 $data     = [
                'deal_no'            	  => $this->input->post('deal_no'),
                'supplier_id' 			  => $this->input->post('supplier'),
                'supplier_name'           => $supplier_details->company,
                'date'              	  => $this->sma->fld(trim($this->input->post('ddate'))),
                'discount_sale_val'       => $this->input->post('pdiscount'),
                'sales_val'       		  => $this->input->post('salesval'),
                'discount_purchase_order' => $this->input->post('pdiscountporder')
            ];

            $this->deals_model->UpdateDeal($id,$data);

            $this->session->set_flashdata('message', lang('Deal Updated'));
            admin_redirect('deals');

    	}else
    	{
	        $deals = $this->deals_model->getDealById($id);
	        $this->data['suppliers']  = $this->site->getAllCompanies('supplier');
	        $this->data['deal'] = $deals;
	        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
	            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Edit Deal')]];
	            $meta                = ['page_title' => lang('Edit Deal'), 'bc' => $bc];
	            $this->page_construct('deals/edit', $meta, $this->data);

        }
    }

}