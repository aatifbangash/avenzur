<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Invoice - <?= isset($delivery->id) ? $delivery->id : 'N/A' ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= $assets ?>styles/theme.css" />
    <link rel="stylesheet" href="<?= $assets ?>styles/style.css" />
    
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 13px;
            background: #fff;
        }
        
        .delivery-invoice {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 20px;
        }
        
        .company-logo {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .invoice-title {
            font-size: 24px;
            color: #3498db;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .invoice-number {
            font-size: 16px;
            color: #7f8c8d;
            margin: 5px 0;
        }
        
        .info-section {
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #3498db;
            text-transform: uppercase;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 10px;
        }
        
        .info-item {
            display: flex;
            padding: 8px 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            min-width: 140px;
            flex-shrink: 0;
        }
        
        .info-value {
            color: #2c3e50;
            flex-grow: 1;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .items-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .items-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        
        .items-table tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: background 0.2s;
        }
        
        .items-table tbody tr:hover {
            background: #f5f7fa;
        }
        
        .items-table td {
            padding: 12px;
            color: #2c3e50;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .total-row {
            background: #ecf0f1 !important;
            font-weight: bold;
            font-size: 14px;
        }
        
        .total-row td {
            padding: 15px 12px;
            color: #2c3e50;
        }
        
        .signature-section {
            margin-top: 50px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        
        .signature-box {
            text-align: center;
            padding: 20px 10px;
        }
        
        .signature-line {
            border-top: 2px solid #2c3e50;
            margin-top: 60px;
            padding-top: 10px;
            font-weight: bold;
            color: #555;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            text-align: center;
            color: #95a5a6;
            font-size: 11px;
        }
        
        .action-buttons {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .btn-print, .btn-pdf, .btn-close {
            display: inline-block;
            padding: 12px 30px;
            margin: 0 10px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-pdf {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .btn-pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 87, 108, 0.4);
        }
        
        .btn-close {
            background: #95a5a6;
            color: white;
        }
        
        .btn-close:hover {
            background: #7f8c8d;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
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
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 5px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .action-buttons {
                display: none !important;
            }
            
            .delivery-invoice {
                box-shadow: none;
                padding: 15px;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        @media screen and (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .signature-section {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .items-table {
                font-size: 11px;
            }
            
            .items-table th,
            .items-table td {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="delivery-invoice">
        <!-- Action Buttons -->
        <div class="action-buttons no-print">
            <button class="btn-print" onclick="window.print();">
                <i class="fa fa-print"></i> Print Invoice
            </button>
            <a href="<?= admin_url('delivery/download_pdf/' . (isset($delivery->id) ? $delivery->id : '')) ?>" class="btn-pdf" target="_blank">
                <i class="fa fa-file-pdf-o"></i> Download PDF
            </a>
            <button class="btn-close" onclick="window.close();">
                <i class="fa fa-times"></i> Close
            </button>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-logo">Avenzur Pharmaceuticals</div>
            <div class="invoice-title">DELIVERY INVOICE</div>
            <div class="invoice-number">Delivery ID: <strong><?= isset($delivery->id) ? str_pad($delivery->id, 6, '0', STR_PAD_LEFT) : 'N/A' ?></strong></div>
            <div class="invoice-number">
                Date: <strong><?= isset($delivery->date_string) ? date('d M Y, h:i A', strtotime($delivery->date_string)) : date('d M Y, h:i A') ?></strong>
            </div>
        </div>

        <!-- Delivery Information -->
        <!-- Delivery Information -->
        <div class="info-section">
            <div class="section-title">
                <i class="fa fa-truck"></i> Delivery Information
            </div>
            <div class="info-grid">
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
                <div class="info-item">
                    <span class="info-label">Delivery Date:</span>
                    <span class="info-value"><?= isset($delivery->date_string) ? date('d M Y, h:i A', strtotime($delivery->date_string)) : 'N/A' ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Invoices:</span>
                    <span class="info-value"><strong><?= isset($delivery->invoice_count) ? $delivery->invoice_count : count($items) ?></strong></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Items:</span>
                    <span class="info-value"><strong><?= isset($delivery->total_items) ? $delivery->total_items : 0 ?></strong></span>
                </div>
            </div>
        </div>

        <!-- Driver & Vehicle Information -->
        <div class="info-section">
            <div class="section-title">
                <i class="fa fa-user"></i> Driver & Vehicle Details
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Driver Name:</span>
                    <span class="info-value"><strong><?= isset($delivery->driver_name) ? $delivery->driver_name : 'N/A' ?></strong></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Vehicle Number:</span>
                    <span class="info-value"><strong><?= isset($delivery->truck_no) ? $delivery->truck_no : (isset($delivery->truck_number) ? $delivery->truck_number : 'N/A') ?></strong></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Odometer Reading:</span>
                    <span class="info-value"><?= isset($delivery->odometer) && $delivery->odometer ? number_format($delivery->odometer) . ' km' : 'Not Recorded' ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Assigned By:</span>
                    <span class="info-value"><?= isset($delivery->assigned_by_name) ? $delivery->assigned_by_name : 'N/A' ?></span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="info-section">
            <div class="section-title">
                <i class="fa fa-list"></i> Delivery Items
            </div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Invoice No.</th>
                        <th style="width: 25%;">Customer</th>
                        <th style="width: 12%;">Invoice Date</th>
                        <th class="text-right" style="width: 13%;">Amount</th>
                        <th class="text-center" style="width: 10%;">Items</th>
                        <th class="text-center" style="width: 10%;">Refrigerated</th>
                        <th class="text-center" style="width: 10%;">Notes</th>
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
                            <td class="text-center">-</td>
                        </tr>
                    <?php 
                        endforeach; 
                    ?>
                        <tr class="total-row">
                            <td colspan="4" style="text-align: right; padding-right: 15px;">
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
                            <td></td>
                        </tr>
                    <?php 
                    } else {
                    ?>
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 30px; color: #95a5a6;">
                                <i class="fa fa-inbox fa-3x" style="display: block; margin-bottom: 10px;"></i>
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
            <p><strong>Avenzur Pharmaceuticals</strong> | Pharmaceutical Distribution & Logistics</p>
            <p>This is a computer-generated delivery invoice. Printed on <?= date('d M Y, h:i A') ?></p>
            <p>For any queries, please contact our logistics department.</p>
        </div>
    </div>

    <!-- Include FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</body>
</html>
