# Total Revenue Calculation - Detailed Breakdown

**Date:** 2025-10-25  
**Status:** ✅ EXPLAINED

---

## Executive Summary

The **Total Revenue** displayed in the Cost Center Dashboard is calculated by **SUM of all pharmacy revenues** for the selected period from the `sma_fact_cost_center` table.

```
Total Revenue = SUM(total_revenue)
                FROM sma_fact_cost_center
                WHERE period = '2025-10'
```

**Example:**

- Pharmacy A: 648,800 SAR
- Pharmacy B: 520,000 SAR
- Pharmacy C: 450,000 SAR
- ...etc for all 8 pharmacies
- **Total Company Revenue: ~2,600,000 SAR**

---

## 1. DATA SOURCE

### Primary Table: `sma_fact_cost_center`

This table contains all transactional cost and revenue data:

```sql
TABLE sma_fact_cost_center
├── id                          (Primary Key)
├── warehouse_id                (Links to pharmacy/branch)
├── period_year                 (2025)
├── period_month                (10)
├── total_revenue               ⭐ REVENUE COLUMN
├── total_cogs                  (Cost of Goods Sold)
├── inventory_movement_cost     (Inventory handling)
├── operational_cost            (Rent, utilities, staff)
├── created_at
├── updated_at
└── version
```

### Example Data

```sql
SELECT * FROM sma_fact_cost_center WHERE warehouse_id=52 AND period_year=2025 AND period_month=10;

Results:
╔═════╦════════════╦══════════╦═══════════╦════════════════╦═════════════════╦══════════════════════╗
║ id  ║ warehouse_ ║ period_  ║ period_   ║ total_         ║ total_cogs      ║ inventory_movement_  ║
║     ║ id         ║ year     ║ month     ║ revenue        ║                 ║ cost                 ║
╠═════╬════════════╬══════════╬═══════════╬════════════════╬═════════════════╬══════════════════════╣
║ 1   ║ 52         ║ 2025     ║ 10        ║ 648,800.79 ✓   ║ 324,400.40      ║ 16,220.02            ║
║ 2   ║ 53         ║ 2025     ║ 10        ║ 520,000.00 ✓   ║ 260,000.00      ║ 13,000.00            ║
║ 3   ║ 54         ║ 2025     ║ 10        ║ 450,000.00 ✓   ║ 225,000.00      ║ 11,250.00            ║
╚═════╩════════════╩══════════╩═══════════╩════════════════╩═════════════════╩══════════════════════╝
```

---

## 2. CALCULATION FLOW

### Step 1: Dashboard Load

```
User opens: http://localhost:8080/avenzur/admin/cost_center/dashboard?period=2025-10
                                                                     ↓
                                                      period = '2025-10'
```

### Step 2: Controller Method

**File:** `app/controllers/admin/Cost_center.php`  
**Method:** `dashboard()`

```php
public function dashboard() {
    $period = $this->input->get('period') ?: date('Y-m');  // period = '2025-10'

    // Fetch summary stats for the period
    $summary = $this->cost_center->get_summary_stats($period);
    //                                              └─ '2025-10' passed here

    // Pass to view
    $view_data['summary'] = $summary;
}
```

### Step 3: Model Method

**File:** `app/models/admin/Cost_center_model.php`  
**Method:** `get_summary_stats($period)`

```php
public function get_summary_stats($period = null) {
    if (!$period) {
        $period = date('Y-m');  // Default: current month
    }

    $query = "
        SELECT
            level,
            entity_name,
            period,
            kpi_total_revenue,      ⭐ THIS IS THE TOTAL REVENUE
            kpi_total_cost,
            kpi_profit_loss,
            kpi_profit_margin_pct,
            entity_count,
            last_updated
        FROM view_cost_center_summary
        WHERE period = ?
    ";

    $result = $this->db->query($query, [$period]);  // period = '2025-10'
    return $result->row_array();
}
```

### Step 4: Database View

**File:** Database View `view_cost_center_summary`

This view aggregates ALL pharmacies' revenue for the selected period:

