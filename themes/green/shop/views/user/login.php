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

                                <button id="registerBtnCall" type="button" class="btn  text-white continueBtn" data-bs-toggle="modal">Continue</button>
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

                                <button id="loginBtnCall" type="button" class="btn  text-white continueBtn" data-bs-toggle="modal">Continue</button>
                                <?= form_close(); ?>
                            </div>


                            <!-- Register Modal Starts -->
                            <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content px-4 rounded-4">
                                        <div class="modal-header border-0">
                                            <button type="button" class="modalcloseBtn" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                                        </div>
                                        <div class="modal-body ">
                                            <div class="emailOTP">
                                                <div class="text-center px-5">
                                                    <h2>Verify your email</h2>
                                                    <h5 class="fs-4 px-5 lh-base">OTP has been sent to <span id="identifier"></span></h5>
                                                </div>
                                                <?php 
                                                    $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'registerOtpForm'];
                                                    echo form_open('register_otp', $attrib); 
                                                ?>
                                                <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2"> 
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part1" id="first" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part2" id="second" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part3" id="third" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part4" id="fourth" maxlength="1" /> 
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part5" id="fifth" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part6" id="sixth" maxlength="1" />
                                                    <input type="hidden" id="identifier_input" name="identifier_input" value="" />
                                                </div>
                                                <div  class="text-center">
                                                    <h6 class="m-0 mt-2"><span id="register-clock"></span> <span class="ms-2 fw-semibold opacity-50" id="registerOTP">Resend OTP </span></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pb-4">
                                            <button type="submit" id="registerOtpBtn" class="btn  text-white continueBtn rounded w-75 mx-auto mt-0" data-bs-toggle="modal" data-bs-target="#exampleModal">Login</button>
                                        </div>
                                        <?= form_close(); ?>
                                    </div>
                                </div>
                            </div>
                            <!-- Register Modal Ends -->

                            <!-- Login Modal Starts -->
                            <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content px-4 rounded-4">
                                        <div class="modal-header border-0">
                                            
                                            <button type="button" class="modalcloseBtn" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                                        </div>
                                        <div class="modal-body ">
                                            <div class="smsOTP">
                                                <div class="text-center px-5">
                                                    <h2>Verify your phone</h2>
                                                    <h5 class="fs-4 px-5 lh-base">OTP has been sent to <span id="identifierl"></span></h5>
                                                </div>
                                                <?php 
                                                    $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'loginOtpForm'];
                                                    echo form_open('login_otp', $attrib); 
                                                ?>
                                                <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2"> 
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part1" id="first" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part2" id="second" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part3" id="third" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part4" id="fourth" maxlength="1" /> 
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part5" id="fifth" maxlength="1" />
                                                    <input class="m-1 text-center form-control rounded" type="text" name="opt_part6" id="sixth" maxlength="1" />
                                                    <input type="hidden" id="identifierl_input" name="identifier_input" value="" />
                                                </div>
                                                
                                                <div  class="text-center">
                                                    <h6 class="m-0 mt-2"><span id="login-clock"></span> <span class="ms-2 fw-semibold opacity-50" id="loginOTP">Resend OTP Via SMS</span></h6>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="modal-footer border-0 pb-4">
                                            <button type="submit" id="loginOtpBtn" class="btn  text-white continueBtn rounded w-75 mx-auto mt-0">Login</button>
                                        </div>
                                        <?= form_close(); ?>
                                    </div>
                                </div>
                                </div>
                                <!-- Login Modal Ends -->
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

       function handleRegisterOTPClick(){
            var formData = $('#registrationForm').serialize();
            $.ajax({
                type: 'POST',
                url: $('#registrationForm').attr('action'),
                data: formData,
                success: function (response) {
                    var respObj = JSON.parse(response);
                    if (respObj.status == 'success' || respObj.code == 1) {
                        $('#registerOTP').off('click', handleRegisterOTPClick);
                        document.getElementById('registerOTP').style.color = 'grey';
                        document.getElementById('registerOTP').style.cursor = 'none';
                        $('#registerModal').modal('show');
                        document.getElementById('identifier').innerHTML = document.getElementById('email').value;
                        document.getElementById('identifier_input').value = document.getElementById('email').value;

                        const countdownDuration = 60; // Duration in seconds
                        const countdownDisplay = document.getElementById("register-clock");
                        
                        let timer = countdownDuration, minutes, seconds;
                        const intervalId = setInterval(function () {
                            minutes = parseInt(timer / 60, 10);
                            seconds = parseInt(timer % 60, 10);

                            countdownDisplay.textContent = minutes + "." + (seconds < 10 ? "0" : "") + seconds;

                            if (--timer < 0) {
                                clearInterval(intervalId);
                                document.getElementById('registerOTP').style.color = '#662d91';
                                document.getElementById('registerOTP').style.cursor = 'pointer';
                                $('#registerOTP').click(handleRegisterOTPClick);
                            }
                        }, 1000);

                    } else {
                        alert('Signup failed. Please try again.');
                    }
                },
                error: function (error) {
                    console.error(error);
                }
            });
       }

       $('#registerBtnCall').click(function (e) {
            e.preventDefault(); 

            var formData = $('#registrationForm').serialize();
            $.ajax({
                type: 'POST',
                url: $('#registrationForm').attr('action'),
                data: formData,
                success: function (response) {
                    var respObj = JSON.parse(response);
                    if (respObj.status == 'success' || respObj.code == 1) {
                        $('#registerOTP').off('click', handleRegisterOTPClick);
                        document.getElementById('registerOTP').style.color = 'grey';
                        document.getElementById('registerOTP').style.cursor = 'none';
                        $('#registerModal').modal('show');
                        document.getElementById('identifier').innerHTML = document.getElementById('email').value;
                        document.getElementById('identifier_input').value = document.getElementById('email').value;

                        const countdownDuration = 60; // Duration in seconds
                        const countdownDisplay = document.getElementById("register-clock");
                        
                        let timer = countdownDuration, minutes, seconds;
                        const intervalId = setInterval(function () {
                            minutes = parseInt(timer / 60, 10);
                            seconds = parseInt(timer % 60, 10);

                            countdownDisplay.textContent = minutes + "." + (seconds < 10 ? "0" : "") + seconds;

                            if (--timer < 0) {
                                clearInterval(intervalId);
                                document.getElementById('registerOTP').style.color = '#662d91';
                                document.getElementById('registerOTP').style.cursor = 'pointer';
                                $('#registerOTP').click(handleRegisterOTPClick);
                            }
                        }, 1000);

                    } else {
                        alert('Signup failed. Please try again.');
                    }
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
                    var respObj = JSON.parse(response);
                    if (respObj.status == 'success' || respObj.code == 1) {
                        $('#loginOTP').off('click', handleLoginOTPClick);
                        document.getElementById('loginOTP').style.color = 'grey';
                        document.getElementById('loginOTP').style.cursor = 'none';
                        $('#loginModal').modal('show');
                        document.getElementById('identifierl').innerHTML = document.getElementById('identity').value;
                        document.getElementById('identifierl_input').value = document.getElementById('identity').value;

                        const countdownDuration = 60; // Duration in seconds
                        const countdownDisplay = document.getElementById("login-clock");
                        
                        let timer = countdownDuration, minutes, seconds;
                        const intervalId = setInterval(function () {
                            minutes = parseInt(timer / 60, 10);
                            seconds = parseInt(timer % 60, 10);

                            countdownDisplay.textContent = minutes + "." + (seconds < 10 ? "0" : "") + seconds;

                            if (--timer < 0) {
                                clearInterval(intervalId);
                                document.getElementById('loginOTP').style.color = '#662d91';
                                document.getElementById('loginOTP').style.cursor = 'pointer';
                                $('#loginOTP').click(handleLoginOTPClick);
                            }
                        }, 1000);

                    } else {
                        alert('Login failed. Please try again.');
                    }
                },
                error: function (error) {
                    console.error(error);
                }
            });
        });

        function handleLoginOTPClick(){
            var formData = $('#loginForm').serialize();
            $.ajax({
                type: 'POST',
                url: $('#loginForm').attr('action'),
                data: formData,
                success: function (response) {
                    var respObj = JSON.parse(response);
                    if (respObj.status == 'success' || respObj.code == 1) {
                        $('#loginOTP').off('click', handleLoginOTPClick);
                        document.getElementById('loginOTP').style.color = 'grey';
                        document.getElementById('loginOTP').style.cursor = 'none';
                        $('#loginModal').modal('show');
                        document.getElementById('identifierl').innerHTML = document.getElementById('identity').value;
                        document.getElementById('identifierl_input').value = document.getElementById('identity').value;

                        const countdownDuration = 60; // Duration in seconds
                        const countdownDisplay = document.getElementById("login-clock");
                        
                        let timer = countdownDuration, minutes, seconds;
                        const intervalId = setInterval(function () {
                            minutes = parseInt(timer / 60, 10);
                            seconds = parseInt(timer % 60, 10);

                            countdownDisplay.textContent = minutes + "." + (seconds < 10 ? "0" : "") + seconds;

                            if (--timer < 0) {
                                clearInterval(intervalId);
                                document.getElementById('loginOTP').style.color = '#662d91';
                                document.getElementById('loginOTP').style.cursor = 'pointer';
                                $('#loginOTP').click(handleLoginOTPClick);
                            }
                        }, 1000);

                    } else {
                        alert('Login failed. Please try again.');
                    }
                },
                error: function (error) {
                    console.error(error);
                }
            });
        }

        /*$('#loginOtpBtn').click(function (e) {
            e.preventDefault(); 

            var formData = $('#loginOtpForm').serialize();
            $.ajax({
                type: 'POST',
                url: $('#loginOtpForm').attr('action'),
                data: formData,
                success: function (response) {
                    var respObj = JSON.parse(response);
                    if (respObj.status == 'success' || respObj.code == 1) {
                        alert('Login Success');
                    } else {
                        alert('Login failed. Please try again.');
                    }
                },
                error: function (error) {
                    console.error(error);
                }
            });
        });*/
    });
        
  </script>