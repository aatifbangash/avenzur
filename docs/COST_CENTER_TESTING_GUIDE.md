# Cost Center Dashboard - Manual Testing Guide

## Quick Start

1. **Login** to the admin panel
2. **Navigate** to: Menu → Cost Centre (or directly to `/admin/cost_center/dashboard`)
3. **Verify** the page loads with styling applied

## What You Should See

### Dashboard Page Elements ✅

- Page title: "Cost Center Dashboard" with pie chart icon
- Subtitle: "Monitor pharmacy budget allocation and spending"
- Period selector dropdown in top right with current period selected
- Refresh button next to period selector

### KPI Cards Row (4 cards) ✅

1. **Total Revenue** (Blue/Primary)

   - Shows total revenue amount in SAR format
   - Displays pharmacy count
   - Example: "617,810.52 SAR | 1 pharmacies"

2. **Total Cost** (Red/Danger)

   - Shows total cost amount
   - Shows cost as % of revenue
   - Example: "0.00 SAR | 0.0% of revenue"

3. **Total Profit** (Green/Success)

   - Shows profit amount
   - Shows profit as % of revenue
   - Example: "617,810.52 SAR | 100.0% of revenue"

4. **Profit Margin %** (Purple/Info)
   - Shows margin percentage
   - Displays trend indicator
   - Example: "100.00%"

### Pharmacy Table ✅

- Columns: Pharmacy Name | Code | Revenue | Cost | Profit | Margin %
- Shows 1+ rows for each pharmacy
- Table should be sortable by clicking column headers
- Rows should be clickable to drill-down to pharmacy detail

### Period Selector ✅

- Dropdown with "-- Select Period --" placeholder
- Shows formatted months: "Sep 2025", "Oct 2025", etc.
- Current period should be pre-selected

## Testing Workflows

### Test 1: Page Loads Without Errors

```
1. Navigate to /admin/cost_center/dashboard
2. Wait for page to load (should be instant)
3. Check browser console - NO red errors
4. Page should have full styling (colors, fonts, spacing)
```

### Test 2: KPI Cards Display Data

```
1. Verify all 4 KPI cards are visible
2. Verify numbers are formatted with commas (e.g., "617,810.52")
3. Verify SAR currency is shown
4. Verify all cards have colored backgrounds
```

### Test 3: Pharmacy Table Shows Data

```
1. Verify table header row is visible
2. Verify at least 1 pharmacy row is displayed
3. Verify columns align and are readable
4. Verify row has: Name, Code, Revenue, Cost, Profit, Margin %
```

### Test 4: Period Selector Works

```
1. Open period dropdown
2. Verify "Sep 2025" and "Oct 2025" options visible
3. Select different period
4. Observe page updates (KPI cards, table may change)
5. Current period should show as selected
```

### Test 5: Drill-Down to Pharmacy

```
1. Click on any pharmacy row in table
2. Page should navigate to /admin/cost_center/pharmacy/{id}
3. Breadcrumb should show: Dashboard > Pharmacy Name
4. Should see pharmacy KPI cards
5. Should see list of branches for that pharmacy
```

### Test 6: Drill-Down to Branch

```
1. From pharmacy detail, click on any branch row
2. Page should navigate to /admin/cost_center/branch/{id}
3. Breadcrumb should show: Dashboard > Pharmacy > Branch Name
4. Should see branch KPI cards
5. Should see cost breakdown chart
```

## Expected Browser Behavior

### Chrome DevTools Inspection

- Network tab: All assets should load with 200 status
- Console: No red error messages
- Elements: Should see proper HTML structure with Bootstrap classes

### Responsive Design

- Desktop (1920px): Full dashboard visible
- Tablet (768px): Single column, cards stack vertically
- Mobile (375px): Optimized for small screen

## Data Values Reference

**Company Level (2025-10):**

- Total Revenue: 617,810.52 SAR
- Total Cost: 0.00 SAR
- Total Profit: 617,810.52 SAR
- Profit Margin: 100.00%
- Pharmacy Count: 1

**Company Level (2025-09):**

- Total Revenue: 648,800.79 SAR
- Total Cost: 0.00 SAR
- Total Profit: 648,800.79 SAR
- Profit Margin: 100.00%
- Pharmacy Count: 1

## Troubleshooting

### Problem: Page shows blank white screen

**Solution:** Check browser console for errors, reload page

### Problem: Page is loading but no styling/colors

**Solution:** CSS didn't load - check network tab, clear browser cache and reload

### Problem: Error messages appear in red

**Solution:** Check `app/logs/log-*.php` for detailed error messages

### Problem: Period dropdown empty

**Solution:** Database views may be missing - verify all migrations were run

### Problem: Pharmacy table empty

**Solution:** Check database - verify `view_cost_center_pharmacy` has data:

```sql
SELECT * FROM view_cost_center_pharmacy LIMIT 1;
```

### Problem: 500 error when clicking pharmacy

**Solution:** Check if pharmacy ID exists in database

## Logs to Check

Location: `/app/logs/log-2025-10-25.php`

Look for entries starting with:

- `[COST_CENTER]` - Debug logging from controller
- `ERROR` - Any errors that occurred

Example good log entry:

```
DEBUG - 2025-10-25 09:52:00 --> Total execution time: 0.4171
```

Example bad log entry:

```
ERROR - 2025-10-25 09:26:23 --> Severity: error --> Exception: ...
```

## Success Criteria

✅ All items below should be confirmed:

- [ ] Dashboard loads without 500 error
- [ ] Page has proper styling (colors, fonts, spacing)
- [ ] KPI cards display with data
- [ ] Pharmacy table displays with data
- [ ] Period selector works and filters data
- [ ] Can click pharmacy row to drill-down
- [ ] Pharmacy detail page loads
- [ ] Can click branch row to drill-down
- [ ] Branch detail page loads
- [ ] No red errors in browser console
- [ ] No errors in application logs

---

**All checks above passing = Dashboard is working correctly! 🎉**
