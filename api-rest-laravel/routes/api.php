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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('/register', 'UserController@register')->name('register');
Route::post('/login', 'UserController@login')->name('login');
Route::put('/user/update', 'UserController@update')->name('user.update');
Route::post('/user/upload', 'UserController@upload')->name('user.upload')->middleware('api.auth');
Route::get('/user/avatar/{filename}', 'UserController@getImage')->name('user.avatar');
Route::get('/user/profile/{id}', 'UserController@getProfile')->name('user.profile');
