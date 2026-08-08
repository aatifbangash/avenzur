<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-excel-o"></i><?= lang('CSV Inventory Adjustment'); ?></h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext">Upload CSV file to adjust inventory based on physical count data.</p>

                <div class="alert alert-info">
                    <h4><i class="fa fa-info-circle"></i> CSV Format Requirements:</h4>
                    <ul>
                        <li><strong>Required columns:</strong> product_id, actual_batch, actual_expiry, quantity, system_quantity, cost, price</li>
                        <li><strong>Optional columns:</strong> product_code, product_name, actual_shelf, item_code, shelf, inventory_group</li>
                        <li>If cost or price is empty, it will be treated as 0</li>
                        <li>Only rows with variance (actual_quantity ≠ system_quantity) will be processed</li>
                        <li>Date format for expiry: <strong>YYYY-MM-DD</strong></li>
                        <li>Maximum file size: <strong>5MB</strong></li>
                    </ul>
                </div>

                <?php if ($error) { ?>
                    <div class="alert alert-danger">
                        <button data-dismiss="alert" class="close" type="button">×</button>
                        <?= $error; ?>
                    </div>
                <?php } ?>

                <div class="well well-sm">
                    <?php echo admin_form_open_multipart('stock_request/hills_adjust_inventory_csv', 'id="csv_adjustment_form"'); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("Inventory Check Request", "inventory_check_request_id"); ?> *
                                <?php
                                $inv_check_options = array('' => lang("select") . " " . lang("Inventory Check Request"));
                                if (!empty($inventory_checks)) {
                                    foreach ($inventory_checks as $check) {
                                        $inv_check_options[$check->id] = $check->code . ' - ' . $check->warehouse_name;
                                    }
                                }
                                echo form_dropdown('inventory_check_request_id', $inv_check_options, set_value('inventory_check_request_id'), 
                                    'id="inventory_check_request_id" class="form-control select2" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang("warehouse", "location_id"); ?> *
                                <?php
                                $wh_options = array('' => lang("select") . " " . lang("warehouse"));
                                if (!empty($warehouses)) {
                                    foreach ($warehouses as $warehouse) {
                                        $wh_options[$warehouse->id] = $warehouse->name;
                                    }
                                }
                                echo form_dropdown('location_id', $wh_options, set_value('location_id'), 
                                    'id="location_id" class="form-control select2" required="required" style="width:100%;"');
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <?= lang("CSV File", "csvfile"); ?> * <small class="text-muted">(Max: 5MB, .csv only)</small>
                                <input id="csvfile" type="file" name="csvfile" class="form-control" 
                                       accept=".csv" required="required" />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="controls">
                            <?php echo form_submit('submit_csv_adjustment', lang('Process CSV Adjustment'), 
                                'class="btn btn-primary"'); ?>
                            <a href="<?= admin_url('stock_request/inventory_check'); ?>" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> <?= lang('Back'); ?>
                            </a>
                        </div>
                    </div>

                    <?php echo form_close(); ?>
                </div>

                <div class="alert alert-warning">
                    <h4><i class="fa fa-exclamation-triangle"></i> Important Notes:</h4>
                    <ul>
                        <li>This process will create inventory transfers based on CSV data</li>
                        <li>Cost and price values from CSV will be used directly (not from database)</li>
                        <li>Shortage items will be distributed across AVZ codes proportionally</li>
                        <li>Make sure to backup your data before processing large adjustments</li>
                        <li>Keep the CSV file for your audit trail</li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function () {
    // Initialize Select2
    $('.select2').select2({
        minimumResultsForSearch: 7
    });

    // Form submission confirmation
    $('#csv_adjustment_form').submit(function(e) {
        var fileName = $('#csvfile').val();
        var invCheck = $('#inventory_check_request_id option:selected').text();
        var warehouse = $('#location_id option:selected').text();
        
        if (!fileName) {
            bootbox.alert('<?= lang("Please select a CSV file"); ?>');
            e.preventDefault();
            return false;
        }
        
        var confirmMsg = '<?= lang("Are you sure you want to process this CSV file?"); ?>\n\n' +
                        'Inventory Check: ' + invCheck + '\n' +
                        'Warehouse: ' + warehouse + '\n' +
                        'File: ' + fileName.split('\\').pop();
        
        if (!confirm(confirmMsg)) {
            e.preventDefault();
            return false;
        }
    });

    // File input validation
    $('#csvfile').change(function() {
        var file = this.files[0];
        if (file) {
            // Check file size (5MB = 5242880 bytes)
            if (file.size > 5242880) {
                bootbox.alert('<?= lang("File size exceeds 5MB limit"); ?>');
                $(this).val('');
                return false;
            }
            
            // Check file extension
            var fileName = file.name;
            var ext = fileName.split('.').pop().toLowerCase();
            if (ext !== 'csv') {
                bootbox.alert('<?= lang("Only CSV files are allowed"); ?>');
                $(this).val('');
                return false;
            }
        }
    });
});
</script>
