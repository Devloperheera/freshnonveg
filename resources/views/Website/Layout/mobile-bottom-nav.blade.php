@php
    $guestLoginUrl = $guestLoginUrl ?? route('login');
    $wishlistUrl = auth()->check() ? route('wishlist') : $guestLoginUrl;
    $cartUrl = auth()->check() ? route('cart') : $guestLoginUrl;
    $accountUrl = auth()->check() ? route('dashboard') : $guestLoginUrl;
    $isHome = request()->routeIs('home');
    $isSearch = request()->routeIs('shop.index');
    $isWishlist = request()->routeIs('wishlist');
    $isCart = request()->routeIs('cart');
    $isAccount = request()->routeIs('profile', 'login', 'register');
@endphp
<nav class="mobile-bottom-nav d-md-none" aria-label="Mobile navigation">
    <a href="{{ route('home') }}" class="mobile-bottom-nav__item {{ $isHome ? 'active' : '' }}">
        <i class="bi bi-house" aria-hidden="true"></i>
        <span>Home</span>
    </a>
    <button type="button" class="mobile-bottom-nav__item {{ $isSearch ? 'active' : '' }}" id="mobileBottomSearch" aria-label="Open search">
        <i class="bi bi-search" aria-hidden="true"></i>
        <span>Search</span>
    </button>
    <a href="{{ $wishlistUrl }}" class="mobile-bottom-nav__item {{ $isWishlist ? 'active' : '' }}">
        <i class="bi bi-heart" aria-hidden="true"></i>
        <span>Wishlist</span>
    </a>
    <a href="{{ $cartUrl }}" class="mobile-bottom-nav__item {{ $isCart ? 'active' : '' }}">
        <i class="bi bi-bag" aria-hidden="true"></i>
        <span>Cart</span>
    </a>
    <a href="{{ $accountUrl }}" class="mobile-bottom-nav__item {{ $isAccount ? 'active' : '' }}">
        <i class="bi bi-person" aria-hidden="true"></i>
        <span>Account</span>
    </a>
</nav>

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('mobileBottomSearch');
    if (!btn) return;
    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        setTimeout(function () {
            var toggle = document.getElementById('headerSearchToggle');
            if (toggle) toggle.click();
        }, 280);
    });
})();
</script>
@endpush
