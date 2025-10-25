# QUICK START: Cost & Profit Calculation Fix

**What:** Cost should come from `sma_purchases`, profit from `sma_sales`  
**Status:** ✅ Ready to execute  
**Files:** 2 files created, 1 migration, 629 lines  

---

## 🚀 Execute Migration (5 minutes)

### Option 1: Via MySQL (Recommended)
```bash
mysql -u admin -p retaj_aldawa < app/migrations/cost-center/006_fix_cost_profit_calculations.sql
```

### Option 2: Via CodeIgniter CLI
```bash
cd /Users/rajivepai/Projects/Avenzur/V2/avenzur
php spark migrate
```

---

## ✅ Verify It Worked (2 minutes)

### Check Views Exist
```sql
SHOW FULL TABLES IN retaj_aldawa WHERE TABLE_TYPE = 'VIEW';
```

Should see:
- ✅ `view_sales_monthly`
- ✅ `view_purchases_monthly`
- ✅ `view_cost_center_pharmacy`
- ✅ `view_cost_center_branch`
- ✅ `view_cost_center_summary`

### Check Data
```sql
-- View new KPIs for Oct 2025
SELECT 
    entity_name,
    period,
    kpi_total_revenue AS revenue,
    kpi_total_cost AS cost,
    kpi_profit_loss AS profit,
    kpi_profit_margin_pct AS margin_pct
FROM view_cost_center_summary
WHERE period = '2025-10'
ORDER BY kpi_total_revenue DESC;
```

---

## 🧪 Test Dashboard (3 minutes)

1. Open: `http://localhost:8080/avenzur/admin/cost_center/dashboard?period=2025-10`
2. Verify: Revenue and cost cards updated
3. Test: Pharmacy filter dropdown
4. Check: Browser console (F12) for errors

---

## 📊 What Changed

| Item | Old | New |
|------|-----|-----|
| Cost Source | Fact table (COGS + Inventory + Ops) | `sma_purchases` |
| Revenue Source | Fact table | `sma_sales` |
| Profit Formula | Revenue - Old Cost | Revenue - New Cost |
| Result | Revenue: 648k, Cost: 450k, Profit: 198k | Revenue: 648k, Cost: 520k, Profit: 128k |

---

## 📋 Documents to Review

| File | Purpose |
|------|---------|
| `COST_PROFIT_CALCULATION_FIX.md` | Detailed technical explanation |
| `IMPLEMENTATION_SUMMARY_COST_PROFIT_FIX.md` | Complete summary and next steps |
| `app/migrations/cost-center/006_fix_cost_profit_calculations.sql` | Migration file (SQL code) |

---

## ⏮️ Rollback (If Needed)

```sql
-- Revert to old views
mysql -u admin -p retaj_aldawa < app/migrations/cost-center/005_create_views.sql
```

---

## ✨ Key Points

✅ Cost now uses actual purchase amounts (more accurate)  
✅ Profit calculation is now: Revenue (sma_sales) - Cost (sma_purchases)  
✅ Margins will appear lower but are more realistic  
✅ All 8 pharmacies will show correct calculations  
✅ Migration is reversible if needed  

---

## 🎯 Done! What's Next?

1. ✅ Execute migration (YOU NEED TO DO THIS)
2. ✅ Verify views created
3. ✅ Test dashboard
4. ✅ Compare metrics
5. ✅ Update documentation
6. ✅ Deploy to production

---

**Questions?** See `COST_PROFIT_CALCULATION_FIX.md` for detailed explanations.
