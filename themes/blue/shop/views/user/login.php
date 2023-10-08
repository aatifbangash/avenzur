

<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                
                    <div class="row">
                    <div class="col-sm-9 col-md-10">

                        <ul class="nav nav-tabs" role="tablist">
                            
                            <li role="" >  </li>
                            <?php if (!$shop_settings->private) {
    ?>
                            <!--<li role="presentation" class="active"  ><a href="#register" class="register" aria-controls="register" role="tab" data-toggle="tab"><?= lang('register'); ?></a></li>-->
                            <?php
} ?>
                        </ul>

                        <div class="tab-content padding-lg white bordered-light"  style="margin-top:-1px;">
                            <div class="loginform" style="display:none;" >
                            
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="well margin-bottom-no">
                                          
                                         <?php include 'login_form.php'; ?>
                                        </div>
                                    </div>
                             <?php if (!$shop_settings->private) {
     ?>
                                    <div class="col-sm-6">
                                        <h4 class="title"><span><?= lang('register_new_account'); ?></span></h4>
                                        <p>
                                            <?= lang('register_account_info'); ?>
                                        </p>
                                        <a  class="btn btn-primary" id="registerbtn" onclick="registerBtn()" ><?= lang('register'); ?></a>
                                        
                                    </div>
                             <?php
 } ?>
                                </div>
                            </div>

                            <?php if (!$shop_settings->private) {
        ?>
                            <div id="register">
                                  
                                <?php $attrib = ['class' => 'validate', 'role' => 'form'];
                                                echo form_open('register', $attrib); ?>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('first_name', 'first_name'); ?>
                                            <div class="controls">
                                                <?= form_input('first_name', '', 'class="form-control" id="first_name" required="required" pattern=".{3,10}"'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('last_name', 'last_name'); ?>
                                            <div class="controls">
                                                <?= form_input('last_name', '', 'class="form-control" id="last_name" required="required"'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('Country', 'Country'); ?>
                                            <select class="form-control" id="country" name="country" >
                                       
                                         <!--<option value="AE">AE</option>-->
                                       <option value="">Select Country</option>
                                            <?php
                                                 foreach($country as $country)
                                                         {
                                                          echo '<option value="'.$country->code.'">'.$country->name.'</option>';
                                                           
                                                         
                                                         
                                                          }
                                                        
                                                              
                                                         
                                                          
                                            ?>
                                                        
                                                        
                            </select>
                            

                                        </div>
                                    </div>
                                 
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('phone ', 'phone '); ?>
                                            <div class="controls">
                                                  <input type="tel" id="phone" name="phone" class="form-control" />
                                                <!--<?= form_input('phone', '', 'class="form-control" id="phone" '); ?>-->
                                            </div>
                                        </div>
                                    </div>
                               
                             
                                    
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('email', 'email'); ?>
                                            <div class="controls">
                                                <input type="email" id="email" name="email" class="form-control" required="required"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('username', 'username'); ?>
                                            <?= form_input('username', set_value('username'), 'class="form-control tip" id="username" required="required"'); ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('password', 'passwordr'); ?>
                                            <div class="controls">
                                                <?= form_password('password', '', 'class="form-control tip" id="passwordr" required="required" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"'); ?>
                                                <span class="help-block"><?= lang('pasword_hint'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <?= lang('confirm_password', 'password_confirm'); ?>
                                            <div class="controls">
                                                <?= form_password('password_confirm', '', 'class="form-control" id="password_confirm" required="required" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" data-bv-identical="true" data-bv-identical-field="password" data-bv-identical-message="' . lang('pw_not_same') . '"'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <input type="submit" name="register" class="btn btn-primary" id="register" value="Register" >
                                <!--<h5>For Login  <a href="<?= site_url('login_form.php'); ?>">Click Here</a></h5>-->
                                 <!--<?= form_submit('register', lang('register'), 'class="btn btn-primary"'); ?>-->
                               <a   id="login" onclick="LoginFn()" name="login" value="Login" class="btn btn-secondary">Already have an account</a>
                                <?= form_close(); ?>
                           
                            </div>
                            
                            <?php
    } ?>
                        </div>
                      
                      
                        
                    </div>

                    <div class="col-sm-3 col-md-2">
                        <div id="sticky-con">
                        <?php
                        if ($side_featured) {
                            ?>
                            <h4 class="margin-top-md title text-bold">
                                <span><?= lang('featured'); ?></span>
                                <div class="pull-right">
                                    <div class="controls pull-right hidden-xs">
                                        <a class="left fa fa-chevron-left btn btn-xs btn-default" href="#carousel-example"
                                        data-slide="prev"></a>
                                        <a class="right fa fa-chevron-right btn btn-xs btn-default" href="#carousel-example"
                                        data-slide="next"></a>
                                    </div>
                                </div>
                            </h4>

                            <div id="carousel-example" class="carousel slide" data-ride="carousel">
                                <!-- Wrapper for slides -->
                                <div class="carousel-inner">
                                    <?php
                                    $r = 0;
                            foreach ($side_featured as $fp) {
                                ?>
                                        <div class="item <?= empty($r) ? 'active' : ''; ?>">
                                            <div class="featured-products">
                                                <div class="product" style="z-index: 1;">
                                                    <div class="details" style="transition: all 100ms ease-out 0s;">
                                                        <?php
                                                        if ($fp->promotion) {
                                                            ?>
                                                            <span class="badge badge-right theme"><?= lang('promo'); ?></span>
                                                            <?php
                                                        } ?>
                                                        <img src="<?= base_url('assets/uploads/' . $fp->image); ?>" alt="">
                                                        <?php if (!$shop_settings->hide_price) {
                                                            ?>
                                                        <div class="image_overlay"></div>
                                                        <div class="btn btn-sm add-to-cart" data-id="<?= $fp->id; ?>"><i class="fa fa-shopping-cart"></i> <?= lang('add_to_cart'); ?></div>
                                                        <?php
                                                        } ?>
                                                        <div class="stats-container">
                                                            <?php if (!$shop_settings->hide_price) {
                                                            ?>
                                                            <span class="product_price">
                                                                <?php
                                                                if ($fp->promotion) {
                                                                    echo '<del class="text-red">' . $this->sma->convertMoney(isset($fp->special_price) && !empty(isset($fp->special_price)) ? $fp->special_price : $fp->price) . '</del><br>';
                                                                    echo $this->sma->convertMoney($fp->promo_price);
                                                                } else {
                                                                    echo $this->sma->convertMoney(isset($fp->special_price) && !empty(isset($fp->special_price)) ? $fp->special_price : $fp->price);
                                                                } ?>
                                                            </span>
                                                            <?php
                                                        } ?>
                                                            <span class="product_name">
                                                                <a href="<?= site_url('product/' . $fp->slug); ?>"><?= $fp->name; ?></a>
                                                            </span>
                                                            <a href="<?= site_url('category/' . $fp->category_slug); ?>" class="link"><?= $fp->category_name; ?></a>
                                                            <?php
                                                            if ($fp->brand_name) {
                                                                ?>
                                                                <span class="link">-</span>
                                                                <a href="<?= site_url('brand/' . $fp->brand_slug); ?>" class="link"><?= $fp->brand_name; ?></a>
                                                                <?php
                                                            } ?>
                                                        </div>
                                                        <div class="clearfix"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                        $r++;
                            } ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>
 <script>
    // Vanilla Javascript
    var input = document.querySelector("#phone");
    window.intlTelInput(input,({
      // options here
    }));
function LoginFn(){
             
         
                  $(".register").hide();
                    $("#register").hide();
                  $(".loginform").show();
     
           
}
function registerBtn(){
     $(".loginform").hide();
                $(".register").show();
                    $("#register").show();
                 
}


    $(document).ready(function() {
        $('.iti__flag-container').click(function() { 
          var countryCode = $('.iti__selected-flag').attr('title');
          var countryCode = countryCode.replace(/[^0-9]/g,'')
          $('#phone').val("");
          $('#phone').val("+"+countryCode+" "+ $('#phone').val());
       });
 
         
    });
         

  </script>