<?php

defined('BASEPATH') or exit('No direct script access allowed');
//require_once(APPPATH . 'factories/PdfServiceFactory.php');
class Sales extends MY_Controller
{
    //private $pdfService;
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->load->admin_model('cmt_model');
        $this->load->library('RASDCore',$params=null, 'rasd');
        $this->lang->admin_load('sales', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('sales_model');
        $this->load->admin_model('companies_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '2048';
        $this->data['logo']        = true;
        $this->load->library('attachments', [
            'path'     => $this->digital_upload_path,
            'types'    => $this->digital_file_types,
            'max_size' => $this->allowed_file_size,
        ]);
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
        //$this->pdfService = PdfServiceFactory::create('html2pdf'); 
    }

    /* ------------------------------------------------------------------ */

    public function showUploadSales() {
        $this->sma->checkPermissions();

        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['customers'] = $this->site->getAllCompanies('customer');
        $this->data['billers'] = $this->site->getAllCompanies('biller');

        echo "<script>console.log(" . json_encode($this->data['customers']) . ");</script>";
        echo "<script>console.log(" . json_encode($this->data['billers']) . ");</script>";

        $this->load->view($this->theme . 'sales/uploadCsvSales', $this->data);
    }


    public function mapSales()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        //$this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        //$this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = 'pending';
            $payment_status   = 'pending';
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days')) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = $customer_details->company && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));

            $total            = 0;
            $total_net_sale   = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;

            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('sales/mapSales');
                }
                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $arr_length = count($arrResult);
                if ($arr_length > 499) {
                    $this->session->set_flashdata('error', lang('too_many_products'));
                    redirect($_SERVER['HTTP_REFERER']);
                    exit();
                }
                $titles = array_shift($arrResult);
                $keys = [
                    'code',
                    'avz_code',
                    'cost_price',
                    'purchase_price',
                    'sale_price',
                    'quantity',
                    'batch_no',
                    'expiry',
                    'bonus',
                    'dis1',
                    'dis1_val',
                    'dis2',
                    'dis2_val',
                    'vat',
                    'vat_val',
                    'total_sale',
                    'net_sale',
                    'unit_sale_price'
                ];   
                $final  = [];
                
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
               
