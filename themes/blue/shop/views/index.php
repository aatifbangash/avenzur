<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link href="<?= base_url('assets/custom/carousel.css') ?>" rel="stylesheet"/>
<script src="<?= base_url('assets/custom/waltzerjs.min.js') ?>"></script>
<!-- Welcome <?php // echo $this->session->userdata('country'); ?> -->
<?php if (!empty($slider) && !empty($slider[0]->image) && !empty($slider[0]->link)) {
  ?>
  <style>
    div#select-prod:hover {
      border: none !important;
      background: none !important;
    }

    .product:hover {
      border: none !important;
      box-shadow: none;
    }
  </style>
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
                echo '<img src="' . base_url('assets/uploads/' . $slide->image) . '" alt="" style="max-height:600px;">';
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
        <!--<img src="<?php //echo base_url('assets/images/SD-Italy.svg') ?>" style="margin: 0 auto; display: block;" class="img-responsive icon" > -->
        <h2 style="text-align:center !important;   " class="select-head"> Featured Categories</h2>
        <?php
        $r = 0;
        foreach (array_chunk($featured_categories, 8) as $fps) {
          ?>
          <div class="item row <?= empty($r) ? 'active' : ''; ?>">

            <?php
            foreach ($fps as $fp) {
              ?>
              <div class="col-sm-2" id="select-prod" style="padding: 0px !important;">
                <div class="product alt "
                     style="border-radius: 0px !important;border: 0px !important;padding: 25px !important;">

                  <div class="product-top">
                    <div class="image">
                      <a href="<?= site_url('category/' . $fp->slug) ?>">
                        <img src="<?= base_url('assets/uploads/' . $fp->image); ?>" alt=""
                             class="img-responsive">
                      </a>
                    </div>
                  </div>
                  <div class="product-desc homeprod">
                    <div class="product_name"
                         style="font-size: 12px !important;font-weight: normal !important;display:block;text-align:center;">
                      <a href="<?= site_url('product/' . $fp->slug); ?>"><?= $fp->name; ?></a><br>
                    </div>
                    <div class="pro-cat">
                      <a href="<?= site_url('category/' . $fp->category_slug); ?>"
                         class="link"><?= $fp->category_name; ?></a>
                      <?php
                      if ($fp->brand_name) {
                        ?>
                        <span class="link">-</span>
                        <a href="<?= site_url('brand/' . $fp->brand_slug); ?>"
                           class="link"><?= $fp->brand_name; ?></a>
                        <?php
                      } ?>
                    </div>
                  </div>

                  <div class="product-bottom">
                    <!--<div class="product-price">

                                                        <?php
                    //if ($fp->promotion) {
                    // echo '<del class="text-red">' . $this->sma->convertMoney(isset($fp->special_price) && !empty(isset($fp->special_price)) ? $fp->special_price : $fp->price) . '</del>&nbsp;';
                    //echo $this->sma->convertMoney($fp->promo_price);
                    //} else {
                    //echo $this->sma->convertMoney(isset($fp->special_price) && !empty(isset($fp->special_price)) ? $fp->special_price : $fp->price);
                    //}
                    ?>
                                                        </div>-->

                    <!--<div class="details" style="transition: all 100ms ease-out 0s;">-->

                    <!--<div class="clearfix"></div>


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
                                                                <div class="product-cart">
                                                                <div class="btn-group" role="group" aria-label="...">
                                                                <button class="btn btn-info add-to-wishlist" data-id="13"><i class="fa fa-heart-o"></i></button>
                                                                 <div class="btn btn-theme add-to-cart" data-id="<?php //echo $fp->id; ?>"><i class="fa fa-shopping-cart"></i> <?= lang('add_to_cart'); ?></div>
                                                               <div class="clearfix"></div>

                                                                </div>
                                                                </div>

                                                    </div>
                                                </div>-->

                  </div>


                </div>
              </div>
              <?php
            } ?>

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

    <div class="row" id="back-img" style="padding:0px 40px !important;margin-bottom: 45px;">

      <div class="col-md-12">
        <div class="banner11"
             style="display: flex;justify-content: center;align-items: center;margin-bottom: 80px;margin-top: 0px;">
          <a href="<?= site_url('category/skn'); ?>">
            <img style="max-width: 100%;max-height: 100%;"
                 src="<?= base_url('assets/images/side-banner.jpg') ?>"/>
          </a>
        </div>
      </div>

      <div class="col-md-6">

        <img title="B1" class="img-responsive" src="<?= base_url('assets/images/bg1.jpg'); ?>">

      </div>
      <div class="col-md-6" id="sect-2">
        <h1 style="font-size:64px !important;font-family: Montserrat !important;font-weight:400 !important;">
          Beauty starts <br> from inside
        </h1>
        <p style="padding:40px 0px !important;font-size:20px !important; ">
          Discover our specially formulated products that helps you shine from inside out.
        </p>
        <a href="<?= site_url('category/beauty'); ?>">
          <button type="button" class="btn btn-success" style="padding: 20px 40px !important;">DISCOVER
          </button>
        </a>

      </div>
    </div>
  </div>
</section>
<section class="data-above-footer" style="background:#FFFFFF;">
  <div class="container">
    <div class="row" style="padding:40px 15px !important;">

      <!--<div class="col-md-12">
                <div id="carousel1" class='outerWrapper'>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/9b0ec372041c805dada856cd0ba83438.jpeg') ?>"></div>
                    </div>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/2e2b4769f47726f01ee1ec487b5d5206.jpeg') ?>"></div>
                    </div>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/a67dd40afc0aeef53a1222cb80c01b27.jpeg') ?>"></div>
                    </div>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/9b0ec372041c805dada856cd0ba83438.jpeg') ?>"></div>
                    </div>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/2e2b4769f47726f01ee1ec487b5d5206.jpeg') ?>"></div>
                    </div>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/a67dd40afc0aeef53a1222cb80c01b27.jpeg') ?>"></div>
                    </div>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/9b0ec372041c805dada856cd0ba83438.jpeg') ?>"></div>
                    </div>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/2e2b4769f47726f01ee1ec487b5d5206.jpeg') ?>"></div>
                    </div>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/a67dd40afc0aeef53a1222cb80c01b27.jpeg') ?>"></div>
                    </div>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/9b0ec372041c805dada856cd0ba83438.jpeg') ?>"></div>
                    </div>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/2e2b4769f47726f01ee1ec487b5d5206.jpeg') ?>"></div>
                    </div>
                    <div class="carousel-item">
                        <div><img width="223" height="240" src="<?= base_url('assets/images/a67dd40afc0aeef53a1222cb80c01b27.jpeg') ?>"></div>
                    </div>
                </div>
                <script>$('#carousel1').waltzer({scroll:1});</script>
            </div>-->

      <div class="col-md-12">
        <div class="banner1"
             style="display: flex;justify-content: center;align-items: center;margin-bottom: 15px;">
          <a href="<?= site_url('category/vitamins'); ?>">
            <img style="max-width: 100%;max-height: 100%;"
                 src="<?= base_url('assets/images/1440-x-300.jpg') ?>"/>
          </a>
        </div>

        <div class="banner111"
             style="display: flex;justify-content: center;align-items: center;margin-bottom: 15px;">
          <a href="<?= site_url('category/beauty'); ?>">
            <img style="max-width: 100%;max-height: 100%;"
                 src="<?= base_url('assets/images/1440-x-300-(3).jpg') ?>"/>
          </a>
        </div>
      </div>

      <div class="col-md-12">
        <h2 style="text-align:center !important;" class="select-head"> Featured Products <span
            style="float: right;font-size: 16px;color: #FFA41C;text-decoration: underline;"><a
              style="color: #FFA41C;"
              href="<?= base_url('shop/featured_products') ?>">View All</a></span></h2>
        <?php
        $r = 0;
        foreach (array_chunk($featured_products, 4) as $sps) {
          ?>
          <div class="item row <?= empty($r) ? 'active' : ''; ?>"
               style="margin-top: 40px;margin-bottom: 40px;">
            <div class="selected-products">
              <?php
              foreach ($sps as $sp) {
                ?>
                <div class="col-sm-6 col-md-3">
                  <div class="product alt ">
                    <div class="product-top">
                      <div class="image">
                        <a href="<?= site_url('product/' . $sp->slug); ?>">
                          <img src="<?= base_url('assets/uploads/' . $sp->image); ?>" alt=""
                               class="img-responsive">
                        </a>
                      </div>
                    </div>
                    <div class="product-desc homeprod">
                      <div class="product_name">
                        <a href="<?= site_url('product/' . $sp->slug); ?>"><?= $sp->name; ?></a>
                      </div>
                      <div class="pro-cat">
                        <a href="<?= site_url('category/' . $sp->category_slug); ?>"
                           class="link"><?= $sp->category_name; ?></a>
                        <?php
                        if ($sp->brand_name) {
                          ?>
                          <span class="link">-</span>
                          <a href="<?= site_url('brand/' . $sp->brand_slug); ?>"
                             class="link"><?= $sp->brand_name; ?></a>
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
                                                                <span class="input-group-addon pointer btn-minus"><span
                                                                    class="fa fa-minus"></span></span>
                                <input type="text" name="quantity"
                                       class="form-control text-center quantity-input prod_quant"
                                       value="1" required="required">
                                <span class="input-group-addon pointer btn-plus"><span
                                    class="fa fa-plus"></span></span>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-5 col-xs-6">
                          <div class="clearfix"></div>
                          <div class="product-cart">
                            <div class="btn-group" role="group" aria-label="...">
                              <button class="btn btn-info add-to-wishlist" data-id="13"><i
                                  class="fa fa-heart-o"></i></button>
                              <div class="btn btn-theme add-to-cart"
                                   data-id="<?= $sp->id; ?>"><i
                                  class="fa fa-shopping-cart"></i> <?= lang('add_to_cart'); ?>
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

      <div class="col-md-12">
        <div class="banner11"
             style="display: flex;justify-content: center;align-items: center;margin-bottom: 15px;">
          <a href="<?= site_url('category/sup'); ?>">
            <img style="max-width: 100%;max-height: 100%;"
                 src="<?= base_url('assets/images/1440-x-300-(2).jpg') ?>"/>
          </a>
        </div>

        <div class="twinbanners"
             style="display: flex;justify-content: center;align-items: center;margin-top: 60px;margin-bottom: 40px;">
          <div class="col-md-6" style="padding-left: 0px;">
            <div class="banner2">
              <a href="<?= site_url('category/mombaby'); ?>">
                <img alt="Banner2" width="547" height="230"
                     src="<?= base_url('assets/images/710-x-300-(1).jpg') ?>"/>
              </a>
            </div>
          </div>
          <div class="col-md-6" style="padding-left: 0px;">
            <div class="banner3">
              <a href="<?= site_url('category/skn'); ?>">
                <img alt="Banner3" width="555" height="230"
                     src="<?= base_url('assets/images/710-x-300-(2).jpg') ?>"/>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!--- Special Offers -->
      <div class="col-md-12">
        <h2 style="text-align:center !important;" class="select-head"> Special Offers</h2>
        <?php
        $r = 0;
        foreach (array_chunk($special_offers, 4) as $sps) {
          ?>
          <div class="item row <?= empty($r) ? 'active' : ''; ?>" style="margin-top: 40px;">
            <div class="selected-products">
              <?php
              foreach ($sps as $sp) {
                ?>
                <div class="col-sm-6 col-md-3">
                  <div class="product alt ">
                    <div class="product-top">
                      <div class="image">
                        <a href="<?= site_url('product/' . $sp->slug); ?>">
                          <img src="<?= base_url('assets/uploads/' . $sp->image); ?>" alt=""
                               class="img-responsive">
                        </a>
                      </div>
                    </div>
                    <div class="product-desc homeprod">
                      <div class="product_name">
                        <a href="<?= site_url('product/' . $sp->slug); ?>"><?= $sp->name; ?></a>
                      </div>
                      <div class="pro-cat">
                        <a href="<?= site_url('category/' . $sp->category_slug); ?>"
                           class="link"><?= $sp->category_name; ?></a>
                        <?php
                        if ($sp->brand_name) {
                          ?>
                          <span class="link">-</span>
                          <a href="<?= site_url('brand/' . $sp->brand_slug); ?>"
                             class="link"><?= $sp->brand_name; ?></a>
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
                                                                <span class="input-group-addon pointer btn-minus"><span
                                                                    class="fa fa-minus"></span></span>
                                <input type="text" name="quantity"
                                       class="form-control text-center quantity-input prod_quant"
                                       value="1" required="required">
                                <span class="input-group-addon pointer btn-plus"><span
                                    class="fa fa-plus"></span></span>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div class="col-md-5 col-xs-6">
                          <div class="clearfix"></div>
                          <div class="product-cart">
                            <div class="btn-group" role="group" aria-label="...">
                              <button class="btn btn-info add-to-wishlist" data-id="13"><i
                                  class="fa fa-heart-o"></i></button>
                              <div class="btn btn-theme add-to-cart"
                                   data-id="<?= $sp->id; ?>"><i
                                  class="fa fa-shopping-cart"></i> <?= lang('add_to_cart'); ?>
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
  </div>

  </div>
</section>
