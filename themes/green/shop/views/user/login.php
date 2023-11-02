<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents">
    <div class="container container-max-width register-k">
        <div class="row">
            <div class="col-xs-12">
                
                    <div class="row">
                    <div class="col-sm-9 col-md-12">

                        <ul class="nav nav-tabs" role="tablist">
                            
                            <li role="" >  </li>
                            <?php if (!$shop_settings->private) {
    ?>
                            <!--<li role="presentation" class="active"  ><a href="#register" class="register" aria-controls="register" role="tab" data-toggle="tab"><?= lang('register'); ?></a></li>-->
                            <?php
} ?>
                        </ul>

                        <div class="tab-content padding-lg white bordered-light"  style="margin-top:-1px;">
                            <div class="loginform" style="display:none;" >
                            
                                <div class="row w-100 m-auto">
                                    <div class="col-sm-5">
                                        <div class="well margin-bottom-no">
                                          
                                         <?php include 'login_form.php'; ?>
                                        </div>
                                    </div>
                             <?php if (!$shop_settings->private) {
     ?>
                                    <div class="col-sm-7 px-md-5">
                                        <h4 class="title"><span><?= lang('register_new_account'); ?></span></h4>
                                        <p>
                                            <?= lang('register_account_info'); ?>
                                        </p>
                                        <a  class="btn btn-primary" id="registerbtn" onclick="registerBtn()" ><?= lang('register'); ?></a>
                                        
                                    </div>
                             <?php
 } ?>
                                </div>
                            </div>

                            <?php if (!$shop_settings->private) {
        ?>
                            <div id="register">
                                  
                                <?php $attrib = ['class' => 'validate', 'role' => 'form'];
                                                echo form_open('register', $attrib); ?>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('first_name', 'first_name'); ?>
                                            <div class="controls">
                                                <?= form_input('first_name', '', 'class="form-control" id="first_name" required="required" pattern=".{3,10}"'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('last_name', 'last_name'); ?>
                                            <div class="controls">
                                                <?= form_input('last_name', '', 'class="form-control" id="last_name" required="required"'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                        <?= lang('Country', 'Country'); ?>
                                        <select class="form-control" id="country" name="country" >
                                       
                                        <option value="AE">AE</option>
                                        <option value="">Select Country</option>
                                        <?php
                                            foreach($country as $country)
                                            {
                                                $selected = (trim($country->code) == trim($country_code)) ? 'selected' : '';
                                                echo '<option value="'.$country->code.'"' . $selected . '>'.$country->name.'</option>';
                                            }
                                        ?>                    
                                        </select>
                            

                                        </div>
                                    </div>
                                 
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('phone ', 'phone '); ?>
                                            <div class="controls">
                                                  <input type="tel" id="phone" name="phone" class="form-control" />
                                                <!--<?= form_input('phone', '', 'class="form-control" id="phone" '); ?>-->
                                            </div>
                                        </div>
                                    </div>
                               
                             
                                    
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('email', 'email'); ?>
                                            <div class="controls">
                                                <input type="email" id="email" name="email" class="form-control" required="required"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('username', 'username'); ?>
                                            <?= form_input('username', set_value('username'), 'class="form-control tip" id="username" required="required"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('password', 'passwordr'); ?>
                                            <div class="controls">
                                                <?= form_password('password', '', 'class="form-control tip" id="passwordr" required="required" pattern="[0-9a-zA-Z]{5,}"'); ?>
                                                <span class="help-block"><?= lang('pasword_hint'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('confirm_password', 'password_confirm'); ?>
                                            <div class="controls">
                                                <?= form_password('password_confirm', '', 'class="form-control" id="password_confirm" required="required" pattern="[0-9a-zA-Z]{5,}" data-bv-identical="true" data-bv-identical-field="password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="mt-2">
                                <input type="submit" name="register" class="btn btn-primary" id="register" value="Register" >
                                <!--<h5>For Login  <a href="<?= site_url('login_form.php'); ?>">Click Here</a></h5>-->
                                 <!--<?= form_submit('register', lang('register'), 'class="btn btn-primary"'); ?>-->
                               <a   id="login" onclick="LoginFn()" name="login" value="Login" class="btn btn-secondary">Already have an account</a>
                               </div>
                                <?= form_close(); ?>
                           
                            </div>
                            
                            <?php
    } ?>
                        </div>
                      
                      
                        
                    </div>

                    <div class="col-sm-3 col-md-2"></div>
                </div>
                
            </div>
        </div>
    </div>
</section>
 <script>
    // Vanilla Javascript

    function LoginFn(){ 
        $(".register").hide();
        $("#register").hide();
        $(".loginform").show();
    }

    function registerBtn(){
        $(".loginform").hide();
        $(".register").show();
        $("#register").show();  
    }

    var input = document.querySelector("#phone");
    window.intlTelInput(input,({
        initialCountry: "<?= trim($country_code); ?>"
    }));

    $(document).ready(function() {
        $('.iti__flag-container').click(function() { 
          var countryCode = $('.iti__selected-flag').attr('title');
          var countryCode = countryCode.replace(/[^0-9]/g,'')
          $('#phone').val("");
          $('#phone').val("+"+countryCode+" "+ $('#phone').val());
       });
 
         
    });
         

  </script>