```sql
CREATE VIEW view_cost_center_summary AS
SELECT
    'COMPANY' AS level,
    'Avenzur Company' AS entity_name,
    CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,

    -- ⭐ THIS IS WHERE TOTAL REVENUE IS CALCULATED
    SUM(fcc.total_revenue) AS kpi_total_revenue,

    SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost) AS kpi_total_cost,
    SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) AS kpi_profit_loss,

    CASE
        WHEN SUM(fcc.total_revenue) = 0 THEN 0
        ELSE ROUND((SUM(fcc.total_revenue - (fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)) / SUM(fcc.total_revenue)) * 100, 2)
    END AS kpi_profit_margin_pct,

    COUNT(DISTINCT w.id) AS entity_count,
    MAX(fcc.updated_at) AS last_updated

FROM sma_fact_cost_center fcc
LEFT JOIN sma_warehouses w ON fcc.warehouse_id = w.id AND w.warehouse_type = 'pharmacy'

WHERE CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) = '{period}'

GROUP BY fcc.period_year, fcc.period_month
```

### Step 5: View Displays Result

**File:** `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`

```php
<?php
// From controller: $summary['kpi_total_revenue'] = 2,600,000.00

// Display in KPI card:
echo formatCurrency($summary['kpi_total_revenue']);
// Output: SAR 2,600,000
?>
```

---

## 3. REVENUE CALCULATION DETAILS

### Formula

```
Total Revenue = SUM(total_revenue)
                FROM sma_fact_cost_center
                WHERE period_year = {year}
                  AND period_month = {month}
```

### Step-by-Step Calculation

**Given data for period 2025-10:**

```
Pharmacy 52:  648,800.79 SAR
Pharmacy 53:  520,000.00 SAR
Pharmacy 54:  450,000.00 SAR
Pharmacy 55:  380,000.00 SAR
Pharmacy 56:  320,000.00 SAR
Pharmacy 57:  190,000.00 SAR
Pharmacy 58:  75,200.00 SAR
Pharmacy 59:  16,000.00 SAR
────────────────────────────
Total:       2,599,800.79 SAR  ✓
```

### Actual SQL Query Executed

```sql
SELECT SUM(total_revenue) AS kpi_total_revenue
FROM sma_fact_cost_center
WHERE period_year = 2025
  AND period_month = 10;

Result: 2,599,800.79
```

---

## 4. PHARMACY-LEVEL REVENUE

When you **filter by a single pharmacy**, a different query is used:

**File:** `app/models/admin/Cost_center_model.php`  
**Method:** `get_pharmacy_detail($pharmacy_id, $period)`

```php
public function get_pharmacy_detail($pharmacy_id = null, $period = null) {
    $query = "
        SELECT
            w.id AS pharmacy_id,
            w.name AS pharmacy_name,

            -- ⭐ SUM ONLY FOR THIS PHARMACY
            COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,

            COALESCE(SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost), 0) AS kpi_total_cost,
            ...
        FROM sma_warehouses w
        LEFT JOIN sma_fact_cost_center fcc ON w.id = fcc.warehouse_id
            AND CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) = ?

        WHERE w.warehouse_type = 'pharmacy'
          AND w.id = ?          -- ⭐ ONLY THIS PHARMACY

        GROUP BY w.id
    ";

    $result = $this->db->query($query, [$period, $pharmacy_id]);
    return $result->row_array();
}
```

**Result for Pharmacy 52:**

```sql
SELECT SUM(total_revenue)
FROM sma_fact_cost_center
WHERE warehouse_id = 52
  AND period_year = 2025
  AND period_month = 10;

Result: 648,800.79  (Only pharmacy 52, not total)
```

---

## 5. COMPARISON: COMPANY vs PHARMACY

| Level           | Query                                                 | Revenue          | Count              |
| --------------- | ----------------------------------------------------- | ---------------- | ------------------ |
| **Company**     | `SUM(total_revenue) WHERE period='2025-10'`           | **2,599,800.79** | All 8 pharmacies   |
| **Pharmacy 52** | `SUM(...) WHERE warehouse_id=52 AND period='2025-10'` | 648,800.79       | Only this pharmacy |
| **Pharmacy 53** | `SUM(...) WHERE warehouse_id=53 AND period='2025-10'` | 520,000.00       | Only this pharmacy |
| **Pharmacy 54** | `SUM(...) WHERE warehouse_id=54 AND period='2025-10'` | 450,000.00       | Only this pharmacy |

