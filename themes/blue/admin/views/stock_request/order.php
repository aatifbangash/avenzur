<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript"></script>
<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
    echo admin_form_open('stock_request/stock_order', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('Stock Order Request'); ?>
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
                    <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="add_request">
                        <?php 
                            if(isset($request_id)){
                                echo lang('Edit Request');
                            }else{
                                echo lang('Add Request');
                            } 
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
                            <!--<th><?php echo lang('cost'); ?></th>-->
                            <th><?= lang('Available Quantity'); ?></th>
                            <th><?= lang('Avg Sale'); ?></th>
                            <th colspan="2"><?= lang('Required Stock'); ?></th>
                            <th><?= lang('Months'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php 
                                if($stock_array){
                                    $count = 0;
                                    foreach($stock_array as $stock){
                                        $count++;
                                        ?>
                                            <tr>
                                                <td class="dataTables_empty"><?= $count; ?></td>
                                                <td class="dataTables_empty"><?= $stock->code; ?></td>
                                                <td colspan="2" class="dataTables_empty"><?= $stock->name; ?></td>
                                                <!--<td class="dataTables_empty"><?php //echo number_format((float) $stock->cost, 2, '.', ''); ?></td>
                                                <td class="dataTables_empty"><?= number_format((float) $stock->available_stock, 2, '.', ''); ?></td>
                                                <td class="dataTables_empty"><?= isset($stock->avg_stock) ? number_format((float) ($stock->avg_stock), 2, '.', '') : number_format((float) ($stock->avg_last_3_months_sales) / 3, 2, '.', ''); ?></td>
                                                <td colspan="2" class="dataTables_empty">
                                                    <?php 
                                                        if(isset($stock->required_stock)){
                                                            $required_stock = $stock->required_stock;
                                                        }else{
                                                            $required_stock = ($stock->avg_last_3_months_sales / 3) - $stock->available_stock > 0 ? number_format((float) ($stock->avg_last_3_months_sales / 3) - $stock->available_stock, 2, '.', '') : '0.00';
                                                        } 
                                                    ?>
                                                    <input name="required_stock[]" type="text" value="<?= $required_stock; ?>" class="rid" />
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
                                                <td class="dataTables_empty">1 month</td>
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

