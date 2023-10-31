<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!--<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC9B2FB0OWZb_CrS8Njrdgek7djxBagYek&libraries=places"></script>-->
<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-sm-9 col-md-10">

                        <div class="panel panel-default margin-top-lg">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-map margin-right-sm"></i> <?= lang('my_addresses'); ?>
                            </div>
                            <div class="panel-body">
<!--                                <input id="autocomplete_search" name="autocomplete_search" type="text" class="form-control"-->
<!--                                       placeholder="Search"/>-->
                                <?php
                                if ($this->Settings->indian_gst) {
                                    $istates = $this->gst->getIndianStates();
                                }
                                if (!empty($addresses)) {
                                    echo '<div class="row">';
                                    echo '<div class="col-sm-12 text-bold">' . lang('select_address_to_edit') . '</div>';
                                    $r = 1;
                                    foreach ($addresses as $address) {
                                        ?>
                                        <div class="col-sm-6">
                                            <a href="#" class="link-address edit-address" data-id="<?= $address->id; ?>">
                                                    <?= $address->line1; ?><br>
                                                    <?= $address->line2; ?><br>
                                                    <?= $address->city; ?>
                                                    <?= $this->Settings->indian_gst && isset($istates[$address->state]) ? $istates[$address->state] . ' - ' . $address->state : $address->state; ?><br>
                                                    <?= $address->postal_code; ?> <?= $address->country; ?><br>
                                                    <?= lang('phone') . ': ' . $address->phone; ?>
                                                    <span class="count"><i><?= $r; ?></i></span>
                                                    <span class="edit"><i class="fa fa-edit"></i></span>
                                                </a>
                                        </div>
                                        <?php
                                        $r++;
                                    }
                                    echo '</div>';
                                }
                                if (count($addresses) < 6) {
                                    echo '<div class="row margin-top-lg">';
                                    echo '<div class="col-sm-12"><a href="#" id="add-address" class="btn btn-primary btn-sm">' . lang('add_address') . '</a></div>';
                                    echo '</div>';
                                }
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
                        </div>
                    </div>

                    <div class="col-sm-3 col-md-2">
                        <?php include 'sidebar1.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
var addresses = <?= !empty($addresses) ? json_encode($addresses) : 'false'; ?>;
</script>

<!--<script>-->
<!--    google.maps.event.addDomListener(window, 'load', initialize);-->
<!--    function initialize() {-->
<!--        var input = document.getElementById('autocomplete_search');-->
<!--        var autocomplete = new google.maps.places.Autocomplete(input);-->
<!--        console.log(autocomplete)-->
<!--        autocomplete.addListener('place_changed', function () {-->
<!---->
<!--            var place = autocomplete.getPlace();-->
<!--            // Define variables to store city and country names-->
<!--            var city, country, street;-->
<!---->
<!--            // Loop through address components to find city and country-->
<!--            place.address_components.forEach(function (component) {-->
<!---->
<!--                component.types.forEach(function (type) {-->
<!--                    console.log(type + component.long_name)-->
<!--                    if (type === 'locality') {-->
<!--                        city = component.long_name;-->
<!--                    }-->
<!--                    if (type === 'country') {-->
<!--                        country = component.long_name;-->
<!--                    }-->
<!--                    if (type === 'route') {-->
<!--                        street = component.long_name-->
<!--                    }-->
<!--                });-->
<!--            });-->
<!---->
<!--            console.log('Street: ' + street);-->
<!--            console.log('City: ' + city);-->
<!--            console.log('Country: ' + country);-->
<!--            // place variable will have all the information you are looking for.-->
<!--            $('#lat').val(place.geometry['location'].lat());-->
<!--            $('#long').val(place.geometry['location'].lng());-->
<!--        });-->
<!--    }-->
<!--</script>-->
