<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- hero silder -->
<section class="heroSlider">
      <div class="container container-max-width py-4">
        <div id="carouselExampleIndicators" class="carousel slide">
          <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
           
          </div>

          <div class="carousel-inner">
            <?php
                $sr = 0;
                foreach ($slider as $slide) {
                    if (!empty($slide->image)) {
                        ?>
                            <div class="carousel-item <?= ($sr == 0 ? ' active' : ''); ?>">
                                <a href="<?php echo $slide->link; ?>">
                                    <img src="<?= base_url('assets/uploads/' . $slide->image); ?>" class="d-block w-100" alt="...">
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
    <section>
      <div class="container container-max-width py-4">
       <div class="featureTitle text-center mb-5"><h2>Featured Categories</h2></div>
       <!-- cards -->
       <div class="row feature-cards text-center gy-4 ">
            <?php
                $r = 0;
                foreach (array_chunk($featured_categories, 8) as $fps) {
                    foreach ($fps as $fp) {
                    ?>
                        <div class="col-md-2 col-sm-12 col-6">
                            <a href="<?= site_url('category/'.$fp->slug) ?>" class="text-decoration-none">
                            
                                <div class="card" style="width: 100%;">
                                <div class="cardImg">
                                
                                    <img src="<?= base_url('assets/uploads/' . $fp->image); ?>" class="card-img-top" alt="..."></div>
                                <div class="card-body">
                                    <h5 class="card-title"><?= $fp->name; ?></h5>
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

    <!-- boom categores -->
    <section class="boom-categories">
      <div class="container container-max-width py-4">
        <div class="row">

          <div class="col-lg-8 col-md-12 col-sm-12">
            
            <div class="row ">

              <div class="col-lg-8 col-md-7 col-sm-12">
                <div class="boom-product-cat py-4 px-5" style="background-image: url(<?= base_url('assets/images/banners/boom1.png'); ?>)">
                  <div class="row align-items-center">
                    <div class="col-md-5">

                      <img src="<?= base_url('assets/images/banners/boomtab1.png'); ?>" alt=""  class="w-100"/>
                    </div>
                    <div class="col-lg-7 col-md-6">
                      <p class="m-0 py-2"><span class="boom-parag">Get it now 45% OFF</span></p>
                      <span class="btitle py-3"><span style="font-weight: bold;">Pyridoxine</span> Vitamin B6</span>
                      <button type="button" class="btn primary-buttonAV mt-4 pt-1">Buy now <i class="bi bi-chevron-right ms-1"></i></button>

                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-4 col-md-5 col-sm-12">
                <div class="boom-product-cat py-4 px-5" style="background-image: url(<?= base_url('assets/images/banners/boom2.png'); ?>)">
                  <div class="row align-items-center">
                   
                    <div class="col-md-12">
                      <p class="m-0 py-2"><span class="boom-parag">Get it now 45% OFF</span></p>
                      <span class="btitle py-3"><span style="font-weight: bold;">Pyridoxine</span> Vitamin B6</span>
                      <button type="button" class="btn primary-buttonAV mt-3 pt-1">Buy now <i class="bi bi-chevron-right ms-1s"></i></button>

                    </div>
                    <div class="col-md-12">

                      <img src="<?= base_url('assets/images/banners/tabpack.png'); ?>" alt=""  class="w-100"/>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- boom-sectond row -->
            <div class="row mt-4">

              <div class="col-md-5 col-sm-12">
                <div class="boom-product-cat py-4 px-4" style="background-image: url(<?= base_url('assets/images/banners/boom4.png'); ?>)">
                  <div class="row ">
                   
                    <div class="col-md-6 pe-md-0">
                      <p class="m-0 py-2"><span class="boom-parag">Get it now 45% OFF</span></p>
                      <span class="btitle py-3"><span style="font-weight: bold;">Pyridoxine</span> Vitamin B6</span>
                      <button type="button" class="btn primary-buttonAV mt-3 pt-1">Buy now <i class="bi bi-chevron-right ms-1s"></i></button>

                    </div>
                    <div class="col-md-6 p-0">

                      <img src="<?= base_url('assets/images/banners/tabpack3.png'); ?>" alt=""  class="w-100"/>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-7 col-sm-12">
                <div class="boom-product-cat py-4 px-5" style="background-image: url(<?= base_url('assets/images/banners/boom5.png'); ?>)">
                  <div class="row align-items-center">
                   
                    <div class="col-md-6 p-md-0 boomsale">
                      <span class="bigsale">Big Sale 65% OFF</span>
                      <p class="m-0 py-2"><span class="boom-parag">Get it now 45% OFF</span></p>
                      <span class="btitle "><span style="font-weight: bold;">Pyridoxine</span> Vitamin B6, A1, C</span>
                      <button type="button" class="btn primary-buttonAV mt-3 pt-1">Buy now <i class="bi bi-chevron-right ms-1"></i></button>

                    </div>
                    <div class="col-md-6">

                      <img src="<?= base_url('assets/images/banners/tabpack5.png'); ?>" alt=""  class="w-100"/>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-12 col-sm-12  pt-lg-0 pt-md-4">
            <div class="boom-product-cat py-4 px-5" style="background-image: url('<?= base_url('assets/images/banners/boom3.png'); ?>')">
              <div class="row align-items-center">
               
                <div class="col-md-12 mb-4">
                  <span class="bigsale">Big Sale 65% OFF</span>
                  <p class="m-0 py-2"><span class="boom-parag">Get it now 45% OFF</span></p>
                  <span class="btitle py-3"><span style="font-weight: bold;">Pyridoxine</span> Vitamin B6</span>
                  <button type="button" class="btn primary-buttonAV mt-4 pt-1">Buy now <i class="bi bi-chevron-right ms-1s"></i></button>

                </div>
                <div class="col-md-12">

                  <img src="<?= base_url('assets/images/banners/tabpack2.png'); ?>" alt=""  class="w-100"/>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
    <!-- boom categores end -->

    <!-- banner area 1 -->
    <section class="side-banner py-3 ">
      <div class="container container-max-width">
        <div class="sideBannerImg">
          <img src="<?= base_url('assets/images/banners/side-banner.jpg'); ?>" alt="placeholder" class="w-100" >
        </div>
      </div>
    </section>
    <!-- banner area 1 end -->

    <!-- skin container area -->
    <section class="skin-container py-3 ">
      <div class="container container-max-width ">
        <div class=" row skinBannerRow  rounded-4 align-items-center justify-content-between">
          <div class="col-lg-6 col-md-12 col-sm-12 ps-0 skinbnnerimg">
            <a href="<?= site_url('category/skn'); ?>"  >
                <img src="<?= base_url('assets/images/banners/skinbanner.jpg'); ?>" alt="placeholder" class="w-100    rounded-start-3 rounded-end-5" />
            </a>
          </div>

          <div class="col-lg-5 col-md-12 col-sm-12 pt-lg-0 pt-md-3">
            <div class="px-4">
              <div class="skinareaTitle"><h2 class="fw-bold">Beauty starts from inside</h2></div>
              <div class="skinareatext"><p class="m-0 py-2">Discover our specially formulated products that helps you shine from inside out.</p></div>
              <button type="button" class="btn primary-buttonAV my-3 py-2 discoverbtn">Discover <i class="bi bi-chevron-right ms-1"></i></button>
            </div>
          </div>
         
        </div>
      </div>
    </section>
    <!-- skin container area end -->

    <section class="popularCat-container py-3">
        <div class="container container-max-width">
            <div class="categoryTabs pt-4">
                <div class="popularTitle text-center mb-4"><h2>Popular Categories</h2></div>
                <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
                    <?php
                    $pp = 0;
                    foreach($popular_categories as $popular_category){
                    ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php if($pp == 0) echo 'active'; ?>" id="pills-<?= $popular_category->name; ?>-tab" data-bs-toggle="pill" data-bs-target="#pills-<?= $popular_category->name; ?>" type="button" role="tab" aria-controls="pills-<?= $popular_category->name; ?>" aria-selected="true"><?= $popular_category->name; ?></button>
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
                        <div class="tab-pane fade show <?php if($pc == 0) echo 'active'; ?>" id="pills-<?= $popular_category->name; ?>" role="tabpanel" aria-labelledby="pills-<?= $popular_category->name; ?>-tab" tabindex="0">
                            <!-- cards -->
                            <div class="row products-card text-center gy-4">
                                <?php
                                foreach($popular_category->products as $popular_product){
                                ?>
                                    <div class="col-lg-3 col-md-4 col-sm-12">
                                        <div class="card" style="width: 100%">
                                            <!--<a href="#" class="text-decoration-none">-->
                                                <div class="cardImg position-relative">
                                                    <!--<span class="position-absolute   badge rounded-pill bg-danger" style="top:20px;left:10px;font-size:14px">
                                                        Sale 20% OFF
                                                    </span>-->
                                                    <a href="<?= site_url('product/' . $popular_product->slug); ?>" class="text-decoration-none">
                                                        <img src="<?= base_url('assets/uploads/' . $popular_product->image); ?>" class="card-img-top" alt="...">
                                                    </a>
                                                </div>
                                                <div class="card-body px-0 text-start pb-0">
                                                    <div class="product-cat-title">
                                                        <span class="text-uppercase"><?= $popular_category->name; ?></span>
                                                    </div>
                                                    <a href="<?= site_url('product/' . $popular_product->slug); ?>" class="text-decoration-none">
                                                        <h5 class="card-title text-start"><?= $popular_product->name; ?></h5>
                                                    </a>
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-md-6">
                                                            <div class="rating">
                                                                <i class="bi bi-star-fill rated"></i>
                                                                <i class="bi bi-star-fill rated"></i>
                                                                <i class="bi bi-star-fill rated"></i>
                                                                <i class="bi bi-star-fill"></i>
                                                            </div>
                                                        </div>
                                                        <?php
                                                            if ($popular_product->promotion) {
                                                                ?>
                                                                    <div class="col-md-6">
                                                                        <div class="discountPrice price text-end py-2">
                                                                            <h4 class="m-0 text-decoration-line-through">
                                                                                <?= $this->sma->convertMoney(isset($popular_product->special_price) && !empty(isset($popular_product->special_price)) ? $popular_product->special_price : $popular_product->price); ?>
                                                                            </h4>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                
                                                            }
                                                        ?>
                                                    </div> 
                                                    <div class="row align-items-center justify-content-between">
                                                        <div class="col-md-6 ">
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
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="quantity text-end py-2 d-flex align-items-center justify-content-between">
                                                                <span class="plus btn-plus">
                                                                    <i class="bi bi-plus-circle-fill"></i>
                                                                </span>
                                                                <input type="text" name="quantity" class="Qnum" value="1" required="required" />
                                                                <!--<span class="Qnum ">1</span>-->
                                                                <span class="minus btn-minus"><i class="bi bi-dash-circle-fill"></i></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <!--</a>-->
                                            <div> 
                                                <button type="button" data-id="<?= $popular_product->id; ?>" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark add-to-cart">Add to cart </button>
                                            </div>
                                        </div> 
                                    </div>
                                <? } ?>
                            </div>
                        </div>
                    <? 
                    $pc++;
                    } ?>
                </div>
            </div>
        </div>
    </section>

     
