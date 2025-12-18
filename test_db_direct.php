<?php
// Direct database test - check purchase data
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'allinthisnet_pharmacy';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "<h2>Purchase Data Test</h2>";

// Check today's purchases
$result = $mysqli->query("SELECT COUNT(*) as count FROM sma_purchases WHERE DATE(date) = CURDATE()");
$row = $result->fetch_assoc();
echo "<p>Purchases today: " . $row['count'] . "</p>";

// Check this month's purchases
$result = $mysqli->query("SELECT COUNT(*) as count FROM sma_purchases WHERE DATE(date) >= DATE_FORMAT(NOW(), '%Y-%m-01')");
$row = $result->fetch_assoc();
echo "<p>Purchases this month: " . $row['count'] . "</p>";

// Check this year's purchases
$result = $mysqli->query("SELECT COUNT(*) as count FROM sma_purchases WHERE YEAR(date) = YEAR(NOW())");
$row = $result->fetch_assoc();
echo "<p>Purchases this year: " . $row['count'] . "</p>";

// Sample data
echo "<h3>Sample Purchase Records (Last 10):</h3>";
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
    echo "<td>" . ($row['supplier_name'] ?? 'N/A') . "</td>";
    echo "<td>" . number_format($row['grand_total'], 2) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test the exact query from the model for today
echo "<h3>Test Query for Today:</h3>";
$start_date = date('Y-m-d');
$end_date = date('Y-m-d');

$query = "
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
    WHERE DATE(p.date) >= '{$start_date}' AND DATE(p.date) <= '{$end_date}'
    GROUP BY p.id
    ORDER BY p.date DESC
";

echo "<p>Query: <pre>" . htmlspecialchars($query) . "</pre></p>";

$result = $mysqli->query($query);
echo "<p>Results: " . $result->num_rows . " records</p>";

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Invoice</th><th>Date</th><th>Supplier</th><th>Purchase</th><th>VAT</th><th>Payable</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['invoice'] . "</td>";
        echo "<td>" . $row['date'] . "</td>";
        echo "<td>" . ($row['supplier_name'] ?? 'N/A') . "</td>";
        echo "<td>" . number_format($row['purchase'], 2) . "</td>";
        echo "<td>" . number_format($row['vat'], 2) . "</td>";
        echo "<td>" . number_format($row['payable'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$mysqli->close();
?>

