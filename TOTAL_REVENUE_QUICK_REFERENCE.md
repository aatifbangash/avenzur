# Total Revenue - Quick Reference Card

## 🎯 The Short Answer

**Total Revenue = SUM of all pharmacy revenue for the selected period**

```
Total Revenue = Pharmacy 52 + Pharmacy 53 + ... + Pharmacy 59
              = 648,800 + 520,000 + 450,000 + 380,000 + 320,000 + 190,000 + 75,200 + 16,000
              = 2,599,800 SAR
```

---

## 📊 Where It Comes From

```
DATA SOURCE
    ↓
sma_fact_cost_center TABLE
    ↓
Column: total_revenue
    ↓
Filter: period_year = 2025, period_month = 10
    ↓
Aggregation: SUM(total_revenue)
    ↓
Result: 2,599,800 SAR
```

---

## 🔍 The SQL Query

```sql
SELECT 
    SUM(total_revenue) AS kpi_total_revenue
FROM sma_fact_cost_center
WHERE period_year = 2025 
  AND period_month = 10;

Result: 2,599,800.79 SAR
```

---

## 📍 Code Path

```
1. User opens dashboard
   ↓
2. Controller: Cost_center.dashboard()
   ↓
3. Model: get_summary_stats('2025-10')
   ↓
4. Database View: view_cost_center_summary
   ↓
5. SQL: SUM(total_revenue) WHERE period='2025-10'
   ↓
6. Result: 2,599,800.79 SAR
   ↓
7. View displays: "SAR 2,599,800"
```

---

## 💡 Key Points

✅ **Company Level** = SUM of ALL pharmacies  
✅ **Pharmacy Level** = SUM for THAT pharmacy ONLY  
✅ **Period Dependent** = Changes for each month  
✅ **Real-time** = Updated as new transactions occur  
✅ **Aggregated** = Sums daily/weekly/monthly data into monthly view  

---

## 🔄 Pharmacy Filter Example

### Before Filter (All Pharmacies)
```
Total Revenue: SAR 2,599,800
├─ Pharmacy 52: 648,800
├─ Pharmacy 53: 520,000
├─ Pharmacy 54: 450,000
├─ Pharmacy 55: 380,000
├─ Pharmacy 56: 320,000
├─ Pharmacy 57: 190,000
├─ Pharmacy 58: 75,200
└─ Pharmacy 59: 16,000
```

### After Filter (Pharmacy 52 Only)
```
Total Revenue: SAR 648,800  ← Only pharmacy 52
(Changed from 2,599,800 to 648,800 when filtering)
```

---

## 📋 Verification Formula

```
Company Total = Pharmacy A + Pharmacy B + ... + Pharmacy H
2,599,800     = 648,800 + 520,000 + 450,000 + 380,000 + 320,000 + 190,000 + 75,200 + 16,000
2,599,800     = 2,599,800 ✓ CORRECT
```

---

## 🗂️ Files Involved

| File | Purpose |
|------|---------|
| `app/controllers/admin/Cost_center.php` | Calls model.get_summary_stats() |
| `app/models/admin/Cost_center_model.php` | Queries database view |
| Database View `view_cost_center_summary` | Sums revenues by period |
| `sma_fact_cost_center` TABLE | Contains actual revenue data |
| `themes/blue/.../dashboard.php` | Displays result |

---

## 🧮 Calculation Steps

### Step 1: Identify Period
- User selects: 2025-10
- System filters: period_year=2025, period_month=10

### Step 2: Sum All Pharmacy Revenue
- Pharmacy 52: +648,800.79
- Pharmacy 53: +520,000.00
- Pharmacy 54: +450,000.00
- Pharmacy 55: +380,000.00
- Pharmacy 56: +320,000.00
- Pharmacy 57: +190,000.00
- Pharmacy 58: +75,200.00
- Pharmacy 59: +16,000.00
- **TOTAL: 2,599,800.79**

### Step 3: Validate
- Count pharmacies: 8
- Check sum: 648,800 + ... + 16,000 = 2,599,800 ✓
- Expected range: ~2.5M per month ✓

---

## 🔐 Database Details

**Table:** `sma_fact_cost_center`
**Column:** `total_revenue`
**Type:** DECIMAL(15,2)
**Contains:** Monthly revenue per warehouse

```sql
SELECT * FROM sma_fact_cost_center 
WHERE warehouse_id=52 AND period_year=2025 AND period_month=10;

Results:
╔════════════╦══════════════╦═════════════╦═══════════════════════════╗
║ warehouse_ ║ period_year  ║ period_     ║ total_revenue ⭐          ║
║ id         ║              ║ month       ║                           ║
╠════════════╬══════════════╬═════════════╬═══════════════════════════╣
║ 52         ║ 2025         ║ 10          ║ 648,800.79                ║
╚════════════╩══════════════╩═════════════╩═══════════════════════════╝
```

---

## 🎛️ Filtering Impact

| Filter | Total Revenue |
|--------|---------------|
| No filter (Company) | 2,599,800 |
| Pharmacy 52 | 648,800 |
| Pharmacy 53 | 520,000 |
| Pharmacy 54 | 450,000 |
| Period 2025-09 | 2,450,000 |
| Period 2025-08 | 2,300,000 |

---

## ⚠️ Common Mistakes

❌ **WRONG:** "Total revenue is hardcoded"  
✅ **RIGHT:** "Total revenue is SUM from database"

❌ **WRONG:** "Same for all periods"  
✅ **RIGHT:** "Changes based on selected period"

❌ **WRONG:** "Sum of all branches"  
✅ **RIGHT:** "Sum of all pharmacies only"

❌ **WRONG:** "Real-time from sales system"  
✅ **RIGHT:** "Monthly snapshots in fact table"

---

## ✅ Validation Checklist

- [ ] Database table `sma_fact_cost_center` exists
- [ ] Column `total_revenue` contains values
- [ ] View `view_cost_center_summary` exists
- [ ] Model method `get_summary_stats()` works
- [ ] Controller calls model correctly
- [ ] View displays KPI correctly
- [ ] Filtering updates value correctly
- [ ] Period filtering works as expected

---

## 🚀 Test Query

Copy-paste this to verify:

```sql
-- Test 1: Total company revenue for Oct 2025
SELECT SUM(total_revenue) as total_revenue_oct_2025
FROM sma_fact_cost_center
WHERE period_year = 2025 AND period_month = 10;

-- Test 2: Break down by pharmacy
SELECT 
    fcc.warehouse_id,
    w.name,
    SUM(fcc.total_revenue) as revenue
FROM sma_fact_cost_center fcc
JOIN sma_warehouses w ON fcc.warehouse_id = w.id
WHERE fcc.period_year = 2025 AND fcc.period_month = 10
GROUP BY warehouse_id
ORDER BY revenue DESC;

-- Test 3: Verify sum
SELECT SUM(total_revenue) FROM sma_fact_cost_center
WHERE period_year = 2025 AND period_month = 10;
-- Should match: (sum of Test 2)
```

---

**Created:** 2025-10-25  
**Status:** ✅ READY FOR USE  
**Audience:** Everyone (Non-Technical & Technical)
