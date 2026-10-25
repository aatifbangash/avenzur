<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
    $months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $periodLabel = $months[$run['period_month']] . ' ' . $run['period_year'];
    $isPosted    = $run['status'] === 'posted';
    $isDraft     = $run['status'] === 'draft';
?>

<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-8">
                <h2 class="box-title">
                    <i class="fa fa-users"></i> <?php echo html_escape($run['run_name']); ?>
                    &nbsp;
                    <?php if ($isPosted): ?>
                        <span class="label label-success">Posted</span>
                    <?php elseif ($isDraft): ?>
                        <span class="label label-default">Draft</span>
                    <?php else: ?>
                        <span class="label label-danger">Cancelled</span>
                    <?php endif; ?>
                </h2>
            </div>
            <div class="col-md-4 text-right">
                <?php if ($isDraft): ?>
                    <a href="<?php echo admin_url('entries/salary_post/' . $run['id']); ?>"
                       class="btn btn-success btn-sm confirm-action"
                       data-confirm="Post this salary run to Journal Ledger? This creates JL entries and cannot be undone.">
                        <i class="fa fa-upload"></i> Post to JL
                    </a>
                    <a href="<?php echo admin_url('entries/salary_delete/' . $run['id']); ?>"
                       class="btn btn-danger btn-sm confirm-action"
                       data-confirm="Delete this salary run? This cannot be undone.">
                        <i class="fa fa-trash"></i> Delete
                    </a>
                <?php endif; ?>
                <a href="<?php echo admin_url('entries/salary_index'); ?>" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="box-body">

        <?php if ($this->session->flashdata('message')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-check-circle"></i> <?php echo $this->session->flashdata('message'); ?>
            </div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fa fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>

        <!-- Summary KPI row -->
        <div class="row" style="margin-bottom:16px;">
            <div class="col-sm-4">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-money"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Gross</span>
                        <span class="info-box-number"><?php echo number_format($run['total_gross'], 2); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-minus-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Deductions</span>
                        <span class="info-box-number"><?php echo number_format($run['total_deductions'], 2); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Net Payable</span>
                        <span class="info-box-number"><?php echo number_format($run['total_net'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Run meta -->
        <div class="row" style="margin-bottom:16px;">
            <div class="col-md-6">
                <table class="table table-condensed table-bordered">
                    <tr><th width="40%">Period</th>    <td><?php echo $periodLabel; ?></td></tr>
                    <tr><th>Run Date</th>               <td><?php echo $run['run_date']; ?></td></tr>
                    <tr><th>Description</th>            <td><?php echo html_escape($run['description'] ?: '—'); ?></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-condensed table-bordered">
                    <tr>
                        <th width="40%">JL Entry</th>
                        <td>
                            <?php if (isset($jl_entry)): ?>
                                <a href="<?php echo admin_url('entries/view/journal/' . $jl_entry['id']); ?>" target="_blank" class="text-success">
                                    <i class="fa fa-file-text-o"></i>
                                    Entry #<?php echo $jl_entry['number']; ?> &mdash; <?php echo $jl_entry['date']; ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Not posted yet</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr><th>Status</th>
                        <td>
                            <?php
                                $statusMap = ['draft' => 'default', 'posted' => 'success', 'cancelled' => 'danger'];
                                $cls = $statusMap[$run['status']] ?? 'default';
                            ?>
                            <span class="label label-<?php echo $cls; ?>"><?php echo ucfirst($run['status']); ?></span>
                        </td>
                    </tr>
                    <tr><th>Created By</th> <td><?php echo $run['created_by'] ?? '—'; ?></td></tr>
                </table>
            </div>
        </div>

        <!-- Employee lines table -->
        <h4><i class="fa fa-list"></i> Employee Salary Lines</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover table-condensed">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Salary Expense Ledger (Dr)</th>
                        <th>Salary Payable Ledger (Cr)</th>
                        <th class="text-right">Gross</th>
                        <th class="text-right">Deductions</th>
                        <th class="text-right">Net</th>
                        <th>Narration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i => $item): ?>
                        <tr>
                            <td class="text-center"><?php echo $i + 1; ?></td>
                            <td><strong><?php echo html_escape($item['employee_name']); ?></strong></td>
                            <td><?php echo html_escape($item['department_id'] ?: '—'); ?></td>
                            <td class="text-success small">
                                <i class="fa fa-arrow-right"></i>
                                <?php echo html_escape($item['salary_exp_ledger_name'] ?? 'Ledger #' . $item['ledger_salary_exp_id']); ?>
                            </td>
                            <td class="text-primary small">
                                <i class="fa fa-arrow-left"></i>
                                <?php echo html_escape($item['payable_ledger_name'] ?? 'Ledger #' . $item['ledger_payable_id']); ?>
                            </td>
                            <td class="text-right"><?php echo number_format($item['gross_amount'], 2); ?></td>
                            <td class="text-right text-danger">
                                <?php echo $item['deductions'] > 0 ? '(' . number_format($item['deductions'], 2) . ')' : '—'; ?>
                            </td>
                            <td class="text-right"><strong><?php echo number_format($item['net_amount'], 2); ?></strong></td>
                            <td class="text-muted small"><?php echo html_escape($item['narration'] ?: '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="info">
                        <th colspan="5"><strong>Totals</strong></th>
                        <th class="text-right"><strong><?php echo number_format($run['total_gross'], 2); ?></strong></th>
                        <th class="text-right text-danger"><strong>
                            <?php echo $run['total_deductions'] > 0 ? '(' . number_format($run['total_deductions'], 2) . ')' : '—'; ?>
                        </strong></th>
                        <th class="text-right"><strong><?php echo number_format($run['total_net'], 2); ?></strong></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if ($isPosted && isset($jl_entry)): ?>
            <div class="alert alert-success" style="margin-top:12px;">
                <i class="fa fa-check-circle"></i>
                This salary run is posted to Journal Ledger as
                <strong>Entry #<?php echo $jl_entry['number']; ?></strong>
                on <strong><?php echo $jl_entry['date']; ?></strong>.
                <a href="<?php echo admin_url('entries/view/journal/' . $jl_entry['id']); ?>" target="_blank" class="btn btn-xs btn-success" style="margin-left:8px;">
                    <i class="fa fa-eye"></i> View JL Entry
                </a>
            </div>
        <?php endif; ?>

    </div><!-- /.box-body -->
</div><!-- /.box -->

<script>
$(function () {
    $(document).on('click', '.confirm-action', function (e) {
        var msg = $(this).data('confirm') || 'Are you sure?';
        if (!confirm(msg)) { e.preventDefault(); }
    });
});
</script>
