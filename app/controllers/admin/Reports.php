<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reports extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }

        $this->lang->admin_load('reports', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('reports_model');
        $this->load->admin_model('companies_model');
        $this->data['pb'] = [
            'cash' => lang('cash'),
            'CC' => lang('CC'),
            'Cheque' => lang('Cheque'),
            'paypal_pro' => lang('paypal_pro'),
            'stripe' => lang('stripe'),
            'gift_card' => lang('gift_card'),
            'deposit' => lang('deposit'),
            'authorize' => lang('authorize'),
        ];

        $this->load->admin_model('deals_model');
        $this->load->admin_model('pos_model');
    }
    public function adjustments($warehouse_id = null)
    {
        $this->sma->checkPermissions('products');

        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('adjustments_report')]];
        $meta = ['page_title' => lang('adjustments_report'), 'bc' => $bc];
        $this->page_construct('reports/adjustments', $meta, $this->data);
    }

    public function best_sellers($warehouse_id = null)
    {
        $this->sma->checkPermissions('products');

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $y1 = date('Y', strtotime('-1 month'));
        $m1 = date('m', strtotime('-1 month'));
        $m1sdate = $y1 . '-' . $m1 . '-01 00:00:00';
        $m1edate = $y1 . '-' . $m1 . '-' . days_in_month($m1, $y1) . ' 23:59:59';
        $this->data['m1'] = date('M Y', strtotime($y1 . '-' . $m1));
        $this->data['m1bs'] = $this->reports_model->getBestSeller($m1sdate, $m1edate, $warehouse_id);
        $y2 = date('Y', strtotime('-2 months'));
        $m2 = date('m', strtotime('-2 months'));
        $m2sdate = $y2 . '-' . $m2 . '-01 00:00:00';
        $m2edate = $y2 . '-' . $m2 . '-' . days_in_month($m2, $y2) . ' 23:59:59';
        $this->data['m2'] = date('M Y', strtotime($y2 . '-' . $m2));
        $this->data['m2bs'] = $this->reports_model->getBestSeller($m2sdate, $m2edate, $warehouse_id);
        $y3 = date('Y', strtotime('-3 months'));
        $m3 = date('m', strtotime('-3 months'));
        $m3sdate = $y3 . '-' . $m3 . '-01 23:59:59';
        $this->data['m3'] = date('M Y', strtotime($y3 . '-' . $m3)) . ' - ' . $this->data['m1'];
        $this->data['m3bs'] = $this->reports_model->getBestSeller($m3sdate, $m1edate, $warehouse_id);
        $y4 = date('Y', strtotime('-12 months'));
        $m4 = date('m', strtotime('-12 months'));
        $m4sdate = $y4 . '-' . $m4 . '-01 23:59:59';
        $this->data['m4'] = date('M Y', strtotime($y4 . '-' . $m4)) . ' - ' . $this->data['m1'];
        $this->data['m4bs'] = $this->reports_model->getBestSeller($m4sdate, $m1edate, $warehouse_id);
        // $this->sma->print_arrays($this->data['m1bs'], $this->data['m2bs'], $this->data['m3bs'], $this->data['m4bs']);
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('best_sellers')]];
        $meta = ['page_title' => lang('best_sellers'), 'bc' => $bc];
        $this->page_construct('reports/best_sellers', $meta, $this->data);
    }

    public function brands()
    {
        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
        } else {
            $dt = 'Till ' . $this->input->post('end_date');
        }
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('brands_report')]];
        $meta = ['page_title' => lang('brands_report'), 'bc' => $bc];
        $this->page_construct('reports/brands', $meta, $this->data);
    }

    public function categories()
    {
        $this->sma->checkPermissions('products');
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
        } else {
            $dt = 'Till ' . $this->input->post('end_date');
        }
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('categories_report')]];
        $meta = ['page_title' => lang('categories_report'), 'bc' => $bc];
        $this->page_construct('reports/categories', $meta, $this->data);
    }

    public function customer_report($user_id = null)
    {
        $this->sma->checkPermissions('customers', true);
        if (!$user_id) {
            $this->session->set_flashdata('error', lang('no_customer_selected'));
            admin_redirect('reports/customers');
        }
        $this->data['sales'] = $this->reports_model->getSalesTotals($user_id);
        $this->data['total_sales'] = $this->reports_model->getCustomerSales($user_id);
        $this->data['total_quotes'] = $this->reports_model->getCustomerQuotes($user_id);
        $this->data['total_returns'] = $this->reports_model->getCustomerReturns($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $this->data['user_id'] = $user_id;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('customers_report')]];
        $meta = ['page_title' => lang('customers_report'), 'bc' => $bc];
        $this->page_construct('reports/customer_report', $meta, $this->data);
    }

    public function daily_purchase_report(){
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $supplier = $this->input->post('supplier') ? $this->input->post('supplier') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;

        if ($from_date || $to_date || $supplier) {
            $response_arr = $this->reports_model->get_daily_purchase($supplier, $from_date, $to_date);
        }

        $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
        $this->data['start_date'] = $from_date;
        $this->data['end_date'] = $to_date;
        $this->data['supplier_id'] = $supplier;
        $this->data['daily_purchase_data'] = $response_arr;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('daily_purchase_report')]];
        $meta = ['page_title' => lang('daily_purchase_report'), 'bc' => $bc];
        if ($viewtype == 'pdf') {
            $this->data['viewtype'] = $viewtype;
            $name = lang('daily_purchase_report') . '.pdf';
            $html = $this->load->view($this->theme . 'reports/daily_purchase_report', $this->data, true);
            $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
        } else {
            $this->page_construct('reports/daily_purchase_report', $meta, $this->data);
        }
    }

    public function total_income(){
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $supplier = $this->input->post('supplier') != 'All'? $this->input->post('supplier') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;

        if ($from_date || $to_date || $supplier) {
            $response_arr = $this->reports_model->get_total_income($supplier, $from_date, $to_date);
        }

        $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
        $this->data['start_date'] = $from_date;
        $this->data['end_date'] = $to_date;
        $this->data['supplier_id'] = $supplier;
        $this->data['income_data'] = $response_arr;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('total_income_report')]];
        $meta = ['page_title' => lang('total_income_report'), 'bc' => $bc];
        if ($viewtype == 'pdf') {
            $this->data['viewtype'] = $viewtype;
            $name = lang('total_income_report') . '.pdf';
            $html = $this->load->view($this->theme . 'reports/total_income_report', $this->data, true);
            $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
        } else {
            $this->page_construct('reports/total_income_report', $meta, $this->data);
        }
    }

    public function supplier_stock(){
        $response_arr = array();
        $supplier = $this->input->post('supplier') ? $this->input->post('supplier') : '';
        $warehouse = $this->input->post('warehouse') ? $this->input->post('warehouse') : '';
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : '';
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : '';

        if (isset($_POST['submit'])) {
            $response_arr = $this->reports_model->getSupplierStockData($supplier, $warehouse, $from_date, $to_date);
            //echo '<pre>';print_r($response_arr);exit;
        }

        $this->data['warehouse_id'] = $warehouse;
        $this->data['supplier_id'] = $supplier;
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
        $this->data['start_date'] = $from_date;
        $this->data['end_date'] = $to_date;
        $this->data['stock_data'] = $response_arr;

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('reports'), 'page' => lang('reports')],
            ['link' => '#', 'page' => lang('supplier_stock_report')]
        ];

        $meta = [
            'page_title' => lang('supplier_stock_report'),
            'bc' => $bc
        ];
        $this->page_construct('reports/supplier_stock', $meta, $this->data);
    }

    public function pharmacy_stock()
    {
        $item = $this->input->post('item') ? $this->input->post('item') : null;

        if (isset($_POST['submit'])) {
            $rows = $this->reports_model->getPharmacyStockData($item);

        } else {
            $rows = $this->reports_model->getPharmacyStockData();
        }

        $this->data['warehouses'] = $this->site->getAllWarehouses();

        foreach ($rows as $row) {
            $productId = $row->id;
            $productCode = $row->item_code;
            $productName = $row->name;
            $warehouseName = $row->warehouse_name;
            $batchNo = $row->batch_no;
            $quantity = $row->quantity;
            $expiry = $row->expiry;

            // Check if the product is already in the organized array
            if (!isset($organizedResults[$productId . $batchNo])) {
                // If not, initialize the product information
                $organizedResults[$productId . $batchNo] = [
                    'id' => $productId,
                    'product_name' => $productName,
                    'product_code' => $productCode,
                    'warehouses' => [],
                ];

                foreach ($this->data['warehouses'] as $ware_house) {
                    $organizedResults[$productId . $batchNo]['warehouses'][] = [
                        'warehouse_name' => $ware_house->name,
                        'batch_no' => '-',
                        'quantity' => '-',
                        'expiry' => '-'
                    ];
                }
            }

            foreach ($organizedResults[$productId . $batchNo]['warehouses'] as $key => $ware_house) {
                if ($ware_house['warehouse_name'] == $warehouseName) {
                    $organizedResults[$productId . $batchNo]['warehouses'][$key]['batch_no'] = $batchNo;
                    $organizedResults[$productId . $batchNo]['warehouses'][$key]['quantity'] = $quantity;
                    $organizedResults[$productId . $batchNo]['warehouses'][$key]['expiry'] = $expiry;
                }
            }

        }

        $this->data['stock_data'] = $organizedResults;

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('reports'), 'page' => lang('reports')],
            ['link' => '#', 'page' => lang('pharmacy_stock_report')]
        ];

        $meta = [
            'page_title' => lang('pharmacy_stock_report'),
            'bc' => $bc
        ];
        $this->page_construct('reports/pharmacy_stock', $meta, $this->data);
    }

    public function stock_export_excel(){
        $data = array();
        $at_date = $this->input->get('at_date') ? $this->input->get('at_date') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $item_group = $this->input->get('item_group') ? $this->input->get('item_group') : null;
        $item = $this->input->get('item') ? $this->input->get('item') : null;
        $filterOnType = $this->input->get('filterOnType') ? $this->input->get('filterOnType') : null;

        $data = $this->reports_model->getStockData($at_date, $warehouse, $item_group, $filterOnType, $item);

        if (!empty($data)) {
            $this->load->library('excel');
            $this->excel->setActiveSheetIndex(0);
            $this->excel->getActiveSheet()->setTitle(lang('stock_report'));
            $this->excel->getActiveSheet()->SetCellValue('A1', lang('Item Code'));
            $this->excel->getActiveSheet()->SetCellValue('B1', lang('Avz Code'));
            $this->excel->getActiveSheet()->SetCellValue('C1', lang('Item Name'));
            $this->excel->getActiveSheet()->SetCellValue('D1', lang('Batch'));
            $this->excel->getActiveSheet()->SetCellValue('E1', lang('Expiry'));
            $this->excel->getActiveSheet()->SetCellValue('F1', lang('Quantity'));
            $this->excel->getActiveSheet()->SetCellValue('G1', lang('Sale Price'));
            $this->excel->getActiveSheet()->SetCellValue('H1', lang('Total Sale'));
            $this->excel->getActiveSheet()->SetCellValue('I1', lang('Purchase Price'));
            $this->excel->getActiveSheet()->SetCellValue('J1', lang('Total Purchase'));
            $this->excel->getActiveSheet()->SetCellValue('K1', lang('Cost Price'));
            $this->excel->getActiveSheet()->SetCellValue('L1', lang('Total Cost'));

            $row = 2;
            $total_quantity = $total_sale = $total_purchase = $total_cost = 0;
            foreach ($data as $data_row) {
                $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->item_code);
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->avz_item_code);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->name);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->batch_no);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->expiry);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->quantity);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, ($data_row->sale_price));
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->sale_price * $data_row->quantity));
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, ($data_row->purchase_price));
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, ($data_row->purchase_price * $data_row->quantity));
                $this->excel->getActiveSheet()->SetCellValue('K' . $row, ($data_row->cost_price));
                $this->excel->getActiveSheet()->SetCellValue('L' . $row, ($data_row->cost_price * $data_row->quantity));
                
                $total_quantity += $data_row->quantity;
                $row++;
            }
            $this->excel->getActiveSheet()->getStyle('E' . $row . ':J' . $row)->getBorders()
                ->getTop()->setBorderStyle('medium');
            //$this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->formatDecimal($igst));
            //$this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->sma->formatDecimal($cgst));
            //$this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->sma->formatDecimal($sgst));
            //$this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->sma->formatDecimal($product_tax));
            //$this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->sma->formatDecimal($order_tax));
            //$this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->sma->formatDecimal($total));

            //$this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            //$this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            //$this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            //$this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            //$this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
            //$this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
            //$this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
            //$this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
            //$this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
            $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
            $filename = 'stock_report';
            $this->load->helper('excel');
            create_excel($this->excel, $filename);
        }
    }

    public function stock()
    {
        $at_date = $this->input->get('at_date') ? $this->input->get('at_date') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        //$supplier = $this->input->post('supplier') ? $this->input->post('supplier') : null;
        $item_group = $this->input->get('item_group') ? $this->input->get('item_group') : null;
        $item = $this->input->get('item') ? $this->input->get('item') : null;
        $filterOnType = $this->input->get('filterOnType') ? $this->input->get('filterOnType') : null;
        $viewtype = $this->input->get('viewtype') ? $this->input->get('viewtype') : null;

        $filterOnTypeArr = [
            "" => "-- ALL --",
            "purchase" => "Purchases",
            "sale" => "Sales",
            "pos" => "Pos",
            "customer_return" => "Return Customer",
            "return_to_supplier" => "Return Supplier",
            "transfer_in" => "Transfer In",
            "transfer_out" => "Transfer Out"
        ];
        $this->data['filterOnTypeArr'] = $filterOnTypeArr;
        $this->data['filterOnType'] = $filterOnType;

        if (isset($_GET['submit'])) {
            $this->load->library('pagination'); 
            $config['per_page'] = 100; 
            $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
            $this->data['stock_data'] = $this->reports_model->getStockData($at_date, $warehouse, $item_group, $filterOnType, $item, $page, $config['per_page']);
            $this->data['stock_data_totals'] = $this->reports_model->getStockDataTotals($at_date, $warehouse, $item_group, $filterOnType, $item);
            $grand = $this->reports_model->getStockDataGrandTotals($at_date, $warehouse, $item_group, $filterOnType, $item);
            $this->data['new_grand_total'] = $grand[0];
            $this->data['offset'] = $page;

            $config['base_url'] = admin_url('reports/stock');
            $config['total_rows'] = sizeof($this->data['stock_data_totals']);
            $config['reuse_query_string'] = TRUE;
            $this->pagination->initialize($config); 
            $this->data['pagination_links']=  $this->pagination->create_links();
        } else {
            $this->data['stock_data'] = [];
        }

        $this->data['at_date'] = $at_date;

        $this->data['warehouses'] = $this->site->getAllWarehouses();
        //$this->data['suppliers'] = $this->deals_model->getAllSuppliersList();
        $this->data['categories'] = $this->site->getAllCategories();
        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('reports'), 'page' => lang('reports')],
            ['link' => '#', 'page' => lang('stock_report')]
        ];

        $meta = [
            'page_title' => lang('stock_report'),
            'bc' => $bc
        ];
        if ($viewtype == 'pdf') {
            $this->data['viewtype'] = $viewtype;
            $name = lang('stock') . '.pdf';
            $html = $this->load->view($this->theme . 'reports/stock', $this->data, true);
            $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
        } else {
            $this->page_construct('reports/stock', $meta, $this->data);
        }

    }

    public function getStock()
    {
        //        $this->sma->checkPermissions('customers', true);

        $this->load->library('datatables');
        $this->datatables
            ->select('p.`id` AS `id`,
                        p.`code` AS `code`,
                        p.`cost` AS `item_cost`,
                        p.`price` AS `sale_price`,
                        p.`name` AS `name`,
                        wp.`quantity` AS `quantity`,
                        wp.`batchno` AS `batchno`,
                        wp.`expiry` AS `expiry`,
                        wp.`purchase_cost`', false)
            ->from('sma_products p')
            ->join('sma_warehouses_products wp', ' wp.`product_id` = p.`id`', 'left')
            ->where('wp.warehouse_id', 32)
            ->where('p.`type`', 'standard');
        //            ->group_by('companies.id')
//            ->add_column('Actions', "<div class='text-center'><a class=\"tip\" title='" . lang('view_report') . "' href='" . admin_url('reports/customer_report/$1') . "'><span class='label label-primary'>" . lang('view_report') . '</span></a></div>', 'id')
//            ->unset_column('id');
        echo $this->datatables->generate();
    }

    public function customers()
    {
        $this->sma->checkPermissions('customers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('customers_report')]];
        $meta = ['page_title' => lang('customers_report'), 'bc' => $bc];
        $this->page_construct('reports/customers', $meta, $this->data);
    }

    public function daily_purchases($warehouse_id = null, $year = null, $month = null, $pdf = null, $user_id = null)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $config = [
            'show_next_prev' => true,
            'next_prev_url' => admin_url('reports/daily_purchases/' . ($warehouse_id ? $warehouse_id : 0)),
            'month_type' => 'long',
            'day_type' => 'long',
        ];

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table print-table table-bordered dfTable">{/table_open}
        {heading_row_start}<tr>{/heading_row_start}
        {heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
        {heading_title_cell}<th colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
        {heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
        {heading_row_end}</tr>{/heading_row_end}
        {week_row_start}<tr>{/week_row_start}
        {week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
        {week_row_end}</tr>{/week_row_end}
        {cal_row_start}<tr class="days">{/cal_row_start}
        {cal_cell_start}<td class="day">{/cal_cell_start}
        {cal_cell_content}
        <div class="day_num">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content}
        {cal_cell_content_today}
        <div class="day_num highlight">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content_today}
        {cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
        {cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
        {cal_cell_blank}&nbsp;{/cal_cell_blank}
        {cal_cell_end}</td>{/cal_cell_end}
        {cal_row_end}</tr>{/cal_row_end}
        {table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $purchases = $user_id ? $this->reports_model->getStaffDailyPurchases($user_id, $year, $month, $warehouse_id) : $this->reports_model->getDailyPurchases($year, $month, $warehouse_id);

        if (!empty($purchases)) {
            foreach ($purchases as $purchase) {
                $daily_purchase[$purchase->date] = "<table class='table print-table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang('discount') . '</td><td>' . $this->sma->formatMoney($purchase->discount) . '</td></tr><tr><td>' . lang('shipping') . '</td><td>' . $this->sma->formatMoney($purchase->shipping) . '</td></tr><tr><td>' . lang('product_tax') . '</td><td>' . $this->sma->formatMoney($purchase->tax1) . '</td></tr><tr><td>' . lang('order_tax') . '</td><td>' . $this->sma->formatMoney($purchase->tax2) . '</td></tr><tr><td>' . lang('total') . '</td><td>' . $this->sma->formatMoney($purchase->total) . '</td></tr></table>';
            }
        } else {
            $daily_purchase = [];
        }

        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_purchase);
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/daily', $this->data, true);
            $name = lang('daily_purchases') . '_' . $year . '_' . $month . '.pdf';
            $html = str_replace('<p class="introtext">' . lang('reports_calendar_text') . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('daily_purchases_report')]];
        $meta = ['page_title' => lang('daily_purchases_report'), 'bc' => $bc];
        $this->page_construct('reports/daily_purchases', $meta, $this->data);
    }

    public function daily_sales($warehouse_id = null, $year = null, $month = null, $pdf = null, $user_id = null)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$month) {
            $month = date('m');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $config = [
            'show_next_prev' => true,
            'next_prev_url' => admin_url('reports/daily_sales/' . ($warehouse_id ? $warehouse_id : 0)),
            'month_type' => 'long',
            'day_type' => 'long',
        ];

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table print-table table-bordered dfTable">{/table_open}
        {heading_row_start}<tr>{/heading_row_start}
        {heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
        {heading_title_cell}<th colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
        {heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
        {heading_row_end}</tr>{/heading_row_end}
        {week_row_start}<tr>{/week_row_start}
        {week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
        {week_row_end}</tr>{/week_row_end}
        {cal_row_start}<tr class="days">{/cal_row_start}
        {cal_cell_start}<td class="day">{/cal_cell_start}
        {cal_cell_content}
        <div class="day_num">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content}
        {cal_cell_content_today}
        <div class="day_num highlight">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content_today}
        {cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
        {cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
        {cal_cell_blank}&nbsp;{/cal_cell_blank}
        {cal_cell_end}</td>{/cal_cell_end}
        {cal_row_end}</tr>{/cal_row_end}
        {table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $sales = $user_id ? $this->reports_model->getStaffDailySales($user_id, $year, $month, $warehouse_id) : $this->reports_model->getDailySales($year, $month, $warehouse_id);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $daily_sale[$sale->date] = "<table class='table print-table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang('discount') . '</td><td>' . $this->sma->formatMoney($sale->discount) . '</td></tr><tr><td>' . lang('shipping') . '</td><td>' . $this->sma->formatMoney($sale->shipping) . '</td></tr><tr><td>' . lang('product_tax') . '</td><td>' . $this->sma->formatMoney($sale->tax1) . '</td></tr><tr><td>' . lang('order_tax') . '</td><td>' . $this->sma->formatMoney($sale->tax2) . '</td></tr><tr><td>' . lang('total') . '</td><td>' . $this->sma->formatMoney($sale->total) . '</td></tr></table>';
            }
        } else {
            $daily_sale = [];
        }

        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/daily', $this->data, true);
            $name = lang('daily_sales') . '_' . $year . '_' . $month . '.pdf';
            $html = str_replace('<p class="introtext">' . lang('reports_calendar_text') . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('daily_sales_report')]];
        $meta = ['page_title' => lang('daily_sales_report'), 'bc' => $bc];
        $this->page_construct('reports/daily', $meta, $this->data);
    }

    public function expenses($id = null)
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['categories'] = $this->reports_model->getExpenseCategories();
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('expenses')]];
        $meta = ['page_title' => lang('expenses'), 'bc' => $bc];
        $this->page_construct('reports/expenses', $meta, $this->data);
    }

    public function expiry_alerts($warehouse_id = null)
    {
        $this->sma->checkPermissions('expiry_alerts');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : null;
        }

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('product_expiry_alerts')]];
        $meta = ['page_title' => lang('product_expiry_alerts'), 'bc' => $bc];
        $this->page_construct('reports/expiry_alerts', $meta, $this->data);
    }

    public function get_deposits($company_id = null)
    {
        $this->sma->checkPermissions('customers', true);
        $this->load->library('datatables');
        $this->datatables
            ->select("date, amount, paid_by, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note", false)
            ->from('deposits')
            ->join('users', 'users.id=deposits.created_by', 'left')
            ->where($this->db->dbprefix('deposits') . '.company_id', $company_id);
        echo $this->datatables->generate();
    }

    public function get_purchase_taxes($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('tax', true);
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        if ($pdf || $xls) {
            $this->db
                ->select("date, reference_no, CONCAT({$this->db->dbprefix('warehouses')}.name, ' (', {$this->db->dbprefix('warehouses')}.code, ')') as warehouse, supplier, igst, cgst, sgst, product_tax, order_tax, grand_total, paid")
                ->from('purchases')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                ->order_by('purchases.date desc');

            if ($supplier) {
                $this->db->where('supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->db->where('warehouse_id', $warehouse);
            }
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('igst'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('cgst'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('sgst'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('product_tax'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('order_tax'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('grand_total'));

                $row = 2;
                $total = $order_tax = $product_tax = $igst = $cgst = $sgst = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->warehouse);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->supplier);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->formatDecimal($data_row->igst));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->sma->formatDecimal($data_row->cgst));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->sma->formatDecimal($data_row->sgst));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->sma->formatDecimal($data_row->product_tax));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->sma->formatDecimal($data_row->order_tax));
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->sma->formatDecimal($data_row->grand_total));
                    $igst += $data_row->igst;
                    $cgst += $data_row->cgst;
                    $sgst += $data_row->sgst;
                    $product_tax += $data_row->product_tax;
                    $order_tax += $data_row->order_tax;
                    $total += $data_row->grand_total;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('E' . $row . ':J' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->formatDecimal($igst));
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->sma->formatDecimal($cgst));
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->sma->formatDecimal($sgst));
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->sma->formatDecimal($product_tax));
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->sma->formatDecimal($order_tax));
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->sma->formatDecimal($total));

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'purchase_tax_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, status, CONCAT({$this->db->dbprefix('warehouses')}.name, ' (', {$this->db->dbprefix('warehouses')}.code, ')') as warehouse, supplier, " . ($this->Settings->indian_gst ? 'igst, cgst, sgst,' : '') . " product_tax, order_tax, grand_total, {$this->db->dbprefix('purchases')}.id as id", false)
                ->from('purchases')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
            if ($supplier) {
                $this->datatables->where('supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->datatables->where('warehouse_id', $warehouse);
            }
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function get_sale_taxes($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('tax', true);
        $biller = $this->input->get('biller') ? $this->input->get('biller') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        if ($pdf || $xls) {
            $this->db
                ->select("date, reference_no, CONCAT({$this->db->dbprefix('warehouses')}.name, ' (', {$this->db->dbprefix('warehouses')}.code, ')') as warehouse, biller, igst, cgst, sgst, product_tax, order_tax, grand_total, paid, payment_status")
                ->from('sales')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->order_by('date desc');

            if ($biller) {
                $this->db->where('biller_id', $biller);
            }
            if ($warehouse) {
                $this->db->where('warehouse_id', $warehouse);
            }
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('igst'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('cgst'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('sgst'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('product_tax'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('order_tax'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('grand_total'));

                $row = 2;
                $total = $order_tax = $product_tax = $igst = $cgst = $sgst = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->warehouse);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->formatDecimal($data_row->igst));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->sma->formatDecimal($data_row->cgst));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->sma->formatDecimal($data_row->sgst));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->sma->formatDecimal($data_row->product_tax));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->sma->formatDecimal($data_row->order_tax));
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->sma->formatDecimal($data_row->grand_total));
                    $igst += $data_row->igst;
                    $cgst += $data_row->cgst;
                    $sgst += $data_row->sgst;
                    $product_tax += $data_row->product_tax;
                    $order_tax += $data_row->order_tax;
                    $total += $data_row->grand_total;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('E' . $row . ':J' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->formatDecimal($igst));
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->sma->formatDecimal($cgst));
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->sma->formatDecimal($sgst));
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->sma->formatDecimal($product_tax));
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->sma->formatDecimal($order_tax));
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->sma->formatDecimal($total));

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'sale_tax_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, sale_status, CONCAT({$this->db->dbprefix('warehouses')}.name, ' (', {$this->db->dbprefix('warehouses')}.code, ')') as warehouse, biller, " . ($this->Settings->indian_gst ? 'igst, cgst, sgst,' : '') . " product_tax, order_tax, grand_total, {$this->db->dbprefix('sales')}.id as id", false)
                ->from('sales')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
            if ($biller) {
                $this->datatables->where('biller_id', $biller);
            }
            if ($warehouse) {
                $this->datatables->where('warehouse_id', $warehouse);
            }
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function getAdjustmentReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('products', true);

        $product = $this->input->get('product') ? $this->input->get('product') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $user = $this->input->get('user') ? $this->input->get('user') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : null;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {
            $ai = "( SELECT adjustment_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('products')}.name, ' (', (CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END), ')') SEPARATOR '\n') as item_nane from {$this->db->dbprefix('adjustment_items')} LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id={$this->db->dbprefix('adjustment_items')}.product_id GROUP BY {$this->db->dbprefix('adjustment_items')}.adjustment_id ) FAI";

            $this->db->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note, FAI.item_nane as iname, {$this->db->dbprefix('adjustments')}.id as id", false)
                ->from('adjustments')
                ->join($ai, 'FAI.adjustment_id=adjustments.id', 'left')
                ->join('users', 'users.id=adjustments.created_by', 'left')
                ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');

            if ($user) {
                $this->db->where('adjustments.created_by', $user);
            }
            if ($product) {
                $this->db->where('FAI.product_id', $product);
            }
            if ($serial) {
                $this->db->like('FAI.serial_no', $serial);
            }
            if ($warehouse) {
                $this->db->where('adjustments.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('adjustments.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('adjustments') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('adjustments_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('created_by'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('products'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->wh_name);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->created_by);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->decode_html($data_row->note));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->iname);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $this->excel->getActiveSheet()->getStyle('F2:F' . $row)->getAlignment()->setWrapText(true);
                $filename = 'adjustments_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $ai = "( SELECT adjustment_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('products')}.name, '__', (CASE WHEN {$this->db->dbprefix('adjustment_items')}.type  = 'subtraction' THEN (0-{$this->db->dbprefix('adjustment_items')}.quantity) ELSE {$this->db->dbprefix('adjustment_items')}.quantity END)) SEPARATOR '___') as item_nane from {$this->db->dbprefix('adjustment_items')} LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id={$this->db->dbprefix('adjustment_items')}.product_id ";
            if ($product || $serial) {
                $ai .= ' WHERE ';
            }
            if ($product) {
                $ai .= " {$this->db->dbprefix('adjustment_items')}.product_id = {$product} ";
            }
            if ($product && $serial) {
                $ai .= ' AND ';
            }
            if ($serial) {
                $ai .= " {$this->db->dbprefix('adjustment_items')}.serial_no LIKe '%{$serial}%' ";
            }
            $ai .= " GROUP BY {$this->db->dbprefix('adjustment_items')}.adjustment_id ) FAI";
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, warehouses.name as wh_name, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as created_by, note, FAI.item_nane as iname, {$this->db->dbprefix('adjustments')}.id as id", false)
                ->from('adjustments')
                ->join($ai, 'FAI.adjustment_id=adjustments.id', 'left')
                ->join('users', 'users.id=adjustments.created_by', 'left')
                ->join('warehouses', 'warehouses.id=adjustments.warehouse_id', 'left');

            if ($user) {
                $this->datatables->where('adjustments.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FAI.product_id', $product);
            }
            if ($serial) {
                $this->datatables->like('FAI.serial_no', $serial);
            }
            if ($warehouse) {
                $this->datatables->where('adjustments.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('adjustments.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('adjustments') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function getBrandsReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('products', true);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $pp = "( SELECT pp.brand as brand, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . ' pi ON pp.id = pi.product_id
                left join ' . $this->db->dbprefix('purchases') . ' p ON p.id = pi.purchase_id ';
        $sp = "( SELECT sp.brand as brand, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . ' si ON sp.id = si.product_id
                left join ' . $this->db->dbprefix('sales') . ' s ON s.id = si.sale_id ';
        if ($start_date || $warehouse) {
            $pp .= ' WHERE ';
            $sp .= ' WHERE ';
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= ' AND ';
                    $sp .= ' AND ';
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= ' GROUP BY pp.brand ) PCosts';
        $sp .= ' GROUP BY sp.brand ) PSales';

        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('brands') . '.name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit', false)
                ->from('brands')
                ->join($sp, 'brands.id = PSales.brand', 'left')
                ->join($pp, 'brands.id = PCosts.brand', 'left')
                ->group_by('brands.id, brands.name')
                ->order_by('brands.code', 'asc');

            if ($brand) {
                $this->db->where($this->db->dbprefix('brands') . '.id', $brand);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('brands_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('brands'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('profit_loss'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $profit);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl += $profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('B' . $row . ':F' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'brands_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('brands') . '.id as id, ' . $this->db->dbprefix('brands') . '.name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit', false)
                ->from('brands')
                ->join($sp, 'brands.id = PSales.brand', 'left')
                ->join($pp, 'brands.id = PCosts.brand', 'left');

            if ($brand) {
                $this->datatables->where('brands.id', $brand);
            }
            $this->datatables->group_by('brands.id, brands.name, PSales.SoldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase');
            $this->datatables->unset_column('id');
            echo $this->datatables->generate();
        }
    }

    public function getCategoriesReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('products', true);
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $category = $this->input->get('category') ? $this->input->get('category') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $pp = "( SELECT pp.category_id as category, SUM( pi.quantity ) purchasedQty, SUM( pi.subtotal ) totalPurchase from {$this->db->dbprefix('products')} pp
                left JOIN " . $this->db->dbprefix('purchase_items') . ' pi ON pp.id = pi.product_id
                left join ' . $this->db->dbprefix('purchases') . ' p ON p.id = pi.purchase_id ';
        $sp = "( SELECT sp.category_id as category, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from {$this->db->dbprefix('products')} sp
                left JOIN " . $this->db->dbprefix('sale_items') . ' si ON sp.id = si.product_id
                left join ' . $this->db->dbprefix('sales') . ' s ON s.id = si.sale_id ';
        if ($start_date || $warehouse) {
            $pp .= ' WHERE ';
            $sp .= ' WHERE ';
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " p.date >= '{$start_date}' AND p.date < '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date < '{$end_date}' ";
                if ($warehouse) {
                    $pp .= ' AND ';
                    $sp .= ' AND ';
                }
            }
            if ($warehouse) {
                $pp .= " pi.warehouse_id = '{$warehouse}' ";
                $sp .= " si.warehouse_id = '{$warehouse}' ";
            }
        }
        $pp .= ' GROUP BY pp.category_id ) PCosts';
        $sp .= ' GROUP BY sp.category_id ) PSales';

        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('categories') . '.code, ' . $this->db->dbprefix('categories') . '.name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit', false)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left')
                ->group_start()->where('parent_id is NULL', null, false)->or_where('parent_id', 0)->group_end()
                ->group_by('categories.id, categories.code, categories.name')
                ->order_by('categories.code', 'asc');

            if ($category) {
                $this->db->where($this->db->dbprefix('categories') . '.id', $category);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('categories_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('category_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('category_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('profit_loss'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $profit = $data_row->TotalSales - $data_row->TotalPurchase;
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $profit);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $pl += $profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('C' . $row . ':G' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $pl);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'categories_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('categories') . '.id as cid, ' . $this->db->dbprefix('categories') . '.code, ' . $this->db->dbprefix('categories') . '.name,
                    SUM( COALESCE( PCosts.purchasedQty, 0 ) ) as PurchasedQty,
                    SUM( COALESCE( PSales.soldQty, 0 ) ) as SoldQty,
                    SUM( COALESCE( PCosts.totalPurchase, 0 ) ) as TotalPurchase,
                    SUM( COALESCE( PSales.totalSale, 0 ) ) as TotalSales,
                    (SUM( COALESCE( PSales.totalSale, 0 ) )- SUM( COALESCE( PCosts.totalPurchase, 0 ) ) ) as Profit', false)
                ->from('categories')
                ->join($sp, 'categories.id = PSales.category', 'left')
                ->join($pp, 'categories.id = PCosts.category', 'left')
                ->group_start()->where('parent_id is NULL', null, false)->or_where('parent_id', 0)->group_end();

            if ($category) {
                $this->datatables->where('categories.id', $category);
            }
            $this->datatables->group_by('categories.id, categories.code, categories.name, PSales.SoldQty, PSales.totalSale, PCosts.purchasedQty, PCosts.totalPurchase');
            $this->datatables->unset_column('cid');
            echo $this->datatables->generate();
        }
    }

    public function getCustomerLogins($id = null)
    {
        if ($this->input->get('login_start_date')) {
            $login_start_date = $this->input->get('login_start_date');
        } else {
            $login_start_date = null;
        }
        if ($this->input->get('login_end_date')) {
            $login_end_date = $this->input->get('login_end_date');
        } else {
            $login_end_date = null;
        }
        if ($login_start_date) {
            $login_start_date = $this->sma->fld($login_start_date);
            $login_end_date = $login_end_date ? $this->sma->fld($login_end_date) : date('Y-m-d H:i:s');
        }
        $this->load->library('datatables');
        $this->datatables
            ->select('login, ip_address, time')
            ->from('user_logins')
            ->where('customer_id', $id);
        if ($login_start_date) {
            $this->datatables->where('time BETWEEN "' . $login_start_date . '" and "' . $login_end_date . '"');
        }
        echo $this->datatables->generate();
    }

    public function getCustomers($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('customers', true);

        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('companies') . '.id as id, company, name, phone, email, count(' . $this->db->dbprefix('sales') . '.id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance', false)
                ->from('companies')
                ->join('sales', 'sales.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
                ->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('customers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_sales'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->total);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->sma->formatMoney($data_row->total_amount));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->sma->formatMoney($data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->sma->formatMoney($data_row->balance));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $filename = 'customers_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $s = '( SELECT customer_id, count(' . $this->db->dbprefix('sales') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance from {$this->db->dbprefix('sales')} GROUP BY {$this->db->dbprefix('sales')}.customer_id ) FS";

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . '.id as id, company, name, phone, email, FS.total, FS.total_amount, FS.paid, FS.balance', false)
                ->from('companies')
                ->join($s, 'FS.customer_id=companies.id')
                ->where('companies.group_name', 'customer')
                ->group_by('companies.id')
                ->add_column('Actions', "<div class='text-center'><a class=\"tip\" title='" . lang('view_report') . "' href='" . admin_url('reports/customer_report/$1') . "'><span class='label label-primary'>" . lang('view_report') . '</span></a></div>', 'id')
                ->unset_column('id');
            echo $this->datatables->generate();
        }
    }

    public function getExpensesReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('expenses');

        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $category = $this->input->get('category') ? $this->input->get('category') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $note = $this->input->get('note') ? $this->input->get('note') : null;
        $user = $this->input->get('user') ? $this->input->get('user') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        if ($pdf || $xls) {
            $this->db
                ->select("date, reference, {$this->db->dbprefix('expense_categories')}.name as category, amount, note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, attachment, {$this->db->dbprefix('expenses')}.id as id", false)
                ->from('expenses')
                ->join('users', 'users.id=expenses.created_by', 'left')
                ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
                ->group_by('expenses.id');

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->db->where('created_by', $this->session->userdata('user_id'));
            }

            if ($note) {
                $this->db->like('note', $note, 'both');
            }
            if ($reference_no) {
                $this->db->like('reference', $reference_no, 'both');
            }
            if ($category) {
                $this->db->where('category_id', $category);
            }
            if ($warehouse) {
                $this->db->where('expenses.warehouse_id', $warehouse);
            }
            if ($user) {
                $this->db->where('created_by', $user);
            }
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('expenses_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('category'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('note'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('created_by'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->category);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->note);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->user);
                    $total += $data_row->amount;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('D' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $filename = 'expenses_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference, {$this->db->dbprefix('expense_categories')}.name as category, amount, note, CONCAT({$this->db->dbprefix('users')}.first_name, ' ', {$this->db->dbprefix('users')}.last_name) as user, attachment, {$this->db->dbprefix('expenses')}.id as id", false)
                ->from('expenses')
                ->join('users', 'users.id=expenses.created_by', 'left')
                ->join('expense_categories', 'expense_categories.id=expenses.category_id', 'left')
                ->group_by('expenses.id');

            if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
                $this->datatables->where('created_by', $this->session->userdata('user_id'));
            }

            if ($note) {
                $this->datatables->like('note', $note, 'both');
            }
            if ($reference_no) {
                $this->datatables->like('reference', $reference_no, 'both');
            }
            if ($category) {
                $this->datatables->where('category_id', $category);
            }
            if ($warehouse) {
                $this->datatables->where('expenses.warehouse_id', $warehouse);
            }
            if ($user) {
                $this->datatables->where('created_by', $user);
            }
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function getExpiryAlerts($warehouse_id = null)
    {
        $month = $this->input->get('month') ? $this->input->get('month') : null;

        $monthNumber = '+' . $month . 'months';
        $this->sma->checkPermissions('expiry_alerts', true);
        $date = date('Y-m-d', strtotime($monthNumber));

        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select('image, product_code,batchno,product_name, quantity_balance, warehouses.name, expiry')
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
                ->group_start()
                ->where('warehouse_id', $warehouse_id)
                ->where('expiry !=', null)->where('expiry !=', '0000-00-00')
                ->where('quantity_balance >', 0)
                ->where('expiry <', $date)
                ->group_end();
        } else {
            $this->datatables
                ->select('image, product_code, batchno, product_name, quantity_balance, warehouses.name, expiry')
                ->from('purchase_items')
                ->join('products', 'products.id=purchase_items.product_id', 'left')
                ->join('warehouses', 'warehouses.id=purchase_items.warehouse_id', 'left')
                ->group_start()
                ->where('expiry !=', null)->where('expiry !=', '0000-00-00')
                ->where('quantity_balance >', 0)
                ->where('expiry <', $date)
                ->group_end();
        }
        echo $this->datatables->generate();
    }

    public function getPaymentsReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('payments', true);

        $user = $this->input->get('user') ? $this->input->get('user') : null;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : null;
        $payment_ref = $this->input->get('payment_ref') ? $this->input->get('payment_ref') : null;
        $paid_by = $this->input->get('paid_by') ? $this->input->get('paid_by') : null;
        $sale_ref = $this->input->get('sale_ref') ? $this->input->get('sale_ref') : null;
        $purchase_ref = $this->input->get('purchase_ref') ? $this->input->get('purchase_ref') : null;
        $card = $this->input->get('card') ? $this->input->get('card') : null;
        $cheque = $this->input->get('cheque') ? $this->input->get('cheque') : null;
        $transaction_id = $this->input->get('tid') ? $this->input->get('tid') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        if ($pdf || $xls) {
            $this->db
                ->select('' . $this->db->dbprefix('payments') . '.date, ' . $this->db->dbprefix('payments') . '.reference_no as payment_ref, ' . $this->db->dbprefix('sales') . '.reference_no as sale_ref, ' . $this->db->dbprefix('purchases') . '.reference_no as purchase_ref, paid_by, amount, type')
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id')
                ->order_by('payments.date desc');

            if ($user) {
                $this->db->where('payments.created_by', $user);
            }
            if ($card) {
                $this->db->like('payments.cc_no', $card, 'both');
            }
            if ($cheque) {
                $this->db->where('payments.cheque_no', $cheque);
            }
            if ($transaction_id) {
                $this->db->where('payments.transaction_id', $transaction_id);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->db->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($paid_by) {
                $this->db->where('payments.paid_by', $paid_by);
            }
            if ($sale_ref) {
                $this->db->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->db->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('payments') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('payments_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('payment_reference'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('sale_reference'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('purchase_reference'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('paid_by'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('type'));

                $row = 2;
                $total = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->payment_ref);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->sale_ref);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->purchase_ref);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, lang($data_row->paid_by));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->amount);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->type);
                    if ($data_row->type == 'returned' || $data_row->type == 'sent') {
                        $total -= $data_row->amount;
                    } else {
                        $total += $data_row->amount;
                    }
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('F' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $filename = 'payments_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT({$this->db->dbprefix('payments')}.date, '%Y-%m-%d %T') as date, " . $this->db->dbprefix('payments') . '.reference_no as payment_ref, ' . $this->db->dbprefix('sales') . '.reference_no as sale_ref, ' . $this->db->dbprefix('purchases') . ".reference_no as purchase_ref, paid_by, amount, type, {$this->db->dbprefix('payments')}.id as id")
                ->from('payments')
                ->join('sales', 'payments.sale_id=sales.id', 'left')
                ->join('purchases', 'payments.purchase_id=purchases.id', 'left')
                ->group_by('payments.id');


            if ($user) {
                $this->datatables->where('payments.created_by', $user);
            }
            if ($card) {
                $this->datatables->like('payments.cc_no', $card, 'both');
            }
            if ($cheque) {
                $this->datatables->where('payments.cheque_no', $cheque);
            }
            if ($transaction_id) {
                $this->datatables->where('payments.transaction_id', $transaction_id);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($payment_ref) {
                $this->datatables->like('payments.reference_no', $payment_ref, 'both');
            }
            if ($paid_by) {
                $this->datatables->where('payments.paid_by', $paid_by);
            }
            if ($sale_ref) {
                $this->datatables->like('sales.reference_no', $sale_ref, 'both');
            }
            if ($purchase_ref) {
                $this->datatables->like('purchases.reference_no', $purchase_ref, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('payments') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function getProductsReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('products', true);

        $product = $this->input->get('product') ? $this->input->get('product') : null;
        $user = $this->input->get('user') ? $this->input->get('user') : null;
        $category = $this->input->get('category') ? $this->input->get('category') : null;
        $brand = $this->input->get('brand') ? $this->input->get('brand') : null;
        $subcategory = $this->input->get('subcategory') ? $this->input->get('subcategory') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $cf1 = $this->input->get('cf1') ? $this->input->get('cf1') : null;
        $cf2 = $this->input->get('cf2') ? $this->input->get('cf2') : null;
        $cf3 = $this->input->get('cf3') ? $this->input->get('cf3') : null;
        $cf4 = $this->input->get('cf4') ? $this->input->get('cf4') : null;
        $cf5 = $this->input->get('cf5') ? $this->input->get('cf5') : null;
        $cf6 = $this->input->get('cf6') ? $this->input->get('cf6') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        $pp = "( SELECT product_id, p.date as date, p.created_by as created_by, SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN quantity ELSE 0 END) as purchasedQty, SUM(quantity_balance) as balacneQty, SUM( unit_cost * quantity_balance ) balacneValue, SUM( (CASE WHEN pi.purchase_id IS NOT NULL THEN (pi.subtotal) ELSE 0 END) ) totalPurchase from {$this->db->dbprefix('purchase_items')} pi LEFT JOIN {$this->db->dbprefix('purchases')} p on p.id = pi.purchase_id WHERE pi.status = 'received' ";
        // WHERE p.status != 'pending' AND p.status != 'ordered'
        $sp = '( SELECT si.product_id, s.date as date, s.created_by as created_by, SUM( si.quantity ) soldQty, SUM( si.quantity * si.sale_unit_price ) totalSale from ' . $this->db->dbprefix('costing') . ' si JOIN ' . $this->db->dbprefix('sales') . ' s on s.id = si.sale_id ';
        // $sp = '( SELECT si.product_id, s.date as date, s.created_by as created_by, SUM( si.quantity ) soldQty, SUM( si.subtotal ) totalSale from ' . $this->db->dbprefix('sale_items') . ' si JOIN ' . $this->db->dbprefix('sales') . ' s on s.id = si.sale_id ';
        if ($start_date || $warehouse || $user) {
            $sp .= ' WHERE ';
            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $end_date ? $this->sma->fld($end_date) : date('Y-m-d');
                $pp .= " AND p.date >= '{$start_date}' AND p.date <= '{$end_date}' ";
                $sp .= " s.date >= '{$start_date}' AND s.date <= '{$end_date}' ";
            }
            if ($warehouse) {
                $pp .= " AND pi.warehouse_id = '{$warehouse}' ";
                $sp .= ($start_date ? ' AND ' : '') . " s.warehouse_id = '{$warehouse}' ";
            }
            if ($user) {
                $pp .= " AND p.created_by = '{$user}' ";
                $sp .= ($start_date || $warehouse ? ' AND ' : '') . " s.created_by = '{$user}' ";
            }
        }
        $pp .= ' GROUP BY pi.product_id ) PCosts';
        $sp .= ' GROUP BY si.product_id ) PSales';
        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . '.name,
                COALESCE( PCosts.purchasedQty, 0 ) as PurchasedQty,
                COALESCE( PSales.soldQty, 0 ) as SoldQty,
                COALESCE( PCosts.balacneQty, 0 ) as BalacneQty,
                COALESCE( PCosts.totalPurchase, 0 ) as TotalPurchase,
                COALESCE( PCosts.balacneValue, 0 ) as TotalBalance,
                COALESCE( PSales.totalSale, 0 ) as TotalSales,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit', false)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->where('products.type !=', 'combo')
                ->group_by('products.code');

            if ($product) {
                $this->db->where($this->db->dbprefix('products') . '.id', $product);
            }
            if ($cf1) {
                $this->db->where($this->db->dbprefix('products') . '.cf1', $cf1);
            }
            if ($cf2) {
                $this->db->where($this->db->dbprefix('products') . '.cf2', $cf2);
            }
            if ($cf3) {
                $this->db->where($this->db->dbprefix('products') . '.cf3', $cf3);
            }
            if ($cf4) {
                $this->db->where($this->db->dbprefix('products') . '.cf4', $cf4);
            }
            if ($cf5) {
                $this->db->where($this->db->dbprefix('products') . '.cf5', $cf5);
            }
            if ($cf6) {
                $this->db->where($this->db->dbprefix('products') . '.cf6', $cf6);
            }
            if ($category) {
                $this->db->where($this->db->dbprefix('products') . '.category_id', $category);
            }
            if ($subcategory) {
                $this->db->where($this->db->dbprefix('products') . '.subcategory_id', $subcategory);
            }
            if ($brand) {
                $this->db->where($this->db->dbprefix('products') . '.brand', $brand);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('products_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('purchased'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('sold'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('purchased_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('sold_amount'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('profit_loss'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('stock_in_hand'));

                $row = 2;
                $sQty = 0;
                $pQty = 0;
                $sAmt = 0;
                $pAmt = 0;
                $bQty = 0;
                $bAmt = 0;
                $pl = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->PurchasedQty);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->SoldQty);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->BalacneQty);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->TotalPurchase);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->TotalSales);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->Profit);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->TotalBalance);
                    $pQty += $data_row->PurchasedQty;
                    $sQty += $data_row->SoldQty;
                    $bQty += $data_row->BalacneQty;
                    $pAmt += $data_row->TotalPurchase;
                    $sAmt += $data_row->TotalSales;
                    $bAmt += $data_row->TotalBalance;
                    $pl += $data_row->Profit;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('C' . $row . ':I' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $pQty);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $sQty);
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, $bQty);
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $pAmt);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $sAmt);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $pl);
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, $bAmt);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('C2:G' . $row)->getAlignment()->setWrapText(true);
                $filename = 'products_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('products') . '.code, ' . $this->db->dbprefix('products') . ".name,
                CONCAT(COALESCE( PCosts.purchasedQty, 0 ), '__', COALESCE( PCosts.totalPurchase, 0 )) as purchased,
                CONCAT(COALESCE( PSales.soldQty, 0 ), '__', COALESCE( PSales.totalSale, 0 )) as sold,
                (COALESCE( PSales.totalSale, 0 ) - COALESCE( PCosts.totalPurchase, 0 )) as Profit,
                CONCAT(COALESCE( PCosts.balacneQty, 0 ), '__', COALESCE( PCosts.balacneValue, 0 )) as balance, {$this->db->dbprefix('products')}.id as id", false)
                ->from('products')
                ->join($sp, 'products.id = PSales.product_id', 'left')
                ->join($pp, 'products.id = PCosts.product_id', 'left')
                ->where('products.type !=', 'combo')
                ->group_by('products.code');

            if ($product) {
                $this->datatables->where($this->db->dbprefix('products') . '.id', $product);
            }
            if ($cf1) {
                $this->datatables->where($this->db->dbprefix('products') . '.cf1', $cf1);
            }
            if ($cf2) {
                $this->datatables->where($this->db->dbprefix('products') . '.cf2', $cf2);
            }
            if ($cf3) {
                $this->datatables->where($this->db->dbprefix('products') . '.cf3', $cf3);
            }
            if ($cf4) {
                $this->datatables->where($this->db->dbprefix('products') . '.cf4', $cf4);
            }
            if ($cf5) {
                $this->datatables->where($this->db->dbprefix('products') . '.cf5', $cf5);
            }
            if ($cf6) {
                $this->datatables->where($this->db->dbprefix('products') . '.cf6', $cf6);
            }
            if ($category) {
                $this->datatables->where($this->db->dbprefix('products') . '.category_id', $category);
            }
            if ($subcategory) {
                $this->datatables->where($this->db->dbprefix('products') . '.subcategory_id', $subcategory);
            }
            if ($brand) {
                $this->datatables->where($this->db->dbprefix('products') . '.brand', $brand);
            }

            echo $this->datatables->generate();
        }
    }

    public function getPurchasesReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('purchases', true);

        $product = $this->input->get('product') ? $this->input->get('product') : null;
        $user = $this->input->get('user') ? $this->input->get('user') : null;
        $supplier = $this->input->get('supplier') ? $this->input->get('supplier') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {
            $this->db
                ->select("{$this->db->dbprefix('purchases')}.date, reference_no, {$this->db->dbprefix('warehouses')}.name as wname, supplier, GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, ' (',  ROUND({$this->db->dbprefix('purchase_items')}.quantity, {$this->Settings->qty_decimals}), ')') SEPARATOR '\n') as iname, grand_total, paid, {$this->db->dbprefix('purchases')}.status", false)
                ->from('purchases')
                ->join('purchase_items', 'purchase_items.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left')
                ->group_by('purchases.id')
                ->order_by('purchases.date desc');

            if ($user) {
                $this->db->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->db->where('purchase_items.product_id', $product);
            }
            if ($supplier) {
                $this->db->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->db->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('purchases') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('purchase_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('warehouse'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('supplier'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->wname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->supplier);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->status);
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('F' . $row . ':H' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'purchase_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $pi = "( SELECT purchase_id, product_id, (GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.quantity) SEPARATOR '___')) as item_nane from {$this->db->dbprefix('purchase_items')} ";
            if ($product) {
                $pi .= " WHERE {$this->db->dbprefix('purchase_items')}.product_id = {$product} ";
            }
            $pi .= " GROUP BY {$this->db->dbprefix('purchase_items')}.purchase_id ) FPI";

            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT({$this->db->dbprefix('purchases')}.date, '%Y-%m-%d %T') as date, reference_no, {$this->db->dbprefix('warehouses')}.name as wname, supplier, (FPI.item_nane) as iname, grand_total, paid, (grand_total-paid) as balance, {$this->db->dbprefix('purchases')}.status, {$this->db->dbprefix('purchases')}.id as id", false)
                ->from('purchases')
                ->join($pi, 'FPI.purchase_id=purchases.id', 'left')
                ->join('warehouses', 'warehouses.id=purchases.warehouse_id', 'left');
            // ->group_by('purchases.id');

            if ($user) {
                $this->datatables->where('purchases.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FPI.product_id', $product, false);
            }
            if ($supplier) {
                $this->datatables->where('purchases.supplier_id', $supplier);
            }
            if ($warehouse) {
                $this->datatables->where('purchases.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('purchases.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('purchases') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function getQuantityAlerts($warehouse_id = null, $pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('quantity_alerts', true);
        if (!$this->Owner && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }

        if ($pdf || $xls) {
            if ($warehouse_id) {
                $this->db
                    ->select('image, code, name, wp.quantity, alert_quantity')
                    ->from('products')
                    ->join("( SELECT * from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left')
                    ->where('alert_quantity >= wp.quantity', null)
                    ->or_where('wp.quantity', null)
                    ->where('track_quantity', 1)
                    ->group_by('products.id')
                    ->order_by('products.code desc');
            } else {
                $this->db
                    ->select('image, code, name, quantity, alert_quantity')
                    ->from('products')
                    ->where('alert_quantity >= quantity', null)
                    ->or_where('quantity', null)
                    ->where('track_quantity', 1)
                    ->order_by('code desc');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('product_quantity_alerts'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('quantity'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('alert_quantity'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->quantity);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->alert_quantity);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $filename = 'product_quantity_alerts';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            if ($warehouse_id) {
                $this->datatables
                    ->select('image, code, name, wp.quantity as quantity, alert_quantity')
                    ->from('products')
                    ->join("( SELECT * from {$this->db->dbprefix('warehouses_products')} WHERE warehouse_id = {$warehouse_id}) wp", 'products.id=wp.product_id', 'left')
                    ->group_start()
                    ->where('alert_quantity >= wp.quantity', null)
                    ->or_where('wp.quantity', null)
                    ->group_end()
                    ->where('track_quantity', 1)
                    ->group_by('products.id');
            } else {
                $this->datatables
                    ->select('image, code, name, quantity, alert_quantity')
                    ->from('products')
                    ->group_start()
                    ->where('alert_quantity >= quantity', null)
                    ->or_where('quantity', null)
                    ->group_end()
                    ->where('track_quantity', 1);
            }

            echo $this->datatables->generate();
        }
    }

    public function getQuotesReport($pdf = null, $xls = null)
    {
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = null;
        }
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = null;
        }
        if ($this->input->get('customer')) {
            $customer = $this->input->get('customer');
        } else {
            $customer = null;
        }
        if ($this->input->get('biller')) {
            $biller = $this->input->get('biller');
        } else {
            $biller = null;
        }
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        } else {
            $warehouse = null;
        }
        if ($this->input->get('reference_no')) {
            $reference_no = $this->input->get('reference_no');
        } else {
            $reference_no = null;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = null;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = null;
        }
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if ($pdf || $xls) {
            $this->db
                ->select('date, reference_no, biller, customer, GROUP_CONCAT(CONCAT(' . $this->db->dbprefix('quote_items') . ".product_name, ' (', " . $this->db->dbprefix('quote_items') . ".quantity, ')') SEPARATOR '<br>') as iname, grand_total, status", false)
                ->from('quotes')
                ->join('quote_items', 'quote_items.quote_id=quotes.id', 'left')
                ->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
                ->group_by('quotes.id');

            if ($user) {
                $this->db->where('quotes.created_by', $user);
            }
            if ($product) {
                $this->db->where('quote_items.product_id', $product);
            }
            if ($biller) {
                $this->db->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('quotes') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('quotes_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'quotes_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $qi = "( SELECT quote_id, product_id, GROUP_CONCAT(CONCAT({$this->db->dbprefix('quote_items')}.product_name, '__', {$this->db->dbprefix('quote_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('quote_items')} ";
            if ($product) {
                $qi .= " WHERE {$this->db->dbprefix('quote_items')}.product_id = {$product} ";
            }
            $qi .= " GROUP BY {$this->db->dbprefix('quote_items')}.quote_id ) FQI";
            $this->load->library('datatables');
            $this->datatables
                ->select("date, reference_no, biller, customer, FQI.item_nane as iname, grand_total, status, {$this->db->dbprefix('quotes')}.id as id", false)
                ->from('quotes')
                ->join($qi, 'FQI.quote_id=quotes.id', 'left')
                ->join('warehouses', 'warehouses.id=quotes.warehouse_id', 'left')
                ->group_by('quotes.id');

            if ($user) {
                $this->datatables->where('quotes.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FQI.product_id', $product, false);
            }
            if ($biller) {
                $this->datatables->where('quotes.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('quotes.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('quotes.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('quotes.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('quotes') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function getRrgisterlogs($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('register', true);
        if ($this->input->get('user')) {
            $user = $this->input->get('user');
        } else {
            $user = null;
        }
        if ($this->input->get('start_date')) {
            $start_date = $this->input->get('start_date');
        } else {
            $start_date = null;
        }
        if ($this->input->get('end_date')) {
            $end_date = $this->input->get('end_date');
        } else {
            $end_date = null;
        }
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }

        if ($pdf || $xls) {
            $this->db
                ->select('date, closed_at, CONCAT(' . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, ' (', users.email, ')') as user, cash_in_hand, total_cc_slips, total_cheques, total_cash, total_cc_slips_submitted, total_cheques_submitted,total_cash_submitted, note", false)
                ->from('pos_register')
                ->join('users', 'users.id=pos_register.user_id', 'left')
                ->order_by('date desc');
            //->where('status', 'close');

            if ($user) {
                $this->db->where('pos_register.user_id', $user);
            }
            if ($start_date) {
                $this->db->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('register_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('open_time'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('close_time'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('user'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('cash_in_hand'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('cc_slips'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('cheques'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('total_cash'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('cc_slips_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('cheques_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('total_cash_submitted'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('note'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->closed_at);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->user);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->cash_in_hand);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->total_cc_slips);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->total_cheques);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->total_cash);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->total_cc_slips_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $data_row->total_cheques_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $data_row->total_cash_submitted);
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $data_row->note);
                    if ($data_row->total_cash_submitted < $data_row->total_cash || $data_row->total_cheques_submitted < $data_row->total_cheques || $data_row->total_cc_slips_submitted < $data_row->total_cc_slips) {
                        $this->excel->getActiveSheet()->getStyle('A' . $row . ':K' . $row)->applyFromArray(
                            ['fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'F2DEDE']]]
                        );
                    }
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(35);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $filename = 'register_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select('date, closed_at, CONCAT(' . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name, '<br>', " . $this->db->dbprefix('users') . ".email) as user, cash_in_hand, CONCAT(total_cc_slips, ' (', total_cc_slips_submitted, ')'), CONCAT(total_cheques, ' (', total_cheques_submitted, ')'), CONCAT(total_cash, ' (', total_cash_submitted, ')'), note", false)
                ->from('pos_register')
                ->join('users', 'users.id=pos_register.user_id', 'left');

            if ($user) {
                $this->datatables->where('pos_register.user_id', $user);
            }
            if ($start_date) {
                $this->datatables->where('date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function getSalesReport($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('sales', true);
        $product = $this->input->get('product') ? $this->input->get('product') : null;
        $user = $this->input->get('user') ? $this->input->get('user') : null;
        $customer = $this->input->get('customer') ? $this->input->get('customer') : null;
        $biller = $this->input->get('biller') ? $this->input->get('biller') : null;
        $warehouse = $this->input->get('warehouse') ? $this->input->get('warehouse') : null;
        $reference_no = $this->input->get('reference_no') ? $this->input->get('reference_no') : null;
        $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
        $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;
        $serial = $this->input->get('serial') ? $this->input->get('serial') : null;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {
            $this->db
                ->select("date, reference_no, biller, customer, GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name, ' (', ROUND({$this->db->dbprefix('sale_items')}.quantity, {$this->Settings->qty_decimals}), ')') SEPARATOR '\n') as iname, grand_total, paid, payment_status", false)
                ->from('sales')
                ->join('sale_items', 'sale_items.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')
                ->group_by('sales.id')
                ->order_by('sales.date desc');

            if ($user) {
                $this->db->where('sales.created_by', $user);
            }
            if ($product) {
                $this->db->where('sale_items.product_id', $product);
            }
            if ($serial) {
                $this->db->like('sale_items.serial_no', $serial);
            }
            if ($biller) {
                $this->db->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->db->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->db->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->db->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('sales_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('biller'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('customer'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('payment_status'));

                $row = 2;
                $total = 0;
                $paid = 0;
                $balance = 0;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->reference_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->biller);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->customer);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->paid);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, ($data_row->grand_total - $data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, lang($data_row->payment_status));
                    $total += $data_row->grand_total;
                    $paid += $data_row->paid;
                    $balance += ($data_row->grand_total - $data_row->paid);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('F' . $row . ':H' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, $total);
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $paid);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $balance);

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'sales_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name, '__', {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";
            if ($product || $serial) {
                $si .= ' WHERE ';
            }
            if ($product) {
                $si .= " {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
            if ($product && $serial) {
                $si .= ' AND ';
            }
            if ($serial) {
                $si .= " {$this->db->dbprefix('sale_items')}.serial_no LIKe '%{$serial}%' ";
            }
            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
            $this->load->library('datatables');
            $this->datatables
                ->select("DATE_FORMAT(date, '%Y-%m-%d %T') as date, reference_no, biller, customer, FSI.item_nane as iname, grand_total, paid, (grand_total-paid) as balance, payment_status, {$this->db->dbprefix('sales')}.id as id", false)
                ->from('sales')
                ->join($si, 'FSI.sale_id=sales.id', 'left')
                ->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left');
            // ->group_by('sales.id');

            if ($user) {
                $this->datatables->where('sales.created_by', $user);
            }
            if ($product) {
                $this->datatables->where('FSI.product_id', $product);
            }
            if ($serial) {
                $this->datatables->like('FSI.serial_no', $serial);
            }
            if ($biller) {
                $this->datatables->where('sales.biller_id', $biller);
            }
            if ($customer) {
                $this->datatables->where('sales.customer_id', $customer);
            }
            if ($warehouse) {
                $this->datatables->where('sales.warehouse_id', $warehouse);
            }
            if ($reference_no) {
                $this->datatables->like('sales.reference_no', $reference_no, 'both');
            }
            if ($start_date) {
                $this->datatables->where($this->db->dbprefix('sales') . '.date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            echo $this->datatables->generate();
        }
    }

    public function getSuppliers($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('suppliers', true);

        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('companies') . ".id as id, company, name, phone, email, count({$this->db->dbprefix('purchases')}.id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance", false)
                ->from('companies')
                ->join('purchases', 'purchases.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->order_by('companies.company asc')
                ->group_by('companies.id');

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('suppliers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('company'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('phone'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('total_purchases'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('total_amount'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('paid'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('balance'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->company);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->phone);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->email);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $this->sma->formatDecimal($data_row->total));
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $this->sma->formatDecimal($data_row->total_amount));
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->sma->formatDecimal($data_row->paid));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->sma->formatDecimal($data_row->balance));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $filename = 'suppliers_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $p = '( SELECT supplier_id, count(' . $this->db->dbprefix('purchases') . ".id) as total, COALESCE(sum(grand_total), 0) as total_amount, COALESCE(sum(paid), 0) as paid, ( COALESCE(sum(grand_total), 0) - COALESCE(sum(paid), 0)) as balance from {$this->db->dbprefix('purchases')} GROUP BY {$this->db->dbprefix('purchases')}.supplier_id ) FP";

            $this->load->library('datatables');
            $this->datatables
                ->select($this->db->dbprefix('companies') . '.id as id, company, name, phone, email, FP.total, FP.total_amount, FP.paid, FP.balance', false)
                ->from('companies')
                ->join($p, 'FP.supplier_id=companies.id')
                ->where('companies.group_name', 'supplier')
                ->group_by('companies.id')
                ->add_column('Actions', "<div class='text-center'><a class=\"tip\" title='" . lang('view_report') . "' href='" . admin_url('reports/supplier_report/$1') . "'><span class='label label-primary'>" . lang('view_report') . '</span></a></div>', 'id')
                ->unset_column('id');
            echo $this->datatables->generate();
        }
    }

    public function getTransfersReport($pdf = null, $xls = null)
    {
        if ($this->input->get('product')) {
            $product = $this->input->get('product');
        } else {
            $product = null;
        }

        if ($pdf || $xls) {
            $this->db
                ->select($this->db->dbprefix('transfers') . '.date, transfer_no, (CASE WHEN ' . $this->db->dbprefix('transfers') . ".status = 'completed' THEN  GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('purchase_items') . ".product_name, ' (', " . $this->db->dbprefix('purchase_items') . ".quantity, ')') SEPARATOR '<br>') ELSE GROUP_CONCAT(CONCAT(" . $this->db->dbprefix('transfer_items') . ".product_name, ' (', " . $this->db->dbprefix('transfer_items') . ".quantity, ')') SEPARATOR '<br>') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, " . $this->db->dbprefix('transfers') . '.status')
                ->from('transfers')
                ->join('transfer_items', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id')->order_by('transfers.date desc');
            if ($product) {
                $this->db->where($this->db->dbprefix('purchase_items') . '.product_id', $product);
                $this->db->or_where($this->db->dbprefix('transfer_items') . '.product_id', $product);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('transfers_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('transfer_no'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('product_qty'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('warehouse') . ' (' . lang('from') . ')');
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('warehouse') . ' (' . lang('to') . ')');
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('grand_total'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('status'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($data_row->date));
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->transfer_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->iname);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->fname . ' (' . $data_row->fcode . ')');
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->tname . ' (' . $data_row->tcode . ')');
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->grand_total);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->status);
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                $filename = 'transfers_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('transfers')}.date, transfer_no, (CASE WHEN {$this->db->dbprefix('transfers')}.status = 'completed' THEN  GROUP_CONCAT(CONCAT({$this->db->dbprefix('purchase_items')}.product_name, '__', {$this->db->dbprefix('purchase_items')}.quantity) SEPARATOR '___') ELSE GROUP_CONCAT(CONCAT({$this->db->dbprefix('transfer_items')}.product_name, '__', {$this->db->dbprefix('transfer_items')}.quantity) SEPARATOR '___') END) as iname, from_warehouse_name as fname, from_warehouse_code as fcode, to_warehouse_name as tname,to_warehouse_code as tcode, grand_total, {$this->db->dbprefix('transfers')}.status, {$this->db->dbprefix('transfers')}.id as id", false)
                ->from('transfer_items')
                ->join('transfers', 'transfer_items.transfer_id=transfers.id', 'left')
                ->join('purchase_items', 'purchase_items.transfer_id=transfers.id', 'left')
                ->group_by('transfers.id');
            if ($product) {
                $this->datatables->where(" (({$this->db->dbprefix('purchase_items')}.product_id = {$product}) OR ({$this->db->dbprefix('transfer_items')}.product_id = {$product})) ", null, false);
            }
            $this->datatables->edit_column('fname', '$1 ($2)', 'fname, fcode')
                ->edit_column('tname', '$1 ($2)', 'tname, tcode')
                ->unset_column('fcode')
                ->unset_column('tcode');
            echo $this->datatables->generate();
        }
    }

    public function getUserLogins($id = null, $pdf = null, $xls = null)
    {
        if ($this->input->get('start_date')) {
            $login_start_date = $this->input->get('start_date');
        } else {
            $login_start_date = null;
        }
        if ($this->input->get('end_date')) {
            $login_end_date = $this->input->get('end_date');
        } else {
            $login_end_date = null;
        }
        if ($login_start_date) {
            $login_start_date = $this->sma->fld($login_start_date);
            $login_end_date = $login_end_date ? $this->sma->fld($login_end_date) : date('Y-m-d H:i:s');
        }
        if ($pdf || $xls) {
            $this->db
                ->select('login, ip_address, time')
                ->from('user_logins')
                ->where('user_id', $id)
                ->order_by('time desc');
            if ($login_start_date) {
                $this->db->where("time BETWEEN '{$login_start_date}' and '{$login_end_date}'", null, false);
            }

            $q = $this->db->get();
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('staff_login_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('email'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('ip_address'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('time'));

                $row = 2;
                foreach ($data as $data_row) {
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->login);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->ip_address);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $this->sma->hrld($data_row->time));
                    $row++;
                }

                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(35);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('C2:C' . $row)->getAlignment()->setWrapText(true);
                $filename = 'staff_login_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->load->library('datatables');
            $this->datatables
                ->select("login, ip_address, DATE_FORMAT(time, '%Y-%m-%d %T') as time")
                ->from('user_logins')
                ->where('user_id', $id);
            if ($login_start_date) {
                $this->datatables->where("time BETWEEN '{$login_start_date}' and '{$login_end_date}'", null, false);
            }
            echo $this->datatables->generate();
        }
    }

    public function getUsers()
    {
        $this->load->library('datatables');
        $this->datatables
            ->select($this->db->dbprefix('users') . '.id as id, first_name, last_name, email, company, ' . $this->db->dbprefix('groups') . '.name, active')
            ->from('users')
            ->join('groups', 'users.group_id=groups.id', 'left')
            ->group_by('users.id')
            ->where('company_id', null);
        if (!$this->Owner) {
            $this->datatables->where('group_id !=', 1);
        }
        $this->datatables
            ->edit_column('active', '$1__$2', 'active, id')
            ->add_column('Actions', "<div class='text-center'><a class=\"tip\" title='" . lang('view_report') . "' href='" . admin_url('reports/staff_report/$1') . "'><span class='label label-primary'>" . lang('view_report') . '</span></a></div>', 'id')
            ->unset_column('id');
        echo $this->datatables->generate();
    }

    public function index()
    {
        $this->sma->checkPermissions();
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['monthly_sales'] = $this->reports_model->getChartData();
        $this->data['stock'] = $this->reports_model->getStockValue();
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('reports')]];
        $meta = ['page_title' => lang('reports'), 'bc' => $bc];
        $this->page_construct('reports/index', $meta, $this->data);
    }

    public function monthly_profit($year, $month, $warehouse_id = null, $re = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }

        $this->data['costing'] = $this->reports_model->getCosting(null, $warehouse_id, $year, $month);
        $this->data['discount'] = $this->reports_model->getOrderDiscount(null, $warehouse_id, $year, $month);
        $this->data['expenses'] = $this->reports_model->getExpenses(null, $warehouse_id, $year, $month);
        $this->data['returns'] = $this->reports_model->getReturns(null, $warehouse_id, $year, $month);
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['swh'] = $warehouse_id;
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        $this->data['date'] = date('F Y', strtotime($year . '-' . $month . '-' . '01'));
        if ($re) {
            echo $this->load->view($this->theme . 'reports/monthly_profit', $this->data, true);
            exit();
        }
        $this->load->view($this->theme . 'reports/monthly_profit', $this->data);
    }

    public function monthly_purchases($warehouse_id = null, $year = null, $pdf = null, $user_id = null)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->load->language('calendar');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['year'] = $year;
        $this->data['purchases'] = $user_id ? $this->reports_model->getStaffMonthlyPurchases($user_id, $year, $warehouse_id) : $this->reports_model->getMonthlyPurchases($year, $warehouse_id);
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/monthly', $this->data, true);
            $name = lang('monthly_purchases') . '_' . $year . '.pdf';
            $html = str_replace('<p class="introtext">' . lang('reports_calendar_text') . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('monthly_purchases_report')]];
        $meta = ['page_title' => lang('monthly_purchases_report'), 'bc' => $bc];
        $this->page_construct('reports/monthly_purchases', $meta, $this->data);
    }

    public function monthly_sales($warehouse_id = null, $year = null, $pdf = null, $user_id = null)
    {
        $this->sma->checkPermissions();
        if (!$this->Owner && !$this->Admin && $this->session->userdata('warehouse_id')) {
            $warehouse_id = $this->session->userdata('warehouse_id');
        }
        if (!$year) {
            $year = date('Y');
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user_id = $this->session->userdata('user_id');
        }
        $this->load->language('calendar');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['year'] = $year;
        $this->data['sales'] = $user_id ? $this->reports_model->getStaffMonthlySales($user_id, $year, $warehouse_id) : $this->reports_model->getMonthlySales($year, $warehouse_id);
        if ($pdf) {
            $html = $this->load->view($this->theme . 'reports/monthly', $this->data, true);
            $name = lang('monthly_sales') . '_' . $year . '.pdf';
            $html = str_replace('<p class="introtext">' . lang('reports_calendar_text') . '</p>', '', $html);
            $this->sma->generate_pdf($html, $name, null, null, null, null, null, 'L');
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse_id;
        $this->data['sel_warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('monthly_sales_report')]];
        $meta = ['page_title' => lang('monthly_sales_report'), 'bc' => $bc];
        $this->page_construct('reports/monthly', $meta, $this->data);
    }

    public function payments()
    {
        $this->sma->checkPermissions('payments');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['pos_settings'] = POS ? $this->reports_model->getPOSSetting('biller') : false;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('payments_report')]];
        $meta = ['page_title' => lang('payments_report'), 'bc' => $bc];
        $this->page_construct('reports/payments', $meta, $this->data);
    }

    public function products()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $this->data['categories'] = $this->site->getAllCategories();
        $this->data['brands'] = $this->site->getAllBrands();
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($this->input->post('start_date')) {
            $dt = 'From ' . $this->input->post('start_date') . ' to ' . $this->input->post('end_date');
        } else {
            $dt = 'Till ' . $this->input->post('end_date');
        }
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('products_report')]];
        $meta = ['page_title' => lang('products_report'), 'bc' => $bc];
        $this->page_construct('reports/products', $meta, $this->data);
    }

    public function profit($date = null, $warehouse_id = null, $re = null)
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('error', lang('access_denied'));
            $this->sma->md();
        }
        if (!$date) {
            $date = date('Y-m-d');
        }
        $this->data['costing'] = $this->reports_model->getCosting($date, $warehouse_id);
        $this->data['discount'] = $this->reports_model->getOrderDiscount($date, $warehouse_id);
        $this->data['expenses'] = $this->reports_model->getExpenses($date, $warehouse_id);
        $this->data['returns'] = $this->reports_model->getReturns($date, $warehouse_id);
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['swh'] = $warehouse_id;
        $this->data['date'] = $date;
        if ($re) {
            echo $this->load->view($this->theme . 'reports/profit', $this->data, true);
            exit();
        }
        $this->load->view($this->theme . 'reports/profit', $this->data);
    }

    public function profit_loss($start_date = null, $end_date = null)
    {
        $this->sma->checkPermissions('profit_loss');
        if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $this->data['total_purchases'] = $this->reports_model->getTotalPurchases($start, $end);
        $this->data['total_sales'] = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_return_sales'] = $this->reports_model->getTotalReturnSales($start, $end);
        $this->data['total_expenses'] = $this->reports_model->getTotalExpenses($start, $end);
        $this->data['total_paid'] = $this->reports_model->getTotalPaidAmount($start, $end);
        $this->data['total_received'] = $this->reports_model->getTotalReceivedAmount($start, $end);
        $this->data['total_received_cash'] = $this->reports_model->getTotalReceivedCashAmount($start, $end);
        $this->data['total_received_cc'] = $this->reports_model->getTotalReceivedCCAmount($start, $end);
        $this->data['total_received_cheque'] = $this->reports_model->getTotalReceivedChequeAmount($start, $end);
        $this->data['total_received_ppp'] = $this->reports_model->getTotalReceivedPPPAmount($start, $end);
        $this->data['total_received_stripe'] = $this->reports_model->getTotalReceivedStripeAmount($start, $end);
        $this->data['total_returned'] = $this->reports_model->getTotalReturnedAmount($start, $end);
        $this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);

        $warehouses = $this->site->getAllWarehouses();
        foreach ($warehouses as $warehouse) {
            $total_purchases = $this->reports_model->getTotalPurchases($start, $end, $warehouse->id);
            $total_sales = $this->reports_model->getTotalSales($start, $end, $warehouse->id);
            $total_returns = $this->reports_model->getTotalReturnSales($start, $end, $warehouse->id);
            $total_expenses = $this->reports_model->getTotalExpenses($start, $end, $warehouse->id);
            $warehouses_report[] = [
                'warehouse' => $warehouse,
                'total_purchases' => $total_purchases,
                'total_sales' => $total_sales,
                'total_returns' => $total_returns,
                'total_expenses' => $total_expenses,
            ];
        }
        $this->data['warehouses_report'] = $warehouses_report;

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('profit_loss')]];
        $meta = ['page_title' => lang('profit_loss'), 'bc' => $bc];
        $this->page_construct('reports/profit_loss', $meta, $this->data);
    }

    public function profit_loss_pdf($start_date = null, $end_date = null)
    {
        $this->sma->checkPermissions('profit_loss');
        if (!$start_date) {
            $start = $this->db->escape(date('Y-m') . '-1');
            $start_date = date('Y-m') . '-1';
        } else {
            $start = $this->db->escape(urldecode($start_date));
        }
        if (!$end_date) {
            $end = $this->db->escape(date('Y-m-d H:i'));
            $end_date = date('Y-m-d H:i');
        } else {
            $end = $this->db->escape(urldecode($end_date));
        }

        $this->data['total_purchases'] = $this->reports_model->getTotalPurchases($start, $end);
        $this->data['total_sales'] = $this->reports_model->getTotalSales($start, $end);
        $this->data['total_expenses'] = $this->reports_model->getTotalExpenses($start, $end);
        $this->data['total_paid'] = $this->reports_model->getTotalPaidAmount($start, $end);
        $this->data['total_received'] = $this->reports_model->getTotalReceivedAmount($start, $end);
        $this->data['total_received_cash'] = $this->reports_model->getTotalReceivedCashAmount($start, $end);
        $this->data['total_received_cc'] = $this->reports_model->getTotalReceivedCCAmount($start, $end);
        $this->data['total_received_cheque'] = $this->reports_model->getTotalReceivedChequeAmount($start, $end);
        $this->data['total_received_ppp'] = $this->reports_model->getTotalReceivedPPPAmount($start, $end);
        $this->data['total_received_stripe'] = $this->reports_model->getTotalReceivedStripeAmount($start, $end);
        $this->data['total_returned'] = $this->reports_model->getTotalReturnedAmount($start, $end);
        $this->data['start'] = urldecode($start_date);
        $this->data['end'] = urldecode($end_date);

        $warehouses = $this->site->getAllWarehouses();
        foreach ($warehouses as $warehouse) {
            $total_purchases = $this->reports_model->getTotalPurchases($start, $end, $warehouse->id);
            $total_sales = $this->reports_model->getTotalSales($start, $end, $warehouse->id);
            $warehouses_report[] = [
                'warehouse' => $warehouse,
                'total_purchases' => $total_purchases,
                'total_sales' => $total_sales,
            ];
        }
        $this->data['warehouses_report'] = $warehouses_report;

        $html = $this->load->view($this->theme . 'reports/profit_loss_pdf', $this->data, true);
        $name = lang('profit_loss') . '-' . str_replace(['-', ' ', ':'], '_', $this->data['start']) . '-' . str_replace(['-', ' ', ':'], '_', $this->data['end']) . '.pdf';
        $this->sma->generate_pdf($html, $name, false, false, false, false, false, 'L');
    }

    public function purchases()
    {
        $this->sma->checkPermissions('purchases');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('purchases_report')]];
        $meta = ['page_title' => lang('purchases_report'), 'bc' => $bc];
        $this->page_construct('reports/purchases', $meta, $this->data);
    }

    public function quantity_alerts($warehouse_id = null)
    {
        $this->sma->checkPermissions('quantity_alerts');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $user = $this->site->getUser();
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $user->warehouse_id;
            $this->data['warehouse'] = $user->warehouse_id ? $this->site->getWarehouseByID($user->warehouse_id) : null;
        }

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('product_quantity_alerts')]];
        $meta = ['page_title' => lang('product_quantity_alerts'), 'bc' => $bc];
        $this->page_construct('reports/quantity_alerts', $meta, $this->data);
    }

    public function register()
    {
        $this->sma->checkPermissions('register');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('register_report')]];
        $meta = ['page_title' => lang('register_report'), 'bc' => $bc];
        $this->page_construct('reports/register', $meta, $this->data);
    }

    public function out_of_stock_dashboard()
    {
        $this->sma->checkPermissions(); //'sales'
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        // $this->data['users'] = $this->reports_model->getStaff(); 
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('out_of_stock_dashboard')]];
        $meta = ['page_title' => lang('out_of_stock_dashboard'), 'bc' => $bc];
        $this->page_construct('reports/out_of_stock_dashboard', $meta, $this->data);
    }
    public function get_out_of_stock_products($pdf = null, $xls = null)
    {

        $this->sma->checkPermissions('sales', true);
        // $product = $this->input->get('product') ? $this->input->get('product') : null;  
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }
        $status = $this->input->post('status') ? $this->input->post('status') : null;

        if ($pdf || $xls) {
            $keyword = $this->input->get('keyword') ? $this->input->get('keyword') : null;
            $status = $this->input->get('status') ? $this->input->get('status') : null;
            $this->db
                ->select(" {$this->db->dbprefix('products')}.code, 
            {$this->db->dbprefix('products')}.name,  
            {$this->db->dbprefix('products')}.alert_quantity, 
            SUM({$this->db->dbprefix('inventory_movements')}.quantity) as total_quantity
            ", false)
                ->from('inventory_movements');
            $this->db->join('products', 'products.id=inventory_movements.product_id')
                ->group_by("inventory_movements.product_id");
            if ($status == 'out_of_stock') {
                $this->db->having("sum({$this->db->dbprefix('inventory_movements')}.quantity)<=0");
            } else {
                $this->db->having("sum({$this->db->dbprefix('inventory_movements')}.quantity)<={$this->db->dbprefix('products')}.alert_quantity");
            }
            $keyword = trim($this->input->post('keyword'));
            if (!empty($keyword)) {
                $this->db->group_start();
                $this->db->where("{$this->db->dbprefix('products')}.code", $keyword);
                $this->db->or_like("{$this->db->dbprefix('products')}.name", $keyword, 'both');
                $this->db->group_end();
            }
            $q = $this->db->get();
            //echo $this->db->last_query(); exit; 
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('Stock_dashboard'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('alert_quantity'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('quantity'));
                $row = 2;
                foreach ($data as $data_row) {         //  $row, $this->sma->hrld($data_row->code) // 
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->alert_quantity);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->total_quantity);
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('G' . $row . ':H' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');

                // $this->excel->getActiveSheet()->SetCellValue('H' . $row, $balance); 
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'Stock_dashboard';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('products')}.image, {$this->db->dbprefix('products')}.code, 
                {$this->db->dbprefix('products')}.name,  
                {$this->db->dbprefix('products')}.alert_quantity, 
                SUM({$this->db->dbprefix('inventory_movements')}.quantity) as total_quantity
                ", false)
                ->from('inventory_movements');
            $this->datatables->join('products', 'products.id=inventory_movements.product_id')
                ->group_by("inventory_movements.product_id");
            if ($status == 'out_of_stock') {
                $this->datatables->having("sum({$this->db->dbprefix('inventory_movements')}.quantity)<=0");
            } else {
                $this->datatables->having("sum({$this->db->dbprefix('inventory_movements')}.quantity)<={$this->db->dbprefix('products')}.alert_quantity");
            }


            $keyword = trim($this->input->post('keyword'));
            if (!empty($keyword)) {
                $this->db->group_start();
                $this->datatables->where("{$this->db->dbprefix('products')}.code", $keyword);
                $this->db->or_like("{$this->db->dbprefix('products')}.name", $keyword, 'both');
                // $this->db->or_like("{$this->db->dbprefix('brands')}.name",$keyword,'both');   
                // $this->db->or_like("{$this->db->dbprefix('categories')}.name",$keyword,'both'); 
                $this->db->group_end();
            }

            // $this->db->order_by("total_pieces",'DESC');  
            echo $this->datatables->generate();
        }
    }
    public function promotion_items_report()
    {
        $this->sma->checkPermissions(); //'sales'
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('promotion_items_report')]];
        $meta = ['page_title' => lang('promotion_items_report'), 'bc' => $bc];
        $this->page_construct('reports/promotion_items_report', $meta, $this->data);
    }
    public function get_promotion_items($pdf = null, $xls = null)
    {

        $this->sma->checkPermissions('sales', true);
        // $product = $this->input->get('product') ? $this->input->get('product') : null; 
        $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : null;
        $end_date = $this->input->post('end_date') ? $this->input->post('end_date') : null;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
            $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;
            $keyword = $this->input->get('keyword') ? $this->input->get('keyword') : null;

            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $this->sma->fld($end_date);
            }
            $this->db
                ->select("{$this->db->dbprefix('products')}.code, {$this->db->dbprefix('products')}.name,  
                {$this->db->dbprefix('products')}.start_date,  {$this->db->dbprefix('products')}.end_date,  
                {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname,
                {$this->db->dbprefix('products')}.promo_price,
                {$this->db->dbprefix('products')}.price,
                ", false)
                ->from('products');
            $this->db->join('categories', 'products.category_id=categories.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->group_by("products.id");
            $this->db->where("{$this->db->dbprefix('products')}.promotion", 1);
            $keyword = trim($this->input->post('keyword'));
            if (!empty($keyword)) {
                $this->db->group_start();
                $this->db->where("{$this->db->dbprefix('products')}.code", $keyword);
                $this->db->or_like("{$this->db->dbprefix('products')}.name", $keyword, 'both');
                $this->db->or_like("{$this->db->dbprefix('brands')}.name", $keyword, 'both');
                $this->db->or_like("{$this->db->dbprefix('categories')}.name", $keyword, 'both');
                $this->db->group_end();
            }
            if ($start_date) {
                $this->db->where($this->db->dbprefix('products') . '.start_date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            //echo $this->db->last_query(); exit; 
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('promo_items'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('start_date'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('end_date'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('brand'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('category'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('promo_price'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('Price'));
                $row = 2;
                $gtotal_promo_price = 0;
                $gtotal_Price = 0;

                foreach ($data as $data_row) {         //  $row, $this->sma->hrld($data_row->code) // 
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->start_date);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->end_date);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->brand);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->cname);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $data_row->promo_price);
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $data_row->price);

                    $gtotal_promo_price += $data_row->promo_price;
                    $gtotal_Price += $data_row->price;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('G' . $row . ':H' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, $gtotal_promo_price);
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, $gtotal_Price);
                // $this->excel->getActiveSheet()->SetCellValue('H' . $row, $balance); 
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'promotion_items_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);

            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {

            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('products')}.image, {$this->db->dbprefix('products')}.code, 
                {$this->db->dbprefix('products')}.name,  
                {$this->db->dbprefix('products')}.start_date,  {$this->db->dbprefix('products')}.end_date,  
                {$this->db->dbprefix('brands')}.name as brand, {$this->db->dbprefix('categories')}.name as cname,
                {$this->db->dbprefix('products')}.promo_price,
                {$this->db->dbprefix('products')}.price,
                ", false)
                ->from('products');
            $this->datatables->join('categories', 'products.category_id=categories.id', 'left')
                ->join('brands', 'products.brand=brands.id', 'left')
                ->group_by("products.id");
            $this->datatables->where("{$this->db->dbprefix('products')}.promotion", 1);
            $keyword = trim($this->input->post('keyword'));
            if (!empty($keyword)) {
                $this->db->group_start();
                $this->datatables->where("{$this->db->dbprefix('products')}.code", $keyword);
                $this->db->or_like("{$this->db->dbprefix('products')}.name", $keyword, 'both');
                $this->db->or_like("{$this->db->dbprefix('brands')}.name", $keyword, 'both');
                $this->db->or_like("{$this->db->dbprefix('categories')}.name", $keyword, 'both');
                $this->db->group_end();
            }
            if (!empty($start_date) and !empty($end_date)) {
                $this->datatables->where($this->db->dbprefix('products') . '.start_date BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            // $this->db->order_by("total_pieces",'DESC');  
            echo $this->datatables->generate();
        }
    }
    public function fast_moving_items()
    {
        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        // $this->data['warehouses'] = $this->site->getAllWarehouses();
        // $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('fast_moving_items')]];
        $meta = ['page_title' => lang('fast_moving_items'), 'bc' => $bc];
        $this->page_construct('reports/fast_moving_items', $meta, $this->data);
    }

    public function ecommerce_fast_moving_items($pdf = null, $xls = null)
    {
        $this->sma->checkPermissions('sales', true);
        // $product = $this->input->get('product') ? $this->input->get('product') : null; 
        $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : null;
        $end_date = $this->input->post('end_date') ? $this->input->post('end_date') : null;

        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $user = $this->session->userdata('user_id');
        }

        if ($pdf || $xls) {

            $start_date = $this->input->get('start_date') ? $this->input->get('start_date') : null;
            $end_date = $this->input->get('end_date') ? $this->input->get('end_date') : null;
            $keyword = $this->input->get('keyword') ? $this->input->get('keyword') : null;

            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $this->sma->fld($end_date);
            }
            $this->db
                ->select(" {$this->db->dbprefix('products')}.code, {$this->db->dbprefix('products')}.name,  
            SUM({$this->db->dbprefix('sale_items')}.quantity) as total_pieces, SUM({$this->db->dbprefix('sale_items')}.subtotal) as total_amount 
            ", false)
                ->from('sale_items')
                ->join('sales', 'sales.id=sale_items.sale_id', 'left')
                ->join('products', 'products.id=sale_items.product_id', 'left')
                ->group_by('sale_items.product_id')
                ->order_by("total_pieces", 'DESC')
            ;
            $this->db->where("{$this->db->dbprefix('sales')}.shop", 1);
            $this->db->where("{$this->db->dbprefix('sales')}.sale_status", "Completed");

            if (!empty($keyword)) {
                $this->db->group_start();
                $this->db->where("{$this->db->dbprefix('products')}.code", $keyword);
                $this->db->or_like("{$this->db->dbprefix('products')}.name", $keyword, 'both');
                $this->db->group_end();
            }
            // if ($product) {
            //     $this->db->where('sale_items.product_id', $product);
            // }  
            if ($start_date) {
                $this->db->where('DATE(' . $this->db->dbprefix('sales') . '.date) BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }

            $q = $this->db->get();
            //echo $this->db->last_query(); exit; 
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $data[] = $row;
                }
            } else {
                $data = null;
            }

            if (!empty($data)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('ecommerce_fast_moving_items'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('product_code'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('product_name'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('quantities_sold'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('total_amount'));
                $row = 2;
                $gtotal_pieces = 0;
                $gtotal_amount = 0;

                foreach ($data as $data_row) {         //  $row, $this->sma->hrld($data_row->code) // 
                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->code);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->name);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $data_row->total_pieces);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->total_amount);
                    $gtotal_pieces += $data_row->total_pieces;
                    $gtotal_amount += $data_row->total_amount;
                    $row++;
                }
                $this->excel->getActiveSheet()->getStyle('C' . $row . ':D' . $row)->getBorders()
                    ->getTop()->setBorderStyle('medium');
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, $gtotal_pieces);
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, $gtotal_amount);
                // $this->excel->getActiveSheet()->SetCellValue('H' . $row, $balance); 
                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $this->excel->getActiveSheet()->getStyle('E2:E' . $row)->getAlignment()->setWrapText(true);
                $filename = 'ecomerce_fast_moving_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);
        } else {

            $si = "( SELECT sale_id, product_id, serial_no, GROUP_CONCAT(CONCAT({$this->db->dbprefix('sale_items')}.product_name, '__', {$this->db->dbprefix('sale_items')}.quantity) SEPARATOR '___') as item_nane from {$this->db->dbprefix('sale_items')} ";
            if ($product || $serial) {
                $si .= ' WHERE ';
            }
            if ($product) {
                $si .= " {$this->db->dbprefix('sale_items')}.product_id = {$product} ";
            }
            if ($product && $serial) {
                $si .= ' AND ';
            }
            if ($serial) {
                $si .= " {$this->db->dbprefix('sale_items')}.serial_no LIKe '%{$serial}%' ";
            }
            $si .= " GROUP BY {$this->db->dbprefix('sale_items')}.sale_id ) FSI";
            $this->load->library('datatables');
            $this->datatables
                ->select("{$this->db->dbprefix('products')}.image, {$this->db->dbprefix('products')}.code, {$this->db->dbprefix('products')}.name,  
                SUM({$this->db->dbprefix('sale_items')}.quantity) as total_pieces, SUM({$this->db->dbprefix('sale_items')}.subtotal) as total_amount 
                ", false)
                ->from('sale_items')
                ->join('sales', 'sales.id=sale_items.sale_id', 'left')
                ->join('products', 'products.id=sale_items.product_id', 'left')
                ->group_by('sale_items.product_id')
                // ->order_by("{$this->db->dbprefix('sale_items')}.product_code",'DESC')
            ;
            //->join($si, 'FSI.sale_id=sales.id', 'left')
            //->join('warehouses', 'warehouses.id=sales.warehouse_id', 'left')   
            $this->datatables->where("{$this->db->dbprefix('sales')}.shop", 1);
            $this->db->where("{$this->db->dbprefix('sales')}.sale_status", "Completed");
            $keyword = trim($this->input->post('keyword'));
            if (!empty($keyword)) {
                $this->db->group_start();
                $this->datatables->where("{$this->db->dbprefix('products')}.code", $keyword);
                $this->db->or_like("{$this->db->dbprefix('products')}.name", $keyword, 'both');
                $this->db->group_end();
            }

            if ($start_date) {
                $this->datatables->where('DATE(' . $this->db->dbprefix('sales') . '.date) BETWEEN "' . $start_date . '" and "' . $end_date . '"');
            }
            // $this->db->order_by("total_pieces",'DESC');  
            $action = '';
            $action .= '<li><a href="' . base_url() . 'assets/uploads/$2" data-type="image" data-toggle="lightbox"><i class="fa fa-file-photo-o"></i> '
                . lang('view_image') . '</a></li>';
            echo $this->datatables->generate();
        }
    }

    public function sales()
    {
        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('sales_report')]];
        $meta = ['page_title' => lang('sales_report'), 'bc' => $bc];
        $this->page_construct('reports/sales', $meta, $this->data);
    }

    public function staff_report($user_id = null, $year = null, $month = null, $pdf = null, $cal = 0)
    {
        $this->sma->checkPermissions('staff', true);
        if (!$user_id) {
            $this->session->set_flashdata('error', lang('no_user_selected'));
            admin_redirect('reports/users');
        }
        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');
        $this->data['purchases'] = $this->reports_model->getStaffPurchases($user_id);
        $this->data['sales'] = $this->reports_model->getStaffSales($user_id);
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        if (!$year) {
            $year = date('Y');
        }
        if (!$month || $month == '#monthly-con') {
            $month = date('m');
        }
        if ($pdf) {
            if ($cal) {
                $this->monthly_sales($year, $pdf, $user_id);
            } else {
                $this->daily_sales($year, $month, $pdf, $user_id);
            }
        }
        $config = [
            'show_next_prev' => true,
            'next_prev_url' => admin_url('reports/staff_report/' . $user_id),
            'month_type' => 'long',
            'day_type' => 'long',
        ];

        $config['template'] = '{table_open}<div class="table-responsive"><table border="0" cellpadding="0" cellspacing="0" class="table print-table table-bordered dfTable reports-table">{/table_open}
        {heading_row_start}<tr>{/heading_row_start}
        {heading_previous_cell}<th class="text-center"><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
        {heading_title_cell}<th class="text-center" colspan="{colspan}" id="month_year">{heading}</th>{/heading_title_cell}
        {heading_next_cell}<th class="text-center"><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}
        {heading_row_end}</tr>{/heading_row_end}
        {week_row_start}<tr>{/week_row_start}
        {week_day_cell}<td class="cl_wday">{week_day}</td>{/week_day_cell}
        {week_row_end}</tr>{/week_row_end}
        {cal_row_start}<tr class="days">{/cal_row_start}
        {cal_cell_start}<td class="day">{/cal_cell_start}
        {cal_cell_content}
        <div class="day_num">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content}
        {cal_cell_content_today}
        <div class="day_num highlight">{day}</div>
        <div class="content">{content}</div>
        {/cal_cell_content_today}
        {cal_cell_no_content}<div class="day_num">{day}</div>{/cal_cell_no_content}
        {cal_cell_no_content_today}<div class="day_num highlight">{day}</div>{/cal_cell_no_content_today}
        {cal_cell_blank}&nbsp;{/cal_cell_blank}
        {cal_cell_end}</td>{/cal_cell_end}
        {cal_row_end}</tr>{/cal_row_end}
        {table_close}</table></div>{/table_close}';

        $this->load->library('calendar', $config);
        $sales = $this->reports_model->getStaffDailySales($user_id, $year, $month);

        if (!empty($sales)) {
            foreach ($sales as $sale) {
                $daily_sale[$sale->date] = "<table class='table print-table table-bordered table-hover table-striped table-condensed data' style='margin:0;'><tr><td>" . lang('discount') . '</td><td>' . $this->sma->formatMoney($sale->discount) . '</td></tr><tr><td>' . lang('product_tax') . '</td><td>' . $this->sma->formatMoney($sale->tax1) . '</td></tr><tr><td>' . lang('order_tax') . '</td><td>' . $this->sma->formatMoney($sale->tax2) . '</td></tr><tr><td>' . lang('total') . '</td><td>' . $this->sma->formatMoney($sale->total) . '</td></tr></table>';
            }
        } else {
            $daily_sale = [];
        }
        $this->data['calender'] = $this->calendar->generate($year, $month, $daily_sale);
        if ($this->input->get('pdf')) {
        }
        $this->data['year'] = $year;
        $this->data['month'] = $month;
        $this->data['msales'] = $this->reports_model->getStaffMonthlySales($user_id, $year);
        $this->data['user_id'] = $user_id;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('staff_report')]];
        $meta = ['page_title' => lang('staff_report'), 'bc' => $bc];
        $this->page_construct('reports/staff_report', $meta, $this->data);
    }

    public function suggestions()
    {
        $term = $this->input->get('term', true);
        if (strlen($term) < 1) {
            die();
        }
        $term = addslashes($term);
        $rows = $this->reports_model->getProductNames($term);
        if ($rows) {
            foreach ($rows as $row) {
                $pr[] = ['id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')'];
            }
            $this->sma->send_json($pr);
        } else {
            echo false;
        }
    }

    public function supplier_report($user_id = null)
    {
        $this->sma->checkPermissions('suppliers', true);
        if (!$user_id) {
            $this->session->set_flashdata('error', lang('no_supplier_selected'));
            admin_redirect('reports/suppliers');
        }

        $this->data['purchases'] = $this->reports_model->getPurchasesTotals($user_id);
        $this->data['total_purchases'] = $this->reports_model->getSupplierPurchases($user_id);
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();

        $this->data['error'] = validation_errors() ? validation_errors() : $this->session->flashdata('error');

        $this->data['user_id'] = $user_id;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('suppliers_report')]];
        $meta = ['page_title' => lang('suppliers_report'), 'bc' => $bc];
        $this->page_construct('reports/supplier_report', $meta, $this->data);
    }

    public function suppliers()
    {
        $this->sma->checkPermissions('suppliers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('suppliers_report')]];
        $meta = ['page_title' => lang('suppliers_report'), 'bc' => $bc];
        $this->page_construct('reports/suppliers', $meta, $this->data);
    }

    public function daily_stats(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $date = $this->input->post('date') ? $this->input->post('date') : null;

        if ($date) {
            $response = $this->reports_model->getUserStats($date);
            $this->data['date'] = $date;
            $this->data['user_stats'] = $response['user_stats'];
            $this->data['daily_stats'] = $response['daily_stats'];
            $this->data['order_stats'] = $response['order_stats'];
            $this->data['social_stats'] = $response['social_stats'];
        }

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('daily_stats')]];
        $meta = ['page_title' => lang('daily_stats'), 'bc' => $bc];
        $this->page_construct('reports/daily_stats', $meta, $this->data);  
    }

    public function general_ledger_trial_balance()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $department = $this->input->post('department') ? $this->input->post('department') : null;
        $employee = $this->input->post('employee') ? $this->input->post('employee') : null;

        $this->data['employees'] = $this->site->getAllEmployees();
        $this->data['departments'] = $this->site->getAllDepartments();
        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $trial_balance_array = $this->reports_model->getGeneralLedgerTrialBalance($start_date, $end_date, $department, $employee);

            foreach ($trial_balance_array['trs'] as $supplier_data) {

                $idExists = false;
                foreach ($response_arr as $response_item) {
                    if ($response_item->id == $supplier_data->id) {
                        $idExists = true;
                        if ($supplier_data->dc == 'D') {
                            $response_item->trs_debit = $response_item->trs_debit + $supplier_data->total_amount; //$this->sma->formatDecimal($response_item->trs_debit + $supplier_data->total_amount);
                        } else if ($supplier_data->dc == 'C') {
                            $response_item->trs_credit = $response_item->trs_credit + $supplier_data->total_amount; //$this->sma->formatDecimal($response_item->trs_credit + $supplier_data->total_amount);
                        }
                        break;
                    }
                }
                // check object exists or not
                if (!$idExists) {
                    $obj = new stdClass();
                    $obj->id = $supplier_data->id;
                    $obj->name = $supplier_data->name;
                    $obj->code = $supplier_data->code;
                    $obj->notes = $supplier_data->notes;
                    $obj->trs_debit = 0;
                    $obj->trs_credit = 0;
                    $obj->ob_debit = 0;
                    $obj->ob_credit = 0;
                    $obj->total_trs_credit = 0;
                    $obj->total_trs_debit = 0;
                    if ($supplier_data->dc == 'D') {
                        $obj->trs_debit = $supplier_data->total_amount; //$this->sma->formatDecimal($supplier_data->total_amount);
                    } else if ($supplier_data->dc == 'C') {
                        $obj->trs_credit = $supplier_data->total_amount; //$this->sma->formatDecimal($supplier_data->total_amount);
                    }
                    array_push($response_arr, $obj);
                }
            }

            foreach ($trial_balance_array['ob'] as $supplier_data) {
                $idExists = false;
                foreach ($response_arr as $response_item) {
                    if ($response_item->id == $supplier_data->id) {
                        $idExists = true;
                        if ($supplier_data->dc == 'D') {
                            $response_item->ob_debit = $supplier_data->total_amount;//$this->sma->formatDecimal($supplier_data->total_amount);
                        } else if ($supplier_data->dc == 'C') {
                            $response_item->ob_credit = $supplier_data->total_amount; //$this->sma->formatDecimal($supplier_data->total_amount);
                        }
                        break;
                    }
                }

                // check object exists or not
                if (!$idExists) {
                    $obj = new stdClass();
                    $obj->id = $supplier_data->id;
                    $obj->name = $supplier_data->name;
                    $obj->code = $supplier_data->code;
                    $obj->notes = $supplier_data->notes;
                    $obj->trs_debit = 0;
                    $obj->trs_credit = 0;
                    $obj->ob_debit = 0;
                    $obj->ob_credit = 0;
                    $obj->total_trs_credit = 0;
                    $obj->total_trs_debit = 0;
                    if ($supplier_data->dc == 'D') {
                        $obj->ob_debit = $supplier_data->total_amount; //$this->sma->formatDecimal($supplier_data->total_amount);
                    } else if ($supplier_data->dc == 'C') {
                        $obj->ob_credit = $supplier_data->total_amount; //$this->sma->formatDecimal($supplier_data->total_amount);
                    }
                    array_push($response_arr, $obj);
                }
            }

            // Final Response array
            foreach ($response_arr as $resp_arr) {
                if ($resp_arr->ob_debit >= $resp_arr->ob_credit) {
                    $resp_arr->ob_debit = $resp_arr->ob_debit - $resp_arr->ob_credit;
                    $resp_arr->ob_credit = 0;
                } else if ($resp_arr->ob_credit > $resp_arr->ob_debit) {
                    $resp_arr->ob_credit = $resp_arr->ob_credit - $resp_arr->ob_debit;
                    $resp_arr->ob_debit = 0;
                }

                if ($resp_arr->trs_debit >= $resp_arr->trs_credit) {
                    $resp_arr->total_trs_debit = $resp_arr->trs_debit - $resp_arr->trs_credit;
                    //$resp_arr->trs_credit = 0;
                } else if ($resp_arr->trs_credit > $resp_arr->trs_debit) {
                    $resp_arr->total_trs_credit = $resp_arr->trs_credit - $resp_arr->trs_debit;
                    //$resp_arr->trs_debit = 0;
                }

                $resp_arr->eb_debit = $resp_arr->ob_debit + $resp_arr->total_trs_debit;
                $resp_arr->eb_credit = $resp_arr->ob_credit + $resp_arr->total_trs_credit;

                if ($resp_arr->eb_debit >= $resp_arr->eb_credit) {
                    $resp_arr->eb_debit = $resp_arr->eb_debit - $resp_arr->eb_credit;
                    $resp_arr->eb_credit = 0;
                } else if ($resp_arr->eb_credit > $resp_arr->eb_debit) {
                    $resp_arr->eb_credit = $resp_arr->eb_credit - $resp_arr->eb_debit;
                    $resp_arr->eb_debit = 0;
                }
            }

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['department'] = $department;
            $this->data['employee'] = $employee;
            $this->data['trial_balance'] = $response_arr;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('general_ledger_report')]];
            $meta = ['page_title' => lang('general_ledger_report'), 'bc' => $bc];

            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('general_ledger_trial_balance') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/general_ledger_trial_balance', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/general_ledger_trial_balance', $meta, $this->data);
            }




        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('general_ledger_report')]];
            $meta = ['page_title' => lang('general_ledger_report'), 'bc' => $bc];
            $this->page_construct('reports/general_ledger_trial_balance', $meta, $this->data);
        }
    }

    public function customer_statement()
    {
        $this->sma->checkPermissions('customers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;

        $this->data['customers'] = $this->site->getAllCompanies('customer');

        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $supplier_id = $this->input->post('customer');

            $supplier_details = $this->companies_model->getCompanyByID($supplier_id);
            // print_r($supplier_details);exit;
            $ledger_account = $supplier_details->ledger_account;
            $supplier_statement = $this->reports_model->getCustomerStatement($start_date, $end_date, $supplier_id, $ledger_account);

            $total_ob = 0;
            $total_ob_credit = 0;
            $total_ob_debit = 0;
            $ob_type = '';
            foreach ($supplier_statement['ob'] as $ob) {
                if ($ob->dc == 'D') {
                    $total_ob_debit = $ob->amount;
                } else if ($ob->dc == 'C') {
                    $total_ob_credit = $ob->amount;
                }
            }

            $total_ob = $total_ob_debit - $total_ob_credit;

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['customer_id'] = $supplier_id;
            $this->data['ob_type'] = $ob_type;
            $this->data['total_ob'] = $this->sma->formatDecimal($total_ob);
            $this->data['supplier_statement'] = $supplier_statement['report'];

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('customer_statement')]];
            $meta = ['page_title' => lang('customer_statement'), 'bc' => $bc];

            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('customers_statement_report') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/customers_statement', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/customers_statement', $meta, $this->data);
            }



        } else {
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('customer_statement')]];
            $meta = ['page_title' => lang('customer_statement'), 'bc' => $bc];
            $this->page_construct('reports/customers_statement', $meta, $this->data);
        }
    }

    public function general_ledger_statement()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;

        $this->data['ledgers'] = $this->reports_model->getCompanyLedgers();

        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $ledger_id = $this->input->post('ledger');

            if (!$ledger_id) {
                $this->session->set_flashdata('error', lang('No ledger is selected.'));
                redirect($_SERVER['HTTP_REFERER']);
            }

            $supplier_statement = $this->reports_model->getGeneralLedgerStatement($start_date, $end_date, '', $ledger_id);

            $total_ob = 0;
            $total_ob_credit = 0;
            $total_ob_debit = 0;
            $ob_type = '';
            foreach ($supplier_statement['ob'] as $ob) {
                if ($ob->dc == 'D') {
                    $total_ob_debit = $ob->total_amount;
                } else if ($ob->dc == 'C') {
                    $total_ob_credit = $ob->total_amount;
                }
            }

            $total_ob = $total_ob_debit - $total_ob_credit;

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['supplier_id'] = $supplier_id;
            $this->data['ob_type'] = $ob_type;
            $this->data['ledger_id'] = $ledger_id;
            $this->data['total_ob'] = $this->sma->formatDecimal($total_ob);
            $this->data['supplier_statement'] = $supplier_statement['report'];

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('general_ledger_statement')]];
            $meta = ['page_title' => lang('general_ledger_statement'), 'bc' => $bc];

            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('general_ledger_statement') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/general_ledger_statement', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/general_ledger_statement', $meta, $this->data);
            }

        } else {
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('general_ledger_statement')]];
            $meta = ['page_title' => lang('general_ledger_statement'), 'bc' => $bc];
            $this->page_construct('reports/general_ledger_statement', $meta, $this->data);
        }
    }

    public function supplier_statement()
    {
        $this->sma->checkPermissions('suppliers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;

        $this->data['suppliers'] = $this->site->getAllCompanies('supplier');

        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $supplier_id = $this->input->post('supplier');


            $supplier_details = $this->companies_model->getCompanyByID($supplier_id);
            $ledger_account = $supplier_details->ledger_account;
            $supplier_statement = $this->reports_model->getSupplierStatement($start_date, $end_date, $supplier_id, $ledger_account);

            $total_ob = 0;
            $total_ob_credit = 0;
            $total_ob_debit = 0;
            $ob_type = '';

            foreach ($supplier_statement['ob'] as $ob) {
                if ($ob->dc == 'D') {
                    $total_ob_debit += $ob->amount;
                } else if ($ob->dc == 'C') {
                    $total_ob_credit += $ob->amount;
                }
            }

            $total_ob = $total_ob_credit - $total_ob_debit;

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['supplier_id'] = $supplier_id;
            $this->data['ob_type'] = $ob_type;
            $this->data['total_ob_credit'] = $total_ob_credit;
            $this->data['total_ob_debit'] = $total_ob_debit;
            $this->data['total_ob'] = $this->sma->formatDecimal($total_ob);
            $this->data['supplier_statement'] = $supplier_statement['report'];

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('supplier_statement')]];
            $meta = ['page_title' => lang('supplier_statement'), 'bc' => $bc];

            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('suppliers_statement_report') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/suppliers_statement', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/suppliers_statement', $meta, $this->data);
            }


        } else {
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('supplier_statement')]];
            $meta = ['page_title' => lang('supplier_statement'), 'bc' => $bc];
            $this->page_construct('reports/suppliers_statement', $meta, $this->data);
        }
    }

    public function customer_aging()
    {
        $this->sma->checkPermissions('customers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $duration = $this->input->post('duration') ? $this->input->post('duration') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $response_arr = array();

        $customer_id_array = array();
        if (!empty($this->input->post('customer'))) {
            $customer_id_array = $this->input->post('customer');
        }

        $this->data['customers'] = $this->site->getAllCompanies('customer');
        $response_arr = array();
        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
        }

        if ($duration) {
            $supplier_aging_array = $this->reports_model->getCustomerAging($duration, $start_date, $customer_id_array);
        } else {
            $supplier_aging_array = $this->reports_model->getCustomerAging($duration = 120, $start_date, $customer_id_array);
        }

        $this->data['customer_id_array'] = $customer_id_array;
        $this->data['start_date'] = $this->input->post('from_date');

        $this->data['supplier_aging'] = $supplier_aging_array;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('customers_aging')]];
        $meta = ['page_title' => lang('customers_aging'), 'bc' => $bc];
        if ($viewtype == 'pdf') {
            $this->data['viewtype'] = $viewtype;
            $name = lang('customers_aging_report') . '.pdf';
            $html = $this->load->view($this->theme . 'reports/customers_aging', $this->data, true);
            $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
        } else {
            $this->page_construct('reports/customers_aging', $meta, $this->data);
        }

    }

    public function supplier_aging()
    {
        $this->sma->checkPermissions('suppliers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $duration = $this->input->post('duration') ? $this->input->post('duration') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $supplier_id_array = array();
        if (!empty($this->input->post('supplier'))) {
            $supplier_id_array = $this->input->post('supplier');
        }
        $this->data['suppliers'] = $this->site->getAllCompanies('supplier');
        $response_arr = array();
        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
        }

        if ($duration) {

            $supplier_aging_array = $this->reports_model->getSupplierAging($duration, $start_date, $supplier_id_array);
        } else {
            $supplier_aging_array = $this->reports_model->getSupplierAging($duration = 120, $start_date, $supplier_id_array);
        }
        $this->data['supplier_id_array'] = $supplier_id_array;
        $this->data['start_date'] = $this->input->post('from_date');
        $this->data['supplier_aging'] = $supplier_aging_array;
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('suppliers_aging')]];
        $meta = ['page_title' => lang('suppliers_aging'), 'bc' => $bc];
        if ($viewtype == 'pdf') {
            $this->data['viewtype'] = $viewtype;
            $name = lang('suppliers_aging') . '.pdf';
            $html = $this->load->view($this->theme . 'reports/suppliers_aging', $this->data, true);
            $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
        } else {
            $this->page_construct('reports/suppliers_aging', $meta, $this->data);
        }
    }

    public function daily_sales_with_promo_code_by_order(){

        $this->sma->checkPermissions('suppliers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
      
        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            
            $response_arr = $this->reports_model->get_sales_report_with_promocode_by_order($start_date, $end_date);
            
            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['coupon_data']  =  $response_arr;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('promo_code_report')]];
            $meta = ['page_title' => lang('promo_code_report'), 'bc' => $bc];
            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('promo_sale_report') . '.pdf';
                $html = $this->load->view($this->theme . 'r', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/promo_sale_report_by_order', $meta, $this->data);
            }
        }else{
                    
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('promo_code_report')]];
            $meta = ['page_title' => lang('promo_code_report'), 'bc' => $bc];
            $this->page_construct('reports/promo_sale_report_by_order', $meta, $this->data);
        }
    }

    public function daily_sales_with_promo_code(){

        $this->sma->checkPermissions('suppliers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
      
        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            
            $response_arr = $this->reports_model->get_sales_report_with_promocode($start_date, $end_date);
            
            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['coupon_data']  =  $response_arr;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('promo_code_report')]];
            $meta = ['page_title' => lang('promo_code_report'), 'bc' => $bc];
            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('promo_sale_report') . '.pdf';
                $html = $this->load->view($this->theme . 'r', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/promo_sale_report', $meta, $this->data);
            }
        }else{
                    
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('promo_code_report')]];
            $meta = ['page_title' => lang('promo_code_report'), 'bc' => $bc];
            $this->page_construct('reports/promo_sale_report', $meta, $this->data);
        }
    }

    public function suppliers_trial_balance()
    {
        $this->sma->checkPermissions('suppliers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            //$trial_balance_array = $this->reports_model->getSuppliersTrialBalance($start_date, $end_date);
            $response_arr = $this->reports_model->get_suppliers_trial_balance($start_date, $end_date);

            // $response_arr = array();
            // foreach ($trial_balance_array['trs'] as $trans) {
            //     $response_arr[$trans->id]["id"] = $trans->id;
            //     $response_arr[$trans->id]["sequence_code"] = $trans->sequence_code;
            //     $response_arr[$trans->id]["name"] = $trans->name;
            //     $response_arr[$trans->id]["trsDebit"] = $trans->totalPayment + $trans->totalReturn + $trans->totalMemo;
            //     $response_arr[$trans->id]["trsCredit"] = $trans->totalPurchases + $trans->totalTaxes;
            // }
            // foreach ($trial_balance_array['ob'] as $obtrans) {
            //     $response_arr[$obtrans->id]["obDebit"] = $obtrans->totalPayment + $obtrans->totalReturn + $obtrans->totalMemo;
            //     $response_arr[$obtrans->id]["obCredit"] = $obtrans->totalPurchases + $obtrans->totalTaxes;
            // }

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['customer_data'] = $response_arr;
            $this->data['trial_balance'] = $response_arr;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('suppliers_report')]];
            $meta = ['page_title' => lang('suppliers_report'), 'bc' => $bc];

            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('suppliers_trial_balance_report') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/suppliers_trial_balance', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/suppliers_trial_balance', $meta, $this->data);
            }

        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('suppliers_report')]];
            $meta = ['page_title' => lang('suppliers_report'), 'bc' => $bc];
            $this->page_construct('reports/suppliers_trial_balance', $meta, $this->data);
        }
    }

    public function financial_position()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $date = $this->input->post('date') ? $this->input->post('date') : null;
        if ($date) {
            $ledger_groups = $this->reports_model->getPLLedgerGroups();
            $income_balance = $this->reports_model->getIncome($date);
            $expense_balance = $this->reports_model->getExpense($date);

            foreach ($ledger_groups as $ledger_group) {
                if (!isset($ledger_group->ledgers)) {
                    $ledger_group->ledgers = array();
                }

                foreach ($income_balance as $income) {
                    if ($ledger_group->id == $income->group_id) {
                        array_push($ledger_group->ledgers, $income);
                    }
                }

                foreach ($expense_balance as $expense) {
                    if ($ledger_group->id == $expense->group_id) {
                        array_push($ledger_group->ledgers, $expense);
                    }
                }
            }

            $response_arr['income_balance'] = $income_balance;
            $response_arr['expense_balance'] = $expense_balance;
            $response_arr['ledger_groups'] = $ledger_groups;
            $this->data['date'] = $date;
            $this->data['financial_position'] = $response_arr;

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('financial_position')]];
            $meta = ['page_title' => lang('financial_position'), 'bc' => $bc];
            $this->page_construct('reports/financial_position', $meta, $this->data);
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('financial_position')]];
            $meta = ['page_title' => lang('financial_position'), 'bc' => $bc];
            $this->page_construct('reports/financial_position', $meta, $this->data);
        }
    }

    public function balance_sheet()
    {
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $date = $this->input->post('date') ? $this->input->post('date') : null;
        if ($date) {
            $ledger_groups = $this->reports_model->getLedgerGroups();
            $assets_balance = $this->reports_model->getAssetsBalance($date);
            $liabilities_balance = $this->reports_model->getLiabilitiesBalance($date);
            $equity_balance = $this->reports_model->getEquityBalance($date);

            //print_r($liabilities_balance);exit;
            foreach ($ledger_groups as $ledger_group) {
                if (!isset($ledger_group->ledgers)) {
                    $ledger_group->ledgers = array();
                }

                foreach ($assets_balance as $asset_balance) {
                    if ($ledger_group->id == $asset_balance->group_id) {
                        array_push($ledger_group->ledgers, $asset_balance);
                    }
                }

                foreach ($liabilities_balance as $liability_balance) {
                    if ($ledger_group->id == $liability_balance->group_id) {
                        array_push($ledger_group->ledgers, $liability_balance);
                    }
                }

                foreach ($equity_balance as $equity) {
                    if ($ledger_group->id == $equity->group_id) {
                        array_push($ledger_group->ledgers, $equity);
                    }
                }
            }

            $response_arr['assets'] = $assets_balance;
            $response_arr['liabilities'] = $liabilities_balance;
            $response_arr['equity'] = $equity_balance;
            $response_arr['ledger_groups'] = $ledger_groups;
            $this->data['date'] = $date;
            $this->data['balance_sheet'] = $response_arr;

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('balance_sheet')]];
            $meta = ['page_title' => lang('balance_sheet'), 'bc' => $bc];

            if ($viewtype == 'pdf') {  // pdf generation 
                $this->data['viewtype'] = $viewtype;
                $name = lang('balance_sheet') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/balance_sheet', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/balance_sheet', $meta, $this->data);
            }
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('balance_sheet')]];
            $meta = ['page_title' => lang('balance_sheet'), 'bc' => $bc];
            if ($viewtype == 'pdf') {  // pdf generation 
                $this->data['viewtype'] = $viewtype;
                $name = lang('balance_sheet') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/balance_sheet', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/balance_sheet', $meta, $this->data);
            }

        }
    }

    public function customers_trial_balance()
    {
        $this->sma->checkPermissions('customers');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            //$trial_balance_array = $this->reports_model->getCustomersTrialBalance($start_date, $end_date);
            $trial_balance_array = $this->reports_model->get_customer_trial_balance($start_date, $end_date);
            // echo "<pre>";
            // print_r($trial_balance_array);
            // exit;
            $response_arr = array();
            /**OLD LOGIC */
            // foreach ($trial_balance_array['trs'] as $trans) {
            //     $response_arr[$trans->id]["name"] = $trans->name;
            //     $response_arr[$trans->id]["company"] = $trans->company;
            //     $response_arr[$trans->id]["sequence_code"] = $trans->sequence_code;
            //     $response_arr[$trans->id]["trsDebit"] = $trans->payment_total + $trans->sale_total;
            //     $response_arr[$trans->id]["trsCredit"] =  $trans->return_total + $trans->memo_total;
            // }


            // foreach ($trial_balance_array['ob'] as $trans) {
            //     $response_arr[$trans->id]["obDebit"] = $trans->payment_total + $trans->sale_total;
            //     $response_arr[$trans->id]["obCredit"] =  $trans->return_total + $trans->memo_total;
            // }
            /**END OLD LOGIC */

            foreach ($trial_balance_array['trs'] as $trans) {
                $response_arr[$trans->id]["name"] = $trans->name;
                $response_arr[$trans->id]["company"] = $trans->company;
                $response_arr[$trans->id]["sequence_code"] = $trans->sequence_code;
                $response_arr[$trans->id]["trsDebit"] = $trans->total_debit;
                $response_arr[$trans->id]["trsCredit"] = $trans->total_credit;
            }


            foreach ($trial_balance_array['ob'] as $trans) {
                $response_arr[$trans->id]["name"] = $trans->name;
                $response_arr[$trans->id]["company"] = $trans->company;
                $response_arr[$trans->id]["sequence_code"] = $trans->sequence_code;
                $response_arr[$trans->id]["obDebit"] = $trans->total_debit;
                $response_arr[$trans->id]["obCredit"] = $trans->total_credit;
            }

            //dd($response_arr);


            // foreach($trial_balance_array['trs'] as $supplier_data){

            //     $idExists = false;
            //     foreach ($response_arr as $response_item) {
            //         if ($response_item->id == $supplier_data->id) {
            //             $idExists = true;
            //             if($supplier_data->dc == 'D'){
            //                 $response_item->trs_debit = $response_item->trs_debit + $supplier_data->total_amount;
            //             }else if($supplier_data->dc == 'C'){
            //                 $response_item->trs_credit = $response_item->trs_credit + $supplier_data->total_amount;
            //             }
            //             break;
            //         }
            //     }
            //     // check object exists or not
            //     if(!$idExists){
            //         $obj = new stdClass();
            //         $obj->id = $supplier_data->id;
            //         $obj->name = $supplier_data->company;
            //         $obj->ledger_account = $supplier_data->ledger_account;
            //         $obj->trs_debit = 0;
            //         $obj->trs_credit = 0;
            //         $obj->ob_debit = 0;
            //         $obj->ob_credit = 0;
            //         if($supplier_data->dc == 'D'){
            //             $obj->trs_debit = $supplier_data->total_amount;
            //         }else if($supplier_data->dc == 'C'){
            //             $obj->trs_credit = $supplier_data->total_amount;
            //         }
            //         array_push($response_arr, $obj);  
            //     }
            // }

            // foreach($trial_balance_array['ob'] as $supplier_data){
            //     foreach ($response_arr as $response_item) {
            //         if ($response_item->id == $supplier_data->id) {
            //             if($supplier_data->dc == 'D'){
            //                 $response_item->ob_debit = $supplier_data->total_amount;
            //             }else if($supplier_data->dc == 'C'){
            //                 $response_item->ob_credit = $supplier_data->total_amount;
            //             }

            //         }
            //     }
            // }

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['customer_data'] = $trial_balance_array;
            $this->data['trial_balance'] = $response_arr;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('customers_report')]];
            $meta = ['page_title' => lang('customers_report'), 'bc' => $bc];

            if ($viewtype == 'pdf') {  // for download pdf 
                $this->data['viewtype'] = $viewtype;
                $name = lang('customers_trial_balance') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/customers_trial_balance', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');

            } else {
                $this->page_construct('reports/customers_trial_balance', $meta, $this->data);
            }
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('customers_report')]];
            $meta = ['page_title' => lang('customers_report'), 'bc' => $bc];
            $this->page_construct('reports/customers_trial_balance', $meta, $this->data);
        }
    }

    public function item_movement_report_xls($productId, $type, $startDate, $endDate, $xls)
    {

        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');


        if ($productId && $startDate && $endDate) {
            $start_date = $startDate;
            $end_date = $endDate;

            $user = $this->site->getUser();
            $defaultWareHouseId = ($user->warehouse_id ? $user->warehouse_id : $this->site->Settings->default_warehouse);

            $itemOpenings = $this->reports_model->getItemOpeningBalance($productId, $start_date, $defaultWareHouseId);
            $reportData = $this->reports_model->getItemMovementRecords($productId, $start_date, $end_date, $defaultWareHouseId, $type);

            // Expire Date	Batch No.	Sale Price	Purchase Price	Quantity	Unit Cost	Item balance quantity	Value of item current balance

            if (!empty($reportData)) {
                $this->load->library('excel');
                $this->excel->setActiveSheetIndex(0);
                $this->excel->getActiveSheet()->setTitle(lang('item_movement_report'));
                $this->excel->getActiveSheet()->SetCellValue('A1', lang('Date'));
                $this->excel->getActiveSheet()->SetCellValue('B1', lang('Document No'));
                $this->excel->getActiveSheet()->SetCellValue('C1', lang('Type'));
                $this->excel->getActiveSheet()->SetCellValue('D1', lang('Name Of'));
                $this->excel->getActiveSheet()->SetCellValue('E1', lang('Expire Date'));
                $this->excel->getActiveSheet()->SetCellValue('F1', lang('Batch No.'));
                $this->excel->getActiveSheet()->SetCellValue('G1', lang('Sale Price'));
                $this->excel->getActiveSheet()->SetCellValue('H1', lang('Purchase Price'));
                $this->excel->getActiveSheet()->SetCellValue('I1', lang('Quantity'));
                $this->excel->getActiveSheet()->SetCellValue('J1', lang('Unit Cost'));
                $this->excel->getActiveSheet()->SetCellValue('K1', lang('Item balance quantity'));
                $this->excel->getActiveSheet()->SetCellValue('L1', lang('Value of item current balance'));

                $row = 2;
                $this->excel->getActiveSheet()->SetCellValue('A' . $row, 'Opening Balance');
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->sma->formatMoney($itemOpenings->unitPrice, 'none'));
                $this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->sma->formatQuantity(($itemOpenings->openingBalance > 0 ? $itemOpenings->openingBalance : 0.00)));
                $this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->sma->formatMoney(($itemOpenings->openingBalance > 0 && $itemOpenings->unitPrice > 0 ? $itemOpenings->openingBalance * $itemOpenings->unitPrice : 0.00), 'none'));


                $balanceQantity = $itemOpenings->openingBalance;
                $row = 3;
                $name = null;
                foreach ($reportData as $data_row) {

                    $name = $data_row->id . '-' . $data_row->name . '(' . $data_row->code . ')';

                    if ($data_row->type == 'Purchase' || $data_row->type == 'Return-Customer' || $data_row->type == "Transfer-In") {
                        $balanceQantity += $data_row->quantity;
                    }
                    if (($data_row->type == 'Sale' || $data_row->type == 'Return-Supplier' || $data_row->type == "Transfer-Out") && $balanceQantity > 0) {
                        $balanceQantity -= $data_row->quantity;
                    }

                    if ($data_rowrp->type == 'Transfer-Out' || $data_row->type == "Transfer-In") {
                        $type = 'Transfer';
                    } else {
                        $type = $data_row->type;
                    }

                    $this->excel->getActiveSheet()->SetCellValue('A' . $row, $data_row->entry_date);
                    $this->excel->getActiveSheet()->SetCellValue('B' . $row, $data_row->document_no);
                    $this->excel->getActiveSheet()->SetCellValue('C' . $row, $type);
                    $this->excel->getActiveSheet()->SetCellValue('D' . $row, $data_row->name_of);
                    $this->excel->getActiveSheet()->SetCellValue('E' . $row, $data_row->expiry_date);
                    $this->excel->getActiveSheet()->SetCellValue('F' . $row, $data_row->batch_no);
                    $this->excel->getActiveSheet()->SetCellValue('G' . $row, $this->sma->formatMoney(($data_row->sale_price ? $data_row->sale_price : 0.0), 'none'));
                    $this->excel->getActiveSheet()->SetCellValue('H' . $row, $this->sma->formatMoney(($data_row->purchase_price ? $data_row->purchase_price : 0.0), 'none'));
                    $this->excel->getActiveSheet()->SetCellValue('I' . $row, $this->sma->formatQuantity($data_row->quantity ? $data_row->quantity : 0.0));
                    $this->excel->getActiveSheet()->SetCellValue('J' . $row, $this->sma->formatMoney(($data_row->unit_cost ? $data_row->unit_cost : 0.0), 'none'));
                    $this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->sma->formatQuantity($balanceQantity ? $balanceQantity : 0.0));
                    $this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->sma->formatMoney(($balanceQantity * $data_row->unit_cost), 'none'));

                    $row++;
                }

                $this->excel->getActiveSheet()->SetCellValue('A' . $row, 'Closing Balance');
                $this->excel->getActiveSheet()->SetCellValue('B' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('C' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('D' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('E' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('F' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('G' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('H' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('I' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('J' . $row, '');
                $this->excel->getActiveSheet()->SetCellValue('K' . $row, $this->sma->formatQuantity($balanceQantity));
                $this->excel->getActiveSheet()->SetCellValue('L' . $row, $this->sma->formatMoney($balanceQantity * $itemOpenings->unitPrice, 'none'));



                $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
                $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(25);
                $this->excel->getActiveSheet()->getColumnDimension('L')->setWidth(25);

                $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                $filename = $name . '-' . date('Y-m-d') . '-item_movement_report';
                $this->load->helper('excel');
                create_excel($this->excel, $filename);
            }
            $this->session->set_flashdata('error', lang('nothing_found'));
            redirect($_SERVER['HTTP_REFERER']);

        } else {
            redirect($_SERVER['HTTP_REFERER']);
            return null;
        }

    }

    public function item_movement_report()
    {

        //$this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $filterOnTypeArr = [
            "" => "-- ALL --",
            "purchase" => "Purchases",
            "sale" => "Sales",
            "customer_return" => "Return Customer",
            "return_to_supplier" => "Return Supplier",
            "transfer_in" => "Transfer In",
            "transfer_out" => "Transfer Out"
        ];
        $this->data['filterOnTypeArr'] = $filterOnTypeArr;
        $user = $this->site->getUser();
        $warehouse = $this->input->post('warehouse') ? $this->input->post('warehouse') : null;

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $productId = $this->input->post('product') ? $this->input->post('product') : 0;
        $filterOnType = $this->input->post('filterOnType') ? $this->input->post('filterOnType') : null;
        $document_number = $this->input->post('document_number') ? $this->input->post('document_number') : null;
        $this->data['start_date'] = $from_date;
        $this->data['end_date'] = $to_date;
        $this->data['product'] = $productId;
        $this->data['filterOnType'] = $filterOnType;
        $this->data['document_number'] = $document_number;
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($productId && $from_date && $to_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);

            $itemOpenings = $this->reports_model->getItemOpeningBalance($productId, $start_date, $warehouse);

            $reportData = $this->reports_model->getItemMovementRecords($productId, $start_date, $end_date, $warehouse, $filterOnType, $document_number);

            
            $this->data['itemOpenings'] = $itemOpenings;
            $this->data['reportData'] = $reportData;

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('item_movement_report')]];
            $meta = ['page_title' => lang('item_movement_report'), 'bc' => $bc];
            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('item_movement_report') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/item_movement_report', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/item_movement_report', $meta, $this->data);
            }

        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('item_movement_report')]];
            $meta = ['page_title' => lang('item_movement_report'), 'bc' => $bc];
            $this->page_construct('reports/item_movement_report', $meta, $this->data);
        }
    }

    public function inventory_trial_balance()
    {

        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $user = $this->site->getUser();
        $defaultWareHouseId = ($user->warehouse_id ? $user->warehouse_id : $this->site->Settings->default_warehouse);

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $from_warehouse_id = $this->input->post('from_warehouse_id') ? $this->input->post('from_warehouse_id') : 0;
        $to_warehouse_id = $this->input->post('to_warehouse_id') ? $this->input->post('to_warehouse_id') : 0;

        $allWareHouses = $this->site->getAllWarehouses();
        $filteredWareHouses = ["-- ALL --"];
        foreach ($allWareHouses as $warehouse) {
            if ($warehouse->goods_in_transit == 0) {
                $filteredWareHouses[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';
            }
        }
        $this->data['warehouses'] = $filteredWareHouses;


        if ($from_date && $to_date) {


            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);

            if ($from_warehouse_id == 0) {
                $from_warehouse_id = $defaultWareHouseId;
            }

            $inventryReportData = [];
            //$productOpeningsData = $this->reports_model->getProductsQuantityUnitCost($start_date,$from_warehouse_id);

            $productInOutData = $this->reports_model->getInventoryTrialBalance($start_date, $end_date, $from_warehouse_id, $to_warehouse_id);

            // foreach($productInOutData as $prdId => $row){

            //     $productCost = 1;
            //     if(count($productOpeningsData) > 0&& array_key_exists($prdId, $productOpeningsData)){

            //         $productOpenQty      = $productOpeningsData[$prdId]['total_opening_qty'];
            //         $productOpenUnitCost = $productOpeningsData[$prdId]['avg_unit_cost'];
            //         $productOpenValue    = $productOpenQty * $productOpenUnitCost;
            //     }else{

            //         $productOpenQty      = 0.00;
            //         $productOpenUnitCost = 0.00;
            //         $productOpenValue    = 0.00;
            //     }

            //     if($productOpenUnitCost){
            //         //$productCost = $productOpenUnitCost;
            //         $productCost = $row->movement_out_cost;
            //     }else{
            //         //$productCost = $row->movement_in_cost;
            //         $productCost = $row->movement_out_cost;
            //     }

            //     $inventryReportData[] = [
            //         'product_id'          =>  $row->product_id,
            //         'product_name'        =>  $row->product_name,
            //         'product_code'        =>  $row->product_code,
            //         'openning_qty'        =>  $productOpenQty,
            //         'openning_cost'       =>  $productOpenUnitCost,
            //         'openning_ttl'        =>  $productOpenValue,
            //         'movement_in_qty'     =>  $row->movement_in_quantity,
            //         'movement_in_cost'    =>  $row->movement_in_cost,
            //         'movement_in_ttl'     =>  $row->movement_in_quantity * $row->movement_in_cost,
            //         'movement_out_qty'    =>  $row->movement_out_quantity,
            //         'movement_out_cost'   =>  $row->movement_out_cost,
            //         'movement_out_ttl'    =>  $row->movement_out_quantity * $row->movement_out_cost,

            //         'closing_qty'        =>  ($productOpenQty + $row->movement_in_quantity) - $row->movement_out_quantity,
            //         'closing_cost'       =>  $productCost,
            //         'closing_ttl'        =>  (($productOpenQty + $row->movement_in_quantity) - $row->movement_out_quantity) * $productCost

            //     ];
            // }

            // echo '<pre>', print_r($inventryReportData), '</pre>';
            // $reportData = $this->reports_model->getInventoryTrialBalance($start_date, $end_date, $from_warehouse_id, $to_warehouse_id);

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['from_warehouse_id'] = $from_warehouse_id;
            $this->data['to_warehouse_id'] = $to_warehouse_id;
            $this->data['inventryReportData'] = $productInOutData;


            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('inventory_trial_balance')]];
            $meta = ['page_title' => lang('inventory_trial_balance'), 'bc' => $bc];
            if ($viewtype == 'pdf') { // for generating PDF
                $this->data['viewtype'] = $viewtype;
                $name = lang('inventory_trial_balance') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/inventory_trial_balance', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/inventory_trial_balance', $meta, $this->data);
            }
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('inventory_trial_balance')]];
            $meta = ['page_title' => lang('item_movement_report'), 'bc' => $bc];
            $this->page_construct('reports/inventory_trial_balance', $meta, $this->data);
        }
    }


    public function inventory_movement()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;

        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);

            $inventory_array = $this->reports_model->getInventoryMovementReport($start_date, $end_date);
            // echo '<pre>';print_r($inventory_array); echo "</pre>";

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['vat_purchase'] = $inventory_array;

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('inventory_movement_report')]];
            $meta = ['page_title' => lang('inventory_movement_report'), 'bc' => $bc];

            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('inventory_movement_report') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/inventory_movement_report', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'L');
            } else {
                $this->page_construct('reports/inventory_movement_report', $meta, $this->data);
            }

        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('inventory_movement_report')]];
            $meta = ['page_title' => lang('inventory_movement_report'), 'bc' => $bc];
            $this->page_construct('reports/inventory_movement_report', $meta, $this->data);
        }
    }

    public function inventory_movementBK()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $inventory_array = $this->reports_model->getInventoryMovementReport($start_date, $end_date);
            $response_arr = array();
            echo '<pre>';
            print_r($inventory_array);
            exit;
            foreach ($inventory_array['period'] as $inventory_data) {
                print_r($inventory_data);
                exit;
                /*foreach ($response_arr as $response_item) {
                    if ($response_item->id == $supplier_data->id) {
                        if($supplier_data->dc == 'D'){
                            $response_item->ob_debit = $this->sma->formatDecimal($supplier_data->total_amount);
                        }else if($supplier_data->dc == 'C'){
                            $response_item->ob_credit = $this->sma->formatDecimal($supplier_data->total_amount);
                        }
                        
                    }
                }*/
            }

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['vat_purchase'] = $vat_purchase_array;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('inventory_movement_report')]];
            $meta = ['page_title' => lang('inventory_movement_report'), 'bc' => $bc];
            $this->page_construct('reports/inventory_movement_report', $meta, $this->data);
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('inventory_movement_report')]];
            $meta = ['page_title' => lang('inventory_movement_report'), 'bc' => $bc];
            $this->page_construct('reports/inventory_movement_report', $meta, $this->data);
        }
    }

    public function vat_purchase()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');


        $filterOnTypeArr = [
            "" => "-- Select Type --",
            "purchases" => "Purchases",
            "returnSupplier" => "Return to Supplier",
            "serviceInvoice" => "Service Invoice"
        ];
        $this->data['filterOnTypeArr'] = $filterOnTypeArr;

        $allWareHouses = $this->site->getAllWarehouses();
        $filteredWareHouses = [];
        $filteredWareHouses[] = '-- All --';
        foreach ($allWareHouses as $warehouse) {

            $filteredWareHouses[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';

        }
        $this->data['warehouses'] = $filteredWareHouses;

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $warehouse_id = $this->input->post('warehouse_id') ? $this->input->post('warehouse_id') : null;
        $filterOnType = $this->input->post('filterOnType') ? $this->input->post('filterOnType') : null;

        if ($from_date && $to_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $vat_purchase_array = $this->reports_model->getVatPurchaseReport($start_date, $end_date, $warehouse_id, $filterOnType);

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['vat_purchase'] = $vat_purchase_array;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('vat_purchase_report')]];
            $meta = ['page_title' => lang('vat_purchase_report'), 'bc' => $bc];

            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('vat_purchase_report') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/vat_purchase_report', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/vat_purchase_report', $meta, $this->data);
            }
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('vat_purchase_report')]];
            $meta = ['page_title' => lang('vat_purchase_report'), 'bc' => $bc];
            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('vat_purchase_report') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/vat_purchase_report', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/vat_purchase_report', $meta, $this->data);
            }

        }
    }

    public function vat_sale()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');


        $filterOnTypeArr = [
            "" => "-- Select Type --",
            "sale" => "Sale",
            "returnCustomer" => "Return From Customer",
            "serviceInvoice" => "Service Invoice"
        ];
        $this->data['filterOnTypeArr'] = $filterOnTypeArr;

        $allWareHouses = $this->site->getAllWarehouses();
        $filteredWareHouses = [];
        $filteredWareHouses[] = '-- All --';
        foreach ($allWareHouses as $warehouse) {

            $filteredWareHouses[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';

        }
        $this->data['warehouses'] = $filteredWareHouses;

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $warehouse_id = $this->input->post('warehouse_id') ? $this->input->post('warehouse_id') : null;
        $filterOnType = $this->input->post('filterOnType') ? $this->input->post('filterOnType') : null;
        if ($viewtype == 'pdf') {
            $this->data['viewtype'] = $viewtype;
        }
        if ($from_date && $to_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $vat_purchase_array = $this->reports_model->getVatSaleReport($start_date, $end_date, $warehouse_id, $filterOnType);

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['vat_purchase'] = $vat_purchase_array;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('Vat Sale Report')]];
            $meta = ['page_title' => lang('Vat Sale Report'), 'bc' => $bc];

            if ($viewtype == 'pdf') {
                $name = lang('vat') . '_' . 'sale_report' . '.pdf';
                $html = $this->load->view($this->theme . 'reports/vat_sale_report', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/vat_sale_report', $meta, $this->data);
            }
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('Vat Sale Report')]];
            $meta = ['page_title' => lang('Vat Sale Report'), 'bc' => $bc];
            $this->page_construct('reports/vat_sale_report', $meta, $this->data);
        }


    }
    public function vat_sale_pdf()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');


        $filterOnTypeArr = [
            "" => "-- Select Type --",
            "sale" => "Sale",
            "returnCustomer" => "Return From Customer",
            "serviceInvoice" => "Service Invoice"
        ];
        $this->data['filterOnTypeArr'] = $filterOnTypeArr;

        $allWareHouses = $this->site->getAllWarehouses();
        $filteredWareHouses = [];
        $filteredWareHouses[] = '-- All --';
        foreach ($allWareHouses as $warehouse) {

            $filteredWareHouses[$warehouse->id] = $warehouse->name . ' (' . $warehouse->code . ')';

        }
        $this->data['warehouses'] = $filteredWareHouses;

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $warehouse_id = $this->input->post('warehouse_id') ? $this->input->post('warehouse_id') : null;
        $filterOnType = $this->input->post('filterOnType') ? $this->input->post('filterOnType') : null;

        if ($viewtype == 'pdf') {
            $this->data['viewtype'] = $viewtype;
        }
        if ($from_date && $to_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $vat_purchase_array = $this->reports_model->getVatSaleReport($start_date, $end_date, $warehouse_id, $filterOnType);

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['vat_purchase'] = $vat_purchase_array;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('Vat Sale Report')]];
            $meta = ['page_title' => lang('Vat Sale Report'), 'bc' => $bc];
            // $this->page_construct('reports/vat_sale_report', $meta, $this->data);
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('Vat Sale Report')]];
            $meta = ['page_title' => lang('Vat Sale Report'), 'bc' => $bc];
            // $this->page_construct('reports/vat_sale_report', $meta, $this->data);
        }

        $name = lang('vat') . '_' . 'sale_report' . '.pdf';
        $html = $this->load->view($this->theme . 'reports/vat_sale_report', $this->data, true);
        //echo $html;exit;
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
        // generate_pdf($content, $name = 'download.pdf', $output_type = null, $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'P')

    }


    public function vat_purchase_ledger()
    {
        $this->sma->checkPermissions();
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $vat_purchase_array = $this->reports_model->getVatPurchaseLedgerReport($start_date, $end_date);

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['vat_purchase'] = $vat_purchase_array;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('vat_purchase_report')]];
            $meta = ['page_title' => lang('vat_purchase_report'), 'bc' => $bc];

            if ($viewtype == 'pdf') {
                $this->data['viewtype'] = $viewtype;
                $name = lang('vat_purchase_ledger_report') . '.pdf';
                $html = $this->load->view($this->theme . 'reports/vat_purchase_ledger_report', $this->data, true);
                $this->sma->generate_pdf($html, $name, 'I', '', $footer = null, $margin_bottom = null, $header = null, $margin_top = null, $orientation = 'Pl');
            } else {
                $this->page_construct('reports/vat_purchase_ledger_report', $meta, $this->data);
            }
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('vat_purchase_report')]];
            $meta = ['page_title' => lang('vat_purchase_report'), 'bc' => $bc];
            $this->page_construct('reports/vat_purchase_ledger_report', $meta, $this->data);
        }
    }

    public function tax()
    {
        $this->sma->checkPermissions();
        $start_date = $this->input->post('start_date') ? $this->input->post('start_date') : null;
        $end_date = $this->input->post('end_date') ? $this->input->post('end_date') : null;
        if ($start_date) {
            $start_date = $this->sma->fld($start_date);
            $end_date = $this->sma->fld($end_date);
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $this->data['sale_tax'] = $this->reports_model->getSalesTax($start_date, $end_date);
        $this->data['purchase_tax'] = $this->reports_model->getPurchasesTax($start_date, $end_date);
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('tax_report')]];
        $meta = ['page_title' => lang('tax_report'), 'bc' => $bc];
        $this->page_construct('reports/tax', $meta, $this->data);
    }

    public function users()
    {
        $this->sma->checkPermissions('staff');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('staff_report')]];
        $meta = ['page_title' => lang('staff_report'), 'bc' => $bc];
        $this->page_construct('reports/users', $meta, $this->data);
    }



    // public function incentives()
    // {
    //     $this->data['users']      = $this->reports_model->getStaff();
    //     $this->data['incentives']= "incentives";
    //     $this->data['error']  = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
    //     $bc                   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('staff_report')]];
    //     $meta                 = ['page_title' => lang('staff_report'), 'bc' => $bc];
    //     $this->page_construct('reports/incentives', $meta, $this->data);
    // }


    public function incentives()
    {
        $this->data['incentives'] = "incentive-screen";

        $this->sma->checkPermissions('sales');
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['billers'] = $this->site->getAllCompanies('biller');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('Pharmacist_Incentice_Report')]];
        $meta = ['page_title' => lang('sales_report'), 'bc' => $bc];
        $this->page_construct('reports/incentives', $meta, $this->data);
    }


    public function getIncentives()
    {
        $this->data['incentives'] = "incentive";
        $this->data['users'] = $this->reports_model->getStaff();

        $user = $this->input->post('user');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        if (!empty($user)) {

            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $this->sma->fld($end_date);
            }


            $this->db->select('p.*,SUM(t.quantity) as total_quantity,SUM(t.net_unit_price) as total_price', false)
                ->from('sale_items t')
                ->join('products p', 'p.id = t.product_id', 'full')
                ->join('sales s', 's.id = t.sale_id', 'full')
                ->where('s.created_by', $user)
                ->where('p.incentive_qty IS NOT NULL', NULL)
                ->group_by('t.product_id');


            if ($start_date && $start_date != "0000-00-00 00:00:00") {
                $this->db->where('s.date >=', $start_date);


                if ($end_date && $end_date != "0000-00-00 00:00:00") {
                    $this->db->where('s.date <=', $end_date);
                }
            }
            $this->data['products'] = $this->db->get()->result();
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('staff_report')]];
        $meta = ['page_title' => lang('staff_report'), 'bc' => $bc];
        $this->page_construct('reports/incentives', $meta, $this->data);
    }

    public function departmental_incentive()
    {
        $this->data['incentives'] = "incentive";
        $this->data['users'] = $this->reports_model->getStaff();
        $this->data['categories'] = $this->reports_model->getAllCategories();

        $user = $this->input->post('user');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        if (!empty($user)) {

            if ($start_date) {
                $start_date = $this->sma->fld($start_date);
                $end_date = $this->sma->fld($end_date);
            }


            $this->db->select('p.*,SUM(t.quantity) as total_quantity,SUM(t.net_unit_price) as total_price', false)
                ->from('sale_items t')
                ->join('products p', 'p.id = t.product_id', 'full')
                ->join('sales s', 's.id = t.sale_id', 'full')
                ->where('s.created_by', $user)
                ->where('p.incentive_qty IS NOT NULL', NULL)
                ->group_by('t.product_id');


            if ($start_date && $start_date != "0000-00-00 00:00:00") {
                $this->db->where('s.date >=', $start_date);


                if ($end_date && $end_date != "0000-00-00 00:00:00") {
                    $this->db->where('s.date <=', $end_date);
                }
            }
            $this->data['products'] = $this->db->get()->result();
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('staff_report')]];
        $meta = ['page_title' => lang('staff_report'), 'bc' => $bc];
        $this->page_construct('reports/dep_incentives', $meta, $this->data);
    }

    public function warehouse_stock($warehouse = null)
    {
        $this->sma->checkPermissions('index', true);
        $data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->input->get('warehouse')) {
            $warehouse = $this->input->get('warehouse');
        }

        $this->data['stock'] = $warehouse ? $this->reports_model->getWarehouseStockValue($warehouse) : $this->reports_model->getStockValue();
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['warehouse_id'] = $warehouse;
        $this->data['warehouse'] = $warehouse ? $this->site->getWarehouseByID($warehouse) : null;
        $this->data['totals'] = $this->reports_model->getWarehouseTotals($warehouse);
        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('reports')]];
        $meta = ['page_title' => lang('reports'), 'bc' => $bc];
        $this->page_construct('reports/warehouse_stock', $meta, $this->data);
    }

    public function collections_by_pharmacy(){
      
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $warehouse = $this->input->post('pharmacy') ? $this->input->post('pharmacy') : null;
        //print_r($this->input->post());
    
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $collections_data = $this->reports_model->getCollectionsByPharmacy($start_date, $end_date, $warehouse);
            //echo "<pre>";
            //print_r($collections_data);exit;

            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['warehouse'] = $warehouse;
            $this->data['collections_data'] = $collections_data;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('collection_pharmacy')]];
            $meta = ['page_title' => lang('collection_pharmacy'), 'bc' => $bc];

          
            $this->page_construct('reports/collection_pharmacy', $meta, $this->data);
         
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('reports')]];
           $meta = ['page_title' => lang('reports'), 'bc' => $bc];
           $this->page_construct('reports/collection_pharmacy', $meta, $this->data);


        }

    }

    public function sales_by_category(){
      
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $warehouse = $this->input->post('pharmacy') ? $this->input->post('pharmacy') : null;
        //print_r($this->input->post());
        if( strtolower($warehouse) == 'all' || $warehouse  == '' )
        {
            $warehouse = '';
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($from_date && $to_date ) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $sales_data = $this->reports_model->getSalesByCategory($start_date, $end_date, $warehouse);
           // echo "<pre>"; print_r($sales_data);exit;
            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['warehouse'] = $warehouse;
            $this->data['sales_data'] = $sales_data;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('sales_by_category')]];
            $meta = ['page_title' => lang('sales_by_category'), 'bc' => $bc];

          
            $this->page_construct('reports/sales_by_category', $meta, $this->data);
         
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('reports')]];
           $meta = ['page_title' => lang('reports'), 'bc' => $bc];
           $this->page_construct('reports/sales_by_category', $meta, $this->data);


        }

    }

    public function sales_by_item(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $warehouse = $this->input->post('pharmacy') ? $this->input->post('pharmacy') : null;
        //print_r($this->input->post());
        if( strtolower($warehouse) == 'all' || $warehouse  == '' )
        {
            $warehouse = '';
        }
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($from_date) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $response_data = $this->reports_model->getSalesByItems($start_date, $end_date, $warehouse);
            //echo "<pre>"; print_r($sales_data);exit;
            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['warehouse'] = $warehouse;
            $this->data['response_data'] = $response_data;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('sales_by_category')]];
            $meta = ['page_title' => lang('sales_by_item'), 'bc' => $bc];

          
            $this->page_construct('reports/sales_by_item', $meta, $this->data);
         
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('reports')]];
           $meta = ['page_title' => lang('reports'), 'bc' => $bc];
           $this->page_construct('reports/sales_by_item', $meta, $this->data);


        }

    }

    public function pharmacist_comission(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $warehouse = $this->input->post('pharmacy') ? $this->input->post('pharmacy') : null;
        $pharmacist = $this->input->post('pharmacist') ? $this->input->post('pharmacist') : null;
        //print_r($this->input->post());
    
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['pharmacists'] = $this->site->getAllPharmacists();
        if ($from_date && $to_date && $warehouse && $pharmacist) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $commission_data = $this->reports_model->getPharmacistsCommission($start_date, $end_date, $warehouse, $pharmacist);
           // echo "<pre>"; print_r($commission_data);exit;
            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['warehouse'] = $warehouse;
            $this->data['pharmacist'] = $pharmacist;
            $this->data['commission_data'] = $commission_data;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('pharmacist_commission')]];
            $meta = ['page_title' => lang(''), 'bc' => $bc];

          
            $this->page_construct('reports/pharmacist_comission', $meta, $this->data);
         
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('reports')]];
           $meta = ['page_title' => lang('reports'), 'bc' => $bc];
           $this->page_construct('reports/pharmacist_comission', $meta, $this->data);


        }

    }

    public function transfer_items_monthly_wise(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $from_pharmacy = $this->input->post('frompharmacy') ? $this->input->post('frompharmacy') : null;
        $to_pharmacy = $this->input->post('topharmacy') ? $this->input->post('topharmacy') : null;
        //print_r($this->input->post());
    
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        if ($from_date && $to_date && $from_pharmacy && $to_pharmacy) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $response_data = $this->reports_model->getTransferItemsMonthlyWise($start_date, $end_date, $from_pharmacy, $to_pharmacy);
            //echo "<pre>"; print_r($response_data);exit;
            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['from_pharmacy'] = $from_pharmacy;
            $this->data['to_pharmacy'] = $to_pharmacy;
            $this->data['response_data'] = $response_data;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('Tranfer Items Monthly Wise')]];
            $meta = ['page_title' => lang(''), 'bc' => $bc];

          
            $this->page_construct('reports/transfer_items_monthly_wise', $meta, $this->data);
         
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('reports')]];
           $meta = ['page_title' => lang('reports'), 'bc' => $bc];
           $this->page_construct('reports/transfer_items_monthly_wise', $meta, $this->data);


        }

    }

    public function get_item_deatils(){
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->get('viewtype') ? $this->input->get('viewtype') : null;
        $year = $this->input->get('year') ? $this->input->get('year') : null;
        $month = $this->input->get('month') ? $this->input->get('month') : null;
        $from_date = $this->input->get('from_date') ? $this->input->get('from_date') : null;
        $to_date = $this->input->get('to_date') ? $this->input->get('to_date') : null;
        $from_pharmacy = $this->input->get('from_pharmacy') ? $this->input->get('from_pharmacy') : null;
        $to_pharmacy = $this->input->get('to_pharmacy') ? $this->input->get('to_pharmacy') : null;
        //print_r( $this->input->get());
        if ($from_date && $to_date && $from_pharmacy && $to_pharmacy) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);
            $response_data = $this->reports_model->getTransferItemsDetailsMonthlyWise($year, $month, $start_date, $end_date, $from_pharmacy, $to_pharmacy);
            //echo "<pre>response"; print_r($response_data);exit;
            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['from_pharmacy'] = $from_pharmacy;
            $this->data['to_pharmacy'] = $to_pharmacy;
            $this->data['response_data'] = $response_data;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('Tranfer Items Monthly Wise')]];
            $meta = ['page_title' => lang(''), 'bc' => $bc];

          
            $this->load->view($this->theme . 'reports/items_transfer_details', $this->data);
         
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('reports')]];
           $meta = ['page_title' => lang('reports'), 'bc' => $bc];
           $this->load->view($this->theme . 'reports/items_transfer_details', $this->data);

        }

    }

    public function close_register_details(){
        // error_reporting(-1);
		// ini_set('display_errors', 1);
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $response_arr = array();
        $viewtype = $this->input->post('viewtype') ? $this->input->post('viewtype') : null;
        $from_date = $this->input->post('from_date') ? $this->input->post('from_date') : null;
        $to_date = $this->input->post('to_date') ? $this->input->post('to_date') : null;
        $warehouse = $this->input->post('pharmacy') ? $this->input->post('pharmacy') : null;
        $pharmacist_id = $this->input->post('pharmacist_id') ? $this->input->post('pharmacist_id') : null;
        //print_r($this->input->post());
         //for testing purpose
         /*$user_id ='';
         if($warehouse == 34) {
            $user_id = 6653 ;
         } else if($warehouse == 37) {
            $user_id = 6655 ;
         }*/

        if ($this->Owner || $this->Admin || $this->PurchaseManager) {
            if($warehouse != null){
                $user_data = $this->site->getUserByWarehouseID($warehouse);
                if($pharmacist_id){
                    if($pharmacist_id != 'all'){
                        $user_id = $pharmacist_id;
                    }else{
                        $pharmacist_group = $this->site->getUserGroupByName('pharmacist')->id;
                        $all_pharmacy_pharmacists = $this->site->getUsersByGroupAndLocation($warehouse,$pharmacist_group);
                        $user_id = $all_pharmacy_pharmacists;
                    }
                }else{
                    $user_id = $user_data->id;
                }
                
            }
        }else{
            $warehouse = $this->session->userdata('warehouse_id');
            if($pharmacist_id){
                $user_id = $pharmacist_id;
            }else{
                $user_id = $this->session->userdata('user_id');
            }
        }   

        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['user_group'] = $this->site->getUserGroupByName('pharmacist');
        $this->data['pharmacists'] = $this->site->getUsersByGroup($this->data['user_group']->id);
        
        if ($from_date && $to_date && $warehouse) {
            $start_date = $this->sma->fld($from_date);
            $end_date = $this->sma->fld($to_date);

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
            $register_open_time = $start_date;
            $register_end_time = $end_date;

            //$response_data = $this->reports_model->getCloseRegisterDetails($start_date, $end_date, $warehouse);
            //$this->data['chsales']         = $this->pos_model->getRegisterChSales($register_open_time, $user_id);
            //$this->data['gcsales']         = $this->pos_model->getRegisterGCSales($register_open_time);
            //$this->data['pppsales']        = $this->pos_model->getRegisterPPPSales($register_open_time, $user_id);
            //$this->data['stripesales']     = $this->pos_model->getRegisterStripeSales($register_open_time, $user_id);
             //$this->data['refunds']         = $this->pos_model->getRegisterRefunds($register_open_time, $user_id);
            //$this->data['returns']         = $this->pos_model->getRegisterReturns($register_open_time, $user_id);
            //$this->data['cashrefunds']     = $this->pos_model->getRegisterCashRefunds($register_open_time, $user_id);
            //$this->data['expenses']        = $this->pos_model->getRegisterExpenses($register_open_time, $user_id);
            //$this->data['users']           = $this->pos_model->getUsers($user_id);
            //$this->data['suspended_bills'] = $this->pos_model->getSuspendedsales($user_id);
              //$this->data['authorizesales']  = $this->pos_model->getRegisterAuthorizeSales($register_open_time, $user_id);
            
            $this->data['ccsales']         = $this->pos_model->getRegisterCCSales($register_open_time, $user_id, $register_end_time);
            $this->data['cashsales']       = $this->pos_model->getRegisterCashSales($register_open_time, $user_id, $register_end_time);
            $this->data['othersales']      = $this->pos_model->getRegisterOtherSales($register_open_time);
            $this->data['totalsales']      = $this->pos_model->getRegisterSales($register_open_time, $user_id, $register_end_time);
            $this->data['totalreturns']    = $this->pos_model->getRegisterReturnsNew($register_open_time, $user_id, $register_end_time);
           
            $this->data['user_id']         = $user_id;
            //echo "<pre>"; print_r($response_data);exit;
            $this->data['start_date'] = $from_date;
            $this->data['end_date'] = $to_date;
            $this->data['warehouse_id'] = $warehouse;
            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('reports'), 'page' => lang('reports')], ['link' => '#', 'page' => lang('Close Register Details')]];
            $meta = ['page_title' => lang(''), 'bc' => $bc];

          
            $this->page_construct('reports/close_register_details', $meta, $this->data);
         
        } else {

            $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('reports')]];
           $meta = ['page_title' => lang('reports'), 'bc' => $bc];
           $this->page_construct('reports/close_register_details', $meta, $this->data);
        }
    }
}
