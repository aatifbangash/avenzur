<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
#customer {
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

/* Compact table styling for slimmer rows */
#invoices-table td, #invoices-table th,
#returns-table td, #returns-table th,
#creditmemo-table td, #creditmemo-table th,
#serviceinvoice-table td, #serviceinvoice-table th {
    padding: 3px 6px !important;
    line-height: 1.1 !important;
    vertical-align: middle !important;
    font-size: 12px !important;
}

#invoices-table tbody tr,
#returns-table tbody tr,
#creditmemo-table tbody tr,
#serviceinvoice-table tbody tr {
    height: 28px !important;
}

#invoices-table .payment-amount {
    height: 24px !important;
    padding: 1px 4px !important;
    font-size: 11px !important;
}

#invoices-table input[type="checkbox"],
#returns-table input[type="checkbox"],
#creditmemo-table input[type="checkbox"],
#serviceinvoice-table input[type="checkbox"] {
    margin: 0 !important;
    transform: scale(0.8);
}

#invoices-table thead th,
#returns-table thead th,
#creditmemo-table thead th,
#serviceinvoice-table thead th {
    font-size: 11px !important;
    font-weight: bold !important;
    padding: 4px 6px !important;
}

#invoices-table tfoot td,
#returns-table tfoot td,
#creditmemo-table tfoot td,
#serviceinvoice-table tfoot td {
    padding: 3px 6px !important;
    line-height: 1.1 !important;
    font-size: 12px !important;
    vertical-align: middle !important;
}
</style>

