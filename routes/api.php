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
], function() {
    Route::post('login', 'LoginController@store')->name('login');
    Route::post('register', 'RegisterController@store')->name('register');
    Route::post('forgot', 'ForgotPasswordController')->name('forgot');
    Route::post('reset-password', 'ResetPasswordController')->name('reset-password');
});

Route::group([
    'prefix' => 'user',
    'as' => 'user.',
    'middleware' => 'auth:api',
], function() {
    Route::get('/', function (Request $request) {
        return $request->user();
    })->name('index');
});

Route::group([
    'prefix' => 'meeting',
    'as' => 'api.meeting.',
    'namespace' => 'Api',
    'middleware' => ['auth:api']
], function () {
    Route::get('/', 'MeetingController@index')->name('list');
    Route::post('/', 'MeetingController@store')->name('create');
    Route::get('/{meeting}', 'MeetingController@show')->name('show');
    Route::put('/{meeting}', 'MeetingController@update')->name('update');
    Route::delete('/{meeting}', 'MeetingController@destroy')->name('delete');

    Route::get('/{meeting}/attend', 'AttendController@index')->name('attend.list');
    Route::get('/{meeting}/attend/{user}', 'AttendController@show')->name('attend.show');
    // in the following methods:
    //   the user is gotten from the request to ensure only the logged in user can affect their attendance
    Route::post('/{meeting}/attend', 'AttendController@store')->name('attend.create');
    Route::put('/{meeting}/attend', 'AttendController@update')->name('attend.update');
    Route::delete('/{meeting}/attend', 'AttendController@destroy')->name('attend.delete');
});
