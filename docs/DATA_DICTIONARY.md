# Data Dictionary - Available Views & Columns

**October 25, 2025**

---

## VIEW 1: `view_cost_center_pharmacy`

**Purpose:** Monthly pharmacy-level KPI aggregation  
**Granularity:** 1 row per pharmacy per month  
**Refresh:** Daily (via sp_populate_fact_cost_center)

### Columns

| Column                  | Type          | Description             | Sample Value        |
| ----------------------- | ------------- | ----------------------- | ------------------- |
| `hierarchy_level`       | VARCHAR(50)   | Always 'pharmacy'       | 'pharmacy'          |
| `pharmacy_id`           | INT           | Unique pharmacy ID      | 1                   |
| `warehouse_id`          | INT           | Linked warehouse        | 101                 |
| `pharmacy_name`         | VARCHAR(255)  | Pharmacy name           | 'Pharmacy A'        |
| `pharmacy_code`         | VARCHAR(50)   | Pharmacy code           | 'PHA001'            |
| `period`                | VARCHAR(7)    | YYYY-MM format          | '2025-10'           |
| `kpi_total_revenue`     | DECIMAL(15,2) | Monthly sales           | 450000.00           |
| `kpi_total_cost`        | DECIMAL(15,2) | Total monthly costs     | 270000.00           |
| `kpi_profit_loss`       | DECIMAL(15,2) | Revenue - costs         | 180000.00           |
| `kpi_profit_margin_pct` | DECIMAL(8,2)  | Profit / Revenue \* 100 | 40.00               |
| `kpi_cost_ratio_pct`    | DECIMAL(8,2)  | Costs / Revenue \* 100  | 60.00               |
| `branch_count`          | INT           | Active branches         | 3                   |
| `last_updated`          | TIMESTAMP     | Last refresh time       | 2025-10-25 10:30:00 |

### Example Query

```sql
SELECT * FROM view_cost_center_pharmacy
WHERE period = '2025-10'
ORDER BY kpi_total_revenue DESC;

-- Returns:
-- pharmacy_id | pharmacy_name | kpi_total_revenue | kpi_profit_loss | kpi_profit_margin_pct
-- 1           | Pharmacy A    | 450000            | 180000          | 40.00
-- 2           | Pharmacy B    | 320000            | 128000          | 40.00
-- 3           | Pharmacy C    | 180000            | 72000           | 40.00
```

### Use Cases

- List all pharmacies with KPIs
- Rank pharmacies by revenue/profit
- Calculate company totals (SUM by period)
- Identify top performers
- Find underperformers

---

## VIEW 2: `view_cost_center_branch`

**Purpose:** Monthly branch-level KPI aggregation with cost breakdown  
**Granularity:** 1 row per branch per month  
**Refresh:** Daily

### Columns

| Column                        | Type          | Description                | Sample Value        |
| ----------------------------- | ------------- | -------------------------- | ------------------- |
| `hierarchy_level`             | VARCHAR(50)   | Always 'branch'            | 'branch'            |
| `branch_id`                   | INT           | Unique branch ID           | 1                   |
| `warehouse_id`                | INT           | Linked warehouse           | 101                 |
| `pharmacy_id`                 | INT           | Parent pharmacy            | 1                   |
| `branch_name`                 | VARCHAR(255)  | Branch name                | 'Branch 001'        |
| `branch_code`                 | VARCHAR(50)   | Branch code                | 'BR001'             |
| `pharmacy_name`               | VARCHAR(255)  | Parent pharmacy            | 'Pharmacy A'        |
| `period`                      | VARCHAR(7)    | YYYY-MM format             | '2025-10'           |
| `kpi_total_revenue`           | DECIMAL(15,2) | Branch monthly sales       | 150000.00           |
| `kpi_cogs`                    | DECIMAL(15,2) | Cost of goods sold         | 90000.00            |
| `kpi_inventory_movement_cost` | DECIMAL(15,2) | Inventory adjustment costs | 30000.00            |
| `kpi_operational_cost`        | DECIMAL(15,2) | Operational expenses       | 30000.00            |
| `kpi_total_cost`              | DECIMAL(15,2) | Sum of all costs           | 150000.00           |
| `kpi_profit_loss`             | DECIMAL(15,2) | Revenue - costs            | 0.00                |
| `kpi_profit_margin_pct`       | DECIMAL(8,2)  | Profit / Revenue \* 100    | 0.00                |
| `kpi_cost_ratio_pct`          | DECIMAL(8,2)  | Costs / Revenue \* 100     | 100.00              |
| `last_updated`                | TIMESTAMP     | Last refresh               | 2025-10-25 10:30:00 |

