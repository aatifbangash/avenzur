# Cost Center Module - Implementation Guide for GitHub Autopilot

## PROJECT OVERVIEW

**Project Name:** Pharmacy Cost Center Dashboard  
**Objective:** Build a hierarchical cost center tracking system (Pharmacy Group → Pharmacy → Branch)  
**Timeline:** 1 day implementation  
**Architecture:** Database + ETL + BI Dashboard with drill-down capability

---

## HIERARCHY STRUCTURE

```
Pharmacy Group (Company)
    ├── Pharmacy A
    │   ├── Branch 001
    │   ├── Branch 002
    │   └── Branch 003
    ├── Pharmacy B
    │   ├── Branch 004
    │   └── Branch 005
    └── Pharmacy C
        └── Branch 006
```

**Master Table:** `sma_warehouse` (contains all hierarchy levels)

---

## DATA SOURCES

| Table                    | Purpose                      | Key Fields                                                                        |
| ------------------------ | ---------------------------- | --------------------------------------------------------------------------------- |
| `sma_sale`               | Revenue transactions         | warehouse_id, sale_date, total, net_amount                                        |
| `sma_sale_items`         | Line items (optional detail) | sale_id, item_id, quantity, unit_price                                            |
| `sma_purchases`          | Purchase/Cost transactions   | warehouse_id, purchase_date, total_cost, unit_cost                                |
| `sma_inventory_movement` | Inter-warehouse transfers    | from_warehouse_id, to_warehouse_id, transaction_date, movement_cost               |
| `sma_warehouse`          | Hierarchy master             | warehouse_id, warehouse_name, warehouse_code, warehouse_type, parent_warehouse_id |

---

## IMPLEMENTATION STEPS

### STEP 1: Database Schema - Dimension Tables (30 mins)

**File to Create:** `database/migrations/001_create_dimensions.sql`

**Requirements:**

- Create `dim_pharmacy` table from `sma_warehouse` (top-level pharmacies)
- Create `dim_branch` table from `sma_warehouse` (branches with parent pharmacy reference)
- Create `dim_date` table for time-based queries
- Add indexes on warehouse_id and parent_warehouse_id

**Constraints:**

- Must handle NULL values in parent_warehouse_id (for pharmacy group level)
- warehouse_type must be properly categorized
- Dates must be in ISO format (YYYY-MM-DD)

**Success Criteria:**

- dim_pharmacy has ≥ expected pharmacy count
- dim_branch has ≥ expected branch count
- All branch records have parent pharmacy_id
- No NULL warehouse_names

---

### STEP 2: Database Schema - Fact Table (1 hour)

**File to Create:** `database/migrations/002_create_fact_table.sql`

**Requirements:**

- Create `fact_cost_center` denormalized table
- Join data from `sma_sale`, `sma_purchases`, `sma_inventory_movement`, `sma_warehouse`
- Aggregate by warehouse_id and transaction_date
- Calculate four cost components:
  - `total_revenue`: SUM of sale totals per warehouse/day
  - `total_cogs`: SUM of purchase costs per warehouse/day
  - `inventory_movement_cost`: SUM of transfer costs per warehouse/day
  - `operational_cost`: Additional costs from purchases table (shipping, handling)

**Schema:**

```
warehouse_id (PK)
warehouse_name
warehouse_type (pharmacy_group/pharmacy/branch)
parent_warehouse_id
transaction_date (PK)
total_revenue (DECIMAL 18,2)
total_cogs (DECIMAL 18,2)
inventory_movement_cost (DECIMAL 18,2)
operational_cost (DECIMAL 18,2)
```

**Constraints:**

- FULL OUTER JOIN to capture all three data sources
- Handle date mismatches gracefully (different timestamps)
- Aggregate at daily level for flexibility
- NULL values should default to 0

**Success Criteria:**

- Fact table has ≥ 30 days of data
- No NULL revenue/cost values (must be 0)
- All warehouses represented
- Total revenue ≈ sum of daily sales in sma_sale

---

### STEP 3: Database Schema - KPI View (20 mins)

**File to Create:** `database/migrations/003_create_kpi_views.sql`

