<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CartController;
use App\Http\Controllers\Admin\ComplaintController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\NotificationGroupController;
use App\Http\Controllers\Admin\PushNotificationController;
use App\Http\Controllers\Admin\OrderNotificationController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Admin\WishlistController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\ProductReviewController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\AdvantageItemController;
use App\Http\Controllers\Admin\DeliverySlotController;
use App\Http\Controllers\Admin\OrderStepController;
use App\Http\Controllers\Admin\SiteStatController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('FreshNonVegAdmin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', function () {
            return auth('admin')->check()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('admin.login');
        });

        Route::middleware('admin.guest')->group(function (): void {
            Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.post');

            Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot.password');
            Route::post('/forgot-password', [AuthController::class, 'sendForgotPasswordOtp'])->middleware('throttle:otp')->name('forgot.password.post');

            Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('verify.otp');
            Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:otp')->name('verify.otp.post');

            Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('reset.password');
            Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:otp')->name('reset.password.post');
        });

        Route::middleware('admin.auth')->group(function (): void {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

            Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
            Route::match(['put', 'patch'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::match(['put', 'patch'], '/profile/password', [ProfileController::class, 'updatePassword'])->name('password.update');

            $comingSoon = static fn () => redirect()->route('admin.dashboard');

            Route::get('/banners', [BannerController::class, 'index'])->name('banners.index');
            Route::get('/banners/create', [BannerController::class, 'create'])->name('banners.create');
            Route::post('/banners', [BannerController::class, 'store'])->name('banners.store');
            Route::get('/banners/{banner}/edit', [BannerController::class, 'edit'])->name('banners.edit');
            Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
            Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('banners.destroy');
            Route::patch('/banners/{banner}/toggle-status', [BannerController::class, 'toggleStatus'])->name('banners.toggle-status');
            Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
            Route::patch('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
            Route::get('/products', [ProductController::class, 'index'])->name('products.index');
            Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/products', [ProductController::class, 'store'])->name('products.store');
            Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
            Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
            Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
            Route::get('/home-services', $comingSoon)->name('home-services.index');
            Route::get('/about/content', $comingSoon)->name('about.edit-content');
            Route::get('/services', $comingSoon)->name('services.index');
            Route::get('/advantage-items', [AdvantageItemController::class, 'index'])->name('advantage-items.index');
            Route::put('/advantage-items/section', [AdvantageItemController::class, 'updateSection'])->name('advantage-items.update-section');
            Route::get('/advantage-items/create', [AdvantageItemController::class, 'create'])->name('advantage-items.create');
            Route::post('/advantage-items', [AdvantageItemController::class, 'store'])->name('advantage-items.store');
            Route::get('/advantage-items/{advantageItem}/edit', [AdvantageItemController::class, 'edit'])->name('advantage-items.edit');
            Route::put('/advantage-items/{advantageItem}', [AdvantageItemController::class, 'update'])->name('advantage-items.update');
            Route::delete('/advantage-items/{advantageItem}', [AdvantageItemController::class, 'destroy'])->name('advantage-items.destroy');
            Route::patch('/advantage-items/{advantageItem}/toggle-status', [AdvantageItemController::class, 'toggleStatus'])->name('advantage-items.toggle-status');
            Route::get('/why-fresh-non-veg', fn () => redirect()->route('admin.advantage-items.index'))->name('why-fresh-non-veg.index');
            Route::get('/order-steps', [OrderStepController::class, 'index'])->name('order-steps.index');
            Route::put('/order-steps/section', [OrderStepController::class, 'updateSection'])->name('order-steps.update-section');
            Route::get('/order-steps/create', [OrderStepController::class, 'create'])->name('order-steps.create');
            Route::post('/order-steps', [OrderStepController::class, 'store'])->name('order-steps.store');
            Route::get('/order-steps/{orderStep}/edit', [OrderStepController::class, 'edit'])->name('order-steps.edit');
            Route::put('/order-steps/{orderStep}', [OrderStepController::class, 'update'])->name('order-steps.update');
            Route::delete('/order-steps/{orderStep}', [OrderStepController::class, 'destroy'])->name('order-steps.destroy');
            Route::patch('/order-steps/{orderStep}/toggle-status', [OrderStepController::class, 'toggleStatus'])->name('order-steps.toggle-status');
            Route::get('/site-stats', [SiteStatController::class, 'index'])->name('site-stats.index');
            Route::get('/site-stats/create', [SiteStatController::class, 'create'])->name('site-stats.create');
            Route::post('/site-stats', [SiteStatController::class, 'store'])->name('site-stats.store');
            Route::get('/site-stats/{siteStat}/edit', [SiteStatController::class, 'edit'])->name('site-stats.edit');
            Route::put('/site-stats/{siteStat}', [SiteStatController::class, 'update'])->name('site-stats.update');
            Route::delete('/site-stats/{siteStat}', [SiteStatController::class, 'destroy'])->name('site-stats.destroy');
            Route::patch('/site-stats/{siteStat}/toggle-status', [SiteStatController::class, 'toggleStatus'])->name('site-stats.toggle-status');
            Route::get('/stats', fn () => redirect()->route('admin.site-stats.index'))->name('stats.index');
            Route::get('/amenities', $comingSoon)->name('amenities.index');
            Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');
            Route::get('/faqs/create', [FaqController::class, 'create'])->name('faqs.create');
            Route::post('/faqs', [FaqController::class, 'store'])->name('faqs.store');
            Route::get('/faqs/{faq}/edit', [FaqController::class, 'edit'])->name('faqs.edit');
            Route::put('/faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
            Route::delete('/faqs/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');
            Route::patch('/faqs/{faq}/toggle-status', [FaqController::class, 'toggleStatus'])->name('faqs.toggle-status');
            Route::get('/instagram-feeds', $comingSoon)->name('instagram-feeds.index');
            Route::get('/gallery', $comingSoon)->name('gallery.index');
            Route::get('/plans', $comingSoon)->name('plans.index');
            Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
            Route::get('/coupons/create', [CouponController::class, 'create'])->name('coupons.create');
            Route::post('/coupons', [CouponController::class, 'store'])->name('coupons.store');
            Route::get('/coupons/{coupon}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
            Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('coupons.update');
            Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('coupons.destroy');
            Route::patch('/coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle-status');
            Route::get('/delivery-zones', [DeliveryZoneController::class, 'index'])->name('delivery-zones.index');
            Route::get('/delivery-zones/create', [DeliveryZoneController::class, 'create'])->name('delivery-zones.create');
            Route::post('/delivery-zones', [DeliveryZoneController::class, 'store'])->name('delivery-zones.store');
            Route::get('/delivery-zones/pincode/{pincode}', [DeliveryZoneController::class, 'pincodeLookup'])->name('delivery-zones.pincode');
            Route::get('/delivery-zones/{deliveryZone}/edit', [DeliveryZoneController::class, 'edit'])->name('delivery-zones.edit');
            Route::put('/delivery-zones/{deliveryZone}', [DeliveryZoneController::class, 'update'])->name('delivery-zones.update');
            Route::delete('/delivery-zones/{deliveryZone}', [DeliveryZoneController::class, 'destroy'])->name('delivery-zones.destroy');
            Route::patch('/delivery-zones/{deliveryZone}/toggle-status', [DeliveryZoneController::class, 'toggleStatus'])->name('delivery-zones.toggle-status');
            Route::get('/product-reviews', [ProductReviewController::class, 'index'])->name('product-reviews.index');
            Route::get('/product-reviews/{productReview}', [ProductReviewController::class, 'show'])->name('product-reviews.show');
            Route::get('/product-reviews/{productReview}/edit', [ProductReviewController::class, 'edit'])->name('product-reviews.edit');
            Route::put('/product-reviews/{productReview}', [ProductReviewController::class, 'update'])->name('product-reviews.update');
            Route::patch('/product-reviews/{productReview}/toggle-status', [ProductReviewController::class, 'toggleStatus'])->name('product-reviews.toggle-status');
            Route::delete('/product-reviews/{productReview}', [ProductReviewController::class, 'destroy'])->name('product-reviews.destroy');
            Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/export/excel', [OrderController::class, 'exportExcel'])->name('orders.export.excel');
            Route::get('/orders/export/csv', [OrderController::class, 'exportCsv'])->name('orders.export.csv');
            Route::get('/orders/notifications', [OrderNotificationController::class, 'index'])->name('orders.notifications');
            Route::post('/orders/notifications/seen', [OrderNotificationController::class, 'markSeen'])->name('orders.notifications.seen');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('/orders/{order}/bill.pdf', [OrderController::class, 'billPdf'])->name('orders.bill.pdf');
            Route::get('/orders/{order}/bill.xlsx', [OrderController::class, 'billExcel'])->name('orders.bill.excel');
            Route::get('/carts', [CartController::class, 'index'])->name('carts.index');
            Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
            Route::get('/wallet/credit', [WalletController::class, 'credit'])->name('wallet.credit');
            Route::post('/wallet/credit', [WalletController::class, 'store'])->name('wallet.store');
            Route::get('/wallet/users', [WalletController::class, 'userSearch'])->name('wallet.users');
            Route::get('/wishlists', [WishlistController::class, 'index'])->name('wishlists.index');
            Route::prefix('notifications')->name('notifications.')->group(function (): void {
                Route::get('/', [PushNotificationController::class, 'index'])->name('index');
                Route::post('/', [PushNotificationController::class, 'store'])->name('store');
                Route::post('/{pushNotification}/resend', [PushNotificationController::class, 'resend'])->name('resend');

                Route::get('/groups', [NotificationGroupController::class, 'index'])->name('groups.index');
                Route::post('/groups', [NotificationGroupController::class, 'store'])->name('groups.store');
                Route::put('/groups/{notificationGroup}', [NotificationGroupController::class, 'update'])->name('groups.update');
                Route::delete('/groups/{notificationGroup}', [NotificationGroupController::class, 'destroy'])->name('groups.destroy');
                Route::get('/groups/{notificationGroup}/users-page', [NotificationGroupController::class, 'usersPage'])->name('groups.users');
                Route::post('/groups/{notificationGroup}/members', [NotificationGroupController::class, 'addMembers'])->name('groups.members.add');
                Route::delete('/groups/{notificationGroup}/members/{user}', [NotificationGroupController::class, 'removeMember'])->name('groups.members.remove');
            });

            Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
            Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
            Route::put('/complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
            Route::get('/workspace-bookings', $comingSoon)->name('workspace-bookings.index');
            Route::get('/contact/submissions', $comingSoon)->name('contact.submissions');
            Route::get('/tickets', $comingSoon)->name('tickets.index');
            Route::get('/users/export/excel', [UserController::class, 'exportExcel'])->name('users.export.excel');
            Route::get('/users/export/csv', [UserController::class, 'exportCsv'])->name('users.export.csv');
            Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::get('/invoices', $comingSoon)->name('invoices.index');
            Route::get('/payments', $comingSoon)->name('payments.index');
            Route::get('/slots', [DeliverySlotController::class, 'index'])->name('slots.index');
            Route::get('/slots/create', [DeliverySlotController::class, 'create'])->name('slots.create');
            Route::post('/slots', [DeliverySlotController::class, 'store'])->name('slots.store');
            Route::get('/slots/{deliverySlot}/edit', [DeliverySlotController::class, 'edit'])->name('slots.edit');
            Route::put('/slots/{deliverySlot}', [DeliverySlotController::class, 'update'])->name('slots.update');
            Route::delete('/slots/{deliverySlot}', [DeliverySlotController::class, 'destroy'])->name('slots.destroy');
            Route::patch('/slots/{deliverySlot}/toggle-status', [DeliverySlotController::class, 'toggleStatus'])->name('slots.toggle-status');
            Route::get('/contact/content', $comingSoon)->name('contact.content');
            Route::get('/enquiry-content', $comingSoon)->name('enquiry-content.index');
            Route::get('/booking-content', $comingSoon)->name('booking-content.index');
            Route::get('/dynamic-forms', $comingSoon)->name('dynamic-forms.index');
            Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
            Route::get('/pages/create', [PageController::class, 'create'])->name('pages.create');
            Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
            Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
            Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
            Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.destroy');
            Route::patch('/pages/{page}/toggle-status', [PageController::class, 'toggleStatus'])->name('pages.toggle-status');
            Route::get('/page-contents', [PageContentController::class, 'index'])->name('page-contents.index');
            Route::get('/page-contents/create', [PageContentController::class, 'create'])->name('page-contents.create');
            Route::post('/page-contents', [PageContentController::class, 'store'])->name('page-contents.store');
            Route::get('/page-contents/{pageContent}/edit', [PageContentController::class, 'edit'])->name('page-contents.edit');
            Route::put('/page-contents/{pageContent}', [PageContentController::class, 'update'])->name('page-contents.update');
            Route::delete('/page-contents/{pageContent}', [PageContentController::class, 'destroy'])->name('page-contents.destroy');
            Route::patch('/page-contents/{pageContent}/toggle-status', [PageContentController::class, 'toggleStatus'])->name('page-contents.toggle-status');
            Route::get('/testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
            Route::get('/testimonials/create', [TestimonialController::class, 'create'])->name('testimonials.create');
            Route::post('/testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');
            Route::get('/testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('testimonials.edit');
            Route::put('/testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
            Route::delete('/testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');
            Route::patch('/testimonials/{testimonial}/toggle-status', [TestimonialController::class, 'toggleStatus'])->name('testimonials.toggle-status');
            Route::get('/header-content', $comingSoon)->name('header-content.index');
        });
    });
