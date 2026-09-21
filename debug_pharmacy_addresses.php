<?php
/**
 * Debug script to check pharmacy addresses
 * Visit: /debug_pharmacy_addresses.php
 */

// Get CodeIgniter instance
require 'index.php';
$CI = &get_instance();

echo "<h2>Pharmacy Address Debug</h2>";

// Check loyalty_pharmacies
echo "<h3>Loyalty Pharmacies:</h3>";
$query = "SELECT lp.id, lp.code, lp.name, lp.external_id, sw.address, sw.phone 
          FROM loyalty_pharmacies lp
          LEFT JOIN sma_warehouses sw ON lp.external_id = sw.id
          ORDER BY lp.id DESC";
$result = $CI->db->query($query)->result_array();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Code</th><th>Name</th><th>External ID</th><th>Address</th><th>Phone</th></tr>";
foreach ($result as $row) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['code'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['external_id'] . "</td>";
    echo "<td>" . ($row['address'] ?: '<span style="color:red;">EMPTY</span>') . "</td>";
    echo "<td>" . ($row['phone'] ?: '<span style="color:orange;">EMPTY</span>') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";

// Show warehouse records
echo "<h3>Warehouse Records (pharmacy type):</h3>";
$query2 = "SELECT id, code, name, address, phone FROM sma_warehouses WHERE warehouse_type='pharmacy'";
$result2 = $CI->db->query($query2)->result_array();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Code</th><th>Name</th><th>Address</th><th>Phone</th></tr>";
foreach ($result2 as $row) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['code'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . ($row['address'] ?: '<span style="color:red;">EMPTY</span>') . "</td>";
    echo "<td>" . ($row['phone'] ?: '<span style="color:orange;">EMPTY</span>') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
