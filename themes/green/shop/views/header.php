<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">
  <head>
  <?php
    if (strpos($currentUrl, 'avenzur.com') !== false) {
    ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-ML8N7LRG');</script>
    <!-- End Google Tag Manager -->
    <?php
    }
    ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Avenzur</title>
    <!--<link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>-->
    <!--<link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;1,100;1,300;1,400;1,700&family=Manrope:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="<?= $assets; ?>css/ecommerce-main.css" rel="stylesheet">

    <script src="<?= $assets; ?>build/js/intlTelInput.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <?php
    if (strpos($currentUrl, 'avenzur.com') !== false) {
    ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0GGN9ELJJG">
    </script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-0GGN9ELJJG');
    </script>
    <?php
    }
    ?>
    <?php

    if (strpos($currentUrl, 'avenzur.com') !== false) {
    ?>
    <!-- Meta Pixel Code --><script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js'); fbq('init', '674586174823661'); fbq('track', 'PageView');</script><noscript> <img height="1" width="1" src="https://www.facebook.com/tr?id=674586174823661&ev=PageView&noscript=1"/></noscript><!-- End Meta Pixel Code -->
      <?php
    }
    ?>
    <?php

    if (strpos($currentUrl, 'avenzur.com') !== false) {
      ?>
        <!-- Meta Pixel Code -->
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '768269778475763');
        fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=768269778475763&ev=PageView&noscript=1"
        /></noscript>
        <!-- End Meta Pixel Code -->
      <?php
    }
    ?>
