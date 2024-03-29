<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class=" py-1 ">
    <div class="container container-max-width">
        <div class=" my-4">
            <div class="addressbar">
                <div class="w-100 "><h3 class="fw-semibold d-flex justify-content-between"><!--<i class="bi bi-map-fill me-2"></i>--> <?= lang('my_addresses'); ?>   
                <button type="button" id="add-address" class="btn primary-buttonAV  rounded-1 pb-2" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <?= lang('add_address'); ?>
                </button>
            </h3></div>
                <div class="p-0">
                <div class="col-sm-12 text-bold mb-3"><?= lang('select_address_to_edit'); ?></div>
                <div class="row" id="addressList">
                    <?php
                        if ($this->Settings->indian_gst) {
                            $istates = $this->gst->getIndianStates();
                        }
                    ?>
                    <?php 
                        foreach ($addresses as $address){
                            ?>
                                <div class="col-md-6 mb-4" id="<?= $address->id; ?>">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= $address->line1; ?></h5>
                                            <h6 class="card-subtitle mb-2 text-muted"><?= $address->line2; ?></h6>
                                            <p class="card-text"><?= $address->city; ?>, <?= $this->Settings->indian_gst && isset($istates[$address->state]) ? $istates[$address->state] . ' - ' . $address->state : $address->state; ?></p>
                                            <p class="card-text">Postal Code: <?= $address->postal_code; ?></p>
                                            <p class="card-text">Country: <?= $address->country; ?></p>
                                            <p class="card-text addPhone-k"><?= lang('phone') . ': '?>  <span class="phonear-k"><?= $address->phone; ?></span></p>
                                            <button type="button" class="btn btn-primary edit-address" data-bs-toggle="modal" data-bs-target="#editAddressModal" data-id="<?= $address->id; ?>">Edit</button>
                                        </div>
                                    </div>
                                </div>
                            <?php
                        }
                    ?>
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

        <!-- Text Update Modal -->
    
        <!-- Add Address Modal -->
        <!--<div class="modal fade" id="addAddressModal" tabindex="-1" aria-labelledby="addAddressModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAddressModalLabel"><?php //echo lang('add_address'); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addressForm">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="line1Input" class="form-label">Line 1:</label>
                                        <input type="text" id="line1Input" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="line2Input" class="form-label">Line 2:</label>
                                        <input type="text" id="line2Input" class="form-control">
                                    </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cityInput" class="form-label">City:</label>
                                    <input type="text" id="cityInput" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stateInput" class="form-label">State:</label>
                                    <input type="text" id="stateInput" class="form-control">
                                </div>
                            </div>
                        </div>  
                        
                        <div class="row">
                            <div class="col-md-6">                  
                                    <div class="mb-3">
                                        <label for="postalCodeInput" class="form-label">Postal Code:</label>
                                        <input type="text" id="postalCodeInput" class="form-control">
                                    </div>
                            </div>

                            <div class="col-md-6"> 
                                <div class="mb-3">
                                    <label for="countryInput" class="form-label">Country:</label>
                                    <input type="text" id="countryInput" class="form-control">
                                </div>
                            </div>
                        </div>        
                        

                        <div class="row">
                                
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="phoneInput" class="form-label">Phone:</label>
                                    <input type="text" id="phoneInput" class="form-control">
                                </div>
                            </div>
                        
                        </div>   
                        
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="submitAddress"><?php //echo lang('add_address'); ?></button>
                    </div>
                </div>
            </div>
        </div>-->
    
        <!-- Edit Address Modal -->
        <!--<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAddressModalLabel">Edit Address</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editAddressForm">
                            <div class="row">                            
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="editLine1Input" class="form-label">Line 1:</label>
                                        <input type="text" id="editLine1Input" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            
                            <div class="row">
                                
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="editLine2Input" class="form-label">Line 2:</label>
                                        <input type="text" id="editLine2Input" class="form-control">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editCityInput" class="form-label">City:</label>
                                        <input type="text" id="editCityInput" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editStateInput" class="form-label">State:</label>
                                        <input type="text" id="editStateInput" class="form-control">
                                    </div>
                                </div>
                            </div>        
                            

                            <div class="row">
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editPostalCodeInput" class="form-label">Postal Code:</label>
                                        <input type="text" id="editPostalCodeInput" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editCountryInput" class="form-label">Country:</label>
                                        <input type="text" id="editCountryInput" class="form-control">
                                    </div>
                                </div>

                            </div>        
                            <div class="row">
                                
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="editPhoneInput" class="form-label">Phone:</label>
                                        <input type="text" id="editPhoneInput" class="form-control">
                                    </div>
                                </div>
                            </div>        
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="updateAddress">Update Address</button>
                    </div>
                </div>
            </div>
        </div>-->
    </div>
</section>

<script type="text/javascript">
var addresses = <?= !empty($addresses) ? json_encode($addresses) : 'false'; ?>;
</script>
