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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['cors', 'json.response']], function () {
    Route::prefix('/user')->group(function(){
        Route::post('register','App\Http\Controllers\api\v1\accounts\AccountsController@register')->name('user.register');
        Route::post('login','App\Http\Controllers\api\v1\accounts\AccountsController@login')->name('user.login');
        Route::post('password-reset','App\Http\Controllers\api\v1\accounts\AccountsController@resetPassword');
        Route::put('password-reset','App\Http\Controllers\api\v1\accounts\AccountsController@changePassword');

        Route::middleware(['auth:user','scope:user'])->group(function () {
            
        });
    });
});