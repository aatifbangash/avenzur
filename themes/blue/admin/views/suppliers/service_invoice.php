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
            addServiceRow();
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
        $(document).on('input', '.amount', function() {
            var row = $(this).closest('tr');
            calculateRowTotals(row);
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

        function addServiceRow() {
            var rowCount = $('#serviceTable tbody tr').length + 1;
            var ledgers = <?php echo json_encode($ledgers ?? []); ?>;
            var ledgerOptions = '<option value="">Select Ledger Account</option>';
            for (var i = 0; i < ledgers.length; i++) {
                var ledger = ledgers[i];
                ledgerOptions += '<option value="' + ledger.id + '">' + ledger.name +' - ' + ledger.code + '</option>';
            }

            var newRow = `
                <tr>
                    <td class="text-center">${rowCount}</td>
                    <td>
                        <select class="form-control ledger-account" name="ledger_account[]" required>
                            ${ledgerOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control amount" name="amount[]" placeholder="0.00" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control vat" name="vat[]" placeholder="0.00" readonly>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control total" name="total[]" placeholder="0.00" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            <i class="fa fa-minus"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#serviceTable tbody').append(newRow);
        }

        function calculateRowTotals(row) {
            var amount = parseFloat(row.find('.amount').val()) || 0;
            var vatRate = 0.15; // 15%

            var vatAmount = amount * vatRate;
            var total = amount + vatAmount;

            row.find('.vat').val(vatAmount.toFixed(2));
            row.find('.total').val(total.toFixed(2));
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
                                        <th style="width: 15%;">VAT (15%)</th>
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

