<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontEndController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShoppingAssistantController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::controller(FrontEndController::class)->group(function(){
//     Route::get('/','homePage')->name('home-page');
// });


Route::view('/example-page','example-page');
Route::view('/example-auth','example-auth');
Route::view('example-frontend','example-frontend');

Route::get(
    '/shop',
    [
        ShopController::class,
        'index',
    ]
)->name('shop.index');

//Customer Registration , login

Route::middleware('guest:web')
    ->prefix('account')
    ->name('customer.')
    ->controller(CustomerAuthController::class)
    ->group(function () {
        Route::get('/register', 'showRegister')->name('register');
        Route::post('/register', 'register')
            ->middleware('throttle:5,1')
            ->name('register.store');

        Route::get('/login', 'showLogin')->name('login');
        Route::post('/login', 'login')
            ->middleware('throttle:5,1')
            ->name('login.store');
    });

Route::middleware('auth:web')
    ->prefix('account')
    ->name('customer.')
    ->controller(CustomerAuthController::class)
    ->group(function () {
        Route::get('/profile', 'profile')->name('profile');
        Route::patch('/profile', 'updateProfile')->name('profile.update');
        Route::patch('/password', 'updatePassword')->name('password.update');
        Route::post('/logout', 'logout')->name('logout');

        //wishlist
    });

//frontend uses

Route::controller(FrontEndController::class)
    ->group(function () {
        Route::get(
            '/',
            'homePage'
        )->name('home-page');

        Route::get(
            '/products/{product:slug}',
            'productDetails'
        )->name('products.show');
    });

Route::post(
    '/shopping-assistant/chat',
    [
        ShoppingAssistantController::class,
        'chat',
    ]
)
    ->middleware('throttle:10,1')
    ->name('shopping-assistant.chat');


    //cart

Route::prefix('cart')
    ->name('cart.')
    ->controller(CartController::class)
    ->group(function () {
        Route::get(
            '/',
            'index'
        )->name('index');

        Route::post(
            '/{product}',
            'store'
        )->name('store');

        Route::patch(
            '/{product}',
            'update'
        )->name('update');

        Route::delete(
            '/{product}',
            'destroy'
        )->name('destroy');
    });

//checkout

Route::middleware('auth:web')
    ->group(function () {
        Route::get(
            '/checkout',
            [
                CheckoutController::class,
                'create',
            ]
        )->name('checkout.create');

        Route::post(
            '/checkout',
            [
                CheckoutController::class,
                'store',
            ]
        )->name('checkout.store');

        Route::get(
            '/account/orders',
            [
                CustomerOrderController::class,
                'index',
            ]
        )->name('customer.orders.index');

        Route::get(
            '/account/orders/{order}',
            [
                CustomerOrderController::class,
                'show',
            ]
        )->name('customer.orders.show');



        //wishlist and review

        Route::get(
            '/account/wishlist',
            [
                WishlistController::class,
                'index',
            ]
        )->name('wishlist.index');

        Route::post(
            '/wishlist/{product}',
            [
                WishlistController::class,
                'store',
            ]
        )->name('wishlist.store');

        Route::delete(
            '/wishlist/{product}',
            [
                WishlistController::class,
                'destroy',
            ]
        )->name('wishlist.destroy');

        Route::post(
            '/products/{product}/review',
            [
                ReviewController::class,
                'store',
            ]
        )->name('reviews.store');

        Route::delete(
            '/reviews/{review}',
            [
                ReviewController::class,
                'destroy',
            ]
        )->name('reviews.destroy');
});