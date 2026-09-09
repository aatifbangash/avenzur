<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .info-box {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .info-box-header {
        background: linear-gradient(to right, #3c8dbc, #5fa2d1);
        color: white;
        padding: 12px 15px;
        margin: -20px -20px 20px -20px;
        border-radius: 4px 4px 0 0;
        font-size: 16px;
        font-weight: 600;
    }
    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f4f4f4;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        flex: 0 0 200px;
        font-weight: 600;
        color: #555;
        display: flex;
        align-items: center;
    }
    .info-label i {
        margin-right: 8px;
        color: #3c8dbc;
        width: 20px;
        text-align: center;
    }
    .info-value {
        flex: 1;
        color: #333;
        font-size: 15px;
    }
    .amount-highlight {
        background: #f0f8ff;
        padding: 8px 12px;
        border-radius: 4px;
        border-left: 3px solid #3c8dbc;
        font-weight: 600;
        font-size: 16px;
        color: #2c5f7d;
    }
    .entries-table {
        margin-top: 0;
    }
    .entries-table table {
        background: white;
    }
    .entries-table thead {
        background: linear-gradient(to right, #3c8dbc, #5fa2d1);
        color: white;
    }
    .entries-table thead th {
        font-weight: 600;
        padding: 12px 8px;
        border: none;
    }
    .entries-table tbody td {
        padding: 10px 8px;
        vertical-align: middle;
    }
    .entries-table tbody tr:hover {
        background: #f9f9f9;
    }
</style>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-eye"></i><?= lang('View Supplier Memo'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?= admin_url('suppliers/list_debit_memo'); ?>" class="tip btn btn-primary btn-sm" title="<?= lang('Back to List') ?>">
                        <i class="fa fa-arrow-left"></i> <?= lang('Back to List'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        
        <!-- Basic Information -->
        <div class="info-box">
            <div class="info-box-header">
                <i class="fa fa-info-circle"></i> Basic Information
            </div>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fa fa-calendar"></i> <?= lang('Date'); ?>:
                </div>
                <div class="info-value"><?= $memo_data->date; ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fa fa-hashtag"></i> <?= lang('Reference No'); ?>:
                </div>
                <div class="info-value"><?= $memo_data->reference_no; ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fa fa-money"></i> <?= lang('Payment Amount'); ?>:
                </div>
                <div class="info-value">
                    <div class="amount-highlight"><?= $this->sma->formatMoney($memo_data->payment_amount); ?></div>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fa fa-file-text"></i> <?= lang('Voucher Type'); ?>:
                </div>
                <div class="info-value">
                    <span class="label label-<?= $memo_data->type == 'debit' ? 'danger' : 'success'; ?>">
                        <?= ucfirst($memo_data->type); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Supplier & Account Information -->
        <div class="info-box">
            <div class="info-box-header">
                <i class="fa fa-building"></i> Supplier & Account Details
            </div>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fa fa-truck"></i> <?= lang('Supplier'); ?>:
                </div>
                <div class="info-value">
                    <?php 
                        $supplier = $this->site->getCompanyByID($memo_data->supplier_id);
                        echo $supplier ? $supplier->company : 'N/A';
                    ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fa fa-book"></i> <?= lang('Opposite Account'); ?>:
                </div>
                <div class="info-value">
                    <?php echo isset($ledger_options[$memo_data->ledger_account]) ? $ledger_options[$memo_data->ledger_account] : 'N/A'; ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fa fa-percent"></i> <?= lang('VAT Account'); ?>:
                </div>
                <div class="info-value">
                    <?php 
                        if($memo_data->vat_account){
                            echo isset($ledger_options[$memo_data->vat_account]) ? $ledger_options[$memo_data->vat_account] : 'N/A';
                        } else {
                            echo 'N/A';
                        }
                    ?>
                </div>
            </div>
            
            <div class="info-row">
                <div class="info-label">
                    <i class="fa fa-calculator"></i> <?= lang('VAT %'); ?>:
                </div>
                <div class="info-value">
                    <?= $memo_data->vat_percent ? $memo_data->vat_percent . '%' : '0%'; ?>
                </div>
            </div>
        </div>

        <!-- Note -->
        <?php if($memo_data->note): ?>
        <div class="info-box">
            <div class="info-box-header">
                <i class="fa fa-sticky-note"></i> Note
            </div>
            <div style="padding: 10px 0;">
                <?= $memo_data->note; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Memo Entries -->
        <div class="info-box entries-table">
            <div class="info-box-header">
                <i class="fa fa-list"></i> Memo Entries
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th><?= lang('Description'); ?></th>
                            <th width="150" class="text-right"><?= lang('Amount'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        $i = 1;
                        foreach ($memo_entries_data as $entry): 
                            $total += $entry->amount;
                        ?>
                        <tr>
                            <td class="text-center"><?= $i++; ?></td>
                            <td><?= $entry->description; ?></td>
                            <td class="text-right"><strong><?= $this->sma->formatMoney($entry->amount); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: #f5f5f5;">
                            <td colspan="2" class="text-right"><strong><?= lang('Total'); ?>:</strong></td>
                            <td class="text-right"><strong style="color: #3c8dbc; font-size: 16px;"><?= $this->sma->formatMoney($total); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</div>
