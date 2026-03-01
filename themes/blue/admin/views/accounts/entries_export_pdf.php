<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($entrytype['label']) ?> Entry #<?= $entry['number'] ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            direction: <?= $Settings->rtl ? 'rtl' : 'ltr' ?>;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            color: #333;
        }
        .info-section {
            margin-bottom: 15px;
            font-size: 11px;
        }
        .info-section p {
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #94ce58;
            color: #000;
            padding: 8px;
            text-align: center;
            border: 1px solid #333;
            font-weight: bold;
        }
        table td {
            padding: 6px 8px;
            border: 1px solid #999;
            vertical-align: top;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .total-row {
            background-color: #fdbf2d;
            font-weight: bold;
        }
        .striped-row {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><?= ucfirst($entrytype['label']) ?> <?= lang('entry_title') ?> #<?= $entry['number'] ?></h2>
        <p><strong><?= lang('entries_views_add_label_date') ?>:</strong> <?= $entry['date'] ?></p>
    </div>

    <div class="info-section">
        <?php if (isset($entry['pid']) && $entry['pid'] > 0): ?>
            <p><strong><?= lang('Purchase_id') ?>:</strong> <?= $entry['pid'] ?></p>
        <?php endif; ?>
        
        <?php if (isset($entry['sid']) && $entry['sid'] > 0): ?>
            <p><strong><?= lang('Sale_id') ?>:</strong> <?= $entry['sid'] ?></p>
        <?php endif; ?>
        
        <?php if (isset($entry['tid']) && $entry['tid'] > 0): ?>
            <p><strong><?= lang('Transfer_ID') ?>:</strong> <?= $entry['tid'] ?></p>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">
                    <?php 
                    if ($mSettings->drcr_toby == 'toby') {
                        echo lang('entries_views_views_th_to_by');
                    } else {
                        echo lang('entries_views_views_th_dr_cr');
                    }
                    ?>
                </th>
                <th style="width: 35%;"><?= lang('entries_views_views_th_ledger') ?></th>
                <th style="width: 15%;" class="text-right"><?= lang('entries_views_views_th_dr_amount') ?></th>
                <th style="width: 15%;" class="text-right"><?= lang('entries_views_views_th_cr_amount') ?></th>
                <th style="width: 27%;"><?= lang('entries_views_views_th_narration') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $row_num = 0;
            foreach ($curEntryitems as $entryitem): 
                $row_num++;
                $striped = ($row_num % 2 == 0) ? 'striped-row' : '';
                
                // Determine DR/CR label
                if ($mSettings->drcr_toby == 'toby') {
                    $dr_cr_label = ($entryitem['dc'] == 'D') ? lang('entries_views_views_toby_D') : lang('entries_views_views_toby_C');
                } else {
                    $dr_cr_label = ($entryitem['dc'] == 'D') ? lang('entries_views_views_drcr_D') : lang('entries_views_views_drcr_C');
                }
            ?>
            <tr class="<?= $striped ?>">
                <td class="text-center"><?= $dr_cr_label ?></td>
                <td><?= $entryitem['ledger_name'] ?></td>
                <td class="text-right"><?= ($entryitem['dc'] == 'D') ? number_format($entryitem['dr_amount'], 2) : '' ?></td>
                <td class="text-right"><?= ($entryitem['dc'] == 'C') ? number_format($entryitem['cr_amount'], 2) : '' ?></td>
                <td><?= $entryitem['narration'] ?></td>
            </tr>
            <?php endforeach; ?>
            
            <!-- Total Row -->
            <tr class="total-row">
                <td colspan="2" class="text-center"><strong><?= lang('entries_views_views_td_total') ?></strong></td>
                <td class="text-right"><strong><?= number_format($entry['dr_total'], 2) ?></strong></td>
                <td class="text-right"><strong><?= number_format($entry['cr_total'], 2) ?></strong></td>
                <td></td>
            </tr>
            
            <?php if ($entry['dr_total'] != $entry['cr_total']): ?>
            <!-- Difference Row -->
            <tr class="total-row">
                <td colspan="2" class="text-center"><strong><?= lang('entries_views_views_td_diff') ?></strong></td>
                <?php if ($entry['dr_total'] > $entry['cr_total']): ?>
                    <td class="text-right"><strong><?= number_format($entry['dr_total'] - $entry['cr_total'], 2) ?></strong></td>
                    <td class="text-right"></td>
                <?php else: ?>
                    <td class="text-right"></td>
                    <td class="text-right"><strong><?= number_format($entry['cr_total'] - $entry['dr_total'], 2) ?></strong></td>
                <?php endif; ?>
                <td></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($jl_attachments)): ?>
    <div style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 15px;">
        <h3 style="font-size: 14px; margin-bottom: 10px;">Attachments:</h3>
        <table style="width: 100%; font-size: 11px;">
            <thead>
                <tr>
                    <th style="width: 50%; text-align: left; padding: 5px; border: 1px solid #ccc; background-color: #f5f5f5;">File Name</th>
                    <th style="width: 25%; text-align: left; padding: 5px; border: 1px solid #ccc; background-color: #f5f5f5;">File Size</th>
                    <th style="width: 25%; text-align: left; padding: 5px; border: 1px solid #ccc; background-color: #f5f5f5;">Uploaded</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jl_attachments as $attachment): 
                    $file_size_kb = round($attachment['file_size'] / 1024, 2);
                ?>
                <tr>
                    <td style="padding: 5px; border: 1px solid #ccc;"><?= $attachment['file_name'] ?></td>
                    <td style="padding: 5px; border: 1px solid #ccc;"><?= $file_size_kb ?> KB</td>
                    <td style="padding: 5px; border: 1px solid #ccc;"><?= date('Y-m-d H:i', strtotime($attachment['uploaded_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="footer">
        <p><?= lang('printed_on') ?>: <?= date('Y-m-d H:i:s') ?></p>
        <p><?= $Settings->site_name ?></p>
    </div>
</body>
</html>
