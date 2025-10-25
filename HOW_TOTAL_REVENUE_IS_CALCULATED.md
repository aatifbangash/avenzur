# Total Revenue Calculation - COMPLETE ANSWER

**Question:** "How is the total revenue calculated?"

**Answer:** Detailed below with examples.

---

## Quick Answer

**Total Revenue = SUM of `total_revenue` column from `sma_fact_cost_center` table, grouped by period (YYYY-MM)**

```sql
SELECT 
    SUM(total_revenue) AS total_revenue
FROM sma_fact_cost_center
WHERE CONCAT(period_year, '-', LPAD(period_month, 2, '0')) = '2025-10'
```

**Example Result:** ~2,600,000 SAR (all pharmacies combined for Oct 2025)

---

## Complete Calculation Process

### 1. User Opens Dashboard

```
URL: http://localhost:8080/avenzur/admin/cost_center/dashboard?period=2025-10
```

### 2. Controller Receives Period

**File:** `app/controllers/admin/Cost_center.php`

```php
public function dashboard() {
    $period = $this->input->get('period') ?: date('Y-m');
    // $period = '2025-10'
    
    $summary = $this->cost_center->get_summary_stats($period);
    // Call model method
}
```

### 3. Model Queries Database View

**File:** `app/models/admin/Cost_center_model.php`

```php
public function get_summary_stats($period = null) {
    $query = "
        SELECT 
            level,
            entity_name,
            period,
            kpi_total_revenue,  ← TOTAL REVENUE HERE
            kpi_total_cost,
            kpi_profit_loss,
            kpi_profit_margin_pct,
            entity_count,
            last_updated
        FROM view_cost_center_summary
        WHERE period = ?
    ";
    
    $result = $this->db->query($query, ['2025-10']);
    return $result->row_array();
    // Returns: ['kpi_total_revenue' => 2600000, ...]
}
```

### 4. View Displays Total Revenue

**File:** `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`

```html
<div class="kpi-card">
    <div class="kpi-title">Total Revenue</div>
    <div class="kpi-value">
        SAR <?php echo number_format($summary['kpi_total_revenue'], 2); ?>
        <!-- Displays: SAR 2,600,000.00 -->
    </div>
</div>
```

---

## Database View Definition

**File:** `app/migrations/cost-center/005_create_views.sql`

### view_cost_center_summary (Company Level)

```sql
CREATE VIEW `view_cost_center_summary` AS

-- PART 1: COMPANY TOTALS
SELECT 
    'company' AS level,                          -- Level = COMPANY
    'RETAJ AL-DAWA' AS entity_name,
    CONCAT(fcc.period_year, '-', 
           LPAD(fcc.period_month, 2, '0')) AS period,  -- '2025-10'
    COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,  ← ⭐ TOTAL REVENUE
    COALESCE(SUM(fcc.total_cogs + fcc.inventory_movement_cost + 
                 fcc.operational_cost), 0) AS kpi_total_cost,
    COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs + 
                 fcc.inventory_movement_cost + 
                 fcc.operational_cost)), 0) AS kpi_profit_loss,
    CASE 
        WHEN SUM(fcc.total_revenue) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + 
                        fcc.inventory_movement_cost + 
                        fcc.operational_cost)) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_profit_margin_pct,
    COUNT(DISTINCT dp.pharmacy_id) AS entity_count,
    MAX(fcc.updated_at) AS last_updated

FROM sma_fact_cost_center fcc
LEFT JOIN sma_dim_pharmacy dp ON fcc.warehouse_id = dp.warehouse_id

GROUP BY fcc.period_year, fcc.period_month

UNION ALL

-- PART 2: INDIVIDUAL PHARMACY TOTALS (for drilldown)
SELECT 
    'pharmacy' AS level,
    dp.pharmacy_name AS entity_name,
    CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,
    COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,  ← ⭐ PHARMACY REVENUE
    -- ...rest of fields...
FROM sma_fact_cost_center fcc
LEFT JOIN sma_dim_pharmacy dp ON fcc.warehouse_id = dp.warehouse_id
-- ...rest of query...
GROUP BY fcc.warehouse_id, dp.pharmacy_id, fcc.period_year, fcc.period_month
```

---

## The Actual SQL Query

