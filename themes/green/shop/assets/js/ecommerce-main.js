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
    //$("#cart-contents").show()
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

function update_cart_item(t, e, a, s, i) {
  $.ajax({
      url: t,
      type: "POST",
      data: e,
      success: function(t) {
          t.error ? ("text" == i ? s.val(a) : s.selectpicker("val", $po),
          sa_alert("Error!", t.message, "error", !0)) : (t.cart && (cart = t.cart,
          update_mini_cart(cart),
          update_cart(cart)),
          sa_alert(t.status, t.message))
      },
      error: function() {
          sa_alert("Error!", "Ajax call failed, please try again or contact site owner.", "error", !0)
      }
  })
}

function update_cart(t) {
  if (t.total_items && t.total_items > 0) {
      $("#cart-table tbody").empty();
      var e = 1;
      $.each(t.contents, function() {
          var t = this
            , a = '\n            <td class="text-center">\n            <a href="#" class="text-red remove-item" data-rowid="' + this.rowid + '"><i class="fa fa-trash-o"></i><a>\n            </td>\n            <td><input type="hidden" name="' + e + '[rowid]" value="' + this.rowid + '">' + e + '</td>\n            <td>\n            <a href="' + site.site_url + "/product/" + this.slug + '"><span class="cart-item-image pull-right"><img src="' + site.base_url + "assets/uploads/thumbs/" + this.image + '" alt=""></span></a>\n            </td>\n            <td><a href="' + site.site_url + "/product/" + this.slug + '">' + this.name + "</a></td>\n            <td>";
          this.options && (a += '<select name="' + e + '[option]" class="selectpicker mobile-device cart-item-option" data-width="100%" data-style="btn-default">',
          $.each(this.options, function() {
              a += '<option value="' + this.id + '" ' + (this.id == t.option ? "selected" : "") + ">" + this.name + " " + (0 != parseFloat(this.price) ? "(+" + this.price + ")" : "") + "</option>"
          }),
          a += "</select>"),
          a += '</td>\n            <td><input type="text" name="' + e + '[qty]" class="form-control text-center input-qty cart-item-qty" value="' + this.qty + '"></td>\n            <td class="text-right">' + this.price + '</td>\n            <td class="text-right">' + this.subtotal + "</td>\n            ",
          e++,
          $('<tr id="' + this.rowid + '">' + a + "</tr>").appendTo("#cart-table tbody")
      }),
      $("#cart-totals").empty();
      var a = "<tr><td>" + lang.total_w_o_tax + '</td><td class="text-right">' + t.subtotal + "</td></tr>";
      a += "<tr><td>" + lang.product_tax + '</td><td class="text-right">' + t.total_item_tax + "</td></tr>",
      a += "<tr><td>" + lang.total + '</td><td class="text-right">' + t.total + "</td></tr>",
      !1 !== site.settings.tax2 && (a += "<tr><td>" + lang.order_tax + '</td><td class="text-right">' + t.order_tax + "</td></tr>"),
      a += "<tr><td>" + lang.shipping + ' *</td><td class="text-right">' + t.shipping + "</td></tr>",
      a += '<tr><td colspan="2"></td></tr>',
      a += '<tr class="active text-bold"><td>' + lang.grand_total + '</td><td class="text-right">' + t.grand_total + "</td></tr>",
      $("<tbody>" + a + "</tbody>").appendTo("#cart-totals"),
      $("#total-items").text(t.total_items + "(" + t.total_unique_items + ")"),
      //$(".cart-item-option").selectpicker("refresh"),
      $(".cart-empty-msg").hide(),
      $(".cart-contents").show()
  } else
      $("#total-items").text(t.total_items),
      $(".cart-contents").hide(),
      $(".cart-empty-msg").show()
}

