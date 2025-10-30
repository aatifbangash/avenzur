<?php
/**
 * ETL Sales Aggregates Script
 * 
 * Purpose: Populate sma_sales_aggregates and sma_purchases_aggregates tables
 *          with daily metrics including today, current month, and YTD sales/costs
 * 
 * Run: 
 * 1. Manually: php etl_sales_aggregates.php
 * 2. As cron:/15 * * * * /usr/bin/php /path/to/etl_sales_aggregates.php  (every 15 min)
 *             0 2 * * * /usr/bin/php /path/to/etl_sales_aggregates.php backfill (daily at 2am)
 * 
 * Usage:
 *   php etl_sales_aggregates.php              // Today's aggregates (15-min frequency)
 *   php etl_sales_aggregates.php date YYYY-MM-DD  // Specific date
 *   php etl_sales_aggregates.php backfill [start] [end]  // Date range
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

    echo "[" . date('Y-m-d H:i:s') . "] Sales Aggregates ETL Pipeline Started\n";
    echo "Mode: $mode\n";

    switch ($mode) {
        case 'today':
            $date = date('Y-m-d');
            echo "Processing aggregates for: $date\n";
            execute_etl($mysqli, $date);
            break;

        case 'date':
            if (!validate_date($date_param)) {
                throw new Exception("Invalid date format: $date_param (use YYYY-MM-DD)");
            }
            echo "Processing aggregates for: $date_param\n";
            execute_etl($mysqli, $date_param);
            break;

        case 'backfill':
            if (!validate_date($date_param) || !validate_date($end_date_param)) {
                throw new Exception("Invalid date format (use YYYY-MM-DD)");
            }
            echo "Backfilling aggregates from $date_param to $end_date_param\n";
            execute_backfill($mysqli, $date_param, $end_date_param);
            break;

        default:
            throw new Exception("Unknown mode: $mode (use: today, date, backfill)");
    }

    echo "[" . date('Y-m-d H:i:s') . "] Sales Aggregates ETL Pipeline Completed\n";
    $mysqli->close();

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Execute ETL for a single date
 */
