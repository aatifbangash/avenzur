

// Find the source and target divs by their ids
var buttonToMove = document.getElementById('menuiconMob');
var sourceDiv = document.getElementById('sourcedivmob');
var targetDiv = document.getElementById('shoppingdivMob');
var saledivToMove = document.getElementById('salediv');
var sourcesalemob = document.getElementById('salemob');
var targetmobnav = document.getElementById('mobnav');
// Check if the screen width is less than a certain threshold (e.g., 768 pixels for typical mobile screens)

// Remove the button from the source div
sourceDiv.removeChild(buttonToMove);

// Append the button to the target div
targetDiv.appendChild(buttonToMove);

if (window.innerWidth < 768) {
    // Remove the button from the source div
    sourcesalemob.removeChild(saledivToMove);

    // Append the button to the target div
    targetmobnav.appendChild(saledivToMove);
}

//     seaerch bar =================================
var toggleSearch = document.getElementById('searchtoggle');
var searchBar = document.getElementById('searchbarmob');
var toggleSearchcros = document.getElementById('searchtogglecros');
// Add a click event listener to the button
toggleSearch.addEventListener('click', function() {
    // Toggle the visibility of the div
    if (searchBar.style.display === 'block') {
        searchBar.style.display = 'none';
    } else {
        searchBar.style.display = 'block';
    }
});
toggleSearchcros.addEventListener('click', function() {
    // Toggle the visibility of the div
    if (searchBar.style.display === 'none') {
        searchBar.style.display = 'block';
    } else {
        searchBar.style.display = 'none';
    }
}

);

function update_mini_cart(t) {
  if (t.total_items && t.total_items > 0) {
    $(".cart-total-items").show();
    $(".cart-total-items").text(t.total_items);
    //$(".cart-total-items").text(t.total_items + " " + (t.total_items > 1 ? lang.items : lang.item));
    /*  $("#cart-items").empty(),
      $.each(t.contents, function() {
          var t = '<td><a href="' + site.site_url + "/product/" + this.slug + '"><span class="cart-item-image"><img src="' + site.base_url + "assets/uploads/thumbs/" + this.image + '" alt=""></span></a></td><td><a href="' + site.site_url + "/product/" + this.slug + '">' + this.name + "</a><br>" + this.qty + " x " + this.price + '</td><td class="text-right text-bold">' + this.subtotal + "</td>";
          $("<tr>" + t + "</tr>").appendTo("#cart-items")
      });
      var e = '\n        <tr class="text-bold"><td colspan="2">' + lang.total_items + '</td><td class="text-right">' + t.total_items + '</td></tr>\n        <tr class="text-bold"><td colspan="2">' + lang.total + '</td><td class="text-right">' + t.total + "</td></tr>\n        ";
      $("<tfoot>" + e + "</tfoot>").appendTo("#cart-items"),
      $("#cart-empty").hide(),
      $("#cart-contents").show()*/
  } else{
    //$(".cart-total-items").text(lang.cart_empty);
    //$("#cart-contents").hide();
    //$("#cart-empty").show();
  }
}

function get(t) {
  if ("undefined" != typeof Storage)
      return localStorage.getItem(t);
  alert("Please use a modern browser as this site needs localstroage!")
}

