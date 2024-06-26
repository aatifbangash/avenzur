<?php defined('BASEPATH') or exit('No direct script access allowed'); 
if(!isset($GLOBALS['CHILD_IDS'])) {
    $GLOBALS['CHILD_IDS'] = [];
} 
// $queryString = $_SERVER['QUERY_STRING'];
// parse_str($queryString, $queryParams);
//  $addQueryString = '';
//  if(http_build_query($queryParams) != '')
//  {
//     $addQueryString = '?'.http_build_query($queryParams);
//  }


?>
<!-- products start -->
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
<!-- <input type="hidden" id="min-price" value="<?php echo $filters['min_price'];?>">
<input type="hidden" id="max-price" value="<?php echo $filters['max_price'];?>">
<input type="hidden" id="brands"value="<?php echo $filters['brands'];?>"> -->
<section class="products">
    <div class="container container-max-width py-3">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Products</li>
            </ol>
        </nav>
        <div class="row  ">
            <div class="col-xl-2 col-lg-3">

                <!-- side bar left -->
                <button class="btn btn-primary d-lg-none catsidebarmob" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasProducts" aria-controls="offcanvasProducts"><i
                        class="bi bi-sort-down-alt"></i></button>



                <div class="offcanvas-lg offcanvas-end" tabindex="-1" id="offcanvasProducts"
                    aria-labelledby="offcanvasProductsLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasProductsLabel">Categories</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                            data-bs-target="#offcanvasProducts" aria-label="Close"></button>
                    </div>
                    <!-- <div class="offcanvas-body">
                        <div class="categ w-100">
                        <h5 class="fw-semibold">Categories</h5>
                        <div class="list-group catList">
                            <?php
                            foreach ($categories as $cat) {
                                ?>
                                        <a href="<?= site_url('category/' . $cat->slug); ?>" class="list-group-item list-group-item-action <?php if ($category_slug == $cat->slug) {
                                                echo 'active';
                                            } ?>" aria-current="true">
                                        <?= ucfirst(strtolower($cat->name)); ?>
                                        </a>
                                    <?php
                            }
                            ?>
                        </div>
                        
                        
                        <hr>
                            <div class="py-3">
                            <div>
                                <h5>Price</h5>
                                <input type="range" class="form-range" min="0" max="5" step="0.5" id="customRange3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="input-group ">
                                        
                                        <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default" value="0">
                                        </div><div class="px-2"> to</div>
                                        <div class="input-group ">
                                        
                                        <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default" value="100">
                                        </div>
                                </div>
                            </div>
                            </div>

                            <div class="py-3">
                                <h5>Brands</h5>
                                <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                                <label class="form-check-label" for="flexCheckDefault">
                                    <h6 class="fw-bold">Beatswell</h6>
                                </label>
                                </div>
                                <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="" id="flexCheckChecked" checked>
                                <label class="form-check-label" for="flexCheckChecked">
                                    <h6 class="fw-bold">Manukora</h6>
                                </label>
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
                                            <a class="brand-link list-group-item-action" data-brand="<?php echo $brand->slug; ?>" style="text-decoration:none;">
                                                <h6><?php echo ucfirst(strtolower($brand->name)); ?></h6>
                                            </a>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>



                </div>
                <!-- side bar end -->
            </div>
            <div class="col-xl-10 col-lg-9 ">
                <div class="row justify-content-between align-items-center" style="text-align: right">
                    <h1 style="font-size: 22px; font-weight: bold; color: #000;"><?php echo $page_title2; ?></h1>

                    <?php
                    if ($promo_banner) {
                        ?>
                        <section class="side-banner section-marg-top" style="margin-top:10px;margin-bottom: 40px;">
                            <div class="container container-max-width" style="padding-left: 0px;padding-right: 0px;">
                                <div class="sideBannerImg">
                                    <a href="<?= site_url('shop/products?promo=yes'); ?>">
                                        <img id="promo-page-banner-1" loading="lazy"
                                            src="<?= base_url('assets/images/banners/promo_inner_2024-06-12_en.jpg' . '?timestamp=' . time()); ?>"
                                            alt="placeholder" class="w-100" />
                                    </a>
                                </div>
                            </div>
                        </section>
                        <?php
                    }else if($suppliment_banner){
                        ?>
                        <section class="side-banner section-marg-top" style="margin-top:10px;margin-bottom: 40px;">
                            <div class="container container-max-width" style="padding-left: 0px;padding-right: 0px;">
                                <div class="sideBannerImg">
                                    <a href="<?= site_url('category/supplements'); ?>">
                                        <img id="promo-page-banner-2" loading="lazy"
                                            src="<?= base_url('assets/images/banners/supplement_inner_banner_2024-06-12_en.jpg' . '?timestamp=' . time()); ?>"
                                            alt="placeholder" class="w-100" />
                                    </a>
                                </div>
                            </div>
                        </section>
                        <?php
                    }
                    else if($honst_banner) {
                        ?>
                        <section class="side-banner section-marg-top" style="margin-top:10px;margin-bottom: 40px;">
                            <div class="container container-max-width" style="padding-left: 0px;padding-right: 0px;">
                                <div class="sideBannerImg">
                                    <a href="<?= site_url('brand/honstHonst'); ?>">
                                        <img id="honst-page-banner" loading="lazy"
                                            src="<?= base_url('assets/images/banners/honst_2024-06-25_en.png' . '?timestamp=' . time()); ?>"
                                            alt="placeholder" class="w-100" />
                                    </a>
                                </div> 
                            </div>
                        </section>
                   <?php     
                    }
                    ?>
                    <!--<div class="col-md-6 col-6">
                        <div class="dropdown sortp">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Default sorting <i class="bi bi-chevron-down ps-2"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Sort by Price: low to heigh</a></li>
                                <li><a class="dropdown-item" href="#">Sort by Price: heigh to low</a></li>
                                
                            </ul>
                            </div>
                    </div>
                    <div class="col-md-6 col-6 ">
                        <div class="form-check ms-auto " style="width: fit-content;">
                            <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                            <label class="form-check-label" for="flexCheckDefault">
                                only product on sale
                            </label>
                        </div>
                    </div>-->
                </div>
                <!-- all products -->

                <div id="results" class="row products-card text-center SS gy-4 pb-4">

                </div>
                <!-- all products end -->
            </div>
        </div>
    </div>
</section>

<!-- products end -->


<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">

                    <div class="col-sm-3 col-md-2">
                        <!--<h4>Categories</h4>
                        <ul style="list-style-type: none;padding: 0px;">-->
                        <?php
                        /*foreach($categories as $cat)
                        {
                                echo '<li class="category-side"><a href="' . site_url('category/' . $cat->slug) . '">' . ucfirst(strtolower($cat->name)) . '</a></li>';
                        }*/

                        ?>
                        <!--</ul>-->
                    </div>

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
                searchProducts();
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