<script>
    $(document).ready(function() {
        var paymentInvoicesMap = <?= json_encode($paymentInvoicesMap ?? []) ?>;
        var paymentServiceInvoicesMap = <?= json_encode($paymentServiceInvoicesMap ?? []) ?>;
        var paymentCreditMemosMap = <?= json_encode($paymentCreditMemosMap ?? []) ?>;
        var paymentReturnsMap = <?= json_encode($paymentReturnsMap ?? []) ?>;

        // Initialize date picker
        $('#date').datetimepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true
        });

        // Load existing payment data on page load
        loadExistingPaymentData();

        // Function to load and pre-fill existing payment data
        function loadExistingPaymentData() {
            var customer_id = $('#customer').val();
            if (customer_id) {
                loadCustomerInvoices(customer_id);
                loadCustomerReturns(customer_id);
                loadCustomerCreditMemos(customer_id);
                loadCustomerServiceInvoices(customer_id);
            }
        }

        // Load customer invoices
        function loadCustomerInvoices(customer_id) {
            $.ajax({
                url: '<?= admin_url('customers/get_customer_invoices') ?>',
                type: 'GET',
                data: { customer_id: customer_id, payment_id: <?= $payment_ref->id ?> },
                dataType: 'json',
                success: function(invoices) {
                    displayInvoices(invoices);
                },
                error: function() {
                    $('#invoices-section').hide();
                }
            });
        }

        // Display invoices
        function displayInvoices(invoices) {
            var tbody = $('#invoices-table tbody');
            tbody.empty();
            
            if (invoices.length > 0) {
                $.each(invoices, function(index, invoice) {
                    var paidInThisPayment = paymentInvoicesMap[invoice.id] || 0;
                    var row = '<tr>' +
                        '<td><input type="checkbox" class="invoice-checkbox" name="invoice_ids[]" value="' + invoice.id + '" data-amount="' + invoice.outstanding_amount + '" ' + (paidInThisPayment > 0 ? 'checked' : '') + '></td>' +
                        '<td>' + invoice.date + '</td>' +
                        '<td>' + invoice.id + '</td>' +
                        '<td>' + invoice.type + '</td>' +
                        '<td class="text-right">' + parseFloat(invoice.grand_total).toFixed(5) + '</td>' +
                        '<td class="text-right">' + parseFloat(invoice.total_paid).toFixed(5) + '</td>' +
                        '<td class="text-right">' + parseFloat(invoice.outstanding_amount).toFixed(5) + '</td>' +
                        '<td class="text-right"><span class="invoice-payment-amount" id="invoice-payment-' + invoice.id + '">' + parseFloat(paidInThisPayment).toFixed(2) + '</span>' +
                        '<input type="hidden" name="invoice_amounts[' + invoice.id + ']" value="' + parseFloat(paidInThisPayment).toFixed(2) + '" id="invoice-amount-' + invoice.id + '"></td>' +
                    '</tr>';
                    tbody.append(row);
                });
                $('#invoices-section').show();
            } else {
                tbody.append('<tr><td colspan="8" class="text-center">No invoices found</td></tr>');
            }
        }

        // Load customer returns
        function loadCustomerReturns(customer_id) {
            $.ajax({
                url: '<?= admin_url('customers/get_customer_returns') ?>',
                type: 'GET',
                data: { customer_id: customer_id, payment_id: <?= $payment_ref->id ?> },
                dataType: 'json',
                success: function(returns) {
                    displayReturns(returns);
                },
                error: function() {
                    $('#returns-section').hide();
                }
            });
        }

        // Display returns
        function displayReturns(returns) {
            var tbody = $('#returns-table tbody');
            tbody.empty();
            
            if (returns.length > 0) {
                $.each(returns, function(index, return_item) {
                    var usedInThisPayment = paymentReturnsMap[return_item.id] || 0;
                    var row = '<tr>' +
                        '<td><input type="checkbox" class="return-checkbox" name="return_ids[]" value="' + return_item.id + '" data-amount="' + return_item.outstanding_amount + '" ' + (usedInThisPayment > 0 ? 'checked' : '') + '></td>' +
                        '<td>' + return_item.date + '</td>' +
                        '<td>' + return_item.reference_no + '</td>' +
                        '<td>' + return_item.type + '</td>' +
                        '<td class="text-right">' + parseFloat(return_item.grand_total).toFixed(5) + '</td>' +
                        '<td class="text-right">' + parseFloat(return_item.total_paid || 0).toFixed(5) + '</td>' +
                        '<td class="text-right">' + parseFloat(return_item.outstanding_amount || 0).toFixed(5) + '</td>' +
                        '<td class="text-right"><span class="return-used-amount" id="return-used-' + return_item.id + '">' + parseFloat(usedInThisPayment).toFixed(2) + '</span>' +
                        '<input type="hidden" name="return_amounts[' + return_item.id + ']" value="' + parseFloat(usedInThisPayment).toFixed(2) + '" id="return-amount-' + return_item.id + '"></td>' +
                    '</tr>';
                    tbody.append(row);
                });
                $('#returns-section').show();
            } else {
                tbody.append('<tr><td colspan="8" class="text-center">No returns found</td></tr>');
            }
        }

        // Load customer credit memos
        function loadCustomerCreditMemos(customer_id) {
            $.ajax({
                url: '<?= admin_url('customers/get_customer_credit_memos') ?>',
                type: 'GET',
                data: { customer_id: customer_id, payment_id: <?= $payment_ref->id ?> },
                dataType: 'json',
                success: function(creditmemos) {
                    displayCreditMemos(creditmemos);
                },
                error: function() {
                    $('#creditmemo-section').hide();
                }
            });
        }

        // Display credit memos
        function displayCreditMemos(creditmemos) {
            var tbody = $('#creditmemo-table tbody');
            tbody.empty();
            
            if (creditmemos.length > 0) {
                $.each(creditmemos, function(index, creditmemo) {
                    var appliedInThisPayment = paymentCreditMemosMap[creditmemo.id] || 0;
                    var row = '<tr>' +
                        '<td><input type="checkbox" class="creditmemo-checkbox" name="creditmemo_ids[]" value="' + creditmemo.id + '" data-amount="' + creditmemo.available_balance + '" ' + (appliedInThisPayment > 0 ? 'checked' : '') + '></td>' +
                        '<td>' + creditmemo.date + '</td>' +
                        '<td>' + creditmemo.reference_no + '</td>' +
                        '<td class="text-right">' + parseFloat(creditmemo.grand_total).toFixed(5) + '</td>' +
                        '<td class="text-right">' + parseFloat(creditmemo.used_amount || 0).toFixed(5) + '</td>' +
                        '<td class="text-right">' + parseFloat(creditmemo.available_balance).toFixed(5) + '</td>' +
                        '<td class="text-right"><span class="creditmemo-applied-amount" id="creditmemo-applied-' + creditmemo.id + '">' + parseFloat(appliedInThisPayment).toFixed(2) + '</span>' +
                        '<input type="hidden" name="creditmemo_amounts[' + creditmemo.id + ']" value="' + parseFloat(appliedInThisPayment).toFixed(2) + '" id="creditmemo-amount-' + creditmemo.id + '"></td>' +
                    '</tr>';
                    tbody.append(row);
                });
                $('#creditmemo-section').show();
            } else {
                tbody.append('<tr><td colspan="7" class="text-center">No credit memos found</td></tr>');
            }
        }

        // Load service invoices
        function loadCustomerServiceInvoices(customer_id) {
            $.ajax({
                url: '<?= admin_url('customers/get_customer_service_invoices') ?>',
                type: 'GET',
                data: { customer_id: customer_id, payment_id: <?= $payment_ref->id ?> },
                dataType: 'json',
                success: function(invoices) {
                    displayServiceInvoices(invoices);
                },
                error: function() {
                    $('#serviceinvoice-section').hide();
                }
            });
        }

        // Display service invoices
        function displayServiceInvoices(invoices) {
            var tbody = $('#serviceinvoice-table tbody');
            tbody.empty();
            
            if (invoices.length > 0) {
                $.each(invoices, function(index, invoice) {
                    var paidInThisPayment = paymentServiceInvoicesMap[invoice.id] || 0;
                    var row = '<tr>' +
                        '<td><input type="checkbox" class="serviceinvoice-checkbox" name="service_invoice_ids[]" value="' + invoice.id + '" data-amount="' + invoice.outstanding_amount + '" ' + (paidInThisPayment > 0 ? 'checked' : '') + '></td>' +
                        '<td>' + invoice.date + '</td>' +
                        '<td>' + invoice.reference_no + '</td>' +
                        '<td class="text-right">' + parseFloat(invoice.grand_total).toFixed(5) + '</td>' +
                        '<td class="text-right">' + parseFloat(invoice.total_paid || 0).toFixed(5) + '</td>' +
                        '<td class="text-right">' + parseFloat(invoice.outstanding_amount).toFixed(5) + '</td>' +
                        '<td class="text-right"><span class="serviceinvoice-payment-amount" id="serviceinvoice-payment-' + invoice.id + '">' + parseFloat(paidInThisPayment).toFixed(2) + '</span>' +
                        '<input type="hidden" name="service_invoice_amounts[' + invoice.id + ']" value="' + parseFloat(paidInThisPayment).toFixed(2) + '" id="serviceinvoice-amount-' + invoice.id + '"></td>' +
                    '</tr>';
                    tbody.append(row);
                });
                $('#serviceinvoice-section').show();
            } else {
                tbody.append('<tr><td colspan="7" class="text-center">No service invoices found</td></tr>');
            }
        }

        // Handle checkbox changes
        $(document).on('change', '.invoice-checkbox, .return-checkbox, .creditmemo-checkbox, .serviceinvoice-checkbox', function() {
            var invoice_id = $(this).val();
            var is_checked = $(this).is(':checked');
            var input_id = '#invoice-amount-' + invoice_id;

            if ($(this).hasClass('return-checkbox')) {
                input_id = '#return-amount-' + invoice_id;
            } else if ($(this).hasClass('creditmemo-checkbox')) {
                input_id = '#creditmemo-amount-' + invoice_id;
            } else if ($(this).hasClass('serviceinvoice-checkbox')) {
                input_id = '#serviceinvoice-amount-' + invoice_id;
            }

            if (is_checked) {
                $(input_id).val($(this).data('amount').toFixed(2));
            } else {
                $(input_id).val('0.00');
            }

            recalculate();
        });

        // Recalculate totals
        function recalculate() {
            var totalInvApplied = 0;
            var totalReturnUsed = 0;
            var totalCMApplied = 0;
            var totalSIPayment = 0;

            // Sum invoices
            $('.invoice-checkbox:checked').each(function() {
                var amount = parseFloat($('#invoice-amount-' + $(this).val()).val()) || 0;
                totalInvApplied += amount;
            });

            // Sum returns
            $('.return-checkbox:checked').each(function() {
                var amount = parseFloat($('#return-amount-' + $(this).val()).val()) || 0;
                totalReturnUsed += amount;
            });

            // Sum credit memos
            $('.creditmemo-checkbox:checked').each(function() {
                var amount = parseFloat($('#creditmemo-amount-' + $(this).val()).val()) || 0;
                totalCMApplied += amount;
            });

            // Sum service invoices
            $('.serviceinvoice-checkbox:checked').each(function() {
                var amount = parseFloat($('#serviceinvoice-amount-' + $(this).val()).val()) || 0;
                totalSIPayment += amount;
            });

            var totalApplied = totalInvApplied + totalReturnUsed + totalCMApplied + totalSIPayment;

            // Update display
            $('#total-payment-summary').text(totalApplied.toFixed(2));
            $('#total-invoices-applied').text(totalInvApplied.toFixed(2));
            $('#total-returns-used').text(totalReturnUsed.toFixed(2));
            $('#total-creditmemos-applied').text(totalCMApplied.toFixed(2));
            $('#total-serviceinvoices-payment').text(totalSIPayment.toFixed(2));
        }

        // Initial calculation
        recalculate();
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i> <?= lang('Edit Customer Payment') ?></h2>
        <?php if ($is_closed): ?>
            <div style="display: inline-block; margin-left: 20px;">
                <span class="label label-danger"><i class="fa fa-lock"></i> <?= lang('This payment is locked and cannot be edited') ?></span>
            </div>
        <?php endif; ?>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php echo admin_form_open('customers/edit_payment_save', ['data-toggle' => 'validator', 'role' => 'form']); ?>
                <input type="hidden" name="payment_id" value="<?= $payment_ref->id ?>">
                <input type="hidden" name="customer" id="customer" value="<?= $payment_ref->customer_id ?>">

                <!-- Row 1: Date | Reference No | Customer -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Date', 'date'); ?>
                            <?php $date_display = !empty($payment_ref->date) ? date('d/m/Y', strtotime($payment_ref->date)) : date('d/m/Y');  ?>
                            <?= form_input('date', $date_display, 'class="form-control input-tip date" id="date" required="required"' . ($is_closed ? ' disabled' : '')); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reference_no', 'reference_no'); ?>
                            <?= form_input('reference_no', ($payment_ref->reference_no ?? ''), 'class="form-control input-tip" id="reference_no" required="required"' . ($is_closed ? ' disabled' : '')); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('customer', 'customer_display'); ?>
                            <?php
                            $customer_display = '';
                            if (isset($payment_ref)) {
                                foreach ($customers as $c) {
                                    if ($c->id == $payment_ref->customer_id) {
                                        $customer_display = $c->company . ' (' . $c->name . ')';
                                        if (isset($c->sequence_code)) {
                                            $customer_display .= ' - ' . $c->sequence_code;
                                        }
                                        break;
                                    }
                                }
                            }
                            ?>
                            <input type="text" class="form-control" readonly value="<?= htmlspecialchars($customer_display); ?>" style="background-color: #f5f5f5; cursor: default;">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Ledger | Payment Amount | Note -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Ledger', 'ledger'); ?>
                            <?php
                            $ldg_opts = ['' => lang('select') . ' ' . lang('ledger')];
                            foreach ($ledgers as $l) {
                                $ldg_opts[$l->id] = $l->name;
                            }
                            echo form_dropdown('ledger', $ldg_opts, ($payment_ref->transfer_from_ledger ?? ''), 'id="ledger_id" class="form-control input-tip select" style="width:100%;"' . ($is_closed ? ' disabled' : ''));
                            ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('payment_amount', 'payment_amount'); ?>
                            <?php 
                                $payment_amt = isset($payment_ref->amount) ? (float)$payment_ref->amount : 0;
                            ?>
                            <?= form_input('payment_amount', number_format($payment_amt, 2), 'class="form-control text-right acct-money" id="payment_amount" type="text" inputmode="decimal" autocomplete="off" placeholder="0.00"' . ($is_closed ? ' disabled' : '')); ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('note', 'note'); ?>
                            <?= form_input('note', ($payment_ref->note ?? ''), 'class="form-control" id="note" placeholder="Payment notes..."' . ($is_closed ? ' disabled' : '')); ?>
                        </div>
                    </div>
                </div>

                <!-- Invoices Section -->
                <div id="invoices-section" style="display:none; margin-top:15px;">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-file-text-o"></i> <?= lang('Invoices') ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="invoices-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="4%"><input type="checkbox" id="select-all-invoices" <?= $is_closed ? 'disabled' : '' ?>></th>
                                            <th><?= lang('Date') ?></th>
                                            <th><?= lang('Invoice ID') ?></th>
                                            <th><?= lang('Type') ?></th>
                                            <th class="text-right"><?= lang('Total') ?></th>
                                            <th class="text-right"><?= lang('Paid') ?></th>
                                            <th class="text-right"><?= lang('Outstanding') ?></th>
                                            <th class="text-right"><?= lang('Apply Amount') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th class="text-right"><?= lang('Total Invoices:') ?></th>
                                            <th></th>
                                            <th></th>
                                            <th class="text-right"><strong id="total-invoices-applied">0.00</strong></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Returns Section -->
                <div id="returns-section" style="display:none; margin-top:15px;">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-reply"></i> <?= lang('Returns') ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="returns-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="4%"><input type="checkbox" id="select-all-returns" <?= $is_closed ? 'disabled' : '' ?>></th>
                                            <th><?= lang('Date') ?></th>
                                            <th><?= lang('Reference') ?></th>
                                            <th><?= lang('Type') ?></th>
                                            <th class="text-right"><?= lang('Total') ?></th>
                                            <th class="text-right"><?= lang('Used') ?></th>
                                            <th class="text-right"><?= lang('Outstanding') ?></th>
                                            <th class="text-right"><?= lang('Use Amount') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th class="text-right"><?= lang('Total Returns:') ?></th>
                                            <th></th>
                                            <th></th>
                                            <th class="text-right"><strong id="total-returns-used">0.00</strong></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credit Memos Section -->
                <div id="creditmemo-section" style="display:none; margin-top:15px;">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-file"></i> <?= lang('Credit Memos') ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="creditmemo-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="4%"><input type="checkbox" id="select-all-creditmemos" <?= $is_closed ? 'disabled' : '' ?>></th>
                                            <th><?= lang('Date') ?></th>
                                            <th><?= lang('Reference') ?></th>
                                            <th class="text-right"><?= lang('Total') ?></th>
                                            <th class="text-right"><?= lang('Used') ?></th>
                                            <th class="text-right"><?= lang('Available') ?></th>
                                            <th class="text-right"><?= lang('Apply Amount') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th class="text-right"><?= lang('Total Credit Memos:') ?></th>
                                            <th></th>
                                            <th></th>
                                            <th class="text-right"><strong id="total-creditmemos-applied">0.00</strong></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Invoices Section -->
                <div id="serviceinvoice-section" style="display:none; margin-top:15px;">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i class="fa fa-wrench"></i> <?= lang('Service Invoices') ?></h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="serviceinvoice-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="4%"><input type="checkbox" id="select-all-serviceinvoices" <?= $is_closed ? 'disabled' : '' ?>></th>
                                            <th><?= lang('Date') ?></th>
                                            <th><?= lang('Reference') ?></th>
                                            <th class="text-right"><?= lang('Total') ?></th>
                                            <th class="text-right"><?= lang('Paid') ?></th>
                                            <th class="text-right"><?= lang('Outstanding') ?></th>
                                            <th class="text-right"><?= lang('Apply Amount') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th class="text-right"><?= lang('Total Service Invoices:') ?></th>
                                            <th></th>
                                            <th></th>
                                            <th class="text-right"><strong id="total-serviceinvoices-payment">0.00</strong></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="row" style="margin-top:15px; padding:10px 15px; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:4px;">
                    <div class="col-md-12">
                        <div style="font-size: 14px;">
                            <strong><?= lang('Total Invoices Applied:') ?> <span style="color:#0097a7; font-size:16px;" id="total-payment-summary">0.00</span></strong>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row" style="margin-top:15px;">
                    <div class="col-md-12">
                        <?php if (!$is_closed): ?>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?= lang('Update Payment') ?></button>
                        <?php endif; ?>
                        <a href="<?= admin_url('customers/list_payments') ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> <?= lang('Back to List') ?></a>
                    </div>
                </div>

                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>