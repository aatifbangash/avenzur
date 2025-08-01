<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchases extends MY_Controller
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
        if ($this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->load->admin_model('cmt_model');
        $this->load->library('RASDCore',$params=null, 'rasd');
        $this->lang->admin_load('purchases', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('purchases_model');
        $this->load->admin_model('transfers_model');
        $this->load->admin_model('Inventory_model');
        $this->load->admin_model('deals_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024000';
        $this->data['logo'] = true;
        $this->load->library('attachments', [
            'path' => $this->digital_upload_path,
            'types' => $this->digital_file_types,
            'max_size' => $this->allowed_file_size,
        ]);

        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
    }

    /* -------------------------------------------------------------------------------------------------------------------------------- */

    // show Upload Purchases
    public function showUploadPurchases() {
        //$this->sma->checkPermissions();

        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['suppliers'] = $this->site->getAllCompanies('supplier');

        echo "<script>console.log(" . json_encode($this->data['suppliers']) . ");</script>";
        echo "<script>console.log(" . json_encode($this->data['warehouses']) . ");</script>";

        $this->load->view($this->theme . 'purchases/uploadCsvPurchases', $this->data);
    }

    public function mapPurchases(){
        //$this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('mwarehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('msupplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('userfile', $this->lang->line('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $quantity = 'quantity';
            $product = 'product';
            $unit_cost = 'unit_cost';
            $tax_rate = 'tax_rate';
            $reference = $this->input->post('mreference_no') ? $this->input->post('mreference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('mdate')));
            } else {
                $date = $this->sma->fld(trim($this->input->post('mdate')));
            }
            $warehouse_id = $this->input->post('mwarehouse');
            $child_supplier_id = $this->input->post('mchildsupplier') ? $this->input->post('mchildsupplier') : 0;
            $supplier_id = $child_supplier_id ? $child_supplier_id : $this->input->post('msupplier');
            $status = 'pending';
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total_sale_price = 0;
            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;

            $grand_total = 0;
            $grand_total_net_purchase = 0;
            $total_discount = 0;

            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('purchases/uploadCsvPurchases');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }

                $arr_length = count($arrResult);
                if ($arr_length > 5000000) {
                    $this->session->set_flashdata('error', lang('too_many_products'));
                    redirect($_SERVER['HTTP_REFERER']);
                    exit();
                }
                $titles = array_shift($arrResult);
                $keys = ['code', 'sale_price', 'purchase_price', 'batchno', 'expiry', 'quantity', 'bonus', 'discount1', 'discount1_val', 'discount2', 'discount2_val', 'item_tax_rate', 'item_tax_val', 'total_purchase', 'total_sale', 'net_purchase', 'net_unit_cost' ];
                $final = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_cost']) && isset($csv_pr['quantity'])) {
                        if ($product_details = $this->purchases_model->getProductByCode($csv_pr['code'])) {
                            
                            $item_code = $csv_pr['code'];
                            $item_net_cost = $csv_pr['net_unit_cost'];
                            $item_quantity = $csv_pr['quantity'];
                            $quantity_balance = $csv_pr['quantity'];
                            $item_tax_rate = $csv_pr['item_tax_rate'];
                            $pr_item_tax = $csv_pr['item_tax_val'];
                            $item_bonus = $csv_pr['bonus'];

                            $item_expiry = isset($csv_pr['expiry']) ? $this->sma->fsd($csv_pr['expiry']) : null;
                            $item_purchase_price = $csv_pr['purchase_price'];
                            $item_sale_price = $csv_pr['sale_price'];
                            $item_batchno = $csv_pr['batchno'];
                            $item_discount1 = $csv_pr['discount1'];
                            $item_discount1_val = $csv_pr['discount1_val'];
                            $item_discount2 = $csv_pr['discount2'];
                            $item_discount2_val = $csv_pr['discount2_val'];

                            $total_purchases = $csv_pr['total_purchase'];

                            $total_before_vat = $csv_pr['net_purchase'];
                            $main_net = ($csv_pr['net_purchase'] + $pr_item_tax);
                            //$total_purchases = $item_net_cost * $item_quantity;

                            $tax = '';
                            $unit_cost = $item_net_cost;
                            $product_tax += $pr_item_tax;
                            $tax_details = ((isset($item_tax_rate) && !empty($item_tax_rate)) ? $this->purchases_model->getTaxRateByName($item_tax_rate) : $this->site->getTaxRateByID($product_details->tax_rate));
                            if ($tax_details) {
                                $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                                $tax = $ctax['tax'];
                            }

                            $product_discount = $item_discount1_val + $item_discount2_val;
                            $total_discount += $product_discount;

                            $subtotal = $main_net;
                            $subtotal2 = (($item_net_cost * $item_quantity));// + $pr_item_tax);

                            $unit = $this->site->getUnitByID($product_details->unit);
                            $real_unit_cost = $this->sma->formatDecimal(($unit_cost + $pr_discount), 4);
                            $product = [
                                'product_id' => $product_details->id,
                                'product_code' => $item_code,
                                'product_name' => $product_details->name,
                                'net_unit_cost' => $item_net_cost,
                                'quantity' => ($item_quantity  + $item_bonus),
                                'bonus' => $item_bonus,
                                'product_unit_id' => $product_details->unit,
                                'product_unit_code' => $unit->code,
                                'unit_quantity' => $item_quantity,
                                'quantity_balance' => $status == 'received' ? $item_quantity + $item_bonus : 0,
                                'quantity_received' => $status == 'received' ? $item_quantity + $item_bonus : 0,
                                'warehouse_id' => $warehouse_id,
                                'item_tax' => $pr_item_tax,
                                'tax_rate_id' => $tax_details ? $tax_details->id : null,
                                'tax' => $tax,
                                'discount' => $item_discount1,
                                'item_discount' => $item_discount1_val,
                                'expiry' => $item_expiry,
                                'sale_price' => $item_sale_price,
                                'batchno' => $item_batchno,
                                'discount1' => $item_discount1,
                                'discount2' => $item_discount2,
                                'second_discount_value' => $item_discount2_val,
                                'totalbeforevat' => $total_before_vat,
                                'main_net' => $main_net,
                                'subtotal' => $subtotal,
                                'subtotal2' => $subtotal2,
                                'date' => date('Y-m-d', strtotime($date)),
                                'status' => $status,
                                'unit_cost' => $item_purchase_price, // $this->sma->formatDecimal(($item_net_cost + $item_tax), 4),
                                'real_unit_cost' => $item_purchase_price,
                                'base_unit_cost' => $item_purchase_price,
                            ];

                            $products[] = ($product);
                            // $total += $this->sma->formatDecimal(($item_net_cost * $item_quantity), 4);
                            $total += $total_purchases;
                            $total_sale_price += $this->sma->formatDecimal($item_sale_price, 4);

                        } else {
                            $this->session->set_flashdata('error', $this->lang->line('pr_not_found') . ' ( ' . $csv_pr['code'] . ' ). ' . $this->lang->line('line_no') . ' ' . $rw);
                            redirect($_SERVER['HTTP_REFERER']);
                        }


                        $grand_total += $main_net;
                        $grand_total_net_purchase += $total_before_vat;

                        $rw++;
                    }
                }
            }

            // $order_discount = $this->site->calculateDiscount($this->input->post('discount') ? $this->input->post('order_discount') : null, ($total + $product_tax), true);
            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), $total, true);//$this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax), true);
            
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $product_tax + $order_tax;

            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            //$grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            
            $data = [
                'reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'total_net_purchase' => $grand_total_net_purchase,
                'total_sale' => $total_sale_price,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'sequence_code' => $this->sequenceCode->generate('PR', 5),
                'created_by' => $this->session->userdata('user_id'),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            $attachments = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            //$this->sma->print_arrays($data, $products);exit;
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products, $attachments)) {
            $this->session->set_flashdata('message', $this->lang->line('purchase_added'));
            admin_redirect('purchases');
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['ponumber'] = ''; // $this->site->getReference('po');

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('add_purchase_by_csv')]];
            $meta = ['page_title' => lang('add_purchase_by_csv'), 'bc' => $bc];
            $this->page_construct('purchases/purchase_by_csv', $meta, $this->data);
        }
    }


    public function add($quote_id = null)
    {
        $this->sma->checkPermissions();

        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        // $this->form_validation->set_rules('batchno[]', lang('Batch'), 'required');
        $product_id_arr = $this->input->post('product_id');

        foreach ($product_id_arr as $index => $prid) {
            // Set validation rules for each quantity field
            $this->form_validation->set_rules(
                'quantity[' . $index . ']',
                'Quantity for Product ' . $_POST['product_name'][$index],  // Replace with actual product identifier
                'required|greater_than[0]',
                array(
                    'required' => 'Quantity for Product <b>' . $_POST['product_name'][$index] . '</b> is required.',
                    'greater_than' => 'Quantity for Product <b>' . $_POST['product_name'][$index] . '</b> must be greater than zero.'
                )
            );
        }

        foreach ($product_id_arr as $index => $prid) {
            // Set validation rules for prduct expiry field
            $this->form_validation->set_rules(
                'expiry[' . $index . ']',
                'Expiry Date for Product ' . $_POST['product_name'][$index],  // Replace with actual product identifier
                'required',
                array(
                    'required' => 'Expiry Date for Product <b>' . $_POST['product_name'][$index] . '</b> is required.',
                )
            );
        }

       
        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {

            // echo "<pre>";
            // print_r($this->input->post());exit;
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $child_supplier_id = $this->input->post('childsupplier') ? $this->input->post('childsupplier') : 0;
            $supplier_id = $child_supplier_id ? $child_supplier_id : $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;

            $total = 0;
            $total_sale_price = 0;
            $product_tax = 0;
            $product_discount = 0;
            $i = sizeof($_POST['product']);
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
                $avz_item_code = isset($_POST['avz_item_code'][$r]) && !empty($_POST['avz_item_code'][$r]) ? $_POST['avz_item_code'][$r] : '';
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $item_sale_price = $_POST['sale_price'][$r];
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = $_POST['product_tax'][$r] ?? null;
                //$item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_discount = $_POST['dis1'][$r] ?? null;
                $item_discount2 = $_POST['dis2'][$r] ?? null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $item_unit_quantity;//$_POST['product_base_quantity'][$r];

                $item_batchno = trim($_POST['batchno'][$r]);
                if (empty($item_batchno)) {
                    $item_batchno = 'Default-' . $product_id;
                }
                $item_serial_no = $_POST['serial_no'][$r];
                $item_bonus = $_POST['bonus'][$r];
                $item_dis1 = $_POST['dis1'][$r];
                $item_dis2 = $_POST['dis2'][$r];
                $totalbeforevat = $_POST['totalbeforevat'][$r];
                $main_net = $_POST['main_net'][$r];

                //$net_cost_obj = $this->purchases_model->getAverageCost($item_batchno, $item_code);
                //$net_cost_sales = $net_cost_obj[0]->cost_price;

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {

                    /**
                     * NEED TO DISCUSS
                     */
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($product_details->price != $item_sale_price) {
                        // update product sale price
                        $this->purchases_model->updateProductSalePrice($item_code, $item_sale_price, $item_tax_rate);
                    }

                    if ($item_expiry) {
                        $today = date('Y-m-d');
                        if ($item_expiry <= $today) {
                            $this->session->set_flashdata('error', lang('product_expiry_date_issue') . ' (' . $product_details->name . ')');
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    }
                    
                    // $unit_cost = $real_unit_cost;
                    $pr_discount = $this->site->calculateDiscount($item_discount . '%', $unit_cost);
                    $amount_after_dis1 = $unit_cost - $pr_discount;
                    $pr_discount2 = $this->site->calculateDiscount($item_discount2 . '%', $amount_after_dis1);

                    //$item_net_cost = $unit_cost - $pr_discount - $pr_discount2;
                    //$item_net_cost    = $unit_cost;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $item_unit_quantity);
                    $product_discount += ($pr_item_discount + $pr_item_discount2);
                    $pr_item_tax = $item_tax = 0;
                    $tax = '';

                    $totalbeforevat = ($item_sale_price * $item_quantity) - $pr_item_discount - $pr_item_discount2;
                    $totalpurcahsesbeforevat = ($unit_cost * $item_quantity) - $pr_item_discount - $pr_item_discount2;

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax = $this->sma->formatDecimal($ctax['amount']);
                        $tax = $ctax['tax'];

                        /*if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }*/
                        $pr_item_tax = $this->sma->formatDecimal(($totalpurcahsesbeforevat * ($tax_details->rate / 100)), 4);//$this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);

                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }

                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = $main_net; //(($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $subtotal2 = (($unit_cost * $item_unit_quantity));// + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    //$item_net_cost = ($totalpurcahsesbeforevat) / ($item_quantity + $item_bonus);
                    $item_net_cost = ($main_net / ($item_quantity + $item_bonus) );
                    $item_net_price = ($totalpurcahsesbeforevat) / ($item_quantity);

                    /**
                     * POST FIELDS
                     */
                    $new_item_first_discount = $_POST['item_first_discount'][$r];
                    $new_item_second_discount = $_POST['item_second_discount'][$r];
                    $new_item_vat_value = $_POST['item_vat_values'][$r];
                    
                    $product = [
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $_POST['item_unit_cost'][$r], //item_net_cost,
                        'unit_cost' => $_POST['net_cost'][$r], //+ $item_tax),
                        'quantity' => $item_quantity + $item_bonus,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $status == 'received' ? $item_quantity + $item_bonus : 0,
                        'quantity_received' => $status == 'received' ? $item_quantity + $item_bonus : 0,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $new_item_vat_value,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => str_replace('%', '', $tax),
                        'discount' => $item_discount,
                        'item_discount' => $new_item_first_discount,
                        'subtotal' => $_POST['item_total_purchase'][$r],
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $_POST['real_unit_cost'][$r],
                        'sale_price' => $item_sale_price,
                        'date' => date('Y-m-d', strtotime($date)),
                        'status' => $status,
                        'supplier_part_no' => $supplier_part_no,
                        'subtotal2' => $this->sma->formatDecimal($subtotal2),
                        'batchno' => $item_batchno,
                        'serial_number' => $item_serial_no ? $item_serial_no : 'Default',
                        'bonus'             => $item_bonus,
                        //'bonus' => 0,
                        'discount1' => $item_dis1,
                        'discount2' => $item_dis2,
                        'second_discount_value' => $new_item_second_discount,
                        'totalbeforevat' => $_POST['item_net_purchase'][$r],
                        'main_net' => $main_net
                    ];

                    if($avz_item_code){
                        $product['avz_item_code'] = $avz_item_code;
                    }

                    if ($unit->id != $product_details->unit) {
                        $product['base_unit_cost'] = $this->site->convertToBase($unit, $real_unit_cost);
                    } else {
                        $product['base_unit_cost'] = $real_unit_cost;
                    }

                    $products[] = ($product + $gst_data);
                    $total_sale_price += $this->sma->formatDecimal($item_sale_price, 4);
                    $total += $this->sma->formatDecimal($main_net, 4);//$this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
            if (empty($products)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), $total, true);//$this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->sma->formatDecimal(($order_tax), 4);
            //$total_tax      = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);

            // below line commented by mm
            // $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $grand_total = $this->sma->formatDecimal(($total + $product_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
           
            /**
             * post values
             */
            
            $grand_total_purchase = $this->input->post('grand_total_purchase');
            $grand_total_net_purchase = $this->input->post('grand_total_net_purchase');
            $grand_total_discount = $this->input->post('grand_total_discount');
            $grand_total_vat = $this->input->post('grand_total_vat');
            $grand_total_sale = $this->input->post('grand_total_sale');
            $grand_total = $this->input->post('grand_total');

            $data = [
                'reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $grand_total_purchase,
                'total_net_purchase' => $grand_total_net_purchase,
                'total_sale' => $grand_total_sale,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount' => $order_discount,
                'total_discount' => $grand_total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $grand_total_vat,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'created_by' => $this->session->userdata('user_id'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'sequence_code' => $this->sequenceCode->generate('PR', 5)
            ];

            if($supplier_details->balance > 0 && $status == 'received'){
                if($supplier_details->balance >= $grand_total){
                    $paid = $grand_total;
                    $new_balance = $supplier_details->balance - $grand_total;
                    $payment_status = 'paid';
                }else{
                    $paid = $grand_total - $supplier_details->balance;
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

            $attachments = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            //$this->sma->print_arrays($data, $products);exit;
        }

        if ($this->form_validation->run() == true && $purchase_id = $this->purchases_model->addPurchase($data, $products, $attachments)) {
            if ($status == 'received') {
                $this->convert_purchse_invoice($purchase_id);

                if($supplier_details->balance > 0){
                    $this->purchases_model->update_balance($supplier_details->id, $new_balance);
                }
            }
            //   echo "<pre>";
            // print_r($this->input->post());exit;

            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('purchase_added'));
            admin_redirect('purchases?lastInsertedId=' . $purchase_id);
        } else {
            if ($quote_id) {
                $this->data['quote'] = $this->purchases_model->getQuoteByID($quote_id);
                $supplier_id = $this->data['quote']->supplier_id;
                $items = $this->purchases_model->getAllQuoteItems($quote_id);
                krsort($items);
                $c = rand(100000, 9999999);
                $count = 0;
                foreach ($items as $item) {
                    $row = $this->site->getProductByID($item->product_id);
                    
                    if (!$row) {
                        $this->session->set_flashdata('error', sprintf(lang('product_x_found'), $item->product_name . '(' . $item->product_code . ')'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    $count++;
                    if ($row->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($row->id, $item->warehouse_id);
                        foreach ($combo_items as $citem) {
                            $crow = $this->site->getProductByID($citem->id);
                            if (!$crow) {
                                $crow = json_decode('{}');
                                $crow->qty = $item->quantity;
                            } else {
                                unset($crow->details, $crow->product_details, $crow->price);
                                $crow->qty = $citem->qty * $item->quantity;
                            }
                            $crow->base_quantity = $item->quantity;
                            $crow->base_unit = $crow->unit ? $crow->unit : $item->product_unit_id;
                            $crow->base_unit_cost = $crow->cost ? $crow->cost : $item->unit_cost;
                            $crow->unit = $item->product_unit_id;
                            $crow->discount = $item->discount ? $item->discount : '0';
                            $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $crow) : $crow->cost;
                            $crow->cost = $supplier_cost ? $supplier_cost : 0;
                            $crow->tax_rate = $item->tax_rate_id;
                            $crow->real_unit_cost = $crow->cost ? $crow->cost : 0;
                            $crow->expiry = '';
                            $options = $this->purchases_model->getProductOptions($crow->id);
                            $units = $this->site->getUnitsByBUID($row->base_unit);
                            $tax_rate = $this->site->getTaxRateByID($crow->tax_rate);
                            $ri = $this->Settings->item_addition ? $crow->id : $c;
                            $row->serial_no = $count;
                            $pr[$ri] = ['serial_no'=>$count, 'id' => $c, 'item_id' => $crow->id, 'label' => $crow->name . ' (' . $crow->code . ')', 'row' => $crow, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options];
                            $c++;
                        }
                    } elseif ($row->type == 'standard') {
                        if (!$row) {
                            $row = json_decode('{}');
                            $row->quantity = 0;
                        } else {
                            unset($row->details, $row->product_details);
                        }
                        $row->serial_no = $count;
                        $row->id = $item->product_id;
                        $row->code = $item->product_code;
                        $row->name = $item->product_name;
                        $row->base_quantity = $item->quantity;
                        $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                        $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                        $row->unit = $item->product_unit_id;
                        $row->qty = $item->unit_quantity;
                        $row->bonus = $item->bonus;
                        $row->option = $item->option_id;
                        $row->discount = $item->discount ? $item->discount : '0';
                        $supplier_cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                        $row->cost = $supplier_cost ? $supplier_cost : 0;
                        $row->tax_rate = $item->tax_rate_id;
                        $row->expiry = '';
                        $row->real_unit_cost = $row->cost ? $row->cost : 0;
                        $options = $this->purchases_model->getProductOptions($row->id);

                        $units = $this->site->getUnitsByBUID($row->base_unit);
                        $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                        $ri = $this->Settings->item_addition ? $row->id : $c;

                        $pr[$ri] = [
                            'serial_no' => $count,
                            'id' => $c,
                            'item_id' => $row->id,
                            'label' => $row->name . ' (' . $row->code . ')',
                            'row' => $row,
                            'tax_rate' => $tax_rate,
                            'units' => $units,
                            'options' => $options,
                        ];
                        $c++;
                    }
                }
                 
                $this->data['quote_items'] = json_encode($pr);
            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['quote_id'] = $quote_id;
            $this->data['suppliers'] = $this->site->getAllParentCompanies('supplier');
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['ponumber'] = ''; //$this->site->getReference('po');
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('add_purchase')]];
            $meta = ['page_title' => lang('add_purchase'), 'bc' => $bc];
            $this->page_construct('purchases/add', $meta, $this->data);
        }
    }

    public function push_serials_to_rasd_manually()
    {
        $purchase_id = $_GET['purchase_id'];
        $purchase_details = $this->purchases_model->getPurchaseByID($purchase_id);
        $serials_reference = $purchase_details->reference_no;

        $items = $this->purchases_model->getAllPurchaseItems($purchase_id);

        foreach ($items as $item) {
            $serials_quantity = $item->quantity;
            $serials_gtin = $item->product_code;
            $serials_batch_no = $item->batchno;

            $dispatch_array = $this->db->get_where('sma_rasd_notifications', ['invoice_no' => $serials_reference], 1);
            if ($dispatch_array->num_rows() > 0) {
                foreach (($dispatch_array->result()) as $d_array) {
                    $dispatch_id = $d_array->dispatch_id;
                    $notification_serials = $this->db->get_where('sma_notification_serials', ['gtin' => $serials_gtin, 'dispatch_id' => $dispatch_id, 'batch_no' => $serials_batch_no, 'used' => 0], $serials_quantity);
                    if ($notification_serials->num_rows() > 0) {
                        foreach (($notification_serials->result()) as $row) {
                            $serials_data[] = $row;
                            $invoice_serials = array();
                            $invoice_serials['serial_number'] = $row->serial_no;
                            $invoice_serials['gtin'] = $row->gtin;
                            $invoice_serials['batch_no'] = $row->batch_no;
                            $invoice_serials['pid'] = $purchase_id;
                            $invoice_serials['date'] = date('Y-m-d');

                            $this->db->update('sma_notification_serials', ['used' => 1], ['serial_no' => $row->serial_no, 'batch_no' => $row->batch_no, 'gtin' => $row->gtin]);
                            $this->db->insert('sma_invoice_serials', $invoice_serials);
                        }
                    }
                }
            }

        }

        // Code for serials end here
    }

    public function add_expense()
    {
        $this->sma->checkPermissions('expenses', true);
        $this->load->helper('security');

        //$this->form_validation->set_rules('reference', lang("reference"), 'required');
        $this->form_validation->set_rules('amount', lang('amount'), 'required');
        $this->form_validation->set_rules('attachments', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date' => $date,
                'reference' => $this->input->post('reference') ? $this->input->post('reference') : $this->site->getReference('ex'),
                'amount' => $this->input->post('amount'),
                'created_by' => $this->session->userdata('user_id'),
                'note' => $this->input->post('note', true),
                'category_id' => $this->input->post('category', true),
                'warehouse_id' => $this->input->post('warehouse', true),
            ];

            $attachments = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            //$this->sma->print_arrays($data);
        } elseif ($this->input->post('add_expense')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addExpense($data, $attachments)) {
            $this->session->set_flashdata('message', lang('expense_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['exnumber'] = ''; //$this->site->getReference('ex');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['categories'] = $this->purchases_model->getExpenseCategories();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/add_expense', $this->data);
        }
    }

    public function convert_supplier_payment_invoice($pid, $amount)
    {
        $inv = $this->purchases_model->getPurchaseByID($pid);
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($inv->supplier_id);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => 'supplierpayment',
            'number' => 'PO-' . $inv->reference_no,
            'date' => date('Y-m-d'),
            'dr_total' => $this->sma->formatDecimal($amount + $this->Settings->bank_fees, 4),
            'cr_total' => $this->sma->formatDecimal($amount + $this->Settings->bank_fees, 4),
            'notes' => 'Return Reference: ' . $inv->reference_no . ' Date: ' . date('Y-m-d H:i:s'),
            'pid' => $inv->id
        );
        $add = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        $entryitemdata = array();

        //supplier
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $supplier->ledger_account,
                'amount' => $amount,
                'narration' => ''
            )
        );

        //bank charges
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $this->bank_fees,
                //'amount' => $inv->order_tax,
                'amount' => $this->Settings->bank_fees,
                'narration' => ''
            )
        );

        //bank fund cash
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $this->bank_fund_cash,
                //'amount' => $inv->order_tax,
                'amount' => $this->sma->formatDecimal($amount + $this->Settings->bank_fees, 4),
                'narration' => ''
            )
        );

        foreach ($entryitemdata as $row => $itemdata) {
            $this->db->insert('sma_accounts_entryitems', $itemdata['Entryitem']);
        }

    }

    public function add_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $purchase = $this->purchases_model->getPurchaseByID($id);
        if ($purchase->payment_status == 'paid' && $purchase->grand_total == $purchase->paid) {
            $this->session->set_flashdata('error', lang('purchase_already_paid'));
            $this->sma->md();
        }

        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = [
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('ppay'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->sma->clear_tags($this->input->post('note')),
                'created_by' => $this->session->userdata('user_id'),
                'type' => 'sent',
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);
        } elseif ($this->input->post('add_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPayment($payment)) {

            $this->convert_supplier_payment_invoice($this->input->post('purchase_id'), $this->input->post('amount-paid'));

            $this->session->set_flashdata('message', lang('payment_added'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $purchase;
            $this->data['payment_ref'] = ''; //$this->site->getReference('ppay');
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/add_payment', $this->data);
        }
    }

    public function combine_pdf($purchases_id)
    {
        $this->sma->checkPermissions('pdf');

        foreach ($purchases_id as $purchase_id) {
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $inv = $this->purchases_model->getPurchaseByID($purchase_id);
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
            $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
            $this->data['created_by'] = $this->site->getUser($inv->created_by);
            $this->data['inv'] = $inv;
            $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
            $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
            $inv_html = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
            if (!$this->Settings->barcode_img) {
                $inv_html = preg_replace("'\<\?xml(.*)\?\>'", '', $inv_html);
            }
            $html[] = [
                'content' => $inv_html,
                'footer' => '',
            ];
        }

        $name = lang('purchases') . '.pdf';
        $this->sma->generate_pdf($html, $name);
    }

    /* --------------------------------------------------------------------------- */

    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->purchases_model->deletePurchase($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 0, 'msg' => lang('purchase_deleted')]);
            }
            $this->session->set_flashdata('message', lang('purchase_deleted'));
            admin_redirect('welcome');
        } else {
            $this->sma->send_json(['error' => 1, 'msg' => 'Cannot delete this purchase']);
        }
    }

    public function delete_expense($id = null)
    {
        $this->sma->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        $expense = $this->purchases_model->getExpenseByID($id);
        if ($this->purchases_model->deleteExpense($id)) {
            if ($expense->attachment) {
                unlink($this->upload_path . $expense->attachment);
            }
            $this->sma->send_json(['error' => 0, 'msg' => lang('expense_deleted')]);
        }
    }

    public function delete_payment($id = null)
    {
        $this->sma->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        if (!$id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('id_not_found')]);
        }

        if ($this->purchases_model->deletePayment($id)) {
            //echo lang("payment_deleted");
            $this->session->set_flashdata('message', lang('payment_deleted'));
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    /* ------------------------------------------------------------------------------------- */

    public function edit($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $purchase_transferred = 0;

        $inv = $this->purchases_model->getPurchaseByID($id);
        $pur_inv_items = $this->purchases_model->getAllPurchaseItems($id);
        foreach ($pur_inv_items as $pur_item) {
            //$transferreditem = $this->Inventory_model->get_transferred_item($inv->warehouse_id,'transfer_out',$pur_item->product_id,$pur_item->avz_item_code,$pur_item->batchno);
            $transferreditem = $this->Inventory_model->get_transferred_item('null','transfer_in',$pur_item->product_id,$pur_item->avz_item_code,$pur_item->batchno);
            if($transferreditem){
                $purchase_transferred = 1;
            }
        }

        /*if ($inv->status == 'received' && $purchase_transferred == 1) {
             $this->session->set_flashdata('error', 'This invoice has items already transferred');

             admin_redirect('purchases');
        }*/

        $supplier_purchase_discount = $this->deals_model->getPurchaseDiscount($inv->supplier_id);
        if ($inv->status == 'returned' || $inv->return_id || $inv->return_purchase_ref) {
            $this->session->set_flashdata('error', lang('purchase_x_action'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'welcome');
        }
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('reference_no', $this->lang->line('ref_no'), 'required');
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');

        $this->session->unset_userdata('csrf_token');
        // echo "<pre>";
        // print_r($this->input->post());
        // exit;
        if ($this->form_validation->run() == true) {
        //     echo "<pre>";
        // print_r($this->input->post());
        // exit;
            $reference = $this->input->post('reference_no');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = $inv->date;
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $tempstatus = $this->input->post('tempstatus');
            //$lotnumber       = $this->input->post('lotnumber');
            $lotnumber = '';
            $shelf_status = $this->input->post('shelf_status') ? $this->input->post('shelf_status') : "NULL";
            $validate = $this->input->post('validate') ? $this->input->post('validate') : "NULL";


            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $payment_term = $this->input->post('payment_term');
            $due_date = $payment_term ? date('Y-m-d', strtotime('+' . $payment_term . ' days', strtotime($date))) : null;

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $partial = false;
            $i = sizeof($_POST['product']);
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
                $avz_item_code = isset($_POST['avz_item_code'][$r]) && !empty($_POST['avz_item_code'][$r]) ? $_POST['avz_item_code'][$r] : '';
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $item_sale_price = $_POST['sale_price'][$r];

                $item_unit_quantity = $_POST['quantity'][$r];
                //$quantity_received  = $_POST['received_base_quantity'][$r];
                $quantity_received = $item_unit_quantity;
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'undefined' ? $_POST['product_option'][$r] : null;
                $item_tax_rate = $_POST['product_tax'][$r] ?? null;
                //$item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_discount = $_POST['dis1'][$r] ?? null;
                $item_discount2 = $_POST['dis2'][$r] ?? null;
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                $supplier_part_no = (isset($_POST['part_no'][$r]) && !empty($_POST['part_no'][$r])) ? $_POST['part_no'][$r] : null;
                $quantity_balance = $_POST['quantity_balance'][$r];
                //$ordered_quantity   = $_POST['ordered_quantity'][$r];
                $ordered_quantity = $item_unit_quantity;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $item_unit_quantity;//$_POST['product_base_quantity'][$r];

                $item_batchno = trim($_POST['batchno'][$r]);
                if (empty($item_batchno)) {
                    $item_batchno = 'Default-' . $product_id;
                }
                $item_serial_no = $_POST['serial_no'][$r];
                $item_bonus = $_POST['bonus'][$r];
                $item_dis1 = $_POST['dis1'][$r];
                $item_dis2 = $_POST['dis2'][$r];
                $totalbeforevat = $_POST['totalbeforevat'][$r];
                $main_net = $_POST['main_net'][$r];
                $warehouse_shelf = $_POST['warehouse_shelf'][$r];

                if ($status == 'received' || $status == 'partial') {
                    /*if ($quantity_received < $item_quantity) {
                        $partial = 'partial';
                    } elseif ($quantity_received > $item_quantity) {
                        $this->session->set_flashdata('error', lang('received_more_than_ordered'));
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    $balance_qty = $quantity_received - ($ordered_quantity - $quantity_balance);*/
                    $balance_qty = $item_quantity;
                    $quantity_received = $item_quantity;
                } else {
                    $balance_qty = $item_quantity;
                    $quantity_received = $item_quantity;
                }

                //$net_cost_obj = $this->purchases_model->getAverageCost($item_batchno, $item_code);
                //$net_cost_sales = $net_cost_obj[0]->cost_price;

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity) && isset($quantity_balance)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);
                    if ($product_details->price != $item_sale_price) {
                        // update product sale price
                        $this->purchases_model->updateProductSalePrice($item_code, $item_sale_price, $item_tax_rate);
                    }
                    // $unit_cost = $real_unit_cost;
                    //$pr_discount      = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $pr_discount = $this->site->calculateDiscount($item_discount . '%', $unit_cost);
                    $amount_after_dis1 = $unit_cost - $pr_discount;
                    $pr_discount2 = $this->site->calculateDiscount($item_discount2 . '%', $amount_after_dis1);

                    //$unit_cost        = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost - $pr_discount - $pr_discount2;
                    //$item_net_cost    = $unit_cost;
                    //$pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $item_unit_quantity);
                    $product_discount += ($pr_item_discount + $pr_item_discount2);

                    //$product_discount += $pr_item_discount;
                    $pr_item_tax = 0;
                    $item_tax = 0;
                    $tax = '';

                    //$totalbeforevat = ($item_sale_price*$item_quantity) - $pr_item_discount - $pr_item_discount2;
                    $totalpurcahsesbeforevat = ($unit_cost * ($item_quantity - $item_bonus)) - $pr_item_discount - $pr_item_discount2;

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax = $this->sma->formatDecimal($ctax['amount']);
                        $tax = $ctax['tax'];
                        /*if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }*/
                        //$pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                        $pr_item_tax = $this->sma->formatDecimal(($totalpurcahsesbeforevat * ($tax_details->rate / 100)), 2);//$this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                        //echo 'main:'.$main_net;
                        //echo 'tax:'.$tax_details->rate;
                        //echo $main_net * ($tax_details->rate / 100);
                        $pr_item_tax =  $this->sma->formatDecimal(($main_net * ($tax_details->rate / 100)), 2);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = $main_net;//(($item_net_cost * $item_unit_quantity) + $pr_item_tax);
                    $subtotal2 = (($unit_cost * $item_unit_quantity));// + $pr_item_tax);
                    $unit = $this->site->getUnitByID($item_unit);

                    //$item_net_cost = ($totalpurcahsesbeforevat) / ($item_quantity);
                    $item_net_cost = ($main_net / ($item_quantity + $item_bonus) );
                    $item_net_price = ($totalpurcahsesbeforevat) / ($item_quantity - $item_bonus);

                        /**
                     * POST FIELDS
                     */
                    $new_item_first_discount = $_POST['item_first_discount'][$r];
                    $new_item_second_discount = $_POST['item_second_discount'][$r];
                    $new_item_vat_value = $_POST['item_vat_values'][$r];
                    $new_subtotal = $_POST['item_total_purchase'][$r];
                    $new_real_unit_cost = $_POST['real_unit_cost'][$r];

                    $item = [
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $_POST['item_unit_cost'][$r], //item_net_cost,
                        'unit_cost' => $_POST['net_cost'][$r], //+ $item_tax),
                        'quantity' => $item_quantity + $item_bonus,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $balance_qty,
                        'quantity_received' => $quantity_received,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $new_item_vat_value,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => str_replace('%', '', $tax),
                        'discount' => $item_discount,
                        'item_discount' =>  $new_item_first_discount,
                        'subtotal' => $new_subtotal,
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $new_real_unit_cost,
                        'sale_price' => $item_sale_price,
                        'supplier_part_no' => $supplier_part_no,
                        'date' => date('Y-m-d', strtotime($date)),
                        'subtotal2' => $this->sma->formatDecimal($subtotal2),
                        'batchno' => $item_batchno,
                        'serial_number' => $item_serial_no ? $item_serial_no : 'Default',
                        'bonus'             => $item_bonus,
                        //'bonus' => 0,
                        'discount1' => $item_dis1,
                        'discount2' => $item_dis2,
                        'second_discount_value' => $new_item_second_discount,
                        'totalbeforevat' => $_POST['item_net_purchase'][$r],
                        'main_net' => $main_net,
                        'warehouse_shelf' => ($warehouse_shelf ? $warehouse_shelf : '')
                    ];

                    if($avz_item_code){
                        $item['avz_item_code'] = $avz_item_code;
                    }

                    if ($unit->id != $product_details->unit) {
                        $item['base_unit_cost'] = $this->site->convertToBase($unit, $real_unit_cost);
                    } else {
                        $item['base_unit_cost'] = $real_unit_cost;
                    }

                    $items[] = ($item + $gst_data);
                    $total += $this->sma->formatDecimal($main_net, 4);//$item_net_cost * $item_unit_quantity;
                }
            }

            if (empty($items)) {
                $this->form_validation->set_rules('product', lang('order_items'), 'required');
            } else {
                foreach ($items as $item) {
                    $item['status'] = ($status == 'partial') ? 'received' : $status;
                    $products[] = $item;
                }
                krsort($products);
            }

            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), $total, true);
            //$this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->sma->formatDecimal(($order_tax), 4);

            //$this->sma->formatDecimal(($product_tax + $order_tax), 4);
            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            // below line commented by mm
            // $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $grand_total = $this->sma->formatDecimal(($total + $product_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
           
              /**
             * post values
             */
            
             $grand_total_purchase = $this->input->post('grand_total_purchase');
             $grand_total_net_purchase = $this->input->post('grand_total_net_purchase');
             $grand_total_discount = $this->input->post('grand_total_discount');
             $grand_total_vat = $this->input->post('grand_total_vat');
             $grand_total_sale = $this->input->post('grand_total_sale');
             $grand_total = $this->input->post('grand_total');

            $data = [
                'reference_no' => $reference,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $grand_total_purchase,
                'total_net_purchase' => $grand_total_net_purchase,
                'total_sale' => $grand_total_sale,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount' => $order_discount,
                'total_discount' => $grand_total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $grand_total_vat,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
                'payment_term' => $payment_term,
                'due_date' => $due_date,
                'tempstatus' => $tempstatus,
                'lotnumber' => $lotnumber,
                'shelf_status' => $shelf_status,
                'validate' => $validate


            ];

            if($supplier_details->balance > 0 && $status == 'received'){
                if($supplier_details->balance >= $grand_total){
                    $paid = $grand_total;
                    $new_balance = $supplier_details->balance - $grand_total;
                    $payment_status = 'paid';
                }else{
                    $paid = $grand_total - $supplier_details->balance;
                    $new_balance = 0;
                    $payment_status = 'partial';
                }

                $data['paid'] = $paid;
                $data['payment_status'] = $payment_status;
            }

            if ($date) {
                $data['date'] = $date;
            }
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            $attachments = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            //$this->sma->print_arrays($data, $products);exit;

            // echo "<pre>";
            // print_r($this->input->post());
            // print_r($data);
            // print_r($products);
            // exit;
        }


        if ($this->form_validation->run() == true && $this->purchases_model->updatePurchase($id, $data, $products, $attachments)) {
            if ($status == 'received') {
                $this->convert_purchse_invoice($id);

                if($supplier_details->balance > 0){
                    $this->purchases_model->update_balance($supplier_details->id, $new_balance);
                }
            }

            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('purchase_added'));

            admin_redirect('purchases');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $inv;
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang('purchase_x_edited_older_than_x_days'), $this->Settings->disable_editing));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $inv_items = $this->purchases_model->getAllPurchaseItems($id);
            $end_date = date('d/m/Y h:i');
            $start_date = date('d/m/Y h:i', strtotime('-3 month'));
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $this->session->set_flashdata('error', lang('product_deleted_x_edit'));
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->sale_price = $item->sale_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->oqty = $item->quantity;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity - $row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = $item->option_id;
                $row->real_unit_cost = $item->real_unit_cost;
                //$row->cost             = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->cost = $item->unit_cost;
                $row->tax_rate = $item->tax_rate_id;
                $row->bonus = $item->bonus;
                $row->dis1 = $item->discount1;
                $row->dis2 = $item->discount2;
                $row->totalbeforevat = $item->totalbeforevat;
                $row->main_net = $item->main_net;
                $row->batchno = $item->batchno;
                $row->avz_item_code = isset($item->avz_item_code) && !empty($item->avz_item_code) ? $item->avz_item_code : '';
                $row->serial_number = $item->serial_number;
                $row->get_supplier_discount = $supplier_purchase_discount;
                $row->three_month_sale = $this->purchases_model->getThreeMonthSale($item->product_id, $start_date, $end_date);
                $row->warehouse_shelf = $item->warehouse_shelf;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = [
                    'id' => $c,
                    'item_id' => $row->id,
                    'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row,
                    'tax_rate' => $tax_rate,
                    'units' => $units,
                    'options' => $options,
                ];
                $c++;
            }

            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
            $this->data['purchase'] = $this->purchases_model->getPurchaseByID($id);
            $this->data['categories'] = $this->site->getAllCategories();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['shelves'] = $this->site->getAllShelf($inv->warehouse_id);
            $this->load->helper('string');
            $value = random_string('alnum', 20);
            $this->session->set_userdata('user_csrf', $value);
            $this->session->set_userdata('remove_pols', 1);
            $this->data['csrf'] = $this->session->userdata('user_csrf');
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('edit_purchase')]];
            $meta = ['page_title' => lang('edit_purchase'), 'bc' => $bc];
            $this->page_construct('purchases/edit', $meta, $this->data);
        }
    }

    public function edit_expense($id = null)
    {
        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference', lang('reference'), 'required');
        $this->form_validation->set_rules('amount', lang('amount'), 'required');
        $this->form_validation->set_rules('attachments', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $data = [
                'date' => $date,
                'reference' => $this->input->post('reference'),
                'amount' => $this->input->post('amount'),
                'note' => $this->input->post('note', true),
                'category_id' => $this->input->post('category', true),
                'warehouse_id' => $this->input->post('warehouse', true),
            ];

            $attachments = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            //$this->sma->print_arrays($data);
        } elseif ($this->input->post('edit_expense')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updateExpense($id, $data, $attachments)) {
            $this->session->set_flashdata('message', lang('expense_updated'));
            admin_redirect('purchases/expenses');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['expense'] = $this->purchases_model->getExpenseByID($id);
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['modal_js'] = $this->site->modal_js();
            $this->data['categories'] = $this->purchases_model->getExpenseCategories();
            $this->load->view($this->theme . 'purchases/edit_expense', $this->data);
        }
    }

    public function edit_payment($id = null)
    {
        $this->sma->checkPermissions('edit', true);
        $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('reference_no', lang('reference_no'), 'required');
        $this->form_validation->set_rules('amount-paid', lang('amount'), 'required');
        $this->form_validation->set_rules('paid_by', lang('paid_by'), 'required');
        $this->form_validation->set_rules('userfile', lang('attachment'), 'xss_clean');
        if ($this->form_validation->run() == true) {
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $payment = [
                'date' => $date,
                'purchase_id' => $this->input->post('purchase_id'),
                'reference_no' => $this->input->post('reference_no'),
                'amount' => $this->input->post('amount-paid'),
                'paid_by' => $this->input->post('paid_by'),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->sma->clear_tags($this->input->post('note')),
            ];

            if ($_FILES['userfile']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo = $this->upload->file_name;
                $payment['attachment'] = $photo;
            }

            //$this->sma->print_arrays($payment);
        } elseif ($this->input->post('edit_payment')) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updatePayment($id, $payment)) {
            $this->session->set_flashdata('message', lang('payment_updated'));
            admin_redirect('purchases');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['payment'] = $this->purchases_model->getPaymentByID($id);
            $this->data['modal_js'] = $this->site->modal_js();

            $this->load->view($this->theme . 'purchases/edit_payment', $this->data);
        }
    }

    public function email($purchase_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        $this->form_validation->set_rules('to', $this->lang->line('to') . ' ' . $this->lang->line('email'), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', $this->lang->line('subject'), 'trim|required');
        $this->form_validation->set_rules('cc', $this->lang->line('cc'), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', $this->lang->line('bcc'), 'trim|valid_emails');
        $this->form_validation->set_rules('note', $this->lang->line('message'), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($inv->created_by);
            }
            $to = $this->input->post('to');
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
            $supplier = $this->site->getCompanyByID($inv->supplier_id);
            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $inv->reference_no,
                'contact_person' => $supplier->name,
                'company' => $supplier->company,
                'site_link' => base_url(),
                'site_name' => $this->Settings->site_name,
                'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
            ];
            $msg = $this->input->post('note');
            $message = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($purchase_id, null, 'S');

            try {
                if ($this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $this->db->update('purchases', ['status' => 'ordered'], ['id' => $purchase_id]);
                    $this->session->set_flashdata('message', $this->lang->line('email_sent'));
                    admin_redirect('purchases');
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
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->Settings->theme . '/admin/views/email_templates/purchase.html')) {
                $purchase_temp = file_get_contents('themes/' . $this->Settings->theme . '/admin/views/email_templates/purchase.html');
            } else {
                $purchase_temp = file_get_contents('./themes/default/admin/views/email_templates/purchase.html');
            }
            $this->data['subject'] = [
                'name' => 'subject',
                'id' => 'subject',
                'type' => 'text',
                'value' => $this->form_validation->set_value('subject', lang('purchase_order') . ' (' . $inv->reference_no . ') ' . lang('from') . ' ' . $this->Settings->site_name),
            ];
            $this->data['note'] = [
                'name' => 'note',
                'id' => 'note',
                'type' => 'text',
                'value' => $this->form_validation->set_value('note', $purchase_temp),
            ];
            $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);

            $this->data['id'] = $purchase_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/email', $this->data);
        }
    }

    public function email_payment($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment = $this->purchases_model->getPaymentByID($id);
        $inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
        $supplier = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        if (!$supplier->email) {
            $this->sma->send_json(['msg' => lang('update_supplier_email')]);
        }
        $this->data['supplier'] = $supplier;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = lang('payment_note');
        $html = $this->load->view($this->theme . 'purchases/payment_note', $this->data, true);

        $html = str_replace(['<i class="fa fa-2x">&times;</i>', 'modal-', '<p>&nbsp;</p>', '<p style="border-bottom: 1px solid #666;">&nbsp;</p>', '<p>' . lang('stamp_sign') . '</p>'], '', $html);
        $html = preg_replace("/<img[^>]+\>/i", '', $html);
        // $html = '<div style="border:1px solid #DDD; padding:10px; margin:10px 0;">'.$html.'</div>';

        $this->load->library('parser');
        $parse_data = [
            'stylesheet' => '<link href="' . $this->data['assets'] . 'styles/helpers/bootstrap.min.css" rel="stylesheet"/>',
            'name' => $supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name,
            'email' => $supplier->email,
            'heading' => lang('payment_note') . '<hr>',
            'msg' => $html,
            'site_link' => base_url(),
            'site_name' => $this->Settings->site_name,
            'logo' => '<img src="' . base_url('assets/uploads/logos/' . $this->Settings->logo) . '" alt="' . $this->Settings->site_name . '"/>',
        ];
        $msg = file_get_contents('./themes/' . $this->Settings->theme . '/admin/views/email_templates/email_con.html');
        $message = $this->parser->parse_string($msg, $parse_data);
        $subject = lang('payment_note') . ' - ' . $this->Settings->site_name;

        if ($this->sma->send_email($supplier->email, $subject, $message)) {
            $this->sma->send_json(['msg' => lang('email_sent')]);
        } else {
            $this->sma->send_json(['msg' => lang('email_failed')]);
        }
    }

    public function expense_actions()
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
                        $this->purchases_model->deleteExpense($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('expenses_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                }

                if ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('expenses'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('amount'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('note'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('created_by'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $expense = $this->purchases_model->getExpenseByID($id);
                        $user = $this->site->getUser($expense->created_by);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($expense->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $expense->reference);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->sma->formatMoney($expense->amount));
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $expense->note);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $user->first_name . ' ' . $user->last_name);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'expenses_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_expense_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function expense_note($id = null)
    {
        $expense = $this->purchases_model->getExpenseByID($id);
        $this->data['user'] = $this->site->getUser($expense->created_by);
        $this->data['category'] = $expense->category_id ? $this->purchases_model->getExpenseCategoryByID($expense->category_id) : null;
        $this->data['warehouse'] = $expense->warehouse_id ? $this->site->getWarehouseByID($expense->warehouse_id) : null;
        $this->data['expense'] = $expense;
        $this->data['attachments'] = $this->site->getAttachments($id, 'expense');
        $this->data['page_title'] = $this->lang->line('expense_note');
        $this->load->view($this->theme . 'purchases/expense_note', $this->data);
    }

    /* -------------------------------------------------------------------------------- */

    public function expenses($id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('expenses')]];
        $meta = ['page_title' => lang('expenses'), 'bc' => $bc];
        $this->page_construct('purchases/expenses', $meta, $this->data);
    }

    public function getExpenses()
    {
        $this->sma->checkPermissions('expenses');

        $detail_link = anchor('admin/purchases/expense_note/$1', '<i class="fa fa-file-text-o"></i> ' . lang('expense_note'), 'data-toggle="modal" data-target="#myModal2"');
        $edit_link = anchor('admin/purchases/edit_expense/$1', '<i class="fa fa-edit"></i> ' . lang('edit_expense'), 'data-toggle="modal" data-target="#myModal"');
        //$attachment_link = '<a href="'.base_url('assets/uploads/$1').'" target="_blank"><i class="fa fa-chain"></i></a>';
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_expense') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete_expense/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_expense') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $edit_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';

        $this->load->library('datatables');

        $this->datatables
            ->select($this->db->dbprefix('expenses') . ".id as id, date, reference, {$this->db->dbprefix('expense_categories')}.name as category, amount, note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, attachment", false)
            ->from('expenses')
            ->join('users', 'users.id=expenses.created_by', 'left')
            ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
            ->group_by('expenses.id');

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }
        //$this->datatables->edit_column("attachment", $attachment_link, "attachment");
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }

    public function convert_purchse_invoice($pid)
    {
        if ($this->purchases_model->puchaseToInvoice($pid)) {
            // Delete exiting entry if available first
            $this->site->deleteAccountingEntry($pid, 'purchase');

            $inv = $this->purchases_model->getPurchaseByID($pid);
            $this->load->admin_model('companies_model');
            $supplier = $this->companies_model->getCompanyByID($inv->supplier_id);
            $inv_items = $this->purchases_model->getAllPurchaseItems($pid);
            $warehouse_id = $inv->warehouse_id;
            $warehouse_ledgers = $this->site->getWarehouseByID($warehouse_id);

            /*Accounts Entries*/
            $entry = array(
                'entrytype_id' => 4,
                'transaction_type' => 'purchaseorder',
                'number' => 'PO-' . $inv->reference_no,
                'date' => date('Y-m-d'),
                'dr_total' => $inv->grand_total,
                'cr_total' => $inv->grand_total,
                'notes' => 'Purchase Reference: ' . $inv->reference_no . ' Date: ' . date('Y-m-d H:i:s'),
                'pid' => $inv->id,
                'supplier_id' => $inv->supplier_id
            );
            $add = $this->db->insert('sma_accounts_entries', $entry);
            $insert_id = $this->db->insert_id();

            $entryitemdata = array();

            $inventory_amount = 0;
            foreach ($inv_items as $item) {
                $proid = $item->product_id;
                $product = $this->site->getProductByID($proid);
                //products
                /*$entryitemdata[] = array(
                    'Entryitem' => array(
                        'entry_id' => $insert_id,
                        'dc' => 'D',
                        //'ledger_id' => $product->inventory_account,
                        'ledger_id' => $warehouse_ledgers->inventory_ledger,
                        'amount' => $item->main_net,
                        'narration' => 'Inventory'
                    )
                );*/

                $inventory_amount += $item->main_net;

            }


           //supplier
                  $entryitemdata[] = array(
                    'Entryitem' => array(
                        'entry_id' => $insert_id,
                        'dc' => 'C',
                        'ledger_id' => $supplier->ledger_account,
                        //'amount' => $inv->grand_total + $inv->product_tax,
                        'amount' => $inv->grand_total,
                        'narration' => 'Accounts payable'
                    )
                );
    
            // Inventory Entry

            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    //'ledger_id' => $product->inventory_account,
                    'ledger_id' => $warehouse_ledgers->inventory_ledger,
                    'amount' => $inv->total_net_purchase,
                    'narration' => 'Inventory'
                )
            );

            //vat on purchase
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'D',
                    'ledger_id' => $this->vat_on_purchase,
                    //'amount' => $inv->order_tax,
                    'amount' => $inv->total_tax,
                    'narration' => 'Vat on Purchase'
                )
            );
      
            foreach ($entryitemdata as $row => $itemdata) {
                $this->db->insert('sma_accounts_entryitems', $itemdata['Entryitem']);
            }
        }
    }

    public function getPurchases($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');
        
        $pid = $this->input->get('pid');
        $pfromDate = $this->input->get('from');
        $ptoDate = $this->input->get('to');

        if ((!$this->Owner && !$this->Admin)) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link = anchor('admin/purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $transfer_link = anchor('admin/purchases/transfer/$1', '<i class="fa fa-money"></i> ' . lang('Transfer to Pharmacy'), 'data-toggle="modal" data-target="#myModal"');
        $journal_entry_link = anchor('admin/entries/view/journal/?pid=$1', '<i class="fa fa-eye"></i> ' . lang('Journal Entry'));

        if (isset($this->GP) && $this->GP['accountant']) {
            $convert_purchase_invoice = anchor('admin/purchases/convert_purchse_invoice/$1', '<i class="fa fa-money"></i> ' . lang('Convert to Invoice'));
        }

        $add_payment_link = anchor('admin/purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');

        $email_link = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('admin/purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('admin/purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $return_link = anchor('admin/returns_supplier/add/?purchase=$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_purchase') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('purchases/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_purchase') . '</a>';


        if (isset($this->GP) && $this->GP['accountant']) {

            $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $convert_purchase_invoice . '</li>
                <li>' . $detail_link . '</li>
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $pdf_link . '</li>
                <li>' . $email_link . '</li>
                <li>' . $print_barcode . '</li>
                <li>' . $return_link . '</li>
                <li>' . $journal_entry_link . '</li>
                <li>' . $delete_link . '</li> 
            </ul>
            </div></div>';

        } else {

            $action = '<div class="text-center"><div class="btn-group text-left">'
                . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
                . lang('actions') . ' <span class="caret"></span></button>
            <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $detail_link . '</li>
                <li>' . $payments_link . '</li>
                <li>' . $add_payment_link . '</li>
                <li>' . $edit_link . '</li>
                <li>' . $pdf_link . '</li>
                <li>' . $email_link . '</li>
                <li>' . $print_barcode . '</li>
                <li>' . $return_link . '</li>
                <li>' . $delete_link . '</li>
                <li>' . $transfer_link . '</li>
               <li>' . $journal_entry_link . '</li>
            </ul>
            </div></div>';


        }
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, sequence_code, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment")
                ->from('purchases')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, sequence_code, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment")
                ->from('purchases');
        }
        
        if(!empty($pid) && is_numeric($pid)) {
            $this->datatables->where('id', $pid);
        }

        if (!empty($pfromDate)) {
            $this->datatables->where('date >=', $pfromDate);
        }

        if (!empty($ptoDate)) {
            $this->datatables->where('date <=', $ptoDate);
        }

        // if($this->sma->checkPermissionsForRequest('p_status_pending'))
        // {
        //     $this->datatables->where('status', 'pending');
        //}
        // $this->datatables->where('status !=', 'returned');

        if (isset($this->GP) && $this->GP["purchase_supervisor"]) {
            $this->datatables->where('status', 'pending');
            $this->datatables->or_where('shelf_status', 'Shelves Added');

        }

        if (isset($this->GP) && $this->GP["purchase_manager"]) {
            $this->datatables->where('status', 'pending');
            $this->datatables->or_where('status', 'ordered');
            $this->datatables->or_where('status', 'rejected');
        }

        if (isset($this->GP) && $this->GP["purchase_receiving_supervisor"]) {
            $this->datatables->where('status', 'arrived');
            $this->datatables->or_where('status', 'received');
            $this->datatables->or_where('status', 'partial');
            $this->datatables->or_where('status', 'rejected');

        }

        if (isset($this->GP) && $this->GP["purchase_warehouse_supervisor"]) {
            $this->datatables->where('status', 'received');
            $this->datatables->or_where('status', 'partial');

        }
        if (isset($this->GP) && $this->GP["accountant"]) {
            $this->datatables->where('status', 'received');
            $this->datatables->or_where('status', 'partial');
            //$this->datatables->or_where('status', 'partial');

        }



        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->Pharmacist && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }

    public function getstatusPurchases($statuswise = null, $warehouse_id = null)
    {
        //$this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        $detail_link = anchor('admin/purchases/view/$1', '<i class="fa fa-file-text-o"></i> ' . lang('purchase_details'));
        $payments_link = anchor('admin/purchases/payments/$1', '<i class="fa fa-money"></i> ' . lang('view_payments'), 'data-toggle="modal" data-target="#myModal"');
        $add_payment_link = anchor('admin/purchases/add_payment/$1', '<i class="fa fa-money"></i> ' . lang('add_payment'), 'data-toggle="modal" data-target="#myModal"');
        $email_link = anchor('admin/purchases/email/$1', '<i class="fa fa-envelope"></i> ' . lang('email_purchase'), 'data-toggle="modal" data-target="#myModal"');
        $edit_link = anchor('admin/purchases/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_purchase'));
        $pdf_link = anchor('admin/purchases/pdf/$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $print_barcode = anchor('admin/products/print_barcodes/?purchase=$1', '<i class="fa fa-print"></i> ' . lang('print_barcodes'));
        $return_link = anchor('admin/purchases/return_purchase/$1', '<i class="fa fa-angle-double-left"></i> ' . lang('return_purchase'));
        $delete_link = "<a href='#' class='po' title='<b>" . $this->lang->line('delete_purchase') . "</b>' data-content=\"<p>"
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
            <li>' . $edit_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $email_link . '</li>
            <li>' . $print_barcode . '</li>
            <li>' . $return_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
        </div></div>';
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, sequence_code, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment")
                ->from('purchases')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("id, DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, sequence_code, supplier, status, grand_total, paid, (grand_total-paid) as balance, payment_status, attachment")
                ->from('purchases');
        }

        // if($this->sma->checkPermissionsForRequest('p_status_pending'))
        // {
        //     $this->datatables->where('status', 'pending');
        //}
        // $this->datatables->where('status !=', 'returned');

        if ($statuswise != null) {
            $this->datatables->where('status', $statuswise);
        }
        if (!$this->Customer && !$this->Supplier && !$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        } elseif ($this->Supplier) {
            $this->datatables->where('supplier_id', $this->session->userdata('user_id'));
        }
        $this->datatables->add_column('Actions', $action, 'id');
        echo $this->datatables->generate();
    }

    public function getSupplierCost($supplier_id, $product)
    {
        switch ($supplier_id) {
            case $product->supplier1:
                $cost = $product->supplier1price > 0 ? $product->supplier1price : $product->cost;
                break;
            case $product->supplier2:
                $cost = $product->supplier2price > 0 ? $product->supplier2price : $product->cost;
                break;
            case $product->supplier3:
                $cost = $product->supplier3price > 0 ? $product->supplier3price : $product->cost;
                break;
            case $product->supplier4:
                $cost = $product->supplier4price > 0 ? $product->supplier4price : $product->cost;
                break;
            case $product->supplier5:
                $cost = $product->supplier5price > 0 ? $product->supplier5price : $product->cost;
                break;
            default:
                $cost = $product->cost;
        }
        return $cost;
    }

    /* ------------------------------------------------------------------------- */

    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $this->data['lastInsertedId'] = $this->input->get('lastInsertedId');
        $this->data['pid'] = $this->input->get('pid');
        $this->data['pfromDate'] = $this->input->get('from');
        $this->data['ptoDate'] = $this->input->get('to');

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('purchases')]];
        $meta = ['page_title' => lang('purchases'), 'bc' => $bc];
        $this->page_construct('purchases/index', $meta, $this->data);
    }

    public function status($statuswise = null, $warehouse_id = null)
    {
        //$this->sma->checkPermissions();

        // $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
            $this->data['statuswise'] = $statuswise;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
            $this->data['statuswise'] = $statuswise;
        }

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('purchases')]];
        $meta = ['page_title' => lang('purchases'), 'bc' => $bc];
        $this->page_construct('purchases/status_listwise', $meta, $this->data);
    }

    /* ----------------------------------------------------------------------------- */

    public function modal_view($purchase_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $supplier = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['parent_supplier'] = '';
        if($supplier->level == 2 && $supplier->parent_code != '') {
            $parentSupplier = $this->site->getCompanyByParentCode($supplier->parent_code);
            if(isset($parentSupplier->name)) {
                $this->data['parent_supplier'] = $parentSupplier;
            }
        }
        $this->data['journal_entry'] = $this->site->getJournalEntryByTypeId('purchase', $purchase_id);
        $this->data['supplier'] = $supplier;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
        $this->data['attachments'] = $this->site->getAttachments($purchase_id, 'purchase');
        $this->data['purchase_id'] = $purchase_id;

        $this->load->view($this->theme . 'purchases/modal_view', $this->data);
    }

    public function payment_note($id = null)
    {
        $this->sma->checkPermissions('payments', true);
        $payment = $this->purchases_model->getPaymentByID($id);
        $inv = $this->purchases_model->getPurchaseByID($payment->purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payment'] = $payment;
        $this->data['page_title'] = $this->lang->line('payment_note');

        $this->load->view($this->theme . 'purchases/payment_note', $this->data);
    }

    /* -------------------------------------------------------------------------------- */

    public function payments($id = null)
    {
        $this->sma->checkPermissions(false, true);

        $this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
        $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
        $this->load->view($this->theme . 'purchases/payments', $this->data);
    }

    public function transfer($id = null)
    {
        $this->sma->checkPermissions();
        $this->data['purchase_id'] = $id;
        $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);

        if (empty($this->data['inv']->invoice_number) || $this->data['inv']->invoice_number == '') {
            $this->session->set_flashdata('error', 'Cannot transfer orders that are not invoiced');
            //return false;
            admin_redirect('purchases');
        }

        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['payments'] = $this->purchases_model->getPurchasePayments($id);
        $this->data['purchase_items'] = $this->purchases_model->getAllPurchaseItems($id);
        $this->load->view($this->theme . 'purchases/transfer', $this->data);

    }

    public function transfer_stock()
    {
        $this->sma->checkPermissions(false, true);

        $purchase_id = $this->input->post('purchase_id');
        $warehouse = $this->input->post('warehouse');
        $product_ids = $this->input->post('product_ids');
        
        $purchase_inovice = $this->purchases_model->getPurchaseItemsWithExclude($purchase_id, $product_ids);
        $purchase_detail = $this->purchases_model->getPurchaseByID($purchase_id);

        $check_existing_transfer = $this->transfers_model->getTransferByReferenceId($purchase_detail->reference_no);

        //if($check_existing_transfer){
        //    $this->session->set_flashdata('error', lang('Invoice with this reference no. is already transferred'));
        //    admin_redirect('purchases');
       // }else{
            $date = date('Y-m-d H:i:s');
            $transfer_no = $purchase_detail->reference_no;
            $purchase_notification = $purchase_detail->notification_id;

            $to_warehouse = $warehouse;
            $from_warehouse = $purchase_detail->warehouse_id;
            $note = $this->sma->clear_tags($purchase_detail->note);
            $shipping = $purchase_detail->shipping;
            //$status = 'completed';

            $status = $this->input->post('status');
            $from_warehouse_details = $this->site->getWarehouseByID($from_warehouse);
            $from_warehouse_code = $from_warehouse_details->code;
            $from_warehouse_name = $from_warehouse_details->name;
            $to_warehouse_details = $this->site->getWarehouseByID($to_warehouse);
            $to_warehouse_code = $to_warehouse_details->code;
            $to_warehouse_name = $to_warehouse_details->name;

            $total = 0;
            $product_tax = 0;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            $grand_total = 0;
            $grand_total_cost_price      = 0;

            if ($purchase_detail->return_id > 0) {
                // get purchase return products
                $purchase_inovice_return = $this->purchases_model->getAllPurchaseItems($purchase_detail->return_id);
                $return_products = array();
                foreach ($purchase_inovice_return as $row) {
                    $return_products[$row->product_id] =  $row->quantity;
                }
            }
        
            //

            for ($i = 0; $i < sizeOf($purchase_inovice); $i++) {

                $item_code = $purchase_inovice[$i]->product_code;
                $item_net_cost = $this->sma->formatDecimal($purchase_inovice[$i]->net_unit_cost);
                $unit_cost = $this->sma->formatDecimal($purchase_inovice[$i]->unit_cost);
                //$real_unit_cost = $this->sma->formatDecimal($purchase_inovice[$i]->real_unit_cost);
                $real_unit_cost = $unit_cost;
                $item_unit_quantity = $purchase_inovice[$i]->quantity;
                $item_returned_quantity = $purchase_inovice[$i]->returned_quantity;

                if($item_unit_quantity - $item_returned_quantity <= 0){
                    continue;
                }

                $item_tax_rate = $purchase_inovice[$i]->tax_rate_id;
                $item_batchno = $purchase_inovice[$i]->batchno;
                $item_serial_no = $purchase_inovice[$i]->serial_number;
                $item_expiry = isset($purchase_inovice[$i]->expiry) ? $purchase_inovice[$i]->expiry : null;

                $item_option = $purchase_inovice[$i]->option_id;
                $item_unit = $purchase_inovice[$i]->product_unit_id;
                $item_quantity = $purchase_inovice[$i]->quantity;
                $avz_code = $purchase_inovice[$i]->avz_item_code;
                    //check quantity with reutrn products
                    $pid = $purchase_inovice[$i]->product_id;
                    if( isset($return_products[$pid]) ) {
                        $item_quantity = $purchase_inovice[$i]->quantity + $return_products[$pid];
                        if($item_quantity <= 0) {
                            continue;
                        }
                    }
        

                $unit_cost = $item_net_cost;

                $product_details = $this->transfers_model->getProductById($purchase_inovice[$i]->product_id);

                $net_cost = $item_net_cost;
                $real_cost = $real_unit_cost;
                //$net_cost = $this->site->getAvgCost($item_batchno, $product_details->id);
                //$real_cost = $this->site->getRealAvgCost($item_batchno, $product_details->id);

                if (isset($item_code) && isset($item_quantity)) {

                    $warehouse_quantity = $this->transfers_model->getWarehouseProduct($from_warehouse_details->id, $product_details->id, $item_option, $item_batchno);

                    /*if ($warehouse_quantity->quantity < $item_quantity) {
                        $this->session->set_flashdata('error', lang('no_match_found') . ' (' . lang('product_name') . ' <strong>' . $product_details->name . '</strong> ' . lang('product_code') . ' <strong>' . $product_details->code . '</strong>)');
                        admin_redirect('purchases');
                    }*/

                    $pr_item_tax = $item_tax = 0;
                    $tax = '';
                    $item_net_cost = $unit_cost;

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $tax_rate = $tax_details->rate;
                        //$ctax = $this->site->calculateTax($product_details, $tax_details, $purchase_inovice[$i]->sale_price);
                        //$item_tax = $ctax['amount'];
                        //$tax = $ctax['tax'];

                        //echo '<pre>';print_r($tax_details);exit;

                        $item_tax = (($purchase_inovice[$i]->sale_price) * $tax_rate / 100); 
                        $tax = $tax_rate;

                        if (!empty($product_details) && $product_details->tax_method != 1) {
                            $item_net_cost = $purchase_inovice[$i]->sale_price - $item_tax;
                        }

                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, false, $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($purchase_inovice[$i]->sale_price * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id' => $product_details->id,
                        'product_code' => $item_code,
                        'product_name' => $product_details->name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $net_cost,
                        'unit_cost' => $this->sma->formatDecimal($item_net_cost + $item_tax, 4),
                        'quantity' => ($item_quantity - $item_returned_quantity),
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => ($item_unit_quantity - $item_returned_quantity),
                        'quantity_balance' => ($item_quantity - $item_returned_quantity),
                        'warehouse_id' => $to_warehouse,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => str_replace('%', '', $tax),
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $real_unit_cost,
                        'sale_price' => $this->sma->formatDecimal($purchase_inovice[$i]->sale_price, 4),
                        'date' => date('Y-m-d', strtotime($date)),
                        'batchno' => $item_batchno,
                        'serial_number' => $item_serial_no,
                        'real_cost' => $real_cost,
                        'avz_item_code'     => $avz_code
                    ];

                    $products[] = ($product + $gst_data);
                    $total += ($purchase_inovice[$i]->sale_price * $item_unit_quantity);
                    $grand_total += $subtotal;
                    $grand_total_cost_price +=  ($net_cost* $item_unit_quantity);   
                }

            }

            if (empty($products)) {
                $this->session->set_flashdata('error', lang('No products found to transfer'));
                admin_redirect('purchases');
            } else {
                krsort($products);
            }

            //$grand_total = $this->sma->formatDecimal(($total), 4);
            $data = [
                'transfer_no' => $transfer_no,
                'date' => $date,
                'from_warehouse_id' => $from_warehouse,
                'from_warehouse_code' => $from_warehouse_code,
                'from_warehouse_name' => $from_warehouse_name,
                'to_warehouse_id' => $to_warehouse,
                'to_warehouse_code' => $to_warehouse_code,
                'to_warehouse_name' => $to_warehouse_name,
                'note' => $note,
                'total_tax' => $product_tax,
                'total' => $total,
                'total_cost' => $grand_total_cost_price,
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'status' => $status,
                'shipping' => $shipping,
                'type' => 'transfer',
                'sequence_code' => $this->sequenceCode->generate('TR', 5)
            ];

            $attachments = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);

            //$this->sma->print_arrays($data, $products);exit;

            //if ($this->transfers_model->transferPurchaseInvoice($data, $products, $attachments)) {
            if ($transfer_id = $this->transfers_model->addTransfer($data, $products, $attachments)) {

                $this->session->set_flashdata('message', lang('transfer_added'));
                admin_redirect('transfers');
            } else {
                $this->session->set_flashdata('error', lang('Error adding transfer'));
                admin_redirect('purchases');
            }
       // }
    }

    /* ----------------------------------------------------------------------------- */

    //generate pdf and force to download

    public function pdf($purchase_id = null, $view = null, $save_bufffer = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['inv'] = $inv;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
        $name = $this->lang->line('purchase') . '_' . str_replace('/', '_', $inv->reference_no) . '.pdf';
        $html = $this->load->view($this->theme . 'purchases/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            echo $html;
            die();
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        }
        $this->sma->generate_pdf($html, $name);
    }

    /* -------------------------------------------------------------------------------- */

    public function purchase_actions()
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
                        $this->purchases_model->deletePurchase($id);
                    }
                    $this->session->set_flashdata('message', $this->lang->line('purchases_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('purchases'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('supplier'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $purchase = $this->purchases_model->getPurchaseByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($purchase->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $purchase->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $purchase->supplier);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $purchase->status);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->formatMoney($purchase->grand_total));
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'purchases_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', $this->lang->line('no_purchase_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    /* ----------------------------------------------------------------------------------------------------------- */

    public function purchase_by_csv()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
        $this->form_validation->set_rules('userfile', $this->lang->line('upload_file'), 'xss_clean');

        if ($this->form_validation->run() == true) {
            $quantity = 'quantity';
            $product = 'product';
            $unit_cost = 'unit_cost';
            $tax_rate = 'tax_rate';
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = null;
            }
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $status = $this->input->post('status');
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = $supplier_details->company && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));

            $total_sale_price = 0;
            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;

            if (isset($_FILES['userfile'])) {
                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;

                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('purchases/purchase_by_csv');
                }

                $csv = $this->upload->file_name;

                $arrResult = [];
                $handle = fopen($this->digital_upload_path . $csv, 'r');
                if ($handle) {
                    while (($row = fgetcsv($handle, 5000, ',')) !== false) {
                        $arrResult[] = $row;
                    }
                    fclose($handle);
                }
                $arr_length = count($arrResult);
                if ($arr_length > 5000000) {
                    $this->session->set_flashdata('error', lang('too_many_products'));
                    redirect($_SERVER['HTTP_REFERER']);
                    exit();
                }
                $titles = array_shift($arrResult);
                $keys = ['code', 'net_unit_cost', 'quantity', 'variant', 'item_tax_rate', 'expiry', 'sale_price', 'batchno', 'serial_number', 'discount1', 'discount2'];
                $final = [];
                foreach ($arrResult as $key => $value) {
                    $final[] = array_combine($keys, $value);
                }
                $rw = 2;
                foreach ($final as $csv_pr) {
                    if (isset($csv_pr['code']) && isset($csv_pr['net_unit_cost']) && isset($csv_pr['quantity'])) {
                        if ($product_details = $this->purchases_model->getProductByCode($csv_pr['code'])) {
                            if ($csv_pr['variant']) {
                                $item_option = $this->purchases_model->getProductVariantByName($csv_pr['variant'], $product_details->id);
                                if (!$item_option) {
                                    $this->session->set_flashdata('error', lang('pr_not_found') . ' ( ' . $product_details->name . ' - ' . $csv_pr['variant'] . ' ). ' . lang('line_no') . ' ' . $rw);
                                    redirect($_SERVER['HTTP_REFERER']);
                                }
                            } else {
                                $item_option = json_decode('{}');
                                $item_option->id = null;
                            }

                            $item_code = $csv_pr['code'];
                            $item_net_cost = $this->sma->formatDecimal($csv_pr['net_unit_cost']);
                            $item_quantity = $csv_pr['quantity'];
                            $quantity_balance = $csv_pr['quantity'];
                            $item_tax_rate = $csv_pr['item_tax_rate'];
                            $item_discount = $csv_pr['discount'];
                            $item_expiry = isset($csv_pr['expiry']) ? $this->sma->fsd($csv_pr['expiry']) : null;

                            $item_sale_price = $csv_pr['sale_price'];
                            $item_batchno = $csv_pr['batchno'];
                            $item_serial_number = $csv_pr['serial_number'];
                            $item_discount1 = $csv_pr['discount1'];
                            $item_discount2 = $csv_pr['discount2'];

                            $pr_discount = 0;
                            // $pr_discount      = $this->site->calculateDiscount($item_discount, $item_net_cost);
                            // $pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_quantity), 4);
                            // $product_discount += $pr_item_discount;

                            $total_purchases = $item_net_cost * $item_quantity;
                            $total_after_dicount_1 = $total_purchases * ($item_discount1 / 100);
                            $total_after_dicount_2 = ($total_purchases - $total_after_dicount_1) * ($item_discount2 / 100);
                            $main_net = $total_purchases - ($total_after_dicount_1 + $total_after_dicount_2);


                            $tax = '';
                            $pr_item_tax = 0;
                            $item_net_cost = $item_net_cost - $pr_discount;
                            $unit_cost = $item_net_cost;
                            $gst_data = [];
                            $tax_details = ((isset($item_tax_rate) && !empty($item_tax_rate)) ? $this->purchases_model->getTaxRateByName($item_tax_rate) : $this->site->getTaxRateByID($product_details->tax_rate));
                            if ($tax_details) {
                                $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                                $item_tax = $this->sma->formatDecimal($ctax['amount']);
                                $tax = $ctax['tax'];
                                if ($product_details->tax_method != 1) {
                                    $item_net_cost = $unit_cost - $item_tax;
                                }
                                $pr_item_tax = $this->sma->formatDecimal($item_tax * $item_quantity, 4);
                                if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                                    $total_cgst += $gst_data['cgst'];
                                    $total_sgst += $gst_data['sgst'];
                                    $total_igst += $gst_data['igst'];
                                }
                            }

                            $product_tax += $pr_item_tax;
                            // $subtotal       = (($item_net_cost * $item_quantity) + $pr_item_tax);
                            $subtotal = $main_net;
                            $subtotal2 = (($item_net_cost * $item_quantity));// + $pr_item_tax);


                            $unit = $this->site->getUnitByID($product_details->unit);
                            $real_unit_cost = $this->sma->formatDecimal(($unit_cost + $pr_discount), 4);
                            $product = [
                                'product_id' => $product_details->id,
                                'product_code' => $item_code,
                                'product_name' => $product_details->name,
                                'option_id' => $item_option->id,
                                'net_unit_cost' => $item_net_cost,
                                'quantity' => $item_quantity,
                                'product_unit_id' => $product_details->unit,
                                'product_unit_code' => $unit->code,
                                'unit_quantity' => $item_quantity,
                                'quantity_balance' => $quantity_balance,
                                'warehouse_id' => $warehouse_id,
                                'item_tax' => $pr_item_tax,
                                'tax_rate_id' => $tax_details ? $tax_details->id : null,
                                'tax' => $tax,
                                'discount' => $item_discount,
                                'item_discount' => $pr_item_discount,
                                'expiry' => $item_expiry,
                                'sale_price' => $item_sale_price,
                                'batchno' => $item_batchno,
                                'serial_number' => $item_serial_number,
                                'discount1' => $item_discount1,
                                'discount2' => $item_discount2,
                                'totalbeforevat' => $total_after_dicount_2,
                                'main_net' => $main_net,
                                'subtotal' => $this->sma->formatDecimal($subtotal),
                                'subtotal2' => $this->sma->formatDecimal($subtotal2),
                                'date' => date('Y-m-d', strtotime($date)),
                                'status' => $status,
                                'unit_cost' => $unit_cost, // $this->sma->formatDecimal(($item_net_cost + $item_tax), 4),
                                'real_unit_cost' => $real_unit_cost,
                                'base_unit_cost' => $real_unit_cost,
                            ];

                            $products[] = ($product + $gst_data);
                            // $total += $this->sma->formatDecimal(($item_net_cost * $item_quantity), 4);
                            $total += $this->sma->formatDecimal($main_net, 4);
                            $total_sale_price += $this->sma->formatDecimal($item_sale_price, 4);

                        } else {
                            $this->session->set_flashdata('error', $this->lang->line('pr_not_found') . ' ( ' . $csv_pr['code'] . ' ). ' . $this->lang->line('line_no') . ' ' . $rw);
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                        $rw++;
                    }
                }
            }

            // $order_discount = $this->site->calculateDiscount($this->input->post('discount') ? $this->input->post('order_discount') : null, ($total + $product_tax), true);
            $order_discount = $this->site->calculateDiscount($this->input->post('discount'), $total, true);//$this->site->calculateDiscount($this->input->post('discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);

            // $grand_total    = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $data = [
                'reference_no' => $reference,
                'date' => $date,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'total' => $total,
                'total_sale' => $total_sale_price,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'shipping' => $this->sma->formatDecimal($shipping),
                'grand_total' => $grand_total,
                'status' => $status,
                'sequence_code' => $this->sequenceCode->generate('PR', 5),
                'created_by' => $this->session->userdata('username'),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            $attachments = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products, $attachments)) {
            $this->session->set_flashdata('message', $this->lang->line('purchase_added'));
            admin_redirect('purchases');
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['ponumber'] = ''; // $this->site->getReference('po');

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('add_purchase_by_csv')]];
            $meta = ['page_title' => lang('add_purchase_by_csv'), 'bc' => $bc];
            $this->page_construct('purchases/purchase_by_csv', $meta, $this->data);
        }
    }

    public function convert_return_invoice($pid, $oid)
    {
        $inv = $this->purchases_model->getPurchaseByID($pid);
        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($inv->supplier_id);
        $inv_items = $this->purchases_model->getAllPurchaseItems($pid);
        $warehouse_id = $inv->warehouse_id;
        $warehouse_ledgers = $this->site->getWarehouseByID($warehouse_id);

        //$inv = $this->purchases_model->getReturnByID($rid);

        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => 'returnorder',
            'number' => 'RO-' . $inv->reference_no,
            'date' => date('Y-m-d'),
            'dr_total' => $inv->grand_total,
            'cr_total' => $inv->grand_total,
            'notes' => 'Return Reference: ' . $inv->reference_no . ' Date: ' . date('Y-m-d H:i:s'),
            'pid' => $inv->id
        );
        $add = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        $entryitemdata = array();

        //$inv_items = $this->purchases_model->getReturnItems($sid);
        $inventory_amount = 0;
        foreach ($inv_items as $item) {
            $proid = $item->product_id;
            $product = $this->site->getProductByID($proid);
            //products
            /*$entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'C',
                    //'ledger_id' => $product->inventory_account,
                    'ledger_id' => $warehouse_ledgers->inventory_ledger,
                    'amount' => -1 * ($item->net_unit_cost * $item->quantity),
                    'narration' => 'Inventory'
                )
            );*/

            $inventory_amount += (-1 * ($item->net_unit_cost * $item->quantity));
        }

        // Inventory Entry
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                //'ledger_id' => $product->inventory_account,
                'ledger_id' => $warehouse_ledgers->inventory_ledger,
                'amount' => $inventory_amount,
                'narration' => 'Inventory'
            )
        );

        //vat on purchase
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $this->vat_on_purchase,
                //'amount' => $inv->order_tax,
                'amount' => -1 * ($inv->product_tax),
                'narration' => 'Vat on Purchase'
            )
        );

        //supplier
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $supplier->ledger_account,
                'amount' => -1 * ($inv->grand_total),
                'narration' => 'Accounts payable'
            )
        );

        foreach ($entryitemdata as $row => $itemdata) {
            $this->db->insert('sma_accounts_entryitems', $itemdata['Entryitem']);
        }

    }

    public function return_purchase($id = null)
    {
        $this->sma->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $purchase = $this->purchases_model->getPurchaseByID($id);
        /*if ($purchase->return_id) {
            $this->session->set_flashdata('error', lang('purchase_already_returned'));
            redirect($_SERVER['HTTP_REFERER']);
        }*/
        $this->form_validation->set_rules('return_surcharge', lang('return_surcharge'), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('rep');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }

            $return_surcharge = $this->input->post('return_surcharge') ? $this->input->post('return_surcharge') : 0;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $supplier_details = $this->site->getCompanyByID($purchase->supplier_id);

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            $i = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_code = $_POST['product'][$r];
                $avz_item_code = $_POST['avz_item_code'][$r];
                $purchase_item_id = $_POST['purchase_item_id'][$r];
                $item_option = isset($_POST['product_option'][$r]) && !empty($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' ? $_POST['product_option'][$r] : null;
                $real_unit_cost = $this->sma->formatDecimal($_POST['real_unit_cost'][$r]);
                $unit_cost = $this->sma->formatDecimal($_POST['unit_cost'][$r]);
                $sale_price = $this->sma->formatDecimal($_POST['sale_price'][$r]);
                $item_unit_quantity = (0 - ($_POST['quantity'][$r] + $_POST['bonus'][$r]));
                $item_bonus = $_POST['bonus'][$r];
                //$item_expiry        = $_POST['expiry'][$r]           ?? '';

                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;

                $item_tax_rate = $_POST['product_tax'][$r] ?? null;
                $item_discount = $_POST['discount1'][$r] ?? null;
                $item_discount2 = $_POST['discount2'][$r] ?? null;
                //$item_discount      = $_POST['product_discount'][$r] ?? null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = (0 - $_POST['product_base_quantity'][$r]);
                $item_batch = $_POST['batch_no'][$r];

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {
                    $product_details = $this->purchases_model->getProductByCode($item_code);

                    $item_type = $product_details->type;
                    $item_name = $product_details->name;
                    //$pr_discount    = $this->site->calculateDiscount($item_discount, $unit_cost);
                    $pr_discount = $this->site->calculateDiscount($item_discount . '%', $unit_cost);
                    $amount_after_dis1 = $unit_cost - $pr_discount;
                    $pr_discount2 = $this->site->calculateDiscount($item_discount2 . '%', $amount_after_dis1);

                    //$unit_cost        = $this->sma->formatDecimal($unit_cost - $pr_discount);
                    $item_net_cost = $unit_cost - $pr_discount - $pr_discount2;
                    //$pr_item_discount = $this->sma->formatDecimal(($pr_discount * $item_unit_quantity), 4);

                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * ($item_unit_quantity + $item_bonus));
                    $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * ($item_unit_quantity + $item_bonus));
                    $product_discount += ($pr_item_discount + $pr_item_discount2);
                    //$product_discount += $pr_item_discount;
                    //$item_net_cost = $unit_cost;
                    
                    $pr_item_tax = $item_tax = 0;
                    $tax = '';
                    $totalpurcahsesbeforevat = ($unit_cost * ($item_quantity + $item_bonus)) - $pr_item_discount - $pr_item_discount2;
                    
                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax = $this->sma->formatDecimal($ctax['amount']);
                        $tax = $ctax['tax'];
                        if ($product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }
                        //$pr_item_tax = $this->sma->formatDecimal($item_tax * $item_unit_quantity, 4);
                        $pr_item_tax = $this->sma->formatDecimal(($totalpurcahsesbeforevat * ($tax_details->rate / 100)), 4);

                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($this->Settings->state == $supplier_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }
                    //echo $totalpurcahsesbeforevat.' -- '.$item_quantity.' -- '.$item_bonus;exit;
                    $item_net_cost = ($totalpurcahsesbeforevat) / ($item_quantity);

                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity)), 4);
                    $main_net = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity)), 4);
                    $subtotal2 = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'option_id' => $item_option,
                        'net_unit_cost' => $item_net_cost,
                        'unit_cost' => $this->sma->formatDecimal($unit_cost),
                        'quantity' => $item_quantity,
                        'batchno' => $item_batch,
                        'bonus' => $item_bonus,
                        'expiry' => $item_expiry,
                        'discount1' => $item_discount,
                        'discount2' => $item_discount2,
                        'product_unit_id' => $item_unit,
                        'product_unit_code' => $unit->code,
                        'unit_quantity' => $item_unit_quantity,
                        'quantity_balance' => $item_quantity,
                        'warehouse_id' => $purchase->warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'subtotal2' => $this->sma->formatDecimal($subtotal2),
                        'real_unit_cost' => $real_unit_cost,
                        'sale_price' => $sale_price,
                        'purchase_item_id' => $purchase_item_id,
                        'status' => 'received',
                        'main_net' => $main_net,
                        'avz_item_code' => $avz_item_code
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

            $order_discount = $this->site->calculateDiscount($this->input->post('order_discount'), ($total + $product_tax), true);
            $total_discount = $this->sma->formatDecimal(($order_discount + $product_discount), 4);
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($total_tax) + $this->sma->formatDecimal($return_surcharge) - $this->sma->formatDecimal($order_discount)), 4);
            $data = [
                'date' => $date,
                'purchase_id' => $id,
                'reference_no' => $purchase->reference_no,
                'supplier_id' => $purchase->supplier_id,
                'supplier' => $purchase->supplier,
                'warehouse_id' => $purchase->warehouse_id,
                'note' => $note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => ($this->input->post('discount') ? $this->input->post('order_discount') : null),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'surcharge' => $this->sma->formatDecimal($return_surcharge),
                'grand_total' => $grand_total,
                'created_by' => $this->session->userdata('user_id'),
                'return_purchase_ref' => $reference,
                'status' => 'returned',
                'sequence_code' => $this->sequenceCode->generate('PR', 5),
                'payment_status' => $purchase->payment_status == 'paid' ? 'due' : 'pending',
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            $attachments = $this->attachments->upload();
            $data['attachment'] = !empty($attachments);
            //$this->sma->print_arrays($data, $products); exit; 
        }

        if ($this->form_validation->run() == true && $this->purchases_model->addPurchase($data, $products, $attachments)) {
            $purchase_after_return = $this->purchases_model->getPurchaseByID($id);
            if ($purchase_after_return->return_id) {
                $this->convert_return_invoice($purchase_after_return->return_id, $id);
            }

            $this->session->set_flashdata('message', lang('return_purchase_added'));
            admin_redirect('purchases');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            $this->data['inv'] = $purchase;
            if ($this->data['inv']->status != 'received' && $this->data['inv']->status != 'partial') {
                $this->session->set_flashdata('error', lang('purchase_status_x_received'));
                redirect($_SERVER['HTTP_REFERER']);
            }
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang('purchase_x_edited_older_than_x_days'), $this->Settings->disable_editing));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $inv_items = $this->purchases_model->getAllReturnInvoiceItems($id); 
            // krsort($inv_items);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {
                $row = $this->site->getProductByID($item->product_id);
                $this->Inventory_model->get_current_stock($item->product_id,'null',$item->batchno);
                $row->batchno = $item->batchno;
                $row->bonus = $item->total_bonus;
                $row->obonus = $item->bonus;
                $row->avz_item_code = $item->avz_item_code;
                $row->discount1 = $item->discount1;
                $row->discount2 = $item->discount2;
                // $row->discount2          = $item->discount2;
                $row->net_unit_cost = $item->net_unit_cost;
                $row->expiry = (($item->expiry && $item->expiry != '0000-00-00') ? $this->sma->hrsd($item->expiry) : '');
                $row->base_quantity = $item->quantity;
                
                $row->base_unit = $row->unit ? $row->unit : $item->product_unit_id;
                $row->base_unit_cost = $row->cost ? $row->cost : $item->unit_cost;
                $row->unit = $item->product_unit_id;
                //$row->qty = $item->unit_quantity - $row->bonus;
                //$row->oqty = $item->unit_quantity - $row->bonus;
                $row->qty = $item->total_quantity - $row->bonus;
                $row->oqty = $item->total_quantity - $row->bonus;
                
                $row->purchase_item_id = $item->id;
                $row->supplier_part_no = $item->supplier_part_no;
                $row->received = $item->quantity_received ? $item->quantity_received : $item->quantity;
                $row->received = $row->received - $row->bonus;
                $row->quantity_balance = $item->quantity_balance + ($item->quantity - $row->received);
                $row->discount = $item->discount ? $item->discount : '0';
                $options = $this->purchases_model->getProductOptions($row->id);
                $row->option = !empty($item->option_id) ? $item->option_id : '';
                $row->real_unit_cost = $item->real_unit_cost;
                //$row->cost             = $this->sma->formatDecimal($item->net_unit_cost + ($item->item_discount / $item->quantity));
                $row->cost = $this->sma->formatDecimal($item->unit_cost);
                $row->sale_price = $this->sma->formatDecimal($item->sale_price);
                $row->tax_rate = $item->tax_rate_id;
                $row->main_net = $item->main_net;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')', 'row' => $row, 'units' => $units, 'tax_rate' => $tax_rate, 'options' => $options];

                $c++;
            }
            
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['reference'] = '';
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('return_purchase')]];
            $meta = ['page_title' => lang('return_purchase'), 'bc' => $bc];
            $this->page_construct('purchases/return_purchase', $meta, $this->data);
        }
    }

    /* --------------------------------------------------------------------------- */

    /*  public function suggestions()
      {
          $term        = $this->input->get('term', true);
          $supplier_id = $this->input->get('supplier_id', true);

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

          $rows = $this->purchases_model->getProductNames($sr);
          if ($rows) {
              $r = 0;
              foreach ($rows as $row) {
                  $c                    = uniqid(mt_rand(), true);
                  $option               = false;
                  $row->item_tax_method = $row->tax_method;
                  $options              = $this->purchases_model->getProductOptions($row->id);
                  if ($options) {
                      $opt = $option_id && $r == 0 ? $this->purchases_model->getProductOptionByID($option_id) : current($options);
                      if (!$option_id || $r > 0) {
                          $option_id = $opt->id;
                      }
                  } else {
                      $opt       = json_decode('{}');
                      $opt->cost = 0;
                      $option_id = false;
                  }
                  if(gettype($options) == 'array')
                  {
                      if(count($options) > 1)
                      {
                          foreach($options as $ooid)
                          {
                             $row->option           = $ooid->id;
                              $row->supplier_part_no = '';
                              if ($row->supplier1 == $supplier_id) {
                                  $row->supplier_part_no = $row->supplier1_part_no;
                              } elseif ($row->supplier2 == $supplier_id) {
                                  $row->supplier_part_no = $row->supplier2_part_no;
                              } elseif ($row->supplier3 == $supplier_id) {
                                  $row->supplier_part_no = $row->supplier3_part_no;
                              } elseif ($row->supplier4 == $supplier_id) {
                                  $row->supplier_part_no = $row->supplier4_part_no;
                              } elseif ($row->supplier5 == $supplier_id) {
                                  $row->supplier_part_no = $row->supplier5_part_no;
                              }
                              if ($opt->cost != 0) {
                                  $row->cost = $opt->cost;
                              }
                              $row->cost             = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                              $row->real_unit_cost   = $row->cost;
                              $row->base_quantity    = 1;
                              $row->base_unit        = $row->unit;
                              $row->base_unit_cost   = $row->cost;
                              $row->unit             = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                              $row->new_entry        = 1;
                              $row->expiry           = '';
                              $row->qty              = 1;
                              $row->quantity_balance = '';
                              $row->discount         = '0';
                              unset($row->details, $row->product_details, $row->price, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                              if ($qty) {
                                  $row->qty           = $qty;
                                  $row->base_quantity = $qty;
                              } else {
                                  $row->qty = ($bprice ? $bprice / $row->cost : 1);
                              }
              
                              $units    = $this->site->getUnitsByBUID($row->base_unit);
                              $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
              
                              if ($row->purchase_unit) {
                                  foreach ($units as $unit) {
                                      if ($unit->id == $row->purchase_unit) {
                                          $row->real_unit_cost = $this->site->convertToUnit($unit, $row->cost);
                                      }
                                  }
                              }
              
                              $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . '('.$row->code.'-' . $ooid->id . ')',
                                  'row'     => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                              $r++;
                          }
                      }
                  }else{
                      
                  $row->option           = $option_id;
                  $row->supplier_part_no = '';
                  if ($row->supplier1 == $supplier_id) {
                      $row->supplier_part_no = $row->supplier1_part_no;
                  } elseif ($row->supplier2 == $supplier_id) {
                      $row->supplier_part_no = $row->supplier2_part_no;
                  } elseif ($row->supplier3 == $supplier_id) {
                      $row->supplier_part_no = $row->supplier3_part_no;
                  } elseif ($row->supplier4 == $supplier_id) {
                      $row->supplier_part_no = $row->supplier4_part_no;
                  } elseif ($row->supplier5 == $supplier_id) {
                      $row->supplier_part_no = $row->supplier5_part_no;
                  }
                  if ($opt->cost != 0) {
                      $row->cost = $opt->cost;
                  }
                  $row->cost             = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                  $row->real_unit_cost   = $row->cost;
                  $row->base_quantity    = 1;
                  $row->base_unit        = $row->unit;
                  $row->base_unit_cost   = $row->cost;
                  $row->unit             = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                  $row->new_entry        = 1;
                  $row->expiry           = '';
                  $row->qty              = 1;
                  $row->quantity_balance = '';
                  $row->discount         = '0';
                  unset($row->details, $row->product_details, $row->price, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                  if ($qty) {
                      $row->qty           = $qty;
                      $row->base_quantity = $qty;
                  } else {
                      $row->qty = ($bprice ? $bprice / $row->cost : 1);
                  }

                  $units    = $this->site->getUnitsByBUID($row->base_unit);
                  $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                  if ($row->purchase_unit) {
                      foreach ($units as $unit) {
                          if ($unit->id == $row->purchase_unit) {
                              $row->real_unit_cost = $this->site->convertToUnit($unit, $row->cost);
                          }
                      }
                  }

                  $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                      'row'     => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                  $r++;
                  }
                  
              }
              $this->sma->send_json($pr);
          } else {
              $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
          }
      }*/

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

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        $supplier_id = $this->input->get('supplier_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $extracted_data = $this->extract_gs1_data($term);
        $search_term = $extracted_data['gtin'] ?? $term;
        $batch_number = $extracted_data['batch_number'] ?? null;
        $expiry_date = $extracted_data['expiry_date'] ?? null;

        $analyzed = $this->sma->analyze_term($search_term);

        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $sr = addslashes($sr);
        $strict = $analyzed['strict'] ?? false;
        $qty = $strict ? null : $analyzed['quantity'] ?? null;
        $bprice = $strict ? null : $analyzed['price'] ?? null;

        $rows = $this->purchases_model->getProductNames($sr);
        $end_date = date('d/m/Y h:i');
        $start_date = date('d/m/Y h:i', strtotime('-3 month'));

        if ($rows) {
            $r = 0;
            $count = 0;
            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                $option = false;
                $row->item_tax_method = $row->tax_method;
                $options = $this->purchases_model->getProductOptions($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->purchases_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = false;
                }
                $row->option = $option_id;
                $row->supplier_part_no = '';
                if ($row->supplier1 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier1_part_no;
                } elseif ($row->supplier2 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier2_part_no;
                } elseif ($row->supplier3 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier3_part_no;
                } elseif ($row->supplier4 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier4_part_no;
                } elseif ($row->supplier5 == $supplier_id) {
                    $row->supplier_part_no = $row->supplier5_part_no;
                }
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                $count++;
                $row->serial_no = $count;
                $row->cost = $supplier_id ? $this->getSupplierCost($supplier_id, $row) : $row->cost;
                $row->real_unit_cost = $row->cost;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_cost = $row->cost;
                $row->sale_price = $row->price;
                $row->unit = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->new_entry = 1;
                $row->expiry = $expiry_date != null ? $expiry_date : '';
                $row->qty = 1;
                $row->quantity_balance = '';
                $row->discount = '0';
                $row->bonus = 0;
                $row->dis1 = 0;
                $row->dis2 = 0;
                $row->batchno = $batch_number != null ? $batch_number : '';
                $row->avz_item_code = '';
                $row->serial_number = '';
                $row->warehouse_shelf = '';
                //$row->three_month_sale = $this->purchases_model->getThreeMonthSale($row->id,$start_date,$end_date);
                $row->get_supplier_discount = $this->deals_model->getPurchaseDiscount($supplier_id);
                unset($row->details, $row->product_details, $row->price, $row->file, $row->supplier1price, $row->supplier2price, $row->supplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                if ($qty) {
                    $row->qty = $qty;
                    $row->base_quantity = $qty;
                } else {
                    $row->qty = ($bprice ? $bprice / $row->cost : 1);
                }

                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                if ($row->purchase_unit) {
                    foreach ($units as $unit) {
                        if ($unit->id == $row->purchase_unit) {
                            $row->real_unit_cost = $this->site->convertToUnit($unit, $row->cost);
                        }
                    }
                }

                $pr[] = [
                    'id' => sha1($c . $r),
                    'item_id' => $row->id,
                    'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row,
                    'tax_rate' => $tax_rate,
                    'units' => $units,
                    'options' => $options,
                ];
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function update_status($id)
    {
        $this->form_validation->set_rules('status', lang('status'), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note = $this->sma->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'sales');
        }

        if ($this->form_validation->run() == true && $this->purchases_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'sales');
        } else {
            $this->data['inv'] = $this->purchases_model->getPurchaseByID($id);
            $this->data['returned'] = false;
            if ($this->data['inv']->status == 'returned' || $this->data['inv']->return_id) {
                $this->data['returned'] = true;
            }
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'purchases/update_status', $this->data);
        }
    }

    public function view($purchase_id = null)
    {
        $this->sma->checkPermissions('index');

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchases_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
        $this->data['attachments'] = $this->site->getAttachments($purchase_id, 'purchase');

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_purchase_details'), 'bc' => $bc];
        $this->page_construct('purchases/view', $meta, $this->data);
    }

    public function view_return($id = null)
    {
        $this->sma->checkPermissions('return_purchases');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchases_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['barcode'] = "<img src='" . admin_url('products/gen_barcode/' . $inv->reference_no) . "' alt='" . $inv->reference_no . "' class='pull-left' />";
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['payments'] = $this->purchases_model->getPaymentsForPurchase($id);
        $this->data['user'] = $this->site->getUser($inv->created_by);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->purchases_model->getAllReturnItems($id);
        $this->data['purchase'] = $this->purchases_model->getPurchaseByID($inv->purchase_id);
        $this->load->view($this->theme . 'purchases/view_return', $this->data);
    }
    public function check_status()
    {
        $this->data['modal_js'] = $this->site->modal_js();

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('check_status'), 'page' => lang('Check Status')], ['link' => '#', 'page' => lang('Check Status')]];
        $meta = ['page_title' => lang('Check Status'), 'bc' => $bc];
        $this->page_construct('purchases/check_status', $meta, $this->data);
    }

    public function searchBySequenceCode()
    {
        $sequenceCode = $this->input->post('sequence_code');
        $purchase = $this->purchases_model->searchBySequenceCode($sequenceCode);
        if ($purchase == 420) {
            $this->data['purchase'] = 420;
        } else {
            $this->data['purchase'] = $purchase;
        }
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('check_status'), 'page' => lang('Check Status')], ['link' => '#', 'page' => lang('Check Status')]];
        $meta = ['page_title' => lang('Check Status'), 'bc' => $bc];
        $this->page_construct('purchases/check_status_list', $meta, $this->data);
    }

    public function searchByReference()
    {
        $referenceNo = $this->input->post('reference_no');
        $purchase = $this->purchases_model->searchByReference($referenceNo);
        if ($purchase == 420) {
            $this->data['purchase'] = 420;
        } else {
            $this->data['purchase'] = $purchase;
        }
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('check_status'), 'page' => lang('Check Status')], ['link' => '#', 'page' => lang('Check Status')]];
        $meta = ['page_title' => lang('Check Status'), 'bc' => $bc];
        $this->page_construct('purchases/check_status_list', $meta, $this->data);
    }

    public function searchByDate()
    {
        $start_date = $this->sma->fld(trim($this->input->post('start_date')));
        $end_date = $this->sma->fld(trim($this->input->post('end_date')));
        //     $start_date_time   = $start_date.' 00:00:00';
        //   echo  $end_date_time     = $end_date.' 23:59:59';    

        $purchase = $this->purchases_model->searchByDate($start_date, $end_date);
        $this->data['purchase'] = $purchase;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('check_status'), 'page' => lang('Check Status')], ['link' => '#', 'page' => lang('Check Status')]];
        $meta = ['page_title' => lang('Check Status'), 'bc' => $bc];
        $this->page_construct('purchases/check_status_list', $meta, $this->data);
    }
}
