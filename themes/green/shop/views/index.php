<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- hero silder -->
<section class="heroSlider mt-4">
      <div class="container container-max-width">
        <div id="carouselExampleIndicators" class="carousel slide">
          <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" class="active" aria-current="true" aria-label="Slide 3"></button>
           
          </div>

          <div class="carousel-inner">
            <?php
                $sr = 0;
                foreach ($slider as $slide) {
                    if (!empty($slide->image)) {
                        ?>
                            <div class="carousel-item <?= ($sr == 2 ? ' active' : ''); ?>">
                                <a href="<?php echo $slide->link; ?>">
                               

                                

                                  <img src="<?= base_url('assets/uploads/' . $slide->image.'?timestamp='.time()); ?>" class="d-block w-100" alt="Sulfad Promotion"> 
                                </a>
                            </div>
                        <?php
                    }
                    $sr++;
                }
            ?>
          </div>
          <div class="arowbtn">
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true" ><i class="bi bi-arrow-left-square-fill"></i></span>
            <span class="visually-hidden">previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
           <span class="arrowRight">
            <span class="carousel-control-next-icon" aria-hidden="true" ><i class="bi bi-arrow-right-square-fill"></i></span>
            <span class="visually-hidden">next</span>
           </span>
          </button>
        </div>
        </div>
      </div>
</section>
    <!-- hero slider end -->

    <!-- feature section -->
    <section class="categories section-marg-top">
      <div class="container container-max-width">
       <div class="featureTitle text-center"><h1 class="title-wrapper override-h1">Categories</h1></div>
       <!-- cards -->
       <div class="row feature-cards text-center feature-cards2">
            <?php
                $r = 0;
                foreach (array_chunk($featured_categories, 8) as $fps) {
                    foreach ($fps as $fp) {
                    ?>
                        <div class="col-lg-2 col-md-4 col-sm-6">
                            <a href="<?= site_url('category/'.$fp->slug) ?>" class="text-decoration-none">
                            
                                <div class="card" style="width: 100%;">
                                <div class="cardImg">
                                
                                    <img src="<?= base_url('assets/uploads/' . $fp->image); ?>" class="card-img-top"  alt="<?= $fp->name; ?>"></div>
                                <div class="card-body">
                                    <h5 class="card-title"><?= ( strtolower($fp->name) == 'otc' ? 'OTC' : ucfirst(strtolower($fp->name))); ?></h5>
                                </div>
                                </div> 

                            </a>
                        </div>
                    <?php
                    }
                }
            ?>
       </div>
      </div>
    </section>
    <!-- feature section end -->
    
<!-- speacial offer banner slider -->
    <section class="speacialOffer section-marg-top">
      <div class="container container-max-width ">
      <div class="featureTitle text-center"><h2 class="title-wrapper">Special Offers</h2></div>
          <div class="d-flex speacialOfferMove margin-minus">
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/har?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner1.jpg" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/vitamins?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner2.jpg" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/man?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner3.jpg" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/beauty1?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner4.jpg" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/skin-care?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner5.jpg" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/flu?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner6.jpg" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/har?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner1.jpg"  alt="movingBanner" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/vitamins?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner2.jpg" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/man?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner3.jpg" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/beauty1?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner4.jpg" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/skin-care?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner5.jpg" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
            <div class="item">
              <div class="moveBanner">
                <a href="<?= site_url('category/flu?special_product=yes'); ?>">
                  <img src="assets/images/banners/moveBanner6.jpg" class="w-100" alt="Special Offer">
                </a>
              </div>
            </div>
          </div>
      </div>
    </section>
