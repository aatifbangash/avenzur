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
    var transportRates = <?= json_encode($transport_rates ?? new stdClass()) ?>;
    var transportRegions = <?= json_encode($transport_regions ?? []) ?>;
    var transportCapacities = <?= json_encode($transport_capacities ?? new stdClass()) ?>;
    var isEditMode = <?= !empty($memo_data) ? 'true' : 'false' ?>;
    var existingServiceEntries = <?= !empty($memo_entries_data) ? json_encode($memo_entries_data) : '[]' ?>;

    var serviceInvoiceMinDate = "<?= $this->sma->hrsd(date('Y-m-d')); ?>";

    $(document).ready(function () {
        var datepickerOpts = {
            format: site.dateFormats.js_sdate,
            fontAwesome: true,
            language: 'sma',
            todayBtn: 1,
            autoclose: 1,
            minView: 2
        };
        if (!isEditMode) {
            datepickerOpts.startDate = serviceInvoiceMinDate;
        }
        $('#podate').datetimepicker(datepickerOpts);

        if ($('#serviceTable tbody tr').length === 0) {
            if (existingServiceEntries && existingServiceEntries.length > 0) {
                existingServiceEntries.forEach(function(entry) {
                    populateServiceRow(entry);
                });
            } else {
                addServiceRow(true);
                var firstRow = $('#serviceTable tbody tr:first');
                firstRow.find('select.service-type').val('transportation');
                updateServiceColumns(firstRow, 'transportation');
            }
        }

        $('#addRowBtn').click(function() {
            addServiceRow(false);
        });

        $('#serviceTable').on('change', 'select.service-type', function() {
            var row = $(this).closest('tr');
            updateServiceColumns(row, $(this).val());
        });

        $('#serviceTable').on('change', 'select.to-region, select.capacity-field', function() {
            var row = $(this).closest('tr');
            if (row.find('select.service-type').val() === 'transportation') {
                applyTransportPrice(row);
            }
        });

        $('form').on('submit', function(e) {
            var isValid = true;
            var errorMessages = [];

            var invoiceDateStr = $('#podate').val();
            if (!isEditMode && invoiceDateStr) {
                var invoiceMoment = moment(invoiceDateStr, site.dateFormats.js_sdate.toUpperCase());
                if (invoiceMoment.isValid() && invoiceMoment.isBefore(moment().startOf('day'), 'day')) {
                    isValid = false;
                    errorMessages.push('Invoice date cannot be earlier than today.');
                }
            }

            $('#serviceTable tbody tr').each(function(index) {
                var row = $(this);
                var serviceType = row.find('select.service-type').val();
                var amount = parseFloat(row.find('.amount').val()) || 0;
                var quantity = parseFloat(row.find('.quantity').val()) || 0;

                if (!serviceType) {
                    isValid = false;
                    errorMessages.push('Row ' + (index + 1) + ': Please select a service type');
                }

                if (quantity <= 0) {
                    isValid = false;
                    errorMessages.push('Row ' + (index + 1) + ': Quantity must be greater than 0');
                }

                if (serviceType === 'transportation') {
                    if (!row.find('select.to-region').val() || !row.find('select.capacity-field').val()) {
                        isValid = false;
                        errorMessages.push('Row ' + (index + 1) + ': To region and capacity are required');
                    }
                } else if (amount <= 0) {
                    isValid = false;
                    errorMessages.push('Row ' + (index + 1) + ': Unit price must be greater than 0');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following errors:\n\n' + errorMessages.join('\n'));
                return false;
            }

            return true;
        });

        $('#serviceTable').on('input', '.amount, .quantity', function() {
            var row = $(this).closest('tr');
            calculateRowTotals(row);
        });

        $(document).on('click', '.remove-row', function() {
            if ($('#serviceTable tbody tr').length > 1) {
                $(this).closest('tr').remove();
                updateRowNumbers();
            } else {
                alert('At least one row is required');
            }
        });

        function capacityKeyFromLabel(label) {
            if (!label) {
                return '';
            }
            for (var key in transportCapacities) {
                if (transportCapacities.hasOwnProperty(key) && transportCapacities[key] === label) {
                    return key;
                }
            }
            return label;
        }

        function populateServiceRow(entry) {
            addServiceRow(false);
            var row = $('#serviceTable tbody tr:last');
            var serviceType = entry.service_type || '';
            var quantity = parseFloat(entry.quantity) || 1;
            var unitValue = parseFloat(entry.unit_value) || 0;
            var unitPrice = quantity > 0 ? (unitValue / quantity) : unitValue;
            var vat = parseFloat(entry.vat) || 0;
            var total = parseFloat(entry.payment_amount) || (unitValue + vat);

            row.find('select.service-type').val(serviceType);
            updateServiceColumns(row, serviceType, true);

            if (serviceType === 'transportation') {
                row.find('select.from-region').val(entry.from_val || 'Jeddah');
                row.find('select.to-region').val(entry.to_val || '');
                row.find('select.capacity-field').val(capacityKeyFromLabel(entry.name || ''));
                row.find('.amount').val(unitPrice.toFixed(2));
            } else if (serviceType === 'storage_fees') {
                row.find('.from-date-field').val(entry.from_val || '');
                row.find('.to-date-field').val(entry.to_val || '');
                row.find('.amount').val(unitPrice.toFixed(2));
            } else {
                row.find('.amount').val(unitPrice.toFixed(2));
            }

            row.find('.quantity').val(quantity);
            row.find('.unit-price').val(unitValue.toFixed(2));
            row.find('.vat').val(vat.toFixed(2));
            row.find('.total').val(total.toFixed(2));
        }

        function buildRegionOptions(selected) {
            var html = '<option value="">Select Region</option>';
            transportRegions.forEach(function(region) {
                var sel = region === selected ? ' selected' : '';
                html += '<option value="' + region + '"' + sel + '>' + region + '</option>';
            });
            return html;
        }

        function buildCapacityOptions(selected) {
            var html = '<option value="">Select Capacity</option>';
            for (var key in transportCapacities) {
                if (!transportCapacities.hasOwnProperty(key)) {
                    continue;
                }
                var sel = key === selected ? ' selected' : '';
                html += '<option value="' + key + '"' + sel + '>' + transportCapacities[key] + '</option>';
            }
            return html;
        }

        function getTransportPrice(toRegion, capacity) {
            if (!transportRates[toRegion] || transportRates[toRegion][capacity] === undefined) {
                return null;
            }
            return parseFloat(transportRates[toRegion][capacity]);
        }

        function applyTransportPrice(row) {
            var toRegion = row.find('select.to-region').val();
            var capacity = row.find('select.capacity-field').val();
            var price = getTransportPrice(toRegion, capacity);

            if (price !== null) {
                row.find('.amount').val(price.toFixed(2));
            } else {
                row.find('.amount').val('');
            }

            calculateRowTotals(row);
        }

        function addServiceRow(defaultFromJeddah) {
            var rowCount = $('#serviceTable tbody tr').length + 1;
            var fromOptions = buildRegionOptions(defaultFromJeddah ? 'Jeddah' : '');
            var toOptions = buildRegionOptions('');
            var capacityOptions = buildCapacityOptions('');

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
                        <select class="form-control skip from-region from-field" name="from_city[]" style="display: none;">
                            ${fromOptions}
                        </select>
                        <input type="date" class="form-control from-date-field" name="from_date[]" style="display: none;">
                    </td>
                    <td>
                        <select class="form-control skip to-region to-field" name="to_city[]" style="display: none;">
                            ${toOptions}
                        </select>
                        <input type="date" class="form-control to-date-field" name="to_date[]" style="display: none;">
                    </td>
                    <td>
                        <select class="form-control skip capacity-field" name="capacity[]" style="display: none;">
                            ${capacityOptions}
                        </select>
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

        function updateServiceColumns(row, serviceType, skipRecalc) {
            row.find('select.from-region, select.to-region, select.capacity-field, .from-date-field, .to-date-field').hide().prop('required', false);

            if (serviceType === 'transportation') {
                row.find('select.from-region').show().prop('required', true);
                row.find('select.to-region').show().prop('required', true);
                row.find('select.capacity-field').show().prop('required', true);
                if (!row.find('select.from-region').val()) {
                    row.find('select.from-region').val('Jeddah');
                }
                row.find('.amount').prop('readonly', true);
                if (!skipRecalc) {
                    applyTransportPrice(row);
                }
            } else if (serviceType === 'storage_fees') {
                row.find('.from-date-field').show().prop('required', true);
                row.find('.to-date-field').show().prop('required', true);
                row.find('.amount').prop('readonly', false);
                row.find('select.capacity-field').val('');
                if (!skipRecalc) {
                    calculateRowTotals(row);
                }
            } else {
                row.find('.amount').prop('readonly', false);
                row.find('select.capacity-field').val('');
                if (!skipRecalc) {
                    calculateRowTotals(row);
                }
            }
        }

        function calculateRowTotals(row) {
            var amount = parseFloat(row.find('.amount').val()) || 0;
            var quantity = parseFloat(row.find('.quantity').val()) || 0;
            var vatRate = 0.15;

            var subtotal = amount * quantity;
            var vatAmount = subtotal * vatRate;
            var total = subtotal + vatAmount;

            row.find('.vat').val(vatAmount.toFixed(2));
            row.find('.total').val(total.toFixed(2));
            row.find('.unit-price').val(subtotal.toFixed(2));
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

                <?php if (!empty($error)) { ?>
                    <div class="alert alert-danger">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?= $error; ?>
                    </div>
                <?php } ?>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('date', 'podate'); ?>
                            <?php
                            $invoice_date = !empty($memo_data->date ?? '')
                                ? $this->sma->hrsd($memo_data->date)
                                : $this->sma->hrsd(date('Y-m-d'));
                            echo form_input('date', $invoice_date, 'class="form-control input-tip" id="podate" required="required" autocomplete="off"');
                            ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('reference_no', 'poref'); ?>
                            <?php
                            $si_reference = $next_service_invoice_reference ?? '';
                            echo form_input('reference_no', $si_reference, 'class="form-control input-tip" id="poref" readonly="readonly" tabindex="-1"');
                            ?>
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
                            echo form_dropdown('customer', $sp, ($memo_data->customer_id ?? ''), 'id="customer_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('customer') . '" required="required" style="width:100%;" '); ?>
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
                                        <th class="text-center" style="width: 4%;">#</th>
                                        <th style="width: 10%;">Service Type</th>
                                        <th style="width: 9%;">From</th>
                                        <th style="width: 9%;">To</th>
                                        <th style="width: 11%;">Capacity</th>
                                        <th style="width: 9%;">Unit Price</th>
                                        <th style="width: 7%;">Quantity</th>
                                        <th style="width: 9%;">Amount</th>
                                        <th style="width: 9%;">VAT (15%)</th>
                                        <th style="width: 9%;">Total</th>
                                        <th class="text-center" style="width: 4%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="add_payment">
                                <?= !empty($memo_data) ? lang('Update Service Invoice') : lang('Add Service Invoice') ?>
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
