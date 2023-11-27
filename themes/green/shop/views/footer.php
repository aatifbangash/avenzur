<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- footer -->
<section class="footer-container" >
      <div class="ft" style="background-image: url(<?= base_url('assets/images/banners/bgbanner.jpg'); ?>); max-width:1440px; margin-inline:auto;">
        <footer class="pt-5 pb-3">

          <div class="container container-max-width">
            <div class="row">
              <div class="col-md-12 col-sm-12">
                <a class="navbar-brand" href="#"><img src="<?= base_url('assets/uploads/logos/'.$shop_settings->logo); ?>" alt="logo" ></a>
              </div>
            </div>
    
            <div class="row pt-5">
              <div class="col-md-5 col-sm-12">
                
              
                  <h6>CR:1010160412</h6>
                  <h6>Pharma Medical Company</h6>
                  <h6>6675, Olaya, Olaya District, 2628</h6>
                  <!--<h6>KSA ,Riyadh ,Olaya main road,Mousa bin nosair street.</h6>
                  <h6>Silicon building no.1, Office 7</h6>-->
                  <h6>Phone No. 0114654636</h6>
                  <h6><a href="mailto:Email info@avenzur.com" class="text-dark text-decoration-none">Email: info@avenzur.com</a></h6>
              
               
              </div>
              <div class="col-md-2 col-sm-12">
                <div>
                  <h6><a href="" class="text-dark text-decoration-none" ><b>Our Top Categories</b></a></h6>
                  <h6><a href="<?= site_url('category/beauty'); ?>" class="text-dark text-decoration-none"> Beauty</a></h6>
                  <h6><a href="<?= site_url('category/herbal'); ?>" class="text-dark text-decoration-none"> Herbal</a></h6>
                  <h6> <a href="<?= site_url('category/medical'); ?>" class="text-dark text-decoration-none">Medicine</a></h6>
                  <h6> <a href="<?= site_url('category/mombaby'); ?>" class="text-dark text-decoration-none">Mom & Baby</a></h6>
                  <h6> <a href="<?= site_url('category/vitamins'); ?>" class="text-dark text-decoration-none">Vitamins</a></h6>
                </div>
              </div>

              <div class="col-md-2 col-sm-12">
                <div>
                  <h6><a href="" class="text-dark text-decoration-none" ><b>Store Policies</b></a></h6>
                  
                  <h6><a href="<?= site_url('shop/page/privacy-policy'); ?>" class="text-dark text-decoration-none"> Privacy Policy</a></h6>
                  <h6> <a href="<?= site_url('shop/page/Terms-Conditions'); ?>" class="text-dark text-decoration-none">Terms & Conditions</a></h6>
                  <h6> <a href="<?= site_url('shop/page/exchange-return-policy'); ?>" class="text-dark text-decoration-none">Refund & Return</a></h6>
                  <h6> <a href="<?= site_url('shop/page/delivery'); ?>" class="text-dark text-decoration-none">Delivery Information</a></h6>
                </div>
              </div>
              
              <div class="col-md-2 col-sm-12">
                <div class="footer-icons">
                  <h6><a href="" class="text-dark text-decoration-none" ><b>About Us</b></a></h6>
                  <h6><a href="<?= site_url('shop/page/about-us'); ?>" class="text-dark text-decoration-none"> About Avenzur</a></h6>
                  <h6><a href="<?= site_url('shop/contact_us'); ?>" class="text-dark text-decoration-none" >Contact Us </a></h6>
                  <h6><a href="<?= site_url('profile'); ?>" class="text-dark text-decoration-none" >My Account </a></h6>
                </div>
              </div>
            </div>
            <div class="row pb-2 align-items-center">
              <div class="col-md-7 col-sm-12">
                <img src="<?= base_url('assets/images/banners/pay.png'); ?>" alt="logo" class="footer-pay w-50 mt-3">
              </div>
              <div class="col-md-5 col-sm-12">
                <div class="mobficon text-center">
                  <h6 class="m-0">
                    <a href="https://www.facebook.com/people/Avenzur/61551081317111/" class="text-dark text-decoration-none mx-2"> <i class="bi bi-facebook"></i></a> 
                    <a href="https://www.linkedin.com/company/avenzur/?viewAsMember=true" class="text-dark text-decoration-none mx-2"> <i class="bi bi-linkedin"></i></a>
                    <a href="https://www.youtube.com/channel/UCrzcYJ1xERstbhGunjgWDLA" class="text-dark text-decoration-none mx-2"> <i class="bi bi-youtube"></i></a> 
                    <a href="https://twitter.com/aveznur" class="text-dark text-decoration-none mx-2"> <i class="bi bi-twitter"></i></a>
                    <a href="https://instagram.com/avenzurksa?igshid=NTc4MTIwNjQ2YQ==" class="text-dark text-decoration-none mx-2"><i class="bi bi-instagram"></i></a> 
                    <a href="tiktok.com/@avenzur" class="text-dark text-decoration-none mx-2"><i class="bi bi-tiktok"></i></a></h6>
              </div>
              </div>
            </div>
          </div>
          <div class="text-center border-top pt-3 pb-2">
            <p class="m-0 fw-bold copyr">All Rights Reserved Avenzur.com By Pharma Medcial Company</p>
          </div>
        </footer>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/intlTelInput.min.js"></script>
    <!--<script src="<?php //echo $assets; ?>js/intlTelInput.min.js"></script>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/utils.min.js"></script>
    <!--<script src="<?php //echo $assets; ?>js/utils.min.js"></script>-->
    <!-- Add the slick-theme.css if you want default styling -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <!--<link href="<?php //echo $assets; ?>css/slick.css" rel="stylesheet">-->
    <!-- Add the slick-theme.css if you want default styling -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <!--<link href="<?php //echo $assets; ?>css/slick-theme.css" rel="stylesheet">-->
    <!--<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">-->
    <link href="<?php echo $assets; ?>css/owl.carousel.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <!--<link href="<?php //echo $assets; ?>css/owl.theme.default.min.css" rel="stylesheet">-->
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      Weglot.initialize({
          api_key: 'wg_42c9daf242af8316a7b7d92e5a2aa0e55',
          originalLanguage: 'en',
          destinationLanguages: ['ar'],
          autoSwitch: false,
          hide_switcher: true
      });

      Weglot.on("languageChanged", function(newLang, prevLang) {
          console.log("The language on the page just changed to (code): " + newLang)
          console.log("The full name of the language is: " + Weglot.getLanguageName(newLang))
      });

      function switchToArabic() {
        Weglot.switchTo('ar');
      }

      switchToArabic();
    });
    </script>
    <style> 
      .pac-container{
          z-index: 9999 !important;
      }
    </style>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>-->
    <script src="<?= $assets; ?>js/libs.min.js"></script>
    <script src="<?= $assets; ?>js/jquery.toast.min.js"></script>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC9B2FB0OWZb_CrS8Njrdgek7djxBagYek&libraries=places"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <!--<script  src="<?php //echo $assets; ?>js/slick.min.js"></script>-->
    <script  src="<?= $assets; ?>js/ecommerce-main.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <!--<script  src="<?php //echo $assets; ?>js/owl.carousel2.min.js"></script>-->
    <script type="text/javascript">
    var m = '<?= $m; ?>', v = '<?= $v; ?>', products = {}, filters = <?= isset($filters) && !empty($filters) ? json_encode($filters) : '{}'; ?>, shop_color, shop_grid, sorting;

    var cart = <?= isset($cart) && !empty($cart) ? json_encode($cart) : '{}' ?>;
    var site = {base_url: '<?= base_url(); ?>', site_url: '<?= site_url('/'); ?>', shop_url: '<?= shop_url(); ?>', csrf_token: '<?= $this->security->get_csrf_token_name() ?>', csrf_token_value: '<?= $this->security->get_csrf_hash() ?>', settings: {display_symbol: '<?= $Settings->display_symbol; ?>', symbol: '<?= $Settings->symbol; ?>', decimals: <?= $Settings->decimals; ?>, thousands_sep: '<?= $Settings->thousands_sep; ?>', decimals_sep: '<?= $Settings->decimals_sep; ?>', order_tax_rate: false, products_page: <?= $shop_settings->products_page ? 1 : 0; ?>}, shop_settings: {private: <?= $shop_settings->private ? 1 : 0; ?>, hide_price: <?= $shop_settings->hide_price ? 1 : 0; ?>}}

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
<?php if ($message || $warning || $error || $reminder) {
        ?>
$(document).ready(function() {
    <?php if ($message) {
            ?>
        sa_alert('<?=lang('success'); ?>', '<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($message))); ?>');
    <?php
        }
        if ($warning) {
            ?>
        sa_alert('<?=lang('warning'); ?>', '<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($warning))); ?>', 'warning');
    <?php
        }
        if ($error) {
            ?>
        sa_alert('<?=lang('error'); ?>', '<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($error))); ?>', 'error', 1);
    <?php
        }
        if ($reminder) {
            ?>
        sa_alert('<?=lang('reminder'); ?>', '<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($reminder))); ?>', 'info');
    <?php
        } ?>
});
<?php
    } ?>
   


