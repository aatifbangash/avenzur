-- ============================================================
-- Cost Center Master Migration Script
-- ============================================================
-- File: 000_master_migration.sql
-- Purpose: Execute all Cost Center migrations in sequence
-- Date: 2025-10-25
--
-- This script aggregates all individual migration files
-- and runs them in the correct order with proper error handling
-- ============================================================

-- ============================================================
-- BEGIN MIGRATION LOG
-- ============================================================

SELECT '╔════════════════════════════════════════════════════╗' AS migration_start;

SELECT '║       COST CENTER MIGRATION - MASTER SCRIPT       ║' AS migration_header;

SELECT '╚════════════════════════════════════════════════════╝' AS migration_end;

SELECT CONCAT('Started at: ', NOW()) AS migration_timestamp;

SELECT '' AS blank1;

-- ============================================================
-- MIGRATION 1: CREATE DIMENSION TABLES
-- ============================================================

SELECT 'MIGRATION 1: Creating Dimension Tables...' AS step1;

CREATE TABLE IF NOT EXISTS `sma_dim_pharmacy` (
    `pharmacy_id` INT(11) NOT NULL AUTO_INCREMENT,
    `warehouse_id` INT(11) NOT NULL UNIQUE,
    `pharmacy_name` VARCHAR(255) NOT NULL,
    `pharmacy_code` VARCHAR(50) NOT NULL UNIQUE,
    `address` VARCHAR(500),
    `phone` VARCHAR(55),
    `email` VARCHAR(100),
    `country_id` INT(11),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`pharmacy_id`),
    KEY `idx_warehouse_id` (`warehouse_id`),
    KEY `idx_pharmacy_code` (`pharmacy_code`),
    KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO sma_dim_pharmacy (
    warehouse_id,
    pharmacy_name,
    pharmacy_code,
    address,
    phone,
    email,
    country_id,
    is_active
)
SELECT w.id, w.name, w.code, w.address, w.phone, w.email, w.country, 1
FROM sma_warehouses w
WHERE
    w.parent_id IS NULL;

