<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f5f5f5; font-weight: bold; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.bg-light { background-color: #f9f9f9; }
.font-weight-bold { font-weight: bold; }
</style>

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
            <strong>Supplier Service Invoice No:</strong> <?= $service_invoice->id; ?>
            <br>
            <strong>Date:</strong> <?= date('d/m/Y', strtotime($service_invoice->date)); ?>
            <br>
            <strong>Supplier:</strong> <?= $supplier->name; ?>
            <?php if ($supplier->sequence_code): ?>
            <br>
            <strong>Supplier ID:</strong> <?= $supplier->sequence_code; ?>
            <?php endif; ?>
            <?php if ($supplier->vat_no): ?>
            <br>
            <strong>Supplier VAT No:</strong> <?= $supplier->vat_no; ?>
            <?php endif; ?>
            <?php if ($supplier->cr): ?>
            <br>
            <strong>Supplier CR No:</strong> <?= $supplier->cr; ?>
            <?php endif; ?>
            <?php if ($supplier->address): ?>
            <br>
            <strong>Supplier Address:</strong> <?= $supplier->address; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- RIGHT: Company Information -->
    <div style="width:45%; float:right; padding:15px; background-color:#f9f9f9;">
        <table style="width:100%; border:0;">
            <tr>
                <td style="vertical-align:top; padding:0 10px 0 0;border:none;">
                    <div style="font-size:12px; line-height:3;">
                        <strong>Company Information:</strong><br><br>
                        <?php if ($biller->name): ?>
                        <strong style="line-height:3;">Name:</strong> <?= $biller->name; ?><br><br>
                        <?php endif; ?>
                        <?php if ($biller->vat_no): ?>
                        <strong style="line-height:3;">VAT No:</strong> <?= $biller->vat_no; ?><br><br>
                        <?php endif; ?>
                        <?php if ($biller->cr): ?>
                        <strong style="line-height:3;">CR No:</strong> <?= $biller->cr; ?><br><br>
                        <?php endif; ?>
                        <?php if ($biller->address): ?>
                        <strong style="line-height:3;">Address:</strong> <?= $biller->address; ?>
                        <?php endif; ?>
                    </div>
                </td>
                <?php if (isset($qr_code_base64)): ?>
                <td style="vertical-align:top; text-align:right; padding:0; width:80px;border:none;">
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
    <h2 style="margin:0; color:#333;">TAX INVOICE</h2>
</div>

<!-- Service Details Table -->
<table>
    <thead>
        <tr>
            <th>Ledger Account</th>
            <th class="text-right">Amount</th>
            <th class="text-right">VAT (15%)</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_amount = 0;
        $total_vat = 0;
        $grand_total = 0;
        foreach ($service_invoice_entries as $entry):
            $total_amount += $entry->amount;
            $total_vat += $entry->vat;
            $grand_total += $entry->payment_amount;
        ?>
        <tr>
            <td>
                <?php echo $entry->ledger_name; ?>
            </td>
            <td class="text-right"><?= number_format($entry->payment_amount - $entry->vat, 2); ?> SAR</td>
            <td class="text-right"><?= number_format($entry->vat, 2); ?> SAR</td>
            <td class="text-right"><?= number_format($entry->payment_amount, 2); ?> SAR</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr class="bg-light font-weight-bold">
            <td class="text-right">Totals:</td>
            <td class="text-right"><?= number_format($total_amount - $total_vat, 2); ?> SAR</td>
            <td class="text-right"><?= number_format($total_vat, 2); ?> SAR</td>
            <td class="text-right"><?= number_format($grand_total, 2); ?> SAR</td>
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