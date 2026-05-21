<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="box">
    <div class="box-header">
        <h2 class="red"><i class="fa fa-fw fa fa-exclamation-triangle"></i><?= lang('Credit Limit Override') ?></h2>
    </div>
    <div class="box-content">

        <div class="alert alert-danger">
            <h4><i class="fa fa-ban"></i> <?= lang('Customer Credit Limit Exceeded') ?></h4>
            <table class="table table-condensed" style="margin-top:10px; margin-bottom:0; background:transparent;">
                <tr>
                    <th style="border:none; width:220px;"><?= lang('Customer') ?></th>
                    <td style="border:none;"><strong><?= htmlspecialchars($customer_name) ?></strong></td>
                </tr>
                <tr>
                    <th style="border:none;"><?= lang('Credit Limit') ?></th>
                    <td style="border:none;"><?= number_format($credit_limit, 2) ?></td>
                </tr>
                <tr>
                    <th style="border:none;"><?= lang('Outstanding Balance') ?></th>
                    <td style="border:none;"><strong><?= number_format($pending_amount, 2) ?></strong></td>
                </tr>
                <tr>
                    <th style="border:none;"><?= lang('Quote') ?> #</th>
                    <td style="border:none;"><?= htmlspecialchars($quote->id) ?></td>
                </tr>
            </table>
        </div>

        <div class="alert alert-warning">
            <i class="fa fa-info-circle"></i>
            <?= lang('As a Trade Manager you may override this restriction. You must provide a justification note which will be recorded against this quote.') ?>
        </div>

        <?= form_open(admin_url('sales/add_from_quote/' . $quote->id)) ?>
            <div class="form-group">
                <label for="trade_note"><strong><?= lang('Override Justification Note') ?> <span class="required">*</span></strong></label>
                <textarea name="trade_note" id="trade_note" class="form-control" rows="4"
                    placeholder="<?= lang('Enter reason for overriding the credit limit...') ?>"
                    required><?= $this->input->post('trade_note') ? htmlspecialchars($this->input->post('trade_note')) : '' ?></textarea>
                <span class="help-block"><?= lang('This note will be saved to the quote record for audit purposes.') ?></span>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-danger">
                    <i class="fa fa-check"></i> <?= lang('Override &amp; Convert to Sale') ?>
                </button>
                <a href="<?= admin_url('quotes') ?>" class="btn btn-default">
                    <i class="fa fa-times"></i> <?= lang('Cancel') ?>
                </a>
            </div>
        <?= form_close() ?>

    </div>
</div>
