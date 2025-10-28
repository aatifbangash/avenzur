<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_order extends MY_Controller
{
    public function __construct()
    {
        // error_reporting(E_ALL);        
        //ini_set('display_errors', 1); 
        parent::__construct();
      //print_r(!$this->loggedIn);exit;
        // if (!$this->loggedIn) {
        //     $this->session->set_userdata('requested_page', $this->uri->uri_string());
        //     $url = "admin/login";
        //     if ($this->input->server('QUERY_STRING')) {
        //         $url = $url . '?' . $this->input->server('QUERY_STRING') . '&redirect=' . $this->uri->uri_string();
        //     }

        //     $this->sma->md($url);
        // }
        if ($this->Customer) {
        
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->load->admin_model('cmt_model');
        $this->load->library('RASDCore', $params = null, 'rasd');
        $this->lang->admin_load('purchases', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('purchase_order_model');
        $this->load->admin_model('purchases_model');
         $this->load->admin_model('transfers_model');
        $this->load->admin_model('Inventory_model');
        $this->load->admin_model('deals_model');
        $this->load->admin_model('purchase_requisition_model');
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
        $this->load->library('pagination');
    }

    /* -------------------------------------------------------------------------------------------------------------------------------- */

    private function setValidationRule($product_id_arr)
    {
        if (!empty($product_id_arr)) {
           
        foreach ($product_id_arr as $index => $prid) {
            // Set validation rules for each quantity field 
            //$this->form_validation->set_rules('product', lang('order_items'), 'required');
            $this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required|is_natural_no_zero');
        $this->form_validation->set_rules('supplier', $this->lang->line('supplier'), 'required');
            $this->form_validation->set_rules(
                'quantity[' . $index . ']',
                'Quantity for Product ' . $_POST['product_name'][$index],  // Replace with actual product identifier
                'required|greater_than[0]',
                array(
                    'required' => 'Quantity for Product <b>' . $_POST['product_name'][$index] . '</b> is required.',
                    'greater_than' => 'Quantity for Product <b>' . $_POST['product_name'][$index] . '</b> must be greater than zero.'
                )
            );

             $this->form_validation->set_rules(
                'expiry[' . $index . ']',
                'Expiry Date for Product ' . $_POST['product_name'][$index],  // Replace with actual product identifier
                'required',
                array(
                    'required' => 'Expiry Date for Product <b>' . $_POST['product_name'][$index] . '</b> is required.',
                )
            );
        }
        }
    }

    private function preparePurchaseItems()
    {
        $products = [];
        $total_items = sizeof($_POST['product']);
        $warehouse_id = $this->input->post('warehouse');
        //$status = $this->input->post('status');
        $status = $this->input->post('status') ?? "pending";
        for ($r = 0; $r < $total_items; $r++) {

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
                $discount3 = $_POST['dis3'][$r];
                $item_third_discount = $_POST['item_third_discount'][$r];

                //$net_cost_obj = $this->purchases_model->getAverageCost($item_batchno, $item_code);
                //$net_cost_sales = $net_cost_obj[0]->cost_price;

                if (isset($item_code) && isset($real_unit_cost) && isset($unit_cost) && isset($item_quantity)) {

                    /**
                     * NEED TO DISCUSS
                     */
                    $product_details = $this->purchase_order_model->getProductByCode($item_code);
                    if ($product_details->price != $item_sale_price) {
                        // update product sale price
                        $this->purchase_order_model->updateProductSalePrice($item_code, $item_sale_price, $item_tax_rate);
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
                    $item_net_cost = ($main_net / ($item_quantity + $item_bonus));
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
                        'bonus' => $item_bonus,
                        //'bonus' => 0,
                        'discount1' => $item_dis1,
                        'discount2' => $item_dis2,
                        'second_discount_value' => $new_item_second_discount,
                        'totalbeforevat' => $_POST['item_net_purchase'][$r],
                        'main_net' => $main_net,
                        'discount3' => $discount3,
                        'third_discount_value' => $item_third_discount,
                    ];

                    if ($avz_item_code) {
                        $product['avz_item_code'] = $avz_item_code;
                    }

                    if ($unit->id != $product_details->unit) {
                        $product['base_unit_cost'] = $this->site->convertToBase($unit, $real_unit_cost);
                    } else {
                        $product['base_unit_cost'] = $real_unit_cost;
                    }

                    $products[] = $product ;
                    $total_sale_price += $this->sma->formatDecimal($item_sale_price, 4);
                    $total += $this->sma->formatDecimal($main_net, 4);//$this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                }
            }
        return $products;
    }

    public function add($quote_id = null)
    {
       
        //$this->sma->checkPermissions();
        
        // $this->form_validation->set_rules('batchno[]', lang('Batch'), 'required');
        $product_id_arr = $this->input->post('product_id');

        $this->setValidationRule($product_id_arr);
        

        $this->session->unset_userdata('csrf_token');
        if ($this->form_validation->run() == true) {
            //echo "<pre>";
            //print_r($this->input->post());exit;
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('po');
            if ($this->Owner || $this->Admin) {
                $date = $this->sma->fld(trim($this->input->post('date')));
            } else {
                $date = date('Y-m-d H:i:s');
            }
            $warehouse_id = $this->input->post('warehouse');
            $child_supplier_id = $this->input->post('childsupplier') ? $this->input->post('childsupplier') : 0;
            $supplier_id = $child_supplier_id ? $child_supplier_id : $this->input->post('supplier');
            $status = $this->input->post('status') ?? "pending";
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
            //$_POST['warehouse_id'] = $warehouse_id;
            $products = $this->preparePurchaseItems();
            
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
            $grand_deal_discount = $this->input->post('grand_deal_discount');

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
                'sequence_code' => $this->sequenceCode->generate('PR', 5),
                'grand_deal_discount' => $grand_deal_discount
            ];

            if ($supplier_details->balance > 0 && $status == 'received') {
                if ($supplier_details->balance >= $grand_total) {
                    $paid = $grand_total;
                    $new_balance = $supplier_details->balance - $grand_total;
                    $payment_status = 'paid';
                } else {
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

            if ($_FILES['attachment']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('attachment')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            //$attachments = $this->attachments->upload();
            //$data['attachment'] = !empty($attachments);
            //$this->sma->print_arrays($data, $products);exit;
    
     //echo "<pre>";print_r($data);print_r($products);exit;
        if ($this->form_validation->run() == true && $purchase_id = $this->purchase_order_model->addPurchase($data, $products, $attachments)) {
            

            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('purchase_added'));


              $pr_id = $this->input->post('pr_id');
              if( $pr_id != '' ) {
                // update pr status to closed
                $this->purchase_requisition_model->update_status( $pr_id, 'closed' );
                $this->db->update('purchase_requisitions', ['status' => 'po-created', 
                                                            'updated_by' => $this->session->userdata('user_id'),
                                                            'updated_at' => date('Y-m-d H:i:s')],
                                                            ['id' => $pr_id]);
              }
            admin_redirect('purchase_order?lastInsertedId=' . $purchase_id);
            // check for action against pr

            //if( $this->input->post->('action') == 'create_po' && $this->input->post('pr_id') ) {
              
                // log activity
            //     $audit_log = array(
            //     'pr_id' => $pr_id,
            //     'action' => 'PO Created',
            //     'details' => 'PO created by '.$this->session->userdata('username').' with PO ID: '.$purchase_id,
            //     'done_by' => $this->session->userdata('user_id'),
            //     'created_at' => date('Y-m-d H:i:s')
            // );

            // $this->db->insert('pr_audit_logs', $audit_log);
                                                        
            

        } else if($this->input->get('action') == 'create_po') {


                $pr_info = $this->getPurchaseRequesitionItems($this->input->get('pr_id'));

                // echo "<pre>";print_r($pr_info);exit;
                $this->data['purchase_requesition_items'] = json_encode($pr_info);
              
                //$this->data['pr_data'] = $pr_data;
                $this->data['pr_id'] = $pr_id;
                $this->data['action'] = $this->input->get('action') ;
                $this->data['module_name'] = 'purchase_order';


            }

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            // $this->data['quote_id'] = $quote_id;
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
            $this->page_construct('purchase_order/add', $meta, $this->data);
    }
    

    public function getPurchaseRequesitionItems($pr_id = null) {
        // $pr_id = base64_decode( $this->input->get('id') );

                // Check if PR exists
                $pr_data = $this->purchase_requisition_model->get_requisition($pr_id);
                // echo "<pre>";print_r($pr_id);exit;
                if (!$pr_data) {
                    $this->session->set_flashdata('error', 'Purchase Requisition not found.');
                    admin_redirect('purchase_requisition'); // redirect back
                }
               // echo "<pre>";print_r($pr_data);exit;
              $pr_info = [];
               $c = 1;
                foreach($pr_data->items as $row) {
                    $row->qty = $row->quantity;
                    $row->name = $row->product_name;
                    $row->code = $row->product_code;
                    $row->cost = $row->cost ? $row->cost : 3.0;
                    $row->sale_price = $row->price ? $row->price : 4.0;
                    $row->bonus = 0;
                    $row->dis1 = 9;
                    $row->dis2 = 33;

                    // append missing keys
    $row->alert_quantity = 0.00;
    $row->image = "no_image.png";
    $row->category_id = "";
    $row->subcategory_id = null;
    $row->cf1 = "";
    $row->cf2 = "";
    $row->cf3 = "";
    $row->cf4 = "";
    $row->cf5 = "";
    $row->cf6 = "";
    $row->track_quantity = 1;
    $row->warehouse = null;
    $row->barcode_symbology = "code128";
    $row->tax_method = 1;
    $row->type = "standard";
    $row->supplier1 = 0;
    $row->supplier2 = null;
    $row->supplier3 = null;
    $row->supplier4 = null;
    $row->supplier5 = null;
    $row->promotion = null;
    $row->promo_price = 0.00;
    $row->start_date = null;
    $row->end_date = null;
    $row->sale_unit = "";
    $row->purchase_unit = "";
    $row->brand = "";
    $row->slug = "";
    $row->featured = null;
    $row->special_offer = null;
    $row->weight = 0.00;
    $row->hsn_code = null;
    $row->views = 0;
    $row->hide = 0;
    $row->second_name = "";
    $row->hide_pos = 0;
    $row->trade_name = "";
    $row->manufacture_name = "";
    $row->main_agent = "";
    $row->purchase_account = 0;
    $row->sale_account = 0;
    $row->inventory_account = 0;
    $row->incentive_value = null;
    $row->incentive_qty = null;
    $row->sequence_code = "";
    $row->draft = null;
    $row->google_merch = null;
    $row->imported = 0;
    $row->ascon_code = null;
    $row->special_product = null;
    $row->item_code = $row->product_code; // logical mapping
    $row->item_tax_method = 1;
    $row->option = false;
    $row->supplier_part_no = "";
    $row->serial_no = "";
    $row->real_unit_cost = 0.00;
    $row->base_quantity = 1;
    $row->base_unit = "";
    $row->base_unit_cost = 0.00;
    $row->new_entry = 1;
    $row->expiry = "";
    $row->quantity_balance = "";
    $row->discount = 0;
    $row->batchno = "";
    $row->avz_item_code = "";
    $row->serial_number = "";
    $row->warehouse_shelf = "";
    $row->get_supplier_discount = 0;
    $units = $this->site->getUnitsByBUID(6);
    $options = $this->purchases_model->getProductOptions($row->product_id);

                    $units = $this->site->getUnitsByBUID($row->base_unit);
                    $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                      $pr_info[$c] = [
                        'id' => $c,
                        'item_id' => $row->product_id,
                        'label' => $row->product_name . ' (' . $row->product_code . ')',
                        'row' => $row,
                        'tax_rate' => $tax_rate,
                        'units' => $units,
                        'options' => $options,
                    ];
                    $c++;
                }
    return $pr_info;            
    }

   


    /* ------------------------------------------------------------------------------------- */

    public function edit($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $purchase_transferred = 0;

        $inv = $this->purchase_order_model->getPurchaseByID($id);
        $pur_inv_items = $this->purchase_order_model->getAllPurchaseItems($id);

       
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
                $discount3 = $_POST['dis3'][$r];
                $item_third_discount = $_POST['item_third_discount'][$r];

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
                        $pr_item_tax = $this->sma->formatDecimal(($main_net * ($tax_details->rate / 100)), 2);
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
                    $item_net_cost = ($main_net / ($item_quantity + $item_bonus));
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
                        'item_discount' => $new_item_first_discount,
                        'subtotal' => $new_subtotal,
                        'expiry' => $item_expiry,
                        'real_unit_cost' => $new_real_unit_cost,
                        'sale_price' => $item_sale_price,
                        'supplier_part_no' => $supplier_part_no,
                        'date' => date('Y-m-d', strtotime($date)),
                        'subtotal2' => $this->sma->formatDecimal($subtotal2),
                        'batchno' => $item_batchno,
                        'serial_number' => $item_serial_no ? $item_serial_no : 'Default',
                        'bonus' => $item_bonus,
                        //'bonus' => 0,
                        'discount1' => $item_dis1,
                        'discount2' => $item_dis2,
                        'second_discount_value' => $new_item_second_discount,
                        'totalbeforevat' => $_POST['item_net_purchase'][$r],
                        'main_net' => $main_net,
                        'warehouse_shelf' => ($warehouse_shelf ? $warehouse_shelf : ''),
                        'discount3' => $discount3,
                        'third_discount_value' => $item_third_discount,
                    ];

                    if ($avz_item_code) {
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
            $grand_deal_discount = $this->input->post('grand_deal_discount');

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
                'validate' => $validate,
                'grand_deal_discount' => $grand_deal_discount


            ];

            if ($supplier_details->balance > 0 && $status == 'received') {
                if ($supplier_details->balance >= $grand_total) {
                    $paid = $grand_total;
                    $new_balance = $supplier_details->balance - $grand_total;
                    $payment_status = 'paid';
                } else {
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

            if ($_FILES['attachment']['size'] > 0) { 
                $this->load->library('upload'); 
                $config['upload_path'] = $this->digital_upload_path; 
                $config['allowed_types'] = $this->digital_file_types; 
                $config['max_size'] = $this->allowed_file_size; 
                $config['overwrite'] = false; 
                $config['encrypt_name'] = true; $this->upload->initialize($config); 
                if (!$this->upload->do_upload('attachment')) { 
                    $error = $this->upload->display_errors(); 
                    $this->session->set_flashdata('error', $error); 
                    redirect($_SERVER['HTTP_REFERER']); 
                } 
                $photo = $this->upload->file_name; 
                $data['attachment'] = $photo; 
            }

            //$attachments = $this->attachments->upload();
            //$data['attachment'] = !empty($attachments);
            //$this->sma->print_arrays($data, $products);exit;

            // echo "<pre>";
            // print_r($this->input->post());
            // print_r($data);
            // print_r($products);
            // exit;
        }


        if ($this->form_validation->run() == true && $this->purchase_order_model->updatePurchase($id, $data, $products, $attachments)) {

            $this->session->set_userdata('remove_pols', 1);
            $this->session->set_flashdata('message', $this->lang->line('purchase_added'));

            admin_redirect('purchase_order');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['inv'] = $inv;
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang('purchase_x_edited_older_than_x_days'), $this->Settings->disable_editing));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $inv_items = $this->purchase_order_model->getAllPurchaseItems($id);
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
                $options = $this->purchase_order_model->getProductOptions($row->id);
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
                $row->three_month_sale = $this->purchase_order_model->getThreeMonthSale($item->product_id, $start_date, $end_date);
                $row->warehouse_shelf = $item->warehouse_shelf;
                unset($row->details, $row->product_details, $row->price, $row->file, $row->product_group_id);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;
                $row->dis3 = $item->discount3;
                $row->item_third_discount = $item->third_discount_value;

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
            $this->data['purchase'] = $this->purchase_order_model->getPurchaseByID($id);
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
            $this->page_construct('purchase_order/edit', $meta, $this->data);
        }
    }

    
  
    /* ------------------------------------------------------------------------- */

    public function index($warehouse_id = null)
    {
        //error_reporting(E_ALL);        // Report all errorsP
        //ini_set('display_errors', 1); 
        
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

        //update index func

        $filters = [
            'supplier_id' => $this->input->get('supplier_id'),
            'warehouse_id' => $this->input->get('warehouse_id'),
            'status' => $this->input->get('status'),
            'from_date' => $this->input->get('from'),
            'to_date' => $this->input->get('to'),
            'pid' => $this->input->get('pid'),
            'lastInsertedId' => $this->input->get('lastInsertedId')
        ];

        $limit = $this->input->get('limit') ?? 100;
        $page = $this->input->get('page') ?? 1;
        $offset = ($page - 1) * $limit;

        $total_rows = $this->purchase_order_model->count_purchases($filters);
        

        $config['base_url'] = admin_url('purchases/index');
        $config['total_rows'] = $total_rows;
        $config['per_page'] = $limit;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        $config['reuse_query_string'] = TRUE;
        $config['use_page_numbers']    = TRUE;   

        // Styling (Bootstrap 4/5)
        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';
        $config['num_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['num_tag_close'] = '</span></li>';
        $config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
        $config['cur_tag_close'] = '</span></li>';
        $config['next_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['next_tag_close'] = '</span></li>';
        $config['prev_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['prev_tag_close'] = '</span></li>';
        $config['first_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['first_tag_close'] = '</span></li>';
        $config['last_tag_open'] = '<li class="page-item"><span class="page-link">';
        $config['last_tag_close'] = '</span></li>';

        $this->pagination->initialize($config);
        $this->data['pagination'] = $this->pagination->create_links();
        $this->data['purchases'] = $this->purchase_order_model->get_purchases($filters, $limit, $offset);

        //end update index func

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('purchases')]];
        $meta = ['page_title' => lang('purchases'), 'bc' => $bc];
        $this->page_construct('purchase_order/index', $meta, $this->data);
    }



    /* ----------------------------------------------------------------------------- */

    public function modal_view($purchase_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $purchase_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->purchase_order_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['rows'] = $this->purchase_order_model->getAllPurchaseItems($purchase_id);
        $supplier = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['parent_supplier'] = '';
        if ($supplier->level == 2 && $supplier->parent_code != '') {
            $parentSupplier = $this->site->getCompanyByParentCode($supplier->parent_code);
            if (isset($parentSupplier->name)) {
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

        $this->load->view($this->theme . 'purchase_order/modal_view', $this->data);
    }

 
    /* -------------------------------------------------------------------------------- */

   
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
        //echo $html;exit;
        if ($view) {
            echo $html;
            die();
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        }
        $this->sma->generate_pdf($html, $name);
    }

    /* -------------------------------------------------------------------------------- */

   

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
                $this->Inventory_model->get_current_stock($item->product_id, 'null', $item->batchno);
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
        $inv = $this->purchase_order_model->getPurchaseByID($purchase_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->data['rows'] = $this->purchase_order_model->getAllPurchaseItems($purchase_id);
        $this->data['supplier'] = $this->site->getCompanyByID($inv->supplier_id);
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['payments'] = $this->purchase_order_model->getPaymentsForPurchase($purchase_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['return_purchase'] = $inv->return_id ? $this->purchases_model->getPurchaseByID($inv->return_id) : null;
        $this->data['return_rows'] = $inv->return_id ? $this->purchases_model->getAllPurchaseItems($inv->return_id) : null;
        $this->data['attachments'] = $this->site->getAttachments($purchase_id, 'purchase');

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('view')]];
        $meta = ['page_title' => lang('view_purchase_details'), 'bc' => $bc];
        $this->page_construct('purchase_order/view', $meta, $this->data);
    }

    public function send_to_supplier()
    {
        $purchase_id = $this->input->post('purchase_order_id');
        $notes = $this->input->post('notes');

        $data = [
            'status' => 'sent_to_supplier',
            'sending_notes' => $notes,
            'updated_by' => $this->session->userdata('user_id'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $purchase_id)->update('purchase_orders', $data);

        echo json_encode(['success' => true]);
    }


    public function add_grn($po_id) {

     if( $this->input->post() ) {
        $items = $this->input->post('items', true);

         if (empty($items)) {
            show_error('No items to update.');
        }

            // Initialize CASE expressions for each field
        $quantityCase       = "CASE id";
        $actualQtyCase      = "CASE id";
        $commentCase        = "CASE id";
        $ids = [];
        
        foreach ($items as $item) {
            if ($item['quantity'] <= 0 || $item['quantity'] > $item['actual_quantity']) {
                //show_error("Invalid quantity for item ID: " . $item['item_id']);
            }

            $id = (int) $item['item_id'];
            $quantity = (float) $item['quantity'];
            $actualQty = (float) $item['actual_quantity'];
            $comment = $this->db->escape($item['remarks']); // safe escape for string

            // Optional: Validation to avoid invalid entries
            if ($quantity <= 0 || $quantity > $actualQty) {
                continue; // skip invalid entries
            }

            $quantityCase  .= " WHEN {$id} THEN {$quantity}";
            $actualQtyCase .= " WHEN {$id} THEN {$actualQty}";
            $commentCase   .= " WHEN {$id} THEN {$comment}";

            $ids[] = $id;


        }

         if (empty($ids)) {
            show_error('No valid items to update.');
        }

         // Close CASE statements
        $quantityCase  .= " END";
        $actualQtyCase .= " END";
        $commentCase   .= " END";

        $idList = implode(',', $ids);

           $sql = "
        UPDATE sma_purchase_order_items
        SET 
            quantity = {$quantityCase},
            actual_quantity = {$actualQtyCase},
            grn_comments = {$commentCase}
        WHERE id IN ({$idList})
        ";

        // Execute query
        $this->db->query($sql);
        
        // Update purchase order status
        $this->db->where('id', $po_id)->update('purchase_orders', [
            'status' => 'goods_received',
            'total_items_received' => sizeof($items),
            'grn_notes' => $this->input->post('remarks'),
            'received_by' => $this->session->userdata('user_id'),
            'updated_by' => $this->session->userdata('user_id'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);


        // Redirect or show success message
        $this->session->set_flashdata('message', 'Purchase items updated successfully!');
        admin_redirect('purchase_order/view/' . $po_id);

     }   
     
    $this->data['rows'] = $this->purchase_order_model->getAllPurchaseItems($po_id);
    $this->data['po_id'] = $po_id;

    $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('purchases'), 'page' => lang('purchases')], ['link' => '#', 'page' => lang('add_purchase')]];
    $meta = ['page_title' => lang('add_purchase'), 'bc' => $bc];
    $this->page_construct('purchase_order/grn', $meta, $this->data);

    // $purchase_order_id = $this->input->post('purchase_order_id');
    // $received_date = $this->input->post('received_date');
    // $received_by = $this->session->userdata('user_id'); ;
    // $grn_notes = $this->input->post('grn_notes');
    // $received_qty = $this->input->post('received_qty'); // array
    // $total_items = $this->input->post('total_items'); // array
    // $total_quantity = $this->input->post('total_quantity'); // array

    // // Handle file upload
    // $attachment_path = null;
    // if (!empty($_FILES['grn_attachment']['name'])) {
    //     $config['upload_path'] = './assets/uploads/grn/';
    //     $config['allowed_types'] = 'pdf|jpg|png|jpeg';
    //     $config['max_size'] = 2048;
    //     $config['encrypt_name'] = TRUE;

    //     $this->load->library('upload', $config);

    //     if ($this->upload->do_upload('grn_attachment')) {
    //         $data = $this->upload->data();
    //         $attachment_path = 'uploads/grn/' . $data['file_name'];
    //     } else {
    //         echo $this->upload->display_errors();
    //         return;
    //     }
    // }

    // // Insert GRN record
    // $grn_data = [
    //     'purchase_order_id' => $purchase_order_id,
    //     'received_date' => $received_date,
    //     'received_by' => $received_by,
    //     'notes' => $grn_notes,
    //     'attachment' => $attachment_path,
    //     'total_items' => $total_items,
    //     'total_quantity' => $total_quantity,
    //     'created_at' => date('Y-m-d H:i:s')
    // ];
    // $this->db->insert('purchase_order_grn', $grn_data);
    // $grn_id = $this->db->insert_id();

    // // Update purchase order status
    // $this->db->where('id', $purchase_order_id)->update('purchase_orders', [
    //     'status' => 'goods_received',
    //     'grn_id' => $grn_id,
    //     'updated_by' => $this->session->userdata('user_id'),
    //     'updated_at' => date('Y-m-d H:i:s')
    // ]);

    // echo json_encode(['success' => true]);
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
}
