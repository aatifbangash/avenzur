<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Delivery Note - <?=$delivery->id?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px;
            margin-top: 10px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 30%;
        }
        .info-value {
            width: 70%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
        }
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-around;
        }
        .signature-line {
            width: 30%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 30px;
        }
        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Avenzur</div>
        <div>DELIVERY NOTE</div>
        <div>Delivery ID: <?=$delivery->id?></div>
    </div>

    <div class="section">
        <div class="section-title">Delivery Information</div>
        <div class="info-row">
            <span class="info-label">Date:</span>
            <span class="info-value"><?=!empty($delivery->date_string) ? date('d-m-Y H:i', strtotime($delivery->date_string)) : date('d-m-Y')?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Driver Name:</span>
            <span class="info-value"><?=$delivery->driver_name?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Truck Number:</span>
            <span class="info-value"><?=$delivery->truck_number?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Odometer:</span>
            <span class="info-value"><?=$delivery->odometer ? $delivery->odometer . ' km' : 'N/A'?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value"><?=ucfirst(str_replace('_', ' ', $delivery->status))?></span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Items in Delivery</div>
        <table>
            <thead>
                <tr>
                    <th>Invoice No.</th>
                    <th>Customer</th>
                    <th>Invoice Date</th>
                    <th>Amount</th>
                    <th>Items</th>
                    <th>Refrigerated</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_amount = 0;
                $total_items = 0;
                $total_refrigerated = 0;
                foreach ($items as $item): 
                    $total_amount += $item->total_amount;
                    $total_items += $item->quantity_items;
                    $total_refrigerated += $item->refrigerated_items;
                ?>
                    <tr>
                        <td><?=$item->reference_no?></td>
                        <td><?=$item->customer_name?></td>
                        <td><?=date('d-m-Y', strtotime($item->sale_date))?></td>
                        <td><?=currency($item->total_amount)?></td>
                        <td><?=$item->quantity_items?></td>
                        <td><?=$item->refrigerated_items?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3">TOTAL</td>
                    <td><?=currency($total_amount)?></td>
                    <td><?=$total_items?></td>
                    <td><?=$total_refrigerated?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="signature-section">
        <div class="signature-line">
            Driver Signature
        </div>
        <div class="signature-line">
            Receiver Signature
        </div>
        <div class="signature-line">
            Date & Time
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. Printed on <?=date('d-m-Y H:i:s')?></p>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
