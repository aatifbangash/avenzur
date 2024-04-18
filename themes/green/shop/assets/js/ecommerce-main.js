// Find the source and target divs by their ids
var buttonToMove = document.getElementById("menuiconMob");
var sourceDiv = document.getElementById("sourcedivmob");
var targetDiv = document.getElementById("shoppingdivMob");
var saledivToMove = document.getElementById("salediv");
var sourcesalemob = document.getElementById("salemob");
var targetmobnav = document.getElementById("mobnav");
var cartToMove = document.getElementById("cart-items");
var cartsourceDiv = document.getElementById("cartdiv");
var allCatToMove = document.getElementById("allCatmob");
var catsourceDiv = document.getElementById("allcatDiv");
var targetmenuDiv = document.getElementById("navbarSupportedContent");
// Check if the screen width is less than a certain threshold (e.g., 768 pixels for typical mobile screens)

// Remove the button from the source div
sourceDiv.removeChild(buttonToMove);

// Append the button to the target div
targetDiv.appendChild(buttonToMove);

//     seaerch bar =================================
var toggleSearch = document.getElementById("searchtoggle");
var searchBar = document.getElementById("searchbarmob");
var toggleSearchcros = document.getElementById("searchtogglecros");
// Add a click event listener to the button
toggleSearch.addEventListener("click", function () {
  // Toggle the visibility of the div
  if (searchBar.style.display === "block") {
    searchBar.style.display = "none";
  } else {
    searchBar.style.display = "block";
  }
});
toggleSearchcros.addEventListener("click", function () {
  // Toggle the visibility of the div
  if (searchBar.style.display === "none") {
    searchBar.style.display = "block";
  } else {
    searchBar.style.display = "none";
  }
});
function sa_img(t, e) {
  swal({
    title: t,
    html: e,
    type: "success",
    confirmButtonText: lang.okay,
  }).catch(swal.noop);
}

function update_popup_cart(t) {
  //if (t.total_items && t.total_items > 0) {
  $("#product-canvas-body").html("");
  $.each(t.contents, function () {
    /*var t =
        '<div class=" row align-items-center">' +
        '<div class="addicon col-md-3 px-0">' +
        '<img src="' +
        site.base_url +
        "assets/uploads/" +
        this.image +
        '" class="w-100">' +
        "</div>" +
        '<div class=" col-md-9">' +
        '<p class="m-0 fs-5 fw-semibold text-start">' +
        this.name +
        "</p>" +
        '<p class="m-0 fs-5 fw-semibold mt-2 text-end pe-4">' +
        this.subtotal +
        "</p>" +
        "</div></div><hr>";

        $("#product-popup-modal-body").append(t);*/

    var t =
      '<div class="d-flex align-items-center justify-content-center cart-cont-row">' +
      '<div class="addicon">' +
      '<img width="80" height="80" src="' +
      site.base_url +
      "assets/uploads/" +
      this.image +
      '" class="w-100" />' +
      "</div>" +
      '<div class=" title-price-cont">' +
      '<p class="m-0 product-title fw-semibold text-start">' +
      this.name + '<span style="margin-left:5px;">('+ this.qty +')</span>' +
      "</p>" +
      '<p class="m-0 product-price fw-semibold mt-2 text-end pe-4 d-flex justify-content-between align-items-center">' +
      '<a href="#" data-rowid="' +
      this.rowid +
      '" class="text-red remove-item-sidepopup text-decoration-none text-danger fs-6 fw-normal"><i class="fa fa-trash-o"></i> Remove</a>' +
      this.subtotal +
      "</p>" +
      "</div>" +
      "</div><hr />";

    $("#product-canvas-body").append(t);
  });

  $("#product-canvas-total").html(t.total);

  /*var e =
      '<div class=" row align-items-center mt-4">' +
      '<div class="addicon col-md-3 px-0">' +
      '<p class="m-0 fs-5 fw-semibold text-start text-dark">Cart Total</p>' +
      "</div>" +
      '<div class=" col-md-9">' +
      '<p class="m-0 fs-5 fw-semibold mt-2 text-end text-dark">' +
      t.total +
      "</p>" +
      "</div></div>";
    $("#product-popup-modal-body").append(e);*/
  //}
}
function update_mini_cart(t) {
  if (t.total_items && t.total_items > 0) {
    var cart_table =
      "<thead><tr><th>Image</th><th>Name</th><th>Price</th></tr></thead>";
    cart_table += '<tbody id="cart-body"></tbody>';
    cart_table += '<tfoot id="cart-foot"></tfoot>';
    $("#cart-items-table").html(cart_table);

    $(".cart-total-items").show();
    $(".cart-total-items").text(t.total_unique_items);
    //$(".cart-total-items").text(t.total_items + " " + (t.total_items > 1 ? lang.items : lang.item));
    $("#cart-body").empty(),
      $.each(t.contents, function () {
        var t =
          '<td><a href="' +
          site.site_url +
          "/product/" +
          this.slug +
          '"><span class="cart-item-image"><img style="width: 42px;" src="' +
          site.base_url +
          "assets/uploads/" +
          this.image +
          '" alt=""></span></a></td><td><a style="color: #000;font-size: 14px;" href="' +
          site.site_url +
          "/product/" +
          this.slug +
          '">' +
          this.name +
          " (" +
          this.qty +
          ")" +
          "</a><br>" +
          //this.qty +
          //" x " +
          //this.price +
          '</td><td class="text-right text-bold" style="color: #000;font-size: 14px;">' +
          this.subtotal +
          "</td>";
        //$("<tr>" + t + "</tr>").appendTo("#cart-body")
        $("#cart-body").append("<tr>" + t + "</tr>");
      });
    var e =
      '\n  <tr><td class="ar-colCart" colspan="2">' +
      lang.total_items +
      '</td><td class="text-end fw-bold">' +
      t.total_items +
      "</td></tr>\n        <tr><td>" +
      lang.total +
      '</td><td colspan="2" class="text-end fw-bold">' +
      t.total +
      "</td></tr>\n        ";
    $("#cart-foot").html(e);
    //$("#cart-empty").hide();
    //$("#cart-contents").show()
  } else {
    $("#cart-items-table").empty();
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
    success: function (t) {
      t.error
        ? ("text" == i ? s.val(a) : s.selectpicker("val", $po),
          sa_alert("Error!", t.message, "error", !0))
        : t.cart &&
          ((cart = t.cart), update_mini_cart(cart), update_cart(cart));
      //sa_alert(t.status, t.message));
    },
    error: function () {
      sa_alert(
        "Error!",
        "Ajax call failed, please try again or contact site owner.",
        "error",
        !0
      );
    },
  });
}

