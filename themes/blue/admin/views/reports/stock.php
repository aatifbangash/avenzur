<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if($viewtype!='pdf'){ ?>
    <style> 
    .tableFixHead          { overflow: auto; height: 100px; }
    .tableFixHead thead  { position: sticky; top: 0; z-index: 1; }
    /* Just common table stuff. Really. */ 
    .tableFixHead thead th, td {background:#eee;  padding: 8px 16px; }
    </style> 
<?php  } ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function generatePDF(){
       $('.viewtype').val('pdf');  
       $('#load_report').trigger('click'); 
       //document.getElementById("searchForm").submit(); 
       $('.viewtype').val('');  
    }
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {sheet: 'Sheet 1'});
        XLSX.writeFile(wb, filename);
    }
    
    $(document).ready(function () {

    });
</script>
<?php if($viewtype=='pdf'){ ?>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet"> 
  <?php  } ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('stock_report'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <!-- <li class="dropdown"><a href="#" id="image" class="tip" title= <?= lang('save_image') ?> "><i  class="icon fa fa-file-picture-o"></i></a></li> -->
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'stock.xlsx')"   id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>                              
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
                    $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm', 'name'=>'searchForm'];
                    echo admin_form_open_multipart('reports/stock', $attrib)
                    ?>
                    <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" >
                
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('At Date', 'at_date'); ?>
                                    <?php echo form_input('at_date', ($at_date ?? ''), 'class="form-control input-tip date" id="at_date"'); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('Store', 'warehouse'); ?>
                                    <?php
                                    $optionsWarehouse[0] = 'Select';
                                    if (!empty($warehouses)) {
                                        foreach ($warehouses as $warehouse) {
                                            $optionsWarehouse[$warehouse->id] = $warehouse->name;
                                        }
                                    }

                                    ?>
                                    <?php echo form_dropdown('warehouse', $optionsWarehouse, set_value('warehouse'), array('class' => 'form-control disable-select'), array('none')); ?>

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('Supplier', 'supplier'); ?>
                                    <?php
                                    $optionsSuppliers[0] = 'Select';
                                    if (!empty($suppliers)) {
                                        foreach ($suppliers as $sup) {
                                            $optionsSuppliers[$sup->id] = $sup->name;
                                        }
                                    }
                                    ?>
                                    <?php echo form_dropdown('supplier', $optionsSuppliers, set_value('supplier'), array('class' => 'form-control disable-select'), array('none')); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4" style="margin-left: 15px; width: 32.6%">
                            <div class="form-group">
                                <?= lang('Item Group', 'item_group'); ?>
                                <?php
                                $optionsCategories[0] = 'Select';
                                if (!empty($categories)) {
                                    foreach ($categories as $cat) {
                                        $optionsCategories[$cat->id] = $cat->name;
                                    }
                                }
                                ?>
                                <?php echo form_dropdown('item_group', $optionsCategories, set_value('item_group'), array('class' => 'form-control disable-select'), array('none')); ?>

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <?php echo lang('Item', 'item'); ?>
                                <?php echo form_input('sgproduct', (isset($_POST['sgproduct']) ? $_POST['sgproduct'] : ''), 'class="form-control" id="suggest_product2" data-bv-notempty="true"'); ?>
                                <input type="hidden" name="item" value="<?= isset($_POST['item']) ? $_POST['item'] : 0 ?>" id="report_product_id2" />
                            </div>
                        </div>
                        <!--<div class="col-md-4">
                            <div class="form-group">
                                <?php //echo lang('Item', 'item'); ?>
                                <?php //echo form_input('item', set_value('item'), 'class="form-control input-tip" id="item"'); ?>
                            </div>
                        </div>-->
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
                    <?php echo form_close(); 
                } ?>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table tableFixHead tbl_pdf">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('Item Code'); ?></th>
                                <th><?= lang('Item Name'); ?></th>
                                <th><?= lang('Batch No'); ?></th>
                                <th><?= lang('Expiry'); ?></th>
                                <th><?= lang('Quantity Balance'); ?></th>
                                <th><?= lang('Sale Price'); ?></th>
                                <th><?= lang('Total Sale Price'); ?></th>
                                <th><?= lang('Purchase Price'); ?></th>
                                <th><?= lang('Total Purchase Price'); ?></th>
                                <th><?= lang('Cost Price'); ?></th>
                                <th><?= lang('Total Cost Price'); ?></th>
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
                                        <td><?= $row->expiry ?></td>

                                        <td><?= $row->quantity ?></td>
                                        <?php $totalQuantity += $row->quantity; ?>

                                        <td><?= number_format($row->sale_price, 2, '.', ',') ?></td>
                                        <?php $totalSalePrice += $row->sale_price; ?>

                                        <td><?= number_format($row->sale_price * $row->quantity, 2, '.', ',') ?></td>
                                        <?php $grandTotalSalePrice += $row->sale_price * $row->quantity; ?>

                                        <td><?= number_format($row->purchase_price, 2, '.', ',') ?></td>
                                        <?php $totalPurchasePrice += $row->purchase_price; ?>

                                        <td><?= number_format($row->purchase_price * $row->quantity, 2, '.', ',') ?></td>
                                        <?php $grandTotalPurchasePrice += $row->purchase_price * $row->quantity; ?>

                                        <td><?= number_format($row->cost_price, 2, '.', ',') ?></td>
                                        <?php $totalCostPrice += $row->cost_price; ?>
                                        <td><?= number_format($row->cost_price * $row->quantity, 2, '.', ',') ?></td>
                                        <?php $grandTotalCostPrice += $row->cost_price * $row->quantity; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <th colspan="12">No records to show.</th>
                                </tr>
                            <?php endif; ?>

                            </tbody>
                            <tfoot>
                            <tr>
                                <th>Total</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th>&nbsp;</th>
                                <th><?= $totalQuantity ?></th>
                                <th><?= number_format($totalSalePrice, 2, '.', ',') ?></th>
                                <th><?= number_format($grandTotalSalePrice, 2, '.', ',') ?></th>
                                <th><?= number_format($totalPurchasePrice, 2, '.', ',') ?></th>
                                <th><?= number_format($grandTotalPurchasePrice, 2, '.', ',') ?></th>
                                <th><?= number_format($totalCostPrice, 2, '.', ',') ?></th>
                                <th><?= number_format($grandTotalCostPrice, 2, '.', ',') ?></th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
