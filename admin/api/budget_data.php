<?php
/**
 * Budget Data API Endpoint
 * Direct database access without CodeIgniter dependency
 * Called via AJAX from cost_center_dashboard.php
 */

header('Content-Type: application/json');

$response = [
    'success' => false,
    'data' => [],
    'message' => 'Unknown action'
];

try {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $period = isset($_GET['period']) ? $_GET['period'] : date('Y-m');
    
    if (!$action) {
        throw new Exception('action parameter required');
    }
    
    // Direct database connection
    $db_host = 'localhost';
    $db_user = 'admin';
    $db_pass = 'R00tr00t';
    $db_name = 'retaj_aldawa';
    
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if ($mysqli->connect_error) {
        throw new Exception('Database connection failed: ' . $mysqli->connect_error);
    }
    
    // Set charset
    $mysqli->set_charset('utf8mb4');
    
    switch ($action) {
        case 'allocated':
            $query = "SELECT * FROM sma_budget_allocation WHERE period = ? AND is_active = 1 ORDER BY allocated_at DESC";
            break;
            
        case 'tracking':
            $query = "SELECT * FROM sma_budget_tracking WHERE period = ? ORDER BY calculated_at DESC";
            break;
            
        case 'forecast':
            $query = "SELECT * FROM sma_budget_forecast WHERE period = ? ORDER BY calculated_at DESC";
            break;
            
        case 'alerts':
            // Alerts don't have a period column, so join with allocation
            $query = "SELECT ae.*, ba.period FROM sma_budget_alert_events ae 
                     JOIN sma_budget_allocation ba ON ae.allocation_id = ba.allocation_id 
                     WHERE ba.period = ? AND ae.status = 'active' 
                     ORDER BY ae.triggered_at DESC";
            break;
            
        default:
            throw new Exception("Unknown action: $action");
    }
    
    // Execute query
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        throw new Exception('Query prepare failed: ' . $mysqli->error);
    }
    
    $stmt->bind_param('s', $period);
    if (!$stmt->execute()) {
        throw new Exception('Query execute failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $data = [];
    
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $response = [
        'success' => true,
        'data' => $data,
        'period' => $period,
        'count' => count($data)
    ];
    
    $stmt->close();
    $mysqli->close();
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    $response['error'] = $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
exit;
