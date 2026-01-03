<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Check Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .header h3 {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        
        .info-section p {
            margin: 3px 0;
            font-size: 11px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th,
        table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            font-size: 9px;
        }
        
        table th {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .difference-row {
            background-color: #fff3cd !important;
        }
        
        .difference-row td {
            color: red;
            font-weight: bold;
        }
        
        .changed-badge {
            background-color: #ffc107;
            color: #000;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .strikethrough {
            text-decoration: line-through;
            color: #999;
        }
        
        .highlight {
            background-color: #fff3cd;
            font-weight: bold;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding: 5px;
        }
        
        @page {
            margin-top: 30px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Inventory Check Report</h1>
        <h3><?= $warehouse_detail->name; ?></h3>
        <h3>Date: <?= date('Y-m-d', strtotime($inventory_check_request_details->date)); ?></h3>
        <?php if(isset($selected_shelf) && $selected_shelf != ''){ ?>
            <h3>Shelf: <?= $selected_shelf ?></h3>
        <?php } ?>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <p><strong>Request ID:</strong> <?= $inventory_check_request_details->id ?></p>
        <p><strong>Warehouse:</strong> <?= $warehouse_detail->name ?></p>
        <p><strong>Status:</strong> <?= ucfirst($inventory_check_request_details->status) ?></p>
        <p><strong>Generated:</strong> <?= date('Y-m-d H:i:s') ?></p>
        <?php if(isset($selected_shelf) && $selected_shelf != ''){ ?>
            <p><strong>Filtered by Shelf:</strong> <?= $selected_shelf ?></p>
        <?php } else { ?>
            <p><strong>Filter:</strong> All Shelves</p>
        <?php } ?>
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width:3%;">#</th>
                <th style="width:20%;">Product Name</th>
                <?php if($this->Settings->site_name == 'Hills Business Medical'){ ?>
                    <th style="width:6%;">Actual Shelf</th>
                    <th style="width:8%;">Old Code</th>
                    <th style="width:8%;">System Batch</th>
                    <th style="width:8%;">Actual Batch</th>
                    <th style="width:8%;">System Expiry</th>
                    <th style="width:8%;">Actual Expiry</th>
                <?php } ?>
                <th style="width:9%;">Actual Quantity</th>
            </tr>
        </thead>
        <tbody>
        <?php 
            if($inventory_check_array){
                $count = 0;
                foreach($inventory_check_array as $inventory_check){
                    $count++;
                    $variance = $inventory_check->quantity - ($inventory_check->system_quantity ?? 0);
                    $variance_class = ($variance != 0) ? 'difference-row' : '';
        ?>
            <tr class="<?= $variance_class ?>">
                <td style="text-align: center;"><?= $count; ?></td>
                <td><?= $inventory_check->product_name ? $inventory_check->product_name : '-'; ?></td>
                <?php if($this->Settings->site_name == 'Hills Business Medical'){ ?>
                    <td style="text-align: center;"><?= $inventory_check->actual_shelf ?? '-'; ?></td>
                    <td style="text-align: center;"><?= $inventory_check->item_code ?? '-'; ?></td>
                    
                    <?php 
                        // Check if batch or expiry was changed
                        $batch_changed = ($inventory_check->system_batch && $inventory_check->actual_batch && 
                                         $inventory_check->system_batch != $inventory_check->actual_batch);
                        $expiry_changed = ($inventory_check->system_expiry && $inventory_check->actual_expiry && 
                                          $inventory_check->system_expiry != $inventory_check->actual_expiry);
                    ?>
                    
                    <!-- System Batch -->
                    <td style="text-align: center;" class="<?= $batch_changed ? 'strikethrough' : '' ?>">
                        <?= $inventory_check->system_batch ?? '-'; ?>
                    </td>
                    
                    <!-- Actual Batch -->
                    <td style="text-align: center;" class="<?= $batch_changed ? 'highlight' : '' ?>">
                        <?= $inventory_check->actual_batch ?? '-'; ?>
                        <?php if($batch_changed) { ?>
                            <span class="changed-badge">Changed</span>
                        <?php } ?>
                    </td>
                    
                    <!-- System Expiry -->
                    <td style="text-align: center;" class="<?= $expiry_changed ? 'strikethrough' : '' ?>">
                        <?= $inventory_check->system_expiry ? date('d M y', strtotime($inventory_check->system_expiry)) : '-'; ?>
                    </td>
                    
                    <!-- Actual Expiry -->
                    <td style="text-align: center;" class="<?= $expiry_changed ? 'highlight' : '' ?>">
                        <?= $inventory_check->actual_expiry ? date('d M y', strtotime($inventory_check->actual_expiry)) : '-'; ?>
                        <?php if($expiry_changed) { ?>
                            <span class="changed-badge">Changed</span>
                        <?php } ?>
                    </td>
                <?php } ?>
                <td style="text-align: center; font-weight: bold;"> <?= number_format($inventory_check->quantity, 2); ?></td>
            </tr>
        <?php
                }
            }else{
        ?>
            <tr><td colspan="16" style="text-align: center;">No data available</td></tr>
        <?php
            }
        ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Generated by Avenzur ERP System | Page {PAGENO} of {nbpg}</p>
    </div>
</body>
</html>
