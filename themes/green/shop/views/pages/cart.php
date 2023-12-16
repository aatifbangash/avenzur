<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents" style="background:white !important;">
    <div class="container container-max-width cartpage">
        <div class="row">
            <div class="col-md-12">

                <!-- cart new dsign -->
                <div class="row justify-content-between ">

                    <div class="col-sm-7">
                        <h3>Shopping Cart</h3>
                        <div class="row bg-light ps-1 mt-5" id="cart-table-new" style="margin-inline: 3px;padding: 25px;">
                            
                        </div>
                    </div>


                    <div class="col-sm-5">
                        <div class="text-end">
                            <!--<a class="navbar-brand" href="#">
                            <img src="<?= base_url('assets/uploads/logos/'.$shop_settings->logo); ?>" alt="logo" >
                            </a>-->
                        </div>
                        <div class="border p-3 px-4 mt-5 pb-5">
                            <h2>Order Summary</h2>
                        
                            <h4>Coupon Code</h4>
                            <div class="d-flex">
                                <input type="text"  class="form-control  rounded-0" placeholder="Welcom20">
                                <button class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-1 " style="font-size:14px !important;width: 175px !important;"> Code applied!</button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-3">
                                <h4 class="m-0 fw-semibold">Subtotal (<span id="total-unique_items">0</spam> items)</h4>
                                <h4 class="m-0 fw-semibold"><span id="total-price">0 SAR</span></h4>
                            </div>
                            <div class="d-flex justify-content-between align-items-center my-3">
                                <h4 class="m-0 ">Discount</h4>
                                <h4 class="m-0 "><span id="total-discount">SAR 0</span></h4>
                            </div>
                            <h4 class="m-0 opacity-50 border-bottom pb-1">Shipping fee will be calculated at checkout</h4>
                            <div class="d-flex justify-content-between align-items-center my-3">
                                <h4 class="m-0 fw-bold fs-5">Total <span class="fs-6 opacity-50">(Inclusive of VAT)</span></h4>
                                <h4 class="m-0 fw-bold fs-5"><span id="total-after_discount">SAR 0</span></h4>
                            </div>

                            <a href="<?= site_url('cart/destroy'); ?>" id="empty-cart" class="btn btn-danger btn-sm">
                                <button class="btn btn-lg primary-buttonAV rounded-0 fw-normal px-4 py-1 " style="font-size:18px !important;"> Checkout</button>
                            </a>
                            
                        </div>
                    </div>

                </div>
                <!-- cart new dsign end -->




                <div class="row">

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

                </div>


                

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
