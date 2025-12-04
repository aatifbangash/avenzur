<?php
/**
 * Budget Dashboard Data Helper
 * 
 * Provides budget data for dashboard via AJAX
 * Called from cost_center_dashboard.php
 */

// Start session
if (!isset($_SESSION)) {
    session_start();
}

// Include CodeIgniter bootstrap
// Path: /themes/blue/admin/views/cost_center/ -> root = ../../../../../
$root_path = dirname(__FILE__) . '/../../../../../';
require_once $root_path . 'index.php';

// Get parameters
$action = isset($_GET['action']) ? $_GET['action'] : '';
$period = isset($_GET['period']) ? $_GET['period'] : date('Y-m');

try {
    $CI =& get_instance();
    $CI->load->model('admin/Budget_model', 'budget');
    
    $response = [
        'success' => false,
        'data' => [],
        'message' => 'Unknown action'
    ];

    switch ($action) {
        case 'allocated':
            // Get budget allocations
            $allocations = $CI->db->query(
                "SELECT * FROM sma_budget_allocation WHERE period = ? AND is_active = 1 ORDER BY allocated_at DESC",
                [$period]
            )->result_array();
            
            $response = [
                'success' => true,
                'data' => $allocations ?: [],
                'period' => $period
            ];
            break;

        case 'tracking':
            // Get budget tracking data
            $tracking = $CI->db->query(
                "SELECT * FROM sma_budget_tracking WHERE period = ? ORDER BY updated_at DESC",
                [$period]
            )->result_array();
            
            $response = [
                'success' => true,
                'data' => $tracking ?: []
            ];
            break;

        case 'forecast':
            // Get forecast data
            $forecast = $CI->db->query(
                "SELECT * FROM sma_budget_forecast WHERE period = ? ORDER BY created_at DESC",
                [$period]
            )->result_array();
            
            $response = [
                'success' => true,
                'data' => $forecast ?: []
            ];
            break;

        case 'alerts':
            // Get alerts
            $alerts = $CI->db->query(
                "SELECT * FROM sma_alert_events WHERE period = ? ORDER BY triggered_at DESC",
                [$period]
            )->result_array();
            
            $response = [
                'success' => true,
                'data' => $alerts ?: []
            ];
            break;

        default:
            $response['message'] = "Unknown action: $action";
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ];
    
    error_log('Budget dashboard error: ' . $e->getMessage());
}

// Output JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
