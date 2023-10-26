<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Avenzur</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;1,100;1,300;1,400;1,700&family=Manrope:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="<?= $assets; ?>css/ecommerce-main.css" rel="stylesheet">
    <!-- Add the slick-theme.css if you want default styling -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<!-- Add the slick-theme.css if you want default styling -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
<script type="text/javascript" src="https://cdn.weglot.com/weglot.min.js"></script>
<script>
    Weglot.initialize({
        api_key: 'wg_42c9daf242af8316a7b7d92e5a2aa0e55'
    });

    var currentLanguage = Weglot.getCurrentLang();
    if (currentLanguage === 'en') {
        // Change banner for English
        alert('Current Language: '+currentLanguage);
        
    } else if (currentLanguage === 'fr') {
        // Change banner for French
        alert('Current Language: '+currentLanguage);
    }

    Weglot.on("languageChanged", function(newLang, prevLang) {
        console.log("The language on the page just changed to (code): " + newLang)
        console.log("The full name of the language is: " + Weglot.getLanguageName(newLang))
    })
</script>

</head>
  <body>
    <!-- top bar -->
    <section class="top-bar py-1 ">
        <div class="container container-max-width">
            <div class="row align-items-center">
              <div class="col-md-6 topBartxt">
                Avenzur will deliver fast with safe packing in all over the country
              </div>
              <div class="col-md-6 d-flex justify-content-end topbarBtns">
                <div class="dropdown me-2">
                    <a class="btn  dropdown-toggle text-white moblang" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-globe-americas me-1"></i> EN <i class="bi bi-chevron-down ms-2"></i>
                    </a>
                  
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">EN</a></li>
                      <li><a class="dropdown-item" href="#">AR</a></li>
                      
                      
                    </ul>
                </div>
                <!--<div class="dropdown me-2">
                    <a class="btn  dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      SAR <i class="bi bi-chevron-down ms-2"></i>
                    </a>
                  
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">SAR</a></li>
                      <li><a class="dropdown-item" href="#">USD</a></li>
                      <li><a class="dropdown-item" href="#">AED</a></li>
                      
                    </ul>
                </div>-->
                <div class="dropdown">
                <button type="button" class="btn text-white dropdown-toggle px-0 border-0" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                  <i class="bi bi-person-fill"></i>&nbsp; My Account
                </button>
                  <form class="dropdown-menu p-3 myaccountForm" >
                    <div class="mb-3">
                    
                      <label for="exampleDropdownFormEmail2" class="form-label">Username/Email</label>
                      <a href="<?= site_url().'/login#register'; ?>" class="float-end text-decoration-none text-dark">Register</a>
                      <input type="email" class="form-control" id="exampleDropdownFormEmail2" placeholder="Email">
                    </div>
                    <div class="mb-3">
                      <label for="exampleDropdownFormPassword2" class="form-label">Password</label>
                      <a href="#" class="float-end text-decoration-none text-dark">Forgot?</a>
                      <input type="password" class="form-control" id="exampleDropdownFormPassword2" placeholder="Password">
                    </div>
                    <div class="mb-3">
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="dropdownCheck2">
                        <label class="form-check-label" for="dropdownCheck2">
                          Remember me
                        </label>
                      </div>
                    </div>
                    <button type="submit" class="btn primary-buttonAV w-100 rounded-1 pb-2">Login</button>
                  </form>
                </div>
              </div>
              
            </div>
          </div>
    </section>
    <!-- top bar end -->

    <!-- logo search bar -->
    <section class="logo-searchBar">
      <div class="container container-max-width">
        <div class="row  align-items-center justify-content-between py-3">

          <div class="col-lg-2 col-md-3  mb-2">
            <div class="logosearchMob">

              <div> <a class="navbar-brand" href="<?= site_url(); ?>"><img src="<?= base_url('assets/uploads/logos/'.$shop_settings->logo); ?>" alt="<?= $shop_settings->shop_name; ?>"></a></div>
            <div id="searchtoggle"><i class="bi bi-search"></i></div>
            </div>
           
             

          </div>
          <div class="col-lg-7 col-md-8" id="searchbarmob">
            <div id="searchtogglecros"><i class="bi bi-x-circle-fill"></i></div>
            <form class="d-flex search-bar" role="search" >
              
              <select class="form-select w-auto bg-transparent border-0 ps-4 categorySelect" aria-label="Default select">
                <option selected>Category</option>
                <?php
                  foreach($all_categories as $category){
                    ?>
                      <option value="<?= $category->id; ?>"><?= $category->name; ?></option>
                    <?php
                  }
                ?>
              </select>
              <input class="form-control border-0 bg-transparent py-3" type="search" placeholder="What are you looking for?" aria-label="Search">
              <button class="btn searchsubmitBtn" type="submit"><i class="bi bi-search"></i></button>
            </form>
          </div>
          <div class="col-lg-2 col-md-1 ps-md-0" id="salemob"></div>

        </div>
      </div>
        
    </section>
    
    <!-- logo search bar end -->

    <!-- menu bar -->
    <section>
      <div class="container container-max-width main-menuTab">
        <div class="row align-items-center">
          <div class="col-md-2 col-sm-2 mob-catS">
            <button class="btn all-categoryBtn d-flex align-items-center justify-content-between" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
              <i class="bi bi-filter-left "></i> All Category
            </button>
            
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
              <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasExampleLabel">Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
              </div>
              <div class="offcanvas-body">           
            
                <ol class="list-group list-group-numbered">
                  <?php
                    foreach($all_categories as $category){
                      ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                          <div class="ms-2 me-auto">
                            <div class="fw-bold"><?= $category->name; ?></div>
                            <?= $category->description; ?>
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
          <div class="col-md-6 col-sm-2 mob-menu">
            <nav class="navbar navbar-expand-lg navbar-expand-md  container-max-width">
              <div class="container-fluid">
                
                <div class="menu-av" id="sourcedivmob">
                  
                  <button id="menuiconMob" class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"><i class="bi bi-list"></i></span>
                  </button>
                  <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0" >
                      <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Home</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link"  href="<?= site_url('shop/products'); ?>">Products</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link"  href="#">About</a>
                      </li>
                    
                     <div id="mobnav"> 

                     </div>
                     
                    </ul>
                    
                  </div>
                </div>
               
               
              </div>
            </nav>
          </div>
          <div class="col-md-4 col-sm-2 shop-icons">
            <div class="text-end" id="shoppingdivMob">

              <!--<span class="shuffleIcon me-2">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <g id="Frame">
                  <path id="Vector" d="M8.8 23.2H6.4C5.76348 23.2 5.15303 22.9471 4.70294 22.497C4.25286 22.047 4 21.4365 4 20.8L4 11.2C4 10.5635 4.25286 9.95302 4.70294 9.50293C5.15303 9.05284 5.76348 8.79999 6.4 8.79999L20 8.79999" stroke="#171A1F" stroke-width="1.92" stroke-miterlimit="10"/>
                  <path id="Vector_2" d="M23.2 8.79999H25.6C26.2365 8.79999 26.847 9.05284 27.2971 9.50293C27.7471 9.95302 28 10.5635 28 11.2L28 20.8C28 21.4365 27.7471 22.047 27.2971 22.497C26.847 22.9471 26.2365 23.2 25.6 23.2L12 23.2" stroke="#171A1F" stroke-width="1.92" stroke-miterlimit="10"/>
                  <path id="Vector_3" d="M16 4.79999L20 8.79999L16 12.8" stroke="#171A1F" stroke-width="1.92" stroke-miterlimit="10" stroke-linecap="square"/>
                  <path id="Vector_4" d="M16 19.2L12 23.2L16 27.2" stroke="#171A1F" stroke-width="1.92" stroke-miterlimit="10" stroke-linecap="square"/>
                  </g>
                  </svg>
                  
              </span>
            <span class="heartIcon me-2">
              <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M26.0832 7.51679C23.5272 4.96079 19.3824 4.96079 16.8264 7.51679C16.5112 7.83199 16.2376 8.17199 16 8.52879C15.7624 8.17199 15.4888 7.83199 15.1736 7.51759C12.6176 4.96159 8.47282 4.96159 5.91682 7.51759C3.36082 10.0736 3.36082 14.2184 5.91682 16.7744L16 26.8568L26.0832 16.7736C28.6392 14.2176 28.6392 10.0736 26.0832 7.51679Z" stroke="#171A1F" stroke-width="1.92" stroke-miterlimit="10" stroke-linecap="square"/>
              </svg>
            </span>-->
            
            <span class="cartIcon" id="cart-items">
              <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.33331 24C7.86665 24 6.67998 25.2 6.67998 26.6667C6.67998 28.1333 7.86665 29.3333 9.33331 29.3333C10.8 29.3333 12 28.1333 12 26.6667C12 25.2 10.8 24 9.33331 24ZM22.6666 24C21.2 24 20.0133 25.2 20.0133 26.6667C20.0133 28.1333 21.2 29.3333 22.6666 29.3333C24.1333 29.3333 25.3333 28.1333 25.3333 26.6667C25.3333 25.2 24.1333 24 22.6666 24ZM20.7333 17.3333C21.7333 17.3333 22.6133 16.7867 23.0666 15.96L27.84 7.30666C27.9523 7.10456 28.01 6.87662 28.0072 6.6454C28.0045 6.41418 27.9414 6.18769 27.8242 5.98834C27.707 5.78899 27.5398 5.6237 27.3391 5.50881C27.1384 5.39393 26.9112 5.33344 26.68 5.33332L6.94665 5.33332L5.69331 2.66666L1.33331 2.66666L1.33331 5.33332H3.99998L8.79998 15.4533L6.99998 18.7067C6.02665 20.4933 7.30665 22.6667 9.33331 22.6667L25.3333 22.6667V20L9.33331 20L10.8 17.3333L20.7333 17.3333ZM8.21331 7.99999L24.4133 7.99999L20.7333 14.6667L11.3733 14.6667L8.21331 7.99999Z" fill="#171A1F"/>
              </svg>
              <span class="quantitynum cart-total-items" style="display: none;">1</span>
            </span>
              
            </div>
          
              
              
          </div>
        </div>
      </div>
    </section>
    <!-- menu bar end -->
