<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    #map-container {
        position: relative;
        border: none;
    }

    #autocomplete_search,
    #load_current_location-2 {
        position: absolute;
        z-index: 1;
        /* Ensure the controls are on top of the map */
        background: rgba(255, 255, 255, 0.7);
        /* Use rgba for a transparent white background */
        border: 1px solid #ccc;
        padding: 10px;
    }

    #autocomplete_search {
        top: 10px;
        /* left: 10px; */
    }

    #load_current_location-2 {
        right: 62px;
        bottom: 22px;
        /* Adjust the left position as needed */
    }

    #load_map {
        height: 400px;
        width: 100%;
    }

    .checkout_address {
        border: none;
        border-bottom: 1px solid #ccc;
        border-radius: 0;
    }

    .error {
        border: 2px solid red !important;
        /* Set the initial border color to red for error */
        animation: blink 2s infinite !important;
        /* Apply the blink animation */
    }

    @keyframes blink {
        0% {
            border-color: red;
        }

        50% {
            border-color: transparent;
        }

        100% {
            border-color: red;
        }
    }
</style>
<?php
$longitude = '';
$latitude = '';
$address_line_1 = '';
$address_line_2 = '';
$address_city = '';
$address_state = '';
$address_country = '';
$mobile_number = '';
$first_name = '';
$last_name = '';
if (isset($selected_address_info) & !empty($selected_address_info)) {
    $longitude = $selected_address_info->longitude;
    $latitude = $selected_address_info->latitude;
    if (isset($selected_address_info->address)) {
        $address_line_1 = $selected_address_info->address;
    } else {
        $address_line_1 = $selected_address_info->line1;
    }
    $address_line_2 = $selected_address_info->line2;
    $address_city = $selected_address_info->city;
    $address_state = $selected_address_info->state;
    $address_country = $selected_address_info->country;
    $mobile_number = $selected_address_info->phone;
    $first_name = $selected_address_info->first_name;
    $last_name = $selected_address_info->last_name;
}

//verified_numbers = [];
?>