</head>
  
  <body>
  <?php

    if (strpos($currentUrl, 'avenzur.com') !== false) {
      ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-ML8N7LRG"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
    }
    ?>
    <!-- top bar -->
    <section class="top-bar py-3 ">
      <div class="container container-max-width">
          <div class="row align-items-center">
            <!-- <div class="col-md-6 topBartxt" style="font-weight: bold;font-size: medium;">
              Free shipping.....Limited time offer *
            </div> -->
          <div class="col-lg-3 col-md-3  mb-2">
            <div class="logosearchMob" id="shoppingdivMob">
              <div class="logo-k"> <a class="navbar-brand" href="<?= site_url(); ?>"><img src="<?= base_url('assets/uploads/logos/'.$shop_settings->logo); ?>" alt="<?= $shop_settings->shop_name; ?>"></a></div>
              <div id="searchtoggle"><i class="bi bi-search"></i></div>
            </div>       
          </div>

          <div class="col-lg-5 col-md-5" id="searchbarmob">
            <div id="searchtogglecros"><i class="bi bi-x-circle-fill"></i></div>
            <?= shop_form_open('products', 'class="d-flex search-bar"'); ?>
              <input name="query" class="form-control border-0 bg-transparent py-1 add_item_search"  id="product-search" type="search" placeholder="What are you looking for?" aria-label="Search">
              <button class="btn searchsubmitBtn" type="submit"><i class="bi bi-search"></i></button>
            <?= form_close(); ?>
            <ul id="autocomplete-suggestions" class="ui-autocomplete"></ul>
          </div>
          <div class="col-md-4 d-flex justify-content-end topbarBtns">
            <div class="dropdown me-2">
                <a class="btn  dropdown-toggle text-white moblang" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-globe-americas me-1"></i> English <i class="bi bi-chevron-down ms-2"></i>
                </a>
              
                <ul class="dropdown-menu" id="languageDropdown">
                  <li><a class="dropdown-item" href="#" data-lang="en">ENGLISH</a></li>
                  <li><a class="dropdown-item" href="#" data-lang="ar">عربي</a></li>
                </ul>
            </div>
            <div class="dropdown me-2">
                <a class="btn  dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  SAR <i class="bi bi-chevron-down ms-2"></i>
                </a>
              
                <ul class="dropdown-menu" id="currencyDropdown">
                    <li><a class="dropdown-item" href="#" data-lang="SAR">SAR</a></li>
                  <li><a class="dropdown-item" href="#" data-lang="USD">USD</a></li>
                  <li><a class="dropdown-item" href="#" data-lang="AED">AED</a></li>
                  
                </ul>
            </div>
            <div class="dropdown logindropdown-k">
            <button type="button" id="login-btn-trigger" class="btn text-white dropdown-toggle px-0 border-0" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
              <?php
                if ($loggedIn) {
                  ?>
                  <i class="bi bi-person-fill"></i>&nbsp; <?= lang('hi') . ' ' . $loggedInUser->first_name; ?>
                <?php }else{
                  ?>
                    <i class="bi bi-person-fill"></i>&nbsp; Login
                  <?php
                }
                ?>
            </button>
              <?php 
                if ($loggedIn) {
                  ?>
                  <div class="dropdown-menu dropdown-menu-right p-3 loggedin"">
                    <div>
                      <a class="text-decoration-none text-dark" href="<?= site_url('profile'); ?>"><i class="mi fa fa-user"></i> <?= lang('profile'); ?></a>
                    </div>
                    <div>
                      <a class="text-decoration-none text-dark" href="<?= shop_url('orders'); ?>"><i class="mi fa fa-heart"></i> <?= lang('orders'); ?></a>
                    </div>
                    <div>
                      <a class="text-decoration-none text-dark" href="<?= shop_url('addresses'); ?>"><i class="mi fa fa-building"></i> <?= lang('addresses'); ?></a>
                    </div>
                    <div>
                      <a class="text-decoration-none text-dark" href="<?= shop_url('rateAndReview'); ?>"><i class="mi fa fa-star"></i>Rate and Review</a>
                    </div>
                    <div>
                      <a class="text-decoration-none text-dark" href="<?= site_url('logout'); ?>"><i class="mi fa fa-sign-out"></i> <?= lang('logout'); ?></a>
                    </div>
                </div>
                  <?php
                }else{
                  ?>  
                    <?php $u = mt_rand();
                      $currentUri = $this->uri->uri_string(); 
                      if ($currentUri !== 'profile' && $currentUri !== 'login' && $currentUri !== 'login#register') {
                    ?>
                    
                    <div class="dropdown-menu p-3 myaccountForm validate" id="myaccountForm">
                    <div class="loginRCard px-4 w-100">
                        <div class="logo-k mb-5"> 
                            <a class="navbar-brand" href="http://localhost/avenzur/">
                                <img src="<?= base_url('assets/uploads/logos/avenzur-logov2-024.png') ?>" alt="AVENZUR">
                            </a>
                        </div>
                        <h4 class="fw-bold letstart">Let's get started</h4>
                        <div class="logsignBtns mt-3 d-flex justify-content-center">
                            <button type="button" id="loginBtn" class="btn  text-white" onclick="LoginFn(this);">Log in</button>
                            <button type="button" id="registerBtn" class="btn  text-white px-4 active" onclick="registerFnBtn(this);">Sign up</button>
                        </div>
                        <div id="registerBlock">
                            <?php 
                                $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'registrationForm'];
                                echo form_open('register', $attrib); 
                            ?>
                            <div class="controls logcardinput">
                            
                            <input type="email" id="email" name="email" class="form-control" placeholder="Please enter email" required="required"/>
                            
                            </div>

                            <button id="registerBtnCall" type="button" class="btn  text-white continueBtn" data-bs-toggle="modal">Continue</button>
                            <?= form_close(); ?>
                        </div>

                        <div id="loginBlock" style="display:none;">
                            <?php 
                                $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'loginForm'];
                                echo form_open('login', $attrib); 
                            ?>
                            <div class="controls logcardinput" id="inputContainer">
                            
                            <input type="text" id="identity" name="identity" class="form-control" placeholder="Please enter email or phone number" required="required" />
                            
                            </div>

                            <button id="loginBtnCall" type="button" class="btn  text-white continueBtn" data-bs-toggle="modal">Continue</button>
                            <?= form_close(); ?>
                        </div>
                        
                      <div>
                      <span id="register-message" style="color: blue;"></span>
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
    <!-- top bar end -->

    <!-- logo search bar -->
    <!-- <section class="logo-searchBar">
      <div class="container container-max-width">
        <div class="row  align-items-center justify-content-between py-3">

          <div class="col-lg-2 col-md-3  mb-2">
            <div class="logosearchMob" id="shoppingdivMob">

              <div class="logo-k"> <a class="navbar-brand" href="<?= site_url(); ?>"><img src="<?= base_url('assets/uploads/logos/'.$shop_settings->logo); ?>" alt="<?= $shop_settings->shop_name; ?>"></a></div>
            <div id="searchtoggle"><i class="bi bi-search"></i></div>
            </div>
           
             

          </div>
          <div class="col-lg-9 col-md-8" id="searchbarmob">
            <div id="searchtogglecros"><i class="bi bi-x-circle-fill"></i></div>
            <?= shop_form_open('products', 'class="d-flex search-bar"'); ?>
              <input name="query" class="form-control border-0 bg-transparent py-3 add_item_search"  id="product-search" type="search" placeholder="What are you looking for?" aria-label="Search">
              <button class="btn searchsubmitBtn" type="submit"><i class="bi bi-search"></i></button>
            <?= form_close(); ?>
            <ul id="autocomplete-suggestions" class="ui-autocomplete"></ul>
          </div>
          <div class="col-lg-2 col-md-1 ps-md-0" id="salemob"></div>

        </div>
      </div>
        
    </section> -->
    
    <!-- logo search bar end -->

    <!-- menu bar -->
    <section>
      <div class="container container-max-width main-menuTab">
        <div class="row align-items-center">
          <div class="col-md-2 col-sm-2 mob-catS" id="allcatDiv">
            <button class="btn all-categoryBtn d-flex align-items-center justify-content-between" id="allCatmob" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
              <i class="bi bi-filter-left "></i> All Category
            </button>
            
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
              <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasExampleLabel">Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
              </div>
              <div class="offcanvas-body">           
            
                <ol class="list-group list-group-numbered ar-ol">
                  <?php
                    foreach($all_categories as $category){
                      ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                          <div class="ms-2 me-auto ar-catL">
                            <div class="fw-bold"><a href="<?= site_url('category/'.$category->slug) ?>"><?= $category->name; ?></a></div>
                            <?php //echo $category->description; ?>
                          </div>
                          <!--<span class="badge bg-primary rounded-pill">14</span>-->
                        </li>
                      <?php
                    }
                  ?>
                </ol>
                
              </div>
            </div>
          </div>
          <div class="col-md-7 col-sm-2 mob-menu">
            <nav class="navbar navbar-expand-lg navbar-expand-md  container-max-width">
              <div class="container-fluid">
                
                <div class="menu-av" id="sourcedivmob">
                  
                  <button id="menuiconMob" class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"><i class="bi bi-list"></i></span>
                  </button>
                  <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0" >
                      <li class="nav-item">
                        <?php 
                          $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
                          $domain = $_SERVER['HTTP_HOST'];
                          $url = ($isHttps ? 'https://' : 'http://') . $domain . $_SERVER['REQUEST_URI'];
                        ?>
                        <a class="nav-link <?php if(site_url() == $url){ echo 'active'; } ?>" aria-current="page" href="<?= site_url(); ?>">Home</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link <?php if(site_url('shop/products') == $url){ echo 'active'; } ?>"  href="<?= site_url('shop/products'); ?>">Products</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link <?php if(site_url('shop/products?promo=yes') == $url){ echo 'active'; } ?>"  href="<?= site_url('shop/products?promo=yes'); ?>">Promotions</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link <?php if(site_url('shop/bestsellers') == $url){ echo 'active'; } ?>"  href="<?= site_url('shop/bestsellers'); ?>">Best Sellers</a>
                      </li>
                      
                    
                     <div id="mobnav"> 

                     </div>
                     
                    </ul>
                    
                  </div>
                </div>
               
               
              </div>
            </nav>
          </div>
          <div class="col-md-3 col-sm-2 shop-icons">
            <div class="text-end" id="cartdiv">
            
              <span class="cartIcon" id="cart-items">
                <a class="btn  dropdown-toggle border-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.33331 24C7.86665 24 6.67998 25.2 6.67998 26.6667C6.67998 28.1333 7.86665 29.3333 9.33331 29.3333C10.8 29.3333 12 28.1333 12 26.6667C12 25.2 10.8 24 9.33331 24ZM22.6666 24C21.2 24 20.0133 25.2 20.0133 26.6667C20.0133 28.1333 21.2 29.3333 22.6666 29.3333C24.1333 29.3333 25.3333 28.1333 25.3333 26.6667C25.3333 25.2 24.1333 24 22.6666 24ZM20.7333 17.3333C21.7333 17.3333 22.6133 16.7867 23.0666 15.96L27.84 7.30666C27.9523 7.10456 28.01 6.87662 28.0072 6.6454C28.0045 6.41418 27.9414 6.18769 27.8242 5.98834C27.707 5.78899 27.5398 5.6237 27.3391 5.50881C27.1384 5.39393 26.9112 5.33344 26.68 5.33332L6.94665 5.33332L5.69331 2.66666L1.33331 2.66666L1.33331 5.33332H3.99998L8.79998 15.4533L6.99998 18.7067C6.02665 20.4933 7.30665 22.6667 9.33331 22.6667L25.3333 22.6667V20L9.33331 20L10.8 17.3333L20.7333 17.3333ZM8.21331 7.99999L24.4133 7.99999L20.7333 14.6667L11.3733 14.6667L8.21331 7.99999Z" fill="#171A1F"/>
                  </svg>
                  <span class="quantitynum cart-total-items" style="display: none;">1</span>
                </a>

                <div id="cart-contents" class=" dropdown-menu p-3 myaccountForm cartform">
                  <table class="table " id="cart-items-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                          
                        </tr>
                    </thead>
                    <tbody id="cart-body" ></tbody>
                    <tfoot id="cart-foot"></tfoot>
                  </table>
                <div class="d-flex">
                  <a href="<?= site_url('cart'); ?>" class="btn primary-buttonAV w-100 rounded-1 pb-2 mx-2 text-center">View Cart</a>
                  <?php
                    if ($loggedIn) {
                        ?>
                        <a href="<?= site_url('cart/checkout'); ?>" class="btn primary-buttonAV w-100 rounded-1 pb-2 mx-2 text-center">Checkout</a>
                        <?php
                    }else{
                      ?>
                        <a href="<?= site_url('cart'); ?>" class="btn primary-buttonAV w-100 rounded-1 pb-2 mx-2 text-center">Checkout</a>
                      <?php
                    }
                  ?>
                  
                </div>
              </div>

              </span>
              
            </div>
          
              
              
          </div>
        </div>
      </div>
    </section>
    <!-- menu bar end -->

    <?php 
      
      $currentUri = $this->uri->uri_string(); 
      if ($currentUri !== 'profile' && $currentUri !== 'login' && $currentUri !== 'login#register' && $currentUri !== 'cart/checkout') {
        ?>
          <!-- Register Modal Starts -->
          <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content px-4 rounded-4">
                    <div class="modal-header border-0">
                        <button type="button" class="modalcloseBtn" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="modal-body ">
                        <div class="emailOTP">
                            <div class="text-center px-5">
                                <h2>Verify your email</h2>
                                <h5 class="fs-4 px-5 lh-base">OTP has been sent to <span id="identifier"></span></h5>
                            </div>
                            <?php 
                                $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'registerOtpForm'];
                                echo form_open('register_otp', $attrib); 
                            ?>
                            <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2"> 
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part1" id="register_otp_1" maxlength="1" />
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part2" id="register_otp_2" maxlength="1" />
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part3" id="register_otp_3" maxlength="1" />
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part4" id="register_otp_4" maxlength="1" /> 
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part5" id="register_otp_5" maxlength="1" />
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part6" id="register_otp_6" maxlength="1" />
                                <input type="hidden" id="identifier_input" name="identifier_input" value="" />
                            </div>
                            <div  class="text-center">
                                <h6 class="m-0 mt-2"><span id="register-clock"></span> <span class="ms-2 fw-semibold opacity-50" id="registerOTP">Resend OTP </span></h6>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4">
                        <button type="submit" id="registerOtpBtn" class="btn  text-white continueBtn rounded w-75 mx-auto mt-0" data-bs-toggle="modal" data-bs-target="#exampleModal">Login</button>
                    </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
        <!-- Register Modal Ends -->

        <!-- Login Modal Starts -->
        <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content px-4 rounded-4">
                    <div class="modal-header border-0">
                        
                        <button type="button" class="modalcloseBtn" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
                    </div>
                    <div class="modal-body ">
                        <div class="smsOTP">
                            <div class="text-center px-5">
                                <h2>Verify OTP</h2>
                                <h5 class="fs-4 px-5 lh-base">OTP has been sent to <span id="identifierl"></span></h5>
                            </div>
                            <?php 
                                $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'loginOtpForm'];
                                echo form_open('login_otp', $attrib); 
                            ?>
                            <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2"> 
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part1" id="login_otp_1" maxlength="1" />
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part2" id="login_otp_2" maxlength="1" />
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part3" id="login_otp_3" maxlength="1" />
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part4" id="login_otp_4" maxlength="1" /> 
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part5" id="login_otp_5" maxlength="1" />
                                <input class="m-1 text-center form-control rounded" type="text" name="opt_part6" id="login_otp_6" maxlength="1" />
                                <input type="hidden" id="identifierl_input" name="identifier_input" value="" />
                            </div>
                            
                            <div  class="text-center">
                                <h6 class="m-0 mt-2"><span id="login-clock"></span> <span class="ms-2 fw-semibold opacity-50" id="loginOTP">Resend OTP</span></h6>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-0 pb-4">
                        <button type="submit" id="loginOtpBtn" class="btn  text-white continueBtn rounded w-75 mx-auto mt-0">Login</button>
                    </div>
                    <?= form_close(); ?>
                </div>
            </div>
        </div>
        <!-- Login Modal Ends -->
        <?php
      }
    ?>

  <!-- product popup start -->
  <div class="modal fade" id="productPop" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content px-4 rounded-4">
            <div class="modal-header border-0">
                
                <button type="button" class="modalcloseBtn" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body" id="product-popup-modal-body">
              
              <!--<div class=" row align-items-center mt-4">
                <div class="addicon col-md-3 px-0">
                    <p class="m-0 fs-5 fw-semibold text-start text-dark">
                        Cart Total  
                    </p>
                </div>
                <div class=" col-md-9">
                    <p class="m-0 fs-5 fw-semibold mt-2 text-end text-dark"> SAR 190</p>  
                </div>                                                             
              </div>-->
            </div>
            <div class="modal-footer border-0 pb-4 d-flex flex-nowrap">
                <button type="submit" class="btn text-white continueBtn w-50 rounded  mx-1 mt-0">
                  <?php 
                    if ($loggedIn) {
                      ?>
                        <a href="<?= site_url('cart'); ?>" style="color: #fff;text-decoration: none;">
                          Checkout
                        </a>
                      <?php
                    }else{
                      ?>
                        <a href="<?= site_url('cart'); ?>" style="color: #fff;text-decoration: none;">
                          Checkout
                        </a>
                      <?php
                    }
                  
                  ?>
                
                </button>
                <button type="submit"  class="btn text-white continueBtn w-50 rounded  mx-1 mt-0" data-bs-dismiss="modal">Continue Shopping</button>
            </div>
            
        </div>
    </div>
  </div>

</div>
<!-- product popup end -->

    <?php if ($this->session->flashdata('error')){ ?>
        <div class="error-message"><?php echo $this->session->flashdata('error');exit; ?></div>
    <?php } ?>