function update_cart(t) {
  $("#cart-table-new").empty();
  if (t.total_items && t.total_items > 0) {
    var e = 1;
    $.each(t.contents, function () {
      var t = this,
        a =
          '<div class="d-flex align-items-center  py-4">' +
          '<div class="cart-item-image pe-3">' +
          '<img class="cart-img" style="object-fit: contain;" src="' +
          site.base_url +
          "assets/uploads/" +
          this.image +
          '" class="card-img-top" alt="...">' +
          "</div>" +
          '<div class="d-flex flex-column justify-content-between w-100"><div class="d-flex align-items-center justify-content-between flex-mobile-column">' +
          '<h5 class="m-0">' +
          this.name +
          "</h5>" +
          "<div>" +
          '<h4 class="m-0 fw-semibold fs-5 price-label" >' +
          this.price +
          "</h4>" +
          //'<p class="m-0 text-decoration-line-through text-danger text-center fw-semibold mb-4">SAR 10</p>' +
          "</div></div>" +
          '<div class="d-flex justify-content-between align-items-center flex-mobile-column remove-quatity-container"><div>' +
          '<a href="#" data-rowid="' +
          this.rowid +
          '" class="text-red remove-item text-decoration-none text-dark"><i class="fa fa-trash-o"></i> Remove</a></div>' +
          '<div class="quantity text-end py-2 d-flex align-items-center justify-content-between cartQuantity"><h6 class="my-1 me-2">Quantity</h6>' +
          '<span class="plus btn-plus-update"><i class="bi bi-plus-circle-fill"></i></span>' +
          '<span class="fs-6 px-2"><input type="text" style="width: 50px;" name="' +
          e +
          '[qty]" class="form-control text-center input-qty cart-item-qty" value="' +
          this.qty +
          '"></span>' +
          '<span class="minus btn-minus-update"><i class="bi bi-dash-circle-fill"></i></span>' +
          "</div></div></div> </div>";

      $(
        '<div class="cart-content-wrapper" id="' +
          this.rowid +
          '">' +
          a +
          "</div>"
      ).appendTo("#cart-table-new");
      //$(a).appendTo('#cart-table-new');
    });

    $("#total-unique_items").html(t.total_unique_items);
    $("#total-price").html(t.subtotal);
    $("#total-discount").html(t.total_discount);
    $("#total-after_discount").html(t.total);

    /*$("#cart-table tbody").empty();
    var e = 1;
    $.each(t.contents, function () {
      var t = this,
        a =
          '\n            <td class="text-center">\n            <a href="#" class="text-red remove-item" data-rowid="' +
          this.rowid +
          '"><i class="fa fa-trash-o"></i><a>\n            </td>' +
          '\n            <td><input type="hidden" name="' +
          e +
          '[rowid]" value="' +
          this.rowid +
          '">' +
          e +
          '</td>\n            <td>\n            <a href="' +
          site.site_url +
          "/product/" +
          this.slug +
          '"><span class="cart-item-image pull-right"><img src="' +
          site.base_url +
          "assets/uploads/" +
          this.image +
          '" alt=""></span></a>\n            </td>\n            <td><a href="' +
          site.site_url +
          "/product/" +
          this.slug +
          '">' +
          this.name +
          "</a></td>\n            <td>";
      this.options &&
        ((a +=
          '<select name="' +
          e +
          '[option]" class="selectpicker mobile-device cart-item-option" data-width="100%" data-style="btn-default">'),
        $.each(this.options, function () {
          a +=
            '<option value="' +
            this.id +
            '" ' +
            (this.id == t.option ? "selected" : "") +
            ">" +
            this.name +
            " " +
            (0 != parseFloat(this.price) ? "(+" + this.price + ")" : "") +
            "</option>";
        }),
        (a += "</select>")),
        (a +=
          '</td>\n            <td><input type="text" name="' +
          e +
          '[qty]" class="form-control text-center input-qty cart-item-qty" value="' +
          this.qty +
          '"></td>\n            <td class="text-right">' +
          this.price +
          '</td>\n            <td class="text-right">' +
          this.subtotal +
          "</td>\n            "),
        e++,
        $('<tr id="' + this.rowid + '">' + a + "</tr>").appendTo(
          "#cart-table tbody"
        );
    }),
      $("#cart-totals").empty();
    var a =
      "<tr><td>" +
      lang.total_w_o_tax +
      '</td><td class="text-right">' +
      t.subtotal +
      "</td></tr>";
    (a +=
      "<tr><td>" +
      lang.product_tax +
      '</td><td class="text-right">' +
      t.total_item_tax +
      "</td></tr>"),
      (a +=
        "<tr><td>" +
        lang.total +
        '</td><td class="text-right">' +
        t.total +
        "</td></tr>"),
      $("<tbody>" + a + "</tbody>").appendTo("#cart-totals"),
      $("#total-items").text(t.total_items + "(" + t.total_unique_items + ")"),
      //$(".cart-item-option").selectpicker("refresh"),
      $(".cart-empty-msg").hide(),
      $(".cart-contents").show();*/
  } else {
    /*$("#total-items").text(t.total_items),
      $(".cart-contents").hide(),
      $(".cart-empty-msg").show();*/

    $("#total-unique_items").html(t.total_unique_items);
    $("#total-price").html(t.subtotal);
    $("#total-discount").html(t.total_discount);
    $("#total-after_discount").html(t.total);
  }
}

function formatMoney(t, e) {
  if ((e || (e = site.settings.symbol), 1 == site.settings.sac))
    return (
      (1 == site.settings.display_symbol ? e : "") +
      "" +
      formatSA(parseFloat(t).toFixed(site.settings.decimals)) +
      (2 == site.settings.display_symbol ? e : "")
    );
  var a = accounting.formatMoney(
    t,
    e,
    site.settings.decimals,
    0 == site.settings.thousands_sep ? " " : site.settings.thousands_sep,
    site.settings.decimals_sep,
    "%s%v"
  );
  return (
    (1 == site.settings.display_symbol ? e : "") +
    a +
    (2 == site.settings.display_symbol ? e : "")
  );
}

function formatSA(t) {
  t = t.toString();
  var e = "";
  t.indexOf(".") > 0 && (e = t.substring(t.indexOf("."), t.length)),
    (t = Math.floor(t)),
    (t = t.toString());
  var a = t.substring(t.length - 3),
    s = t.substring(0, t.length - 3);
  return (
    "" != s && (a = "," + a), s.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + a + e
  );
}

function sa_alert(t, e, a, s) {
  (a = a || "success"),
    (s = s || !1),
    swal({
      title: t,
      html: e,
      type: a,
      timer: s ? 6e4 : 2e3,
      confirmButtonText: "Okay",
    }).catch(swal.noop);
}

function saaa_alert(t, e, a, s) {
  (a = a || lang.delete),
    (e = e || lang.x_reverted_back),
    (s = s || {}),
    (s._method = a),
    (s[site.csrf_token] = site.csrf_token_value),
    $.ajax({
      url: t,
      type: "POST",
      data: s,
      success: function (t) {
        if (t.redirect) return (window.location.href = t.redirect), !1;
        t.cart &&
          ((cart = t.cart),
          update_mini_cart(cart),
          update_cart(cart),
          update_popup_cart(cart));
      },
      error: function () {
        /*sa_alert(
        "Error!",
        "Ajax call failed, please try again or contact site owner.",
        "error",
        !0
      );*/
      },
    });
}

function saa_alert(t, e, a, s) {
  (a = a || lang.delete),
    (e = e || lang.x_reverted_back),
    (s = s || {}),
    (s._method = a),
    (s[site.csrf_token] = site.csrf_token_value),
    $.ajax({
      url: t,
      type: "POST",
      data: s,
      success: function (t) {
        if (t.redirect) return (window.location.href = t.redirect), !1;
        t.cart && ((cart = t.cart), update_mini_cart(cart), update_cart(cart));

        if (
          t.cart.total_items <= 0 ||
          typeof t.cart.total_items == "undefined"
        ) {
          window.location.href = "shop/products";
        } else {
          console.log(t.total_items);
        }
        //sa_alert(t.status, t.message);
      },
      error: function () {
        /*sa_alert(
        "Error!",
        "Ajax call failed, please try again or contact site owner.",
        "error",
        !0
      );*/
      },
    });

  /*(a = a || lang.delete),
    (e = e || lang.x_reverted_back),
    (s = s || {}),
    (s._method = a),
    (s[site.csrf_token] = site.csrf_token_value),
    swal({
      title: lang.r_u_sure,
      html: e,
      type: "question",
      showCancelButton: !0,
      allowOutsideClick: !1,
      showLoaderOnConfirm: !0,
      preConfirm: function () {
        return new Promise(function () {
          $.ajax({
            url: t,
            type: "POST",
            data: s,
            success: function (t) {
              if (t.redirect) return (window.location.href = t.redirect), !1;
              t.cart &&
                ((cart = t.cart), update_mini_cart(cart), update_cart(cart)),
                sa_alert(t.status, t.message);
            },
            error: function () {
              sa_alert(
                "Error!",
                "Ajax call failed, please try again or contact site owner.",
                "error",
                !0
              );
            },
          });
        });
      },
    }).catch(swal.noop);*/
}

function prompt(t, e, a) {
  (t = t || "Reset Password"),
    (e = e || "Please type your email address"),
    (a = a || {}),
    (a[site.csrf_token] = site.csrf_token_value),
    swal({
      title: t,
      html: e,
      input: "email",
      showCancelButton: !0,
      allowOutsideClick: !1,
      showLoaderOnConfirm: !0,
      cancelButtonText: lang.cancel,
      confirmButtonText: lang.submit,
      preConfirm: function (t) {
        return (
          (a.email = t),
          new Promise(function (t, e) {
            $.ajax({
              url: site.base_url + "forgot_password",
              type: "POST",
              data: a,
              success: function (a) {
                a.status ? t(a) : e(a);
              },
              error: function () {
                sa_alert(
                  "Error!",
                  "Ajax call failed, please try again or contact site owner.",
                  "error",
                  !0
                );
              },
            });
          })
        );
      },
    }).then(function (t) {
      sa_alert(t.status, t.message);
    });
}

function get(t) {
  if ("undefined" != typeof Storage) return localStorage.getItem(t);
  alert("Please use a modern browser as this site needs localstroage!");
}
function store(t, e) {
  "undefined" != typeof Storage
    ? localStorage.setItem(t, e)
    : alert("Please use a modern browser as this site needs localstroage!");
}
function remove(t) {
  "undefined" != typeof Storage
    ? localStorage.removeItem(t)
    : alert("Please use a modern browser as this site needs localstroage!");
}

