#!/usr/bin/env php
<?php
/**
 * Accounts Dashboard - Data Quality Diagnostic
 * Quick verification of purchase vs sales data
 * 
 * Run: php diagnostic_accounts_data.php
 */

echo "\n" . str_repeat("=", 70) . "\n";
echo "ACCOUNTS DASHBOARD - DATA QUALITY DIAGNOSTIC\n";
echo str_repeat("=", 70) . "\n\n";

// Change to project root
$project_root = dirname(dirname(__FILE__));
chdir($project_root);

// Bootstrap CodeIgniter
require_once 'index.php';
$CI = &get_instance();

$CI->load->model('admin/Accounts_dashboard_model', 'adash_model');
$CI->load->database();

echo "✓ CodeIgniter loaded\n";
echo "✓ Database: " . $CI->db->database . "\n\n";

// Get date range
$current_date = date('Y-m-d');
$ytd_start = date('Y-01-01');
$current_month_start = date('Y-m-01');
$previous_month_start = date('Y-m-01', strtotime('-1 month'));
$previous_month_end = date('Y-m-t', strtotime('-1 month'));

echo "📅 DATE RANGES:\n";
echo "  YTD: $ytd_start → $current_date\n";
echo "  Current Month: $current_month_start → $current_date\n";
echo "  Previous Month: $previous_month_start → $previous_month_end\n\n";

// ========== PURCHASES ANALYSIS ==========
echo str_repeat("-", 70) . "\n";
echo "PURCHASES ANALYSIS\n";
echo str_repeat("-", 70) . "\n\n";

$queries = [
    'YTD Purchases' => "
        SELECT 
            COUNT(*) as count,
            SUM(grand_total) as total,
            MIN(DATE(date)) as first_date,
            MAX(DATE(date)) as last_date,
            COUNT(DISTINCT supplier_id) as suppliers,
            COUNT(DISTINCT warehouse_id) as warehouses
        FROM sma_purchases
        WHERE DATE(date) >= '$ytd_start' 
          AND DATE(date) <= '$current_date'
          AND status IN ('received', 'received_partial')
    ",
    
    'Current Month Purchases' => "
        SELECT 
            COUNT(*) as count,
            SUM(grand_total) as total,
            MIN(DATE(date)) as first_date,
            MAX(DATE(date)) as last_date
        FROM sma_purchases
        WHERE DATE(date) >= '$current_month_start' 
          AND DATE(date) <= '$current_date'
          AND status IN ('received', 'received_partial')
    ",
    
    'Previous Month Purchases' => "
        SELECT 
            COUNT(*) as count,
            SUM(grand_total) as total
        FROM sma_purchases
        WHERE DATE(date) >= '$previous_month_start' 
          AND DATE(date) <= '$previous_month_end'
          AND status IN ('received', 'received_partial')
    ",
    
    'Purchases by Year' => "
        SELECT 
            YEAR(date) as year,
            COUNT(*) as count,
            SUM(grand_total) as total
        FROM sma_purchases
        WHERE status IN ('received', 'received_partial')
        GROUP BY YEAR(date)
        ORDER BY YEAR(date) DESC
    "
];

foreach ($queries as $label => $query) {
    echo "📊 $label:\n";
    $result = $CI->db->query($query);
    
    if ($result->num_rows() == 0) {
        echo "   ❌ No data\n\n";
        continue;
    }
    
    foreach ($result->result_array() as $row) {
        foreach ($row as $key => $value) {
            if ($key == 'total') {
                echo "   $key: " . number_format($value, 2) . " SAR\n";
            } elseif ($key == 'count') {
                echo "   $key: " . number_format($value) . "\n";
            } else {
                echo "   $key: $value\n";
            }
        }
    }
    echo "\n";
}

// ========== SALES ANALYSIS ==========
echo str_repeat("-", 70) . "\n";
echo "SALES ANALYSIS\n";
echo str_repeat("-", 70) . "\n\n";

$sales_queries = [
    'YTD Sales' => "
        SELECT 
            COUNT(*) as count,
            SUM(grand_total) as total,
            MIN(DATE(date)) as first_date,
            MAX(DATE(date)) as last_date,
            COUNT(DISTINCT customer_id) as customers,
            COUNT(DISTINCT warehouse_id) as warehouses
        FROM sma_sales
        WHERE DATE(date) >= '$ytd_start' 
          AND DATE(date) <= '$current_date'
          AND sale_status IN ('completed', 'completed_partial')
    ",
    
    'Current Month Sales' => "
        SELECT 
            COUNT(*) as count,
            SUM(grand_total) as total,
            MIN(DATE(date)) as first_date,
            MAX(DATE(date)) as last_date
        FROM sma_sales
        WHERE DATE(date) >= '$current_month_start' 
          AND DATE(date) <= '$current_date'
          AND sale_status IN ('completed', 'completed_partial')
    ",
    
    'Previous Month Sales' => "
        SELECT 
            COUNT(*) as count,
            SUM(grand_total) as total
        FROM sma_sales
        WHERE DATE(date) >= '$previous_month_start' 
          AND DATE(date) <= '$previous_month_end'
          AND sale_status IN ('completed', 'completed_partial')
    ",
    
    'Sales by Year' => "
        SELECT 
            YEAR(date) as year,
            COUNT(*) as count,
            SUM(grand_total) as total
        FROM sma_sales
        WHERE sale_status IN ('completed', 'completed_partial')
        GROUP BY YEAR(date)
        ORDER BY YEAR(date) DESC
    "
];

