<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-8">
                <h2 class="box-title"><i class="fa fa-users"></i> Salary Runs</h2>
            </div>
            <div class="col-md-4 text-right">
                <a href="<?php echo admin_url('entries/salary_add'); ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus-circle"></i> New Salary Run
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

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Run Name</th>
                        <th>Period</th>
                        <th>Run Date</th>
                        <th class="text-right">Gross</th>
                        <th class="text-right">Deductions</th>
                        <th class="text-right">Net</th>
                        <th>Status</th>
                        <th>JL Entry</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($runs)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">
                                <i class="fa fa-inbox fa-2x"></i><br>No salary runs yet.
                                <a href="<?php echo admin_url('entries/salary_add'); ?>">Create the first run &rarr;</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                            $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        ?>
                        <?php foreach ($runs as $run): ?>
                            <tr>
                                <td><?php echo $run['id']; ?></td>
                                <td><strong><?php echo html_escape($run['run_name']); ?></strong></td>
                                <td><?php echo $months[$run['period_month']] . ' ' . $run['period_year']; ?></td>
                                <td><?php echo $run['run_date']; ?></td>
                                <td class="text-right"><?php echo number_format($run['total_gross'], 2); ?></td>
                                <td class="text-right text-danger">
                                    <?php if ($run['total_deductions'] > 0): ?>
                                        (<?php echo number_format($run['total_deductions'], 2); ?>)
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td class="text-right"><strong><?php echo number_format($run['total_net'], 2); ?></strong></td>
                                <td>
                                    <?php
                                        $statusMap = [
                                            'draft'     => 'default',
                                            'posted'    => 'success',
                                            'cancelled' => 'danger',
                                        ];
                                        $cls = $statusMap[$run['status']] ?? 'default';
                                    ?>
                                    <span class="label label-<?php echo $cls; ?>"><?php echo ucfirst($run['status']); ?></span>
                                </td>
                                <td>
                                    <?php if ($run['entry_id']): ?>
                                        <a href="<?php echo admin_url('entries/view/journal/' . $run['entry_id']); ?>" target="_blank" class="text-success small">
                                            <i class="fa fa-file-text-o"></i> #<?php echo $run['entry_id']; ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('entries/salary_view/' . $run['id']); ?>"
                                       class="btn btn-xs btn-primary" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <?php if ($run['status'] === 'draft'): ?>
                                        <a href="<?php echo admin_url('entries/salary_post/' . $run['id']); ?>"
                                           class="btn btn-xs btn-success confirm-action"
                                           data-confirm="Post salary run '<?php echo html_escape($run['run_name']); ?>' to Journal Ledger? This cannot be undone."
                                           title="Post to JL">
                                            <i class="fa fa-upload"></i> Post
                                        </a>
                                        <a href="<?php echo admin_url('entries/salary_delete/' . $run['id']); ?>"
                                           class="btn btn-xs btn-danger confirm-action"
                                           data-confirm="Delete salary run '<?php echo html_escape($run['run_name']); ?>'?"
                                           title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

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
