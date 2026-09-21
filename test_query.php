<?php
// Load CodeIgniter
require 'index.php';

// Get CI instance
$CI = &get_instance();

// Test query
$query = "SELECT 
            lp.id, 
            lp.code, 
            lp.name,
            lp.pharmacy_group_id,
            lp.external_id,
            COALESCE(sw.address, '') as address,
            COALESCE(sw.phone, '') as phone,
            COALESCE(sw.email, '') as email
          FROM loyalty_pharmacies lp
          LEFT JOIN sma_warehouses sw ON lp.external_id = sw.id
          LIMIT 1";

$result = $CI->db->query($query)->row_array();
echo "<pre>";
echo "Query result:\n";
var_dump($result);
echo "</pre>";

// Also check warehouse table
echo "<pre>";
echo "Sample warehouse records:\n";
$warehouses = $CI->db->query("SELECT id, code, name, address, phone, external_id FROM sma_warehouses LIMIT 2")->result_array();
var_dump($warehouses);
echo "</pre>";
?>
