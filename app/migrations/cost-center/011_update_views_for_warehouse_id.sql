-- ============================================================
-- Cost Center Migration: Update Views for warehouse_id
-- ============================================================
-- File: 011_update_views_for_warehouse_id.sql
-- Purpose: Update views to use warehouse_id (natural key) instead of pharmacy_id
-- Date: 2025-10-26
--
-- CHANGES:
-- 1. Create sma_dim_warehouse for central warehouses (type="warehouse")
-- 2. Update view_cost_center_pharmacy to include central warehouses
-- 3. Update view_cost_center_branch to use pharmacy_warehouse_id
-- 4. Update view_cost_center_summary to include warehouse data
-- ============================================================

-- ============================================================
-- STEP 0: CREATE sma_dim_warehouse table
-- ============================================================
-- Dimension table for central warehouses (type="warehouse" with no parent)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_dim_warehouse` (
    `warehouse_id` INT PRIMARY KEY,
    `warehouse_name` VARCHAR(255) NOT NULL,
    `warehouse_code` VARCHAR(50),
    `warehouse_type` VARCHAR(50) DEFAULT 'warehouse',
    `is_active` TINYINT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`warehouse_id`) REFERENCES `sma_warehouses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate sma_dim_warehouse with central warehouses
INSERT INTO
    sma_dim_warehouse (
        warehouse_id,
        warehouse_name,
        warehouse_code,
        warehouse_type,
        is_active
    )
SELECT w.id, w.name, w.code, COALESCE(w.warehouse_type, 'warehouse'), w.active
FROM sma_warehouses w
WHERE (
        w.warehouse_type = 'warehouse'
        OR w.warehouse_type IS NULL
    )
    AND w.parent_id IS NULL
    AND w.active = 1 ON DUPLICATE KEY
UPDATE warehouse_name =
VALUES (warehouse_name),
    warehouse_code =
VALUES (warehouse_code),
    is_active =
VALUES (is_active);

-- ============================================================
-- DROP existing views
-- ============================================================
DROP VIEW IF EXISTS `view_cost_center_branch`;

DROP VIEW IF EXISTS `view_cost_center_pharmacy`;

DROP VIEW IF EXISTS `view_cost_center_summary`;

-- ============================================================
-- STEP 1: CREATE view_cost_center_pharmacy (Updated)
-- ============================================================
-- Returns pharmacy-level aggregated costs with warehouse_id as natural key
-- UPDATED: Includes central warehouses (type="warehouse") in pharmacy revenue
-- ============================================================
CREATE VIEW `view_cost_center_pharmacy` AS
SELECT
    'pharmacy' AS hierarchy_level,
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
    CASE
        WHEN COALESCE(vsm.total_sales_amount, 0) = 0 THEN 0
        ELSE ROUND(
            (
                COALESCE(vpm.total_purchase_cost, 0) / COALESCE(vsm.total_sales_amount, 0)
            ) * 100,
            2
        )
    END AS kpi_cost_ratio_pct,
    COALESCE(vsm.sales_count, 0) AS sales_transactions,
    COALESCE(vpm.purchase_count, 0) AS purchase_transactions,
    COALESCE(db.branch_count, 0) AS branch_count,
    NOW() AS last_updated
FROM sma_dim_pharmacy dp
LEFT JOIN view_sales_monthly vsm ON dp.warehouse_id = vsm.warehouse_id
LEFT JOIN view_purchases_monthly vpm ON dp.warehouse_id = vpm.warehouse_id
LEFT JOIN (
    SELECT pharmacy_warehouse_id, COUNT(*) as branch_count
    FROM sma_dim_branch
    WHERE is_active = 1
    GROUP BY pharmacy_warehouse_id
) db ON dp.warehouse_id = db.pharmacy_warehouse_id
WHERE dp.is_active = 1
UNION ALL
-- UPDATED: Add central warehouses (type="warehouse") with no parent
-- These roll up to company-level revenue
SELECT
    'warehouse' AS hierarchy_level,
    dw.warehouse_id,
    dw.warehouse_name,
    dw.warehouse_code,
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
    CASE
        WHEN COALESCE(vsm.total_sales_amount, 0) = 0 THEN 0
        ELSE ROUND(
            (
                COALESCE(vpm.total_purchase_cost, 0) / COALESCE(vsm.total_sales_amount, 0)
            ) * 100,
            2
        )
    END AS kpi_cost_ratio_pct,
    COALESCE(vsm.sales_count, 0) AS sales_transactions,
    COALESCE(vpm.purchase_count, 0) AS purchase_transactions,
    0 AS branch_count,
    NOW() AS last_updated