function gen_html(t) {
  var e = "";
  if (get_width() > 992)
    var a = get("shop_grid"),
      s = ".three-col" == a ? 3 : 4;
  else
    var a = ".four-col",
      s = 4;
  var i = a && ".three-col" == a ? "col-sm-6 col-md-4" : "col-md-6",
    o = a && ".three-col" == a ? "alt" : "";
  if (
    (t ||
      (e +=
        '<div class=" col-lg-12"><div class="alert alert-warning text-center padding-xl margin-top-lg"><h4 class="margin-bottom-no">' +
        lang.x_product +
        "</h4></div></div>"),
    1 == site.settings.products_page &&
      ($("#results").empty(), $(".grid").isotope("destroy").isotope()),
    $.each(t, function (a, r) {
      var n = r.special_price ? r.special_price : r.price,
        l = r.special_price ? r.formated_special_price : r.formated_price,
        c =
          (r.promotion && r.promo_price && 0 != r.promo_price && r.promo_price,
          r.promotion && r.promo_price && 0 != r.promo_price
            ? r.formated_promo_price
            : l);
      //  console.log('quantitiy', parseFloat(r.quantity));
      //1 != site.settings.products_page && (0 === a ? e += '<div class="row">' : a % s == 0 && (e += '</div><div class="row">')),
      //e += '<div class="product-container ' + i + " " + (1 == site.settings.products_page ? "grid-item" : "") + '">\n        <div class="product ' + o + " " + (1 == site.settings.products_page ? "grid-sizer" : "") + '">\n        ' + (r.promo_price ? '<span class="badge badge-right theme">Promo</span>' : "") + '\n        <div class="product-top">\n        <div class="product-image">\n        <a href="' + site.site_url + "product/" + r.slug + '">\n        <img class="img-responsive" src="' + site.base_url + "assets/uploads/" + r.image + '" alt=""/>\n        </a>\n        </div>\n        <div class="product-desc">\n        <a href="' + site.site_url + "product/" + r.slug + '">\n        <h2 class="product-name">' + r.name + "</h2>\n        </a>\n        <p>" + r.details + '</p>\n        </div>\n        </div>\n        <div class="clearfix"></div>\n        ' + (1 == site.shop_settings.hide_price ? "" : '\n        <div class="product-bottom">\n        <div class="product-price">\n        ' + (r.promo_price ? '<del class="text-danger text-size-sm">' + l + "</del>" : "") + "\n        " + c + '\n        </div>\n        <div class="product-rating">\n        <div class="form-group" style="margin-bottom:0;">\n        <div class="input-group">\n        <span class="input-group-addon pointer btn-minus"><span class="fa fa-minus"></span></span>\n        <input type="text" name="quantity" class="form-control text-center quantity-input" value="1" required="required">\n        <span class="input-group-addon pointer btn-plus"><span class="fa fa-plus"></span></span>\n        </div>\n        </div>\n        </div>\n        <div class="clearfix"></div>\n        <div class="product-cart-button">\n        <div class="btn-group" role="group" aria-label="...">\n        <button class="btn btn-info add-to-wishlist" data-id="' + r.id + '"><i class="fa fa-heart-o"></i></button>\n        <button class="btn btn-theme add-to-cart" data-id="' + r.id + '"><i class="fa fa-shopping-cart padding-right-md"></i> ' + lang.add_to_cart + '</button>\n        </div>\n        </div>\n        <div class="clearfix"></div>\n        </div>') + '\n        </div>\n        <div class="clearfix"></div>\n        </div>',
      //1 != site.settings.products_page && a + 1 === t.length && (e += "</div>")

      e += '<div class="col-xl-3 col-lg-4 col-md-6 col-6 product-cards-cont">';
      e += '<div class="card" style="width: 100%">';
      //e += '<a href="#" class="text-decoration-none">';
      e += '<div class="cardImg">';
      if (r.promotion && r.price > 0 && r.promo_price > 0) {
        e +=
          '<span class="position-absolute badge rounded-pill bg-danger" style="top:0px;left:10px;font-size:11px">' +
          Math.round(((r.price - r.promo_price) / r.price) * 100) +
          "% OFF</span>";
      }
      e +=
        '<a href="' +
        site.base_url +
        "product/" +
        r.slug +
        '" class="text-decoration-none">';
      e +=
        '<img src="' +
        site.base_url +
        "assets/uploads/" +
        r.image +
        '" class="card-img-top" alt="...">';
      e += "</a>";
      e += "</div>";
      e += '<div class="card-body px-0 text-start pb-0">';
      e +=
        '<div class="product-cat-title"><span class="text-uppercase">' +
        r.category_name +
        "</span></div>";
      e +=
        '<a href="' +
        site.base_url +
        "product/" +
        r.slug +
        '" class="text-decoration-none">';
      e += '<h5 class="card-title text-start">' + r.name + "</h5>";
      e += "</a>";
      e += '<div class="d-flex align-items-center justify-content-between">';
      e += '<div class="rating">';
      for (i = 1; i <= 5; i++) {
        if (i <= r.avg_rating) {
          e += '<i class="bi bi-star-fill rated"></i>';
        } else {
          e += '<i class="bi bi-star-fill"></i>';
        }
      }
      e += "</div>";

      if (r.promotion) {
        e +=
          '<div class="discountPrice price text-end py-2"><h4 class="m-0 text-decoration-line-through">' +
          l +
          "</h4></div>";
      }
      e += "</div>";
      e += '<div class="d-flex align-items-center justify-content-between">';
      e += '<div class="price text-start  py-2"><h4 class="m-0 fw-bold">';
      if (r.promotion) {
        e += r.formated_promo_price;
      } else {
        e += l;
      }
      e += "</h4></div>";

      e +=
        '<div class="quantity text-end py-2 d-flex align-items-center justify-content-between">';
      e +=
        '<span class="plus btn-plus"><i class="bi bi-plus-circle-fill"></i></span>';
      //e += '<span class="Qnum ">1</span>';
      e +=
        '<input type="text" name="quantity" class="Qnum" value="1" required="required" />';
      e +=
        '<span class="minus btn-minus"><i class="bi bi-dash-circle-fill"></i></span>';
      e += "</div>";
      e += "</div>";
      e += "</div>";

      //e += '</a>';
      e += "<div>";
      const prod_quantity = parseFloat(r.product_quantity);
      if (isNaN(prod_quantity) || prod_quantity === 0) {
        e += "Out of Stock ";
        e +=
          '<button type="button" class="btn btn-link btn-notify-add-to-list" href="#" data-id="' +
          r.id +
          '" data-title="' +
          r.name +
          '" data-image="' +
          r.image +
          '" data-price="' +
          (r.promotion ? r.formated_promo_price : l) +
          '" >Notify me</button>';
      } else {
        e +=
          '<button type="button" data-id="' +
          r.id +
          '" class="btn primary-buttonAV mt-3 py-1 addtocart w-100 text-dark add-to-cart" aria-controls="offcanvasWithBothOptions">Add to cart </button>';
      }
      e += "</div>";
      e += "</div>";
      e += "</div>";
      e += "</div>";
    }),
    1 != site.settings.products_page)
  )
    $("#results").empty(), $(e).appendTo($("#results"));
  else {
    var r = $(e);
    $(".grid").isotope("insert", r).isotope("layout"),
      setTimeout(function () {
        $(".grid").isotope({
          itemSelector: ".grid-item",
        });
      }, 200);
  }
}

function get_width() {
  return $(window).width();
}

function get_filters() {
  return (
    (filters.category = $("#product-category").val()
      ? $("#product-category").val()
      : filters.category),
    (filters.min_price = $("#min-price").val()),
    (filters.max_price = $("#max-price").val()),
    (filters.in_stock = $("#in-stock").is(":checked") ? 1 : 0),
    (filters.promo = $("#promotions").is(":checked") ? "yes" : 0),
    (filters.featured = $("#featured").is(":checked") ? "yes" : 0),
    (filters.sorting = get("sorting")),
    filters
  );
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
  var special_product = 0;
  var callUrl;
  var currentURL = window.location.href;
  if (currentURL.includes("promo=yes")) {
    promo = 1;
    callUrl = site.shop_url + "search?page=" + filters.page + "&promo=yes";
  } else {
    callUrl = site.shop_url + "search?page=" + filters.page;
  }

  if (currentURL.includes("special_product=yes")) {
    special_product = 1;
    callUrl =
      site.shop_url + "search?page=" + filters.page + "&special_product=yes";
  } else {
    callUrl = site.shop_url + "search?page=" + filters.page;
  }

  $("#loading").show();
  var a = {};
  (a[site.csrf_token] = site.csrf_token_value),
    (a.filters = get_filters()),
    (a.filters.promo = promo),
    (a.filters.special_product = special_product),
    (a.format = "json"),
    $.ajax({
      url: callUrl,
      type: "POST",
      data: a,
      dataType: "json",
    })
      .done(function (t) {
        (products = t.products),
          $(".page-info").empty(),
          $("#pagination").empty(),
          t.products &&
            (t.pagination && $("#pagination").html(t.pagination),
            t.info &&
              $(".page-info").text(
                lang.page_info
                  .replace("_page_", t.info.page)
                  .replace("_total_", t.info.total)
              )),
          gen_html(products);
      })
      .always(function () {
        $("#loading").hide();
      }),
    location.href.includes("products") &&
      t &&
      (window.history.pushState(
        {
          link: t,
          filters: filters,
        },
        "",
        t
      ),
      (window.onpopstate = function (t) {
        t.state && t.state.filters
          ? ((filters = t.state.filters), searchProducts())
          : ((filters.page = 1), searchProducts());
      })),
    setTimeout(function () {
      window.scrollTo(0, 0);
    }, 500);
}