**Requirements:**

- Create `view_cost_center_pharmacy` - KPIs aggregated at Pharmacy level (monthly)
- Create `view_cost_center_branch` - KPIs aggregated at Branch level (monthly)
- Calculate 5 KPIs in each view:
  1. `kpi_total_revenue`: SUM(total_revenue)
  2. `kpi_total_cost`: SUM(total_cogs + inventory_movement_cost + operational_cost)
  3. `kpi_profit_loss`: kpi_total_revenue - kpi_total_cost
  4. `kpi_profit_margin_pct`: (kpi_profit_loss / kpi_total_revenue) × 100
  5. `kpi_cost_ratio_pct`: (kpi_total_cost / kpi_total_revenue) × 100

**Schema for view_cost_center_pharmacy:**

```
warehouse_id
warehouse_name
warehouse_code
warehouse_type (always = 'pharmacy')
period (YYYY-MM format)
kpi_total_revenue
kpi_total_cost
kpi_profit_loss
kpi_profit_margin_pct
kpi_cost_ratio_pct
branch_count (number of branches)
```

**Schema for view_cost_center_branch:**

```
warehouse_id
warehouse_name
warehouse_code
warehouse_type (always = 'branch')
pharmacy_id
pharmacy_name
period (YYYY-MM format)
kpi_total_revenue
kpi_total_cost
kpi_profit_loss
kpi_profit_margin_pct
kpi_cost_ratio_pct
```

**Constraints:**

- Profit margin must handle division by zero (use NULLIF)
- Round percentages to 2 decimal places
- Period must be sortable (YYYY-MM format)
- Include branch_count in pharmacy view for context

**Success Criteria:**

- Both views return data for all pharmacies and branches
- No NULL values in KPI columns (all calculations complete)
- Profit margin % between -100 and 100
- Cost ratio % between 0 and 500

---

### STEP 4: ETL/Data Pipeline (30 mins)

**File to Create:** `database/scripts/etl_cost_center.sql`

**Requirements:**

- Create scheduled SQL procedure/function to populate fact table daily
- Delete previous day data and re-insert (or use UPSERT)
- Log execution (start time, row count, errors)
- Handle data quality issues (missing warehouses, invalid dates)

**Procedure Logic:**

```
1. Log: "ETL Started - $(date)"
2. Delete fact_cost_center records for yesterday
3. Query sma_sale for yesterday's transactions
4. Query sma_purchases for yesterday's transactions
5. Query sma_inventory_movement for yesterday's transactions
6. FULL OUTER JOIN all three
7. Aggregate by warehouse_id + date
8. Insert into fact_cost_center
9. Log: "ETL Completed - X rows inserted"
10. If errors, send alert
```

**Constraints:**

- Must be idempotent (safe to run multiple times)
- Timezone handling for transaction dates
- Include error handling and retry logic
- Performance: should complete in < 5 minutes

**Success Criteria:**

- Procedure executes without errors
- Row count matches expected sales + purchases for the day
- ETL logs are created
- Can be scheduled daily at 2 AM

---

### STEP 5: Database - Indexes & Optimization (15 mins)

**File to Create:** `database/migrations/004_create_indexes.sql`

**Requirements:**

- Create composite indexes for fast query performance:
  - `fact_cost_center`: (warehouse_id, transaction_date)
  - `fact_cost_center`: (warehouse_type, transaction_date)
  - `dim_branch`: (pharmacy_id, warehouse_id)
  - `sma_sale`: (warehouse_id, sale_date)
  - `sma_purchases`: (warehouse_id, purchase_date)
  - `sma_inventory_movement`: (to_warehouse_id, transaction_date)

**Constraints:**

- Index size should not exceed 5% of table size
- Analyze execution plans for slow queries
- Use EXPLAIN to verify index usage

**Success Criteria:**

- Dashboard queries execute in < 2 seconds
- No full table scans in EXPLAIN plans
- Index size reasonable

---

### STEP 6: Backend API - Cost Center Endpoints (1.5 hours)

**File to Create:** `backend/routes/costCenter.js` (or Python/Go equivalent)

