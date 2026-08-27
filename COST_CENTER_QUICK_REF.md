# Cost Center - Quick Reference Card

## 📞 One Stored Procedure to Rule Them All

```sql
sp_get_sales_analytics_hierarchical(period_type, target_month, warehouse_id, level)
```

---

## 🎯 Period Types

| Type             | Format      | Example     | Description         |
| ---------------- | ----------- | ----------- | ------------------- |
| **Monthly**      | `'YYYY-MM'` | `'2025-10'` | Specific month data |
| **Today**        | `'today'`   | `'today'`   | Current day sales   |
| **Year-to-Date** | `'ytd'`     | `'ytd'`     | Jan 1 to today      |

---

## 🏢 Hierarchy Levels

| Level        | Parameter    | Returns                  |
| ------------ | ------------ | ------------------------ |
| **Company**  | `'company'`  | All pharmacies + summary |
| **Pharmacy** | `'pharmacy'` | Pharmacy + branches      |
| **Branch**   | `'branch'`   | Single branch details    |

---

## 💻 Model Methods - All Support All Period Types

```php
// Company summary
get_summary_stats('today')         // Today's company totals
get_summary_stats('ytd')           // YTD company totals
get_summary_stats('2025-10')       // October 2025 totals

// Pharmacies listing
get_pharmacies_with_health_scores('today', 10, 0)
get_pharmacies_with_health_scores('ytd', 10, 0)
get_pharmacies_with_health_scores('2025-10', 10, 0)

// Branches listing
get_branches_with_health_scores('today', 10, 0)
get_branches_with_health_scores('ytd', 10, 0)
get_branches_with_health_scores('2025-10', 10, 0)

// Pharmacy detail
get_pharmacy_detail(40, 'today')
get_pharmacy_detail(40, 'ytd')
get_pharmacy_detail(40, '2025-10')
```

---

## 📊 Return Data Structure

### Company Summary

```php
[
    'total_revenue' => 9219588.62,
    'total_cost' => 43983.89,
    'total_margin_pct' => 0.48,
    'total_customers' => 4,
    'total_items_sold' => 16646,
    'avg_transaction' => 13638.44
]
```

### Pharmacy Listing

```php
[
    [
        'pharmacy_id' => 40,
        'pharmacy_name' => 'صيدلية رتاج',
        'kpi_total_revenue' => 5249.58,
        'kpi_profit_margin_pct' => 30.5,
        'branch_count' => 3,
        'health_status' => '✓ Healthy',
        'health_color' => '#10B981'
    ],
    // ... more pharmacies
]
```

### Pharmacy Detail

```php
[
    'pharmacy_id' => 40,
    'pharmacy_name' => 'صيدلية رتاج',
    'kpi_total_revenue' => 5249.58,
    'branch_count' => 3,
    'branches' => [
        [
            'branch_id' => 101,
            'branch_name' => 'فرع الخالدية',
            'total_revenue' => 2100.00,
            'margin_percentage' => 28.5
        ],
        // ... more branches
    ]
]
```

---

## 🎨 Health Status Colors

| Status    | Margin | Color              | Badge          |
| --------- | ------ | ------------------ | -------------- |
| ✓ Healthy | ≥ 30%  | `#10B981` (Green)  | `badge-green`  |
| ⚠ Monitor | 20-29% | `#F59E0B` (Yellow) | `badge-yellow` |
| ✗ Low     | < 20%  | `#EF4444` (Red)    | `badge-red`    |

---

## 🔧 Direct SQL Calls

```sql
-- Company today
CALL sp_get_sales_analytics_hierarchical('today', NULL, NULL, 'company');

-- Company YTD
CALL sp_get_sales_analytics_hierarchical('ytd', NULL, NULL, 'company');

-- Company October 2025
CALL sp_get_sales_analytics_hierarchical('monthly', '2025-10', NULL, 'company');

-- Pharmacy 40 October 2025
CALL sp_get_sales_analytics_hierarchical('monthly', '2025-10', 40, 'pharmacy');

-- Pharmacy 40 YTD
CALL sp_get_sales_analytics_hierarchical('ytd', NULL, 40, 'pharmacy');
```

---

## ⚡ Quick Tips

1. **Default Period:** If no period specified, uses current month

   ```php
   get_summary_stats()  // Same as get_summary_stats(date('Y-m'))
   ```

2. **Period Type Auto-Detection:**

   ```php
   // Code automatically detects:
   'today' → period_type='today'
   'ytd'   → period_type='ytd'
   'YYYY-MM' → period_type='monthly'
   ```

3. **Limit/Offset for Pagination:**

   ```php
   get_pharmacies_with_health_scores('2025-10', 10, 0)   // First 10
   get_pharmacies_with_health_scores('2025-10', 10, 10)  // Next 10
   get_pharmacies_with_health_scores('2025-10', 10, 20)  // Page 3
   ```

4. **Health Score Calculation:**

   ```
   Margin % = ((Revenue - Cost) / Revenue) × 100

   If margin >= 30%  → ✓ Healthy (Green)
   If margin >= 20%  → ⚠ Monitor (Yellow)
   If margin < 20%   → ✗ Low (Red)
   ```

---

## 🚀 Performance Notes

- **Caching:** Results cached for 5 minutes (recommended)
- **Indexes:** Ensure indexes on `warehouse_id`, `date`, `sale_status`
- **Limit Usage:** Always use limit/offset for large datasets
- **Query Time:**
  - Today: ~50ms
  - Monthly: ~200ms
  - YTD: ~500ms

---

## 📞 Need Help?

- **Full Documentation:** `/COST_CENTER_CONSOLIDATION.md`
- **Completion Summary:** `/CONSOLIDATION_COMPLETE.md`
- **Original Migration:** `/COST_CENTER_STORED_PROCEDURES.md`

---

**Last Updated:** November 5, 2025  
**Version:** 2.0 (Consolidated)