// slick slider =====================
$(document).ready(function () {
  var curr_url = window.location.href;
  if (curr_url != "https://avenzur.com/") {
    searchProducts();
  }

  $(document).on("click", function (event) {

    if (!$('.offcanvas').is(event.target) && $('.offcanvas').has(event.target).length === 0 && $('.offcanvas').hasClass('show')) {
        // Hide the offcanvas
        //$('.offcanvas').offcanvas('hide');
        $('.offcanvas').removeClass('show');
        $('.offcanvas-backdropaddP').removeClass('show');
    }

    var popup = $("#myaccountForm");
    var link = $(".checkout-link");
    var logintrigegr = $("#login-btn-trigger");

    // Check if the clicked element is not inside the popup
    if (
      !popup.is(event.target) &&
      popup.has(event.target).length === 0 &&
      !link.is(event.target) &&
      link.has(event.target).length === 0 &&
      !logintrigegr.is(event.target) &&
      logintrigegr.has(event.target).length === 0
    ) {
      // Close the popup
      // popup.hide();
      popup.removeClass("show");
    }
  });

  /*$(document).on("click", '#login-btn-trigger', function (event){
    $('#myaccountForm').show();
  });*/

  $(document).on("click", ".checkout-link", function (event) {
    // $("#cart-contents").hide();
    $("#productPop").modal("hide");
    var popup = $("#myaccountForm");
    // var myaccountForm = document.getElementById("myaccountForm");
    // myaccountForm.style.position = "absolute";
    // myaccountForm.style.inset = "0px auto auto 0px";
    // myaccountForm.style.margin = "0px";
    // myaccountForm.style.transform = "translate3d(-240px, 38.4px, 0px)";
    // $("#myaccountForm").show();
    //added this line of code by kamal
    $("#login-btn-trigger").trigger("click");
    popup.addClass("show");
  });

  $(document).on("click", "#pagination a", function (t) {
    t.preventDefault();
    var e = $(this).attr("href"),
      a = e.split("page=");
    if (a[1]) {
      var s = a[1].split("&");
      filters.page = s[0];
    } else filters.page = 1;
    return searchProducts(e), !1;
  });

  $(".product").each(function (t, e) {
    $(e)
      .find(".details")
      .hover(
        function () {
          $(this).parent().css("z-index", "20"), $(this).addClass("animate");
        },
        function () {
          $(this).removeClass("animate"), $(this).parent().css("z-index", "1");
        }
      );
  });

  $(document).on("click", ".remove-item", function (t) {
    t.preventDefault();
    var e = {};
    (e.rowid = $(this).attr("data-rowid")),
      saa_alert(site.site_url + "cart/remove", !1, "post", e);
  }),
    $(document).on("click", ".remove-item-sidepopup", function (t) {
      t.preventDefault();
      var e = {};
      (e.rowid = $(this).attr("data-rowid")),
        saaa_alert(site.site_url + "cart/remove", !1, "post", e);
    }),
    $("#empty-cart").click(function (t) {
      t.preventDefault(), saa_alert($(this).attr("href"));
    });

  update_cart(cart);

  $(document).on("change", ".cart-item-option, .cart-item-qty", function (t) {
    t.preventDefault();
    var e = this.defaultValue,
      a = $(this).closest(".cart-content-wrapper"),
      s = a.attr("id"),
      i = site.site_url + "cart/update",
      o = {};
    (o[site.csrf_token] = site.csrf_token_value),
      (o.rowid = s),
      (o.qty = a.find(".cart-item-qty").val()),
      /*(o.option = a
        .find(".cart-item-option")
        .children("option:selected")
        .val()),*/
      update_cart_item(i, o, e, $(this), t.target.type);
  });

  var slider = $("#slider");
  var thumb = $("#thumb");
  var slidesPerPage = 4; //globaly define number of elements per page
  var syncedSecondary = true;
  slider
    .owlCarousel({
      items: 1,
      slideSpeed: 2000,
      nav: false,
      autoplay: false,
      dots: false,
      loop: true,
      responsiveRefreshRate: 200,
    })
    .on("changed.owl.carousel", syncPosition);

  thumb
    .on("initialized.owl.carousel", function () {
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
      navText: [
        '<i class="bi bi-arrow-left-square-fill"></i>',
        '<i class="bi bi-arrow-right-square-fill"></i>',
      ],
      responsiveRefreshRate: 100,
    })
    .on("changed.owl.carousel", syncPosition2);

  function syncPosition(el) {
    var count = el.item.count - 1;
    var current = Math.round(el.item.index - el.item.count / 2 - 0.5);
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
    var onscreen = thumb.find(".owl-item.active").length - 1;
    var start = thumb.find(".owl-item.active").first().index();
    var end = thumb.find(".owl-item.active").last().index();
    if (current > end) {
      thumb.data("owl.carousel").to(current, 100, true);
    }
    if (current < start) {
      thumb.data("owl.carousel").to(current - onscreen, 100, true);
    }
  }

  function syncPosition2(el) {
    if (syncedSecondary) {
      var number = el.item.index;
      slider.data("owl.carousel").to(number, 100, true);
    }
  }

  thumb.on("click", ".owl-item", function (e) {
    e.preventDefault();
    var number = $(this).index();
    slider.data("owl.carousel").to(number, 300, true);
  });

  $(".qtyminus").on("click", function () {
    var now = $(".qty").val();
    if ($.isNumeric(now)) {
      if (parseInt(now) - 1 > 0) {
        now--;
      }
      $(".qty").val(now);
    }
  });

  $(".qtyplus").on("click", function () {
    var now = $(".qty").val();
    if ($.isNumeric(now)) {
      $(".qty").val(parseInt(now) + 1);
    }
  });

  const productImages = document.querySelectorAll(".productzoomImg img");

  productImages.forEach((image) => {
    image.addEventListener("mouseover", () => {
      zoomIn(image);
    });

    image.addEventListener("mouseout", () => {
      zoomOut(image);
    });

    image.addEventListener("mousemove", (e) => {
      zoomMove(e, image);
    });
  });

  function zoomIn(image) {
    image.style.transform = "scale(1.5)";
  }

  function zoomOut(image) {
    image.style.transform = "scale(1)";
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

  $(document).on("click", "#newsletterSubscribe", function (t) {
    t.preventDefault();
    var newsletterEmail = $("#newsletterEmail").val();

    //, s = $(this).parents(".product-bottom").find(".quantity-input");
    /*,i=$(this).parents(".product").find("img").eq(0);if(i){i.clone().offset({top:i.offset().top,left:i.offset().left}).css({opacity:"0.5",position:"absolute",height:"150px",width:"150px","z-index":"1000"}).appendTo($("body")).animate({top:a.offset().top+10,left:a.offset().left+10,width:"50px",height:"50px"},400).animate({width:0,height:0},function(){$(this).detach()})}*/
    $.ajax({
      url: site.site_url + "cart/subscribe_newsletter",
      type: "GET",
      dataType: "json",
      data: {
        newsletterEmail: newsletterEmail,
      },
    }).done(function (t) {
      if (t.status == "Error!") {
        $.toast({
          heading: "Error",
          text: t.message,
          position: "top-right",
          showHideTransition: "slide",
          icon: "error",
        });
      } else {
        $.toast({
          heading: "Success",
          text: t.message,
          position: "top-right",
          showHideTransition: "slide",
          icon: "success",
        });
      }
    });
  });

  $(document).on("click", ".offcanvasClose", function () {
    $(".addcartcanvas").removeClass("show");
    $(".offcanvas-backdropaddP").removeClass("show");
    $(".offcanvas-backdropaddP").hide();
  });

  $(document).on("click", ".add-to-cart", function (t) {
    t.preventDefault();
    $(".offcanvas-backdropaddP").removeClass("show");
    var e = $(this).attr("data-id"),
      a = $(".shopping-cart:visible"),
      s = $(this).parents(".card").find("input");

    if (typeof s.val() === "undefined") {
      s = $(this).parents(".get-quantity").find("input");
    }

    //, s = $(this).parents(".product-bottom").find(".quantity-input");
    /*,i=$(this).parents(".product").find("img").eq(0);if(i){i.clone().offset({top:i.offset().top,left:i.offset().left}).css({opacity:"0.5",position:"absolute",height:"150px",width:"150px","z-index":"1000"}).appendTo($("body")).animate({top:a.offset().top+10,left:a.offset().left+10,width:"50px",height:"50px"},400).animate({width:0,height:0},function(){$(this).detach()})}*/
    $.ajax({
      url: site.site_url + "cart/add/" + e,
      type: "GET",
      dataType: "json",
      data: {
        qty: s.val(),
      },
    }).done(function (t) {
      //t.error ? sa_alert("Error!", t.message, "error", !0) : (a = t,
      //update_mini_cart(t))

      //(a = t, update_mini_cart(t));
      t.error
        ? $.notify(t.message, "warning") //alert('out of stock')//sa_alert("Error!", t.message, "error", !0)
        : ((a = t), update_mini_cart(t), update_popup_cart(t));
      if (t.error) {
        $(".addcartcanvas").removeClass("show");
        $(".offcanvas-backdropaddP").removeClass("show");
      } else {
        $(".addcartcanvas").addClass("show");
        $(".offcanvas-backdropaddP").addClass("show");
        $(".offcanvas-backdropaddP").show();
      }
      //$("#productPop").modal("show"));
      /*$('#product-canvas-toggle').attr({
            "data-bs-toggle": "offcanvas",
            "data-bs-target": "#offcanvasWithBothOptions"
          });*/

      /*$.toast({
            heading: "Success",
            text: "Product Added To The Cart.",
            position: "top-right",
            showHideTransition: "slide",
            icon: "success",
          })*/
    });
  });

  $("#same_as_billing").change(function (t) {
    $(this).is(":checked") &&
      ($("#shipping_line1").val($("#billing_line1").val()).change(),
      $("#shipping_line2").val($("#billing_line2").val()).change(),
      $("#shipping_city").val($("#billing_city").val()).change(),
      $("#shipping_state").val($("#billing_state").val()).change(),
      $("#shipping_postal_code").val($("#billing_postal_code").val()).change(),
      $("#shipping_country").val($("#billing_country").val()).change(),
      $("#shipping_phone").val($("#phone").val()).change(),
      $("#guest-checkout").data("formValidation").resetForm());
  });

  $(document).on("click", ".btn-minus", function (t) {
    var e = $(this).parent().find("input");
    if (e.val() > 1) {
      parseInt(e.val()) > 1 && e.val(parseInt(e.val()) - 1);
    }
  });

  $(document).on("click", ".btn-plus", function (t) {
    var e = $(this).parent().find("input");
    if (e.val() < 3) {
      e.val(parseInt(e.val()) + 1);
    }
  });

  $(document).on("click", ".btn-minus-update", function (t) {
    var e = $(this).parent().find("input");
    if (e.val() > 1) {
      parseInt(e.val()) > 1 && e.val(parseInt(e.val()) - 1);

      var a = $(this).closest(".cart-content-wrapper"),
        s = a.attr("id"),
        i = site.site_url + "cart/update",
        o = {};
      (o[site.csrf_token] = site.csrf_token_value),
        (o.rowid = s),
        (o.qty = e.val()),
        update_cart_item(i, o, e, $(this), t.target.type);
    }
  });

  $(document).on("click", ".btn-plus-update", function (t) {
    var e = $(this).parent().find("input");
    if (e.val() < 3) {
      e.val(parseInt(e.val()) + 1);

      var a = $(this).closest(".cart-content-wrapper"),
        s = a.attr("id"),
        i = site.site_url + "cart/update",
        o = {};
      (o[site.csrf_token] = site.csrf_token_value),
        (o.rowid = s),
        (o.qty = e.val()),
        update_cart_item(i, o, e, $(this), t.target.type);
    }
  });

  if (window.innerWidth < 1030) {

    $(".feature-cards").slick({
      infinite: false,
      speed: 300,
      slidesToShow: 4,
      slidesToScroll: 1,
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 4,
            slidesToScroll: 1,
            infinite: true,
            dots: true,
          },
        },
        {
          breakpoint: 991,
          settings: {
            slidesToShow: 4,
            slidesToScroll: 1,
            prevArrow: false,
            nextArrow: false,
            autoplay: true,
            infinite: true,
          },
        },
        {
          breakpoint: 480,
          settings: {
            slidesToShow: 4,
            slidesToScroll: 1,
            prevArrow: false,
            nextArrow: false,
            autoplay: true,
            infinite: true,
          },
        },

     
        // You can unslick at a given breakpoint now by adding:
        // settings: "unslick"
        // instead of a settings object
      ],
      prevArrow:
        "<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
      nextArrow:
        "<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
    });

  }

  $(".popularCat").slick({
    infinite: false,
    speed: 300,
    slidesToShow: 5,
    slidesToScroll: 1,
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
          infinite: true,
          dots: true,
        },
      },
      {
        breakpoint: 991,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ],
    prevArrow:
      "<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
    nextArrow:
      "<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
  });

  // special offer slider
  $(".speacialOfferMove").slick({
    slidesToShow: 6,
    slidesToScroll: 1,
    autoplay: true,
    autoplaySpeed: 2000,
    arrows: false,
    infinite: true,
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 4,
          slidesToScroll: 1,
          infinite: true,
          dots: false,
        },
      },
      {
        breakpoint: 991,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ],
  });

  $(".feature_products").slick({
    infinite: false,
    speed: 300,
    slidesToShow: 5,
    slidesToScroll: 1,
    margin: 20,
    prevArrow:
      "<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
    nextArrow:
      "<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
          infinite: true,
          dots: false,
        },
      },
      {
        breakpoint: 991,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ],
  });

  $(".customer_viewed_products").slick({
    infinite: false,
    speed: 300,
    slidesToShow: 4,
    slidesToScroll: 1,
    margin: 10,
    prevArrow:
      "<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
    nextArrow:
      "<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
          infinite: true,
          dots: false,
        },
      },
      {
        breakpoint: 991,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ],
  });

  $(".special_products").slick({
    infinite: false,
    speed: 300,
    slidesToShow: 3,
    slidesToScroll: 1,
    margin: 20,
    prevArrow:
      "<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
    nextArrow:
      "<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
          infinite: true,
          dots: false,
        },
      },
      {
        breakpoint: 991,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ],
  });

  $(".brands-logo").slick({
    infinite: false,
    speed: 300,
    slidesToShow: 5,
    slidesToScroll: 1,
    autoplay: true,
    infinite: true,
    prevArrow: false,
    nextArrow: false,

    prevArrow:
      "<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
    nextArrow:
      "<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
          infinite: true,
          dots: false,
          autoplay: true,
      
          prevArrow: false,
          nextArrow: false,
        },
      },
      {
        breakpoint: 991,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ],
  });

  $(".suprtwidget").slick({
    infinite: false,
    speed: 300,
    slidesToShow: 3,
    slidesToScroll: 1,

    prevArrow:
      "<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
    nextArrow:
      "<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 1,
          infinite: true,
          dots: true,
        },
      },
      {
        breakpoint: 991,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          prevArrow: false,
            nextArrow: false,
            autoplay: true,
            infinite: true,
        },
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
          dots: true,
          prevArrow: false,
          nextArrow: false,
          autoplay: true,
          infinite: true,
        },
      },
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ],
  });

  $(".nav-pills .nav-item button").click(function () {
    $(".popularCat").slick("refresh");
  });
});