### Example Query

```sql
SELECT * FROM view_cost_center_branch
WHERE period = '2025-10' AND pharmacy_id = 1
ORDER BY kpi_total_revenue DESC;

-- Returns cost breakdown per branch
```

### Use Cases

- Get all branches under a pharmacy
- Compare branch performance
- Analyze cost breakdown (COGS vs inventory vs operational)
- Identify profit loss issues
- Drill-down from pharmacy to branch level

---

## VIEW 3: `view_cost_center_summary`

**Purpose:** Company-level and pharmacy-level overview (UNION of two queries)  
**Granularity:** 2 row types per month  
**Refresh:** Daily

### Part A: Company Level

| Column                  | Type          | Description             | Sample Value        |
| ----------------------- | ------------- | ----------------------- | ------------------- |
| `level`                 | VARCHAR(50)   | 'company'               | 'company'           |
| `entity_name`           | VARCHAR(255)  | 'RETAJ AL-DAWA'         | 'RETAJ AL-DAWA'     |
| `period`                | VARCHAR(7)    | '2025-10'               | '2025-10'           |
| `kpi_total_revenue`     | DECIMAL(15,2) | All pharmacies total    | 950000.00           |
| `kpi_total_cost`        | DECIMAL(15,2) | All pharmacies costs    | 570000.00           |
| `kpi_profit_loss`       | DECIMAL(15,2) | Revenue - costs         | 380000.00           |
| `kpi_profit_margin_pct` | DECIMAL(8,2)  | Profit / Revenue \* 100 | 40.00               |
| `entity_count`          | INT           | Number of pharmacies    | 3                   |
| `last_updated`          | TIMESTAMP     | Last refresh            | 2025-10-25 10:30:00 |

### Part B: Pharmacy Level

| Column                  | Type          | Description             | Sample Value        |
| ----------------------- | ------------- | ----------------------- | ------------------- |
| `level`                 | VARCHAR(50)   | 'pharmacy'              | 'pharmacy'          |
| `entity_name`           | VARCHAR(255)  | Pharmacy name           | 'Pharmacy A'        |
| `period`                | VARCHAR(7)    | '2025-10'               | '2025-10'           |
| `kpi_total_revenue`     | DECIMAL(15,2) | Pharmacy total          | 450000.00           |
| `kpi_total_cost`        | DECIMAL(15,2) | Pharmacy costs          | 270000.00           |
| `kpi_profit_loss`       | DECIMAL(15,2) | Revenue - costs         | 180000.00           |
| `kpi_profit_margin_pct` | DECIMAL(8,2)  | Profit / Revenue \* 100 | 40.00               |
| `entity_count`          | INT           | Number of branches      | 3                   |
| `last_updated`          | TIMESTAMP     | Last refresh            | 2025-10-25 10:30:00 |

### Example Query

```sql
SELECT * FROM view_cost_center_summary
WHERE period = '2025-10'
ORDER BY level, kpi_total_revenue DESC;

-- Returns:
-- level    | entity_name      | kpi_total_revenue | kpi_profit_loss | entity_count
-- company  | RETAJ AL-DAWA    | 950000            | 380000          | 3
-- pharmacy | Pharmacy A       | 450000            | 180000          | 3
-- pharmacy | Pharmacy B       | 320000            | 128000          | 2
-- pharmacy | Pharmacy C       | 180000            | 72000           | 1
```

