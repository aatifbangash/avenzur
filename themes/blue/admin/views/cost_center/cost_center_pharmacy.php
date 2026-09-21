<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
/**
 * Cost Center Pharmacy Detail View - Blue Theme
 * 
 * Displays:
 * - Pharmacy metrics header
 * - All branches with KPIs
 * - Branch comparison chart
 * - Period selector and navigation
 * 
 * Data variables:
 * - $pharmacy: Pharmacy metrics object
 * - $branches: Array of branch objects with KPIs
 * - $periods: Available periods for selection
 * - $period: Selected period (YYYY-MM)
 */
?>

<div class="box">
    <!-- Box Header -->
    <div class="box-header">
        <h2 class="blue"><i class="fa fa-building"></i> <?= htmlspecialchars($pharmacy['pharmacy_name'] ?? ''); ?></h2>
        
        <div class="box-icon">
            <ul class="btn-tasks">
                <li>
                    <a href="<?php echo site_url('admin/cost_center/dashboard'); ?>" title="Back to Dashboard">
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
        <!-- Pharmacy Code -->
        <div class="row mb-3">
            <div class="col-lg-12">
                <p class="text-muted">
                    <i class="fa fa-code"></i> <strong>Code:</strong> <?php echo htmlspecialchars($pharmacy['pharmacy_code'] ?? ''); ?> 
                    <span class="mx-3">|</span>
                    <i class="fa fa-sitemap"></i> <strong>Branches:</strong> <?php echo $pharmacy['branch_count'] ?? 0; ?>
                </p>
            </div>
        </div>

        <!-- Pharmacy Metrics Cards -->
        <div class="row">
            <!-- Revenue Card -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="info-box blue-bg">
                    <i class="fa fa-money"></i>
                    <div class="info-box-content">
                        <span class="info-box-text">Revenue</span>
                        <span class="info-box-number"><?php echo number_format($pharmacy['kpi_total_revenue'] ?? 0, 0); ?> <small>SAR</small></span>
                    </div>
                </div>
            </div>

            <!-- Cost Card -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="info-box orange-bg">
                    <i class="fa fa-shopping-cart"></i>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Cost</span>
                        <span class="info-box-number"><?php echo number_format($pharmacy['kpi_total_cost'] ?? 0, 0); ?> <small>SAR</small></span>
                    </div>
                </div>
            </div>

            <!-- Profit Card -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="info-box green-bg">
                    <i class="fa fa-check-circle"></i>
                    <div class="info-box-content">
                        <span class="info-box-text">Profit</span>
                        <span class="info-box-number"><?php echo number_format($pharmacy['kpi_profit_loss'] ?? 0, 0); ?> <small>SAR</small></span>
                    </div>
                </div>
            </div>

            <!-- Margin Card -->
            <div class="col-lg-3 col-sm-6 col-xs-12">
                <div class="info-box red-bg">
                    <i class="fa fa-fire"></i>
                    <div class="info-box-content">
                        <span class="info-box-text">Profit Margin %</span>
                        <span class="info-box-number"><?php echo number_format($pharmacy['kpi_profit_margin_pct'] ?? 0, 1); ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Comparison Chart Box -->
        <div class="col-lg-12" style="margin-top: 20px;">
            <div class="box">
                <div class="box-header">
                    <h4><i class="fa fa-bar-chart"></i> Branch Comparison - Profit by Branch</h4>
                </div>
                <div class="box-content">
                    <div id="branchChart" style="width: 100%; height: 400px;"></div>
                </div>
            </div>
        </div>

        <!-- Branches Table Box -->
        <div class="col-lg-12" style="margin-top: 20px;">
            <div class="box">
                <div class="box-header">
                    <h4><i class="fa fa-sitemap"></i> All Branches (<?php echo count($branches); ?>)</h4>
                </div>
                <div class="box-content">
                    <table class="table table-hover table-bordered" id="branchesTable">
                        <thead>
                            <tr class="bg-light">
                                <th>Branch Name</th>
                                <th class="text-right">Revenue</th>
                                <th class="text-right">Cost</th>
                                <th class="text-right">Profit</th>
                                <th class="text-right">Margin %</th>
                                <th class="text-right">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($branches)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fa fa-info-circle"></i> No branches data available
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($branches as $branch): ?>
                                    <?php 
                                        $status = $branch['kpi_profit_margin_pct'] >= 35 
                                            ? 'text-success' 
                                            : ($branch['kpi_profit_margin_pct'] >= 25 
                                                ? 'text-warning' 
                                                : 'text-danger');
                                        $status_text = $branch['kpi_profit_margin_pct'] >= 35 
                                            ? '✓ Healthy' 
                                            : ($branch['kpi_profit_margin_pct'] >= 25 
                                                ? '⚠ Monitor' 
                                                : '✗ Low');
                                    ?>
                                    <tr class="cursor-pointer" onclick="goToBranch(<?php echo $branch['warehouse_id']; ?>)">
                                        <td>
                                            <strong><?php echo htmlspecialchars($branch['warehouse_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($branch['warehouse_code'] ?? ''); ?></small>
                                        </td>
                                        <td class="text-right">
                                            <strong><?php echo number_format($branch['kpi_total_revenue'], 2); ?></strong>
                                            <br>
                                            <small class="text-muted">SAR</small>
                                        </td>
                                        <td class="text-right">
                                            <?php echo number_format($branch['kpi_total_cost'], 2); ?>
                                            <br>
                                            <small class="text-muted">SAR</small>
                                        </td>
                                        <td class="text-right">
                                            <strong><?php echo number_format($branch['kpi_profit_loss'], 2); ?></strong>
                                            <br>
                                            <small class="text-muted">SAR</small>
                                        </td>
                                        <td class="text-right">
                                            <strong class="<?php echo $status; ?>">
                                                <?php echo number_format($branch['kpi_profit_margin_pct'] ?? 0, 1); ?>%
                                            </strong>
                                        </td>
                                        <td class="text-right">
                                            <span class="<?php echo $status; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); goToBranch(<?php echo $branch['warehouse_id']; ?>)">
                                                <i class="fa fa-arrow-right"></i> Detail
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

<!-- JavaScript -->
<script>
    const pharmacyId = <?php echo $pharmacy['warehouse_id'] ?? 0; ?>;
    const period = '<?php echo $period; ?>';

    // Period change handler
    function changePeriod(selectedPeriod) {
        if (selectedPeriod) {
            window.location.href = '<?php echo site_url('admin/cost_center/pharmacy'); ?>/' + pharmacyId + '?period=' + selectedPeriod;
        }
    }

    // Navigate to branch detail
    function goToBranch(branchId) {
        window.location.href = '<?php echo site_url('admin/cost_center/branch'); ?>/' + branchId + '?period=' + period;
    }

    // Sort branches
    function sortBranches(sortBy) {
        // In production, this would call an API
        alert('Sort by ' + sortBy);
        location.reload();
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

    // Initialize branch comparison chart
    document.addEventListener('DOMContentLoaded', function() {
        initializeBranchChart();
    });

    function initializeBranchChart() {
        const ctx = document.getElementById('branchChart');
        if (!ctx) return;

        const branches = <?php echo json_encode($branches); ?>;
        const labels = branches.map(b => b.warehouse_name);
        const profitData = branches.map(b => b.kpi_profit_loss);
        const colors = branches.map(b => {
            const margin = b.kpi_profit_margin_pct || 0;
            return margin >= 35 ? '#51CF66' : (margin >= 25 ? '#FFD93D' : '#FF6B6B');
        });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Profit (SAR)',
                    data: profitData,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: 'y',
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div><!-- End of box-content -->
</div><!-- End of main box -->

<script src="<?php echo $assets; ?>js/echarts.min.js"></script>
<script>
    // Period change handler
    function changePeriod(element) {
        const period = element.value;
        if (period) {
            window.location.href = '<?php echo site_url('admin/cost_center/pharmacy'); ?>/<?php echo $pharmacy['warehouse_id']; ?>?period=' + period;
        }
    }

    // Navigate to branch detail
    function goToBranch(branchId) {
        const period = '<?php echo $period; ?>';
        window.location.href = '<?php echo site_url('admin/cost_center/branch'); ?>/' + branchId + '?period=' + period;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeBranchChart();
    });

    // Initialize branch comparison chart using ECharts
    function initializeBranchChart() {
        const chartDom = document.getElementById('branchChart');
        if (!chartDom) return;

        const myChart = echarts.init(chartDom);

        // Sample data - in production, this would come from backend
        const option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            legend: {
                data: ['Revenue', 'Cost', 'Profit']
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: ['Branch 1', 'Branch 2', 'Branch 3']
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    formatter: function(value) {
                        return (value / 1000).toFixed(0) + 'K';
                    }
                }
            },
            series: [
                {
                    name: 'Revenue',
                    data: [45000, 50000, 48000],
                    type: 'bar',
                    itemStyle: { color: '#1E90FF' }
                },
                {
                    name: 'Cost',
                    data: [25000, 28000, 26000],
                    type: 'bar',
                    itemStyle: { color: '#FF6B6B' }
                },
                {
                    name: 'Profit',
                    data: [20000, 22000, 22000],
                    type: 'bar',
                    itemStyle: { color: '#51CF66' }
                }
            ]
        };

        myChart.setOption(option);

        // Responsive resize
        window.addEventListener('resize', function() {
            myChart.resize();
        });
    }
</script>
