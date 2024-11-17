<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>

@media print {
    body {
        zoom: 90%;
        margin: 0;
        padding: 0;
    }

    /* Ensure modal content is printed */
    .modal {
        position: static; /* Ensure it's positioned well for print */
        display: block !important; /* Ensure modal content is visible */
        width: 100%; /* Ensure modal uses full width */
        background-color: #fff; /* Set a white background for clarity */
    }

    /* Hide backdrop, buttons, or other interactive elements */
    .modal-backdrop, .print-hide {
        display: none !important;
    }

    /* Ensure proper page breaks inside tables */
    table, tr, td {
        page-break-inside: avoid;
    }

    /* Optional: Remove shadows, borders, or extra elements that might interfere with printing */
    .modal-shadow, .modal-border {
        box-shadow: none;
        border: none;
    }

    /* Adjust font size for better readability */
    body, table {
        font-size: 12px;
    }

    /* Ensure proper table formatting in print */
    table {
        width: 100%; /* Ensure tables use full width */
        border-collapse: collapse;
    }
    
    th, td {
        border: 1px solid #000; /* Ensure tables are clearly printed */
    }
    .table-summary {
        float: right !important;
        width: 30% !important; /* Ensure it maintains 30% width */
    }
    .clear{
        clear: both;
    }
}

