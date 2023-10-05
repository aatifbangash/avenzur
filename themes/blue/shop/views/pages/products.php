
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents">
    <div class="container">
        <?php if(isset($featureImage)){
         
        
        ?>
        <div class="row">
            <div class="col-lg-12">
                <div class="fusion-title-sc-wrapper" style="background-color: #848280;  ">
														
					<div class="fusion-title title fusion-title-2 fusion-sep-none fusion-title-text fusion-title-size-two" style="font-size:60px;margin-top:0px;margin-right:0px;margin-bottom:0px;margin-left:0px;">
							<h2 class="title-heading-left fusion-responsive-typography-calculated" style="margin: 0px; font-size: 1em; line-height: 1.33; --fontSize:60; --typography_sensitivity:1.3; color:white !important;" data-fontsize="60" data-lineheight="79.8px">
							<?php echo $featureImage->name; ?></h2>
					</div>				
							
				</div>
                <!--<img src="<?= base_url('assets/uploads/thumbs/' . $featureImage->image); ?>" style="max-width:100%;width:100%;padding:20px 0px 20px 0px; ">-->
            </div>
        </div>
        
        <?php } ?>
        <div class="row">
            <div class="col-xs-12">

                <div class="row">
             
                    <!-- categories section start -->

                    <div class="col-sm-3 col-md-2">
                        <h4>Categories</h4>
                        <?php
                        foreach($categories as $cat)
                        {
                                echo '<li class="category-side"><a href="' . site_url('category/' . $cat->slug) . '">' . ucfirst(strtolower($cat->name)) . '</a></li>';
                        //   echo  '<li>' .'<a>'.$cat->name.'</a>'.'</li>'.'<br>';
                        }
                            
                        ?>
                    </div>

                    <!-- categories section end -->

                    <div class="col-sm-9 col-md-10">
                        <div id="grid-selector">
                        </div>

                        <div class="clearfix"></div>
                        <div class="row">
                            <div id="results" class="grid"></div>
                        </div>
                        <div class="clearfix"></div>

                        <div class="row">
                            <div class="col-md-6">
                                <span class="page-info line-height-xl hidden-xs hidden-sm"></span>
                            </div>
                            <div class="col-md-6">
                                <div id="pagination" class="pagination-right"></div>
                            </div>
                        </div>

                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</section>
