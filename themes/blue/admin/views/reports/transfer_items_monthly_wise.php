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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('Tansfer Items Monthly Wise'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'items_monlthy_wise.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a>
                </li>
                <li class="dropdown"> <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li>
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
            echo admin_form_open_multipart('reports/transfer_items_monthly_wise', $attrib)
        ?> <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" > 
                <div class="row">
                    <div class="col-lg-12">
                       
                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('From Date', 'podate'); ?>
                                <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <?= lang('To Date', 'podate'); ?>
                                <?php echo form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                            <?= lang('From Pharmacy', 'pofrompharmacy'); ?>
                            <?php
                            $selected_from_pharmacye_id[] = isset($from_pharmacy) ? $from_pharmacy : '';
                            $fromdp[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $fromdp[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('frompharmacy', $fromdp, $selected_from_pharmacye_id, 'id="from_pharmacy_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('from pharmacy') . '" required="required" style="width:100%;" ', null); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                            <?= lang('To Pharmacy', 'potopharmacy'); ?>
                            <?php
                            $selected_to_pharmacye_id[] = isset($to_pharmacy) ? $to_pharmacy : '';
                            $todp[''] = '';
                            foreach ($warehouses as $warehouse) {
                                $todp[$warehouse->id] = $warehouse->name;
                            }
                            echo form_dropdown('topharmacy', $todp, $selected_to_pharmacye_id, 'id="to_pharmacy_id" class="form-control input-tip select" data-placeholder="' . lang('select') . ' ' . lang('to pharmacy') . '" required="required" style="width:100%;" ', null); ?>
                            </div>
                        </div>


                        <div class="col-md-2">
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
                                class="table items table-striped table-bordered table-condensed table-hover sortable_table tbl_pdf">
                            <thead>
                            <tr>
                            
                                <th><?= lang('S. No'); ?></th>
                                <th><?= lang('Transfer Year'); ?></th>
                                <th><?= lang('Month'); ?></th>
                                <th><?= lang('Month Name'); ?></th>
                                <th><?= lang('Total Cost'); ?></th>
                                <th><?= lang('Total Sale'); ?></th>
                                <th><?= lang('Profit'); ?></th>
                                <th><?= lang('Profit %'); ?></th>
                            </tr>
                            </thead>
                            
       
                            <tbody style="text-align:center;">
                                <?php
                                    $count = 0;
                                    $grand_sales = 0;
                                    $grand_cost = 0;
                                    $grand_profit = 0;
                                    
                                    foreach ($response_data as $key => $data){
                                       
                                        $count ++ ; 
                                        
                                        $grand_sales += $data->total_sales ;
                                        $grand_cost += $data->total_cost;
                                        $grand_profit += $data->total_profit;
                                       
                                        ?>
            
                                            <tr class="report_transfer_link" year="<?=$data->year?>" month="<?=$data->month?>" 
                                                  from_date="<?=$start_date?>" to_date="<?=$end_date?>" from_pharmacy="<?=$from_pharmacy?>" to_pharmacy="<?=$to_pharmacy?>" >
                                            
                                                <td><?= $count; ?></td>
                                                <td><?= $data->year; ?></td>
                                                <td><?= $data->month; ?></td>
                                                <td><?= $data->month_name; ?></td>
                                                <td><?= $data->total_cost; ?></td>
                                                <td><?= $data->total_sales; ?></td>
                                                <td><?= $data->total_profit; ?></td>
                                                <td><?= $data->profit_percentage; ?></td>
                                               
                                                                
                                            </tr>
                                        <?php
                                    }
                                ?>
                                <tr>
                                    <td colspan="4"><strong>Totals: </strong></td>
                                    <td colspan="1"><strong><?= $this->sma->formatNumber($grand_cost); ?></strong></td>
                                    <td colspan="1"><strong><?= $this->sma->formatNumber($grand_sales); ?></strong></td>
                                    <td colspan="1"><strong><?= $this->sma->formatNumber($grand_profit); ?></strong></td>
                                    <td colspan="1"></td>
                                  
                                </tr>
                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div> 
            </div> 
        </div>
    </div> 
</div>


