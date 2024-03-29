<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    #map {
        height: 100%;
        width: 100%;
    }

    #map-container {
        height: 350px;
    }
</style>
<?php

$cart_contents = $this->cart->contents();
$not_express_items = 0;
foreach ($cart_contents as $cartItem) {
    $cart_item_code = $cartItem['code'];
    if (strpos($cart_item_code, 'AM-') !== false || strpos($cart_item_code, 'IH-') !== false) {
        $not_express_items++;
    }
}

?>

<section class="page-contents">

    <div class="container container-max-width">
        <div class="row">
            <div class="col-md-12">
                <div class="row checkoutbox-k">

                    <div class="col-sm-8">
                        <div class="panel panel-default margin-top-lg checkLeftCol-k">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-shopping-cart margin-right-sm"></i> <?= lang('checkout'); ?>
                                <a href="<?= site_url('cart'); ?>" class="pull-right back-k">
                                    <i class="fa fa-share"></i>
                                    <?= lang('back_to_cart'); ?>
                                </a>
                            </div>
                            <div class="panel-body">
                                <?php if ($this->session->flashdata('validation_errors')) {
                                    ?>
                                    <div class="alert alert-danger">
                                        <?= $this->session->flashdata('validation_errors') ?>
                                        <?php $this->session->set_flashdata('validation_errors', null) ?>
                                    </div>
                                    <?php
                                } ?>
                                <div>
                                    <?php
                                    //echo $this->loggedIn;
                                    if (!$this->loggedIn) {
                                        ?>
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation"><a href="#guest" aria-controls="guest" role="tab"
                                                                       data-toggle="tab"><?= lang('guest_checkout'); ?></a>
                                            </li>
                                            <li role="presentation" class="active"><a href="#user" aria-controls="user"
                                                                                      role="tab"
                                                                                      data-toggle="tab"><?= lang('returning_user'); ?></a>
                                            </li>

                                        </ul>
                                        <?php
                                    }
                                    ?>

                                    <div class="tab-content padding-lg">
                                        <div role="tabpanel" class="tab-pane fade in active " id="user">
                                            <?php
                                            $calculateShipping = 0;

                                            if ($this->loggedIn) {

                                                if ($this->Settings->indian_gst) {
                                                    $istates = $this->gst->getIndianStates();
                                                }
                                            if (!empty($addresses)) {
                                                echo shop_form_open('order', 'class="validate addressform-k"');
                                                echo '<div class="row address-row-k">';
                                                echo '<div class="col-sm-12 text-bold py-2">' . lang('select_address') . '</div>';
                                                $r = 1;
                                            foreach ($addresses as $address) {
                                                ?>
                                                <div class="col-sm-6">
                                                    <div class="checkbox bg addressbox-k">
                                                        <label>
                                                            <input
                                                                    class="payment-address"
                                                                    type="radio"
                                                                    name="address"
                                                                    value="<?= $address->id; ?>"
                                                                    data-payload='<?= json_encode($address) ?>'
                                                            >
                                                            <span>
                                                                        <?= $address->line1; ?><br>
                                                                        <?= $address->line2; ?><br>
                                                                        <?= $address->city; ?>
                                                                <?= $this->Settings->indian_gst && isset($istates[$address->state]) ? $istates[$address->state] . ' - ' . $address->state : $address->state; ?><br>
                                                                        <?= $address->postal_code; ?> <?= $address->country; ?><br>
                                                                        <?= lang('phone') . ': ' . $address->phone; ?>
                                                                    </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php
                                            $r++;
                                            }
                                            echo '</div>';
                                            }
                                            if (count($addresses) < 6 && !$this->Staff) {
                                                echo '<div class="row margin-bottom-lg">';
                                                echo '<div class="col-sm-12"><a href="#" id="add-address" class="btn btn-primary btn-sm">' . lang('add_new_address') . '</a></div>';
                                                echo '<div class="col-sm-12"><a href="?action=changeaddress" id="change-address" class="btn btn-primary btn-sm">' . lang('change_address') . '</a></div>';
                                                echo '</div>';
                                            }
                                            if ($this->Settings->indian_gst && (isset($istates))) {
                                            ?>
                                                <script>
                                                    var istates = <?= json_encode($istates); ?>
                                                </script>
                                            <?php
                                            } else {
                                                echo '<script>var istates = false; </script>';
                                            } ?>
                                                <!--<hr>-->
                                                <!--<h5><strong><?= lang('payment_method'); ?></strong></h5>-->
                                            <input type="hidden" name="payment_method" value="directpay" id="directpay"
                                                   required="required">
                                                <div class="checkbox bg">
                                                    <?php if ($paypal->active) {
                                                        ?>
                                                        <!--<label style="display: inline-block; width: auto;">-->
                                                        <!--    <input type="radio" name="payment_method" value="paypal" id="paypal" required="required">-->
                                                        <!--    <span>-->
                                                        <!--        <i class="fa fa-paypal margin-right-md"></i> <?= lang('paypal') ?>-->
                                                        <!--    </span>-->
                                                        <!--</label>-->
                                                        <?php
                                                    } ?>
                                                    <?php if ($skrill->active) {
                                                        ?>
                                                        <!--<label style="display: inline-block; width: auto;">-->
                                                        <!--    <input type="radio" name="payment_method" value="skrill" id="skrill" required="required">-->
                                                        <!--    <span>-->
                                                        <!--        <i class="fa fa-credit-card-alt margin-right-md"></i> <?= lang('skrill') ?>-->
                                                        <!--    </span>-->
                                                        <!--</label>-->
                                                        <?php
                                                    } ?>
                                                    <?php if ($shop_settings->stripe) {
                                                        ?>
                                                        <!--<label style="display: inline-block; width: auto;">-->
                                                        <!--    <input type="radio" name="payment_method" value="stripe" id="stripe" required="required">-->
                                                        <!--    <span>-->
                                                        <!--        <i class="fa fa-cc-stripe margin-right-md"></i> <?= lang('stripe') ?>-->
                                                        <!--    </span>-->
                                                        <!--</label>-->
                                                        <?php
                                                    } ?>
                                                    <!--<label style="display: inline-block; width: auto;">-->

                                                    <!--<span>-->
                                                    <!--    <i class="fa fa-cc-stripe margin-right-md"></i> Direct Pay-->
                                                    <!--</span>-->
                                                    <!--</label>-->
                                                    <!--<label style="display: inline-block; width: auto;">-->
                                                    <!--    <input type="radio" name="payment_method" value="bank" id="bank" required="required">-->
                                                    <!--    <span>-->
                                                    <!--        <i class="fa fa-bank margin-right-md"></i> <?= lang('bank_in') ?>-->
                                                    <!--    </span>-->
                                                    <!--</label>-->

                                                    <!--<label style="display: inline-block; width: auto;">-->
                                                    <!--    <input type="radio" name="payment_method" value="cod" id="cod" required="required">-->
                                                    <!--    <span>-->
                                                    <!--        <i class="fa fa-money margin-right-md"></i> <?= lang('cod') ?>-->
                                                    <!--    </span>-->
                                                    <!--</label>-->
                                                </div>
                                                <!--<hr>-->
                                                <div class="form-group">
                                                    <?= lang('comment_any', 'comment'); ?>
                                                    <?= form_textarea('comment', set_value('comment'), 'class="form-control" id="comment" style="height:100px;"'); ?>
                                                </div>
                                            <?php
                                            if (!empty($addresses) && !$this->Staff) {
                                            ?>
                                            <input type="hidden" name="express_delivery" id="express_delivery"
                                                   value="Standard"/>
                                            <input type="hidden" id="shipping-input" name="shipping"
                                                   value="<?= $calculateShipping ?>"/>
                                            <?php
                                            echo form_submit('add_order', lang('Proceed to Payment'), 'class="btn btn-theme payment-k" ');
                                            } elseif ($this->Staff) {
                                                echo '<div class="alert alert-warning margin-bottom-no">' . lang('staff_not_allowed') . '</div>';
                                            } else {
                                                echo '<div class="alert alert-warning margin-bottom-no">' . lang('please_add_address_first') . '</div>';
                                            }
                                            echo form_close();
                                            } else {
                                            ?>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="well margin-bottom-no">
                                                            <?php include FCPATH . 'themes' . DIRECTORY_SEPARATOR . $Settings->theme . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'login_form.php'; ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="registertab-k">
                                                            <h4 class="title">
                                                                <span><?= lang('register_new_account'); ?></span></h4>
                                                            <p>
                                                                <?= lang('register_account_info'); ?>
                                                            </p>
                                                            <a href="<?= site_url('login#register'); ?>"
                                                               class="btn btn-theme"><?= lang('register'); ?></a>
                                                            <a href="#"
                                                               class="btn btn-default pull-right guest-checkout"><?= lang('guest_checkout'); ?></a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div role="tabpanel" class="tab-pane fade " id="guest">
                                            <?= shop_form_open('order', 'class="validate" id="guest-checkout"'); ?>
                                            <input type="hidden" value="1" name="guest_checkout">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <?= lang('First Name', 'name'); ?> *
                                                                <?= form_input('name', set_value('name'), 'class="form-control" id="name" required="required"'); ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <?= lang('Last_name', 'name'); ?>
                                                                <?= form_input('last_name', set_value('last_name'), 'class="form-control" id="last_name"'); ?>
                                                            </div>
                                                        </div>
                                                        <!--<div class="col-md-6">-->
                                                        <!--    <div class="form-group">-->
                                                        <!--        <?= lang('company', 'company'); ?>-->
                                                        <!--        <?= form_input('hidden', set_value('company'), 'class="form-control" id="company"'); ?>-->
                                                        <!--    </div>-->
                                                        <!--</div>-->
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <?= lang('email', 'email'); ?> *
                                                        <?= form_input('email', set_value('email'), 'class="form-control" id="email" required="required"'); ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group" id="account-phone">
                                                        <?= lang('phone', 'phone'); ?> * <br>
                                                        <input type="tel" id="phone" name="phone" class="form-control"
                                                               required="required"/>
                                                        <!--<?= form_input('phone', set_value('phone'), 'class="form-control" id="phone" required="required"'); ?>-->
                                                    </div>
                                                </div>
                                                <div id="billing-address-container" style="display: none;">
                                                <div class="col-md-12">
                                                    <h5><strong><?= lang('billing_address'); ?></strong></h5>
                                                    <input type="hidden" value="new" name="address">
                                                    <hr>


                                                    <div class="form-group">
                                                        <?= lang('Search_For_The_Billing_Address', ''); ?>
                                                        <input id="autocomplete_search_billing" type="text"
                                                               class="form-control"
                                                               placeholder=""/>
                                                    </div>
                                                    <div class="form-group">
                                                        <?= lang('line1', 'billing_line1'); ?> *
                                                        <?= form_input('billing_line1', set_value('billing_line1'), 'class="form-control" id="billing_line1"'); ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <?= lang('line2', 'billing_line2'); ?>
                                                        <?= form_input('billing_line2', set_value('billing_line2'), 'class="form-control" id="billing_line2"'); ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <?= lang('city', 'billing_city'); ?> *
                                                                <?= form_input('billing_city', set_value('billing_city'), 'class="form-control" id="billing_city" '); ?>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <?= lang('postal_code', 'billing_postal_code'); ?>
                                                                <?= form_input('billing_postal_code', set_value('billing_postal_code'), 'class="form-control" id="billing_postal_code"'); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <?= lang('state', 'billing_state'); ?>
                                                        <?php
                                                        if ($Settings->indian_gst) {
                                                            $states = $this->gst->getIndianStates();
                                                            echo form_dropdown('billing_state', $states, '', 'class="form-control selectpicker mobile-device" id="billing_state" title="Select" ');
                                                        } else {
                                                            echo form_input('billing_state', '', 'class="form-control" id="billing_state"');
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">

                                                    <div class="form-group">
                                                        <?= lang('country', 'country'); ?> *

                                                        <?= form_input('billing_country', set_value('billing_country'), 'class="form-control" id="billing_country" '); ?>
                                                        <!--<select class="form-control" id="billing_country"
                                                                name="billing_country">


                                                            <?php
                                                        /*                                                            foreach ($country as $u) {
                                                                                                                        echo '<option value="' . $u->code . '">' . $u->name . '</option>';
                                                                                                                    }
                                                                                                                    */ ?>


                                                        </select>-->

                                                        <!--form_input('billing_country', set_value('billing_country'), 'class="form-control" id="billing_country" required="required"')-->
                                                    </div>
                                                </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <!--<div class="checkbox bg pull-right"
                                                         style="margin-top: 0; margin-bottom: 0;">
                                                        <label>
                                                            <input type="checkbox" name="same" value="1"
                                                                   id="same_as_billing">
                                                            <span>
                                                                <?php //echo lang('same_as_billing'); ?>
                                                            </span>
                                                        </label>
                                                    </div>-->
                                                    <h5><strong><?= lang('shipping_address'); ?></strong></h5>
                                                    <?= lang('Look_For_The_Shipping_Address', ''); ?>
                                                    (drag the marker to set your address)
                                                    <button type="button" id="load_current_location">Current Location</button>
                                                    <div id="map-container">
                                                        <div id="map"></div>
                                                    </div>
                                                    <div class="form-group">
                                                        <input
                                                                type="text"
                                                                readonly="readonly"
                                                                id="google-map-selected-address"
                                                                value="Note:- Allow the browser to access your location."
                                                                class="form-control"
                                                        />
                                                    </div>
                                                    <input type="hidden" value="new" name="address">
                                                    <hr>
                                                    <strong>OR</strong>
                                                    <br/>
                                                    <input type="checkbox" id="manual-shipping-check"/> Check the box to
                                                    type the address manually
                                                    <!--<div class="form-group">
                                                        <?/*= lang('Search_For_The_Shipping_Address', ''); */?>
                                                        <input id="autocomplete_search_shipping" type="text"
                                                               class="form-control"
                                                               placeholder=""/>
                                                    </div>-->

                                                </div>
                                                <div id="manual-shipping-address" style="display: none;">
                                               <div class="col-md-12">
                                                   <div class="form-group">
                                                       <?= lang('Address', 'shipping_line1'); ?> *
                                                       <?= form_input('shipping_line1', set_value('shipping_line1'), 'class="form-control" id="shipping_line1" '); ?>
                                                   </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <?= lang('country', 'shipping_country'); ?> *
                                                                <input
                                                                        type="hidden"
                                                                        class="form-control"
                                                                        name="shipping_country"
                                                                        id="shipping_country"
                                                                        required="required"
                                                                />
                                                                <select id="shipping_country_dropdown" class="form-control" >
                                                                    <option value="0">--SELECT--</option>
                                                                    <?php
                                                                    if(!empty($countries) && sizeof($countries) > 0){
                                                                        foreach($countries as $country){
                                                                            ?>
                                                                            <option value="<?= $country->id ?>"><?= $country->name ?></option>
                                                                            <?php
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                                <!--form_input('shipping_country', set_value('shipping_country'), 'class="form-control" id="shipping_country" required="required"'); ?>-->
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <?= lang('city', 'shipping_city'); ?> *
                                                                <input
                                                                        type="hidden"
                                                                        class="form-control"
                                                                        name="shipping_city"
                                                                        id="shipping_city"
                                                                        required="required"
                                                                />
                                                                <select id="shipping_city_dropdown" class="form-control" >
                                                                    <option value="0">--SELECT--</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <!--<div class="col-md-6">
                                                            <div class="form-group">
                                                                <?/*= lang('postal_code', 'shipping_postal_code'); */?>
                                                                <?/*= form_input('shipping_postal_code', set_value('shipping_postal_code'), 'class="form-control" id="shipping_postal_code"'); */?>
                                                            </div>
                                                        </div>-->
                                                    </div>
                                                </div>

                                                <input type="hidden" id="shipping_latitude" name="shipping_latitude"
                                                       value=""/>
                                                <input type="hidden" id="shipping_longitude" name="shipping_longitude"
                                                       value=""/>


                                                <div class="col-md-12 d-none" >
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <?= lang('state', 'shipping_state'); ?>
                                                                <?php
                                                                if ($Settings->indian_gst) {
                                                                    $states = $this->gst->getIndianStates();
                                                                    echo form_dropdown('shipping_state', $states, '', 'class="form-control selectpicker mobile-device" id="shipping_state" title="Select" required="required"');
                                                                } else {
                                                                    echo form_input('shipping_state', '', 'class="form-control" id="shipping_state"');
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 d-none">
                                                    <div class="form-group" id="shipping-phone">
                                                        <?= lang('phone', 'shipping_phone'); ?> *
                                                        <input type="tel" name="shipping_phone" value="" class="form-control" id="shipping_phone" required="required" />
                                                        <?php // form_input('shipping_phone', set_value('shipping_phone'), 'class="form-control" id="shipping_phone" required="required"'); ?>
                                                    </div>
                                                </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <!--<h5><strong><?= lang('payment_method'); ?></strong></h5>-->
                                                    <!--<hr>-->
                                                    <!--<div class="checkbox bg">-->
                                                    <?php if ($paypal->active) {
                                                        ?>
                                                        <label style="display: inline-block; width: auto;">
                                                            <input type="radio" name="payment_method" value="paypal"
                                                                   id="paypal" required="required">
                                                            <span>
                                                                <i class="fa fa-paypal margin-right-md"></i> <?= lang('paypal') ?>
                                                            </span>
                                                        </label>
                                                        <?php
                                                    } ?>
                                                    <?php if ($skrill->active) {
                                                        ?>
                                                        <label style="display: inline-block; width: auto;">
                                                            <input type="radio" name="payment_method" value="skrill"
                                                                   id="skrill" required="required">
                                                            <span>
                                                                <i class="fa fa-credit-card-alt margin-right-md"></i> <?= lang('skrill') ?>
                                                            </span>
                                                        </label>
                                                        <?php
                                                    } ?>
                                                    <?php if ($shop_settings->stripe) {
                                                        ?>
                                                        <label style="display: inline-block; width: auto;">
                                                            <input type="radio" name="payment_method" value="stripe"
                                                                   id="stripe" required="required">
                                                            <span>
                                                                <i class="fa fa-cc-stripe margin-right-md"></i> <?= lang('stripe') ?>
                                                            </span>
                                                        </label>
                                                        <?php
                                                    } ?>

                                                    <label style="display: inline-block; width: auto;">
                                                        <input type="hidden" name="payment_method" value="directpay"
                                                               id="directpay" required="required">

                                                        <!--<input type="radio" name="payment_method" value="directpay" id="directpay" required="required">-->
                                                        <!--<span>-->
                                                        <!--    <i class="fa fa-bank margin-right-md"></i> Direct Pay-->
                                                        <!--</span>-->
                                                    </label>


                                                    <!--<label style="display: inline-block; width: auto;">-->
                                                    <!--    <input type="radio" name="payment_method" value="bank" id="bank" required="required">-->
                                                    <!--    <span>-->
                                                    <!--        <i class="fa fa-bank margin-right-md"></i> <?= lang('bank_in') ?>-->
                                                    <!--    </span>-->
                                                    <!--</label>-->

                                                    <!--<label style="display: inline-block; width: auto;">-->
                                                    <!--    <input type="radio" name="payment_method" value="cod" id="cod" required="required">-->
                                                    <!--    <span>-->
                                                    <!--        <i class="fa fa-money margin-right-md"></i> <?= lang('cod') ?>-->
                                                    <!--    </span>-->
                                                    <!--</label>-->
                                                    <!--</div>-->
                                                </div>

                                            </div>
                                            <input type="hidden" name="express_delivery" id="express_delivery"
                                                   value="Standard"/>
                                            <input type="hidden" id="shipping-input" name="shipping"
                                                   value="<?= $calculateShipping ?>"/>
                                            <?= form_submit('guest_order', lang('Proceed to Payment'), 'class="btn btn-lg btn-primary payment-k" '); ?>
                                            <?= form_close(); ?>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-sm-4">
                        <div id="sticky-con" class="margin-top-lg">
                            <div class="panel panel-default">
                                <div class="panel-heading text-bold">
                                    <i class="fa fa-shopping-cart margin-right-sm"></i> <?= lang('totals'); ?>
                                </div>
                                <div class="panel-body total-k">
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
                                    <table class="table table-striped table-borderless cart-totals margin-bottom-no">
                                        <tr>
                                            <td><?= lang('Total_Before_Tax'); ?></td>
                                            <td class="text-right"><?= $this->sma->convertMoney($this->cart->total() - $this->cart->total_item_tax()); ?></td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('product_tax'); ?></td>
                                            <td class="text-right"><?= $this->sma->convertMoney($this->cart->total_item_tax()); ?>
                                                <input type="hidden" id="total-order-tax"
                                                       value="<?php echo $this->cart->total_item_tax(); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('total'); ?></td>
                                            <td class="text-right">
                                                <?= $this->sma->formatMoney($total, $selected_currency->symbol); ?>
                                                <input type="hidden" id="total-price" value="<?= $total ?>"/>
                                            </td>
                                        </tr>
                                        <?php
                                        /*if ($Settings->tax2 !== false) {
                                            echo '<tr><td>' . lang('order_tax') . '</td>
                                                    <td class="text-right">' . $this->sma->formatMoney($order_tax, $selected_currency->symbol) . '
                                                    <input type="hidden" id="total-order-tax" value="' . $order_tax . '"/>
                                                    </td>
                                                    </tr>';
                                        } */

                                        ?>
                                        <tr>
                                            <td><?= lang('shipping'); ?> *</td>
                                            <td class="text-right">
                                                <span id="shipping-price">
                                                <?= $this->sma->formatNumber($shipping); ?></span><?= $selected_currency->symbol ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?= lang('Delivery'); ?> *</td>
                                            <td class="text-right">
                                                <span id="delivery-days">
                                                <?php
                                                if ($not_express_items > 0) {
                                                    echo '4 to 6 Days';
                                                } else {
                                                    echo 'Not Available';
                                                }
                                                ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr class="active text-bold">
                                            <td><?= lang('grand_total'); ?></td>
                                            <td class="text-right"><span
                                                        id="grand-total-price"><?= $this->sma->formatDecimal(($this->sma->formatDecimal($total) + $this->sma->formatDecimal($order_tax) + $this->sma->formatDecimal($shipping))); ?></span><?= $selected_currency->symbol ?>
                                            </td>
                                        </tr>
                                        <tr class="active text-bold">
                                            <td><?= lang('Express_delivery'); ?></td>
                                            <td class="text-right">
                                                <input type="checkbox" id="express-delivery-check" disabled/>
                                            </td>
                                        </tr>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--<code class="text-muted">* <?= lang('shipping_rate_info'); ?></code>-->
            </div>
        </div>
    </div>
</section>
<script>
    let map;

<?php if (!$this->loggedIn) { ?>

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: 23.8859, lng: 45.0792}, // Example coordinates (San Francisco)
            zoom: 18, // Adjust the zoom level
        });


        // Try to get the user's current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    // Center the map at the user's location

                    document.getElementById('shipping_latitude').value = position.coords.latitude;
                    document.getElementById('shipping_longitude').value = position.coords.longitude;
                    map.setCenter(userLocation);

                    // Add a marker at the user's location
                    const marker = new google.maps.Marker({
                        position: userLocation,
                        map: map,
                        title: 'Your Location',
                        draggable: true
                    });
                    document.getElementById('manual-shipping-check').checked = false;
                    document.getElementById('manual-shipping-address').style.display = 'none';
                    geocodeLatLng(userLocation);

                    marker.addListener('dragend', function () {

                        document.getElementById('manual-shipping-check').checked = false;
                        document.getElementById('manual-shipping-address').style.display = 'none';

                        const newPosition = marker.getPosition();
                        document.getElementById('shipping_latitude').value = newPosition.lat();
                        document.getElementById('shipping_longitude').value = newPosition.lng();
                        geocodeLatLng(newPosition);
                    });

                    $('#load_current_location').on('click', function(e) {
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                function (position) {
                                    const userLocation = {
                                        lat: position.coords.latitude,
                                        lng: position.coords.longitude
                                    };

                                    document.getElementById('shipping_latitude').value = position.coords.latitude;
                                    document.getElementById('shipping_longitude').value = position.coords.longitude;
                                    marker.setPosition(userLocation);
                                    map.setCenter(userLocation);
                                    document.getElementById('manual-shipping-check').checked = false;
                                    document.getElementById('manual-shipping-address').style.display = 'none';
                                    geocodeLatLng(userLocation);
                                },
                                function (error) {
                                    console.error('Error getting user location:', error);
                                },
                                {
                                    enableHighAccuracy: true
                                }
                            );
                        }
                    })
                },
                function (error) {
                    console.error('Error getting user location:', error);
                },
                {
                    enableHighAccuracy: true
                }
            );
        } else {
            console.error('Geolocation is not supported by this browser.');
        }
    }

    function geocodeLatLng(latLng) {
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({'location': latLng}, function (results, status) {
            if (status === 'OK') {
                if (results[0]) {
                    const addressComponents = results[0].address_components;
                    const formattedAddress = results[0].formatted_address;
                    document.getElementById('google-map-selected-address').value = formattedAddress;
                    document.getElementById('shipping_line1').value = formattedAddress;
                    let city, country, state, street;

                    for (const component of addressComponents) {
                        const types = component.types;
                        if (types.includes('locality')) {
                            city = component.long_name;
                            document.getElementById('shipping_city').value = city;
                        } else if (types.includes('country')) {
                            country = component.long_name;
                            document.getElementById('shipping_country').value = country;

                            // $("#shipping_country option:contains('" + country + "')").prop("selected", true);
                        } else if (types.includes('route')) {
                            street = component.long_name;
                            // document.getElementById('shipping_line1').value = street;
                        } else if (types.includes('administrative_area_level_1')) {
                            state = component.long_name;
                            document.getElementById('shipping_state').value = state;
                        }
                    }

                    calCulateShipping(city, country)
                } else {
                    console.log('No results found');
                }
            } else {
                console.log('Geocoder failed due to: ' + status);
            }
        });
    }
    <?php } ?>
