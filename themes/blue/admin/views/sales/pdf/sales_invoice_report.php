<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line('sale') . ' ' . $inv->reference_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
    <style>
        tbody {
            border: 1px solid black;
        }

        td {
            font-size: 11px;
            border: 1px solid;
            padding: 3px !important;
            padding-top: 5px !important;
            padding-bottom: 5px !important;
        }

        th {
            font-size: 12px;
            border: 1px solid;
            padding: 3px !important;

        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .content {
            margin: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
        }

        .container {
            width: 100%;
        }

        .row {
            display: flex;
            width: 100%;
            font-size: 12px;
        }

        .col-half {
            width: 40%;
            padding: 2px;
            float: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold;
        }

        /* .well { border: 1px solid #ddd; padding: 10px; border-radius: 5px; background-color: #f7f7f7; } */
        .well {
            border: 1px solid #ddd;
            background-color: #f6f6f6;
            box-shadow: none;
            border-radius: 0px;
            font-size: 12px;
            height: auto;
        }

        .clearfix {
            clear: both;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .table thead {
            background-color: #007bff;
            color: #fff;
        }

        @media print {
        thead {
            display: table-header-group;
        }
        tfoot {
            display: table-footer-group; /* Optional: makes footers repeat too */
        }
        }
    </style>
</head>

<body>


    <div class="container">

        <div class="header">
            <img src="data:image/png;base64,<?php echo base64_encode(file_get_contents(base_url() . 'assets/uploads/logos/avenzur-logov2-024.png')); ?>"
                alt="Avenzur" style="max-width: 200px;">
        </div>

        <div class="well" style="width: 96%; height: 8%; background-color: #f6f6f6; padding: 15px;">
            <div style="width: 50%; float: left;">
                <p>Date: <?= $this->sma->hrld($inv->date); ?></p>
                <p>Reference: <?= date("Y").'/'.$inv->id; ?></p>
                <p>Sale Status: <?= $inv->sale_status; ?></p>
                <p>Payment Status: <?= $inv->payment_status; ?></p>
            </div>
            <div style="width: 50%; float:left; text-align: right; padding-top: 5px;">
                <img src="<?= admin_url('misc/barcode/' . $this->sma->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>"
                    alt="<?= $inv->reference_no; ?>" class="bcimg" />
                <?php
                if ($Settings->ksa_qrcode) {
                    $qrtext = $this->inv_qrcode->base64([
                        'seller' => $biller->company && $biller->company != '-' ? $biller->company : $biller->name,
                        'vat_no' => $biller->vat_no ?: $biller->get_no,
                        'date' => $inv->date,
                        'grand_total' => $return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total,
                        'total_tax_amount' => $return_sale ? ($inv->total_tax + $return_sale->total_tax) : $inv->total_tax,
                    ]);
                    echo $this->sma->qrcode('text', $qrtext, 2);
                } else {
                    echo $this->sma->qrcode('link', urlencode(site_url('view/sale/' . $inv->hash)), 2);
                }
                ?>
            </div>
        </div>
        <div style="clear: both"></div>
        <!--<div class="row text-center">
            <h3>TAX INVOICE</h3>
        </div>-->

        <div class="row">
            <div class="col-half" style="margin-top:30px;">
                <p class="bold">To:</p>
                <p><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></p>
                <?= $customer->company && $customer->company != '-' ? '' : 'Attn: ' . $customer->name ?>

                <?php
                echo $customer->address . '<br>' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . '<br>' . $customer->country;

                echo '<p>';
                /*if ($customer->sequence_code != '-' && $customer->sequence_code != '') {
                    echo '<br>' . lang('sequence_code') . ': ' . $customer->sequence_code;
                }*/
                if ($customer->vat_no != '-' && $customer->vat_no != '') {
                    echo '<br>' . lang('vat_no') . ': ' . $customer->vat_no;
                }
                if ($customer->gst_no != '-' && $customer->gst_no != '') {
                    echo '<br>' . lang('gst_no') . ': ' . $customer->gst_no;
                }
                if ($customer->cf1 != '-' && $customer->cf1 != '') {
                    echo '<br>' . lang('ccf1') . ': ' . $customer->cf1;
                }
                if ($customer->cf2 != '-' && $customer->cf2 != '') {
                    echo '<br>' . lang('ccf2') . ': ' . $customer->cf2;
                }
                if ($customer->cf3 != '-' && $customer->cf3 != '') {
                    echo '<br>' . lang('ccf3') . ': ' . $customer->cf3;
                }
                if ($customer->cf4 != '-' && $customer->cf4 != '') {
                    echo '<br>' . lang('ccf4') . ': ' . $customer->cf4;
                }
                if ($customer->cf5 != '-' && $customer->cf5 != '') {
                    echo '<br>' . lang('ccf5') . ': ' . $customer->cf5;
                }
                if ($customer->cf6 != '-' && $customer->cf6 != '') {
                    echo '<br>' . lang('ccf6') . ': ' . $customer->cf6;
                }

                echo '</p>';
                echo lang('tel') . ': ' . $customer->phone . '<br>' . lang('email') . ': ' . $customer->email;
                ?>

            </div>
            <div class="col-half" style="margin-top:30px;">
                <p class="bold">From:</p>
                <p><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></p>
                <?= $biller->company ? '' : 'Attn: ' . $biller->name ?>

                <?php
                echo $biller->address . '<br>' . $biller->city . ' ' . $biller->postal_code . ' ' . $biller->state . '<br>' . $biller->country;

                echo '<p>';

                if ($biller->vat_no != '-' && $biller->vat_no != '') {
                    echo '<br>' . lang('vat_no') . ': ' . $biller->vat_no;
                }
                if ($biller->gst_no != '-' && $biller->gst_no != '') {
                    echo '<br>' . lang('gst_no') . ': ' . $biller->gst_no;
                }
                if ($biller->cf1 != '-' && $biller->cf1 != '') {
                    echo '<br>' . lang('bcf1') . ': ' . $biller->cf1;
                }
                if ($biller->cf2 != '-' && $biller->cf2 != '') {
                    echo '<br>' . lang('bcf2') . ': ' . $biller->cf2;
                }
                if ($biller->cf3 != '-' && $biller->cf3 != '') {
                    echo '<br>' . lang('bcf3') . ': ' . $biller->cf3;
                }
                if ($biller->cf4 != '-' && $biller->cf4 != '') {
                    echo '<br>' . lang('bcf4') . ': ' . $biller->cf4;
                }
                if ($biller->cf5 != '-' && $biller->cf5 != '') {
                    echo '<br>' . lang('bcf5') . ': ' . $biller->cf5;
                }
                if ($biller->cf6 != '-' && $biller->cf6 != '') {
                    echo '<br>' . lang('bcf6') . ': ' . $biller->cf6;
                }

                echo '</p>';
                echo lang('tel') . ': ' . $biller->phone . '<br>' . lang('email') . ': ' . $biller->email;
                ?>
            </div>
        </div>
        <div style="clear: both"></div>

        <div class="table-responsive" style="margin-top:30px;">
            <table class="table table-bordered table-hover table-striped">
                <thead>
                    <?php $col = 6; ?>
                    <tr>
                        <th>#</th>
                        <th><?= lang('Description'); ?></th>
                        <th><?= lang('Batch'); ?></th>
                        <th><?= lang('Expiry'); ?></th>
                        <th><?= lang('Qty'); ?></th>
                        <th><?= lang('Bonus'); ?></th>
                        <th><?= lang('S.Price'); ?></th>
                        <th><?= lang('S.Total'); ?></th>
                        <?php if ($Settings->product_discount && $inv->product_discount != 0) {
                            $col +=2; 
                            echo '<th>' . lang('Disc1 %') . '</th>';
                            echo '<th>' . lang('Disc1 Val') . '</th>';
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            $col +=2; 
                            echo '<th>' . lang('Disc2 %') . '</th>';
                            echo '<th>' . lang('Disc2 Val') . '</th>';
                        }
                        echo '<th>' . lang('Total_without_VAT') . '</th>';
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            $col +=2; 
                            echo '<th>' . lang('VAT%') . '</th>';
                            echo '<th>' . lang('VAT') . '</th>';
                        }
                        echo '<th>' . lang('Total With VAT') . '</th>';
                        ?>
                    </tr>

                </thead>
                <tbody>
                    <?php $r = 1;
                    $tax_summary = [];
                    $subTotal = 0;
                    $totalAmount = 0;
                    foreach ($rows as $row):
                        $subTotal = ($row->real_unit_price * $row->unit_quantity);
                        ?>
                        <tr>
                            <td style="text-align:center;vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                <?= $row->details ? '<br>' . $row->details : ''; ?>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= $row->batch_no; ?>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= $row->expiry; ?>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= $this->sma->formatQuantity($row->unit_quantity); ?>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= $this->sma->formatQuantity($row->bonus); ?>
                            </td>
                            <td style="text-align:right;">
                                <?= $this->sma->formatNumber($row->real_unit_price); ?>
                            </td>
                            <td style="text-align:right;"><?= $this->sma->formatNumber($row->subtotal); ?></td>
                            <?php
                             if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style=" text-align:right; vertical-align:middle;">' . ($row->discount1 != 0 ?  $this->sma->formatNumber($row->discount1)  : '') .  '</td>'; 
                              
                                $unit_cost=$row->real_unit_price;
                                $pr_discount      = $this->site->calculateDiscount($row->discount1.'%', $row->real_unit_price);
                                $amount_after_dis1 = $unit_cost - $pr_discount;
                                $pr_discount2      = $this->site->calculateDiscount($row->discount2.'%', $amount_after_dis1);  
                                $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $row->unit_quantity);
                                $row->discount2= $this->sma->formatNumber($row->discount2,null);
                                echo '<td style=" text-align:right; vertical-align:middle;">' . $this->sma->formatNumber($row->item_discount) . '</td>';
                       
                                echo '<td style="text-align:right; vertical-align:middle;">' . ($row->discount2 != 0 ?  $row->discount2  : '') . '</td>';
                                echo '<td style="text-align:right; vertical-align:middle;">' . $this->sma->formatNumber($row->second_discount_value) . '</td>';
                            }
                            ?>
                            <td style="text-align:right;"><?= $this->sma->formatNumber($row->totalbeforevat, null); ?></td>
                            <?php
                            $vat_value = 0;
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                $vat_value = $this->sma->formatNumber($row->item_tax);
                                echo '<td style="text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ?  ($Settings->indian_gst ? $row->tax : $row->tax_code)  : '') . '</td>';
                                echo '<td>'.$this->sma->formatNumber($row->tax).'</td>';
                            }
                            ?>
                            <td style="text-align:right;"><?= $this->sma->formatNumber($row->main_net); ?></td> 
                        </tr>
                        <?php
                        $totalAmount += $subTotal;
                        $totalDiscount += $row->item_discount;
                        $netBeforeVAT += $row->subtotal;
                        $totalVAT  += $vat_value;
                        $r++;
                    endforeach;
                    /*if ($return_rows) {
                        echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                        foreach ($return_rows as $row):
                            ?>
                            <tr class="warning">
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="vertical-align:middle;">
                                    <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                                </td>
                                <?php if ($Settings->indian_gst) {
                                    ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?>
                                    </td>
                                    <?php
                                } ?>
                                <td style="width: 80px; text-align:center; vertical-align:middle;">
                                    <?= $this->sma->formatQuantity($row->quantity) . ' ' . $row->base_unit_code; ?>
                                </td>
                                <td style="width: 80px; text-align:center; vertical-align:middle;">
                                    <?= $this->sma->formatQuantity($row->bonus) . ' ' . $row->base_unit_code; ?>
                                </td>
                                <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->unit_price); ?>
                                </td>
                                <?php
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                                }
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                } ?>
                                <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        endforeach;
                    }*/
                    ?>
                </tbody>
                
            </table>
        </div>

        <div class="table-responsive table-summary" style="width:30%; float: right">
            <table class="table table-bordered table-hover table-striped print-table order-table" >
                
                        <tr>
                            <td>Total</td>
                            <td><?php echo $this->sma->formatNumber($inv->total);?></td>
                        </tr>
                        <tr>
                            <td>T-DISC</td>
                            <td><?php echo $this->sma->formatNumber($inv->total_discount);?></td>
                        </tr>
                        <tr>
                            <td>Net Before VAT</td>
                            <td><?php echo $this->sma->formatNumber($inv->total_net_sale);?></td>
                        </tr>
                        <tr>
                            <td>Total VAT</td>
                            <td><?php echo $this->sma->formatNumber($inv->total_tax); ?></td>
                        </tr>
                        <tr>
                            <td>Total After VAT</td>
                            <td><?php echo $this->sma->formatNumber($inv->grand_total);?></td>
                        </tr>
        </table>
        </div>

        <?php //echo $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax)) : ''; ?>
        
        <!--<div class="well" style="width: 30%; float: right; padding: 10px;">
            <p>
                <?= lang('created_by'); ?>:
                <?= $inv->created_by ? $created_by->first_name . ' ' . $created_by->last_name : $customer->name; ?> <br>
                <?= lang('date'); ?>: <?= $this->sma->hrld($inv->date); ?>
            </p>
            <?php if ($inv->updated_by) {
                ?>
                <p>
                    <?= lang('updated_by'); ?>: <?= $updated_by->first_name . ' ' . $updated_by->last_name; ?><br>
                    <?= lang('update_at'); ?>: <?= $this->sma->hrld($inv->updated_at); ?>
                </p>
                <?php
            } ?>
        </div>-->

        

    </div>

    <!--<div style="margin-top: 60px; width: 100%; font-size: 12px;float:left;">
        <table style="width: 100%; border: none !important; ">
            <tr>
                <td style="width: 25%; text-align: left; border: none;">
                    <strong>STORE KEEPER:</strong>
                    <div style="height: 40px; border-bottom: 1px solid #000;"></div>
                </td>
                <td style="width: 25%; text-align: left; border: none;">
                    <strong>SALES MANAGER:</strong>
                    <div style="height: 40px; border-bottom: 1px solid #000;"></div>
                </td>
                <td style="width: 25%; text-align: left; border: none;">
                    <strong>RECEIVED BY:</strong>
                    <div style="height: 40px; border-bottom: 1px solid #000;"></div>
                </td>
                <td style="width: 25%; text-align: left; border: none;">
                    <strong>SIGNATURE / STAMP:</strong>
                    <div style="height: 40px; border-bottom: 1px solid #000;"></div>
                </td>
            </tr>
        </table>
    </div>-->

</body>

</html>