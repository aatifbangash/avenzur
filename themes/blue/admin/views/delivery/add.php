<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?=lang('add')?> Delivery</h2>
    </div>
    <div class="box-content">
        <?php echo admin_form_open('delivery/save', 'id="delivery-form"'); ?>

        <!-- Driver Selection Dropdown -->
        <div class="form-group">
            <label for="driver_id"><?=lang('select_driver')?> <span class="required">*</span></label>
            <select name="driver_id" id="driver_id" class="form-control select2" required style="width:100%">
                <option value="">-- <?=lang('select_driver')?> --</option>
                <?php if (!empty($drivers)): ?>
                    <?php foreach ($drivers as $driver): ?>
                        <option value="<?=$driver->id?>"
                                data-truck="<?=$driver->truck_id ? $driver->truck_id : 'N/A'?>"
                                data-license="<?=$driver->license_number ? $driver->license_number : 'N/A'?>">
                            <?=$driver->first_name?> <?=$driver->last_name?>
                            <?php if ($driver->truck_id): ?>
                                (Truck: <?=$driver->truck_id?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>No registered drivers available</option>
                <?php endif; ?>
            </select>
            <?php echo form_error('driver_id', '<span class="help-block text-danger">', '</span>'); ?>
            <small class="help-block">Only registered drivers from the system are available for selection.</small>
        </div>

        <!-- Driver Information Display (Read-only) -->
        <div class="row" id="driver-info-section" style="display: none;">
            <div class="col-md-6">
                <div class="alert alert-info">
                    <strong><i class="fa fa-truck"></i> Truck Number:</strong>
                    <span id="display-truck-number">-</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-info">
                    <strong><i class="fa fa-id-card"></i> License Number:</strong>
                    <span id="display-license-number">-</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="date_string"><?=lang('date')?></label>
                    <input type="text" name="date_string" id="date_string" class="form-control datepicker" value="<?=date('Y-m-d')?>" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="status"><?=lang('status')?> <span class="required">*</span></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="pending">Pending</option>
                        <option value="assigned">Assigned</option>
                        <option value="out_for_delivery">Out for Delivery</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Odometer Reading Section -->
        <div class="row" id="odometer-section" style="display: none;">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h4 class="panel-title"><i class="fa fa-tachometer"></i> Odometer Reading</h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="odometer">Current Odometer Reading <span class="required">*</span></label>
                                    <input type="number" name="odometer" id="odometer" class="form-control" placeholder="Enter odometer reading" min="0" />
                                    <small class="help-block">Enter the current odometer reading in kilometers</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-warning" id="last-odometer-display" style="display: none; margin-top: 25px;">
                                    <strong><i class="fa fa-info-circle"></i> Last Reading:</strong>
                                    <span id="last-odometer-value">-</span> km
                                    <br><small id="last-odometer-date"></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-success" id="distance-display" style="display: none; margin-top: 25px;">
                                    <strong><i class="fa fa-road"></i> Distance Traveled:</strong>
                                    <h3 style="margin: 5px 0;"><span id="distance-value">0</span> km</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label><?=lang('invoices')?> <span class="required">*</span></label>
            <div id="invoice-list">
                <?php if (!empty($invoices)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">
                            <thead>
                            <tr class="active">
                                <th style="width: 40px;">
                                    <input type="checkbox" id="select-all-invoices" />
                                </th>
                                <th><?=lang('reference_no')?></th>
                                <th><?=lang('customer')?></th>
                                <th><?=lang('date')?></th>
                                <th><?=lang('amount')?></th>
                                <th><?=lang('items')?></th>
                                <th style="min-width: 150px;">Current Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr <?php if(!empty($invoice->current_driver_name)): ?>class="warning"<?php endif; ?>>
                                    <td>
                                        <input type="checkbox"
                                               name="invoice_ids[]"
                                               value="<?=$invoice->id?>"
                                               class="invoice-checkbox"
                                                <?php if(!empty($invoice->current_driver_name)): ?>
                                                    disabled
                                                    title="Already assigned to <?=$invoice->current_driver_name?>"
                                                <?php endif; ?> />
                                    </td>
                                    <td><strong><?=$invoice->reference_no?></strong></td>
                                    <td><?=$invoice->customer_name?></td>
                                    <td><?=date('Y-m-d', strtotime($invoice->sale_date))?></td>
                                    <td><strong><?=$this->sma->formatMoney($invoice->total_amount)?></strong></td>
                                    <td>
                                        <span class="badge badge-primary"><?=$invoice->total_items?></span>
                                    </td>
                                    <td>
                                        <?php if(!empty($invoice->current_driver_name)): ?>
                                            <span class="label label-warning">
                                                    <i class="fa fa-truck"></i>
                                                    Assigned to: <?=$invoice->current_driver_name?>
                                                    <br>
                                                    <small>(<?=ucfirst(str_replace('_', ' ', $invoice->delivery_status))?>)</small>
                                                </span>
                                        <?php else: ?>
                                            <span class="label label-success">
                                                    <i class="fa fa-check-circle"></i> Available
                                                </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fa fa-info-circle"></i>
                        <strong>Note:</strong> Invoices with a yellow background are already assigned to another delivery and cannot be selected.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> <?=lang('no_records_found')?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fa fa-save"></i> <?=lang('save')?>
            </button>
            <a href="<?=admin_url('delivery')?>" class="btn btn-default btn-lg">
                <i class="fa fa-times"></i> <?=lang('cancel')?>
            </a>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        var lastOdometerReading = 0;

        // Initialize Select2 for driver dropdown
        $('#driver_id').select2({
            placeholder: "-- <?=lang('select_driver')?> --",
            allowClear: true,
            width: '100%'
        });

        // Show driver information when driver is selected
        $('#driver_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var truckNumber = selectedOption.data('truck');
            var licenseNumber = selectedOption.data('license');
            var driverId = $(this).val();

            if (driverId) {
                $('#display-truck-number').text(truckNumber);
                $('#display-license-number').text(licenseNumber);
                $('#driver-info-section').slideDown();
                $('#odometer-section').slideDown();

                // Fetch last odometer reading for this driver
                fetchLastOdometer(driverId);
            } else {
                $('#driver-info-section').slideUp();
                $('#odometer-section').slideUp();
                $('#last-odometer-display').hide();
                $('#distance-display').hide();
            }
        });

        // Fetch last odometer reading via AJAX
        function fetchLastOdometer(driverId) {
            $.ajax({
                url: '<?=admin_url('delivery/get_last_odometer')?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    driver_id: driverId,
                    '<?=$this->security->get_csrf_token_name()?>': '<?=$this->security->get_csrf_hash()?>'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        lastOdometerReading = parseInt(response.data.odometer);
                        $('#last-odometer-value').text(lastOdometerReading.toLocaleString());
                        $('#last-odometer-date').text('Date: ' + response.data.date);
                        $('#last-odometer-display').slideDown();
                    } else {
                        $('#last-odometer-display').hide();
                        lastOdometerReading = 0;
                    }
                },
                error: function() {
                    lastOdometerReading = 0;
                    $('#last-odometer-display').hide();
                }
            });
        }

        // Calculate distance when odometer value changes
        $('#odometer').on('input', function() {
            var currentReading = parseInt($(this).val()) || 0;

            if (currentReading > 0 && lastOdometerReading > 0) {
                var distance = currentReading - lastOdometerReading;

                if (distance < 0) {
                    $('#distance-display').removeClass('alert-success').addClass('alert-danger');
                    $('#distance-value').text('Invalid! Current reading is less than last reading');
                } else {
                    $('#distance-display').removeClass('alert-danger').addClass('alert-success');
                    $('#distance-value').text(distance.toLocaleString());
                }
                $('#distance-display').slideDown();
            } else if (currentReading > 0 && lastOdometerReading === 0) {
                $('#distance-display').removeClass('alert-danger').addClass('alert-info');
                $('#distance-value').text('First reading for this driver');
                $('#distance-display').slideDown();
            } else {
                $('#distance-display').hide();
            }
        });

        // Select all invoices functionality
        $('#select-all-invoices').on('change', function() {
            $('.invoice-checkbox:not(:disabled)').prop('checked', $(this).is(':checked'));
        });

        // Update select-all checkbox based on individual checkboxes
        $('.invoice-checkbox').on('change', function() {
            var totalCheckboxes = $('.invoice-checkbox:not(:disabled)').length;
            var checkedCheckboxes = $('.invoice-checkbox:checked').length;

            if (checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0) {
                $('#select-all-invoices').prop('checked', true);
            } else {
                $('#select-all-invoices').prop('checked', false);
            }
        });

        // Initialize datepicker
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });

        // Form validation before submit
        $('#delivery-form').on('submit', function(e) {
            var driverId = $('#driver_id').val();
            var selectedInvoices = $('.invoice-checkbox:checked').length;
            var currentOdometer = parseInt($('#odometer').val()) || 0;

            if (!driverId) {
                e.preventDefault();
                alert('Please select a driver before submitting.');
                $('#driver_id').focus();
                return false;
            }

            if (selectedInvoices === 0) {
                e.preventDefault();
                alert('Please select at least one invoice for delivery.');
                return false;
            }

            if (currentOdometer > 0 && lastOdometerReading > 0 && currentOdometer < lastOdometerReading) {
                e.preventDefault();
                alert('Current odometer reading cannot be less than the last reading (' + lastOdometerReading + ' km)');
                $('#odometer').focus();
                return false;
            }

            return true;
        });
    });
</script>

<style>
    /* Custom styling for better UX */
    .table-hover tbody tr:hover {
        background-color: #f5f5f5;
    }

    .table-bordered {
        border: 1px solid #ddd;
    }

    .invoice-checkbox[disabled] {
        cursor: not-allowed;
    }

    tr.warning {
        background-color: #fcf8e3 !important;
    }

    .label {
        display: inline-block;
        padding: 5px 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-primary {
        background-color: #337ab7;
        padding: 5px 10px;
        font-size: 13px;
    }

    .alert-info {
        border-left: 4px solid #5bc0de;
    }

    #driver-info-section .alert {
        margin-bottom: 0;
    }

    .select2-container .select2-selection--single {
        height: 40px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
    }
</style>