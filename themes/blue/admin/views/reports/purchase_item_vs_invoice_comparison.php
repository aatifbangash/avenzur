<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .cmp-ok { color: #27ae60; font-weight: bold; }
    .cmp-bad { color: #c0392b; font-weight: bold; }
    .cmp-table th, .cmp-table td { font-size: 12px; vertical-align: middle !important; }
    .cmp-table tr.diff-row > td { background: #fff3cd !important; }
    .cmp-table tr.missing-item > td { background: #fdecea !important; }
    .cmp-table tr.missing-invoice > td { background: #eaf2ff !important; }
    .cmp-notes li { margin-bottom: 4px; }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-exchange"></i>Purchase Per Item vs Purchase Per Invoice Comparison</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm'];
                echo admin_form_open('reports/purchase_item_vs_invoice_comparison', $attrib);
                ?>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= lang('From Date', 'from_date'); ?>
                            <?= form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="from_date"'); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= lang('To Date', 'to_date'); ?>
                            <?= form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="to_date"'); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= lang('supplier', 'supplier_id'); ?>
                            <select name="supplier_id" id="supplier_id" class="form-control select2" style="width:100%;">
                                <option value=""><?= lang('all'); ?></option>
                                <?php if (!empty($suppliers)): ?>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= (int) $supplier->id; ?>" <?= (!empty($supplier_id) && (int) $supplier_id === (int) $supplier->id) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($supplier->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <?php $this->load->view($this->theme . 'reports/partials/warehouse_filter_field', ['wh_col' => 'col-md-3']); ?>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary" style="margin-top:5px;">
                            <i class="fa fa-search"></i> <?= lang('Load Report'); ?>
                        </button>
                    </div>
                </div>
                <?php echo form_close(); ?>
                <hr />
            </div>
        </div>

        <?php if (!empty($comparison)): ?>
            <?php
            $item = $comparison['item_summary'];
            $inv = $comparison['invoice_summary'];
            $cmp = $comparison['invoice_comparable'];
            $diff = $comparison['summary_diff'];
            $fmt = function ($n) {
                return number_format((float) $n, 2, '.', ',');
            };
            $cls = function ($n) {
                return abs((float) $n) > 0.009 ? 'cmp-bad' : 'cmp-ok';
            };
            ?>

            <div class="alert alert-info">
                <strong>How to read this:</strong>
                Document amounts are compared using absolute values (returns flipped to positive).
                Summary “Comparable” uses Item net (purchases − returns) vs Invoice (purchase rows − return rows).
                Payment rows exist only in Purchase Per Invoice and are excluded from amount matching.
            </div>

            <?php if (!empty($comparison['notes'])): ?>
                <div class="well well-sm">
                    <strong>Known structural differences</strong>
                    <ul class="cmp-notes" style="margin-bottom:0;">
                        <?php foreach ($comparison['notes'] as $note): ?>
                            <li><?= htmlspecialchars($note); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <h3>1) Summary Totals</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-condensed cmp-table">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th class="text-right">Purchase Per Item</th>
                            <th class="text-right">Purchase Per Invoice (raw footer)</th>
                            <th class="text-right">Invoice Comparable (Purch − Return)</th>
                            <th class="text-right">Diff (Item − Comparable)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Purchase docs</td>
                            <td class="text-right"><?= (int) $item['purchase_count']; ?></td>
                            <td class="text-right"><?= (int) $inv['purchase_count']; ?></td>
                            <td class="text-right">—</td>
                            <td class="text-right <?= $cls($item['purchase_count'] - $inv['purchase_count']); ?>">
                                <?= (int) ($item['purchase_count'] - $inv['purchase_count']); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Return docs</td>
                            <td class="text-right"><?= (int) $item['return_count']; ?></td>
                            <td class="text-right"><?= (int) $inv['return_count']; ?></td>
                            <td class="text-right">—</td>
                            <td class="text-right <?= $cls($item['return_count'] - $inv['return_count']); ?>">
                                <?= (int) ($item['return_count'] - $inv['return_count']); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Payment rows (invoice only)</td>
                            <td class="text-right">0</td>
                            <td class="text-right"><?= (int) $inv['payment_count']; ?> (<?= $fmt($inv['payment']); ?>)</td>
                            <td class="text-right">ignored</td>
                            <td class="text-right">—</td>
                        </tr>
                        <tr>
                            <td>Purchase amount</td>
                            <td class="text-right"><?= $fmt($item['purchase']); ?></td>
                            <td class="text-right"><?= $fmt($inv['purchase']); ?></td>
                            <td class="text-right"><?= $fmt($cmp['purchase']); ?></td>
                            <td class="text-right <?= $cls($diff['purchase']); ?>"><?= $fmt($diff['purchase']); ?></td>
                        </tr>
                        <tr>
                            <td>VAT</td>
                            <td class="text-right"><?= $fmt($item['vat']); ?></td>
                            <td class="text-right"><?= $fmt($inv['vat']); ?></td>
                            <td class="text-right"><?= $fmt($cmp['vat']); ?></td>
                            <td class="text-right <?= $cls($diff['vat']); ?>"><?= $fmt($diff['vat']); ?></td>
                        </tr>
                        <tr>
                            <td>Payable</td>
                            <td class="text-right"><?= $fmt($item['payable']); ?></td>
                            <td class="text-right"><?= $fmt($inv['payable']); ?></td>
                            <td class="text-right"><?= $fmt($cmp['payable']); ?></td>
                            <td class="text-right <?= $cls($diff['payable']); ?>"><?= $fmt($diff['payable']); ?></td>
                        </tr>
                        <tr>
                            <td>Matched docs OK</td>
                            <td colspan="4"><span class="cmp-ok"><?= (int) $comparison['matched_ok']; ?></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <?php
            $render_rows = function ($rows, $row_class = '') use ($fmt) {
                if (empty($rows)) {
                    echo '<tr><td colspan="14" class="text-center">None</td></tr>';
                    return;
                }
                foreach ($rows as $r) {
                    ?>
                    <tr class="<?= $row_class; ?>">
                        <td><?= htmlspecialchars($r['type']); ?></td>
                        <td><?= htmlspecialchars($r['doc_id']); ?></td>
                        <td><?= htmlspecialchars($r['linked_purchase_id']); ?></td>
                        <td><?= htmlspecialchars($r['date']); ?></td>
                        <td><?= htmlspecialchars(trim(($r['supplier_no'] ?? '') . ' ' . ($r['supplier_name'] ?? ''))); ?></td>
                        <td class="text-center"><?= !empty($r['in_item']) ? 'Yes' : 'No'; ?><?= !empty($r['item_lines']) ? ' (' . (int) $r['item_lines'] . ' lines)' : ''; ?></td>
                        <td class="text-center"><?= !empty($r['in_invoice']) ? 'Yes' : 'No'; ?></td>
                        <td class="text-right"><?= $fmt($r['item_purchase']); ?></td>
                        <td class="text-right"><?= $fmt($r['invoice_purchase']); ?></td>
                        <td class="text-right"><?= $fmt($r['diff_purchase']); ?></td>
                        <td class="text-right"><?= $fmt($r['diff_vat']); ?></td>
                        <td class="text-right"><?= $fmt($r['diff_payable']); ?></td>
                        <td><?= htmlspecialchars($r['status'] ?? ''); ?></td>
                        <td><?= htmlspecialchars(implode('; ', $r['reasons'] ?? [])); ?></td>
                    </tr>
                    <?php
                }
            };
            ?>

            <h3>2) Amount Mismatches (same document, different totals)
                <small class="text-muted">(<?= count($comparison['amount_mismatches']); ?>)</small>
            </h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-condensed cmp-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Doc ID</th>
                            <th>Linked Purchase</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>In Item</th>
                            <th>In Invoice</th>
                            <th>Item Purchase</th>
                            <th>Invoice Purchase</th>
                            <th>Diff Purchase</th>
                            <th>Diff VAT</th>
                            <th>Diff Payable</th>
                            <th>Status</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $render_rows($comparison['amount_mismatches'], 'diff-row'); ?>
                    </tbody>
                </table>
            </div>

            <h3>3) Only in Purchase Per Item
                <small class="text-muted">(<?= count($comparison['only_in_item']); ?>)</small>
            </h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-condensed cmp-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Doc ID</th>
                            <th>Linked Purchase</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>In Item</th>
                            <th>In Invoice</th>
                            <th>Item Purchase</th>
                            <th>Invoice Purchase</th>
                            <th>Diff Purchase</th>
                            <th>Diff VAT</th>
                            <th>Diff Payable</th>
                            <th>Status</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $render_rows($comparison['only_in_item'], 'missing-invoice'); ?>
                    </tbody>
                </table>
            </div>

            <h3>4) Only in Purchase Per Invoice
                <small class="text-muted">(<?= count($comparison['only_in_invoice']); ?>)</small>
            </h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-condensed cmp-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Doc ID</th>
                            <th>Linked Purchase</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>In Item</th>
                            <th>In Invoice</th>
                            <th>Item Purchase</th>
                            <th>Invoice Purchase</th>
                            <th>Diff Purchase</th>
                            <th>Diff VAT</th>
                            <th>Diff Payable</th>
                            <th>Status</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $render_rows($comparison['only_in_invoice'], 'missing-item'); ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <div class="alert alert-warning">
                Select date range (and optional supplier/warehouse), then load the report to find where Purchase Per Item and Purchase Per Invoice diverge.
            </div>
        <?php endif; ?>
    </div>
</div>