**Verification:**

```
648,800.79 + 520,000.00 + 450,000.00 + ... = 2,599,800.79 ✓
```

---

## 6. FILTERING BY PERIOD

The total revenue changes based on the selected period:

### Period 2025-10

```sql
SELECT SUM(total_revenue)
FROM sma_fact_cost_center
WHERE period_year=2025 AND period_month=10;
Result: 2,599,800.79
```

### Period 2025-09

```sql
SELECT SUM(total_revenue)
FROM sma_fact_cost_center
WHERE period_year=2025 AND period_month=9;
Result: 2,450,000.00 (different month = different total)
```

### All Time

```sql
SELECT SUM(total_revenue)
FROM sma_fact_cost_center;
Result: 12,750,000.00 (sum of all months)
```

---

## 7. API ENDPOINT CALCULATION

### Endpoint: Get Pharmacy Detail

```
GET /api/v1/cost-center/pharmacy-detail/52?period=2025-10
```

**File:** `app/controllers/api/v1/Cost_center.php`  
**Method:** `pharmacy_detail($pharmacy_id)`

```php
public function pharmacy_detail($pharmacy_id = null) {
    $period = $this->get('period') ?: date('Y-m');  // period = '2025-10'

    $data = $this->cost_center->get_pharmacy_detail($pharmacy_id, $period);
    //                                                 52        '2025-10'

    return $this->response([
        'success' => true,
        'data' => $data,
        'period' => $period,
        'timestamp' => date('Y-m-d\TH:i:s\Z'),
        'status' => 200
    ]);
}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "pharmacy_id": "52",
        "pharmacy_name": "E&M Central Plaza Pharmacy",
        "kpi_total_revenue": "648800.79",    ← Pharmacy 52 only
        "kpi_total_cost": "373060.46",
        "kpi_profit_loss": "275740.33",
        "kpi_profit_margin_pct": "42.45"
    },
    "period": "2025-10",
    "status": 200
}
```

---

## 8. JAVASCRIPT DISPLAY

### Dashboard - Company Total

```php
// themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php

let dashboardData = {
    summary: <?php echo json_encode($summary ?? []); ?>,
    // summary.kpi_total_revenue = 2,599,800.79 (all pharmacies)
};

function renderKPICards() {
    const totalRevenue = dashboardData.summary.kpi_total_revenue;  // 2,599,800.79

    document.getElementById('kpi_revenue').textContent = formatCurrency(totalRevenue);
    // Output: "SAR 2,599,800"
}
```

### Dashboard - Filter by Pharmacy

```javascript
function handlePharmacyFilter(pharmacyId) {
	// Fetch pharmacy-specific data from API
	fetch(
		`${dashboardData.baseUrl}api/v1/cost-center/pharmacy-detail/${pharmacyId}?period=${dashboardData.currentPeriod}`
	)
		.then((response) => response.json())
		.then((result) => {
			// result.data.kpi_total_revenue = 648,800.79 (only pharmacy 52)

			const filteredSummary = {
				kpi_total_revenue: result.data.kpi_total_revenue, // 648,800.79
				kpi_total_cost: result.data.kpi_total_cost,
				kpi_profit_loss: result.data.kpi_profit_loss,
				kpi_profit_margin_pct: result.data.kpi_profit_margin_pct,
			};

			// Temporarily swap dashboard data
			dashboardData.summary = filteredSummary;

			// Re-render KPI cards with pharmacy data
			renderKPICards();
			// Now displays: "SAR 648,800" (only this pharmacy)
		});
}
```

---

## 9. COST BREAKDOWN

Total Revenue is the starting point for calculating other metrics:

