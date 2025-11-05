<?php
/**
 * Loyalty Burn Rate Reporting Dashboard
 * 
 * Purpose: Monitor budget spending and burn rate with detailed analytics
 * 
 * Data Variables:
 * - $summary: Company-level KPI summary
 * - $daily_burn_data: Daily spend trend data
 * - $burn_rate_trend: Burn rate trend over time
 * - $pharmacy_breakdown: Spending by pharmacy
 * - $forecast_data: Projected spending
 * - $alerts: Active budget alerts
 * 
 * Features:
 * - KPI cards (Budget, Spent, Remaining, Burn Rate)
 * - Daily spending trend chart
 * - Burn rate trend analysis
 * - Expense vs Budget comparison
 * - Pharmacy-wise breakdown with drill-down
 * - Forecast projections
 * - Export reports
 * - Real-time updates
 */
?>

<style>
/* ============================================================================
   BURN RATE DASHBOARD - HORIZON UI DESIGN SYSTEM
   ============================================================================ */

:root {
    --horizon-primary: #1a73e8;
    --horizon-success: #05cd99;
    --horizon-error: #f34235;
    --horizon-warning: #ff9a56;
    --horizon-secondary: #6c5ce7;
    --horizon-dark-text: #111111;
    --horizon-light-text: #7a8694;
    --horizon-bg-light: #f5f5f5;
    --horizon-bg-neutral: #e0e0e0;
    --horizon-border: #e0e0e0;
    --horizon-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --horizon-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --horizon-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.horizon-dashboard {
    background: #ffffff;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    padding: 0;
}

/* ============================================================================
   HEADER / NAVBAR SECTION
   ============================================================================ */

.horizon-header {
    background: #ffffff;
    border-bottom: 1px solid var(--horizon-border);
    padding: 20px 24px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.horizon-header-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.horizon-header-title h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: var(--horizon-dark-text);
}

.horizon-header-title p {
    margin: 0;
    font-size: 14px;
    color: var(--horizon-light-text);
    font-weight: 400;
}

.horizon-header-controls {
    display: flex;
    gap: 12px;
    align-items: center;
}

/* ============================================================================
   CONTROL BAR SECTION
   ============================================================================ */

.horizon-control-bar {
    background: var(--horizon-bg-light);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.horizon-controls-left {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.horizon-controls-right {
    display: flex;
    gap: 12px;
}

.horizon-select-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.horizon-select-group label {
    font-size: 12px;
    font-weight: 600;
    color: var(--horizon-light-text);
    text-transform: uppercase;
}

.horizon-select-group select,
.horizon-select-group input {
    padding: 8px 12px;
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    font-size: 14px;
    font-family: inherit;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
}

.horizon-select-group select:hover,
.horizon-select-group input:hover {
    border-color: var(--horizon-primary);
}

.horizon-select-group select:focus,
.horizon-select-group input:focus {
    outline: none;
    border-color: var(--horizon-primary);
    box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
}

/* ============================================================================
   BUTTONS
   ============================================================================ */

.btn-horizon {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-horizon-primary {
    background: var(--horizon-primary);
    color: white;
}

.btn-horizon-primary:hover {
    background: #0d47a1;
    box-shadow: var(--horizon-shadow-md);
}

.btn-horizon-secondary {
    background: var(--horizon-bg-light);
    color: var(--horizon-dark-text);
    border: 1px solid var(--horizon-border);
}

.btn-horizon-secondary:hover {
    background: #f0f0f0;
}

/* ============================================================================
   KPI METRIC CARDS
   ============================================================================ */

.kpi-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.metric-card {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 12px;
    padding: 24px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.metric-card:hover {
    box-shadow: var(--horizon-shadow-lg);
    transform: translateY(-2px);
}

.metric-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
}

.metric-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.metric-card-icon.blue {
    background: rgba(26, 115, 232, 0.1);
    color: var(--horizon-primary);
}

.metric-card-icon.green {
    background: rgba(5, 205, 153, 0.1);
    color: var(--horizon-success);
}

.metric-card-icon.red {
    background: rgba(243, 66, 53, 0.1);
    color: var(--horizon-error);
}

.metric-card-icon.purple {
    background: rgba(108, 92, 231, 0.1);
    color: var(--horizon-secondary);
}

.metric-card-label {
    font-size: 14px;
    font-weight: 500;
    color: var(--horizon-light-text);
    margin-bottom: 8px;
}

.metric-card-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--horizon-dark-text);
    margin-bottom: 12px;
}

.metric-card-trend {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    font-weight: 600;
}

.metric-card-trend.positive {
    color: var(--horizon-success);
}

.metric-card-trend.negative {
    color: var(--horizon-error);
}

/* ============================================================================
   CHARTS SECTION
   ============================================================================ */

.charts-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.chart-container {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 12px;
    padding: 24px;
    box-shadow: var(--horizon-shadow-sm);
}

.chart-header {
    margin-bottom: 20px;
}

.chart-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--horizon-dark-text);
}

