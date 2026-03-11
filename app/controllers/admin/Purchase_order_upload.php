<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Purchase Order Upload Controller
 *
 * Handles the upload, parsing, review, and submission of purchase orders via Excel files.
 */
class Purchase_order_upload extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
            
        // permissions (adjust key to your system)
        // example: $this->sma->checkPermissions('po-upload', TRUE);

        $this->load->admin_model('Purchase_order_upload_model', 'pou');
        $this->load->admin_model('Purchase_order_model', 'pom');
        $this->load->library('session');
        $this->load->helper(['url', 'form', 'text']);
    }

    /**
     * Page 1: Upload
     */
    public function index()
    {
        $this->data['page_title'] = 'Upload Purchase Order Excel';
        $this->load->view($this->theme . 'header', $this->data);
        $this->load->view($this->theme . 'purchase_order_upload/upload', $this->data);
        $this->load->view($this->theme . 'footer', $this->data);

    }

    /**
     * Handle upload + parse → store in session → redirect review
     */
    public function parse()
    {
        if (empty($_FILES['excel_file']['name'])) {
            $this->session->set_flashdata('error', 'Please select an Excel file.');
            redirect(admin_url('purchase_order_upload'));
        }

        $date = $this->sma->fld($this->input->post('date'));
        $warehouse_id = (int) $this->input->post('warehouse');
        $supplier_id  = (int) $this->input->post('supplier');
        $config['upload_path']   = FCPATH . 'assets/uploads/excel/';
        $config['allowed_types'] = 'xlsx|xls';
        $config['max_size']      = 10240; // 10MB
        $config['encrypt_name']  = TRUE;
        
        if (!is_dir($config['upload_path'])) {
            @mkdir($config['upload_path'], 0777, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('excel_file')) {
            $this->session->set_flashdata('error', $this->upload->display_errors('', ''));
            redirect(admin_url('purchase_order_upload'));
        }

        $file = $this->upload->data();
        $path = $file['full_path'];

        try {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);
        } catch (Exception $e) {
            @unlink($path);
            $this->session->set_flashdata('error', 'Excel could not be read.');
            redirect(admin_url('purchase_order_upload'));
        }

        if (empty($rows) || count($rows) < 2) {
            @unlink($path);
            $this->session->set_flashdata('error', 'Excel file is empty.');
            redirect(admin_url('purchase_order_upload'));
        }

        $parsed_rows = [];
        $has_errors  = false;
        $row_count   = 0;
        foreach ($rows as $row) {
            if ($row_count == 0) {
                $row_count++;
                continue; // skip header
            }
            $item_barcode        = isset($row[0]) ? trim($row[0]) : '';
            $item_name_en        = isset($row[1]) ? trim($row[1]) : '';
            $variant_barcode     = isset($row[2]) ? trim($row[2]) : '';
            $variant_name        = isset($row[3]) ? trim($row[3]) : '';
            $brand_name          = isset($row[4]) ? trim($row[4]) : '';
            $batch_number        = isset($row[5]) ? trim($row[5]) : '';
            $expiry_date_raw     = isset($row[6]) ? $row[6] : '';

            $quantity            = isset($row[7]) ? (float)$row[7] : 0;
            $sale_price_inc_vat  = isset($row[8]) ? (float)$row[8] : 0;
            $purchase_price      = isset($row[9]) ? (float)$row[9] : 0;
            $cost_price          = isset($row[10]) ? (float)$row[10] : 0;

            $vat_percent         = isset($row[11]) ? (float)$row[11] : 0;

            $discount1_percent   = isset($row[12]) ? (float)$row[12] : 0;
            $discount1_value     = isset($row[13]) ? (float)$row[13] : 0;

            $discount2_percent   = isset($row[14]) ? (float)$row[14] : 0;
            $discount2_value     = isset($row[15]) ? (float)$row[15] : 0;

            $discount3_percent   = isset($row[16]) ? (float)$row[16] : 0;
            $discount3_value     = isset($row[17]) ? (float)$row[17] : 0;

            $description_en      = isset($row[18]) ? trim($row[18]) : '';
            $image_link          = isset($row[19]) ? trim($row[19]) : '';


            // skip empty row
            if (
                trim($item_barcode) === '' &&
                trim($item_name_en) === '' &&
                trim($brand_name) === '' &&
                $quantity == 0 &&
                $purchase_price == 0 &&
                $sale_price_inc_vat == 0
            ) {
                continue;
            }


            // expiry formatting
            $expiry_date = null;

            if (!empty($expiry_date_raw)) {
                if (is_numeric($expiry_date_raw)) {
                    $expiry_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expiry_date_raw)->format('Y-m-d');
                } else {
                    $expiry_date = date('Y-m-d', strtotime($expiry_date_raw));
                }
            }


            // prefer cost price, fallback to purchase price
            $final_cost_price = $cost_price > 0 ? $cost_price : $purchase_price;

            $errors = [];


            // mandatory checks
            if ($item_barcode === '') {
                $errors[] = 'Item barcode is required';
            }

            if ($item_name_en === '') {
                $errors[] = 'Item name is required';
            }

            if ($image_link === '') {
                $errors[] = 'Image link is required';
            }

            if ($brand_name === '') {
                $errors[] = 'Brand name is required';
            }

            if ($quantity <= 0) {
                $errors[] = 'Quantity is required';
            }

            if ($final_cost_price <= 0) {
                $errors[] = 'Cost price is required';
            }

            if ($sale_price_inc_vat <= 0) {
                $errors[] = 'Sale price is required';
            }


            if (!empty($errors)) {
                $has_errors = true;
            }


            $parsed_rows[] = [
                'row_no'            => $row_count + 1,
                'item_barcode'      => $item_barcode,
                'item_name'         => strtolower($item_name_en),
                'variant_barcode'   => $variant_barcode,
                'variant_name'      => $variant_name,
                'batch_number'      => $batch_number,
                'expiry_date'       => $expiry_date,
                'quantity'          => $quantity,
                'sale_price'        => $sale_price_inc_vat,
                'purchase_price'    => $purchase_price,
                'cost_price'        => $final_cost_price,
                'vat_percent'       => $vat_percent,

                'discount1_percent' => $discount1_percent,
                'discount1_value'   => $discount1_value,

                'discount2_percent' => $discount2_percent,
                'discount2_value'   => $discount2_value,

                'discount3_percent' => $discount3_percent,
                'discount3_value'   => $discount3_value,
                'subtotal'        => $purchase_price * $quantity,
                'description_en'    => $description_en,
                'image_link'        => $image_link,
                'brand_name'        => strtolower($brand_name),

                'error'             => $errors,
                'has_error'         => !empty($errors),
            ];


            $row_count++;
        }
        if (!empty($has_errors)) {
            $this->session->set_flashdata('errors', $errors);
            $this->session->set_flashdata('error', 'Some rows have errors. Please correct the Excel file and reupload.');
             redirect(admin_url('purchase_order_upload'));
        }

        $payload = [
            'date'         => $date,
            'warehouse_id' => $warehouse_id,
            'supplier_id'  => $supplier_id,
            'file'         => $path,
            'rows'         => $parsed_rows,
            'has_errors'   => $has_errors,
        ];

        $this->session->set_userdata('po_upload_rows', $parsed_rows);
        $this->session->set_userdata('po_upload_payload', $payload);
        $this->session->set_userdata('po_upload_file', $path);

        redirect(admin_url('purchase_order_upload/review'));
    }


    /**
     * Page 2: Review
     */
    public function review()
    {
        $rows = $this->session->userdata('po_upload_rows');
        if (empty($rows)) {
            $this->session->set_flashdata('error', 'Nothing to review. Please upload file again.');
            redirect(admin_url('purchase_order_upload'));
        }
        $this->data['page_title'] = 'Review Purchase Order Items';
        $this->data['suppliers'] = $this->site->getAllParentCompanies('supplier');
        $this->data['child_suppliers'] = $this->site->getAllChildCompanies('supplier');
        $this->data['warehouses'] = $this->site->getAllWarehouses();
        $this->data['rows'] = $rows;

        $this->load->view($this->theme . 'header', $this->data);
        $this->load->view($this->theme . 'purchase_order_upload/review', $this->data);
        $this->load->view($this->theme . 'footer', $this->data);
    }

    /**
     * Page 3: Submit → Save into DB
     */
    public function submit()
    {
        $rows = $this->session->userdata('po_upload_rows');
        if (empty($rows)) {
            $this->session->set_flashdata('error', 'No parsed rows found. Upload again.');
            redirect(admin_url('purchase_order_upload'));
        }
        if (!empty($rows['has_errors'])) {
            $this->session->set_flashdata('error', 'Some rows have errors. Please correct the Excel file and reupload.');
            redirect(admin_url('purchase_order_upload/review'));
        }
        $rows['date'] = $this->sma->fld($this->input->post('date'));
        $rows['warehouse_id'] = (int) $this->input->post('warehouse');
        $rows['supplier_id'] = (int) $this->input->post('supplier');
        if (!$rows['warehouse_id'] || !$rows['supplier_id']) {
            return [
                'success' => false,
                'error'   => 'Supplier and warehouse are required.'
            ];
        }
        $this->load->admin_model('purchase_order_upload_model');
        $result = $this->pou->save_reviewed_po($rows);
        if ($result['success']) {
            $this->session->unset_userdata('po_upload_rows');
            $this->session->unset_userdata('po_upload_payload');
            $this->session->unset_userdata('po_upload_file');
            $this->session->unset_userdata('po_upload_date');
            $this->session->unset_userdata('po_upload_warehouse_id');
            $this->session->unset_userdata('po_upload_supplier_id');

            $this->data['page_title']   = 'Purchase Order';
            $this->data['purchase_id']  = $result['purchase_id'];
            $this->session->set_flashdata('message', 'Purchase Order Uploaded Successfully');
            $this->load->view($this->theme . 'header', $this->data);
            $this->load->view($this->theme . 'purchase_order_upload/success', $this->data);
            $this->load->view($this->theme . 'footer', $this->data);
        }else {
            $this->session->set_flashdata('error', $result['error']);
            redirect(admin_url('purchase_order_upload/review'));
        }
        
    }

    
    private function _parse_excel_rows($file_path)
    {
        $spreadsheet = IOFactory::load($file_path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();

        $rows = [];
        // start from row 2 (row 1 header)
        for ($r = 2; $r <= $highestRow; $r++) 
        {
            $code       = trim((string)$sheet->getCell("A{$r}")->getValue());
            $name       = trim((string)$sheet->getCell("B{$r}")->getValue());
            $details    = trim((string)$sheet->getCell("C{$r}")->getValue());
            $image      = trim((string)$sheet->getCell("D{$r}")->getValue());
            $brand_name = trim((string)$sheet->getCell("E{$r}")->getValue());
            $qty        = (float)$sheet->getCell("F{$r}")->getCalculatedValue();
            $unit_cost  = (float)$sheet->getCell("G{$r}")->getCalculatedValue();
            $tax_pct    = (float)$sheet->getCell("H{$r}")->getCalculatedValue();

            // skip empty lines
            if ($code === '' && $name === '') {
                continue;
            }

            // defaults
            if ($qty <= 0) $qty = 1;
            if ($unit_cost < 0) $unit_cost = 0;

            $rows[] = [
                'code'       => $code,
                'name'       => $name,
                'details'    => $details,
                'image'      => $image,
                'brand_name' => $brand_name,
                'quantity'   => $qty,
                'unit_cost'  => $unit_cost,
                'tax_percent'=> $tax_pct,
            ];
        }

        return $rows;
    }
}