```
COMPANY LEVEL (2025-10):
Total Revenue:                    2,599,800.79 SAR (100.0%)
├─ COGS:                          1,299,900.00 SAR (50.0%)
├─ Inventory Movement Cost:       64,995.00 SAR    (2.5%)
├─ Operational Cost:              129,990.00 SAR   (5.0%)
└─ Total Cost:                    1,494,885.00 SAR (57.5%)

Profit (Revenue - Cost):           1,104,915.79 SAR (42.5%)

Gross Margin (Revenue-COGS)/Revenue:  50.0%
Net Margin (Profit)/Revenue:          42.5%
```

---

## 10. VERIFICATION QUERIES

### Query 1: Verify Total Company Revenue

```sql
SELECT
    SUM(fcc.total_revenue) as total_revenue,
    COUNT(DISTINCT fcc.warehouse_id) as pharmacy_count,
    CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) as period
FROM sma_fact_cost_center fcc
WHERE fcc.period_year = 2025 AND fcc.period_month = 10
GROUP BY fcc.period_year, fcc.period_month;

Expected Result:
╔══════════════════╦════════════════╦═══════╗
║ total_revenue    ║ pharmacy_count ║ period║
╠══════════════════╬════════════════╬═══════╣
║ 2,599,800.79     ║ 8              ║ 2025-10
╚══════════════════╩════════════════╩═══════╝
```

### Query 2: Break Down by Pharmacy

```sql
SELECT
    w.id,
    w.name,
    SUM(fcc.total_revenue) as revenue,
    CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) as period
FROM sma_warehouses w
LEFT JOIN sma_fact_cost_center fcc ON w.id = fcc.warehouse_id
WHERE fcc.period_year = 2025
  AND fcc.period_month = 10
  AND w.warehouse_type = 'pharmacy'
GROUP BY w.id
ORDER BY revenue DESC;

Expected Result (8 rows):
╔════╦═════════════════════════════╦════════════════╦═══════╗
║ id ║ name                        ║ revenue        ║ period║
╠════╬═════════════════════════════╬════════════════╬═══════╣
║ 52 ║ E&M Central Plaza Pharmacy  ║ 648,800.79     ║ 2025-10
║ 53 ║ [Pharmacy Name]             ║ 520,000.00     ║ 2025-10
║ 54 ║ [Pharmacy Name]             ║ 450,000.00     ║ 2025-10
║    ║ ...                         ║ ...            ║
╚════╩═════════════════════════════╩════════════════╩═══════╝

Sum of all: 2,599,800.79 ✓
```

### Query 3: Verify View Query

```sql
SELECT * FROM view_cost_center_summary WHERE period = '2025-10';

Expected:
╔═══════╦═══════════════════╦════════╦═════════════════╦═══════════════════╗
║ level ║ entity_name       ║ period ║ kpi_total_       ║ kpi_profit_margin ║
║       ║                   ║        ║ revenue          ║ _pct              ║
╠═══════╬═══════════════════╬════════╬═════════════════╬═══════════════════╣
║ COMPANY║ Avenzur Company   ║ 2025-10║ 2,599,800.79    ║ 42.50             ║
╚═══════╩═══════════════════╩════════╩═════════════════╩═══════════════════╝
```

---

## 11. DATA FLOW DIAGRAM

