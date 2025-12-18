<?php
/**
 * Test Script: Agent Name Retrieval Logic
 *
 * This script tests the agent name retrieval logic for the Purchase Per Invoice report.
 * It verifies the 3-step process:
 * 1. Get supplier_id from purchase/payment/return
 * 2. Get parent_code from sma_companies (supplier)
 * 3. Get agent name from sma_companies where sequence_code = parent_code
 */

// Load CodeIgniter
require_once('index.php');

$CI =& get_instance();
$CI->load->database();

echo "<h1>Agent Name Retrieval Logic Test</h1>";
echo "<style>
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { background-color: #e7f3fe; padding: 10px; margin: 10px 0; border-left: 4px solid #2196F3; }
</style>";

// Test 1: Test agent name retrieval for all suppliers
echo "<h2>Test 1: Agent Name Retrieval for All Suppliers</h2>";
echo "<div class='info'>This test shows all suppliers and their associated agents.</div>";

$query = $CI->db->query("
    SELECT 
        s.id as supplier_id,
        s.name as supplier_name,
        s.sequence_code as supplier_code,
        s.parent_code,
        agent.sequence_code as agent_code,
        agent.name as agent_name
    FROM sma_companies s
    LEFT JOIN sma_companies agent ON agent.sequence_code = s.parent_code
    WHERE s.id IN (SELECT DISTINCT supplier_id FROM sma_purchases WHERE supplier_id IS NOT NULL)
    ORDER BY s.name
    LIMIT 20
");

if ($query->num_rows() > 0) {
    echo "<table>";
    echo "<tr>
            <th>Supplier ID</th>
            <th>Supplier Name</th>
            <th>Supplier Code</th>
            <th>Parent Code</th>
            <th>Agent Code</th>
            <th>Agent Name</th>
            <th>Status</th>
          </tr>";

    foreach ($query->result_array() as $row) {
        $status = !empty($row['agent_name']) ? "<span class='success'>✓ Found</span>" : "<span class='error'>✗ No Agent</span>";
        echo "<tr>
                <td>{$row['supplier_id']}</td>
                <td>{$row['supplier_name']}</td>
                <td>{$row['supplier_code']}</td>
                <td>{$row['parent_code']}</td>
                <td>{$row['agent_code']}</td>
                <td>{$row['agent_name']}</td>
                <td>{$status}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>No suppliers found!</p>";
}

// Test 2: Test purchase query with agent name
echo "<h2>Test 2: Purchase Query with Agent Name</h2>";
echo "<div class='info'>This test shows recent purchases with their agent names.</div>";

$query = $CI->db->query("
    SELECT 
        'Purchase' as type,
        p.date,
        p.reference_no as invoice,
        COALESCE(agent.name, '') as agent_name,
        s.sequence_code as supplier_no,
        s.name as supplier_name,
        p.grand_total as amount
    FROM sma_purchases p
    LEFT JOIN sma_companies s ON p.supplier_id = s.id
    LEFT JOIN sma_companies agent ON agent.sequence_code = s.parent_code
    WHERE (p.note IS NULL OR p.note != 'import from excel')
    AND (p.return_purchase_ref IS NULL OR p.return_purchase_ref = '')
    ORDER BY p.date DESC
    LIMIT 10
");

if ($query->num_rows() > 0) {
    echo "<table>";
    echo "<tr>
            <th>Type</th>
            <th>Date</th>
            <th>Invoice</th>
            <th>Agent Name</th>
            <th>Supplier No</th>
            <th>Supplier Name</th>
            <th>Amount</th>
          </tr>";

    foreach ($query->result_array() as $row) {
        echo "<tr>
                <td>{$row['type']}</td>
                <td>{$row['date']}</td>
                <td>{$row['invoice']}</td>
                <td>" . (!empty($row['agent_name']) ? $row['agent_name'] : '<span class="error">N/A</span>') . "</td>
                <td>{$row['supplier_no']}</td>
                <td>{$row['supplier_name']}</td>
                <td>" . number_format($row['amount'], 2) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>No purchases found!</p>";
}

// Test 3: Test payment query with agent name
echo "<h2>Test 3: Payment Query with Agent Name</h2>";
echo "<div class='info'>This test shows recent payments with their agent names.</div>";

$query = $CI->db->query("
    SELECT 
        'Payment' as type,
        pay.date,
        CONCAT('PAY-', pay.id) as invoice,
        COALESCE(p.reference_no, '') as purchase_ref,
        COALESCE(agent.name, '') as agent_name,
        COALESCE(s.sequence_code, '') as supplier_no,
        COALESCE(s.name, 'Direct Payment') as supplier_name,
        pay.amount
    FROM sma_payments pay
    LEFT JOIN sma_purchases p ON pay.purchase_id = p.id
    LEFT JOIN sma_companies s ON p.supplier_id = s.id
    LEFT JOIN sma_companies agent ON agent.sequence_code = s.parent_code
    WHERE pay.purchase_id IS NOT NULL
    AND (pay.sale_id IS NULL OR pay.sale_id = 0)
    AND (pay.return_id IS NULL OR pay.return_id = 0)
    ORDER BY pay.date DESC
    LIMIT 10
");

if ($query->num_rows() > 0) {
    echo "<table>";
    echo "<tr>
            <th>Type</th>
            <th>Date</th>
            <th>Payment ID</th>
            <th>Purchase Ref</th>
            <th>Agent Name</th>
            <th>Supplier No</th>
            <th>Supplier Name</th>
            <th>Amount</th>
          </tr>";

    foreach ($query->result_array() as $row) {
        echo "<tr>
                <td>{$row['type']}</td>
                <td>{$row['date']}</td>
                <td>{$row['invoice']}</td>
                <td>{$row['purchase_ref']}</td>
                <td>" . (!empty($row['agent_name']) ? $row['agent_name'] : '<span class="error">N/A</span>') . "</td>
                <td>{$row['supplier_no']}</td>
                <td>{$row['supplier_name']}</td>
                <td>" . number_format($row['amount'], 2) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>No payments found!</p>";
}

// Test 4: Test return query with agent name
echo "<h2>Test 4: Return Query with Agent Name</h2>";
echo "<div class='info'>This test shows recent returns with their agent names.</div>";

$query = $CI->db->query("
    SELECT 
        'Return' as type,
        rs.date,
        rs.reference_no as invoice,
        COALESCE(agent.name, '') as agent_name,
        COALESCE(s.sequence_code, '') as supplier_no,
        COALESCE(s.name, rs.supplier) as supplier_name,
        COALESCE(rs.grand_total, 0) as amount
    FROM sma_returns_supplier rs
    LEFT JOIN sma_companies s ON rs.supplier_id = s.id
    LEFT JOIN sma_companies agent ON agent.sequence_code = s.parent_code
    WHERE (rs.note IS NULL OR rs.note != 'import from excel')
    ORDER BY rs.date DESC
    LIMIT 10
");

if ($query->num_rows() > 0) {
    echo "<table>";
    echo "<tr>
            <th>Type</th>
            <th>Date</th>
            <th>Invoice</th>
            <th>Agent Name</th>
            <th>Supplier No</th>
            <th>Supplier Name</th>
            <th>Amount</th>
          </tr>";

    foreach ($query->result_array() as $row) {
        echo "<tr>
                <td>{$row['type']}</td>
                <td>{$row['date']}</td>
                <td>{$row['invoice']}</td>
                <td>" . (!empty($row['agent_name']) ? $row['agent_name'] : '<span class="error">N/A</span>') . "</td>
                <td>{$row['supplier_no']}</td>
                <td>{$row['supplier_name']}</td>
                <td>" . number_format($row['amount'], 2) . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>No returns found!</p>";
}

// Test 5: Summary statistics
echo "<h2>Test 5: Summary Statistics</h2>";

$stats = [];

// Count suppliers with agents
$query = $CI->db->query("
    SELECT 
        COUNT(*) as total_suppliers,
        SUM(CASE WHEN agent.name IS NOT NULL THEN 1 ELSE 0 END) as suppliers_with_agents,
        SUM(CASE WHEN agent.name IS NULL THEN 1 ELSE 0 END) as suppliers_without_agents
    FROM sma_companies s
    LEFT JOIN sma_companies agent ON agent.sequence_code = s.parent_code
    WHERE s.id IN (SELECT DISTINCT supplier_id FROM sma_purchases WHERE supplier_id IS NOT NULL)
");
$stats = $query->row_array();

echo "<div class='info'>";
echo "<h3>Agent Coverage Statistics</h3>";
echo "<ul>";
echo "<li>Total Suppliers: <strong>{$stats['total_suppliers']}</strong></li>";
echo "<li>Suppliers with Agents: <strong class='success'>{$stats['suppliers_with_agents']}</strong></li>";
echo "<li>Suppliers without Agents: <strong class='error'>{$stats['suppliers_without_agents']}</strong></li>";
$coverage = $stats['total_suppliers'] > 0 ? round(($stats['suppliers_with_agents'] / $stats['total_suppliers']) * 100, 2) : 0;
echo "<li>Agent Coverage: <strong>" . $coverage . "%</strong></li>";
echo "</ul>";
echo "</div>";

echo "<h3>✅ Test Complete!</h3>";
echo "<p>Review the results above to verify the agent name retrieval logic is working correctly.</p>";
?>

