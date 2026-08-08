<?php
/**
 * Debug script to test delete pharmacy logic
 */

// Get CodeIgniter instance
require 'index.php';
$CI = &get_instance();

echo "<h2>Delete Pharmacy Debug</h2>";

// Get a sample pharmacy
echo "<h3>Sample Pharmacy Record:</h3>";
$pharmacy = $CI->db->query("SELECT id, code, external_id FROM loyalty_pharmacies LIMIT 1")->row();

if ($pharmacy) {
    echo "ID: " . $pharmacy->id . "<br>";
    echo "Code: " . $pharmacy->code . "<br>";
    echo "External ID (Warehouse ID): " . $pharmacy->external_id . "<br>";
    
    // Check warehouse
    echo "<h3>Warehouse Record:</h3>";
    $warehouse = $CI->db->query("SELECT id, code, name, warehouse_type FROM sma_warehouses WHERE id = ?", [$pharmacy->external_id])->row();
    if ($warehouse) {
        var_dump($warehouse);
    } else {
        echo "Warehouse not found!";
    }
    
    // Check branches
    echo "<h3>Branch Warehouses for this Pharmacy:</h3>";
    $branches = $CI->db->query(
        "SELECT id, code, name, warehouse_type FROM sma_warehouses WHERE parent_id = ? AND warehouse_type = 'branch'",
        [$pharmacy->external_id]
    )->result_array();
    
    if (count($branches) > 0) {
        echo "Found " . count($branches) . " branches:<br>";
        foreach ($branches as $branch) {
            echo "- ID: " . $branch['id'] . ", Code: " . $branch['code'] . ", Name: " . $branch['name'] . "<br>";
        }
    } else {
        echo "No branches found.";
    }
    
    // Check loyalty branches
    echo "<h3>Loyalty Branches for this Pharmacy:</h3>";
    $loyalty_branches = $CI->db->query("SELECT id, code, name, external_id FROM loyalty_branches WHERE pharmacy_id = ?", [$pharmacy->id])->result_array();
    
    if (count($loyalty_branches) > 0) {
        echo "Found " . count($loyalty_branches) . " loyalty branches:<br>";
        foreach ($loyalty_branches as $lb) {
            echo "- ID: " . $lb['id'] . ", Code: " . $lb['code'] . ", Name: " . $lb['name'] . ", External ID: " . $lb['external_id'] . "<br>";
        }
    } else {
        echo "No loyalty branches found.";
    }
} else {
    echo "No pharmacy records found in database.";
}

echo "<hr>";
echo "<h3>Database Status:</h3>";
echo "Pharmacy Count: " . $CI->db->query("SELECT COUNT(*) as cnt FROM loyalty_pharmacies")->row()->cnt . "<br>";
echo "Warehouse Count (pharmacy type): " . $CI->db->query("SELECT COUNT(*) as cnt FROM sma_warehouses WHERE warehouse_type = 'pharmacy'")->row()->cnt . "<br>";
echo "Warehouse Count (branch type): " . $CI->db->query("SELECT COUNT(*) as cnt FROM sma_warehouses WHERE warehouse_type = 'branch'")->row()->cnt . "<br>";
echo "Loyalty Branches Count: " . $CI->db->query("SELECT COUNT(*) as cnt FROM loyalty_branches")->row()->cnt . "<br>";
?>
