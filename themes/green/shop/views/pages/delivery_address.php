<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .selected-address {
        border: 2px solid greenyellow !important;
    }
    .address-container {
    
    }
</style>

<section class="page-contents">

    <div class="container container-max-width">
        <div class="row">
            <div class="col-md-12">
                <div class="row checkoutbox-k">

                    <div class="col-sm-12">
                        <div class=" margin-top-lg checkLeftCol-k">
                        <h3 class="mb-3 fw-semibold"> <i class="fa fa-address margin-right-sm"></i>
                                <?= lang('select_delivery_address'); ?>
</h3>
                               
                          
                            <div class="">
                                <div class="row align-items-stretch">
                                <div class="col-lg-6 mb-4 ">
                                <div class="border rounded  p-3 default-address address-container h-100 " data-address-id="default"
                                    style="cursor: pointer;">
                                
                                        <div  class="d-flex justify-content-end"> 
                                            
                                        
                                        <a href="?action=editaddress&id=default" ><i
                                            class="bi bi-pencil  text-primary"></i></a>
                                        
                                        
                                        
                                        </div>
                                   
                                        <div class="d-flex">

                                            <div class="addressDetail d-flex align-items-start">
                                                <div class="addicon "><i class="bi bi-geo-alt  purpColor"></i></div>
                                                <div class="ms-3">
                                                    <p class="m-0 fs-6 fw-semibold">
                                                        <?= $address->first_name; ?>
                                                        <?= $address->last_name; ?> 
                                                        <?= $default_address->address; ?>,
                                                        <?= $default_address->city; ?> ,
                                                        <?= $default_address->state; ?>
                                                        <?= $default_address->country; ?>
                                                    </p>
                                                    
                                                    <p class="m-0 fs-6 fw-semibold"> +966
                                                        <span class="phone_number"><?= $default_address->phone; ?></span> <i
                                                            class="bi bi-check-circle-fill  purpColor"></i>
                                                    </p>
                                                    <span class="phone_verified" style="visibility:hidden;"><?= $default_address->mobile_verified; ?></span>
                                                </div>
                                            </div>

                                        </div>
                              
                                 </div>
