<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<section class="page-contents" style="padding:40px 40px ;background-color: #ffffff;">
<div class="container" >
		<div class="row">
		    <?php //echo $demo; ?>
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="row">
				    <h2 style="text-align:center !important; padding:20px 20px ;">Blogs</h2>
                    <?php foreach($blogs as $post) { ?>
                        <div class="col-md-4">
                            <div class="card" style="">
                                <img class="card-img-top" src="<?php echo site_url('assets/uploads/'.$post['image']); ?>" >
                                 <div class="card-body">
                                    <h5 class="card-title"><?php echo $post['title']; ?></h5>
                                    <p class="card-text"><?php echo $post['description']; ?></p>
                                        <p class="card-text"><?php echo $post['category']; ?></p>
                                        <br>
                                   
                                        
                                    <a href="<?php echo site_url('shop/blog/'.$post['slug']); ?>" class="btn-read-more">Read More</a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                </div>
			</div>
			
			<!--<div class="m-auto">-->
			<!--	<div class="pagination mt-5 pt-4">-->
			<!--		<ul class="list-inline ">-->
			<!--			<li class="list-inline-item"><a href="#" class="active">1</a></li>-->
			<!--			<li class="list-inline-item"><a href="#">2</a></li>-->
			<!--			<li class="list-inline-item"><a href="#">3</a></li>-->
			<!--			<li class="list-inline-item"><a href="#" class="prev-posts"><i class="ti-arrow-right"></i></a></li>-->
			<!--		</ul>-->
			<!--	</div>-->
			<!--</div>-->
		</div>
	</div>

</section>