<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Returns_supplier extends MY_Controller
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
            $this->sma->md('login');
        }
        if ($this->Supplier || $this->Customer) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('returns', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('returns_supplier_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '1024';
        $this->data['logo'] = true;
    }

    public function add_return()
    {
        $this->sma->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang('customer'), 'required');
        $this->form_validation->set_rules('biller', lang('biller'), 'required');

        if ($this->form_validation->run() == true) {
            $date = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : date('Y-m-d H:i:s');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $biller_id = $this->input->post('biller');
            $total_items = $this->input->post('total_items');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = !empty($customer_details->company) && $customer_details->company != '-' ? $customer_details->company : $customer_details->name;
            $biller_details = $this->site->getCompanyByID($biller_id);
            $biller = !empty($biller_details->company) && $biller_details->company != '-' ? $biller_details->company : $biller_details->name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;


            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial = $_POST['serial'][$r] ?? '';
                $item_tax_rate = $_POST['product_tax'][$r] ?? null;
                $item_discount = $_POST['product_discount'][$r] ?? null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;
                    $pr_item_tax = $item_tax = 0;
                    $tax = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax = $this->sma->formatDecimal($ctax['amount']);
                        $tax = $ctax['tax'];
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
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $pr_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'serial_no' => $item_serial,
                        'real_unit_price' => $real_unit_price,
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
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);
            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);
            $data = [
                'date' => $date,
                'reference_no' => $reference,
                'customer_id' => $customer_id,
                'customer' => $customer,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'paid' => 0,
                'created_by' => $this->session->userdata('user_id'),
                'hash' => hash('sha256', microtime() . mt_rand()),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->returns_supplier_model->addReturn($data, $products)) {

            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang('return_added'));
            admin_redirect('returns_supplier');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['units'] = $this->site->getAllBaseUnits();

            // $user         = $this->site->getUser();
            // $group_id = $user->group_id;

            $this->db->select('id,name')->from('companies')->where('group_name', 'supplier');
            $this->data['suppliers'] = $this->db->get()->result();

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('returns_supplier'), 'page' => lang('returns')], ['link' => '#', 'page' => lang('add_return')]];
            $meta = ['page_title' => lang('add_return'), 'bc' => $bc];
            $this->page_construct('returns_supplier/add_supplier', $meta, $this->data);
        }
    }

    public function add()
    {
        $this->sma->checkPermissions();
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('supplier', lang('supplier'), 'required');
//        $this->form_validation->set_rules('biller', lang('biller'), 'required');

        if ($this->form_validation->run() == true) {

            $date = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : date('Y-m-d H:i:s');
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('re');
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $biller_id = null;
            $total_items = $this->input->post('total_items');
            $supplier_details = $this->site->getCompanyByID($supplier_id);


            $supplier = !empty($supplier_details->company) && $supplier_details->company != '-'
                ? $supplier_details->company
                : $supplier_details->name;

            $biller_details = null;
            $biller = null;

            $note = $this->sma->clear_tags($this->input->post('note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));
            $shipping = $this->input->post('shipping') ? $this->input->post('shipping') : 0;

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $total_product_discount = 0;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $item_net_cost = $this->sma->formatDecimal($_POST['net_cost'][$r]);
                $item_net_price = $this->sma->formatDecimal($_POST['net_price'][$r]);
                $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_batchno = $_POST['batch_no'][$r];
                $item_serial_no = $_POST['serial_no'][$r];
                //$item_expiry        = $_POST['expiry'][$r];
                $item_expiry = (isset($_POST['expiry'][$r]) && !empty($_POST['expiry'][$r])) ? $this->sma->fsd($_POST['expiry'][$r]) : null;
                //$item_bonus         = $_POST['bonus'][$r];
                $item_dis1 = $_POST['dis1'][$r];
                $item_dis2 = $_POST['dis2'][$r];
                $item_serial = $_POST['serial'][$r] ?? '';
                $item_tax_rate = $_POST['product_tax'][$r] ?? null;
                $item_discount = $_POST['product_discount'][$r] ?? null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];

                $totalbeforevat = $_POST['totalbeforevat'][$r];

                $net_cost_obj = $this->returns_supplier_model->getAverageCost($item_batchno, $item_code);
                $net_cost = $net_cost_obj[0]->cost_price;

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    //$item_net_price   = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;

                    //Discount calculation---------------------------------- 
                    //The above will be deleted later becasue order discount is not in use                  
                    $product_discount1 = $this->site->calculateDiscount($item_dis1 . '%', $unit_price);
                    $amount_after_discount1 = $unit_price - $product_discount1;
                    $product_discount2 = $this->site->calculateDiscount($item_dis2 . '%', $amount_after_discount1);


                    $product_item_discount1 = $this->sma->formatDecimal($product_discount1 * $item_unit_quantity);
                    $product_item_discount2 = $this->sma->formatDecimal($product_discount2 * $item_unit_quantity);

                    $product_item_discount = ($product_item_discount1 + $product_item_discount2);
                    $total_product_discount += $product_item_discount;
                    //Discount calculation----------------------------------

                    $pr_item_tax = $item_tax = 0;
                    $tax = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax = $this->sma->formatDecimal($ctax['amount']);
                        $tax = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                            //$item_net_price = $item_net_price - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                        if ($this->Settings->indian_gst && $gst_data = $this->gst->calculateIndianGST($pr_item_tax, ($biller_details->state == $supplier_details->state), $tax_details)) {
                            $total_cgst += $gst_data['cgst'];
                            $total_sgst += $gst_data['sgst'];
                            $total_igst += $gst_data['igst'];
                        }
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax - $product_item_discount);
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = [
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_cost' => $net_cost,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $product_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'serial_no' => $item_serial,
                        'expiry' => $item_expiry,
                        'batch_no' => $item_batchno,
                        'serial_number' => $item_serial_no,
                        'real_unit_price' => $real_unit_price,
                        //'bonus'             => $item_bonus,
                        'bonus' => 0,
                        'discount1' => $item_dis1,
                        'discount2' => $item_dis2
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
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);

            //Discount calculation
            // total discount must be deducted from  grandtotal
            //$grand_total    = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($order_discount)), 4);

            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $this->sma->formatDecimal($shipping) - $this->sma->formatDecimal($total_product_discount)), 4);
            //Discount calculation

            $data = [
                'date' => $date,
                'reference_no' => $reference,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'biller_id' => $biller_id,
                'biller' => $biller,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $total_product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_product_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'total_items' => $total_items,
                'paid' => 0,
                'created_by' => $this->session->userdata('user_id'),
                'hash' => hash('sha256', microtime() . mt_rand()),
            ];

            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            //$this->sma->print_arrays($data, $products);exit;

        }

        if ($this->form_validation->run() == true && $return_insert_id = $this->returns_supplier_model->addReturn($data, $products)) {

            //$this->returns_supplier_model->convert_return_invoice($return_insert_id, $products);
            $this->convert_return_invoice($return_insert_id);

            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang('return_added'));
            admin_redirect('returns_supplier');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
