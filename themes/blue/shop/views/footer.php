<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (DEMO && ($m == 'main' && $v == 'index')) {
    ?>
<div class="page-contents padding-top-no">
    <div class="container">
        <div class="alert alert-info margin-bottom-no">
            <p>
                <strong>Shop module is not complete item but add-on to Stock Manager Advance and is available separately.</strong><br>
                This is joint demo for main item (Stock Manager Advance) and add-ons (POS & Shop Module). Please check the item page on codecanyon.net for more info about what's not included in the item and you must read the page there before purchase. Thank you
            </p>
        </div>
    </div>
</div>
<?php
} ?>

<section class="footer">
    <div class="container padding-bottom-md">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <!--<div class="title-footer"><span><?= lang('about_us'); ?></span></div>-->
                <a href="<?= site_url(); ?>">
                                <img  style= " height: 58px !important; width: 283px !important;    margin-bottom: 50px;" alt="<?= $shop_settings->shop_name; ?>" src="<?= base_url('assets/uploads/logos/'.$shop_settings->logo); ?>" class="img-responsive" />
                 </a>
                <p style="margin-bottom: 50px !important;">
                    Copyright 2022 © Avenzur *Disclaimer: Statements made, or products sold through this website,
                    have not been evaluated by the United States Food and Drug Administration (FDA).
                    They are not intended to diagnose, treat, cure or prevent any disease.
                   
                </p>
                <p>
                    <!--<i class="fa fa-phone"></i> <span class="margin-left-md"><?= $shop_settings->phone; ?></span>-->
                    <!--<i class="fa fa-envelope margin-left-xl"></i> <span class="margin-left-md"><?= $shop_settings->email; ?></span>-->
                </p>
             <?php if (!empty($pages)) {
                            echo '<ul class="list-inline">';
                             foreach ($pages as $page) {
                                 echo '<li><a href="' . site_url('shop/page/' . $page->slug) . '">' . $page->name . '</a></li>';
                                //   echo '<li><a href="' . site_url('shop/page/' . $page->slug) . '">' . $page->name . '</a></li>';
                                    
                            }
                           echo '</ul>';
                        }?>
                <!--<ul class="list-inline">-->
                    
                <!--    <li><a href="<?= site_url('shop/page/' . $shop_settings->privacy_link); ?>"><?= lang('privacy_policy'); ?></a></li>-->
                <!--     <li>    <a href="<?= site_url('shop/page/' . $shop_settings->terms_link); ?>"><?= lang('terms_conditions'); ?></a></li>-->
                <!--     <li>  <a href="<?= site_url('shop/page/not-healthcare-advice'); ?>"><?= lang('Not_Healthcare_Advice'); ?></a></li>-->
                <!--     <li>  <a href="<?= site_url('shop/blog'); ?>"><?= lang('Blog'); ?></a>  </li>-->
                <!--</ul>-->
            </div>

            <div class="clearfix visible-sm-block"></div>
            <div class="col-md-3 col-sm-6">
                
            </div>

            <div class="col-md-3 col-sm-6">
                
                <!--<div class="title-footer"><span><?= lang('payment_methods'); ?></span></div>-->
             
                <img class="img-responsive" src="<?= $assets; ?>/images/payment-methods.png" alt="Payment Methods">
                
            </div>

        </div>
    </div>

    <!--  <div class="underfoteer" style="background: #F2F3F5 !important;">-->
    <!--  <div class="row">-->
    
    <!--<div class="col-md-12">-->
    <!--      <ul class="list-inline nav center " style="line-height: 0.7 !important;text-align: center !important;">-->
                                <?php
                               // if (DEMO) {
                                    
                               // }
                                ?>
                               
                                <!--<li class="dropdown">-->
                                <!--    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">-->
                                 <!--   <img src="<?= base_url('assets/images/' . $Settings->user_language . '.png'); ?>" alt="">-->
                                 <!--   <span class="hidden-xs">&nbsp;&nbsp;<?= ucwords($Settings->user_language); ?></span>-->
                                 <!--</a>-->
                                 <!--<ul class="dropdown-menu dropdown-menu-right">-->
                                    <?php $scanned_lang_dir = array_map(function ($path) {
                                    return basename($path);
                                }, glob(APPPATH . 'language/*', GLOB_ONLYDIR));
                                    foreach ($scanned_lang_dir as $entry) {
                                        if (file_exists(APPPATH . 'language' . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'shop_lang.php')) {
                                            ?>
                                    <!--<li>-->
                                    <!--    <a href="<?= site_url('main/language/' . $entry); ?>">-->
                                    <!--        <img src="<?= base_url('assets/images/' . $entry . '.png'); ?>" class="language-img">-->
                                    <!--        &nbsp;&nbsp;<?= ucwords($entry); ?>-->
                                    <!--    </a>-->
                                    <!--</li>-->
                                    <?php
                                        }
                                    } ?>
                                </ul>
                            </li>
                          
                             
                            </ul>
        
    </div>
    </div>

