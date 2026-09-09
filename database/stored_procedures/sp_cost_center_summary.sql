-- =============================================
-- Stored Procedure: sp_cost_center_summary
-- Purpose: Get company-wide cost center summary for a period
-- Parameters:
--   p_year: Year (e.g., 2025)
--   p_month: Month (1-12)
-- Returns: Single row with company-wide KPIs
-- =============================================

DROP PROCEDURE IF EXISTS sp_cost_center_summary;

DELIMITER $$

CREATE PROCEDURE sp_cost_center_summary(
    IN p_year INT,
    IN p_month INT
)
BEGIN
    SELECT 
        'COMPANY' AS level,
        'All Pharmacies' AS entity_name,
        CONCAT(p_year, '-', LPAD(p_month, 2, '0')) AS period,
        COALESCE(SUM(s.grand_total), 0) AS kpi_total_revenue,
        COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0) AS kpi_total_cost,
        COALESCE(SUM(s.grand_total) - SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0) AS kpi_profit_loss,
        CASE 
            WHEN COALESCE(SUM(s.grand_total), 0) = 0 THEN 0
            ELSE ROUND(((COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0)) / COALESCE(SUM(s.grand_total), 0)) * 100, 2)
        END AS kpi_profit_margin_pct,
        COUNT(DISTINCT s.warehouse_id) AS entity_count,
        MAX(s.date) AS last_updated
    FROM sma_sales s
    LEFT JOIN sma_sale_items si ON s.id = si.sale_id
    WHERE YEAR(s.date) = p_year 
      AND MONTH(s.date) = p_month
      AND s.sale_status = 'completed';
END$$

DELIMITER;