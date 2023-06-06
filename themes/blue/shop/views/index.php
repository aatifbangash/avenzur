<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
 <!-- Welcome <?php // echo $this->session->userdata('country'); ?> -->
<?php if (!empty($slider) && !empty($slider[0]->image) && !empty($slider[0]->link)) {
    ?>
<section class="slider-container">
    <div class="container">
        <div class="row">
            <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
                <ol class="carousel-indicators margin-bottom-sm">
                    <?php
                    $sr = 0;
    foreach ($slider as $slide) {
        if (!empty($slide->image)) {
            echo '<li data-target="#carousel-example-generic" data-slide-to="' . $sr . '" class="' . ($sr == 0 ? 'active' : '') . '"></li> ';
        }
        $sr++;
    } ?>
                </ol>

                <div class="carousel-inner" role="listbox">
                    <?php
                    $sr = 0;
    foreach ($slider as $slide) {
        if (!empty($slide->image)) {
            echo '<div class="item' . ($sr == 0 ? ' active' : '') . '">';
            if (!empty($slide->link)) {
                echo '<a href="' . $slide->link . '">';
            }
            echo '<img src="' .base_url('assets/uploads/' . $slide->image) . '" alt="" style="max-height:600px;object-fit:cover;">';
            if (!empty($slide->caption)) {
                echo '<div class="carousel-caption">' . $slide->caption . '</div>';
            }
            if (!empty($slide->link)) {
                echo '</a>';
            }
            echo '</div>';
        }
        $sr++;
    } ?>
                </div>

                <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
                    <span class="fa fa-chevron-left" aria-hidden="true"></span>
                    <span class="sr-only"><?= lang('prev'); ?></span>
                </a>
                <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
                    <span class="fa fa-chevron-right" aria-hidden="true"></span>
                    <span class="sr-only"><?= lang('next'); ?></span>
                </a>
            </div>
        </div>
    </div>
</section>
<?php
} ?>

