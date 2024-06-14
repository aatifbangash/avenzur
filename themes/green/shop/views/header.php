<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">

<head>
  <?php
  $currentUrl = current_url();
  if (strpos($currentUrl, 'avenzur.com') !== false) {
    ?>
    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i) {
        w[l] = w[l] || []; w[l].push({
          'gtm.start':
            new Date().getTime(), event: 'gtm.js'
        }); var f = d.getElementsByTagName(s)[0],
          j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
            'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
      })(window, document, 'script', 'dataLayer', 'GTM-ML8N7LRG');</script>
    <!-- End Google Tag Manager -->
    <?php
  }
  ?>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php
  $seo_title = isset($page_title2) ? $page_title2 : 'avenzur  | Health, Wellness & Beauty';
  $seo_description = 'Avenzur is a Health, Wellness and Beauty Store. Shop daily vitamins, supplements, beauty, skin care products and more.';
  
  if (isset($seoSetting) && !empty($seoSetting) && isset($product) && !empty($product)) {
    $seo_title = strip_tags(str_replace('{{title}}', $product->name, $seoSetting->title));

    $descriptionLimit = 20;
    $plainDescription = strip_tags($product->product_details);

    $words = explode(' ', $plainDescription);
    $limitedDescription = implode(' ', array_slice($words, 0, $descriptionLimit));
    $seo_description = strip_tags(str_replace('{{description}}', $limitedDescription, $seoSetting->description));

    $seo_keywords = strip_tags($seoSetting->keywords);

  }else{
    $seo_keywords = 'Avenzur, Saudi Arabia Store, Sulfad, Beauty';
  }
  $max_length = 60; // Maximum title length
    if (strlen($seo_title) > $max_length) {
        $seo_title = substr($seo_title, 0, $max_length);
        // Optionally, you can break the title at the last space within the limit to avoid breaking words
        $last_space = strrpos($seo_title, ' ');
        if ($last_space !== false) {
            $seo_title = substr($title, 0, $last_space);
        }
    }

    $max_length = 160; // Maximum title length
    if (strlen($seo_description) > $max_length) {
        $seo_description = substr($seo_description, 0, $max_length);
    }
  
    $actual_link_href = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  ?>
  <title>
    <?php echo htmlspecialchars($seo_title); ?>
  </title>
  <link rel="icon" type="image/x-icon" href="<?= base_url('assets/images/avenzur-png.png'); ?>">
  <link rel="canonical" href="<?= $actual_link_href; ?>" />
  <meta name="description" content="<?php echo htmlspecialchars($seo_description); ?>">
  <meta name="keywords" content="<?php echo $seo_keywords; ?>">
  <meta name="p:domain_verify" content="0704e772b1ab59012494397c16667a45"/>
  <meta property="og:title" content="avenzur | Health, Wellness & Beauty">
  <meta property="og:description" content="Avenzur is a Health, Wellness and Beauty Store. Shop daily vitamins, supplements, beauty, skin care products and more.">
  <meta property="og:image" content="https://avenzur.com/assets/uploads/logos/avenzur-logov2-024.png">
  <meta property="og:url" content="https://avenzur.com">
  <meta name="twitter:title" content="avenzur | Health, Wellness & Beauty">
  <meta name="twitter:description" content="Avenzur is a Health, Wellness and Beauty Store. Shop daily vitamins, supplements, beauty, skin care products and more.">
  <meta name="twitter:url" content="https://avenzur.com/assets/uploads/logos/avenzur-logov2-024.png">
  <meta name="twitter:card" content="summary">
  <!--<link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>-->
  <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;1,100;1,300;1,400;1,700&family=Manrope:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link href="<?= $assets; ?>css/ecommerce-main.css" rel="stylesheet">
  <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/droid-arabic-kufi" type="text/css"/>
  
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
      function gtag() { dataLayer.push(arguments); }
      gtag('js', new Date());

      gtag('config', 'G-0GGN9ELJJG');
    </script>
    <?php
  }
  ?>
  <?php

  if (strpos($currentUrl, 'avenzur.com') !== false) {
    ?>
    <!-- Meta Pixel Code -->
    <script>!function (f, b, e, v, n, t, s) { if (f.fbq) return; n = f.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments) }; if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0'; n.queue = []; t = b.createElement(e); t.async = !0; t.src = v; s = b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t, s) }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js'); fbq('init', '674586174823661'); fbq('track', 'PageView');</script>
    <noscript> <img height="1" width="1"
        src="https://www.facebook.com/tr?id=674586174823661&ev=PageView&noscript=1" /></noscript><!-- End Meta Pixel Code -->
    <?php
  }
  ?>
  <?php

  if (strpos($currentUrl, 'avenzur.com') !== false) {
    ?>
    <!-- Twitter conversion tracking base code -->
    <script>
    !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);
    },s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='https://static.ads-twitter.com/uwt.js',
    a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');
    twq('config','oiotp');
    </script>
    <!-- End Twitter conversion tracking base code -->

    <!-- Meta Pixel Code -->
    <script>
      !function (f, b, e, v, n, t, s) {
        if (f.fbq) return; n = f.fbq = function () {
          n.callMethod ?
            n.callMethod.apply(n, arguments) : n.queue.push(arguments)
        };
        if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
        n.queue = []; t = b.createElement(e); t.async = !0;
        t.src = v; s = b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t, s)
      }(window, document, 'script',
        'https://connect.facebook.net/en_US/fbevents.js');
      fbq('init', '768269778475763');
      fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=768269778475763&ev=PageView&noscript=1" /></noscript>
    <!-- End Meta Pixel Code -->

    <script>
      !function (w, d, t) {
        w.TiktokAnalyticsObject = t; var ttq = w[t] = w[t] || []; ttq.methods = ["page", "track", "identify", "instances", "debug", "on", "off", "once", "ready", "alias", "group", "enableCookie", "disableCookie"], ttq.setAndDefer = function (t, e) { t[e] = function () { t.push([e].concat(Array.prototype.slice.call(arguments, 0))) } }; for (var i = 0; i < ttq.methods.length; i++)ttq.setAndDefer(ttq, ttq.methods[i]); ttq.instance = function (t) { for (var e = ttq._i[t] || [], n = 0; n < ttq.methods.length; n++)ttq.setAndDefer(e, ttq.methods[n]); return e }, ttq.load = function (e, n) { var i = "https://analytics.tiktok.com/i18n/pixel/events.js"; ttq._i = ttq._i || {}, ttq._i[e] = [], ttq._i[e]._u = i, ttq._t = ttq._t || {}, ttq._t[e] = +new Date, ttq._o = ttq._o || {}, ttq._o[e] = n || {}; var o = document.createElement("script"); o.type = "text/javascript", o.async = !0, o.src = i + "?sdkid=" + e + "&lib=" + t; var a = document.getElementsByTagName("script")[0]; a.parentNode.insertBefore(o, a) };

        ttq.load('CM9BCQBC77UBFHFT9UAG');
        ttq.page();
      }(window, document, 'ttq');
    </script>

    <script type="text/javascript">
      function normalizeEmail(email){
        return email.trim().toLowerCase();
      }

      function normalizePhone(phone){
        var numbers = phone.replace(/\D/g, '');
        return parseInt(numbers, 10).toString();
      }
    </script>

    <!-- Snap Pixel Code -->
    <script type='text/javascript'>
      (function (e, t, n) {
        if (e.snaptr) return; var a = e.snaptr = function () { a.handleRequest ? a.handleRequest.apply(a, arguments) : a.queue.push(arguments) };
        a.queue = []; var s = 'script'; r = t.createElement(s); r.async = !0;
        r.src = n; var u = t.getElementsByTagName(s)[0];
        u.parentNode.insertBefore(r, u);
      })(window, document,
        'https://sc-static.net/scevent.min.js');

      var userSessionEmail = "<?php echo $this->session->userdata('email'); ?>";
      if (userSessionEmail) {
        var normalizedEmail = normalizeEmail(userSessionEmail);
      }else{
        var normalizedEmail = '';
      }

      var userSessionPhone = "<?php echo $this->session->userdata('phone'); ?>";
      if (userSessionPhone) {
        var normalizedPhone = normalizeEmail(userSessionPhone);
      }else{
        var normalizedPhone = '';
      }

      var userSessionFirstName = "<?php echo $this->session->userdata('customer_first_name'); ?>";
      if (userSessionFirstName) {
        var normalizedFirstName = normalizeEmail(userSessionFirstName);
      }else{
        var normalizedFirstName = '';
      }

      var userSessionLastName = "<?php echo $this->session->userdata('customer_last_name'); ?>";
      if (userSessionLastName) {
        var normalizedLastName = normalizeEmail(userSessionLastName);
      }else{
        var normalizedLastName = '';
      }

      var userSessionZipCode = "<?php echo $this->session->userdata('customer_zip_code'); ?>";
      if (userSessionZipCode) {
        var normalizedZipCode = normalizeEmail(userSessionZipCode);
      }else{
        var normalizedZipCode = '';
      }

      snaptr('init', '48414e12-17e7-4ba1-bfd6-407aa41991b0', {
        'user_email': normalizedEmail,
        'user_phone': normalizedPhone,
        'firstname': normalizedFirstName,
        'lastname': normalizedLastName,
        'geo_postal_code': normalizedZipCode
      });

      snaptr('track', 'PAGE_VIEW');

    </script>
    <!-- End Snap Pixel Code -->

    <?php
  }
  ?>

  <style>
    .inputs input.error-border {
      border: 1px solid red;
    }
    .notify-me-product {
      margin-top: 5%;
    }
    .product-image-wrapper{
      float: left;
      margin-right: 10%;
    }
  
    #notify_product_price{
      margin-top: 2%;
    }
   
    #notify_out_of_stock{
      margin-top: 2%;
    }

    #notifyMeBtn{
      margin-top: 4px;
      margin-bottom: 4px;
      padding-block: 5px !important;
    }

  </style>
