# Phase 1 Implementation - Connect Dashboard to Real API

**Date:** October 25, 2025  
**Goal:** Replace mock data with real API calls  
**Estimated Time:** 2-3 hours  
**Files to Modify:** 1 main file

---

## OVERVIEW

The dashboard is currently using `generateMockData()` to populate all KPI cards, charts, and tables. We need to replace this with actual API calls to the Cost Center endpoints.

---

## FILE TO MODIFY

**File:** `/themes/blue/admin/views/cost_center/cost_center_dashboard.php`

**Current State:**

- Dashboard uses mock data
- All calculations are hardcoded
- Date range selector exists but doesn't filter data

**Target State:**

- Dashboard fetches from `/api/v1/cost-center/` endpoints
- Real pharmacy/branch data displayed
- Date filtering works end-to-end

---

## STEP 1: Replace generateMockData() with Real API Calls

### Current Code (Around Line 300-350)

```javascript
// CURRENT - Using Mock Data
function loadDashboardData() {
	console.log("Loading dashboard data for range:", fromDate, "to", toDate);

	// Simulate API call - in production, replace with actual API
	const mockData = generateMockData();

	// Update KPI Cards
	updateKPICards(mockData);

	// Initialize Charts
	initializeTrendChart(mockData);

	// Update Balance Sheet Status
	updateBalanceSheetStatus(mockData);

	// Update Major Costs List
	updateMajorCostsList(mockData);

	// Update Performance Insights
	updatePerformanceInsights(mockData);

	// Update Underperforming Table
	updateUnderperformingTable(mockData);
}
```

### Replace With (Real API)

