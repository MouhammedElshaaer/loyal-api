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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });


/**
 * Mobile Services
 */
Route::post('mobile/login', 'Mobile\UsersController@login');
Route::post('mobile/register', 'Mobile\UsersController@register');
Route::post('mobile/login/social', 'Mobile\UsersController@socailLogin');
Route::post('mobile/signup/complete', 'Mobile\UsersController@completeSignup');

Route::post('mobile/verify', 'Mobile\UsersController@verifyAccount');
Route::post('mobile/resend', 'Mobile\UsersController@resendCode');

Route::post('mobile/validate', 'Mobile\UsersController@validateUser');
Route::post('mobile/phone/verify', 'Mobile\UsersController@verifyPhone');


Route::group(['middleware' => ['web', 'auth:api']], function () {

    Route::post('mobile/logout', 'Mobile\UsersController@logout');
    Route::post('mobile/reset', 'Mobile\UsersController@resetPassword');
    Route::post('mobile/home', 'Mobile\HomeController@homeContent');

    //Reports
    Route::get('mobile/reports', 'Web\AdminController@getReports');
    Route::get('mobile/report/{id}', 'Web\AdminController@getReport');
    Route::get('mobile/report/delete/{id}', 'Web\AdminController@deleteReport');
    Route::post('mobile/report/{id}', 'Web\AdminController@updateReport');
    Route::post('mobile/report', 'Shared\SharedController@addReport');
    //Vouchers
    Route::get('mobile/rewards', 'Shared\SharedController@getVouchers');
    Route::get('mobile/reward/{id}', 'Shared\SharedController@getVoucher');
    Route::get('mobile/reward/delete/{id}', 'Web\AdminController@deleteVoucher');
    Route::post('mobile/reward', 'Web\AdminController@addVoucher');
    Route::post('mobile/reward/{id}', 'Web\AdminController@updateVoucher');
    //Settings
    Route::post('mobile/settings/fetch', 'Web\AdminController@fetchSettings');
    Route::post('/web/configuration', 'Web\AdminController@addConfiguration');
    
});

/**
 * Shared Services
 */
Route::post('store', 'Shared\ImagesController@store');


/**
 * Staging Routes
 */

Route::post('mobile/transaction', 'Mobile\HomeController@addTransaction');


