# Dashboard Data Integration - Clarifications Needed

Date: 2025-10-25  
Status: Ready for Implementation (Awaiting Clarifications)

---

## Current Understanding

I've reviewed the existing database structure:

### ✅ Available Resources

1. **Fact Table**: `sma_fact_cost_center`

   - Contains: `total_revenue`, `total_cogs`, `inventory_movement_cost`, `operational_cost`
   - Granularity: Daily per warehouse
   - Period: Last 90+ days

2. **Existing Views**:

   - `view_cost_center_pharmacy` - Monthly KPI aggregates at pharmacy level
   - `view_cost_center_branch` - Monthly KPI aggregates at branch level
   - `view_cost_center_summary` - Company-level overview

3. **Source Tables**:
   - `sma_sales` - Sales transactions (revenue)
   - `sma_purchases` - Purchase transactions (COGS)
   - `sma_inventory_movement` - Inventory movements (costs)
   - `sma_loyalty_discount_transactions` - Discount transactions
   - `sma_dim_pharmacy` - Pharmacy dimension
   - `sma_dim_branch` - Branch dimension

---

## Data Points Required - Clarification Questions

### 1️⃣ PROFIT MARGIN CALCULATION

**Current View Formula**:

```sql
ROUND((SUM(total_revenue - (total_cogs + inventory_movement_cost + operational_cost)) / SUM(total_revenue)) * 100, 2)
```

**Question**: Is this correct formula, or should it be:

- Option A: `(Revenue - COGS) / Revenue * 100` (Gross Margin)
- Option B: `(Revenue - COGS - Inventory Cost - Operational Cost) / Revenue * 100` (Net Margin)
- Option C: `(Revenue - COGS) / Revenue * 100` considering inventory_movement as separate line item?

**Current understanding**: Option B (what the view calculates)

---

### 2️⃣ REVENUE CALCULATION (Revenue Per Pharmacy)

**Source**: `sma_sales` table

**Question**: Should I use:

- `SUM(grand_total)` from sales?
- `SUM(total_line_items)` if different from grand_total?
- Include cancelled/refunded transactions or only `sale_status = 'completed'`?
- Any date range filtering (last 30/90 days)?

**My assumption**:

```sql
SUM(grand_total) FROM sma_sales
WHERE sale_status = 'completed'
AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
```

---

### 3️⃣ TOTAL COST CALCULATION

**Question**: Should total cost include:

- COGS (Cost of Goods Sold) from `sma_purchases`?
- Inventory Movement Cost (Transfer between warehouses)?
- Loyalty Discount costs (how to calculate)?
- Operational costs (how are these currently stored)?

**Current view** combines:

```
total_cogs + inventory_movement_cost + operational_cost
```

**My assumption**: These three components are already aggregated in `sma_fact_cost_center`

---

### 4️⃣ PROFIT MARGIN TREND

**Question**: Should I show:

- Monthly trend? (Last 12 months)
- Weekly trend? (Last 12 weeks)
- Daily trend? (Last 30 days)

**My assumption**: Monthly for company-level, but granular per data available

---

### 5️⃣ PHARMACY PERFORMANCE COMPARISON

**Question**: What does "Total Sale + Margin" comparison mean?

- Compare pharmacy by pharmacy side-by-side?
- Show as ranks (1st best, 2nd, 3rd)?
- Show as percentage of total company?

**My assumption**: Side-by-side comparison with Total Revenue and Profit Margin %

---

### 6️⃣ TRIAL BALANCE GLANCE (Health Check)

**Question**: What determines if a pharmacy/branch is "healthy"?

- Profit margin > X%?
- Revenue > X amount?
- Revenue growth > Y%?
- Profitability trend?

**Possible metrics**:

- 🟢 Green: Profit margin > 35%, Revenue stable/increasing
- 🟡 Yellow: Profit margin 20-35%, Revenue declining
- 🔴 Red: Profit margin < 20%, Revenue declining

