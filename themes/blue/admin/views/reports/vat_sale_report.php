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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('Vat Sale Report').' (Invoice)'; ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
            <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'vat_sale.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                                class="icon fa fa-file-excel-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
        <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('reports/vat_sale', $attrib)
        ?>
        <div class="col-lg-12">
                <div class="row">

                <div class="col-lg-12">

                        <div class="col-md-6">
                            <div class="form-group">
                               
                            <div class="form-group">
                                <?= lang('Warehouse', 'warehouse_id'); ?>
                                <?php echo form_dropdown('warehouse_id', $warehouses, set_value('warehouse_id', $_POST['warehouse_id']), array('class' => 'form-control', 'id' => 'warehouse_id'),array('none')); ?>

                            </div>
                        </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('Type', 'Type'); ?>
                                <?php echo form_dropdown('filterOnType', $filterOnTypeArr, set_value('filterOnType', $_POST['filterOnType']), array('class' => 'form-control', 'data-placeholder' => "-- Select Type --", 'id' => 'filterOnType'),array('none')); ?>

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
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>SR</th>
                                <th><?= lang('Trx Type'); ?></th>
                                <th><?= lang('Branch'); ?></th>
                                <th><?= lang('INV. NO'); ?></th>
                                <th><?= lang('INV DATE.'); ?></th>

                                <th><?= lang('TOTAL INV.')//lang('Total Before Discount.'); ?></th>
                                <th><?= lang('T.DIS')//lang('Total Discount.'); ?></th>
                                <th><?= lang('T.AFTER DIS')//lang('Total After Discount.'); ?></th>

                                <th><?= lang('15% VAT VALUE')//lang('Total Items with VAT.'); ?></th>
                                <th><?= lang('0% VAT VALUE')//lang('Total Items Zero Vat.'); ?></th>

                                <!-- <th><?= lang('Total Purchases Value'); ?></th> -->
                                <th><?= lang('VAT Amount')//lang('VAT on Purchases'); ?></th>
                                <th><?= lang('NET INV.')//lang('Total with VAT'); ?></th>

                                

                                <!-- <th><?= lang('Legal No.'); ?></th>
                                <th><?= lang('Vendor Code'); ?></th> -->
                                <th><?= lang('CUSTOMER NAME'); ?></th>
                                <th><?= lang('CUSTOMER VAT NO.'); ?></th>
                                <th><?= lang('G/L NO.'); ?></th>
                                
                                <!-- <th><?= lang('Qty'); ?></th> -->
                                <!-- <th><?= lang('Tax'); ?></th> -->
                              
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 1;
                                    $totalQty = 0;
                                    $totalTax = 0;
                                    $totalWithoutTax = 0;
                                    $totalWithTax = 0;

                                    $totalTotalBeforeDiscount = 0;
                                    $totalTotalDiscount = 0;
                                    $totalTotalAfterDiscount = 0;


                                    $totalItemWithVAT = 0;
                                    $totalItemWithZeroVAT = 0;

                                    $totalWithTax = 0;
                                    foreach ($vat_purchase as $data){
                                        //$totalQty += $data->total_quantity;
                                        $totalTax += $data->total_tax;
                                        $totalWithoutTax += ($data->grand_total - $data->total_tax);
                                        $totalWithTax += $data->grand_total;

                                        $totalTotalBeforeDiscount += $data->grand_total + $data->total_discount;;
                                        $totalTotalDiscount += $data->total_discount;
                                        $totalTotalAfterDiscount += $data->grand_total;


                                        $totalItemWithVAT += $data->total_item_with_vat;
                                        $totalItemWithOutVAT += $data->total_item_without_tax;

                                        ?>
                                            <tr>
                                                <td><?= $data->trans_ID; ?></td>
                                                <td><?=$data->trans_type?></td>
                                                <td><?= $data->warehouse; ?></td>
                                                <td><?= $data->reference_no; ?></td>
                                                <td><?= $data->trans_date; ?></td>
                                                
                                                <td><?= $this->sma->formatMoney($data->grand_total+$data->total_discount,'none'); ?></td>
                                                <td><?= $this->sma->formatMoney($data->total_discount,'none'); ?></td>
                                                <td><?= $this->sma->formatMoney($data->grand_total,'none'); ?></td>

                                                <td><?= $this->sma->formatMoney($data->total_item_with_vat,'none'); ?></td>
                                                <td><?= $this->sma->formatMoney($data->total_item_without_tax,'none'); ?></td>


                                                <!-- <td><?= $this->sma->formatMoney($data->grand_total - $data->total_tax,'none'); ?></td> -->
                                                <td><?= $this->sma->formatMoney($data->total_tax,'none'); ?></td>
                                                <td><?= $this->sma->formatMoney($data->grand_total,'none'); ?></td>



                                                <td><?= $data->customer_name; ?></td>
                                                <td><?= $data->customer_vat_no; ?></td>
                                                <td><?= $data->ledger_entry_number; ?></td>
                                                
                                                
                                            </tr>
                                        <?php
                                        $count++;
                                    }
                                ?>
                                
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>

                                    <th class="text-center"><?=$this->sma->formatMoney($totalTotalBeforeDiscount,'none')?></th>
                                    <th class="text-center"><?=$this->sma->formatMoney($totalTotalDiscount,'none')?></th>
                                    <th class="text-center"><?=$this->sma->formatMoney($totalTotalAfterDiscount,'none')?></th>


                                    <th class="text-center"><?=$this->sma->formatMoney($totalItemWithVAT,'none')?></th>
                                    <th class="text-center"><?=$this->sma->formatMoney($totalItemWithOutVAT,'none')?></th>

                                    <!-- <th class="text-center"><?= $this->sma->formatMoney($totalWithoutTax,'none'); ?></th> -->
                                    <th class="text-center"><?= $this->sma->formatMoney($totalTax,'none'); ?></th>
                                    <th class="text-center"><?= $this->sma->formatMoney($totalWithTax,'none'); ?></th>



                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <!-- <th>&nbsp;</th> -->
                                    <!-- <th>&nbsp;</th>
                                    <th>&nbsp;</th> -->
                                    <!-- <th class="text-center"><?= $this->sma->formatQuantity($totalQty); ?></th> -->
                                    <!-- <th>&nbsp;</th> -->
                                   
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                
            </div>

        </div>
    </div>
    <?php echo form_close(); ?>
</div>
