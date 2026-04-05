<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
/* Voucher header */
.voucher-header-box { background:#fff; border:1px solid #ddd; border-radius:4px; padding:16px 20px; margin-bottom:20px }
.voucher-header-box .voucher-meta { display:flex; flex-wrap:wrap; gap:24px; align-items:center }
.voucher-header-box .meta-item label { font-size:11px; font-weight:600; text-transform:uppercase; color:#888; margin-bottom:2px; display:block }
.voucher-header-box .meta-item .form-control { min-width:160px }

/* JV entry table matching screenshot layout */
.jv-table { width:100%; border-collapse:collapse; font-size:14px }
.jv-table th { padding:9px 12px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; border:1px solid #ddd }
.jv-table td { padding:7px 10px; border:1px solid #e0e0e0; vertical-align:middle }

/* Debit rows - green background */
.jv-table tr.debit-row { background:#f0faf2 }
.jv-table tr.debit-row td { border-color:#c3e6cb }
.jv-table tr.debit-row:hover { background:#e3f5e8 }

/* Credit rows - amber/orange background */
.jv-table tr.credit-row { background:#fffce8 }
.jv-table tr.credit-row td { border-color:#ffecb5 }
.jv-table tr.credit-row:hover { background:#fff6cc }

/* Amount input cells */
.amount-input { text-align:right; width:140px; font-family:monospace; font-size:14px }
.amount-input:focus { border-color:#3c8dbc; box-shadow:none; outline:none }
.empty-side { color:#ccc; text-align:center; font-size:18px }

/* Totals row */
.jv-table tfoot tr.total-row th { background:#f4f4f4; font-size:14px; padding:10px 12px }
.jv-table tfoot tr.balance-row th { font-size:13px; padding:8px 12px }
.balance-ok   { color:#28a745; font-weight:700 }
.balance-bad  { color:#dc3545; font-weight:700 }

/* Section labels */
.side-label { display:inline-block; padding:3px 10px; border-radius:3px; font-size:12px; font-weight:700; letter-spacing:.3px }
.side-label.debit  { background:#28a745; color:#fff }
.side-label.credit { background:#ffc107; color:#333 }

/* Narration */
.narration-row td { background:#f8f9fa; }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="box-title">
            <i class="fa fa-upload"></i> Post Voucher
            &mdash; <em><?php echo html_escape($schedule['name']); ?></em>
        </h2>
        <div class="box-tools pull-right">
            <a href="<?php echo admin_url('entries/recurring_view/' . $schedule['id']); ?>" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> Back to Template
            </a>
        </div>
    </div>

    <div class="box-body">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($debit_lines) || empty($credit_lines)): ?>
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-circle"></i>
                This template has no <?php echo empty($debit_lines) ? 'debit' : 'credit'; ?> accounts defined.
                <a href="<?php echo admin_url('entries/recurring_index'); ?>">Go back and edit the template.</a>
            </div>
        <?php else: ?>

        <?php echo form_open(admin_url('entries/recurring_post_voucher/' . $schedule['id']), ['id' => 'postVoucherForm']); ?>

        <!-- Voucher Header: Type, Number, Month, Date -->
        <div class="voucher-header-box">
            <div class="voucher-meta">
                <div class="meta-item">
                    <label>Voucher Type</label>
                    <div><strong><?php echo ucfirst($schedule['type']); ?> JV</strong></div>
                </div>
                <div class="meta-item">
                    <label>Entry #</label>
                    <div><strong>#<?php echo $next_entry_num; ?></strong>
                        <small class="text-muted">(auto-assigned)</small>
                    </div>
                </div>
                <div class="meta-item">
                    <label>Month <span class="text-danger">*</span></label>
                    <input type="text" name="voucher_month" id="voucherMonth" class="form-control"
                           placeholder="e.g. Jan 2025"
                           value="<?php echo html_escape($this->input->post('voucher_month') ?: date('M Y')); ?>">
                </div>
                <div class="meta-item">
                    <label>Voucher Date (end of month) <span class="text-danger">*</span></label>
                    <input type="text" name="voucher_date" id="voucherDate" class="form-control date"
                           placeholder="<?php echo $this->mAccountSettings->date_format; ?>"
                           value="<?php echo html_escape($this->input->post('voucher_date') ?: date($this->mAccountSettings->date_format === 'd/m/Y' ? 'd/m/Y' : 'Y-m-d', strtotime('last day of this month'))); ?>"
                           required>
                </div>
                <div class="meta-item" style="flex:1;min-width:220px;">
                    <label>Narration</label>
                    <input type="text" name="narration" class="form-control"
                           placeholder="Optional narration"
                           value="<?php echo html_escape($this->input->post('narration') ?: $schedule['narration']); ?>">
                </div>
            </div>
        </div>

        <!-- JV Entry Table matching screenshot -->
        <div class="table-responsive">
        <table class="jv-table" id="jvTable">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th width="120" class="text-right">Debit</th>
                    <th width="120" class="text-right">Credit</th>
                    <th width="140">Account #</th>
                    <th>Account Name</th>
                    <th width="80"></th>
                </tr>
            </thead>
            <tbody>

                <!-- ===== DEBIT ROWS (green) ===== -->
                <tr>
                    <td colspan="5" style="background:#e9f7ec;padding:5px 10px;border-color:#c3e6cb;">
                        <span class="side-label debit"><i class="fa fa-arrow-circle-right"></i> Debit Entries</span>
                    </td>
                </tr>
                <?php foreach ($debit_lines as $line): ?>
                <tr class="debit-row">
                    <td>
                        <input type="number"
                               name="debit_amount[<?php echo $line['id']; ?>]"
                               class="form-control amount-input debit-amount"
                               step="0.01" min="0" placeholder="0.00"
                               value="<?php echo html_escape($this->input->post('debit_amount[' . $line['id'] . ']') ?: ''); ?>">
                    </td>
                    <td class="empty-side">&mdash;</td>
                    <td><code><?php echo html_escape($line['ledger_code']); ?></code></td>
                    <td><?php echo html_escape($line['ledger_name']); ?>
                        <?php if ($line['notes']): ?>
                            <small class="text-muted"> &bull; <?php echo html_escape($line['notes']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><span class="label label-default">Dr</span></td>
                </tr>
                <?php endforeach; ?>

                <!-- ===== CREDIT ROWS (amber) ===== -->
                <tr>
                    <td colspan="5" style="background:#fffaec;padding:5px 10px;border-color:#ffecb5;">
                        <span class="side-label credit"><i class="fa fa-arrow-circle-left"></i> Credit Entries</span>
                    </td>
                </tr>
                <?php foreach ($credit_lines as $line): ?>
                <tr class="credit-row">
                    <td class="empty-side">&mdash;</td>
                    <td>
                        <input type="number"
                               name="credit_amount[<?php echo $line['id']; ?>]"
                               class="form-control amount-input credit-amount"
                               step="0.01" min="0" placeholder="0.00"
                               value="<?php echo html_escape($this->input->post('credit_amount[' . $line['id'] . ']') ?: ''); ?>">
                    </td>
                    <td><code><?php echo html_escape($line['ledger_code']); ?></code></td>
                    <td><?php echo html_escape($line['ledger_name']); ?>
                        <?php if ($line['notes']): ?>
                            <small class="text-muted"> &bull; <?php echo html_escape($line['notes']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><span class="label label-warning">Cr</span></td>
                </tr>
                <?php endforeach; ?>

            </tbody>
            <tfoot>
                <!-- Running totals -->
                <tr class="total-row">
                    <th class="text-right" id="totalDebitDisplay">0.00</th>
                    <th class="text-right" id="totalCreditDisplay">0.00</th>
                    <th colspan="3" class="text-muted" style="font-weight:400;font-size:12px;">
                        <em>Totals — both sides must match</em>
                    </th>
                </tr>
                <!-- Balance indicator -->
                <tr class="balance-row">
                    <th colspan="5">
                        <span id="balanceIndicator" class="balance-bad">
                            <i class="fa fa-exclamation-circle"></i> Enter amounts above — Debit must equal Credit
                        </span>
                    </th>
                </tr>
            </tfoot>
        </table>
        </div><!-- /.table-responsive -->

        <!-- Actions -->
        <div class="form-group" style="margin-top:20px;">
            <button type="submit" class="btn btn-success btn-lg" id="postBtn" disabled>
                <i class="fa fa-upload"></i> Post Voucher to Journal
            </button>
            <a href="<?php echo admin_url('entries/recurring_view/' . $schedule['id']); ?>" class="btn btn-default btn-lg">
                <i class="fa fa-times"></i> Cancel
            </a>
            <small class="text-muted" style="margin-left:12px;">
                <i class="fa fa-info-circle"></i>
                Voucher #<?php echo $posted_count + 1; ?> for this template &bull;
                Entry will be auto-numbered #<?php echo $next_entry_num; ?>
            </small>
        </div>

        <?php echo form_close(); ?>

        <?php endif; // end check for empty lines ?>

    </div>
</div>

<script>
$(function () {
    // Initialize date picker for voucher date
    if ($.fn.datetimepicker) {
        $('#voucherDate').datetimepicker({ format: 'dd/mm/yyyy', minView: 2, autoclose: true });
    }

    function sumInputs(selector) {
        var total = 0;
        $(selector).each(function () {
            var v = parseFloat($(this).val()) || 0;
            if (v > 0) total += v;
        });
        return total;
    }

    function updateTotals() {
        var dr = sumInputs('.debit-amount');
        var cr = sumInputs('.credit-amount');
        var diff = Math.abs(dr - cr);

        $('#totalDebitDisplay').text(dr.toFixed(2));
        $('#totalCreditDisplay').text(cr.toFixed(2));

        var $ind = $('#balanceIndicator');
        var $btn = $('#postBtn');

        if (dr === 0 && cr === 0) {
            $ind.attr('class', 'balance-bad')
                .html('<i class="fa fa-exclamation-circle"></i> Enter amounts above  Debit must equal Credit');
            $btn.prop('disabled', true);
        } else if (diff < 0.01) {
            $ind.attr('class', 'balance-ok')
                .html('<i class="fa fa-check-circle"></i> Balanced  Total: ' + dr.toFixed(2));
            $btn.prop('disabled', false);
        } else {
            $ind.attr('class', 'balance-bad')
                .html('<i class="fa fa-times-circle"></i> Out of balance by ' + diff.toFixed(2) +
                      '  (Debit: ' + dr.toFixed(2) + ' | Credit: ' + cr.toFixed(2) + ')');
            $btn.prop('disabled', true);
        }
    }

    $(document).on('input change', '.amount-input', updateTotals);
    updateTotals();

    // Normalize voucher_date from dd/mm/yyyy → yyyy-mm-dd before submit
    // so PHP strtotime() can always parse it correctly.
    $('#postVoucherForm').on('submit', function () {
        var dateVal = $.trim($('#voucherDate').val());
        if (dateVal) {
            var m = dateVal.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (m) {
                var iso = m[3] + '-' + ('0' + m[2]).slice(-2) + '-' + ('0' + m[1]).slice(-2);
                $('#voucherDate').val(iso);
            }
        }
    });
});
</script>
