<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
/* Compact table styling for slimmer rows */
#advances-table td, #advances-table th,
#invoices-table td, #invoices-table th,
#returns-table td, #returns-table th,
#creditmemo-table td, #creditmemo-table th,
#serviceinvoice-table td, #serviceinvoice-table th {
    padding: 3px 6px !important;
    line-height: 1.1 !important;
    vertical-align: middle !important;
    font-size: 12px !important;
}
#advances-table tbody tr, #invoices-table tbody tr,
#returns-table tbody tr, #creditmemo-table tbody tr,
#serviceinvoice-table tbody tr { height: 28px !important; }

#advances-table input[type="checkbox"],
#invoices-table input[type="checkbox"],
#returns-table input[type="checkbox"],
#creditmemo-table input[type="checkbox"],
#serviceinvoice-table input[type="checkbox"] { margin:0 !important; transform:scale(0.8); }

#advances-table thead th, #invoices-table thead th,
#returns-table thead th, #creditmemo-table thead th,
#serviceinvoice-table thead th { font-size:11px !important; font-weight:bold !important; padding:4px 6px !important; }

#advances-table tfoot td, #invoices-table tfoot td,
#returns-table tfoot td, #creditmemo-table tfoot td,
#serviceinvoice-table tfoot td { padding:3px 6px !important; line-height:1.1 !important; font-size:12px !important; vertical-align:middle !important; }
</style>
<script>
/* ── Helper functions for number formatting ────────────────────────── */
function fmtAcct(n, decimals) {
    decimals = (decimals === undefined || decimals === null) ? 2 : decimals;
    var num = parseFloat(n);
    if (isNaN(num)) {
        num = 0;
    }
    if (typeof accounting !== 'undefined' && accounting.formatNumber) {
        return accounting.formatNumber(num, decimals, ',', '.');
    }
    return num.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

function parseAcct(v) {
    if (v === null || v === undefined || v === '') {
        return 0;
    }
    if (typeof v === 'number') {
        return v;
    }
    return parseFloat(String(v).replace(/,/g, '')) || 0;
}

$(document).ready(function() {

    /* ── Pre-fill maps from PHP ─────────────────────────────────── */
    var paymentInvoicesMap        = <?= json_encode($paymentInvoicesMap        ?? []) ?>;
    var paymentServiceInvoicesMap = <?= json_encode($paymentServiceInvoicesMap ?? []) ?>;
    var paymentCreditMemosMap     = <?= json_encode($paymentCreditMemosMap     ?? []) ?>;
    var paymentReturnsMap         = <?= json_encode($paymentReturnsMap         ?? []) ?>;
    var paymentAdvancesMap        = <?= json_encode($paymentAdvancesMap        ?? []) ?>;
    var isLocked = <?= (!empty($is_closed) && $is_closed) ? 'true' : 'false' ?>;

    /* ── Date picker ─────────────────────────────────────────────── */
    $('#date').datetimepicker({ format: 'dd/mm/yyyy', autoclose: true, todayHighlight: true });

    /* ── Global: order of checked invoices (for calculateTotalPayment) */
    var checkedInvoicesOrder = [];

    /* ── Counter: trigger summary once all 5 sections loaded ──────── */
    var sectionsLoaded = 0;
    function onSectionLoaded() {
        sectionsLoaded++;
        if (sectionsLoaded >= 5) { updateEditSummary(); }
    }

    /* ── Load all sections on page load ──────────────────────────── */
    var customerId = $('#customer_hidden').val();
    var paymentId = <?= isset($payment_ref->id) ? json_encode($payment_ref->id) : 'null' ?>;
    if (customerId) {
        loadCustomerInfo(customerId);
        loadCustomerAdvances(customerId, paymentId);
        loadCustomerInvoices(customerId, paymentId);
        loadCustomerReturns(customerId, paymentId);
        loadCustomerCreditMemos(customerId, paymentId);
        loadCustomerServiceInvoices(customerId, paymentId);
    }

    /* ── Customer info ───────────────────────────────────────────── */
    function loadCustomerInfo(customer_id) {
        $.ajax({
            url: '<?= admin_url('customers/customer_limit_info') ?>',
            type: 'GET', data: { customer_id: customer_id }, dataType: 'json',
            success: function(r) {
                if (r.customer_name) {
                    $('#customer-info').show();
                    $('#customer-name').text(r.customer_name);
                    $('#credit-limit').text('<?= $this->sma->formatMoney(0) ?>'.replace('0.00', r.credit_limit.toFixed(5)));
                    $('#current-balance').text('<?= $this->sma->formatMoney(0) ?>'.replace('0.00', r.current_balance.toFixed(5)));
                    $('#remaining-limit').text('<?= $this->sma->formatMoney(0) ?>'.replace('0.00', r.remaining_limit.toFixed(5)));
                    $('#available-advance').text('<?= $this->sma->formatMoney(0) ?>'.replace('0.00', r.available_advance.toFixed(5)));
                    $('#payment-term').text(r.payment_term || 'N/A');
                }
            }
        });
    }

    /* ── Invoices ────────────────────────────────────────────────── */
    function loadCustomerInvoices(customer_id, payment_id) {
        $.ajax({
            url: '<?= admin_url('customers/get_all_customer_invoices_for_payment') ?>',
            type: 'GET', data: { customer_id: customer_id, payment_id: payment_id }, dataType: 'json',
            success: function(d) { displayInvoices(d); },
            error: function() { $('#invoices-section').show(); onSectionLoaded(); }
        });
    }
    function displayInvoices(invoices) {
        var tbody = $('#invoices-table tbody').empty();
        $('#select-all-invoices').prop('checked', false);
        var ta = 0, tp = 0, to = 0;
        if (invoices.length > 0) {
            $.each(invoices, function(i, inv) {
                // Use paid_in_this_payment from API response if available (for edit), otherwise use pre-fill map
                var pre = parseFloat(inv.paid_in_this_payment !== undefined ? inv.paid_in_this_payment : (paymentInvoicesMap[inv.id] || 0));
                var chk = pre > 0, amt = pre > 0 ? pre.toFixed(5) : '0.00';
                if (chk && checkedInvoicesOrder.indexOf(inv.id.toString()) === -1) checkedInvoicesOrder.push(inv.id.toString());
                tbody.append('<tr>' +
                    '<td><input type="checkbox" class="invoice-checkbox" name="invoice_ids[]" value="' + inv.id + '" data-amount="' + inv.outstanding_amount + '"' + (chk?' checked':'') + '></td>' +
                    '<td>' + inv.date + '</td><td>' + inv.reference_no + '</td><td></td>' +
                    '<td class="text-right">' + parseFloat(inv.grand_total).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(inv.paid).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(inv.outstanding_amount).toFixed(5) + '</td>' +
                    '<td class="text-right"><span class="invoice-payment-amount" id="invoice-payment-' + inv.id + '">' + amt + '</span>' +
                    '<input type="hidden" name="invoice_amounts[' + inv.id + ']" value="' + amt + '" id="invoice-amount-' + inv.id + '"></td></tr>');
                ta += parseFloat(inv.grand_total); tp += parseFloat(inv.paid); to += parseFloat(inv.outstanding_amount);
            });
            $('#invoices-section').show();
        } else {
            tbody.append('<tr><td colspan="8" class="text-center">No invoices found for this customer</td></tr>');
            $('#invoices-section').show();
        }
        $('#total-amount').text(ta.toFixed(5)); $('#total-paid').text(tp.toFixed(5)); $('#total-outstanding').text(to.toFixed(5));
        onSectionLoaded();
    }

    /* ── Returns ─────────────────────────────────────────────────── */
    function loadCustomerReturns(customer_id, payment_id) {
        $.ajax({
            url: '<?= admin_url('customers/get_all_customer_returns_for_payment') ?>',
            type: 'GET', data: { customer_id: customer_id, payment_id: payment_id }, dataType: 'json',
            success: function(d) { displayReturns(d); },
            error: function() { $('#returns-section').show(); onSectionLoaded(); }
        });
    }
    function displayReturns(returns) {
        var tbody = $('#returns-table tbody').empty();
        $('#select-all-returns').prop('checked', false);
        var ta = 0, tp = 0, to = 0, tu = 0;
        if (returns.length > 0) {
            $.each(returns, function(i, r) {
                // Use used_in_this_payment from API response if available (for edit), otherwise use pre-fill map
                var pre = parseFloat(r.used_in_this_payment !== undefined ? r.used_in_this_payment : (paymentReturnsMap[r.id] || 0));
                var chk = pre > 0, amt = pre > 0 ? pre.toFixed(5) : '0.00';
                // Pre-checked: data-amount = what was applied in this payment (prevents the full balance inflating totalPriorityAvailable)
                // Not pre-checked: data-amount = full available balance (user newly selecting)
                var retDataAmt = chk ? pre : parseFloat(r.outstanding_amount || 0);
                tu += pre;
                tbody.append('<tr>' +
                    '<td><input type="checkbox" class="return-checkbox" name="return_ids[]" value="' + r.id + '" data-amount="' + retDataAmt + '"' + (chk?' checked':'') + '></td>' +
                    '<td>' + r.date + '</td><td>' + r.reference_no + '</td><td>' + (r.type || 'return') + '</td>' +
                    '<td class="text-right">' + parseFloat(r.grand_total).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(r.used_amount || 0).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(r.outstanding_amount || 0).toFixed(5) + '</td>' +
                    '<td class="text-right"><span class="return-used-amount" id="return-used-' + r.id + '">' + amt + '</span>' +
                    '<input type="hidden" name="return_amounts[' + r.id + ']" value="' + amt + '" id="return-amount-' + r.id + '"></td></tr>');
                ta += parseFloat(r.grand_total); tp += parseFloat(r.used_amount || 0); to += parseFloat(r.outstanding_amount || 0);
            });
            $('#returns-section').show();
        } else {
            tbody.append('<tr><td colspan="8" class="text-center">No returns found for this customer</td></tr>');
            $('#returns-section').show();
        }
        $('#returns-total-amount').text(ta.toFixed(5)); $('#returns-total-paid').text(tp.toFixed(5));
        $('#returns-total-outstanding').text(to.toFixed(5)); $('#returns-total-used').text(tu.toFixed(5));
        onSectionLoaded();
    }

    /* ── Credit Memos ────────────────────────────────────────────── */
    function loadCustomerCreditMemos(customer_id, payment_id) {
        $.ajax({
            url: '<?= admin_url('customers/get_all_customer_credit_memos_for_payment') ?>',
            type: 'GET', data: { customer_id: customer_id, payment_id: payment_id }, dataType: 'json',
            success: function(d) { displayCreditMemos(d); },
            error: function() { $('#creditmemo-section').show(); onSectionLoaded(); }
        });
    }
    function displayCreditMemos(cms) {
        var tbody = $('#creditmemo-table tbody').empty();
        $('#select-all-creditmemos').prop('checked', false);
        var ta = 0, tu = 0, tav = 0, tap = 0;
        if (cms.length > 0) {
            $.each(cms, function(i, cm) {
                // Use used_in_this_payment from API response if available (for edit), otherwise use pre-fill map
                var pre = parseFloat(cm.used_in_this_payment !== undefined ? cm.used_in_this_payment : (paymentCreditMemosMap[cm.id] || 0));
                var chk = pre > 0, amt = pre > 0 ? pre.toFixed(5) : '0.00';
                // Pre-checked: data-amount = what was applied in this payment (prevents the full balance inflating totalPriorityAvailable)
                var cmDataAmt = chk ? pre : parseFloat(cm.outstanding_amount || 0);
                tap += pre;
                tbody.append('<tr>' +
                    '<td><input type="checkbox" class="creditmemo-checkbox" name="creditmemo_ids[]" value="' + cm.id + '" data-amount="' + cmDataAmt + '"' + (chk?' checked':'') + '></td>' +
                    '<td>' + cm.date + '</td><td>' + cm.reference_no + '</td><td>' + cm.type + '</td>' +
                    '<td class="text-right">' + parseFloat(cm.grand_total).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(cm.used_amount || 0).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(cm.outstanding_amount || 0).toFixed(5) + '</td>' +
                    '<td class="text-right"><span class="creditmemo-applied-amount" id="creditmemo-applied-' + cm.id + '">' + amt + '</span>' +
                    '<input type="hidden" name="creditmemo_amounts[' + cm.id + ']" value="' + amt + '" id="creditmemo-amount-' + cm.id + '"></td></tr>');
                ta += parseFloat(cm.grand_total); tu += parseFloat(cm.used_amount || 0); tav += parseFloat(cm.outstanding_amount || 0);
            });
            $('#creditmemo-section').show();
        } else {
            tbody.append('<tr><td colspan="8" class="text-center">No credit memos found for this customer</td></tr>');
            $('#creditmemo-section').show();
        }
        $('#creditmemo-total-amount').text(ta.toFixed(5)); $('#creditmemo-total-used').text(tu.toFixed(5));
        $('#creditmemo-total-available').text(tav.toFixed(5)); $('#creditmemo-total-applied').text(tap.toFixed(5));
        onSectionLoaded();
    }

    /* ── Service Invoices ────────────────────────────────────────── */
    function loadCustomerServiceInvoices(customer_id, payment_id) {
        $.ajax({
            url: '<?= admin_url('customers/get_all_customer_service_invoices_for_payment') ?>',
            type: 'GET', data: { customer_id: customer_id, payment_id: payment_id }, dataType: 'json',
            success: function(d) { displayServiceInvoices(d); },
            error: function() { $('#serviceinvoice-section').show(); onSectionLoaded(); }
        });
    }
    function displayServiceInvoices(invoices) {
        var tbody = $('#serviceinvoice-table tbody').empty();
        $('#select-all-serviceinvoices').prop('checked', false);
        var ta = 0, tp = 0, to = 0, tpaid = 0;
        if (invoices.length > 0) {
            $.each(invoices, function(i, inv) {
                // Use used_in_this_payment from API response if available (for edit), otherwise use pre-fill map
                var pre = parseFloat(inv.used_in_this_payment !== undefined ? inv.used_in_this_payment : (paymentServiceInvoicesMap[inv.id] || 0));
                var chk = pre > 0, amt = pre > 0 ? pre.toFixed(5) : '0.00';
                tpaid += pre;
                if (chk && checkedInvoicesOrder.indexOf('si_' + inv.id) === -1) checkedInvoicesOrder.push('si_' + inv.id);
                tbody.append('<tr>' +
                    '<td><input type="checkbox" class="serviceinvoice-checkbox" name="service_invoice_ids[]" value="' + inv.id + '" data-amount="' + inv.outstanding_amount + '"' + (chk?' checked':'') + '></td>' +
                    '<td>' + inv.date + '</td><td>' + inv.reference_no + '</td><td>' + inv.type + '</td>' +
                    '<td class="text-right">' + parseFloat(inv.grand_total).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(inv.used_amount || 0).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(inv.outstanding_amount).toFixed(5) + '</td>' +
                    '<td class="text-right"><span class="serviceinvoice-payment-amount" id="serviceinvoice-payment-' + inv.id + '">' + amt + '</span>' +
                    '<input type="hidden" name="service_invoice_amounts[' + inv.id + ']" value="' + amt + '" id="serviceinvoice-amount-' + inv.id + '"></td></tr>');
                ta += parseFloat(inv.grand_total); tp += parseFloat(inv.used_amount || 0); to += parseFloat(inv.outstanding_amount);
            });
            $('#serviceinvoice-section').show();
        } else {
            tbody.append('<tr><td colspan="8" class="text-center">No service invoices found for this customer</td></tr>');
            $('#serviceinvoice-section').show();
        }
        $('#si-total-amount').text(ta.toFixed(5)); $('#si-total-paid').text(tp.toFixed(5));
        $('#si-total-outstanding').text(to.toFixed(5)); $('#si-total-payment').text(tpaid.toFixed(5));
        onSectionLoaded();
    }

    /* ── Advances ────────────────────────────────────────────────── */
    function loadCustomerAdvances(customer_id, payment_id) {
        $.ajax({
            url: '<?= admin_url('customers/get_all_customer_advances_for_payment') ?>',
            type: 'GET', data: { customer_id: customer_id, payment_id: payment_id }, dataType: 'json',
            success: function(d) { displayAdvances(d); },
            error: function() { $('#advances-section').show(); onSectionLoaded(); }
        });
    }
    function displayAdvances(advances) {
        var tbody = $('#advances-table tbody').empty();
        $('#select-all-advances').prop('checked', false);
        var ta = 0, tu = 0, tav = 0, tap = 0;
        if (advances.length > 0) {
            $.each(advances, function(i, adv) {
                // Use used_in_this_payment from API response if available (for edit), otherwise use pre-fill map
                var pre = parseFloat(adv.used_in_this_payment !== undefined ? adv.used_in_this_payment : (paymentAdvancesMap[adv.id] || 0));
                var chk = pre > 0, amt = pre > 0 ? pre.toFixed(5) : '0.00';
                // Pre-checked: data-amount = what was applied in this payment (prevents the full balance inflating totalPriorityAvailable)
                var advDataAmt = chk ? pre : parseFloat(adv.available_balance || 0);
                tap += pre;
                tbody.append('<tr>' +
                    '<td><input type="checkbox" class="advance-checkbox" name="advance_ids[]" value="' + adv.id + '" data-amount="' + advDataAmt + '"' + (chk?' checked':'') + '></td>' +
                    '<td>' + adv.date + '</td><td>' + adv.reference_no + '</td><td>' + (adv.type || 'advance') + '</td>' +
                    '<td class="text-right">' + parseFloat(adv.amount).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(adv.used_amount || 0).toFixed(5) + '</td>' +
                    '<td class="text-right">' + parseFloat(adv.available_balance || 0).toFixed(5) + '</td>' +
                    '<td class="text-right"><span class="advance-applied-amount" id="advance-applied-' + adv.id + '">' + amt + '</span>' +
                    '<input type="hidden" name="advance_amounts[' + adv.id + ']" value="' + amt + '" id="advance-amount-' + adv.id + '"></td></tr>');
                ta += parseFloat(adv.amount); tu += parseFloat(adv.used_amount || 0); tav += parseFloat(adv.available_balance || 0);
            });
            $('#advances-section').show();
        } else {
            tbody.append('<tr><td colspan="8" class="text-center">No advances found for this customer</td></tr>');
            $('#advances-section').show();
        }
        $('#advances-total-amount').text(ta.toFixed(5)); $('#advances-total-used').text(tu.toFixed(5));
        $('#advances-total-available').text(tav.toFixed(5)); $('#advances-total-applied').text(tap.toFixed(5));
        onSectionLoaded();
    }

    /* ── Checkbox handlers (identical to add form) ───────────────── */
    $(document).on('change', '.invoice-checkbox', function() {
        var chk = $(this).is(':checked'), id = $(this).val();
        var pi = $(this).closest('tr').find('.payment-amount');
        var outstanding = parseFloat($(this).data('amount')) || 0;
        if (chk) {
            if (checkedInvoicesOrder.indexOf(id) === -1) checkedInvoicesOrder.push(id);
            pi.prop('disabled', false).val(outstanding.toFixed(5));
        } else {
            var idx = checkedInvoicesOrder.indexOf(id);
            if (idx > -1) checkedInvoicesOrder.splice(idx, 1);
            pi.prop('disabled', true).val('');
            $('#invoice-amount-' + id).val('0.00');
            $('#invoice-payment-' + id).text('0.00');
        }
        calculateTotalPayment();
    });
    $(document).on('change', '.return-checkbox', function() {
        var chk = $(this).is(':checked'), id = $(this).val(), avail = parseFloat($(this).data('amount')) || 0;
        if (chk) { $('#return-amount-' + id).val(avail.toFixed(5)); $('#return-used-' + id).text(avail.toFixed(5)); }
        else      { $('#return-amount-' + id).val('0.00');          $('#return-used-' + id).text('0.00'); }
        calculateTotalPayment();
    });
    $(document).on('change', '.creditmemo-checkbox', function() {
        var chk = $(this).is(':checked'), id = $(this).val(), avail = parseFloat($(this).data('amount')) || 0;
        if (chk) { $('#creditmemo-amount-' + id).val(avail.toFixed(5)); $('#creditmemo-applied-' + id).text(avail.toFixed(5)); }
        else      { $('#creditmemo-amount-' + id).val('0.00');          $('#creditmemo-applied-' + id).text('0.00'); }
        calculateTotalPayment();
    });
    $(document).on('change', '.serviceinvoice-checkbox', function() {
        var chk = $(this).is(':checked'), id = $(this).val();
        if (chk) { if (checkedInvoicesOrder.indexOf('si_' + id) === -1) checkedInvoicesOrder.push('si_' + id); }
        else      { var idx = checkedInvoicesOrder.indexOf('si_' + id); if (idx > -1) checkedInvoicesOrder.splice(idx, 1); $('#serviceinvoice-amount-' + id).val('0.00'); $('#serviceinvoice-payment-' + id).text('0.00'); }
        calculateTotalPayment();
    });
    $(document).on('change', '.advance-checkbox', function() {
        var chk = $(this).is(':checked'), id = $(this).val(), avail = parseFloat($(this).data('amount')) || 0;
        if (chk) { $('#advance-amount-' + id).val(avail.toFixed(5)); $('#advance-applied-' + id).text(avail.toFixed(5)); }
        else      { $('#advance-amount-' + id).val('0.00');           $('#advance-applied-' + id).text('0.00'); }
        calculateTotalPayment();
    });

    /* ── payment_amount: trigger full recalculate for proper validation ── */
    $(document).on('input blur', '#payment_amount', function() {
        var val = parseAcct($(this).val());
        $('#payment-amount-display').text(val.toFixed(5));
        calculateTotalPayment();
    });

    /* ── calculateTotalPayment — matches add-payment page logic exactly ── */
    function calculateTotalPayment() {
        var paymentAmount = parseAcct($('#payment_amount').val());

        // Reset all row amounts
        $('.invoice-payment-amount').text('0.00');       $('[id^="invoice-amount-"]').val('0.00');
        $('.return-used-amount').text('0.00');            $('[id^="return-amount-"]').val('0.00');
        $('.creditmemo-applied-amount').text('0.00');     $('[id^="creditmemo-amount-"]').val('0.00');
        $('.advance-applied-amount').text('0.00');        $('[id^="advance-amount-"]').val('0.00');
        $('.serviceinvoice-payment-amount').text('0.00'); $('[id^="serviceinvoice-amount-"]').val('0.00');

        var selectedInvoices      = $('.invoice-checkbox:checked');
        var selectedServiceInvoices = $('.serviceinvoice-checkbox:checked');
        var selectedAdvances      = $('.advance-checkbox:checked');
        var selectedReturns       = $('.return-checkbox:checked');
        var selectedCreditMemos   = $('.creditmemo-checkbox:checked');

        // Sum outstanding for all selected invoices + service invoices
        var totalInvoiceOutstanding = 0;
        selectedInvoices.each(function() { totalInvoiceOutstanding += parseFloat($(this).data('amount')) || 0; });
        selectedServiceInvoices.each(function() { totalInvoiceOutstanding += parseFloat($(this).data('amount')) || 0; });

        // Sum all priority (debit) sources
        var totalAdvancesAvailable = 0;
        selectedAdvances.each(function() { totalAdvancesAvailable += parseFloat($(this).data('amount')) || 0; });
        var totalReturnsAvailable = 0;
        selectedReturns.each(function() { totalReturnsAvailable += parseFloat($(this).data('amount')) || 0; });
        var totalCreditMemosAvailable = 0;
        selectedCreditMemos.each(function() { totalCreditMemosAvailable += parseFloat($(this).data('amount')) || 0; });
        var totalPriorityAvailable = totalAdvancesAvailable + totalReturnsAvailable + totalCreditMemosAvailable;

        // Priority settles invoices first; cash covers remaining — identical to add-payment page
        var priorityApplied    = Math.min(totalPriorityAvailable, totalInvoiceOutstanding);
        var remainingOutstanding = totalInvoiceOutstanding - priorityApplied;
        var paymentApplied     = Math.min(paymentAmount, remainingOutstanding);
        var totalAppliedToInvoices = priorityApplied + paymentApplied;

        // Greedy distribution across invoices in the order they were checked
        if (totalAppliedToInvoices > 0 && checkedInvoicesOrder.length > 0) {
            var remainingSettlement = totalAppliedToInvoices;
            for (var i = 0; i < checkedInvoicesOrder.length && remainingSettlement > 0.001; i++) {
                var item = checkedInvoicesOrder[i];
                if (item.indexOf('si_') === 0) {
                    var siId = item.substring(3);
                    var cbSI = $('.serviceinvoice-checkbox[value="' + siId + '"]');
                    var siOut = parseFloat(cbSI.data('amount')) || 0;
                    var siApply = Math.min(siOut, remainingSettlement);
                    $('#serviceinvoice-payment-' + siId).text(siApply.toFixed(5));
                    $('#serviceinvoice-amount-'  + siId).val(siApply.toFixed(5));
                    remainingSettlement -= siApply;
                } else {
                    var cbInv = $('.invoice-checkbox[value="' + item + '"]');
                    var invOut = parseFloat(cbInv.data('amount')) || 0;
                    var invApply = Math.min(invOut, remainingSettlement);
                    $('#invoice-payment-' + item).text(invApply.toFixed(5));
                    $('#invoice-amount-'  + item).val(invApply.toFixed(5));
                    remainingSettlement -= invApply;
                }
            }
        }

        // Distribute priority items up to priorityApplied (not the full pool)
        var priRem = priorityApplied;
        selectedAdvances.each(function() {
            var avail = parseFloat($(this).data('amount')) || 0;
            var amt   = Math.min(avail, priRem);
            $('#advance-applied-' + $(this).val()).text(amt.toFixed(5));
            $('#advance-amount-'  + $(this).val()).val(amt.toFixed(5));
            priRem -= amt;
        });
        selectedReturns.each(function() {
            var avail = parseFloat($(this).data('amount')) || 0;
            var amt   = Math.min(avail, priRem);
            $('#return-used-'   + $(this).val()).text(amt.toFixed(5));
            $('#return-amount-' + $(this).val()).val(amt.toFixed(5));
            priRem -= amt;
        });
        selectedCreditMemos.each(function() {
            var avail = parseFloat($(this).data('amount')) || 0;
            var amt   = Math.min(avail, priRem);
            $('#creditmemo-applied-' + $(this).val()).text(amt.toFixed(5));
            $('#creditmemo-amount-'  + $(this).val()).val(amt.toFixed(5));
            priRem -= amt;
        });

        // Update tfoot totals
        var at = 0; $('.advance-applied-amount').each(function() { at += parseFloat($(this).text())||0; });    $('#advances-total-applied').text(at.toFixed(5));
        var rt = 0; $('.return-used-amount').each(function() { rt += parseFloat($(this).text())||0; });        $('#returns-total-used').text(rt.toFixed(5));
        var ct = 0; $('.creditmemo-applied-amount').each(function() { ct += parseFloat($(this).text())||0; }); $('#creditmemo-total-applied').text(ct.toFixed(5));
        var it = 0; $('.invoice-payment-amount').each(function() { it += parseFloat($(this).text())||0; });
        var st = 0; $('.serviceinvoice-payment-amount').each(function() { st += parseFloat($(this).text())||0; });
        $('#total-payment-summary').text((it + st).toFixed(5));
        $('#si-total-payment').text(st.toFixed(5));

        // Total settlement = cash + ALL selected priority items (same display formula as add page)
        var totalPaymentAmount = paymentAmount + totalPriorityAvailable;

        // Remaining = applied-to-invoices minus total-settlement (negative = excess → new advance)
        var remainingAmount = totalAppliedToInvoices - totalPaymentAmount;
        var excessToAdvance = remainingAmount < -0.001 ? Math.abs(remainingAmount) : 0;

        // Update summary display
        $('#total-outstanding-selected').text(totalInvoiceOutstanding.toFixed(5));
        $('#priority-total-display').text(totalPriorityAvailable.toFixed(5));
        $('#payment-amount-display').text(totalPaymentAmount.toFixed(5));   // cash + priority
        $('#total-display').text(totalAppliedToInvoices.toFixed(5));
        $('#advance-from-payment').text(excessToAdvance.toFixed(5));
        $('#excess-to-advance-input').val(excessToAdvance.toFixed(5));
        $('#remaining-amount').text(remainingAmount.toFixed(5));

        updateRemainingAmountStyling(remainingAmount);
    }

    /* ── Summary update for initial pre-fill (no reset) — matches add-payment logic ── */
    function updateEditSummary() {
        // Read amounts already rendered in the rows by the display functions
        var ti = 0; $('.invoice-payment-amount').each(function() { ti += parseFloat($(this).text())||0; });
        var ts = 0; $('.serviceinvoice-payment-amount').each(function() { ts += parseFloat($(this).text())||0; });
        var tr = 0; $('.return-used-amount').each(function() { tr += parseFloat($(this).text())||0; });
        var tc = 0; $('.creditmemo-applied-amount').each(function() { tc += parseFloat($(this).text())||0; });
        var ta = 0; $('.advance-applied-amount').each(function() { ta += parseFloat($(this).text())||0; });

        // Outstanding of SELECTED invoices/service invoices
        var tInvOut = 0;
        $('.invoice-checkbox:checked').each(function() { tInvOut += parseFloat($(this).data('amount')) || 0; });
        $('.serviceinvoice-checkbox:checked').each(function() { tInvOut += parseFloat($(this).data('amount')) || 0; });

        var pa            = parseAcct($('#payment_amount').val());
        var priority      = ta + tr + tc;         // total priority items applied
        var totalApplied  = ti + ts;              // applied to invoices only
        var totalPayment  = pa + priority;        // total settlement (cash + priority)
        var diff          = totalApplied - totalPayment;
        var excessToAdv   = diff < -0.001 ? Math.abs(diff) : 0;

        // Update all display fields — same semantics as add-payment page
        $('#total-outstanding-selected').text(tInvOut.toFixed(5));
        $('#priority-total-display').text(priority.toFixed(5));
        $('#payment-amount-display').text(totalPayment.toFixed(5));  // cash + priority
        $('#total-display').text(totalApplied.toFixed(5));
        $('#advance-from-payment').text(excessToAdv.toFixed(5));
        $('#excess-to-advance-input').val(excessToAdv.toFixed(5));
        $('#total-payment-summary').text(totalApplied.toFixed(5));
        $('#advances-total-applied').text(ta.toFixed(5));
        $('#returns-total-used').text(tr.toFixed(5));
        $('#creditmemo-total-applied').text(tc.toFixed(5));
        $('#si-total-payment').text(ts.toFixed(5));
        $('#remaining-amount').text(diff.toFixed(5));

        updateRemainingAmountStyling(diff);
    }

    /* ── Remaining amount styling — identical to add-payment page ──── */
    function updateRemainingAmountStyling(remainingAmount) {
        var el  = $('#remaining-amount');
        var btn = $('#submit-btn');
        var selectedInvoices = $('.invoice-checkbox:checked, .serviceinvoice-checkbox:checked');

        if (selectedInvoices.length > 0 && Math.abs(remainingAmount) < 0.01) {
            // Green: exact settlement
            el.css({'background-color':'#d4edda','color':'#155724','padding':'2px 8px','border-radius':'3px','display':'inline-block'});
            if (!isLocked) btn.prop('disabled', false);
        } else if (selectedInvoices.length > 0 && remainingAmount < -0.01) {
            // Orange: overpayment — excess posted to customer advance
            el.css({'background-color':'#fff3cd','color':'#856404','padding':'2px 8px','border-radius':'3px','display':'inline-block'});
            if (!isLocked) btn.prop('disabled', false);
        } else {
            // Red: no invoices selected (partial payment is allowed, so this only fires when nothing is checked)
            el.css({'background-color':'#f8d7da','color':'#721c24','padding':'2px 8px','border-radius':'3px','display':'inline-block'});
            if (!isLocked) btn.prop('disabled', true);
        }
    }

    /* ── Select-all ──────────────────────────────────────────────── */
    $(document).on('change', '#select-all-invoices',        function() { $('.invoice-checkbox').prop('checked', $(this).is(':checked')).trigger('change'); });
    $(document).on('change', '#select-all-returns',         function() { $('.return-checkbox').prop('checked', $(this).is(':checked')).trigger('change'); });
    $(document).on('change', '#select-all-creditmemos',     function() { $('.creditmemo-checkbox').prop('checked', $(this).is(':checked')).trigger('change'); });
    $(document).on('change', '#select-all-serviceinvoices', function() { $('.serviceinvoice-checkbox').prop('checked', $(this).is(':checked')).trigger('change'); });
    $(document).on('change', '#select-all-advances',        function() { $('.advance-checkbox').prop('checked', $(this).is(':checked')).trigger('change'); });

    /* ── Locked: disable all inputs ─────────────────────────────── */
    <?php if (!empty($is_closed) && $is_closed): ?>
    $('input, select, textarea').prop('disabled', true);
    $('#submit-btn').hide();
    <?php endif; ?>

});
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i> <?= lang('Edit Customer Payment') ?></h2>
        <?php if (!empty($is_closed) && $is_closed): ?>
        <span class="label label-danger" style="font-size:13px; padding:5px 10px; margin-left:15px;">
            <i class="fa fa-lock"></i> <?= lang('Payment is closed and cannot be edited') ?>
        </span>
        <?php endif; ?>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php echo admin_form_open('customers/edit_payment_save', ['data-toggle' => 'validator', 'role' => 'form']); ?>
                <input type="hidden" name="edit_customer_payment" value="1">
                <input type="hidden" name="payment_id" value="<?= $payment_ref->id ?>">
                <input type="hidden" id="customer_hidden" name="customer" value="<?= $payment_ref->customer_id ?>">

                <!-- Row 1: Date | Reference No | Customer -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Date', 'date') ?>
                            <?php $date_display = !empty($payment_ref->date) ? date('d/m/Y', strtotime($payment_ref->date)) : date('d/m/Y'); ?>
                            <input type="text" name="date" id="date" value="<?= $date_display ?>" class="form-control input-tip date" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reference_no', 'reference_no') ?>
                            <input type="text" name="reference_no" id="reference_no" value="<?= htmlspecialchars($payment_ref->reference_no ?? '') ?>" class="form-control input-tip" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('customer', 'customer_display') ?>
                            <?php
                            $customer_display = '';
                            if (!empty($customers)) {
                                foreach ($customers as $c) {
                                    if ($c->id == $payment_ref->customer_id) {
                                        $customer_display = $c->company . ' (' . $c->name . ')';
                                        if (!empty($c->sequence_code)) $customer_display .= ' - ' . $c->sequence_code;
                                        break;
                                    }
                                }
                            }
                            ?>
                            <input type="text" class="form-control" readonly value="<?= htmlspecialchars($customer_display) ?>" style="background-color:#f5f5f5; cursor:default;">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Ledger | Payment Amount | Note -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Ledger', 'ledger') ?>
                            <?php
                            $ldg_opts = ['' => lang('select') . ' ' . lang('ledger')];
                            if (!empty($ledgers)) { foreach ($ledgers as $l) { $ldg_opts[$l->id] = $l->name; } }
                            echo form_dropdown('ledger', $ldg_opts, ($payment_ref->transfer_from_ledger ?? ''), 'id="ledger_id" class="form-control input-tip select" style="width:100%;"');
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('payment_amount', 'payment_amount') ?>
                            <input type="text" name="payment_amount" id="payment_amount" value="<?= number_format((float)($payment_ref->amount ?? 0), 2) ?>" class="form-control input-tip" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('note', 'note') ?>
                            <input type="text" name="note" id="note" value="<?= htmlspecialchars($payment_ref->note ?? '') ?>" class="form-control" placeholder="Enter payment notes...">
                        </div>
                    </div>
                </div>

                <!-- Customer Information Box -->
                <div id="customer-info" class="row" style="display:none;">
                    <div class="col-md-12">
                        <div class="box box-primary" style="border-color:#dbd2d2; padding:10px;">
                            <div class="box-body" style="background-color:#f8f9fa;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong style="color:#1976d2;"><?= lang('customer') ?>:</strong><br>
                                        <span id="customer-name" style="font-size:14px; color:#333;"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong style="color:#388e3c;"><?= lang('credit_limit') ?>:</strong><br>
                                        <span id="credit-limit" style="font-size:14px; color:#388e3c; font-weight:bold;"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong style="color:#f57c00;"><?= lang('current_balance') ?>:</strong><br>
                                        <span id="current-balance" style="font-size:14px; color:#f57c00; font-weight:bold;"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong style="color:#7b1fa2;"><?= lang('remaining_limit') ?>:</strong><br>
                                        <span id="remaining-limit" style="font-size:14px; color:#7b1fa2; font-weight:bold;"></span>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:10px;">
                                    <div class="col-md-3">
                                        <strong style="color:#0097a7;"><?= lang('available_advance') ?>:</strong><br>
                                        <span id="available-advance" style="font-size:14px; color:#0097a7; font-weight:bold;"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong style="color:#c2185b;"><?= lang('payment_term') ?>:</strong><br>
                                        <span id="payment-term" style="font-size:14px; color:#c2185b; font-weight:bold;"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Advances Section -->
                <div id="advances-section" class="row" style="display:none; margin-top:20px;">
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Customer Advances</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="advances-table" class="table table-striped table-bordered">
                                        <thead style="background-color:#d1ecf1;">
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="select-all-advances"></th>
                                                <th>Date</th><th>Reference No</th><th>Type</th>
                                                <th class="text-right">Advance Amount</th>
                                                <th class="text-right">Used Amount</th>
                                                <th class="text-right">Available Balance</th>
                                                <th class="text-right">Applied Amount</th>
                                            </tr>
                                        </thead>
                                        <tfoot style="background-color:#bee5eb; font-weight:bold;">
                                            <tr>
                                                <td colspan="4"><strong>Advances Total</strong></td>
                                                <td class="text-right" id="advances-total-amount">0.00</td>
                                                <td class="text-right" id="advances-total-used">0.00</td>
                                                <td class="text-right" id="advances-total-available">0.00</td>
                                                <td class="text-right" id="advances-total-applied">0.00</td>
                                            </tr>
                                        </tfoot>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Invoices Section -->
                <div id="invoices-section" class="row" style="display:none; margin-top:20px;">
                    <div class="col-md-12">
                        <div class="box box-primary">
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="invoices-table" class="table table-striped table-bordered">
                                        <thead style="background-color:#f5f5f5;">
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="select-all-invoices"></th>
                                                <th>Date</th><th>Reference No</th><th>Type</th>
                                                <th class="text-right">Total Amount</th>
                                                <th class="text-right">Paid Amount</th>
                                                <th class="text-right">Outstanding</th>
                                                <th class="text-right">Payment Amount</th>
                                            </tr>
                                        </thead>
                                        <tfoot style="background-color:#e8f5e8; font-weight:bold;">
                                            <tr>
                                                <td colspan="4"><strong>Invoices Total</strong></td>
                                                <td class="text-right" id="total-amount">0.00</td>
                                                <td class="text-right" id="total-paid">0.00</td>
                                                <td class="text-right" id="total-outstanding">0.00</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Invoices Section -->
                <div id="serviceinvoice-section" class="row" style="display:none; margin-top:20px;">
                    <div class="col-md-12">
                        <div class="box box-info">
                            <div class="box-header with-border">
                                <h3 class="box-title">Service Invoices</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="serviceinvoice-table" class="table table-striped table-bordered">
                                        <thead style="background-color:#d1ecf1;">
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="select-all-serviceinvoices"></th>
                                                <th>Date</th><th>Reference No</th><th>Type</th>
                                                <th class="text-right">Total Amount</th>
                                                <th class="text-right">Paid Amount</th>
                                                <th class="text-right">Outstanding</th>
                                                <th class="text-right">Payment Amount</th>
                                            </tr>
                                        </thead>
                                        <tfoot style="background-color:#bee5eb; font-weight:bold;">
                                            <tr>
                                                <td colspan="4"><strong>Service Invoices Total</strong></td>
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
                </div>

                <!-- Customer Returns Section -->
                <!--<div id="returns-section" class="row" style="display:none; margin-top:20px;">
                    <div class="col-md-12">
                        <div class="box box-warning">
                            <div class="box-header with-border">
                                <h3 class="box-title">Customer Returns</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="returns-table" class="table table-striped table-bordered">
                                        <thead style="background-color:#fff3cd;">
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="select-all-returns"></th>
                                                <th>Date</th><th>Reference No</th><th>Type</th>
                                                <th class="text-right">Return Amount</th>
                                                <th class="text-right">Paid Amount</th>
                                                <th class="text-right">Outstanding</th>
                                                <th class="text-right">Used Amount</th>
                                            </tr>
                                        </thead>
                                        <tfoot style="background-color:#ffeaa7; font-weight:bold;">
                                            <tr>
                                                <td colspan="4"><strong>Returns Total</strong></td>
                                                <td class="text-right" id="returns-total-amount">0.00</td>
                                                <td class="text-right" id="returns-total-paid">0.00</td>
                                                <td class="text-right" id="returns-total-outstanding">0.00</td>
                                                <td class="text-right" id="returns-total-used">0.00</td>
                                            </tr>
                                        </tfoot>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->

                <!-- Customer Credit Memos Section -->
                <!--<div id="creditmemo-section" class="row" style="display:none; margin-top:20px;">
                    <div class="col-md-12">
                        <div class="box box-success">
                            <div class="box-header with-border">
                                <h3 class="box-title">Customer Credit Memos</h3>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table id="creditmemo-table" class="table table-striped table-bordered">
                                        <thead style="background-color:#d4edda;">
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="select-all-creditmemos"></th>
                                                <th>Date</th><th>Reference No</th><th>Type</th>
                                                <th class="text-right">Credit Amount</th>
                                                <th class="text-right">Used Amount</th>
                                                <th class="text-right">Available Balance</th>
                                                <th class="text-right">Applied Amount</th>
                                            </tr>
                                        </thead>
                                        <tfoot style="background-color:#c3e6cb; font-weight:bold;">
                                            <tr>
                                                <td colspan="4"><strong>Credit Memo Total</strong></td>
                                                <td class="text-right" id="creditmemo-total-amount">0.00</td>
                                                <td class="text-right" id="creditmemo-total-used">0.00</td>
                                                <td class="text-right" id="creditmemo-total-available">0.00</td>
                                                <td class="text-right" id="creditmemo-total-applied">0.00</td>
                                            </tr>
                                        </tfoot>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->

                <!-- Payment Summary Well -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="well" style="background-color:#f8f9fa; border:1px solid #dee2e6; border-radius:5px; padding:15px; margin-bottom:20px;">

                            <div class="row">
                                <div class="col-md-12" style="margin-bottom:15px;">
                                    <div class="form-group">
                                        <label class="control-label" style="font-weight:bold; font-size:14px;">
                                            Total Outstanding (Selected): <span id="total-outstanding-selected" style="color:#f39c12; font-size:16px; font-weight:bold;">0.00</span>
                                        </label><br>
                                        <small class="text-muted">Sum of selected invoice outstanding minus selected returns/credit memos</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12" style="margin-bottom:15px;">
                                    <div class="form-group">
                                        <label class="control-label" style="font-weight:bold; font-size:14px;">Payment Breakdown:</label><br>
                                        <div style="background-color:#f8f9fa; padding:10px; border-radius:5px; margin-top:5px;">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <small class="text-muted">Priority Amount (Advances/Returns/Credit Memos):</small><br>
                                                    <span id="priority-total-display" style="color:#28a745; font-size:16px; font-weight:bold;">0.00</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted">Total Settlement Amount:</small><br>
                                                    <span id="payment-amount-display" style="color:#007bff; font-size:16px; font-weight:bold;">0.00</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted">Total Applied:</small><br>
                                                    <span id="total-display" style="color:#2196F3; font-size:16px; font-weight:bold;">0.00</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted">Advance Created:</small><br>
                                                    <span id="advance-from-payment" style="color:#ff9800; font-size:16px; font-weight:bold;">0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted">Priority amounts are applied first, then payment amount covers remaining balance</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label" style="font-weight:bold; font-size:14px;">
                                            Remaining Amount: <span id="remaining-amount" style="font-size:16px; font-weight:bold;">0.00</span>
                                        </label><br>
                                        <small class="text-muted">Difference between invoices selected and total payment. Zero = exact match (green). Negative = excess posted to customer advance (orange).</small>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <input type="hidden" name="excess_to_advance" id="excess-to-advance-input" value="0.00">

                <!-- Submit -->
                <div class="row" style="margin-top:10px;">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?php if (empty($is_closed) || !$is_closed): ?>
                            <button type="submit" id="submit-btn" class="btn btn-primary">
                                <?= lang('submit') ?>
                            </button>
                            <?php endif; ?>
                            <a href="<?= admin_url('customers/list_payments') ?>" class="btn btn-default">
                                <?= lang('cancel') ?>
                            </a>
                            <!-- Delete Payment Button (Finance Manager Only) -->
                            <button type="button" id="delete-btn" class="btn btn-danger" style="margin-left: 10px;" data-toggle="modal" data-target="#deletePaymentModal">
                                <i class="fa fa-trash"></i> <?= lang('delete') ?>
                            </button>
                        </div>
                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Payment Confirmation Modal -->
<div class="modal fade" id="deletePaymentModal" tabindex="-1" role="dialog" aria-labelledby="deletePaymentLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="deletePaymentLabel"><i class="fa fa-warning"></i> <?= lang('Confirm Delete') ?></h5>
            </div>
            <div class="modal-body">
                <p><strong><?= lang('Warning') ?>:</strong> <?= lang('delete_payment_warning') ?></p>
                <p><?= lang('This action will') ?>:</p>
                <ul>
                    <li><?= lang('Delete this payment record') ?></li>
                    <li><?= lang('Reverse all paid invoice amounts') ?></li>
                    <li><?= lang('Reverse all return and memo usage') ?></li>
                    <li><?= lang('Delete all journal entries') ?></li>
                    <li><?= lang('This action cannot be undone') ?></li>
                </ul>
                <p style="color: red;"><strong><?= lang('Are you sure you want to proceed?') ?></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?= lang('cancel') ?></button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn" onclick="deletePaymentConfirmed()">
                    <i class="fa fa-trash"></i> <?= lang('Yes, Delete Payment') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function deletePaymentConfirmed() {
    var paymentId = <?= isset($payment_ref->id) ? json_encode($payment_ref->id) : 'null' ?>;
    
    if (!paymentId) {
        alert('<?= lang("Payment ID not found") ?>');
        return;
    }

    var csrfTokenName = '<?= $this->security->get_csrf_token_name() ?>';
    var csrfTokenValue = '<?= $this->security->get_csrf_hash() ?>';
    var data = { payment_id: paymentId };
    data[csrfTokenName] = csrfTokenValue;

    $.ajax({
        url: '<?= admin_url('customers/delete_payment') ?>',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message || '<?= lang("Payment deleted successfully") ?>');
                window.location.href = '<?= admin_url('customers/list_payments') ?>';
            } else {
                alert(response.message || '<?= lang("Failed to delete payment") ?>');
            }
        },
        error: function() {
            alert('<?= lang("Error deleting payment") ?>');
        }
    });
}
</script>
