# HOUR 6 DASHBOARD INTEGRATION CHECKLIST

**Timeline:** 1.5 hours remaining  
**Target:** Connect real API to dashboard  
**Status:** READY TO EXECUTE

---

## ✅ PRE-INTEGRATION VERIFICATION

### Database Status

- [x] All 6 tables created and accessible
- [x] All 3 views working and queryable
- [x] Test data inserted (15 records)
- [x] Foreign keys enforced
- [x] Indexes created
- [x] Sample queries returning data

### API Status

- [x] All 7 endpoints implemented
- [x] Role-based filtering configured
- [x] Error handling in place
- [x] Helper functions available
- [x] Business logic complete

### Documentation Status

- [x] API response structures documented
- [x] Implementation guide created
- [x] Code templates provided
- [x] Testing checklist available
- [x] Troubleshooting guide included

---

## 📋 HOUR 6 EXECUTION PLAN

### Phase 1: Create API Service (20 minutes)

**Task 1.1:** Create `/themes/blue/admin/js/services/budgetAPI.js`

- [ ] Create BudgetAPI class
- [ ] Implement fetch method with timeout
- [ ] Add getAllocated() method
- [ ] Add getTracking() method
- [ ] Add getForecast() method
- [ ] Add getAlerts() method
- [ ] Add error handling
- [ ] Test class instantiation

**Task 1.2:** Create utility functions

- [ ] formatCurrency() function
- [ ] getStatusColor() function
- [ ] parseDate() function
- [ ] calculateTrend() function

### Phase 2: Update Dashboard (30 minutes)

**Task 2.1:** Replace data loading

- [ ] Locate generateMockData() function
- [ ] Delete or comment out mock function
- [ ] Create loadBudgetData() function
- [ ] Implement Promise.all for parallel API calls
- [ ] Add error handling
- [ ] Add loading state management
- [ ] Test data loading

**Task 2.2:** Update data processing

- [ ] Create processBudgetData() function
- [ ] Extract company-level tracking
- [ ] Calculate KPI values
- [ ] Prepare chart data
- [ ] Prepare alerts data

**Task 2.3:** Update rendering

- [ ] Create updateKPICards() function
- [ ] Create updateCharts() function
- [ ] Create updateAlerts() function
- [ ] Test rendering with real data

### Phase 3: Connect Charts (20 minutes)

**Task 3.1:** Update TrendChart

- [ ] Get tracking data from API
- [ ] Format for chart (dates, amounts)
- [ ] Update ECharts options
- [ ] Test chart rendering
- [ ] Verify responsive behavior

**Task 3.2:** Update BreakdownChart

- [ ] Get hierarchy data from API
- [ ] Group by pharmacy/branch
- [ ] Format for pie/bar chart
- [ ] Update chart rendering
- [ ] Test with real data

**Task 3.3:** Update ForecastChart

- [ ] Get forecast data from API
- [ ] Prepare best/current/worst scenarios
- [ ] Format for area chart
- [ ] Update chart rendering
- [ ] Add reference line (budget)

### Phase 4: Add Features (20 minutes)

**Task 4.1:** Loading states

- [ ] Create showLoadingState() function
- [ ] Create hideLoadingState() function
- [ ] Add skeleton loaders
- [ ] Test loading display

**Task 4.2:** Error handling

- [ ] Create showErrorState() function
- [ ] Add error message display
- [ ] Add retry button functionality
- [ ] Test error scenarios

**Task 4.3:** Auto-refresh

- [ ] Setup 30-second refresh interval
- [ ] Implement period selector handling
- [ ] Add URL parameter support
- [ ] Test refresh functionality

### Phase 5: Testing & Optimization (10 minutes)

**Task 5.1:** Functional testing

- [ ] KPI cards show correct values
- [ ] Colors match status correctly
- [ ] Charts render with real data
- [ ] Alerts display if any
- [ ] Period selector works
- [ ] Error handling works
- [ ] Loading states display
- [ ] Auto-refresh works

**Task 5.2:** Performance testing

- [ ] Initial load < 2 seconds
- [ ] API response < 5 seconds
- [ ] Chart render < 300ms
- [ ] No memory leaks
- [ ] No console errors

**Task 5.3:** Responsiveness

- [ ] Test on desktop (1920px)
- [ ] Test on tablet (768px)
- [ ] Test on mobile (375px)
- [ ] Verify scrolling works
- [ ] Check layout integrity

---

## 🔍 DETAILED IMPLEMENTATION GUIDE

### Step 1: Create BudgetAPI Service

**File:** `/themes/blue/admin/js/services/budgetAPI.js`

