@extends('Admin.Layout.master')

@section('title', 'Orders — FreshNonVeg Admin')

@section('custom_css')
<style>
.ord-stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
@media (max-width: 1200px) { .ord-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 640px) { .ord-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.ord-stat { background: #fff; border-radius: 10px; padding: 10px 14px; border-left: 4px solid #2BAE49; box-shadow: 0 3px 8px rgba(30,142,62,.08); }
.ord-stat.orange { border-left-color: #F57C00; }
.ord-stat.red { border-left-color: #D32F2F; }
.ord-stat .label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: .04em; }
.ord-stat .value { font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
.ord-filter { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 12px; }
.ord-filter .form-label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; }
.ord-presets { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
.ord-presets a { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; border: 1px solid #cbd5e1; color: #334155; text-decoration: none; background: #fff; }
.ord-presets a.active, .ord-presets a:hover { background: #2BAE49; border-color: #2BAE49; color: #fff; }
.ord-table { font-size: 12px; }
.ord-table thead th { font-size: 10px; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
.ord-thumbs { display: flex; flex-wrap: wrap; gap: 4px; max-width: 190px; }
.ord-thumb { width: 38px; height: 38px; border-radius: 6px; object-fit: cover; border: 1px solid #e2e8f0; cursor: zoom-in; transition: transform .15s; }
.ord-thumb:hover { transform: scale(1.08); border-color: #2BAE49; }
.ord-thumb-empty { width: 38px; height: 38px; border-radius: 6px; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; }
.ord-more { font-size: 10px; font-weight: 700; color: #64748b; align-self: center; }
.ord-cust-name { font-weight: 700; color: #0f172a; text-decoration: none; }
.ord-cust-name:hover { color: #2BAE49; }
.ord-meta { font-size: 11px; color: #64748b; }
#imgModal .modal-body img { max-width: 100%; max-height: 70vh; border-radius: 10px; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-shopping-bag me-2"></i> Customer Orders
        </h2>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('admin.orders.export.excel', [], false) }}{{ $exportQuery ? '?'.$exportQuery : '' }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Excel Export
            </a>
            <a href="{{ route('admin.orders.export.csv', [], false) }}{{ $exportQuery ? '?'.$exportQuery : '' }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-csv me-1"></i> CSV Export
            </a>
            <span class="ord-meta">Lagaye gaye filters export par bhi lagenge</span>
        </div>
    </div>

    <div class="ord-stats">
        <div class="ord-stat"><div class="label">Orders</div><div class="value">{{ number_format($summary['count']) }}</div></div>
        <div class="ord-stat"><div class="label">Paid revenue</div><div class="value">₹{{ number_format($summary['revenue'], 2) }}</div></div>
        <div class="ord-stat"><div class="label">Paid / confirmed</div><div class="value">{{ number_format($summary['paid']) }}</div></div>
        <div class="ord-stat orange"><div class="label">Pending / failed</div><div class="value">{{ number_format($summary['pending']) }}</div></div>
        <div class="ord-stat red"><div class="label">Cancelled</div><div class="value">{{ number_format($summary['cancelled']) }}</div></div>
    </div>

    <div class="table-container">
        <form method="GET" action="{{ route('admin.orders.index', [], false) }}" class="ord-filter" id="ordersFilterForm">
            <div class="ord-presets">
                @foreach(\App\Http\Controllers\Admin\OrderController::DATE_PRESETS as $key => $label)
                    <a href="{{ route('admin.orders.index', array_merge(request()->except(['page', 'date_from', 'date_to']), ['preset' => $key]), false) }}"
                       class="{{ $filters['preset'] === $key ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
                @if($filters['preset'] !== '')
                    <a href="{{ route('admin.orders.index', request()->except(['page', 'preset']), false) }}">✕ Preset hatao</a>
                @endif
            </div>
            <input type="hidden" name="preset" value="{{ $filters['preset'] }}" id="presetInput">
            @if($filters['user_id'] !== '')<input type="hidden" name="user_id" value="{{ $filters['user_id'] }}">@endif

            <div class="row g-2 align-items-end">
                <div class="col-xl-3 col-md-4">
                    <label class="form-label" for="q">Search</label>
                    <input type="search" name="q" id="q" class="form-control form-control-sm" value="{{ $filters['q'] }}"
                           placeholder="Order no, naam, phone, email, product, payment ID">
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach(\App\Http\Controllers\Admin\OrderController::STATUS_LABELS as $key => $label)
                            <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="delivery_type">Delivery type</label>
                    <select name="delivery_type" id="delivery_type" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="instant" @selected($filters['delivery_type'] === 'instant')>Instant</option>
                        <option value="scheduled" @selected($filters['delivery_type'] === 'scheduled')>Scheduled</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="date_field">Date kis par</label>
                    <select name="date_field" id="date_field" class="form-select form-select-sm">
                        <option value="order" @selected($filters['date_field'] === 'order')>Order date</option>
                        <option value="delivery" @selected($filters['date_field'] === 'delivery')>Delivery date</option>
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
                    <label class="form-label" for="city">City</label>
                    <select name="city" id="city" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" @selected($filters['city'] === $city)>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="pincode">Pincode</label>
                    <input type="text" name="pincode" id="pincode" maxlength="6" class="form-control form-control-sm" value="{{ $filters['pincode'] }}">
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="amount_min">Min ₹</label>
                    <input type="number" step="0.01" min="0" name="amount_min" id="amount_min" class="form-control form-control-sm" value="{{ $filters['amount_min'] }}">
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="amount_max">Max ₹</label>
                    <input type="number" step="0.01" min="0" name="amount_max" id="amount_max" class="form-control form-control-sm" value="{{ $filters['amount_max'] }}">
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="coupon">Coupon</label>
                    <select name="coupon" id="coupon" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="yes" @selected($filters['coupon'] === 'yes')>Used</option>
                        <option value="no" @selected($filters['coupon'] === 'no')>No</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="sort">Sort</label>
                    <select name="sort" id="sort" class="form-select form-select-sm">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest first</option>
                        <option value="amount_high" @selected($filters['sort'] === 'amount_high')>Amount high → low</option>
                        <option value="amount_low" @selected($filters['sort'] === 'amount_low')>Amount low → high</option>
                        <option value="delivery_date" @selected($filters['sort'] === 'delivery_date')>Delivery date</option>
                    </select>
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="per_page">Per page</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(\App\Http\Controllers\Admin\OrderController::PER_PAGE_OPTIONS as $opt)
                            <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter me-1"></i> Apply</button>
                    <a href="{{ route('admin.orders.index', [], false) }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
                </div>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div class="ord-meta">
                Showing <strong>{{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }}</strong> of <strong>{{ $orders->total() }}</strong> orders
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0 ord-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Products</th>
                        <th>Delivery</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $badge = match ($order->status) {
                                'paid', 'confirmed' => 'bg-success',
                                'cancelled' => 'bg-secondary',
                                'payment_failed' => 'bg-danger',
                                'awaiting_payment' => 'bg-warning text-dark',
                                default => 'bg-light text-dark',
                            };
                        @endphp
                        <tr>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.orders.show', $order, false) }}" class="fw-bold text-success text-decoration-none">{{ $order->order_number }}</a>
                                <div class="ord-meta">{{ $order->created_at?->format('d M Y, h:i A') }}</div>
                            </td>
                            <td>
                                @if($order->user)
                                    <a href="{{ route('admin.users.show', $order->user, false) }}" class="ord-cust-name">{{ $order->user->name }}</a>
                                    <div class="ord-meta">{{ $order->user->phone ?? $order->phone }}</div>
                                    @if($order->user->email)<div class="ord-meta">{{ $order->user->email }}</div>@endif
                                @else
                                    <span class="text-muted">Guest</span>
                                    <div class="ord-meta">{{ $order->phone }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="ord-thumbs">
                                    @foreach($order->items->take(4) as $item)
                                        @php $img = $item->product?->images->first()?->url(); @endphp
                                        @if($img)
                                            <img src="{{ $img }}" alt="{{ $item->product_name }}" class="ord-thumb js-img-zoom" loading="lazy"
                                                 data-full="{{ $img }}" data-caption="{{ $item->product_name }} · {{ $item->weight_label }} × {{ $item->quantity }}"
                                                 title="{{ $item->product_name }} × {{ $item->quantity }}">
                                        @else
                                            <span class="ord-thumb-empty" title="{{ $item->product_name }}"><i class="bi bi-image"></i></span>
                                        @endif
                                    @endforeach
                                    @if($order->items->count() > 4)
                                        <span class="ord-more">+{{ $order->items->count() - 4 }}</span>
                                    @endif
                                </div>
                                <div class="ord-meta mt-1">{{ $order->items->count() }} item · {{ $order->items->sum('quantity') }} qty</div>
                            </td>
                            <td>
                                <div class="text-capitalize fw-semibold">{{ $order->delivery_schedule_type }}</div>
                                @if($order->delivery_schedule_type === 'scheduled')
                                    <div class="ord-meta">{{ $order->delivery_date?->format('d M Y') }} · {{ $order->delivery_time }}</div>
                                @endif
                                <div class="ord-meta">{{ $order->city }} · {{ $order->pincode }}</div>
                            </td>
                            <td class="text-nowrap">
                                <div class="fw-bold">₹{{ number_format((float) $order->total, 2) }}</div>
                                @if((float) $order->discount_amount > 0)<div class="ord-meta">Coupon −₹{{ number_format((float) $order->discount_amount, 2) }}</div>@endif
                                @if((float) $order->wallet_amount > 0)<div class="ord-meta">Wallet −₹{{ number_format((float) $order->wallet_amount, 2) }}</div>@endif
                                <div class="ord-meta text-capitalize">{{ $order->payment_method }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $badge }}">{{ \App\Http\Controllers\Admin\OrderController::STATUS_LABELS[$order->status] ?? $order->status }}</span>
                            </td>
                            <td class="text-nowrap">
                                <div class="adm-actions">
                                    <a href="{{ route('admin.orders.show', $order, false) }}" class="adm-act adm-act-view" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.orders.bill.pdf', $order, false) }}" class="adm-act adm-act-delete" title="Bill PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                                    <a href="{{ route('admin.orders.bill.excel', $order, false) }}" class="adm-act adm-act-edit" title="Bill Excel"><i class="bi bi-file-earmark-excel"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Koi order nahi mila.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($orders->hasPages())
        <div class="mt-3">{{ $orders->links() }}</div>
    @endif
</div>

@include('Admin.Orders.partials.image-modal')
@endsection

@section('custom_js')
<script>
document.querySelectorAll('.js-date').forEach(function (el) {
    el.addEventListener('change', function () { document.getElementById('presetInput').value = ''; });
});
</script>
@endsection
