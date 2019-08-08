<?php

use Illuminate\Http\Request;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/**
 * Variables used in canAccess middleware
 */
$admin_customer_privileged = config('constants.roles.admin').','.config('constants.roles.customer');
$cashier_customer_privileged = config('constants.roles.cashier').','.config('constants.roles.customer');
$admin_privileged = config('constants.roles.admin');
$cashier_privileged = config('constants.roles.cashier');

/**
 * Shared and Customer Mobile Authorized Services
 */
Route::group(['middleware' =>['auth:api', 'canAccess:'.$admin_customer_privileged]], function () {

    /**Shared */
    Route::post('logout', 'Mobile\UsersController@logout');
    Route::get('rewards', 'Shared\SharedController@getVouchers');
    Route::get('reward/{id}', 'Shared\SharedController@getVoucher');
    Route::get('vouchers', 'Shared\SharedController@getVoucherInstances');
    Route::get('voucher/{id}', 'Shared\SharedController@getVoucherInstance');
    Route::get('user/{id}', 'Shared\SharedController@getUser');

    /**Mobile Customer*/
    Route::get('mobile/home', 'Mobile\HomeController@homeContent');
    Route::get('mobile/history/points', 'Mobile\HomeController@getTransactionPointsHistory');
    Route::post('mobile/reset', 'Mobile\UsersController@resetPassword');
    Route::post('mobile/report', 'Mobile\UsersController@addReport');
    Route::post('mobile/redeem', 'Mobile\HomeController@redeemVoucher');
    Route::post('mobile/profile', 'Mobile\HomeController@updateProfile');


});

/**
 * Merchant and Customer Mobile Authorized Services
 */
Route::group(['middleware' =>['auth:api', 'canAccess:'.$cashier_customer_privileged]], function () {

    Route::post('device', 'Shared\SharedController@bindDevice');

});

/**
 * Merchant Mobile Authorized Services
 */
Route::group(['middleware' => ['auth:api', 'canAccess:'.$cashier_privileged]], function () {

    /**Merchant*/
    Route::post('mobile/transaction', 'Mobile\MerchantController@addTransaction');
    Route::post('mobile/refund', 'Mobile\MerchantController@refundTransaction');
    Route::post('mobile/voucher/check', 'Mobile\MerchantController@checkVoucherInstance');
});

/**
 * Web Authorized Services
 */
// Route::group(['middleware' => ['auth:api', 'canAccess:'.$admin_privileged]], function () {
Route::group(['middleware' => ['auth:api']], function () {

    //Dashboard
    Route::get('admin/dashboard', 'Web\AdminController@dashboard');
    //Reports
    Route::get('web/reports', 'Web\AdminController@getReports');
    Route::get('web/report/{id}', 'Web\AdminController@getReport');
    Route::get('web/report/delete/{id}', 'Web\AdminController@deleteReport');
    Route::post('web/report/{id}', 'Web\AdminController@updateReport');
    //Rewards
    Route::get('web/reward/delete/{id}', 'Web\AdminController@deleteVoucher');
    Route::post('web/reward', 'Web\AdminController@addVoucher');
    Route::post('web/reward/{id}', 'Web\AdminController@updateVoucher');
    //Settings
    Route::get('web/settings', 'Web\AdminController@settings');
    Route::post('web/settings/fetch', 'Web\AdminController@fetchSettings');
    Route::post('web/configuration', 'Web\AdminController@addConfiguration');
    //Cashier
    Route::post('web/cashier/create', 'Web\AdminController@createCashier');
    Route::post('web/cashier/delete/{id}', 'Web\AdminController@deleteUser');
    //Users + Cashier
    Route::post('web/customer', 'Web\AdminController@updateCustomer');
    Route::post('web/cashier', 'Web\AdminController@updateCashier');
    //Users
    Route::get('web/users', 'Web\AdminController@getUsers');
    //Action Logs
    Route::get('web/actions', 'Web\AdminController@getActionLogs');
    //Notifications
    Route::get('web/notifications', 'Web\AdminController@getNotifications');
    Route::post('web/notify', 'Web\AdminController@notify');

});

/**
 * Mobile Unauthorized Services
 */
Route::post('mobile/register', 'Mobile\UsersController@register');
Route::post('mobile/login/social', 'Mobile\UsersController@socailLogin');
Route::post('mobile/signup/complete', 'Mobile\UsersController@completeSignup');

Route::post('mobile/verify', 'Mobile\UsersController@verifyAccount');
Route::post('mobile/resend', 'Mobile\UsersController@resendCode');

Route::post('mobile/validate', 'Mobile\UsersController@validateUser');
Route::post('mobile/phone/verify', 'Mobile\UsersController@verifyPhone');

/**
 * Web Unauthorized Services
 */


/**
 * Shared Unauthorized Services
 */
Route::post('mobile/login', 'Mobile\UsersController@login');
Route::post('store', 'Shared\ImagesController@store');


/**
 * Staging Routes
 */


