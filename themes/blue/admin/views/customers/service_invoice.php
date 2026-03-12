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

        // Service type change handler
        $('#serviceTable').on('change', '.service-type', function() {
            var row = $(this).closest('tr');
            var serviceType = $(this).val();
            console.log('Service Type changed to: ' + serviceType);
            updateServiceColumns(row, serviceType);
        });

        // Form validation before submit
        $('form').on('submit', function(e) {
            var isValid = true;
            var errorMessages = [];

            // Check each service row
            $('#serviceTable tbody tr').each(function(index) {
                var row = index;
                var serviceTypeElement = row.find('.service-type');
                var selectedIndex = serviceTypeElement.prop('selectedIndex');
                var serviceType = selectedIndex > 0 ? serviceTypeElement.find('option').eq(selectedIndex).val() : '';
                var amount = parseFloat(row.find('.amount').val()) || 0;
                var quantity = parseFloat(row.find('.quantity').val()) || 0;

                if (!serviceType || serviceType === '') {
                    isValid = false;
                    errorMessages.push('Row ' + (index + 1) + ': Please select a service type');
                }

                if (serviceType === 'transportation') {
                    var fromCity = row.find('.from-field').val();
                    var toCity = row.find('.to-field').val();
                    if (!fromCity || !toCity) {
                        isValid = false;
                        errorMessages.push('Row ' + (index + 1) + ': From City and To City are required for Transportation');
                    }
                }

                if (serviceType === 'storage_fees') {
                    var fromDate = row.find('.from-date-field').val();
                    var toDate = row.find('.to-date-field').val();
                    if (!fromDate || !toDate) {
                        isValid = false;
                        errorMessages.push('Row ' + (index + 1) + ': From Date and To Date are required for Storage Fees');
                    }
                }

                if (amount <= 0) {
                    isValid = false;
                    errorMessages.push('Row ' + (index + 1) + ': Amount must be greater than 0');
                }

                if (quantity <= 0) {
                    isValid = false;
                    errorMessages.push('Row ' + (index + 1) + ': Quantity must be greater than 0');
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
        $(document).on('input', '.amount, .quantity, .vat', function() {
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
            var newRow = `
                <tr>
                    <td class="text-center">${rowCount}</td>
                    <td>
                        <select class="form-control service-type" name="service_type[]" required>
                            <option value="">Select Service Type</option>
                            <option value="transportation">Transportation</option>
                            <option value="storage_fees">Storage Fees</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control from-field" name="from_city[]" placeholder="From City" required style="display: none;">
                        <input type="date" class="form-control from-date-field" name="from_date[]" style="display: none;">
                    </td>
                    <td>
                        <input type="text" class="form-control to-field" name="to_city[]" placeholder="To City" required style="display: none;">
                        <input type="date" class="form-control to-date-field" name="to_date[]" style="display: none;">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control amount" name="amount[]" placeholder="0.00" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control quantity" name="quantity[]" placeholder="1" value="1" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control unit-price" name="unit_price[]" placeholder="0.00" readonly>
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

        function updateServiceColumns(row, serviceType) {
            // Hide all conditional fields first
            row.find('.from-field, .to-field, .from-date-field, .to-date-field').hide();

            if (serviceType === 'transportation') {
                // Show text inputs for cities
                row.find('.from-field').show().attr('placeholder', 'From City').prop('required', true);
                row.find('.to-field').show().attr('placeholder', 'To City').prop('required', true);
            } else if (serviceType === 'storage_fees') {
                // Show date inputs
                row.find('.from-date-field').show();
                row.find('.to-date-field').show();
            }
        }

        function calculateRowTotals(row) {
            var amount = parseFloat(row.find('.amount').val()) || 0;
            var quantity = parseFloat(row.find('.quantity').val()) || 0;
            var vatRate = 0.15; // 15%

            var subtotal = amount * quantity;
            var vatAmount = subtotal * vatRate;
            var total = subtotal + vatAmount;
            var unitPrice = subtotal;

            row.find('.vat').val(vatAmount.toFixed(2));
            row.find('.total').val(total.toFixed(2));
            row.find('.unit-price').val(unitPrice.toFixed(2));
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
            echo admin_form_open_multipart('customers/service_invoice', $attrib)
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
                            <?= lang('customer', 'posupplier'); ?>
                            <?php
                            $sp[''] = '';
                            foreach ($customers as $customer) {
                                $sp[$customer->id] = $customer->company. ' ('. $customer->name.')';
                            }
                            echo form_dropdown('customer', $sp, ($memo_data->customer_id), 'id="customer_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" style="width:100%;" '); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?= lang('description', 'description'); ?>
                            <?php echo form_textarea('description', ($memo_data->description ?? ''), 'class="form-control" id="description"'); ?>       
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
                                        <th style="width: 12%;">Service Type</th>
                                        <th style="width: 12%;">From</th>
                                        <th style="width: 12%;">To</th>
                                        <th style="width: 10%;">Unit Price</th>
                                        <th style="width: 8%;">Quantity</th>
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

