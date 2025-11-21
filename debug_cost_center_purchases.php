<?php
/**
 * Debug Cost Center Column in Purchases
 * Run this to check if cost_center_id column exists and what data is available
 */

// Include CodeIgniter bootstrap
require_once(dirname(__FILE__) . '/index.php');

// Get CI instance
$ci =& get_instance();

// Load database
$ci->load->database();

echo "=== COST CENTER COLUMN DEBUG ===\n\n";

// Check if cost_center_id column exists in sma_purchases table
echo "1. Checking if cost_center_id column exists in sma_purchases table:\n";
$columns = $ci->db->query("SHOW COLUMNS FROM sma_purchases LIKE 'cost_center_id'")->result_array();
if (!empty($columns)) {
    echo "✓ cost_center_id column EXISTS\n";
    print_r($columns[0]);
} else {
    echo "✗ cost_center_id column DOES NOT EXIST\n";
    echo "   Please run the migration: 014_add_cost_center_to_purchases.sql\n";
}

echo "\n2. Sample purchase data (first 3 records):\n";
$sample_purchases = $ci->db->select('id, reference_no, warehouse_id, cost_center_id')
                           ->from('sma_purchases')
                           ->limit(3)
                           ->get()
                           ->result();

if (!empty($sample_purchases)) {
    foreach ($sample_purchases as $purchase) {
        echo "Purchase ID: {$purchase->id}, Ref: {$purchase->reference_no}, ";
        echo "Warehouse: {$purchase->warehouse_id}, ";
        if (isset($purchase->cost_center_id)) {
            echo "Cost Center: " . ($purchase->cost_center_id ? $purchase->cost_center_id : 'NULL') . "\n";
        } else {
            echo "Cost Center: COLUMN MISSING\n";
        }
    }
} else {
    echo "No purchase records found.\n";
}

echo "\n3. Cost centers available:\n";
$cost_centers = $ci->db->select('cost_center_id, name, warehouse_id')
                       ->from('sma_cost_centers')
                       ->limit(5)
                       ->get()
                       ->result();

if (!empty($cost_centers)) {
    foreach ($cost_centers as $cc) {
        echo "Cost Center ID: {$cc->cost_center_id}, Name: {$cc->name}, Warehouse: {$cc->warehouse_id}\n";
    }
} else {
    echo "No cost centers found.\n";
}

echo "\n=== END DEBUG ===\n";
?>