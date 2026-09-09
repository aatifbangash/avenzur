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
        <h2 class="blue"><i class="fa-fw fa fa-exchange"></i>Customer TB vs Unpaid AR Comparison</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="muted-note">
                    Compares <strong>Receivable Trial Balance EB Debit</strong>
                    (<a href="<?= admin_url('reports/customers_trial_balance'); ?>" target="_blank">customers_trial_balance</a>)
                    against <strong>Unpaid AR outstanding</strong>
                    (<a href="<?= admin_url('reports/unpaid_invoices_ar'); ?>" target="_blank">unpaid_invoices_ar</a>)
                    and groups customers by the likely mismatch area.
                </p>
                <?php
                $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'id' => 'searchForm'];
                echo admin_form_open_multipart('reports/customer_tb_vs_unpaid_ar_comparison', $attrib);
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
                            <?= lang('customer_type', 'customer_rent_type'); ?>
                            <select name="customer_rent_type" id="customer_rent_type" class="form-control select" style="width:100%;">
                                <option value="non_rental" <?= (($customer_rent_type ?? 'non_rental') === 'non_rental') ? 'selected' : ''; ?>><?= lang('non_rental_customers'); ?></option>
                                <option value="rental" <?= (($customer_rent_type ?? '') === 'rental') ? 'selected' : ''; ?>><?= lang('rental_customers'); ?></option>
                                <option value="all" <?= (($customer_rent_type ?? '') === 'all') ? 'selected' : ''; ?>><?= lang('all_customers'); ?></option>
                            </select>
                        </div>
                    </div>
                    <?php $this->load->view($this->theme . 'reports/partials/warehouse_filter_field', [
                        'wh_col' => 'col-md-3',
                        'wh_val' => ($warehouse_id ?: ''),
                    ]); ?>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= lang('Customers', 'customers'); ?>
                            <select name="customer_ids[]" id="customer_ids" class="form-control select2" multiple="multiple" data-placeholder="<?= lang('Select Customers'); ?>" style="width:100%;">
                                <?php foreach (($customers ?? []) as $customer): ?>
                                    <option value="<?= (int) $customer->id; ?>"
                                        <?= (!empty($selected_customers) && in_array($customer->id, $selected_customers)) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($customer->company ?? $customer->name, ENT_QUOTES, 'UTF-8'); ?>
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
                <strong>Grand TB EB Debit:</strong> <?= $fmt($summary['grand_tb_debit']); ?>
                &nbsp;|&nbsp; <strong>Grand Unpaid AR:</strong> <?= $fmt($summary['grand_unpaid']); ?>
                &nbsp;|&nbsp; <strong>Difference (TB − Unpaid):</strong>
                <span class="<?= abs($summary['grand_diff']) >= 0.01 ? 'diff-pos' : 'diff-neg'; ?>">
                    <?= $fmt($summary['grand_diff']); ?>
                </span><br>
                <strong>Customers with mismatch:</strong> <?= (int) $summary['mismatch_count']; ?><br>
                <?php if (empty($summary['include_memos_in_unpaid'])): ?>
                    <span class="text-warning">
                        Note: for a single branch warehouse, Unpaid side is sales only.
                        Customer service invoices are shown under “Memos excluded”. Prefer
                        <strong>All Local Warehouses</strong> for the closest TB match.
                    </span>
                <?php else: ?>
                    <span class="text-success">
                        Scope: <?= empty($warehouse_id) ? 'All local warehouses' : 'selected warehouse'; ?>.
                        Customer service invoices are included in Unpaid AR totals.
                    </span>
                <?php endif; ?>
            </div>

            <?php if (empty($grouped)): ?>
                <div class="alert alert-success">No material mismatches found for the selected filters.</div>
            <?php else: ?>
                <?php foreach ($grouped as $cat): ?>
                    <div class="tb-unpaid-cat prio-<?= htmlspecialchars($cat['priority'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="cat-head">
                            <h4><?= htmlspecialchars($cat['label'], ENT_QUOTES, 'UTF-8'); ?>
                                <small>(<?= count($cat['customers']); ?> customers, abs gap <?= $fmt($cat['sum_abs_diff']); ?>)</small>
                            </h4>
                            <p><?= htmlspecialchars($cat['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <p><strong>Recommended:</strong> <?= htmlspecialchars($cat['action'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-condensed table-hover" style="font-size:12px; margin:0;">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Customer</th>
                                        <th class="text-right">TB EB Debit</th>
                                        <th class="text-right">Unpaid Sales</th>
                                        <th class="text-right">Service Inv.</th>
                                        <th class="text-right">Memos excluded</th>
                                        <th class="text-right">Unpaid Total</th>
                                        <th class="text-right">Diff (TB−Unpaid)</th>
                                        <th class="text-right">Unsettled Returns</th>
                                        <th class="text-right">Unsettled Credit Memos</th>
                                        <th class="text-right">GL Debits w/o SID</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cat['customers'] as $row): ?>
                                        <?php
                                        $stmt_url = admin_url('reports/customer_statement')
                                            . '?customer=' . (int) $row['customer_id']
                                            . '&from_date=' . urlencode($start_date ?? '')
                                            . '&to_date=' . urlencode($end_date ?? '');
                                        $unpaid_url = admin_url('reports/unpaid_invoices_ar')
                                            . '?at_date=' . urlencode($end_date ?? '')
                                            . '&party_id=' . (int) $row['customer_id']
                                            . '&customer_rent_type=' . urlencode($customer_rent_type ?? 'non_rental');
                                        if (!empty($warehouse_id)) {
                                            $stmt_url .= '&warehouse_id=' . (int) $warehouse_id;
                                            $unpaid_url .= '&warehouse_id=' . (int) $warehouse_id;
                                        } else {
                                            $stmt_url .= '&all=1';
                                            $unpaid_url .= '&all=1&warehouse_id=';
                                        }
                                        $diff_cls = $row['difference'] > 0.01 ? 'diff-pos' : ($row['difference'] < -0.01 ? 'diff-neg' : '');
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['sequence_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-right"><?= $fmt($row['tb_eb_debit']); ?></td>
                                            <td class="text-right">
                                                <?= $fmt($row['unpaid_sales']); ?>
                                                <small class="text-muted">(<?= (int) $row['open_sale_count']; ?>)</small>
                                            </td>
                                            <td class="text-right"><?= $fmt($row['unpaid_service']); ?></td>
                                            <td class="text-right"><?= $fmt($row['memos_excluded_from_unpaid']); ?></td>
                                            <td class="text-right"><?= $fmt($row['unpaid_total']); ?></td>
                                            <td class="text-right <?= $diff_cls; ?>"><?= $fmt($row['difference']); ?></td>
                                            <td class="text-right"><?= $fmt($row['unsettled_returns']); ?></td>
                                            <td class="text-right"><?= $fmt($row['unsettled_credit_memos']); ?></td>
                                            <td class="text-right"><?= $fmt($row['ledger_debits_no_sid']); ?></td>
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
            $('#customer_ids').select2({ allowClear: true });
        }
    });
</script>
