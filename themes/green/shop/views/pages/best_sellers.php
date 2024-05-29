<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<style>
    .arrow{
        float: right;
    }
    .active {
        color: white;
    }
</style>
<style>

    /* Override the existing rule by setting content to none */
.list-group.catList a::before {
    content: none; /* This will remove the custom bullet points */
    display: none; /* Ensures the previous display property is overridden */
}
.catList {
    padding-left: 0;
}
.list-group-item {
    border: none; /* Remove border from list items */
    padding-left: 1rem; /* Adjust padding to align text */
}
.collapse-toggle{
    cursor: pointer;
    display: flex;
    align-items: center;
    
}
a.me-2.collapse-toggle:hover {
  z-index: 2;
  color: var(--bs-list-group-active-color);
  background-color: var(--primary-color);
  border-color: var(--primary-color);
  padding-left: 10px;
  border-top-left-radius: inherit;
  border-top-right-radius: inherit;
}
.arrow {
    /* margin-right: 0.5rem; */
}
.d-flex {
    display: flex;
    align-items: center;
}

.icon-box {
    width: 15px;
  height: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  /* background-color: black; */
  color: gray;
  /* border-radius: 50%; */
  font-size: 14px;
  border: 1px solid gray;
}
</style>
<!-- products start -->
<section class="products">
    <div class="container container-max-width py-3">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Products</li>
            </ol>
        </nav>
        <div class="row  ">
            <div class="col-xl-2  col-lg-3">
                
                <!-- side bar left -->
                <button class="btn btn-primary d-lg-none catsidebarmob" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasProducts" aria-controls="offcanvasProducts"><i class="bi bi-sort-down-alt"></i></button>

                
                
                <!-- <div class="offcanvas-lg offcanvas-end" tabindex="-1" id="offcanvasProducts" aria-labelledby="offcanvasProductsLabel">
                    <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasProductsLabel">Categories</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#offcanvasProducts" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <div class="categ w-100">
                        <h5 class="fw-semibold">Categories</h5>
                        <div class="list-group catList">
                            <?php
                                foreach($categories as $cat)
                                {
                                    ?>
                                        <a href="<?= site_url('category/' . $cat->slug); ?>" class="list-group-item list-group-item-action <?php if($category_slug == $cat->slug) { echo 'active'; } ?>" aria-current="true">
                                        <?= ucfirst(strtolower($cat->name)); ?>
                                        </a>
                                    <?php
                                }
                            ?>
                        </div>
                        </div>
                    </div>
                </div> -->
                <div class="offcanvas-body" style="display: flex; flex-direction: column;">
                        <!-- Categories Section -->
                        <!-- <div class="w-100">
                            <h5  data-bs-toggle="collapse" href="#categoriesCollapse" role="button"
                                aria-expanded="true" aria-controls="categoriesCollapse"><b>Categories <i class="bi bi-chevron-down arrow"></i></b></h5>
                            <div class="collapse show" id="categoriesCollapse">
                                <div class="list-group catList">
                                    <?php foreach ($categories as $cat): ?>
                                        <a style="font-weight: none" href="<?= site_url('category/' . $cat->slug.$addQueryString); ?>"
                                            class="list-group-item list-group-item-action <?php if ($category_slug == $cat->slug)
                                                echo 'active'; ?>">
                                            <?= ucfirst(strtolower($cat->name)); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div> -->
                        

                        <?php
                            $this->load->helper('url');
                            // Function to recursively render category list
                            function renderCategories(&$categories, $parent_id = null, $addQueryString = '', $category_slug = '')
                            {

                                echo '<div class="list-group catList">';
                                foreach ($categories as $key => $cat) {
                                    // echo "<pre>"; var_dump($GLOBALS['CHILD_IDS'], !in_array($cat->id, $GLOBALS['CHILD_IDS']), $cat->id);
                                    if ($cat->parent_id == $parent_id || ($parent_id === null || $cat->parent_id === 0) &&  !in_array($cat->id, $GLOBALS['CHILD_IDS'])) {
                                        $hasChildren = hasChild($cat->id, $categories);
                                        $toggleIcon = '<div class="icon-box"><i class="bi bi-plus arrow"></i></div>';
                                        
                                        // Determine if this category or any of its children are active
                                        $isActive = ($category_slug == $cat->slug || isChildActive($cat->id, $categories, $category_slug));
                                        $collapseClass = $isActive ? ' show' : '';

                                        echo '<div class="list-group-item">';
                                        echo '<div class="d-flex align-items-center">';
                                        echo '<a href="#" class="me-2 collapse-toggle" data-bs-toggle="collapse" data-bs-target="#collapse-' . $cat->slug . '">';
                                        echo $toggleIcon;
                                        echo '</a>';
                                        
                                        echo '<a style="font-weight: none; flex: 1;" href="' . site_url('category/' . $cat->slug . $addQueryString) . '" class="list-group-item-action ' . ($category_slug == $cat->slug ? 'active' : '') . '">';
                                        echo ucfirst(strtolower($cat->name));
                                        echo '</a>';
                                        echo '</div>';
                                        if ($cat->parent_id == $parent_id)
                                        {
                                            $GLOBALS['CHILD_IDS'][] = $cat->id;
                                            unset($categories[$key]);

                                        }
                                        // var_dump($childIds);

                                        if ($hasChildren) {
                                            echo '<div class="collapse' . $collapseClass . '" id="collapse-' . $cat->slug . '">';
                                            renderCategories($categories, $cat->id, $addQueryString, $category_slug);
                                            echo '</div>';
                                        }
                                        // echo "<pre>"; var_dump("herer", $categories); exit;
                                        echo '</div>';

                                    }
                                }
                                // var_dump($childIds); exit;
                                echo '</div>';
                            }

                            // Function to check if a category has children
                            function hasChild($category_id, $categories)
                            {
                                foreach ($categories as $cat) {
                                    if ($cat->parent_id == $category_id) {
                                        return true;
                                    }
                                }
                                return false;
                            }

                            // Function to check if any child category is active
                            function isChildActive($parent_id, $categories, $category_slug)
                            {
                                foreach ($categories as $cat) {
                                    if ($cat->parent_id == $parent_id) {
                                        if ($cat->slug == $category_slug || isChildActive($cat->id, $categories, $category_slug)) {
                                            return true;
                                        }
                                    }
                                }
                                return false;
                            }

                            $childIds = [];


                        ?>

                        <div class="w-100">
                            <h5 data-bs-toggle="collapse" href="#categoriesCollapse" role="button" aria-expanded="true" aria-controls="categoriesCollapse">
                                <b>Categories <i class="bi bi-chevron-down arrow"></i></b>
                            </h5>
                            <div class="collapse show" id="categoriesCollapse">
                                <?php renderCategories($categories, null, $addQueryString, $category_slug);  ?>

                            </div>
                        </div>
                        <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            document.querySelectorAll('.collapse-toggle').forEach(function (element) {
                                element.addEventListener('click', function (e) {
                                    e.preventDefault(); // Prevent default anchor behavior
                                    const icon = this.querySelector('.arrow');
                                    if (icon) {
                                        icon.classList.toggle('bi-plus');
                                        icon.classList.toggle('bi-dash');
                                    }
                                });
                            });

                            // Ensure the correct icon is shown for initially expanded categories
                            document.querySelectorAll('.collapse.show').forEach(function (element) {
                                const toggle = element.previousElementSibling.querySelector('.collapse-toggle .arrow');
                                if (toggle) {
                                    toggle.classList.remove('bi-plus');
                                    toggle.classList.add('bi-dash');
                                }
                            });
                        });
                        </script>

                        <hr>

                        <!-- Price Section -->
                        <div class="py-3">
                            <h5 data-bs-toggle="collapse" href="#priceCollapse" role="button" aria-expanded="true"
                                aria-controls="priceCollapse"><b>Price <i class="bi bi-chevron-down arrow"></i></b></h5>
                            <div class="collapse show" id="priceCollapse">
                               
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="input-group">
                                        <input type="text" name="min_price" id="input_min_price" class="form-control" value="0">
                                    </div>
                                    <div class="px-2">to</div>
                                    <div class="input-group">
                                        <input type="text" name="max_price" id="input_max_price" class="form-control" value="100">
                                    </div>
                                    <a href="#" class="btn btn-sm px-2" style=" margin-left: 2px; background-color: var(--primary-color); color: white;" id="goButton">GO</a>
                                </div>

                            </div>
                        </div>
                        <!-- Brands Section -->
                        <div class="py-3">
                            <h5 data-bs-toggle="collapse" href="#brandsCollapse" role="button" aria-expanded="false"
                                aria-controls="brandsCollapse" id="brandsHeader">
                                <b>Brands <i class="bi bi-chevron-right arrow" id="brandsIcon"></i></b>
                            </h5>
                            <div class="collapse" id="brandsCollapse">
                                <?php foreach ($brands as $brand): ?>
                                    <div class="form-check">
                                        <input class="form-check-input brand-checkbox" type="checkbox" value="<?php echo $brand->id; ?>" id="flexCheckDefault<?php echo $brand->id; ?>">
                                        <label class="form-check-label" for="flexCheckDefault<?php echo $brand->id; ?>">
                                            <a href="#" class="brand-link list-group-item-action" data-brand="<?php echo $brand->slug; ?>">
                                                <h6><?php echo ucfirst(strtolower($brand->name)); ?></h6>
                                            </a>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <!-- side bar end -->
            </div>
            <div class="col-xl-10 col-lg-9">

                <!-- all products -->
                <div class="row products-card text-center gy-4 pb-4">
                <h1 style="font-size: 22px; font-weight: bold; color: #000; text-align:right;"><?php echo $page_title; ?></h1>             
                <?php
                    $r = 0;
                    foreach (array_chunk($best_sellers, 4) as $sps){
                        foreach ($sps as $sp) {
                            ?>
                              <div class="col-xl-3 col-lg-4 col-md-6 col-6 product-cards-cont product-cards-wrapper">  
                                        <div class="card" style="width: 100%">
                                        <a href="<?= site_url('product/' . $sp->slug); ?>" class="text-decoration-none">
                                        <div class="cardImg">
                                        <?php 
                                        if($sp->promotion && $sp->price > 0 && $sp->promo_price > 0){
                                            ?>
                                            <span class="position-absolute badge rounded-pill bg-danger" style="top:12px;left:12px;font-size:10px">
                                                <?php echo round((($sp->price - $sp->promo_price) / $sp->price) * 100); ?>% OFF
                                            </span>
                                            <?php
                                        }

                                        if($sp->global == 1){
                                            ?>
                                              <!--<span class="position-absolute badge rounded-pill bg-info" style="top:0px;right:0px;font-size:10px">
                                                Global
                                              </span>-->
                                              <span class="position-absolute badge" style="top:0px;right:0px;width: 90px;">
                                                <img src="<?= base_url('assets/images/global.jpg'); ?>" style="height:20px;" class="card-img-top" alt="Global">
                                              </span>
                                            <?php
                                          }
                                        ?>
                                            <img src="<?= base_url('assets/uploads/thumbs/' . $sp->image); ?>" class="card-img-top" alt="..."></div>
                                        <div class="card-body px-0 text-start pb-0">
                                            <div class="product-cat-title"><span class="text-uppercase">Medical</span></div>
                                            <h5 class="card-title text-start"><?= $sp->name; ?></h5>
                                            <div class="d-flex align-items-center justify-content-between">
                                          
                                                <div class="rating">
                                                    <?php 
                                                        for($i=1; $i<=5; $i++) {
                                                            $class = '';
                                                            if($i<=$sp->avg_rating) {$class = 'rated';}?>
                                                    <i class="bi bi-star-fill <?php echo $class;?>" ></i>
                                                    <?php }?>
                                                </div>
                                          
                                            <?php
                                            if ($sp->promotion) {
                                                ?>
                                           
                                                    <div class="discountPrice price text-end py-2">
                                                        <h4 class="m-0 text-decoration-line-through">
                                                            <?php echo $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price); ?>
                                                        </h4>
                                                    </div>
                                            
                                                <?php
                                            }
                                            ?>
                                            </div> 
                        
                                            <div class="d-flex align-items-center justify-content-between">
                                         
                                                <div class="price text-start  py-2">
                                                    <h4 class="m-0 fw-bold">
                                                    <?php
                                                    if ($sp->promotion) {
                                                        echo $this->sma->convertMoney($sp->promo_price);
                                                    }else{
                                                        echo $this->sma->convertMoney(isset($sp->special_price) && !empty(isset($sp->special_price)) ? $sp->special_price : $sp->price);
                                                    }
                                                    ?>
                                                    </h4>
                                                </div>
                                            
                                         
                                                <div class="quantity text-end py-2 d-flex align-items-center justify-content-between">
                                                <span class="plus btn-plus"><i class="bi bi-plus-circle-fill"></i></span>
                                                <input type="text" name="quantity" class="Qnum" value="1" required="required" />
                                                <!--<span class="Qnum ">1</span>-->
                                                <span class="minus btn-minus"><i class="bi bi-dash-circle-fill"></i></span>
                                                </div>
                                         
                                            </div>
                                        
                                            </div>
                                        </a>
                                        <div> 
                                            <?php 
                                                if($sp->product_quantity > 0){
                                                    ?>
                                                        <button type="button" data-id="<?= $sp->id; ?>" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark add-to-cart">Add to cart </button>
                                                    <?php
                                                }else{
                                                    $notify_price = $sp->promotion == 1 ? $sp->promo_price : $sp->price;
                                                    ?>
                                                        Out of Stock
                                                        <button type="button" class="btn btn-link btn-notify-add-to-list" href="#" data-id="<?= $sp->id; ?>" data-title="<?= $sp->name; ?>" data-image="<?= $sp->image; ?>" data-price="<?= $notify_price; ?>" >Notify me</button>
                                                    <?php
                                                }
                                            ?>
                                            
                                        </div>
                                    </div>
                                </div>
                            <?php
                        }
                    }
                ?>
                 
                                       
                </div>
                <!-- all products end -->
            </div>
        </div>
    </div>
