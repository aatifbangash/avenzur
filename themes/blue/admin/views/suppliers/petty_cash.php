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
.petty-cash-vat-cell {
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.petty-cash-vat-cell .vat-rate.form-control {
    display: inline-block;
    width: auto;
    min-width: 3.75rem;
    flex: 0 0 auto;
    padding: 6px 2px 6px 4px;
    height: 34px;
    font-size: 12px;
    line-height: 1.4;
}
.petty-cash-vat-cell .vat {
    flex: 1;
    min-width: 70px;
    padding: 4px 8px;
    font-size: 12px;
}
#pettyCashTable th.col-amount,
#pettyCashTable td:nth-child(7),
#pettyCashTable th.col-total,
#pettyCashTable td:nth-child(9) {
    min-width: 110px;
    width: 110px;
}
#pettyCashTable .amount,
#pettyCashTable .vat,
#pettyCashTable .total {
    min-width: 100px;
    text-align: right;
    font-size: 12px;
    padding: 6px 8px;
}
</style>
<script>
    $(document).ready(function () {
        function refreshPettyCashReference() {
            var dateVal = $('#podate').val();
            if (!dateVal) {
                return;
            }
            $.get('<?= admin_url('suppliers/next_petty_cash_reference'); ?>', { date: dateVal }, function (res) {
                if (res && res.reference_no) {
                    $('#poref').val(res.reference_no);
                }
            }, 'json');
        }

        $('#podate').on('change changeDate', refreshPettyCashReference);

        function normalizePettyCashVatRate($context) {
            $context.find('.vat-rate').addClass('skip').each(function () {
                var $el = $(this);
                if ($el.data('select2')) {
                    $el.select2('destroy');
                }
            });
        }

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

        // Calculate VAT and totals when amount changes
        $(document).on('input', '#pettyCashTable .amount', function() {
            var row = $(this).closest('tr');
            calculateRowTotals(row);
        });

        // Calculate when VAT rate changes
        $(document).on('change', '#pettyCashTable .vat-rate', function() {
            console.log('VAT rate changed to:', $(this).val());
            var row = $(this).closest('tr');
            console.log('here...',row);
            calculateRowTotals(row, $(this));
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
            if ($('#pettyCashTable tbody tr').length > 1) {
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
                        <input type="text" class="form-control invoice-no" name="invoice_no[]" placeholder="Invoice No.">
                    </td>
                    <td>
                        <input type="text" class="form-control vat-number" name="vat_number[]" placeholder="VAT Number">
                    </td>
                    <td>
                        <input type="text" class="form-control description" name="description[]" placeholder="Description">
                    </td>
                    <td>
                        <select class="form-control ledger-account" name="ledger_account[]" required>
                            ${ledgerOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control amount" name="amount[]" placeholder="0.00" required>
                    </td>
                    <td class="petty-cash-vat-cell">
                        <select class="form-control skip vat-rate" name="vat_rate[]">
                            <option value="0">0%</option>
                            <option value="15" selected>15%</option>
                        </select>
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
            normalizePettyCashVatRate($('#pettyCashTable tbody tr:last'));
            // Calculate totals for the new row
            calculateRowTotals($('#pettyCashTable tbody tr:last'));
            // Ensure VAT rate is set to 15 for new rows
            $('#pettyCashTable tbody tr:last .vat-rate').val(15);
        }

        function calculateRowTotals(row, vatRateElement = null) {
            var amountInput = row.find('.amount');
            var vatRateSelect = vatRateElement || row.find('.vat-rate');
            var vatInput = row.find('.vat');
            var totalInput = row.find('.total');

            console.log('calculateRowTotals called');
            console.log('Amount input value:', amountInput.val());
            console.log('VAT rate select value:', vatRateSelect.val());

            var amount = parseFloat(amountInput.val()) || 0;
            var vatRateValue = vatRateSelect.val();
            var vatRate = (vatRateValue !== "" && vatRateValue !== null) ? parseFloat(vatRateValue) : 15;
            var vatRatePercent = vatRate / 100;

            console.log('Calculated vatRate:', vatRate);
            console.log('Calculated vatRatePercent:', vatRatePercent);

            var vatAmount = amount * vatRatePercent;
            var total = amount + vatAmount;

            console.log('Calculated vatAmount:', vatAmount);
            console.log('Calculated total:', total);

            vatInput.val(vatAmount.toFixed(2));
            totalInput.val(total.toFixed(2));
            
            // Ensure VAT rate select has a valid value
            if (vatRateSelect.val() === "" || vatRateSelect.val() === null) {
                vatRateSelect.val(15);
            }
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

                // Calculate VAT rate from existing data
                var amount = entry.payment_amount - entry.vat;
                var vatRate = 0;
                if (amount > 0) {
                    vatRate = Math.round((entry.vat / amount) * 100);
                }

                var newRow = `
                    <tr>
                        <td class="text-center">${rowCount}</td>
                        <td>
                            <input type="text" class="form-control supplier-name" name="supplier_name[]" placeholder="Enter supplier name" value="${entry.supplier_name || ''}">
                        </td>
                        <td>
                            <input type="text" class="form-control invoice-no" name="invoice_no[]" placeholder="Invoice No" value="${entry.invoice_no || ''}">
                        </td>
                        <td>
                            <input type="text" class="form-control vat-number" name="vat_number[]" placeholder="VAT Number" value="${entry.vat_number || ''}">
                        </td>
                        <td>
                            <input type="text" class="form-control description" name="description[]" placeholder="Description" value="${entry.description || ''}">
                        </td>
                        <td>
                            <select class="form-control ledger-account" name="ledger_account[]" required>
                                ${ledgerOptions}
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control amount" name="amount[]" placeholder="0.00" value="${(entry.payment_amount - entry.vat).toFixed(2)}" required>
                        </td>
                        <td class="petty-cash-vat-cell">
                            <select class="form-control skip vat-rate" name="vat_rate[]" required>
                                <option value="0" ${vatRate === 0 ? 'selected' : ''}>0%</option>
                                <option value="15" ${vatRate !== 0 ? 'selected' : ''}>15%</option>
                            </select>
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
                var lastRow = $('#pettyCashTable tbody tr:last');
                normalizePettyCashVatRate(lastRow);
                lastRow.find('.vat-rate').val(vatRate);
            });

            normalizePettyCashVatRate($('#pettyCashTable'));
            // Calculate totals for all existing rows
            $('#pettyCashTable tbody tr').each(function() {
                calculateRowTotals($(this));
            });
        });
        <?php endif; ?>

        // Calculate totals for initial row on page load
        $(document).ready(function() {
            normalizePettyCashVatRate($('#pettyCashTable'));
            $('#pettyCashTable tbody tr').each(function() {
                calculateRowTotals($(this));
            });
        });

        // Run after global select2 init in core.js
        setTimeout(function () {
            normalizePettyCashVatRate($('#pettyCashTable'));
        }, 0);
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
                            <?php
                            $ref_value = $memo_data->reference_no ?? ($next_reference_no ?? '');
                            echo form_input(
                                'reference_no',
                                $ref_value,
                                'class="form-control input-tip" id="poref" readonly="readonly"'
                            );
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Petty Cash ledger (supplier is fixed to Petty Cash SUP-00265) -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= lang('ledger_account', 'petty_cash_ledger_id'); ?>
                            <select class="form-control select" name="petty_cash_ledger_id" id="petty_cash_ledger_id" required="required">
                                <option value=""><?= lang('select') . ' ' . lang('ledger_account'); ?></option>
                                <?php
                                $selected_petty_ledger = isset($memo_data->ledger_account) ? (int) $memo_data->ledger_account : 0;
                                $petty_ledger_ids = array_map(function ($l) { return (int) $l->id; }, $petty_cash_ledgers ?? []);
                                if ($selected_petty_ledger && !in_array($selected_petty_ledger, $petty_ledger_ids, true)) {
                                    $selected_petty_ledger = 0;
                                }
                                foreach ($petty_cash_ledgers ?? [] as $ledger): ?>
                                    <option value="<?= (int) $ledger->id ?>" <?= $selected_petty_ledger === (int) $ledger->id ? 'selected' : '' ?>>
                                        <?= $ledger->code ? $ledger->code . ' — ' : '' ?><?= $ledger->name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="main_supplier_id" value="<?= (int) ($petty_cash_supplier_id ?? 0) ?>">

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
                                        <th style="width: 12%;">Supplier Name</th>
                                        <th style="width: 10%;">Invoice No.</th>
                                        <th style="width: 10%;">VAT Number</th>
                                        <th style="width: 12%;">Description</th>
                                        <th style="width: 12%;">Ledger Account</th>
                                        <th class="col-amount">Amount</th>
                                        <th style="width: 10%;">VAT Rate & Amount</th>
                                        <th class="col-total">Total</th>
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

