@extends('Admin.Layout.master')

@section('title', 'Carts — FreshNonVeg Admin')

@section('custom_css')
@include('Admin.partials.catalog-list-styles')
<style>
.cart-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
@media (max-width: 900px) { .cart-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.cart-stat { background: #fff; border-radius: 10px; padding: 10px 14px; border-left: 4px solid #2BAE49; box-shadow: 0 3px 8px rgba(30,142,62,.08); }
.cart-stat.orange { border-left-color: #F57C00; }
.cart-stat.blue { border-left-color: #1565C0; }
.cart-stat.red { border-left-color: #D32F2F; }
.cart-stat .label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: .04em; }
.cart-stat .value { font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
.cart-filter { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 12px; }
.cart-filter .form-label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; }
.cart-presets { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
.cart-presets a { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; border: 1px solid #cbd5e1; color: #334155; text-decoration: none; background: #fff; }
.cart-presets a.active, .cart-presets a:hover { background: #2BAE49; border-color: #2BAE49; color: #fff; }
.cart-meta { font-size: 11px; color: #64748b; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="section-title mb-0 border-0 pb-0">
                <i class="fas fa-cart-shopping me-2"></i> Active Carts
            </h2>
            <p class="small text-muted mb-0 mt-1">Checkout se pehle cart — product + customer details.</p>
        </div>
    </div>

    <div class="cart-stats">
        <div class="cart-stat"><div class="label">Line items</div><div class="value">{{ number_format($summary['items']) }}</div></div>
        <div class="cart-stat orange"><div class="label">Customers</div><div class="value">{{ number_format($summary['users']) }}</div></div>
        <div class="cart-stat blue"><div class="label">Total qty</div><div class="value">{{ number_format($summary['qty']) }}</div></div>
        <div class="cart-stat red"><div class="label">Cart value</div><div class="value">₹{{ number_format($summary['value'], 2) }}</div></div>
    </div>

    <div class="table-container">
        <form method="GET" action="{{ route('admin.carts.index', [], false) }}" class="cart-filter" id="cartsFilterForm">
            <div class="cart-presets">
                @foreach(\App\Http\Controllers\Admin\CartController::DATE_PRESETS as $key => $label)
                    <a href="{{ route('admin.carts.index', array_merge(request()->except(['page', 'date_from', 'date_to']), ['preset' => $key]), false) }}"
                       class="{{ $filters['preset'] === $key ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
                @if($filters['preset'] !== '')
                    <a href="{{ route('admin.carts.index', request()->except(['page', 'preset']), false) }}">✕ Preset hatao</a>
                @endif
            </div>
            <input type="hidden" name="preset" value="{{ $filters['preset'] }}" id="presetInput">
            @if($filters['user_id'] !== '')<input type="hidden" name="user_id" value="{{ $filters['user_id'] }}">@endif

            <div class="row g-2 align-items-end">
                <div class="col-xl-3 col-md-4">
                    <label class="form-label" for="q">Search</label>
                    <input type="search" name="q" id="q" class="form-control form-control-sm" value="{{ $filters['q'] }}"
                           placeholder="Naam, phone, email, product, note">
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected($filters['category_id'] === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="weight">Weight</label>
                    <select name="weight" id="weight" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($weights as $weight)
                            <option value="{{ $weight }}" @selected($filters['weight'] === $weight)>{{ $weight }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="date_field">Date kis par</label>
                    <select name="date_field" id="date_field" class="form-select form-select-sm">
                        <option value="updated" @selected($filters['date_field'] === 'updated')>Last updated</option>
                        <option value="created" @selected($filters['date_field'] === 'created')>Added date</option>
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
                    <label class="form-label" for="idle">Idle cart</label>
                    <select name="idle" id="idle" class="form-select form-select-sm">
                        <option value="">Any</option>
                        @foreach(\App\Http\Controllers\Admin\CartController::IDLE_OPTIONS as $key => $label)
                            <option value="{{ $key }}" @selected($filters['idle'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="qty_min">Min qty</label>
                    <input type="number" min="1" name="qty_min" id="qty_min" class="form-control form-control-sm" value="{{ $filters['qty_min'] }}">
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="qty_max">Max qty</label>
                    <input type="number" min="1" name="qty_max" id="qty_max" class="form-control form-control-sm" value="{{ $filters['qty_max'] }}">
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
                    <label class="form-label" for="has_note">Preference note</label>
                    <select name="has_note" id="has_note" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="yes" @selected($filters['has_note'] === 'yes')>With note</option>
                        <option value="no" @selected($filters['has_note'] === 'no')>No note</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="product">Product link</label>
                    <select name="product" id="product" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="linked" @selected($filters['product'] === 'linked')>Product linked</option>
                        <option value="missing" @selected($filters['product'] === 'missing')>Product missing</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="sort">Sort</label>
                    <select name="sort" id="sort" class="form-select form-select-sm">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Updated newest</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Updated oldest</option>
                        <option value="added_new" @selected($filters['sort'] === 'added_new')>Added newest</option>
                        <option value="qty_high" @selected($filters['sort'] === 'qty_high')>Qty high → low</option>
                        <option value="amount_high" @selected($filters['sort'] === 'amount_high')>Amount high → low</option>
                        <option value="amount_low" @selected($filters['sort'] === 'amount_low')>Amount low → high</option>
                    </select>
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="per_page">Per page</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(\App\Http\Controllers\Admin\CartController::PER_PAGE_OPTIONS as $opt)
                            <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter me-1"></i> Apply</button>
                    <a href="{{ route('admin.carts.index', [], false) }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
                </div>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div class="cart-meta">
                Showing <strong>{{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }}</strong> of <strong>{{ $items->total() }}</strong> cart items
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
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Line total</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="text-muted">{{ $item->id }}</td>
                            <td>@include('Admin.partials.user-cell', ['user' => $item->user])</td>
                            <td>
                                @include('Admin.partials.product-cell', [
                                    'product' => $item->product,
                                    'item' => $item,
                                    'fallbackName' => $item->product_name,
                                    'weight' => $item->weight_label,
                                    'extra' => $item->preference_note ? 'Note: '.\Illuminate\Support\Str::limit($item->preference_note, 40) : null,
                                ])
                            </td>
                            <td class="fw-bold">{{ $item->quantity }}</td>
                            <td>₹{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="fw-bold text-success">₹{{ number_format($item->lineTotal(), 2) }}</td>
                            <td class="small text-nowrap">
                                {{ $item->updated_at?->format('d M Y') }}<br>
                                <span class="text-muted">{{ $item->updated_at?->format('h:i A') }}</span>
                            </td>
                            <td class="text-nowrap">
                                @if($item->user)
                                    @include('Admin.partials.row-actions', ['view' => route('admin.users.show', $item->user, false)])
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">Is filter se koi cart item nahi mila.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($items->hasPages())
        <div class="mt-3">{{ $items->links() }}</div>
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
