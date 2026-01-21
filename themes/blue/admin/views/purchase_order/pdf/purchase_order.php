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
                        <th><?= lang('Dis3 %'); ?></th>
                        <th><?= lang('VAT %'); ?></th>
                        <th><?= lang('Total without VAT'); ?></th>
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
                                <?= $row->product_name; ?>
                                
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= number_format($row->unit_cost, 2); ?>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= number_format($row->quantity, 2); ?>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= $row->bonus; ?>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <?= number_format($row->discount1, 2); ?>
                            </td>
                            <td style="text-align:right;">
                                <?= number_format($row->discount2, 2); ?>
                            </td>
                              <td style="text-align:right;">
                                <?= number_format($row->discount3, 2); ?>
                            </td>
                             <td style="text-align:right;">
                                <?= $row->item_tax ? '15% - ' . number_format($row->item_tax, 2) : '0%' ?>
                            </td>
                               <td style="text-align:right;">
                                <?= number_format($row->totalbeforevat, 2); ?>
                            </td>
                               <td style="text-align:right;">
                                <?= number_format($row->main_net, 2); ?>
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