```javascript
class BudgetAPI {
	constructor(baseUrl = "/api/v1/budgets") {
		this.baseUrl = baseUrl;
		this.timeout = 5000;
	}

	async getAllocated(period) {
		return this.fetch(`${this.baseUrl}/allocated?period=${period}`);
	}

	async getTracking() {
		return this.fetch(`${this.baseUrl}/tracking`);
	}

	async getForecast() {
		return this.fetch(`${this.baseUrl}/forecast`);
	}

	async getAlerts() {
		return this.fetch(`${this.baseUrl}/alerts`);
	}

	async fetch(url) {
		try {
			const controller = new AbortController();
			const timeoutId = setTimeout(() => controller.abort(), this.timeout);

			const response = await fetch(url, {
				headers: {
					Authorization: `Bearer ${this.getAuthToken()}`,
					Accept: "application/json",
				},
				signal: controller.signal,
			});

			clearTimeout(timeoutId);

			if (!response.ok) {
				throw new Error(`API Error: ${response.status}`);
			}

			return await response.json();
		} catch (error) {
			console.error("API Error:", error);
			throw error;
		}
	}

	getAuthToken() {
		return localStorage.getItem("auth_token") || "";
	}
}

const budgetAPI = new BudgetAPI();
```

### Step 2: Update Dashboard HTML

Add script tag to dashboard template footer:

```html
<script src="/themes/blue/admin/js/services/budgetAPI.js"></script>
<script>
	// Load budget data when page loads
	document.addEventListener("DOMContentLoaded", async () => {
		const period =
			new URLSearchParams(window.location.search).get("period") ||
			new Date().toISOString().slice(0, 7);

		await loadBudgetData();

		// Refresh every 30 seconds
		setInterval(() => loadBudgetData(), 30000);
	});

	// Handle period selector change
	document
		.getElementById("period-selector")
		?.addEventListener("change", (e) => {
			const period = e.target.value;
			window.history.pushState({}, "", `?period=${period}`);
			loadBudgetData();
		});
</script>
```

### Step 3: Replace Mock Data Function

**Current (DELETE):**

```javascript
function generateMockData() {
	return {
		totalBudget: 500000,
		totalSpent: 125000,
		// ...
	};
}
```

**New (ADD):**

```javascript
async function loadBudgetData() {
	try {
		showLoadingState();

		// Load all data in parallel
		const [allocated, tracking, forecast, alerts] = await Promise.all([
			budgetAPI.getAllocated("2025-10"),
			budgetAPI.getTracking(),
			budgetAPI.getForecast(),
			budgetAPI.getAlerts(),
		]);

		// Process and render
		const budgetData = processBudgetData(allocated, tracking, forecast, alerts);
		renderDashboard(budgetData);
		hideLoadingState();

		return budgetData;
	} catch (error) {
		console.error("Failed to load budget data:", error);
		showErrorState(error);
	}
}
```

### Step 4: Process API Data

```javascript
function processBudgetData(allocated, tracking, forecast, alerts) {
	const companyTracking =
		tracking.data?.find((t) => t.hierarchy_level === "company") || {};
	const companyForecast = forecast.data?.[0] || {};

	return {
		// KPIs
		totalBudget: companyTracking.allocated_amount || 0,
		totalSpent: companyTracking.actual_spent || 0,
		remaining: companyTracking.remaining_amount || 0,
		percentageUsed: companyTracking.percentage_used || 0,

		// Forecast
		projectedEndOfMonth: companyForecast.projected_end_of_month || 0,
		burnRateDaily: companyForecast.burn_rate_daily || 0,
		riskLevel: companyForecast.risk_level || "low",

		// Status
		status: companyTracking.status || "safe",

		// Alerts
		activeAlerts: alerts.data?.filter((a) => a.status === "active") || [],

		// Raw data for charts
		hierarchyData: allocated.data || [],
		trackingData: tracking.data || [],
		forecastData: forecast.data || [],
		alertsData: alerts.data || [],
	};
}
```

### Step 5: Render Dashboard

```javascript
function renderDashboard(budgetData) {
	updateKPICards(budgetData);
	updateCharts(budgetData);
	updateAlerts(budgetData);
}

function updateKPICards(data) {
	// Update DOM elements with API data
	document.getElementById("kpi-total-budget").innerText = formatCurrency(
		data.totalBudget
	);
	document.getElementById("kpi-total-spent").innerText = formatCurrency(
		data.totalSpent
	);
	document.getElementById("kpi-remaining").innerText = formatCurrency(
		data.remaining
	);
	document.getElementById("kpi-forecast").innerText = formatCurrency(
		data.projectedEndOfMonth
	);

	// Update meter
	updateMeter(data.percentageUsed, data.status);
}

function updateMeter(percentage, status) {
	const meter = document.getElementById("budget-meter");
	meter.style.background = getStatusColor(status);
	meter.setAttribute("aria-valuenow", percentage);
	meter.innerText = `${percentage.toFixed(1)}%`;
}

function getStatusColor(status) {
	const colors = {
		safe: "#10B981",
		warning: "#F59E0B",
		danger: "#FB923C",
		exceeded: "#EF4444",
	};
	return colors[status] || colors["safe"];
}

function formatCurrency(amount) {
	return new Intl.NumberFormat("en-SA", {
		style: "currency",
		currency: "SAR",
	}).format(amount);
}
```

