<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {sheet: 'Sheet 1'});
        XLSX.writeFile(wb, filename);
    }

    $(document).ready(function () {

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('pharmacy_stock_report'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'stock.xlsx')"
                                        id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
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
            echo admin_form_open_multipart('reports/pharmacy_stock', $attrib)
            ?>
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <?php echo lang('Item', 'item'); ?>
                            <?php echo form_input('sgproduct', (isset($_POST['sgproduct']) ? $_POST['sgproduct'] : ''), 'class="form-control" id="suggest_product2" data-bv-notempty="true"'); ?>
                            <input type="hidden" name="item" value="<?= isset($_POST['item']) ? $_POST['item'] : 0 ?>" id="report_product_id2" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="from-group">
                            <button type="submit" name="submit" style="margin-top: 28px;" class="btn btn-primary"
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
                                <th><?= lang('PH1 Batch'); ?></th>
                                <th><?= lang('PH1 Stock'); ?></th>
                                <th><?= lang('PH1 Expiry'); ?></th>
                                <th><?= lang('PH2 Batch'); ?></th>
                                <th><?= lang('PH2 Stock'); ?></th>
                                <th><?= lang('PH2 Expiry'); ?></th>
                                <th><?= lang('PH3 Batch'); ?></th>
                                <th><?= lang('PH3 Stock'); ?></th>
                                <th><?= lang('PH3 Expiry'); ?></th>
                                <th><?= lang('PH4 Batch'); ?></th>
                                <th><?= lang('PH4 Stock'); ?></th>
                                <th><?= lang('PH4 Expiry'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            <?php if (!empty($stock_data)): ?>
                                <?php
                                $totalQuantity = 0;
                                $totalSalePrice = 0;
                                $grandTotalSalePrice = 0;
                                $totalPurchasePrice = 0;
                                $grandTotalPurchasePrice = 0;
                                $totalCostPrice = 0;
                                $grandTotalCostPrice = 0;
                                ?>
                                <?php foreach ($stock_data as $index => $row): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= $row->item_code ?></td>
                                        <td><?= $row->name ?></td>
                                        <td><?= $row->batch_no ?></td>
                                        <td><?= $row->quantity ?></td>
                                        <td><?= $row->expiry ?></td>

                                        <td><?= $row->batch_no ?></td>
                                        <td><?= $row->quantity ?></td>
                                        <td><?= $row->expiry ?></td>

                                        <td><?= $row->batch_no ?></td>
                                        <td><?= $row->quantity ?></td>
                                        <td><?= $row->expiry ?></td>

                                        <td><?= $row->batch_no ?></td>
                                        <td><?= $row->quantity ?></td>
                                        <td><?= $row->expiry ?></td>

                                        <?php $totalQuantity += $row->quantity; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <th colspan="14">No records to show.</th>
                                </tr>
                            <?php endif; ?>

                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Total</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th><?= $totalQuantity ?></th>

                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th><?= $totalQuantity ?></th>

                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th><?= $totalQuantity ?></th>

                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th><?= $totalQuantity ?></th>
                                
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
