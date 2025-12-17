<?php
/**
 * Debug script for Purchase Per Invoice Report
 * Direct database query to verify data exists
 */

// Database connection
define('BASEPATH', true);
require_once 'app/config/database.php';

$mysqli = new mysqli(
    $db['default']['hostname'],
    $db['default']['username'],
    $db['default']['password'],
    $db['default']['database']
);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "<h2>Purchase Data Debug Report</h2>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Check table structure
echo "<h3>1. Check sma_purchases table</h3>";
$result = $mysqli->query("SHOW TABLES LIKE 'sma_purchases'");
echo "<p>Table exists: " . ($result->num_rows > 0 ? "YES" : "NO") . "</p>";

// Count total purchases
$result = $mysqli->query("SELECT COUNT(*) as count FROM sma_purchases");
$row = $result->fetch_assoc();
$total_purchases = $row['count'];
echo "<p><strong>Total purchases in database:</strong> {$total_purchases}</p>";

// Today's data
$today = date('Y-m-d');
echo "<h3>2. Today's Purchases ({$today})</h3>";
$query = "SELECT COUNT(*) as count FROM sma_purchases WHERE DATE(date) = '{$today}'";
$result = $mysqli->query($query);
$row = $result->fetch_assoc();
echo "<p><strong>Count:</strong> {$row['count']}</p>";
echo "<p><small>Query: {$query}</small></p>";

// This month
$first_day = date('Y-m-01');
$last_day = date('Y-m-t');
echo "<h3>3. This Month's Purchases ({$first_day} to {$last_day})</h3>";
$query = "SELECT COUNT(*) as count FROM sma_purchases WHERE DATE(date) >= '{$first_day}' AND DATE(date) <= '{$last_day}'";
$result = $mysqli->query($query);
$row = $result->fetch_assoc();
echo "<p><strong>Count:</strong> {$row['count']}</p>";
echo "<p><small>Query: {$query}</small></p>";

// This year
$year_start = date('Y-01-01');
$year_end = date('Y-m-d');
echo "<h3>4. This Year's Purchases ({$year_start} to {$year_end})</h3>";
$query = "SELECT COUNT(*) as count FROM sma_purchases WHERE DATE(date) >= '{$year_start}' AND DATE(date) <= '{$year_end}'";
$result = $mysqli->query($query);
$row = $result->fetch_assoc();
echo "<p><strong>Count:</strong> {$row['count']}</p>";
echo "<p><small>Query: {$query}</small></p>";

// Recent purchases with details
echo "<h3>5. Recent Purchase Records (Last 10)</h3>";
$query = "
    SELECT 
        p.id,
        p.reference_no,
        p.date,
        p.supplier_id,
        s.name as supplier_name,
        s.company as supplier_no,
        p.total,
        p.product_tax as vat,
        p.grand_total,
        p.warehouse_id,
        w.name as warehouse_name
    FROM sma_purchases p
    LEFT JOIN sma_companies s ON s.id = p.supplier_id
    LEFT JOIN sma_warehouses w ON w.id = p.warehouse_id
    ORDER BY p.date DESC
    LIMIT 10
";
$result = $mysqli->query($query);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%; font-size: 12px;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>ID</th><th>Reference</th><th>Date</th><th>Supplier ID</th><th>Supplier</th>";
    echo "<th>Supplier No</th><th>Total</th><th>VAT</th><th>Grand Total</th><th>Warehouse</th>";
    echo "</tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['reference_no']}</td>";
        echo "<td>{$row['date']}</td>";
        echo "<td>{$row['supplier_id']}</td>";
        echo "<td>{$row['supplier_name']}</td>";
        echo "<td>{$row['supplier_no']}</td>";
        echo "<td>" . number_format($row['total'], 2) . "</td>";
        echo "<td>" . number_format($row['vat'], 2) . "</td>";
        echo "<td>" . number_format($row['grand_total'], 2) . "</td>";
        echo "<td>{$row['warehouse_name']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p><strong>No records found!</strong></p>";
}

// Test the exact query from the model
echo "<h3>6. Test Model Query (Today's Data)</h3>";
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
    WHERE DATE(p.date) >= '{$today}' AND DATE(p.date) <= '{$today}'
    GROUP BY p.id
    ORDER BY p.date DESC
";
$result = $mysqli->query($query);
echo "<p><strong>Result count:</strong> {$result->num_rows}</p>";
echo "<p><small>Query: " . str_replace("\n", " ", $query) . "</small></p>";

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%; font-size: 11px;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Invoice</th><th>Date</th><th>Supplier</th><th>Purchase</th><th>VAT</th><th>Payable</th><th>Items</th><th>Qty</th>";
    echo "</tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['invoice']}</td>";
        echo "<td>{$row['date']}</td>";
        echo "<td>{$row['supplier_name']}</td>";
        echo "<td>" . number_format($row['purchase'], 2) . "</td>";
        echo "<td>" . number_format($row['vat'], 2) . "</td>";
        echo "<td>" . number_format($row['payable'], 2) . "</td>";
        echo "<td>{$row['item_count']}</td>";
        echo "<td>{$row['total_quantity']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check application logs
echo "<h3>7. Check Application Logs</h3>";
$log_file = 'app/logs/log-' . date('Y-m-d') . '.php';
if (file_exists($log_file)) {
    echo "<p><strong>Log file exists:</strong> {$log_file}</p>";
    $log_content = file_get_contents($log_file);
    $lines = explode("\n", $log_content);
    $purchase_logs = array_filter($lines, function($line) {
        return stripos($line, 'purchase') !== false || stripos($line, 'getPurchasePerInvoice') !== false;
    });

    if (count($purchase_logs) > 0) {
        echo "<p><strong>Recent purchase-related logs (last 20):</strong></p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; font-size: 11px; max-height: 400px; overflow-y: auto;'>";
        echo implode("\n", array_slice($purchase_logs, -20));
        echo "</pre>";
    } else {
        echo "<p>No purchase-related logs found today.</p>";
    }
} else {
    echo "<p><strong>Log file not found:</strong> {$log_file}</p>";
}

$mysqli->close();

echo "<hr>";
echo "<p><em>Generated at: " . date('Y-m-d H:i:s') . "</em></p>";
?>

