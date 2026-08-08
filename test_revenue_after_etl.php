<?php
/**
 * Test Revenue After ETL
 * Quick check to see if revenue is now populating correctly
 */

// Database configuration
$db_config = [
    'host' => 'localhost',
    'user' => 'admin',
    'password' => 'R00tr00t',
    'database' => 'demo',
];

try {
    $mysqli = new mysqli(
        $db_config['host'],
        $db_config['user'],
        $db_config['password'],
        $db_config['database']
    );

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    echo "========================================\n";
    echo "REVENUE TEST AFTER ETL\n";
    echo "========================================\n\n";

    // Test 1: Check sma_sales_aggregates
    echo "1. Checking sma_sales_aggregates (current month: 2025-11):\n";
    echo "-----------------------------------------------------------\n";
    $query = "
        SELECT 
            warehouse_id,
            aggregate_date,
            current_month_sales_amount,
            current_month_sales_count
        FROM sma_sales_aggregates
        WHERE aggregate_year = 2025 AND aggregate_month = 11
        ORDER BY aggregate_date DESC, warehouse_id
        LIMIT 10
    ";
    $result = $mysqli->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo sprintf(
                "  Warehouse %d | Date: %s | Sales: %s SAR | Count: %d\n",
                $row['warehouse_id'],
                $row['aggregate_date'],
                number_format($row['current_month_sales_amount'], 2),
                $row['current_month_sales_count']
            );
        }
    } else {
        echo "  No data found\n";
    }
    echo "\n";

    // Test 2: Check view_sales_monthly
    echo "2. Checking view_sales_monthly (2025-11):\n";
    echo "-----------------------------------------------------------\n";
    $query = "
        SELECT 
            warehouse_id,
            period,
            total_sales_amount,
            sales_count
        FROM view_sales_monthly
        WHERE period = '2025-11'
        LIMIT 10
    ";
    $result = $mysqli->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo sprintf(
                "  Warehouse %d | Period: %s | Sales: %s SAR | Count: %d\n",
                $row['warehouse_id'],
                $row['period'],
                number_format($row['total_sales_amount'], 2),
                $row['sales_count']
            );
        }
    } else {
        echo "  No data found\n";
    }
    echo "\n";

    // Test 3: Check view_cost_center_summary
    echo "3. Checking view_cost_center_summary (2025-11):\n";
    echo "-----------------------------------------------------------\n";
    $query = "
        SELECT 
            level,
            entity_name,
            period,
            kpi_total_revenue,
            kpi_total_cost,
            kpi_profit_loss,
            kpi_profit_margin_pct
        FROM view_cost_center_summary
        WHERE period = '2025-11'
    ";
    $result = $mysqli->query($query);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo sprintf(
                "  Level: %s | Entity: %s\n  Revenue: %s SAR | Cost: %s SAR\n  Profit: %s SAR | Margin: %s%%\n",
                $row['level'],
                $row['entity_name'],
                number_format($row['kpi_total_revenue'], 2),
                number_format($row['kpi_total_cost'], 2),
                number_format($row['kpi_profit_loss'], 2),
                $row['kpi_profit_margin_pct']
            );
        }
    } else {
        echo "  No data found\n";
    }
    echo "\n";

    // Test 4: Direct sales table check
    echo "4. Checking sma_sales directly (Nov 2025):\n";
    echo "-----------------------------------------------------------\n";
    $query = "
        SELECT 
            COUNT(*) as sales_count,
            SUM(grand_total) as total_revenue,
            MIN(date) as first_sale,
            MAX(date) as last_sale
        FROM sma_sales
        WHERE YEAR(date) = 2025 AND MONTH(date) = 11
        AND sale_status = 'completed'
    ";
    $result = $mysqli->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        echo sprintf(
            "  Total Sales: %d transactions\n  Total Revenue: %s SAR\n  First Sale: %s\n  Last Sale: %s\n",
            $row['sales_count'],
            number_format($row['total_revenue'], 2),
            $row['first_sale'] ?? 'N/A',
            $row['last_sale'] ?? 'N/A'
        );
    }
    echo "\n";

    echo "========================================\n";
    echo "TEST COMPLETE\n";
    echo "========================================\n";

    $mysqli->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
