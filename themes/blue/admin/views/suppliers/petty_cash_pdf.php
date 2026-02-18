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
.supplier-col { width: 15%; }
.vat-number-col { width: 12%; }
.description-col { width: 15%; }
.ledger-account { width: 15%; }
.amount-col { width: 10%; }
.vat-col { width: 10%; }
.total-col { width: 10%; }
</style>

<!-- Petty Cash Title -->
<div style="text-align:center; margin-bottom:20px;">
    <h2 style="margin:0; color:#333;">PETTY CASH</h2>
</div>

<!-- PDF Header -->
<div style="width:100%; margin-bottom:15px; position:relative;">

    <!-- LEFT: Petty Cash details -->
    <div style="width:45%; float:left;">

        <div style="font-size:10px; color:#666; margin-bottom:6px;">
            Printed on: <?= date('d/m/Y H:i:s'); ?>
        </div>

        <div style="font-size:13px; line-height:2.4;">
            <strong>Petty Cash No:</strong> <?= $petty_cash->sequence_code; ?>
            <br>
            <strong>Date:</strong> <?= date('d/m/Y', strtotime($petty_cash->date)); ?>
            <br>
            <strong>Reference:</strong> <?= $petty_cash->reference_no; ?>
        </div>

    </div>

    <!-- CLEAR -->
    <div style="clear:both;"></div>

</div>

<!-- Petty Cash Details Table -->
<table>
    <thead>
        <tr>
            <th class="supplier-col">Supplier</th>
            <th class="vat-number-col">VAT Number</th>
            <th class="description-col">Description</th>
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
        foreach ($petty_cash_entries as $entry):
            $total_amount += $entry->payment_amount - $entry->vat;
            $total_vat += $entry->vat;
            $grand_total += $entry->payment_amount;
        ?>
        <tr>
            <td>
                <?php echo $entry->name; ?>
            </td>
            <td><?php echo $entry->vat_number ?: '-'; ?></td>
            <td><?php echo $entry->description ?: '-'; ?></td>
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
            <td></td>
            <td></td>
            <td></td>
            <td class="text-right">Totals:</td>
            <td class="text-right"><?= number_format($total_amount, 2); ?> SAR</td>
            <td class="text-right"><?= number_format($total_vat, 2); ?> SAR</td>
            <td class="text-right"><?= number_format($grand_total, 2); ?> SAR</td>
        </tr>
    </tfoot>
</table>

<!-- Notes Section -->
<?php if (!empty($petty_cash->description)): ?>
<div style="margin-top:20px;">
    <strong>Notes:</strong><br>
    <?= nl2br($petty_cash->description); ?>
</div>
<?php endif; ?>

<!-- Footer -->
<div style="margin-top:30px; text-align:center; font-size:10px; color:#666;">
    This is a computer generated document.
</div>