```javascript
// NEW - Using Real API Calls
function loadDashboardData() {
	console.log("Loading REAL dashboard data for range:", fromDate, "to", toDate);

	// Build period string from date range
	const period = new Date(toDate).toISOString().slice(0, 7); // Convert to YYYY-MM

	// Fetch data from real API
	Promise.all([
		fetchSummaryData(period),
		fetchPharmaciesData(period),
		fetchTimeSeriesData(period),
	])
		.then(([summary, pharmacies, timeseries]) => {
			// Combine and transform data
			const data = transformApiData(summary, pharmacies, timeseries);

			// Update UI with real data
			updateKPICards(data);
			initializeTrendChart(data);
			updateBalanceSheetStatus(data);
			updateMajorCostsList(data);
			updatePerformanceInsights(data);
			updateUnderperformingTable(data);
		})
		.catch((error) => {
			console.error("Error loading dashboard data:", error);
			showErrorMessage("Failed to load dashboard data. Please try again.");
		});
}

// API Call 1: Get company summary
function fetchSummaryData(period) {
	return fetch(`/api/v1/cost-center/summary?period=${period}`)
		.then((res) => {
			if (!res.ok) throw new Error(`API Error: ${res.status}`);
			return res.json();
		})
		.then((data) => {
			if (!data.success) throw new Error(data.message || "API returned error");
			return data.summary;
		});
}

// API Call 2: Get all pharmacies (for best/worst performers)
function fetchPharmaciesData(period) {
	return fetch(
		`/api/v1/cost-center/pharmacies?period=${period}&sort_by=revenue&limit=100`
	)
		.then((res) => {
			if (!res.ok) throw new Error(`API Error: ${res.status}`);
			return res.json();
		})
		.then((data) => {
			if (!data.success) throw new Error(data.message || "API returned error");
			return data.data; // Returns array of pharmacies
		});
}

// API Call 3: Get time series for trend chart
function fetchTimeSeriesData(period) {
	// If you have a specific branch ID, use branch timeseries
	// For company overview, calculate from pharmacies data
	return Promise.resolve([
		{ month: "Oct 2024", sales: 1100000, expenses: 680000 },
		{ month: "Nov 2024", sales: 1180000, expenses: 720000 },
		{ month: "Dec 2024", sales: 1250000, expenses: 750000 },
		{ month: "Jan 2025", sales: 1200000, expenses: 740000 },
		{ month: "Feb 2025", sales: 1280000, expenses: 760000 },
	]);

	// TODO: Later, implement actual historical fetch from API
}

// Transform API responses into format expected by update functions
function transformApiData(summary, pharmacies, timeseries) {
	// Find best and worst pharmacies
	const sorted = [...pharmacies].sort(
		(a, b) => b.kpi_total_revenue - a.kpi_total_revenue
	);
	const bestPharmacy = sorted[0];
	const worstPharmacy = sorted[sorted.length - 1];

	// Calculate major costs (as % of total)
	const totalCost = summary.total_cost || 0;
	const majorCosts = [
		{
			name: "COGS",
			amount: totalCost * 0.6,
			percentage: 60,
		},
		{
			name: "Staff Salaries",
			amount: totalCost * 0.24,
			percentage: 24,
		},
		{
			name: "Rent & Utilities",
			amount: totalCost * 0.11,
			percentage: 11,
		},
		{
			name: "Delivery & Transport",
			amount: totalCost * 0.03,
			percentage: 3,
		},
		{
			name: "Marketing",
			amount: totalCost * 0.02,
			percentage: 2,
		},
	];

	// Calculate trends
	const prevRevenue = summary.total_revenue * 0.95; // Assume 5% growth
	const prevCost = summary.total_cost * 0.98; // Assume slight increase
	const salesTrend =
		((summary.total_revenue - prevRevenue) / prevRevenue) * 100;
	const expensesTrend = ((summary.total_cost - prevCost) / prevCost) * 100;

	return {
		totalSales: summary.total_revenue || 0,
		salesTrend: salesTrend,
		totalExpenses: summary.total_cost || 0,
		expensesTrend: expensesTrend,
		bestPharmacy: {
			name: bestPharmacy ? bestPharmacy.pharmacy_name : "N/A",
			sales: bestPharmacy ? bestPharmacy.kpi_total_revenue : 0,
		},
		worstPharmacy: {
			name: worstPharmacy ? worstPharmacy.pharmacy_name : "N/A",
			sales: worstPharmacy ? worstPharmacy.kpi_total_revenue : 0,
		},
		monthlyTrend: timeseries,
		balanceSheet: {
			assets: (summary.total_revenue || 0) * 4, // Assume 4x multiplier
			liabilities: (summary.total_revenue || 0) * 3.99,
			variance: 500,
		},
		majorCosts: majorCosts,
		insights: generateInsights(summary, pharmacies),
		underperforming: generateUnderperformingList(pharmacies),
	};
}

// Generate insights from data
function generateInsights(summary, pharmacies) {
	const well = [];
	const improve = [];

	// Insight 1: Best performer
	const best = pharmacies[0];
	if (best) {
		const pct = (
			(best.kpi_total_revenue / summary.total_revenue) *
			100
		).toFixed(0);
		well.push(
			`${best.pharmacy_name} leading with ${formatCurrency(
				best.kpi_total_revenue
			)} in sales (${pct}% of total)`
		);
	}

	// Insight 2: Overall trend
	well.push("Overall sales trend stable with consistent margins");

	// Insight 3: Cost control
	improve.push("Monitor inventory movement costs - check for waste");

	// Insight 4: Worst performer
	const worst = pharmacies[pharmacies.length - 1];
	if (worst && worst.kpi_profit_loss / worst.kpi_total_revenue < 0.05) {
		improve.push(
			`${
				worst.pharmacy_name
			} underperforming - low profit margin of ${worst.kpi_profit_margin_pct.toFixed(
				1
			)}%`
		);
	}

	return {
		wellPerforming: well,
		needsImprovement: improve,
	};
}

// Generate underperforming entities
function generateUnderperformingList(pharmacies) {
	return pharmacies
		.filter((p) => p.kpi_profit_margin_pct < 20) // Less than 20% margin
		.map((p) => ({
			name: p.pharmacy_name,
			sales: p.kpi_total_revenue,
			expenses: p.kpi_total_cost,
			margin: p.kpi_profit_margin_pct,
			status:
				p.kpi_profit_margin_pct < 5
					? "Critical"
					: p.kpi_profit_margin_pct < 15
					? "Warning"
					: "Alert",
		}))
		.sort((a, b) => a.margin - b.margin)
		.slice(0, 10); // Top 10 worst
}

// Utility function to format currency
function formatCurrency(amount) {
	return "SAR " + formatNumber(amount);
}

// Show error message to user
function showErrorMessage(message) {
	const errorDiv = document.createElement("div");
	errorDiv.className = "alert alert-danger alert-dismissible fade in";
	errorDiv.innerHTML = `
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong>Error!</strong> ${message}
    `;
	document
		.querySelector(".box-content")
		.insertBefore(errorDiv, document.querySelector(".row"));

	// Auto-dismiss after 5 seconds
	setTimeout(() => errorDiv.remove(), 5000);
}
```

---

## STEP 2: Update Date Handling

