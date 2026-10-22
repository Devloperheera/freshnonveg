<?php

namespace App\Support;

class StorageUrl
{
    public static function baseUrl(): string
    {
        $configured = env('APP_STORAGE_URL');

        if (is_string($configured) && $configured !== '') {
            $appUrl = rtrim((string) env('APP_URL', 'http://localhost'), '/');
            $configured = str_replace(['${APP_URL}', '$APP_URL'], $appUrl, $configured);

            return rtrim($configured, '/');
        }

        return rtrim((string) env('APP_URL', 'http://localhost'), '/').'/storage';
    }

    public static function forPath(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path));

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return self::normalizeAbsoluteUrl($path);
        }

        if (str_starts_with($path, '/storage/')) {
            $path = ltrim(substr($path, strlen('/storage/')), '/');
        }

        return self::baseUrl().'/'.ltrim($path, '/');
    }

    private static function normalizeAbsoluteUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (is_string($path) && preg_match('#/storage/(.+)$#', $path, $matches)) {
            return self::baseUrl().'/'.ltrim($matches[1], '/');
        }

        return $url;
    }
}
