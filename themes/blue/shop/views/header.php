<?php defined('BASEPATH') or exit('No direct script access allowed'); ?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript">if (parent.frames.length !== 0) { top.location = '<?= site_url(); ?>'; }</script>
    <title><?= $page_title; ?></title>
    <meta name="description" content="<?= $page_desc; ?>">
    <link rel="shortcut icon" href="<?= $assets; ?>images/edited.svg">
    
    <link href="<?= $assets; ?>css/libs.min.css" rel="stylesheet">
    
    <link href="<?= $assets; ?>css/styles.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/custom/shop.css') ?>" rel="stylesheet"/>
    
    <meta property="og:url" content="<?= isset($product) && !empty($product) ? site_url('product/' . $product->slug) : site_url(); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= $page_title; ?>" />
    <meta property="og:description" content="<?= $page_desc; ?>" />
    <link href="<?= $assets; ?>css/jquery-ui.css?<?php echo time(); ?>" rel="stylesheet">
    <meta property="og:image" content="<?= isset($product) && !empty($product) ? base_url('assets/uploads/' . $product->image) : base_url('assets/uploads/logos/' . $shop_settings->logo); ?>" />
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
   
     <link href="https://fonts.googleapis.com/css?family=Montserrat:700%2C500%2C400%7CRoboto:400&amp;display=swap" rel="stylesheet" property="stylesheet" media="all" type="text/css">


<!-- Css for country code -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/css/intlTelInput.min.css" />
    <link rel="stylesheet" href="<?= $assets; ?>build/css/intlTelInput.css">
  <!-- JS for country code -->
  <!--<script src="<?= $assets; ?>build/js/intlTelInput.min.js"></script>-->
    <script src="<?= $assets; ?>build/js/intlTelInput.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/intlTelInput.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/utils.min.js"></script>

</head>
<style>






.blue, .blue .theme {
    background-color: unset !important;
}
button.btn.btn-secondary.dropdown-toggle.globe {
    background: white !important;
    padding: 10px;
}
.padding-bottom-md {
    padding-bottom: 30px !important;
    padding-top: 30px !important;
    background-color: #ffffff00 !important;
    bottom: 0;
    color: #8b8b8b !important;
}

 /*.ui-autocomplete-row*/
 /*     {*/
 /*       padding:8px;*/
 /*       background-color: #f4f4f4;*/
 /*       border-bottom:1px solid #ccc;*/
 /*       font-weight:bold;*/
 /*     }*/
 /*     .ui-autocomplete-row:hover*/
 /*     {*/
 /*       background-color: #ddd;*/
 /*     }*/
