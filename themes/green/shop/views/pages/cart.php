<?php defined('BASEPATH') or exit('No direct script access allowed'); 

$cart_contents = $this->cart->contents();

?>

<section class="page-contents" style="background:white !important;">
    <div class="container container-max-width cartpage">
        <div class="row">
            <div class="col-md-12">

                <!-- cart new dsign -->
                <div class="row justify-content-between  ">
              

                    <div class="col-lg-7">
                    <h3 class="mb-3 fw-semibold">Shopping Cart</h3>
                       
                        <div class=" bg-light  shoppingCardmob p-4" >

                     

                        <div id="cart-table-new" >
                        </div>
                            
                        </div>
                    </div>


                    <div class="col-lg-5">
                        <div class="text-end">
                            <!--<a class="navbar-brand" href="#">
                            <img src="<?= base_url('assets/uploads/logos/'.$shop_settings->logo); ?>" alt="logo" >
                            </a>-->
                        </div>
                        <h3 class="mb-3 fw-semibold">Order Summary</h3>
                        <div class="border order-summary-cont p-4">
                           
                        
                            <h4>Coupon Code</h4>
                            <div class="d-flex">
                                <?php
                                    $attrib = ['class' => 'validate d-flex w-100', 'role' => 'form', 'id' => 'coupon_form'];
                                    echo form_open('apply_coupon', $attrib); 
                                ?>
                                    <?php 
                                        //if($this->cart->get_total_discount() > 0){
                                        if(isset($this->session->userdata('coupon_details')['code'])){
                                            ?>
                                                <input type="text" name="coupon_code" disabled="disabled" class="form-control  rounded-0" placeholder="<?php echo $this->session->userdata('coupon_details')['code']; ?>">
                                                <button disabled="disabled" class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-1 " style="background: green;color: #fff;border: none;font-size:14px !important;width: 175px !important;">
                                                    Code Applied
                                                </button>
                                            <?php
                                        }else{
                                            ?>
                                                <input type="text" name="coupon_code"  class="form-control  rounded-0" placeholder="">
                                                <button type="submit" class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-1 apply-code" style="font-size:14px !important;width: 175px !important;">
                                                    Apply Code
                                                </button>
                                            <?php
                                           
                                        }
                                    ?>   
                                <?php 
                                    echo form_close();
                                ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-3">
                                <h4 class="m-0 fw-semibold">Subtotal (<span id="total-unique_items">0</span> items)</h4>
                                <h4 class="m-0 fw-semibold"><span id="total-price">0 SAR</span></h4>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-3">
                                <h4 class="m-0 ">Discount</h4>
                                <h4 class="m-0 "><span id="total-discount">SAR 0</span></h4>
                            </div>
                            <h4 class="m-0 opacity-50 border-bottom pb-3">Shipping fee will be calculated at checkout</h4>
                            <div class="d-flex justify-content-between align-items-center my-3">
                                <h4 class="m-0 fw-bold fs-5">Total <span class="fs-6 opacity-50">(Inclusive of VAT)</span></h4>
                                <h4 class="m-0 fw-bold fs-5"><span id="total-after_discount">SAR 0</span></h4>
                            </div>
                            <?php 
                                if ($loggedIn) {
                                    ?>
                                        <a href="<?= site_url('cart/checkout'); ?>" style="border:0px;background: none;" class="btn btn-danger btn-sm mt-3">
                                            <button class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-4 py-1 " style="font-size:18px !important;"> Checkout</button>
                                        </a>
                                    <?php
                                }else{
                                    ?>
                                        <a style="border:0px;background: none;" class="btn btn-danger btn-sm checkout-link mt-3">
                                            <button class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-4 py-1 text-center" style="font-size:18px !important;"> Checkout</button>
                                        </a>
                                    <?php
                                }
                            ?>
                            
                            
                        </div>
                    </div>

                </div>
                <!-- cart new dsign end -->




                <!--<div class="row">

                    <div class="col-sm-8">
                        <div class="panel panel-default margin-top-lg">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-shopping-cart margin-right-sm"></i> <?= lang('shopping_cart'); ?>
                                [ <?= lang('items'); ?>: <span id="total-items"></span> ]
                                <a href="<?= shop_url('products'); ?>" class="pull-right hidden-xs continushopingtxt">
                                    <i class="fa fa-share"></i>
                                    <?= lang('continue_shopping'); ?>
                                </a>
                            </div>
                            <div class="panel-body" style="padding:0;">
                                <div class="cart-empty-msg <?= ($this->cart->total_items() > 1) ? 'hide' : ''; ?>">
                                    <?= '<h4 class="text-bold">' . lang('empty_cart') . '</h4>'; ?>
                                </div>
                                <div class="cart-contents">
                                    <div class="table-responsive">
                                        <table id="cart-table"
                                               class="table table-condensed table-striped table-cart margin-bottom-no">
                                            <thead>
                                            <tr>
                                                <th><i class="text-grey fa fa-trash-o"></i></th>
                                                <th>#</th>
                                                <th class="col-xs-4" colspan="2"><?= lang('product'); ?></th>
                                                <th class="col-xs-3"><?= lang('option'); ?></th>
                                                <th class="col-xs-1"><?= lang('qty'); ?></th>
                                                <th class="col-xs-2"><?= lang('price'); ?></th>
                                                <th class="col-xs-2"><?= lang('subtotal'); ?></th>
                                            </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="cart-contents">
                                <div id="cart-helper" class="panel panel-footer margin-bottom-no">
                                    <a href="<?= site_url('cart/destroy'); ?>" id="empty-cart"
                                       class="btn btn-danger btn-sm">
                                        <?= lang('empty_cart'); ?>
                                    </a>
                                    <a href="<?= shop_url('products'); ?>"
                                       class="btn btn-primary btn-sm pull-right continushoping">
                                        <i class="fa fa-share"></i>
                                        <?= lang('continue_shopping'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-sm-4">
                        <div class="cart-contents">
                            <div id="sticky-con" class="margin-top-lg">
                                <div class="panel panel-default">
                                    <div class="panel-heading text-bold">
                                        <i class="fa fa-calculator margin-right-sm"></i> <?= lang('cart_totals'); ?>
                                    </div>
                                    <div class="panel-body">
                                        <table id="cart-totals"
                                               class="table table-borderless table-striped cart-totals"></table>
                                        <a href="<?= site_url('cart/checkout'); ?>"
                                           class="btn btn-primary btn-lg btn-block proceed-k"><?= lang('checkout'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>-->


                

                <!--<code class="text-muted">* <?= lang('shipping_rate_info'); ?></code>-->
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function () {
        $('.shipping-row').hide()
    })
</script>
