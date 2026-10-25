<?php
/**
 * Diagnostic Script: Cost Centre Revenue Issue
 * Purpose: Check why total revenue is showing zero
 */

// Load CodeIgniter
require_once('index.php');

echo "<h1>Cost Centre Revenue Diagnostic</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #4CAF50; color: white; }
    .error { color: red; }
    .success { color: green; }
    .warning { color: orange; }
    h2 { margin-top: 30px; border-bottom: 2px solid #333; }
</style>";

// Get database connection
$ci =& get_instance();
$ci->load->database();

// Get current period
$period = date('Y-m');
list($year, $month) = explode('-', $period);

echo "<h2>1. Checking Period: $period (Year: $year, Month: $month)</h2>";

// Check if sma_fact_cost_center table exists
echo "<h2>2. Checking if sma_fact_cost_center table exists</h2>";
$query = "SHOW TABLES LIKE 'sma_fact_cost_center'";
$result = $ci->db->query($query);
if ($result->num_rows() > 0) {
    echo "<p class='success'>✓ Table sma_fact_cost_center exists</p>";
} else {
    echo "<p class='error'>✗ Table sma_fact_cost_center does NOT exist!</p>";
    exit;
}

// Check table structure
echo "<h2>3. Table Structure</h2>";
$query = "DESCRIBE sma_fact_cost_center";
$result = $ci->db->query($query);
echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
foreach ($result->result_array() as $row) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check if there's any data in the fact table
echo "<h2>4. Total Records in sma_fact_cost_center</h2>";
$query = "SELECT COUNT(*) as total FROM sma_fact_cost_center";
$result = $ci->db->query($query);
$row = $result->row();
echo "<p>Total records: <strong>{$row->total}</strong></p>";

if ($row->total == 0) {
    echo "<p class='error'>✗ No data in sma_fact_cost_center table! This is why revenue is showing zero.</p>";
} else {
    echo "<p class='success'>✓ Data exists in sma_fact_cost_center</p>";
}

// Check data for current period
echo "<h2>5. Data for Current Period ($period)</h2>";
$query = "
    SELECT 
        warehouse_id,
        period_year,
        period_month,
        total_revenue,
        total_cogs,
        inventory_movement_cost,
        operational_cost,
        updated_at
    FROM sma_fact_cost_center
    WHERE period_year = ? AND period_month = ?
    LIMIT 10
";
$result = $ci->db->query($query, [$year, $month]);
echo "<p>Records for current period: <strong>{$result->num_rows()}</strong></p>";

if ($result->num_rows() > 0) {
    echo "<table><tr><th>Warehouse ID</th><th>Year</th><th>Month</th><th>Revenue</th><th>COGS</th><th>Inv Cost</th><th>Op Cost</th><th>Updated</th></tr>";
    foreach ($result->result_array() as $row) {
        echo "<tr>";
        echo "<td>{$row['warehouse_id']}</td>";
        echo "<td>{$row['period_year']}</td>";
        echo "<td>{$row['period_month']}</td>";
        echo "<td>" . number_format($row['total_revenue'], 2) . "</td>";
        echo "<td>" . number_format($row['total_cogs'], 2) . "</td>";
        echo "<td>" . number_format($row['inventory_movement_cost'], 2) . "</td>";
        echo "<td>" . number_format($row['operational_cost'], 2) . "</td>";
        echo "<td>{$row['updated_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>✗ No data for current period!</p>";
}

// Check all available periods
echo "<h2>6. All Available Periods in Fact Table</h2>";
$query = "
    SELECT 
        period_year,
        period_month,
        COUNT(*) as record_count,
        SUM(total_revenue) as total_revenue,
        MAX(updated_at) as last_updated
    FROM sma_fact_cost_center
    GROUP BY period_year, period_month
    ORDER BY period_year DESC, period_month DESC
    LIMIT 12
";
$result = $ci->db->query($query);
if ($result->num_rows() > 0) {
    echo "<table><tr><th>Year</th><th>Month</th><th>Records</th><th>Total Revenue</th><th>Last Updated</th></tr>";
    foreach ($result->result_array() as $row) {
        echo "<tr>";
        echo "<td>{$row['period_year']}</td>";
        echo "<td>{$row['period_month']}</td>";
        echo "<td>{$row['record_count']}</td>";
        echo "<td>" . number_format($row['total_revenue'], 2) . "</td>";
        echo "<td>{$row['last_updated']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>No data in any period</p>";
}

// Check if there are sales in sma_sales table
echo "<h2>7. Checking Actual Sales Data (sma_sales)</h2>";
$query = "
    SELECT 
        COUNT(*) as total_sales,
        SUM(grand_total) as total_revenue,
        MIN(date) as earliest_sale,
        MAX(date) as latest_sale
    FROM sma_sales
    WHERE YEAR(date) = ? AND MONTH(date) = ?
";
$result = $ci->db->query($query, [$year, $month]);
$row = $result->row();
echo "<table>";
echo "<tr><th>Metric</th><th>Value</th></tr>";
echo "<tr><td>Total Sales Count</td><td>" . number_format($row->total_sales) . "</td></tr>";
echo "<tr><td>Total Revenue</td><td>" . number_format($row->total_revenue, 2) . "</td></tr>";
echo "<tr><td>Earliest Sale</td><td>{$row->earliest_sale}</td></tr>";
echo "<tr><td>Latest Sale</td><td>{$row->latest_sale}</td></tr>";
echo "</table>";

if ($row->total_sales > 0 && $row->total_revenue > 0) {
    echo "<p class='success'>✓ Sales data exists for current period</p>";
    echo "<p class='error'><strong>ISSUE FOUND:</strong> Sales exist in sma_sales but NOT in sma_fact_cost_center. The ETL process needs to be run!</p>";
} else {
    echo "<p class='warning'>⚠ No sales data for current period</p>";
}

// Check warehouses
echo "<h2>8. Checking Warehouses</h2>";
$query = "
    SELECT 
        id,
        code,
        name,
        warehouse_type,
        parent_id
    FROM sma_warehouses
    WHERE warehouse_type IN ('pharmacy', 'branch', 'warehouse')
    LIMIT 10
";
$result = $ci->db->query($query);
echo "<table><tr><th>ID</th><th>Code</th><th>Name</th><th>Type</th><th>Parent ID</th></tr>";
foreach ($result->result_array() as $row) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['code']}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['warehouse_type']}</td>";
    echo "<td>{$row['parent_id']}</td>";
    echo "</tr>";
}
echo "</table>";

