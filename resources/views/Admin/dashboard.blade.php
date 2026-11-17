@extends('Admin.Layout.master')

@section('title', 'Dashboard — FreshNonVeg Admin')

@section('custom_css')
<style>
.dash-cards { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
@media (max-width: 1400px) { .dash-cards { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
@media (max-width: 992px) { .dash-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.dash-card {
    display: block; text-decoration: none; color: inherit;
    border-radius: 16px; padding: 14px 16px; min-height: 112px;
    border: 1px solid transparent; box-shadow: 0 8px 18px rgba(15,23,42,.08);
    transition: transform .15s, box-shadow .15s;
}
.dash-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(15,23,42,.14); color: inherit; }
.dash-card .ico { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 16px; margin-bottom: 8px; }
.dash-card .lbl { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; opacity: .8; }
.dash-card .val { font-size: 1.4rem; font-weight: 800; line-height: 1.15; margin-top: 2px; }
.dash-card .sub { font-size: 11px; opacity: .75; margin-top: 3px; }
.tone-green { background: linear-gradient(135deg,#ecfdf5,#d1fae5); border-color:#6ee7b7; color:#065f46; }
.tone-green .ico { background:#2BAE49; }
.tone-orange { background: linear-gradient(135deg,#fff7ed,#ffedd5); border-color:#fdba74; color:#9a3412; }
.tone-orange .ico { background:#F57C00; }
.tone-blue { background: linear-gradient(135deg,#eff6ff,#dbeafe); border-color:#93c5fd; color:#1e3a8a; }
.tone-blue .ico { background:#2563eb; }
.tone-red { background: linear-gradient(135deg,#fef2f2,#fee2e2); border-color:#fca5a5; color:#991b1b; }
.tone-red .ico { background:#D32F2F; }
.tone-purple { background: linear-gradient(135deg,#f5f3ff,#ede9fe); border-color:#c4b5fd; color:#5b21b6; }
.tone-purple .ico { background:#7c3aed; }
.tone-pink { background: linear-gradient(135deg,#fdf2f8,#fce7f3); border-color:#f9a8d4; color:#9d174d; }
.tone-pink .ico { background:#db2777; }
.tone-teal { background: linear-gradient(135deg,#f0fdfa,#ccfbf1); border-color:#5eead4; color:#115e59; }
.tone-teal .ico { background:#0d9488; }
.tone-rose { background: linear-gradient(135deg,#fff1f2,#ffe4e6); border-color:#fda4af; color:#9f1239; }
.tone-rose .ico { background:#e11d48; }
.tone-gold { background: linear-gradient(135deg,#fffbeb,#fef3c7); border-color:#fcd34d; color:#92400e; }
.tone-gold .ico { background:#d97706; }
.tone-lime { background: linear-gradient(135deg,#f7fee7,#ecfccb); border-color:#bef264; color:#3f6212; }
.tone-lime .ico { background:#65a30d; }
.dash-graphs { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
@media (max-width: 1200px) { .dash-graphs { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .dash-graphs { grid-template-columns: 1fr; } }
.dash-g { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:12px 14px 8px; box-shadow:0 6px 14px rgba(15,23,42,.06); }
.dash-g h6 { font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; color:#334155; margin:0 0 8px; }
.dash-g canvas { width:100% !important; height:220px !important; }
.dash-recent { background:#fff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden; }
.dash-recent h6 { font-size:12px; font-weight:800; text-transform:uppercase; padding:12px 14px; margin:0; border-bottom:1px solid #e2e8f0; background:#f8fafc; }

/* Amount hide / show */
.amt-eye {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px; border-radius: 7px; border: 0; padding: 0;
    background: rgba(255,255,255,.65); color: inherit; cursor: pointer;
    font-size: 13px; vertical-align: middle; line-height: 1; opacity: .8;
    transition: opacity .15s, background .15s;
}
.amt-eye:hover, .amt-eye:focus-visible { opacity: 1; background: rgba(255,255,255,.95); outline: none; box-shadow: 0 0 0 2px rgba(15,23,42,.15); }
.dash-card .amt-eye { position: absolute; top: 12px; right: 12px; }
.dash-card { position: relative; }
.dash-recent th .amt-eye, .dash-g h6 .amt-eye { background: #f1f5f9; margin-left: 4px; }
.amt-mask { display: none; letter-spacing: .12em; }
[data-amt].is-hidden .amt-real { display: none; }
[data-amt].is-hidden .amt-mask { display: inline; }
.dash-g canvas { transition: filter .2s; }
.dash-g.is-hidden canvas { filter: blur(9px); pointer-events: none; user-select: none; }
.amt-toggle-all { font-size: 12px; font-weight: 700; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="section-title mb-0 border-0 pb-0"><i class="fas fa-chart-line me-2"></i> Dashboard</h2>
            <p class="small text-muted mb-0 mt-1">Cards clickable hain — graph last 14 din ka live data hai.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-secondary amt-toggle-all" id="amtToggleAll"
                    aria-pressed="false">
                <i class="bi bi-eye-slash me-1"></i><span>Saare amount hide karo</span>
            </button>
            <span class="small text-muted">{{ now()->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</span>
        </div>
    </div>

    <div class="dash-cards">
        @foreach($cards as $card)
            @php
                $isMoney = str_starts_with((string) $card['value'], '₹');
                $subIsMoney = str_starts_with((string) $card['sub'], '₹');
                $amtKey = 'card-'.\Illuminate\Support\Str::slug($card['label']);
            @endphp
            <a href="{{ $card['url'] }}" class="dash-card tone-{{ $card['tone'] }}"
               @if($isMoney || $subIsMoney) data-amt="{{ $amtKey }}" @endif>
                @if($isMoney || $subIsMoney)
                    <button type="button" class="amt-eye" data-amt-toggle="{{ $amtKey }}"
                            title="Amount hide / show" aria-label="{{ $card['label'] }} amount hide karo">
                        <i class="bi bi-eye"></i>
                    </button>
                @endif
                <div class="ico"><i class="bi {{ $card['icon'] }}"></i></div>
                <div class="lbl">{{ $card['label'] }}</div>
                <div class="val">
                    @if($isMoney)
                        <span class="amt-real">{{ $card['value'] }}</span><span class="amt-mask">₹ ••••</span>
                    @else
                        {{ $card['value'] }}
                    @endif
                </div>
                <div class="sub">
                    @if($subIsMoney)
                        @php [$subAmt, $subRest] = array_pad(explode(' ', (string) $card['sub'], 2), 2, ''); @endphp
                        <span class="amt-real">{{ $subAmt }}</span><span class="amt-mask">₹ ••</span> {{ $subRest }}
                    @else
                        {{ $card['sub'] }}
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    <div class="dash-graphs">
        <div class="dash-g">
            <h6>Orders · 14 days</h6>
            <canvas id="chartOrders"></canvas>
        </div>
        <div class="dash-g" data-amt="chart-revenue">
            <h6 class="d-flex justify-content-between align-items-center">
                <span>Revenue · 14 days</span>
                <button type="button" class="amt-eye" data-amt-toggle="chart-revenue"
                        title="Amount hide / show" aria-label="Revenue graph hide karo">
                    <i class="bi bi-eye"></i>
                </button>
            </h6>
            <canvas id="chartRevenue"></canvas>
        </div>
        <div class="dash-g">
            <h6>Order status</h6>
            <canvas id="chartStatus"></canvas>
        </div>
        <div class="dash-g">
            <h6>New users · 14 days</h6>
            <canvas id="chartUsers"></canvas>
        </div>
    </div>

    <div class="dash-recent">
        <h6 class="d-flex justify-content-between align-items-center">
            <span>Latest orders</span>
            <a href="{{ route('admin.orders.index', [], false) }}" class="small">View all</a>
        </h6>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0" data-amt="recent-orders">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>
                            Amount
                            <button type="button" class="amt-eye" data-amt-toggle="recent-orders"
                                    title="Amount hide / show" aria-label="Order amounts hide karo">
                                <i class="bi bi-eye"></i>
                            </button>
                        </th>
                        <th>Status</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order, false) }}" class="fw-semibold text-success">{{ $order->order_number }}</a></td>
                            <td>{{ $order->user?->name ?? '—' }}<div class="small text-muted">{{ $order->user?->phone }}</div></td>
                            <td class="fw-bold"><span class="amt-real">₹{{ number_format((float) $order->total, 2) }}</span><span class="amt-mask">₹ ••••</span></td>
                            <td><span class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', $order->status) }}</span></td>
                            <td class="small text-muted">{{ $order->created_at?->format('d M, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-3 text-muted">Abhi koi order nahi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const data = @json($charts);
    const grid = { color: 'rgba(148,163,184,.25)' };
    Chart.defaults.font.family = 'system-ui, Segoe UI, sans-serif';
    Chart.defaults.font.size = 11;

    new Chart(document.getElementById('chartOrders'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Orders',
                data: data.orders,
                borderColor: '#2BAE49',
                backgroundColor: 'rgba(43,174,73,.18)',
                fill: true, tension: .35, pointRadius: 3, borderWidth: 2
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid }, x: { grid: { display: false } } }, maintainAspectRatio: false }
    });

    new Chart(document.getElementById('chartRevenue'), {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Revenue',
                data: data.revenue,
                backgroundColor: '#F57C00',
                borderRadius: 6,
                maxBarThickness: 18
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid }, x: { grid: { display: false } } }, maintainAspectRatio: false }
    });

    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: data.status.labels,
            datasets: [{
                data: data.status.data,
                backgroundColor: ['#F57C00', '#2BAE49', '#2563eb', '#D32F2F', '#64748b'],
                borderWidth: 0
            }]
        },
        options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8 } } }, cutout: '58%', maintainAspectRatio: false }
    });

    new Chart(document.getElementById('chartUsers'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Users',
                data: data.users,
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124,58,237,.16)',
                fill: true, tension: .35, pointRadius: 3, borderWidth: 2
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid }, x: { grid: { display: false } } }, maintainAspectRatio: false }
    });
})();

/* ---------- Amount hide / show (eye icon) ---------- */
(function () {
    var STORE = 'fnv_admin_dash_hidden_amounts';
    var groups = Array.prototype.slice.call(document.querySelectorAll('[data-amt]'));
    var allBtn = document.getElementById('amtToggleAll');
    var state = {};

    try { state = JSON.parse(localStorage.getItem(STORE) || '{}') || {}; } catch (e) { state = {}; }

    function save() {
        try { localStorage.setItem(STORE, JSON.stringify(state)); } catch (e) {}
    }

    function apply(key) {
        var hidden = !!state[key];
        groups.forEach(function (el) {
            if (el.getAttribute('data-amt') === key) el.classList.toggle('is-hidden', hidden);
        });
        document.querySelectorAll('[data-amt-toggle="' + key + '"]').forEach(function (btn) {
            var icon = btn.querySelector('i');
            if (icon) icon.className = hidden ? 'bi bi-eye-slash' : 'bi bi-eye';
            btn.setAttribute('aria-pressed', hidden ? 'true' : 'false');
            btn.title = hidden ? 'Amount dikhao' : 'Amount hide karo';
        });
    }

    function keys() {
        return groups.map(function (el) { return el.getAttribute('data-amt'); });
    }

    function syncAllBtn() {
        if (!allBtn) return;
        var allHidden = keys().length > 0 && keys().every(function (k) { return !!state[k]; });
        allBtn.setAttribute('aria-pressed', allHidden ? 'true' : 'false');
        allBtn.querySelector('i').className = (allHidden ? 'bi bi-eye' : 'bi bi-eye-slash') + ' me-1';
        allBtn.querySelector('span').textContent = allHidden ? 'Saare amount dikhao' : 'Saare amount hide karo';
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-amt-toggle]');
        if (!btn) return;
        // Card ek link hai — eye dabane par page na khule
        e.preventDefault();
        e.stopPropagation();
        var key = btn.getAttribute('data-amt-toggle');
        state[key] = !state[key];
        apply(key);
        save();
        syncAllBtn();
    }, true);

    if (allBtn) {
        allBtn.addEventListener('click', function () {
            var hide = allBtn.getAttribute('aria-pressed') !== 'true';
            keys().forEach(function (k) { state[k] = hide; apply(k); });
            save();
            syncAllBtn();
        });
    }

    keys().forEach(apply);
    syncAllBtn();
})();
</script>
@endsection
