<?php
/**
 * Wasfaty Test Data Helper
 * 
 * This script helps you find product IDs to use in the Wasfaty test data
 * Run this script to get actual product IDs, then update wasfaty_migration.sql
 * 
 * Usage: Access via browser at: http://yoursite.com/test_wasfaty_products.php
 */

define('BASEPATH', 'dummy'); // Prevent CI errors
require_once('app/config/database.php');

// Create database connection
$conn = new mysqli(
    $db['default']['hostname'],
    $db['default']['username'],
    $db['default']['password'],
    $db['default']['database']
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Wasfaty Test Data Helper</h1>";
echo "<p>Finding suitable products for test prescription...</p>";

// Search for products that might match our test items
$search_terms = [
    'EXYLIN' => 'EXYLIN 100ML SYRUP',
    'Panadol' => 'Panadol Cold Flu 24Cap (Green)',
    'syrup' => 'Any Syrup',
    'tablet' => 'Any Tablet',
    'capsule' => 'Any Capsule'
];

echo "<h2>Product Search Results:</h2>";

foreach ($search_terms as $term => $description) {
    echo "<h3>Searching for: $term ($description)</h3>";
    
    $sql = "SELECT id, name, code, price FROM sma_products WHERE name LIKE '%$term%' OR code LIKE '%$term%' LIMIT 5";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Code</th><th>Price</th></tr>";
        
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['code'] . "</td>";
            echo "<td>" . $row['price'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "<p>No products found for '$term'</p>";
    }
}

// Get any 5 products as fallback
echo "<h3>First 5 Products in Database (Fallback):</h3>";
$sql = "SELECT id, name, code, price FROM sma_products ORDER BY id ASC LIMIT 5";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Code</th><th>Price</th></tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['code'] . "</td>";
        echo "<td>" . $row['price'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<h2>Instructions:</h2>";
echo "<ol>";
echo "<li>Choose 2 product IDs from above</li>";
echo "<li>Open <code>wasfaty_migration.sql</code></li>";
echo "<li>Replace <code>medicine_id</code> values (currently 1 and 2) with actual product IDs</li>";
echo "<li>Update <code>medicine_name</code> to match actual product names</li>";
echo "<li>Run the migration SQL file</li>";
echo "</ol>";

$conn->close();
?>
