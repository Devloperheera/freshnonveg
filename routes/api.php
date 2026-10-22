<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\HomeContentController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserDashboard\ComplaintController as UserDashboardComplaintController;
use App\Http\Controllers\Api\UserDashboard\DashboardController as UserDashboardController;
use App\Http\Controllers\Api\UserDashboard\OrderController as UserDashboardOrderController;
use App\Http\Controllers\Api\UserDashboard\ProfileController as UserDashboardProfileController;
use App\Http\Controllers\Api\UserDashboard\RatingController as UserDashboardRatingController;
use App\Http\Controllers\Api\UserDashboard\WalletController as UserDashboardWalletController;
use App\Http\Controllers\Api\Cart\CartController as ApiCartController;
use App\Http\Controllers\Api\Cart\WishlistController as ApiWishlistController;
use App\Http\Controllers\Api\Checkout\CheckoutController as ApiCheckoutController;
use App\Http\Controllers\Api\Checkout\CouponController as ApiCouponController;
use App\Http\Controllers\Api\Checkout\InvoiceController as ApiInvoiceController;
use App\Http\Controllers\Api\Checkout\LocationController as ApiLocationController;
use App\Http\Controllers\Api\Checkout\PaymentController as ApiPaymentController;
use App\Support\SitePage;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/pages', [PageController::class, 'index']);
    Route::get('/pages/{slug}', [PageController::class, 'show'])
        ->where('slug', SitePage::slugPattern());

    Route::get('/home-content', [HomeContentController::class, 'index']);

    Route::get('/banners', [BannerController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
        Route::post('/otp/send', [AuthController::class, 'sendOtp'])->middleware('throttle:otp');
        Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:otp');
        Route::post('/forgot-password/send', [AuthController::class, 'sendForgotOtp'])->middleware('throttle:otp');
        Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:otp');
        Route::post('/google', [AuthController::class, 'google'])->middleware('throttle:login');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::get('/profile', [AuthController::class, 'showProfile']);
            Route::match(['put', 'patch'], '/profile', [AuthController::class, 'updateProfile']);
            Route::match(['put', 'patch'], '/profile/password', [AuthController::class, 'updatePassword']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/complete-profile', [AuthController::class, 'completeProfile']);
            Route::post('/device', [AuthController::class, 'updateDevice']);
        });
    });

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/cart', [ApiCartController::class, 'index']);
        Route::post('/cart/products/{product}', [ApiCartController::class, 'store']);
        Route::patch('/cart/{cartItem}', [ApiCartController::class, 'update']);
        Route::delete('/cart/{cartItem}', [ApiCartController::class, 'destroy']);
        Route::post('/cart/clear', [ApiCartController::class, 'clear']);

        Route::get('/wishlist', [ApiWishlistController::class, 'index']);
        Route::post('/wishlist/products/{product}', [ApiWishlistController::class, 'store']);
        Route::delete('/wishlist/products/{product}', [ApiWishlistController::class, 'destroyProduct']);
        Route::delete('/wishlist/{wishlistItem}', [ApiWishlistController::class, 'destroy']);

        Route::get('/checkout', [ApiCheckoutController::class, 'show']);
        Route::post('/checkout/quote', [ApiCheckoutController::class, 'quote']);
        Route::post('/checkout/coupon', [ApiCouponController::class, 'apply'])->middleware('throttle:checkout');
        Route::get('/checkout/pincode/{pincode}', [ApiLocationController::class, 'pincode'])->middleware('throttle:search');
        Route::get('/checkout/address-suggest', [ApiLocationController::class, 'addressSuggest'])->middleware('throttle:search');
        Route::post('/checkout', [ApiCheckoutController::class, 'store'])->middleware('throttle:checkout');
        Route::post('/checkout/verify', [ApiPaymentController::class, 'verify'])->middleware('throttle:checkout');
        Route::get('/checkout/orders/{order}/status', [ApiPaymentController::class, 'status']);
        Route::get('/checkout/orders/{order}/success', [ApiPaymentController::class, 'success']);
        Route::get('/checkout/orders/{order}/invoice', [ApiInvoiceController::class, 'show']);
        Route::get('/orders/{order}/invoice', [ApiInvoiceController::class, 'show']);
    });

    Route::middleware('auth:sanctum')->prefix('dashboard')->group(function (): void {
        Route::get('/', [UserDashboardController::class, 'index']);

        Route::get('/profile', [UserDashboardProfileController::class, 'show']);
        Route::match(['put', 'patch'], '/profile', [UserDashboardProfileController::class, 'update']);
        Route::match(['put', 'patch'], '/profile/password', [UserDashboardProfileController::class, 'updatePassword']);

        Route::get('/orders', [UserDashboardOrderController::class, 'index']);
        Route::get('/orders/{order}', [UserDashboardOrderController::class, 'show']);
        Route::post('/orders/{order}/cancel', [UserDashboardOrderController::class, 'cancel']);
        Route::get('/orders/{order}/bill', [UserDashboardOrderController::class, 'bill']);

        Route::get('/wallet', [UserDashboardWalletController::class, 'index']);

        Route::get('/complaints', [UserDashboardComplaintController::class, 'index']);
        Route::post('/complaints', [UserDashboardComplaintController::class, 'store']);
        Route::get('/complaints/{complaint}', [UserDashboardComplaintController::class, 'show']);

        Route::get('/ratings', [UserDashboardRatingController::class, 'index']);
        Route::post('/ratings/{product}', [UserDashboardRatingController::class, 'store']);
    });
});