### Use Cases

- Get company overview
- Get pharmacy level summaries
- Compare company vs pharmacy metrics
- Calculate drill-down paths

---

## FACT TABLE: `sma_fact_cost_center`

**Purpose:** Raw monthly cost data (aggregated from transactions)  
**Granularity:** 1 row per warehouse per month  
**Population:** Stored procedure `sp_populate_fact_cost_center` (daily)

### Key Columns

| Column                    | Type          | Description                  |
| ------------------------- | ------------- | ---------------------------- |
| `warehouse_id`            | INT           | FK to warehouse/branch       |
| `period_year`             | INT           | e.g., 2025                   |
| `period_month`            | INT           | e.g., 10 (October)           |
| `total_revenue`           | DECIMAL(15,2) | Sum of all sales             |
| `total_cogs`              | DECIMAL(15,2) | Cost of goods sold           |
| `inventory_movement_cost` | DECIMAL(15,2) | Inventory adjustments        |
| `operational_cost`        | DECIMAL(15,2) | Wages, utilities, rent, etc. |
| `updated_at`              | TIMESTAMP     | Last updated                 |

### Example Query

```sql
SELECT
    CONCAT(period_year, '-', LPAD(period_month, 2, '0')) AS period,
    COUNT(*) as warehouse_count,
    SUM(total_revenue) as total_revenue,
    SUM(total_cogs + inventory_movement_cost + operational_cost) as total_cost
FROM sma_fact_cost_center
GROUP BY period_year, period_month
ORDER BY period_year DESC, period_month DESC
LIMIT 12;
```

---

## DIMENSION TABLES

### `sma_dim_pharmacy`

```sql
-- Maps warehouses to pharmacies
SELECT * FROM sma_dim_pharmacy WHERE is_active = 1;

Columns:
- pharmacy_id (PK)
- warehouse_id (FK)
- pharmacy_name
- pharmacy_code
- city, country
- created_at, is_active
```

### `sma_dim_branch`

```sql
-- Maps warehouses to branches
SELECT * FROM sma_dim_branch WHERE is_active = 1;

Columns:
- branch_id (PK)
- warehouse_id (FK)
- pharmacy_id (FK)
- branch_name
- branch_code
- manager_id
- created_at, is_active
```

---

## API RESPONSE STRUCTURE

### GET `/api/v1/cost-center/summary`

```json
{
  "success": true,
  "summary": {
    "period": "2025-10",
    "total_revenue": 950000,
    "total_cost": 570000,
    "profit": 380000,
    "profit_margin_pct": 40.00,
    "pharmacy_count": 3,
    "last_updated": "2025-10-25T10:30:00Z"
  },
  "available_periods": [
    "2025-10", "2025-09", "2025-08", ...
  ],
  "timestamp": "2025-10-25T10:45:00Z",
  "status": 200
}
```

### GET `/api/v1/cost-center/pharmacies`

```json
{
  "success": true,
  "data": [
    {
      "pharmacy_id": 1,
      "warehouse_id": 101,
      "pharmacy_name": "Pharmacy A",
      "pharmacy_code": "PHA001",
      "period": "2025-10",
      "kpi_total_revenue": 450000,
      "kpi_total_cost": 270000,
      "kpi_profit_loss": 180000,
      "kpi_profit_margin_pct": 40.00,
      "kpi_cost_ratio_pct": 60.00,
      "branch_count": 3,
      "last_updated": "2025-10-25T10:30:00Z"
    },
    { ... }
  ],
  "pagination": {
    "total": 3,
    "limit": 100,
    "offset": 0,
    "pages": 1
  },
  "timestamp": "2025-10-25T10:45:00Z",
  "status": 200
}
```

