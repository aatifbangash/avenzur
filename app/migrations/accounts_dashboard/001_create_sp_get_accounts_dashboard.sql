-- ============================================================================
-- Stored Procedure: sp_get_accounts_dashboard
-- ============================================================================
-- Description: Generates comprehensive financial dashboard reports
-- Parameters:
--   @p_report_type: 'ytd' (Year to Date), 'monthly' (Current Month), 'today' (Current Day)
--   @p_reference_date: Reference date for calculations (format: YYYY-MM-DD)
--
-- Returns 7 Result Sets:
--   1. sales_summary: Sales by period (gross and net)
--   2. collection_summary: Collections by period (cash, card, cheque)
--   3. purchase_summary: Purchases by period
--   4. purchase_per_item: Top 5 purchase items
--   5. expiry_report: Products expiring this month
--   6. customer_summary: Customer transactions by period
--   7. overall_summary: KPI totals (single row)
--
-- Date: 2025-10-30
-- ============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_get_accounts_dashboard`$$

CREATE PROCEDURE `sp_get_accounts_dashboard`(
    IN p_report_type VARCHAR(20),  -- 'ytd', 'monthly', 'today'
    IN p_reference_date DATE        -- Reference date for calculations
)
BEGIN
    DECLARE v_start_date DATE;
    DECLARE v_end_date DATE;
    DECLARE v_year_start DATE;
    DECLARE v_month_start DATE;
    DECLARE v_today DATE;
    
    -- Set dates based on reference date
    SET v_today = IFNULL(p_reference_date, CURDATE());
    SET v_year_start = DATE_FORMAT(v_today, '%Y-01-01');
    SET v_month_start = DATE_FORMAT(v_today, '%Y-%m-01');
    
    -- Determine date range based on report type
    IF p_report_type = 'ytd' THEN
        SET v_start_date = v_year_start;
        SET v_end_date = v_today;
    ELSEIF p_report_type = 'monthly' THEN
        SET v_start_date = v_month_start;
        SET v_end_date = LAST_DAY(v_today);
    ELSE -- 'today'
        SET v_start_date = v_today;
        SET v_end_date = v_today;
    END IF;
    
    -- 1. SALES REPORT (Gross and Net)
    -- Shows sales amounts and transaction counts grouped by period
    SELECT 
        'sales_summary' AS report_type,
        p_report_type AS period_type,
        CASE 
            WHEN p_report_type = 'ytd' THEN DATE_FORMAT(s.date, '%Y-%m')
            WHEN p_report_type = 'monthly' THEN DATE_FORMAT(s.date, '%Y-%m-%d')
            ELSE DATE_FORMAT(s.date, '%Y-%m-%d %H:00:00')
        END AS period,
        SUM(s.grand_total) AS total_gross_sales,
        SUM(s.total_net) AS total_net_sales,
        COUNT(s.id) AS sale_count
    FROM sma_sales s
    WHERE DATE(s.date) BETWEEN v_start_date AND v_end_date
        AND s.sale_status != 'returned'
    GROUP BY period
    ORDER BY period;
    
    -- 2. COLLECTION REPORT
    -- Shows collections by payment method (cash, card, cheque)
    SELECT 
        'collection_summary' AS report_type,
        p_report_type AS period_type,
        CASE 
            WHEN p_report_type = 'ytd' THEN DATE_FORMAT(p.date, '%Y-%m')
            WHEN p_report_type = 'monthly' THEN DATE_FORMAT(p.date, '%Y-%m-%d')
            ELSE DATE_FORMAT(p.date, '%Y-%m-%d %H:00:00')
        END AS period,
        SUM(p.amount) AS total_collection,
        COUNT(p.id) AS payment_count,
        SUM(CASE WHEN p.paid_by = 'cash' THEN p.amount ELSE 0 END) AS cash_collection,
        SUM(CASE WHEN p.paid_by = 'card' THEN p.amount ELSE 0 END) AS card_collection,
        SUM(CASE WHEN p.paid_by = 'cheque' THEN p.amount ELSE 0 END) AS cheque_collection
    FROM sma_payments p
    WHERE DATE(p.date) BETWEEN v_start_date AND v_end_date
    GROUP BY period
    ORDER BY period;
    
    -- 3. PURCHASE REPORT
    -- Shows purchase amounts and transaction counts grouped by period
    SELECT 
        'purchase_summary' AS report_type,
        p_report_type AS period_type,
        CASE 
            WHEN p_report_type = 'ytd' THEN DATE_FORMAT(pu.date, '%Y-%m')
            WHEN p_report_type = 'monthly' THEN DATE_FORMAT(pu.date, '%Y-%m-%d')
            ELSE DATE_FORMAT(pu.date, '%Y-%m-%d %H:00:00')
        END AS period,
        SUM(pu.grand_total) AS total_purchase,
        SUM(pu.total_net_purchase) AS net_purchase,
        COUNT(pu.id) AS purchase_count
    FROM sma_purchases pu
    WHERE DATE(pu.date) BETWEEN v_start_date AND v_end_date
        AND pu.status != 'returned'
    GROUP BY period
    ORDER BY period;
    
    -- 4. PURCHASE PER ITEM (Top 5)
    -- Shows top 5 purchase items by amount with detailed metrics
    SELECT 
        'purchase_per_item' AS report_type,
        p_report_type AS period_type,
        pi.product_id,
        pi.product_code,
        pi.product_name,
        pr.name AS product_full_name,
        SUM(pi.quantity) AS total_quantity,
        SUM(pi.subtotal) AS total_amount,
        COUNT(DISTINCT pi.purchase_id) AS purchase_count,
        AVG(pi.net_unit_cost) AS avg_unit_cost
    FROM sma_purchase_items pi
    INNER JOIN sma_purchases pu ON pi.purchase_id = pu.id
    LEFT JOIN sma_products pr ON pi.product_id = pr.id
    WHERE DATE(pu.date) BETWEEN v_start_date AND v_end_date
        AND pu.status != 'returned'
    GROUP BY pi.product_id, pi.product_code, pi.product_name, pr.name
    ORDER BY total_amount DESC
    LIMIT 5;
    
    -- 5. EXPIRY REPORT (Expiring this month & Loss)
    -- Shows products expiring this month with potential loss value
    SELECT 
        'expiry_report' AS report_type,
        pi.product_id,
        pi.product_code,
        pi.product_name,
        pr.name AS product_full_name,
        pi.expiry AS expiry_date,
        DATEDIFF(pi.expiry, v_today) AS days_to_expiry,
        SUM(pi.quantity_balance) AS expiring_quantity,
        SUM(pi.quantity_balance * pi.net_unit_cost) AS potential_loss,
        pi.warehouse_id
    FROM sma_purchase_items pi
    LEFT JOIN sma_products pr ON pi.product_id = pr.id
    WHERE pi.expiry IS NOT NULL
        AND pi.expiry BETWEEN v_month_start AND LAST_DAY(v_month_start)
        AND pi.quantity_balance > 0
    GROUP BY pi.product_id, pi.product_code, pi.product_name, pr.name, 
             pi.expiry, pi.warehouse_id
    ORDER BY expiry_date ASC, potential_loss DESC;
    
    -- 6. CUSTOMER REPORT (Unique customers)
    -- Shows customer activity by period
    SELECT 
        'customer_summary' AS report_type,
        p_report_type AS period_type,
        CASE 
            WHEN p_report_type = 'ytd' THEN DATE_FORMAT(s.date, '%Y-%m')
            WHEN p_report_type = 'monthly' THEN DATE_FORMAT(s.date, '%Y-%m-%d')
            ELSE DATE_FORMAT(s.date, '%Y-%m-%d')
        END AS period,
        COUNT(DISTINCT s.customer_id) AS unique_customers,
        COUNT(s.id) AS total_transactions,
        SUM(s.grand_total) AS total_sales
    FROM sma_sales s
    WHERE DATE(s.date) BETWEEN v_start_date AND v_end_date
        AND s.sale_status != 'returned'
    GROUP BY period
    ORDER BY period;
    
    -- 7. OVERALL SUMMARY
    -- Single row with all KPI totals for the selected period
    SELECT 
        'overall_summary' AS report_type,
        p_report_type AS period_type,
        v_start_date AS start_date,
        v_end_date AS end_date,
        
        -- Sales KPIs
        (SELECT COUNT(DISTINCT id) FROM sma_sales 
         WHERE DATE(date) BETWEEN v_start_date AND v_end_date) AS total_sales_count,
        (SELECT IFNULL(SUM(grand_total), 0) FROM sma_sales 
         WHERE DATE(date) BETWEEN v_start_date AND v_end_date) AS total_gross_sales,
        (SELECT IFNULL(SUM(total_net), 0) FROM sma_sales 
         WHERE DATE(date) BETWEEN v_start_date AND v_end_date) AS total_net_sales,
        
        -- Collection KPIs
        (SELECT IFNULL(SUM(amount), 0) FROM sma_payments 
         WHERE DATE(date) BETWEEN v_start_date AND v_end_date) AS total_collection,
        
        -- Purchase KPIs
        (SELECT IFNULL(SUM(grand_total), 0) FROM sma_purchases 
         WHERE DATE(date) BETWEEN v_start_date AND v_end_date) AS total_purchase,
        
        -- Customer KPIs
        (SELECT COUNT(DISTINCT customer_id) FROM sma_sales 
         WHERE DATE(date) BETWEEN v_start_date AND v_end_date) AS unique_customers,
        
        -- Expiring items value
        (SELECT IFNULL(SUM(quantity_balance * net_unit_cost), 0) 
         FROM sma_purchase_items 
         WHERE expiry BETWEEN v_month_start AND LAST_DAY(v_month_start)
         AND quantity_balance > 0) AS expiring_stock_value;
         
END$$

DELIMITER;

-- ============================================================================
-- Usage Examples
-- ============================================================================
--
-- Year to Date Report:
-- CALL sp_get_accounts_dashboard('ytd', '2025-10-30');
--
-- Monthly Report:
-- CALL sp_get_accounts_dashboard('monthly', '2025-10-30');
--
-- Daily Report:
-- CALL sp_get_accounts_dashboard('today', '2025-10-30');
--
-- ============================================================================