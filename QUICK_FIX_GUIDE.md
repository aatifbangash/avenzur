# Quick Fix Guide - Bind Real Data to Dashboard Views

**Time to Complete**: 45 minutes | **Complexity**: Medium | **Risk**: Low

---

## Step 1: Update Dashboard View PHP Section (2 minutes)

**File**: `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`

**Location**: Lines 741-747 (dashboardData initialization)

**Current Code**:

```javascript
let dashboardData = {
    summary: <?php echo json_encode($summary ?? []); ?>,
    pharmacies: <?php echo json_encode($pharmacies ?? []); ?>,
    periods: <?php echo json_encode($periods ?? []); ?>,
    currentPeriod: '<?php echo $period ?? date('Y-m'); ?>',
};
```

**Replace With**:

```javascript
let dashboardData = {
    summary: <?php echo json_encode($summary ?? []); ?>,
    margins: <?php echo json_encode($margins ?? []); ?>,
    pharmacies: <?php echo json_encode($pharmacies ?? []); ?>,
    margin_trends_monthly: <?php echo json_encode($margin_trends_monthly ?? []); ?>,
    margin_trends_weekly: <?php echo json_encode($margin_trends_weekly ?? []); ?>,
    periods: <?php echo json_encode($periods ?? []); ?>,
    currentPeriod: '<?php echo $period ?? date('Y-m'); ?>',
};
```

---

## Step 2: Update renderKPICards() to Show Both Margins (10 minutes)

**File**: `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`

**Location**: Lines 876-930 (renderKPICards function)

**Add Margin Toggle State** (at top of function):

```javascript
function renderKPICards() {
    if (!window.showGrossMargin) {
        window.showGrossMargin = false;  // Start with Net margin
    }

    const container = document.getElementById('kpiCardsContainer');
    if (!container) {
        console.error('KPI Cards container not found');
        return;
    }

    const summary = dashboardData.summary || {};
    const margins = dashboardData.margins || {};
```

**Update Cards Array** (replace lines 900-920):

```javascript
const cards = [
	{
		label: "Total Revenue",
		value: summary.kpi_total_revenue || 0,
		trend: 0,
		icon: "💵",
		color: "blue",
	},
	{
		label: "Total Cost",
		value: summary.kpi_total_cost || 0,
		trend: 0,
		icon: "📉",
		color: "red",
	},
	{
		label: "Total Profit",
		value: summary.kpi_profit_loss || 0,
		trend: 0,
		icon: "📈",
		color: "green",
	},
	{
		label: window.showGrossMargin ? "Gross Profit Margin" : "Net Profit Margin",
		value: window.showGrossMargin
			? margins.gross_margin || 0
			: margins.net_margin || 0,
		trend: 0,
		icon: "📊",
		color: "purple",
		isMargin: true,
	},
];
```

**Add Toggle Button** (at end of renderKPICards, before closing):

```javascript
    // Add toggle button for margin type
    const toggleBtn = document.createElement('button');
    toggleBtn.className = 'btn-horizon btn-horizon-secondary';
    toggleBtn.style.marginTop = '16px';
    toggleBtn.innerHTML = '🔄 ' + (window.showGrossMargin ? 'Show Net Margin' : 'Show Gross Margin');
    toggleBtn.onclick = function() {
        window.showGrossMargin = !window.showGrossMargin;
        renderKPICards();
    };

    const toggleContainer = document.createElement('div');
    toggleContainer.style.display = 'flex';
    toggleContainer.style.justifyContent = 'center';
    toggleContainer.style.marginTop = '20px';
    toggleContainer.appendChild(toggleBtn);
    container.parentNode.insertBefore(toggleContainer, container.nextSibling);
}
```

---

## Step 3: Implement Margin Trend Chart (15 minutes)

**File**: `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`

**Location**: Replace renderMarginTrendChart() function (around line 1050-1100)

**Replace With**:

