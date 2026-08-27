<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounts_dashboard extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Check authentication and permissions
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            admin_redirect('login');
        }
        
        // Load model using admin_model pattern consistent with other admin controllers
        $this->load->admin_model('accounts_dashboard_model');
        $this->load->helper('url');
        $this->load->library('session');
    }
    
    /**
     * Main dashboard view
     */
    public function index() {
        $this->data['page_title'] = 'Accounts Dashboard';
        $this->data['report_type'] = 'ytd'; // Default to year-to-date
        
        // Load template header (includes sidebar)
        $this->load->view($this->theme . 'header', $this->data);
        
        // Load dashboard view
        $this->load->view($this->theme . 'finance/accounts_dashboard', $this->data);
        
        // Load template footer
        $this->load->view($this->theme . 'footer', $this->data);
    }
    
    /**
     * Get dashboard data via AJAX
     */
    public function get_data() {
        // Get parameters
        $report_type = $this->input->get('report_type') ?: 'ytd';
        $reference_date = $this->input->get('reference_date') ?: date('Y-m-d');
        
        // Validate report type
        if (!in_array($report_type, ['ytd', 'monthly', 'today'])) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Invalid report type'
                )));
            return;
        }
        
        // Validate date format (Y-m-d)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $reference_date) || !strtotime($reference_date)) {
            $reference_date = date('Y-m-d');
        }
        
        try {
            // Get data from model
            $dashboard_data = $this->accounts_dashboard_model->get_dashboard_data(
                $report_type,
                $reference_date
            );
            
            // Calculate trends
            $trends = $this->accounts_dashboard_model->calculate_trends(
                $report_type,
                $reference_date
            );
            
            // Format response
            $response = array(
                'success' => true,
                'data' => $dashboard_data,
                'trends' => $trends,
                'report_type' => $report_type,
                'reference_date' => $reference_date
            );
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
                
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Error retrieving dashboard data: ' . $e->getMessage()
                )));
        }
    }
    
    /**
     * Get expanded purchase items list
     */
    public function get_purchase_items_expanded() {
        $report_type = $this->input->get('report_type') ?: 'ytd';
        $reference_date = $this->input->get('reference_date') ?: date('Y-m-d');
        $limit = (int)($this->input->get('limit') ?: 50);
        $offset = (int)($this->input->get('offset') ?: 0);
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $reference_date) || !strtotime($reference_date)) {
            $reference_date = date('Y-m-d');
        }
        
        // Validate pagination params
        $limit = max(1, min($limit, 500)); // Max 500 items per request
        $offset = max(0, $offset);
        
        try {
            $items = $this->accounts_dashboard_model->get_purchase_items_expanded(
                $report_type,
                $reference_date,
                $limit,
                $offset
            );
            
            $response = array(
                'success' => true,
                'data' => $items,
                'limit' => $limit,
                'offset' => $offset
            );
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
                
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Error retrieving purchase items: ' . $e->getMessage()
                )));
        }
    }
    
    /**
     * Export dashboard data to Excel/CSV
     */
    public function export() {
        $report_type = $this->input->get('report_type') ?: 'ytd';
        $reference_date = $this->input->get('reference_date') ?: date('Y-m-d');
        $format = $this->input->get('format') ?: 'csv';
        
        // Validate format
        if (!in_array($format, ['csv', 'json'])) {
            $format = 'csv';
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $reference_date) || !strtotime($reference_date)) {
            $reference_date = date('Y-m-d');
        }
        
        try {
            $dashboard_data = $this->accounts_dashboard_model->get_dashboard_data(
                $report_type,
                $reference_date
            );
            
            if ($format === 'json') {
                // JSON export
                $filename = 'accounts_dashboard_' . $report_type . '_' . date('Y-m-d_His') . '.json';
                
                header('Content-Type: application/json; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                
                echo json_encode($dashboard_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                
            } else {
                // CSV export with proper encoding
                $filename = 'accounts_dashboard_' . $report_type . '_' . date('Y-m-d_His') . '.csv';
                
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                
                $output = fopen('php://output', 'w');
                
                // Add UTF-8 BOM for Excel compatibility
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Export each section
                foreach ($dashboard_data as $section => $data) {
                    if (empty($data)) continue;
                    
                    fputcsv($output, array(strtoupper(str_replace('_', ' ', $section))));
                    
                    if ($section === 'overall_summary') {
                        // Overall summary is a single row
                        fputcsv($output, array_keys($data));
                        fputcsv($output, array_values($data));
                    } else {
                        // Other sections are arrays
                        if (isset($data[0])) {
                            fputcsv($output, array_keys($data[0]));
                        }
                        foreach ($data as $row) {
                            fputcsv($output, $row);
                        }
                    }
                    
                    fputcsv($output, array()); // Empty row separator
                }
                
                fclose($output);
            }
            
        } catch (Exception $e) {
            show_error('Error exporting data: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get summary statistics for widgets
     */
    public function get_summary_stats() {
        $report_type = $this->input->get('report_type') ?: 'ytd';
        $reference_date = $this->input->get('reference_date') ?: date('Y-m-d');
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $reference_date) || !strtotime($reference_date)) {
            $reference_date = date('Y-m-d');
        }
        
        try {
            $dashboard_data = $this->accounts_dashboard_model->get_dashboard_data(
                $report_type,
                $reference_date
            );
            
            $summary = $dashboard_data['overall_summary'];
            
            // Calculate additional metrics with safe division
            $profit_margin = 0;
            if (!empty($summary['total_gross_sales']) && $summary['total_gross_sales'] > 0) {
                $profit_margin = (($summary['total_net_sales'] - $summary['total_purchase']) / 
                                 $summary['total_gross_sales']) * 100;
            }
            
            $collection_rate = 0;
            if (!empty($summary['total_gross_sales']) && $summary['total_gross_sales'] > 0) {
                $collection_rate = ($summary['total_collection'] / $summary['total_gross_sales']) * 100;
            }
            
            $avg_transaction_value = 0;
            if (!empty($summary['total_sales_count']) && $summary['total_sales_count'] > 0) {
                $avg_transaction_value = round($summary['total_gross_sales'] / $summary['total_sales_count'], 2);
            }
            
            $response = array(
                'success' => true,
                'summary' => $summary,
                'metrics' => array(
                    'profit_margin' => round($profit_margin, 2),
                    'collection_rate' => round($collection_rate, 2),
                    'avg_transaction_value' => $avg_transaction_value
                )
            );
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
                
        } catch (Exception $e) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Error retrieving summary stats: ' . $e->getMessage()
                )));
        }
    }
}