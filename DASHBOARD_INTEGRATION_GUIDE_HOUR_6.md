# DASHBOARD INTEGRATION GUIDE - Hour 6

**File:** `/themes/blue/admin/views/cost_center/cost_center_dashboard.php`  
**Timeline:** 1.5 hours  
**Target:** Replace mock data with real API calls

---

## 📋 CURRENT STATE

The dashboard currently uses a `generateMockData()` function that returns hardcoded data:

```javascript
// CURRENT (Mock Data)
function generateMockData() {
	return {
		totalBudget: 500000,
		totalSpent: 125000,
		percentageUsed: 25,
		remaining: 375000,
		// ... more mock fields
	};
}
```

---

## 🎯 TARGET STATE

Replace with real API calls to retrieve live data:

```javascript
// TARGET (Real API)
fetch("/api/v1/budgets/allocated?period=2025-10")
	.then((r) => r.json())
	.then((data) => renderDashboard(data))
	.catch((error) => handleError(error));
```

---

## 🔗 API RESPONSE STRUCTURES

### 1. GET /api/v1/budgets/allocated?period=YYYY-MM

**Request:**

```
GET /api/v1/budgets/allocated?period=2025-10
Headers: Authorization: Bearer {token}
```

**Response (Company-Level View):**

```json
{
	"success": true,
	"code": 200,
	"data": [
		{
			"allocation_id": 10,
			"hierarchy_level": "company",
			"period": "2025-10",
			"warehouse_id": 1,
			"entity_name": "Avenzur Company",
			"allocated_amount": 150000.0,
			"parent_name": "Avenzur Company"
		},
		{
			"allocation_id": 11,
			"hierarchy_level": "pharmacy",
			"period": "2025-10",
			"warehouse_id": 2,
			"entity_name": "E&M Central Plaza Pharmacy",
			"allocated_amount": 75000.0,
			"parent_name": "Avenzur Company"
		}
	]
}
```

**Mapping for Dashboard:**

```javascript
const allocatedData = response.data;
const totalBudget = allocatedData
	.filter((a) => a.hierarchy_level === "company")
	.reduce((sum, a) => sum + parseFloat(a.allocated_amount), 0);
```

---

### 2. GET /api/v1/budgets/tracking

**Response:**

```json
{
	"success": true,
	"code": 200,
	"data": [
		{
			"tracking_id": 1,
			"allocation_id": 10,
			"hierarchy_level": "company",
			"warehouse_id": 1,
			"entity_name": "Avenzur Company",
			"period": "2025-10",
			"allocated_amount": 150000.0,
			"actual_spent": 975.0,
			"remaining_amount": 149025.0,
			"percentage_used": 0.65,
			"status": "safe"
		}
	]
}
```

**Mapping for Dashboard:**

```javascript
const trackingData = response.data;
const companyTracking = trackingData.find(
	(t) => t.hierarchy_level === "company"
);

// For KPI cards
const kpis = {
	totalBudget: companyTracking.allocated_amount,
	totalSpent: companyTracking.actual_spent,
	remaining: companyTracking.remaining_amount,
	percentageUsed: companyTracking.percentage_used,
	status: companyTracking.status, // 'safe', 'warning', 'danger', 'exceeded'
};
```

---

### 3. GET /api/v1/budgets/forecast

**Response:**

```json
{
	"success": true,
	"code": 200,
	"data": [
		{
			"forecast_id": 1,
			"allocation_id": 10,
			"hierarchy_level": "company",
			"warehouse_id": 1,
			"entity_name": "Avenzur Company",
			"period": "2025-10",
			"allocated_amount": 150000.0,
			"current_spent": 975.0,
			"days_used": 4,
			"days_remaining": 26,
			"burn_rate_daily": 97.5,
			"burn_rate_weekly": 682.5,
			"burn_rate_trend": "stable",
			"projected_end_of_month": 6435.0,
			"confidence_score": 85,
			"risk_level": "low",
			"will_exceed_budget": false,
			"recommendation": "Current spending is well within budget..."
		}
	]
}
```

**Mapping for Dashboard:**

```javascript
const forecastData = response.data[0];

// For forecast card
const forecast = {
	projectedTotal: forecastData.projected_end_of_month,
	riskLevel: forecastData.risk_level, // 'low', 'medium', 'high', 'critical'
	willExceed: forecastData.will_exceed_budget,
	burnRateDaily: forecastData.burn_rate_daily,
	daysRemaining: forecastData.days_remaining,
	recommendation: forecastData.recommendation,
};
```

