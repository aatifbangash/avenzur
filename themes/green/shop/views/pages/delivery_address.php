<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .selected-address {
        border: 2px solid greenyellow !important;
    }
</style>
<?php


?>

<section class="page-contents">

    <div class="container container-max-width">
        <div class="row">
            <div class="col-md-12">
                <div class="row checkoutbox-k">

                    <div class="col-sm-8">
                        <div class="panel panel-default margin-top-lg checkLeftCol-k">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-shopping-cart margin-right-sm"></i>
                                <?= lang('checkout'); ?>
                                <a href="<?= site_url('cart'); ?>" class="pull-right back-k">
                                    <i class="fa fa-share"></i>
                                    <?= lang('back_to_cart'); ?>
                                </a>
                            </div>
                            <div class="panel-body">
                                Select Delivery Address

                                <div class="border rounded p-3 pb-5 mb-4 address-container" data-address-id="default" style="cursor: pointer;">
                                    <button class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-4 py-1 "
                                        style="font-size:10px !important;"> Default</button>
                                    <a href="?action=editaddress&id=default" style="float:right">Edit Address</a>
                                    <div class="d-flex">

                                        <div class="addressDetail d-flex align-items-center">
                                            <div class="addicon "><i class="bi bi-geo-alt fs-5 purpColor"></i></div>
                                            <div class="ms-3">
                                                <p class="m-0 fs-6 fw-semibold">
                                                    <?= $default_address->address; ?>, <?= $default_address->city; ?> ,
                                                    <?= $default_address->state; ?>
                                                    <?= $default_address->country; ?>
                                                </p>
                                                <p class="m-0 fs-6 fw-semibold">
                                                    <span class="phone_number"> +966 <?= $default_address->phone; ?> </span>
                                                    <i class="bi bi-check-circle-fill ms-2 purpColor"></i></p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <?php
                                if (!empty($addresses)) {


                                    $r = 1;
                                    foreach ($addresses as $address) {
                                        ?>
                                        <div class="border rounded p-3 pb-5 mb-4 address-container"
                                            data-address-id="<?=$address->id;?>" style="cursor: pointer;">
                                            <a href="?action=editaddress&id=<?=$address->id;?>" style="float:right">Edit Address </a> <br>
                                            <a href="?action=deleteaddress&id=<?=$address->id;?>" style="float:right">Delete</a>
                                            <div class="d-flex">
                                                <div class="addressDetail d-flex align-items-center">
                                                    <div class="addicon "><i class="bi bi-geo-alt fs-5 purpColor"></i></div>
                                                    <div class="ms-3">
                                                        <p class="m-0 fs-6 fw-semibold">
                                                            <?= $address->line1; ?>,
                                                            <?= $address->city; ?>,
                                                            <?= $address->state; ?>
                                                            <?= $address->country; ?>
                                                        </p>
                                                        <p class="m-0 fs-6 fw-semibold"> +966
                                                            <span class="phone_number"><?= $address->phone; ?> </span>
                                                            <?php if($address->is_verified == 1):?>
                                                            <i class="bi bi-check-circle-fill ms-2 purpColor"></i>
                                                            <?php else:?>
                                                                <i class="bi bi-exclamation-triangle text-danger fs-5"></i>
                                                            <?php endif; ?>    
                                                        </p>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                        <?php
                                        $r++;
                                    }

                                }

                                ?>
                                  
                                  <?php 
                                    $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'checkoutAddress'];
                                    echo form_open('shop/confirmaddress', $attrib); 
                                ?>   
                                  
                                <input type="hidden" id="selected-address-id" name="selected_address_id" required>

                               


                                <a href="?action=addnewaddress"
                                    class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-4 py-1 "
                                    style="font-size:18px !important;"> Add New Address</a>
                                <a href="<?=base_url().'cart/checkout';?>" class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-4 py-1 "
                                    style="font-size:18px !important;"> Cancel</a>
                                <button type="submit" id="confirm-address" class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-4 py-1 "
                                    style="font-size:18px !important;"> Confirm</button>

                                    <?= form_close(); ?>    

                            </div>
                        </div>
                    </div>


                </div>

            </div>
        </div>
    </div>
</section>

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

            // Check the hidden radio button for form submission
            $("#address-selection-radio").prop("checked", true);
        });
        
        $("#confirm-address").on("click", function (e) {
            e.preventDefault(); 

            var selected_phone = $('.selected-address .phone_number').text();
            alert(selected_phone);
        });
    });
</script>