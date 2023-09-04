<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript"></script>
<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Safety Stock', 'safety_stock'); ?>
                            <?php echo form_input('safety_stock', ($_POST['safety_stock'] ?? '1'), 'class="form-control input-tip" id="slref"'); ?>
                        </div>
                    </div>
                    <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="add_request">
                        <?php 
                                echo lang('Approve Purchase Request');
                        ?>
                    </button>
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
                            <th><?= lang('cost'); ?></th>
                            <th><?= lang('Available Quantity'); ?></th>
                            <th><?= lang('Avg Consumption'); ?></th>
                            <th colspan="2"><?= lang('Q req'); ?></th>
                            <!--<th><?php //echo lang('Safety Stock'); ?></th>-->
                        </tr>
                        </thead>
                        <tbody>
                            <?php 
                                if($current_pr){
                                    $count = 0;
                                    foreach($current_pr as $pr){
                                        $count++;
                                        ?>
                                            <tr>
                                                <td class="dataTables_empty"><?= $count; ?></td>
                                                <td class="dataTables_empty"><?= $pr->code; ?></td>
                                                <td colspan="2" class="dataTables_empty"><?= $pr->name; ?></td>
                                                <td class="dataTables_empty"><?= number_format((float) $pr->cost, 2, '.', ''); ?></td>
                                                <td class="dataTables_empty"><?= number_format((float) $pr->total_warehouses_quantity, 2, '.', ''); ?></td>
                                                <td class="dataTables_empty"><?= isset($pr->total_avg_stock) ? number_format((float) ($pr->total_avg_stock), 2, '.', '') : '0.00'; ?></td>
                                                <td colspan="2" class="dataTables_empty">
                                                    <input name="required_stock[]" type="text" value="<?= $pr->qreq; ?>" class="rid" />
                                                    <input type="hidden" name="product_id[]" value="<?= $stock->id; ?>" />
                                                    <input type="hidden" name="available_stock[]" value="<?= $stock->available_stock; ?>" />
                                                    <?php 
                                                        if(isset($request_id)){
                                                            ?>
                                                                <input type="hidden" name="avg_stock[]" value="<?= ($stock->avg_stock); ?>" />
                                                            <?php
                                                        }else{
                                                            ?>
                                                                <input type="hidden" name="avg_stock[]" value="<?= ($stock->avg_last_3_months_sales / 3); ?>" />
                                                            <?php
                                                        }
                                                    ?>
                                                    
                                                </td>
                                                <!--<td class="dataTables_empty"><input style="width:40%;" type="text" name="safety_stock" value="1" /> months</td>-->
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
                                <tr><td colspan="11" class="dataTables_empty"><?= lang('Could not load data'); ?></td></tr>
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
<?php echo form_close(); ?>

