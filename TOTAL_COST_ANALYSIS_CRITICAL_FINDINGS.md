# Total Cost Calculation - CRITICAL FINDINGS

**Date:** 2025-10-25  
**Status:** ⚠️ **IMPORTANT DISCOVERY**

---

## Executive Summary

**The total cost calculation currently DOES NOT include purchases from the `sma_purchases` table.**

The dashboard only uses three cost components from `sma_fact_cost_center`:

1. `total_cogs` (Cost of Goods Sold)
2. `inventory_movement_cost` (Inventory handling)
3. `operational_cost` (Operational expenses)

**`sma_purchases` table is completely ignored.**

---

## Current Cost Calculation

### What IS Included

```sql
Total Cost = COGS + Inventory Movement + Operational Cost

Total Cost = total_cogs + inventory_movement_cost + operational_cost
```

### Formula (From Database View)

```sql
COALESCE(
    SUM(fcc.total_cogs
        + fcc.inventory_movement_cost
        + fcc.operational_cost), 0
) AS kpi_total_cost
```

**File:** `app/migrations/cost-center/005_create_views.sql`

---

## What IS NOT Included

### Missing: `sma_purchases` Table

The `sma_purchases` table is NOT joined or referenced anywhere in the cost center calculations.

```sql
-- This query does NOT include sma_purchases:
SELECT
    SUM(fcc.total_cogs) AS cogs,
    SUM(fcc.inventory_movement_cost) AS inventory,
    SUM(fcc.operational_cost) AS operational
FROM sma_fact_cost_center fcc
-- ❌ NO JOIN to sma_purchases
WHERE fcc.period = '2025-10'
```

---

## Cost Data Sources

### Current Sources (YES - Included)

| Table                  | Column                    | What It Represents            | Status      |
| ---------------------- | ------------------------- | ----------------------------- | ----------- |
| `sma_fact_cost_center` | `total_cogs`              | Cost of goods sold            | ✅ INCLUDED |
| `sma_fact_cost_center` | `inventory_movement_cost` | Inventory handling, movements | ✅ INCLUDED |
| `sma_fact_cost_center` | `operational_cost`        | Rent, utilities, staff, ops   | ✅ INCLUDED |

### Possible Missing Sources (NO - Not Included)

| Table           | Column         | What It Should Represent | Status          |
| --------------- | -------------- | ------------------------ | --------------- |
| `sma_purchases` | (cost/amount)  | Actual purchase amounts  | ❌ NOT INCLUDED |
| `sma_purchases` | `total_amount` | Purchase total           | ❌ NOT INCLUDED |
| `sma_suppliers` | pricing        | Supplier costs           | ❌ NOT INCLUDED |

---

## Current View Definition

**File:** `app/migrations/cost-center/005_create_views.sql`

### view_cost_center_summary (Company Level)

```sql
CREATE VIEW `view_cost_center_summary` AS
SELECT
    'company' AS level,
    'RETAJ AL-DAWA' AS entity_name,
    CONCAT(fcc.period_year, '-', LPAD(fcc.period_month, 2, '0')) AS period,
    COALESCE(SUM(fcc.total_revenue), 0) AS kpi_total_revenue,
    ⚠️  COALESCE(SUM(fcc.total_cogs
                      + fcc.inventory_movement_cost
                      + fcc.operational_cost), 0) AS kpi_total_cost,
    -- ❌ sma_purchases NOT USED
    COALESCE(SUM(fcc.total_revenue - (fcc.total_cogs
                                       + fcc.inventory_movement_cost
                                       + fcc.operational_cost)), 0) AS kpi_profit_loss
FROM sma_fact_cost_center fcc
LEFT JOIN sma_dim_pharmacy dp ON fcc.warehouse_id = dp.warehouse_id
GROUP BY fcc.period_year, fcc.period_month
```

**Key Finding:** `sma_purchases` is NOT in the FROM or JOIN clauses

---

## Questions to Address

1. **Should `sma_purchases` be included?**

   - What data is in `sma_purchases`?
   - How does it relate to cost calculation?
   - Should it be summed per period?

2. **How should `sma_purchases` be joined?**

   - By warehouse_id? (Link to pharmacy/branch)
   - By date? (Group by period: YYYY-MM)
   - By supplier?

