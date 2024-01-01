<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php //print_r($default_address);?>
<?php
 if (!$this->loggedIn) {
redirect('login') ; 
}
$cart_contents = $this->cart->contents();

// print_r($cart_contents);
$not_express_items = 0;
foreach ($cart_contents as $cartItem) {
    $cart_item_code = $cartItem['code'];
    if (strpos($cart_item_code, 'AM-') !== false || strpos($cart_item_code, 'IH-') !== false) {
        $not_express_items++;
    }
}

$calculateShipping = 0;
if ($this->Settings->indian_gst) {
    $istates = $this->gst->getIndianStates();
}

?>
<style>
 h3 {
    font-size: 25px !important;
 }

</style>
<section class="page-contents" id="checkout-page" style="background:white !important;">
    <div class="container container-max-width">
    
                                <?php if ($this->session->flashdata('validation_errors')) {
                                    ?>
                                    <div class="panel-body">
                                    <div class="alert alert-danger">
                                        <?= $this->session->flashdata('validation_errors') ?>
                                        <?php $this->session->set_flashdata('validation_errors', null) ?>
                                    </div>
                                    </div>       
                                    <?php
                                } ?>
                   
        <div class="row">
            <div class="col-md-8">
                <h3 class=" fw-bold pb-2">Shipping Address</h3>
                <div class=" border rounded p-3 pb-5 mb-4">
                    <div class="d-flex">
                        
                        <div class="addressDetail d-flex align-items-center">
                            <div class="addicon "><i class="bi bi-geo-alt fs-5 purpColor"></i></div>
                            <div class="ms-3">
                                <p class="m-0 fs-6 fw-semibold">
                                    <?php echo isset($default_address->line1)?$default_address->line1:$default_address->address;?>
                                </p>
                                <p class="m-0 fs-6 fw-semibold"> +966  <?php echo isset($default_address)?$default_address->phone:'';?> <i class="bi bi-check-circle-fill ms-2 purpColor"></i></p>  
                            </div>                                                             
                        </div>

                        <?php //&& !$this->Staff
                        if (count($addresses) < 6 && !$this->Staff) {?>
                                                <p class="m-0 ms-auto">
                                                <a href="?action=changeaddress" class=" text-decoration-none">Change Address</a>
                                            </p>
                                           <?php }
                                            if ($this->Settings->indian_gst && (isset($istates))) {
                                            ?>
                                                <script>
                                                    var istates = <?= json_encode($istates); ?>
                                                </script>
                                            <?php
                                            } else {
                                                echo '<script>var istates = false; </script>';
                                            } ?>
                        
                    </div>
                </div>


                <h3 class=" fw-bold pb-2">Your Order</h3>
                <div class=" border rounded p-3 pb-5 mb-4">
                    <div class="">
                        <h3 class="fs-5 fw-bold">Shipment <span class="fs-5 fw-medium purpColor">(<?php echo count($cart_contents)?> item<?php echo count($cart_contents) > 1 ? 's':'';?>)</span></h3>
                        <?php if(!empty($cart_contents)) {
                            foreach($cart_contents as $key => $item) { ?>
                            <div class="addressDetail row align-items-center">
                                <div class="addicon col-md-2">
                                    <?php $image = $item['image'] != '' ? base_url() . 'assets/uploads/'.$item['image'] : '';?>
                                        
                                    <img src="<?php echo $image; ?>" class="w-100">
                                </div>
                                <div class="ms-2 col-md-4">
                                    <p class="m-0 fs-6 fw-semibold">
                                    <?php echo $item['name'];?>  
                                    </p>
                                    <p class="m-0 fs-6 fw-semibold mt-2"> SAR <?php echo $item['price'];?> <br /><span style="font-size: 13.5px;">Quantity (<?php echo $item['qty'];?>)</span></p>  
                                </div>                                                             
                            </div>
                            <hr />
                        <?php } }?>
                       
                    </div>
                </div>

                

            </div>
            
        <div class="col-md-4">
                <div class="mt-5">
                    <h2 class=" fw-bold pb-2 border-bottom m-0">Payment</h2>
                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                        <div class="form-check fs-5">
                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" checked>
                            <label class="form-check-label fw-semibold" for="flexRadioDefault2">
                                Debit / Credit Card Payment
                            </label>
                            <?php echo shop_form_open('order', 'class="validate addressform-k"'); ?>
                            <input type="text" class="form-control required px-0 pt-1" style="margin-bottom: 5px;padding: 12px !important;font-size: 14px;" name="card_name" value="" id="card_name" placeholder="Cardholder Name" />
                            <input type="text" class="form-control required px-0 pt-1" style="margin-bottom: 5px;padding: 12px !important;font-size: 14px;" name="card_number" value="" id="card_number" placeholder="Card Number" />
                            <input type="text" class="form-control required px-0 pt-1" style="margin-bottom: 5px;padding: 12px !important;font-size: 14px;" name="card_expiry_year" value="" id="card_expiry_year" placeholder="Card Expiry Year" />
                            <input type="text" class="form-control required px-0 pt-1" style="margin-bottom: 5px;padding: 12px !important;font-size: 14px;" name="card_expiry_month" value="" id="card_expiry_month" placeholder="Card Expiry Month" />
                            <input type="text" class="form-control required px-0 pt-1" style="margin-bottom: 5px;padding: 12px !important;font-size: 14px;" name="card_cvv" value="" id="card_cvv" placeholder="Card Cvv" />
                        </div>
                        <img src="https://avenzur.com/assets/images/banners/pay.png" alt="paycard" class=" w-25 ">
                    </div>
                </div>

                <div class="mt-4">
                    <h2 class=" fw-bold pb-2 fs-2  m-0">Shipping</h2>
                    <div class="d-flex align-items-center  py-3 standard-div">
                        <div class="form-check fs-5">
                        <input class="form-check-input" type="radio" name="delivery" id="flexRadiostandard" checked value="shipping_standard">
                        <label class="form-check-label fs-6 fw-semibold" for="flexRadiostandard">
                            Standard delivery
                        </label>
                        </div>
                        <div>
                            <p class="m-0 fst-italic text-white px-4  rounded" style="background:#662d91">Free</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center  py-1 standard-div" id="express-delivery-details" style="display: none !important;">
                        <div class="form-check fs-5">
                        <input class="form-check-input" type="radio" name="delivery" id="express-delivery-check" value="shipping_express">
                        <label class="form-check-label fs-6 fw-semibold" for="express-delivery-check">
                            Express delivery
                        </label>
                        </div>
                        <div>
                            <p class="m-0 fs-6 fw-semibold px-3 mx-1">21 SAR</p>
                        </div>
                    </div>
                </div>
            
           <?php
                $total = $this->sma->convertMoney($this->cart->total(), false, false);
                $order_tax = $this->sma->convertMoney($this->cart->order_tax(), false, false);

                if(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($order_tax)) > 200){
                    $shipping = $this->sma->convertMoney(0, false, false);
                }else{
                    if ($not_express_items > 0) {
                        $shipping = $this->sma->convertMoney(32, false, false);
                    } else {
                        $shipping = $this->sma->convertMoney($calculateShipping, false, false);
                    }
                }

            ?>                     

           <input type="hidden" name="payment_method" value="directpay" id="directpay"
                                                   required="required">
            <input type="hidden" name="address" id="address" value="<?php echo isset($default_address->company_id) ? $default_address->id : 'default';?>">    
            <input type="hidden" name="shipping_city" id="shipping_city" value="<?php echo $default_address->city;?>">                                       
            <input type="hidden" name="shipping_state" id="shipping_state" value="<?php echo $default_address->state;?>">          
            <input type="hidden" name="shipping_country" id="shipping_country" value="<?php echo $default_address->country;?>">                             
            <input type="hidden" id="total-price" value="<?= $total ?>"/>
                <h3 class=" fw-bold pb-2">Order Summary</h3>
                <div class="border rounded py-3 px-2">
                    <h4 class="m-0 fw-semibold mb-1">Order Details</h4>
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="m-0 my-2">Sub total</h4>
                            <h4 class="m-0 my-2">Shipping Fee</h4>
                           
                        </div>
                        <div class="text-end">
                            <h4 class="m-0 my-2"> <?= $this->sma->formatMoney($total, $selected_currency->symbol); ?>
                                               </h4>
                            <h4 class="text-success m-0 my-2" id="shipping-price"> <?= $this->sma->formatNumber($shipping); ?></span><?= $selected_currency->symbol ?></h4>
                           
                        </div>
                    </div>
                    <hr class="mb-0 mt-2">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mt-3"><span class="fw-semibold">Total</span> Incl. VAT</h4>
                        </div>
                        <div>
                            <h4 class="mt-3"><span class="fw-semibold"  id="grand-total-price">SAR <?= $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($order_tax) + $this->sma->formatDecimal($shipping))); ?></span><?= $selected_currency->symbol ?></span> </h4>
                        </div>
                    </div>    
                </div>
                <div class="payBtn">
                <!-- <button type="button" class="btn primary-buttonAV mt-3 pt-1 rounded-4 w-100 fs-1">Proceed to Pay</button> -->
                        <?php
                                            if ((!empty($addresses) || !empty($default_address)) && !$this->Staff) {
                                            ?>
                                            <input type="hidden" name="express_delivery" id="express_delivery"
                                                   value="Standard"/>
                                            <input type="hidden" id="shipping-input" name="shipping"
                                                   value="<?= $calculateShipping ?>"/>
                                            <!--<input type="button" name="proceed_pay" class="btn primary-buttonAV mt-3 pt-1 rounded-4 w-100 payment-k" value="Proceed to Pay" id="proceed-payment" />-->
                                            <?php
                                           echo form_submit('add_order', lang('Proceed to Pay'), 'class="btn primary-buttonAV mt-3 pt-1 rounded-4 w-100 payment-k"');
                                            } elseif ($this->Staff) {
                                                echo '<div class="alert alert-warning margin-bottom-no">' . lang('staff_not_allowed') . '</div>';
                                            } else {
                                                echo '<div class="alert alert-warning margin-bottom-no">' . lang('please_add_address_first') . '</div>';
                                            }
                                        ?>                       
               </div>

                <div class=" opacity-50 purpColor mt-4">
                    <h4 class="fw-semibold fs-5 "><i class="bi bi-shield-check me-1"></i> Security and Privacy</h4>
                    <h4 class="fs-6 "> Our checkout is safe and secure. Your Personal and payment information is securely transmitted via 128-bit encryption. We do not store any payment card information on our systems</h4>
                    <h4 class="fs-6 mt-4"> Terms of USe Terms of Sale Privacy Policy</h4>
                </div>
            </div>
            <?php  echo form_close();?>
        </div>
    </div>