<section class=" py-1 ">
    <div class="container container-max-width">
        <div class=" my-4">
            <div class="addressbar rounded-0">
                <div class="w-100 addtitle">
                    <h5><i class="bi bi-map-fill me-2"></i>
                        <?= lang('address'); ?>
                    </h5>
                </div>
                <div class="px-3 py-2">
                    <div class="py-3">
                        <h6 class="m-0 fw-semibold">
                            <?= lang('add_new_address'); ?>
                        </h6>
                    </div>
                    <div class="row" id="addressList">
                        <?php
                        if ($this->Settings->indian_gst) {
                            $istates = $this->gst->getIndianStates();
                        }
                        ?>
                        <div class="col-md-6 mb-4" id="<?= $address->id; ?>" style="margin-right:25px;">
                            <div class="card" id="map-container">
                                <input id="autocomplete_search" type="text" class="form-control bg-white border-0 py-2"
                                    style="box-shadow: 0px 1px 5px #b2b2b2;" placeholder="Type for the address..."
                                    autocomplete="on" value="<?= $address_line_1; ?>">
                                <div id="load_map" style="height: 100%; width: 100%;"></div>
                                <button type="button" id="load_current_location-2"
                                    class="bg-white rounded-5 border-0 py-2"
                                    style="width:100px;box-shadow: 0px 1px 5px #b2b2b2;">Locate Me</button>

                            </div>
                        </div>

                        <div class="col-md-5 mb-4" id="<?= $address->id; ?>">
                            <div class="card" style="border:none">
                                <span class="text-bold padding-bottom-md fw-bold mb-3"
                                    style="font-size:20px;font-weight: bold;color: #662d91;">
                                    New Address Details
                                    <?php
                                    $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'checkoutAddress'];
                                    echo form_open('shop/saveCheckoutAddress', $attrib);
                                    //echo form_open('verify_phone', $attrib);
                                    ?>
                                    <input type="hidden" id="action_type" name="action_type_id"
                                        value="<?= $address_id; ?>">
                                    <input type="hidden" id="longitude" name="longitude" value="<?= $longitude; ?>">
                                    <input type="hidden" id="latitude" name="latitude" value="<?= $latitude; ?>">
                                    <input type="hidden" id="address-line-1" name="address_line_1"
                                        value="<?= $address_line_1; ?>">
                                    <input type="hidden" id="address-city" name="city" value="<?= $address_city; ?>">
                                    <input type="hidden" id="address-state" name="state" value="<?= $address_state; ?>">
                                    <input type="hidden" id="address-country" name="country"
                                        value="<?= $address_country; ?>">
                                    <input type="hidden" id="current_mobile_number" name="current_mobile_number"
                                        value="<?= $mobile_number; ?>">
                                    <input type="hidden" id="opt_verified" name="opt_verified" value="0">
                                    <input type="hidden" id="action_type" name="action_type" value="<?=$action_type;?>">

                                    <span class=" padding-bottom-md fw-bold" style="font-size:17px;">
                                        LOCATION INFORMATION
                                    </span>
                                    <div class="form-row">
                                        <div class="form-group col-md-8">
                                            <label for="exampleFormControlInput1" class=" fw-bold fs-6">Additional
                                                Address Details</label>
                                            <input type="text" class="form-control checkout_address ps-0"
                                                name="address_line_2" id="exampleFormControlInput1"
                                                placeholder="Building No, Floor, Flate No etc"
                                                value="<?= $address_line_2; ?>">
                                        </div>
                                    </div>

                                    <!-- <div class="form-row">
                                        <div class="form-group col-md-3" style="float: left">
                                            <label for="inputEmail4" class=" fw-bold fs-6">Mobile Number</label>
                                            <select class="form-control checkout_address py-1 px-0"
                                                id="exampleFormControlSelect1">
                                                <option>+966</option>
                                                </select>
                                        </div>
                                        <div class="form-group col-md-5 col-7" style="float: left; margin-left:0px; margin-top:11px">
                                            <label for="inputPassword4">&nbsp;</label>
                                            <input type="text" class="form-control required checkout_address px-0 pt-1 " id="mobile_number" name="mobile_number"
                                                placeholder="Mobile Number">
                                        </div>
                                    </div> -->

                                    <div class="form-row">
                                        <div class="form-group col-md-4 col-12" style="float: left">
                                            <label for="mobile_number" class=" fw-bold fs-6">Mobile Number</label>
                                            <select class="form-control checkout_address py-1 px-0"
                                                id="exampleFormControlSelect1">
                                                <option>+966</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4 col-12 ms-md-4" style="float: left;">
                                            <label for="mobile_number" class=" fw-bold fs-6">&nbsp;</label>
                                            <input type="text" class="form-control required checkout_address px-0 pt-1 "
                                                id="mobile_number" name="mobile_number" placeholder="Mobile Number"
                                                value="<?= $mobile_number; ?>">
                                        </div>
                                    </div>

                                    <div class=" padding-bottom-md fw-bold fs-6" style="clear:both">
                                        PERSONAL INFORMATION
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-4 col-12" style="float: left">
                                            <label for="first_name" class=" fw-bold fs-6">First Name</label>
                                            <input type="text" class="form-control checkout_address ps-0"
                                                id="first_name" name="first_name" placeholder="Enter First Name"
                                                value="<?= $first_name; ?>">
                                        </div>
                                        <div class="form-group col-md-4 col-12 ms-md-4" style="float: left;">
                                            <label for="last_name" class=" fw-bold fs-6">Last Name</label>
                                            <input type="text" class="form-control checkout_address ps-0" id="last_name"
                                                name="last_name" placeholder="Enter Last Name" value="<?= $last_name; ?>">
                                        </div>
                                    </div>

                                    <div class="custom-control custom-switch" style="clear: both">

                                        <!-- <div class="form-check form-switch d-flex align-items-center  ps-0 pe-3 w-100">
                                        <label class="form-check-label  mt-2" for="flexSwitchCheckDefault"  style="width: 200px; font-size: 18px;">Set as default address</label>
                                        <input class="form-check-input fs-5 " type="checkbox" role="switch" id="flexSwitchCheckDefault" checked="checked" name="is_default" style="margin-top:12px; margin-left: 12px;">
                                    
                                    </div> -->
                                    </div>

                                    <div>
                                        <button type="submit" class="btn primary-buttonAV  rounded-1 pb-2"
                                            style="margin-top:25px;">
                                            <?= lang('Confirm_&_Save_Address'); ?>
                                        </button>
                                    </div>
                                    <?= form_close(); ?>

                            </div>
                        </div>
                    </div>
                    <!-- Button to trigger the add address modal -->
                    <!-- Register Modal Starts -->
                    <div class="modal fade" id="verifyMobileModal" tabindex="-1" aria-labelledby="verifyMobileLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content px-4 rounded-4">
                                <div class="modal-header border-0">
                                    <button type="button" class="modalcloseBtn" data-bs-dismiss="modal"
                                        aria-label="Close"><i class="bi bi-x-lg"></i></button>
                                </div>
                                <div class="modal-body ">
                                    <div class="emailOTP">
                                        <div class="text-center px-5">
                                            <h2>Verify your mobile</h2>
                                            <h5 class="fs-4 px-5 lh-base">OTP has been sent to <span
                                                    id="identifier"></span></h5>
                                        </div>
                                        <?php
                                        $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'mobileOtpForm'];
                                        echo form_open('verify_phone_otp', $attrib);
                                        ?>
                                        <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2">
                                            <input class="m-1 text-center form-control rounded" type="text"
                                                name="opt_part1" id="checkout_login_1" maxlength="1" />
                                            <input class="m-1 text-center form-control rounded" type="text"
                                                name="opt_part2" id="checkout_login_2" maxlength="1" />
                                            <input class="m-1 text-center form-control rounded" type="text"
                                                name="opt_part3" id="checkout_login_3" maxlength="1" />
                                            <input class="m-1 text-center form-control rounded" type="text"
                                                name="opt_part4" id="checkout_login_4" maxlength="1" />
                                            <input class="m-1 text-center form-control rounded" type="text"
                                                name="opt_part5" id="checkout_login_5" maxlength="1" />
                                            <input class="m-1 text-center form-control rounded" type="text"
                                                name="opt_part6" id="checkout_login_6" maxlength="1" />
                                            <input type="hidden" id="identifier_input" name="identifier_input"
                                                value="" />
                                        </div>
                                        <div class="text-center">
                                            <h6 class="m-0 mt-2"><span id="register-clock"></span> <span
                                                    class="ms-2 fw-semibold opacity-50" id="mobileOTP">Resend OTP
                                                </span></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pb-4">
                                    <button type="submit" id="mobileOtpBtn"
                                        class="btn  text-white continueBtn rounded w-75 mx-auto mt-0"
                                        data-bs-toggle="modal" data-bs-target="#exampleModal">Verify</button>
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

        <?php
        if ($this->Settings->indian_gst) {
            ?>
            <script>
                var istates = <?= json_encode($istates); ?>
            </script>
            <?php
        } else {
            echo '<script>var istates = false; </script>';
        }
        ?>

    </div>
