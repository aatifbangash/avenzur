<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-icon">
                <svg class="icon-30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M2 12h20"/>
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                </svg>
            </div>
            <div class="page-title">
                <h1><?php echo lang('Loyalty Dashboard'); ?></h1>
                <p class="text-muted"><?php echo lang('Overview of your loyalty program budgets and spending'); ?></p>
            </div>
        </div>
        <div class="page-header-right">
            <a href="<?php echo admin_url('loyalty/budget_definition'); ?>" class="btn btn-primary">
                <i class="fa fa-plus"></i> <?php echo lang('New Budget'); ?>
            </a>
        </div>
    </div>

    <!-- Control Bar -->
    <div class="control-bar mb-4 p-3 bg-white rounded-lg shadow-sm d-flex justify-content-between align-items-center">
        <div class="control-bar-left d-flex gap-3">
            <div class="form-group mb-0">
                <label class="small fw-600 text-muted"><?php echo lang('Period'); ?></label>
                <select id="periodFilter" class="form-control form-control-sm" onchange="filterByPeriod(this.value)">
                    <option value="monthly"><?php echo lang('This Month'); ?></option>
                    <option value="quarterly"><?php echo lang('This Quarter'); ?></option>
                    <option value="annual"><?php echo lang('This Year'); ?></option>
                    <option value="all"><?php echo lang('All Time'); ?></option>
                </select>
            </div>
            <div class="form-group mb-0">
                <label class="small fw-600 text-muted"><?php echo lang('Status'); ?></label>
                <select id="statusFilter" class="form-control form-control-sm" onchange="filterByStatus(this.value)">
                    <option value="all"><?php echo lang('All'); ?></option>
                    <option value="active"><?php echo lang('Active'); ?></option>
                    <option value="exceeded"><?php echo lang('Exceeded'); ?></option>
                    <option value="warning"><?php echo lang('Warning'); ?></option>
                </select>
            </div>
        </div>
        <div class="control-bar-right d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fa fa-refresh"></i> <?php echo lang('Refresh'); ?>
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="exportDashboard()">
                <i class="fa fa-download"></i> <?php echo lang('Export'); ?>
            </button>
        </div>
    </div>

    <!-- KPI Cards Grid (4 columns) -->
    <div class="row mb-4">
        <!-- Total Budget Card -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-card-icon bg-gradient-blue">
                    <svg class="icon-24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                    </svg>
                </div>
                <div class="metric-card-body">
                    <h6 class="metric-card-title"><?php echo lang('Total Budget'); ?></h6>
                    <div class="metric-card-value">
                        <span class="amount" id="totalBudget">0</span>
                        <span class="currency">SAR</span>
                    </div>
                    <div class="metric-card-footer">
                        <small class="text-muted"><?php echo lang('Across all allocations'); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Spent Card -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-card-icon bg-gradient-orange">
                    <svg class="icon-24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
                    </svg>
                </div>
                <div class="metric-card-body">
                    <h6 class="metric-card-title"><?php echo lang('Total Spent'); ?></h6>
                    <div class="metric-card-value">
                        <span class="amount" id="totalSpent">0</span>
                        <span class="currency">SAR</span>
                    </div>
                    <div class="metric-card-footer">
                        <small class="text-muted"><span id="spendPercentage">0</span>% <?php echo lang('of budget'); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remaining Budget Card -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-card-icon bg-gradient-green">
                    <svg class="icon-24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <div class="metric-card-body">
                    <h6 class="metric-card-title"><?php echo lang('Remaining Budget'); ?></h6>
                    <div class="metric-card-value">
                        <span class="amount" id="remainingBudget">0</span>
                        <span class="currency">SAR</span>
                    </div>
                    <div class="metric-card-footer">
                        <small class="text-muted"><?php echo lang('Available to spend'); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Allocations Card -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-card-icon bg-gradient-purple">
                    <svg class="icon-24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S15.33 8 14.5 8 13 8.67 13 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S8.33 8 7.5 8 6 8.67 6 9.5 6.67 11 7.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                    </svg>
                </div>
                <div class="metric-card-body">
                    <h6 class="metric-card-title"><?php echo lang('Active Allocations'); ?></h6>
                    <div class="metric-card-value">
                        <span class="amount" id="activeAllocations">0</span>
                        <span class="unit"><?php echo lang('Allocations'); ?></span>
                    </div>
                    <div class="metric-card-footer">
                        <small class="text-muted"><?php echo lang('Currently active'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid (2 sections) -->
    <div class="row mb-4">
        <!-- Chart Section: Budget vs Spending -->
        <div class="col-lg-6 mb-4">
            <div class="chart-card">
                <div class="chart-header">
                    <h6><?php echo lang('Budget vs Spending'); ?></h6>
                    <small class="text-muted"><?php echo lang('Monthly comparison'); ?></small>
                </div>
                <div id="budgetVsSpendingChart" class="chart-container" style="height: 300px;">
                    <div class="text-center text-muted p-4">
                        <small><?php echo lang('Loading chart...'); ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section: Spending by Category -->
        <div class="col-lg-6 mb-4">
            <div class="chart-card">
                <div class="chart-header">
                    <h6><?php echo lang('Budget Allocations'); ?></h6>
                    <small class="text-muted"><?php echo lang('Distribution by pharmacy'); ?></small>
                </div>
                <div id="spendingByCategoryChart" class="chart-container" style="height: 300px;">
                    <div class="text-center text-muted p-4">
                        <small><?php echo lang('Loading chart...'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Allocations Table -->
    <div class="card mb-4">
        <div class="card-header-custom">
            <h6 class="mb-0"><?php echo lang('Recent Budget Allocations'); ?></h6>
            <a href="<?php echo admin_url('loyalty/budget_definition'); ?>" class="small">
                <?php echo lang('View All'); ?> <i class="fa fa-arrow-right"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr class="table-header-gradient">
                        <th><?php echo lang('Reference'); ?></th>
                        <th><?php echo lang('Period'); ?></th>
                        <th><?php echo lang('Amount'); ?></th>
                        <th><?php echo lang('Spent'); ?></th>
                        <th><?php echo lang('Status'); ?></th>
                        <th><?php echo lang('Created By'); ?></th>
                        <th><?php echo lang('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody id="allocationsTableBody">
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <small><?php echo lang('Loading allocations...'); ?></small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for viewing allocation details -->
<div class="modal fade" id="allocationDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo lang('Allocation Details'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="allocationDetailBody">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Page Header Styles */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 2rem 0;
    }

    .page-header-left {
        gap: 1rem;
    }

    .page-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .page-icon svg {
        width: 30px;
        height: 30px;
    }

    .page-title h1 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
    }

    .page-title p {
        margin: 0.25rem 0 0 0;
        font-size: 0.875rem;
    }

    /* Control Bar */
    .control-bar {
        border: 1px solid #e5e7eb;
    }

    .control-bar-left,
    .control-bar-right {
        display: flex;
        gap: 1rem;
    }

    .control-bar .form-group {
        min-width: 180px;
    }

    /* Metric Cards */
    .metric-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        gap: 1rem;
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        border-color: #d1d5db;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .metric-card-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
    }

    .bg-gradient-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    }

    .bg-gradient-orange {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .bg-gradient-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .bg-gradient-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    }

    .metric-card-icon svg {
        width: 24px;
        height: 24px;
    }

    .metric-card-body {
        flex: 1;
    }

    .metric-card-title {
        font-size: 0.75rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .metric-card-value {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
        margin: 0.5rem 0;
    }

    .metric-card-value .amount {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2937;
    }

    .metric-card-value .currency,
    .metric-card-value .unit {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    .metric-card-footer {
        margin-top: 0.5rem;
    }

    /* Chart Cards */
    .chart-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
    }

    .chart-header {
        margin-bottom: 1.5rem;
    }

    .chart-header h6 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
    }

    .chart-container {
        position: relative;
    }

    /* Card Table Styles */
    .card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .card-header-custom {
        padding: 1.5rem;
        background: white;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header-custom h6 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
    }

    /* Table Styles */
    .table {
        font-size: 0.875rem;
    }

    .table-header-gradient {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .table-header-gradient th {
        border: none;
        padding: 1rem 1.5rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.75rem;
    }

    .table tbody tr:hover {
        background-color: #f9fafb;
    }

    .table td {
        padding: 1rem 1.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #e5e7eb;
    }

    /* Status Badges */
    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-success {
        background-color: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background-color: #fef3c7;
        color: #92400e;
    }

    .badge-danger {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .badge-info {
        background-color: #dbeafe;
        color: #0c2d6b;
    }

    /* Action Buttons */
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
    }

    .btn-outline-secondary {
        border-color: #d1d5db;
        color: #6b7280;
    }

    .btn-outline-secondary:hover {
        background-color: #f3f4f6;
        border-color: #9ca3af;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 1.5rem;
            align-items: flex-start;
        }

        .page-header-right {
            width: 100%;
        }

        .control-bar {
            flex-direction: column;
        }

        .control-bar-left,
        .control-bar-right {
            width: 100%;
            flex-wrap: wrap;
        }

        .control-bar .form-group {
            flex: 1;
            min-width: 150px;
        }

        .metric-card {
            padding: 1rem;
        }

        .metric-card-value .amount {
            font-size: 1.5rem;
        }

        .table {
            font-size: 0.75rem;
        }

        .table td {
            padding: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .metric-card {
            flex-direction: column;
        }

        .metric-card-icon {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .control-bar-left,
        .control-bar-right {
            flex-direction: column;
        }

        .control-bar .form-group {
            width: 100%;
            min-width: auto;
        }
    }
</style>

<script>
    // Initialize dashboard on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadDashboardData();
    });

    /**
     * Load all dashboard data
     */
    function loadDashboardData() {
        // Load summary metrics
        loadSummaryMetrics();
        // Load charts
        loadBudgetVsSpendingChart();
        loadSpendingByCategoryChart();
        // Load allocations table
        loadAllocationsTable();
    }

    /**
     * Load summary metrics from API
     */
    function loadSummaryMetrics() {
        fetch('<?php echo admin_url('loyalty/get_summary'); ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('totalBudget').textContent = formatCurrency(data.totalBudget);
                    document.getElementById('totalSpent').textContent = formatCurrency(data.totalSpent);
                    document.getElementById('spendPercentage').textContent = Math.round((data.totalSpent / data.totalBudget) * 100);
                    document.getElementById('remainingBudget').textContent = formatCurrency(data.remainingBudget);
                    document.getElementById('activeAllocations').textContent = data.activeAllocations;
                }
            })
            .catch(error => console.error('Error loading summary:', error));
    }

    /**
     * Load Budget vs Spending chart
     */
    function loadBudgetVsSpendingChart() {
        // This would load data from API and render chart
        // Placeholder for ECharts integration
        console.log('Loading Budget vs Spending chart...');
    }

    /**
     * Load Spending by Category chart
     */
    function loadSpendingByCategoryChart() {
        // This would load data from API and render chart
        // Placeholder for ECharts integration
        console.log('Loading Spending by Category chart...');
    }

    /**
     * Load allocations table
     */
    function loadAllocationsTable() {
        fetch('<?php echo admin_url('loyalty/get_budget_status'); ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.allocations) {
                    renderAllocationsTable(data.allocations);
                }
            })
            .catch(error => console.error('Error loading allocations:', error));
    }

    /**
     * Render allocations table
     */
    function renderAllocationsTable(allocations) {
        const tbody = document.getElementById('allocationsTableBody');
        if (!allocations || allocations.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted"><small><?php echo lang('No allocations found'); ?></small></td></tr>';
            return;
        }

        tbody.innerHTML = allocations.map(alloc => `
            <tr>
                <td>${alloc.reference || 'N/A'}</td>
                <td>${alloc.period || 'N/A'}</td>
                <td>${formatCurrency(alloc.allocated || 0)}</td>
                <td>${formatCurrency(alloc.spent || 0)}</td>
                <td><span class="badge badge-${getStatusClass(alloc.status)}">${alloc.status || 'Pending'}</span></td>
                <td>${alloc.created_by || 'System'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-secondary" onclick="viewAllocationDetail('${alloc.id}')">
                        <i class="fa fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    /**
     * Format currency
     */
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-SA', {
            style: 'currency',
            currency: 'SAR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    /**
     * Get status badge class
     */
    function getStatusClass(status) {
        const statusMap = {
            'Active': 'success',
            'Pending': 'warning',
            'Exceeded': 'danger',
            'Archived': 'info'
        };
        return statusMap[status] || 'info';
    }

    /**
     * Filter by period
     */
    function filterByPeriod(period) {
        console.log('Filtering by period:', period);
        loadDashboardData();
    }

    /**
     * Filter by status
     */
    function filterByStatus(status) {
        console.log('Filtering by status:', status);
        loadDashboardData();
    }

    /**
     * Export dashboard data
     */
    function exportDashboard() {
        console.log('Exporting dashboard data...');
        // Implement export functionality
    }

    /**
     * View allocation detail
     */
    function viewAllocationDetail(id) {
        const modal = new bootstrap.Modal(document.getElementById('allocationDetailModal'));
        modal.show();
        // Load and display detail
    }
</script>
