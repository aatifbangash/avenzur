<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="row">
    <div class="col-md-8">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i> <?=lang('delivery')?> Details</h2>
            </div>
            <div class="box-content">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><?=lang('delivery')?> ID:</strong> <?=$delivery->id?></p>
                        <p><strong><?=lang('driver_name')?>:</strong> <?=$delivery->driver_name?></p>
                        <p><strong><?=lang('truck_number')?>:</strong> <?=$delivery->truck_number?></p>
                        <p><strong><?=lang('date')?>:</strong> <?=!empty($delivery->date_string) ? date('Y-m-d H:i', strtotime($delivery->date_string)) : 'N/A'?></p>
                    </div>
                    <div class="col-md-6">
                        <p>
                            <strong><?=lang('status')?>:</strong> 
                            <span class="label label-<?=$delivery->status === 'completed' ? 'success' : ($delivery->status === 'out_for_delivery' ? 'warning' : 'info')?>">
                                <?=ucfirst(str_replace('_', ' ', $delivery->status))?>
                            </span>
                        </p>
                        <p><strong><?=lang('items')?>:</strong> <?=$delivery->total_items_in_delivery_package?></p>
                        <p><strong><?=lang('refrigerated_items')?>:</strong> <?=$delivery->total_refrigerated_items?></p>
                        <p><strong><?=lang('odometer')?>:</strong> <?=$delivery->odometer ? $delivery->odometer . ' km' : 'N/A'?></p>
                    </div>
                </div>

                <hr />

                <div class="row">
                    <div class="col-md-6">
                        <p><strong><?=lang('assigned_by')?>:</strong> <?=$delivery->assigned_by_name?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><?=lang('out_time')?>:</strong> <?=!empty($delivery->out_time) ? date('Y-m-d H:i', strtotime($delivery->out_time)) : 'N/A'?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-list"></i> <?=lang('invoices')?></h2>
            </div>
            <div class="box-content">
                <?php if (!empty($items)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?=lang('reference_no')?></th>
                                    <th><?=lang('customer')?></th>
                                    <th><?=lang('date')?></th>
                                    <th><?=lang('amount')?></th>
                                    <th><?=lang('items')?></th>
                                    <th><?=lang('refrigerated')?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?=$item->reference_no?></td>
                                        <td><?=$item->customer_name?></td>
                                        <td><?=date('Y-m-d', strtotime($item->sale_date))?></td>
                                        <td><?=currency($item->total_amount)?></td>
                                        <td><?=$item->quantity_items?></td>
                                        <td><?=$item->refrigerated_items?></td>
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
    </div>

    <div class="col-md-4">
        <div class="box">
            <div class="box-header">
                <h2 class="blue"><i class="fa-fw fa fa-cog"></i> <?=lang('actions')?></h2>
            </div>
            <div class="box-content">
                <a href="<?=admin_url('delivery/edit/' . $delivery->id)?>" class="btn btn-primary btn-block"><i class="fa fa-edit"></i> <?=lang('edit')?></a>
                <a href="<?=admin_url('delivery/print/' . $delivery->id)?>" class="btn btn-info btn-block" target="_blank"><i class="fa fa-print"></i> <?=lang('print')?></a>
                <!--<button onclick="updateStatus(<?=$delivery->id?>, 'out_for_delivery')" class="btn btn-warning btn-block"><i class="fa fa-truck"></i> <?=lang('mark_out_for_delivery')?></button>-->
                <!--<button onclick="updateStatus(<?=$delivery->id?>, 'completed')" class="btn btn-success btn-block"><i class="fa fa-check"></i> <?=lang('mark_completed')?></button>-->
                <a href="<?=admin_url('delivery')?>" class="btn btn-default btn-block"><i class="fa fa-list"></i> <?=lang('back')?></a>
            </div>
        </div>

        <?php if (!empty($print_history)): ?>
            <div class="box">
                <div class="box-header">
                    <h2 class="blue"><i class="fa-fw fa fa-history"></i> <?=lang('print_history')?></h2>
                </div>
                <div class="box-content">
                    <div class="list-group">
                        <?php foreach ($print_history as $print): ?>
                            <div class="list-group-item">
                                <p class="list-group-item-text">
                                    <small><?=$print->printed_by_name?> - <?=date('Y-m-d H:i', strtotime($print->printed_at))?></small>
                                </p>
                                <p class="list-group-item-text">
                                    <small>Copies: <?=$print->print_count?></small>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($audit_logs)): ?>
    <div class="box" style="margin-top: 20px;">
        <div class="box-header">
            <h2 class="blue"><i class="fa-fw fa fa-history"></i> <?=lang('audit_log')?></h2>
        </div>
        <div class="box-content">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th><?=lang('action')?></th>
                            <th><?=lang('done_by')?></th>
                            <th><?=lang('date')?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audit_logs as $log): ?>
                            <tr>
                                <td><?=ucfirst(str_replace('_', ' ', $log->action))?></td>
                                <td><?=$log->done_by_name?></td>
                                <td><?=date('Y-m-d H:i', strtotime($log->created_at))?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    /*function updateStatus(deliveryId, status) {
        $.ajax({
            url: '<?=admin_url('delivery/update_status')?>',
            type: 'POST',
            data: {
                delivery_id: deliveryId,
                status: status,
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
    }*/
</script>