.chart-subtitle {
    margin: 4px 0 0 0;
    font-size: 12px;
    color: var(--horizon-light-text);
    font-weight: 400;
}

.chart-content {
    width: 100%;
    height: 300px;
}

/* ============================================================================
   TABLE SECTION
   ============================================================================ */

.table-section {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 12px;
    padding: 24px;
    box-shadow: var(--horizon-shadow-sm);
    margin-bottom: 20px;
}

.table-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.table-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--horizon-dark-text);
}

.table-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.table-wrapper {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.data-table thead {
    background: linear-gradient(135deg, var(--horizon-primary) 0%, var(--horizon-secondary) 100%);
    color: white;
}

.data-table thead tr th {
    padding: 16px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    user-select: none;
}

.data-table tbody tr {
    border-bottom: 1px solid var(--horizon-border);
    transition: background 0.2s ease;
}

.data-table tbody tr:hover {
    background: var(--horizon-bg-light);
}

.data-table tbody td {
    padding: 16px;
    color: var(--horizon-dark-text);
}

.sort-indicator {
    font-size: 10px;
    opacity: 0.6;
    margin-left: 4px;
}

/* ============================================================================
   RESPONSIVE DESIGN
   ============================================================================ */

@media (max-width: 767px) {
    .horizon-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .kpi-cards-grid {
        grid-template-columns: 1fr;
    }

    .charts-section {
        grid-template-columns: 1fr;
    }

    .table-wrapper {
        font-size: 12px;
    }

    .data-table th,
    .data-table td {
        padding: 8px 10px;
    }

    .horizon-control-bar {
        flex-direction: column;
        align-items: flex-start;
    }

    .horizon-controls-left,
    .horizon-controls-right {
        width: 100%;
        flex-direction: column;
    }

    .horizon-select-group select,
    .horizon-select-group input {
        width: 100%;
    }
}

@media (min-width: 768px) and (max-width: 1023px) {
    .kpi-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .charts-section {
        grid-template-columns: 1fr;
    }
}

@media (min-width: 1024px) {
    .kpi-cards-grid {
        grid-template-columns: repeat(4, 1fr);
    }

    .charts-section {
        grid-template-columns: repeat(2, 1fr);
    }
}

.skeleton-card {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 2s infinite;
    border-radius: 12px;
    height: 160px;
}

@keyframes skeleton-loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

.empty-state {
    text-align: center;
    padding: 48px 24px;
    color: var(--horizon-light-text);
}

.empty-state-icon {
    font-size: 48px;
    margin-bottom: 16px;
}

.empty-state-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--horizon-dark-text);
    margin-bottom: 8px;
}

/* ============================================================================
   LEGACY KPI CARDS (FALLBACK)
   ============================================================================ */

.kpi-card {
    background: white;
    border-radius: 12px;
    box-shadow: var(--horizon-shadow-sm);
    padding: 24px;
    margin-bottom: 20px;
    border: 1px solid var(--horizon-border);
    border-left: 4px solid var(--horizon-primary);
    transition: all 0.3s ease;
}

.kpi-card:hover {
    box-shadow: var(--horizon-shadow-lg);
    transform: translateY(-2px);
}

.kpi-card.primary { border-left-color: var(--horizon-primary); }
.kpi-card.warning { border-left-color: var(--horizon-warning); }
.kpi-card.success { border-left-color: var(--horizon-success); }
.kpi-card.danger { border-left-color: var(--horizon-error); }

.kpi-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--horizon-light-text);
    margin-bottom: 8px;
    letter-spacing: 0.5px;
}

.kpi-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--horizon-dark-text);
    margin-bottom: 8px;
}

.kpi-meta {
    font-size: 13px;
    color: var(--horizon-light-text);
}
}
</style>

