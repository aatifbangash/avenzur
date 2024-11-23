<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    @media print {
        /* body {
            zoom: 80%;
            margin: 0;
            padding: 0;
        } */

        /* Ensure modal content is printed */
        .modal {
            position: static;
            /* Ensure it's positioned well for print */
            display: block !important;
            /* Ensure modal content is visible */
            width: 100%;
            /* Ensure modal uses full width */
            background-color: #fff;
            /* Set a white background for clarity */
        }

        /* Hide backdrop, buttons, or other interactive elements */
        /* .modal-backdrop,
        .print-hide {
            display: none !important;
        } */

        /* Ensure proper page breaks inside tables */
        /* table,
        tr,
        td {
            page-break-inside: avoid;
        } */

        /* Optional: Remove shadows, borders, or extra elements that might interfere with printing */
        /* .modal-shadow,
        .modal-border {
            box-shadow: none;
            border: none;
        } */

        /* Adjust font size for better readability */
        body,
        table {
            font-size: 12px;
        }

        /* Ensure proper table formatting in print */

        .table-summary {
            float: right !important;
            width: 30% !important;
        }

        /* .clear {
            clear: both;
        } */

        .header, .footer, .navigation, .no-print {
        display: none;
    }
    }
</style>
<div class="modal-dialog modal-lg no-modal-header" style="width: 90%; font-size: 12px;">
    <div class="modal-content">
        <div class="modal-body" id="printThis">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" id="Print" class="print btn btn-xs btn-default no-print pull-right"
                style="margin-right:15px;">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <?php if ($logo) {
                ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/uploads/logos/' . $Settings->logo; ?>"
                        alt="<?= $Settings->site_name; ?>">
                </div>
                <?php
            } ?>
            <div class="well-sm">
                <div class="row bold">
                    <div class="col-xs-4">
                        <p class="bold">
                        <?= lang('Purchase Invoice No.'); ?>: <?= $purchase_id; ?><br>
                            <?= lang('Transaction Date'); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                            <?= lang('ref'); ?>: <?= $inv->reference_no; ?><br>
                            <?php if (!empty($inv->return_purchase_ref)) {
                                echo lang('return_ref') . ': ' . $inv->return_purchase_ref;
                                if ($inv->return_id) {
                                    echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('purchases/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                                } else {
                                    echo '<br>';
                                }
                            } ?>
                            <?= lang('Store Code'); ?>: <?= $warehouse->code; ?><br>
                            <?= lang('Store Name'); ?>: <?= $warehouse->name; ?><br>
                            <?php
                            // if ($inv->payment_status != 'paid' && $inv->due_date) {
                            //     echo '<br>' . lang('due_date') . ': ' . $this->sma->hrsd($inv->due_date);
                            // } ?>
                        </p>
                    </div>
                    
                    <div class="col-xs-4">
                        <p class="bold">
                        <?= lang('Parent Supplier Code'); ?>: <?= isset($parent_supplier->sequence_code) ? $parent_supplier->sequence_code : $supplier->sequence_code;?><br>
                        <?= lang('Parent Supplier Name'); ?>: <?= isset($parent_supplier->name) ? $parent_supplier->name : $supplier->name;?><br>
                          
                        <?= lang('Child Supplier Code'); ?>: <?= !isset($parent_supplier->sequence_code) ? $supplier->sequence_code : '';?><br>
                        <?= lang('Child Supplier Name'); ?>: <?= !isset($parent_supplier->name) ? $supplier->name : '';?><br>
                           
                        </p>
                    </div>

                    <div class="col-xs-4">
                        <p class="bold">
                            <?= lang('Document No.'); ?>: <?= isset($inv->sequence_code) ? $inv->sequence_code : '';?><br>
                            <?= lang('Invoice No.'); ?>: <?= isset($inv->invoice_number) ? $inv->invoice_number : '';?><br>
                            <?= lang('JL Entry'); ?>: ID : <?= isset($journal_entry->id) ? $journal_entry->id : '';?> Date: <?= isset($journal_entry->date) ? date('Y', strtotime($journal_entry->date)) : '';?><br>
                          
                        </p>
                    </div>

                    <!-- <div class="col-xs-7 text-right order_barcodes">
                        <img src="<?= admin_url('misc/barcode/' . $this->sma->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>"
                            alt="<?= $inv->reference_no; ?>" class="bcimg" />
                        <?= $this->sma->qrcode('link', urlencode(admin_url('purchases/view/' . $inv->id)), 2); ?>
                    </div> -->
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <!-- <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                    <?php echo $this->lang->line('to'); ?>:
                    <h2 style="margin-top:10px;">
                        <?= $supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name; ?>
                    </h2>
                    <?= $supplier->company && $supplier->company != '-' ? '' : 'Attn: ' . $supplier->name ?>

                    <?php
                    echo $supplier->address . '<br />' . $supplier->city . ' ' . $supplier->postal_code . ' ' . $supplier->state . '<br />' . $supplier->country;

                    echo '<p>';

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
                </div>
                <div class="col-xs-6">
                    <?php echo $this->lang->line('from'); ?>:<br />
                    <h2 style="margin-top:10px;"><?= $Settings->site_name; ?></h2>
                    <?= $warehouse->name ?>

                    <?php
                    echo $warehouse->address;
                    echo ($warehouse->phone ? lang('tel') . ': ' . $warehouse->phone . '<br>' : '') . ($warehouse->email ? lang('email') . ': ' . $warehouse->email : '');
                    ?>
                </div>
            </div> -->
            <?php $total_col = 5; ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">

                    <thead>

                        <tr>
                            <th><?= lang('no.'); ?></th>
                            <th><?= lang('description'); ?></th>
                            <th><?= lang('Batch No'); ?></th>
                            <?php if ($Settings->indian_gst) {
                                $total_col += 1;
                                ?>
                                <th><?= lang('hsn_sac_code'); ?></th>
                                <?php
                            } ?>
                            <th><?= lang('Base Quantity'); ?></th>
                            <?php
                            if ($inv->status == 'partial') {
                                $total_col += 1;
                                echo '<th>' . lang('received') . '</th>';
                            }
                            ?>
                            <th><?= lang('Sale_price'); ?></th>
                           <th><?= lang('Purchase_price'); ?></th>
                            <th><?= lang('Cost_price'); ?></th>
                            <th><?= lang('Bonus'); ?></th>

                            <th><?= lang('subtotal'); ?></th>
                            <?php if ($Settings->product_discount && $inv->product_discount != 0) {
                                $total_col += 2;
                                echo '<th>' . lang('Disc1 %') . '</th>';
                                echo '<th>' . lang('Disc1 Value') . '</th>';
                            }

                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                $total_col += 2;
                                echo '<th>' . lang('Disc2 %') . '</th>';
                                echo '<th>' . lang('Disc2 Value') . '</th>';
                            } ?>
                            <th><?= lang('Total_without_VAT'); ?></th>
                            <?php
                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                $total_col += 2;
                                echo '<th>' . lang('VAT%') . '</th>';
                                echo '<th>' . lang('VAT_value') . '</th>';
                            }
                            echo '<th>' . lang('Total_with_VAT') . '</th>';
                            ?>
                        </tr>

                    </thead>

                    <tbody>

                        <?php $r = 1;
                        $tax_summary = [];
                        $totalAmount = 0;
                        foreach ($rows as $row):
                            $subTotal = ($row->unit_cost * $row->unit_quantity);
                            // echo "<pre>";
                             //print_r($row);
                            //$base_quantity = $row->unit_quantity - $row->bonus;
                            ?>
                            <tr>
                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                <td style="width: 30%; vertical-align:middle;">
                                    <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                    <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                    <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                    <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('EX') . ': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                                    <?= 'Item# '.$row->item_code ;?>
                                </td>
                                <td style="width: 8%;text-align:center; vertical-align:middle;"><?= $row->batchno ?: ''; ?>
                                </td>
                                <?php if ($Settings->indian_gst) {
                                    ?>
                                    <td style=" text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?></td>
                                    <?php
                                } ?>
                                <td style="text-align:center; vertical-align:middle;">
                                    <?= $this->sma->formatQuantity($row->unit_quantity); ?></td>
                                <?php
                                if ($inv->status == 'partial') {
                                    echo '<td style="text-align:center;vertical-align:middle;width:80px;">' . $this->sma->formatQuantity($row->quantity_received) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; width:100px;">
                                    <!-- <?= $row->unit_cost != $row->real_unit_cost && $row->item_discount > 0 ? '<del>' . $this->sma->formatMoney($row->real_unit_cost) . '</del>' : ''; ?> -->
                                    <?= $this->sma->formatNumber($row->sale_price); ?>
                                </td>
                                <td style="text-align:right; width:100px;">
                                     <?= $this->sma->formatNumber($row->unit_cost); ?>
                                </td>
                                <td style="text-align:right; width:100px;">
                                   <?= $this->sma->formatNumber($row->net_unit_cost); ?>
                                </td>
                                <td style="text-align:right; width:100px;">
                                   <?= $this->sma->formatNumber($row->bonus); ?>
                                </td>
                                <td style="text-align:right; width:120px;"><?= $this->sma->formatNumber($row->unit_cost * $row->unit_quantity); ?></td>
                                <?php
                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                    echo '<td style=" text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? $row->discount : '') . '</td>';
                                    echo '<td style=" text-align:right; vertical-align:middle;">' . $this->sma->formatNumber($row->item_discount) . '</td>';
                                }
                               
                                if ($Settings->product_discount != 0 && $inv->product_discount != 0) {
                                    $unit_cost = $row->unit_cost;
                                    $pr_discount = $this->site->calculateDiscount($row->discount1 . '%', $row->unit_cost);
                                    $subtotal_discount = $row->unit_cost * $row->unit_quantity;
                                    $amount_after_dis1 =  $subtotal_discount- $row->item_discount; //$unit_cost - $pr_discount;
                                    $pr_discount2 = $this->site->calculateDiscount($row->discount2 . '%', $amount_after_dis1);
                                    $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $row->quantity);
                                    $row->discount2 = $this->sma->formatNumber($row->discount2, null);
                                    echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount2 != 0 ? $row->discount2 : '') . '</td>';
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . $this->sma->formatNumber($pr_discount2) . '</td>';
                                }
                                ?>
                                <td style="text-align:right; width:120px;">
                                    <?= $this->sma->formatNumber($row->subtotal, null); ?></td>
                                <?php
                                $vat_value = 0;
                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                    $vat_value = $this->sma->formatNumber($row->item_tax);
                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? ($Settings->indian_gst ? $row->tax : $row->tax_code) : '') . '</td>';
                                    echo '<td>' . $vat_value . '</td>';
                                }
                                ?>

                                <td style="text-align:right; width:120px;">
                                    <?= $this->sma->formatNumber($row->subtotal)  + $this->sma->formatNumber($vat_value); ?></td>

                            </tr>
                            <?php
                            //t-disc, net_before_vat, total_vat,total_after_vat
                            $totalAmount += $subTotal;
                            $totalDiscount += $row->item_discount + $pr_item_discount2;
                            $netBeforeVAT += $row->subtotal;
                            $totalVAT += $vat_value;

                            $r++;
                        endforeach;
                        /*  if ($return_rows) {
                              echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                              foreach ($return_rows as $row):
                              ?>
                                  <tr class="warning">
                                      <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                      <td style="vertical-align:middle;">
                                          <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                          <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                          <?= $row->supplier_part_no ? '<br>' . lang('supplier_part_no') . ': ' . $row->supplier_part_no : ''; ?>
                                          <?= $row->details ? '<br>' . $row->details : ''; ?>
                                          <?= ($row->expiry && $row->expiry != '0000-00-00') ? '<br>' . lang('expiry') . ': ' . $this->sma->hrsd($row->expiry) : ''; ?>
                                      </td>
                                      <?php if ($Settings->indian_gst) { ?> 
                                          <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $row->hsn_code ?: ''; ?></td>
                                      <?php
                                      } ?>
                                      <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity) ; ?></td>
                                      <?php
                                      if ($inv->status == 'partial') {
                                          echo '<td style="text-align:center;vertical-align:middle;width:80px;">' . $this->sma->formatQuantity($row->quantity_received) . '</td>';
                                      } ?>
                                      <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->unit_cost); ?></td>
                                      <?php
                                      if ($Settings->tax1 && $inv->product_tax > 0) {
                                          echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 ? '<small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                                      }
                                      if ($Settings->product_discount && $inv->product_discount != 0) {
                                          echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                      } 
                                      if ($Settings->product_discount != 0 && $inv->product_discount != 0) { 
                                          $unit_cost=$row->unit_cost;
                                          $pr_discount      = $this->site->calculateDiscount($row->discount1.'%', $row->unit_cost);
                                          $amount_after_dis1 = $unit_cost - $pr_discount;
                                          $pr_discount2      = $this->site->calculateDiscount($row->discount2.'%', $amount_after_dis1);  
                                          $pr_item_discount2 = $this->sma->formatDecimal($pr_discount2 * $row->quantity);
                                          $row->discount2= $this->sma->formatNumber($row->discount2,null);
                                          echo '<td style="width: 120px; text-align:right; vertical-align:middle;">' . ($row->discount2 != 0 ? '<small>(' . $row->discount2 . ')</small>' : '') . ' ' . $this->sma->formatMoney($pr_item_discount2) . '</td>';
                                      } 
                                      ?>  
                                      <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                                  </tr>
                                  <?php
                                  $r++;
                              endforeach;
                          } */
                        ?>
                    </tbody>
                    <tfoot>
                        <?php
                        /* $col = $Settings->indian_gst ? 6 : 5;
                         if ($inv->status == 'partial') {
                             $col++;  
                         }
                         if ($Settings->product_discount && $inv->product_discount != 0) {
                             $col++;
                         }
                         if ($Settings->tax1 && $inv->product_tax > 0) {
                             $col++;
                         }
                         if ($Settings->tax1 && $inv->product_tax > 0 && $inv->product_discount == 0) {
                             $col=$col-1;
                         }
                         if($Settings->tax1 && $inv->product_tax == 0  && $inv->product_discount == 0) {
                             $col = $col-1;
                         }
                         if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                             $tcol = $total_col-4;  // $col - 2; 
                         } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                             $tcol = $total_col-1; // $col - 1; 
                         } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                             $tcol = $total_col-2;// $col - 2; 
                         } elseif ($Settings->tax1 && $inv->product_tax == 0  && $inv->product_discount == 0) {
                             $tcol =  $total_col-2;//$col-1; 
                         }else {
                             $tcol =  $total_col; // $col;
                         }

                         $colspan= $total_col-1; 
                         ?>
                         <?php if ($inv->grand_total != $inv->total) {
                             $other_colspan= $total_col-2;
                             ?>
                             <tr>
                                 <td colspan="<?= $tcol; ?>" class="text-right"><?= lang('total'); ?> <?= $tcol.''.$vals; ?>  
                                     (<?= $default_currency->code; ?>)
                                 </td>
                                 <?php
                                 if ($Settings->tax1 && $inv->product_tax > 0) {
                                     echo '<td class="text-right">' . $this->sma->formatMoney($return_purchase ? ($inv->product_tax + $return_purchase->product_tax) : $inv->product_tax) . '</td>';
                                 }
                             if ($Settings->product_discount && $inv->product_discount != 0) {
                                 echo '<td class="text-center"  colspan="2">' . $this->sma->formatMoney($return_purchase ? ($inv->product_discount + $return_purchase->product_discount) : $inv->product_discount) . '</td>';
                             } ?>
                                 <td class="text-right"><?= $this->sma->formatMoney($return_purchase ? (($inv->total + $inv->product_tax) + ($return_purchase->total + $return_purchase->product_tax)) : ($inv->total + $inv->product_tax)); ?></td>
                             </tr>
                         <?php
                         } ?>
                         <?php
                         if ($return_purchase) {
                             echo '<tr><td colspan="' . $colspan . '" class="text-right">' . lang('return_total') . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->sma->formatMoney($return_purchase->grand_total) . '</td></tr>';
                         }
                         if ($inv->surcharge != 0) {
                             echo '<tr><td colspan="' . $colspan . '" class="text-right">' . lang('return_surcharge') . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
                         }
                         ?>

                         <?php if ($Settings->indian_gst) {
                             if ($inv->cgst > 0) {
                                 $cgst = $return_purchase ? $inv->cgst + $return_purchase->cgst : $inv->cgst;
                                 echo '<tr><td colspan="' . $colspan . '" class="text-right">' . lang('cgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($cgst) : $cgst) . '</td></tr>';
                             }
                             if ($inv->sgst > 0) {
                                 $sgst = $return_purchase ? $inv->sgst + $return_purchase->sgst : $inv->sgst;
                                 echo '<tr><td colspan="' . $colspan . '" class="text-right">' . lang('sgst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($sgst) : $sgst) . '</td></tr>';
                             }
                             if ($inv->igst > 0) {
                                 $igst = $return_purchase ? $inv->igst + $return_purchase->igst : $inv->igst;
                                 echo '<tr><td colspan="' . $colspan . '" class="text-right">' . lang('igst') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($Settings->format_gst ? $this->sma->formatMoney($igst) : $igst) . '</td></tr>';
                             }
                         } ?>

                         <?php if ($inv->order_discount != 0) {
                             echo '<tr><td colspan="' . $colspan . '" class="text-right">' . lang('order_discount') . ' (' . $default_currency->code . ')</td><td class="text-right">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->sma->formatMoney($return_purchase ? ($inv->order_discount + $return_purchase->order_discount) : $inv->order_discount) . '</td></tr>';
                         }
                         ?>
                         <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                             echo '<tr><td colspan="' . $colspan . '" class="text-right">' . lang('order_tax') . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->sma->formatMoney($return_purchase ? ($inv->order_tax + $return_purchase->order_tax) : $inv->order_tax) . '</td></tr>';
                         }
                         ?>
                         <?php if ($inv->shipping != 0) {
                             echo '<tr><td colspan="' . $colspan . '" class="text-right">' . lang('shipping') . ' (' . $default_currency->code . ')</td><td class="text-right">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';
                         }
                         ?>
                         <tr>
                             <td colspan="<?= $colspan; //$col; ?>"
                                 style="text-align:right; font-weight:bold;"><?= lang('total_amount'); ?>
                                 (<?= $default_currency->code; ?>)
                             </td>
                             <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total); ?></td>
                         </tr>
                         <tr>
                             <td colspan="<?= $colspan; ?>"
                                 style="text-align:right; font-weight:bold;"><?= lang('paid'); ?>
                                 (<?= $default_currency->code; ?>)
                             </td>
                             <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid); ?></td>
                         </tr>
                         <tr>
                             <td colspan="<?= $colspan; ?>"
                                 style="text-align:right; font-weight:bold;"><?= lang('balance'); ?>
                                 (<?= $default_currency->code; ?>)
                             </td>
                             <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_purchase ? ($inv->grand_total + $return_purchase->grand_total) : $inv->grand_total) - ($return_purchase ? ($inv->paid + $return_purchase->paid) : $inv->paid)); ?></td>
                         </tr>
                          <?php */ ?>
                    </tfoot>
                </table>
            </div>

            <div class="table-responsive table-summary" style="width:30%; float: right">
                <table class="table table-bordered table-hover table-striped print-table order-table">

                    <tr>
                        <td>Total</td>
                        <td><?php echo $this->sma->formatNumber($totalAmount); ?></td>
                    </tr>
                    <tr>
                        <td>T-DISC</td>
                        <td><?php echo $this->sma->formatNumber($totalDiscount); ?></td>
                    </tr>
                    <tr>
                        <td>Net Before VAT</td>
                        <td><?php echo $this->sma->formatNumber($netBeforeVAT); ?></td>
                    </tr>
                    <tr>
                        <td>Total VAT</td>
                        <td><?php echo $this->sma->formatNumber($totalVAT); ?></td>
                    </tr>
                    <tr>
                        <td>Total After VAT</td>
                        <td><?php echo $this->sma->formatNumber($netBeforeVAT + $totalVAT); ?></td>
                    </tr>
                </table>
            </div>


            <!-- <?= $Settings->invoice_view > 0 ? $this->gst->summary($rows, $return_rows, ($return_purchase ? $inv->product_tax + $return_purchase->product_tax : $inv->product_tax), true) : ''; ?> -->

            <div class="row">
                <div class="col-xs-12">
                    <?php
                    if ($inv->note || $inv->note != '') {
                        ?>
                        <div class="well well-sm">
                            <p class="bold"><?= lang('note'); ?>:</p>
                            <div><?= $this->sma->decode_html($inv->note); ?></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <!-- <div class="col-xs-5 pull-right">
                    <div class="well well-sm">
                        <p>
                            <?= lang('created_by'); ?>: <?= $created_by->first_name . ' ' . $created_by->last_name; ?> <br>
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
                    </div>
                </div> -->
            </div>

            <?php //include(dirname(__FILE__) . '/../partials/attachments.php'); ?>
            <?php if (!$Supplier || !$Customer) {
                ?>
                <!-- <div class="buttons">
                    <div class="btn-group btn-group-justified">
                        <div class="btn-group">
                            <a href="<?= admin_url('purchases/add_payment/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('add_payment') ?>">
                                <i class="fa fa-dollar"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('add_payment') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('purchases/email/' . $inv->id) ?>" data-toggle="modal" data-target="#myModal2" class="tip btn btn-primary" title="<?= lang('email') ?>">
                                <i class="fa fa-envelope-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('email') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('purchases/pdf/' . $inv->id) ?>" class="tip btn btn-primary" title="<?= lang('download_pdf') ?>">
                                <i class="fa fa-download"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('pdf') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="<?= admin_url('purchases/edit/' . $inv->id) ?>" class="tip btn btn-warning sledit" title="<?= lang('edit') ?>">
                                <i class="fa fa-edit"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('edit') ?></span>
                            </a>
                        </div>
                        <div class="btn-group">
                            <a href="#" class="tip btn btn-danger bpo" title="<b><?= $this->lang->line('delete') ?></b>"
                                data-content="<div style='width:150px;'><p><?= lang('r_u_sure') ?></p><a class='btn btn-danger' href='<?= admin_url('purchases/delete/' . $inv->id) ?>'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button></div>"
                                data-html="true" data-placement="top">
                                <i class="fa fa-trash-o"></i>
                                <span class="hidden-sm hidden-xs"><?= lang('delete') ?></span>
                            </a>
                        </div>
                    </div>
                </div> -->
                <?php
            } ?>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.tip').tooltip();
    });

    document.getElementById("Print").onclick = function () {
        //printElement(document.getElementById("printThis"));
        window.print();
    };

    // function printElement(elem) {
    //     var domClone = elem.cloneNode(true);

    //     var $printSection = document.getElementById("printSection");

    //     if (!$printSection) {
    //         var $printSection = document.createElement("div");
    //         $printSection.id = "printSection";
    //         document.body.appendChild($printSection);
    //     }

    //     $printSection.innerHTML = "";
    //     $printSection.appendChild(domClone);
    //     window.print();
    // }
</script>