

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
// slick slider =====================
$(document).ready(function(){
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