// Addresses Section

$("#add-address").click(function (t) {
  t.preventDefault(), add_address();
});

$(".edit-address").click(function (t) {
  t.preventDefault();
  var e = $(this).attr("data-id");
  addresses &&
    $.each(addresses, function () {
      this.id == e && add_address(this);
    });
}),
  $(document).on("click", ".forgot-password", function (t) {
    t.preventDefault(), prompt(lang.reset_pw, lang.type_email);
  });

function initMap() {
  let map = new google.maps.Map(document.getElementById("load_map"), {
    center: { lat: 23.8859, lng: 45.0792 }, // Example coordinates (San Francisco)
    zoom: 18, // Adjust the zoom level
  });

  // Try to get the user's current location
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function (position) {
        const userLocation = {
          lat: position.coords.latitude,
          lng: position.coords.longitude,
        };

        // Center the map at the user's location

        document.getElementById("latitude").value = position.coords.latitude;
        document.getElementById("longitude").value = position.coords.longitude;
        map.setCenter(userLocation);

        // Add a marker at the user's location
        const marker = new google.maps.Marker({
          position: userLocation,
          map: map,
          title: "Your Location",
          draggable: true,
        });
        document.getElementById("manual-shipping-check-2").checked = false;
        document.getElementById("manual-shipping-address-2").style.display =
          "none";
        geocodeLatLng2(userLocation);

        marker.addListener("dragend", function () {
          document.getElementById("manual-shipping-check-2").checked = false;
          document.getElementById("manual-shipping-address-2").style.display =
            "none";

          const newPosition = marker.getPosition();
          document.getElementById("latitude").value = newPosition.lat();
          document.getElementById("longitude").value = newPosition.lng();
          geocodeLatLng2(newPosition);
        });

        $("#load_current_location-2").on("click", function (e) {
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
              function (position) {
                const userLocation = {
                  lat: position.coords.latitude,
                  lng: position.coords.longitude,
                };

                document.getElementById("latitude").value =
                  position.coords.latitude;
                document.getElementById("longitude").value =
                  position.coords.longitude;
                marker.setPosition(userLocation);
                map.setCenter(userLocation);
                document.getElementById(
                  "manual-shipping-check-2"
                ).checked = false;
                document.getElementById(
                  "manual-shipping-address-2"
                ).style.display = "none";
                geocodeLatLng2(userLocation);
              },
              function (error) {
                console.error("Error getting user location:", error);
              },
              {
                enableHighAccuracy: true,
              }
            );
          }
        });
      },
      function (error) {
        console.error("Error getting user location:", error);
      },
      {
        enableHighAccuracy: true,
      }
    );
  } else {
    console.error("Geolocation is not supported by this browser.");
  }
}