<section class="page-contents" style="background:#FFFFFF !important;">
    <div class="container">
        <div class="row" style="padding:40px 40px !important;">
            <div class="col-md-12">
                <img src="<?= base_url('assets/images/SD-Italy.svg') ?>" style="margin: 0 auto; display: block;" class="img-responsive icon" > 
              <h2 style="text-align:center !important;   " class="select-head"> Selected products for you</h2>
                        <?php
                        $r = 0;
                        foreach (array_chunk($featured_products, 8) as $fps) {
                            ?>
                            <div class="item row <?= empty($r) ? 'active' : ''; ?>">
                                <div class="selected-products">
                                        <?php
                                        foreach ($fps as $fp) {
                                            ?>
                                        <div class="col-sm-6 col-md-3" id="select-prod">
                                                <div class="product alt ">
        
                                                    <div class="product-top">
                                                        <div class="image">
                                                        
                                                        <img src="<?= base_url('assets/uploads/' . $fp->image); ?>" alt=""  class="img-responsive">
                                                        </a>
                                                    </div>
                                                </div>
                                                    <div class="product-desc homeprod">
                                                          <div class="product_name">
                                                            <a href="<?= site_url('product/' . $fp->slug); ?>"><?= $fp->name; ?></a><br>
                                                        </div>
                                                        <div class="pro-cat">
                                                        <a href="<?= site_url('category/' . $fp->category_slug); ?>" class="link"><?= $fp->category_name; ?></a>
                                                        <?php
                                                        if ($fp->brand_name) {
                                                            ?>
                                                            <span class="link">-</span>
                                                            <a href="<?= site_url('brand/' . $fp->brand_slug); ?>" class="link"><?= $fp->brand_name; ?></a>
                                                            <?php
                                                        } ?>
                                                        </div>
                                                    </div>
                                                        <div class="product-bottom">
                                                             <div class="product-price">
                                
                                                                 <?php
                                                            if ($fp->promotion) {
                                                                echo '<del class="text-red">' . $this->sma->convertMoney(isset($fp->special_price) && !empty(isset($fp->special_price)) ? $fp->special_price : $fp->price) . '</del>&nbsp;';
                                                                echo $this->sma->convertMoney($fp->promo_price);
                                                            } else {
                                                                echo $this->sma->convertMoney(isset($fp->special_price) && !empty(isset($fp->special_price)) ? $fp->special_price : $fp->price);
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
                                                    <div class="col-md-5 col-xs-6" id="cart-button">
                                                          <!--<div class="clearfix"></div>-->
                                                                <div class="product-cart">
                                                                <div class="btn-group" role="group" aria-label="...">
                                                                <button class="btn btn-info add-to-wishlist" data-id="13"><i class="fa fa-heart-o"></i></button>
                                                                 <div class="btn btn-theme add-to-cart" data-id="<?= $fp->id; ?>"><i class="fa fa-shopping-cart"></i> <?= lang('add_to_cart'); ?></div>
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
   
        </div>
        
    </div>
</section>
<section class="page-dataa">
        <div class="container">    

    <div class="row" id="back-img" style="padding:40px 40px !important;  ">
            <div class="col-md-6">

                  <img   title="B1" class="img-responsive" src="<?= base_url('assets/images/B1-1200x1274.jpg'); ?>" >
            
            </div>
        <div class="col-md-6" id="sect-2">
            <h1 style="font-size:64px !important;font-family: Montserrat !important;font-weight:400 !important;">
                Beauty starts <br> from inside
            </h1>
            <p style="padding:40px 0px !important;font-size:20px !important; ">
                Discover our specially formulated products that helps you shine from inside out.
            </p>
<?php             if (isset($fp)) {
    
     ?>
             <a href="<?= site_url('category/' . $fp->category_slug); ?>"  ><button type="button" class="btn btn-success" style="padding: 20px 40px !important;">DISCOVER</button></a>
      <?php } ?>
        </div>
        
    </div>
 </div>   
</section>
<section class="data-above-footer" style="background:#FFFFFF;">
    <div class="container">
         <div class="row" style="padding:40px 15px !important;">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-xs-9">
                        <!--<h3 class="margin-top-no text-size-lg">-->
                        <!--    <?= lang('featured_products'); ?>-->
                        <!--</h3>-->
                    </div>
                    <?php
                    if (count($featured_products) > 8) {
                        ?>
                        <!--<div class="col-xs-3">-->
                        <!--    <div class="controls pull-right hidden-xs">-->
                        <!--        <a class="left fa fa-chevron-left btn btn-xs btn-default" href="#carousel-example"-->
                        <!--        data-slide="prev"></a>-->
                        <!--        <a class="right fa fa-chevron-right btn btn-xs btn-default" href="#carousel-example"-->
                        <!--        data-slide="next"></a>-->
                        <!--    </div>-->
                        <!--</div>-->
                        <?php
                    }
                    ?>
                </div>
             <div class="col-md-12">
              
                        <?php
                        $r = 0;
                        foreach (array_chunk($featured_products, 4) as $fps) {
                            ?>
                            <div class="item row <?= empty($r) ? 'active' : ''; ?>">
                                <div class="selected-products">
                                        <?php
                                        foreach ($fps as $fp) {
                                            ?>
                                        <div class="col-sm-6 col-md-3">
                                                <div class="product alt ">
        
                                                    <div class="product-top">
                                                        <div class="image">
                                                        
                                                        <img src="<?= base_url('assets/uploads/' . $fp->image); ?>" alt="" class="img-responsive">
                                                     
                                                    </div>
                                                </div>
                                                    <div class="product-desc homeprod">
                                                          <div class="product_name">
                                                            <a href="<?= site_url('product/' . $fp->slug); ?>"><?= $fp->name; ?></a>
                                                        </div>
                                                         <div class="pro-cat">
                                                        <a href="<?= site_url('category/' . $fp->category_slug); ?>" class="link"><?= $fp->category_name; ?></a>
                                                        <?php
                                                        if ($fp->brand_name) {
                                                            ?>
                                                            <span class="link">-</span>
                                                            <a href="<?= site_url('brand/' . $fp->brand_slug); ?>" class="link"><?= $fp->brand_name; ?></a>
                                                            <?php
                                                        } ?>
                                                        </div>
                                                    </div>
                                                        <div class="product-bottom">
                                                             <div class="product-price">
                                
                                                             <?php
                                                                                        if ($fp->promotion) {
                                                                                            echo '<del class="text-red">' . $this->sma->convertMoney(isset($fp->special_price) && !empty(isset($fp->special_price)) ? $fp->special_price : $fp->price) . '</del><br>';
                                                                                            echo $this->sma->convertMoney($fp->promo_price);
                                                                                        } else {
                                                                                            echo $this->sma->convertMoney(isset($fp->special_price) && !empty(isset($fp->special_price)) ? $fp->special_price : $fp->price);
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
                                                                 <div class="btn btn-theme add-to-cart" data-id="<?= $fp->id; ?>"><i class="fa fa-shopping-cart"></i> <?= lang('add_to_cart'); ?></div>
                                                               <div class="clearfix"></div>

                                                                </div>
                                                                </div>

                                                    </div>
                                                </div>  </div> 
                                            
                                                              
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
        </div>
        
    </div>
</section>