</head>

<body>
  <?php

  if (strpos($currentUrl, 'avenzur.com') !== false) {
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-ML8N7LRG" height="0" width="0"
        style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
  }
  ?>
  <!-- top bar -->
  <section class="top-bar py-1 ">
    <div class="container container-max-width">
      <div class="row align-items-center">
        <div class="col-md-6 topBartxt" style="font-weight: bold;font-size: medium;color: red;">
          Free shipping. Limited time offer. Due to eid holidays order delivery will be delayed *
        </div>
        <div class="col-md-6 d-flex justify-content-end topbarBtns">
          <div class="dropdown me-2">
            <a class="btn  dropdown-toggle text-white moblang" href="#" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              <i class="bi bi-globe-americas me-1"></i> English <i class="bi bi-chevron-down ms-2"></i>
            </a>

            <ul class="dropdown-menu" id="languageDropdown">
              <li><a class="dropdown-item" href="#" data-lang="en">ENGLISH</a></li>
              <li><a class="dropdown-item" href="#" data-lang="ar">عربي</a></li>
            </ul>
          </div>
          <div class="dropdown me-2">
            <a class="btn  dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown"
              aria-expanded="false">
              SAR <i class="bi bi-chevron-down ms-2"></i>
            </a>

            <ul class="dropdown-menu" id="currencyDropdown">
              <li><a class="dropdown-item" href="#" data-lang="SAR">SAR</a></li>
              <li><a class="dropdown-item" href="#" data-lang="USD">USD</a></li>
              <li><a class="dropdown-item" href="#" data-lang="AED">AED</a></li>

            </ul>
          </div>
          <div class="dropdown logindropdown-k">
            <button type="button" id="login-btn-trigger" class="btn text-white dropdown-toggle px-0 border-0"
              data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
              <?php
              if ($loggedIn) {
                ?>
                <i class="bi bi-person-fill"></i>&nbsp;
                <?= lang('hi') . ' ' . $loggedInUserByCompany->first_name; ?>
              <?php } else {
                ?>
                <i class="bi bi-person-fill"></i>&nbsp; Login
                <?php
              }
              ?>
            </button>
            <?php
            if ($loggedIn) {
              ?>
              <div class="dropdown-menu loggedin">
                <div>
                  <a class=" text-decoration-none text-dark" href="<?= site_url('profile'); ?>"><i
                      class="mi fa fa-user"></i>
                    <?= lang('profile'); ?>
                  </a>
                </div>
                <div>
                  <a class="text-decoration-none text-dark" href="<?= shop_url('orders'); ?>"><i
                      class="mi fa fa-heart"></i>
                    <?= lang('orders'); ?>
                  </a>
                </div>
                <!--<div>
                          <a class="text-decoration-none text-dark" href="<?= shop_url('addresses'); ?>"><i class="mi fa fa-building"></i> <?= lang('addresses'); ?></a>
                        </div>-->
                <div>
                  <a class="text-decoration-none text-dark" href="<?= shop_url('rateAndReview'); ?>"><i
                      class="mi fa fa-star"></i>Rate and Review</a>
                </div>
                <div>
                  <a class="text-decoration-none text-dark" href="<?= site_url('logout'); ?>"><i
                      class="mi fa fa-sign-out"></i>
                    <?= lang('logout'); ?>
                  </a>
                </div>
              </div>
              <?php
            } else {
              ?>
              <?php $u = mt_rand();
              $currentUri = $this->uri->uri_string();
              if ($currentUri !== 'profile' && $currentUri !== 'login' && $currentUri !== 'login#register') {
                ?>

                <div class="dropdown-menu  myaccountForm validate" id="myaccountForm">
                  <div class="loginRCard px-3">
                    <div class="d-flex justify-content-end w-100 pb-3 ">
                      <div class=" login-close d-flex justify-content-center align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="19" viewBox="0 0 20 19" fill="none">
                          <path
                            d="M18.3972 17.9999L9.99379 9.49995M9.99379 9.49995L1.59033 1M9.99379 9.49995L18.3973 1M9.99379 9.49995L1.59033 18"
                            stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                      </div>
                    </div>
                    <div class="logo-k">
                      <a class="navbar-brand" href="<?= base_url(); ?>">
                        <img src="<?= base_url('assets/uploads/logos/avenzur-logov2-024.png') ?>" alt="AVENZUR">
                      </a>
                    </div>
                    <h4 class="fw-bold letstart">Let's get started</h4>
                    <div class="logsignBtns mt-3 d-flex justify-content-center">
                      <button type="button" id="loginBtn" class="btn  text-white" onclick="LoginFn(this);">Log in</button>
                      <button type="button" id="registerBtn" class="btn  text-white px-4 active"
                        onclick="registerFnBtn(this);">Sign up</button>
                    </div>
                    <div id="registerBlock">
                      <?php
                      $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'registrationForm'];
                      echo form_open('register', $attrib);
                      ?>
                      <div class="controls logcardinput" id="inputContainerRegister">

                        <input type="email" id="email" name="email" class="form-control"
                          placeholder="Please enter email or phone number" required="required" />

                      </div>
                      <div class="controls" style="font-size: 12px; font-weight: bold">
                        Phone Number Format: 5XXXXXXXX
                      </div>

                      <button id="registerBtnCall" type="button" class="btn  text-white continueBtn"
                        data-bs-toggle="modal">Continue
                        <span id="spinner" class="spinner-border spinner-border-sm d-none" role="status"
                          aria-hidden="true"></span>
                      </button>
                      <?= form_close(); ?>
                    </div>

                    <div id="loginBlock" style="display:none;">
                      <?php
                      $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'loginForm'];
                      echo form_open('login', $attrib);
                      ?>
                      <div class="controls logcardinput" id="inputContainer">

                        <input type="text" id="identity" name="identity" class="form-control"
                          placeholder="Please enter email or phone number" required="required" />

                      </div>
                      <div class="controls" style="font-size: 12px; font-weight: bold">
                        Phone Number Format: 5XXXXXXXX
                      </div>

                      <button id="loginBtnCall" type="button" class="btn  text-white continueBtn"
                        data-bs-toggle="modal">Continue</button>
                      <?= form_close(); ?>
                    </div>

                  <div>
                    <span id="register-message" style="color: red;padding-top: 5px;"></span>
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
  <section class="logo-searchBar">
    <div class="container container-max-width">
      <div class="d-flex  align-items-center justify-content-between py-3">

        <div class="mb-2 w-100 d-flex justify-content-between w-100">
          <div class="logosearchMob" id="shoppingdivMob">

            <div class="logo-k"> <a class="navbar-brand" href="<?= site_url(); ?>"><img
                  src="<?= base_url('assets/uploads/logos/' . $shop_settings->logo); ?>"
                  alt="<?= $shop_settings->shop_name; ?>"></a></div>
            <div id="searchtoggle"><i class="bi bi-search"></i></div>
          </div>
        </div>
        <div id="searchbarmob">
          <div id="searchtogglecros"><i class="bi bi-x-circle-fill"></i></div>
          <?= shop_form_open('products', 'class="d-flex search-bar"'); ?>
          <input name="query" class="form-control border-0 bg-transparent py-3 add_item_search" id="product-search"
            type="search" placeholder="What are you looking for?" aria-label="Search">
          <button class="btn searchsubmitBtn" type="submit"><i class="bi bi-search"></i></button>
          <?= form_close(); ?>
          <ul id="autocomplete-suggestions" class="ui-autocomplete"></ul>
        </div>
        <div class=" ps-md-0" id="salemob"></div>

      </div>
    </div>

  </section>

  <!-- logo search bar end -->

  <!-- menu bar -->
  <section>
    <div class="container container-max-width">
      <div class="d-flex align-items-center flex-wrap main-menuTab">
        <div class="mob-catS" id="allcatDiv">
          <button class="btn all-categoryBtn d-flex align-items-center justify-content-between" id="allCatmob"
            type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample"
            aria-controls="offcanvasExample">
            <i class="bi bi-filter-left "></i> All Category
          </button>

          <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample"
            aria-labelledby="offcanvasExampleLabel">
            <div class="offcanvas-header">
              <h5 class="offcanvas-title" id="offcanvasExampleLabel">Categories</h5>
              <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">

              <ol class="list-group list-group-numbered ar-ol">
                <?php
                foreach ($all_categories as $category) {
                  ?>
                  <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto ar-catL">
                      <div class="fw-bold"><a href="<?= site_url('category/' . $category->slug) ?>">
                          <?= strtolower($category->name) == 'otc' ? 'OTC' : $category->name; ?>
                        </a></div>
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
        <div class="mob-menu">
          <nav class="navbar navbar-expand-lg navbar-expand-md  container-max-width">

            <div class="menu-av" id="sourcedivmob">

              <button id="menuiconMob" class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"><i class="bi bi-list"></i></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                  <li class="nav-item">
                    <?php
                    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
                    $domain = $_SERVER['HTTP_HOST'];
                    $url = ($isHttps ? 'https://' : 'http://') . $domain . $_SERVER['REQUEST_URI'];
                    ?>
                    <a class="nav-link <?php if (site_url() == $url) {
                      echo 'active';
                    } ?>" aria-current="page" href="<?= site_url(); ?>">Home</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if (site_url('shop/products') == $url) {
                      echo 'active';
                    } ?>" href="<?= site_url('shop/products'); ?>">Products</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if (site_url('shop/products?promo=yes') == $url) {
                      echo 'active';
                    } ?>" href="<?= site_url('shop/products?promo=yes'); ?>">Promotions</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link <?php if (site_url('shop/bestsellers') == $url) {
                      echo 'active';
                    } ?>" href="<?= site_url('shop/bestsellers'); ?>">Best Sellers</a>
                  </li>
                  


                  <div id="mobnav">

                    </div>

                </ul>

              </div>
            </div>

          </nav>
        </div>
        <div class="shop-icons">
          <div class="text-end" id="cartdiv">

            <span class="cartIcon" id="cart-items">
              <a class="btn  dropdown-toggle border-0" href="#" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M9.33331 24C7.86665 24 6.67998 25.2 6.67998 26.6667C6.67998 28.1333 7.86665 29.3333 9.33331 29.3333C10.8 29.3333 12 28.1333 12 26.6667C12 25.2 10.8 24 9.33331 24ZM22.6666 24C21.2 24 20.0133 25.2 20.0133 26.6667C20.0133 28.1333 21.2 29.3333 22.6666 29.3333C24.1333 29.3333 25.3333 28.1333 25.3333 26.6667C25.3333 25.2 24.1333 24 22.6666 24ZM20.7333 17.3333C21.7333 17.3333 22.6133 16.7867 23.0666 15.96L27.84 7.30666C27.9523 7.10456 28.01 6.87662 28.0072 6.6454C28.0045 6.41418 27.9414 6.18769 27.8242 5.98834C27.707 5.78899 27.5398 5.6237 27.3391 5.50881C27.1384 5.39393 26.9112 5.33344 26.68 5.33332L6.94665 5.33332L5.69331 2.66666L1.33331 2.66666L1.33331 5.33332H3.99998L8.79998 15.4533L6.99998 18.7067C6.02665 20.4933 7.30665 22.6667 9.33331 22.6667L25.3333 22.6667V20L9.33331 20L10.8 17.3333L20.7333 17.3333ZM8.21331 7.99999L24.4133 7.99999L20.7333 14.6667L11.3733 14.6667L8.21331 7.99999Z"
                    fill="#171A1F" />
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
                  <tbody id="cart-body"></tbody>
                  <tfoot id="cart-foot"></tfoot>
                </table>
                <div class="d-flex">
                  <a href="<?= site_url('cart'); ?>"
                    class="btn primary-buttonAV w-100 rounded-1 pb-2 mx-2 text-center">View Cart</a>
                  <?php
                  if ($loggedIn) {
                    ?>
                    <a href="<?= site_url('cart/checkout'); ?>"
                      class="btn primary-buttonAV w-100 rounded-1 pb-2 mx-2 text-center">Checkout</a>
                    <?php
                  } else {
                    ?>
                    <a href="<?= site_url('cart'); ?>"
                      class="btn primary-buttonAV w-100 rounded-1 pb-2 mx-2 text-center">Checkout</a>
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
    <div class="modal fade otpcontainer" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content px-4 rounded-4">
          <div class="modal-header border-0">
            <button type="button" class="modalcloseBtn" data-bs-dismiss="modal" aria-label="Close"><i
                class="bi bi-x-lg"></i></button>
          </div>
          <div class="modal-body ">
            <div class="emailOTP">
              <div class="text-center">
                <h2>Verify OTP</h2>
                <h5 class="fs-4 lh-base">OTP has been sent to <span id="identifier"></span></h5>
              </div>
              <?php
              $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'registerOtpForm'];
              echo form_open('register_otp', $attrib);
              ?>
              <div id="otp" class="inputs d-flex flex-row justify-content-center mt-2">
                <input class="m-1 text-center form-control rounded ap-otp-input-reg" type="tel" name="opt_part1"
                  id="register_otp_1" data-index="0" maxlength="1" />
                <input class="m-1 text-center form-control rounded ap-otp-input-reg" type="tel" name="opt_part2"
                  id="register_otp_2" data-index="1" maxlength="1" />
                <input class="m-1 text-center form-control rounded ap-otp-input-reg" type="tel" name="opt_part3"
                  id="register_otp_3" data-index="2" maxlength="1" />
                <input class="m-1 text-center form-control rounded ap-otp-input-reg" type="tel" name="opt_part4"
                  id="register_otp_4" data-index="3" maxlength="1" />
                <input class="m-1 text-center form-control rounded ap-otp-input-reg" type="tel" name="opt_part5"
                  id="register_otp_5" data-index="4" maxlength="1" />
                <input class="m-1 text-center form-control rounded ap-otp-input-reg" type="tel" name="opt_part6"
                  id="register_otp_6" data-index="5" maxlength="1" />
                <input type="hidden" id="identifier_input" name="identifier_input" value="" />
              </div>
              <div class="text-center">
                <h6 class="m-0 mt-2"><span id="register-clock"></span> <span class="ms-2 fw-semibold opacity-50"
                    id="registerOTP">Resend OTP </span></h6>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pb-4">
            <button type="submit" id="registerOtpBtn" class="btn  text-white continueBtn rounded w-75 mx-auto mt-0"
              data-bs-toggle="modal" data-bs-target="#exampleModal">Login</button>
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

            <button type="button" class="modalcloseBtn" data-bs-dismiss="modal" aria-label="Close"><i
                class="bi bi-x-lg"></i></button>
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
                <input class="m-1 text-center form-control rounded ap-otp-input" type="tel" name="opt_part1"
                  id="login_otp_1" data-index="0" maxlength="1" />
                <input class="m-1 text-center form-control rounded ap-otp-input" type="tel" name="opt_part2"
                  id="login_otp_2" data-index="1" maxlength="1" />
                <input class="m-1 text-center form-control rounded ap-otp-input" type="tel" name="opt_part3"
                  id="login_otp_3" data-index="2" maxlength="1" />
                <input class="m-1 text-center form-control rounded ap-otp-input" type="tel" name="opt_part4"
                  id="login_otp_4" data-index="3" maxlength="1" />
                <input class="m-1 text-center form-control rounded ap-otp-input" type="tel" name="opt_part5"
                  id="login_otp_5" data-index="4" maxlength="1" />
                <input class="m-1 text-center form-control rounded ap-otp-input" type="tel" name="opt_part6"
                  id="login_otp_6" data-index="5" maxlength="1" />
                <input type="hidden" id="identifierl_input" name="identifier_input" value="" />
              </div>
              <div id="error" style="color: red; text-align: center"></div>
              <div class="text-center">
                <h6 class="m-0 mt-2"><span id="login-clock"></span> <span class="ms-2 fw-semibold opacity-50"
                    id="loginOTP">Resend OTP</span></h6>
              </div>
            </div>

          </div>
          <div class="modal-footer border-0 pb-4">
            <button type="submit" id="loginOtpBtn"
              class="btn  text-white continueBtn rounded w-75 mx-auto mt-0">Login</button>
          </div>
          <?= form_close(); ?>

        </div>
      </div>
    </div>
    <!-- Login Modal Ends -->
    <?php
  }
  ?>

  <div class="offcanvas-backdropaddP fade"></div>
  <div class="offcanvas offcanvas-end addcartcanvas " data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
    aria-labelledby="offcanvasWithBothOptionsLabel">
    <div class="offcanvas-header">

      <button type="button" class="btn-close offcanvasClose" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body px-1">
      <div class="m-2">
        <div class=" row align-items-center" id="product-canvas-body">

        </div>

        <div class="d-flex justify-content-between p-1 align-items-center">
          <div class="addicon">
            <p class="m-0 fs-5 fw-semibold text-start text-dark">Cart Total</p>
          </div>

          <p class="m-0 fs-5 fw-semibold mt-2 text-end text-dark" id="product-canvas-total"></p>

        </div>

        <div class="border-0 pb-4 d-flex flex-nowrap mt-3">
          <button type="submit" class="btn text-white continueBtn w-50 rounded  mx-1 mt-0 fs-6"
            onclick="redirectToCheckout('<?= site_url('cart'); ?>')">
            Checkout
          </button>
          <button type="button" class="btn continueShopping w-50 rounded  mx-1 mt-0 fs-6 offcanvasClose"
            data-bs-dismiss="offcanvas">Continue Shopping</button>
        </div>
      </div>
    </div>
  </div>

  <!-- product popup start -->
  <div class="modal fade" id="productPop" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content px-4 rounded-4">
        <div class="modal-header border-0">

          <button type="button" class="modalcloseBtn" data-bs-dismiss="modal" aria-label="Close"><i
              class="bi bi-x-lg"></i></button>
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
            } else {
              ?>
              <a href="<?= site_url('cart'); ?>" style="color: #fff;text-decoration: none;">
                Checkout
              </a>
              <?php
            }

            ?>

          </button>
          <button type="submit" class="btn text-white continueBtn w-50 rounded  mx-1 mt-0"
            data-bs-dismiss="modal">Continue Shopping</button>
        </div>

      </div>
    </div>
  </div>

  </div>
  <!-- product popup end -->
  
  <!-- Notify Modal Ends -->
  <div class="modal fade" id="notifyModal" tabindex="-1" aria-labelledby="notifyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content px-4 rounded-4">
        <div class="modal-header border-0" style="padding-bottom: 0px;">
          <button type="button" class="modalcloseBtn" data-bs-dismiss="modal" aria-label="Close"><i
              class="bi bi-x-lg"></i></button>
          <h5 class="">Get notified when this product is available</h5>
        </div>
        <div class="modal-body" style="padding-top: 5px;">
          <?php
          $attrib = ['class' => 'validate', 'role' => 'form', 'id' => 'notifyMeForm'];
          echo form_open('notify_me', $attrib);
          ?>
           <span id="notify-response"></span>
          <div id="notify_content" class="d-flex flex-row justify-content-center mt-2">
            <input class="m-1 form-control required" type="email" name="notify_email" id="notify_email"
              data-index="0" />
              
            <button type="button" type="submit" id="notifyMeBtn" class="btn text-white continueBtn rounded w-75 mx-auto">Submit</button>
            <input type="hidden" id="product_input" name="product_input" value="" />
          </div>
          I agree to receive notifications about this item and similar products
          <div class="notify-me-product">
            <div class="product-image-wrapper">
              <img id="notify_product_image" src="" width="100" height="100">
            </div>
            <div class="product-details">
              <div id="notify_product_title"></div>
              <div id="notify_product_price"></div>
              <div id="notify_out_of_stock">Out of stock</div>
            </div>

          </div>
        </div>

        <?= form_close(); ?>
      </div>
    </div>
  </div>

  <!--- End Notify modal -->


  <?php if ($this->session->flashdata('error')) { ?>
    <div class="error-message">
      <?php echo $this->session->flashdata('error');
      exit; ?>
    </div>
  <?php } ?>
  <script>
    $('.login-close').click(function () {
      $(this).parent().parent().parent().removeClass('show');
      $(':root').css('overflow', 'auto');
      $(this).parent().parent().parent().click(function () {
        $('#myaccountForm').hide();
      })

    });

    $('#login-btn-trigger').click(function () {
      $(this).siblings(".myaccountForm.validate ").addClass('show');

      if ($(".dropdown-menu.myaccountForm.validate ").hasClass("show")) {

        $(':root').css('overflow', 'hidden');

      }
      else {
        $(':root').css('overflow', 'auto');
      }


    });
  </script>