<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration 317: Create Cost Center Dimension Tables
 * 
 * Creates:
 * - dim_pharmacy: Master pharmacy dimensions
 * - dim_branch: Master branch dimensions with pharmacy parent reference
 * - dim_date: Time dimension table for efficient time-based queries
 * 
 * Date: 2025-10-25
 * Purpose: Foundation for cost center hierarchical reporting
 */

class Migration_Update317 extends CI_Migration {

    public function up() {
        // ============================================================
        // Create dim_pharmacy table (Pharmacy Level Master)
        // ============================================================
        $this->db->query("
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Populate dim_pharmacy from sma_warehouses (pharmacy type)
        $this->db->query("
            INSERT INTO sma_dim_pharmacy (warehouse_id, pharmacy_name, pharmacy_code, address, phone, email, country_id, is_active)
            SELECT 
                w.id,
                w.name,
                w.code,
                w.address,
                w.phone,
                w.email,
                w.country,
                1
            FROM sma_warehouses w
            WHERE w.parent_id IS NULL
            ON DUPLICATE KEY UPDATE
                pharmacy_name = VALUES(pharmacy_name),
                address = VALUES(address),
                phone = VALUES(phone),
                email = VALUES(email),
                country_id = VALUES(country_id)
        ");

        // ============================================================
        // Create dim_branch table (Branch Level Master)
        // ============================================================
        $this->db->query("
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Populate dim_branch from sma_warehouses (branch type)
        $this->db->query("
            INSERT INTO sma_dim_branch (
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
            SELECT 
                b.id,
                dp.pharmacy_id,
                b.parent_id,
                b.name,
                b.code,
                b.address,
                b.phone,
                b.email,
                b.country,
                1
            FROM sma_warehouses b
            LEFT JOIN sma_dim_pharmacy dp ON b.parent_id = dp.warehouse_id
            WHERE b.parent_id IS NOT NULL
            ON DUPLICATE KEY UPDATE
                branch_name = VALUES(branch_name),
                address = VALUES(address),
                phone = VALUES(phone),
                email = VALUES(email),
                country_id = VALUES(country_id)
        ");

        // ============================================================
        // Create dim_date table (Time Dimension)
        // ============================================================
        $this->db->query("
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        log_message('info', 'Migration 317: Cost Center Dimension Tables created successfully');
        return true;
    }

    public function down() {
        // Drop tables in reverse order
        $this->db->query("DROP TABLE IF EXISTS `sma_dim_branch`");
        $this->db->query("DROP TABLE IF EXISTS `sma_dim_pharmacy`");
        $this->db->query("DROP TABLE IF EXISTS `sma_dim_date`");
        
        log_message('info', 'Migration 317: Cost Center Dimension Tables rolled back');
        return true;
    }
}
