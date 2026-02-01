# Cost Center Module - Implementation Guide

**Version:** 1.0  
**Date:** October 25, 2025  
**Status:** Phase 1-2 Complete (Database & API)  
**Last Updated:** 2025-10-25

---

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [API Endpoints](#api-endpoints)
5. [Installation & Setup](#installation--setup)
6. [ETL Pipeline](#etl-pipeline)
7. [Usage Examples](#usage-examples)
8. [Troubleshooting](#troubleshooting)

---

## Overview

The Cost Center module provides hierarchical cost and revenue tracking across your pharmacy network:

- **Company Level:** Total company metrics
- **Pharmacy Level:** Individual pharmacy performance
- **Branch Level:** Detailed branch-level breakdown with cost components

### Key Features

✓ Hierarchical KPI tracking (Company → Pharmacy → Branch)  
✓ Real-time cost center reporting  
✓ Daily automated ETL pipeline  
✓ Monthly aggregated views  
✓ 4 REST API endpoints for drill-down analytics  
✓ Cost breakdown (COGS, inventory movement, operational)  
✓ 12-month trend analysis

---

## Architecture

### Component Diagram

```
┌─────────────────────────────────────────────────────────┐
│              Frontend Dashboard (React)                 │
│  - Period Selector  - KPI Cards  - Charts  - Tables     │
└────────────────────┬────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
┌───────▼──────────────────┐  ┌──▼──────────────────────┐
│   API Controller         │  │  Cache Layer (Redis)    │
│  /api/v1/cost-center/    │  │  - 1 hour TTL           │
│   - pharmacies           │  │  - Invalidate on ETL    │
│   - branches/:id         │  │                         │
│   - timeseries           │  └──┬───────────────────────┘
│   - summary              │     │
└────────────┬─────────────┘     │
             │                   │
        ┌────▼───────────────────▼──────┐
        │   Cost Center Model            │
        │  - get_pharmacies_with_kpis()  │
        │  - get_timeseries_data()       │
        │  - get_cost_breakdown()        │
        └────┬──────────────────────────┘
             │
    ┌────────▼───────────────────┐
    │   Database Views           │
    │ - view_cost_center_pharmacy│
    │ - view_cost_center_branch  │
    │ - view_cost_center_summary │
    └────┬──────────────────────┘
         │
    ┌────▼──────────────────────────────────┐
    │      Fact Table & Dimensions          │
    │  - fact_cost_center (daily data)      │
    │  - dim_pharmacy, dim_branch, dim_date │
    │  - etl_audit_log (ETL tracking)       │
    └────┬──────────────────────────────────┘
         │
    ┌────▼──────────────────────────────────┐
    │    Source Tables (Read-Only)          │
    │  - sma_sales                          │
    │  - sma_purchases                      │
    │  - sma_warehouses (hierarchy)         │
    │  - sma_inventory_movement (future)    │
    └───────────────────────────────────────┘
```

---

## Database Schema

### Dimension Tables

#### `dim_pharmacy`

Master pharmacy data extracted from `sma_warehouses` (pharmacy type)

```sql
-- Columns
pharmacy_id (PK)          -- Auto-increment primary key
warehouse_id (UNIQUE)     -- Reference to sma_warehouses.id
pharmacy_name             -- Pharmacy name
pharmacy_code (UNIQUE)    -- Pharmacy code
address, phone, email     -- Contact info
country_id                -- Country reference
is_active                 -- Soft delete flag
created_at, updated_at    -- Timestamps
```

**Indexes:**

- `idx_warehouse_id` - For warehouse lookup
- `idx_pharmacy_code` - For code-based queries
- `idx_is_active` - For active record filtering

#### `dim_branch`

Master branch data extracted from `sma_warehouses` (branch type with parent)

```sql
-- Columns
branch_id (PK)                    -- Auto-increment
warehouse_id (UNIQUE)             -- Reference to sma_warehouses.id
pharmacy_id (FK)                  -- Reference to dim_pharmacy.pharmacy_id
pharmacy_warehouse_id             -- Parent warehouse ID
branch_name, branch_code          -- Identifiers
address, phone, email             -- Contact info
country_id                        -- Country reference
is_active, created_at, updated_at
```

**Indexes:**

- `idx_warehouse_id`, `idx_pharmacy_id`
- `idx_pharmacy_warehouse_id` - For parent lookups
- `idx_branch_code`, `idx_is_active`

#### `dim_date`

Time dimension for efficient date-based queries (auto-populated for ±4 years)

```sql
-- Columns
date_id (PK)         -- Auto-increment
date (UNIQUE)        -- ISO date
day_of_week          -- 0-6 (Sunday-Saturday)
day_name             -- Monday, Tuesday, etc.
day_of_month         -- 1-31
month, month_name    -- Month number and name
quarter, year        -- Q1-Q4, year number
week_of_year         -- ISO week number
is_weekday           -- Flag for M-F
is_holiday           -- Extensible holiday flag
```

**Indexes:**

- `idx_date` - Primary date lookup
- `idx_year_month` - For monthly aggregations
- `idx_quarter` - For quarterly reporting

### Fact Table

#### `fact_cost_center`

Denormalized daily aggregates of all costs and revenue

```sql
-- Columns
fact_id (PK)                     -- Auto-increment
warehouse_id                     -- Warehouse reference
warehouse_name, warehouse_type   -- Denormalized for performance
pharmacy_id, pharmacy_name       -- Parent pharmacy (if branch)
branch_id, branch_name           -- Self (if branch)
parent_warehouse_id              -- Parent reference
transaction_date (PK)            -- Daily aggregate date
period_year, period_month        -- Year and month for grouping

-- Cost Components
total_revenue                    -- SUM of sales.grand_total
total_cogs                       -- SUM of purchases.grand_total
inventory_movement_cost          -- Transfer costs
operational_cost                 -- Shipping + surcharge
total_cost (COMPUTED)            -- COGS + movement + operational

-- Metadata
created_at, updated_at
```

**Unique Constraint:** `(warehouse_id, transaction_date)` - One record per warehouse per day

**Indexes:**

- `idx_warehouse_date` - Composite for warehouse + date queries
- `idx_warehouse_type` - For level-based filtering
- `idx_transaction_date` - For time-range queries
- `idx_period_year_month` - For monthly aggregations
- `idx_pharmacy_date`, `idx_branch_date` - For hierarchy drill-down

### KPI Views

#### `view_cost_center_pharmacy`

Monthly pharmacy-level KPIs

```sql
SELECT
  pharmacy_id, warehouse_id, pharmacy_name, pharmacy_code
  period (YYYY-MM format)
  days_active
  kpi_total_revenue          -- SUM of daily revenue
  kpi_cogs                   -- SUM of cost of goods
  kpi_inventory_movement    -- Transfer costs
  kpi_operational           -- Operational costs
  kpi_total_cost            -- Total cost
  kpi_profit_loss           -- Revenue - Cost
  kpi_profit_margin_pct     -- (Profit / Revenue) * 100
  kpi_cost_ratio_pct        -- (Cost / Revenue) * 100
  branch_count              -- Number of branches
  last_updated
FROM fact_cost_center
GROUP BY pharmacy_id, period
```

#### `view_cost_center_branch`

Monthly branch-level KPIs with pharmacy parent

```sql
SELECT
  branch_id, warehouse_id, branch_name, branch_code
  pharmacy_id, pharmacy_name
  period (YYYY-MM format)
  days_active
  [Same KPIs as pharmacy view]
  last_updated
FROM fact_cost_center
GROUP BY branch_id, period
```

#### `view_cost_center_summary`

Company-wide monthly summary

```sql
SELECT
  level ('COMPANY')
  entity_name ('Company Total')
  period (YYYY-MM format)
  kpi_total_revenue, kpi_total_cost, kpi_profit_loss, kpi_profit_margin_pct
  entity_count (number of active warehouses)
  last_updated
FROM fact_cost_center
GROUP BY period
```

### Supporting Tables

#### `etl_audit_log`

Track all ETL runs for monitoring and debugging

```sql
-- Columns
log_id (PK)
process_name              -- Name of ETL process
start_time, end_time      -- Execution timestamps
status                    -- STARTED|COMPLETED|FAILED|PARTIAL
rows_processed            -- Number of records processed
rows_inserted, rows_updated
error_message             -- Captured errors
duration_seconds          -- Execution time
created_at
```

---

## API Endpoints

### Base URL

```
http://localhost/avenzur/api/v1/cost-center
```

### 1. Get All Pharmacies with KPIs

**Endpoint:** `GET /pharmacies`

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `period` | string | Current month | YYYY-MM format |
| `sort_by` | string | revenue | revenue\|profit\|margin\|cost |
| `limit` | integer | 100 | Max 500 |
| `offset` | integer | 0 | Pagination offset |

**Example Request:**

```bash
curl "http://localhost/avenzur/api/v1/cost-center/pharmacies?period=2025-10&sort_by=profit&limit=50"
```

**Response (200 OK):**

```json
{
	"success": true,
	"data": [
		{
			"pharmacy_id": 1,
			"warehouse_id": 9,
			"pharmacy_name": "Pharma Drug Store",
			"pharmacy_code": "PHA001",
			"period": "2025-10",
			"kpi_total_revenue": 500000.0,
			"kpi_total_cost": 300000.0,
			"kpi_profit_loss": 200000.0,
			"kpi_profit_margin_pct": 40.0,
			"kpi_cost_ratio_pct": 60.0,
			"branch_count": 3,
			"last_updated": "2025-10-25T14:30:00Z"
		}
	],
	"period": "2025-10",
	"pagination": {
		"total": 5,
		"limit": 50,
		"offset": 0,
		"pages": 1
	},
	"timestamp": "2025-10-25T14:35:22Z",
	"status": 200
}
```

---

### 2. Get Pharmacy with All Branches (Drill-Down)

**Endpoint:** `GET /pharmacies/{id}/branches`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Pharmacy ID |

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `period` | string | Current month | YYYY-MM format |

**Example Request:**

```bash
curl "http://localhost/avenzur/api/v1/cost-center/pharmacies/1/branches?period=2025-10"
```

**Response (200 OK):**

```json
{
	"success": true,
	"pharmacy": {
		"pharmacy_id": 1,
		"warehouse_id": 9,
		"pharmacy_name": "Pharma Drug Store",
		"pharmacy_code": "PHA001",
		"period": "2025-10",
		"kpi_total_revenue": 500000.0,
		"kpi_total_cost": 300000.0,
		"kpi_profit_loss": 200000.0,
		"kpi_profit_margin_pct": 40.0,
		"kpi_cost_ratio_pct": 60.0,
		"branch_count": 3,
		"last_updated": "2025-10-25T14:30:00Z"
	},
	"branches": [
		{
			"branch_id": 10,
			"warehouse_id": 10,
			"branch_name": "Branch 001",
			"branch_code": "BR001",
			"period": "2025-10",
			"kpi_total_revenue": 200000.0,
			"kpi_total_cost": 120000.0,
			"kpi_profit_loss": 80000.0,
			"kpi_profit_margin_pct": 40.0,
			"kpi_cost_ratio_pct": 60.0,
			"last_updated": "2025-10-25T14:30:00Z"
		}
	],
	"period": "2025-10",
	"branch_count": 3,
	"timestamp": "2025-10-25T14:35:22Z",
	"status": 200
}
```

---

### 3. Get Branch Detail with Cost Breakdown

**Endpoint:** `GET /branches/{id}/detail`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Branch ID |

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `period` | string | Current month | YYYY-MM format |

**Example Request:**

```bash
curl "http://localhost/avenzur/api/v1/cost-center/branches/10/detail?period=2025-10"
```

**Response (200 OK):**

```json
{
	"success": true,
	"branch_id": 10,
	"branch_name": "Branch 001",
	"branch_code": "BR001",
	"pharmacy_id": 1,
	"pharmacy_name": "Pharma Drug Store",
	"period": "2025-10",
	"kpi_total_revenue": 200000.0,
	"cost_breakdown": {
		"cogs": 100000.0,
		"inventory_movement": 15000.0,
		"operational": 5000.0,
		"total_cost": 120000.0
	},
	"kpi_profit_loss": 80000.0,
	"kpi_profit_margin_pct": 40.0,
	"kpi_cost_ratio_pct": 60.0,
	"timestamp": "2025-10-25T14:35:22Z",
	"status": 200
}
```

---

### 4. Get Branch Time Series (Trend Data)

**Endpoint:** `GET /branches/{id}/timeseries`

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Branch ID |

**Query Parameters:**
| Parameter | Type | Default | Max | Description |
|-----------|------|---------|-----|-------------|
| `months` | integer | 12 | 60 | Number of months |

**Example Request:**

```bash
curl "http://localhost/avenzur/api/v1/cost-center/branches/10/timeseries?months=12"
```

**Response (200 OK):**

```json
{
	"success": true,
	"branch_id": 10,
	"months": 12,
	"data": [
		{
			"period": "2024-10",
			"revenue": 150000.0,
			"cost": 90000.0,
			"profit": 60000.0,
			"margin_pct": 40.0
		},
		{
			"period": "2024-11",
			"revenue": 180000.0,
			"cost": 108000.0,
			"profit": 72000.0,
			"margin_pct": 40.0
		},
		{
			"period": "2025-10",
			"revenue": 200000.0,
			"cost": 120000.0,
			"profit": 80000.0,
			"margin_pct": 40.0
		}
	],
	"timestamp": "2025-10-25T14:35:22Z",
	"status": 200
}
```

---

### 5. Get Company Summary

**Endpoint:** `GET /summary`

**Query Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `period` | string | Current month | YYYY-MM format |

**Example Request:**

```bash
curl "http://localhost/avenzur/api/v1/cost-center/summary?period=2025-10"
```

**Response (200 OK):**

```json
{
	"success": true,
	"summary": {
		"period": "2025-10",
		"total_revenue": 5000000.0,
		"total_cost": 3000000.0,
		"profit": 2000000.0,
		"profit_margin_pct": 40.0,
		"pharmacy_count": 5,
		"last_updated": "2025-10-25T14:30:00Z"
	},
	"available_periods": [
		{
			"period": "2025-10",
			"period_year": 2025,
			"period_month": 10
		},
		{
			"period": "2025-09",
			"period_year": 2025,
			"period_month": 9
		}
	],
	"timestamp": "2025-10-25T14:35:22Z",
	"status": 200
}
```

---

### Error Responses

**400 Bad Request:**

```json
{
	"success": false,
	"message": "Invalid period format. Use YYYY-MM",
	"status": 400
}
```

**404 Not Found:**

```json
{
	"success": false,
	"message": "Pharmacy not found",
	"status": 404
}
```

**500 Internal Server Error:**

```json
{
	"success": false,
	"message": "Error fetching pharmacies",
	"error": "Database connection failed",
	"status": 500
}
```

---

## Installation & Setup

### Step 1: Run Database Migrations

The three migration files create all necessary tables, views, and stored procedures.

**Using CodeIgniter Migrations:**

```bash
cd /path/to/avenzur
php index.php migrations
```

**Or manually run the migrations:**

```bash
# Migration 1: Dimension Tables
php index.php migrate 001_create_cost_center_dimensions

# Migration 2: Fact Table & Views
php index.php migrate 002_create_fact_cost_center

# Migration 3: ETL Pipeline & Indexes
php index.php migrate 003_create_etl_pipeline
```

### Step 2: Verify Installation

Run the setup test script:

```bash
php tests/cost_center_setup_test.php
```

This will check:

- ✓ All tables created
- ✓ Views accessible
- ✓ Stored procedures installed
- ✓ Indexes created
- ✓ Sample queries working

### Step 3: Backfill Historical Data

Populate fact table with last 90 days of data:

```bash
php database/scripts/etl_cost_center.php backfill 2025-07-25 2025-10-25
```

### Step 4: Schedule Daily ETL

Add cron job to run ETL every day at 2 AM:

```bash
# Edit crontab
crontab -e

# Add line:
0 2 * * * /usr/bin/php /home/user/avenzur/database/scripts/etl_cost_center.php today >> /var/log/avenzur-etl.log 2>&1
```

---

## ETL Pipeline

### How It Works

The ETL pipeline (Extraction, Transformation, Loading) populates the fact table daily with aggregated revenue and cost data.

```
Source Tables (sma_sales, sma_purchases)
            ↓
        Extract by Date
            ↓
    Aggregate by Warehouse
            ↓
   Transform & Calculate KPIs
            ↓
   Load into fact_cost_center
            ↓
    Invalidate Cache
            ↓
   Views Auto-Update
```

### Running Manually

**Today's data:**

```bash
php database/scripts/etl_cost_center.php today
```

**Specific date:**

```bash
php database/scripts/etl_cost_center.php date 2025-10-25
```

**Date range (backfill):**

```bash
php database/scripts/etl_cost_center.php backfill 2025-01-01 2025-10-25
```

### Monitoring ETL

Check recent ETL runs:

```sql
SELECT * FROM etl_audit_log
WHERE process_name = 'sp_populate_fact_cost_center'
ORDER BY start_time DESC
LIMIT 10;
```

Check for failed runs:

```sql
SELECT * FROM etl_audit_log
WHERE status = 'FAILED'
ORDER BY start_time DESC;
```

---

## Usage Examples

### Get Top 10 Pharmacies by Profit

```php
// Model
$pharmacies = $this->cost_center->get_pharmacies_with_kpis('2025-10', 'profit', 10);

// API
GET /api/v1/cost-center/pharmacies?period=2025-10&sort_by=profit&limit=10

// Result
[
  { "pharmacy_name": "Pharma A", "kpi_profit_loss": 200000, ... },
  { "pharmacy_name": "Pharma B", "kpi_profit_loss": 180000, ... },
  ...
]
```

### Compare Branch Performance

```php
// Get pharmacy with all branches
$data = $this->cost_center->get_pharmacy_with_branches(1, '2025-10');

// Identify underperforming branch
$branches = $data['branches'];
usort($branches, fn($a, $b) => $a['kpi_profit_margin_pct'] <=> $b['kpi_profit_margin_pct']);
$worst_branch = $branches[0]; // Lowest margin
```

### Track Revenue Trend

```php
// Get last 12 months
$timeseries = $this->cost_center->get_timeseries_data(10, 12, 'branch');

// Calculate growth
$months_ago = $timeseries[0]; // 12 months ago
$current = end($timeseries);  // Current month
$growth_pct = (($current['revenue'] - $months_ago['revenue']) / $months_ago['revenue']) * 100;
```

### Dashboard Summary

```php
$summary = $this->cost_center->get_summary_stats('2025-10');
$periods = $this->cost_center->get_available_periods(24);

echo "Company Total: {$summary['kpi_total_revenue']}";
echo "Total Cost: {$summary['kpi_total_cost']}";
echo "Profit Margin: {$summary['kpi_profit_margin_pct']}%";
```

---

## Troubleshooting

### Common Issues

#### 1. "No data available for selected period"

**Cause:** Fact table is empty for that month

**Solution:**

```bash
# Backfill data
php database/scripts/etl_cost_center.php backfill 2025-10-01 2025-10-25

# Verify data loaded
SELECT COUNT(*) FROM fact_cost_center
WHERE YEAR(transaction_date)=2025 AND MONTH(transaction_date)=10;
```

#### 2. "Invalid period format"

**Cause:** Period not in YYYY-MM format

**Solution:**

```
❌ Wrong: "October 2025" or "10/2025" or "25-10"
✓ Correct: "2025-10"
```

#### 3. Slow API responses

**Cause:** Missing indexes or large dataset

**Solution:**

```sql
-- Check indexes
SHOW INDEXES FROM fact_cost_center;

-- Analyze table
ANALYZE TABLE fact_cost_center;

-- Run EXPLAIN on slow query
EXPLAIN SELECT * FROM view_cost_center_pharmacy
WHERE period = '2025-10' LIMIT 10;
```

#### 4. ETL failed to run

**Check logs:**

```bash
tail /var/log/avenzur-etl.log

# Or query
SELECT * FROM etl_audit_log
WHERE status = 'FAILED'
ORDER BY start_time DESC;
```

#### 5. Dimension tables empty

**Cause:** No pharmacies/branches marked as such in sma_warehouses

**Solution:**

```sql
-- Check warehouses
SELECT * FROM sma_warehouses
WHERE warehouse_type IN ('pharmacy', 'branch');

-- Manual sync if needed
INSERT INTO dim_pharmacy (warehouse_id, pharmacy_name, pharmacy_code, ...)
SELECT id, name, code, ... FROM sma_warehouses
WHERE warehouse_type = 'pharmacy' AND parent_id IS NULL;
```

---

## Next Steps (Phases 3-4)

### Phase 3: Frontend Dashboard (React Components)

- [ ] CostCenterDashboard.tsx - Main page
- [ ] BudgetCard.tsx - KPI metric display
- [ ] TrendChart.tsx - Revenue/cost trends
- [ ] PharmacyTable.tsx - Sortable pharmacy list
- [ ] BranchTable.tsx - Branch drill-down table

### Phase 4: Testing & Optimization

- [ ] Unit tests for model methods
- [ ] Integration tests for API endpoints
- [ ] Performance testing & optimization
- [ ] Caching strategy (Redis)
- [ ] Production deployment guide

---

## Support & Maintenance

For issues or questions, refer to:

1. Database schema documentation above
2. API endpoint examples
3. ETL pipeline monitoring
4. CodeIgniter model/controller documentation
5. System logs in `/app/logs/`

---

**Last Updated:** October 25, 2025  
**Version:** 1.0 (Phase 1-2 Complete)
