<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Cost Center Branch Detail - Modern Horizon UI Design
 * 
 * Features:
 * - Breadcrumb navigation
 * - Branch-level KPI cards
 * - 12-month trend charts
 * - Cost breakdown analysis
 * - Key metrics display
 * 
 * Date: 2025-10-25
 */
?>

<style>
:root {
    --horizon-primary: #1a73e8;
    --horizon-success: #05cd99;
    --horizon-error: #f34235;
    --horizon-warning: #ff9a56;
    --horizon-secondary: #6c5ce7;
    --horizon-dark-text: #111111;
    --horizon-light-text: #7a8694;
    --horizon-bg-light: #f5f5f5;
    --horizon-border: #e0e0e0;
    --horizon-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

* {
    box-sizing: border-box;
}

.horizon-dashboard {
    background: #ffffff;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

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
}

.horizon-control-bar {
    background: var(--horizon-bg-light);
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
    display: flex;
    gap: 16px;
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

.horizon-select-group select {
    padding: 8px 12px;
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    font-size: 14px;
    background: white;
}

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
    transition: all 0.3s ease;
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
}

.metric-card-icon.green {
    background: rgba(5, 205, 153, 0.1);
}

.metric-card-icon.red {
    background: rgba(243, 66, 53, 0.1);
}

.metric-card-icon.purple {
    background: rgba(108, 92, 231, 0.1);
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
}

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

.chart-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--horizon-dark-text);
    margin: 0 0 4px 0;
}

.chart-subtitle {
    font-size: 12px;
    color: var(--horizon-light-text);
    margin: 0 0 16px 0;
}

.chart-content {
    min-height: 300px;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
    background: white;
    border: 1px solid var(--horizon-border);
    border-radius: 12px;
    padding: 24px;
}

.metric-box {
    border-left: 4px solid var(--horizon-primary);
    padding-left: 16px;
}

.metric-box.success {
    border-left-color: var(--horizon-success);
}

.metric-box.error {
    border-left-color: var(--horizon-error);
}

.metric-box-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--horizon-light-text);
    text-transform: uppercase;
    margin-bottom: 8px;
}

.metric-box-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--horizon-dark-text);
}

.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
}

.breadcrumb-nav a,
.breadcrumb-nav span {
    font-size: 14px;
    color: var(--horizon-primary);
    text-decoration: none;
}

.breadcrumb-nav span {
    color: var(--horizon-light-text);
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 6px;
    background: var(--horizon-primary);
    color: white;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}

.btn-back:hover {
    background: #1557b0;
}

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

    .metrics-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Branch Detail Dashboard -->
