<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('stock_report'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="#" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                                class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"><a href="#" id="image" class="tip" title="<?= lang('save_image') ?>"><i
                                class="icon fa fa-file-picture-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('reports/stock', $attrib)
            ?>
            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('At Date', 'at_date'); ?>
                                <?php echo form_input('at_date', ($start_date ?? ''), 'class="form-control input-tip date" id="at_date"'); ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('Store', 'warehouse'); ?>
                                <?php echo form_dropdown('filterOnType', ['name' => 'atif'], set_value('filterOnType', $_POST['filterOnType']), array('class' => 'form-control', 'data-placeholder' => "-- Select Type --", 'id' => 'filterOnType')); ?>

                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary"
                                        id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('Item Code'); ?></th>
                                <th><?= lang('Item Name'); ?></th>
                                <th><?= lang('Batch No'); ?></th>
                                <th><?= lang('Expiry'); ?></th>
                                <th><?= lang('Quantity Balance'); ?></th>
                                <th><?= lang('Sale Price'); ?></th>
                                <th><?= lang('Cost Price'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            <?php if (!empty($stock_data)): ?>
                                <?php foreach ($stock_data as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $row->item_code ?></td>
                                        <td><?= $row->name ?></td>
                                        <td><?= $row->batch_no ?></td>
                                        <td><?= $row->expiry ?></td>
                                        <td><?= $row->quantity ?></td>
                                        <td><?= $row->sale_price ?></td>
                                        <td><?= $row->cost_price ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            </tbody>
                            <tfoot>
                            <tr>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
