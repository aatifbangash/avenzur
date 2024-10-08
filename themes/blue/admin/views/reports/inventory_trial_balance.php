<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<?php if($viewtype!='pdf'){ ?>
<style>
.tableFixHead          { overflow: auto; height: 100px; }
.tableFixHead thead  { position: sticky; top: 0; z-index: 1; }
/* Just common table stuff. Really. */ 
.tableFixHead thead th, td {background:#eee;  padding: 8px 16px; }
</style>
<?php } ?>

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
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('inventory_movement_report'); ?></h2>
        <?php  if($viewtype!='pdf'){?>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'inventry_trail_balance_report.xlsx')" id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
                <li class="dropdown"> <a href="javascript:void(0);" onclick="generatePDF()" id="pdf" class="tip" title="<?= lang('download_PDF') ?>"><i class="icon fa fa-file-pdf-o"></i></a></li>
            </ul>
        </div>
        <?php } ?>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            if($viewtype!='pdf')
            {
                $attrib = ['data-toggle' => 'validator', 'role' => 'form','id' => 'searchForm'];
                echo admin_form_open_multipart('reports/inventory_trial_balance', $attrib)
                ?>
                <input type="hidden" name="viewtype" id="viewtype" class="viewtype" value="" >
            <div class="col-lg-12">
                <div class="row">

                   <!-- <div class="col-lg-12">
                        <div class="col-md-4">
                            <div class="form-group">
                                <?= lang('Warehouse', 'from_warehouse_id'); ?>
                                <?php echo form_dropdown('from_warehouse_id', $warehouses, set_value('from_warehouse_id', $_POST['from_warehouse_id']), array('class' => 'form-control', 'id' => 'from_warehouse_id'), array('none')); ?>

                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="form-group">
                                <?= lang('To Warehouse', 'to_warehouse_id'); ?>
                                <?php echo form_dropdown('to_warehouse_id', $warehouses, set_value('to_warehouse_id', $_POST['to_warehouse_id']), array('class' => 'form-control', 'id' => 'to_warehouse_id'), array('none')); ?>

                            </div>
                        </div>
                    </div> -->

                    <div class="col-lg-12">
                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('From Date', 'podate'); ?>
                                <?php echo form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('To Date', 'podate'); ?>
                                <?php echo form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <?= lang('Warehouse', 'from_warehouse_id'); ?>
                                <?php echo form_dropdown('from_warehouse_id', $warehouses, set_value('from_warehouse_id', $_POST['from_warehouse_id']), array('class' => 'form-control', 'id' => 'from_warehouse_id'), array('none')); ?>

                            </div>
                         </div>   

                        <div class="col-md-2">
                            <div class="from-group">
                                <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>

                </div>
                <hr />
                <?php echo form_close(); 
                } ?>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable" class="table items table-striped table-bordered table-condensed table-hover tableFixHead tbl_order tbl_tb">
                            <thead>

                                <tr>

                                    <th colspan=4>ITEM</th>
                                    <th colspan=3>Opening Balance</th>
                                    <th colspan=3>Movement In</th>
                                    <th colspan=3>Movement Out</th>
                                    <th colspan=3>Closing Balance</th>
                                </tr>
                                <tr>
                                    <th>SN</th>
                                    <th><?= lang('Item No'); ?></th>
                                    <th><?= lang('Item Code'); ?></th>
                                    <th><?= lang('Item Desc'); ?></th>

                                    <th><?= lang('On-hand Qty'); ?></th>
                                    <th><?= lang('On-hand U Cost'); ?></th>
                                    <th><?= lang('On-hand Total'); ?></th>

                                    <th><?= lang('Mv-In Qty'); ?></th>
                                    <th><?= lang('Mv-In U Cost'); ?></th>
                                    <th><?= lang('Mv-In Total'); ?></th>

                                    <th><?= lang('Mv-Out Qty'); ?></th>
                                    <th><?= lang('Mv-Out U Cost'); ?></th>
                                    <th><?= lang('Mv-Out Total'); ?></th>

                                    <th><?= lang('CB Qty'); ?></th>
                                    <th><?= lang('CB U Cost'); ?></th>
                                    <th><?= lang('CB Total'); ?></th>
                                </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php
                                $count = 1;

                                $openQtyTotal = 0;
                                $openTotalTotals = 0;

                                $mvInQtyTotal = 0;
                                $mvInTotalTotals = 0;

                                $mvOutQtyTotal = 0;
                                $mvOutTotalTotals = 0;

                                $cbQtyTotal = 0;
                                $cbTotalTotals = 0;


                                foreach ($inventryReportData as  $item ) {
                                    $item = (array) $item;
                                    $openQtyTotal += $item['openning_qty'];

                                    $item['openning_ttl'] = $item['openning_qty'] * $item['openning_cost'];
                                    $openTotalTotals += $item['openning_ttl'];

                                    $mvInQtyTotal += $item['movement_in_qty'];
                                    $item['movement_in_ttl'] = $item['movement_in_qty'] * $item['movement_in_cost'];
                                    $mvInTotalTotals += $item['movement_in_ttl'];

                                    $mvOutQtyTotal += $item['movement_out_qty'];
                                    $item['movement_out_ttl'] = $item['movement_out_qty'] * $item['movement_out_cost'];
                                    $mvOutTotalTotals += $item['movement_out_ttl'];

                                     $cbQtyTotal += $item['closing_qty'];
                                     $item['closing_cost'] = ($item['openning_ttl'] +  $item['movement_in_ttl'] + abs($item['movement_out_ttl']) ) / ($item['openning_qty'] + $item['movement_in_qty'] + abs($item['movement_out_qty']));
                                     $item['closing_ttl'] = $item['closing_qty'] * $item['closing_cost'];
                                     $cbTotalTotals += $item['closing_ttl'];
                                    // $item['closing_ttl'] = $item['closing_qty'] * $item['closing_cost'];
                                    // $cbTotalTotals += $item['closing_ttl'];
                                   // $closing_cost =  ($item['openning_ttl'] +  $item['openning_ttl'] + $item['openning_ttl']) / $item['openning_qty'] + $item['movement_in_qty'] + $item['movement_out_qty'];

                                ?>
                                    <tr>
                                        <td><?= $count; ?></td>
                                        <td><?= $item['product_id']; ?></td>
                                        <td><?= $item['product_code']; ?></td>
                                        <td><?= $item['product_name']; ?></td>

                                        <td><?= $this->sma->formatQuantity($item['openning_qty']); ?></td>
                                        <td><?= $this->sma->formatMoney($item['openning_cost'], 'none'); ?></td>
                                        <td><?= $this->sma->formatMoney($item['openning_ttl'], 'none'); ?></td>

                                        <td><?= $this->sma->formatQuantity($item['movement_in_qty']); ?></td>
                                        <td><?= $this->sma->formatMoney($item['movement_in_cost'], 'none'); ?></td>
                                        <td><?= $this->sma->formatMoney($item['movement_in_ttl'], 'none'); ?></td>

                                        <td><?= $this->sma->formatQuantity(abs( $item['movement_out_qty']) ); ?></td>
                                        <td><?= $this->sma->formatMoney(abs( $item['movement_out_cost']), 'none'); ?></td>
                                        <td><?= $this->sma->formatMoney( abs($item['movement_out_ttl']), 'none'); ?></td>

                                        <td><?= $this->sma->formatQuantity($item['closing_qty']); ?> </td>
                                        <td><?= $this->sma->formatMoney($item['closing_cost'], 'none'); ?> </td>
                                        <td><?= $this->sma->formatMoney( $item['closing_ttl'], 'none'); ?> </td>

                                    </tr>
                                <?php
                                    $count++;
                                }


                                ?>
                                <tr>
                                    <td colspan=4><strong>Totals</strong></td>
                                    <td><?= $this->sma->formatQuantity($openQtyTotal) ?></td>
                                    <td></td>
                                    <td><?= $this->sma->formatMoney($openTotalTotals, 'none') ?></td>

                                    <td><?= $this->sma->formatQuantity($mvInQtyTotal) ?></td>
                                    <td></td>
                                    <td><?= $this->sma->formatMoney($mvInTotalTotals, 'none') ?></td>

                                    <td><?= $this->sma->formatQuantity($mvOutQtyTotal) ?></td>
                                    <td></td>
                                    <td><?= $this->sma->formatMoney($mvOutTotalTotals, 'none') ?></td>

                                    <td><?= $this->sma->formatQuantity($cbQtyTotal) ?></td>
                                    <td></td>
                                    <td><?= $this->sma->formatMoney($cbTotalTotals, 'none') ?></td>
                                </tr>

                            </tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>

                </div>

            </div>
        </div>
        
    </div>