                foreach ($final as $csv_pr) {
                    if (isset($csv_pr['code']) && isset($csv_pr['unit_sale_price']) && isset($csv_pr['quantity']) && isset($csv_pr['batch_no'])) {    
                        if ($product_details = $this->sales_model->getProductByCode($csv_pr['code'])) {
                            
                            $item_id        = $product_details->id;
                            $item_type      = $product_details->type;
                            $item_code      = $product_details->code;
                            $item_name      = $product_details->name;

                            $total_sale     = $csv_pr['total_sale'];
                            $cost_price     = $csv_pr['cost_price'];
                            $purchase_price = $csv_pr['purchase_price'];
                            $sale_price     = $csv_pr['sale_price'];
                            $item_net_price = $csv_pr['unit_sale_price'];
                            $item_quantity  = $csv_pr['quantity'];
                            $item_bonus     = $csv_pr['bonus'];
                            $item_tax_rate  = $csv_pr['vat'];
                            $vat_val        = $csv_pr['vat_val'];
                            $item_discount  = $csv_pr['discount'];
                            $item_dis1      = $csv_pr['dis1'];
                            $item_dis1_val  = $csv_pr['dis1_val'];
                            $item_dis2      = $csv_pr['dis2'];
                            $item_dis2_val  = $csv_pr['dis2_val'];
                            
                            $item_batchno   = $csv_pr['batch_no'];  
                            $avz_code       = $csv_pr['avz_code'];
                            $totalbeforevat = $csv_pr['net_sale'];
                            $main_net       = ($csv_pr['net_sale'] + $vat_val);
                           
                            $item_expiry    = isset($csv_pr['expiry']) ? $this->sma->fsd($csv_pr['expiry']) : null;

                            if (isset($item_code) && isset($item_net_price) && isset($item_quantity)) {
                                $product_details  = $this->sales_model->getProductByCode($item_code);
                                
                                $prroduct_item_discount = ($item_dis1_val + $item_dis2_val);
                                $product_discount += $prroduct_item_discount;

                                $tax         = '';
                                
                                $unit_price  = $sale_price;
                                $tax_details = ((isset($item_tax_rate) && !empty($item_tax_rate)) ? $this->sales_model->getTaxRateByName($item_tax_rate) : $this->site->getTaxRateByID($product_details->tax_rate));
                                if ($tax_details) {
                                    $ctax     = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                                    $tax      = $ctax['tax'];
                                }

                                $product_tax += $vat_val;
                                $subtotal = $csv_pr['total_sale'];
                                $unit     = $this->site->getUnitByID($product_details->unit);

                                $product = [
                                    'product_id'        => $product_details->id,
                                    'product_code'      => $item_code,
                                    'product_name'      => $item_name,
                                    'product_type'      => $item_type,
                                    'net_cost'          => $cost_price,
                                    'net_unit_price'    => $item_net_price,
                                    'quantity'          => $item_quantity + $item_bonus,
                                    'product_unit_id'   => $product_details->unit,
                                    'product_unit_code' => $unit->code,
                                    'unit_quantity'     => $item_quantity,
                                    'warehouse_id'      => $warehouse_id,
                                    'item_tax'          => $vat_val,
                                    'tax_rate_id'       => $tax_details ? $tax_details->id : null,
                                    'tax'               => $tax,
                                    'item_discount'     => $item_dis1_val,
                                    'expiry'            => $item_expiry,
                                    'discount1'         => $item_dis1,
                                    'discount2'         => $item_dis2,
                                    'subtotal'          => $subtotal,
                                    'batch_no'          => $item_batchno,
                                    'unit_price'        => $unit_price,
                                    'real_unit_price'   => $unit_price,
                                    'subtotal2'         => $main_net,
                                    'bonus'             => $item_bonus,
                                    'avz_item_code'     => $avz_code ,
                                    'totalbeforevat'    => $totalbeforevat,
                                    'main_net'          => $main_net,
                                    'real_cost'         => $purchase_price,
                                    'second_discount_value' => $item_dis2_val
                                    // 'second_discount_value' => $pr_discount2 * $item_quantity
                                ];

                                $products[] = ($product);
                                $total += $total_sale;
                                $total_net_sale += $totalbeforevat;
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('pr_not_found') . ' ( ' . $csv_pr['code'] . ' ). ' . lang('line_no') . ' ' . $rw);
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        $rw++;
                    }
                }
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $product_discount;
            $order_tax      = $this->input->post('order_tax');
            $total_tax      = $product_tax + $order_tax;
            $grand_total = $total_net_sale + $total_tax + $shipping;
            $data        = ['date'  => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'total_net_sale'    => $total_net_sale,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->sma->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'paid'              => 0,
                'created_by'        => $this->session->userdata('user_id'),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($payment_status == 'paid') {
                $payment = [
                    'date'         => $date,
                    'reference_no' => $this->site->getReference('pay'),
                    'amount'       => $grand_total,
                    'paid_by'      => 'cash',
                    'cheque_no'    => '',
                    'cc_no'        => '',
                    'cc_holder'    => '',
                    'cc_month'     => '',
                    'cc_year'      => '',
                    'cc_type'      => '',
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => lang('auto_added_for_sale_by_csv') . ' (' . lang('sale_reference_no') . ' ' . $reference . ')',
                    'type'         => 'received',
                ];
            } else {
                $payment = [];
            }

            $attachments        = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, [], $attachments)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('sales');
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['slnumber']   = $this->site->getReference('so');

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale_by_csv')]];
            $meta = ['page_title' => lang('add_sale_by_csv'), 'bc' => $bc];
            $this->page_construct('sales/sale_by_csv', $meta, $this->data);
        }
    }


    public function add($quote_id = null)
    {
        $this->sma->checkPermissions();
        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;

        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('quantity[]', lang('quantity'), 'required'); 
        $this->form_validation->set_rules('batchno[]', lang('batchno'), 'required'); 
        
        $product_id_arr= $this->input->post('product_id');  
        foreach ($product_id_arr as $index => $prid) {
            // Set validation rules for each quantity field
            $this->form_validation->set_rules(
                'quantity['.$index.']',
                'Quantity for Product '.$_POST['product_name'][$index],  // Replace with actual product identifier
                'required|greater_than[0]',
                array(
                    'required' => 'Quantity for Product '.$_POST['product_name'][$index].' is required.',
                    'greater_than' => 'Quantity for Product '.$_POST['product_name'][$index].' must be greater than zero.'
                )
            );
        }
 

        if ($this->form_validation->run() == true) {
            // echo "<pre>";
            // print_r($_POST);exit;

            $customerId = $this->input->post('customer');
           //  echo 'valid'; exit;  
            $customer = $this->companies_model->getCompanyByID($customerId);
            $customerCreditLimit = $customer->credit_limit;

            // Check If customer pending sales exceeded the customer credit limit START
            $pendingSales = $this->companies_model->getPendingSalesByCustomer($customerId);
            if(!empty($pendingSales->pendingSalesAmount) && $pendingSales->pendingSalesAmount >= $customerCreditLimit) {
                $this->session->set_flashdata('error', lang('Customer credit limit exceeded. '));
                redirect($_SERVER['HTTP_REFERER']);
            }
            // Check If customer pending sales exceeded the customer credit limit END

            $warning_note = $this->input->post('warning_note') ? $this->input->post('warning_note') : NULL;
            
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));
            $quote_id         = $this->input->post('quote_id') ? $this->input->post('quote_id') : null;

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $digital          = false;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            //echo '<pre>';print_r($_POST);exit;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $avz_code           = $_POST['avz_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                //$real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $real_unit_price = $unit_price;
                $net_cost           = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $real_cost          = $this->sma->formatDecimal($_POST['real_cost'][$r]);

                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';
                
                $item_expiry        = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $item_batchno       = $_POST['batchno'][$r]          ?? '';
                $item_serial_no     = $_POST['serial_no'][$r]        ?? '';
                $item_lotno         = $_POST['lotno'][$r]            ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['quantity'][$r];
                $item_bonus = $_POST['bonus'][$r];
                $item_dis1 = $_POST['dis1'][$r];
                $item_dis2 = $_POST['dis2'][$r];
                $totalbeforevat = $_POST['totalbeforevat'][$r];
                $main_net = $_POST['main_net'][$r];

                //$net_cost_obj = $this->sales_model->getAvgCost($item_batchno, $item_id);
                //$net_cost = $net_cost_obj[0]->cost_price;

                //$net_cost = $this->sales_model->getAvgCost($item_batchno, $item_id);
                //$real_cost = $this->sales_model->getRealAvgCost($item_batchno, $item_id);
               
                
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    if ($item_type == 'digital') {
                        $digital = true;
                    }
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $unit_price       = $this->sma->formatDecimal($unit_price);
                    $product_discount += $pr_item_discount;


                    //Discount calculation---------------------------------- 
                    //The above will be deleted later becasue order discount is not in use                  
                    $pr_discount      = $this->site->calculateDiscount($item_dis1.'%', $real_unit_price);
                    $amount_after_dis1 = $real_unit_price - $pr_discount;
                    $pr_discount2      = $this->site->calculateDiscount($item_dis2.'%', $amount_after_dis1);

                   
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $item_unit_quantity);
                    $prroduct_item_discount = ($pr_item_discount + $pr_item_discount2);
                    $product_discount += $prroduct_item_discount;
                    //Discount calculation----------------------------------

                    // NEW: Net unit price calculation
                    $item_net_price   = $this->sma->formatDecimal((($real_unit_price * ($item_quantity)) - $pr_item_discount - $pr_item_discount2) / ($item_quantity + $item_bonus));
                    
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        //$ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $this->sma->formatDecimal($main_net/$item_unit_quantity, 4));
                        
                        $item_tax    = $this->sma->formatDecimal($ctax['amount'], 4);
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
                    $subtotal = $main_net;//(($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    //$subtotal2 = (($item_net_price * $item_unit_quantity) + $product_tax);// + $pr_item_tax);
                    $subtotal2 = $main_net + $pr_item_tax;
                    $unit     = $this->site->getUnitByID($item_unit);

                    /**
                     * POST FIELDS
                     */
                    $new_item_first_discount = $_POST['item_first_discount'][$r];
                    $new_item_second_discount = $_POST['item_second_discount'][$r];
                    $new_item_vat_value = $_POST['item_vat_values'][$r];
                    $new_item_total_sale = $_POST['item_total_sale'][$r];
                    
                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_cost'          => $net_cost,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $item_net_price,
                        'quantity'          => $item_quantity + $item_bonus,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $new_item_vat_value,
                        'discount'          => $item_discount,
                        'item_discount'     => $new_item_first_discount,
                        'subtotal'          => $new_item_total_sale,
                        'serial_no'         => $item_serial,
                        'serial_number'     => $item_serial_no,
                        'expiry'            => $item_expiry,
                        'batch_no'          => $item_batchno,
                        'lot_no'            => $item_lotno,

                        'real_unit_price'   => $real_unit_price,
                        'subtotal2'         => $this->sma->formatDecimal($subtotal2),
                        'bonus'             => $item_bonus,
                        //'bonus'             => 0,
                        'discount1'         => $item_dis1,
                        'discount2'         => $item_dis2,
                        'second_discount_value' => $new_item_second_discount,
                        'totalbeforevat'    => $totalbeforevat,
                        'main_net'          => $main_net,
                        'real_cost'         => $real_cost,
                        'avz_item_code'     => $avz_code
                    ];
                    
                    $products[] = ($product + $gst_data);
                    //$total += $this->sma->formatDecimal($main_net, 4);//$this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                    // NEW: Grand total calculation
                    $total += $this->sma->formatDecimal($subtotal2, 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), $total, true);//$this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal($order_tax, 4);//$this->sma->formatDecimal(($product_tax + $order_tax), 4);
            //print_r($product_tax);exit;
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            //$grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            
            // NEW: Grand total calculation
            $grand_total = $this->sma->formatDecimal(($total), 4);

             /**
             * post values
             */
            
             $grand_total_net_sale = $this->input->post('grand_total_net_sale');
             $grand_total_discount = $this->input->post('grand_total_discount');
             $grand_total_vat = $this->input->post('grand_total_vat');
             $grand_total_sale = $this->input->post('grand_total_sale');
             $grand_total = $this->input->post('grand_total');

            $data = ['date'  => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'warning_note'      => $warning_note, 
                'total'             => $grand_total_sale,
                'total_net_sale'    => $grand_total_net_sale,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
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
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'paid'              => 0,
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
            ];

            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($payment_status == 'partial' || $payment_status == 'paid') {
                if ($this->input->post('paid_by') == 'deposit') {
                    if (!$this->site->check_customer_deposit($customer_id, $this->input->post('amount-paid'))) {
                        $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                }
                if ($this->input->post('paid_by') == 'gift_card') {
                    $gc            = $this->site->getGiftCardByNO($this->input->post('gift_card_no'));
                    $amount_paying = $grand_total >= $gc->balance ? $gc->balance : $grand_total;
                    $gc_balance    = $gc->balance - $amount_paying;
                    $payment       = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->sma->formatDecimal($amount_paying),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('gift_card_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                        'gc_balance'   => $gc_balance,
                    ];
                } else {
                    $payment = [
                        'date'         => $date,
                        'reference_no' => $this->input->post('payment_reference_no'),
                        'amount'       => $this->sma->formatDecimal($this->input->post('amount-paid')),
                        'paid_by'      => $this->input->post('paid_by'),
                        'cheque_no'    => $this->input->post('cheque_no'),
                        'cc_no'        => $this->input->post('pcc_no'),
                        'cc_holder'    => $this->input->post('pcc_holder'),
                        'cc_month'     => $this->input->post('pcc_month'),
                        'cc_year'      => $this->input->post('pcc_year'),
                        'cc_type'      => $this->input->post('pcc_type'),
                        'created_by'   => $this->session->userdata('user_id'),
                        'note'         => $this->input->post('payment_note'),
                        'type'         => 'received',
                    ];
                }
            } else {
                $payment = [];
            }

            $attachments        = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            //$this->sma->print_arrays($data, $products, $payment, $attachments); exit; 
        }
        
        if ($this->form_validation->run() == true && $sale_id = $this->sales_model->addSale($data, $products, $payment, [], $attachments)) {
            $this->session->set_userdata('remove_slls', 1);
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }
            /**
             * Zatca Integration B2B Start
             */
            if($sale_status  == 'completed'){
                 if($this->zatca_enabled){
                    $zatca_payload =  $this->Zetca_model->get_zetca_data_b2b($sale_id); 
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
                        "sale_id" => $sale_id,
                        "date" => $date,
                        "is_success" => $is_success,
                        "request" => $request,
                        "response" => $response,
                        "remarks" => $remarks
                    ];

                    $this->Zetca_model->report_zatca_status($reporting_data);
                 }
            }

            /**
             * Zatca Integration B2B End
             */
            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('sales?lastInsertedId='.$sale_id);
        } else {
            if ($quote_id || $sale_id) {
                if ($quote_id) {
                    $this->data['quote'] = $this->sales_model->getQuoteByID($quote_id);
                    $items               = $this->sales_model->getAllQuoteItems($quote_id);
                } elseif ($sale_id) {
                    $this->data['quote'] = $this->sales_model->getInvoiceByID($sale_id);
                    $items               = $this->sales_model->getAllInvoiceItems($sale_id);
                }
                krsort($items);
                $c = rand(100000, 9999999);
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    if (!$row) {
                        $row             = json_decode('{}');
                        $row->tax_method = 0;
                    } else {
                        unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                    }
                    $row->quantity = 0;
                    $pis           = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                    if ($pis) {
                        foreach ($pis as $pi) {
                            $row->quantity += $pi->quantity_balance;
                        }
                    }
                    $row->id              = $item->product_id;
                    $row->code            = $item->product_code;
                    $row->name            = $item->product_name;
                    $row->type            = $item->product_type;
                    $row->qty             = $item->quantity;
                    $row->base_quantity   = $item->quantity;
                    $row->base_unit       = $row->unit  ?? $item->product_unit_id;
                    $row->base_unit_price = $row->price ?? $item->unit_price;
                    $row->unit            = $item->product_unit_id;
                    $row->qty             = $item->unit_quantity;
                    $row->discount        = $item->discount ? $item->discount : '0';
                    $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                    $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                    $row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($row->item_discount));
                    $row->unit_price      = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($row->item_discount) + $this->sma->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                    $row->real_unit_price = $item->real_unit_price;
                    $row->tax_rate        = $item->tax_rate_id;
                    $row->serial          = '';
                    $row->expiry          = '';
                    $row->batchNo         = '';
                    $row->serial_number   = '';
                    $row->lotNo          = '';
                    $row->option          = $item->option_id;
                    $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id);
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
                    $combo_items = false;
                    if ($row->type == 'combo') {
                        $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    }
                    $units    = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri       = $this->Settings->item_addition ? $row->id : $c;

                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                        'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                    $c++;
                }
                $this->data['quote_items'] = json_encode($pr);
            }

            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id']   = $quote_id ? $quote_id : $sale_id;
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['units']      = $this->site->getAllBaseUnits();
            //$this->data['currencies'] = $this->sales_model->getAllCurrencies();
            $this->data['slnumber']    = ''; //$this->site->getReference('so');
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale')]];
            $meta                      = ['page_title' => lang('add_sale'), 'bc' => $bc];
            $this->page_construct('sales/add', $meta, $this->data);
        }
    }

    public function add_delivery($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->sale_status != 'completed') {
            $this->session->set_flashdata('error', lang('status_is_x_completed'));
            $this->sma->md();
        }

        if ($delivery = $this->sales_model->getDeliveryBySaleID($id)) {
            $this->edit_delivery($delivery->id);
        } else {
            $this->form_validation->set_rules('sale_reference_no', lang('sale_reference_no'), 'required');
            $this->form_validation->set_rules('customer', lang('customer'), 'required');
            $this->form_validation->set_rules('address', lang('address'), 'required');

            if ($this->form_validation->run() == true) {
                if ($this->Owner || $this->Admin) {
                    $date = $this->sma->fld(trim($this->input->post('date')));
                } else {
                    $date = date('Y-m-d H:i:s');
                }
                $dlDetails = [
                    'date'              => $date,
                    'sale_id'           => $this->input->post('sale_id'),
                    'do_reference_no'   => $this->input->post('do_reference_no') ? $this->input->post('do_reference_no') : $this->site->getReference('do'),
                    'sale_reference_no' => $this->input->post('sale_reference_no'),
                    'customer'          => $this->input->post('customer'),
                    'address'           => $this->input->post('address'),
                    'status'            => $this->input->post('status'),
                    'delivered_by'      => $this->input->post('delivered_by'),
                    'received_by'       => $this->input->post('received_by'),
                    'note'              => $this->sma->clear_tags($this->input->post('note')),
                    'created_by'        => $this->session->userdata('user_id'),
                ];
                if ($_FILES['document']['size'] > 0) {
                    $this->load->library('upload');
                    $config['upload_path']   = $this->digital_upload_path;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size']      = $this->allowed_file_size;
                    $config['overwrite']     = false;
                    $config['encrypt_name']  = true;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    $photo                   = $this->upload->file_name;
                    $dlDetails['attachment'] = $photo;
                }
            } elseif ($this->input->post('add_delivery')) {
                if ($sale->shop) {
                    $this->load->library('sms');
                    $this->sms->delivering($sale->id, $dlDetails['do_reference_no']);
                }
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER['HTTP_REFERER']);
            }

            if ($this->form_validation->run() == true && $this->sales_model->addDelivery($dlDetails)) {
                $this->session->set_flashdata('message', lang('delivery_added'));
                admin_redirect('sales/deliveries');
            } else {
                $this->data['error']           = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['customer']        = $this->site->getCompanyByID($sale->customer_id);
                $this->data['address']         = $this->site->getAddressByID($sale->address_id);
                $this->data['inv']             = $sale;
                $this->data['do_reference_no'] = ''; //$this->site->getReference('do');
                $this->data['modal_js']        = $this->site->modal_js();

                $this->load->view($this->theme . 'sales/add_delivery', $this->data);
            }
        }
    }

    public function add_gift_card()
    {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang('card_no'), 'trim|is_unique[gift_cards.card_no]|required');
        $this->form_validation->set_rules('value', lang('value'), 'required');

        if ($this->form_validation->run() == true) {
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer         = $customer_details ? $customer_details->company : null;
            $data             = ['card_no' => $this->input->post('card_no'),
                'value'                    => $this->input->post('value'),
                'customer_id'              => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer'                 => $customer,
                'balance'                  => $this->input->post('value'),
                'expiry'                   => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
                'created_by'               => $this->session->userdata('user_id'),
            ];
            $sa_data = [];
            $ca_data = [];
            if ($this->input->post('staff_points')) {
                $sa_points = $this->input->post('sa_points');
                $user      = $this->site->getUser($this->input->post('user'));
                if ($user->award_points < $sa_points) {
                    $this->session->set_flashdata('error', lang('award_points_wrong'));
                    admin_redirect('sales/gift_cards');
                }
                $sa_data = ['user' => $user->id, 'points' => ($user->award_points - $sa_points)];
            } elseif ($customer_details && $this->input->post('use_points')) {
                $ca_points = $this->input->post('ca_points');
                if ($customer_details->award_points < $ca_points) {
                    $this->session->set_flashdata('error', lang('award_points_wrong'));
                    admin_redirect('sales/gift_cards');
                }
                $ca_data = ['customer' => $this->input->post('customer'), 'points' => ($customer_details->award_points - $ca_points)];
            }
            // $this->sma->print_arrays($data, $ca_data, $sa_data);
        } elseif ($this->input->post('add_gift_card')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('sales/gift_cards');
        }

        if ($this->form_validation->run() == true && $this->sales_model->addGiftCard($data, $ca_data, $sa_data)) {
            $this->session->set_flashdata('message', lang('gift_card_added'));
            admin_redirect('sales/gift_cards');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['users']      = $this->sales_model->getStaff();
            $this->data['page_title'] = lang('new_gift_card');
            $this->load->view($this->theme . 'sales/add_gift_card', $this->data);
        }
    }

    public function convert_customer_payment_invoice($sid, $amount){
        $inv = $this->sales_model->getSaleByID($sid);
        $this->load->admin_model('companies_model');
        $customer = $this->companies_model->getCompanyByID($inv->customer_id);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => 'customerpayment',
            'number'       => 'SO-'.$inv->reference_no,
            'date'         => date('Y-m-d'), 
            'dr_total'     => $amount,
            'cr_total'     => $amount,
            'notes'        => 'Sale Reference: '.$inv->reference_no.' Date: '.date('Y-m-d H:i:s'),
            'sid'          =>  $inv->id,
            'customer_id'  => $inv->customer_id
            );
    
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        $entryitemdata = array();

        //bank fund cash
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $this->bank_checking_account,
                'amount' => $amount,
                'narration' => ''
            )
        );

        //customer
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $customer->ledger_account,
                //'amount' => $inv->order_tax,
                'amount' => $amount,
                'narration' => ''
            )
        );

        foreach ($entryitemdata as $row => $itemdata)
        {
                $this->db->insert('sma_accounts_entryitems' ,$itemdata['Entryitem']);
        }

    }

    public function add_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->payment_status == 'paid' && $sale->grand_total == $sale->paid) {
            $this->session->set_flashdata('error', lang('sale_already_paid'));
            $this->sma->md();
        }

        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            $sale = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
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
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay'),
                'amount'       => $this->input->post('amount-paid'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
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

        if ($this->form_validation->run() == true && $this->sales_model->addPayment($payment, $customer_id)) {

            $this->convert_customer_payment_invoice($this->input->post('sale_id'), $this->input->post('amount-paid'));

            if ($sale->shop) {
                $this->load->library('sms');
                $this->sms->paymentReceived($sale->id, $payment['reference_no'], $payment['amount']);
            }
            $this->session->set_flashdata('message', lang('payment_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            if ($sale->sale_status == 'returned' && $sale->paid == $sale->grand_total) {
                $this->session->set_flashdata('warning', lang('payment_was_returned'));
                $this->sma->md();
            }
            $this->data['inv']         = $sale;
            $this->data['payment_ref'] = ''; //$this->site->getReference('pay');
            $this->data['modal_js']    = $this->site->modal_js();

            $this->load->view($this->theme . 'sales/add_payment', $this->data);
        }
    }

    public function combine_pdf($sales_id)
    {
        $this->sma->checkPermissions('pdf');

        foreach ($sales_id as $id) {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv                 = $this->sales_model->getInvoiceByID($id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
            $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
            $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
            $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
            $this->data['user']        = $this->site->getUser($inv->created_by);
            $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['inv']         = $inv;
            $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
            $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
            $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
            $html_data                 = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $html_data = preg_replace("'\<\?xml(.*)\?\>'", '', $html_data);
            }

            $html[] = [
                'content' => $html_data,
                'footer'  => $this->data['biller']->invoice_footer,
            ];
        }

        $name = lang('sales') . '.pdf';
        $this->sma->generate_pdf($html, $name);
    }

    /* ------------------------------- */

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        $inv = $this->sales_model->getInvoiceByID($id);
        if ($inv->sale_status == 'returned') {
            $this->sma->send_json(['error' => 1, 'msg' => lang('sale_x_action')]);
        }

        if ($this->sales_model->deleteSale($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 0, 'msg' => lang('sale_deleted')]);
            }
            $this->session->set_flashdata('message', lang('sale_deleted'));
            admin_redirect('welcome');
        }else{
            $this->sma->send_json(['error' => 1, 'msg' => 'Cannot delete this sale']);
        }
    }

    public function delete_delivery($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->sales_model->deleteDelivery($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('delivery_deleted')]);
        }
    }

    public function delete_gift_card($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->sales_model->deleteGiftCard($id)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('gift_card_deleted')]);
        }
    }

    public function delete_payment($id = null)
    {
        $this->sma->checkPermissions('delete');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->sales_model->deletePayment($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang('payment_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_return($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->sales_model->deleteReturn($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 0, 'msg' => lang('return_sale_deleted')]);
            }
            $this->session->set_flashdata('message', lang('return_sale_deleted'));
            admin_redirect('welcome');
        }
    }

    /* ------------------------------- */

    public function deliveries()
    {
        $this->sma->checkPermissions();

        $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $bc            = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('deliveries')]];
        $meta          = ['page_title' => lang('deliveries'), 'bc' => $bc];
        $this->page_construct('sales/deliveries', $meta, $this->data);
    }

    public function delivery_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete_delivery');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteDelivery($id);
                    }
                    $this->session->set_flashdata('message', lang('deliveries_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('deliveries'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('do_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('address'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $delivery = $this->sales_model->getDeliveryByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($delivery->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $delivery->do_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $delivery->sale_reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $delivery->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $delivery->address);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($delivery->status));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);

                    $filename = 'deliveries_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_delivery_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    /* ------------------------------------------------------------------------ */

    public function edit($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $inv = $this->sales_model->getInvoiceByID($id);
        
        if($inv->sale_status == 'completed'){
            $this->session->set_flashdata('error', 'Cannot edit completed sales');

            admin_redirect('sales');
        }
        if ($inv->sale_status == 'returned' || $inv->return_id || $inv->return_sale_ref) {
            $this->session->set_flashdata('error', lang('sale_x_action'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }

        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        //$this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');
        $this->form_validation->set_rules('batchno[]', lang('Batch Number'), 'required');
        $product_id_arr= $this->input->post('product_id');  
        foreach ($product_id_arr as $index => $prid) {
            // Set validation rules for each quantity field
            $this->form_validation->set_rules(
                'quantity['.$index.']',
                'Quantity for Product '.$_POST['product_name'][$index],  // Replace with actual product identifier
                'required|greater_than[0]',
                array(
                    'required' => 'Quantity for Product '.$_POST['product_name'][$index].' is required.',
                    'greater_than' => 'Quantity for Product '.$_POST['product_name'][$index].' must be greater than zero.'
                )
            );
        }
        if ($this->form_validation->run() == true) {
            // echo "<pre>";
            // print_r($_POST);exit;
            //$reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));
            $warning_note     = $this->sma->clear_tags($this->input->post('warning_note'));
            

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $total_product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;

            //echo '<pre>';print_r($_POST);exit;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_avz_code      = $_POST['avz_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                //$net_cost           = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $real_unit_price    = $unit_price ; //$this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';

                $item_expiry        = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $item_batchno       = $_POST['batchno'][$r]          ?? '';
                $item_serial_no     = $_POST['serial_no'][$r]        ?? '';
                $item_lotno         = $_POST['lotno'][$r]            ?? '';

                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['quantity'][$r];
                $net_cost           = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $real_cost          = $this->sma->formatDecimal($_POST['real_cost'][$r]);


                $item_bonus = $_POST['bonus'][$r];
                $item_dis1 = $_POST['dis1'][$r];
                $item_dis2 = $_POST['dis2'][$r];
                $totalbeforevat = $_POST['totalbeforevat'][$r];
                $main_net = $_POST['main_net'][$r];
                
                
                $data2['OperationType'] = 'DRUG SALE';
                $data2['TransactionNumber']  = '';
                $data2['FromID'] =  $id;
                $data2['ToID'] = 0; 

                $data2['GTIN']  = $item_code;
                $data2['BatchNumber'] =  $item_batchno;
                $data2['ExpiryDate'] = $item_expiry; 
                $data2['SerialNo'] = $item_serial;      
                if($sale_status == "completed"){
                    for ($k = 0; $k < $item_unit_quantity; $k++) {
 
                        $this->db->insert('sma_rsd' ,$data2);
                        
                     }

                }

                //$net_cost_obj = $this->sales_model->getAverageCost($item_batchno, $item_code);
                //$net_cost = $net_cost_obj[0]->cost_price;

                //$net_cost = $this->sales_model->getAvgCost($item_batchno, $item_id);
                //$real_cost = $this->sales_model->getRealAvgCost($item_batchno, $item_id);

               
                if (isset($item_code) && isset($unit_price) && isset($item_quantity)) {
                   
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;

                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $unit_price       = $this->sma->formatDecimal($unit_price);
                    $item_net_price   = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $product_discount += $pr_item_discount;

                    //Discount calculation---------------------------------- 
                    //The above will be deleted later becasue order discount is not in use                  
                    $product_discount1      = $this->site->calculateDiscount($item_dis1.'%', $unit_price);
                    $amount_after_discount1 = $unit_price - $product_discount1;
                    $product_discount2      = $this->site->calculateDiscount($item_dis2.'%', $amount_after_discount1);

                   
                    $product_item_discount1 = $this->sma->formatDecimal($product_discount1 * $item_unit_quantity);
                    $product_item_discount2 = $this->sma->formatDecimal($product_discount2 * $item_unit_quantity);
                    
                    $product_item_discount = ($product_item_discount1 + $product_item_discount2);
                    $total_product_discount += $product_item_discount;

                    //Discount calculation---------------------------------- 
                    //The above will be deleted later becasue order discount is not in use                  
                    $pr_discount      = $this->site->calculateDiscount($item_dis1.'%', $real_unit_price);
                    $amount_after_dis1 = $real_unit_price - $pr_discount;
                    $pr_discount2      = $this->site->calculateDiscount($item_dis2.'%', $amount_after_dis1);

                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $item_unit_quantity);
                    $prroduct_item_discount = ($pr_item_discount + $pr_item_discount2);
                    $product_discount += $prroduct_item_discount;
                    //Discount calculation----------------------------------

                    // NEW: Net unit price calculation
                    $item_net_price   = $this->sma->formatDecimal((($real_unit_price * $item_quantity) - $pr_item_discount - $pr_item_discount2) / ($item_quantity + $item_bonus));
                    
                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        //$ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $this->sma->formatDecimal($main_net/$item_unit_quantity, 4));
                        $item_tax    = $this->sma->formatDecimal($ctax['amount'], 4);
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
                    $subtotal = $main_net;//(($item_net_price * $item_unit_quantity) + $pr_item_tax);
                    //$subtotal2 = (($item_net_price * $item_unit_quantity));

                    $subtotal2 = ($main_net + $product_tax);

                    $unit     = $this->site->getUnitByID($item_unit);

                     /**
                     * POST FIELDS
                     */
                    $new_item_first_discount = $_POST['item_first_discount'][$r];
                    $new_item_second_discount = $_POST['item_second_discount'][$r];
                    $new_item_vat_value = $_POST['item_vat_values'][$r];
                    $new_item_total_sale = $_POST['item_total_sale'][$r];
                    $item_net_unit_sale = $_POST['item_unit_sale'][$r];
                    $item_unit_sale = $_POST['unit_price'][$r];

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_cost'          => $net_cost,
                        'net_unit_price'    => $item_net_unit_sale,
                        'unit_price'        => $item_unit_sale,
                        'quantity'          => $item_quantity + $item_bonus,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $new_item_vat_value,
                        'discount'          => $item_discount,
                        'item_discount'     => $new_item_first_discount,
                        'second_discount_value' => $new_item_second_discount,
                        'subtotal'          => $new_item_total_sale,
                        'serial_no'         => $item_serial,
                        'expiry'            => $item_expiry,
                        'batch_no'          => $item_batchno,
                        'serial_number'     => $item_serial_no,
                        'lot_no'            => $item_lotno,
                        'real_unit_price'   => $real_unit_price,
                        'subtotal2'         => $this->sma->formatDecimal($subtotal2),
                        'bonus'           => $item_bonus,
                        //'bonus'             => 0,
                        'discount1'         => $item_dis1,
                        'discount2'         => $item_dis2,
                        'totalbeforevat'    => $totalbeforevat,
                        'main_net'          => $main_net,
                        'avz_item_code'     => $item_avz_code,
                        'real_cost'         => $real_cost
                    ];

                    $products[] = ($product + $gst_data);
                    //$total += $this->sma->formatDecimal($main_net, 4);//$this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                    $total += $this->sma->formatDecimal($subtotal2, 4);
                }
            }
            // echo "products<pre>";
            // print_r($products);
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'),$total , true);//$this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            //$grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            
            // NEW: Grand total calculation
            $grand_total = $this->sma->formatDecimal(($total), 4);

              /**
             * post values
             */
        
            $grand_total_net_sale = $this->input->post('grand_total_net_sale');
            $grand_total_discount = $this->input->post('grand_total_discount');
            $grand_total_vat = $this->input->post('grand_total_vat');
            $grand_total_sale = $this->input->post('grand_total_sale');
            $grand_total = $this->input->post('grand_total');
            $cost_goods_sold = $this->input->post('cost_goods_sold');
            
            $data        = ['date'  => $date,
                //'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'warning_note'      => $warning_note,
                'total'             => $grand_total_sale,
                'total_net_sale'    => $grand_total_net_sale,
                'product_discount'  => $total_product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $grand_total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $grand_total_vat,
                'shipping'          => $this->sma->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'cost_goods_sold'   => $cost_goods_sold,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'updated_by'        => $this->session->userdata('user_id'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            if($customer_details->balance > 0 && $sale_status == 'completed'){
                if($customer_details->balance >= $grand_total){
                    $paid = $grand_total;
                    $new_balance = $customer_details->balance - $grand_total;
                    $payment_status = 'paid';
                }else{
                    $paid = $grand_total - $customer_details->balance;
                    $new_balance = 0;
                    $payment_status = 'partial';
                }

                $data['paid'] = $paid;
                $data['payment_status'] = $payment_status;
            }

            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            $attachments        = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            //$this->sma->print_arrays($data, $products);exit;
        }
        // echo "<pre>";
        // print_r($_POST);
        //  $this->sma->print_arrays($data, $products);exit;

        if ($this->form_validation->run() == true && $this->sales_model->updateSale($id, $data, $products, $attachments)) {
            if($sale_status == 'completed'){
                $this->convert_sale_invoice($id);
                /**
                 * Zatca Integration B2B Start
                 */
                if($this->zatca_enabled){
                    $zatca_payload =  $this->Zetca_model->get_zetca_data_b2b($id); 
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
                        "sale_id" => $id,
                        "date" => $date,
                        "is_success" => $is_success,
                        "request" => $request,
                        "response" => $response,
                        "remarks" => $remarks
                    ];

                    $this->Zetca_model->report_zatca_status($reporting_data);
                }

                if($customer_details->balance > 0){
                    $this->sales_model->update_balance($customer_details->id, $new_balance);
                }

                /**RASD Integration Code */
                $data_for_rasd = [
                    "products" => $products,
                    "source_warehouse_id" => $data['warehouse_id'],
                    "destination_customer_id" => $data['customer_id'],
                    "sale_id" => $id
                ];
                $response_model = $this->sales_model->get_rasd_required_fields($data_for_rasd);
                $body_for_rasd_dispatch = $response_model['payload'];

                //$payload_for_accept_dispatch = $response_model['payload_for_accept_dispatch'];
                //log_message("info", json_encode($payload_for_accept_dispatch, true));

                $rasd_user = $response_model['user'];
                $rasd_pass = $response_model['pass'];
                $resp_sale_status = $response_model['status'];
                //$ph_user = $response_model['pharmacy_user'];
                //$ph_pass = $response_model['pharmacy_pass'];
                //$map_update = $response_model['update_map_table'];
                $rasd_success = false;
                log_message("info", json_encode($body_for_rasd_dispatch));
                $payload_used =  [
                        'source_gln' => $response_model['source_gln'],
                        'destination_gln' => $response_model['destination_gln'],
                        'warehouse_id' => $data['warehouse_id']
                    ];  
                    
                if($resp_sale_status == 'completed'){
                    foreach($body_for_rasd_dispatch as $index => $payload_dispatch){
                        log_message("info", "RASD AUTH START");
                        $this->rasd->set_base_url('https://qdttsbe.qtzit.com:10101/api/web');
                        $auth_response = $this->rasd->authenticate($rasd_user, $rasd_pass);
                        if(isset($auth_response['token'])){
                            $auth_token = $auth_response['token'];
                            log_message("info", 'RASD Authentication Success: DISPATCH_PRODUCT');
                            $zadca_dispatch_response = $this->rasd->dispatch_product_133($payload_dispatch, $auth_token);
                            
                            
                            if(isset($zadca_dispatch_response['body']['DicOfDic']['MR']['TRID']) && $zadca_dispatch_response['body']['DicOfDic']['MR']['ResCodeDesc'] != "Failed"){                
                                log_message("info", "Dispatch successful");
                                $rasd_success = true;                

                                $this->cmt_model->add_rasd_transactions($payload_used,'sale_dispatch_product',true, $zadca_dispatch_response,$payload_dispatch);
                            
                            }else{
                                $rasd_success = false;
                                log_message("error", "Dispatch Failed");
                                log_message("error", json_encode($zadca_dispatch_response,true));
                                $this->cmt_model->add_rasd_transactions($payload_used,'sale_dispatch_product',false, $zadca_dispatch_response,$payload_dispatch);
                            }
                            
                        }else{
                            log_message("error", 'RASD Authentication FAILED: DISPATCH_PRODUCT');
                            $this->cmt_model->add_rasd_transactions($payload_used,'sale_dispatch_product',false, $accept_dispatch_result,$body_for_rasd_dispatch);
                        }
                    }
                }
            }

            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_updated'));
            admin_redirect($inv->pos ? 'pos/sales' : 'sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $this->sales_model->getInvoiceByID($id);
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang('sale_x_edited_older_than_x_days'), $this->Settings->disable_editing));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $inv_items = $this->sales_model->getAllInvoiceItems($id);

            
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                // $row = $this->site->getProductByID($item->product_id);
                $row = $this->sales_model->getWarehouseProduct($item->product_id, $item->warehouse_id);
                if (!$row) {
                    $row             = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity   = 0;
                } else {
                    unset($row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                //echo '<pre>';print_r($item);exit;
                $row->id              = $item->product_id;
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->base_quantity   = $item->quantity;
                $row->base_unit       = !empty($row->unit) ? $row->unit : $item->product_unit_id;
                $row->base_unit_price = !empty($row->price) ? $row->price : $item->unit_price;
                $row->cost            = $item->net_cost;
                $row->unit            = $item->product_unit_id;
                $row->qty             = $item->unit_quantity;
                $row->quantity += $item->quantity;
                $row->discount        = $item->discount ? $item->discount : '0';
                $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                $row->avz_item_code   = $item->avz_item_code; 
                $row->net_unit_cost   = $item->net_cost;
                $row->real_unit_cost  = $item->real_cost;

                //Discount calculation----------------------------------
                // this row is deleted becasue of discount must not be added in sale price 
                //$row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($row->item_discount));
                
                $row->price           = $this->sma->formatDecimal($item->real_unit_price);
                //Discount calculation----------------------------------

                $row->net_unit_sale = $this->sma->formatDecimal($item->real_unit_price);

                $row->unit_price      = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($row->item_discount) + $this->sma->formatDecimal($row->item_tax) : $item->unit_price + ($row->item_discount);
                $row->real_unit_price = $item->real_unit_price;
                $row->tax_rate        = $item->tax_rate_id;
                $row->serial          = $item->serial_no;
                
                $row->expiry          = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->batch_no        = $item->batch_no;
                $row->serial_number   = $item->serial_number;
                $row->lot_no          = $item->lot_no;
                
                $row->option          = $item->option_id;
                $row->bonus            = $item->bonus;
                $row->dis1             = $item->discount1;
                $row->dis2            = $item->discount2;
                $row->totalbeforevat   = $item->totalbeforevat;
                $row->main_net            = $item->main_net;
                $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id, true);
                if ($options) {
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $item->warehouse_id, $item->option_id);
                        if ($pis) {
                            $option->quantity = 0;
                            foreach ($pis as $pi) {
                                $option->quantity += $pi->quantity_balance;
                            }
                        }
                        if ($row->option == $option->id) {
                            $option->quantity += $item->quantity;
                        }
                    }
                }

                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $item->warehouse_id);
                    $te          = $combo_items;
                    foreach ($combo_items as $combo_item) {
                        $combo_item->quantity = $combo_item->qty * $item->quantity;
                    }
                }
                $units    = !empty($row->base_unit) ? $this->site->getUnitsByBUID($row->base_unit) : null;
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri       = $this->Settings->item_addition ? $row->id : $c;

                $batches = $this->site->getProductBatchesData($row->id, $item->warehouse_id);

                $row->batchPurchaseCost = $row->cost; 
                $row->batchQuantity = 0;               
                if ($batches) {
                    foreach ($batches as $batchesR) {
                        if($batchesR->batchno == $row->batch_no){
                            $row->batchQuantity = $batchesR->quantity;
                            break;
                        }
                    }
                }

                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'        => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'batches'=>$batches];
                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id']        = $id;
            //$this->data['currencies'] = $this->site->getAllCurrencies();
            $this->data['billers']    = ($this->Owner || $this->Admin || !$this->session->userdata('biller_id')) ? $this->site->getAllCompanies('biller') : null;
            $this->data['units']      = $this->site->getAllBaseUnits();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('edit_sale')]];
            $meta = ['page_title' => lang('edit_sale'), 'bc' => $bc];
            $this->page_construct('sales/edit', $meta, $this->data);
        }
    }

    public function edit_delivery($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('do_reference_no', lang('do_reference_no'), 'required');
        $this->form_validation->set_rules('sale_reference_no', lang('sale_reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('address', lang('address'), 'required');

        if ($this->form_validation->run() == true) {
            $dlDetails = [
                'sale_id'           => $this->input->post('sale_id'),
                'do_reference_no'   => $this->input->post('do_reference_no'),
                'sale_reference_no' => $this->input->post('sale_reference_no'),
                'customer'          => $this->input->post('customer'),
                'address'           => $this->input->post('address'),
                'status'            => $this->input->post('status'),
                'delivered_by'      => $this->input->post('delivered_by'),
                'received_by'       => $this->input->post('received_by'),
                'note'              => $this->sma->clear_tags($this->input->post('note')),
                'created_by'        => $this->session->userdata('user_id'),
            ];

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = false;
                $config['encrypt_name']  = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo                   = $this->upload->file_name;
                $dlDetails['attachment'] = $photo;
            }

            if ($this->Owner || $this->Admin) {
                $date              = $this->sma->fld(trim($this->input->post('date')));
                $dlDetails['date'] = $date;
            }
        } elseif ($this->input->post('edit_delivery')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateDelivery($id, $dlDetails)) {
            $this->session->set_flashdata('message', lang('delivery_updated'));
            admin_redirect('sales/deliveries');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['delivery'] = $this->sales_model->getDeliveryByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_delivery', $this->data);
        }
    }

    public function edit_gift_card($id = null)
    {
        $this->sma->checkPermissions(false, true);

        $this->form_validation->set_rules('card_no', lang('card_no'), 'trim|required');
        $gc_details = $this->site->getGiftCardByID($id);
        if ($this->input->post('card_no') != $gc_details->card_no) {
            $this->form_validation->set_rules('card_no', lang('card_no'), 'is_unique[gift_cards.card_no]');
        }
        $this->form_validation->set_rules('value', lang('value'), 'required');
        //$this->form_validation->set_rules('customer', lang("customer"), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $gift_card        = $this->site->getGiftCardByID($id);
            $customer_details = $this->input->post('customer') ? $this->site->getCompanyByID($this->input->post('customer')) : null;
            $customer         = $customer_details ? $customer_details->company : null;
            $data             = ['card_no' => $this->input->post('card_no'),
                'value'                    => $this->input->post('value'),
                'customer_id'              => $this->input->post('customer') ? $this->input->post('customer') : null,
                'customer'                 => $customer,
                'balance'                  => ($this->input->post('value') - $gift_card->value) + $gift_card->balance,
                'expiry'                   => $this->input->post('expiry') ? $this->sma->fsd($this->input->post('expiry')) : null,
            ];
        } elseif ($this->input->post('edit_gift_card')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('sales/gift_cards');
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateGiftCard($id, $data)) {
            $this->session->set_flashdata('message', lang('gift_card_updated'));
            admin_redirect('sales/gift_cards');
        } else {
            $this->data['error']     = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['gift_card'] = $this->site->getGiftCardByID($id);
            $this->data['id']        = $id;
            $this->data['modal_js']  = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_gift_card', $this->data);
        }
    }

    public function edit_payment($id = null)
    {
        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $payment = $this->sales_model->getPaymentByID($id);
        if ($payment->paid_by == 'ppp' || $payment->paid_by == 'stripe' || $payment->paid_by == 'paypal' || $payment->paid_by == 'skrill') {
            $this->session->set_flashdata('error', lang('x_edit_payment'));
            $this->sma->md();
        }
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->input->post('paid_by') == 'deposit') {
                $sale        = $this->sales_model->getInvoiceByID($this->input->post('sale_id'));
                $customer_id = $sale->customer_id;
                $amount      = $this->input->post('amount-paid') - $payment->amount;
                if (!$this->site->check_customer_deposit($customer_id, $amount)) {
                    $this->session->set_flashdata('error', lang('amount_greater_than_deposit'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            } else {
                $customer_id = null;
            }
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $payment->date;
            }
            $payment = [
                'date'         => $date,
                'sale_id'      => $this->input->post('sale_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount'       => $this->input->post('amount-paid'),
                'paid_by'      => $this->input->post('paid_by'),
                'cheque_no'    => $this->input->post('cheque_no'),
                'cc_no'        => $this->input->post('pcc_no'),
                'cc_holder'    => $this->input->post('pcc_holder'),
                'cc_month'     => $this->input->post('pcc_month'),
                'cc_year'      => $this->input->post('pcc_year'),
                'cc_type'      => $this->input->post('pcc_type'),
                'note'         => $this->input->post('note'),
                'created_by'   => $this->session->userdata('user_id'),
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
        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->sales_model->updatePayment($id, $payment, $customer_id)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            admin_redirect('sales');
        } else {
            $this->data['error']    = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['payment']  = $payment;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/edit_payment', $this->data);
        }
    }

    public function email($id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->sales_model->getInvoiceByID($id);
        $this->form_validation->set_rules('to', lang('to') . ' ' . lang('email'), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang('subject'), 'trim|required');
        $this->form_validation->set_rules('cc', lang('cc'), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang('bcc'), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang('message'), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $to      = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller   = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $inv->reference_no,
                'contact_person'   => $customer->name,
                'company'          => $customer->company && $customer->company != '-' ? '(' . $customer->company . ')' : '',
                'order_link'       => $inv->shop ? shop_url('orders/' . $inv->id . '/' . ($this->loggedIn ? '' : $inv->hash)) : base_url(),
                'site_link'        => base_url(),
                'site_name'        => $this->Settings->site_name,
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            ];
            $msg      = $this->input->post('note');
            $message  = $this->parser->parse_string($msg, $parse_data);
            $paypal   = $this->sales_model->getPaypalSettings();
            $skrill   = $this->sales_model->getSkrillSettings();
            $btn_code = '<div id="payment_buttons" class="text-center margin010">';
            if ($paypal->active == '1' && $inv->grand_total != '0.00') {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
                } else {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
                }
                $btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->default_currency->code . '&bn=FC-BuyNow&rm=2&return=' . admin_url('sales/view/' . $inv->id) . '&cancel_return=' . admin_url('sales/view/' . $inv->id) . '&notify_url=' . admin_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('assets/images/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';
            }
            if ($skrill->active == '1' && $inv->grand_total != '0.00') {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_my / 100);
                } else {
                    $skrill_fee = $skrill->fixed_charges + ($inv->grand_total * $skrill->extra_charges_other / 100);
                }
                $btn_code .= ' <a href="https://www.moneybookers.com/app/payment.pl?method=get&pay_to_email=' . $skrill->account_email . '&language=EN&merchant_fields=item_name,item_number&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&logo_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $skrill_fee) . '&return_url=' . admin_url('sales/view/' . $inv->id) . '&cancel_url=' . admin_url('sales/view/' . $inv->id) . '&detail1_description=' . $inv->reference_no . '&detail1_text=Payment for the sale invoice ' . $inv->reference_no . ': ' . $inv->grand_total . '(+ fee: ' . $skrill_fee . ') = ' . $this->sma->formatMoney($inv->grand_total + $skrill_fee) . '&currency=' . $this->default_currency->code . '&status_url=' . admin_url('payments/skrillipn') . '"><img src="' . base_url('assets/images/btn-skrill.png') . '" alt="Pay by Skrill"></a>';
            }

            $btn_code .= '<div class="clearfix"></div></div>';
            $message    = $message . $btn_code;
            $attachment = $this->pdf($id, null, 'S');

            try {
                if ($this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $this->session->set_flashdata('message', lang('email_sent'));
                    admin_redirect('sales');
                }
            } catch (Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            if (file_exists('./themes/' . $this->Settings->theme . '/admin/views/email_templates/sale.html')) {
                $sale_temp = file_get_contents('themes/' . $this->Settings->theme . '/admin/views/email_templates/sale.html');
            } else {
                $sale_temp = file_get_contents('./themes/default/admin/views/email_templates/sale.html');
            }

            $this->data['subject'] = ['name' => 'subject',
                'id'                         => 'subject',
                'type'                       => 'text',
                'value'                      => $this->form_validation->set_value('subject', lang('invoice') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            ];
            $this->data['note'] = ['name' => 'note',
                'id'                      => 'note',
                'type'                    => 'text',
                'value'                   => $this->form_validation->set_value('note', $sale_temp),
            ];
            $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);

            $this->data['id']       = $id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/email', $this->data);
        }
    }

    public function email_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment              = $this->sales_model->getPaymentByID($id);
        $inv                  = $this->sales_model->getInvoiceByID($payment->sale_id);
        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $customer             = $this->site->getCompanyByID($inv->customer_id);
        if (!$customer->email) {
            $this->sma->send_json(['msg' => lang('update_customer_email')]);
        }
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['customer']   = $customer;
        $this->data['page_title'] = lang('payment_note');
        $html                     = $this->load->view($this->theme . 'sales/payment_note', $this->data, true);

        $html = str_replace(['<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>' . lang('stamp_sign') . '</p>'], '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);
        // $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">'.$html.'</div>';

        $this->load->library('parser');
        $parse_data = [
            'stylesheet' => '<link href="' . $this->data['assets'] . 'styles/helpers/bootstrap.min.css" rel="stylesheet"/>',
            'name'       => $customer->company && $customer->company != '-' ? $customer->company : $customer->name,
            'email'      => $customer->email,
            'heading'    => lang('payment_note') . '<hr>',
            'msg'        => $html,
            'site_link'  => base_url(),
            'site_name'  => $this->Settings->site_name,
            'logo'       => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>',
        ];
        $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->Settings->site_name;

        if ($this->sma->send_email($customer->email, $subject, $message)) {
            $this->sma->send_json(['msg' => lang('email_sent')]);
        } else {
            $this->sma->send_json(['msg' => lang('email_failed')]);
        }
    }

    public function get_award_points($id = null)
    {
        $this->sma->checkPermissions('index');

        $row = $this->site->getUser($id);
        $this->sma->send_json(['sa_points' => $row->award_points]);
    }

    public function getDeliveries()
    {
        $this->sma->checkPermissions('deliveries');

        $detail_link = anchor('admin/sales/view_delivery/$1', '<i class="fa fa-file-text-o"></i> ' . lang('delivery_details'), 'data-toggle="modal" data-target="#myModal"');
        $email_link  = anchor('admin/sales/email_delivery/$1', '<i class="fa fa-envelope"></i> ' . lang('email_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link   = anchor('admin/sales/edit_delivery/$1', '<i class="fa fa-edit"></i> ' . lang('edit_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $pdf_link    = anchor('admin/sales/pdf_delivery/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link = "<a href='#' class='po' title='<b>" . lang('delete_delivery') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete_delivery/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_delivery') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
    <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $detail_link . '</li>
        <li>' . $edit_link . '</li>
        <li>' . $pdf_link . '</li>
        <li>' . $delete_link . '</li>
    </ul>
</div></div>';

        $this->load->library('datatables');
        //GROUP_CONCAT(CONCAT('Name: ', sale_items.product_name, ' Qty: ', sale_items.quantity ) SEPARATOR '<br>')
        $this->datatables
            ->select('deliveries.id as id, date, do_reference_no, sale_reference_no, customer, address, status, attachment')
            ->from('deliveries')
            ->join('sale_items', 'sale_items.sale_id=deliveries.sale_id', 'left')
            ->group_by('deliveries.id');
        $this->datatables->add_column('Actions', $action, 'id');

        echo $this->datatables->generate();
    }

    public function getGiftCards()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('gift_cards') . '.id as id, card_no, value, balance, CONCAT(' . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . '.last_name) as created_by, customer, expiry', false)
            ->join('users', 'users.id=gift_cards.created_by', 'left')
            ->from('gift_cards')
            ->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('sales/view_gift_card/$1') . "' class='tip' title='" . lang('view_gift_card') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-eye\"></i></a> <a href='" . admin_url('sales/topup_gift_card/$1') . "' class='tip' title='" . lang('topup_gift_card') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-dollar\"></i></a> <a href='" . admin_url('sales/edit_gift_card/$1') . "' class='tip' title='" . lang('edit_gift_card') . "' data-toggle='modal' data-target='#myModal'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_gift_card') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete_gift_card/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        //->unset_column('id');

        echo $this->datatables->generate();
    }




    public function convert_sale_invoice($sid)
    {
        $inv = $this->sales_model->getSaleByID($sid);
          
         if($inv->sale_invoice == 0){
             
          if ($this->sales_model->saleToInvoice($sid)) {

            # Update Sales to Completed
            if(isset($this->GP) && $this->GP['accountant']){
                $this->db->update('sales', ['sale_status' => 'completed'], ['id' => $sid]);
                $this->site->syncQuantity($sid);
            }
            
            $this->load->admin_model('companies_model');
            $customer = $this->companies_model->getCompanyByID($inv->customer_id);
            $inv_items = $this->sales_model->getAllSaleItems($sid);
            $warehouse_id = $inv->warehouse_id;
            $warehouse_ledgers = $this->site->getWarehouseByID($warehouse_id);

            /*Accounts Entries*/
            $entry = array(
                    'entrytype_id' => 4,
                    'transaction_type' => 'saleorder',
                    'number'       => 'SO-'.$inv->reference_no,
                    'date'         => date('Y-m-d'), 
                    'dr_total'     => $inv->grand_total,
                    'cr_total'     => $inv->grand_total,
                    'notes'        => 'Sale Reference: '.$inv->reference_no.' Date: '.date('Y-m-d H:i:s'),
                    'sid'          =>  $inv->id,
                    'customer_id'  => $inv->customer_id
                    );
            
            $add  = $this->db->insert('sma_accounts_entries', $entry);
            $insert_id = $this->db->insert_id();
             //$insert_id = 999;
             $entryitemdata = array();
             $inventory_amount = 0;
             $sale_amount = 0;
             $cogs_amount = 0;
             foreach ($inv_items as $item) 
             {
                 $proid = $item->product_id;
                 $product  = $this->site->getProductByID($proid);
                 //products
                 
                $inventory_amount += ($item->net_cost * ($item->quantity + $item->bonus));
                $sale_amount +=  ($item->real_unit_price * $item->unit_quantity); //$item->main_net;
                //add bonus to quantity
                $cogs_amount += ($item->net_cost * ($item->quantity + $item->bonus));
             }

            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $customer->cogs_ledger,
                    //'amount' => $item->main_net,
                    'amount' => $inv->cost_goods_sold,
                    'narration' => 'cost of goods sold'
                )
            );

            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'C',
                    'ledger_id' => $warehouse_ledgers->inventory_ledger,
                    //'amount' => $item->main_net,
                    'amount' => $inv->cost_goods_sold,
                    'narration' => 'inventory account'
                )
            );


              // //total discount
              $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $customer->discount_ledger,
                    'amount' => $inv->total_discount,
                    'narration' => 'total discount'
                )
         );

         
             // //customer
             $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $customer->ledger_account,
                    'amount' => ($inv->grand_total),
                    'narration' => 'customer'
                  )
            );

            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'C',
                    'ledger_id' => $customer->sales_ledger,
                    'amount' => $inv->total,
                    'narration' => 'sale account'
                )
            );
          
          
             // //vat on sale
             $entryitemdata[] = array(
                         'Entryitem' => array(
                             'entry_id' => $insert_id,
                             'dc' => 'C',
                             'ledger_id' => $this->vat_on_sale,
                             'amount' => $inv->total_tax,
                             'narration' => 'vat on sale'
                         )
                     );
 
 

           
                     
            //   /*Accounts Entry Items*/
            foreach ($entryitemdata as $row => $itemdata)
            {
                
                  $this->db->insert('sma_accounts_entryitems', $itemdata['Entryitem']);
            }

        }
        }
    }

    public function createRunXOrder($token, $sale, $courier){
        $authHeaderString = 'Authorization: Bearer ' . $token;
        $headers = array(
            $authHeaderString,
            'Accept: application/json',
            'Content-Type: application/json',
        );

        $address_id = $sale->address_id;
        $customer = $this->site->getCompanyByID($sale->customer_id);

        if($address_id == 0){
            $address = new stdClass();
            $address->line1 = $customer->address;
            $address->phone = $customer->phone;
            $address->longitude = $customer->longitude;
            $address->latitude = $customer->latitude;
            $address->first_name = $customer->first_name;
            $address->last_name = $customer->last_name;
        }else{
            $address = $this->site->getAddressByID($address_id);
        }

        if (strpos($address->phone, "+966") !== false) {
            //echo "The string contains +966.";
        } else {
            $address->phone = '+966'.$address->phone;
        }

        $sale_items = $this->site->getAllSaleItems($sale->id);

        $items_data = array();
        foreach ($sale_items as $sale_item){

            $product = $this->site->getProductByID($sale_item->product_id);
            $strippedDescription = str_replace('<p><strong>Product Description:</strong></p>', '', $product->product_details);
            $strippedDescription = strip_tags($strippedDescription, '<ul><li><strong>');
            // Remove additional line

            $items_data[] = array(
                'product_id' => $sale_item->product_id,
                'product_name' => $sale_item->product_name,
                'product_quantity' => $sale_item->quantity,
                'product_description' => $sale_item->product_name,
                'package_weight' => 0,
                'product_temperature' => 0,
                'package_length' => 0,
                'package_width' => 0,
                'package_height' => 0
            );
        }

        $data = array(
            //'order_number' => '123457'.$sale->id,
            'order_number' => $sale->id,
            'source_customer_phone' => '0114654636',
            'source_customer_name' => 'Avenzur.com',
            'source_customer_reference' => '0',
            'source_location_lat' => 0.0,
            'source_location_long' => 0.0,
            'source_address' => '-',
            'destination_customer_phone' => $address->phone,
            'destination_customer_name' => $address->first_name.' '.$address->last_name,
            'destination_customer_reference' => $sale->reference_no,
            'destination_location_lat' => !empty($address->latitude) ? $address->latitude : 0.0,
            'destination_location_long' => !empty($address->longitude) ? $address->longitude : 0.0,
            'destination_address' => $address->line1,
            //'destination_address' => $address->line1.', '.$address->line2.', '.$address->state.', '.$address->city.', '.$customer->country,
            'shipping_date' => date('Y-m-d'),
            'collection_time' => '00:00',
            'total_weight' => 0,
            'payment_type' => 'prepaid',
            'payment_method' => 'directpay',
            'payment_status' => 'paid',
            'price' => $sale->total,
            'tax' => $sale->total_tax,
            'delivery_fee' => $sale->shipping,
            'total_amount' => $sale->paid,
            'number_of_packages' => sizeOf($items_data),
            'notes' => 'no comments',
            'packages' => $items_data
        );

        $ch = curl_init($courier->url.'orders');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        curl_close($ch);

        return $response;
    }

    public function getRunXOrders() {
        $courier_id = 1;
        $courier = $this->site->getCourierById($courier_id);
    
        // Step 1: Get Bearer token
        $token = $this->getBearerToken($courier);
        // Step 2: Make API request with Bearer token
        $orderResponse = $this->makeOrderRequest($courier->url, $token);
        echo $orderResponse;
        // Process $orderResponse as needed
    }

    public function getJTOrders(){
        $courier_id = 3;
        $courier = $this->site->getCourierById($courier_id);

        // API endpoint URL
        $url = $courier->url.'order/getOrders';
        $body_digest='';

        $privateKey = $courier->auth_key;
        $customerCode= $courier->username;
        $pwd  = $courier->password;
        $account = $courier->api_account;
       
        $bizContent = '{
            "command": "1",
            "serialNumber": ["33", "30", "28"],
            "digest": "'.$body_digest.'"
        }';
        
        $post_data = $this->get_post_data($customerCode,$pwd,$privateKey,$bizContent);
        $head_dagest = $this->get_header_digest($post_data,$privateKey);
        $post_content = array(
            'bizContent' => $post_data
        );
    
        $postdata = http_build_query($post_content);
    
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' =>
                    array('Content-type: application/x-www-form-urlencoded',
                        'apiAccount:' . $account,
                        'digest:' . $head_dagest,
                        'timestamp: 1638428570653'),
                'content' => $postdata,
                'timeout' => 15 * 60
            )
        );
        $context = stream_context_create($options);
    
        $result = file_get_contents($url, false, $context);
        
        echo $result;
    }

    private function getBearerToken($courier) {
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
        );
    
        $data = array(
            'email' => $courier->username,
            'password' => $courier->password,
        );
    
        $ch = curl_init($courier->url . 'login');
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }
    
        curl_close($ch);
    
        if($respArr = json_decode($response)){
            if(isset($respArr->success)){
                $token = $respArr->success->token;
            }else{
                $token = false;
            }
        }else{
            $token = false;
        }
        return $token; // Assuming the token is in 'access_token' field
    }

    private function makeOrderRequest($apiUrl, $token) {
        $headers = array(
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
            'Content-Type: application/json',
        );
    
        $ch = curl_init($apiUrl . 'orders');
    
        // Set up your order request data here if needed
        // $orderData = ...
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // Set up other CURLOPT options as needed
        // ...
    
        // You can set CURLOPT_POSTFIELDS if you have data to send in the request body
    
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }
    
        curl_close($ch);
    
        return $response;
    }

    public function add_to_courier(){
        $courier_id = $_POST['Courier'];
        $sale_id = $_POST['sale_id'];
        $warehouse_id = $_POST['pickupLocation'];

        $courier = $this->site->getCourierById($courier_id);
        $sale = $this->site->getSaleByID($sale_id);
        $warehouse = $this->site->getWarehouseByID($warehouse_id);

        if($sale->courier_id == 0){
            if($courier->name == 'Run X'){
                $response = $this->assignRunX($sale, $courier);
                if($respArr = json_decode($response)){
                    if(isset($respArr->success)){
                        $token = $respArr->success->token;
                        $order = $this->createRunXOrder($token, $sale, $courier);
                        $order_resp = json_decode($order);
                        
                        if(isset($order_resp->errors) || (isset($order_resp->status) && $order_resp->status == false)){
                            $this->session->set_flashdata('error', $order_resp->message);
                            admin_redirect('sales/ecommerce');
                        }else{
                            $tracking_id = isset($order_resp->data->id) ? $order_resp->data->id : '';
                            $this->sales_model->updateSaleWithCourier($sale_id, $courier->id, $tracking_id, $warehouse_id);
                            $this->session->set_flashdata('message', 'Courier Assigned Successfully');
                            admin_redirect('sales/ecommerce');
                        }
                    }
                }
            }else if($courier->name == 'J&T'){
                $response = $this->assignJT($sale, $courier);
                $order_resp = json_decode($response);

                if((isset($order_resp->msg) && $order_resp->msg == 'success')){

                    $billCode = isset($order_resp->data->billCode) ? $order_resp->data->billCode : '';
                    $this->sales_model->updateSaleWithCourier($sale_id, $courier->id, $billCode, $warehouse_id);
                    $this->session->set_flashdata('message', 'Courier Assigned Successfully');
                    admin_redirect('sales/ecommerce');
                }else{
                    echo $order_resp->message;
                    print_r($order_resp);
                    exit;
                    $this->session->set_flashdata('error', $order_resp->message);
                    admin_redirect('sales/ecommerce');
                }
            }
            else if($courier->name == 'STC'){
                // echo "<pre>";
                // print_r($courier);
                // print_r($warehouse);
                // print_r($sale);
                // exit;
                $response = $this->assignSTC($sale, $courier, $warehouse);
                $order_resp = json_decode($response, true);
                // echo "<pre>";
                // print_r($order_resp);
                if(isset($order_resp['shipmentNumber'])){
                    $this->sales_model->updateSaleWithCourier($sale_id, $courier->id, $order_resp['shipmentNumber'], $warehouse_id);
                    $this->session->set_flashdata('message', 'Courier Assigned Successfully');
                    admin_redirect('sales/ecommerce');
                }else{
                    $this->session->set_flashdata('error', $order_resp['error_msg']);
                    admin_redirect('sales/ecommerce');
                }
               
            }
        }else{
            $this->session->set_flashdata('error', lang('Courier Already Assigned'));
            admin_redirect('sales/ecommerce');
        }
        
    }

    public function assignSTC($sale, $courier, $warehouse){
       
        $address_id = $sale->address_id;
        $customer = $this->site->getCompanyByID($sale->customer_id);

        if($address_id == 0){
            $address = new stdClass();
            $address->line1 = $customer->address;
            $address->line2 = '';
            $address->phone = $customer->phone;
            $address->email = $customer->email;
            $address->longitude = $customer->longitude;
            $address->latitude = $customer->latitude;
            $address->city = $customer->city;
            $address->state = $customer->state;
            $address->postal_code = $customer->postal_code;
            $address->country = $customer->country;
            $address->first_name = $customer->first_name;
            $address->last_name = $customer->last_name;
        }else{
            $address = $this->site->getAddressByID($address_id);
        }

        if (strpos($address->phone, "+966") !== false) {
            //echo "The string contains +966.";
        } else {
           // $address->phone = '+966'.$address->phone;
        }

        $sale_items = $this->site->getAllSaleItems($sale->id);

        $address->phone = $this->arabicToEnglishNumber($address->phone);
        //print_r($address);exit;

        $packages = [];
        foreach ($sale_items as $sale_item){

            $product = $this->site->getProductByID($sale_item->product_id);
            $strippedDescription = str_replace('<p><strong>Product Description:</strong></p>', '', $product->product_details);
            $strippedDescription = strip_tags($strippedDescription, '<ul><li><strong>');

            $packages[] = [
                "serialNo" => $sale_item->product_id,
                "sku" => $sale_item->product_code,
                "productName" => $sale_item->product_name,
                "itemCode" => $sale_item->product_code,
                "price" => $sale_item->subtotal,
                "htsCode" => ""
              
            ];

        }

        $packages_list = json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $url = $courier->url.'/shipment/create';
        $apiKey = $courier->api_account;
        $jsonDataArr = array(
            "partnerCode" => "avenzur",
            "serviceType" => "delivery",
            "orderReferences" => array(
                "partnerReference" => "avenzur-".$sale->id
            ),
            "pickUp"=> array(
                "contactName" => $warehouse->name,
                "contactMobile" => $warehouse->phone,
                "contactEmail" => $warehouse->email,
                "address" => $warehouse->address,
                "city" => $warehouse->city,
                "countryCode" => "SA",
                "hubCode" => $warehouse->hubcode,
                "longitude" => $warehouse->longitude,
                "latitude" => $warehouse->latitude,
                "nationalAddress" => $warehouse->national_address
            ),
            "dropOff" => array(
                "contactName" => $address->first_name.' '.$address->last_name,
                "contactMobile" => $address->phone,
                "contactEmail" => $address->email,
                "address" => $address->line1,
                "city" => $address->city,
                "countryCode" => "SA",
                "longitude" => $address->longitude,
                "latitude" => $address->latitude,
                "nationalAddress" => ""
            ),
            "payment" => array(
                "totalAmount" => $sale->total,
                "collectAmount" => 0.00,
                "currency" => "SAR"
            ),
            "packages" => json_decode($packages_list, true) 
        );
        
       
        $jsonData = json_encode($jsonDataArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // echo $url;
        // echo $apiKey; 
        // echo $jsonData;
        // Initialize cURL session
        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Api-Key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true); // HTTP POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Set request body

                // Execute the request
       $response = curl_exec($ch);
      
    //    if (curl_errno($ch)) {
    //         $error_msg = curl_error($ch);
    //         echo "cURL Error: $error_msg";
    //     } else {
    //         // Get HTTP status code
    //         $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
    //         if ($http_status == 200) {
    //             // Success response
    //             echo "Request was successful.\n";
    //             echo "Response: " . $response;
    //         } else {
    //             // Error response
    //             echo "Request failed with status code: $http_status.\n";
    //             echo "Response: " . $response;
    //         }
    //     }
        

        // Close cURL session
        curl_close($ch);
//print_r($response);exit;
        return  $response;

    }

    public function create_order($customerCode,$pwd,$key,$account,$waybillinfo,$url) {
        $post_data = $this->get_post_data($customerCode,$pwd,$key,$waybillinfo);
        $head_dagest = $this->get_header_digest($post_data,$key);
        $post_content = array(
            'bizContent' => $post_data
        );
    
        $postdata = http_build_query($post_content);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' =>
                    array('Content-type: application/x-www-form-urlencoded',
                        'apiAccount:' . $account,
                        'digest:' . $head_dagest,
                        'timestamp: 1638428570653'),
                'content' => $postdata,
                'timeout' => 15 * 60
            )
        );
       
        $context = stream_context_create($options);
       
        $result = file_get_contents($url, false, $context);
        
        return $result;
    }

    public function assignJT($sale, $courier){
        // API endpoint URL
        $url = $courier->url.'order/addOrder?uuid=3c201038f68747128c8a49c793747a02';

        $privateKey = $courier->auth_key;
        $customerCode= $courier->username;
        $pwd  = $courier->password;
        $account = $courier->api_account;
        $waybillinfo = $this->populateShipmentParams($sale, $courier);
        $resp = $this->create_order($customerCode, $pwd, $privateKey, $account, $waybillinfo, $url);
        
        return $resp;

    }

    public function get_post_data($customerCode,$pwd,$key,$waybillinfo){
        //echo $waybillinfo;exit;
        $postdate = json_decode($waybillinfo, true);
        $postdate['customerCode'] = $customerCode;
        $postdate['digest'] = $this->get_content_digest($customerCode,$pwd,$key);
        return json_encode($postdate);
    }

    public function arabicToEnglishNumber($arabicNumber)
    {
        $arabicNumerals = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $englishNumerals = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $englishNumber = str_replace($arabicNumerals, $englishNumerals, $arabicNumber);

        return $englishNumber;
    }

    public function populateShipmentParams($sale, $courier)
    {
        $address_id = $sale->address_id;
        $customer = $this->site->getCompanyByID($sale->customer_id);

        if($address_id == 0){
            $address = new stdClass();
            $address->line1 = $customer->address;
            $address->line2 = '';
            $address->phone = $customer->phone;
            $address->longitude = $customer->longitude;
            $address->latitude = $customer->latitude;
            $address->city = $customer->city;
            $address->state = $customer->state;
            $address->postal_code = $customer->postal_code;
            $address->country = $customer->country;
            $address->first_name = $customer->first_name;
            $address->last_name = $customer->last_name;
        }else{
            $address = $this->site->getAddressByID($address_id);
        }

        if (strpos($address->phone, "+966") !== false) {
            //echo "The string contains +966.";
        } else {
            $address->phone = '+966'.$address->phone;
        }

        $sale_items = $this->site->getAllSaleItems($sale->id);

        $address->phone = $this->arabicToEnglishNumber($address->phone);

        //$countryArr = $this->site->getCountryByName($address->country);

        $items_data = array();
        $items_str = '';
        $count = 0;
        $jand_items = [];
        foreach ($sale_items as $sale_item){

            $product = $this->site->getProductByID($sale_item->product_id);
            $strippedDescription = str_replace('<p><strong>Product Description:</strong></p>', '', $product->product_details);
            $strippedDescription = strip_tags($strippedDescription, '<ul><li><strong>');

            if($count > 0){
                $items_str .= ',';
            }

            $items_str .= '{
                "number":"'.$sale_item->product_id.'",
                "itemType":"ITN4",
                "itemName":"'.$sale_item->product_name.'",
                "priceCurrency":"SAR",
                "itemValue":"'.$sale_item->subtotal.'",
                "itemUrl":"https:\/\/www.avenzur.com/product/'.$product->slug.'",
                "desc":"'.$sale_item->product_name.'"
            }';

            $jand_items[] = [
                "number" => $sale_item->product_id,
                "itemType" => "ITN4",
                "itemName" => $sale_item->product_name,
                "priceCurrency" => "SAR",
                "itemValue" => $sale_item->subtotal,
                "itemUrl" => "https://www.avenzur.com/product/" . $product->slug,
                "desc" => $sale_item->product_name
            ];

            $count++;
        }

        $jandt_items_str = json_encode($jand_items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // $waybillinfo = '{
        //     "serviceType": "01",
        //     "orderType": "1",
        //     "deliveryType": "04",
        //     "countryCode": "KSA",
        //     "receiver":{
        //         "address":"'.$address->line1.' '.$address->line2.'",
        //         "city":"'.$address->city.'",
        //         "mobile":"'.$address->phone.'",
        //         "phone":"'.$address->phone.'",
        //         "countryCode":"KSA",
        //         "name":"'.$address->first_name.' '.$address->last_name.'",
        //         "postCode":"'.$address->postal_code.'",
        //         "prov":"'.$address->state.'"
        //     },
        //     "expressType":"EZKSA",
        //     "remark":"",
        //     "txlogisticId":"'.$sale->id.'",
        //     "goodsType":"ITN4",
        //     "priceCurrency":"SAR",
        //     "sender":{
        //         "address":"Business Gate, Riyadh KSA",
        //         "city":"Riyadh",
        //         "mobile":"0114654636",
        //         "phone":"0114654636",
        //         "countryCode":"KSA",
        //         "name":"Avenzur.com",
        //         "prov":"Riyadh"
        //     },
        //     "itemsValue":"0",
        //     "items":'.$jandt_items_str.',
        //     "operateType":1
        // }';


        $waybillInfo = [
            "serviceType" => "01",
            "orderType" => "1",
            "deliveryType" => "04",
            "countryCode" => "KSA",
            "receiver" => [
                "address" => $address->line1 . ' ' . $address->line2,
                "city" => $address->city,
                "mobile" => $address->phone,
                "phone" => $address->phone,
                "countryCode" => "KSA",
                "name" => $address->first_name . ' ' . $address->last_name,
                "postCode" => $address->postal_code,
                "prov" => $address->state
            ],
            "expressType" => "EZKSA",
            "remark" => "",
            "txlogisticId" => $sale->id,
            "goodsType" => "ITN4",
            "priceCurrency" => "SAR",
            "sender" => [
                "address" => "Business Gate, Riyadh KSA",
                "city" => "Riyadh",
                "mobile" => "0114654636",
                "phone" => "0114654636",
                "countryCode" => "KSA",
                "name" => "Avenzur.com",
                "prov" => "Riyadh"
            ],
            "itemsValue" => "0",
            "items" => json_decode($jandt_items_str, true),  
            "operateType" => 1
        ];

        $jsonWaybillInfo = json_encode($waybillInfo, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        //echo "data:".$waybillinfo;
        
        return $jsonWaybillInfo;
    }

    public function get_content_digest($customerCode,$pwd,$key)
    {
        $str = strtoupper($customerCode . md5($pwd . 'jadada236t2')) . $key;

        return base64_encode(pack('H*', strtoupper(md5($str))));
    }

    public function get_header_digest($post,$key){
        $digest = base64_encode(pack('H*',strtoupper(md5($post.$key))));
        return $digest;
    }

    /*public function assignJT($sale, $courier){

        $headers = array(
            'apiAccount: '.$apiAccount,
            'digest: tEnWMX751wCYwyChevGzyg==',
            'timestamp: '.time(),
            'Content-Type: application/x-www-form-urlencoded',
        );

        $express_type = $sale->delivery_type == 'Express' ? 'SDD' : 'EZKSA';

        $data = array(
            'bizContent' => json_encode(array(
                'customerCode' => $courier->username, // This is test customer code
                'digest' => $body_digest,
                'serviceType' => '02', // 01 => door to door pickup, 02 => store delivery
                'orderType' => '1', // 1 => individual customer, 2 => monthly settlement
                'deliveryType' => '03', // 03 => pick at store, 04 => door to door pickup
                'expressType' => 'SDD', // SDD => Same day delivery KSA, EZKSA => standard KSA 
                'sendStartTime' => date("Y-m-d H:i:s"),
                'weight' => 0, // We cannot send this
                'billCode' => $sale->reference_no, // Waybill number (Needed)
                //'batchNumber' => '',
                'txlogisticId' => $sale->id,
                'goodsType' => 'ITN4', // ITN1 Clothing, ITN2 file, ITN3 Food, ITN4 Others, ITN5 digital products, ITN6 daily necessities, ITN7 Fragile
                'receiver' => array(
                    'address' => $address->line1.', '.$address->line2.', '.$address->state.', '.$address->city.', '.$customer->country,
                    'city' => $address->city,
                    'mobile' => $address->phone,
                    'phone' => $address->phone,
                    'countryCode' => $countryArr->iso3,
                    'name' => $customer->name,
                    'postCode' => $address->postal_code,
                    'prov' => $address->state
                ),
                'sender' => array(
                    'address' => 'Business Gate, Riyadh KSA',
                    'city' => 'Riyadh',
                    'mobile' => '0114654636',
                    'phone' => '0114654636',
                    'countryCode' => 'KSA',
                    'name' => 'Avenzur.com',
                    'prov' => 'Riyadh'
                ),
                'itemsValue' => $sale->paid,
                'priceCurrency' => 'SAR',
                'items' => $items_data,
                'sendEndTime' => date("Y-m-d H:i:s"),
                'operateType' => 1, // 1 => New, 2 => Modifications
                'platformName' => 'Avenzur.com',
            )),
        );

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Encode data as X-WWW-FORM-URLENCODED
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }

        curl_close($ch);

        return $response;

    }*/

    public function assignRunX($sale, $courier){
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json',
        );
    
        $data = array(
            'email' => $courier->username,
            'password' => $courier->password,
        );
    
        $ch = curl_init($courier->url.'login');
    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Send JSON-encoded data
    
        $response = curl_exec($ch);
    
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        }
    
        curl_close($ch);
    
        return $response;
    }

    public function assign_courier($id = null)
    {
        $this->sma->checkPermissions();
        $this->data['sale_id'] = $id;
        //$this->data['inv']      = $this->purchases_model->getPurchaseByID($id);

        //if(empty($this->data['inv']->invoice_number) || $this->data['inv']->invoice_number == ''){
        //    $this->session->set_flashdata('error', 'Cannot transfer orders that are not invoiced');
            //return false;
            //admin_redirect('purchases');
        //}

        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['couriers'] = $this->site->getAllCouriers();
        //$this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
        $this->load->view($this->theme . 'sales/assign_courier', $this->data);
        
    }

    public function getEcommerceSales($warehouse_id = null){
       
        //  echo '<pre>'; print_r($this->input->post()); exit; 
        $keyword=  trim($this->input->post('keyword'));  
        $this->sma->checkPermissions('index');

        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link       = anchor('admin/sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $duplicate_link    = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link     = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        
          if(isset($this->GP) && $this->GP['accountant'])
        {
            $convert_sale_invoice = anchor('admin/sales/convert_sale_invoice/$1', '<i class="fa fa-money"></i> ' . lang('Convert to Invoice'));
        }
        $convert_sale_invoice = anchor('admin/sales/convert_sale_invoice/$1', '<i class="fa fa-money"></i> ' . lang('Convert to Invoice'));
        
        $add_payment_link  = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $packagink_link    = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link        = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link         = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link          = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link       = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        $courier_link    = anchor('admin/sales/assign_courier/$1', '<i class="fa fa-money"></i> ' . lang('Assign to Courier'), 'data-toggle="modal" data-target="#myModal"');
        //$shipment_link     = anchor('$1', '<i class="fa fa-angle-double-left"></i> ' . lang('Shipping_Slip'));
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        
        
       if(isset($this->GP) && $this->GP['accountant'])
        {

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $convert_sale_invoice . '</li>
            <li>' . $detail_link . '</li>
            <li>' . $duplicate_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $packagink_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
    }else{

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
        <li>' . $convert_sale_invoice . '</li>
            <li>' . $detail_link . '</li>
            <li>' . $duplicate_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $packagink_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>
            <li>' .$courier_link. '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';
    }
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id as id, {$this->db->dbprefix('sales')}.id as sale_id,
                DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no, 
                {$this->db->dbprefix('sales')}.sequence_code as code, biller,  
                CASE WHEN {$this->db->dbprefix('sales')}.address_id IS NULL OR {$this->db->dbprefix('sales')}.address_id=0 THEN CONCAT({$this->db->dbprefix('companies')}.first_name, ' ',{$this->db->dbprefix('companies')}.last_name )  ELSE CONCAT({$this->db->dbprefix('addresses')}.first_name,' ', {$this->db->dbprefix('addresses')}.last_name) END AS customer,
                CASE WHEN {$this->db->dbprefix('sales')}.address_id IS NULL OR {$this->db->dbprefix('sales')}.address_id=0 THEN {$this->db->dbprefix('companies')}.phone  ELSE ({$this->db->dbprefix('addresses')}.phone) END AS phone,  
                sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, 
                {$this->db->dbprefix('sales')}.attachment, return_id, {$this->db->dbprefix('courier')}.name as courier_name, 
                CASE WHEN {$this->db->dbprefix('sales')}.address_id IS NULL OR {$this->db->dbprefix('sales')}.address_id=0 THEN {$this->db->dbprefix('companies')}.city  ELSE ({$this->db->dbprefix('addresses')}.city) END AS city,

                {$this->db->dbprefix('sales')}.delivery_type,{$this->db->dbprefix('sales')}.courier_order_status, warehouses.name as warehouse_name")
                ->from('sales')
                ->join('warehouses', 'warehouses.id = sales.pickup_location_id', 'left')
                ->where('warehouse_id', $warehouse_id)
                ->where('shop', 1);
                //->join('aramex_shipment', 'aramex_shipment.salesid=sales.id');
        } else {
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id as id, {$this->db->dbprefix('sales')}.id as sale_id , DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, 
                reference_no, {$this->db->dbprefix('sales')}.sequence_code as code, biller, 
                CASE WHEN {$this->db->dbprefix('sales')}.address_id IS NULL OR {$this->db->dbprefix('sales')}.address_id=0 THEN CONCAT({$this->db->dbprefix('companies')}.first_name, ' ',{$this->db->dbprefix('companies')}.last_name )   ELSE CONCAT({$this->db->dbprefix('addresses')}.first_name,' ', {$this->db->dbprefix('addresses')}.last_name) END AS customer,
                CASE WHEN {$this->db->dbprefix('sales')}.address_id IS NULL OR {$this->db->dbprefix('sales')}.address_id=0 THEN {$this->db->dbprefix('companies')}.phone  ELSE ({$this->db->dbprefix('addresses')}.phone) END AS phone,  
                sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, {$this->db->dbprefix('sales')}.attachment, 
                return_id, {$this->db->dbprefix('courier')}.name as courier_name,  
                CASE WHEN {$this->db->dbprefix('sales')}.address_id IS NULL OR {$this->db->dbprefix('sales')}.address_id=0 THEN {$this->db->dbprefix('companies')}.city  ELSE ({$this->db->dbprefix('addresses')}.city) END AS city,
                {$this->db->dbprefix('sales')}.delivery_type,{$this->db->dbprefix('sales')}.courier_order_status, warehouses.name as warehouse_name")
                ->from('sales')
                ->join('warehouses', 'warehouses.id = sales.pickup_location_id', 'left')
                ->where('shop', 1);  
        }
 
        $subquery = "(SELECT COUNT(*) FROM sma_sale_items WHERE sma_sale_items.sale_id = sma_sales.id AND (sma_sale_items.product_code LIKE 'AM-%' OR sma_sale_items.product_code LIKE 'IH-%'))";
        $this->datatables
            ->select("{$subquery} > 0 AS global_product", false); // Use a subquery to determine global_product
        
        // $this->datatables->join("{$this->db->dbprefix('aramex_shipment')}", 'sales.id');
        if ($this->input->get('shop') == 'yes') {
            $this->datatables->where('shop', 1);
        } elseif ($this->input->get('shop') == 'no') {
            $this->datatables->where('shop !=', 1);
        }
        if ($this->input->get('delivery') == 'no') {
            $this->datatables->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
            ->where('sales.sale_status', 'completed')->where('sales.payment_status', 'paid')
            ->where("({$this->db->dbprefix('deliveries')}.status != 'delivered' OR {$this->db->dbprefix('deliveries')}.status IS NULL)", null);
        } 
        $this->datatables->join('courier', 'courier.id=sales.courier_id', 'left');
       //  $this->datatables->join('addresses', 'addresses.id=sales.address_id', 'left'); by mm  
       // ({$this->db->dbprefix('sales')}.address_id IS NOT NULL and {$this->db->dbprefix('sales')}.address_id!=0 )
        $this->datatables->join("addresses", "{$this->db->dbprefix('sales')}.address_id>0   AND {$this->db->dbprefix('sales')}.address_id = {$this->db->dbprefix('addresses')}.id", "left");
        $this->datatables->join("companies", "({$this->db->dbprefix('sales')}.address_id IS NULL OR {$this->db->dbprefix('sales')}.address_id=0 )  AND {$this->db->dbprefix('sales')}.customer_id = {$this->db->dbprefix('companies')}.id", "left");
       
       if(!empty( $keyword)){ 
        
        $this->db->group_start();  
        $this->datatables->where("{$this->db->dbprefix('sales')}.sale_status",$keyword); 
        $this->datatables->or_where("{$this->db->dbprefix('sales')}.payment_status",$keyword); 
        $this->datatables->or_where("{$this->db->dbprefix('sales')}.id",$keyword);  
        $this->datatables->or_where("{$this->db->dbprefix('sales')}.reference_no",$keyword);  
        $this->datatables->or_where("{$this->db->dbprefix('sales')}.sequence_code",$keyword);  
        $this->datatables->or_where("{$this->db->dbprefix('sales')}.courier_order_status",$keyword); 
        $this->datatables->or_where("{$this->db->dbprefix('courier')}.name",$keyword); 
        $this->datatables->or_where("{$this->db->dbprefix('sales')}.delivery_type",$keyword);         

        $this->datatables->or_where("({$this->db->dbprefix('sales')}.address_id >0 AND ({$this->db->dbprefix('addresses')}.phone LIKE '%".$keyword."%' OR {$this->db->dbprefix('addresses')}.first_name LIKE '%".$keyword."%' OR {$this->db->dbprefix('addresses')}.last_name LIKE '%".$keyword."%' OR   concat_ws(' ',{$this->db->dbprefix('addresses')}.first_name,{$this->db->dbprefix('addresses')}.last_name) like '%$".$keyword."%'  OR {$this->db->dbprefix('addresses')}.city LIKE '%".$keyword."%')) OR 
        (({$this->db->dbprefix('sales')}.address_id IS NULL or {$this->db->dbprefix('sales')}.address_id=0) AND ({$this->db->dbprefix('companies')}.phone LIKE '%".$keyword."%' OR {$this->db->dbprefix('companies')}.first_name LIKE '%".$keyword."%' OR {$this->db->dbprefix('companies')}.last_name LIKE '%".$keyword."%' OR   concat_ws(' ',{$this->db->dbprefix('companies')}.first_name,{$this->db->dbprefix('companies')}.last_name) like '%$".$keyword."%' OR {$this->db->dbprefix('companies')}.city LIKE '%".$keyword."%'))
        ");   
        $this->db->group_end();   
       }  
        if ($this->input->get('attachment') == 'yes') {
            $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        }
        $this->datatables->where('pos !=', 1); // ->where('sale_status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }


    public function getSales($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');
        $sid = $this->input->get('sid');
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link       = anchor('admin/sales/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('sale_details'));
        $duplicate_link    = anchor('admin/sales/add?sale_id=$1', '<i class="fa fa-plus-circle"></i> ' . lang('duplicate_sale'));
        $payments_link     = anchor('admin/sales/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        
          if(isset($this->GP) && $this->GP['accountant'])
        {
            $convert_sale_invoice = anchor('admin/sales/convert_sale_invoice/$1', '<i class="fa fa-money"></i> ' . lang('Convert to Invoice'));
        }
        
        $convert_sale_invoice = anchor('admin/sales/convert_sale_invoice/$1', '<i class="fa fa-money"></i> ' . lang('Convert to Invoice'));
        $add_payment_link  = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $packagink_link    = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link        = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link         = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link          = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link       = anchor('admin/returns/add/?sale=$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
        //$shipment_link     = anchor('$1', '<i class="fa fa-angle-double-left"></i> ' . lang('Shipping_Slip'));
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('sales/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_sale') . '</a>';
        $journal_entry_link      = anchor('admin/entries/view/journal/?sid=$1', '<i class="fa fa-eye"></i> ' . lang('Journal Entry'));
        
       if(isset($this->GP) && $this->GP['accountant'])
        {

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $convert_sale_invoice . '</li>
            <li>' . $detail_link . '</li>
            <li>' . $duplicate_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $packagink_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
            <li>' . $journal_entry_link . '</li>
        </ul>
    </div></div>';
    }else{

        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
         <li>' . $convert_sale_invoice . '</li>
            <li>' . $detail_link . '</li>
            <li>' . $duplicate_link . '</li>
            <li>' . $payments_link . '</li>
            <li>' . $add_payment_link . '</li>
            <li>' . $packagink_link . '</li>
            <li>' . $add_delivery_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
            <li>' . $journal_entry_link . '</li>
        </ul>
    </div></div>';
    }
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id as id, DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('sales')}.sequence_code as code, biller, {$this->db->dbprefix('sales')}.customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, {$this->db->dbprefix('sales')}.attachment, return_id")
                ->from('sales')
                ->where('warehouse_id', $warehouse_id)
                ->where('shop', 0);
                //->join('aramex_shipment', 'aramex_shipment.salesid=sales.id');
        } else {
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id as id, DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('sales')}.sequence_code as code, biller, {$this->db->dbprefix('sales')}.customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, {$this->db->dbprefix('sales')}.attachment, return_id")
                ->from('sales')
                ->where('shop', 0); 
        } 
        if(is_numeric($sid)) {
            $this->datatables->where('id', $sid);
        }
        // $this->datatables->join("{$this->db->dbprefix('aramex_shipment')}", 'sales.id');
        if ($this->input->get('shop') == 'yes') {
            $this->datatables->where('shop', 1);
        } elseif ($this->input->get('shop') == 'no') {
            $this->datatables->where('shop !=', 1);
        }
        if ($this->input->get('delivery') == 'no') {
            $this->datatables->join('deliveries', 'deliveries.sale_id=sales.id', 'left')
            ->where('sales.sale_status', 'completed')->where('sales.payment_status', 'paid')
            ->where("({$this->db->dbprefix('deliveries')}.status != 'delivered' OR {$this->db->dbprefix('deliveries')}.status IS NULL)", null);
        }
        if ($this->input->get('attachment') == 'yes') {
            $this->datatables->where('payment_status !=', 'paid')->where('attachment !=', null);
        }
        $this->datatables->where('pos !=', 1); // ->where('sale_status !=', 'returned');
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Customer) {
            $this->datatables->where('customer_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }

    public function gift_card_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete_gift_card');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteGiftCard($id);
                    }
                    $this->session->set_flashdata('message', lang('gift_cards_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('gift_cards'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('card_no'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('value'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('customer'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sc = $this->site->getGiftCardByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $sc->card_no);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sc->value);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sc->customer);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'gift_cards_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_gift_card_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
        
    }

    /* ------------------------------------ Gift Cards ---------------------------------- */

    public function gift_cards()
    {
        $this->sma->checkPermissions();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('gift_cards')]];
        $meta = ['page_title' => lang('gift_cards'), 'bc' => $bc];
        $this->page_construct('sales/gift_cards', $meta, $this->data);
    }

    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses']   = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse']    = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['lastInsertedId'] =  $this->input->get('lastInsertedId') ;
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
        $meta = ['page_title' => lang('sales'), 'bc' => $bc];
        $this->data['sid'] = $this->input->get('sid');
        $this->page_construct('sales/index', $meta, $this->data);
    }

    public function ecommerce($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses']   = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse']    = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses']   = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse']    = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
        $meta = ['page_title' => lang('sales'), 'bc' => $bc];
        $this->page_construct('sales/ecommerce', $meta, $this->data);
    }



    public function modal_view($id = null)
    {
        $this->sma->checkPermissions('index', true);
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['address']     = $this->site->getAddressByID($inv->address_id);
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['attachments'] = $this->site->getAttachments($id, 'sale');
        $this->data['sale_id'] = $id;

        $this->load->view($this->theme . 'sales/modal_view', $this->data);
    }

    public function packaging($id)
    {
        $sale                   = $this->sales_model->getInvoiceByID($id);
        $this->data['returned'] = false;
        if ($sale->sale_status == 'returned' || $sale->return_id) {
            $this->data['returned'] = true;
        }
        $this->data['warehouse'] = $this->site->getWarehouseByID($sale->warehouse_id);
        $items                   = $this->sales_model->getAllInvoiceItems($sale->id);
        foreach ($items as $item) {
            $packaging[] = [
                'name'     => $item->product_code . ' - ' . $item->product_name,
                'quantity' => $item->quantity,
                'unit'     => $item->product_unit_code,
                'rack'     => $this->sales_model->getItemRack($item->product_id, $sale->warehouse_id),
            ];
        }
        $this->data['packaging'] = $packaging;
        $this->data['sale']      = $sale;

        $this->load->view($this->theme . 'sales/packaging', $this->data);
    }

    public function payment_note($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment                  = $this->sales_model->getPaymentByID($id);
        $inv                      = $this->sales_model->getInvoiceByID($payment->sale_id);
        $this->data['biller']     = $this->site->getCompanyByID($inv->biller_id);
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['inv']        = $inv;
        $this->data['payment']    = $payment;
        $this->data['page_title'] = lang('payment_note');

        $this->load->view($this->theme . 'sales/payment_note', $this->data);
    }

    /* -------------------------------------------------------------------------------- */

    public function payments($id = null)
    {
        $this->sma->checkPermissions(false, true);
        $this->data['payments'] = $this->sales_model->getInvoicePayments($id);
        $this->data['inv']      = $this->sales_model->getInvoiceByID($id);
        $this->load->view($this->theme . 'sales/payments', $this->data);
    }

    public function pdf($id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['user']        = $this->site->getUser($inv->created_by);
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        //$this->data['paypal'] = $this->sales_model->getPaypalSettings();
        //$this->data['skrill'] = $this->sales_model->getSkrillSettings();
        // echo "<pre>";
        // print_r($this->data);exit;
        $name = lang('sale') . '_' . str_replace('/', '_', $inv->reference_no) . '.pdf';
        $html = $this->load->view($this->theme . 'sales/pdf/sales_invoice_report', $this->data, true);
        //echo $html;exit;
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
       
       // $outputPath = FCPATH . 'reports/sample_report.pdf';
        //$this->pdfService->generatePDF( $html, 'sale_invoice.pdf');
       // echo "PDF generated at: " . base_url('reports/sample_report.pdf');
        $this->load->view($this->theme . 'sales/pdf/sales_invoice_report', $this->data);
        if ($view) {
            $this->load->view($this->theme . 'sales/pdf/sales_invoice_report', $this->data);
        } elseif ($save_bufffer) {
           // return $this->sma->generate_pdf($html, $name, $save_bufffer, $this->data['biller']->invoice_footer);
        } else {
            $this->sma->generate_pdf($html, $name, 'I', $this->data['biller']->invoice_footer);
        }
    }

    public function pdf_delivery($id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli                = $this->sales_model->getDeliveryByID($id);

        $this->data['delivery'] = $deli;
        $sale                   = $this->sales_model->getInvoiceByID($deli->sale_id);
        $this->data['biller']   = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows']     = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
        $this->data['user']     = $this->site->getUser($deli->created_by);

        $name = lang('delivery') . '_' . str_replace('/', '_', $deli->do_reference_no) . '.pdf';
        $html = $this->load->view($this->theme . 'sales/pdf_delivery', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'sales/pdf_delivery', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->sma->generate_pdf($html, $name);
        }
    }

    /* ------------------------------- */

    public function return_sale($id = null)
    {
        $this->sma->checkPermissions('return_sales');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $sale = $this->sales_model->getInvoiceByID($id);
        if ($sale->return_id) {
            $this->session->set_flashdata('error', lang('sale_already_returned'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('return_surcharge', lang('return_surcharge'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $customer_details = $this->site->getCompanyByID($sale->customer_id);
            $biller_details   = $this->site->getCompanyByID($sale->biller_id);

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $sale_item_id       = $_POST['sale_item_id'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = (0 - $_POST['quantity'][$r]);
                $item_serial        = $_POST['serial'][$r]           ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = (0 - $_POST['product_base_quantity'][$r]);

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->sma->formatDecimal(($unit_price - $pr_discount), 4);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity, 4);
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
                    $subtotal = $this->sma->formatDecimal((($item_net_price * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit     = $item_unit ? $this->site->getUnitByID($item_unit) : false;

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $item_unit,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $sale->warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'real_unit_price'   => $real_unit_price,
                        'sale_item_id'      => $sale_item_id,
                    ];

                    $si_return[] = [
                        'id'           => $sale_item_id,
                        'sale_id'      => $id,
                        'product_id'   => $item_id,
                        'option_id'    => $item_option,
                        'quantity'     => (0 - $item_quantity),
                        'warehouse_id' => $sale->warehouse_id,
                    ];

                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = (0 - $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax)));
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($return_surcharge) + (0 - $shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $return_surcharge + (0 - $shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $data        = [
                'date'              => $date,
                'sale_id'           => $id,
                'reference_no'      => $sale->reference_no,
                'customer_id'       => $sale->customer_id,
                'customer'          => $sale->customer,
                'biller_id'         => $sale->biller_id,
                'biller'            => $sale->biller,
                'warehouse_id'      => $sale->warehouse_id,
                'note'              => $note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('discount') ? $this->input->post('order_discount') : null,
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'surcharge'         => $this->sma->formatDecimal($return_surcharge),
                'grand_total'       => $grand_total,
                'created_by'        => $this->session->userdata('user_id'),
                'return_sale_ref'   => $reference,
                'shipping'          => $shipping,
                'sale_status'       => 'returned',
                'pos'               => $sale->pos,
                'payment_status'    => $sale->payment_status == 'paid' ? 'due' : 'pending',
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($this->input->post('amount-paid') && $this->input->post('amount-paid') > 0) {
                $pay_ref = $this->input->post('payment_reference_no') ? $this->input->post('payment_reference_no') : $this->site->getReference('pay');
                $payment = [
                    'date'         => $date,
                    'reference_no' => $pay_ref,
                    'amount'       => (0 - $this->input->post('amount-paid')),
                    'paid_by'      => $this->input->post('paid_by'),
                    'cheque_no'    => $this->input->post('cheque_no'),
                    'cc_no'        => $this->input->post('pcc_no'),
                    'cc_holder'    => $this->input->post('pcc_holder'),
                    'cc_month'     => $this->input->post('pcc_month'),
                    'cc_year'      => $this->input->post('pcc_year'),
                    'cc_type'      => $this->input->post('pcc_type'),
                    'created_by'   => $this->session->userdata('user_id'),
                    'type'         => 'returned',
                ];
                $data['payment_status'] = $grand_total == $this->input->post('amount-paid') ? 'paid' : 'partial';
            } else {
                $payment = [];
            }

            $attachments        = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            // $this->sma->print_arrays($data, $products, $si_return, $payment);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, $si_return, $attachments)) {
            $this->session->set_flashdata('message', lang('return_sale_added'));
            admin_redirect($sale->pos ? 'pos/sales' : 'sales');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $sale;
            if ($this->data['inv']->sale_status != 'completed') {
                $this->session->set_flashdata('error', lang('sale_status_x_competed'));
                redirect($_SERVER['HTTP_REFERER']);
            }
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang('sale_x_edited_older_than_x_days'), $this->Settings->disable_editing));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $inv_items = $this->sales_model->getAllInvoiceItems($id);
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row             = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity   = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }
                $pis = $this->site->getPurchasedItems($item->product_id, $item->warehouse_id, $item->option_id);
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                $row->id              = $item->product_id;
                $row->sale_item_id    = $item->id;
                $row->code            = $item->product_code;
                $row->name            = $item->product_name;
                $row->type            = $item->product_type;
                $row->unit            = $item->product_unit_id;
                $row->qty             = $item->unit_quantity;
                $row->oqty            = $item->unit_quantity;
                $row->discount        = $item->discount ? $item->discount : '0';
                $row->item_tax        = $item->item_tax      > 0 ? $item->item_tax      / $item->quantity : 0;
                $row->item_discount   = $item->item_discount > 0 ? $item->item_discount / $item->quantity : 0;
                $row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($item->item_discount / $item->unit_quantity));
                $row->unit_price      = $row->tax_method ? $item->unit_price + $this->sma->formatDecimal($item->item_discount / $item->unit_quantity) - $this->sma->formatDecimal($item->item_tax / $item->unit_quantity) : $item->unit_price + ($item->item_discount / $item->unit_quantity);
                $row->real_unit_price = $item->real_unit_price;
                $row->base_quantity   = $item->quantity;
                $row->base_unit       = $row->unit       ?? $item->product_unit_id;
                $row->base_unit_price = $row->unit_price ?? $item->unit_price;
                $row->tax_rate        = $item->tax_rate_id;
                $row->serial          = $item->serial_no;
                $row->option          = $item->option_id;
                $options              = $this->sales_model->getProductOptions($row->id, $item->warehouse_id, true);
                $units                = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate             = $this->site->getTaxRateByID($row->tax_rate);
                $ri                   = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options];
                $c++;
            }
            $this->data['inv_items']   = json_encode($pr);
            $this->data['id']          = $id;
            $this->data['payment_ref'] = '';
            $this->data['reference']   = ''; // $this->site->getReference('re');
            $this->data['tax_rates']   = $this->site->getAllTaxRates();
            $bc                        = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('return_sale')]];
            $meta                      = ['page_title' => lang('return_sale'), 'bc' => $bc];
            $this->page_construct('sales/return_sale', $meta, $this->data);
        }
    }

    public function sale_actions()
    {
        if (!$this->Owner && !$this->GP['bulk_actions']) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    $this->sma->checkPermissions('delete');
                    foreach ($_POST['val'] as $id) {
                        $this->sales_model->deleteSale($id);
                    }
                    $this->session->set_flashdata('message', lang('sales_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('sales'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('paid'));
                    $this->excel->getActiveSheet()->SetCellValue('G1', lang('payment_status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $sale = $this->sales_model->getInvoiceByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($sale->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $sale->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sale->biller);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sale->customer);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sale->grand_total);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, lang($sale->paid));
                        $this->excel->getActiveSheet()->SetCellValue('G' . $row, lang($sale->payment_status));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'sales_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_sale_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    /* -------------------------------------------------------------------------------------- */

    public function sale_by_csv()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_rules('userfile', lang('upload_file'), 'xss_clean');
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('so');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $sale_status      = $this->input->post('sale_status');
            $payment_status   = $this->input->post('payment_status');
            $payment_term     = $this->input->post('payment_term');
            $due_date         = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days')) : null;
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = $customer_details->company && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = $biller_details->company && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('sales/sale_by_csv');
                }
                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle    = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $arr_length = count($arrResult);
                if ($arr_length > 499) {
                    $this->session->set_flashdata('error', lang('too_many_products'));
                    redirect($_SERVER['HTTP_REFERER']);
                    exit();
                }
                $titles = array_shift($arrResult);
                $keys   = ['code', 'net_unit_price', 'quantity', 'variant', 'item_tax_rate', 'discount', 'serial','batch_no'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_price']) && isset($csv_pr['quantity']) && isset($csv_pr['batch_no'])) {    
                        if ($product_details = $this->sales_model->getProductByCode($csv_pr['code'])) {
                            if ($csv_pr['variant']) {
                                $item_option = $this->sales_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang('pr_not_found') . ' ( ' . $product_details->name . ' - ' . $csv_pr['variant'] . ' ). ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER['HTTP_REFERER']);
                                }
                            } else {
                                $item_option     = json_decode('{}');
                                $item_option->id = null;
                            }

                            $item_id        = $product_details->id;
                            $item_type      = $product_details->type;
                            $item_code      = $product_details->code;
                            $item_name      = $product_details->name;
                            $item_net_price = $this->sma->formatDecimal($csv_pr['net_unit_price']);
                            $item_quantity  = $csv_pr['quantity'];
                            $item_tax_rate  = $csv_pr['item_tax_rate'];
                            $item_discount  = $csv_pr['discount'];
                            $item_serial    = $csv_pr['serial'];
                            $item_batchno    = $csv_pr['batch_no'];  

                            if (isset($item_code) && isset($item_net_price) && isset($item_quantity)) {
                                $product_details  = $this->sales_model->getProductByCode($item_code);
                                $pr_discount      = $this->site->calculateDiscount($item_discount, $item_net_price);
                                $item_net_price   = $this->sma->formatDecimal(($item_net_price - $pr_discount), 4);
                                $pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_quantity), 4);
                                $product_discount += $pr_item_discount;

                                $tax         = '';
                                $pr_item_tax = 0;
                                $unit_price  = $item_net_price;
                                $tax_details = ((isset($item_tax_rate) && !empty($item_tax_rate)) ? $this->sales_model->getTaxRateByName($item_tax_rate) : $this->site->getTaxRateByID($product_details->tax_rate));
                                if ($tax_details) {
                                    $ctax     = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                                    $item_tax = $this->sma->formatDecimal($ctax['amount']);
                                    $tax      = $ctax['tax'];
                                    // $this->sma->print_arrays($product_details);
                                    // if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                                    $unit_price = $unit_price + $item_tax;
                                    // }
                                    $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_quantity, 4);
                                    if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($biller_details->state == $customer_details->state), $tax_details)) {
                                        $total_cgst += $gst_data['cgst'];
                                        $total_sgst += $gst_data['sgst'];
                                        $total_igst += $gst_data['igst'];
                                    }
                                }

                                $product_tax += $pr_item_tax;
                                $subtotal = $this->sma->formatDecimal(($unit_price * $item_quantity), 4);
                                $unit     = $this->site->getUnitByID($product_details->unit);

                                $product = [
                                    'product_id'        => $product_details->id,
                                    'product_code'      => $item_code,
                                    'product_name'      => $item_name,
                                    'product_type'      => $item_type,
                                    'option_id'         => $item_option->id,
                                    'net_unit_price'    => $item_net_price,
                                    'quantity'          => $item_quantity,
                                    'product_unit_id'   => $product_details->unit,
                                    'product_unit_code' => $unit->code,
                                    'unit_quantity'     => $item_quantity,
                                    'warehouse_id'      => $warehouse_id,
                                    'item_tax'          => $pr_item_tax,
                                    'tax_rate_id'       => $tax_details ? $tax_details->id : null,
                                    'tax'               => $tax,
                                    'discount'          => $item_discount,
                                    'item_discount'     => $pr_item_discount,
                                    'subtotal'          => $subtotal,
                                    'serial_no'         => $item_serial,
                                    'batch_no'          => $item_batchno,
                                    'unit_price'        => $this->sma->formatDecimal($unit_price, 4),
                                    'real_unit_price'   => $this->sma->formatDecimal(($unit_price + $pr_discount), 4),
                                ];

                                $products[] = ($product + $gst_data);
                                $total += $this->sma->formatDecimal(($item_net_price * $item_quantity), 4);
                            }
                        } else {
                            $this->session->set_flashdata('error', lang('pr_not_found') . ' ( ' . $csv_pr['code'] . ' ). ' . lang('line_no') . ' ' . $rw);
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        $rw++;
                    }
                }
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $data        = ['date'  => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $total,
                'product_discount'  => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $total_tax,
                'shipping'          => $this->sma->formatDecimal($shipping),
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'sale_status'       => $sale_status,
                'payment_status'    => $payment_status,
                'payment_term'      => $payment_term,
                'due_date'          => $due_date,
                'paid'              => 0,
                'created_by'        => $this->session->userdata('user_id'),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($payment_status == 'paid') {
                $payment = [
                    'date'         => $date,
                    'reference_no' => $this->site->getReference('pay'),
                    'amount'       => $grand_total,
                    'paid_by'      => 'cash',
                    'cheque_no'    => '',
                    'cc_no'        => '',
                    'cc_holder'    => '',
                    'cc_month'     => '',
                    'cc_year'      => '',
                    'cc_type'      => '',
                    'created_by'   => $this->session->userdata('user_id'),
                    'note'         => lang('auto_added_for_sale_by_csv') . ' (' . lang('sale_reference_no') . ' ' . $reference . ')',
                    'type'         => 'received',
                ];
            } else {
                $payment = [];
            }

            $attachments        = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            // $this->sma->print_arrays($data, $products, $payment);
        }

        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, [], $attachments)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('sales');
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['slnumber']   = $this->site->getReference('so');

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('add_sale_by_csv')]];
            $meta = ['page_title' => lang('add_sale_by_csv'), 'bc' => $bc];
            $this->page_construct('sales/sale_by_csv', $meta, $this->data);
        }
    }

    public function sell_gift_card()
    {
        $this->sma->checkPermissions('gift_cards', true);
        $error  = null;
        $gcData = $this->input->get('gcdata');
        if (empty($gcData[0])) {
            $error = lang('value') . ' ' . lang('is_required');
        }
        if (empty($gcData[1])) {
            $error = lang('card_no') . ' ' . lang('is_required');
        }

        $customer_details = (!empty($gcData[2])) ? $this->site->getCompanyByID($gcData[2]) : null;
        $customer         = $customer_details ? $customer_details->company : null;
        $data             = ['card_no' => $gcData[0],
            'value'                    => $gcData[1],
            'customer_id'              => (!empty($gcData[2])) ? $gcData[2] : null,
            'customer'                 => $customer,
            'balance'                  => $gcData[1],
            'expiry'                   => (!empty($gcData[3])) ? $this->sma->fsd($gcData[3]) : null,
            'created_by'               => $this->session->userdata('user_id'),
        ];

        if (!$error) {
            if ($this->sales_model->addGiftCard($data)) {
                $this->sma->send_json(['result' => 'success', 'message' => lang('gift_card_added')]);
            }
        } else {
            $this->sma->send_json(['result' => 'failed', 'message' => $error]);
        }
    }

    /* --------------------------------------------------------------------------------------------- */

    public function bch_suggestions($pos = 0)
    {
        $term         = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id  = $this->input->get('customer_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
            //01076123456789001710050310AC3453G3  34
        $analyzed  = $this->sma->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $sr        = addslashes($sr);
        $strict    = $analyzed['strict']                    ?? false;
        $qty       = $strict ? null : $analyzed['quantity'] ?? null;
        $bprice    = $strict ? null : $analyzed['price']    ?? null;

        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        $customer       = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows           = $this->sales_model->getProductNamesWithBatches($sr, $warehouse_id, $pos);
        $count = 0;
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {

                $c = uniqid(mt_rand(), true);
                unset($row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option               = false;
                $row->quantity        = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty             = 1;
                $row->discount        = '0';
                $row->serial          = '';
                // $row->expiry          = '';
                $row->batch_no        = '';
                $row->lot_no          = '';
                $row->actual_prod_price = $row->price;
                $options              = $this->sales_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->sales_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt        = json_decode('{}');
                    $opt->price = 0;
                    $option_id  = false;
                }
                $row->option = $option_id;
                $pis         = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option); 
                if ($pis) {
                    $row->expiry = "";
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                        $row->expiry    = $pi->expiry;
                        $row->serial_number    = $pi->serial_number;
                    }
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
                if ($customer_group->discount && $customer_group->percent < 0) {
                    $row->discount = (0 - $customer_group->percent) . '%';
                } else {
                    $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
                }
                $count++;
                $row->real_unit_price = $row->price;
                $row->base_quantity   = 0;
                $row->base_unit       = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment         = '';
                $row->bonus         = 0;
                $row->dis1         = 0;
                $row->dis2         = 0;
                $combo_items          = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
                }
                if ($qty) {
                    $row->qty           = $qty;
                    $row->base_quantity = $qty;
                } else {
                    $row->qty = ($bprice ? $bprice / $row->price : 0);
                }
                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $row->batch_no = '';
                $row->batchQuantity = 0;
                $row->batchPurchaseCost = 0;
                $row->expiry  = null;
                $row->serial_no = $count;
                $batches = $this->site->getProductBatchesData($row->id, $warehouse_id);
                $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id,
                    'row'     => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'batches'=>$batches];
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function suggestions($pos = 0)
    {
        $term         = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id  = $this->input->get('customer_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
            //01076123456789001710050310AC3453G3  34
        $analyzed  = $this->sma->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $sr        = addslashes($sr);
        $strict    = $analyzed['strict']                    ?? false;
        $qty       = $strict ? null : $analyzed['quantity'] ?? null;
        $bprice    = $strict ? null : $analyzed['price']    ?? null;

        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        $customer       = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows           = $this->sales_model->getProductNames($sr, $warehouse_id, $pos);

        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                unset($row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option               = false;
               // $row->quantity        = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty             = 1;
                $row->discount        = '0';
                $row->serial          = '';
                // $row->expiry          = '';
                $row->batch_no        = '';
                $row->lot_no          = '';

                $options              = $this->sales_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->sales_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt        = json_decode('{}');
                    $opt->price = 0;
                    $option_id  = false;
                }
                $row->option = $option_id;
                $pis         = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);

                
                if ($pis) {
                    $row->expiry = "";
                    //$row->quantity = 0;
                    foreach ($pis as $pi) {
                      //  $row->quantity += $pi->quantity_balance;
                        $row->expiry    = $pi->expiry;
                    }
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
                if ($customer_group->discount && $customer_group->percent < 0) {
                    $row->discount = (0 - $customer_group->percent) . '%';
                } else {
                    $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
                }
                $row->real_unit_price = $row->price;
                $row->base_quantity   = 1;
                $row->base_unit       = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment         = '';
                $row->bonus         = 0;
                $row->dis1         = 0;
                $row->dis2         = 0;
                $combo_items          = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
                }
                if ($qty) {
                    $row->qty           = $qty;
                    $row->base_quantity = $qty;
                } else {
                    $row->qty = ($bprice ? $bprice / $row->price : 1);
                }
                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'category' => $row->category_id,
                    'row'     => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function topup_gift_card($card_id)
    {
        $this->sma->checkPermissions('add_gift_card', true);
        $card = $this->site->getGiftCardByID($card_id);
        $this->form_validation->set_rules('amount', lang('amount'), 'trim|integer|required');

        if ($this->form_validation->run() == true) {
            $data = ['card_id' => $card_id,
                'amount'       => $this->input->post('amount'),
                'date'         => date('Y-m-d H:i:s'),
                'created_by'   => $this->session->userdata('user_id'),
            ];
            $card_data['balance'] = ($this->input->post('amount') + $card->balance);
            // $card_data['value'] = ($this->input->post('amount')+$card->value);
            if ($this->input->post('expiry')) {
                $card_data['expiry'] = $this->sma->fld(trim($this->input->post('expiry')));
            }
        } elseif ($this->input->post('topup')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('sales/gift_cards');
        }

        if ($this->form_validation->run() == true && $this->sales_model->topupGiftCard($data, $card_data)) {
            $this->session->set_flashdata('message', lang('topup_added'));
            admin_redirect('sales/gift_cards');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['modal_js']   = $this->site->modal_js();
            $this->data['card']       = $card;
            $this->data['page_title'] = lang('topup_gift_card');
            $this->load->view($this->theme . 'sales/topup_gift_card', $this->data);
        }
    }

    public function update_status($id)
    {
        $this->sma->checkPermissions('edit', true);
        $this->form_validation->set_rules('status', lang('sale_status'), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note   = $this->sma->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'sales');
        }

        if ($this->form_validation->run() == true && $this->sales_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'sales');
        } else {
            $this->data['inv']      = $this->sales_model->getInvoiceByID($id);
            $this->data['returned'] = false;
            if ($this->data['inv']->sale_status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned'] = true;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'sales/update_status', $this->data);
        }
    }

    public function validate_gift_card($no)
    {
        //$this->sma->checkPermissions();
        if ($gc = $this->site->getGiftCardByNO($no)) {
            if ($gc->expiry) {
                if ($gc->expiry >= date('Y-m-d')) {
                    $this->sma->send_json($gc);
                } else {
                    $this->sma->send_json(false);
                }
            } else {
                $this->sma->send_json($gc);
            }
        } else {
            $this->sma->send_json(false);
        }
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index');
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->sales_model->getInvoiceByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode']     = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['customer']    = $this->site->getCompanyByID($inv->customer_id);
        $this->data['payments']    = $this->sales_model->getPaymentsForSale($id);
        $this->data['biller']      = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by']  = $this->site->getUser($inv->created_by);
        $this->data['updated_by']  = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']   = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']         = $inv;
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['paypal']      = $this->sales_model->getPaypalSettings();
        $this->data['skrill']      = $this->sales_model->getSkrillSettings();
        $this->data['attachments'] = $this->site->getAttachments($id, 'sale');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('sales'), 'page' => lang('sales')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_sales_details'), 'bc' => $bc];
        $this->page_construct('sales/view', $meta, $this->data);
    }

    public function view_delivery($id = null)
    {
        $this->sma->checkPermissions('deliveries');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $deli                = $this->sales_model->getDeliveryByID($id);
        $sale                = $this->sales_model->getInvoiceByID($deli->sale_id);
        if (!$sale) {
            $this->session->set_flashdata('error', lang('sale_not_found'));
            $this->sma->md();
        }
        $this->data['delivery']   = $deli;
        $this->data['biller']     = $this->site->getCompanyByID($sale->biller_id);
        $this->data['rows']       = $this->sales_model->getAllInvoiceItemsWithDetails($deli->sale_id);
        $this->data['user']       = $this->site->getUser($deli->created_by);
        $this->data['page_title'] = lang('delivery_order');

        $this->load->view($this->theme . 'sales/view_delivery', $this->data);
    }

    public function view_gift_card($id = null)
    {
        $this->data['page_title'] = lang('gift_card');
        $gift_card                = $this->site->getGiftCardByID($id);
        $this->data['gift_card']  = $this->site->getGiftCardByID($id);
        $this->data['customer']   = $this->site->getCompanyByID($gift_card->customer_id);
        $this->data['topups']     = $this->sales_model->getAllGCTopups($id);
        $this->load->view($this->theme . 'sales/view_gift_card', $this->data);
    }

    public function qty_onhold_requests($id=null){
      
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
       
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
        $meta = ['page_title' => lang('sales'), 'bc' => $bc];
       //echo 'test';exit;
       
        $this->data['results'] =  $this->sales_model->getQtyOnholdRequests();
        $this->page_construct('sales/qty_onhold_requests', $meta, $this->data);
    } 

    public function delete_qty_onhold($id = null){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if($this->sales_model->delete_onhold_qty($id)){
            $this->session->set_flashdata('message', lang('onhold_request_deleted'));
            admin_redirect('sales/qty_onhold_requests');
        }else{
            $this->session->set_flashdata('error', lang('Could not delete request'));
            admin_redirect('sales/qty_onhold_requests');
        }
    }

}