---

## 🧪 TESTING STEPS

### Test 1: API Connectivity

```javascript
// In browser console:
budgetAPI.getAllocated("2025-10").then((r) => console.log(r));
```

### Test 2: Data Loading

- [ ] Dashboard loads without errors
- [ ] Loading spinner shows initially
- [ ] Data appears after 1-2 seconds
- [ ] No console errors

### Test 3: KPI Card Values

- [ ] Total Budget: 150,000 SAR ✓
- [ ] Total Spent: 975 SAR ✓
- [ ] Remaining: 149,025 SAR ✓
- [ ] Percentage: 0.65% ✓
- [ ] Status: SAFE (Green) ✓

### Test 4: Charts

- [ ] TrendChart renders
- [ ] BreakdownChart renders
- [ ] ForecastChart renders
- [ ] All data visible

### Test 5: Alerts

- [ ] Alert section displays
- [ ] No active alerts (OK) ✓

### Test 6: Responsiveness

- [ ] Desktop layout (1920px) ✓
- [ ] Tablet layout (768px) ✓
- [ ] Mobile layout (375px) ✓

### Test 7: Refresh & Auto-Update

- [ ] Page refresh works
- [ ] Period selector changes dashboard
- [ ] Data auto-refreshes every 30s
- [ ] URL updates with period param

### Test 8: Error Handling

- [ ] Network error shows message
- [ ] Retry button works
- [ ] Invalid period handled
- [ ] API timeout handled (5s)

---

## 🔧 TROUBLESHOOTING

| Issue                | Cause                   | Solution                                          |
| -------------------- | ----------------------- | ------------------------------------------------- |
| "Failed to fetch"    | CORS issue or network   | Check browser console, verify API endpoint        |
| Data not updating    | API returns error       | Check API status: `curl /api/v1/budgets/tracking` |
| Wrong values         | Data processing error   | Add console.log in processBudgetData()            |
| Slow loading         | Large dataset           | Add filtering by period in API                    |
| Charts not rendering | ECharts library missing | Verify script tags and library load               |

---

## ✅ SIGN-OFF CHECKLIST

### Before Starting Hour 6

- [x] Database migration complete
- [x] Test data populated
- [x] API endpoints ready
- [x] Integration guide created
- [x] Code templates provided

### During Hour 6 Implementation

- [ ] Create budgetAPI.js service
- [ ] Replace generateMockData() function
- [ ] Update KPI card rendering
- [ ] Connect all 3 charts
- [ ] Add error handling
- [ ] Test all functionality

### Before End of Hour 6

- [ ] Dashboard loads real data
- [ ] All KPI cards show correct values
- [ ] Charts display with real data
- [ ] Alerts display correctly
- [ ] No console errors
- [ ] Refresh works
- [ ] Period selector works
- [ ] Error handling works
- [ ] Code documented
- [ ] Ready for testing (Hour 7)

---

## 📊 SUCCESS CRITERIA

**Dashboard Integration is COMPLETE when:**

1. ✅ All 4 KPI cards display real API data
2. ✅ Percentage meter shows correct status color
3. ✅ All 3 charts render with real data
4. ✅ Alerts section displays correctly
5. ✅ Period selector updates dashboard
6. ✅ Data auto-refreshes every 30 seconds
7. ✅ Loading state displays during API calls
8. ✅ Error messages show on API failure
9. ✅ No console errors
10. ✅ Performance meets targets (< 2 seconds)

---

## 🎯 NEXT STEPS

**After Hour 6 Completion:**

- Document any changes made
- Prepare for Hour 7 testing
- Create test cases
- Setup monitoring
- Brief team on changes

**For Hour 7:**

- Execute end-to-end testing
- Verify all calculations
- Test role-based access
- Test alert triggering
- Prepare for deployment

**For Hour 8:**

- Backup database
- Deploy to production
- Final verification
- Announce to team

---

**Document Created:** 2025-10-25  
**Target Completion:** Hour 6 (1.5 hours)  
**Status:** READY FOR EXECUTION
