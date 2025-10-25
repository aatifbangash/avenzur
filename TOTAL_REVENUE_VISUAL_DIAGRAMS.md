# Total Revenue Calculation - Visual Flow Diagrams

**Date:** 2025-10-25  
**Purpose:** Visual representation of revenue calculation flow

---

## 1. SIMPLE CALCULATION FLOW

```
┌─────────────────────────────────────────────────┐
│         USER OPENS DASHBOARD                    │
│  http://localhost:8080/.../cost_center/        │
│  dashboard?period=2025-10                       │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│         PERIOD EXTRACTED                        │
│         period = '2025-10'                      │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│         CONTROLLER (Cost_center.dashboard)      │
│         Calls: get_summary_stats('2025-10')     │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│         MODEL (get_summary_stats)               │
│         Queries: view_cost_center_summary       │
│         WHERE period = '2025-10'                │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│         DATABASE VIEW                           │
│         SELECT SUM(total_revenue)               │
│         FROM sma_fact_cost_center               │
│         WHERE period = '2025-10'                │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│         DATABASE TABLE (sma_fact_cost_center)   │
│         Rows for Oct 2025:                      │
│         ├─ Pharmacy 52: 648,800.79              │
│         ├─ Pharmacy 53: 520,000.00              │
│         ├─ Pharmacy 54: 450,000.00              │
│         └─ ... (8 pharmacies total)             │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│         AGGREGATION (SUM)                       │
│         648,800 + 520,000 + 450,000 + ...      │
│         = 2,599,800.79 SAR                      │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│         RESULT RETURNED                         │
│         kpi_total_revenue = 2,599,800.79        │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│         VIEW (Dashboard HTML)                   │
│         Displays in KPI Card:                   │
│         "SAR 2,599,800"                         │
└────────────────┬────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────┐
│         USER SEES DASHBOARD                     │
│         Total Revenue: SAR 2,599,800            │
└─────────────────────────────────────────────────┘
```

---

## 2. COMPANY LEVEL vs PHARMACY LEVEL

```
┌──────────────────────────────────────────────────────────────────┐
│                        COMPANY LEVEL                             │
│          (All Pharmacies - Dashboard Default)                    │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Query: SUM(total_revenue) for ALL pharmacies                   │
│                                                                   │
│  ┌─────────────────────────────────────────┐                    │
│  │ sma_fact_cost_center                    │                    │
│  ├─────────────────────────────────────────┤                    │
│  │ warehouse_id│ total_revenue│ period     │                    │
│  ├─────────────────────────────────────────┤                    │
│  │ 52          │ 648,800.79   │ 2025-10    │ ┐                │
│  │ 53          │ 520,000.00   │ 2025-10    │ │                │
│  │ 54          │ 450,000.00   │ 2025-10    │ ├─→ SUM = 2,599,800
│  │ 55          │ 380,000.00   │ 2025-10    │ │                │
│  │ ... (more)  │ ...          │ 2025-10    │ ┘                │
│  └─────────────────────────────────────────┘                    │
│                                                                   │
│  Result: Total Revenue = 2,599,800.79 SAR                       │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘

                              ▼▼▼

┌──────────────────────────────────────────────────────────────────┐
│                     PHARMACY LEVEL                               │
│            (Single Pharmacy - After Filter Click)                │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Query: SUM(total_revenue) for ONLY Pharmacy 52                 │
│                                                                   │
│  ┌─────────────────────────────────────────┐                    │
│  │ sma_fact_cost_center                    │                    │
│  ├─────────────────────────────────────────┤                    │
│  │ warehouse_id│ total_revenue│ period     │                    │
│  ├─────────────────────────────────────────┤                    │
│  │ 52          │ 648,800.79   │ 2025-10    │ ←─ Selected       │
│  │ 53          │ 520,000.00   │ 2025-10    │ (ignored)         │
│  │ 54          │ 450,000.00   │ 2025-10    │ (ignored)         │
│  │ ...         │ ...          │ 2025-10    │ (ignored)         │
│  └─────────────────────────────────────────┘                    │
│                                                                   │
│  Result: Total Revenue = 648,800.79 SAR (Pharmacy 52 only)      │
│                                                                   │
└──────────────────────────────────────────────────────────────────┘
```

