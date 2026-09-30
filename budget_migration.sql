-- ============================================================
-- BUDGET MODULE DATABASE MIGRATION
-- Database: retaj_aldawa
-- Date: 2025-10-25
-- ============================================================

USE retaj_aldawa;

-- ============================================================
-- TABLE 1: Budget Allocation (Core Budget Table)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_budget_allocation` (
    `allocation_id` BIGINT(20) NOT NULL AUTO_INCREMENT,

-- Hierarchy Information
`hierarchy_level` VARCHAR(50) NOT NULL COMMENT 'company|pharmacy|branch',
    `parent_hierarchy` VARCHAR(50) COMMENT 'company|pharmacy|branch',
    `parent_warehouse_id` INT(11) COMMENT 'FK: warehouse allocating',
    `parent_name` VARCHAR(255),
    
    `child_hierarchy` VARCHAR(50) COMMENT 'pharmacy|branch|none',
    `child_warehouse_id` INT(11) COMMENT 'FK: warehouse receiving',
    `child_name` VARCHAR(255),

-- Budget Information
`period` VARCHAR(7) NOT NULL COMMENT 'YYYY-MM',
    `allocated_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `allocation_method` VARCHAR(50) NOT NULL DEFAULT 'equal' COMMENT 'equal|proportional|custom',

-- Reference Data
`pharmacy_id` INT(11) COMMENT 'For direct pharmacy allocation',
    `branch_id` INT(11) COMMENT 'For direct branch allocation',

-- Audit Information
`allocated_by_user_id` INT(11) NOT NULL,
    `allocated_by_user_name` VARCHAR(255),
    `allocated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_by_user_id` INT(11),
    `updated_at` TIMESTAMP DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

