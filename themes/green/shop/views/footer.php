<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- footer -->
<style>
  #live-chat a {
    display: -ms-flexbox;
    display: flex;
    -ms-flex-pack: center;
    justify-content: center;
    -ms-flex-align: center;
    align-items: center;
    width: 2.9375rem;
    height: 2.9375rem;
    background-color: #1ed743;
    border-radius: 50%;
  }

  .wg-default {
    display: none;
  }
</style>
<section class="footer-container mb-4">
  <div class="container container-max-width">
    <div class="ft" style="background-image: url(<?= base_url('assets/images/banners/bgbanner.jpg'); ?>);">
      <footer class="pt-5 pb-3">

        <div class="container container-max-width">
          <div class="row">
            <div class="col-md-12 col-sm-12">
              <a class="navbar-brand" href="#"><img
                  src="<?= base_url('assets/uploads/logos/' . $shop_settings->logo); ?>" alt="logo"></a>
            </div>
          </div>

          <div class="row pt-5">
            <div class="col-lg-5 col-md-5 col-sm-12">
              <h5 class="first-h5 footer-header-collapse arrow-down"><a
                  class="text-dark text-decoration-none"><b>Contact Us</b></a> </h5>
              <ul class="contact-info hide ">
                <!--<li><b> CR: </b> 1010160412</li>
              <li>شركة فرماء الطبية</li>
              <li>٦٦٧٥ ، العليا، حي العليا، ٢٦٢٨</li>
              <li> الرياض , 12241</li>-->
                <!--<h6>KSA ,Riyadh ,Olaya main road,Mousa bin nosair street.</h6>
                    <h6>Silicon building no.1, Office 7</h6>-->
                <li><b> Phone No: </b> 114654636</li>
                <!--<li><a href="mailto:Email info@avenzur.com" class="text-dark text-decoration-none"> <b> Email: </b>
                  info@avenzur.com</a></li>-->
              </ul>

            </div>
            <div class="col-lg-2 col-md-3 col-sm-12">
              <div>
                <h5 class="footer-header-collapse arrow-down"><a class="text-dark text-decoration-none"><b>Our Top
                      Categories</b></a></h5>
                <ul class="hide">
                  <li>
                    <a href="<?= site_url('category/beauty'); ?>" class="text-dark text-decoration-none"> Beauty</a>
                  </li>
                  <li>
                    <a href="<?= site_url('category/herbal'); ?>" class="text-dark text-decoration-none"> Herbal</a>
                  </li>
                  <li> <a href="<?= site_url('category/medical'); ?>"
                      class="text-dark text-decoration-none">Medicine</a></li>
                  <li> <a href="<?= site_url('category/mombaby'); ?>" class="text-dark text-decoration-none">Mom &
                      Baby</a></li>
                  <li> <a href="<?= site_url('category/vitamins'); ?>"
                      class="text-dark text-decoration-none">Vitamins</a> </li>
                </ul>
              </div>
            </div>

            <div class="col-lg-2 col-md-2 col-sm-12">
              <div>
                <h5 class="footer-header-collapse arrow-down"><a class="text-dark text-decoration-none"><b>Store
                      Policies</b></a></h5>

                <ul class="hide">
                  <li> <a href="<?= site_url('shop/page/privacy-policy'); ?>" class="text-dark text-decoration-none">
                      Privacy
                      Policy</a> </li>

                  <li> <a href="<?= site_url('shop/page/Terms-Conditions'); ?>"
                      class="text-dark text-decoration-none">Terms
                      & Conditions</a> </li>
                  <li> <a href="<?= site_url('shop/page/exchange-return-policy'); ?>"
                      class="text-dark text-decoration-none">Refund & Return</a> </li>

                  <li> <a href="<?= site_url('shop/page/delivery'); ?>" class="text-dark text-decoration-none">Delivery
                      Information</a> </li>
                </ul>
              </div>
            </div>

              <div class="col-lg-2 col-md-2 col-sm-12">
                <div class="footer-icons">
                  <h5 class="footer-header-collapse arrow-down"><a class="text-dark text-decoration-none" ><b>About Us</b></a></h5>
                  <ul class="hide">
                    <li> 
                    <a href="<?= site_url('shop/page/about-us'); ?>" class="text-dark text-decoration-none"> About Avenzur</a></li>
                    <li> <a href="<?= site_url('shop/contact_us'); ?>" class="text-dark text-decoration-none" >Contact Us </a> </li>
                    <li> <a href="<?= site_url('profile'); ?>" class="text-dark text-decoration-none" >My Account </a> </li>
                    <li> <a href="<?= site_url('shop/blog'); ?>" class="text-dark text-decoration-none" >Blog </a> </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-between pb-2 align-items-center  footer-end">
              
              <img src="<?= base_url('assets/images/banners/pay-new.png'); ?>" alt="logo" class="footer-pay w-50 mt-3">
              
              
                <div class="mobficon justify-content-start mt-3" style="text-align:center;">
                  <img width="70" style="margin-bottom: 20px;" src="<?= base_url('assets/images/gold-logo.png'); ?>" />
                  <h6 class="m-0">
                    <a href="https://www.facebook.com/people/Avenzur/61553215776690/?mibextid=9R9pXO" class="text-dark text-decoration-none mx-2"> <i class="bi bi-facebook"></i></a>
                    <a href="https://www.linkedin.com/company/avenzur/?viewAsMember=true" class="text-dark text-decoration-none mx-2"> <i class="bi bi-linkedin"></i></a>
                    <a href="https://www.youtube.com/channel/UCrzcYJ1xERstbhGunjgWDLA" class="text-dark text-decoration-none mx-2"> <i class="bi bi-youtube"></i></a>
                    <a href="https://x.com/avenzurworld?s=11" class="text-dark text-decoration-none mx-2"> <i class="bi bi-twitter"></i></a>
                    <a href="https://www.instagram.com/avenzurworld?igsh=MmZicnZnZHZ2aGhl" class="text-dark text-decoration-none mx-2"><i class="bi bi-instagram"></i></a>
                    <a href="https://www.tiktok.com/@avenzur?_t=8i4BKIMkJVK&_r=1" class="text-dark text-decoration-none mx-2"><i class="bi bi-tiktok"></i></a>
                    <a href="https://www.snapchat.com/add/avenzurworld" class="text-dark text-decoration-none mx-2"><i class="bi bi-snapchat"></i></a>
                  </h6>
                </div>
              
            </div>
          </div>
          <div class="text-center border-top pt-3 pb-2">
            <p class="m-0 fw-bold copyr">Copyright &copy; Avenzur, All rights reserved</p>
          </div>
          <div id="live-chat" style="position: fixed;z-index: 999;bottom: 0.2rem;right: 2rem;">
            <a href="https://api.whatsapp.com/send?phone=966551251997" title="whatsapp chat" target="_blank" rel="nofollow noreferrer">
            <svg xmlns="http://www.w3.org/2000/svg" width="27" viewBox="0 0 448 512">
              <path fill="#ffffff"
                d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z">
              </path>
            </svg>
          </a>
        </div>
      </footer>
    </div>
  </div>

