<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
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
<script>
$(document).ready(function () {

    // ── Date picker ──────────────────────────────────────────────────
    $('#date').datetimepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    });

    // ── Supplier change ──────────────────────────────────────────────
    $('#supplier').change(function () {
        var supplier_id = $(this).val();
        resetAll();

        if (!supplier_id) {
            $('#supplier-info').hide();
            return;
        }

        // Load advance balance
        $.ajax({
            url: '<?= admin_url('suppliers/get_supplier_advance_balance') ?>',
            type: 'GET',
            data: { supplier_id: supplier_id },
            dataType: 'json',
            success: function (response) {
                var balance = parseFloat(response.advance_balance || 0);
                $('#supplier-advance-balance').text(balance.toFixed(5));
                $('#advance-balance-raw').val(balance.toFixed(5));
                $('#advance-max-display').text(balance.toFixed(5));
                $('#supplier-info').show();
            },
            error: function () {
                $('#supplier-info').show();
                $('#supplier-advance-balance').text('0.00');
                $('#advance-balance-raw').val('0.00');
            }
        });

        loadSupplierInvoices(supplier_id);
        // loadSupplierDebitMemos(supplier_id); // hidden
        loadSupplierServiceInvoices(supplier_id);
        loadSupplierCreditMemos(supplier_id);
    });

    // ── Load invoices ────────────────────────────────────────────────
    function loadSupplierInvoices(supplier_id) {
        $.ajax({
            url: '<?= admin_url('suppliers/get_supplier_invoices_for_payment') ?>',
            type: 'GET',
            data: { supplier_id: supplier_id },
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
                    '<td class="text-right">' + parseFloat(inv.grand_total).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(inv.paid || 0).toFixed(5) + '</td>' +
                    '<td class="text-right">' + outstanding.toFixed(5) + '</td>' +
                    '<td class="text-right">' +
                        '<span id="inv-amount-display-' + inv.id + '">0.00000</span>' +
                        '<input type="hidden" name="invoice_amounts[' + inv.id + ']" id="inv-amount-' + inv.id + '" value="0.00000">' +
                    '</td>' +
                    '</tr>';
                tbody.append(row);
            });
            $('#invoices-section').show();
        } else {
            tbody.append('<tr><td colspan="7" class="text-center">No pending invoices for this supplier</td></tr>');
            $('#invoices-section').show();
        }

        $('#inv-total-grand').text(totalGrand.toFixed(5));
        $('#inv-total-paid').text(totalPaid.toFixed(5));
        $('#inv-total-outstanding').text(totalOut.toFixed(5));
    }

    // ── Load debit memos ─────────────────────────────────────────────
    function loadSupplierDebitMemos(supplier_id) {
        $.ajax({
            url: '<?= admin_url('suppliers/get_supplier_debit_memos_for_payment') ?>',
            type: 'GET',
            data: { supplier_id: supplier_id },
            dataType: 'json',
            success: function (memos) {
                displayDebitMemos(memos);
            },
            error: function () {
                $('#debitmemo-section').hide();
            }
        });
    }

    function displayDebitMemos(memos) {
        var tbody = $('#debitmemo-table tbody');
        tbody.empty();
        $('#select-all-debitmemos').prop('checked', false);

        var totalAmt = 0, totalUsed = 0, totalAvail = 0;

        if (memos.length > 0) {
            $.each(memos, function (i, memo) {
                var available = parseFloat(memo.available_balance);
                totalAmt   += parseFloat(memo.amount);
                totalUsed  += parseFloat(memo.used_amount || 0);
                totalAvail += available;

                var row = '<tr>' +
                    '<td><input type="checkbox" class="debitmemo-checkbox" name="debitmemo_ids_ui[]" value="' + memo.id + '" data-amount="' + available + '"></td>' +
                    '<td>' + memo.date + '</td>' +
                    '<td>' + memo.reference_no + '</td>' +
                    '<td class="text-right">' + parseFloat(memo.amount).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(memo.used_amount || 0).toFixed(5) + '</td>' +
                    '<td class="text-right">' + available.toFixed(5) + '</td>' +
                    '<td class="text-right">' +
                        '<span id="dm-amount-display-' + memo.id + '">0.00000</span>' +
                        '<input type="hidden" name="debit_memo_amounts[' + memo.id + ']" id="dm-amount-' + memo.id + '" value="0.00000">' +
                    '</td>' +
                    '</tr>';
                tbody.append(row);
            });
            $('#debitmemo-section').show();
        } else {
            tbody.append('<tr><td colspan="7" class="text-center">No available debit memos for this supplier</td></tr>');
            $('#debitmemo-section').show();
        }

        $('#dm-total-amount').text(totalAmt.toFixed(5));
        $('#dm-total-used').text(totalUsed.toFixed(5));
        $('#dm-total-available').text(totalAvail.toFixed(5));
        $('#dm-total-applied').text('0.00000');
    }

    // ── Load service invoices ────────────────────────────────────────
    function loadSupplierServiceInvoices(supplier_id) {
        $.ajax({
            url: '<?= admin_url('suppliers/get_supplier_service_invoices') ?>',
            type: 'GET',
            data: { supplier_id: supplier_id },
            dataType: 'json',
            success: function (invoices) {
                displayServiceInvoices(invoices);
            },
            error: function () {
                $('#serviceinvoice-section').hide();
            }
        });
    }

    // ── Load credit memos ─────────────────────────────────────────────────
    function loadSupplierCreditMemos(supplier_id) {
        $.ajax({
            url: '<?= admin_url('suppliers/get_supplier_credit_memos_for_payment') ?>',
            type: 'GET',
            data: { supplier_id: supplier_id },
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
                totalAmt  += parseFloat(memo.grand_total);
                totalPaid += parseFloat(memo.total_paid);
                totalOut  += outstanding;

                var row = '<tr>' +
                    '<td><input type="checkbox" class="creditmemo-checkbox" name="creditmemo_ids_ui[]" value="' + memo.id + '" data-amount="' + outstanding + '"></td>' +
                    '<td>' + memo.date + '</td>' +
                    '<td>' + memo.reference_no + '</td>' +
                    '<td>' + memo.type + '</td>' +
                    '<td class="text-right">' + parseFloat(memo.grand_total).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(memo.total_paid).toFixed(5) + '</td>' +
                    '<td class="text-right">' + outstanding.toFixed(5) + '</td>' +
                    '<td class="text-right">' +
                        '<span id="cm-amount-display-' + memo.id + '">0.00000</span>' +
                        '<input type="hidden" name="credit_memo_amounts[' + memo.id + ']" id="cm-amount-' + memo.id + '" value="0.00000">' +
                    '</td>' +
                    '</tr>';
                tbody.append(row);
            });
            $('#creditmemo-section').show();
        } else {
            tbody.append('<tr><td colspan="8" class="text-center">No pending credit memos for this supplier</td></tr>');
            $('#creditmemo-section').show();
        }

        $('#cm-total-amount').text(totalAmt.toFixed(5));
        $('#cm-total-paid').text(totalPaid.toFixed(5));
        $('#cm-total-outstanding').text(totalOut.toFixed(5));
        $('#cm-total-payment').text('0.00000');
    }

    function displayServiceInvoices(invoices) {
        var tbody = $('#serviceinvoice-table tbody');
        tbody.empty();
        $('#select-all-serviceinvoices').prop('checked', false);

        var totalAmt = 0, totalPaid = 0, totalOut = 0;

        if (invoices.length > 0) {
            $.each(invoices, function (i, inv) {
                var outstanding = parseFloat(inv.outstanding_amount);
                totalAmt  += parseFloat(inv.grand_total);
                totalPaid += parseFloat(inv.total_paid);
                totalOut  += outstanding;

                var row = '<tr>' +
                    '<td><input type="checkbox" class="serviceinvoice-checkbox" name="service_invoice_ids[]" value="' + inv.id + '" data-amount="' + outstanding + '"></td>' +
                    '<td>' + inv.date + '</td>' +
                    '<td>' + inv.reference_no + '</td>' +
                    '<td>' + inv.type + '</td>' +
                    '<td class="text-right">' + parseFloat(inv.grand_total).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(inv.total_paid).toFixed(5) + '</td>' +
                    '<td class="text-right">' + outstanding.toFixed(5) + '</td>' +
                    '<td class="text-right">' +
                        '<span id="si-amount-display-' + inv.id + '">0.00000</span>' +
                        '<input type="hidden" name="service_invoice_amounts[' + inv.id + ']" id="si-amount-' + inv.id + '" value="0.00000">' +
                    '</td>' +
                    '</tr>';
                tbody.append(row);
            });
            $('#serviceinvoice-section').show();
        } else {
            tbody.append('<tr><td colspan="8" class="text-center">No pending service invoices for this supplier</td></tr>');
            $('#serviceinvoice-section').show();
        }

        $('#si-total-amount').text(totalAmt.toFixed(5));
        $('#si-total-paid').text(totalPaid.toFixed(5));
        $('#si-total-outstanding').text(totalOut.toFixed(5));
        $('#si-total-payment').text('0.00000');
    }

    // ── Invoice checkbox ─────────────────────────────────────────────
    $(document).on('change', '.invoice-checkbox', function () {
        if (!$(this).is(':checked')) {
            var invId = $(this).val();
            $('#inv-amount-' + invId).val('0.00000');
            $('#inv-amount-display-' + invId).text('0.00000');
        }
        syncPaymentAmount();
        recalculate();
    });

    // Select all invoices
    $(document).on('change', '#select-all-invoices', function () {
        $('.invoice-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
    });

    // ── Debit memo checkbox ──────────────────────────────────────────
    $(document).on('change', '.debitmemo-checkbox', function () {
        if (!$(this).is(':checked')) {
            var memoId = $(this).val();
            $('#dm-amount-' + memoId).val('0.00000');
            $('#dm-amount-display-' + memoId).text('0.00000');
        }
        syncPaymentAmount();
        recalculate();
    });

    // Select all debit memos
    $(document).on('change', '#select-all-debitmemos', function () {
        $('.debitmemo-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
    });

    // ── Service invoice checkbox ────────────────────────────────────
    $(document).on('change', '.serviceinvoice-checkbox', function () {
        if (!$(this).is(':checked')) {
            var siId = $(this).val();
            $('#si-amount-' + siId).val('0.00000');
            $('#si-amount-display-' + siId).text('0.00000');
        }
        syncPaymentAmount();
        recalculate();
    });

    // Select all service invoices
    $(document).on('change', '#select-all-serviceinvoices', function () {
        $('.serviceinvoice-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
    });

    // ── Credit memo checkbox ────────────────────────────────────────
    $(document).on('change', '.creditmemo-checkbox', function () {
        if (!$(this).is(':checked')) {
            var cmId = $(this).val();
            $('#cm-amount-' + cmId).val('0.00000');
            $('#cm-amount-display-' + cmId).text('0.00000');
        }
        syncPaymentAmount();
        recalculate();
    });

    // Select all credit memos
    $(document).on('change', '#select-all-creditmemos', function () {
        $('.creditmemo-checkbox').prop('checked', $(this).is(':checked')).trigger('change');
    });

    // ── Sync payment_amount to cover all checked invoices ────────────
    // Called whenever any invoice / service-invoice / debit-memo checkbox changes.
    // Sets payment_amount = total checked outstanding − debit-memo coverage,
    // so the greedy distribution in recalculate() always has budget to work with.
    function syncPaymentAmount() {
        var totalChecked = 0;
        $('.invoice-checkbox:checked, .serviceinvoice-checkbox:checked, .creditmemo-checkbox:checked').each(function () {
            totalChecked += parseFloat($(this).data('amount')) || 0;
        });
        var totalDMCoverage = 0;
        $('.debitmemo-checkbox:checked').each(function () {
            totalDMCoverage += parseFloat($(this).data('amount')) || 0;
        });
        var needed = Math.max(0, totalChecked - totalDMCoverage);
        $('#payment_amount').val(needed.toFixed(5));
    }

    // ── Advance checkbox toggle ──────────────────────────────────────
    $(document).on('change', '#use-advance', function () {
        if ($(this).is(':checked')) {
            $('#advance_amount').prop('disabled', false);
        } else {
            $('#advance_amount').prop('disabled', true).val('0.00000');
        }
        recalculate();
    });

    // ── Inputs that affect totals ────────────────────────────────────
    $(document).on('input', '#payment_amount, #advance_amount', function () {
        recalculate();
    });

    // ── Main recalculate ─────────────────────────────────────────────
    // Auto-distributes available budget across checked invoices (greedy, DOM order).
    // Debit memos are applied at full available balance when checked.
    function recalculate() {
        var cashPayment    = parseFloat($('#payment_amount').val()) || 0;
        var advanceMax     = parseFloat($('#advance-balance-raw').val()) || 0;
        var advanceApplied = 0;
        if ($('#use-advance').is(':checked')) {
            advanceApplied = Math.min(parseFloat($('#advance_amount').val()) || 0, advanceMax);
            $('#advance_amount').val(advanceApplied.toFixed(5));
        }

        // Debit memos: each checked memo applies its full available balance
        var totalDMApplied = 0;
        $('.debitmemo-checkbox').each(function () {
            var memoId = $(this).val();
            if ($(this).is(':checked')) {
                var amt = parseFloat($(this).data('amount')) || 0;
                totalDMApplied += amt;
                $('#dm-amount-' + memoId).val(amt.toFixed(5));
                $('#dm-amount-display-' + memoId).text(amt.toFixed(5));
            } else {
                $('#dm-amount-' + memoId).val('0.00000');
                $('#dm-amount-display-' + memoId).text('0.00000');
            }
        });
        $('#dm-total-applied').text(totalDMApplied.toFixed(5));

        var totalSources = cashPayment + advanceApplied + totalDMApplied;
        var budgetLeft      = totalSources;
        var totalInvApplied = 0;
        var totalSIPayment  = 0;
        var totalCMPayment  = 0;

        // Greedy distribution: fill each checked invoice (regular + service + credit memo) up to its outstanding; stop when budget exhausted
        $('.invoice-checkbox, .serviceinvoice-checkbox, .creditmemo-checkbox').each(function () {
            var id           = $(this).val();
            var isServiceInv = $(this).hasClass('serviceinvoice-checkbox');
            var isCreditMemo = $(this).hasClass('creditmemo-checkbox');
            var outstanding  = parseFloat($(this).data('amount')) || 0;
            if ($(this).is(':checked')) {
                var apply = Math.min(outstanding, budgetLeft);
                budgetLeft      -= apply;
                totalInvApplied += apply;
                if (isServiceInv) {
                    totalSIPayment += apply;
                    $('#si-amount-' + id).val(apply.toFixed(5));
                    $('#si-amount-display-' + id).text(apply.toFixed(5));
                } else if (isCreditMemo) {
                    totalCMPayment += apply;
                    $('#cm-amount-' + id).val(apply.toFixed(5));
                    $('#cm-amount-display-' + id).text(apply.toFixed(5));
                } else {
                    $('#inv-amount-' + id).val(apply.toFixed(5));
                    $('#inv-amount-display-' + id).text(apply.toFixed(5));
                }
            } else {
                if (isServiceInv) {
                    $('#si-amount-' + id).val('0.00000');
                    $('#si-amount-display-' + id).text('0.00000');
                } else if (isCreditMemo) {
                    $('#cm-amount-' + id).val('0.00000');
                    $('#cm-amount-display-' + id).text('0.00000');
                } else {
                    $('#inv-amount-' + id).val('0.00000');
                    $('#inv-amount-display-' + id).text('0.00000');
                }
            }
        });
        $('#si-total-payment').text(totalSIPayment.toFixed(5));
        $('#cm-total-payment').text(totalCMPayment.toFixed(5));

        // diff < 0: excess cash (sources > invoices applied) — orange
        // diff > 0: impossible with greedy fill, but guard anyway
        // diff = 0: balanced — green, enable submit
        var diff = totalInvApplied - totalSources;

        $('#summary-inv-applied').text(totalInvApplied.toFixed(5));
        $('#summary-cash').text(cashPayment.toFixed(5));
        $('#summary-dm-applied').text(totalDMApplied.toFixed(5));
        $('#summary-advance').text(advanceApplied.toFixed(5));
        $('#summary-remaining').text(diff.toFixed(5));

        var $summaryRem = $('#summary-remaining');
        if (Math.abs(diff) < 0.01) {
            $summaryRem.css('color', '#28a745');
            $('#submit-btn').prop('disabled', false);
        } else {
            $summaryRem.css('color', diff > 0 ? '#dc3545' : '#f39c12');
            $('#submit-btn').prop('disabled', true);
        }
    }

    // ── Reset all on supplier change ─────────────────────────────────
    function resetAll() {
        $('#invoices-table tbody').empty();
        $('#debitmemo-table tbody').empty();
        $('#serviceinvoice-table tbody').empty();
        $('#creditmemo-table tbody').empty();
        $('#invoices-section').hide();
        $('#debitmemo-section').hide();
        $('#serviceinvoice-section').hide();
        $('#creditmemo-section').hide();
        $('#inv-total-grand, #inv-total-paid, #inv-total-outstanding').text('0.00000');
        $('#dm-total-amount, #dm-total-used, #dm-total-available, #dm-total-applied').text('0.00000');
        $('#si-total-amount, #si-total-paid, #si-total-outstanding, #si-total-payment').text('0.00000');
        $('#cm-total-amount, #cm-total-paid, #cm-total-outstanding, #cm-total-payment').text('0.00000');
        $('#summary-inv-applied, #summary-cash, #summary-dm-applied, #summary-advance, #summary-remaining').text('0.00000');
        $('#payment_amount').val('0.00000');
        $('#use-advance').prop('checked', false);
        $('#advance_amount').prop('disabled', true).val('0.00');
        $('#advance-balance-raw').val('0.00');
        $('#summary-remaining').css('color', '#333');
        $('#submit-btn').prop('disabled', true);
    }

    // ── Initial state ────────────────────────────────────────────────
    $('#supplier-info').hide();
    $('#invoices-section').hide();
    $('#debitmemo-section').hide();
    $('#creditmemo-section').hide();
    $('#submit-btn').prop('disabled', true);
});
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i> <?= lang('Supplier Invoice Payment') ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php echo admin_form_open('suppliers/payment_to_supplier_new', ['data-toggle' => 'validator', 'role' => 'form']); ?>

                <!-- Row 1: Date | Reference No | Supplier -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Date', 'date'); ?>
                            <?= form_input('date', date('d/m/Y'), 'class="form-control input-tip date" id="date" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reference_no', 'reference_no'); ?>
                            <?= form_input('reference_no', set_value('reference_no'), 'class="form-control input-tip" id="reference_no" required="required"'); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('supplier', 'supplier'); ?>
                            <?php
                            $sup_opts = ['' => lang('select_supplier')];
                            foreach ($suppliers as $s) {
                                $sup_opts[$s->id] = $s->company . ' (' . $s->name . ')' . (isset($s->sequence_code) ? ' - ' . $s->sequence_code : '');
                            }
                            echo form_dropdown('supplier', $sup_opts, set_value('supplier'), 'id="supplier" class="form-control input-tip select" required="required" style="width:100%;"');
                            ?>
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
                            echo form_dropdown('ledger', $ldg_opts, set_value('ledger'), 'id="ledger_id" class="form-control input-tip select" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= lang('payment_amount', 'payment_amount'); ?>
                            <?= form_input('payment_amount', '0.00', 'class="form-control" id="payment_amount" type="number" step="0.01" min="0" placeholder="0.00"'); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="bank_charges"><?= lang('bank_charges') ?></label>
                            <?= form_input('bank_charges', '0.00', 'class="form-control" id="bank_charges" type="number" step="0.01" min="0" placeholder="0.00"'); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= lang('note', 'note'); ?>
                            <?= form_input('note', set_value('note'), 'class="form-control" id="note" placeholder="Payment notes..."'); ?>
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
                                    <input type="number" id="advance_amount" name="advance_amount"
                                           class="form-control" style="width:160px; display:inline-block;"
                                           value="0.00" step="0.01" min="0" placeholder="0.00" disabled>
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

                <!-- Debit Memos Section (hidden) -->
                <div id="debitmemo-section" style="display:none !important;">
                    <div class="box box-warning">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-minus-circle"></i> Available Debit Memos</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="debitmemo-table" class="table table-striped table-bordered table-hover">
                                    <thead style="background:#fff3cd;">
                                        <tr>
                                            <th width="4%"><input type="checkbox" id="select-all-debitmemos"></th>
                                            <th>Date</th>
                                            <th>Reference No</th>
                                            <th class="text-right">Memo Amount</th>
                                            <th class="text-right">Used Amount</th>
                                            <th class="text-right">Available Balance</th>
                                            <th class="text-right">Apply Amount</th>
                                        </tr>
                                    </thead>
                                    <tfoot style="background:#ffeaa7; font-weight:bold;">
                                        <tr>
                                            <td colspan="3"><strong>Totals</strong></td>
                                            <td class="text-right" id="dm-total-amount">0.00</td>
                                            <td class="text-right" id="dm-total-used">0.00</td>
                                            <td class="text-right" id="dm-total-available">0.00</td>
                                            <td class="text-right" id="dm-total-applied">0.00</td>
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
                            <h3 class="box-title"><i class="fa fa-file-text-o"></i> Pending Service Invoices</h3>
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
                                    <small class="text-muted">Debit Memos Applied</small><br>
                                    <span id="summary-dm-applied" style="font-size:18px; font-weight:bold; color:#f57c00;">0.00</span>
                                </div>
                                <div class="col-md-2 text-center" style="border-right:1px solid #eee;">
                                    <small class="text-muted">Advance Applied</small><br>
                                    <span id="summary-advance" style="font-size:18px; font-weight:bold; color:#7b1fa2;">0.00</span>
                                </div>
                                <div class="col-md-4 text-center">
                                    <small class="text-muted">Remaining (must be 0.00)</small><br>
                                    <span id="summary-remaining" style="font-size:22px; font-weight:bold; color:#333;">0.00</span>
                                </div>
                            </div>
                            <div class="row" style="margin-top:8px;">
                                <div class="col-md-12">
                                    <small class="text-muted">
                                        <strong>Note:</strong> Total invoice applied must equal Cash + Debit Memos + Advance. The Submit button is enabled only when Remaining equals 0.00.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <button type="submit" id="submit-btn" class="btn btn-primary" disabled>
                                <i class="fa fa-check"></i> <?= lang('submit') ?>
                            </button>
                            <a href="<?= admin_url('suppliers/payments') ?>" class="btn btn-default">
                                <i class="fa fa-times"></i> <?= lang('cancel') ?>
                            </a>
                        </div>
                    </div>
                </div>

                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
