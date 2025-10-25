# Data Flow Diagram - Pharmacy Filter

## Complete System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER INTERFACE                           │
│                  (Browser / Dashboard)                          │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ├──────────────────────────┐
                       │                          │
                       ▼                          ▼
            ┌────────────────────┐    ┌──────────────────────┐
            │  Initial Load      │    │  Pharmacy Selection  │
            │ GET /dashboard     │    │ (Dropdown Change)    │
            └────────┬───────────┘    └──────────┬───────────┘
                     │                           │
                     ▼                           ▼
    ╔═══════════════════════════╗   ╔══════════════════════════════╗
    ║  CONTROLLER - Dashboard   ║   ║  VIEW - JavaScript Handler   ║
    ║  Cost_center::dashboard() ║   ║  handlePharmacyFilter()      ║
    ╚═════════════┬─────────────╝   ╚═════────────┬────────────────╝
                  │                               │
                  │ Calls:                        │ Calls:
                  │ • get_summary_stats()         │ fetch(/api/v1/
                  │ • get_pharmacies_with_...()  │   cost-center/
                  │ • get_profit_margins_both..()│   pharmacy-detail/ID)
                  │ • get_pharmacy_trends()      │
                  │                               │
                  ▼                               ▼
    ╔═══════════════════════════╗   ╔══════════════════════════════╗
    ║   MODEL LAYER             ║   ║   API CONTROLLER             ║
    ║ Cost_center_model.php     ║   ║ api/v1/Cost_center.php      ║
    ║                           ║   ║                              ║
    ║ • get_summary_stats()     ║   ║ pharmacy_detail_get($id)    ║
    ║ • get_pharmacies_with...()║   ║   └─► Calls model method    ║
    ║ • get_profit_margins...() ║   ║                              ║
    ║ • get_pharmacy_detail()   ║   ║ ▼ Returns JSON              ║
    ║ • get_pharmacy_trends()   ║   ║ {                            ║
    ║ • get_branches_with...()  ║   ║   pharmacy_id: 52,           ║
    ╚════────────┬──────────────╝   ║   revenue: 648800.79,        ║
                 │                   ║   profit_margin: 42.45,     ║
                 │                   ║   ...                        ║
                 │                   ║ }                            ║
                 ▼                   ▼
    ┌────────────────────────────────────────────────────────────┐
    │           DATABASE LAYER                                   │
    │  MySQL retaj_aldawa                                        │
    └───┬────────────────────────────────────────────────────────┘
        │
        ├─────────────────────┬────────────────────────────────┐
        │                     │                                │
        ▼                     ▼                                ▼
    ┌──────────────────┐  ┌───────────────────┐  ┌─────────────────────┐
    │ sma_fact_cost_   │  │ sma_warehouses    │  │ sma_dim_pharmacy    │
    │ center           │  │                   │  │ (reference only)    │
    │                  │  │ id (warehouse_id) │  │                     │
    │ warehouse_id ──┐ │  │ name              │  │ pharmacy_id         │
    │ total_revenue  ├─┼─→│ warehouse_type    │  │ warehouse_id (FK)   │
    │ total_cogs     │ │  │ parent_id         │  │ pharmacy_name       │
    │ inventory_..   │ │  │                   │  │                     │
    │ operational_.. │ │  │ Types:            │  └─────────────────────┘
    │ period_year    │ │  │ • warehouse       │
    │ period_month   │ │  │ • pharmacy (8)    │
    └──────────────┬─┘ │  │ • branch (9)      │
                  │    │  └───────────────────┘
                  │    │
                  │    └──────────┬──────────┐
                  │               │          │
                  ▼               ▼          ▼
        ╔══════════════════╗  ┌────────┐  ┌────────┐
        ║  FACT TABLE      ║  │ Views  │  │ (Other │
        ║  Data Example:   ║  │        │  │ tables)│
        ║                  ║  │(refs   │  │        │
        ║ warehouse_id: 52 ║  │only)   │  │        │
        ║ revenue: 648,800 ║  │        │  │        │
        ║ cogs: 324,400    ║  │        │  │        │
        ║ inventory: 16,220║  │        │  │        │
        ║ operational:32440║  │        │  │        │
        ║ period: 2025-10  ║  │        │  │        │
        ╚══════════════════╝  └────────┘  └────────┘