function gen_html(t) {
  var e = "";
  if (get_width() > 992)
      var a = get("shop_grid")
        , s = ".three-col" == a ? 3 : 4;
  else
      var a = ".four-col"
        , s = 4;
  var i = a && ".three-col" == a ? "col-sm-6 col-md-4" : "col-md-6"
    , o = a && ".three-col" == a ? "alt" : "";
  if (t || (e += '<div class="col-sm-12"><div class="alert alert-warning text-center padding-xl margin-top-lg"><h4 class="margin-bottom-no">' + lang.x_product + "</h4></div></div>"),
  1 == site.settings.products_page && ($("#results").empty(),
  $(".grid").isotope("destroy").isotope()),
  $.each(t, function(a, r) {
      var n = r.special_price ? r.special_price : r.price
        , l = r.special_price ? r.formated_special_price : r.formated_price
        , c = (r.promotion && r.promo_price && 0 != r.promo_price && r.promo_price,
      r.promotion && r.promo_price && 0 != r.promo_price ? r.formated_promo_price : l);
      1 != site.settings.products_page && (0 === a ? e += '<div class="row">' : a % s == 0 && (e += '</div><div class="row">')),
      e += '<div class="product-container ' + i + " " + (1 == site.settings.products_page ? "grid-item" : "") + '">\n        <div class="product ' + o + " " + (1 == site.settings.products_page ? "grid-sizer" : "") + '">\n        ' + (r.promo_price ? '<span class="badge badge-right theme">Promo</span>' : "") + '\n        <div class="product-top">\n        <div class="product-image">\n        <a href="' + site.site_url + "product/" + r.slug + '">\n        <img class="img-responsive" src="' + site.base_url + "assets/uploads/" + r.image + '" alt=""/>\n        </a>\n        </div>\n        <div class="product-desc">\n        <a href="' + site.site_url + "product/" + r.slug + '">\n        <h2 class="product-name">' + r.name + "</h2>\n        </a>\n        <p>" + r.details + '</p>\n        </div>\n        </div>\n        <div class="clearfix"></div>\n        ' + (1 == site.shop_settings.hide_price ? "" : '\n        <div class="product-bottom">\n        <div class="product-price">\n        ' + (r.promo_price ? '<del class="text-danger text-size-sm">' + l + "</del>" : "") + "\n        " + c + '\n        </div>\n        <div class="product-rating">\n        <div class="form-group" style="margin-bottom:0;">\n        <div class="input-group">\n        <span class="input-group-addon pointer btn-minus"><span class="fa fa-minus"></span></span>\n        <input type="text" name="quantity" class="form-control text-center quantity-input" value="1" required="required">\n        <span class="input-group-addon pointer btn-plus"><span class="fa fa-plus"></span></span>\n        </div>\n        </div>\n        </div>\n        <div class="clearfix"></div>\n        <div class="product-cart-button">\n        <div class="btn-group" role="group" aria-label="...">\n        <button class="btn btn-info add-to-wishlist" data-id="' + r.id + '"><i class="fa fa-heart-o"></i></button>\n        <button class="btn btn-theme add-to-cart" data-id="' + r.id + '"><i class="fa fa-shopping-cart padding-right-md"></i> ' + lang.add_to_cart + '</button>\n        </div>\n        </div>\n        <div class="clearfix"></div>\n        </div>') + '\n        </div>\n        <div class="clearfix"></div>\n        </div>',
      1 != site.settings.products_page && a + 1 === t.length && (e += "</div>")
  }),
  1 != site.settings.products_page)
      $("#results").empty(),
      $(e).appendTo($("#results"));
  else {
      var r = $(e);
      $(".grid").isotope("insert", r).isotope("layout"),
      setTimeout(function() {
          $(".grid").isotope({
              itemSelector: ".grid-item"
          })
      }, 200)
  }
}

function get_width() {
  return $(window).width()
}

function get_filters() {
  return filters.category = $("#product-category").val() ? $("#product-category").val() : filters.category,
  filters.min_price = $("#min-price").val(),
  filters.max_price = $("#max-price").val(),
  filters.in_stock = $("#in-stock").is(":checked") ? 1 : 0,
  filters.promo = $("#promotions").is(":checked") ? "yes" : 0,
  filters.featured = $("#featured").is(":checked") ? "yes" : 0,
  filters.sorting = get("sorting"),
  filters
}

