<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php //print_r($default_address);?>
<?php
 if (!$this->loggedIn) {
redirect('login') ; 
}
$cart_contents = $this->cart->contents();
//print_r($this->session->userdata('coupon_details')['free_shipping']);exit;
// print_r($cart_contents);
$not_express_items = 0;
$cart_total_items = 0;
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

 .form-control::placeholder {
    opacity: 0.5;
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
                <div class=" border rounded p-3 mb-4">
                    <div class="d-flex mobile-wrap justify-content-between mobile-start ">
                        
                        <div class="addressDetail d-flex align-items-center">
                            
                            <div class="">
                                <p class="m-0 fs-6 fw-semibold d-flex mb-3 align-items-start" style="font-size: 0.87rem!important;"> <i class="bi bi-geo-alt fs-4 pe-2 purpColor"></i>
                                    <?php echo isset($default_address->line1)?$default_address->line1:$default_address->address;?>
                                </p>
                                <p class="m-0 fs-6 fw-semibold d-flex align-items-center" style="font-size: 0.87rem!important;"><i class="bi bi-phone fs-4 pe-2 purpColor"></i>  +966  <?php echo isset($default_address)?$default_address->phone:'';?> <i class="bi bi-check-circle-fill ms-2 purpColor"></i></p>  
                            </div>                                                             
                        </div>

                        <?php //&& !$this->Staff
                        if (!$this->Staff) {?>
                                                <p class="m-0 change-address-link-cont">
                                                <a href="?action=changeaddress" class="  d-flex change-address-link align-items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <g clip-path="url(#clip0_2587_2962)">
                                                <path d="M12.436 0.619885L4.30804 8.74789C3.99761 9.05665 3.7515 9.42392 3.58397 9.82845C3.41643 10.233 3.33081 10.6667 3.33204 11.1046V11.9999C3.33204 12.1767 3.40228 12.3463 3.52731 12.4713C3.65233 12.5963 3.8219 12.6666 3.99871 12.6666H4.89404C5.33189 12.6678 5.76562 12.5822 6.17015 12.4146C6.57467 12.2471 6.94195 12.001 7.25071 11.6906L15.3787 3.56255C15.7683 3.172 15.9871 2.64287 15.9871 2.09122C15.9871 1.53957 15.7683 1.01044 15.3787 0.619885C14.9825 0.241148 14.4555 0.0297852 13.9074 0.0297852C13.3593 0.0297852 12.8323 0.241148 12.436 0.619885ZM14.436 2.61989L6.30804 10.7479C5.93213 11.1215 5.42405 11.3318 4.89404 11.3332H4.66538V11.1046C4.66677 10.5745 4.87709 10.0665 5.25071 9.69055L13.3787 1.56255C13.5211 1.42652 13.7105 1.35061 13.9074 1.35061C14.1043 1.35061 14.2937 1.42652 14.436 1.56255C14.576 1.7029 14.6546 1.89301 14.6546 2.09122C14.6546 2.28942 14.576 2.47954 14.436 2.61989Z" fill="#662D91"/>
                                                <path d="M15.3333 5.986C15.1565 5.986 14.987 6.05624 14.8619 6.18126C14.7369 6.30629 14.6667 6.47586 14.6667 6.65267V10H12C11.4696 10 10.9609 10.2107 10.5858 10.5858C10.2107 10.9609 10 11.4696 10 12V14.6667H3.33333C2.8029 14.6667 2.29419 14.456 1.91912 14.0809C1.54405 13.7058 1.33333 13.1971 1.33333 12.6667V3.33333C1.33333 2.8029 1.54405 2.29419 1.91912 1.91912C2.29419 1.54405 2.8029 1.33333 3.33333 1.33333H9.36133C9.53815 1.33333 9.70771 1.2631 9.83274 1.13807C9.95776 1.01305 10.028 0.843478 10.028 0.666667C10.028 0.489856 9.95776 0.320286 9.83274 0.195262C9.70771 0.0702379 9.53815 0 9.36133 0L3.33333 0C2.4496 0.00105857 1.60237 0.352588 0.97748 0.97748C0.352588 1.60237 0.00105857 2.4496 0 3.33333L0 12.6667C0.00105857 13.5504 0.352588 14.3976 0.97748 15.0225C1.60237 15.6474 2.4496 15.9989 3.33333 16H10.8953C11.3333 16.0013 11.7671 15.9156 12.1718 15.7481C12.5764 15.5806 12.9438 15.3345 13.2527 15.024L15.0233 13.252C15.3338 12.9432 15.58 12.576 15.7477 12.1715C15.9153 11.767 16.0011 11.3332 16 10.8953V6.65267C16 6.47586 15.9298 6.30629 15.8047 6.18126C15.6797 6.05624 15.5101 5.986 15.3333 5.986ZM12.31 14.0813C12.042 14.3487 11.7031 14.5337 11.3333 14.6147V12C11.3333 11.8232 11.4036 11.6536 11.5286 11.5286C11.6536 11.4036 11.8232 11.3333 12 11.3333H14.6167C14.5342 11.7023 14.3493 12.0406 14.0833 12.3093L12.31 14.0813Z" fill="#662D91"/>
                                                </g>
                                                <defs>
                                                <clipPath id="clip0_2587_2962">
                                                <rect width="16" height="16" fill="white"/>
                                                </clipPath>
                                                </defs>
                                                </svg>    
                                                Change Address</a>
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
                <div class=" border rounded p-3  mb-4 products-pay">
                    <div class="">
                        <h3 class="fs-5 fw-bold">Shipment <span class="fs-5 fw-medium purpColor">(<?php echo count($cart_contents)?> item<?php echo count($cart_contents) > 1 ? 's':'';?>)</span></h3>
                        <?php if(!empty($cart_contents)) {
                            foreach($cart_contents as $key => $item) { ?>
                            <div class="addressDetail d-flex align-items-center">
                                <div class="addicon">
                                    <?php $image = $item['image'] != '' ? base_url() . 'assets/uploads/'.$item['image'] : '';?>
                                        
                                    <img src="<?php echo $image; ?>" class="cart-img" style="object-fit: contain;">
                                </div>
                                <div class="ps-2">
                                    <p class="m-0 fs-6 fw-bold">
                                    <?php echo $item['name'];?>  
                                    </p>
                                    <p class="m-0 fs-6 fw-semibold mt-2 price"> SAR <?php echo $item['price'];?> <br /><span class="quantity" style="font-size: 13.5px;">Quantity (<?php echo $item['qty'];?>)</span></p>  
                                </div>                                                                
                            </div>
                            
                        <?php } }?>
                       
                    </div>
                </div>

                <div class="mt-4">
                    <h2 class=" fw-bold pb-2 fs-2  m-0">Shipping <span style="font-size: 16px;color: #662d91;margin-left: 14px"><?php echo $virtual_pharmacy_items > 0 ? '(Expected delivery 4 to 6 days)' : ''; ?></span></h2>
                    <div class="d-flex align-items-center  py-3 standard-div">
                        <div class="form-check px-0">
                        <input class="form-check-input" type="radio" name="delivery" id="flexRadiostandard" checked value="shipping_standard">
                        <label class="form-check-label fs-6 fw-semibold" for="flexRadiostandard">
                            Standard delivery
                        </label>
                        </div>
                        <div>
                            <p class="m-0 fst-italic text-white px-4  rounded" id="shipping-fees-span" style="background:#662d91">Free</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center  py-3 standard-div" id="express-delivery-details" style="display: none !important;">
                        <div class="form-check px-0">
                        <input class="form-check-input" type="radio" name="delivery" id="express-delivery-check" value="shipping_express">
                        <label class="form-check-label fs-6 fw-semibold" for="express-delivery-check">
                            Express delivery
                        </label>
                        </div>
                        <div>
                            <p class="m-0 fs-6 fw-semibold px-3 mx-1 delivery_pg" id="express-shipping-fees-span">21 SAR</p>
                        </div>
                    </div>
                </div>

            </div>
         
        <div class="col-md-4 payment-method-wrapper">
            <?php echo shop_form_open('order', 'class="validate addressform-k p-0"'); ?>
            <h3 class=" fw-bold pb-2 border-bottom m-0">Payment</h2>
            <div class="d-flex align-items-center justify-content-between py-3 border-bottom mobile-wrap  mobile-start">
                <div class="form-check px-0 w-100 d-flex flex-column">
                    <div class="d-flex w-100 radio-button-wrapper my-3">
                    <div class="d-flex align-items-center item1">
                        <input class="form-check-input" style="float:none;" type="radio" name="payment_method_details" onclick="showCardDetails();" value="1" id="flexRadioDefault2" checked>
                        <img src="<?= base_url('assets/images/creditcard.png'); ?>" style="width:40px;" alt="Credit Debit Card" />
                     </div>
                    <div class="d-flex align-items-center item2">
                        <input class="form-check-input" style="float:none;" type="radio" name="payment_method_details" onclick="hideCardDetails();" value="3" id="apple-pay">
                        <img src="<?= base_url('assets/images/applepay.svg'); ?>" style="width:55px;height:48px;" alt="apple-pay" />
                    </div>
                    <div class="d-flex align-items-center item3">
                        <input class="form-check-input" style="float:none;" type="radio" name="payment_method_details" onclick="hideCardDetails();" value="5" id="stc-pay">
                        <img src="<?= base_url('assets/images/stcpay.svg'); ?>" style="width:60px;height:100px;" alt="stc-pay" />
                    </div>
                    <div class="d-flex align-items-center item3">
                        <input class="form-check-input" style="float:none;" type="radio" name="payment_method_details" onclick="hideCardDetails();" value="4" id="ur-pay">
                        <img src="<?= base_url('assets/images/urpay.png'); ?>" style="width:60px;" alt="ur-pay" />
                    </div>
                    
                  </div>
                    <input type="text" class="form-control required px-0 pt-1" style="margin-bottom: 5px;padding: 12px !important;font-size: 14px;" value="" id="card_name" placeholder="John Doe" />
                    <div><input type="text" maxlength="19" class="form-control required px-0 pt-1" style="margin-bottom: 5px;padding: 12px !important;font-size: 14px;" value="" id="card_number" placeholder="5105 1051 0510 5100" />
                    <img src="" id="card_type_image" style="width: 30px; height: 30px;display:none;"></div>
                    <input type="text" class="form-control required px-0 pt-1" style="margin-bottom: 5px;padding: 12px !important;font-size: 14px;" value="" id="card_expiry_year" placeholder="12 / 31" />
                    <!--<input type="text" class="form-control required px-0 pt-1" style="margin-bottom: 5px;padding: 12px !important;font-size: 14px;" value="" id="card_expiry_month" maxlength="2" placeholder="Card Expiry Month" />-->
                    <input type="text" class="form-control required px-0 pt-1" style="margin-bottom: 5px;padding: 12px !important;font-size: 14px;" value="" id="card_cvv" maxlength="3" pattern="\d*" title="Please enter a 3-digit CVV" placeholder="358" />
                </div>
             
            </div>

            <img src="https://avenzur.com/assets/images/banners/pay.png" alt="paycard" class=" payment-method">

            <?php 
                $promo_applied = false;
                if(isset($this->session->userdata('coupon_details')['code'])){
                    $promo_applied = true;
                    $promo_code = $this->session->userdata('coupon_details')['code'].' Applied';
                }else{
                    $promo_code = '';
                }
            ?>

            <h3 class=" fw-bold pb-2 order-summary-title">Order Summary <span id="promo_span" style="<?php echo isset($promo_applied) ? ($promo_applied ? 'color:green;' : 'color:grey;') : ''; ?> font-size:16px; "><?php echo $promo_code; ?></span></h3>  
            
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

            <input type="hidden" name="payment_method" value="directpay" id="directpay" required="required">
            <input type="hidden" name="card_name" value="" id="card_name_hidden" required="required" />
            <input type="hidden" name="card_number" value="" id="card_number_hidden" required="required" />
            <input type="hidden" name="card_expiry_year" value="" id="card_expiry_year_hidden" required="required" />
            <!--<input type="hidden" name="card_expiry_month" value="" id="card_expiry_month_hidden" required="required" />-->
            <input type="hidden" name="card_cvv" value="" id="card_cvv_hidden" required="required" />
            <input type="hidden" name="address" id="address" value="<?php echo isset($default_address->company_id) ? $default_address->id : 'default';?>">    
            <input type="hidden" name="shipping_city" id="shipping_city" value="<?php echo $default_address->city;?>">                                       
            <input type="hidden" name="shipping_state" id="shipping_state" value="<?php echo $default_address->state;?>">          
            <input type="hidden" name="shipping_country" id="shipping_country" value="<?php echo $default_address->country;?>">                             
            <input type="hidden" id="total-price" value="<?= $total ?>"/>
                
                <div class="border rounded py-3 px-2">
                    <h4 class="m-0 fw-semibold mb-1">Order Details</h4>
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="m-0 my-2">Sub total</h4>
                            <h4 class="m-0 my-2">Shipping Fee</h4>
                            <h4 class="m-0 my-2">Discount</h4>
                        </div>
                        <div class="text-end">
                            <h4 class="m-0 my-2" id="sub-total-amt"> <?= $this->sma->formatMoney($total + $this->cart->get_total_discount(), $selected_currency->symbol); ?>
                                               </h4>
                            <h4 class="text-success m-0 my-2" id="shipping-price"> <?= $this->sma->formatNumber($shipping); ?><?= $selected_currency->symbol ?></h4>
                            <h4 class="text-success m-0 my-2" id="discount-amt"> <?= $this->sma->formatNumber($this->cart->get_total_discount()); ?><?= $selected_currency->symbol ?></h4>
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
                                                if($default_address->country != 'saudi arabia' && $default_address->country != 'saudi' && $default_address->country != 'saudia' && $default_address->country != 'al saudi arabia' && $default_address->country != 'al-saudi arabia' && $default_address->country != 'al-saudi' && $default_address->country != 'ksa' && $default_address->country != 'kingdom of saudi arabia' && $default_address->country != 'al saudi' && $default_address->country != 'al saudia' && $default_address->country != 'al-saudia' &&
                                                    $default_address->country != 'سعودی عرب' &&
                                                    $default_address->country != 'آلسعوديه' &&
                                                    $default_address->country != 'سعودی' &&
                                                    $default_address->country != 'سعودية' &&
                                                    $default_address->country != 'السعودية' &&
                                                    $default_address->country != 'المملكة العربية السعودية' &&
                                                    $default_address->country != 'SA'){
                                                    if($this->cart->total_items() > 2){
                                                        echo form_submit('add_order', lang('Proceed to Pay'), 'class="btn primary-buttonAV mt-3 pt-1 rounded-4 w-100 payment-k validate" id="proceed-to-payment"');
                                                    }else{
                                                        echo '<button class="btn primary-buttonAV mt-3 pt-1 rounded-4 w-100 payment-k validate" id="proceed-to-payment" disabled>'.lang('Proceed to Pay').'</button>';
                                                    }
                                                }else{
                                                    echo form_submit('add_order', lang('Proceed to Pay'), 'class="btn primary-buttonAV mt-3 pt-1 rounded-4 w-100 payment-k validate" id="proceed-to-payment"');
                                                }
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
                    <h4 class="fs-6 mt-4"> 
                        <a style="color: #662d91;text-decoration:none;margin-right: 12px;" target="blank" href="https://avenzur.com/shop/page/Terms-Conditions">Terms of USe</a> 
                        <a style="color: #662d91;text-decoration:none;" target="blank" href="https://avenzur.com/shop/page/privacy-policy">Privacy Policy</a>
                    </h4>
                </div>
            </div>
            <?php  echo form_close();?>
        </div>
    </div>
</section>


<script>

    if (userSessionEmail || userSessionPhone) {

        snaptr('track', 'ADD_CART', {'currency': 'SAR', 'price': '<?php  echo $total; ?>', 'payment_info_available':1, 'item_category': 'Medical' });
    }

    function showCardDetails(){
        document.getElementById('card_name').style.display = 'block';
        document.getElementById('card_number').style.display = 'block';
        //document.getElementById('card_type_image').style.display = 'block';
        document.getElementById('card_expiry_year').style.display = 'block';
        document.getElementById('card_cvv').style.display = 'block';

        var promo_code = '<?php echo $this->session->userdata('coupon_details')['code']; ?>';
        if(typeof promo_code != 'undefined' && promo_code != ''){
            $('#promo_span').css('color', 'green');
            $('#promo_span').text(promo_code+' Applied');
        }else{
            $('#promo_span').css('color', 'grey');
            $('#promo_span').text('No Code Applied');
        }
        
    }

    function hideCardDetails(){
        $('#card_number').val('');
        $('#card_cvv').val('');
        $('#card_expiry_year').val('');
        $('#card_name').val('');

        document.getElementById('card_name').style.display = 'none';
        document.getElementById('card_number').style.display = 'none';
        document.getElementById('card_type_image').style.display = 'none';
        document.getElementById('card_expiry_year').style.display = 'none';
        document.getElementById('card_cvv').style.display = 'none';

        var cardNum = '';

        $.ajax({
            url: site.base_url +'cart/apply_coupon',
            type: "POST",
            data: {token: site.csrf_token_value, card_number: cardNum},
            success: function (t) {
                var response = JSON.parse(t);
                if(response.status == 'success'){
                    if(response.action == 'add'){
                        $('#discount-amt').html(parseFloat(response.discount).toFixed(2) + '<?php echo $selected_currency->symbol; ?>');
                        $('#grand-total-price').html(parseFloat(response.total).toFixed(2));
                        $('#sub-total-amt').html(parseFloat(parseFloat(response.total) + parseFloat(response.discount)).toFixed(2) + '<?php echo $selected_currency->symbol; ?>');


                        $('#total-price').val(parseFloat(parseFloat(response.total)));
                        var city = $('#shipping_city').val();
                        var country = $('#shipping_country').val();
                        calCulateShipping(city, country, $('#express-delivery-check').prop('checked'));
                    }else if(response.action == 'subtract'){
                        $('#promo_span').css('color', 'grey');
                        $('#promo_span').text('Promo Not Applicable');
                        $('#discount-amt').html(parseFloat(response.discount).toFixed(2) + '<?php echo $selected_currency->symbol; ?>');
                        $('#grand-total-price').html(parseFloat(response.total).toFixed(2));
                        $('#sub-total-amt').html(parseFloat(parseFloat(response.total) + parseFloat(response.discount)).toFixed(2) + '<?php echo $selected_currency->symbol; ?>');

                        $('#total-price').val(parseFloat(parseFloat(response.total)));
                        var city = $('#shipping_city').val();
                        var country = $('#shipping_country').val();
                        calCulateShipping(city, country, $('#express-delivery-check').prop('checked'));
                    }
                }
            },
            error: function () {
            sa_alert(
                "Error!",
                "Ajax call failed, please try again or contact site owner.",
                "error",
                !0
            );
            },
        });
    }

   $(document).ready(function () {
        var currentYear = new Date().getFullYear();

        // Set the minimum and maximum allowed years
        //document.getElementById('card_expiry_year').setAttribute('min', currentYear);
        //document.getElementById('card_expiry_year').setAttribute('max', currentYear + 10); // Allowing the next 10 years

        $('#card_cvv').on('input', function() {
            var cvv = $(this).val();
            var cvvRegex = /^\d{3}$/;

            if (!cvvRegex.test(cvv)) {
                $('#cvvError').text('Please enter a valid 3-digit CVV');
            } else {
                $('#cvvError').text('');
            }
        });

        $('#card_name').change(function(){
            $('#card_name_hidden').val($(this).val());
        });

        $('#card_number').on('input', function() {
            // Get the input value and remove non-numeric characters
            var cardNumber = $(this).val().replace(/\D/g, '');

            // Detect card type based on the first digit
            var cardType = detectCardType(cardNumber);

            // Format the card number
            var formattedNumber = formatCardNumber(cardNumber, cardType);

            // Update the input value
            $(this).val(formattedNumber);

            // Update the card type image
            updateCardTypeImage(cardType);
        });

        $('#card_number').on('blur', function() {
            var cardNumber = $(this).val().replace(/\D/g, '');
            $.ajax({
                url: site.base_url +'cart/apply_coupon',
                type: "POST",
                data: {token: site.csrf_token_value, card_number: cardNumber},
                success: function (t) {
                    var response = JSON.parse(t);
                    
                    if(response.status == 'success'){
                        if(response.action == 'add'){
                            var promo_code = '<?php echo $this->session->userdata('coupon_details')['code']; ?>';
                            if(typeof promo_code != 'undefined' && promo_code != ''){
                                $('#promo_span').css('color', 'green');
                                $('#promo_span').text(promo_code+' Applied');
                            }else{
                                $('#promo_span').css('color', 'grey');
                                $('#promo_span').text('');
                            }

                            $('#discount-amt').html(parseFloat(response.discount).toFixed(2) + '<?php echo $selected_currency->symbol; ?>');
                            $('#grand-total-price').html(parseFloat(response.total).toFixed(2));
                            $('#sub-total-amt').html(parseFloat(parseFloat(response.total) + parseFloat(response.discount)).toFixed(2) + '<?php echo $selected_currency->symbol; ?>');


                            $('#total-price').val(parseFloat(parseFloat(response.total)));
                            var city = $('#shipping_city').val();
                            var country = $('#shipping_country').val();
                            calCulateShipping(city, country, $('#express-delivery-check').prop('checked'));
                        }else if(response.action == 'subtract'){
                            $('#promo_span').css('color', 'grey');
                            $('#promo_span').text('Code Not Applicable');

                            $('#discount-amt').html(parseFloat(response.discount).toFixed(2) + '<?php echo $selected_currency->symbol; ?>');
                            $('#grand-total-price').html(parseFloat(response.total).toFixed(2));
                            $('#sub-total-amt').html(parseFloat(parseFloat(response.total) + parseFloat(response.discount)).toFixed(2) + '<?php echo $selected_currency->symbol; ?>');

                            $('#total-price').val(parseFloat(parseFloat(response.total)));
                            var city = $('#shipping_city').val();
                            var country = $('#shipping_country').val();
                            calCulateShipping(city, country, $('#express-delivery-check').prop('checked'));
                        }
                    }
                },
                error: function () {
                sa_alert(
                    "Error!",
                    "Ajax call failed, please try again or contact site owner.",
                    "error",
                    !0
                );
                },
            });
        });

        function detectCardType(cardNumber) {
            if (/^4/.test(cardNumber)) {
                return 'visa';
            } else if (/^5[1-5]/.test(cardNumber)) {
                return 'mastercard';
            } else {
                return 'unknown';
            }
        }

        function formatCardNumber(cardNumber, cardType) {
            // Format the card number based on the card type
            if (cardType === 'visa' || cardType === 'mastercard') {
                // Insert spaces after every 4 characters
                return cardNumber.replace(/(\d{4})/g, '$1 ').trim();
            } else {
                // Default: no specific formatting for other card types
                return cardNumber;
            }
        }

        function updateCardTypeImage(cardType) {
            var imageUrl = '';

            $('#card_type_image').hide();
            if (cardType === 'visa') {
                imageUrl = 'visa.png'; // Replace with the actual path to your Visa image
                $('#card_type_image').show();
            } else if (cardType === 'mastercard') {
                $('#card_type_image').show();
                imageUrl = 'mastercard.png'; // Replace with the actual path to your Mastercard image
            }

            // Update the image source
            $('#card_type_image').attr('src', site.site_url+'assets/images/'+imageUrl);
        }

        $('#card_number').change(function(){
            $('#card_number_hidden').val($(this).val());
        });

        $('#card_expiry_year').on('input', function() {
            var inputValue = $(this).val();
            var isBackspace = event.inputType === 'deleteContentBackward';

            // Remove non-numeric characters
            var numericValue = inputValue.replace(/\D/g, '');

            // Format the date (MM / YY)
            if (numericValue.length >= 2 && !isBackspace) {
                var formattedValue = numericValue.substr(0, 2) + ' / ' + numericValue.substr(2, 2);
                $(this).val(formattedValue);
            } else if (!isBackspace) {
                $(this).val(numericValue);
            }

            // Handle backspace to remove trailing "/"
            if (isBackspace && numericValue.length === 2) {
                $(this).val(function(index, value) {
                    return value.substring(0, value.length - 3);
                });
            }
        });

        $('#card_expiry_year').on('blur', function() {
            var inputValue = $(this).val();
            if (!isValidExpiryDate(inputValue)) {
                $(this).val('');
                $('#card_expiry_year_hidden').val('');
            } else {
                $('#card_expiry_year_hidden').val($(this).val());
            }
        });

        function isValidExpiryDate(value) {
            // Implement your own validation logic if needed
            // For example, check if the value matches the expected format
            return /^\d{2}(\s*\/\s*\d{2})?$/.test(value);
        }
        
        /*$('#card_expiry_year').change(function(){
            $('#card_expiry_year_hidden').val($(this).val());
        });*/

        /*$('#card_expiry_month').change(function(){
            $('#card_expiry_month_hidden').val($(this).val());
        });*/

        $('#card_cvv').on('input',function(){
            var inputValue = $(this).val();
            var numericValue = inputValue.replace(/\D/g, '');

            $('#card_cvv').val(numericValue);
            $('#card_cvv_hidden').val(numericValue);
        });

        $('form').submit(function(e){
            e.preventDefault();

            var isValid = true;
            var payment_method_details = $('input[name="payment_method_details"]:checked').val();
            
            if(payment_method_details == 1){
                if($('#card_cvv_hidden').val() == ''){
                    isValid = false;
                }

                if($('#card_expiry_year').val() == ''){
                    isValid = false;
                }

                if($('#card_name').val() == ''){
                    isValid = false;
                }

                if($('#card_number').val() == ''){
                    isValid = false;
                }
            }

            if (isValid) {
                $('form').unbind('submit').submit();
            }else{
                $.notify('<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes('Please enter payment details'))); ?>', 'warn');
                
            }
        });

        $('#proceed-to-payment').click(function (e) {
            // On purchase track on snapchat
            snaptr('track', 'PURCHASE', {'currency': 'SAR', 'price': '<?php  echo $total; ?>', 'payment_info_available':1, 'item_category': 'Medical' });
        });

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

            const originalData = {
                token: site.csrf_token_value,
                card_name: card_name,
                card_number: card_number,
                card_cvv: card_cvv,
                card_expiry: card_expiry,
                shipping: shipping,
                address: address,
                payment_method: payment_method,
                express_delivery: express_delivery
            };

            const formData = { ...originalData };
            if (payment_method === 3) {
                delete formData.card_name;
                delete formData.card_number;
                delete formData.card_cvv;
                delete formData.card_expiry;
            }

            $.ajax({
                type: 'POST',
                url: site.site_url + 'pay/process_payment',
                data: formData,
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


        const is_free_shipping = '<?php echo $this->session->userdata('coupon_details')['free_shipping']; ?>' ;
       // alert(city);
        //$("#express-delivery-check").prop("disabled", true);
        $("#express-delivery-check").hide();
        $("#express-delivery-details").css("display", "none !important");
        //$("#express-delivery-details").hide();
        //$('.payment-k').prop('disabled', true);

        var shipping = parseInt('<?= round($calculateShipping); ?>');
        var deliveryDays = "Not Available";
        if (non_express_items > 0) {
            deliveryDays = 'Expected delivery 4 to 6 days';
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
                'المملكة العربية السعودية',
                'SA'
            ].includes(country)) {
                $('.payment-k').prop('disabled', false)
                shipping = 25;
                if(is_free_shipping){
                    shipping = 0
                }
                deliveryDays = "2 to 4 days"

                if (city.toLowerCase() === 'riyadh' || city.toLowerCase() === 'al riyadh' || city.toLowerCase() === 'al-riyadh' || [
                    'الرياض',
                    'ریاض'
                ].includes(city)) {
                    shipping = 20
                    
                    if(is_free_shipping){
                        
                        shipping = 0;
                    }
                    deliveryDays = "1 to 2 days"
                    //$("#express-delivery-check").prop("disabled", false);
                    $("#express-delivery-check").show();
                    
                    $("#express-delivery-details").show(); // After Eid uncomment this line

                    if (isExpressDelivery == true) {
                        shipping = 30;
                        if(is_free_shipping){
                            shipping = 0;
                        }
                        deliveryDays = "5 to 6 hours"
                    }
                }

                if (city.toLowerCase() === 'jeddah' || city.toLowerCase() === 'al jeddah' || city.toLowerCase() === 'al-jeddah' || [
                    'جده'
                ].includes(city)) {
                    
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
                    shipping = 62
                    deliveryDays = "Expected delivery 4 to 6 days"
                }
            } else {
                shipping = 62
                deliveryDays = "5 to 8 days"
            }

            if (non_express_items > 0) {
                if (saudiOrder == 1 && orderWithTax > 200) {
                    shipping = 0;
                } else {
                    shipping = 62;
                }
                /*else{
                    shipping = 32;
                }*/
                deliveryDays = 'Expected delivery 5 to 8 days';

                //$("#express-delivery-check").prop("disabled", true);
                $("#express-delivery-check").show();
                
                $("#express-delivery-details").show(); // After eid uncomment this line
            }

            if(totalPrice > 200 && !is_free_shipping && !isExpressDelivery){
                shipping = 0;
            }else if(totalPrice > 200 && !is_free_shipping && isExpressDelivery){
                shipping = 10;
            }

            if(!saudiOrder){
                shipping = 62;
            }
            
            /*if(shipping > 0){
                $('#shipping-price').text(parseFloat(shipping).toFixed(2))
                $('#shipping-fees-span').text(parseFloat(shipping).toFixed(2));
            }else{
                $('#shipping-price').text('Free');
                $('#shipping-fees-span').text('Free');
                //$('#express-shipping-fees-span').text('Free');
            }*/

            if(!saudiOrder){
                $('#shipping-price').text(parseFloat(shipping).toFixed(2));
                $('#shipping-fees-span').text(parseFloat(shipping).toFixed(2));
                //$('#express-shipping-fees-span').text('Free');
            }else if(is_free_shipping){
                // Coupon applied, no express delivery
                $('#shipping-price').text('Free');
                $('#shipping-fees-span').text('Free');
                $('#express-shipping-fees-span').text('Free');
                
            }else if(!is_free_shipping && shipping == 0 && totalPrice > 200){
                $('#shipping-price').text('Free');
                $('#shipping-fees-span').text('Free');
                $('#express-shipping-fees-span').text('10.00');
            }else if(!is_free_shipping && shipping > 0 && totalPrice > 200){
                // No coupon applied, order value less than 200

                $('#shipping-price').text('10.00');
                $('#shipping-fees-span').text('Free');
                $('#express-shipping-fees-span').text('10.00');
            }else if(!is_free_shipping && shipping > 0 && totalPrice < 200){
                // No coupon applied, order value less than 200

                $('#shipping-price').text(parseFloat(shipping).toFixed(2));
                $('#shipping-fees-span').text(parseFloat(shipping).toFixed(2));
                $('#express-shipping-fees-span').text('30.00');
            }

            var grandTotalPrice = parseFloat(totalPrice) + parseFloat(shipping);
            
            $('#grand-total-price').text(parseFloat(grandTotalPrice).toFixed(2))
            $('#shipping-input').val(parseFloat(shipping).toFixed(2));
            $('#delivery-days').text(deliveryDays);
        }
    }

 
</script>