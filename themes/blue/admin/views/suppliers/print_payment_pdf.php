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
    <td><strong>Date:</strong> <?= date('Y-m-d', strtotime($payment_ref->date)) ?></td>
</tr>
<tr>
    <td><strong>Supplier:</strong> <?= $payment_ref->name ?></td>
    <td><strong>Journal Ref:</strong> <?= $payment_ref->journal_id ?></td>
</tr>
<tr>
    <td><strong>Transfer From:</strong> <?= $payment_ref->transfer_from ?></td>
    <td><strong>Bank Charges:</strong> <?= number_format($payment_ref->bank_charges, 2) ?></td>
</tr>
<tr>
    <td><strong>VAT on Bank Charges:</strong> <?= number_format($payment_ref->bank_charge_vat, 2) ?></td>
    <td><strong>Credit Limit:</strong> <?= number_format($payment_ref->credit_limit, 2) ?></td>
</tr>
<tr>
    <td><strong>Payment Term:</strong> <?= number_format($payment_ref->payment_term, 2) ?></td>
    <td></td>
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
    <th class="right">Discount</th>
    <th class="right">Return</th>
    <th class="right">Paid</th>
    <th class="right">Balance</th>
    <th class="right">Due</th>
</tr>
</thead>
<tbody>

<?php
$total_paid = 0;
foreach ($payments as $p):
    $total_paid += $p->amount;
    
    // Determine invoice type
    $invoice_type = isset($p->invoice_type) ? $p->invoice_type : 'advance';
    
    if ($invoice_type == 'service') {
        $type_label = 'Service Invoice Payment';
    } elseif ($invoice_type == 'purchase') {
        $type_label = 'Purchase Invoice Payment';
    } else {
        $type_label = 'Advance Payment';
    }
    
    // Calculate amounts based on invoice type
    $original_amount = ($p->grand_total > 0) ? $p->grand_total : 0;
    
    // For both purchase and service invoices, purchase_paid contains the CURRENT total paid (including this payment)
    // We need to calculate previously paid (before this payment) and remaining due (after this payment)
    $total_paid_now = isset($p->purchase_paid) ? $p->purchase_paid : 0;
    $previously_paid = ($total_paid_now - $p->amount) >= 0 ? ($total_paid_now - $p->amount) : 0;
    $this_payment = $p->amount;
    $remaining_due = ($original_amount - $total_paid_now) >= 0 ? ($original_amount - $total_paid_now) : 0;
?>
<tr>
    <td><?= ($p->purchase_date) ? date('Y-m-d', strtotime($p->purchase_date)) : date('d-m-Y', strtotime($p->date)) ?></td>
    <td><?= $p->ref_no ? $p->ref_no : $p->reference_no ?></td>
    <td><?= $type_label ?></td>
    <td class="right"><?= number_format($original_amount, 2) ?></td>
    <td class="right"><?= number_format($p->additional_discount ?? 0, 2) ?></td>
    <td class="right"><?= number_format($p->return_amount ?? 0, 2) ?></td>
    <td class="right"><?= number_format($previously_paid, 2) ?></td>
    <td class="right"><?= number_format($this_payment, 2) ?></td>
    <td class="right"><?= number_format($remaining_due, 2) ?></td>
    
</tr>
<?php endforeach; ?>

<tr>
    <th colspan="8" class="right">Total Paid</th>
    <th class="right"><?= number_format($total_paid, 2) ?></th>
</tr>

</tbody>
</table>


<!-- SUPPLIER BALANCE & AGING -->
<br>
<table class="no-border" cellpadding="5" style="width: 60%;">
    <tr>
        <td><strong>Supplier Advance:</strong></td>
        <td class="right"><strong><?= number_format($supplier_balance ?? 0, 2) ?></strong></td>
    </tr>
    <tr>
        <td><strong>Supplier Balance:</strong></td>
        <td class="right"><strong><?= number_format($total_due ?? 0, 2) ?></strong></td>
    </tr>

    <!--<tr>
        <td><strong>Supplier Due:</strong></td>
        <td class="right"><strong><?= number_format($total_due ?? 0, 2) ?></strong></td>
    </tr>-->
</table>

<?php if (!empty($supplier_aging)) : ?>
<br>
<table class="no-border" cellpadding="5" style="width: 80%;">
    <tr>
        <th colspan="2" style="text-align:left; background:#f2f2f2;">Supplier Aging (SAR)</th>
    </tr>
    <tr>
        <td>0-30 days</td>
        <td class="right"><?= number_format($supplier_aging['0-30'] ?? 0, 2) ?></td>
    </tr>
    <tr>
        <td>31-60 days</td>
        <td class="right"><?= number_format($supplier_aging['31-60'] ?? 0, 2) ?></td>
    </tr>
    <tr>
        <td>61-90 days</td>
        <td class="right"><?= number_format($supplier_aging['61-90'] ?? 0, 2) ?></td>
    </tr>
    <tr>
        <td>91-120 days</td>
        <td class="right"><?= number_format($supplier_aging['91-120'] ?? 0, 2) ?></td>
    </tr>
    <tr>
        <td>121-150 days</td>
        <td class="right"><?= number_format($supplier_aging['121-150'] ?? 0, 2) ?></td>
    </tr>
    <tr>
        <td>151-180 days</td>
        <td class="right"><?= number_format($supplier_aging['151-180'] ?? 0, 2) ?></td>
    </tr>
    <tr>
        <td>>180 days</td>
        <td class="right"><?= number_format($supplier_aging['>180'] ?? 0, 2) ?></td>
    </tr>
</table>
<?php endif; ?>

<br>
<!-- NOTE -->
<table class="no-border">
<tr>
    <td><strong>Note:</strong> <?= $payment_ref->note ?></td>
</tr>
</table>
