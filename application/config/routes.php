<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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



