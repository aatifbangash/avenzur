-- ============================================================
-- BUDGET MODULE - SIMPLIFIED TEST DATA
-- Database: retaj_aldawa
-- ============================================================

USE retaj_aldawa;

-- ============================================================
-- INSERT COMPANY-LEVEL BUDGET ALLOCATION
-- ============================================================
INSERT INTO
    sma_budget_allocation (
        hierarchy_level,
        parent_hierarchy,
        parent_warehouse_id,
        parent_name,
        child_hierarchy,
        child_warehouse_id,
        child_name,
        period,
        allocated_amount,
        allocation_method,
        allocated_by_user_id,
        allocated_by_user_name,
        is_active,
        notes
    )
VALUES (
        'company',
        NULL,
        1,
        'Avenzur Company',
        NULL,
        NULL,
        NULL,
        '2025-10',
        150000.00,
        'equal',
        1,
        'Admin User',
        1,
        'Initial company-wide budget allocation for October 2025'
    );

SET @ company_allocation_id = LAST_INSERT_ID ();

-- ============================================================
-- INSERT PHARMACY-LEVEL ALLOCATIONS
-- ============================================================
INSERT INTO
    sma_budget_allocation (
        hierarchy_level,
        parent_hierarchy,
        parent_warehouse_id,
        parent_name,
        child_hierarchy,
        child_warehouse_id,
        child_name,
        period,
        allocated_amount,
        allocation_method,
        pharmacy_id,
        allocated_by_user_id,
        allocated_by_user_name,
        is_active,
        notes
    )
VALUES (
        'pharmacy',
        'company',
        1,
        'Avenzur Company',
        'pharmacy',
        2,
        'E&M Central Plaza Pharmacy',
        '2025-10',
        75000.00,
        'equal',
        2,
        1,
        'Admin User',
        1,
        'Pharmacy allocation from company budget - 50% split'
    ),
    (
        'pharmacy',
        'company',
        1,
        'Avenzur Company',
        'pharmacy',
        3,
        'HealthPlus Main Street Pharmacy',
        '2025-10',
        75000.00,
        'equal',
        3,
        1,
        'Admin User',
        1,
        'Pharmacy allocation from company budget - 50% split'
    );

SET @ pharmacy_allocation_1 = @ company_allocation_id + 1;

SET @ pharmacy_allocation_2 = @ company_allocation_id + 2;

-- ============================================================
-- INSERT BRANCH-LEVEL ALLOCATIONS
-- ============================================================
INSERT INTO
    sma_budget_allocation (
        hierarchy_level,
        parent_hierarchy,
        parent_warehouse_id,
        parent_name,
        child_hierarchy,
        child_warehouse_id,
        child_name,
        period,
        allocated_amount,
        allocation_method,
        branch_id,
        allocated_by_user_id,
        allocated_by_user_name,
        is_active,
        notes
    )
VALUES (
        'branch',
        'pharmacy',
        2,
        'E&M Central Plaza Pharmacy',
        'branch',
        4,
        'Avenzur Downtown - Main Branch',
        '2025-10',
        37500.00,
        'equal',
        4,
        1,
        'Admin User',
        1,
        'Branch allocation from pharmacy - 50% of pharmacy budget'
    ),
    (
        'branch',
        'pharmacy',
        2,
        'E&M Central Plaza Pharmacy',
        'branch',
        5,
        'Avenzur Southside - Mall Branch',
        '2025-10',
        37500.00,
        'equal',
        5,
        1,
        'Admin User',
        1,
        'Branch allocation from pharmacy - 50% of pharmacy budget'
    ),
    (
        'branch',
        'pharmacy',
        3,
        'HealthPlus Main Street Pharmacy',
        'branch',
        6,
        'E&M Midtown - Main Branch',
        '2025-10',
        75000.00,
        'equal',
        6,
        1,
        'Admin User',
        1,
        'Branch allocation from pharmacy - 100% of pharmacy budget'
    );

