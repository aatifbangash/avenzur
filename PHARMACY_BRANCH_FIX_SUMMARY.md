# Pharmacy & Branch Data Fix - Implementation Summary

**Date:** October 25, 2025  
**Status:** ✅ COMPLETE

## Problem

The Cost Center Dashboard was only showing warehouse data instead of actual pharmacies and branches. The view was relying on the `sma_dim_pharmacy` dimension table which only had references to 3 warehouses (Main Warehouse, Expiry Store, Goods In Transit).

## Solution

Updated the Cost Center Model to fetch data directly from `sma_warehouses` table with proper hierarchy mapping:

- **Pharmacies**: `warehouse_type = 'pharmacy'` (8 pharmacies)
- **Branches**: `warehouse_type = 'branch'` (9 branches) with parent_id linking to pharmacy

## Changes Made

### 1. Cost_center_model.php

Updated `get_pharmacies_with_health_scores()` method:

- ✅ Changed from `view_cost_center_pharmacy` to direct query on `sma_warehouses`
- ✅ Filters: `warehouse_type = 'pharmacy'` AND `id NOT IN (32, 48, 51)` (excludes special warehouses)
- ✅ Joins with `sma_fact_cost_center` for KPI data
- ✅ Joins with branches (LEFT JOIN for branch count)
- ✅ Returns 8 actual pharmacies instead of 3
- ✅ Includes health_status text ('✓ Healthy', '⚠ Monitor', '✗ Low')
- ✅ Includes health_color hex code for badges

Added new `get_branches_with_health_scores()` method:

- ✅ Fetches all 9 branches
- ✅ Links to parent pharmacy via `parent_id`
- ✅ Similar KPI calculations as pharmacies
- ✅ Includes health scoring with text and color

### 2. Cost_center.php Controller

Enhanced dashboard() method:

- ✅ Added call to `get_branches_with_health_scores()`
- ✅ Passes branches data to view via `$view_data['branches']`

### 3. cost_center_dashboard_modern.php View

- ✅ Added branches to dashboardData object: `branches: <?php echo json_encode($branches ?? []); ?>`
- ✅ Branches data now available in JavaScript for future enhancements

## Data Now Displayed

### Pharmacies (Actual Results)

```
PHR-001 Avenzur Downtown Pharmacy       → 2 branches
PHR-002 Avenzur Northgate Pharmacy      → 1 branch
PHR-003 Avenzur Southside Pharmacy      → 2 branches
PHR-004 E&M Central Plaza Pharmacy      → 1 branch
PHR-005 E&M Midtown Pharmacy            → 2 branches
PHR-006 HealthPlus Main Street Pharmacy → 1 branch
PHR-0101 Rawabi North Pharma            → 0 branches
PHR-011 Rawabi South                    → 0 branches
```

### Branches (Sample)

```
Avenzur Downtown - Main Branch          → PHR-001
Avenzur Downtown - Express Branch       → PHR-001
Avenzur Northgate - Main Branch         → PHR-002
Avenzur Southside - Main Branch         → PHR-003
[... 5 more branches]
```

## Database Hierarchy

```
sma_warehouses
├── warehouse_type = 'warehouse' (Main Warehouse, Expiry Store, Goods In Transit)
├── warehouse_type = 'pharmacy' (8 records)
│   └── warehouse_type = 'branch' (9 records, linked via parent_id)
```

## KPI Data

- **Revenue**: `SUM(fcc.total_revenue)`
- **Cost**: `SUM(fcc.total_cogs + fcc.inventory_movement_cost + fcc.operational_cost)`
- **Profit**: `SUM(total_revenue - total_cost)`
- **Margin %**: `(profit / revenue) * 100`
- **Health Score**: Uses margin >= 30% (Healthy), >= 20% (Monitor), < 20% (Low)

## Testing Status

- ✅ Query validated with sample period (2025-10)
- ✅ All 8 pharmacies returned
- ✅ Branch parent_id mapping verified
- ✅ Health status logic working
- ⏳ Dashboard rendering (next step)
- ⏳ Toggle and charts (next step)

## Next Steps

1. Test dashboard loads with new pharmacy/branch data
2. Verify charts render with real data
3. Test margin toggle functionality
4. Verify health badges display correctly
5. Test drill-down navigation
6. Spot-check calculations against expected values

---

**Files Modified:**

- `/app/models/admin/Cost_center_model.php`
- `/app/controllers/admin/Cost_center.php`
- `/themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`
