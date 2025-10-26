-- Cost Center Dashboard Test Queries
-- Use these queries to verify data and test dashboard functionality

-- ============================================================
-- 1. PHARMACY LEVEL OVERVIEW
-- ============================================================
SELECT
    'PHARMACY LEVEL' AS level,
    dp.pharmacy_name,
    dp.pharmacy_code,
    COUNT(DISTINCT db.branch_id) AS branch_count,
    SUM(fc.total_revenue) AS total_revenue,
    SUM(fc.total_cogs) AS total_cogs,
    (
        SUM(fc.total_revenue) - SUM(fc.total_cogs)
    ) AS profit,
    CASE
        WHEN SUM(fc.total_revenue) = 0 THEN 0
        ELSE ROUND(
            (
                SUM(fc.total_revenue) - SUM(fc.total_cogs)
            ) / SUM(fc.total_revenue) * 100,
            2
        )
    END AS profit_margin_pct,
    COUNT(DISTINCT fc.transaction_date) AS active_days,
    MAX(fc.updated_at) AS last_updated
FROM
    sma_fact_cost_center fc
    LEFT JOIN sma_dim_pharmacy dp ON fc.pharmacy_id = dp.pharmacy_id
    LEFT JOIN sma_dim_branch db ON dp.pharmacy_id = db.pharmacy_id
WHERE
    fc.pharmacy_id IS NOT NULL
GROUP BY
    dp.pharmacy_id,
    dp.pharmacy_name,
    dp.pharmacy_code
ORDER BY total_revenue DESC;

-- ============================================================
-- 2. BRANCH LEVEL OVERVIEW
-- ============================================================
SELECT
    'BRANCH LEVEL' AS level,
    db.branch_name,
    db.branch_code,
    dp.pharmacy_name,
    fc.transaction_date,
    fc.total_revenue,
    fc.total_cogs,
    (
        fc.total_revenue - fc.total_cogs
    ) AS profit,
    CASE
        WHEN fc.total_revenue = 0 THEN 0
        ELSE ROUND(
            (
                fc.total_revenue - fc.total_cogs
            ) / fc.total_revenue * 100,
            2
        )
    END AS profit_margin_pct
FROM
    sma_fact_cost_center fc
    LEFT JOIN sma_dim_branch db ON fc.branch_id = db.branch_id
    LEFT JOIN sma_dim_pharmacy dp ON fc.pharmacy_id = dp.pharmacy_id
WHERE
    fc.branch_id IS NOT NULL
ORDER BY fc.transaction_date DESC, fc.total_revenue DESC;

-- ============================================================
-- 3. COMPANY LEVEL SUMMARY
-- ============================================================
SELECT
    'COMPANY TOTAL' AS level,
    COUNT(
        DISTINCT CONCAT(
            YEAR (transaction_date),
            '-',
            MONTH (transaction_date)
        )
    ) AS months_active,
    SUM(total_revenue) AS total_revenue,
    SUM(total_cogs) AS total_cogs,
    SUM(inventory_movement_cost) AS inventory_movement,
    SUM(operational_cost) AS operational_cost,
    (
        SUM(total_revenue) - SUM(total_cogs) - SUM(inventory_movement_cost) - SUM(operational_cost)
    ) AS total_profit,
    CASE
        WHEN SUM(total_revenue) = 0 THEN 0
        ELSE ROUND(
            (
                SUM(total_revenue) - SUM(total_cogs) - SUM(inventory_movement_cost) - SUM(operational_cost)
            ) / SUM(total_revenue) * 100,
            2
        )
    END AS overall_profit_margin_pct,
    COUNT(DISTINCT warehouse_id) AS total_warehouses,
    COUNT(DISTINCT transaction_date) AS days_with_data
FROM sma_fact_cost_center;

-- ============================================================
-- 4. REVENUE TREND (LAST 30 DAYS)
-- ============================================================
SELECT
    transaction_date,
    SUM(total_revenue) AS daily_revenue,
    SUM(total_cogs) AS daily_cost,
    (
        SUM(total_revenue) - SUM(total_cogs)
    ) AS daily_profit,
    COUNT(DISTINCT warehouse_id) AS active_warehouses
