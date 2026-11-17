<?php

namespace App\Providers;

use App\Models\Product;
use App\Services\CartService;
use App\Support\AuthRedirect;
use App\Support\SpeedCache;
use App\Support\StorageUrl;
use App\Support\WhatsAppOrderMessage;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        if (! $this->app->runningInConsole()) {
            $root = rtrim(request()->root(), '/');
            if ($root !== '') {
                URL::forceRootUrl($root);
            }
        }

        $this->configureRateLimiting();

        Paginator::useBootstrapFive();

        Config::set('filesystems.disks.public.url', StorageUrl::resolveFromEnv());

        View::composer('Website.Layout.master', function ($view): void {
            $view->with([
                'guestLoginUrl' => AuthRedirect::loginRoute(),
                'guestRegisterUrl' => AuthRedirect::registerRoute(),
                'guestGoogleUrl' => AuthRedirect::googleRoute(),
            ]);
        });

        View::composer('Website.partials.whatsapp-float', function ($view): void {
            if ($this->app->runningInConsole()) {
                return;
            }

            $user = auth()->user();
            $cartItems = $user ? app(CartService::class)->items($user) : collect();

            $product = null;
            if (request()->routeIs('products.show')) {
                $routeProduct = request()->route('product');
                if ($routeProduct instanceof Product) {
                    $product = $routeProduct->loadMissing('weights');
                }
            }

            $view->with('whatsAppOrderUrl', WhatsAppOrderMessage::waMeUrl($user, $product, $cartItems));
        });

        View::composer('Admin.Layout.sidebar', function ($view): void {
            $view->with('sidebarCounts', SpeedCache::sidebarCounts());
        });

        if (! $this->app->runningInConsole()) {
            SpeedCache::registerInvalidation();
        }
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $id = strtolower((string) $request->input('email', $request->input('login', '')));

            return Limit::perMinute(5)->by($id.'|'.$request->ip());
        });

        RateLimiter::for('otp', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(8)->by($request->user()?->id ?: $request->ip()));

        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));

        RateLimiter::for('webhook', fn (Request $request) => Limit::perMinute(40)->by($request->ip()));
    }
}
