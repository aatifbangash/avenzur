<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {
            sheet: 'Sheet 1'
        });
        XLSX.writeFile(wb, filename);
    }
    function generatePDF(){
       $('.viewtype').val('pdf');  
       document.getElementById("searchForm").submit();
       $('.viewtype').val(''); 
    } 
    $(document).ready(function() {

    });
</script>
<?php if($viewtype=='pdf'){ ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet"> 
  <?php  } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('vat_purchase_report').' (Ledger)'; ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'Vat_Purchase_Report.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
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
            echo admin_form_open_multipart('reports/vat_purchase_ledger', $attrib)
        ?>
         <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
        
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
                <?php echo form_close(); 
                } ?>
                <hr />
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table tbl_pdf tbl_vat_purchase">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('Date'); ?></th>
                                <th><?= lang('Invoice No.'); ?></th>
                                <th><?= lang('Legal No.'); ?></th>
                                <th><?= lang('Vendor Code'); ?></th>
                                <th><?= lang('Vendor Name'); ?></th>
                                <th><?= lang('VAT No.'); ?></th>
                                <th><?= lang('Purchases Type'); ?></th>
                                <th><?= lang('Qty'); ?></th>
                                <!-- <th><?= lang('Tax'); ?></th> -->
                                <th><?= lang('Total Purchases Value'); ?></th>
                                <th><?= lang('VAT on Purchases'); ?></th>
                                <th><?= lang('Total with VAT'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 1;
                                    $totalQty = 0;
                                    $totalTax = 0;
                                    $totalWithoutTax = 0;
                                    $totalWithTax = 0;
                                    foreach ($vat_purchase as $data){
                                        $totalQty += $data->total_quantity;
                                        $totalTax += $data->total_tax;
                                        $totalWithoutTax += ($data->total_with_vat - $data->total_tax);
                                        $totalWithTax += $data->total_with_vat;
                                        ?>
                                            <tr>
                                                <td><?= $count; ?></td>
                                                <td><?= $data->date; ?></td>
                                                <td><?php

                                                if($data->type == 'Purchase'){
                                                    echo $data->invoice_number;
                                                }else{
                                                    echo $data->number;
                                                }
                                                
                                                ?></td>
                                                <td>
                                                <?php 
                                                if($data->type == 'Purchase'){
                                                    echo $data->purchase_sequence_code;
                                                }else{
                                                    echo $data->transaction_id;
                                                }
                                                ?>    
                                                
                                                </td>
                                                <td><?= $data->supplier_code; ?></td>
                                                <td><?= $data->supplier; ?></td>
                                                <td><?= $data->vat_no; ?></td>
                                                <td><?= $data->type; ?></td>
                                                <td><?= $this->sma->formatQuantity($data->total_quantity); ?></td>
                                                <!-- <td><?= $data->tax_name; ?></td> -->
                                                <td><?=  $this->sma->formatNumber($data->total_with_vat - $data->total_tax); ?></td>
                                                <td><?= $this->sma->formatNumber($data->total_tax); ?></td>
                                                <td><?= $this->sma->formatNumber($data->total_with_vat); ?></td>
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
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th class="text-center"><?= $this->sma->formatQuantity($totalQty); ?></th>
                                    <!-- <th>&nbsp;</th> -->
                                    <th class="text-center"><?= $this->sma->formatNumber($totalWithoutTax); ?></th>
                                    <th class="text-center"><?= $this->sma->formatNumber($totalTax); ?></th>
                                    <th class="text-center"><?= $this->sma->formatNumber($totalWithTax); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                
            </div>

        </div>
    </div>
    
</div>