FROM sma_fact_cost_center
WHERE
    transaction_date >= DATE_SUB (CURDATE (), INTERVAL 30 DAY)
GROUP BY
    transaction_date
ORDER BY transaction_date DESC;

-- ============================================================
-- 5. TOP 10 REVENUE GENERATING WAREHOUSES (MONTH-TO-DATE)
-- ============================================================
SELECT
    warehouse_id,
    warehouse_name,
    warehouse_type,
    SUM(total_revenue) AS total_revenue,
    COUNT(DISTINCT transaction_date) AS days_active,
    ROUND(
        SUM(total_revenue) / COUNT(DISTINCT transaction_date),
        2
    ) AS avg_daily_revenue,
    MAX(transaction_date) AS last_transaction_date
FROM sma_fact_cost_center
WHERE
    YEAR (transaction_date) = YEAR (CURDATE ())
    AND MONTH (transaction_date) = MONTH (CURDATE ())
GROUP BY
    warehouse_id,
    warehouse_name,
    warehouse_type
ORDER BY total_revenue DESC
LIMIT 10;

-- ============================================================
-- 6. DIMENSION TABLE VERIFICATION
-- ============================================================
SELECT 'Dimension Tables' AS category, COUNT(*) AS record_count
FROM (
        SELECT 1
        FROM sma_dim_pharmacy
        UNION ALL
        SELECT 1
        FROM sma_dim_branch
        UNION ALL
        SELECT 1
        FROM sma_dim_date
    ) AS t;

SELECT 'Total Pharmacies' AS entity, COUNT(*) AS count
FROM sma_dim_pharmacy
UNION ALL
SELECT 'Total Branches', COUNT(*)
FROM sma_dim_branch
UNION ALL
SELECT 'Total Fact Records', COUNT(*)
FROM sma_fact_cost_center;

-- ============================================================
-- 7. DATA QUALITY CHECK
-- ============================================================
SELECT 'Total Revenue' AS metric, CONCAT(
        'SAR ', FORMAT(SUM(total_revenue), 2)
    ) AS value
FROM sma_fact_cost_center
UNION ALL
SELECT 'Total COGS', CONCAT(
        'SAR ', FORMAT(SUM(total_cogs), 2)
    )
FROM sma_fact_cost_center
UNION ALL
SELECT 'Inventory Movement', CONCAT(
        'SAR ', FORMAT(
            SUM(inventory_movement_cost), 2
        )
    )
FROM sma_fact_cost_center
UNION ALL
SELECT 'Operational Cost', CONCAT(
        'SAR ', FORMAT(SUM(operational_cost), 2)
    )
FROM sma_fact_cost_center
UNION ALL
SELECT 'Fact Records', COUNT(*)
FROM sma_fact_cost_center
UNION ALL
SELECT 'Date Range', CONCAT(
        MIN(transaction_date), ' to ', MAX(transaction_date)
    )
FROM sma_fact_cost_center;

-- ============================================================
-- 8. PHARMACY DRILL-DOWN EXAMPLE (Pharmacy #1)
-- ============================================================
-- Replace 1 with pharmacy_id to drill into specific pharmacy
SELECT
    dp.pharmacy_name,
    db.branch_name,
    SUM(fc.total_revenue) AS revenue,
    SUM(fc.total_cogs) AS costs,
    COUNT(fc.transaction_date) AS transaction_days
FROM
    sma_fact_cost_center fc
    LEFT JOIN sma_dim_pharmacy dp ON fc.pharmacy_id = dp.pharmacy_id
    LEFT JOIN sma_dim_branch db ON fc.branch_id = db.branch_id
    AND fc.pharmacy_id = db.pharmacy_id
WHERE
    fc.pharmacy_id = (
        SELECT pharmacy_id
        FROM sma_dim_pharmacy
        LIMIT 1
    )
GROUP BY
    dp.pharmacy_name,
    db.branch_name
ORDER BY revenue DESC;

-- ============================================================
-- 9. ETL AUDIT LOG (if populated)
-- ============================================================
SELECT
    process_name,
    start_time,
    end_time,
    status,
    rows_processed,
    rows_inserted,
    duration_seconds
FROM sma_etl_audit_log
ORDER BY start_time DESC
LIMIT 10;