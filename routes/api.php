<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ShopCategoriesController;
use App\Http\Controllers\ShopController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register', [ShopController::class, 'store']);
Route::get('send_email', [ShopController::class, 'send_email']);
Route::group(['middleware' => 'auth:api'], function(){
    Route::get('user/detail', [ShopController::class, 'register']);
    Route::post('logout', [ShopController::class, 'register']);
});
