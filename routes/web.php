<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group whichf
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/filter-categories', [HomeController::class, 'filterByCity'])->name('api.filter-categories');
    Route::get('/category-products', [HomeController::class, 'getCategoryProducts'])->name('api.category-products');


Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath']], function () {
     Route::get('/', [HomeController::class, 'index'])->name('home');

    

});
