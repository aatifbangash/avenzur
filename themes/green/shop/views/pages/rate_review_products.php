<?php defined('BASEPATH') or exit('No direct script access allowed');?>
<style>
    .checked {
      color: orange;
    }
    .star {
      cursor: pointer;
    }

    .star.checked {
      color: orange;
    }

    .star:hover {
      color: orange;
    }
    .review-container {
            margin: 10px auto;
            background-color: #fff;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .review {
            border-bottom: 1px solid #ccc;
            padding: 10px 0;
        }
  </style>
<section class="page-contents" id="user-profile-section">
    <div class="container">
        <div class="row">
        <?php if ($this->session->flashdata('submit_review_error')) {
                                    ?>
                                    <div class="alert alert-danger">
                                        OOPs! Something goes wrong with review submission. Please try again!
                                        <?php $this->session->set_flashdata('submit_review_error', null) ?>
                                    </div>
                                    <?php
                                } ?>
             <?php if ($this->session->flashdata('submit_review_success')) {
                                    ?>
                                    <div class="alert alert-success">
                                        Your review has been submitted successfully!
                                        <?php $this->session->set_flashdata('submit_review_success', null) ?>
                                    </div>
                                    <?php
                                } ?>                    
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-sm-9 col-md-10">

                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#review_products" aria-controls="review_products" role="tab" data-toggle="tab"><?= lang('Products Review'); ?></a></li>
                            <li role="presentation" class=""><a href="#past_reviews" aria-controls="past_reviews" role="tab" data-toggle="tab"><?= lang('My Past Reviews'); ?></a></li>
                        </ul>

                        <div class="tab-content padding-lg white bordered-light" style="margin-top:-1px;">
                            <div role="tabpanel" class="tab-pane fade in active" id="review_products">

                                <?php if( isset($products) && !empty($products) ) {
                                echo form_open('shop/submit_review', 'class="validate" id="user-profile"'); ?>
                                    <?php 
                                    $pid =1;
                                    foreach ($products as $key => $product):
                                        $rating_id = $product->product_id.$pid;
                                        ?>
                                        <div class="form-group">
                                            <label for="rating_<?= $product->product_id; ?>">Review for <?= $product->product_code.' '.$product->product_name; ?>:</label>
                                            <div class="star-container" >
                                           <?php for($i=1; $i<=5; $i++) {?>
                                            <span class="fa fa-star star" style="cursor: pointer;" class="fa fa-star star" id='star<?php echo $rating_id.$i;?>' onclick="rateStar(<?php echo $rating_id;?>, <?php echo $i;?>)" onmouseover="highlightStars(<?php echo $rating_id;?>, <?php echo $i;?>)"></span>
                                            <?php }?>
                                           </div>
                                            <!-- <span class="fa fa-star star" onclick="rateStar(2)" onmouseover="highlightStars(2)"></span>
                                            <span class="fa fa-star star" onclick="rateStar(3)" onmouseover="highlightStars(3)"></span>
                                            <span class="fa fa-star star" onclick="rateStar(4)" onmouseover="highlightStars(4)"></span>
                                            <span class="fa fa-star star" onclick="rateStar(5)" onmouseover="highlightStars(5)"></span> -->
                                            <input type="text" id="rating_<?= $rating_id; ?>" name="reviews[<?= $product->product_id; ?>][rating][]" value="">
                                            
                                            <textarea class="form-control" name="reviews[<?= $product->product_id; ?>][review][]" rows="4" placeholder="Write your review"></textarea>
                                        </div>
                                    <?php $pid=$pid+1; 
                                          endforeach; 
                                          ?>

                                   
                                <?= form_submit('billing', lang('Submit Reviews'), 'class="btn btn-lg btn-primary"'); ?>
                                <?php echo form_close(); ?>
                                <?php } else{?>
                                    <div class="alert alert-info">No products available for review. Please visit the products section to place an order.</div>
                                    <?php }?>          
                            
                        </div>

                      
                            <div role="tabpanel" class="tab-pane fade in" id="past_reviews">

                                <?php if( isset($reviewProducts) && !empty($reviewProducts) ) {
                                    ?>
                        
                                    <div class="review-container">
                                        <?php foreach ($reviewProducts as $key => $review):  ?>
                                            <div class="review">
                                                <h3><?= $review->code.' '.$review->name; ?></h3>
                                                <p><strong>Rating:</strong>
                                                <?php 
                                                  for($i=1; $i<=5; $i++) {
                                                    $class = '';
                                                    if($i<=$review->rating) {$class = 'checked';}?>
                                            <span class="fa fa-star <?php echo $class;?>" ></span>
                                            <?php }?></p>
                                                <p><strong>Review:</strong> <?= $review->review; ?></p>
                                               
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                
                                <?php } else{?>
                                    <div class="alert alert-info">No past reviews available. Please visit the products section to place an order.</div>
                                    <?php }?>          
                            </div>

                            
                                  

                    </div>

                    <div class="col-sm-3 col-md-2">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
  // JavaScript function to handle star rating
  function rateStar(product_id, rating) {
    // Reset all stars to default color
    for (let i = 1; i <= 5; i++) {
      const star = document.getElementById(`star${product_id}${i}`);
      if (star) {
        star.classList.remove('checked');
      }
    }

    // Highlight selected stars
    for (let i = 1; i <= rating; i++) {
      const star = document.getElementById(`star${product_id}${i}`);
      if (star) {
        star.classList.add('checked');
      }
    }

    // Update the hidden input field with the selected rating
    document.getElementById(`rating_${product_id}`).value = rating;
  }

  // JavaScript function to highlight stars onmouseover
  function highlightStars(product_id, rating) {
    // Remove 'checked' class from all stars
    for (let i = 1; i <= 5; i++) {
      const star = document.getElementById(`star${product_id}${i}`);
      if (star) {
        star.classList.remove('checked');
      }
    }

    // Highlight stars up to the current rating
    for (let i = 1; i <= rating; i++) {
      const star = document.getElementById(`star${product_id}${i}`);
      if (star) {
        star.classList.add('checked');
      }
    }
  }
  
//  // JavaScript function to handle star rating
//  const containers = document.querySelectorAll('.star-container');

// // Add event listeners for mouseover and mouseout for each container
// containers.forEach(container => {
//     // Get all stars within the current container
//     const stars = container.querySelectorAll('.star');

//     // Add event listeners for mouseover and mouseout for each star in the current container
//     stars.forEach(star => {
//         star.addEventListener('mouseover', () => {
//             // Add the 'checked' class on mouseover
//             star.classList.add('checked');
//         });

//         star.addEventListener('mouseout', () => {
//             // Remove the 'checked' class on mouseout
//             star.classList.remove('checked');
//         });
//     });
// });

</script>
