<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-info-circle"></i><?= lang('Customer Service Invoices'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks"></ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive" style="font-size: 12px;">
                    <table class="table table-striped table-bordered table-condensed table-hover">
                        <thead style="background:#f5f5f5;">
                            <tr>
                                <th>#</th>
                                <th><?= lang('Reference No.'); ?></th>
                                <th><?= lang('Customer') ?></th>
                                <th><?= lang('Date') ?></th>
                                <th class="text-right"><?= lang('Payment Amount') ?></th>
                                <th><?= lang('Status') ?></th>
                                <th><?= lang('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($service_invoices)):
                                $count = 0;
                                foreach ($service_invoices as $invoice):
                                    $count++;
                                    $period_closed = $this->period_closing_model->isPeriodClosed('ar', $invoice->date);
                            ?>
                                <tr>
                                    <td><?= $count ?></td>
                                    <td><?= htmlspecialchars($invoice->reference_no) ?></td>
                                    <td><?= htmlspecialchars($invoice->company ?? '') ?></td>
                                    <td><?= !empty($invoice->date) ? date('d-M-Y', strtotime($invoice->date)) : '—' ?></td>
                                    <td class="text-right"><?= number_format((float)$invoice->payment_amount, 2) ?></td>
                                    <td>
                                        <?php if ($period_closed): ?>
                                            <span class="label label-danger tip" title="AR period closed for this invoice date"><i class="fa fa-lock"></i> Period Closed</span>
                                        <?php elseif (($invoice->status ?? 'open') === 'locked'): ?>
                                            <span class="label label-danger"><i class="fa fa-lock"></i> Locked</span>
                                        <?php else: ?>
                                            <span class="label label-info">Open</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= admin_url('customers/service_invoice_pdf/' . $invoice->id) ?>"
                                           class="tip btn btn-xs btn-default" title="Download PDF">
                                            <i class="fa fa-file-pdf-o"></i>
                                        </a>
                                        <?php if (!$period_closed && ($invoice->status ?? 'open') !== 'locked' && empty($invoice->payment_count)): ?>
                                            <a href="<?= admin_url('customers/edit_service_invoice/' . $invoice->id) ?>"
                                               class="tip btn btn-xs btn-warning" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        <?php elseif (!$period_closed && !empty($invoice->payment_count)): ?>
                                            <span class="tip btn btn-xs btn-default disabled" title="Cannot edit — payment recorded against this invoice">
                                                <i class="fa fa-edit"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted"><?= lang('no_records_found') ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