</section>
<!-- footer end -->
<link href="<?= $assets; ?>css/fontfamily.css" rel="stylesheet">
<!--<link href="<?php //echo $assets; ?>css/bootstrap.min.css" rel="stylesheet">-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!--<link href="<?php //echo $assets; ?>css/bootstrap-icons.css" rel="stylesheet">-->
<link href="<?= $assets; ?>css/libs.min.css" rel="stylesheet">
<link href="<?= $assets; ?>css/jquery.toast.min.css?<?php echo time(); ?>" rel="stylesheet">
<link rel="stylesheet" href="<?= $assets; ?>build/css/intlTelInput.css">
<!--<script src="<?php //echo $assets; ?>js/jquery.min.js"></script>-->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/intlTelInput.min.js"></script> -->
<!--<script src="<?php //echo $assets; ?>js/intlTelInput.min.js"></script>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/utils.min.js"></script>
<!--<script src="<?php //echo $assets; ?>js/utils.min.js"></script>-->
<!-- Add the slick-theme.css if you want default styling -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css" />
<!--<link href="<?php //echo $assets; ?>css/slick.css" rel="stylesheet">-->
<!-- Add the slick-theme.css if you want default styling -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css" />
<!--<link href="<?php //echo $assets; ?>css/slick-theme.css" rel="stylesheet">-->
<!--<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">-->
<link href="<?php echo $assets; ?>css/owl.carousel.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css"
  href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
