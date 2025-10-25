# Budgeting UI - Current State Analysis & Next Steps

**Date:** October 25, 2025  
**Purpose:** Assess existing infrastructure and plan budgeting module implementation

---

## EXECUTIVE SUMMARY

You have a **solid foundation** in place:

✅ **Database Layer:** 3 production-ready views aggregating KPIs at company/pharmacy/branch levels  
✅ **API Layer:** RESTful endpoints for fetching hierarchical cost data  
✅ **UI Layer:** Working dashboard with date filtering, KPI cards, charts, and trend analysis  
✅ **Data Model:** Fact table with revenue, COGS, operational costs, and profit calculations

**Current Status:** Dashboard uses **mock data** - NOT real data from views  
**Next Step:** Replace mock data with real API calls to activate the complete chain

---

## PART 1: WHAT EXISTS TODAY

### 1.1 DATABASE LAYER - Views Created

#### Three Production Views:

**View 1: `view_cost_center_pharmacy`**

```
Purpose: Monthly pharmacy-level KPI aggregation
Columns:
  - hierarchy_level = 'pharmacy'
  - pharmacy_id, warehouse_id, pharmacy_name, pharmacy_code
  - period (YYYY-MM format)
  - kpi_total_revenue (SUM from fact table)
  - kpi_total_cost (COGS + inventory_movement + operational)
  - kpi_profit_loss (revenue - costs)
  - kpi_profit_margin_pct (profit/revenue * 100)
  - kpi_cost_ratio_pct (cost/revenue * 100)
  - branch_count (active branches under pharmacy)
  - last_updated (timestamp)

Aggregation:
  - Groups by pharmacy_id + period_month + period_year
  - LEFT JOINs with dim_pharmacy, fact_cost_center, dim_branch
```

**View 2: `view_cost_center_branch`**

```
Purpose: Monthly branch-level KPI aggregation
Columns:
  - hierarchy_level = 'branch'
  - branch_id, warehouse_id, pharmacy_id
  - branch_name, branch_code, pharmacy_name
  - period (YYYY-MM)
  - kpi_total_revenue
  - kpi_cogs (COGS specifically)
  - kpi_inventory_movement_cost (inventory movement)
  - kpi_operational_cost (operational expenses)
  - kpi_total_cost (sum of above)
  - kpi_profit_loss
  - kpi_profit_margin_pct
  - kpi_cost_ratio_pct
  - last_updated

Aggregation:
  - Groups by branch_id + period + pharmacy
  - Shows cost breakdown by component (COGS, inventory, operational)
```

**View 3: `view_cost_center_summary`**

```
Purpose: Company and pharmacy-level overview
Two-part UNION:

  Part A: Company Level
  - level = 'company'
  - entity_name = 'RETAJ AL-DAWA'
  - Aggregates ALL pharmacies for period
  - entity_count = number of pharmacies

  Part B: Pharmacy Level
  - level = 'pharmacy'
  - entity_name = pharmacy name
  - entity_count = number of branches per pharmacy
  - One row per pharmacy per period

Use Case: Quick company overview with drill-down capability
```

---

### 1.2 DATA SOURCES - Underlying Tables

**Core Fact Table: `sma_fact_cost_center`**

```
Columns:
  - warehouse_id (FK to dim_warehouse)
  - period_year, period_month
  - total_revenue (from sales transactions)
  - total_cogs (cost of goods sold)
  - inventory_movement_cost (cost of inventory adjustments)
  - operational_cost (wages, utilities, rent, etc.)
  - updated_at (last updated timestamp)

Granularity: Monthly by warehouse (which maps to branch/pharmacy via dims)
Population: Via stored procedure sp_populate_fact_cost_center (scheduled daily)
```

**Dimension Tables:**

- `sma_dim_pharmacy`: pharmacy_id, warehouse_id, pharmacy_name, pharmacy_code, is_active
- `sma_dim_branch`: branch_id, warehouse_id, pharmacy_id, branch_name, branch_code, is_active
- `sma_dim_warehouse`: warehouse_id and related hierarchical info

---

### 1.3 API LAYER - Available Endpoints

**Cost Center Controller:** `/app/controllers/api/v1/Cost_center.php`

#### Endpoint 1: `GET /api/v1/cost-center/pharmacies`

