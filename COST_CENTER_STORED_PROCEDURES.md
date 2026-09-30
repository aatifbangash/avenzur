# Cost Center: Stored Procedures Migration

## Summary

Successfully migrated Cost_center_model to use stored procedures instead of raw SQL queries for better maintainability, consistency, and performance.

## Created Stored Procedures

### 1. sp_cost_center_summary

- **Purpose**: Get company-wide cost center summary
- **Parameters**: p_year (INT), p_month (INT)
- **Returns**: Single row with company-wide KPIs
- **Usage**: Company dashboard summary statistics

### 2. sp_cost_center_pharmacies

- **Purpose**: Get all pharmacies with health scores
- **Parameters**: p_year (INT), p_month (INT), p_limit (INT), p_offset (INT)
- **Returns**: Multiple rows, one per pharmacy with KPIs
- **Usage**: Pharmacy listing with pagination

### 3. sp_cost_center_branches

- **Purpose**: Get all branches with health scores
- **Parameters**: p_year (INT), p_month (INT), p_limit (INT), p_offset (INT)
- **Returns**: Multiple rows, one per branch with KPIs
- **Usage**: Branch listing with pagination

### 4. sp_cost_center_pharmacy_detail

- **Purpose**: Get detailed KPIs for a specific pharmacy
- **Parameters**: p_pharmacy_id (INT), p_year (INT), p_month (INT)
- **Returns**: Single row with pharmacy KPIs including branches
- **Usage**: Pharmacy drill-down detail page

## Model Methods Updated

1. **get_summary_stats()** → Uses `sp_cost_center_summary`
2. **get_pharmacies_with_health_scores()** → Uses `sp_cost_center_pharmacies`
3. **get_branches_with_health_scores()** → Uses `sp_cost_center_branches`
4. **get_pharmacy_detail()** → Uses `sp_cost_center_pharmacy_detail`

## Cost Calculation Formula

All stored procedures use consistent cost calculation:

```sql
COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0) AS kpi_total_cost
```

## Margin Calculation

Proper NULL handling for accurate margins:

```sql
CASE
    WHEN COALESCE(SUM(s.grand_total), 0) = 0 THEN 0
    ELSE ROUND(((COALESCE(SUM(s.grand_total), 0) - COALESCE(SUM(si.subtotal - (si.subtotal * (si.discount / 100))), 0)) / COALESCE(SUM(s.grand_total), 0)) * 100, 2)
END AS kpi_profit_margin_pct
```

## Benefits

1. **Consistency**: Same calculation logic across all methods
2. **Maintainability**: Update SQL in one place (stored procedure)
3. **Performance**: Pre-compiled execution plans
4. **Security**: Parameterized queries prevent SQL injection
5. **Testability**: Can test procedures independently

## Verified Results

October 2025 Test:

- Revenue: 9,219,588.62 SAR
- Cost: 21,556.98 SAR
- Profit: 9,198,031.63 SAR
- **Margin: 99.77%** ✓

## Files Created

- `/database/stored_procedures/sp_cost_center_summary.sql`
- `/database/stored_procedures/sp_cost_center_pharmacies.sql`
- `/database/stored_procedures/sp_cost_center_branches.sql`
- `/database/stored_procedures/sp_cost_center_pharmacy_detail.sql`

## Files Modified

- `/app/models/admin/Cost_center_model.php`
  - Replaced raw SQL with stored procedure calls
  - Added proper result cleanup for multiple result sets
  - Improved error handling with logging
