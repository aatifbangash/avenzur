<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="box">
    
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php $attrib = ['data-toggle' => 'validator', 'role' => 'form', 'autocomplete' => 'off'];
                echo admin_form_open_multipart('system_settings/add_ledgers', $attrib);
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <fieldset class="scheduler-border">
                            <legend class="scheduler-border"><?= lang('Accounts Ledgers') ?></legend>
                           
                            <div class="col-md-4">
                                <div class="form-group">
                                <label class="control-label" for="vat_on_purchase_ledger"><?= lang('Vat On Purchase'); ?></label>
                                <?php 
                                    echo form_dropdown('vat_on_purchase_ledger', $LO,'', 'id="sale_ledger_account" class="ledger-dropdown form-control" required="required"',$DIS);  
                                ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="vat_on_sale_ledger"><?= lang('Vat On Sale'); ?></label>
                                    <?php 
                                        echo form_dropdown('vat_on_sale_ledger', $LO,'', 'id="purchase_ledger_account" class="ledger-dropdown form-control" required="required"',$DIS);  
                                    ?>
                                    
                                </div>
                            </div>
                        
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="bank_fund_cash_ledger"><?= lang('Bank Fund Cash'); ?></label>
                                    <?php 
                                        echo form_dropdown('bank_fund_cash_ledger', $LO,'', 'id="bank_fund_cash_ledger" class="ledger-dropdown form-control" required="required"',$DIS);  
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="bank_fees_ledger"><?= lang('Bank Fees'); ?></label>
                                    <?php 
                                        echo form_dropdown('bank_fees_ledger', $LO,'', 'id="bank_fees_ledger" class="ledger-dropdown form-control" required="required"',$DIS);  
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label" for="bank_checking_account_ledger"><?= lang('Bank Checking Account'); ?></label>
                                    <?php 
                                        echo form_dropdown('bank_checking_account_ledger', $LO,'', 'id="bank_checking_account_ledger" class="ledger-dropdown form-control" required="required"',$DIS);  
                                    ?>
                                </div>
                            </div>
                         </fieldset>

                    </div>
                </div>
                <div class="cleafix"></div>
                <div class="form-group">
                    <div class="controls">
                        <?= form_submit('update_settings', lang('update_settings'), 'class="btn btn-primary btn-lg"'); ?>
                    </div>
                </div>
            <?= form_close(); ?>
        </div>
    </div>
    
</div>
</div>