```

## Data Query Flow for Pharmacy Filter

```
┌─ USER SELECTS PHARMACY ─┐
│  Pharmacy ID: 52         │
└──────────┬──────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│  JavaScript Handler                  │
│  handlePharmacyFilter(52)            │
└──────────┬───────────────────────────┘
           │
           ├─ Filter table data (client-side)
           │  tableData = pharmacies.filter(p => p.id == 52)
           │
           └─ Fetch pharmacy detail (server-side)
              fetch('/api/v1/cost-center/pharmacy-detail/52')
              │
              ▼
┌──────────────────────────────────────┐
│  API: pharmacy_detail_get(52)        │
│  GET /api/v1/cost-center/...        │
└──────────┬───────────────────────────┘
           │
           ├─ Get period from query params
           │  period = 2025-10
           │
           └─ Call Model Method
              └─ get_pharmacy_detail(52, '2025-10')
                 │
                 ▼
         ┌───────────────────────────┐
         │  SQL Query Executed       │
         │                           │
         │  SELECT                   │
         │    w.id,                  │
         │    w.name,                │
         │    SUM(fcc.total_revenue) │
         │    SUM(fcc.total_cogs)    │
         │    SUM(fcc.inventory...)  │
         │    SUM(fcc.operational..) │
         │    ... more calcs ...     │
         │  FROM sma_fact_cost_center│
         │  LEFT JOIN sma_warehouses │
         │  WHERE                    │
         │    w.id = 52              │
         │    w.warehouse_type='phm' │
         │    period_year=2025       │
         │    period_month=10        │
         │  GROUP BY w.id            │
         └────┬────────────────────────┘
              │
              ▼
        ┌─────────────────┐
        │ Database Returns│
        │                 │
        │ pharmacy_id: 52 │
        │ revenue: 648800 │
        │ margin%: 42.45  │
        │ ... more fields │
        └────┬────────────┘
             │
             ▼
    ╔═════════════════════════╗
    ║ API returns JSON        ║
    ║ {                       ║
    ║   success: true,        ║
    ║   data: {               ║
    ║     pharmacy_id: 52,    ║
    ║     pharmacy_name: ..., ║
    ║     kpi_total_revenue:..║
    ║     kpi_profit_loss:... ║
    ║     kpi_profit_margin..║
    ║     ...                 ║
    ║   }                     ║
    ║ }                       ║
    ╚═════════┬───────────────╝
              │
              ▼
    ┌──────────────────────┐
    │ Browser receives     │
    │ JSON response        │
    └──────────┬───────────┘
               │
               ├─ Extract pharmacy data
               │
               ├─ Create filteredSummary object
               │  {
               │    kpi_total_revenue: 648800,
               │    kpi_total_cost: 373060,
               │    kpi_profit_loss: 275740,
               │    ...
               │  }
               │
               ├─ Create filteredMargins object
               │  {
               │    gross_margin: 50.00,
               │    net_margin: 42.45,
               │    ...
               │  }
               │
               └─ Update Dashboard UI
                  │
                  ├─ renderKPICards()
                  │  └─ Show pharmacy revenue
                  │     Show pharmacy margin %
                  │
                  ├─ renderCharts()
                  │  └─ Show pharmacy trends
                  │     Show pharmacy breakdown
                  │
                  └─ renderTable()
                     └─ Filter to only this pharmacy
```

## Revenue Calculation - Company vs Pharmacy

```
┌─────────────────────────────────────────────────────────────────┐
│                    COMPANY LEVEL (Default)                      │
└─────────────────────────────────────────────────────────────────┘

  SELECT SUM(total_revenue) FROM sma_fact_cost_center
  WHERE period_year=2025 AND period_month=10
  
  Pharmacy 1: 648,800.79
  Pharmacy 2: 520,640.63
  Pharmacy 3: 432,533.86
  Pharmacy 4: 324,400.40
  Pharmacy 5: 270,333.66
  Pharmacy 6: 216,266.93
  Pharmacy 7: 162,200.25
  Pharmacy 8: 108,133.52
  ─────────────────────────
  TOTAL:   2,683,309.04 ← Shown in KPI Card "Total Revenue"


