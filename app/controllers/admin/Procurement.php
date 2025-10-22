<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Mpdf\Mpdf;

class Procurement extends MY_Controller {

    public function __construct()
    { 
        //error_reporting(E_ALL);        // Report all errors
        //ini_set('display_errors', 1); 
        parent::__construct();
        $this->load->admin_model('users_model');
        $this->load->admin_model('pr_audit_logs_model');
        $this->load->admin_model('purchase_requisition_model');
    }

    /**
     * Procuremnt main dashboard
     */
    public function dashboard() {
       
        $this->data['id'] = 1;
          $this->data['total_suppliers']         = 128;
        $this->data['total_pr']                = 240;
        $this->data['total_pr_amount']         = 458000.75;
        $this->data['total_po']                = 192;
        $this->data['total_po_amount']         = 396500.25;
        $this->data['total_invoices']          = 165;
        $this->data['total_invoice_amount']    = 372000.00;

        // Supplier-wise invoices
        $this->data['supplier_invoices'] = [
            ['supplier_name' => 'Pharma Supply Co.', 'invoice_count' => 20, 'total_amount' => 54000],
            ['supplier_name' => 'Gulf Medical Ltd.', 'invoice_count' => 14, 'total_amount' => 42000],
            ['supplier_name' => 'HealthPlus Trading', 'invoice_count' => 18, 'total_amount' => 49000],
            ['supplier_name' => 'Global Distributors', 'invoice_count' => 12, 'total_amount' => 38000],
        ];

        // Monthly invoice trends
        $this->data['monthly_invoices'] = [
            ['month' => 'Jan', 'invoice_count' => 12, 'total_amount' => 31000],
            ['month' => 'Feb', 'invoice_count' => 15, 'total_amount' => 34000],
            ['month' => 'Mar', 'invoice_count' => 18, 'total_amount' => 36000],
            ['month' => 'Apr', 'invoice_count' => 20, 'total_amount' => 41000],
            ['month' => 'May', 'invoice_count' => 22, 'total_amount' => 46000],
            ['month' => 'Jun', 'invoice_count' => 19, 'total_amount' => 44000],
            ['month' => 'Jul', 'invoice_count' => 24, 'total_amount' => 47000],
            ['month' => 'Aug', 'invoice_count' => 21, 'total_amount' => 45000],
            ['month' => 'Sep', 'invoice_count' => 17, 'total_amount' => 42000],
            ['month' => 'Oct', 'invoice_count' => 15, 'total_amount' => 40000],
        ];

        // Weekly VAT invoices
        $this->data['weekly_vat_invoices'] = [
            ['week' => 'Week 1', 'invoice_count' => 12, 'vat_amount' => 2100],
            ['week' => 'Week 2', 'invoice_count' => 10, 'vat_amount' => 1800],
            ['week' => 'Week 3', 'invoice_count' => 9, 'vat_amount' => 1500],
            ['week' => 'Week 4', 'invoice_count' => 11, 'vat_amount' => 2000],
        ];
        //print_r( $this->data['suppliers']); exit;
        $meta = ['page_title' => 'View Requisition', 'bc' => [['link' => admin_url('purchase_requisition'), 'page' => 'Purchase Requisitions'], ['link' => '#', 'page' => 'View Requisition']]];
        $this->page_construct('procurement/dashboard', $meta, $this->data);

    }



}
