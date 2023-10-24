

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

// slick slider =====================
$(document).ready(function(){
  $(document).on("click", ".add-to-cart", function(t) {
      t.preventDefault();
      var e = $(this).attr("data-id")
        , a = $(".shopping-cart:visible")
        , s = $(this).parents(".quantity").find("input");

      console.log(s);

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