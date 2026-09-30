<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
// Prepare JS-compatible arrays (all from stdClass objects)
$ledgers_for_js = [];
foreach ($ledger_options as $row) {
    $ledgers_for_js[] = ['id' => (int)$row['id'], 'name' => $row['code'] . ' - ' . $row['name']];
}
$employees_for_js = [];
foreach ((array)($employees ?: []) as $e) {
    $employees_for_js[] = ['id' => (int)$e->id, 'name' => (string)$e->name];
}
$departments_for_js = [];
foreach ((array)($departments ?: []) as $d) {
    $departments_for_js[] = ['id' => (int)$d->id, 'name' => (string)$d->name];
}
?>
<style>
.salary-table th { background-color: #f5f5f5; font-weight: bold; vertical-align: middle; }
.salary-table td { vertical-align: middle; }
.select2-container--default.select2-container--focus,
.select2-selection.select2-container--focus,
.select2-container--default:focus,
.select2-selection:focus,
.select2-container--default:active,
.select2-selection:active { outline: none }
.select2-container--default .select2-selection--single,
.select2-selection .select2-selection--single {
    border: 1px solid #d2d6de; border-radius: 0; padding: 6px 12px; height: 34px
}
.select2-container--default.select2-container--open { border-color: #3c8dbc }
.select2-dropdown { border: 1px solid #d2d6de; border-radius: 0; background-color: #fff; }
.select2-results { background-color: #fff; }
.select2-results__options { background-color: #fff; }
.select2-results__option { background-color: #fff; }
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #3c8dbc; color: white
}
.select2-results__option { padding: 6px 12px; user-select: none; -webkit-user-select: none }
.select2-container .select2-selection--single .select2-selection__rendered {
    padding-left: 0; padding-right: 0; height: auto; margin-top: -4px
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 28px; right: 3px
}
.select2-container--default .select2-selection--single .select2-selection__arrow b { margin-top: 0 }
.select2-container--default .select2-results__option[aria-disabled=true] { color: #999 }
.select2-container--default .select2-results__option[aria-selected=true] { background-color: #ddd; color: #444 }
.select2-container--default .select2-results>.select2-results__options { max-height: 200px; overflow-y: auto; }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="box-title"><i class="fa fa-users"></i> New Salary Run</h2>
    </div>

    <div class="box-body">

        <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php echo form_open(admin_url('entries/salary_add'), ['role' => 'form', 'id' => 'salaryForm']); ?>

        <!-- ── Run Header ── -->
        <div class="panel panel-default">
            <div class="panel-heading"><strong>Run Details</strong></div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Run Name <span class="text-danger">*</span></label>
                            <input type="text" name="run_name" class="form-control"
                                   placeholder="e.g. February 2025 Payroll"
                                   value="<?php echo set_value('run_name'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Month <span class="text-danger">*</span></label>
                            <select name="period_month" class="form-control skip" required>
                                <?php
                                    $months = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
                                    for ($m = 1; $m <= 12; $m++):
                                ?>
                                    <option value="<?php echo $m; ?>" <?php echo set_select('period_month', $m); ?>>
                                        <?php echo $months[$m]; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Year <span class="text-danger">*</span></label>
                            <input type="number" name="period_year" class="form-control"
                                   min="2000" max="2099"
                                   value="<?php echo set_value('period_year', date('Y')); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Run Date <span class="text-danger">*</span></label>
                            <input type="text" name="run_date" class="form-control date"
                                   placeholder="<?php echo $this->mAccountSettings->date_format; ?>"
                                   value="<?php echo set_value('run_date', date('d/m/Y')); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Entry Type</label>
                            <select name="entrytype_id" class="form-control skip">
                                <option value="">-- Default --</option>
                                <?php foreach ($entrytypes as $et): ?>
                                    <option value="<?php echo $et['id']; ?>"
                                        <?php echo set_select('entrytype_id', $et['id']); ?>>
                                        <?php echo html_escape($et['label'] ?? $et['name'] ?? $et['id']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control"
                                   placeholder="Optional notes"
                                   value="<?php echo set_value('description'); ?>">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── Employee Rows ── -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>Employee Salary Rows</strong>
                <button type="button" class="btn btn-xs btn-success pull-right" id="addEmployeeRow">
                    <i class="fa fa-plus"></i> Add Employee Row
                </button>
            </div>
            <div class="panel-body">

                <div class="alert alert-info alert-sm" style="padding:6px 10px;margin-bottom:8px;">
                    <i class="fa fa-info-circle"></i>
                    Each row: <strong>Dr</strong> Salary Expense ledger (gross), <strong>Cr</strong> Salary Payable ledger (net after deductions).
                </div>

                <div style="overflow: visible;">
                    <table class="table table-bordered table-condensed" id="employeeRowsTable" style="min-width:1100px;">
                        <thead>
                            <tr>
                                <th style="min-width:130px;">Employee Name <span class="text-danger">*</span></th>
                                <th style="min-width:160px;">Salary Expense Ledger (Dr) <span class="text-danger">*</span></th>
                                <th style="min-width:160px;">Salary Payable Ledger (Cr) <span class="text-danger">*</span></th>
                                <th style="min-width:100px;">Gross <span class="text-danger">*</span></th>
                                <th style="min-width:100px;">Deductions</th>
                                <th style="min-width:100px;">Net</th>
                                <th style="min-width:120px;">Department</th>
                                <th style="min-width:130px;">Narration</th>
                                <th width="40">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody id="employeeRows">
                            <!-- Initial row injected by JS -->
                        </tbody>
                        <tfoot>
                            <tr class="active">
                                <td colspan="3"><strong>Totals</strong></td>
                                <td class="text-right"><strong id="totalGross">0.00</strong></td>
                                <td class="text-right text-danger"><strong id="totalDeductions">0.00</strong></td>
                                <td class="text-right"><strong id="totalNet">0.00</strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>

        <!-- ── Actions ── -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary" id="saveBtn">
                <i class="fa fa-save"></i> Save as Draft
            </button>
            <a href="<?php echo admin_url('entries/salary_index'); ?>" class="btn btn-default">
                <i class="fa fa-times"></i> Cancel
            </a>
        </div>

        <?php echo form_close(); ?>
    </div><!-- /.box-body -->
</div><!-- /.box -->

<script>
(function () {
    var ledgersList   = <?php echo json_encode($ledgers_for_js); ?>;
    var employeesList = <?php echo json_encode($employees_for_js); ?>;
    var deptList      = <?php echo json_encode($departments_for_js); ?>;

    var rowIndex = 0;

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function buildOptions(items, emptyLabel, emptyVal) {
        var html = '<option value="' + emptyVal + '">' + escHtml(emptyLabel) + '</option>';
        for (var i = 0; i < items.length; i++) {
            var item = items[i];
            if (item.disabled) {
                html += '<option disabled>' + escHtml(item.name) + '</option>';
            } else {
                html += '<option value="' + item.id + '" data-name="' + escHtml(item.name) + '">' + escHtml(item.name) + '</option>';
            }
        }
        return html;
    }

    function addRow() {
        var empOpts    = buildOptions(employeesList, '-- Select Employee --', '');
        var ledgerOpts = buildOptions(ledgersList,   '-- Select Ledger --',   '');
        var deptOpts   = buildOptions(deptList,      '-- None --',            '0');

        var idx = rowIndex;
        var row = '<tr class="employee-row">'
            + '<td>'
            +   '<input type="hidden" name="rows[' + idx + '][employee_id]" class="emp-id-field" value="">'
            +   '<select class="form-control input-sm skip emp-select" data-idx="' + idx + '" required>'
            +     empOpts
            +   '</select>'
            +   '<input type="hidden" name="rows[' + idx + '][employee_name]" class="emp-name-field" value="">'
            + '</td>'
            + '<td>'
            +   '<select name="rows[' + idx + '][ledger_salary_exp_id]" class="form-control input-sm ledger-sel" required>'
            +     ledgerOpts
            +   '</select>'
            + '</td>'
            + '<td>'
            +   '<select name="rows[' + idx + '][ledger_payable_id]" class="form-control input-sm ledger-sel" required>'
            +     ledgerOpts
            +   '</select>'
            + '</td>'
            + '<td>'
            +   '<input type="number" name="rows[' + idx + '][gross_amount]" class="form-control input-sm gross-field text-right" step="0.01" min="0" placeholder="0.00" required>'
            + '</td>'
            + '<td>'
            +   '<input type="number" name="rows[' + idx + '][deductions]" class="form-control input-sm deductions-field text-right" step="0.01" min="0" placeholder="0.00" value="0">'
            + '</td>'
            + '<td>'
            +   '<input type="number" name="rows[' + idx + '][net_amount]" class="form-control input-sm net-field text-right" step="0.01" readonly placeholder="0.00">'
            + '</td>'
            + '<td>'
            +   '<select name="rows[' + idx + '][department_id]" class="form-control input-sm skip">'
            +     deptOpts
            +   '</select>'
            + '</td>'
            + '<td>'
            +   '<input type="text" name="rows[' + idx + '][narration]" class="form-control input-sm" placeholder="Optional">'
            + '</td>'
            + '<td>'
            +   '<button type="button" class="btn btn-xs btn-danger remove-row"><i class="fa fa-trash"></i></button>'
            + '</td>'
            + '</tr>';

        $('#employeeRows').append(row);

        // Init select2 on the ledger dropdowns just added (core.js won't touch dynamic elements)
        $('#employeeRows tr:last').find('.ledger-sel').select2({ minimumResultsForSearch: 7, width: '100%' });

        // Initialize select2 on newly added row selects
        // (no select2 — using native Bootstrap form-control to avoid v3/v4 CSS conflict)

        // Keep hidden employee_id and employee_name in sync
        $('#employeeRows tr:last .emp-select').on('change', function () {
            var $tr = $(this).closest('tr');
            $tr.find('.emp-id-field').val($(this).val());
            $tr.find('.emp-name-field').val($(this).find('option:selected').data('name') || '');
        });

        rowIndex++;
        recalcTotals();
    }

    function recalcTotals() {
        var gross = 0, deductions = 0, net = 0;
        $('.employee-row').each(function () {
            var g = parseFloat($(this).find('.gross-field').val()) || 0;
            var d = parseFloat($(this).find('.deductions-field').val()) || 0;
            var n = g - d;
            $(this).find('.net-field').val(n.toFixed(2));
            gross += g; deductions += d; net += n;
        });
        $('#totalGross').text(gross.toFixed(2));
        $('#totalDeductions').text(deductions.toFixed(2));
        $('#totalNet').text(net.toFixed(2));
    }

    $(function () {
        // Static header selects have class 'skip' so core.js ignores them — no select2 needed

        addRow();

        $('#addEmployeeRow').on('click', function () { addRow(); });

        $(document).on('input', '.gross-field, .deductions-field', function () {
            recalcTotals();
        });

        $(document).on('click', '.remove-row', function () {
            if ($('.employee-row').length <= 1) {
                alert('At least one employee row is required.');
                return;
            }
            $(this).closest('.employee-row').remove();
            recalcTotals();
        });

        $('#salaryForm').on('submit', function () {
            var hasRows = false;
            $('.employee-row').each(function () {
                var empId  = $(this).find('.emp-id-field').val();
                var gross  = parseFloat($(this).find('.gross-field').val()) || 0;
                if (empId && gross > 0) { hasRows = true; }
            });
            if (!hasRows) {
                alert('Please add at least one valid employee row with an employee selected and a gross amount.');
                return false;
            }
        });
    });
}());
</script>
