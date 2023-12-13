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
                                <button type="button" id="loginBtn" class="btn  text-white  " onclick="LoginFn(this);">Log in</button>
                                <button type="button" id="registerBtn" class="btn  text-white px-4 active" onclick="registerBtn(this);">Sign up</button>
                            </div>
                            <div id="registerBlock">
                                <?php 
                                    $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'registrationForm'];
                                    echo form_open('register', $attrib); 
                                ?>
                                <div class="controls logcardinput">
                                
                                <input type="email" id="email" name="email" class="form-control" placeholder="Please enter email" required="required"/>
                                
                                </div>

                                <button id="registerBtnCall" type="button" class="btn  text-white continueBtn" data-bs-toggle="modal" data-bs-target="#exampleModal">Continue</button>
                                <?= form_close(); ?>
                            </div>

                            <div id="loginBlock" style="display:none;">
                                <?php 
                                    $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'loginForm'];
                                    echo form_open('login', $attrib); 
                                ?>
                                <div class="controls logcardinput">
                                
                                <input type="text" id="identity" name="identity" class="form-control" placeholder="Please enter email or phone number" required="required" />
                                
                                </div>

                                <button id="loginBtnCall" type="button" class="btn  text-white continueBtn" data-bs-toggle="modal" data-bs-target="#exampleModal">Continue</button>
                                <?= form_close(); ?>
                            </div>

                            
                            <!-- Modal Starts -->
                            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content px-4 rounded-4">
                                        <div class="modal-header border-0">
                                            
                                            <button type="button" class="modalcloseBtn" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                                        </div>
                                        <div class="modal-body ">
                                            <div class="smsOTP">
                                                <div class="text-center px-5">
                                                    <h2>Verify your phone</h2>
                                                    <h5 class="fs-4 px-5 lh-base">OTP has been sent to +921234567 Via SMS</h5>
                                                </div>
                                                <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2"> 
                                                    <input class="m-1 text-center form-control rounded" type="text" id="first" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" id="second" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" id="third" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" id="fourth" maxlength="1" /> 
                                                    <input class="m-1 text-center form-control rounded" type="text" id="fifth" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" id="sixth" maxlength="1" />
                                                </div>
                                                <div  class="text-center">
                                                    <h6 class="m-0 mt-2">0.13 <span class="ms-2 fw-semibold opacity-50">Resend OTP Via SMS</span></h6>
                                                </div>
                                            </div>

                                            <div class="emailOTP">
                                                <div class="text-center px-5">
                                                    <h2>Verify your email</h2>
                                                    <h5 class="fs-4 px-5 lh-base">OTP has been sent to example@gmail.com</h5>
                                                </div>
                                                <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2"> 
                                                    <input class="m-1 text-center form-control rounded" type="text" id="first" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" id="second" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" id="third" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" id="fourth" maxlength="1" /> 
                                                    <input class="m-1 text-center form-control rounded" type="text" id="fifth" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" id="sixth" maxlength="1" />
                                                </div>
                                                <div  class="text-center">
                                                    <h6 class="m-0 mt-2">0.13 <span class="ms-2 fw-semibold opacity-50">Resend OTP </span></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pb-4">
                                        
                                        <button type="button" class="btn  text-white continueBtn rounded w-75 mx-auto mt-0" data-bs-toggle="modal" data-bs-target="#exampleModal">Login</button>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                <!-- Modal Ends -->
                            </div>
                        </div>
                </div>
                
            </div>
        </div>
    </div>
</section>
 <script>
    // Vanilla Javascript

    function LoginFn(obj){ 
        $('#loginBtn').addClass("active");
        $('#registerBtn').removeClass("active");
        $('#loginBlock').show();
        $('#registerBlock').hide();
    }

    function registerBtn(obj){
        $('#loginBtn').removeClass("active");
        $('#registerBtn').addClass("active");
        $('#loginBlock').hide();
        $('#registerBlock').show();
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