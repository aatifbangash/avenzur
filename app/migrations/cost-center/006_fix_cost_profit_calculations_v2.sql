-- ============================================================
-- Cost Center Migration: Fix Cost Calculation (CORRECTED VERSION)
-- ============================================================
-- File: 006_fix_cost_profit_calculations_v2.sql
-- Purpose: Fix cost showing as ZERO issue
-- Date: 2025-10-25
--
-- ISSUES FIXED FROM V1:
-- 1. Simplified view logic to avoid NULL joins
-- 2. Direct aggregation from sma_purchases without complex joins
-- 3. Better warehouse_id matching
-- 4. Removed unnecessary COALESCE that was causing issues
-- ============================================================

-- ============================================================
-- STEP 1: Create simple sales monthly aggregation
-- ============================================================
DROP VIEW IF EXISTS `view_sales_monthly`;

CREATE VIEW `view_sales_monthly` AS
SELECT
    ss.warehouse_id,
    YEAR(ss.date) AS sales_year,
    MONTH(ss.date) AS sales_month,
    CONCAT(
        YEAR(ss.date),
        '-',
        LPAD(MONTH(ss.date), 2, '0')
    ) AS period,
    SUM(ss.grand_total) AS total_sales_amount,
    COUNT(DISTINCT ss.id) AS sales_count
FROM sma_sales ss
WHERE
    ss.warehouse_id IS NOT NULL
    AND ss.sale_status IN (
        'completed',
        'completed_partial'
    )
GROUP BY
    ss.warehouse_id,
    YEAR(ss.date),
    MONTH(ss.date);

-- ============================================================
-- STEP 2: Create simple purchases monthly aggregation
-- ============================================================
DROP VIEW IF EXISTS `view_purchases_monthly`;

CREATE VIEW `view_purchases_monthly` AS
SELECT
    sp.warehouse_id,
    YEAR(sp.date) AS purchase_year,
    MONTH(sp.date) AS purchase_month,
    CONCAT(
        YEAR(sp.date),
        '-',
        LPAD(MONTH(sp.date), 2, '0')
    ) AS period,
    SUM(sp.grand_total) AS total_purchase_cost,
    COUNT(DISTINCT sp.id) AS purchase_count
FROM sma_purchases sp
WHERE
    sp.warehouse_id IS NOT NULL
    AND sp.status IN (
        'received',
        'received_partial',
        'pending'
    )
GROUP BY
    sp.warehouse_id,
    YEAR(sp.date),
    MONTH(sp.date);

-- ============================================================
-- STEP 3: DROP existing views
-- ============================================================
DROP VIEW IF EXISTS `view_cost_center_pharmacy`;

DROP VIEW IF EXISTS `view_cost_center_branch`;

DROP VIEW IF EXISTS `view_cost_center_summary`;

-- ============================================================
-- STEP 4: CREATE NEW view_cost_center_pharmacy
-- SIMPLER VERSION - Direct joins without COALESCE confusion
-- ============================================================
CREATE VIEW `view_cost_center_pharmacy` AS
SELECT
    'pharmacy' AS hierarchy_level,
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code,
    vsm.period,
    COALESCE(vsm.total_sales_amount, 0) AS kpi_total_revenue,
    COALESCE(vpm.total_purchase_cost, 0) AS kpi_total_cost,
    COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0) AS kpi_profit_loss,
    CASE
        WHEN COALESCE(vsm.total_sales_amount, 0) = 0 THEN 0
        ELSE ROUND(
            (
                (
                    COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0)
                ) / COALESCE(vsm.total_sales_amount, 0)
            ) * 100,
            2
        )
    END AS kpi_profit_margin_pct,
    COALESCE(vsm.sales_count, 0) AS sales_count,
    COALESCE(vpm.purchase_count, 0) AS purchase_count
FROM
    sma_dim_pharmacy dp
    INNER JOIN view_sales_monthly vsm ON dp.warehouse_id = vsm.warehouse_id
    LEFT JOIN view_purchases_monthly vpm ON dp.warehouse_id = vpm.warehouse_id
    AND vsm.period = vpm.period
WHERE
    dp.is_active = 1;

-- ============================================================
-- STEP 5: CREATE NEW view_cost_center_branch
-- ============================================================
CREATE VIEW `view_cost_center_branch` AS
SELECT
    'branch' AS hierarchy_level,
    db.branch_id,
    db.warehouse_id,
    db.pharmacy_id,
    db.branch_name,
    db.branch_code,
    dp.pharmacy_name,
    vsm.period,
    COALESCE(vsm.total_sales_amount, 0) AS kpi_total_revenue,
    COALESCE(vpm.total_purchase_cost, 0) AS kpi_total_cost,
    COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0) AS kpi_profit_loss,
    CASE
        WHEN COALESCE(vsm.total_sales_amount, 0) = 0 THEN 0
        ELSE ROUND(
            (
                (
                    COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0)
                ) / COALESCE(vsm.total_sales_amount, 0)
            ) * 100,
            2
        )
    END AS kpi_profit_margin_pct,
    COALESCE(vsm.sales_count, 0) AS sales_count,
    COALESCE(vpm.purchase_count, 0) AS purchase_count
