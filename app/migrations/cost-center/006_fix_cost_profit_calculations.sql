-- ============================================================
-- Cost Center Migration: Fix Cost and Profit Calculations
-- ============================================================
-- File: 006_fix_cost_profit_calculations.sql
-- Purpose: Update KPI views to use correct sources:
--   - Cost: from sma_purchases (grand_total - not paid amounts or actual purchase costs)
--   - Profit: from sma_sales (revenue - cost)
-- Date: 2025-10-25
--
-- CRITICAL CHANGE:
-- Previous: Cost = COGS + Inventory + Operational (from fact table)
-- New: Cost = Purchases from sma_purchases table
--       Profit = Sales - Purchases (from sma_sales and sma_purchases)
--
-- Tables involved:
-- - sma_sales (for revenue/profit calculation)
-- - sma_purchases (for actual purchase costs)
-- - sma_dim_pharmacy (pharmacy master)
-- - sma_dim_branch (branch master)
-- ============================================================

-- ============================================================
-- STEP 1: Create intermediate view for monthly sales aggregates
-- ============================================================
DROP VIEW IF EXISTS `view_sales_monthly`;

CREATE VIEW `view_sales_monthly` AS
SELECT 
    COALESCE(ss.warehouse_id, 0) AS warehouse_id,
    YEAR(ss.date) AS sales_year,
    MONTH(ss.date) AS sales_month,
    CONCAT(YEAR(ss.date), '-', LPAD(MONTH(ss.date), 2, '0')) AS period,
    SUM(ss.grand_total) AS total_sales_amount,
    COUNT(DISTINCT ss.id) AS sales_count,
    MAX(ss.date) AS last_sale_date
FROM sma_sales ss
WHERE ss.sale_status IN ('completed', 'completed_partial')
  AND ss.payment_status NOT IN ('draft', 'void')
GROUP BY 
    COALESCE(ss.warehouse_id, 0),
    YEAR(ss.date),
    MONTH(ss.date);

-- ============================================================
-- STEP 2: Create intermediate view for monthly purchases aggregates
-- ============================================================
DROP VIEW IF EXISTS `view_purchases_monthly`;

CREATE VIEW `view_purchases_monthly` AS
SELECT 
    sp.warehouse_id,
    YEAR(sp.date) AS purchase_year,
    MONTH(sp.date) AS purchase_month,
    CONCAT(YEAR(sp.date), '-', LPAD(MONTH(sp.date), 2, '0')) AS period,
    SUM(sp.grand_total) AS total_purchase_cost,
    COUNT(DISTINCT sp.id) AS purchase_count,
    MAX(sp.date) AS last_purchase_date
FROM sma_purchases sp
WHERE sp.status IN ('received', 'received_partial')
  AND sp.payment_status NOT IN ('draft', 'void', 'cancelled')
GROUP BY 
    sp.warehouse_id,
    YEAR(sp.date),
    MONTH(sp.date);

-- ============================================================
-- STEP 3: DROP existing pharmacy view (will recreate with new logic)
-- ============================================================
DROP VIEW IF EXISTS `view_cost_center_pharmacy`;

-- ============================================================
-- STEP 4: CREATE NEW view_cost_center_pharmacy
-- Purpose: Monthly pharmacy-level KPI aggregation
-- Data Sources:
--   - Revenue: sma_sales (grand_total)
--   - Cost: sma_purchases (grand_total)
--   - Profit: Revenue - Cost
-- ============================================================
CREATE VIEW `view_cost_center_pharmacy` AS
SELECT 
    'pharmacy' AS hierarchy_level,
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code,
    COALESCE(vsm.period, vpm.period) AS period,
    COALESCE(vsm.total_sales_amount, 0) AS kpi_total_revenue,
    COALESCE(vpm.total_purchase_cost, 0) AS kpi_total_cost,
    COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0) AS kpi_profit_loss,
    CASE 
        WHEN COALESCE(vsm.total_sales_amount, 0) = 0 THEN 0
        ELSE ROUND(((COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0)) / COALESCE(vsm.total_sales_amount, 0)) * 100, 2)
    END AS kpi_profit_margin_pct,
    CASE 
        WHEN COALESCE(vsm.total_sales_amount, 0) = 0 THEN 0
        ELSE ROUND((COALESCE(vpm.total_purchase_cost, 0) / COALESCE(vsm.total_sales_amount, 0)) * 100, 2)
    END AS kpi_cost_ratio_pct,
    COUNT(DISTINCT CASE WHEN db.branch_id IS NOT NULL THEN db.branch_id END) AS branch_count,
    COALESCE(vsm.sales_count, 0) AS sales_count,
    COALESCE(vpm.purchase_count, 0) AS purchase_count,
    COALESCE(GREATEST(vsm.last_sale_date, vpm.last_purchase_date), NOW()) AS last_updated
