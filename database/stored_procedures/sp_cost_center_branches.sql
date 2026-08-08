-- =============================================
-- Stored Procedure: sp_cost_center_branches
-- Purpose: Get all branches with health scores for a period
-- Parameters:
--   p_year: Year (e.g., 2025)
--   p_month: Month (1-12)
--   p_limit: Number of records to return
--   p_offset: Offset for pagination
-- Returns: Multiple rows, one per branch with KPIs
-- =============================================

DROP PROCEDURE IF EXISTS sp_cost_center_branches;

DELIMITER $$

CREATE PROCEDURE sp_cost_center_branches(
    IN p_year INT,
    IN p_month INT,
    IN p_limit INT,
    IN p_offset INT
)
BEGIN
    SELECT 
        b.id AS branch_id,
        b.code AS branch_code,
        b.name AS branch_name,
        b.warehouse_type,
        p.id AS pharmacy_id,
        p.code AS pharmacy_code,
        p.name AS pharmacy_name,
        CONCAT(p_year, '-', LPAD(p_month, 2, '0')) AS period,
        COALESCE(SUM(s.grand_total), 0) AS kpi_total_revenue,
        COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0) AS kpi_total_cost,
        COALESCE(SUM(s.grand_total) - SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0) AS kpi_profit_loss,
        CASE 
            WHEN COALESCE(SUM(s.grand_total), 0) = 0 THEN 0
            ELSE ROUND(((COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0)) / COALESCE(SUM(s.grand_total), 0)) * 100, 2)
        END AS kpi_profit_margin_pct,
        CASE 
            WHEN COALESCE(SUM(s.grand_total), 0) = 0 THEN 0
            ELSE ROUND((COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0) / COALESCE(SUM(s.grand_total), 0)) * 100, 2)
        END AS kpi_cost_ratio_pct,
        MAX(s.date) AS last_updated,
        CASE 
            WHEN COALESCE((COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0)) / NULLIF(COALESCE(SUM(s.grand_total), 0), 0), 0) * 100 >= 30 THEN '✓ Healthy'
            WHEN COALESCE((COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0)) / NULLIF(COALESCE(SUM(s.grand_total), 0), 0), 0) * 100 >= 20 THEN '⚠ Monitor'
            ELSE '✗ Low'
        END AS health_status,
        CASE 
            WHEN COALESCE((COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0)) / NULLIF(COALESCE(SUM(s.grand_total), 0), 0), 0) * 100 >= 30 THEN '#10B981'
            WHEN COALESCE((COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0)) / NULLIF(COALESCE(SUM(s.grand_total), 0), 0), 0) * 100 >= 20 THEN '#F59E0B'
            ELSE '#EF4444'
        END AS health_color,
        ROUND(COALESCE((COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0)) / NULLIF(COALESCE(SUM(s.grand_total), 0), 0), 0) * 100, 2) AS net_margin_pct
    FROM sma_warehouses b
    LEFT JOIN sma_warehouses p ON b.parent_id = p.id AND p.warehouse_type = 'pharmacy'
    LEFT JOIN sma_sales s ON s.warehouse_id = b.id 
        AND YEAR(s.date) = p_year 
        AND MONTH(s.date) = p_month
        AND s.sale_status = 'completed'
    LEFT JOIN sma_sale_items si ON s.id = si.sale_id
    WHERE b.warehouse_type = 'branch'
    GROUP BY b.id, b.code, b.name, b.warehouse_type, p.id, p.code, p.name
    ORDER BY kpi_total_revenue DESC
    LIMIT p_limit OFFSET p_offset;
END$$

DELIMITER;