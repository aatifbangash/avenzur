<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Rebuild display dates from Y-m-d → d/m/Y
$filter_from        = '';
$filter_to          = '';
$filter_customer_id = !empty($filters['customer_id']) ? $filters['customer_id'] : '';
if (!empty($filters['from_date'])) {
    $d = DateTime::createFromFormat('Y-m-d', $filters['from_date']);
    $filter_from = $d ? $d->format('d/m/Y') : $filters['from_date'];
}
if (!empty($filters['to_date'])) {
    $d = DateTime::createFromFormat('Y-m-d', $filters['to_date']);
    $filter_to = $d ? $d->format('d/m/Y') : $filters['to_date'];
}

$total_pages  = $per_page > 0 ? (int)ceil($total_records / $per_page) : 1;
$page_start   = $total_records > 0 ? (($page - 1) * $per_page) + 1 : 0;
$page_end     = min($page * $per_page, $total_records);

// Build pagination base URL (preserve all filters)
$base_params = ['customer_id' => $filter_customer_id, 'from_date' => $filter_from, 'to_date' => $filter_to];
$base_qs     = http_build_query(array_filter($base_params));
$base_url    = admin_url('reports/customer_collections_report') . ($base_qs ? '?' . $base_qs . '&' : '?');
?>

<style>
#ccrFilterForm .form-group { margin-bottom: 0; }
.ccr-table th  { background: #3c8dbc; color: #fff; white-space: nowrap; }
.ccr-table tfoot td { font-weight: 700; background: #f5f5f5; }
.ccr-pagination-info { font-size: 13px; color: #555; }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-file-text-o"></i> Customer Collections Report</h2>
    </div>
    <div class="box-content">

        <!-- ── Filter Form ───────────────────────────────────────── -->
        <form id="ccrFilterForm" method="get" action="<?= admin_url('reports/customer_collections_report') ?>">
            <div class="row" style="margin-bottom:15px; padding:10px 15px; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:4px;">

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="from_date" style="font-size:12px; font-weight:600;">From Date</label>
                        <input type="text" id="from_date" name="from_date" class="form-control input-sm date-picker-filter"
                               placeholder="dd/mm/yyyy" value="<?= htmlspecialchars($filter_from) ?>" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="to_date" style="font-size:12px; font-weight:600;">To Date</label>
                        <input type="text" id="to_date" name="to_date" class="form-control input-sm date-picker-filter"
                               placeholder="dd/mm/yyyy" value="<?= htmlspecialchars($filter_to) ?>" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="customer_id" style="font-size:12px; font-weight:600;">Customer</label>
                        <select name="customer_id" id="customer_id" class="form-control input-sm select" style="width:100%;">
                            <option value="">All Customers</option>
                            <?php if (!empty($customers)): foreach ($customers as $c): ?>
                                <option value="<?= $c->id ?>" <?= ($filter_customer_id == $c->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((!empty($c->sequence_code) ? $c->sequence_code . ' - ' : '') . $c->name) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3" style="padding-top:22px;">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filter</button>
                    <a href="<?= admin_url('reports/customer_collections_report') ?>" class="btn btn-default btn-sm"><i class="fa fa-times"></i> Reset</a>
                </div>

            </div>
        </form>
        <!-- ── End Filter Form ──────────────────────────────────── -->

        <!-- ── Pagination / Info ─────────────────────────────────── -->
        <?php if ($total_records > 0): ?>
        <div class="row" style="margin-bottom:8px;">
            <div class="col-md-6 ccr-pagination-info">
                Showing <?= number_format($page_start) ?> – <?= number_format($page_end) ?> of <?= number_format($total_records) ?> records
            </div>
            <div class="col-md-6 text-right">
                <?php if ($total_pages > 1): ?>
                <ul class="pagination pagination-sm" style="margin:0;">
                    <?php if ($page > 1): ?>
                    <li><a href="<?= $base_url ?>page=<?= $page - 1 ?>">&laquo; Prev</a></li>
                    <?php else: ?>
                    <li class="disabled"><a>&laquo; Prev</a></li>
                    <?php endif; ?>

                    <?php
                    $start_p = max(1, $page - 3);
                    $end_p   = min($total_pages, $page + 3);
                    if ($start_p > 1): ?>
                        <li><a href="<?= $base_url ?>page=1">1</a></li>
                        <?php if ($start_p > 2): ?><li class="disabled"><a>…</a></li><?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start_p; $i <= $end_p; $i++): ?>
                    <li <?= ($i === $page) ? 'class="active"' : '' ?>>
                        <a href="<?= $base_url ?>page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($end_p < $total_pages): ?>
                        <?php if ($end_p < $total_pages - 1): ?><li class="disabled"><a>…</a></li><?php endif; ?>
                        <li><a href="<?= $base_url ?>page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
                    <?php endif; ?>

                    <?php if ($page < $total_pages): ?>
                    <li><a href="<?= $base_url ?>page=<?= $page + 1 ?>">Next &raquo;</a></li>
                    <?php else: ?>
                    <li class="disabled"><a>Next &raquo;</a></li>
                    <?php endif; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Table ─────────────────────────────────────────────── -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped ccr-table" style="margin-bottom:5px;">
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">#</th>
                        <th style="width:110px;">Date</th>
                        <th style="width:120px;">Customer Code</th>
                        <th>Customer Name</th>
                        <th style="width:150px; text-align:right;">Collection Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): $row_num = $page_start; foreach ($payments as $p): ?>
                    <tr>
                        <td style="text-align:center;"><?= $row_num++ ?></td>
                        <td><?= !empty($p->date) ? date('d-M-Y', strtotime($p->date)) : '—' ?></td>
                        <td><?= htmlspecialchars($p->sequence_code ?? '—') ?></td>
                        <td><?= htmlspecialchars($p->company ?? '—') ?></td>
                        <td style="text-align:right;"><?= number_format((float)$p->amount, 2) ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="text-center" style="padding:30px; color:#888;">
                            <i class="fa fa-inbox fa-2x"></i><br>No records found for the selected filters.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($payments)): ?>
                <tfoot>
                    <tr>
                        <td colspan="4" style="text-align:right; padding-right:15px;">
                            Grand Total (all <?= number_format($total_records) ?> records):
                        </td>
                        <td style="text-align:right;"><?= number_format($grand_total_sum, 2) ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <!-- ── Bottom Pagination ─────────────────────────────────── -->
        <?php if ($total_pages > 1): ?>
        <div class="text-right" style="margin-top:8px;">
            <ul class="pagination pagination-sm" style="margin:0;">
                <?php if ($page > 1): ?>
                <li><a href="<?= $base_url ?>page=<?= $page - 1 ?>">&laquo; Prev</a></li>
                <?php else: ?>
                <li class="disabled"><a>&laquo; Prev</a></li>
                <?php endif; ?>

                <?php
                $start_p = max(1, $page - 3);
                $end_p   = min($total_pages, $page + 3);
                if ($start_p > 1): ?>
                    <li><a href="<?= $base_url ?>page=1">1</a></li>
                    <?php if ($start_p > 2): ?><li class="disabled"><a>…</a></li><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_p; $i <= $end_p; $i++): ?>
                <li <?= ($i === $page) ? 'class="active"' : '' ?>>
                    <a href="<?= $base_url ?>page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($end_p < $total_pages): ?>
                    <?php if ($end_p < $total_pages - 1): ?><li class="disabled"><a>…</a></li><?php endif; ?>
                    <li><a href="<?= $base_url ?>page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                <li><a href="<?= $base_url ?>page=<?= $page + 1 ?>">Next &raquo;</a></li>
                <?php else: ?>
                <li class="disabled"><a>Next &raquo;</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

    </div><!-- /.box-content -->
</div><!-- /.box -->

<script>
$(document).ready(function () {
    // Date pickers
    $('.date-picker-filter').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    });

    // Select2 for customer dropdown
    if ($.fn.select2) {
        $('#customer_id').select2({ placeholder: 'All Customers', allowClear: true });
    }
});
</script>
