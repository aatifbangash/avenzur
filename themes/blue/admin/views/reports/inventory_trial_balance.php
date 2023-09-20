<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {

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
            echo admin_form_open_multipart('reports/inventory_trial_balance', $attrib)
            ?>
            <div class="col-lg-12">
                <div class="row">

                    <div class="col-lg-12">
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('From Warehouse', 'from_warehouse_id'); ?>
                                <?php echo form_dropdown('from_warehouse_id', $warehouses, set_value('from_warehouse_id', $_POST['from_warehouse_id']), array('class' => 'form-control', 'id' => 'from_warehouse_id'), array('none')); ?>

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('To Warehouse', 'to_warehouse_id'); ?>
                                <?php echo form_dropdown('to_warehouse_id', $warehouses, set_value('to_warehouse_id', $_POST['to_warehouse_id']), array('class' => 'form-control', 'id' => 'to_warehouse_id'), array('none')); ?>

                            </div>
                        </div>
                    </div>

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
                        <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover">
                            <thead>

                                <tr>

                                    <th colspan=4>ITEM</th>
                                    <th colspan=3>Opening Balance</th>
                                    <th colspan=3>Movement In</th>
                                    <th colspan=3>Movement Out</th>
                                    <th colspan=3>Closing Balance</th>
                                </tr>
                                <tr>
                                    <th>SN</th>
                                    <th><?= lang('Item No'); ?></th>
                                    <th><?= lang('Item Code'); ?></th>
                                    <th><?= lang('Item Desc'); ?></th>

                                    <th><?= lang('On-hand Qty'); ?></th>
                                    <th><?= lang('On-hand U Cost'); ?></th>
                                    <th><?= lang('On-hand Total'); ?></th>

                                    <th><?= lang('Mv-In Qty'); ?></th>
                                    <th><?= lang('Mv-In U Cost'); ?></th>
                                    <th><?= lang('Mv-In Total'); ?></th>

                                    <th><?= lang('Mv-Out Qty'); ?></th>
                                    <th><?= lang('Mv-Out U Cost'); ?></th>
                                    <th><?= lang('Mv-Out Total'); ?></th>

                                    <th><?= lang('CB Qty'); ?></th>
                                    <th><?= lang('CB U Cost'); ?></th>
                                    <th><?= lang('CB Total'); ?></th>
                                </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                $count = 1;

                                $openQtyTotal = 0;
                                $openTotalTotals = 0;

                                $mvInQtyTotal = 0;
                                $mvInTotalTotals = 0;

                                $mvOutQtyTotal = 0;
                                $mvOutTotalTotals = 0;

                                $cbQtyTotal = 0;
                                $cbTotalTotals = 0;


                                foreach ($report_data as $item) {

                                    $openQtyTotal += $item->opening_quantity;
                                    $openTotalTotals += $item->opening_total;

                                    $mvInQtyTotal += $item->movement_in_quantity;
                                    $mvInTotalTotals += $item->movement_in_total;

                                    $mvOutQtyTotal += $item->movement_out_quantity;
                                    $mvOutTotalTotals += $item->movement_out_total;

                                    $cbQtyTotal += $item->closing_quantity;
                                    $cbTotalTotals += $item->closing_total;


                                ?>
                                    <tr>
                                        <td><?= $count; ?></td>
                                        <td><?= $item->product_id; ?></td>
                                        <td><?= $item->code; ?></td>
                                        <td><?= $item->name; ?></td>

                                        <td><?= $this->sma->formatQuantity($item->opening_quantity); ?></td>
                                        <td><?= $this->sma->formatDecimal($item->opening_cost); ?></td>
                                        <td><?= $this->sma->formatDecimal($item->opening_total); ?></td>

                                        <td><?= $this->sma->formatQuantity($item->movement_in_quantity); ?></td>
                                        <td><?= $this->sma->formatDecimal($item->movement_in_cost); ?></td>
                                        <td><?= $this->sma->formatDecimal($item->movement_in_total); ?></td>

                                        <td><?= $this->sma->formatQuantity($item->movement_out_quantity); ?></td>
                                        <td><?= $this->sma->formatDecimal($item->movement_out_cost); ?></td>
                                        <td><?= $this->sma->formatDecimal($item->movement_out_total); ?></td>

                                        <td><?= $this->sma->formatQuantity($item->closing_quantity); ?> </td>
                                        <td><?= $this->sma->formatDecimal($item->closing_cost); ?> </td>
                                        <td><?= $this->sma->formatDecimal($item->closing_total); ?> </td>

                                    </tr>
                                <?php
                                 $count++;
                                }
                               

                                ?>
                                <tr>
                                    <td colspan=4><strong>Totals</strong></td>
                                    <td><?= $this->sma->formatQuantity($openQtyTotal) ?></td>
                                    <td></td>
                                    <td><?= $this->sma->formatDecimal($openTotalTotals) ?></td>

                                    <td><?= $this->sma->formatQuantity($mvInQtyTotal) ?></td>
                                    <td></td>
                                    <td><?= $this->sma->formatDecimal($mvInTotalTotals) ?></td>

                                    <td><?= $this->sma->formatQuantity($mvOutQtyTotal) ?></td>
                                    <td></td>
                                    <td><?= $this->sma->formatDecimal($mvOutTotalTotals) ?></td>

                                    <td><?= $this->sma->formatQuantity($cbQtyTotal) ?></td>
                                    <td></td>
                                    <td><?= $this->sma->formatDecimal($cbTotalTotals) ?></td>
                                </tr>

                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>

                </div>

            </div>
        </div>
        <?php echo form_close(); ?>
    </div>