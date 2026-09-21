#!/usr/bin/env php
<?php
/**
 * Budget Dashboard Data Test Script
 * Simulates the budget data fetching that the dashboard will perform
 */

echo "Starting budget data test...\n";
flush();

// Change to project root
chdir('/Users/rajivepai/Projects/Avenzur/V2/avenzur');

echo "Changed directory to: " . getcwd() . "\n";
flush();

// Load CodeIgniter
echo "Loading CodeIgniter...\n";
flush();

try {
    require_once 'index.php';
    echo "CodeIgniter loaded successfully\n";
    flush();
} catch (Exception $e) {
    echo "Error loading CodeIgniter: " . $e->getMessage() . "\n";
    exit(1);
}

// Get CI instance
echo "Getting CI instance...\n";
flush();

$CI = &get_instance();

echo "Got CI instance\n";
flush();

// Test 1: Get allocated budgets
echo "\n=== TEST 1: Allocated Budgets ===\n";
$query = "SELECT * FROM sma_budget_allocation WHERE period = '2025-10' AND is_active = 1 ORDER BY allocated_at DESC LIMIT 5";
$result = $CI->db->query($query);
$rows = $result->result_array();
echo "Found " . count($rows) . " allocations\n";
if ($rows) {
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}

// Test 2: Get budget tracking
echo "\n=== TEST 2: Budget Tracking ===\n";
$query = "SELECT * FROM sma_budget_tracking WHERE period = '2025-10' ORDER BY updated_at DESC LIMIT 5";
$result = $CI->db->query($query);
$rows = $result->result_array();
echo "Found " . count($rows) . " tracking records\n";
if ($rows) {
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}

// Test 3: Get forecast
echo "\n=== TEST 3: Budget Forecast ===\n";
$query = "SELECT * FROM sma_budget_forecast WHERE period = '2025-10' ORDER BY created_at DESC LIMIT 5";
$result = $CI->db->query($query);
$rows = $result->result_array();
echo "Found " . count($rows) . " forecast records\n";
if ($rows) {
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}

// Test 4: Get alerts
echo "\n=== TEST 4: Budget Alerts ===\n";
$query = "SELECT * FROM sma_alert_events WHERE period = '2025-10' ORDER BY triggered_at DESC LIMIT 5";
$result = $CI->db->query($query);
$rows = $result->result_array();
echo "Found " . count($rows) . " alert events\n";
if ($rows) {
    echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}

echo "\n✅ All tests completed successfully!\n";
