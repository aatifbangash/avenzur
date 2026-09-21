<?php
/**
 * Cost Center ETL Pipeline Script
 * 
 * Purpose: Populate fact_cost_center table with aggregated data
 * Can be run:
 * 1. Manually: php etl_cost_center.php
 * 2. As cron: 0 2 * * * /usr/bin/php /path/to/etl_cost_center.php
 * 
 * Usage:
 *   php etl_cost_center.php [date]  // Specific date (YYYY-MM-DD)
 *   php etl_cost_center.php          // Today's date
 *   php etl_cost_center.php backfill [start_date] [end_date]  // Date range
 */

// Get command line arguments
$mode = isset($argv[1]) ? $argv[1] : 'today';
$date_param = isset($argv[2]) ? $argv[2] : date('Y-m-d');
$end_date_param = isset($argv[3]) ? $argv[3] : date('Y-m-d');

// Database configuration
$db_config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'user' => getenv('DB_USER') ?: 'admin',
    'password' => getenv('DB_PASSWORD') ?: 'R00tr00t',
    'database' => getenv('DB_NAME') ?: 'rawabi_jeddah',
];

try {
    // Connect to database
    $mysqli = new mysqli(
        $db_config['host'],
        $db_config['user'],
        $db_config['password'],
        $db_config['database']
    );

    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Cost Center ETL Pipeline Started\n";
    echo "Mode: $mode\n";

    switch ($mode) {
        case 'today':
            $date = date('Y-m-d');
            echo "Processing date: $date\n";
            execute_etl($mysqli, $date);
            break;

        case 'date':
            if (!validate_date($date_param)) {
                throw new Exception("Invalid date format: $date_param (use YYYY-MM-DD)");
            }
            echo "Processing date: $date_param\n";
            execute_etl($mysqli, $date_param);
            break;

        case 'backfill':
            if (!validate_date($date_param) || !validate_date($end_date_param)) {
                throw new Exception("Invalid date format (use YYYY-MM-DD)");
            }
            echo "Backfilling from $date_param to $end_date_param\n";
            execute_backfill($mysqli, $date_param, $end_date_param);
            break;

        default:
            throw new Exception("Unknown mode: $mode (use: today, date, backfill)");
    }

    echo "[" . date('Y-m-d H:i:s') . "] Cost Center ETL Pipeline Completed\n";
    $mysqli->close();

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Execute ETL for a single date
 */
function execute_etl($mysqli, $transaction_date) {
    $log_id = null;

    try {
        // Start transaction
        $mysqli->begin_transaction();

        // Log start
        $stmt = $mysqli->prepare("
            INSERT INTO etl_audit_log (process_name, start_time, status)
            VALUES ('sp_populate_fact_cost_center', NOW(), 'STARTED')
        ");
        $stmt->execute();
        $log_id = $mysqli->insert_id;

        // Delete existing data for safety (allows re-runs)
        $delete_stmt = $mysqli->prepare("DELETE FROM sma_fact_cost_center WHERE DATE(transaction_date) = ?");
        $delete_stmt->bind_param('s', $transaction_date);
        $delete_stmt->execute();
        echo "  - Deleted existing records for $transaction_date\n";

        // ============================================================
        // Process Sales Revenue
        // ============================================================
        // UPDATED: Now includes warehouses (type="warehouse") at pharmacy level
        // - Branches: pharmacy_id = parent_id (pharmacy warehouse_id)
        // - Warehouses: pharmacy_id = NULL (company-level central warehouses)
        // - Both roll up to pharmacy group revenue at company level
        // ============================================================
        $sales_query = "
            INSERT INTO sma_fact_cost_center (
                warehouse_id, warehouse_name, warehouse_type,
                pharmacy_id, pharmacy_name, branch_id, branch_name,
                parent_warehouse_id, transaction_date, period_year, period_month,
                total_revenue
            )
            SELECT 
                w.id,
                w.name,
                COALESCE(w.warehouse_type, 'warehouse'),
                CASE 
                    WHEN w.warehouse_type = 'branch' THEN w.parent_id 
                    WHEN w.warehouse_type = 'warehouse' AND w.parent_id IS NULL THEN NULL
                    ELSE w.parent_id
                END AS pharmacy_id,
                CASE 
                    WHEN w.warehouse_type = 'branch' THEN pw.name 
                    WHEN w.warehouse_type = 'warehouse' AND w.parent_id IS NULL THEN NULL
                    ELSE pw.name
                END AS pharmacy_name,
                CASE WHEN w.warehouse_type = 'branch' THEN w.id ELSE NULL END AS branch_id,
                CASE WHEN w.warehouse_type = 'branch' THEN w.name ELSE NULL END AS branch_name,
                w.parent_id,
                DATE(s.date) AS transaction_date,
                YEAR(s.date) AS period_year,
                MONTH(s.date) AS period_month,
                COALESCE(SUM(s.grand_total), 0) AS total_revenue
            FROM sma_sales s
            LEFT JOIN sma_warehouses w ON s.warehouse_id = w.id
            LEFT JOIN sma_warehouses pw ON w.parent_id = pw.id
            WHERE DATE(s.date) = ?
            AND s.sale_status = 'completed'
            GROUP BY 
                w.id, 
                w.name, 
                w.warehouse_type, 
                w.parent_id,
                pw.id,
                pw.name,
                DATE(s.date),
                YEAR(s.date),
                MONTH(s.date)
            ON DUPLICATE KEY UPDATE total_revenue = VALUES(total_revenue)
        ";

        $stmt = $mysqli->prepare($sales_query);
        $stmt->bind_param('s', $transaction_date);
        $stmt->execute();
        $revenue_count = $stmt->affected_rows;
        echo "  - Processed $revenue_count revenue records\n";

        // ============================================================
        // Process Purchase Costs (COGS)
        // ============================================================
        // UPDATED: Now includes warehouses (type="warehouse") at pharmacy level
        // - Branches: pharmacy_id = parent_id (pharmacy warehouse_id)
        // - Warehouses: pharmacy_id = NULL (company-level central warehouses)
        // - Both roll up to pharmacy group costs at company level
        // ============================================================
        $purchases_query = "
            INSERT INTO sma_fact_cost_center (
                warehouse_id, warehouse_name, warehouse_type,
                pharmacy_id, pharmacy_name, branch_id, branch_name,
                parent_warehouse_id, transaction_date, period_year, period_month,
                total_cogs
            )
            SELECT 
                w.id,
                w.name,
                COALESCE(w.warehouse_type, 'warehouse'),
                CASE 
                    WHEN w.warehouse_type = 'branch' THEN w.parent_id 
                    WHEN w.warehouse_type = 'warehouse' AND w.parent_id IS NULL THEN NULL
                    ELSE w.parent_id
                END AS pharmacy_id,
                CASE 
                    WHEN w.warehouse_type = 'branch' THEN pw.name 
                    WHEN w.warehouse_type = 'warehouse' AND w.parent_id IS NULL THEN NULL
                    ELSE pw.name
                END AS pharmacy_name,
                CASE WHEN w.warehouse_type = 'branch' THEN w.id ELSE NULL END AS branch_id,
                CASE WHEN w.warehouse_type = 'branch' THEN w.name ELSE NULL END AS branch_name,
                w.parent_id,
                DATE(p.date) AS transaction_date,
                YEAR(p.date) AS period_year,
                MONTH(p.date) AS period_month,
                COALESCE(SUM(p.grand_total), 0) AS total_cogs
            FROM sma_purchases p
            LEFT JOIN sma_warehouses w ON p.warehouse_id = w.id
            LEFT JOIN sma_warehouses pw ON w.parent_id = pw.id
            WHERE DATE(p.date) = ?
            AND p.status = 'received'
            GROUP BY 
                w.id, 
                w.name, 
                w.warehouse_type, 
                w.parent_id,
                pw.id,
                pw.name,
                DATE(p.date),
                YEAR(p.date),
                MONTH(p.date)
            ON DUPLICATE KEY UPDATE total_cogs = VALUES(total_cogs)
        ";

        $stmt = $mysqli->prepare($purchases_query);
        $stmt->bind_param('s', $transaction_date);
        $stmt->execute();
        $purchase_count = $stmt->affected_rows;
        echo "  - Processed $purchase_count purchase cost records\n";

        // ============================================================
        // Process Operational Costs (shipping, surcharge)
        // ============================================================
        $operational_query = "
            UPDATE sma_fact_cost_center f
            SET f.operational_cost = (
                SELECT COALESCE(SUM(p.shipping + COALESCE(p.surcharge, 0)), 0)
                FROM sma_purchases p
                LEFT JOIN sma_warehouses w ON p.warehouse_id = w.id
                WHERE w.id = f.warehouse_id
                AND DATE(p.date) = ?
                AND p.status = 'received'
            )
            WHERE DATE(f.transaction_date) = ?
        ";

        $stmt = $mysqli->prepare($operational_query);
        $stmt->bind_param('ss', $transaction_date, $transaction_date);
        $stmt->execute();
        echo "  - Updated operational costs\n";

        // Commit transaction
        $mysqli->commit();

        // Update log status
        $duration = 0; // You can add timing if needed
        $update_log = $mysqli->prepare("
            UPDATE etl_audit_log 
            SET end_time = NOW(),
                status = 'COMPLETED',
                duration_seconds = ?,
                rows_processed = (SELECT COUNT(*) FROM sma_fact_cost_center WHERE DATE(transaction_date) = ?)
            WHERE id = ?
        ");
        $update_log->bind_param('isi', $duration, $transaction_date, $log_id);
        $update_log->execute();

        echo "  ✓ ETL completed successfully for $transaction_date\n";

    } catch (Exception $e) {
        $mysqli->rollback();
        echo "  ✗ ETL failed: " . $e->getMessage() . "\n";

        if ($log_id) {
            $update_log = $mysqli->prepare("
                UPDATE etl_audit_log 
                SET end_time = NOW(),
                    status = 'FAILED',
                    error_message = ?
                WHERE id = ?
            ");
            $error_msg = $e->getMessage();
            $update_log->bind_param('si', $error_msg, $log_id);
            $update_log->execute();
        }
        throw $e;
    }
}

/**
 * Execute backfill for a date range
 */
function execute_backfill($mysqli, $start_date, $end_date) {
    $current = new DateTime($start_date);
    $end = new DateTime($end_date);
    $count = 0;
    $failed = 0;

    while ($current <= $end) {
        $date_str = $current->format('Y-m-d');
        try {
            execute_etl($mysqli, $date_str);
            $count++;
        } catch (Exception $e) {
            $failed++;
            echo "  Failed on $date_str: " . $e->getMessage() . "\n";
        }
        $current->modify('+1 day');
    }

    echo "\nBackfill Summary:\n";
    echo "  Total dates processed: $count\n";
    echo "  Failed: $failed\n";
}

/**
 * Validate date format
 */
function validate_date($date_str, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date_str);
    return $d && $d->format($format) === $date_str;
}