</div>
</section>
<style>
 
 .fade.show{ opacity:1;}   
 .modal-body{background-color:#fff;}  
 #subscribeModal .modal-dialog{margin-top:2em;}
</style>

<div class="modal fade" id="subscribeModal" tabindex="-1" role="dialog" style="display: none;" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg">
        <!--<div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
            <i class="fa fa-2x">×</i>
        </button>
    </div>-->
    <div class="modal-body">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-title text-center">
                  <h4>Please select your Country</h4>
                  <p>
                      <div class="form-group">
                          <select name="country"  id="popup_country" class="form-control" style="margin-top: 10px; " >
                              <option value="-1">Select Country</option>
                              <option value="0">All</option>
                                <?php foreach($allCountries as $cdata){ ?>
                                    <option value="<?=$cdata->id?>"><?=$cdata->name?></option>
                                <?php } ?>   
                          </select>
                      </div>
                  </p>
                </div>
            </div>
        </div>
    </div>    
    </div>
</div>
  
 
<a href="#" class="back-to-top text-center" onclick="$('body,html').animate({scrollTop:0},500); return false">
    <i class="fa fa-angle-double-up"></i>
</a> 

</section>

<?php if (!get_cookie('shop_use_cookie') && get_cookie('shop_use_cookie') != 'accepted' && !empty($shop_settings->cookie_message)) {
        ?>
<div class="cookie-warning">
    <div class="bounceInLeft alert alert-info">
        <!-- <a href="<?= site_url('main/cookie/accepted'); ?>" class="close">&times;</a> -->
        <a href="<?= site_url('main/cookie/accepted'); ?>" class="btn btn-sm btn-primary" style="float: right;"><?= lang('i_accept'); ?></a>
        <p>
            <?= $shop_settings->cookie_message; ?>
            <?php if (!empty($shop_settings->cookie_link)) {
            ?>
            <a href="<?= site_url('page/' . $shop_settings->cookie_link); ?>"><?= lang('read_more'); ?></a>
            <?php
        } ?>
        </p>
    </div>
</div>
<?php
    } ?>
    
<script src="<?= $assets; ?>js/libs.min.js"></script>
<script src="<?= $assets; ?>js/scripts.min.js"></script>
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

<?php if ($m == 'shop' && $v == 'product') {
        ?>
<script type="text/javascript">
$(document).ready(function ($) {
  $('.rrssb-buttons').rrssb({
    title: '<?= $product->code . ' - ' . $product->name; ?>',
    url: '<?= site_url('product/' . $product->slug); ?>',
    image: '<?= base_url('assets/uploads/' . $product->image); ?>',
    description: '<?= $page_desc; ?>',
    // emailSubject: '',
    // emailBody: '',
  });
  
 
});
</script>

<?php
    } ?>
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

<script type="text/javascript" src="<?= base_url('assets/custom/shop.js') ?>"></script>
<script>
    if(!get('shop_grid')) {
        store('shop_grid', '.three-col');
    }
</script>
<!-- Vendor JS -->



<script>
    $(document).ready(function () {
        
        
        $('#subscribeModal').modal({backdrop: 'static', keyboard: false}) ; 
        // $('#subscribeModal').modal('show');
        $(".selectcountry").on('click',function(){
            $('#subscribeModal').modal('show');
        });
        $("#popup_country").on('change',function(){
                
                var country = $(this).val();
                if(country != -1)
                {
                     $.ajax({
                            type: 'get',
                            url: '<?php echo base_url();?>shop/globalupdate',
                            data: {
                                countryName: country,
                                
                            },
                            success: function (data) {
                                $('#subscribeModal').modal('hide');
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
        .append( "<a>" + "<img style='width:35px;height:35px' src='" +site.site_url+"assets/uploads/"+ item.image + "' /> " + item.label+ "</a>" )  
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
});
// $("#cart-contents").hide();
$(".cart-dropdown").click(function(){
    $(".drop-cart").toggle();
        // $("#cart-links").toggle();
        //  $("#cart-items").toggle();
    });
</script>
 	<script>
// 		  $("li.dropdown-item").click(function() {
//           var country =  this.id;
//           $.ajax({
//              type:"GET",//or POST
//              url:'shop/countryName',
//              data:country,
      
//           success: function (data) {//once the request successfully process to the server side it will return result here
// 	           alert(data);
// 	        }
            
//   });

//       });
//          $(document).ready(function(){
 
//   $('#country').change(function(){
//     var country_val = $('#country').val();
//     $.ajax({
//      url:"<?php echo base_url('/shop/countryDetails'); ?>",
//         method:"POST",
//      data: {country_val: country_val},
//      dataType: 'json',
//      success: function(response){
//       var len = response.length;
//       $('#suname').text('');
//          // Read values
//         alert(' var uname = response[0].country');
//          //$('#suname').text(uname);
 
//      }
//   });
//   });
//  });
 

 
 

		</script>
		
<script src="<?= $assets; ?>js/jquery-ui.min.js"></script>
<script src="<?= $assets; ?>js/jquery-ui.js"></script> 

</body>
</html>
