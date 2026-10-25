<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
/**
 * Cost Center Branch Detail View - Blue Theme
 * 
 * Displays:
 * - Branch metrics (revenue, cost, profit, margin)
 * - Cost breakdown pie chart
 * - 12-month trend chart
 * - Period selector and navigation
 * 
 * Data variables:
 * - $branch: Branch metrics object
 * - $breakdown: Cost breakdown by category
 * - $timeseries: 12-month historical data
 * - $periods: Available periods for selection
 * - $period: Selected period (YYYY-MM)
 */
?>

<div class="box">
    <!-- Box Header -->
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-map-marker"></i> <?= htmlspecialchars($branch['branch_name'] ?? ''); ?></h2>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li>
                    <a href="<?php echo site_url('admin/cost_center/pharmacy/' . ($branch['pharmacy_id'] ?? '')); ?>" title="Back to Pharmacy">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </li>
                <li>
                    <div class="form-group d-inline-block" style="width: 200px; margin: 0;">
                        <select id="periodSelector" class="form-control form-control-sm" onchange="changePeriod(this)">
                            <option value="">-- Select Period --</option>
                            <?php foreach ($periods as $p): ?>
                                <option value="<?php echo $p; ?>" <?php echo ($p === $period) ? 'selected' : ''; ?>>
                                    <?php echo date('M Y', strtotime($p . '-01')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </li>
                <li>
                    <a href="javascript:location.reload()" title="Refresh">
                        <i class="fa fa-refresh"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="box-content">
        <!-- Branch Info -->
        <div class="row mb-3">
            <div class="col-lg-12">
                <p class="text-muted">
                    <i class="fa fa-code"></i> <strong>Code:</strong> <?php echo htmlspecialchars($branch['branch_code'] ?? ''); ?> 
                    <span class="mx-3">|</span>
                    <i class="fa fa-hospital"></i> <strong>Pharmacy:</strong> <?php echo htmlspecialchars($branch['pharmacy_name'] ?? ''); ?>
                </p>
            </div>
        </div>

        <!-- Branch Metrics Cards -->
        <div class="row">
            <!-- Revenue Card -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="info-box blue-bg">
                    <i class="fa fa-money"></i>
                    <div class="info-box-content">
                        <span class="info-box-text">Revenue</span>
                        <span class="info-box-number"><?php echo number_format($branch['kpi_total_revenue'] ?? 0, 0); ?> <small>SAR</small></span>
                    </div>
                </div>
            </div>

            <!-- Cost Card -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="info-box orange-bg">
                    <i class="fa fa-shopping-cart"></i>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Cost</span>
                        <span class="info-box-number"><?php echo number_format($branch['kpi_total_cost'] ?? 0, 0); ?> <small>SAR</small></span>
                    </div>
                </div>
            </div>

            <!-- Profit Card -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="info-box green-bg">
                    <i class="fa fa-check-circle"></i>
                    <div class="info-box-content">
                        <span class="info-box-text">Profit</span>
                        <span class="info-box-number"><?php echo number_format($branch['kpi_profit_loss'] ?? 0, 0); ?> <small>SAR</small></span>
                    </div>
                </div>
            </div>

            <!-- Margin Card -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="info-box red-bg">
                    <i class="fa fa-fire"></i>
                    <div class="info-box-content">
                        <span class="info-box-text">Profit Margin %</span>
                        <span class="info-box-number"><?php echo number_format($branch['kpi_profit_margin_pct'] ?? 0, 1); ?>%</span>
                    </div>
                </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Cost Breakdown Pie Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0 font-weight-bold">Cost Breakdown</h6>
                </div>
                <div class="card-body">
                    <canvas id="costChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Trend Chart -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0 font-weight-bold">12-Month Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Cost Breakdown Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0 font-weight-bold">Cost Categories Detail</h6>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead class="bg-light">
                            <tr>
                                <th>Category</th>
                                <th class="text-right">Amount (SAR)</th>
                                <th class="text-right">% of Total Cost</th>
                                <th class="text-right">% of Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $total_cost = $branch['kpi_total_cost'] ?? 0;
                                $total_revenue = $branch['kpi_total_revenue'] ?? 0;
                                
                                $categories = [
                                    [
                                        'name' => 'COGS (Cost of Goods Sold)',
                                        'amount' => $breakdown['cogs'] ?? 0,
                                        'icon' => 'fa fa-cube'
                                    ],
                                    [
                                        'name' => 'Inventory Movement',
                                        'amount' => $breakdown['inventory_movement'] ?? 0,
                                        'icon' => 'fa fa-arrow-right'
                                    ],
                                    [
                                        'name' => 'Operational Costs',
                                        'amount' => $breakdown['operational'] ?? 0,
                                        'icon' => 'fa fa-cog'
                                    ]
                                ];
                            ?>
                            <?php foreach ($categories as $category): ?>
                                <?php 
                                    $pct_of_cost = ($total_cost > 0) ? ($category['amount'] / $total_cost * 100) : 0;
                                    $pct_of_revenue = ($total_revenue > 0) ? ($category['amount'] / $total_revenue * 100) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <i class="<?php echo $category['icon']; ?> mr-2"></i>
                                        <strong><?php echo $category['name']; ?></strong>
                                    </td>
                                    <td class="text-right">
                                        <strong><?php echo number_format($category['amount'], 2); ?></strong>
                                    </td>
                                    <td class="text-right">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo $pct_of_cost; ?>%" 
                                                 aria-valuenow="<?php echo $pct_of_cost; ?>" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                <?php echo number_format($pct_of_cost, 1); ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <?php echo number_format($pct_of_revenue, 1); ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="border-top border-secondary font-weight-bold">
                                <td><i class="fa fa-plus-circle mr-2"></i>Total Cost</td>
                                <td class="text-right"><?php echo number_format($total_cost, 2); ?></td>
                                <td class="text-right">100%</td>
                                <td class="text-right"><?php echo number_format(($total_revenue > 0) ? ($total_cost / $total_revenue * 100) : 0, 1); ?>%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    const branchId = <?php echo $branch['warehouse_id'] ?? 0; ?>;
    const period = '<?php echo $period; ?>';
    const breakdown = <?php echo json_encode($breakdown); ?>;
    const timeseries = <?php echo json_encode($timeseries ?? []); ?>;

    // Period change handler
    function changePeriod(selectedPeriod) {
        if (selectedPeriod) {
            window.location.href = '<?php echo site_url('admin/cost_center/branch'); ?>/' + branchId + '?period=' + selectedPeriod;
        }
    }

    // Download chart
    function downloadChart(chartId) {
        const element = document.getElementById(chartId);
        if (element && element.toDataURL) {
            const link = document.createElement('a');
            link.href = element.toDataURL('image/png');
            link.download = chartId + '_' + new Date().getTime() + '.png';
            link.click();
        }
    }

    // Initialize charts
    document.addEventListener('DOMContentLoaded', function() {
        initializeCostChart();
        initializeTrendChart();
    });

    // Cost Breakdown Pie Chart
    function initializeCostChart() {
        const ctx = document.getElementById('costChart');
        if (!ctx) return;

        const costData = [
            breakdown.cogs || 0,
            breakdown.inventory_movement || 0,
            breakdown.operational || 0
        ];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['COGS', 'Inventory Movement', 'Operational'],
                datasets: [{
                    data: costData,
                    backgroundColor: [
                        '#FF6B6B',
                        '#FFD93D',
                        '#51CF66'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Trend Chart (12 months)
    function initializeTrendChart() {
        const ctx = document.getElementById('trendChart');
        if (!ctx) return;

        if (!timeseries || timeseries.length === 0) {
            // Show empty state
            ctx.parentElement.innerHTML = '<p class="text-center text-muted py-5">No historical data available</p>';
            return;
        }

        const labels = timeseries.map(t => t.period || '');
        const revenueData = timeseries.map(t => t.revenue || 0);
        const costData = timeseries.map(t => t.cost || 0);
        const profitData = timeseries.map(t => t.profit || 0);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: revenueData,
                        borderColor: '#1E90FF',
                        backgroundColor: 'rgba(30, 144, 255, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#1E90FF',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    },
                    {
                        label: 'Cost',
                        data: costData,
                        borderColor: '#FF6B6B',
                        backgroundColor: 'rgba(255, 107, 107, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#FF6B6B',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    },
                    {
                        label: 'Profit',
                        data: profitData,
                        borderColor: '#51CF66',
                        backgroundColor: 'rgba(81, 207, 102, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#51CF66',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' SAR';
                            }
                        }
                    }
                }
            }
        });
    }
</script>

<style>
    .border-left-primary {
        border-left: 4px solid #1E90FF !important;
    }
    .border-left-danger {
        border-left: 4px solid #FF6B6B !important;
    }
    .border-left-success {
        border-left: 4px solid #51CF66 !important;
    }
    .border-left-warning {
        border-left: 4px solid #FFD93D !important;
    }
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .progress {
        background-color: #e9ecef;
    }
    .progress-bar {
        background-color: #1E90FF;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.75rem;
        font-weight: bold;
    }
</style>
