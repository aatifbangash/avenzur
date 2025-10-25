# Browser Testing Checklist & Troubleshooting

**Date:** 2025-10-25  
**Purpose:** Systematic testing guide for Cost Center Dashboard

---

## ⏱️ Pre-Testing Checklist

- [ ] You have admin access/login
- [ ] Browser is Chrome, Firefox, or Safari (modern version)
- [ ] Developer Tools open: Press `F12`
- [ ] Navigate to: `http://localhost:8080/avenzur/admin/cost_center/dashboard?period=2025-10`

---

## 🧪 Test 1: Dashboard Loads

### What to Check

```
1. Page loads without errors
2. You see: KPI cards, pharmacy table, filters
3. Browser console (F12 → Console): No red X errors
4. Status code 200 OK (F12 → Network → localhost)
```

### Expected Result

✅ Dashboard displays with company-wide KPI data

### If It Fails

```
❌ Blank page → Check browser console for errors
❌ 404 error → Check URL has /avenzur/ subdirectory
❌ Login page → Need to login first
❌ Errors in console → Note error message for support
```

---

## 🧪 Test 2: Pharmacy Filter Dropdown

### What to Locate

Find the section labeled "Filter by Pharmacy" with a dropdown menu

### What to Do

```
1. Click dropdown menu
2. You should see list of 8 pharmacies:
   - E&M Central Plaza Pharmacy (ID 52)
   - [7 others]
```

### Expected Result

✅ Dropdown lists all pharmacies

### If It Fails

```
❌ Dropdown empty → Check model returns pharmacies
❌ Dropdown closed → Dropdown not working
❌ Only some pharmacies → Check warehouse_type filter
→ Run SQL: SELECT COUNT(*) FROM sma_warehouses WHERE warehouse_type='pharmacy'
```

---

## 🧪 Test 3: Pharmacy Filter Action

### What to Do

```
1. Open browser console (F12 → Console)
2. Select pharmacy from dropdown: "E&M Central Plaza Pharmacy"
3. Watch console output
4. Look for console.log messages like:
   "Fetching pharmacy detail for ID: 52"
   "API URL: http://localhost:8080/avenzur/api/v1/cost-center/pharmacy-detail/52?period=2025-10"
```

### Expected Result

```
✅ Console shows API URL
✅ API call succeeds (Response 200)
✅ KPI cards update with pharmacy-specific data
✅ Table shows only selected pharmacy
```

### If It Fails

**Scenario A: No console log messages**

```
❌ Dropdown event not firing
→ Open browser DevTools (F12)
→ Click dropdown again
→ Note any errors in console
```

**Scenario B: API URL shows but returns 404**

```
❌ API endpoint not found
→ Check URL format in console
→ Should be: http://localhost:8080/avenzur/api/v1/cost-center/...
→ If missing /avenzur/, that's the problem
→ Already fixed in code (baseUrl added)
```

**Scenario C: API returns error JSON**

```
❌ Data error from API
→ Check Network tab (F12 → Network)
→ Click on API call request
→ View Response tab
→ Read error message
→ Common: "Pharmacy not found" or "No data available for period"
```

**Scenario D: KPI cards don't update**

```
❌ Response received but UI not updating
→ Check console for JavaScript errors
→ Check if renderKPICards() function exists
→ Verify data structure in API response
```

---

## 🧪 Test 4: "View" Button Navigation

### What to Do

```
1. Click "View →" button on any pharmacy row
2. Observe browser URL bar
3. Check if page changes
```

### Expected Result

```
✅ URL changes to: /admin/cost_center/pharmacy/52?period=2025-10
✅ Pharmacy detail page loads
✅ Page shows pharmacy name, KPI cards, branches table
```

### If It Fails

**Scenario A: Nothing happens**

```
❌ Button click not registered
→ Inspect button element (F12 → Inspector)
→ Check if onclick handler exists
→ Note any console errors
```

**Scenario B: URL changes but page still dashboard**

```
❌ Route not matching correctly
→ Check URL format exactly
→ Should be: /admin/cost_center/pharmacy/52
→ Check routes.php has correct pattern
```

**Scenario C: Redirects to login**

```
❌ User session expired
→ Logout completely
→ Login again
→ Try View button again
```

