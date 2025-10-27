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
        // Fetch total suppliers
        $this->data['total_suppliers'] = $this->db->count_all('sma_companies');

        // Fetch total purchase orders and their amount
        $this->data['total_po'] = $this->db->count_all('sma_purchases');
        $this->data['total_po_amount'] = $this->db->select_sum('grand_total')->get('sma_purchases')->row()->grand_total;

        // Fetch total invoices and their amount
        $this->data['total_invoices'] = $this->db->where('status', 'completed')->count_all_results('sma_purchases');
        $this->data['total_invoice_amount'] = $this->db->select_sum('grand_total')->where('status', 'completed')->get('sma_purchases')->row()->grand_total;

        // Supplier-wise expenditure
        $this->data['supplier_invoices'] = $this->db->select('supplier, COUNT(id) as invoice_count, SUM(grand_total) as total_amount')
            ->group_by('supplier')
            ->order_by('total_amount', 'DESC')
            ->limit(5)
            ->get('sma_purchases')
            ->result_array();

        // Monthly expenditure trends
        $this->data['monthly_invoices'] = $this->db->query(
            "SELECT DATE_FORMAT(date, '%b') as month, SUM(grand_total) as total_amount
            FROM sma_purchases
            GROUP BY YEAR(date), MONTH(date)
            ORDER BY MONTH(date)")
            ->result_array();

        // Top suppliers by expenditure
        $this->data['top_suppliers'] = $this->db->select('supplier, SUM(grand_total) as total_expenditure')
            ->group_by('supplier')
            ->order_by('total_expenditure', 'DESC')
            ->limit(5)
            ->get('sma_purchases')
            ->result_array();

        // Quarterly purchase trends
        $this->data['quarterly_trends'] = $this->db->query(
            "SELECT CONCAT('Q', QUARTER(date)) as quarter, SUM(grand_total) as total_amount
            FROM sma_purchases
            GROUP BY YEAR(date), QUARTER(date)")
            ->result_array();

        $meta = ['page_title' => 'Procurement Dashboard', 'bc' => [['link' => admin_url('procurement'), 'page' => 'Procurement'], ['link' => '#', 'page' => 'Dashboard']]];
        $this->page_construct('procurement/dashboard', $meta, $this->data);
    }



}