FROM sma_dim_warehouse dw
LEFT JOIN view_sales_monthly vsm ON dw.warehouse_id = vsm.warehouse_id
LEFT JOIN view_purchases_monthly vpm ON dw.warehouse_id = vpm.warehouse_id
WHERE dw.is_active = 1;

-- ============================================================
-- STEP 2: CREATE view_cost_center_branch (Updated)
-- ============================================================
-- Returns branch-level aggregated costs with parent pharmacy reference
-- KEY CHANGE: pharmacy_warehouse_id (references sma_dim_pharmacy.warehouse_id)
-- ============================================================
CREATE VIEW `view_cost_center_branch` AS
SELECT
    'branch' AS hierarchy_level,
    db.warehouse_id,
    db.branch_name,
    db.branch_code,
    dp.warehouse_id AS pharmacy_warehouse_id,
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
    CASE
        WHEN COALESCE(vsm.total_sales_amount, 0) = 0 THEN 0
        ELSE ROUND(
            (
                COALESCE(vpm.total_purchase_cost, 0) / COALESCE(vsm.total_sales_amount, 0)
            ) * 100,
            2
        )
    END AS kpi_cost_ratio_pct,
    COALESCE(vsm.sales_count, 0) AS sales_transactions,
    COALESCE(vpm.purchase_count, 0) AS purchase_transactions,
    NOW() AS last_updated
FROM sma_dim_branch db
LEFT JOIN sma_dim_pharmacy dp ON db.pharmacy_warehouse_id = dp.warehouse_id
LEFT JOIN view_sales_monthly vsm ON db.warehouse_id = vsm.warehouse_id
LEFT JOIN view_purchases_monthly vpm ON db.warehouse_id = vpm.warehouse_id
WHERE db.is_active = 1;

-- ============================================================
-- STEP 3: CREATE view_cost_center_summary (Updated)
-- ============================================================
-- Company-level summary aggregating all pharmacies and branches
-- ============================================================
CREATE VIEW `view_cost_center_summary` AS
SELECT
    'COMPANY' AS level,
    'All Pharmacies' AS entity_name,
    vsm.period,
    COALESCE(SUM(vsm.total_sales_amount), 0) AS kpi_total_revenue,
    COALESCE(SUM(vpm.total_purchase_cost), 0) AS kpi_total_cost,
    COALESCE(SUM(vsm.total_sales_amount), 0) - COALESCE(SUM(vpm.total_purchase_cost), 0) AS kpi_profit_loss,
    CASE
        WHEN COALESCE(SUM(vsm.total_sales_amount), 0) = 0 THEN 0
        ELSE ROUND(
            (
                (
                    COALESCE(SUM(vsm.total_sales_amount), 0) - COALESCE(SUM(vpm.total_purchase_cost), 0)
                ) / COALESCE(SUM(vsm.total_sales_amount), 0)
            ) * 100,
            2
        )
    END AS kpi_profit_margin_pct,
    CASE
        WHEN COALESCE(SUM(vsm.total_sales_amount), 0) = 0 THEN 0
        ELSE ROUND(
            (
                COALESCE(SUM(vpm.total_purchase_cost), 0) / COALESCE(SUM(vsm.total_sales_amount), 0)
            ) * 100,
            2
        )
    END AS kpi_cost_ratio_pct,
    COUNT(DISTINCT COALESCE(vsm.warehouse_id, vpm.warehouse_id)) AS entity_count,
    NOW() AS last_updated
FROM view_sales_monthly vsm
FULL OUTER JOIN view_purchases_monthly vpm 
    ON vsm.warehouse_id = vpm.warehouse_id 
    AND vsm.period = vpm.period
GROUP BY vsm.period;

-- ============================================================
-- Migration notes
-- ============================================================
-- BEFORE: view_cost_center_pharmacy.pharmacy_id (surrogate)
-- AFTER:  view_cost_center_pharmacy.warehouse_id (natural key)
--
-- BEFORE: view_cost_center_branch.pharmacy_id (surrogate)
-- AFTER:  view_cost_center_branch.pharmacy_warehouse_id (natural key to parent)
--
-- NEW: view_cost_center_pharmacy now includes central warehouses (type="warehouse")
--      These warehouses have no parent and roll up to company-level revenue
--      ETL now captures both branches and warehouses in pharmacy_id field
--      (pharmacy_id = parent_id for branches, NULL for central warehouses)
--
-- This aligns with the fixed dimension tables and Cost_center_model.php updates
-- ============================================================

INSERT INTO
    sma_migrations (name, batch)
VALUES (
        '011_update_views_for_warehouse_id',
        (
            SELECT MAX(batch)
            FROM sma_migrations
        ) + 1
    ) ON DUPLICATE KEY
UPDATE batch =
VALUES (batch);