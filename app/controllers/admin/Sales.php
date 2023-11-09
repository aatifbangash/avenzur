<?php

defined('BASEPATH') or exit('No direct script access allowed');
// ALTER TABLE `sma_sales` ADD `sequence_code` VARCHAR(255) NULL AFTER `sale_invoice`;
class Sales extends MY_Controller
{
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
        $this->lang->admin_load('sales', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('sales_model');
        $this->load->admin_model('companies_model');
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

    /* ------------------------------------------------------------------ */

    public function add($quote_id = null)
    {
        $this->sma->checkPermissions();
        $sale_id = $this->input->get('sale_id') ? $this->input->get('sale_id') : null;

        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

        if ($this->form_validation->run() == true) {

            $customerId = $this->input->post('customer');

            $customer = $this->companies_model->getCompanyByID($customerId);
            $customerCreditLimit = $customer->credit_limit;

            // Check If customer pending sales exceeded the customer credit limit START
            $pendingSales = $this->companies_model->getPendingSalesByCustomer($customerId);
            if(!empty($pendingSales->pendingSalesAmount) && $pendingSales->pendingSalesAmount >= $customerCreditLimit) {
                $this->session->set_flashdata('error', lang('Customer credit limit exceeded. '));
                redirect($_SERVER['HTTP_REFERER']);
            }
            // Check If customer pending sales exceeded the customer credit limit END

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
            for ($r = 0; $r < $i; $r++) {
                $item_id            = $_POST['product_id'][$r];
                $item_type          = $_POST['product_type'][$r];
                $item_code          = $_POST['product_code'][$r];
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                //$net_cost           = $this->sma->formatDecimal($_POST['net_cost'][$r]);

                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';
                
                $item_expiry        = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $item_batchno       = $_POST['batchno'][$r]          ?? '';
                $item_serial_no     = $_POST['serial_no'][$r]        ?? '';
                $item_lotno         = $_POST['lotno'][$r]            ?? '';
                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];
                //$item_bonus = $_POST['bonus'][$r];
                $item_dis1 = $_POST['dis1'][$r];
                $item_dis2 = $_POST['dis2'][$r];
                $totalbeforevat = $_POST['totalbeforevat'][$r];
                $main_net = $_POST['main_net'][$r];

                $net_cost_obj = $this->sales_model->getAverageCost($item_batchno, $item_code);
                $net_cost = $net_cost_obj[0]->cost_price;
                if(empty($net_cost) || $net_cost == NULL){
                    $net_cost = 0;
                }

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;
                    // $unit_price = $real_unit_price;
                    if ($item_type == 'digital') {
                        $digital = true;
                    }
                    $pr_discount      = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price       = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
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
                    $subtotal2 = (($item_net_price * $item_unit_quantity));// + $pr_item_tax);
                    $unit     = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_cost'          => $net_cost,
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
                        'item_discount'     => $prroduct_item_discount,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'serial_number'     => $item_serial_no,
                        'expiry'            => $item_expiry,
                        'batch_no'          => $item_batchno,
                        'lot_no'            => $item_lotno,

                        'real_unit_price'   => $real_unit_price,
                        'subtotal2'         => $this->sma->formatDecimal($subtotal2),
                        //'bonus'           => $item_bonus,
                        'bonus'             => 0,
                        'discount1'         => $item_dis1,
                        'discount2'         => $item_dis2,
                        'totalbeforevat'    => $totalbeforevat,
                        'main_net'          => $main_net,
                    ];

                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal($main_net, 4);//$this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
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
            // $this->sma->print_arrays($data, $products, $payment, $attachments);
        }
        
        if ($this->form_validation->run() == true && $this->sales_model->addSale($data, $products, $payment, [], $attachments)) {
            $this->session->set_userdata('remove_slls', 1);
            if ($quote_id) {
                $this->db->update('quotes', ['status' => 'completed'], ['id' => $quote_id]);
            }
            $this->session->set_flashdata('message', lang('sale_added'));
            admin_redirect('sales');
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
            'sid'          =>  $inv->id
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
        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');
        $this->form_validation->set_rules('sale_status', lang('sale_status'), 'required');
        $this->form_validation->set_rules('payment_status', lang('payment_status'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no');
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
                $item_name          = $_POST['product_name'][$r];
                $item_option        = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                //$net_cost           = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $real_unit_price    = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price         = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial        = $_POST['serial'][$r]           ?? '';

                $item_expiry        = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $item_batchno       = $_POST['batchno'][$r]          ?? '';
                $item_serial_no     = $_POST['serial_no'][$r]        ?? '';
                $item_lotno         = $_POST['lotno'][$r]            ?? '';

                $item_tax_rate      = $_POST['product_tax'][$r]      ?? null;
                $item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit          = $_POST['product_unit'][$r];
                $item_quantity      = $_POST['product_base_quantity'][$r];


                //$item_bonus = $_POST['bonus'][$r];
                $item_dis1 = $_POST['dis1'][$r];
                $item_dis2 = $_POST['dis2'][$r];
                $totalbeforevat = $_POST['totalbeforevat'][$r];
                $main_net = $_POST['main_net'][$r];
                
                
                $data2['OperationType'] = 'DRUG SALE';
                $data2['TransactionNumber']  = $reference;
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

                $net_cost_obj = $this->sales_model->getAverageCost($item_batchno, $item_code);
                $net_cost = $net_cost_obj[0]->cost_price;
                if(empty($net_cost) || $net_cost == NULL){
                    $net_cost = 0;
                }

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->sales_model->getProductByCode($item_code) : null;

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
                    $subtotal2 = (($item_net_price * $item_unit_quantity));
                    $unit     = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id'        => $item_id,
                        'product_code'      => $item_code,
                        'product_name'      => $item_name,
                        'product_type'      => $item_type,
                        'option_id'         => $item_option,
                        'net_cost'          => $net_cost,
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
                        'item_discount'     => $product_item_discount,
                        'subtotal'          => $this->sma->formatDecimal($subtotal),
                        'serial_no'         => $item_serial,
                        'expiry'            => $item_expiry,
                        'batch_no'          => $item_batchno,
                        'serial_number'     => $item_serial_no,
                        'lot_no'            => $item_lotno,
                        'real_unit_price'   => $real_unit_price,
                        'subtotal2'         => $this->sma->formatDecimal($subtotal2),
                        //'bonus'           => $item_bonus,
                        'bonus'             => 0,
                        'discount1'         => $item_dis1,
                        'discount2'         => $item_dis2,
                        'totalbeforevat'    => $totalbeforevat,
                        'main_net'          => $main_net,
                    ];

                    $products[] = ($product + $gst_data);
                    $total += $this->sma->formatDecimal($main_net, 4);//$this->sma->formatDecimal(($item_net_price * $item_unit_quantity), 4);
                }
            }
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
                'product_discount'  => $total_product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount'    => $order_discount,
                'total_discount'    => $total_product_discount,
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
                'updated_by'        => $this->session->userdata('user_id'),
                'updated_at'        => date('Y-m-d H:i:s'),
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

        if ($this->form_validation->run() == true && $this->sales_model->updateSale($id, $data, $products, $attachments)) {
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

                //Discount calculation----------------------------------
                // this row is deleted becasue of discount must not be added in sale price 
                //$row->price           = $this->sma->formatDecimal($item->net_unit_price + $this->sma->formatDecimal($row->item_discount));
                
                $row->price           = $this->sma->formatDecimal($item->net_unit_price);
                //Discount calculation----------------------------------

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
                    'sid'          =>  $inv->id
                    );
            
            $add  = $this->db->insert('sma_accounts_entries', $entry);
            $insert_id = $this->db->insert_id();
             //$insert_id = 999;
             $entryitemdata = array();
             foreach ($inv_items as $item) 
             {
                 $proid = $item->product_id;
                 $product  = $this->site->getProductByID($proid);
                 //products
                 

                $entryitemdata[] = array(
                    'Entryitem' => array(
                        'entry_id' => $insert_id,
                        'dc' => 'D',
                        'ledger_id' => $customer->cogs_ledger,
                        //'amount' => $item->main_net,
                        'amount' => ($item->net_cost * $item->quantity),
                        'narration' => 'cost of goods sold'
                    )
                );
 
                $entryitemdata[] = array(
                    'Entryitem' => array(
                        'entry_id' => $insert_id,
                        'dc' => 'C',
                        'ledger_id' => $customer->sales_ledger,
                        'amount' => $item->main_net,
                        'narration' => 'sale account'
                    )
                );
 
                $entryitemdata[] = array(
                    'Entryitem' => array(
                        'entry_id' => $insert_id,
                        'dc' => 'C',
                        'ledger_id' => $warehouse_ledgers->inventory_ledger,
                        //'amount' => $item->main_net,
                        'amount' => ($item->net_cost * $item->quantity),
                        'narration' => 'inventory account'
                    )
                );
 
             }
          
             // //vat on sale
             $entryitemdata[] = array(
                         'Entryitem' => array(
                             'entry_id' => $insert_id,
                             'dc' => 'C',
                             'ledger_id' => $this->vat_on_sale,
                             'amount' => $inv->product_tax,
                             'narration' => 'vat on sale'
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

             // //total discount
             /*$entryitemdata[] = array(
                    'Entryitem' => array(
                        'entry_id' => $insert_id,
                        'dc' => 'D',
                        'ledger_id' => $this->vat_on_sale,
                        'amount' => $inv->total_discount,
                        'narration' => 'total discount'
                    )
             );*/
                     
            //   /*Accounts Entry Items*/
            foreach ($entryitemdata as $row => $itemdata)
            {
                
                  $this->db->insert('sma_accounts_entryitems', $itemdata['Entryitem']);
            }


            $this->session->set_flashdata('message', lang('Sale is Converted to invoice Successfully!'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'sales');
       

        }
         }else{

            $this->session->set_flashdata('error', lang('Sale Already Converted to invoice!'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'sales');
        }
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
        //$this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
        $this->load->view($this->theme . 'sales/assign_courier', $this->data);
        
    }

    public function getEcommerceSales($warehouse_id = null){
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
                ->select("{$this->db->dbprefix('sales')}.id as id, DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('sales')}.sequence_code as code, biller, {$this->db->dbprefix('sales')}.customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, {$this->db->dbprefix('sales')}.attachment, return_id")
                ->from('sales')
                ->where('warehouse_id', $warehouse_id)
                ->where('shop', 1);
                //->join('aramex_shipment', 'aramex_shipment.salesid=sales.id');
        } else {
            $this->datatables
                ->select("{$this->db->dbprefix('sales')}.id as id, DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('sales')}.sequence_code as code, biller, {$this->db->dbprefix('sales')}.customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, {$this->db->dbprefix('sales')}.attachment, return_id,")
                ->from('sales')
                ->where('shop', 1);
                
                
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


    public function getSales($warehouse_id = null)
    {
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
        
        $add_payment_link  = anchor('admin/sales/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $packagink_link    = anchor('admin/sales/packaging/$1', '<i class="fa fa-archive"></i> ' . lang('packaging'), 'data-toggle="modal" data-target="#myModal"');
        $add_delivery_link = anchor('admin/sales/add_delivery/$1', '<i class="fa fa-truck"></i> ' . lang('add_delivery'), 'data-toggle="modal" data-target="#myModal"');
        $email_link        = anchor('admin/sales/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_sale'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link         = anchor('admin/sales/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_sale'), 'class="sledit"');
        $pdf_link          = anchor('admin/sales/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $return_link       = anchor('admin/sales/return_sale/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_sale'));
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
                ->select("{$this->db->dbprefix('sales')}.id as id, DATE_FORMAT({$this->db->dbprefix('sales')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('sales')}.sequence_code as code, biller, {$this->db->dbprefix('sales')}.customer, sale_status, grand_total, paid, (grand_total-paid) as balance, payment_status, {$this->db->dbprefix('sales')}.attachment, return_id,")
                ->from('sales')
                ->where('shop', 0);
                
                
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

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('sales')]];
        $meta = ['page_title' => lang('sales'), 'bc' => $bc];
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
        $this->data['rows']        = $this->sales_model->getAllInvoiceItems($id);
        $this->data['return_sale'] = $inv->return_id ? $this->sales_model->getInvoiceByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->sales_model->getAllInvoiceItems($inv->return_id) : null;
        $this->data['attachments'] = $this->site->getAttachments($id, 'sale');

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

        $name = lang('sale') . '_' . str_replace('/', '_', $inv->reference_no) . '.pdf';
        $html = $this->load->view($this->theme . 'sales/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }

        if ($view) {
            $this->load->view($this->theme . 'sales/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer, $this->data['biller']->invoice_footer);
        } else {
            $this->sma->generate_pdf($html, $name, false, $this->data['biller']->invoice_footer);
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
                $keys   = ['code', 'net_unit_price', 'quantity', 'variant', 'item_tax_rate', 'discount', 'serial'];
                $final  = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_price']) && isset($csv_pr['quantity'])) {
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
                $row->quantity        = 0;
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
                    $row->quantity = 0;
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
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
}
