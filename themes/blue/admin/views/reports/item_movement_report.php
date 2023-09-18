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
                                <?php echo form_dropdown('filterOnType', $filterOnTypeArr, set_value('filterOnType', $_POST['filterOnType']), array('class' => 'form-control', 'data-placeholder' => "-- Select Type --", 'id' => 'filterOnType')); ?>

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
                                    <th><?= lang('SN'); ?></th>
                                    <th><?= lang('Date'); ?></th>
                                    <th><?= lang('Document No'); ?></th>
                                    <th><?= lang('Type'); ?></th>
                                    <th><?= lang('Name Of'); ?></th>
                                    <th><?= lang('Expire Date'); ?></th>
                                    <th><?= lang('Batch No.'); ?></th>
                                    <th><?= lang('Sale Price'); ?></th>
                                    <th><?= lang('Purchase Price'); ?></th>
                                    <th><?= lang('Quantity'); ?></th>
                                    <th><?= lang('Unit Cost'); ?></th>
                                    <th><?= lang('Item balance quantity'); ?></th>
                                    <th><?= lang('Value of item current balance'); ?></th>
                                </tr>
                            </thead>

                            <?php if ($reportData) { ?>
                                <tbody style="text-align:center;">
                                    <tr>
                                        <td colspan="2">Oening Balance</td>
                                        <td colspan="8">&nbsp;</td>
                                        <td><?php echo $this->sma->formatMoney($itemOpenings->unitPrice, 'none'); ?></td>
                                        <td><?php echo $this->sma->formatQuantity(($itemOpenings->openingBalance > 0 ? $itemOpenings->openingBalance : 0.00)); ?></td>
                                        <td><?php echo $this->sma->formatMoney(($itemOpenings->openingBalance > 0 && $itemOpenings->unitPrice > 0 ? $itemOpenings->openingBalance * $itemOpenings->unitPrice  : 0.00), 'none'); ?></td>

                                    </tr>

                                    <?php
                                    $count = 1;
                                    $balanceQantity = $itemOpenings->openingBalance;

                                    foreach ($reportData as $rp) {

                                        if ($rp->type == 'Purchase' || $rp->type == 'Return-Customer' || $rp->type == "Transfer-In") {
                                            $balanceQantity += $rp->quantity;
                                        }
                                        if (($rp->type == 'Sale' || $rp->type == 'Return-Supplier' || $rp->type == "Transfer-Out") && $balanceQantity > 0) {
                                            $balanceQantity -= $rp->quantity;
                                        }

                                        if ($rp->type ==  'Transfer-Out' || $rp->type == "Transfer-In") {
                                            $type = 'Transfer';
                                        } else {
                                            $type = $rp->type;
                                        }

                                    ?>
                                        <tr>
                                            <td><?= $count; ?></td>
                                            <td><?= $rp->entry_date; ?></td>
                                            <td><?= $rp->document_no; ?></td>
                                            <td><?= $type; ?></td>
                                            <td><?= $rp->name_of; ?></td>
                                            <td><?= $rp->expiry_date; ?></td>
                                            <td><?= $rp->batch_no; ?></td>
                                            <td><?= $this->sma->formatMoney(($rp->sale_price ? $rp->sale_price : 0.0), 'none'); ?></td>
                                            <td><?= $this->sma->formatMoney(($rp->purchase_price ? $rp->purchase_price : 0.0), 'none'); ?></td>
                                            <td><?= $this->sma->formatQuantity($rp->quantity ? $rp->quantity : 0.0); ?></td>
                                            <td><?= $this->sma->formatMoney(($rp->unit_cost ? $rp->unit_cost : 0.0), 'none'); ?></td>
                                            <td><?= $this->sma->formatQuantity($balanceQantity); ?></td>
                                            <td><?= $this->sma->formatMoney(($balanceQantity * $itemOpenings->unitPrice), 'none'); ?></td>
                                        </tr>
                                    <?php
                                        $count++;
                                    }


                                    ?>

                                    <tr>
                                        <td colspan="2">Closing</td>
                                        <td colspan="9">&nbsp;</td>
                                        <td><?php echo $this->sma->formatQuantity($balanceQantity); ?></td>
                                        <td><?php echo $this->sma->formatMoney(($balanceQantity * $itemOpenings->unitPrice), 'none'); ?></td>

                                    </tr>

                                </tbody>
                            <?php } ?>
                            <tfoot></tfoot>
                        </table>
                    </div>

                </div>

            </div>
        </div>
        <?php echo form_close(); ?>
    </div>

    <?php
    $productId = ($_POST['product'] ? $_POST['product'] : 0);
    $type = ($_POST['filterOnType'] ? $_POST['filterOnType'] : 'all');
    $startDate = ($_POST['from_date'] ? trim($this->sma->fld($_POST['from_date'])) : null);
    $endDate = ($_POST['to_date'] ? trim($this->sma->fld($_POST['to_date'])) : null);
    ?>

    <script type="text/javascript" src="<?= $assets ?>js/html2canvas.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#xls').click(function(event) {
                var prod = $('#report_product_id2').val();
                var fromdate = $('#fromdate').val();
                var todate = $('#todate').val();
                if (prod && fromdate && todate) {
                    event.preventDefault();
                    window.location.href = "<?= admin_url("reports/item_movement_report_xls/$productId/$type/$startDate/$endDate/xls") ?>";
                    return false;
                } else {
                    return false;
                }
            });
            $('#image').click(function(event) {
                event.preventDefault();
                html2canvas($('.box'), {
                    onrendered: function(canvas) {
                        openImg(canvas.toDataURL());
                    }
                });
                return false;
            });
        });
    </script>