3. **Which column from `sma_purchases`?**

   - `total_amount`?
   - `cost_price`?
   - `quantity * unit_cost`?

4. **How is COGS calculated?**
   - Is `total_cogs` in `sma_fact_cost_center` already derived from `sma_purchases`?
   - Or are they separate metrics?

---

## Where Costs Come From

### sma_fact_cost_center Table Structure

```sql
TABLE sma_fact_cost_center {
    id                          -- Primary Key
    warehouse_id                -- Links to pharmacy/branch
    period_year                 -- 2025
    period_month                -- 10
    total_revenue               -- Sales ✓
    total_cogs                  -- Cost of Goods ✓
    inventory_movement_cost     -- Inventory Ops ✓
    operational_cost            -- Operational Expense ✓
    created_at
    updated_at
}
```

### Question: Where do these costs originate?

- **Is `total_cogs` calculated from `sma_purchases`?**

  - Likely YES (COGS = beginning inventory + purchases - ending inventory)
  - Then COGS is pre-calculated and stored in fact table

- **OR are both needed?**
  - Total Cost = COGS (from fact table) + actual Purchases (from sma_purchases)?
  - Then we're missing purchases!

---

## Current Cost Formula (Example)

### For Period 2025-10, Pharmacy 52

```sql
SELECT
    SUM(total_cogs) AS cogs,                              -- 324,400.40
    SUM(inventory_movement_cost) AS inventory,            -- 16,220.02
    SUM(operational_cost) AS operational,                 -- 32,440.04
    SUM(total_cogs + inventory_movement_cost +
        operational_cost) AS total_cost                   -- 373,060.46
FROM sma_fact_cost_center
WHERE warehouse_id = 52 AND period = '2025-10'
-- ❌ No sma_purchases included
```

### Result

```
Pharmacy 52 (October 2025):
  Revenue:              648,800.79 SAR
  Cost (current):       373,060.46 SAR (57.5% of revenue)
  Profit:               275,740.33 SAR (42.5% margin)

  ⚠️  Cost may be INCOMPLETE if sma_purchases not included!
```

---

## Data Relationship Diagram

```
Current Setup (WITHOUT sma_purchases):
════════════════════════════════════════

                    Period: 2025-10
                          ↓
    ┌─────────────────────────────────────────┐
    │  sma_fact_cost_center                   │
    │  ✓ total_revenue: 648,800.79            │
    │  ✓ total_cogs: 324,400.40               │
    │  ✓ inventory_movement_cost: 16,220.02   │
    │  ✓ operational_cost: 32,440.04          │
    └─────────────────────────────────────────┘
                       ↓ SUM
              Total Cost: 373,060.46

    ⚠️  What about actual PURCHASES?

Proposed Setup (WITH sma_purchases):
════════════════════════════════════════

                    Period: 2025-10
                          ↓
    ┌────────────────────────┐    ┌──────────────────────┐
    │ sma_fact_cost_center   │    │ sma_purchases        │
    │ ✓ total_cogs           │    │ ✓ purchase_amount    │
    │ ✓ inventory_cost       │    │ ✓ date               │
    │ ✓ operational_cost     │    │ ✓ warehouse_id       │
    └────────────────────────┘    └──────────────────────┘
              ↓                             ↓
              └─────────────────┬───────────┘
                               ↓
                    Total Cost (Combined)?
```

---

## Verification Steps

### Step 1: Check sma_purchases Table Structure

```bash
# Run in database:
DESCRIBE sma_purchases;

# Look for:
- id
- warehouse_id (or similar link to warehouse)
- total_amount (or cost/price columns)
- purchase_date / created_at
- supplier_id
```

### Step 2: Sample sma_purchases Data

```sql
SELECT
    id,
    warehouse_id,
    total_amount,
    DATE(purchase_date) as purchase_date,
    supplier_id
FROM sma_purchases
WHERE YEAR(purchase_date) = 2025
  AND MONTH(purchase_date) = 10
LIMIT 10;
```

### Step 3: Compare Totals