function geocodeLatLng2(latLng) {
  const geocoder = new google.maps.Geocoder();
  geocoder.geocode({ location: latLng }, function (results, status) {
    if (status === "OK") {
      if (results[0]) {
        const addressComponents = results[0].address_components;
        const formattedAddress = results[0].formatted_address;
        document.getElementById("autocomplete_search").value = formattedAddress;
        document.getElementById("address-line-1").value = formattedAddress;
        let city, country, state, street;

        for (const component of addressComponents) {
          const types = component.types;
          if (types.includes("locality")) {
            city = component.long_name;
            document.getElementById("address-city").value = city;
          } else if (types.includes("country")) {
            country = component.long_name;
            document.getElementById("address-country").value = country;
          } else if (types.includes("route")) {
            street = component.long_name;
            // document.getElementById("address-line-1").value = street;
          } else if (types.includes("administrative_area_level_1")) {
            state = component.long_name;
            document.getElementById("address-state").value = state;
          }
        }
        console.log(city, country);
      } else {
        console.log("No results found");
      }
    } else {
      console.log("Geocoder failed due to: " + status);
    }
  });
}
function initialize() {
  var input = document.getElementById("autocomplete_search");
  var autocomplete = new google.maps.places.Autocomplete(input);
  autocomplete.addListener("place_changed", function () {
    document.getElementById("address-city").value = "";
    document.getElementById("address-country").value = "";
    document.getElementById("address-line-1").value = "";
    document.getElementById("address-postal-code").value = "";
    document.getElementById("address-state").value = "";

    var place = autocomplete.getPlace();
    // Define variables to store city and country names
    var city, country, street, postalCode, stateName, latitude, logitude;
    // Loop through address components to find city and country
    place.address_components.forEach(function (component) {
      component.types.forEach(function (type) {
        if (type === "locality") {
          city = component.long_name;
          document.getElementById("address-city").value = city;
        }
        if (type === "country") {
          country = component.long_name;
          document.getElementById("address-country").value = country;
        }
        if (type === "route") {
          street = component.long_name;
          //document.getElementById("address-line-1").value = street;
          document.getElementById("address-line-1").value =
            document.getElementById("autocomplete_search").value;
        }
        if (type === "postal_code") {
          postalCode = component.long_name;
          document.getElementById("address-postal-code").value = postalCode;
        }
        if (type === "administrative_area_level_1") {
          stateName = component.long_name;
          document.getElementById("address-state").value = stateName;
        }
      });
    });

    // place variable will have all the information you are looking for.
    $("#latitude").val(place.geometry["location"].lat());
    $("#longitude").val(place.geometry["location"].lng());
  });
}

function add_address(t) {
  t = t || {};
  var e = "";
  if (istates) {
    var a = document.createElement("select");
    (a.id = "address-state"),
      (a.name = "state"),
      (a.className = "selectpickerstate mobile-device"),
      a.setAttribute("data-live-search", !0),
      a.setAttribute("title", "State");
    Object.keys(istates).map(function (t) {
      if (0 != t) {
        var e = document.createElement("option");
        (e.value = t), (e.text = istates[t]), a.appendChild(e);
      }
    }),
      (e = a.outerHTML);
  } else
    e =
      '<input name="state" value="' +
      (t.state ? t.state : "") +
      '" id="address-state" class="form-control" placeholder="' +
      lang.state +
      '">';
  swal({
    title: t.id ? lang.update_address : lang.add_address,
    html:
      '<span class="text-bold padding-bottom-md">' +
      lang.fill_form +
      '</span><hr class="swal2-spacer padding-bottom-xs" style="display: block;"><form action="' +
      site.shop_url +
      'address" id="address-form" class="padding-bottom-md"><input type="hidden" name="' +
      site.csrf_token +
      '" value="' +
      site.csrf_token_value +
      '"><input type="hidden" id="longitude" name="longitude" value="' +
      (t.longitude ? t.longitude : "") +
      '"><input type="hidden" id="latitude" name="latitude" value="' +
      (t.latitude ? t.latitude : "") +
      '"><div class="row"><div class="form-group col-sm-12"><button type="button" id="load_current_location-2">Current Location</button><div style="height: 350px; z-index: 99999;" id="load_map"></div><input id="google-map-selected-address-2" type="text" readonly  class="form-control" /><input id="autocomplete_search" type="hidden"  class="form-control" placeholder="Type for the address..." autocomplete="on" /></div></div><h4 class="or">OR</h4><h5 class="orcheckbox"><input type="checkbox" id="manual-shipping-check-2"/> Check the box to type the address manually</h5><div id="manual-shipping-address-2" style="display: none;"><div class="row"><div class="form-group col-sm-12"><input name="line1" id="address-line-1" value="' +
      (t.line1 ? t.line1 : "") +
      '" class="form-control" placeholder="Address"></div></div><div class="row"><div class="form-group col-sm-6">' +
      "" +
      '<select id="address-country-dropdown-2" class="form-control">' +
      '<option value="0">--SELECT--</option>' +
      "</select>" +
      '<input type="hidden" name="country" value="' +
      (t.country ? t.country : "") +
      '" id="address-country" class="form-control" placeholder="' +
      lang.country +
      '">' +
      "" +
      '</div><div class="form-group col-sm-6">' +
      "" +
      '<select id="address-city-dropdown-2" class="form-control">' +
      '<option value="0">--SELECT--</option>' +
      "</select>" +
      '<input type="hidden" name="city" value="' +
      (t.city ? t.city : "") +
      '" id="address-city" class="form-control" placeholder="' +
      lang.city +
      '">' +
      '</div><div class="form-group col-sm-6 d-none"><input name="postal_code" value="' +
      (t.postal_code ? t.postal_code : "") +
      '" id="address-postal-code" class="form-control" placeholder="' +
      lang.postal_code +
      '"></div><div class="form-group col-sm-6 d-none" id="istates">' +
      e +
      '</div><div class="form-group col-md-6 margin-bottom-no text-left ar-addr d-none"><input type="tel" name="phone" value="' +
      (t.phone ? t.phone : "") +
      '" id="address-phone" class="form-control" placeholder="' +
      lang.phone +
      '"></div></div></form></div>',
    showCancelButton: !0,
    allowOutsideClick: !1,
    cancelButtonText: lang.cancel,
    confirmButtonText: lang.submit,
    preConfirm: function () {
      return new Promise(function (t, e) {
        $("#address-line-1").val() || e("Address" + " " + lang.is_required),
          $("#address-city").val() || e(lang.city + " " + lang.is_required),
          // $("#address-state").val() || e(lang.state + " " + lang.is_required),
          $("#address-country").val() ||
            e(lang.country + " " + lang.is_required),
          $("#address-phone").val() || e(lang.phone + " " + lang.is_required),
          t();
      });
    },
    onOpen: function () {
      if (
        ($("#address-line-1")
          .val(t.line1 ? t.line1 : "")
          .focus(),
        /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent))
      )
        $(".selectpickerstate").selectpicker({
          modile: !0,
        }),
          $(".selectpickerstate").selectpicker("val", t.state ? t.state : "");
      else {
        for (
          var e = document.querySelectorAll(".mobile-device"), a = 0;
          a < e.length;
          a++
        )
          e[a].classList.remove("mobile-device");
        $(".selectpickerstate").selectpicker({
          size: 5,
        }),
          $(".selectpickerstate").selectpicker("val", t.state ? t.state : "");
      }

      if (!t.phone) {
        var input_address_phone = document.querySelector("#address-phone");
        window.intlTelInput(input_address_phone, {
          initialCountry: "SA",
        });
        $("#address-phone").val("+966");
      } else {
        var input_address_phone = document.querySelector("#address-phone");
        window.intlTelInput(input_address_phone, {
          //initialCountry: "SA"
        });

        var address_phone_val = $("#address-phone").val();

        var countryCode = $(".iti__selected-flag").attr("title");
        var countryCode = countryCode.replace(/[^0-9]/g, "");

        if (address_phone_val.startsWith("0")) {
          address_phone_val = address_phone_val.substr(1);

          $("#address-phone").val("+" + countryCode + " " + address_phone_val);
        }
      }

      // initialize();
      initMap();

      document.getElementById("manual-shipping-check-2").onchange = function (
        e
      ) {
        document.getElementById("google-map-selected-address-2").value = "";
        document.getElementById("address-country-dropdown-2").value = $(
          "#address-country-dropdown-2 option:first"
        ).val();
        document.getElementById("address-city-dropdown-2").value = $(
          "#address-city-dropdown-2 option:first"
        ).val();
        document.getElementById("address-line-1").value = "";
        document.getElementById("address-city").value = "";
        document.getElementById("address-country").value = "";
        document.getElementById("address-state").value = "";
        document.getElementById("latitude").value = "";
        document.getElementById("longitude").value = "";

        let manualMapBlock = document.getElementById(
          "manual-shipping-address-2"
        );
        if (e.target.checked === true) {
          manualMapBlock.style.display = "block";
        } else {
          manualMapBlock.style.display = "none";
        }
      };

      //load countries
      $.ajax({
        url: site.base_url + "cart/get_countries",
        method: "GET",
        success: function (jsonResponse) {
          var response = JSON.parse(jsonResponse);
          $("#address-country-dropdown-2").empty();
          if (response.length > 0) {
            $("#address-country-dropdown-2").append(
              '<option value="0">--SELECT--</option>'
            );
            response.forEach(function (city) {
              $("#address-country-dropdown-2").append(
                '<option value="' + city.id + '">' + city.name + "</option>"
              );
            });
          }
        },
        error: function () {
          console.error("Failed to fetch cities.");
        },
      });

      $("#address-country-dropdown-2").on("change", function () {
        $("#address-city").val("");

        let countryId = $(this).val();
        let selectedOption = $(this).find(":selected").text();
        if (selectedOption != "--SELECT--") {
          $("#address-country").val(selectedOption);
        } else {
          $("#address-country").val("");
        }

        //load cities
        $.ajax({
          url: site.base_url + "cart/get_cities_by_country_id/" + countryId, // Replace with the actual endpoint to fetch cities
          method: "GET",
          success: function (jsonResponse) {
            var response = JSON.parse(jsonResponse);
            $("#address-city-dropdown-2").empty();
            if (response.length > 0) {
              $("#address-city-dropdown-2").append(
                '<option value="0">--SELECT--</option>'
              );
              response.forEach(function (city) {
                $("#address-city-dropdown-2").append(
                  '<option value="' + city.id + '">' + city.name + "</option>"
                );
              });
            }
          },
          error: function () {
            console.error("Failed to fetch cities.");
          },
        });
      });

      $("#address-city-dropdown-2").on("change", function () {
        let selectedOption = $(this).find(":selected").text();
        if (selectedOption != "--SELECT--") {
          $("#address-city").val(selectedOption);
        } else {
          $("#address-city").val("");
        }
      });
    },
  })
    .then(function (e) {
      var a = $("#address-form");
      $.ajax({
        url: a.attr("action") + (t.id ? "/" + t.id : ""),
        type: "POST",
        data: a.serialize(),
        success: function (t) {
          if (t.redirect) return (window.location.href = t.redirect), !1;
          sa_alert(t.status, t.message, t.level);
        },
        error: function () {
          sa_alert(
            "Error!",
            "Ajax call failed, please try again or contact site owner.",
            "error",
            !0
          );
        },
      });
    })
    .catch(swal.noop);
}

