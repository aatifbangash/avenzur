<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounts_dashboard_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get dashboard data using stored procedure
     * Uses direct MySQLi to handle multiple result sets
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
            // Get MySQLi connection directly
            $mysqli = $this->db->conn_id;
            
            // Call stored procedure with proper escaping
            $sql = "CALL sp_get_accounts_dashboard('" . $mysqli->real_escape_string($report_type) . "', '" . $mysqli->real_escape_string($reference_date) . "')";
            
            if (!$mysqli->multi_query($sql)) {
                throw new Exception("Multi-query failed: " . $mysqli->error);
            }
            
            $results = array(
                'sales_summary' => array(),
                'collection_summary' => array(),
                'purchase_summary' => array(),
                'purchase_per_item' => array(),
                'expiry_report' => array(),
                'customer_summary' => array(),
                'overall_summary' => array()
            );
            
            $result_keys = array('sales_summary', 'collection_summary', 'purchase_summary', 'purchase_per_item', 'expiry_report', 'customer_summary', 'overall_summary');
            $key_index = 0;
            
            // Process each result set
            do {
                if ($result_index = $mysqli->store_result()) {
                    $key = $result_keys[$key_index] ?? null;
                    
                    if ($key) {
                        $data = $result_index->fetch_all(MYSQLI_ASSOC);
                        
                        // For single summary results, store as object, not array
                        if ($key === 'overall_summary' && !empty($data)) {
                            $results[$key] = $data[0]; // Store first row as object
                        } else {
                            $results[$key] = $data;
                        }
                    }
                    
                    $result_index->free();
                    $key_index++;
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
            
            // Clear any remaining results
            while ($mysqli->more_results() && $mysqli->next_result()) {
                if ($res = $mysqli->store_result()) {
                    $res->free();
                }
            }
            
            return $results;
            
        } catch (Exception $e) {
            throw new Exception('Error calling sp_get_accounts_dashboard: ' . $e->getMessage());
        }
    }
    
    /**
     * Calculate trend percentages by comparing current period with previous period
     * 
     * @param string $report_type ('ytd', 'monthly', 'today')
     * @param string $reference_date Current period date
     * @return array Trend percentages for each metric
     */
    public function calculate_trends($report_type = 'ytd', $reference_date = null) {
        if (empty($reference_date)) {
            $reference_date = date('Y-m-d');
        }
        
        try {
            // Get current period data
            $current_data = $this->get_dashboard_data($report_type, $reference_date);
            
            // Calculate previous period date
            $previous_date = $this->get_previous_period_date($report_type, $reference_date);
            
            // Get previous period data
            $previous_data = $this->get_dashboard_data($report_type, $previous_date);
            
            // Extract summary data
            $current_summary = $current_data['sales_summary'][0] ?? array();
            $current_collections = $current_data['collection_summary'][0] ?? array();
            $current_purchases = $current_data['purchase_summary'][0] ?? array();
            $current_overall = $current_data['overall_summary'] ?? array();
            
            $previous_summary = $previous_data['sales_summary'][0] ?? array();
            $previous_collections = $previous_data['collection_summary'][0] ?? array();
            $previous_purchases = $previous_data['purchase_summary'][0] ?? array();
            $previous_overall = $previous_data['overall_summary'] ?? array();
            
            // Calculate trends
            $trends = array(
                'sales_trend' => $this->calculate_percentage_change(
                    $previous_summary['total_sales'] ?? 0,
                    $current_summary['total_sales'] ?? 0
                ),
                'collections_trend' => $this->calculate_percentage_change(
                    $previous_collections['total_collected'] ?? 0,
                    $current_collections['total_collected'] ?? 0
                ),
                'purchases_trend' => $this->calculate_percentage_change(
                    $previous_purchases['total_purchase'] ?? 0,
                    $current_purchases['total_purchase'] ?? 0
                ),
                'net_sales_trend' => $this->calculate_percentage_change(
                    $previous_summary['net_sales'] ?? 0,
                    $current_summary['net_sales'] ?? 0
                ),
                'profit_trend' => $this->calculate_percentage_change(
                    ($previous_overall['gross_profit'] ?? 0),
                    ($current_overall['gross_profit'] ?? 0)
                )
            );
            
            return $trends;
            
        } catch (Exception $e) {
            // On error, return default trends
            return array(
                'sales_trend' => 0,
                'collections_trend' => 0,
                'purchases_trend' => 0,
                'net_sales_trend' => 0,
                'profit_trend' => 0
            );
        }
    }
    
    /**
     * Get the previous period date based on report type
     * 
     * @param string $report_type
     * @param string $reference_date
     * @return string Previous period date in Y-m-d format
     */
    private function get_previous_period_date($report_type, $reference_date) {
        $date = new DateTime($reference_date);
        
        switch ($report_type) {
            case 'today':
                // Previous day
                $date->modify('-1 day');
                break;
            case 'monthly':
                // Previous month, same day
                $date->modify('first day of this month');
                $date->modify('-1 day');
                break;
            case 'ytd':
            default:
                // Previous year same date
                $date->modify('-1 year');
                break;
        }
        
        return $date->format('Y-m-d');
    }
    
    /**
     * Calculate percentage change between two values
     * 
     * @param float $previous
     * @param float $current
     * @return float Percentage change (e.g., 5.2 for +5.2%)
     */
    private function calculate_percentage_change($previous, $current) {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0; // 100% increase if previous was 0
        }
        
        $change = (($current - $previous) / abs($previous)) * 100;
        return round($change, 2);
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