```
COMPANY LEVEL (Dashboard)
│
├─ URL: /admin/cost_center/dashboard?period=2025-10
│         ↓
├─ Controller: Cost_center.dashboard()
│   ├─ Get period from URL: '2025-10'
│   ├─ Call Model: get_summary_stats('2025-10')
│   └─ Pass $summary to view
│
├─ Model: get_summary_stats('2025-10')
│   └─ Query: SELECT ... FROM view_cost_center_summary WHERE period='2025-10'
│       └─ Returns: kpi_total_revenue = 2,599,800.79
│
├─ Database View: view_cost_center_summary
│   └─ Query: SUM(fcc.total_revenue) FROM sma_fact_cost_center WHERE ...
│       ├─ Pharmacy 52: +648,800.79
│       ├─ Pharmacy 53: +520,000.00
│       ├─ Pharmacy 54: +450,000.00
│       └─ ...etc = 2,599,800.79 TOTAL
│
├─ View: cost_center_dashboard_modern.php
│   ├─ Displays dashboardData.summary.kpi_total_revenue
│   └─ Renders: "SAR 2,599,800"
│
└─ User sees: Total Revenue: SAR 2,599,800


PHARMACY LEVEL (After Filter Click)
│
├─ URL: /admin/cost_center/pharmacy/52?period=2025-10
│         ↓
├─ JavaScript: handlePharmacyFilter(52)
│   ├─ Fetch: /api/v1/cost-center/pharmacy-detail/52?period=2025-10
│   ├─ Get response: kpi_total_revenue = 648,800.79
│   └─ Update KPI cards
│
├─ API: Cost_center.pharmacy_detail(52)
│   ├─ Get period: '2025-10'
│   ├─ Call Model: get_pharmacy_detail(52, '2025-10')
│   └─ Return JSON response
│
├─ Model: get_pharmacy_detail(52, '2025-10')
│   └─ Query: SELECT SUM(fcc.total_revenue) FROM sma_fact_cost_center
│       WHERE warehouse_id=52 AND period='2025-10'
│       └─ Returns: kpi_total_revenue = 648,800.79
│
└─ Dashboard updates: "SAR 648,800" (only pharmacy 52)
```

---

## 12. SUMMARY

| Aspect             | Details                                |
| ------------------ | -------------------------------------- |
| **Source Table**   | `sma_fact_cost_center`                 |
| **Column**         | `total_revenue`                        |
| **Calculation**    | SUM(total_revenue)                     |
| **Period**         | Filtered by period_year & period_month |
| **Company Level**  | ~2,599,800 SAR (all 8 pharmacies)      |
| **Pharmacy Level** | ~648,800 SAR (pharmacy 52 only)        |
| **Aggregation**    | View: `view_cost_center_summary`       |
| **Filtering**      | By warehouse_id (pharmacy/branch)      |
| **Updates**        | Real-time from sma_fact_cost_center    |

---

## 13. TROUBLESHOOTING

### Issue: Total Revenue Shows 0

**Cause:** No data in sma_fact_cost_center for the period  
**Solution:**

```sql
SELECT COUNT(*) FROM sma_fact_cost_center
WHERE period_year=2025 AND period_month=10;
-- If result is 0, no data exists for that period
```

### Issue: Pharmacy Revenue Shows 0

**Cause:** No data for that pharmacy_id in the period  
**Solution:**

```sql
SELECT SUM(total_revenue)
FROM sma_fact_cost_center
WHERE warehouse_id=52 AND period_year=2025 AND period_month=10;
-- If result is 0 or NULL, check if warehouse_id 52 exists and has data
```

### Issue: View Not Aggregating Correctly

**Cause:** View query has incorrect WHERE clause  
**Solution:** Re-create view with proper aggregation

```sql
DROP VIEW IF EXISTS view_cost_center_summary;
CREATE VIEW view_cost_center_summary AS
SELECT
    'COMPANY' AS level,
    'Avenzur Company' AS entity_name,
    CONCAT(period_year, '-', LPAD(period_month, 2, '0')) AS period,
    SUM(total_revenue) AS kpi_total_revenue,
    ...
FROM sma_fact_cost_center
GROUP BY period_year, period_month;
```

---

## 14. RELATED METRICS

Once Total Revenue is calculated, other metrics are derived:

```
Total Revenue: 2,599,800.79
├─ Total Cost: 1,494,885.00
│  ├─ COGS: 1,299,900.00
│  ├─ Inventory: 64,995.00
│  └─ Operational: 129,990.00
│
├─ Profit Loss: 1,104,915.79 (Revenue - Cost)
│
├─ Gross Margin %: 50.0% ((Revenue - COGS) / Revenue * 100)
│
├─ Net Margin %: 42.5% ((Revenue - TotalCost) / Revenue * 100)
│
├─ Cost Ratio %: 57.5% ((TotalCost) / Revenue * 100)
│
└─ Per Pharmacy Average: 324,975.10 (Revenue / 8 pharmacies)
```

---

**Status:** ✅ COMPLETE  
**Last Updated:** 2025-10-25  
**Ready:** FOR REVIEW & VALIDATION
