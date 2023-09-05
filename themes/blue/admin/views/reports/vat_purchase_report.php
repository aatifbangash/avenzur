<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>
    $(document).ready(function () {
        
    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('vat_purchase_report').' (Invoice)'; ?></h2>

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
            echo admin_form_open_multipart('reports/vat_purchase', $attrib)
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
                                <th>#</th>
                                <th><?= lang('Purchases Type'); ?></th>
                                <th><?= lang('Date'); ?></th>
                                <th><?= lang('Invoice No.'); ?></th>
                                <th><?= lang('Invoice Date.'); ?></th>

                                <th><?= lang('Total Before Discount.'); ?></th>
                                <th><?= lang('Total Discount.'); ?></th>
                                <th><?= lang('Total After Discount.'); ?></th>

                                <th><?= lang('Total Items with VAT.'); ?></th>
                                <th><?= lang('Total Items Zero Vat.'); ?></th>

                                <th><?= lang('Total Purchases Value'); ?></th>
                                <th><?= lang('VAT on Purchases'); ?></th>
                                <th><?= lang('Total with VAT'); ?></th>

                                

                                <!-- <th><?= lang('Legal No.'); ?></th>
                                <th><?= lang('Vendor Code'); ?></th> -->
                                <th><?= lang('Vendor Name'); ?></th>
                                <th><?= lang('VAT No.'); ?></th>
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
                                        $totalQty += $data->total_quantity;
                                        $totalTax += $data->total_tax;
                                        $totalWithoutTax += ($data->total_with_vat - $data->total_tax);
                                        $totalWithTax += $data->total_with_vat;

                                        $totalTotalBeforeDiscount += $data->total_with_vat + $data->total_discount;;
                                        $totalTotalDiscount += $data->total_discount;
                                        $totalTotalAfterDiscount += $data->total_with_vat;


                                        $totalItemWithVAT += $data->total_item_with_vat;
                                        $totalItemWithZeroVAT += $data->total_item_with_zero_tax;

                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td>Purchase</td>
                                                <td><?= $data->date; ?></td>
                                                <td><?= $data->invoice_number; ?></td>
                                                <td><?= date("Y-m-d",str_replace('INV','',$data->invoice_number)); ?></td>
                                                
                                                <td><?= $this->sma->formatDecimal($data->total_with_vat + $data->total_discount); ?></td>
                                                <td><?= $this->sma->formatDecimal($data->total_discount); ?></td>
                                                <td><?= $this->sma->formatDecimal($data->total_with_vat); ?></td>

                                                <td><?= $this->sma->formatDecimal($data->total_item_with_vat); ?></td>
                                                <td><?= $this->sma->formatDecimal($data->total_item_with_zero_tax); ?></td>


                                                <td><?= $this->sma->formatDecimal($data->total_with_vat - $data->total_tax); ?></td>
                                                <td><?= $this->sma->formatDecimal($data->total_tax); ?></td>
                                                <td><?= $this->sma->formatDecimal($data->total_with_vat); ?></td>



                                                <!-- <td><?= $data->transaction_id; ?></td>
                                                <td><?= $data->supplier_code; ?></td> -->
                                                <td><?= $data->supplier; ?></td>
                                                <td><?= $data->vat_no; ?></td>
                                                <td><?= $data->account_number; ?></td>
                                                
                                                <!-- <td><?= $this->sma->formatQuantity($data->total_quantity); ?></td> -->
                                                <!-- <td><?= $data->tax_name; ?></td> -->
                                                
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

                                    <th class="text-center"><?=$this->sma->formatDecimal($totalTotalBeforeDiscount)?></th>
                                    <th class="text-center"><?=$this->sma->formatDecimal($totalTotalDiscount)?></th>
                                    <th class="text-center"><?=$this->sma->formatDecimal($totalTotalAfterDiscount)?></th>


                                    <th class="text-center"><?=$this->sma->formatDecimal($totalItemWithVAT)?></th>
                                    <th class="text-center"><?=$this->sma->formatDecimal($totalItemWithZeroVAT)?></th>

                                    <th class="text-center"><?= $this->sma->formatDecimal($totalWithoutTax); ?></th>
                                    <th class="text-center"><?= $this->sma->formatDecimal($totalTax); ?></th>
                                    <th class="text-center"><?= $this->sma->formatDecimal($totalWithTax); ?></th>



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
