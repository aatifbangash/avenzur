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

        // Sequence-Code
        $this->load->library('SequenceCode');
        $this->sequenceCode = new SequenceCode();
    }
    
    /*public function index()
    {
        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Stock Requests')]];
        $meta = ['page_title' => lang('Stock Requests'), 'bc' => $bc];
        $this->page_construct('stock_request/index', $meta, $this->data);
    }*/

    public function index()
    {
        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $warehouse_id = $this->session->userdata('warehouse_id');

        $stock_requests_array = $this->stock_request_model->getStockRequests($warehouse_id);

        $this->data['stock_requests_array'] = $stock_requests_array;
        $this->data['warehouse_id'] = $warehouse_id;
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Stock Requests')]];
        $meta = ['page_title' => lang('Stock Requests'), 'bc' => $bc];
        $this->page_construct('stock_request/list_requests', $meta, $this->data);
    }

    public function delete($id = null){
        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if($this->stock_request_model->delete($id)){
            $this->session->set_flashdata('message', lang('stock_request_deleted'));
            admin_redirect('stock_request');
        }else{
            $this->session->set_flashdata('error', lang('Could not delete request'));
            admin_redirect('stock_request');
        }
    }

    public function delete_purchase($id = null){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if($this->stock_request_model->delete_purchase($id)){
            $this->session->set_flashdata('message', lang('purchase_request_deleted'));
            admin_redirect('stock_request/purchase_requests');
        }else{
            $this->session->set_flashdata('error', lang('Could not delete request'));
            admin_redirect('stock_request/purchase_requests');
        }
    }

    /*public function view($id = null){
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
    }*/

    public function view($id = null, $xls = null){
        if($xls){
            $stock_array = $this->stock_request_model->getStockRequestItems($id);
            if($stock_array){
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('Stock Order'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Available Quantity'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Average Sale'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('Required Stock'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Months'));

                $row = 2;
                foreach ($stock_array as $data_row) {
                    $available_stock = number_format((float) $data_row->available_stock, 2, '.', '');
                    $avg_stock = isset($data_row->avg_stock) ? number_format((float) ($data_row->avg_stock), 2, '.', '') : number_format((float) ($data_row->avg_last_3_months_sales) / 3, 2, '.', '');
                    if(isset($data_row->required_stock)){
                        $required_stock = $data_row->required_stock;
                    }else{
                        $required_stock = ($data_row->avg_last_3_months_sales / 3) - $data_row->available_stock > 0 ? number_format((float) ($data_row->avg_last_3_months_sales / 3) - $data_row->available_stock, 2, '.', '') : '0.00';
                    } 

                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $available_stock);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $avg_stock);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $required_stock);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, '1 month');
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);

                $filename = 'stock_request';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }

            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }else{
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }

            $stock_array = $this->stock_request_model->getStockRequestItems($id);
            $this->data['stock_array'] = $stock_array;
            $this->data['request_id'] = $id;
            
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Stock Order Request')]];
            $meta = ['page_title' => lang('Stock Order Request'), 'bc' => $bc];
            $this->page_construct('stock_request/view_order', $meta, $this->data);
        } 
    }

    public function edit($id = null){
        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $stock_array = $this->stock_request_model->getStockRequestItems($id);
        $this->data['stock_array'] = $stock_array;
        $this->data['request_id'] = $id;
        
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Stock Order Request')]];
        $meta = ['page_title' => lang('Stock Order Request'), 'bc' => $bc];
        $this->page_construct('stock_request/order', $meta, $this->data);
    }

    public function view_purchase($id = null, $xls = null){
        if($xls){
            $pr_array = $this->stock_request_model->getPurchaseRequestItems($id);
            if($pr_array){
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('Purchase Request'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Available Quantity'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Average Consumption'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('Quantity Required'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Safety Stock'));

                $row = 2;
                foreach ($pr_array as $data_row) {
                    $available_quantity = number_format((float) $data_row->total_warehouses_quantity, 2, '.', '');
                    $total_avg_stock = isset($data_row->total_avg_stock) ? number_format((float) ($data_row->total_avg_stock), 2, '.', '') : '0.00';

                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $available_quantity);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $total_avg_stock);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, number_format((float) $data_row->qreq, 2, '.', ''));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->months.' months');
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);

                $filename = 'purchase_request';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }

            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        }else{
            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            if ($this->input->get('id')) {
                $id = $this->input->get('id');
            }

            $current_pr = $this->stock_request_model->getPurchaseRequestItems($id);
            $this->data['current_pr'] = $current_pr;
            $this->data['request_id'] = $id;

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Purchase Order Request')]];
            $meta = ['page_title' => lang('Purchase Order Request'), 'bc' => $bc];
            $this->page_construct('stock_request/view_pr', $meta, $this->data);
        }
    }

    public function edit_purchase($id = null){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $current_pr = $this->stock_request_model->getPurchaseRequestItems($id);
        $this->data['current_pr'] = $current_pr;
        $this->data['request_id'] = $id;

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Purchase Order Request')]];
        $meta = ['page_title' => lang('Purchase Order Request'), 'bc' => $bc];
        $this->page_construct('stock_request/current_pr', $meta, $this->data);
    }

    public function inventory_check(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $inventory_check_requests_array = $this->stock_request_model->getInventoryCheckRequests();
        $this->data['inventory_check_requests_array'] = $inventory_check_requests_array;
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Inventory Check Requests')]];
        $meta = ['page_title' => lang('Inventory Check Requests'), 'bc' => $bc];
        $this->page_construct('stock_request/list_inventory_check', $meta, $this->data);
    }

    public function delete_inventory_check(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $inventory_check_request_id = $this->uri->segment(4);
        $inventory_check_request_details = $this->stock_request_model->deleteInventoryCheckRequest($inventory_check_request_id );
        
        admin_redirect('stock_request/inventory_check');
    }

    public function hills_adjust_inventory(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->form_validation->set_rules('inventory_check_request_id', $this->lang->line('inventory_check_request_id'), 'required');

		if ($this->form_validation->run() == true) {
            $inventory_check_request_id = $this->input->post('inventory_check_request_id');
            $location_id = $this->input->post('location_id');
            
            // Get manually entered inventory check items (grouped by batch+expiry)
            $this->db->select('product_id, batch_number, expiry_date, shelf, quantity as actual_quantity');
            $this->db->from('sma_inventory_check_items');
            $this->db->where('inv_check_id', $inventory_check_request_id);
            $inventory_check_items = $this->db->get()->result();
            
            if(empty($inventory_check_items)){
                $this->session->set_flashdata('error', 'No inventory check items found.');
                admin_redirect('stock_request/inventory_check');
                return;
            }
            
            $adj_warehouse = $this->site->getAdjustmentStore();
            $products_out = [];
            $products_in = [];
            $product_tax_out = $product_tax_in = 0;
            $total_out = $total_in = 0;
            $grand_total_cost_price_out = $grand_total_cost_price_in = 0;
            $inventory_check_report_data = [];
            $shipping = 0;
            $status = 'completed';
            
            // Process each manually entered item
            foreach($inventory_check_items as $check_item){
                $product_id = $check_item->product_id;
                $batch_number = $check_item->batch_number;
                $expiry_date = $check_item->expiry_date;
                $actual_quantity = $check_item->actual_quantity;
                
                // Get ALL avz_codes for this batch+expiry from inventory_movements
                $this->db->select('im.avz_item_code, im.quantity, im.batch_number, im.expiry_date,
                                 p.code as product_code, p.name as product_name, p.unit as product_unit,
                                 im.net_unit_cost,
                                 im.net_unit_sale,
                                 p.tax_rate');
                $this->db->from('sma_inventory_movements im');
                $this->db->join('sma_products p', 'p.id = im.product_id', 'left');
                $this->db->where('im.product_id', $product_id);
                $this->db->where('im.batch_number', $batch_number);
                $this->db->where('im.location_id', $location_id);
                
                if($expiry_date){
                    $this->db->where('im.expiry_date', $expiry_date);
                }
                
                $avz_entries = $this->db->get()->result();
                
                // Handle products found on shelf but not in system (surplus items)
                if(empty($avz_entries)){
                    // This product was found but not in system - treat as surplus (system_quantity = 0)
                    $product_details = $this->transfers_model->getProductById($product_id);
                    
                    if(!$product_details){
                        continue; // Skip if product not found
                    }
                    
                    // Use default cost from product master or 0
                    $net_cost = $product_details->cost ?? 0;
                    $unit_cost = $net_cost;
                    $item_net_cost = $net_cost;
                    $pr_item_tax = 0;
                    $tax = 0;
                    
                    // Calculate tax if applicable
                    if($product_details->tax_rate && $product_details->tax_rate != 0){
                        $tax_details = $this->site->getTaxRateByID($product_details->tax_rate);
                        if($tax_details){
                            $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                            $item_tax = $ctax['amount'];
                            $tax = $ctax['tax'];
                            
                            if($product_details->tax_method != 1){
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                            
                            $pr_item_tax = $this->sma->formatDecimal(($item_tax * $actual_quantity), 4);
                        }
                    }
                    
                    $subtotal = $this->sma->formatDecimal((($item_net_cost * $actual_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($product_details->unit);
                    
                    // Generate unique AVZ code for new entry
                    //$avz_code = 'AVZ-NEW-' . $product_id . '-' . time();
                    $avz_code = $this->sma->generateUUIDv4();
                    
                    // Create product entry for transfer IN from adjustment store
                    $product = [
                        'product_id'        => $product_id,
                        'product_code'      => $product_details->code,
                        'product_name'      => $product_details->name,
                        'net_unit_cost'     => $net_cost,
                        'unit_cost'         => $this->sma->formatDecimal($item_net_cost + ($item_tax ?? 0), 4),
                        'quantity'          => $actual_quantity,
                        'product_unit_id'   => $product_details->unit,
                        'product_unit_code' => $unit ? $unit->code : '',
                        'unit_quantity'     => $actual_quantity,
                        'quantity_balance'  => $actual_quantity,
                        'warehouse_id'      => $location_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $product_details->tax_rate,
                        'tax'               => $tax,
                        'subtotal'          => $subtotal,
                        'expiry'            => $expiry_date,
                        'real_unit_cost'    => $net_cost,
                        'sale_price'        => $product_details->price ?? 0,
                        'date'              => date('Y-m-d'),
                        'batchno'           => $batch_number,
                        'real_cost'         => $net_cost,
                        'avz_item_code'     => $avz_code
                    ];
                    
                    // Add to IN bucket (surplus found)
                    $products_in[] = $product;
                    $product_tax_in += $pr_item_tax;
                    $total_in += $this->sma->formatDecimal(($item_net_cost * $actual_quantity), 4);
                    $grand_total_cost_price_in += ($net_cost * $actual_quantity);
                    
                    // Add to report data
                    $inventory_check_report_data[] = [
                        'inv_check_id'   => $inventory_check_request_id,
                        'avz_code'       => $avz_code,
                        'old_qty'        => 0,
                        'new_qty'        => $actual_quantity,
                        'item_code'      => $product_details->code,
                        'item_name'      => $product_details->name,
                        'variance'       => $actual_quantity,
                        'old_cost_price' => 0,
                        'new_cost_price' => ($net_cost * $actual_quantity),
                        'sales_price'    => $product_details->price ?? 0,
                        'short'          => ($product_details->price ?? 0) * $actual_quantity,
                        'batch_no'       => $batch_number
                    ];
                    
                    continue; // Move to next item
                }
                
                // Calculate system total quantity across all avz_codes
                $system_quantity = 0;
                foreach($avz_entries as $entry){
                    $system_quantity += $entry->quantity;
                }
                
                // Calculate variance
                $variance = $actual_quantity - $system_quantity;
                
                if($variance == 0){
                    continue; // No adjustment needed
                }
                
                // Determine direction first
                if($variance > 0){
                    // SURPLUS (Transfer IN): Use FIRST avz_code only - simple case
                    $entry = $avz_entries[0];
                    $avz_code = $entry->avz_item_code;
                    $adjusted_quantity = abs($variance);
                    
                    $product_details = $this->transfers_model->getProductById($product_id);
                    $net_cost = $entry->net_unit_cost;
                    $unit_cost = $net_cost;
                    $item_net_cost = $net_cost;
                    $pr_item_tax = 0;
                    $tax = 0;
                    
                    // Calculate tax if applicable
                    if($entry->tax_rate && $entry->tax_rate != 0){
                        $tax_details = $this->site->getTaxRateByID($entry->tax_rate);
                        if($tax_details){
                            $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                            $item_tax = $ctax['amount'];
                            $tax = $ctax['tax'];
                            
                            if($product_details && $product_details->tax_method != 1){
                                $item_net_cost = $unit_cost - $item_tax;
                            }
                            
                            $pr_item_tax = $this->sma->formatDecimal(($item_tax * $adjusted_quantity), 4);
                        }
                    }
                    
                    $subtotal = $this->sma->formatDecimal((($item_net_cost * $adjusted_quantity) + $pr_item_tax), 4);
                    $unit = $this->site->getUnitByID($entry->product_unit);
                    
                    $product = [
                        'product_id'        => $product_id,
                        'product_code'      => $entry->product_code,
                        'product_name'      => $entry->product_name,
                        'net_unit_cost'     => $net_cost,
                        'unit_cost'         => $this->sma->formatDecimal($item_net_cost + ($item_tax ?? 0), 4),
                        'quantity'          => $adjusted_quantity,
                        'product_unit_id'   => $entry->product_unit,
                        'product_unit_code' => $unit ? $unit->code : '',
                        'unit_quantity'     => $adjusted_quantity,
                        'quantity_balance'  => $adjusted_quantity,
                        'warehouse_id'      => $location_id,
                        'item_tax'          => $pr_item_tax,
                        'tax_rate_id'       => $entry->tax_rate,
                        'tax'               => $tax,
                        'subtotal'          => $subtotal,
                        'expiry'            => $entry->expiry_date,
                        'real_unit_cost'    => $net_cost,
                        'sale_price'        => $entry->net_unit_sale,
                        'date'              => date('Y-m-d'),
                        'batchno'           => $entry->batch_number,
                        'real_cost'         => $net_cost,
                        'avz_item_code'     => $avz_code
                    ];
                    
                    $products_in[] = $product;
                    $product_tax_in += $pr_item_tax;
                    $total_in += $this->sma->formatDecimal(($item_net_cost * $adjusted_quantity), 4);
                    $grand_total_cost_price_in += ($net_cost * $adjusted_quantity);
                    
                    // Add to report data
                    $inventory_check_report_data[] = [
                        'inv_check_id'   => $inventory_check_request_id,
                        'avz_code'       => $avz_code,
                        'old_qty'        => $system_quantity,
                        'new_qty'        => $actual_quantity,
                        'item_code'      => $entry->product_code,
                        'item_name'      => $entry->product_name,
                        'variance'       => $variance,
                        'old_cost_price' => ($net_cost * $system_quantity),
                        'new_cost_price' => ($net_cost * $actual_quantity),
                        'sales_price'    => $entry->net_unit_sale,
                        'short'          => $entry->net_unit_sale * $variance,
                        'batch_no'       => $batch_number  // Use batch from check_item, not system
                    ];
                    
                } else {
                    // SHORTAGE (Transfer OUT): May need MULTIPLE avz_codes to fulfill
                    $shortage_remaining = abs($variance);
                    
                    // Store first entry data for report (use first AVZ details but total system quantity)
                    $first_entry = $avz_entries[0];
                    $report_avz_code = $first_entry->avz_item_code;
                    $report_net_cost = $first_entry->net_unit_cost;
                    $report_product_code = $first_entry->product_code;
                    $report_product_name = $first_entry->product_name;
                    $report_net_unit_sale = $first_entry->net_unit_sale;
                    $report_batch_number = $first_entry->batch_number;
                    
                    foreach($avz_entries as $entry){
                        if($shortage_remaining <= 0){
                            break; // Shortage fully covered
                        }
                        
                        $avz_code = $entry->avz_item_code;
                        $avz_quantity = $entry->quantity;
                        
                        // Take what we can from this AVZ (min of shortage_remaining or avz_quantity)
                        $adjusted_quantity = min($shortage_remaining, $avz_quantity);
                        
                        if($adjusted_quantity <= 0){
                            continue; // Nothing to take from this AVZ
                        }
                        
                        $product_details = $this->transfers_model->getProductById($product_id);
                        $net_cost = $entry->net_unit_cost;
                        $unit_cost = $net_cost;
                        $item_net_cost = $net_cost;
                        $pr_item_tax = 0;
                        $tax = 0;
                        
                        // Calculate tax if applicable
                        if($entry->tax_rate && $entry->tax_rate != 0){
                            $tax_details = $this->site->getTaxRateByID($entry->tax_rate);
                            if($tax_details){
                                $ctax = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                                $item_tax = $ctax['amount'];
                                $tax = $ctax['tax'];
                                
                                if($product_details && $product_details->tax_method != 1){
                                    $item_net_cost = $unit_cost - $item_tax;
                                }
                                
                                $pr_item_tax = $this->sma->formatDecimal(($item_tax * $adjusted_quantity), 4);
                            }
                        }
                        
                        $subtotal = $this->sma->formatDecimal((($item_net_cost * $adjusted_quantity) + $pr_item_tax), 4);
                        $unit = $this->site->getUnitByID($entry->product_unit);
                        
                        $product = [
                            'product_id'        => $product_id,
                            'product_code'      => $entry->product_code,
                            'product_name'      => $entry->product_name,
                            'net_unit_cost'     => $net_cost,
                            'unit_cost'         => $this->sma->formatDecimal($item_net_cost + ($item_tax ?? 0), 4),
                            'quantity'          => $adjusted_quantity,
                            'product_unit_id'   => $entry->product_unit,
                            'product_unit_code' => $unit ? $unit->code : '',
                            'unit_quantity'     => $adjusted_quantity,
                            'quantity_balance'  => $adjusted_quantity,
                            'warehouse_id'      => $adj_warehouse->id,
                            'item_tax'          => $pr_item_tax,
                            'tax_rate_id'       => $entry->tax_rate,
                            'tax'               => $tax,
                            'subtotal'          => $subtotal,
                            'expiry'            => $entry->expiry_date,
                            'real_unit_cost'    => $net_cost,
                            'sale_price'        => $entry->net_unit_sale,
                            'date'              => date('Y-m-d'),
                            'batchno'           => $entry->batch_number,
                            'real_cost'         => $net_cost,
                            'avz_item_code'     => $avz_code
                        ];
                        
                        $products_out[] = $product;
                        $product_tax_out += $pr_item_tax;
                        $total_out += $this->sma->formatDecimal(($item_net_cost * $adjusted_quantity), 4);
                        $grand_total_cost_price_out += ($net_cost * $adjusted_quantity);
                        
                        $shortage_remaining -= $adjusted_quantity;
                    }
                    
                    // Add SINGLE report entry for this batch+expiry with TOTAL system quantity
                    $inventory_check_report_data[] = [
                        'inv_check_id'   => $inventory_check_request_id,
                        'avz_code'       => $report_avz_code,
                        'old_qty'        => $system_quantity,  // Use total system quantity (correct!)
                        'new_qty'        => $actual_quantity,
                        'item_code'      => $report_product_code,
                        'item_name'      => $report_product_name,
                        'variance'       => $variance,  // Total variance (negative)
                        'old_cost_price' => ($report_net_cost * $system_quantity),
                        'new_cost_price' => ($report_net_cost * $actual_quantity),
                        'sales_price'    => $report_net_unit_sale,
                        'short'          => $report_net_unit_sale * $variance,
                        'batch_no'       => $batch_number  // Use batch from check_item, not system
                    ];
                }
            }
            
            // Get warehouse details
            $location_details = $this->site->getWarehouseByID($location_id);
            $adj_details = $this->site->getWarehouseByID($adj_warehouse->id);
            $note = 'Inventory Check Adjustment - Proportional Distribution';
            
            $created_any = false;
            
            // Create OUT transfer if any
            if(!empty($products_out)){
                $grand_total_out = $this->sma->formatDecimal(($total_out + $shipping + $product_tax_out), 4);
                $data_out = [
                    'transfer_no'         => 'inv_check_adj_out_' . $inventory_check_request_id,
                    'date'                => date('Y-m-d H:i:s'),
                    'from_warehouse_id'   => $location_id,
                    'from_warehouse_code' => $location_details->code,
                    'from_warehouse_name' => $location_details->name,
                    'to_warehouse_id'     => $adj_warehouse->id,
                    'to_warehouse_code'   => $adj_details->code,
                    'to_warehouse_name'   => $adj_details->name,
                    'note'                => $note,
                    'total_tax'           => $product_tax_out,
                    'total'               => $total_out,
                    'total_cost'          => $grand_total_cost_price_out,
                    'grand_total'         => $grand_total_out,
                    'created_by'          => $this->session->userdata('user_id'),
                    'status'              => $status,
                    'shipping'            => $shipping,
                    'type'                => 'transfer',
                    'sequence_code'       => $this->sequenceCode->generate('TR', 5)
                ];
                
                if($this->transfers_model->addTransfer($data_out, $products_out, [])){
                    $created_any = true;
                }
            }
            
            // Create IN transfer if any
            if(!empty($products_in)){
                $grand_total_in = $this->sma->formatDecimal(($total_in + $shipping + $product_tax_in), 4);
                $data_in = [
                    'transfer_no'         => 'inv_check_adj_in_' . $inventory_check_request_id,
                    'date'                => date('Y-m-d H:i:s'),
                    'from_warehouse_id'   => $adj_warehouse->id,
                    'from_warehouse_code' => $adj_details->code,
                    'from_warehouse_name' => $adj_details->name,
                    'to_warehouse_id'     => $location_id,
                    'to_warehouse_code'   => $location_details->code,
                    'to_warehouse_name'   => $location_details->name,
                    'note'                => $note,
                    'total_tax'           => $product_tax_in,
                    'total'               => $total_in,
                    'total_cost'          => $grand_total_cost_price_in,
                    'grand_total'         => $grand_total_in,
                    'created_by'          => $this->session->userdata('user_id'),
                    'status'              => $status,
                    'shipping'            => $shipping,
                    'type'                => 'transfer',
                    'sequence_code'       => $this->sequenceCode->generate('TR', 5)
                ];
                
                if($this->transfers_model->addTransfer($data_in, $products_in, [])){
                    $created_any = true;
                }
            }
            
            // If transfers created, update status and create report
            if($created_any){
                $this->stock_request_model->updateAdjustmentStatus($inventory_check_request_id);
                
                if(!empty($inventory_check_report_data)){
                    $this->stock_request_model->createInventoryCheckReport($inventory_check_report_data);
                }
                
                $this->session->set_flashdata('message', 'Inventory adjusted successfully using proportional distribution.');
            } else {
                $this->session->set_flashdata('error', 'No adjustments were needed or inventory adjustment failed.');
            }

            admin_redirect('stock_request/inventory_check');
            
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            admin_redirect('stock_request/inventory_check');
        }
    } 

    public function adjust_inventory(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->form_validation->set_rules('inventory_check_request_id', $this->lang->line('inventory_check_request_id'), 'required');

		if ($this->form_validation->run() == true) {
            $inventory_check_request_id = $this->input->post('inventory_check_request_id');
            $inventory_check_array = $this->stock_request_model->getInventoryCheck($inventory_check_request_id, $this->input->post('location_id'));
            $adj_warehouse = $this->site->getAdjustmentStore();
            
            $i = sizeof($inventory_check_array);
            
            $products = [];
            $product_tax = 0;
            $total = 0;
            $grand_total_cost_price = 0;
            for ($r = 0; $r < $i; $r++) {
                $system_quantity    = $inventory_check_array[$r]->system_quantity ? $inventory_check_array[$r]->system_quantity : 0; 
                $actual_quantity    = $inventory_check_array[$r]->quantity ? $inventory_check_array[$r]->quantity : 0; 

                if($system_quantity  == $actual_quantity){
                    continue;
                }else if($system_quantity > $actual_quantity){
                    $to_warehouse           = $adj_warehouse->id;
                    $from_warehouse         = $this->input->post('location_id');
                    $item_unit_quantity     = $system_quantity - $actual_quantity;
                    $item_quantity          = $item_unit_quantity;
                }else if($system_quantity < $actual_quantity){
                    $to_warehouse           = $this->input->post('location_id');
                    $from_warehouse         = $adj_warehouse->id;
                    $item_unit_quantity     = $actual_quantity - $system_quantity;
                    $item_quantity          = $item_unit_quantity;
                }

                $note                   = 'Inventory Check Adjustment';
                $shipping               =  0;
                $status                 = 'completed';
                $from_warehouse_details = $this->site->getWarehouseByID($from_warehouse);
                $from_warehouse_code    = $from_warehouse_details->code;
                $from_warehouse_name    = $from_warehouse_details->name;
                $to_warehouse_details   = $this->site->getWarehouseByID($to_warehouse);
                $to_warehouse_code      = $to_warehouse_details->code;
                $to_warehouse_name      = $to_warehouse_details->name;

                $pr_id              = $inventory_check_array[$r]->product_id; 
                $item_code          = $inventory_check_array[$r]->product_code;
                $avz_code           = $inventory_check_array[$r]->avz_code;
                $item_net_cost      = $inventory_check_array[$r]->net_unit_cost;
                $unit_cost          = $inventory_check_array[$r]->net_unit_cost;
                $real_unit_cost     = $inventory_check_array[$r]->real_unit_cost;
                $net_unit_cost      = $inventory_check_array[$r]->net_unit_cost;
                $net_unit_sale      = $inventory_check_array[$r]->net_unit_sale;
                
                $item_tax_rate      = $inventory_check_array[$r]->tax_rate;
                $item_batchno       = $inventory_check_array[$r]->batch_number;
                $item_expiry        = $inventory_check_array[$r]->expiry_date;
                $item_unit          = $inventory_check_array[$r]->unit;
                $unit_cost          = $item_net_cost;

                $product_details    = $this->transfers_model->getProductById($pr_id);
                $net_cost = $net_unit_cost;
                $real_cost = $real_unit_cost;
                
                if (isset($item_code) && isset($item_quantity)) {

                    if (isset($item_tax_rate) && $item_tax_rate != 0) {
                        $tax_details = $this->site->getTaxRateByID($item_tax_rate);
                        $ctax        = $this->site->calculateTax($product_details, $tax_details, $unit_cost);
                        $item_tax    = $ctax['amount'];
                        $tax         = $ctax['tax'];

                        if (!empty($product_details) && $product_details->tax_method != 1) {
                            $item_net_cost = $unit_cost - $item_tax;
                        }

                        $pr_item_tax = $this->sma->formatDecimal(($item_tax * $item_unit_quantity), 4);
                    }

                    $product_tax += $pr_item_tax;
                    $subtotal = $this->sma->formatDecimal((($item_net_cost * $item_unit_quantity) + $pr_item_tax), 4);
                    $unit     = $this->site->getUnitByID($item_unit); 

                    $product = [
                        'product_id'        => $product_details->id,
                        'product_code'      => $item_code,
                        'product_name'      => $product_details->name,
                        'net_unit_cost'     => $net_cost,
                        //'net_unit_cost1'          => $net_unit_cost,
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
                        'sale_price'        => $net_unit_sale, //$this->sma->formatDecimal($item_net_cost, 4),
                        'date'              => date('Y-m-d'),
                        'batchno'           => $item_batchno,
                        'real_cost'         => $real_cost,
                        'avz_item_code'     => $avz_code
                    ];

                    $products[] = ($product);
                    $total += $this->sma->formatDecimal(($item_net_cost * $item_unit_quantity), 4);
                    $grand_total_cost_price +=  ($net_cost* $item_unit_quantity);  
                    $grand_total = $this->sma->formatDecimal(($total + $shipping + $product_tax), 4);

                }
            }

            $data = [
                        'transfer_no' => 'inv_check_adjustment',
                        'date'                    => date('Y-m-d H:i:s'),
                        'from_warehouse_id'       => $from_warehouse,
                        'from_warehouse_code'     => $from_warehouse_code,
                        'from_warehouse_name'     => $from_warehouse_name,
                        'to_warehouse_id'         => $to_warehouse,
                        'to_warehouse_code'       => $to_warehouse_code,
                        'to_warehouse_name'       => $to_warehouse_name,
                        'note'                    => $note,
                        'total_tax'               => $product_tax,
                        'total'                   => $total,
                        'total_cost'              => $grand_total_cost_price,
                        'grand_total'             => $grand_total,   
                        'created_by'              => $this->session->userdata('user_id'),
                        'status'                  => $status,
                        'shipping'                => $shipping,
                        'type'                    => 'transfer',
                        'sequence_code'           => $this->sequenceCode->generate('TR', 5)
                    ];

            if($transfer_id = $this->transfers_model->addTransfer($data, $products, [])){
                $this->stock_request_model->updateAdjustmentStatus($inventory_check_request_id);

                $inventory_check_report_data = [];
                for ($r = 0; $r < $i; $r++) {

                    $inventory_check_report_record = [
                        'inv_check_id'        => $inventory_check_request_id,
                        'avz_code'            => $inventory_check_array[$r]->avz_code,
                        'old_qty'             => $inventory_check_array[$r]->system_quantity,
                        'new_qty'             => $inventory_check_array[$r]->quantity,
                        'item_code'           => $inventory_check_array[$r]->product_code,
                        'item_name'           => $inventory_check_array[$r]->product_name,
                        'variance'            => ($inventory_check_array[$r]->quantity - $inventory_check_array[$r]->system_quantity),
                        'old_cost_price'      => ($inventory_check_array[$r]->net_unit_cost * $inventory_check_array[$r]->system_quantity),
                        'new_cost_price'      => ($inventory_check_array[$r]->net_unit_cost * $inventory_check_array[$r]->quantity),
                        'sales_price'         => $inventory_check_array[$r]->net_unit_sale,
                        'short'               => $inventory_check_array[$r]->net_unit_sale * ($inventory_check_array[$r]->quantity - $inventory_check_array[$r]->system_quantity),
                        'batch_no'            => $inventory_check_array[$r]->batch_number
                    ];

                    $inventory_check_report_data[] = ($inventory_check_report_record);
                }

                $this->stock_request_model->createInventoryCheckReport($inventory_check_report_data);
            }

            admin_redirect('stock_request/inventory_check');
            
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            admin_redirect('stock_request/inventory_check');
        }
    }

    /**
     * AJAX: Get closed shelves for a warehouse/request
     */
    public function get_closed_shelves() {
        $warehouse_id = $this->input->post('warehouse_id');
        $closed_shelves = [];
        if ($warehouse_id) {
            // Get current pending inventory check request for this warehouse
            $this->db->select('id');
            $this->db->from('sma_inventory_check_requests');
            $this->db->where('location_id', $warehouse_id);
            $this->db->where('status', 'pending');
            $this->db->order_by('id', 'DESC');
            $this->db->limit(1);
            $request_query = $this->db->get();
            $current_request = $request_query->row();
            $current_request_id = $current_request ? $current_request->id : null;

            if ($current_request_id) {
                $this->db->select('shelf');
                $this->db->from('sma_inventory_check_closed_shelves');
                $this->db->where('inv_check_id', $current_request_id);
                $query = $this->db->get();
                foreach ($query->result() as $row) {
                    $closed_shelves[] = $row->shelf;
                }
            }
        }
        $response = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(),
            'closed_shelves' => $closed_shelves
        ];
        $this->sma->send_json($response);
    }

    public function view_inventory_check_report(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $inventory_check_request_id = $this->uri->segment(4);
        $inventory_check_request_details = $this->stock_request_model->getInventoryCheckRequestById($inventory_check_request_id );
        $inventory_check_report_data = $this->stock_request_model->getInventoryCheckReportById($inventory_check_request_id );
        $warehouse_detail = $this->site->getWarehouseByID($inventory_check_request_details[0]->location_id);

        
        $this->data['warehouse_detail'] = $warehouse_detail;
        $this->data['inventory_check_request_details'] = $inventory_check_request_details[0];
        $this->data['inventory_check_report_data'] = $inventory_check_report_data;
        //echo '<pre>';print_r($this->data['inventory_check_report_data']);exit;
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Inventory Check Report')]];
        $meta = ['page_title' => lang('Inventory Check Report'), 'bc' => $bc];
        $this->page_construct('stock_request/view_inventory_check_report', $meta, $this->data);
    }

    public function view_inventory_check_shelfwise(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $inventory_check_request_id = $this->uri->segment(4);
        $shelf = $this->input->get('shelf');
        $pdf = $this->input->get('pdf'); // Check if PDF download requested

        $inventory_check_request_details = $this->stock_request_model->getInventoryCheckRequestById($inventory_check_request_id );
        $warehouse_detail = $this->site->getWarehouseByID($inventory_check_request_details[0]->location_id);

        // Query from sma_inventory_check_items as PRIMARY source to not miss any manually added items
        $this->db->select('ici.id, ici.product_id, 
                          ici.batch_number as actual_batch, 
                          ici.expiry_date as actual_expiry, 
                          ici.shelf, ici.shelf as actual_shelf,
                          ici.quantity as quantity, ici.quantity as actual_quantity,
                          p.code as product_code, p.code as item_code,
                          p.name as product_name,
                          COALESCE(SUM(im.quantity), 0) as system_quantity,
                          MAX(im.batch_number) as system_batch,
                          MAX(im.expiry_date) as system_expiry');
        $this->db->from('sma_inventory_check_items ici');
        $this->db->join('sma_products p', 'p.id = ici.product_id', 'left');
        $this->db->join('sma_inventory_movements im', 
                       'im.product_id = ici.product_id 
                        AND im.batch_number = ici.batch_number 
                        AND im.location_id = ' . $this->db->escape($inventory_check_request_details[0]->location_id) . '
                        AND (im.expiry_date = ici.expiry_date OR (im.expiry_date IS NULL AND ici.expiry_date IS NULL))', 
                       'left');
        $this->db->where('ici.inv_check_id', $inventory_check_request_id);
        
        // Apply shelf filter at SQL level for efficiency
        if($shelf && $shelf != ''){
            $this->db->where('ici.shelf', $shelf);
        }
        
        $this->db->group_by('ici.id, ici.product_id, ici.batch_number, ici.expiry_date, ici.shelf, ici.quantity, p.code, p.name');
        $this->db->order_by('ici.shelf ASC, p.name ASC');
        
        $inventory_check_array = $this->db->get()->result();
        
        // Get distinct shelves for filter dropdown
        $this->db->select('DISTINCT(shelf) as shelf');
        $this->db->from('sma_inventory_check_items');
        $this->db->where('inv_check_id', $inventory_check_request_id);
        $this->db->where('shelf IS NOT NULL');
        $this->db->where('shelf !=', '');
        $this->db->order_by('shelf', 'ASC');
        $shelves = $this->db->get()->result();
        
        $this->data['warehouse_detail'] = $warehouse_detail;
        $this->data['inventory_check_request_details'] = $inventory_check_request_details[0];
        $this->data['inventory_check_array'] = $inventory_check_array;
        $this->data['shelves'] = $shelves;
        $this->data['selected_shelf'] = $shelf;
        
        // If PDF requested, generate PDF
        if($pdf == '1'){
            $this->load->library('tec_mpdf');
            
            $html = $this->load->view($this->theme . 'stock_request/view_inventory_check_shelf_wise_pdf', $this->data, true);
            
            $filename = 'Inventory_Check_' . $inventory_check_request_id;
            if($shelf){
                $filename .= '_Shelf_' . $shelf;
            }
            $filename .= '_' . date('Y-m-d') . '.pdf';
            
            // Generate PDF (D = download)
            $this->tec_mpdf->generate($html, $filename, 'D', null, 15, null, 15, 'P');
        } else {
            // Regular view
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Inventory Check Requests')]];
            $meta = ['page_title' => lang('Inventory Check Requests'), 'bc' => $bc];
            $this->page_construct('stock_request/view_inventory_check_shelf_wise', $meta, $this->data);
        }
    }

    public function view_inventory_check(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $inventory_check_request_id = $this->uri->segment(4);

        $inventory_check_request_details = $this->stock_request_model->getInventoryCheckRequestById($inventory_check_request_id );
        $warehouse_detail = $this->site->getWarehouseByID($inventory_check_request_details[0]->location_id);

        if($this->Settings->site_name == 'Hills Business Medical'){
            $inventory_check_array = $this->stock_request_model->getInventoryCheckByBatch($inventory_check_request_id, $inventory_check_request_details[0]->location_id);
        }else{
            $inventory_check_array = $this->stock_request_model->getInventoryCheck($inventory_check_request_id, $inventory_check_request_details[0]->location_id);
        }
        
        //echo '<pre>';print_r($inventory_check_array);exit;
        $this->data['warehouse_detail'] = $warehouse_detail;
        $this->data['inventory_check_request_details'] = $inventory_check_request_details[0];
        $this->data['inventory_check_array'] = $inventory_check_array;
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Inventory Check Requests')]];
        $meta = ['page_title' => lang('Inventory Check Requests'), 'bc' => $bc];
        $this->page_construct('stock_request/view_inventory_check', $meta, $this->data);
    }

    public function upload_csv_inventory(){
		$this->data['warehouses'] = $this->site->getAllWarehouses();
        //$this->data['suppliers'] = $this->site->getAllCompanies('supplier');
        $this->load->view($this->theme . 'stock_request/upload_csv_inventory', $this->data);
	}

    public function upload_csv_inventory_request(){
        $this->form_validation->set_rules('warehouse', $this->lang->line('warehouse'), 'required');
		$this->form_validation->set_rules('csvfile', $this->lang->line('upload_file'), 'xss_clean');

		if ($this->form_validation->run() == true) {
            $warehouse = $this->input->post('warehouse');
            
            if (isset($_FILES['csvfile'])) {
                $this->load->library('upload');

                $config['upload_path'] = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size'] = $this->allowed_file_size;
                $config['overwrite'] = true;

                $this->upload->initialize($config);
				
                if (!$this->upload->do_upload('csvfile')) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('entries');
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
            
                    $this->session->set_flashdata('error', lang('too_many_records'));
                    redirect($_SERVER['HTTP_REFERER']);
                    exit();
                }

				$parsed = $arrResult;

				if(sizeOf($parsed) > 0){
                    $insert_inv_req = [
                        'location_id' => $warehouse,
                        'status' => 'pending',
                        'date' => date('Y-m-d')
                    ];

                    $this->db->insert('sma_inventory_check_requests', $insert_inv_req);
					$inv_check_id = $this->db->insert_id();

                    $count = 0;
                    foreach ($parsed as $row) {
                        // Now extract the transaction row
                        if($this->Settings->site_name == 'Hills Business Medical'){
                            if ($count == 0) { $count++; continue; } // Skip header
                            $product_code = $row[0] ?? '';
                            $item_batchno = $row[1] ?? '';
                            $item_expiry = $row[2] ?? '';
                            $item_quantity = $row[3] ?? '';

                            $product_details = $this->db
                                    ->where('item_code', $product_code) // exact match
                                    ->get('sma_products')
                                    ->row();

                            if($product_details) {
                                $product_id = $product_details->id;
                            }else{
                                $product_details = $this->db
                                    ->where('code', $product_code) // exact match
                                    ->get('sma_products')
                                    ->row();
                            }
                            
                            // -----------------------------
                            // FIND MATCHING (AVZ CODE)
                            // -----------------------------
                            $this->db->select('avz_item_code', false);
                            $this->db->from('sma_inventory_movements');
                            $this->db->where('product_id', $product_details->id);
                            $this->db->where('batch_number', $item_batchno);
                            $this->db->where('location_id', $warehouse);
                            $this->db->where('expiry_date', date('Y-m-d', strtotime($item_expiry)));
                            $this->db->group_by('avz_item_code');
                            //$this->db->having('qty >=', $quantity);

                            $inventory_details = $this->db->get()->row();
                            
                            $record = [
                                'inv_check_id'    => $inv_check_id,
                                'avz_code'        => $inventory_details->avz_item_code ?? '',
                                'product_id'     => $product_details->id ?? '',
                                'batch_number'   => $item_batchno,
                                'expiry_date'    => date('Y-m-d', strtotime($item_expiry)),
                                'quantity'        => $item_quantity ?? '',
                            ];                        
                        }else{
                            $record = [
                                'inv_check_id'    => $inv_check_id,
                                'avz_code'        => $row[0] ?? '',
                                'quantity'        => $row[1] ?? '',
                            ];
                        }
            
                        $inv_item_id = $this->db->insert('sma_inventory_check_items', $record);
                    }
                }
				
				admin_redirect('stock_request/inventory_check');
				
			}
        } else {
            $data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            admin_redirect('stock_request/inventory_check');
        }
    }

    public function purchase_requests(){
        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        //$warehouse_id = $this->session->userdata('warehouse_id');

        $purchase_requests_array = $this->stock_request_model->getPurchaseRequests();

        $this->data['purchase_requests_array'] = $purchase_requests_array;
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Purchase Requests')]];
        $meta = ['page_title' => lang('Purchase Requests'), 'bc' => $bc];
        $this->page_construct('stock_request/list_purchase_requests', $meta, $this->data);
    }

    public function current_pr(){
        //$this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses']   = $this->site->getAllWarehouses();

        if ($_POST && !$_POST['search_product']) {
            $status = $_POST['status'];
            for($i=0;$i<sizeof($_POST['product_id']);$i++){
                $product_id      = $_POST['product_id'][$i];
                $available_stock      = $_POST['available_stock'][$i];
                $avg_stock      = $_POST['avg_stock'][$i];
                $required_stock      = $_POST['required_stock'][$i];
                $safety_stock = $_POST['safety_stock'][$i];

                $item = [
                    'product_id'        => $product_id,
                    'available_stock'   => $available_stock,
                    'avg_stock'         => $avg_stock,
                    'required_stock'    => $required_stock,
                    'months'            => $safety_stock
                ];

                $items[] = $item;
            }
        
            if (empty($items)) {
                $this->session->set_flashdata('error', $this->lang->line('Products not found'));
                admin_redirect('stock_request/purchase_requests');
            } else {
                krsort($items);
            }
            
            $data = [
                'date' => date('Y-m-d'),
                'status' => $status,
                'approved_by' => $this->session->userdata['user_id']
            ];
            
        }

        if($_POST && !$_POST['search_product']){
            $warehouse_id = isset($_POST['warehouse']) ? $_POST['warehouse'] : $_POST['warehouse_id'];
            $fromdate = $_POST['fromdate'];
            $todate = $_POST['todate'];
            // Resume work here on dates check, to update only those warehouses which fall in date range
            if(isset($_POST['request_id'])){
                if($this->stock_request_model->editPurchaseRequest($_POST['request_id'], $data, $items, $warehouse_id, $fromdate, $todate)){
                    $this->session->set_flashdata('message', $this->lang->line('Purchase_request_edited'));
                    admin_redirect('stock_request/purchase_requests');
                }else{
                    $this->session->set_flashdata('error', $this->lang->line('Purchase request not edited'));
                    admin_redirect('stock_request/purchase_requests');
                }    
            }else{
                if($this->stock_request_model->addPurchaseRequest($data, $items, $warehouse_id, $fromdate, $todate)){
                    $this->session->set_flashdata('message', $this->lang->line('Purchase_request_added'));
                    admin_redirect('stock_request/purchase_requests');
                }else{
                    $this->session->set_flashdata('error', $this->lang->line('Purchase request not added'));
                    admin_redirect('stock_request/purchase_requests');
                }   
            }
        }else{
            $warehouse_id = isset($_POST['warehouse']) ? $_POST['warehouse'] : $_POST['warehouse_id'];
            $fromdate = $_POST['fromdate'];
            $todate = $_POST['todate'];
           
            $current_pr = $this->stock_request_model->getCurrentPR($warehouse_id, $fromdate, $todate);
            $this->data['current_pr'] = $current_pr;
            $this->data['warehouse_id'] = $warehouse_id;

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('opened Purchase Request')]];
            $meta = ['page_title' => lang('Opened Purchase Request'), 'bc' => $bc];
            $this->page_construct('stock_request/current_pr', $meta, $this->data);
        }
    }

    public function stock_order(){
        //$this->sma->checkPermissions();
        //$this->form_validation->set_message('is_natural_no_zero', $this->lang->line('no_zero_required'));

        //$this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $warehouse_id = $this->session->userdata('warehouse_id');
        $productId = $this->input->post('product') ? $this->input->post('product') : 0;
        $product_ids = $this->input->post('product_ids') ? $this->input->post('product_ids') : 0;

        if ($_POST && !$_POST['search_product']) {
            $status = $_POST['status'];
            for($i=0;$i<sizeof($_POST['product_id']);$i++){
                $product_id      = $_POST['product_id'][$i];
                $available_stock      = $_POST['available_stock'][$i];
                $avg_stock      = $_POST['avg_stock'][$i];
                $required_stock      = $_POST['required_stock'][$i];

                $item = [
                    'product_id'        => $product_id,
                    'available_stock'   => $available_stock,
                    'avg_stock'         => $avg_stock,
                    'required_stock'    => $required_stock
                ];

                $items[] = $item;
            }
        
            if (empty($items)) {
                $this->session->set_flashdata('error', $this->lang->line('Products not found'));
                admin_redirect('stock_request/stock_order');
            } else {
                krsort($items);
            }

            $data = [
                'warehouse_id' => $warehouse_id,
                'status' => $status,
                'date' => date('Y-m-d')
            ];
            
        }
        
        if($_POST && !$_POST['search_product']){
            if(isset($_POST['request_id'])){
                if($this->stock_request_model->editStockRequest($_POST['request_id'], $data, $items)){
                    $this->session->set_flashdata('message', $this->lang->line('Stock_request_edited'));
                    admin_redirect('stock_request');
                }else{
                    $this->session->set_flashdata('error', $this->lang->line('Stock request not edited'));
                    admin_redirect('stock_request');
                }    
            }else{
                if($this->stock_request_model->addStockRequest($data, $items)){
                    $this->session->set_flashdata('message', $this->lang->line('Stock_request_added'));
                    admin_redirect('stock_request');
                }else{
                    $this->session->set_flashdata('error', $this->lang->line('Stock request not added'));
                    admin_redirect('stock_request');
                }   
            }
            
        } else{
            $stock_array = $this->stock_request_model->getStockForPharmacy($warehouse_id, $product_ids);
            $products = $this->products_model->getAllProductsOnLocation($warehouse_id);
            $this->data['stock_array'] = $stock_array;
            $this->data['product'] = $product_ids;
            $this->data['products'] = $products;

            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Stock Order Request')]];
            $meta = ['page_title' => lang('Stock Order Request'), 'bc' => $bc];
            $this->page_construct('stock_request/order', $meta, $this->data);
        }   
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
    
    /*public function delete($id = null)
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
    }*/
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
    
    /*public function view($transfer_id = null)
    {
        //$this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer            = $this->transfers_model->getTransferByID($transfer_id);
        
        $this->data['rows']           = $this->transfers_model->getAllTransferItems($transfer_id, $transfer->status);
        $this->data['from_warehouse'] = $this->site->getWarehouseByID($transfer->from_warehouse_id);
        $this->data['to_warehouse']   = $this->site->getWarehouseByID($transfer->to_warehouse_id);
        $this->data['transfer']       = $transfer;
        $this->data['tid']            = $transfer_id;
        $this->data['attachments']    = $this->site->getAttachments($transfer_id, 'transfer');
        $this->data['created_by']     = $this->site->getUser($transfer->created_by);
        // $this->data['updated_by']     = $this->site->getUser($transfer->updated_by);
        $this->load->view($this->theme . 'stock_request/view', $this->data);
    }*/
    
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

    // =====================================================
    // HILLS BUSINESS INVENTORY CHECK (MANUAL ENTRY)
    // =====================================================

    /**
     * Main page for Hills Business inventory check
     * Shows warehouse and shelf filters, then products table
     */
    public function hills_inventory_check(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        
        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('Hills Inventory Check')]];
        $meta = ['page_title' => lang('Hills Inventory Check'), 'bc' => $bc];
        $this->page_construct('stock_request/hills_inventory_check', $meta, $this->data);
    }

    /**
     * AJAX: Get all shelves for selected warehouse
     */
    public function get_warehouse_shelves(){
        $warehouse_id = $this->input->post('warehouse_id');
        
        if(!$warehouse_id){
            $this->sma->send_json(['error' => 1, 'msg' => 'Warehouse ID required']);
        }

        // Get distinct shelves from sma_product_shelves for this warehouse
        $this->db->select('DISTINCT(ps.shelf) as shelf');
        $this->db->from('sma_product_shelves ps');
        $this->db->join('sma_inventory_movements im', 'im.product_id = ps.product_id', 'inner');
        $this->db->where('ps.warehouse_id', $warehouse_id);
        $this->db->where('im.location_id', $warehouse_id);
        $this->db->where('ps.shelf IS NOT NULL');
        $this->db->where('ps.shelf !=', '');
        $this->db->group_by('ps.shelf');
        $this->db->order_by('ps.shelf', 'ASC');
        
        $query = $this->db->get();
        $shelves = $query->result();

        $response = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash()
        ];

        if($shelves){
            $response['error'] = 0;
            $response['shelves'] = $shelves;
            $this->sma->send_json($response);
        } else {
            $response['error'] = 1;
            $response['msg'] = 'No shelves found for this warehouse';
            $this->sma->send_json($response);
        }
    }

    /**
     * AJAX: Get products list for dropdown (distinct products only)
     */
    public function get_shelf_products_dropdown(){
        $warehouse_id = $this->input->post('warehouse_id');
        $shelf = $this->input->post('shelf');
        
        if(!$warehouse_id || !$shelf){
            $this->sma->send_json(['error' => 1, 'msg' => 'Warehouse ID and Shelf required']);
        }

        // Get distinct products for dropdown
        $this->db->select('DISTINCT(p.id) as product_id, p.code as product_code, p.item_code, p.name as product_name');
        $this->db->from('sma_products p');
        $this->db->join('sma_product_shelves ps', 'ps.product_id = p.id', 'inner');
        $this->db->join('sma_inventory_movements im', 'im.product_id = p.id', 'inner');
        $this->db->where('ps.warehouse_id', $warehouse_id);
        $this->db->where('ps.shelf', $shelf);
        $this->db->where('im.location_id', $warehouse_id);
        $this->db->order_by('p.name', 'ASC');
        
        $query = $this->db->get();
        $products = $query->result();

        $response = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash()
        ];

        if($products){
            $response['error'] = 0;
            $response['products'] = $products;
            $this->sma->send_json($response);
        } else {
            $response['error'] = 1;
            $response['msg'] = 'No products found for this shelf';
            $this->sma->send_json($response);
        }
    }

    /**
     * AJAX: Get all products with batch/expiry for selected shelf
     * Optionally filter by specific product_id
     * Also returns saved quantities from current pending request
     */
    public function get_shelf_products(){
        $warehouse_id = $this->input->post('warehouse_id');
        $shelf = $this->input->post('shelf');
        $product_id = $this->input->post('product_id'); // Optional filter
        
        if(!$warehouse_id || !$shelf){
            $this->sma->send_json(['error' => 1, 'msg' => 'Warehouse ID and Shelf required']);
        }

        // Get current pending inventory check request for this warehouse
        $this->db->select('id');
        $this->db->from('sma_inventory_check_requests');
        $this->db->where('location_id', $warehouse_id);
        $this->db->where('status', 'pending');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $request_query = $this->db->get();
        $current_request = $request_query->row();
        $current_request_id = $current_request ? $current_request->id : null;

        // Get products from inventory movements (existing items in system)
        $this->db->select('p.id as product_id, p.code as product_code, p.item_code, p.name as product_name, 
                          im.batch_number, im.expiry_date,
                          SUM(im.quantity) as system_quantity', FALSE);
        $this->db->from('sma_products p');
        $this->db->join('sma_product_shelves ps', 'ps.product_id = p.id', 'inner');
        $this->db->join('sma_inventory_movements im', 'im.product_id = p.id', 'inner');
        $this->db->where('ps.warehouse_id', $warehouse_id);
        $this->db->where('ps.shelf', $shelf);
        $this->db->where('im.location_id', $warehouse_id);
        
        // If specific product is selected, filter by it
        if($product_id && $product_id != ''){
            $this->db->where('p.id', $product_id);
        }
        
        $this->db->group_by('p.id, im.batch_number, im.expiry_date');
        $this->db->order_by('p.name', 'ASC');
        
        $query = $this->db->get();
        $products_from_inventory = $query->result();
        
        // Get additional products from current inventory check items (manually added items)
        $products_from_check = [];
        if($current_request_id){
            $this->db->select('p.id as product_id, p.code as product_code, p.item_code, p.name as product_name, 
                              ici.batch_number, ici.expiry_date,
                              0 as system_quantity', FALSE);
            $this->db->from('sma_inventory_check_items ici');
            $this->db->join('sma_products p', 'p.id = ici.product_id', 'inner');
            $this->db->where('ici.inv_check_id', $current_request_id);
            $this->db->where('ici.shelf', $shelf);
            
            // If specific product is selected, filter by it
            if($product_id && $product_id != ''){
                $this->db->where('p.id', $product_id);
            }
            
            $this->db->group_by('p.id, ici.batch_number, ici.expiry_date');
            $this->db->order_by('p.name', 'ASC');
            
            $check_query = $this->db->get();
            $products_from_check = $check_query->result();
        }
        
        // Merge both arrays and remove duplicates
        $products_map = [];
        
        // Add inventory movement products first
        foreach($products_from_inventory as $product){
            $key = $product->product_id . '_' . $product->batch_number . '_' . $product->expiry_date;
            $products_map[$key] = $product;
        }
        
        // Add manually added products (only if they don't already exist)
        foreach($products_from_check as $product){
            $key = $product->product_id . '_' . $product->batch_number . '_' . $product->expiry_date;
            if(!isset($products_map[$key])){
                $products_map[$key] = $product;
            }
        }
        
        // Convert back to array
        $products = array_values($products_map);
        
        // If there's a pending request, get saved quantities by batch+expiry
        if($current_request_id && $products){
            $this->db->select('product_id, batch_number, expiry_date, shelf, SUM(quantity) as quantity', FALSE);
            $this->db->from('sma_inventory_check_items');
            $this->db->where('inv_check_id', $current_request_id);
            $this->db->where('shelf', $shelf);
            $this->db->group_by('product_id, batch_number, expiry_date, shelf');
            $saved_query = $this->db->get();
            $saved_items = $saved_query->result();

            // Create lookup array for saved values
            $saved_lookup = [];
            foreach($saved_items as $saved){
                $key = $saved->product_id . '_' . $saved->batch_number . '_' . $saved->expiry_date;
                $saved_lookup[$key] = [
                    'quantity' => $saved->quantity
                ];
            }
            
            // Add saved values to products
            foreach($products as $product){
                $key = $product->product_id . '_' . $product->batch_number . '_' . $product->expiry_date;
                if(isset($saved_lookup[$key])){
                    $product->saved_quantity = $saved_lookup[$key]['quantity'];
                } else {
                    $product->saved_quantity = null;
                }
            }
        }

        $response = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash()
        ];

        if($products){
            $response['error'] = 0;
            $response['products'] = $products;
            $response['request_id'] = $current_request_id;
            $response['is_existing_request'] = $current_request_id ? true : false;
            $this->sma->send_json($response);
        } else {
            $response['error'] = 1;
            $response['msg'] = 'No products found for this shelf';
            $this->sma->send_json($response);
        }
    }

    /**
     * Save Hills Business inventory check entries
     * Reuses existing pending request if available
     */
    public function save_hills_inventory_check(){
        $this->form_validation->set_rules('warehouse_id', 'Warehouse', 'required');
        $this->form_validation->set_rules('shelf', 'Shelf', 'required');

        if ($this->form_validation->run() == true) {
            $warehouse_id = $this->input->post('warehouse_id');
            
            // Check if there's an existing pending request for this warehouse
            $this->db->select('id');
            $this->db->from('sma_inventory_check_requests');
            $this->db->where('location_id', $warehouse_id);
            $this->db->where('status', 'pending');
            $this->db->order_by('id', 'DESC');
            $this->db->limit(1);
            $existing_request = $this->db->get()->row();
            
            if($existing_request){
                // Reuse existing pending request
                $inv_check_id = $existing_request->id;
            } else {
                // Create new inventory check request record
                $insert_inv_req = [
                    'location_id' => $warehouse_id,
                    'status' => 'pending',
                    'date' => date('Y-m-d'),
                ];
                $this->db->insert('sma_inventory_check_requests', $insert_inv_req);
                $inv_check_id = $this->db->insert_id();
            }

            // Get posted product data
            $shelf = $this->input->post('shelf');
            $product_ids = $this->input->post('product_id');
            $batch_numbers = $this->input->post('batch_number');
            $expiry_dates = $this->input->post('expiry_date');
            $quantities = $this->input->post('quantity');

            if($product_ids && is_array($product_ids)){
                $count = 0;
                foreach($product_ids as $index => $product_id){
                    $quantity = $quantities[$index] ?? '';
                    
                    // Get batch and expiry (no more separate actual/system)
                    $batch_number = $batch_numbers[$index] ?? '';
                    $expiry_date = $expiry_dates[$index] ?? '';
                    
                    // Use same values for both batch_number and system_batch_number
                    $batch_to_save = $batch_number;
                    $expiry_to_save_raw = $expiry_date;
                    
                    // Only save if quantity is entered (including 0 for "not found on shelf")
                    // Empty string means user didn't enter anything, so skip it
                    if($quantity !== '' && $quantity !== null){
                        // Determine the expiry date to save first (using DD/MM/YYYY format)
                        $expiry_to_save = null;
                        if(!empty($expiry_to_save_raw) && $expiry_to_save_raw !== 'N/A'){
                            // Try DD/MM/YYYY format first (European format)
                            $date_obj = DateTime::createFromFormat('d/m/Y', $expiry_to_save_raw);
                            if($date_obj !== false){
                                $expiry_to_save = $date_obj->format('Y-m-d');
                            } else {
                                // Fallback: try standard formats
                                $timestamp = strtotime($expiry_to_save_raw);
                                if($timestamp !== false && $timestamp > 0){
                                    $expiry_to_save = date('Y-m-d', $timestamp);
                                }
                            }
                        }
                        
                        // Delete existing record based on batch AND expiry (critical for uniqueness)
                        $this->db->where('inv_check_id', $inv_check_id);
                        $this->db->where('product_id', $product_id);
                        $this->db->where('batch_number', $batch_number);
                        $this->db->where('shelf', $shelf);
                        // Include expiry in the WHERE clause to differentiate records with same batch
                        if($expiry_to_save !== null){
                            $this->db->where('expiry_date', $expiry_to_save);
                        } else {
                            $this->db->where('(expiry_date IS NULL OR expiry_date = "")');
                        }
                        $this->db->delete('sma_inventory_check_items');
                        
                        // Insert new/updated record (batch and system_batch are the same now)
                        $record = [
                            'inv_check_id'        => $inv_check_id,
                            'product_id'          => $product_id,
                            'batch_number'        => $batch_to_save,
                            'expiry_date'         => $expiry_to_save,
                            'system_batch_number' => $batch_to_save,  // Same as batch_number
                            'system_expiry_date'  => $expiry_to_save, // Same as expiry_date
                            'quantity'            => $quantity,
                            'shelf'               => $shelf,
                            'user_id'             => $this->session->userdata('user_id')
                        ];
                        //echo '<pre>';   print_r($record); echo '</pre>';
                        $this->db->insert('sma_inventory_check_items', $record);
                        $count++;
                    }
                }

                if($count > 0){
                    $message = $count . ' item(s) saved successfully for inventory check.';
                    if($existing_request){
                        $message .= ' (Updated Request #' . $inv_check_id . ')';
                    } else {
                        $message .= ' (New Request #' . $inv_check_id . ')';
                    }
                    $this->session->set_flashdata('message', $message);
                } else {
                    $this->session->set_flashdata('warning', 'No quantities were entered.');
                }
            } else {
                $this->session->set_flashdata('error', 'No products found to save.');
            }

            // If close_shelf flag is set, mark shelf as closed
            if ($this->input->post('close_shelf') == '1' && isset($inv_check_id)) {
                $shelf = $this->input->post('shelf');
                $user_id = $this->session->userdata('user_id');
                // Check if already closed for this request/shelf
                $exists = $this->db->get_where('sma_inventory_check_closed_shelves', [
                    'inv_check_id' => $inv_check_id,
                    'shelf' => $shelf
                ])->row();
                if (!$exists) {
                    $this->db->insert('sma_inventory_check_closed_shelves', [
                        'inv_check_id' => $inv_check_id,
                        'shelf' => $shelf,
                        'user_id' => $user_id
                    ]);
                    $this->session->set_flashdata('message', 'Shelf "' . $shelf . '" closed for this inventory check.');
                } else {
                    $this->session->set_flashdata('warning', 'Shelf already closed for this inventory check.');
                }
            }
            admin_redirect('stock_request/inventory_check');
        } else {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect('stock_request/hills_inventory_check');
        }
    }
    
    /**
     * Close shelf without saving inventory data
     */
    public function close_shelf_only(){
        $warehouse_id = $this->input->post('warehouse_id');
        $shelf = $this->input->post('shelf');
        
        if(!$warehouse_id || !$shelf){
            $this->sma->send_json([
                'error' => 1,
                'msg' => 'Warehouse and shelf are required'
            ]);
            return;
        }
        
        // Get or create pending inventory check request for this warehouse
        $this->db->select('id');
        $this->db->from('sma_inventory_check_requests');
        $this->db->where('location_id', $warehouse_id);
        $this->db->where('status', 'pending');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $existing_request = $this->db->get()->row();
        
        if($existing_request){
            $inv_check_id = $existing_request->id;
        } else {
            // Create new inventory check request record
            $insert_inv_req = [
                'location_id' => $warehouse_id,
                'status' => 'pending',
                'date' => date('Y-m-d'),
            ];
            $this->db->insert('sma_inventory_check_requests', $insert_inv_req);
            $inv_check_id = $this->db->insert_id();
        }
        
        $user_id = $this->session->userdata('user_id');
        
        // Check if already closed for this request/shelf
        $exists = $this->db->get_where('sma_inventory_check_closed_shelves', [
            'inv_check_id' => $inv_check_id,
            'shelf' => $shelf
        ])->row();
        
        $response = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash()
        ];
        
        if (!$exists) {
            $this->db->insert('sma_inventory_check_closed_shelves', [
                'inv_check_id' => $inv_check_id,
                'shelf' => $shelf,
                'user_id' => $user_id
            ]);
            $response['error'] = 0;
            $response['msg'] = 'Shelf "' . $shelf . '" closed successfully.';
        } else {
            $response['error'] = 1;
            $response['msg'] = 'Shelf already closed for this inventory check.';
        }
        
        $this->sma->send_json($response);
    }
    
    /**
     * AJAX: Search products globally for adding/moving to a shelf (select2)
     */
    public function search_products(){
        // Accept term via GET or POST (Select2 may send either)
        $term = $this->input->get_post('term', true);
        $term = trim($term);

        if(!$term){
            $this->sma->send_json([ $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), 'results' => [] ]);
        }

        // Get warehouse_id if provided (for warehouse-specific shelf lookup)
        $warehouse_id = $this->input->get_post('warehouse_id');
        
        if($warehouse_id){
            $this->db->select('p.id, p.code as product_code, p.item_code, p.name as product_name, ps.shelf as warehouse_shelf');
            $this->db->from('sma_products p');
            $this->db->join('sma_product_shelves ps', 'ps.product_id = p.id AND ps.warehouse_id = ' . $this->db->escape($warehouse_id), 'left');
        } else {
            $this->db->select('p.id, p.code as product_code, p.item_code, p.name as product_name, "" as warehouse_shelf');
            $this->db->from('sma_products p');
        }
        
        $this->db->group_start();
        $this->db->like('p.name', $term);
        $this->db->or_like('p.code', $term);
        $this->db->or_like('p.item_code', $term);
        $this->db->group_end();
        $this->db->order_by('p.name', 'ASC');
        $this->db->limit(50);
        $query = $this->db->get();
        $products = $query->result();

        $results = [];
        foreach($products as $p){
            $text = ($p->product_code ? $p->product_code . ' - ' : '') . $p->product_name;
            if($p->warehouse_shelf){
                $text .= ' (Shelf: ' . $p->warehouse_shelf . ')';
            }
            $results[] = [
                'id' => $p->id,
                'text' => $text,
                'warehouse_shelf' => $p->warehouse_shelf,
                'product_code' => $p->product_code,
                'item_code' => $p->item_code,
                'product_name' => $p->product_name
            ];
        }

        $this->sma->send_json([ $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), 'results' => $results ]);
    }

    /**
     * AJAX: Get all products available in a warehouse (no search)
     */
    public function get_products_for_warehouse(){
        $warehouse_id = $this->input->post('warehouse_id');
        if(!$warehouse_id){
            $this->sma->send_json([ $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), 'error' => 1, 'msg' => 'Warehouse ID required' ]);
        }

        // Get ALL products, not just those with inventory records
        $this->db->select('p.id as product_id, p.code as product_code, p.item_code, p.name as product_name, ps.shelf as warehouse_shelf');
        $this->db->from('sma_products p');
        $this->db->join('sma_product_shelves ps', 'ps.product_id = p.id AND ps.warehouse_id = ' . $this->db->escape($warehouse_id), 'left');
        $this->db->order_by('p.name', 'ASC');
        $query = $this->db->get();
        $products = $query->result();

        $response = [ $this->security->get_csrf_token_name() => $this->security->get_csrf_hash() ];
        if($products){
            $response['error'] = 0;
            $response['products'] = $products;
            $this->sma->send_json($response);
        } else {
            $response['error'] = 1;
            $response['msg'] = 'No products found for this warehouse';
            $this->sma->send_json($response);
        }
    }

    /**
     * AJAX: Add or move a product to selected shelf and optionally create a pending inventory check item
     */
    public function add_move_product_to_shelf(){
        $product_id = $this->input->post('product_id');
        $warehouse_id = $this->input->post('warehouse_id');
        $shelf = $this->input->post('shelf');
        $batch_number = $this->input->post('batch_number');
        $expiry_date = $this->input->post('expiry_date');
        $quantity = $this->input->post('quantity');

        if(!$product_id || !$warehouse_id || !$shelf){
            $this->sma->send_json([ $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), 'error' => 1, 'msg' => 'Product, Warehouse and Shelf are required' ]);
        }

        // Validate quantity is provided for inventory check
        if($quantity === '' || $quantity === null){
            $this->sma->send_json([ $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(), 'error' => 1, 'msg' => 'Quantity is required' ]);
        }

        // Get or create pending inventory check request for this warehouse
        $this->db->select('id');
        $this->db->from('sma_inventory_check_requests');
        $this->db->where('location_id', $warehouse_id);
        $this->db->where('status', 'pending');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $existing_request = $this->db->get()->row();
        
        if($existing_request){
            $inv_check_id = $existing_request->id;
        } else {
            // Create new inventory check request
            $insert_inv_req = [
                'location_id' => $warehouse_id,
                'status' => 'pending',
                'date' => date('Y-m-d'),
            ];
            $this->db->insert('sma_inventory_check_requests', $insert_inv_req);
            $inv_check_id = $this->db->insert_id();
        }

        // Check if product exists in inventory_movements for this warehouse/batch/expiry
        // to get system batch/expiry info
        /*$this->db->select('batch_number, expiry_date, SUM(quantity) as system_qty');
        $this->db->from('sma_inventory_movements');
        $this->db->where('product_id', $product_id);
        $this->db->where('location_id', $warehouse_id);
        if($batch_number){
            $this->db->where('batch_number', $batch_number);
        }
        if($expiry_date){
            $this->db->where('expiry_date', $expiry_date);
        }
        $this->db->group_by('batch_number, expiry_date');
        $system_record = $this->db->get()->row();*/

        $this->db->select('batch_number, expiry_date, SUM(quantity) AS system_qty', false);
        $this->db->from('sma_inventory_movements');
        $this->db->where('product_id', $product_id);
        $this->db->where('location_id', $warehouse_id);

        if (!empty($batch_number)) {
            $this->db->where('batch_number', $batch_number);
        }

        if (!empty($expiry_date)) {
            $this->db->where('expiry_date', $expiry_date);
        }

        $this->db->group_by(['batch_number', 'expiry_date']);

        $query = $this->db->get();

        $system_record = ($query && $query->num_rows() > 0)
            ? $query->row()
            : null;

        // Determine batch/expiry to save
        $batch_to_save = $batch_number ?: ($system_record ? $system_record->batch_number : '');
        $expiry_to_save = null;
        if($expiry_date && $expiry_date !== 'N/A'){
            // Check if already in Y-m-d format (from database)
            if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry_date)){
                $expiry_to_save = $expiry_date;
            } else {
                // Parse date as DD/MM/YYYY format (European format)
                $date_obj = DateTime::createFromFormat('d/m/Y', $expiry_date);
                if($date_obj !== false){
                    $expiry_to_save = $date_obj->format('Y-m-d');
                } else {
                    // Fallback: try other common formats
                    $timestamp = strtotime($expiry_date);
                    if($timestamp !== false && $timestamp > 0){
                        $expiry_to_save = date('Y-m-d', $timestamp);
                    }
                }
            }
        } elseif($system_record && $system_record->expiry_date){
            $expiry_to_save = $system_record->expiry_date;
        }
        //echo 'Expiry To Save: '.$expiry_to_save;exit;
        
        // System batch/expiry (null if product doesn't exist in system for this shelf)
        $system_batch = $system_record ? $system_record->batch_number : null;
        $system_expiry = $system_record ? $system_record->expiry_date : null;

        // Delete existing entry if any (for re-adding) - MUST include expiry to avoid deleting other expiry dates
        $this->db->where('inv_check_id', $inv_check_id);
        $this->db->where('product_id', $product_id);
        $this->db->where('batch_number', $batch_to_save);
        $this->db->where('shelf', $shelf);
        // Include expiry in WHERE clause to differentiate records with same batch but different expiry
        if($expiry_to_save !== null){
            $this->db->where('expiry_date', $expiry_to_save);
        } else {
            $this->db->where('(expiry_date IS NULL OR expiry_date = "")');
        }
        $this->db->delete('sma_inventory_check_items');

        // Insert new inventory check item
        $record = [
            'inv_check_id'        => $inv_check_id,
            'product_id'          => $product_id,
            'batch_number'        => $batch_to_save,
            'expiry_date'         => $expiry_to_save,
            'system_batch_number' => $batch_to_save,
            'system_expiry_date'  => $expiry_to_save,
            'quantity'            => $quantity,
            'shelf'               => $shelf,
            'user_id'             => $this->session->userdata('user_id')
        ];
        
        $this->db->insert('sma_inventory_check_items', $record);

        // Fetch product details to return
        $this->db->select('p.id as product_id, p.code as product_code, p.item_code, p.name as product_name, ps.shelf as warehouse_shelf');
        $this->db->from('sma_products p');
        $this->db->join('sma_product_shelves ps', 'ps.product_id = p.id AND ps.warehouse_id = ' . $this->db->escape($warehouse_id), 'left');
        $this->db->where('p.id', $product_id);
        $product = $this->db->get()->row();

        $response = [
            $this->security->get_csrf_token_name() => $this->security->get_csrf_hash(),
            'error' => 0,
            'product' => $product,
            'batch_number' => $batch_to_save,
            'expiry_date' => $expiry_to_save ?? '', // Always return Y-m-d format
            'quantity' => $quantity,
            'system_quantity' => $system_record ? $system_record->system_qty : 0,
            'inv_check_id' => $inv_check_id,
            'msg' => 'Product added to inventory check for shelf ' . $shelf
        ];

        $this->sma->send_json($response);
    }

}