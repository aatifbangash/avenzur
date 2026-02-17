<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">

<!-- PDF Header -->
<div style="width:100%; margin-bottom:15px; position:relative;">

    <!-- RIGHT: Logo -->
    <div style="position:absolute; top:0; right:0; text-align:right;">
        <img src="<?= base_url('assets/uploads/logos/' . $biller->logo); ?>"
             style="max-width:150px; max-height:60px;">
        <div style="font-size: 12px; font-weight: bold; margin-top: 5px; color: #333;">
            <?= $biller->name ?? ''; ?>
        </div>
    </div>

    <!-- LEFT: Service Invoice details -->
    <div style="width:45%; float:left;">

        <div style="font-size:10px; color:#666; margin-bottom:6px;">
            Printed on: <?= date('d/m/Y H:i:s'); ?>
        </div>

        <div style="font-size:13px; line-height:2.4;">
            <strong>Service Invoice No:</strong> <?= $service_invoice->id; ?>
            <br>
            <strong>Date:</strong> <?= date('d/m/Y', strtotime($service_invoice->date)); ?>
            <br>
            <strong>Customer:</strong> <?= $customer->name; ?>
            <?php if ($customer->sequence_code): ?>
            <br>
            <strong>Customer ID:</strong> <?= $customer->sequence_code; ?>
            <?php endif; ?>
            <?php if ($customer->vat_no): ?>
            <br>
            <strong>Customer VAT No:</strong> <?= $customer->vat_no; ?>
            <?php endif; ?>
            <?php if ($customer->cr): ?>
            <br>
            <strong>Customer CR No:</strong> <?= $customer->cr; ?>
            <?php endif; ?>
            <?php if ($customer->address): ?>
            <br>
            <strong>Customer Address:</strong> <?= $customer->address; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- RIGHT: Company Information -->
    <div style="width:45%; float:right; padding:15px; background-color:#f9f9f9;">
        <table style="width:100%; border:0;">
            <tr>
                <td style="vertical-align:top; padding:0 10px 0 0;">
                    <div style="font-size:12px; line-height:2;">
                        <strong>Company Information:</strong><br>
                        <?php if ($biller->name): ?>
                        <strong>Name:</strong> <?= $biller->name; ?><br>
                        <?php endif; ?>
                        <?php if ($biller->vat_no): ?>
                        <strong>VAT No:</strong> <?= $biller->vat_no; ?><br>
                        <?php endif; ?>
                        <?php if ($biller->cr): ?>
                        <strong>CR No:</strong> <?= $biller->cr; ?><br>
                        <?php endif; ?>
                        <?php if ($biller->address): ?>
                        <strong>Address:</strong> <?= $biller->address; ?>
                        <?php endif; ?>
                    </div>
                </td>
                <?php if (isset($qr_code_base64)): ?>
                <td style="vertical-align:top; text-align:right; padding:0; width:80px;">
                    <img src="data:image/png;base64,<?= $qr_code_base64 ?>" width="70" height="70" />
                </td>
                <?php endif; ?>
            </tr>
        </table>
    </div>

    <!-- CLEAR -->
    <div style="clear:both;"></div>

</div>

<!-- Service Invoice Title -->
<div style="text-align:center; margin-bottom:20px;">
    <h2 style="margin:0; color:#333;">VAT INVOICE</h2>
</div>

<!-- Service Details Table -->
<table style="width:100%; border-collapse:collapse; margin-bottom:20px;">
    <thead>
        <tr style="background-color:#f5f5f5;">
            <th style="border:1px solid #ddd; padding:8px; text-align:left; font-weight:bold;">Service Type</th>
            <th style="border:1px solid #ddd; padding:8px; text-align:left; font-weight:bold;">From</th>
            <th style="border:1px solid #ddd; padding:8px; text-align:left; font-weight:bold;">To</th>
            <th style="border:1px solid #ddd; padding:8px; text-align:right; font-weight:bold;">Quantity</th>
            <th style="border:1px solid #ddd; padding:8px; text-align:right; font-weight:bold;">Unit Price</th>
            <th style="border:1px solid #ddd; padding:8px; text-align:right; font-weight:bold;">Amount</th>
            <th style="border:1px solid #ddd; padding:8px; text-align:right; font-weight:bold;">VAT</th>
            <th style="border:1px solid #ddd; padding:8px; text-align:right; font-weight:bold;">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_amount = 0;
        $total_vat = 0;
        $grand_total = 0;
        foreach ($service_invoice_entries as $entry):
            $total_amount += $entry->unit_value * $entry->quantity;
            $total_vat += $entry->vat;
            $grand_total += $entry->payment_amount;
        ?>
        <tr>
            <td style="border:1px solid #ddd; padding:8px;"><?= ucfirst(str_replace('_', ' ', $entry->service_type)); ?></td>
            <td style="border:1px solid #ddd; padding:8px;">
                <?php
                if ($entry->service_type == 'transportation') {
                    echo $entry->from_val; // City
                } else {
                    echo date('d/m/Y', strtotime($entry->from_val)); // Date
                }
                ?>
            </td>
            <td style="border:1px solid #ddd; padding:8px;">
                <?php
                if ($entry->service_type == 'transportation') {
                    echo $entry->to_val; // City
                } else {
                    echo date('d/m/Y', strtotime($entry->to_val)); // Date
                }
                ?>
            </td>
            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?= number_format($entry->quantity, 2); ?></td>
            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?= number_format($entry->unit_value, 2); ?> SAR</td>
            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?= number_format($entry->unit_value * $entry->quantity, 2); ?> SAR</td>
            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?= number_format($entry->vat, 2); ?> SAR</td>
            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?= number_format($entry->payment_amount, 2); ?> SAR</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr style="background-color:#f9f9f9; font-weight:bold;">
            <td colspan="5" style="border:1px solid #ddd; padding:8px; text-align:right;">Totals:</td>
            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?= number_format($total_amount, 2); ?> SAR</td>
            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?= number_format($total_vat, 2); ?> SAR</td>
            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?= number_format($grand_total, 2); ?> SAR</td>
        </tr>
    </tfoot>
</table>

<!-- Notes Section -->
<?php if (!empty($service_invoice->description) || !empty($service_invoice->note)): ?>
<div style="margin-top:20px;font-size:12px; color:#333;">
    <?= 'Description: '.nl2br(strip_tags($service_invoice->description)); ?>
</div>
<?php endif; ?>

<!-- Footer -->
<div style="margin-top:30px; text-align:center; font-size:10px; color:#666;">
    This is a computer generated invoice.
</div>