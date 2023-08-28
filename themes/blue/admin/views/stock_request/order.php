<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript"></script>
<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
    echo admin_form_open('transfers/transfer_actions', 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('Stock Order Request'); ?></h2>

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

                <p class="introtext"><?= lang('list_results'); ?></p>

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
                                                <td class="dataTables_empty"><?= $stock->cost; ?></td>
                                                <td class="dataTables_empty"><?= $stock->available_stock; ?></td>
                                                <td class="dataTables_empty"><?= ($stock->avg_last_3_months_sales) / 3; ?></td>
                                                <td colspan="2" class="dataTables_empty"><?= ($stock->avg_last_3_months_sales / 3) - $stock->available_stock; ?></td>
                                                <td class="dataTables_empty">1 month</td>
                                            </tr>
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
<?php if ($Owner || ($GP && $GP['bulk_actions'])) {
    ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
    <?php
} ?>