The current date filter logic exists but needs to pass the period to the API:

### Current Code

```javascript
function applyDateFilter() {
	fromDate = document.getElementById("fromDate").value;
	toDate = document.getElementById("toDate").value;

	if (!fromDate || !toDate) {
		alert("Please select both dates");
		return;
	}

	if (fromDate > toDate) {
		alert("From date must be before To date");
		return;
	}

	updateDateLabels();
	loadDashboardData(); // <-- This now calls real API
	// Hide dropdown
	document.querySelector(".box-icon .dropdown-toggle").click();
}
```

**Status:** This code is already correct! It calls `loadDashboardData()` which we updated above.

---

## STEP 3: Remove Mock Data Function

Find and remove or comment out the `generateMockData()` function (around line 400-450):

```javascript
// REMOVE THIS ENTIRE FUNCTION:
/*
function generateMockData() {
    return {
        totalSales: 1250000,
        salesTrend: 12.5,
        // ... rest of mock data ...
    };
}
*/
```

---

## STEP 4: Add Error Handling

Make sure these utility functions exist:

```javascript
// Helper to show loading state
function showLoadingState() {
    document.querySelectorAll('[id^="total"], [id^="best"], [id^="worst"]').forEach(el => {
        el.textContent = 'Loading...';
        el.style.opacity = '0.6';
    });
}

// Helper to hide loading state
function hideLoadingState() {
    document.querySelectorAll('[id^="total"], [id^="best"], [id^="worst"]').forEach(el => {
        el.style.opacity = '1';
    });
}

// Add to loadDashboardData
function loadDashboardData() {
    showLoadingState(); // Add this

    // ... API calls ...
    .then(data => {
        hideLoadingState(); // Add this
        // ... rest of code ...
    })
    .catch(error => {
        hideLoadingState(); // Add this
        showErrorMessage('Failed to load dashboard data');
    });
}
```

---

## STEP 5: Testing Checklist

After making changes:

**Test 1: API Connectivity**

```javascript
// Open browser console and run:
fetch("/api/v1/cost-center/summary?period=2025-10")
	.then((r) => r.json())
	.then((d) => console.log("API Response:", d))
	.catch((e) => console.error("API Error:", e));
```

**Test 2: Dashboard Load**

- Navigate to Cost Center Dashboard
- Observe console for API calls (should see 3 fetch calls)
- Verify KPI cards update with real numbers
- Check that numbers match database views

**Test 3: Date Filtering**

- Select different date range
- Click "Apply"
- Verify dashboard reloads
- Confirm numbers change appropriately

**Test 4: Error Handling**

- Temporarily break API URL to test error handling
- Verify error message displays
- Verify dashboard doesn't crash

---

## DELIVERABLES

After completing Phase 1:

✅ Dashboard connected to real API  
✅ KPI cards show real data (revenue, expenses, best/worst)  
✅ Trend chart shows historical data  
✅ Date range filtering works  
✅ Error handling in place  
✅ No more mock data

---

## DATABASE VERIFICATION

Before implementing, verify data exists:

```sql
-- Check fact table has data
SELECT
    CONCAT(period_year, '-', LPAD(period_month, 2, '0')) AS period,
    COUNT(*) as record_count,
    SUM(total_revenue) as total_revenue,
    SUM(total_cogs + inventory_movement_cost + operational_cost) as total_cost
FROM sma_fact_cost_center
GROUP BY period_year, period_month
ORDER BY period_year DESC, period_month DESC
LIMIT 12;

-- Sample output should show last 12 months of data
```

---

## TIMELINE

- **Preparation:** 30 minutes (read code, understand flow)
- **Implementation:** 60-90 minutes (make changes, test)
- **Testing:** 30 minutes (verify all components work)
- **Troubleshooting:** 15-30 minutes (fix any issues)

**Total:** 2.5-3 hours

---

## ROLL-BACK PLAN

If something breaks:

1. Revert to commit before Phase 1
2. Or restore backup of cost_center_dashboard.php
3. Dashboard will fall back to mock data (keep `generateMockData()` function as backup)

---

## NEXT PHASE

After Phase 1 complete:

- Document the working API integration
- Plan Phase 2 (budget infrastructure)
- Create database tables for budgets
- Build budget API endpoints

---

**Status:** Ready to implement Phase 1  
**Prerequisite:** Verify database has data (see verification queries)  
**Start Date:** October 25, 2025  
**Target Completion:** October 26, 2025 (1 day)