<div class="horizon-dashboard">
    <!-- Breadcrumb Navigation -->
    <div style="padding: 16px 24px; background: #f9f9f9; border-bottom: 1px solid var(--horizon-border);">
        <div class="breadcrumb-nav">
            <a href="<?php echo admin_url('cost_center/dashboard'); ?>">Dashboard</a>
            <span>›</span>
            <a href="<?php echo admin_url('cost_center/pharmacy/' . $branch['pharmacy_id'] . '?period=' . $period); ?>">
                <?php echo $branch['pharmacy_name']; ?>
            </a>
            <span>›</span>
            <span><?php echo $branch['branch_name']; ?></span>
        </div>
    </div>

    <!-- Header with Back Button -->
    <div class="horizon-header">
        <div class="horizon-header-title">
            <h1>
                <i class="fa fa-sitemap"></i> <?php echo $branch['branch_name']; ?>
            </h1>
            <p>Period: <?php echo $period; ?> • Code: <?php echo $branch['branch_code']; ?></p>
        </div>
        <div>
            <a href="<?php echo admin_url('cost_center/pharmacy/' . $branch['pharmacy_id'] . '?period=' . $period); ?>" class="btn-back">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Control Bar -->
    <div class="horizon-control-bar">
        <div class="horizon-select-group">
            <label>Period</label>
            <select onchange="handlePeriodChange(this.value)">
                <?php foreach($periods as $p): ?>
                    <option value="<?php echo $p['period']; ?>" <?php echo $p['period'] === $period ? 'selected' : ''; ?>>
                        <?php echo $p['period']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Branch KPI Cards -->
    <div class="kpi-cards-grid">
        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Total Revenue</div>
                    <div class="metric-card-value"><?php echo formatCurrency($branch['kpi_total_revenue']); ?></div>
                </div>
                <div class="metric-card-icon blue">💵</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Total Cost</div>
                    <div class="metric-card-value"><?php echo formatCurrency($branch['kpi_total_cost']); ?></div>
                </div>
                <div class="metric-card-icon red">📉</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Total Profit</div>
                    <div class="metric-card-value"><?php echo formatCurrency($branch['kpi_profit_loss']); ?></div>
                </div>
                <div class="metric-card-icon green">📈</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Profit Margin %</div>
                    <div class="metric-card-value"><?php echo number_format($branch['kpi_profit_margin_pct'] ?? 0, 2); ?>%</div>
                </div>
                <div class="metric-card-icon purple">📊</div>
            </div>
        </div>
    </div>

    <!-- 12-Month Trend Chart -->
    <div class="charts-section">
        <div class="chart-container">
            <h3 class="chart-title">12-Month Revenue Trend</h3>
            <p class="chart-subtitle">Historical revenue analysis</p>
            <div id="revenueTrendChart" class="chart-content"></div>
        </div>

        <div class="chart-container">
            <h3 class="chart-title">12-Month Profit Trend</h3>
            <p class="chart-subtitle">Historical profit analysis</p>
            <div id="profitTrendChart" class="chart-content"></div>
        </div>
    </div>

    <!-- Cost Breakdown Section -->
    <div class="charts-section">
        <div class="chart-container">
            <h3 class="chart-title">Cost Breakdown</h3>
            <p class="chart-subtitle">Cost category distribution</p>
            <div id="costBreakdownChart" class="chart-content"></div>
        </div>

        <div class="chart-container">
            <h3 class="chart-title">Margin & Performance</h3>
            <p class="chart-subtitle">Key performance indicators</p>
            <div id="marginChart" class="chart-content"></div>
        </div>
    </div>

    <!-- Key Metrics Display -->
    <div class="metrics-grid">
        <div class="metric-box">
            <div class="metric-box-label">Avg Daily Revenue</div>
            <div class="metric-box-value"><?php echo formatCurrency($branch['kpi_total_revenue'] / 30); ?></div>
        </div>

        <div class="metric-box">
            <div class="metric-box-label">Avg Daily Cost</div>
            <div class="metric-box-value"><?php echo formatCurrency($branch['kpi_total_cost'] / 30); ?></div>
        </div>

        <div class="metric-box success">
            <div class="metric-box-label">Cost Ratio %</div>
            <div class="metric-box-value"><?php echo number_format($branch['kpi_cost_ratio_pct'] ?? 0, 2); ?>%</div>
        </div>

        <div class="metric-box">
            <div class="metric-box-label">Transactions Count</div>
            <div class="metric-box-value"><?php echo number_format($branch['transaction_count'] ?? 0); ?></div>
        </div>

        <div class="metric-box">
            <div class="metric-box-label">Avg Transaction Value</div>
            <div class="metric-box-value"><?php 
                $txn_count = $branch['transaction_count'] ?? 1;
                $avg_txn = ($txn_count > 0) ? $branch['kpi_total_revenue'] / $txn_count : 0;
                echo formatCurrency($avg_txn);
            ?></div>
        </div>

        <div class="metric-box">
            <div class="metric-box-label">Last Updated</div>
            <div class="metric-box-value" style="font-size: 14px;">
                <?php echo date('M d, Y', strtotime($branch['last_updated'])); ?>
            </div>
        </div>
    </div>