**Scenario D: Shows 404 or error**

```
❌ Page/method not found
→ Check route in routes.php
→ Check controller method exists
→ Check controller file name capitalization
```

---

## 🧪 Test 5: Pharmacy Detail Page Display

### What to Check

If View button worked, verify pharmacy detail page shows:

```
✅ Pharmacy name at top
✅ Breadcrumb: "Dashboard > Pharmacy Name"
✅ KPI cards: Revenue, Cost, Profit, Margin
✅ Branches table with all branches
✅ Charts and trend data
✅ Period selector
```

### If Something's Missing

```
- KPI cards blank → Data not fetched
- Table empty → No branches for pharmacy
- Charts not showing → Chart library issue
- Period selector not working → JavaScript issue
```

---

## 🔍 Diagnostic Steps

### If Any Test Fails:

**Step 1: Check Browser Console**

```
Open: F12 → Console tab
Look for:
- Red X errors
- Yellow warnings
- Any JavaScript exceptions
→ Screenshot and note error message
```

**Step 2: Check Network Tab**

```
Open: F12 → Network tab
Reload page
Look for:
- Failed requests (red X)
- 404 responses
- 500 responses
- Slow requests (>1s)
→ Click on failed request to see details
```

**Step 3: Verify URL Format**

```
Check address bar shows:
http://localhost:8080/avenzur/admin/cost_center/dashboard?period=2025-10
                     ▲              ▲
              Port 8080          /avenzur/ needed
```

**Step 4: Check Authentication**

```
If redirected to login page:
- Login with admin credentials
- After login, try test again
- Session might expire → login again if needed
```

**Step 5: Run SQL Verification**

```
Verify data exists:
SELECT COUNT(*) FROM sma_warehouses WHERE warehouse_type='pharmacy'
→ Should return 8

SELECT COUNT(*) FROM sma_fact_cost_center WHERE warehouse_id=52 AND period='2025-10'
→ Should return > 0
```

---

## 💾 Information to Collect for Support

If something isn't working, collect:

1. **Screenshot of Problem**

   - Show full page
   - Show browser URL
   - Show any error messages

2. **Console Output (F12)**

   - Copy any red errors
   - Copy any warning messages
   - Copy relevant log messages

3. **Network Errors (F12 → Network)**

   - Screenshot of failed requests
   - HTTP status codes
   - Response text

4. **Specific Issue**

   - Does dashboard load? (Y/N)
   - Does dropdown appear? (Y/N)
   - Does filter work? (Y/N)
   - Does View button work? (Y/N)
   - Does detail page load? (Y/N)

5. **Steps to Reproduce**
   - Exact sequence of actions you took
   - What happened vs. what was expected

---

## ✅ Success Scenarios

### Scenario 1: Everything Works 🎉

```
✅ Dashboard loads
✅ Pharmacy filter dropdown appears
✅ Selecting pharmacy updates KPI cards
✅ Table filters to selected pharmacy
✅ View button navigates to detail page
✅ Detail page displays correctly
✅ No errors in console
→ READY FOR PRODUCTION
```

### Scenario 2: Minor Issues Fixed

```
⚠️ Some issues found
✅ Issues documented
✅ Fixes applied
✅ Retested successfully
→ READY FOR PRODUCTION
```

### Scenario 3: Data Issues

```
❌ Dashboard shows zeros
✅ Code is working correctly
❌ Database missing data
→ Need to verify data in database
→ Run SQL diagnostics
```

---

## 📞 Support Contact

If you encounter issues:

1. **First:** Run through this checklist
2. **Collect:** Console errors, network info, screenshots
3. **Report:** Provide all collected information
4. **Include:**
   - What test failed
   - Exact error message
   - Steps to reproduce
   - Browser type & version

---

## 🎯 Expected Completion Time

Testing all scenarios: **15-30 minutes**

If issues found:

- Simple fixes: 5-15 minutes
- Complex fixes: 30+ minutes

---

**Next Steps:**

1. Open browser
2. Navigate to dashboard
3. Run tests in order 1-5
4. Note results
5. Report any failures

**Status:** Ready to test whenever you're available!

---

Generated: 2025-10-25  
Updated: SESSION COMPLETE
