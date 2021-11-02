<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ShopCategoriesController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CarTypeController;
use App\Http\Controllers\GroupProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\QueueNumberController;
use App\Models\Car_type;
use Yajra\Datatables\Datatables;
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
Route::post('login', [UserController::class, 'login']);
Route::get('send_email', [ShopController::class, 'send_email']);
Route::group(['middleware' => 'auth:api'], function(){
    Route::get('user/detail', [ShopController::class, 'register']);
    Route::post('logout', [ShopController::class, 'register']);

    //car type
    Route::post('car_type/add', [CarTypeController::class, 'store']);
    Route::post('car_type/update/{id}', [CarTypeController::class, 'update']);
    Route::delete('car_type/delete/{id}', [CarTypeController::class, 'destroy']);
    Route::get('car_type',[CarTypeController::class, 'index']);

    //group product
    Route::post('group_product/add', [GroupProductController::class, 'store']);
    Route::post('group_product/update/{id}', [GroupProductController::class, 'update']);
    Route::delete('group_product/delete/{id}', [GroupProductController::class, 'destroy']);
    Route::get('group_product',[GroupProductController::class, 'index']);

    //product
    Route::post('product/add', [ProductController::class, 'store']);
    Route::post('product/update/{id}', [ProductController::class, 'update']);
    Route::delete('product/delete/{id}', [ProductController::class, 'destroy']);
    Route::get('product',[ProductController::class, 'index']);

    //payment method
    Route::post('payment_method/add', [PaymentMethodController::class, 'store']);
    Route::post('payment_method/update/{id}', [PaymentMethodController::class, 'update']);
    Route::delete('payment_method/delete/{id}', [PaymentMethodController::class, 'destroy']);
    Route::get('payment_method',[PaymentMethodController::class, 'index']);

    //order
    Route::post('order/check', [OrderController::class, 'check_price']);
    Route::post('order/checkout', [OrderController::class, 'store']);
    Route::get('order',[OrderController::class, 'index']);
    Route::get('order/preload',[OrderController::class, 'order_preload']);

    //queue
    Route::get('queue',[QueueNumberController::class, 'index']);
    Route::post('queue/call', [QueueNumberController::class, 'call']);
});