-- ============================================================
-- INSERT TRACKING DATA
-- ============================================================
INSERT INTO
    sma_budget_tracking (
        allocation_id,
        hierarchy_level,
        warehouse_id,
        entity_name,
        period,
        allocated_amount,
        actual_spent
    )
VALUES (
        @ company_allocation_id,
        'company',
        1,
        'Avenzur Company',
        '2025-10',
        150000.00,
        975.00
    ),
    (
        @ pharmacy_allocation_1,
        'pharmacy',
        2,
        'E&M Central Plaza Pharmacy',
        '2025-10',
        75000.00,
        450.00
    ),
    (
        @ pharmacy_allocation_2,
        'pharmacy',
        3,
        'HealthPlus Main Street Pharmacy',
        '2025-10',
        75000.00,
        525.00
    );

-- ============================================================
-- INSERT FORECAST DATA
-- ============================================================
INSERT INTO
    sma_budget_forecast (
        allocation_id,
        hierarchy_level,
        warehouse_id,
        entity_name,
        period,
        allocated_amount,
        current_spent,
        days_used,
        days_remaining,
        burn_rate_daily,
        burn_rate_weekly,
        burn_rate_trend,
        projected_end_of_month,
        confidence_score,
        risk_level,
        will_exceed_budget,
        recommendation
    )
VALUES (
        @ company_allocation_id,
        'company',
        1,
        'Avenzur Company',
        '2025-10',
        150000.00,
        975.00,
        4,
        26,
        97.5,
        682.50,
        'stable',
        6435.00,
        85,
        'low',
        0,
        'Current spending is well within budget. Projected to spend 4.3% of allocated budget by month-end.'
    );

-- ============================================================
-- INSERT ALERT CONFIGURATIONS
-- ============================================================
INSERT INTO
    sma_budget_alert_config (
        allocation_id,
        threshold_percentage,
        alert_type,
        recipient_user_ids,
        recipient_roles,
        notification_channels,
        is_active,
        trigger_count,
        created_by_user_id
    )
VALUES (
        @ company_allocation_id,
        80,
        'budget_threshold',
        JSON_ARRAY (1, 2),
        JSON_ARRAY ('admin', 'finance'),
        JSON_ARRAY ('email', 'in-app'),
        1,
        0,
        1
    ),
    (
        @ pharmacy_allocation_1,
        75,
        'budget_threshold',
        JSON_ARRAY (3),
        JSON_ARRAY ('pharmacy_manager'),
        JSON_ARRAY ('email', 'sms'),
        1,
        0,
        1
    );

-- ============================================================
-- INSERT AUDIT TRAIL
-- ============================================================
INSERT INTO
    sma_budget_audit_trail (
        allocation_id,
        action,
        change_field,
        old_value,
        new_value,
        user_id,
        user_name,
        user_role,
        change_reason
    )
VALUES (
        @ company_allocation_id,
        'created',
        'allocated_amount',
        '0',
        '150000',
        1,
        'Admin User',
        'admin',
        'Initial budget allocation for company - October 2025'
    ),
    (
        @ pharmacy_allocation_1,
        'created',
        'allocated_amount',
        '0',
        '75000',
        1,
        'Admin User',
        'admin',
        'Budget allocation from company to E&M Central Plaza Pharmacy'
    ),
    (
        @ pharmacy_allocation_2,
        'created',
        'allocated_amount',
        '0',
        '75000',
        1,
        'Admin User',
        'admin',
        'Budget allocation from company to HealthPlus Main Street Pharmacy'
    );

-- ============================================================
-- VERIFICATION
-- ============================================================
SELECT '✅ TEST DATA POPULATION COMPLETE' AS status;

SELECT COUNT(*) as allocation_count FROM sma_budget_allocation;

SELECT COUNT(*) as tracking_count FROM sma_budget_tracking;

SELECT COUNT(*) as forecast_count FROM sma_budget_forecast;

SELECT COUNT(*) as alert_config_count FROM sma_budget_alert_config;

SELECT COUNT(*) as audit_count FROM sma_budget_audit_trail;