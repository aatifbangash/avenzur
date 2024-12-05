<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, { sheet: 'Sheet 1' });
        XLSX.writeFile(wb, filename);
    }
    function generatePDF(){
       $('.viewtype').val('pdf');  
       document.getElementById("searchForm").submit();
       $('.viewtype').val(''); 
    } 
    $(document).ready(function () {

    });
</script>
<?php if($viewtype=='pdf'){ ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet"> 
  <?php  } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('vat_purchase_report').' (Invoice)'; ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'vat_purchase.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"> <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>"><i
                class="icon fa fa-file-pdf-o"></i></a></li>
            </ul>
        </div>
        <?php } ?>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12"> 
        <?php
        if($viewtype!='pdf')
        {
            $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm'];
            echo admin_form_open_multipart('reports/vat_purchase', $attrib)
        ?>
        <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
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
                <?php echo form_close(); 
                } ?>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table tbl_pdf tbl_vat_purchase">
                            <thead>
                            <tr>
                                <th>SR</th>
                                <th><?= lang('Trx Type'); ?></th>
                                <th><?= lang('Branch'); ?></th>
                                <!-- <th><?= lang('INV. NO'); ?></th> -->
                                <th><?= lang('INV DATE.'); ?></th>

                                <th><?= lang('TOTAL INV.')//lang('Total Before Discount.'); ?></th>
                                <th><?= lang('T.DIS')//lang('Total Discount.'); ?></th>
                                <th><?= lang('T.AFTER DIS')//lang('Total After Discount.'); ?></th>
<!-- 
                                <th><?= lang('15% VAT VALUE')//lang('Total Items with VAT.'); ?></th>
                                <th><?= lang('0% VAT VALUE')//lang('Total Items Zero Vat.'); ?></th> -->

                                <!-- <th><?= lang('Total Purchases Value'); ?></th> -->
                                <th><?= lang('VAT Amount')//lang('VAT on Purchases'); ?></th>
                                <th><?= lang('NET INV.')//lang('Total with VAT'); ?></th>

                                

                                <!-- <th><?= lang('Legal No.'); ?></th>
                                <th><?= lang('Vendor Code'); ?></th> -->
                                <th><?= lang('SUPPLIER NAME'); ?></th>
                                <th><?= lang('SUPPLIER VAT NO.'); ?></th>
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
                                        // echo "<pre>";
                                        // print_r($data);
                                        $sign = "";
                                        
                                        if($data->trans_type == "returnSupplier"){                                         
                                            $sign = "-";
                                        }
                                               $total_invoice = $data->total_invoice;
                                               $total_discount  =   $data->total_discount;
                                               $total_after_discount    =   $data->total_after_discount;
                                               $total_tax   =   $data->total_tax;
                                               $net_total = $data->grand_total;

                                               $grand_total_invoice += $total_invoice;
                                               $grand_total_discount += $total_discount;
                                               $grand_after_discount += $total_after_discount;
                                               $grand_total_tax += $total_tax;
                                               $grand_total += $net_total;
                                            
                                               if( $data->trans_type == 'returnSupplier' ) {
                                                $modal_class = 'oreturn_supplier_link';
                                               }else{
                                                $modal_class = 'purchase_link';
                                               }
                                               

                                        ?>
                                            <tr id="<?= $data->trans_ID; ?>" class="<?=$modal_class;?>">
                                                <td><?= $data->trans_ID; ?></td>
                                                <td><?=$data->trans_type?></td>
                                                <td><?= $data->warehouse; ?></td>
                                                <!-- <td><?= $data->reference_no; ?></td> -->
                                                <td><?= $data->trans_date; ?></td>
                                                
                                                <td><?= $total_invoice;//$this->sma->formatMoney($data->grand_total + $data->total_discount - $data->total_tax,'none'); ?></td>
                                                <td><?= $total_discount;//$this->sma->formatMoney($data->total_discount,'none'); ?></td>
                                                <td><?= $total_after_discount;//$this->sma->formatMoney($data->grand_total - $data->total_tax,'none'); ?></td>

                                                <td><?= $total_tax; ?></td>
                                                <td><?= $net_total; ?></td>



                                                <td><?= $data->supplier_name; ?></td>
                                                <td><?= $data->supplier_vat_no; ?></td>
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
                                    

                                    <th class="text-center"><?=$grand_total_invoice;?></th>
                                    <th class="text-center"><?=$grand_total_discount ?></th>
                                    <th class="text-center"><?=$grand_after_discount; //$this->sma->formatMoney($totalTotalAfterDiscount,'none')?></th>
                                   
                                   <th class="text-center"><?=$grand_total_tax; //$this->sma->formatMoney($totalTax,'none'); ?></th>
                                    <th class="text-center"><?=$grand_total; //$this->sma->formatMoney($totalWithTax,'none'); ?></th>
                                    <th>&nbsp;</th>


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
   
</div>
