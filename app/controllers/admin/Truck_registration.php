<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Truck_registration extends MY_Controller
{
    public function __construct() {
        parent::__construct(); 
        $this->load->admin_model('truck_model');
        $this->load->library('form_validation');
    }

    public function index() 
    {
        $this->data['purchase']  = $this->truck_model->getReferenceNo();
    	$bc    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Truck Registration')]];
        $meta = ['page_title' => lang('Truck Registration'), 'bc' => $bc];
        $this->page_construct('truck_registration/index', $meta, $this->data);
    } 

    public function add(){
            $this->data['purchase']  = $this->truck_model->getReferenceNo();

    		 $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('truck_registration'), 'page' => lang('Truck registration')], ['link' => '#', 'page' => lang('Add Truck')]];
            $meta               = ['page_title' => lang('Add Truck'), 'bc' => $bc];
            $this->page_construct('truck_registration/add', $meta, $this->data);
    }

    public function save()
    {
        $data['truck_no'] = $this->input->post('truck_no');
        $data['truck_date'] =  $this->sma->fld(trim($this->input->post('ddate')));
        $data['truck_time'] = $this->input->post('truck_time');   
        $referenceNo = $this->input->post('reference_no');   
        $purchase =explode("@/",$referenceNo);
        $data['reference_no'] = $purchase[0];
        $data['purchase_id']  = $purchase[1];
       
         $this->truck_model->addTruck($data,$purchase[1]);
         $this->session->set_flashdata('message', lang('Truck Registration added'));
         admin_redirect('truck_registration');
    }

    public function getTrucks()
    {
       
        $this->load->library('datatables');
      
        $this->datatables
            ->select('id,truck_no,reference_no,truck_date,truck_time')
            ->from('truck_registration')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('truck_registration/edit/$1') . "' class='tip' title='" . lang('edit_notification') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . $this->lang->line('delete_notification') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('truck_registration/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
            $this->datatables->unset_column('id');
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

        if ($this->truck_model->deleteDeal($id)) {
            
                $this->sma->send_json(['error' => 0, 'msg' => lang('Deal Deleted')]);
                $this->session->set_flashdata('message', lang('Deal Deleted'));
            admin_redirect('truck_registration');
        }
    }

    public function edit($id = null)
    {
       
        
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
	        $truck = $this->truck_model->getTruckById($id);
	        $this->data['truck'] = $truck;
            $this->data['purchase']  = $this->truck_model->getReferenceNo();
            $this->session->flashdata('error');
	            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Edit Deal')]];
	            $meta                = ['page_title' => lang('Edit Truck Registration'), 'bc' => $bc];
	        $this->page_construct('truck_registration/edit', $meta, $this->data);
    }


    public function update($id = null)
    {
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
          $referenceNo  = $this->input->post('reference_no');
          $purchase =explode("@/",$referenceNo);
            $reference_no = $purchase[0];
            $purchase_id  = $purchase[1];
            $purchaseIdOld  = $this->input->post('purchase_id_old');

    		 $data     = [
                'truck_no'            	  => $this->input->post('truck_no'),
                'truck_date' 			  => $this->sma->fld(trim($this->input->post('truck_date'))),
                'truck_time'              => $this->input->post('truck_time'),
                'reference_no' 			  => $reference_no,
                'purchase_id' 			  => $purchase_id
            ];

            $this->truck_model->updateTruck($id,$data,$purchaseIdOld,$purchase_id);
            $this->session->set_flashdata('message', lang('Truck Registration Data Updated'));
            admin_redirect('truck_registration');
    }


}