**Requirements:**

- Create RESTful API endpoints with proper error handling and validation

#### Endpoint 1: Get All Pharmacies with KPIs

```
GET /api/cost-center/pharmacies
Query Params: period=YYYY-MM, sort_by=revenue|profit|margin
Response:
{
  success: true,
  data: [
    {
      warehouse_id: 1,
      warehouse_name: "Pharmacy A",
      warehouse_code: "PHA001",
      period: "2025-10",
      kpi_total_revenue: 500000.00,
      kpi_total_cost: 300000.00,
      kpi_profit_loss: 200000.00,
      kpi_profit_margin_pct: 40.00,
      kpi_cost_ratio_pct: 60.00,
      branch_count: 3
    }
  ],
  total_count: 3,
  timestamp: "2025-10-25T14:30:00Z"
}
```

#### Endpoint 2: Get Pharmacy Detail with Branches

```
GET /api/cost-center/pharmacies/:pharmacy_id/branches
Query Params: period=YYYY-MM
Response:
{
  success: true,
  pharmacy: {
    warehouse_id: 1,
    warehouse_name: "Pharmacy A",
    kpi_total_revenue: 500000.00,
    kpi_total_cost: 300000.00,
    kpi_profit_loss: 200000.00,
    kpi_profit_margin_pct: 40.00
  },
  branches: [
    {
      warehouse_id: 10,
      warehouse_name: "Branch 001",
      warehouse_code: "BR001",
      kpi_total_revenue: 200000.00,
      kpi_total_cost: 120000.00,
      kpi_profit_loss: 80000.00,
      kpi_profit_margin_pct: 40.00,
      kpi_cost_ratio_pct: 60.00
    }
  ]
}
```

#### Endpoint 3: Get Branch Detail with Cost Breakdown

```
GET /api/cost-center/branches/:branch_id/detail
Query Params: period=YYYY-MM
Response:
{
  success: true,
  data: {
    warehouse_id: 10,
    warehouse_name: "Branch 001",
    pharmacy_name: "Pharmacy A",
    period: "2025-10",
    kpi_total_revenue: 200000.00,
    cost_breakdown: {
      cogs: 100000.00,
      inventory_movement: 15000.00,
      operational: 5000.00,
      total_cost: 120000.00
    },
    kpi_profit_loss: 80000.00,
    kpi_profit_margin_pct: 40.00
  }
}
```

#### Endpoint 4: Get Time Series Data (for charts)

```
GET /api/cost-center/branches/:branch_id/timeseries
Query Params: months=12 (default 12)
Response:
{
  success: true,
  branch_id: 10,
  data: [
    {
      period: "2025-10",
      revenue: 200000.00,
      cost: 120000.00,
      profit: 80000.00,
      margin_pct: 40.00
    },
    { period: "2025-09", ... }
  ]
}
```

**Constraints:**

- All monetary values: DECIMAL precision, 2 decimal places
- Period format: YYYY-MM only
- Validate period exists in data before returning
- Return 400 if invalid warehouse_id
- Return 404 if pharmacy/branch not found
- Implement pagination for large result sets (limit 100)
- Cache responses for 1 hour

**Success Criteria:**

- All 4 endpoints return data without errors
- Response times < 1 second
- Proper error messages for invalid inputs
- JSON schema validation passing

---

### STEP 7: Frontend - Dashboard Component (1.5 hours)

**File to Create:** `frontend/components/CostCenterDashboard.jsx` (React) or equivalent

**Requirements:**

- Build responsive dashboard with two views: Pharmacy Level & Branch Level

#### View 1: Pharmacy Level Dashboard

**Components:**

- Period Selector (dropdown: Month/Quarter/Year filter)
- KPI Cards (4 cards showing):
  - Total Revenue (all pharmacies)
  - Total Cost (all pharmacies)
  - Total Profit (all pharmacies)
  - Average Profit Margin % (all pharmacies)
- Pharmacy Table (sortable, clickable rows):
  - Columns: Pharmacy Name | Revenue | Cost | Profit | Margin %
  - Click row → drill-down to Branch view
  - Sorting: revenue, cost, profit, margin (ascending/descending)