---

### 4. GET /api/v1/budgets/alerts

**Response:**

```json
{
	"success": true,
	"code": 200,
	"data": [
		{
			"event_id": 1,
			"allocation_id": 10,
			"entity_name": "Avenzur Company",
			"hierarchy_level": "company",
			"period": "2025-10",
			"event_type": "threshold_exceeded",
			"status": "active",
			"percentage_at_trigger": 85,
			"amount_at_trigger": 127500.0,
			"triggered_at": "2025-10-25T10:30:00Z",
			"current_status": "safe",
			"current_percentage": 0.65,
			"risk_level": "low"
		}
	]
}
```

**Mapping for Dashboard:**

```javascript
const alerts = response.data;

// For alerts section
const activeAlerts = alerts
	.filter((a) => a.status === "active")
	.map((a) => ({
		id: a.event_id,
		message: `Budget alert: ${a.entity_name} at ${a.percentage_at_trigger}%`,
		severity: a.risk_level,
		timestamp: new Date(a.triggered_at),
	}));
```

---

## 📝 IMPLEMENTATION STEPS

### Step 1: Create API Service (5 minutes)

Create `/themes/blue/admin/js/services/budgetAPI.js`:

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
				throw new Error(`API Error: ${response.status} ${response.statusText}`);
			}

			return await response.json();
		} catch (error) {
			console.error("API Error:", error);
			throw error;
		}
	}

	getAuthToken() {
		// Get from localStorage or session
		return localStorage.getItem("auth_token") || "";
	}
}

const budgetAPI = new BudgetAPI();
```

### Step 2: Load API Service (2 minutes)

In dashboard template, add script tag:

```html
<script src="/themes/blue/admin/js/services/budgetAPI.js"></script>
```

### Step 3: Replace Data Loading (15 minutes)

Replace `generateMockData()` function:

```javascript
// OLD (DELETE THIS)
function generateMockData() { ... }

// NEW (REPLACE WITH THIS)
async function loadBudgetData() {
    try {
        // Show loading skeleton
        showLoadingState();

        // Load all data in parallel
        const [allocated, tracking, forecast, alerts] = await Promise.all([
            budgetAPI.getAllocated('2025-10'),
            budgetAPI.getTracking(),
            budgetAPI.getForecast(),
            budgetAPI.getAlerts()
        ]);

        // Process data
        const budgetData = processBudgetData(allocated, tracking, forecast, alerts);

        // Render dashboard
        renderDashboard(budgetData);

        // Hide loading
        hideLoadingState();

        return budgetData;
    } catch (error) {
        console.error('Failed to load budget data:', error);
        showErrorState(error);
    }
}

function processBudgetData(allocated, tracking, forecast, alerts) {
    const companyTracking = tracking.data?.find(t => t.hierarchy_level === 'company') || {};
    const companyForecast = forecast.data?.[0] || {};

    return {
        // KPI Cards
        totalBudget: companyTracking.allocated_amount || 0,
        totalSpent: companyTracking.actual_spent || 0,
        remaining: companyTracking.remaining_amount || 0,
        percentageUsed: companyTracking.percentage_used || 0,

        // Forecast
        projectedEndOfMonth: companyForecast.projected_end_of_month || 0,
        burnRateDaily: companyForecast.burn_rate_daily || 0,
        riskLevel: companyForecast.risk_level || 'low',
        recommendation: companyForecast.recommendation || '',

        // Status
        status: companyTracking.status || 'safe',  // 'safe', 'warning', 'danger', 'exceeded'

        // Alerts
        activeAlerts: alerts.data?.filter(a => a.status === 'active') || [],

        // Raw data for charts
        hierarchyData: allocated.data || [],
        trackingData: tracking.data || [],
        forecastData: forecast.data || [],
        alertsData: alerts.data || []
    };
}
```

### Step 4: Update KPI Cards Display (10 minutes)

```javascript
function renderDashboard(budgetData) {
	// Update KPI Cards
	updateKPICards(budgetData);

	// Update Charts
	updateCharts(budgetData);

	// Update Alerts
	updateAlerts(budgetData);
}

