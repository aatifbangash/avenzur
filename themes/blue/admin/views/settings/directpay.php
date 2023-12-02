<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>

</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('Direct_Pay'); ?></h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown"><a href="<?= admin_url('system_settings/paypal') ?>" class="toggle_up"><i
                            class="icon fa fa-paypal"></i><span
                            class="padding-right-10"><?= lang('paypal'); ?></span></a></li>
                <li class="dropdown"><a href="<?= admin_url('system_settings/skrill') ?>" class="toggle_down"><i
                            class="icon fa fa-bank"></i><span class="padding-right-10"><?= lang('skrill'); ?></span></a>
                </li>
                 <li class="dropdown"><a href="<?= admin_url('system_settings/directPay') ?>" class="toggle_down"><i
                            class="icon fa fa-bank"></i><span class="padding-right-10"><?= lang('Direct_Pay'); ?></span></a>
                </li>
                 <li class="dropdown"><a href="<?= admin_url('system_settings/aramex') ?>" class="toggle_down"><i
                            class="icon fa fa-bank"></i><span class="padding-right-10"><?= lang('Aramex'); ?></span></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">

                <p class="introtext"><?= lang('update_info'); ?></p>

                <?php $attrib = ['role' => 'form', 'id="paypal_form"'];
                echo admin_form_open('system_settings/directPay', $attrib);
                ?>
                <div class="row">
                    <div class="col-md-6">
                    <?php    //$directPay->account_email ?>

                        <div class="form-group">
                            <?= lang('Merchant_Id', 'Merchant_Id'); ?>
                            <input type="text" name="merchant_id"  id="merchant_id" class="form-control tip" value="<?php   echo $directpay->merchant_id; ?>">
                            <!--<small class="help-block"><?= lang('merchant_id'); ?></small>-->
                        </div>

                        <div class="form-group">
                         
                            <?= lang('Authentication_token', 'Authentication_token'); ?>
                               <input type="text" name="authentication_token" id="authentication_token" class="form-control tip"  value="<?php   echo $directpay->authentication_token; ?>">
                            <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                        </div>
                        <div class="form-group">
                            <?= lang('Payment_link', 'Payment_link'); ?>
                             <input type="text" name="payment_link"  id="payment_link" class="form-control tip" value="<?php   echo $directpay->payment_link; ?>" >
                            <!--<small class="help-block"><?= lang('extra_charges_my_tip'); ?></small>-->
                        </div>
                        <div class="form-group">
                            <?= lang('Refund_link', 'Refund_link'); ?>
                            <input type="text" name="refund_link" id="refund_link"  class="form-control tip"  value="<?php   echo $directpay->refund_link; ?>">
                            <!--<small class="help-block"><?= lang('extra_charges_others_tip'); ?></small>-->
                        </div>

                        <div class="form-group">
                            <?= lang('Test_Merchant_Id', 'Test Merchant Id'); ?>
                            <input type="text" name="test_merchant_id"  id="test_merchant_id" class="form-control tip" value="<?php   echo $directpay->test_Merchant_id; ?>">
                            <!--<small class="help-block"><?= lang('merchant_id'); ?></small>-->
                        </div>

                        <div class="form-group">
                         
                            <?= lang('Test_Authentication_token', 'Test Authentication Token'); ?>
                               <input type="text" name="test_auth_token" id="test_auth_token" class="form-control tip"  value="<?php   echo $directpay->test_auth_token; ?>">
                            <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                        </div>
                         <div class="form-group">
                            <?= lang('Test_Payment_Link', 'Test_Payment_Link'); ?>
                             <input type="text" name="test_payment_link" id="test_payment_link" class="form-control tip" value="<?php   echo $directpay->test_payment_link; ?>" >
                            <!--<small class="help-block"><?= lang('extra_charges_my_tip'); ?></small>-->
                        </div>
                        <div class="form-group">
                            <?= lang('Test_Refund_Link', 'Test_Refund_Link'); ?>
                             <input type="text" name="test_refund_link" id="test_refund_link" class="form-control tip"  value="<?php   echo $directpay->test_refund_link; ?>">
                            <!--<small class="help-block"><?= lang('extra_charges_others_tip'); ?></small>-->
                        </div>
                        <div class="form-group">
                          
                       
                            <select name="activation" id="activation" class="form-control"  required="required" value=" ">
                                       <?php if($directpay->activation=="1"){ ?>
                                       
                                    <option value="1">Live</option>
                                    <option value="0">Sandbox</option>
                                    <?php } else{ ?>
                                            <option value="0">Sandbox</option>
                                             <option value="1">Live</option>
                                            <?php } ?>
                                                          
                             </select>
                      
                        </div>
                         <div class="form-group">
                            <?= lang('Version', 'Version'); ?>
                             <input type="text" name="version" id="version" class="form-control tip"  value="<?php   echo $directpay->version; ?>">
                            <!--<small class="help-block"><?= lang('extra_charges_others_tip'); ?></small>-->
                        </div>
                         <div class="form-group">
                            <?= lang('CurrencyISOCode', 'CurrencyISOCode'); ?>
                             <input type="text" name="currencyISOCode" id="currencyISOCode" class="form-control tip"  value="<?php   echo $directpay->currencyISOCode; ?>">
                            <!--<small class="help-block"><?= lang('extra_charges_others_tip'); ?></small>-->
                        </div>
                         <div class="form-group">
                            <?= lang('Payment Message id', 'Payment Message id'); ?>
                             <input type="text" name="payment_message_id" id="payment_message_id" class="form-control tip"  value="<?php   echo $directpay->payment_message_id; ?>">
                            <!--<small class="help-block"><?= lang('extra_charges_others_tip'); ?></small>-->
                        </div>
                      
                         <div class="form-group">
                            <?= lang('  Refund Message id', '  Refund Message id'); ?>
                             <input type="text" name="refund_message_id" id="refund_message_id" class="form-control tip"  value="<?php   echo $directpay->refund_message_id; ?>">
                            <!--<small class="help-block"><?= lang('extra_charges_others_tip'); ?></small>-->
                        </div>
                        <!--<div 
                        class="form-group">
                            <label><?= lang('ipn_link'); ?></label>
                            <span class="form-control" id="ipn_link"><?= admin_url('paypalipn'); ?></span>
                            <small class="help-block"><?= lang('ipn_link_tip'); ?></small>
                        </div>-->
                    </div>
                </div>
                <div style="clear: both; height: 10px;"></div>
                <div class="form-group">
                    <?php echo form_submit('update_settings', lang('update_settings'), 'class="btn btn-primary"'); ?>
                </div>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>