@extends('Admin.Layout.master')

@section('title', 'Complaints — FreshNonVeg Admin')

@section('custom_css')
@include('Admin.partials.catalog-list-styles')
<style>
.cmp-stats { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
@media (max-width: 1200px) { .cmp-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 640px) { .cmp-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.cmp-stat {
    background: #fff; border-radius: 10px; padding: 10px 14px; border-left: 4px solid #64748b;
    box-shadow: 0 3px 8px rgba(30,142,62,.08); text-decoration: none; color: inherit; display: block;
}
.cmp-stat:hover { transform: translateY(-1px); }
.cmp-stat.active { box-shadow: 0 0 0 2px #2BAE49 inset, 0 3px 8px rgba(30,142,62,.08); }
.cmp-stat.green { border-left-color: #2BAE49; }
.cmp-stat.orange { border-left-color: #F57C00; }
.cmp-stat.blue { border-left-color: #1565C0; }
.cmp-stat.red { border-left-color: #D32F2F; }
.cmp-stat .label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: .04em; }
.cmp-stat .value { font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
.cmp-filter { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 12px; }
.cmp-filter .form-label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; }
.cmp-presets { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
.cmp-presets a { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; border: 1px solid #cbd5e1; color: #334155; text-decoration: none; background: #fff; }
.cmp-presets a.active, .cmp-presets a:hover { background: #2BAE49; border-color: #2BAE49; color: #fff; }
.cmp-meta { font-size: 11px; color: #64748b; }
</style>
@endsection

@section('content')
@php
    $baseParams = request()->except(['page']);
    $statusUrl = fn (?string $status) => route('admin.complaints.index', $status
        ? array_merge($baseParams, ['status' => $status])
        : request()->except(['page', 'status']), false);
@endphp
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="section-title mb-0 border-0 pb-0">
                <i class="fas fa-comment-dots me-2"></i> Customer Complaints
            </h2>
            <p class="small text-muted mb-0 mt-1">Reply email par jayega. Compensation wallet me turant credit hoga.</p>
        </div>
    </div>

    <div class="cmp-stats">
        <a href="{{ $statusUrl(null) }}" class="cmp-stat green {{ $filters['status'] === '' ? 'active' : '' }}">
            <div class="label">All</div>
            <div class="value">{{ number_format($summary['all']) }}</div>
        </a>
        <a href="{{ $statusUrl('open') }}" class="cmp-stat orange {{ $filters['status'] === 'open' ? 'active' : '' }}">
            <div class="label">Open</div>
            <div class="value">{{ number_format($summary['open']) }}</div>
        </a>
        <a href="{{ $statusUrl('in_progress') }}" class="cmp-stat blue {{ $filters['status'] === 'in_progress' ? 'active' : '' }}">
            <div class="label">In progress</div>
            <div class="value">{{ number_format($summary['in_progress']) }}</div>
        </a>
        <a href="{{ $statusUrl('resolved') }}" class="cmp-stat green {{ $filters['status'] === 'resolved' ? 'active' : '' }}">
            <div class="label">Resolved</div>
            <div class="value">{{ number_format($summary['resolved']) }}</div>
        </a>
        <a href="{{ $statusUrl('rejected') }}" class="cmp-stat red {{ $filters['status'] === 'rejected' ? 'active' : '' }}">
            <div class="label">Rejected</div>
            <div class="value">{{ number_format($summary['rejected']) }}</div>
        </a>
        <a href="{{ route('admin.complaints.index', array_merge($baseParams, ['credited' => 'yes']), false) }}"
           class="cmp-stat orange {{ $filters['credited'] === 'yes' ? 'active' : '' }}">
            <div class="label">Wallet credited</div>
            <div class="value">₹{{ number_format($summary['credited'], 0) }}</div>
        </a>
    </div>

    <div class="table-container">
        <form method="GET" action="{{ route('admin.complaints.index', [], false) }}" class="cmp-filter" id="complaintsFilterForm">
            <div class="cmp-presets">
                @foreach(\App\Http\Controllers\Admin\ComplaintController::DATE_PRESETS as $key => $label)
                    <a href="{{ route('admin.complaints.index', array_merge(request()->except(['page', 'date_from', 'date_to']), ['preset' => $key]), false) }}"
                       class="{{ $filters['preset'] === $key ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
                @if($filters['preset'] !== '')
                    <a href="{{ route('admin.complaints.index', request()->except(['page', 'preset']), false) }}">✕ Preset hatao</a>
                @endif
            </div>
            <input type="hidden" name="preset" value="{{ $filters['preset'] }}" id="presetInput">
            @if($filters['user_id'] !== '')<input type="hidden" name="user_id" value="{{ $filters['user_id'] }}">@endif

            <div class="row g-2 align-items-end">
                <div class="col-xl-3 col-md-4">
                    <label class="form-label" for="q">Search</label>
                    <input type="search" name="q" id="q" class="form-control form-control-sm" value="{{ $filters['q'] }}"
                           placeholder="Subject, message, naam, phone, email, order no">
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Models\Complaint::STATUSES as $key => $label)
                            <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="date_field">Date kis par</label>
                    <select name="date_field" id="date_field" class="form-select form-select-sm">
                        <option value="created" @selected($filters['date_field'] === 'created')>Created date</option>
                        <option value="resolved" @selected($filters['date_field'] === 'resolved')>Resolved date</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="date_from">From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control form-control-sm js-date" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="date_to">To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control form-control-sm js-date" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="waiting">Waiting / no reply</label>
                    <select name="waiting" id="waiting" class="form-select form-select-sm">
                        <option value="">Any</option>
                        @foreach(\App\Http\Controllers\Admin\ComplaintController::WAITING_OPTIONS as $key => $label)
                            <option value="{{ $key }}" @selected($filters['waiting'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="has_order">Linked order</label>
                    <select name="has_order" id="has_order" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="yes" @selected($filters['has_order'] === 'yes')>With order</option>
                        <option value="no" @selected($filters['has_order'] === 'no')>No order</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="has_reply">Admin reply</label>
                    <select name="has_reply" id="has_reply" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="yes" @selected($filters['has_reply'] === 'yes')>Replied</option>
                        <option value="no" @selected($filters['has_reply'] === 'no')>No reply</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="emailed">Reply email</label>
                    <select name="emailed" id="emailed" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="sent" @selected($filters['emailed'] === 'sent')>Sent</option>
                        <option value="pending" @selected($filters['emailed'] === 'pending')>Pending</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="credited">Wallet credit</label>
                    <select name="credited" id="credited" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="yes" @selected($filters['credited'] === 'yes')>Credited</option>
                        <option value="no" @selected($filters['credited'] === 'no')>Not credited</option>
                    </select>
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="amount_min">Min ₹</label>
                    <input type="number" step="0.01" min="0" name="amount_min" id="amount_min" class="form-control form-control-sm" value="{{ $filters['amount_min'] }}">
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="amount_max">Max ₹</label>
                    <input type="number" step="0.01" min="0" name="amount_max" id="amount_max" class="form-control form-control-sm" value="{{ $filters['amount_max'] }}">
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="user_status">Customer status</label>
                    <select name="user_status" id="user_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" @selected($filters['user_status'] === 'active')>Active</option>
                        <option value="inactive" @selected($filters['user_status'] === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="sort">Sort</label>
                    <select name="sort" id="sort" class="form-select form-select-sm">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest first</option>
                        <option value="waiting" @selected($filters['sort'] === 'waiting')>Waiting longest</option>
                        <option value="credited_high" @selected($filters['sort'] === 'credited_high')>Credit high → low</option>
                    </select>
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="per_page">Per page</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(\App\Http\Controllers\Admin\ComplaintController::PER_PAGE_OPTIONS as $opt)
                            <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter me-1"></i> Apply</button>
                    <a href="{{ route('admin.complaints.index', [], false) }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
                </div>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div class="cmp-meta">
                Showing <strong>{{ $complaints->firstItem() ?? 0 }}–{{ $complaints->lastItem() ?? 0 }}</strong>
                of <strong>{{ $complaints->total() }}</strong> complaints
                @if($filters['user_id'] !== '')
                    · User #{{ $filters['user_id'] }}
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0 adm-catalog-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Subject</th>
                        <th>Order</th>
                        <th>Wallet</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $complaint)
                        <tr>
                            <td class="text-muted">{{ $complaint->id }}</td>
                            <td>@include('Admin.partials.user-cell', ['user' => $complaint->user])</td>
                            <td>
                                <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($complaint->subject, 60) }}</div>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($complaint->message, 70) }}</div>
                            </td>
                            <td>
                                @if($complaint->order)
                                    <a href="{{ route('admin.orders.show', $complaint->order, false) }}" class="fw-semibold text-decoration-none">
                                        {{ $complaint->order->order_number }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                ₹{{ number_format((float) $complaint->credited_amount, 2) }}
                                <div class="small text-muted">bal ₹{{ number_format((float) $complaint->user?->wallet_balance, 2) }}</div>
                            </td>
                            <td>
                                @if($complaint->reply_emailed_at)
                                    <span class="badge bg-success">Sent</span>
                                    <div class="small text-muted">{{ $complaint->reply_emailed_at->format('d M, h:i A') }}</div>
                                @else
                                    <span class="badge bg-light text-dark">Pending</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badge = match ($complaint->status) {
                                        'resolved' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'in_progress' => 'bg-warning text-dark',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $complaint->statusLabel() }}</span>
                            </td>
                            <td class="small text-nowrap">
                                {{ $complaint->created_at?->format('d M Y') }}<br>
                                <span class="text-muted">{{ $complaint->created_at?->format('h:i A') }}</span>
                            </td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['view' => route('admin.complaints.show', $complaint, false)])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">Is filter se koi complaint nahi mili.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($complaints->hasPages())
        <div class="mt-3">{{ $complaints->links() }}</div>
    @endif
</div>
@endsection

@section('custom_js')
<script>
document.querySelectorAll('.js-date').forEach(function (el) {
    el.addEventListener('change', function () { document.getElementById('presetInput').value = ''; });
});
</script>
@endsection
