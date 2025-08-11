<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Api\v1\Driver\ComplaintDriverController;
use App\Http\Controllers\Api\v1\Driver\OrderDriverController;
use App\Http\Controllers\Api\v1\Driver\RatingDriverController;
use App\Http\Controllers\Api\v1\Driver\ServiceDriverController;
use App\Http\Controllers\Api\v1\Driver\HomeDriverController;
use App\Http\Controllers\Api\v1\Driver\WalletDriverController;
use App\Http\Controllers\Api\v1\Driver\WithdrawalRequestDriverController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\User\AuthController;
use App\Http\Controllers\Api\v1\User\UserAddressController;
use App\Http\Controllers\Api\v1\User\UploadPhotoVoiceController;
use App\Http\Controllers\Api\v1\User\RatingController;
use App\Http\Controllers\Api\v1\User\DeliveryController;

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

//Route unAuth
Route::group(['prefix' => 'v1/user'], function () {

    //---------------- Auth --------------------//
    Route::get('/banners', [BannerController::class, 'index']);
    Route::get('/shop_categories', [ShopCategoryController::class, 'index']);

    Route::get('/cities', [AuthController::class, 'get_cities']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/deliveries', [DeliveryController::class, 'index']);
    Route::get('/pages/{type}', [PageController::class, 'index']);


    Route::get('/products/{id}', [ProductController::class, 'productDetails']); // Done
    Route::get('products/search', [ProductController::class, 'searchProducts']);
    Route::get('products/offers', [ProductController::class, 'offerProducts']);
    //Category product
    Route::get('/categories/{id}/products',  [CategoryController::class, 'getProducts']); // Done
    Route::get('/categories', [CategoryController::class, 'index']); // Done



    // Auth Route
    Route::group(['middleware' => ['auth:user-api']], function () {

        Route::get('/active', [AuthController::class, 'active']);


        Route::post('/update_profile', [AuthController::class, 'updateProfile']);
        Route::post('/delete_account', [AuthController::class, 'deleteAccount']);
        Route::get('/userProfile', [AuthController::class, 'userProfile']);

        //Notification
        Route::get('/notifications', [AuthController::class, 'notifications']);
         //--------------- Favourite ------------------------//
        Route::get('/favourites', [FavouriteController::class, 'index']); // Done
        Route::post('/favourites', [FavouriteController::class, 'store']); // Done

        //--------------- Coupon ------------------------//
        Route::post('/applyCoupon', [CouponController::class, 'applyCoupon']); // Done


        Route::get('/delivery', [DeliveryController::class, 'index']); // Done

        //-------------------- Address ------------------------//
        Route::get('/addresses', [UserAddressController::class, 'index']); // Done
        Route::post('/addresses', [UserAddressController::class, 'store']); // Done
        Route::post('/addresses/{address_id}', [UserAddressController::class, 'update']); // Done
        Route::delete('/addresses/{id}', [UserAddressController::class, 'destroy']); // Done
        //----------- Product Review ----------------------//
        Route::post('/product-reviews', [ProductReviewController::class, 'store']); // Done


        //----------------- Cart -------------------------------//
        Route::get('/carts', [CartController::class, 'index']); // Done
        Route::post('/carts', [CartController::class, 'store']); // Done
        Route::post('/carts/{id}', [CartController::class, 'update']); // Done
        Route::delete('/carts/{id}', [CartController::class, 'destroy']); // Done


        //---------------------- Order -----------------------//
        Route::get('/orders', [OrderController::class, 'index']); // Done
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders/{id}', [OrderController::class, 'update']);
        Route::post('/orders', [OrderController::class, 'store']); // Done
        Route::get('/orders/{id}/cancel', [OrderController::class, 'cancel_order']);
        Route::post('/orders/{id}/refund', [OrderController::class, 'refund']);
   

    });
});


 