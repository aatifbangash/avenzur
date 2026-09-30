<?php
/**
 * Cost Center Pharmacy Detail View
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
<div class="container-fluid mt-4">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb bg-light rounded px-3 py-2">
            <li class="breadcrumb-item">
                <a href="<?php echo site_url('admin/cost_center/dashboard'); ?>">
                    <i class="fas fa-chart-pie"></i> Cost Center
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars($pharmacy['pharmacy_name'] ?? ''); ?>
            </li>
        </ol>
    </nav>

    <!-- Header Row -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 text-dark mb-2">
                <?php echo htmlspecialchars($pharmacy['pharmacy_name'] ?? ''); ?>
            </h1>
            <p class="text-muted">
                <i class="fas fa-code"></i> <?php echo htmlspecialchars($pharmacy['pharmacy_code'] ?? ''); ?>
                <span class="mx-2">|</span>
                <i class="fas fa-building"></i> <?php echo $pharmacy['branch_count'] ?? 0; ?> branches
            </p>
        </div>
        <div class="col-md-4 text-right">
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
            <a href="<?php echo site_url('admin/cost_center/dashboard?period=' . $period); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Pharmacy Metrics Cards -->
    <div class="row mb-4">
        <!-- Revenue Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-uppercase mb-1">Revenue</div>
                    <h3 class="h4 font-weight-bold mb-0">
                        <?php echo number_format($pharmacy['kpi_total_revenue'] ?? 0, 2); ?>
                        <span class="text-muted" style="font-size: 0.7em;">SAR</span>
                    </h3>
                </div>
            </div>
        </div>

        <!-- Cost Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="text-danger font-weight-bold text-uppercase mb-1">Total Cost</div>
                    <h3 class="h4 font-weight-bold mb-0">
                        <?php echo number_format($pharmacy['kpi_total_cost'] ?? 0, 2); ?>
                        <span class="text-muted" style="font-size: 0.7em;">SAR</span>
                    </h3>
                </div>
            </div>
        </div>

        <!-- Profit Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-uppercase mb-1">Profit</div>
                    <h3 class="h4 font-weight-bold mb-0">
                        <?php echo number_format($pharmacy['kpi_profit_loss'] ?? 0, 2); ?>
                        <span class="text-muted" style="font-size: 0.7em;">SAR</span>
                    </h3>
                </div>
            </div>
        </div>

        <!-- Margin Card -->
        <div class="col-md-3 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-uppercase mb-1">Margin %</div>
                    <h3 class="h4 font-weight-bold mb-0">
                        <?php echo number_format($pharmacy['kpi_profit_margin_pct'] ?? 0, 1); ?>%
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Comparison Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between">
                    <h6 class="mb-0 font-weight-bold">Branch Comparison - Profit by Branch</h6>
                    <button class="btn btn-xs btn-outline-secondary" onclick="downloadChart('branchChart')">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                <div class="card-body">
                    <canvas id="branchChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Branches Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between">
                    <h6 class="mb-0 font-weight-bold">All Branches (<?php echo count($branches); ?>)</h6>
                    <div>
                        <button class="btn btn-xs btn-outline-secondary mr-1" onclick="sortBranches('revenue')">
                            <i class="fas fa-sort-amount-down"></i> By Revenue
                        </button>
                        <button class="btn btn-xs btn-outline-secondary" onclick="sortBranches('profit')">
                            <i class="fas fa-sort-amount-down"></i> By Profit
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-hover" id="branchesTable">
                        <thead class="bg-light">
                            <tr>
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
                                        <i class="fas fa-info-circle"></i> No branches data available
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
                                            <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); goToBranch(<?php echo $branch['warehouse_id']; ?>)">
                                                <i class="fas fa-arrow-right"></i> Detail
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
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
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
</style>
