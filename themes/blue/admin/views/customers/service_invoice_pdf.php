<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link href="<?= $assets ?>styles/pdf/pdf.css" rel="stylesheet">
<style>
body { padding: 0 15px 50px 15px !important; }
body h2 { margin: 0 0 8px 0 !important; padding: 0 !important; }
</style>

<?php
$logo_file = !empty($biller->logo) ? FCPATH . 'assets/uploads/logos/' . $biller->logo : '';
$has_logo = $logo_file && file_exists($logo_file);
$invoice_no = !empty($service_invoice->reference_no) && $service_invoice->reference_no !== '0'
    ? $service_invoice->reference_no
    : (!empty($service_invoice->sequence_code)
        ? $service_invoice->sequence_code
        : 'SI-' . $service_invoice->id);
?>

<!-- PDF Header -->
<div style="width:100%; margin-bottom:15px;">

    <div style="text-align:center; margin:0; padding:0;">
        <h2 style="margin:0; color:#333; font-size:18px; line-height:1.2;">VAT INVOICE</h2>
    </div>

    <!-- Logo (left) and QR (right) below title -->
    <div style="position:relative; width:100%; min-height:70px; margin-bottom:10px;">
        <?php if ($has_logo): ?>
        <div style="position:absolute; top:0; left:0; text-align:left;">
            <img src="<?= base_url('assets/uploads/logos/' . $biller->logo); ?>"
                 style="max-width:150px; max-height:60px;">
        </div>
        <?php endif; ?>

        <?php if (isset($qr_code_base64)): ?>
        <div style="position:absolute; top:0; right:0; text-align:right;">
            <img src="data:image/png;base64,<?= $qr_code_base64 ?>" width="70" height="70" alt="QR Code" />
        </div>
        <?php endif; ?>
    </div>

    <!-- Customer and company details -->
    <table style="width:100%; border:0; border-collapse:collapse; margin-bottom:0;">
        <tr>
            <td style="width:50%; vertical-align:top; border:none; padding:0 12px 0 0;">
                <div style="font-size:10px; color:#666; margin-bottom:10px;">
                    Printed on: <?= date('d/m/Y H:i:s'); ?>
                </div>

                <div style="font-size:12px; line-height:2.2;">
                    <strong>Service Invoice No:</strong> <?= $invoice_no; ?><br><br>
                    <strong>Date:</strong> <?= date('d/m/Y', strtotime($service_invoice->date)); ?><br><br>
                    <strong>Customer:</strong> <?= $customer->name; ?>
                    <?php if ($customer->sequence_code): ?>
                    <br><br>
                    <strong>Customer ID:</strong> <?= $customer->sequence_code; ?>
                    <?php endif; ?>
                    <?php if ($customer->vat_no): ?>
                    <br><br>
                    <strong>Customer VAT No:</strong> <?= $customer->vat_no; ?>
                    <?php endif; ?>
                    <?php if ($customer->cr): ?>
                    <br><br>
                    <strong>Customer CR No:</strong> <?= $customer->cr; ?>
                    <?php endif; ?>
                    <?php if ($customer->address): ?>
                    <br><br>
                    <strong>Customer Address:</strong> <?= $customer->address; ?>
                    <?php endif; ?>
                </div>
            </td>
            <td style="width:50%; vertical-align:top; border:none; padding:0 0 0 12px;">
                <div style="padding:15px; background-color:#f9f9f9;">
                    <div style="font-size:12px; line-height:2.2;">
                        <strong>Company Information:</strong><br><br>
                        <?php if ($biller->name): ?>
                        <strong>Name:</strong> <?= $biller->name; ?><br><br>
                        <?php endif; ?>
                        <?php if ($biller->vat_no): ?>
                        <strong>VAT No:</strong> <?= $biller->vat_no; ?><br><br>
                        <?php endif; ?>
                        <?php if ($biller->cr): ?>
                        <strong>CR No:</strong> <?= $biller->cr; ?><br><br>
                        <?php endif; ?>
                        <?php if ($biller->address): ?>
                        <strong>Address:</strong> <?= $biller->address; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div style="clear:both;"></div>
</div>

<!-- Service Details Table -->
<table style="width:100%; border-collapse:collapse; margin-top:25px; margin-bottom:20px;">
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
            $total_amount += $entry->unit_value;
            $total_vat += $entry->vat;
            $grand_total += $entry->payment_amount;
        ?>
        <tr>
            <td style="border:1px solid #ddd; padding:8px;">
                <?php
                $serviceLabel = ucfirst(str_replace('_', ' ', $entry->service_type));
                if ($entry->service_type == 'transportation' && !empty($entry->name)) {
                    $serviceLabel .= ' — ' . $entry->name;
                }
                echo $serviceLabel;
                ?>
            </td>
            <td style="border:1px solid #ddd; padding:8px;">
                <?php
                if ($entry->service_type == 'transportation') {
                    echo $entry->from_val;
                } else {
                    echo date('d/m/Y', strtotime($entry->from_val));
                }
                ?>
            </td>
            <td style="border:1px solid #ddd; padding:8px;">
                <?php
                if ($entry->service_type == 'transportation') {
                    echo $entry->to_val;
                } else {
                    echo date('d/m/Y', strtotime($entry->to_val));
                }
                ?>
            </td>
            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?= number_format($entry->quantity, 2); ?></td>
            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?= number_format(($entry->unit_value / $entry->quantity), 2); ?> SAR</td>
            <td style="border:1px solid #ddd; padding:8px; text-align:right;"><?= number_format($entry->unit_value, 2); ?> SAR</td>
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