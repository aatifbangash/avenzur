<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<script>

</script>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cog"></i><?= lang('Aramex'); ?></h2>

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
                    echo admin_form_open('system_settings/aramex', $attrib);
                    ?>
                    <div class="row">
                        <div class="col-md-4">
                             <?php    //$directPay->account_email ?>
    
                            <div class="form-group">
                                <?= lang('line1', 'line1'); ?>
                                <input type="text" name="line1"  id="line1" class="form-control tip" value="<?php   echo $aramex->line1; ?>">
                                <!--<small class="help-block"><?= lang('merchant_id'); ?></small>-->
                            </div>
                        </div>
                        <div class="col-md-4">
                                <div class="form-group">
                                 
                                    <?= lang('line_2', 'lline_2'); ?>
                                       <input type="text" name="line2" id="line2" class="form-control tip"  value="<?php   echo $aramex->line2; ?>">
                                    <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                </div>
                                
                        </div>
                        <div class="col-md-4">
                                                <div class="form-group">
                                                 
                                                    <?= lang('city', 'city'); ?>
                                                       <input type="text" name="city" id="city" class="form-control tip"  value="<?php   echo $aramex->city; ?>">
                                                    <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                                </div>
                                                
                        </div>
                </div>
                    <div class="row">
                        <div class="col-md-4">
                             <?php    //$directPay->account_email ?>
    
                            <div class="form-group">
                                <?= lang('postal_code', 'postal_code'); ?>
                                <input type="text" name="postal_code"  id="postal_code" class="form-control tip" value="<?php   echo $aramex->postal_code; ?>">
                                <!--<small class="help-block"><?= lang('merchant_id'); ?></small>-->
                            </div>
                        </div>
                        <div class="col-md-4">
                                <div class="form-group">
                                 
                                    <?= lang('country_code', 'country_code'); ?>
                                       <input type="text" name="country_code" id="country_code" class="form-control tip"  value="<?php   echo $aramex->country_code; ?>">
                                    <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                </div>
                                
                        </div>
                        <div class="col-md-4">
                                                <div class="form-group">
                                                 
                                                    <?= lang('person_name', 'person_name'); ?>
                                                       <input type="text" name="person_name" id="person_name" class="form-control tip"  value="<?php   echo $aramex->person_name; ?>">
                                                    <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                                </div>
                                                
                        </div>
                </div>
                 <div class="row">
                        <div class="col-md-4">
                             <?php    //$directPay->account_email ?>
    
                            <div class="form-group">
                                <?= lang('company_name', 'company_name'); ?>
                                <input type="text" name="company_name"  id="company_name" class="form-control tip" value="<?php   echo $aramex->company_name; ?>">
                                <!--<small class="help-block"><?= lang('merchant_id'); ?></small>-->
                            </div>
                        </div>
                        <div class="col-md-4">
                                <div class="form-group">
                                 
                                    <?= lang('landline_number', 'landline_number'); ?>
                                       <input type="text" name="landline_number" id="landline_number" class="form-control tip"  value="<?php   echo $aramex->landline_number; ?>">
                                    <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                </div>
                                
                        </div>
                        <div class="col-md-4">
                                                <div class="form-group">
                                                 
                                                    <?= lang('cell_number', 'cell_number'); ?>
                                                       <input type="text" name="cell_number" id="cell_number" class="form-control tip"  value="<?php   echo $aramex->cell_number; ?>">
                                                    <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                                </div>
                                                
                        </div>
                </div>
                <div class="row">
                        <div class="col-md-4">
                             <?php    //$directPay->account_email ?>
    
                            <div class="form-group">
                                <?= lang('Email', 'Email'); ?>
                                <input type="text" name="Email"  id="Email" class="form-control tip" value="<?php   echo $aramex->Email; ?>">
                                <!--<small class="help-block"><?= lang('merchant_id'); ?></small>-->
                            </div>
                        </div>
                        <div class="col-md-4">
                                <div class="form-group">
                                 
                                    <?= lang('account_entity', 'account_entity'); ?>
                                       <input type="text" name="account_entity" id="account_entity" class="form-control tip"  value="<?php   echo $aramex->account_entity; ?>">
                                    <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                </div>
                                
                        </div>
                        <div class="col-md-4">
                                                <div class="form-group">
                                                 
                                                    <?= lang('account_number', 'account_number'); ?>
                                                       <input type="text" name="account_number" id="account_number" class="form-control tip"  value="<?php   echo $aramex->account_number; ?>">
                                                    <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                                </div>
                                                
                        </div>
                </div>
                
                <div class="row">
                                <div class="col-md-4">
                                     <?php    //$directPay->account_email ?>
            
                                    <div class="form-group">
                                        <?= lang('account_pin', 'account_pin'); ?>
                                        <input type="text" name="account_pin"  id="account_pin" class="form-control tip" value="<?php   echo $aramex->account_pin; ?>">
                                        <!--<small class="help-block"><?= lang('merchant_id'); ?></small>-->
                                    </div>
                                </div>
                                <div class="col-md-4">
                                        <div class="form-group">
                                         
                                            <?= lang('user_name', 'user_name'); ?>
                                               <input type="text" name="user_name" id="user_name" class="form-control tip"  value="<?php   echo $aramex->user_name; ?>">
                                            <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                        </div>
                                        
                                </div>
                                <div class="col-md-4">
                                                        <div class="form-group">
                                                         
                                                            <?= lang('password', 'password'); ?>
                                                               <input type="text" name="password" id="password" class="form-control tip"  value="<?php   echo $aramex->password; ?>">
                                                            <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                                        </div>
                                                        
                                </div>
                        </div>
                        <div class="row">
                                <div class="col-md-4">
                                     <?php    //$directPay->account_email ?>
            
                                    <div class="form-group">
                                        <?= lang('version', 'version'); ?>
                                        <input type="text" name="version"  id="version" class="form-control tip" value="<?php   echo $aramex->version; ?>">
                                        <!--<small class="help-block"><?= lang('merchant_id'); ?></small>-->
                                    </div>
                                </div>
                                <div class="col-md-4">
                                <div class="form-group">
                                    <?= lang('Mode', 'Mode'); ?>
                                    <select name="activation" id="activation" class="form-control"  required="required" value=" ">
                                               <?php if($aramex->activation=="1"){ ?>
                                               
                                            <option value="1">Live</option>
                                            <option value="0">Sandbox</option>
                                            <?php } else{ ?>
                                                    <option value="0">Sandbox</option>
                                                     <option value="1">Live</option>
                                                    <?php } ?>
                                                                  
                                     </select>
                              
                                </div>
                            </div>

                        </div>
                        <div class="row">
                                <div class="col-md-6">
                                        <div class="form-group">
                                         
                                            <?= lang('shippment_url', 'shippment_url'); ?>
                                               <input type="text" name="shippment_url" id="shippment_url" class="form-control tip"  value="<?php   echo $aramex->shippment_url; ?>">
                                            <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                        </div>
                                        
                                </div>
                                <div class="col-md-6">
                                                        <div class="form-group">
                                                         
                                                            <?= lang('pickup_url', 'pickup_url'); ?>
                                                               <input type="text" name="pickup_url" id="pickup_url" class="form-control tip"  value="<?php   echo $aramex->pickup_url; ?>">
                                                            <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                                        </div>
                                                        
                                </div>
                        </div>
                        
                        
                         <div class="row">
                                <div class="col-md-6">
                                     <?php    //$directPay->account_email ?>
            
                                    <div class="form-group">
                                        <?= lang('test_shippment_url', 'test_shippment_url'); ?>
                                        <input type="text" name="test_shippment_url"  id="test_shippment_url" class="form-control tip" value="<?php   echo $aramex->test_shippment_url; ?>">
                                        <!--<small class="help-block"><?= lang('merchant_id'); ?></small>-->
                                    </div>
                                </div>
                                <div class="col-md-6">
                                        <div class="form-group">
                                         
                                            <?= lang('test_pickup_url', 'test_pickup_url'); ?>
                                               <input type="text" name="test_pickup_url" id="test_pickup_url" class="form-control tip"  value="<?php   echo $aramex->test_pickup_url; ?>">
                                            <!--<small class="help-block"><?= lang('fixed_charges_tip'); ?></small>-->
                                        </div>
                                        
                                </div>
                             
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