<div class="sidebar" id="sidebar">
    <div class="sidebar-header d-flex align-items-center justify-content-center">
        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
            <img src="{{ asset('website_assets/image/logo.png') }}" width="200" alt="Logo">
        </a>
    </div>
    <nav class="sidebar-menu">

        {{-- MAIN --}}
        <div class="menu-section">MAIN</div>
        <a href="{{ route('admin.dashboard') }}"
            class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <div class="menu-item-content"><i class="bi bi-graph-up"></i><span>Dashboard</span></div>
        </a>
        <a href="{{ route('admin.profile') }}"
            class="menu-item {{ request()->routeIs('admin.profile*') ? 'active' : '' }}">
            <div class="menu-item-content"><i class="bi bi-person-circle"></i><span>My Profile</span></div>
        </a>

        {{-- CUSTOMERS --}}
        <div class="menu-section">CUSTOMERS</div>
        <a href="{{ route('admin.users.index') }}"
            class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-people"></i>
                <span>Users</span>
            </div>
            <span class="menu-badge green">{{ $sidebarCounts['users'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.wishlists.index') }}"
            class="menu-item {{ request()->routeIs('admin.wishlists.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-heart"></i>
                <span>Wishlist</span>
            </div>
            <span class="menu-badge green">{{ $sidebarCounts['wishlist_items'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.carts.index') }}"
            class="menu-item {{ request()->routeIs('admin.carts.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-cart3"></i>
                <span>Cart</span>
            </div>
            <span class="menu-badge green">{{ $sidebarCounts['cart_items'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.orders.index') }}"
            class="menu-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-bag-check"></i>
                <span>Orders</span>
            </div>
            <span class="menu-badge green">{{ $sidebarCounts['orders'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.wallet.index') }}"
            class="menu-item {{ request()->routeIs('admin.wallet.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-wallet2"></i>
                <span>Wallet</span>
            </div>
            <span class="menu-badge green">₹{{ number_format($sidebarCounts['wallet_balance'] ?? 0) }}</span>
        </a>
        <a href="{{ route('admin.complaints.index') }}"
            class="menu-item {{ request()->routeIs('admin.complaints.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-chat-left-dots"></i>
                <span>Complaints</span>
            </div>
            <span class="menu-badge red">{{ $sidebarCounts['open_complaints'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.product-reviews.index') }}"
            class="menu-item {{ request()->routeIs('admin.product-reviews.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-star"></i>
                <span>Product Reviews</span>
            </div>
            <span class="menu-badge green">{{ $sidebarCounts['product_reviews'] ?? 0 }}</span>
        </a>

        

        {{-- CATALOG --}}
        <div class="menu-section">CATALOG</div>
        <a href="{{ route('admin.banners.index') }}"
            class="menu-item {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-card-image"></i>
                <span>Banners</span>
            </div>
            <span class="menu-badge green">{{ $sidebarCounts['banners'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.categories.index') }}"
            class="menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-grid"></i>
                <span>Categories</span>
            </div>
            <span class="menu-badge green">{{ $sidebarCounts['categories'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.products.index') }}"
            class="menu-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-box-seam"></i>
                <span>Products</span>
            </div>
            <span class="menu-badge green">{{ $sidebarCounts['products'] ?? 0 }}</span>
        </a>

        <a href="{{ route('admin.pages.index') }}"
            class="menu-item {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-file-earmark"></i>
                <span>Pages</span>
            </div>
            <span class="menu-badge blue">{{ $sidebarCounts['pages'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.page-contents.index') }}"
            class="menu-item {{ request()->routeIs('admin.page-contents.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-file-earmark-text"></i>
                <span>Page Content</span>
            </div>
            <span class="menu-badge blue">{{ $sidebarCounts['page_contents'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.testimonials.index') }}"
            class="menu-item {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-chat-quote"></i>
                <span>Testimonials</span>
            </div>
            <span class="menu-badge blue">{{ $sidebarCounts['testimonials'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.faqs.index') }}"
            class="menu-item {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-question-circle"></i>
                <span>FAQs</span>
            </div>
            <span class="menu-badge blue">{{ $sidebarCounts['faqs'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.advantage-items.index') }}"
            class="menu-item {{ request()->routeIs('admin.advantage-items.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-heart-pulse"></i>
                <span>Fresh Advantages</span>
            </div>
            <span class="menu-badge blue">{{ $sidebarCounts['advantage_items'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.order-steps.index') }}"
            class="menu-item {{ request()->routeIs('admin.order-steps.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-list-ol"></i>
                <span>Order Steps</span>
            </div>
            <span class="menu-badge blue">{{ $sidebarCounts['order_steps'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.site-stats.index') }}"
            class="menu-item {{ request()->routeIs('admin.site-stats.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-bar-chart"></i>
                <span>Home Stats</span>
            </div>
            <span class="menu-badge blue">{{ $sidebarCounts['site_stats'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.delivery-zones.index') }}"
            class="menu-item {{ request()->routeIs('admin.delivery-zones.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-map"></i>
                <span>Delivery Zones</span>
            </div>
            <span class="menu-badge blue">{{ $sidebarCounts['delivery_zones'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.coupons.index') }}"
            class="menu-item {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-ticket-perforated"></i>
                <span>Coupons</span>
            </div>
            <span class="menu-badge blue">{{ $sidebarCounts['coupons'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.slots.index') }}"
            class="menu-item {{ request()->routeIs('admin.slots.*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-clock"></i>
                <span>Delivery Slots</span>
            </div>
            <span class="menu-badge blue">{{ $sidebarCounts['delivery_slots'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.header-content.index') }}"
            class="menu-item {{ request()->routeIs('admin.header-content.index*') ? 'active' : '' }}">
            <div class="menu-item-content">
                <i class="bi bi-shield-check"></i>
                <span>Settings</span>
            </div>
        </a>

        <a href="javascript:void(0)" class="menu-item logout-item" onclick="sidebarLogout()">
            <div class="menu-item-content"><i class="bi bi-box-arrow-left"></i><span>Logout</span></div>
        </a>
        <form id="sidebarLogoutForm" action="{{ route('admin.logout') }}" method="POST" style="display:none">@csrf
        </form>
    </nav>
    <div class="sidebar-footer">
        <div class="admin-avatar-wrap">
            <div class="admin-av">{{ strtoupper(substr(auth('admin')->user()->name, 0, 1)) }}</div>
            <div>
                <div class="admin-av-name">{{ auth('admin')->user()->name }}</div>
                <div class="admin-av-role">Admin</div>
            </div>
        </div>
    </div>
</div>
<style>
    .sidebar {
        width: 218px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1001;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: all .3s;
        background: #2BAE49;
        color: #fff;
        box-shadow: 3px 0 14px rgba(0, 62, 126, .32)
    }

    .sidebar.collapsed {
        width: 56px
    }

    .sidebar-menu {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 4px 0 10px
    }

    .menu-section {
        padding: 10px 12px 4px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .07em;
        color: rgba(255, 255, 255, .4);
        text-transform: uppercase
    }

    .menu-item {
        padding: 7px 10px 7px 12px;
        color: rgba(255, 255, 255, .88);
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        border-left: 2px solid transparent;
        margin: 0;
        font-size: 12.5px;
        font-weight: 500;
        transition: all .15s
    }

    .menu-item i {
        width: 16px;
        text-align: center;
        font-size: 13.5px;
        margin: 0;
        color: rgba(255, 255, 255, .72);
        flex-shrink: 0
    }

    .menu-item-content {
        display: flex;
        align-items: center;
        flex: 1;
        gap: 8px;
        min-width: 0
    }

    .menu-item-content span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis
    }

    .menu-badge {
        flex-shrink: 0;
        min-width: 18px;
        height: 17px;
        padding: 0 6px;
        border-radius: 9px;
        font-size: 9.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        white-space: nowrap
    }

    .menu-badge.green {
       background: #fbd303;
    color: #004e15;
    }

.menu-badge.blue {
    background: #fbd303;
    color: #004e15;
}

    .menu-badge.red {
        background: #ef4444;
        color: #fff
    }

    .menu-badge.yellow {
        background: #fbbf24;
        color: #1f2937
    }

    .menu-item:hover {
        background: rgba(255, 255, 255, .08);
        color: #fff
    }

    .menu-item:hover i {
        color: #fff
    }

    .menu-item.active {
        background: rgba(255, 255, 255, .12);
        color: #fff;
        border-left-color: #F57C00
    }

    .menu-item.active i {
        color: #F57C00
    }

    .menu-item.logout-item {
        color: #fca5a5
    }

    .menu-item.logout-item i {
        color: #f87171
    }

    .menu-item.logout-item:hover {
        background: rgba(220, 53, 69, .18);
        color: #fecaca;
        border-left-color: #dc3545
    }

    .sidebar.collapsed .menu-section,
    .sidebar.collapsed .menu-item-content span,
    .sidebar.collapsed .menu-badge {
        display: none
    }

    .sidebar.collapsed .menu-item {
        justify-content: center;
        padding: 8px 0
    }

    .sidebar.collapsed .menu-item-content {
        justify-content: center
    }

    .sidebar-footer {
        padding: 8px 10px;
        border-top: 1px solid rgba(255, 255, 255, .1);
        flex-shrink: 0;
        background: rgba(0, 0, 0, .2)
    }

    .admin-avatar-wrap {
        display: flex;
        align-items: center;
        gap: 7px
    }

    .admin-av {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
        background: linear-gradient(135deg, #F57C00, #D32F2F);
        color: #fff;
        border: 1px solid rgba(255, 255, 255, .25)
    }

    .admin-av-name {
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.2;
        color: #fff
    }

    .admin-av-role {
        font-size: 9.5px;
        color: #F57C00
    }

    .sidebar.collapsed .admin-av-name,
    .sidebar.collapsed .admin-av-role {
        display: none
    }

    .sidebar-menu::-webkit-scrollbar {
        width: 6px
    }

    .sidebar-menu::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, .08);
        border-radius: 6px;
        margin: 4px 0
    }

    .sidebar-menu::-webkit-scrollbar-thumb {
        background: rgba(231, 120, 45, .85);
        border-radius: 6px
    }

    .sidebar-menu::-webkit-scrollbar-thumb:hover {
        background: #F57C00
    }

    .sidebar-menu {
        scrollbar-width: thin;
        scrollbar-color: #F57C00 rgba(255, 255, 255, .08)
    }

    @media(max-width:768px) {
        .sidebar {
            left: -218px
        }

        .sidebar.mobile-active {
            left: 0
        }
    }
</style>
<script>
    function sidebarLogout() {
        Swal.fire({
            title: 'Logout?',
            text: 'Are you sure?',
            icon: 'warning',
            showCancelButton: !0,
            confirmButtonText: 'Yes, Logout!',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            reverseButtons: !0
        }).then(function(r) {
            if (r.isConfirmed) {
                Swal.fire({
                    title: 'Logging out...',
                    allowOutsideClick: !1,
                    showConfirmButton: !1,
                    didOpen: () => Swal.showLoading()
                });
                setTimeout(() => document.getElementById('sidebarLogoutForm').submit(), 800)
            }
        })
    }
</script>
