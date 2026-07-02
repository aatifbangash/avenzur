<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
.service-table th {
    background-color: #f5f5f5;
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
}
.service-table td {
    vertical-align: middle;
}
.remove-row {
    padding: 2px 8px;
}
.add-service-btn {
    margin-bottom: 15px;
}
</style>
<script>
    $(document).ready(function () {
        // Initialize with one row
        if ($('#serviceTable tbody tr').length === 0) {
            <?php if(isset($memo_entries_data) && !empty($memo_entries_data)): ?>
                var existingData = <?php echo json_encode($memo_entries_data); ?>;
                for (var i = 0; i < existingData.length; i++) {
                    var entry = existingData[i];
                    var vatRate = entry.vat_rate || 15;
                    var vatAmount = entry.vat_amount || entry.vat || 0;
                    var amount = entry.amount || (entry.payment_amount - vatAmount);
                    var total = entry.total || entry.payment_amount || 0;
                    addServiceRow(entry.ledger_account_id, amount, vatRate, vatAmount, total, true);
                }
            <?php else: ?>
                addServiceRow();
            <?php endif; ?>
        }

        // Add row button
        $('#addRowBtn').click(function() {
            addServiceRow();
        });

        // Form validation before submit
        $('form').on('submit', function(e) {
            var isValid = true;
            var errorMessages = [];

            // Check each service row
            $('#serviceTable tbody tr').each(function(index) {
                var row = $(this);
                var ledgerElement = row.find('.ledger-account');
                var selectedLedger = ledgerElement.val();
                var amount = parseFloat(row.find('.amount').val()) || 0;

                /*if (!selectedLedger || selectedLedger === '') {
                    isValid = false;
                    errorMessages.push('Row ' + (index + 1) + ': Please select a ledger account');
                }*/

                if (amount <= 0) {
                    isValid = false;
                    errorMessages.push('Row ' + (index + 1) + ': Amount must be greater than 0');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errorMessages.join('\n'));
                return false;
            }

            return true;
        });

        // Calculate VAT and totals
        $('#serviceTable').on('input', '.amount', function() {
            var row = $(this).closest('tr');
            calculateRowTotals(row);
        });

        // VAT rate change
        $('#serviceTable').on('change', '.vat-rate', function() {
            console.log('VAT rate changed to:', $(this).val());
            var row = $(this).closest('tr');
            calculateRowTotals(row, $(this));
        });

        // Remove row
        $(document).on('click', '.remove-row', function() {
            if ($('#serviceTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
                updateRowNumbers();
            } else {
                alert('At least one row is required');
            }
        });

        function addServiceRow(ledgerId = '', amount = '', vatRate = '15', vatAmount = '', total = '', skipCalculation = false) {
            var rowCount = $('#serviceTable tbody tr').length + 1;
            var ledgers = <?php echo json_encode($ledgers ?? []); ?>;
            var ledgerOptions = '<option value="">Select Ledger Account</option>';
            for (var i = 0; i < ledgers.length; i++) {
                var ledger = ledgers[i];
                var selected = ledgerId == ledger.id ? 'selected' : '';
                ledgerOptions += '<option value="' + ledger.id + '" ' + selected + '>' + ledger.name +' - ' + ledger.code + '</option>';
            }

            var vatRateOptions = '<select class="form-control vat-rate" name="vat_rate[]" style="margin-bottom: 5px;">';
            vatRateOptions += '<option value="0" ' + (vatRate == '0' || vatRate == 0 ? 'selected' : '') + '>0%</option>';
            vatRateOptions += '<option value="15" ' + (vatRate == '15' || vatRate == 15 ? 'selected' : '') + '>15%</option>';
            vatRateOptions += '</select>';

            var newRow = `
                <tr>
                    <td class="text-center">${rowCount}</td>
                    <td>
                        <select class="form-control ledger-account" name="ledger_account[]" required>
                            ${ledgerOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control amount" name="amount[]" placeholder="0.00" value="${amount}" required>
                    </td>
                    <td>
                        ${vatRateOptions}
                        <input type="number" step="0.01" class="form-control vat-amount" name="vat_amount[]" placeholder="0.00" value="${vatAmount}" readonly style="margin-top: 5px;">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control total" name="total[]" placeholder="0.00" value="${total}" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fa fa-minus"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#serviceTable tbody').append(newRow);
            // Calculate totals for the new row (skip for existing data)
            if (!skipCalculation) {
                calculateRowTotals($('#serviceTable tbody tr:last'));
            }
        }

        function calculateRowTotals(row, vatRateElement = null) {
            console.log('calculateRowTotals called for row:', row);
            var amount = parseFloat(row.find('.amount').val()) || 0;
            var vatRateSelect = vatRateElement || row.find('.vat-rate');
            var vatRateValue = vatRateSelect.val();
            console.log('VAT rate value:', vatRateValue, 'type:', typeof vatRateValue);
            var vatRate = (vatRateValue !== "" && vatRateValue !== null) ? parseFloat(vatRateValue) : 15;
            var vatRateDecimal = vatRate / 100;

            console.log('Calculated VAT rate:', vatRate, 'decimal:', vatRateDecimal);
            var vatAmount = amount * vatRateDecimal;
            var total = amount + vatAmount;

            console.log('VAT amount:', vatAmount, 'total:', total);
            var vatAmountField = row.find('.vat-amount');
            console.log('VAT amount field found:', vatAmountField.length);
            vatAmountField.val(vatAmount.toFixed(2));
            row.find('.total').val(total.toFixed(2));
            console.log('Fields updated');
        }

        function updateRowNumbers() {
            $('#serviceTable tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('service_invoice'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('suppliers/service_invoice', $attrib)
            ?>
            <div class="col-lg-12">

                <?php if ($error) { ?>
                    <div class="alert alert-danger">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?= $error; ?>
                    </div>
                <?php } ?>

                <!-- Simplified Header Form -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('date', 'podate'); ?>
                            <?php echo form_input('date', ($memo_data->date ?? ''), 'class="form-control input-tip date" id="podate" required="required"'); ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reference_no', 'poref'); ?>
                            <?php echo form_input('reference_no', ($memo_data->reference_no ?? ''), 'class="form-control input-tip" id="poref" required="required"'); ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('supplier', 'posupplier'); ?>
                            <?php
                            $sp[''] = '';
                            foreach ($suppliers as $supplier) {
                                $sp[$supplier->id] = $supplier->company. ' ('. $supplier->name.')';
                            }
                            echo form_dropdown('supplier', $sp, ($memo_data->supplier_id), 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" required="required" style="width:100%;" '); ?>
                        </div>
                    </div>
                </div>

                <!-- Description Field -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?= lang('description', 'description'); ?>
                            <?php echo form_textarea('description', ($memo_data->description ?? ''), 'class="form-control" id="description" rows="3" placeholder="Enter service description"'); ?>
                        </div>
                    </div>
                </div>

                <!-- Service Invoice Table -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <button type="button" id="addRowBtn" class="btn btn-success btn-sm">
                                <i class="fa fa-plus"></i> Add Service Row
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped service-table" id="serviceTable">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 5%;">#</th>
                                        <th style="width: 25%;">Ledger Account</th>
                                        <th style="width: 15%;">Amount</th>
                                        <th style="width: 20%;">VAT Rate & Amount</th>
                                        <th style="width: 15%;">Total</th>
                                        <th class="text-center" style="width: 5%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic rows will be added here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="add_payment">
                                <?= lang('Add Service Invoice') ?>
                            </button>
                        </div>
                    </div>
                </div>

                <?php
                if(isset($memo_data) && !empty($memo_data)){
                    ?>
                        <input type="hidden" name="memo_id" value="<?= $memo_data->id; ?>" />
                        <input type="hidden" name="request_type" value="update" />
                    <?php
                }else{
                    ?>
                        <input type="hidden" name="request_type" value="add" />
                    <?php
                }
                ?>
            </div>

            <?php echo form_close(); ?>

        </div>
    </div>
</div>

