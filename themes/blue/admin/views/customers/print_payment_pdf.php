<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
body { font-family: DejaVu Sans; font-size: 12px; }
table { width:100%; border-collapse: collapse; }
th, td { border:1px solid #000; padding:6px; }
th { background:#f2f2f2; }
.no-border td { border:none; }
.right { text-align:right; }
.center { text-align:center; }
</style>

<!-- PAYMENT INFO -->
<table class="no-border" cellpadding="5">
<tr>
    <td><strong>Voucher No:</strong> <?= $payment_ref->id ?></td>
    <td>
        <strong>Date:</strong> <?= date('Y-m-d', strtotime($payment_ref->date)) ?><br>
    </td>
</tr>
<tr>
    <td><strong>Customer:</strong>
        <?php if (!empty($customer->sequence_code)): ?><strong><?= htmlspecialchars($customer->sequence_code) ?></strong> - <?php endif; ?>
        <?= htmlspecialchars($payment_ref->name) ?>
    </td>
    <td><strong>Journal Ref:</strong> <?= $payment_ref->journal_id ?></td>
</tr>
<tr>
    <td><strong>Collected By:</strong> <?= $payment_ref->transfer_from ?></td>
    <td><strong>Payment Amount:</strong> <?= number_format((float)$payment_ref->amount, 2) ?></td>
</tr>
<tr>
    <td><strong>Credit Limit:</strong> <?= number_format($customer->credit_limit ?? 0, 2) ?></td>
    <td><strong>Payment Term:</strong> <?= $customer->payment_term ?? '' ?></td>
</tr>
</table>

<br>

<!-- PAYMENT DETAILS -->
<table>
<thead>
<tr>
    <th>Date</th>
    <th>Reference</th>
    <th>Type</th>
    <th class="right">Original</th>
    <th class="right">Return</th>
    <th class="right">Discount</th>
    <th class="right">Prev. Collected</th>
    <th class="right">This Payment</th>
    <th class="right">Due</th>
</tr>
</thead>
<tbody>

<?php
$total_collected = 0;
foreach ($payments as $p):
    $total_collected += (float)$p->amount;

    $type_label = ($p->sale_id) ? 'Invoice Payment' : 'Advance Collection';

    $original_amount     = (float)($p->grand_total ?? 0);
    $return_amount       = (float)($p->returns_total_deducted ?? 0);
    $discount_amount     = (float)($p->additional_discount ?? 0);
    $this_payment        = (float)$p->amount;
    $total_paid_on_sale  = (float)($p->paid ?? 0);
    $previously_collected = max(0, $total_paid_on_sale - $this_payment);
    $remaining_due       = max(0, $original_amount - $return_amount - $discount_amount - $total_paid_on_sale);
?>
<tr>
    <td><?= $p->sale_id ? date('Y-m-d', strtotime($p->sale_date)) : date('Y-m-d', strtotime($p->date)) ?></td>
    <td><?= $p->ref_no ?: ($p->sale_id ?: '-') ?></td>
    <td><?= $type_label ?></td>
    <td class="right"><?= number_format($original_amount, 2) ?></td>
    <td class="right"><?= number_format($return_amount, 2) ?></td>
    <td class="right"><?= number_format($discount_amount, 2) ?></td>
    <td class="right"><?= number_format($previously_collected, 2) ?></td>
    <td class="right"><?= number_format($this_payment, 2) ?></td>
    <td class="right"><?= number_format($remaining_due, 2) ?></td>
</tr>
<?php endforeach; ?>

<tr>
    <th colspan="7" class="right">Total Collected</th>
    <th class="right"><?= number_format($total_collected, 2) ?></th>
    <th></th>
</tr>

</tbody>
</table>


<!-- CUSTOMER BALANCE -->
<br>
<table class="no-border" cellpadding="5" style="width: 60%;">
    <tr>
        <td><strong>Customer Advance:</strong></td>
        <td class="right"><strong><?= number_format($customer_balance ?? 0, 2) ?></strong></td>
    </tr>
    <tr>
        <td><strong>Customer Balance:</strong></td>
        <td class="right"><strong><?= number_format($total_due ?? 0, 2) ?></strong></td>
    </tr>
</table>

<?php if (!empty($customer_aging)) : ?>
<br>
<table cellpadding="5" style="width:100%; table-layout:fixed;">
    <colgroup>
        <col style="width:14.28%">
        <col style="width:14.28%">
        <col style="width:14.28%">
        <col style="width:14.28%">
        <col style="width:14.28%">
        <col style="width:14.28%">
        <col style="width:14.32%">
    </colgroup>
    <thead>
        <tr>
            <th class="center">0-30 days</th>
            <th class="center">31-60 days</th>
            <th class="center">61-90 days</th>
            <th class="center">91-120 days</th>
            <th class="center">121-150 days</th>
            <th class="center">151-180 days</th>
            <th class="center">&gt;180 days</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="right"><?= number_format($customer_aging['0-30']    ?? 0, 2) ?></td>
            <td class="right"><?= number_format($customer_aging['31-60']   ?? 0, 2) ?></td>
            <td class="right"><?= number_format($customer_aging['61-90']   ?? 0, 2) ?></td>
            <td class="right"><?= number_format($customer_aging['91-120']  ?? 0, 2) ?></td>
            <td class="right"><?= number_format($customer_aging['121-150'] ?? 0, 2) ?></td>
            <td class="right"><?= number_format($customer_aging['151-180'] ?? 0, 2) ?></td>
            <td class="right"><?= number_format($customer_aging['>180']    ?? 0, 2) ?></td>
        </tr>
    </tbody>
</table>
<?php endif; ?>

<br>
<!-- NOTE -->
<table class="no-border">
<tr>
    <td><strong>Note:</strong> <?= $payment_ref->note ?></td>
</tr>
</table>
