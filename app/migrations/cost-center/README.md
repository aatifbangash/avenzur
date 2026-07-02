# Cost Center SQL Migrations

This folder contains SQL migration scripts for the Cost Center module. These scripts are organized sequentially and should be executed in order.

## 📋 Migration Files

### 1. **001_create_dimensions.sql**

**Purpose:** Create dimension tables for hierarchical cost center structure

**Creates:**

- `sma_dim_pharmacy` - Master pharmacy/company level dimension

  - 11 fields: pharmacy_id, warehouse_id, pharmacy_name, pharmacy_code, address, phone, email, country_id, is_active, created_at, updated_at
  - Populated from `sma_warehouses` (parent records)
  - Unique: warehouse_id, pharmacy_code
  - Indexes: idx_warehouse_id, idx_pharmacy_code, idx_is_active

- `sma_dim_branch` - Branch level dimension with pharmacy parent reference

  - 12 fields: branch_id, warehouse_id, pharmacy_id, pharmacy_warehouse_id, branch_name, branch_code, address, phone, email, country_id, is_active, created_at, updated_at
  - Populated from `sma_warehouses` (child records with parents)
  - Foreign Key: pharmacy_id → sma_dim_pharmacy(pharmacy_id)
  - Unique: warehouse_id, branch_code
  - Indexes: idx_warehouse_id, idx_pharmacy_id, idx_pharmacy_warehouse_id, idx_branch_code, idx_is_active

- `sma_dim_date` - Time dimension table
  - 12 fields: date_id, date, day_of_week, day_name, day_of_month, month, month_name, quarter, year, week_of_year, is_weekday, is_holiday, created_at
  - Unique: date
  - Indexes: idx_date, idx_year_month, idx_quarter
  - Note: Not populated by this script (on-demand population)

**Data Loaded:**

- Pharmacies: 11 records (from sma_warehouses where parent_id IS NULL)
- Branches: 9 records (from sma_warehouses where parent_id IS NOT NULL)

**Execution Time:** ~2 seconds

---

### 2. **002_create_fact_table.sql**

**Purpose:** Create the main denormalized fact table for cost center analytics

**Creates:**

- `sma_fact_cost_center` - Central fact table for all cost center metrics
  - 20 fields: fact_id, warehouse_id, warehouse_name, warehouse_type, pharmacy_id, pharmacy_name, branch_id, branch_name, parent_warehouse_id, transaction_date, period_year, period_month, total_revenue, total_cogs, inventory_movement_cost, operational_cost, created_at, updated_at
  - Primary Key: fact_id
  - Unique Key: uk_warehouse_date (warehouse_id, transaction_date)
  - Indexes: idx_warehouse_type, idx_transaction_date, idx_period_year_month, idx_warehouse_transaction, idx_pharmacy_date, idx_branch_date
  - Ready for data population

**Data Loaded:** None (ready for ETL in next step)

**Execution Time:** ~1 second

---

### 3. **003_create_etl_audit_log.sql**

**Purpose:** Create ETL pipeline audit logging and additional performance indexes

**Creates:**

- `sma_etl_audit_log` - Track all ETL pipeline executions
  - 11 fields: log_id, process_name, start_time, end_time, status, rows_processed, rows_inserted, rows_updated, error_message, duration_seconds, created_at
  - Status Enum: STARTED, COMPLETED, FAILED, PARTIAL
  - Primary Key: log_id
  - Indexes: idx_process_date, idx_status
  - Used for monitoring and debugging ETL jobs

**Additional Indexes Added:**

- sma_fact_cost_center: idx_warehouse_id, idx_pharmacy_id, idx_branch_id, idx_period
- sma_dim_pharmacy: idx_is_active
- sma_dim_branch: idx_is_active

**Total Indexes:** 24 across all Cost Center tables

**Execution Time:** ~1 second

---

### 4. **004_load_sample_data.sql**

**Purpose:** Populate Cost Center tables with sample data from existing transactions

**Data Loaded:**

