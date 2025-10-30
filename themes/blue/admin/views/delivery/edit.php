<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?=lang('edit')?> Delivery</h2>
    </div>
    <div class="box-content">
        <?php echo admin_form_open('delivery/update/' . $delivery->id, 'id="delivery-form"'); ?>
        
        <input type="hidden" name="id" value="<?=$delivery->id?>" />

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="driver_name"><?=lang('driver_name')?> <span class="required">*</span></label>
                    <input type="text" name="driver_name" id="driver_name" class="form-control" value="<?=$delivery->driver_name?>" required />
                    <?php echo form_error('driver_name', '<span class="help-block text-danger">', '</span>'); ?>
                </div>

                <div class="form-group">
                    <label for="truck_number"><?=lang('truck_number')?> <span class="required">*</span></label>
                    <input type="text" name="truck_number" id="truck_number" class="form-control" value="<?=$delivery->truck_number?>" required />
                    <?php echo form_error('truck_number', '<span class="help-block text-danger">', '</span>'); ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="date_string"><?=lang('date')?></label>
                    <input type="text" name="date_string" id="date_string" class="form-control datepicker" value="<?=!empty($delivery->date_string) ? date('Y-m-d', strtotime($delivery->date_string)) : ''?>" />
                </div>

                <div class="form-group">
                    <label><?=lang('status')?></label>
                    <p class="form-control-static"><span class="label label-<?=$delivery->status === 'completed' ? 'success' : ($delivery->status === 'out_for_delivery' ? 'warning' : 'info')?>">
                        <?=ucfirst(str_replace('_', ' ', $delivery->status))?>
                    </span></p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="odometer"><?=lang('odometer')?></label>
                    <input type="number" name="odometer" id="odometer" class="form-control" value="<?=$delivery->odometer?>" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="total_refrigerated_items"><?=lang('refrigerated_items')?></label>
                    <input type="number" name="total_refrigerated_items" id="total_refrigerated_items" class="form-control" value="<?=$delivery->total_refrigerated_items?>" />
                </div>
            </div>
        </div>

        <hr />
        <h5><?=lang('invoices')?> in this Delivery</h5>
        
        <?php if (!empty($items)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><?=lang('reference_no')?></th>
                            <th><?=lang('customer')?></th>
                            <th><?=lang('date')?></th>
                            <th><?=lang('items')?></th>
                            <th><?=lang('refrigerated_items')?></th>
                            <th><?=lang('action')?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?=$item->reference_no?></td>
                                <td><?=$item->customer_name?></td>
                                <td><?=date('Y-m-d', strtotime($item->sale_date))?></td>
                                <td><?=$item->quantity_items?></td>
                                <td><?=$item->refrigerated_items?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(<?=$delivery->id?>, <?=$item->invoice_id?>)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning"><?=lang('no_records_found')?></div>
        <?php endif; ?>

        <hr />
        <h5><?=lang('add_invoices')?></h5>

        <?php if (!empty($available_invoices)): ?>
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
                            <th><?=lang('refrigerated_items')?></th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($available_invoices as $invoice): ?>
                            <tr <?php if(!empty($invoice->assigned_delivery_id) && $invoice->current_delivery_id != $delivery->id): ?>class="warning"<?php endif; ?>>
                                <td><input type="checkbox" name="invoice_ids[]" value="<?=$invoice->id?>" class="invoice-checkbox" <?php if(!empty($invoice->assigned_delivery_id) && $invoice->current_delivery_id != $delivery->id): ?>title="Already assigned to another delivery"<?php endif; ?> /></td>
                                <td><?=$invoice->reference_no?></td>
                                <td><?=$invoice->customer_name?></td>
                                <td><?=date('Y-m-d', strtotime($invoice->sale_date))?></td>
                                <td><?=$this->sma->formatMoney($invoice->total_amount)?></td>
                                <td><?=$invoice->total_items?></td>
                                <td>
                                    <input type="number" name="refrigerated_items_<?=$invoice->id?>" class="form-control form-control-sm" value="0" min="0" max="<?=$invoice->total_items?>" />
                                </td>
                                <td>
                                    <?php if(!empty($invoice->assigned_delivery_id)): ?>
                                        <?php if($invoice->current_delivery_id == $delivery->id): ?>
                                            <span class="label label-success">
                                                <i class="fa fa-check"></i> In This Delivery
                                            </span>
                                        <?php else: ?>
                                            <span class="label label-warning">
                                                <i class="fa fa-truck"></i> 
                                                <?=$invoice->current_driver_name?> (<?=ucfirst(str_replace('_', ' ', $invoice->delivery_status))?>)
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="label label-default">Not Assigned</span>
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

        <div class="form-group" style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?=lang('save')?></button>
            <a href="<?=admin_url('delivery')?>" class="btn btn-default"><i class="fa fa-times"></i> <?=lang('cancel')?></a>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<script>
    function removeItem(deliveryId, invoiceId) {
        if (confirm('<?=lang('r_u_sure')?>')) {
            $.ajax({
                url: '<?=admin_url('delivery/remove_item')?>',
                type: 'POST',
                data: {
                    delivery_id: deliveryId,
                    invoice_id: invoiceId,
                    '<?=$this->security->get_csrf_token_name()?>': '<?=$this->security->get_csrf_hash()?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.error === 0) {
                        location.reload();
                    } else {
                        alert(response.msg);
                    }
                }
            });
        }
    }

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