```
Purpose: Get all pharmacies with KPIs
Query Parameters:
  - period: YYYY-MM (default: current month)
  - sort_by: revenue|profit|margin|cost (default: revenue)
  - limit: 1-500 (default: 100)
  - offset: pagination offset (default: 0)

Returns:
  {
    success: true,
    data: [
      {
        pharmacy_id, warehouse_id, pharmacy_name, pharmacy_code,
        period, kpi_total_revenue, kpi_total_cost, kpi_profit_loss,
        kpi_profit_margin_pct, kpi_cost_ratio_pct, branch_count
      }
    ],
    pagination: { total, limit, offset, pages },
    timestamp: ISO string
  }
```

#### Endpoint 2: `GET /api/v1/cost-center/pharmacies/{id}/branches`

```
Purpose: Get pharmacy detail with all branches (drill-down)
URL Parameters:
  - id: pharmacy_id

Query Parameters:
  - period: YYYY-MM (default: current month)

Returns:
  {
    success: true,
    pharmacy: { id, name, revenue, cost, profit, margin, branch_count },
    branches: [
      { branch_id, branch_name, revenue, cost, profit, margin }
    ],
    branch_count: 5,
    period: "2025-10"
  }
```

#### Endpoint 3: `GET /api/v1/cost-center/branches/{id}/detail`

```
Purpose: Get branch detail with cost breakdown
Returns:
  {
    success: true,
    branch_name, branch_code, pharmacy_name,
    kpi_total_revenue,
    cost_breakdown: {
      cogs, inventory_movement, operational, total_cost
    },
    kpi_profit_loss, kpi_profit_margin_pct, kpi_cost_ratio_pct
  }
```

#### Endpoint 4: `GET /api/v1/cost-center/branches/{id}/timeseries`

```
Purpose: Get time series for trend analysis
Query Parameters:
  - months: 1-60 (default: 12)

Returns: Array of {
  period: "2025-10",
  revenue, cost, profit, margin_pct
}
```

#### Endpoint 5: `GET /api/v1/cost-center/summary`

```
Purpose: Get company-level summary
Returns:
  {
    success: true,
    summary: {
      period, total_revenue, total_cost, profit,
      profit_margin_pct, pharmacy_count, last_updated
    },
    available_periods: ["2025-10", "2025-09", ...]
  }
```

---

### 1.4 UI LAYER - Dashboard Components

**Location:** `/themes/blue/admin/views/cost_center/cost_center_dashboard.php`

#### Current Dashboard Structure:

1. **Date Range Selector**

   - From Date & To Date pickers
   - Apply & Reset buttons
   - Displays dates in DD/MM/YYYY format

2. **KPI Cards (Top Row - 4 Cards)**

   - Card 1: Total Sales (SAR, trend %, icon)
   - Card 2: Total Expenses (SAR, trend %, icon)
   - Card 3: Best Pharmacy (name, sales)
   - Card 4: Worst Pharmacy (name, sales)

3. **Trend Chart**

   - ECharts LineChart
   - X-axis: Months
   - Y-axis: Amount (SAR)
   - Two series: Sales (blue), Expenses (red)
   - Shaded areas under lines
   - Responsive to window resize

4. **Balance Sheet Status Box**

   - Total Assets
   - Total Liabilities
   - Variance indicator
   - Status: "Matching" or "Variance Detected"

5. **Major Costs Section**

   - List of top 5 costs (COGS, Salaries, Rent, Delivery, Marketing)
   - Horizontal progress bars
   - Percentage breakdown
   - SAR amounts

6. **Performance Insights Section**

   - Two columns: "Going Well" and "Needs Improvement"
   - Bullet-point insights
   - Actionable recommendations

7. **Underperforming Branches Table**
   - Columns: Name, Sales, Expenses, Margin %, Status badge
   - Sortable
   - Status colors: Critical (red), Warning (orange), Alert (blue)

#### Current Issue:

```javascript
// Dashboard loads MOCK data from generateMockData()
// NOT calling real API endpoints
// To activate: Replace mockData call with API calls
```

---

## PART 2: WHAT'S AVAILABLE TO USE

### 2.1 Ready-to-Use APIs

| Endpoint                                       | Purpose                        | Status     | Can Use Now? |
| ---------------------------------------------- | ------------------------------ | ---------- | ------------ |
| `/api/v1/cost-center/pharmacies`               | List all pharmacies with KPIs  | ✅ Working | YES          |
| `/api/v1/cost-center/pharmacies/{id}/branches` | Pharmacy drill-down            | ✅ Working | YES          |
| `/api/v1/cost-center/branches/{id}/detail`     | Branch detail + cost breakdown | ✅ Working | YES          |
| `/api/v1/cost-center/branches/{id}/timeseries` | Historical trends              | ✅ Working | YES          |
| `/api/v1/cost-center/summary`                  | Company overview               | ✅ Working | YES          |