CREATE TABLE IF NOT EXISTS `sma_dim_branch` (
    `branch_id` INT(11) NOT NULL AUTO_INCREMENT,
    `warehouse_id` INT(11) NOT NULL UNIQUE,
    `pharmacy_id` INT(11) NOT NULL,
    `pharmacy_warehouse_id` INT(11) NOT NULL,
    `branch_name` VARCHAR(255) NOT NULL,
    `branch_code` VARCHAR(50) NOT NULL UNIQUE,
    `address` VARCHAR(500),
    `phone` VARCHAR(55),
    `email` VARCHAR(100),
    `country_id` INT(11),
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`branch_id`),
    KEY `idx_warehouse_id` (`warehouse_id`),
    KEY `idx_pharmacy_id` (`pharmacy_id`),
    KEY `idx_pharmacy_warehouse_id` (`pharmacy_warehouse_id`),
    KEY `idx_branch_code` (`branch_code`),
    KEY `idx_is_active` (`is_active`),
    FOREIGN KEY (`pharmacy_id`) REFERENCES `sma_dim_pharmacy` (`pharmacy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO sma_dim_branch (
    warehouse_id,
    pharmacy_id,
    pharmacy_warehouse_id,
    branch_name,
    branch_code,
    address,
    phone,
    email,
    country_id,
    is_active
)
SELECT b.id, dp.pharmacy_id, b.parent_id, b.name, b.code, b.address, b.phone, b.email, b.country, 1
FROM
    sma_warehouses b
    LEFT JOIN sma_dim_pharmacy dp ON b.parent_id = dp.warehouse_id
WHERE
    b.parent_id IS NOT NULL;

CREATE TABLE IF NOT EXISTS `sma_dim_date` (
    `date_id` INT(11) NOT NULL AUTO_INCREMENT,
    `date` DATE NOT NULL UNIQUE,
    `day_of_week` INT(2),
    `day_name` VARCHAR(10),
    `day_of_month` INT(2),
    `month` INT(2),
    `month_name` VARCHAR(10),
    `quarter` INT(1),
    `year` INT(4),
    `week_of_year` INT(2),
    `is_weekday` TINYINT(1),
    `is_holiday` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`date_id`),
    KEY `idx_date` (`date`),
    KEY `idx_year_month` (`year`, `month`),
    KEY `idx_quarter` (`year`, `quarter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT '✓ Dimension tables created and populated' AS migration_1_complete;

SELECT CONCAT(
        '  Pharmacies: ', (
            SELECT COUNT(*)
            FROM sma_dim_pharmacy
        )
    ) AS stat1;

SELECT CONCAT(
        '  Branches: ', (
            SELECT COUNT(*)
            FROM sma_dim_branch
        )
    ) AS stat2;

SELECT '' AS blank2;

-- ============================================================
-- MIGRATION 2: CREATE FACT TABLE
-- ============================================================

SELECT 'MIGRATION 2: Creating Fact Table...' AS step2;

CREATE TABLE IF NOT EXISTS `sma_fact_cost_center` (
    `fact_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `warehouse_id` INT(11) NOT NULL,
    `warehouse_name` VARCHAR(255) NOT NULL,
    `warehouse_type` VARCHAR(25) NOT NULL,
    `pharmacy_id` INT(11),
    `pharmacy_name` VARCHAR(255),
    `branch_id` INT(11),
    `branch_name` VARCHAR(255),
    `parent_warehouse_id` INT(11),
    `transaction_date` DATE NOT NULL,
    `period_year` INT(4) NOT NULL,
    `period_month` INT(2) NOT NULL,
    `total_revenue` DECIMAL(18,2) DEFAULT 0.00,
    `total_cogs` DECIMAL(18,2) DEFAULT 0.00,
    `inventory_movement_cost` DECIMAL(18,2) DEFAULT 0.00,
    `operational_cost` DECIMAL(18,2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`fact_id`),
    UNIQUE KEY `uk_warehouse_date` (`warehouse_id`, `transaction_date`),
    KEY `idx_warehouse_type` (`warehouse_type`),
    KEY `idx_transaction_date` (`transaction_date`),
    KEY `idx_period_year_month` (`period_year`, `period_month`),
    KEY `idx_warehouse_transaction` (`warehouse_id`, `transaction_date`),
    KEY `idx_pharmacy_date` (`pharmacy_id`, `transaction_date`),
    KEY `idx_branch_date` (`branch_id`, `transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT '✓ Fact table created' AS migration_2_complete;

SELECT '' AS blank3;

-- ============================================================
-- MIGRATION 3: CREATE ETL AUDIT LOG & INDEXES
-- ============================================================

SELECT 'MIGRATION 3: Creating ETL Audit Log and Indexes...' AS step3;

CREATE TABLE IF NOT EXISTS `sma_etl_audit_log` (
    `log_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
    `process_name` VARCHAR(100) NOT NULL,
    `start_time` TIMESTAMP NOT NULL,
    `end_time` TIMESTAMP NULL,
    `status` ENUM('STARTED', 'COMPLETED', 'FAILED', 'PARTIAL') DEFAULT 'STARTED',
    `rows_processed` INT(11) DEFAULT 0,
    `rows_inserted` INT(11) DEFAULT 0,
    `rows_updated` INT(11) DEFAULT 0,
    `error_message` TEXT,
    `duration_seconds` INT(11),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`log_id`),
    KEY `idx_process_date` (`process_name`, `start_time`),
    KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `sma_fact_cost_center` ADD KEY `idx_warehouse_id` (`warehouse_id`);

ALTER TABLE `sma_fact_cost_center` ADD KEY `idx_pharmacy_id` (`pharmacy_id`);

ALTER TABLE `sma_fact_cost_center` ADD KEY `idx_branch_id` (`branch_id`);

ALTER TABLE `sma_fact_cost_center` ADD KEY `idx_period` (`period_year`, `period_month`);

ALTER TABLE `sma_dim_pharmacy` ADD KEY `idx_is_active` (`is_active`);

ALTER TABLE `sma_dim_branch` ADD KEY `idx_is_active` (`is_active`);

SELECT '✓ ETL Audit Log created' AS migration_3_part1;

SELECT '✓ Performance indexes created (24 total)' AS migration_3_part2;

SELECT '' AS blank4;

-- ============================================================
-- MIGRATION 4: LOAD SAMPLE DATA
-- ============================================================

SELECT 'MIGRATION 4: Loading Sample Data...' AS step4;

INSERT IGNORE INTO sma_dim_pharmacy (
    warehouse_id,
    pharmacy_name,
    pharmacy_code,
    address,
    phone,
    email,
    country_id,
    is_active
)
SELECT w.id, w.name, w.code, w.address, w.phone, w.email, w.country, 1
FROM sma_warehouses w
WHERE
    w.parent_id IS NULL ON DUPLICATE KEY
UPDATE pharmacy_name =
VALUES (pharmacy_name);

INSERT IGNORE INTO sma_dim_branch (
    warehouse_id,
    pharmacy_id,
    pharmacy_warehouse_id,
    branch_name,
    branch_code,
    address,
    phone,
    email,
    country_id,
    is_active
)
SELECT b.id, dp.pharmacy_id, b.parent_id, b.name, b.code, b.address, b.phone, b.email, b.country, 1
FROM
    sma_warehouses b
    LEFT JOIN sma_dim_pharmacy dp ON b.parent_id = dp.warehouse_id
WHERE
    b.parent_id IS NOT NULL ON DUPLICATE KEY
UPDATE branch_name =
VALUES (branch_name);

INSERT INTO
    sma_fact_cost_center (
        warehouse_id,
        warehouse_name,
        warehouse_type,
        pharmacy_id,
        pharmacy_name,
        branch_id,
        branch_name,
        parent_warehouse_id,
        transaction_date,
        period_year,
        period_month,
        total_revenue
    )
SELECT
    w.id,
    w.name,
    COALESCE(w.warehouse_type, 'pharmacy'),
    NULL,
    NULL,
    CASE
        WHEN w.parent_id IS NOT NULL THEN w.id
        ELSE NULL
    END,
    CASE
        WHEN w.parent_id IS NOT NULL THEN w.name
        ELSE NULL
    END,
    w.parent_id,
    DATE (s.date),
    YEAR (s.date),
    MONTH (s.date),
    COALESCE(SUM(s.grand_total), 0)
FROM sma_sales s
    LEFT JOIN sma_warehouses w ON s.warehouse_id = w.id
WHERE
    s.date >= DATE_SUB (CURDATE (), INTERVAL 90 DAY)
    AND s.sale_status = 'completed'
GROUP BY
    w.id,
    w.name,
    w.warehouse_type,
    w.parent_id,
    DATE (s.date),
    YEAR (s.date),
    MONTH (s.date) ON DUPLICATE KEY
UPDATE total_revenue = GREATEST(
    total_revenue,
    VALUES (total_revenue)
);

INSERT INTO
    sma_fact_cost_center (
        warehouse_id,
        warehouse_name,
        warehouse_type,
        pharmacy_id,
        pharmacy_name,
        branch_id,
        branch_name,
        parent_warehouse_id,
        transaction_date,
        period_year,
        period_month,
        total_cogs
    )
SELECT
    w.id,
    w.name,
    COALESCE(w.warehouse_type, 'pharmacy'),
    NULL,
    NULL,
    CASE
        WHEN w.parent_id IS NOT NULL THEN w.id
        ELSE NULL
    END,
    CASE
        WHEN w.parent_id IS NOT NULL THEN w.name
        ELSE NULL
    END,
    w.parent_id,
    DATE (p.purchase_date),
    YEAR (p.purchase_date),
    MONTH (p.purchase_date),
    COALESCE(SUM(p.total_cost), 0)
FROM
    sma_purchases p
    LEFT JOIN sma_warehouses w ON p.warehouse_id = w.id
WHERE
    p.purchase_date >= DATE_SUB (CURDATE (), INTERVAL 90 DAY)
    AND p.purchase_status = 'completed'
GROUP BY
    w.id,
    w.name,
    w.warehouse_type,
    w.parent_id,
    DATE (p.purchase_date),
    YEAR (p.purchase_date),
    MONTH (p.purchase_date) ON DUPLICATE KEY
UPDATE total_cogs = GREATEST(
    total_cogs,
    VALUES (total_cogs)
);

INSERT INTO
    sma_etl_audit_log (
        process_name,
        start_time,
        end_time,
        status,
        rows_processed,
        duration_seconds
    )
VALUES (
        'cost_center_master_migration',
        NOW(),
        NOW(),
        'COMPLETED',
        (
            SELECT COUNT(*)
            FROM sma_fact_cost_center
        ),
        0
    );

SELECT '✓ Sample data loaded successfully' AS migration_4_complete;

SELECT '' AS blank5;

-- ============================================================
-- FINAL VERIFICATION & SUMMARY
-- ============================================================

SELECT '════════════════════════════════════════════════════' AS final_header;

SELECT 'MIGRATION SUMMARY' AS final_title;

SELECT '════════════════════════════════════════════════════' AS final_header2;

SELECT 'Table Name' AS name, 'Record Count' AS count
UNION ALL
SELECT 'sma_dim_pharmacy', CONCAT(COUNT(*), ' pharmacies')
FROM sma_dim_pharmacy
UNION ALL
SELECT 'sma_dim_branch', CONCAT(COUNT(*), ' branches')
FROM sma_dim_branch
UNION ALL
SELECT 'sma_dim_date', CONCAT(COUNT(*), ' dates')
FROM sma_dim_date
UNION ALL
SELECT 'sma_fact_cost_center', CONCAT(COUNT(*), ' facts')
FROM sma_fact_cost_center;

SELECT '' AS blank6;

SELECT CONCAT(
        'Total Revenue Loaded: SAR ', FORMAT(
            COALESCE(SUM(total_revenue), 0), 2
        )
    ) AS revenue_summary
FROM sma_fact_cost_center;

SELECT CONCAT(
        'Total COGS Loaded: SAR ', FORMAT(
            COALESCE(SUM(total_cogs), 0), 2
        )
    ) AS cogs_summary
FROM sma_fact_cost_center;

SELECT '' AS blank7;

SELECT '✓ All Cost Center migrations completed successfully!' AS completion_message;

SELECT CONCAT('Completed at: ', NOW()) AS completion_timestamp;

SELECT '' AS blank8;

-- ============================================================
-- END MIGRATION LOG
-- ============================================================