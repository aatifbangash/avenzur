<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents">
    <div class="container container-max-width register-k">
        <div class="row">
            <div class="col-xs-12">
                
                    <div class="row">
                    <div class="col-sm-9 col-md-12">

                        <div class="tab-content padding-lg white bordered-light"  style="margin-top:-1px;">






                            <!-- Paste here -->







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
                            <button type="button" class="btn  text-white  active" onclick="LoginFn(this);" >Log in</button>
                            <button type="button" class="btn  text-white px-4" onclick="registerBtn(this);" >Sign up</button>
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
        alert('Login Now');
        //$(".register").hide();
        //$("#register").hide();
        //$(".loginform").show();
    }

    function registerBtn(){
        alert('Register Now');
        //$(".loginform").hide();
        //$(".register").show();
        //$("#register").show();  
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