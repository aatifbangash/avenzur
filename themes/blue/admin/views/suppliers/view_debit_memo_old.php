<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-eye"></i><?= lang('View Debit Memo'); ?></h2>
        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a href="<?= admin_url('suppliers/list_debit_memo'); ?>" class="tip" title="<?= lang('Back to List') ?>">
                        <i class="fa fa-list"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                
                <div class="row">
                    <div class="col-lg-12">
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Date'); ?></label>
                                <p class="form-control-static"><strong><?= $memo_data->date; ?></strong></p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Reference No'); ?></label>
                                <p class="form-control-static"><strong><?= $memo_data->reference_no; ?></strong></p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Payment Amount'); ?></label>
                                <p class="form-control-static"><strong><?= $this->sma->formatMoney($memo_data->payment_amount); ?></strong></p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Supplier'); ?></label>
                                <p class="form-control-static"><strong><?php 
                                    $supplier = $this->site->getCompanyByID($memo_data->supplier_id);
                                    echo $supplier ? $supplier->company . ' (' . $supplier->name . ')' : 'N/A';
                                ?></strong></p>
                            </div>
                        </div>
                            
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Opposite Account'); ?></label>
                                <p class="form-control-static"><strong><?php 
                                    echo isset($ledger_options[$memo_data->ledger_account]) ? $ledger_options[$memo_data->ledger_account] : 'N/A';
                                ?></strong></p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('VAT Account'); ?></label>
                                <p class="form-control-static"><strong><?php 
                                    if($memo_data->vat_account){
                                        echo isset($ledger_options[$memo_data->vat_account]) ? $ledger_options[$memo_data->vat_account] : 'N/A';
                                    } else {
                                        echo 'N/A';
                                    }
                                ?></strong></p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('VAT %'); ?></label>
                                <p class="form-control-static"><strong><?= isset($memo_data->vat_percent) ? $memo_data->vat_percent . '%' : '0%'; ?></strong></p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Voucher Type'); ?></label>
                                <p class="form-control-static"><strong><?= ($memo_data->supplier_entry_type == 'D') ? 'Debit' : 'Credit'; ?></strong></p>
                            </div>
                        </div>
                        
                    </div>

                    <div class="col-lg-12">
                        <h4><?= lang('Memo Entries'); ?></h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-condensed table-hover">
                                <thead>
                                    <tr>
                                        <th><?= lang('#'); ?></th>
                                        <th><?= lang('Description'); ?></th>
                                        <th><?= lang('Payment Amount'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        if(isset($memo_entries_data) && !empty($memo_entries_data)){
                                            $count = 0;
                                            foreach($memo_entries_data as $entry){
                                                $count++;
                                            ?>
                                                <tr>
                                                    <td><?= $count; ?></td>
                                                    <td><?= $entry->description; ?></td>
                                                    <td><?= $this->sma->formatMoney($entry->payment_amount); ?></td>
                                                </tr>
                                            <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="3" class="text-center"><?= lang('No entries found'); ?></td>
                                            </tr>
                                            <?php
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                
                </div>

            </div>
        </div>
    </div>
</div>
