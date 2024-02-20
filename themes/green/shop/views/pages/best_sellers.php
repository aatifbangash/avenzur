<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!-- products start -->
<section class="products">
    <div class="container container-max-width py-3">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Products</li>
            </ol>
        </nav>
        <div class="row  ">
            <div class="col-xl-2  col-lg-3">
                
                <!-- side bar left -->
                <button class="btn btn-primary d-lg-none catsidebarmob" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasProducts" aria-controls="offcanvasProducts"><i class="bi bi-sort-down-alt"></i></button>

                
                
                <div class="offcanvas-lg offcanvas-end" tabindex="-1" id="offcanvasProducts" aria-labelledby="offcanvasProductsLabel">
                    <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasProductsLabel">Categories</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasProducts" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <div class="categ w-100">
                        <h5 class="fw-semibold">Categories</h5>
                        <div class="list-group catList">
                            <?php
                                foreach($categories as $cat)
                                {
                                    ?>
                                        <a href="<?= site_url('category/' . $cat->slug); ?>" class="list-group-item list-group-item-action <?php if($category_slug == $cat->slug) { echo 'active'; } ?>" aria-current="true">
                                        <?= ucfirst(strtolower($cat->name)); ?>
                                        </a>
                                    <?php
                                }
                            ?>
                        </div>
                        </div>
                    </div>
                </div>
                <!-- side bar end -->
            </div>
            <div class="col-xl-10 col-lg-9">

                <!-- all products -->
                <div class="row products-card text-center gy-4 pb-4">
                <h1 style="font-size: 22px; font-weight: bold; color: #000; text-align:right;"><?php echo $page_title; ?></h1>             
                <?php
                    $r = 0;
                    foreach (array_chunk($best_sellers, 4) as $sps){
                        foreach ($sps as $sp) {
                            ?>
                              <div class="col-xl-3 col-lg-4 col-md-6 col-6 product-cards-cont product-cards-wrapper">  
                                        <div class="card" style="width: 100%">
                                        <a href="<?= site_url('product/' . $sp->slug); ?>" class="text-decoration-none">
                                        <div class="cardImg"><img src="<?= base_url('assets/uploads/thumbs/' . $sp->image); ?>" class="card-img-top" alt="..."></div>
                                        <div class="card-body px-0 text-start pb-0">
                                            <div class="product-cat-title"><span class="text-uppercase">Medical</span></div>
                                            <h5 class="card-title text-start"><?= $sp->name; ?></h5>
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
                                            
                                         
                                                <div class="quantity text-end py-2 d-flex align-items-center justify-content-between">
                                                <span class="plus btn-plus"><i class="bi bi-plus-circle-fill"></i></span>
                                                <input type="text" name="quantity" class="Qnum" value="1" required="required" />
                                                <!--<span class="Qnum ">1</span>-->
                                                <span class="minus btn-minus"><i class="bi bi-dash-circle-fill"></i></span>
                                                </div>
                                         
                                            </div>
                                        
                                            </div>
                                        </a>
                                        <div> <button type="button" data-id="<?= $sp->id; ?>" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark add-to-cart">Add to cart </button></div>
                                    </div>
                                </div>
                            <?php
                        }
                    }
                ?>
                 
                                       
                </div>
                <!-- all products end -->
            </div>
        </div>
    </div>
</section>

<!-- products end -->

