<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Rebuild display values from filters (Y-m-d back to d/m/Y for the inputs)
$filter_from        = '';
$filter_to          = '';
$filter_cust_id     = !empty($filters['customer_id']) ? $filters['customer_id'] : '';
$filter_category    = !empty($filters['category'])    ? $filters['category']    : '';
$filter_sales_agent = !empty($filters['sales_agent']) ? $filters['sales_agent'] : '';
if (!empty($filters['from_date'])) {
    $d = DateTime::createFromFormat('Y-m-d', $filters['from_date']);
    $filter_from = $d ? $d->format('d/m/Y') : $filters['from_date'];
}
if (!empty($filters['to_date'])) {
    $d = DateTime::createFromFormat('Y-m-d', $filters['to_date']);
    $filter_to = $d ? $d->format('d/m/Y') : $filters['to_date'];
}
?>

<style>
#paymentFilterForm .form-group { margin-bottom: 0; }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-money"></i><?= lang('Customer Payments'); ?></h2>
    </div>
    <div class="box-content">

        <!-- ── Filter Form ─────────────────────────────────────── -->
        <form id="paymentFilterForm" method="get" action="<?= admin_url('customers/list_payments') ?>">
            <div class="row" style="margin-bottom:8px; padding: 10px 15px; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:4px;">

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="from_date" style="font-size:12px; font-weight:600;"><?= lang('From Date') ?></label>
                        <input type="text" id="from_date" name="from_date" class="form-control input-sm date-picker-filter"
                               placeholder="dd/mm/yyyy" value="<?= htmlspecialchars($filter_from) ?>" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="to_date" style="font-size:12px; font-weight:600;"><?= lang('To Date') ?></label>
                        <input type="text" id="to_date" name="to_date" class="form-control input-sm date-picker-filter"
                               placeholder="dd/mm/yyyy" value="<?= htmlspecialchars($filter_to) ?>" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="customer_id" style="font-size:12px; font-weight:600;"><?= lang('Customer') ?></label>
                        <select name="customer_id" id="customer_id" class="form-control input-sm select" style="width:100%;">
                            <option value=""><?= lang('All Customers') ?></option>
                            <?php if (!empty($customers)): foreach ($customers as $c): ?>
                                <option value="<?= $c->id ?>" <?= ($filter_cust_id == $c->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c->company . ' (' . $c->name . ')') ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sales_agent" style="font-size:12px; font-weight:600;"><?= lang('Sales Agent') ?></label>
                        <select name="sales_agent" id="sales_agent" class="form-control input-sm" style="width:100%;">
                            <option value=""><?= lang('All Agents') ?></option>
                            <?php if (!empty($salesmen)): foreach ($salesmen as $sm): ?>
                                <option value="<?= htmlspecialchars($sm->name) ?>" <?= ($filter_sales_agent === $sm->name) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sm->name) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="category" style="font-size:12px; font-weight:600;"><?= lang('Category') ?></label>
                        <select name="category" id="category" class="form-control input-sm" style="width:100%;">
                            <option value=""><?= lang('All Categories') ?></option>
                            <?php if (!empty($customer_categories)): foreach ($customer_categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= ($filter_category === $cat) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-1" style="padding-top:22px;">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fa fa-filter"></i> <?= lang('Filter') ?>
                    </button>
                    <a href="<?= admin_url('customers/list_payments') ?>" class="btn btn-default btn-sm btn-block" style="margin-top:4px;">
                        <i class="fa fa-times"></i> <?= lang('Reset') ?>
                    </a>
                </div>

            </div>
        </form>

        <!-- ── Export button ──────────────────────────────────── -->
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-12 text-right">
                <?php
                $export_params = http_build_query([
                    'customer_id'  => $filter_cust_id,
                    'category'     => $filter_category,
                    'sales_agent'  => $filter_sales_agent,
                    'from_date'    => $filter_from,
                    'to_date'      => $filter_to,
                    'export_excel' => 1,
                ]);
                ?>
                <a href="<?= admin_url('customers/list_payments') ?>?<?= $export_params ?>"
                   class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel-o"></i> <?= lang('Export Excel') ?>
                </a>
            </div>
        </div>

        <!-- ── Table ──────────────────────────────────────────── -->
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive" style="font-size: 12px;">
                    <table id="paymentsTable"
                           class="table table-striped table-bordered table-condensed table-hover">
                        <thead style="background:#f5f5f5;">
                            <tr>
                                <th>#</th>
                                <th><?= lang('Reference No.') ?></th>
                                <th><?= lang('Customer Code') ?></th>
                                <th><?= lang('Customer') ?></th>
                                <th><?= lang('Category') ?></th>
                                <th><?= lang('Sales Agent') ?></th>
                                <th><?= lang('Date') ?></th>
                                <th class="text-right"><?= lang('Payment Amount') ?></th>
                                <th><?= lang('Bank') ?></th>
                                <th><?= lang('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 0;
                            $grand_total = 0.0;
                            if (!empty($payments)):
                                foreach ($payments as $payment):
                                    $count++;
                                    $grand_total += (float) $payment->amount;
                            ?>
                            <tr>
                                <td><?= $count ?></td>
                                <td><?= htmlspecialchars($payment->reference_no) ?></td>
                                <td><?= htmlspecialchars($payment->sequence_code ?? '') ?></td>
                                <td><?= htmlspecialchars($payment->company) ?></td>
                                <td>
                                    <?php if (!empty($payment->customer_group)): ?>
                                        <span class="label label-info"><?= htmlspecialchars($payment->customer_group) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($payment->sales_agent ?? '') ?></td>
                                <td><?= !empty($payment->date) ? date('d-M-Y', strtotime($payment->date)) : '' ?></td>
                                <td class="text-right">
                                    <?= number_format((float) $payment->amount, 2) ?>
                                </td>
                                <td><?= htmlspecialchars($payment->ledger_name ?? '') ?></td>
                                <td>
                                    <a href="<?= admin_url('customers/view_payment/' . $payment->id) ?>"
                                       class="tip btn btn-xs btn-default" title="<?= lang('View Payment') ?>">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                        <tfoot style="background:#e8f5e8; font-weight:bold;">
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th class="text-right"><?= lang('Total') ?></th>
                                <th class="text-right"><?= number_format($grand_total, 2) ?></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /.box-content -->
</div><!-- /.box -->

<script>
$(document).ready(function () {
    // Date pickers
    $('.date-picker-filter').datetimepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true,
        minView: 2
    });

    // DataTable for sorting / searching / pagination
    if ($.fn.dataTable) {
        $('#paymentsTable').DataTable({
            destroy:   true,
            paging:    true,
            searching: true,
            ordering:  true,
            order:     [],
            pageLength: 100,
            columnDefs: [{ orderable: false, targets: [9] }],
            language: {
                search: '<?= lang("Search") ?>:',
                emptyTable: '<?= lang("No payments found") ?>',
                lengthMenu: '_MENU_ records per page',
                info: 'Showing _START_ to _END_ of _TOTAL_ records'
            }
        });
    }
});
</script>

