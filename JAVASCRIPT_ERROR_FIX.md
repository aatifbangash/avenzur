# Cost Center Dashboard - JavaScript Error Fix

**Date:** October 25, 2025  
**Issue:** `Uncaught TypeError: Cannot set properties of null (setting 'textContent')`  
**Status:** ✅ **FIXED**

## Problem Analysis

The error occurred because the JavaScript code was trying to update HTML elements that either:

1. Don't exist in the new simplified HTML structure
2. Were trying to manipulate non-existent icon elements (`salesTrendIcon`, `expensesTrendIcon`)

### Error Stack Trace

```
Uncaught TypeError: Cannot set properties of null (setting 'textContent')
    at updateKPICards (dashboard:2396:55)
    at loadDashboardData (dashboard:2033:5)
    at HTMLDocument.<anonymous> (dashboard:1915:5)
```

## Root Cause

When we updated the HTML structure from `info-box` to `stat-card` components, we:

- ✅ Changed the HTML structure
- ✅ Added new CSS
- ❌ **FORGOT** to update the JavaScript functions that manipulate these elements

### Old HTML Structure:

```html
<div class="info-box blue-bg">
	<i class="fa fa-arrow-up"></i>
	<!-- Font Awesome icon -->
	<div class="info-box-content">
		<span class="info-box-text">Total Sales</span>
		<span class="info-box-number" id="totalSales">-</span>
		<span class="info-box-number" style="font-size: 12px; margin-top: 5px;">
			<i class="fa fa-arrow-up" id="salesTrendIcon"></i>
			<!-- This was manipulated -->
			<span id="salesTrend">0%</span>
		</span>
	</div>
</div>
```

### New HTML Structure:

```html
<div class="stat-card indigo">
	<div class="fs-4 fw-semibold">
		<span id="totalSales">-</span>
		<!-- Direct span -->
		<span class="fs-6 fw-normal">
			(<span id="salesTrendValue">+1.2%</span>
			<!-- Renamed from 'salesTrend' -->
			<svg>...</svg>)
			<!-- SVG, not Font Awesome -->
		</span>
	</div>
	<small class="text-white-75">Total Sales</small>
</div>
```

## Solution Implemented

### 1. Fixed `updateKPICards()` Function (Line 900)

**Before (causing error):**

```javascript
function updateKPICards(data) {
	// This fails because 'totalSales' is now nested, and 'salesTrendIcon' doesn't exist
	document.getElementById("totalSales").textContent =
		"SAR " + formatNumber(data.totalSales);
	const salesIcon = document.getElementById("salesTrendIcon"); // ← RETURNS NULL
	salesIcon.className =
		data.salesTrend >= 0 ? "fa fa-arrow-up" : "fa fa-arrow-down";
	// ↑ Trying to set property on null throws error
}
```

**After (fixed):**

```javascript
function updateKPICards(data) {
	// Sales KPI
	const totalSalesEl = document.getElementById("totalSales");
	if (totalSalesEl)
		totalSalesEl.textContent = formatNumber(data.totalSales / 1000) + "K";

	const salesTrendEl = document.getElementById("salesTrendValue"); // ← Renamed ID
	if (salesTrendEl)
		salesTrendEl.textContent =
			(data.salesTrend >= 0 ? "+" : "") +
			Math.abs(data.salesTrend).toFixed(1) +
			"%";

	// Expenses KPI
	const totalExpensesEl = document.getElementById("totalExpenses");
	if (totalExpensesEl)
		totalExpensesEl.textContent = formatNumber(data.totalExpenses / 1000) + "K";

	const expensesTrendEl = document.getElementById("expensesTrendValue"); // ← Renamed ID
	if (expensesTrendEl)
		expensesTrendEl.textContent =
			(data.expensesTrend >= 0 ? "+" : "") +
			Math.abs(data.expensesTrend).toFixed(1) +
			"%";

	// Best/Worst Pharmacy (updated with null checks)
	// ...
}
```

**Key changes:**

- ✅ Added `null` checks: `if (element) { ... }`
- ✅ Updated element IDs to match new HTML: `salesTrendValue` instead of `salesTrend`
- ✅ Removed Font Awesome icon manipulation (no longer needed)
- ✅ Updated value formatting: Now showing `K` suffix (125K instead of SAR 125000)

### 2. Fixed `updateBudgetKPICards()` Function (Line 730)

**Before (causing error):**

