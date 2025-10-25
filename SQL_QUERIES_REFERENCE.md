# SQL Queries Reference - Cost Center Dashboard

## Database Hierarchy Views

### Available Views

```sql
-- Company-level summary
SELECT * FROM view_cost_center_summary WHERE period = '2025-10';

-- Pharmacy-level data (uses dim_pharmacy - NOT RECOMMENDED)
SELECT * FROM view_cost_center_pharmacy WHERE period = '2025-10';

-- Branch-level data
SELECT * FROM view_cost_center_branch WHERE period = '2025-10';
```

---

## Recommended Queries (Now Implemented)

### 1. Fetch All Pharmacies with Health Scores

**File:** `/app/models/admin/Cost_center_model.php` → `get_pharmacies_with_health_scores()`

```sql
SELECT
    w.id AS pharmacy_id,
    w.code AS pharmacy_code,
    w.name AS pharmacy_name,
    w.warehouse_type,
    CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,
    COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,
    COALESCE(SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost), 0) AS kpi_total_cost,
    COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)), 0) AS kpi_profit_loss,
    CASE
        WHEN COALESCE(SUM(fcc.total_revenue), 0) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_profit_margin_pct,
    CASE
        WHEN COALESCE(SUM(fcc.total_revenue), 0) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_cost_ratio_pct,
    COALESCE(COUNT(DISTINCT db.id), 0) AS branch_count,
    MAX(fcc.updated_at) AS last_updated,
    CASE
        WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 30 THEN '✓ Healthy'
        WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 20 THEN '⚠ Monitor'
        ELSE '✗ Low'
    END AS health_status,
    CASE
        WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 30 THEN '#10B981'
        WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 20 THEN '#F59E0B'
        ELSE '#EF4444'
    END AS health_color
FROM sma_warehouses w
LEFT JOIN sma_fact_cost_center fcc ON w.id = fcc.warehouse_id AND CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) = ?
LEFT JOIN sma_warehouses db ON db.warehouse_type = 'branch' AND db.parent_id = w.id
WHERE w.warehouse_type = 'pharmacy' AND w.id NOT IN (32, 48, 51)
GROUP BY w.id, w.code, w.name, fcc.period_year, fcc.period_month
ORDER BY kpi_total_revenue DESC
LIMIT ? OFFSET ?
```

**Parameters:** `[$period, $limit, $offset]`  
**Example:** `['2025-10', 100, 0]`  
**Returns:** 8 pharmacies

---

### 2. Fetch All Branches with Health Scores

**File:** `/app/models/admin/Cost_center_model.php` → `get_branches_with_health_scores()`

```sql
SELECT
    b.id AS branch_id,
    b.code AS branch_code,
    b.name AS branch_name,
    b.warehouse_type,
    p.id AS pharmacy_id,
    p.code AS pharmacy_code,
    p.name AS pharmacy_name,
    CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,
    COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,
    COALESCE(SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost), 0) AS kpi_total_cost,
    COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)), 0) AS kpi_profit_loss,
    CASE
        WHEN COALESCE(SUM(fcc.total_revenue), 0) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_profit_margin_pct,
    CASE
        WHEN COALESCE(SUM(fcc.total_revenue), 0) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_cost_ratio_pct,
    MAX(fcc.updated_at) AS last_updated,
    CASE
        WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 30 THEN '✓ Healthy'
        WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 20 THEN '⚠ Monitor'
        ELSE '✗ Low'
    END AS health_status,
    CASE
        WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 30 THEN '#10B981'
        WHEN COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue), 0) * 100 >= 20 THEN '#F59E0B'
        ELSE '#EF4444'
    END AS health_color
FROM sma_warehouses b
LEFT JOIN sma_warehouses p ON b.parent_id = p.id AND p.warehouse_type = 'pharmacy'
LEFT JOIN sma_fact_cost_center fcc ON b.id = fcc.warehouse_id AND CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) = ?
WHERE b.warehouse_type = 'branch'
GROUP BY b.id, b.code, b.name, p.id, p.code, p.name, fcc.period_year, fcc.period_month
ORDER BY kpi_total_revenue DESC
LIMIT ? OFFSET ?
```

**Parameters:** `[$period, $limit, $offset]`  
**Example:** `['2025-10', 100, 0]`  
**Returns:** 9 branches

---

### 3. Get Company-Level Summary

**File:** `/app/models/admin/Cost_center_model.php` → `get_summary_stats()`

