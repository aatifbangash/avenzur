<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Accounts Dashboard - Modern Horizon UI Design
 * 
 * Features:
 * - KPI Metric Cards (Total Budget, Sales, Collections, Purchases, Profit)
 * - ECharts Visualizations (Time Series, Bar Charts, Pie Charts)
 * - Financial Performance Metrics
 * - Period and Report Type Filters
 * - Responsive Design (Desktop, Tablet, Mobile)
 * - Real-time Data Updates
 * 
 * Design System: Horizon UI (Consistent with Cost Center Dashboard)
 * - Primary Blue: #1a73e8
 * - Success Green: #05cd99
 * - Error Red: #f34235
 * - Warning Orange: #ff9a56
 * - Secondary Purple: #6c5ce7
 * 
 * Date: 2025-10-30
 * 
 * Note: Header, sidebar, and footer are loaded by the controller.
 * This view contains only the main dashboard content.
 */
?>

<!-- Horizon UI Modern Dashboard -->
<style>
/* ============================================================================
   HORIZON UI Design System - CSS Variables & Global Styles
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

* {
    box-sizing: border-box;
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

.metric-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--horizon-primary);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.metric-card:hover::before {
    opacity: 1;
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

.metric-card-icon.orange {
    background: rgba(255, 154, 86, 0.1);
    color: var(--horizon-warning);
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
    font-family: 'Courier New', monospace;
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

.metric-card-trend .icon {
    width: 16px;
    height: 16px;
}

/* ============================================================================
   CHART SECTION
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
}

.chart-header {
    margin-bottom: 20px;
}

.chart-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--horizon-dark-text);
    margin: 0 0 4px 0;
}

.chart-subtitle {
    font-size: 12px;
    color: var(--horizon-light-text);
    margin: 0;
}

.chart-content {
    min-height: 350px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ============================================================================
   TABLE SECTION
   ============================================================================ */

.table-section {
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 30px;
}

.table-header-bar {
    background: var(--horizon-bg-light);
    padding: 16px 24px;
    border-bottom: 1px solid var(--horizon-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--horizon-dark-text);
}

.table-actions {
    display: flex;
    gap: 8px;
}

.table-wrapper {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: var(--horizon-bg-light);
}

.data-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: var(--horizon-dark-text);
    border-bottom: 1px solid var(--horizon-border);
    cursor: pointer;
    user-select: none;
    text-transform: uppercase;
}

.data-table th:hover {
    background: #e8e8e8;
}

.data-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--horizon-border);
    font-size: 14px;
}

.data-table tbody tr {
    transition: background 0.2s ease;
}

.data-table tbody tr:hover {
    background: var(--horizon-bg-light);
    cursor: pointer;
}

.table-currency {
    font-family: 'Courier New', monospace;
    text-align: right;
    font-weight: 600;
}

.table-percentage {
    font-family: 'Courier New', monospace;
    text-align: right;
    font-weight: 600;
}

.table-status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.table-status-positive {
    background: rgba(5, 205, 153, 0.1);
    color: var(--horizon-success);
}

.table-status-negative {
    background: rgba(243, 66, 53, 0.1);
    color: var(--horizon-error);
}

/* ============================================================================
   BUTTONS
   ============================================================================ */

