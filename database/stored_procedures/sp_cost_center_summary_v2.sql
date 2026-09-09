-- =============================================
-- Stored Procedure: sp_cost_center_summary_v2
-- Purpose: Get company-wide cost center summary with flexible period types
-- Parameters:
--   p_period_type: 'today', 'monthly', 'ytd'
--   p_year: Year (e.g., 2025)
--   p_month: Month (1-12) - only required for 'monthly'
-- Returns: Single row with company-wide KPIs
-- =============================================

DROP PROCEDURE IF EXISTS sp_cost_center_summary_v2;

DELIMITER $$

CREATE PROCEDURE sp_cost_center_summary_v2(
    IN p_period_type VARCHAR(20),
    IN p_year INT,
    IN p_month INT
)
BEGIN
    DECLARE v_period_label VARCHAR(50);
    
    -- Set period label
    SET v_period_label = CASE 
        WHEN p_period_type = 'today' THEN DATE_FORMAT(CURDATE(), '%Y-%m-%d')
        WHEN p_period_type = 'monthly' THEN CONCAT(p_year, '-', LPAD(p_month, 2, '0'))
        WHEN p_period_type = 'ytd' THEN CONCAT(p_year, ' YTD')
        ELSE CONCAT(p_year, '-', LPAD(p_month, 2, '0'))
    END;
    
    -- Dynamic query based on period type
    IF p_period_type = 'today' THEN
        SELECT 
            'COMPANY' AS level,
            'All Pharmacies' AS entity_name,
            v_period_label AS period,
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
        WHERE DATE(s.date) = CURDATE()
          AND s.sale_status = 'completed';
          
    ELSEIF p_period_type = 'monthly' THEN
        SELECT 
            'COMPANY' AS level,
            'All Pharmacies' AS entity_name,
            v_period_label AS period,
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
          
    ELSEIF p_period_type = 'ytd' THEN
        SELECT 
            'COMPANY' AS level,
            'All Pharmacies' AS entity_name,
            v_period_label AS period,
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
          AND s.date >= CONCAT(p_year, '-01-01')
          AND s.date <= CURDATE()
          AND s.sale_status = 'completed';
    END IF;
END$$

DELIMITER;