</section>


<script>
   $(document).ready(function () {
        $('#proceed-payment').click(function (e) {
            e.preventDefault(); 

            var card_name = $('#card_name').val();
            var card_number = $('#card_number').val();
            var card_cvv = $('#card_cvv').val();
            var card_expiry = $('#card_expiry').val();
            var shipping = $('#shipping-input').val();
            var address = $('#address').val();
            var payment_method = $('#directpay').val();
            var express_delivery = $('#express_delivery').val();

            $.ajax({
                type: 'POST',
                url: site.site_url + 'pay/process_payment',
                data: { 
                    token: site.csrf_token_value, 
                    card_name: card_name, 
                    card_number: card_number, 
                    card_cvv: card_cvv, 
                    card_expiry: card_expiry,
                    shipping: shipping,
                    address: address,
                    payment_method: payment_method,
                    express_delivery: express_delivery
                },
                success: function (response) {
                    //var respObj = JSON.parse(response);
                    console.log(response);
                    //if (respObj.status == 'success' || respObj.code == 1) {
                        
                    //}
                },
                error: function (error) {
                    console.error(error);
                }
            });
        });

        $('#shipping-phone.iti__flag-container').click(function () {
            var countryCode = $('#shipping-phone.iti__selected-flag').attr('title');
            var countryCode = countryCode.replace(/[^0-9]/g, '')
            $('#shipping_phone').val("");
            $('#shipping_phone').val("+" + countryCode + " " + $('#shipping_phone').val());
        });

        $('#account-phone.iti__flag-container').click(function () {
            var countryCode = $('#account-phone.iti__selected-flag').attr('title');
            var countryCode = countryCode.replace(/[^0-9]/g, '')
            $('#phone').val("");
            $('#phone').val("+" + countryCode + " " + $('#phone').val());
        });

        $('#shipping_country').blur(function () {
            $("#express-delivery-check").prop('checked', false)
            var city = $('#shipping_city').val();
            var country = $('#shipping_country').val();
            calCulateShipping(city, country);
        })

        $('#shipping_city').blur(function () {
            $("#express-delivery-check").prop('checked', false)
            var city = $('#shipping_city').val();
            var country = $('#shipping_country').val();
            calCulateShipping(city, country);
        })

        $('#express-delivery-check').change(function () {
          
            var city = $('#shipping_city').val();
            var country = $('#shipping_country').val();
        

            if ($(this).prop('checked') == true) {
                document.getElementById('express_delivery').value = 'Express';
            } else {
                document.getElementById('express_delivery').value = 'Standard';
            }

            calCulateShipping(city, country, $(this).prop('checked'));
        })

        $('#flexRadiostandard').change(function () {
          
          var city = $('#shipping_city').val();
          var country = $('#shipping_country').val();
      
          if ($(this).prop('checked') == true) {
              document.getElementById('express_delivery').value = 'Standard';
              calCulateShipping(city, country, false);
          } else {
              document.getElementById('express_delivery').value = 'Express';
              calCulateShipping(city, country, true);
          }

      })

        $('.payment-address').click(function (e) {
            $("#express-delivery-check").prop('checked', false)
            var addressObject = $(this).data('payload');
            if (addressObject) {
                var country = addressObject.country
                var city = addressObject.city
                calCulateShipping(city, country)
            }
        })
    });
    // console.log('testss');
    calCulateShipping($('#shipping_city').val(), $('#shipping_country').val());
    $('#express-delivery-check').change(function () {
          
            var city = $('#shipping_city').val();
            var country = $('#shipping_country').val();
        

            if ($(this).prop('checked') == true) {
                document.getElementById('express_delivery').value = 'Express';
            } else {
                document.getElementById('express_delivery').value = 'Standard';
            }

            calCulateShipping(city, country, $(this).prop('checked'));
        });
    // Vanilla Javascript
    var input = document.querySelector("#phone");
    var input_shipping_phone = document.querySelector("#shipping_phone");
    var non_express_items = '<?php echo $not_express_items; ?>';
    window.intlTelInput(input, ({
        initialCountry: "SA"
    }));

    window.intlTelInput(input_shipping_phone, ({
        initialCountry: "SA"
    }));

    $('#phone').val("+966");
    $('#shipping_phone').val("+966");

    document.getElementById('manual-shipping-check').onchange = function (e) {
        document.getElementById('google-map-selected-address').value = '';
        document.getElementById('shipping_line1').value = '';
        document.getElementById('shipping_city').value = '';
        document.getElementById('shipping_country').value = '';
        document.getElementById('shipping_country_dropdown').value = $("#shipping_country_dropdown option:first").val();
        document.getElementById('shipping_city_dropdown').value = $("#shipping_city_dropdown option:first").val();
        document.getElementById('shipping_state').value = '';
        document.getElementById('shipping_latitude').value = '';
        document.getElementById('shipping_longitude').value = '';

        let manualMapBlock = document.getElementById('manual-shipping-address')
        if (e.target.checked === true) {
            manualMapBlock.style.display = 'block';
        } else {
            manualMapBlock.style.display = 'none';
        }
    }

    function calCulateShipping(city, country, isExpressDelivery = false) {
       // alert(city);
        //$("#express-delivery-check").prop("disabled", true);
        $("#express-delivery-check").hide();
        $("#express-delivery-details").css("display", "none !important");
        //$("#express-delivery-details").hide();
        //$('.payment-k').prop('disabled', true);

        var shipping = parseInt('<?= round($calculateShipping); ?>');
        var deliveryDays = "Not Available";
        if (non_express_items > 0) {
            deliveryDays = '4 to 6 days';
        }

        var totalPrice = parseFloat($('#total-price').val());
        var totalOrderTax = parseFloat($('#total-order-tax').val());
        var orderWithTax = totalPrice + totalOrderTax;
        var saudiOrder = 0;

        if(orderWithTax > 200){
            shipping = 0;
        }

        if (city != '' || country != '') {

            if (country.toLowerCase() === 'saudi arabia' || country.toLowerCase() === 'saudi' || country.toLowerCase() === 'saudia' || country.toLowerCase() === 'al saudi arabia' || country.toLowerCase() === 'al-saudi arabia' || country.toLowerCase() === 'al-saudi' || country.toLowerCase() === 'ksa' || country.toLowerCase() === 'kingdom of saudi arabia' || country.toLowerCase() === 'al saudi' || country.toLowerCase() === 'al saudia' || country.toLowerCase() === 'al-saudia' || [
                'سعودی عرب',
                'آلسعوديه',
                'سعودی',
                'سعودية',
                'السعودية',
                'المملكة العربية السعودية'
            ].includes(country)) {
                $('.payment-k').prop('disabled', false)
                shipping = 0
                deliveryDays = "2 to 4 days"

                if (city.toLowerCase() === 'riyadh' || city.toLowerCase() === 'al riyadh' || city.toLowerCase() === 'al-riyadh' || [
                    'الرياض',
                    'ریاض'
                ].includes(city)) {
                    shipping = 0 // 19 SAR
                    deliveryDays = "1 to 2 days"
                    //$("#express-delivery-check").prop("disabled", false);
                    $("#express-delivery-check").show();
                    //$("#express-delivery-details").css("display", "block !important");
                    $("#express-delivery-details").show();

                    if (isExpressDelivery == true) {
                        shipping = 21
                        deliveryDays = "5 to 6 hours"
                    }
                }

                if (city.toLowerCase() === 'jeddah' || city.toLowerCase() === 'al jeddah' || city.toLowerCase() === 'al-jeddah' || [
                    'جده'
                ].includes(city)) {
                    shipping = 0
                    deliveryDays = "1 to 2 days"
                }

                if (orderWithTax > 200) {
                    shipping = 0;
                }

                saudiOrder = 1;

            } else if (['bahrain',
                'kuwait',
                'oman',
                'qatar',
                'united arab emirates',
                'uae']
                .includes(country.toLowerCase()) || [
                'البحرين',
                'دولة قطر',
                'قطر',
                'سلطنة عمان',
                'عمان',
                'الكويت',
                'الإمارات العربية المتحدة',
                'الإمارات'
            ].includes(country)) { //GCC
                $('.payment-k').prop('disabled', false)
                {
                    shipping = 32
                    deliveryDays = "4 to 6 days"
                }
            } else {
                shipping = 38
                deliveryDays = "5 to 8 days"
            }

            var grandTotalPrice = totalPrice + shipping;

            if (non_express_items > 0) {
                if (saudiOrder == 1 && orderWithTax > 200) {
                    shipping = 0;
                } else {
                    shipping = 32;
                }
                /*else{
                    shipping = 32;
                }*/
                deliveryDays = '4 to 6 days';

                //$("#express-delivery-check").prop("disabled", true);
                $("#express-delivery-check").show();
                //$("#express-delivery-details").css("display", "block !important");
                $("#express-delivery-details").show();
            }

            $('#shipping-price').text(parseFloat(shipping).toFixed(2))
            $('#grand-total-price').text(parseFloat(grandTotalPrice).toFixed(2))
            $('#shipping-input').val(parseFloat(shipping).toFixed(2));
            $('#delivery-days').text(deliveryDays);
        }
    }

 
</script>