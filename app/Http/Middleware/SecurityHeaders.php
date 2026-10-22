<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->method(), ['TRACE', 'TRACK'], true)) {
            abort(405);
        }

        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self), payment=(self)');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->set('Content-Security-Policy', $this->csp());

        return $response;
    }

    private function csp(): string
    {
        $script = implode(' ', [
            "'self'",
            "'unsafe-inline'",
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
            'https://code.jquery.com',
            'https://sdk.cashfree.com',
            'https://sandbox.cashfree.com',
            'https://payments.cashfree.com',
            'https://cdn.quilljs.com',
            'https://unpkg.com',
        ]);

        $style = implode(' ', [
            "'self'",
            "'unsafe-inline'",
            'https://fonts.googleapis.com',
            'https://cdn.jsdelivr.net',
            'https://cdnjs.cloudflare.com',
            'https://cdn.quilljs.com',
            'https://unpkg.com',
        ]);

        $connect = implode(' ', [
            "'self'",
            'https://photon.komoot.io',
            'https://api.cashfree.com',
            'https://api-sandbox.cashfree.com',
            'https://accounts.google.com',
        ]);

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self' https://sandbox.cashfree.com https://payments.cashfree.com https://accounts.google.com",
            "img-src 'self' data: blob: https:",
            "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "style-src {$style}",
            "script-src {$script}",
            "connect-src {$connect}",
            'frame-src https://sandbox.cashfree.com https://payments.cashfree.com https://accounts.google.com https://www.google.com',
        ]);
    }
}
