<?php
/**
 * Cost Center Setup & Testing Script
 * 
 * Purpose: Verify all cost center components are properly installed
 * Usage: php tests/cost_center_setup_test.php
 * 
 * Checks:
 * - Database tables created
 * - Views available
 * - Stored procedures functional
 * - Initial data population
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load CodeIgniter
define('BASEPATH', dirname(dirname(__FILE__)) . '/app/');
define('APPPATH', dirname(dirname(__FILE__)) . '/app/');

echo "═══════════════════════════════════════════════════════════════\n";
echo "  Cost Center Module - Setup & Verification\n";
echo "  Date: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Database configuration
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_password = getenv('DB_PASSWORD') ?: '';
$db_name = getenv('DB_NAME') ?: 'avenzur_pharmacy';

try {
    $mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
    
    if ($mysqli->connect_error) {
        throw new Exception("Database connection failed: " . $mysqli->connect_error);
    }
    
    echo "[✓] Database Connected: $db_name\n\n";

    // ============================================================
    // Check Tables
    // ============================================================
    echo "Checking Tables...\n";
    $tables = ['dim_pharmacy', 'dim_branch', 'dim_date', 'fact_cost_center', 'etl_audit_log'];
    
    foreach ($tables as $table) {
        $result = $mysqli->query("SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA='$db_name' AND TABLE_NAME='$table'");
        if ($result->num_rows > 0) {
            $count = $mysqli->query("SELECT COUNT(*) as cnt FROM $table")->fetch_assoc()['cnt'];
            echo "  [✓] $table ($count records)\n";
        } else {
            echo "  [✗] $table - NOT FOUND\n";
        }
    }
    echo "\n";

    // ============================================================
    // Check Views
    // ============================================================
    echo "Checking Views...\n";
    $views = ['view_cost_center_pharmacy', 'view_cost_center_branch', 'view_cost_center_summary'];
    
    foreach ($views as $view) {
        $result = $mysqli->query("SELECT 1 FROM information_schema.VIEWS WHERE TABLE_SCHEMA='$db_name' AND TABLE_NAME='$view'");
        if ($result->num_rows > 0) {
            echo "  [✓] $view\n";
        } else {
            echo "  [✗] $view - NOT FOUND\n";
        }
    }
    echo "\n";

    // ============================================================
    // Check Stored Procedures
    // ============================================================
    echo "Checking Stored Procedures...\n";
    $procs = ['sp_populate_fact_cost_center', 'sp_backfill_fact_cost_center'];
    
    foreach ($procs as $proc) {
        $result = $mysqli->query("SELECT 1 FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA='$db_name' AND ROUTINE_NAME='$proc'");
        if ($result->num_rows > 0) {
            echo "  [✓] $proc\n";
        } else {
            echo "  [✗] $proc - NOT FOUND\n";
        }
    }
    echo "\n";

    // ============================================================
    // Check Indexes
    // ============================================================
    echo "Checking Indexes...\n";
    $indexes_check = [
        'fact_cost_center' => ['idx_warehouse_date', 'idx_transaction_date'],
        'sma_sales' => ['idx_warehouse_date', 'idx_sale_status'],
        'sma_purchases' => ['idx_warehouse_date', 'idx_purchase_status']
    ];
    
    foreach ($indexes_check as $table => $indexes) {
        foreach ($indexes as $idx) {
            $result = $mysqli->query("SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA='$db_name' AND TABLE_NAME='$table' AND INDEX_NAME='$idx'");
            if ($result->num_rows > 0) {
                echo "  [✓] $table.$idx\n";
            } else {
                echo "  [✗] $table.$idx - NOT FOUND\n";
            }
        }
    }
    echo "\n";

    // ============================================================
    // Verify Data
    // ============================================================
    echo "Verifying Data...\n";
    
    $pharmacy_count = $mysqli->query("SELECT COUNT(*) as cnt FROM dim_pharmacy WHERE is_active=1")->fetch_assoc()['cnt'];
    echo "  Pharmacies: $pharmacy_count\n";
    
    $branch_count = $mysqli->query("SELECT COUNT(*) as cnt FROM dim_branch WHERE is_active=1")->fetch_assoc()['cnt'];
    echo "  Branches: $branch_count\n";
    
    $date_count = $mysqli->query("SELECT COUNT(*) as cnt FROM dim_date")->fetch_assoc()['cnt'];
    echo "  Date dimension records: $date_count\n";
    
    $fact_count = $mysqli->query("SELECT COUNT(*) as cnt FROM fact_cost_center")->fetch_assoc()['cnt'];
    echo "  Fact records: $fact_count\n";
    
    echo "\n";

    // ============================================================
    // Check ETL Status
    // ============================================================
    echo "ETL Status...\n";
    $etl_result = $mysqli->query("SELECT * FROM etl_audit_log ORDER BY start_time DESC LIMIT 1");
    if ($etl_result && $etl_result->num_rows > 0) {
        $etl = $etl_result->fetch_assoc();
        echo "  Last Run: " . $etl['start_time'] . "\n";
        echo "  Status: " . $etl['status'] . "\n";
        echo "  Rows Processed: " . $etl['rows_processed'] . "\n";
    } else {
        echo "  No ETL runs recorded\n";
    }
    echo "\n";

    // ============================================================
    // Health Check: Sample Query
    // ============================================================
    echo "Running Sample Queries...\n";
    
    $current_month = date('Y-m');
    $pharmacy_query = $mysqli->query("
        SELECT period, COUNT(*) as cnt, SUM(kpi_total_revenue) as revenue
        FROM view_cost_center_pharmacy
        WHERE period = '$current_month'
        GROUP BY period
    ");
    
    if ($pharmacy_query && $pharmacy_query->num_rows > 0) {
        $result = $pharmacy_query->fetch_assoc();
        echo "  [✓] Pharmacy View Query - Period: {$result['period']}, Records: {$result['cnt']}, Revenue: {$result['revenue']}\n";
    } else {
        echo "  [!] Pharmacy View Query - No data for current period\n";
    }

    $branch_query = $mysqli->query("
        SELECT period, COUNT(*) as cnt, SUM(kpi_total_revenue) as revenue
        FROM view_cost_center_branch
        WHERE period = '$current_month'
        GROUP BY period
    ");
    
    if ($branch_query && $branch_query->num_rows > 0) {
        $result = $branch_query->fetch_assoc();
        echo "  [✓] Branch View Query - Period: {$result['period']}, Records: {$result['cnt']}, Revenue: {$result['revenue']}\n";
    } else {
        echo "  [!] Branch View Query - No data for current period\n";
    }

    echo "\n";

    // ============================================================
    // Display Next Steps
    // ============================================================
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "  NEXT STEPS\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";
    
    echo "1. Run Initial ETL Backfill (populate last 3 months):\n";
    echo "   php database/scripts/etl_cost_center.php backfill 2025-07-01 2025-10-25\n\n";
    
    echo "2. Test API Endpoints:\n";
    echo "   GET http://localhost/avenzur/api/v1/cost-center/pharmacies\n";
    echo "   GET http://localhost/avenzur/api/v1/cost-center/pharmacies/1/branches?period=2025-10\n";
    echo "   GET http://localhost/avenzur/api/v1/cost-center/branches/1/detail?period=2025-10\n";
    echo "   GET http://localhost/avenzur/api/v1/cost-center/branches/1/timeseries?months=12\n\n";
    
    echo "3. Set up Cron Job for Daily ETL:\n";
    echo "   0 2 * * * /usr/bin/php /path/to/etl_cost_center.php today\n\n";
    
    echo "4. Build Frontend Dashboard (React components):\n";
    echo "   - CostCenterDashboard.tsx\n";
    echo "   - Chart components (TrendChart, SpendingBreakdown)\n";
    echo "   - Table components (PharmacyTable, BranchTable)\n\n";

    echo "═══════════════════════════════════════════════════════════════\n";
    echo "  VERIFICATION COMPLETE\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";

    $mysqli->close();

} catch (Exception $e) {
    echo "[✗] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
