<?php
/**
 * Test Script for sp_get_accounts_dashboard Stored Procedure
 * 
 * This script tests the stored procedure and verifies it returns expected data
 * Usage: php test_sp_accounts_dashboard.php
 */

// Include CodeIgniter bootstrap
define('BASEPATH', dirname(__FILE__) . '/system/');
define('APPPATH', dirname(__FILE__) . '/app/');
define('FCPATH', dirname(__FILE__) . '/');

// Minimal database connection
$host = 'localhost';
$user = 'root';
$pass = 'R00tr00t';
$db = 'rawabi_jeddah';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "Connected to database: $db\n";
echo "========================================\n\n";

// Test the stored procedure with different parameters
$test_cases = [
    ['ytd', date('Y-m-d')],
    ['monthly', date('Y-m-d')],
    ['today', date('Y-m-d')]
];

foreach ($test_cases as list($report_type, $reference_date)) {
    echo "Test: report_type='$report_type', reference_date='$reference_date'\n";
    echo "----------------------------------------\n";
    
    $sql = "CALL sp_get_accounts_dashboard('" . $mysqli->real_escape_string($report_type) . "', '" . $mysqli->real_escape_string($reference_date) . "')";
    
    echo "SQL: $sql\n\n";
    
    if (!$mysqli->multi_query($sql)) {
        echo "ERROR: Multi-query failed: " . $mysqli->error . "\n\n";
        continue;
    }
    
    $result_set = 0;
    $result_names = ['Sales Summary', 'Collections', 'Purchases', 'Purchase Items', 'Expiry Report', 'Customers', 'Overall Summary'];
    
    do {
        $result_set++;
        
        if ($result = $mysqli->store_result()) {
            echo "Result Set #$result_set ({$result_names[$result_set-1]}):\n";
            echo "  Rows: " . $result->num_rows . "\n";
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "  Columns: " . implode(', ', array_keys($row)) . "\n";
                echo "  Sample Row: ";
                print_r($row);
            } else {
                echo "  (No data)\n";
            }
            
            $result->free();
        } else if ($mysqli->error) {
            echo "Result Set #$result_set ERROR: " . $mysqli->error . "\n";
        } else {
            echo "Result Set #$result_set (affected rows: " . $mysqli->affected_rows . ")\n";
        }
        
        echo "\n";
        
    } while ($mysqli->more_results() && $mysqli->next_result());
    
    // Clear any remaining results
    while ($mysqli->more_results() && $mysqli->next_result()) {
        if ($res = $mysqli->store_result()) {
            $res->free();
        }
    }
    
    echo "\n";
}

echo "========================================\n";
echo "Test Complete!\n";

$mysqli->close();
?>