.btn-horizon {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.btn-horizon-primary {
    background: var(--horizon-primary);
    color: white;
}

.btn-horizon-primary:hover {
    background: #1557b0;
    box-shadow: var(--horizon-shadow-md);
}

.btn-horizon-secondary {
    background: var(--horizon-bg-light);
    color: var(--horizon-dark-text);
    border: 1px solid var(--horizon-border);
}

.btn-horizon-secondary:hover {
    background: #e0e0e0;
}

/* ============================================================================
   LOADING & EMPTY STATES
   ============================================================================ */

.skeleton-card {
    background: #f0f0f0;
    border-radius: 12px;
    height: 160px;
    animation: skeleton-loading 1s linear infinite alternate;
}

@keyframes skeleton-loading {
    0% {
        background: #f0f0f0;
    }
    100% {
        background: #e0e0e0;
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
   RESPONSIVE DESIGN
   ============================================================================ */

/* Mobile: 320px - 767px */
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

/* Tablet: 768px - 1023px */
@media (min-width: 768px) and (max-width: 1023px) {
    .kpi-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .charts-section {
        grid-template-columns: 1fr;
    }
}

/* Desktop: 1024px+ */
@media (min-width: 1024px) {
    .kpi-cards-grid {
        grid-template-columns: repeat(5, 1fr);
    }

    .charts-section {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Large Desktop: 1920px+ */
@media (min-width: 1920px) {
    .charts-section {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* ============================================================================
   LOADER STYLES
   ============================================================================ */

.spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(26, 115, 232, 0.2);
    border-radius: 50%;
    border-top-color: var(--horizon-primary);
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.chart-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 350px;
    flex-direction: column;
    gap: 16px;
}

.chart-loading .spinner {
    width: 40px;
    height: 40px;
}
</style>

<!-- HTML Structure -->
<div class="horizon-dashboard">
    <!-- Header -->
    <div class="horizon-header">
        <div class="horizon-header-title">
            <h1>📊 Finance Dashboard</h1>
            <p>Account & Financial Performance Overview</p>
        </div>
    </div>

    <!-- Control Bar -->
    <div class="horizon-control-bar">
        <div class="horizon-controls-left">
            <div class="horizon-select-group">
                <label for="reportType">Report Type</label>
                <select id="reportType" onchange="updateDashboard()">
                    <option value="ytd" selected>Year to Date</option>
                    <option value="monthly">Monthly</option>
                    <option value="today">Today</option>
                </select>
            </div>
            
            <div class="horizon-select-group">
                <label for="referenceDate">Date</label>
                <input type="date" id="referenceDate" value="<?php echo date('Y-m-d'); ?>" onchange="updateDashboard()" />
            </div>
        </div>

        <div class="horizon-controls-right">
            <button class="btn-horizon btn-horizon-primary" onclick="exportData('json')">📥 Export JSON</button>
            <button class="btn-horizon btn-horizon-secondary" onclick="exportData('csv')">📥 Export CSV</button>
            <button class="btn-horizon btn-horizon-primary" onclick="refreshData()">🔄 Refresh</button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-cards-grid" id="kpiCardsContainer">
        <!-- Cards will be populated by JavaScript -->
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
        <div class="skeleton-card"></div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <!-- Sales Trend Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Sales Trend</h3>
                <p class="chart-subtitle">Daily sales over selected period</p>
            </div>
            <div id="salesTrendChart" class="chart-content">
                <div class="chart-loading">
                    <div class="spinner"></div>
                    <span>Loading chart...</span>
                </div>
            </div>
        </div>

        <!-- Collection Trend Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Collection Trend</h3>
                <p class="chart-subtitle">Daily collections over selected period</p>
            </div>
            <div id="collectionTrendChart" class="chart-content">
                <div class="chart-loading">
                    <div class="spinner"></div>
                    <span>Loading chart...</span>
                </div>
            </div>
        </div>

        <!-- Purchase Breakdown Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Purchase Summary</h3>
                <p class="chart-subtitle">Purchase amounts by period</p>
            </div>
            <div id="purchaseChart" class="chart-content">
                <div class="chart-loading">
                    <div class="spinner"></div>
                    <span>Loading chart...</span>
                </div>
            </div>
        </div>

        <!-- Revenue Distribution -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Revenue Distribution</h3>
                <p class="chart-subtitle">Sales vs Collection vs Purchase</p>
            </div>
            <div id="revenueDistributionChart" class="chart-content">
                <div class="chart-loading">
                    <div class="spinner"></div>
                    <span>Loading chart...</span>
                </div>
            </div>
        </div>

        <!-- Purchase Items Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Top Purchase Items</h3>
                <p class="chart-subtitle">Purchase items by amount</p>
            </div>
            <div id="purchaseItemsChart" class="chart-content">
                <div class="chart-loading">
                    <div class="spinner"></div>
                    <span>Loading chart...</span>
                </div>
            </div>
        </div>

        <!-- Customer Summary -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Customer Credit Analysis</h3>
                <p class="chart-subtitle">Credit limit vs balance overview</p>
            </div>
            <div id="customerChartContainer" class="chart-content">
                <div class="chart-loading">
                    <div class="spinner"></div>
                    <span>Loading chart...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Summary Table -->
    <div class="table-section">
        <div class="table-header-bar">
            <h3 class="table-title">Sales Summary by Branch</h3>
            <div class="table-actions">
                <input type="text" placeholder="Search..." id="salesTableSearch" style="padding: 8px 12px; border: 1px solid var(--horizon-border); border-radius: 6px;" onkeyup="filterTable('salesTable', this.value)">
            </div>
        </div>
        <div class="table-wrapper">
            <table class="data-table" id="salesTable">
                <thead>
                    <tr>
                        <th onclick="sortTable('salesTable', 0)">Branch Name ↕</th>
                        <th onclick="sortTable('salesTable', 1)" class="table-currency">Sales Amount ↕</th>
                        <th onclick="sortTable('salesTable', 2)" class="table-currency">Sale Count ↕</th>
                        <th class="table-currency">Avg Transaction</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody">
                    <tr><td colspan="4" style="text-align: center; padding: 20px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Purchase Summary Table -->
    <div class="table-section">
        <div class="table-header-bar">
            <h3 class="table-title">Top Purchase Items</h3>
            <div class="table-actions">
                <input type="text" placeholder="Search..." id="purchaseTableSearch" style="padding: 8px 12px; border: 1px solid var(--horizon-border); border-radius: 6px;" onkeyup="filterTable('purchaseTable', this.value)">
            </div>
        </div>
        <div class="table-wrapper">
            <table class="data-table" id="purchaseTable">
                <thead>
                    <tr>
                        <th onclick="sortTable('purchaseTable', 0)">Product Name ↕</th>
                        <th onclick="sortTable('purchaseTable', 1)" class="table-currency">Quantity ↕</th>
                        <th onclick="sortTable('purchaseTable', 2)" class="table-currency">Total Amount ↕</th>
                        <th class="table-currency">Avg Unit Cost</th>
                        <th class="table-currency">Purchase Count</th>
                    </tr>
                </thead>
                <tbody id="purchaseTableBody">
                    <tr><td colspan="5" style="text-align: center; padding: 20px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ECharts Library -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

<!-- Dashboard JavaScript -->
<script>
// Global state
let dashboardData = {};
let dashboardTrends = {};
let charts = {};

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    updateDashboard();
});

// Update dashboard with new data
function updateDashboard() {
    const reportType = document.getElementById('reportType').value;
    const referenceDate = document.getElementById('referenceDate').value;

    // Fetch dashboard data
    fetch(`<?php echo base_url('admin/accounts_dashboard/get_data'); ?>?report_type=${reportType}&reference_date=${referenceDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                dashboardData = data.data;
                dashboardTrends = data.trends || {}; // Store trends for use in renderKPICards
                renderKPICards();
                renderCharts();
                renderTables();
            } else {
                showError('Failed to load dashboard data');
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            showError('Error loading dashboard data');
        });
}

// Render KPI Cards
function renderKPICards() {
    const sales_summary = dashboardData.sales_summary?.[0] || {};
    const collection_summary = dashboardData.collection_summary?.[0] || {};
    const purchase_summary = dashboardData.purchase_summary?.[0] || {};
    const overall_summary = dashboardData.overall_summary || {};
    const trends = dashboardTrends || {};
    
    // Calculate net sales
    const net_sales = (sales_summary.total_sales || 0) - (sales_summary.total_discount || 0);
    
    // Calculate profit
    const profit = (overall_summary.total_sales_revenue || 0) - (overall_summary.total_purchase_cost || 0);
    
    const kpiData = [
        {
            label: 'Total Sales',
            value: formatCurrencyShort(sales_summary.total_sales || 0),
            icon: '💰',
            color: 'blue',
            trend: (trends.sales_trend || 0) >= 0 ? `+${trends.sales_trend || 0}%` : `${trends.sales_trend || 0}%`
        },
        {
            label: 'Total Collections',
            value: formatCurrencyShort(collection_summary.total_collected || 0),
            icon: '📈',
            color: 'green',
            trend: (trends.collections_trend || 0) >= 0 ? `+${trends.collections_trend || 0}%` : `${trends.collections_trend || 0}%`
        },
        {
            label: 'Total Purchases',
            value: formatCurrencyShort(purchase_summary.total_purchase || 0),
            icon: '🛍️',
            color: 'orange',
            trend: (trends.purchases_trend || 0) >= 0 ? `+${trends.purchases_trend || 0}%` : `${trends.purchases_trend || 0}%`
        },
        {
            label: 'Net Sales',
            value: formatCurrencyShort(net_sales),
            icon: '📊',
            color: 'purple',
            trend: (trends.net_sales_trend || 0) >= 0 ? `+${trends.net_sales_trend || 0}%` : `${trends.net_sales_trend || 0}%`
        },
        {
            label: 'Total Profit',
            value: formatCurrencyShort(profit),
            icon: '💹',
            color: profit > 0 ? 'green' : 'red',
            trend: (trends.profit_trend || 0) >= 0 ? `+${trends.profit_trend || 0}%` : `${trends.profit_trend || 0}%`
        }
    ];

    const container = document.getElementById('kpiCardsContainer');
    container.innerHTML = kpiData.map(card => `
        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">${card.label}</div>
                    <div class="metric-card-value">${card.value}</div>
                </div>
                <div class="metric-card-icon ${card.color}">${card.icon}</div>
            </div>
            <div class="metric-card-trend ${card.trend.startsWith('+') ? 'positive' : 'negative'}">
                ${card.trend.startsWith('+') ? '📈' : '📉'} ${card.trend} from last period
            </div>
        </div>
    `).join('');
}

// Render Charts
function renderCharts() {
    const summary = dashboardData.sales_summary || [];
    
    // Sales Trend Chart
    renderSalesTrendChart(summary);
    
    // Collection Trend Chart
    renderCollectionTrendChart(dashboardData.collection_summary || []);
    
    // Purchase Chart
    renderPurchaseChart(dashboardData.purchase_summary || []);
    
    // Revenue Distribution
    renderRevenueDistribution();
    
    // Purchase Items Chart
    renderPurchaseItemsChart(dashboardData.purchase_per_item || []);
    
    // Customer Chart
    renderCustomerChart(dashboardData.customer_summary || []);
}

// Sales Trend Chart
function renderSalesTrendChart(data) {
    const chartDom = document.getElementById('salesTrendChart');
    if (charts.salesTrend) charts.salesTrend.dispose();
    
    const chart = echarts.init(chartDom);
    charts.salesTrend = chart;

    const option = {
        color: ['#1a73e8', '#05cd99'],
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(50, 50, 50, 0.7)',
            borderColor: '#333',
            textStyle: { color: '#fff' },
            formatter: function(params) {
                let result = '';
                params.forEach((param, index) => {
                    result += `${param.marker} ${param.seriesName}: ${formatCurrency(param.value)}<br/>`;
                });
                return result;
            }
        },
        grid: {
            left: '10%',
            right: '10%',
            bottom: '15%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: data.length > 0 ? data.map((_, i) => `Day ${i + 1}`) : [],
            boundaryGap: false,
            axisLabel: {
                color: '#7a8694',
                fontSize: 12
            }
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                color: '#7a8694',
                fontSize: 12,
                formatter: function(value) {
                    return formatCurrencyShort(value);
                }
            }
        },
        series: [
            {
                name: 'Gross Sales',
                data: data.map(item => item.total_gross_sales || 0),
                type: 'line',
                smooth: true,
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(26, 115, 232, 0.3)' },
                        { offset: 1, color: 'rgba(26, 115, 232, 0)' }
                    ])
                },
                itemStyle: {
                    color: '#1a73e8',
                    borderColor: '#fff',
                    borderWidth: 2
                },
                lineStyle: {
                    color: '#1a73e8',
                    width: 3
                }
            },
            {
                name: 'Net Sales',
                data: data.map(item => item.total_net_sales || 0),
                type: 'line',
                smooth: true,
                itemStyle: {
                    color: '#05cd99'
                },
                lineStyle: {
                    color: '#05cd99',
                    width: 2,
                    type: 'dashed'
                }
            }
        ]
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

// Collection Trend Chart
function renderCollectionTrendChart(data) {
    const chartDom = document.getElementById('collectionTrendChart');
    if (charts.collectionTrend) charts.collectionTrend.dispose();
    
    const chart = echarts.init(chartDom);
    charts.collectionTrend = chart;

    const option = {
        color: ['#05cd99'],
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(50, 50, 50, 0.7)',
            borderColor: '#333',
            textStyle: { color: '#fff' }
        },
        grid: {
            left: '10%',
            right: '10%',
            bottom: '15%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: data.length > 0 ? data.map((_, i) => `Day ${i + 1}`) : [],
            boundaryGap: false,
            axisLabel: {
                color: '#7a8694',
                fontSize: 12
            }
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                color: '#7a8694',
                fontSize: 12,
                formatter: function(value) {
                    return formatCurrencyShort(value);
                }
            }
        },
        series: [
            {
                name: 'Collections',
                data: data.map(item => item.total_collection || 0),
                type: 'line',
                smooth: true,
                areaStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        { offset: 0, color: 'rgba(5, 205, 153, 0.3)' },
                        { offset: 1, color: 'rgba(5, 205, 153, 0)' }
                    ])
                },
                itemStyle: {
                    color: '#05cd99'
                },
                lineStyle: {
                    color: '#05cd99',
                    width: 3
                }
            }
        ]
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

// Purchase Chart
function renderPurchaseChart(data) {
    const chartDom = document.getElementById('purchaseChart');
    if (charts.purchase) charts.purchase.dispose();
    
    const chart = echarts.init(chartDom);
    charts.purchase = chart;

    const option = {
        color: ['#ff9a56'],
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(50, 50, 50, 0.7)',
            borderColor: '#333',
            textStyle: { color: '#fff' }
        },
        grid: {
            left: '10%',
            right: '10%',
            bottom: '15%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: data.length > 0 ? data.map((_, i) => `Period ${i + 1}`) : [],
            axisLabel: {
                color: '#7a8694',
                fontSize: 12
            }
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                color: '#7a8694',
                fontSize: 12,
                formatter: function(value) {
                    return formatCurrencyShort(value);
                }
            }
        },
        series: [
            {
                name: 'Total Purchases',
                data: data.map(item => item.total_purchase || 0),
                type: 'bar',
                itemStyle: {
                    color: '#ff9a56',
                    borderRadius: [8, 8, 0, 0]
                }
            }
        ]
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

// Revenue Distribution Chart
function renderRevenueDistribution() {
    const summary = dashboardData.overall_summary || {};
    const chartDom = document.getElementById('revenueDistributionChart');
    if (charts.revenueDistribution) charts.revenueDistribution.dispose();
    
    const chart = echarts.init(chartDom);
    charts.revenueDistribution = chart;

    const data = [
        { value: summary.total_gross_sales || 0, name: 'Sales', color: '#1a73e8' },
        { value: summary.total_collection || 0, name: 'Collections', color: '#05cd99' },
        { value: summary.total_purchase || 0, name: 'Purchases', color: '#ff9a56' }
    ];

    const option = {
        tooltip: {
            trigger: 'item',
            backgroundColor: 'rgba(50, 50, 50, 0.7)',
            borderColor: '#333',
            textStyle: { color: '#fff' },
            formatter: function(params) {
                return `${params.name}: ${formatCurrency(params.value)}`;
            }
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            textStyle: {
                color: '#7a8694'
            }
        },
        series: [
            {
                name: 'Amount',
                type: 'pie',
                radius: '50%',
                data: data,
                itemStyle: {
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    color: '#333',
                    fontSize: 12,
                    formatter: '{b}: {d}%'
                },
                emphasis: {
                    itemStyle: {
                        shadowBlur: 10,
                        shadowOffsetX: 0,
                        shadowColor: 'rgba(0, 0, 0, 0.5)'
                    }
                }
            }
        ]
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

// Purchase Items Chart
function renderPurchaseItemsChart(data) {
    const chartDom = document.getElementById('purchaseItemsChart');
    if (charts.purchaseItems) charts.purchaseItems.dispose();
    
    const chart = echarts.init(chartDom);
    charts.purchaseItems = chart;

    const topItems = data.slice(0, 10);
    const names = topItems.map(item => item.product_name || item.product_code || 'Unknown');
    const amounts = topItems.map(item => item.total_amount || 0);

    const option = {
        color: ['#6c5ce7'],
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(50, 50, 50, 0.7)',
            borderColor: '#333',
            textStyle: { color: '#fff' },
            formatter: function(params) {
                if (params.length > 0) {
                    return `${params[0].name}: ${formatCurrency(params[0].value)}`;
                }
            }
        },
        grid: {
            left: '20%',
            right: '10%',
            bottom: '15%',
            containLabel: true
        },
        xAxis: {
            type: 'value',
            axisLabel: {
                color: '#7a8694',
                fontSize: 12,
                formatter: function(value) {
                    return formatCurrencyShort(value);
                }
            }
        },
        yAxis: {
            type: 'category',
            data: names,
            axisLabel: {
                color: '#7a8694',
                fontSize: 12
            }
        },
        series: [
            {
                name: 'Amount',
                data: amounts,
                type: 'bar',
                itemStyle: {
                    color: '#6c5ce7',
                    borderRadius: [0, 8, 8, 0]
                }
            }
        ]
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

// Customer Chart
function renderCustomerChart(data) {
    const chartDom = document.getElementById('customerChartContainer');
    if (charts.customer) charts.customer.dispose();
    
    const chart = echarts.init(chartDom);
    charts.customer = chart;

    const topCustomers = data.slice(0, 5);
    const names = topCustomers.map(item => item.customer_name || 'Unknown');
    const creditLimits = topCustomers.map(item => item.credit_limit || 0);
    const balances = topCustomers.map(item => item.balance || 0);

    const option = {
        color: ['#1a73e8', '#f34235'],
        tooltip: {
            trigger: 'axis',
            backgroundColor: 'rgba(50, 50, 50, 0.7)',
            borderColor: '#333',
            textStyle: { color: '#fff' }
        },
        grid: {
            left: '10%',
            right: '10%',
            bottom: '15%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: names,
            axisLabel: {
                color: '#7a8694',
                fontSize: 12
            }
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                color: '#7a8694',
                fontSize: 12,
                formatter: function(value) {
                    return formatCurrencyShort(value);
                }
            }
        },
        series: [
            {
                name: 'Credit Limit',
                data: creditLimits,
                type: 'bar',
                itemStyle: {
                    color: '#1a73e8',
                    borderRadius: [8, 8, 0, 0]
                }
            },
            {
                name: 'Balance',
                data: balances,
                type: 'bar',
                itemStyle: {
                    color: '#f34235',
                    borderRadius: [8, 8, 0, 0]
                }
            }
        ]
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

// Render Tables
function renderTables() {
    renderSalesTable();
    renderPurchaseTable();
}

// Render Sales Table
function renderSalesTable() {
    const data = dashboardData.sales_summary || [];
    const tbody = document.getElementById('salesTableBody');
    
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 20px;">No data available</td></tr>';
        return;
    }

    tbody.innerHTML = data.map(row => `
        <tr>
            <td>${row.branch_name || 'N/A'}</td>
            <td class="table-currency">${formatCurrency(row.total_gross_sales || 0)}</td>
            <td class="table-currency">${row.sale_count || 0}</td>
            <td class="table-currency">${formatCurrency((row.total_gross_sales || 0) / (row.sale_count || 1))}</td>
        </tr>
    `).join('');
}

// Render Purchase Table
function renderPurchaseTable() {
    const data = dashboardData.purchase_per_item || [];
    const tbody = document.getElementById('purchaseTableBody');
    
    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No data available</td></tr>';
        return;
    }

    tbody.innerHTML = data.slice(0, 20).map(row => `
        <tr>
            <td>${row.product_name || row.product_code || 'Unknown'}</td>
            <td class="table-currency">${row.total_quantity || 0}</td>
            <td class="table-currency">${formatCurrency(row.total_amount || 0)}</td>
            <td class="table-currency">${formatCurrency(row.avg_unit_cost || 0)}</td>
            <td class="table-currency">${row.purchase_count || 0}</td>
        </tr>
    `).join('');
}

// Utility Functions
function formatCurrency(value) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'SAR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

function formatCurrencyShort(value) {
    const isNegative = value < 0;
    const absValue = Math.abs(value);
    let result = '';
    
    if (absValue >= 1000000) {
        result = (absValue / 1000000).toFixed(1) + 'M';
    } else if (absValue >= 1000) {
        result = (absValue / 1000).toFixed(1) + 'K';
    } else {
        return formatCurrency(value);
    }
    
    return isNegative ? '-' + result : result;
}

function exportData(format) {
    const reportType = document.getElementById('reportType').value;
    const referenceDate = document.getElementById('referenceDate').value;
    const url = `<?php echo base_url('admin/accounts_dashboard/export'); ?>?report_type=${reportType}&reference_date=${referenceDate}&format=${format}`;
    window.location.href = url;
}

function refreshData() {
    updateDashboard();
    showSuccess('Dashboard refreshed successfully');
}

function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        const aVal = a.cells[columnIndex].innerText;
        const bVal = b.cells[columnIndex].innerText;
        
        if (!isNaN(aVal) && !isNaN(bVal)) {
            return parseFloat(aVal) - parseFloat(bVal);
        }
        return aVal.localeCompare(bVal);
    });

    rows.forEach(row => tbody.appendChild(row));
}

function filterTable(tableId, searchTerm) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(searchTerm.toLowerCase()) ? '' : 'none';
    });
}

function showError(message) {
    console.error(message);
    // You can replace this with a proper notification system
    alert('Error: ' + message);
}

function showSuccess(message) {
    console.log(message);
    // You can replace this with a proper notification system
}

// Resize charts on window resize
window.addEventListener('resize', () => {
    Object.values(charts).forEach(chart => {
        if (chart) chart.resize();
    });
});
</script>