<!--<link href="<?php //echo $assets; ?>css/owl.theme.default.min.css" rel="stylesheet">-->
<script type="text/javascript" src="https://cdn.weglot.com/weglot.min.js"></script>
<!--<script src="<?php //echo $assets; ?>js/weglot.min.js"></script>-->
<script>
  Weglot.initialize({
    api_key: 'wg_42c9daf242af8316a7b7d92e5a2aa0e55',
    translate_search: true,
    search_forms: "#product-search",
    search_parameter: "query"
  });

  var currentLanguage = Weglot.getCurrentLang();
  if (currentLanguage === 'en') {
    // Change banner for English
    console.log('Current Language: ' + currentLanguage);
    //alert('Current Language: '+currentLanguage);

  } else if (currentLanguage === 'ar') {
    // Change banner for French
    console.log('Current Language: ' + currentLanguage);
    //alert('Current Language: '+currentLanguage);
  }

  Weglot.on("languageChanged", function (newLang, prevLang) {

    console.log("The language on the page just changed to (code): " + newLang)
    console.log("The full name of the language is: " + Weglot.getLanguageName(newLang))

    var carouselItems = document.querySelectorAll('.carousel-item');
    if (newLang === 'en') {

      if (document.getElementById('honst-page-banner')) {
          document.getElementById('honst-page-banner').src = site.site_url + '/assets/images/banners/honst_2024-06-25_en.png?timestamp=' + Date.now();
        }
      if (document.getElementById('banner-pr-detail-img')) {
        document.getElementById('banner-pr-detail-img').src = site.site_url + '/assets/images/banners/Mobily-En.jpg?timestamp=' + Date.now();
      }

      if (carouselItems.length > 0) {
        var imgname1 = carouselItems[0].querySelector('img').src;
        var imgname2 = carouselItems[1].querySelector('img').src;
        var imgname3 = carouselItems[1].querySelector('img').src;

        var parts1 = imgname1.split('/');
        var lastPart1 = parts1[parts1.length - 1];
        var imageName_1 = lastPart1.split('?')[0];
        imageName_1 = imageName_1.slice(0, -4);
        var parts2 = imgname2.split('/');
        var lastPart2 = parts2[parts2.length - 1];
        var imageName_2 = lastPart2.split('?')[0];
        imageName_2 = imageName_2.slice(0, -4);

        var parts3 = imgname3.split('/');
        var lastPart3 = parts3[parts3.length - 1];
        var imageName_3 = lastPart3.split('?')[0];
        imageName_3 = imageName_3.slice(0, -4);

        if (imageName_1.endsWith('-ar')) {
          imageName_1 = imageName_1.slice(0, -3);
        }

        if (imageName_2.endsWith('-ar')) {
          imageName_2 = imageName_2.slice(0, -3);
        }

        if (imageName_3.endsWith('-ar')) {
          imageName_3 = imageName_3.slice(0, -3);
        }

        carouselItems[0].querySelector('img').src = site.site_url + '/assets/uploads/' + imageName_1 + '.jpg?timestamp=' + Date.now();
        carouselItems[1].querySelector('img').src = site.site_url + '/assets/uploads/' + imageName_2 + '.jpg?timestamp=' + Date.now();
        carouselItems[2].querySelector('img').src = site.site_url + '/assets/uploads/' + imageName_3 + '.jpg?timestamp=' + Date.now();
        // set english promo banner
        document.getElementById('promo-banner-1').src = site.site_url + '/assets/images/banners/promo_offers_2024-06-12_en.jpg?timestamp=' + Date.now();
        document.getElementById('skincare-banner-1').src = site.site_url + '/assets/images/banners/SkinCare_Banner_2024-04-16.jpg?timestamp=' + Date.now();
        document.getElementById('vitamins-banner-1').src = site.site_url + '/assets/images/banners/Vitamins_Banner_2024-04-16.jpg?timestamp=' + Date.now();
        document.getElementById('makeup-banner-1').src = site.site_url + '/assets/images/banners/Makeup_Banner_2024-04-16.jpg?timestamp=' + Date.now();
        document.getElementById('supplements-banner-1').src = site.site_url + '/assets/images/banners/Supplements_Banner_2024-04-17.jpg?timestamp=' + Date.now();
        document.getElementById('mombaby-banner-1').src = site.site_url + '/assets/images/banners/momBaby.jpg?timestamp=' + Date.now();
        document.getElementById('perc-banner-1').src = site.site_url + '/assets/images/banners/persC.jpg?timestamp=' + Date.now();
      } else {
        if (document.getElementById('promo-page-banner-1')) {
          document.getElementById('promo-page-banner-1').src = site.site_url + '/assets/images/banners/promo_inner_2024-06-12_en.jpg?timestamp=' + Date.now();
        }
        
        document.getElementById('promo-page-banner-2').src = site.site_url + '/assets/images/banners/supplement_inner_banner_2024-06-12_en.jpg?timestamp=' + Date.now();
      }

    } else if (newLang === 'ar') {
      if (document.getElementById('honst-page-banner')) {
      document.getElementById('honst-page-banner').src = site.site_url + '/assets/images/banners/honst_2024-06-25_ar.png?timestamp=' + Date.now();
      }
      if (document.getElementById('banner-pr-detail-img')) {
        document.getElementById('banner-pr-detail-img').src = site.site_url + '/assets/images/banners/Mobily-Ar.jpg?timestamp=' + Date.now();
      }

      if (carouselItems.length > 0) {
        var imgname1 = carouselItems[0].querySelector('img').src;
        var imgname2 = carouselItems[1].querySelector('img').src;
        var imgname3 = carouselItems[2].querySelector('img').src;

        var parts1 = imgname1.split('/');
        var lastPart1 = parts1[parts1.length - 1];
        var imageName_1 = lastPart1.split('?')[0];
        imageName_1 = imageName_1.slice(0, -4);
        var parts2 = imgname2.split('/');
        var lastPart2 = parts2[parts2.length - 1];
        var imageName_2 = lastPart2.split('?')[0];
        imageName_2 = imageName_2.slice(0, -4);
        var parts3 = imgname3.split('/');
        var lastPart3 = parts3[parts3.length - 1];
        var imageName_3 = lastPart3.split('?')[0];
        imageName_3 = imageName_3.slice(0, -4);

        if (imageName_1.endsWith('-ar')) {

        } else {
          imageName_1 += '-ar';
        }

        if (imageName_2.endsWith('-ar')) {

        } else {
          imageName_2 += '-ar';
        }

        if (imageName_3.endsWith('-ar')) {

        } else {
          imageName_3 += '-ar';
        }

        carouselItems[0].querySelector('img').src = site.site_url + '/assets/uploads/' + imageName_1 + '.jpg?timestamp=' + Date.now();
        carouselItems[1].querySelector('img').src = site.site_url + '/assets/uploads/' + imageName_2 + '.jpg?timestamp=' + Date.now();
        carouselItems[2].querySelector('img').src = site.site_url + '/assets/uploads/' + imageName_3 + '.jpg?timestamp=' + Date.now();

        // set arabic promo banner
        document.getElementById('promo-banner-1').src = site.site_url + '/assets/images/banners/promo_offers_2024-06-12_ar.jpg?timestamp=' + Date.now();
        document.getElementById('skincare-banner-1').src = site.site_url + '/assets/images/banners/SkinCare_Banner_2024-04-16_ar.jpg?timestamp=' + Date.now();
        document.getElementById('vitamins-banner-1').src = site.site_url + '/assets/images/banners/Vitamins_Banner_2024-04-16_ar.jpg?timestamp=' + Date.now();
        document.getElementById('makeup-banner-1').src = site.site_url + '/assets/images/banners/Makeup_Banner_2024-04-16_ar.jpg?timestamp=' + Date.now();
        document.getElementById('supplements-banner-1').src = site.site_url + '/assets/images/banners/Supplements_Banner_2024-04-17_ar.jpg?timestamp=' + Date.now();
        document.getElementById('mombaby-banner-1').src = site.site_url + '/assets/images/banners/momBaby_ar.jpg?timestamp=' + Date.now();
        document.getElementById('perc-banner-1').src = site.site_url + '/assets/images/banners/persC_ar.jpg?timestamp=' + Date.now();
      } else {
        if (document.getElementById('promo-page-banner-1')) {
          document.getElementById('promo-page-banner-1').src = site.site_url + '/assets/images/banners/promo_inner_2024-06-12_ar.jpg?timestamp=' + Date.now();
        }
        
        document.getElementById('promo-page-banner-2').src = site.site_url + '/assets/images/banners/supplement_inner_banner_2024-06-12_ar.jpg?timestamp=' + Date.now();
      }
    }

  })
