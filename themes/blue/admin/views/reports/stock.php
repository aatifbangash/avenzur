<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, { sheet: 'Sheet 1' });
        XLSX.writeFile(wb, filename);
    }
    $(document).ready(function () {

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('stock_report'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'stock.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                                class="icon fa fa-file-excel-o"></i></a></li>
                <!--                <li class="dropdown"><a href="#" id="image" class="tip" title="-->
                <? //= lang('save_image') ?><!--"><i-->
                <!--                                class="icon fa fa-file-picture-o"></i></a></li>-->
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
                                <?php echo form_input('at_date', ($at_date ?? ''), 'class="form-control input-tip date" id="at_date"'); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Store', 'warehouse'); ?>
                                <?php
                                $optionsWarehouse[0] = 'Select';
                                if (!empty($warehouses)) {
                                    foreach ($warehouses as $warehouse) {
                                        $optionsWarehouse[$warehouse->id] = $warehouse->name;
                                    }
                                }

                                ?>
                                <?php echo form_dropdown('warehouse', $optionsWarehouse, $_POST['warehouse'], array('class' => 'form-control', 'data-placeholder' => "-- Select --", 'id' => 'warehouse')); ?>

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Supplier', 'supplier'); ?>
                                <?php
                                $optionsSuppliers[0] = 'Select';
                                if (!empty($suppliers)) {
                                    foreach ($suppliers as $sup) {
                                        $optionsSuppliers[$sup->id] = $sup->name;
                                    }
                                }
                                ?>
                                <?php echo form_dropdown('supplier', $optionsSuppliers, set_value('supplier'), array('class' => 'form-control', 'data-placeholder' => "-- Select --", 'id' => 'supplier_field')); ?>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4" style="margin-left: 15px; width: 32.6%">
                        <div class="form-group">
                            <?= lang('Item Group', 'item_group'); ?>
                            <?php
                            $optionsCategories[0] = 'Select';
                            if (!empty($categories)) {
                                foreach ($categories as $cat) {
                                    $optionsCategories[$cat->id] = $cat->name;
                                }
                            }
                            ?>
                            <?php echo form_dropdown('item_group', $optionsCategories, set_value('item_group'), array('class' => 'form-control', 'data-placeholder' => "-- Select --", 'id' => 'item_group')); ?>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Item', 'item'); ?>
                            <?php echo form_input('item', set_value('item'), 'class="form-control input-tip" id="item"'); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="from-group">
                            <button type="submit" style="margin-top: 28px;" class="btn btn-primary"
                                    id="load_report"><?= lang('Load Report') ?></button>
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
                                <?php
                                $totalQuantity = 0;
                                $totalSalePrice = 0;
                                $totalCostPrice = 0;
                                ?>
                                <?php foreach ($stock_data as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $row->item_code ?></td>
                                        <td><?= $row->name ?></td>
                                        <td><?= $row->batch_no ?></td>
                                        <td><?= $row->expiry ?></td>

                                        <td><?= $row->quantity ?></td>
                                        <?php $totalQuantity += $row->quantity; ?>

                                        <td><?= number_format($row->sale_price, 2, '.', ',') ?></td>
                                        <?php $totalSalePrice += $row->sale_price; ?>

                                        <td><?= number_format($row->cost_price, 2, '.', ',') ?></td>
                                        <?php $totalCostPrice += $row->cost_price; ?>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <th colspan="8">No records found.</th>
                                </tr>
                            <?php endif; ?>

                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Total</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th><?= $totalQuantity ?></th>
                                <th><?= number_format($totalSalePrice, 2, '.', ',') ?></th>
                                <th><?= number_format($totalCostPrice, 2, '.', ',') ?></th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
