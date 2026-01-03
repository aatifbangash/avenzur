<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script type="text/javascript">
    function exportTableToExcel(tableId, filename = 'table.xlsx') {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {sheet: 'Sheet 1'});
        XLSX.writeFile(wb, filename);
    }
    
    function filterByShelf() {
        var shelf = $('#shelf_filter').val();
        var url = window.location.href.split('?')[0];
        if(shelf) {
            window.location.href = url + '?shelf=' + encodeURIComponent(shelf);
        } else {
            window.location.href = url;
        }
    }
    
    function downloadPDF() {
        var shelf = $('#shelf_filter').val();
        var url = window.location.href.split('?')[0] + '?pdf=1';
        if(shelf) {
            url += '&shelf=' + encodeURIComponent(shelf);
        }
        window.location.href = url;
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
    .filter-section {
        margin-bottom: 20px;
        padding: 15px;
        background-color: #f5f5f5;
        border-radius: 4px;
    }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-star-o"></i><?= lang('Inventory Check'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="javascript:void(0);" onclick="downloadPDF()"
                                        id="pdf" class="tip" title="<?= lang('download_pdf') ?>"><i
                                class="icon fa fa-file-pdf-o"></i></a></li>
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

                <!-- Shelf Filter Section -->
                <div class="filter-section">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="shelf_filter"><?= lang('Filter by Shelf'); ?></label>
                                <select id="shelf_filter" class="form-control" onchange="filterByShelf()">
                                    <option value=""><?= lang('All Shelves'); ?></option>
                                    <?php 
                                    if($shelves){
                                        foreach($shelves as $shelf_option){
                                    ?>
                                        <option value="<?= $shelf_option->shelf ?>" <?= (isset($selected_shelf) && $selected_shelf == $shelf_option->shelf) ? 'selected' : '' ?>>
                                            <?= $shelf_option->shelf ?>
                                        </option>
                                    <?php 
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <?php if(isset($selected_shelf) && $selected_shelf != ''){ ?>
                        <div class="col-md-8">
                            <div class="alert alert-info" style="margin-top: 25px;">
                                <i class="fa fa-info-circle"></i> <?= lang('Showing results for shelf:'); ?> <strong><?= $selected_shelf ?></strong>
                                <a href="<?= current_url() ?>" class="btn btn-xs btn-default" style="margin-left: 10px;">
                                    <i class="fa fa-times"></i> <?= lang('Clear Filter'); ?>
                                </a>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <h3><b style="background: lightgrey;color: black;padding:10px;border: 1px solid grey;"><?= $warehouse_detail->name; ?> - ( <?= date('Y-m-d', strtotime($inventory_check_request_details->date)); ?> )</b></h3>
                
                    <table id="poTable" cellpadding="0" cellspacing="0" border="0"
                           class="table table-bordered table-condensed table-hover table-striped">
                        <thead>
                        <tr class="active">
                            <th style="width:2%;">#</th>
                            <th colspan="3"><?= lang('Product Name'); ?></th>
                            <?php 
                                if($this->Settings->site_name == 'Hills Business Medical'){
                            ?>
                            <th><?= lang('Actual Shelf'); ?></th>
                            <th><?= lang('Old Code'); ?></th>
                            <th><?= lang('System Batch'); ?></th>
                            <th><?= lang('Actual Batch'); ?></th>
                            <th><?= lang('System Expiry'); ?></th>
                            <th><?= lang('Actual Expiry'); ?></th>
                            <?php } ?>
                            <th><?= lang('Actual Quantity'); ?></th>
                            
                        </tr>
                        </thead>
                        <tbody>
                        <?php 
                                if($inventory_check_array){
                                    $count = 0;
                                    foreach($inventory_check_array as $inventory_check){
                                        $count++;
                                        $variance = $inventory_check->quantity - $inventory_check->system_quantity;
                                        $variance_class = ($variance != 0) ? 'difference-row' : '';
                                        ?>
                                            <tr class="<?= $variance_class ?>">
                                                <td class="dataTables_empty"><?= $count; ?></td>
                                                <td class="dataTables_empty" style="text-align: center;" colspan="3"><?= $inventory_check->product_name ? $inventory_check->product_name : '-'; ?></td>
                                                <?php 
                                                    if($this->Settings->site_name == 'Hills Business Medical'){
                                                ?>
                                                <td class="dataTables_empty" style="text-align: center;"><?= $inventory_check->actual_shelf ?? '-'; ?></td>
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
                                                
                                                <?php } ?>
                                                <td class="dataTables_empty" style="text-align: center; font-weight: bold;"> <?= number_format($inventory_check->quantity, 2); ?></td>
                                                
                                            </tr>
                                            <?php
                                    }
                                }else{
                            ?>
                                <tr><td colspan="16" class="dataTables_empty"><?= lang('Could not load data'); ?></td></tr>
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