</section>

<script type="text/javascript">
    var addresses = <?= !empty($addresses) ? json_encode($addresses) : 'false'; ?>;
</script>


<script>

    $(document).ready(function () {

        var autocomplete_search = $('#autocomplete_search');

        // Remove the error class when the input field is focused
        autocomplete_search.focus(function () {
            autocomplete_search.removeClass('error');
        });

        // Remove the error class when the input value changes
        autocomplete_search.on('change', function () {
            autocomplete_search.removeClass('error');
        });
        const mobile_number = $('#mobile_number');
        mobile_number.focus(function () {
            mobile_number.removeClass('error');
        });

        const first_name = $('#first_name');
        first_name.focus(function () {
            first_name.removeClass('error');
        });

        const last_name = $('#last_name');
        last_name.focus(function () {
            last_name.removeClass('error');
        });


        $('#checkoutAddress').submit(function (event) {
            // Remove previous error highlights
            $('.error').removeClass('error');
            //event.preventDefault();
            // alert('Here in change address...bublooo');
            // Perform validation
            var autocomplete_search = $('#autocomplete_search').val();

            if (autocomplete_search === '') {

                // Highlight the input field with an error
                $('#autocomplete_search').addClass('error');
                event.preventDefault(); // Prevent form submission
            }
            var mobile_number = $('#mobile_number').val();

            if (mobile_number === '') {
                // Highlight the input field with an error
                $('#mobile_number').addClass('error');
                event.preventDefault(); // Prevent form submission
            }

            var first_name = $('#first_name');

            if (first_name.val() === '') {
                // Highlight the input field with an error
                first_name.addClass('error');
                event.preventDefault(); // Prevent form submission
            }

            var last_name = $('#last_name');

            if (last_name.val() === '') {
                // Highlight the input field with an error
                last_name.addClass('error');
                event.preventDefault(); // Prevent form submission
            }

            // Add more validation for other fields as needed
            if($('#action_type').val() == '' || $('#action_type_id').val() == 'default' ) {
            if ($('#opt_verified').val() == 0 && last_name.val() !== '' && first_name.val() !== '' && mobile_number !== '' && autocomplete_search !== '') {
                // check if current_mobile_number != phone
                verifyNumber();
            }
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
                        $('#opt_verified').val(1);
                        $('#checkoutAddress').submit();
                    } else {
                        $('#otp-message').html('OTP verification failed');
                    }
                },
                error: function (error) {
                    console.error(error);
                }
            });
        });

        function verifyNumber() {
            event.preventDefault();
            var formData = $('#checkoutAddress').serialize();
            $.ajax({
                type: 'POST',
                url: '<?= base_url(); ?>verify_phone',
                //url: $('#checkoutAddress').attr('action'),
                data: formData,
                success: function (response) {
                    var respObj = JSON.parse(response);
                    if(respObj.status == 'verified') {
                        $('#checkoutAddress').off('submit'); // Unbind existing submit handler
                        $('#checkoutAddress').submit();
                    }
                    else if (respObj.status == 'success' || respObj.code == 1) {

                            $('#mobileOTP').off('click', handleMobileOTPClick);
                            document.getElementById('mobileOTP').style.color = 'grey';
                            document.getElementById('mobileOTP').style.cursor = 'none';
                            $('#verifyMobileModal').modal('show');
                            document.getElementById('identifier').innerHTML = $('#mobile_number').val();
                            document.getElementById('identifier_input').value = $('#mobile_number').val();

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

        function handleMobileOTPClick() {
            var formData = $('#checkoutAddress').serialize();
            $.ajax({
                type: 'POST',
                url: '<?= base_url(); ?>verify_phone',
                data: formData,
                success: function (response) {
                    var respObj = JSON.parse(response);
                    if (respObj.status == 'success' || respObj.code == 1) {
                        $('#mobileOTP').off('click', handleMobileOTPClick);
                        document.getElementById('mobileOTP').style.color = 'grey';
                        document.getElementById('mobileOTP').style.cursor = 'none';
                        $('#mobileModal').modal('show');
                        document.getElementById('identifier').innerHTML = $('#mobile_number').val();
                        document.getElementById('identifier_input').value = $('#mobile_number').val();

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

    function initMap() {
        let map = new google.maps.Map(document.getElementById("load_map"), {
            center: { lat: 23.8859, lng: 45.0792 },
            zoom: 18,
        });
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const userLocation = {
                        lat: <?php echo $latitude ? $latitude : 'position.coords.latitude'; ?>,
                        lng: <?php echo $longitude ? $longitude : 'position.coords.longitude'; ?>
                    };

                    document.getElementById("latitude").value = userLocation.lat;
                    document.getElementById("longitude").value = userLocation.lng;
                    map.setCenter(userLocation);

                    const marker = new google.maps.Marker({
                        position: userLocation,
                        map: map,
                        title: "Your Location",
                        draggable: true,
                    });

                    // document.getElementById("manual-shipping-check-2").checked = false;
                    // document.getElementById("manual-shipping-address-2").style.display =
                    //   "none";

                    function updateLocation(newPosition) {
                        console.log('dragend', newPosition);
                        //   document.getElementById("manual-shipping-check-2").checked = false;
                        //   document.getElementById("manual-shipping-address-2").style.display =
                        //     "none";

                        document.getElementById("latitude").value = newPosition.lat();
                        document.getElementById("longitude").value = newPosition.lng();
                        console.log('dragend', newPosition);
                        const userNewLocation = {
                            lat: newPosition.lat(),
                            lng: newPosition.lng(),
                        };
                        geocodeLatLng2(userNewLocation);
                    }

                    marker.addListener("dragend", function () {
                        console.log('drag event call');
                        updateLocation(marker.getPosition());
                        //geocodeLatLng2(userLocation);
                    });

                    $('#load_current_location-2').on('click', function (e) {
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                function (position) {
                                    const userLocation = {
                                        lat: position.coords.latitude,
                                        lng: position.coords.longitude,
                                    };

                                    marker.setPosition(userLocation);
                                    map.setCenter(userLocation);
                                    //updateLocation(userLocation);
                                    geocodeLatLng2(userLocation);
                                    //$('#autocomplete_search').removeClass('error');
                                },
                                function (error) {
                                    console.error('Error getting user location:', error);
                                },
                                {
                                    enableHighAccuracy: true
                                }
                            );
                        }
                    });

                    const input = document.getElementById("autocomplete_search");
                    const searchBox = new google.maps.places.SearchBox(input);

                    map.addListener("bounds_changed", function () {
                        searchBox.setBounds(map.getBounds());
                    });

                    let markers = [];

                    searchBox.addListener("places_changed", function () {
                        const places = searchBox.getPlaces();

                        if (places.length === 0) {
                            return;
                        }

                        markers.forEach(function (marker) {
                            marker.setMap(null);
                        });
                        markers = [];

                        const bounds = new google.maps.LatLngBounds();
                        places.forEach(function (place) {
                            if (!place.geometry) {
                                console.log("Returned place contains no geometry");
                                return;
                            }

                            markers.push(
                                new google.maps.Marker({
                                    map: map,
                                    title: place.name,
                                    position: place.geometry.location,
                                    draggable: true
                                })

                            );

                            const marker = markers[markers.length - 1];

                            marker.addListener("dragend", function () {
                                updateLocation(marker.getPosition());
                            });

                            if (place.geometry.viewport) {
                                bounds.union(place.geometry.viewport);
                            } else {
                                bounds.extend(place.geometry.location);
                            }
                        });

                        map.fitBounds(bounds);
                        console.log('markers', markers.length);
                        if (markers.length > 0) {
                            updateLocation(markers[0].getPosition());
                            //geocodeLatLng2(userLocation);
                        }
                    });

                },
                function (error) {
                    console.error("Error getting user location:", error);
                },
                {
                    enableHighAccuracy: true,
                }
            );
        } else {
            console.error("Geolocation is not supported by this browser.");
        }
    }

    // Load the map when the page is loaded
    //google.maps.event.addDomListener(window, 'load', initMap('<?= $latitude ?>','<?= $longitude; ?>'));
</script>