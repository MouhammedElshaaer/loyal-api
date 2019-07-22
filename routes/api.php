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
 * Shared and Mobile Authorized Services
 */
Route::group(['middleware' => ['auth:api']], function () {

    /**Shared */
    Route::post('logout', 'Mobile\UsersController@logout');
    Route::get('rewards', 'Shared\SharedController@getVouchers');
    Route::get('reward/{id}', 'Shared\SharedController@getVoucher');
    Route::get('vouchers', 'Shared\SharedController@getVoucherInstances');
    Route::get('voucher/{id}', 'Shared\SharedController@getVoucherInstance');

    /**Mobile Customer*/
    Route::get('mobile/home', 'Mobile\HomeController@homeContent');
    Route::post('mobile/reset', 'Mobile\UsersController@resetPassword');
    Route::post('mobile/report', 'Mobile\UsersController@addReport');
    Route::post('mobile/redeem', 'Mobile\HomeController@redeemVoucher');

    /**Mobile Merchant*/

    
});

/**
 * Web Authorized Services
 * TODO: add admin middleware
 */
Route::group(['middleware' => ['auth:api']], function () {
     
    //Reports
    Route::get('web/reports', 'Web\AdminController@getReports');
    Route::get('web/report/{id}', 'Web\AdminController@getReport');
    Route::get('web/report/delete/{id}', 'Web\AdminController@deleteReport');
    Route::post('web/report/{id}', 'Web\AdminController@updateReport');
    //Vouchers
    Route::get('web/reward/delete/{id}', 'Web\AdminController@deleteVoucher');
    Route::post('web/reward', 'Web\AdminController@addVoucher');
    Route::post('web/reward/{id}', 'Web\AdminController@updateVoucher');
    //Settings
    Route::post('web/settings/fetch', 'Web\AdminController@fetchSettings');
    Route::post('web/configuration', 'Web\AdminController@addConfiguration');

});

/**
 * Mobile Unauthorized Services
 */
Route::post('mobile/login', 'Mobile\UsersController@login');
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
Route::post('store', 'Shared\ImagesController@store');


/**
 * Staging Routes
 */

Route::post('mobile/transaction', 'Mobile\MerchantController@addTransaction');
Route::post('mobile/refund', 'Mobile\MerchantController@refundTransaction');
Route::post('mobile/voucher/check', 'Mobile\MerchantController@checkVoucherInstance');


