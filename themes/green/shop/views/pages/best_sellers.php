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
            <div class="col-md-2 ">
                
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
                            <a href="#" class="list-group-item list-group-item-action active" aria-current="true">
                            Beauty
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">Eye Care</a>
                            <a href="#" class="list-group-item list-group-item-action">Hair Care</a>
                            <a href="#" class="list-group-item list-group-item-action">Herbel</a>
                            
                        </div>
                        
                        
                        <hr>
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
                            </div>

                            <div class="py-3">
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
                            </div>
                        </div>
                    </div>
                </div>
                <!-- side bar end -->
            </div>
            <div class="col-md-10 ps-md-5">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md-6 col-6">
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
                    </div>
                </div>

                <!-- all products -->

                <div class="row products-card text-center gy-4 py-4">
                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <div class="card" style="width: 100%">
                        <a href="#" class="text-decoration-none">
                        <div class="cardImg"><img src="./images/productimg1.jpg" class="card-img-top" alt="..."></div>
                        <div class="card-body px-0 text-start pb-0">
                            <div class="product-cat-title"><span class="text-uppercase">Medical</span></div>
                            <h5 class="card-title text-start">Vitamin C 500mg Sugarless Tab x 75</h5>
                            <div class="row align-items-center justify-content-between">
                            <div class="col-md-6">
                                <div class="rating">
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill"></i>
                                
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="discountPrice price text-end py-2"><h4 class="m-0 text-decoration-line-through">240.00 SAR</h4></div>
                            </div>
                            </div> 
                            <!--price and quantity araea  -->
        
                            <div class="row align-items-center justify-content-between">
                            <div class="col-md-6 ">
                                <div class="price text-start  py-2"><h4 class="m-0 fw-bold">240.00 SAR</h4></div>
                            </div>
                            <div class="col-md-6">
                                <div class="quantity text-end py-2 d-flex align-items-center justify-content-between">
                                <span class="plus"><i class="bi bi-plus-circle-fill"></i></span>
                                <span class="Qnum ">1</span>
                                <span class="minus"><i class="bi bi-dash-circle-fill"></i></span>
                                </div>
                            </div>
                            </div>
                        
                            <!-- price area end -->
                            
                            
                            </div>
                        </a>
                        <div> <button type="button" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark">Add to cart </button></div>
                        </div> 
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <div class="card" style="width: 100%">
                        <a href="#" class="text-decoration-none">
                        <div class="cardImg"><img src="./images/productimg1.jpg" class="card-img-top" alt="..."></div>
                        <div class="card-body px-0 text-start pb-0">
                            <div class="product-cat-title"><span class="text-uppercase">Medical</span></div>
                            <h5 class="card-title text-start">Vitamin C 500mg Sugarless Tab x 75</h5>
                            <div class="row align-items-center justify-content-between">
                            <div class="col-md-6">
                                <div class="rating">
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill"></i>
                                
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="discountPrice price text-end py-2"><h4 class="m-0 text-decoration-line-through">240.00 SAR</h4></div>
                            </div>
                            </div> 
                            <!--price and quantity araea  -->
        
                            <div class="row align-items-center justify-content-between">
                            <div class="col-md-6 ">
                                <div class="price text-start  py-2"><h4 class="m-0 fw-bold">240.00 SAR</h4></div>
                            </div>
                            <div class="col-md-6">
                                <div class="quantity text-end py-2 d-flex align-items-center justify-content-between">
                                <span class="plus"><i class="bi bi-plus-circle-fill"></i></span>
                                <span class="Qnum ">1</span>
                                <span class="minus"><i class="bi bi-dash-circle-fill"></i></span>
                                </div>
                            </div>
                            </div>
                        
                            <!-- price area end -->
                            
                            
                            </div>
                        </a>
                        <div> <button type="button" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark">Add to cart </button></div>
                        </div> 
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <div class="card" style="width: 100%">
                        <a href="#" class="text-decoration-none">
                        <div class="cardImg"><img src="./images/productimg1.jpg" class="card-img-top" alt="..."></div>
                        <div class="card-body px-0 text-start pb-0">
                            <div class="product-cat-title"><span class="text-uppercase">Medical</span></div>
                            <h5 class="card-title text-start">Vitamin C 500mg Sugarless Tab x 75</h5>
                            <div class="row align-items-center justify-content-between">
                            <div class="col-md-6">
                                <div class="rating">
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill"></i>
                                
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="discountPrice price text-end py-2"><h4 class="m-0 text-decoration-line-through">240.00 SAR</h4></div>
                            </div>
                            </div> 
                            <!--price and quantity araea  -->
        
                            <div class="row align-items-center justify-content-between">
                            <div class="col-md-6 ">
                                <div class="price text-start  py-2"><h4 class="m-0 fw-bold">240.00 SAR</h4></div>
                            </div>
                            <div class="col-md-6">
                                <div class="quantity text-end py-2 d-flex align-items-center justify-content-between">
                                <span class="plus"><i class="bi bi-plus-circle-fill"></i></span>
                                <span class="Qnum ">1</span>
                                <span class="minus"><i class="bi bi-dash-circle-fill"></i></span>
                                </div>
                            </div>
                            </div>
                        
                            <!-- price area end -->
                            
                            
                            </div>
                        </a>
                        <div> <button type="button" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark">Add to cart </button></div>
                        </div> 
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-12">
                        <div class="card" style="width: 100%">
                        <a href="#" class="text-decoration-none">
                        <div class="cardImg"><img src="./images/productimg3.png" class="card-img-top" alt="..."></div>
                        <div class="card-body px-0 text-start pb-0">
                            <div class="product-cat-title"><span class="text-uppercase">Medical</span></div>
                            <h5 class="card-title text-start">Vitamin C 500mg Sugarless Tab x 75</h5>
                            <div class="row align-items-center justify-content-between">
                            <div class="col-md-6">
                                <div class="rating">
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill rated"></i>
                                <i class="bi bi-star-fill"></i>
                                
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="discountPrice price text-end py-2"><h4 class="m-0 text-decoration-line-through">240.00 SAR</h4></div>
                            </div>
                            </div> 
                            <!--price and quantity araea  -->
        
                            <div class="row align-items-center justify-content-between">
                            <div class="col-md-6 ">
                                <div class="price text-start  py-2"><h4 class="m-0 fw-bold">240.00 SAR</h4></div>
                            </div>
                            <div class="col-md-6">
                                <div class="quantity text-end py-2 d-flex align-items-center justify-content-between">
                                <span class="plus"><i class="bi bi-plus-circle-fill"></i></span>
                                <span class="Qnum ">1</span>
                                <span class="minus"><i class="bi bi-dash-circle-fill"></i></span>
                                </div>
                            </div>
                            </div>
                        
                            <!-- price area end -->
                            
                            
                            </div>
                        </a>
                        <div> <button type="button" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark">Add to cart </button></div>
                        </div> 
                    </div>
                    
                    
                    </div>
                <!-- all products end -->
            </div>
        </div>
    </div>
</section>

<!-- products end -->

