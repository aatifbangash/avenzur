<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Returns extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->output->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $url = "admin/login";
            if( $this->input->server('QUERY_STRING') ){
                $url = $url.'?'.$this->input->server('QUERY_STRING').'&redirect='.$this->uri->uri_string();
            }
           
            $this->sma->md($url);
        }
        if ($this->Supplier || $this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('returns', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('returns_model');
        $this->load->admin_model('sales_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }

    public function add_return()
    {
        $this->sma->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');

        if ($this->form_validation->run() == true) {
            $date             = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : date('Y-m-d H:i:s');
            $reference        = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;

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
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details  = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
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

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity'          => $item_quantity,
                        'product_unit_id'   => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity'     => $item_unit_quantity,
                        'warehouse_id'      => $warehouse_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $item_tax_rate,
                        'tax'               => $tax,
                        'discount'          => $item_discount,
                        'item_discount'     => $pr_item_discount,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'real_unit_price'   => $real_unit_price,
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
            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total    = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $data           = [
                'date'              => $date,
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
                'grand_total'       => $grand_total,
                'total_items'       => $total_items,
                'paid'              => 0,
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->returns_model->addReturn($data, $products)) {
            //$return_insert_id = $this->db->insert_id();
            //$this->returns_model->convert_return_invoice($return_insert_id, $products);

            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang('return_added'));
            admin_redirect('returns');
        } else {
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['units']      = $this->site->getAllBaseUnits();

            // $user         = $this->site->getUser();
            // $group_id = $user->group_id;

            $this->db->select('id,name')->from('companies')->where('group_name','supplier');
            $this->data['suppliers'] =$this->db->get()->result();
            
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('returns'), 'page' => lang('returns')], ['link' => '#', 'page' => lang('add_return')]];
            $meta                     = ['page_title' => lang('add_return'), 'bc' => $bc];
            $this->page_construct('returns/add_supplier', $meta, $this->data);
        }
    }

    public function add()
    {
        $this->sma->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');

        if ($this->form_validation->run() == true) {

            // echo "<pre>";
            // print_r($_POST);exit;

            $return_screen     = $this->input->post('return_screen');

            $date             = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : date('Y-m-d H:i:s');
            $reference        = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            //$reference        = $this->input->post('sale_id') ? $this->input->post('sale_id') : '0';
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));
            $shipping         = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $status           = $this->input->post('status') ? $this->input->post('status') : 'pending';

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $total_product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $avz_item_code      = $_POST['avz_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $item_net_cost      = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $item_net_price      = $this->sma->formatDecimal($_POST['net_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_batchno       = $_POST['batch_no'][$r];
                $item_serial_no     = $_POST['serial_no'][$r];
                //$item_expiry        = $_POST['expiry'][$r];
                $item_expiry        = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $item_bonus         = $_POST['bonus'][$r];
                $item_dis1          = $_POST['dis1'][$r];
                $item_dis2          = $_POST['dis2'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['quantity'][$r];
                $net_cost           = $_POST['net_cost'][$r];
                $real_cost          = $_POST['real_cost'][$r];
                $main_net           = $_POST['main_net'][$r];
                
                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details  = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    //$item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
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

                    //echo $real_unit_price * $item_quantity;exit;

                    // NEW: Net unit price calculation
                    $item_net_price   = $this->sma->formatDecimal((($real_unit_price * $item_quantity) - $product_item_discount1 - $product_item_discount2) / ($item_quantity + $item_bonus));
                    //$main_net = $this->sma->formatDecimal((($real_unit_price * $item_quantity) - $product_item_discount1 - $product_item_discount2));

                    $pr_item_tax = $item_tax = 0;
                    $tax         = ''; 

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
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
                    $subtotal = $main_net;
                    $unit     = $this->site->getUnitByID($item_unit);

                    /**
                     * POST FIELDS
                    */
                    $new_item_first_discount = $_POST['item_first_discount'][$r];
                    $new_item_second_discount = $_POST['item_second_discount'][$r];
                    $new_item_vat_value = $_POST['item_vat_values'][$r];
                    $new_item_total_sale = $_POST['item_total_sale'][$r];
                    $totalbeforevat = $_POST['totalbeforevat'][$r];
                    $new_item_main_net = $_POST['main_net'][$r];

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_cost'          => $net_cost,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->sma->formatDecimal($item_net_price),
                        'quantity'          => ($item_quantity + $item_bonus),
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
                        'expiry'            => $item_expiry,
                        'batch_no'          => $item_batchno,
                        'serial_number'     => $item_serial_no,
                        'real_unit_price'   => $real_unit_price,
                        'bonus'             => $item_bonus,
                        //'bonus'             => 0,
                        'discount1'         => $item_dis1,
                        'discount2'         => $item_dis2,
                        'second_discount_value' => $new_item_second_discount,
                        'totalbeforevat'    => $totalbeforevat,
                        'main_net'          => $new_item_main_net,
                        'real_cost'         => $real_cost,
                        'avz_item_code'     => $avz_item_code
                    ];

                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal(($subtotal), 4);
                }


                /* Code for payment to customer */

                $sl_inv = $this->sales_model->get_sale_by_avzcode($avz_item_code);
                $return = [
                    'sale_id' => $sl_inv->sale_id,
                    'amount' => ($totalbeforevat + $new_item_vat_value)
                ];
                $returns[] = $return;

                /* Code for payment to customer END */
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }
            
            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            
            //Discount calculation
            // total discount must be deducted from  grandtotal
            //$grand_total    = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            
            $grand_total    = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping)), 4);
            
            //Discount calculation

            /**
             * post values
             */
            
             $grand_total_net_return = $this->input->post('grand_total_net_sale');
             $grand_total_discount = $this->input->post('grand_total_discount');
             $grand_total_vat = $this->input->post('grand_total_vat');
             $grand_total_return = $this->input->post('grand_total_sale');
             $grand_total = $this->input->post('grand_total');
             $cost_goods_sold = $this->input->post('cost_goods_sold');

            $data           = [
                'date'              => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $grand_total_return,
                'total_net_return'  => $grand_total_net_return,
                'product_discount'  => $total_product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $grand_total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $grand_total_vat,
                'grand_total'       => $grand_total,
                'cost_goods_sold'   => $cost_goods_sold,
                'total_items'       => $total_items,
                'paid'              => 0,
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
                'status'            => $status,
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            //$this->sma->print_arrays($data, $products, $returns);exit;
        }

        if ($this->form_validation->run() == true && $return_insert_id = $this->returns_model->addReturn($data, $products)) {

            //$this->returns_model->convert_return_invoice($return_insert_id, $products);
            if($data['status'] == "completed"){
                $this->convert_return_invoice($return_insert_id);
                $this->payment_to_customer($returns, $return_insert_id);  
            }

            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang('return_added'));
            admin_redirect('returns?lastInsertedId='.$return_insert_id);
        } else {

            if(isset($_GET['sale']) && !empty($_GET['sale'])){
                $sale = $this->sales_model->getSaleByID($_GET['sale']);
                $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['inv'] = $sale;
                $inv_items = $this->sales_model->getAllReturnInvoiceItems($_GET['sale'], $sale->customer_id);
                
                $c = rand(100000, 9999999);
                foreach ($inv_items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    $row->batch_no = $item->batch_no;
                    $row->bonus = -1*($item->total_bonus);
                    $row->obonus = -1*($item->total_bonus);
                    $row->avz_item_code = $item->avz_item_code;
                    $row->discount1 = $item->discount1;
                    $row->discount2 = $item->discount2;
                    
                    $row->net_unit_cost = $item->net_cost;
                    $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                    $row->base_quantity = -1*($item->total_quantity);
                    
                    $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                    $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                    $row->unit = $item->product_unit_id;
                    //$row->qty = $item->unit_quantity - $row->bonus;
                    //$row->oqty = $item->unit_quantity - $row->bonus;

                    //$row->qty = $row->base_quantity - $row->bonus;
                    $row->qty = $item->quantity;
                    $row->oqty = $row->base_quantity - $row->bonus;
                    
                    $row->sale_item_id = $item->id;
                    $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                    $row->received = $row->received - $row->bonus;
                    $row->quantity_balance = $item->quantity_balance + ($item->quantity - $row->received);
                    $row->discount = $item->item_discount ? $item->item_discount : '0';
                    //$options = $this->purchases_model->getProductOptions($row->id);
                    //$row->option = !empty($item->option_id) ? $item->option_id : '';
                    $row->real_unit_cost = $item->real_cost;
                    //$row->cost             = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                    $row->cost = $this->sma->formatDecimal($item->net_cost);
                    $row->sale_price = $this->sma->formatDecimal($item->real_unit_price);
                    $row->real_unit_sale = $row->sale_price;
                    $row->net_unit_sale = $this->sma->formatDecimal($item->net_unit_price);
                    $row->tax_rate = $item->tax_rate_id;
                    $row->main_net = $item->main_net;
                    unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                    $units = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                    $ri = $this->Settings->item_addition ? $row->id : $c;
    
                    $options = false;
                    $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options];
    
                    $c++;
                }
                $this->data['inv_items'] = json_encode($pr);
                $this->data['id'] = $_GET['sale'];
                $this->data['reference'] = $_GET['sale'];
                $this->data['billers']    = $this->site->getAllCompanies('biller');
                $this->data['warehouses'] = $this->site->getMainWarehouse();
                $this->data['tax_rates'] = $this->site->getAllTaxRates();
                $this->data['units']      = $this->site->getAllBaseUnits();
                $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('returns'), 'page' => lang('returns')], ['link' => '#', 'page' => lang('add_return')]];
                $meta                     = ['page_title' => lang('add_return'), 'bc' => $bc];
                $this->page_construct('returns/add', $meta, $this->data);

            }else{
                $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
                $this->data['billers']    = $this->site->getAllCompanies('biller');
                //$this->data['warehouses'] = $this->site->getAllWarehouses();
                $this->data['warehouses'] = $this->site->getMainWarehouse();
                $this->data['tax_rates']  = $this->site->getAllTaxRates();
                $this->data['units']      = $this->site->getAllBaseUnits();
                $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('returns'), 'page' => lang('returns')], ['link' => '#', 'page' => lang('add_return')]];
                $meta                     = ['page_title' => lang('add_return'), 'bc' => $bc];
                $this->page_construct('returns/add', $meta, $this->data);
            }
        }
    }

    public function payment_to_customer($returns, $return_insert_id){

        $result = [];
        foreach ($returns as $item) {
            $sale_id = $item['sale_id'];
            $amount = $item['amount'];

            if($sale_id){
                if (isset($result[$sale_id])) {
                    $result[$sale_id]['amount'] += $amount;
                } else {
                    $result[$sale_id] = [
                        'sale_id' => $sale_id,
                        'amount' => $amount,
                    ];
                }
            }
        }


        $net_amount = 0;
        if($sale_id){
            $result = array_values($result);
            foreach($result as $return){
                $net_amount += $return['amount'];
                $this->sales_model->update_sale_paid_amount($return['sale_id'], $return['amount']);
            }
        }else if($return_insert_id){
            foreach ($returns as $retitem) {
                $net_amount += $retitem['amount'];
            }
        }
        

        $payment = [
            'date'          => date('Y-m-d h:i:s'),
            'sale_id'       => $sale_id,
            'return_id'     => $return_insert_id,
            'reference_no'  => '',
            'amount'        => $net_amount,
            'note'          => 'Return By Customer',
            'created_by'    => $this->session->userdata('user_id'),
            'type'          => 'sent',
            'payment_id'    => NULL
        ];

        $this->sales_model->addPayment($payment);
    }

    public function convert_return_invoice($rid){
        $inv = $this->returns_model->getReturnByID($rid);
        
        $this->load->admin_model('companies_model');
        $customer = $this->companies_model->getCompanyByID($inv->customer_id);
        $warehouse_id = $inv->warehouse_id;
        $warehouse_ledgers = $this->site->getWarehouseByID($warehouse_id);

        /*Accounts Entries*/
        $entry = array(
        'entrytype_id' => 4,
        'number'       => 'RCO-'.$inv->reference_no,
        'date'         => date('Y-m-d'), 
        'dr_total'     => $inv->grand_total,
        'cr_total'     => $inv->grand_total,
        'notes'        => 'RCO Reference: '.$inv->reference_no.' Date: '.date('Y-m-d H:i:s'),
        'rid'          =>  $inv->id,
        'transaction_type'   =>  'returncustomerorder',
        'customer_id' => $inv->customer_id
        );
        
        $add  = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();
        
        //$insert_id = 999;
        $entryitemdata = array();

        $inv_items = $this->returns_model->getReturnItems($rid);

        $totalSalePrice = 0;
        $totalPurchasePrice = 0;
        foreach ($inv_items as $item) 
        {
            $proid = $item->product_id;
            $product  = $this->site->getProductByID($proid);

            $totalSalePrice = ($totalSalePrice)+($item->net_unit_price * $item->quantity);
            $totalPurchasePrice = $totalPurchasePrice + ($item->net_cost * $item->quantity);
        }

        $amount_to_pay = $totalSalePrice + $inv->total_tax - $inv->total_discount;

        //echo '<pre>';print_r($warehouse_ledgers);exit;

        if($warehouse_ledgers->warehouse_type == 'pharmacy'){
            // cost of goods sold
            $entryitemdata[] = array(
                'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $warehouse_ledgers->cogs_ledger,
                'amount' =>  $inv->cost_goods_sold,
                'narration' => 'cost of goods sold'
                )
            );
        }else{
            // cost of goods sold
            $entryitemdata[] = array(
                'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $customer->cogs_ledger,
                'amount' =>  $inv->cost_goods_sold,
                'narration' => 'cost of goods sold'
                )
            );
        }
            

              // inventory
        $entryitemdata[] = array(
            'Entryitem' => array(
            'entry_id' => $insert_id,
            'dc' => 'D',
            'ledger_id' => $warehouse_ledgers->inventory_ledger,
            'amount' => $inv->cost_goods_sold,
            'narration' => 'inventory'
            )
        );


        if($warehouse_ledgers->warehouse_type == 'pharmacy'){
            // //discount
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'C',
                    'ledger_id' => $warehouse_ledgers->discount_ledger,
                    'amount' => $inv->total_discount,
                    'narration' => 'discount'
                )
            );
        }else{
            // //discount
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'C',
                    'ledger_id' => $customer->discount_ledger,
                    'amount' => $inv->total_discount,
                    'narration' => 'discount'
                )
            );
        }
        

        if($warehouse_ledgers->warehouse_type == 'pharmacy'){
            // //cash
            $entryitemdata[] = array(
                'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $warehouse_ledgers->fund_books_ledger,
                //'amount' =>(($totalSalePrice + $inv->order_tax) - $inv->total_discount),
                'amount' => $inv->grand_total,
                'narration' => 'customer'
                )
            );
        }else{
            // //cash
            $entryitemdata[] = array(
                'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $customer->ledger_account,
                //'amount' =>(($totalSalePrice + $inv->order_tax) - $inv->total_discount),
                'amount' => $inv->grand_total,
                'narration' => 'customer'
                )
            );
        }

    
        if($warehouse_ledgers->warehouse_type == 'pharmacy'){
            // // sale account
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $warehouse_ledgers->sales_ledger,
                    'amount' => $inv->total,
                    'narration' => 'sale account'
                )
            );
        }else{
            // // sale account
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $customer->sales_ledger,
                    'amount' => $inv->total,
                    'narration' => 'sale account'
                )
            );
        }
      
        if($warehouse_ledgers->warehouse_type == 'pharmacy'){
            // //vat on sale
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $warehouse_ledgers->vat_on_sales_ledger,
                    'amount' => $inv->total_tax,
                    'narration' => 'vat on sale'
                )
            );
        }else{
            // //vat on sale
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $this->vat_on_sale,
                    'amount' => $inv->total_tax,
                    'narration' => 'vat on sale'
                )
            );
        }      
        
        $total_invoice_entry = $inv->total_tax + $totalSalePrice + $totalPurchasePrice;
        

        $this->db->update('sma_accounts_entries', ['dr_total' => $total_invoice_entry, 'cr_total' => $total_invoice_entry], ['id' => $insert_id]);
                
        //   /*Accounts Entry Items*/
        foreach ($entryitemdata as $row => $itemdata)
        {
                $this->db->insert('sma_accounts_entryitems', $itemdata['Entryitem']);
        }

    }

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->returns_model->deleteReturn($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 0, 'msg' => lang('return_deleted')]);
            }
            $this->session->set_flashdata('message', lang('return_deleted'));
            admin_redirect('welcome');
        }
    }

    public function delete_previous_entry($id){
        $accouting_entry = $this->returns_model->getAccoutsEntryByID($id);

        $this->db->delete('sma_accounts_entryitems', ['entry_id' => $accouting_entry->id]);
        $this->db->delete('sma_accounts_entries', ['rid' => $id]);
    }

    public function edit($id = null)
    {
        $this->sma->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->returns_model->getReturnByID($id);

        if($inv->status == 'completed'){
            $this->session->set_flashdata('error', 'Cannot edit completed returns');
            admin_redirect('returns');
        }

        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');

        if ($this->form_validation->run() == true) {
            $date             = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : $inv->date;
            $reference        = $this->input->post('reference_no');
            $warehouse_id     = $this->input->post('warehouse');
            $customer_id      = $this->input->post('customer');
            $biller_id        = $this->input->post('biller');
            $total_items      = $this->input->post('total_items');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer         = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details   = $this->site->getCompanyByID($biller_id);
            $biller           = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note             = $this->sma->clear_tags($this->input->post('note'));
            $staff_note       = $this->sma->clear_tags($this->input->post('staff_note'));
            $shipping         = $this->input->post('shipping');
            $status           = $this->input->post('status') ? $this->input->post('status') : 'pending';

            $total            = 0;
            $product_tax      = 0;
            $product_discount = 0;
            $total_product_discount = 0;
            $gst_data         = [];
            $total_cgst       = $total_sgst       = $total_igst       = 0;
            $i                = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $avz_item_code      = $_POST['avz_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $item_net_cost      = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $item_net_price      = $this->sma->formatDecimal($_POST['net_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_batchno       = $_POST['batch_no'][$r];
                $item_serial_no     = $_POST['serial_no'][$r];
                //$item_expiry        = $_POST['expiry'][$r];
                $item_expiry        = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $item_bonus         = $_POST['bonus'][$r];
                $item_dis1          = $_POST['dis1'][$r];
                $item_dis2          = $_POST['dis2'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['quantity'][$r];
                $net_cost           = $_POST['net_cost'][$r];
                $real_cost          = $_POST['real_cost'][$r];
                $main_net           = $_POST['main_net'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details  = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
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

                    // NEW: Net unit price calculation
                    $item_net_price   = $this->sma->formatDecimal((($real_unit_price * $item_quantity) - $product_item_discount1 - $product_item_discount2) / ($item_quantity + $item_bonus));

                    $pr_item_tax = $item_tax = 0;
                    $tax         = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
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
                    $subtotal = $main_net;
                    $unit     = $this->site->getUnitByID($item_unit);

                    /**
                     * POST FIELDS
                    */
                    $new_item_first_discount = $_POST['item_first_discount'][$r];
                    $new_item_second_discount = $_POST['item_second_discount'][$r];
                    $new_item_vat_value = $_POST['item_vat_values'][$r];
                    $new_item_total_sale = $_POST['item_total_sale'][$r];
                    $totalbeforevat = $_POST['totalbeforevat'][$r];
                    $new_item_main_net = $_POST['main_net'][$r];

                    $product = [
                        'return_id'         => $id,
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_cost'          => $net_cost,
                        'net_unit_price'    => $item_net_price,
                        'unit_price'        => $this->sma->formatDecimal($item_net_price),
                        'quantity'          => ($item_quantity + $item_bonus),
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
                        'expiry'            => $item_expiry,
                        'batch_no'          => $item_batchno,
                        'serial_number'     => $item_serial_no,
                        'real_unit_price'   => $real_unit_price,
                        'bonus'             => $item_bonus,
                        //'bonus'             => 0,
                        'discount1'         => $item_dis1,
                        'discount2'         => $item_dis2,
                        'second_discount_value' => $new_item_second_discount,
                        'totalbeforevat'    => $totalbeforevat,
                        'main_net'          => $new_item_main_net,
                        'real_cost'         => $real_cost,
                        'avz_item_code'     => $avz_item_code
                    ];

                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal(($subtotal), 4);
                }

                /* Code for payment to customer */

                $sl_inv = $this->sales_model->get_sale_by_avzcode($avz_item_code);
                $return = [
                    'sale_id' => $sl_inv->sale_id,
                    'amount' => ($totalbeforevat + $new_item_vat_value)
                ];
                $returns[] = $return;

                /* Code for payment to customer END */
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax      = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            
            //Discount calculation
            // total discount must be deducted from  grandtotal
            //$grand_total    = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            
            $grand_total    = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping)), 4);
            //Discount calculation

            /**
             * post values
             */
            
             $grand_total_net_return = $this->input->post('grand_total_net_sale');
             $grand_total_discount = $this->input->post('grand_total_discount');
             $grand_total_vat = $this->input->post('grand_total_vat');
             $grand_total_return = $this->input->post('grand_total_sale');
             $grand_total = $this->input->post('grand_total');
             $cost_goods_sold = $this->input->post('cost_goods_sold');
            
             $data           = [
                'date'              => $date,
                'reference_no'      => $reference,
                'customer_id'       => $customer_id,
                'customer'          => $customer,
                'biller_id'         => $biller_id,
                'biller'            => $biller,
                'warehouse_id'      => $warehouse_id,
                'note'              => $note,
                'staff_note'        => $staff_note,
                'total'             => $grand_total_return,
                'total_net_return'  => $grand_total_net_return,
                'product_discount'  => $total_product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $grand_total_discount,
                'product_tax'       => $product_tax,
                'order_tax_id'      => $this->input->post('order_tax'),
                'order_tax'         => $order_tax,
                'total_tax'         => $grand_total_vat,
                'grand_total'       => $grand_total,
                'cost_goods_sold'   => $cost_goods_sold,
                'total_items'       => $total_items,
                'paid'              => 0,
                'created_by'        => $this->session->userdata('user_id'),
                'hash'              => hash('sha256', microtime() . mt_rand()),
                'status'            => $status,
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

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
                $photo              = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->returns_model->updateReturn($id, $data, $products)) {

            if($data['status'] == "completed"){
                $this->convert_return_invoice($id);
                $this->payment_to_customer($returns, $id);  
            }

            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang('return_added'));
            admin_redirect('returns?lastInsertedId='.$id);
            
            //$this->delete_previous_entry($id);
            
        } else {
            $this->data['inv'] = $inv;
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang('return_x_edited_older_than_x_days'), $this->Settings->disable_editing));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $inv_items = $this->returns_model->getReturnItemsNew($id, $inv->customer_id);
            $c         = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $row->batch_no = $item->batch_no;
                $row->bonus = -1*($item->total_bonus);
                $row->obonus = -1*($item->total_bonus);
                $row->avz_item_code = $item->avz_item_code;
                $row->discount1 = $item->discount1;
                $row->discount2 = $item->discount2;
                
                $row->net_unit_cost = $item->net_cost;
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->base_quantity = abs($item->total_quantity);
                
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                //$row->qty = $item->unit_quantity - $row->bonus;
                //$row->oqty = $item->unit_quantity - $row->bonus;

                //$row->qty = $row->base_quantity - $row->bonus;
                $row->qty = $item->quantity;
                $row->oqty = $row->base_quantity - $row->bonus;
                
                $row->sale_item_id = $item->id;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->received = $row->received - $row->bonus;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity - $row->received);
                $row->discount = $item->item_discount ? $item->item_discount : '0';
                //$options = $this->purchases_model->getProductOptions($row->id);
                //$row->option = !empty($item->option_id) ? $item->option_id : '';
                $row->real_unit_cost = $item->real_cost;
                //$row->cost             = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->cost = $this->sma->formatDecimal($item->net_cost);
                $row->sale_price = $this->sma->formatDecimal($item->real_unit_price);
                $row->real_unit_sale = $row->sale_price;
                $row->net_unit_sale = $this->sma->formatDecimal($item->net_unit_price);
                $row->tax_rate = $item->tax_rate_id;
                $row->main_net = $item->main_net;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $options = false;
                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options];

                $c++;
            }
            $this->data['inv_items']  = json_encode($pr);
            $this->data['id']         = $id;
            $this->data['error']      = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers']    = $this->site->getAllCompanies('biller');
            //$this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouses'] = $this->site->getMainWarehouse();
            $this->data['tax_rates']  = $this->site->getAllTaxRates();
            $this->data['units']      = $this->site->getAllBaseUnits();
            $bc                       = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('returns'), 'page' => lang('returns')], ['link' => '#', 'page' => lang('edit_return')]];
            $meta                     = ['page_title' => lang('edit_return'), 'bc' => $bc];
            $this->page_construct('returns/edit', $meta, $this->data);
        }
    }

    public function getReturns($warehouse_id = null)
    {
       
         $this->sma->checkPermissions('index');
         if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user         = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
         }

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("{$this->db->dbprefix('returns')}.id as id, DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no, biller, {$this->db->dbprefix('returns')}.customer, grand_total, status, {$this->db->dbprefix('returns')}.attachment")
                ->from('returns')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("{$this->db->dbprefix('returns')}.id as id, DATE_FORMAT({$this->db->dbprefix('returns')}.date, '%Y-%m-%d %T') as date, reference_no, biller, {$this->db->dbprefix('returns')}.customer, grand_total, status, {$this->db->dbprefix('returns')}.attachment")
                ->from('returns');
        }

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
       
        $edit_link         = anchor('admin/returns/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_return'), 'class="tip"');
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_return') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po po-delete' href='" . admin_url('returns/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_return') . '</a>';
        $journal_entry_link      = anchor('admin/entries/view/journal/?rid=$1', '<i class="fa fa-eye"></i> ' . lang('Journal Entry'));
        
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
                
                <li>' . $journal_entry_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $delete_link . '</li> 
        </ul>
    </div></div>';
         
    $this->datatables->add_column('Actions', $action, 'id');
       // $this->datatables->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('returns/edit/$1') . "' class='tip' title='" . lang('edit_return') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_return') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('returns/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        echo $this->datatables->generate();
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
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('returns')]];
        $meta = ['page_title' => lang('returns'), 'bc' => $bc];
        $this->page_construct('returns/index', $meta, $this->data);
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed  = $this->sma->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $sr        = addslashes($sr);
        $strict    = $analyzed['strict']                    ?? false;
        $qty       = $strict ? null : $analyzed['quantity'] ?? null;
        $bprice    = $strict ? null : $analyzed['price']    ?? null;

        $rows = $this->returns_model->getProductNames($sr);

        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c      = uniqid(mt_rand(), true);
                $option = false;
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $row->item_tax_method = $row->tax_method;
                $options              = $this->returns_model->getProductOptions($row->id);

                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->returns_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt        = json_decode('{}');
                    $opt->price = 0;
                    $opt->cost = 0;
                    $option_id  = false;
                }

                $sold                   = $this->returns_model->getProductsSold($row->id);
                $row->net_cost          = $sold->cost;
                
                 $row->discount1         = 0;
                 $row->discount2         = 0;

                $row->option = $option_id;
                if ($row->promotion) {
                    $row->price = $row->promo_price;
                }


                
                 $row->cost_price = $opt->cost;
               

                // $row->cost       = $row->cost;

                $row->base_quantity   = 1;
                $row->base_unit       = $row->unit;
                $row->real_unit_price = $row->price;
                $row->base_unit_price = $row->price;
                $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->qty             = 1;
                $row->discount        = '0';
                $row->serial          = '';
                $row->comment         = '';
                $row->batch_no        = '';
                $row->serial_number   = '';
                $row->expiry          = '';
                $row->bonus           = '0';
                $row->dis1            = 0;
                $row->dis2            = 0;


                $combo_items          = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($row->id);
                }
                $row->qty = $qty ? $qty : ($bprice ? $bprice / $row->price : 1);
                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                // $row->batch_no = $row->batchno;
                $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'     => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    private function extract_gs1_data($input)
    {
        $data = [
            'gtin' => null,
            'batch_number' => null,
            'expiry_date' => null,
        ];
    
        // Extract GTIN (14 digits after (01))
        if (preg_match('/\(01\)(\d{14})/', $input, $matches)) {
            $data['gtin'] = $matches[1];
        }
    
        // Extract Batch Number (variable length after (10), stops at next AI)
        if (preg_match('/\(10\)([^\(]+)/', $input, $matches)) {
            $data['batch_number'] = $matches[1];
        }
    
        // Extract Expiry Date (YYMMDD format after (17))
        if (preg_match('/\(17\)(\d{6})/', $input, $matches)) {
            $expiry_raw = $matches[1]; // "270228"
    
            // Convert YYMMDD to YYYY-MM-DD
            $year = substr($expiry_raw, 0, 2); // "27"
            $month = substr($expiry_raw, 2, 2); // "02"
            $day = substr($expiry_raw, 4, 2); // "28"
    
            // Assume year is in 2000s if below 50, otherwise in 1900s
            $year = ($year < 50) ? '20' . $year : '19' . $year;
    
            $data['expiry_date'] = "$day/$month/$year"; // "2027-02-28"
        }
    
        return $data;
    }

    public function bch_suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $extracted_data = $this->extract_gs1_data($term);
        $search_term = $extracted_data['gtin'] ?? $term;
        $batch_number = $extracted_data['batch_number'] ?? null;
        $expiry_date = $extracted_data['expiry_date'] ?? null;

        $analyzed  = $this->sma->analyze_term($search_term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $sr        = addslashes($sr);
        $strict    = $analyzed['strict']                    ?? false;
        $qty       = $strict ? null : $analyzed['quantity'] ?? null;
        $bprice    = $strict ? null : $analyzed['price']    ?? null;

        $rows = $this->returns_model->getProductNamesWithBatches($sr, $warehouse_id, $pos);

        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c      = uniqid(mt_rand(), true);
                $option = false;
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $row->item_tax_method = $row->tax_method;
                $options              = $this->returns_model->getProductOptions($row->id);

                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->returns_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt        = json_decode('{}');
                    $opt->price = 0;
                    $opt->cost = 0;
                    $option_id  = false;
                }

                $sold                   = $this->returns_model->getProductsSold($row->id);
                $row->net_cost          = $sold->cost;
                
                 $row->discount1         = 0;
                 $row->discount2         = 0;

                $row->option = $option_id;
                if ($row->promotion) {
                    //$row->price = $row->promo_price;
                }


                
                 $row->cost_price = $opt->cost;
               

                // $row->cost       = $row->cost;

                $row->base_quantity   = 0;
                $row->base_unit       = $row->unit;
                $row->real_unit_price = $row->price;
                $row->base_unit_price = $row->price;
                $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->qty             = 0;
                $row->discount        = '0';
                $row->serial          = '';
                $row->comment         = '';
                $row->batch_no        = '';
                $row->serial_number   = '';
                $row->expiry          = '';
                $row->bonus           = '0';
                $row->dis1            = 0;
                $row->dis2            = 0;


                $combo_items          = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($row->id);
                }
                $row->qty = $qty ? $qty : ($bprice ? $bprice / $row->price : 0);
                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                // $row->batch_no = $row->batchno;
                $row->batch_no = '';
                $row->batchQuantity = 0;
                $row->batchPurchaseCost = 0;
                $row->expiry  = null;
                
                $batches = $this->site->getProductBatchesData($row->id, $warehouse_id);

                $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'     => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'batches'=>$batches];
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function modal_view($id = null)
    {
        $this->sma->checkPermissions('index', true);
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->returns_model->getReturnByID($id);
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
        $this->data['rows']        = $this->returns_model->getReturnItems($id);
        //$this->data['return_sale'] = $inv->return_id ? $this->returns_model->getInvoiceByID($inv->return_id) : null;
        //$this->data['return_rows'] = $inv->return_id ? $this->returns_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['attachments'] = $this->site->getAttachments($id, 'return');
        $this->data['return_id'] = $id;

        $this->load->view($this->theme . 'returns/modal_view', $this->data);
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index', true);
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv                 = $this->returns_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['customer']   = $this->site->getCompanyByID($inv->customer_id);
        $this->data['biller']     = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse']  = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv']        = $inv;
        $this->data['rows']       = $this->returns_model->getReturnItems($id);

        $this->load->view($this->theme . 'returns/view', $this->data);
    }
}
