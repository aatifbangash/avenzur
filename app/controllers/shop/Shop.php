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
    }

    // Add/edit customer address
    public function address($id = null)
    {
            
        if (!$this->loggedIn) {
            $this->sma->send_json(['status' => 'error', 'message' => lang('please_login')]);
        }
        $this->form_validation->set_rules('line1', lang('line1'), 'trim|required');
        // $this->form_validation->set_rules('line2', lang("line2"), 'trim|required');
        $this->form_validation->set_rules('city', lang('city'), 'trim|required');
        $this->form_validation->set_rules('state', lang('state'), 'trim|required');
        // $this->form_validation->set_rules('postal_code', lang("postal_code"), 'trim|required');
        $this->form_validation->set_rules('country', lang('country'), 'trim|required');
        $this->form_validation->set_rules('phone', lang('phone'), 'trim|required');

        if ($this->form_validation->run() == true) {
            $user_addresses = $this->shop_model->getAddresses();
            if (count($user_addresses) >= 6) {
                $this->sma->send_json(['status' => 'error', 'message' => lang('already_have_max_addresses'), 'level' => 'error']);
            }

            $data = ['line1'  => $this->input->post('line1'),
                'line2'       => $this->input->post('line2'),
                'phone'       => $this->input->post('phone'),
                'city'        => $this->input->post('city'),
                'state'       => $this->input->post('state'),
                'postal_code' => $this->input->post('postal_code'),
                'country'     => $this->input->post('country'),
                'company_id'  => $this->session->userdata('company_id'), ];

            if ($id) {
                $this->db->update('addresses', $data, ['id' => $id]);
                $this->session->set_flashdata('message', lang('address_updated'));
                $this->sma->send_json(['redirect' => $_SERVER['HTTP_REFERER']]);
            } else {
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
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['addresses']  = $this->shop_model->getAddresses();
        $this->data['page_title'] = lang('my_addresses');
        $this->data['page_desc']  = '';
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
            $page   = $this->input->get('page') ? $this->input->get('page', true) : 1;
            $limit  = 10;
            $offset = ($page * $limit) - $limit;
            $this->load->helper('pagination');
            $total_rows = $this->shop_model->getDownloadsCount();
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['downloads']  = $this->shop_model->getDownloads($limit, $offset);
            $this->data['pagination'] = pagination('shop/download', $total_rows, $limit);
            $this->data['page_info']  = ['page' => $page, 'total' => ceil($total_rows / $limit)];
            $this->data['page_title'] = lang('my_downloads');
            $this->data['page_desc']  = '';
            $this->page_construct('pages/downloads', $this->data);
        }
    }

    // Add attachment to sale on manual payment
    public function manual_payment($order_id)
    {
        if ($_FILES['payment_receipt']['size'] > 0) {
            $this->load->library('upload');
            $config['upload_path']   = 'files/';
            $config['allowed_types'] = 'zip|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
            $config['max_size']      = 2048;
            $config['overwrite']     = false;
            $config['max_filename']  = 25;
            $config['encrypt_name']  = true;
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('payment_receipt')) {
                $error = $this->upload->display_errors();
                $this->session->set_flashdata('error', $error);
                redirect($_SERVER['HTTP_REFERER']);
            }
            $attachment = [
                'subject_type' => 'sale',
                'subject_id'   => $order_id,
                'file_name'    => $this->upload->data('file_name'),
                'orig_name'    => $this->upload->data('orig_name'),
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
        $this->form_validation->set_rules('address', lang('address'), 'trim|required');
        $this->form_validation->set_rules('note', lang('comment'), 'trim');
        $this->form_validation->set_rules('payment_method', lang('payment_method'), 'required');
        if ($guest_checkout) {
            $this->form_validation->set_rules('name', lang('name'), 'trim|required');
            $this->form_validation->set_rules('email', lang('email'), 'trim|required|valid_email');
            $this->form_validation->set_rules('phone', lang('phone'), 'trim|required');
            $this->form_validation->set_rules('billing_line1', lang('billing_address') . ' ' . lang('line1'), 'trim|required');
            $this->form_validation->set_rules('billing_city', lang('billing_address') . ' ' . lang('city'), 'trim|required');
            $this->form_validation->set_rules('billing_country', lang('billing_address') . ' ' . lang('country'), 'trim|required');
            $this->form_validation->set_rules('shipping_line1', lang('shipping_address') . ' ' . lang('line1'), 'trim|required');
            $this->form_validation->set_rules('shipping_city', lang('shipping_address') . ' ' . lang('city'), 'trim|required');
            $this->form_validation->set_rules('shipping_country', lang('shipping_address') . ' ' . lang('country'), 'trim|required');
            $this->form_validation->set_rules('shipping_phone', lang('shipping_address') . ' ' . lang('phone'), 'trim|required');
        }
        if ($guest_checkout && $this->Settings->indian_gst) {
            $this->form_validation->set_rules('billing_state', lang('billing_address') . ' ' . lang('state'), 'trim|required');
            $this->form_validation->set_rules('shipping_state', lang('shipping_address') . ' ' . lang('state'), 'trim|required');
        }

        if ($this->form_validation->run() == true) {
            if ($guest_checkout || $address = $this->shop_model->getAddressByID($this->input->post('address'))) {
                $new_customer = false;
                if ($guest_checkout) {
                    $address = [
                        'phone'       => $this->input->post('shipping_phone'),
                        'line1'       => $this->input->post('shipping_line1'),
                        'line2'       => $this->input->post('shipping_line2'),
                        'city'        => $this->input->post('shipping_city'),
                        'state'       => $this->input->post('shipping_state'),
                        'postal_code' => $this->input->post('shipping_postal_code'),
                        'country'     => $this->input->post('shipping_country'),
                    ];
                }
                if ($this->input->post('address') != 'new') {
                    $customer = $this->site->getCompanyByID($this->session->userdata('company_id'));
                } else {
                    if (!($customer = $this->shop_model->getCompanyByEmail($this->input->post('email')))) {
                        $customer                      = new stdClass();
                        $customer->name                = $this->input->post('name');
                        $customer->company             = 'Pharma Drug Store';//$this->input->post('company');
                        $customer->phone               = $this->input->post('phone');
                        $customer->email               = $this->input->post('email');
                        $customer->address             = $this->input->post('billing_line1') . '<br>' . $this->input->post('billing_line2');
                        $customer->city                = $this->input->post('billing_city');
                        $customer->state               = $this->input->post('billing_state');
                        $customer->postal_code         = $this->input->post('billing_postal_code');
                        $customer->country             = $this->input->post('billing_country');
                        $customer->group_id            = 3;
                        $customer->group_name          = 'customer';
                        $customer->country             = $this->input->post('billing_country');
                        $customer_group                = $this->shop_model->getCustomerGroup($this->Settings->customer_group);
                        $price_group                   = $this->shop_model->getPriceGroup($this->Settings->price_group);
                        $customer->customer_group_id   = (!empty($customer_group)) ? $customer_group->id : null;
                        $customer->customer_group_name = (!empty($customer_group)) ? $customer_group->name : null;
                        $customer->price_group_id      = (!empty($price_group)) ? $price_group->id : null;
                        $customer->price_group_name    = (!empty($price_group)) ? $price_group->name : null;
                        $new_customer                  = true;
                    }
                }
                $biller      = $this->site->getCompanyByID($this->shop_settings->biller);
                $note        = $this->db->escape_str($this->input->post('comment'));
                $product_tax = 0;
                $total       = 0;
                $gst_data    = [];
                $pro_weight = [];
                $total_cgst  = $total_sgst  = $total_igst  = 0;
                foreach ($this->cart->contents() as $item) {
                    $item_option = null;
                    if ($product_details = $this->shop_model->getProductForCart($item['product_id'])) {
                        $price = $this->sma->setCustomerGroupPrice(($this->loggedIn && isset($product_details->special_price) ? $product_details->special_price : $product_details->price), $this->customer_group);
                        $price = $this->sma->isPromo($product_details) ? $product_details->promo_price : $price;
                        if ($item['option']) {
                            if ($product_variant = $this->shop_model->getProductVariantByID($item['option'])) {
                                $item_option = $product_variant->id;
                                $price       = $product_variant->price + $price;
                            }
                        }

                        $item_net_price = $unit_price = $price;
                        $item_quantity  = $item_unit_quantity  = $item['qty'];
                        $pr_item_tax    = $item_tax    = 0;
                        $tax            = '';

                        if (!empty($product_details->tax_rate)) {
                            $tax_details = $this->site->getTaxRateByID($product_details->tax_rate);
                            $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                            $item_tax    = $ctax['amount'];
                            $tax         = $ctax['tax'];
                            if ($product_details->tax_method != 1) {
                                $item_net_price = $unit_price - $item_tax;
                            }
                            $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
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
                            'product_id'        => $product_details->id,
                            'product_code'      => $product_details->code,
                            'product_name'      => $product_details->name,
                            'product_type'      => $product_details->type,
                            'option_id'         => $item_option,
                            'net_unit_price'    => $item_net_price,
                            'unit_price'        => $this->sma->formatDecimal($item_net_price + $item_tax),
                            'quantity'          => $item_quantity,
                            'product_unit_id'   => $unit ? $unit->id : null,
                            'product_unit_code' => $unit ? $unit->code : null,
                            'unit_quantity'     => $item_unit_quantity,
                            'warehouse_id'      => $this->shop_settings->warehouse,
                            'item_tax'          => $pr_item_tax,
                            'tax_rate_id'       => $product_details->tax_rate,
                            'tax'               => $tax,
                            'discount'          => null,
                            'item_discount'     => 0,
                            'subtotal'          => $this->sma->formatDecimal($subtotal),
                            'serial_no'         => null,
                            'real_unit_price'   => $price,
                        ];
                        $ww =  $this->shop_model->getProductByID($product_details->id);
                         $ww2 = array('product_weight' => $ww->weight);
                         $pro_weight[] = $ww2;
                        $products[] = ($product + $gst_data );
                        $total += $this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                    } else {
                        $this->session->set_flashdata('error', lang('product_x_found') . ' (' . $item['name'] . ')');
                        redirect($_SERVER['HTTP_REFERER'] ?? 'cart');
                    }
                }

                $shipping    = $this->shop_settings->shipping;
                $order_tax   = $this->site->calculateOrderTax($this->Settings->default_tax_rate2, ($total + $product_tax));
                $total_tax   = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
                $grand_total = $this->sma->formatDecimal(($total + $total_tax + $shipping), 4);

                $data = [
                    'date'              => date('Y-m-d H:i:s'),
                    'reference_no'      => $this->site->getReference('so'),
                    'customer_id'       => $customer->id ?? '',
                    'customer'          => ($customer->company && $customer->company != '-' ? $customer->company : $customer->name),
                    'biller_id'         => $biller->id,
                    'biller'            => ($biller->company && $biller->company != '-' ? $biller->company : $biller->name),
                    'warehouse_id'      => $this->shop_settings->warehouse,
                    'note'              => $note,
                    'staff_note'        => null,
                    'total'             => $total,
                    'product_discount'  => 0,
                    'order_discount_id' => null,
                    'order_discount'    => 0,
                    'total_discount'    => 0,
                    'product_tax'       => $product_tax,
                    'order_tax_id'      => $this->Settings->default_tax_rate2,
                    'order_tax'         => $order_tax,
                    'total_tax'         => $total_tax,
                    'shipping'          => $shipping,
                    'grand_total'       => $grand_total,
                    'total_items'       => $this->cart->total_items(),
                    'sale_status'       => 'pending',
                    'payment_status'    => 'pending',
                    'payment_term'      => null,
                    'due_date'          => null,
                    'paid'              => 0,
                    'created_by'        => $this->session->userdata('user_id') ? $this->session->userdata('user_id') : null,
                    'shop'              => 1,
                    'address_id'        => ($this->input->post('address') == 'new') ? '' : $address->id,
                    'hash'              => hash('sha256', microtime() . mt_rand()),
                    'payment_method'    => $this->input->post('payment_method'),
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
                   
                if ($sale_id = $this->shop_model->addSale($data, $products, $customer, $address)) {
                    
                   $this->aramexshipment($sale_id, $data, $products, $customer, $address,$pro_weight);
                    // $email = $this->order_received($sale_id, $data['hash']);
                     
                    //if (!$email['sent']) {
                      //  $this->session->set_flashdata('error', $email['error']);
                    //}
                   // $this->load->library('sms');
                   // $this->sms->newSale($sale_id);
                    $this->cart->destroy();
                    $this->session->set_flashdata('info', lang('order_added_make_payment'));
                    if ($this->input->post('payment_method') == 'paypal') {
                        redirect('pay/paypal/' . $sale_id);
                    } elseif ($this->input->post('payment_method') == 'skrill') {
                        redirect('pay/skrill/' . $sale_id);
                    } elseif ($this->input->post('payment_method') == 'directpay') {
                        redirect('pay/directpay/' . $sale_id);
                    }else {
                        shop_redirect('orders/' . $sale_id . '/' . ($this->loggedIn ? '' : $data['hash']));
                    }
                }
            } else {
                $this->session->set_flashdata('error', lang('address_x_found'));
                redirect($_SERVER['HTTP_REFERER'] ?? 'cart/checkout');
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect('cart/checkout' . ($guest_checkout ? '#guest' : ''));
        }
    }
    
   public function aramexshipment($sale_id, $data, $products, $customer, $address, $pro_weight)
    {
        $dp = $this->shop_model->getAramexSettings();

        if($dp->activation == 1)
        {
            $p_accountnumber = $dp->account_number ; //'71449672';
                $p_line1 = $dp->line1 ; //"Al kharaj";
                $p_city = $dp->city ; //"Riyadh";
                $p_postcode = $dp->postal_code ; //"11663";
                $p_countrycode = $dp->country_code ; //"SA";
                $p_personname = $dp->person_name ; //"Amr";
                $p_companyname = $dp->company_name ; //"Pharma drug store";
                $p_phonenumber = $dp->landline_number ; //"966568241418";
                $p_cellnumber = $dp->cell_number ; //"966568241418";
                $p_shipper_email    = $dp->Email ; //"ama@pharma.com.sa";
                
                $p_AccountEntity = $dp->account_entity ; //'RUH';
                $p_AccountNumber  = $dp->account_number ; //'71449672';
                $p_AccountPin = $dp->account_pin ; //'107806';
                $p_UserName = $dp->user_name ; //'testingapi@aramex.com';
                $p_Password = $dp->password ; //'R123456789$r';
                $p_Version = $dp->version ; //'1.0';

                $p_soapLink = $dp->shippment_url;

        }else
        {
            $p_accountnumber = '71449672';
                $p_line1 = "Al kharaj";
                $p_city = "Riyadh";
                $p_postcode = "11663";
                $p_countrycode = "SA";
                $p_personname = "Amr";
                $p_companyname = "Pharma drug store";
                $p_phonenumber = "966568241418";
                $p_cellnumber = "966568241418";
                $p_shipper_email    = "ama@pharma.com.sa";
                
                $p_AccountEntity = 'RUH';
                $p_AccountNumber  = '71449672';
                $p_AccountPin = '107806';
                $p_UserName = 'testingapi@aramex.com';
                $p_Password = 'R123456789$r';
                $p_Version = '1.0';

                $p_soapLink = $dp->test_shippment_url;//'https://ws.sbx.aramex.net/ShippingAPI.V2/Shipping/Service_1_0.svc?wsdl';
        }


        $soapClient = new SoapClient($p_soapLink);
            	//echo '<pre>';
            	//print_r($soapClient->__getFunctions());
            	
            	
            	$p_sale_id = $sale_id;
            	$p_transaction = (int)(microtime(true) * 1000);
            	
            	
            	$cutomer_array  = (array)$customer;
                 
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
            	$c_CompanyName = 'Pharma Drug Store';//$cutomer_array['company'];
            	$c_State = $cutomer_array['state'];
            
                $product_weight = 0.0;
                $product_unit = 'Kg';
                $params = array();
                
                foreach($pro_weight as $w)
                {
                    $product_weight += $w['product_weight'];
                }
                foreach($products as $product)
                {
                    //$product_weight += $product['product_weight'];
                    
                    $params['Shipments']['Shipment']['Details']['Items'][] = array(
                                                		'PackageType' 	=> 'Box',
                                                		'Quantity'		=> $product['quantity'],
                                                		'Weight'		=> array(
                                                				'Value'		=> '0.20',//$product['product_weight'],
                                                				'Unit'		=> 'Kg',		
                                                		),
                                                		'Comments'		=> 'Medicine Boxes',
                                                		'Reference'		=> ''
                                                	);
                }
            	
            //var_dump($pro_weight);
        	$params = array(
        			'Shipments' => array(
        				'Shipment' => array(
        						'Shipper'	=> array(
        										'Reference1' 	=> $p_transaction ,
        										'Reference2' 	=> $p_sale_id,
        										'AccountNumber' => $p_accountnumber,
        										'PartyAddress'	=> array(
        											'Line1'					=> $p_line1,
        											'Line2' 				=> '',
        											'Line3' 				=> '',
        											'City'					=> $p_city,
        											'StateOrProvinceCode'	=> '',
        											'PostCode'				=> $p_postcode,
        											'CountryCode'			=> $p_countrycode
        										),
        										'Contact'		=> array(
        											'Department'			=> '',
        											'PersonName'			=> $p_personname,
        											'Title'					=> '',
        											'CompanyName'			=> $p_companyname,
        											'PhoneNumber1'			=> $p_phonenumber,
        											'PhoneNumber1Ext'		=> '',
        											'PhoneNumber2'			=> '',
        											'PhoneNumber2Ext'		=> '',
        											'FaxNumber'				=> '',
        											'CellPhone'				=> $p_cellnumber,
        											'EmailAddress'			=> $p_shipper_email,
        											'Type'					=> ''
        										),
        						),
        												
        						'Consignee'	=> array(
        										'Reference1'	=> $p_transaction,
        										'Reference2'	=> $p_sale_id,
        										'AccountNumber' => $p_accountnumber,
        										'PartyAddress'	=> array(
        											'Line1'					=> $c_Line1,
        											'Line2'					=> '',
        											'Line3'					=> '',
        											'City'					=> $c_City,
        											'StateOrProvinceCode'	=> (!empty($c_State)) ? $c_State : '',
        											'PostCode'				=> (!empty($c_PostCode)) ? $c_PostCode : '',
        											'CountryCode'			=> $c_CountryCode
        										),
        										
        										'Contact'		=> array(
        											'Department'			=> '',
        											'PersonName'			=> $c_PersonName,
        											'Title'					=> '',
        											'CompanyName'			=> (!empty($c_CompanyName)) ? $c_CompanyName : '',
        											'PhoneNumber1'			=> $c_PhoneNumber,
        											'PhoneNumber1Ext'		=> '',
        											'PhoneNumber2'			=> '',
        											'PhoneNumber2Ext'		=> '',
        											'FaxNumber'				=> '',
        											'CellPhone'				=> $c_CellPhone,
        											'EmailAddress'			=> $c_EmailAddress,
        											'Type'					=> ''
        										),
        						),
        						
        						'ThirdParty' => array(
        										'Reference1' 	=> '',
        										'Reference2' 	=> '',
        										'AccountNumber' => '',
        										'PartyAddress'	=> array(
        											'Line1'					=> '',
        											'Line2'					=> '',
        											'Line3'					=> '',
        											'City'					=> '',
        											'StateOrProvinceCode'	=> '',
        											'PostCode'				=> '',
        											'CountryCode'			=> ''
        										),
        										'Contact'		=> array(
        											'Department'			=> '',
        											'PersonName'			=> '',
        											'Title'					=> '',
        											'CompanyName'			=> '',
        											'PhoneNumber1'			=> '',
        											'PhoneNumber1Ext'		=> '',
        											'PhoneNumber2'			=> '',
        											'PhoneNumber2Ext'		=> '',
        											'FaxNumber'				=> '',
        											'CellPhone'				=> '',
        											'EmailAddress'			=> '',
        											'Type'					=> ''							
        										),
        						),
        						
        						'Reference1' 				=> $p_transaction ,
        						'Reference2' 				=> $p_sale_id,
        						'Reference3' 				=> '',
        						'ForeignHAWB'				=> $p_transaction,
        						'TransportType'				=> 0,
        						'ShippingDateTime' 			=> time(),
        						'DueDate'					=> time(),
        						'PickupLocation'			=> 'Reception',
        						'PickupGUID'				=> '',
        						'Comments'					=> $p_sale_id,
        						'AccountingInstrcutions' 	=> '',
        						'OperationsInstructions'	=> '',
        						
        						'Details' => array(
        										'Dimensions' => array(
        											'Length'				=> '',
        											'Width'					=> '',
        											'Height'				=> '',
        											'Unit'					=> 'cm',
        											
        										),
        										
        										'ActualWeight' => array(
        											'Value'					=> ($product_weight >= 1) ? $product_weight : 1.0 ,
        											'Unit'					=> 'Kg'
        										),
        										
        										'ProductGroup' 			=> 'EXP',
        										'ProductType'			=> 'PDX',
        										'PaymentType'			=> 'P',
        										'PaymentOptions' 		=> '',
        										'Services'				=> '',
        										'NumberOfPieces'		=> 1,
        										'DescriptionOfGoods' 	=> 'Medicine',
        										'GoodsOriginCountry' 	=> $p_countrycode,
        										
        										'CashOnDeliveryAmount' 	=> array(
        											'Value'					=> 0,
        											'CurrencyCode'			=> ''
        										),
        										
        										'InsuranceAmount'		=> array(
        											'Value'					=> 0,
        											'CurrencyCode'			=> ''
        										),
        										
        										'CollectAmount'			=> array(
        											'Value'					=> 0,
        											'CurrencyCode'			=> ''
        										),
        										
        										'CashAdditionalAmount'	=> array(
        											'Value'					=> 0,
        											'CurrencyCode'			=> ''							
        										),
        										
        										'CashAdditionalAmountDescription' => '',
        										
        										'CustomsValueAmount' => array(
        											'Value'					=> 0,
        											'CurrencyCode'			=> ''								
        										),
        										
        										'Items' 				=> array(
        											
        										)
        						),
        				),
        		),
        		
        			'ClientInfo'  			=> array(
        										'AccountCountryCode'	=> $p_countrycode,
        										'AccountEntity'		 	=> $p_AccountEntity,
        										'AccountNumber'		 	=> $p_AccountNumber,
        										'AccountPin'		 	=> $p_AccountPin,
        										'UserName'			 	=> $p_UserName,
        										'Password'			 	=> $p_Password,
        										'Version'			 	=> $p_Version
        									),
        
        			'Transaction' 			=> array(
        										'Reference1'			=> $p_transaction ,
        										'Reference2'			=> $p_sale_id, 
        										'Reference3'			=> '', 
        										'Reference4'			=> '', 
        										'Reference5'			=> '',									
        									),
        			'LabelInfo'				=> array(
        										'ReportID' 				=> 9202,
        										'ReportType'			=> 'URL',
        			),
        	);
        	
        	
        	
     
        	
        	//print_r($params);
        	
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
        		$data = json_decode(json_encode($auth_call), true);//(array)$auth_call;
        		
        		
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
        		$this->shop_model->salesarrAdd($p_sale_id,$salesarr);
        		/*echo "Salesid ".$data["Transaction"]["Reference1"].'<br>';
        		echo "Reference ".$data["Transaction"]["Reference2"].'<br>';
        		echo "HasErrors: ".$data["HasErrors"]."<br>";
        		echo "Shipments ID: ".$data['Shipments']["ProcessedShipment"]["ID"]."<br>";
        		echo "Shipments Label URL: ".$data['Shipments']["ProcessedShipment"]["ShipmentLabel"]["LabelURL"]."<br>";
        		echo "Shipment Origin: ".$data['Shipments']["ProcessedShipment"]["ShipmentDetails"]["Origin"]."<br>";
        		echo "Shipment Destination: ".$data['Shipments']["ProcessedShipment"]["ShipmentDetails"]["Destination"]."<br>";*/
        		
        	//	die();
        	} catch (SoapFault $fault) {
        		//die('Error : ' . $fault->faultstring);
        	}
        	return;
    }

    public function order_received($id = null, $hash = null)
    {
        if ($inv = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {
            $user     = $inv->created_by ? $this->site->getUser($inv->created_by) : null;
            $customer = $this->site->getCompanyByID($inv->customer_id);
            $biller   = $this->site->getCompanyByID($inv->biller_id);
            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $inv->reference_no,
                'contact_person'   => $customer->name,
                'company'          => $customer->company && $customer->company != '-' ? '(' . $customer->company . ')' : '',
                'order_link'       => shop_url('orders/' . $id . '/' . ($this->loggedIn ? '' : $inv->hash)),
                'site_link'        => base_url(),
                'site_name'        => $this->Settings->site_name,
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $biller->logo . '" alt="' . ($biller->company && $biller->company != '-' ? $biller->company : $biller->name) . '"/>',
            ];
            $msg     = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/sale.html');
            $message = $this->parser->parse_string($msg, $parse_data);
            $this->load->model('pay_model');
            $paypal   = $this->pay_model->getPaypalSettings();
            $skrill   = $this->pay_model->getSkrillSettings();
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
            $message    = $message . $btn_code;
            $attachment = $this->orders($id, $hash, true, 'S');
            $subject    = lang('new_order_received');
            $sent       = false;
            $error      = false;
            $cc         = [];
            $bcc        = [];
            if ($user) {
                $cc[] = $customer->email;
            }
            $cc[]      = $biller->email;
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

    // Customer order/orders page
    public function orders($id = null, $hash = null, $pdf = null, $buffer_save = null)
    {
        $hash = $hash ? $hash : $this->input->get('hash', true);
        if (!$this->loggedIn && !$hash) {
            redirect('login');
        }
        if ($this->Staff) {
            admin_redirect('sales');
        }
        if ($id && !$pdf) {
            if ($order = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash])) {
                $this->load->library('inv_qrcode');
                $this->data['inv']         = $order;
                $this->data['rows']        = $this->shop_model->getOrderItems($id);
                $this->data['customer']    = $this->site->getCompanyByID($order->customer_id);
                $this->data['biller']      = $this->site->getCompanyByID($order->biller_id);
                $this->data['address']     = $this->shop_model->getAddressByID($order->address_id);
                $this->data['return_sale'] = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
                $this->data['return_rows'] = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
                $this->data['paypal']      = $this->shop_model->getPaypalSettings();
                $this->data['skrill']      = $this->shop_model->getSkrillSettings();
                $this->data['page_title']  = lang('view_order');
                $this->data['page_desc']   = '';

                $this->config->load('payment_gateways');
                $this->data['stripe_secret_key']      = $this->config->item('stripe_secret_key');
                $this->data['stripe_publishable_key'] = $this->config->item('stripe_publishable_key');
                $this->page_construct('pages/view_order', $this->data);
            } else {
                $this->session->set_flashdata('error', lang('access_denied'));
                redirect('/');
            }
        } elseif ($pdf || $this->input->get('download')) {
            $id                          = $pdf ? $id : $this->input->get('download', true);
            $hash                        = $hash ? $hash : $this->input->get('hash', true);
            $order                       = $this->shop_model->getOrder(['id' => $id, 'hash' => $hash]);
            $this->data['inv']           = $order;
            $this->data['rows']          = $this->shop_model->getOrderItems($id);
            $this->data['customer']      = $this->site->getCompanyByID($order->customer_id);
            $this->data['biller']        = $this->site->getCompanyByID($order->biller_id);
            $this->data['address']       = $this->shop_model->getAddressByID($order->address_id);
            $this->data['return_sale']   = $order->return_id ? $this->shop_model->getOrder(['id' => $id]) : null;
            $this->data['return_rows']   = $order->return_id ? $this->shop_model->getOrderItems($order->return_id) : null;
            $this->data['Settings']      = $this->Settings;
            $this->data['shop_settings'] = $this->shop_settings;
            $html                        = $this->load->view($this->Settings->theme . '/shop/views/pages/pdf_invoice', $this->data, true);
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
            $page   = $this->input->get('page') ? $this->input->get('page', true) : 1;
            $limit  = 10;
            $offset = ($page * $limit) - $limit;
            $this->load->helper('pagination');
            $total_rows = $this->shop_model->getOrdersCount();
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['orders']     = $this->shop_model->getOrders($limit, $offset);
            $this->data['pagination'] = pagination('shop/orders', $total_rows, $limit);
            $this->data['page_info']  = ['page' => $page, 'total' => ceil($total_rows / $limit)];
            $this->data['page_title'] = lang('my_orders');
            $this->data['page_desc']  = '';
            $this->page_construct('pages/orders', $this->data);
        }
    }

    // Display Page
    public function page($slug)
    {
        $page = $this->shop_model->getPageBySlug($slug);
        if (!$page) {
            redirect('notify/error_404');
        }
        $this->data['page']       = $page;
        $this->data['page_title'] = $page->title;
        $this->data['page_desc']  = $page->description;
        
        
        $this->page_construct('pages/page', $this->data);
    }
    
    // Display blog page
    public function blog($slug = NULL)
    {
        if($slug == NULL)
        {
            $this->data['blogs']  = $this->shop_model->get_all_records();
            $this->data['page']       = 'blog_page';
            
            $this->data['page_title'] = 'blog';
            $this->data['page_desc']  = '';
             $this->page_construct('pages/blog_page', $this->data);
        }else{
            $page = $this->shop_model->getBlogBySlug($slug);
        if (!$page) {
            redirect('notify/error_404');
        }
        $this->data['page']       = $page;
        $this->data['page_title'] = $page->title;
        $this->data['page_desc']  = $page->description;
        $this->page_construct('pages/blog', $this->data);
        }
        
    }
     public  function get_all_data(){
                     $this->data['demo']  = $this->Shop_model->get_all_records();
                     $this->load->view('pages/blog_page',   $this->data);
                     // $this->page_construct('pages/blog_page', $demo);
                }

    // Display Page
    public function product($slug)
    {
      
        $product = $this->shop_model->getProductBySlug($slug);
        if (!$slug || !$product) {
            $this->session->set_flashdata('error', lang('product_not_found'));
            $this->sma->md('/');
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $product->code . '/' . $product->barcode_symbology . '/40/0') . "' alt='" . $product->code . "' class='pull-left' />";
        if ($product->type == 'combo') {
            $this->data['combo_items'] = $this->shop_model->getProductComboItems($product->id);
        }
        $this->shop_model->updateProductViews($product->id, $product->views);
        $this->data['product']        = $product;
        $this->data['other_products'] = $this->shop_model->getOtherProducts($product->id, $product->category_id, $product->brand);
        $this->data['unit']           = $this->site->getUnitByID($product->unit);
        $this->data['brand']          = $this->site->getBrandByID($product->brand);
        $this->data['images']         = $this->shop_model->getProductPhotos($product->id);
        $this->data['category']       = $this->site->getCategoryByID($product->category_id);
        $this->data['subcategory']    = $product->subcategory_id ? $this->site->getCategoryByID($product->subcategory_id) : null;
        $this->data['tax_rate']       = $product->tax_rate ? $this->site->getTaxRateByID($product->tax_rate) : null;
        $this->data['warehouse']      = $this->shop_model->getAllWarehouseWithPQ($product->id);
        $this->data['options']        = $this->shop_model->getProductOptionsWithWH($product->id);
        $this->data['variants']       = $this->shop_model->getProductOptions($product->id);
        $this->load->helper('text');
        $this->data['page_title'] = $product->code . ' - ' . $product->name;
        $this->data['page_desc']  = character_limiter(strip_tags($product->product_details), 160);
        $this->page_construct('pages/view_product', $this->data);
    }

    // Products,  categories and brands page
    public function products($category_slug = null, $subcategory_slug = null, $brand_slug = null, $promo = null)
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
        
        if($category_slug != null)
        { $this->data['featureImage'] = $this->shop_model->getCategoryBySlug($category_slug); }
        $reset = $category_slug || $subcategory_slug || $brand_slug ? true : false;

        $filters = [
            'query'       => $this->input->post('query'),
            'category'    => $category_slug ? $this->shop_model->getCategoryBySlug($category_slug) : null,
            'subcategory' => $subcategory_slug ? $this->shop_model->getCategoryBySlug($subcategory_slug) : null,
            'brand'       => $brand_slug ? $this->shop_model->getBrandBySlug($brand_slug) : null,
            'promo'       => $promo,
            'sorting'     => $reset ? null : $this->input->get('sorting'),
            'min_price'   => $reset ? null : $this->input->get('min_price'),
            'max_price'   => $reset ? null : $this->input->get('max_price'),
            'in_stock'    => $reset ? null : $this->input->get('in_stock'),
            'page'        => $this->input->get('page') ? $this->input->get('page', true) : 1,
        ];
        $this->data['filtered_subcategories'] = $category_slug ? $this->shop_model->getSubCategories($filters['category']->id) : null;
        $this->data['filters']    = $filters;
        $this->data['all_categories']    = $this->shop_model->getAllCategories();
        $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['page_title'] = (!empty($filters['category']) ? $filters['category']->name : (!empty($filters['brand']) ? $filters['brand']->name : lang('products'))) . ' - ' . $this->shop_settings->shop_name;
        $this->data['page_title2'] = (!empty($filters['category']) ? $filters['category']->name : (!empty($filters['brand']) ? $filters['brand']->name : lang('products'))) ;
        $this->data['page_desc']  = !empty($filters['category']) ? $filters['category']->description : (!empty($filters['brand']) ? $filters['brand']->description : $this->shop_settings->products_description);
           $this->data['location']    = $this->shop_model->getProductLocation();
           if($this->data=='Saudi Arabia'){
                echo "Test";
                
            }
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
                $this->data['inv']        = $order;
                $this->data['rows']       = $this->shop_model->getQuoteItems($id);
                $this->data['customer']   = $this->site->getCompanyByID($order->customer_id);
                $this->data['biller']     = $this->site->getCompanyByID($order->biller_id);
                $this->data['created_by'] = $this->site->getUser($order->created_by);
                $this->data['updated_by'] = $this->site->getUser($order->updated_by);
                $this->data['page_title'] = lang('view_quote');
                $this->data['page_desc']  = '';
                $this->page_construct('pages/view_quote', $this->data);
            } else {
                $this->session->set_flashdata('error', lang('access_denied'));
                redirect('/');
            }
        } else {
            if ($this->input->get('download')) {
                $id                     = $this->input->get('download', true);
                $order                  = $this->shop_model->getQuote(['id' => $id]);
                $this->data['inv']      = $order;
                $this->data['rows']     = $this->shop_model->getQuoteItems($id);
                $this->data['customer'] = $this->site->getCompanyByID($order->customer_id);
                $this->data['biller']   = $this->site->getCompanyByID($order->biller_id);
                // $this->data['created_by'] = $this->site->getUser($order->created_by);
                // $this->data['updated_by'] = $this->site->getUser($order->updated_by);
                $this->data['Settings'] = $this->Settings;
                $html                   = $this->load->view($this->Settings->theme . '/shop/views/pages/pdf_quote', $this->data, true);
                if ($this->input->get('view')) {
                    echo $html;
                    exit;
                }
                $name = lang('quote') . '_' . str_replace('/', '_', $order->reference_no) . '.pdf';
                $this->sma->generate_pdf($html, $name);
            }
            $page   = $this->input->get('page') ? $this->input->get('page', true) : 1;
            $limit  = 10;
            $offset = ($page * $limit) - $limit;
            $this->load->helper('pagination');
            $total_rows = $this->shop_model->getQuotesCount();
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->data['error']      = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['orders']     = $this->shop_model->getQuotes($limit, $offset);
            $this->data['pagination'] = pagination('shop/quotes', $total_rows, $limit);
            $this->data['page_info']  = ['page' => $page, 'total' => ceil($total_rows / $limit)];
            $this->data['page_title'] = lang('my_orders');
            $this->data['page_desc']  = '';
            $this->page_construct('pages/quotes', $this->data);
        }
    }

    // Search products page - ajax
    public function search()
    {
        $filters           = $this->input->post('filters') ? $this->input->post('filters', true) : [];
        $limit             = 12;
        $total_rows        = $this->shop_model->getProductsCount($filters);
        $filters['limit']  = $limit;
        $filters['offset'] = isset($filters['page']) && !empty($filters['page']) && ($filters['page'] > 1) ? (($filters['page'] * $limit) - $limit) : null;

        if ($products = $this->shop_model->getProducts($filters)) {
            $this->load->helper(['text', 'pagination']);
            foreach ($products as &$value) {
                $value['details'] = character_limiter(strip_tags($value['details']), 140);
                if ($this->shop_settings->hide_price) {
                    $value['price']         = $value['formated_price']         = 0;
                    $value['promo_price']   = $value['formated_promo_price']   = 0;
                    $value['special_price'] = $value['formated_special_price'] = 0;
                } else {
                    $value['price']                  = $this->sma->setCustomerGroupPrice($value['price'], $this->customer_group);
                    $value['formated_price']         = $this->sma->convertMoney($value['price']);
                    $value['promo_price']            = $this->sma->isPromo($value) ? $value['promo_price'] : 0;
                    $value['formated_promo_price']   = $this->sma->convertMoney($value['promo_price']);
                    $value['special_price']          = isset($value['special_price']) && !empty($value['special_price']) ? $this->sma->setCustomerGroupPrice($value['special_price'], $this->customer_group) : 0;
                    $value['formated_special_price'] = $this->sma->convertMoney($value['special_price']);
                }
            }

            $pagination = pagination('shop/products', $total_rows, $limit);
            $info       = ['page' => (isset($filters['page']) && !empty($filters['page']) ? $filters['page'] : 1), 'total' => ceil($total_rows / $limit)];

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
        $total               = $this->shop_model->getWishlist(true);
        $products            = $this->shop_model->getWishlist();
        $this->load->helper('text');
        foreach ($products as $product) {
            $item          = $this->shop_model->getProductByID($product->product_id);
            $item->details = character_limiter(strip_tags($item->details), 140);
            $items[]       = $item;
        }
        $this->data['items']      = $products ? $items : null;
        $this->data['page_title'] = lang('wishlist');
        $this->data['page_desc']  = '';
        $this->page_construct('pages/wishlist', $this->data);
    }
    
    public function suggestions($pos = 0)
    {
        $term         = $this->input->get('term', true);
        $warehouse_id = $this->shop_settings->warehouse;
        $category_id  = $this->input->get('category_id', true);
        //$customer_id  = $this->input->get('customer_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed  = $this->sma->analyze_term($term);
        $sr        = $analyzed['term'];
        //$option_id = $analyzed['option_id'];

        $warehouse      = $this->site->getWarehouseByID($warehouse_id);
        $customer_group = "Retail";//$this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows           = $this->shop_model->getProductNames($sr, $warehouse_id,$category_id, $pos);
        $currencies = $this->site->getAllCurrencies();
        

        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
            
                $c = uniqid(mt_rand(), true);
                unset($row->cost, $row->details, $row->product_details,  $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option               = false;
                $row->quantity        = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty             = 1;
                $row->discount        = '0';
                $row->serial          = '';
                $options              = $this->shop_model->getProductOptions($row->id, $warehouse_id);
                
                
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->shop_model->getProductOptionByID($option_id) : $options[0];
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
                } /*elseif ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                } elseif ($warehouse->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                }*/
               
                /*else if($customer_group->name == "Wholesale")
                {
                    
                    if($row->Price_in_Dollar!=3)
                        $row->price = ($row->price - (($row->price * $row->whole_sale_price) / 100))*$dcurrRate;
                    else
                        $row->price = $row->whole_sale_price;
                    
                }
                else if($customer_group->name=="Library")
                {
                    $productprice         = $this->sales_model->getUserProductPrice($row->id, $warehouse_id,$customer_id); 
                     
                    $row->productprice = $productprice;
                    if($productprice){
                        $row->oldprice = $row->price;
                        $row->price = $productprice->unit_price;
                        $row->lib_price = $productprice->unit_price;
                    }
                    
                    if($row->Price_in_Dollar!=3)
                        $row->price  = ($row->price - (($row->price * $row->lib_price) / 100))*$dcurrRate;
                    else
                        $row->price = $row->lib_price;
                     
                }else{
                    $productprice         = $this->sales_model->getUserProductPrice($row->id, $warehouse_id,$customer_id); 
                    if($productprice){
                        $row->oldprice = $row->price;
                        $row->price = $productprice->unit_price;
                    }
                }*/
                
                $row->real_unit_price = $row->price;
                $row->base_quantity   = 1;
                $row->base_unit       = $row->unit;
                $row->base_unit_price = $row->price;
                $row->customer_group = $customer_group;
                $row->unit            = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment         = '';
                
                $combo_items          = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->shop_model->getProductComboItems($row->id, $warehouse_id);
                }
                $units    = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $brand = $this->site->getBrandByID($row->brand);
                $row->brand_name=$brand->name;
                 $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id,'image'=>$row->image, 'label' => $row->name , 'category' => $row->category_id,
                    'row'     => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options,'plink' => base_url().'product/'.$row->slug ];
                $r++;
            }
            $this->sma->send_json($pr);
            
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
          // $this->sma->send_json([['id' => 0, 'value' => $term]]);
        }
    }
    
    public function refundData()
	{
        $dt   = date('Y-m-d');;
        
        $data = array(
        	
        	'order_id'=>$this->input->get('order_id'),
            'user_id'=>$this->input->get('customer_id'),
        	'req_dates'=>$dt,
        	'reason_refund'=>$this->input->get('reason_refund'),
        	 'notes'=>$this->input->get('notes')
        		);
        		
        		$this->load->model('shop_model');
        		$result=$this->shop_model->saveRefundRecord($data);
        		if($result)
        		{
        		echo  1;	
        		}
        		else
        		{
        		echo  0;	
        		}
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
