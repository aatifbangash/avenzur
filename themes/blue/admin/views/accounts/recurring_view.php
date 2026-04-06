<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
    $countPosted = count($items);
    $totalPosted = array_sum(array_column($items, 'amount'));
?>
<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-8">
                <h2 class="box-title">
                    <i class="fa fa-file-text-o"></i>
                    <?php echo html_escape($schedule['name']); ?>
                    &nbsp;
                    <span class="label label-primary"><?php echo ucfirst($schedule['type']); ?> JV</span>
                    &nbsp;
                    <span class="label label-default"><?php echo ucfirst($schedule['period_type']); ?></span>
                </h2>
            </div>
            <div class="col-md-4 text-right">
                <a href="<?php echo admin_url('entries/recurring_post_voucher/' . $schedule['id']); ?>" class="btn btn-success btn-sm">
                    <i class="fa fa-plus-circle"></i> Add New Voucher
                </a>
                <a href="<?php echo admin_url('entries/recurring_index'); ?>" class="btn btn-default btn-sm">
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

        <!-- Summary info -->
        <div class="row" style="margin-bottom:18px;">
            <div class="col-sm-3 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-teal"><i class="fa fa-list-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Debit Accounts</span>
                        <span class="info-box-number"><?php echo count($debit_lines); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-orange"><i class="fa fa-list-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Credit Accounts</span>
                        <span class="info-box-number"><?php echo count($credit_lines); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Vouchers Posted</span>
                        <span class="info-box-number"><?php echo $countPosted; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-purple"><i class="fa fa-money"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Posted</span>
                        <span class="info-box-number"><?php echo number_format($totalPosted, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template Account Lines -->
        <div class="row">
            <!-- Debit Accounts (green) -->
            <div class="col-md-6">
                <div class="panel" style="border-color:#c3e6cb;">
                    <div class="panel-heading" style="background:#d4edda;color:#155724;border-color:#c3e6cb;">
                        <strong><i class="fa fa-arrow-circle-right"></i> Debit Accounts</strong>
                        <span class="badge" style="background:#28a745;margin-left:8px;"><?php echo count($debit_lines); ?></span>
                    </div>
                    <div class="panel-body" style="padding:0">
                        <?php if (empty($debit_lines)): ?>
                            <p class="text-muted" style="padding:12px;">No debit accounts defined.</p>
                        <?php else: ?>
                        <table class="table table-condensed table-hover" style="margin:0">
                            <thead style="background:#e9f7ec;color:#155724;">
                                <tr>
                                    <th width="40">#</th>
                                    <th>Account #</th>
                                    <th>Account Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($debit_lines as $i => $line): ?>
                                    <tr>
                                        <td class="text-muted"><?php echo $i + 1; ?></td>
                                        <td><code><?php echo html_escape($line['ledger_code']); ?></code></td>
                                        <td><?php echo html_escape($line['ledger_name']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Credit Accounts (yellow/orange) -->
            <div class="col-md-6">
                <div class="panel" style="border-color:#ffecb5;">
                    <div class="panel-heading" style="background:#fff3cd;color:#856404;border-color:#ffecb5;">
                        <strong><i class="fa fa-arrow-circle-left"></i> Credit Accounts</strong>
                        <span class="badge" style="background:#ffc107;color:#333;margin-left:8px;"><?php echo count($credit_lines); ?></span>
                    </div>
                    <div class="panel-body" style="padding:0">
                        <?php if (empty($credit_lines)): ?>
                            <p class="text-muted" style="padding:12px;">No credit accounts defined.</p>
                        <?php else: ?>
                        <table class="table table-condensed table-hover" style="margin:0">
                            <thead style="background:#fffaec;color:#7a5a00;">
                                <tr>
                                    <th width="40">#</th>
                                    <th>Account #</th>
                                    <th>Account Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($credit_lines as $i => $line): ?>
                                    <tr>
                                        <td class="text-muted"><?php echo $i + 1; ?></td>
                                        <td><code><?php echo html_escape($line['ledger_code']); ?></code></td>
                                        <td><?php echo html_escape($line['ledger_name']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posted Vouchers list -->
        <h4 style="margin-top:24px;"><i class="fa fa-list-ol"></i> Posted Vouchers</h4>
        <?php if (empty($items)): ?>
            <div class="callout callout-info">
                <p>No vouchers posted yet. <a href="<?php echo admin_url('entries/recurring_post_voucher/' . $schedule['id']); ?>">Post the first voucher &rarr;</a></p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Voucher Date</th>
                        <th class="text-right">Total Amount (Dr = Cr)</th>
                        <th>Journal Entry</th>
                        <th>Posted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="text-center"><?php echo $item['period_number']; ?></td>
                            <td><?php echo $item['due_date']; ?></td>
                            <td class="text-right"><strong><?php echo number_format($item['amount'], 2); ?></strong></td>
                            <td>
                                <?php if ($item['entry_id']): ?>
                                    <a href="<?php echo admin_url('entries/view/journal/' . $item['entry_id']); ?>" target="_blank" class="text-success">
                                        <i class="fa fa-file-text-o"></i> Entry #<?php echo $item['entry_id']; ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?php echo $item['posted_at'] ?: '—'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="active">
                        <th colspan="2" class="text-right">Total</th>
                        <th class="text-right"><?php echo number_format($totalPosted, 2); ?></th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>

        <!-- Template meta info -->
        <div class="row" style="margin-top:20px;">
            <div class="col-md-4">
                <table class="table table-condensed">
                    <tr><th>Default Narration</th><td><?php echo html_escape($schedule['narration'] ?: ''); ?></td></tr>
                    <tr><th>Description</th><td><?php echo html_escape($schedule['description'] ?: ''); ?></td></tr>
                    <tr><th>Status</th>
                        <td><?php
                            $sc = ['active'=>'success','completed'=>'info','cancelled'=>'danger'];
                            echo '<span class="label label-' . ($sc[$schedule['status']] ?? 'default') . '">' . ucfirst($schedule['status']) . '</span>';
                        ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-8 text-right" style="padding-top:10px;">
                <a href="<?php echo admin_url('entries/recurring_delete/' . $schedule['id']); ?>"
                   class="btn btn-danger btn-sm confirm-action"
                   data-confirm="Delete this JV template? Posted journal entries will NOT be affected.">
                    <i class="fa fa-trash"></i> Delete Template
                </a>
            </div>
        </div>

    </div>
</div>