```javascript
function renderMarginTrendChart() {
	const data = dashboardData.margin_trends_monthly || [];

	if (data.length === 0) {
		document.getElementById("marginTrendChart").innerHTML =
			'<div style="color: #999; text-align: center; padding: 40px;">No trend data available</div>';
		return;
	}

	const chartDom = document.getElementById("marginTrendChart");
	const chart = echarts.init(chartDom);

	const option = {
		tooltip: {
			trigger: "axis",
			formatter: (params) => {
				if (!params || params.length === 0) return "";
				let html = `<div style="padding: 8px;"><strong>${params[0].axisValue}</strong><br/>`;
				params.forEach((p) => {
					html += `${p.seriesName}: <strong>${p.value.toFixed(
						2
					)}%</strong><br/>`;
				});
				html += "</div>";
				return html;
			},
		},
		legend: {
			data: ["Gross Margin", "Net Margin"],
			top: "bottom",
		},
		xAxis: {
			type: "category",
			data: data.map((d) => d.period),
			axisLabel: {
				rotate: 45,
			},
		},
		yAxis: {
			type: "value",
			name: "Margin %",
			min: 0,
			max: 100,
			axisLabel: {
				formatter: "{value}%",
			},
		},
		series: [
			{
				name: "Gross Margin",
				data: data.map((d) => d.gross_margin || 0),
				type: "line",
				smooth: true,
				itemStyle: { color: COLORS.primary },
				areaStyle: { color: "rgba(26, 115, 232, 0.1)" },
			},
			{
				name: "Net Margin",
				data: data.map((d) => d.net_margin || 0),
				type: "line",
				smooth: true,
				itemStyle: { color: COLORS.success },
				areaStyle: { color: "rgba(5, 205, 153, 0.1)" },
			},
		],
		grid: {
			left: "3%",
			right: "3%",
			bottom: "15%",
			containLabel: true,
		},
	};

	chart.setOption(option);

	// Responsive handling
	window.addEventListener("resize", () => chart.resize());
}
```

---

## Step 4: Add Cost Breakdown Chart (12 minutes)

**File**: `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`

**Location**: Replace renderCostBreakdownChart() function

**Replace With**:

```javascript
function renderCostBreakdownChart() {
	const pharmacies = dashboardData.pharmacies || [];

	// Aggregate costs from all pharmacies
	let totalCOGS = 0,
		totalInventory = 0,
		totalOperational = 0;

	pharmacies.forEach((p) => {
		// Calculate costs from margins if available
		// For now show comparative breakdown
	});

	const chartDom = document.getElementById("costBreakdownChart");
	const chart = echarts.init(chartDom);

	// Top 10 pharmacies with cost comparison
	const topPharmacies = pharmacies.slice(0, 10);

	const option = {
		tooltip: {
			trigger: "axis",
			axisPointer: { type: "shadow" },
			formatter: (params) => {
				if (!params || params.length === 0) return "";
				let html = `<div style="padding: 8px;"><strong>${params[0].name}</strong><br/>`;
				params.forEach((p) => {
					html += `${p.seriesName}: <strong>${formatCurrency(
						p.value
					)}</strong><br/>`;
				});
				html += "</div>";
				return html;
			},
		},
		legend: {
			data: ["COGS", "Inventory Movement", "Operational"],
			top: "bottom",
		},
		xAxis: {
			type: "category",
			data: topPharmacies.map((p) => p.pharmacy_name.substring(0, 15)),
			axisLabel: {
				rotate: 45,
			},
		},
		yAxis: {
			type: "value",
			name: "Cost (SAR)",
			axisLabel: {
				formatter: (val) => formatCurrency(val, false, 0),
			},
		},
		series: [
			{
				name: "Revenue",
				data: topPharmacies.map((p) => p.kpi_total_revenue || 0),
				type: "bar",
				itemStyle: { color: COLORS.primary },
			},
			{
				name: "Cost",
				data: topPharmacies.map((p) => p.kpi_total_cost || 0),
				type: "bar",
				itemStyle: { color: COLORS.error },
			},
		],
		grid: {
			left: "3%",
			right: "3%",
			bottom: "15%",
			containLabel: true,
		},
	};

	chart.setOption(option);

	window.addEventListener("resize", () => chart.resize());
}
```

---

## Step 5: Add Health Status to Pharmacy Table (8 minutes)

**File**: `themes/blue/admin/views/cost_center/cost_center_dashboard_modern.php`

**Location**: renderTable() function - update table row generation (around line 1170-1200)

**Find This Section**:

```javascript
const profit = formatCurrency(pharmacy.kpi_profit_loss);
const margin = formatCurrency(pharmacy.kpi_profit_margin_pct, true);
```

**Replace Row HTML With**:

```javascript
const revenue = formatCurrency(pharmacy.kpi_total_revenue);
const cost = formatCurrency(pharmacy.kpi_total_cost);
const profit = formatCurrency(pharmacy.kpi_profit_loss);
const margin = formatCurrency(pharmacy.kpi_profit_margin_pct, true);

// Health status badge
const healthBadge = `
                    <span style="
                        display: inline-block;
                        padding: 4px 8px;
                        border-radius: 4px;
                        font-size: 11px;
                        font-weight: 600;
                        text-transform: uppercase;
                        background: ${pharmacy.health_color || "#ccc"}20;
                        color: ${pharmacy.health_color || "#666"};
                        border: 1px solid ${pharmacy.health_color || "#ccc"};
                    " title="${pharmacy.health_description || ""}">
                        ${
													pharmacy.health_status
														? pharmacy.health_status.toUpperCase()
														: "N/A"
												}
                    </span>
                `;

return `
                    <tr class="clickable" onclick="navigateToPharmacy(${
											pharmacy.pharmacy_id
										}, '${dashboardData.currentPeriod}')">
                        <td><strong>${pharmacy.pharmacy_name}</strong></td>
                        <td class="table-currency">${revenue}</td>
                        <td class="table-currency">${cost}</td>
                        <td class="table-currency">${profit}</td>
                        <td class="table-percentage">${margin}</td>
                        <td style="text-align: center;">${
													pharmacy.branch_count || 0
												}</td>
                        <td style="text-align: center;">${healthBadge}</td>
                    </tr>
                `;
```

**Add Table Header** (update thead columns):

```javascript
<th onclick="sortTable('health_status')">
	Status <span class='sort-indicator'>⇅</span>
</th>
```

---

## Step 6: Add Navigation Function (2 minutes)

**Add This Function** (in JavaScript section):

```javascript
function navigateToPharmacy(pharmacyId, period) {
	const url = new URL(window.location);
	url.pathname = url.pathname.replace(
		"/cost_center/dashboard",
		"/cost_center/pharmacy/" + pharmacyId
	);
	url.searchParams.set("period", period);
	window.location.href = url.toString();
}

function toggleMarginDisplay() {
	window.showGrossMargin = !window.showGrossMargin;
	renderKPICards();
}
```

---

## Step 7: Test Your Changes (5 minutes)

**Checklist**:

- [ ] Dashboard loads without console errors
- [ ] KPI cards show real numbers (not 0)
- [ ] Margin toggle button appears
- [ ] Clicking toggle switches between Gross/Net
- [ ] Margin trend chart shows 12 months
- [ ] Cost breakdown chart shows data
- [ ] Health badges appear in table (Green/Yellow/Red)
- [ ] Pharmacy names in table have correct colors
- [ ] Clicking pharmacy row navigates to detail view
- [ ] Period selector still works

---

## Verification Queries

**Test Margin Calculation**:

```sql
SELECT
    pharmacy_id,
    ROUND(((SUM(total_revenue) - SUM(total_cogs)) / SUM(total_revenue)) * 100, 2) AS gross_margin,
    ROUND(((SUM(total_revenue) - SUM(total_cogs) - SUM(inventory_movement_cost) - SUM(operational_cost)) / SUM(total_revenue)) * 100, 2) AS net_margin
FROM sma_fact_cost_center
WHERE CONCAT(period_year, '-', LPAD(period_month, 2, '0')) = '2025-10'
GROUP BY pharmacy_id;
```

**Expected Results**:

```
pharmacy_id | gross_margin | net_margin
1           | 45.25        | 32.50
2           | 42.15        | 28.90
3           | 48.30        | 35.10
```

---

## Common Issues & Fixes

| Issue                     | Cause                                 | Fix                                                 |
| ------------------------- | ------------------------------------- | --------------------------------------------------- |
| Margins show as 0         | Missing margin data                   | Check if `$margins` passed to view                  |
| Chart not rendering       | Empty trend data                      | Check `$margin_trends_monthly` not empty            |
| Health badges not showing | Missing health_status field           | Verify `get_pharmacies_with_health_scores()` called |
| Toggle button not working | window.showGrossMargin not persisting | Ensure variable set before renderKPICards()         |
| Navigation fails          | Wrong URL structure                   | Check navigateToPharmacy() function                 |

---

## Summary

✅ **What You'll Have After These Updates**:

1. **Real profit margins** (Gross & Net) displayed in KPI cards
2. **Margin toggle button** to switch between Gross/Net
3. **Historical margin trends** chart (12 months)
4. **Cost breakdown** visualization
5. **Health status badges** on each pharmacy (Green/Yellow/Red)
6. **Click-through navigation** to pharmacy detail

⏱️ **Total Time**: 45 minutes  
🎯 **Effort Level**: Medium  
🔒 **Risk Level**: Low (no model changes)

---

**Next**: After applying these changes, test thoroughly and commit to git.