</style>
<body>
    <section id="wrapper" class="blue">
        <header>
            <!-- Top Header -->
            <section class="top-header">
                <div class="container">
                    <div class="row">
                        <div class="col-xs-12">
                    
    <?php
                                                                        if ($loggedIn) {
                                                                        ?>
                                                                
                                                            
                                                              <div class="dropdown">
                                                                    <a href="#" class="dropdown-toggle user" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                                                        <?= lang('hi') . ' ' . $loggedInUser->first_name; ?> <span class="caret"></span>
                                                                    </a>
                                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                                        <li class=""><a href="<?= site_url('profile'); ?>"><i class="mi fa fa-user"></i> <?= lang('profile'); ?></a></li>
                                                                        <li class=""><a href="<?= shop_url('orders'); ?>"><i class="mi fa fa-heart"></i> <?= lang('orders'); ?></a></li>
                                                                        <li class=""><a href="<?= shop_url('quotes'); ?>"><i class="mi fa fa-heart-o"></i> <?= lang('quotes'); ?></a></li>
                                                                        <li class=""><a href="<?= shop_url('downloads'); ?>"><i class="mi fa fa-download"></i> <?= lang('downloads'); ?></a></li>
                                                                        <li class=""><a href="<?= shop_url('addresses'); ?>"><i class="mi fa fa-building"></i> <?= lang('addresses'); ?></a></li>
                                                                        <li class="divider"></li>
                                                                        <li class=""><a href="<?= site_url('logout'); ?>"><i class="mi fa fa-sign-out"></i> <?= lang('logout'); ?></a></li>
                                                                    </ul>
                                                                </div>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                
                                                                    <div class="dropdown">
                                                                        <button class="btn dropdown-toggle" type="button" id="dropdownLogin" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                                            <i class="fa fa-user"></i> 
                                                                        </button>
                                                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-login" aria-labelledby="dropdownLogin" data-dropdown-in="zoomIn" data-dropdown-out="fadeOut">
                                                                            <?php  include FCPATH . 'themes' . DIRECTORY_SEPARATOR . $Settings->theme . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'login_form.php'; ?>
                                                                        </div>
                                                                    </div>
                                                                
                                                                <?php
                                                            }
                                                            ?>
                                                            
                                                            
                                                            <ul class="nav navbar-nav header-mobile" >
                                                                <li>
                                                                        <div class="header-action-icon-2 cart-dropdown">
                            
                                                                                     <a class="mini-cart-icon" href="<?= site_url('cart'); ?>">
                                                                                <i class="fa fa-shopping-cart items-c">
                                        
                                                                                        <!--<img alt="cart" src="<?= $assets; ?>images/theme/icons/icon-cart.svg" />-->
                                        
                                                                                    <span class="pro-count  cart-total-items"></span>
                                                                                </i>
                                        
                            </a>
                                                                    
                                                                            <!--<div class="dropdown-menu dropdown-menu-right drop-cart" aria-labelledby="dropdown-cart" style="">-->
                                                                            <!--                    <div id="cart-contents">-->
                                                            
                                                                            <!--                        <table class="table table-condensed table-striped table-cart" id="cart-items"></table>-->
                                                            
                                                                            <!--                        <div class="btn-group btn-group-justified" role="group" aria-label="View Cart and Checkout Button">-->
                                                                            <!--                                    <div class="btn-group">-->
                                                            
                                                                            <!--                        <div class="shopping-cart-footer" id="cart-links">-->
                                                            
                                                                             
                                                                            <!--                            <div class="shopping-cart-button">-->
                                                            
                                                                            <!--                                <a href="<?= site_url('cart'); ?>" class="outline"><?= lang('view_cart'); ?></a>-->
                                                            
                                                                            <!--                                <a href="<?= site_url('cart/checkout'); ?>"><?= lang('checkout'); ?></a>-->
                                                            
                                                                            <!--                            </div>-->
                                                                                                       
                                                            
                                                                                                    
                                                            
                                                                            <!--                    </div>-->
                                                                            <!--                    </div>-->
                                                                            <!--                    </div>-->
                                                                            <!--                    <div id="cart-empty"><p>Please add item to the cart first</p></div>-->
                                                                            <!--            </div>-->
                                                            
                                                                            <!--</div>-->
                                                                        </div>
                                                                </li>
                                                                
                                                              <!--<li><a href="<?= shop_url('wishlist'); ?>"><i class="fa fa-heart"></i> <span class="hidden-xs"></span> (<span id="total-wishlist"><?= $wishlist; ?></span>)</a></li>-->
                                          
                                                        </ul>
                                                            
                                                            
                                                            
                            
                        </div>
                    </div>
                </div>
            </section>
            <!-- End Top Header -->

            <!-- Main Header -->
            <!--<section class="main-header">-->
            <!--    <div class="container padding-y-md">-->
            <!--        <div class="row">-->

            <!--            <div class="col-sm-4 col-md-3 logo">-->

            <!--            </div>-->

            <!--            <div class="col-sm-8 col-md-9 margin-top-lg" style="width:100% !important;">-->
                            
            <!--                <div class="row">-->
            <!--                    <div class="<?= (!$shop_settings->hide_price) ? 'col-sm-8 col-md-6 col-md-offset-3' : 'col-md-6 col-md-offset-6'; ?> search-box">-->
            <!--                        <?= shop_form_open('products', 'id="product-search-form"'); ?>-->
            <!--                        <div class="input-group">-->
            <!--                            <input name="query" type="text" class="add_item_search"  id="product-search" aria-label="Search..." placeholder="<?= lang('search'); ?>">-->
            <!--                            <div class="input-group-btn">-->
            <!--                                <button type="submit" class="btn btn-default btn-search"><i class="fa fa-search"></i></button>-->
            <!--                            </div>-->
            <!--                        </div>-->
                                    <?= form_close(); ?>
                                <!--</div>-->

                                <?php if (!$shop_settings->hide_price) {
                                    ?>
                                <!--<div class="col-sm-4 col-md-3 cart-btn hidden-xs">-->
                                <!--    <button type="button" class="btn btn-theme btn-block dropdown-toggle shopping-cart" id="dropdown-cart" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">-->
                                <!--        <i class="fa fa-shopping-cart margin-right-md"></i>-->
                                <!--        <span class="cart-total-items"></span>-->
                                        <!-- <i class="fa fa-caret-down margin-left-md"></i> -->
                                <!--    </button>-->
                                <!--    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-cart">-->
                                <!--        <div id="cart-contents">-->
                                <!--            <table class="table table-condensed table-striped table-cart" id="cart-items"></table>-->
                                <!--            <div id="cart-links" class="text-center margin-bottom-md">-->
                                <!--                <div class="btn-group btn-group-justified" role="group" aria-label="View Cart and Checkout Button">-->
                                <!--                    <div class="btn-group">-->
                                <!--                        <a class="btn btn-default btn-sm" href="<?= site_url('cart'); ?>"><i class="fa fa-shopping-cart"></i> <?= lang('view_cart'); ?></a>-->
                                <!--                    </div>-->
                                <!--                    <div class="btn-group">-->
                                <!--                        <a class="btn btn-default btn-sm" href="<?= site_url('cart/checkout'); ?>"><i class="fa fa-check"></i> <?= lang('checkout'); ?></a>-->
                                <!--                    </div>-->
                                <!--                </div>-->
                                <!--            </div>-->
                                <!--        </div>-->
                                        <!--<div id="cart-empty"><?= lang('please_add_item_to_cart'); ?></div>-->
                                <!--    </div>-->
                                <!--</div>-->
                                <?php
                                } ?>
            <!--                </div>-->
            <!--            </div>-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</section>-->
            <!-- End Main Header -->

            <!-- Nav Bar -->
            <nav class="navbar navbar-default" role="navigation">
                <div class="container" >
                    <div class="row">
                         
                               <div class="col-md-2 col-xs-5 logo">
                                   <a href="<?= site_url(); ?>">
                                        <img  style= " height: 35px !important; width: 185px !important;    margin-bottom: 0px;" alt="<?= $shop_settings->shop_name; ?>" src="<?= base_url('assets/uploads/logos/'.$shop_settings->logo); ?>" class="img-responsive" />
                                     </a>
                                </div>
                        
                        
                                <div class="col-xs-7 col-md-10">
                                       <div class="navbar-header">
                                                 <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-ex1-collapse">
                                             <span><img src="<?= base_url('assets/uploads/menu-bar.png') ?>" height="20px" width="20px"></span>   
                                            </button>
                                            <div class="collapse navbar-collapse" id="navbar-ex1-collapse">
                                               
                                               
                                                        <ul class="nav navbar-nav">
                                                            
                                                            <!--<li class="<?= $m == 'main' && $v == 'index' ? 'active' : ''; ?>"><a href="<?= base_url(); ?>"><?= lang('home'); ?></a></li>-->
                                                            <?php if ($isPromo) {
                                                                    ?>
                                                            <li class="<?= $m == 'shop' && $v == 'products' && $this->input->get('promo') == 'yes' ? 'active' : ''; ?>"><a href="<?= shop_url('products?promo=yes'); ?>"><?= lang('promotions'); ?></a></li>
                                                            <?php
                                                                } ?>
                                                            <li class="<?= $m == 'shop' && $v == 'products' && $this->input->get('promo') != 'yes' ? 'active' : ''; ?>"><a href="<?= shop_url('products'); ?>"><?= lang('products'); ?></a></li>
                                                            <li class="dropdown">
                                                                <a href="#" class="dropdown-toggle " data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                                                    <?= lang('categories'); ?> <span class="caret"></span>
                                                                </a>
                                                                <ul class="dropdown-menu">
                                                                    <?php
                                                                    foreach ($categories as $pc) {
                                                                        echo '<li class="' . ($pc->subcategories ? 'dropdown dropdown-submenu' : '') . '">';
                                                                        echo '<a ' . ($pc->subcategories ? 'class="dropdown-toggle" data-toggle="dropdown"' : '') . ' href="' . site_url('category/' . $pc->slug) . '">' . $pc->name . '</a>';
                                                                        if ($pc->subcategories) {
                                                                            echo '<ul class="dropdown-menu">';
                                                                            foreach ($pc->subcategories as $sc) {
                                                                                echo '<li><a href="' . site_url('category/' . $pc->slug . '/' . $sc->slug) . '">' . $sc->name . '</a></li>';
                                                                            }
                                                                            echo '<li class="divider"></li>';
                                                                            echo '<li><a href="' . site_url('category/' . $pc->slug) . '">' . lang('all_products') . '</a></li>';
                                                                            echo '</ul>';
                                                                        }
                                                                        echo '</li>';
                                                                    }
                                                                    ?>
                                                                </ul>
                                                            </li>
                                                            
                                                            </ul>
                                                    
                                                     <div class="<?= (!$shop_settings->hide_price) ? 'col-sm-8 col-md-6' : 'col-md-6 col-md-offset-6'; ?> search-box">
                                    <?= shop_form_open('products', 'id="product-search-form"'); ?>
                                    <div class="input-group">
                                        <input name="query" type="text" class="add_item_search"  id="product-search" aria-label="Search..." placeholder="<?= lang('search'); ?>">
                                        <div class="input-group-btn">
                                            <button type="submit" class="btn btn-default btn-search"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                    <?= form_close(); ?>
                                </div>
                                                    
                                                        <ul class="nav navbar-nav header" >
                                                                <li>
                                                                        <div class="header-action-icon-2 cart-dropdown">
                            
                                                                                     <!--<a class="mini-cart-icon" href="<?= site_url('cart'); ?>">-->
                                                                                <i class="fa fa-shopping-cart items-c">
                                        
                                                                                        <!--<img alt="cart" src="<?= $assets; ?>images/theme/icons/icon-cart.svg" />-->
                                        
                                                                                    <span class="pro-count  cart-total-items"></span>
                                                                                </i>
                                        
                            
                                                                    
                                                                            <div class="dropdown-menu dropdown-menu-right drop-cart" aria-labelledby="dropdown-cart" style="">
                                                                                                <div id="cart-contents">
                                                            
                                                                                                    <table class="table table-condensed table-striped table-cart" id="cart-items"></table>
                                                            
                                                                                                    <div class="btn-group btn-group-justified" role="group" aria-label="View Cart and Checkout Button">
                                                                                                                <div class="btn-group">
                                                            
                                                                                                    <div class="shopping-cart-footer" id="cart-links">
                                                            
                                                                             
                                                                                                        <div class="shopping-cart-button">
                                                            
                                                                                                            <a href="<?= site_url('cart'); ?>" class="outline"><?= lang('view_cart'); ?></a>
                                                            
                                                                                                            <a href="<?= site_url('cart/checkout'); ?>"><?= lang('checkout'); ?></a>
                                                            
                                                                                                        </div>
                                                                                                       
                                                            
                                                                                                    
                                                            
                                                                                                </div>
                                                                                                </div>
                                                                                                </div>
                                                                                                <div id="cart-empty"><p>Please add item to the cart first</p></div>
                                                                                        </div>
                                                            
                                                                            </div>
                                                                        </div>
                                                                </li>
                                                                <!--<li class="hidden-xs hidden-sm"> <a href="<?= site_url('cart'); ?>" class="outline"><i class="fa fa-shopping-cart"> <span class="pro-count  cart-total-items"></span></i> </a></li>-->
                                                                         <?php
                                                                        if ($loggedIn) {
                                                                        ?>
                                                                
                                                            
                                                              <li class="dropdown">
                                                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                                                        <?= lang('hi') . ' ' . $loggedInUser->first_name; ?> <span class="caret"></span>
                                                                    </a>
                                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                                        <li class=""><a href="<?= site_url('profile'); ?>"><i class="mi fa fa-user"></i> <?= lang('profile'); ?></a></li>
                                                                        <li class=""><a href="<?= shop_url('orders'); ?>"><i class="mi fa fa-heart"></i> <?= lang('orders'); ?></a></li>
                                                                        <li class=""><a href="<?= shop_url('quotes'); ?>"><i class="mi fa fa-heart-o"></i> <?= lang('quotes'); ?></a></li>
                                                                        <li class=""><a href="<?= shop_url('downloads'); ?>"><i class="mi fa fa-download"></i> <?= lang('downloads'); ?></a></li>
                                                                        <li class=""><a href="<?= shop_url('addresses'); ?>"><i class="mi fa fa-building"></i> <?= lang('addresses'); ?></a></li>
                                                                        <li class="divider"></li>
                                                                        <li class=""><a href="<?= site_url('logout'); ?>"><i class="mi fa fa-sign-out"></i> <?= lang('logout'); ?></a></li>
                                                                    </ul>
                                                                </li>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <li>
                                                                    <div class="dropdown">
                                                                        <button class="btn dropdown-toggle" type="button" id="dropdownLogin" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                                            <i class="fa fa-user"></i> 
                                                                        </button>
                                                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-login" aria-labelledby="dropdownLogin" data-dropdown-in="zoomIn" data-dropdown-out="fadeOut">
                                                                            <?php  include FCPATH . 'themes' . DIRECTORY_SEPARATOR . $Settings->theme . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'login_form.php'; ?>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                                <?php
                                                            }
                                                            ?>
                                                       
                                                              <!--<li><a href="<?= shop_url('wishlist'); ?>"><i class="fa fa-heart"></i> <span class="hidden-xs"></span> (<span id="total-wishlist"><?= $wishlist; ?></span>)</a></li>-->
                                          <li>
                                              
                                              <div class="header-action-icon-2 selectcountry"  style="margin-top: 10px;cursor:pointer;">
                                                  <i class="fa fa-globe"></i>
                                              </div>
                                             
                                                <!--<div class="dropdown">-->
                                                <!--  <button class="btn btn-secondary dropdown-toggle globe" type="button" data-bs-toggle="dropdown" aria-expanded="false">-->
                                                <!--     <i class="fa fa-globe" id="locations-icon" ></i> -->
                                                <!--  </button>-->
                                                   
                                                <!--  <ul class="dropdown-menu"  name="locations" id="locations"  >-->
                                                <!--    <li  class="dropdown-item"  id="Saudi Arabia">Saudi Arabia</li>-->
                                                <!--    <li class="dropdown-item"   id="UAE">UAE</li>-->
                                                <!--    <li  class="dropdown-item"  id="Both">Both</li>-->
                                                <!--  </ul>-->
                                                
                                                <!--</div>-->
                                                <!--<div class="dropdown">-->
                                                <!--  <button class="btn btn-secondary dropdown-toggle globe" type="button" data-bs-toggle="dropdown" aria-expanded="false">-->
                                                <!--     <i class="fa fa-globe" id="locations-icon"></i> -->
                                                <!--  </button>-->
                                                   
                                                <!--  <select class="dropdown-menu"  name="locations" id="locations" >-->
                                                <!--    <option><a class="dropdown-item" href="saudiarabia" value="Saudi Arabia" >Saudi Arabia</a></option>-->
                                                <!--    <option><a class="dropdown-item" href="uae" value="UAE">UAE</a></option>-->
                                                <!--    <option><a class="dropdown-item" href="both" value="Both">Both</a></option>-->
                                                <!--  </select>-->
                                                
                                                <!--</div>
                                                <select name="country"  id="country" style="margin-top: 10px; " >
                                                    
                                                <option value="both">All</option>    
                                                <?php foreach($allCountries as $cdata){ ?>
                                                  <option value="<?=$cdata->code?>"><?=$cdata->name?></option>
                                                <?php } ?>   
                                                </select>
                                                -->
                                             
                                            </li>
                                        </ul>
                                                       
                                            </div>
                                     
         
                                
                                         </div>
                         
                                
                            </div>
                   
                   
                 
                            
                    </div>
                    <div class="<?= (!$shop_settings->hide_price) ? 'col-sm-8 col-md-6' : 'col-md-6 col-md-offset-6'; ?> search-box-mbl">
                                    <?= shop_form_open('products', 'id="product-search-form"'); ?>
                                    <div class="input-group">
                                        <input name="query" type="text" class="add_item_search"  id="product-search" aria-label="Search..." placeholder="<?= lang('search'); ?>">
                                        <div class="input-group-btn">
                                            <button type="submit" class="btn btn-default btn-search"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                    <?= form_close(); ?>
                                </div>
                </div>
                            
                  
                 
               
            </nav>
            
            <!-- End Nav Bar -->
        </header>
        <?php if (DEMO && ($m != 'main' || $v != 'index')) {
                                    ?>
        <!--<div class="page-contents padding-bottom-no">-->
        <!--    <div class="container">-->
        <!--        <div class="alert alert-info margin-bottom-no">-->
        <!--            <p>-->
        <!--                <strong>Shop module is not complete item but add-on to Stock Manager Advance and is available separately.</strong><br>-->
        <!--                This is joint demo for main item (Stock Manager Advance) and add-ons (POS & Shop Module). Please check the item page on codecanyon.net for more info about what's not included in the item and you must read the page there before purchase. Thank you-->
        <!--            </p>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--</div>-->
        <?php
                                } ?>
                                
                                
                                
                                
