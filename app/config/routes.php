<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Framework routes
$route['default_controller']   = 'main';
$route['404_override']         = 'notify/error_404';
$route['translate_uri_dashes'] = true;

// Shop routes
$route['shop']                   = 'main';
$route['shop/search']            = 'shop/shop/search';
$route['shop/products']          = 'shop/shop/products';
$route['product/(:any)']         = 'shop/shop/product/$1';
$route['blog/(:any)']            = 'shop/shop/blog/$1';
$route['category/(:any)']        = 'shop/shop/products/$1';
$route['brand/(:any)']           = 'shop/shop/products/0/0/$1';

$route['category/(:any)/(:any)'] = 'shop/shop/products/$1/$2';
$route['pay/process_payment']    = 'pay/process_payment';
$route['pay/test_directpay_post']  = 'pay/test_directpay_post';

//$route['sitemapold\.xml']           = "Sitemap/index";
$route['sitemap.xml']        = "Sitemap/index";

// Page route
$route['page/contact-us'] = 'shop/shop/contact_us';
$route['page/(:any)'] = 'shop/shop/page/$1';

// Cart routes
$route['cart']               = 'shop/cart_ajax';
$route['cart/(:any)']        = 'shop/cart_ajax/$1';
$route['cart/(:any)/(:any)'] = 'shop/cart_ajax/$1/$2';
$route['apply_coupon']       = 'shop/cart_ajax/apply_coupon';
$route['remove_coupon']       = 'shop/cart_ajax/remove_coupon';

// Misc routes
$route['shop/(:any)']               = 'shop/shop/$1';
$route['shop/(:any)/(:any)']        = 'shop/shop/$1/$2';
$route['shop/(:any)/(:any)/(:any)'] = 'shop/shop/$1/$2/$3';

// Auth routes
$route['login']                  = 'main/login';
$route['login_otp']              = 'main/login_otp';
$route['set_shipping_phone']     = 'main/set_shipping_phone';
$route['verify_phone']           = 'main/verify_phone';
$route['verify_phone_otp']       = 'main/verify_phone_otp';
$route['activate_phone']         = 'main/activate_phone';
$route['mobile_verify_otp']      = 'main/mobile_verify_otp';
$route['logout']                 = 'main/logout';
$route['profile']                = 'main/profile';
$route['register']               = 'main/register';
$route['register_otp']           = 'main/register_otp';
$route['login/(:any)']           = 'main/login/$1';
$route['logout/(:any)']          = 'main/logout/$1';
$route['profile/(:any)']         = 'main/profile/$1';
$route['forgot_password']        = 'main/forgot_password';
$route['activate/(:any)/(:any)'] = 'main/activate/$1/$2';
$route['reset_password/(:any)']  = 'main/reset_password/$1';
$route['notify_me']              = 'main/notify_me';

// Admin area routes
$route['admin']                      = 'admin/welcome';
$route['admin/users']                = 'admin/auth/users';
$route['admin/users/create_user']    = 'admin/auth/create_user';
$route['admin/users/profile/(:num)'] = 'admin/auth/profile/$1';
$route['admin/avzlogin']                = 'admin/avzAdmin/index';
$route['admin/avzlogin/(:any)']         = 'admin/avzAdmin/index/$1';
$route['admin/login']                = 'admin/auth/login';
$route['admin/login/(:any)']         = 'admin/auth/login/$1';
$route['admin/logout']               = 'admin/auth/logout';
$route['admin/logout/(:any)']        = 'admin/auth/logout/$1';
// $route['admin/register'] = 'admin/auth/register';
$route['admin/forgot_password']  = 'admin/auth/forgot_password';
$route['admin/sales/(:num)']     = 'admin/sales/index/$1';
$route['admin/products/(:num)']  = 'admin/products/index/$1';
$route['admin/purchases/(:num)'] = 'admin/purchases/index/$1';
$route['admin/quotes/(:num)']    = 'admin/quotes/index/$1';
$route['admin/returns/(:num)']   = 'admin/returns/index/$1';
$route['admin/returns_supplier/(:num)']   = 'admin/returns_supplier/index/$1';
$route['admin/sales/qty_onhold_requests']     = 'admin/sales/qty_onhold_requests'; 
$route['admin/product_image/remove_image/(:num)'] = 'admin/product_image/remove_image/$1';