# Dashboard Data Integration - Summary

## Current Status

✅ **Bug Fixed**: Dashboard loads without errors  
⏳ **Data Integration**: Ready for implementation (awaiting clarifications)

---

## What We Have Now

### ✅ Available Views (Database)

```
view_cost_center_pharmacy
├── pharmacy_id, pharmacy_name
├── kpi_total_revenue
├── kpi_total_cost
├── kpi_profit_loss
├── kpi_profit_margin_pct (calculated)
├── kpi_cost_ratio_pct (calculated)
├── branch_count
├── period (YYYY-MM)
└── last_updated

view_cost_center_branch
├── branch_id, branch_name
├── pharmacy_id, pharmacy_name
├── kpi_total_revenue
├── kpi_total_cost
├── kpi_profit_loss
├── kpi_profit_margin_pct (calculated)
├── kpi_cost_ratio_pct (calculated)
└── period (YYYY-MM)

view_cost_center_summary
├── level (company / pharmacy)
├── entity_name
├── kpi_total_revenue
├── kpi_total_cost
├── kpi_profit_loss
├── kpi_profit_margin_pct (calculated)
├── entity_count (# of branches)
└── period (YYYY-MM)
```

### ✅ Available Source Data

```
sma_fact_cost_center (Daily aggregates)
├── warehouse_id, warehouse_name
├── pharmacy_id, pharmacy_name
├── branch_id, branch_name
├── transaction_date
├── period_year, period_month
├── total_revenue
├── total_cogs
├── inventory_movement_cost
├── operational_cost
└── updated_at

sma_sales (Sale transactions)
├── id, date
├── warehouse_id
├── grand_total
├── sale_status (completed, cancelled, etc.)
└── items with unit_price, qty, etc.

sma_inventory_movement (Inventory transactions)
├── id, date
├── warehouse_id, warehouse_to_id
├── type (purchase, transfer_in, transfer_out, sale, return)
├── quantity, unit_cost
└── total_cost

sma_loyalty_discount_transactions (Discount records)
├── id, date
├── pharmacy_id, branch_id
├── discount_amount
└── transaction_type
```

---

## What We Need

### 1. Clarify Data Calculations

**Question 1**: Profit Margin Formula?

- Should it be: `(Revenue - COGS) / Revenue * 100` (Gross Margin)
- Or: `(Revenue - COGS - Inventory Cost - Operational Cost) / Revenue * 100` (Net Margin)

**Question 2**: Revenue Definition?

- Use `sma_sales.grand_total` or calculated from items?
- Include refunded transactions?

**Question 3**: Cost Components?

- COGS source: `sma_purchases` or `sma_fact_cost_center.total_cogs`?
- Operational costs: Where are these stored?
- Loyalty discount cost: How to extract?

**Question 4**: Time Periods?

- Show trends monthly? Weekly? Daily?
- Last 30 days, 90 days, or full history?

**Question 5**: Health Check Thresholds?

- Green zone: Profit margin > ?% AND revenue > $?
- Yellow zone: Profit margin between ?-?%
- Red zone: Profit margin < ?% OR revenue declining > ?%

---

## What Dashboard Will Show (After Clarification)

### KPI Cards (Real Data)

```
┌──────────┬──────────┬──────────┬──────────┐
│ Revenue  │ Cost     │ Profit   │ Margin % │
│ $500K    │ $300K    │ $200K    │ 40.0%    │
│ from DB  │ from DB  │Calc'd   │ Calc'd   │
└──────────┴──────────┴──────────┴──────────┘
```

### Charts (Real Data from Views)

```
📊 Revenue by Pharmacy
  - Data from: view_cost_center_pharmacy.kpi_total_revenue

📈 Profit Margin Trend
  - Data from: sma_fact_cost_center (aggregated by month)
  - Calculation: Based on clarified formula

📊 Cost Breakdown by Component
  - COGS: sma_fact_cost_center.total_cogs
  - Inventory: sma_fact_cost_center.inventory_movement_cost
  - Operational: sma_fact_cost_center.operational_cost

🏥 Pharmacy Health Status
  - Data from: view_cost_center_pharmacy + thresholds
  - Shows: Green/Yellow/Red badges per pharmacy
```

### Tables (Real Data)

```
Pharmacy Name  │ Revenue  │ Cost    │ Profit  │ Margin % │ Health
Pharmacy A     │ $500K    │ $300K   │ $200K   │ 40.0%    │ 🟢
Pharmacy B     │ $400K    │ $240K   │ $160K   │ 40.0%    │ 🟢
Pharmacy C     │ $280K    │ $168K   │ $112K   │ 40.0%    │ 🟡
```

---

## Database Query Examples (What We'll Use)

### Example 1: Get Pharmacy Performance (Current Implementation ✅)

