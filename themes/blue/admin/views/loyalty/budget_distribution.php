<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header mb-4" style="display: flex; justify-content: space-between; align-items: center; padding: 2rem 0;">
        <div class="page-header-left" style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;">
                <svg style="width: 30px; height: 30px;" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
            </div>
            <div>
                <h1 style="margin: 0; font-size: 1.75rem; font-weight: 700; color: #1f2937;"><?php echo lang('Budget Distribution'); ?></h1>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #6b7280;"><?php echo lang('Allocate and distribute budgets across pharmacies'); ?></p>
            </div>
        </div>
        <button class="btn btn-primary" onclick="showDistributionModal()">
            <i class="fa fa-plus"></i> <?php echo lang('New Distribution'); ?>
        </button>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Control Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding: 1rem; background: white; border: 1px solid #e5e7eb; border-radius: 12px;">
        <div style="display: flex; gap: 1rem;">
            <div>
                <label style="font-size: 0.875rem; font-weight: 600; color: #6b7280;"><?php echo lang('Allocation'); ?></label>
                <select id="allocationFilter" class="form-control form-control-sm" onchange="filterByAllocation(this.value)" style="width: 200px;">
                    <option value=""><?php echo lang('Select Allocation'); ?></option>
                </select>
            </div>
            <div>
                <label style="font-size: 0.875rem; font-weight: 600; color: #6b7280;"><?php echo lang('Pharmacy Group'); ?></label>
                <select id="pharmacyGroupFilter" class="form-control form-control-sm" onchange="filterByGroup(this.value)" style="width: 200px;">
                    <option value=""><?php echo lang('All Groups'); ?></option>
                </select>
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fa fa-refresh"></i> <?php echo lang('Refresh'); ?>
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="exportDistributions()">
                <i class="fa fa-download"></i> <?php echo lang('Export'); ?>
            </button>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; display: flex; gap: 1rem;">
                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                    <svg style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                </div>
                <div>
                    <h6 style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;"><?php echo lang('Total Allocated'); ?></h6>
                    <div style="font-size: 1.75rem; font-weight: 700; color: #1f2937; margin: 0.5rem 0;" id="totalAllocated">0</div>
                    <small style="color: #6b7280;"><?php echo lang('Across all pharmacies'); ?></small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; display: flex; gap: 1rem;">
                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                    <svg style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                </div>
                <div>
                    <h6 style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;"><?php echo lang('Unallocated'); ?></h6>
                    <div style="font-size: 1.75rem; font-weight: 700; color: #1f2937; margin: 0.5rem 0;" id="unallocatedBudget">0</div>
                    <small style="color: #6b7280;"><span id="unallocatedPercent">0%</span></small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; display: flex; gap: 1rem;">
                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                    <svg style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                </div>
                <div>
                    <h6 style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;"><?php echo lang('Pharmacies'); ?></h6>
                    <div style="font-size: 1.75rem; font-weight: 700; color: #1f2937; margin: 0.5rem 0;" id="pharmaciesCount">0</div>
                    <small style="color: #6b7280;"><?php echo lang('With allocations'); ?></small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; display: flex; gap: 1rem;">
                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                    <svg style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                </div>
                <div>
                    <h6 style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;"><?php echo lang('Allocation %'); ?></h6>
                    <div style="font-size: 1.75rem; font-weight: 700; color: #1f2937; margin: 0.5rem 0;" id="allocationPercent">0%</div>
                    <small style="color: #6b7280;"><?php echo lang('Of total budget'); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem;">
                <h6 style="margin: 0 0 1rem 0; font-size: 1rem; font-weight: 600; color: #1f2937;"><?php echo lang('Distribution by Pharmacy'); ?></h6>
                <div id="distributionPieChart" style="height: 350px; display: flex; align-items: center; justify-content: center; color: #6b7280;">
                    <small><?php echo lang('Loading chart...'); ?></small>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem;">
                <h6 style="margin: 0 0 1rem 0; font-size: 1rem; font-weight: 600; color: #1f2937;"><?php echo lang('Budget Allocation Progress'); ?></h6>
                <div id="allocationProgressChart" style="height: 350px; display: flex; align-items: center; justify-content: center; color: #6b7280;">
                    <small><?php echo lang('Loading chart...'); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Pharmacy Allocations Table -->
    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <h6 style="margin: 0; font-size: 1rem; font-weight: 600; color: #1f2937;"><?php echo lang('Pharmacy Allocations'); ?></h6>
            <a href="<?php echo admin_url('loyalty/budget_definition'); ?>" style="font-size: 0.875rem; color: #667eea;">
                <?php echo lang('Manage Allocations'); ?> <i class="fa fa-arrow-right"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size: 0.875rem;">
                <thead>
                    <tr style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white;">
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Pharmacy'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Group'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Allocated'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Spent'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Remaining'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Usage'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Status'); ?></th>
                    </tr>
                </thead>
                <tbody id="pharmacyTableBody">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                            <small><?php echo lang('Loading pharmacy data...'); ?></small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Distribution Modal -->
