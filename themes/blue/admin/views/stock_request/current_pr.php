<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript"></script>
<?php if ($Owner || ($GP && $GP['stock_request_view'])) {
    echo admin_form_open('stock_request/current_pr', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('Opened Purchase Request'); ?>
    </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip"  data-placement="left" title="<?= lang('actions') ?>"></i>
                    </a>
             </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext">
                    <!--<div class="col-md-4">
                        <div class="form-group">
                            <?php //echo lang('Safety Stock', 'safety_stock'); ?>
                            <?php //echo form_input('safety_stock', ($_POST['safety_stock'] ?? '1'), 'class="form-control input-tip" onchange="safety_stock_changed();" id="slref"'); ?>
                        </div>
                    </div>-->

                    <?php if (!empty($warehouses)) {
                        ?>
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?=lang('warehouses')?>"></i></a>
                            <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                                <li><a href="<?=admin_url('purchases')?>"><i class="fa fa-building-o"></i> <?=lang('all_warehouses')?></a></li>
                                <li class="divider"></li>
                                <?php
                                foreach ($warehouses as $warehouse) {
                                    echo '<li ' . ($warehouse_id && $warehouse_id == $warehouse->id ? 'class="active"' : '') . '><a href="' . admin_url('purchases/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                } ?>
                            </ul>
                        </li>
                        <?php
                    }
                    ?>

                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Status', 'Status'); ?>
                            <?php
                            //$statuses = array('completed' => 'completed', 'rejected' => 'rejected');
                            //echo form_dropdown('status', $statuses, '', 'id="powarehouse" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('status') . '" required="required" style="width:100%;" '); 
                            $statuses = array('completed', 'rejected');
                           ?>
                            <select class="form-control" id="status" name="status" >
                                    <?php
                                        foreach($statuses as $status)
                                        {
                                            echo '<option value="'.$status.'">'.$status.'</option>';
                                        }
                                    ?>                  
                            </select>
                            <br /><br />
                            <button type="submit" class="btn btn-primary" id="add_request">
                            <?php 
                                echo lang('Submit');
                            ?>
                            </button>
                        </div>
                    </div>
                </p>
                <div class="table-responsive">
                    <table id="TOData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                #
                            </th>
                            <th><?= lang('code'); ?></th>
                            <th colspan="2"><?= lang('name'); ?></th>
                            <th><?= lang('Available Quantity'); ?></th>
                            <th><?= lang('Q ord'); ?></th>
                            <th><?= lang('Avg Consumption'); ?></th>
                            <th colspan="2"><?= lang('Q req'); ?></th>
                            <th><?= lang('Safety Stock'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php 
                                if($current_pr){
                                    $count = 0;
                                    foreach($current_pr as $pr){
                                        $count++;
                                        $months = isset($pr->months) ? $pr->months : 1;
                                        ?>
                                            <tr>
                                                <td class="dataTables_empty"><?= $count; ?></td>
                                                <td class="dataTables_empty"><?= $pr->code; ?></td>
                                                <td colspan="2" class="dataTables_empty"><?= $pr->name; ?></td>
                                                <td class="dataTables_empty"><?= number_format((float) $pr->total_warehouses_quantity, 2, '.', ''); ?></td>
                                                <td class="dataTables_empty"><?= $pr->total_req_stock; ?></td>
                                                <td class="dataTables_empty"><?= isset($pr->total_avg_stock) ? number_format((float) ($pr->total_avg_stock), 2, '.', '') : '0.00'; ?></td>
                                                <td colspan="2" class="dataTables_empty">
                                                    <input name="required_stock[]" id="required_stock_<?= $count; ?>" type="text" value="<?= $pr->qreq; ?>" class="rid" />
                                                    <input type="hidden" name="product_id[]" value="<?= $pr->id; ?>" />
                                                    <input type="hidden" name="available_stock[]" id="available_stock_<?= $count; ?>" value="<?= $pr->total_warehouses_quantity; ?>" />
                                                    <?php 
                                                        if(isset($request_id)){
                                                            ?>
                                                                <input type="hidden" id="avg_stock_<?= $count; ?>" name="avg_stock[]" value="<?= ($pr->total_avg_stock); ?>" />
                                                            <?php
                                                        }else{
                                                            ?>
                                                                <input type="hidden" id="avg_stock_<?= $count; ?>" name="avg_stock[]" value="<?= ($pr->total_avg_stock); ?>" />
                                                            <?php
                                                        }
                                                    ?>
                                                    
                                                </td>
                                                <td class="dataTables_empty"><input style="width:40%;" type="text" name="safety_stock[]" value="<?= $months; ?>" onchange="changeSafetyStock(this, '<?= $count; ?>');" /> months</td>
                                            </tr>
                                        <?php
                                    }

                                    if(isset($request_id)){
                                        ?>
                                            <input type="hidden" name="request_id" value="<?= $request_id; ?>" />
                                        <?php
                                    }
                                                    
                                }else{
                            ?>
                                <tr><td colspan="11" class="dataTables_empty"><?= lang('No rows found.'); ?></td></tr>
                            <?php
                                }
                            ?>
                            
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function changeSafetyStock(obj, count){
        var available_stock = document.getElementById('available_stock_'+count).value;
        var average_stock = document.getElementById('avg_stock_'+count).value;
        var qreq = (average_stock*obj.value) - available_stock;
        document.getElementById('required_stock_'+count).value = qreq;
    }
</script>
<?php echo form_close(); ?>

