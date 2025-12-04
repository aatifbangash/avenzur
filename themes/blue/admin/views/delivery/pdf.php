<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Delivery Note PDF - <?=$delivery->id?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
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
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-around;
        }
        .signature-line {
            width: 25%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 30px;
            font-size: 11px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-name">Avenzur</div>
            <div style="font-size: 14px; font-weight: bold;">DELIVERY NOTE</div>
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
                <span class="info-label">Odometer Reading:</span>
                <span class="info-value"><?=$delivery->odometer ? $delivery->odometer . ' km' : 'N/A'?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value"><?=ucfirst(str_replace('_', ' ', $delivery->status))?></span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Items in Delivery Package</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Invoice No.</th>
                        <th style="width: 25%;">Customer</th>
                        <th style="width: 15%;">Invoice Date</th>
                        <th style="width: 15%;">Amount</th>
                        <th style="width: 10%;">Items</th>
                        <th style="width: 10%;">Refrigerated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_amount = 0;
                    $total_items = 0;
                    $total_refrigerated = 0;
                    if (!empty($items)):
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
                    <?php 
                        endforeach;
                    endif;
                    ?>
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
                <strong>Driver Signature</strong>
            </div>
            <div class="signature-line">
                <strong>Receiver Signature</strong>
            </div>
            <div class="signature-line">
                <strong>Date & Time</strong>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated delivery note. Printed on <?=date('d-m-Y H:i:s')?> by <?=$this->session->userdata('first_name')?> <?=$this->session->userdata('last_name')?></p>
        </div>
    </div>
</body>
</html>