</script>
<style>
  .pac-container {
    z-index: 9999 !important;
  }
</style>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>-->
<script src="<?= $assets; ?>js/libs.min.js"></script>
<script src="<?= $assets; ?>js/jquery.toast.min.js"></script>
<?php if ($v == 'addresses' || $v == 'checkout') { ?>
  <script type="text/javascript"
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC9B2FB0OWZb_CrS8Njrdgek7djxBagYek&libraries=places&callback=initMap"></script>
<?php } ?>
<!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC9B2FB0OWZb_CrS8Njrdgek7djxBagYek&callback=initMap"></script> -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<!--<script  src="<?php //echo $assets; ?>js/slick.min.js"></script>-->
<script src="<?= $assets; ?>js/ecommerce-main.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<!--<script  src="<?php //echo $assets; ?>js/owl.carousel2.min.js"></script>-->
<script type="text/javascript">
  var m = '<?= $m; ?>', v = '<?= $v; ?>', products = {}, filters = <?= isset($filters) && !empty($filters) ? json_encode($filters) : '{}'; ?>, shop_color, shop_grid, sorting;

  var cart = <?= isset($cart) && !empty($cart) ? json_encode($cart) : '{}' ?>;
  var site = { base_url: '<?= base_url(); ?>', site_url: '<?= site_url('/'); ?>', shop_url: '<?= shop_url(); ?>', csrf_token: '<?= $this->security->get_csrf_token_name() ?>', csrf_token_value: '<?= $this->security->get_csrf_hash() ?>', settings: { display_symbol: '<?= $Settings->display_symbol; ?>', symbol: '<?= $Settings->symbol; ?>', decimals: <?= $Settings->decimals; ?>, thousands_sep: '<?= $Settings->thousands_sep; ?>', decimals_sep: '<?= $Settings->decimals_sep; ?>', order_tax_rate: false, products_page: <?= $shop_settings->products_page ? 1 : 0; ?> }, shop_settings: { private: <?= $shop_settings->private ? 1 : 0; ?>, hide_price: <?= $shop_settings->hide_price ? 1 : 0; ?> } }

  var lang = {};
  lang.page_info = '<?= lang('page_info'); ?>';
  lang.cart_empty = '<?= lang('empty_cart'); ?>';
  lang.item = '<?= lang('item'); ?>';
  lang.items = '<?= lang('items'); ?>';
  lang.unique = '<?= lang('unique'); ?>';
  lang.total_items = '<?= lang('total_items'); ?>';
  lang.total_unique_items = '<?= lang('total_unique_items'); ?>';
  lang.tax = '<?= lang('tax'); ?>';
  lang.shipping = '<?= lang('shipping'); ?>';
  lang.total_w_o_tax = '<?= lang('total_w_o_tax'); ?>';
  lang.product_tax = '<?= lang('product_tax'); ?>';
  lang.order_tax = '<?= lang('order_tax'); ?>';
  lang.total = '<?= lang('total'); ?>';
  lang.grand_total = '<?= lang('grand_total'); ?>';
  lang.reset_pw = '<?= lang('forgot_password?'); ?>';
  lang.type_email = '<?= lang('type_email_to_reset'); ?>';
  lang.submit = '<?= lang('submit'); ?>';
  lang.error = '<?= lang('error'); ?>';
  lang.add_address = '<?= lang('add_address'); ?>';
  lang.update_address = '<?= lang('update_address'); ?>';
  lang.fill_form = '<?= lang('fill_form'); ?>';
  lang.already_have_max_addresses = '<?= lang('already_have_max_addresses'); ?>';
  lang.send_email_title = '<?= lang('send_email_title'); ?>';
  lang.message_sent = '<?= lang('message_sent'); ?>';
  lang.add_to_cart = '<?= lang('add_to_cart'); ?>';
  lang.out_of_stock = '<?= lang('out_of_stock'); ?>';
  lang.x_product = '<?= lang('x_product'); ?>';
  lang.r_u_sure = '<?= lang('r_u_sure'); ?>';
  lang.x_reverted_back = "<?= lang('x_reverted_back'); ?>";
  lang.delete = '<?= lang('delete'); ?>';
  lang.line_1 = '<?= lang('line1'); ?>';
  lang.line_2 = '<?= lang('line2'); ?>';
  lang.city = '<?= lang('city'); ?>';
  lang.state = '<?= lang('state'); ?>';
  lang.postal_code = '<?= lang('postal_code'); ?>';
  lang.country = '<?= lang('country'); ?>';
  lang.phone = '<?= lang('phone'); ?>';
  lang.is_required = '<?= lang('is_required'); ?>';
  lang.okay = '<?= lang('okay'); ?>';
  lang.cancel = '<?= lang('cancel'); ?>';
  lang.email_is_invalid = '<?= lang('email_is_invalid'); ?>';
  lang.name = '<?= lang('name'); ?>';
  lang.full_name = '<?= lang('full_name'); ?>';
  lang.email = '<?= lang('email'); ?>';
  lang.subject = '<?= lang('subject'); ?>';
  lang.message = '<?= lang('message'); ?>';
  lang.required_invalid = '<?= lang('required_invalid'); ?>';

  update_mini_cart(cart);
