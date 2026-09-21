<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script type="text/javascript">
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {sheet: 'Sheet 1'});
        XLSX.writeFile(wb, filename);
    }
</script>
<style>
    .difference-row {
        background-color: #fff3cd !important;
        color: red;
    }
    .difference-row:hover {
        background-color: #ffe69c !important;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('Inventory Check'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="exportTableToExcel('poTable', 'inventory-check.xlsx')"
                                        id="xls" class="tip" title="<?= lang('download_xls') ?>"><i
                                class="icon fa fa-file-excel-o"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('list_results'); ?></p>

                <div class="table-responsive">
                    <h3><b style="background: lightgrey;color: black;padding:10px;border: 1px solid grey;"><?= $warehouse_detail->name; ?> - ( <?= date('Y-m-d', strtotime($inventory_check_request_details->date)); ?> )</b></h3>
                    <?php 
                        if($this->Settings->site_name != 'Hills Business Medical'){
                    ?>
                        <?php $attrib = ['role' => 'form'];
                            echo admin_form_open_multipart('stock_request/adjust_inventory', $attrib);  
                        ?>
                        <input type="hidden" name="inventory_check_request_id" value="<?= $inventory_check_request_details->id; ?>" />
                        <input type="hidden" name="location_id" value="<?= $inventory_check_request_details->location_id; ?>" />
                        <?php 
                        if($inventory_check_request_details->status == 'pending'){
                            $data = array(
                                'name' => 'adjust_inv',
                                'onclick'=>"return confirm('Are you sure to proceed?')"
                            );
                            echo form_submit($data, $this->lang->line('Adjust Inventory'), 'id="adjust_inv" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); 
                            
                        }
                    }else{
                       ?>
                        <?php $attrib = ['role' => 'form'];
                            echo admin_form_open_multipart('stock_request/hills_adjust_inventory', $attrib);  
                        ?>
                        <input type="hidden" name="inventory_check_request_id" value="<?= $inventory_check_request_details->id; ?>" />
                        <input type="hidden" name="location_id" value="<?= $inventory_check_request_details->location_id; ?>" />
                        <?php 
                        if($inventory_check_request_details->status == 'pending'){
                            $data = array(
                                'name' => 'adjust_inv',
                                'onclick'=>"return confirm('Are you sure to proceed?')"
                            );
                            echo form_submit($data, $this->lang->line('Adjust Inventory'), 'id="adjust_inv" class="btn btn-primary" style="padding: 6px 15px; margin:15px 0;"'); 
                            
                        } 
                    }
                    ?>
                    <?php echo form_close(); ?>

                    <table id="poTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="width:2%;">#</th>
                            <th colspan="3"><?= lang('Product Name'); ?></th>
                            <?php 
                                if($this->Settings->site_name == 'Hills Business Medical'){
                            ?>
                            <th><?= lang('AVZ Codes'); ?></th>
                            <th><?= lang('Shelf'); ?></th>
                            <th><?= lang('Actual Shelf'); ?></th>
                            <th><?= lang('Group'); ?></th>
                            <th><?= lang('Old Code'); ?></th>
                            <th><?= lang('System Batch'); ?></th>
                            <th><?= lang('Actual Batch'); ?></th>
                            <th><?= lang('System Expiry'); ?></th>
                            <th><?= lang('Actual Expiry'); ?></th>
                            <?php } else { ?>
                            <th><?= lang('Avz Code'); ?></th>
                            <?php } ?>
                            <th><?= lang('Actual Quantity'); ?></th>
                            <th><?= lang('System Quantity'); ?></th>
                            <th><?= lang('Variance'); ?></th>
                            <th><?= lang('Cost'); ?></th>
                            <th><?= lang('Total Cost'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php 
                                if($inventory_check_array){
                                    $count = 0;
                                    foreach($inventory_check_array as $inventory_check){
                                        $count++;
                                        $variance = $inventory_check->quantity - $inventory_check->system_quantity;
                                        if($inventory_check_request_details->location_id == 48){
                                            $variance = 0;
                                        }
                                        $variance_class = ($variance != 0) ? 'difference-row' : '';
                                        ?>
                                            <tr class="<?= $variance_class ?>">
                                                <td class="dataTables_empty"><?= $count; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;" colspan="3"><?= $inventory_check->product_name ? $inventory_check->product_name : '-'; ?></td>
                                                <?php 
                                                    if($this->Settings->site_name == 'Hills Business Medical'){
                                                ?>
                                                <td class="dataTables_empty" style="text-align: center;" title="<?= $inventory_check->avz_codes ?? '-' ?>">
                                                    <span class="badge badge-info"><?= $inventory_check->avz_code_count ?? 0 ?> codes</span>
                                                </td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->shelf ?? '-'; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->actual_shelf ?? '-'; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->inventory_group ?? $inventory_check->group_name; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->item_code ?? '-'; ?></td>
                                                
                                                <?php 
                                                    // Check if batch or expiry was changed
                                                    $batch_changed = ($inventory_check->system_batch && $inventory_check->actual_batch && 
                                                                     $inventory_check->system_batch != $inventory_check->actual_batch);
                                                    $expiry_changed = ($inventory_check->system_expiry && $inventory_check->actual_expiry && 
                                                                      $inventory_check->system_expiry != $inventory_check->actual_expiry);
                                                ?>
                                                
                                                <!-- System Batch -->
                                                <td class="dataTables_empty" style="text-align: center; <?= $batch_changed ? 'text-decoration: line-through; color: #999;' : '' ?>">
                                                    <?= $inventory_check->system_batch ?? '-'; ?>
                                                </td>
                                                
                                                <!-- Actual Batch -->
                                                <td class="dataTables_empty" style="text-align: center; <?= $batch_changed ? 'background-color: #fff3cd; font-weight: bold;' : '' ?>">
                                                    <?= $inventory_check->actual_batch ?? '-'; ?>
                                                    <?php if($batch_changed) { ?>
                                                        <span class="label label-warning">Changed</span>
                                                    <?php } ?>
                                                </td>
                                                
                                                <!-- System Expiry -->
                                                <td class="dataTables_empty" style="text-align: center; <?= $expiry_changed ? 'text-decoration: line-through; color: #999;' : '' ?>">
                                                    <?= $inventory_check->system_expiry ? date('d M y', strtotime($inventory_check->system_expiry)) : '-'; ?>
                                                </td>
                                                
                                                <!-- Actual Expiry -->
                                                <td class="dataTables_empty" style="text-align: center; <?= $expiry_changed ? 'background-color: #fff3cd; font-weight: bold;' : '' ?>">
                                                    <?= $inventory_check->actual_expiry ? date('d M y', strtotime($inventory_check->actual_expiry)) : '-'; ?>
                                                    <?php if($expiry_changed) { ?>
                                                        <span class="label label-warning">Changed</span>
                                                    <?php } ?>
                                                </td>
                                                
                                                <?php } else { ?>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->avz_code ?? '-'; ?></td>
                                                <?php } ?>
                                                <td class="dataTables_empty" style="text-align: center; font-weight: bold;"> <?= number_format($inventory_check->quantity, 2); ?></td>
                                                <?php if($inventory_check_request_details->location_id == 48){ ?>
                                                    <td class="dataTables_empty" style="text-align: center;"><?= number_format($inventory_check->quantity, 2); ?></td>
                                                    <?php $variance = 0; ?>
                                                    <td class="dataTables_empty" style="text-align: center; <?= $variance > 0 ? 'color: green;' : ($variance < 0 ? 'color: red;' : '') ?> font-weight: bold;">
                                                    <?= $variance > 0 ? '+' : '' ?><?= number_format($variance, 2) ?>
                                                    </td>
                                                    <td class="dataTables_empty" style="text-align: center;"> <?= number_format($inventory_check->cost ?? 0, 2); ?></td>
                                                    <td class="dataTables_empty" style="text-align: center; font-weight: bold;">
                                                        <?= number_format(($inventory_check->cost * $inventory_check->quantity), 2); ?>
                                                    </td>
                                                <?php } else { ?>
                                                    <td class="dataTables_empty" style="text-align: center;"><?= number_format($inventory_check->system_quantity, 2); ?></td>
                                                    <td class="dataTables_empty" style="text-align: center; <?= $variance > 0 ? 'color: green;' : ($variance < 0 ? 'color: red;' : '') ?> font-weight: bold;">
                                                    <?= $variance > 0 ? '+' : '' ?><?= number_format($variance, 2) ?>
                                                    </td>
                                                    <td class="dataTables_empty" style="text-align: center;"> <?= number_format($inventory_check->cost ?? 0, 2); ?></td>
                                                    <td class="dataTables_empty" style="text-align: center; <?= ($inventory_check->total_cost ?? 0) > 0 ? 'color: green;' : ((($inventory_check->total_cost ?? 0) < 0) ? 'color: red;' : '') ?> font-weight: bold;">
                                                        <?= ($inventory_check->total_cost ?? 0) > 0 ? '+' : '' ?><?= number_format($inventory_check->total_cost ?? 0, 2); ?>
                                                    </td>
                                                <?php } ?>
                                                
                                                
                                            </tr>
                                            <?php
                                    }
                                }else{
                            ?>
                                <tr><td colspan="18" class="dataTables_empty"><?= lang('Could not load data'); ?></td></tr>
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
