<?php defined('BASEPATH') or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line('purchase') . ' ' . $inv->reference_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/bootstrap.min.css" rel="stylesheet">
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
            font-size: 10px;
            border: 1px solid;
            padding: 3px !important;
            vertical-align: middle;
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
            width: 104% !important;
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
            background: #428bca !important;
            color: #fff !important;
            border-color: #357ebd !important;
            border-top: 1px solid #357ebd !important;
            font-weight: bold !important;
            vertical-align: middle !important;
        }

        .table thead {
            background-color: #007bff;
            color: #fff;
        }
    </style>
</head>

<body>
<div id="wrap"  style="width: 100%; font-size: 12px;">
    <div class="row">
        <div class="col-lg-12">

            <?php
            if ($logo) {
                $path   = base_url() . 'assets/uploads/logos/' . $Settings->logo;
                $type   = pathinfo($path, PATHINFO_EXTENSION);
                $data   = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data); ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= $base64; ?>" alt="<?=$Settings->site_name; ?>">
                </div>
            <?php
            } ?>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-4">
                        <p styel="font-weight:bold;line-height:1.4em;">
                            <?= lang('date'); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                            <?= lang('ref'); ?>: <?= $inv->reference_no; ?><br>
                            
                            <?= lang('status'); ?>: <?= lang($inv->status); ?><br>
                            <?= lang('payment_status'); ?>: <?= lang($inv->payment_status); ?>
                        </p>
                    </div>
                    <div class="col-xs-6 pull-right text-right order_barcodes">
                        <?php
                        $path   = admin_url('misc/barcode/' . $this->sma->base64url_encode($inv->reference_no) . '/code128/74/0/1');
                        $type   = $Settings->barcode_img ? 'png' : 'svg+xml';
                        $data   = file_get_contents($path);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        ?>
                        <img src="<?= $base64; ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" />
                        <?php /*echo $this->sma->qrcode('link', urlencode(admin_url('purchases/view/' . $inv->id)), 2);*/ ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="clearfix"></div>
            <div class="row padding10">

                <div class="col-xs-5">
                    
                    <p class="bold">FROM:</p><?=$supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name;?></p>
                    <?=$supplier->company              && $supplier->company != '-' ? '' : 'Attn: ' . $supplier->name?>
                    <?php
                        echo $supplier->address . '<br />' . $supplier->city . ' ' . $supplier->postal_code . ' ' . $supplier->state . '<br />' . $supplier->country;
                        echo '<p>';
                        if ($supplier->code != '-' && $supplier->code != '') {
                            echo '<br>' . lang('code') . ': ' . $supplier->code;
                        }
                        if ($supplier->vat_no != '-' && $supplier->vat_no != '') {
                            echo '<br>' . lang('vat_no') . ': ' . $supplier->vat_no;
                        }
                        echo lang('tel') . ': ' . $supplier->phone . '<br />' . lang('email') . ': ' . $supplier->email;
                        echo '</p>';
                        
                    ?>
                    <div class="clearfix"></div>
                </div>

                <div class="col-xs-5">
                    
                    <p class="bold">TO:</p><?=$Settings->site_name;?></p>
                    <?=$warehouse->name?>

                    <?php
                        echo $warehouse->address . '<br>';
                        echo($warehouse->phone ? lang('tel') . ': ' . $warehouse->phone . '<br>' : '') . ($warehouse->email ? lang('email') . ': ' . $warehouse->email : '');
                    ?>
                    <div class="clearfix"></div>
                </div>

            </div>

            <div class="clearfix"></div>
            <?php
                $col = $Settings->indian_gst ? 5 : 4;
                if ($inv->status == 'partial') {
                    $col++;
                }
                if ($Settings->product_discount && $inv->product_discount != 0) {
                    $col++;
                }
                if ($Settings->tax1 && $inv->product_tax > 0) {
                    $col++;
                }
                if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                    $tcol = $col - 2;
                } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                    $tcol = $col - 1;
                } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                    $tcol = $col - 1;
                } else {
                    $tcol = $col;
                }
            ?>
            <div style="margin-top: 15px;">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead style="background: blue;">
                        <?php $col = 6; ?>
                        <tr>
                            <th>#</th>
                            <th><?= lang('Description'); ?></th>
                            <th><?= lang('Qty'); ?></th>
                            <th><?= lang('P.Price'); ?></th>
                            <th><?= lang('Bonus'); ?></th>
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
                    <?php
                        $r = 1;
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
                                    <?= $this->sma->formatQuantity($row->unit_quantity); ?>
                                </td>
                                
                                <td style="text-align:right; vertical-align:middle;">
                                    <?= $this->sma->formatNumber($row->unit_cost); ?>
                                </td>
                                <td style="text-align:center; vertical-align:middle;">
                                    <?= $this->sma->formatQuantity($row->bonus); ?>
                                </td>
                                <td style="text-align:right; vertical-align:middle;"><?= $this->sma->formatNumber($row->subtotal); ?></td>
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
                                <td style="text-align:right;vertical-align:middle;"><?= $this->sma->formatNumber($row->totalbeforevat, null); ?></td>
                                <?php
                                $vat_value = 0;
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    $vat_value = $this->sma->formatNumber($row->item_tax);
                                    echo '<td style="text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ?  ($Settings->indian_gst ? $row->tax : $row->tax_code)  : '') . '</td>';
                                    echo '<td style="text-align:right; vertical-align:middle;">'.$this->sma->formatNumber($row->item_tax).'</td>';
                                }
                                ?>
                                <td style="text-align:right; vertical-align:middle;"><?= $this->sma->formatNumber($row->main_net); ?></td> 
                            </tr>
                            <?php
                            $totalAmount += $subTotal;
                            $totalDiscount += $row->item_discount;
                            $netBeforeVAT += $row->subtotal;
                            $totalVAT  += $vat_value;
                            $r++;
                        endforeach;
                        
                    ?>
                    </tbody>
                    
                </table>
            </div>

            <div class="table-responsive table-summary" style="width:40%; float: right">
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
                                <td><?php echo $this->sma->formatNumber($inv->total_net_purchase);?></td>
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

            <?php //echo $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax + $return_purchase->product_tax : $inv->product_tax), true) : ''; ?>
            </div>
            <div class="clearfix"></div>

            <!--<div class="row">
                <div class="col-xs-7 pull-left">
                    <?php if ($inv->note || $inv->note != '') {
                        ?>
                        <div class="well well-sm">
                            <p class="bold"><?=lang('note'); ?>:</p>

                            <div><?=$this->sma->decode_html($inv->note); ?></div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
                <div class="col-xs-4 pull-right">
                    <p><?=lang('order_by');?>: <?=$created_by->first_name . ' ' . $created_by->last_name;?> </p>

                    <p>&nbsp;</p>

                    <p>&nbsp;</p>
                    <hr>
                    <p><?=lang('stamp_sign');?></p>
                </div>
            </div>-->

        </div>
    </div>
</div>
</body>
</html>
