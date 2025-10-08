<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }

    .invoice-header { text-align: center; margin-bottom: 10px; }

    .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .items-table th, .items-table td {
      border: 1px solid #000;
      padding: 5px;
      text-align: left;
    }
    .items-table th {
      background-color: #f2f2f2;
    }

    /* Prevent table rows from breaking awkwardly */
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; page-break-after: auto; }

    /* Summary/footer section */
    .summary {
      width: 100%;
      margin-top: 15px;
      border-top: 2px solid #000;
    }
    .summary td {
      padding: 6px;
      text-align: right;
    }
    .summary tr td.label {
      text-align: left;
      font-weight: bold;
    }

    /* Optional styling for last page totals */
    .totals { margin-top: 20px; }
  </style>
</head>
<body>


<table class="table table-bordered table-hover table-striped items-table">
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
 


</body>
</html>