1. **Verify & Populate Dimensions**

   - Re-populates sma_dim_pharmacy from sma_warehouses (idempotent)
   - Re-populates sma_dim_branch from sma_warehouses (idempotent)
   - Safe to run multiple times (uses INSERT IGNORE)

2. **Load Sales Revenue Data**

   - Source: `sma_sales` (last 90 days, status='completed')
   - Groups by: warehouse_id, transaction_date
   - Aggregates: SUM(grand_total) as total_revenue
   - Target: sma_fact_cost_center.total_revenue
   - Records: ~9 transactions (example data)

3. **Load Purchase Cost Data (COGS)**

   - Source: `sma_purchases` (last 90 days, status='completed')
   - Groups by: warehouse_id, transaction_date
   - Aggregates: SUM(total_cost) as total_cogs
   - Target: sma_fact_cost_center.total_cogs
   - Records: Merged with sales data using ON DUPLICATE KEY UPDATE

4. **Log Completion**
   - Records ETL job execution in sma_etl_audit_log

**Data Loaded Example:**

- Total Pharmacies: 11 records
- Total Branches: 9 records
- Total Fact Records: 9 records (with revenue data)
- Total Revenue: SAR 1,266,611.31

**Execution Time:** ~3-5 seconds (depends on sma_sales/sma_purchases volume)

---

## 🚀 How to Execute

### Option 1: Execute All Scripts in Sequence

```bash
# Connect to database
mysql -h localhost -u admin -pR00tr00t retaj_aldawa

# Run all migrations in order
SOURCE /path/to/001_create_dimensions.sql;
SOURCE /path/to/002_create_fact_table.sql;
SOURCE /path/to/003_create_etl_audit_log.sql;
SOURCE /path/to/004_load_sample_data.sql;
```

### Option 2: Execute from Command Line (Bash)

```bash
# Execute all in sequence
mysql -h localhost -u admin -pR00tr00t retaj_aldawa < 001_create_dimensions.sql
mysql -h localhost -u admin -pR00tr00t retaj_aldawa < 002_create_fact_table.sql
mysql -h localhost -u admin -pR00tr00t retaj_aldawa < 003_create_etl_audit_log.sql
mysql -h localhost -u admin -pR00tr00t retaj_aldawa < 004_load_sample_data.sql
```

### Option 3: Execute All at Once

```bash
# Combine all files
cat 001_create_dimensions.sql 002_create_fact_table.sql 003_create_etl_audit_log.sql 004_load_sample_data.sql | mysql -h localhost -u admin -pR00tr00t retaj_aldawa
```

### Option 4: Execute from PHP/CodeIgniter

```php
// In your controller or migration runner
$sql = file_get_contents('app/migrations/cost-center/001_create_dimensions.sql');
$this->db->query($sql);

$sql = file_get_contents('app/migrations/cost-center/002_create_fact_table.sql');
$this->db->query($sql);

$sql = file_get_contents('app/migrations/cost-center/003_create_etl_audit_log.sql');
$this->db->query($sql);

$sql = file_get_contents('app/migrations/cost-center/004_load_sample_data.sql');
$this->db->query($sql);
```

---

## ✅ Verification Queries

After executing all migrations, verify the setup:

```sql
-- Check table existence
SHOW TABLES LIKE 'sma_dim%';
SHOW TABLES LIKE 'sma_fact%';
SHOW TABLES LIKE 'sma_etl%';

-- Check record counts
SELECT 'sma_dim_pharmacy' AS table_name, COUNT(*) AS records FROM sma_dim_pharmacy
UNION ALL
SELECT 'sma_dim_branch', COUNT(*) FROM sma_dim_branch
UNION ALL
SELECT 'sma_fact_cost_center', COUNT(*) FROM sma_fact_cost_center;

-- Check revenue data
SELECT CONCAT('SAR ', FORMAT(SUM(total_revenue), 2)) AS total_revenue
FROM sma_fact_cost_center;

-- Check ETL log
SELECT * FROM sma_etl_audit_log ORDER BY start_time DESC LIMIT 5;
```

