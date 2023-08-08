<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('inventory_movement_report'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
        <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('reports/inventory_movement', $attrib)
        ?>
        <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">
                       
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('From Date', 'podate'); ?>
                                <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('To Date', 'podate'); ?>
                                <?php echo form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>
                            
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>SN</th>
                                <th><?= lang('Item No'); ?></th>
                                <th><?= lang('Item Desc'); ?></th>
                                <th><?= lang('OB Quantity'); ?></th>
                                <th><?= lang('OB Cost'); ?></th>
                                <th><?= lang('OB Value'); ?></th>
                                <th><?= lang('Movement In Qty'); ?></th>
                                <th><?= lang('Movement In Cost'); ?></th>
                                <th><?= lang('Movement In Manufacturing'); ?></th>
                                <th><?= lang('Movement Out Qty'); ?></th>
                                <th><?= lang('Movement Out Cost'); ?></th>
                                <th><?= lang('Movement Out Manufacturing'); ?></th>
                                <th><?= lang('CB Quantity'); ?></th>
                                <th><?= lang('CB Cost'); ?></th>
                                <th><?= lang('CB Value'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 1;
                                    foreach ($vat_purchase as $data){
                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><?= $data->date; ?></td>
                                                <td><?= $data->invoice_number; ?></td>
                                                <td><?= $data->transaction_id; ?></td>
                                                <td><?= $data->supplier_code; ?></td>
                                                <td><?= $data->supplier; ?></td>
                                                <td><?= $data->vat_no; ?></td>
                                                <td>Purchase</td>
                                                <td><?= $data->total_quantity; ?></td>
                                                <td><?=  $data->total_with_vat - $data->total_tax; ?></td>
                                                <td><?= $data->total_tax; ?></td>
                                                <td><?= $data->total_with_vat; ?></td>
                                            </tr>
                                        <?php
                                        $count++;
                                    }
                                ?>
                                
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>
                
            </div>

        </div>
    </div>
    <?php echo form_close(); ?>
</div>
