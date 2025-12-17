<?php
// Debug script to test Purchase Per Invoice query

define('BASEPATH', TRUE);
require_once('app/config/database.php');

// Get database connection
$conn = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Testing Purchase Per Invoice Query</h2>";

// Test parameters (adjust these based on your selections)
$start_date = '2025-01-01';  // This year (ytd)
$end_date = '2025-12-17';
$supplier_id = null;  // all suppliers
$pharmacy_id = 1;     // Rawabi warehouse (adjust if different)

// Build query exactly as in model
$sql = "
SELECT 
    p.id,
    p.reference_no as invoice,
    p.date,
    p.supplier_id,
    s.name as supplier_name,
    s.company as supplier_no,
    p.total as purchase,
    p.product_tax as vat,
    p.grand_total as payable,
    p.warehouse_id,
    w.name as warehouse_name,
    COUNT(pi.id) as item_count,
    SUM(pi.quantity) as total_quantity
FROM sma_purchases p
LEFT JOIN sma_companies s ON s.id = p.supplier_id
LEFT JOIN sma_warehouses w ON w.id = p.warehouse_id
LEFT JOIN sma_purchase_items pi ON pi.purchase_id = p.id
WHERE DATE(p.date) >= '$start_date'
AND DATE(p.date) <= '$end_date'
";

// Add pharmacy filter if specified
if ($pharmacy_id) {
    $sql .= " AND p.warehouse_id = $pharmacy_id";
}

$sql .= " GROUP BY p.id ORDER BY p.date DESC";

echo "<h3>Query:</h3>";
echo "<pre>" . htmlspecialchars($sql) . "</pre>";

// Execute query
$result = $conn->query($sql);

echo "<h3>Results: " . $result->num_rows . " rows</h3>";

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Invoice</th><th>Date</th><th>Supplier</th><th>Warehouse</th><th>Purchase</th><th>VAT</th><th>Payable</th></tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['invoice'] . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . $row['supplier_name'] . "</td>";
        echo "<td>" . $row['warehouse_name'] . "</td>";
        echo "<td>" . number_format($row['purchase'], 2) . "</td>";
        echo "<td>" . number_format($row['vat'], 2) . "</td>";
        echo "<td>" . number_format($row['payable'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No data found!</p>";

    // Let's check if there are any purchases at all in the database
    $check_sql = "SELECT COUNT(*) as total FROM sma_purchases WHERE DATE(date) >= '$start_date' AND DATE(date) <= '$end_date'";
    $check_result = $conn->query($check_sql);
    $check_row = $check_result->fetch_assoc();
    echo "<p>Total purchases in date range: " . $check_row['total'] . "</p>";

    // Check warehouses
    $wh_sql = "SELECT id, name FROM sma_warehouses";
    $wh_result = $conn->query($wh_sql);
    echo "<h4>Available Warehouses:</h4>";
    echo "<ul>";
    while($wh = $wh_result->fetch_assoc()) {
        echo "<li>ID: " . $wh['id'] . " - " . $wh['name'] . "</li>";
    }
    echo "</ul>";
}

$conn->close();
?>