function formatMoney(t, e) {
  if (e || (e = site.settings.symbol),
  1 == site.settings.sac)
      return (1 == site.settings.display_symbol ? e : "") + "" + formatSA(parseFloat(t).toFixed(site.settings.decimals)) + (2 == site.settings.display_symbol ? e : "");
  var a = accounting.formatMoney(t, e, site.settings.decimals, 0 == site.settings.thousands_sep ? " " : site.settings.thousands_sep, site.settings.decimals_sep, "%s%v");
  return (1 == site.settings.display_symbol ? e : "") + a + (2 == site.settings.display_symbol ? e : "")
}

function formatSA(t) {
  t = t.toString();
  var e = "";
  t.indexOf(".") > 0 && (e = t.substring(t.indexOf("."), t.length)),
  t = Math.floor(t),
  t = t.toString();
  var a = t.substring(t.length - 3)
    , s = t.substring(0, t.length - 3);
  return "" != s && (a = "," + a),
  s.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + a + e
}

function sa_alert(t, e, a, s) {
  a = a || "success",
  s = s || !1,
  swal({
      title: t,
      html: e,
      type: a,
      timer: s ? 6e4 : 2e3,
      confirmButtonText: "Okay"
  }).catch(swal.noop)
}

function saa_alert(t, e, a, s) {
  a = a || lang.delete,
  e = e || lang.x_reverted_back,
  s = s || {},
  s._method = a,
  s[site.csrf_token] = site.csrf_token_value,
  swal({
      title: lang.r_u_sure,
      html: e,
      type: "question",
      showCancelButton: !0,
      allowOutsideClick: !1,
      showLoaderOnConfirm: !0,
      preConfirm: function() {
          return new Promise(function() {
              $.ajax({
                  url: t,
                  type: "POST",
                  data: s,
                  success: function(t) {
                      if (t.redirect)
                          return window.location.href = t.redirect,
                          !1;
                      t.cart && (cart = t.cart,
                      update_mini_cart(cart),
                      update_cart(cart)),
                      sa_alert(t.status, t.message)
                  },
                  error: function() {
                      sa_alert("Error!", "Ajax call failed, please try again or contact site owner.", "error", !0)
                  }
              })
          }
          )
      }
  }).catch(swal.noop)
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
  if (t || (e += '<div class="col-lg-3 col-md-4 col-sm-12"><div class="alert alert-warning text-center padding-xl margin-top-lg"><h4 class="margin-bottom-no">' + lang.x_product + "</h4></div></div>"),
  1 == site.settings.products_page && ($("#results").empty(),
  $(".grid").isotope("destroy").isotope()),
  $.each(t, function(a, r) {
      var n = r.special_price ? r.special_price : r.price
        , l = r.special_price ? r.formated_special_price : r.formated_price
        , c = (r.promotion && r.promo_price && 0 != r.promo_price && r.promo_price,
      r.promotion && r.promo_price && 0 != r.promo_price ? r.formated_promo_price : l);
      //1 != site.settings.products_page && (0 === a ? e += '<div class="row">' : a % s == 0 && (e += '</div><div class="row">')),
      //e += '<div class="product-container ' + i + " " + (1 == site.settings.products_page ? "grid-item" : "") + '">\n        <div class="product ' + o + " " + (1 == site.settings.products_page ? "grid-sizer" : "") + '">\n        ' + (r.promo_price ? '<span class="badge badge-right theme">Promo</span>' : "") + '\n        <div class="product-top">\n        <div class="product-image">\n        <a href="' + site.site_url + "product/" + r.slug + '">\n        <img class="img-responsive" src="' + site.base_url + "assets/uploads/" + r.image + '" alt=""/>\n        </a>\n        </div>\n        <div class="product-desc">\n        <a href="' + site.site_url + "product/" + r.slug + '">\n        <h2 class="product-name">' + r.name + "</h2>\n        </a>\n        <p>" + r.details + '</p>\n        </div>\n        </div>\n        <div class="clearfix"></div>\n        ' + (1 == site.shop_settings.hide_price ? "" : '\n        <div class="product-bottom">\n        <div class="product-price">\n        ' + (r.promo_price ? '<del class="text-danger text-size-sm">' + l + "</del>" : "") + "\n        " + c + '\n        </div>\n        <div class="product-rating">\n        <div class="form-group" style="margin-bottom:0;">\n        <div class="input-group">\n        <span class="input-group-addon pointer btn-minus"><span class="fa fa-minus"></span></span>\n        <input type="text" name="quantity" class="form-control text-center quantity-input" value="1" required="required">\n        <span class="input-group-addon pointer btn-plus"><span class="fa fa-plus"></span></span>\n        </div>\n        </div>\n        </div>\n        <div class="clearfix"></div>\n        <div class="product-cart-button">\n        <div class="btn-group" role="group" aria-label="...">\n        <button class="btn btn-info add-to-wishlist" data-id="' + r.id + '"><i class="fa fa-heart-o"></i></button>\n        <button class="btn btn-theme add-to-cart" data-id="' + r.id + '"><i class="fa fa-shopping-cart padding-right-md"></i> ' + lang.add_to_cart + '</button>\n        </div>\n        </div>\n        <div class="clearfix"></div>\n        </div>') + '\n        </div>\n        <div class="clearfix"></div>\n        </div>',
      //1 != site.settings.products_page && a + 1 === t.length && (e += "</div>")

      e += '<div class="col-lg-3 col-md-4 col-sm-12">';
      e += '<div class="card" style="width: 100%">';
      //e += '<a href="#" class="text-decoration-none">';
      e += '<div class="cardImg">';
      e += '<a href="'+site.base_url+'product/'+r.slug+'" class="text-decoration-none">';
      e += '<img src="'+site.base_url + "assets/uploads/" + r.image+'" class="card-img-top" alt="...">';
      e += '</a>';
      e += '</div>';
      e += '<div class="card-body px-0 text-start pb-0">';
      e += '<div class="product-cat-title"><span class="text-uppercase">'+r.category_name+'</span></div>';
      e += '<a href="'+site.base_url+'product/'+r.slug+'" class="text-decoration-none">';
      e += '<h5 class="card-title text-start">'+r.name+'</h5>';
      e += '</a>';
      e += '<div class="row align-items-center justify-content-between">';
      e += '<div class="col-md-6"><div class="rating"><i class="bi bi-star-fill rated"></i><i class="bi bi-star-fill rated"></i><i class="bi bi-star-fill rated"></i><i class="bi bi-star-fill"></i></div></div>';
      if(r.promotion){
        e += '<div class="col-md-6"><div class="discountPrice price text-end py-2"><h4 class="m-0 text-decoration-line-through">'+l+'</h4></div></div>';
      }
      e += '</div>';
      e += '<div class="row align-items-center justify-content-between">';
      e += '<div class="col-md-6 "><div class="price text-start  py-2"><h4 class="m-0 fw-bold">';
      if(r.promotion){
        e += r.formated_promo_price;
      }else{
        e += l;
      }
      e += '</h4></div></div>';
      e += '<div class="col-md-6">';
      e += '<div class="quantity text-end py-2 d-flex align-items-center justify-content-between">';
      e += '<span class="plus btn-plus"><i class="bi bi-plus-circle-fill"></i></span>';
      //e += '<span class="Qnum ">1</span>';
      e += '<input type="text" name="quantity" class="Qnum" value="1" required="required" />';
      e += '<span class="minus btn-minus"><i class="bi bi-dash-circle-fill"></i></span>';
      e += '</div>';
      e += '</div>';
      e += '</div>';
      e += '</div>';
      //e += '</a>';
      e += '<div>';
      e += '<button type="button" data-id="'+r.id+'" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark add-to-cart">Add to cart </button>';
      e += '</div>';
      e += '</div>';
      e += '</div>';
      e += '</div>';

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

  $(".product").each(function(t, e) {
      $(e).find(".details").hover(function() {
          $(this).parent().css("z-index", "20"),
          $(this).addClass("animate")
      }, function() {
          $(this).removeClass("animate"),
          $(this).parent().css("z-index", "1")
      })
  });

  update_cart(cart);

  $(document).on("change", ".cart-item-option, .cart-item-qty", function(t) {
      t.preventDefault();
      var e = this.defaultValue
        , a = $(this).closest("tr")
        , s = a.attr("id")
        , i = site.site_url + "cart/update"
        , o = {};
      o[site.csrf_token] = site.csrf_token_value,
      o.rowid = s,
      o.qty = a.find(".cart-item-qty").val(),
      o.option = a.find(".cart-item-option").children("option:selected").val(),
      update_cart_item(i, o, e, $(this), t.target.type)
  });
  


  var slider = $("#slider");
  var thumb = $("#thumb");
  var slidesPerPage = 4; //globaly define number of elements per page
  var syncedSecondary = true;
  slider.owlCarousel({
      items: 1,
      slideSpeed: 2000,
      nav: false,
      autoplay: false, 
      dots: false,
      loop: true,
      responsiveRefreshRate: 200
  }).on('changed.owl.carousel', syncPosition);

  thumb
  .on('initialized.owl.carousel', function() {
      thumb.find(".owl-item").eq(0).addClass("current");
  })
  .owlCarousel({
      items: slidesPerPage,
      dots: false,
      nav: true,
      item: 4,
      smartSpeed: 200,
      slideSpeed: 500,
      slideBy: slidesPerPage, 
    navText: ['<i class="bi bi-arrow-left-square-fill"></i>', '<i class="bi bi-arrow-right-square-fill"></i>'],
      responsiveRefreshRate: 100
  }).on('changed.owl.carousel', syncPosition2);

  function syncPosition(el) {
    var count = el.item.count - 1;
    var current = Math.round(el.item.index - (el.item.count / 2) - .5);
    if (current < 0) {
        current = count;
    }
    if (current > count) {
        current = 0;
    }
    thumb
        .find(".owl-item")
        .removeClass("current")
        .eq(current)
        .addClass("current");
    var onscreen = thumb.find('.owl-item.active').length - 1;
    var start = thumb.find('.owl-item.active').first().index();
    var end = thumb.find('.owl-item.active').last().index();
    if (current > end) {
        thumb.data('owl.carousel').to(current, 100, true);
    }
    if (current < start) {
        thumb.data('owl.carousel').to(current - onscreen, 100, true);
    }
  }

  function syncPosition2(el) {
    if (syncedSecondary) {
        var number = el.item.index;
        slider.data('owl.carousel').to(number, 100, true);
    }
  }

  thumb.on("click", ".owl-item", function(e) {
    e.preventDefault();
    var number = $(this).index();
    slider.data('owl.carousel').to(number, 300, true);
  });

  $(".qtyminus").on("click",function(){
      var now = $(".qty").val();
      if ($.isNumeric(now)){
          if (parseInt(now) -1> 0)
          { now--;}
          $(".qty").val(now);
      }
  });      

  $(".qtyplus").on("click",function(){
      var now = $(".qty").val();
      if ($.isNumeric(now)){
          $(".qty").val(parseInt(now)+1);
      }
  });

  const productImages = document.querySelectorAll('.productzoomImg img');

  productImages.forEach((image) => {
    image.addEventListener('mouseover', () => {
      zoomIn(image);
    });

    image.addEventListener('mouseout', () => {
      zoomOut(image);
    });

    image.addEventListener('mousemove', (e) => {
      zoomMove(e, image);
    });
  });

  function zoomIn(image) {
    image.style.transform = 'scale(1.5)';
  }

  function zoomOut(image) {
    image.style.transform = 'scale(1)';
  }

  function zoomMove(e, image) {
    const imageRect = image.getBoundingClientRect();
    const x = e.clientX - imageRect.left;
    const y = e.clientY - imageRect.top;

    const scaleX = 1.5;
    const scaleY = 1.5;

    const transformOriginX = (x / imageRect.width) * 100;
    const transformOriginY = (y / imageRect.height) * 100;

    image.style.transformOrigin = `${transformOriginX}% ${transformOriginY}%`;
  }

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