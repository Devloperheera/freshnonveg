@extends('Admin.Layout.master')

@section('title', 'Wallet — FreshNonVeg Admin')

@section('custom_css')
<style>
.wal-cards { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
@media (max-width: 1200px) { .wal-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 720px) { .wal-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.wal-card {
    display: block; text-decoration: none; color: inherit;
    border-radius: 14px; padding: 14px 16px; min-height: 108px;
    border: 1px solid transparent; box-shadow: 0 6px 16px rgba(15, 23, 42, .08);
    transition: transform .15s, box-shadow .15s, outline-color .15s; cursor: pointer;
}
.wal-card:hover { transform: translateY(-3px); box-shadow: 0 10px 22px rgba(15, 23, 42, .14); color: inherit; }
.wal-card.active { outline: 3px solid rgba(15, 23, 42, .18); outline-offset: 1px; }
.wal-card .ico { width: 32px; height: 32px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; margin-bottom: 8px; }
.wal-card .lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; opacity: .85; }
.wal-card .val { font-size: 1.35rem; font-weight: 800; line-height: 1.15; margin-top: 2px; }
.wal-card .sub { font-size: 11px; opacity: .8; margin-top: 2px; }
.wal-c1 { background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-color: #6ee7b7; color: #065f46; }
.wal-c1 .ico { background: #2BAE49; color: #fff; }
.wal-c2 { background: linear-gradient(135deg, #fff7ed, #ffedd5); border-color: #fdba74; color: #9a3412; }
.wal-c2 .ico { background: #F57C00; color: #fff; }
.wal-c3 { background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #93c5fd; color: #1e3a8a; }
.wal-c3 .ico { background: #2563eb; color: #fff; }
.wal-c4 { background: linear-gradient(135deg, #fef2f2, #fee2e2); border-color: #fca5a5; color: #991b1b; }
.wal-c4 .ico { background: #D32F2F; color: #fff; }
.wal-c5 { background: linear-gradient(135deg, #f5f3ff, #ede9fe); border-color: #c4b5fd; color: #5b21b6; }
.wal-c5 .ico { background: #7c3aed; color: #fff; }
.wal-filter { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; }
.wal-filter .form-label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; }
.wal-presets { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
.wal-presets a { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; border: 1px solid #cbd5e1; color: #334155; text-decoration: none; background: #fff; }
.wal-presets a.active, .wal-presets a:hover { background: #2BAE49; border-color: #2BAE49; color: #fff; }
.wal-credit { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; margin-bottom: 16px; }
.wal-picker { position: relative; }
.wal-picker-list {
    position: absolute; z-index: 20; left: 0; right: 0; top: 100%;
    background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; margin-top: 4px;
    max-height: 280px; overflow: auto; box-shadow: 0 12px 28px rgba(15,23,42,.12); display: none;
}
.wal-picker-item { display: flex; gap: 10px; align-items: center; padding: 8px 10px; cursor: pointer; border-bottom: 1px solid #f1f5f9; }
.wal-picker-item:hover { background: #f0fdf4; }
.wal-av { width: 36px; height: 36px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg,#F57C00,#D32F2F); color: #fff; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; }
.wal-av img { width: 100%; height: 100%; object-fit: cover; }
.wal-chip { display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #86efac; border-radius: 10px; padding: 6px 10px; }
.wal-table { font-size: 12px; }
.wal-table thead th { font-size: 10px; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
</style>
@endsection

@section('content')
@php
    $cardQs = fn (array $extra) => array_filter(array_merge(request()->except(['page', 'source', 'view']), $extra), fn ($v) => $v !== null && $v !== '');
    $activeSource = $view === 'balances' ? 'balances' : $filters['source'];
@endphp
<div class="container-fluid mt-3 mb-4">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="section-title mb-0 border-0 pb-0"><i class="fas fa-wallet me-2"></i> Wallet</h2>
            <p class="small text-muted mb-0 mt-1">Transactions yahan. Amount add karne ke liye alag page kholo — call wali complaint ke liye bhi.</p>
        </div>
        <a href="{{ route('admin.wallet.credit', [], false) }}" class="btn btn-success btn-sm"><i class="fas fa-plus me-1"></i> Amount add karein</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="wal-cards">
        <a href="{{ route('admin.wallet.index', $cardQs(['source' => 'credits']), false) }}" class="wal-card wal-c1 {{ $activeSource === 'credits' ? 'active' : '' }}">
            <div class="ico"><i class="fas fa-arrow-down"></i></div>
            <div class="lbl">Aaj tak add hua</div>
            <div class="val">₹{{ number_format($cards['added'], 2) }}</div>
            <div class="sub">Credits + refunds</div>
        </a>
        <a href="{{ route('admin.wallet.index', $cardQs(['source' => 'used']), false) }}" class="wal-card wal-c2 {{ $activeSource === 'used' ? 'active' : '' }}">
            <div class="ico"><i class="fas fa-users"></i></div>
            <div class="lbl">Kitne logo ne use kiya</div>
            <div class="val">{{ number_format($cards['users_used']) }}</div>
            <div class="sub">₹{{ number_format($cards['used'], 2) }} orders par kharch</div>
        </a>
        <a href="{{ route('admin.wallet.index', $cardQs(['view' => 'balances']), false) }}" class="wal-card wal-c3 {{ $activeSource === 'balances' ? 'active' : '' }}">
            <div class="ico"><i class="fas fa-piggy-bank"></i></div>
            <div class="lbl">Users ke pass bacha</div>
            <div class="val">₹{{ number_format($cards['balance'], 2) }}</div>
            <div class="sub">{{ number_format($cards['users_with_balance']) }} customers ke wallet me</div>
        </a>
        <a href="{{ route('admin.wallet.index', $cardQs(['source' => 'direct']), false) }}" class="wal-card wal-c4 {{ $activeSource === 'direct' ? 'active' : '' }}">
            <div class="ico"><i class="fas fa-hand-holding-dollar"></i></div>
            <div class="lbl">Direct amount diya</div>
            <div class="val">₹{{ number_format($cards['direct'], 2) }}</div>
            <div class="sub">Admin ne khud credit kiya</div>
        </a>
        <a href="{{ route('admin.wallet.index', $cardQs(['source' => 'complaint']), false) }}" class="wal-card wal-c5 {{ $activeSource === 'complaint' ? 'active' : '' }}">
            <div class="ico"><i class="fas fa-comment-dots"></i></div>
            <div class="lbl">Complaints ke through</div>
            <div class="val">₹{{ number_format($cards['complaint'], 2) }}</div>
            <div class="sub">Compensation credits</div>
        </a>
    </div>

    <div class="table-container">
        <form method="GET" action="{{ route('admin.wallet.index', [], false) }}" class="wal-filter">
            <input type="hidden" name="view" value="{{ $view }}">
            <input type="hidden" name="source" value="{{ $filters['source'] }}">
            <input type="hidden" name="preset" id="presetInput" value="{{ $filters['preset'] }}">
            @if($filters['user_id'] !== '')<input type="hidden" name="user_id" value="{{ $filters['user_id'] }}">@endif

            <div class="wal-presets">
                @foreach(\App\Http\Controllers\Admin\WalletController::DATE_PRESETS as $key => $label)
                    <a href="{{ route('admin.wallet.index', array_merge(request()->except(['page', 'date_from', 'date_to']), ['preset' => $key]), false) }}"
                       class="{{ $filters['preset'] === $key ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
                @if($filters['preset'] !== '')
                    <a href="{{ route('admin.wallet.index', request()->except(['page', 'preset']), false) }}">✕ Preset hatao</a>
                @endif
            </div>

            <div class="row g-2 align-items-end">
                <div class="col-lg-3">
                    <label class="form-label">Search</label>
                    <input type="search" name="q" class="form-control form-control-sm" value="{{ $filters['q'] }}" placeholder="Naam, phone, email, order no, note">
                </div>
                <div class="col-lg-2">
                    <label class="form-label">Source</label>
                    <select name="source" class="form-select form-select-sm" onchange="this.form.view.value='transactions'; this.form.submit()">
                        <option value="">All transactions</option>
                        @foreach(\App\Models\WalletTransaction::SOURCES as $key => $label)
                            <option value="{{ $key }}" @selected($filters['source'] === $key)>{{ $label }}</option>
                        @endforeach
                        <option value="credits" @selected($filters['source'] === 'credits')>All credits</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm js-date" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-lg-2">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm js-date" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-lg-1">
                    <label class="form-label">Min ₹</label>
                    <input type="number" step="0.01" min="0" name="amount_min" class="form-control form-control-sm" value="{{ $filters['amount_min'] }}">
                </div>
                <div class="col-lg-1">
                    <label class="form-label">Max ₹</label>
                    <input type="number" step="0.01" min="0" name="amount_max" class="form-control form-control-sm" value="{{ $filters['amount_max'] }}">
                </div>
                <div class="col-lg-1">
                    <label class="form-label">Per page</label>
                    <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(\App\Http\Controllers\Admin\WalletController::PER_PAGE_OPTIONS as $opt)
                            <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter me-1"></i> Apply</button>
                    <a href="{{ route('admin.wallet.index', [], false) }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
                </div>
            </div>
            @if($selectedUser)
                <div class="mt-2 small">Filtered user: <strong>{{ $selectedUser->name }}</strong> ({{ $selectedUser->phone }})
                    <a href="{{ route('admin.wallet.index', request()->except(['user_id', 'page']), false) }}">clear</a>
                </div>
            @endif
        </form>

        @if($view === 'balances')
            <div class="small text-muted mb-2">Jin customers ke wallet me abhi balance bacha hai.</div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover align-middle mb-0 wal-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Current balance</th>
                            <th>Total credited</th>
                            <th>Total used</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($balances as $user)
                            <tr>
                                <td>@include('Admin.partials.user-cell', ['user' => $user])</td>
                                <td class="fw-bold text-success">₹{{ number_format((float) $user->wallet_balance, 2) }}</td>
                                <td>₹{{ number_format((float) $user->credited_sum, 2) }}</td>
                                <td>₹{{ number_format((float) $user->used_sum, 2) }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.wallet.index', ['user_id' => $user->id], false) }}" class="btn btn-sm btn-outline-success py-0">Transactions</a>
                                    <a href="{{ route('admin.wallet.credit', ['user' => $user->id], false) }}" class="btn btn-sm btn-success py-0">Add</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">Kisi ke wallet me balance nahi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($balances->hasPages())<div class="mt-3">{{ $balances->links() }}</div>@endif
        @else
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-2 small text-muted">
                <div>Showing <strong>{{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }}</strong> of <strong>{{ $transactions->total() }}</strong> transactions</div>
                @if($filteredTotals)
                    <div>In this filter: credit ₹{{ number_format($filteredTotals['credit'], 2) }} · used ₹{{ number_format($filteredTotals['debit'], 2) }}</div>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover align-middle mb-0 wal-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>When</th>
                            <th>Customer</th>
                            <th>Source</th>
                            <th>Amount</th>
                            <th>Balance after</th>
                            <th>Details</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                            @php
                                $src = $txn->source();
                                $badge = match ($src) {
                                    'direct' => 'bg-danger',
                                    'complaint' => 'bg-primary',
                                    'refund' => 'bg-warning text-dark',
                                    'used' => 'bg-secondary',
                                    default => 'bg-light text-dark',
                                };
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $txn->id }}</td>
                                <td class="text-nowrap small">{{ $txn->created_at?->format('d M Y') }}<br><span class="text-muted">{{ $txn->created_at?->format('h:i A') }}</span></td>
                                <td>@include('Admin.partials.user-cell', ['user' => $txn->user])</td>
                                <td><span class="badge {{ $badge }}">{{ \App\Models\WalletTransaction::SOURCES[$src] ?? $txn->type }}</span></td>
                                <td class="fw-bold {{ $txn->isCredit() ? 'text-success' : 'text-danger' }}">
                                    {{ $txn->isCredit() ? '+' : '−' }}₹{{ number_format((float) $txn->amount, 2) }}
                                </td>
                                <td>₹{{ number_format((float) $txn->balance_after, 2) }}</td>
                                <td>
                                    <div>{{ $txn->description }}</div>
                                    @if($txn->complaint)
                                        <a href="{{ route('admin.complaints.show', $txn->complaint, false) }}" class="small">Complaint #{{ $txn->complaint->id }}</a>
                                    @endif
                                    @if($txn->order)
                                        <a href="{{ route('admin.orders.show', $txn->order, false) }}" class="small d-block">{{ $txn->order->order_number }}</a>
                                    @endif
                                </td>
                                <td class="small">{{ $txn->admin?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">Koi wallet transaction nahi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())<div class="mt-3">{{ $transactions->links() }}</div>@endif
        @endif
    </div>
</div>
@endsection

@section('custom_js')
<script>
document.querySelectorAll('.js-date').forEach(function (el) {
    el.addEventListener('change', function () { document.getElementById('presetInput').value = ''; });
});
</script>
@endsection
