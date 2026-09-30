-- ============================================================
-- Cost Center Migration: Create Sales Views (Pharmacy & Branch)
-- ============================================================
-- File: 013_create_sales_views_pharmacy_branch.sql
-- Purpose: Create dedicated views for sales metrics at pharmacy and branch level
--          with time-period breakdowns (today, current month, YTD)
-- Date: 2025-10-26
--
-- Creates:
-- - view_sales_per_pharmacy: Sales aggregated at pharmacy level (warehouse_id parent)
-- - view_sales_per_branch: Sales aggregated at branch level (warehouse_id leaf)
-- - view_purchases_per_pharmacy: Purchase costs at pharmacy level
-- - view_purchases_per_branch: Purchase costs at branch level
-- ============================================================

-- ============================================================
-- DROP existing views (if any)
-- ============================================================
DROP VIEW IF EXISTS `view_sales_per_pharmacy`;

DROP VIEW IF EXISTS `view_sales_per_branch`;

DROP VIEW IF EXISTS `view_purchases_per_pharmacy`;

DROP VIEW IF EXISTS `view_purchases_per_branch`;

-- ============================================================
-- VIEW 1: view_sales_per_pharmacy
-- Purpose: Sales metrics at PHARMACY level with time periods
--
-- Logic:
--   - Groups by warehouse_id (pharmacy warehouse)
--   - Includes all branches under this pharmacy
--   - Shows: today_sales, current_month_sales, ytd_sales
--   - Trends included for comparison
-- ============================================================
CREATE VIEW `view_sales_per_pharmacy` AS
SELECT
    'pharmacy' AS hierarchy_level,
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code,

-- Today's Metrics
COALESCE(SUM(sa.today_sales_amount), 0) AS today_sales_amount,
COALESCE(SUM(sa.today_sales_count), 0) AS today_sales_count,

-- Current Month Metrics
COALESCE(
    SUM(sa.current_month_sales_amount),
    0
) AS current_month_sales_amount,
COALESCE(
    SUM(sa.current_month_sales_count),
    0
) AS current_month_sales_count,

-- Year-to-Date Metrics
COALESCE(SUM(sa.ytd_sales_amount), 0) AS ytd_sales_amount,
COALESCE(SUM(sa.ytd_sales_count), 0) AS ytd_sales_count,

-- Previous Day Metrics (for trend comparison)
COALESCE(
    SUM(sa.previous_day_sales_amount),
    0
) AS previous_day_sales_amount,
COALESCE(
    SUM(sa.previous_day_sales_count),
    0
) AS previous_day_sales_count,

-- Trends (percentage change)
CASE
    WHEN COALESCE(
        SUM(sa.previous_day_sales_amount),
        0
    ) = 0 THEN 0
    ELSE ROUND(
        (
            (
                COALESCE(SUM(sa.today_sales_amount), 0) - COALESCE(
                    SUM(sa.previous_day_sales_amount),
                    0
                )
            ) / COALESCE(
                SUM(sa.previous_day_sales_amount),
                0
            )
        ) * 100,
        2
    )
END AS today_vs_yesterday_pct,

-- Month Average (current month sales / days so far)
CASE
    WHEN DAYOFMONTH (CURRENT_DATE()) = 1 THEN 0
    ELSE ROUND(
        COALESCE(
            SUM(sa.current_month_sales_amount),
            0
        ) / DAYOFMONTH (CURRENT_DATE()),
        2
    )
END AS current_month_daily_average,

-- YTD Average (YTD sales / days so far in year)
CASE
    WHEN DAYOFYEAR (CURRENT_DATE()) = 1 THEN 0
    ELSE ROUND(
        COALESCE(SUM(sa.ytd_sales_amount), 0) / DAYOFYEAR (CURRENT_DATE()),
        2
    )
END AS ytd_daily_average,

-- Branch Count
COALESCE( COUNT(DISTINCT db.branch_id), 0 ) AS branch_count,

-- Last Update
CURRENT_TIMESTAMP() AS last_updated
FROM
    sma_dim_pharmacy dp
    LEFT JOIN sma_sales_aggregates sa ON dp.warehouse_id = sa.warehouse_id
    LEFT JOIN sma_dim_branch db ON dp.pharmacy_id = db.pharmacy_id
    AND db.is_active = 1
WHERE
    dp.is_active = 1
GROUP BY
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code;

-- ============================================================
-- VIEW 2: view_sales_per_branch

-- ============================================================
-- VIEW 2: view_sales_per_branch
-- Purpose: Sales metrics at BRANCH level with time periods
--
-- Logic:
--   - Groups by warehouse_id (branch warehouse)
--   - Links to parent pharmacy
--   - Shows: today_sales, current_month_sales, ytd_sales
--   - Includes pharmacy hierarchy info
-- ============================================================
CREATE VIEW `view_sales_per_branch` AS
SELECT
    'branch' AS hierarchy_level,
    db.branch_id,
    db.warehouse_id,
    db.branch_name,
    db.branch_code,

