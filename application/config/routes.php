<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
*/

$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Authentication Routes
$route['login'] = 'login/index';
$route['login/send-otp'] = 'login/send_otp';
$route['login/verify-otp'] = 'login/verify_otp';
$route['logout'] = 'login/logout';

// Dashboard
$route['dashboard'] = 'dashboard/index';

// Users Management
$route['users'] = 'users/index';
$route['users/add'] = 'users/add';
$route['users/edit/(:num)'] = 'users/edit/$1';
$route['users/delete/(:num)'] = 'users/delete/$1';
$route['users/toggle-status'] = 'users/toggle_status';

// Categories Management
$route['categories'] = 'categories/index';
$route['categories/add'] = 'categories/add';
$route['categories/edit/(:num)'] = 'categories/edit/$1';
$route['categories/delete/(:num)'] = 'categories/delete/$1';

// Products Management
$route['products'] = 'products/index';
$route['products/add'] = 'products/add';
$route['products/edit/(:num)'] = 'products/edit/$1';
$route['products/delete/(:num)'] = 'products/delete/$1';

// Orders Management
$route['orders'] = 'orders/index';
$route['orders/view/(:num)'] = 'orders/view/$1';
$route['orders/update-status'] = 'orders/update_status';

// Settings
$route['settings/profile'] = 'profile/index';
$route['settings/delivery'] = 'settings/index';
$route['users/login-as-vendor/(:num)'] = 'users/login_as_vendor/$1';





// vendor api
$route['api/login_send_otp'] = 'api/login_send_otp';
$route['api/verify_login_otp'] = 'api/verify_login_otp';
$route['api/get_profile/(:num)'] = 'api/get_profile/$1';
$route['api/update_profile'] = 'api/update_profile';
$route['api/categories'] = 'api/categories';
$route['api/get_products'] = 'api/get_products';
$route['api/product_details/(:num)'] = 'api/product_details/$1';
$route['api/add_vendor_product'] = 'api/add_vendor_product';
$route['api/edit_vendor_product/(:num)'] = 'api/edit_vendor_product/$1';
$route['api/delete_vendor_product/(:num)'] = 'api/delete_vendor_product/$1';
$route['api/get_inventory'] = 'api/get_inventory';
$route['api/update_stock'] = 'api/update_stock';
$route['api/get_offers'] = 'api/get_offers';
$route['api/add_offer'] = 'api/add_offer';
$route['api/edit_offer/(:num)'] = 'api/edit_offer/$1';
$route['api/delete_offer/(:num)'] = 'api/delete_offer/$1';
$route['api/get_vendor_orders'] = 'api/get_vendor_orders';
$route['api/update_vendor_order_status'] = 'api/update_vendor_order_status';
$route['api/order_invoice/(:num)'] = 'api/order_invoice/$1';
$route['api/get_vendor_stats'] = 'api/get_vendor_stats';
$route['api/get_customer'] = 'api/get_customer';
$route['api/get_report'] = 'api/get_report';

// User API Routes
$route['api/user/register_send_otp'] = 'user/api/register_send_otp';
$route['api/user/verify_register_otp'] = 'user/api/verify_register_otp';
$route['api/user/login_send_otp'] = 'user/api/login_send_otp';
$route['api/user/verify_login_otp'] = 'user/api/verify_login_otp';
$route['api/user/google_login'] = 'user/api/google_login';
$route['api/user/guest_login'] = 'user/api/guest_login';
$route['api/user/home'] = 'user/api/home';
$route['api/user/get_category_list'] = 'user/api/get_category_list';
$route['api/user/get_category_detail'] = 'user/api/get_category_detail';
$route['api/user/get_category_detail/(:num)'] = 'user/api/get_category_detail/$1';
$route['api/user/get_products_by_category'] = 'user/api/get_products_by_category';
$route['api/user/get_products_by_category/(:num)'] = 'user/api/get_products_by_category/$1';
$route['api/user/get_product_list'] = 'user/api/get_product_list';
$route['api/user/get_product_detail'] = 'user/api/get_product_detail';
$route['api/user/get_product_detail/(:num)'] = 'user/api/get_product_detail/$1';
$route['api/user/get_profile'] = 'user/api/get_profile';
$route['api/user/update_profile'] = 'user/api/update_profile';
$route['api/user/search_products'] = 'user/api/search_products';
$route['api/user/search_categories'] = 'user/api/search_categories';

// Cart API Routes
$route['api/user/get_cart_row'] = 'user/api/get_cart_row';
$route['api/user/get_cart_row/(:num)'] = 'user/api/get_cart_row/$1';
$route['api/user/get_cart_summary'] = 'user/api/get_cart_summary';
$route['api/user/add_to_cart'] = 'user/api/add_to_cart';
$route['api/user/get_cart'] = 'user/api/get_cart';
$route['api/user/update_cart_quantity'] = 'user/api/update_cart_quantity';
$route['api/user/remove_from_cart'] = 'user/api/remove_from_cart';
$route['api/user/clear_cart'] = 'user/api/clear_cart';

// Wishlist API Routes
$route['api/user/add_to_wishlist'] = 'user/api/add_to_wishlist';
$route['api/user/clear_wishlist'] = 'user/api/clear_wishlist';
$route['api/user/get_wishlist_summary'] = 'user/api/get_wishlist_summary';
$route['api/user/get_wishlist'] = 'user/api/get_wishlist';
$route['api/user/update_wishlist_quantity'] = 'user/api/update_wishlist_quantity';
$route['api/user/remove_from_wishlist'] = 'user/api/remove_from_wishlist';
$route['api/user/add_all_to_cart'] = 'user/api/add_all_to_cart';

// Address API Routes
$route['api/user/get_addresses'] = 'user/api/get_addresses';
$route['api/user/save_address'] = 'user/api/save_address';
$route['api/user/update_address'] = 'user/api/update_address';
$route['api/user/delete_address'] = 'user/api/delete_address';

// Order API Routes
$route['api/user/place_order'] = 'user/api/place_order';
$route['api/user/calculate_delivery_charge'] = 'user/api/calculate_delivery_charge';
$route['api/user/verify_order_payment'] = 'user/api/verify_order_payment';
$route['api/user/get_orders'] = 'user/api/get_orders';
$route['api/user/get_order_details'] = 'user/api/get_order_details';
$route['api/user/get_order_details/(:num)'] = 'user/api/get_order_details/$1';
$route['api/user/cancel_order'] = 'user/api/cancel_order';

// Vendor Orders API Routes
$route['api/user/get_vendor_orders'] = 'user/api/get_vendor_orders';
$route['api/user/update_vendor_order_status'] = 'user/api/update_vendor_order_status';
$route['api/user/order_invoice/(:num)'] = 'user/api/order_invoice/$1';
$route['api/user/download_invoice/(:num)'] = 'user/api/order_invoice/$1';

$route['api/user/test_token'] = 'user/api/test_token';
$route['api/user/logout'] = 'user/api/logout';