```sql
-- Fact table costs (current):
SELECT SUM(total_cogs) as fact_cogs
FROM sma_fact_cost_center
WHERE warehouse_id = 52 AND period = '2025-10';
-- Result: 324,400.40

-- Purchases table (potentially missing):
SELECT SUM(total_amount) as purchase_total
FROM sma_purchases
WHERE warehouse_id = 52
  AND YEAR(purchase_date) = 2025
  AND MONTH(purchase_date) = 10;
-- Result: ???

-- Are they same or different?
```

---

## Action Items

### Required Investigation

1. **Understand data model**

   - [ ] What's in `sma_purchases` table?
   - [ ] How does it relate to `sma_fact_cost_center`?
   - [ ] Are purchases already included in COGS?

2. **Verify business logic**

   - [ ] Should TOTAL COST = COGS + Inventory + Operational + Purchases?
   - [ ] Or is COGS derived from purchases (already included)?
   - [ ] Talk to business: "What should total cost include?"

3. **Check current data**

   - [ ] Query sma_purchases for Oct 2025
   - [ ] Compare purchase totals vs COGS in fact table
   - [ ] Are they related?

4. **Update if needed**
   - [ ] If sma_purchases should be included:
     - Update view: `005_create_views.sql`
     - Add JOIN to `sma_purchases`
     - Include purchase amount in cost calculation
   - [ ] Re-run migrations
   - [ ] Recalculate all KPIs

---

## Current Implementation Files

| File                                              | Purpose                            | Issue                            |
| ------------------------------------------------- | ---------------------------------- | -------------------------------- |
| `app/migrations/cost-center/005_create_views.sql` | Creates views for cost calculation | ❌ Doesn't include sma_purchases |
| `app/models/admin/Cost_center_model.php`          | Queries views                      | ❌ Uses incomplete views         |
| `themes/.../cost_center_dashboard_modern.php`     | Displays costs                     | ❌ Shows incomplete data         |

---

## Example: What Might Be Missing

### Scenario: October 2025, Pharmacy 52

**Current Calculation (Fact Table Only):**

```
Total Revenue:       648,800 SAR
COGS:                324,400 SAR (from fact table)
Inventory Cost:       16,220 SAR (from fact table)
Operational Cost:     32,440 SAR (from fact table)
────────────────────────────────
Total Cost:          373,060 SAR
Profit:              275,740 SAR
Margin:              42.5%
```

**If sma_purchases Were Included:**

```
Total Revenue:       648,800 SAR
COGS:                324,400 SAR (from fact table)
Actual Purchases:    ??? SAR (from sma_purchases - MISSING)
Inventory Cost:       16,220 SAR (from fact table)
Operational Cost:     32,440 SAR (from fact table)
────────────────────────────────
Total Cost:          ??? SAR (UNKNOWN - possibly higher!)
Profit:              ??? SAR (UNKNOWN - possibly lower!)
Margin:              ??? % (UNKNOWN - possibly lower!)
```

---

## Recommendation

**Before the dashboard goes to production:**

1. **Clarify with business stakeholders:**

   - "What should 'Total Cost' include?"
   - "Should purchases from sma_purchases be included?"
   - "How are purchases related to COGS?"

2. **If sma_purchases should be included:**

   - Update `005_create_views.sql`
   - Add JOIN to sma_purchases
   - Re-run all migrations
   - Update all KPI calculations
   - Re-test all functionality

3. **If sma_purchases should NOT be included:**
   - Document why (in code comments)
   - Mark as "intentional exclusion" in requirements

---

## Summary

| Item                | Current Status                       |
| ------------------- | ------------------------------------ |
| Revenue Calculation | ✅ Complete (from sales)             |
| COGS Calculation    | ✅ Included (from fact table)        |
| Inventory Cost      | ✅ Included (from fact table)        |
| Operational Cost    | ✅ Included (from fact table)        |
| Purchases Cost      | ❌ NOT INCLUDED (from sma_purchases) |
| **Overall:**        | ⚠️ Potentially incomplete            |

---

**Next Step:** Clarify with business whether sma_purchases should be included in cost calculations.

---

**Status:** ⚠️ **AWAITING DECISION**  
**Impact:** HIGH - Affects all financial metrics  
**Urgency:** HIGH - Must resolve before production
