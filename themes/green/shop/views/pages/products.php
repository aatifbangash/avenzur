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
            <div class="col-xl-2 col-lg-3">
                
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
                        
                        
                        <!--<hr>
                            <div class="py-3">
                            <div>
                                <label for="customRange3" class="form-label"><h5>Price</h5></label>
                                <input type="range" class="form-range" min="0" max="5" step="0.5" id="customRange3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="input-group ">
                                        
                                        <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default" value="0">
                                        </div><div class="px-2"> to</div>
                                        <div class="input-group ">
                                        
                                        <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default" value="100">
                                        </div>
                                </div>
                            </div>
                            </div>-->

                            <!--<div class="py-3">
                                <h5>Brands</h5>
                                <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                <label class="form-check-label" for="flexCheckDefault">
                                    <h6 class="fw-bold">Beatswell</h6>
                                </label>
                                </div>
                                <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckChecked" checked>
                                <label class="form-check-label" for="flexCheckChecked">
                                    <h6 class="fw-bold">Manukora</h6>
                                </label>
                                </div>
                            </div>-->
                        </div>
                    </div>
                </div>
                <!-- side bar end -->
            </div>
            <div class="col-xl-10 col-lg-9 ">
                <div class="row justify-content-between align-items-center" style="text-align: right">
                    <h1 style="font-size: 22px; font-weight: bold; color: #000;"><?php echo $page_title2; ?></h1>

                    <?php 
                        if($promo_banner){
                            ?>
                                <section class="side-banner section-marg-top" style="margin-top:10px;margin-bottom: 40px;">
                                <div class="container container-max-width">
                                    <div class="sideBannerImg">
                                    <a href="<?= site_url('shop/products?promo=yes'); ?>">
                                        <img id="promo-page-banner-1" loading="lazy" src="<?= base_url('assets/images/banners/promo-page-banner-en.jpg'.'?timestamp='.time()); ?>" alt="placeholder" class="w-100" />
                                    </a>
                                    </div>
                                </div>
                                </section>
                            <?php
                        }
                    ?>
                    <!--<div class="col-md-6 col-6">
                        <div class="dropdown sortp">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Default sorting <i class="bi bi-chevron-down ps-2"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Sort by Price: low to heigh</a></li>
                                <li><a class="dropdown-item" href="#">Sort by Price: heigh to low</a></li>
                                
                            </ul>
                            </div>
                    </div>
                    <div class="col-md-6 col-6 ">
                        <div class="form-check ms-auto " style="width: fit-content;">
                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                            <label class="form-check-label" for="flexCheckDefault">
                                only product on sale
                            </label>
                        </div>
                    </div>-->
                </div>
    <!-- all products -->

    <div id="results" class="row products-card text-center SS gy-4 pb-4">

    </div>
    <!-- all products end -->
    </div>
    </div>
    </div>
</section>

<!-- products end -->


<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">

                    <div class="col-sm-3 col-md-2">
                        <!--<h4>Categories</h4>
                        <ul style="list-style-type: none;padding: 0px;">-->
                        <?php
                        /*foreach($categories as $cat)
                        {
                                echo '<li class="category-side"><a href="' . site_url('category/' . $cat->slug) . '">' . ucfirst(strtolower($cat->name)) . '</a></li>';
                        }*/
                            
                        ?>
                        <!--</ul>-->
                    </div>

                    <div class="col-sm-9 col-md-10">
                        <div id="grid-selector">
                        </div>

                        <div class="clearfix"></div>
                        <div class="row">
                            <div id="results" class="grid"></div>
                        </div>
                        <div class="clearfix"></div>

                        <div class="row">
                            <div class="col-md-6">
                                <span class="page-info line-height-xl hidden-xs hidden-sm"></span>
                            </div>
                            <div class="col-md-6">
                                <div id="pagination" class="pagination-right"></div>
                            </div>
                        </div>

                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</section>
