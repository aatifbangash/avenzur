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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('Total Income Report'); ?></h2>
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
            echo admin_form_open_multipart('reports/total_income', $attrib)
        ?>
        <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
                <div class="row"> 
                <div class="col-lg-12"> 
                        
                        <div class="col-md-4">
                            <div class="form-group">
                            <?= lang('supplier', 'posupplier'); ?>
                            <?php
                            $selected_supplier_id[] = isset($supplier_id) ? $supplier_id : '';
                            $sp[''] = '';
                            foreach ($suppliers as $supplier) {
                                $sp[$supplier->id] = $supplier->company. ' ('. $supplier->name.')'.' - '.$supplier->sequence_code;
                            }
                            echo form_dropdown('supplier', $sp, $selected_supplier_id, 'id="supplier_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '" required="required" style="width:100%;" ', null); ?>
                            </div>
                        </div>

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

                    </div>
                    <div class="col-lg-12">
                       
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
                                
                                <th><?= lang('Inv. No'); ?></th>
                                <th><?= lang('Date'); ?></th>
                                <th><?= lang('Supplier'); ?></th>
                                <th><?= lang('Type'); ?></th>
                                <th><?= lang('Total Purchase') ?></th>
                                <th><?= lang('Total Discount') ?></th>
                                <th><?= lang('Net Purchase') ?></th>
                                <th><?= lang('Total Sale') ?></th>
                                <th><?= lang('Bonus') ?></th>
                                <th><?= lang('Net Profit') ?></th>
                                <th><?= lang('Profit %') ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php 
                                    $grand_total_invoice = 0;
                                    $grand_total_sale = 0;
                                    $grand_total_discount = 0;
                                    $count = 0;
                                    $grand_bonus = 0;
                                    foreach($income_data as $row){
                                        $net_profit = $row->total_sale - $row->total_net_purchase;

                                        $grand_total_invoice += $row->total_purchase;
                                        $grand_total_discount += $row->total_discount;
                                        $grand_total_sale += $row->total_sale;
                                        $grand_total_net_purchase += $row->total_net_purchase;
                                        $grand_bonus += $row->total_bonus;
                                        $count++;
                                        ?>
                                        <tr>
                                            
                                            <td><?= $row->id; ?></td>
                                            <td><?= $row->inv_date; ?></td>
                                            <td><?= $row->supplier; ?></td>
                                            <td><?= $row->type; ?></td>
                                            <td><?= $this->sma->formatNumber($row->total_purchase); ?></td>
                                            <td><?= $this->sma->formatNumber($row->total_discount); ?></td>
                                            <td><?= $this->sma->formatNumber($row->total_net_purchase); ?></td>
                                            <td><?= $this->sma->formatNumber($row->total_sale); ?></td>
                                            <td><?= $row->total_bonus; ?></td>
                                            <td><?= $this->sma->formatNumber($net_profit); ?></td>
                                            <td><?php echo $this->sma->formatNumber(($net_profit) / ($row->total_sale)).'%'; ?></td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                                
                            </tbody>
                            <tfoot>
                                <tr>
                                    
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>

                                    <th class="text-center"><?= $this->sma->formatNumber($grand_total_invoice); ?></th>
                                    <th class="text-center"><?= $this->sma->formatNumber($grand_total_discount); ?></th>
                                    <th class="text-center"><?= $grand_total_net_purchase ? $this->sma->formatNumber($grand_total_net_purchase) : $this->sma->formatNumber(0.00); ?></th>
                                    <th class="text-center"><?= $this->sma->formatNumber($grand_total_sale); ?></th>
                                    <th class="text-center"><?= $this->sma->formatNumber($grand_bonus); ?></th>
                                    <th class="text-center"><?= $this->sma->formatNumber($grand_total_sale); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                
            </div>

        </div>
    </div>
   
</div>
