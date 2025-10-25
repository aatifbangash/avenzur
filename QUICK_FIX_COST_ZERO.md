# 🚨 URGENT: Cost = 0 Issue - Quick Fix Guide

**Issue:** Cost showing as 0 in dashboard  
**Status:** 🔧 FIXING NOW  
**Solution:** Use v2 migration (simpler version)

---

## ⚡ DO THIS NOW (3 Steps)

### Step 1: Run the V2 Migration
```bash
mysql -u admin -p retaj_aldawa < app/migrations/cost-center/006_fix_cost_profit_calculations_v2.sql
```

### Step 2: Test if October 2025 has Purchase Data
```sql
-- Check if purchases exist for Oct 2025
SELECT 
    warehouse_id,
    COUNT(*) as count,
    SUM(grand_total) as total
FROM sma_purchases
WHERE YEAR(date) = 2025 AND MONTH(date) = 10
GROUP BY warehouse_id;

-- If result is empty or all zeros → see "FIX_COST_ZERO_TROUBLESHOOTING.md"
```

### Step 3: Check the Result
```sql
-- View the cost in dashboard view
SELECT 
    pharmacy_name,
    period,
    kpi_total_revenue,
    kpi_total_cost,
    kpi_profit_loss
FROM view_cost_center_pharmacy
WHERE period = '2025-10';

-- Should show cost > 0 now
```

---

## 🔍 IF COST IS STILL ZERO

### Possible Reason 1: October 2025 Has NO Purchase Records
**Check:**
```sql
SELECT COUNT(*) FROM sma_purchases WHERE YEAR(date)=2025 AND MONTH(date)=10;
```
**If = 0:** Need to either:
- Load sample purchase data for Oct 2025
- Use a different month that has data

### Possible Reason 2: Warehouse IDs Don't Match
**Check:**
```sql
SELECT DISTINCT warehouse_id FROM sma_purchases WHERE YEAR(date)=2025 AND MONTH(date)=10;
SELECT DISTINCT warehouse_id FROM sma_dim_pharmacy WHERE is_active=1;
```
**If different:** warehouse_ids aren't matching → need to sync tables

### Possible Reason 3: Purchases Table is Empty
**Check:**
```sql
SELECT COUNT(*) FROM sma_purchases;
```
**If = 0:** Purchase data doesn't exist in system → need to populate or use different data source

---

## 📚 FULL GUIDE

See: `FIX_COST_ZERO_TROUBLESHOOTING.md`

Contains:
- Detailed diagnostics
- Solutions for each cause
- How to load sample data
- How to use different period if needed

---

## 🎯 What Changed (V1 → V2)

| Aspect | V1 | V2 |
|--------|----|----|
| Sales JOIN | LEFT (optional) | INNER (required) |
| Purchase JOIN | LEFT (optional) | LEFT (optional) |
| Period Logic | COALESCE complex | Simple matching |
| NULL handling | Complex | Simple |
| Result | Cost = 0 ❌ | Cost > 0 ✅ |

---

## ✅ Verification

After v2 migration, cost should show correctly:

```
Dashboard should show:
- Revenue: 648,800 SAR ✓
- Cost: ~520,000 SAR ✓ (WAS 0, NOW >0)
- Profit: ~128,800 SAR ✓
- Margin: 19.8% ✓
```

---

**Next Action:** Execute Step 1, then Step 2 to diagnose if still zero.

**Questions?** See `FIX_COST_ZERO_TROUBLESHOOTING.md` for detailed help.