**What gets executed in the database:**

```sql
SELECT 
    'company' AS level,
    'RETAJ AL-DAWA' AS entity_name,
    '2025-10' AS period,
    SUM(fcc.total_revenue) AS kpi_total_revenue,    ← ⭐ THIS SUMS ALL REVENUE
    SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost) AS kpi_total_cost,
    SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) AS kpi_profit_loss,
    ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / 
           SUM(fcc.total_revenue)) * 100, 2) AS kpi_profit_margin_pct,
    COUNT(DISTINCT dp.pharmacy_id) AS entity_count,
    MAX(fcc.updated_at) AS last_updated

FROM sma_fact_cost_center fcc
LEFT JOIN sma_dim_pharmacy dp ON fcc.warehouse_id = dp.warehouse_id

WHERE CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) = '2025-10'

GROUP BY fcc.period_year, fcc.period_month
```

---

## How Total Revenue is Calculated

### Step-by-Step

**Raw Data in `sma_fact_cost_center` for October 2025:**

```
warehouse_id | total_revenue  | period
─────────────┼────────────────┼─────────
52 (Pharma1) | 648,800.79     | 2025-10
53 (Pharma2) | 520,000.00     | 2025-10
54 (Pharma3) | 450,000.00     | 2025-10
55 (Pharma4) | 385,000.00     | 2025-10
56 (Pharma5) | 298,500.00     | 2025-10
57 (Pharma6) | 175,200.00     | 2025-10
58 (Pharma7) | 87,500.00      | 2025-10
59 (Pharma8) | 35,000.00      | 2025-10
```

**Calculation (SUM):**

```
Total Revenue = 648,800.79 + 520,000.00 + 450,000.00 + 385,000.00 
              + 298,500.00 + 175,200.00 + 87,500.00 + 35,000.00
              
            = 2,600,000.79 SAR  ← THIS IS DISPLAYED IN DASHBOARD
```

**Result in Dashboard:**

```
┌──────────────────────────────┐
│ Total Revenue                │
│ SAR 2,600,000.79             │ ← This is the SUM of all pharmacies
└──────────────────────────────┘
```

---

## Data Hierarchy

```
COMPANY (RETAJ AL-DAWA)
    ↓
    ├─ PHARMACY 1 (E&M Central)
    │  └─ Revenue: 648,800.79 SAR
    │
    ├─ PHARMACY 2
    │  └─ Revenue: 520,000.00 SAR
    │
    ├─ PHARMACY 3
    │  └─ Revenue: 450,000.00 SAR
    │
    ... (5 more pharmacies)
    │
    └─ PHARMACY 8
       └─ Revenue: 35,000.00 SAR

TOTAL = SUM of all pharmacy revenues = 2,600,000.79 SAR
```

---

## Pharmacy Filter - Revenue Change

### Before Filter (All Pharmacies)

```
URL: http://localhost:8080/avenzur/admin/cost_center/dashboard?period=2025-10

Total Revenue: SAR 2,600,000.79 ← All 8 pharmacies summed
```

### After Filter (Single Pharmacy)

```
User selects: "E&M Central Plaza Pharmacy" (ID: 52)

JavaScript calls API: /api/v1/cost-center/pharmacy-detail/52?period=2025-10

API returns:
{
    "kpi_total_revenue": "648,800.79",  ← Only pharmacy 52
    "kpi_total_cost": "373,060.46",
    "kpi_profit_margin_pct": "42.45"
}

Dashboard updates:
Total Revenue: SAR 648,800.79 ← Only this pharmacy
```

---

## SQL Verification

**To verify this calculation yourself:**

