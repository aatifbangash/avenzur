# QUESTION ANSWERED: How is Total Revenue Calculated?

**Your Question:** "How is the total revenue calculated? Can you give me details?"

**Answer:** Provided in detail below.

---

## TL;DR (Too Long; Didn't Read)

**Total Revenue = SUM of all pharmacy sales (`total_revenue` column) from `sma_fact_cost_center` table for the selected period (YYYY-MM)**

```sql
SELECT SUM(total_revenue) 
FROM sma_fact_cost_center
WHERE period = '2025-10'
-- Result: ~2,600,000 SAR (all 8 pharmacies combined)
```

---

## DETAILED ANSWER

### Where the Data Comes From

**Table:** `sma_fact_cost_center`

**Column:** `total_revenue` ← This is what gets summed

**Aggregation:** By period (YYYY-MM) and warehouse (pharmacy/branch)

### The Calculation Formula

```
Total Revenue = SUM(total_revenue)
                FROM sma_fact_cost_center
                WHERE period = '2025-10'
```

### How It's Implemented

#### 1. Database View (SQL)
- **File:** `app/migrations/cost-center/005_create_views.sql`
- **View Name:** `view_cost_center_summary`
- **Purpose:** Aggregates revenue by period

#### 2. Model Method (PHP)
- **File:** `app/models/admin/Cost_center_model.php`
- **Method:** `get_summary_stats($period)`
- **Action:** Queries the view for company totals

#### 3. Controller (PHP)
- **File:** `app/controllers/admin/Cost_center.php`
- **Method:** `dashboard()`
- **Action:** Gets period from URL, calls model, passes to view

#### 4. Display (HTML/JavaScript)
- **File:** `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`
- **Display:** Shows formatted revenue with SAR and commas

### The Actual Database Query

```sql
-- This is what runs when you open the dashboard:
SELECT 
    'company' AS level,
    'RETAJ AL-DAWA' AS entity_name,
    '2025-10' AS period,
    SUM(fcc.total_revenue) AS kpi_total_revenue  ← ⭐ THE TOTAL
FROM sma_fact_cost_center fcc
LEFT JOIN sma_dim_pharmacy dp ON fcc.warehouse_id = dp.warehouse_id
WHERE CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) = '2025-10'
GROUP BY fcc.period_year, fcc.period_month
```

### What Gets Summed

**October 2025 Example:**

```
Pharmacy 1:    648,800.79 SAR
Pharmacy 2:    520,000.00 SAR
Pharmacy 3:    450,000.00 SAR
Pharmacy 4:    385,000.00 SAR
Pharmacy 5:    298,500.00 SAR
Pharmacy 6:    175,200.00 SAR
Pharmacy 7:     87,500.00 SAR
Pharmacy 8:     35,000.00 SAR
─────────────────────────────
TOTAL:     2,600,000.79 SAR  ← This is displayed in KPI card
```

---

## How the Dashboard Shows It

### Company Level (All Pharmacies)
```
┌─────────────────────────────────┐
│ Total Revenue                   │
│ SAR 2,600,000.79                │ ← SUM of all 8 pharmacies
└─────────────────────────────────┘
```

### When You Filter to One Pharmacy
```
URL: admin/cost_center/pharmacy/52?period=2025-10

┌─────────────────────────────────┐
│ Total Revenue                   │
│ SAR 648,800.79                  │ ← Only pharmacy 52
└─────────────────────────────────┘
```

---

## Critical Discovery About COST (Not Revenue)

**Note:** While investigating how revenue is calculated, I discovered that **total cost does NOT include the `sma_purchases` table.**

### Cost Calculation (Current - Potentially Incomplete)

```sql
Total Cost = COGS + Inventory Movement + Operational Cost

Total Cost = total_cogs 
           + inventory_movement_cost 
           + operational_cost
```

### Missing Component

- `sma_purchases` table is NOT included in cost calculations
- This means total cost may be **UNDERSTATED**
- Profit and margins may be **OVERSTATED**

### Action Needed

**Before production:** Clarify with business whether purchases should be included in cost calculations.

See: `TOTAL_COST_ANALYSIS_CRITICAL_FINDINGS.md`

---

## Complete Documentation

For detailed information, see these files:

| File | Content |
|------|---------|
| `HOW_TOTAL_REVENUE_IS_CALCULATED.md` | Complete revenue calculation with examples |
| `TOTAL_REVENUE_CALCULATION_GUIDE.md` | Step-by-step revenue guide |
| `TOTAL_COST_ANALYSIS_CRITICAL_FINDINGS.md` | ⚠️ Cost calculation issues |
| `SESSION_SUMMARY_2025_10_25_FINAL.md` | Complete session overview |

---

## Summary

**Total Revenue is calculated by:**
1. Reading `total_revenue` from `sma_fact_cost_center`
2. Filtering by period (YYYY-MM)
3. Summing across all pharmacies
4. Displaying in dashboard

**The formula:** `SUM(total_revenue) by period`

**The result:** ~2,600,000 SAR for all 8 pharmacies in October 2025

---

**Answer Provided:** ✅ COMPLETE

**See:** `HOW_TOTAL_REVENUE_IS_CALCULATED.md` for full details
