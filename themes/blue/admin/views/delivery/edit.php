<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-edit"></i><?=lang('edit')?> Delivery</h2>
    </div>
    <div class="box-content">
        <?php echo admin_form_open('delivery/update/' . $delivery->id, 'id="delivery-form"'); ?>
        
        <input type="hidden" name="id" value="<?=$delivery->id?>" />
        <?php //echo '<pre>';print_r($delivery);exit; ?>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="driver_id"><?=lang('select_driver')?> <span class="required">*</span></label>
                    <select name="driver_id" id="driver_id" class="form-control select2" required style="width:100%">
                        <option value="">-- <?=lang('select_driver')?> --</option>

                        <?php if (!empty($drivers)): ?>
                            <?php foreach ($drivers as $driver): ?>
                                <option value="<?=$driver->id?>"
                                        data-truck="<?=$driver->truck_id ?? 'N/A'?>"
                                        data-license="<?=$driver->license_number ?? 'N/A'?>"
                                        <?= (isset($delivery->driver_id) && $delivery->driver_id == $driver->id) ? 'selected' : '' ?>>
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
                </div>

                <?php
                    if($delivery->status == 'out_for_delivery'){
                        ?>
                        <div class="form-group<div class="form-group">
                            <label for="odometer"><?=lang('Odometer Start')?></label>
                            <input type="number" name="odometer_mileage" id="odometer" class="form-control" value="<?=$delivery->odometer?>" readonly />
                        </div>

                        <div class="form-group<div class="form-group">
                            <label for="odometer"><?=lang('Odometer Mileage')?></label>
                            <input type="number" name="odometer_mileage" id="odometer" class="form-control" value="0" />
                        </div>
                        <?php
                    }else{
                        ?>
                        <div class="form-group<div class="form-group">
                            <label for="odometer"><?=lang('Odometer')?></label>
                            <input type="number" name="odometer" id="odometer" class="form-control" value="<?=$delivery->odometer?>" />
                        </div>
                        <?php
                    }
                ?>
                
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="date_string"><?=lang('date')?></label>
                    <input type="text" name="date_string" id="date_string" class="form-control datepicker" value="<?=!empty($delivery->date_string) ? date('Y-m-d', strtotime($delivery->date_string)) : ''?>" />
                </div>

                <?php
                    if($delivery->status == 'out_for_delivery'){
                        ?>
                <div class="form-group">
                    <?= lang('Current Status', 'status'); ?>
                    <p class="form-control-static"><span class="label label-<?=$delivery->status === 'completed' ? 'success' : ($delivery->status === 'out_for_delivery' ? 'warning' : 'info')?>">
                        <?=ucfirst(str_replace('_', ' ', $delivery->status))?>
                    </span></p>
                </div>

                <?php } ?>

                <div class="form-group">
                    <?= lang('Mark Status', 'status'); ?>
                    <?php
                    if($delivery->status == 'out_for_delivery'){
                        $post = ['delivered' => lang('Delivered')];
                    }else{
                        $post = ['driver_assigned' => lang('Assigned'), 'out_for_delivery' => lang('Out for Delivery')];
                    }

                    echo form_dropdown('status', $post, ($_POST['status'] ?? $delivery->status), ' class="form-control input-tip select" data-placeholder="' . $this->lang->line('select') . ' ' . $this->lang->line('status') . '" id="status"  style="width:100%;" ');
                    ?>
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
                            <?php if($delivery->status != 'out_for_delivery'){ ?>
                            <th><?=lang('action')?></th>
                            <?php } ?>
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
                                <?php if($delivery->status != 'out_for_delivery'){ ?>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(<?=$delivery->id?>, <?=$item->invoice_id?>)">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                                <?php } ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning"><?=lang('no_records_found')?></div>
        <?php endif; ?>

        <?php
        if($delivery->status != 'out_for_delivery'){
        ?>

        <hr />
        <h5><?=lang('add_invoices')?></h5>

        <?php if (!empty($available_invoices)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            
                            <th><?=lang('reference_no')?></th>
                            <th><?=lang('customer')?></th>
                            <th><?=lang('date')?></th>
                            <th><?=lang('amount')?></th>
                            <th><?=lang('items')?></th>
                            <th><?=lang('refrigerated_items')?></th>
                            <?php if($delivery->status != 'out_for_delivery'){ ?>
                            <th>Action</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($available_invoices as $invoice): ?>
                            <tr <?php if(!empty($invoice->assigned_delivery_id) && $invoice->current_delivery_id != $delivery->id): ?>class="warning"<?php endif; ?>>
                                
                                <td><?=$invoice->reference_no?></td>
                                <td><?=$invoice->customer_name?></td>
                                <td><?=date('Y-m-d', strtotime($invoice->sale_date))?></td>
                                <td><?=$this->sma->formatMoney($invoice->total_amount)?></td>
                                <td><?=$invoice->total_items?></td>
                                <td>
                                    <input type="number" name="refrigerated_items_<?=$invoice->id?>" class="form-control form-control-sm" value="<?= $invoice->assigned_refrigerated_items; ?>" min="0" max="<?=$invoice->total_items?>" readonly />
                                </td>
                                <?php if($delivery->status != 'out_for_delivery'){ ?>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" onclick="addItem(<?=$delivery->id?>, <?=$invoice->id?>, <?=$invoice->total_items?>, <?=$invoice->assigned_refrigerated_items?>)">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </td>
                                <?php } ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info"><?=lang('no_records_found')?></div>
        <?php endif; ?>

        <?php } ?>

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

    function addItem(deliveryId, invoiceId, total_items, max_refrigerated_items) {
        if (confirm('<?=lang('r_u_sure')?>')) {
            $.ajax({
                url: '<?=admin_url('delivery/add_item')?>',
                type: 'POST',
                data: {
                    delivery_id: deliveryId,
                    invoice_id: invoiceId,
                    total_items: total_items,
                    max_refrigerated_items: max_refrigerated_items,
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

        //$('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
    });

    $('#driver_id').trigger('change');
</script>
