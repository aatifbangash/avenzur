-- ============================================================
-- Cost Center Migration: Create KPI Views
-- ============================================================
-- File: 005_create_views.sql
-- Purpose: Create aggregated views for dashboard KPI display
-- Date: 2025-10-25
--
-- Creates:
-- - view_cost_center_pharmacy: Pharmacy-level KPI aggregates (monthly)
-- - view_cost_center_branch: Branch-level KPI aggregates (monthly)
-- - view_cost_center_summary: Company-level overview (all hierarchies)
-- ============================================================

-- ============================================================
-- DROP existing views if they exist (for re-run safety)
-- ============================================================
DROP VIEW IF EXISTS `view_cost_center_pharmacy`;

DROP VIEW IF EXISTS `view_cost_center_branch`;

DROP VIEW IF EXISTS `view_cost_center_summary`;

-- ============================================================
-- VIEW 1: view_cost_center_pharmacy
-- Purpose: Monthly pharmacy-level KPI aggregation
-- ============================================================
CREATE VIEW `view_cost_center_pharmacy` AS
SELECT 
    'pharmacy' AS hierarchy_level,
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code,
    CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,
    COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,
    COALESCE(SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost), 0) AS kpi_total_cost,
    COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)), 0) AS kpi_profit_loss,
    CASE 
        WHEN SUM(fcc.total_revenue) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_profit_margin_pct,
    CASE 
        WHEN SUM(fcc.total_revenue) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_cost_ratio_pct,
    COUNT(DISTINCT CASE WHEN db.branch_id IS NOT NULL THEN db.branch_id END) AS branch_count,
    MAX(fcc.updated_at) AS last_updated
FROM sma_dim_pharmacy dp
LEFT JOIN sma_fact_cost_center fcc ON dp.warehouse_id = fcc.warehouse_id
LEFT JOIN sma_dim_branch db ON dp.pharmacy_id = db.pharmacy_id AND db.is_active = 1
WHERE dp.is_active = 1
GROUP BY 
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code,
    fcc.period_year,
    fcc.period_month;

-- ============================================================
-- VIEW 2: view_cost_center_branch
-- Purpose: Monthly branch-level KPI aggregation
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
    CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,
    COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,
    COALESCE(SUM(fcc.total_cogs), 0) AS kpi_cogs,
    COALESCE(SUM(fcc.inventory_movement_cost), 0) AS kpi_inventory_movement_cost,
    COALESCE(SUM(fcc.operational_cost), 0) AS kpi_operational_cost,
    COALESCE(SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost), 0) AS kpi_total_cost,
    COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)), 0) AS kpi_profit_loss,
    CASE 
        WHEN SUM(fcc.total_revenue) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_profit_margin_pct,
    CASE 
        WHEN SUM(fcc.total_revenue) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_cost_ratio_pct,
    MAX(fcc.updated_at) AS last_updated
FROM sma_dim_branch db
LEFT JOIN sma_dim_pharmacy dp ON db.pharmacy_id = dp.pharmacy_id
LEFT JOIN sma_fact_cost_center fcc ON db.warehouse_id = fcc.warehouse_id
WHERE db.is_active = 1
GROUP BY 
    db.branch_id,
    db.warehouse_id,
    db.pharmacy_id,
    db.branch_name,
    db.branch_code,
    dp.pharmacy_name,
    fcc.period_year,
    fcc.period_month;

-- ============================================================
-- VIEW 3: view_cost_center_summary
-- Purpose: Company and pharmacy-level overview summary
-- Combines pharmacy aggregates with entity count
-- ============================================================

CREATE VIEW `view_cost_center_summary` AS
SELECT 
    'company' AS level,
    'RETAJ AL-DAWA' AS entity_name,
    CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,
    COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,
    COALESCE(SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost), 0) AS kpi_total_cost,
    COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)), 0) AS kpi_profit_loss,
    CASE 
        WHEN SUM(fcc.total_revenue) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_profit_margin_pct,
    COUNT(DISTINCT dp.pharmacy_id) AS entity_count,
    MAX(fcc.updated_at) AS last_updated
FROM sma_fact_cost_center fcc
LEFT JOIN sma_dim_pharmacy dp ON fcc.warehouse_id = dp.warehouse_id
GROUP BY fcc.period_year, fcc.period_month

UNION ALL

SELECT 
    'pharmacy' AS level,
    dp.pharmacy_name AS entity_name,
    CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,
    COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,
    COALESCE(SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost), 0) AS kpi_total_cost,
    COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)), 0) AS kpi_profit_loss,
    CASE 
        WHEN SUM(fcc.total_revenue) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_profit_margin_pct,
    COUNT(DISTINCT db.branch_id) AS entity_count,
    MAX(fcc.updated_at) AS last_updated
FROM sma_fact_cost_center fcc
LEFT JOIN sma_dim_pharmacy dp ON fcc.warehouse_id = dp.warehouse_id
LEFT JOIN sma_dim_branch db ON dp.pharmacy_id = db.pharmacy_id
WHERE fcc.warehouse_id IS NOT NULL AND dp.pharmacy_id IS NOT NULL
GROUP BY 
    fcc.warehouse_id,
    dp.pharmacy_id,
    dp.pharmacy_name,
    fcc.period_year,
    fcc.period_month
ORDER BY period DESC, kpi_total_revenue DESC;

-- ============================================================
-- Migration 005 completed successfully.
-- Views created:
-- - view_cost_center_pharmacy (pharmacy-level KPIs)
-- - view_cost_center_branch (branch-level KPIs)
-- - view_cost_center_summary (company-level overview)
-- ============================================================