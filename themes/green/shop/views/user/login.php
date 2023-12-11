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
                                <button type="button" class="btn btn-primary" id="registerBtn">Register</button>
                                <a id="login" onclick="LoginFn()" name="login" value="Login" class="btn btn-secondary">Already have an account</a>
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
    /*window.intlTelInput(input,({
        initialCountry: "<?php //echo trim($country_code); ?>"
    }));*/

    window.intlTelInput(input,({
        initialCountry: "SA"
    }));
    $('#phone').val("+966");

    $(document).ready(function() {
        $('.iti__flag-container').click(function() { 
          var countryCode = $('.iti__selected-flag').attr('title');
          var countryCode = countryCode.replace(/[^0-9]/g,'')
          $('#phone').val("");
          $('#phone').val("+"+countryCode+" "+ $('#phone').val());
       });

       $('#registrationForm').click(function (e) {
            e.preventDefault(); // Prevent the default form submission

            // Serialize the form data
            var formData = $('#registrationForm').serialize();

            // Send the data to the server using AJAX
            $.ajax({
                type: 'POST',
                url: $('#registrationForm').attr('action'),
                data: formData,
                success: function (response) {
                    // Handle the success response
                    console.log(response);
                },
                error: function (error) {
                    // Handle the error response
                    console.error(error);
                }
            });
        });
    });
         

  </script>