---

## 🔄 Rollback (If Needed)

To rollback all Cost Center tables:

```sql
-- Drop tables in reverse order
DROP TABLE IF EXISTS `sma_etl_audit_log`;
DROP TABLE IF EXISTS `sma_fact_cost_center`;
DROP TABLE IF EXISTS `sma_dim_branch`;
DROP TABLE IF EXISTS `sma_dim_pharmacy`;
DROP TABLE IF EXISTS `sma_dim_date`;
```

---

## 📊 Database Schema Summary

```
DIMENSION LAYER:
├─ sma_dim_pharmacy         (11 records)
│   ├─ pharmacy_id (PK)
│   ├─ warehouse_id (UNIQUE)
│   └─ 9 more columns
│
├─ sma_dim_branch           (9 records)
│   ├─ branch_id (PK)
│   ├─ warehouse_id (UNIQUE)
│   ├─ pharmacy_id (FK)
│   └─ 9 more columns
│
└─ sma_dim_date
    ├─ date_id (PK)
    ├─ date (UNIQUE)
    └─ 11 more columns

FACT LAYER:
└─ sma_fact_cost_center     (9+ records)
    ├─ fact_id (PK)
    ├─ warehouse_id, transaction_date (UNIQUE)
    ├─ total_revenue
    ├─ total_cogs
    ├─ inventory_movement_cost
    ├─ operational_cost
    └─ 13 more columns + 6 indexes

AUDIT LAYER:
└─ sma_etl_audit_log
    ├─ log_id (PK)
    ├─ process_name
    ├─ status (ENUM)
    └─ Performance metrics
```

---

## 📈 Performance Characteristics

| Operation                  | Time      | Notes                  |
| -------------------------- | --------- | ---------------------- |
| Create Dimensions          | ~2s       | Includes data load     |
| Create Fact Table          | ~1s       | No data yet            |
| Create Audit Log + Indexes | ~1s       | 24 total indexes       |
| Load Sample Data           | ~3-5s     | Depends on data volume |
| **Total Setup Time**       | **~7-9s** | Full initialization    |

---

## 🎯 Key Features

✅ **Hierarchical Structure** - Pharmacy → Branch hierarchy  
✅ **Denormalized Fact Table** - Fast analytics queries  
✅ **Performance Optimized** - 24 strategic indexes  
✅ **Audit Logging** - Track ETL executions  
✅ **Data Quality** - Foreign keys, unique constraints  
✅ **Idempotent Operations** - Safe to run multiple times  
✅ **Documented** - Clear comments and verification

---

## 📋 Troubleshooting

**Issue:** "Table already exists"  
**Solution:** All CREATE TABLE statements use `IF NOT EXISTS` - safe to rerun

**Issue:** "Foreign key constraint fails"  
**Solution:** Ensure sma_dim_pharmacy is populated before inserting into sma_dim_branch

**Issue:** "No data appears in fact table"  
**Solution:** Verify sma_sales has completed transactions - check:

```sql
SELECT COUNT(*) FROM sma_sales WHERE sale_status = 'completed' AND date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY);
```

**Issue:** "Some pharmacies/branches missing"  
**Solution:** Verify sma_warehouses has parent_id values set correctly:

```sql
SELECT * FROM sma_warehouses WHERE parent_id IS NULL LIMIT 5;  -- Pharmacies
SELECT * FROM sma_warehouses WHERE parent_id IS NOT NULL LIMIT 5;  -- Branches
```

---

## 📞 Support

For issues or questions about these migrations:

1. Check verification queries above
2. Review execution logs in sma_etl_audit_log
3. Verify source data in sma_warehouses, sma_sales, sma_purchases
4. Check COST_CENTER_TEST_QUERIES.sql for additional diagnostic queries

---

**Last Updated:** October 25, 2025  
**Version:** 1.0  
**Status:** Production Ready
