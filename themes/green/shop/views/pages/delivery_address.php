<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .selected-address {
        border: 2px solid greenyellow !important;
    }
    .address-container {
        float: left;
        width: 48%;
        margin: 10px;
        height: 150px;
    }
</style>

<section class="page-contents">

    <div class="container container-max-width">
        <div class="row">
            <div class="col-md-12">
                <div class="row checkoutbox-k">

                    <div class="col-sm-12">
                        <div class="panel panel-default margin-top-lg checkLeftCol-k">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-address margin-right-sm"></i>
                                <?= lang('select_delivery_address'); ?>

                            </div>
                            <div class="panel-body">
                                <div class="border rounded p-3 pb-5 mb-4 address-container" data-address-id="default"
                                    style="cursor: pointer;">
                                    <button class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-4 py-1 "
                                        style="font-size:10px !important; border: 1px solid; border-radius: 50% !important">
                                        Default</button>
                                        <div style="float: right"> <a href="?action=editaddress&id=default" style="float:right"><i
                                            class="bi bi-pencil fs-5 text-primary"></i></a></div>
                                   
                                        <div class="d-flex">

                                            <div class="addressDetail d-flex align-items-center">
                                                <div class="addicon "><i class="bi bi-geo-alt fs-5 purpColor"></i></div>
                                                <div class="ms-3">
                                                    <p class="m-0 fs-6 fw-semibold">
                                                        <?= $address->first_name; ?>
                                                        <?= $address->last_name; ?> <br>
                                                        <?= $default_address->address; ?>,
                                                        <?= $default_address->city; ?> ,
                                                        <?= $default_address->state; ?>
                                                        <?= $default_address->country; ?>
                                                    </p>
                                                    <p class="m-0 fs-6 fw-semibold"> +966
                                                        <?= $default_address->phone; ?> <i
                                                            class="bi bi-check-circle-fill ms-2 purpColor"></i>
                                                    </p>
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
                                            data-address-id="<?= $address->id; ?>" style="cursor: pointer;">
                                            <div style="float: right">
                                            <a href="?action=editaddress&id=<?= $address->id; ?>" ><i
                                                    class="bi bi-pencil fs-5 text-primary"></i>
                                            </a>
                                            <a onclick="return confirmDelete(<?= $address->id; ?>)" style="padding-left: 10px;" ><i
                                                    class="bi bi-trash fs-5 text-danger"></i></a>
                                           </div>        
                                            <div class="d-flex">
                                                <div class="addressDetail d-flex align-items-center">
                                                    <div class="addicon "><i class="bi bi-geo-alt fs-5 purpColor"></i>
                                                    </div>
                                                      
                                                    <div class="ms-3" style="clear: both">
                                                        <p class="m-0 fs-6 fw-semibold">
                                                        <?= $address->first_name; ?>
                                                        <?= $address->last_name; ?> <br>
                                                            <?= $address->line1; ?>,
                                                            <?= $address->city; ?>,
                                                            <?= $address->state; ?>
                                                            <?= $address->country; ?>
                                                        </p>
                                                        <p class="m-0 fs-6 fw-semibold"> +966
                                                            <?= $address->phone; ?>
                                                            <?php if ($address->is_verified == 1): ?>
                                                                <i class="bi bi-check-circle-fill ms-2 purpColor"></i>
                                                            <?php else: ?>
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
                                <div style="clear: both">
                                    <?php
                                    $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'checkoutAddress'];
                                    echo form_open('shop/confirmaddress', $attrib);
                                    ?>

                                    <input type="hidden" id="selected-address-id" name="selected_address_id" required>

                                    <a href="?action=addnewaddress"
                                        class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-4 py-1 "
                                        style="font-size:18px !important;"> Add New Address</a>
                                    <a href="<?= base_url() . 'cart/checkout'; ?>"
                                        class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-4 py-1 "
                                        style="font-size:18px !important;"> Cancel</a>
                                    <button type="submit"
                                        class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-4 py-1 "
                                        style="font-size:18px !important;"> Confirm</button>

                                    <?= form_close(); ?>
                                </div>
                            </div>
                        </div>
                    </div>


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

            // Check the hidden radio button for form submission
            $("#address-selection-radio").prop("checked", true);
        });

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