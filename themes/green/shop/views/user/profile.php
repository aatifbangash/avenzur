<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents" id="user-profile-section">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-sm-9 col-md-12">

                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#user" aria-controls="user" role="tab" data-toggle="tab"><?= lang('details'); ?></a></li>
                            <li role="presentation"><a href="#password" aria-controls="password" role="tab" data-toggle="tab"><?= lang('change_password'); ?></a></li>
                        </ul>

                        <div class="tab-content padding-lg white bordered-light" style="margin-top:-1px;">
                            <div role="tabpanel" class="tab-pane fade in active" id="user">

                                <!--<p><?php //echo lang('fill_form'); ?></p>-->
                                <?= form_open('profile/user', 'class="validate" id="user-profile"'); ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('first_name', 'first_name'); ?>
                                            <?= form_input('first_name', set_value('first_name', $user->first_name), 'class="form-control tip" id="first_name" required="required"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('last_name', 'last_name'); ?>
                                            <?= form_input('last_name', set_value('last_name', $user->last_name), 'class="form-control tip" id="last_name" required="required"'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('phone', 'phone'); ?>
                                            <?= form_input('phone', set_value('phone', $customer->phone), 'class="form-control tip" id="phone" required="required"'); ?>
                                            <?php 
                                                if($customer->mobile_verified == 1){
                                                    echo '<a style="color: green;">Verified Number</a>';
                                                }else{
                                                    echo '<a style="color: blue;text-decoration: underline;cursor: pointer;" onclick="verifyNumber();">Verify Number</a>';
                                                }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('email', 'email'); ?>
                                            <?= form_input('email', set_value('email', $customer->email), 'class="form-control tip" id="email" required="required"'); ?>
                                        </div>
                                    </div>
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

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('company', 'company'); ?>
                                            <?= form_input('company', set_value('company', $customer->company), 'class="form-control tip" id="company"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('vat_no', 'vat_no'); ?>
                                            <?= form_input('vat_no', set_value('vat_no', $customer->vat_no), 'class="form-control tip" id="vat_no"'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('billing_address', 'address'); ?>
                                            <?= form_input('address', set_value('address', $customer->address), 'class="form-control tip" id="address" required="required"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('city', 'city'); ?>
                                            <?= form_input('city', set_value('city', $customer->city), 'class="form-control tip" id="city" required="required"'); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('state', 'state'); ?>
                                            <?php
                                            if ($Settings->indian_gst) {
                                                $states = $this->gst->getIndianStates(true);
                                                echo form_dropdown('state', $states, set_value('state', $customer->state), 'class="form-control selectpicker mobile-device" id="state" required="required"');
                                            } else {
                                                echo form_input('state', set_value('state', $customer->state), 'class="form-control" id="state"');
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('postal_code', 'postal_code'); ?>
                                            <?= form_input('postal_code', set_value('postal_code', $customer->postal_code), 'class="form-control tip" id="postal_code" required="required"'); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('country', 'country'); ?>
                                            <?php  //var_dump($customer);?>
                                            
                                             <select class="form-control" id="country" name="country"  required="required">
                                                              
                                                       
                                                      <?php
                                                                   foreach($country as $u)
                                                                   {
                                                                       if($customer->country==$u->code){
                                                                    echo '<option value="'.$u->code.'" selected="selected">'.$u->name.'</option>';
                                                                   }
                                                                  else
                                                                  {
                                                                      echo '<option value="'.$u->code.'" >'.$u->name.'</option>';
                                                                  }
                                                                   }
                                                                   
                                                                  ?>
                                                        
                                                        
                                                       </select>
                                        </div>
                                    </div>
                                </div>

                                <?= form_submit('billing', lang('update'), 'class="btn btn-lg btn-primary"'); ?>
                                <?php echo form_close(); ?>

                            </div>

                            <div role="tabpanel" class="tab-pane fade" id="password">
                                <!--<p><?php //echo lang('fill_form'); ?></p>-->
                                <?= form_open('profile/password', 'class="validate" id="user-change-password"'); ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <?= lang('current_password', 'old_password'); ?>
                                            <?= form_password('old_password', set_value('old_password'), 'class="form-control tip" id="old_password" required="required"'); ?>
                                        </div>

                                        <div class="form-group">
                                            <?= lang('new_password', 'new_password'); ?>
                                            <?= form_password('new_password', set_value('new_password'), 'class="form-control tip" id="new_password" required="required" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" data-fv-regexp-message="' . lang('pasword_hint') . '"'); ?>
                                        </div>

                                        <div class="form-group">
                                            <?= lang('confirm_password', 'new_password_confirm'); ?>
                                            <?= form_password('new_password_confirm', set_value('new_password_confirm'), 'class="form-control tip" id="new_password_confirm" required="required" data-fv-identical="true" data-fv-identical-field="new_password" data-fv-identical-message="' . lang('pw_not_same') . '"'); ?>
                                        </div>

                                        <?= form_submit('change_password', lang('change_password'), 'class="btn btn-lg btn-primary"'); ?>
                                    </div>
                                </div>
                                <?php echo form_close(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-3 col-md-2">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">

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
                    document.getElementById('identifier').value = document.getElementById('email').value;
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

    function verifyNumber(){
        var csrf_token = '<?= $this->security->get_csrf_hash(); ?>';
        $.ajax({
            type: 'GET',
            url: '<?= base_url(); ?>verify_phone',
            success: function (response) {
                var respObj = JSON.parse(response);
                if (respObj.status == 'success' || respObj.code == 1) {
                    $('#registerOTP').off('click', handleRegisterOTPClick);
                    document.getElementById('registerOTP').style.color = 'grey';
                    document.getElementById('registerOTP').style.cursor = 'none';
                    $('#registerModal').modal('show');
                    document.getElementById('identifier').value = document.getElementById('phone').value;
                    document.getElementById('identifier_input').value = document.getElementById('phone').value;

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
</script>