// Final diagnosis
echo "<h2>9. DIAGNOSIS</h2>";
echo "<div style='background: #f0f0f0; padding: 20px; border-left: 5px solid #333;'>";
echo "<h3>Likely Issue:</h3>";
echo "<p>The <code>sma_fact_cost_center</code> table is not being populated with data from <code>sma_sales</code>.</p>";
echo "<h3>Solution:</h3>";
echo "<ol>";
echo "<li>Check if ETL stored procedures exist (sp_populate_fact_cost_center or similar)</li>";
echo "<li>Run ETL process to populate fact table from sales data</li>";
echo "<li>Set up scheduled job to keep fact table updated</li>";
echo "</ol>";
echo "</div>";

echo "<h2>10. Checking for ETL Stored Procedures</h2>";
$query = "
    SELECT 
        ROUTINE_NAME,
        ROUTINE_TYPE,
        CREATED,
        LAST_ALTERED
    FROM information_schema.ROUTINES
    WHERE ROUTINE_SCHEMA = DATABASE()
    AND (
        ROUTINE_NAME LIKE '%fact%'
        OR ROUTINE_NAME LIKE '%etl%'
        OR ROUTINE_NAME LIKE '%populate%'
        OR ROUTINE_NAME LIKE '%cost%center%'
    )
";
$result = $ci->db->query($query);
if ($result->num_rows() > 0) {
    echo "<table><tr><th>Procedure Name</th><th>Type</th><th>Created</th><th>Last Altered</th></tr>";
    foreach ($result->result_array() as $row) {
        echo "<tr>";
        echo "<td>{$row['ROUTINE_NAME']}</td>";
        echo "<td>{$row['ROUTINE_TYPE']}</td>";
        echo "<td>{$row['CREATED']}</td>";
        echo "<td>{$row['LAST_ALTERED']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>✗ No ETL stored procedures found!</p>";
}