-- Pharmacy Info
db.pharmacy_id,
dp.pharmacy_name,
dp.pharmacy_code,
dp.warehouse_id AS pharmacy_warehouse_id,

-- Today's Metrics
COALESCE(SUM(sa.today_sales_amount), 0) AS today_sales_amount,
COALESCE(SUM(sa.today_sales_count), 0) AS today_sales_count,

-- Current Month Metrics
COALESCE(
    SUM(sa.current_month_sales_amount),
    0
) AS current_month_sales_amount,
COALESCE(
    SUM(sa.current_month_sales_count),
    0
) AS current_month_sales_count,

-- Year-to-Date Metrics
COALESCE(SUM(sa.ytd_sales_amount), 0) AS ytd_sales_amount,
COALESCE(SUM(sa.ytd_sales_count), 0) AS ytd_sales_count,

-- Previous Day Metrics (for trend comparison)
COALESCE(
    SUM(sa.previous_day_sales_amount),
    0
) AS previous_day_sales_amount,
COALESCE(
    SUM(sa.previous_day_sales_count),
    0
) AS previous_day_sales_count,

-- Trends (percentage change)
CASE
    WHEN COALESCE(
        SUM(sa.previous_day_sales_amount),
        0
    ) = 0 THEN 0
    ELSE ROUND(
        (
            (
                COALESCE(SUM(sa.today_sales_amount), 0) - COALESCE(
                    SUM(sa.previous_day_sales_amount),
                    0
                )
            ) / COALESCE(
                SUM(sa.previous_day_sales_amount),
                0
            )
        ) * 100,
        2
    )
END AS today_vs_yesterday_pct,

-- Month Average (current month sales / days so far)
CASE
    WHEN DAYOFMONTH (CURRENT_DATE()) = 1 THEN 0
    ELSE ROUND(
        COALESCE(
            SUM(sa.current_month_sales_amount),
            0
        ) / DAYOFMONTH (CURRENT_DATE()),
        2
    )
END AS current_month_daily_average,

-- YTD Average (YTD sales / days so far in year)
CASE
    WHEN DAYOFYEAR (CURRENT_DATE()) = 1 THEN 0
    ELSE ROUND(
        COALESCE(SUM(sa.ytd_sales_amount), 0) / DAYOFYEAR (CURRENT_DATE()),
        2
    )
END AS ytd_daily_average,

-- Last Update
CURRENT_TIMESTAMP() AS last_updated
FROM
    sma_dim_branch db
    LEFT JOIN sma_dim_pharmacy dp ON db.pharmacy_id = dp.pharmacy_id
    LEFT JOIN sma_sales_aggregates sa ON db.warehouse_id = sa.warehouse_id
WHERE
    db.is_active = 1
GROUP BY
    db.branch_id,
    db.warehouse_id,
    db.branch_name,
    db.branch_code,
    db.pharmacy_id,
    dp.pharmacy_name,
    dp.pharmacy_code,
    dp.warehouse_id;

-- ============================================================
-- VIEW 3: view_purchases_per_pharmacy
-- Purpose: Purchase costs at PHARMACY level with time periods
-- ============================================================
CREATE VIEW `view_purchases_per_pharmacy` AS
SELECT
    'pharmacy' AS hierarchy_level,
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code,

-- Today's Metrics
COALESCE(SUM(pa.today_cost_amount), 0) AS today_cost_amount,
COALESCE(SUM(pa.today_cost_count), 0) AS today_cost_count,

-- Current Month Metrics
COALESCE(
    SUM(pa.current_month_cost_amount),
    0
) AS current_month_cost_amount,
COALESCE(
    SUM(pa.current_month_cost_count),
    0
) AS current_month_cost_count,

-- Year-to-Date Metrics
COALESCE(SUM(pa.ytd_cost_amount), 0) AS ytd_cost_amount,
COALESCE(SUM(pa.ytd_cost_count), 0) AS ytd_cost_count,

-- Previous Day Metrics (for trend comparison)
COALESCE(
    SUM(pa.previous_day_cost_amount),
    0
) AS previous_day_cost_amount,
COALESCE(
    SUM(pa.previous_day_cost_count),
    0
) AS previous_day_cost_count,

-- Trends (percentage change)
CASE
    WHEN COALESCE(
        SUM(pa.previous_day_cost_amount),
        0
    ) = 0 THEN 0
    ELSE ROUND(
        (
            (
                COALESCE(SUM(pa.today_cost_amount), 0) - COALESCE(
                    SUM(pa.previous_day_cost_amount),
                    0
                )
            ) / COALESCE(
                SUM(pa.previous_day_cost_amount),
                0
            )
        ) * 100,
        2
    )