</div>

<!-- Include ECharts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>

<script>
const COLORS = {
    primary: '#1a73e8',
    success: '#05cd99',
    error: '#f34235',
    warning: '#ff9a56',
    secondary: '#6c5ce7',
};

const timeseriesData = <?php echo json_encode($timeseries ?? []); ?>;
const breakdownData = <?php echo json_encode($breakdown ?? []); ?>;
const period = '<?php echo $period; ?>';

document.addEventListener('DOMContentLoaded', function() {
    renderAllCharts();
});

function renderAllCharts() {
    renderRevenueTrendChart();
    renderProfitTrendChart();
    renderCostBreakdownChart();
    renderMarginChart();
}

function renderRevenueTrendChart() {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const data = timeseriesData.map(d => d.revenue || 0).slice(0, 12);

    const chartDom = document.getElementById('revenueTrendChart');
    const chart = echarts.init(chartDom);

    const option = {
        tooltip: { trigger: 'axis', axisPointer: { type: 'cross' } },
        xAxis: { type: 'category', data: months },
        yAxis: { type: 'value' },
        series: [{
            data: data,
            type: 'line',
            smooth: true,
            itemStyle: { color: COLORS.primary },
            areaStyle: { color: 'rgba(26, 115, 232, 0.2)' },
            symbolSize: 6
        }],
        grid: { left: 60, right: 20, top: 20, bottom: 30, containLabel: true }
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

function renderProfitTrendChart() {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const data = timeseriesData.map(d => d.profit || 0).slice(0, 12);

    const chartDom = document.getElementById('profitTrendChart');
    const chart = echarts.init(chartDom);

    const option = {
        tooltip: { trigger: 'axis', axisPointer: { type: 'cross' } },
        xAxis: { type: 'category', data: months },
        yAxis: { type: 'value' },
        series: [{
            data: data,
            type: 'line',
            smooth: true,
            itemStyle: { color: COLORS.success },
            areaStyle: { color: 'rgba(5, 205, 153, 0.2)' },
            symbolSize: 6
        }],
        grid: { left: 60, right: 20, top: 20, bottom: 30, containLabel: true }
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

function renderCostBreakdownChart() {
    const chartDom = document.getElementById('costBreakdownChart');
    const chart = echarts.init(chartDom);

    const option = {
        tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
        series: [{
            data: breakdownData.length > 0 ? breakdownData : [
                { value: 65, name: 'COGS' },
                { value: 20, name: 'Movement' },
                { value: 15, name: 'Operational' }
            ],
            type: 'pie',
            radius: [0, '70%'],
            label: { position: 'inside', formatter: '{d}%', fontSize: 12 }
        }],
        color: [COLORS.error, COLORS.warning, COLORS.secondary]
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

function renderMarginChart() {
    const chartDom = document.getElementById('marginChart');
    const chart = echarts.init(chartDom);

    const marginTrend = timeseriesData.map(d => d.margin || 0).slice(0, 12);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    const option = {
        tooltip: { trigger: 'axis' },
        xAxis: { type: 'category', data: months },
        yAxis: { type: 'value', min: 0, max: 100 },
        series: [{
            data: marginTrend,
            type: 'bar',
            itemStyle: { color: COLORS.secondary }
        }],
        grid: { left: 60, right: 20, top: 20, bottom: 30, containLabel: true }
    };

    chart.setOption(option);
    window.addEventListener('resize', () => chart.resize());
}

function handlePeriodChange(newPeriod) {
    const url = new URL(window.location);
    url.searchParams.set('period', newPeriod);
    window.location.href = url.toString();
}

function formatCurrency(value) {
    const num = parseFloat(value) || 0;
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'SAR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(num);
}
</script>

<?php
function formatCurrency($value) {
    $num = floatval($value) ?? 0;
    return 'SAR ' . number_format($num, 0);
}
?>
