<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<!--product detail page  -->

<section class="side-banner py-3">
    <div class="container container-max-width">
        <div class="brad-crumb py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item " aria-current="page"><a href="#" class="text-decoration-none">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $product->name; ?></li>
            </ol>
        </nav>
        </div>
        
        <div class="row">
        <!-- product image col and thumb slider -->
            <div class="col-md-5 col-12 pe-md-5 view_product_ar">
                <div id="slider" class="owl-carousel product-slider">
                    <?php
                        if(isset($product->image)){
                            ?>
                                <div class="item productzoomImg">
                                        <img src="<?= base_url('assets/uploads/' . $product->image); ?>" />
                                </div>
                            <?php
                        }

                        if (!empty($images)) {
                            foreach ($images as $ph) {
                                ?>
                                    <div class="item productzoomImg">
                                        <img src="<?= base_url('assets/uploads/' . $ph->photo); ?>" />
                                    </div>
                                <?php
                            }
                        }
                    ?>	
                </div>

                <div id="thumb" class="owl-carousel product-thumb">
                    <?php
                        if(isset($product->image)){
                            ?>
                                <div class="item">
                                        <img src="<?= base_url('assets/uploads/' . $product->image); ?>" />
                                </div>
                            <?php
                        }

                        if (!empty($images)) {
                            foreach ($images as $ph) {
                                ?>
                                    <div class="item">
                                        <img src="<?= base_url('assets/uploads/' . $ph->photo); ?>" />
                                    </div>
                                <?php
                            }
                        }
                    ?>
                </div>       
            </div>
            <!-- product image col and thumb slider end-->

            <!--product info start  -->

            <div class="col-md-7 col-12">
                <div class="product-dtl">
                    <div class="product-info">
                        <?php if(isset($brand->name)){
                            ?>
                                <span style="background: #662d91;color: #fff;padding: 3px;font-size: 13px;border-radius: 3px;"><?= $brand->name; ?></span>
                            <?php
                        } ?>
                        <div class="product-name">
                            <h2><?= $product->name; ?></h2>
                        </div>	
                        <div class="rating">
                            <?php 
                                for($i=1; $i<=5; $i++) {
                                    $class = '';
                                    if($i<=$product->avg_rating) {$class = 'rated';}?>
                            <i class="bi bi-star-fill <?php echo $class;?>" ></i>
                            <?php }?>
                        </div>	        		
            </div>

            <div class="product-price-discount"><h4 class="m-0">
                <?php 
                    if ($product->promotion) {
                        ?>
                            <span style="font-weight: bold;font-size: 20px;"><?= $this->sma->convertMoney($product->promo_price); ?> <span style="font-size: 12px;font-weight: normal;">(vat inclusive)</span></span>
                            <span class="line-through"><?= $this->sma->convertMoney(isset($product->special_price) ? $product->special_price : $product->price); ?> </span>
                        <?php
                    } else{
                        ?>
                            <span style="font-weight: bold; font-size: 20px;"><?= $this->sma->convertMoney(isset($product->special_price) ? $product->special_price : $product->price); ?> <span style="font-size: 12px;font-weight: normal;">(vat inclusive)</span></span>
                        <?php
                    }
                    
                ?>
                
            </h4></div>

            <div class="product-desc border-top" id="show_desc" style="min-height: 220px;line-height: 28px;">
                <p class="m-0 py-3">
                    <span><?php 
                        echo $product->details; 
                        //if(strlen($product->details) > 350){
                            //echo mb_strimwidth($product->details, 0, 350, ''); 
                        //}else{
                          //  echo $product->details;
                        //}   
                    ?></span>
                    <?php 
                    //if(strlen($product->details) > 350){
                    ?>
                    <!--<a id="a_desc" style="text-decoration: underline;cursor: pointer;float: right;margin-right: 50px; color: grey;">More...</a>-->
                    <?php
                    //}
                    ?>
                </p>

            </div>
                    
            <div class="product-detail product-count d-flex align-items-center get-quantity" style="width: fit-content;">
                        
                <form action="#" class=" quantity text-end py-2 d-flex align-items-center justify-content-md-between">
                

                <span class="minus btn-minus"><i class="bi bi-dash-circle-fill"></i></span>
                <input type="text" name="quantity" value="1" class="qty Qnum text-center w-100 p-0 ">
                <span class="plus btn-plus"><i class="bi bi-plus-circle-fill"></i></span>
                
                
                </form>
                <button type="button" data-id="<?= $product->id; ?>" class="btn primary-buttonAV ms-2 py-1 addtocart  text-dark add-to-cart" aria-controls="offcanvasWithBothOptions" ><i class="bi bi-cart3 me-2"></i> Add to cart </button>
                <!--<button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">show canvas</button>-->

            </div>
            <!-- <div class="product_meta pt-4">
                <p class="sku_wrapper m-0">SKU: <span class="sku">10007095</span></p>
                <p class="posted_in m-0">Categories: <a href="#" rel="tag">Supplements</a>, <a href="#" rel="tag">Vitamins</a></p> 
                <p class="tagged_as m-0">Tag: <a href="#" rel="tag">Vitamins</a></p>
            </div>
            <div class="mobficon shareproduct mt-4">
                <h6 class="m-0"><a href="#" class="text-dark text-decoration-none mx-2"> <i class="bi bi-facebook"></i></a> <a href="#" class="text-dark text-decoration-none mx-2"> <i class="bi bi-linkedin"></i></a>
                <a href="#" class="text-dark text-decoration-none mx-2"> <i class="bi bi-youtube"></i></a> <a href="#" class="text-dark text-decoration-none mx-2"> <i class="bi bi-twitter"></i></a>
                <a href="#" class="text-dark text-decoration-none mx-2"><i class="bi bi-instagram"></i></a> <a href="#" class="text-dark text-decoration-none mx-2"><i class="bi bi-skype"></i></a></h6>
            </div> -->
                </div>
            </div>
        <!-- product info end -->
        </div>
        
        <!-- product info tab start -->
        <div class="productInfoTab mt-5 py-2">
        <ul class="nav nav-tabs" id="productTabs" role="tablist">
            <li class="nav-item" role="presentation">
            <a class="nav-link active" id="description-tab" data-bs-toggle="tab" href="#description" role="tab" aria-controls="description" aria-selected="true">Description</a>
            </li>
            <li class="nav-item" role="presentation">
            <a class="nav-link" id="reviews-tab" data-bs-toggle="tab" href="#additional" role="tab" aria-controls="reviews" aria-selected="false">Additional Information</a>
            </li>
        </ul>
        <div class="tab-content" id="productTabsContent">
            <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
            <p class="m-0 py-3 lh-base fs-6">
                <?= $product->product_details; ?>
            </p>
            </div>
            <div class="tab-pane fade" id="additional" role="tabpanel" aria-labelledby="additional-tab">
            
            <div class="py-3 px-2 my-3  rounded-2 reviwCont">
                <table class="table w-25 m-0 addtable">
                
                <tbody>
                    <tr>
                    
                    <td>Code</td>
                    <td><?= $product->code; ?></td>
                    
                    </tr>
                    <tr>
                    
                    <td>Brand</td>
                    <td><?= $brand ? '<a href="' . site_url('brand/' . $brand->slug) . '" class="line-height-lg">' . $brand->name . '</a>' : ''; ?></td>
                    
                    </tr>
                    <tr>
                    
                    <td>Category</td>
                    <td><?= '<a href="' . site_url('category/' . $category->slug) . '" class="line-height-lg">' . $category->name . '</a>'; ?></td>
                    
                    </tr>
                    <tr>
                    
                    <td>Unit</td>
                    <td><?= $unit ? $unit->name . ' (' . $unit->code . ')' : ''; ?></td>
                    
                    </tr>
                    
                </tbody>
                </table>
            </div>
                
            </div>
        </div>
        </div>

        <!-- product info tab end -->

        <!-- related products -->
        
        <!-- related products end -->
        

    </div>
