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

Route::post('mobile/login', 'Mobile\UsersController@login');
Route::post('mobile/register', 'Mobile\UsersController@register');
Route::post('mobile/verify', 'Mobile\UsersController@verify');
Route::post('mobile/resend', 'Mobile\UsersController@resendCode');//->middleware('auth:api');
