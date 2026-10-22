{{-- resources/views/Admin/Layout/navbar.blade.php --}}

<nav class="top-nav">

    <button class="menu-toggle" id="menuToggle" type="button">
        <i class="bi bi-list" id="menuIcon"></i>
    </button>

    {{-- Sidebar search with autosuggest --}}
    <div class="nav-search-wrap" id="navSearchWrap">
        <div class="nav-search-box">
            <i class="bi bi-search nav-search-icon"></i>
            <input type="text"
                   id="adminSidebarSearch"
                   class="nav-search-input"
                   placeholder="Search sidebar menu..."
                   autocomplete="off"
                   aria-label="Search sidebar menu">
            <button type="button" class="nav-search-clear" id="navSearchClear" title="Clear" style="display:none;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="nav-search-suggest" id="navSearchSuggest" hidden></div>
    </div>

    <div class="nav-right">
        @auth('admin')
        <div class="nav-bell-wrap" id="navBellWrap">
            <button type="button" class="nav-bell-btn" id="navBellBtn" title="Order alerts" aria-label="Order notifications">
                <i class="bi bi-bell-fill"></i>
                <span class="nav-bell-badge" id="navBellBadge" hidden>0</span>
            </button>
            <div class="nav-bell-drop" id="navBellDrop" hidden>
                <div class="nav-bell-head">
                    <strong>New orders</strong>
                    <button type="button" class="nav-bell-clear" id="navBellClear">Sab dekh liye</button>
                </div>
                <div class="nav-bell-list" id="navBellList">
                    <div class="nav-bell-empty">Koi nayi order nahi</div>
                </div>
            </div>
        </div>
        <audio id="orderAlertSound" preload="auto" playsinline>
            <source src="/admin/sounds/new-order.wav" type="audio/wav">
        </audio>
        @endauth

        <div class="user-info" id="userInfoWrap">
        <div class="user-dropdown" id="userChip" onclick="toggleAdminDropdown(event)">
            <span class="welcome-text">
                @auth('admin')
                    Welcome, {{ Auth::guard('admin')->user()->name }}!
                @else
                    Welcome, Guest!
                @endauth
            </span>
            <div class="user-avatar">
                @auth('admin')
                    {{ strtoupper(substr(Auth::guard('admin')->user()->name, 0, 1)) }}
                @else
                    <i class="bi bi-person"></i>
                @endauth
            </div>
            <i class="bi bi-chevron-down drop-arrow" id="dropArrow"></i>
        </div>

        <div class="admin-dropdown" id="adminDropdown">
            @auth('admin')
                <div class="adrop-header">
                    <div class="adrop-avatar">
                        {{ strtoupper(substr(Auth::guard('admin')->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="adrop-name">{{ Auth::guard('admin')->user()->name }}</div>
                        <div class="adrop-email">{{ Auth::guard('admin')->user()->email }}</div>
                    </div>
                </div>
                <div class="adrop-divider"></div>
                <a href="{{ route('admin.dashboard') }}" class="adrop-item">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('admin.profile') }}" class="adrop-item">
                    <i class="bi bi-person-circle"></i> My Profile
                </a>
                <a href="{{ route('admin.users.index') }}" class="adrop-item">
                    <i class="bi bi-people"></i> Users
                </a>
                <a href="{{ route('admin.invoices.index') }}" class="adrop-item">
                    <i class="bi bi-receipt"></i> Invoices
                </a>
                <div class="adrop-divider"></div>
                <a href="javascript:void(0)" class="adrop-item adrop-logout" onclick="adminLogout()">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </a>
            @else
                <a href="{{ route('admin.login') }}" class="adrop-item">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
            @endauth
        </div>
        </div>
    </div>

    @auth('admin')
    <form id="adminLogoutForm" method="POST" action="{{ route('admin.logout') }}" style="display:none;">
        @csrf
    </form>
    @endauth

</nav>

<style>
    .top-nav {
        height: 70px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 0 24px;
        border-bottom: 3px solid #F57C00;
        box-shadow: 0 2px 12px rgba(0,62,126,.1);
        position: sticky;
        top: 0;
        z-index: 500;
    }

    .menu-toggle {
        width: 42px;
        height: 42px;
        background: #f0f5fa;
        border: 1.5px solid #c5d8ec;
        border-radius: 10px;
        color: #2BAE49;
        font-size: 1.25rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .menu-toggle:hover  { background: #2BAE49; color: #fff; }
    .menu-toggle:focus  { outline: none; box-shadow: none; }

    .nav-search-wrap {
        flex: 1;
        max-width: 520px;
        position: relative;
        margin: 0 auto;
    }
    .nav-search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f4f8f5;
        border: 1.5px solid #cfe0d5;
        border-radius: 12px;
        padding: 0 12px;
        min-height: 42px;
        transition: border-color .15s, box-shadow .15s;
    }
    .nav-search-box:focus-within {
        border-color: #2BAE49;
        box-shadow: 0 0 0 3px rgba(7,91,43,.12);
        background: #fff;
    }
    .nav-search-icon { color: #2BAE49; font-size: 1rem; }
    .nav-search-input {
        flex: 1;
        border: 0;
        outline: none;
        background: transparent;
        font-size: .9rem;
        color: #073b1f;
        min-width: 0;
        height: 40px;
    }
    .nav-search-input::placeholder { color: #8aa193; }
    .nav-search-clear {
        border: 0;
        background: transparent;
        color: #6b7c72;
        padding: 0;
        width: 24px;
        height: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .nav-search-suggest {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #d9e8de;
        border-radius: 12px;
        box-shadow: 0 12px 28px rgba(7,91,43,.14);
        max-height: 320px;
        overflow-y: auto;
        z-index: 10000;
    }
    .nav-suggest-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        text-decoration: none;
        color: #073b1f;
        border-bottom: 1px solid #f0f5f2;
        font-size: .88rem;
        font-weight: 600;
    }
    .nav-suggest-item:last-child { border-bottom: 0; }
    .nav-suggest-item:hover,
    .nav-suggest-item.active {
        background: #eef7f1;
        color: #2BAE49;
    }
    .nav-suggest-item i {
        width: 20px;
        text-align: center;
        color: #2BAE49;
    }
    .nav-suggest-empty {
        padding: 14px;
        text-align: center;
        color: #8aa193;
        font-size: .85rem;
    }

    .nav-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
        margin-left: auto;
    }

    .nav-bell-wrap { position: relative; }

    .nav-bell-btn {
        width: 44px;
        height: 44px;
        border: 1.5px solid #ffd7b0;
        border-radius: 12px;
        background: linear-gradient(180deg, #fff7ed, #ffe8cc);
        color: #E65100;
        font-size: 1.2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(245,124,0,.18);
        transition: transform .15s, box-shadow .15s;
    }
    .nav-bell-btn:hover { transform: translateY(-1px); color: #bf360c; }
    .nav-bell-btn.ringing i {
        animation: bellShake .55s ease-in-out infinite;
        transform-origin: top center;
    }

    @keyframes bellShake {
        0%, 100% { transform: rotate(0deg); }
        20% { transform: rotate(18deg); }
        40% { transform: rotate(-16deg); }
        60% { transform: rotate(10deg); }
        80% { transform: rotate(-8deg); }
    }

    .nav-bell-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: 999px;
        background: #D32F2F;
        color: #fff;
        font-size: .68rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 0 2px #fff;
    }

    .nav-bell-drop {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 360px;
        max-width: min(360px, calc(100vw - 24px));
        background: #fff;
        border: 1px solid #f0d9c0;
        border-radius: 16px;
        box-shadow: 0 16px 40px rgba(62,39,0,.18);
        overflow: hidden;
        z-index: 10001;
        animation: adropIn .18s ease;
    }
    .nav-bell-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 12px 14px;
        background: linear-gradient(135deg, #2BAE49, #F57C00);
        color: #fff;
    }
    .nav-bell-head strong { font-size: .9rem; }
    .nav-bell-clear {
        border: 0;
        background: rgba(255,255,255,.18);
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 4px 10px;
        cursor: pointer;
    }
    .nav-bell-list { max-height: 360px; overflow-y: auto; }
    .nav-bell-empty {
        padding: 28px 16px;
        text-align: center;
        color: #8aa193;
        font-size: .86rem;
    }
    .nav-bell-item {
        display: flex;
        gap: 10px;
        padding: 12px 14px;
        text-decoration: none;
        color: #123;
        border-bottom: 1px solid #f6efe6;
        cursor: pointer;
        background: #fff;
        width: 100%;
        text-align: left;
    }
    .nav-bell-item:hover { background: #fff8f0; }
    .nav-bell-item-ico {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #fff3e0;
        color: #E65100;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.05rem;
    }
    .nav-bell-item strong { display: block; font-size: .86rem; }
    .nav-bell-item span { display: block; font-size: .75rem; color: #6b7c72; margin-top: 2px; }

    .order-alert-toast {
        position: fixed;
        top: 86px;
        right: 18px;
        z-index: 12000;
        width: 340px;
        max-width: calc(100vw - 24px);
        background: #fff;
        border-radius: 16px;
        border: 1px solid #ffcc80;
        box-shadow: 0 18px 40px rgba(230,81,0,.22);
        padding: 14px 16px;
        display: none;
        cursor: pointer;
        animation: adropIn .2s ease;
    }
    .order-alert-toast.show { display: block; }
    .order-alert-toast b { color: #E65100; }
    .order-alert-toast p { margin: 4px 0 0; font-size: .82rem; color: #445; }

    .user-info { position: relative; flex-shrink: 0; }

    .user-dropdown {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        border-radius: 50px;
        background: linear-gradient(135deg, #2BAE49, #F57C00);
        color: #fff;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease;
        box-shadow: 0 4px 14px rgba(0,62,126,.3);
    }
    .user-dropdown:hover { transform: translateY(-1px); }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255,255,255,.2);
        border: 2px solid rgba(255,255,255,.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .82rem;
        font-weight: 800;
    }

    .welcome-text   { font-size: .84rem; font-weight: 600; white-space: nowrap; }
    .drop-arrow     { font-size: .72rem; transition: transform 0.25s ease; }
    .drop-arrow.open { transform: rotate(180deg); }

    .admin-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 230px;
        background: #2BAE49;
        border-radius: 16px;
        box-shadow: 0 12px 40px rgba(0,62,126,.35);
        overflow: hidden;
        z-index: 9999;
        animation: adropIn .2s ease;
    }
    .admin-dropdown.open { display: block; }

    @keyframes adropIn {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .adrop-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        background: rgba(0,0,0,.15);
        border-bottom: 1px solid rgba(255,255,255,.1);
    }
    .adrop-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #F57C00, #D32F2F);
        color: #fff;
        font-weight: 800;
        font-size: .88rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .adrop-name  { font-size: .86rem; font-weight: 700; color: #fff; }
    .adrop-email {
        font-size: .71rem;
        color: rgba(255,255,255,.55);
        margin-top: 2px;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .adrop-divider { height: 1px; background: rgba(255,255,255,.1); margin: 4px 0; }

    .adrop-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: #c5d8ec;
        text-decoration: none;
        font-size: .86rem;
        font-weight: 500;
        transition: background .15s, transform .15s;
    }
    .adrop-item:hover   { background: rgba(231,120,45,.25); color: #fff; transform: translateX(4px); }
    .adrop-item i       { width: 20px; text-align: center; font-size: .95rem; flex-shrink: 0; }
    .adrop-logout       { color: #ffb3a0; }
    .adrop-logout:hover { background: rgba(231,120,45,.4); color: #fff; }

    @media (max-width: 768px) {
        .top-nav { padding: 0 14px; gap: 10px; }
        .nav-search-wrap { max-width: none; }
    }
    @media (max-width: 480px) {
        .welcome-text { display: none; }
        .nav-search-input::placeholder { content: 'Search...'; }
    }
</style>

<script>
    function toggleAdminDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('adminDropdown');
        const arrow    = document.getElementById('dropArrow');
        if (!dropdown) return;
        const isOpen = dropdown.classList.contains('open');
        dropdown.classList.toggle('open', !isOpen);
        arrow?.classList.toggle('open', !isOpen);
    }

    document.addEventListener('click', function (e) {
        const wrap = document.getElementById('userInfoWrap');
        if (wrap && !wrap.contains(e.target)) {
            document.getElementById('adminDropdown')?.classList.remove('open');
            document.getElementById('dropArrow')?.classList.remove('open');
        }
        const searchWrap = document.getElementById('navSearchWrap');
        if (searchWrap && !searchWrap.contains(e.target)) {
            document.getElementById('navSearchSuggest')?.setAttribute('hidden', 'hidden');
        }
        const bellWrap = document.getElementById('navBellWrap');
        if (bellWrap && !bellWrap.contains(e.target)) {
            document.getElementById('navBellDrop')?.setAttribute('hidden', 'hidden');
        }
    });

    function adminLogout() {
        document.getElementById('adminDropdown')?.classList.remove('open');
        document.getElementById('dropArrow')?.classList.remove('open');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Confirm Logout',
                text: 'Are you sure you want to logout?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Logout!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-danger mx-2',
                    cancelButton : 'btn btn-secondary mx-2'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Logging out...',
                        allowOutsideClick: false,
                        allowEscapeKey  : false,
                        showConfirmButton: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    setTimeout(() => {
                        document.getElementById('adminLogoutForm')?.submit();
                    }, 1500);
                }
            });
        } else {
            if (confirm('Are you sure you want to logout?')) {
                document.getElementById('adminLogoutForm')?.submit();
            }
        }
    }

    // ===== Sidebar menu search (navbar) =====
    (function () {
        const input = document.getElementById('adminSidebarSearch');
        const suggest = document.getElementById('navSearchSuggest');
        const clearBtn = document.getElementById('navSearchClear');
        if (!input || !suggest) return;

        function collectMenuItems() {
            const items = [];
            document.querySelectorAll('#sidebar .sidebar-menu a.menu-item').forEach(function (a) {
                const href = a.getAttribute('href') || '';
                if (!href || href.indexOf('javascript:') === 0) return;
                const labelEl = a.querySelector('.menu-item-content span');
                const iconEl = a.querySelector('.menu-item-content i');
                const label = (labelEl?.textContent || '').trim();
                if (!label) return;
                items.push({
                    label: label,
                    href: href,
                    icon: iconEl ? iconEl.className : 'bi bi-circle'
                });
            });
            return items;
        }

        let menuItems = [];
        let activeIndex = -1;

        function render(matches) {
            if (!matches.length) {
                suggest.innerHTML = '<div class="nav-suggest-empty">No menu found</div>';
                suggest.removeAttribute('hidden');
                return;
            }
            suggest.innerHTML = matches.map(function (m, i) {
                return '<a class="nav-suggest-item' + (i === activeIndex ? ' active' : '') + '" href="' + m.href + '" data-idx="' + i + '">' +
                    '<i class="' + m.icon + '"></i><span>' + m.label + '</span></a>';
            }).join('');
            suggest.removeAttribute('hidden');
        }

        function filter(q) {
            q = (q || '').trim().toLowerCase();
            if (!q) {
                suggest.setAttribute('hidden', 'hidden');
                clearBtn.style.display = 'none';
                return;
            }
            clearBtn.style.display = 'flex';
            if (!menuItems.length) menuItems = collectMenuItems();
            const matches = menuItems.filter(function (m) {
                return m.label.toLowerCase().indexOf(q) !== -1;
            }).slice(0, 10);
            activeIndex = matches.length ? 0 : -1;
            render(matches);
        }

        input.addEventListener('focus', function () {
            if (!menuItems.length) menuItems = collectMenuItems();
            if (input.value.trim()) filter(input.value);
        });

        input.addEventListener('input', function () {
            filter(this.value);
            filterSidebar(this.value);
        });

        input.addEventListener('keydown', function (e) {
            const items = suggest.querySelectorAll('.nav-suggest-item');
            if (suggest.hasAttribute('hidden') || !items.length) {
                if (e.key === 'Escape') {
                    input.blur();
                    suggest.setAttribute('hidden', 'hidden');
                }
                return;
            }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
            } else if (e.key === 'Enter') {
                e.preventDefault();
                const target = items[activeIndex] || items[0];
                if (target) window.location.href = target.getAttribute('href');
            } else if (e.key === 'Escape') {
                suggest.setAttribute('hidden', 'hidden');
                input.blur();
            }
        });

        function filterSidebar(q) {
            q = (q || '').trim().toLowerCase();
            document.querySelectorAll('#sidebar .sidebar-menu a.menu-item').forEach(function (a) {
                const href = a.getAttribute('href') || '';
                if (!href || href.indexOf('javascript:') === 0) return;
                const label = (a.querySelector('.menu-item-content span')?.textContent || '').trim().toLowerCase();
                const show = !q || label.indexOf(q) !== -1;
                a.style.display = show ? '' : 'none';
            });
            document.querySelectorAll('#sidebar .sidebar-menu .menu-section').forEach(function (sec) {
                let next = sec.nextElementSibling;
                let any = false;
                while (next && !next.classList.contains('menu-section')) {
                    if (next.classList.contains('menu-item') && next.style.display !== 'none') {
                        const href = next.getAttribute('href') || '';
                        if (href && href.indexOf('javascript:') !== 0) any = true;
                    }
                    next = next.nextElementSibling;
                }
                sec.style.display = (!q || any) ? '' : 'none';
            });
        }

        clearBtn.addEventListener('click', function () {
            input.value = '';
            clearBtn.style.display = 'none';
            suggest.setAttribute('hidden', 'hidden');
            filterSidebar('');
            input.focus();
        });
    })();

    (function () {
        const btn = document.getElementById('navBellBtn');
        const drop = document.getElementById('navBellDrop');
        const list = document.getElementById('navBellList');
        const badge = document.getElementById('navBellBadge');
        const clearBtn = document.getElementById('navBellClear');
        const audio = document.getElementById('orderAlertSound');
        if (!btn || !drop || !list) return;

        const pollUrl = @json(route('admin.orders.notifications', [], false));
        const seenUrl = @json(route('admin.orders.notifications.seen', [], false));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        let knownIds = new Set();
        let primed = false;
        let audioReady = false;
        let audioCtx = null;
        let ringingTimer = null;

        function csrfHeaders() {
            return {
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            };
        }

        function unlockAudio() {
            if (audioReady) return;
            audioReady = true;
            try {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                if (audioCtx.state === 'suspended') audioCtx.resume();
            } catch (e) {}
            if (audio) {
                audio.volume = 1;
                audio.muted = false;
                const p = audio.play();
                if (p && p.then) {
                    p.then(function () {
                        audio.pause();
                        audio.currentTime = 0;
                    }).catch(function () {});
                }
            }
        }

        document.addEventListener('click', unlockAudio, { once: true });
        document.addEventListener('keydown', unlockAudio, { once: true });
        btn.addEventListener('click', unlockAudio);

        function playTone(freq, start, dur, vol) {
            if (!audioCtx) return;
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'square';
            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.0001, start);
            gain.gain.exponentialRampToValueAtTime(vol, start + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, start + dur);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start(start);
            osc.stop(start + dur + 0.02);
        }

        function playFullAlert() {
            if (audio) {
                audio.volume = 1;
                audio.muted = false;
                audio.currentTime = 0;
                const playPromise = audio.play();
                if (playPromise && playPromise.catch) playPromise.catch(function () {});
            }
            if (audioCtx) {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const now = audioCtx.currentTime;
                for (let i = 0; i < 6; i++) {
                    const t = now + (i * 0.55);
                    playTone(880, t, 0.22, 0.95);
                    playTone(1174, t, 0.22, 0.7);
                    playTone(698, t + 0.24, 0.28, 0.95);
                    playTone(523, t + 0.24, 0.28, 0.55);
                }
            }
            if (navigator.vibrate) navigator.vibrate([200, 80, 220, 80, 400, 80, 500]);
            btn.classList.add('ringing');
            clearTimeout(ringingTimer);
            ringingTimer = setTimeout(function () { btn.classList.remove('ringing'); }, 3800);
        }

        function toast(order) {
            let el = document.getElementById('orderAlertToast');
            if (!el) {
                el = document.createElement('div');
                el.id = 'orderAlertToast';
                el.className = 'order-alert-toast';
                document.body.appendChild(el);
            }
            el.innerHTML = '<b>Naya order aaya</b><p>' +
                escapeHtml(order.order_number) + ' · ₹' + Number(order.total).toFixed(0) +
                ' · ' + escapeHtml(order.customer) + '</p>';
            el.classList.add('show');
            el.onclick = function () { viewOrder(order); };
            setTimeout(function () { el.classList.remove('show'); }, 12000);
        }

        function escapeHtml(s) {
            return String(s || '').replace(/[&<>"']/g, function (c) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
            });
        }

        function render(orders) {
            const count = orders.length;
            if (count) {
                badge.hidden = false;
                badge.textContent = count > 99 ? '99+' : String(count);
            } else {
                badge.hidden = true;
            }
            if (!count) {
                list.innerHTML = '<div class="nav-bell-empty">Koi nayi order nahi</div>';
                return;
            }
            list.innerHTML = orders.map(function (o) {
                return '<button type="button" class="nav-bell-item" data-id="' + o.id + '">' +
                    '<span class="nav-bell-item-ico"><i class="bi bi-bag-check-fill"></i></span>' +
                    '<span><strong>' + escapeHtml(o.order_number) + ' · ₹' + Number(o.total).toFixed(0) + '</strong>' +
                    '<span>' + escapeHtml(o.customer) + (o.phone ? ' · ' + escapeHtml(o.phone) : '') + '</span>' +
                    '<span>' + escapeHtml(o.created_at || '') + ' · View</span></span></button>';
            }).join('');
            list.querySelectorAll('.nav-bell-item').forEach(function (item) {
                item.addEventListener('click', function () {
                    const id = Number(item.getAttribute('data-id'));
                    const order = orders.find(function (o) { return o.id === id; });
                    if (order) viewOrder(order);
                });
            });
        }

        function markSeen(payload) {
            return fetch(seenUrl, {
                method: 'POST',
                headers: csrfHeaders(),
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
        }

        function viewOrder(order) {
            markSeen({ ids: [order.id] }).finally(function () {
                window.location.href = order.url;
            });
        }

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            document.getElementById('adminDropdown')?.classList.remove('open');
            const isHidden = drop.hasAttribute('hidden');
            if (isHidden) drop.removeAttribute('hidden');
            else drop.setAttribute('hidden', 'hidden');
        });

        clearBtn?.addEventListener('click', function (e) {
            e.stopPropagation();
            markSeen({ all: true }).then(function () {
                knownIds = new Set();
                render([]);
                drop.setAttribute('hidden', 'hidden');
                document.getElementById('orderAlertToast')?.classList.remove('show');
                poll();
            });
        });

        function poll() {
            fetch(pollUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    const orders = Array.isArray(data.orders) ? data.orders : [];
                    const ids = orders.map(function (o) { return o.id; });
                    const fresh = ids.filter(function (id) { return !knownIds.has(id); });
                    render(orders);
                    if (primed && fresh.length) {
                        playFullAlert();
                        toast(orders.find(function (o) { return o.id === fresh[0]; }) || orders[0]);
                    }
                    knownIds = new Set(ids);
                    primed = true;
                })
                .catch(function () {});
        }

        poll();
        setInterval(poll, 6000);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) poll();
        });
    })();
</script>
