<?php

namespace App\Providers;

use App\Support\AuthRedirect;
use App\Support\SpeedCache;
use App\Support\StorageUrl;
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

        Config::set('filesystems.disks.public.url', StorageUrl::baseUrl());

        View::composer('Website.Layout.master', function ($view): void {
            $view->with([
                'guestLoginUrl' => AuthRedirect::loginRoute(),
                'guestRegisterUrl' => AuthRedirect::registerRoute(),
                'guestGoogleUrl' => AuthRedirect::googleRoute(),
            ]);
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
