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
                                <?php 
                                    $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'registrationForm'];
                                    echo form_open('register', $attrib); 
                                ?>
                                <div class="row">
                                    
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('email', 'email'); ?>
                                            <div class="controls">
                                                <input type="email" id="email" name="email" class="form-control" required="required"/>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="clearfix"></div>
                                </div>
                                <div class="mt-2">
                                <!--<input type="submit" name="register" class="btn btn-primary" id="registerBtn" value="Register" >-->
                                <button type="button" class="btn btn-primary" id="registerBtnCall">Register</button>
                                <a id="login" onclick="LoginFn()" name="login" value="Login" class="btn btn-secondary">Already have an account</a>
                               </div>
                                <?= form_close(); ?>
                            </div>
                            
                            <?php
                            } ?>
                        </div>
                      
                      
                        
                    </div>

                    <div class="col-sm-3 col-md-12">
                        <div class="loginRCard px-5">
                        <div class="logo-k mb-5"> 
                            <a class="navbar-brand" href="http://localhost/avenzur/">
                                <img src="http://localhost/avenzur/assets/uploads/logos/avenzur-logov2-024.png" alt="AVENZUR">
                            </a>
                        </div>
                        <h4 class="fw-bold letstart">Let's get started</h4>
                        <div class="logsignBtns mt-3 d-flex justify-content-center">
                            <button type="button" class="btn  text-white  active">Log in</button>
                            <button type="button" class="btn  text-white px-4">Sign up</button>
                        </div>
                        <div>
                            <div class="controls logcardinput">
                             
                            <input type="email" id="email" name="email" class="form-control" placeholder="Please enter mobile or phone number" required="required"/>
                            
                            </div>

                            <button type="button" class="btn  text-white continueBtn">Continue</button>
                        </div>

                        </div>
                    </div>
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

    /*var input = document.querySelector("#phone");

    window.intlTelInput(input,({
        initialCountry: "SA"
    }));
    $('#phone').val("+966");*/

    $(document).ready(function() {
        /*$('.iti__flag-container').click(function() { 
          var countryCode = $('.iti__selected-flag').attr('title');
          var countryCode = countryCode.replace(/[^0-9]/g,'')
          $('#phone').val("");
          $('#phone').val("+"+countryCode+" "+ $('#phone').val());
       });*/

       $('#registerBtnCall').click(function (e) {
            e.preventDefault(); 

            var formData = $('#registrationForm').serialize();
            $.ajax({
                type: 'POST',
                url: $('#registrationForm').attr('action'),
                data: formData,
                success: function (response) {
                    console.log(response);
                },
                error: function (error) {
                    console.error(error);
                }
            });
        });

        $('#loginBtnCall').click(function (e) {
            e.preventDefault(); 

            var formData = $('#loginForm').serialize();
            $.ajax({
                type: 'POST',
                url: $('#loginForm').attr('action'),
                data: formData,
                success: function (response) {
                    console.log(response);
                },
                error: function (error) {
                    console.error(error);
                }
            });
        });
    });
        
  </script>