**My assumption**: Need your specific thresholds

---

## Data Structure to Implement

### Current Available in View:

```
view_cost_center_pharmacy:
- pharmacy_id, pharmacy_name, pharmacy_code
- kpi_total_revenue
- kpi_total_cost
- kpi_profit_loss (calculated)
- kpi_profit_margin_pct (calculated)
- kpi_cost_ratio_pct (calculated)
- branch_count
- period
- last_updated
```

### Additional Data Needed:

Based on your requirements, I need to add:

1. **Revenue Trend** (by month)

   - Query: Aggregate daily revenue by month
   - Source: `sma_fact_cost_center` or `sma_sales`

2. **Profit Margin Trend** (by month, company-wide)

   - Query: Calculate margin for each month
   - Source: `sma_fact_cost_center`

3. **Cost Breakdown Details**

   - COGS Cost
   - Inventory Movement Cost
   - Operational Cost
   - Loyalty Discount Cost

4. **Health Score**
   - Calculated field based on thresholds
   - Include growth rate

---

## Questions to Answer

Please clarify:

1. **Profit Margin Formula** - Which option is correct?
2. **Revenue Definition** - Use `grand_total`, include refunds?
3. **Cost Components** - What's in operational_cost? How to calculate loyalty discounts?
4. **Trend Period** - Monthly, weekly, or daily?
5. **Health Thresholds** - What are the green/yellow/red boundaries?
6. **Missing Data** - Any other data sources I should know about?
7. **Date Range** - Last 30 days, 90 days, or full available history?

---

## Proposed Implementation Plan

Once you clarify above, I will:

### Step 1: Update Cost_center_model.php

- Add methods for trend data retrieval
- Add methods for cost breakdown
- Add health score calculation logic
- Add company-wide metrics

### Step 2: Update Database Queries

- Create additional queries if needed for profit margin trend
- Get COGS breakdown per pharmacy
- Get cost movement breakdown
- Get loyalty discount impact

### Step 3: Update Dashboard View

- Pass actual data (not hardcoded)
- Display profit margin trend chart with real data
- Show cost breakdown by component
- Add health score badges

### Step 4: Update ECharts Visualizations

- Revenue by Pharmacy (already using view data ✅)
- Profit Margin Trend (need to add)
- Cost Breakdown by Component (need to add)
- Pharmacy Comparison with Health Scores (need to add)

---

## Data Source Map

| Data Point       | Source Table                                        | Calculation                             | Notes                 |
| ---------------- | --------------------------------------------------- | --------------------------------------- | --------------------- |
| Total Revenue    | `sma_fact_cost_center.total_revenue` or `sma_sales` | SUM by period                           | Already in fact table |
| Total COGS       | `sma_fact_cost_center.total_cogs`                   | SUM by period                           | Already in fact table |
| Inventory Cost   | `sma_fact_cost_center.inventory_movement_cost`      | SUM by period                           | Already in fact table |
| Operational Cost | `sma_fact_cost_center.operational_cost`             | SUM by period                           | Already in fact table |
| Profit Margin    | Calculated                                          | (Revenue - Total Cost) / Revenue \* 100 | Clarification needed  |
| Loyalty Discount | ?                                                   | ?                                       | Need location in DB   |
| Revenue Trend    | `sma_fact_cost_center` + `sma_sales`                | Aggregate by month                      | Available             |
| Margin Trend     | `sma_fact_cost_center`                              | Aggregate by month                      | Available             |
| Health Score     | Calculated                                          | Based on thresholds                     | Thresholds needed     |

---

## Next Steps

1. **Reply with answers** to the clarification questions above
2. **Confirm thresholds** for health scoring
3. **I will implement** all data fetching methods
4. **Dashboard will show** real data from database views

---

**Status**: ⏳ Awaiting Your Clarifications  
**Timeline**: Implementation will start immediately upon clarification
