<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Refund extends MY_Controller {
     public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect('admin');
        }
        $this->lang->admin_load('front_end', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->database(); ///load database
        $this->load->admin_model('Refund_model');
        
    }
    
    public function index()
    {
        
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('Refund'), 'page' => 'Refund'], ['link' => '#', 'page' => 'Refund']];
        $meta = ['page_title' => lang('Refund'), 'bc' => $bc];
         $this->data['refund']=$this->Refund_model->display_data();
        
        $this->page_construct('refund/list_refund', $meta, $this->data);
    }
    
    public function refundDisplay()
                {
                     $data['refund']=$this->Refund_model->display_data();
                  
               
                   
                    //$print_barcode = anchor('admin/products/print_barcodes/?category=$1', '<i class="fa fa-print"></i>', 'title="' . lang('print_barcodes') . '" class="tip"');
            
                    // $this->load->library('datatables');
                    // $this->datatables
                    //     ->select("{$this->db->dbprefix('refund')}.id as id, {$this->db->dbprefix('refund')}.order_id as oid, 
                    //     {$this->db->dbprefix('refund')}.user_id, {$this->db->dbprefix('refund')}.reason_refund, 
                    //     {$this->db->dbprefix('refund')}.notes,{$this->db->dbprefix('refund')}.req_dates,
                    //     {$this->db->dbprefix('refund')}.refund_status as refund_status", false)
                    //     ->from('refund')
                    //     //  ->join('sales c', 'user.id=refund.user_id', 'left')
                    //   ->group_by('refund.id')
                    //     ->add_column('Actions', '<div class="text-center">'  ."  <a href='" . base_url('admin/payments/directpayRefund/$1/$2')."'    class='tip' title='" . lang('refund') . "'><i id='refund' class=\"fa fa-money\"></i></a>
                        
                    //     <a href='#' class='tip po' title='<b>" . lang('delete_data') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p>
                    //     <a class='btn btn-danger po-delete' href='" . admin_url('Refund/delete_refund/$2') . "'>" . lang('i_m_sure') . "</a> 
                    //     <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i>
                    //     </a>
    
                    //     </div>", 'oid,id,refund_status');
                       
                    // echo $this->datatables->generate();
                    
                }
          public function delete_refund($id = null)
                {
                    if (!$id) {
                        $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
                    }
                    if ($this->Refund_model->deleteRefund($id)) {
                        $this->sma->send_json(['error' => 0, 'msg' => lang('Refund_deleted')]);
                    }
                }
                
                public function cancel_refund($id = null)
                {
                    if (!$id) {
                        $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
                    }
                    if ($this->Refund_model->cancelRefund($id)) {
                        $this->sma->send_json(['error' => 0, 'msg' => lang('Cancel_Refund')]);
                    }
                }
    
}