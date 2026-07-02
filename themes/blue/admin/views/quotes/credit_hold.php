<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="red"><i class="fa fa-fw fa-lock"></i> Credit Hold Quotes</h2>
        <div class="box-icon">
            <a href="<?= admin_url('quotes') ?>" class="btn btn-xs btn-default">
                <i class="fa fa-arrow-left"></i> Back to Quotes
            </a>
        </div>
    </div>
    <div class="box-content">

        <p class="text-muted" style="margin-bottom:15px;">
            Approved quotes where the customer's outstanding balance has reached or exceeded their credit limit.
            Only Finance Managers and Trade Managers may convert or reject these quotes.
        </p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <?php if (empty($exceeded_quotes)): ?>
            <div class="alert alert-success">
                <i class="fa fa-check-circle"></i> No credit hold quotes at this time. All customers are within their credit limits.
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i>
                <strong><?= count($exceeded_quotes) ?></strong> quote(s) are on credit hold and require review.
            </div>

            <table class="table table-bordered table-striped table-hover" id="creditHoldTable">
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th class="text-right">Credit Limit</th>
                        <th class="text-right">Outstanding Balance</th>
                        <th class="text-right">Overage</th>
                        <th class="text-right">Quote Total</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exceeded_quotes as $q): ?>
                    <tr>
                        <td>
                            <a href="<?= admin_url('quotes/view/' . $q->id) ?>" target="_blank">
                                <?= htmlspecialchars($q->reference_no ?? $q->id) ?>
                            </a>
                        </td>
                        <td><?= !empty($q->date) ? date('d M Y', strtotime($q->date)) : '—' ?></td>
                        <td><strong><?= htmlspecialchars($q->customer) ?></strong></td>
                        <td class="text-right"><?= number_format($q->credit_limit, 2) ?></td>
                        <td class="text-right text-danger"><strong><?= number_format($q->current_balance, 2) ?></strong></td>
                        <td class="text-right">
                            <span class="label label-danger"><?= number_format($q->overage, 2) ?></span>
                        </td>
                        <td class="text-right"><?= number_format($q->grand_total, 2) ?></td>
                        <td class="text-center" style="white-space:nowrap;">
                            <a href="<?= admin_url('sales/add_from_quote/' . $q->id) ?>"
                               class="btn btn-xs btn-success"
                               title="Convert this quote to a sale (justification note required)">
                                <i class="fa fa-check"></i> Convert to Sale
                            </a>
                            <a href="<?= admin_url('quotes/reject_credit_hold/' . $q->id) ?>"
                               class="btn btn-xs btn-danger">
                                <i class="fa fa-times"></i> Reject
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <script>
            $(document).ready(function () {
                if ($.fn.dataTable) {
                    $('#creditHoldTable').DataTable({
                        order: [[1, 'desc']],
                        pageLength: 25,
                        language: { search: 'Filter:' }
                    });
                }
            });
            </script>
        <?php endif; ?>

    </div>
</div>
