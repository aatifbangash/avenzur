<?php
// Test warehouses and cost centers data

$hostname = "host.docker.internal";
$username = "admin";
$password = "R00tr00t";
$database = "retaj_aldawa";

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to database: $database\n\n";

// Check warehouses
echo "=== WAREHOUSES ===\n";
$result = $conn->query("SELECT id, name, code, warehouse_type FROM sma_warehouses LIMIT 5");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Name: " . $row['name'] . " | Code: " . $row['code'] . " | Type: " . $row['warehouse_type'] . "\n";
    }
} else {
    echo "No warehouses found\n";
}

echo "\n=== COST CENTERS ===\n";
$result = $conn->query("SELECT cost_center_id, cost_center_code, cost_center_name, entity_id FROM sma_cost_centers LIMIT 5");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['cost_center_id'] . " | Code: " . $row['cost_center_code'] . " | Name: " . $row['cost_center_name'] . " | Entity ID: " . $row['entity_id'] . "\n";
    }
} else {
    echo "No cost centers found\n";
}

echo "\n=== COST CENTERS FOR WAREHOUSE 1 ===\n";
$query = "
    SELECT 
        cc.cost_center_id,
        cc.cost_center_code,
        cc.cost_center_name,
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
    WHERE (w.id = 1 OR w.parent_id = 1)
    ORDER BY 
        CASE 
            WHEN w.warehouse_type = 'warehouse' THEN 1 
            WHEN w.warehouse_type = 'branch' THEN 2 
            ELSE 3 
        END,
        w.name ASC
";

$result = $conn->query($query);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Cost Center: " . $row['cost_center_name'] . " | Entity: " . $row['entity_name'] . " | Type: " . $row['entity_type'] . "\n";
    }
} else {
    echo "No cost centers found for warehouse 1\n";
}

$conn->close();
?>