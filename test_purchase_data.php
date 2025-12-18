<?php
// Test script to check purchase data
require_once 'app/config/database.php';

$mysqli = new mysqli($db['default']['hostname'], $db['default']['username'], $db['default']['password'], $db['default']['database']);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "<h2>Purchase Data Test</h2>";

// Check today's purchases
$result = $mysqli->query("SELECT COUNT(*) as count FROM sma_purchases WHERE DATE(date) = CURDATE()");
$row = $result->fetch_assoc();
echo "<p>Purchases today: " . $row['count'] . "</p>";

// Check total purchases
$result = $mysqli->query("SELECT COUNT(*) as count FROM sma_purchases");
$row = $result->fetch_assoc();
echo "<p>Total purchases: " . $row['count'] . "</p>";

// Check this month's purchases
$result = $mysqli->query("SELECT COUNT(*) as count FROM sma_purchases WHERE DATE(date) >= DATE_FORMAT(NOW(), '%Y-%m-01')");
$row = $result->fetch_assoc();
echo "<p>Purchases this month: " . $row['count'] . "</p>";

// Check this year's purchases
$result = $mysqli->query("SELECT COUNT(*) as count FROM sma_purchases WHERE YEAR(date) = YEAR(NOW())");
$row = $result->fetch_assoc();
echo "<p>Purchases this year: " . $row['count'] . "</p>";

// Sample data
echo "<h3>Sample Purchase Records:</h3>";
$result = $mysqli->query("
    SELECT p.id, p.reference_no, p.date, p.supplier_id, s.name as supplier_name, p.grand_total 
    FROM sma_purchases p 
    LEFT JOIN sma_companies s ON s.id = p.supplier_id 
    ORDER BY p.date DESC 
    LIMIT 10
");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Reference</th><th>Date</th><th>Supplier ID</th><th>Supplier Name</th><th>Grand Total</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['reference_no'] . "</td>";
    echo "<td>" . $row['date'] . "</td>";
    echo "<td>" . $row['supplier_id'] . "</td>";
    echo "<td>" . $row['supplier_name'] . "</td>";
    echo "<td>" . number_format($row['grand_total'], 2) . "</td>";
    echo "</tr>";
}
echo "</table>";

$mysqli->close();
?>