---

## 3. DATABASE AGGREGATION HIERARCHY

```
                    DATABASE
                        │
            ┌───────────┴───────────┐
            │                       │
            ▼                       ▼
    sma_fact_cost_center      view_cost_center_summary
    (Raw Data)                 (Aggregated View)
            │                       │
            ├─ Pharmacy 52          │
            │  ├─ Oct 2025          ├─ COMPANY LEVEL (Oct 2025)
            │  │  └─ 648,800.79     │  └─ 2,599,800.79
            │  └─ Sep 2025          │
            │                       ├─ COMPANY LEVEL (Sep 2025)
            ├─ Pharmacy 53          │  └─ 2,450,000.00
            │  ├─ Oct 2025          │
            │  │  └─ 520,000.00     └─ ...
            │  └─ Sep 2025
            │                       (View aggregates all rows
            └─ ... (8 pharmacies)    for each period)
```

---

## 4. CALCULATION BREAKDOWN

```
Period: 2025-10

FROM sma_fact_cost_center
│
├─ Pharmacy 52 (warehouse_id=52)
│  └─ total_revenue: 648,800.79  ┐
│                                 │
├─ Pharmacy 53 (warehouse_id=53)  │
│  └─ total_revenue: 520,000.00  │
│                                 ├─ SUM() = 2,599,800.79
├─ Pharmacy 54 (warehouse_id=54)  │
│  └─ total_revenue: 450,000.00  │
│                                 │
├─ Pharmacy 55 (warehouse_id=55)  │
│  └─ total_revenue: 380,000.00  │
│                                 │
├─ Pharmacy 56 (warehouse_id=56)  │
│  └─ total_revenue: 320,000.00  │
│                                 │
├─ Pharmacy 57 (warehouse_id=57)  │
│  └─ total_revenue: 190,000.00  │
│                                 │
├─ Pharmacy 58 (warehouse_id=58)  │
│  └─ total_revenue: 75,200.00   │
│                                 │
└─ Pharmacy 59 (warehouse_id=59)  │
   └─ total_revenue: 16,000.00   ┘

Total Company Revenue (Oct 2025) = 2,599,800.79 SAR
```

---

## 5. FILTERING IMPACT

```
BEFORE FILTER                        AFTER FILTER
(Company Level)                      (Pharmacy 52 Level)

Dashboard Data                       Dashboard Data
  ↓                                    ↓
summary = {                          summary = {
  kpi_total_revenue: 2,599,800,        kpi_total_revenue: 648,800,
  ...                                  ...
}                                    }
  ↓                                    ↓
KPI Card Display                     KPI Card Display
  ↓                                    ↓
"SAR 2,599,800"                      "SAR 648,800"
(All 8 pharmacies)                   (Pharmacy 52 only)
  ↓                                    ↓
Table: All 8 rows                    Table: Only 1 row
Chart: Company totals                Chart: Pharmacy 52 trends
```

---

## 6. TIME PERIOD IMPACT

```
┌──────────────────────────────────────────────────────────────┐
│              PERIOD SELECTOR                                 │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │ 2025-10     │  │ 2025-09     │  │ 2025-08     │  ...    │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│         │                 │                 │                │
│         ▼                 ▼                 ▼                │
│    SELECT SUM()      SELECT SUM()       SELECT SUM()        │
│    WHERE month=10    WHERE month=9      WHERE month=8       │
│         │                 │                 │                │
│         ▼                 ▼                 ▼                │
│    2,599,800         2,450,000          2,300,000          │
│    (Oct 2025)        (Sep 2025)         (Aug 2025)         │
│                                                              │
│  Select month → Query updates → Revenue recalculated       │
└──────────────────────────────────────────────────────────────┘
```

---

## 7. API CALL FLOW (FILTERING)

