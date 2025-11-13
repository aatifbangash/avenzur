-- ============================================================
-- Cost Center Migration: Load Sample Data
-- ============================================================
-- File: 004_load_sample_data.sql
-- Purpose: Populate Cost Center tables with sample data
-- Date: 2025-10-25
--
-- Loads:
-- - Dimension data from existing sma_warehouses
-- - Fact data from sma_sales and sma_purchases
-- - Date dimension data
-- ============================================================

-- ============================================================
-- 1. VERIFY AND POPULATE DIMENSION TABLES
-- ============================================================

-- Verify dim_pharmacy population (should have data from 001_create_dimensions.sql)
-- If empty, run this to populate from sma_warehouses:
INSERT IGNORE INTO sma_dim_pharmacy (
    warehouse_id,
    pharmacy_name,
    pharmacy_code,
    address,
    phone,
    email,
    country_id,
    is_active
)
SELECT w.id, w.name, w.code, w.address, w.phone, w.email, w.country, 1
FROM sma_warehouses w
WHERE
    w.parent_id IS NULL ON DUPLICATE KEY
UPDATE pharmacy_name =
VALUES (pharmacy_name),
    address =
VALUES (address),
    phone =
VALUES (phone),
    email =
VALUES (email),
    country_id =
VALUES (country_id);

-- Verify dim_branch population (should have data from 001_create_dimensions.sql)
-- If empty, run this to populate from sma_warehouses:
INSERT IGNORE INTO sma_dim_branch (
    warehouse_id,
    pharmacy_id,
    pharmacy_warehouse_id,
    branch_name,
    branch_code,
    address,
    phone,
    email,
    country_id,
    is_active
)
SELECT b.id, dp.pharmacy_id, b.parent_id, b.name, b.code, b.address, b.phone, b.email, b.country, 1
FROM
    sma_warehouses b
    LEFT JOIN sma_dim_pharmacy dp ON b.parent_id = dp.warehouse_id
WHERE
    b.parent_id IS NOT NULL ON DUPLICATE KEY
UPDATE branch_name =
VALUES (branch_name),
    address =
VALUES (address),
    phone =
VALUES (phone),
    email =
VALUES (email),
    country_id =
VALUES (country_id);

-- ============================================================
-- 2. POPULATE FACT TABLE WITH SALES REVENUE DATA
-- ============================================================
-- Load last 90 days of sales transactions

INSERT INTO
    sma_fact_cost_center (
        warehouse_id,
        warehouse_name,
        warehouse_type,
        pharmacy_id,
        pharmacy_name,
        branch_id,
        branch_name,
        parent_warehouse_id,
        transaction_date,
        period_year,
        period_month,
        total_revenue
    )
SELECT
    w.id,
    w.name,
    COALESCE(w.warehouse_type, 'pharmacy'),
    NULL,
    NULL,
    CASE
        WHEN w.parent_id IS NOT NULL THEN w.id
        ELSE NULL
    END,
    CASE
        WHEN w.parent_id IS NOT NULL THEN w.name
        ELSE NULL
    END,
    w.parent_id,
    DATE (s.date) AS transaction_date,
    YEAR (s.date),
    MONTH (s.date),
    COALESCE(SUM(s.grand_total), 0)
FROM sma_sales s
    LEFT JOIN sma_warehouses w ON s.warehouse_id = w.id
WHERE
    s.date >= DATE_SUB (CURDATE (), INTERVAL 90 DAY)
    AND s.sale_status = 'completed'
GROUP BY
    w.id,
    w.name,
    w.warehouse_type,
    w.parent_id,
    DATE (s.date),
    YEAR (s.date),
    MONTH (s.date) ON DUPLICATE KEY
UPDATE total_revenue = GREATEST(
    total_revenue,
    VALUES (total_revenue)
);

-- ============================================================
-- 3. POPULATE FACT TABLE WITH PURCHASE COST DATA (COGS)
-- ============================================================
-- Load last 90 days of purchase transactions

INSERT INTO
    sma_fact_cost_center (
        warehouse_id,
        warehouse_name,
        warehouse_type,
        pharmacy_id,
        pharmacy_name,
        branch_id,
        branch_name,
        parent_warehouse_id,
        transaction_date,
        period_year,
        period_month,
        total_cogs
    )
SELECT
    w.id,
    w.name,
    COALESCE(w.warehouse_type, 'pharmacy'),
    NULL,
    NULL,
    CASE
        WHEN w.parent_id IS NOT NULL THEN w.id
        ELSE NULL
    END,
    CASE
        WHEN w.parent_id IS NOT NULL THEN w.name
        ELSE NULL
    END,
    w.parent_id,
    DATE (p.purchase_date) AS transaction_date,
    YEAR (p.purchase_date),
    MONTH (p.purchase_date),
    COALESCE(SUM(p.total_cost), 0)
FROM
    sma_purchases p
    LEFT JOIN sma_warehouses w ON p.warehouse_id = w.id
WHERE
    p.purchase_date >= DATE_SUB (CURDATE (), INTERVAL 90 DAY)
    AND p.purchase_status = 'completed'
GROUP BY
    w.id,
    w.name,
    w.warehouse_type,
    w.parent_id,
    DATE (p.purchase_date),
    YEAR (p.purchase_date),
    MONTH (p.purchase_date) ON DUPLICATE KEY
UPDATE total_cogs = GREATEST(
    total_cogs,
    VALUES (total_cogs)
) ON DUPLICATE KEY
UPDATE total_cogs = GREATEST(
    total_cogs,
    VALUES (total_cogs)
);

-- ============================================================
-- 4. LOG DATA LOAD COMPLETION
-- ============================================================

INSERT INTO
    sma_etl_audit_log (
        process_name,
        start_time,
        end_time,
        status,
        rows_processed,
        duration_seconds
    )
VALUES (
        'cost_center_sample_data_load',
        NOW(),
        NOW(),
        'COMPLETED',
        (
            SELECT COUNT(*)
            FROM sma_fact_cost_center
        ),
        0
    );

-- ============================================================
-- 5. VERIFICATION QUERIES
-- ============================================================

-- Display data load summary
SELECT 'Cost Center Data Load Summary' AS summary;

SELECT 'Pharmacies' AS entity, COUNT(*) AS record_count
FROM sma_dim_pharmacy
UNION ALL
SELECT 'Branches', COUNT(*)
FROM sma_dim_branch
UNION ALL
SELECT 'Fact Records', COUNT(*)
FROM sma_fact_cost_center
UNION ALL
SELECT 'Fact Records with Revenue', COUNT(*)
FROM sma_fact_cost_center
WHERE
    total_revenue > 0;

SELECT '' AS blank;

SELECT 'Total Revenue Loaded' AS metric, CONCAT(
        'SAR ', FORMAT(
            COALESCE(SUM(total_revenue), 0), 2
        )
    ) AS value
FROM sma_fact_cost_center;

SELECT 'Total COGS Loaded' AS metric, CONCAT(
        'SAR ', FORMAT(
            COALESCE(SUM(total_cogs), 0), 2
        )
    ) AS value
FROM sma_fact_cost_center;

-- ============================================================
-- Completion Message
-- ============================================================
-- Migration 004 completed successfully.
-- Data loaded:
-- - Dimension data from sma_warehouses
-- - Last 90 days of sales revenue
-- - Last 90 days of purchase costs
-- ============================================================