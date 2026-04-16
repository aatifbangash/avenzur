<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename) {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {sheet: 'Sheet 1'});
        XLSX.writeFile(wb, filename || 'vat_report.xlsx');
    }

    $(document).ready(function () {
        $('#start_date, #end_date').datetimepicker({
            format: site.dateFormats.js_date,
            fontAwesome: true,
            language: 'sma',
            weekStart: 1,
            todayBtn: 1,
            autoclose: 1,
            todayHighlight: 1,
            startView: 2,
            minView: 2,
            forceParse: 0
        });

        $(document).on('click', '.type-tab', function () {
            $('#type').val($(this).data('type'));
            $('#filterForm').submit();
        });
    });
</script>

<style>
    .summary-box       { padding: 14px 18px; border-radius: 6px; margin-bottom: 10px; color: #fff; }
    .summary-box .label-sm { font-size: 12px; opacity: .85; margin-bottom: 2px; }
    .summary-box .value-lg { font-size: 20px; font-weight: 700; }
    .row-sales-invoice  { background-color: #eaf4fb !important; }
    .row-sales-return   { background-color: #fdecea !important; }
    .row-purchase-inv   { background-color: #fffbe6 !important; }
    .row-purchase-return{ background-color: #fde8f0 !important; }
    .section-heading    { background: #f0f4f8; font-weight: 700; font-size: 13px;
                          padding: 6px 10px; border-left: 4px solid #3498db;
                          margin: 18px 0 8px; border-radius: 3px; }
    .section-heading.purchases { border-left-color: #e67e22; }
    .vat-positive { color: #27ae60; font-weight: 700; }
    .vat-negative { color: #e74c3c; font-weight: 700; }
    .net-position-box { border-radius: 6px; padding: 12px 18px; color: #fff; font-weight: 700; font-size: 16px; }
    .row-memo-service   { background-color: #eafaf1 !important; }
    .row-memo-petty     { background-color: #f5eaff !important; }
    .row-memo-credit    { background-color: #ffe8e8 !important; }
    .row-memo-debit     { background-color: #e8eeff !important; }
    .row-memo-general   { background-color: #f5f5f5 !important; }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-percent"></i>
            VAT Report
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li>
                    <a href="javascript:void(0);"
                       onclick="exportTableToExcel('vatReportTable', 'VAT_Report_<?= date('Y-m-d') ?>.xlsx')"
                       class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li>
                    <?php $export_params = array_merge($_GET, ['export_excel' => 1]); ?>
                    <a href="<?= admin_url('reports/vat_report?' . http_build_query($export_params)) ?>"
                       class="tip" title="Export (Server-side Excel)">
                        <i class="icon fa fa-download"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="box-content">

        <!-- Type Tabs -->
        <ul class="nav nav-tabs" style="margin-bottom: 15px;">
            <li class="<?= ($type === 'all') ? 'active' : '' ?>">
                <a href="javascript:void(0);" class="type-tab" data-type="all">
                    <i class="fa fa-list"></i> All (Sales &amp; Purchases)
                </a>
            </li>
            <li class="<?= ($type === 'sales') ? 'active' : '' ?>">
                <a href="javascript:void(0);" class="type-tab" data-type="sales">
                    <i class="fa fa-arrow-circle-right text-success"></i> Output VAT (Sales)
                </a>
            </li>
            <li class="<?= ($type === 'purchases') ? 'active' : '' ?>">
                <a href="javascript:void(0);" class="type-tab" data-type="purchases">
                    <i class="fa fa-arrow-circle-left text-warning"></i> Input VAT (Purchases)
                </a>
            </li>
        </ul>

        <!-- Filter Form -->
        <?php
        $attrib = ['id' => 'filterForm', 'method' => 'get', 'role' => 'form'];
        echo admin_form_open_multipart('reports/vat_report', $attrib);
        ?>
        <input type="hidden" name="type" id="type" value="<?= htmlspecialchars($type) ?>">

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control" name="start_date" id="start_date"
                                   value="<?= htmlspecialchars($start_date ?? '') ?>"
                                   placeholder="<?= lang('start_date') ?>" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control" name="end_date" id="end_date"
                                   value="<?= htmlspecialchars($end_date ?? '') ?>"
                                   placeholder="<?= lang('end_date') ?>" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="warehouse_id" class="form-control" style="height:34px;">
                            <option value="0"><?= lang('all_warehouses') ?: '-- All Warehouses --' ?></option>
                            <?php foreach ($warehouses as $wh): ?>
                            <option value="<?= $wh->id ?>" <?= ((int)($warehouse_id ?? 0) === (int)$wh->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($wh->name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm" style="height:34px;">
                            <i class="fa fa-search"></i> <?= lang('search') ?>
                        </button>
                        <a href="<?= admin_url('reports/vat_report') ?>" class="btn btn-default btn-sm" style="height:34px;">
                            <i class="fa fa-refresh"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        </form>

        <?php
        $summary      = $summary      ?? [];
        $sales_rows   = $sales_rows   ?? [];
        $purchase_rows = $purchase_rows ?? [];

        $s_net   = $summary['sales_net']      ?? 0;
        $s_vat   = $summary['sales_vat']      ?? 0;
        $s_gross = $summary['sales_gross']    ?? 0;
        $p_net   = $summary['purchase_net']   ?? 0;
        $p_vat   = $summary['purchase_vat']   ?? 0;
        $p_gross = $summary['purchase_gross'] ?? 0;
        $net_pos = $summary['net_vat_position'] ?? ($s_vat - $p_vat);
        ?>

        <!-- Summary Boxes -->
        <div class="row" style="margin-top:15px;">
            <?php if (in_array($type, ['all', 'sales'])): ?>
            <div class="col-md-3">
                <div class="summary-box" style="background:#2980b9;">
                    <div class="label-sm"><i class="fa fa-arrow-circle-right"></i> Output VAT (Sales)</div>
                    <div class="value-lg"><?= $this->sma->formatMoney($s_vat) ?></div>
                    <div style="font-size:11px; margin-top:4px; opacity:.8;">
                        Net: <?= $this->sma->formatMoney($s_net) ?> &nbsp;|&nbsp;
                        Gross: <?= $this->sma->formatMoney($s_gross) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if (in_array($type, ['all', 'purchases'])): ?>
            <div class="col-md-3">
                <div class="summary-box" style="background:#e67e22;">
                    <div class="label-sm"><i class="fa fa-arrow-circle-left"></i> Input VAT (Purchases)</div>
                    <div class="value-lg"><?= $this->sma->formatMoney($p_vat) ?></div>
                    <div style="font-size:11px; margin-top:4px; opacity:.8;">
                        Net: <?= $this->sma->formatMoney($p_net) ?> &nbsp;|&nbsp;
                        Gross: <?= $this->sma->formatMoney($p_gross) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($type === 'all'): ?>
            <div class="col-md-3">
                <div class="summary-box" style="background:<?= ($net_pos >= 0) ? '#27ae60' : '#c0392b' ?>;">
                    <div class="label-sm"><i class="fa fa-balance-scale"></i> Net VAT Position (Output − Input)</div>
                    <div class="value-lg"><?= $this->sma->formatMoney($net_pos) ?></div>
                    <div style="font-size:11px; margin-top:4px; opacity:.8;">
                        <?= ($net_pos >= 0) ? 'VAT payable to authority' : 'VAT refund due' ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-md-3">
                <div class="summary-box" style="background:#8e44ad;">
                    <div class="label-sm"><i class="fa fa-file-text-o"></i> Total Transactions</div>
                    <div class="value-lg"><?= count($sales_rows) + count($purchase_rows) ?></div>
                    <div style="font-size:11px; margin-top:4px; opacity:.8;">
                        Sales: <?= count($sales_rows) ?> &nbsp;|&nbsp; Purchases: <?= count($purchase_rows) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $type_labels = [
            'sale'            => 'Sales Invoice',
            'returnCustomer'  => 'Sales Return',
            'purchase'        => 'Purchase Invoice',
            'returnSupplier'  => 'Purchase Return',
            'serviceinvoice'  => 'Service Invoice',
            'pettycash'       => 'Petty Cash',
            'creditmemo'      => 'Credit Memo',
            'debitmemo'       => 'Debit Memo',
            'memo'            => 'Memo',
        ];
        $type_row_class = [
            'sale'            => 'row-sales-invoice',
            'returnCustomer'  => 'row-sales-return',
            'purchase'        => 'row-purchase-inv',
            'returnSupplier'  => 'row-purchase-return',
            'serviceinvoice'  => 'row-memo-service',
            'pettycash'       => 'row-memo-petty',
            'creditmemo'      => 'row-memo-credit',
            'debitmemo'       => 'row-memo-debit',
            'memo'            => 'row-memo-general',
        ];
        $type_badge = [
            'sale'            => 'primary',
            'returnCustomer'  => 'danger',
            'purchase'        => 'warning',
            'returnSupplier'  => 'danger',
            'serviceinvoice'  => 'success',
            'pettycash'       => 'info',
            'creditmemo'      => 'danger',
            'debitmemo'       => 'warning',
            'memo'            => 'default',
        ];

        $has_sales     = !empty($sales_rows);
        $has_purchases = !empty($purchase_rows);
        ?>

        <!-- Unified Table for Excel export -->
        <table id="vatReportTable" style="display:none;">
            <thead>
                <tr>
                    <th>#</th><th>Date</th><th>Ref#</th><th>Type</th>
                    <th>Party</th><th>VAT No</th><th>Warehouse</th>
                    <th>Net (ex-VAT)</th><th>VAT Amount</th><th>Grand Total</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $idx = 0;
            foreach ($sales_rows as $r):
                $idx++;
            ?>
                <tr>
                    <td><?= $idx ?></td>
                    <td><?= date('d-M-Y', strtotime($r->trans_date)) ?></td>
                    <td><?= htmlspecialchars($r->reference_no) ?></td>
                    <td><?= $type_labels[$r->trans_type] ?? $r->trans_type ?></td>
                    <td><?= htmlspecialchars($r->party_name ?? '') ?></td>
                    <td><?= htmlspecialchars($r->party_vat_no ?? '') ?></td>
                    <td><?= htmlspecialchars($r->warehouse ?? '') ?></td>
                    <td><?= round((float)$r->total_net, 2) ?></td>
                    <td><?= round((float)$r->total_tax, 2) ?></td>
                    <td><?= round((float)$r->grand_total, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($purchase_rows as $r):
                $idx++;
            ?>
                <tr>
                    <td><?= $idx ?></td>
                    <td><?= date('d-M-Y', strtotime($r->trans_date)) ?></td>
                    <td><?= htmlspecialchars($r->reference_no) ?></td>
                    <td><?= $type_labels[$r->trans_type] ?? $r->trans_type ?></td>
                    <td><?= htmlspecialchars($r->party_name ?? '') ?></td>
                    <td><?= htmlspecialchars($r->party_vat_no ?? '') ?></td>
                    <td><?= htmlspecialchars($r->warehouse ?? '') ?></td>
                    <td><?= round((float)$r->total_net, 2) ?></td>
                    <td><?= round((float)$r->total_tax, 2) ?></td>
                    <td><?= round((float)$r->grand_total, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ── SALES SECTION ──────────────────────────────────────── -->
        <?php if (in_array($type, ['all', 'sales'])): ?>
        <div class="section-heading">
            <i class="fa fa-arrow-circle-right" style="color:#2980b9;"></i>
            &nbsp;Output VAT — Sales &amp; Sales Returns
            <span class="pull-right" style="font-weight:400; font-size:12px;">
                <?= count($sales_rows) ?> transactions &nbsp;|&nbsp;
                VAT: <strong><?= $this->sma->formatMoney($s_vat) ?></strong>
            </span>
        </div>
        <?php if ($has_sales): ?>

        <div class="controls table-controls" style="font-size: 12px !important;">
            <table class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                <thead>
                    <tr style="background:#d6eaf8;">
                        <th>#</th>
                        <th><?= lang('date') ?></th>
                        <th><?= lang('reference_no') ?></th>
                        <th>Type</th>
                        <th><?= lang('customer') ?></th>
                        <th>VAT No</th>
                        <th><?= lang('warehouse') ?></th>
                        <th class="text-right">Net Amount<br><small class="muted">(ex-VAT)</small></th>
                        <th class="text-right">VAT Amount</th>
                        <th class="text-right">Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (!$has_sales): ?>
                    <tr><td colspan="10" class="text-center muted">No sales records found for the selected period.</td></tr>
                <?php else:
                    $s_sub_net   = 0; $s_sub_vat = 0; $s_sub_gross = 0;
                    $i = 0;
                    foreach ($sales_rows as $r):
                        $i++;
                        $net   = (float)$r->total_net;
                        $vat   = (float)$r->total_tax;
                        $gross = (float)$r->grand_total;
                        $s_sub_net   += $net;
                        $s_sub_vat   += $vat;
                        $s_sub_gross += $gross;
                        $rclass  = $type_row_class[$r->trans_type] ?? '';
                        $badge   = $type_badge[$r->trans_type] ?? 'default';
                        $tlabel  = $type_labels[$r->trans_type] ?? $r->trans_type;
                ?>
                    <tr class="<?= $rclass ?>">
                        <td><?= $i ?></td>
                        <td><?= date('d-M-Y', strtotime($r->trans_date)) ?></td>
                        <td><?= htmlspecialchars($r->reference_no ?? '') ?></td>
                        <td>
                            <span class="label label-<?= $badge ?>"><?= $tlabel ?></span>
                            <?php if (!empty($r->entry_type ?? '')): ?>
                                <span class="label label-<?= ($r->entry_type === 'D') ? 'info' : 'warning' ?>" style="font-size:10px; margin-left:3px;"><?= htmlspecialchars($r->entry_type) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($r->party_name ?? '') ?></td>
                        <td style="font-size:11px;"><?= htmlspecialchars($r->party_vat_no ?? '') ?></td>
                        <td><?= htmlspecialchars($r->warehouse ?? '') ?></td>
                        <td class="text-right"><?= $this->sma->formatMoney($net) ?></td>
                        <td class="text-right <?= ($vat < 0) ? 'vat-negative' : 'vat-positive' ?>"><?= $this->sma->formatMoney($vat) ?></td>
                        <td class="text-right"><?= $this->sma->formatMoney($gross) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight:bold; background:#d6eaf8;">
                        <td colspan="7" class="text-right"><?= lang('total') ?></td>
                        <td class="text-right"><?= $this->sma->formatMoney($s_sub_net) ?></td>
                        <td class="text-right <?= ($s_sub_vat < 0) ? 'vat-negative' : 'vat-positive' ?>"><?= $this->sma->formatMoney($s_sub_vat) ?></td>
                        <td class="text-right"><?= $this->sma->formatMoney($s_sub_gross) ?></td>
                    </tr>
                <?php endif; ?>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
            <p class="text-muted" style="padding:8px 0;">No sales records found for the selected period.</p>
        <?php endif; ?>
        <?php endif; /* end sales section */ ?>

        <!-- ── PURCHASES SECTION ─────────────────────────────────── -->
        <?php if (in_array($type, ['all', 'purchases'])): ?>
        <div class="section-heading purchases">
            <i class="fa fa-arrow-circle-left" style="color:#e67e22;"></i>
            &nbsp;Input VAT — Purchases &amp; Purchase Returns
            <span class="pull-right" style="font-weight:400; font-size:12px;">
                <?= count($purchase_rows) ?> transactions &nbsp;|&nbsp;
                VAT: <strong><?= $this->sma->formatMoney($p_vat) ?></strong>
            </span>
        </div>
        <?php if ($has_purchases): ?>

        <div class="controls table-controls" style="font-size: 12px !important;">
            <table class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                <thead>
                    <tr style="background:#fdebd0;">
                        <th>#</th>
                        <th><?= lang('date') ?></th>
                        <th><?= lang('reference_no') ?></th>
                        <th>Type</th>
                        <th><?= lang('supplier') ?></th>
                        <th>VAT No</th>
                        <th><?= lang('warehouse') ?></th>
                        <th class="text-right">Net Amount<br><small class="muted">(ex-VAT)</small></th>
                        <th class="text-right">VAT Amount</th>
                        <th class="text-right">Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if (!$has_purchases): ?>
                    <tr><td colspan="10" class="text-center muted">No purchase records found for the selected period.</td></tr>
                <?php else:
                    $p_sub_net   = 0; $p_sub_vat = 0; $p_sub_gross = 0;
                    $j = 0;
                    foreach ($purchase_rows as $r):
                        $j++;
                        $net   = (float)$r->total_net;
                        $vat   = (float)$r->total_tax;
                        $gross = (float)$r->grand_total;
                        $p_sub_net   += $net;
                        $p_sub_vat   += $vat;
                        $p_sub_gross += $gross;
                        $rclass  = $type_row_class[$r->trans_type] ?? '';
                        $badge   = $type_badge[$r->trans_type] ?? 'default';
                        $tlabel  = $type_labels[$r->trans_type] ?? $r->trans_type;
                ?>
                    <tr class="<?= $rclass ?>">
                        <td><?= $j ?></td>
                        <td><?= date('d-M-Y', strtotime($r->trans_date)) ?></td>
                        <td><?= htmlspecialchars($r->reference_no ?? '') ?></td>
                        <td>
                            <span class="label label-<?= $badge ?>"><?= $tlabel ?></span>
                            <?php if (!empty($r->entry_type ?? '')): ?>
                                <span class="label label-<?= ($r->entry_type === 'D') ? 'info' : 'warning' ?>" style="font-size:10px; margin-left:3px;"><?= htmlspecialchars($r->entry_type) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($r->party_name ?? '') ?></td>
                        <td style="font-size:11px;"><?= htmlspecialchars($r->party_vat_no ?? '') ?></td>
                        <td><?= htmlspecialchars($r->warehouse ?? '') ?></td>
                        <td class="text-right"><?= $this->sma->formatMoney($net) ?></td>
                        <td class="text-right <?= ($vat < 0) ? 'vat-negative' : 'vat-positive' ?>"><?= $this->sma->formatMoney($vat) ?></td>
                        <td class="text-right"><?= $this->sma->formatMoney($gross) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight:bold; background:#fdebd0;">
                        <td colspan="7" class="text-right"><?= lang('total') ?></td>
                        <td class="text-right"><?= $this->sma->formatMoney($p_sub_net) ?></td>
                        <td class="text-right <?= ($p_sub_vat < 0) ? 'vat-negative' : 'vat-positive' ?>"><?= $this->sma->formatMoney($p_sub_vat) ?></td>
                        <td class="text-right"><?= $this->sma->formatMoney($p_sub_gross) ?></td>
                    </tr>
                <?php endif; ?>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
            <p class="text-muted" style="padding:8px 0;">No purchase records found for the selected period.</p>
        <?php endif; ?>
        <?php endif; /* end purchases section */ ?>

        <!-- ── NET VAT POSITION FOOTER (when type = all) ────────── -->
        <?php if ($type === 'all' && ($has_sales || $has_purchases)): ?>
        <div class="row" style="margin-top: 18px;">
            <div class="col-md-6 col-md-offset-6">
                <table class="table table-condensed table-bordered" style="font-size:13px;">
                    <tbody>
                        <tr>
                            <td>Total Output VAT (Sales)</td>
                            <td class="text-right vat-positive"><?= $this->sma->formatMoney($s_vat) ?></td>
                        </tr>
                        <tr>
                            <td>Total Input VAT (Purchases)</td>
                            <td class="text-right vat-positive"><?= $this->sma->formatMoney($p_vat) ?></td>
                        </tr>
                        <tr style="font-weight:700; font-size:14px; background:<?= ($net_pos >= 0) ? '#d5f5e3' : '#fce4e4' ?>;">
                            <td>Net VAT Position (Output − Input)</td>
                            <td class="text-right <?= ($net_pos >= 0) ? 'vat-positive' : 'vat-negative' ?>">
                                <?= $this->sma->formatMoney($net_pos) ?>
                                <small style="font-weight:400;"><?= ($net_pos >= 0) ? ' payable' : ' refund' ?></small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Legend -->
        <div style="margin-top: 10px; font-size: 11px; color: #666;">
            <span style="background:#eaf4fb; padding:2px 8px; border-radius:3px; margin-right:6px; border:1px solid #aed"></span> Sales Invoice &nbsp;
            <span style="background:#fdecea; padding:2px 8px; border-radius:3px; margin-right:6px; border:1px solid #fcc"></span> Sales Return &nbsp;
            <span style="background:#fffbe6; padding:2px 8px; border-radius:3px; margin-right:6px; border:1px solid #eca"></span> Purchase Invoice &nbsp;
            <span style="background:#fde8f0; padding:2px 8px; border-radius:3px; margin-right:6px; border:1px solid #ecb"></span> Purchase Return &nbsp;
            <span style="background:#eafaf1; padding:2px 8px; border-radius:3px; margin-right:6px; border:1px solid #9fc"></span> Service Invoice &nbsp;
            <span style="background:#f5eaff; padding:2px 8px; border-radius:3px; margin-right:6px; border:1px solid #d9b"></span> Petty Cash &nbsp;
            <span style="background:#ffe8e8; padding:2px 8px; border-radius:3px; margin-right:6px; border:1px solid #faa"></span> Credit Memo &nbsp;
            <span style="background:#e8eeff; padding:2px 8px; border-radius:3px; margin-right:6px; border:1px solid #aaf"></span> Debit Memo &nbsp;
            <span style="background:#f5f5f5; padding:2px 8px; border-radius:3px; margin-right:6px; border:1px solid #ccc"></span> Memo
            &nbsp;&nbsp;<em>D/C badge = Debit/Credit entry direction</em>
        </div>

    </div><!-- /.box-content -->
</div><!-- /.box -->