END AS today_vs_yesterday_pct,

-- Month Average
CASE
    WHEN DAYOFMONTH (CURRENT_DATE()) = 1 THEN 0
    ELSE ROUND(
        COALESCE(
            SUM(pa.current_month_cost_amount),
            0
        ) / DAYOFMONTH (CURRENT_DATE()),
        2
    )
END AS current_month_daily_average,

-- YTD Average
CASE
    WHEN DAYOFYEAR (CURRENT_DATE()) = 1 THEN 0
    ELSE ROUND(
        COALESCE(SUM(pa.ytd_cost_amount), 0) / DAYOFYEAR (CURRENT_DATE()),
        2
    )
END AS ytd_daily_average,

-- Last Update
CURRENT_TIMESTAMP() AS last_updated
FROM
    sma_dim_pharmacy dp
    LEFT JOIN sma_purchases_aggregates pa ON dp.warehouse_id = pa.warehouse_id
WHERE
    dp.is_active = 1
GROUP BY
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code;

-- ============================================================
-- VIEW 4: view_purchases_per_branch
-- Purpose: Purchase costs at BRANCH level with time periods
-- ============================================================
CREATE VIEW `view_purchases_per_branch` AS
SELECT
    'branch' AS hierarchy_level,
    db.branch_id,
    db.warehouse_id,
    db.branch_name,
    db.branch_code,

-- Pharmacy Info
db.pharmacy_id,
dp.pharmacy_name,
dp.pharmacy_code,
dp.warehouse_id AS pharmacy_warehouse_id,

-- Today's Metrics
COALESCE(SUM(pa.today_cost_amount), 0) AS today_cost_amount,
COALESCE(SUM(pa.today_cost_count), 0) AS today_cost_count,

-- Current Month Metrics
COALESCE(
    SUM(pa.current_month_cost_amount),
    0
) AS current_month_cost_amount,
COALESCE(
    SUM(pa.current_month_cost_count),
    0
) AS current_month_cost_count,

-- Year-to-Date Metrics
COALESCE(SUM(pa.ytd_cost_amount), 0) AS ytd_cost_amount,
COALESCE(SUM(pa.ytd_cost_count), 0) AS ytd_cost_count,

-- Previous Day Metrics (for trend comparison)
COALESCE(
    SUM(pa.previous_day_cost_amount),
    0
) AS previous_day_cost_amount,
COALESCE(
    SUM(pa.previous_day_cost_count),
    0
) AS previous_day_cost_count,

-- Trends (percentage change)
CASE
    WHEN COALESCE(
        SUM(pa.previous_day_cost_amount),
        0
    ) = 0 THEN 0
    ELSE ROUND(
        (
            (
                COALESCE(SUM(pa.today_cost_amount), 0) - COALESCE(
                    SUM(pa.previous_day_cost_amount),
                    0
                )
            ) / COALESCE(
                SUM(pa.previous_day_cost_amount),
                0
            )
        ) * 100,
        2
    )
END AS today_vs_yesterday_pct,

-- Month Average
CASE
    WHEN DAYOFMONTH (CURRENT_DATE()) = 1 THEN 0
    ELSE ROUND(
        COALESCE(
            SUM(pa.current_month_cost_amount),
            0
        ) / DAYOFMONTH (CURRENT_DATE()),
        2
    )
END AS current_month_daily_average,

-- YTD Average
CASE
    WHEN DAYOFYEAR (CURRENT_DATE()) = 1 THEN 0
    ELSE ROUND(
        COALESCE(SUM(pa.ytd_cost_amount), 0) / DAYOFYEAR (CURRENT_DATE()),
        2
    )
END AS ytd_daily_average,

-- Last Update
CURRENT_TIMESTAMP() AS last_updated
FROM
    sma_dim_branch db
    LEFT JOIN sma_dim_pharmacy dp ON db.pharmacy_id = dp.pharmacy_id
    LEFT JOIN sma_purchases_aggregates pa ON db.warehouse_id = pa.warehouse_id
WHERE
    db.is_active = 1
GROUP BY
    db.branch_id,
    db.warehouse_id,
    db.branch_name,
    db.branch_code,
    db.pharmacy_id,
    dp.pharmacy_name,
    dp.pharmacy_code,
    dp.warehouse_id;

-- ============================================================
-- Migration 013 completed successfully.
-- Views created:
-- - view_sales_per_pharmacy (pharmacy-level sales metrics)
-- - view_sales_per_branch (branch-level sales metrics)
-- - view_purchases_per_pharmacy (pharmacy-level purchase metrics)
-- - view_purchases_per_branch (branch-level purchase metrics)
--
-- All views include:
--   - today_sales/cost metrics
--   - current_month_sales/cost metrics
--   - ytd_sales/cost metrics
--   - Trend analysis (today vs yesterday)
--   - Daily averages for current month and YTD
-- ============================================================