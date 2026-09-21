<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Cost Center Pharmacy Detail - Modern Horizon UI Design
 * 
 * Features:
 * - Breadcrumb navigation back to dashboard
 * - Pharmacy-level KPI cards
 * - Branch performance charts
 * - Branch data table with drill-down
 * - Period selector
 * 
 * Date: 2025-10-25
 */
?>

<style>
/* Reuse horizon dashboard styles */
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
    font-family: inherit;
    background: white;
    cursor: pointer;
}

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
}

.table-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--horizon-dark-text);
}

.table-wrapper {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    padding: 12px 16px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    color: var(--horizon-dark-text);
    border-bottom: 1px solid var(--horizon-border);
    background: var(--horizon-bg-light);
    text-transform: uppercase;
}

.data-table td {
    padding: 12px 16px;
    border-bottom: 1px solid var(--horizon-border);
    font-size: 14px;
}

.data-table tbody tr:hover {
    background: var(--horizon-bg-light);
}

.table-currency {
    font-family: 'Courier New', monospace;
    text-align: right;
}

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
}

.btn-horizon-secondary {
    background: var(--horizon-bg-light);
    color: var(--horizon-dark-text);
    border: 1px solid var(--horizon-border);
}

.btn-horizon-secondary:hover {
    background: #e0e0e0;
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
    cursor: pointer;
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

    .table-wrapper {
        font-size: 12px;
    }

    .data-table th,
    .data-table td {
        padding: 8px 10px;
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
</style>

<!-- Pharmacy Detail Dashboard -->
<div class="horizon-dashboard">
    <!-- Breadcrumb Navigation -->
    <div style="padding: 16px 24px; background: #f9f9f9; border-bottom: 1px solid var(--horizon-border);">
        <div class="breadcrumb-nav">
            <a href="<?php echo admin_url('cost_center/dashboard'); ?>">Dashboard</a>
            <span>›</span>
            <span><?php echo $pharmacy['pharmacy_name']; ?></span>
        </div>
    </div>

    <!-- Header with Back Button -->
    <div class="horizon-header">
        <div class="horizon-header-title">
            <h1>
                <i class="fa fa-building"></i> <?php echo $pharmacy['pharmacy_name']; ?>
            </h1>
            <p>Period: <?php echo $period; ?> • <?php echo count($branches); ?> Branches</p>
        </div>
        <div class="horizon-header-controls">
            <a href="<?php echo admin_url('cost_center/dashboard'); ?>" class="btn-back">
                <i class="fa fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Control Bar -->
    <div class="horizon-control-bar">
        <div class="horizon-controls-left">
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
    </div>

    <!-- Empty Data Alert -->
    <?php if (isset($pharmacy['is_empty_data']) && $pharmacy['is_empty_data']): ?>
    <div style="background: #e3f2fd; border: 1px solid #90caf9; border-radius: 8px; padding: 16px 20px; margin-bottom: 20px; margin-left: 24px; margin-right: 24px; display: flex; align-items: flex-start; gap: 12px;">
        <div style="color: #1976d2; font-size: 20px; flex-shrink: 0;">ℹ️</div>
        <div>
            <strong style="color: #1565c0;">No transaction data available</strong>
            <p style="margin: 4px 0 0 0; color: #1565c0; font-size: 14px;">
                No sales or cost transactions were recorded for <?php echo htmlspecialchars($pharmacy['pharmacy_name']); ?> 
                in <?php echo htmlspecialchars($period); ?>. Showing pharmacy information with zero values.
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pharmacy KPI Cards -->
    <div class="kpi-cards-grid">
        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Total Revenue</div>
                    <div class="metric-card-value"><?php echo formatCurrency($pharmacy['kpi_total_revenue']); ?></div>
                </div>
                <div class="metric-card-icon blue">💵</div>
            </div>
            <div class="metric-card-trend positive">
                ↑ Pharmacy Summary
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Total Cost</div>
                    <div class="metric-card-value"><?php echo formatCurrency($pharmacy['kpi_total_cost']); ?></div>
                </div>
                <div class="metric-card-icon red">📉</div>
            </div>
            <div class="metric-card-trend">
                Cost Analysis
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Total Profit</div>
                    <div class="metric-card-value"><?php echo formatCurrency($pharmacy['kpi_profit_loss']); ?></div>
                </div>
                <div class="metric-card-icon green">📈</div>
            </div>
            <div class="metric-card-trend positive">
                ✓ Profitability
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div>
                    <div class="metric-card-label">Profit Margin %</div>
                    <div class="metric-card-value"><?php echo number_format($pharmacy['kpi_profit_margin_pct'] ?? 0, 2); ?>%</div>
                </div>
                <div class="metric-card-icon purple">📊</div>
            </div>
            <div class="metric-card-trend">
                Margin Performance
            </div>
        </div>
    </div>

    <!-- Branches Performance Chart -->
    <div class="charts-section">
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Branch Revenue Distribution</h3>
                <p class="chart-subtitle">Revenue by branch</p>
            </div>
            <div id="branchRevenueChart" class="chart-content"></div>
        </div>

        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Branch Profit Comparison</h3>
                <p class="chart-subtitle">Profit performance by branch</p>
            </div>
            <div id="branchProfitChart" class="chart-content"></div>
        </div>
    </div>

    <!-- Branches Table -->
    <div class="table-section">
        <div class="table-header-bar">
            <h3 class="table-title">Branches Performance</h3>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Branch Code</th>
                        <th>Branch Name</th>
                        <th>Revenue</th>
                        <th>Cost</th>
                        <th>Profit</th>
                        <th>Margin %</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($branches)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <i class="fa fa-info-circle"></i> No branches data available
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($branches as $branch): ?>
                        <tr>
                            <td><strong><?php echo $branch['branch_code']; ?></strong></td>
                            <td><?php echo $branch['branch_name']; ?></td>
                            <td class="table-currency"><?php echo formatCurrency($branch['kpi_total_revenue']); ?></td>
                            <td class="table-currency"><?php echo formatCurrency($branch['kpi_total_cost']); ?></td>
                            <td class="table-currency"><?php echo formatCurrency($branch['kpi_profit_loss']); ?></td>
                            <td class="table-currency"><?php echo number_format($branch['kpi_profit_margin_pct'] ?? 0, 2); ?>%</td>
                            <td>
                                <a href="<?php echo admin_url('cost_center/branch/' . $branch['branch_id'] . '?period=' . $period); ?>" 
                                   class="btn-horizon btn-horizon-secondary" style="font-size: 12px;">
                                    View →
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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

const branchesData = <?php echo json_encode($branches ?? []); ?>;
const pharmacyData = <?php echo json_encode($pharmacy); ?>;
const period = '<?php echo $period; ?>';

document.addEventListener('DOMContentLoaded', function() {
    renderBranchCharts();
});

function renderBranchCharts() {
    // Revenue Chart
    const revenueDom = document.getElementById('branchRevenueChart');
    const revenueChart = echarts.init(revenueDom);

    const revenueOption = {
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        xAxis: {
            type: 'category',
            data: branchesData.map(b => b.branch_code),
            axisLabel: { rotate: 45, fontSize: 11 }
        },
        yAxis: { type: 'value' },
        series: [{
            data: branchesData.map(b => b.kpi_total_revenue),
            type: 'bar',
            itemStyle: { color: COLORS.primary }
        }],
        grid: { left: 50, right: 20, top: 20, bottom: 80, containLabel: true }
    };

    revenueChart.setOption(revenueOption);
    window.addEventListener('resize', () => revenueChart.resize());

    // Profit Chart
    const profitDom = document.getElementById('branchProfitChart');
    const profitChart = echarts.init(profitDom);

    const profitOption = {
        tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
        xAxis: {
            type: 'category',
            data: branchesData.map(b => b.branch_code),
            axisLabel: { rotate: 45, fontSize: 11 }
        },
        yAxis: { type: 'value' },
        series: [{
            data: branchesData.map(b => b.kpi_profit_loss),
            type: 'bar',
            itemStyle: { color: COLORS.success }
        }],
        grid: { left: 50, right: 20, top: 20, bottom: 80, containLabel: true }
    };

    profitChart.setOption(profitOption);
    window.addEventListener('resize', () => profitChart.resize());
}

function handlePeriodChange(period) {
    const url = new URL(window.location);
    url.searchParams.set('period', period);
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
/**
 * Helper function to format currency
 */
function formatCurrency($value) {
    $num = floatval($value) ?? 0;
    return 'SAR ' . number_format($num, 0);
}
?>