<!-- Main Dashboard Container -->
<div class="horizon-dashboard">
    <!-- Header Section -->
    <div class="horizon-header">
        <div class="horizon-header-title">
            <h1>
                <i class="fa fa-fire" style="margin-right: 8px;"></i>
                Budget Burn Rate Dashboard
            </h1>
            <p>Monitor spending patterns and budget depletion rates</p>
        </div>
        <div class="horizon-header-controls">
            <button class="btn-horizon btn-horizon-secondary" onclick="location.reload()">
                <i class="fa fa-refresh"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Control Bar -->
    <div class="horizon-control-bar">
        <div class="horizon-controls-left">
            <div class="horizon-select-group">
                <label>Period</label>
                <select id="periodSelector" onchange="handlePeriodChange(this.value)">
                    <option value="today">Today</option>
                    <option value="week" selected>This Week</option>
                    <option value="month">This Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div class="horizon-select-group">
                <label>Pharmacy</label>
                <select id="pharmacyFilter" onchange="handlePharmacyFilter(this.value)">
                    <option value="">All Pharmacies</option>
                </select>
            </div>
        </div>
        <div class="horizon-controls-right">
            <button class="btn-horizon btn-horizon-primary" onclick="exportTableToCSV()">
                <i class="fa fa-download"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- KPI Metrics Cards -->
    <div id="kpiCardsContainer" class="kpi-cards-grid">
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
    </div>

    <!-- Charts Section -->
    <div id="chartsContainer" class="charts-section">
        <!-- Daily Spending Trend Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Daily Spending Trend</h3>
                <p class="chart-subtitle">Budget consumption over time</p>
            </div>
            <div id="dailyTrendChart" class="chart-content"></div>
        </div>

        <!-- Burn Rate Trend Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Burn Rate Analysis (30 Days)</h3>
                <p class="chart-subtitle">Daily burn rate trend</p>
            </div>
            <div id="burnRateTrendChart" class="chart-content"></div>
        </div>

        <!-- Expense vs Budget Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Expense vs Budget Comparison</h3>
                <p class="chart-subtitle">Pharmacy-wise allocation</p>
            </div>
            <div id="expenseVsBudgetChart" class="chart-content"></div>
        </div>

        <!-- Forecast Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Budget Forecast</h3>
                <p class="chart-subtitle">Projected spending to period end</p>
            </div>
            <div id="forecastChart" class="chart-content"></div>
        </div>
    </div>

    <!-- Pharmacy Data Table -->
    <div class="table-section">
        <div class="table-header-bar">
            <h3 class="table-title">Pharmacy Breakdown</h3>
            <div class="table-actions">
                <input type="text" id="tableSearch" placeholder="Search pharmacies..." style="padding: 8px 12px; border: 1px solid var(--horizon-border); border-radius: 6px; font-size: 14px;">
            </div>
        </div>
        <div class="table-wrapper">
            <table class="data-table" id="pharmacyTable">
                <thead>
                    <tr>
                        <th onclick="sortTable('pharmacy_name')">Pharmacy <span class="sort-indicator">⇅</span></th>
                        <th onclick="sortTable('budget_allocated')">Budget Allocated <span class="sort-indicator">⇅</span></th>
                        <th onclick="sortTable('amount_spent')">Amount Spent <span class="sort-indicator">⇅</span></th>
                        <th onclick="sortTable('remaining_budget')">Remaining <span class="sort-indicator">⇅</span></th>
                        <th onclick="sortTable('percentage_used')">% Used <span class="sort-indicator">⇅</span></th>
                        <th onclick="sortTable('daily_burn_rate')">Daily Burn Rate <span class="sort-indicator">⇅</span></th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px;">
                            <i class="fa fa-spinner fa-spin"></i> Loading data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include ECharts Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>

<script>
// ============================================================================
// BURN RATE DASHBOARD - JavaScript Logic
// ============================================================================

const COLORS = {
    primary: '#1a73e8',
    success: '#05cd99',
    error: '#f34235',
    warning: '#ff9a56',
    secondary: '#6c5ce7',
    darkText: '#111111',
    lightText: '#7a8694',
};

let dashboardData = {
    baseUrl: '<?php echo base_url(); ?>',
    summary: <?php echo json_encode($summary ?? []); ?>,
    pharmacies: <?php echo json_encode($pharmacy_breakdown ?? []); ?>,
    dailyBurnData: <?php echo json_encode($daily_burn_data ?? []); ?>,
    burnRateTrend: <?php echo json_encode($burn_rate_trend ?? []); ?>,
    forecastData: <?php echo json_encode($forecast_data ?? []); ?>,
    alerts: <?php echo json_encode($alerts ?? []); ?>,
};

let tableData = [...dashboardData.pharmacies];
let currentSort = { column: 'amount_spent', direction: 'DESC' };

// ============================================================================
// INITIALIZATION
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('Burn Rate Dashboard initializing...', dashboardData);
        initializeDashboard();
    } catch (error) {
        console.error('Error initializing dashboard:', error);
        console.error('Stack:', error.stack);
        showErrorBanner('Error initializing dashboard: ' + error.message);
    }
});

