<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="red"><i class="fa fa-fw fa-times-circle"></i> Reject Quote</h2>
    </div>
    <div class="box-content">

        <div class="alert alert-warning">
            <table class="table table-condensed" style="margin:0; background:transparent;">
                <tr>
                    <th style="border:none; width:200px;"><?= lang('Customer') ?></th>
                    <td style="border:none;"><strong><?= htmlspecialchars($customer_name) ?></strong></td>
                </tr>
                <tr>
                    <th style="border:none;"><?= lang('Quote') ?> #</th>
                    <td style="border:none;"><?= htmlspecialchars($quote->reference_no ?? $quote->id) ?></td>
                </tr>
                <tr>
                    <th style="border:none;"><?= lang('Credit Limit') ?></th>
                    <td style="border:none;"><?= number_format($credit_limit, 2) ?></td>
                </tr>
                <tr>
                    <th style="border:none;"><?= lang('Outstanding Balance') ?></th>
                    <td style="border:none;"><strong class="text-danger"><?= number_format($current_balance, 2) ?></strong></td>
                </tr>
            </table>
        </div>

        <?= form_open(admin_url('quotes/reject_credit_hold/' . $quote->id)) ?>
            <div class="form-group">
                <label for="rejection_reason"><strong><?= lang('Reason for Rejection') ?> <span class="required">*</span></strong></label>
                <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4"
                    placeholder="<?= lang('Enter the reason for rejecting this quote...') ?>"
                    required><?= $this->input->post('rejection_reason') ? htmlspecialchars($this->input->post('rejection_reason')) : '' ?></textarea>
                <span class="help-block"><?= lang('This reason will be saved against the quote record.') ?></span>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-danger">
                    <i class="fa fa-times"></i> <?= lang('Confirm Rejection') ?>
                </button>
                <a href="<?= admin_url('quotes/credit_hold') ?>" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> <?= lang('Cancel') ?>
                </a>
            </div>
        <?= form_close() ?>

    </div>
</div>
