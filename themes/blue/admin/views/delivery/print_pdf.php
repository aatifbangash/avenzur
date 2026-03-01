<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delivery Invoice - <?= isset($delivery->id) ? $delivery->id : 'N/A' ?></title>
    
    <style>
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 11px;
            color: #2c3e50;
        }
        
        .delivery-invoice {
            width: 100%;
        }
        
        .invoice-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 15px;
        }
        
        .company-logo {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .invoice-title {
            font-size: 20px;
            color: #3498db;
            font-weight: bold;
            margin: 8px 0;
        }
        
        .invoice-number {
            font-size: 13px;
            color: #7f8c8d;
            margin: 4px 0;
        }
        
        .info-section {
            margin-bottom: 20px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 4px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 13px;
            color: #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #3498db;
            text-transform: uppercase;
        }
        
        .info-grid {
            width: 100%;
            margin-top: 8px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .info-item {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            color: #555;
            width: 30%;
            padding: 4px 0;
        }
        
        .info-value {
            display: table-cell;
            color: #2c3e50;
            padding: 4px 0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            page-break-inside: auto;
        }
        
        .items-table thead {
            background: #667eea;
            color: white;
        }
        
        .items-table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            border: 1px solid #5a6fd8;
        }
        
        .items-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
            page-break-inside: avoid;
        }
        
        .items-table td {
            padding: 8px;
            color: #2c3e50;
            border: 1px solid #e0e0e0;
            font-size: 10px;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .total-row {
            background: #ecf0f1;
            font-weight: bold;
            font-size: 11px;
        }
        
        .total-row td {
            padding: 12px 8px;
            color: #2c3e50;
            border: 2px solid #bdc3c7;
        }
        
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-box {
            display: inline-block;
            width: 30%;
            text-align: center;
            padding: 15px 5px;
            vertical-align: top;
        }
        
        .signature-line {
            border-top: 2px solid #2c3e50;
            margin-top: 50px;
            padding-top: 8px;
            font-weight: bold;
            color: #555;
            font-size: 11px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            color: #95a5a6;
            font-size: 9px;
            page-break-inside: avoid;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-pending {
            background: #f39c12;
            color: white;
        }
        
        .badge-out {
            background: #3498db;
            color: white;
        }
        
        .badge-delivered {
            background: #27ae60;
            color: white;
        }
        
        .refrigerated-badge {
            background: #e74c3c;
            color: white;
            font-size: 9px;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="delivery-invoice">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-logo"><?= $biller->name; ?></div>
            <div class="invoice-title">DELIVERY INVOICE</div>
            <div class="invoice-number">Delivery ID: <strong><?= isset($delivery->id) ? str_pad($delivery->id, 6, '0', STR_PAD_LEFT) : 'N/A' ?></strong></div>
            <div class="invoice-number">
                Date: <strong><?= isset($delivery->date_string) ? date('d M Y, h:i A', strtotime($delivery->date_string)) : date('d M Y, h:i A') ?></strong>
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="info-section">
            <div class="section-title">Delivery Information</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            <?php
                            $status = isset($delivery->status) ? $delivery->status : 'pending';
                            $status_class = 'badge-pending';
                            if ($status == 'out_for_delivery') {
                                $status_class = 'badge-out';
                            } elseif ($status == 'delivered') {
                                $status_class = 'badge-delivered';
                            }
                            ?>
                            <span class="badge <?= $status_class ?>"><?= ucwords(str_replace('_', ' ', $status)) ?></span>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Delivery Date:</span>
                        <span class="info-value"><?= isset($delivery->date_string) ? date('d M Y, h:i A', strtotime($delivery->date_string)) : 'N/A' ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Total Invoices:</span>
                        <span class="info-value"><strong><?= isset($delivery->invoice_count) ? $delivery->invoice_count : count($items) ?></strong></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Total Items:</span>
                        <span class="info-value"><strong><?= isset($delivery->total_items) ? $delivery->total_items : 0 ?></strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Driver & Vehicle Information -->
        <div class="info-section">
            <div class="section-title">Driver & Vehicle Details</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Driver Name:</span>
                        <span class="info-value"><strong><?= isset($delivery->driver_name) ? $delivery->driver_name : 'N/A' ?></strong></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Vehicle Number:</span>
                        <span class="info-value"><strong><?= isset($delivery->truck_no) ? $delivery->truck_no : (isset($delivery->truck_number) ? $delivery->truck_number : 'N/A') ?></strong></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-label">Odometer Reading:</span>
                        <span class="info-value"><?= isset($delivery->odometer) && $delivery->odometer ? number_format($delivery->odometer) . ' km' : 'Not Recorded' ?></span>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Items Table -->
        <div class="info-section">
            <div class="section-title">Delivery Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 18%;">Invoice No.</th>
                        <th style="width: 27%;">Customer</th>
                        <th style="width: 13%;">Invoice Date</th>
                        <th class="text-right" style="width: 15%;">Amount</th>
                        <th class="text-center" style="width: 10%;">Items</th>
                        <th class="text-center" style="width: 12%;">Refrigerated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (isset($items) && !empty($items)) {
                        $total_amount = 0;
                        $total_items = 0;
                        $total_refrigerated = 0;
                        $counter = 1;
                        
                        foreach ($items as $item): 
                            $total_amount += isset($item->total_amount) ? $item->total_amount : 0;
                            $total_items += isset($item->quantity_items) ? $item->quantity_items : 0;
                            $total_refrigerated += isset($item->refrigerated_items) ? $item->refrigerated_items : 0;
                    ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><strong><?= isset($item->reference_no) ? $item->reference_no : 'N/A' ?></strong></td>
                            <td><?= isset($item->customer_name) ? $item->customer_name : 'N/A' ?></td>
                            <td><?= isset($item->sale_date) ? date('d M Y', strtotime($item->sale_date)) : 'N/A' ?></td>
                            <td class="text-right"><?= isset($item->total_amount) ? number_format($item->total_amount, 2) : '0.00' ?> SAR</td>
                            <td class="text-center"><?= isset($item->quantity_items) ? $item->quantity_items : 0 ?></td>
                            <td class="text-center">
                                <?= isset($item->refrigerated_items) && $item->refrigerated_items > 0 ? '<span class="refrigerated-badge">' . $item->refrigerated_items . '</span>' : '-' ?>
                            </td>
                        </tr>
                    <?php 
                        endforeach; 
                    ?>
                        <tr class="total-row">
                            <td colspan="4" style="text-align: right; padding-right: 12px;">
                                <strong>GRAND TOTAL:</strong>
                            </td>
                            <td class="text-right">
                                <strong><?= number_format($total_amount, 2) ?> SAR</strong>
                            </td>
                            <td class="text-center">
                                <strong><?= $total_items ?></strong>
                            </td>
                            <td class="text-center">
                                <strong><?= $total_refrigerated > 0 ? '<span class="refrigerated-badge">' . $total_refrigerated . '</span>' : '-' ?></strong>
                            </td>
                        </tr>
                    <?php 
                    } else {
                    ?>
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 25px; color: #95a5a6;">
                                No items found in this delivery
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    Driver Signature
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    Receiver Signature
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    Date & Time
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong><?= $biller->name; ?></strong> | Pharmaceutical Distribution & Logistics</p>
            <p>This is a computer-generated delivery invoice. Generated on <?= date('d M Y, h:i A') ?></p>
            <p>For any queries, please contact our logistics department.</p>
        </div>
    </div>
</body>
</html>