function updateKPICards(data) {
	// Card 1: Total Budget
	document.getElementById("kpi-total-budget").innerText = formatCurrency(
		data.totalBudget
	);

	// Card 2: Total Spent
	document.getElementById("kpi-total-spent").innerText = formatCurrency(
		data.totalSpent
	);

	// Card 3: Remaining
	document.getElementById("kpi-remaining").innerText = formatCurrency(
		data.remaining
	);

	// Card 4: Forecast
	document.getElementById("kpi-forecast").innerText = formatCurrency(
		data.projectedEndOfMonth
	);

	// Update Percentage Meter
	updateMeter(data.percentageUsed, data.status);
}

function formatCurrency(amount) {
	return new Intl.NumberFormat("en-SA", {
		style: "currency",
		currency: "SAR",
	}).format(amount);
}

function updateMeter(percentage, status) {
	const meter = document.getElementById("budget-meter");
	meter.style.background = getStatusColor(status);
	meter.setAttribute("aria-valuenow", percentage);
	meter.innerText = `${percentage.toFixed(1)}%`;
}

function getStatusColor(status) {
	const colors = {
		safe: "#10B981", // Green
		warning: "#F59E0B", // Amber
		danger: "#FB923C", // Orange
		exceeded: "#EF4444", // Red
	};
	return colors[status] || colors["safe"];
}
```

### Step 5: Update Charts (15 minutes)

```javascript
function updateCharts(data) {
	// Trend Chart (Daily Spending)
	updateTrendChart(data.trackingData);

	// Breakdown Chart (By Branch)
	updateBreakdownChart(data.hierarchyData);

	// Forecast Chart (Projections)
	updateForecastChart(data.forecastData);
}

function updateTrendChart(trackingData) {
	const container = document.getElementById("trend-chart");

	// Group by date (if you have historical data)
	const chartData = trackingData.map((t) => ({
		date: t.period,
		spent: t.actual_spent,
		allocated: t.allocated_amount,
		percentage: t.percentage_used,
	}));

	// Use ECharts or Chart.js to render
	const option = {
		xAxis: { type: "category", data: chartData.map((d) => d.date) },
		yAxis: { type: "value" },
		series: [
			{
				name: "Spent",
				data: chartData.map((d) => d.spent),
				type: "line",
				smooth: true,
			},
			{
				name: "Allocated",
				data: chartData.map((d) => d.allocated),
				type: "line",
				smooth: true,
			},
		],
	};

	// Initialize chart (assumes ECharts already loaded)
	const chart = echarts.init(container);
	chart.setOption(option);
}

function updateBreakdownChart(hierarchyData) {
	// Group by hierarchy level
	const byLevel = {};
	hierarchyData.forEach((item) => {
		if (!byLevel[item.hierarchy_level]) {
			byLevel[item.hierarchy_level] = [];
		}
		byLevel[item.hierarchy_level].push(item);
	});

	// Render pie chart or bar chart
	// ...
}

function updateForecastChart(forecastData) {
	// Show forecast scenarios (best, current, worst)
	// ...
}
```

### Step 6: Handle Alerts Display (10 minutes)

```javascript
function updateAlerts(data) {
	const alertsContainer = document.getElementById("alerts-section");

	if (data.activeAlerts.length === 0) {
		alertsContainer.innerHTML =
			'<p class="text-green-500">✅ No active alerts</p>';
		return;
	}

	const alertsHTML = data.activeAlerts
		.map(
			(alert) => `
        <div class="alert alert-${alert.risk_level}">
            <strong>${alert.entity_name}</strong>
            <p>Triggered at ${alert.percentage_at_trigger}% of budget</p>
            <button onclick="acknowledgeAlert(${alert.event_id})">Acknowledge</button>
        </div>
    `
		)
		.join("");

	alertsContainer.innerHTML = alertsHTML;
}

async function acknowledgeAlert(eventId) {
	try {
		await fetch(`/api/v1/budgets/alerts/${eventId}/acknowledge`, {
			method: "POST",
			headers: { Authorization: `Bearer ${budgetAPI.getAuthToken()}` },
		});

		// Reload alerts
		const alerts = await budgetAPI.getAlerts();
		updateAlerts(alerts.data);
	} catch (error) {
		console.error("Failed to acknowledge alert:", error);
	}
}
```

### Step 7: Add Error Handling (10 minutes)

```javascript
function showLoadingState() {
	const dashboard = document.getElementById("dashboard-container");
	dashboard.innerHTML = `
        <div class="skeleton-loader">
            <div class="skeleton-card"></div>
            <div class="skeleton-card"></div>
            <div class="skeleton-card"></div>
            <div class="skeleton-card"></div>
        </div>
    `;
}