//            $this->data['billers']    = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['units'] = $this->site->getAllBaseUnits();
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('returns_supplier'), 'page' => lang('returns')], ['link' => '#', 'page' => lang('add_supplier_return')]];
            $meta = ['page_title' => lang('add_supplier_return'), 'bc' => $bc];
            $this->page_construct('returns_supplier/add', $meta, $this->data);
        }
    }

    public function convert_return_invoice($rid)
    {
        $inv = $this->returns_supplier_model->getReturnByID($rid);

        $this->load->admin_model('companies_model');
        $supplier = $this->companies_model->getCompanyByID($inv->supplier_id);
        $warehouse_id = $inv->warehouse_id;
        $warehouse_ledgers = $this->site->getWarehouseByID($warehouse_id);


        /*Accounts Entries*/
        $entry = array(
            'entrytype_id' => 4,
            'transaction_type' => 'returnorder',
            'number' => 'RSO-' . $inv->reference_no,
            'date' => date('Y-m-d'),
            'dr_total' => $inv->grand_total,
            'cr_total' => $inv->grand_total,
            'notes' => 'Return Reference: ' . $inv->reference_no . ' Date: ' . date('Y-m-d H:i:s'),
            'rsid' => $inv->id
        );
        $add = $this->db->insert('sma_accounts_entries', $entry);
        $insert_id = $this->db->insert_id();

        $entryitemdata = array();

        $inv_items = $this->returns_supplier_model->getReturnItems($rid);

        foreach ($inv_items as $item) {
            $proid = $item->product_id;
            $product = $this->site->getProductByID($proid);
            //products
            $entryitemdata[] = array(
                'Entryitem' => array(
                    'entry_id' => $insert_id,
                    'dc' => 'C',
                    'ledger_id' => $warehouse_ledgers->inventory_ledger,
                    'amount' => ($item->net_unit_price * $item->quantity),
                    'narration' => 'Inventory'
                )
            );
        }

        //vat on purchase
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'C',
                'ledger_id' => $this->vat_on_purchase,
                'amount' => ($inv->product_tax),
                'narration' => 'Vat on Purchase'
            )
        );

        //supplier
        $entryitemdata[] = array(
            'Entryitem' => array(
                'entry_id' => $insert_id,
                'dc' => 'D',
                'ledger_id' => $supplier->ledger_account,
                'amount' => ($inv->grand_total),
                'narration' => 'Accounts payable'
            )
        );

        foreach ($entryitemdata as $row => $itemdata) {
            $this->db->insert('sma_accounts_entryitems', $itemdata['Entryitem']);
        }