-- Status
`is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `notes` TEXT,
    
    PRIMARY KEY (`allocation_id`),
    UNIQUE KEY `uk_hierarchy_period` (`parent_hierarchy`, `parent_warehouse_id`, `child_warehouse_id`, `period`),
    KEY `idx_parent_warehouse_period` (`parent_warehouse_id`, `period`),
    KEY `idx_child_warehouse_period` (`child_warehouse_id`, `period`),
    KEY `idx_pharmacy_period` (`pharmacy_id`, `period`),
    KEY `idx_branch_period` (`branch_id`, `period`),
    KEY `idx_period` (`period`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_allocated_at` (`allocated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 2: Budget Tracking (Actual vs Budget)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_budget_tracking` (
    `tracking_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `allocation_id` BIGINT(20) NOT NULL,

-- Hierarchy & Period
`hierarchy_level` VARCHAR(50) NOT NULL,
    `warehouse_id` INT(11) NOT NULL,
    `entity_name` VARCHAR(255),
    `period` VARCHAR(7) NOT NULL,

-- Budget vs Actual
`allocated_amount` DECIMAL(15,2) NOT NULL,
    `actual_spent` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'From fact_cost_center',
    `remaining_amount` DECIMAL(15,2) GENERATED ALWAYS AS (allocated_amount - actual_spent) STORED,
    `percentage_used` DECIMAL(8,2) GENERATED ALWAYS AS 
        (CASE 
            WHEN allocated_amount = 0 THEN 0
            ELSE ROUND((actual_spent / allocated_amount) * 100, 2)
        END) STORED,

-- Status Flags
`status` VARCHAR(50) DEFAULT 'safe' COMMENT 'safe|warning|danger|exceeded',
    `status_updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

-- Tracking Info
`calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_sync_at` TIMESTAMP NULL,
    
    PRIMARY KEY (`tracking_id`),
    UNIQUE KEY `uk_allocation_warehouse_period` (`allocation_id`, `warehouse_id`, `period`),
    FOREIGN KEY (`allocation_id`) REFERENCES `sma_budget_allocation` (`allocation_id`) ON DELETE CASCADE,
    KEY `idx_warehouse_period` (`warehouse_id`, `period`),
    KEY `idx_period` (`period`),
    KEY `idx_status` (`status`),
    KEY `idx_percentage_used` (`percentage_used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 3: Budget Forecast (Predictive Analytics)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_budget_forecast` (
    `forecast_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `allocation_id` BIGINT(20) NOT NULL,

-- Hierarchy & Period
`hierarchy_level` VARCHAR(50) NOT NULL,
    `warehouse_id` INT(11) NOT NULL,
    `entity_name` VARCHAR(255),
    `period` VARCHAR(7) NOT NULL,

-- Budget Information
`allocated_amount` DECIMAL(15,2) NOT NULL,
    `current_spent` DECIMAL(15,2) NOT NULL,
    `days_used` INT(11),
    `days_remaining` INT(11),

-- Burn Rate Calculations
`burn_rate_daily` DECIMAL(15,2) COMMENT 'Average daily spending',
    `burn_rate_weekly` DECIMAL(15,2),
    `burn_rate_trend` VARCHAR(50) COMMENT 'increasing|stable|decreasing',

-- Projections
`projected_end_of_month` DECIMAL(15,2) COMMENT 'Total spent if trend continues',
    `projected_vs_budget` DECIMAL(15,2) GENERATED ALWAYS AS (projected_end_of_month - allocated_amount) STORED,
    `confidence_score` DECIMAL(8,2) COMMENT 'Confidence 0-100',

-- Risk Assessment
`risk_level` VARCHAR(50) DEFAULT 'low' COMMENT 'low|medium|high|critical',
    `will_exceed_budget` TINYINT(1) DEFAULT 0,
    `recommendation` TEXT,

-- Calculation Info
`calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `based_on_days` INT(11) COMMENT 'Days of data used for calculation',
    
    PRIMARY KEY (`forecast_id`),
    UNIQUE KEY `uk_allocation_period` (`allocation_id`, `period`),
    FOREIGN KEY (`allocation_id`) REFERENCES `sma_budget_allocation` (`allocation_id`) ON DELETE CASCADE,
    KEY `idx_warehouse_period` (`warehouse_id`, `period`),
    KEY `idx_risk_level` (`risk_level`),
    KEY `idx_will_exceed` (`will_exceed_budget`),
    KEY `idx_calculated_at` (`calculated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 4: Budget Alert Configuration
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_budget_alert_config` (
    `alert_config_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `allocation_id` BIGINT(20) NOT NULL,

-- Alert Information
`threshold_percentage` INT(11) NOT NULL COMMENT '50|75|90|100 (% of budget)',
    `alert_type` VARCHAR(50) NOT NULL COMMENT 'budget_threshold|burn_rate|variance',

-- Recipients & Channels
`recipient_user_ids` JSON COMMENT 'Array of user IDs to notify',
    `recipient_roles` JSON COMMENT 'Array of roles (admin, finance, pharmacy_manager)',
    `notification_channels` JSON COMMENT 'email|sms|in-app|webhook',

-- Status
`is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `trigger_count` INT(11) DEFAULT 0 COMMENT 'Times alert triggered',
    `last_triggered_at` TIMESTAMP NULL,

-- Configuration
`created_by_user_id` INT(11),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`alert_config_id`),
    UNIQUE KEY `uk_allocation_threshold` (`allocation_id`, `threshold_percentage`),
    FOREIGN KEY (`allocation_id`) REFERENCES `sma_budget_allocation` (`allocation_id`) ON DELETE CASCADE,
    KEY `idx_is_active` (`is_active`),
    KEY `idx_threshold` (`threshold_percentage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 5: Budget Alert Trigger Events
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_budget_alert_events` (
    `event_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `alert_config_id` BIGINT(20) NOT NULL,
    `allocation_id` BIGINT(20) NOT NULL,

-- Event Information
`event_type` VARCHAR(50) NOT NULL COMMENT 'threshold_exceeded|burn_rate_spike|variance_detected',
    `percentage_at_trigger` DECIMAL(8,2),
    `amount_at_trigger` DECIMAL(15,2),

-- Status
`status` VARCHAR(50) NOT NULL DEFAULT 'active' COMMENT 'active|acknowledged|resolved',
    `acknowledged_by_user_id` INT(11),
    `acknowledged_at` TIMESTAMP NULL,
    `resolved_at` TIMESTAMP NULL,

-- Notification Sent
`notification_sent` TINYINT(1) DEFAULT 0,
    `notification_sent_at` TIMESTAMP NULL,
    `notification_channels` JSON,

-- Timestamp
`triggered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`event_id`),
    FOREIGN KEY (`alert_config_id`) REFERENCES `sma_budget_alert_config` (`alert_config_id`) ON DELETE CASCADE,
    FOREIGN KEY (`allocation_id`) REFERENCES `sma_budget_allocation` (`allocation_id`) ON DELETE CASCADE,
    KEY `idx_status` (`status`),
    KEY `idx_triggered_at` (`triggered_at`),
    KEY `idx_allocation_event` (`allocation_id`, `triggered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE 6: Budget Audit Trail (All changes tracked)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sma_budget_audit_trail` (
    `audit_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `allocation_id` BIGINT(20) NOT NULL,

-- What Changed
`action` VARCHAR(50) NOT NULL COMMENT 'created|updated|deleted|forecasted|alert_triggered',
    `change_field` VARCHAR(100) COMMENT 'Field that changed',
    `old_value` TEXT,
    `new_value` TEXT,

-- Who Changed It
`user_id` INT(11) NOT NULL,
    `user_name` VARCHAR(255),
    `user_role` VARCHAR(100),

-- When & Where
`changed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `ip_address` VARCHAR(45),
    `change_reason` VARCHAR(500),
    
    PRIMARY KEY (`audit_id`),
    FOREIGN KEY (`allocation_id`) REFERENCES `sma_budget_allocation` (`allocation_id`) ON DELETE CASCADE,
    KEY `idx_allocation_date` (`allocation_id`, `changed_at`),
    KEY `idx_user_date` (`user_id`, `changed_at`),
    KEY `idx_action` (`action`),
    KEY `idx_changed_at` (`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- VIEW: Budget vs Actual Comparison
-- ============================================================
CREATE OR REPLACE VIEW `view_budget_vs_actual` AS
SELECT 
    ba.allocation_id,
    ba.hierarchy_level,
    ba.period,
    COALESCE(ba.parent_warehouse_id, ba.child_warehouse_id) AS warehouse_id,
    COALESCE(ba.parent_name, ba.child_name) AS entity_name,
    ba.allocated_amount,
    COALESCE(bt.actual_spent, 0) AS actual_spent,
    ba.allocated_amount - COALESCE(bt.actual_spent, 0) AS remaining_amount,
    CASE 
        WHEN ba.allocated_amount = 0 THEN 0
        ELSE ROUND((COALESCE(bt.actual_spent, 0) / ba.allocated_amount) * 100, 2)
    END AS percentage_used,
    COALESCE(bt.status, 'safe') AS status,
    bf.burn_rate_daily,
    bf.projected_end_of_month,
    bf.risk_level,
    bf.will_exceed_budget,
    ba.allocated_at,
    ba.is_active
FROM sma_budget_allocation ba
LEFT JOIN sma_budget_tracking bt ON ba.allocation_id = bt.allocation_id
LEFT JOIN sma_budget_forecast bf ON ba.allocation_id = bf.allocation_id
WHERE ba.is_active = 1;

-- ============================================================
-- VIEW: Budget Summary by Entity
-- ============================================================
CREATE OR REPLACE VIEW `view_budget_summary` AS
SELECT 
    ba.hierarchy_level,
    ba.period,
    COALESCE(ba.parent_warehouse_id, ba.child_warehouse_id) AS warehouse_id,
    COALESCE(ba.parent_name, ba.child_name) AS entity_name,
    COUNT(*) as allocation_count,
    SUM(ba.allocated_amount) as total_allocated,
    SUM(COALESCE(bt.actual_spent, 0)) as total_spent,
    SUM(ba.allocated_amount) - SUM(COALESCE(bt.actual_spent, 0)) as total_remaining,
    ROUND(SUM(COALESCE(bt.actual_spent, 0)) / SUM(ba.allocated_amount) * 100, 2) as overall_percentage,
    COUNT(CASE WHEN COALESCE(bt.status, 'safe') = 'safe' THEN 1 END) as safe_count,
    COUNT(CASE WHEN bt.status = 'warning' THEN 1 END) as warning_count,
    COUNT(CASE WHEN bt.status = 'danger' THEN 1 END) as danger_count,
    COUNT(CASE WHEN bt.status = 'exceeded' THEN 1 END) as exceeded_count,
    MAX(ba.allocated_at) as last_allocated
FROM sma_budget_allocation ba
LEFT JOIN sma_budget_tracking bt ON ba.allocation_id = bt.allocation_id
WHERE ba.is_active = 1
GROUP BY ba.hierarchy_level, ba.period, COALESCE(ba.parent_warehouse_id, ba.child_warehouse_id);

-- ============================================================
-- VIEW: Budget Alerts Dashboard
-- ============================================================
CREATE OR REPLACE VIEW `view_budget_alerts_dashboard` AS
SELECT 
    bae.event_id,
    bae.alert_config_id,
    bae.allocation_id,
    ba.parent_name AS entity_name,
    ba.hierarchy_level,
    ba.period,
    bae.event_type,
    bae.status,
    bae.percentage_at_trigger,
    bae.amount_at_trigger,
    bae.triggered_at,
    bae.acknowledged_by_user_id,
    bae.acknowledged_at,
    COALESCE(bt.status, 'safe') as current_status,
    COALESCE(bt.percentage_used, 0) as current_percentage,
    COALESCE(bf.risk_level, 'low') as risk_level
FROM sma_budget_alert_events bae
JOIN sma_budget_allocation ba ON bae.allocation_id = ba.allocation_id
LEFT JOIN sma_budget_tracking bt ON ba.allocation_id = bt.allocation_id
LEFT JOIN sma_budget_forecast bf ON ba.allocation_id = bf.allocation_id
WHERE bae.status IN ('active', 'acknowledged')
ORDER BY bae.triggered_at DESC;

-- ============================================================
-- VERIFY MIGRATION
-- ============================================================
SELECT 'Migration Complete! Tables & Views Created:' AS status;

SHOW TABLES LIKE 'sma_budget%';

SHOW FULL TABLES
WHERE
    TABLE_TYPE = 'VIEW'
    AND TABLE_SCHEMA = 'retaj_aldawa';