<div class="modal fade" id="distributionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo lang('New Budget Distribution'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="distributionForm">
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('Allocation'); ?></label>
                        <select id="formAllocation" class="form-control" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('Pharmacy Group'); ?></label>
                        <select id="formPharmacyGroup" class="form-control" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('Pharmacy'); ?></label>
                        <select id="formPharmacy" class="form-control" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('Budget Amount'); ?></label>
                        <div class="input-group">
                            <input type="number" id="formBudgetAmount" class="form-control" min="0" required>
                            <span class="input-group-text">SAR</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo lang('Cancel'); ?></button>
                <button type="button" class="btn btn-primary" onclick="saveDistribution()"><?php echo lang('Save'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDistributionData();
});

function loadDistributionData() {
    fetch('<?php echo admin_url("loyalty/get_summary"); ?>')
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                document.getElementById('totalAllocated').textContent = formatCurrency(d.totalAllocated || 0);
                document.getElementById('unallocatedBudget').textContent = formatCurrency(d.unallocatedBudget || 0);
                document.getElementById('unallocatedPercent').textContent = Math.round((d.unallocatedBudget / d.totalBudget) * 100) + '%';
                document.getElementById('pharmaciesCount').textContent = d.pharmaciesCount || 0;
                document.getElementById('allocationPercent').textContent = Math.round((d.totalAllocated / d.totalBudget) * 100) || 0;
            }
        });

    fetch('<?php echo admin_url("loyalty/get_all_pharmacies"); ?>')
        .then(r => r.json())
        .then(d => {
            if (d.success && d.pharmacies) {
                const tbody = document.getElementById('pharmacyTableBody');
                tbody.innerHTML = d.pharmacies.map(p => {
                    const usage = Math.min(100, Math.round((p.spent / p.allocated) * 100));
                    return '<tr><td style="padding: 1rem 1.5rem;">' + p.name + '</td><td style="padding: 1rem 1.5rem;">' + (p.group_name || 'N/A') + '</td><td style="padding: 1rem 1.5rem;">' + formatCurrency(p.allocated || 0) + '</td><td style="padding: 1rem 1.5rem;">' + formatCurrency(p.spent || 0) + '</td><td style="padding: 1rem 1.5rem;">' + formatCurrency((p.allocated - p.spent) || 0) + '</td><td style="padding: 1rem 1.5rem;">' + usage + '%</td><td style="padding: 1rem 1.5rem;"><span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; background: ' + (p.status === 'Active' ? '#d1fae5' : '#fee2e2') + '; color: ' + (p.status === 'Active' ? '#065f46' : '#991b1b') + ';">' + (p.status || 'Active') + '</span></td></tr>';
                }).join('');
            }
        });
}

function formatCurrency(n) {
    return new Intl.NumberFormat('en-SA', {style: 'currency', currency: 'SAR', minimumFractionDigits: 0}).format(n);
}

function showDistributionModal() {
    new bootstrap.Modal(document.getElementById('distributionModal')).show();
}

function saveDistribution() {
    const data = {
        allocation_id: document.getElementById('formAllocation').value,
        pharmacy_group_id: document.getElementById('formPharmacyGroup').value,
        pharmacy_id: document.getElementById('formPharmacy').value,
        amount: document.getElementById('formBudgetAmount').value
    };
    fetch('<?php echo admin_url("loyalty/save_distribution"); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            bootstrap.Modal.getInstance(document.getElementById('distributionModal')).hide();
            loadDistributionData();
        }
    });
}

function filterByAllocation(v) {}
function filterByGroup(v) {}
function exportDistributions() {}
</script>