function execute_etl($mysqli, $aggregate_date) {
    $log_id = null;

    try {
        // Start transaction
        $mysqli->begin_transaction();

        // Log start
        $stmt = $mysqli->prepare("
            INSERT INTO etl_sales_aggregates_log 
            (process_name, aggregate_date, start_time, status)
            VALUES ('etl_sales_aggregates', ?, NOW(), 'STARTED')
        ");
        $stmt->bind_param('s', $aggregate_date);
        $stmt->execute();
        $log_id = $mysqli->insert_id;

        echo "\n=== Sales Aggregates for $aggregate_date ===\n";

        // ============================================================
        // Calculate Date Parameters
        // ============================================================
        $timestamp = strtotime($aggregate_date);
        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        $day = date('d', $timestamp);
        
        // Month start
        $month_start = $year . '-' . $month . '-01';
        
        // Year start
        $year_start = $year . '-01-01';
        
        // Previous day
        $prev_timestamp = strtotime($aggregate_date . ' -1 day');
        $previous_date = date('Y-m-d', $prev_timestamp);

        echo "  Date: $aggregate_date\n";
        echo "  Month Start: $month_start\n";
        echo "  Year Start: $year_start\n";
        echo "  Previous Day: $previous_date\n";

        // ============================================================
        // STEP 1: Populate sma_sales_aggregates
        // ============================================================
        echo "\n  [STEP 1] Populating Sales Aggregates...\n";

        $sales_query = "
            INSERT INTO sma_sales_aggregates 
            (
                warehouse_id, aggregate_date, aggregate_year, aggregate_month,
                today_sales_amount, today_sales_count,
                current_month_sales_amount, current_month_sales_count, current_month_start_date,
                ytd_sales_amount, ytd_sales_count, ytd_start_date,
                previous_day_sales_amount, previous_day_sales_count,
                last_updated
            )
            SELECT
                w.id,
                ? AS aggregate_date,
                ? AS aggregate_year,
                ? AS aggregate_month,
                
                -- Today's Sales
                COALESCE(SUM(CASE WHEN DATE(s.date) = ? THEN s.grand_total ELSE 0 END), 0),
                COALESCE(COUNT(DISTINCT CASE WHEN DATE(s.date) = ? THEN s.id END), 0),
                
                -- Current Month Sales (1st to today)
                COALESCE(SUM(CASE 
                    WHEN DATE(s.date) >= ? AND DATE(s.date) <= ? 
                    THEN s.grand_total ELSE 0 END), 0),
                COALESCE(COUNT(DISTINCT CASE 
                    WHEN DATE(s.date) >= ? AND DATE(s.date) <= ? 
                    THEN s.id END), 0),
                ?,
                
                -- YTD Sales (Jan 1 to today)
                COALESCE(SUM(CASE 
                    WHEN DATE(s.date) >= ? AND DATE(s.date) <= ? 
                    THEN s.grand_total ELSE 0 END), 0),
                COALESCE(COUNT(DISTINCT CASE 
                    WHEN DATE(s.date) >= ? AND DATE(s.date) <= ? 
                    THEN s.id END), 0),
                ?,
                
                -- Previous Day Sales
                COALESCE(SUM(CASE WHEN DATE(s.date) = ? THEN s.grand_total ELSE 0 END), 0),
                COALESCE(COUNT(DISTINCT CASE WHEN DATE(s.date) = ? THEN s.id END), 0),
                
                NOW()
            FROM sma_warehouses w
            LEFT JOIN sma_sales s ON w.id = s.warehouse_id 
                AND s.sale_status IN ('completed', 'completed_partial')
            WHERE w.id IS NOT NULL
            GROUP BY w.id
            
            ON DUPLICATE KEY UPDATE
                today_sales_amount = VALUES(today_sales_amount),
                today_sales_count = VALUES(today_sales_count),
                current_month_sales_amount = VALUES(current_month_sales_amount),
                current_month_sales_count = VALUES(current_month_sales_count),
                ytd_sales_amount = VALUES(ytd_sales_amount),
                ytd_sales_count = VALUES(ytd_sales_count),
                previous_day_sales_amount = VALUES(previous_day_sales_amount),
                previous_day_sales_count = VALUES(previous_day_sales_count),
                last_updated = NOW()
        ";

        $stmt = $mysqli->prepare($sales_query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }

        $stmt->bind_param(
            'sisssssssssssssss',
            $aggregate_date, $year, $month,
            $aggregate_date, $aggregate_date,
            $month_start, $aggregate_date,
            $month_start, $aggregate_date,
            $month_start,
            $year_start, $aggregate_date,
            $year_start, $aggregate_date,
            $year_start,
            $previous_date, $previous_date
        );
        $stmt->execute();
        $sales_rows = $stmt->affected_rows;
        echo "    ✓ Updated $sales_rows warehouse sales records\n";

        // ============================================================
        // STEP 2: Populate sma_purchases_aggregates
        // ============================================================
        echo "\n  [STEP 2] Populating Purchases Aggregates...\n";

        $purchases_query = "
            INSERT INTO sma_purchases_aggregates 
            (
                warehouse_id, aggregate_date, aggregate_year, aggregate_month,
                today_cost_amount, today_cost_count,
                current_month_cost_amount, current_month_cost_count, current_month_start_date,
                ytd_cost_amount, ytd_cost_count, ytd_start_date,
                previous_day_cost_amount, previous_day_cost_count,
                last_updated
            )
            SELECT
                w.id,
                ? AS aggregate_date,
                ? AS aggregate_year,
                ? AS aggregate_month,
                
                -- Today's Cost
                COALESCE(SUM(CASE WHEN DATE(p.date) = ? THEN p.grand_total ELSE 0 END), 0),
                COALESCE(COUNT(DISTINCT CASE WHEN DATE(p.date) = ? THEN p.id END), 0),
                
                -- Current Month Cost (1st to today)
                COALESCE(SUM(CASE 
                    WHEN DATE(p.date) >= ? AND DATE(p.date) <= ? 
                    THEN p.grand_total ELSE 0 END), 0),
                COALESCE(COUNT(DISTINCT CASE 
                    WHEN DATE(p.date) >= ? AND DATE(p.date) <= ? 
                    THEN p.id END), 0),
                ?,
                
                -- YTD Cost (Jan 1 to today)
                COALESCE(SUM(CASE 
                    WHEN DATE(p.date) >= ? AND DATE(p.date) <= ? 
                    THEN p.grand_total ELSE 0 END), 0),
                COALESCE(COUNT(DISTINCT CASE 
                    WHEN DATE(p.date) >= ? AND DATE(p.date) <= ? 
                    THEN p.id END), 0),
                ?,
                
                -- Previous Day Cost
                COALESCE(SUM(CASE WHEN DATE(p.date) = ? THEN p.grand_total ELSE 0 END), 0),
                COALESCE(COUNT(DISTINCT CASE WHEN DATE(p.date) = ? THEN p.id END), 0),
                
                NOW()
            FROM sma_warehouses w
            LEFT JOIN sma_purchases p ON w.id = p.warehouse_id 
                AND p.status IN ('received', 'received_partial', 'pending')
            WHERE w.id IS NOT NULL
            GROUP BY w.id
            
            ON DUPLICATE KEY UPDATE
                today_cost_amount = VALUES(today_cost_amount),
                today_cost_count = VALUES(today_cost_count),
                current_month_cost_amount = VALUES(current_month_cost_amount),
                current_month_cost_count = VALUES(current_month_cost_count),
                ytd_cost_amount = VALUES(ytd_cost_amount),
                ytd_cost_count = VALUES(ytd_cost_count),
                previous_day_cost_amount = VALUES(previous_day_cost_amount),
                previous_day_cost_count = VALUES(previous_day_cost_count),
                last_updated = NOW()
        ";

        $stmt = $mysqli->prepare($purchases_query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }

        $stmt->bind_param(
            'sisssssssssssssss',
            $aggregate_date, $year, $month,
            $aggregate_date, $aggregate_date,
            $month_start, $aggregate_date,
            $month_start, $aggregate_date,
            $month_start,
            $year_start, $aggregate_date,
            $year_start, $aggregate_date,
            $year_start,
            $previous_date, $previous_date
        );
        $stmt->execute();
        $purchase_rows = $stmt->affected_rows;
        echo "    ✓ Updated $purchase_rows warehouse purchase records\n";

        // ============================================================
        // STEP 3: Populate sma_sales_aggregates_hourly (for real-time dashboard)
        // ============================================================
        echo "\n  [STEP 3] Populating Hourly Sales Aggregates...\n";

        $hourly_query = "
            INSERT INTO sma_sales_aggregates_hourly
            (
                warehouse_id, aggregate_date, aggregate_hour, aggregate_datetime,
                hour_sales_amount, hour_sales_count, today_sales_amount, today_sales_count
            )
            SELECT
                w.id,
                ? AS aggregate_date,
                COALESCE(HOUR(s.date), 0) AS aggregate_hour,
                COALESCE(DATE_FORMAT(s.date, '%Y-%m-%d %H:00:00'), CONCAT(?, ' 00:00:00')) AS aggregate_datetime,
                
                -- Hour Sales
                COALESCE(SUM(s.grand_total), 0) AS hour_sales_amount,
                COALESCE(COUNT(DISTINCT s.id), 0) AS hour_sales_count,
                
                -- Today Running Total (will be calculated in application)
                0 AS today_sales_amount,
                0 AS today_sales_count
                
            FROM sma_warehouses w
            LEFT JOIN sma_sales s ON w.id = s.warehouse_id
                AND DATE(s.date) = ?
                AND s.sale_status IN ('completed', 'completed_partial')
            WHERE w.id IS NOT NULL AND s.id IS NOT NULL
            GROUP BY w.id, DATE(s.date), HOUR(s.date), DATE_FORMAT(s.date, '%Y-%m-%d %H:00:00')
            
            ON DUPLICATE KEY UPDATE
                hour_sales_amount = VALUES(hour_sales_amount),
                hour_sales_count = VALUES(hour_sales_count),
                today_sales_amount = VALUES(today_sales_amount),
                today_sales_count = VALUES(today_sales_count)
        ";

        $stmt = $mysqli->prepare($hourly_query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $mysqli->error);
        }

        $stmt->bind_param('ss', $aggregate_date, $aggregate_date);
        $stmt->execute();
        $hourly_rows = $stmt->affected_rows;
        echo "    ✓ Updated $hourly_rows hourly sales records\n";

        // Commit transaction
        $mysqli->commit();

        // Update log status
        $update_log = $mysqli->prepare("
            UPDATE etl_sales_aggregates_log 
            SET end_time = NOW(),
                status = 'COMPLETED',
                duration_seconds = TIMESTAMPDIFF(SECOND, start_time, NOW()),
                rows_processed = ?,
                rows_updated = ?
            WHERE id = ?
        ");
        $total_rows = $sales_rows + $purchase_rows + $hourly_rows;
        $update_log->bind_param('iii', $total_rows, $total_rows, $log_id);
        $update_log->execute();

        echo "\n  ✓ Sales Aggregates ETL completed successfully for $aggregate_date\n";
        echo "    Total records updated: $total_rows\n";

    } catch (Exception $e) {
        $mysqli->rollback();
        echo "  ✗ ETL failed: " . $e->getMessage() . "\n";

        if ($log_id) {
            $update_log = $mysqli->prepare("
                UPDATE etl_sales_aggregates_log 
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

    echo "\n=== Backfill Summary ===\n";
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
?>
