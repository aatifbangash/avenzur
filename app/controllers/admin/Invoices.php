<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Invoices extends MY_Controller {
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        //if (!$this->Owner) {
        //    $this->session->set_flashdata('warning', lang('access_denied'));
        //    redirect('admin');
       // }
       
    }

    public function index($warehouse_id = null)
    {
        //$this->sma->checkPermissions();

       // $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses']   = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse']    = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Invoices')]];
        $meta = ['page_title' => lang('Invoices'), 'bc' => $bc];
        $this->page_construct('invoices/index', $meta, $this->data);
    }


     public function getInvoices($warehouse_id = null)
    {
        //$this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link      = anchor('admin/purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link    = anchor('admin/purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');


        $add_payment_link = anchor('admin/purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');

        $email_link       = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
       // $edit_link        = anchor('admin/purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link         = anchor('admin/purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        //$print_barcode    = anchor('admin/products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        //$return_link      = anchor('admin/purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link      = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_purchase') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_purchase') . '</a>';


        
            $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li>' . $pdf_link . '</li>
                <li>' . $email_link . '</li>
                <li>' . $delete_link . '</li>
            </ul>
            </div></div>';

       
        

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment")
                ->from('purchases')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment")
                ->from('purchases');
        }

            $status = 'Invoiced';
            $this->datatables->where('purchase_invoice', 1);
            $this->datatables->edit_column('status',$status,'id');      

       

        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }
}