┌─────────────────────────────────────────────────────────────────┐
│               PHARMACY LEVEL (When Filtered to ID=52)           │
└─────────────────────────────────────────────────────────────────┘

  SELECT SUM(total_revenue) FROM sma_fact_cost_center
  WHERE warehouse_id=52 AND period_year=2025 AND period_month=10
  
  Only Pharmacy 52 (E&M Central): 648,800.79
  ─────────────────────────────────────────
  TOTAL: 648,800.79 ← Updated in KPI Card when pharmacy selected
  
  ∴ Revenue decreased from 2.6M to 648K
  ✓ Shows filtering is working
  ✓ Data is pharmacy-specific, not company-wide
```

## Cost Breakdown Calculation

```
┌─────────────────────────────────────────┐
│  Pharmacy 52 (E&M Central Plaza)        │
│  Period: 2025-10                        │
└─────────────────────────────────────────┘

Total Revenue:                  648,800.79
                                    ▲
                           ┌────────┴────────┐
                           │                 │
                    From Pharmacy  From Multi-Unit
                    Sales Trans    Transactions


Cost Component Breakdown:
├─ COGS (Cost of Goods Sold)
│  Amount: 324,400.40
│  % of Revenue: 50.00%
│  Description: Direct cost to acquire inventory sold
│
├─ Inventory Movement Cost
│  Amount: 16,220.02
│  % of Revenue: 2.50%
│  Description: Cost to store, transfer, handle inventory
│
└─ Operational Cost
   Amount: 32,440.04
   % of Revenue: 5.00%
   Description: Rent, utilities, staff, admin, etc.

Total Cost: 373,060.46 (57.50% of revenue)

Profit Calculation:
  Revenue - Total Cost = Profit
  648,800.79 - 373,060.46 = 275,740.33

Margin %:
  (Profit / Revenue) × 100 = Margin %
  (275,740.33 / 648,800.79) × 100 = 42.45%
  
  ✓ This is the "Net Profit Margin" displayed
  ✓ Recalculates for each pharmacy
  ✓ Based on actual transaction data
```

## Pharmacy Hierarchy Visualization

```
                           ┌─ COMPANY LEVEL ─┐
                           │ (All Pharmacies)│
                           │ Total Revenue:  │
                           │  2,683,309.04   │
                           │ Margin: 42.30%  │
                           └────────┬────────┘
                                    │
                 ┌──────────────────┼──────────────────┐
                 │                  │                  │
                 ▼                  ▼                  ▼
           ┌──────────┐      ┌──────────┐      ┌──────────┐
           │Pharmacy 1│      │Pharmacy 2│      │Pharmacy 3│
           │PHR-001   │      │PHR-002   │      │PHR-003   │
           │Rev:648K  │      │Rev:520K  │      │Rev:432K  │
           │Margin:42%│      │Margin:41%│      │Margin:43%│
           └────┬─────┘      └────┬─────┘      └────┬─────┘
                │                 │                 │
          ┌─────┴─────┐     ┌─────┴─────┐     ┌─────┴─────┐
          │            │     │            │     │            │
          ▼            ▼     ▼            ▼     ▼            ▼
      ┌──────┐     ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐
      │Br-001│     │Br-002│ │Br-003│ │Br-004│ │Br-005│ │Br-006│
      │Branch│     │Branch│ │Branch│ │Branch│ │Branch│ │Branch│
      └──────┘     └──────┘ └──────┘ └──────┘ └──────┘ └──────┘
      
  (4 more pharmacies + branches below)

KEY RELATIONSHIP:
  Parent Pharmacy ──one:many──► Child Branches
  Company ──one:many──► Pharmacies
  
  When you select a pharmacy:
  • Revenue shows ONLY that pharmacy
  • Branches show ONLY that pharmacy's branches
  • Costs are ONLY for that pharmacy
  • NOT including child branches in revenue
```

---

## Summary

✅ **Revenue data comes from:** `sma_fact_cost_center` table  
✅ **Filtered by:** `warehouse_id = selected_pharmacy_id` and `period`  
✅ **Company total is:** SUM across all pharmacy warehouse IDs  
✅ **Pharmacy total is:** SUM for specific warehouse_id only  
✅ **Costs are tracked separately:** COGS, Inventory, Operational  
✅ **Margins recalculate:** Based on pharmacy-specific revenue & costs  
✅ **Hierarchy preserved:** Company → Pharmacies → Branches
