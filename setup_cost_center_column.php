<?php
/**
 * Add cost_center_id column to purchases table if missing
 * Run this script to ensure the database schema is up to date
 */

// Include CodeIgniter bootstrap
$file_path = __DIR__ . '/index.php';
if (file_exists($file_path)) {
    require_once($file_path);
    // Get CI instance
    $ci =& get_instance();
} else {
    echo "Error: Could not find index.php. Please run this script from the project root.\n";
    exit(1);
}

// Load database
$ci->load->database();

echo "=== COST CENTER COLUMN SETUP ===\n\n";

// Check if cost_center_id column exists in purchases table (with prefix)
$table_name = $ci->db->dbprefix('purchases');
echo "Checking table: {$table_name}\n";

$columns = $ci->db->query("SHOW COLUMNS FROM {$table_name} LIKE 'cost_center_id'")->result_array();

if (!empty($columns)) {
    echo "✓ cost_center_id column already EXISTS in {$table_name}\n";
    print_r($columns[0]);
} else {
    echo "✗ cost_center_id column DOES NOT EXIST in {$table_name}\n";
    echo "Adding the column...\n";
    
    try {
        // Add the column
        $sql = "ALTER TABLE {$table_name} ADD COLUMN cost_center_id INT(11) NULL DEFAULT NULL COMMENT 'Foreign key to cost centers table' AFTER warehouse_id";
        $ci->db->query($sql);
        
        // Add index
        $sql_index = "CREATE INDEX idx_purchases_cost_center ON {$table_name} (cost_center_id)";
        $ci->db->query($sql_index);
        
        echo "✓ Successfully added cost_center_id column and index to {$table_name}\n";
        
    } catch (Exception $e) {
        echo "✗ Error adding column: " . $e->getMessage() . "\n";
        echo "Please run the SQL migration manually:\n";
        echo "ALTER TABLE {$table_name} ADD COLUMN cost_center_id INT(11) NULL DEFAULT NULL AFTER warehouse_id;\n";
        echo "CREATE INDEX idx_purchases_cost_center ON {$table_name} (cost_center_id);\n";
    }
}

echo "\n2. Testing purchase data retrieval:\n";
// Test retrieving a purchase to see if cost_center_id is now available
$ci->load->model('admin/Purchases_model');
$sample_purchase = $ci->db->select('id')->from('purchases')->limit(1)->get()->row();

if ($sample_purchase) {
    $purchase_data = $ci->Purchases_model->getPurchaseByID($sample_purchase->id);
    echo "Sample purchase ID: {$sample_purchase->id}\n";
    if (property_exists($purchase_data, 'cost_center_id')) {
        echo "✓ cost_center_id property exists in purchase data: " . ($purchase_data->cost_center_id ?? 'NULL') . "\n";
    } else {
        echo "✗ cost_center_id property still missing in purchase data\n";
    }
} else {
    echo "No purchase records found for testing\n";
}

echo "\n=== SETUP COMPLETE ===\n";
?>