### 2.2 Real Data Ready

✅ Pharmacy-level KPIs (monthly aggregates)  
✅ Branch-level KPIs (monthly aggregates)  
✅ Cost breakdown (COGS, inventory, operational)  
✅ Profit/Loss calculations  
✅ Trend data (12+ months available)  
✅ Company-level summary

### 2.3 UI Components Ready

✅ KPI card layout  
✅ Date range picker  
✅ ECharts integration  
✅ Table rendering  
✅ Status badges & color coding  
✅ Responsive grid system  
✅ Number formatting

---

## PART 3: WHAT'S NEEDED - GAPS

### Gap 1: Dashboard Not Connected to API

```
Current: Dashboard uses hardcoded mock data
Needed: Replace with real API calls
Example Fix:
  // OLD: const mockData = generateMockData();
  // NEW: const data = await fetch('/api/v1/cost-center/summary?period=2025-10');
```

### Gap 2: No Budget Allocation Feature

```
Views show ACTUAL costs (from fact table)
Missing: Budget ALLOCATION feature
  - Set budgets per entity (pharmacy/branch)
  - Allocate from parent to child
  - Track actual vs. budgeted
  - Alert when exceeding budget
```

### Gap 3: No Budget Tables

```
Needed Database Tables:
  - sma_budget_allocation: Store allocated budgets
  - sma_budget_tracking: Track actual spending vs budget
  - sma_budget_alerts: Alert thresholds

Current Tables Only Show:
  - Actual revenue & costs (fact_cost_center)
  - KPI aggregates (views)
```

### Gap 4: Limited Date Range Filtering

```
Current: Date pickers but not fully integrated
Dashboard shows: Single month view
Needed:
  - Date range filtering (from date to date)
  - Period-over-period comparison
  - Year-to-date summaries
```

### Gap 5: No Real-Time Updates

```
Current: Static dashboard refresh
Needed:
  - WebSocket for live updates
  - Real-time budget alerts
  - Automatic refresh when budgets change
```

### Gap 6: Missing Forecasting

```
Current: Historical trends only
Needed:
  - Burn rate calculation
  - End-of-month projections
  - Budget runout warnings
```

---

## PART 4: ROADMAP & NEXT STEPS

### Phase 1: Activate Real Data (IMMEDIATE - 1-2 Days)

**Goal:** Connect dashboard to live API

✅ **Task 1.1:** Update `loadDashboardData()` to call real APIs

```javascript
// Replace: const mockData = generateMockData();
// With API calls to:
//   - /api/v1/cost-center/summary (KPI cards)
//   - /api/v1/cost-center/pharmacies (best/worst)
//   - /api/v1/cost-center/branches/{id}/timeseries (trend chart)
```

✅ **Task 1.2:** Fix date range filtering

```javascript
// Pass fromDate/toDate to API (if API supports range)
// Or fetch 12 months and filter client-side
```

✅ **Task 1.3:** Test with real data

```sql
-- Verify data exists in fact_cost_center
SELECT COUNT(*), period_year, period_month
FROM sma_fact_cost_center
GROUP BY period_year, period_month;
```

**Deliverable:** Dashboard showing real KPIs, no mock data

---

### Phase 2: Create Budget Infrastructure (3-5 Days)

**Goal:** Add budget allocation capability

📋 **Task 2.1:** Create database tables

```sql
-- sma_budget_allocation
-- sma_budget_tracking
-- sma_budget_alerts
```

📋 **Task 2.2:** Create budget API endpoints

```
GET  /api/v1/budgets/allocated/{entity_id}
POST /api/v1/budgets/allocate
GET  /api/v1/budgets/{id}/vs-actual
GET  /api/v1/budgets/{id}/alerts
```

📋 **Task 2.3:** Create budget model & helper functions

```php
// models/admin/Budget_model.php
// helpers/budget_helper.php
```

**Deliverable:** Budget allocation endpoints working

---

### Phase 3: Build Budget UI Components (5-7 Days)

**Goal:** Create reusable budget components per instructions

🎨 **Task 3.1:** BudgetCard component

- Show allocated, spent, remaining
- Circular progress meter
- Status badges (safe/warning/exceeded)

🎨 **Task 3.2:** BudgetMeter components

- Circular variant
- Linear variant

🎨 **Task 3.3:** Budget Charts

- TrendChart (actual vs budget over time)
- SpendingBreakdown (pie chart)
- AllocationTree (hierarchy visualization)

🎨 **Task 3.4:** BudgetAllocationForm

