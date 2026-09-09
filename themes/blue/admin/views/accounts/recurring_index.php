<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-8">
                <h2 class="box-title">
                    <i class="fa fa-file-text-o"></i> Recurring JV Templates
                    <small class="text-muted">&mdash; Depreciation, Amortization &amp; Custom</small>
                </h2>
            </div>
            <div class="col-md-4 text-right">
                <a href="<?php echo admin_url('entries/recurring_add'); ?>" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus-circle"></i> Add JV Template
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
            <table class="table table-bordered table-striped table-hover" id="schedulesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Voucher Type</th>
                        <th>Template Name</th>
                        <th class="text-center">Accounts</th>
                        <th class="text-center">Vouchers Posted</th>
                        <th>Frequency</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($schedules)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted" style="padding:30px;">
                                <i class="fa fa-inbox fa-2x"></i><br><br>
                                No JV templates found.
                                <a href="<?php echo admin_url('entries/recurring_add'); ?>">Create the first template &rarr;</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($schedules as $s): ?>
                            <tr>
                                <td><?php echo $s['id']; ?></td>
                                <td>
                                    <?php
                                        $typeLabels = [
                                            'depreciation' => ['label-warning', 'Depreciation JV'],
                                            'amortization' => ['label-info',    'Amortization JV'],
                                            'accrual'      => ['label-primary', 'Accrual JV'],
                                            'prepaid'      => ['label-default', 'Prepaid JV'],
                                            'custom'       => ['label-default', 'Custom JV'],
                                        ];
                                        $tl = $typeLabels[$s['type']] ?? ['label-default', ucfirst($s['type']) . ' JV'];
                                    ?>
                                    <span class="label <?php echo $tl[0]; ?>"><?php echo $tl[1]; ?></span>
                                </td>
                                <td><strong><?php echo html_escape($s['name']); ?></strong>
                                    <?php if ($s['description']): ?>
                                        <br><small class="text-muted"><?php echo html_escape($s['description']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-green" title="Account lines defined"><?php echo $s['line_count']; ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($s['posted_count'] > 0): ?>
                                        <span class="badge bg-blue"><?php echo $s['posted_count']; ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo ucfirst($s['period_type']); ?></td>
                                <td>
                                    <?php
                                        $statusMap = ['active'=>'label-primary','completed'=>'label-success','cancelled'=>'label-danger'];
                                        $cls = $statusMap[$s['status']] ?? 'label-default';
                                    ?>
                                    <span class="label <?php echo $cls; ?>"><?php echo ucfirst($s['status']); ?></span>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('entries/recurring_view/' . $s['id']); ?>"
                                       class="btn btn-xs btn-default" title="View Template">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                    <a href="<?php echo admin_url('entries/recurring_post_voucher/' . $s['id']); ?>"
                                       class="btn btn-xs btn-success" title="Post New Voucher">
                                        <i class="fa fa-upload"></i> Add Entry
                                    </a>
                                    <a href="<?php echo admin_url('entries/recurring_delete/' . $s['id']); ?>"
                                       class="btn btn-xs btn-danger confirm-action" title="Delete Template"
                                       data-confirm="Delete template '<?php echo html_escape($s['name']); ?>'?\nPosted journal entries will NOT be affected.">
                                        <i class="fa fa-trash"></i>
                                    </a>
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

        <!-- Filter buttons -->
        <div class="btn-group btn-group-sm mb-3" id="typeFilter" style="margin-bottom:12px;">
            <button class="btn btn-default active" data-filter="all">All</button>
            <button class="btn btn-info"    data-filter="amortization">Amortization</button>
            <button class="btn btn-warning" data-filter="depreciation">Depreciation</button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="schedulesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Asset Code</th>
                        <th>Total Amount</th>
                        <th>Method</th>
                        <th>Periods</th>
                        <th>Posted</th>
                        <th>Pending</th>
                        <th>Start Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($schedules)): ?>
                        <tr>
                            <td colspan="12" class="text-center text-muted">
                                <i class="fa fa-inbox fa-2x"></i><br>No recurring schedules found.
                                <a href="<?php echo admin_url('entries/recurring_add'); ?>">Create one now &rarr;</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($schedules as $s): ?>
                            <tr data-type="<?php echo $s['type']; ?>">
                                <td><?php echo $s['id']; ?></td>
                                <td>
                                    <?php if ($s['type'] === 'amortization'): ?>
                                        <span class="label label-info">Amortization</span>
                                    <?php else: ?>
                                        <span class="label label-warning">Depreciation</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo html_escape($s['name']); ?></strong></td>
                                <td><?php echo html_escape($s['asset_code']); ?></td>
                                <td class="text-right">
                                    <?php echo number_format($s['total_amount'], 2); ?>
                                </td>
                                <td><?php echo ucwords(str_replace('_', ' ', $s['depreciation_method'] ?: 'straight_line')); ?></td>
                                <td class="text-center"><?php echo $s['periods']; ?></td>
                                <td class="text-center">
                                    <span class="badge bg-green"><?php echo $s['posted_count']; ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-yellow"><?php echo $s['pending_count']; ?></span>
                                </td>
                                <td><?php echo $s['start_date']; ?></td>
                                <td>
                                    <?php
                                        $statusMap = [
                                            'active'    => 'label-primary',
                                            'completed' => 'label-success',
                                            'paused'    => 'label-warning',
                                            'cancelled' => 'label-danger',
                                        ];
                                        $cls = $statusMap[$s['status']] ?? 'label-default';
                                    ?>
                                    <span class="label <?php echo $cls; ?>"><?php echo ucfirst($s['status']); ?></span>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('entries/recurring_view/' . $s['id']); ?>"
                                       class="btn btn-xs btn-primary" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <?php if ($s['pending_count'] > 0): ?>
                                        <a href="<?php echo admin_url('entries/recurring_post_all/' . $s['id']); ?>"
                                           class="btn btn-xs btn-success confirm-post" title="Post All Pending"
                                           data-confirm="Post ALL <?php echo $s['pending_count']; ?> pending period(s) for '<?php echo html_escape($s['name']); ?>'?">
                                            <i class="fa fa-check-circle"></i> Post All
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($s['posted_count'] == 0): ?>
                                        <a href="<?php echo admin_url('entries/recurring_delete/' . $s['id']); ?>"
                                           class="btn btn-xs btn-danger confirm-action" title="Delete"
                                           data-confirm="Delete schedule '<?php echo html_escape($s['name']); ?>'? This cannot be undone.">
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
    // Type filter
    $('#typeFilter button').on('click', function () {
        $('#typeFilter button').removeClass('active');
        $(this).addClass('active');
        var filter = $(this).data('filter');
        if (filter === 'all') {
            $('#schedulesTable tbody tr').show();
        } else {
            $('#schedulesTable tbody tr').hide();
            $('#schedulesTable tbody tr[data-type="' + filter + '"]').show();
        }
    });

    // Confirm dialogs
    $(document).on('click', '.confirm-post, .confirm-action', function (e) {
        var msg = $(this).data('confirm') || 'Are you sure?';
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });
});
</script>
