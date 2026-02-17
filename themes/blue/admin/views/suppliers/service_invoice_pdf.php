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
.ledger-account { width: 40%; }
.amount-col { width: 20%; }
.vat-col { width: 20%; }
.total-col { width: 20%; }
</style>

<!-- Service Invoice Title -->
<div style="text-align:center; margin-bottom:20px;">
    <h2 style="margin:0; color:#333;">SUPPLIER SERVICE INVOICE</h2>
</div>

<!-- PDF Header -->
<div style="width:100%; margin-bottom:15px; position:relative;">

    <!-- LEFT: Service Invoice details -->
    <div style="width:45%; float:left;">

        <div style="font-size:10px; color:#666; margin-bottom:6px;">
            Printed on: <?= date('d/m/Y H:i:s'); ?>
        </div>

        <div style="font-size:13px; line-height:2.4;">
            <strong>Supplier Service Invoice No:</strong> <?= $service_invoice->sequence_code; ?>
            <br>
            <strong>Date:</strong> <?= date('d/m/Y', strtotime($service_invoice->date)); ?>
            <br>
            <strong>Supplier:</strong> <?= $supplier->name; ?>
            <?php if ($supplier->sequence_code): ?>
            <br>
            <strong>Supplier ID:</strong> <?= $supplier->sequence_code; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- CLEAR -->
    <div style="clear:both;"></div>

</div>

<!-- Service Details Table -->
<table>
    <thead>
        <tr>
            <th class="ledger-account">Ledger Account</th>
            <th class="amount-col text-right">Amount</th>
            <th class="vat-col text-right">VAT (15%)</th>
            <th class="total-col text-right">Total</th>
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
            <td class="text-right"><?= number_format($grand_total - $total_vat, 2); ?> SAR</td>
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

<!-- Related Ledger Entry -->
<?php if (isset($ledger_entry) && isset($ledger_entryitems)): ?>
<div style="margin-top:30px;">
    <h3 style="margin-bottom:10px; color:#333;">Ledger Entry</h3>
    <div style="font-size:12px; margin-bottom:10px;">
        <strong>Number:</strong> <?= $ledger_entry->id; ?><br>
        <strong>Date:</strong> <?= date('d/m/Y', strtotime($ledger_entry->date)); ?><br>
        
    </div>
    <table>
        <thead>
            <tr>
                <th>Ledger</th>
                <th>D/C</th>
                <th class="text-right">Amount</th>
                <th>Narration</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ledger_entryitems as $item): ?>
            <tr>
                <td><?= $item->ledger_name; ?></td>
                <td class="text-center"><?= $item->dc; ?></td>
                <td class="text-right"><?= number_format($item->amount, 2); ?> SAR</td>
                <td><?= $item->narration; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Footer -->
<div style="margin-top:30px; text-align:center; font-size:10px; color:#666;">
    This is a computer generated invoice.
</div>