- Chart 1: Revenue by Pharmacy (Bar Chart)
- Chart 2: Profit Distribution (Pie Chart)
- Chart 3: Profit Margin Trend (Line Chart - last 12 months)

**Features:**

- Responsive (desktop/tablet/mobile)
- Loading states & error handling
- Export to CSV button

#### View 2: Branch Level Dashboard (Drill-Down)

**Components:**

- Breadcrumb: Pharmacy Group > Pharmacy Name > Branches
- Back button to return to Pharmacy view
- KPI Cards (4 cards for selected pharmacy):
  - Same as pharmacy level but filtered
- Branch Table (same sorting as pharmacy table):
  - Columns: Branch Name | Revenue | Cost | Profit | Margin %
- Chart 1: Branch Revenue Trend (Line Chart - last 12 months)
- Chart 2: Cost Breakdown (Stacked Bar Chart):
  - COGS | Inventory Movement | Operational Costs
- Chart 3: Branch Comparison (Horizontal Bar Chart)

**Technical Requirements:**

- Use React Hooks (useState, useEffect, useContext)
- State management for selected period & pharmacy
- API integration with cost-center endpoints
- Error boundary component
- Loading spinner component
- No localStorage (use Context API for state)
- Responsive grid layout (CSS Grid or Tailwind)

**Styling:**

