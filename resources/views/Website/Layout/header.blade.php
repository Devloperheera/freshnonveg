@php
    $guestLoginUrl = $guestLoginUrl ?? route('login');
    $headerCounts = auth()->check()
        ? \App\Support\SpeedCache::headerCounts((int) auth()->id())
        : ['wishlist' => 0, 'cart' => 0];
    $wishlistCount = (int) $headerCounts['wishlist'];
    $cartCount = (int) $headerCounts['cart'];
    $wishlistUrl = auth()->check() ? route('wishlist') : $guestLoginUrl;
    $cartUrl = auth()->check() ? route('cart') : $guestLoginUrl;
@endphp
<nav class="navbar navbar-expand-md" id="siteHeader">
    <div class="container header-bar">
      <a class="navbar-brand logo header-bar-brand d-flex align-items-center mb-0" href="{{ route('home') }}">
        <img src="{{ asset('website_assets/image/logo.png') }}" alt="Fresh Non Veg" class="header-logo-img" width="220" height="48">
      </a>

      <div class="header-mobile-actions d-flex d-md-none align-items-center">
        <button type="button" class="header-icon-btn" id="headerSearchToggle" aria-label="Search" aria-expanded="false" aria-controls="headerSearchPanel">
          <i class="bi bi-search" aria-hidden="true"></i>
        </button>
        <a class="header-icon-btn header-icon-wishlist wishlist-icon" href="{{ $wishlistUrl }}" title="Wishlist" aria-label="Wishlist">
            <i class="bi bi-heart" aria-hidden="true"></i>
            @if($wishlistCount > 0)
                <em>{{ $wishlistCount > 99 ? '99+' : $wishlistCount }}</em>
            @endif
        </a>
        <a class="header-icon-btn header-icon-cart cart" href="{{ $cartUrl }}" title="Cart" aria-label="Cart">
            <i class="bi bi-bag" aria-hidden="true"></i>
            @if($cartCount > 0)
                <em>{{ $cartCount > 99 ? '99+' : $cartCount }}</em>
            @endif
        </a>
        @auth
            <div class="header-profile">
                <button type="button" class="btn btn-fun btn-yel header-mobile-login header-profile-toggle" aria-expanded="false" aria-haspopup="true">
                    Profile / प्रोफाइल
                </button>
                <div class="header-profile-menu" hidden>
                    <a href="{{ route('dashboard') }}"><i class="bi bi-grid me-1"></i> My dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
                    </form>
                </div>
            </div>
        @else
            <a class="btn btn-fun btn-yel header-mobile-login" href="{{ $guestLoginUrl }}">Login / लॉगिन</a>
        @endauth
      </div>

      <div class="navbar-collapse header-bar-collapse header-bar-panel" id="headerSearchPanel">
        <div class="header-search-wrap my-2 my-md-0" id="headerSearchWrap">
          <form class="header-search-form" action="{{ route('shop.index') }}" method="get" role="search" autocomplete="off">
            <i class="bi bi-search header-search-icon" aria-hidden="true"></i>
            <input type="search" name="q" id="headerSearchInput" class="header-search-input"
                   placeholder="Category, tag ya product search…" aria-label="Search catalog" autocomplete="off"
                   value="{{ request('q') }}">
            <button type="button" class="header-search-clear" id="headerSearchClear" hidden aria-label="Clear search">&times;</button>
          </form>
          <div class="header-search-dropdown" id="headerSearchDropdown" hidden></div>
        </div>

        <div class="d-none d-md-flex align-items-center gap-2 gap-md-3 header-actions header-bar-actions">
            <a class="wishlist-icon" href="{{ $wishlistUrl }}" title="Wishlist" aria-label="Wishlist">
                <i class="bi bi-heart-fill"></i>
                @if($wishlistCount > 0)
                    <em>{{ $wishlistCount > 99 ? '99+' : $wishlistCount }}</em>
                @endif
            </a>
            <a class="cart" href="{{ $cartUrl }}" title="Cart" aria-label="Cart">
                🛒
                @if($cartCount > 0)
                    <em>{{ $cartCount > 99 ? '99+' : $cartCount }}</em>
                @endif
            </a>
            @auth
                <div class="header-profile">
                    <button type="button" class="btn btn-fun btn-yel btn-sm px-3 header-profile-toggle" aria-expanded="false" aria-haspopup="true">
                        Profile / प्रोफाइल
                    </button>
                    <div class="header-profile-menu" hidden>
                        <a href="{{ route('dashboard') }}"><i class="bi bi-grid me-1"></i> My dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
                        </form>
                    </div>
                </div>
            @else
                <a class="btn btn-fun btn-yel btn-sm px-3" href="{{ $guestLoginUrl }}">Login / लॉगिन</a>
            @endauth
        </div>
      </div>
    </div>
  </nav>