foreach ($sales_queries as $label => $query) {
    echo "📊 $label:\n";
    $result = $CI->db->query($query);
    
    if ($result->num_rows() == 0) {
        echo "   ❌ No data\n\n";
        continue;
    }
    
    foreach ($result->result_array() as $row) {
        foreach ($row as $key => $value) {
            if ($key == 'total') {
                echo "   $key: " . number_format($value, 2) . " SAR\n";
            } elseif ($key == 'count') {
                echo "   $key: " . number_format($value) . "\n";
            } else {
                echo "   $key: $value\n";
            }
        }
    }
    echo "\n";
}

// ========== PROFIT ANALYSIS ==========
echo str_repeat("-", 70) . "\n";
echo "PROFIT ANALYSIS\n";
echo str_repeat("-", 70) . "\n\n";

$profit_query = "
    SELECT 
        'YTD' as period,
        (SELECT COALESCE(SUM(grand_total), 0) FROM sma_sales 
         WHERE DATE(date) >= '$ytd_start' AND DATE(date) <= '$current_date'
         AND sale_status IN ('completed', 'completed_partial')) as revenue,
        (SELECT COALESCE(SUM(grand_total), 0) FROM sma_purchases 
         WHERE DATE(date) >= '$ytd_start' AND DATE(date) <= '$current_date'
         AND status IN ('received', 'received_partial')) as cost,
        (SELECT COALESCE(SUM(grand_total), 0) FROM sma_sales 
         WHERE DATE(date) >= '$ytd_start' AND DATE(date) <= '$current_date'
         AND sale_status IN ('completed', 'completed_partial')) - 
        (SELECT COALESCE(SUM(grand_total), 0) FROM sma_purchases 
         WHERE DATE(date) >= '$ytd_start' AND DATE(date) <= '$current_date'
         AND status IN ('received', 'received_partial')) as profit
    UNION ALL
    SELECT 
        'Current Month' as period,
        (SELECT COALESCE(SUM(grand_total), 0) FROM sma_sales 
         WHERE DATE(date) >= '$current_month_start' AND DATE(date) <= '$current_date'
         AND sale_status IN ('completed', 'completed_partial')) as revenue,
        (SELECT COALESCE(SUM(grand_total), 0) FROM sma_purchases 
         WHERE DATE(date) >= '$current_month_start' AND DATE(date) <= '$current_date'
         AND status IN ('received', 'received_partial')) as cost,
        (SELECT COALESCE(SUM(grand_total), 0) FROM sma_sales 
         WHERE DATE(date) >= '$current_month_start' AND DATE(date) <= '$current_date'
         AND sale_status IN ('completed', 'completed_partial')) - 
        (SELECT COALESCE(SUM(grand_total), 0) FROM sma_purchases 
         WHERE DATE(date) >= '$current_month_start' AND DATE(date) <= '$current_date'
         AND status IN ('received', 'received_partial')) as profit
";

$result = $CI->db->query($profit_query);

foreach ($result->result_array() as $row) {
    $revenue = $row['revenue'];
    $cost = $row['cost'];
    $profit = $row['profit'];
    $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
    
    echo "📈 {$row['period']}:\n";
    echo "   Revenue: " . number_format($revenue, 2) . " SAR\n";
    echo "   Cost:    " . number_format($cost, 2) . " SAR\n";
    echo "   Profit:  " . number_format($profit, 2) . " SAR\n";
    echo "   Margin:  " . number_format($margin, 2) . "%\n";
    
    if ($profit < 0) {
        echo "   ⚠️  NEGATIVE PROFIT\n";
    } elseif ($profit == 0) {
        echo "   ⚠️  BREAK-EVEN\n";
    } else {
        echo "   ✅ Positive\n";
    }
    echo "\n";
}

// ========== INTERPRETATION ==========
echo str_repeat("-", 70) . "\n";
echo "INTERPRETATION\n";
echo str_repeat("-", 70) . "\n\n";

$ytd_sales = $CI->db->query("
    SELECT COALESCE(SUM(grand_total), 0) as total 
    FROM sma_sales 
    WHERE DATE(date) >= '$ytd_start' AND DATE(date) <= '$current_date'
    AND sale_status IN ('completed', 'completed_partial')
")->row()->total;

$ytd_purchases = $CI->db->query("
    SELECT COALESCE(SUM(grand_total), 0) as total 
    FROM sma_purchases 
    WHERE DATE(date) >= '$ytd_start' AND DATE(date) <= '$current_date'
    AND status IN ('received', 'received_partial')
")->row()->total;

if ($ytd_sales < 100) {
    echo "🔴 CRITICAL: Very low YTD sales (" . number_format($ytd_sales, 2) . " SAR)\n";
    echo "   → Possible issues:\n";
    echo "     1. Sales data hasn't been entered yet\n";
    echo "     2. Wrong date range selected\n";
    echo "     3. System just started collecting sales\n\n";
}

if ($ytd_purchases > 1000000) {
    echo "🟡 WARNING: Very high YTD purchases (" . number_format($ytd_purchases, 2) . " SAR)\n";
    echo "   → Possible issues:\n";
    echo "     1. Large inventory bulk purchase (normal)\n";
    echo "     2. Test/dummy data in database\n";
    echo "     3. Date range includes old data\n\n";
}

if ($ytd_sales < 1000 && $ytd_purchases > 1000000) {
    echo "🔴 RED FLAG: Huge cost vs minimal sales\n";
    echo "   Ratio: " . number_format($ytd_purchases / max($ytd_sales, 0.01), 0) . ":1\n";
    echo "   → Recommend:\n";
    echo "     1. Check if purchases are test data\n";
    echo "     2. Verify date ranges on purchases\n";
    echo "     3. Review warehouse inventory status\n\n";
}

echo "✓ Diagnostic complete\n\n";