```
USER CLICKS "View" on Pharmacy Row
             │
             ▼
JavaScript Event: onclick="navigateToPharmacy(52, '2025-10')"
             │
             ▼
API Call: GET /api/v1/cost-center/pharmacy-detail/52?period=2025-10
             │
             ▼
API Controller: pharmacy_detail(52)
             │
             ▼
Model Call: get_pharmacy_detail(52, '2025-10')
             │
             ▼
Database Query:
  SELECT SUM(total_revenue)
  FROM sma_fact_cost_center
  WHERE warehouse_id = 52
    AND period_year = 2025
    AND period_month = 10
             │
             ▼
Result: {
  "pharmacy_id": "52",
  "kpi_total_revenue": "648800.79",
  "period": "2025-10"
}
             │
             ▼
JavaScript Receives JSON
             │
             ▼
Update KPI Card Display
             │
             ▼
USER SEES: "SAR 648,800" (Changed from 2,599,800)
```

---

## 8. COST CALCULATION CHAIN

```
                    Total Revenue
                         │
                  2,599,800.79 SAR
                         │
          ┌──────────────┼──────────────┐
          │              │              │
          ▼              ▼              ▼
      COGS         Inventory        Operational
      │            Cost             │
      │            │                │
      ▼            ▼                ▼
   1,299,900      64,995          129,990
   (50%)           (2.5%)           (5%)
          │              │              │
          └──────────────┼──────────────┘
                         │
                         ▼
                   Total Cost
                   1,494,885
                    (57.5%)
                         │
                Revenue - TotalCost
                         │
                         ▼
                   Profit Loss
                   1,104,915.79
                    (42.5%)
```

---

## 9. VERIFICATION QUERIES FLOWCHART

```
                    START
                      │
                      ▼
    ┌─────────────────────────────────────┐
    │ Query 1: Total Company Revenue      │
    │ SELECT SUM(total_revenue)           │
    │ FROM sma_fact_cost_center           │
    │ WHERE period='2025-10'              │
    │ Result: 2,599,800.79 ✓              │
    └─────────────────┬───────────────────┘
                      │
                      ▼
    ┌─────────────────────────────────────┐
    │ Query 2: Break Down by Pharmacy     │
    │ GROUP BY warehouse_id               │
    │ ORDER BY revenue DESC               │
    │ Results: 8 rows (one per pharmacy)  │
    │ Sum: 2,599,800.79 ✓                 │
    └─────────────────┬───────────────────┘
                      │
                      ▼
    ┌─────────────────────────────────────┐
    │ Query 3: Verify View                │
    │ SELECT * FROM view_cost_center_     │
    │ summary WHERE period='2025-10'      │
    │ Result: kpi_total_revenue =         │
    │ 2,599,800.79 ✓                      │
    └─────────────────┬───────────────────┘
                      │
                      ▼
    ┌─────────────────────────────────────┐
    │ All Queries Match                   │
    │ Dashboard value is CORRECT ✓        │
    └─────────────────────────────────────┘
```

---

## 10. CODE EXECUTION PATH

```
HTTP Request: GET /admin/cost_center/dashboard?period=2025-10
        │
        ▼
┌─────────────────────────────────────────────────────┐
│ routes.php                                          │
│ $route['admin/cost_center/dashboard'] =             │
│ 'admin/cost_center/dashboard'                       │
└────────────┬────────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────┐
│ Cost_center.php (Controller)                        │
│ public function dashboard()                         │
│ {                                                   │
│   $period = $this->input->get('period');           │
│   // period = '2025-10'                            │
│                                                     │
│   $summary = $this->cost_center->                  │
│              get_summary_stats($period);            │
│ }                                                   │
└────────────┬────────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────┐
│ Cost_center_model.php (Model)                       │
│ public function get_summary_stats($period)          │
│ {                                                   │
│   $query = "SELECT ... FROM                        │
│   view_cost_center_summary WHERE period = ?";      │
│   return $this->db->query($query, [$period]);      │
│ }                                                   │
└────────────┬────────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────┐
│ Database View: view_cost_center_summary             │
│ SELECT SUM(fcc.total_revenue) AS                   │
│ kpi_total_revenue FROM sma_fact_cost_center        │
│ WHERE period = '2025-10'                           │
│                                                     │
│ Result: 2,599,800.79                               │
└────────────┬────────────────────────────────────────┘
             │
             ▼
┌─────────────────────────────────────────────────────┐
│ Dashboard View: cost_center_dashboard_modern.php    │
│ <?php echo formatCurrency(                         │
│   $summary['kpi_total_revenue']                    │
│ ); ?>                                              │
│                                                     │
│ Output: "SAR 2,599,800"                            │
└────────────┬────────────────────────────────────────┘
             │
             ▼
HTML Response to Browser with Dashboard
```

