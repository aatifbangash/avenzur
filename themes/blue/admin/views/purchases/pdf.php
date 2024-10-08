<?php defined('BASEPATH') or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->lang->line('purchase') . ' ' . $inv->reference_no; ?></title>
    <link href="<?= $assets ?>styles/pdf/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
</head>

<body>
<div id="wrap"  style="width: 90%; font-size: 12px;">
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
                            <?php
                            if (!empty($inv->return_purchase_ref)) {
                                echo lang('return_ref') . ': ' . $inv->return_purchase_ref;
                                if ($inv->return_id) {
                                    echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('purchases/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                                } else {
                                    echo '<br>';
                                }
                            } ?>
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
                    <?= $this->lang->line('to'); ?>
                    <h2 class=""><?=$supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name;?></h2>
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
                        if ($supplier->gst_no != '-' && $supplier->gst_no != '') {
                            echo '<br>' . lang('gst_no') . ': ' . $supplier->gst_no;
                        }
                        if ($supplier->cf1 != '-' && $supplier->cf1 != '') {
                            echo '<br>' . lang('scf1') . ': ' . $supplier->cf1;
                        }
                        if ($supplier->cf2 != '-' && $supplier->cf2 != '') {
                            echo '<br>' . lang('scf2') . ': ' . $supplier->cf2;
                        }
                        if ($supplier->cf3 != '-' && $supplier->cf3 != '') {
                            echo '<br>' . lang('scf3') . ': ' . $supplier->cf3;
                        }
                        if ($supplier->cf4 != '-' && $supplier->cf4 != '') {
                            echo '<br>' . lang('scf4') . ': ' . $supplier->cf4;
                        }
                        if ($supplier->cf5 != '-' && $supplier->cf5 != '') {
                            echo '<br>' . lang('scf5') . ': ' . $supplier->cf5;
                        }
                        if ($supplier->cf6 != '-' && $supplier->cf6 != '') {
                            echo '<br>' . lang('scf6') . ': ' . $supplier->cf6;
                        }
                        echo '</p>';
                        echo lang('tel') . ': ' . $supplier->phone . '<br />' . lang('email') . ': ' . $supplier->email;
                    ?>
                    <div class="clearfix"></div>
                </div>

                <div class="col-xs-5">
                    <?= $this->lang->line('from'); ?>
                    <h2 class=""><?=$Settings->site_name;?></h2>
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
                    <thead>
                    <tr>
                        <th><?= lang('no.'); ?></th>
                        <th><?= lang('description'); ?></th>
                        <th><?= lang('Batch No');?></th>
                        <?php if ($Settings->indian_gst) {
                            $total_col +=1; 
                        ?>
                            <th><?= lang('hsn_sac_code'); ?></th>
                        <?php
                    } ?>
                        <th><?= lang('quantity'); ?></th>
                        <?php
                            if ($inv->status == 'partial') {
                                $total_col +=1; 
                                echo '<th>' . lang('received') . '</th>';
                            }
                        ?>
                        <th><?= lang('Sale_price'); ?></th>
                     
                        <th><?= lang('subtotal'); ?></th>
                        <?php if ($Settings->product_discount && $inv->product_discount != 0) {
                            $total_col +=2; 
                            echo '<th>' . lang('Disc1 %') . '</th>';
                            echo '<th>' . lang('Disc1 Value') . '</th>';
                        }

                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            $total_col +=2; 
                            echo '<th>' . lang('Disc2 %') . '</th>';
                            echo '<th>' . lang('Disc2 Value') . '</th>';
                        } ?>
                         <th><?= lang('Total_without_VAT'); ?></th>
                         <?php
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            $total_col +=2; 
                            echo '<th>' . lang('VAT%') . '</th>';
                            echo '<th>' . lang('VAT_value') . '</th>';
                        }
                        echo '<th>' . lang('Total_with_VAT') . '</th>';
                        ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                        $r = 1;
                        foreach ($rows as $row):
                            $subTotal = $this->sma->formatNumber($row->sale_price * $row->unit_quantity);
                            ?>
                                <tr>
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                    <td style="width: 30%; vertical-align:middle;">
                                        <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('EX') . ': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                                    </td>
                                    <td style="width: 8%;text-align:center; vertical-align:middle;"><?= $row->batchno ?: ''; ?></td>
                                    <?php if ($Settings->indian_gst) {
                                ?>
                                    <td style=" text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?></td>
                                    <?php
                                    } ?>
                                    <td style="text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity); ?></td>
                                    <?php
                                    if ($inv->status == 'partial') {
                                        echo '<td style="text-align:center;vertical-align:middle;width:80px;">' . $this->sma->formatQuantity($row->quantity_received). '</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; width:100px;">
                                        <!-- <?= $row->unit_cost != $row->real_unit_cost && $row->item_discount > 0 ? '<del>' . $this->sma->formatMoney($row->real_unit_cost) . '</del>' : ''; ?> -->
                                        <?= $this->sma->formatNumber($row->sale_price); ?>
                                    </td>
                                   
                                    <td style="text-align:right; width:120px;"><?=$subTotal; ?></td>
                                    <?php
                                     if ($Settings->product_discount && $inv->product_discount != 0) {
                                        echo '<td style=" text-align:right; vertical-align:middle;">' . ($row->discount != 0 ?  $row->discount  : '') .  '</td>'; 
                                        echo '<td style=" text-align:right; vertical-align:middle;">' . $this->sma->formatNumber($row->item_discount) . '</td>';
                                    }
                                    if ($Settings->product_discount != 0 && $inv->product_discount != 0) { 
                                        $unit_cost=$row->unit_cost;
                                        $pr_discount      = $this->site->calculateDiscount($row->discount1.'%', $row->unit_cost);
                                        $amount_after_dis1 = $unit_cost - $pr_discount;
                                        $pr_discount2      = $this->site->calculateDiscount($row->discount2.'%', $amount_after_dis1);  
                                        $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $row->quantity);
                                        $row->discount2= $this->sma->formatNumber($row->discount2,null);
                                        echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount2 != 0 ?  $row->discount2  : '') . '</td>';
                                        echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . $this->sma->formatNumber($pr_item_discount2) . '</td>';
                                    }
                                    ?>
                                    <td style="text-align:right; width:120px;"><?= $this->sma->formatNumber($row->subtotal, null); ?></td>
                                    <?php
                                    $vat_value = 0;
                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                        $vat_value = $this->sma->formatNumber($row->item_tax);
                                        echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ?  ($Settings->indian_gst ? $row->tax : $row->tax_code)  : '') . '</td>';
                                        echo '<td>'.$vat_value.'</td>';
                                    }
                                    ?>
                                    
                                    <td style="text-align:right; width:120px;"><?= $this->sma->formatNumber($row->subtotal + $total_without_vat); ?></td>
                                    
                                </tr>
                                <?php
                                            //t-disc, net_before_vat, total_vat,total_after_vat
                                        $totalAmount += $subTotal;
                                        $totalDiscount += $row->item_discount + $pr_item_discount2;
                                        $netBeforeVAT += $row->subtotal;
                                        $totalVAT  += $vat_value;
            
                            $r++;
                        endforeach;
                        if ($return_rows) {
                            echo '<tr class="warning"><td colspan="' . ($col + 1) . '" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                            foreach ($return_rows as $row):
                            ?>
                                <tr>
                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?=$r; ?></td>
                                    <td style="vertical-align:middle;">
                                        <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                        <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                        <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                                        <?=$row->details ? '<br>' . $row->details : ''; ?>
                                        <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('expiry') . ': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                                    </td>
                                    <?php if ($Settings->indian_gst) {
                                ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?></td>
                                    <?php
                            } ?>
                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?=$this->sma->formatQuantity($row->quantity) . ' ' . $row->product_unit_code; ?></td>
                                    <?php
                                        if ($inv->status == 'partial') {
                                            echo '<td style="text-align:center;vertical-align:middle;width:120px;">' . $this->sma->formatQuantity($row->quantity_received) . ' ' . $row->product_unit_code . '</td>';
                                        } ?>
                                    <td style="text-align:right; width:100px;"><?=$this->sma->formatMoney($row->unit_cost); ?></td>
                                    <?php
                                        if ($Settings->tax1 && $inv->product_tax > 0) {
                                            echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' : '') . $this->sma->formatMoney($row->item_tax) . '</td>';
                                        }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                            } ?>
                                    <td style="text-align:right; width:120px;"><?=$this->sma->formatMoney($row->subtotal); ?></td>
                                </tr>
                                <?php
                                $r++;
                            endforeach;
                        }
                    ?>
                    </tbody>
                    <tfoot>

                    <?php if ($inv->grand_total != $inv->total) {
                        ?>
                        <tr>
                            <td colspan="<?= $tcol; ?>"
                                style="text-align:right;"><?= lang('total'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->product_tax + $return_purchase->product_tax) : $inv->product_tax) . '</td>';
                            }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->product_discount + $return_purchase->product_discount) : $inv->product_discount) . '</td>';
                        } ?>
                            <td style="text-align:right;"><?= $this->sma->formatMoney($return_purchase ? (($inv->total + $inv->product_tax) + ($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                        </tr>
                    <?php
                    } ?>
                    <?php
                    if ($return_purchase) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($return_purchase->grand_total) . '</td></tr>';
                    }
                    if ($inv->surcharge != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
                    }
                    ?>
                    <?php if ($Settings->indian_gst) {
                        if ($inv->cgst > 0) {
                            $cgst = $return_purchase ? $inv->cgst + $return_purchase->cgst : $inv->cgst;
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('cgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($cgst) : $cgst) . '</td></tr>';
                        }
                        if ($inv->sgst > 0) {
                            $sgst = $return_purchase ? $inv->sgst + $return_purchase->sgst : $inv->sgst;
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('sgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($sgst) : $sgst) . '</td></tr>';
                        }
                        if ($inv->igst > 0) {
                            $igst = $return_purchase ? $inv->igst + $return_purchase->igst : $inv->igst;
                            echo '<tr><td colspan="' . $col . '" class="text-right">' . lang('igst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($igst) : $igst) . '</td></tr>';
                        }
                    } ?>
                    <?php if ($inv->order_discount != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->sma->formatMoney($return_purchase ? ($inv->order_discount + $return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                    }
                    ?>
                    <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($return_purchase ? ($inv->order_tax + $return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                    }
                    ?>
                    <?php if ($inv->shipping != 0) {
                        echo '<tr><td colspan="' . $col . '" style="text-align:right;">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td style="text-align:right;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                    }
                    ?>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang('paid'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid); ?></td>
                    </tr>
                    <tr>
                        <td colspan="<?= $col; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang('balance'); ?>
                            (<?= $default_currency->code; ?>)
                        </td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid)); ?></td>
                    </tr>

                    </tfoot>
                </table>
            </div>
            <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax + $return_purchase->product_tax : $inv->product_tax), true) : ''; ?>
            </div>
            <div class="clearfix"></div>

            <div class="row">
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
            </div>

        </div>
    </div>
</div>
</body>
</html>
