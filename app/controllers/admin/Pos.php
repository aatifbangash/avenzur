<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pos extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $url = "admin/login";
            if( $this->input->server('QUERY_STRING') ){
                $url = $url.'?'.$this->input->server('QUERY_STRING').'&redirect='.$this->uri->uri_string();
            }
           
            $this->sma->md($url);
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->load->admin_model('sales_model');
        $this->load->admin_model('pos_model');
        $this->load->helper('text');
        $this->pos_settings           = $this->pos_model->getSetting();
        $this->pos_settings->pin_code = $this->pos_settings->pin_code ? md5($this->pos_settings->pin_code) : null;
        $this->data['pos_settings']   = $this->pos_settings;
        $this->session->set_userdata('last_activity', now());
        $this->lang->admin_load('pos', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->library('RASDCore',$params=null, 'rasd');
        $this->load->admin_model('cmt_model');
        $this->load->admin_model("Zetca_model");
        $this->zatca_enabled = false;
        $d = $this->Zetca_model->get_zetca_settings();

        if($d['zatca_enabled']){
            $this->zatca_enabled = true;
            $params = array(
            'base_url' => $d['zatca_url'],
            "api_key" => $d['zatca_appkey'],
            "api_secret" => $d['zatca_secretKey']
            );
           $this->load->library('ZatcaServices', $params, 'zatca');                       
        }
       
       
       
    }

    public function active()
    {
        $this->session->set_userdata('last_activity', now());
        if ((now() - $this->session->userdata('last_activity')) <= 20) {
            die('Successfully updated the last activity.');
        }
        die('Failed to update last activity.');
    }

    public function add_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true, 'sales');
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $sale = $this->pos_model->getInvoiceByID($this->input->post('sale_id'));
            if ($this->input->post('paid_by') == 'deposit') {
                $customer_id = $sale->customer_id;
                if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                    $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = [
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'cc_cvv2'      => $this->input->post('pcc_ccv'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
                'type'         => $sale->sale_status == 'returned' ? 'returned' : 'received',
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                 = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $msg = $this->pos_model->addPayment($payment, $customer_id)) {
            if ($msg) {
                if ($msg['status'] == 0) {
                    unset($msg['status']);
                    $error = '';
                    foreach ($msg as $m) {
                        if (is_array($m)) {
                            foreach ($m as $e) {
                                $error .= '<br>' . $e;
                            }
                        } else {
                            $error .= '<br>' . $m;
                        }
                    }
                    $this->session->set_flashdata('error', '<pre>' . $error . '</pre>');
                } else {
                    $this->session->set_flashdata('message', lang('payment_added'));
                }
            } else {
                $this->session->set_flashdata('error', lang('payment_failed'));
            }
            admin_redirect('pos/sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $sale                      = $this->pos_model->getInvoiceByID($id);
            $this->data['inv']         = $sale;
            $this->data['payment_ref'] = $this->site->getReference('pay');
            $this->data['modal_js']    = $this->site->modal_js();

            $this->load->view($this->theme . 'pos/add_payment', $this->data);
        }
    }

    public function add_printer()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('pos');
        }

        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line('profile'), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line('char_per_line'), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line('ip_address'), 'required|is_unique[printers.ip_address]');
            $this->form_validation->set_rules('port', $this->lang->line('port'), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line('path'), 'required|is_unique[printers.path]');
        }

        if ($this->form_validation->run() == true) {
            $data = ['title'    => $this->input->post('title'),
                'type'          => $this->input->post('type'),
                'profile'       => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path'          => $this->input->post('path'),
                'ip_address'    => $this->input->post('ip_address'),
                'port'          => ($this->input->post('type') == 'network') ? $this->input->post('port') : null,
            ];
        }

        if ($this->form_validation->run() == true && $cid = $this->pos_model->addPrinter($data)) {
            $this->session->set_flashdata('message', $this->lang->line('printer_added'));
            admin_redirect('pos/printers');
        } else {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['status' => 'failed', 'msg' => validation_errors()]);
                die();
            }

            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('add_printer');
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => admin_url('pos/printers'), 'page' => lang('printers')], ['link' => '#', 'page' => lang('add_printer')]];
            $meta                     = ['page_title' => lang('add_printer'), 'bc' => $bc];
            $this->page_construct('pos/add_printer', $meta, $this->data);
        }
    }

    public function ajaxbranddata($brand_id = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }
        
        $products = $this->ajaxproducts(false, $brand_id);
        if (!($tcp = $this->pos_model->products_count(false, false, $brand_id))) {
            $tcp = 0;
        }

        $this->sma->send_json(['products' => $products, 'tcp' => $tcp]);
    }

    public function ajaxcategorydata($category_id = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }

        $subcategories = $this->site->getSubCategories($category_id);
        $scats         = '';
        if ($subcategories) {
            foreach ($subcategories as $category) {
                $scats .= '<button id="subcategory-' . $category->id . "\" type=\"button\" value='" . $category->id . "' class=\"btn-prni subcategory\" ><img src=\"" . base_url() . 'assets/uploads/thumbs/' . ($category->image ? $category->image : 'no_image.png') . "\" class='img-rounded img-thumbnail' /><span>" . $category->name . '</span></button>';
            }
        }

        $products = $this->ajaxproducts($category_id);

        if (!($tcp = $this->pos_model->products_count($category_id))) {
            $tcp = 0;
        }

        $this->sma->send_json(['products' => $products, 'subcategories' => $scats, 'tcp' => $tcp]);
    }

    public function ajaxproducts($category_id = null, $brand_id = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('brand_id')) {
            $brand_id = $this->input->get('brand_id');
        }
        if ($this->input->get('category_id')) {
            $category_id = $this->input->get('category_id');
        } else {
            $category_id = $this->pos_settings->default_category;
        }
        if ($this->input->get('subcategory_id')) {
            $subcategory_id = $this->input->get('subcategory_id');
        } else {
            $subcategory_id = null;
        }
        if (empty($this->input->get('per_page')) || $this->input->get('per_page') == 'n') {
            $page = 0;
        } else {
            $page = $this->input->get('per_page');
        }

        $this->load->library('pagination');

        $config                  = [];
        $config['base_url']      = base_url() . 'pos/ajaxproducts';
        $config['total_rows']    = $this->pos_model->products_count($category_id, $subcategory_id, $brand_id);
        $config['per_page']      = $this->pos_settings->pro_limit;
        $config['prev_link']     = false;
        $config['next_link']     = false;
        $config['display_pages'] = false;
        $config['first_link']    = false;
        $config['last_link']     = false;

        $this->pagination->initialize($config);

        $products = $this->pos_model->fetch_products($category_id, $config['per_page'], $page, $subcategory_id, $brand_id);
        $pro      = 1;
        $prods    = '<div>';
        if (!empty($products)) {
            foreach ($products as $product) {
                $count = $product->id;
                if ($count < 10) {
                    $count = '0' . ($count / 100) * 100;
                }
                if ($category_id < 10) {
                    $category_id = '0' . ($category_id / 100) * 100;
                }

                $prods .= '<button id="product-' . $category_id . $count . "\" type=\"button\" value='" . $product->code . "' title=\"" . $product->name . '" class="btn-prni btn-' . $this->pos_settings->product_button_color . ' product pos-tip" data-container="body"><img src="' . base_url() . 'assets/uploads/thumbs/' . $product->image . '" alt="' . $product->name . "\" class='img-rounded' /><span>" . character_limiter($product->name, 40) . '</span></button>';

                $pro++;
            }
        }
        $prods .= '</div>';

        if ($this->input->get('per_page')) {
            echo $prods;
        } else {
            return $prods;
        }
    }

    public function barcode($text = null, $bcs = 'code128', $height = 50)
    {
        return admin_url('products/gen_barcode/' . $text . '/' . $bcs . '/' . $height);
    }

    public function check_pin()
    {
        $pin = $this->input->post('pw', true);
        if ($pin == $this->pos_pin) {
            $this->sma->send_json(['res' => 1]);
        }
        $this->sma->send_json(['res' => 0]);
    }

    public function close_register($user_id = null)
    {
        $this->sma->checkPermissions('index');
        if (!$this->Owner && !$this->Admin) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->form_validation->set_rules('total_cash', lang('total_cash'), 'trim|required|numeric');
        $this->form_validation->set_rules('total_cheques', lang('total_cheques'), 'trim|numeric');
        $this->form_validation->set_rules('total_cc_slips', lang('total_cc_slips'), 'trim|numeric');

        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $user_register = $user_id ? $this->pos_model->registerData($user_id) : null;
                $rid           = $user_register ? $user_register->id : $this->session->userdata('register_id');
                $user_id       = $user_register ? $user_register->user_id : $this->session->userdata('user_id');
            } else {
                $rid     = $this->session->userdata('register_id');
                $user_id = $this->session->userdata('user_id');
            }
        //for testing purpose
        $user_id =6655;
            $data = [
                'closed_at'                => date('Y-m-d H:i:s'),
                'total_cash'               => $this->input->post('total_cash'),
                'total_cheques'            => $this->input->post('total_cheques'),
                'total_cc_slips'           => $this->input->post('total_cc_slips'),
                'total_cash_submitted'     => $this->input->post('total_cash_submitted'),
                'total_cheques_submitted'  => $this->input->post('total_cheques_submitted'),
                'total_cc_slips_submitted' => $this->input->post('total_cc_slips_submitted'),
                'note'                     => $this->input->post('note'),
                'status'                   => 'close',
                'transfer_opened_bills'    => $this->input->post('transfer_opened_bills'),
                'closed_by'                => $this->session->userdata('user_id'),
            ];
        } elseif ($this->input->post('close_register')) {
            $this->session->set_flashdata('error', (validation_errors() ? validation_errors() : $this->session->flashdata('error')));
            admin_redirect('pos');
        }

        if ($this->form_validation->run() == true && $this->pos_model->closeRegister($rid, $user_id, $data)) {
            $this->session->set_flashdata('message', lang('register_closed'));
            admin_redirect('welcome');
        } else {

            if ($this->Owner || $this->Admin) {
                $user_register                    = $user_id ? $this->pos_model->registerData($user_id) : null;
                $register_open_time               = $user_register ? $user_register->date : null;
                $this->data['cash_in_hand']       = $user_register ? $user_register->cash_in_hand : null;
                $this->data['register_open_time'] = $user_register ? $register_open_time : null;
            } else {
                $register_open_time               = $this->session->userdata('register_open_time');
                $this->data['cash_in_hand']       = null;
                $this->data['register_open_time'] = null;
            }

            $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['ccsales']         = $this->pos_model->getRegisterCCSales($register_open_time, $user_id);
            $this->data['cashsales']       = $this->pos_model->getRegisterCashSales($register_open_time, $user_id);
            $this->data['chsales']         = $this->pos_model->getRegisterChSales($register_open_time, $user_id);
            $this->data['gcsales']         = $this->pos_model->getRegisterGCSales($register_open_time);
            $this->data['pppsales']        = $this->pos_model->getRegisterPPPSales($register_open_time, $user_id);
            $this->data['stripesales']     = $this->pos_model->getRegisterStripeSales($register_open_time, $user_id);
            $this->data['othersales']      = $this->pos_model->getRegisterOtherSales($register_open_time);
            $this->data['authorizesales']  = $this->pos_model->getRegisterAuthorizeSales($register_open_time, $user_id);
            $this->data['totalsales']      = $this->pos_model->getRegisterSales($register_open_time, $user_id);
            $this->data['refunds']         = $this->pos_model->getRegisterRefunds($register_open_time, $user_id);
            $this->data['returns']         = $this->pos_model->getRegisterReturns($register_open_time, $user_id);
            $this->data['cashrefunds']     = $this->pos_model->getRegisterCashRefunds($register_open_time, $user_id);
            $this->data['expenses']        = $this->pos_model->getRegisterExpenses($register_open_time, $user_id);
            $this->data['users']           = $this->pos_model->getUsers($user_id);
            $this->data['suspended_bills'] = $this->pos_model->getSuspendedsales($user_id);
            $this->data['user_id']         = $user_id;
            $this->data['modal_js']        = $this->site->modal_js();
            $this->load->view($this->theme . 'pos/close_register', $this->data);
        }
    }

    public function delete($id = null)
    {
        $this->sma->checkPermissions('index');
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }
        if ($this->pos_model->deleteBill($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('suspended_sale_deleted')]);
        }
    }

    public function delete_printer($id = null)
    {
        if (DEMO) {
            $this->session->set_flashdata('error', $this->lang->line('disabled_in_demo'));
            $this->sma->md();
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->pos_model->deletePrinter($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('printer_deleted')]);
        }
    }

    public function edit_printer($id = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('pos');
        }
        if ($this->input->get('id')) {
            $id = $this->input->get('id', true);
        }

        $printer = $this->pos_model->getPrinterByID($id);
        $this->form_validation->set_rules('title', $this->lang->line('title'), 'required');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'required');
        $this->form_validation->set_rules('profile', $this->lang->line('profile'), 'required');
        $this->form_validation->set_rules('char_per_line', $this->lang->line('char_per_line'), 'required');
        if ($this->input->post('type') == 'network') {
            $this->form_validation->set_rules('ip_address', $this->lang->line('ip_address'), 'required');
            if ($this->input->post('ip_address') != $printer->ip_address) {
                $this->form_validation->set_rules('ip_address', $this->lang->line('ip_address'), 'is_unique[printers.ip_address]');
            }
            $this->form_validation->set_rules('port', $this->lang->line('port'), 'required');
        } else {
            $this->form_validation->set_rules('path', $this->lang->line('path'), 'required');
            if ($this->input->post('path') != $printer->path) {
                $this->form_validation->set_rules('path', $this->lang->line('path'), 'is_unique[printers.path]');
            }
        }

        if ($this->form_validation->run() == true) {
            $data = ['title'    => $this->input->post('title'),
                'type'          => $this->input->post('type'),
                'profile'       => $this->input->post('profile'),
                'char_per_line' => $this->input->post('char_per_line'),
                'path'          => $this->input->post('path'),
                'ip_address'    => $this->input->post('ip_address'),
                'port'          => ($this->input->post('type') == 'network') ? $this->input->post('port') : null,
            ];
        }

        if ($this->form_validation->run() == true && $this->pos_model->updatePrinter($id, $data)) {
            $this->session->set_flashdata('message', $this->lang->line('printer_updated'));
            admin_redirect('pos/printers');
        } else {
            $this->data['printer']    = $printer;
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['page_title'] = lang('edit_printer');
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => admin_url('pos/printers'), 'page' => lang('printers')], ['link' => '#', 'page' => lang('edit_printer')]];
            $meta                     = ['page_title' => lang('edit_printer'), 'bc' => $bc];
            $this->page_construct('pos/edit_printer', $meta, $this->data);
        }
    }

    public function email_receipt($sale_id = null, $view = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->post('id')) {
            $sale_id = $this->input->post('id');
        }
        if (!$sale_id) {
            die('No sale selected.');
        }
        if ($this->input->post('email')) {
            $to = $this->input->post('email');
        }
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');

        $this->data['rows']            = $this->pos_model->getAllInvoiceItems($sale_id);
        $inv                           = $this->pos_model->getInvoiceByID($sale_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos']             = $this->pos_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sale_id;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['page_title']      = $this->lang->line('invoice');

        $receipt = $this->load->view($this->theme . 'pos/email_receipt', $this->data, true);
        if ($view) {
            echo $receipt;
            die();
        }

        if (!$to) {
            $to = $this->data['customer']->email;
        }
        if (!$to) {
            $this->sma->send_json(['msg' => $this->lang->line('no_meil_provided')]);
        }

        try {
            if ($this->sma->send_email($to, lang('receipt_from') . ' ' . $this->data['biller']->company, $receipt)) {
                $this->sma->send_json(['msg' => $this->lang->line('email_sent')]);
            } else {
                $this->sma->send_json(['msg' => $this->lang->line('email_failed')]);
            }
        } catch (Exception $e) {
            $this->sma->send_json(['msg' => $e->getMessage()]);
        }
    }

    public function get_printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        $this->load->library('datatables');
        $this->datatables
        ->select('id, title, type, profile, path, ip_address, port')
        ->from('printers')
        ->add_column('Actions', "<div class='text-center'> <a href='" . admin_url('pos/edit_printer/$1') . "' class='btn-warning btn-xs tip' title='" . lang('edit_printer') . "'><i class='fa fa-edit'></i></a> <a href='#' class='btn-danger btn-xs tip po' title='<b>" . lang('delete_printer') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('pos/delete_printer/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id')
        ->unset_column('id');
        echo $this->datatables->generate();
    }

    public function getProductDataByCode($code = null, $warehouse_id = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('code')) {
            $code = $this->input->get('code', true);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', true);
        }
        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id', true);
        }
        if (!$code) {
            echo null;
            die();
        }
        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        $customer       = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $row            = $this->pos_model->getWHProduct($code, $warehouse_id);
        $option         = false;
        if ($row) {
            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->item_tax_method = $row->tax_method;
            $row->qty             = 1;
            $row->discount        = '0';
            $row->serial          = '';
            $options              = $this->pos_model->getProductOptions($row->id, $warehouse_id);
            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option = $opt->id;
                }
            } else {
                $opt        = json_decode('{}');
                $opt->price = 0;
            }
            $row->option   = $option;
            $row->quantity = 0;
            $pis           = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
            if ($pis) {
                foreach ($pis as $pi) {
                    $row->quantity += $pi->quantity_balance;
                }
            }
            if ($row->type == 'standard' && (!$this->Settings->overselling && $row->quantity < 1)) {
                echo null;
                die();
            }
            if ($options) {
                $option_quantity = 0;
                foreach ($options as $option) {
                    $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $option_quantity += $pi->quantity_balance;
                        }
                    }
                    if ($option->quantity > $option_quantity) {
                        $option->quantity = $option_quantity;
                    }
                }
            }

            if ($this->sma->isPromo($row)) {
                $row->price = $row->promo_price;
            } elseif ($customer->price_group_id) {
                if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                    $row->price = $pr_group_price->price;
                }
            } elseif ($warehouse->price_group_id) {
                if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                    $row->price = $pr_group_price->price;
                }
            }
            if ($customer_group) {
                if ($customer_group->discount && $customer_group->percent < 0) {
                    $row->discount = (0 - $customer_group->percent) . '%';
                } else {
                    $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
                }
            }
            $row->real_unit_price = $row->price;
            $row->base_quantity   = 1;
            $row->base_unit       = $row->unit;
            $row->base_unit_price = $row->price;
            $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
            $row->comment         = '';
            $combo_items          = false;
            if ($row->type == 'combo') {
                $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
            }
            $units    = $this->site->getUnitsByBUID($row->base_unit);
            $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

            $pr = ['id' => sha1(uniqid(mt_rand(), true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options];

            $this->sma->send_json($pr);
        } else {
            echo null;
        }
    }

    public function getProductData($pId = null, $warehouse_id = null){
        $this->sma->checkPermissions('index');
        if ($this->input->get('product_id')) {
            $pId = $this->input->get('product_id', true);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', true);
        }

        $rows       = $this->pos_model->getWHProductById($pId, $warehouse_id);
        $r = 0;
        $count = 0;

        $option    = false;
        if ($rows) {
            $c = uniqid(mt_rand(), true);
            $row = $rows[0];
            unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
            $row->item_tax_method = $row->tax_method;
            $row->qty             = 1;
            $row->price           = $row->net_unit_sale;
            $row->discount        = '0';
            $row->serial          = '';
            //$options              = $this->pos_model->getProductOptions($row->id, $warehouse_id);

            if ($options) {
                $opt = current($options);
                if (!$option) {
                    $option = $opt->id;
                }
                }

            $row->option          = $option;
            $row->real_unit_price = $row->net_unit_sale;
            $row->base_quantity   = 1;
            $row->base_unit       = $row->unit;
            $row->base_unit_price = $row->net_unit_sale;
            $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
            $row->comment         = '';
            $combo_items          = false;

            $row->batch_no = $row->batchno;
            $row->qty = $row->total_quantity;

            $row->id = $row->product_id;
            $row->name = $row->product_name;
            $row->code = $row->product_code;

            // if ($row->type == 'combo') {
            //     $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
            // }
            $units    = $this->site->getUnitsByBUID($row->base_unit);
            $tax_rate = false; // $this->site->getTaxRateByID($row->tax_rate);

            //$pr = ['id' => sha1(uniqid(mt_rand(), true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options];

            $total_quantity = $row->total_quantity;
            $row->quantity = $row->total_quantity;
            $count++;
            $row->serial_no = $count;
            $options = [];
            $pr = (object)[
                'id' => sha1($c . $r),
                'item_id' => $row->product_id,
                'label' => $row->product_name . ' (' . $row->code . ')',
                'row' => $row,
                'tax_rate' => $tax_rate,
                'units' => $units,
                'options' => $options,
                'batches' => $batches,
                'total_quantity' => $total_quantity
            ];
            $r++;

            $this->sma->send_json($pr);
        }
    }

    public function getProductPromo($pId = null, $warehouse_id = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('product_id')) {
            $pId = $this->input->get('product_id', true);
        }
        if ($this->input->get('warehouse_id')) {
            $warehouse_id = $this->input->get('warehouse_id', true);
        }
        $this->load->admin_model('promos_model');
        $promos = $this->promos_model->getPromosByProduct($pId);

        if ($promos) {
            foreach ($promos as $promo) {
                $warehouse = $this->site->getWarehouseByID($warehouse_id);
                $row       = $this->pos_model->getWHProductById($promo->product2get, $warehouse_id);
                $option    = false;
                if ($row) {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    $row->item_tax_method = $row->tax_method;
                    $row->qty             = 1;
                    $row->price           = 0;
                    $row->discount        = '0';
                    $row->serial          = '';
                    $options              = $this->pos_model->getProductOptions($row->id, $warehouse_id);

                    if ($options) {
                        $opt = current($options);
                        if (!$option) {
                            $option = $opt->id;
                        }
                     }

                    $row->option          = $option;
                    $row->real_unit_price = $row->net_unit_sale;
                    $row->base_quantity   = 1;
                    $row->base_unit       = $row->unit;
                    $row->base_unit_price = $row->net_unit_sale;
                    $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                    $row->comment         = '';
                    $combo_items          = false;

                    $row->batch_no = $row->batchno;
                    $row->qty = $row->total_quantity;

                    // if ($row->type == 'combo') {
                    //     $combo_items = $this->pos_model->getProductComboItems($row->id, $warehouse_id);
                    // }
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = false; // $this->site->getTaxRateByID($row->tax_rate);

                    $pr = ['id' => sha1(uniqid(mt_rand(), true)), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id, 'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options];

                    $this->sma->send_json($pr);
                } else {
                    echo null;
                }
            }
        } else {
            echo null;
        }
    }

    public function getSales($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');
       // print_r($this->input->get());
        $sid = $this->input->get('sid');
        $sfromDate = $this->input->get('from');
        $stoDate = $this->input->get('to');
        $swarehouse = $this->input->get('warehouse');
        
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        
        $duplicate_link    = anchor('admin/pos/?duplicate=$1', '<i class="fa fa-plus-square"></i> ' . lang('duplicate_sale'), 'class="duplicate_pos"');
        $detail_link       = anchor('admin/pos/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('view_receipt'));
        $detail_link2      = anchor('admin/sales/modal_view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details_modal'), 'data-toggle="modal" data-target="#myModal"');
        $detail_link3      = anchor('admin/sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $payments_link     = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link  = anchor('admin/pos/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $packagink_link    = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link        = anchor('admin/#', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'class="email_receipt" data-id="$1" data-email-address="$2"');
        $edit_link         = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $return_link       = anchor('admin/returns/add/?sale=$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_sale') . '</a>';
        $journal_entry_link      = anchor('admin/entries/view/journal/?sid=$1', '<i class="fa fa-eye"></i> ' . lang('Journal Entry'));

        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $duplicate_link . '</li>
                <li>' . $detail_link . '</li>
                <li>' . $detail_link2 . '</li>
                <li>' . $detail_link3 . '</li>
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li>' . $packagink_link . '</li>
                <li>' . $add_delivery_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $email_link . '</li>
                <li>' . $return_link . '</li>
                <li>' . $delete_link . '</li> 
                <li>' . $journal_entry_link . '</li>  
            </ul>
        </div></div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no,{$this->db->dbprefix('sales')}.sequence_code as code, biller, customer, (grand_total+COALESCE(rounding, 0)), paid, CONCAT(grand_total, '__', rounding, '__', paid) as balance, sale_status, payment_status, companies.email as cemail, warehouses.name as warehouse_name")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->join('warehouses', 'warehouses.id = sales.warehouse_id', 'left')
                ->where('warehouse_id', $warehouse_id)
                ->group_by('sales.id');
        } else {
            $this->datatables
                ->select($this->db->dbprefix('sales') . ".id as id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no,{$this->db->dbprefix('sales')}.sequence_code as code, biller, customer, (grand_total+COALESCE(rounding, 0)), paid, CONCAT(grand_total, '__', rounding, '__', paid) as balance, sale_status, payment_status, companies.email as cemail, warehouses.name as warehouse_name")
                ->from('sales')
                ->join('companies', 'companies.id=sales.customer_id', 'left')
                ->join('warehouses', 'warehouses.id = sales.warehouse_id', 'left')
                ->group_by('sales.id');
        }

        if (!empty($sid) && is_numeric($sid)) {
            $this->datatables->where($this->db->dbprefix('sales') . '.id', $sid);
        }

        if (!empty($sfromDate)) {
            $this->datatables->where('DATE(date) >=', $sfromDate);
        }

        if (!empty($stoDate)) {
            $this->datatables->where('DATE(date) <=', $stoDate);
        }

        if (!empty($swarehouse) && is_numeric($swarehouse)) {
            $this->datatables->where($this->db->dbprefix('sales') . '.warehouse_id', $swarehouse);

        }

        $this->datatables->where('pos', 1);
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id, cemail')->unset_column('cemail');
        echo $this->datatables->generate();
    }

    /* ---------------------------------------------------------------------------------------------------- */
    private function create_payload_for_gln($gln, $items) {
        $payload = [
            'DicOfDic' => [
                '202' => [
                    "167" => "",
                    "166"=> "",
                    "168"=> "",
                    "169"=> ""	 
                ],
                'MH' => [
                    'MN' => '160',
                    '222' => (string) $gln
                ]
            ],
            'DicOfDT' => [
                '202' => []
            ]
        ];

        foreach ($items as $item) {
            $payload['DicOfDT']['202'][] = [
                '223' => $item->gtin,
                '219' => $item->batchno,
                '214' => $item->serial_number
            ];
        }

        return $payload;
    }

    public function publish_sale($grouped_results, $warehouse_id){
        $cred = $this->sales_model->get_rasd_credential($warehouse_id);
        $this->rasd->set_base_url('https://qdttsbe.qtzit.com:10100/api/web');
        $res = $this->rasd->authenticate($cred['user'],$cred['pass']);
      
        if(isset($res['token']) && $res['token']){
              
            $auth_token = $res['token'];
            $this->rasd->set_headers([]);
            $this->rasd->set_auth_token($auth_token);
            $headers = array(
            'FunctionName:APIReq',
            'Token: '.$auth_token,
            'Accept :*/*',
            "Accept-Encoding : gzip, deflate, br"
            );
            $this->rasd->set_headers($headers);
            foreach ($grouped_results as $gln => $items) {
                $payload = $this->create_payload_for_gln($gln, $items);
                $response = $this->rasd->patient_pharmacy_sale_product_160($payload);
                $response_body = $response['body'];
                $this->process_api_response($response_body, $items);                
            }  
        }
    }
    private function process_api_response($response, $items) {
        // Check if the API call was successful
        if (isset($response['DicOfDic']['MR']['TRID'])&&$response['DicOfDic']['MR']['TRID'] ) {
            // Update the is_pushed status for these items
            $sale_ids = array_unique(array_column($items, 'sale_id'));
            $this->sales_model->mark_sales_as_reported($sale_ids);
        } else {
            // Log the error
            echo "Error Calling API";
        }
    }

    public function process_rasd_pharmacy_sales(){
        $serials_data = $this->pos_model->getUnprocessedSerials();

        if ($serials_data->num_rows() > 0) {
            foreach (($serials_data->result()) as $row) {
                $this->rasd->set_base_url('https://qdttsbe.qtzit.com:10100/api/web');
                $res = $this->rasd->authenticate($row->rasd_user,$row->rasd_pass);
            
                if(isset($res['token']) && $res['token']){
                    $auth_token = $res['token'];
                    $this->rasd->set_headers([]);
                    $this->rasd->set_auth_token($auth_token);
                    $headers = array(
                    'FunctionName:APIReq',
                    'Token: '.$auth_token,
                    'Accept :*/*',
                    "Accept-Encoding : gzip, deflate, br"
                    );
                    $this->rasd->set_headers($headers);

                    $item = array();

                    $item[] = (object)[
                        'batchno' => $row->batchno,
                        'serial_number' => $row->serial_number,
                        'gtin' => $row->gtin,
                    ];
                    $payload = $this->create_payload_for_gln($row->pharmacy_gln, $item);
                    $response = $this->rasd->patient_pharmacy_sale_product_160($payload);
                    $response_body = $response['body'];

                    $payload_used =  [
                        'source_gln' => '',
                        'destination_gln' => $row->pharmacy_gln,
                        'warehouse_id' => $row->warehouse_id
                    ];
                    
                    if (isset($response_body['DicOfDic']['MR']['TRID'])&&$response_body['DicOfDic']['MR']['TRID'] ) {
                        $this->sales_model->mark_sales_as_reported([$row->sale_id]);

                        $this->cmt_model->add_rasd_transactions($payload_used,'pharmacy_sale_product',true, $response,$payload);
                    } else {
                        // Log the error
                        echo "Error Calling API";
                        $this->cmt_model->add_rasd_transactions($payload_used,'pharmacy_sale_product',false, $response,$payload);
                    }
                }
            }
        }
    }

    public function index($sid = null)
    {
        $this->sma->checkPermissions();
        //echo $this->input->post();exit;

        if (!$this->pos_settings->default_biller || !$this->pos_settings->default_customer || !$this->pos_settings->default_category) {
            $this->session->set_flashdata('warning', lang('please_update_settings'));
            admin_redirect('pos/settings');
        }
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = ['register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date];
            $this->session->set_userdata($register_data);
        } else {
            $this->session->set_flashdata('error', lang('register_not_open'));
            admin_redirect('pos/open_register');
        }

        $this->data['sid'] = $this->input->get('suspend_id') ? $this->input->get('suspend_id') : $sid;
        $did               = $this->input->post('delete_id') ? $this->input->post('delete_id') : null;
        $suspend           = $this->input->post('suspend') ? true : false;
        $count             = $this->input->post('count') ? $this->input->post('count') : null;

        $duplicate_sale = $this->input->get('duplicate') ? $this->input->get('duplicate') : null;

        //validate form input
        $this->form_validation->set_rules('customer', $this->lang->line('customer'), 'trim|required');
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required');
        $this->form_validation->set_rules('biller', $this->lang->line('biller'), 'required');

        if ($this->form_validation->run() == true) {
            
           //echo "<pre>";print_r($_POST);exit;
            $date             = date('Y-m-d H:i:s');
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = 'completed';
            $payment_term     = 0;
            $due_date         = date('Y-m-d', strtotime('+' . $payment_term . ' days'));
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = $customer_details->company && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('pos_note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));
            $customer_name    = $this->sma->clear_tags($this->input->post('customer_name'));
            $mobile_number    = $this->sma->clear_tags($this->input->post('mobile_number'));
            $instructions     = $this->input->post('instructions');
            $medicinename     = $this->input->post('medicinename');
            $instructionsArr = array();

            for($i=0;$i<sizeOf($medicinename);$i++){
                $instructionsArr[$medicinename[$i]] = $instructions[$i];
            }
            $instructions_json = json_encode($instructionsArr);

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $digital          = false;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            $serials_info     = array();
            $serial_ids       = array();
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_comment       = $_POST['product_comment'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $_POST['real_unit_price'][$r];
                $unit_price         = $_POST['unit_price'][$r];
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                $item_serials       = $_POST['serial_numbers'][$r];
                $serials_array      = explode(',',$item_serials);

                //echo '<pre>';print_r($serials_array);exit;
                /*$product_details = $this->pos_model->getProductQuantityWithNearestExpiry($item_id, $item_code, $warehouse_id);
                if(empty($product_details)){
                    $this->session->set_flashdata('error', lang( $item_code. '-'. $item_name . ' may Expired Please remove it from the list'));
                    admin_redirect('pos');
                }  
                $batch_no = $product_details['batchno'];
                $expiry = $product_details['expiry'];*/
                // $item_unit_cost = $product_details['avg_cost']; 

                $batch_no = $_POST['batchno'][$r];
                $expiry = $_POST['item_expiry'][$r];
                $item_unit_cost = $_POST['item_unit_cost'][$r];
                $real_cost = $_POST['real_unit_cost'][$r];
                $avz_item_code = $_POST['avz_item_code'][$r];
                $serials_array = array_filter($serials_array, function($value) {
                    return !empty($value); // Keep only non-empty values
                });

                if(sizeof($serials_array) > 0){
                    foreach($serials_array as $serial){
                        $serial_ids []= $serial;
                        $serials_info[] = array('serial_number' => $serial, 'batchno' => $batch_no, 'gtin' => $item_code, 'avz_item_code' => $avz_item_code, 'is_pushed' => 0, 'sale_id' => 0, 'expiry' => $expiry, 'date_created' => date('Y-m-d'));
                    }
                    $serials_info = array_values(array_unique($serials_info, SORT_REGULAR));
                }else{
                    $serials_info = false;
                }

                //$item_unit_cost = $this->site->getAvgCost($batch_no, $item_id); 
                //$real_cost = $this->site->getRealAvgCost($batch_no, $item_id);

                /*if(empty($item_unit_cost)){
                    $this->session->set_flashdata('error', lang('Avg Cost not found for product: '.$item_code. '-'. $item_name ));
                    admin_redirect('pos');
                }*/ 
                    // $this->db->select('cost')->from('products')->where('id', $item_id);
                    // $productCost =$this->db->get()->result();
                
                $q = $this->db->get_where('products',['id' => $item_id]);
                $productCost = $q->row()->cost;


                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->pos_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    if ($item_type == 'digital') {
                        $digital = true;
                    }
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';
                    
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax    = $this->sma->formatDecimal($ctax['amount']);
                        $tax         = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);

                    /**
                     * new post values
                     */
                    $new_item_discount = $_POST['item_total_discount'][$r];
                    $new_discount1 = $_POST['item_discount1'][$r];
                    $new_item_vat_value = $_POST['item_vat_values'][$r];
                    $new_item_total_sale = $_POST['item_total_sale'][$r];
                    $new_item_unit_sale = $_POST['item_unit_sale'][$r];
                    $new_item_main_net  = $_POST['main_net'][$r];
                    $new_totalbeforevat = $_POST['totalbeforevat'][$r];
                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_cost'          => $item_unit_cost, // before it was prodcost
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $new_item_unit_sale, //$this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $new_item_vat_value,
                        'discount1'         => $new_discount1,
                        'discount'          => $item_discount,
                        'item_discount'     => $new_item_discount,
                        'subtotal'          => $new_item_total_sale,//$this->sma->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'expiry'            => $expiry,
                        'batch_no'          => $batch_no,
                        'real_unit_price'   => $real_unit_price,
                        'comment'           => $item_comment,
                        'real_cost'         => $real_cost,
                        'avz_item_code'     => $avz_item_code,
                        'main_net'          => $new_item_main_net,
                        'totalbeforevat	'   => $new_totalbeforevat
                    ];

                    $products[] = ($product + $gst_data);
                    $total += ($item_net_price * $item_unit_quantity);
                }
            }


            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } elseif ($this->pos_settings->item_order == 0) {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), ($total), true);
            $total_discount = ($order_discount + $product_discount);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total - $order_discount));
            $total_tax      = ($product_tax + $order_tax);
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $grand_total = ($total + $total_tax + $shipping - $order_discount);
            $rounding    = 0;
            if ($this->pos_settings->rounding) {
                $round_total = $this->sma->roundNumber($grand_total, $this->pos_settings->rounding);
                $rounding    = $round_total - $grand_total;
            }

              /**
             * post values
             */
            
             $grand_total_net_sale = $this->input->post('grand_total_net_sale');
             $grand_total_discount = $this->input->post('grand_total_discount');
             $grand_total_vat = $this->input->post('grand_total_vat');
             $grand_total_sale = $this->input->post('grand_total_sale');
             $grand_cost_goods_sold = $this->input->post('cost_goods_sold');
             $grand_total = $this->input->post('grand_total');

            $data = ['date'         => $date,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $grand_total_sale,
                'total_net_sale'    => $grand_total_net_sale,
                'product_discount'  => $product_discount,
                'order_discount_id' => str_replace('%', '', $this->input->post('discount')),
                'order_discount'    => $order_discount,
                'total_discount'    => $grand_total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $grand_total_vat,
                'shipping'          => $this->sma->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $grand_total > 0 ? 'due' : 'paid',
                'payment_term'      => $payment_term,
                'rounding'          => $rounding,
                'suspend_note'      => $this->input->post('suspend_note'),
                'pos'               => 1,
                'cost_goods_sold'   => $grand_cost_goods_sold,
                'paid'              => $this->input->post('amount-paid') ? $this->input->post('amount-paid') : 0,
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
            ];

            if($mobile_number != ''){
                $data['mobile_number'] = $mobile_number;
            }

            if($customer_name != ''){
                $data['customer_name'] = $customer_name;
            }

            if($instructions_json != ''){
                $data['instructions'] = $instructions_json;
            }

            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if (!$suspend) {
                $p    = isset($_POST['amount']) ? sizeof($_POST['amount']) : 0;
                $paid = 0;
                for ($r = 0; $r < $p; $r++) {
                    if (isset($_POST['amount'][$r]) && !empty($_POST['amount'][$r]) && isset($_POST['paid_by'][$r]) && !empty($_POST['paid_by'][$r])) {
                        $amount = $_POST['balance_amount'][$r] > 0 ? $_POST['amount'][$r] - $_POST['balance_amount'][$r] : $_POST['amount'][$r];
                        if ($_POST['paid_by'][$r] == 'deposit') {
                            if (!$this->site->check_customer_deposit($customer_id, $amount)) {
                                $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                        }
                        if ($_POST['paid_by'][$r] == 'gift_card') {
                            $gc            = $this->site->getGiftCardByNO($_POST['paying_gift_card_no'][$r]);
                            $amount_paying = $_POST['amount'][$r] >= $gc->balance ? $gc->balance : $_POST['amount'][$r];
                            $gc_balance    = $gc->balance - $amount_paying;
                            $payment[]     = [
                                'date'        => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                'amount'      => $amount,
                                'paid_by'     => $_POST['paid_by'][$r],
                                'cheque_no'   => $_POST['cheque_no'][$r],
                                'cc_no'       => $_POST['paying_gift_card_no'][$r],
                                'cc_holder'   => $_POST['cc_holder'][$r],
                                'cc_month'    => $_POST['cc_month'][$r],
                                'cc_year'     => $_POST['cc_year'][$r],
                                'cc_type'     => $_POST['cc_type'][$r],
                                'cc_cvv2'     => $_POST['cc_cvv2'][$r],
                                'created_by'  => $this->session->userdata('user_id'),
                                'type'        => 'received',
                                'note'        => $_POST['payment_note'][$r],
                                'pos_paid'    => $_POST['amount'][$r],
                                'pos_balance' => $_POST['balance_amount'][$r],
                                'gc_balance'  => $gc_balance,
                            ];
                        } else {
                            $payment[] = [
                                'date' => $date,
                                // 'reference_no' => $this->site->getReference('pay'),
                                'amount'      => $amount,
                                'paid_by'     => $_POST['paid_by'][$r],
                                'cheque_no'   => $_POST['cheque_no'][$r],
                                'cc_no'       => $_POST['cc_no'][$r],
                                'cc_holder'   => $_POST['cc_holder'][$r],
                                'cc_month'    => $_POST['cc_month'][$r],
                                'cc_year'     => $_POST['cc_year'][$r],
                                'cc_type'     => $_POST['cc_type'][$r],
                                'cc_cvv2'     => $_POST['cc_cvv2'][$r],
                                'created_by'  => $this->session->userdata('user_id'),
                                'type'        => 'received',
                                'note'        => $_POST['payment_note'][$r],
                                'pos_paid'    => $_POST['amount'][$r],
                                'pos_balance' => $_POST['balance_amount'][$r],
                            ];
                        }
                    }
                }
            }
            if (!isset($payment) || empty($payment)) {
                $payment = [];
            }
            //$this->sma->print_arrays($data, $products, $payment, $serials_info);exit;
        }

        if ($this->form_validation->run() == true && !empty($products) && !empty($data)) {
            if ($suspend) {
                if ($this->pos_model->suspendSale($data, $products, $did)) {
                    $this->session->set_userdata('remove_posls', 1);
                    $this->session->set_flashdata('message', $this->lang->line('sale_suspended'));
                    admin_redirect('pos');
                }
            } else {
                $rsdItems = '';
                if ($sale = $this->pos_model->addSale($data, $products, $payment,$rsdItems, $did)) {
                        /**Added the Zatca Integration */
                        if($this->zatca_enabled){
                            $zatca_payload =  $this->Zetca_model->get_zatca_data($sale['sale_id']);                            
                            $zatca_response = $this->zatca->post('',  $zatca_payload);
                             $is_success = true;
                            $remarks = "";
                            if($zatca_response['status'] >= 400){
                                $is_success = false;
                                if(isset($zatca_response['body']['errors'])){
                                    if(!empty($zatca_response['body']['errors'])){
                                        $remarks = $zatca_response['body']['errors'][0];
                                    }
                                }
                            }
                            $date = date('Y-m-d H:i:s');
                            $request = json_encode($zatca_payload, true);
                            $response = json_encode($zatca_response, true);
                            $reporting_data = [
                                "sale_id" => $sale['sale_id'],
                                "date" => $date,
                                "is_success" => $is_success,
                                "request" => $request,
                                "response" => $response,
                                "remarks" => $remarks
                            ];
                            $this->Zetca_model->report_zatca_status($reporting_data);
                           
                        }
                        /**End of Integration */
                      

                    if ($serials_info) {
                        foreach ($serials_info as &$serialArr) { // Use & to pass by reference
                            $serialArr['sale_id'] = $sale['sale_id'];
                        }
                        unset($serialArr); // Unset reference after loop to prevent accidental modifications
                        $this->pos_model->addSerialsBatch($serials_info);                       
                        //$sales_to_report_grouped = $this->sales_model->get_unreported_sales($serial_ids);
                        /*if(!empty($sales_to_report_grouped)){
                           $this->publish_sale($sales_to_report_grouped, $warehouse_id);
                        }*/
                    }

                    $this->session->set_userdata('remove_posls', 1);
                    $msg = $this->lang->line('sale_added');
                    if (!empty($sale['message'])) {
                        foreach ($sale['message'] as $m) {
                            $msg .= '<br>' . $m;
                        }
                    }
                    $this->session->set_flashdata('message', $msg);
                    $redirect_to = $this->pos_settings->after_sale_page ? 'pos' : 'pos/view/' . $sale['sale_id'];
                    if ($this->pos_settings->auto_print) {
                        if ($this->Settings->remote_printing != 1) {
                            $redirect_to .= '?print=' . $sale['sale_id'];
                        }
                    }

                    $sid = $sale['sale_id'];
                    
                    //$payemntsType = $this->pos_model->getPaymentType($sid);
                    //$paidBillType = $payemntsType->paid_by;

                    $payemntsTypes = $this->pos_model->getPaymentTypes($sid);
                    
                    $inv = $this->sales_model->getSaleByID($sid);
                    if($inv->sale_invoice == 0){
                    if ($this->sales_model->saleToInvoice($sid)) {
                        $this->load->admin_model('companies_model');
                        $this->load->admin_model('settings_model');
                        $customer = $this->settings_model->getWarehouseByID($inv->warehouse_id);

                        /*Accounts Entries*/
                        $entry = array(
                        'entrytype_id' => 4,
                        'number'       => 'SO-'.$inv->reference_no,
                        'date'         => date('Y-m-d'), 
                        'dr_total'     => $inv->grand_total,
                        'cr_total'     => $inv->grand_total,
                        'notes'        => 'POS Reference: '.$inv->reference_no.' Date: '.date('Y-m-d H:i:s'),
                        'sid'          =>  $inv->id,
                        'transaction_type'   =>  'pos',
                        'customer_id'  => $inv->customer_id
                        );
                        
                        $add  = $this->db->insert('sma_accounts_entries', $entry);
                        $insert_id = $this->db->insert_id();
                        
                        //$insert_id = 999;
                        $entryitemdata = array();

                        $inv_items = $this->sales_model->getAllSaleItems($sid);

                        $totalSalePrice = 0;
                        $totalPurchasePrice = 0;
                        foreach ($inv_items as $item) 
                        {
                            $proid = $item->product_id;
                            $product  = $this->site->getProductByID($proid);

                            $totalSalePrice = ($totalSalePrice)+($item->net_unit_price * $item->quantity);
                            $totalPurchasePrice = $totalPurchasePrice + ($item->net_cost * $item->quantity);
                        }

                        //$amount_paid_pos = $_POST['amount'][0];
                        $amount_paid_pos = 0;
                        $amount_due_pos = 0;
                        $pos_amount_balance = 0;
                        foreach ($payemntsTypes as $payemntsType){
                            $paidBillType = $payemntsType->paid_by;
                            $amount_due_pos += $payemntsType->amount;
                            $amount_paid_pos += $payemntsType->pos_paid;

                            if($paidBillType =="cash"){
                                // check if cash has decimal 
                                 $paidAmount = $payemntsType->amount ;
                                 //echo 'paid amount:'.$paidAmount;
                                 //echo 'floor:'. floor($paidAmount);
                                 
                                $halalaAmount = 0;
                                if( floor($paidAmount)  != $paidAmount) {
                                    $integerPart = floor($payemntsType->amount);
                                    $decimalPart = $paidAmount - $integerPart;
                                    //echo 'integer'.$integerPart;
                                    //echo 'decimal'. $decimalPart;
                                  
                                    if ($decimalPart <= 0.50) {
                                        $paidAmount =  $integerPart; 
                                        $halalaAmount = $decimalPart;
                                        $difference_type = 'D';
                                    } else {
                                        $difference_type = 'C';
                                        $paidAmount = $integerPart + 1; 
                                        $halalaAmount = 1 - $decimalPart;
                                    }
                                    
                                }
                                 //echo 'paidamount'.$paidAmount;
                                 //echo 'halaamount'.$halalaAmount;
                                 //exit;
                               
                                // //cash
                                $entryitemdata[] = array(
                                'Entryitem' => array(
                                'entry_id' => $insert_id,
                                'dc' => 'D',
                                'ledger_id' => $customer->fund_books_ledger,
                                //'amount' =>(($totalSalePrice + $inv->order_tax) - $inv->total_discount),
                                'amount' => $paidAmount,
                                'narration' => 'cash'
                                )
                                );
                                
                                if( $halalaAmount > 0) {
                                    $entryitemdata[] = array(
                                        'Entryitem' => array(
                                            'entry_id' => $insert_id,
                                            'dc' => $difference_type,
                                            'ledger_id' => $customer->price_difference_ledger,
                                            'amount' => abs($halalaAmount),
                                            'narration' => 'Halala Difference'
                                        )
                                    ); 
                                }
                              

                            }else{
                                   // //credit card
                                $entryitemdata[] = array(
                                    'Entryitem' => array(
                                    'entry_id' => $insert_id,
                                    'dc' => 'D',
                                    'ledger_id' => $customer->credit_card_ledger,
                                    //'amount' =>(($totalSalePrice + $inv->order_tax) - $inv->total_discount),
                                    'amount' => $payemntsType->amount,
                                    'narration' => 'Credit Card'
                                    )
                                );  
                            }

                            $pos_amount_balance = $payemntsType->pos_balance;
                        }

                        //$price_difference = $amount_paid_pos - ($totalSalePrice + $inv->total_tax - $inv->total_discount);
                        
                        //$price_difference = $amount_due_pos - $amount_paid_pos;

                        $price_difference = $pos_amount_balance;
                        

                        // cost of goods sold
                        $entryitemdata[] = array(
                            'Entryitem' => array(
                            'entry_id' => $insert_id,
                            'dc' => 'D',
                            'ledger_id' => $customer->cogs_ledger,
                            'amount' => $inv->cost_goods_sold,
                            'narration' => 'cost of goods sold'
                            )
                        );

                        // inventory
                        $entryitemdata[] = array(
                            'Entryitem' => array(
                            'entry_id' => $insert_id,
                            'dc' => 'C',
                            'ledger_id' => $customer->inventory_ledger,
                            'amount' => $inv->cost_goods_sold,
                            'narration' => 'inventory'
                            )
                        );

                        // // sale account
                        $entryitemdata[] = array(
                            'Entryitem' => array(
                                'entry_id' => $insert_id,
                                'dc' => 'C',
                                'ledger_id' => $customer->sales_ledger,
                                'amount' => $inv->total,
                                'narration' => 'sale'
                            )
                        );
                          

                        // //discount
                        $entryitemdata[] = array(
                            'Entryitem' => array(
                                'entry_id' => $insert_id,
                                'dc' => 'D',
                                'ledger_id' => $customer->discount_ledger,
                                'amount' => $inv->total_discount,
                                'narration' => 'discount'
                            )
                        );
                     
                        // //vat on sale
                        $entryitemdata[] = array(
                                    'Entryitem' => array(
                                        'entry_id' => $insert_id,
                                        'dc' => 'C',
                                        'ledger_id' => $customer->vat_on_sales_ledger,
                                        'amount' => $inv->total_tax,
                                        'narration' => 'vat on sale'
                                    )
                                );

                        if($price_difference != 0){
                            // //price difference
                            // $entryitemdata[] = array(
                            //     'Entryitem' => array(
                            //         'entry_id' => $insert_id,
                            //         'dc' => $difference_type,
                            //         'ledger_id' => $customer->price_difference_ledger,
                            //         'amount' => abs($price_difference),
                            //         'narration' => 'Halala Difference'
                            //     )
                            // );
                        }    
                        
                        $total_invoice_entry = $inv->total_tax + $totalSalePrice + $totalPurchasePrice;
                        if($price_difference != 0){
                            $total_invoice_entry += abs($price_difference);
                        }

                        $this->db->update('sma_accounts_entries', ['dr_total' => $total_invoice_entry, 'cr_total' => $total_invoice_entry], ['id' => $insert_id]);
                                
                       //   /*Accounts Entry Items*/
                       foreach ($entryitemdata as $row => $itemdata)
                       {
                             $this->db->insert('sma_accounts_entryitems', $itemdata['Entryitem']);
                       }
        
                        admin_redirect($redirect_to);
                   }
                    }else{
           
                       $this->session->set_flashdata('error', lang('Sale Already Converted to invoice!'));
                       admin_redirect($_SERVER['HTTP_REFERER'] ?? 'sales');
                   }



                  
                }
            }
        } else {
            $this->data['old_sale'] = null;
            $this->data['oid']      = null;
            if ($duplicate_sale) {
                if ($old_sale = $this->pos_model->getInvoiceByID($duplicate_sale)) {
                    $inv_items              = $this->pos_model->getSaleItems($duplicate_sale);
                    $this->data['oid']      = $duplicate_sale;
                    $this->data['old_sale'] = $old_sale;
                    $this->data['message']  = lang('old_sale_loaded');
                    $this->data['customer'] = $this->pos_model->getCompanyByID($old_sale->customer_id);
                } else {
                    $this->session->set_flashdata('error', lang('bill_x_found'));
                    admin_redirect('pos');
                }
            }
            $this->data['suspend_sale'] = null;
            if ($sid) {
                if ($suspended_sale = $this->pos_model->getOpenBillByID($sid)) {
                    $inv_items                    = $this->pos_model->getSuspendedSaleItems($sid);
                    $this->data['sid']            = $sid;
                    $this->data['suspend_sale']   = $suspended_sale;
                    $this->data['message']        = lang('suspended_sale_loaded');
                    $this->data['customer']       = $this->pos_model->getCompanyByID($suspended_sale->customer_id);
                    $this->data['reference_note'] = $suspended_sale->suspend_note;
                } else {
                    $this->session->set_flashdata('error', lang('bill_x_found'));
                    admin_redirect('pos');
                }
            }

            if (($sid || $duplicate_sale) && $inv_items) {
                // krsort($inv_items);
                $c = rand(100000, 9999999);
                foreach ($inv_items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row             = json_decode('{}');
                        $row->tax_method = 0;
                        $row->quantity   = 0;
                    } else {
                        $category           = $this->site->getCategoryByID($row->category_id);
                        $row->category_name = $category->name;
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
                    }
                    $row->id   = $item->product_id;
                    $row->code = $item->product_code;
                    $row->name = $item->product_name;
                    $row->type = $item->product_type;
                    $row->quantity += $item->quantity;
                    $row->discount        = $item->discount ? $item->discount : '0';
                    $row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity));
                    $row->unit_price      = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->quantity) + $this->sma->formatDecimal($item->item_tax / $item->quantity) : $item->unit_price + ($item->item_discount / $item->quantity);
                    $row->real_unit_price = $item->real_unit_price;
                    $row->base_quantity   = $item->quantity;
                    $row->base_unit       = $row->unit ? $row->unit : $item->product_unit_id;
                    // $row->base_unit_price = $row->price ? $row->price : $item->unit_price;
                    $row->base_unit_price = $item->real_unit_price;
                    $row->unit            = $item->product_unit_id;
                    $row->qty             = $item->unit_quantity;
                    $row->tax_rate        = $item->tax_rate_id;
                    $row->serial          = $item->serial_no;
                    $row->option          = $item->option_id;
                    $options              = $this->pos_model->getProductOptions($row->id, $item->warehouse_id);

                    if ($options) {
                        $option_quantity = 0;
                        foreach ($options as $option) {
                            $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                            if ($pis) {
                                foreach ($pis as $pi) {
                                    $option_quantity += $pi->quantity_balance;
                                }
                            }
                            if ($option->quantity > $option_quantity) {
                                $option->quantity = $option_quantity;
                            }
                        }
                    }

                    $row->comment = $item->comment ?? '';
                    $row->ordered = 1;
                    $combo_items  = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->pos_model->getProductComboItems($row->id, $item->warehouse_id);
                    }
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri       = $this->Settings->item_addition ? $row->id : $c;

                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                    $c++;
                }

                $this->data['items'] = json_encode($pr);
            // $this->sma->print_arrays($this->data['items']);
            } else {
                $this->data['customer']       = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
                $this->data['reference_note'] = null;
            }

            $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['message'] = $this->data['message'] ?? $this->session->flashdata('message');

            // $this->data['biller'] = $this->site->getCompanyByID($this->pos_settings->default_biller);
            $this->data['billers']       = $this->site->getAllCompanies('biller');
            $this->data['warehouses']    = $this->site->getAllWarehouses();
            $this->data['pharmacies']    = $this->site->getAllPharmacies();
            $this->data['tax_rates']     = $this->site->getAllTaxRates();
            $this->data['user']          = $this->site->getUser();
            $this->data['tcp']           = $this->pos_model->products_count($this->pos_settings->default_category);
            $this->data['products']      = $this->ajaxproducts($this->pos_settings->default_category);
            $this->data['categories']    = $this->site->getAllCategories();
            $this->data['brands']        = $this->site->getAllBrands();
            $this->data['subcategories'] = $this->site->getSubCategories($this->pos_settings->default_category);
            $this->data['printer']       = $this->pos_model->getPrinterByID($this->pos_settings->printer);
            $order_printers              = json_decode($this->pos_settings->order_printers);
            $printers                    = [];
            if (!empty($order_printers)) {
                foreach ($order_printers as $printer_id) {
                    $printers[] = $this->pos_model->getPrinterByID($printer_id);
                }
            }
            $this->data['order_printers'] = $printers;
            $this->data['pos_settings']   = $this->pos_settings;

            if ($this->pos_settings->after_sale_page && $saleid = $this->input->get('print', true)) {
                if ($inv = $this->pos_model->getInvoiceByID($saleid)) {
                    $this->load->helper('pos');
                    if (!$this->session->userdata('view_right')) {
                        $this->sma->view_rights($inv->created_by, true);
                    }
                    $this->data['rows']            = $this->pos_model->getAllInvoiceItems($inv->id);
                    $this->data['biller']          = $this->pos_model->getCompanyByID($inv->biller_id);
                    $this->data['customer']        = $this->pos_model->getCompanyByID($inv->customer_id);
                    $this->data['payments']        = $this->pos_model->getInvoicePayments($inv->id);
                    $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
                    $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
                    $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
                    $this->data['inv']             = $inv;
                    $this->data['print']           = $inv->id;
                    $this->data['created_by']      = $this->site->getUser($inv->created_by);
                }
            }

            $this->load->view($this->theme . 'pos/add', $this->data);
        }
    }

    public function install_update($file, $m_version, $version)
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->load->helper('update');
        save_remote_file($file . '.zip');
        $this->sma->unzip('./files/updates/' . $file . '.zip');
        if ($m_version) {
            $this->load->library('migration');
            if (!$this->migration->latest()) {
                $this->session->set_flashdata('error', $this->migration->error_string());
                admin_redirect('pos/updates');
            }
        }
        $this->db->update('pos_settings', ['version' => $version], ['pos_id' => 1]);
        unlink('./files/updates/' . $file . '.zip');
        $this->session->set_flashdata('success', lang('update_done'));
        admin_redirect('pos/updates');
    }

    public function open_drawer()
    {
        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->open_drawer();
    }

    public function open_register()
    {
        $this->sma->checkPermissions('index');
        $this->form_validation->set_rules('cash_in_hand', lang('cash_in_hand'), 'trim|required|numeric');
        if ($register = $this->pos_model->registerData($this->session->userdata('user_id'))) {
            $register_data = ['register_id' => $register->id, 'cash_in_hand' => $register->cash_in_hand, 'register_open_time' => $register->date];
            $this->session->set_userdata($register_data);
            admin_redirect('pos');
        }

        if ($this->form_validation->run() == true) {
            $data = [
                'date'         => date('Y-m-d H:i:s'),
                'cash_in_hand' => $this->input->post('cash_in_hand'),
                'user_id'      => $this->session->userdata('user_id'),
                'status'       => 'open',
            ];
        }
        if ($this->form_validation->run() == true && $this->pos_model->openRegister($data)) {
            $this->session->set_flashdata('message', lang('welcome_to_pos'));
            admin_redirect('pos');
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $bc                  = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('open_register')]];
            $meta                = ['page_title' => lang('open_register'), 'bc' => $bc];
            $this->page_construct('pos/open_register', $meta, $this->data);
        }
    }

    public function opened_bills($per_page = 0)
    {
        $this->load->library('pagination');

        //$this->table->set_heading('Id', 'The Title', 'The Content');
        if ($this->input->get('per_page')) {
            $per_page = $this->input->get('per_page');
        }

        $config['base_url']   = admin_url('pos/opened_bills');
        $config['total_rows'] = $this->pos_model->bills_count();
        $config['per_page']   = 6;
        $config['num_links']  = 3;

        $config['full_tag_open']   = '<ul class="pagination pagination-sm">';
        $config['full_tag_close']  = '</ul>';
        $config['first_tag_open']  = '<li>';
        $config['first_tag_close'] = '</li>';
        $config['last_tag_open']   = '<li>';
        $config['last_tag_close']  = '</li>';
        $config['next_tag_open']   = '<li>';
        $config['next_tag_close']  = '</li>';
        $config['prev_tag_open']   = '<li>';
        $config['prev_tag_close']  = '</li>';
        $config['num_tag_open']    = '<li>';
        $config['num_tag_close']   = '</li>';
        $config['cur_tag_open']    = '<li class="active"><a>';
        $config['cur_tag_close']   = '</a></li>';

        $this->pagination->initialize($config);
        $data['r'] = true;
        $bills     = $this->pos_model->fetch_bills($config['per_page'], $per_page);
        if (!empty($bills)) {
            $html = '';
            $html .= '<ul class="ob">';
            foreach ($bills as $bill) {
                $html .= '<li><button type="button" class="btn btn-info sus_sale" id="' . $bill->id . '"><p>' . $bill->suspend_note . '</p><strong>' . $bill->customer . '</strong><br>' . lang('date') . ': ' . $bill->date . '<br>' . lang('items') . ': ' . $bill->count . '<br>' . lang('total') . ': ' . $this->sma->formatMoney($bill->total) . '</button><a class="btn btn-danger" href=' . admin_url("pos/delete_bills/$bill->id") . '>Remove</a></li>';
            }
            $html .= '</ul>';
        } else {
            $html      = '<h3>' . lang('no_opeded_bill') . '</h3><p>&nbsp;</p>';
            $data['r'] = false;
        }

        $data['html'] = $html;

        $data['page'] = $this->pagination->create_links();
        echo $this->load->view($this->theme . 'pos/opened', $data, true);
    }

    

    public function delete_bills($id)
    {
        if ($this->pos_model->deleteBill($id)) {
            admin_redirect('pos');
        } else {
            admin_redirect('pos');
        }

    }

    public function p()
    {
        $data = json_decode($this->input->get('data'));
        $this->load->library('escpos');
        $this->escpos->load($data->printer);
        $this->escpos->print_receipt($data);
    }

    public function paypal_balance()
    {
        if (!$this->Owner) {
            return false;
        }
        $this->load->admin_model('paypal_payments');

        return $this->paypal_payments->get_balance();
    }

    public function printers()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('pos');
        }
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('printers');
        $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('printers')]];
        $meta                     = ['page_title' => lang('list_printers'), 'bc' => $bc];
        $this->page_construct('pos/printers', $meta, $this->data);
    }

    public function register_details()
    {
        //for testing purpose - remove after testing
        $user_id =6655;
        $this->sma->checkPermissions('index');
        $register_open_time           = $this->session->userdata('register_open_time');
        $this->data['error']          = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales']        = $this->pos_model->getRegisterCCSales($register_open_time, $user_id);
        $this->data['cashsales']      = $this->pos_model->getRegisterCashSales($register_open_time, $user_id);
        $this->data['chsales']        = $this->pos_model->getRegisterChSales($register_open_time, $user_id);
        $this->data['gcsales']        = $this->pos_model->getRegisterGCSales($register_open_time);
        $this->data['pppsales']       = $this->pos_model->getRegisterPPPSales($register_open_time);
        $this->data['stripesales']    = $this->pos_model->getRegisterStripeSales($register_open_time);
        $this->data['othersales']     = $this->pos_model->getRegisterOtherSales($register_open_time);
        $this->data['authorizesales'] = $this->pos_model->getRegisterAuthorizeSales($register_open_time);
        $this->data['totalsales']     = $this->pos_model->getRegisterSales($register_open_time, $user_id);
        $this->data['refunds']        = $this->pos_model->getRegisterRefunds($register_open_time, $user_id);
        $this->data['returns']        = $this->pos_model->getRegisterReturns($register_open_time, $user_id);
        $this->data['expenses']       = $this->pos_model->getRegisterExpenses($register_open_time, $user_id);
        $this->load->view($this->theme . 'pos/register_details', $this->data);
    }

    public function registers()
    {
        $this->sma->checkPermissions();

        $this->data['error']     = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['registers'] = $this->pos_model->getOpenRegisters();
        $bc                      = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('open_registers')]];
        $meta                    = ['page_title' => lang('open_registers'), 'bc' => $bc];
        $this->page_construct('pos/registers', $meta, $this->data);
    }

    public function sales($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $user                       = $this->site->getUser();
            $this->data['warehouses']   = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse']    = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }
        
        $this->data['sid'] = $this->input->get('sid');
        $this->data['sfromDate'] = $this->input->get('from');
        $this->data['stoDate'] = $this->input->get('to');
        $this->data['swarehouse'] = $this->input->get('warehouse');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('pos_sales')]];
        $meta = ['page_title' => lang('pos_sales'), 'bc' => $bc];
        $this->page_construct('pos/sales', $meta, $this->data);
    }

    public function settings()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('pro_limit', $this->lang->line('pro_limit'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('pin_code', $this->lang->line('delete_code'), 'numeric');
        $this->form_validation->set_rules('category', $this->lang->line('default_category'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('customer', $this->lang->line('default_customer'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('biller', $this->lang->line('default_biller'), 'required|is_natural_no_zero');

        if ($this->form_validation->run() == true) {
            $data = [
                'pro_limit'                 => $this->input->post('pro_limit'),
                'pin_code'                  => $this->input->post('pin_code') ? $this->input->post('pin_code') : null,
                'default_category'          => $this->input->post('category'),
                'default_customer'          => $this->input->post('customer'),
                'default_biller'            => $this->input->post('biller'),
                'display_time'              => $this->input->post('display_time'),
                'receipt_printer'           => $this->input->post('receipt_printer'),
                'cash_drawer_codes'         => $this->input->post('cash_drawer_codes'),
                'cf_title1'                 => $this->input->post('cf_title1'),
                'cf_title2'                 => $this->input->post('cf_title2'),
                'cf_value1'                 => $this->input->post('cf_value1'),
                'cf_value2'                 => $this->input->post('cf_value2'),
                'focus_add_item'            => $this->input->post('focus_add_item'),
                'add_manual_product'        => $this->input->post('add_manual_product'),
                'customer_selection'        => $this->input->post('customer_selection'),
                'add_customer'              => $this->input->post('add_customer'),
                'toggle_category_slider'    => $this->input->post('toggle_category_slider'),
                'toggle_subcategory_slider' => $this->input->post('toggle_subcategory_slider'),
                'toggle_brands_slider'      => $this->input->post('toggle_brands_slider'),
                'cancel_sale'               => $this->input->post('cancel_sale'),
                'suspend_sale'              => $this->input->post('suspend_sale'),
                'print_items_list'          => $this->input->post('print_items_list'),
                'finalize_sale'             => $this->input->post('finalize_sale'),
                'today_sale'                => $this->input->post('today_sale'),
                'open_hold_bills'           => $this->input->post('open_hold_bills'),
                'close_register'            => $this->input->post('close_register'),
                'tooltips'                  => $this->input->post('tooltips'),
                'keyboard'                  => $this->input->post('keyboard'),
                'pos_printers'              => $this->input->post('pos_printers'),
                'java_applet'               => $this->input->post('enable_java_applet'),
                'product_button_color'      => $this->input->post('product_button_color'),
                'paypal_pro'                => $this->input->post('paypal_pro'),
                'stripe'                    => $this->input->post('stripe'),
                'authorize'                 => $this->input->post('authorize'),
                'rounding'                  => $this->input->post('rounding'),
                'item_order'                => $this->input->post('item_order'),
                'after_sale_page'           => $this->input->post('after_sale_page'),
                'printer'                   => $this->input->post('receipt_printer'),
                'order_printers'            => json_encode($this->input->post('order_printers')),
                'auto_print'                => $this->input->post('auto_print'),
                'remote_printing'           => DEMO ? 1 : $this->input->post('remote_printing'),
                'customer_details'          => $this->input->post('customer_details'),
                'local_printers'            => $this->input->post('local_printers'),
            ];
            $payment_config = [
                'APIUsername'            => $this->input->post('APIUsername'),
                'APIPassword'            => $this->input->post('APIPassword'),
                'APISignature'           => $this->input->post('APISignature'),
                'stripe_secret_key'      => $this->input->post('stripe_secret_key'),
                'stripe_publishable_key' => $this->input->post('stripe_publishable_key'),
                'api_login_id'           => $this->input->post('api_login_id'),
                'api_transaction_key'    => $this->input->post('api_transaction_key'),
            ];
        } elseif ($this->input->post('update_settings')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('pos/settings');
        }

        if ($this->form_validation->run() == true && $this->pos_model->updateSetting($data)) {
            if (DEMO) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                admin_redirect('pos/settings');
            }
            if ($this->write_payments_config($payment_config)) {
                $this->session->set_flashdata('message', $this->lang->line('pos_setting_updated'));
                admin_redirect('pos/settings');
            } else {
                $this->session->set_flashdata('error', $this->lang->line('pos_setting_updated_payment_failed'));
                admin_redirect('pos/settings');
            }
        } else {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

            $this->data['pos']        = $this->pos_model->getSetting();
            $this->data['categories'] = $this->site->getAllCategories();
            //$this->data['customer'] = $this->pos_model->getCompanyByID($this->pos_settings->default_customer);
            $this->data['billers'] = $this->pos_model->getAllBillerCompanies();
            $this->config->load('payment_gateways');
            $this->data['stripe_secret_key']      = $this->config->item('stripe_secret_key');
            $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
            $authorize                            = $this->config->item('authorize');
            $this->data['api_login_id']           = $authorize['api_login_id'];
            $this->data['api_transaction_key']    = $authorize['api_transaction_key'];
            $this->data['APIUsername']            = $this->config->item('APIUsername');
            $this->data['APIPassword']            = $this->config->item('APIPassword');
            $this->data['APISignature']           = $this->config->item('APISignature');
            $this->data['printers']               = $this->pos_model->getAllPrinters();
            $this->data['paypal_balance']         = null; // $this->pos_settings->paypal_pro ? $this->paypal_balance() : NULL;
            $this->data['stripe_balance']         = null; // $this->pos_settings->stripe ? $this->stripe_balance() : NULL;
            $bc                                   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('pos_settings')]];
            $meta                                 = ['page_title' => lang('pos_settings'), 'bc' => $bc];
            $this->page_construct('pos/settings', $meta, $this->data);
        }
    }

    public function stripe_balance()
    {
        if (!$this->Owner) {
            return false;
        }
        $this->load->admin_model('stripe_payments');

        return $this->stripe_payments->get_balance();
    }

    public function today_sale()
    {
        if (!$this->Owner && !$this->Admin) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        $this->data['error']          = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['ccsales']        = $this->pos_model->getTodayCCSales();
        $this->data['cashsales']      = $this->pos_model->getTodayCashSales();
        $this->data['chsales']        = $this->pos_model->getTodayChSales();
        $this->data['pppsales']       = $this->pos_model->getTodayPPPSales();
        $this->data['stripesales']    = $this->pos_model->getTodayStripeSales();
        $this->data['authorizesales'] = $this->pos_model->getTodayAuthorizeSales();
        $this->data['totalsales']     = $this->pos_model->getTodaySales();
        $this->data['refunds']        = $this->pos_model->getTodayRefunds();
        $this->data['returns']        = $this->pos_model->getTodayReturns();
        $this->data['expenses']       = $this->pos_model->getTodayExpenses();
        $this->load->view($this->theme . 'pos/today_sale', $this->data);
    }

    public function updates()
    {
        if (DEMO) {
            $this->session->set_flashdata('warning', lang('disabled_in_demo'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        $this->form_validation->set_rules('purchase_code', lang('purchase_code'), 'required');
        $this->form_validation->set_rules('envato_username', lang('envato_username'), 'required');
        if ($this->form_validation->run() == true) {
            $this->db->update('pos_settings', ['purchase_code' => $this->input->post('purchase_code', true), 'envato_username' => $this->input->post('envato_username', true)], ['pos_id' => 1]);
            admin_redirect('pos/updates');
        } else {
            $fields = ['version' => $this->pos_settings->version, 'code' => $this->pos_settings->purchase_code, 'username' => $this->pos_settings->envato_username, 'site' => base_url()];
            $this->load->helper('update');
            $protocol              = is_https() ? 'https://' : 'http://';
            $updates               = get_remote_contents($protocol . 'api.tecdiary.com/v1/update/', $fields);
            $this->data['updates'] = json_decode($updates);
            $bc                    = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('updates')]];
            $meta                  = ['page_title' => lang('updates'), 'bc' => $bc];
            $this->page_construct('pos/updates', $meta, $this->data);
        }
    }

    /* ------------------------------------------------------------------------------------ */

    public function view($sale_id = null, $modal = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');
        if ($this->input->get('id')) {
            $sale_id = $this->input->get('id');
        }
        $this->load->helper('pos');
        $this->load->admin_model('settings_model');
        $this->data['error']   = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['message'] = $this->session->flashdata('message');
        $inv                   = $this->pos_model->getInvoiceByID($sale_id);

        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }

        $this->data['rows']            = $this->pos_model->getAllInvoiceItems($sale_id);
        $biller_id                     = $inv->biller_id;
        $customer_id                   = $inv->customer_id;
        $this->data['biller']          = $this->pos_model->getCompanyByID($biller_id);
        $this->data['customer']        = $this->pos_model->getCompanyByID($customer_id);
        $this->data['payments']        = $this->pos_model->getInvoicePayments($sale_id);
        $this->data['pos']             = $this->pos_model->getSetting();
        $this->data['barcode']         = $this->barcode($inv->reference_no, 'code128', 30);
        $this->data['return_sale']     = $inv->return_id ? $this->pos_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows']     = $inv->return_id ? $this->pos_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['return_payments'] = $this->data['return_sale'] ? $this->pos_model->getInvoicePayments($this->data['return_sale']->id) : null;
        $this->data['inv']             = $inv;
        $this->data['sid']             = $sale_id;
        $this->data['modal']           = $modal;
        $this->data['created_by']      = $this->site->getUser($inv->created_by);
        $this->data['printer']         = $this->pos_model->getPrinterByID($this->pos_settings->printer);
        $this->data['page_title']      = $this->lang->line('invoice');
        $this->data['pharmacist_name'] = $this->data['created_by']->first_name.' '.$this->data['created_by']->last_name;
        $this->data['warehouse']       = $this->settings_model->getWarehouseByID($this->data['created_by']->warehouse_id);
        $this->data['pharmacy_name']   = $this->data['warehouse']->name;
        $this->data['pharmacy_address'] = $this->data['warehouse']->address;
        $this->data['printing_date']   = $this->data['inv']->date;
        $instructions                  = json_decode($this->data['inv']->instructions);
        $items_array = array();

        foreach ($instructions as $key => $value) {
            array_push($items_array, $key);
        }
        
        foreach ($this->data['rows'] as $row){
            if(array_search($row->product_name,$items_array) > -1){
                $instructions->{$row->product_name} = $instructions->{$row->product_name}.':'.$row->expiry;
            }
        }
        $this->data['instructions']     = json_encode($instructions);
        $this->load->view($this->theme . 'pos/view', $this->data);
    }

    public function view_bill()
    {
        $this->sma->checkPermissions('index');
        $this->data['tax_rates'] = $this->site->getAllTaxRates();
        $this->load->view($this->theme . 'pos/view_bill', $this->data);
    }

    public function write_payments_config($config)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            admin_redirect('welcome');
        }
        if (DEMO) {
            return true;
        }
        $file_contents = file_get_contents('./assets/config_dumps/payment_gateways.php');
        $output_path   = APPPATH . 'config/payment_gateways.php';
        $this->load->library('parser');
        $parse_data = [
            'APIUsername'            => $config['APIUsername'],
            'APIPassword'            => $config['APIPassword'],
            'APISignature'           => $config['APISignature'],
            'stripe_secret_key'      => $config['stripe_secret_key'],
            'stripe_publishable_key' => $config['stripe_publishable_key'],
            'api_login_id'           => $config['api_login_id'],
            'api_transaction_key'    => $config['api_transaction_key'],
        ];
        $new_config = $this->parser->parse_string($file_contents, $parse_data);

        $handle = fopen($output_path, 'w+');
        @chmod($output_path, 0777);

        if (is_writable($output_path)) {
            if (fwrite($handle, $new_config)) {
                @chmod($output_path, 0644);
                return true;
            }
            @chmod($output_path, 0644);
            return false;
        }
        @chmod($output_path, 0644);
        return false;
    }

    public function sales_date_wise_old()
    {
        $this->sma->checkPermissions('index');
        $at_date = '';
        if ($this->input->post('at_date') == true) {
            echo $at_date = $this->input->post('at_date');
            $this->sma->fld($at_date);
            if ($at_date == '') {
                $this->session->set_flashdata('error', lang('please_select_date'));
                admin_redirect('pos/sales_date_wise');
            }
            $at_date =  $this->sma->fld($at_date);
           
             $this->data['sale_ids'] = $this->pos_model->getSalesByDateRange($at_date);
           
        }

        $sale_id = $this->input->get('sale_id');
        if($sale_id){
            $inv                 = $this->sales_model->getInvoiceByID($sale_id);
            $this->data['inv']         = $inv;
            $this->data['address']     = $this->site->getAddressByID($inv->address_id);
            $this->data['rows']        = $this->sales_model->getAllInvoiceItems($sale_id);
            $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
            $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
            $this->data['sale_id'] = $sale_id;
        }
      
        // $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['at_date'] = $at_date;



        
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('sales_date_wise')]];
        $meta = ['page_title' => lang('sales_date_wise'), 'bc' => $bc];
        $this->page_construct('pos/sales_date_wise', $meta, $this->data);
    }