FROM sma_dim_pharmacy dp
LEFT JOIN view_sales_monthly vsm ON dp.warehouse_id = vsm.warehouse_id
LEFT JOIN view_purchases_monthly vpm ON dp.warehouse_id = vpm.warehouse_id 
                                     AND vsm.period = vpm.period
LEFT JOIN sma_dim_branch db ON dp.pharmacy_id = db.pharmacy_id AND db.is_active = 1
WHERE dp.is_active = 1
GROUP BY 
    dp.pharmacy_id,
    dp.warehouse_id,
    dp.pharmacy_name,
    dp.pharmacy_code,
    COALESCE(vsm.period, vpm.period),
    vsm.total_sales_amount,
    vpm.total_purchase_cost,
    vsm.sales_count,
    vpm.purchase_count,
    COALESCE(GREATEST(vsm.last_sale_date, vpm.last_purchase_date), NOW());

-- ============================================================
-- STEP 5: DROP existing branch view (will recreate with new logic)
-- ============================================================
DROP VIEW IF EXISTS `view_cost_center_branch`;

-- ============================================================
-- STEP 6: CREATE NEW view_cost_center_branch
-- Purpose: Monthly branch-level KPI aggregation
-- Data Sources:
--   - Revenue: sma_sales (grand_total) filtered by branch warehouse
--   - Cost: sma_purchases (grand_total) filtered by branch warehouse
--   - Profit: Revenue - Cost
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
    COALESCE(vsm.period, vpm.period) AS period,
    COALESCE(vsm.total_sales_amount, 0) AS kpi_total_revenue,
    COALESCE(vpm.total_purchase_cost, 0) AS kpi_total_cost,
    COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0) AS kpi_profit_loss,
    CASE 
        WHEN COALESCE(vsm.total_sales_amount, 0) = 0 THEN 0
        ELSE ROUND(((COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0)) / COALESCE(vsm.total_sales_amount, 0)) * 100, 2)
    END AS kpi_profit_margin_pct,
    CASE 
        WHEN COALESCE(vsm.total_sales_amount, 0) = 0 THEN 0
        ELSE ROUND((COALESCE(vpm.total_purchase_cost, 0) / COALESCE(vsm.total_sales_amount, 0)) * 100, 2)
    END AS kpi_cost_ratio_pct,
    COALESCE(vsm.sales_count, 0) AS sales_count,
    COALESCE(vpm.purchase_count, 0) AS purchase_count,
    COALESCE(GREATEST(vsm.last_sale_date, vpm.last_purchase_date), NOW()) AS last_updated
FROM sma_dim_branch db
LEFT JOIN sma_dim_pharmacy dp ON db.pharmacy_id = dp.pharmacy_id
LEFT JOIN view_sales_monthly vsm ON db.warehouse_id = vsm.warehouse_id
LEFT JOIN view_purchases_monthly vpm ON db.warehouse_id = vpm.warehouse_id 
                                     AND vsm.period = vpm.period
WHERE db.is_active = 1
GROUP BY 
    db.branch_id,
    db.warehouse_id,
    db.pharmacy_id,
    db.branch_name,
    db.branch_code,
    dp.pharmacy_name,
    COALESCE(vsm.period, vpm.period),
    vsm.total_sales_amount,
    vpm.total_purchase_cost,
    vsm.sales_count,
    vpm.purchase_count,
    COALESCE(GREATEST(vsm.last_sale_date, vpm.last_purchase_date), NOW());

-- ============================================================
-- STEP 7: DROP existing summary view (will recreate with new logic)
-- ============================================================
DROP VIEW IF EXISTS `view_cost_center_summary`;

