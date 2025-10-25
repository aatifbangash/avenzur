# Pharmacy Filter Data Flow Documentation

**Date:** October 25, 2025  
**Status:** ✅ IMPLEMENTED

## Overview

When you select a pharmacy from the dashboard dropdown, the system now:
1. Fetches pharmacy-specific KPI data via API
2. Updates KPI cards with filtered data
3. Recalculates margins (gross & net) for selected pharmacy
4. Filters table to show only selected pharmacy
5. Updates all charts with pharmacy-specific data

## Data Sources

### Total Revenue Source

**Company-Wide (Default):**
- Source: `view_cost_center_summary` VIEW
- Field: `sum(fcc.total_revenue)` aggregated across ALL warehouses
- Query: Sums sales from all pharmacies for the selected period

**Pharmacy-Specific (When filtered):**
- Source: `sma_fact_cost_center` TABLE joined with `sma_warehouses`
- Field: Sum of `total_revenue` where `warehouse_id = selected_pharmacy_id`
- Period: Filtered by `YYYY-MM` format

## Architecture

### 1. Backend Flow

```
Dashboard Controller
└── GET /admin/cost_center/dashboard
    ├── Loads Company Summary (All Pharmacies)
    │   ├── get_summary_stats($period)
    │   └── get_profit_margins_both_types(null, $period)
    ├── Loads All Pharmacies
    │   └── get_pharmacies_with_health_scores($period)
    └── Passes to View as JSON

API Controller
└── GET /api/v1/cost-center/pharmacy-detail/{id}
    ├── Accepts pharmacy_id & period
    ├── Calls get_pharmacy_detail($pharmacy_id, $period)
    └── Returns single pharmacy KPIs
```

### 2. Database Query (Pharmacy Detail)

```sql
SELECT 
    w.id AS pharmacy_id,
    w.code AS pharmacy_code,
    w.name AS pharmacy_name,
    -- Revenue & Cost Breakdown
    SUM(fcc.total_revenue) AS kpi_total_revenue,
    SUM(fcc.total_cogs) AS kpi_cogs,
    SUM(fcc.inventory_movement_cost) AS kpi_inventory_movement,
    SUM(fcc.operational_cost) AS kpi_operational_cost,
    -- Calculated Fields
    (kpi_profit_loss) AS kpi_profit_loss,  -- Revenue - All Costs
    kpi_profit_margin_pct,  -- (Profit / Revenue) * 100
    gross_margin_pct,  -- ((Revenue - COGS) / Revenue) * 100
    net_margin_pct,  -- ((Revenue - All Costs) / Revenue) * 100
    branch_count
FROM sma_warehouses w
LEFT JOIN sma_fact_cost_center fcc 
    ON w.id = fcc.warehouse_id 
    AND period = ?
LEFT JOIN sma_warehouses db 
    ON db.warehouse_type = 'branch' AND db.parent_id = w.id
WHERE w.warehouse_type = 'pharmacy' AND w.id = ?
GROUP BY w.id
```

### 3. Frontend JavaScript Flow

```javascript
// User selects pharmacy from dropdown
handlePharmacyFilter(pharmacyId)
    ├── Filter tableData locally
    ├── Fetch /api/v1/cost-center/pharmacy-detail/{id}
    │   └── Returns pharmacy KPIs
    ├── Create filteredSummary object
    │   ├── kpi_total_revenue (revenue for this pharmacy only)
    │   ├── kpi_total_cost (all costs for this pharmacy)
    │   ├── kpi_profit_loss (profit for this pharmacy)
    │   └── kpi_profit_margin_pct (margin %)
    ├── Create filteredMargins object
    │   ├── gross_margin (before inventory & operational)
    │   └── net_margin (after all costs)
    ├── Swap dashboardData temporarily
    ├── Re-render KPI Cards
    ├── Re-render Charts
    ├── Restore dashboardData
    └── Render filtered Table

// When user clears filter
handlePharmacyFilter(null)
    ├── Reset tableData to all pharmacies
    ├── Re-render KPI Cards with company totals
    └── Re-render Table with all pharmacies
```

## Implementation Details

### Model Method: `get_pharmacy_detail()`

**File:** `app/models/admin/Cost_center_model.php`

**Purpose:** Fetch single pharmacy KPI data

**Parameters:**
- `$pharmacy_id` - Warehouse ID of pharmacy
- `$period` - YYYY-MM format

**Returns:**
```php
[
    'pharmacy_id' => 52,
    'pharmacy_code' => 'PHR-004',
    'pharmacy_name' => 'E&M Central Plaza Pharmacy',
    'kpi_total_revenue' => 648800.79,  // This pharmacy's revenue only
    'kpi_cogs' => 324400.40,
    'kpi_inventory_movement' => 16220.02,
    'kpi_operational_cost' => 32440.04,
    'kpi_total_cost' => 373060.46,
    'kpi_profit_loss' => 275740.33,
    'kpi_profit_margin_pct' => 42.45,  // This pharmacy's margin %
    'gross_margin_pct' => 50.00,
    'net_margin_pct' => 42.45,
    'branch_count' => 2,
    'last_updated' => '2025-10-25 08:29:54'
]
```