function initializeDashboard() {
    try {
        console.log('Step 1: Populating pharmacy filter');
        populatePharmacyFilter();
        
        console.log('Step 2: Rendering KPI cards');
        renderKPICards();
        
        console.log('Step 3: Rendering charts');
        renderCharts();
        
        console.log('Step 4: Rendering table');
        renderTable();
        
        console.log('Dashboard initialized successfully');
    } catch (error) {
        console.error('Error in initializeDashboard:', error);
        console.error('Stack:', error.stack);
        throw error;
    }
}

function showErrorBanner(message) {
    const banner = document.createElement('div');
    banner.style.cssText = 'background: #f34235; color: white; padding: 12px; margin: 10px; border-radius: 4px; font-weight: bold;';
    banner.textContent = '⚠️ ' + message;
    const header = document.querySelector('.horizon-header');
    if (header) {
        header.parentNode.insertBefore(banner, header.nextSibling);
    }
}

// ============================================================================
// PHARMACY FILTER
// ============================================================================

function populatePharmacyFilter() {
    const filter = document.getElementById('pharmacyFilter');
    const pharmacies = dashboardData.pharmacies || [];
    
    if (pharmacies.length > 0) {
        const uniquePharmacies = [...new Set(pharmacies.map(p => ({
            id: p.pharmacy_id,
            name: p.pharmacy_name
        })))];
        
        uniquePharmacies.forEach(pharmacy => {
            const option = document.createElement('option');
            option.value = pharmacy.id;
            option.textContent = pharmacy.name;
            filter.appendChild(option);
        });
    }
}

function handlePharmacyFilter(pharmacyId) {
    if (!pharmacyId) {
        tableData = [...dashboardData.pharmacies];
        renderTable();
        renderCharts();
        renderKPICards();
        return;
    }

    tableData = dashboardData.pharmacies.filter(p => p.pharmacy_id == pharmacyId);
    renderTable();
    renderCharts();
    renderKPICards();
}

function handlePeriodChange(period) {
    if (period && period !== 'custom') {
        const url = new URL(window.location);
        url.searchParams.set('period', period);
        window.location.href = url.toString();
    }
}

// ============================================================================
// KPI CARDS RENDERING
// ============================================================================

function renderKPICards() {
    const container = document.getElementById('kpiCardsContainer');
    if (!container) return;
    
    const summary = dashboardData.summary || {};

    const cards = [
        {
            label: 'Total Budget',
            value: summary.total_budget || 0,
            trend: 0,
            icon: '💰',
            color: 'blue'
        },
        {
            label: 'Total Spent',
            value: summary.total_spent || 0,
            trend: summary.spending_trend || 0,
            icon: '📊',
            color: 'red'
        },
        {
            label: 'Daily Burn Rate',
            value: summary.daily_burn_rate || 0,
            trend: 0,
            icon: '🔥',
            color: 'orange'
        },
        {
            label: 'Days Remaining',
            value: summary.days_remaining || 0,
            trend: 0,
            icon: '⏰',
            color: 'green',
            isCount: true
        }
    ];

    try {
        container.innerHTML = cards.map(card => {
            const formattedValue = card.isCount 
                ? card.value.toFixed(0)
                : formatCurrency(card.value);
            
            const trendValue = parseFloat(card.trend) || 0;
            const colorMap = { blue: 'blue', green: 'green', red: 'red', orange: 'purple' };
            
            return `
        <div class="metric-card">
            <div class="metric-card-header">
                <div style="flex: 1;">
                    <div class="metric-card-label">${card.label}</div>
                    <div class="metric-card-value">${formattedValue}</div>
                </div>
                <div class="metric-card-icon ${colorMap[card.color]}">${card.icon}</div>
            </div>
            <div class="metric-card-trend ${trendValue >= 0 ? 'positive' : 'negative'}">
                ${trendValue >= 0 ? '↑' : '↓'} ${Math.abs(trendValue).toFixed(1)}% trend
            </div>
        </div>
            `;
        }).join('');
    } catch (error) {
        console.error('Error rendering KPI cards:', error);
        container.innerHTML = `<div style="color: #f34235; padding: 20px;">Error rendering KPI cards: ${error.message}</div>`;
    }
}

// ============================================================================
// CHARTS RENDERING
// ============================================================================

function renderCharts() {
    try {
        console.log('Rendering Daily Trend Chart');
        renderDailyTrendChart();
    } catch (error) {
        console.error('Error rendering daily trend chart:', error);
    }
    
    try {
        console.log('Rendering Burn Rate Trend Chart');
        renderBurnRateTrendChart();
    } catch (error) {
        console.error('Error rendering burn rate trend chart:', error);
    }
    
    try {
        console.log('Rendering Expense vs Budget Chart');
        renderExpenseVsBudgetChart();
    } catch (error) {
        console.error('Error rendering expense vs budget chart:', error);
    }
    
    try {
        console.log('Rendering Forecast Chart');
        renderForecastChart();
    } catch (error) {
        console.error('Error rendering forecast chart:', error);
    }
}

