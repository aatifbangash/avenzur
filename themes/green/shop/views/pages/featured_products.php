
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents">
    <div class="container">
    
        <div class="item">
            <h2 style="text-align:center !important;" class="select-head"> Featured Products </h2>
            <?php
                $r = 0;
                foreach (array_chunk($featured_products, sizeOf($featured_products)) as $sps){
                    ?>
                    <div class="item row <?= empty($r) ? 'active' : ''; ?>" style="margin-top: 40px;margin-bottom: 40px;">
                        <div class="selected-products">
                                <?php
                                foreach ($sps as $sp) {
                                    ?>
                                <div class="col-sm-6 col-md-3">
                                        <div class="product alt ">
                                            <div class="product-top">
                                                <div class="image">
                                                    <a href="<?= site_url('product/' . $sp->slug); ?>">
                                                        <img src="<?= base_url('assets/uploads/' . $sp->image); ?>" alt="" class="img-responsive">
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="product-desc homeprod">
                                                <div class="product_name">
                                                    <a href="<?= site_url('product/' . $sp->slug); ?>"><?= $sp->name; ?></a>
                                                </div>
                                                <div class="pro-cat">
                                                    <a href="<?= site_url('category/' . $sp->category_slug); ?>" class="link"><?= $sp->category_name; ?></a>
                                                    <?php
                                                    if ($sp->brand_name) {
                                                        ?>
                                                        <span class="link">-</span>
                                                        <a href="<?= site_url('brand/' . $sp->brand_slug); ?>" class="link"><?= $sp->brand_name; ?></a>
                                                        <?php
                                                    } ?>
                                                </div>
                                            </div>
                                            <div class="product-bottom">
                                                <div class="product-price">
                                                <?php
                                                    if ($sp->promotion) {
                                                        echo '<del class="text-red">' . $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price) . '</del><br>';
                                                        echo $this->sma->convertMoney($sp->promo_price);
                                                    } else {
                                                        echo $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price);
                                                    } ?>
                                                </div>
                                    
                                                <!--<div class="details" style="transition: all 100ms ease-out 0s;">-->

                                                <div class="clearfix"></div>
                                        
                                                <div class="row">
                                            
                                                <div class="col-md-7 col-xs-6">
                                                    <div class="product-quantity">
                                                        <div class="form-group" style="margin-bottom:0;">
                                                            <div class="input-group">
                                                                <span class="input-group-addon pointer btn-minus"><span class="fa fa-minus"></span></span>
                                                                <input type="text" name="quantity" class="form-control text-center quantity-input prod_quant" value="1" required="required">
                                                                <span class="input-group-addon pointer btn-plus"><span class="fa fa-plus"></span></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-5 col-xs-6">
                                                    <div class="clearfix"></div>
                                                        <div class="product-cart">
                                                            <div class="btn-group" role="group" aria-label="...">
                                                                <button class="btn btn-info add-to-wishlist" data-id="13"><i class="fa fa-heart-o"></i></button>
                                                                <div class="btn btn-theme add-to-cart" data-id="<?= $sp->id; ?>"><i class="fa fa-shopping-cart"></i> <?= lang('add_to_cart'); ?>
                                                            </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>  
                                    </div>                
                                </div>
                        </div>
                                <?php
                            } ?>
                    </div>
                    </div>
                    <?php
                    $r++;
                    break;
                }
            ?>
        </div>
      
    </div>
</section>

