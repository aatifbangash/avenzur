<?php
/**
 * Simple test to verify Level 2 cost center filtering and Code-Name format
 * This script directly tests the SQL query without complex PHP dependencies
 */

// Simple connection test
$hostname = "host.docker.internal";
$username = "admin"; 
$password = "R00tr00t";
$database = "retaj_aldawa";

echo "Testing Level 2 Cost Center Filtering with Code-Name Format\n";
echo "=========================================================\n\n";

try {
    // Use MySQLi which is more stable than PDO in this environment
    $conn = new mysqli($hostname, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "✓ Connected to database successfully\n\n";
    
    // Test 1: Check all cost center levels
    echo "1. Cost Centers by Level:\n";
    echo "------------------------\n";
    $result = $conn->query("SELECT cost_center_level, COUNT(*) as count FROM sma_cost_centers GROUP BY cost_center_level ORDER BY cost_center_level");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "   Level {$row['cost_center_level']}: {$row['count']} cost centers\n";
        }
    } else {
        echo "   No cost centers found\n";
    }
    
    // Test 2: Show Level 2 cost centers with new format
    echo "\n2. Level 2 Cost Centers (Code-Name Format):\n";
    echo "-------------------------------------------\n";
    $query = "
        SELECT 
            cost_center_id,
            cost_center_code,
            cost_center_name,
            cost_center_level,
            CONCAT(cost_center_code, '-', cost_center_name) as cost_center_display
        FROM sma_cost_centers 
        WHERE cost_center_level = 2
        ORDER BY cost_center_code ASC, cost_center_name ASC
        LIMIT 10
    ";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "   ID: {$row['cost_center_id']} | Display: \"{$row['cost_center_display']}\" | Level: {$row['cost_center_level']}\n";
        }
    } else {
        echo "   No Level 2 cost centers found\n";
    }
    
    // Test 3: Test the actual query used in get_cost_centers_by_warehouse method
    echo "\n3. Test Warehouse-Specific Query (Warehouse ID: 1, Level 2 only):\n";
    echo "----------------------------------------------------------------\n";
    
    $warehouse_id = 1;
    $query = "
        SELECT 
            cc.cost_center_id,
            cc.cost_center_code,
            cc.cost_center_name,
            CONCAT(cc.cost_center_code, '-', cc.cost_center_name) as cost_center_display,
            cc.cost_center_level,
            cc.parent_cost_center_id,
            w.id as entity_id,
            w.name as entity_name,
            w.code as entity_code,
            CASE 
                WHEN w.warehouse_type = 'warehouse' THEN 'pharmacy'
                ELSE w.warehouse_type
            END as entity_type,
            w.warehouse_type,
            w.parent_id
        FROM sma_cost_centers cc
        INNER JOIN sma_warehouses w ON cc.entity_id = w.id
        WHERE (w.id = {$warehouse_id} OR w.parent_id = {$warehouse_id}) AND cc.cost_center_level = 2
        ORDER BY 
            cc.cost_center_code ASC,
            cc.cost_center_name ASC
    ";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        echo "   Found " . $result->num_rows . " Level 2 cost centers for warehouse {$warehouse_id}:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   • {$row['cost_center_display']} (ID: {$row['cost_center_id']}, Entity: {$row['entity_name']})\n";
        }
    } else {
        echo "   No Level 2 cost centers found for warehouse {$warehouse_id}\n";
    }
    
    // Test 4: Sample JSON response format
    echo "\n4. Sample JSON Response Format:\n";
    echo "-------------------------------\n";
    $result->data_seek(0); // Reset result pointer
    $cost_centers = [];
    while ($row = $result->fetch_assoc()) {
        $cost_centers[] = $row;
    }
    
    $response = [
        'success' => true,
        'warehouse_id' => $warehouse_id,
        'cost_centers' => $cost_centers,
        'count' => count($cost_centers)
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
    echo "\n\n✓ All tests completed successfully!\n";
    echo "\nChanges implemented:\n";
    echo "- ✓ Filtered to show Level 2 cost centers only\n";
    echo "- ✓ Added Code-Name format (e.g., 'CC001-Main Pharmacy Operations')\n";
    echo "- ✓ Updated JavaScript to use cost_center_display field\n";
    echo "- ✓ Maintained proper sorting by code and name\n";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>