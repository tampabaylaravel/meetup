<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    'namespace' => 'Auth',
], function () {
    Route::post('login', 'LoginController@store')->name('login');
    Route::post('register', 'RegisterController@store')->name('register');
    Route::post('forgot', 'ForgotPasswordController')->name('forgot');
    Route::post('reset-password', 'ResetPasswordController')->name('reset-password');
});

Route::group([
    'prefix' => 'users',
    'as' => 'user.',
    'middleware' => 'auth:api',
], function () {
    Route::get('/', function (Request $request) {
        return $request->user();
    })->name('index');
});

Route::group([
    'prefix' => 'meetings',
    'as' => 'api.meeting.',
    'namespace' => 'Api',
    'middleware' => ['auth:api'],
], function () {
    Route::get('/', 'MeetingController@index')->name('list');
    Route::post('/', 'MeetingController@store')->name('create');
    Route::get('/{meeting}', 'MeetingController@show')->name('show');
    Route::put('/{meeting}', 'MeetingController@update')->name('update');
    Route::delete('/{meeting}', 'MeetingController@destroy')->name('delete');

    Route::get('/{meeting}/reservations', 'ReservationController@index')->name('reservation.list');
    Route::get('/{meeting}/reservations/{user}', 'ReservationController@show')->name('reservation.show');
    // in the following methods:
    //   the user is gotten from the request to ensure only the logged in user can affect their reservation
    Route::post('/{meeting}/reservations', 'ReservationController@store')->name('reservation.create');
    Route::put('/{meeting}/reservations', 'ReservationController@update')->name('reservation.update');
    Route::delete('/{meeting}/reservations', 'ReservationController@destroy')->name('reservation.delete');
});