</script>


<script type="text/javascript">
  <?php
  //echo 'error'. $error;
  $error = $this->session->userdata('error');
  if ($message || $warning || $error != '' || $reminder) {
    ?>
    $(document).ready(function () {
      <?php if ($message) {
        ?>
        $.notify('<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($message))); ?>', 'success');
        <?php
      }
      if ($warning) {
        ?>
        $.notify('<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($message))); ?>', 'warn');
        <?php
      }
      if ($error != '') {
        ?>
        console.log('error notify should popup');
        $.notify('<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($error))); ?>', 'error');
        <?php
      }
      if ($reminder) {
        ?>
        $.notify('<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($message))); ?>', 'info');
        <?php
      } ?>
    });
    <?php
  } ?>



</script>

<script>

  function redirectToCheckout(redirect_url) {
    window.location.href = redirect_url;
  }

  $(document).ready(function () {

    const inputContainer = $('#inputContainer');

    // Add event listener for input changes
    inputContainer.on('input', '#identity', function () {
      const inputValue = $(this).val();
      const isFirstCharacterDigit = /^\d/.test(inputValue);
      const hasTextOrAlphabet = /[a-zA-Z]/.test(inputValue);

      console.log(inputValue);
      // Change input type and replace accordingly
      if (isFirstCharacterDigit && !hasTextOrAlphabet) {
        // Replace with country code dropdown and phone number input
        inputContainer.html('<input type="text" id="countryCode" class="form-control" style="float: left; width:20%;" value="+966" ready><input type="tel" id="identity_phone" name="identity" class="form-control" style="width: 80%; float: left" placeholder="Enter phone number" required="required">');
      } else if (inputValue === '') {
        // If the input is empty, revert to the original text field
        //inputContainer.html('<input type="text" id="identity" name="identity" class="form-control" placeholder="Please enter email or phone number" required="required">');
      }

      $('#identity').val(inputValue);
      $('#identity_phone').val(inputValue);
      $('#identity_phone').focus();
      //$('#identity').focus();      
    });

    inputContainer.on('input', '#identity_phone', function () {
      const inputValue = $(this).val();

      const hasTextOrAlphabet = /[a-zA-Z]/.test(inputValue);
      console.log(inputValue);
      // Change input type and replace accordingly
      if (hasTextOrAlphabet || inputValue === '') {
        // Replace with country code dropdown and phone number input
        inputContainer.html('<input type="text" id="identity" name="identity" class="form-control" placeholder="Please enter email or phone number" required="required">');
      } else if (inputValue === '') {
        // If the input is empty, revert to the original text field
        // inputContainer.html('<input type="text" id="identity_phone" name="identity" class="form-control" placeholder="Please enter email or phone number" required="required">');
      }
      $('#identity').val(inputValue);
      $('#identity').focus();
    });

    // inputContainerRegister
    const inputContainerRegister = $('#inputContainerRegister');

    // Add event listener for input changes
    inputContainerRegister.on('input', '#email', function () {
      const inputValue = $(this).val();
      const isFirstCharacterDigit = /^\d/.test(inputValue);
      const hasTextOrAlphabet = /[a-zA-Z]/.test(inputValue);

      console.log(inputValue);
      // Change input type and replace accordingly
      if (isFirstCharacterDigit && !hasTextOrAlphabet) {
        // Replace with country code dropdown and phone number input
        inputContainerRegister.html('<input type="text" id="countryCode" class="form-control" style="float: left; width:20%;" value="+966" ready><input type="tel" id="email_phone" name="email" class="form-control" style="width: 80%; float: left" placeholder="Enter phone number" required="required">');
      }
      $('#email').val(inputValue);
      $('#email_phone').val(inputValue);
      $('#email_phone').focus();
      //$('#identity').focus();      
    });

    inputContainerRegister.on('input', '#email_phone', function () {
      const inputValue = $(this).val();

      const hasTextOrAlphabet = /[a-zA-Z]/.test(inputValue);
      console.log(inputValue);
      // Change input type and replace accordingly
      if (hasTextOrAlphabet || inputValue === '') {
        // Replace with country code dropdown and phone number input
        inputContainerRegister.html('<input type="text" id="email" name="email" class="form-control" placeholder="Please enter email or phone number" required="required">');
      } else if (inputValue === '') {
      }
      $('#email').val(inputValue);
      $('#email').focus();
    });

    const dropdown = document.getElementById('languageDropdown');
    dropdown.addEventListener('click', function (event) {
      const target = event.target;

      if (target.tagName === 'A' && target.hasAttribute('data-lang')) {
        const selectedLang = target.getAttribute('data-lang');

        var carouselItems = document.querySelectorAll('.carousel-item');

        if (selectedLang === 'en') {

          Weglot.switchTo(selectedLang);
        } else if (selectedLang === 'ar') {

          Weglot.switchTo(selectedLang);
        }
      }
    });

    const currencydropdown = document.getElementById('currencyDropdown');
    currencydropdown.addEventListener('click', function (event) {
      const target = event.target;

      // Check if the clicked element is a list item with data-lang attribute
      if (target.tagName === 'A' && target.hasAttribute('data-lang')) {
        const selectedCurrency = target.getAttribute('data-lang');
        $.ajax({
          type: 'get',
          url: '<?php echo base_url(); ?>shop/currencyupdate',
          data: {
            currencyName: selectedCurrency,

          },
          success: function (data) {
            location.reload();
          }
        });
      }
    });

    $(".add_item_search").autocomplete({
      source: function (request, response) {

        // $('#add_item').val('').removeClass('ui-autocomplete-loading');
        //bootbox.alert('<?= lang('select_above'); ?>');
        // $('#add_item').focus();


        $.ajax({
          type: 'get',
          url: '<?php echo base_url(); ?>shop/suggestions',
          dataType: "json",
          data: {
            term: request.term,
            category_id: $("#category").val(),
          },
          success: function (data) {
            $(this).removeClass('ui-autocomplete-loading');
            response(data);
          }
        });
      },
      minLength: 1,
      autoFocus: false,
      delay: 250,
      select: function (event, ui) {
        event.preventDefault();
        if (ui.item.id !== 0) {
          // var row = add_invoice_item(ui.item);

          window.open(ui.item.plink, '_self');
          if (row)
            $(this).val('');
        } else {
          //bootbox.alert('<?= lang('no_match_found') ?>');
        }
      }
    }).data('ui-autocomplete')._renderItem = function (ul, item) {
      return $("<li class='ui-autocomplete-row'></li>")
        .data("item.autocomplete", item)
        //.append( "<a>" + "<img style='width:35px;height:35px' src='" +site.site_url+"assets/uploads/"+ item.image + "' /> " + item.label+ "</a>" )
        //.append("<a style='text-decoration:none;color:#000;padding:6px;'>" + item.label + "</a><hr />")
        .append(`
        <div style='display: flex; align-items: center; padding: 6px;'>
              <img src='${site.site_url}assets/uploads/${item.image}' style='width: 50px; height: 50px; margin-right: 10px;' alt='${item.label}'>
              <div style='flex-grow: 1;'>
                  <a style='text-decoration: none; color: #000;'>${item.label}</a>
                  <div style='color: green;'>
                      ${item.row.promo_price !== null ?
            `<span style="text-decoration: line-through; font-size: 12px; color: #888">SAR ${parseFloat(item.original_price).toFixed(2)}</span> <span style="font-weight: bold;"> SAR ${parseFloat(item.row.promo_price).toFixed(2)}</span>` :
            `<span style="font-weight: bold;"> SAR ${parseFloat(item.row.price).toFixed(2)} </span>`
          }
                  </div>
              </div>
          </div>
          <hr />
        `)
        .appendTo(ul);
    };

    $('.ui-autocomplete-input').keydown(function (event) {
      if (event.keyCode == 13) {
        $('form#product-search-form').submit();
        return false;
      }
    });

    var $uiIdElement = $("#ui-id-1").detach();

    // Append the detached element to the form with the class "d-flex search-bar"
    $("#autocomplete-suggestions").append($uiIdElement);
  });

  $('.footer-header-collapse').each(function () {
    $(this).click(function () {
      $(this).next("ul").toggleClass('hide');
      $(this).toggleClass('arrow-down arrow-up')
    })
  })
