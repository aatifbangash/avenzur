<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {sheet: 'Sheet 1'});
        XLSX.writeFile(wb, filename);
    }

    $(document).ready(function () {

    });
</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-users"></i><?= lang('supplier_stock_report'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'stock.xlsx')"
                                        id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                                class="icon fa fa-file-excel-o"></i></a></li>
                <!--                <li class="dropdown"><a href="#" id="image" class="tip" title="-->
                <? //= lang('save_image') ?><!--"><i-->
                <!--                                class="icon fa fa-file-picture-o"></i></a></li>-->
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <?php
            $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
            echo admin_form_open_multipart('reports/supplier_stock', $attrib)
            ?>
            <div class="col-lg-12">
                
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
                </div>

                <div class="row">
                    <div class="col-lg-12">
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
                            <div class="from-group">
                                <button type="submit" name="submit" style="margin-top: 28px;" class="btn btn-primary"
                                        id="load_report"><?= lang('Load Report') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <div class="controls table-controls" style="font-size: 12px !important;">
                        <table id="poTable"
                               class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th><?= lang('Supplier'); ?></th>
                                <th><?= lang('Item Code'); ?></th>
                                <th><?= lang('Item Name'); ?></th>
                                <th><?= lang('Quantity'); ?></th>
                                <th><?= lang('Sale Price'); ?></th>
                                <th><?= lang('Total Value'); ?></th>
                            </tr>
                            </thead>
                            <tbody style="text-align:center;">
                                <?php 
                                    if(sizeof($stock_data) > 0){
                                        $count = 0;
                                        $grandTotalQantity = 0;
                                        $grandTotalValue = 0;
                                        $grandTotalPrice = 0;
                                        foreach ($stock_data as $row){
                                            $count++;
                                            $grandTotalQantity += $row->total_quantity;
                                            $total_value = ($row->total_quantity * $row->price);
                                            $grandTotalValue += $total_value;
                                            ?>
                                                <tr>
                                                    <td><?= $count; ?></td>
                                                    <td><?= $row->supplier; ?></td>
                                                    <td><?= $row->product_code; ?></td>
                                                    <td><?= $row->product_name; ?></td>
                                                    <td><?= $row->total_quantity; ?></td>
                                                    <td><?= $row->price; ?></td>
                                                    <td><?= $total_value; ?></td>
                                                </tr>
                                            <?php
                                        }
                                    }else{
                                        echo '<tr><td colspan="7">No Records Found...</td></tr>';
                                    }
                                ?>

                                <tr>
                                    <td colspan="2"><b>Totals</b></td>
                                    <td colspan="2">&nbsp;</td>
                                    <td><b><?php echo $this->sma->formatQuantity($grandTotalQantity); ?></b></td>
                                    <td><b><?php echo '-'; ?></b></td>
                                    <td><b><?php echo $this->sma->formatMoney($grandTotalValue, 'none'); ?></b></td>
                                </tr>

                            </tbody>
                            <tfoot>
                            
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
