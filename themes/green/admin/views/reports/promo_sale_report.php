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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('promo_code_report'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                <a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'Promo_code_report.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                class="icon fa fa-file-excel-o"></i></a></li>
                
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
            $attrib = ['data-toggle' => 'validator', 'role' => 'form' ,'id' => 'searchForm'];
            echo admin_form_open_multipart('reports/daily_sales_with_promo_code', $attrib)
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
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary"
                                        id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>

                    </div>
                </div>
                <hr/>
                <?php echo form_close(); 
                } ?>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('Code'); ?></th>
                                <th><?= lang('name'); ?></th>
                                <th><?= lang('Quantity Sold'); ?></th>
                                <th><?= lang('Total Amount'); ?></th>
                                <th><?= lang('Promo Code'); ?></th>
                                
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                            <?php
                            $count = 1;
                            
                            foreach ($coupon_data as $data) {
                                ?>
                                <tr>
                                    <td><?= $count; ?></td>
                                    <td><?= $data['product_code']; ?></td>
                                    <td><?= $data['product_name']; ?></td>
                                    <td><?= $data['total_quantity_sold'] > 0 ? number_format($data['total_quantity_sold'], 2, '.', ',') : '-'; ?></td>
                                    <td><?= $data['total_amount_sold'] > 0 ? number_format($data['total_amount_sold'], 2, '.', ',').' SAR' : '-'; ?></td>
                                    <td><?= $data['coupon_code']?></td>
                                    
                                </tr>
                                <?php
                                $count++;
                            }
                            ?>

                            </tbody>
                            
                        </table>
                    </div>

                </div>

            </div>
        </div>
         
    </div>