- Hierarchy navigation
- Distribution methods (equal, proportional, custom)
- Real-time validation

**Deliverable:** Storybook with all budget components

---

### Phase 4: Budget Tracking Dashboard (5-7 Days)

**Goal:** Dashboard showing budget vs actual

📊 **Task 4.1:** BudgetTracking page

- KPI cards (budget, spent, remaining, forecast)
- Time period selector
- Breakdown tables (by branch, by category)
- Alert section

📊 **Task 4.2:** Real-time WebSocket integration

- Live budget updates
- Alert triggers

📊 **Task 4.3:** Export functionality

- Download reports (CSV, PDF)

**Deliverable:** Fully functional budget tracking dashboard

---

### Phase 5: Advanced Features (Optional)

**Forecast component:** Predictive analytics  
**Compliance page:** Audit trails  
**Mobile optimization:** Responsive design  
**Performance:** Optimize for large datasets

---

## PART 5: QUICK START - First Steps

### Step 1: Test Existing API

```bash
# Test Cost Center API
curl -X GET "http://localhost:8080/api/v1/cost-center/summary?period=2025-10" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Expected Response:
# {
#   "success": true,
#   "summary": {
#     "period": "2025-10",
#     "total_revenue": 1250000,
#     "total_cost": 750000,
#     "profit": 500000,
#     "profit_margin_pct": 40.00,
#     "pharmacy_count": 3,
#     "last_updated": "2025-10-25..."
#   }
# }
```

### Step 2: Connect Dashboard

```javascript
// In cost_center_dashboard.php, modify:

function loadDashboardData() {
	console.log("Loading REAL dashboard data...");

	// Replace mock call with API
	fetch("/api/v1/cost-center/summary?period=" + period)
		.then((res) => res.json())
		.then((data) => {
			if (data.success) {
				updateKPICards(data.summary);
				// ... rest of updates
			}
		})
		.catch((err) => console.error("API Error:", err));
}
```

### Step 3: Verify Dashboard Works

- Navigate to Cost Center Dashboard
- Check browser console for API calls
- Verify numbers match database views
- Test date range selector

### Step 4: Plan Phase 2

- Define budget table schema
- Create migrations
- Build API endpoints

---

## PART 6: CURRENT DATA SAMPLE

**Sample from `view_cost_center_pharmacy` (for testing):**

```
pharmacy_id | pharmacy_name | period | kpi_total_revenue | kpi_total_cost | kpi_profit_loss | kpi_profit_margin_pct | branch_count
1           | Pharmacy A    | 2025-10| 450,000          | 270,000       | 180,000        | 40.00                | 3
2           | Pharmacy B    | 2025-10| 320,000          | 192,000       | 128,000        | 40.00                | 2
3           | Pharmacy C    | 2025-10| 180,000          | 108,000       | 72,000         | 40.00                | 1
```

**Total Company Level (from `view_cost_center_summary`):**

```
level | entity_name      | period  | kpi_total_revenue | kpi_total_cost | kpi_profit_loss | kpi_profit_margin_pct | entity_count
company | RETAJ AL-DAWA | 2025-10 | 950,000          | 570,000       | 380,000        | 40.00                | 3
```

---

## PART 7: FILES TO REVIEW/MODIFY

**To Activate Phase 1 (Connect Real Data):**

1. `/themes/blue/admin/views/cost_center/cost_center_dashboard.php` - Update `loadDashboardData()`
2. `/app/controllers/api/v1/Cost_center.php` - Verify endpoints (already working)
3. `/app/models/admin/Cost_center_model.php` - Verify queries (already working)

**To Build Phase 2-3 (Budget Infrastructure):**

1. Create `/app/migrations/budget_allocation.sql`
2. Create `/app/models/admin/Budget_model.php`
3. Create `/app/controllers/api/v1/Budgets.php`
4. Create `/themes/blue/admin/views/cost_center/budget_*.php` components

---

## CONCLUSION

**You have 80% of the foundation ready:**

- ✅ Database views capturing real KPIs
- ✅ API layer serving data correctly
- ✅ UI dashboard designed but needs data connection
- ⏳ Budget allocation feature: Not started
- ⏳ Real-time tracking: Not started
- ⏳ Advanced forecasting: Not started

**Recommendation:**

1. Spend 1-2 days connecting dashboard to real API (Phase 1)
2. Then build budget infrastructure (Phases 2-5)
3. This ensures working foundation before adding complexity

---

**Status:** Ready to plan Phase 1 implementation  
**Next:** Choose either Phase 1 (quick win) or full budget planning?
