<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents" style="background-color: #ffffff !important;">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
<?php //var_dump($page) ?>
                <div class="row">
                    <div class="col-sm-9 col-md-10">
                        <div class="">
                             <img class="card-image" src="<?php echo site_url('assets/uploads/'.$page->image); ?>" >
                            <div class="panel-heading text-bold">
                              <h1>  <?= $page->title; ?></h1>
                            </div>
                            <div class="panel-body" style="padding:10px !important;">
                              <p> <?= $this->sma->decode_html($page->body); ?>
                                <?php
                                if ($page->slug == $shop_settings->contact_link) {
                                    echo '<p><button type="button" class="btn btn-primary email-modal">Send us email</button></p>';
                                }
                                ?>
                                </p> 
                            </div>

                        </div>
                    </div>

                    <div class="col-sm-3 col-md-2">
                        <?php include 'sidebar2.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