</section>

<!-- products end -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Attach collapse event listeners
    var collapsibles = document.querySelectorAll('.collapse');
    collapsibles.forEach(function(collapse) {
        var toggleButton = collapse.previousElementSibling; 

        // Show event
        collapse.addEventListener('show.bs.collapse', function () {
            var icon = toggleButton.querySelector('.arrow');
            if (icon) {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            }
        });

        // Hide event
        collapse.addEventListener('hide.bs.collapse', function () {
            var icon = toggleButton.querySelector('.arrow');
            if (icon) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            }
        });
    });
});

document.getElementById('goButton').addEventListener('click', function() {
    var minPrice = document.getElementById('input_min_price').value;
    var maxPrice = document.getElementById('input_max_price').value;
    var newUrl = updateQueryStringParameter(window.location.href, 'min_price', minPrice);
    newUrl = updateQueryStringParameter(newUrl, 'max_price', maxPrice);
    window.location.href = newUrl; 
});

function updateQueryStringParameter(uri, key, value) {
    
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    }
    else {
        return uri + separator + key + "=" + value;
    }

}

document.querySelectorAll('.brand-link').forEach(function(link) {
    link.addEventListener('click', function(event) {
        event.preventDefault(); 
        var brandValue = this.getAttribute('data-brand'); 
        var newUrl = updateQueryStringParameter(window.location.href, 'brands', brandValue);
        window.location.href = newUrl; 
    });
});

