# Pharmacy Filter - Quick Test Guide

## Testing Pharmacy Filter Functionality

### Step 1: Check Dashboard

1. Go to: `http://localhost/admin/cost_center/dashboard?period=2025-10`
2. Should show:
   - Company totals in KPI cards (all pharmacies)
   - 8 pharmacies in the table
   - Pharmacy dropdown populated

### Step 2: Test Pharmacy Selection

1. Click pharmacy dropdown
2. Select "E&M Central Plaza Pharmacy (PHR-004)"
3. Observe:
   - ✅ KPI cards update with pharmacy-specific numbers
   - ✅ Table filters to show only that pharmacy
   - ✅ Charts update with pharmacy data
   - ✅ Revenue should match pharmacy's revenue (not company total)
   - ✅ Margins recalculate for selected pharmacy

### Step 3: Direct API Test

```bash
# Test the new API endpoint
curl "http://localhost/api/v1/cost-center/pharmacy-detail/52?period=2025-10"

# Expected response:
{
    "success": true,
    "data": {
        "pharmacy_id": 52,
        "pharmacy_name": "E&M Central Plaza Pharmacy",
        "kpi_total_revenue": 648800.79,  # This pharmacy only
        "kpi_profit_margin_pct": 42.45,  # This pharmacy only
        ...
    }
}
```

### Step 4: Verify Numbers Match

**Company Dashboard (Default):**

- Total Revenue: Sum of all pharmacies
- Example: Pharmacy 1 (100,000) + Pharmacy 2 (80,000) + ... = 648,800.79

**Filtered (Select Pharmacy 1):**

- Revenue: Only Pharmacy 1 = 100,000
- This value comes from: `SUM(total_revenue) WHERE warehouse_id = 52`

### Step 5: Browser Console Check

1. Open DevTools (F12) → Console tab
2. Select pharmacy
3. Check logs:
   ```
   Fetching pharmacy detail for ID: 52
   Pharmacy detail response: {success: true, data: {...}}
   ```

### Step 6: Test Reset

1. Select "All Pharmacies" or click reset
2. Verify:
   - KPI cards show company totals again
   - Table shows all 8 pharmacies
   - Numbers match Step 4 totals

## Common Issues & Troubleshooting

### Issue: Dropdown shows only warehouses

**Solution:** Model now correctly filters by `warehouse_type = 'pharmacy'`

### Issue: KPI cards don't update

**Check:**

1. Browser console for errors
2. API endpoint returning data: `GET /api/v1/cost-center/pharmacy-detail/52`
3. Network tab shows 200 response

### Issue: Revenue doesn't match

**Verify:**

- Company view: Sum across all warehouses
- Pharmacy filter: Only one warehouse ID
- Period is consistent

### Issue: Data shows "0" or "NULL"

**Check:**

- Selected period has data in `sma_fact_cost_center`
- Pharmacy warehouse ID is correct (52, 53, 54, etc.)
- Not selecting warehouse type 'warehouse' (only 'pharmacy')

## SQL Query to Verify Data

```sql
-- Check what pharmacies exist
SELECT id, code, name, warehouse_type FROM sma_warehouses
WHERE warehouse_type = 'pharmacy';

-- Check data for October 2025
SELECT DISTINCT warehouse_id, SUM(total_revenue) as revenue
FROM sma_fact_cost_center
WHERE period_year = 2025 AND period_month = 10
GROUP BY warehouse_id;

-- Check specific pharmacy
SELECT SUM(total_revenue), SUM(total_cogs),
       SUM(inventory_movement_cost), SUM(operational_cost)
FROM sma_fact_cost_center
WHERE warehouse_id = 52 AND period_year = 2025 AND period_month = 10;
```

## Files Modified

1. **Model:** `app/models/admin/Cost_center_model.php`

   - Added `get_pharmacy_detail()` method

2. **API:** `app/controllers/api/v1/Cost_center.php`

   - Added `pharmacy_detail_get()` endpoint
   - URL: `/api/v1/cost-center/pharmacy-detail/{id}`

3. **View:** `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`
   - Enhanced `handlePharmacyFilter()` to fetch and update KPIs
   - Added error handling

## Expected Output

### Pharmacy Filter Test Result

```
✅ Pharmacy Selection: Works
✅ KPI Cards Update: Shows pharmacy-specific numbers
✅ Revenue Filtered: Shows only pharmacy revenue
✅ Margins Recalculated: Gross & Net for pharmacy
✅ Table Filtered: Shows 1 pharmacy (selected)
✅ Charts Updated: Reflect pharmacy data
✅ Reset Works: Returns to company totals
```

## Performance Notes

- **Company Summary:** Loads once on page load
- **Pharmacy Detail:** Loaded on demand via API (50-100ms)
- **No real-time sync:** Data from fact table (immutable)
- **Network:** Single API call per filter change

## Next Steps

1. ✅ Model method added
2. ✅ API endpoint added
3. ✅ Filter handler updated
4. ⏳ Manual testing in browser
5. ⏳ Verify calculations are correct
6. ⏳ Test all 8 pharmacies
7. ⏳ Deploy to production
