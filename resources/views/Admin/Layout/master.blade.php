<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('website_assets/image/favicon.png') }}">
    <title>@yield('title', 'FreshNonVeg - Home')</title>
    @include('Admin.Layout.css')
    @yield('custom_css')
</head>
<body>

    {{-- Sidebar (fixed position, bahar rahega) --}}
    @include('Admin.Layout.sidebar')

    {{-- ✅ Main Content wrapper YAHAN open hoga --}}
    <div class="main-content" id="mainContent">

        {{-- Top Navbar --}}
        @include('Admin.Layout.navbar')

        {{-- Page Content --}}
        <div class="content-area">
            @yield('content')
        </div>

    </div>
    {{-- ✅ Main Content wrapper YAHAN close hoga --}}

    @include('Admin.Layout.js')
    @yield('custom_js')

</body>
</html>
