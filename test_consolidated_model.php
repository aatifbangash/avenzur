<?php
// Test consolidated cost center model
require_once 'system/core/Common.php';
require_once 'system/core/CodeIgniter.php';

// Bootstrap CodeIgniter
$_SERVER['REQUEST_URI'] = '/test';
$_SERVER['SCRIPT_NAME'] = '/index.php';

define('BASEPATH', realpath('system') . '/');
define('APPPATH', realpath('app') . '/');
define('ENVIRONMENT', 'development');

// Load database config
require_once APPPATH . 'config/database.php';

// Create database connection
$db_config = $db['default'];
$mysqli = new mysqli(
    $db_config['hostname'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Testing Consolidated Cost Center Model\n";
echo str_repeat("=", 60) . "\n\n";

// Test 1: Company summary for October (monthly)
echo "Test 1: Company Summary - October 2025 (monthly)\n";
echo str_repeat("-", 60) . "\n";
$result = $mysqli->query("CALL sp_get_sales_analytics_hierarchical('monthly', '2025-10', NULL, 'company')");
if ($result) {
    $summary = $result->fetch_assoc();
    echo "Total Sales: " . number_format($summary['total_sales'], 2) . " SAR\n";
    echo "Margin: " . $summary['margin_percentage'] . "%\n";
    echo "Customers: " . $summary['total_customers'] . "\n";
    echo "Items Sold: " . $summary['total_items_sold'] . "\n";
    $result->close();
    $mysqli->next_result();
} else {
    echo "Error: " . $mysqli->error . "\n";
}
echo "\n";

// Test 2: Company summary for YTD
echo "Test 2: Company Summary - YTD 2025\n";
echo str_repeat("-", 60) . "\n";
$result = $mysqli->query("CALL sp_get_sales_analytics_hierarchical('ytd', NULL, NULL, 'company')");
if ($result) {
    $summary = $result->fetch_assoc();
    echo "Total Sales: " . number_format($summary['total_sales'], 2) . " SAR\n";
    echo "Margin: " . $summary['margin_percentage'] . "%\n";
    echo "Customers: " . $summary['total_customers'] . "\n";
    $result->close();
    $mysqli->next_result();
} else {
    echo "Error: " . $mysqli->error . "\n";
}
echo "\n";

// Test 3: Company summary for Today
echo "Test 3: Company Summary - Today\n";
echo str_repeat("-", 60) . "\n";
$result = $mysqli->query("CALL sp_get_sales_analytics_hierarchical('today', NULL, NULL, 'company')");
if ($result) {
    $summary = $result->fetch_assoc();
    echo "Total Sales: " . number_format($summary['total_sales'], 2) . " SAR\n";
    echo "Margin: " . $summary['margin_percentage'] . "%\n";
    echo "Customers: " . $summary['total_customers'] . "\n";
    $result->close();
    $mysqli->next_result();
} else {
    echo "Error: " . $mysqli->error . "\n";
}
echo "\n";

// Test 4: Pharmacy detail (pharmacy level call)
echo "Test 4: Pharmacy Detail - Pharmacy ID 1, October 2025\n";
echo str_repeat("-", 60) . "\n";
$result = $mysqli->query("CALL sp_get_sales_analytics_hierarchical('monthly', '2025-10', 1, 'pharmacy')");
if ($result) {
    $summary = $result->fetch_assoc();
    echo "Pharmacy: " . $summary['warehouse_name'] . "\n";
    echo "Total Sales: " . number_format($summary['total_sales'], 2) . " SAR\n";
    echo "Margin: " . $summary['margin_percentage'] . "%\n";
    $result->close();
    
    // Get branches result set
    if ($mysqli->next_result()) {
        $branches_result = $mysqli->store_result();
        echo "Branches: " . $branches_result->num_rows . "\n";
        if ($branches_result->num_rows > 0) {
            echo "\nBranch Details:\n";
            while ($branch = $branches_result->fetch_assoc()) {
                echo "  - " . $branch['warehouse_name'] . ": " . number_format($branch['total_sales'], 2) . " SAR\n";
            }
        }
        $branches_result->close();
        $mysqli->next_result();
    }
} else {
    echo "Error: " . $mysqli->error . "\n";
}
echo "\n";

echo str_repeat("=", 60) . "\n";
echo "✅ All tests completed!\n";

$mysqli->close();