</style>
<div class="modal-dialog modal-lg no-modal-header" style="width: 90%; font-size: 12px;">
    <div class="modal-content" >
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button> 
           
                <div class="text-center" style="margin-bottom:20px;">
                <h1><?= lang('Sale_Invoice'); ?></h1>
                </div>

            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-5">
                    <p class="bold">
                        <?= lang('date'); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                        <?= lang('ref'); ?>: <?= $inv->reference_no; ?><br>
                        <?php if (!empty($inv->return_sale_ref)) {
                            echo lang('return_ref') . ': ' . $inv->return_sale_ref;
                            if ($inv->return_id) {
                                echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                            } else {
                                echo '<br>';
                            }
                        } ?>
                        <?= lang('sale_status'); ?>: <?= lang($inv->sale_status); ?><br>
                        <?= lang('payment_status'); ?>: <?= lang($inv->payment_status); ?><br>
                        <?= $inv->payment_method ? lang('payment_method') . ': ' . lang($inv->payment_method) : ''; ?>
                        <?php
                        if ($inv->payment_status != 'paid' && $inv->due_date) {
                            echo '<br>' . lang('due_date') . ': ' . $this->sma->hrsd($inv->due_date);
                        } ?>
                    </p>
                    </div>
                    <div class="col-xs-7 text-right order_barcodes">
                        <img src="<?= admin_url('misc/barcode/' . $this->sma->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>" alt="<?= $inv->reference_no; ?>" class="bcimg" />
                        <?php
                        if ($Settings->ksa_qrcode) {
                            $qrtext = $this->inv_qrcode->base64([
                                'seller'           => $biller->company && $biller->company != '-' ? $biller->company : $biller->name,
                                'vat_no'           => $biller->vat_no ?: $biller->get_no,
                                'date'             => $inv->date,
                                'grand_total'      => $return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total,
                                'total_tax_amount' => $return_sale ? ($inv->total_tax + $return_sale->total_tax) : $inv->total_tax,
                            ]);
                            echo $this->sma->qrcode('text', $qrtext, 2);
                        } else {
                            echo $this->sma->qrcode('link', urlencode(site_url('view/sale/' . $inv->hash)), 2);
                        }
                        ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="row" style="margin-bottom:15px;">

              

                <div class="col-xs-6">
                    <?php echo $this->lang->line('to'); ?>:<br/>
                    <h2 style="margin-top:10px;"><?= $customer->company && $customer->company != '-' ? $customer->company : $customer->name; ?></h2>
                    <?= $customer->company                              && $customer->company != '-' ? '' : 'Attn: ' . $customer->name ?>

                    <?php
                    echo $customer->address . '<br>' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . '<br>' . $customer->country;

                    echo '<p>';

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

                <div class="col-xs-6">
                    <?php echo $this->lang->line('from'); ?>:
                    <h2 style="margin-top:10px;"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
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
            <?php
                    $col = $Settings->indian_gst ? 5 : 4;
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
            $total_col=5;
            ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">

                    <thead>

                    <!-- <tr>
                        <th><?= lang('no.'); ?></th>
                        <th><?= lang('description'); ?></th>
                        <?php if ($Settings->indian_gst) {
                            ?>
                            <th><?= lang('hsn_sac_code'); ?></th>
                            <?php
                        } ?>
                        <th><?= lang('quantity'); ?></th>
                        <th><?= lang('unit_price'); ?></th>
                        <?php
                        if ($Settings->tax1 && $inv->product_tax > 0) {
                            echo '<th>' . lang('tax') . '</th>';
                        }
                        if ($Settings->product_discount && $inv->product_discount != 0) {
                            echo '<th>' . lang('discount') . '</th>';
                        }
                        ?>
                        <th><?= lang('subtotal'); ?></th>
                    </tr> -->

                     
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
                        <th><?= lang('Bonus'); ?></th>
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
                   /* $r = 1;
                    foreach ($rows as $row) :
                        ?>
                        <tr>
                            <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                            <td style="vertical-align:middle;">
                                <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                <?= $row->details ? '<br>' . $row->details : ''; ?>
                                <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                            </td>
                            <?php if ($Settings->indian_gst) {
                                ?>
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?></td>
                                <?php
                            } ?>
                            <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity); ?></td>
                            <td style="text-align:right; width:100px;">
                                <?= $row->unit_price != $row->real_unit_price && $row->item_discount > 0 ? '<del>' . $this->sma->formatMoney($row->real_unit_price) . '</del>' : ''; ?>
                                <?= $this->sma->formatMoney($row->unit_price); ?>
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                            }
                            ?>
                            <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                        </tr>
                        <?php
                        $r++;
                    endforeach; */
                   $r     = 1;
                    $tax_summary = [];
                    $totalAmount = 0;
                    foreach ($rows as $row):
                       $subTotal = ($row->real_unit_price * $row->unit_quantity);
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
                            <td style="width: 8%;text-align:center; vertical-align:middle;"><?= $row->batch_no ?: ''; ?></td>
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
                                <?= $this->sma->formatNumber($row->bonus); ?>
                            </td>
                            <td style="text-align:right; width:100px;">
                                 <!-- <?= $row->unit_cost != $row->real_unit_cost && $row->item_discount > 0 ? '<del>' . $this->sma->formatMoney($row->real_unit_price) . '</del>' : ''; ?> -->
                                <?= $this->sma->formatNumber($row->real_unit_price); ?>
                            </td>
                           
                            

                            <td style="text-align:right; width:120px;"><?= $this->sma->formatNumber($subTotal); ?></td>
                            <?php
                             if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style=" text-align:right; vertical-align:middle;">' . ($row->discount1 != 0 ?  $row->discount1  : '') .  '</td>'; 
                              
                                $unit_cost=$row->real_unit_price;
                                $pr_discount      = $this->site->calculateDiscount($row->discount1.'%', $row->real_unit_price);
                                $amount_after_dis1 = $unit_cost - $pr_discount;
                                $pr_discount2      = $this->site->calculateDiscount($row->discount2.'%', $amount_after_dis1);  
                                $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $row->unit_quantity);
                                $row->discount2= $this->sma->formatNumber($row->discount2,null);
                                echo '<td style=" text-align:right; vertical-align:middle;">' . $this->sma->formatNumber($row->item_discount - $pr_item_discount2) . '</td>';
                       
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
                            
                            <td style="text-align:right; width:120px;"><?= $this->sma->formatNumber($row->subtotal + $vat_value); ?></td>
                            
                        </tr>
                        <?php
                                    //t-disc, net_before_vat, total_vat,total_after_vat
                                $totalAmount += $subTotal;
                                $totalDiscount += $row->item_discount;
                                $netBeforeVAT += $row->subtotal;
                                $totalVAT  += $vat_value;
    
                        $r++;
                    endforeach;
                    /*if ($return_rows) {
                        echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                        foreach ($return_rows as $row) :
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
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?></td>
                                    <?php
                                } ?>
                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->quantity) . ' ' . $row->base_unit_code; ?></td>
                                <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->unit_price); ?></td>
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
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <?php if ($inv->grand_total != $inv->total) {
                        ?>
                        <tr>
                            <td colspan="<?= $tcol; ?>"
                                style="text-align:right; padding-right:10px;"><?= lang('total'); ?>
                                (<?= $default_currency->code; ?>)
                            </td>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_tax + $return_sale->product_tax) : $inv->product_tax) . '</td>';
                            }
                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_discount + $return_sale->product_discount) : $inv->product_discount) . '</td>';
                            } ?>
                            <td style="text-align:right; padding-right:10px;"><?= $this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                        </tr>
                        <?php
                    } */?>
                    <?php
                    // if ($return_sale) {
                    //     echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_sale->grand_total) . '</td></tr>';
                    // }
                    // if ($inv->surcharge != 0) {
                    //     echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
                    // }
                    ?>
                    
            
                    </tfoot>
                </table>
            </div>

            