</section>

<!-- join container -->
<!--<section class="join-container " >
    <div class="container container-max-width py-5" style="background-image: url(<?php //echo base_url('assets/images/banners/bgbanner.jpg'); ?>);">
    <div class="text-center">
        <h2>Join our newsletter</h2>
        <p>Join over half a million vitamin lovers and get our latest deals, articles, and resources!</p>
        <form class="d-flex search-bar w-50 mx-auto" role="search">
            
        
            <input class="form-control border-0 bg-white py-3 ps-5" type="search" placeholder="What are you looking for?" aria-label="Search">
            <button class="btn searchsubmitBtn" type="submit"><i class="bi bi-search"></i></button>
        </form>
    </div>
    </div>
</section>-->
<!-- join section end -->

<script>

    $(document).on("click", "#a_desc", function(t) {
        var showId = document.getElementById('show_desc');
        var fullDesc = document.getElementById('description').innerHTML;
        fullDesc += '<a id="l_desc" style="text-decoration: underline;cursor: pointer;float: right;margin-right: 50px; color: grey;">Less...</a>';
        showId.innerHTML = fullDesc;
    });

    $(document).on("click", "#l_desc", function(t) {
        var showId = document.getElementById('show_desc');
        var fullDesc = limitText(document.getElementById('description').innerHTML, 400);
        fullDesc += '<a id="a_desc" style="text-decoration: underline;cursor: pointer;float: right;margin-right: 50px; color: grey;">More...</a>';
        showId.innerHTML = fullDesc;
    });

    function limitText(text, maxLength) {
        if (text.length <= maxLength) {
            return text;
        } else {
            return text.slice(0, maxLength) + '...';
        }
    }
    
</script>
