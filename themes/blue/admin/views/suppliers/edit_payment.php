<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
/* Hide supplier hidden input and any associated dropdown */
#supplier {
    display: none !important;
    visibility: hidden !important;
    height: 0 !important;
    width: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    position: absolute !important;
    left: -9999px !important;
}

#invoices-table td, #invoices-table th,
#debitmemo-table td, #debitmemo-table th {
    padding: 3px 6px !important;
    line-height: 1.1 !important;
    vertical-align: middle !important;
    font-size: 12px !important;
}
#invoices-table tbody tr,
#debitmemo-table tbody tr {
    height: 28px !important;
}
#invoices-table .payment-amount {
    height: 24px !important;
    padding: 1px 4px !important;
    font-size: 11px !important;
}
#invoices-table input[type="checkbox"],
#debitmemo-table input[type="checkbox"] {
    margin: 0 !important;
    transform: scale(0.8);
}
#invoices-table thead th,
#debitmemo-table thead th {
    font-size: 11px !important;
    font-weight: bold !important;
    padding: 4px 6px !important;
}
#invoices-table tfoot td,
#debitmemo-table tfoot td {
    padding: 3px 6px !important;
    font-size: 12px !important;
    vertical-align: middle !important;
}
#serviceinvoice-table td, #serviceinvoice-table th {
    padding: 3px 6px !important;
    line-height: 1.1 !important;
    vertical-align: middle !important;
    font-size: 12px !important;
}
#serviceinvoice-table tbody tr {
    height: 28px !important;
}
#serviceinvoice-table input[type="checkbox"] {
    margin: 0 !important;
    transform: scale(0.8);
}
#serviceinvoice-table thead th {
    font-size: 11px !important;
    font-weight: bold !important;
    padding: 4px 6px !important;
}
#serviceinvoice-table tfoot td {
    padding: 3px 6px !important;
    font-size: 12px !important;
    vertical-align: middle !important;
}
#creditmemo-table td, #creditmemo-table th {
    padding: 3px 6px !important;
    line-height: 1.1 !important;
    vertical-align: middle !important;
    font-size: 12px !important;
}
#creditmemo-table tbody tr {
    height: 28px !important;
}
#creditmemo-table input[type="checkbox"] {
    margin: 0 !important;
    transform: scale(0.8);
}
#creditmemo-table thead th {
    font-size: 11px !important;
    font-weight: bold !important;
    padding: 4px 6px !important;
}
#creditmemo-table tfoot td {
    padding: 3px 6px !important;
    font-size: 12px !important;
    vertical-align: middle !important;
}
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i> <?= lang('Edit Supplier Payment') ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php echo admin_form_open('suppliers/edit_payment', ['data-toggle' => 'validator', 'role' => 'form']); ?>
                <input type="hidden" name="edit_supplier_payment" value="1">
                <input type="hidden" name="payment_id" value="<?= $payment_ref->id ?>">

                <!-- Row 1: Date | Reference No | Supplier -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Date', 'date'); ?>
                            <?php $date_display = !empty($payment_ref->date) ? date('d/m/Y', strtotime($payment_ref->date)) : date('d/m/Y');  ?>
                            <?= form_input('date', $date_display, 'class="form-control input-tip date" id="date" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reference_no', 'reference_no'); ?>
                            <?= form_input('reference_no', ($payment_ref->reference_no ?? ''), 'class="form-control input-tip" id="reference_no" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('supplier', 'supplier'); ?>
                            <?php
                            $supplier_display = '';
                            $supplier_id = '';
                            if (isset($payment_ref)) {
                                // Find the supplier in the array
                                foreach ($suppliers as $s) {
                                    if ($s->id == $payment_ref->supplier_id) {
                                        $supplier_display = $s->company . ' (' . $s->name . ')';
                                        $supplier_id = $s->id;
                                        if (isset($s->sequence_code)) {
                                            $supplier_display .= ' - ' . $s->sequence_code;
                                        }
                                        break;
                                    }
                                }
                            }
                            ?>
                            <div style="position: relative;">
                                <input type="text" class="form-control" readonly value="<?= htmlspecialchars($supplier_display); ?>" style="background-color: #f5f5f5; cursor: default;">
                                <input type="hidden" name="supplier" id="supplier" value="<?= $supplier_id; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Ledger | Payment Amount | Bank Charges | Note -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= lang('Ledger', 'ledger'); ?>
                            <?php
                            $ldg_opts = ['' => lang('select') . ' ' . lang('ledger')];
                            foreach ($ledgers as $l) {
                                $ldg_opts[$l->id] = $l->name;
                            }
                            echo form_dropdown('ledger', $ldg_opts, ($payment_ref->transfer_from_ledger ?? ''), 'id="ledger_id" class="form-control input-tip select" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= lang('payment_amount', 'payment_amount'); ?>
                            <?php 
                                $payment_amt = 0;
                                
                                // First try to get from payment_ref amount field
                                if (isset($payment_ref->amount) && (float)$payment_ref->amount > 0) {
                                    $payment_amt = (float)$payment_ref->amount;
                                } else if (!empty($existing_payments) && is_array($existing_payments)) {
                                    // If not set, calculate from sum of existing payment line items
                                    foreach ($existing_payments as $pmt) {
                                        $payment_amt += (float)($pmt->amount ?? 0);
                                    }
                                }
                            ?>
                            <?= form_input('payment_amount', number_format($payment_amt, 2), 'class="form-control text-right acct-money" id="payment_amount" type="text" inputmode="decimal" autocomplete="off" placeholder="0.00"'); ?>
                            <input type="hidden" id="total_invoices_checked" value="0.00">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="bank_charges"><?= lang('bank_charges') ?></label>
                            <?php 
                                $bank_chg = isset($payment_ref->bank_charges) ? (float)$payment_ref->bank_charges : 0;
                            ?>
                            <?= form_input('bank_charges', number_format($bank_chg, 2), 'class="form-control text-right acct-money" id="bank_charges" type="text" inputmode="decimal" autocomplete="off" placeholder="0.00"'); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= lang('note', 'note'); ?>
                            <?= form_input('note', ($payment_ref->note ?? ''), 'class="form-control" id="note" placeholder="Payment notes..."'); ?>
                        </div>
                    </div>
                </div>

                <!-- Supplier Info Bar -->
                <div id="supplier-info" style="display:none; margin-bottom:15px;">
                    <div class="box box-primary" style="border-color:#dbd2d2; padding:10px; margin-bottom:0;">
                        <div class="box-body" style="background:#f8f9fa; padding:10px;">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong style="color:#0097a7;">Advance Balance:</strong><br>
                                    <span id="supplier-advance-balance" style="font-size:16px; font-weight:bold; color:#0097a7;">0.00</span>
                                    <input type="hidden" id="advance-balance-raw" value="0.00">
                                </div>
                                <div class="col-md-4">
                                    <strong style="color:#388e3c;">Apply Advance:</strong><br>
                                    <label style="font-weight:normal; margin-bottom:4px;">
                                        <input type="checkbox" id="use-advance" style="margin-right:5px;">
                                        Use advance balance
                                    </label><br>
                                    <?php 
                                        $advance_amt = isset($payment_ref->advance_amount) ? (float)$payment_ref->advance_amount : 0;
                                    ?>
                                    <input type="text" id="advance_amount" name="advance_amount"
                                           class="form-control text-right acct-money" style="width:160px; display:inline-block;"
                                           value="<?= number_format($advance_amt, 2) ?>" inputmode="decimal" autocomplete="off" placeholder="0.00" <?= $advance_amt > 0 ? '' : 'disabled' ?>>
                                    <small class="text-muted d-block">Max available: <span id="advance-max-display">0.00</span></small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted">
                                        Advance balance represents pre-payments made to this supplier
                                        that can be applied against invoices.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoices Section -->
                <div id="invoices-section" style="display:none; margin-top:15px;">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-file-text-o"></i> Pending Purchase Invoices</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="invoices-table" class="table table-striped table-bordered table-hover">
                                    <thead style="background:#f5f5f5;">
                                        <tr>
                                            <th width="4%"><input type="checkbox" id="select-all-invoices"></th>
                                            <th>Date</th>
                                            <th>Reference No</th>
                                            <th class="text-right">Total Amount</th>
                                            <th class="text-right">Paid Amount</th>
                                            <th class="text-right">Outstanding</th>
                                            <th class="text-right">Apply Amount</th>
                                        </tr>
                                    </thead>
                                    <tfoot style="background:#e8f5e8; font-weight:bold;">
                                        <tr>
                                            <td colspan="3"><strong>Totals</strong></td>
                                            <td class="text-right" id="inv-total-grand">0.00</td>
                                            <td class="text-right" id="inv-total-paid">0.00</td>
                                            <td class="text-right" id="inv-total-outstanding">0.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Invoices Section -->
                <div id="serviceinvoice-section" style="display:none; margin-top:15px;">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-file-text-o"></i> Pending Service Invoices / Petty Cash</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="serviceinvoice-table" class="table table-striped table-bordered table-hover">
                                    <thead style="background:#d1ecf1;">
                                        <tr>
                                            <th width="4%"><input type="checkbox" id="select-all-serviceinvoices"></th>
                                            <th>Date</th>
                                            <th>Reference No</th>
                                            <th>Type</th>
                                            <th class="text-right">Total Amount</th>
                                            <th class="text-right">Paid Amount</th>
                                            <th class="text-right">Outstanding</th>
                                            <th class="text-right">Apply Amount</th>
                                        </tr>
                                    </thead>
                                    <tfoot style="background:#bee5eb; font-weight:bold;">
                                        <tr>
                                            <td colspan="4"><strong>Totals</strong></td>
                                            <td class="text-right" id="si-total-amount">0.00</td>
                                            <td class="text-right" id="si-total-paid">0.00</td>
                                            <td class="text-right" id="si-total-outstanding">0.00</td>
                                            <td class="text-right" id="si-total-payment">0.00</td>
                                        </tr>
                                    </tfoot>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credit Memos Section -->
                <div id="creditmemo-section" style="display:none; margin-top:15px;">
                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-file-o"></i> Pending Credit Memos</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="creditmemo-table" class="table table-striped table-bordered table-hover">
                                    <thead style="background:#f8d7da;">
                                        <tr>
                                            <th width="4%"><input type="checkbox" id="select-all-creditmemos"></th>
                                            <th>Date</th>
                                            <th>Reference No</th>
                                            <th>Type</th>
                                            <th class="text-right">Total Amount</th>
                                            <th class="text-right">Paid Amount</th>
                                            <th class="text-right">Outstanding</th>
                                            <th class="text-right">Apply Amount</th>
                                        </tr>
                                    </thead>
                                    <tfoot style="background:#f5c6cb; font-weight:bold;">
                                        <tr>
                                            <td colspan="4"><strong>Totals</strong></td>
                                            <td class="text-right" id="cm-total-amount">0.00</td>
                                            <td class="text-right" id="cm-total-paid">0.00</td>
                                            <td class="text-right" id="cm-total-outstanding">0.00</td>
                                            <td class="text-right" id="cm-total-payment">0.00</td>
                                        </tr>
                                    </tfoot>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Panel -->
                <div class="row" style="margin-top:20px;">
                    <div class="col-md-12">
                        <div class="well" style="background:#f8f9fa; border:1px solid #dee2e6; border-radius:5px; padding:15px;">
                            <h4 style="margin-top:0; border-bottom:1px solid #dee2e6; padding-bottom:8px;">
                                <i class="fa fa-calculator"></i> Payment Summary
                            </h4>
                            <div class="row">
                                <div class="col-md-2 text-center" style="border-right:1px solid #eee;">
                                    <small class="text-muted">Total Invoices Applied</small><br>
                                    <span id="summary-inv-applied" style="font-size:18px; font-weight:bold; color:#2196F3;">0.00</span>
                                </div>
                                <div class="col-md-2 text-center" style="border-right:1px solid #eee;">
                                    <small class="text-muted">Cash / Bank Payment</small><br>
                                    <span id="summary-cash" style="font-size:18px; font-weight:bold; color:#388e3c;">0.00</span>
                                </div>
                                <div class="col-md-2 text-center" style="border-right:1px solid #eee;">
                                    <small class="text-muted">Advance Applied</small><br>
                                    <span id="summary-advance" style="font-size:18px; font-weight:bold; color:#4CAF50;">0.00</span>
                                </div>
                                <div class="col-md-2 text-center" style="border-right:1px solid #eee;">
                                    <small class="text-muted">Remaining/Overage</small><br>
                                    <span id="summary-remaining" style="font-size:18px; font-weight:bold; color:#333;">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div style="margin-top:20px;">
                    <a href="<?= admin_url('suppliers/list_payments') ?>" class="btn btn-default">
                        <i class="fa fa-times"></i> <?= lang('Cancel') ?>
                    </a>
                    <button type="submit" id="submit-btn" class="btn btn-primary" disabled>
                        <i class="fa fa-save"></i> <?= lang('Update Payment') ?>
                    </button>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<script>
function fmtAcct(n, decimals) {
    decimals = (decimals === undefined || decimals === null) ? 2 : decimals;
    var num = parseFloat(n);
    if (isNaN(num)) num = 0;
    return num.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

function parseAcct(v) {
    if (v === null || v === undefined || v === '') return 0;
    if (typeof v === 'number') return v;
    return parseFloat(String(v).replace(/,/g, '')) || 0;
}

function fmtAcctRaw(n, decimals) {
    decimals = (decimals === undefined || decimals === null) ? 2 : decimals;
    return parseAcct(n).toFixed(decimals);
}

$(document).ready(function () {

    // ── Date picker ──────────────────────────────────────────────────
    $('#date').datetimepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    });

    // ── Track table loading completion ──────────────────────────────
    var tablesLoaded = 0;
    function checkAllTablesLoaded() {
        tablesLoaded++;
        // When all 3 tables are loaded: invoices, service invoices, credit memos
        if (tablesLoaded >= 3) {
            tablesLoaded = 0; // reset for next supplier change
            // Now that all tables are rendered, pre-populate existing payment data
            loadExistingPaymentData();
            
            // Delay calculation to ensure DOM is updated
            setTimeout(function() {
                if (!is_edit_mode) return;
                
                var totalInvApplied = 0;
                var totalSIPayment = 0;
                var totalCMPayment = 0;
                
                $('[id^="inv-amount-"]').each(function() {
                    if (!$(this).attr('id').includes('display')) {
                        totalInvApplied += parseAcct($(this).val());
                    }
                });
                
                $('[id^="si-amount-"]').each(function() {
                    if (!$(this).attr('id').includes('display')) {
                        totalSIPayment += parseAcct($(this).val());
                    }
                });
                
                $('[id^="cm-amount-"]').each(function() {
                    if (!$(this).attr('id').includes('display')) {
                        totalCMPayment += parseAcct($(this).val());
                    }
                });
                
                var cashPayment = parseAcct($('#payment_amount').val());
                var advanceApplied = parseAcct($('#advance_amount').val());
                var totalSources = cashPayment + advanceApplied;
                var totalApplied = totalInvApplied + totalSIPayment + totalCMPayment;
                var diff = totalApplied - totalSources;
                
                // Display total applied (includes invoices, service invoices, and credit memos)
                $('#summary-inv-applied').text(fmtAcct(totalApplied));
                $('#summary-cash').text(fmtAcct(cashPayment));
                $('#summary-advance').text(fmtAcct(advanceApplied));
                $('#summary-remaining').text(fmtAcct(diff));
                
                var $rem = $('#summary-remaining');
                if (Math.abs(diff) < 0.01) {
                    $rem.css('color', '#28a745');
                    $('#submit-btn').prop('disabled', false);
                } else {
                    $rem.css('color', diff > 0 ? '#dc3545' : '#f39c12');
                    $('#submit-btn').prop('disabled', true);
                }
            }, 100);
        }
    }

    // ── Supplier change ──────────────────────────────────────────────
    $('#supplier').change(function () {
        var supplier_id = $(this).val();
        resetAll();

        if (!supplier_id) {
            $('#supplier-info').hide();
            return;
        }

        $.ajax({
            url: '<?= admin_url('suppliers/get_supplier_advance_balance') ?>',
            type: 'GET',
            data: { supplier_id: supplier_id },
            dataType: 'json',
            success: function (response) {
                var balance = parseFloat(response.advance_balance || 0);
                $('#supplier-advance-balance').text(fmtAcct(balance));
                $('#advance-balance-raw').val(fmtAcctRaw(balance));
                $('#advance-max-display').text(fmtAcct(balance));
                $('#supplier-info').show();
            },
            error: function () {
                $('#supplier-info').show();
                $('#supplier-advance-balance').text(fmtAcct(0));
                $('#advance-balance-raw').val('0.00');
                $('#advance-max-display').text(fmtAcct(0));
            }
        });

        // Load all invoice/memo sections
        loadSupplierInvoices(supplier_id);
        loadSupplierServiceInvoices(supplier_id);
        loadSupplierCreditMemos(supplier_id);
    });

    // ── Load invoices ────────────────────────────────────────────────
    function loadSupplierInvoices(supplier_id) {
        $.ajax({
            url: '<?= admin_url('suppliers/get_all_supplier_invoices_for_payment') ?>',
            type: 'GET',
            data: { 
                supplier_id: supplier_id,
                payment_id: <?= isset($payment_ref->id) ? $payment_ref->id : 'null' ?>
            },
            dataType: 'json',
            success: function (invoices) {
                displayInvoices(invoices);
            },
            error: function () {
                $('#invoices-section').hide();
            }
        });
    }

    function displayInvoices(invoices) {
        var tbody = $('#invoices-table tbody');
        tbody.empty();
        $('#select-all-invoices').prop('checked', false);

        var totalGrand = 0, totalPaid = 0, totalOut = 0;

        if (invoices.length > 0) {
            $.each(invoices, function (i, inv) {
                var outstanding = parseFloat(inv.outstanding_amount);
                totalGrand += parseFloat(inv.grand_total);
                totalPaid  += parseFloat(inv.paid || 0);
                totalOut   += outstanding;

                var row = '<tr>' +
                    '<td><input type="checkbox" class="invoice-checkbox" name="invoice_ids[]" value="' + inv.id + '" data-amount="' + outstanding + '"></td>' +
                    '<td>' + inv.date + '</td>' +
                    '<td>' + inv.reference_no + '</td>' +
                    '<td class="text-right">' + fmtAcct(inv.grand_total) + '</td>' +
                    '<td class="text-right">' + fmtAcct(inv.paid || 0) + '</td>' +
                    '<td class="text-right">' + fmtAcct(outstanding) + '</td>' +
                    '<td class="text-right">' +
                        '<span id="inv-amount-display-' + inv.id + '">' + fmtAcct(0) + '</span>' +
                        '<input type="hidden" name="invoice_amounts[' + inv.id + ']" id="inv-amount-' + inv.id + '" value="0.00">' +
                    '</td>' +
                    '</tr>';
                tbody.append(row);
            });
            $('#invoices-section').show();
        } else {
            tbody.append('<tr><td colspan="7" class="text-center">No pending invoices for this supplier</td></tr>');
            $('#invoices-section').show();
        }

        $('#inv-total-grand').text(fmtAcct(totalGrand));
        $('#inv-total-paid').text(fmtAcct(totalPaid));
        $('#inv-total-outstanding').text(fmtAcct(totalOut));
        
        // Signal that invoices table is loaded
        checkAllTablesLoaded();
    }

    // ── Load service invoices ────────────────────────────────────────
    function loadSupplierServiceInvoices(supplier_id) {
        $.ajax({
            url: '<?= admin_url('suppliers/get_all_supplier_service_invoices') ?>',
            type: 'GET',
            data: { supplier_id: supplier_id, payment_id: <?= isset($payment_ref->id) ? $payment_ref->id : 'null' ?> },
            dataType: 'json',
            success: function (invoices) {
                displayServiceInvoices(invoices);
            },
            error: function () {
                $('#serviceinvoice-section').hide();
            }
        });
    }

    function displayServiceInvoices(invoices) {
        var tbody = $('#serviceinvoice-table tbody');
        tbody.empty();
        $('#select-all-serviceinvoices').prop('checked', false);

        var totalAmt = 0, totalPaid = 0, totalOut = 0;

        if (invoices.length > 0) {
            $.each(invoices, function (i, inv) {
                var outstanding = parseFloat(inv.outstanding_amount);
                var paidInThisPayment = parseFloat(inv.paid_in_this_payment) || 0;
                totalAmt  += parseFloat(inv.grand_total);
                totalPaid += parseFloat(inv.used_amount);
                totalOut  += outstanding;

                var row = '<tr>' +
                    '<td><input type="checkbox" class="serviceinvoice-checkbox" name="service_invoice_ids[]" value="' + inv.id + '" data-amount="' + outstanding + '"></td>' +
                    '<td>' + inv.date + '</td>' +
                    '<td>' + inv.reference_no + '</td>' +
                    '<td>' + inv.type + '</td>' +
                    '<td class="text-right">' + fmtAcct(inv.grand_total) + '</td>' +
                    '<td class="text-right">' + fmtAcct(inv.used_amount) + '</td>' +
                    '<td class="text-right">' + fmtAcct(outstanding) + '</td>' +
                    '<td class="text-right">' +
                        '<span id="si-amount-display-' + inv.id + '">' + fmtAcct(paidInThisPayment) + '</span>' +
                        '<input type="hidden" name="service_invoice_amounts[' + inv.id + ']" id="si-amount-' + inv.id + '" value="' + paidInThisPayment.toFixed(2) + '">' +
                    '</td>' +
                    '</tr>';
                tbody.append(row);
                
                // If there's a paid amount in this payment, check the checkbox
                if (paidInThisPayment > 0) {
                    setTimeout(function() {
                        $('input[name="service_invoice_ids[]"][value="' + inv.id + '"]').prop('checked', true);
                    }, 100);
                }
            });
            $('#serviceinvoice-section').show();
        } else {
            tbody.append('<tr><td colspan="8" class="text-center">No pending service invoices or petty cash vouchers for this supplier</td></tr>');
            $('#serviceinvoice-section').show();
        }

        $('#si-total-amount').text(fmtAcct(totalAmt));
        $('#si-total-paid').text(fmtAcct(totalPaid));
        $('#si-total-outstanding').text(fmtAcct(totalOut));
        $('#si-total-payment').text(fmtAcct(0));
        
        // Signal that service invoices table is loaded
        checkAllTablesLoaded();
    }

    // ── Load credit memos ─────────────────────────────────────────────────
    function loadSupplierCreditMemos(supplier_id) {
        $.ajax({
            url: '<?= admin_url('suppliers/get_all_supplier_credit_memos_for_payment') ?>',
            type: 'GET',
            data: { supplier_id: supplier_id, payment_id: <?= isset($payment_ref->id) ? $payment_ref->id : 'null' ?> },
            dataType: 'json',
            success: function (memos) {
                displayCreditMemos(memos);
            },
            error: function () {
                $('#creditmemo-section').hide();
            }
        });
    }

    function displayCreditMemos(memos) {
        var tbody = $('#creditmemo-table tbody');
        tbody.empty();
        $('#select-all-creditmemos').prop('checked', false);

        var totalAmt = 0, totalPaid = 0, totalOut = 0;

        if (memos.length > 0) {
            $.each(memos, function (i, memo) {
                var outstanding = parseFloat(memo.outstanding_amount);
                var paidInThisPayment = parseFloat(memo.paid_in_this_payment) || 0;
                totalAmt  += parseFloat(memo.grand_total);
                totalPaid += parseFloat(memo.used_amount);
                totalOut  += outstanding;

                var row = '<tr>' +
                    '<td><input type="checkbox" class="creditmemo-checkbox" name="creditmemo_ids_ui[]" value="' + memo.id + '" data-amount="' + outstanding + '"></td>' +
                    '<td>' + memo.date + '</td>' +
                    '<td>' + memo.reference_no + '</td>' +
                    '<td>' + memo.type + '</td>' +
                    '<td class="text-right">' + fmtAcct(memo.grand_total) + '</td>' +
                    '<td class="text-right">' + fmtAcct(memo.used_amount) + '</td>' +
                    '<td class="text-right">' + fmtAcct(outstanding) + '</td>' +
                    '<td class="text-right">' +
                        '<span id="cm-amount-display-' + memo.id + '">' + fmtAcct(paidInThisPayment) + '</span>' +
                        '<input type="hidden" name="credit_memo_amounts[' + memo.id + ']" id="cm-amount-' + memo.id + '" value="' + paidInThisPayment.toFixed(2) + '">' +
                    '</td>' +
                    '</tr>';
                tbody.append(row);
                
                // If there's a paid amount in this payment, check the checkbox
                if (paidInThisPayment > 0) {
                    setTimeout(function() {
                        $('input[name="creditmemo_ids_ui[]"][value="' + memo.id + '"]').prop('checked', true);
                    }, 100);
                }
            });
            $('#creditmemo-section').show();
        } else {
            tbody.append('<tr><td colspan="8" class="text-center">No pending credit memos for this supplier</td></tr>');
            $('#creditmemo-section').show();
        }

        $('#cm-total-amount').text(fmtAcct(totalAmt));
        $('#cm-total-paid').text(fmtAcct(totalPaid));
        $('#cm-total-outstanding').text(fmtAcct(totalOut));
        $('#cm-total-payment').text(fmtAcct(0));
        
        // Signal that credit memos table is loaded
        checkAllTablesLoaded();
    }

    // ── Load existing payment data and pre-check ──────────────────────
    function loadExistingPaymentData() {
        var existing = <?= isset($existing_payments) && !empty($existing_payments) ? json_encode($existing_payments) : '[]' ?>;
        var paymentInvoicesMap = <?= isset($payment_invoices_map) ? json_encode($payment_invoices_map) : '{}' ?>;
        var paymentServiceInvoicesMap = <?= isset($payment_service_invoices_map) ? json_encode($payment_service_invoices_map) : '{}' ?>;
        var paymentCreditMemosMap = <?= isset($payment_credit_memos_map) ? json_encode($payment_credit_memos_map) : '{}' ?>;
        
        if (!Array.isArray(existing) || existing.length === 0) return;

        existing.forEach(function (payment) {
            if (payment.purchase_id) {
                var checkbox = $('input[name="invoice_ids[]"][value="' + payment.purchase_id + '"]');
                if (checkbox.length) {
                    checkbox.prop('checked', true);
                    // Pre-fill the amount that was paid in this payment
                    if (paymentInvoicesMap[payment.purchase_id]) {
                        $('#inv-amount-' + payment.purchase_id).val(fmtAcctRaw(paymentInvoicesMap[payment.purchase_id]));
                        $('#inv-amount-display-' + payment.purchase_id).text(fmtAcct(paymentInvoicesMap[payment.purchase_id]));
                    }
                }
            }
            if (payment.memo_id) {
                // Check service invoice checkbox and pre-fill
                var siCheckbox = $('input[name="service_invoice_ids[]"][value="' + payment.memo_id + '"]');
                if (siCheckbox.length) {
                    siCheckbox.prop('checked', true);
                    if (paymentServiceInvoicesMap[payment.memo_id]) {
                        $('#si-amount-' + payment.memo_id).val(fmtAcctRaw(paymentServiceInvoicesMap[payment.memo_id]));
                        $('#si-amount-display-' + payment.memo_id).text(fmtAcct(paymentServiceInvoicesMap[payment.memo_id]));
                    }
                }
                
                // Check credit memo checkbox and pre-fill
                var cmCheckbox = $('input[name="creditmemo_ids_ui[]"][value="' + payment.memo_id + '"]');
                if (cmCheckbox.length) {
                    cmCheckbox.prop('checked', true);
                    if (paymentCreditMemosMap[payment.memo_id]) {
                        $('#cm-amount-' + payment.memo_id).val(fmtAcctRaw(paymentCreditMemosMap[payment.memo_id]));
                        $('#cm-amount-display-' + payment.memo_id).text(fmtAcct(paymentCreditMemosMap[payment.memo_id]));
                    }
                }
            }
        });

        // Do NOT call recalculate() here - let the existing amounts show as-is
        // User will modify them if needed, then recalculate will be triggered
    }

    // ── Checkbox handlers (same as add screen) ────────────────────────
    // When unchecked: clear THIS invoice's amount to 0, then recalculate redistributes
    $(document).on('change', '.invoice-checkbox', function () {
        if (!$(this).is(':checked')) {
            var id = $(this).val();
            $('#inv-amount-' + id).val('0.00');
            $('#inv-amount-display-' + id).text(fmtAcct(0));
        }
        recalculate();
    });

    $(document).on('change', '#select-all-invoices', function () {
        $('.invoice-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
    });

    $(document).on('change', '.serviceinvoice-checkbox', function () {
        if (!$(this).is(':checked')) {
            var id = $(this).val();
            $('#si-amount-' + id).val('0.00');
            $('#si-amount-display-' + id).text(fmtAcct(0));
        }
        recalculate();
    });

    $(document).on('change', '#select-all-serviceinvoices', function () {
        $('.serviceinvoice-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
    });

    $(document).on('change', '.creditmemo-checkbox', function () {
        if (!$(this).is(':checked')) {
            var id = $(this).val();
            $('#cm-amount-' + id).val('0.00');
            $('#cm-amount-display-' + id).text(fmtAcct(0));
        }
        recalculate();
    });

    $(document).on('change', '#select-all-creditmemos', function () {
        $('.creditmemo-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
    });

    // ── Advance checkbox toggle ──────────────────────────────────────
    $(document).on('change', '#use-advance', function () {
        if ($(this).is(':checked')) {
            $('#advance_amount').prop('disabled', false);
        } else {
            $('#advance_amount').prop('disabled', true).val(fmtAcct(0));
        }
        recalculate();
    });

    // ── Money inputs ─────────────────────────────────────────────────
    $(document).on('focus', '#payment_amount, #bank_charges, #advance_amount', function () {
        var raw = parseAcct($(this).val());
        $(this).val(raw === 0 ? '' : raw);
    });

    $(document).on('blur', '#payment_amount, #bank_charges', function () {
        $(this).val(fmtAcct(parseAcct($(this).val())));
        recalculate();
    });

    $(document).on('blur', '#advance_amount', function () {
        var advanceMax = parseAcct($('#advance-balance-raw').val());
        var val = Math.min(parseAcct($(this).val()), advanceMax);
        $(this).val(fmtAcct(val));
        recalculate();
    });

    $(document).on('input', '#payment_amount, #bank_charges, #advance_amount', function () {
        recalculate();
    });

    // ── recalculate: greedy distribution (identical to add screen) ───
    // Fills each checked invoice up to min(outstanding, budgetLeft); unchecked → 0.
    function recalculate() {
        var cashPayment    = parseAcct($('#payment_amount').val());
        var advanceApplied = 0;
        if ($('#use-advance').is(':checked')) {
            var advanceMax = parseAcct($('#advance-balance-raw').val());
            advanceApplied = Math.min(parseAcct($('#advance_amount').val()), advanceMax);
        }

        var totalSources    = cashPayment + advanceApplied;
        var budgetLeft      = Math.round(totalSources * 1e5) / 1e5;
        var totalInvApplied = 0;
        var totalSIPayment  = 0;
        var totalCMPayment  = 0;

        // Greedy fill in DOM order: checked → min(outstanding, budgetLeft); unchecked → 0
        $('.invoice-checkbox, .serviceinvoice-checkbox, .creditmemo-checkbox').each(function () {
            var id           = $(this).val();
            var isServiceInv = $(this).hasClass('serviceinvoice-checkbox');
            var isCreditMemo = $(this).hasClass('creditmemo-checkbox');
            var outstanding  = Math.round((parseFloat($(this).data('amount')) || 0) * 1e5) / 1e5;

            if ($(this).is(':checked')) {
                var apply = Math.round(Math.min(outstanding, budgetLeft) * 1e5) / 1e5;
                budgetLeft = Math.round((budgetLeft - apply) * 1e5) / 1e5;
                
                if (isServiceInv) {
                    totalSIPayment = Math.round((totalSIPayment + apply) * 1e5) / 1e5;
                    $('#si-amount-' + id).val(fmtAcctRaw(apply));
                    $('#si-amount-display-' + id).text(fmtAcct(apply));
                } else if (isCreditMemo) {
                    totalCMPayment = Math.round((totalCMPayment + apply) * 1e5) / 1e5;
                    $('#cm-amount-' + id).val(fmtAcctRaw(apply));
                    $('#cm-amount-display-' + id).text(fmtAcct(apply));
                } else {
                    totalInvApplied = Math.round((totalInvApplied + apply) * 1e5) / 1e5;
                    $('#inv-amount-' + id).val(fmtAcctRaw(apply));
                    $('#inv-amount-display-' + id).text(fmtAcct(apply));
                }
            } else {
                // Unchecked: always zero
                if (isServiceInv) {
                    $('#si-amount-' + id).val('0.00');
                    $('#si-amount-display-' + id).text(fmtAcct(0));
                } else if (isCreditMemo) {
                    $('#cm-amount-' + id).val('0.00');
                    $('#cm-amount-display-' + id).text(fmtAcct(0));
                } else {
                    $('#inv-amount-' + id).val('0.00');
                    $('#inv-amount-display-' + id).text(fmtAcct(0));
                }
            }
        });

        $('#si-total-payment').text(fmtAcct(totalSIPayment));
        $('#cm-total-payment').text(fmtAcct(totalCMPayment));

        // Total applied = invoices + service invoices + credit memos
        var totalApplied = Math.round((totalInvApplied + totalSIPayment + totalCMPayment) * 1e5) / 1e5;
        var diff = totalApplied - totalSources;

        // Display total applied amount (includes all types)
        $('#summary-inv-applied').text(fmtAcct(totalApplied));
        $('#summary-cash').text(fmtAcct(cashPayment));
        $('#summary-advance').text(fmtAcct(advanceApplied));
        $('#summary-remaining').text(fmtAcct(diff));

        var $rem = $('#summary-remaining');
        if (Math.abs(diff) < 0.01) {
            $rem.css('color', '#28a745');
            $('#submit-btn').prop('disabled', false);
        } else {
            $rem.css('color', diff > 0 ? '#dc3545' : '#f39c12');
            $('#submit-btn').prop('disabled', true);
        }
    }

    // ── Reset all ────────────────────────────────────────────────────
    function resetAll() {
        $('#invoices-table tbody, #serviceinvoice-table tbody, #creditmemo-table tbody').empty();
        $('#invoices-section, #serviceinvoice-section, #creditmemo-section').hide();
        $('#inv-total-grand, #inv-total-paid, #inv-total-outstanding').text(fmtAcct(0));
        $('#si-total-amount, #si-total-paid, #si-total-outstanding, #si-total-payment').text(fmtAcct(0));
        $('#cm-total-amount, #cm-total-paid, #cm-total-outstanding, #cm-total-payment').text(fmtAcct(0));
        $('#summary-inv-applied, #summary-cash, #summary-advance, #summary-remaining').text(fmtAcct(0));
        $('#payment_amount, #bank_charges').val(fmtAcct(0));
        $('#use-advance').prop('checked', false);
        $('#advance_amount').prop('disabled', true).val(fmtAcct(0));
        $('#submit-btn').prop('disabled', true);
    }

    // ── Convert fields to raw decimal before submit ───────────────────
    $('form').on('submit', function () {
        ['#payment_amount', '#bank_charges', '#advance_amount'].forEach(function (sel) {
            var $el = $(sel);
            if ($el.length) $el.val(fmtAcctRaw($el.val()));
        });
        $('input[id^="inv-amount-"], input[id^="si-amount-"], input[id^="cm-amount-"]').each(function () {
            $(this).val(fmtAcctRaw($(this).val()));
        });
    });

    // ── Initial state ────────────────────────────────────────────────
    $('#supplier-info').hide();
    $('#invoices-section').hide();
    $('#serviceinvoice-section').hide();
    $('#creditmemo-section').hide();
    $('#submit-btn').prop('disabled', true);

    // Load initial supplier if page is in edit mode
    // Save field values BEFORE triggering change (which calls resetAll and zeroes them out)
    var initial_supplier = $('#supplier').val();
    if (initial_supplier) {
        var saved_payment_amount = $('#payment_amount').val();
        var saved_bank_charges   = $('#bank_charges').val();
        var saved_note           = $('#note').val();
        var saved_ledger         = $('#ledger_id').val();
        var saved_advance_amount = $('#advance_amount').val();
        var is_edit_mode = true;

        $('#supplier').trigger('change');

        // Restore values that resetAll() wiped out
        $('#payment_amount').val(saved_payment_amount);
        $('#bank_charges').val(saved_bank_charges);
        $('#note').val(saved_note);
        $('#ledger_id').val(saved_ledger);
        
        // For edit mode: show advance amount field
        if (saved_advance_amount && parseAcct(saved_advance_amount) > 0) {
            $('#use-advance').prop('checked', true);
            $('#advance_amount').prop('disabled', false).val(saved_advance_amount);
        }
    }
});
</script>
