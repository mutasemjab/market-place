<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ShopCategoryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CouponController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\UserController;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Permission\Models\Permission;
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

define('PAGINATION_COUNT', 11);
Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {




    Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('logout', [LoginController::class, 'logout'])->name('admin.logout');




        /*         start  update login admin                 */
        Route::get('/admin/edit/{id}', [LoginController::class, 'editlogin'])->name('admin.login.edit');
        Route::post('/admin/update/{id}', [LoginController::class, 'updatelogin'])->name('admin.login.update');
        /*         end  update login admin                */

        /// Role and permission
        Route::resource('employee', 'App\Http\Controllers\Admin\EmployeeController', ['as' => 'admin']);
        Route::get('role', 'App\Http\Controllers\Admin\RoleController@index')->name('admin.role.index');
        Route::get('role/create', 'App\Http\Controllers\Admin\RoleController@create')->name('admin.role.create');
        Route::get('role/{id}/edit', 'App\Http\Controllers\Admin\RoleController@edit')->name('admin.role.edit');
        Route::patch('role/{id}', 'App\Http\Controllers\Admin\RoleController@update')->name('admin.role.update');
        Route::post('role', 'App\Http\Controllers\Admin\RoleController@store')->name('admin.role.store');
        Route::post('admin/role/delete', 'App\Http\Controllers\Admin\RoleController@delete')->name('admin.role.delete');

        Route::get('/permissions/{guard_name}', function ($guard_name) {
            return response()->json(Permission::where('guard_name', $guard_name)->get());
        });





        // Resource Route
        Route::resource('settings', SettingController::class);
        Route::resource('users', UserController::class);
        Route::resource('shop-categories', ShopCategoryController::class);
        Route::resource('shops', ShopController::class);
        Route::resource('cities', CityController::class);
        Route::resource('banners', BannerController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('products', ProductController::class);
        Route::delete('products/photos/{photo}', [ProductController::class, 'deletePhoto'])->name('products.photos.delete');

        Route::resource('offers', OfferController::class);
        Route::resource('coupons', CouponController::class);
        Route::resource('orders', OrderController::class);
        Route::resource('deliveries', DeliveryController::class);

        // other route 
        Route::get('/users/toggle-status/{id}', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::get('/users/adjust-balance/{id}', [UserController::class, 'adjustBalance'])->name('users.adjust-balance');
        Route::get('/users/generate-referal-code', [UserController::class, 'generateReferalCode'])->name('users.generate-referal-code');
        Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');
        Route::get('/products/get-prices/{id}', [ProductController::class, 'getPrices'])->name('products.getPrices');

        Route::get('/search-users', [UserController::class, 'search'])->name('search.users');
        Route::get('/user/{id}/addresses', [UserController::class, 'addresses'])->name('user.addresses');
        Route::get('delivery/{deliveryId}/fee', [UserController::class, 'getFee']);
        
        // Notification
        Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
        Route::post('/notifications/send', [NotificationController::class, 'send'])->name('notifications.send');

        Route::prefix('pages')->group(function () {
            Route::get('/', [PageController::class, 'index'])->name('pages.index');
            Route::get('/create', [PageController::class, 'create'])->name('pages.create');
            Route::post('/store', [PageController::class, 'store'])->name('pages.store');
            Route::get('/edit/{id}', [PageController::class, 'edit'])->name('pages.edit');
            Route::put('/update/{id}', [PageController::class, 'update'])->name('pages.update');
            Route::delete('/delete/{id}', [PageController::class, 'destroy'])->name('pages.destroy');
        });

    
    });
});



Route::group(['namespace' => 'Admin', 'prefix' => 'admin', 'middleware' => 'guest:admin'], function () {
    Route::get('login', [LoginController::class, 'show_login_view'])->name('admin.showlogin');
    Route::post('login', [LoginController::class, 'login'])->name('admin.login');
});