</script>
<?php if (!$this->loggedIn) { ?>
  <script src="<?= $assets; ?>js/login.js"></script>
<?php } ?>

<script>

  function initializeOtpInput(className) {
    const $inp = $(`.${className}`);
    //const $inp = $(".ap-otp-input");

    $inp.on({
      paste(ev) {
        // Handle Pasting

        const clip = ev.originalEvent.clipboardData.getData("text").trim();
        // Allow numbers only
        if (!/\d{6}/.test(clip)) return ev.preventDefault(); // Invalid. Exit here
        // Split string to Array or characters
        const s = [...clip];
        // Populate inputs. Focus last input.
        $inp
          .val((i) => s[i])
          .eq(5)
          .focus();
      },
      input(ev) {
        // Handle typing

        const i = $inp.index(this);
        if (this.value) $inp.eq(i + 1).focus();
      },
      keydown(ev) {
        // Handle Deleting

        const i = $inp.index(this);
        if (!this.value && ev.key === "Backspace" && i) $inp.eq(i - 1).focus();
      },
    });
  }

  initializeOtpInput('ap-otp-input');
  initializeOtpInput('ap-otp-input-reg');
  initializeOtpInput('ap-otp-input-profile');
  initializeOtpInput('ap-otp-input-checkout');

</script>

<script src="<?= $assets; ?>js/jquery-ui.min.js"></script>
<script src="<?= $assets; ?>js/jquery-ui.js"></script>
<script src="<?= $assets; ?>js/notify.min.js"> </script>


</body>

</html>