function searchProducts(t) {
  /*if (history.pushState) {
      var e = window.location.origin + window.location.pathname + "?page=" + filters.page;
      window.history.pushState({
          path: e,
          filters: filters
      }, "", e)
  }*/

var promo = 0;
var callUrl;
var currentURL = window.location.href;
if (currentURL.includes("promo=yes")) {
  promo = 1;
  callUrl = site.shop_url + "search?page=" + filters.page + "&promo=yes";
}else{
  callUrl = site.shop_url + "search?page=" + filters.page;
}

  $("#loading").show();
  var a = {};
  a[site.csrf_token] = site.csrf_token_value,
  a.filters = get_filters(),
a.filters.promo = promo,
  a.format = "json",
  $.ajax({
      url: callUrl,
      type: "POST",
      data: a,
      dataType: "json"
  }).done(function(t) {
      products = t.products,
      $(".page-info").empty(),
      $("#pagination").empty(),
      t.products && (t.pagination && $("#pagination").html(t.pagination),
      t.info && $(".page-info").text(lang.page_info.replace("_page_", t.info.page).replace("_total_", t.info.total))),
      gen_html(products)
  }).always(function() {
      $("#loading").hide()
  }),
  location.href.includes("products") && t && (window.history.pushState({
      link: t,
      filters: filters
  }, "", t),
  window.onpopstate = function(t) {
      t.state && t.state.filters ? (filters = t.state.filters,
      searchProducts()) : (filters.page = 1,
      searchProducts())
  }
  ),
  setTimeout(function() {
      window.scrollTo(0, 0)
  }, 500)
}

// slick slider =====================
$(document).ready(function(){

  searchProducts();

  $(document).on("click", ".add-to-cart", function(t) {
      t.preventDefault();
      var e = $(this).attr("data-id")
        , a = $(".shopping-cart:visible")
        , s = $(this).parents(".card").find("input");

        //, s = $(this).parents(".product-bottom").find(".quantity-input");
      /*,i=$(this).parents(".product").find("img").eq(0);if(i){i.clone().offset({top:i.offset().top,left:i.offset().left}).css({opacity:"0.5",position:"absolute",height:"150px",width:"150px","z-index":"1000"}).appendTo($("body")).animate({top:a.offset().top+10,left:a.offset().left+10,width:"50px",height:"50px"},400).animate({width:0,height:0},function(){$(this).detach()})}*/
      $.ajax({
          url: site.site_url + "cart/add/" + e,
          type: "GET",
          dataType: "json",
          data: {
              qty: s.val()
          }
      }).done(function(t) {
          t.error ? sa_alert("Error!", t.message, "error", !0) : (a = t,
          update_mini_cart(t))
      })
  });

  $(document).on("click", ".btn-minus", function(t) {
    var e = $(this).parent().find("input");
    parseInt(e.val()) > 1 && e.val(parseInt(e.val()) - 1)
  });

  $(document).on("click", ".btn-plus", function(t) {
    var e = $(this).parent().find("input");
    e.val(parseInt(e.val()) + 1)
  });

  $('.feature_products').slick({
    infinite: false,
    speed: 300,
    slidesToShow: 4,
    slidesToScroll: 1,
    margin: 10,
    prevArrow:"<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
    nextArrow:"<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
    responsive: [
      {
        breakpoint: 1024,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 3,
          infinite: true,
          dots: true
        }
      },
      {
        breakpoint: 770,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2
        }
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ]
  })

  $('.special_products').slick({
   
    infinite: false,
    speed: 300,
    slidesToShow: 3,
    slidesToScroll: 1,
    margin: 10,
    prevArrow:"<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
    nextArrow:"<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
    responsive: [
      {
        breakpoint: 1024,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 3,
          infinite: true,
          dots: true
        }
      },
      {
        breakpoint: 770,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ]
  })

  $('.brands-logo').slick({
   
    infinite: false,
    speed: 300,
    slidesToShow: 4,
    slidesToScroll: 1,
  
 
  
    prevArrow:"<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
    nextArrow:"<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
    responsive: [
      {
        breakpoint: 1024,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 3,
          infinite: true,
          dots: true
        }
      },
      {
        breakpoint: 770,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2
        }
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1
        }
      }
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ]
  })
});