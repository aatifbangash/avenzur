<?php
/**
 * Loyalty Budget Definition View
 * 
 * Purpose: Allow admin to define budget at company/pharmacy group level
 * 
 * Features:
 * - Define budgets at company/pharma group level
 * - Period selector (Monthly/Quarterly/Yearly)
 * - Budget amount input with validation
 * - View and manage existing budget definitions
 * - Edit/Archive budget definitions
 */
?>

<style>
/* ============================================================================
   BUDGET DEFINITION - HORIZON UI DESIGN SYSTEM
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

.horizon-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid var(--horizon-border);
    background: #ffffff;
}

.horizon-header-title h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 600;
    color: var(--horizon-dark-text);
}

.horizon-header-title p {
    margin: 4px 0 0 0;
    font-size: 13px;
    color: var(--horizon-light-text);
}

.horizon-control-bar {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary, .btn-outline {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: var(--horizon-primary);
    color: white;
}

.btn-primary:hover {
    background: #1557b0;
    box-shadow: 0 2px 8px rgba(26, 115, 232, 0.25);
}

.btn-secondary {
    background: var(--horizon-secondary);
    color: white;
}

.btn-secondary:hover {
    background: #5a4ba8;
}

.btn-outline {
    background: transparent;
    color: var(--horizon-primary);
    border: 1px solid var(--horizon-primary);
}

.btn-outline:hover {
    background: rgba(26, 115, 232, 0.05);
}

.content-wrapper {
    padding: 24px;
}

.allocation-panel {
    background: #ffffff;
    border: 1px solid var(--horizon-border);
    border-radius: 8px;
    padding: 20px;
    box-shadow: var(--horizon-shadow-sm);
    margin-bottom: 24px;
}

.allocation-panel-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--horizon-dark-text);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.allocation-panel-title i {
    color: var(--horizon-primary);
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: var(--horizon-dark-text);
    margin-bottom: 6px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--horizon-border);
    border-radius: 6px;
    font-size: 13px;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--horizon-primary);
    box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
}

.period-selector {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.period-btn {
    padding: 10px 20px;
    border: 2px solid var(--horizon-border);
    background: #ffffff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    color: var(--horizon-dark-text);
    flex: 1;
    min-width: 120px;
    text-align: center;
}

.period-btn:hover {
    border-color: var(--horizon-primary);
    background: rgba(26, 115, 232, 0.05);
}

.period-btn.active {
    border-color: var(--horizon-primary);
    background: rgba(26, 115, 232, 0.1);
    color: var(--horizon-primary);
    box-shadow: 0 2px 12px rgba(26, 115, 232, 0.2);
}

.budget-summary {
    background: rgba(26, 115, 232, 0.05);
    border: 1px solid rgba(26, 115, 232, 0.2);
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 16px;
}

.budget-summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    margin-bottom: 6px;
}

.budget-summary-row:last-child {
    margin-bottom: 0;
}

.budget-summary-label {
    color: var(--horizon-light-text);
    font-weight: 500;
}

.budget-summary-value {
    color: var(--horizon-dark-text);
    font-weight: 600;
}

.allocation-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.allocation-table thead {
    background: var(--horizon-bg-light);
}

.allocation-table th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: var(--horizon-dark-text);
    border-bottom: 2px solid var(--horizon-border);
}

.allocation-table td {
    padding: 12px;
    border-bottom: 1px solid var(--horizon-border);
    color: var(--horizon-dark-text);
}

.allocation-table tbody tr:hover {
    background: rgba(26, 115, 232, 0.02);
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
}

.badge-info {
    background: rgba(26, 115, 232, 0.15);
    color: var(--horizon-primary);
}

.badge-success {
    background: rgba(5, 205, 153, 0.15);
    color: var(--horizon-success);
}

.badge-warning {
    background: rgba(255, 154, 86, 0.15);
    color: var(--horizon-warning);
}

.action-btn {
    padding: 4px 8px;
    margin: 0 2px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--horizon-primary);
    font-size: 14px;
    transition: all 0.2s ease;
}

.action-btn:hover {
    color: #1557b0;
    transform: scale(1.1);
}

.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid var(--horizon-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.modal-close {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    color: var(--horizon-light-text);
    padding: 0;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 16px 20px;
    border-top: 1px solid var(--horizon-border);
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .horizon-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .period-selector {
        flex-direction: column;
    }

    .period-btn {
        width: 100%;
    }
}
</style>

<!-- Main Dashboard Container -->
<div class="horizon-dashboard">
    <!-- Header Section -->
    <div class="horizon-header">
        <div class="horizon-header-title">
            <h1><i class="fa fa-wallet"></i> Budget Definition</h1>
            <p>Define and manage loyalty/discount budgets at company/pharma group level</p>
        </div>
        <div class="horizon-control-bar">
            <button class="btn-primary" onclick="openBudgetModal()">
                <i class="fa fa-plus"></i> Define New Budget
            </button>
            <button class="btn-outline" onclick="location.reload()">
                <i class="fa fa-refresh"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <!-- Active Budget Overview -->
        <div class="allocation-panel">
            <div class="allocation-panel-title">
                <i class="fa fa-chart-line"></i> Current Budget Overview
            </div>
            <div class="budget-summary">
                <div class="budget-summary-row">
                    <span class="budget-summary-label">Total Budget Defined:</span>
                    <span class="budget-summary-value"><?php echo number_format($summary['data']['total_budget'] ?? 0, 2); ?> SAR</span>
                </div>
                <div class="budget-summary-row">
                    <span class="budget-summary-label">Budget Spent:</span>
                    <span class="budget-summary-value"><?php echo number_format($summary['data']['spent'] ?? 0, 2); ?> SAR</span>
                </div>
                <div class="budget-summary-row">
                    <span class="budget-summary-label">Remaining Budget:</span>
                    <span class="budget-summary-value"><?php echo number_format($summary['data']['available'] ?? 0, 2); ?> SAR</span>
                </div>
                <div class="budget-summary-row">
                    <span class="budget-summary-label">Utilization:</span>
                    <span class="budget-summary-value"><?php echo number_format($summary['data']['percentage_used'] ?? 0, 1); ?>%</span>
                </div>
            </div>
        </div>

        <!-- Budget Definitions Table -->
        <div class="allocation-panel">
            <div class="allocation-panel-title">
                <i class="fa fa-table"></i> Budget Definitions
            </div>
            <table class="allocation-table">
                <thead>
                    <tr>
                        <th>Period Type</th>
                        <th>Period</th>
                        <th>Budget Amount (SAR)</th>
                        <th>Allocated</th>
                        <th>Spent</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($allocations)): ?>
                        <?php foreach ($allocations as $alloc): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo ucfirst($alloc['period_type'] ?? 'Monthly'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($alloc['period_year']) && !empty($alloc['period_month'])) {
                                        echo date('M Y', strtotime($alloc['period_year'] . '-' . str_pad($alloc['period_month'], 2, '0', STR_PAD_LEFT) . '-01')); 
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td style="font-weight: 600; color: var(--horizon-success);">
                                    <?php echo number_format($alloc['total_allocated_amount'] ?? 0, 2); ?>
                                </td>
                                <td><?php echo number_format($alloc['total_allocated'] ?? 0, 2); ?></td>
                                <td><?php echo number_format($alloc['total_spent'] ?? 0, 2); ?></td>
                                <td>
                                    <?php 
                                    $status = $alloc['status'] ?? 'active';
                                    $badgeClass = $status === 'active' ? 'badge-success' : 'badge-warning';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($alloc['allocated_by_user_name'] ?? 'System'); ?></td>
                                <td>
                                    <button class="action-btn" onclick="editBudget(<?php echo htmlspecialchars(json_encode($alloc)); ?>)" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button class="action-btn" onclick="viewDetails(<?php echo $alloc['allocation_id'] ?? 0; ?>)" title="View Details">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button class="action-btn" onclick="archiveBudget(<?php echo $alloc['allocation_id'] ?? 0; ?>)" title="Archive">
                                        <i class="fa fa-archive"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--horizon-light-text);">
                                <i class="fa fa-inbox" style="font-size: 32px; margin-bottom: 12px; display: block;"></i>
                                <p>No budget definitions found. Click "Define New Budget" to get started.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Budget Definition Modal -->
<div class="modal-overlay" id="budgetModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fa fa-wallet"></i> Define New Budget</h2>
            <button class="modal-close" onclick="closeBudgetModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="budgetForm" action="<?php echo admin_url('loyalty/save_budget'); ?>" method="POST">
                <div class="form-group">
                    <label>Budget Level <span style="color: var(--horizon-error);">*</span></label>
                    <select name="hierarchy_level" id="hierarchy_level" required>
                        <option value="company">Company Level</option>
                        <!-- <option value="pharma_group">Pharma Group Level</option> -->
                    </select>
                    <small style="color: var(--horizon-light-text); font-size: 11px;">
                        <i class="fa fa-info-circle"></i> Budget is defined at Company level
                    </small>
                </div>

                <div class="form-group">
                    <label>Period Type <span style="color: var(--horizon-error);">*</span></label>
                    <div class="period-selector">
                        <button type="button" class="period-btn active" data-period="monthly" onclick="selectPeriod('monthly')">
                            <i class="fa fa-calendar"></i> Monthly
                        </button>
                        <button type="button" class="period-btn" data-period="quarterly" onclick="selectPeriod('quarterly')">
                            <i class="fa fa-calendar-alt"></i> Quarterly
                        </button>
                        <button type="button" class="period-btn" data-period="yearly" onclick="selectPeriod('yearly')">
                            <i class="fa fa-calendar-check"></i> Yearly
                        </button>
                    </div>
                    <input type="hidden" name="period_type" id="period_type" value="monthly">
                </div>

                <div class="form-group" id="period_selection_monthly">
                    <label>Select Month <span style="color: var(--horizon-error);">*</span></label>
                    <input type="month" name="period_month" id="period_month" value="<?php echo date('Y-m'); ?>">
                </div>

                <div class="form-group" id="period_selection_quarterly" style="display: none;">
                    <label>Select Quarter <span style="color: var(--horizon-error);">*</span></label>
                    <select name="period_quarter" id="period_quarter">
                        <option value="Q1">Q1 (Jan - Mar)</option>
                        <option value="Q2">Q2 (Apr - Jun)</option>
                        <option value="Q3">Q3 (Jul - Sep)</option>
                        <option value="Q4">Q4 (Oct - Dec)</option>
                    </select>
                    <input type="number" name="quarter_year" id="quarter_year" placeholder="Year (e.g., 2025)" value="<?php echo date('Y'); ?>" style="margin-top: 8px;">
                </div>

                <div class="form-group" id="period_selection_yearly" style="display: none;">
                    <label>Select Year <span style="color: var(--horizon-error);">*</span></label>
                    <input type="number" name="period_year" id="period_year" placeholder="Year (e.g., 2025)" value="<?php echo date('Y'); ?>">
                </div>

                <div class="form-group">
                    <label>Budget Amount (SAR) <span style="color: var(--horizon-error);">*</span></label>
                    <input type="number" name="budget_amount" id="budget_amount" placeholder="Enter budget amount" step="0.01" min="0" required>
                    <small style="color: var(--horizon-light-text); font-size: 11px;">
                        <i class="fa fa-info-circle"></i> Enter the total budget allocation for the selected period
                    </small>
                </div>

                <div class="form-group">
                    <label>Description (Optional)</label>
                    <textarea name="description" id="description" rows="3" placeholder="Add notes or description for this budget..."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-outline" onclick="closeBudgetModal()">Cancel</button>
            <button class="btn-primary" onclick="saveBudget()">
                <i class="fa fa-save"></i> Save Budget
            </button>
        </div>
    </div>
</div>

<script>
// Period selection
function selectPeriod(period) {
    // Update button states
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector('[data-period="' + period + '"]').classList.add('active');
    
    // Update hidden input
    document.getElementById('period_type').value = period;
    
    // Show/hide period selection fields
    document.getElementById('period_selection_monthly').style.display = period === 'monthly' ? 'block' : 'none';
    document.getElementById('period_selection_quarterly').style.display = period === 'quarterly' ? 'block' : 'none';
    document.getElementById('period_selection_yearly').style.display = period === 'yearly' ? 'block' : 'none';
}

// Open budget modal
function openBudgetModal() {
    document.getElementById('budgetModal').classList.add('active');
    document.getElementById('budgetForm').reset();
    selectPeriod('monthly');
}

// Close budget modal
function closeBudgetModal() {
    document.getElementById('budgetModal').classList.remove('active');
}

// Save budget
function saveBudget() {
    const form = document.getElementById('budgetForm');
    if (form.checkValidity()) {
        form.submit();
    } else {
        alert('Please fill in all required fields');
    }
}

// Edit budget
function editBudget(budget) {
    alert('Edit functionality will be implemented soon');
    console.log('Edit budget:', budget);
}

// View details
function viewDetails(id) {
    window.location.href = '<?php echo admin_url('loyalty/budget_allocation/'); ?>' + id;
}

// Archive budget
function archiveBudget(id) {
    if (confirm('Are you sure you want to archive this budget definition?')) {
        window.location.href = '<?php echo admin_url('loyalty/archive_budget/'); ?>' + id;
    }
}

// Close modal when clicking outside
document.getElementById('budgetModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBudgetModal();
    }
});
</script>