public function sales_date_wise()
{
    // echo "<pre>User Session";
    // error_reporting(-1);
    // ini_set('display_errors', 1);
    $this->sma->checkPermissions('index');
    $at_date = '';
    $sale_ids = [];
    if ($this->input->post('at_date') == true) {
        $at_date_original = $this->input->post('at_date');
        $sale_id = $this->input->post('sale_id');
        if ($at_date_original == '') {
            $this->session->set_flashdata('error', lang('please_select_date'));
            admin_redirect('pos/sales_date_wise');
        }
        $at_date =  $this->sma->fld($at_date_original);
        
        // Get sale_ids and store in session
        $sale_ids = $this->pos_model->getSalesByDateRange($at_date,$sale_id);
        $this->session->set_userdata('sale_ids', $sale_ids);
        //print_r($sale_ids[0]);
        admin_redirect('pos/sales_date_wise?at_date='.$at_date_original.'&sale_id='.$sale_ids[0]);
        //pos/sales_date_wise?sale_id=83
    }

    $sale_ids = $this->session->userdata('sale_ids');

    if (!$sale_ids) {
        $sale_ids = []; 
    }
    $current_index = array_search($this->input->get('sale_id'), $sale_ids);

    $sale_id = $this->input->get('sale_id');
    if ($sale_id) {
        $inv = $this->sales_model->getInvoiceByID($sale_id);
        $this->data['inv'] = $inv;
        $this->data['address'] = $this->site->getAddressByID($inv->address_id);
        $this->data['rows'] = $this->sales_model->getAllInvoiceItems($sale_id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['sale_id'] = $sale_id;
    }
    $this->data['at_date'] = $this->input->get('at_date');
   

    // Calculate previous and next sale IDs for navigation
    $prev_sale_id = isset($sale_ids[$current_index - 1]) ? $sale_ids[$current_index - 1] : null;
    $next_sale_id = isset($sale_ids[$current_index + 1]) ? $sale_ids[$current_index + 1] : null;
    
    $this->data['prev_sale_id'] = $prev_sale_id;
    $this->data['next_sale_id'] = $next_sale_id;
    $this->data['total_sales'] = count($sale_ids);

    $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('pos'), 'page' => lang('pos')], ['link' => '#', 'page' => lang('sales_date_wise')]];
    $meta = ['page_title' => lang('sales_date_wise'), 'bc' => $bc];
    $this->page_construct('pos/sales_date_wise', $meta, $this->data);
}

}