<div class="table-responsive table-summary" style="width:30%; float: right">
            <table class="table table-bordered table-hover table-striped print-table order-table" >
                
                            <tr>
                                <td>Total</td>
                                <td><?php echo $this->sma->formatNumber($totalAmount);?></td>
                            </tr>
                            <tr>
                                <td>T-DISC</td>
                                <td><?php echo $this->sma->formatNumber($totalDiscount);?></td>
                            </tr>
                            <tr>
                                <td>Net Before VAT</td>
                                <td><?php echo $this->sma->formatNumber($netBeforeVAT);?></td>
                            </tr>
                            <tr>
                                <td>Total VAT</td>
                                <td><?php echo $this->sma->formatNumber($totalVAT); ?></td>
                            </tr>
                            <tr>
                                <td>Total After VAT</td>
                                <td><?php echo $this->sma->formatNumber($netBeforeVAT + $totalVAT);?></td>
                            </tr>
            </table>
            </div>

            <!-- <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_sale ? $inv->product_tax + $return_sale->product_tax : $inv->product_tax)) : ''; ?> -->

            
            <?php include(dirname(__FILE__) . '/../partials/attachments.php'); ?>
            <!-- <?php include FCPATH . 'themes' . DIRECTORY_SEPARATOR . $Settings->theme . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'attachments.php'; ?> -->
            <?php if (!$Supplier || !$Customer) {
                ?>
                <div class="buttons">
                    <div class="btn-group btn-group-justified">
                        <div class="btn-group">
                            <a href="<?= admin_url('sales/add_payment/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('add_payment') ?>" data-toggle="modal" data-target="#myModal2">
                                <i class="fa fa-dollar"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('payment') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('sales/add_delivery/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('add_delivery') ?>" data-toggle="modal" data-target="#myModal2">
                                <i class="fa fa-truck"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('delivery') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('sales/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                <i class="fa fa-envelope-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                            </a>
                        </div>
                        <!-- <div class="btn-group">
                            <a href="<?= admin_url('sales/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                            </a>
                        </div> -->
                        <?php if (!$inv->sale_id) {
                            ?>
                        <div class="btn-group">
                            <a href="<?= admin_url('sales/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                <i class="fa fa-edit"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line('delete_sale') ?></b>"
                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('sales/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                data-html="true" data-placement="top">
                                <i class="fa fa-trash-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                            </a>
                        </div>
                            <?php
                        } ?>
                    </div>
                </div>
                <?php
            } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready( function() {
        $('.tip').tooltip();
    });
</script>
