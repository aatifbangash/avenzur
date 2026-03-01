<?php
/**
 * Cost Center Dashboard View
 * 
 * Displays:
 * - KPI cards (Total Revenue, Total Cost, Profit, Profit Margin %)
 * - Pharmacy list table with sorting
 * - Period selector
 * - Trend chart
 * 
 * Data variables:
 * - $summary: Company-level KPIs
 * - $pharmacies: List of pharmacies with KPIs
 * - $periods: Available periods for selection
 * - $period: Selected period (YYYY-MM)
 */
?>
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 text-dark">
                <i class="fas fa-chart-pie"></i> Cost Center Dashboard
            </h1>
            <p class="text-muted">Monitor pharmacy budget allocation and spending</p>
        </div>
        <div class="col-md-6 text-right">
            <!-- Period Selector -->
            <div class="form-group d-inline-block mr-3" style="width: 200px;">
                <select id="periodSelector" class="form-control form-control-sm" onchange="changePeriod(this.value)">
                    <option value="">-- Select Period --</option>
                    <?php foreach ($periods as $p): ?>
                        <option value="<?php echo $p; ?>" <?php echo ($p === $period) ? 'selected' : ''; ?>>
                            <?php echo date('M Y', strtotime($p . '-01')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>

    <!-- KPI Cards Row -->
    <div class="row mb-4">
        <!-- Total Revenue Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">Total Revenue</div>
                    <h3 class="h4 font-weight-bold mb-0">
                        <?php echo number_format($summary['total_revenue'] ?? 0, 2); ?>
                        <span class="text-muted" style="font-size: 0.7em;">SAR</span>
                    </h3>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            <?php echo number_format($summary['pharmacy_count'] ?? 0); ?> pharmacies
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Cost Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="text-danger font-weight-bold text-uppercase mb-1">Total Cost</div>
                    <h3 class="h4 font-weight-bold mb-0">
                        <?php echo number_format($summary['total_cost'] ?? 0, 2); ?>
                        <span class="text-muted" style="font-size: 0.7em;">SAR</span>
                    </h3>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            <?php $pct = ($summary['total_revenue'] > 0) ? ($summary['total_cost'] / $summary['total_revenue'] * 100) : 0; ?>
                            <?php echo number_format($pct, 1); ?>% of revenue
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Profit Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-uppercase mb-1">Total Profit</div>
                    <h3 class="h4 font-weight-bold mb-0">
                        <?php echo number_format($summary['total_profit'] ?? 0, 2); ?>
                        <span class="text-muted" style="font-size: 0.7em;">SAR</span>
                    </h3>
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-arrow-up"></i> 
                            <?php echo number_format($summary['total_profit'] - ($summary['total_revenue'] - $summary['total_cost']), 2); ?> vs last month
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profit Margin Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-uppercase mb-1">Profit Margin</div>
                    <h3 class="h4 font-weight-bold mb-0">
                        <?php 
                            $margin = ($summary['total_revenue'] > 0) 
                                ? ($summary['total_profit'] / $summary['total_revenue'] * 100) 
                                : 0; 
                        ?>
                        <?php echo number_format($margin, 1); ?>%
                    </h3>
                    <div class="mt-2">
                        <?php 
                            $status = ($margin >= 35) ? 'text-success' : (($margin >= 25) ? 'text-warning' : 'text-danger');
                        ?>
                        <small class="<?php echo $status; ?>">
                            <?php echo ($margin >= 35) ? '✓ Healthy' : (($margin >= 25) ? '⚠ Monitor' : '✗ Low'); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trend Chart Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between">
                    <h6 class="mb-0 font-weight-bold">Revenue vs Cost Trend (<?php echo date('M Y', strtotime($period . '-01')); ?>)</h6>
                    <button class="btn btn-xs btn-outline-secondary" onclick="downloadChart('trendChart')">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Pharmacy List Row -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between">
                    <h6 class="mb-0 font-weight-bold">Pharmacy Performance</h6>
                    <div>
                        <button class="btn btn-xs btn-outline-secondary mr-1" onclick="sortTable('revenue')">
                            <i class="fas fa-sort-amount-down"></i> By Revenue
                        </button>
                        <button class="btn btn-xs btn-outline-secondary" onclick="sortTable('profit')">
                            <i class="fas fa-sort-amount-down"></i> By Profit
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-hover" id="pharmacyTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Pharmacy Name</th>
                                <th class="text-right">Revenue</th>
                                <th class="text-right">Cost</th>
                                <th class="text-right">Profit</th>
                                <th class="text-right">Margin %</th>
                                <th class="text-right">Branches</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pharmacies)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle"></i> No data available for selected period
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pharmacies as $pharmacy): ?>
                                    <tr class="cursor-pointer" onclick="goToPharmacy(<?php echo $pharmacy['warehouse_id']; ?>)">
                                        <td>
                                            <strong><?php echo htmlspecialchars($pharmacy['warehouse_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($pharmacy['warehouse_code'] ?? ''); ?></small>
                                        </td>
                                        <td class="text-right">
                                            <strong><?php echo number_format($pharmacy['kpi_total_revenue'], 2); ?></strong>
                                            <br>
                                            <small class="text-muted">SAR</small>
                                        </td>
                                        <td class="text-right">
                                            <?php echo number_format($pharmacy['kpi_total_cost'], 2); ?>
                                            <br>
                                            <small class="text-muted">SAR</small>
                                        </td>
                                        <td class="text-right">
                                            <strong><?php echo number_format($pharmacy['kpi_profit_loss'], 2); ?></strong>
                                            <br>
                                            <small class="text-muted">SAR</small>
                                        </td>
                                        <td class="text-right">
                                            <?php 
                                                $margin = $pharmacy['kpi_profit_margin_pct'] ?? 0;
                                                $margin_class = ($margin >= 35) ? 'text-success' : (($margin >= 25) ? 'text-warning' : 'text-danger');
                                            ?>
                                            <strong class="<?php echo $margin_class; ?>">
                                                <?php echo number_format($margin, 1); ?>%
                                            </strong>
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-info">
                                                <?php echo $pharmacy['branch_count'] ?? 0; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); goToPharmacy(<?php echo $pharmacy['warehouse_id']; ?>)">
                                                <i class="fas fa-arrow-right"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Dashboard -->
<script>
    // Period change handler
    function changePeriod(period) {
        if (period) {
            window.location.href = '<?php echo site_url('admin/cost_center/dashboard'); ?>?period=' + period;
        }
    }

    // Navigate to pharmacy detail
    function goToPharmacy(pharmacyId) {
        const period = '<?php echo $period; ?>';
        window.location.href = '<?php echo site_url('admin/cost_center/pharmacy'); ?>/' + pharmacyId + '?period=' + period;
    }

    // Sort table
    function sortTable(sortBy) {
        const period = '<?php echo $period; ?>';
        $.ajax({
            url: '<?php echo site_url('admin/cost_center/get_pharmacies'); ?>',
            type: 'GET',
            data: {
                period: period,
                sort_by: sortBy,
                page: 1,
                limit: 100
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function() {
                alert('Error loading pharmacies');
            }
        });
    }

    // Download chart as image
    function downloadChart(chartId) {
        const element = document.getElementById(chartId);
        if (element) {
            const link = document.createElement('a');
            link.href = element.toDataURL('image/png');
            link.download = chartId + '_' + new Date().getTime() + '.png';
            link.click();
        }
    }

    // Initialize charts on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeTrendChart();
    });

    // Initialize trend chart using Chart.js
    function initializeTrendChart() {
        const ctx = document.getElementById('trendChart');
        if (!ctx) return;

        // Sample data - in production, this would come from backend
        const chartData = {
            labels: ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6', 'Day 7'],
            datasets: [
                {
                    label: 'Revenue',
                    data: [65000, 68000, 62000, 71000, 75000, 72000, 78000],
                    borderColor: '#1E90FF',
                    backgroundColor: 'rgba(30, 144, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#1E90FF',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                },
                {
                    label: 'Cost',
                    data: [35000, 37000, 34000, 38000, 40000, 38000, 41000],
                    borderColor: '#FF6B6B',
                    backgroundColor: 'rgba(255, 107, 107, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#FF6B6B',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }
            ]
        };

        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
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
    .cursor-pointer {
        cursor: pointer;
    }
    .cursor-pointer:hover {
        background-color: #f8f9fa;
    }
    #pharmacyTable tbody tr {
        transition: background-color 0.2s ease;
    }
</style>