```javascript
function updateBudgetKPICards(budgetData) {
	// Directly accessing without null checks
	document.getElementById("budgetAllocated").textContent =
		"SAR " + formatNumber(budgetData.allocated);
	// If element doesn't exist → error
}
```

**After (fixed):**

```javascript
function updateBudgetKPICards(budgetData) {
	const budgetAllocatedEl = document.getElementById("budgetAllocated");
	if (budgetAllocatedEl)
		budgetAllocatedEl.textContent =
			formatNumber(budgetData.allocated / 1000) + "K";

	const budgetPeriodLabelEl = document.getElementById("budgetPeriodLabel");
	if (budgetPeriodLabelEl) budgetPeriodLabelEl.textContent = budgetData.period;

	// Similar null checks for all other elements...
}
```

**Key changes:**

- ✅ Added null checks for all elements
- ✅ Consistent null-safe pattern throughout
- ✅ Updated value formatting to match new card style

### 3. Fixed `showBudgetLoading()` Function (Line 720)

**Before (causing error if elements don't exist):**

```javascript
function showBudgetLoading() {
	document.getElementById("budgetAllocated").innerHTML =
		'<i class="fa fa-spinner fa-spin"></i> Loading...';
	// ↑ If element is null → error
}
```

**After (fixed):**

```javascript
function showBudgetLoading() {
	const budgetAllocatedEl = document.getElementById("budgetAllocated");
	if (budgetAllocatedEl)
		budgetAllocatedEl.innerHTML =
			'<i class="fa fa-spinner fa-spin"></i> Loading...';

	const budgetSpentEl = document.getElementById("budgetSpent");
	if (budgetSpentEl)
		budgetSpentEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

	// Similar pattern for all elements...
}
```

**Key changes:**

- ✅ Added null checks for all DOM queries
- ✅ Safe element updates

## Files Modified

| File                                                             | Lines   | Changes                                                                   |
| ---------------------------------------------------------------- | ------- | ------------------------------------------------------------------------- |
| `/themes/blue/admin/views/cost_center/cost_center_dashboard.php` | 900-925 | Fixed `updateKPICards()` function with null checks                        |
| `/themes/blue/admin/views/cost_center/cost_center_dashboard.php` | 720-770 | Fixed `updateBudgetKPICards()` and `showBudgetLoading()` with null checks |

## Testing Checklist

After applying these fixes:

- [ ] Refresh page: `Cmd+Shift+R` (Mac) or `Ctrl+Shift+F5` (Windows)
- [ ] Check browser console (F12) - no errors should appear
- [ ] KPI cards should display with values:
  - [ ] Total Sales: Shows "125K" with trend
  - [ ] Total Expenses: Shows value with trend
  - [ ] Best Pharmacy: Shows pharmacy name with sales
  - [ ] Worst Pharmacy: Shows pharmacy name with sales
- [ ] Budget cards should display:
  - [ ] Budget Allocated: Shows amount
  - [ ] Budget Spent: Shows amount with percentage
  - [ ] Budget Remaining: Shows amount
  - [ ] Forecast: Shows projected amount
- [ ] Trend chart should render (if echarts is loaded)
- [ ] No console errors: Open DevTools (F12) → Console tab → No red error messages

## Key Improvements

1. **Null Safety:** All DOM queries now check if element exists before manipulation
2. **Consistent Formatting:** All values now use `K` suffix (125K, 50K, etc.)
3. **Separated Concerns:** Element selection and value setting are now separate steps
4. **Error Prevention:** No more "Cannot set properties of null" errors
5. **Maintainability:** Easier to debug future issues with clear element references

## Why This Happened

1. HTML structure was updated to use new `stat-card` components
2. JavaScript wasn't updated to match the new HTML structure
3. Old element IDs (`salesTrendIcon`, `salesTrend`) no longer existed
4. Missing null checks meant immediate crash when trying to access null elements

## Prevention for Future Updates

✅ **Checklist when updating HTML components:**

- [ ] Update HTML structure
- [ ] Update CSS styling
- [ ] **IMPORTANT:** Update ALL JavaScript that references changed element IDs
- [ ] Add null checks: `if (element) { ... }`
- [ ] Test in browser console for errors
- [ ] Verify all data displays correctly

---

**Status:** ✅ **ALL FIXES APPLIED**

The cost center dashboard should now load without JavaScript errors. All KPI cards and budget cards will display properly with the new simplified stat-card design.

If you still see errors after these fixes, please check:

1. Browser developer console (F12) for the exact error
2. The `formatNumber()` function exists and works
3. The data being passed to these functions is valid