/*$(document).ready(function() {
  var addressIdCounter = 1;
  $('#submitAddress').click(function() {
      var line1 = $('#line1Input').val();
      var line2 = $('#line2Input').val();
      var city = $('#cityInput').val();
      var state = $('#stateInput').val();
      var postalCode = $('#postalCodeInput').val();
      var country = $('#countryInput').val();
      var phone = $('#phoneInput').val();

      if (line1 && city) {
          var addressId = 'address' + addressIdCounter;
          addressIdCounter++;
          var addressCard = `
          
          <div class="col-md-6 mb-4" id="${addressId}">
              <div class="card">
                  <div class="card-body">
                      <h5 class="card-title">${line1}</h5>
                      <h6 class="card-subtitle mb-2 text-muted">${line2}</h6>
                      <p class="card-text">${city}, ${state}</p>
                      <p class="card-text">Postal Code: ${postalCode}</p>
                      <p class="card-text">Country: ${country}</p>
                      <p class="card-text">Phone: ${phone}</p>
                      <button type="button" class="btn btn-primary edit-address" data-bs-toggle="modal" data-bs-target="#editAddressModal" data-id="${addressId}">Edit</button>
                  </div>
              </div>
          </div>
          `;

          $('#addressList').append(addressCard);

          $('#addAddressModal').modal('hide');

          $('#line1Input').val('');
          $('#line2Input').val('');
          $('#cityInput').val('');
          $('#stateInput').val('');
          $('#postalCodeInput').val('');
          $('#countryInput').val('');
          $('#phoneInput').val('');
      }
  });

  // Event listener for the "Edit" button in the cards
  $('#addressList').on('click', '.edit-address', function() {
      // Get the unique ID of the address from the button's data-id attribute
      var addressId = $(this).data('id');

      // Find the address card associated with the ID
      var addressCard = $(`#${addressId}`);

      // Extract address details from the card
      var line1 = addressCard.find('.card-title').text();
      var line2 = addressCard.find('.card-subtitle').text();
      var cityState = addressCard.find('.card-text:eq(0)').text().split(',');
      var city = cityState[0].trim();
      var state = cityState[1].trim();
      var postalCode = addressCard.find('.card-text:eq(1)').text().replace('Postal Code: ', '');
      var country = addressCard.find('.card-text:eq(2)').text().replace('Country: ', '');
      var phone = addressCard.find('.card-text:eq(3)').text().replace('Phone: ', '');

      // Set the address details in the edit modal inputs
      $('#editLine1Input').val(line1);
      $('#editLine2Input').val(line2);
      $('#editCityInput').val(city);
      $('#editStateInput').val(state);
      $('#editPostalCodeInput').val(postalCode);
      $('#editCountryInput').val(country);
      $('#editPhoneInput').val(phone);

      // Store the ID in the modal's data-id attribute
      $('#updateAddress').data('id', addressId);
  });

  // Event listener for updating an address
  $('#updateAddress').click(function() {
      var updatedLine1 = $('#editLine1Input').val();
      var updatedLine2 = $('#editLine2Input').val();
      var updatedCity = $('#editCityInput').val();
      var updatedState = $('#editStateInput').val();
      var updatedPostalCode = $('#editPostalCodeInput').val();
      var updatedCountry = $('#editCountryInput').val();
      var updatedPhone = $('#editPhoneInput').val();

      if (updatedLine1 && updatedCity) {
          // Get the unique ID of the address from the modal's data attribute
          var addressId = $(this).data('id');

          // Find the card associated with the ID
          var addressCard = $(`#${addressId}`);

          // Update the address details in the card
          addressCard.find('.card-title').text(updatedLine1);
          addressCard.find('.card-subtitle').text(updatedLine2);
          addressCard.find('.card-text:eq(0)').text(updatedCity + (updatedState ? `, ${updatedState}` : ''));
          addressCard.find('.card-text:eq(1)').text(`Postal Code: ${updatedPostalCode}`);
          addressCard.find('.card-text:eq(2)').text(`Country: ${updatedCountry}`);
          addressCard.find('.card-text:eq(3)').text(`Phone: ${updatedPhone}`);

          // Close the edit modal
          $('#editAddressModal').modal('hide');

          // Clear the input fields
          $('#editLine1Input').val('');
          $('#editLine2Input').val('');
          $('#editCityInput').val('');
          $('#editStateInput').val('');
          $('#editPostalCodeInput').val('');
          $('#editCountryInput').val('');
          $('#editPhoneInput').val('');
      }
  });
});*/
/*if (window.innerWidth < 500) {
  //  // Remove the button from the source div
  cartsourceDiv.removeChild(cartToMove);

  // Append the button to the target div
  targetDiv.appendChild(cartToMove);
  // ===========

  catsourceDiv.removeChild(allCatToMove);

  // Append the button to the target div
  targetmenuDiv.prepend(allCatToMove);
  $(".popularCat").slick({
    infinite: false,
    speed: 300,
    slidesToShow: 4,
    slidesToScroll: 1,
    prevArrow:
      "<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
    nextArrow:
      "<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
    responsive: [
      {
        breakpoint: 1025,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 3,
          infinite: true,
          dots: true,
        },
      },
      {
        breakpoint: 991,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 2,
        },
      },
      {
        breakpoint: 480,
        settings: {
          slidesToShow: 1,
          slidesToScroll: 1,
        },
      },
      // You can unslick at a given breakpoint now by adding:
      // settings: "unslick"
      // instead of a settings object
    ],
  });

  $(".feature-cards").slick({
    infinite: false,
    speed: 300,
    slidesToShow: 2,
    slidesToScroll: 1,
    prevArrow:
      "<button type='button' class='slick-prev pull-left'><i class='bi bi-arrow-left-square-fill'></i></button>",
    nextArrow:
      "<button type='button' class='slick-next pull-right'><i class='bi bi-arrow-right-square-fill'></i></button>",
  });
}*/

// New login workflow functionality

if (window.innerWidth < 991) {

  //  // Remove the button from the source div
  cartsourceDiv.removeChild(cartToMove);


  catsourceDiv.removeChild(allCatToMove);

  // Append the button to the target div
  targetmenuDiv.prepend(allCatToMove);

  targetDiv.prepend(cartToMove);



}

function LoginFn(obj) {
  $("#loginBtn").addClass("active");
  $("#registerBtn").removeClass("active");
  $("#loginBlock").show();
  $("#registerBlock").hide();
}

function registerFnBtn(obj) {
  $("#loginBtn").removeClass("active");
  $("#registerBtn").addClass("active");
  $("#loginBlock").hide();
  $("#registerBlock").show();
}

