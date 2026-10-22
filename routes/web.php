<?php

use App\Http\Controllers\Website\AuthController;
use App\Http\Controllers\Website\CartController;
use App\Http\Controllers\Website\CheckoutController;
use App\Http\Controllers\Website\CatalogSearchController;
use App\Http\Controllers\Website\DashboardController;
use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\AddressSuggestController;
use App\Http\Controllers\Website\PincodeController;
use App\Http\Controllers\Website\ProductCartController;
use App\Http\Controllers\Website\ProductController;
use App\Http\Controllers\Website\LegalPageController;
use App\Http\Controllers\Website\OrderBillController;
use App\Http\Controllers\Website\ProfileController;
use App\Http\Controllers\Website\ShopController;
use App\Http\Controllers\Website\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/page/{slug}', [LegalPageController::class, 'show'])
    ->where('slug', 'terms-conditions|privacy-policy|refund-policy|contact|blog')
    ->name('pages.show');

Route::get('/legal/{slug}', function (string $slug) {
    return redirect()->route('pages.show', $slug, 301);
})->where('slug', 'terms-conditions|privacy-policy|refund-policy|contact|blog');

Route::get('/catalog/search', CatalogSearchController::class)->middleware('throttle:search')->name('catalog.search');

Route::post('/checkout/cashfree/webhook', [CheckoutController::class, 'cashfreeWebhook'])
    ->middleware('throttle:webhook')
    ->name('checkout.cashfree.webhook');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.post');
    Route::post('/login/otp/send', [AuthController::class, 'sendLoginOtp'])->middleware('throttle:otp')->name('login.otp.send');
    Route::post('/login/otp/verify', [AuthController::class, 'verifyLoginOtp'])->middleware('throttle:otp')->name('login.otp.verify');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
    Route::post('/forgot-password', [AuthController::class, 'sendForgotPasswordOtp'])->middleware('throttle:otp')->name('password.forgot.post');
    Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.form');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:otp')->name('password.reset.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:login')->name('register.post');
    Route::get('/auth/google', [AuthController::class, 'redirectGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
    Route::get('/auth/google/complete-phone', [AuthController::class, 'showGooglePhoneForm'])->name('auth.google.phone');
    Route::post('/auth/google/complete-phone', [AuthController::class, 'completeGooglePhone'])->name('auth.google.phone.post');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/product/{product}/cart', [ProductCartController::class, 'addToCart'])->name('products.cart.add');
    Route::post('/product/{product}/wishlist', [ProductCartController::class, 'addToWishlist'])->name('products.wishlist.add');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/checkout/pincode/{pincode}', [PincodeController::class, 'lookup'])->middleware('throttle:search')->name('checkout.pincode');
    Route::get('/checkout/address-suggest', AddressSuggestController::class)->middleware('throttle:search')->name('checkout.address-suggest');
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->middleware('throttle:checkout')->name('checkout.coupon');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
    Route::get('/checkout/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
    Route::get('/checkout/cashfree/return', [CheckoutController::class, 'cashfreeReturn'])->name('checkout.cashfree.return');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/orders/{order}/bill', [OrderBillController::class, 'show'])->name('orders.bill');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
    Route::get('/dashboard/orders/{order}', [DashboardController::class, 'showOrder'])->name('dashboard.orders.show');
    Route::post('/dashboard/orders/{order}/cancel', [DashboardController::class, 'cancelOrder'])->name('dashboard.orders.cancel');
    Route::get('/dashboard/wallet', [DashboardController::class, 'wallet'])->name('dashboard.wallet');
    Route::get('/dashboard/complaints', [DashboardController::class, 'complaints'])->name('dashboard.complaints');
    Route::post('/dashboard/complaints', [DashboardController::class, 'storeComplaint'])->name('dashboard.complaints.store');
    Route::get('/dashboard/ratings', [DashboardController::class, 'ratings'])->name('dashboard.ratings');
    Route::post('/dashboard/ratings/{product}', [DashboardController::class, 'storeRating'])->name('dashboard.ratings.store');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

require __DIR__ . '/admin.php';