//        /*Accounts Entries*/
//        $entry = array(
//            'entrytype_id' => 4,
//            'number' => 'RCO-' . $inv->reference_no,
//            'date' => date('Y-m-d'),
//            'dr_total' => $inv->grand_total,
//            'cr_total' => $inv->grand_total,
//            'notes' => 'RCO Reference: ' . $inv->reference_no . ' Date: ' . date('Y-m-d H:i:s'),
//            'rid' => $inv->id,
//            'transaction_type' => 'returncustomerorder'
//        );
//
//        $add = $this->db->insert('sma_accounts_entries', $entry);
//        $insert_id = $this->db->insert_id();
//
//        //$insert_id = 999;
//        $entryitemdata = array();
//
//        $inv_items = $this->returns_supplier_model->getReturnItems($rid);
//
//        $totalSalePrice = 0;
//        $totalPurchasePrice = 0;
//        foreach ($inv_items as $item) {
//            $proid = $item->product_id;
//            $product = $this->site->getProductByID($proid);
//
//            $totalSalePrice = ($totalSalePrice) + ($item->net_unit_price * $item->quantity);
//            $totalPurchasePrice = $totalPurchasePrice + ($item->net_cost * $item->quantity);
//        }
//
//        $amount_to_pay = $totalSalePrice + $inv->total_tax - $inv->total_discount;
//
//        // //cash
//        $entryitemdata[] = array(
//            'Entryitem' => array(
//                'entry_id' => $insert_id,
//                'dc' => 'C',
//                'ledger_id' => $customer->ledger_account,
//                //'amount' =>(($totalSalePrice + $inv->order_tax) - $inv->total_discount),
//                'amount' => $amount_to_pay,
//                'narration' => 'customer'
//            )
//        );
//
//        // cost of goods sold
//        $entryitemdata[] = array(
//            'Entryitem' => array(
//                'entry_id' => $insert_id,
//                'dc' => 'C',
//                'ledger_id' => $customer->cogs_ledger,
//                'amount' => $totalPurchasePrice,
//                'narration' => 'cost of goods sold'
//            )
//        );
//
//        // inventory
//        $entryitemdata[] = array(
//            'Entryitem' => array(
//                'entry_id' => $insert_id,
//                'dc' => 'D',
//                'ledger_id' => $warehouse_ledgers->inventory_ledger,
//                'amount' => $totalPurchasePrice,
//                'narration' => 'inventory'
//            )
//        );
//
//        // // sale account
//        $entryitemdata[] = array(
//            'Entryitem' => array(
//                'entry_id' => $insert_id,
//                'dc' => 'D',
//                'ledger_id' => $customer->sales_ledger,
//                'amount' => $totalSalePrice,
//                'narration' => 'sale account'
//            )
//        );
//
//
//        // //discount
//        $entryitemdata[] = array(
//            'Entryitem' => array(
//                'entry_id' => $insert_id,
//                'dc' => 'C',
//                'ledger_id' => $customer->discount_ledger,
//                'amount' => $inv->total_discount,
//                'narration' => 'discount'
//            )
//        );
//
//        // //vat on sale
//        $entryitemdata[] = array(
//            'Entryitem' => array(
//                'entry_id' => $insert_id,
//                'dc' => 'D',
//                'ledger_id' => $this->vat_on_sale,
//                'amount' => $inv->total_tax,
//                'narration' => 'vat on sale'
//            )
//        );
//
//        $total_invoice_entry = $inv->total_tax + $totalSalePrice + $totalPurchasePrice;
//
//
//        $this->db->update('sma_accounts_entries', ['dr_total' => $total_invoice_entry, 'cr_total' => $total_invoice_entry], ['id' => $insert_id]);
//
//        //   /*Accounts Entry Items*/
//        foreach ($entryitemdata as $row => $itemdata) {
//            $this->db->insert('sma_accounts_entryitems', $itemdata['Entryitem']);
//        }

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

        if ($this->returns_supplier_model->deleteReturn($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 0, 'msg' => lang('return_deleted')]);
            }
            $this->session->set_flashdata('message', lang('return_deleted'));
            admin_redirect('welcome');
        }
    }

    public function delete_previous_entry($id)
    {
        $accouting_entry = $this->returns_supplier_model->getAccoutsEntryByID($id);

        $this->db->delete('sma_accounts_entryitems', ['entry_id' => $accouting_entry->id]);
        $this->db->delete('sma_accounts_entries', ['rid' => $id]);
    }

    public function edit($id = null)
    {
        $this->sma->checkPermissions();
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->returns_supplier_model->getReturnByID($id);
        if (!$this->session->userdata('edit_right')) {
            $this->sma->view_rights($inv->created_by);
        }
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('supplier', lang('supplier'), 'required');


        if ($this->form_validation->run() == true) {
            $date = ($this->Owner || $this->Admin) ? $this->sma->fld(trim($this->input->post('date'))) : $inv->date;
            $reference = $this->input->post('reference_no');
            $warehouse_id = $this->input->post('warehouse');
            $supplier_id = $this->input->post('supplier');
            $biller_id = null;
            $total_items = $this->input->post('total_items');
            $supplier_details = $this->site->getCompanyByID($supplier_id);
            $supplier = !empty($supplier_details->company) && $supplier_details->company != '-' ? $supplier_details->company : $supplier_details->name;
            $biller_details = null;
            $biller = null;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $staff_note = $this->sma->clear_tags($this->input->post('staff_note'));
            $shipping = $this->input->post('shipping');

            $total = 0;
            $product_tax = 0;
            $product_discount = 0;
            $total_product_discount = 0;
            $gst_data = [];
            $total_cgst = $total_sgst = $total_igst = 0;
            $i = isset($_POST['product_code']) ? sizeof($_POST['product_code']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $item_id = $_POST['product_id'][$r];
                $item_type = $_POST['product_type'][$r];
                $item_code = $_POST['product_code'][$r];
                $item_name = $_POST['product_name'][$r];
                $item_option = isset($_POST['product_option'][$r]) && $_POST['product_option'][$r] != 'false' && $_POST['product_option'][$r] != 'null' ? $_POST['product_option'][$r] : null;
                $real_unit_price = $this->sma->formatDecimal($_POST['real_unit_price'][$r]);
                $unit_price = $this->sma->formatDecimal($_POST['unit_price'][$r]);
                $item_cost_price = $_POST['net_cost'][$r];
                $item_unit_quantity = $_POST['quantity'][$r];
                $item_serial = $_POST['serial'][$r] ?? '';
                $item_tax_rate = $_POST['product_tax'][$r] ?? null;
                $item_discount = $_POST['product_discount'][$r] ?? null;
                $item_unit = $_POST['product_unit'][$r];
                $item_quantity = $_POST['product_base_quantity'][$r];
                $item_batchno = $_POST['batch_no'][$r];
                $item_serial_no = $_POST['serial_no'][$r];
                $item_expiry = $_POST['expiry'][$r];
                //$item_bonus         = $_POST['bonus'][$r];
                $item_dis1 = $_POST['dis1'][$r];
                $item_dis2 = $_POST['dis2'][$r];

                $net_cost_obj = $this->returns_supplier_model->getAverageCost($item_batchno, $item_code);
                $net_cost = $net_cost_obj[0]->cost_price;

                if (isset($item_code) && isset($real_unit_price) && isset($unit_price) && isset($item_quantity)) {
                    $product_details = $item_type != 'manual' ? $this->site->getProductByCode($item_code) : null;
                    $pr_discount = $this->site->calculateDiscount($item_discount, $unit_price);
                    $unit_price = $this->sma->formatDecimal($unit_price - $pr_discount);
                    $item_net_price = $unit_price;
                    $pr_item_discount = $this->sma->formatDecimal($pr_discount * $item_unit_quantity);
                    $product_discount += $pr_item_discount;

                    //Discount calculation---------------------------------- 
                    //The above will be deleted later becasue order discount is not in use                  
                    $product_discount1 = $this->site->calculateDiscount($item_dis1 . '%', $unit_price);
                    $amount_after_discount1 = $unit_price - $product_discount1;
                    $product_discount2 = $this->site->calculateDiscount($item_dis2 . '%', $amount_after_discount1);


                    $product_item_discount1 = $this->sma->formatDecimal($product_discount1 * $item_unit_quantity);
                    $product_item_discount2 = $this->sma->formatDecimal($product_discount2 * $item_unit_quantity);

                    $product_item_discount = ($product_item_discount1 + $product_item_discount2);
                    $total_product_discount += $product_item_discount;
                    //Discount calculation----------------------------------

                    $pr_item_tax = $item_tax = 0;
                    $tax = '';

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_price);
                        $item_tax = $ctax['amount'];
                        $tax = $ctax['tax'];
                        if (!$product_details || (!empty($product_details) && $product_details->tax_method != 1)) {
                            $item_net_price = $unit_price - $item_tax;
                        }
                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);

                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = (($item_net_price * $item_unit_quantity) + $pr_item_tax - $product_item_discount);
                    $unit = $this->site->getUnitByID($item_unit);

                    $product = [
                        'return_id' => $id,
                        'product_id' => $item_id,
                        'product_code' => $item_code,
                        'product_name' => $item_name,
                        'product_type' => $item_type,
                        'option_id' => $item_option,
                        'net_cost' => $net_cost,
                        'net_unit_price' => $item_net_price,
                        'unit_price' => $this->sma->formatDecimal($item_net_price + $item_tax),
                        'quantity' => $item_quantity,
                        'product_unit_id' => $unit ? $unit->id : null,
                        'product_unit_code' => $unit ? $unit->code : null,
                        'unit_quantity' => $item_unit_quantity,
                        'warehouse_id' => $warehouse_id,
                        'item_tax' => $pr_item_tax,
                        'tax_rate_id' => $item_tax_rate,
                        'tax' => $tax,
                        'discount' => $item_discount,
                        'item_discount' => $product_item_discount,
                        'subtotal' => $this->sma->formatDecimal($subtotal),
                        'serial_no' => $item_serial,
                        'batch_no' => $item_batchno,
                        'serial_number' => $item_serial_no,
                        'expiry' => $item_expiry,
                        'real_unit_price' => $real_unit_price,
                        //'bonus'             => $item_bonus,
                        'bonus' => 0,
                        'discount1' => $item_dis1,
                        'discount2' => $item_dis2
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
            $order_tax = $this->site->calculateOrderTax($this->input->post('order_tax'), ($total + $product_tax - $order_discount));
            $total_tax = $this->sma->formatDecimal(($product_tax + $order_tax), 4);


            //Discount calculation
            // total discount must be deducted from  grandtotal
            //$grand_total    = $this->sma->formatDecimal(($total + $total_tax + $shipping - $order_discount), 4);

            $grand_total = $this->sma->formatDecimal(($total + $total_tax + $shipping - $total_product_discount), 4);
            //Discount calculation

            $data = [
                'date' => $date,
                'reference_no' => $reference,
                'supplier_id' => $supplier_id,
                'supplier' => $supplier,
                'warehouse_id' => $warehouse_id,
                'note' => $note,
                'staff_note' => $staff_note,
                'total' => $total,
                'product_discount' => $total_product_discount,
                'order_discount_id' => $this->input->post('order_discount'),
                'order_discount' => $order_discount,
                'total_discount' => $total_product_discount,
                'product_tax' => $product_tax,
                'order_tax_id' => $this->input->post('order_tax'),
                'order_tax' => $order_tax,
                'total_tax' => $total_tax,
                'grand_total' => $grand_total,
                'shipping' => $shipping,
                'total_items' => $total_items,
                'updated_by' => $this->session->userdata('user_id'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($this->Settings->indian_gst) {
                $data['cgst'] = $total_cgst;
                $data['sgst'] = $total_sgst;
                $data['igst'] = $total_igst;
            }

            if ($_FILES['document']['size'] > 0) {
                $this->load->library('upload');
                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = $this->digital_file_types;
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = false;
                $config['encrypt_name'] = true;
                $this->upload->initialize($config);
                if (!$this->upload->do_upload('document')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    redirect($_SERVER['HTTP_REFERER']);
                }
                $photo = $this->upload->file_name;
                $data['attachment'] = $photo;
            }

            // $this->sma->print_arrays($data, $products);
        }

        if ($this->form_validation->run() == true && $this->returns_supplier_model->updateReturn($id, $data, $products)) {

            $this->delete_previous_entry($id);
            $this->convert_return_invoice($id);

            $this->session->set_userdata('remove_rels', 1);
            $this->session->set_flashdata('message', lang('return_updated'));
            admin_redirect('returns_supplier');
        } else {
            $this->data['inv'] = $inv;
            if ($this->Settings->disable_editing) {
                if ($this->data['inv']->date <= date('Y-m-d', strtotime('-' . $this->Settings->disable_editing . ' days'))) {
                    $this->session->set_flashdata('error', sprintf(lang('return_x_edited_older_than_x_days'), $this->Settings->disable_editing));
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }
            $inv_items = $this->returns_supplier_model->getReturnItems($id);
            $c = rand(100000, 9999999);
            foreach ($inv_items as $item) {

                $row = $this->site->getProductByID($item->product_id);
                if (!$row) {
                    $row = json_decode('{}');
                    $row->tax_method = 0;
                    $row->quantity = 0;
                } else {
                    unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                }

                $row->id = $item->product_id;
                $row->code = $item->product_code;
                $row->name = $item->product_name;
                $row->type = $item->product_type;
                $row->item_tax_method = $row->tax_method;
                $options = $this->returns_supplier_model->getProductOptions($row->id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->returns_supplier_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = false;
                }
                if ($row->promotion) {
                    $row->price = $row->promo_price;
                }
                $row->cost_price = $item->net_cost;
                $row->real_unit_price = $item->real_unit_price ?? $row->price;
                $row->base_quantity = $item->quantity;
                $row->base_unit = $row->unit ?? $item->product_unit_id;
                $row->base_unit_price = $row->unit_price ?? $item->unit_price;
                $row->unit = $item->product_unit_id;
                $row->qty = $item->unit_quantity;
                $row->discount = $item->discount ? $item->discount : '0';
                $row->serial = $item->serial_no;
                $row->option = $item->option_id;
                $row->tax_rate = $item->tax_rate_id;
                $row->batch_no = $item->batch_no;
                $row->serial_number = $item->serial_number;
                $row->expiry = $item->expiry;
                $row->bonus = $item->bonus;
                $row->discount1 = $item->discount1;
                $row->discount2 = $item->discount2;
                $row->comment = '';
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($row->id);
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                $ri = $this->Settings->item_addition ? $row->id : $c;

                $batches = $this->site->getProductBatchesData($row->id, $item->warehouse_id);

                $row->batchPurchaseCost = $row->cost_price;
                $row->batchQuantity = 0;
                if ($batches) {
                    foreach ($batches as $batchesR) {
                        if ($batchesR->batchno == $row->batch_no) {
                            $row->batchQuantity = $batchesR->quantity;
                            break;
                        }
                    }
                }

                $pr[$ri] = ['id' => $c, 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'batches' => $batches];
                $c++;

            }
            $this->data['inv_items'] = json_encode($pr);
            $this->data['id'] = $id;
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
//            $this->data['billers'] = $this->site->getAllCompanies('biller');
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['tax_rates'] = $this->site->getAllTaxRates();
            $this->data['units'] = $this->site->getAllBaseUnits();
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('returns_supplier'), 'page' => lang('returns_supplier')], ['link' => '#', 'page' => lang('edit_return')]];
            $meta = ['page_title' => lang('edit_return'), 'bc' => $bc];
            $this->page_construct('returns_supplier/edit', $meta, $this->data);
        }
    }

    public function getReturns($warehouse_id = null)
    {

        $this->sma->checkPermissions('index');
        if ((!$this->Owner && !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("{$this->db->dbprefix('returns_supplier')}.id as id, DATE_FORMAT({$this->db->dbprefix('returns_supplier')}.date, '%Y-%m-%d %T') as date, reference_no, biller, {$this->db->dbprefix('returns_supplier')}.supplier, grand_total, {$this->db->dbprefix('returns_supplier')}.attachment")
                ->from('returns_supplier')
                ->where('warehouse_id', $warehouse_id);
        } else {
            $this->datatables
                ->select("{$this->db->dbprefix('returns_supplier')}.id as id, DATE_FORMAT({$this->db->dbprefix('returns_supplier')}.date, '%Y-%m-%d %T') as date, reference_no, biller, {$this->db->dbprefix('returns_supplier')}.supplier, grand_total, {$this->db->dbprefix('returns_supplier')}.attachment")
                ->from('returns_supplier');
        }

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where('created_by', $this->session->userdata('user_id'));
        }

        $edit_link         = anchor('admin/returns_supplier/edit/$1', '<i class="fa fa-edit"></i> ' . lang('edit_return'), 'class="tip"');
        $delete_link       = "<a href='#' class='po' title='<b>" . lang('delete_sale') . "</b>' data-content=\"<p>"
        . lang('r_u_sure') . "</p><a class='btn btn-danger po po-delete' href='" . admin_url('returns_supplier/delete/$1') . "'>"
        . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
        . lang('delete_return') . '</a>';
        $journal_entry_link      = anchor('admin/entries/view/journal/?rsid=$1', '<i class="fa fa-eye"></i> ' . lang('Journal Entry'));
        
        $action = '<div class="text-center"><div class="btn-group text-left">'
        . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
        . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
                <li>' . $edit_link . '</li> 
                <li>' . $journal_entry_link . '</li>
                <li>' . $delete_link . '</li> 
        </ul>
    </div></div>';
         
    $this->datatables->add_column('Actions', $action, 'id');
       // $this->datatables->add_column('Actions', "<div class=\"text-center\"><a href='" . admin_url('returns_supplier/edit/$1') . "' class='tip' title='" . lang('edit_return') . "'><i class=\"fa fa-edit\"></i></a> <a href='#' class='tip po' title='<b>" . lang('delete_return') . "</b>' data-content=\"<p>" . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('returns_supplier/delete/$1') . "'>" . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i></a></div>", 'id');
        echo $this->datatables->generate();
    }

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

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('returns_supplier')]];
        $meta = ['page_title' => lang('returns_supplier'), 'bc' => $bc];
        $this->page_construct('returns_supplier/index', $meta, $this->data);
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $sr = addslashes($sr);
        $strict = $analyzed['strict'] ?? false;
        $qty = $strict ? null : $analyzed['quantity'] ?? null;
        $bprice = $strict ? null : $analyzed['price'] ?? null;

        $rows = $this->returns_supplier_model->getProductNames($sr);

        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                $option = false;
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $row->item_tax_method = $row->tax_method;
                $options = $this->returns_supplier_model->getProductOptions($row->id);

                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->returns_supplier_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $opt->cost = 0;
                    $option_id = false;
                }

                $sold = $this->returns_supplier_model->getProductsSold($row->id);
                $row->net_cost = $sold->cost;

                $row->discount1 = 0;
                $row->discount2 = 0;

                $row->option = $option_id;
                if ($row->promotion) {
                    $row->price = $row->promo_price;
                }


                $row->cost_price = $opt->cost;


                // $row->cost       = $row->cost;

                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->real_unit_price = $row->price;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->qty = 1;
                $row->discount = '0';
                $row->serial = '';
                $row->comment = '';
                $row->batch_no = '';
                $row->serial_number = '';
                $row->expiry = '';
                $row->bonus = '0';
                $row->dis1 = 0;
                $row->dis2 = 0;


                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($row->id);
                }
                $row->qty = $qty ? $qty : ($bprice ? $bprice / $row->price : 1);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                // $row->batch_no = $row->batchno;
                $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options,];
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function bch_suggestions()
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }
        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $sr = addslashes($sr);
        $strict = $analyzed['strict'] ?? false;
        $qty = $strict ? null : $analyzed['quantity'] ?? null;
        $bprice = $strict ? null : $analyzed['price'] ?? null;

        $rows = $this->returns_supplier_model->getProductNamesWithBatches($sr, $warehouse_id, $pos);

        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                $option = false;
                unset($row->cost, $row->details, $row->product_details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $row->item_tax_method = $row->tax_method;
                $options = $this->returns_supplier_model->getProductOptions($row->id);

                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->returns_supplier_model->getProductOptionByID($option_id) : current($options);
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $opt->cost = 0;
                    $option_id = false;
                }

                $sold = $this->returns_supplier_model->getProductsSold($row->id);
                $row->net_cost = $sold->cost;

                $row->discount1 = 0;
                $row->discount2 = 0;

                $row->option = $option_id;
                if ($row->promotion) {
                    $row->price = $row->promo_price;
                }


                $row->cost_price = $opt->cost;


                // $row->cost       = $row->cost;

                $row->base_quantity = 0;
                $row->base_unit = $row->unit;
                $row->real_unit_price = $row->price;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->qty = 0;
                $row->discount = '0';
                $row->serial = '';
                $row->comment = '';
                $row->batch_no = '';
                $row->serial_number = '';
                $row->expiry = '';
                $row->bonus = '0';
                $row->dis1 = 0;
                $row->dis2 = 0;


                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($row->id);
                }
                $row->qty = $qty ? $qty : ($bprice ? $bprice / $row->price : 0);
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);
                // $row->batch_no = $row->batchno;
                $row->batch_no = '';
                $row->batchQuantity = 0;
                $row->batchPurchaseCost = 0;
                $row->expiry = null;

                $batches = $this->site->getProductBatchesData($row->id, $warehouse_id);

                $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row' => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, 'batches' => $batches];
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function view($id = null)
    {
        $this->sma->checkPermissions('index', true);
        $this->load->library('inv_qrcode');

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->returns_supplier_model->getReturnByID($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($inv->created_by, true);
        }
        $this->data['customer'] = $this->site->getCompanyByID($inv->supplier_id);
//        $this->data['biller'] = $this->site->getCompanyByID($inv->biller_id);
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
        $this->data['rows'] = $this->returns_supplier_model->getReturnItems($id);

        $this->load->view($this->theme . 'returns_supplier/view', $this->data);
    }
}