function renderDailyTrendChart() {
    const chartDom = document.getElementById('dailyTrendChart');
    if (!chartDom) return;
    
    const chart = echarts.init(chartDom);
    const data = dashboardData.dailyBurnData || [];

    const option = {
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' },
            formatter: (params) => {
                if (!params[0]) return '';
                return `
                    <div style="padding: 8px;">
                        <strong>${params[0].name}</strong><br/>
                        Spent: <strong>${formatCurrency(params[0].value)}</strong>
                    </div>
                `;
            }
        },
        xAxis: {
            type: 'category',
            data: data.map(d => d.date || d.day),
            axisLabel: { fontSize: 11 }
        },
        yAxis: {
            type: 'value',
            axisLabel: { formatter: (val) => formatCurrency(val, false, 0) }
        },
        series: [{
            data: data.map(d => d.amount || 0),
            type: 'bar',
            itemStyle: { color: COLORS.primary },
            smooth: true
        }],
        grid: { left: 50, right: 20, top: 20, bottom: 60, containLabel: true }
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

function renderBurnRateTrendChart() {
    const chartDom = document.getElementById('burnRateTrendChart');
    if (!chartDom) return;
    
    const chart = echarts.init(chartDom);
    const data = dashboardData.burnRateTrend || [];

    const option = {
        tooltip: {
            trigger: 'axis',
            formatter: (params) => {
                if (!params[0]) return '';
                return `
                    <div style="padding: 8px;">
                        <strong>Day ${params[0].name}</strong><br/>
                        Burn Rate: <strong>${formatCurrency(params[0].value, false)}/day</strong>
                    </div>
                `;
            }
        },
        xAxis: {
            type: 'category',
            data: data.map((d, i) => i + 1)
        },
        yAxis: {
            type: 'value',
            axisLabel: { formatter: (val) => formatCurrency(val, false, 0) }
        },
        series: [{
            data: data.map(d => d.burn_rate || 0),
            type: 'line',
            smooth: true,
            itemStyle: { color: COLORS.warning },
            areaStyle: { color: 'rgba(255, 154, 86, 0.2)' }
        }],
        grid: { left: 50, right: 20, top: 20, bottom: 60, containLabel: true }
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

function renderExpenseVsBudgetChart() {
    const chartDom = document.getElementById('expenseVsBudgetChart');
    if (!chartDom) return;
    
    const chart = echarts.init(chartDom);
    const pharmacies = tableData.slice(0, 5);

    const option = {
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        legend: { data: ['Budget', 'Spent'], top: 0 },
        xAxis: {
            type: 'category',
            data: pharmacies.map(p => p.pharmacy_name || 'N/A')
        },
        yAxis: { type: 'value' },
        series: [
            {
                name: 'Budget',
                data: pharmacies.map(p => p.budget_allocated || 0),
                type: 'bar',
                itemStyle: { color: COLORS.primary }
            },
            {
                name: 'Spent',
                data: pharmacies.map(p => p.amount_spent || 0),
                type: 'bar',
                itemStyle: { color: COLORS.error }
            }
        ],
        grid: { left: 50, right: 20, top: 60, bottom: 60, containLabel: true }
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

function renderForecastChart() {
    const chartDom = document.getElementById('forecastChart');
    if (!chartDom) return;
    
    const chart = echarts.init(chartDom);
    const forecast = dashboardData.forecastData || [];

    const option = {
        tooltip: { trigger: 'axis' },
        xAxis: { type: 'category', data: forecast.map((d, i) => 'Day ' + (i + 1)) },
        yAxis: { type: 'value' },
        series: [{
            data: forecast.map(d => d.projected || 0),
            type: 'line',
            smooth: true,
            itemStyle: { color: COLORS.success },
            areaStyle: { color: 'rgba(5, 205, 153, 0.2)' }
        }],
        grid: { left: 50, right: 20, top: 20, bottom: 60, containLabel: true }
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

// ============================================================================
// TABLE RENDERING
// ============================================================================

function renderTable() {
    const container = document.getElementById('tableBody');
    if (!container || !tableData || tableData.length === 0) {
        if (container) {
            container.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #7a8694;">
                        <i class="fa fa-inbox" style="font-size: 32px; margin-bottom: 10px;"></i><br>
                        No pharmacy data available
                    </td>
                </tr>
            `;
        }
        return;
    }

    try {
        container.innerHTML = tableData.map((pharmacy) => {
            const percentage = pharmacy.budget_allocated > 0 
                ? ((pharmacy.amount_spent / pharmacy.budget_allocated) * 100).toFixed(1)
                : 0;
            
            let statusClass = 'status-safe';
            let statusText = 'Safe';
            if (percentage > 90) {
                statusClass = 'status-danger';
                statusText = 'Danger';
            } else if (percentage > 75) {
                statusClass = 'status-warning';
                statusText = 'Warning';
            }

            return `
            <tr>
                <td><strong>${pharmacy.pharmacy_name || 'N/A'}</strong></td>
                <td class="table-currency">${formatCurrency(pharmacy.budget_allocated || 0, false)}</td>
                <td class="table-currency" style="color: #05cd99; font-weight: 600;">${formatCurrency(pharmacy.amount_spent || 0, false)}</td>
                <td class="table-currency">${formatCurrency((pharmacy.budget_allocated - pharmacy.amount_spent) || 0, false)}</td>
                <td class="table-percentage">${percentage}%</td>
                <td class="table-currency">${formatCurrency(pharmacy.daily_burn_rate || 0, false)}</td>
                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="viewPharmacyDetail(${pharmacy.pharmacy_id})">
                        <i class="fa fa-eye"></i> View
                    </button>
                </td>
            </tr>
            `;
        }).join('');
    } catch (error) {
        console.error('Error rendering table:', error);
        container.innerHTML = `<tr><td colspan="8" style="color: #f34235; padding: 20px;">Error rendering table: ${error.message}</td></tr>`;
    }
}

function sortTable(column) {
    currentSort.direction = (currentSort.column === column && currentSort.direction === 'DESC') ? 'ASC' : 'DESC';
    currentSort.column = column;
    
    tableData.sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];
        
        if (typeof aVal === 'number' && typeof bVal === 'number') {
            return currentSort.direction === 'DESC' ? bVal - aVal : aVal - bVal;
        }
        
        aVal = String(aVal).toLowerCase();
        bVal = String(bVal).toLowerCase();
        
        return currentSort.direction === 'DESC' ? bVal.localeCompare(aVal) : aVal.localeCompare(bVal);
    });
    
    renderTable();
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

function formatCurrency(value, isPercentage = false, decimals = 2) {
    if (isPercentage) {
        return (value || 0).toFixed(decimals) + '%';
    }
    return 'SAR ' + (value || 0).toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

function formatNumber(value) {
    return (value || 0).toLocaleString('en-US');
}

function viewPharmacyDetail(pharmacyId) {
    alert('Drill-down to pharmacy ID: ' + pharmacyId + ' - Feature coming soon!');
}

function exportTableToCSV() {
    let csv = 'Pharmacy,Budget Allocated,Amount Spent,Remaining,% Used,Daily Burn Rate,Status\n';
    tableData.forEach(row => {
        csv += `"${row.pharmacy_name}",${row.budget_allocated},${row.amount_spent},${row.budget_allocated - row.amount_spent},${((row.amount_spent / row.budget_allocated) * 100).toFixed(1)},${row.daily_burn_rate}\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'burn_rate_dashboard.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
                    </h6>
                </div>
                <div class="card-body">
                    <div id="dailyTrendChart" style="width: 100%; height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Burn Rate Analysis Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-fire"></i> Burn Rate Trend
                    </h6>
                </div>
                <div class="card-body">
                    <div id="burnRateTrendChart" style="width: 100%; height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Expense vs Budget Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-bar"></i> Expense vs Budget
                    </h6>
                </div>
                <div class="card-body">
                    <div id="expenseVsBudgetChart" style="width: 100%; height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Forecast Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-star"></i> Budget Projection
                    </h6>
                </div>
                <div class="card-body">
                    <div id="forecastChart" style="width: 100%; height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pharmacy-wise Breakdown -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-secondary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-hospital"></i> Spending Breakdown by Pharmacy
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="pharmacyTable">
                    <thead class="table-light">
                        <tr>
                            <th>Pharmacy Name</th>
                            <th>Allocated Budget (SAR)</th>
                            <th>Spent (SAR)</th>
                            <th>Remaining (SAR)</th>
                            <th>Usage %</th>
                            <th>Status</th>
                            <th>Burn Rate (SAR/day)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($pharmacy_breakdown)): ?>
                            <?php foreach ($pharmacy_breakdown as $pharmacy): ?>
                                <tr onclick="drillDownPharmacy(<?php echo $pharmacy['id']; ?>)" style="cursor: pointer;">
                                    <td>
                                        <strong><?php echo htmlspecialchars($pharmacy['pharmacy_name']); ?></strong>
                                    </td>
                                    <td><?php echo number_format($pharmacy['allocated_budget'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($pharmacy['spent'] ?? 0, 2); ?></td>
                                    <td><?php echo number_format($pharmacy['remaining'] ?? 0, 2); ?></td>
                                    <td>
                                        <?php 
                                        $usage = ($pharmacy['allocated_budget'] > 0) 
                                            ? ($pharmacy['spent'] / $pharmacy['allocated_budget'] * 100) 
                                            : 0;
                                        ?>
                                        <div class="progress" style="height: 20px;">
                                            <div 
                                                class="progress-bar <?php echo ($usage > 90) ? 'bg-danger' : (($usage > 75) ? 'bg-warning' : 'bg-success'); ?>" 
                                                style="width: <?php echo min($usage, 100); ?>%"
                                            >
                                                <?php echo number_format($usage, 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $status_badge = 'badge-success';
                                        if ($usage > 90) {
                                            $status_badge = 'badge-danger';
                                        } elseif ($usage > 75) {
                                            $status_badge = 'badge-warning';
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_badge; ?>">
                                            <?php echo ($usage > 90) ? 'Critical' : (($usage > 75) ? 'Warning' : 'Good'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format($pharmacy['burn_rate'] ?? 0, 2); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="viewPharmacyDetail(event, <?php echo $pharmacy['id']; ?>)" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox"></i> No pharmacy data available
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Forecast Details -->
    <div class="card shadow">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-magic"></i> Forecast & Recommendations
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title font-weight-bold">Current Burn Rate</h6>
                            <h4><?php echo number_format($summary['daily_burn_rate'] ?? 0, 2); ?> SAR/day</h4>
                            <small class="text-muted">Average daily spending</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title font-weight-bold">Projected Month-End Spending</h6>
                            <h4><?php echo number_format($summary['projected_month_end'] ?? 0, 2); ?> SAR</h4>
                            <small class="text-muted">If burn rate continues</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title font-weight-bold">Budget Utilization Forecast</h6>
                            <h4>
                                <?php 
                                $forecast_pct = ($summary['total_budget'] > 0) 
                                    ? ($summary['projected_month_end'] / $summary['total_budget'] * 100) 
                                    : 0;
                                echo number_format($forecast_pct, 1) . '%';
                                ?>
                            </h4>
                            <small class="text-muted">By end of period</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-3 border-top">
                <h6 class="font-weight-bold mb-2">Recommendations</h6>
                <div id="recommendationsDiv">
                    <ul class="list-unstyled">
                        <?php if (isset($summary['recommendations']) && is_array($summary['recommendations'])): ?>
                            <?php foreach ($summary['recommendations'] as $rec): ?>
                                <li class="mb-2">
                                    <i class="fas fa-lightbulb text-warning"></i>
                                    <?php echo htmlspecialchars($rec); ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Budget is on track. Continue current spending patterns.
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

<script>
// Sample data - Replace with actual API calls
const dailyTrendData = <?php echo isset($daily_burn_data) ? json_encode($daily_burn_data) : '{}'; ?>;
const burnRateTrendData = <?php echo isset($burn_rate_trend) ? json_encode($burn_rate_trend) : '{}'; ?>;
const forecastData = <?php echo isset($forecast_data) ? json_encode($forecast_data) : '{}'; ?>;

$(document).ready(function() {
    // Initialize DataTable for pharmacy table
    $('#pharmacyTable').DataTable({
        "order": [[4, 'desc']], // Sort by Usage % descending
        "pageLength": 10,
        "paging": true,
        "searching": true,
        "info": true
    });

    // Initialize charts
    initializeDailyTrendChart();
    initializeBurnRateTrendChart();
    initializeExpenseVsBudgetChart();
    initializeForecastChart();
});

/**
 * Initialize Daily Trend Chart
 */
function initializeDailyTrendChart() {
    const chartDom = document.getElementById('dailyTrendChart');
    const myChart = echarts.init(chartDom);

    const option = {
        responsive: true,
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'cross' }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14']
        },
        yAxis: {
            type: 'value',
            name: 'Amount (SAR)',
            axisLabel: { formatter: '{value}' }
        },
        series: [
            {
                name: 'Daily Spending',
                type: 'line',
                data: [5200, 5400, 5100, 5600, 5300, 5800, 5500, 5900, 6100, 5800, 6000, 5700, 5900, 6200],
                smooth: true,
                areaStyle: { color: 'rgba(0, 123, 255, 0.2)' },
                itemStyle: { color: '#007bff' },
                lineStyle: { color: '#007bff', width: 2 }
            }
        ]
    };

    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
}

/**
 * Initialize Burn Rate Trend Chart
 */
function initializeBurnRateTrendChart() {
    const chartDom = document.getElementById('burnRateTrendChart');
    const myChart = echarts.init(chartDom);

    const option = {
        responsive: true,
        tooltip: { trigger: 'axis' },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5']
        },
        yAxis: {
            type: 'value',
            name: 'Burn Rate (SAR/day)'
        },
        series: [
            {
                name: 'Weekly Burn Rate',
                type: 'line',
                data: [5000, 5200, 5400, 5600, 5800],
                smooth: true,
                itemStyle: { color: '#dc3545' },
                lineStyle: { color: '#dc3545', width: 2 },
                areaStyle: { color: 'rgba(220, 53, 69, 0.2)' }
            }
        ]
    };

    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
}

/**
 * Initialize Expense vs Budget Chart
 */
function initializeExpenseVsBudgetChart() {
    const chartDom = document.getElementById('expenseVsBudgetChart');
    const myChart = echarts.init(chartDom);

    const option = {
        responsive: true,
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        xAxis: {
            type: 'category',
            data: ['Pharmacy A', 'Pharmacy B', 'Pharmacy C', 'Pharmacy D', 'Pharmacy E']
        },
        yAxis: { type: 'value', name: 'Amount (SAR)' },
        series: [
            {
                name: 'Budget',
                type: 'bar',
                data: [100000, 95000, 110000, 85000, 120000],
                itemStyle: { color: '#28a745' }
            },
            {
                name: 'Spent',
                type: 'bar',
                data: [75000, 68000, 82000, 72000, 95000],
                itemStyle: { color: '#ffc107' }
            }
        ]
    };

    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
}

/**
 * Initialize Forecast Chart
 */
function initializeForecastChart() {
    const chartDom = document.getElementById('forecastChart');
    const myChart = echarts.init(chartDom);

    const option = {
        responsive: true,
        tooltip: { trigger: 'axis' },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: ['Day 1', 'Day 5', 'Day 10', 'Day 15', 'Day 20', 'Day 25', 'Day 30']
        },
        yAxis: { type: 'value', name: 'Cumulative Spending (SAR)' },
        series: [
            {
                name: 'Budget Limit',
                type: 'line',
                data: [0, 26000, 52000, 78000, 104000, 130000, 156000],
                lineStyle: { color: '#dc3545', type: 'dashed', width: 2 }
            },
            {
                name: 'Best Case',
                type: 'line',
                data: [0, 20000, 40000, 60000, 80000, 100000, 120000],
                smooth: true,
                itemStyle: { color: '#28a745' }
            },
            {
                name: 'Current Trend',
                type: 'line',
                data: [0, 26500, 53000, 79500, 106000, 132500, 159000],
                smooth: true,
                itemStyle: { color: '#ffc107' }
            },
            {
                name: 'Worst Case',
                type: 'line',
                data: [0, 32000, 64000, 96000, 128000, 160000, 192000],
                smooth: true,
                itemStyle: { color: '#dc3545' }
            }
        ]
    };

    myChart.setOption(option);
    window.addEventListener('resize', () => myChart.resize());
}

/**
 * Change period
 */
function changePeriod(period) {
    window.location.href = '<?php echo admin_url('loyalty/burn_rate?period='); ?>' + period;
}

/**
 * Export report
 */
function exportReport() {
    alert('Report export functionality - to be implemented');
}

/**
 * Drill down to pharmacy details
 */
function drillDownPharmacy(pharmacyId) {
    window.location.href = '<?php echo admin_url('loyalty/pharmacy_detail/'); ?>' + pharmacyId;
}

/**
 * View pharmacy detail with event prevention
 */
function viewPharmacyDetail(event, pharmacyId) {
    event.stopPropagation();
    drillDownPharmacy(pharmacyId);
}

/**
 * Format number with commas
 */
function number_format(n, c) {
    n = (n + '').split('.');
    const x = n[0];
    const y = n[1];
    const sx = x.split('').reverse();
    let r = [];
    for (let i = 0; i < sx.length; i++) {
        if (i !== 0 && (i % 3) === 0) r.push(',');
        r.push(sx[i]);
    }
    r = r.reverse().join('');
    return r + (c ? '.' + y : '');
}
</script>

<style>
.border-left-primary {
    border-left: 4px solid #007bff !important;
}
.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}
.border-left-success {
    border-left: 4px solid #28a745 !important;
}
.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}
.table-hover tbody tr:hover {
    background-color: #f5f5f5;
}
</style>