- Use Tailwind CSS (core utilities only)
- Color scheme:
  - Revenue: Blue (#3B82F6)
  - Cost: Red (#EF4444)
  - Profit: Green (#10B981)
  - Margin: Purple (#8B5CF6)
- Font: Inter or system font

**Success Criteria:**

- Dashboard loads without errors
- Period filter updates all KPIs correctly
- Pharmacy click triggers drill-down
- Back button returns to pharmacy view
- Charts render with correct data
- Responsive on mobile/tablet
- No console errors

---

### STEP 8: Dashboard - Chart Components (45 mins)

**File to Create:** `frontend/components/charts/` (multiple chart files)

**Requirements:**

- Create reusable chart components using Recharts library

#### Chart 1: BarChart - Revenue by Pharmacy

```
Component: RevenueByPharmacyChart.jsx
Props: data[], height=300
Chart Type: Bar chart (horizontal)
X-axis: Revenue amount (0 to max)
Y-axis: Pharmacy names
Color: Blue
Tooltip: Shows revenue + profit
```

#### Chart 2: PieChart - Profit Distribution

```
Component: ProfitDistributionChart.jsx
Props: data[] (pharmacy objects with profit)
Chart Type: Pie/Donut
Slices: Each pharmacy with color
Legend: Show pharmacy names + profit %
Tooltip: Pharmacy name + profit amount
```

#### Chart 3: LineChart - Profit Margin Trend

```
Component: MarginTrendChart.jsx
Props: data[] (time series), months=12
Chart Type: Line chart
X-axis: Months (last 12)
Y-axis: Profit margin %
Lines: One per pharmacy (different colors)
Tooltip: Month + margin % for each pharmacy
Legend: Pharmacy names
```

#### Chart 4: StackedBarChart - Cost Breakdown

```
Component: CostBreakdownChart.jsx
Props: branches[], period
Chart Type: Stacked horizontal bar
Stacks: COGS (red) | Movement (orange) | Operational (yellow)
X-axis: Total cost
Y-axis: Branch names
Tooltip: Each cost component
```

#### Chart 5: HorizontalBarChart - Branch Comparison

```
Component: BranchComparisonChart.jsx
Props: branches[]
Chart Type: Horizontal bar (sorted by profit)
X-axis: Profit amount
Y-axis: Branch names (sorted)
Color: Green
Tooltip: Revenue + Cost + Profit
```

**Constraints:**

- All charts responsive (mobile-friendly)
- Use Recharts library (from npm)
- No external CSS, use Tailwind only
- Handle empty data gracefully
- Loading state while data fetches

**Success Criteria:**

- All 5 charts render without errors
- Data updates when period changes
- Tooltips show correct values
- Charts responsive on mobile

---

### STEP 9: Frontend - Data Tables (45 mins)

**File to Create:** `frontend/components/tables/` (multiple table files)

**Requirements:**

- Create sortable, responsive data tables

#### Table 1: Pharmacy Table

```
Component: PharmacyTable.jsx
Columns: Pharmacy Name | Code | Revenue | Cost | Profit | Margin %
Rows: Each pharmacy
Features:
  - Click row → navigate to branch drill-down
  - Sort by any column (ascending/descending)
  - Currency formatting (Revenue, Cost, Profit)
  - Percentage formatting (Margin %)
  - Row highlight on hover
  - Total row at bottom
Responsive: Hide "Code" column on mobile
```

#### Table 2: Branch Table

```
Component: BranchTable.jsx
Columns: Branch Name | Code | Revenue | Cost | Profit | Margin %
Rows: Branches under selected pharmacy
Features:
  - Same as Pharmacy Table
  - Sort by any column
  - Show pharmacy name in header
  - Highlight best/worst performer (optional)
Responsive: Hide "Code" column on mobile
```

**Constraints:**

- Tables must be sortable (click header to sort)
- Format numbers correctly (2 decimal places)
- Currency symbol based on locale
- Handle empty states gracefully
- Keyboard accessible (sortable via keyboard)

**Success Criteria:**

- Tables render all data correctly
- Sorting works for all columns
- Number formatting correct
- Mobile responsive
- No horizontal scroll on mobile (hide columns)

---

### STEP 10: Integration Testing (30 mins)

**File to Create:** `tests/costCenter.test.js`

**Requirements:**

- Test database queries return expected structure
- Test API endpoints with mock data
- Test dashboard state management
- Test chart data calculations

**Test Cases:**

#### Database Tests

```
Test 1: fact_cost_center has all warehouses
  Query: SELECT COUNT(DISTINCT warehouse_id) FROM fact_cost_center
  Expected: >= number of warehouses in sma_warehouse

Test 2: KPI calculations correct
  Query: SELECT * FROM view_cost_center_pharmacy WHERE period='2025-10'
  Assert: profit = revenue - cost (for each row)

Test 3: Branch totals match pharmacy sum
  Query: Compare sum of branches vs pharmacy total
  Assert: Sum of branches profit = pharmacy profit
```

#### API Tests

```
Test 1: GET /api/cost-center/pharmacies returns 200
  Mock: Call endpoint
  Assert: response.success === true, data array length > 0

Test 2: GET /api/cost-center/pharmacies/:id/branches returns correct data
  Mock: Call with pharmacy_id=1
  Assert: response.pharmacy exists, response.branches is array

Test 3: Invalid pharmacy_id returns 404
  Mock: Call with pharmacy_id=99999
  Assert: response status 404
```

#### Frontend Tests

```
Test 1: Dashboard renders without errors
  Mount: <CostCenterDashboard />
  Assert: No errors, components visible

Test 2: Period filter updates KPIs
  Action: Change period dropdown
  Assert: API called with new period, KPIs updated

Test 3: Pharmacy click navigates to drill-down
  Action: Click pharmacy row in table
  Assert: Navigate to /cost-center/pharmacy/:id, breadcrumb updated

Test 4: Chart data matches table data
  Assert: Sum of chart values = sum of table values
```

**Success Criteria:**

- All tests pass (≥ 10 tests)
- No errors in test run
- Coverage > 70% for critical functions

---

### STEP 11: Documentation (30 mins)

**Files to Create:**

1. `docs/COST_CENTER_API.md` - API documentation
2. `docs/DATABASE_SCHEMA.md` - Database schema details
3. `docs/DEPLOYMENT.md` - Deployment instructions
4. `README.md` - Quick start guide

**COST_CENTER_API.md Contents:**

- All 4 endpoints with examples
- Request/response schemas
- Error codes and meanings
- Rate limits
- Authentication (if applicable)

**DATABASE_SCHEMA.md Contents:**

- Tables: dim_pharmacy, dim_branch, fact_cost_center, views
- Column definitions and data types
- Relationships and constraints
- Indexes
- Query examples for common reports

**DEPLOYMENT.md Contents:**

- Database migration steps
- Environment variables
- Running ETL job
- Starting backend server
- Building frontend
- Deployment checklist

**README.md Contents:**

- Project overview
- Quick start (5 minutes)
- Architecture diagram
- Tech stack
- File structure
- Contributing guidelines

**Success Criteria:**

- All documentation is clear and complete
- Code examples are runnable
- API documentation matches actual implementation
- Deployment guide is step-by-step

---

### STEP 12: Performance Testing & Optimization (30 mins)

**File to Create:** `performance/loadTest.js`

**Requirements:**

- Test API response times under load
- Verify database query performance
- Optimize slow queries

**Tests:**

```
Test 1: Pharmacy endpoint - 100 concurrent requests
  Expected: Response time < 1 second, 0 errors

Test 2: Branch drill-down endpoint - 50 concurrent requests
  Expected: Response time < 2 seconds, 0 errors

Test 3: Timeseries data endpoint - 1000 data points
  Expected: Response time < 3 seconds

Test 4: Database query - fact_cost_center aggregation
  Expected: Query time < 5 seconds for 12 months data
```

**Optimization:**

- Add indexes if queries > 5 seconds
- Enable query caching if applicable
- Implement pagination for large datasets
- Use denormalization (already done with fact_cost_center)

**Success Criteria:**

- All API responses < 2 seconds (99th percentile)
- Database queries < 5 seconds
- No timeout errors under load

---

## IMPLEMENTATION CHECKLIST

**Phase 1: Database (2 hours)**

- [ ] Step 1: Create dimension tables
- [ ] Step 2: Create fact table
- [ ] Step 3: Create KPI views
- [ ] Step 4: Create ETL pipeline
- [ ] Step 5: Create indexes

**Phase 2: Backend (1.5 hours)**

- [ ] Step 6: Build API endpoints
- [ ] Testing: Unit tests for endpoints

**Phase 3: Frontend (3 hours)**

- [ ] Step 7: Build dashboard component
- [ ] Step 8: Build chart components
- [ ] Step 9: Build table components
- [ ] Testing: Component tests

**Phase 4: Testing & Docs (1.5 hours)**

- [ ] Step 10: Integration testing
- [ ] Step 11: Documentation
- [ ] Step 12: Performance testing

**Total: 8 hours** (with buffer for debugging)

---

## FILE STRUCTURE

```
pharmacy-cost-center/
├── database/
│   ├── migrations/
│   │   ├── 001_create_dimensions.sql
│   │   ├── 002_create_fact_table.sql
│   │   ├── 003_create_kpi_views.sql
│   │   └── 004_create_indexes.sql
│   └── scripts/
│       └── etl_cost_center.sql
├── backend/
│   ├── routes/
│   │   └── costCenter.js
│   └── middleware/
│       └── errorHandler.js
├── frontend/
│   ├── components/
│   │   ├── CostCenterDashboard.jsx
│   │   ├── charts/
│   │   │   ├── RevenueByPharmacyChart.jsx
│   │   │   ├── ProfitDistributionChart.jsx
│   │   │   ├── MarginTrendChart.jsx
│   │   │   ├── CostBreakdownChart.jsx
│   │   │   └── BranchComparisonChart.jsx
│   │   └── tables/
│   │       ├── PharmacyTable.jsx
│   │       └── BranchTable.jsx
│   ├── pages/
│   │   └── CostCenterPage.jsx
│   └── hooks/
│       └── useCostCenter.js
├── tests/
│   ├── costCenter.test.js
│   └── integration.test.js
├── performance/
│   └── loadTest.js
├── docs/
│   ├── COST_CENTER_API.md
│   ├── DATABASE_SCHEMA.md
│   ├── DEPLOYMENT.md
│   └── README.md
└── .env.example
```

---

## KEY SUCCESS METRICS

1. **Database Performance**

   - Query time for KPI queries: < 5 seconds
   - ETL execution time: < 5 minutes
   - No slow queries (> 10 seconds)

2. **API Performance**

   - Response time: < 1-2 seconds (99th percentile)
   - Error rate: < 0.1%
   - Uptime: 99.9%

3. **Frontend Performance**

   - Page load time: < 3 seconds
   - Chart render time: < 1 second
   - No console errors

4. **Data Accuracy**

   - Pharmacy totals = sum of branches (100% match)
   - Revenue from API = sum from database (100% match)
   - Profit = Revenue - Cost (100% match)

5. **User Experience**
   - Drill-down works smoothly
   - Filters update instantly
   - No broken links or missing data

---

## COMMON ISSUES & SOLUTIONS

**Issue 1: NULL values in costs**

- Solution: Use COALESCE(column, 0) in all calculations

**Issue 2: Date mismatches between sale and purchase**

- Solution: Use DATE() function and group by date only (ignore time)

**Issue 3: Slow queries on large fact table**

- Solution: Create composite indexes on (warehouse_id, transaction_date)

**Issue 4: Chart not updating when period changes**

- Solution: Add dependency on period to useEffect hook

**Issue 5: Division by zero in profit margin**

- Solution: Use NULLIF(revenue, 0) in SQL

**Issue 6: ETL running multiple times creates duplicates**

- Solution: DELETE before INSERT or use UPSERT (ON CONFLICT)

**Issue 7: Frontend not connecting to API**

- Solution: Check CORS headers, API endpoint URL, environment variables

**Issue 8: Performance degrades after 6 months of data**

- Solution: Archive old data, use partitioning, increase index

---

## NEXT STEPS AFTER IMPLEMENTATION

1. **Day 2:** Load 3-6 months of historical data
2. **Day 3:** User acceptance testing with client
3. **Day 4:** Performance tuning and optimization
4. **Day 5:** Production deployment
5. **Day 6+:** Monitoring, bug fixes, enhancements

---

## GITHUB AUTOPILOT COMMANDS

Each step can be triggered with:

```bash
# Generate Step 1: Dimensions
autopilot generate "COST_CENTER_STEP_1: Create dimension tables"

# Generate Step 2: Fact Table
autopilot generate "COST_CENTER_STEP_2: Create fact table with all joins"

# Generate Step 3: KPI Views
autopilot generate "COST_CENTER_STEP_3: Create KPI views for pharmacy and branch"

# Generate Step 4: ETL Pipeline
autopilot generate "COST_CENTER_STEP_4: Create daily ETL stored procedure"

# Generate Step 5: Indexes
autopilot generate "COST_CENTER_STEP_5: Create performance indexes"

# Generate Step 6: Backend API
autopilot generate "COST_CENTER_STEP_6: Build 4 REST API endpoints for cost center"

# Generate Step 7: Dashboard Component
autopilot generate "COST_CENTER_STEP_7: Create React dashboard with drill-down"

# Generate Step 8: Chart Components
autopilot generate "COST_CENTER_STEP_8: Create 5 reusable Recharts components"

# Generate Step 9: Table Components
autopilot generate "COST_CENTER_STEP_9: Create sortable data tables"

# Generate Step 10: Testing
autopilot generate "COST_CENTER_STEP_10: Integration tests for database, API, frontend"

# Generate Step 11: Documentation
autopilot generate "COST_CENTER_STEP_11: Complete documentation for all components"

# Generate Step 12: Performance Testing
autopilot generate "COST_CENTER_STEP_12: Load testing and optimization"
```

---

## IMPORTANT NOTES FOR AUTOPILOT

1. **Database Compatibility:** Verify SQL dialect (PostgreSQL, MySQL, SQL Server)
2. **Framework Version:** Confirm React version, Node version
3. **Library Versions:** Use Recharts, Tailwind CSS (latest stable)
4. **Error Handling:** Include try-catch blocks, error boundaries
5. **Logging:** Add console logs for debugging in development
6. **Comments:** Add inline comments for complex logic
7. **Type Safety:** Consider TypeScript for larger components
8. **Testing:** Include unit tests alongside code generation
9. **Accessibility:** Ensure WCAG 2.1 compliance
10. **Security:** Validate all user inputs, sanitize SQL queries

---

**End of Implementation Guide**

_Last Updated: 2025-10-25_  
_Version: 1.0_