### GET `/api/v1/cost-center/pharmacies/{id}/branches`

```json
{
  "success": true,
  "pharmacy": {
    "pharmacy_id": 1,
    "warehouse_id": 101,
    "pharmacy_name": "Pharmacy A",
    "kpi_total_revenue": 450000,
    "kpi_total_cost": 270000,
    "kpi_profit_loss": 180000,
    "kpi_profit_margin_pct": 40.00,
    "branch_count": 3
  },
  "branches": [
    {
      "branch_id": 1,
      "warehouse_id": 101,
      "branch_name": "Branch 001",
      "branch_code": "BR001",
      "kpi_total_revenue": 150000,
      "kpi_total_cost": 150000,
      "kpi_profit_loss": 0,
      "kpi_profit_margin_pct": 0.00
    },
    { ... }
  ],
  "branch_count": 3,
  "period": "2025-10",
  "timestamp": "2025-10-25T10:45:00Z",
  "status": 200
}
```

---

## DATA AVAILABILITY CHECKLIST

```
✅ Pharmacy-level KPIs: YES (view_cost_center_pharmacy)
✅ Branch-level KPIs: YES (view_cost_center_branch)
✅ Company-level summary: YES (view_cost_center_summary)
✅ Revenue by entity: YES (kpi_total_revenue)
✅ Cost breakdown: YES (COGS, inventory, operational)
✅ Profit/Loss: YES (kpi_profit_loss)
✅ Margins: YES (kpi_profit_margin_pct)
✅ Historical data: YES (12+ months available)
✅ Hierarchy mapping: YES (pharmacy → branch)
✅ Real-time updates: YES (daily via SP)

❌ Budget allocation: NOT YET (Phase 2)
❌ Budget tracking vs actual: NOT YET (Phase 2)
❌ Forecasting: NOT YET (Phase 5)
```

---

## TESTING QUERIES

### Verify Views Exist

```sql
SHOW FULL TABLES
WHERE TABLE_SCHEMA = 'your_database'
AND TABLE_TYPE = 'VIEW'
AND TABLE_NAME LIKE 'view_cost_center%';
```

### Check Latest Data

```sql
SELECT
    CONCAT(period_year, '-', LPAD(period_month, 2, '0')) AS period,
    COUNT(DISTINCT warehouse_id) as warehouse_count,
    MAX(updated_at) as latest_update
FROM sma_fact_cost_center
GROUP BY period_year, period_month
ORDER BY period_year DESC, period_month DESC
LIMIT 1;
```

### Sample View Data

```sql
-- Pharmacy level
SELECT * FROM view_cost_center_pharmacy
WHERE period = '2025-10'
LIMIT 5;

-- Branch level
SELECT * FROM view_cost_center_branch
WHERE period = '2025-10'
LIMIT 5;

-- Summary
SELECT * FROM view_cost_center_summary
WHERE period = '2025-10'
LIMIT 10;
```

### Calculate Company Totals

```sql
SELECT
    SUM(kpi_total_revenue) as company_revenue,
    SUM(kpi_total_cost) as company_cost,
    SUM(kpi_profit_loss) as company_profit
FROM view_cost_center_pharmacy
WHERE period = '2025-10';
```

---

## REFERENCE FOR DEVELOPERS

### Accessing the Data

**In Dashboard (JavaScript):**

```javascript
// Call API
fetch("/api/v1/cost-center/summary?period=2025-10")
	.then((r) => r.json())
	.then((d) => console.log(d.summary));
```

**In Backend (PHP):**

```php
// Via Model
$data = $this->Cost_center_model->get_summary_stats('2025-10');
// Returns array with all KPI columns
```

**Direct SQL:**

```sql
SELECT * FROM view_cost_center_summary
WHERE period = '2025-10' AND level = 'company';
```

---

**Last Updated:** October 25, 2025  
**Data Status:** Active & Current  
**Next Update:** After Phase 2 (Budget tables)