</div>
                                <?php
                                if (!empty($addresses)) {
                                    $r = 1;
                                    foreach ($addresses as $address) {
                                        ?>
                                        <div class="col-lg-6  mb-4">
                                        <div class="border rounded p-3 address-container h-100"
                                            data-address-id="<?= $address->id; ?>" style="cursor: pointer;">
                                            <div class="d-flex justify-content-end">
                                            <a href="?action=editaddress&id=<?= $address->id; ?>" ><i
                                                    class="bi bi-pencil  text-primary"></i>
                                            </a>
                                            <a onclick="return confirmDelete(<?= $address->id; ?>)" ><i
                                                    class="bi bi-trash text-danger"></i></a>
                                           </div>        
                                            <div class="d-flex">
                                                <div class="addressDetail d-flex align-items-start">
                                                    <div class="addicon "><i class="bi bi-geo-alt  purpColor"></i>
                                                    </div>
                                                      
                                                    <div class="ms-3">
                                                        <p class="m-0 fs-6 fw-semibold">
                                                        <?= $address->first_name; ?>
                                                        <?= $address->last_name; ?> 
                                                            <?= $address->line1; ?>,
                                                            <?= $address->city; ?>,
                                                            <?= $address->state; ?>
                                                            <?= $address->country; ?>
                                                        </p>
                                                        <span class="phone_verified" style="visibility:hidden;"><?= $address->mobile_verified; ?></span>
                                                        <p class="m-0 fs-6 fw-semibold"> +966
                                                            <span class="phone_number"><?= $address->phone; ?></span>
                                                            
                                                            
                                                            <?php if ($address->mobile_verified == 1): ?>
                                                                <i class="bi bi-check-circle-fill  purpColor"></i>
                                                            <?php else: ?>
                                                                <i class="bi bi-exclamation-triangle text-danger fs-5"></i>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                     </div>
                                        <?php
                                        $r++;
                                    }

                                }

                                ?>
                           </div>
                                <div style="clear: both">
                                    <?php
                                    $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'checkoutAddress'];
                                    echo form_open('shop/confirmaddress', $attrib);
                                    ?>

                                    <input type="hidden" id="selected-address-id" name="selected_address_id" required>

                                    <a href="?action=addnewaddress"
                                        class="btn btn-lg primary-buttonAV rounded-2  fw-normal px-4 py-1 address-but"
                                       > Add New Address</a>
                                    <a href="<?= base_url() . 'cart/checkout'; ?>"
                                        class="btn btn-lg cancel-button rounded-2 fw-normal px-4 py-1 address-but"
                                      > Cancel</a>
                                    <button type="submit"
                                        id="confirm-address" class="btn btn-lg  primary-buttonAV rounded-2 fw-normal px-4 py-1 address-but"
                                       > Confirm</button>

                                    <?= form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Register Modal Starts -->
                    <div class="modal fade" id="verifyMobileModal" tabindex="-1" aria-labelledby="verifyMobileLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content px-4 rounded-4">
                                <div class="modal-header border-0">
                                    <button type="button" class="modalcloseBtn" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                                </div>
                                <div class="modal-body ">
                                    <div class="emailOTP">
                                        <div class="text-center px-5">
                                            <h2>Verify your mobile</h2>
                                            <h5 class="fs-4 px-5 lh-base">OTP has been sent to <span id="identifier"></span></h5>
                                        </div>
                                        <?php 
                                            $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'mobileOtpForm'];
                                            echo form_open('verify_phone_otp', $attrib); 
                                        ?>
                                        <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2"> 
                                            <input class="m-1 text-center form-control rounded ap-otp-input-checkout" type="tel"  data-index="0" name="opt_part1" id="first" maxlength="1" />
                                            <input class="m-1 text-center form-control rounded ap-otp-input-checkout" type="tel"  data-index="1" name="opt_part2" id="second" maxlength="1" />
                                            <input class="m-1 text-center form-control rounded ap-otp-input-checkout" type="tel"  data-index="2" name="opt_part3" id="third" maxlength="1" />
                                            <input class="m-1 text-center form-control rounded ap-otp-input-checkout" type="tel"  data-index="3" name="opt_part4" id="fourth" maxlength="1" /> 
                                            <input class="m-1 text-center form-control rounded ap-otp-input-checkout" type="tel"  data-index="4" name="opt_part5" id="fifth" maxlength="1" />
                                            <input class="m-1 text-center form-control rounded ap-otp-input-checkout" type="tel"  data-index="5" name="opt_part6" id="sixth" maxlength="1" />
                                            <input type="hidden" id="identifier_input" name="identifier_input" value="" />
                                            <input type="hidden" name="change_phone" value="1" />
                                            <input type="hidden" id="selected_add_id" name="selected_add_id" value="" />
                                        </div>
                                        <div  class="text-center">
                                            <h6 class="m-0 mt-2 d-flex justify-content-center"><span id="register-clock"></span> <span class="ms-2 fw-semibold opacity-50" id="mobileOTP">Resend OTP </span></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pb-4">
                                    <button type="submit" id="mobileOtpBtn" class="btn  text-white continueBtn rounded w-75 mx-auto mt-0 d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#exampleModal">Verify</button>
                                </div>
                                <span id="otp-message"></span>
                                <?= form_close(); ?>
                            </div>
                        </div>
                    </div>
                    <!-- Register Modal Ends -->
                </div>

            </div>
        </div>
    </div>
</section>

<?php
$attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'deleteCheckoutAddress'];
echo form_open('shop/deleteDeliveryAddress', $attrib);
?>
<input type="hidden" name="addressId" id="addressId" value="">
<?= form_close(); ?>


