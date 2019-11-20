<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// this is here because the email the framework sends on a password reset event
// requires this named route.
// when we add in the catch all for the api, make sure it is defined above this.
Route::get('/password/reset', function () {

})->name('password.reset');