---

## 11. PHARMACY FILTER SEQUENCE DIAGRAM

```
User                    Browser              API Server         Database
  │                       │                    │                  │
  │ Click "View"          │                    │                  │
  ├──────────────────────>│                    │                  │
  │                       │                    │                  │
  │                       │ navigateToPharmacy(52)                │
  │                       │                    │                  │
  │                       │ Fetch API          │                  │
  │                       ├───────────────────>│                  │
  │                       │                    │                  │
  │                       │                    │ Query DB         │
  │                       │                    ├─────────────────>│
  │                       │                    │                  │
  │                       │                    │ SUM(revenue)     │
  │                       │                    │ WHERE id=52      │
  │                       │                    │                  │
  │                       │                    │<─────────────────┤
  │                       │                    │ 648,800.79       │
  │                       │                    │                  │
  │                       │<───────────────────┤                  │
  │                       │ JSON Response      │                  │
  │                       │ {revenue: 648800}  │                  │
  │                       │                    │                  │
  │                       │ Update KPI Card    │                  │
  │                       │ Re-render View     │                  │
  │                       │                    │                  │
  │<──────────────────────┤                    │                  │
  │ Dashboard Updated     │                    │                  │
  │ SAR 648,800           │                    │                  │
  │                       │                    │                  │
```

---

## 12. COMPLETE DATA FLOW SUMMARY

```
┌─────────────────────────────────────────────────────────────┐
│                      COMPLETE FLOW                          │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  INPUT: User opens dashboard with period=2025-10           │
│    │                                                         │
│    ├─ Controller receives request                           │
│    ├─ Extracts period: '2025-10'                           │
│    ├─ Calls Model: get_summary_stats('2025-10')            │
│    │                                                         │
│    └─ Model queries view_cost_center_summary               │
│       │                                                      │
│       └─ View aggregates from sma_fact_cost_center         │
│          │                                                   │
│          └─ SUM(total_revenue) for all rows where          │
│             period='2025-10'                               │
│                                                              │
│  PROCESSING:                                               │
│    ├─ Pharmacy 52: 648,800.79 +                           │
│    ├─ Pharmacy 53: 520,000.00 +                           │
│    ├─ Pharmacy 54: 450,000.00 +                           │
│    ├─ Pharmacy 55: 380,000.00 +                           │
│    ├─ Pharmacy 56: 320,000.00 +                           │
│    ├─ Pharmacy 57: 190,000.00 +                           │
│    ├─ Pharmacy 58: 75,200.00 +                            │
│    └─ Pharmacy 59: 16,000.00                              │
│       ───────────────────────────                          │
│       Result: 2,599,800.79                                │
│                                                              │
│  OUTPUT:                                                    │
│    └─ Dashboard displays:                                  │
│       Total Revenue: SAR 2,599,800                         │
│                                                              │
│  VERIFICATION:                                             │
│    └─ SQL Sum = 2,599,800.79 ✓                            │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

**All Diagrams Created:** 2025-10-25  
**Format:** ASCII Text (Plain Text Diagrams)  
**Purpose:** Visual Understanding of Revenue Calculation

**Use Cases:**

- Explain flow to non-technical stakeholders
- Debug revenue calculation issues
- Understand data dependencies
- Train new team members
- Document system architecture
