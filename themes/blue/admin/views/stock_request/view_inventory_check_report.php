<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script type="text/javascript">
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {
            sheet: 'Sheet 1'
        });
        XLSX.writeFile(wb, filename);
    }
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('Inventory Check Report'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('TOData', 'inventory.xlsx')"   id="xls" class="tip" title="<?= lang('download_xls') ?>"><i class="icon fa fa-file-excel-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <h3><b style="background: lightgrey;color: black;padding:10px;border: 1px solid grey;"><?= $warehouse_detail->name; ?> - ( <?= date('Y-m-d', strtotime($inventory_check_request_details->date)); ?> )</b></h3>

                    <table id="TOData" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="width:2%;">#</th>
                            <th><?= lang('Product Code'); ?></th>
                            <th><?= lang('Patch No.'); ?></th>
                            <th><?= lang('Product Name'); ?></th>
                            <th><?= lang('Old Bal'); ?></th>
                            <th><?= lang('New Bal'); ?></th>
                            <th><?= lang('Var'); ?></th>
                            <th><?= lang('Cost P. Old'); ?></th>
                            <th><?= lang('Cost P. New'); ?></th>
                            <th><?= lang('Sales Price'); ?></th>
                            <th><?= lang('Short'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php 
                                if($inventory_check_report_data){
                                    $count = 0;
                                    foreach($inventory_check_report_data as $inventory_check){
                                        $count++;
                                        ?>
                                            <tr>
                                                <td class="dataTables_empty"><?= $count; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->item_code; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->avz_code; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->item_name ? $inventory_check->item_name : '-'; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"> <?= $inventory_check->old_qty ? $inventory_check->old_qty : '0.00'; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->new_qty ? $inventory_check->new_qty : '0.00'; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->variance ? $inventory_check->variance : '0.00'; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->old_cost_price ? $inventory_check->old_cost_price : '0.00'; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->new_cost_price ? $inventory_check->new_cost_price : '0.00'; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->sales_price ? $inventory_check->sales_price : '0.00'; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->short ? $inventory_check->short : '0'; ?></td>
                                            </tr>
                                            <?php
                                    }
                                }else{
                            ?>
                                <tr><td colspan="11" class="dataTables_empty"><?= lang('Could not load data'); ?></td></tr>
                            <?php
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