<!-- speacial offer section end -->

    <!-- banner area 1 -->
    <section class="side-banner section-marg-top">
      <div class="container container-max-width">
        <div class="sideBannerImg">
          <a href="<?= site_url('shop/products?promo=yes'); ?>">
            <img id="promo-banner-1" loading="lazy" src="<?= base_url('assets/images/banners/promo_offers_2024-06-12_en.jpg'.'?timestamp='.time()); ?>" alt="Promotion" class="w-100" />
          </a>
        </div>
      </div>
    </section>

     <!-- feature products  end-->

     <section class="side-banner section-marg-top">
      <div class="container container-max-width">
        <div class="sideBannerImg">
          <a href="<?= site_url('category/supplements'); ?>"><img id="supplements-banner-1" loading="lazy" src="<?= base_url('assets/images/banners/supplement_inner_banner_2024-06-12_en.jpg'.'?timestamp='.time()); ?>" alt="Supplements Promotion" class="w-100"></a>
        </div>
      </div>
    </section>
    <!-- banner area 1 end -->

    <!-- boom categores -->
    <section class="boom-categories mobile-boom-categories mt-4">
      <div class="container container-max-width">
        <div class="row">

          <div class="col-lg-8 col-md-12 col-sm-12">
            
            <div class="row align-items-stretch cat-col-6-padding">

              <div class="col-lg-8 col-md-7 col-6">
                <div class="boom-product-cat py-4 px-4" style="background-image: url(<?= base_url('assets/images/banners/boom1.png'); ?>)">
                  <div class="row align-items-center">
                    <div class="col-md-5">

                      <img loading="lazy" src="<?= base_url('assets/images/banners/boomtab1.png'.'?timestamp='.time()); ?>" alt="Ghali'or Collection"  class="w-100"/>
                    </div>
                    <div class="col-lg-7 col-md-6">
                      <!--<p class="m-0 py-2"><span class="boom-parag"></span></p>-->
                      <span class="btitle py-3"><span style="font-weight: bold;">Ghali'or</span> Collection</span>
                      <a href="https://avenzur.com/brand/ghalior-paris1"><button type="button" class="btn primary-buttonAV mt-3 pt-1">Buy now <i class="bi bi-chevron-right ms-1"></i></button></a>

                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-5 col-6">
                <div class="boom-product-cat py-4 px-4" style="background-image: url(<?= base_url('assets/images/banners/boom2.png'); ?>)">
                  <div class="row align-items-center">
                   
                    <div class="col-md-12 product-m-order2">
                      <!--<p class="m-0 py-2"><span class="boom-parag"></span></p>-->
                      <span class="btitle py-3"><span style="font-weight: bold;">Cerave</span> Collections</span>
                      <a href="https://avenzur.com/brand/cerave">
                        <button type="button" class="btn primary-buttonAV mt-3 pt-1">Buy now <i class="bi bi-chevron-right ms-1s"></i></button>
                      </a>

                    </div>
                    <div class="m-l-10 product-m-order1">

                      <img loading="lazy" src="<?= base_url('assets/images/banners/tabpack.png'.'?timestamp='.time()); ?>" alt="Cerave Collection"  class="w-100"/>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- boom-sectond row -->
            <div class="row mt-4 boom-product-cat-mob-marg cat-col-6-padding">

              <div class="col-md-5 col-sm-12 mobile-marg-b col-6">
                <div class="boom-product-cat py-4 px-4 " style="background-image: url(<?= base_url('assets/images/banners/boom4.png'); ?>)">
                  <div class="row align-items-center">
                   
                    <div class="col-md-6 pe-md-0 product-m-order2">
                      <!--<p class="m-0 py-2"><span class="boom-parag"></span></p>-->
                      <span class="btitle py-3"><span style="font-weight: bold;">Laperva</span> Collections</span>
                      <a href="https://avenzur.com/brand/laperva">
                      <button type="button" class="btn primary-buttonAV mt-3 pt-1">Buy now <i class="bi bi-chevron-right ms-1s"></i></button>
                      </a>
                    </div>
                    <div class="col-md-6 p-0 product-m-order1">

                      <img loading="lazy" src="<?= base_url('assets/images/banners/tabpack3.png'.'?timestamp='.time()); ?>" alt="Laperva Collection"  class="w-100"/>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-7 col-sm-12 col-6 mobile-marg-b mobile-h-marg-b-0">
                <div class="boom-product-cat py-4 px-4" style="background-image: url(<?= base_url('assets/images/banners/boom5.png'); ?>)">
                  <div class="d-flex align-items-center mobile-wrap">
                   
                    <div class="col-md-6 p-md-0 boomsale product-m-order2">
                      <span class="bigsale"></span>
                      <p class="m-0 py-2"><span class="boom-parag"></span></p>
                      <span class="btitle py-3"><span style="font-weight: bold;">Honst</span> Food Supplements</span>
                      <a href="https://avenzur.com/brand/honstHonst">
                      <button type="button" class="btn primary-buttonAV mt-3 pt-1">Buy now <i class="bi bi-chevron-right ms-1"></i></button>
                      </a>
                    </div>
                    <div class="col-md-6 product-m-order1">

                      <img loading="lazy" src="<?= base_url('assets/images/banners/tabpack5.png'.'?timestamp='.time()); ?>" alt="Honst Food Supplements"  class="w-100"/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-12 col-sm-12  mt-10 mt-m-0 mobile-h-marg-t">
            <div class="boom-product-cat boom-product-cat-cont py-4 px-4" style="background-image: url('<?= base_url('assets/images/banners/boom3.png'); ?>')">
              <div class="row  align-items-center">
               
                <div class="col-lg-12 col-md-6 mb-4 flex-column d-flex col-6 col-sm-6 ">
                  <span class="bigsale"></span>
                  <p class="m-0 py-2"><span class="boom-parag"></span></p>
                  <span class="btitle "><span style="font-weight: bold;">Vitamins</span> Collection</span>
                  <a href="https://avenzur.com/category/vitamins">
                  <button type="button" class="btn primary-buttonAV mt-3 vitamin-but">Buy now <i class="bi bi-chevron-right ms-1s"></i></button>
                  </a>
                </div>
                <div class="col-lg-12 col-md-6 col-sm-6 col-6">

                  <img loading="lazy" src="<?= base_url('assets/images/banners/tabpack2.png'.'?timestamp='.time()); ?>" alt="Vitamins Collection"  class="w-100"/>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
    <!-- boom categores end -->

    <!-- banner area 1 -->
    <section class="side-banner mt-4">
      <div class="container container-max-width">
        <div class="sideBannerImg">
          <a href="<?= site_url('category/skin-care'); ?>">
            <img id="skincare-banner-1" loading="lazy" src="<?= base_url('assets/images/banners/SkinCare_Banner_2024-04-16.jpg'.'?timestamp='.time()); ?>" alt="Skin Care" class="w-100" />
          </a>
        </div>
      </div>
    </section>
    <!-- banner area 1 end -->

    <section class="popularCat-container section-marg-top">
        <div class="container container-max-width">
            <div class="categoryTabs">
                <div class="popularTitle text-center "><h2 class="title-wrapper">Popular Categories</h2></div>
                <ul class="nav nav-pills justify-content-center" id="pills-tab" role="tablist">
                    <?php
                    $pp = 0;
                    foreach($popular_categories as $popular_category){
                    ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php if($pp == 0) echo 'active'; ?>" id="pills-<?= str_replace(' ', '', $popular_category->name); ?>-tab" data-bs-toggle="pill" data-bs-target="#pills-<?= str_replace(' ', '', $popular_category->name); ?>" type="button" role="tab" aria-controls="pills-<?= str_replace(' ', '', $popular_category->name); ?>" aria-selected="true"><?= $popular_category->name; ?></button>
                        </li>
                    <?php
                        $pp++;
                    }
                    ?> 
                </ul>

                <div class="tab-content pt-3" id="pills-tabContent">
                    <?php
                    $pc = 0;
                    foreach($popular_categories as $popular_category){
                    ?>
                        <div class="tab-pane fade show <?php if($pc == 0) echo 'active'; ?>" id="pills-<?= str_replace(' ', '', $popular_category->name); ?>" role="tabpanel" aria-labelledby="pills-<?= str_replace(' ', '', $popular_category->name); ?>-tab" tabindex="0">
                            <!-- cards -->
                            <div class="row products-card popularCat text-center">
                                <?php
                                foreach($popular_category->products as $popular_product){
                                ?>
                                    <div class="col-lg-3 col-md-4 col-sm-12">
                                        <div class="card" style="width: 100%">
                                            <!--<a href="#" class="text-decoration-none">-->
                                                <div class="cardImg position-relative">
                                                    <?php 
                                                      if($popular_product->promotion && $popular_product->price > 0 && $popular_product->promo_price > 0){
                                                        ?>
                                                          <span class="position-absolute badge rounded-pill bg-danger" style="top:0px;left:0px;font-size:10px">
                                                            <?php echo round((($popular_product->price - $popular_product->promo_price) / $popular_product->price) * 100); ?>% OFF
                                                        </span>
                                                        <?php
                                                      }

                                                      if($popular_product->global == 1){
                                                        ?>
                                                          <!--<span class="position-absolute badge rounded-pill bg-info" style="top:0px;right:0px;font-size:10px">
                                                            Global
                                                          </span>-->
                                                          <span class="position-absolute badge" style="top:0px;right:0px;width: 90px;">
                                                            <img src="<?= base_url('assets/images/global.jpg'); ?>" style="height:20px;" class="card-img-top" alt="Global">
                                                          </span>
                                                        <?php
                                                      }
                                                    ?>
                                                    
                                                    <a href="<?= site_url('product/' . $popular_product->slug); ?>" class="text-decoration-none">
                                                        <img src="<?= base_url('assets/uploads/' . $popular_product->image); ?>" class="card-img-top" alt="<?= $popular_product->name; ?>">
                                                    </a>
                                                </div>
                                                <div class="card-body px-0 text-start pb-0">
                                                    <div class="product-cat-title">
                                                        <span class="text-uppercase"><?= $popular_category->name; ?></span>
                                                    </div>
                                                    <a href="<?= site_url('product/' . $popular_product->slug); ?>" class="text-decoration-none">
                                                        <h5 class="card-title text-start"><?= stripslashes($popular_product->name); ?></h5>
                                                    </a>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        
                                                        <div class="rating">
                                                          <?php 
                                                                for($i=1; $i<=5; $i++) {
                                                                  $class = '';
                                                                  if($i<=$sp->avg_rating) {$class = 'rated';}?>
                                                          <i class="bi bi-star-fill <?php echo $class;?>" ></i>
                                                          <?php }?>
                                                        </div>
                                                        <div class="discountPrice price text-end py-2">
                                                                            <h4 class="m-0 text-decoration-line-through">
                                                        <?php
                                                            if ($popular_product->promotion) {
                                                                ?>
                                                                    
                                                                        
                                                                                <?= $this->sma->convertMoney(isset($popular_product->special_price) && !empty(isset($popular_product->special_price)) ? $popular_product->special_price : $popular_product->price); ?>
                                                                            
                                                                    
                                                                <?php
                                                                
                                                            }
                                                        ?>
                                                        </h4>
                                                                        </div>
                                                    </div> 
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        
                                                            <div class="price text-start  py-2">
                                                                <h4 class="m-0 fw-bold">
                                                                <?php
                                                                if ($popular_product->promotion) {
                                                                    
                                                                        echo $this->sma->convertMoney($popular_product->promo_price);
                                                                    
                                                                }else{
                                                                    echo $this->sma->convertMoney(isset($popular_product->special_price) && !empty(isset($popular_product->special_price)) ? $popular_product->special_price : $popular_product->price);
                                                                }
                                                                ?>
                                                                    
                                                                </h4>
                                                            </div>
                                                        
                                                        
                                                            <div class="quantity text-end py-2 d-flex align-items-center justify-content-between">
                                                                <span class="plus btn-plus">
                                                                    <i class="bi bi-plus-circle-fill"></i>
                                                                </span>
                                                                <input type="text" name="quantity" readonly class="Qnum" value="1" required="required" />
                                                                <!--<span class="Qnum ">1</span>-->
                                                                <span class="minus btn-minus"><i class="bi bi-dash-circle-fill"></i></span>
                                                            </div>
                                                        
                                                    </div>
                                                </div>
                                            <!--</a>-->
                                            <div> 
                                              <?php 
                                                  if($popular_product->product_quantity > 0){
                                                      ?>
                                                          <button type="button" data-id="<?= $popular_product->id; ?>" aria-controls="offcanvasWithBothOptions" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark add-to-cart">Add to cart </button>
                                                      <?php
                                                  }else{
                                                      $notify_price = $popular_product->promotion == 1 ? $popular_product->promo_price : $popular_product->price;
                                                      ?>
                                                          Out of Stock
                                                          <button type="button" class="btn btn-link btn-notify-add-to-list" href="#" data-id="<?= $popular_product->id; ?>" data-title="<?= $popular_product->name; ?>" data-image="<?= $popular_product->image; ?>" data-price="<?= $notify_price; ?>" >Notify me</button>
                                                      <?php
                                                  }
                                              ?>
                                            </div>
                                        </div> 
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php 
                    $pc++;
                    } ?>
                </div>
            </div>
        </div>

        <div class="side-banner section-marg-top">
      <div class="container container-max-width">
        <div class="row">
          <div class="col-md-12">
            <div class="sideBannerImg">
              <a href="<?= site_url('category/vitamins'); ?>"> 
              <img id="vitamins-banner-1" loading="lazy" src="<?= base_url('assets/images/banners/Vitamins_Banner_2024-04-16.jpg'.'?timestamp='.time()); ?>" alt="Vitamins" class="w-100 h-100 " > </a>
            </div>
          </div>
          <div class="col-md-12 mt-3">
            <div class="sideBannerImg">
              <a href="<?= site_url('category/beauty1'); ?>"> <img id="makeup-banner-1" loading="lazy" src="<?= base_url('assets/images/banners/Makeup_Banner_2024-04-16.jpg'.'?timestamp='.time()); ?>" alt="Makeup" class="w-100  " ></a>
            </div>
          </div>
        </div>
        
      </div>
                  </div>



    </section>

     <!-- banner area 1 -->


    <!-- banner area 1 end -->

    <!-- feature product   -->
    <section class="popularCat-container section-marg-top">
      <div class="container container-max-width">
        <div class="featureProductC"> 
          <div class="popularTitle text-center"><h2 class="title-wrapper">Feature Products </h2></div>

          
          <div class="feature_products margin-minus ">

          <?php
            $r = 0;
            foreach (array_chunk($featured_products, 4) as $sps){
                foreach ($sps as $sp) {
                ?>
               
                    <div class=" products-card  text-center ">
                 
                        <div class="card" style="width: 100%">
                            
                            <div class="cardImg position-relative">
                            <?php 
                              if($sp->promotion && $sp->price > 0 && $sp->promo_price > 0){
                                ?>
                                  <span class="position-absolute badge rounded-pill bg-danger" style="top:0px;left:0px;font-size:10px">
                                    <?php echo round((($sp->price - $sp->promo_price) / $sp->price) * 100); ?>% OFF
                                </span>
                                <?php
                              }

                              if($sp->global == 1){
                                ?>
                                  <!--<span class="position-absolute badge rounded-pill bg-info" style="top:0px;right:0px;font-size:10px">
                                    Global
                                  </span>-->
                                  <span class="position-absolute badge" style="top:0px;right:0px;width: 90px;">
                                    <img src="<?= base_url('assets/images/global.jpg'); ?>" style="height:20px;" class="card-img-top" alt="Global">
                                  </span>
                                <?php
                              }
                            ?>
                            <a href="<?= site_url('product/' . $sp->slug); ?>" class="text-decoration-none">
                            <img src="<?= base_url('assets/uploads/' . $sp->image); ?>" class="card-img-top" alt="<?= $sp->name; ?>">
                            </a>
                            </div>
                            <div class="card-body px-0 text-start pb-0">
                            
                            <a href="<?= site_url('product/' . $sp->slug); ?>" class="text-decoration-none"><h5 class="card-title text-start"><?= stripslashes($sp->name); ?></h5></a>
                            <div class="d-flex align-items-center justify-content-between">
                                
                                <div class="rating">
                                  <?php 
                                        for($i=1; $i<=5; $i++) {
                                          $class = '';
                                          if($i<=$sp->avg_rating) {$class = 'rated';}?>
                                  <i class="bi bi-star-fill <?php echo $class;?>" ></i>
                                  <?php }?>
                                </div>
                              
                                <?php
                                if ($sp->promotion) {
                                    ?>
                                 
                                        <div class="discountPrice price text-end py-2">
                                            <h4 class="m-0 text-decoration-line-through">
                                                <?php echo $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price); ?>
                                            </h4>
                                        </div>
                                
                                    <?php
                                }
                                ?>
                                
                            </div> 
                            <!--price and quantity araea  -->

                            <div class="d-flex align-items-center justify-content-between">
                          
                                    <div class="price text-start  py-2">
                                        <h4 class="m-0 fw-bold">
                                            <?php
                                            if ($sp->promotion) {
                                                echo $this->sma->convertMoney($sp->promo_price);
                                            }else{
                                                echo $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price);
                                            }
                                            ?>
                                        </h4>
                                    </div>
                              
                    
                                <div class="quantity text-end py-2 d-flex align-items-center justify-content-md-between">
                                    <span class="plus btn-plus"><i class="bi bi-plus-circle-fill"></i></span>
                                    <input type="text" name="quantity" readonly class="Qnum" value="1" required="required" />
                                    <!--<span class="Qnum ">1</span>-->
                                    <span class="minus btn-minus"><i class="bi bi-dash-circle-fill"></i></span>
                                </div>
                             
                            </div>
                            
                            <!-- price area end -->
                            
                                
                            </div>
                            
                            <div> 
                              <?php 
                                  if($sp->product_quantity > 0){
                                      ?>
                                          <button type="button" data-id="<?= $sp->id; ?>" aria-controls="offcanvasWithBothOptions" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark add-to-cart">Add to cart </button>
                                      <?php
                                  }else{
                                      $notify_price = $sp->promotion == 1 ? $sp->promo_price : $sp->price;
                                      ?>
                                          Out of Stock
                                          <button type="button" class="btn btn-link btn-notify-add-to-list" href="#" data-id="<?= $sp->id; ?>" data-title="<?= $sp->name; ?>" data-image="<?= $sp->image; ?>" data-price="<?= $notify_price; ?>" >Notify me</button>
                                      <?php
                                  }
                              ?>
                              
                            </div>
                        </div> 
                    
                        
                    </div>
                


                <?php 
                }
            } 
            ?> 
                
            </div>
          </div>
        </div>
      </div>
     </section>
    

    <!-- best seller product   -->
    <section class="popularCat-container section-marg-top" >
      <div class="container container-max-width">

        <div class="featureProductC ">
          <div class="popularTitle text-center"><h2 class="title-wrapper">Best Sellers</h2></div>

          
          <div class="feature_products margin-minus ">

          <?php
            $r = 0;
            foreach (array_chunk($best_sellers, 4) as $sps){
                foreach ($sps as $sp) {
                ?>
                    <div class=" products-card text-center ">
                    
                        <div class="card" style="width: 100%">
                            
                            <div class="cardImg position-relative">
                            <?php 
                              if($sp->promotion && $sp->price > 0 && $sp->promo_price > 0){
                                ?>
                                  <span class="position-absolute badge rounded-pill bg-danger" style="top:0px;left:0px;font-size:10px">
                                    <?php echo round((($sp->price - $sp->promo_price) / $sp->price) * 100); ?>% OFF
                                </span>
                                <?php
                              }

                              if($sp->global == 1){
                                ?>
                                  <!--<span class="position-absolute badge rounded-pill bg-info" style="top:0px;right:0px;font-size:10px">
                                    Global
                                  </span>-->
                                  <span class="position-absolute badge" style="top:0px;right:0px;width: 90px;">
                                    <img src="<?= base_url('assets/images/global.jpg'); ?>" style="height:20px;" class="card-img-top" alt="Global">
                                  </span>
                                <?php
                              }
                            ?>
                            <a href="<?= site_url('product/' . $sp->slug); ?>" class="text-decoration-none">
                            <img src="<?= base_url('assets/uploads/' . $sp->image); ?>" class="card-img-top" alt="<?= $sp->name; ?>">
                            </a>
                            </div>
                            <div class="card-body px-0 text-start pb-0">
                            
                            <a href="<?= site_url('product/' . $sp->slug); ?>" class="text-decoration-none"><h5 class="card-title text-start"><?= stripslashes($sp->name); ?></h5></a>
                            <div class="d-flex  rating-cont align-items-center justify-content-between">
                            
                                <div class="rating">
                                  <?php 
                                        for($i=1; $i<=5; $i++) {
                                          $class = '';
                                          if($i<=$sp->avg_rating) {$class = 'rated';}?>
                                  <i class="bi bi-star-fill <?php echo $class;?>" ></i>
                                  <?php }?>
                                </div>
                                <div class="discountPrice price text-end py-2">
                                <h4 class="m-0 text-decoration-line-through">
                                <?php
                                if ($sp->promotion) {
                                    ?>
                                 
                                        
                                                <?php echo $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price); ?>
                                            
                                 
                                    <?php
                                }
                                ?>
                                </h4>
                                 </div>
                            </div> 
                            <!--price and quantity araea  -->

                            <div class="d-flex align-items-center justify-content-between">
                               
                                    <div class="price text-start  py-2">
                                        <h4 class="m-0 fw-bold">
                                            <?php
                                            if ($sp->promotion) {
                                                echo $this->sma->convertMoney($sp->promo_price);
                                            }else{
                                                echo $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price);
                                            }
                                            ?>
                                        </h4>
                                    </div>
                             
                            
                                <div class="quantity text-end py-2 d-flex align-items-center justify-content-md-between">
                                    <span class="plus btn-plus"><i class="bi bi-plus-circle-fill"></i></span>
                                    <input type="text" name="quantity" readonly class="Qnum" value="1" required="required" />
                                    <!--<span class="Qnum ">1</span>-->
                                    <span class="minus btn-minus"><i class="bi bi-dash-circle-fill"></i></span>
                                </div>
                            
                            </div>
                            
                            <!-- price area end -->
                            
                                
                            </div>
                            
                            <div> 
                              <?php 
                                  if($sp->product_quantity > 0){
                                      ?>
                                          <button type="button" data-id="<?= $sp->id; ?>" aria-controls="offcanvasWithBothOptions" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark add-to-cart">Add to cart </button>
                                      <?php
                                  }else{
                                      $notify_price = $sp->promotion == 1 ? $sp->promo_price : $sp->price;
                                      ?>
                                          Out of Stock
                                          <button type="button" class="btn btn-link btn-notify-add-to-list" href="#" data-id="<?= $sp->id; ?>" data-title="<?= $sp->name; ?>" data-image="<?= $sp->image; ?>" data-price="<?= $notify_price; ?>" >Notify me</button>
                                      <?php
                                  }
                              ?>
                              
                            </div>
                        </div> 
                   
                        
                    </div>
                <?php 
                }
            } 
            ?> 
                
            </div>

            <!-- Additional Best Sellers -->
            <div class="feature_products margin-minus">

          <?php
            $r = 0;
            foreach (array_chunk($best_sellers_additional, 4) as $sps){
                foreach ($sps as $sp) {
                ?>
                    <div class="  products-card text-center mt-4 ">
                 
                        <div class="card" style="width: 100%">
                            
                            <div class="cardImg position-relative">
                            <?php 
                              if($sp->promotion && $sp->price > 0 && $sp->promo_price > 0){
                                ?>
                                  <span class="position-absolute badge rounded-pill bg-danger" style="top:0px;left:0px;font-size:10px">
                                    <?php echo round((($sp->price - $sp->promo_price) / $sp->price) * 100); ?>% OFF
                                </span>
                                <?php
                              }

                              if($sp->global == 1){
                                ?>
                                  <!--<span class="position-absolute badge rounded-pill bg-info" style="top:0px;right:0px;font-size:10px">
                                    Global
                                  </span>-->
                                  <span class="position-absolute badge" style="top:0px;right:0px;width: 90px;">
                                    <img src="<?= base_url('assets/images/global.jpg'); ?>" style="height:20px;" class="card-img-top" alt="Global">
                                  </span>
                                <?php
                              }
                            ?>
                            <a href="<?= site_url('product/' . $sp->slug); ?>" class="text-decoration-none">
                            <img src="<?= base_url('assets/uploads/' . $sp->image); ?>" class="card-img-top" alt="<?= $sp->name; ?>">
                            </a>
                            </div>
                            <div class="card-body px-0 text-start pb-0">
                            
                            <a href="<?= site_url('product/' . $sp->slug); ?>" class="text-decoration-none"><h5 class="card-title text-start"><?= stripslashes($sp->name); ?></h5></a>
                            <div class="d-flex rating-cont align-items-center justify-content-between">
                           
                                <div class="rating">
                                  <?php 
                                        for($i=1; $i<=5; $i++) {
                                          $class = '';
                                          if($i<=$sp->avg_rating) {$class = 'rated';}?>
                                  <i class="bi bi-star-fill <?php echo $class;?>" ></i>
                                  <?php }?>
                                </div>
                              
                                <?php
                                if ($sp->promotion) {
                                    ?>
                                 
                                        <div class="discountPrice price text-end py-2">
                                            <h4 class="m-0 text-decoration-line-through">
                                                <?php echo $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price); ?>
                                            </h4>
                                        </div>
                              
                                    <?php
                                }
                                ?>
                                
                            </div> 
                            <!--price and quantity araea  -->

                            <div class="d-flex align-items-center justify-content-between">
                               
                                    <div class="price text-start  py-2">
                                        <h4 class="m-0 fw-bold">
                                            <?php
                                            if ($sp->promotion) {
                                                echo $this->sma->convertMoney($sp->promo_price);
                                            }else{
                                                echo $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price);
                                            }
                                            ?>
                                        </h4>
                                    </div>
                              
                            
                                <div class="quantity text-end py-2 d-flex align-items-center justify-content-md-between">
                                    <span class="plus btn-plus"><i class="bi bi-plus-circle-fill"></i></span>
                                    <input type="text" name="quantity" readonly class="Qnum" value="1" required="required" />
                                    <!--<span class="Qnum ">1</span>-->
                                    <span class="minus btn-minus"><i class="bi bi-dash-circle-fill"></i></span>
                                </div>
                          
                            </div>
                            
                            <!-- price area end -->
                            
                                
                            </div>
                            
                            <div> 
                              <?php 
                                  if($sp->product_quantity > 0){
                                      ?>
                                          <button type="button" data-id="<?= $sp->id; ?>" aria-controls="offcanvasWithBothOptions" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark add-to-cart">Add to cart </button>
                                      <?php
                                  }else{
                                      $notify_price = $sp->promotion == 1 ? $sp->promo_price : $sp->price;
                                      ?>
                                          Out of Stock
                                          <button type="button" class="btn btn-link btn-notify-add-to-list" href="#" data-id="<?= $sp->id; ?>" data-title="<?= $sp->name; ?>" data-image="<?= $sp->image; ?>" data-price="<?= $notify_price; ?>" >Notify me</button>
                                      <?php
                                  }
                              ?>
                              
                            </div>
                        </div> 
                     
                        
                    </div>
                <?php 
                }
            } 
            ?> 
                
            </div>

          </div>
        </div>
     
      </div>
     </section>
     <!-- best seller products  end-->

     <!--banner cat  -->
     <section class="side-banner section-marg-top">
      <div class="container container-max-width">
        <div class="row">
          <div class="col-md-6">
            <div class="sideBannerImg my-2">
             <a href="<?= site_url('category/mombaby'); ?>"> <img id="mombaby-banner-1" loading="lazy" src="<?= base_url('assets/images/banners/momBaby.jpg'.'?timestamp='.time()); ?>" alt="Mom Baby Promotion" class="w-100 h-100 rounded-4" ></a>
            </div>
          </div>
          <div class="col-md-6">
            <div class="sideBannerImg my-2">
              <a href="<?= site_url('category/personal-care'); ?>"> <img id="perc-banner-1" loading="lazy" src="<?= base_url('assets/images/banners/persC.jpg'.'?timestamp='.time()); ?>" alt="Personal Care Promotion" class="w-100  rounded-4" ></a>
            </div>
          </div>
        </div>
        
      </div>
    </section>
    <!-- banner cat end -->



    <!-- special offer -->
  
    <section class="specialOffer-container section-marg-top">
      <div class="container container-max-width">
        <div class="specialOfferProductC">
          <div class="specialOfferpopularTitle text-center"><h2 class="title-wrapper">Special Offer</h2></div>
        </div>

        <!-- special products -->
        <div class="special_products margin-minus special_products-cont">
            <?php
                $r = 0;
                foreach (array_chunk($special_offers, 4) as $sps){
                    foreach ($sps as $sp) {
                    ?>
                        <div class=" specialOfferP get-quantity">
                            <div class=" card">
                            <div class="row align-items-center">
                                <div class="col-md-5 col-sm-12">
                    
                                <div class="cardImg rounded-3">
                                    <?php 
                                      if($sp->promotion && $sp->price > 0 && $sp->promo_price > 0){
                                        ?>
                                          <span class="position-absolute badge rounded-pill bg-danger" style="top:8px;left:8px;font-size:10px">
                                            <?php echo round((($sp->price - $sp->promo_price) / $sp->price) * 100); ?>% OFF
                                        </span>
                                        <?php
                                      }

                                      if($sp->global == 1){
                                        ?>
                                          <!--<span class="position-absolute badge rounded-pill bg-info" style="top:0px;right:0px;font-size:10px">
                                            Global
                                          </span>-->
                                          <span class="position-absolute badge" style="top:0px;right:0px;width: 90px;">
                                            <img src="<?= base_url('assets/images/global.jpg'); ?>" style="height:20px;" class="card-img-top" alt="Global">
                                          </span>
                                        <?php
                                      }
                                    ?>
                                    <a href="<?= site_url('product/' . $sp->slug); ?>"><img src="<?= base_url('assets/uploads/' . $sp->image); ?>" class="card-img-top rounded-3" alt="<?= $sp->name; ?>"></a>
                                </div>
                                </div>
                                <div class="col-md-7 col-sm-12 px-md-0">
                                <div class="card-body px-md-0 text-start pb-0">
                                    <div class="product-cat-title"><span class="text-uppercase"><?= $sp->category_name; ?></span></div>
                                    <a style="text-decoration: none;" href="<?= site_url('product/' . $sp->slug); ?>"><h5 class="card-title text-start"><?= stripslashes($sp->name); ?></h5></a>
                                    <div class="row align-items-center justify-content-between">
                                    
                                    
                                    </div> 
                                    <!--price and quantity araea  -->

                                    <div class="d-flex align-items-center justify-content-between w-100">
                                 
                                        <div class="price text-start  py-2">
                                            <h4 class="m-0 fw-bold">
                                            <?php
                                                if ($sp->promotion) {
                                                    //echo '<del class="text-red">' . $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price) . '</del><br>';
                                                    echo $this->sma->convertMoney($sp->promo_price);
                                                } else {
                                                    echo $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price);
                                                } 
                                            ?>
                                            </h4>
                                        </div>
                                   
                                
                                        <div class="quantity text-end py-2 d-flex align-items-center">
                                        <span class="plus btn-plus"><i class="bi bi-plus-circle-fill"></i></span>
                                        <input type="text" name="quantity" readonly class="Qnum" value="1" required="required" />
                                        <!--<span class="Qnum ">1</span>-->
                                        <span class="minus btn-minus"><i class="bi bi-dash-circle-fill"></i></span>
                                        </div>
                                 
                                    </div>
                                    <div> 
                                      
                                      <?php 
                                        if($sp->product_quantity > 0){
                                            ?>
                                                <button type="button" data-id="<?= $sp->id; ?>" aria-controls="offcanvasWithBothOptions" class="btn primary-buttonAV mt-1 py-1 addtocart w-100 text-dark add-to-cart">Add to cart </button>
                                            <?php
                                        }else{
                                            $notify_price = $sp->promotion == 1 ? $sp->promo_price : $sp->price;
                                            ?>
                                                Out of Stock
                                                <button type="button" class="btn btn-link btn-notify-add-to-list" href="#" data-id="<?= $sp->id; ?>" data-title="<?= $sp->name; ?>" data-image="<?= $sp->image; ?>" data-price="<?= $notify_price; ?>" >Notify me</button>
                                            <?php
                                        }
                                      ?>
                                    </div>
                                    <!-- price area end -->
                                    
                                    
                                </div>
                                </div>
                                
                            </div>

                            </div>
                            
                        </div>
                    <?php
                    }
                }
            ?>
        </div>
        <!-- special products end -->
      </div>
    </section>
    <!-- special offer end -->

    <!-- widgets section -->
    <section class="widgets widgets-wrapper section-marg-top">
      <div class="container container-max-width">
        <div class="d-flex suprtwidget margin-minus ">
          <div class="item">
            <div class="card text-center " >
              <h3 class="pt-2 fw-bolder"><i class="bi bi-telephone-forward-fill"></i></h3>
              <div class="card-body">
                <h5 class="card-title"><b>Help & Support</b></h5>
                <p class="card-text">For inquires and support. Please contact us.</p>
              
              </div>
            </div>
          </div>

          <div class="item ">
            <div class="card text-center ">
              <h3 class="pt-2"><i class="bi bi-box-arrow-up-right"></i></h3>
              <div class="card-body">
                <h5 class="card-title"><b>Easy Return</b></h5>
                <p class="card-text">You can easily return your newly purchased <br />products within 14 days.</p>
              
              </div>
            </div>
          </div>

          <div class="item">
            <div class="card text-center" >
              <h3 class="pt-2"><i class="bi bi-wallet"></i></h3>
              <div class="card-body">
                <h5 class="card-title"><b>Payments</b></h5>
                <p class="card-text">We provides secure and multiple method of payments to customers. VISA, MasterCard, Mada and Apple Pay.</p>
              
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- brand logos -->
    <section class="widgets section-marg-top brands-logo-cont">
      <div class="container container-max-width">
        <div class="widget-bar">
          <div class="brands-logo margin-minus">
            <div><img src="<?= base_url('assets/images/banners/ceraVe.jpg'.'?timestamp='.time()); ?>" alt="Cerave Brand" class="w-100"> </div>
            <div><img src="<?= base_url('assets/images/banners/Ghalior-Paris.jpg'.'?timestamp='.time()); ?>" alt="Ghalior Paris Brand" class="w-100"> </div>
            <div><img src="<?= base_url('assets/images/banners/Jamieson.jpg'.'?timestamp='.time()); ?>" alt="Jamieson Brand" class="w-100"> </div>
            <div><img src="<?= base_url('assets/images/banners/Johnsons.jpg'.'?timestamp='.time()); ?>" alt="Johnsons Brand" class="w-100"> </div>
            <div><img src="<?= base_url('assets/images/banners/Laperva.jpg'.'?timestamp='.time()); ?>" alt="Laperva Brand" class="w-100"> </div>
            <div><img src="<?= base_url('assets/images/banners/Purever-CANADA.jpg'.'?timestamp='.time()); ?>" alt="Purever Brand" class="w-100"> </div>
          </div>
        </div>
      </div>
    </section>
<!-- join container -->
    <section class="join-container section-marg-top mb-3" >
      <div class="container container-max-width">
      <div class=" p-5 border-radius-10 news-letter-container" style="background-image: url(<?= base_url('assets/images/banners/bgbanner.jpg'); ?>);">
        <div class="text-center">
          <h2>Join our newsletter</h2>
          <p>Join our newsletter and get latest deals, articles, and resources!</p>
          <form class="d-flex search-bar w-50 mx-auto" role="search">
              
            
              <input class="form-control border-0 bg-white py-3 ps-5" id="newsletterEmail" type="search" placeholder="Subscribe to our newsletter?" aria-label="Search">
              <button class="btn searchsubmitBtn" id="newsletterSubscribe" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
      </div>
    </div>
    </section>
    <!-- join section end -->