$(document).ready(function () {
  function handleRegisterOTPClick() {
    var formData = $("#registrationForm").serialize();
    $.ajax({
      type: "POST",
      url: $("#registrationForm").attr("action"),
      data: formData,
      success: function (response) {
        var respObj = JSON.parse(response);
        if (respObj.status == "success" || respObj.code == 1) {
          $("#registerOTP").off("click", handleRegisterOTPClick);
          document.getElementById("registerOTP").style.color = "grey";
          document.getElementById("registerOTP").style.cursor = "none";
          $("#registerModal").modal("show");

          if ($("#email").length) {
            var identityVal = $("#email").val();
          } else {
            var identityVal = $("#email_phone").val();
          }

          document.getElementById("identifier").innerHTML = identityVal;
          document.getElementById("identifier_input").value = identityVal;

          const countdownDuration = 60; // Duration in seconds
          const countdownDisplay = document.getElementById("register-clock");

          let timer = countdownDuration,
            minutes,
            seconds;
          const intervalId = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            countdownDisplay.textContent =
              minutes + "." + (seconds < 10 ? "0" : "") + seconds;

            if (--timer < 0) {
              clearInterval(intervalId);
              document.getElementById("registerOTP").style.color = "#662d91";
              document.getElementById("registerOTP").style.cursor = "pointer";
              $("#registerOTP").click(handleRegisterOTPClick);
            }
          }, 1000);
        } else {
          $("#register-message").html(respObj.message);
        }
      },
      error: function (error) {
        console.error(error);
      },
    });
  }

  $("#registerBtnCall").click(function (e) {
    e.preventDefault();
    //$("#registerBtnCall").prop("disabled", true);
    $("#spinner").removeClass("d-none");
    var formData = $("#registrationForm").serialize();
    $.ajax({
      type: "POST",
      url: $("#registrationForm").attr("action"),
      data: formData,
      success: function (response) {
        try {
          var respObj = JSON.parse(response);

          if (respObj.status == "success" || respObj.code == 1) {
            if (respObj.link) {
              window.location.href = respObj.link;
            } else {
              $(".myaccountForm").removeClass("show");
              $("#registerOTP").off("click", handleRegisterOTPClick);
              document.getElementById("registerOTP").style.color = "grey";
              document.getElementById("registerOTP").style.cursor = "none";
              $("#registerModal").modal("show");

              if ($("#email").length) {
                var identityVal = $("#email").val();
              } else {
                var identityVal = $("#email_phone").val();
              }

              document.getElementById("identifier").innerHTML = identityVal;
              document.getElementById("identifier_input").value = identityVal;

              const countdownDuration = 60; // Duration in seconds
              const countdownDisplay =
                document.getElementById("register-clock");

              let timer = countdownDuration,
                minutes,
                seconds;
              const intervalId = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                countdownDisplay.textContent =
                  minutes + "." + (seconds < 10 ? "0" : "") + seconds;

                if (--timer < 0) {
                  clearInterval(intervalId);
                  document.getElementById("registerOTP").style.color =
                    "#662d91";
                  document.getElementById("registerOTP").style.cursor =
                    "pointer";
                  $("#registerOTP").click(handleRegisterOTPClick);
                }
              }, 1000);
            }
          } else {
           
            document.getElementById("registerBtnCall").remove();
            $("#register-message").html(message.message);
          }
        } catch (error) {
          // If there's an error in parsing JSON, catch the exception and handle it
          
          //ocument.getElementById("registerBtnCall").remove();
          //$("#register-message").html(errorMessage);
          //console.error("Error parsing JSON:", error);
          // You can display an error message to the user or perform other actions
        } finally {
          // Hide the spinner after the AJAX request is complete
          $("#spinner").addClass("d-none");
          //$("#registerBtnCall").prop("disabled", false);
        }
      },
      error: function (error) {
        //console.error(error);
      },
    });
  });

  $("#loginBtnCall").click(function (e) {
    e.preventDefault();
    var formData = $("#loginForm").serialize();
    $.ajax({
      type: "POST",
      url: $("#loginForm").attr("action"),
      data: formData,
      success: function (response) {
        var respObj = JSON.parse(response);
        if (respObj.status == "success" || respObj.code == 1) {
          $("#loginOTP").off("click", handleLoginOTPClick);
          $(".myaccountForm").removeClass("show");
          document.getElementById("loginOTP").style.color = "grey";
          document.getElementById("loginOTP").style.cursor = "none";
          $("#loginModal").modal("show");
          $("#loginModal").on("shown.bs.modal", function () {
            $("#login_otp_1").focus();
          });
          if ($("#identity").length) {
            var identityVal = $("#identity").val();
          } else {
            var identityVal = $("#identity_phone").val();
          }
          document.getElementById("identifierl").innerHTML = identityVal; //document.getElementById('identity_phone').value;
          document.getElementById("identifierl_input").value = identityVal; //document.getElementById('identity_phone').value;

          //document.getElementById('login_otp_1').value = 9;

          const countdownDuration = 60; // Duration in seconds
          const countdownDisplay = document.getElementById("login-clock");

          let timer = countdownDuration,
            minutes,
            seconds;

          const intervalId = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            countdownDisplay.textContent =
              minutes + "." + (seconds < 10 ? "0" : "") + seconds;

            if (--timer < 0) {
              clearInterval(intervalId);
              document.getElementById("loginOTP").style.color = "#662d91";
              document.getElementById("loginOTP").style.cursor = "pointer";
              $("#loginOTP").click(handleLoginOTPClick);
            }
          }, 1000);
          document.getElementById("login_otp_1").focus();
        } else {
          $("#register-message").html(respObj.message);
        }
      },
      error: function (error) {
        console.error(error);
      },
    });
  });

  function handleLoginOTPClick() {
    var formData = $("#loginForm").serialize();
    $.ajax({
      type: "POST",
      url: $("#loginForm").attr("action"),
      data: formData,
      success: function (response) {
        var respObj = JSON.parse(response);
        if (respObj.status == "success" || respObj.code == 1) {
          $("#loginOTP").off("click", handleLoginOTPClick);
          document.getElementById("loginOTP").style.color = "grey";
          document.getElementById("loginOTP").style.cursor = "none";
          $("#loginModal").modal("show");

          if ($("#identity").length) {
            var identityVal = $("#identity").val();
          } else {
            var identityVal = $("#identity_phone").val();
          }
          document.getElementById("identifierl").innerHTML = identityVal; //document.getElementById('identity_phone').value;
          document.getElementById("identifierl_input").value = identityVal; //document.getElementById('identity_phone').value;

          const countdownDuration = 60; // Duration in seconds
          const countdownDisplay = document.getElementById("login-clock");

          $("#login_otp_1").focus();

          let timer = countdownDuration,
            minutes,
            seconds;
          const intervalId = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            countdownDisplay.textContent =
              minutes + "." + (seconds < 10 ? "0" : "") + seconds;

            if (--timer < 0) {
              clearInterval(intervalId);
              document.getElementById("loginOTP").style.color = "#662d91";
              document.getElementById("loginOTP").style.cursor = "pointer";
              $("#loginOTP").click(handleLoginOTPClick);
            }
          }, 1000);
        } else {
          $("#register-message").html(respObj.message);
        }
      },
      error: function (error) {
        console.error(error);
      },
    });
  }

  function moveFocus(currentInput, nextInputId) {
    // Check if input value is empty
    if (!currentInput.value.trim()) {
      return;
    }

    // Iterate through each character in the input value
    for (let i = 0; i < currentInput.value.length; i++) {
      // Get the next character
      let char = currentInput.value.charAt(i);

      // Update the value of the current input
      currentInput.value = char;

      // Move focus to the next input
      var nextInput = document.getElementById(nextInputId);
      if (nextInput) {
        nextInput.focus();
      }
    }
  }

  function bindOtpKeyupEvents(prefix, totalFields) {
    //document.getElementById('login_otp_1').focus();
    //document.getElementById('register_otp_1').focus();
    for (let i = 1; i <= totalFields; i++) {
      let currentId = `${prefix}_${i}`;
      let nextId = i < totalFields ? `${prefix}_${i + 1}` : null;
      let prevId = i > 1 ? `${prefix}_${i - 1}` : null;

      $(`#${currentId}`).on("keyup", function (e) {
        //console.log('key', e.key) ;
        if (e.key === "Backspace" && prevId) {
          // If backspace is pressed, move focus to the previous input
          document.getElementById(prevId).focus();
        } else {
          moveFocus(this, nextId);
        }
      });
    }
  }

  // Bind keyup events for login OTP
  bindOtpKeyupEvents("login_otp", 6);

  bindOtpKeyupEvents("register_otp", 6);

  bindOtpKeyupEvents("checkout_login", 6);

  bindOtpKeyupEvents("profile_login", 6);

  bindOtpKeyupEvents("first_login", 6);

  $(document).on("click", ".btn-notify-add-to-list", function (t) {
    t.preventDefault();
    var dataId = $(this).data("id");
    var imageSrc = site.base_url + "assets/uploads/" + $(this).data("image");
    var dataTitle = $(this).data("title");
    var dataPrice = $(this).data("price");
    // Log the value to the console (optional)
    console.log("data-id:", dataId);
    $("#product_input").val(dataId);
    $("#notify_product_title").text(dataTitle);
    $("#notify_product_price").text(dataPrice);
    $("#notify_product_image").attr("src", imageSrc);

    $("#notifyModal").on("shown.bs.modal", function () {
      $("#notify_content").show();
      $("#notify_content").addClass('d-flex');
      $("#notify-response").text("");
    });
    $("#notifyModal").modal("show");
  });

  $("#notifyMeBtn").click(function (e) {
    e.preventDefault();
    // Clear previous error messages
    $("#notify-response").text("");
    // Check if email field is empty
    if ($("#notify_email").val() == "") {
      // Display an error message
      $("#notify-response").text("Please enter your email.");
    } else {
      // Proceed with the AJAX call if the email field is not empty
      var formData = $("#notifyMeForm").serialize();

      $.ajax({
        type: "POST",
        url: $("#notifyMeForm").attr("action"),
        data: formData,
        success: function (response) {
          if (response && typeof response === "object") {
            
            $("#notify-response").html("<p style='color: "+response.color+"'>" + response.message + "</p>");
            if(response.status == 'success' || response.status == 'info') {
              $("#notify_content").hide();
              $("#notify_content").removeClass('d-flex');
            }
          } else {
            console.error("Invalid response format:", response);
            $("#notify-response").html(
              "<p style='color: #FF5252'>Failed to process the server response.</p>"
            );
          }
        },
        error: function (error) {
          console.error(error);
        },
      });
    }
  });
});
