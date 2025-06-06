<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Shop extends MY_Shop_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->Settings->mmode) {
            redirect('notify/offline');
        }
        $this->load->library('form_validation');
        if ($this->shop_settings->private && !$this->loggedIn) {
            redirect('/login');
        }
        // echo "<pre>";
        // print_r($this->session);
    }

    // Add/edit customer address
    public function address($id = null)
    {
        $this->load->admin_model('companies_model');
        if (!$this->loggedIn) {
            $this->sma->send_json(['status' => 'error', 'message' => lang('please_login')]);
        }
        $this->form_validation->set_rules('line1', lang('line1'), 'trim|required');
        // $this->form_validation->set_rules('line2', lang("line2"), 'trim|required');
        $this->form_validation->set_rules('city', lang('city'), 'trim|required');
        //        $this->form_validation->set_rules('state', lang('state'), 'trim|required');
        // $this->form_validation->set_rules('postal_code', lang("postal_code"), 'trim|required');
        $this->form_validation->set_rules('country', lang('country'), 'trim|required');
        //        $this->form_validation->set_rules('phone', lang('phone'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $user_addresses = $this->shop_model->getAddresses();

            $data = ['line1' => $this->input->post('line1'),
                'line2' => $this->input->post('line2'),
                'phone' => $this->companies_model->getCompanyByEmail($this->session->userdata('email'))->phone, //$this->input->post('phone'),
                'city' => $this->input->post('city'),
                'state' => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country' => $this->input->post('country'),
                'latitude' => $this->input->post('latitude'),
                'longitude' => $this->input->post('longitude'),
                'company_id' => $this->session->userdata('company_id'),];

            if ($id) {
                $this->db->update('addresses', $data, ['id' => $id]);
                $this->session->set_flashdata('message', lang('address_updated'));
                $this->sma->send_json(['redirect' => $_SERVER['HTTP_REFERER']]);
            } else {
                if (count($user_addresses) >= 6) {
                    $this->sma->send_json(['status' => 'error', 'message' => lang('already_have_max_addresses'), 'level' => 'error']);
                }
                $this->db->insert('addresses', $data);
                $this->session->set_flashdata('message', lang('address_added'));
                $this->sma->send_json(['redirect' => $_SERVER['HTTP_REFERER']]);
            }
        } elseif ($this->input->is_ajax_request()) {
            $this->sma->send_json(['status' => 'error', 'message' => validation_errors()]);
        } else {
            shop_redirect('shop/addresses');
        }
    }

    public function saveCheckoutAddress()
    {
        $this->load->admin_model('companies_model');
        if (!$this->loggedIn) {
            $this->sma->send_json(['status' => 'error', 'message' => lang('please_login')]);
        }
        $this->form_validation->set_rules('longitude', lang('longitude'), 'trim|required');
        // $this->form_validation->set_rules('line2', lang("line2"), 'trim|required');
        $this->form_validation->set_rules('latitude', lang('latitude'), 'trim|required');
        $this->form_validation->set_rules('state', lang('state'), 'trim|required');
        $this->form_validation->set_rules('city', lang("city"), 'trim|required');
        $this->form_validation->set_rules('address_line_1', lang('address_line_1'), 'trim|required');
        $this->form_validation->set_rules('country', lang('country'), 'trim|required');
        $this->form_validation->set_rules('mobile_number', lang('mobile_number'), 'trim|required');
        $this->form_validation->set_rules('first_name', lang('first_name'), 'trim|required');
        $this->form_validation->set_rules('last_name', lang('last_name'), 'trim|required');
        if($this->input->post('email')){
            $this->form_validation->set_rules('email', lang('email'), 'trim|required');
        }
    
        if ($this->form_validation->run() == true) {
            // update address
            $action_type_id = $this->input->post('action_type_id');
            $verify_phone_numbers = $this->shop_model->getCustomerVerifiedNumbers();
                // insert default address
                $default_address = $this->shop_model->getDefaultChechoutAddress();
                if( in_array($this->input->post('mobile_number') , $verify_phone_numbers) ) {
                    $mobile_verified = 1;
                }else {
                    $mobile_verified = $this->input->post('opt_verified');
                }
               
                if ($default_address->phone == '' || $default_address->address == '' || $action_type_id == 'default') {
                    $data = ['address' => $this->input->post('address_line_1'),
                        'line2' => $this->input->post('address_line_2'),
                        'city' => $this->input->post('city'),
                        'state' => $this->input->post('state'),
                        'phone' => $this->input->post('mobile_number'),
                        'country' => $this->input->post('country'),
                        'latitude' => $this->input->post('latitude'),
                        'longitude' => $this->input->post('longitude'),
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'mobile_verified' => $mobile_verified
                    ];
                    if($this->input->post('current_mobile_number') != $this->input->post('mobile_number')){ 
                        // verify mobile number
                    }
                    if($this->input->post('email') != '') {
                        $data['email'] = $this->input->post('email');
                        $this->db->update('users', ['email'=>$data['email']], ['company_id' => $this->session->userdata('company_id')]);
                    }

                    $this->db->update('companies', $data, ['id' => $this->session->userdata('company_id')]);
                    
                    redirect('cart/checkout');
                } else if($action_type_id != '' && is_numeric($action_type_id) ) {
                  
                    $data = ['line1' => $this->input->post('address_line_1'),
                        'line2' => $this->input->post('address_line_2'),
                        'city' => $this->input->post('city'),
                        'state' => $this->input->post('state'),
                        'phone' => $this->input->post('mobile_number'),
                        'country' => $this->input->post('country'),
                        'latitude' => $this->input->post('latitude'),
                        'longitude' => $this->input->post('longitude'),
                        'company_id' => $this->session->userdata('company_id'),
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'mobile_verified' => $mobile_verified
                    ];
                    $this->db->update('addresses', $data, ['id' => $action_type_id]);
                    redirect('cart/checkout?action=changeaddress');
                } else {
                    $user_addresses = $this->shop_model->getAddresses();
                    $data = ['line1' => $this->input->post('address_line_1'),
                        'line2' => $this->input->post('address_line_2'),
                        'city' => $this->input->post('city'),
                        'state' => $this->input->post('state'),
                        'phone' => $this->input->post('mobile_number'),
                        'country' => $this->input->post('country'),
                        'latitude' => $this->input->post('latitude'),
                        'longitude' => $this->input->post('longitude'),
                        'company_id' => $this->session->userdata('company_id'),
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                        'mobile_verified' => $mobile_verified
                    ];
                    if (count($user_addresses) >= 6) {
                        $this->session->set_flashdata('error', lang('already_have_max_addresses'));
                        redirect('shop/addresses');
                        // $this->sma->send_json(['status' => 'error', 'message' => lang('already_have_max_addresses'), 'level' => 'error']);
                    }
                    
                    $this->db->insert('addresses', $data);
                    //$this->session->set_flashdata('message', lang('address_added'));
                    //$this->sma->send_json(['redirect' => $_SERVER['HTTP_REFERER']]);
                    redirect('cart/checkout?action=changeaddress');
                }
            


        }else{
            redirect('cart/checkout');
        }


    }

    public function deleteDeliveryAddress() {
        if (!$this->loggedIn) {
            $this->sma->send_json(['status' => 'error', 'message' => lang('please_login')]);
        }
        $this->form_validation->set_rules('addressId', lang('addressId'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $addressId = $this->input->post('addressId');
            $this->db->delete('addresses', ['id' => $addressId, 'company_id' => $this->session->userdata('company_id')]);
            redirect('cart/checkout?action=changeaddress');
        }
    }

    // Customer address list
    public function addresses()
    {
        if (!$this->loggedIn) {
            redirect('login');
        }
        if ($this->Staff) {
            admin_redirect('customers');
        }
        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['addresses'] = $this->shop_model->getAddresses();
        $this->data['page_title'] = lang('my_addresses');
        $this->data['page_desc'] = '';
        $this->data['all_categories'] = $this->shop_model->getAllCategories();
        $this->page_construct('pages/addresses', $this->data);
    }

    // Digital products download
    public function downloads($id = null, $hash = null)
    {
        if (!$this->loggedIn) {
            redirect('login');
        }
        if ($this->Staff) {
            admin_redirect();
        }
        if ($id && $hash && md5($id) == $hash) {
            $sale = $this->shop_model->getDownloads(1, 0, $id);
            if (!empty($sale)) {
                $product = $this->site->getProductByID($id);
                if (file_exists('./files/' . $product->file)) {
                    $this->load->helper('download');
                    force_download('./files/' . $product->file, null);
                    exit;
                }
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Transfer-Encoding: Binary');
                header('Content-disposition: attachment; filename="' . basename($product->file) . '"');
                // header('Content-Length: ' . filesize($product->file));
                readfile($product->file);
            }
            $this->session->set_flashdata('error', lang('file_x_exist'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $page = $this->input->get('page') ? $this->input->get('page', true) : 1;
            $limit = 10;
            $offset = ($page * $limit) - $limit;
            $this->load->helper('pagination');
            $total_rows = $this->shop_model->getDownloadsCount();
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['downloads'] = $this->shop_model->getDownloads($limit, $offset);
            $this->data['pagination'] = pagination('shop/download', $total_rows, $limit);
            $this->data['page_info'] = ['page' => $page, 'total' => ceil($total_rows / $limit)];
            $this->data['page_title'] = lang('my_downloads');
            $this->data['page_desc'] = '';
            $this->page_construct('pages/downloads', $this->data);
        }
    }

    // Add attachment to sale on manual payment
    public function manual_payment($order_id)
    {
        if ($_FILES['payment_receipt']['size'] > 0) {
            $this->load->library('upload');
            $config['upload_path'] = 'files/';
            $config['allowed_types'] = 'zip|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
            $config['max_size'] = 2048;
            $config['overwrite'] = false;
            $config['max_filename'] = 25;
            $config['encrypt_name'] = true;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('payment_receipt')) {
                $error = $this->upload->display_errors();
                $this->session->set_flashdata('error', $error);
                redirect($_SERVER['HTTP_REFERER']);
            }
            $attachment = [
                'subject_type' => 'sale',
                'subject_id' => $order_id,
                'file_name' => $this->upload->data('file_name'),
                'orig_name' => $this->upload->data('orig_name'),
            ];
            $this->db->insert('attachments', $attachment);
            $this->db->update('sales', ['attachment' => 1], ['id' => $order_id]);
            $this->session->set_flashdata('message', lang('file_submitted'));
            redirect($_SERVER['HTTP_REFERER'] ?? '/shop/orders');
        }
    }

    // Add new Order form shop
    public function order()
    {
        
        $guest_checkout = $this->input->post('guest_checkout');
        if (!$guest_checkout && !$this->loggedIn) {
            redirect('login');
        }

        $this->load->admin_model('inventory_model');

        $this->form_validation->set_rules('address', lang('address'), 'trim|required');
        $this->form_validation->set_rules('note', lang('comment'), 'trim');
        $this->form_validation->set_rules('payment_method', lang('payment_method'), 'required');
        $this->form_validation->set_rules('shipping', lang('shipping'), 'trim|required');
        if ($guest_checkout) {
            $this->form_validation->set_rules('name', lang('name'), 'trim|required');
            $this->form_validation->set_rules('email', lang('email'), 'trim|required|valid_email');
            $this->form_validation->set_rules('phone', lang('phone'), 'trim|required');
            //            $this->form_validation->set_rules('billing_line1', lang('billing_address') . ' ' . lang('line1'), 'trim|required');
//            $this->form_validation->set_rules('billing_city', lang('billing_address') . ' ' . lang('city'), 'trim|required');
//            $this->form_validation->set_rules('billing_country', lang('billing_address') . ' ' . lang('country'), 'trim|required');
            $this->form_validation->set_rules('shipping_line1', lang('shipping_address') . ' ' . lang('line1'), 'trim|required');
            $this->form_validation->set_rules('shipping_city', lang('shipping_address') . ' ' . lang('city'), 'trim|required');
            $this->form_validation->set_rules('shipping_country', lang('shipping_address') . ' ' . lang('country'), 'trim|required');
            $this->form_validation->set_rules('shipping_phone', lang('shipping_address') . ' ' . lang('phone'), 'trim|required');
        }
        if ($guest_checkout && $this->Settings->indian_gst) {
            //            $this->form_validation->set_rules('billing_state', lang('billing_address') . ' ' . lang('state'), 'trim|required');
            $this->form_validation->set_rules('shipping_state', lang('shipping_address') . ' ' . lang('state'), 'trim|required');
        }

        if ($this->form_validation->run() == true) {
            if ($guest_checkout || $address = $this->shop_model->getAddressByID($this->input->post('address'))) {
                $new_customer = false;
                if ($guest_checkout) {
                    $address = [
                        'phone' => $this->input->post('shipping_phone'),
                        'line1' => $this->input->post('shipping_line1'),
                        'line2' => $this->input->post('shipping_line2'),
                        'city' => $this->input->post('shipping_city'),
                        'state' => $this->input->post('shipping_state'),
                        'postal_code' => $this->input->post('shipping_postal_code'),
                        'country' => $this->input->post('shipping_country'),
                        'latitude' => $this->input->post('shipping_latitude'),
                        'longitude' => $this->input->post('shipping_longitude')
                    ];
                }
                if ($this->input->post('address') != 'new') {
                    $customer = $this->site->getCompanyByID($this->session->userdata('company_id'));
                } else {
                    if (!($customer = $this->shop_model->getCompanyByEmail($this->input->post('email')))) {
                        $customer = new stdClass();
                        $customer->name = $this->input->post('name') . ($this->input->post('last_name') ?: '');
                        $customer->company = 'Pharma Drug Store'; //$this->input->post('company');
                        $customer->phone = $this->input->post('phone');
                        $customer->email = $this->input->post('email');
                        $customer->address = $this->input->post('billing_line1') . '<br>' . $this->input->post('billing_line2');
                        $customer->city = $this->input->post('billing_city');
                        $customer->state = $this->input->post('billing_state');
                        $customer->postal_code = $this->input->post('billing_postal_code');
                        $customer->country = $this->input->post('billing_country');
                        $customer->group_id = 3;
                        $customer->group_name = 'customer';
                        $customer->country = $this->input->post('billing_country');
                        $customer_group = $this->shop_model->getCustomerGroup($this->Settings->customer_group);
                        $price_group = $this->shop_model->getPriceGroup($this->Settings->price_group);
                        $customer->customer_group_id = (!empty($customer_group)) ? $customer_group->id : null;
                        $customer->customer_group_name = (!empty($customer_group)) ? $customer_group->name : null;
                        $customer->price_group_id = (!empty($price_group)) ? $price_group->id : null;
                        $customer->price_group_name = (!empty($price_group)) ? $price_group->name : null;
                        $new_customer = true;
                    }
                }
                $biller = $this->site->getCompanyByID($this->shop_settings->biller);
                $note = $this->db->escape_str($this->input->post('comment'));
                $product_tax = 0;
                $total = 0;
                $gst_data = [];
                $pro_weight = [];
                $total_cgst = $total_sgst = $total_igst = 0;
                $out_stock_item_found = false;
                foreach ($this->cart->contents() as $item) {
                    $item_option = null;
                    $qty_on_hold = $this->shop_model->getProductOnholdQty($item['product_id']);
                    if ($product_details = $this->shop_model->getProductForCart($item['product_id'])) {
                        //$qty_available = $product_details->quantity - $qty_on_hold;
                        $new_stock = $this->inventory_model->get_current_stock($item['product_id'], 'null');
                        $qty_available = intval($new_stock) - $qty_on_hold;
                        if($qty_available >= $item['qty']){
                            $price = $this->sma->setCustomerGroupPrice(($this->loggedIn && isset($product_details->special_price) ? $product_details->special_price : $product_details->price), $this->customer_group);
                            $price = $this->sma->isPromo($product_details) ? $product_details->promo_price : $price;
                            if ($item['option']) {
                                if ($product_variant = $this->shop_model->getProductVariantByID($item['option'])) {
                                    $item_option = $product_variant->id;
                                    $price = $product_variant->price + $price;
                                }
                            }

                            $item_net_price = $unit_price = $price;
                            $item_quantity = $item_unit_quantity = $item['qty'];
                            $pr_item_tax = $item_tax = 0;
                            $tax = '';

                            if (!empty($product_details->tax_rate)) {
                                $tax_details = $this->site->getTaxRateByID($product_details->tax_rate);
                                $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                                $item_tax = $ctax['amount'];
                                $tax = $ctax['tax'];
                                if ($product_details->tax_method != 1) {
                                    $item_net_price = $unit_price - $item_tax;
                                }
                                $pr_item_tax = $this->sma->formatDecimalFunc(($item_tax * $item_unit_quantity), 4);
                                if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($biller->state == $customer->state), $tax_details)) {
                                    $total_cgst += $gst_data['cgst'];
                                    $total_sgst += $gst_data['sgst'];
                                    $total_igst += $gst_data['igst'];
                                }
                            }

                            $product_tax += $pr_item_tax;
                            $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax);

                            $unit = $this->site->getUnitByID($product_details->unit);

                            $product = [
                                'product_id' => $product_details->id,
                                'product_code' => $product_details->code,
                                'product_name' => $product_details->name,
                                'product_type' => $product_details->type,
                                'option_id' => $item_option,
                                'net_unit_price' => $item_net_price,
                                'unit_price' => $this->sma->formatDecimalFunc($item_net_price + $item_tax),
                                'quantity' => $item_quantity,
                                'product_unit_id' => $unit ? $unit->id : null,
                                'product_unit_code' => $unit ? $unit->code : null,
                                'unit_quantity' => $item_unit_quantity,
                                'warehouse_id' => $this->shop_settings->warehouse,
                                'item_tax' => $pr_item_tax,
                                'tax_rate_id' => $product_details->tax_rate,
                                'tax' => $tax,
                                'discount' => null,
                                'item_discount' => 0,
                                'subtotal' => $this->sma->formatDecimalFunc($subtotal),
                                'serial_no' => null,
                                'real_unit_price' => $price,
                            ];
                            $ww = $this->shop_model->getProductByID($product_details->id);
                            $ww2 = array('product_weight' => $ww->weight);
                            $pro_weight[] = $ww2;
                            $products[] = ($product + $gst_data);
                            $total += $this->sma->formatDecimalFunc(($item_net_price * $item_unit_quantity), 4);
                        }else{
                            $out_stock_item_found = true;
                            $this->session->set_flashdata('error', lang('out of stock item') . ' (' . $item['name'] . ')');
                            redirect('cart');
                        }
                        
                    } else {
                        $this->session->set_flashdata('error', lang('product_x_found') . ' (' . $item['name'] . ')');
                        redirect($_SERVER['HTTP_REFERER'] ?? 'cart');
                    }
                }

                $shipping = !empty($this->input->post('shipping'))
                    ? $this->input->post('shipping')
                    : $this->shop_settings->shipping;

                $order_tax = $this->site->calculateOrderTax($this->Settings->default_tax_rate2, ($total + $product_tax));
                $total_tax = $this->sma->formatDecimalFunc(($product_tax + $order_tax), 4);
                //$grand_total = $this->sma->formatDecimal(($total + $total_tax + $shipping), 4);

                $total = !empty($this->cart->total())
                    ? $this->cart->total()
                    : $total;

                $total_tax = !empty($this->cart->total_item_tax())
                    ? $this->cart->total_item_tax()
                    : $total_tax;

                $total_discount = !empty($this->cart->get_total_discount())
                    ? $this->cart->get_total_discount()
                    : 0;

                $grand_total = $this->sma->formatDecimalFunc(($total + $shipping), 4);

                $coupon_details = $this->session->userdata('coupon_details');
                if(isset($coupon_details['code'])){
                    $c_code = $coupon_details['code'];
                }else{
                    $c_code = '';
                }

                $data = [
                    'date' => date('Y-m-d H:i:s'),
                    'reference_no' => $this->site->getReference('so'),
                    'customer_id' => $customer->id ?? '',
                    'customer' => ($customer->company && $customer->company != '-' ? $customer->company : $customer->name),
                    'biller_id' => $biller->id,
                    'biller' => ($biller->company && $biller->company != '-' ? $biller->company : $biller->name),
                    'warehouse_id' => $this->shop_settings->warehouse,
                    'note' => $note,
                    'staff_note' => null,
                    'total' => $total,
                    'product_discount' => 0,
                    'order_discount_id' => null,
                    'order_discount' => 0,
                    'total_discount' => $total_discount,
                    'product_tax' => $total_tax,
                    'order_tax_id' => $this->Settings->default_tax_rate2,
                    'order_tax' => $order_tax,
                    'total_tax' => $total_tax,
                    'shipping' => $shipping,
                    'grand_total' => $grand_total,
                    'total_items' => $this->cart->total_items(),
                    'sale_status' => 'pending',
                    'payment_status' => 'pending',
                    'payment_term' => null,
                    'due_date' => null,
                    'paid' => 0,
                    'created_by' => $this->session->userdata('user_id') ? $this->session->userdata('user_id') : null,
                    'shop' => 1,
                    'address_id' => ($this->input->post('address') == 'new' || $this->input->post('address') == 'default' ) ? 0 : $address->id,
                    'hash' => hash('sha256', microtime() . mt_rand()),
                    'payment_method' => $this->input->post('payment_method'),
                    'delivery_type' => $this->input->post('express_delivery'),
                    'coupon_code' => $c_code
                ];
                if ($this->Settings->invoice_view == 2) {
                    $data['cgst'] = $total_cgst;
                    $data['sgst'] = $total_sgst;
                    $data['igst'] = $total_igst;
                }

                if ($new_customer) {
                    $customer = (array) $customer;
                }
                // $this->sma->print_arrays($data, $products, $customer, $address);

                if($out_stock_item_found == true){
                    $this->session->set_flashdata('error', lang('out of stock item in cart'));
                    redirect($_SERVER['HTTP_REFERER'] ?? 'cart');
                }
                else if ($sale_id = $this->shop_model->addSale($data, $products, $customer, $address)) {
                    //$added_record = $this->aramexshipment($sale_id, $data, $products, $customer, $address,$pro_weight);
                    //$email = $this->order_received($sale_id, $data['hash'], $added_record);

                    if (!$email['sent']) {
                        $this->session->set_flashdata('error', $email['error']);
                    }
                    // $this->load->library('sms');
                    // $this->sms->newSale($sale_id);
                    // $this->cart->destroy();
                    $this->session->set_flashdata('info', lang('order_added_make_payment'));
                    if ($this->input->post('payment_method') == 'paypal') {
                        redirect('pay/paypal/' . $sale_id);
                    } elseif ($this->input->post('payment_method') == 'skrill') {
                        redirect('pay/skrill/' . $sale_id);
                    } elseif ($this->input->post('payment_method') == 'directpay') {
                        //$this->sendTwillioSMS();
                        //$this->sendMsegatSMS();

                        $card_name = $this->input->post('card_name');
                        $card_number = $this->input->post('card_number');
                        $card_cvv = $this->input->post('card_cvv');
                        $card_expiry = $this->input->post('card_expiry_year');
                        $payment_method_details = $this->input->post('payment_method_details');
                        
                        $card_expiry_year = trim(explode('/', $card_expiry)[1]);
                        $card_expiry_month = trim(explode('/', $card_expiry)[0]);

                        $tabby_email = $this->input->post('tabby_email');
                        $tabby_phone = $this->input->post('tabby_phone');

                        // Store card details in session
                        $this->session->set_userdata('card_details', array(
                            'card_name' => $card_name,
                            'card_number' => $card_number,
                            'card_cvv' => $card_cvv,
                            'card_expiry_month' => $card_expiry_month,
                            'card_expiry_year' => $card_expiry_year,
                            'payment_method_details' => $payment_method_details
                        ));

                        // Store tabby details in session
                        $this->session->set_userdata('tabby_details', array(
                            'tabby_email' => $tabby_email,
                            'tabby_phone' => $tabby_phone
                        ));
                        
                        if($card_number == '4847 8358 5060 8454'){
                            redirect('pay/directpay/' . $sale_id);
                        }else{
                            redirect('pay/directpay/' . $sale_id);
                        }
                    } else {
                        shop_redirect('orders/' . $sale_id . '/' . ($this->loggedIn ? '' : $data['hash']));
                    }
                }
            } else {
                $this->session->set_flashdata('error', lang('address_x_found'));
                redirect($_SERVER['HTTP_REFERER'] ?? 'cart/checkout');
            }
        } else {
            $this->session->set_flashdata('validation_errors', validation_errors());
            redirect('cart/checkout' . ($guest_checkout ? '#guest' : ''));
        }
    }

    public function sendMsegatSMS()
    {
        $data = [
            'userName' => 'phmc',
            'numbers' => '966541226217',
            'userSender' => 'phmc',
            'apiKey' => 'd3a916960217e3c7bc0af6ed80d1435c',
            'msg' => 'This is test message from MSEGAT',
        ];

        // Convert the data to JSON format
        $jsonData = json_encode($data);

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, 'https://www.msegat.com/gw/sendsms.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        // Execute the cURL request and store the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
        }

        // Close the cURL session
        curl_close($ch);

        // Output the response
        //echo $response;
    }

    public function sendTwillioSMS()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

        $sid = "AC0a1a268b42316a7925e2190b5501608d";
        $token = "d6cdca446054c10091ae2cbdde8b7f40";
        $client = new Twilio\Rest\Client($sid, $token);

        // Use the Client to make requests to the Twilio REST API
        $client->messages->create(
            // The number you'd like to send the message to
            '+966511065098',
            [
                // A Twilio phone number you purchased at https://console.twilio.com
                'from' => '+12512209687',
                // The body of the text message you'd like to send
                'body' => "Hey Jenny! Good luck on the bar exam!"
            ]
        );
    }

    public function aramexshipment($sale_id, $data, $products, $customer, $address, $pro_weight)
    {
        $dp = $this->shop_model->getAramexSettings();

        if ($dp->activation == 1) {
            $p_accountnumber = $dp->account_number; //'71449672';
            $p_line1 = $dp->line1; //"Al kharaj";
            $p_city = $dp->city; //"Riyadh";
            $p_postcode = $dp->postal_code; //"11663";
            $p_countrycode = $dp->country_code; //"SA";
            $p_personname = $dp->person_name; //"Amr";
            $p_companyname = $dp->company_name; //"Pharma drug store";
            $p_phonenumber = $dp->landline_number; //"966568241418";
            $p_cellnumber = $dp->cell_number; //"966568241418";
            $p_shipper_email = $dp->Email; //"aeid@avenzur.com";

            $p_AccountEntity = $dp->account_entity; //'RUH';
            $p_AccountNumber = $dp->account_number; //'71449672';
            $p_AccountPin = $dp->account_pin; //'107806';
            $p_UserName = $dp->user_name; //'testingapi@aramex.com';
            $p_Password = $dp->password; //'R123456789$r';
            $p_Version = $dp->version; //'1.0';

            $p_soapLink = $dp->shippment_url;

        } else {
            $p_accountnumber = '71449672';
            $p_line1 = "Al kharaj";
            $p_city = "Riyadh";
            $p_postcode = "11663";
            $p_countrycode = "SA";
            $p_personname = "Amr";
            $p_companyname = "Pharma drug store";
            $p_phonenumber = "966568241418";
            $p_cellnumber = "966568241418";
            $p_shipper_email = "aeid@avenzur.com";

            $p_AccountEntity = 'RUH';
            $p_AccountNumber = '71449672';
            $p_AccountPin = '107806';
            $p_UserName = 'testingapi@aramex.com';
            $p_Password = 'R123456789$r';
            $p_Version = '1.0';

            $p_soapLink = $dp->test_shippment_url; //'https://ws.sbx.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?wsdl';
        }


        $soapClient = new SoapClient($p_soapLink);
        //echo '<pre>';
        //print_r($soapClient->__getFunctions());


        $p_sale_id = $sale_id;
        $p_transaction = (int) (microtime(true) * 1000);


        $cutomer_array = (array) $customer;

        //print_r($cutomer_array);

        // var_dump($address);

        $c_Line1 = $cutomer_array['address'];
        $c_City = $cutomer_array['city'];
        $c_PostCode = $cutomer_array['postal_code'];
        $c_CountryCode = $cutomer_array['country'];
        $c_PersonName = $cutomer_array['name'];
        $c_PhoneNumber = $cutomer_array['phone'];
        $c_CellPhone = $cutomer_array['phone'];
        $c_EmailAddress = $cutomer_array['email'];
        $c_CompanyName = 'Pharma Drug Store'; //$cutomer_array['company'];
        $c_State = $cutomer_array['state'];

        $product_weight = 0.0;
        $product_unit = 'Kg';
        $params = array();

        foreach ($pro_weight as $w) {
            $product_weight += $w['product_weight'];
        }
        foreach ($products as $product) {
            //$product_weight += $product['product_weight'];

            $params['Shipments']['Shipment']['Details']['Items'][] = array(
                'PackageType' => 'Box',
                'Quantity' => $product['quantity'],
                'Weight' => array(
                        'Value' => '0.20', //$product['product_weight'],
                        'Unit' => 'Kg',
                    ),
                'Comments' => 'Medicine Boxes',
                'Reference' => ''
            );
        }


        //var_dump($pro_weight);
        $params = array(
            'Shipments' => array(
                'Shipment' => array(
                    'Shipper' => array(
                        'Reference1' => $p_transaction,
                        'Reference2' => $p_sale_id,
                        'AccountNumber' => $p_accountnumber,
                        'PartyAddress' => array(
                                'Line1' => $p_line1,
                                'Line2' => '',
                                'Line3' => '',
                                'City' => $p_city,
                                'StateOrProvinceCode' => '',
                                'PostCode' => $p_postcode,
                                'CountryCode' => $p_countrycode
                            ),
                        'Contact' => array(
                            'Department' => '',
                            'PersonName' => $p_personname,
                            'Title' => '',
                            'CompanyName' => $p_companyname,
                            'PhoneNumber1' => $p_phonenumber,
                            'PhoneNumber1Ext' => '',
                            'PhoneNumber2' => '',
                            'PhoneNumber2Ext' => '',
                            'FaxNumber' => '',
                            'CellPhone' => $p_cellnumber,
                            'EmailAddress' => $p_shipper_email,
                            'Type' => ''
                        ),
                    ),

                    'Consignee' => array(
                        'Reference1' => $p_transaction,
                        'Reference2' => $p_sale_id,
                        'AccountNumber' => $p_accountnumber,
                        'PartyAddress' => array(
                                'Line1' => $c_Line1,
                                'Line2' => '',
                                'Line3' => '',
                                'City' => $c_City,
                                'StateOrProvinceCode' => (!empty($c_State)) ? $c_State : '',
                                'PostCode' => (!empty($c_PostCode)) ? $c_PostCode : '',
                                'CountryCode' => $c_CountryCode
                            ),

                        'Contact' => array(
                            'Department' => '',
                            'PersonName' => $c_PersonName,
                            'Title' => '',
                            'CompanyName' => (!empty($c_CompanyName)) ? $c_CompanyName : '',
                            'PhoneNumber1' => $c_PhoneNumber,
                            'PhoneNumber1Ext' => '',
                            'PhoneNumber2' => '',
                            'PhoneNumber2Ext' => '',
                            'FaxNumber' => '',
                            'CellPhone' => $c_CellPhone,
                            'EmailAddress' => $c_EmailAddress,
                            'Type' => ''
                        ),
                    ),

                    'ThirdParty' => array(
                        'Reference1' => '',
                        'Reference2' => '',
                        'AccountNumber' => '',
                        'PartyAddress' => array(
                                'Line1' => '',
                                'Line2' => '',
                                'Line3' => '',
                                'City' => '',
                                'StateOrProvinceCode' => '',
                                'PostCode' => '',
                                'CountryCode' => ''
                            ),
                        'Contact' => array(
                            'Department' => '',
                            'PersonName' => '',
                            'Title' => '',
                            'CompanyName' => '',
                            'PhoneNumber1' => '',
                            'PhoneNumber1Ext' => '',
                            'PhoneNumber2' => '',
                            'PhoneNumber2Ext' => '',
                            'FaxNumber' => '',
                            'CellPhone' => '',
                            'EmailAddress' => '',
                            'Type' => ''
                        ),
                    ),

                    'Reference1' => $p_transaction,
                    'Reference2' => $p_sale_id,
                    'Reference3' => '',
                    'ForeignHAWB' => $p_transaction,
                    'TransportType' => 0,
                    'ShippingDateTime' => time(),
                    'DueDate' => time(),
                    'PickupLocation' => 'Reception',
                    'PickupGUID' => '',
                    'Comments' => $p_sale_id,
                    'AccountingInstrcutions' => '',
                    'OperationsInstructions' => '',

                    'Details' => array(
                            'Dimensions' => array(
                                'Length' => '',
                                'Width' => '',
                                'Height' => '',
                                'Unit' => 'cm',

                            ),

                            'ActualWeight' => array(
                                'Value' => ($product_weight >= 1) ? $product_weight : 1.0,
                                'Unit' => 'Kg'
                            ),

                            'ProductGroup' => 'EXP',
                            'ProductType' => 'PDX',
                            'PaymentType' => 'P',
                            'PaymentOptions' => '',
                            'Services' => '',
                            'NumberOfPieces' => 1,
                            'DescriptionOfGoods' => 'Medicine',
                            'GoodsOriginCountry' => $p_countrycode,

                            'CashOnDeliveryAmount' => array(
                                    'Value' => 0,
                                    'CurrencyCode' => ''
                                ),

                            'InsuranceAmount' => array(
                                'Value' => 0,
                                'CurrencyCode' => ''
                            ),

                            'CollectAmount' => array(
                                'Value' => 0,
                                'CurrencyCode' => ''
                            ),

                            'CashAdditionalAmount' => array(
                                'Value' => 0,
                                'CurrencyCode' => ''
                            ),

                            'CashAdditionalAmountDescription' => '',

                            'CustomsValueAmount' => array(
                                    'Value' => 0,
                                    'CurrencyCode' => ''
                                ),

                            'Items' => array()
                        ),
                ),
            ),

            'ClientInfo' => array(
                'AccountCountryCode' => $p_countrycode,
                'AccountEntity' => $p_AccountEntity,
                'AccountNumber' => $p_AccountNumber,
                'AccountPin' => $p_AccountPin,
                'UserName' => $p_UserName,
                'Password' => $p_Password,
                'Version' => $p_Version
            ),

            'Transaction' => array(
                'Reference1' => $p_transaction,
                'Reference2' => $p_sale_id,
                'Reference3' => '',
                'Reference4' => '',
                'Reference5' => '',
            ),
            'LabelInfo' => array(
                //'ReportID' 				=> 9202,
                'ReportID' => 9729,
                'ReportType' => 'URL',
            ),
        );


        /*$url = "https://ws.sbx.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc/json/CreateShipments";
        $ch = curl_init( $url );
            # Setup request to send json via POST.
            $payload = json_encode( $params );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            # Return response instead of printing.
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            # Send request.
            $result = curl_exec($ch);
            curl_close($ch);
            # Print response.

            print_r($payload);

            echo $result;*/

        try {
            $auth_call = $soapClient->CreateShipments($params);

            //var_dump((array)$auth_call);
            $data = json_decode(json_encode($auth_call), true); //(array)$auth_call;


            //var_dump($data);

            $record = array(
                "salesid" => $data["Transaction"]["Reference2"],
                "reference" => $data["Transaction"]["Reference1"],
                "shipmentid" => $data['Shipments']["ProcessedShipment"]["ID"],
                "labelurl" => $data['Shipments']["ProcessedShipment"]["ShipmentLabel"]["LabelURL"],
                "date" => date('Y-m-d H:i:s'),
                "note" => "successful"
            );

            $salesarr = array(
                'attachment' => $data['Shipments']["ProcessedShipment"]["ShipmentLabel"]["LabelURL"]
            );


            $this->shop_model->addAramexShippment($record);
            $this->shop_model->salesarrAdd($p_sale_id, $salesarr);
            /*echo "Salesid ".$data["Transaction"]["Reference1"].'<br>';
            echo "Reference ".$data["Transaction"]["Reference2"].'<br>';
            echo "HasErrors: ".$data["HasErrors"]."<br>";
            echo "Shipments ID: ".$data['Shipments']["ProcessedShipment"]["ID"]."<br>";
            echo "Shipments Label URL: ".$data['Shipments']["ProcessedShipment"]["ShipmentLabel"]["LabelURL"]."<br>";
            echo "Shipment Origin: ".$data['Shipments']["ProcessedShipment"]["ShipmentDetails"]["Origin"]."<br>";
            echo "Shipment Destination: ".$data['Shipments']["ProcessedShipment"]["ShipmentDetails"]["Destination"]."<br>";*/
            return $record;
            //	die();
        } catch (SoapFault $fault) {
            //die('Error : ' . $fault->faultstring);
        }
        return;
    }

    public function order_received($id = null, $hash = null, $aramex_record)
    {
        if ($inv = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {
            $user = $inv->created_by ? $this->site->getUser($inv->created_by) : null;
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $inv->reference_no,
                'contact_person' => $customer->name,
                'company' => $customer->company && $customer->company != '-' ? '(' . $customer->company . ')' : '',
                //'order_link'       => shop_url('orders/' . $id . '/' . ($this->loggedIn ? '' : $inv->hash)),
                'order_link' => $aramex_record['labelurl'],
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            ];
            $msg = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/sale.html');
            $message = $this->parser->parse_string($msg, $parse_data);
            $this->load->model('pay_model');
            $paypal = $this->pay_model->getPaypalSettings();
            $skrill = $this->pay_model->getSkrillSettings();
            $btn_code = '<div id="payment_buttons" class="text-center margin010">';
            if (!empty($this->shop_settings->bank_details)) {
                $btn_code .= '<div style="width:100%;">' . $this->shop_settings->bank_details . '</div><hr class="divider or">';
            }
            if ($paypal->active == '1' && $inv->grand_total != '0.00') {
                if (trim(strtolower($customer->country)) == $biller->country) {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_my / 100);
                } else {
                    $paypal_fee = $paypal->fixed_charges + ($inv->grand_total * $paypal->extra_charges_other / 100);
                }
                $btn_code .= '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=' . $paypal->account_email . '&item_name=' . $inv->reference_no . '&item_number=' . $inv->id . '&image_url=' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '&amount=' . (($inv->grand_total - $inv->paid) + $paypal_fee) . '&no_shipping=1&no_note=1&currency_code=' . $this->default_currency->code . '&bn=BuyNow&rm=2&return=' . admin_url('sales/view/' . $inv->id) . '&cancel_return=' . admin_url('sales/view/' . $inv->id) . '&notify_url=' . admin_url('payments/paypalipn') . '&custom=' . $inv->reference_no . '__' . ($inv->grand_total - $inv->paid) . '__' . $paypal_fee . '"><img src="' . base_url('assets/images/btn-paypal.png') . '" alt="Pay by PayPal"></a> ';
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
            $message = $message . $btn_code;
            $attachment = $this->orders($id, $hash, true, 'S');
            $subject = lang('new_order_received');
            $sent = false;
            $error = false;
            $cc = [];
            $bcc = [];
            if ($user) {
                $cc[] = $customer->email;
            }
            $cc[] = $biller->email;
            $warehouse = $this->site->getWarehouseByID($inv->warehouse_id);
            if ($warehouse->email) {
                $cc[] = $warehouse->email;
            }
            try {
                if ($this->sma->send_email(($user ? $user->email : $customer->email), $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $sent = true;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            return ['sent' => $sent, 'error' => $error];
        }
    }

    public function invoiceorders($id = null, $hash = null, $pdf = null, $buffer_save = null){
        $hash = $hash ? $hash : $this->input->get('hash', true);
        /*if (!$this->loggedIn && !$hash) {
            redirect('/');
        }
        if ($this->Staff) {
            admin_redirect('sales');
        }*/
        //order tracking
       $action = $this->input->get('action');
       
       if($action == 'tracking') {
        $order = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash]);
        $this->cart->destroy();
        $this->data['order'] = $order;
        $this->page_construct('pages/order_tracking', $this->data);
       }
        else if ($id && !$pdf) {
            if ($order = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {
                $this->load->library('inv_qrcode');
                $this->data['inv'] = $order;
                $this->data['rows'] = $this->shop_model->getOrderItems($id);
                $this->data['customer'] = $this->site->getCompanyByID($order->customer_id);
                $this->data['biller'] = $this->site->getCompanyByID($order->biller_id);
                $this->data['address'] = array();
                $this->data['address'] = $this->shop_model->getAddressByID($order->address_id);
                
                $this->data['return_sale'] = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
                $this->data['return_rows'] = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
                $this->data['paypal'] = $this->shop_model->getPaypalSettings();
                $this->data['skrill'] = $this->shop_model->getSkrillSettings();
                $this->data['page_title'] = lang('view_order');
                $this->data['page_desc'] = '';

                $this->config->load('payment_gateways');
                $this->data['stripe_secret_key'] = $this->config->item('stripe_secret_key');
                $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
                $this->data['all_categories'] = $this->shop_model->getAllCategories();
                $this->page_construct('pages/view_order', $this->data);
                $this->cart->destroy();
                //$this->page_construct('pages/thankyou', $this->data);
            } else {
                $this->session->set_flashdata('error', lang('access_denied'));
                redirect('/');
            }
        } elseif ($pdf || $this->input->get('download')) {
            $this->load->library('inv_qrcode');
            $id = $pdf ? $id : $this->input->get('download', true);
            $hash = $hash ? $hash : $this->input->get('hash', true);
            $order = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash]);
            $this->data['inv'] = $order;
            $this->data['rows'] = $this->shop_model->getOrderItems($id);
            $this->data['customer'] = $this->site->getCompanyByID($order->customer_id);
            $this->data['biller'] = $this->site->getCompanyByID($order->biller_id);
            $this->data['address'] = $this->shop_model->getAddressByID($order->address_id);
            $this->data['return_sale'] = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
            $this->data['return_rows'] = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
            $this->data['Settings'] = $this->Settings;
            $this->data['all_categories'] = $this->shop_model->getAllCategories();
            $this->data['shop_settings'] = $this->shop_settings;
            $html = $this->load->view($this->Settings->theme . '/shop/views/pages/pdf_invoice', $this->data, true);
            if ($this->input->get('view')) {
                echo $html;
                exit;
            }
            $name = lang('invoice') . '_' . str_replace('/', '_', $order->reference_no) . '.pdf';
            if ($buffer_save) {
                return $this->sma->generate_pdf($html, $name, $buffer_save, $this->data['biller']->invoice_footer);
            }
            $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
        } elseif (!$id) {
            $page = $this->input->get('page') ? $this->input->get('page', true) : 1;
            $limit = 50;
            $offset = ($page * $limit) - $limit;
            $this->load->helper('pagination');
            //$total_rows = $this->shop_model->getCustomerOrdersCount();
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['orders'] = $this->shop_model->getOrders($limit, $offset);
            //$this->data['pagination'] = pagination('shop/orders', $total_rows, $limit);
            $this->data['page_info'] = ['page' => $page, 'total' => ceil($total_rows / $limit)];
            $this->data['page_title'] = lang('my_orders');
            $this->data['page_desc'] = '';
            $this->data['all_categories'] = $this->shop_model->getAllCategories();
            $this->page_construct('pages/orders', $this->data);
        }
    }

    // Customer order/orders page
    public function orders($id = null, $hash = null, $pdf = null, $buffer_save = null)
    {
        $hash = $hash ? $hash : $this->input->get('hash', true);
        if (!$this->loggedIn && !$hash) {
            //redirect('/');
        }
        if ($this->Staff) {
            admin_redirect('sales');
        }
        //order tracking
       $action = $this->input->get('action');
       
       if($action == 'tracking') {
        $order = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash]);
        $this->cart->destroy();
        $this->data['order'] = $order;
        $this->page_construct('pages/order_tracking', $this->data);
       }
        else if ($id && !$pdf) {
            if ($order = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {
                $this->load->library('inv_qrcode');
                $this->data['inv'] = $order;
                $this->data['rows'] = $this->shop_model->getOrderItems($id);
                $this->data['customer'] = $this->site->getCompanyByID($order->customer_id);
                $this->data['biller'] = $this->site->getCompanyByID($order->biller_id);
                $this->data['address'] = array();
                $this->data['address'] = $this->shop_model->getAddressByID($order->address_id);
                
                $this->data['return_sale'] = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
                $this->data['return_rows'] = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
                $this->data['paypal'] = $this->shop_model->getPaypalSettings();
                $this->data['skrill'] = $this->shop_model->getSkrillSettings();
                $this->data['page_title'] = lang('view_order');
                $this->data['page_desc'] = '';

                $this->config->load('payment_gateways');
                $this->data['stripe_secret_key'] = $this->config->item('stripe_secret_key');
                $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
                $this->data['all_categories'] = $this->shop_model->getAllCategories();
                //$this->page_construct('pages/view_order', $this->data);
                $this->cart->destroy();
                $this->page_construct('pages/thankyou', $this->data);
            } else {
                $this->session->set_flashdata('error', lang('access_denied'));
                redirect('/');
            }
        } elseif ($pdf || $this->input->get('download')) {
            $this->load->library('inv_qrcode');
            $id = $pdf ? $id : $this->input->get('download', true);
            $hash = $hash ? $hash : $this->input->get('hash', true);
            $order = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash]);
            $this->data['inv'] = $order;
            $this->data['rows'] = $this->shop_model->getOrderItems($id);
            $this->data['customer'] = $this->site->getCompanyByID($order->customer_id);
            $this->data['biller'] = $this->site->getCompanyByID($order->biller_id);
            $this->data['address'] = $this->shop_model->getAddressByID($order->address_id);
            $this->data['return_sale'] = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
            $this->data['return_rows'] = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
            $this->data['Settings'] = $this->Settings;
            $this->data['all_categories'] = $this->shop_model->getAllCategories();
            $this->data['shop_settings'] = $this->shop_settings;
            $html = $this->load->view($this->Settings->theme . '/shop/views/pages/pdf_invoice', $this->data, true);
            if ($this->input->get('view')) {
                echo $html;
                exit;
            }
            $name = lang('invoice') . '_' . str_replace('/', '_', $order->reference_no) . '.pdf';
            if ($buffer_save) {
                return $this->sma->generate_pdf($html, $name, $buffer_save, $this->data['biller']->invoice_footer);
            }
            $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
        } elseif (!$id) {
            $page = $this->input->get('page') ? $this->input->get('page', true) : 1;
            $limit = 50;
            $offset = ($page * $limit) - $limit;
            $this->load->helper('pagination');
            //$total_rows = $this->shop_model->getCustomerOrdersCount();
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['orders'] = $this->shop_model->getOrders($limit, $offset);
            //$this->data['pagination'] = pagination('shop/orders', $total_rows, $limit);
            $this->data['page_info'] = ['page' => $page, 'total' => ceil($total_rows / $limit)];
            $this->data['page_title'] = lang('my_orders');
            $this->data['page_desc'] = '';
            $this->data['all_categories'] = $this->shop_model->getAllCategories();
            $this->page_construct('pages/orders', $this->data);
        }
    }
    public function track_order($id = null, $hash = null, $pdf = null, $buffer_save = null)
    {
        $order_number=$this->input->post('order_number'); 
        $id= $order_number; 
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = lang('Order_Tracking');
        if(!empty($id)){ 
            $order = $this->shop_model->getOrderByID(['id' => $id, 'hash' => $hash]); 
            // $this->cart->destroy(); 
            $this->data['order'] = $order;
            $this->data['order_number'] = $order_number;
        }
        $this->page_construct('pages/track_orders', $this->data); 
        
    }
    public function contact_us()
    {

        if (!empty($_POST['formSubmitted'])) {
            if (
                $this->shop_model->addContactUsRecord([
                    'user_id' => $this->session->userdata('user_id') ? $this->session->userdata('user_id') : null,
                    //'type' => $_POST['type'],
                    'type' => 'Inquiry',
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'phone' => $_POST['phone'],
                    'content' => $_POST['content']
                ])
            )
                // TODO(Will sent an email notification to the admin here)
                $this->session->set_flashdata('success_message', 'Feedback submitted successfully!');
            ;
        }

        $this->data['page_title'] = "Contact Us";
        $this->data['title'] = "Contact Us";
        $this->data['all_categories'] = $this->shop_model->getAllCategories();
        $this->page_construct('pages/contact_us', $this->data);
        $this->session->unset_userdata('success_message');
    }

    // Display Page
    public function page($slug)
    {
        $page = $this->shop_model->getPageBySlug($slug);
        if (!$page) {
            redirect('notify/error_404');
        }
        $this->data['page'] = $page;
        $this->data['page_title'] = $page->title;
        $this->data['page_desc'] = $page->description;
        $this->data['all_categories'] = $this->shop_model->getAllCategories();


        $this->page_construct('pages/page', $this->data);
    }

    // Display blog page
    public function blog($slug = NULL)
    {
        if ($slug == NULL) {
            $this->data['blogs'] = $this->shop_model->get_all_records();
            $this->data['page'] = 'blog_page';

            $this->data['page_title'] = 'blog';
            $this->data['page_desc'] = '';
            $this->page_construct('pages/blog_page', $this->data);
        } else {
            $page = $this->shop_model->getBlogBySlug($slug);
            if (!$page) {
                redirect('/');
            }
            $this->data['page'] = $page;
            $this->data['page_title'] = $page->title;
            $this->data['page_desc'] = $page->description;
            $this->page_construct('pages/blog', $this->data);
        }

    }

    public function get_all_data()
    {
        $this->data['demo'] = $this->Shop_model->get_all_records();
        $this->load->view('pages/blog_page', $this->data);
        // $this->page_construct('pages/blog_page', $demo);
    }

    // Display Page
    public function product($slug)
    {
        $this->load->admin_model('seo_model');
        $this->load->admin_model('inventory_model');
        $product = $this->shop_model->getProductBySlug($slug);

       $new_stock = $this->inventory_model->get_current_stock($product->id, 'null');
       $onhold_stock = $this->inventory_model->get_onhold_stock($product->id);
       $new_quantity = $new_stock - $onhold_stock;
       $product->quantity = $new_quantity;

        $warehouse_quantities = $this->shop_model->getProductQuantitiesInWarehouses($product->id);
        foreach ($warehouse_quantities as $wh_quantity){
            if(($wh_quantity->warehouse_id == '7' && $wh_quantity->quantity > 0 && $product->id != 3)){
                //$virtual_pharmacy_items += $wh_quantity->quantity;
                $product->global = 1;
            }

            // remove the below block after eid
            // if(($wh_quantity->warehouse_id == '6' && $wh_quantity->quantity > 0 && $product->id != 3)){
                        
            //     $product->global = 1;
            // }

            // remove the below block after eid
            // if(($wh_quantity->warehouse_id == '1' && $wh_quantity->quantity > 0 && $product->id != 3)){
                
            //     $product->global = 1;
            // }
        }

        if (!$slug || !$product) {
            $this->session->set_flashdata('error', lang('product_not_found'));
            $this->sma->md('/');
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $product->code . '/' . $product->barcode_symbology . '/40/0') . "' alt='" . $product->code . "' class='pull-left' />";
        if ($product->type == 'combo') {
            $this->data['combo_items'] = $this->shop_model->getProductComboItems($product->id);
        }
        $this->shop_model->updateProductViews($product->id, $product->views);

        $product->promotion = $this->sma->isPromo($product) ? 1 : 0;

        if ($product->tax_method == '1' && $product->taxPercentage > 0) { // tax_method = 0 means inclusiveTax
            $productTaxPercent = $product->taxPercentage;

            if ($product->promotion == 1) {
                $productPromoPrice = $product->promo_price;
                $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                $product->promo_price = $productPromoPrice + $promoProductTaxAmount;
            }

            $productPrice = $product->price;
            $productTaxAmount = $productPrice * ($productTaxPercent / 100);
            $product->price = $productPrice + $productTaxAmount;
        }

        $this->site->logVisitor();

        $this->data['product'] = $product;
        $this->data['other_products'] = $this->shop_model->getOtherProducts($product->id, $product->category_id, $product->brand);
        $this->data['unit'] = $this->site->getUnitByID($product->unit);
        $this->data['brand'] = $this->site->getBrandByID($product->brand);
        $this->data['images'] = $this->shop_model->getProductPhotos($product->id);
        $this->data['category'] = $this->site->getCategoryByID($product->category_id);
        $this->data['customer_also_viewed'] = $this->shop_model->getCustomerAlsoViewed($product->category_id);
        $this->data['customers_also_bought'] = $this->shop_model->getCustomersAlsoBought($product->id);
        $this->data['subcategory'] = $product->subcategory_id ? $this->site->getCategoryByID($product->subcategory_id) : null;
        $this->data['tax_rate'] = $product->tax_rate ? $this->site->getTaxRateByID($product->tax_rate) : null;
        $this->data['warehouse'] = $this->shop_model->getAllWarehouseWithPQ($product->id);
        $this->data['options'] = $this->shop_model->getProductOptionsWithWH($product->id);
        $this->data['variants'] = $this->shop_model->getProductOptions($product->id);
        $this->load->helper('text');
        $this->data['page_title'] = $product->code . ' - ' . $product->name;
        $this->data['all_categories'] = $this->shop_model->getAllCategories();
        $this->data['page_desc'] = character_limiter(strip_tags($product->product_details), 160);
        $this->data['seoSetting'] = $this->seo_model->getSeoSettings(); 
        $this->data['new_stock'] = $new_stock;
        $this->data['onhold_stock'] = $onhold_stock;
        $this->data['new_quantity'] = $new_quantity;
        $this->page_construct('pages/view_product', $this->data);
    }

    // Ratings and Reviews

    public function rateAndReview()
    {
        if (!$this->loggedIn) {
            redirect('login');
        }
        if ($this->Staff) {
            admin_redirect('customers');
        }
        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->data['page_title'] = lang('rate_and_review');
        $this->data['page_desc'] = '';

        // Extract product IDs from reviewProducts
        $products = $this->shop_model->getOrderItemsByCustomer($this->session->userdata('company_id'));
        $reviewProducts = $this->shop_model->getOrderItemsReviewByCustomer($this->session->userdata('company_id'));
        $reviewedProductIds = array_map(function ($reviewProduct) {
            return $reviewProduct->product_id;
        }, $reviewProducts);

        // Filter products based on whether they have been reviewed
        $filteredProducts = array_filter($products, function ($product) use ($reviewedProductIds) {
            return !in_array($product->product_id, $reviewedProductIds);
        });

        $this->data['products'] = $filteredProducts;
        $this->data['reviewProducts'] = $reviewProducts;

        $this->page_construct('pages/rate_review_products', $this->data);
    }

    public function submit_review()
    {
        $data = [];
        $reviews = $this->input->post('reviews');
        if (!empty($reviews)) {
            foreach ($reviews as $product_id => $review) {
                if (isset($reviews[$product_id]['rating'][0]) && !empty($reviews[$product_id]['rating'][0])) {
                    $data[] = array(
                        'customer_id' => $this->session->userdata('company_id'),
                        'product_id' => $product_id,
                        'rating' => $reviews[$product_id]['rating'][0],
                        'review' => $reviews[$product_id]['review'][0],
                    );
                }
            }
        }
        if ($this->db->insert_batch('product_reviews', $data)) {
            $this->session->set_flashdata('submit_review_success', lang('submit_review_success'));
        } else {
            $this->session->set_flashdata('submit_review_error', lang('submit_review_error'));
        }

        redirect($_SERVER['HTTP_REFERER'] ?? '/shop/rateAndReview');
    }

    // Featured Products
    public function featured_products($category_slug = null, $subcategory_slug = null, $brand_slug = null, $promo = null)
    {


        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        if ($this->input->get('category')) {
            $category_slug = $this->input->get('category', true);

        }
        if ($this->input->get('brand')) {
            $brand_slug = $this->input->get('brand', true);
        }
        if ($this->input->get('promo') && $this->input->get('promo') == 'yes') {
            $promo = true;
        }

        if ($category_slug != null) {
            $this->data['featureImage'] = $this->shop_model->getCategoryBySlug($category_slug);
        }
        $reset = $category_slug || $subcategory_slug || $brand_slug ? true : false;

        $filters = [
            'query' => $this->input->post('query'),
            'category' => $category_slug ? $this->shop_model->getCategoryBySlug($category_slug) : null,
            'subcategory' => $subcategory_slug ? $this->shop_model->getCategoryBySlug($subcategory_slug) : null,
            'brand' => $brand_slug ? $this->shop_model->getBrandBySlug($brand_slug) : null,
            'promo' => $promo,
            'sorting' => $reset ? null : $this->input->get('sorting'),
            'min_price' => $reset ? null : $this->input->get('min_price'),
            'max_price' => $reset ? null : $this->input->get('max_price'),
            'in_stock' => $reset ? null : $this->input->get('in_stock'),
            'page' => $this->input->get('page') ? $this->input->get('page', true) : 1,
        ];
        $this->data['featured_products'] = $this->shop_model->getFeaturedProducts();
        $this->data['filtered_subcategories'] = $category_slug ? $this->shop_model->getSubCategories($filters['category']->id) : null;
        $this->data['filters'] = $filters;
        $this->data['all_categories'] = $this->shop_model->getAllCategories();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = (!empty($filters['category']) ? $filters['category']->name : (!empty($filters['brand']) ? $filters['brand']->name : lang('products'))) . ' - ' . $this->shop_settings->shop_name;
        $this->data['page_title2'] = (!empty($filters['category']) ? $filters['category']->name : (!empty($filters['brand']) ? $filters['brand']->name : lang('products')));
        $this->data['page_desc'] = !empty($filters['category']) ? $filters['category']->description : (!empty($filters['brand']) ? $filters['brand']->description : $this->shop_settings->products_description);
        $this->data['location'] = $this->shop_model->getProductLocation();
        if ($this->data == 'Saudi Arabia') {
            echo "Test";

        }
        $this->page_construct('pages/featured_products', $this->data);
    }

    public function bestsellers()
    {
        $data['filters'] = [
            'min_price' => $this->input->get('min_price'),
            'max_price' =>  $this->input->get('max_price'),
            'brands' =>  $this->input->get('brands'),
        ];
        $this->data['all_categories'] = $this->shop_model->getAllCategories();
        $this->data['location'] = $this->shop_model->getProductLocation();
        $this->data['best_sellers'] = $this->shop_model->getBestSellers(100, true, $data['filters']);
        $this->data['page_title'] = 'Best Sellers';
        
        
        $this->page_construct('pages/best_sellers', $this->data);
    }

    // Products,  categories and brands page
    public function products($category_slug = null, $subcategory_slug = null, $brand_slug = null, $promo = null)
    {
        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        $this->site->logVisitor();

        if ($this->input->get('category')) {
            $category_slug = $this->input->get('category', true);
        }
        if ($this->input->get('brand')) {
            $brand_slug = $this->input->get('brand', true);
        }
        if ($this->input->get('promo') && $this->input->get('promo') == 'yes') {
            $promo = true;
        }
        if ($this->input->get('special_product') && $this->input->get('special_product') == 'yes') {
            $special_product = true;
        }
        if ($category_slug != null) {
            $this->data['featureImage'] = $this->shop_model->getCategoryBySlug($category_slug);
        }
        $reset = $category_slug || $subcategory_slug || $brand_slug ? true : false;

        $filters = [
            'query' => $this->input->post('query'),
            'category' => $category_slug ? $this->shop_model->getCategoryBySlug($category_slug) : null,
            'subcategory' => $subcategory_slug ? $this->shop_model->getCategoryBySlug($subcategory_slug) : null,
            'brand' => $brand_slug ? $this->shop_model->getBrandBySlug($brand_slug) : null,
            'promo' => $promo,
            'special_product' => $special_product,
            'sorting' => $reset ? null : $this->input->get('sorting'),
            'min_price' => $this->input->get('min_price'),
            'max_price' =>  $this->input->get('max_price'),
            'brands' =>  $this->input->get('brands'),
            'in_stock' => $reset ? null : $this->input->get('in_stock'),
            'page' => $this->input->get('page') ? $this->input->get('page', true) : 1,
        ];
        $this->data['filtered_subcategories'] = $category_slug ? $this->shop_model->getSubCategories($filters['category']->id) : null;
        $this->data['filters'] = $filters;
        $this->data['all_categories'] = $this->shop_model->getAllCategories();
        $this->data['category_slug'] = $category_slug;
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = (!empty($filters['category']) ? $filters['category']->name : (!empty($filters['brand']) ? $filters['brand']->name : lang('products'))) . ' - ' . $this->shop_settings->shop_name;
        if ($this->input->get('promo') && $this->input->get('promo') == 'yes') {
            $this->data['page_title2'] = 'Promotions';
            $this->data['promo_banner'] = true;
        }else if(isset($filters['category']) && $filters['category']->id == 25){
            $this->data['suppliment_banner'] = true;
        }else{
            $this->data['page_title2'] = (!empty($filters['category']) ? $filters['category']->name : (!empty($filters['brand']) ? $filters['brand']->name : lang('products')));
        }
        if($brand_slug == 'honstHonst') {
            $this->data['honst_banner'] = true;
        }
        // $this->data['catBrands'] = $this->shop_model->getBrandsByCategoy($filters['category']->id);

        $this->data['page_desc'] = !empty($filters['category']) ? $filters['category']->description : (!empty($filters['brand']) ? $filters['brand']->description : $this->shop_settings->products_description);
        $this->data['location'] = $this->shop_model->getProductLocation();
        if ($this->data == 'Saudi Arabia') {
            echo "Test";

        }
        // echo "<pre>"; print_r($this->data); exit;
        $this->page_construct('pages/products', $this->data);
    }

    // Customer quotations
    public function quotes($id = null, $hash = null)
    {
        if (!$this->loggedIn && !$hash) {
            redirect('login');
        }
        if ($this->Staff) {
            admin_redirect('quotes');
        }
        if ($id) {
            if ($order = $this->shop_model->getQuote(['id' => $id, 'hash' => $hash])) {
                $this->load->library('inv_qrcode');
                $this->data['inv'] = $order;
                $this->data['rows'] = $this->shop_model->getQuoteItems($id);
                $this->data['customer'] = $this->site->getCompanyByID($order->customer_id);
                $this->data['biller'] = $this->site->getCompanyByID($order->biller_id);
                $this->data['created_by'] = $this->site->getUser($order->created_by);
                $this->data['updated_by'] = $this->site->getUser($order->updated_by);
                $this->data['page_title'] = lang('view_quote');
                $this->data['page_desc'] = '';
                $this->page_construct('pages/view_quote', $this->data);
            } else {
                $this->session->set_flashdata('error', lang('access_denied'));
                redirect('/');
            }
        } else {
            if ($this->input->get('download')) {
                $id = $this->input->get('download', true);
                $order = $this->shop_model->getQuote(['id' => $id]);
                $this->data['inv'] = $order;
                $this->data['rows'] = $this->shop_model->getQuoteItems($id);
                $this->data['customer'] = $this->site->getCompanyByID($order->customer_id);
                $this->data['biller'] = $this->site->getCompanyByID($order->biller_id);
                // $this->data['created_by'] = $this->site->getUser($order->created_by);
                // $this->data['updated_by'] = $this->site->getUser($order->updated_by);
                $this->data['Settings'] = $this->Settings;
                $html = $this->load->view($this->Settings->theme . '/shop/views/pages/pdf_quote', $this->data, true);
                if ($this->input->get('view')) {
                    echo $html;
                    exit;
                }
                $name = lang('quote') . '_' . str_replace('/', '_', $order->reference_no) . '.pdf';
                $this->sma->generate_pdf($html, $name);
            }
            $page = $this->input->get('page') ? $this->input->get('page', true) : 1;
            $limit = 10;
            $offset = ($page * $limit) - $limit;
            $this->load->helper('pagination');
            $total_rows = $this->shop_model->getQuotesCount();
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['orders'] = $this->shop_model->getQuotes($limit, $offset);
            $this->data['pagination'] = pagination('shop/quotes', $total_rows, $limit);
            $this->data['page_info'] = ['page' => $page, 'total' => ceil($total_rows / $limit)];
            $this->data['page_title'] = lang('my_orders');
            $this->data['page_desc'] = '';
            $this->page_construct('pages/quotes', $this->data);
        }
    }

    // Search products page - ajax
    public function search()
    {
        $filters = $this->input->post('filters') ? $this->input->post('filters', true) : [];
        $filters['min_price'] = $this->input->get('min_price');
        $filters['max_price'] =  $this->input->get('max_price');
        $filters['brands'] =  $this->input->get('brands');
        $limit = 60;
        $total_rows = $this->shop_model->getProductsCount($filters);
        $filters['limit'] = $limit;
        $filters['offset'] = isset($filters['page']) && !empty($filters['page']) && ($filters['page'] > 1) ? (($filters['page'] * $limit) - $limit) : null;

        if ($products = $this->shop_model->getProducts($filters)) {
            $this->load->helper(['text', 'pagination']);
            foreach ($products as &$value) {
                $value['details'] = character_limiter(strip_tags($value['details']), 140);
                if ($this->shop_settings->hide_price) {
                    $value['price'] = $value['formated_price'] = 0;
                    $value['promo_price'] = $value['formated_promo_price'] = 0;
                    $value['special_price'] = $value['formated_special_price'] = 0;
                } else {
                    $value['price'] = $this->sma->setCustomerGroupPrice($value['price'], $this->customer_group);
                    $value['formated_price'] = $this->sma->convertMoney($value['price']);
                    $value['promo_price'] = $this->sma->isPromo($value) ? $value['promo_price'] : $value['formated_price'];
                    $value['formated_promo_price'] = $this->sma->convertMoney($value['promo_price']);
                    $value['special_price'] = isset($value['special_price']) && !empty($value['special_price']) ? $this->sma->setCustomerGroupPrice($value['special_price'], $this->customer_group) : 0;
                    $value['formated_special_price'] = $this->sma->convertMoney($value['special_price']);
                }

                $value['promotion'] = $this->sma->isPromo($value) ? 1 : null;
            }

            $pagination = pagination('shop/products', $total_rows, $limit);

            //if (isset($_GET['promo']) && !empty($_GET['promo'])) {
            if(isset($filters['promo']) && $filters['promo'] == 1)  {
                $pagination = str_replace('?page=', '?promo=yes&page=', $pagination);
            }

            if (isset($filters['special_product']) && !empty($filters['special_product'])) {
                $pagination = str_replace('?page=', '?special_product=yes&page=', $pagination);
            }

            $info = ['page' => (isset($filters['page']) && !empty($filters['page']) ? $filters['page'] : 1), 'total' => ceil($total_rows / $limit)];

            $this->sma->send_json(['filters' => $filters, 'products' => $products, 'pagination' => $pagination, 'info' => $info]);
        } else {
            $this->sma->send_json(['filters' => $filters, 'products' => false, 'pagination' => false, 'info' => false]);
        }
    }

    // Send us email
    public function send_message()
    {
        $this->form_validation->set_rules('name', lang('name'), 'required');
        $this->form_validation->set_rules('email', lang('email'), 'required|valid_email');
        $this->form_validation->set_rules('subject', lang('subject'), 'required');
        $this->form_validation->set_rules('message', lang('message'), 'required');

        if ($this->form_validation->run() == true) {
            try {
                if ($this->sma->send_email($this->shop_settings->email, $this->input->post('subject'), $this->input->post('message'), $this->input->post('email'), $this->input->post('name'))) {
                    $this->sma->send_json(['status' => 'Success', 'message' => lang('message_sent')]);
                }
                $this->sma->send_json(['status' => 'error', 'message' => lang('action_failed')]);
            } catch (Exception $e) {
                $this->sma->send_json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } elseif ($this->input->is_ajax_request()) {
            $this->sma->send_json(['status' => 'Error!', 'message' => validation_errors(), 'level' => 'error']);
        } else {
            $this->session->set_flashdata('warning', 'Please try to send message from contact page!');
            shop_redirect();
        }
    }

    // Customer wishlist page
    public function wishlist()
    {
        if (!$this->loggedIn) {
            redirect('login');
        }
        $this->session->set_userdata('requested_page', $this->uri->uri_string());
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $total = $this->shop_model->getWishlist(true);
        $products = $this->shop_model->getWishlist();
        $this->load->helper('text');
        foreach ($products as $product) {
            $item = $this->shop_model->getProductByID($product->product_id);
            $item->details = character_limiter(strip_tags($item->details), 140);
            $items[] = $item;
        }
        $this->data['items'] = $products ? $items : null;
        $this->data['page_title'] = lang('wishlist');
        $this->data['page_desc'] = '';
        $this->page_construct('pages/wishlist', $this->data);
    }

    public function getArabicToEnglish($term) {
        // Set API endpoint and your API key
        $apiKey = 'wg_42c9daf242af8316a7b7d92e5a2aa0e55';
        $apiEndpoint = 'https://api.weglot.com/translate?api_key='.$apiKey;

        // Prepare the JSON payload
        // "الصفحة الرئيسية"
        $data = [
            "l_from" => "ar",
            "l_to" => "en",
            "request_url" => "https://www.avenzur.com/",
            "words" => [
                ["w" => $term, "t" => 1]
            ]
        ];

        // Convert the payload to JSON format
        $jsonData = json_encode($data);

        // Initialize cURL session
        $ch = curl_init($apiEndpoint);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);

        // Execute the POST request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            // Decode the response
            $responseData = json_decode($response, true);
            return $responseData;
        }
        // Close the cURL session
        curl_close($ch);
    }

    public function getEnglishToArabic($term) {
        // Set API endpoint and your API key
        $apiKey = 'wg_42c9daf242af8316a7b7d92e5a2aa0e55';
        $apiEndpoint = 'https://api.weglot.com/translate?api_key=' . $apiKey;
    
        // Prepare the JSON payload
        $data = [
            "l_to" => "ar",
            "l_from" => "en",
            "request_url" => "https://www.avenzur.com/",
            "words" => [
                ["w" => "$term", "t" => 1]
            ]
        ];
    
        // Convert the payload to JSON format
        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
        // Initialize cURL session
        $ch = curl_init();
    
        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ],
        ]);
    
        // Execute the POST request
        $response = curl_exec($ch);
    
        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            curl_close($ch);
            return null;
        } else {
            // Decode the response
            $responseData = json_decode($response, true);
            curl_close($ch);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo 'JSON decode error: ' . json_last_error_msg();
                return "JSON decode error.";
            }
    
            // Debug: Print the decoded response
            // var_dump($responseData['to_words'], $term);
    
            if (isset($responseData['to_words']) && is_array($responseData['to_words'])) {
                return $responseData['to_words'];
            } else {
                // Handle the case where the response doesn't have the expected data
                echo "Unexpected response format.";
                return "Translation error or unexpected response format.";
            }
        }
    }

    function containsArabic($text) {
        // Regular expression pattern to match Arabic characters
        $pattern = '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}\x{10E60}-\x{10E7F}]/u';
        
        // Check if the text matches the pattern
        if (preg_match($pattern, $text)) {
            return true; // The text contains Arabic characters
        } else {
            return false; // The text does not contain Arabic characters
        }
    }
    
    public function suggestions($pos = 0)
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->shop_settings->warehouse;
        $category_id = $this->input->get('category_id', true);
        //$customer_id  = $this->input->get('customer_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $convertToAr = false;
        // if ($this->containsArabic($sr)) {
        //     $convertToAr = true;
        //     $convertedData = $this->getArabicToEnglish($sr);
        //     $sr = isset($convertedData['to_words'][0]) ? $convertedData['to_words'][0] : "";
        // }

        //$option_id = $analyzed['option_id'];
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer_group = "Retail"; //$this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->shop_model->getProductNames($sr, $warehouse_id, $category_id, $pos);
        $currencies = $this->site->getAllCurrencies();

        $arabic_lang = false;
        if ($this->containsArabic($sr)) {
            $arabic_lang = true;
        }

        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {

                $c = uniqid(mt_rand(), true);
                unset($row->cost, $row->details, $row->product_details, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                
                // if ($convertToAr) {
                //     $convertedData = $this->getEnglishToArabic($row->name);
                //     $row->name = isset($convertedData[0]) ? $convertedData[0] : "";
                // }

                $option = false;
                $row->quantity = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty = 1;
                $row->discount = '0';
                $row->serial = '';
                $options = $this->shop_model->getProductOptions($row->id, $warehouse_id);

                $original_price = $row->price;
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->shop_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = false;
                }
                $row->option = $option_id;
                $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                // echo '<pre>',print_r($pis);exit;
                if ($pis) {
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
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
                }

                $row->real_unit_price = $row->price;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->price;
                $row->customer_group = $customer_group;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment = '';

                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->shop_model->getProductComboItems($row->id, $warehouse_id);
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                // New tax block

                if ($row->tax_method == '1' && $tax_rate->rate > 0) { // tax_method = 0 means inclusiveTax
                    $productTaxPercent = $tax_rate->rate;
        
                    if ($row->promotion == 1) {
                        $productPromoPrice = $row->promo_price;
                        $promoProductTaxAmount = $productPromoPrice * ($productTaxPercent / 100);
                        $row->promo_price = $productPromoPrice + $promoProductTaxAmount;
                    }
        
                    $productPrice = $row->price;
                    $productTaxAmount = $productPrice * ($productTaxPercent / 100);
                    $row->price = $productPrice + $productTaxAmount;

                    $original_price_tax = $original_price * ($productTaxPercent / 100);
                    $original_price = $original_price + $original_price_tax;
                }

                // New tax block end 

                $brand = $this->site->getBrandByID($row->brand);
                $row->brand_name = $brand->name;
                $label_name = ($arabic_lang) ? $row->name_ar : $row->name;
                $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'original_price' => $original_price, 'image' => $row->image, 'label' => $label_name, 'category' => $row->category_id,
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'plink' => base_url() . 'product/' . $row->slug];
                $r++;
            }
            $this->sma->send_json($pr);

        } else {
            /*$rows = $this->shop_model->getProductBrandsByName($sr);
            if($rows){
                $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
            }else{
                $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
            }*/
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function refundData()
    {
        $dt = date('Y-m-d');
        ;

        $data = array(

            'order_id' => $this->input->get('order_id'),
            'user_id' => $this->input->get('customer_id'),
            'req_dates' => $dt,
            'reason_refund' => $this->input->get('reason_refund'),
            'notes' => $this->input->get('notes')
        );

        $this->load->model('shop_model');
        $result = $this->shop_model->saveRefundRecord($data);
        if ($result) {
            echo 1;
        } else {
            echo 0;
        }
    }

    public function currencyupdate()
    {
        $currency_name = $this->input->get('currencyName');
        //$this->session->set_userdata('country',$country_id);
        set_cookie('shop_currency', $currency_name, 31536000);
        //$this->warehouse = $this->shop_model->getwharehouseID($country_id);
        echo 1;
    }

    public function globalupdate()
    {
        $country_id = $this->input->get('countryName');
        //$this->session->set_userdata('country',$country_id);
        set_cookie('shop_country', $country_id, 31536000);
        //$this->warehouse = $this->shop_model->getwharehouseID($country_id);
        echo 1;
    }


}
