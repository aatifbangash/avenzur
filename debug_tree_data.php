<?php
/**
 * Debug Tree Data - Check pharmacy and branch relationships
 */

// Include CodeIgniter
require_once 'index.php';

// Get CI instance
$CI =& get_instance();

// Load model
$CI->load->model('admin/Cost_center_model', 'cost_center');

echo "<h1>Pharmacy and Branch Tree Data Debug</h1>";

// Get the data
$pharmacies = $CI->cost_center->get_all_pharmacies_for_cost_center();

echo "<h2>Raw Data from Model (Total: " . count($pharmacies) . " records)</h2>";
echo "<pre>";
print_r($pharmacies);
echo "</pre>";

// Group like the view does
$pharmacies_list = [];
$branches_by_pharmacy = [];

foreach ($pharmacies as $entity) {
    if ($entity['warehouse_type'] === 'pharmacy') {
        $pharmacies_list[$entity['warehouse_id']] = $entity;
    } else if ($entity['warehouse_type'] === 'branch' && $entity['parent_id']) {
        $branches_by_pharmacy[$entity['parent_id']][] = $entity;
    }
}

echo "<h2>Grouped Data</h2>";
echo "<h3>Pharmacies (" . count($pharmacies_list) . "):</h3>";
echo "<pre>";
print_r($pharmacies_list);
echo "</pre>";

echo "<h3>Branches by Pharmacy:</h3>";
echo "<pre>";
print_r($branches_by_pharmacy);
echo "</pre>";

// Show missing relationships
echo "<h2>Analysis</h2>";
foreach ($pharmacies as $entity) {
    if ($entity['warehouse_type'] === 'branch') {
        if (empty($entity['parent_id'])) {
            echo "<p style='color: red;'>⚠️ Branch '{$entity['warehouse_name']}' (ID: {$entity['warehouse_id']}) has no parent_id!</p>";
        } else if (!isset($pharmacies_list[$entity['parent_id']])) {
            echo "<p style='color: orange;'>⚠️ Branch '{$entity['warehouse_name']}' (ID: {$entity['warehouse_id']}) has parent_id {$entity['parent_id']} but parent not found in pharmacies list!</p>";
        } else {
            echo "<p style='color: green;'>✅ Branch '{$entity['warehouse_name']}' correctly linked to pharmacy '{$pharmacies_list[$entity['parent_id']]['warehouse_name']}'</p>";
        }
    }
}

// Show which pharmacies have branches
echo "<h3>Pharmacies with Branches:</h3>";
foreach ($pharmacies_list as $pharmacy_id => $pharmacy) {
    $branch_count = isset($branches_by_pharmacy[$pharmacy_id]) ? count($branches_by_pharmacy[$pharmacy_id]) : 0;
    echo "<p>Pharmacy: '{$pharmacy['warehouse_name']}' (ID: $pharmacy_id) has $branch_count branches</p>";
}

?>