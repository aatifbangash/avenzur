<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .tb-unpaid-cat { margin: 22px 0 28px; border: 1px solid #ddd; border-radius: 4px; overflow: hidden; }
    .tb-unpaid-cat .cat-head { padding: 12px 14px; background: #f5f7fa; border-bottom: 1px solid #ddd; }
    .tb-unpaid-cat .cat-head h4 { margin: 0 0 4px; font-size: 14px; }
    .tb-unpaid-cat .cat-head p { margin: 2px 0; font-size: 12px; color: #555; }
    .tb-unpaid-cat.prio-high .cat-head { background: #fdecea; }
    .tb-unpaid-cat.prio-medium .cat-head { background: #fff8e6; }
    .tb-unpaid-cat.prio-low .cat-head { background: #eafaf1; }
    .diff-pos { color: #c0392b; font-weight: bold; }
    .diff-neg { color: #27ae60; font-weight: bold; }
    .muted-note { font-size: 12px; color: #666; }
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-exchange"></i>Supplier TB vs Unpaid AP Comparison</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="muted-note">
                    Compares <strong>Payable Trial Balance EB Credit</strong>
                    (<a href="<?= admin_url('reports/suppliers_trial_balance'); ?>" target="_blank">suppliers_trial_balance</a>)
                    against <strong>Unpaid AP outstanding</strong>
                    (<a href="<?= admin_url('reports/unpaid_invoices_ap'); ?>" target="_blank">unpaid_invoices_ap</a>)
                    and groups suppliers by the likely mismatch area.
                </p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm'];
                echo admin_form_open_multipart('reports/supplier_tb_vs_unpaid_ap_comparison', $attrib);
                ?>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= lang('From Date', 'from_date'); ?>
                            <?= form_input('from_date', ($start_date ?? ''), 'class="form-control input-tip date" id="fromdate"'); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= lang('To Date', 'to_date'); ?>
                            <?= form_input('to_date', ($end_date ?? ''), 'class="form-control input-tip date" id="todate"'); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <?= lang('supplier_type', 'supplier_trade_type'); ?>
                            <select name="supplier_trade_type" id="supplier_trade_type" class="form-control select" style="width:100%;">
                                <option value="trade" <?= (($supplier_trade_type ?? 'trade') === 'trade') ? 'selected' : ''; ?>><?= lang('trade_suppliers'); ?></option>
                                <option value="non_trade" <?= (($supplier_trade_type ?? '') === 'non_trade') ? 'selected' : ''; ?>><?= lang('non_trade_suppliers'); ?></option>
                                <option value="all" <?= (($supplier_trade_type ?? '') === 'all') ? 'selected' : ''; ?>><?= lang('all_suppliers'); ?></option>
                            </select>
                        </div>
                    </div>
                    <?php $this->load->view($this->theme . 'reports/partials/warehouse_filter_field', ['wh_col' => 'col-md-3']); ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Suppliers', 'suppliers'); ?>
                            <select name="supplier_ids[]" id="supplier_ids" class="form-control select2" multiple="multiple" data-placeholder="<?= lang('Select Suppliers'); ?>" style="width:100%;">
                                <?php foreach (($suppliers ?? []) as $supplier): ?>
                                    <option value="<?= (int) $supplier->id; ?>"
                                        <?= (!empty($selected_suppliers) && in_array($supplier->id, $selected_suppliers)) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($supplier->company ?? $supplier->name, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <button type="submit" style="margin-top: 28px;" class="btn btn-primary" id="load_report"><?= lang('Load Report') ?></button>
                        </div>
                    </div>
                </div>
                <?php echo form_close(); ?>
                <hr />
            </div>
        </div>

        <?php if (!empty($comparison_result)): ?>
            <?php
            $summary = $comparison_result['summary'];
            $grouped = $comparison_result['grouped'];
            $fmt = function ($n) {
                return number_format((float) $n, 2, '.', ',');
            };
            ?>

            <div class="alert alert-info">
                <strong>TB period:</strong> <?= htmlspecialchars($start_date ?? '', ENT_QUOTES, 'UTF-8'); ?>
                → <?= htmlspecialchars($end_date ?? '', ENT_QUOTES, 'UTF-8'); ?>
                &nbsp;|&nbsp; <strong>Unpaid as-of:</strong> <?= htmlspecialchars($end_date ?? '', ENT_QUOTES, 'UTF-8'); ?><br>
                <strong>Grand TB EB Credit:</strong> <?= $fmt($summary['grand_tb_credit']); ?>
                &nbsp;|&nbsp; <strong>Grand Unpaid AP:</strong> <?= $fmt($summary['grand_unpaid']); ?>
                &nbsp;|&nbsp; <strong>Difference (TB − Unpaid):</strong>
                <span class="<?= abs($summary['grand_diff']) >= 0.01 ? 'diff-pos' : 'diff-neg'; ?>">
                    <?= $fmt($summary['grand_diff']); ?>
                </span><br>
                <strong>Suppliers with mismatch:</strong> <?= (int) $summary['mismatch_count']; ?><br>
                <?php if (empty($summary['include_memos_in_unpaid'])): ?>
                    <span class="text-warning">
                        Note: Unpaid AP only includes service invoices / credit memos when warehouse = HQ (32).
                        Those memo balances are shown in the “Memos excluded” column / category when they drive the gap.
                    </span>
                <?php else: ?>
                    <span class="text-success">Service invoices &amp; credit memos are included in Unpaid AP totals (warehouse HQ).</span>
                <?php endif; ?>
            </div>

            <?php if (empty($grouped)): ?>
                <div class="alert alert-success">No material mismatches found for the selected filters.</div>
            <?php else: ?>
                <?php foreach ($grouped as $cat): ?>
                    <div class="tb-unpaid-cat prio-<?= htmlspecialchars($cat['priority'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="cat-head">
                            <h4><?= htmlspecialchars($cat['label'], ENT_QUOTES, 'UTF-8'); ?>
                                <small>(<?= count($cat['suppliers']); ?> suppliers, abs gap <?= $fmt($cat['sum_abs_diff']); ?>)</small>
                            </h4>
                            <p><?= htmlspecialchars($cat['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Recommended:</strong> <?= htmlspecialchars($cat['action'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-condensed table-hover" style="font-size:12px; margin:0;">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Supplier</th>
                                        <th class="text-right">TB EB Credit</th>
                                        <th class="text-right">Unpaid Purchases</th>
                                        <th class="text-right">Service Inv.</th>
                                        <th class="text-right">Credit Memos</th>
                                        <th class="text-right">Memos excluded</th>
                                        <th class="text-right">Unpaid Total</th>
                                        <th class="text-right">Diff (TB−Unpaid)</th>
                                        <th class="text-right">Unsettled Returns</th>
                                        <th class="text-right">Unsettled Debit Memos</th>
                                        <th class="text-right">GL Credits w/o PID</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cat['suppliers'] as $row): ?>
                                        <?php
                                        $stmt_url = admin_url('reports/supplier_statement')
                                            . '?supplier=' . (int) $row['supplier_id']
                                            . '&from_date=' . urlencode($start_date ?? '')
                                            . '&to_date=' . urlencode($end_date ?? '');
                                        $unpaid_url = admin_url('reports/unpaid_invoices_ap')
                                            . '?at_date=' . urlencode($end_date ?? '')
                                            . '&party_id=' . (int) $row['supplier_id']
                                            . '&supplier_trade_type=' . urlencode($supplier_trade_type ?? 'trade');
                                        if (!empty($warehouse_id)) {
                                            $stmt_url .= '&warehouse_id=' . (int) $warehouse_id;
                                            $unpaid_url .= '&warehouse_id=' . (int) $warehouse_id;
                                        }
                                        $diff_cls = $row['difference'] > 0.01 ? 'diff-pos' : ($row['difference'] < -0.01 ? 'diff-neg' : '');
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['sequence_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-right"><?= $fmt($row['tb_eb_credit']); ?></td>
                                            <td class="text-right">
                                                <?= $fmt($row['unpaid_purchases']); ?>
                                                <small class="text-muted">(<?= (int) $row['open_purchase_count']; ?>)</small>
                                            </td>
                                            <td class="text-right"><?= $fmt($row['unpaid_service']); ?></td>
                                            <td class="text-right"><?= $fmt($row['unpaid_credit_memos']); ?></td>
                                            <td class="text-right"><?= $fmt($row['memos_excluded_from_unpaid']); ?></td>
                                            <td class="text-right"><?= $fmt($row['unpaid_total']); ?></td>
                                            <td class="text-right <?= $diff_cls; ?>"><?= $fmt($row['difference']); ?></td>
                                            <td class="text-right"><?= $fmt($row['unsettled_returns']); ?></td>
                                            <td class="text-right"><?= $fmt($row['unsettled_debit_memos']); ?></td>
                                            <td class="text-right"><?= $fmt($row['ledger_credits_no_pid']); ?></td>
                                            <td class="text-nowrap">
                                                <a class="btn btn-xs btn-default" href="<?= $stmt_url; ?>" target="_blank">Statement</a>
                                                <a class="btn btn-xs btn-default" href="<?= $unpaid_url; ?>" target="_blank">Unpaid</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<script>
    $(document).ready(function () {
        if ($.fn.select2) {
            $('#supplier_ids').select2({ allowClear: true });
        }
    });
</script>
