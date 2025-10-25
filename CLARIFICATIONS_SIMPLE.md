# ❓ CLARIFICATIONS NEEDED - Dashboard Data Integration

Please answer these 5 simple questions to proceed with data integration:

---

## 1️⃣ PROFIT MARGIN CALCULATION

### Current Formula in Database:

```
Profit Margin % = (Revenue - COGS - Inventory Cost - Operational Cost) / Revenue × 100
```

### Question: Is this correct, or should it be:

- **A)** `(Revenue - COGS) / Revenue × 100` - Gross Margin only
- **B)** `(Revenue - COGS - Inventory Cost - Operational Cost) / Revenue × 100` - Net Margin ← Current
- **C)** Something else? (Please specify)

**My Recommendation**: Option B (what we currently have) seems most comprehensive

---

## 2️⃣ REVENUE DEFINITION

### Question: When calculating Total Revenue, should I use:

- **A)** Sum of all `grand_total` from completed sales
- **B)** Sum of `grand_total` minus refunded/cancelled transactions
- **C)** Calculated from sale items: `SUM(quantity × sale_price)`
- **D)** Something else? (Please specify)

**Note**: We have `sma_sales` table with sales data  
**Date Range**: Last 30 days? 90 days? Full history available?

**My Recommendation**: Option A or B - which refund behavior do you want?

---

## 3️⃣ COST COMPONENTS

### Question: For "Total Cost", should I include:

- **A)** COGS only (Cost of Goods Sold from purchases)
- **B)** COGS + Inventory Movement Cost (transfers between branches)
- **C)** COGS + Inventory + Operational Cost
- **D)** All above + Loyalty Discounts (how to calculate?)

**Current Database has**:

- `total_cogs` - from purchases
- `inventory_movement_cost` - from transfers
- `operational_cost` - (need to understand what's included)
- Loyalty discounts - (location unclear)

**My Recommendation**: Option C (currently implemented), but need clarification on loyalty discounts

---

## 4️⃣ TREND PERIOD & GRANULARITY

### Question: For charts showing trends:

- **A)** Monthly trend (Last 12 months)
- **B)** Weekly trend (Last 12 weeks)
- **C)** Daily trend (Last 30 days)
- **D)** Something else?

**Examples**:

- Profit Margin Trend: Monthly or other?
- Revenue Trend: Monthly or other?
- Cost Breakdown: Monthly or other?

**My Recommendation**: Monthly seems best for executive dashboard

---

## 5️⃣ PHARMACY HEALTH STATUS (Color Badges)

### Question: How to determine if a Pharmacy/Branch is Healthy?

**Define thresholds**:

```
🟢 GREEN (Healthy)
   - Profit Margin > ___% ?
   - AND/OR Revenue > $_____ ?
   - AND/OR Growth > ___% ?

🟡 YELLOW (At Risk)
   - Profit Margin between __% - __% ?
   - AND/OR Revenue declining > __% ?

🔴 RED (Unhealthy)
   - Profit Margin < __% ?
   - AND/OR Revenue declined > __% ?
```

**Example (you fill in blanks)**:

```
🟢 GREEN:  Margin > 35% && Revenue stable
🟡 YELLOW: Margin 20-35% || Revenue -5% to -15%
🔴 RED:    Margin < 20% || Revenue -15%+
```

**My Recommendation**:

- 🟢 Margin > 35%, Revenue stable/growth
- 🟡 Margin 20-35%, Revenue declining < 10%
- 🔴 Margin < 20%, Revenue declining > 10%

---

## 6️⃣ (BONUS) ADDITIONAL REQUIREMENTS

### Any other metrics or calculations I should know about?

- Trial Balance data location?
- Any special cost allocations?
- Currency conversions needed?
- Multiple company support?
- Anything else?

---

## How to Answer

Simply reply with:

```
1. Profit Margin: [A/B/C] - [Confirmation or explanation]
2. Revenue: [A/B/C/D] - [Date range]
3. Cost Components: [A/B/C/D] - [Explanation]
4. Trends: [A/B/C/D] - [Confirmation]
5. Health Status: [Paste your thresholds]
6. Other: [Any additional items]
```

---

## Example Response Format

```
1. Profit Margin: B - Use current formula (Net Margin)
2. Revenue: A - Sum grand_total for completed sales, last 90 days
3. Cost Components: C - Include COGS + Inventory + Operational
4. Trends: A - Monthly trends showing last 12 months
5. Health Status:
   🟢 GREEN:  Margin > 35% AND Revenue > $50,000
   🟡 YELLOW: Margin 20-35% OR Revenue declining
   🔴 RED:    Margin < 20% OR Revenue declining > 15%
6. Other: Calculate loyalty discount impact separately
```

---

## What Happens After You Answer

1. ✅ I get clarification on all calculations
2. ✅ I update `Cost_center_model.php` with new methods
3. ✅ I update dashboard view with real database queries
4. ✅ I add new charts and visualizations
5. ✅ Dashboard shows REAL DATA (not hardcoded)
6. ✅ Drill-down navigation works with real data

---

## No Pressure! 🎉

If you're unsure about any answer:

- I can recommend best practices
- I can show sample calculations
- I can implement flexible system that can be adjusted later

Just let me know what works best for your business!

---

**Ready to go** - Awaiting your answers 👉
