
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">
             
                    <!-- categories section start -->

                    <div class="col-sm-3 col-md-2">
                        <h4>Categories</h4>
                        <ul style="list-style-type: none;padding: 0px;">
                        <?php
                        foreach($categories as $cat)
                        {
                                echo '<li class="category-side"><a href="' . site_url('category/' . $cat->slug) . '">' . ucfirst(strtolower($cat->name)) . '</a></li>';
                        //   echo  '<li>' .'<a>'.$cat->name.'</a>'.'</li>'.'<br>';
                        }
                            
                        ?>
                        </ul>
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
