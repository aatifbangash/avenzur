<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script type="text/javascript"></script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('Inventory Check'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <h3><b style="background: lightgrey;color: black;padding:10px;border: 1px solid grey;"><?= $warehouse_detail->name; ?> - ( <?= date('Y-m-d', strtotime($inventory_check_request_details->date)); ?> )</b></h3>
                    <?php $attrib = ['role' => 'form'];
                        echo admin_form_open_multipart('stock_request/adjust_inventory', $attrib);  
                    ?>
                    <input type="hidden" name="inventory_check_request_id" value="<?= $inventory_check_request_details->id; ?>" />
                    <input type="hidden" name="location_id" value="<?= $inventory_check_request_details->location_id; ?>" />
                    <?php 
                    if($inventory_check_request_details->status == 'pending'){
                        $data = array(
                            'name' => 'adjust_inv',
                            'onclick'=>"return confirm('Are you sure to proceed?')"
                        );
                        echo form_submit($data, $this->lang->line('Adjust Inventory'), 'id="adjust_inv" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); 
                        
                    }
                    
                    ?>
                    <?php echo form_close(); ?>

                    <table id="TOData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="width:2%;">#</th>
                            <th><?= lang('Product Name'); ?></th>
                            <th><?= lang('Avz Code'); ?></th>
                            <th><?= lang('Actual Quantity'); ?></th>
                            <th><?= lang('System Quantity'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php 
                                if($inventory_check_array){
                                    $count = 0;
                                    foreach($inventory_check_array as $inventory_check){
                                        $count++;
                                        ?>
                                            <tr>
                                                <td class="dataTables_empty"><?= $count; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->product_name ? $inventory_check->product_name : '-'; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->avz_code; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"> <?= $inventory_check->quantity; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->system_quantity ? $inventory_check->system_quantity : '0.00'; ?></td>
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
