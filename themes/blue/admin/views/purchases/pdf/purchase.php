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
                        <th><?= lang('Purchase Price'); ?></th>
                        <th><?= lang('Qty'); ?></th>
                        <th><?= lang('Bonus'); ?></th>
                        <th><?= lang('Dis1 %'); ?></th>
                        <th><?= lang('Dis2 %'); ?></th>
                        <?php if ($Settings->product_discount && $inv->product_discount != 0) { ?>
                        <th><?= lang('Deal Disc %'); ?></th>
                        <th><?= lang('Deal Disc Value'); ?></th>
                        <?php } else { ?>
                        <th><?= lang('Dis3 %'); ?></th>
                        <?php } ?>
                        <th><?= lang('VAT %'); ?></th>
                        <th><?= lang('Total without VAT %'); ?></th>
                        <th><?= lang('Grand Total'); ?></th>
                        
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
                                <?= htmlspecialchars(strip_tags((string) $row->product_name), ENT_QUOTES, 'UTF-8'); ?>
                                
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= $row->unit_cost; ?>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= $row->quantity; ?>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= $row->bonus; ?>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= $row->discount1; ?>
                            </td>
                            <td style="text-align:right;">
                                <?= $row->discount2; ?>
                            </td>
                            <?php if ($Settings->product_discount && $inv->product_discount != 0) {
                                $ddp = isset($row->deal_discount_percent) ? (float) $row->deal_discount_percent : 0;
                                $ddv = isset($row->deal_discount_value) ? (float) $row->deal_discount_value : 0;
                                ?>
                            <td style="text-align:right;"><?= $ddp != 0 ? number_format($ddp, 2) . '%' : ''; ?></td>
                            <td style="text-align:right;"><?= $ddv != 0 ? $this->sma->formatNumber($ddv) : ''; ?></td>
                            <?php } else { ?>
                              <td style="text-align:right;">
                                <?= $row->discount3; ?>
                            </td>
                            <?php } ?>
                             <td style="text-align:right;">
                                <?= $row->item_tax; ?>
                            </td>
                               <td style="text-align:right;">
                                <?= $row->totalbeforevat; ?>
                            </td>
                               <td style="text-align:right;">
                                <?= $row->main_net; ?>
                            </td>
                         
                           
                           
                        </tr>
                        <?php
                        $r++;
                    endforeach;

                    ?>
                </tbody>
                
            </table>
 


</body>
</html>