</script>

    <script>
      $(document).ready(function () {

        const dropdown = document.getElementById('languageDropdown');
        dropdown.addEventListener('click', function (event) {
          const target = event.target;
          
          // Check if the clicked element is a list item with data-lang attribute
          if (target.tagName === 'A' && target.hasAttribute('data-lang')) {
            const selectedLang = target.getAttribute('data-lang');
            // Perform your actions based on the selected language (EN or AR)
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
                url: '<?php echo base_url();?>shop/currencyupdate',
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
                    //bootbox.alert('<?=lang('select_above');?>');
                   // $('#add_item').focus();
                    
                
                $.ajax({
                    type: 'get',
                    url: '<?php echo base_url();?>shop/suggestions',
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
                   
                   window.open(ui.item.plink,'_self');
                    if (row)
                        $(this).val('');
                } else {
                    //bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        }).data('ui-autocomplete')._renderItem = function(ul, item){
            return $("<li class='ui-autocomplete-row'></li>")
              .data("item.autocomplete", item)
              //.append( "<a>" + "<img style='width:35px;height:35px' src='" +site.site_url+"assets/uploads/"+ item.image + "' /> " + item.label+ "</a>" )  
              .append( "<a style='text-decoration:none;color:#000;padding:6px;'>" + item.label + "</a><hr />" )
              .appendTo(ul);
          };

        $('.ui-autocomplete-input').keydown(function(event)
        { 
          if(event.keyCode == 13) 
          {
          $('form#product-search-form').submit();
          return false; 
          }
        });

        var $uiIdElement = $("#ui-id-1").detach();
    
        // Append the detached element to the form with the class "d-flex search-bar"
        $("#autocomplete-suggestions").append($uiIdElement);
      });
    </script>
    <script src="<?= $assets; ?>js/jquery-ui.min.js"></script>
    <script src="<?= $assets; ?>js/jquery-ui.js"></script>
  </body>
</html>