<?php

namespace App\Support;

use Illuminate\Http\Request;

class AuthRedirect
{
    public const QUERY_KEY = 'redirect';

    public static function captureIntended(Request $request): void
    {
        $target = $request->query(self::QUERY_KEY);

        if (! is_string($target) || trim($target) === '') {
            return;
        }

        if (self::isSafeRedirect($target, $request)) {
            $request->session()->put('url.intended', $target);
        }
    }

    public static function loginRoute(?Request $request = null): string
    {
        $request ??= request();

        if ($request->user()) {
            return route('login');
        }

        $redirect = self::resolveRedirectTarget($request);

        return $redirect !== null
            ? route('login', [self::QUERY_KEY => $redirect])
            : route('login');
    }

    public static function registerRoute(?Request $request = null): string
    {
        $request ??= request();

        if ($request->user()) {
            return route('register');
        }

        $redirect = self::resolveRedirectTarget($request);

        return $redirect !== null
            ? route('register', [self::QUERY_KEY => $redirect])
            : route('register');
    }

    public static function googleRoute(?Request $request = null): string
    {
        $request ??= request();

        $redirect = self::resolveRedirectTarget($request);

        return $redirect !== null
            ? route('auth.google', [self::QUERY_KEY => $redirect])
            : route('auth.google');
    }

    private static function resolveRedirectTarget(Request $request): ?string
    {
        if ($request->routeIs(
            'login',
            'register',
            'password.forgot',
            'password.reset.form',
            'auth.google.phone',
        )) {
            $existing = $request->query(self::QUERY_KEY);

            return is_string($existing) && self::isSafeRedirect($existing, $request)
                ? $existing
                : $request->session()->get('url.intended');
        }

        return $request->fullUrl();
    }

    public static function isSafeRedirect(string $url, Request $request): bool
    {
        $url = trim($url);

        if ($url === '') {
            return false;
        }

        if (str_contains($url, '\\') || str_contains($url, "\0") || str_contains($url, '@')) {
            return false;
        }

        $lower = strtolower($url);
        if (str_contains($lower, 'javascript:') || str_contains($lower, 'data:')) {
            return false;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//') && ! str_contains($url, ':')) {
            return true;
        }

        $parsed = parse_url($url);
        if ($parsed === false) {
            return false;
        }

        if (! isset($parsed['host'])) {
            return str_starts_with($url, '/');
        }

        return strcasecmp($parsed['host'], $request->getHost()) === 0;
    }
}