```sql
SELECT
    pharmacy_id, pharmacy_name,
    kpi_total_revenue,
    kpi_total_cost,
    kpi_profit_loss,
    kpi_profit_margin_pct,
    branch_count
FROM view_cost_center_pharmacy
WHERE period = '2025-10'
ORDER BY kpi_total_revenue DESC
```

### Example 2: Get Profit Margin Trend (To Be Implemented)

```sql
SELECT
    CONCAT(period_year, '-', LPAD(period_month, 2, '0')) AS period,
    SUM(total_revenue) AS total_revenue,
    SUM(total_cogs + inventory_movement_cost + operational_cost) AS total_cost,
    ROUND((SUM(total_revenue - (total_cogs + inventory_movement_cost + operational_cost)) / SUM(total_revenue)) * 100, 2) AS profit_margin_pct
FROM sma_fact_cost_center
GROUP BY period_year, period_month
ORDER BY period DESC
LIMIT 12
```

### Example 3: Get Cost Breakdown (To Be Implemented)

```sql
SELECT
    pharmacy_id,
    pharmacy_name,
    SUM(total_cogs) AS cogs_cost,
    SUM(inventory_movement_cost) AS inventory_cost,
    SUM(operational_cost) AS operational_cost,
    SUM(total_cogs + inventory_movement_cost + operational_cost) AS total_cost
FROM sma_fact_cost_center
WHERE period_year = 2025 AND period_month = 10
GROUP BY pharmacy_id, pharmacy_name
```

---

## Implementation Roadmap

### Phase 1: Answer Clarifications ⏳ (Your Input)

- [ ] Confirm profit margin formula
- [ ] Define revenue calculation method
- [ ] Define cost components
- [ ] Define time periods for trends
- [ ] Define health score thresholds

### Phase 2: Update Model (My Task)

- [ ] Add method: `get_profit_margin_trend()`
- [ ] Add method: `get_cost_breakdown()`
- [ ] Add method: `get_company_metrics()`
- [ ] Add method: `calculate_health_score()`

### Phase 3: Update Views (My Task)

- [ ] Pass real data to dashboard
- [ ] Add profit margin trend chart
- [ ] Add cost breakdown chart
- [ ] Add health score badges

### Phase 4: Update Charts (My Task)

- [ ] Bind ECharts to real database queries
- [ ] Update legend and labels
- [ ] Add interactivity

### Phase 5: Testing (My Task)

- [ ] Verify all data displays correctly
- [ ] Test drill-down navigation
- [ ] Test filters and sorting

---

## Files Ready for Modification

Once you provide clarifications:

1. **app/models/admin/Cost_center_model.php**

   - Add new data retrieval methods
   - No breaking changes to existing methods

2. **themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php**

   - Update data fetching from PHP to JS
   - Replace hardcoded data with real database values
   - Add new sections for trends and breakdowns

3. **Database Queries**
   - May need to add custom queries if views don't have all data
   - No schema changes needed

---

## Quick Reference: Data Points Mapping

| Dashboard Section  | Data Source                          | Current Status | Notes                                   |
| ------------------ | ------------------------------------ | -------------- | --------------------------------------- |
| Total Revenue Card | `view_cost_center_pharmacy`          | ✅ Ready       | Already implemented                     |
| Total Cost Card    | `view_cost_center_pharmacy`          | ✅ Ready       | Already implemented                     |
| Total Profit Card  | `view_cost_center_pharmacy` (calc'd) | ✅ Ready       | Already implemented                     |
| Avg Margin Card    | `view_cost_center_pharmacy` (calc'd) | ✅ Ready       | Already implemented                     |
| Revenue Chart      | `view_cost_center_pharmacy`          | ✅ Ready       | Currently hardcoded, will use real data |
| Margin Trend       | `sma_fact_cost_center`               | ⏳ Pending     | Need calculation clarification          |
| Cost Breakdown     | `sma_fact_cost_center`               | ⏳ Pending     | Need component clarification            |
| Pharmacy Table     | `view_cost_center_pharmacy`          | ✅ Ready       | Already pulling real data               |
| Health Badges      | `view_cost_center_pharmacy`          | ⏳ Pending     | Need threshold clarification            |

---

## Expected Timeline

- **Clarifications**: ~5 minutes from you
- **Model Updates**: ~30 minutes
- **View Updates**: ~45 minutes
- **Testing**: ~30 minutes
- **Total**: ~2 hours from clarification

---

## Next Action

👉 **Please reply with answers to the 5 key questions in the clarifications document**

Once you provide:

1. Profit margin formula choice
2. Revenue definition
3. Cost component details
4. Time period preferences
5. Health score thresholds

I'll immediately implement all real data integration and update the dashboard!

---

**Status**: Ready to implement • Awaiting clarifications • No blockers
