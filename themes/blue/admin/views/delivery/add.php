<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-plus"></i><?=lang('add')?> Delivery</h2>
    </div>
    <div class="box-content">
        <?php echo admin_form_open('delivery/save', 'id="delivery-form"'); ?>
        <div class="form-group">
            <label for="driver_name"><?=lang('driver_name')?> <span class="required">*</span></label>
            <input type="text" name="driver_name" id="driver_name" class="form-control" required />
            <?php echo form_error('driver_name', '<span class="help-block text-danger">', '</span>'); ?>
        </div>

        <div class="form-group">
            <label for="truck_number"><?=lang('truck_number')?> <span class="required">*</span></label>
            <input type="text" name="truck_number" id="truck_number" class="form-control" required />
            <?php echo form_error('truck_number', '<span class="help-block text-danger">', '</span>'); ?>
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

        <div class="form-group">
            <label><?=lang('invoices')?> <span class="required">*</span></label>
            <div id="invoice-list">
                <?php if (!empty($invoices)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all-invoices" /></th>
                                    <th><?=lang('reference_no')?></th>
                                    <th><?=lang('customer')?></th>
                                    <th><?=lang('date')?></th>
                                    <th><?=lang('amount')?></th>
                                    <th><?=lang('items')?></th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr <?php if(!empty($invoice->current_driver_name)): ?>class="warning"<?php endif; ?>>
                                        <td><input type="checkbox" name="invoice_ids[]" value="<?=$invoice->id?>" class="invoice-checkbox" <?php if(!empty($invoice->current_driver_name)): ?>disabled title="Already assigned to a delivery"<?php endif; ?> /></td>
                                        <td><?=$invoice->reference_no?></td>
                                        <td><?=$invoice->customer_name?></td>
                                        <td><?=date('Y-m-d', strtotime($invoice->sale_date))?></td>
                                        <td><?=$this->sma->formatMoney($invoice->total_amount)?></td>
                                        <td><?=$invoice->total_items?></td>
                                        <td>
                                            <?php if(!empty($invoice->current_driver_name)): ?>
                                                <span class="label label-warning">
                                                    <i class="fa fa-truck"></i> 
                                                    <?=$invoice->current_driver_name?> (<?=ucfirst(str_replace('_', ' ', $invoice->delivery_status))?>)
                                                </span>
                                            <?php else: ?>
                                                <span class="label label-success"><i class="fa fa-check"></i> Available</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info"><?=lang('no_records_found')?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?=lang('save')?></button>
            <a href="<?=admin_url('delivery')?>" class="btn btn-default"><i class="fa fa-times"></i> <?=lang('cancel')?></a>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#select-all-invoices').on('change', function() {
            $('.invoice-checkbox').prop('checked', $(this).is(':checked'));
        });

        $('.invoice-checkbox').on('change', function() {
            if ($('.invoice-checkbox:checked').length === $('.invoice-checkbox').length) {
                $('#select-all-invoices').prop('checked', true);
            } else {
                $('#select-all-invoices').prop('checked', false);
            }
        });

        $('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
    });
</script>