function showErrorState(error) {
	const dashboard = document.getElementById("dashboard-container");
	dashboard.innerHTML = `
        <div class="alert alert-error">
            <h4>Failed to Load Budget Data</h4>
            <p>${error.message}</p>
            <button onclick="location.reload()">Retry</button>
        </div>
    `;
}

function hideLoadingState() {
	// CSS handles visibility toggle
}
```

### Step 8: Initialize on Page Load (5 minutes)

```javascript
// In dashboard template footer
document.addEventListener("DOMContentLoaded", async () => {
	// Load period from URL or default to current month
	const period =
		new URLSearchParams(window.location.search).get("period") ||
		new Date().toISOString().slice(0, 7);

	// Load budget data
	await loadBudgetData();

	// Refresh every 30 seconds
	setInterval(() => loadBudgetData(), 30000);
});

// Also load when period selector changes
document.getElementById("period-selector").addEventListener("change", (e) => {
	const period = e.target.value;
	window.history.pushState({}, "", `?period=${period}`);
	loadBudgetData();
});
```

---

## 🧪 TESTING CHECKLIST

- [ ] API calls return successful responses (200 OK)
- [ ] KPI cards display correct values from API
- [ ] Meter shows correct percentage
- [ ] Status color matches budget usage (green/yellow/orange/red)
- [ ] Charts render with real data
- [ ] Alerts display if any
- [ ] Error handling works (show error message on API failure)
- [ ] Loading state shows during API calls
- [ ] Data refreshes every 30 seconds
- [ ] Period selector updates dashboard
- [ ] Responsive on mobile devices
- [ ] Performance < 500ms for initial load

---

## 📊 SAMPLE OUTPUT

After integration, dashboard should display:

```
┌─────────────────────────────────────────────────────────┐
│                    Budget Dashboard                     │
│  Period: October 2025  [Refresh: 30s]  Period ▼         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌──────────────┬──────────────┬──────────────┬───────┐│
│  │ Total Budget │ Total Spent  │  Remaining   │Forecast││
│  │             │              │              │        ││
│  │ 150,000 SAR │  975.00 SAR  │149,025 SAR   │6,435   ││
│  │             │              │              │SAR     ││
│  │  ████░░░░░  │              │              │        ││
│  │    0.65%    │ Budget: 0%   │   99.3%      │ Low    ││
│  └──────────────┴──────────────┴──────────────┴───────┘│
│                                                         │
│  ✅ Active Alerts: 0                                   │
│                                                         │
│  Spending Trend                                        │
│  ┌─────────────────────────────────────────────────┐  │
│  │                                                 │  │
│  │  Line chart showing daily/weekly spending       │  │
│  │                                                 │  │
│  └─────────────────────────────────────────────────┘  │
│                                                         │
│  Budget by Branch                                      │
│  Pharmacy 1: 75,000  [450]   (0.60%)  ✅ Safe        │
│  Pharmacy 2: 75,000  [525]   (0.70%)  ✅ Safe        │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## ⚠️ COMMON ISSUES & SOLUTIONS

| Issue             | Cause                       | Solution                                           |
| ----------------- | --------------------------- | -------------------------------------------------- |
| "Failed to fetch" | API not accessible          | Check CORS headers, verify endpoint URL            |
| Data not updating | Cache issue                 | Clear browser cache, verify API returns fresh data |
| Wrong numbers     | Incorrect API field mapping | Check field names match API response               |
| Missing alerts    | Alert config not created    | Verify alert configs exist in database             |
| Slow load         | Large dataset               | Add pagination, filter by period                   |

---

## 📞 QUICK REFERENCE

**Period Format:** `YYYY-MM` (e.g., `2025-10`)  
**Status Values:** `safe`, `warning`, `danger`, `exceeded`  
**Risk Levels:** `low`, `medium`, `high`, `critical`  
**HTTP Timeout:** 5 seconds  
**Refresh Interval:** 30 seconds

---

**Next:** Hour 7 - End-to-End Testing
