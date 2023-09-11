<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function() {

        $("#warehouse").select2().select2('val', <?= $warehouse; ?>);
        $('#warehouse').select2().trigger('change');
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('Item Movement Report'); ?></h2>

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
            echo admin_form_open_multipart('reports/item_movement_report', $attrib)
            ?>
            <div class="col-lg-12">
                <div class="row">

                    <div class="col-lg-12">
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('Product', 'product'); ?>
                                <?php // echo form_dropdown('product', $allProducts, set_value('product',$product),array('class' => 'form-control', 'id'=>'product'));
                                ?>
                                <?php echo form_input('sgproduct', (isset($_POST['sgproduct']) ? $_POST['sgproduct'] : ''), 'class="form-control" id="suggest_product2" data-bv-notempty="true"'); ?>
                                <input type="hidden" name="product" value="<?= isset($_POST['product']) ? $_POST['product'] : 0 ?>" id="report_product_id2" />
                            </div>
                        </div>

                         <div class="col-md-6">
                            <div class="form-group">
                            <?= lang('Type', 'Type'); ?>
                                <?php echo form_dropdown('filterOnType', $filterOnTypeArr, set_value('filterOnType', $_POST['filterOnType']), array('class' => 'form-control', 'data-placeholder'=>"-- Select Type --", 'id' => 'filterOnType')); ?>
                               
                            </div>
                        </div>
                        
                    </div>

                    <div class="col-lg-12">

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('From Date', 'fromdate'); ?>
                                <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('To Date', 'todate'); ?>
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
                                    <th><?= lang('Date'); ?></th>
                                    <th><?= lang('Invoice No.'); ?></th>
                                    <th><?= lang('Transaction No.'); ?></th>
                                    <th><?= lang('Description'); ?></th>
                                    <th><?= lang('Name Of'); ?></th>
                                    <th><?= lang('Expiry'); ?></th>
                                    <th><?= lang('Batch No.'); ?></th>
                                    <th><?= lang('Quantity'); ?></th>
                                    <th><?= lang('Unit Price'); ?></th>
                                    <th><?= lang('Sale Price'); ?></th>
                                    <th><?= lang('Current Stock'); ?></th>
                                    <th><?= lang('Total Cost'); ?></th>
                                </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                $count = 1;
                                $preItemQuantity = $preItemQuantity;
                                $effectiveQuantity = 0;
                                $totalCost = 0;
                                foreach ($inventory_array as $idxData) {

                                    foreach ($idxData as $item) {

                                        if ($effectiveQuantity == 0) {
                                            $effectiveQuantity = $preItemQuantity;
                                        }

                                        if ($item['description'] == 'Sale') {
                                            $effectiveQuantity = $effectiveQuantity - $item['quantity'];
                                            $totalCost = $effectiveQuantity * $item['unitCost'];
                                        }

                                        if ($item['description'] == 'Purchase') {
                                            $effectiveQuantity = $effectiveQuantity + $item['quantity'];
                                            $totalCost = $effectiveQuantity * $item['unitCost'];
                                        }

                                        if ($item['description'] == 'RT Supplier') {
                                            $effectiveQuantity = $effectiveQuantity - (-1 * $item['quantity']);
                                            $totalCost = $effectiveQuantity * $item['unitCost'];
                                        }

                                        if ($item['description'] == 'Return') {
                                            $effectiveQuantity = $effectiveQuantity + $item['quantity'];
                                            $totalCost = $effectiveQuantity * $item['unitCost'];
                                        }

                                        // No Effect
                                        if ($item['description'] == 'Transfer') {
                                            // if ($item['negate'] == true) {
                                            //     $effectiveQuantity = $effectiveQuantity - $item['quantity'];
                                            // } else {
                                            //     $effectiveQuantity = $effectiveQuantity + $item['quantity'];
                                            // }
                                            $totalCost = $effectiveQuantity * $item['unitCost'];
                                        }
                                ?>
                                        <tr>
                                            <td><?= $item['date']; ?></td>
                                            <td><?= $item['documentNo']; ?></td>
                                            <td><?= $item['accountTransId']; ?></td>
                                            <td><?= $item['description']; ?></td>
                                            <td><?= $item['nameOf']; ?></td>
                                            <td><?= $item['expiry']; ?></td>
                                            <td><?= $item['batch']; ?></td>
                                            <td><?= $this->sma->formatQuantity($item['quantity']); ?></td>
                                            <td><?= $this->sma->formatDecimal($item['unitCost']); ?></td>
                                            <td><?= $this->sma->formatDecimal($item['salePrice']); ?></td>
                                            <td><?= $this->sma->formatQuantity($effectiveQuantity); ?></td>
                                            <td><?= $this->sma->formatDecimal($totalCost); ?></td>


                                        </tr>
                                <?php

                                    }
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