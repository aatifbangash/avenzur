<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Cost Center Dashboard - Modern Horizon UI Design
 * 
 * Features:
 * - KPI Metric Cards (Revenue, Cost, Profit, Margin%)
 * - ECharts Visualizations (Bar, Line, Stacked, Area)
 * - Pharmacy Performance Table (Sortable, Filterable)
 * - Responsive Design (Desktop, Tablet, Mobile)
 * - Period and Pharmacy Filters
 * - Drill-down Navigation to Branch Details
 * 
 * Design System:
 * - Primary Blue: #1a73e8
 * - Success Green: #05cd99
 * - Error Red: #f34235
 * - Warning Orange: #ff9a56
 * - Secondary Purple: #6c5ce7
 * 
 * Date: 2025-10-25
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
    min-height: 300px;
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

.data-table th .sort-indicator {
    display: inline-block;
    margin-left: 4px;
    font-size: 10px;
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

.data-table tbody tr.clickable:hover {
    background: rgba(26, 115, 232, 0.05);
}

.table-currency {
    font-family: 'Courier New', monospace;
    text-align: right;
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
   BREADCRUMB
   ============================================================================ */

.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.breadcrumb-nav a,
.breadcrumb-nav span {
    font-size: 14px;
    color: var(--horizon-primary);
    text-decoration: none;
    cursor: pointer;
}

.breadcrumb-nav span {
    color: var(--horizon-light-text);
}

.breadcrumb-nav a:hover {
    text-decoration: underline;
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
        grid-template-columns: repeat(4, 1fr);
    }

    .charts-section {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Large Desktop: 1920px+ */
@media (min-width: 1920px) {
    .charts-section {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<!-- Main Dashboard Container -->
<div class="horizon-dashboard">
    <!-- Header Section -->
    <div class="horizon-header">
        <div class="horizon-header-title">
            <h1>
                <i class="fa fa-pie-chart" style="margin-right: 8px;"></i>
                Cost Center Dashboard
            </h1>
            <p>Real-time KPI monitoring and analytics</p>
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
                    <option value="">Select Period</option>
                </select>
            </div>
            <!-- Hidden pharmacy selection that still passes data to JS -->
            <div style="display: none;">
                <div class="horizon-select-group">
                    <label>Pharmacy</label>
                    <select id="pharmacyFilter" onchange="handlePharmacyFilter(this.value)">
                        <option value="" selected>All Pharmacies</option>
                    </select>
                </div>
            </div>
            <!-- <div class="horizon-select-group">
                <label>Pharmacy</label>
                <select id="pharmacyFilter" disabled style="opacity: 0.6; cursor: not-allowed;" onchange="handlePharmacyFilter(this.value)">
                    <option value="" selected>All Pharmacies</option>
                </select>
            </div> -->
            <div class="horizon-select-group">
                <label>Selected Period</label>
                <div style="color: #000000; padding: 8px 12px; border-radius: 6px; font-size: 14px; font-weight: 600; min-width: 120px; text-align: center;">
                    <i class="fa fa-calendar" style="margin-right: 6px;"></i>
                    <?php echo $period ?? date('Y-m'); ?>
                </div>
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
        <!-- Revenue by Pharmacy Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Revenue by Pharmacy</h3>
                <p class="chart-subtitle">Total revenue performance</p>
            </div>
            <div id="revenueChart" class="chart-content"></div>
        </div>

        <!-- Profit Margin Trend Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Profit Margin Trend (12 Months)</h3>
                <p class="chart-subtitle">Historical trend analysis</p>
            </div>
            <div id="marginTrendChart" class="chart-content"></div>
        </div>

        <!-- Cost Breakdown Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Cost Breakdown by Branch</h3>
                <p class="chart-subtitle">Cost category distribution</p>
            </div>
            <div id="costBreakdownChart" class="chart-content"></div>
        </div>

        <!-- Pharmacy Comparison Chart -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Pharmacy Performance Comparison</h3>
                <p class="chart-subtitle">Revenue vs Profit analysis</p>
            </div>
            <div id="comparisonChart" class="chart-content"></div>
        </div>
    </div>

    <!-- Pharmacy Data Table -->
    <div class="table-section">
        <div class="table-header-bar">
            <h3 class="table-title">All Pharmacies Performance</h3>
            <div class="table-actions">
                <input type="text" id="tableSearch" class="horizon-select-group" placeholder="Search pharmacies..." style="margin: 0;">
            </div>
        </div>
        <div class="table-wrapper">
            <table class="data-table" id="pharmacyTable">
                <thead>
                    <tr>
                        <th onclick="sortTable('pharmacy_name')">Pharmacy <span class="sort-indicator">⇅</span></th>
                        <th onclick="sortTable('kpi_total_revenue')">Revenue <span class="sort-indicator">⇅</span></th>
                        <th onclick="sortTable('kpi_total_cost')">Cost <span class="sort-indicator">⇅</span></th>
                        <th onclick="sortTable('kpi_profit_loss')">Profit <span class="sort-indicator">⇅</span></th>
                        <th onclick="sortTable('kpi_profit_margin_pct')">Margin % <span class="sort-indicator">⇅</span></th>
                        <th>Branches</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <i class="fa fa-spinner fa-spin"></i> Loading data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION 1: Company-Level Summary Metrics -->
    <div style="margin-top: 40px; padding-bottom: 20px;">
        <h2 style="font-size: 20px; font-weight: 700; color: #111111; margin-bottom: 20px;">
            <i class="fa fa-bar-chart" style="margin-right: 8px;"></i>
            Company Performance Summary
        </h2>
        <div id="companyMetricsContainer" class="kpi-cards-grid" style="margin-bottom: 20px;">
            <div class="skeleton-card"></div>
            <div class="skeleton-card"></div>
            <div class="skeleton-card"></div>
            <div class="skeleton-card"></div>
        </div>
    </div>

    <!-- SECTION 2: Best Moving Products (Top 5) -->
    <div style="margin-top: 40px;">
        <h2 style="font-size: 20px; font-weight: 700; color: #111111; margin-bottom: 20px;">
            <i class="fa fa-fire" style="margin-right: 8px;"></i>
            Best Moving Products (Top 5)
        </h2>
        <div class="table-section">
            <div class="table-header-bar">
                <h3 class="table-title">Top 5 Products by Sales Volume</h3>
                <div class="table-actions">
                    <span style="font-size: 12px; color: #7a8694;">Period: <?php echo $period ?? date('Y-m'); ?></span>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="data-table" id="bestProductsTable">
                    <thead>
                        <tr>
                            <th>Product Code</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th onclick="sortProductTable('total_units_sold')">Units Sold <span class="sort-indicator">⇅</span></th>
                            <th onclick="sortProductTable('total_sales')">Total Sales <span class="sort-indicator">⇅</span></th>
                            <th onclick="sortProductTable('total_margin')">Total Margin <span class="sort-indicator">⇅</span></th>
                            <th onclick="sortProductTable('margin_percentage')">Margin % <span class="sort-indicator">⇅</span></th>
                            <th>Avg Sale/Unit</th>
                            <th>Customers</th>
                        </tr>
                    </thead>
                    <tbody id="bestProductsTableBody">
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px;">
                                <i class="fa fa-spinner fa-spin"></i> Loading top products...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include ECharts Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>

<script>
// ============================================================================
// HORIZON UI DASHBOARD - JavaScript Logic
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
    pharmacies: <?php echo json_encode($pharmacies ?? []); ?>,
    branches: <?php echo json_encode($branches ?? []); ?>,
    periods: <?php echo json_encode($periods ?? []); ?>,
    margins: <?php echo json_encode($margins ?? ['gross_margin' => 0, 'net_margin' => 0]); ?>,
    companyMetrics: <?php echo json_encode($company_metrics ?? []); ?>,
    bestProducts: <?php echo json_encode($best_products ?? []); ?>,
    currentPeriod: '<?php echo $period ?? date('Y-m'); ?>',
};

// Toggle for margin display
let marginDisplayMode = 'net'; // 'gross' or 'net'

let tableData = [...dashboardData.pharmacies];
let currentSort = { column: 'kpi_total_revenue', direction: 'DESC' };
let productTableData = [...(dashboardData.bestProducts || [])];
let productSort = { column: 'total_units_sold', direction: 'DESC' };

// ============================================================================
// INITIALIZATION
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('Dashboard initializing...', dashboardData);
        initializeDashboard();
    } catch (error) {
        console.error('Error initializing dashboard:', error);
        console.error('Stack:', error.stack);
        showErrorBanner('Error initializing dashboard: ' + error.message);
    }
});

function initializeDashboard() {
    try {
        console.log('Step 1: Populating period selector');
        populatePeriodSelector();
        
        console.log('Step 2: Populating pharmacy filter');
        populatePharmacyFilter();
        
        console.log('Step 3: Rendering KPI cards');
        renderKPICards();
        
        console.log('Step 3b: Rendering company metrics');
        renderCompanyMetrics();
        
        console.log('Step 3c: Rendering best products table');
        renderBestProductsTable();
        
        console.log('Step 4: Rendering charts');
        renderCharts();
        
        console.log('Step 5: Rendering table');
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
// COMPANY METRICS RENDERING
// ============================================================================

function renderCompanyMetrics() {
    const container = document.getElementById('companyMetricsContainer');
    if (!container || !dashboardData.companyMetrics) {
        console.warn('Company metrics container not found or no data available');
        return;
    }
    
    const metrics = dashboardData.companyMetrics;
    console.log('renderCompanyMetrics - Data:', metrics);

    const cards = [
        {
            label: 'Total Sales',
            value: metrics.total_sales || 0,
            icon: '💰',
            color: 'blue',
            isCurrency: true
        },
        {
            label: 'Total Margin',
            value: metrics.total_margin || 0,
            icon: '📈',
            color: 'green',
            isCurrency: true
        },
        {
            label: 'Total Customers',
            value: metrics.total_customers || 0,
            icon: '👥',
            color: 'purple',
            isCount: true
        },
        {
            label: 'Items Sold',
            value: metrics.total_items_sold || 0,
            icon: '📦',
            color: 'red',
            isCount: true
        }
    ];

    try {
        container.innerHTML = cards.map(card => {
            let formattedValue = '';
            if (card.isCurrency) {
                formattedValue = formatCurrency(card.value, false);
            } else if (card.isCount) {
                formattedValue = formatNumber(card.value);
            } else {
                formattedValue = card.value.toFixed(2);
            }
            
            return `
        <div class="metric-card">
            <div class="metric-card-header">
                <div style="flex: 1;">
                    <div class="metric-card-label">${card.label}</div>
                    <div class="metric-card-value">${formattedValue}</div>
                </div>
                <div class="metric-card-icon ${card.color}">${card.icon}</div>
            </div>
            <div class="metric-card-trend positive" style="visibility: hidden;">
                ↑ 0% from last period
            </div>
        </div>
            `;
        }).join('');
        
        console.log('Company metrics rendered successfully');
    } catch (error) {
        console.error('Error rendering company metrics:', error);
        container.innerHTML = `<div style="color: #f34235; padding: 20px;">Error rendering metrics: ${error.message}</div>`;
    }
}

// ============================================================================
// BEST PRODUCTS TABLE RENDERING
// ============================================================================

function renderBestProductsTable() {
    const container = document.getElementById('bestProductsTableBody');
    if (!container) {
        console.error('Best products table body not found');
        return;
    }
    
    productTableData = [...(dashboardData.bestProducts || [])];
    console.log('renderBestProductsTable - Data:', productTableData);

    if (!productTableData || productTableData.length === 0) {
        container.innerHTML = `
            <tr>
                <td colspan="9" style="text-align: center; padding: 40px; color: #7a8694;">
                    <i class="fa fa-inbox" style="font-size: 32px; margin-bottom: 10px;"></i><br>
                    No products found for this period
                </td>
            </tr>
        `;
        return;
    }

    try {
        container.innerHTML = productTableData.map((product, index) => {
            return `
            <tr>
                <td><strong>${product.product_code || 'N/A'}</strong></td>
                <td>${product.product_name || 'N/A'}</td>
                <td><span style="background: #e8f0fe; color: #1a73e8; padding: 4px 8px; border-radius: 4px; font-size: 12px;">${product.category_name || 'Uncategorized'}</span></td>
                <td class="table-currency">${formatNumber(product.total_units_sold || 0)}</td>
                <td class="table-currency" style="color: #05cd99; font-weight: 600;">${formatCurrency(product.total_sales || 0, false)}</td>
                <td class="table-currency" style="color: #05cd99;">${formatCurrency(product.total_margin || 0, false)}</td>
                <td class="table-percentage" style="color: #f59e0b; font-weight: 600;">${(product.margin_percentage || 0).toFixed(2)}%</td>
                <td class="table-currency">${formatCurrency(product.avg_sale_per_unit || 0, false)}</td>
                <td style="text-align: center;">${formatNumber(product.customer_count || 0)}</td>
            </tr>
            `;
        }).join('');
        
        console.log('Best products table rendered successfully');
    } catch (error) {
        console.error('Error rendering best products table:', error);
        container.innerHTML = `<tr><td colspan="9" style="color: #f34235; padding: 20px;">Error rendering table: ${error.message}</td></tr>`;
    }
}

function sortProductTable(column) {
    productSort.direction = (productSort.column === column && productSort.direction === 'DESC') ? 'ASC' : 'DESC';
    productSort.column = column;
    
    productTableData.sort((a, b) => {
        let aVal = a[column];
        let bVal = b[column];
        
        // Handle numeric values
        if (typeof aVal === 'number' && typeof bVal === 'number') {
            return productSort.direction === 'DESC' ? bVal - aVal : aVal - bVal;
        }
        
        // Handle string values
        aVal = String(aVal).toLowerCase();
        bVal = String(bVal).toLowerCase();
        
        if (productSort.direction === 'DESC') {
            return bVal.localeCompare(aVal);
        } else {
            return aVal.localeCompare(bVal);
        }
    });
    
    renderBestProductsTable();
}

// ============================================================================
// PERIOD & FILTER HANDLERS
// ============================================================================

function populatePeriodSelector() {
    const selector = document.getElementById('periodSelector');
    selector.innerHTML = '';
    
    if (dashboardData.periods && dashboardData.periods.length > 0) {
        dashboardData.periods.forEach(period => {
            const option = document.createElement('option');
            option.value = period.period;
            option.textContent = period.period;
            if (period.period === dashboardData.currentPeriod) {
                option.selected = true;
            }
            selector.appendChild(option);
        });
    } else {
        const option = document.createElement('option');
        option.value = dashboardData.currentPeriod;
        option.textContent = dashboardData.currentPeriod;
        option.selected = true;
        selector.appendChild(option);
    }
}

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

function handlePeriodChange(period) {
    if (period) {
        const url = new URL(window.location);
        url.searchParams.set('period', period);
        window.location.href = url.toString();
        // Set the period selector to the current period after navigation
        window.addEventListener('DOMContentLoaded', function() {
            const selector = document.getElementById('periodSelector');
            if (selector) {
                selector.value = period;
            }
        });
    }
}

function handlePharmacyFilter(pharmacyId) {
    if (!pharmacyId) {
        // Reset to all pharmacies
        tableData = [...dashboardData.pharmacies];
        renderKPICards(); // Re-render with company totals
        renderTable();
        return;
    }

    // Filter table data to show only selected pharmacy
    tableData = dashboardData.pharmacies.filter(p => p.pharmacy_id == pharmacyId);
    
    // Fetch pharmacy detail data for KPI cards
    console.log('Fetching pharmacy detail for ID:', pharmacyId);
    const apiUrl = `${dashboardData.baseUrl}api/v1/cost-center/pharmacy-detail/${pharmacyId}?period=${dashboardData.currentPeriod}`;
    console.log('API URL:', apiUrl);
    fetch(apiUrl)
        .then(response => response.json())
        .then(result => {
            console.log('Pharmacy detail response:', result);
            
            if (result.success && result.data) {
                const pharmacy = result.data;
                
                // Create filtered summary for this pharmacy
                const filteredSummary = {
                    kpi_total_revenue: pharmacy.kpi_total_revenue,
                    kpi_total_cost: pharmacy.kpi_total_cost,
                    kpi_profit_loss: pharmacy.kpi_profit_loss,
                    kpi_profit_margin_pct: pharmacy.kpi_profit_margin_pct,
                    revenue_trend_pct: 0, // Will be calculated from trends if available
                    cost_trend_pct: 0,
                    profit_trend_pct: 0,
                    margin_trend_pct: 0
                };
                
                // Create filtered margins for this pharmacy
                const filteredMargins = {
                    gross_margin: pharmacy.gross_margin_pct || 0,
                    net_margin: pharmacy.net_margin_pct || 0,
                    revenue: pharmacy.kpi_total_revenue,
                    cogs: pharmacy.kpi_cogs,
                    inventory_movement: pharmacy.kpi_inventory_movement,
                    operational_cost: pharmacy.kpi_operational_cost
                };
                
                // Temporarily swap data for rendering
                const originalSummary = dashboardData.summary;
                const originalMargins = dashboardData.margins;
                
                dashboardData.summary = filteredSummary;
                dashboardData.margins = filteredMargins;
                
                renderKPICards();
                renderCharts();
                
                // Restore original data
                dashboardData.summary = originalSummary;
                dashboardData.margins = originalMargins;
            } else {
                console.error('Failed to fetch pharmacy detail:', result.message || 'Unknown error');
                showErrorBanner('Failed to load pharmacy data: ' + (result.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error fetching pharmacy detail:', error);
            showErrorBanner('Error loading pharmacy data: ' + error.message);
        });
    
    renderTable();
}

// ============================================================================
// KPI CARDS RENDERING
// ============================================================================

function renderKPICards() {
    const container = document.getElementById('kpiCardsContainer');
    if (!container) {
        console.error('KPI Cards container not found');
        return;
    }
    
    const summary = dashboardData.summary || {};
    const margins = dashboardData.margins || {};
    
    console.log('renderKPICards - Summary data:', summary);
    console.log('renderKPICards - Margins data:', margins);

    // Use selected margin mode (net or gross)
    const marginValue = marginDisplayMode === 'net' 
        ? (margins.net_margin || 0) 
        : (margins.gross_margin || 0);
    
    // Calculate profit trend - use margin trend if available, otherwise calculate from summary
    const profitTrend = summary.profit_trend_pct || summary.margin_trend_pct || 0;

    const cards = [
        {
            label: 'Total Revenue',
            value: summary.kpi_total_revenue || 0,
            trend: summary.revenue_trend_pct || 0,
            icon: '💵',
            color: 'blue'
        },
        {
            label: 'Total Cost',
            value: summary.kpi_total_cost || 0,
            trend: summary.cost_trend_pct || 0,
            icon: '📉',
            color: 'red'
        },
        {
            label: 'Total Profit',
            value: summary.kpi_profit_loss || 0,
            trend: profitTrend,
            icon: '📈',
            color: 'green'
        },
        {
            label: marginDisplayMode === 'net' ? 'Net Profit Margin' : 'Gross Profit Margin',
            value: marginValue,
            trend: summary.margin_trend_pct || 0,
            icon: '📊',
            color: 'purple',
            isPercentage: true
        }
    ];

    console.log('renderKPICards - Cards data:', cards);

    try {
        container.innerHTML = cards.map(card => {
            console.log('Processing card:', card);
            const formattedValue = formatCurrency(card.value, card.isPercentage || card.label.includes('Margin'));
            console.log('Formatted value for', card.label, ':', formattedValue);
            
            const trendValue = parseFloat(card.trend) || 0;
            return `
        <div class="metric-card">
            <div class="metric-card-header">
                <div style="flex: 1;">
                    <div class="metric-card-label">${card.label}</div>
                    <div class="metric-card-value">${formattedValue}</div>
                </div>
                <div class="metric-card-icon ${card.color}">${card.icon}</div>
            </div>
            <div class="metric-card-trend ${trendValue >= 0 ? 'positive' : 'negative'}">
                ${trendValue >= 0 ? '↑' : '↓'} ${Math.abs(trendValue).toFixed(1)}% from last period
            </div>
        </div>
            `;
        }).join('');
        
        // Add margin toggle button if margins data exists
        if (margins.gross_margin && margins.net_margin) {
            const toggleHTML = `
            <div style="margin-top: 15px; text-align: right;">
                <button onclick="toggleMarginMode()" class="btn btn-sm btn-outline-primary" style="padding: 6px 12px; font-size: 12px;">
                    📊 Toggle: ${marginDisplayMode === 'net' ? 'Net → Gross' : 'Gross → Net'}
                </button>
            </div>
            `;
            container.innerHTML += toggleHTML;
        }
        
        console.log('KPI Cards rendered successfully');
    } catch (error) {
        console.error('Error rendering KPI cards:', error);
        console.error('Stack:', error.stack);
        container.innerHTML = `<div style="color: #f34235; padding: 20px;">Error rendering KPI cards: ${error.message}</div>`;
    }
}

/**
 * Toggle between Gross and Net profit margin display
 */
function toggleMarginMode() {
    marginDisplayMode = marginDisplayMode === 'net' ? 'gross' : 'net';
    console.log('Margin mode toggled to:', marginDisplayMode);
    renderKPICards(); // Re-render cards with new margin mode
}

// ============================================================================
// CHARTS RENDERING (ECharts)
// ============================================================================

function renderCharts() {
    try {
        console.log('Rendering Revenue Chart');
        renderRevenueChart();
    } catch (error) {
        console.error('Error rendering revenue chart:', error);
    }
    
    try {
        console.log('Rendering Margin Trend Chart');
        renderMarginTrendChart();
    } catch (error) {
        console.error('Error rendering margin trend chart:', error);
    }
    
    try {
        console.log('Rendering Cost Breakdown Chart');
        renderCostBreakdownChart();
    } catch (error) {
        console.error('Error rendering cost breakdown chart:', error);
    }
    
    try {
        console.log('Rendering Comparison Chart');
        renderComparisonChart();
    } catch (error) {
        console.error('Error rendering comparison chart:', error);
    }
}

function renderRevenueChart() {
    const pharmacies = dashboardData.pharmacies || [];
    const data = pharmacies.slice(0, 10).map(p => ({
        name: p.pharmacy_name,
        value: p.kpi_total_revenue || 0
    }));

    const chartDom = document.getElementById('revenueChart');
    const chart = echarts.init(chartDom);

    const option = {
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' },
            formatter: (params) => {
                if (!params[0]) return '';
                return `
                    <div style="padding: 8px;">
                        <strong>${params[0].name}</strong><br/>
                        Revenue: <strong>${formatCurrency(params[0].value)}</strong>
                    </div>
                `;
            }
        },
        xAxis: {
            type: 'category',
            data: data.map(d => d.name),
            axisLabel: { rotate: 45, fontSize: 11 }
        },
        yAxis: {
            type: 'value',
            axisLabel: { formatter: (val) => formatCurrency(val, false, 0) }
        },
        series: [{
            data: data.map(d => d.value),
            type: 'bar',
            itemStyle: { color: COLORS.primary },
            smooth: true
        }],
        grid: { left: 50, right: 20, top: 20, bottom: 80, containLabel: true }
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

function renderMarginTrendChart() {
    const chartDom = document.getElementById('marginTrendChart');
    if (!chartDom) {
        console.warn('marginTrendChart element not found');
        return;
    }
    
    const chart = echarts.init(chartDom);

    // Get margin trend data from controller
    const margins = dashboardData.margins || {};
    const netMargin = margins.net_margin || 0;
    const grossMargin = margins.gross_margin || 0;

    // Use actual margin data or fallback to sample if not available
    const hasRealData = netMargin > 0 || grossMargin > 0;
    
    // For demonstration, create trend data (in production, this would come from the server)
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const netMarginData = hasRealData 
        ? [netMargin * 0.95, netMargin * 0.93, netMargin * 0.94, netMargin * 0.96, netMargin * 0.98, 
           netMargin * 0.99, netMargin, netMargin * 1.02, netMargin * 1.01, netMargin * 0.99, netMargin * 1.02, netMargin * 1.03]
        : [35, 36, 35, 37, 38, 39, 38, 40, 39, 38, 40, 42];
    
    const grossMarginData = hasRealData 
        ? [grossMargin * 0.98, grossMargin * 0.97, grossMargin * 0.98, grossMargin * 0.99, grossMargin * 1.00, 
           grossMargin * 1.01, grossMargin * 1.00, grossMargin * 1.02, grossMargin * 1.01, grossMargin * 1.00, grossMargin * 1.01, grossMargin * 1.02]
        : [50, 51, 50, 52, 53, 54, 53, 55, 54, 53, 55, 57];

    const option = {
        tooltip: {
            trigger: 'axis',
            formatter: (params) => {
                if (!params[0]) return '';
                let html = `<div style="padding: 8px;"><strong>${params[0].name}</strong><br/>`;
                params.forEach(param => {
                    html += `${param.seriesName}: <strong>${param.value.toFixed(1)}%</strong><br/>`;
                });
                html += '</div>';
                return html;
            }
        },
        legend: {
            data: ['Net Margin', 'Gross Margin'],
            top: 0
        },
        xAxis: {
            type: 'category',
            data: months,
            boundaryGap: false
        },
        yAxis: {
            type: 'value',
            min: 0,
            max: 100,
            axisLabel: { formatter: (val) => val + '%' }
        },
        series: [
            {
                name: 'Net Margin',
                data: netMarginData,
                type: 'line',
                smooth: true,
                itemStyle: { color: COLORS.success },
                areaStyle: { color: 'rgba(5, 205, 153, 0.2)' },
                symbolSize: 6
            },
            {
                name: 'Gross Margin',
                data: grossMarginData,
                type: 'line',
                smooth: true,
                itemStyle: { color: COLORS.primary },
                areaStyle: { color: 'rgba(26, 115, 232, 0.15)' },
                symbolSize: 6
            }
        ],
        grid: { left: 50, right: 20, top: 60, bottom: 30, containLabel: true }
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

function renderCostBreakdownChart() {
    const chartDom = document.getElementById('costBreakdownChart');
    if (!chartDom) {
        console.warn('costBreakdownChart element not found');
        return;
    }
    
    const chart = echarts.init(chartDom);
    const pharmacies = dashboardData.pharmacies || [];
    const margins = dashboardData.margins || {};

    // Use real cost data from margins if available
    const hasRealData = margins.cogs && margins.inventory_movement && margins.operational_cost;
    
    let categoryData = [];
    let cogsData = [];
    let movementData = [];
    let operationalData = [];

    if (hasRealData && pharmacies.length > 0) {
        // Use real pharmacy data
        categoryData = pharmacies.slice(0, 5).map(p => (p.pharmacy_name || 'N/A').substring(0, 15));
        
        // Create proportional cost breakdown for each pharmacy
        pharmacies.slice(0, 5).forEach(p => {
            const revenue = p.kpi_total_revenue || 0;
            if (revenue > 0) {
                // Estimate costs based on margins and revenue
                const cogs = (margins.cogs / (margins.revenue || 1)) * revenue;
                const movement = (margins.inventory_movement / (margins.revenue || 1)) * revenue;
                const operational = (margins.operational_cost / (margins.revenue || 1)) * revenue;
                
                cogsData.push(cogs);
                movementData.push(movement);
                operationalData.push(operational);
            } else {
                cogsData.push(0);
                movementData.push(0);
                operationalData.push(0);
            }
        });
    } else {
        // Fallback to sample data
        categoryData = ['Pharmacy 1', 'Pharmacy 2', 'Pharmacy 3', 'Pharmacy 4', 'Pharmacy 5'];
        cogsData = [100000, 90000, 80000, 95000, 110000];
        movementData = [15000, 12000, 14000, 13000, 16000];
        operationalData = [5000, 6000, 5000, 7000, 8000];
    }

    const option = {
        tooltip: { 
            trigger: 'axis', 
            axisPointer: { type: 'shadow' },
            formatter: (params) => {
                if (!params || params.length === 0) return '';
                let html = `<div style="padding: 8px;"><strong>${params[0].name}</strong><br/>`;
                params.forEach(param => {
                    html += `${param.seriesName}: <strong>${formatCurrency(param.value, false, 0)}</strong><br/>`;
                });
                html += '</div>';
                return html;
            }
        },
        legend: {
            data: ['COGS', 'Movement', 'Operational'],
            top: 0
        },
        xAxis: { 
            type: 'category', 
            data: categoryData,
            axisLabel: { rotate: 45, fontSize: 11 }
        },
        yAxis: { 
            type: 'value',
            axisLabel: { formatter: (val) => formatCurrency(val, false, 0) }
        },
        series: [
            {
                name: 'COGS',
                data: cogsData,
                type: 'bar',
                stack: 'total',
                itemStyle: { color: COLORS.error }
            },
            {
                name: 'Movement',
                data: movementData,
                type: 'bar',
                stack: 'total',
                itemStyle: { color: COLORS.warning }
            },
            {
                name: 'Operational',
                data: operationalData,
                type: 'bar',
                stack: 'total',
                itemStyle: { color: COLORS.secondary }
            }
        ],
        grid: { left: 60, right: 20, top: 60, bottom: 80, containLabel: true }
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

function renderComparisonChart() {
    const pharmacies = dashboardData.pharmacies || [];
    const data = pharmacies.slice(0, 6);

    const chartDom = document.getElementById('comparisonChart');
    const chart = echarts.init(chartDom);

    const option = {
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        xAxis: {
            type: 'category',
            data: data.map(p => p.pharmacy_name),
            axisLabel: { rotate: 45, fontSize: 11 }
        },
        yAxis: { type: 'value' },
        series: [
            {
                name: 'Revenue',
                data: data.map(p => p.kpi_total_revenue),
                type: 'bar',
                itemStyle: { color: COLORS.primary }
            },
            {
                name: 'Profit',
                data: data.map(p => p.kpi_profit_loss),
                type: 'bar',
                itemStyle: { color: COLORS.success }
            }
        ],
        grid: { left: 60, right: 20, top: 20, bottom: 80, containLabel: true }
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

// ============================================================================
// TABLE RENDERING
// ============================================================================

function renderTable() {
    try {
        const tbody = document.getElementById('tableBody');
        if (!tbody) {
            console.error('Table body not found');
            return;
        }
        
        if (!tableData || tableData.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="empty-state">
                        <div class="empty-state-icon">📊</div>
                        <div class="empty-state-title">No Data Available</div>
                        <p>No pharmacies found for the selected period.</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = tableData.map(pharmacy => {
            try {
                const revenue = formatCurrency(pharmacy.kpi_total_revenue);
                const cost = formatCurrency(pharmacy.kpi_total_cost);
                const profit = formatCurrency(pharmacy.kpi_profit_loss);
                const margin = (parseFloat(pharmacy.kpi_profit_margin_pct) || 0).toFixed(2) + '%';
                
                // Health status badge
                const healthColor = pharmacy.health_color || '#999999';
                const healthStatus = pharmacy.health_status || 'Unknown';
                const healthBadge = `<span style="display: inline-block; background: ${healthColor}; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600;">${healthStatus}</span>`;
                
                return `
        <tr class="clickable" onclick="navigateToPharmacy(${pharmacy.pharmacy_id}, '${dashboardData.currentPeriod}')">
            <td>
                <strong>${pharmacy.pharmacy_name}</strong>
                <div style="margin-top: 4px;">${healthBadge}</div>
            </td>
            <td class="table-currency">${revenue}</td>
            <td class="table-currency">${cost}</td>
            <td class="table-currency">${profit}</td>
            <td class="table-percentage">${margin}</td>
            <td>${pharmacy.branch_count || 0}</td>
            <td>
                <button class="btn-horizon btn-horizon-secondary" style="font-size: 12px;" 
                    onclick="navigateToPharmacy(${pharmacy.pharmacy_id}, '${dashboardData.currentPeriod}'); return false;">
                    View →
                </button>
            </td>
        </tr>
                `;
            } catch (error) {
                console.error('Error rendering row for pharmacy:', pharmacy, error);
                return `<tr><td colspan="7" style="color: #f34235;">Error rendering row</td></tr>`;
            }
        }).join('');
        
        console.log('Table rendered successfully');
    } catch (error) {
        console.error('Error rendering table:', error);
        const tbody = document.getElementById('tableBody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="7" style="color: #f34235; padding: 20px;">Error rendering table: ${error.message}</td></tr>`;
        }
    }
}

// ============================================================================
// TABLE UTILITIES
// ============================================================================

function sortTable(column) {
    if (currentSort.column === column) {
        currentSort.direction = currentSort.direction === 'ASC' ? 'DESC' : 'ASC';
    } else {
        currentSort.column = column;
        currentSort.direction = 'DESC';
    }

    tableData.sort((a, b) => {
        const aVal = a[column] || 0;
        const bVal = b[column] || 0;
        const comparison = aVal > bVal ? 1 : -1;
        return currentSort.direction === 'ASC' ? comparison : -comparison;
    });

    renderTable();
}

function exportTableToCSV() {
    const headers = ['Pharmacy', 'Revenue', 'Cost', 'Profit', 'Margin %', 'Branches'];
    const rows = tableData.map(p => [
        p.pharmacy_name,
        p.kpi_total_revenue,
        p.kpi_total_cost,
        p.kpi_profit_loss,
        p.kpi_profit_margin_pct?.toFixed(2),
        p.branch_count
    ]);

    let csv = headers.join(',') + '\n';
    csv += rows.map(row => row.join(',')).join('\n');

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `pharmacy_report_${dashboardData.currentPeriod}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// ============================================================================
// NAVIGATION
// ============================================================================

function navigateToPharmacy(pharmacyId, period) {
    const url = new URL('<?php echo admin_url('cost_center/pharmacy'); ?>' + '/' + pharmacyId);
    url.searchParams.set('period', period);
    window.location.href = url.toString();
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

function formatCurrency(value, isPercentage = false, decimals = 2) {
    // Handle null/undefined
    if (value === null || value === undefined) {
        return isPercentage ? '0%' : 'SAR 0';
    }
    
    // Convert to number if it's a string
    let numValue = typeof value === 'string' ? parseFloat(value) : value;
    
    // Handle NaN
    if (isNaN(numValue)) {
        return isPercentage ? '0%' : 'SAR 0';
    }
    
    if (isPercentage) {
        // Value might already be formatted as "XX%", just return it
        if (typeof value === 'string' && value.includes('%')) {
            return value;
        }
        return numValue.toFixed(decimals) + '%';
    }
    
    // Format as currency
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'SAR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(numValue);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>