</script>
<script>
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
        $("#express-delivery-check").prop("disabled", true);
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
                shipping = 19
                deliveryDays = "2 to 4 days"

                if (city.toLowerCase() === 'riyadh' || city.toLowerCase() === 'al riyadh' || city.toLowerCase() === 'al-riyadh' || [
                    'الرياض',
                    'ریاض'
                ].includes(city)) {
                    shipping = 16
                    deliveryDays = "1 to 2 days"
                    $("#express-delivery-check").prop("disabled", false);

                    if (isExpressDelivery == true) {
                        shipping = 21
                        deliveryDays = "5 to 6 hours"
                    }
                }

                if (city.toLowerCase() === 'jeddah' || city.toLowerCase() === 'al jeddah' || city.toLowerCase() === 'al-jeddah' || [
                    'جده'
                ].includes(city)) {
                    shipping = 16
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

                $("#express-delivery-check").prop("disabled", true);
            }

            $('#shipping-price').text(parseFloat(shipping).toFixed(2))
            $('#grand-total-price').text(parseFloat(grandTotalPrice).toFixed(2))
            $('#shipping-input').val(parseFloat(shipping).toFixed(2));
            $('#delivery-days').text(deliveryDays);
        }
    }

    $(document).ready(function () {
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
            var addressObject = $('.payment-address:checked').data('payload');
            if (addressObject != undefined) {
                var country = addressObject.country
                var city = addressObject.city
            } else {
                var city = $('#shipping_city').val();
                var country = $('#shipping_country').val();
            }

            if ($(this).prop('checked') == true) {
                document.getElementById('express_delivery').value = 'Express';
            } else {
                document.getElementById('express_delivery').value = 'Standard';
            }

            calCulateShipping(city, country, $(this).prop('checked'));
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
</script>
<script>
    $(document).ready(function () {
        google.maps.event.addDomListener(window, 'load', initialize);

        function initialize() {
            return true;
            var input = document.getElementById('autocomplete_search_billing');
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.addListener('place_changed', function () {

                document.getElementById('billing_city').value = "";
                document.getElementById('billing_country').value = "";
                document.getElementById('billing_line1').value = "";
                document.getElementById('billing_postal_code').value = "";
                document.getElementById('billing_state').value = "";

                var place = autocomplete.getPlace();
                // Define variables to store city and country names
                var city, country, street, postalCode, stateName;

                // Loop through address components to find city and country
                place.address_components.forEach(function (component) {
                    component.types.forEach(function (type) {
                        if (type === 'locality') {
                            city = component.long_name;
                            document.getElementById('billing_city').value = city;
                        }
                        if (type === 'country') {
                            country = component.long_name;
                            document.getElementById('billing_country').value = country;
                        }
                        if (type === 'route') {
                            street = component.long_name
                            document.getElementById('billing_line1').value = street;
                        }
                        if (type === 'postal_code') {
                            postalCode = component.long_name
                            document.getElementById('billing_postal_code').value = postalCode;
                        }
                        if (type === 'administrative_area_level_1') {
                            stateName = component.long_name
                            document.getElementById('billing_state').value = stateName;
                        }
                    });
                });

                // calCulateShipping(city, country)
            });


            var inputShipping = document.getElementById('autocomplete_search_shipping');
            var autocompleteShipping = new google.maps.places.Autocomplete(inputShipping);
            autocompleteShipping.addListener('place_changed', function () {

                document.getElementById('shipping_city').value = "";
                document.getElementById('shipping_country').value = "";
                document.getElementById('shipping_line1').value = "";
                document.getElementById('shipping_postal_code').value = "";
                document.getElementById('shipping_state').value = "";

                var placeShipping = autocompleteShipping.getPlace();
                // Define variables to store city and country names
                var cityShipping, countryShipping, streetShipping, postalCodeShipping, stateNameShipping;

                // Loop through address components to find city and country
                placeShipping.address_components.forEach(function (component) {
                    component.types.forEach(function (type) {
                        if (type === 'locality') {
                            cityShipping = component.long_name;
                            document.getElementById('shipping_city').value = cityShipping;
                        }
                        if (type === 'country') {
                            countryShipping = component.long_name;
                            document.getElementById('shipping_country').value = countryShipping;
                        }
                        if (type === 'route') {
                            streetShipping = component.long_name
                            document.getElementById('shipping_line1').value = streetShipping;
                        }
                        if (type === 'postal_code') {
                            postalCodeShipping = component.long_name
                            document.getElementById('shipping_postal_code').value = postalCodeShipping;
                        }
                        if (type === 'administrative_area_level_1') {
                            stateNameShipping = component.long_name
                            document.getElementById('shipping_state').value = stateNameShipping;
                        }
                    });
                });

                var lat = placeShipping.geometry['location'].lat();
                var lng = placeShipping.geometry['location'].lng();

                document.getElementById('shipping_latitude').value = lat;
                document.getElementById('shipping_longitude').value = lng;

                calCulateShipping(cityShipping, countryShipping)
            });
        }
    });

    $(document).ready(function() {
        $("#shipping_country_dropdown").on("change", function() {
            $('#shipping_city').val('');

            let countryId = $(this).val();
            let selectedOption = $(this).find(":selected").text();
            if(selectedOption != '--SELECT--') {
                $("#shipping_country").val(selectedOption);
            } else {
                $("#shipping_country").val('');
            }
            $("#shipping_country").trigger('blur');

            $.ajax({
                url: "get_cities_by_country_id/" + countryId, // Replace with the actual endpoint to fetch cities
                method: "GET",
                success: function(jsonResponse) {
                    var response = JSON.parse(jsonResponse);
                    $("#shipping_city_dropdown").empty();
                    if(response.length > 0) {
                        $("#shipping_city_dropdown").append('<option value="0">--SELECT--</option>');
                        response.forEach(function(city) {
                            $("#shipping_city_dropdown").append('<option value="' + city.id + '">' + city.name + '</option>');
                        });
                    }
                },
                error: function() {
                    console.error("Failed to fetch cities.");
                }
            });
        });

        $("#shipping_city_dropdown").on("change", function() {
            let selectedOption = $(this).find(":selected").text();
            if(selectedOption != '--SELECT--') {
                $("#shipping_city").val(selectedOption);
            } else {
                $("#shipping_city").val('');
            }
            $("#shipping_city").trigger('blur');
        });
    });

</script>
