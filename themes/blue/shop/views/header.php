<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Avenzur</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;1,100;1,300;1,400;1,700&family=Manrope:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="<?= $assets; ?>css/ecommerce-main.css" rel="stylesheet">
</head>
  <body>
    <!-- top bar -->
    <section class="top-bar py-1 ">
        <div class="container container-max-width">
            <div class="row align-items-center">
              <div class="col-md-6">
                Avenzur will deliver fast with safe packing in all over the country
              </div>
              <div class="col-md-6 d-flex justify-content-end ">
                <div class="dropdown me-2">
                    <a class="btn  dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-globe-americas me-1"></i> EN <i class="bi bi-chevron-down ms-2"></i>
                    </a>
                  
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">EN</a></li>
                      <li><a class="dropdown-item" href="#">AR</a></li>
                      
                      
                    </ul>
                </div>
                <div class="dropdown me-2">
                    <a class="btn  dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      SAR <i class="bi bi-chevron-down ms-2"></i>
                    </a>
                  
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">SAR</a></li>
                      <li><a class="dropdown-item" href="#">USD</a></li>
                      <li><a class="dropdown-item" href="#">AED</a></li>
                      
                    </ul>
                </div>
                  <button type="button" class="btn text-white px-0"><i class="bi bi-person-fill"></i>&nbsp; My Account</button>
              </div>
              
            </div>
          </div>
    </section>
    <!-- top bar end -->

    <!-- logo search bar -->
    <section class="logo-searchBar">
      <div class="container container-max-width">
        <div class="row  align-items-center justify-content-between py-3">

          <div class="col-md-2  mb-2">
            <a class="navbar-brand" href="#"><img src="./images/logo.png" alt="logo"></a>
          </div>
          <div class="col-md-7">
            <form class="d-flex search-bar" role="search">
              
            <select class="form-select w-auto bg-transparent border-0 ps-4 categorySelect" aria-label="Default select">
              <option selected>Category</option>
              <option value="Medical">Medical</option>
              <option value="Skin Care">Skin Care</option>
              <option value="Suppliments">Suppliments</option>
            </select>
              <input class="form-control border-0 bg-transparent py-3" type="search" placeholder="What are you looking for?" aria-label="Search">
              <button class="btn searchsubmitBtn" type="submit"><i class="bi bi-search"></i></button>
            </form>
          </div>
          <div class="col-md-2 ps-md-0">
              <div class="d-flex align-items-center">
                <h3 class="me-2 my-0">
                  <svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M23.1 6.29999C26.4417 6.29999 29.6466 7.62748 32.0096 9.99044C34.3725 12.3534 35.7 15.5583 35.7 18.9" stroke="#2B9B48" stroke-width="2.52" stroke-miterlimit="10" stroke-linecap="square"/>
                    <path d="M29.4 18.9C29.4 17.2291 28.7363 15.6267 27.5548 14.4452C26.3733 13.2638 24.7709 12.6 23.1 12.6" stroke="#2B9B48" stroke-width="2.52" stroke-miterlimit="10" stroke-linecap="square"/>
                    <path d="M25.9276 24.4576L23.5315 27.4533C19.8205 25.2721 16.7279 22.1795 14.5467 18.4684L17.5423 16.0723C17.8933 15.7912 18.1413 15.4019 18.2476 14.965C18.354 14.5281 18.3128 14.0683 18.1303 13.6573L15.4003 7.50959C15.2046 7.06855 14.8585 6.71138 14.4239 6.50177C13.9893 6.29215 13.4943 6.24371 13.0273 6.36509L7.82984 7.71329C7.34477 7.83993 6.92302 8.14013 6.64454 8.55699C6.36605 8.97385 6.25018 9.47839 6.31889 9.97499C7.23096 16.4701 10.2328 22.4916 14.8706 27.1294C19.5083 31.7672 25.5299 34.769 32.025 35.6811C32.5214 35.7495 33.0257 35.6335 33.4423 35.3551C33.859 35.0766 34.159 34.655 34.2856 34.1701L35.6338 28.9726C35.7547 28.506 35.7062 28.0117 35.4968 27.5775C35.2874 27.1434 34.9308 26.7976 34.4904 26.6017L28.3426 23.8717C27.9319 23.6891 27.4722 23.6475 27.0354 23.7535C26.5985 23.8595 26.209 24.1071 25.9276 24.4576Z" stroke="#2B9B48" stroke-width="2.52" stroke-miterlimit="10" stroke-linecap="square"/>
                    </svg>
                    
                </h3>
                <div>
                  <p class="m-0  fw-bold">Sale & Service Support</p>
                  <h5 class="fw-bold">0112133551</h5>
                </div>
              </div>
          </div>

        </div>
      </div>
        
    </section>
    
    <!-- logo search bar end -->

    <!-- menu bar -->
    <section>
      <div class="container container-max-width main-menuTab">
        <div class="row align-items-center">
          <div class="col-md-2 col-sm-2 mob-catS">
            <button class="btn all-categoryBtn d-flex align-items-center justify-content-between" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
              <i class="bi bi-filter-left "></i> All Category
            </button>
            
            <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
              <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasExampleLabel">Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
              </div>
              <div class="offcanvas-body">           
            
                <ol class="list-group list-group-numbered">
                  <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                      <div class="fw-bold">Subheading</div>
                      Content for list item
                    </div>
                    <span class="badge bg-primary rounded-pill">14</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                      <div class="fw-bold">Subheading</div>
                      Content for list item
                    </div>
                    <span class="badge bg-primary rounded-pill">14</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                      <div class="fw-bold">Subheading</div>
                      Content for list item
                    </div>
                    <span class="badge bg-primary rounded-pill">14</span>
                  </li>
                </ol>
                
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-2 mob-menu">
            <nav class="navbar navbar-expand-lg  container-max-width">
              <div class="container-fluid">
                
                <div class="menu-av ">
                  
                  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                  </button>
                  <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                      <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Home</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link"  href="#">Products</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link"  href="#">About</a>
                      </li>
                    
                     
                     
                    </ul>
                    
                  </div>
                </div>
               
               
              </div>
            </nav>
          </div>
          <div class="col-md-4 col-sm-2 shop-icons">
            <div class="text-end">

              <span class="shuffleIcon me-2">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <g id="Frame">
                  <path id="Vector" d="M8.8 23.2H6.4C5.76348 23.2 5.15303 22.9471 4.70294 22.497C4.25286 22.047 4 21.4365 4 20.8L4 11.2C4 10.5635 4.25286 9.95302 4.70294 9.50293C5.15303 9.05284 5.76348 8.79999 6.4 8.79999L20 8.79999" stroke="#171A1F" stroke-width="1.92" stroke-miterlimit="10"/>
                  <path id="Vector_2" d="M23.2 8.79999H25.6C26.2365 8.79999 26.847 9.05284 27.2971 9.50293C27.7471 9.95302 28 10.5635 28 11.2L28 20.8C28 21.4365 27.7471 22.047 27.2971 22.497C26.847 22.9471 26.2365 23.2 25.6 23.2L12 23.2" stroke="#171A1F" stroke-width="1.92" stroke-miterlimit="10"/>
                  <path id="Vector_3" d="M16 4.79999L20 8.79999L16 12.8" stroke="#171A1F" stroke-width="1.92" stroke-miterlimit="10" stroke-linecap="square"/>
                  <path id="Vector_4" d="M16 19.2L12 23.2L16 27.2" stroke="#171A1F" stroke-width="1.92" stroke-miterlimit="10" stroke-linecap="square"/>
                  </g>
                  </svg>
                  
              </span>
            <span class="heartIcon me-2">
              <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M26.0832 7.51679C23.5272 4.96079 19.3824 4.96079 16.8264 7.51679C16.5112 7.83199 16.2376 8.17199 16 8.52879C15.7624 8.17199 15.4888 7.83199 15.1736 7.51759C12.6176 4.96159 8.47282 4.96159 5.91682 7.51759C3.36082 10.0736 3.36082 14.2184 5.91682 16.7744L16 26.8568L26.0832 16.7736C28.6392 14.2176 28.6392 10.0736 26.0832 7.51679Z" stroke="#171A1F" stroke-width="1.92" stroke-miterlimit="10" stroke-linecap="square"/>
              </svg>
            </span>
            
              <span class="cartIcon">
              <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.33331 24C7.86665 24 6.67998 25.2 6.67998 26.6667C6.67998 28.1333 7.86665 29.3333 9.33331 29.3333C10.8 29.3333 12 28.1333 12 26.6667C12 25.2 10.8 24 9.33331 24ZM22.6666 24C21.2 24 20.0133 25.2 20.0133 26.6667C20.0133 28.1333 21.2 29.3333 22.6666 29.3333C24.1333 29.3333 25.3333 28.1333 25.3333 26.6667C25.3333 25.2 24.1333 24 22.6666 24ZM20.7333 17.3333C21.7333 17.3333 22.6133 16.7867 23.0666 15.96L27.84 7.30666C27.9523 7.10456 28.01 6.87662 28.0072 6.6454C28.0045 6.41418 27.9414 6.18769 27.8242 5.98834C27.707 5.78899 27.5398 5.6237 27.3391 5.50881C27.1384 5.39393 26.9112 5.33344 26.68 5.33332L6.94665 5.33332L5.69331 2.66666L1.33331 2.66666L1.33331 5.33332H3.99998L8.79998 15.4533L6.99998 18.7067C6.02665 20.4933 7.30665 22.6667 9.33331 22.6667L25.3333 22.6667V20L9.33331 20L10.8 17.3333L20.7333 17.3333ZM8.21331 7.99999L24.4133 7.99999L20.7333 14.6667L11.3733 14.6667L8.21331 7.99999Z" fill="#171A1F"/>
              </svg>
              <span class="quantitynum">1</span>
            </span>
              
            </div>
          
              
              
          </div>
        </div>
      </div>
    </section>
    <!-- menu bar end -->