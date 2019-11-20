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

Route::group([
    'prefix' => 'auth',
    'as' => 'auth.',
    'namespace' => 'Auth'
], function() {
    Route::post('login', 'LoginController@store')->name('login');
    Route::post('register', 'RegisterController@store')->name('register');
    Route::post('forgot', 'ForgotPasswordController')->name('forgot');
    Route::post('reset-password', 'ResetPasswordController')->name('reset-password');
});

Route::group([
    'prefix' => 'user',
    'as' => 'user.',
    'middleware' => 'auth:api'
], function() {
    Route::get('/', function (Request $request) {
        return $request->user();
    })->name('index');
});
