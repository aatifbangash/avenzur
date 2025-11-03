<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header mb-4" style="display: flex; justify-content: space-between; align-items: center; padding: 2rem 0;">
        <div class="page-header-left" style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;">
                <svg style="width: 30px; height: 30px;" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                </svg>
            </div>
            <div>
                <h1 style="margin: 0; font-size: 1.75rem; font-weight: 700; color: #1f2937;"><?php echo lang('Loyalty Rules'); ?></h1>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #6b7280;"><?php echo lang('Create and manage discount/promotion rules'); ?></p>
            </div>
        </div>
        <button class="btn btn-primary" onclick="showRuleModal()">
            <i class="fa fa-plus"></i> <?php echo lang('New Rule'); ?>
        </button>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Filter Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding: 1rem; background: white; border: 1px solid #e5e7eb; border-radius: 12px;">
        <div style="display: flex; gap: 1rem;">
            <input type="text" id="searchRules" class="form-control" placeholder="<?php echo lang('Search rules...'); ?>" onkeyup="filterRules(this.value)" style="min-width: 250px;">
            <select id="ruleTypeFilter" class="form-control form-control-sm" onchange="filterRules()" style="width: 180px;">
                <option value=""><?php echo lang('All Types'); ?></option>
            </select>
            <select id="statusFilter" class="form-control form-control-sm" onchange="filterRules()" style="width: 150px;">
                <option value=""><?php echo lang('All Status'); ?></option>
                <option value="1"><?php echo lang('Active'); ?></option>
                <option value="0"><?php echo lang('Inactive'); ?></option>
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fa fa-refresh"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="exportRules()">
                <i class="fa fa-download"></i>
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; display: flex; gap: 1rem;">
                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                    <i class="fa fa-list" style="font-size: 24px;"></i>
                </div>
                <div>
                    <h6 style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;"><?php echo lang('Total Rules'); ?></h6>
                    <div style="font-size: 1.75rem; font-weight: 700; color: #1f2937; margin: 0.5rem 0;" id="totalRules">0</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; display: flex; gap: 1rem;">
                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                    <i class="fa fa-check" style="font-size: 24px;"></i>
                </div>
                <div>
                    <h6 style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;"><?php echo lang('Active'); ?></h6>
                    <div style="font-size: 1.75rem; font-weight: 700; color: #1f2937; margin: 0.5rem 0;" id="activeRules">0</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; display: flex; gap: 1rem;">
                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                    <i class="fa fa-calendar" style="font-size: 24px;"></i>
                </div>
                <div>
                    <h6 style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;"><?php echo lang('Scheduled'); ?></h6>
                    <div style="font-size: 1.75rem; font-weight: 700; color: #1f2937; margin: 0.5rem 0;" id="scheduledRules">0</div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; display: flex; gap: 1rem;">
                <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0;">
                    <i class="fa fa-clock-o" style="font-size: 24px;"></i>
                </div>
                <div>
                    <h6 style="font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; margin: 0;"><?php echo lang('Expired'); ?></h6>
                    <div style="font-size: 1.75rem; font-weight: 700; color: #1f2937; margin: 0.5rem 0;" id="expiredRules">0</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rules Table -->
    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb;">
            <h6 style="margin: 0; font-size: 1rem; font-weight: 600; color: #1f2937;"><?php echo lang('Rules List'); ?></h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size: 0.875rem;">
                <thead>
                    <tr style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); color: white;">
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Rule Name'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Type'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Priority'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Status'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Start Date'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('End Date'); ?></th>
                        <th style="padding: 1rem 1.5rem; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.75rem;"><?php echo lang('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody id="rulesTableBody">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                            <small><?php echo lang('Loading rules...'); ?></small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Rule Modal -->
<div class="modal fade" id="ruleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ruleModalTitle"><?php echo lang('New Rule'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="ruleForm">
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('Rule Name'); ?></label>
                        <input type="text" id="ruleName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('Rule Type'); ?></label>
                        <select id="ruleType" class="form-control" required></select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo lang('Start Date'); ?></label>
                            <input type="date" id="startDate" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo lang('End Date'); ?></label>
                            <input type="date" id="endDate" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo lang('Priority'); ?></label>
                            <select id="priority" class="form-control" required>
                                <option value="low"><?php echo lang('Low'); ?></option>
                                <option value="medium" selected><?php echo lang('Medium'); ?></option>
                                <option value="high"><?php echo lang('High'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><?php echo lang('Status'); ?></label>
                            <select id="ruleStatus" class="form-control" required>
                                <option value="1"><?php echo lang('Active'); ?></option>
                                <option value="0"><?php echo lang('Inactive'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo lang('Description'); ?></label>
                        <textarea id="ruleDescription" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo lang('Cancel'); ?></button>
                <button type="button" class="btn btn-primary" onclick="saveRule()"><?php echo lang('Save Rule'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadRulesData();
});

function loadRulesData() {
    fetch('<?php echo admin_url("loyalty/get_rules"); ?>')
        .then(r => r.json())
        .then(d => {
            if (d.success && d.rules) {
                renderRulesTable(d.rules);
                updateSummaryCards(d.rules);
            }
        })
        .catch(e => console.error('Error:', e));
}

function renderRulesTable(rules) {
    const tbody = document.getElementById('rulesTableBody');
    if (!rules || rules.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;"><small><?php echo lang("No rules found"); ?></small></td></tr>';
        return;
    }

    tbody.innerHTML = rules.map(r => {
        const statusBadge = r.status ? '<span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; background: #d1fae5; color: #065f46;"><?php echo lang("Active"); ?></span>' : '<span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; background: #fee2e2; color: #991b1b;"><?php echo lang("Inactive"); ?></span>';
        return '<tr><td style="padding: 1rem 1.5rem;"><strong>' + r.name + '</strong></td><td style="padding: 1rem 1.5rem;">' + r.type + '</td><td style="padding: 1rem 1.5rem;"><span style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; background: ' + (r.priority === 'high' ? '#fecaca' : r.priority === 'medium' ? '#fde047' : '#d1d5db') + ';">' + r.priority + '</span></td><td style="padding: 1rem 1.5rem;">' + statusBadge + '</td><td style="padding: 1rem 1.5rem;">' + r.start_date + '</td><td style="padding: 1rem 1.5rem;">' + r.end_date + '</td><td style="padding: 1rem 1.5rem;"><button class="btn btn-sm btn-outline-secondary" onclick="editRule(' + r.id + ')"><i class="fa fa-edit"></i></button></td></tr>';
    }).join('');
}

function updateSummaryCards(rules) {
    const total = rules.length;
    const active = rules.filter(r => r.status).length;
    const scheduled = rules.filter(r => !r.started && r.status).length;
    const expired = rules.filter(r => r.expired).length;

    document.getElementById('totalRules').textContent = total;
    document.getElementById('activeRules').textContent = active;
    document.getElementById('scheduledRules').textContent = scheduled;
    document.getElementById('expiredRules').textContent = expired;
}

function showRuleModal() {
    document.getElementById('ruleModalTitle').textContent = '<?php echo lang("New Rule"); ?>';
    document.getElementById('ruleForm').reset();
    new bootstrap.Modal(document.getElementById('ruleModal')).show();
}

function editRule(id) {
    showRuleModal();
}

function saveRule() {
    const data = {
        name: document.getElementById('ruleName').value,
        type: document.getElementById('ruleType').value,
        start_date: document.getElementById('startDate').value,
        end_date: document.getElementById('endDate').value,
        priority: document.getElementById('priority').value,
        status: document.getElementById('ruleStatus').value,
        description: document.getElementById('ruleDescription').value
    };

    fetch('<?php echo admin_url("loyalty/save_rule"); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            bootstrap.Modal.getInstance(document.getElementById('ruleModal')).hide();
            loadRulesData();
        }
    });
}

function filterRules(q) {
    loadRulesData();
}

function exportRules() {
    console.log('Exporting rules...');
}
</script>