### API Endpoint: `pharmacy_detail_get()`

**File:** `app/controllers/api/v1/Cost_center.php`

**URL:** `GET /api/v1/cost-center/pharmacy-detail/{id}`

**Query Parameters:**
- `period` - YYYY-MM (optional, defaults to current month)

**Response:**
```json
{
    "success": true,
    "data": {
        "pharmacy_id": 52,
        "pharmacy_name": "E&M Central Plaza Pharmacy",
        "kpi_total_revenue": 648800.79,
        "kpi_profit_margin_pct": 42.45,
        "gross_margin_pct": 50.00,
        "net_margin_pct": 42.45,
        ...
    },
    "period": "2025-10",
    "timestamp": "2025-10-25T10:30:45Z"
}
```

## Key Calculations

### Revenue
- **Company Level:** `SUM(fcc.total_revenue)` across all pharmacy warehouses
- **Pharmacy Level:** `SUM(fcc.total_revenue)` where `warehouse_id = selected_pharmacy_id`
- **Per Period:** Filtered by `CONCAT(period_year, '-', LPAD(period_month, 2, '0')) = ?`

### Cost Breakdown

**Three Cost Components:**
1. **COGS** (Cost of Goods Sold) - `total_cogs`
2. **Inventory Movement Cost** - `inventory_movement_cost`
3. **Operational Cost** - `operational_cost`

**Total Cost Calculation:**
```
Total Cost = COGS + Inventory Movement + Operational Cost
```

### Profit Margin Calculation

**Gross Margin:**
```
Gross Margin % = ((Revenue - COGS) / Revenue) * 100
```

**Net Margin (Profit Margin):**
```
Net Margin % = ((Revenue - All Costs) / Revenue) * 100
           = ((Revenue - COGS - Inventory - Operational) / Revenue) * 100
```

**Example with Real Numbers:**
```
Revenue: 648,800.79
COGS: 324,400.40
Inventory: 16,220.02
Operational: 32,440.04
Total Cost: 373,060.46

Profit: 648,800.79 - 373,060.46 = 275,740.33

Gross Margin: ((648,800.79 - 324,400.40) / 648,800.79) * 100 = 50.00%
Net Margin: ((648,800.79 - 373,060.46) / 648,800.79) * 100 = 42.45%
```

## Data Hierarchy

```
Company Level (All Pharmacies)
├── Summary: Totals across all pharmacies
├── Margins: Company-wide average margins
├── Total Revenue: Sum of all pharmacy revenues
└── Pharmacies: 8 pharmacies
    ├── Pharmacy 1 (PHR-001)
    │   ├── Revenue: Individual pharmacy sales
    │   ├── Margins: This pharmacy's margins only
    │   └── Branches: 2 branches
    ├── Pharmacy 2 (PHR-002)
    │   └── ... (similar structure)
    └── ...
```

## Data Filtering in Browser (Client-Side)

When filtering is applied, the browser:

1. **Table Filtering:** Uses simple JavaScript filter
   ```javascript
   tableData = dashboardData.pharmacies.filter(p => p.pharmacy_id == pharmacyId)
   ```

2. **KPI Card Update:** Fetches fresh data from API and swaps `dashboardData.summary`

3. **Chart Recalculation:** Charts re-render with filtered pharmacy data

4. **Reset:** Clears filter and restores company-wide data

## Important Notes

⚠️ **Data Accuracy:**
- All numbers come from `sma_fact_cost_center` table
- Fact table is populated from `sma_sales_details` and cost transactions
- Each period's data is finalized and should not change (immutable fact)

⚠️ **Performance:**
- Company summary loads once (all pharmacies aggregated)
- Pharmacy detail is fetched on-demand via API
- No real-time recalculation; based on fact table snapshots

⚠️ **Missing Data:**
- If a pharmacy has no transactions in selected period, it shows zeros
- Health status calculated based on margin thresholds (see health.md)

## Testing URLs

**Local:**
```
# View dashboard
http://localhost/admin/cost_center/dashboard?period=2025-10

# Filter via dropdown (JavaScript handles this)

# Direct API test
http://localhost/api/v1/cost-center/pharmacy-detail/52?period=2025-10

# View pharmacy detail page
http://localhost/admin/cost_center/pharmacy/52?period=2025-10
```

**Production:** Replace `localhost` with production domain

## Related Files

- **Model:** `app/models/admin/Cost_center_model.php`
  - `get_summary_stats()` - Company totals
  - `get_pharmacies_with_health_scores()` - All pharmacies list
  - `get_pharmacy_detail()` - Single pharmacy KPIs

- **Controllers:**
  - `app/controllers/admin/Cost_center.php` - View rendering
  - `app/controllers/api/v1/Cost_center.php` - API endpoints

- **Views:**
  - `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`
    - `handlePharmacyFilter()` - Filter logic
    - `renderKPICards()` - KPI display
    - `renderTable()` - Table with filtered data

- **Database:**
  - Table: `sma_fact_cost_center` - Core data
  - View: `view_cost_center_pharmacy` - Pharmacy rollup (reference only)
  - View: `view_cost_center_summary` - Company summary
