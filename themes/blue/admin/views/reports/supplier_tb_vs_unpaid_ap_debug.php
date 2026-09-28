<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$fmt = function ($n) {
    return number_format((float) $n, 2, '.', ',');
};
$h = function ($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
};
$amt_cls = function ($n) {
    return $n > 0.01 ? 'diff-pos' : ($n < -0.01 ? 'diff-neg' : '');
};
$supplier = $debug['supplier'];
$supplier_name = $supplier->company ?: $supplier->name;

$scope_qs = '&from_date=' . urlencode($start_date) . '&to_date=' . urlencode($end_date);
$stmt_url = admin_url('reports/supplier_statement') . '?supplier=' . (int) $supplier->id . $scope_qs;
$unpaid_url = admin_url('reports/unpaid_invoices_ap') . '?at_date=' . urlencode($end_date) . '&party_id=' . (int) $supplier->id;
if (!empty($warehouse_id)) {
    $stmt_url .= '&warehouse_id=' . (int) $warehouse_id;
    $unpaid_url .= '&warehouse_id=' . (int) $warehouse_id;
} else {
    $stmt_url .= '&all=1';
    $unpaid_url .= '&all=1&warehouse_id=';
}

$doc_url = function ($kind, $id) {
    switch ($kind) {
        case 'purchase':        return admin_url('purchases/view/' . (int) $id);
        case 'service_invoice': return admin_url('suppliers/edit_service_invoice/' . (int) $id);
        case 'credit_memo':
        case 'debit_memo':      return admin_url('suppliers/view_debit_memo/' . (int) $id);
        case 'return':          return admin_url('returns_supplier/view/' . (int) $id);
    }
    return null;
};
$kind_labels = [
    'purchase'        => 'Purchase',
    'service_invoice' => 'Service Inv.',
    'credit_memo'     => 'Credit Memo',
    'payment'         => 'Payment',
    'no_journal'      => 'Payment (no JV)',
    'return'          => 'Return',
    'debit_memo'      => 'Debit Memo',
];
$status_cls = [
    'overpaid' => 'label-danger',
    'partial'  => 'label-warning',
    'unpaid'   => 'label-default',
    'paid'     => 'label-success',
];

