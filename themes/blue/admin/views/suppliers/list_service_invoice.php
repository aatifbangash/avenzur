<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
// Reverse Y-m-d back to d/m/Y for display in filter inputs
$filter_from        = '';
$filter_to          = '';
$filter_supplier_id = !empty($filters['supplier_id']) ? $filters['supplier_id'] : '';
if (!empty($filters['from_date'])) {
    $d = DateTime::createFromFormat('Y-m-d', $filters['from_date']);
    $filter_from = $d ? $d->format('d/m/Y') : $filters['from_date'];
}
if (!empty($filters['to_date'])) {
    $d = DateTime::createFromFormat('Y-m-d', $filters['to_date']);
    $filter_to = $d ? $d->format('d/m/Y') : $filters['to_date'];
}
?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('Supplier Service Invoice'); ?></h2>
    </div>
    <div class="box-content">

        <!-- ── Filter Form ─────────────────────────────────────── -->
        <form id="serviceInvoiceFilterForm" method="get" action="<?= admin_url('suppliers/list_service_invoice') ?>">
            <div class="row" style="margin-bottom:15px; padding:10px 15px; background:#f9f9f9; border:1px solid #e0e0e0; border-radius:4px;">

                <div class="col-md-2">
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600;"><?= lang('From Date') ?></label>
                        <input type="text" name="from_date" class="form-control input-sm date-picker-filter"
                               placeholder="dd/mm/yyyy" value="<?= htmlspecialchars($filter_from) ?>" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600;"><?= lang('To Date') ?></label>
                        <input type="text" name="to_date" class="form-control input-sm date-picker-filter"
                               placeholder="dd/mm/yyyy" value="<?= htmlspecialchars($filter_to) ?>" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label style="font-size:12px; font-weight:600;"><?= lang('Supplier') ?></label>
                        <select name="supplier_id" class="form-control input-sm select" style="width:100%;">
                            <option value=""><?= lang('All Suppliers') ?></option>
                            <?php if (!empty($suppliers)): foreach ($suppliers as $s): ?>
                                <option value="<?= $s->id ?>" <?= ($filter_supplier_id == $s->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s->name . (!empty($s->sequence_code) ? ' (' . $s->sequence_code . ')' : '')) ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-2" style="padding-top:22px;">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-filter"></i> <?= lang('Filter') ?>
                    </button>
                    <a href="<?= admin_url('suppliers/list_service_invoice') ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-times"></i> <?= lang('Reset') ?>
                    </a>
                </div>

            </div>
        </form>

        <!-- ── Export button ─────────────────────────────────── -->
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-12 text-right">
                <?php
                $export_params = http_build_query([
                    'supplier_id'  => $filter_supplier_id,
                    'from_date'    => $filter_from,
                    'to_date'      => $filter_to,
                    'export_excel' => 1,
                ]);
                ?>
                <a href="<?= admin_url('suppliers/list_service_invoice') ?>?<?= $export_params ?>"
                   class="btn btn-success btn-sm">
                    <i class="fa fa-file-excel-o"></i> <?= lang('Export Excel') ?>
                </a>
            </div>
        </div>

        <!-- ── Table ────────────────────────────────────────────── -->
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive" style="font-size:12px;">
                    <table class="table table-striped table-bordered table-condensed table-hover">
                        <thead style="background:#f5f5f5;">
                            <tr>
                                <th>#</th>
                                <th><?= lang('Reference No.') ?></th>
                                <th><?= lang('Supplier Code') ?></th>
                                <th><?= lang('Supplier') ?></th>
                                <th><?= lang('Date') ?></th>
                                <th class="text-right"><?= lang('Amount (ex-VAT)') ?></th>
                                <th class="text-right"><?= lang('VAT Amount') ?></th>
                                <th class="text-right"><?= lang('Total') ?></th>
                                <th><?= lang('Status') ?></th>
                                <th><?= lang('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($service_invoices)):
                                $count = 0;
                                $total_amount = 0;
                                $total_vat    = 0;
                                foreach ($service_invoices as $invoice):
                                    $count++;
                                    $amount    = (float)$invoice->payment_amount;
                                    $vat       = (float)($invoice->vat_value ?? 0);
                                    $net       = $amount - $vat;
                                    $total_amount += $amount;
                                    $total_vat    += $vat;
                            ?>
                                <tr>
                                    <td><?= $count ?></td>
                                    <td><?= htmlspecialchars($invoice->reference_no) ?></td>
                                    <td><?= htmlspecialchars($invoice->sequence_code ?? '') ?></td>
                                    <td><?= htmlspecialchars($invoice->company ?? '') ?></td>
                                    <td><?= !empty($invoice->date) ? date('d-M-Y', strtotime($invoice->date)) : '—' ?></td>
                                    <td class="text-right"><?= number_format($net, 2) ?></td>
                                    <td class="text-right"><?= number_format($vat, 2) ?></td>
                                    <td class="text-right"><?= number_format($amount, 2) ?></td>
                                    <td>
                                        <?php if (($invoice->status ?? 'open') === 'locked'): ?>
                                            <span class="label label-danger"><i class="fa fa-lock"></i> Locked</span>
                                        <?php else: ?>
                                            <span class="label label-info">Open</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= admin_url('suppliers/service_invoice_pdf/' . $invoice->id) ?>"
                                           class="tip btn btn-xs btn-default" title="Download PDF">
                                            <i class="fa fa-file-pdf-o"></i>
                                        </a>
                                        <?php if (($invoice->status ?? 'open') !== 'locked'): ?>
                                            <a href="<?= admin_url('suppliers/edit_service_invoice/' . $invoice->id) ?>"
                                               class="tip btn btn-xs btn-warning" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($this->sma->in_group('financemanager')): ?>
                                            <?php if (($invoice->status ?? 'open') === 'locked'): ?>
                                                <form method="POST" action="<?= admin_url('suppliers/unlock_service_invoice') ?>" style="display:inline;">
                                                    <input type="hidden" name="memo_id" value="<?= $invoice->id ?>">
                                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                                    <button type="submit" class="tip btn btn-xs btn-success" title="Unlock" onclick="return confirm('Unlock this service invoice? It will be editable again.');">
                                                        <i class="fa fa-unlock-alt"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="<?= admin_url('suppliers/lock_service_invoice') ?>" style="display:inline;">
                                                    <input type="hidden" name="memo_id" value="<?= $invoice->id ?>">
                                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                                                    <button type="submit" class="tip btn btn-xs btn-danger" title="Lock" onclick="return confirm('Lock this service invoice? It will no longer be editable.');">
                                                        <i class="fa fa-lock"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="font-weight:bold; background:#f0f0f0;">
                                    <td colspan="5" class="text-right"><?= lang('total') ?></td>
                                    <td class="text-right"><?= number_format($total_amount - $total_vat, 2) ?></td>
                                    <td class="text-right"><?= number_format($total_vat, 2) ?></td>
                                    <td class="text-right"><?= number_format($total_amount, 2) ?></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            <?php else: ?>
                            <tbody>
                                <tr>
                                    <td colspan="10" class="text-center text-muted"><?= lang('no_records_found') ?></td>
                                </tr>
                            </tbody>
                            <tfoot></tfoot>
                            <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
