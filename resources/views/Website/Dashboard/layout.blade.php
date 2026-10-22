@extends('Website.Layout.master')

@section('content')
@php
    $udUser = auth()->user();
    $udInitial = mb_strtoupper(mb_substr($udUser->name ?? 'U', 0, 1));
    $udNav = [
        ['route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'bi-grid-1x2', 'label' => 'Overview'],
        ['route' => 'dashboard.orders', 'match' => 'dashboard.orders', 'icon' => 'bi-bag-check', 'label' => 'My orders'],
        ['route' => 'dashboard.wallet', 'match' => 'dashboard.wallet', 'icon' => 'bi-wallet2', 'label' => 'Wallet'],
        ['route' => 'dashboard.ratings', 'match' => 'dashboard.ratings*', 'icon' => 'bi-star', 'label' => 'My ratings'],
        ['route' => 'dashboard.complaints', 'match' => 'dashboard.complaints*', 'icon' => 'bi-chat-left-dots', 'label' => 'Complaints'],
        ['route' => 'profile', 'match' => 'profile', 'icon' => 'bi-person-gear', 'label' => 'Profile settings'],
    ];
@endphp
<section class="ud">
    <div class="container">
        <div class="ud-shell">
            <aside class="ud-side">
                <div class="ud-user">
                    @if($udUser->profile_image)
                        <img src="{{ $udUser->profile_image }}" alt="" class="ud-avatar">
                    @else
                        <div class="ud-avatar">{{ $udInitial }}</div>
                    @endif
                    <div class="ud-user-meta">
                        <div class="ud-user-name">{{ $udUser->name }}</div>
                        <div class="ud-user-sub">{{ $udUser->phone ?: $udUser->email }}</div>
                    </div>
                </div>

                <a href="{{ route('dashboard.wallet') }}" class="ud-wallet-chip">
                    <span><i class="bi bi-wallet2 me-1"></i> Wallet</span>
                    <strong>₹{{ number_format((float) $udUser->wallet_balance, 2) }}</strong>
                </a>

                <nav class="ud-nav">
                    @foreach($udNav as $item)
                        <a href="{{ route($item['route']) }}" class="ud-nav-link {{ request()->routeIs($item['match']) ? 'active' : '' }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="ud-nav-link ud-nav-logout">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </nav>
            </aside>

            <main class="ud-main">
                <div class="ud-head">
                    <div>
                        <p class="ud-eyebrow">FreshNonVeg · Member</p>
                        <h1 class="ud-title">@yield('dashboard_title', 'Dashboard')</h1>
                    </div>
                    @yield('dashboard_actions')
                </div>

                @yield('dashboard')
            </main>
        </div>
    </div>
</section>
@endsection