$invoice_gap_count = 0;
foreach ($debug['invoices'] as $inv) {
    if (abs($inv['gap']) >= 0.01) {
        $invoice_gap_count++;
    }
}
$source_gap_count = 0;
foreach ($debug['sources'] as $src) {
    if (abs($src['unallocated']) >= 0.01) {
        $source_gap_count++;
    }
}
?>
<style>
    .dbg-cards { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
    .dbg-card { flex: 1 1 180px; border: 1px solid #ddd; border-radius: 4px; padding: 10px 14px; background: #fafbfc; }
    .dbg-card .k { font-size: 11px; color: #777; text-transform: uppercase; letter-spacing: .03em; }
    .dbg-card .v { font-size: 18px; font-weight: bold; margin-top: 2px; }
    .dbg-section { margin: 26px 0 8px; }
    .dbg-section h3 { font-size: 15px; margin: 0 0 4px; }
    .dbg-section p { font-size: 12px; color: #666; margin: 0 0 8px; }
    .diff-pos { color: #c0392b; font-weight: bold; }
    .diff-neg { color: #27ae60; font-weight: bold; }
    .dbg-table { font-size: 12px; margin: 0; }
    .dbg-table td.num, .dbg-table th.num { text-align: right; white-space: nowrap; }
    .dbg-table tr.has-gap td { background: #fff4f2 !important; }
    .dbg-flags { margin: 3px 0 0; padding: 0; list-style: none; font-size: 11px; color: #a15c00; }
    .dbg-alloc td { background: #f7f9fb !important; font-size: 11px; }
    .dbg-alloc table { margin: 4px 0; background: #fff; }
    .dbg-toggle { cursor: pointer; }
    .dbg-bridge td.num { font-weight: bold; }
    .dbg-bridge tr.total td { border-top: 2px solid #333; font-weight: bold; }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-bug"></i>Debug — <?= $h($supplier->sequence_code); ?> <?= $h($supplier_name); ?></h2>
    </div>
    <div class="box-content">
        <p class="text-muted" style="font-size:12px;">
            TB period <strong><?= $h($start_date); ?> → <?= $h($end_date); ?></strong>,
            Unpaid as-of <strong><?= $h($end_date); ?></strong>,
            scope: <strong><?= empty($warehouse_id) ? 'All local warehouses' : 'Warehouse #' . (int) $warehouse_id; ?></strong>.
            &nbsp;
            <a class="btn btn-xs btn-default" href="<?= $stmt_url; ?>" target="_blank">Statement</a>
            <a class="btn btn-xs btn-default" href="<?= $unpaid_url; ?>" target="_blank">Unpaid</a>
            <a class="btn btn-xs btn-default" href="javascript:history.back()">&larr; Back to comparison</a>
        </p>

        <div class="dbg-cards">
            <div class="dbg-card"><div class="k">TB EB Credit</div><div class="v"><?= $fmt($debug['tb_credit']); ?></div></div>
            <div class="dbg-card"><div class="k">Unpaid AP</div><div class="v"><?= $fmt($debug['unpaid_total']); ?></div></div>
            <div class="dbg-card"><div class="k">Difference (TB − Unpaid)</div><div class="v <?= $amt_cls($debug['difference']); ?>"><?= $fmt($debug['difference']); ?></div></div>
            <div class="dbg-card"><div class="k">Invoices with gap</div><div class="v"><?= $invoice_gap_count; ?></div></div>
            <div class="dbg-card"><div class="k">Payments / returns / memos unallocated</div><div class="v"><?= $source_gap_count; ?></div></div>
        </div>

        <!-- Bridge -->
        <div class="dbg-section">
            <h3>Where the difference comes from</h3>
            <p>Each line is the sum of the matching table below. Positive = statement higher than Unpaid AP.</p>
        </div>
        <div class="table-responsive" style="max-width:720px;">
            <table class="table table-bordered table-condensed dbg-table dbg-bridge">
                <tbody>
                    <?php foreach ($debug['bridge'] as $b): ?>
                        <tr>
                            <td><?= $h($b['label']); ?></td>
                            <td class="num <?= $amt_cls($b['amount']); ?>"><?= $fmt($b['amount']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total">
                        <td>Total explained</td>
                        <td class="num"><?= $fmt($debug['bridge_total']); ?></td>
                    </tr>
                    <?php if (abs($debug['unexplained']) >= 0.01): ?>
                        <tr>
                            <td class="text-danger">Unexplained (rounding / data outside scope)</td>
                            <td class="num diff-pos"><?= $fmt($debug['unexplained']); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Invoices -->
        <div class="dbg-section">
            <h3>Invoices — paid per invoice vs statement</h3>
            <p>
                <strong>Statement</strong> = amount posted to the supplier ledger for the invoice.
                <strong>Paid</strong> = payments, returns and debit memos allocated to it.
                <strong>Gap</strong> = (Statement − Paid) − Unpaid outstanding. Click a row to see its payments.
            </p>
            <label style="font-weight:normal; font-size:12px;">
                <input type="checkbox" class="dbg-only-gap" data-target="#dbg-invoices" checked> Only rows with a gap
            </label>
        </div>
        <div class="table-responsive">
            <table id="dbg-invoices" class="table table-bordered table-condensed table-hover dbg-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Date</th>
                        <th>Warehouse</th>
                        <th class="num">Invoice Amount</th>
                        <th class="num">Statement</th>
                        <th class="num">Paid</th>
                        <th>Payment Status</th>
                        <th class="num">Balance per Statement</th>
                        <th class="num">Unpaid Outstanding</th>
                        <th class="num">Gap</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($debug['invoices'])): ?>
                        <tr><td colspan="11" class="text-center text-muted">No invoices.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($debug['invoices'] as $i => $inv): ?>
                        <?php
                        $gap = abs($inv['gap']) >= 0.01;
                        $st = $inv['status'];
                        $url = $doc_url($inv['kind'], $inv['id']);
                        ?>
                        <tr class="dbg-toggle <?= $gap ? 'has-gap' : 'no-gap'; ?>" data-alloc="#dbg-alloc-<?= $i; ?>">
                            <td><?= $h($kind_labels[$inv['kind']] ?? $inv['kind']); ?></td>
                            <td>
                                <?php if ($url): ?><a href="<?= $url; ?>" target="_blank" onclick="event.stopPropagation();"><?= $h($inv['reference_no'] ?: '#' . $inv['id']); ?></a><?php else: ?><?= $h($inv['reference_no']); ?><?php endif; ?>
                                <?php if (!empty($inv['invoice_number'])): ?><br><small class="text-muted"><?= $h($inv['invoice_number']); ?></small><?php endif; ?>
                                <?php if (!empty($inv['flags'])): ?>
                                    <ul class="dbg-flags">
                                        <?php foreach ($inv['flags'] as $f): ?><li><i class="fa fa-exclamation-triangle"></i> <?= $h($f); ?></li><?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap"><?= $h($inv['date']); ?></td>
                            <td><?= $h($inv['warehouse']); ?></td>
                            <td class="num"><?= $fmt($inv['invoice_amount']); ?></td>
                            <td class="num"><?= $fmt($inv['gl']); ?></td>
                            <td class="num">
                                <?= $fmt($inv['paid']); ?>
                                <small class="text-muted">(<?= (int) $inv['paid_count']; ?>)</small>
                            </td>
                            <td class="text-nowrap">
                                <span class="label <?= $status_cls[$st['key']] ?? 'label-default'; ?>"><?= $h($st['label']); ?></span>
                                <?php if ($st['amount'] > 0.01): ?><small><?= $fmt($st['amount']); ?></small><?php endif; ?>
                            </td>
                            <td class="num <?= $inv['stmt_balance'] < -0.01 ? 'diff-pos' : ''; ?>"><?= $fmt($inv['stmt_balance']); ?></td>
                            <td class="num"><?= $fmt($inv['unpaid']); ?></td>
                            <td class="num <?= $amt_cls($inv['gap']); ?>"><?= $fmt($inv['gap']); ?></td>
                        </tr>
                        <tr id="dbg-alloc-<?= $i; ?>" class="dbg-alloc <?= $gap ? 'has-gap-child' : 'no-gap-child'; ?>" style="display:none;">
                            <td colspan="11">
                                <?php if (empty($inv['allocations'])): ?>
                                    <span class="text-muted">No payments allocated to this invoice.</span>
                                <?php else: ?>
                                    <table class="table table-condensed table-bordered" style="max-width:760px;">
                                        <thead>
                                            <tr><th>Date</th><th>Source</th><th>Paid by</th><th class="num">Amount</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($inv['allocations'] as $a): ?>
                                                <tr>
                                                    <td class="text-nowrap"><?= $h($a['date']); ?></td>
                                                    <td>
                                                        <?php if ($a['pr_id'] > 0 && $a['paid_by'] !== 'return' && $a['paid_by'] !== 'debit_memo'): ?>
                                                            <a href="<?= admin_url('suppliers/view_payment?id=' . (int) $a['pr_id']); ?>" target="_blank"><?= $h($a['source']); ?></a>
                                                        <?php else: ?>
                                                            <?= $h($a['source']); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $h($a['paid_by'] ?: $a['type']); ?></td>
                                                    <td class="num"><?= $fmt($a['amount']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Sources -->
        <div class="dbg-section">
            <h3>Payments, returns &amp; debit memos — posted vs allocated</h3>
            <p>
                <strong>Statement</strong> = amount posted to the supplier ledger (negative = reduces payable).
                <strong>Allocated</strong> = portion applied to invoices above.
                <strong>Unallocated</strong> = Statement + Allocated; non-zero means it moves the TB but not Unpaid AP.
            </p>
            <label style="font-weight:normal; font-size:12px;">
                <input type="checkbox" class="dbg-only-gap" data-target="#dbg-sources" checked> Only rows with unallocated amount
            </label>
        </div>
        <div class="table-responsive">
            <table id="dbg-sources" class="table table-bordered table-condensed table-hover dbg-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Date</th>
                        <th class="num">Statement</th>
                        <th class="num">Allocated to Invoices</th>
                        <th class="num">Unallocated</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($debug['sources'])): ?>
                        <tr><td colspan="7" class="text-center text-muted">No payments, returns or debit memos.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($debug['sources'] as $s): ?>
                        <?php
                        $gap = abs($s['unallocated']) >= 0.01;
                        if (in_array($s['kind'], ['payment', 'no_journal'], true) && !empty($s['pr_id'])) {
                            $url = admin_url('suppliers/view_payment?id=' . (int) $s['pr_id']);
                        } else {
                            $url = $doc_url($s['kind'], $s['id']);
                        }
                        $ref = $s['reference'] ?? '';
                        if ($s['kind'] === 'payment') {
                            $ref = ($ref ?: 'JV') . ' / JV ' . (int) $s['id'];
                        }
                        ?>
                        <tr class="<?= $gap ? 'has-gap' : 'no-gap'; ?>">
                            <td><?= $h($kind_labels[$s['kind']] ?? $s['kind']); ?></td>
                            <td><?php if ($url): ?><a href="<?= $url; ?>" target="_blank"><?= $h($ref); ?></a><?php else: ?><?= $h($ref); ?><?php endif; ?></td>
                            <td class="text-nowrap"><?= $h($s['date'] ?? ''); ?></td>
                            <td class="num"><?= $fmt($s['gl']); ?></td>
                            <td class="num"><?= $fmt($s['allocated']); ?></td>
                            <td class="num <?= $amt_cls($s['unallocated']); ?>"><?= $fmt($s['unallocated']); ?></td>
                            <td style="font-size:11px;"><?= $h($s['note']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Unlinked GL -->
        <?php if (!empty($debug['gl_unlinked'])): ?>
            <div class="dbg-section">
                <h3>Statement entries not linked to any document</h3>
                <p>Opening balances, manual journals, etc. These affect the TB but never appear in Unpaid AP.</p>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-condensed table-hover dbg-table">
                    <thead>
                        <tr><th>JV #</th><th>Number</th><th>Date</th><th>Type</th><th>Narration</th><th class="num">Amount (Cr +)</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debug['gl_unlinked'] as $g): ?>
                            <tr>
                                <td><?= (int) $g['entry_id'] ?: ''; ?></td>
                                <td><?= $h($g['number']); ?></td>
                                <td class="text-nowrap"><?= $h($g['date']); ?></td>
                                <td><?= $h($g['type']); ?></td>
                                <td><?= $h($g['narration']); ?></td>
                                <td class="num <?= $amt_cls($g['amount']); ?>"><?= $fmt($g['amount']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    $(document).ready(function () {
        function applyGapFilter($cb) {
            var $table = $($cb.data('target'));
            var only = $cb.is(':checked');
            $table.find('tbody > tr.no-gap').toggle(!only);
            if (only) {
                $table.find('tbody > tr.no-gap-child').hide();
            }
        }
        $('.dbg-only-gap').each(function () { applyGapFilter($(this)); })
            .on('change', function () { applyGapFilter($(this)); });

        $('#dbg-invoices').on('click', 'tr.dbg-toggle', function () {
            $($(this).data('alloc')).toggle();
        });
    });
</script>
