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
        if ($('#pettyCashTable tbody tr').length === 0) {
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

        // Auto-populate VAT number when supplier name changes
        $(document).on('blur', '.supplier-name', function() {
            var supplierName = $(this).val().trim();
            var row = $(this).closest('tr');
            var vatNumberField = row.find('.vat-number');

            if (supplierName !== '') {
                // Make AJAX call to fetch VAT number
                $.ajax({
                    url: '<?= admin_url('suppliers/get_supplier_vat_number') ?>',
                    type: 'POST',
                    data: {
                        supplier_name: supplierName,
                        '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.vat_number) {
                            vatNumberField.val(response.vat_number);
                        }
                    },
                    error: function() {
                        console.log('Error fetching VAT number');
                    }
                });
            }
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
            var rowCount = $('#pettyCashTable tbody tr').length + 1;
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
                        <input type="text" class="form-control supplier-name" name="supplier_name[]" placeholder="Enter supplier name">
                    </td>
                    <td>
                        <input type="text" class="form-control vat-number" name="vat_number[]" placeholder="VAT Number">
                    </td>
                    <td>
                        <input type="text" class="form-control description" name="description[]" placeholder="Discretion">
                    </td>
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
            $('#pettyCashTable tbody').append(newRow);
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
            $('#pettyCashTable tbody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Populate existing data when editing
        <?php if(isset($memo_entries_data) && !empty($memo_entries_data)): ?>
        $(document).ready(function() {
            var existingData = <?php echo json_encode($memo_entries_data); ?>;
            $('#pettyCashTable tbody').empty(); // Clear existing rows

            existingData.forEach(function(entry, index) {
                var rowCount = index + 1;
                var ledgers = <?php echo json_encode($ledgers ?? []); ?>;
                var ledgerOptions = '<option value="">Select Ledger Account</option>';
                for (var i = 0; i < ledgers.length; i++) {
                    var ledger = ledgers[i];
                    var selected = (ledger.id == entry.ledger_account) ? 'selected' : '';
                    ledgerOptions += '<option value="' + ledger.id + '" ' + selected + '>' + ledger.name +' - ' + ledger.code + '</option>';
                }

                var newRow = `
                    <tr>
                        <td class="text-center">${rowCount}</td>
                        <td>
                            <input type="text" class="form-control supplier-name" name="supplier_name[]" placeholder="Enter supplier name" value="${entry.supplier_name || ''}">
                        </td>
                        <td>
                            <input type="text" class="form-control vat-number" name="vat_number[]" placeholder="VAT Number" value="${entry.vat_number || ''}">
                        </td>
                        <td>
                            <input type="text" class="form-control description" name="description[]" placeholder="Discretion" value="${entry.description || ''}">
                        </td>
                        <td>
                            <select class="form-control ledger-account" name="ledger_account[]" required>
                                ${ledgerOptions}
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control amount" name="amount[]" placeholder="0.00" value="${(entry.payment_amount - entry.vat).toFixed(2)}" required>
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control vat" name="vat[]" placeholder="0.00" value="${entry.vat.toFixed(2)}" readonly>
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control total" name="total[]" placeholder="0.00" value="${entry.payment_amount.toFixed(2)}" readonly>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                <i class="fa fa-minus"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $('#pettyCashTable tbody').append(newRow);
            });
        });
        <?php endif; ?>
    });
</script>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('petty_cash'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('suppliers/list_petty_cash') ?>">
                                <i class="fa fa-list"></i> <?= lang('Petty Cash List') ?>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('suppliers/petty_cash', $attrib)
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('date', 'podate'); ?>
                            <?php echo form_input('date', ($memo_data->date ?? ''), 'class="form-control input-tip date" id="podate" required="required"'); ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('reference_no', 'poref'); ?>
                            <?php echo form_input('reference_no', ($memo_data->reference_no ?? ''), 'class="form-control input-tip" id="poref" required="required"'); ?>
                        </div>
                    </div>
                </div>

                <!-- Description Field -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?= lang('description', 'description'); ?>
                            <?php echo form_textarea('note', ($memo_data->description ?? ''), 'class="form-control" id="description" rows="3" placeholder="Enter description"'); ?>
                        </div>
                    </div>
                </div>

                <!-- Main Supplier Selection -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('Main Supplier', 'main_supplier'); ?>
                            <select class="form-control" name="main_supplier_id" id="main_supplier">
                                <option value="">Select Main Supplier</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= $supplier->id ?>" <?= (isset($memo_data->main_supplier_id) && $memo_data->main_supplier_id == $supplier->id) ? 'selected' : '' ?>>
                                        <?= $supplier->name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Service Invoice Table -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <button type="button" id="addRowBtn" class="btn btn-success btn-sm">
                                <i class="fa fa-plus"></i> Add Petty Cash Row
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped petty-cash-table" id="pettyCashTable">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 5%;">#</th>
                                        <th style="width: 15%;">Supplier Name</th>
                                        <th style="width: 12%;">VAT Number</th>
                                        <th style="width: 15%;">Discretion</th>
                                        <th style="width: 15%;">Ledger Account</th>
                                        <th style="width: 10%;">Amount</th>
                                        <th style="width: 10%;">VAT (15%)</th>
                                        <th style="width: 10%;">Total</th>
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
                                <?= isset($memo_data) && !empty($memo_data) ? lang('Update Petty Cash') : lang('Add Petty Cash') ?>
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

