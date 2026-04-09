<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.3/xlsx.full.min.js"></script>
<script>
    function exportTableToExcel(tableId, filename) {
        const table = document.getElementById(tableId);
        const wb = XLSX.utils.table_to_book(table, {sheet: 'Sheet 1'});
        XLSX.writeFile(wb, filename || 'unpaid_invoices.xlsx');
    }

    $(document).ready(function () {
        // Date picker (date-only — no time component)
        $('#at_date').datetimepicker({
            format: site.dateFormats.js_sdate,
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

        // Auto-submit on type tab change
        $(document).on('click', '.type-tab', function () {
            $('#type').val($(this).data('type'));
            $('#party_id').val('');
            $('#filterForm').submit();
        });
    });
</script>

<style>
    .overdue-high   { background-color: #ffe0e0 !important; }
    .overdue-medium { background-color: #fff3cd !important; }
    .overdue-low    { background-color: #fff8e1 !important; }
    .summary-box  { padding: 12px 18px; border-radius: 6px; margin-bottom: 10px; color: #fff; }
    .summary-box .label-sm { font-size: 12px; opacity: .85; }
    .summary-box .value-lg { font-size: 22px; font-weight: 700; }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue">
            <i class="fa-fw fa fa-file-text"></i>
            <?php
            $type = $type ?? 'ar';
            echo ($type === 'ar') ? 'Unpaid AR Invoices (Accounts Receivable)' : 'Unpaid AP Invoices (Accounts Payable)';
            ?>
        </h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li>
                    <a href="javascript:void(0);"
                       onclick="exportTableToExcel('unpaidTable', '<?= ($type === 'ar') ? 'Unpaid_AR_Invoices' : 'Unpaid_AP_Invoices' ?>.xlsx')"
                       class="tip" title="<?= lang('download_xls') ?>">
                        <i class="icon fa fa-file-excel-o"></i>
                    </a>
                </li>
                <li>
                    <?php
                    $export_params = $_GET;
                    $export_params['export_excel'] = 1;
                    ?>
                    <a href="<?= admin_url('reports/unpaid_invoices?' . http_build_query($export_params)) ?>"
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
            <li class="<?= ($type === 'ar') ? 'active' : '' ?>">
                <a href="javascript:void(0);" class="type-tab" data-type="ar">
                    <i class="fa fa-arrow-down text-success"></i> AR &mdash; Receivables (Sales)
                </a>
            </li>
            <li class="<?= ($type === 'ap') ? 'active' : '' ?>">
                <a href="javascript:void(0);" class="type-tab" data-type="ap">
                    <i class="fa fa-arrow-up text-danger"></i> AP &mdash; Payables (Purchases)
                </a>
            </li>
        </ul>

        <!-- Filter Form -->
        <?php
        $attrib = ['id' => 'filterForm', 'method' => 'get', 'role' => 'form'];
        echo admin_form_open_multipart('reports/unpaid_invoices', $attrib);
        ?>
        <input type="hidden" name="type" id="type" value="<?= htmlspecialchars($type) ?>">

        <div class="row">
            <div class="col-lg-12">

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="at_date">At Date</label>
                        <?php echo form_input('at_date', ($at_date ?? ''), 'class="form-control input-tip date" id="at_date" placeholder="At date"'); ?>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="ref_no"><?= lang('reference_no') ?></label>
                        <?php echo form_input('ref_no', ($ref_no ?? ''), 'class="form-control" id="ref_no" placeholder="Invoice / Ref #"'); ?>
                    </div>
                </div>

                <?php if ($type === 'ar'): ?>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="party_id"><?= lang('customer') ?></label>
                        <?php
                        $cust_opts = ['' => '— ' . lang('all') . ' ' . lang('customers') . ' —'];
                        foreach ($customers as $c) {
                            $cust_opts[$c->id] = $c->name;
                        }
                        echo form_dropdown('party_id', $cust_opts, ($party_id ?? ''),
                            'id="party_id" class="form-control input-tip select" style="width:100%;" data-placeholder="' . lang('select') . ' ' . lang('customer') . '"');
                        ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="party_id"><?= lang('supplier') ?></label>
                        <?php
                        $supp_opts = ['' => '— ' . lang('all') . ' ' . lang('suppliers') . ' —'];
                        foreach ($suppliers as $s) {
                            $supp_opts[$s->id] = $s->name;
                        }
                        echo form_dropdown('party_id', $supp_opts, ($party_id ?? ''),
                            'id="party_id" class="form-control input-tip select" style="width:100%;" data-placeholder="' . lang('select') . ' ' . lang('supplier') . '"');
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-md-2">
                    <div class="form-group">
                        <button type="submit" style="margin-top: 28px;" class="btn btn-primary btn-block">
                            <i class="fa fa-search"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        <?php echo form_close(); ?>

        <hr style="margin: 5px 0 15px;" />

        <?php if (!empty($invoices)): ?>

        <?php
        // ── Summary calculations ──────────────────────────────────
        $total_invoice = array_sum(array_column((array)$invoices, 'invoice_total'));
        $total_discount = array_sum(array_column((array)$invoices, 'discount'));
        $total_returns  = array_sum(array_column((array)$invoices, 'return_amount'));
        $total_paid     = array_sum(array_column((array)$invoices, 'paid'));
        $total_outstanding = array_sum(array_column((array)$invoices, 'outstanding'));
        $count_invoices = count($invoices);
        ?>

        <!-- Summary Boxes -->
        <div class="row" style="margin-bottom: 15px;">
            <div class="col-md-3">
                <div class="summary-box" style="background:#3498db;">
                    <div class="label-sm"><?= lang('Total Invoices') ?></div>
                    <div class="value-lg"><?= number_format($count_invoices) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-box" style="background:#27ae60;">
                    <div class="label-sm"><?= lang('Invoice Total') ?></div>
                    <div class="value-lg"><?= $this->sma->formatMoney($total_invoice) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-box" style="background:#e67e22;">
                    <div class="label-sm"><?= lang('paid') ?></div>
                    <div class="value-lg"><?= $this->sma->formatMoney($total_paid) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-box" style="background:#e74c3c;">
                    <div class="label-sm"><?= lang('outstanding') ?></div>
                    <div class="value-lg"><?= $this->sma->formatMoney($total_outstanding) ?></div>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="controls table-controls" style="font-size: 12px !important;">
            <table id="unpaidTable" class="table items table-striped table-bordered table-condensed table-hover sortable_table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?= lang('date') ?></th>
                        <th><?= lang('reference_no') ?></th>
                        <th><?= ($type === 'ar') ? lang('customer') : lang('supplier') ?></th>
                        <th>Seq. Code</th>
                        <th>Ledger Acc.</th>
                        <?php if ($type === 'ar'): ?><th><?= lang('area') ?></th><?php endif; ?>
                        <th class="text-right"><?= lang('Invoice Total') ?></th>
                        <?php if ($type === 'ar'): ?>
                        
                        <th class="text-right"><?= lang('Returns') ?></th>
                        <?php endif; ?>
                        <th class="text-right"><?= lang('paid') ?></th>
                        <th class="text-right"><?= lang('outstanding') ?></th>
                        <th class="text-center"><?= lang('Due Date') ?></th>
                        <th class="text-center"><?= lang('Days Overdue') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $i = 0;
                foreach ($invoices as $inv):
                    $i++;
                    // Row color by days overdue
                    $row_class = '';
                    if ($inv->days_overdue >= 90)     $row_class = 'overdue-high';
                    elseif ($inv->days_overdue >= 30)  $row_class = 'overdue-medium';
                    elseif ($inv->days_overdue >= 1)   $row_class = 'overdue-low';

                    $detail_url = ($type === 'ar')
                        ? admin_url('sales?sid=' . $inv->invoice_id)
                        : admin_url('purchases/view/' . $inv->invoice_id);
                ?>
                    <tr class="<?= $row_class ?>">
                        <td><?= $i ?></td>
                        <td><?= date('d-M-Y', strtotime($inv->date)) ?></td>
                        <td><a href="<?= $detail_url ?>" target="_blank"><?= htmlspecialchars($inv->reference_no ?: '#' . $inv->invoice_id) ?></a></td>
                        <td><?= htmlspecialchars($inv->party_name) ?></td>
                        <td><?= htmlspecialchars($inv->sequence_code ?? '') ?></td>
                        <td><?= htmlspecialchars($inv->ledger_name ?? '') ?></td>
                        <?php if ($type === 'ar'): ?><td><?= htmlspecialchars($inv->area ?? '') ?></td><?php endif; ?>
                        <td class="text-right"><?= number_format($inv->invoice_total ,2) ?></td>
                        <?php if ($type === 'ar'): ?>
                        
                        <td class="text-right"><?= number_format($inv->return_amount ,2) ?></td>
                        <?php endif; ?>
                        <td class="text-right"><?= number_format($inv->paid ,2) ?></td>
                        <td class="text-right" style="font-weight:600; color:#c0392b;"><?= number_format($inv->outstanding ,2) ?></td>
                        <td class="text-center"><?= ($inv->due_date_calc && $inv->due_date_calc !== '0000-00-00') ? date('d-M-Y', strtotime($inv->due_date_calc)) : '-' ?></td>
                        <td class="text-center">
                            <?php
                            $d = (int)$inv->days_overdue;
                            if ($d <= 0) {
                                $badge_text = ($d === 0) ? 'Due today' : '' . abs($d) . '';
                                echo "<span class=\"label label-success\">{$badge_text}</span>";
                            } else {
                                $badge = ($d >= 90) ? 'danger' : (($d >= 30) ? 'warning' : 'default');
                                echo "<span class=\"label label-{$badge}\">{$d}</span>";
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight:bold; background-color:#f0f0f0;">
                        <td colspan="<?= ($type === 'ar') ? 7 : 6 ?>" class="text-right"><?= lang('total') ?></td>
                        <td class="text-right"><?= number_format($total_invoice ,2) ?></td>
                        <?php if ($type === 'ar'): ?>
                        
                        <td class="text-right"><?= number_format($total_returns ,2) ?></td>
                        <?php endif; ?>
                        <td class="text-right"><?= number_format($total_paid ,2) ?></td>
                        <td class="text-right" style="color:#c0392b;"><?= number_format(($total_outstanding) ,2) ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Legend -->
        <div style="margin-top: 10px; font-size: 11px; color: #666;">
            <span style="background:#ffe0e0; padding:2px 8px; border-radius:3px; margin-right:8px;">&nbsp;</span> &ge; 90 days &nbsp;
            <span style="background:#fff3cd; padding:2px 8px; border-radius:3px; margin-right:8px;">&nbsp;</span> 30–89 days &nbsp;
            <span style="background:#fff8e1; padding:2px 8px; border-radius:3px; margin-right:8px;">&nbsp;</span> 1–29 days
        </div>

        <?php elseif (isset($_GET['type'])): ?>
            <div class="alert alert-info"><?= lang('no_data_available') ?></div>
        <?php else: ?>
            <div class="alert alert-warning">Select filters above and click Search to load the report.</div>
        <?php endif; ?>

    </div><!-- /.box-content -->
</div><!-- /.box -->
