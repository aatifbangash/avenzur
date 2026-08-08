-- =====================================================================
-- Stored Procedure: sp_get_accounts_dashboard
-- Purpose: Get comprehensive accounts dashboard data
-- Corrected syntax and logic errors
-- =====================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS rawabi_jeddah.sp_get_accounts_dashboard$$

CREATE PROCEDURE rawabi_jeddah.sp_get_accounts_dashboard(
    IN p_report_type VARCHAR(20),      -- 'today', 'monthly', 'ytd'
    IN p_reference_date DATE            -- Date reference for filtering
)
BEGIN
    DECLARE v_start_date DATE;
    DECLARE v_end_date DATE;
    DECLARE v_year_start DATE;
    
    -- Initialize dates based on report type
    SET v_year_start = CONCAT(YEAR(p_reference_date), '-01-01');
    
    IF p_report_type = 'ytd' THEN
        SET v_start_date = v_year_start;
        SET v_end_date = p_reference_date;
    ELSEIF p_report_type = 'monthly' THEN
        SET v_start_date = DATE_FORMAT(p_reference_date, '%Y-%m-01');
        SET v_end_date = LAST_DAY(p_reference_date);
    ELSE -- 'today'
        SET v_start_date = p_reference_date;
        SET v_end_date = p_reference_date;
    END IF;
    
    -- ===== RESULT SET 1: SALES SUMMARY =====
    SELECT 
        'Sales Summary' AS category,
        COALESCE(SUM(s.grand_total), 0) AS total_sales,
        COALESCE(SUM(s.total_discount), 0) AS total_discount,
        COALESCE(SUM(s.grand_total - COALESCE(s.total_discount, 0)), 0) AS net_sales,
        COUNT(DISTINCT s.customer_id) AS total_customers,
        COUNT(DISTINCT s.id) AS total_transactions,
        CASE 
            WHEN SUM(s.grand_total) > 0 THEN 
                ROUND((SUM(s.grand_total - COALESCE(s.total_discount, 0)) / SUM(s.grand_total)) * 100, 2)
            ELSE 0 
        END AS discount_percentage
    FROM sma_sales s
    WHERE DATE(s.date) >= v_start_date 
        AND DATE(s.date) <= v_end_date
        AND s.grand_total > 0;
    
    -- ===== RESULT SET 2: COLLECTION SUMMARY =====
    SELECT 
        'Collections Summary' AS category,
        COALESCE(SUM(s.paid), 0) AS total_collected,
        COALESCE(SUM(s.grand_total), 0) AS total_due,
        COALESCE(SUM(s.grand_total - COALESCE(s.paid, 0)), 0) AS outstanding,
        CASE 
            WHEN SUM(s.grand_total) > 0 THEN 
                ROUND((SUM(COALESCE(s.paid, 0)) / SUM(s.grand_total)) * 100, 2)
            ELSE 0 
        END AS collection_rate,
        COUNT(DISTINCT CASE WHEN s.payment_status = 'paid' THEN s.id END) AS completed_transactions,
        COUNT(DISTINCT CASE WHEN s.payment_status IN ('pending', 'due') THEN s.id END) AS pending_transactions
    FROM sma_sales s
    WHERE DATE(s.date) >= v_start_date 
        AND DATE(s.date) <= v_end_date
        AND s.grand_total > 0;
    
    -- ===== RESULT SET 3: PURCHASE SUMMARY =====
    SELECT 
        'Purchase Summary' AS category,
        COALESCE(SUM(p.total), 0) AS total_purchase,
        COALESCE(SUM(p.total_discount), 0) AS purchase_discount,
        COALESCE(SUM(p.total - COALESCE(p.total_discount, 0)), 0) AS net_purchase,
        COUNT(DISTINCT p.supplier_id) AS total_suppliers,
        COUNT(DISTINCT p.id) AS total_purchase_orders,
        CASE 
            WHEN SUM(p.total) > 0 THEN 
                ROUND((SUM(COALESCE(p.total_discount, 0)) / SUM(p.total)) * 100, 2)
            ELSE 0 
        END AS purchase_discount_percentage
    FROM sma_purchases p
    WHERE DATE(p.date) >= v_start_date 
        AND DATE(p.date) <= v_end_date
        AND p.total > 0;
    
    -- ===== RESULT SET 4: TOP PURCHASE ITEMS =====
    SELECT 
        pi.product_id,
        pi.product_code,
        pi.product_name,
        SUM(pi.quantity) AS total_quantity,
        COALESCE(SUM(pi.subtotal), 0) AS total_amount,
        ROUND(AVG(COALESCE(pi.unit_cost, 0)), 2) AS avg_price,
        COUNT(DISTINCT pi.purchase_id) AS purchase_count
    FROM sma_purchase_items pi
    INNER JOIN sma_purchases p ON pi.purchase_id = p.id
    WHERE DATE(p.date) >= v_start_date 
        AND DATE(p.date) <= v_end_date
        AND p.total > 0
    GROUP BY pi.product_id, pi.product_code, pi.product_name
    ORDER BY total_quantity DESC
    LIMIT 10;
    
    -- ===== RESULT SET 5: EXPIRY REPORT =====
    -- Using purchase_items expiry data since product_store table wasn't provided
    SELECT 
        pi.warehouse_id,
        pi.product_id,
        pi.product_code,
        pi.product_name,
        pi.expiry AS expiry_date,
        DATEDIFF(pi.expiry, CURDATE()) AS days_to_expiry,
        SUM(pi.quantity_balance) AS expiring_quantity,
        SUM(pi.quantity_balance * COALESCE(pi.net_unit_cost, 0)) AS potential_loss
    FROM sma_purchase_items pi
    WHERE pi.expiry IS NOT NULL
        AND pi.expiry <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        AND pi.expiry > CURDATE()
        AND pi.quantity_balance > 0
    GROUP BY pi.warehouse_id, pi.product_id, pi.product_code, pi.product_name, pi.expiry
    ORDER BY expiry_date ASC, potential_loss DESC
    LIMIT 20;
    
    -- ===== RESULT SET 6: CUSTOMER SUMMARY =====
    SELECT 
        c.id AS customer_id,
        c.name AS customer_name,
        c.company,
        c.phone,
        c.email,
        COUNT(DISTINCT s.id) AS transaction_count,
        COALESCE(SUM(s.grand_total), 0) AS total_purchases,
        COALESCE(SUM(s.paid), 0) AS total_paid,
        COALESCE(SUM(s.grand_total - COALESCE(s.paid, 0)), 0) AS total_outstanding,
        CASE 
            WHEN SUM(s.grand_total) > 0 THEN 
                ROUND((SUM(COALESCE(s.paid, 0)) / SUM(s.grand_total)) * 100, 2)
            ELSE 0 
        END AS payment_percentage
    FROM sma_companies c
    INNER JOIN sma_sales s ON c.id = s.customer_id 
        AND DATE(s.date) >= v_start_date 
        AND DATE(s.date) <= v_end_date
        AND s.grand_total > 0
    GROUP BY c.id, c.name, c.company, c.phone, c.email
    HAVING transaction_count > 0
    ORDER BY total_purchases DESC
    LIMIT 20;
    
    -- ===== RESULT SET 7: OVERALL SUMMARY =====
    SELECT 
        'Overall Summary' AS summary_type,
        COALESCE(sales_data.total_sales_revenue, 0) AS total_sales_revenue,
        COALESCE(purchase_data.total_purchase_cost, 0) AS total_purchase_cost,
        COALESCE(sales_data.total_sales_revenue, 0) - COALESCE(purchase_data.total_purchase_cost, 0) AS gross_profit,
        CASE 
            WHEN COALESCE(sales_data.total_sales_revenue, 0) > 0 THEN 
                ROUND((
                    (COALESCE(sales_data.total_sales_revenue, 0) - COALESCE(purchase_data.total_purchase_cost, 0)) / 
                    sales_data.total_sales_revenue
                ) * 100, 2)
            ELSE 0 
        END AS profit_margin_percentage,
        COALESCE(sales_data.total_customers, 0) AS total_customers,
        COALESCE(sales_data.total_sales_transactions, 0) AS total_sales_transactions,
        COALESCE(sales_data.total_collections, 0) AS total_collections,
        COALESCE(sales_data.avg_transaction_value, 0) AS avg_transaction_value
    FROM (
        SELECT 
            SUM(s.grand_total) AS total_sales_revenue,
            COUNT(DISTINCT s.customer_id) AS total_customers,
            COUNT(DISTINCT s.id) AS total_sales_transactions,
            SUM(COALESCE(s.paid, 0)) AS total_collections,
            AVG(s.grand_total) AS avg_transaction_value
        FROM sma_sales s
        WHERE DATE(s.date) >= v_start_date 
            AND DATE(s.date) <= v_end_date
            AND s.grand_total > 0
    ) AS sales_data
    CROSS JOIN (
        SELECT 
            SUM(p.total) AS total_purchase_cost
        FROM sma_purchases p
        WHERE DATE(p.date) >= v_start_date 
            AND DATE(p.date) <= v_end_date
            AND p.total > 0
    ) AS purchase_data;

END$$

DELIMITER;