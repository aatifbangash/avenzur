<?php

defined('BASEPATH') or exit('No direct script access allowed');

class stock_request extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('transfers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('transfers_model');
         $this->load->admin_model('products_model');
         $this->load->admin_model('stock_request_model');
         $this->load->admin_model('settings_model');
         
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
        $this->load->library('attachments', [
            'path'     => $this->digital_upload_path,
            'types'    => $this->digital_file_types,
            'max_size' => $this->allowed_file_size,
        ]);
    }
    
    public function index()
    {
        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Stock Requests')]];
        $meta = ['page_title' => lang('Stock Requests'), 'bc' => $bc];
        $this->page_construct('stock_request/index', $meta, $this->data);
    }
    public function incoming_requests()
    {
        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Stock Incoming Requests')]];
        $meta = ['page_title' => lang('Stock Incoming Requests'), 'bc' => $bc];
        $this->page_construct('stock_request/incoming', $meta, $this->data);
    }
    
    public function getStockRequests()
    {
        //$this->sma->checkPermissions('index');
        if($this->sma->checkPermissionsForRequest('stock_request_approval')){
        $approval = '<span class="stock_status_update" style="padding: 3px 20px;white-space: nowrap; cursor: pointer;"><i class="fa fa-edit"></i> ' . lang('Request Approval').'</span>';
        //anchor('', '<i class="fa fa-edit"></i> ' . lang('Request Approval'), 'class="stock_status_update"');
        $detail_link   = anchor('admin/transfers/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('transfer_details'), 'data-toggle="modal" data-target="#myModal"');
        $email_link    = anchor('admin/transfers/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_transfer'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link     = anchor('admin/transfers/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_transfer'));
        $pdf_link      = anchor('admin/transfers/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?transfer=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $delete_link   = "<a href='#' class='tip po' title='<b>" . lang('Delete Request') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . admin_url('stock_request/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('Delete Request') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $delete_link . '</li>
            <li>' . $approval . '</li>
        </ul>
    </div></div>';
        }else{
           $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('No Permission') . ' </button>';
        }
        

        $this->load->library('datatables');

        $this->datatables
            ->select('id, date, transfer_no, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, total, total_tax, grand_total, status, attachment')
            ->from('transfers')
            ->edit_column('fname', '$1 ($2)', 'fname, fcode')
            ->edit_column('tname', '$1 ($2)', 'tname, tcode');
        
          //$this->datatables->or_where('from_warehouse_id', $this->session->userdata('warehouse_id'));
         //$this->datatables->or_where('to_warehouse_id', $this->session->userdata('warehouse_id'));
         
         $this->datatables->where('type', 'stock');
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->or_where('created_by', $this->session->userdata('user_id')); 
        }

        $this->datatables->add_column('Actions', $action, 'id')
            ->unset_column('fcode')
            ->unset_column('tcode');
        echo $this->datatables->generate();
    }
    
    public function getincomingStockRequests()
    {
        //$this->sma->checkPermissions('index');
        if($this->sma->checkPermissionsForRequest('stock_request_approval')){
        $approval = '<span class="stock_status_update" style="padding: 3px 20px;white-space: nowrap; cursor: pointer;"><i class="fa fa-edit"></i> ' . lang('Request Approval').'</span>';
        //anchor('', '<i class="fa fa-edit"></i> ' . lang('Request Approval'), 'class="stock_status_update"');
        $detail_link   = anchor('admin/transfers/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('transfer_details'), 'data-toggle="modal" data-target="#myModal"');
        $email_link    = anchor('admin/transfers/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_transfer'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link     = anchor('admin/transfers/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_transfer'));
        $pdf_link      = anchor('admin/transfers/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?transfer=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $delete_link   = "<a href='#' class='tip po' title='<b>" . lang('Delete Request') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . admin_url('stock_request/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('Delete Request') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $delete_link . '</li>
            <li>' . $approval . '</li>
        </ul>
        </div></div>';
        }else{
           $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('No Permission') . ' </button>';
        }
        

        $this->load->library('datatables');

        $this->datatables
            ->select('id, date, transfer_no, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, total, total_tax, grand_total, status, attachment')
            ->from('transfers')
            ->edit_column('fname', '$1 ($2)', 'fname, fcode')
            ->edit_column('tname', '$1 ($2)', 'tname, tcode');
        
         $this->datatables->where('from_warehouse_id', $this->session->userdata('warehouse_id'));
         //$this->datatables->or_where('to_warehouse_id', $this->session->userdata('warehouse_id'));
         //$this->datatables->or_where('created_by', $this->session->userdata('user_id')); 
         
         $this->datatables->where('type', 'stock');
         
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('status', 'sent'); 
            $this->datatables->or_where('status', 'completed');
            $this->datatables->where('approval', '1');
        }

        $this->datatables->add_column('Actions', $action, 'id')
            ->unset_column('fcode')
            ->unset_column('tcode');
        echo $this->datatables->generate();
    }
    
    public function suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $term = addslashes($term);
        $rows = $this->stock_request_model->getProductNamesPos($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => 1];
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('No matching result found! Product might be out of stock in all warehouses.'), 'value' => $term]]);
        }
    }
    
    public function checkstock()
    {
        $term = $this->input->get('term', true);
        $term = addslashes($term);
         $warehouses = $this->products_model->getAllWarehousesWithPQ($term);
         $product = $this->products_model->getProductByID($term);
         $html='';
         $html .= '<h3>'.$product->name.'</h3>';
         $html .='<table class="table table-bordered table-striped table-condensed dfTable three-columns">
                    <thead>
                        <tr>
                        <th>'.lang("warehouse_name").'</th>
                        <th>'.lang("quantity").'</th></tr>
                        </thead><tbody>';
                        
                                foreach ($warehouses as $warehouse) {
                                            if ($warehouse->quantity != 0) {
                                                $html.= '<tr><td>' . $warehouse->name . ' (' . $warehouse->code . ')</td><td><strong>' . $this->sma->formatQuantity($warehouse->quantity) . '</strong>' . ($warehouse->rack ? ' (' . $warehouse->rack . ')' : '') . '</td></tr>';
                                            }
                                        } 
                        
          $html.='</tbody></table>';
          echo $html;
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

        if ($this->transfers_model->deleteTransfer($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 0, 'msg' => lang('Request successfully deleted')]);
            }
            $this->session->set_flashdata('message', lang('Request successfully deleted'));
            admin_redirect('welcome');
        }
    }
    public function update_status($id)
    {
        $this->form_validation->set_rules('status', lang('status'), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note   = $this->sma->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'transfers');
        }
        $approval = 0;
        if(isset($status) && $status == 'sent'){ $approval = 1;}
        if ($this->form_validation->run() == true && $this->transfers_model->updateStatus1($id, $status,$approval, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'transfers');
        } else {
            $this->data['inv']      = $this->transfers_model->getTransferByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'stock_request/update_status', $this->data);
        }
    }
    
    public function incoming_update_status($id)
    {
        $this->form_validation->set_rules('status', lang('status'), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note   = $this->sma->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'transfers');
        }

        if ($this->form_validation->run() == true && $this->transfers_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'transfers');
        } else {
            $this->data['inv']      = $this->transfers_model->getTransferByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'stock_request/incoming_update_status', $this->data);
        }
    }
    
    public function view($transfer_id = null)
    {
        //$this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer            = $this->transfers_model->getTransferByID($transfer_id);
        /*if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($transfer->created_by, true);
        }*/
        $this->data['rows']           = $this->transfers_model->getAllTransferItems($transfer_id, $transfer->status);
        $this->data['from_warehouse'] = $this->site->getWarehouseByID($transfer->from_warehouse_id);
        $this->data['to_warehouse']   = $this->site->getWarehouseByID($transfer->to_warehouse_id);
        $this->data['transfer']       = $transfer;
        $this->data['tid']            = $transfer_id;
        $this->data['attachments']    = $this->site->getAttachments($transfer_id, 'transfer');
        $this->data['created_by']     = $this->site->getUser($transfer->created_by);
        // $this->data['updated_by']     = $this->site->getUser($transfer->updated_by);
        $this->load->view($this->theme . 'stock_request/view', $this->data);
    }
    
     public function ajax_add()
    {
        //]$this->sma->checkPermissions();

        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('to_warehouse', lang('warehouse') . ' (' . lang('to') . ')', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('from_warehouse', lang('warehouse') . ' (' . lang('from') . ')', 'required|is_natural_no_zero');

        if ($this->form_validation->run()) {
            $transfer_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $to_warehouse           = $this->input->post('to_warehouse');
            $from_warehouse         = $this->input->post('from_warehouse');
            $note                   = $this->sma->clear_tags($this->input->post('note'));
            $shipping               = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $status                 = $this->input->post('status');
            $from_warehouse_details = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_code    = $from_warehouse_details->code;
            $from_warehouse_name    = $from_warehouse_details->name;
            $to_warehouse_details   = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_code      = $to_warehouse_details->code;
            $to_warehouse_name      = $to_warehouse_details->name;

            $total       = 0;
            $product_tax = 0;
            $gst_data    = [];
            $total_cgst  = $total_sgst  = $total_igst  = 0;
            $i           = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_code          = $_POST['product_code'][$r];
                $item_net_cost      = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost          = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost     = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_tax_rate      = $_POST['product_tax'][$r] ?? null;
                $item_expiry        = isset($_POST['expiry'][$r]) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->transfers_model->getProductByCode($item_code);
                    // if (!$this->Settings->overselling) {
                    $warehouse_quantity = $this->transfers_model->getWarehouseProduct($from_warehouse_details->id, $product_details->id, $item_option);
                    if ($warehouse_quantity->quantity < $item_quantity) {
                        $this->session->set_flashdata('error', lang('no_match_found') . ' (' . lang('product_name') . ' <strong>' . $product_details->name . '</strong> ' . lang('product_code') . ' <strong>' . $product_details->code . '</strong>)');
                        admin_redirect('transfers/add');
                    }
                    // }

                    $pr_item_tax   = $item_tax   = 0;
                    $tax           = '';
                    $item_net_cost = $unit_cost;

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];
                        if (!empty($product_details) && $product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, false, $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit     = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'option_id'         => $item_option,
                        'net_unit_cost'     => $item_net_cost,
                        'unit_cost'         => $this->sma->formatDecimal($item_net_cost + $item_tax, 4),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity'     => $item_unit_quantity,
                        'quantity_balance'  => $item_quantity,
                        'warehouse_id'      => $to_warehouse,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'expiry'            => $item_expiry,
                        'real_unit_cost'    => $real_unit_cost,
                        'date'              => date('Y-m-d', strtotime($date)),
                    ];

                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $grand_total = $this->sma->formatDecimal(($total + $shipping + $product_tax), 4);
            $data        = ['transfer_no' => $transfer_no,
                'date'                    => $date,
                'from_warehouse_id'       => $from_warehouse,
                'from_warehouse_code'     => $from_warehouse_code,
                'from_warehouse_name'     => $from_warehouse_name,
                'to_warehouse_id'         => $to_warehouse,
                'to_warehouse_code'       => $to_warehouse_code,
                'to_warehouse_name'       => $to_warehouse_name,
                'note'                    => $note,
                'total_tax'               => $product_tax,
                'total'                   => $total,
                'grand_total'             => $grand_total,
                'created_by'              => $this->session->userdata('user_id'),
                'status'                  => $status,
                'shipping'                => $shipping,
                'type'                    => 'stock',
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            $attachments        = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->transfers_model->addTransfer($data, $products, $attachments)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', 'Request from warehouse successfully added.');
            admin_redirect('pos');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['name'] = ['name' => 'name',
                'id'                      => 'name',
                'type'                    => 'text',
                'value'                   => $this->form_validation->set_value('name'),
            ];
            $this->data['quantity'] = ['name' => 'quantity',
                'id'                          => 'quantity',
                'type'                        => 'text',
                'value'                       => $this->form_validation->set_value('quantity'),
            ];

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['rnumber']    = ''; //$this->site->getReference('to');

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('transfers'), 'page' => lang('transfers')], ['link' => '#', 'page' => lang('add_transfer')]];
            $meta = ['page_title' => lang('transfer_quantity'), 'bc' => $bc];
          //  $this->page_construct('transfers/add', $meta, $this->data);
        }
    } 
    
    
}