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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->prefix('meeting')->namespace('Api')->group(function () {
    Route::get('/', 'MeetingController@index');
    Route::post('/', 'MeetingController@store');
    Route::get('/{meeting}', 'MeetingController@show');
    Route::put('/{meeting}', 'MeetingController@update');
    Route::delete('/{meeting}', 'MeetingController@destroy');

    Route::get('/{meeting}/attend', 'AttendController@index');
    Route::get('/{meeting}/attend/{user}', 'AttendController@show');
    // in the following methods:
    //   the user is gotten from the request to ensure only the logged in user can affect their attendance
    Route::post('/{meeting}/attend', 'AttendController@store');
    Route::put('/{meeting}/attend', 'AttendController@update');
    Route::delete('/{meeting}/attend', 'AttendController@destroy');
});
