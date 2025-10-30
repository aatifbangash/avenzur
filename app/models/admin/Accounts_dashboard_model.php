<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounts_dashboard_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get dashboard data using stored procedure
     * 
     * @param string $report_type ('ytd', 'monthly', 'today')
     * @param string $reference_date (Y-m-d format, defaults to today)
     * @return array Dashboard data with keys: sales_summary, collection_summary, purchase_summary, 
     *               purchase_per_item, expiry_report, customer_summary, overall_summary
     * @throws Exception
     */
    public function get_dashboard_data($report_type = 'ytd', $reference_date = null) {
        if (!in_array($report_type, ['ytd', 'monthly', 'today'])) {
            $report_type = 'ytd';
        }
        
        if (empty($reference_date)) {
            $reference_date = date('Y-m-d');
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $reference_date) || !strtotime($reference_date)) {
            throw new Exception('Invalid reference_date format. Expected Y-m-d');
        }
        
        try {
            // Call stored procedure - it returns 7 result sets
            $query = $this->db->query(
                "CALL sp_get_accounts_dashboard(?, ?)",
                array($report_type, $reference_date)
            );
            
            $results = array(
                'sales_summary' => array(),
                'collection_summary' => array(),
                'purchase_summary' => array(),
                'purchase_per_item' => array(),
                'expiry_report' => array(),
                'customer_summary' => array(),
                'overall_summary' => array()
            );
            
            // First result set - Sales Summary
            if ($query && $query->num_rows() > 0) {
                $results['sales_summary'] = $query->result_array();
            }
            
            // Move to next result set - Collection Summary
            if ($this->db->next_result()) {
                $query = $this->db->use_query_result();
                if ($query && $query->num_rows() > 0) {
                    $results['collection_summary'] = $query->result_array();
                }
            }
            
            // Move to next result set - Purchase Summary
            if ($this->db->next_result()) {
                $query = $this->db->use_query_result();
                if ($query && $query->num_rows() > 0) {
                    $results['purchase_summary'] = $query->result_array();
                }
            }
            
            // Move to next result set - Purchase Per Item
            if ($this->db->next_result()) {
                $query = $this->db->use_query_result();
                if ($query && $query->num_rows() > 0) {
                    $results['purchase_per_item'] = $query->result_array();
                }
            }
            
            // Move to next result set - Expiry Report
            if ($this->db->next_result()) {
                $query = $this->db->use_query_result();
                if ($query && $query->num_rows() > 0) {
                    $results['expiry_report'] = $query->result_array();
                }
            }
            
            // Move to next result set - Customer Summary
            if ($this->db->next_result()) {
                $query = $this->db->use_query_result();
                if ($query && $query->num_rows() > 0) {
                    $results['customer_summary'] = $query->result_array();
                }
            }
            
            // Move to next result set - Overall Summary (single row)
            if ($this->db->next_result()) {
                $query = $this->db->use_query_result();
                if ($query && $query->num_rows() > 0) {
                    $results['overall_summary'] = $query->row_array();
                }
            }
            
            // Close the cursor
            @mysqli_next_result($this->db->conn_id);
            
            return $results;
            
        } catch (Exception $e) {
            throw new Exception('Error calling sp_get_accounts_dashboard: ' . $e->getMessage());
        }
    }
    
    /**
     * Get all purchase items (expanded view)
     * 
     * @param string $report_type ('ytd', 'monthly', 'today')
     * @param string $reference_date (Y-m-d format)
     * @param int $limit Maximum number of records (1-500)
     * @param int $offset Pagination offset
     * @return array Purchase items with aggregated data
     * @throws Exception
     */
    public function get_purchase_items_expanded($report_type = 'ytd', $reference_date = null, $limit = 50, $offset = 0) {
        if (empty($reference_date)) {
            $reference_date = date('Y-m-d');
        }
        
        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $reference_date) || !strtotime($reference_date)) {
            throw new Exception('Invalid reference_date format. Expected Y-m-d');
        }
        
        // Sanitize pagination
        $limit = max(1, min((int)$limit, 500)); // Max 500 items
        $offset = max(0, (int)$offset);
        
        try {
            $date_conditions = $this->get_date_conditions($report_type, $reference_date);
            
            $this->db->select('
                pi.product_id,
                pi.product_code,
                pi.product_name,
                pr.name AS product_full_name,
                pr.category_id,
                SUM(pi.quantity) AS total_quantity,
                SUM(pi.subtotal) AS total_amount,
                COUNT(DISTINCT pi.purchase_id) AS purchase_count,
                AVG(pi.net_unit_cost) AS avg_unit_cost,
                MIN(pi.net_unit_cost) AS min_unit_cost,
                MAX(pi.net_unit_cost) AS max_unit_cost
            ');
            $this->db->from('sma_purchase_items pi');
            $this->db->join('sma_purchases pu', 'pi.purchase_id = pu.id', 'inner');
            $this->db->join('sma_products pr', 'pi.product_id = pr.id', 'left');
            $this->db->where('DATE(pu.date) >=', $date_conditions['start_date']);
            $this->db->where('DATE(pu.date) <=', $date_conditions['end_date']);
            $this->db->where('pu.status !=', 'returned');
            $this->db->group_by('pi.product_id, pi.product_code, pi.product_name, pr.name, pr.category_id');
            $this->db->order_by('total_amount', 'DESC');
            $this->db->limit($limit, $offset);
            
            return $this->db->get()->result_array();
            
        } catch (Exception $e) {
            throw new Exception('Error retrieving purchase items: ' . $e->getMessage());
        }
    }
    
    /**
     * Get date conditions based on report type
     * 
     * @param string $report_type
     * @param string $reference_date
     * @return array
     */
    private function get_date_conditions($report_type, $reference_date) {
        $today = date('Y-m-d', strtotime($reference_date));
        $year_start = date('Y-01-01', strtotime($reference_date));
        $month_start = date('Y-m-01', strtotime($reference_date));
        $month_end = date('Y-m-t', strtotime($reference_date));
        
        switch ($report_type) {
            case 'ytd':
                return array(
                    'start_date' => $year_start,
                    'end_date' => $today
                );
            case 'monthly':
                return array(
                    'start_date' => $month_start,
                    'end_date' => $month_end
                );
            case 'today':
            default:
                return array(
                    'start_date' => $today,
                    'end_date' => $today
                );
        }
    }
    
    /**
     * Get customer details
     * 
     * @param array $customer_ids Array of customer IDs to filter by (optional)
     * @return array Array of customer records with id, name, company, email, phone, vat_no, credit_limit, balance
     */
    public function get_customer_details($customer_ids = array()) {
        $this->db->select('id, name, company, email, phone, vat_no, credit_limit, balance');
        $this->db->from('sma_companies');
        
        if (!empty($customer_ids)) {
            $this->db->where_in('id', $customer_ids);
        }
        
        $this->db->order_by('name', 'ASC');
        
        return $this->db->get()->result_array();
    }
}