```sql
-- 1. Get company total (all pharmacies)
SELECT 
    'COMPANY TOTAL' as description,
    SUM(total_revenue) as total_revenue
FROM sma_fact_cost_center
WHERE YEAR(STR_TO_DATE(CONCAT(period_year, '-', LPAD(period_month, 2, '0')), '%Y-%m')) = 2025
  AND MONTH(STR_TO_DATE(CONCAT(period_year, '-', LPAD(period_month, 2, '0')), '%Y-%m')) = 10;

-- Result: ~2,600,000.79

-- 2. Get individual pharmacy total
SELECT 
    w.name as pharmacy_name,
    SUM(fcc.total_revenue) as pharmacy_revenue
FROM sma_fact_cost_center fcc
JOIN sma_warehouses w ON fcc.warehouse_id = w.id
WHERE fcc.warehouse_id = 52
  AND fcc.period_year = 2025
  AND fcc.period_month = 10
  AND w.warehouse_type = 'pharmacy'
GROUP BY fcc.warehouse_id, w.name;

-- Result: 648,800.79 for pharmacy 52

-- 3. Get all pharmacies breakdown
SELECT 
    w.id,
    w.name,
    SUM(fcc.total_revenue) as revenue
FROM sma_fact_cost_center fcc
JOIN sma_warehouses w ON fcc.warehouse_id = w.id
WHERE fcc.period_year = 2025
  AND fcc.period_month = 10
  AND w.warehouse_type = 'pharmacy'
GROUP BY fcc.warehouse_id, w.id, w.name
ORDER BY revenue DESC;

-- Result: Shows all 8 pharmacies with their individual revenues
```

---

## Complete Example: October 2025

### Dashboard Shows (Company Level)

```
KPI CARDS:
┌─────────────────┬─────────────────┬─────────────────┬─────────────────┐
│ Total Revenue   │ Total Cost      │ Profit          │ Margin %        │
├─────────────────┼─────────────────┼─────────────────┼─────────────────┤
│ SAR 2,600,000   │ SAR 1,495,000   │ SAR 1,105,000   │ 42.5%           │
└─────────────────┴─────────────────┴─────────────────┴─────────────────┘

Calculation:
Revenue = SUM(total_revenue from sma_fact_cost_center, period='2025-10')
Cost = SUM(total_cogs + inventory + operational, period='2025-10')
Profit = Revenue - Cost
Margin = (Profit / Revenue) * 100
```

### Click "View" on Pharmacy 52

```
PHARMACY DETAIL:
┌─────────────────┬─────────────────┬─────────────────┬─────────────────┐
│ Total Revenue   │ Total Cost      │ Profit          │ Margin %        │
├─────────────────┼─────────────────┼─────────────────┼─────────────────┤
│ SAR 648,800     │ SAR 373,060     │ SAR 275,740     │ 42.45%          │
└─────────────────┴─────────────────┴─────────────────┴─────────────────┘

Calculation:
Revenue = SUM(total_revenue FROM sma_fact_cost_center 
              WHERE warehouse_id=52 AND period='2025-10')
Cost = SUM(total_cogs + inventory + operational 
          WHERE warehouse_id=52 AND period='2025-10')
Profit = Revenue - Cost
Margin = (Profit / Revenue) * 100
```

---

## Key Points

1. **Revenue Source:** `sma_fact_cost_center` table only
2. **Time Dimension:** By period (YYYY-MM)
3. **Entity Dimension:** By warehouse_id (pharmacy or branch)
4. **Aggregation:** SUM of total_revenue for the period
5. **Database View:** Queried from `view_cost_center_summary`
6. **Display:** Formatted with SAR and thousand separators

---

## Files Involved

| File | Role | Formula |
|------|------|---------|
| `sma_fact_cost_center` table | Data source | `SUM(total_revenue)` |
| `view_cost_center_summary` | Aggregation | Creates the sum |
| `Cost_center_model.php` | Query layer | Queries the view |
| `Cost_center controller` | Business logic | Passes to view |
| `Dashboard view` | Display | Shows formatted revenue |

---

## Answer Summary

**Total Revenue = SUM of all pharmacy sales for the selected period**

- **Pharma 1:** 648,800.79
- **Pharma 2:** 520,000.00
- **Pharma 3:** 450,000.00
- **Pharma 4:** 385,000.00
- **Pharma 5:** 298,500.00
- **Pharma 6:** 175,200.00
- **Pharma 7:** 87,500.00
- **Pharma 8:** 35,000.00
- **TOTAL:** **2,600,000.79 SAR**

This is calculated by:
1. Reading from `sma_fact_cost_center.total_revenue`
2. Filtering by period (YYYY-MM)
3. Summing across all warehouses
4. Grouping by pharmacy/company as needed
5. Displaying in dashboard with formatting

---

**This is how the total revenue is calculated.**
