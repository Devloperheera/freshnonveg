<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Primary Meta Tags --}}
    <title>@yield('title', 'FreshNonVeg – Fresh Non Veg Delivered to Your Door')</title>
    <meta name="title" content="@yield('meta_title', 'FreshNonVeg | Fresh Non-Veg Delivery in Noida & NCR')">
    <meta name="description" content="@yield('meta_description', 'FreshNonVeg delivers fresh mutton, chicken, fish and seafood to your door. Only fresh, no frozen – direct from meat shop in Noida & NCR.')">
    <meta name="keywords" content="@yield('meta_keywords', 'fresh non veg, mutton delivery, chicken delivery, Noida, Greater Noida, FreshNonVeg')">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <meta name="author" content="FreshNonVeg">
    <meta name="language" content="English">
    <meta name="revisit-after" content="7 days">

    {{-- Open Graph / Facebook Meta Tags --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:title" content="@yield('og_title', 'FreshNonVeg | Fresh Non-Veg Delivery in Noida & NCR')">
    <meta property="og:description" content="@yield('og_description', 'Fresh mutton, chicken, fish & seafood delivered fresh to your home in Noida & NCR.')">
    <meta property="og:image" content="@yield('og_image', asset('website_assets/image/logo.png'))">
    <meta property="og:site_name" content="FreshNonVeg">
    <meta property="og:locale" content="en_IN">

    {{-- Twitter Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="@yield('twitter_url', url()->current())">
    <meta name="twitter:title" content="@yield('twitter_title', 'FreshNonVeg | Fresh Non-Veg Delivery in Noida & NCR')">
    <meta name="twitter:description" content="@yield('twitter_description', 'Fresh mutton, chicken, fish & seafood delivered to your door in Noida & NCR.')">
    <meta name="twitter:image" content="@yield('twitter_image', asset('website_assets/image/logo.png'))">

    {{-- Additional Meta Tags --}}
    <meta name="geo.region" content="IN-DL">
    <meta name="geo.placename" content="New Delhi">
    <meta name="geo.position" content="28.6139;77.2090">
    <meta name="ICBM" content="28.6139, 77.2090">

    {{-- Canonical URL --}}
    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    {{-- Favicon --}}
    <link rel="icon" href="@yield('favicon', asset('website_assets/image/favicon.png'))" type="image/png">
    <link rel="shortcut icon" href="@yield('favicon', asset('website_assets/image/favicon.png'))" type="image/png">

    {{-- CSS --}}
    @include('Website.Layout.css')
    @yield('custom_css')
    @stack('styles')
</head>
<body>
    @include('Website.Layout.header')
    @yield('content')
    @include('Website.Layout.footer')
    @include('Website.Layout.mobile-bottom-nav')
    @include('Website.Layout.js')
    @yield('custom_js')
    @stack('scripts')
</body>
</html>