@push('scripts')
<script>
(function () {
    var wrap = document.getElementById('headerSearchWrap');
    var input = document.getElementById('headerSearchInput');
    var dropdown = document.getElementById('headerSearchDropdown');
    var clearBtn = document.getElementById('headerSearchClear');
    var searchUrl = @json(route('catalog.search'));
    var timer = null;
    var searchToggle = document.getElementById('headerSearchToggle');
    var searchPanel = document.getElementById('headerSearchPanel');
    var siteHeader = document.getElementById('siteHeader');

    function syncHeaderOffset() {
        if (!siteHeader) return;
        document.documentElement.style.setProperty('--site-header-h', siteHeader.offsetHeight + 'px');
    }

    syncHeaderOffset();
    window.addEventListener('resize', syncHeaderOffset);
    if (typeof ResizeObserver !== 'undefined' && siteHeader) {
        new ResizeObserver(syncHeaderOffset).observe(siteHeader);
    }

    function isMobileHeader() {
        return window.matchMedia('(max-width: 767.98px)').matches;
    }

    function setSearchPanelOpen(open) {
        if (!searchPanel || !searchToggle) return;
        searchPanel.classList.toggle('header-search-open', open);
        searchToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open && input) {
            setTimeout(function () { input.focus(); }, 80);
        }
        if (!open) hideDropdown();
        syncHeaderOffset();
    }

    if (searchToggle && searchPanel) {
        searchToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var willOpen = !searchPanel.classList.contains('header-search-open');
            setSearchPanelOpen(willOpen);
        });
    }

    document.addEventListener('click', function (e) {
        if (!isMobileHeader() || !searchPanel) return;
        if (!searchPanel.classList.contains('header-search-open')) return;
        if (siteHeader && siteHeader.contains(e.target)) return;
        setSearchPanelOpen(false);
    });

    window.addEventListener('resize', function () {
        if (!isMobileHeader()) setSearchPanelOpen(false);
    });

    if (!wrap || !input || !dropdown) return;

    function hideDropdown() {
        dropdown.hidden = true;
        dropdown.innerHTML = '';
    }

    function showClear() {
        if (clearBtn) clearBtn.hidden = !(input.value && input.value.length);
    }

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    }

    function renderSection(title, items, renderItem) {
        if (!items || !items.length) return '';
        var html = '<div class="header-search-section"><div class="header-search-section-title">' + esc(title) + '</div>';
        items.forEach(function (item) { html += renderItem(item); });
        return html + '</div>';
    }

    function renderResults(data) {
        var html = '';
        html += renderSection('Categories', data.categories, function (item) {
            return '<a class="header-search-item" href="' + esc(item.url) + '">' +
                (item.image ? '<img src="' + esc(item.image) + '" alt="">' : '<span class="header-search-item-ph"><i class="bi bi-grid"></i></span>') +
                '<span><strong>' + esc(item.name) + '</strong><small>Category</small></span></a>';
        });
        html += renderSection('Tags / Sub-filters', data.subcategories, function (item) {
            return '<a class="header-search-item" href="' + esc(item.url) + '">' +
                '<span class="header-search-item-ph"><i class="bi bi-tag"></i></span>' +
                '<span><strong>' + esc(item.name) + '</strong><small>Tag</small></span></a>';
        });
        html += renderSection('Products', data.products, function (item) {
            var price = item.price != null ? '₹' + Math.round(item.price) : '';
            return '<a class="header-search-item" href="' + esc(item.url) + '">' +
                (item.image ? '<img src="' + esc(item.image) + '" alt="">' : '<span class="header-search-item-ph"><i class="bi bi-box-seam"></i></span>') +
                '<span><strong>' + esc(item.name) + '</strong><small>' + esc(item.category || 'Product') + (price ? ' · ' + price : '') + '</small></span></a>';
        });
        if (!html) {
            html = '<div class="header-search-empty">Koi result nahi mila</div>';
        }
        dropdown.innerHTML = html;
        dropdown.hidden = false;
    }

    function fetchResults(q) {
        fetch(searchUrl + '?q=' + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(renderResults)
            .catch(function () {
                dropdown.innerHTML = '<div class="header-search-empty">Search abhi load nahi ho paya</div>';
                dropdown.hidden = false;
            });
    }

    input.addEventListener('input', function () {
        showClear();
        var q = input.value.trim();
        clearTimeout(timer);
        if (q.length < 2) {
            hideDropdown();
            return;
        }
        timer = setTimeout(function () { fetchResults(q); }, 280);
    });

    input.addEventListener('focus', function () {
        if (input.value.trim().length >= 2) fetchResults(input.value.trim());
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            showClear();
            hideDropdown();
            input.focus();
        });
    }

    document.addEventListener('click', function (e) {
        if (wrap && !wrap.contains(e.target)) hideDropdown();
    });

    showClear();

    document.querySelectorAll('.header-profile').forEach(function (box) {
        var toggle = box.querySelector('.header-profile-toggle');
        var menu = box.querySelector('.header-profile-menu');
        if (!toggle || !menu) {
            return;
        }
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = menu.hidden;
            document.querySelectorAll('.header-profile-menu').forEach(function (other) {
                other.hidden = true;
            });
            document.querySelectorAll('.header-profile-toggle').forEach(function (btn) {
                btn.setAttribute('aria-expanded', 'false');
            });
            menu.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.header-profile')) {
            return;
        }
        document.querySelectorAll('.header-profile-menu').forEach(function (menu) {
            menu.hidden = true;
        });
        document.querySelectorAll('.header-profile-toggle').forEach(function (btn) {
            btn.setAttribute('aria-expanded', 'false');
        });
    });
})();
</script>
@endpush
