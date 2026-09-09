-- =============================================
-- Stored Procedure: sp_cost_center_pharmacy_detail
-- Purpose: Get detailed KPIs for a specific pharmacy
-- Parameters:
--   p_pharmacy_id: Pharmacy warehouse ID
--   p_year: Year (e.g., 2025)
--   p_month: Month (1-12)
-- Returns: Single row with pharmacy KPIs including branches
-- =============================================

DROP PROCEDURE IF EXISTS sp_cost_center_pharmacy_detail;

DELIMITER $$

CREATE PROCEDURE sp_cost_center_pharmacy_detail(
    IN p_pharmacy_id INT,
    IN p_year INT,
    IN p_month INT
)
BEGIN
    SELECT 
        w.id AS pharmacy_id,
        w.code AS pharmacy_code,
        w.name AS pharmacy_name,
        w.warehouse_type,
        CONCAT(p_year, '-', LPAD(p_month, 2, '0')) AS period,
        COALESCE(SUM(s.grand_total), 0) AS kpi_total_revenue,
        COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0) AS kpi_cogs,
        0 AS kpi_inventory_movement,
        0 AS kpi_operational_cost,
        COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0) AS kpi_total_cost,
        COALESCE(SUM(s.grand_total) - SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0) AS kpi_profit_loss,
        CASE 
            WHEN COALESCE(SUM(s.grand_total), 0) = 0 THEN 0
            ELSE ROUND(((COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0)) / COALESCE(SUM(s.grand_total), 0)) * 100, 2)
        END AS kpi_profit_margin_pct,
        CASE 
            WHEN COALESCE(SUM(s.grand_total), 0) = 0 THEN 0
            ELSE ROUND((COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0) / COALESCE(SUM(s.grand_total), 0)) * 100, 2)
        END AS gross_margin_pct,
        CASE 
            WHEN COALESCE(SUM(s.grand_total), 0) = 0 THEN 0
            ELSE ROUND(((COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0)) / COALESCE(SUM(s.grand_total), 0)) * 100, 2)
        END AS net_margin_pct,
        COALESCE(COUNT(DISTINCT db.id), 0) AS branch_count,
        MAX(s.date) AS last_updated
    FROM sma_warehouses w
    LEFT JOIN sma_warehouses db ON db.warehouse_type = 'branch' AND db.parent_id = w.id
    LEFT JOIN sma_sales s ON (s.warehouse_id = w.id OR s.warehouse_id = db.id) 
        AND YEAR(s.date) = p_year 
        AND MONTH(s.date) = p_month
        AND s.sale_status = 'completed'
    LEFT JOIN sma_sale_items si ON s.id = si.sale_id
    WHERE w.warehouse_type = 'pharmacy' 
      AND w.id = p_pharmacy_id
    GROUP BY w.id, w.code, w.name, w.warehouse_type;
END$$

DELIMITER;