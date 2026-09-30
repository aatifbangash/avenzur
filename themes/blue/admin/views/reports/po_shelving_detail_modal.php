<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<h4>
    <i class="fa fa-info-circle"></i>
    <?= htmlspecialchars($po->reference_no) ?> &mdash; <?= htmlspecialchars($po->supplier) ?>
    <small class="text-muted"><?= htmlspecialchars($po->date) ?></small>
</h4>

<?php
// Build lookup maps keyed by trimmed product_code
$cost_map    = [];
$ordered_map = [];
foreach ($ordered as $item) {
    $cost_map[$item->product_code]    = $item->unit_cost;
    $ordered_map[$item->product_code] = $item;
}

$shelved_map = [];
foreach ($shelved as $s) {
    $shelved_map[$s->product_code] = ($shelved_map[$s->product_code] ?? 0) + $s->shelved_qty;
}

// Build merged rows (union of all product codes)
$all_codes = array_unique(array_merge(array_keys($ordered_map), array_keys($shelved_map)));
sort($all_codes);

$t_o_qty = $t_o_val = $t_s_qty = $t_s_val = 0;
?>

<div class="table-responsive" style="margin-top:15px;">
    <table class="table table-bordered table-condensed table-striped" style="font-size:12px;">
        <thead>
            <tr style="background:#dce8f7;">
                <th>#</th>
                <th>Product Code</th>
                <th>Product Name</th>
                <th class="text-right">Unit Cost</th>
                <th class="text-right">Ordered Qty</th>
                <th class="text-right">Ordered Value</th>
                <th class="text-right">Shelved Qty</th>
                <th class="text-right">Shelved Value</th>
                <th class="text-right">Remaining</th>
                <th class="text-right">Shelved %</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 0; foreach ($all_codes as $code):
                $o       = $ordered_map[$code] ?? null;
                $o_qty   = $o ? $o->ordered_qty  : 0;
                $o_val   = $o ? $o->line_value    : 0;
                $name    = $o ? $o->product_name  : ($shelved_map[$code] ? $code : $code);
                $cost    = $cost_map[$code] ?? 0;
                $s_qty   = $shelved_map[$code] ?? 0;
                $s_val   = $s_qty * $cost;
                $remain  = $o_qty - $s_qty;
                $pct     = $o_qty > 0 ? round($s_qty / $o_qty * 100, 1) : 0;
                $pct_cls = $pct >= 100 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                $t_o_qty += $o_qty; $t_o_val += $o_val;
                $t_s_qty += $s_qty; $t_s_val += $s_val;
                $i++;
                ?>
                <tr>
                    <td><?= $i ?></td>
                    <td><?= htmlspecialchars($code) ?></td>
                    <td><?= htmlspecialchars($name) ?></td>
                    <td class="text-right"><?= number_format($cost, 2) ?></td>
                    <td class="text-right"><?= number_format($o_qty, 2) ?></td>
                    <td class="text-right"><?= number_format($o_val, 2) ?></td>
                    <td class="text-right"><?= number_format($s_qty, 2) ?></td>
                    <td class="text-right"><?= number_format($s_val, 2) ?></td>
                    <td class="text-right <?= $remain > 0 ? 'text-danger' : '' ?>"><?= number_format($remain, 2) ?></td>
                    <td class="text-right"><span class="label label-<?= $pct_cls ?>"><?= $pct ?>%</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight:bold; background:#f0f0f0;">
                <td colspan="4" class="text-right">Total</td>
                <td class="text-right"><?= number_format($t_o_qty, 2) ?></td>
                <td class="text-right"><?= number_format($t_o_val, 2) ?></td>
                <td class="text-right"><?= number_format($t_s_qty, 2) ?></td>
                <td class="text-right"><?= number_format($t_s_val, 2) ?></td>
                <td class="text-right"><?= number_format($t_o_qty - $t_s_qty, 2) ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>


</div>