document.addEventListener('DOMContentLoaded', function() {
            // Function to update the URL with the selected brands
            function updateURL() {
                const checkboxes = document.querySelectorAll('.brand-checkbox');
                const selectedBrands = Array.from(checkboxes)
                    .filter(checkbox => checkbox.checked)
                    .map(checkbox => checkbox.value);

                const url = new URL(window.location.href);
                if (selectedBrands.length > 0) {
                    url.searchParams.set('brands', selectedBrands.join(','));
                } else {
                    url.searchParams.delete('brands');
                }
                window.history.replaceState({}, '', url);
                window.location.href = url; 
                // searchProducts();
            }

            // Attach change event listeners to the checkboxes
            document.querySelectorAll('.brand-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', updateURL);
            });
            console.log("herere")

            // Initialize checkboxes based on URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const brandsParam = urlParams.get('brands');
            if (brandsParam) {
                const selectedBrands = brandsParam.split(',');
                selectedBrands.forEach(function(brand) {
                    const checkbox = document.querySelector(`.brand-checkbox[value="${brand}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            var brandsHeader = document.getElementById('brandsHeader');
            // var brandsIcon = document.getElementById('brandsIcon');
            var brandsCollapse = new bootstrap.Collapse(document.getElementById('brandsCollapse'), {
                toggle: false
            });

            brandsHeader.addEventListener('click', function() {
                brandsCollapse.toggle();
                // var isShown = brandsHeader.getAttribute('aria-expanded') === 'true';

                // // Toggle the icon based on the collapse state
                // if (isShown) {
                //     brandsIcon.classList.remove('bi-chevron-down');
                //     brandsIcon.classList.add('bi-chevron-right');
                // } else {
                //     brandsIcon.classList.remove('bi-chevron-right');
                //     brandsIcon.classList.add('bi-chevron-down');
                // }

                // Update the aria-expanded attribute based on the collapse state
                brandsHeader.setAttribute('aria-expanded', !isShown);
            });
        });
</script>