FROM
    sma_dim_branch db
    LEFT JOIN sma_dim_pharmacy dp ON db.pharmacy_id = dp.pharmacy_id
    INNER JOIN view_sales_monthly vsm ON db.warehouse_id = vsm.warehouse_id
    LEFT JOIN view_purchases_monthly vpm ON db.warehouse_id = vpm.warehouse_id
    AND vsm.period = vpm.period
WHERE
    db.is_active = 1;

-- ============================================================
-- STEP 6: CREATE NEW view_cost_center_summary
-- Company and pharmacy summary
-- ============================================================
CREATE VIEW `view_cost_center_summary` AS
-- Company level
SELECT
    'company' AS level,
    'RETAJ AL-DAWA' AS entity_name,
    vsm.period,
    SUM(
        COALESCE(vsm.total_sales_amount, 0)
    ) AS kpi_total_revenue,
    SUM(
        COALESCE(vpm.total_purchase_cost, 0)
    ) AS kpi_total_cost,
    SUM(
        COALESCE(vsm.total_sales_amount, 0)
    ) - SUM(
        COALESCE(vpm.total_purchase_cost, 0)
    ) AS kpi_profit_loss,
    CASE
        WHEN SUM(
            COALESCE(vsm.total_sales_amount, 0)
        ) = 0 THEN 0
        ELSE ROUND(
            (
                (
                    SUM(
                        COALESCE(vsm.total_sales_amount, 0)
                    ) - SUM(
                        COALESCE(vpm.total_purchase_cost, 0)
                    )
                ) / SUM(
                    COALESCE(vsm.total_sales_amount, 0)
                )
            ) * 100,
            2
        )
    END AS kpi_profit_margin_pct,
    COUNT(DISTINCT dp.pharmacy_id) AS entity_count
FROM
    sma_dim_pharmacy dp
    INNER JOIN view_sales_monthly vsm ON dp.warehouse_id = vsm.warehouse_id
    LEFT JOIN view_purchases_monthly vpm ON dp.warehouse_id = vpm.warehouse_id
    AND vsm.period = vpm.period
WHERE
    dp.is_active = 1
GROUP BY
    vsm.period
UNION ALL

-- Pharmacy level
SELECT
    'pharmacy' AS level,
    dp.pharmacy_name AS entity_name,
    vsm.period,
    COALESCE(vsm.total_sales_amount, 0) AS kpi_total_revenue,
    COALESCE(vpm.total_purchase_cost, 0) AS kpi_total_cost,
    COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0) AS kpi_profit_loss,
    CASE
        WHEN COALESCE(vsm.total_sales_amount, 0) = 0 THEN 0
        ELSE ROUND(
            (
                (
                    COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0)
                ) / COALESCE(vsm.total_sales_amount, 0)
            ) * 100,
            2
        )
    END AS kpi_profit_margin_pct,
    COUNT(
        DISTINCT CASE
            WHEN db.branch_id IS NOT NULL THEN db.branch_id
        END
    ) AS entity_count
FROM
    sma_dim_pharmacy dp
    INNER JOIN view_sales_monthly vsm ON dp.warehouse_id = vsm.warehouse_id
    LEFT JOIN sma_dim_branch db ON dp.pharmacy_id = db.pharmacy_id
    AND db.is_active = 1
    LEFT JOIN view_purchases_monthly vpm ON dp.warehouse_id = vpm.warehouse_id
    AND vsm.period = vpm.period
WHERE
    dp.is_active = 1
GROUP BY
    dp.pharmacy_id,
    dp.pharmacy_name,
    vsm.period
ORDER BY period DESC, kpi_total_revenue DESC;

-- ============================================================
-- VERIFICATION QUERIES (Run these to check):
-- ============================================================

-- Check 1: View sales monthly has data
-- SELECT * FROM view_sales_monthly WHERE period = '2025-10';

-- Check 2: View purchases monthly has data
-- SELECT * FROM view_purchases_monthly WHERE period = '2025-10';

-- Check 3: View cost center pharmacy has data
-- SELECT * FROM view_cost_center_pharmacy WHERE period = '2025-10';

-- Check 4: Verify sma_purchases has October data
-- SELECT COUNT(*), SUM(grand_total) FROM sma_purchases
-- WHERE YEAR(date) = 2025 AND MONTH(date) = 10;

-- ============================================================
-- Migration v2 completed
-- ============================================================