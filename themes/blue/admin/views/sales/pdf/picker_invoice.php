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

    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; page-break-after: auto; }

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

    .totals { margin-top: 20px; }
  </style>
</head>
<body>


<table class="table table-bordered table-hover table-striped items-table">
    <thead>
        <tr>
            <th>#</th>
            <th><?= lang('Product Code'); ?></th>
            <th><?= lang('Description'); ?></th>
            <th><?= lang('Warehouse Shelf'); ?></th>
            <th><?= lang('Batch'); ?></th>
            <th><?= lang('Expiry'); ?></th>
            <th><?= lang('Qty'); ?></th>
        </tr>
    </thead>

    <tbody>
        <?php 
        $r = 1;

        // $picker_items is the result of your new picker function
        foreach ($rows as $item):
           // echo '<pre>';print_r($item);exit;
            foreach ($item->picker_batches as $pick): ?>
            
                <tr>
                    <td style="text-align:center;vertical-align:middle;"><?= $r; ?></td>

                    <td style="vertical-align:middle;">
                        <?= $item->product_code ?: ' - '; ?>
                    </td>

                    <td style="vertical-align:middle;">
                        <?= $item->product_name; ?>
                    </td>

                    <td style="text-align:center; vertical-align:middle;">
                        <?= $pick->zone_number . $pick->rack_number . $pick->box_number ?>
                    </td>

                    <td style="text-align:center; vertical-align:middle;">
                        <?= $pick->batch_number; ?>
                    </td>

                    <td style="text-align:center; vertical-align:middle;">
                        <?= $pick->expiry_date; ?>
                    </td>

                    <td style="text-align:center; vertical-align:middle;">
                        <?= $this->sma->formatQuantity($pick->pick_qty); ?>
                    </td>
                </tr>

        <?php 
                $r++;
            endforeach;

        endforeach;
        ?>
    </tbody>
</table>

</body>
</html>