<script>
    // $(document).ready(function () {
    //     $(".address-container").on("click", function () {
    //         // Select the entire box
    //         var range = document.createRange();
    //         range.selectNode(this);
    //         window.getSelection().removeAllRanges();
    //         window.getSelection().addRange(range);
    //     });
    // });

    $(document).ready(function () {
        $(".address-container").on("click", function () {
            // Deselect all addresses
            $(".address-container").removeClass("selected-address");

            // Select the clicked address
            $(this).addClass("selected-address");

            // Store the selected address ID in the hidden input
            var selectedAddressId = $(this).data("address-id");
            $("#selected-address-id").val(selectedAddressId);
            $("#selected_add_id").val(selectedAddressId);

            // Check the hidden radio button for form submission
            $("#address-selection-radio").prop("checked", true);

            // var phone_verified = $('.selected-address').find('.phone_verified').text();
            //// if(phone_verified){
            // //    $('#confirm-address').hide();
            //// }else{
            // //    $('#confirm-address').show();
            //// }
        });
        
        $("#confirm-address").on("click", function (e) {
            e.preventDefault(); 

            var selected_phone = $('.selected-address').find('.phone_number').text();
            var phone_verified = $('.selected-address').find('.phone_verified').text();
            var selected_add_id = $("#selected-address-id").val();
            if (selected_add_id == '') {
                // Show an error message
                alert("Please select an address to confirm.");
                return false;
            }
            if(phone_verified != 1){
                verifyNumber(selected_phone);    
            }else{
                // Set confirmed phone number as shipping
                var addressId = $('.selected-address').data('address-id');
                $.ajax({
                    type: 'GET',
                    url: '<?= base_url(); ?>set_shipping_phone',
                    data: {'mobile_number' : selected_phone, 'address_id': addressId},
                    success: function (response) {
                        console.log(response);
                        window.location.href = site.base_url+'cart/checkout';
                    },
                    error: function (error) {
                        console.error(error);
                    }
                });

                
            }
        });

        $('#mobileOtpBtn').click(function (e) {
            e.preventDefault(); 

            var formData = $('#mobileOtpForm').serialize();
            $.ajax({
                type: 'POST',
                //url: '<?= base_url(); ?>verify_phone_otp',
                url: $('#mobileOtpForm').attr('action'),
                data: formData,
                success: function (response) {
                    var respObj = JSON.parse(response);
                    if (respObj.status == 'success' || respObj.code == 1) {
                        window.location.href = site.base_url+'cart/checkout';
                    } else {
                        $('#otp-message').html('OTP verification failed');
                    }
                },
                error: function (error) {
                    console.error(error);
                }
            });
        });

        function verifyNumber(selected_phone){
            event.preventDefault();
            //var formData = $('#checkoutAddress').serialize();
            $.ajax({
                type: 'GET',
                url: '<?= base_url(); ?>verify_phone',
                //url: $('#checkoutAddress').attr('action'),
                data: {'mobile_number' : selected_phone},
                success: function (response) {
                    var respObj = JSON.parse(response);
                    if (respObj.status == 'success' || respObj.code == 1) {
                        $('#mobileOTP').off('click', handleMobileOTPClick);
                        document.getElementById('mobileOTP').style.color = 'grey';
                        document.getElementById('mobileOTP').style.cursor = 'none';
                        $('#verifyMobileModal').modal('show');
                        document.getElementById('identifier').innerHTML = selected_phone;
                        document.getElementById('identifier_input').value = selected_phone;

                        const countdownDuration = 60; // Duration in seconds
                        const countdownDisplay = document.getElementById("register-clock");
                        
                        let timer = countdownDuration, minutes, seconds;
                        const intervalId = setInterval(function () {
                            minutes = parseInt(timer / 60, 10);
                            seconds = parseInt(timer % 60, 10);

                            countdownDisplay.textContent = minutes + "." + (seconds < 10 ? "0" : "") + seconds;

                            if (--timer < 0) {
                                clearInterval(intervalId);
                                document.getElementById('mobileOTP').style.color = '#662d91';
                                document.getElementById('mobileOTP').style.cursor = 'pointer';
                                $('#mobileOTP').click(handleMobileOTPClick);
                            }
                        }, 1000);

                    } else {
                        alert('Mobile verification failed');
                    }
                },
                error: function (error) {
                    console.error(error);
                }
            });
        }

        function handleMobileOTPClick(){
            //var formData = $('#checkoutAddress').serialize();
            var selected_phone = $('.selected-address .phone_number').text();
            $.ajax({
                type: 'GET',
                url: '<?= base_url(); ?>verify_phone',
                data: {'mobile_number' : selected_phone},
                success: function (response) {
                    var respObj = JSON.parse(response);
                    if (respObj.status == 'success' || respObj.code == 1) {
                        $('#mobileOTP').off('click', handleMobileOTPClick);
                        document.getElementById('mobileOTP').style.color = 'grey';
                        document.getElementById('mobileOTP').style.cursor = 'none';
                        $('#mobileModal').modal('show');
                        document.getElementById('identifier').innerHTML = selected_phone;
                        document.getElementById('identifier_input').value = selected_phone;

                        const countdownDuration = 60; // Duration in seconds
                        const countdownDisplay = document.getElementById("register-clock");
                        
                        let timer = countdownDuration, minutes, seconds;
                        const intervalId = setInterval(function () {
                            minutes = parseInt(timer / 60, 10);
                            seconds = parseInt(timer % 60, 10);

                            countdownDisplay.textContent = minutes + "." + (seconds < 10 ? "0" : "") + seconds;

                            if (--timer < 0) {
                                clearInterval(intervalId);
                                document.getElementById('mobileOTP').style.color = '#662d91';
                                document.getElementById('mobileOTP').style.cursor = 'pointer';
                                $('#mobileOTP').click(handleMobileOTPClick);
                            }
                        }, 1000);

                    } else {
                        alert('Mobile verification failed');
                    }
                },
                error: function (error) {
                    console.error(error);
                }
            });
        }



    });

    function confirmDelete(recordId) {
        if (confirm("Are you sure you want to delete this address?")) {
            // Set the record ID in the hidden field
            document.getElementById("addressId").value = recordId;
            // Submit the form
            document.getElementById("deleteCheckoutAddress").submit();
        }
    }
</script>