-- ============================================================
-- STEP 8: CREATE NEW view_cost_center_summary
-- Purpose: Company and pharmacy-level overview summary
-- Data Sources:
--   - Revenue: from sma_sales
--   - Cost: from sma_purchases
--   - Profit: Revenue - Cost
-- ============================================================
CREATE VIEW `view_cost_center_summary` AS
-- Company-level summary (all pharmacies combined)
SELECT 
    'company' AS level,
    'RETAJ AL-DAWA' AS entity_name,
    COALESCE(vsm.period, vpm.period) AS period,
    COALESCE(SUM(vsm.total_sales_amount), 0) AS kpi_total_revenue,
    COALESCE(SUM(vpm.total_purchase_cost), 0) AS kpi_total_cost,
    COALESCE(SUM(vsm.total_sales_amount), 0) - COALESCE(SUM(vpm.total_purchase_cost), 0) AS kpi_profit_loss,
    CASE 
        WHEN COALESCE(SUM(vsm.total_sales_amount), 0) = 0 THEN 0
        ELSE ROUND(((COALESCE(SUM(vsm.total_sales_amount), 0) - COALESCE(SUM(vpm.total_purchase_cost), 0)) / COALESCE(SUM(vsm.total_sales_amount), 0)) * 100, 2)
    END AS kpi_profit_margin_pct,
    COUNT(DISTINCT dp.pharmacy_id) AS entity_count,
    MAX(COALESCE(GREATEST(vsm.last_sale_date, vpm.last_purchase_date), NOW())) AS last_updated
FROM sma_dim_pharmacy dp
LEFT JOIN view_sales_monthly vsm ON dp.warehouse_id = vsm.warehouse_id
LEFT JOIN view_purchases_monthly vpm ON dp.warehouse_id = vpm.warehouse_id 
                                     AND vsm.period = vpm.period
WHERE dp.is_active = 1
GROUP BY COALESCE(vsm.period, vpm.period)

UNION ALL

-- Pharmacy-level summary (each pharmacy separately)
SELECT 
    'pharmacy' AS level,
    dp.pharmacy_name AS entity_name,
    COALESCE(vsm.period, vpm.period) AS period,
    COALESCE(vsm.total_sales_amount, 0) AS kpi_total_revenue,
    COALESCE(vpm.total_purchase_cost, 0) AS kpi_total_cost,
    COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0) AS kpi_profit_loss,
    CASE 
        WHEN COALESCE(vsm.total_sales_amount, 0) = 0 THEN 0
        ELSE ROUND(((COALESCE(vsm.total_sales_amount, 0) - COALESCE(vpm.total_purchase_cost, 0)) / COALESCE(vsm.total_sales_amount, 0)) * 100, 2)
    END AS kpi_profit_margin_pct,
    COUNT(DISTINCT CASE WHEN db.branch_id IS NOT NULL THEN db.branch_id END) AS entity_count,
    COALESCE(GREATEST(vsm.last_sale_date, vpm.last_purchase_date), NOW()) AS last_updated
FROM sma_dim_pharmacy dp
LEFT JOIN view_sales_monthly vsm ON dp.warehouse_id = vsm.warehouse_id
LEFT JOIN view_purchases_monthly vpm ON dp.warehouse_id = vpm.warehouse_id 
                                     AND vsm.period = vpm.period
LEFT JOIN sma_dim_branch db ON dp.pharmacy_id = db.pharmacy_id AND db.is_active = 1
WHERE dp.is_active = 1
GROUP BY 
    dp.pharmacy_id,
    dp.pharmacy_name,
    COALESCE(vsm.period, vpm.period),
    vsm.total_sales_amount,
    vpm.total_purchase_cost,
    COALESCE(GREATEST(vsm.last_sale_date, vpm.last_purchase_date), NOW())
ORDER BY period DESC, kpi_total_revenue DESC;

-- ============================================================
-- Migration 006 completed successfully.
-- Views updated with new calculation sources:
--   - view_cost_center_pharmacy: Cost from sma_purchases
--   - view_cost_center_branch: Cost from sma_purchases
--   - view_cost_center_summary: Cost from sma_purchases
--
-- Helper views created:
--   - view_sales_monthly: Monthly sales aggregates
--   - view_purchases_monthly: Monthly purchases aggregates
-- ============================================================
