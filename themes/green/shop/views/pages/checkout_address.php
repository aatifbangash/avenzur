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
</style>
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
                            <?= lang('select_address_to_edit'); ?>
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
                                <input id="autocomplete_search" type="text" class="form-control bg-white border-0 py-2" style="box-shadow: 0px 1px 5px #b2b2b2;"
                                    placeholder="Type for the address..." autocomplete="on">
                                <div id="load_map" style="height: 100%; width: 100%;"></div>
                                <button type="button" id="load_current_location-2" class="bg-white rounded-5 border-0 py-2" style="width:100px;box-shadow: 0px 1px 5px #b2b2b2;">Locate Me</button>

                            </div>
                        </div>


                        <div class="col-md-5 mb-4" id="<?= $address->id; ?>">
                            <div class="card" style="border:none">
                                <span class="text-bold padding-bottom-md fw-bold mb-3" style="font-size:20px;font-weight: bold;color: #662d91;">
                                    New Address Details
                                </span>
                                <form action="[your_shop_url]address" id="address-form" class="padding-bottom-md">
                                    <input type="hidden" name="[your_csrf_token]" value="[your_csrf_token_value]">
                                    <input type="hidden" id="longitude" name="longitude"
                                        value="[t.longitude ? t.longitude : '']">
                                    <input type="hidden" id="latitude" name="latitude"
                                        value="[t.latitude ? t.latitude : '']">
                                    <span class=" padding-bottom-md fw-bold" style="font-size:17px;">
                                        LOCATION INFORMATION
                                    </span>
                                    <div class="form-row">
                                    <div class="form-group col-md-8">
                                        <label for="exampleFormControlInput1" class=" fw-bold fs-6">Additional Address Details</label>
                                        <input type="email" class="form-control checkout_address ps-0"
                                            id="exampleFormControlInput1"
                                            placeholder="Building No, Floor, Flate No etc">
                                    </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-3" style="float: left">
                                            <label for="inputEmail4" class=" fw-bold fs-6">Mobile Number</label>
                                            <select class="form-control checkout_address py-1 px-0"
                                                id="exampleFormControlSelect1">
                                                <option>+966</option>
                                                < </select>
                                        </div>
                                        <div class="form-group col-md-5 col-7" style="float: left; margin-left:0px; margin-top:11px">
                                            <label for="inputPassword4">&nbsp;</label>
                                            <input type="text" class="form-control checkout_address px-0 pt-1 " id="inputPassword4"
                                                placeholder="Mobile Number">
                                        </div>
                                    </div>


                                    <div class=" padding-bottom-md fw-bold fs-6" style="clear:both">
                                        PERSONAL INFORMATION
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-4 col-12" style="float: left">
                                            <label for="inputEmail4" class=" fw-bold fs-6">First Name</label>
                                            <input type="email" class="form-control checkout_address ps-0" id="inputEmail4"
                                                placeholder="Enter First Name">
                                        </div>
                                        <div class="form-group col-md-4 col-12 ms-md-4" style="float: left;">
                                            <label for="inputPassword4" class=" fw-bold fs-6">Last Name</label>
                                            <input type="text" class="form-control checkout_address ps-0" id="inputPassword4"
                                            placeholder="Enter Last Name">
                                        </div>
                                    </div>

                                    <div class="custom-control custom-switch" style="clear: both">
                                        
                                    <div class="form-check form-switch d-flex align-items-center  ps-0 pe-3 w-100">
                                        <label class="form-check-label  mt-2" for="flexSwitchCheckDefault"  style="width: 200px;">Set as default address</label>
                                        <input class="form-check-input fs-5 " type="checkbox" role="switch" id="flexSwitchCheckDefault">
                                    
                                    </div>
                                    </div>
                                    
                                    <div>
                                        <button type="button" class="btn primary-buttonAV  rounded-1 pb-2"
                                            style="margin-top:25px;" data-bs-toggle="modal"
                                            data-bs-target="#addAddressModal">
                                            <?= lang('Confirm_&_Save_Address'); ?>
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                    <!-- Button to trigger the add address modal -->

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
    function initMap() {
        console.log('test');
        let map = new google.maps.Map(document.getElementById("load_map"), {
            center: { lat: 23.8859, lng: 45.0792 },
            zoom: 18,
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };

                    document.getElementById("latitude").value = position.coords.latitude;
                    document.getElementById("longitude").value = position.coords.longitude;
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
    google.maps.event.addDomListener(window, 'load', initMap);
</script>