```sql
SELECT
    level,
    entity_name,
    period,
    kpi_total_revenue,
    kpi_total_cost,
    kpi_profit_loss,
    kpi_profit_margin_pct,
    entity_count,
    last_updated
FROM view_cost_center_summary
WHERE period = ?
```

**Parameters:** `[$period]`  
**Example:** `['2025-10']`  
**Returns:** 2 rows (1 company + 1 pharmacy level aggregated)

---

### 4. Get Profit Margins (Gross + Net)

**File:** `/app/models/admin/Cost_center_model.php` → `get_profit_margins_both_types()`

```sql
SELECT
    SUM(total_revenue) AS total_revenue,
    SUM(total_cogs) AS total_cogs,
    SUM(inventory_movement_cost) AS inventory_movement,
    SUM(operational_cost) AS operational_cost
FROM sma_fact_cost_center
WHERE CONCAT(period_year, '-', LPAD(period_month, 2, '0')) = ?
```

**Parameters:** `[$period]`  
**Calculated Values:**

- `gross_margin = ((total_revenue - total_cogs) / total_revenue) * 100`
- `net_margin = ((total_revenue - total_cogs - inventory_movement - operational_cost) / total_revenue) * 100`

---

## Table Structure Reference

### sma_warehouses

```
id              INT PRIMARY KEY
code            VARCHAR(50) - Warehouse code (PHR-001, BR-001-01, etc.)
name            VARCHAR(255) - Display name
warehouse_type  VARCHAR(25) - 'warehouse', 'pharmacy', or 'branch'
parent_id       INT - For branches, points to parent pharmacy
[... other fields]
```

**Hierarchy:**

- Type: `warehouse` → Main warehouses (id: 32, 48, 51)
- Type: `pharmacy` → 8 pharmacies (id: 52-77)
  - Type: `branch` → 9 branches (id: 59-68) with parent_id → pharmacy

---

### sma_fact_cost_center

```
warehouse_id             INT - Links to sma_warehouses.id
period_year             INT
period_month            INT (1-12)
total_revenue           DECIMAL
total_cogs              DECIMAL
inventory_movement_cost DECIMAL
operational_cost        DECIMAL
updated_at              DATETIME
[... other fields]
```

---

## Example Results (from October 2025)

### Query 1: Pharmacies

```
pharmacy_id | pharmacy_code | pharmacy_name                    | branch_count | kpi_total_revenue | health_status
52          | PHR-004      | E&M Central Plaza Pharmacy       | 1            | 0.00              | ✗ Low
53          | PHR-006      | HealthPlus Main Street Pharmacy  | 1            | 0.00              | ✗ Low
54          | PHR-005      | E&M Midtown Pharmacy             | 2            | 0.00              | ✗ Low
55          | PHR-001      | Avenzur Downtown Pharmacy        | 2            | 0.00              | ✗ Low
56          | PHR-002      | Avenzur Northgate Pharmacy       | 1            | 0.00              | ✗ Low
57          | PHR-003      | Avenzur Southside Pharmacy       | 2            | 0.00              | ✗ Low
76          | PHR-0101     | Rawabi North Pharma              | 0            | 0.00              | ✗ Low
77          | PHR-011      | Rawabi South                     | 0            | 0.00              | ✗ Low
```

_Note: October 2025 has no transaction data; September 2025 has main warehouse data only_

---

## Testing Queries

### Check warehouse hierarchy

```sql
SELECT warehouse_type, COUNT(*) FROM sma_warehouses GROUP BY warehouse_type;
-- Result: warehouse (3), pharmacy (8), branch (9)
```

### View all warehouse relationships

```sql
SELECT
    w.id, w.code, w.name, w.warehouse_type,
    p.id parent_id, p.name parent_name
FROM sma_warehouses w
LEFT JOIN sma_warehouses p ON w.parent_id = p.id
ORDER BY w.warehouse_type, w.id;
```

### Check available periods

```sql
SELECT DISTINCT CONCAT(period_year, '-', LPAD(period_month, 2, '0'))
FROM sma_fact_cost_center
ORDER BY period_year DESC, period_month DESC;
```

### Get all data for a specific period

```sql
SELECT w.warehouse_type, w.name, COUNT(*)
FROM sma_fact_cost_center f
JOIN sma_warehouses w ON f.warehouse_id = w.id
WHERE CONCAT(f.period_year, '-', LPAD(f.period_month, 2, '0')) = '2025-09'
GROUP BY f.warehouse_id, w.warehouse_type, w.name;
```

---

**Last Updated:** October 25, 2025  
**Tested:** ✅ All queries validated against live database
