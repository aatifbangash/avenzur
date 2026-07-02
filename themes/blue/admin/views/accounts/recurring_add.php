<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
.select2-container--default.select2-container--focus,
.select2-selection.select2-container--focus,
.select2-container--default:focus,
.select2-selection:focus,
.select2-container--default:active,
.select2-selection:active { outline: none }
.select2-container--default .select2-selection--single,
.select2-selection .select2-selection--single {
    border: 1px solid #d2d6de; border-radius: 0; padding: 6px 12px; height: 34px;
    background-color: #fff;
}
.select2-container--default.select2-container--open { border-color: #3c8dbc }
.select2-dropdown { border: 1px solid #d2d6de; border-radius: 0; background-color: #fff; }
.select2-results { background-color: #fff; }
.select2-results__options { background-color: #fff; }
.select2-results__option { background-color: #fff; }
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #3c8dbc; color: #fff;
}
.select2-results__option { padding: 6px 12px; user-select: none; -webkit-user-select: none }
.select2-container .select2-selection--single .select2-selection__rendered {
    padding-left: 0; padding-right: 0; height: auto; margin-top: -4px; background-color: #fff;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 28px; right: 3px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow b { margin-top: 0 }
.select2-container--default .select2-results__option[aria-disabled=true] { color: #999 }
.select2-container--default .select2-results__option[aria-selected=true] { background-color: #ddd; color: #444 }
.select2-container--default .select2-results>.select2-results__options { max-height: 220px; overflow-y: auto; }
.accounts-table { width: 100%; border-collapse: collapse; margin-bottom: 0 }
.accounts-table th { padding: 8px 10px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px }
.accounts-table td { padding: 6px 8px; vertical-align: middle }
.accounts-table tbody tr:hover { background: rgba(0,0,0,.03) }
.debit-section .panel-heading  { background: #d4edda; color: #155724; border-color: #c3e6cb }
.debit-section .panel          { border-color: #c3e6cb }
.debit-section .accounts-table th { background: #e9f7ec; color: #155724 }
.debit-section .btn-add-row    { background: #28a745; color: #fff; border: none }
.debit-section .btn-add-row:hover { background: #218838 }
.credit-section .panel-heading { background: #fff3cd; color: #856404; border-color: #ffecb5 }
.credit-section .panel         { border-color: #ffecb5 }
.credit-section .accounts-table th { background: #fffaec; color: #7a5a00 }
.credit-section .btn-add-row   { background: #ffc107; color: #333; border: none }
.credit-section .btn-add-row:hover { background: #e0a800 }
.btn-remove-row { background: none; border: none; color: #dc3545; font-size: 16px; padding: 2px 6px; cursor: pointer }
.btn-remove-row:hover { color: #a71d2a }
.row-number { display: inline-block; min-width: 20px; text-align: center; font-weight: 600; color: #888 }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="box-title"><i class="fa fa-plus-circle"></i> Add Recurring JV Template</h2>
        <div class="box-tools pull-right">
            <a href="<?php echo admin_url('entries/recurring_index'); ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <div class="box-body">

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php echo form_open(admin_url('entries/recurring_add'), ['id' => 'jvTemplateForm']); ?>

        <!-- SECTION 1: Template Info -->
        <div class="panel panel-default">
            <div class="panel-heading"><strong><i class="fa fa-info-circle"></i> Template Details</strong></div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Template Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="e.g. Monthly Depreciation JV"
                                   value="<?php echo set_value('name'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Voucher Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-control skip" required>
                                <option value="">-- Select --</option>
                                <option value="depreciation"   <?php echo set_select('type', 'depreciation'); ?>>Depreciation JV</option>
                                <option value="amortization"   <?php echo set_select('type', 'amortization'); ?>>Amortization JV</option>
                                <option value="salary"        <?php echo set_select('type', 'salary'); ?>>Salary JV</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Posting Frequency</label>
                            <select name="period_type" class="form-control skip">
                                <option value="monthly"   <?php echo set_select('period_type', 'monthly', true); ?>>Monthly</option>
                                <option value="quarterly" <?php echo set_select('period_type', 'quarterly'); ?>>Quarterly</option>
                                <option value="annual"    <?php echo set_select('period_type', 'annual'); ?>>Annual</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!--<div class="col-md-4">
                        <div class="form-group">
                            <label>Entry Type</label>
                            <select name="entrytype_id" class="form-control skip">
                                <option value="">-- Default --</option>
                                <?php foreach ($entrytypes as $et): ?>
                                    <option value="<?php echo $et['id']; ?>" <?php echo set_select('entrytype_id', $et['id']); ?>>
                                        <?php echo html_escape($et['label'] ?? $et['name'] ?? $et['id']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>-->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Default Narration</label>
                            <input type="text" name="narration" class="form-control"
                                   placeholder="e.g. Monthly Depreciation Expense"
                                   value="<?php echo set_value('narration'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control"
                                   placeholder="Optional description"
                                   value="<?php echo set_value('description'); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Debit Accounts (green) -->
        <div class="debit-section">
            <div class="panel">
                <div class="panel-heading">
                    <strong><i class="fa fa-arrow-circle-right"></i> Debit Accounts</strong>
                    <small class="text-muted" style="margin-left:8px;">Expense / Asset accounts to be debited each posting</small>
                    <div class="pull-right">
                        <button type="button" class="btn btn-sm btn-add-row" id="addDebitRow">
                            <i class="fa fa-plus"></i> Add Account
                        </button>
                    </div>
                </div>
                <div class="panel-body" style="padding:0">
                    <table class="accounts-table" id="debitTable">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th>Account</th>
                                <th width="220">Notes</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="debitBody">
                            <tr class="debit-row" data-index="0">
                                <td><span class="row-number">1</span></td>
                                <td>
                                    <select name="debit_ledger_id[]" class="form-control ledger-select" required>
                                        <option value="">-- Select Account --</option>
                                        <?php foreach ($ledger_options as $l): ?>
                                            <option value="<?php echo $l['id']; ?>">
                                                <?php echo html_escape($l['code'] . ' - ' . $l['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="text" name="debit_notes[]" class="form-control" placeholder="Optional notes"></td>
                                <td><button type="button" class="btn-remove-row" title="Remove"><i class="fa fa-times-circle"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Credit Accounts (yellow) -->
        <div class="credit-section" style="margin-top:16px;">
            <div class="panel">
                <div class="panel-heading">
                    <strong><i class="fa fa-arrow-circle-left"></i> Credit Accounts</strong>
                    <small class="text-muted" style="margin-left:8px;">Accumulated / Liability accounts to be credited each posting</small>
                    <div class="pull-right">
                        <button type="button" class="btn btn-sm btn-add-row" id="addCreditRow">
                            <i class="fa fa-plus"></i> Add Account
                        </button>
                    </div>
                </div>
                <div class="panel-body" style="padding:0">
                    <table class="accounts-table" id="creditTable">
                        <thead>
                            <tr>
                                <th width="40">#</th>
                                <th>Account</th>
                                <th width="220">Notes</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="creditBody">
                            <tr class="credit-row" data-index="0">
                                <td><span class="row-number">1</span></td>
                                <td>
                                    <select name="credit_ledger_id[]" class="form-control ledger-select" required>
                                        <option value="">-- Select Account --</option>
                                        <?php foreach ($ledger_options as $l): ?>
                                            <option value="<?php echo $l['id']; ?>">
                                                <?php echo html_escape($l['code'] . ' - ' . $l['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="text" name="credit_notes[]" class="form-control" placeholder="Optional notes"></td>
                                <td><button type="button" class="btn-remove-row" title="Remove"><i class="fa fa-times-circle"></i></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-group" style="margin-top:20px;">
            <button type="submit" class="btn btn-primary btn-lg" id="saveBtn">
                <i class="fa fa-save"></i> Save JV Template
            </button>
            <a href="<?php echo admin_url('entries/recurring_index'); ?>" class="btn btn-default btn-lg">
                <i class="fa fa-times"></i> Cancel
            </a>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<!-- Row templates for JS cloning -->
<script type="text/x-template" id="debitRowTpl">
<tr class="debit-row">
    <td><span class="row-number"></span></td>
    <td>
        <select name="debit_ledger_id[]" class="form-control ledger-select" required>
            <option value="">-- Select Account --</option>
            <?php foreach ($ledger_options as $l): ?>
                <option value="<?php echo $l['id']; ?>"><?php echo html_escape($l['code'] . ' - ' . $l['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </td>
    <td><input type="text" name="debit_notes[]" class="form-control" placeholder="Optional notes"></td>
    <td><button type="button" class="btn-remove-row" title="Remove"><i class="fa fa-times-circle"></i></button></td>
</tr>
</script>

<script type="text/x-template" id="creditRowTpl">
<tr class="credit-row">
    <td><span class="row-number"></span></td>
    <td>
        <select name="credit_ledger_id[]" class="form-control ledger-select" required>
            <option value="">-- Select Account --</option>
            <?php foreach ($ledger_options as $l): ?>
                <option value="<?php echo $l['id']; ?>"><?php echo html_escape($l['code'] . ' - ' . $l['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </td>
    <td><input type="text" name="credit_notes[]" class="form-control" placeholder="Optional notes"></td>
    <td><button type="button" class="btn-remove-row" title="Remove"><i class="fa fa-times-circle"></i></button></td>
</tr>
</script>

<script>
$(function () {
    function initSelect2(ctx) {
        $(ctx).find('.ledger-select').each(function () {
            if (!$(this).data('select2')) {
                $(this).select2({ placeholder: '-- Select Account --', allowClear: true, width: '100%' });
            }
        });
    }
    initSelect2('#debitBody');
    initSelect2('#creditBody');

    function renumber(tbody, rowClass) {
        $(tbody).find('.' + rowClass).each(function (i) {
            $(this).find('.row-number').text(i + 1);
        });
    }

    function addRow(bodyId, rowClass, tplId) {
        var tpl = $(tplId).html();
        var $row = $(tpl);
        $('#' + bodyId).append($row);
        initSelect2($row);
        renumber('#' + bodyId, rowClass);
    }

    $('#addDebitRow').on('click',  function () { addRow('debitBody',  'debit-row',  '#debitRowTpl');  });
    $('#addCreditRow').on('click', function () { addRow('creditBody', 'credit-row', '#creditRowTpl'); });

    $(document).on('click', '.btn-remove-row', function () {
        var $row   = $(this).closest('tr');
        var $tbody = $row.closest('tbody');
        var cls    = $row.hasClass('debit-row') ? 'debit-row' : 'credit-row';
        if ($tbody.find('.' + cls).length <= 1) {
            alert('At least one account is required on each side.');
            return;
        }
        $row.remove();
        renumber($tbody, cls);
    });

    $('#jvTemplateForm').on('submit', function (e) {
        var debitOk  = $('#debitBody  select').filter(function () { return !!$(this).val(); }).length > 0;
        var creditOk = $('#creditBody select').filter(function () { return !!$(this).val(); }).length > 0;
        if (!debitOk)  { e.preventDefault(); alert('Please add at least one Debit account.'); return; }
        if (!creditOk) { e.preventDefault(); alert('Please add at least one Credit account.'); return; }
        // Disable empty (un-selected) rows so they don't post as empty
        $('select.ledger-select').each(function () {
            if (!$(this).val()) $(this).prop('disabled', true);
        });
    });
});
</script>
