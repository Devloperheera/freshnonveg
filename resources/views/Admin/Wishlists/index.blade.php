@extends('Admin.Layout.master')

@section('title', 'Wishlists — FreshNonVeg Admin')

@section('custom_css')
@include('Admin.partials.catalog-list-styles')
<style>
.wish-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-bottom: 14px; }
@media (max-width: 900px) { .wish-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.wish-stat { background: #fff; border-radius: 10px; padding: 10px 14px; border-left: 4px solid #2BAE49; box-shadow: 0 3px 8px rgba(30,142,62,.08); }
.wish-stat.orange { border-left-color: #F57C00; }
.wish-stat.blue { border-left-color: #1565C0; }
.wish-stat.red { border-left-color: #D32F2F; }
.wish-stat .label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: .04em; }
.wish-stat .value { font-size: 1.35rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
.wish-filter { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 12px; }
.wish-filter .form-label { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; }
.wish-presets { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
.wish-presets a { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; border: 1px solid #cbd5e1; color: #334155; text-decoration: none; background: #fff; }
.wish-presets a.active, .wish-presets a:hover { background: #2BAE49; border-color: #2BAE49; color: #fff; }
.wish-meta { font-size: 11px; color: #64748b; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="section-title mb-0 border-0 pb-0">
                <i class="fas fa-heart me-2"></i> Customer Wishlists
            </h2>
            <p class="small text-muted mb-0 mt-1">Product image, details aur customer ki poori info.</p>
        </div>
    </div>

    <div class="wish-stats">
        <div class="wish-stat"><div class="label">Items</div><div class="value">{{ number_format($summary['items']) }}</div></div>
        <div class="wish-stat orange"><div class="label">Customers</div><div class="value">{{ number_format($summary['users']) }}</div></div>
        <div class="wish-stat blue"><div class="label">Sitting 7+ days</div><div class="value">{{ number_format($summary['sitting']) }}</div></div>
        <div class="wish-stat red"><div class="label">Product missing</div><div class="value">{{ number_format($summary['missing']) }}</div></div>
    </div>

    <div class="table-container">
        <form method="GET" action="{{ route('admin.wishlists.index', [], false) }}" class="wish-filter" id="wishlistsFilterForm">
            <div class="wish-presets">
                @foreach(\App\Http\Controllers\Admin\WishlistController::DATE_PRESETS as $key => $label)
                    <a href="{{ route('admin.wishlists.index', array_merge(request()->except(['page', 'date_from', 'date_to']), ['preset' => $key]), false) }}"
                       class="{{ $filters['preset'] === $key ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
                @if($filters['preset'] !== '')
                    <a href="{{ route('admin.wishlists.index', request()->except(['page', 'preset']), false) }}">✕ Preset hatao</a>
                @endif
            </div>
            <input type="hidden" name="preset" value="{{ $filters['preset'] }}" id="presetInput">
            @if($filters['user_id'] !== '')<input type="hidden" name="user_id" value="{{ $filters['user_id'] }}">@endif

            <div class="row g-2 align-items-end">
                <div class="col-xl-3 col-md-4">
                    <label class="form-label" for="q">Search</label>
                    <input type="search" name="q" id="q" class="form-control form-control-sm" value="{{ $filters['q'] }}"
                           placeholder="Naam, phone, email, product">
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
                    <label class="form-label" for="date_from">From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control form-control-sm js-date" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="date_to">To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control form-control-sm js-date" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="sitting">Sitting in wishlist</label>
                    <select name="sitting" id="sitting" class="form-select form-select-sm">
                        <option value="">Any</option>
                        @foreach(\App\Http\Controllers\Admin\WishlistController::SITTING_OPTIONS as $key => $label)
                            <option value="{{ $key }}" @selected($filters['sitting'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
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
                    <label class="form-label" for="product">Product link</label>
                    <select name="product" id="product" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="linked" @selected($filters['product'] === 'linked')>Product linked</option>
                        <option value="missing" @selected($filters['product'] === 'missing')>Product missing</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="product_status">Product status</label>
                    <select name="product_status" id="product_status" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="active" @selected($filters['product_status'] === 'active')>Active</option>
                        <option value="inactive" @selected($filters['product_status'] === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="featured">Featured</label>
                    <select name="featured" id="featured" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="yes" @selected($filters['featured'] === 'yes')>Featured</option>
                        <option value="no" @selected($filters['featured'] === 'no')>Not featured</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3">
                    <label class="form-label" for="sort">Sort</label>
                    <select name="sort" id="sort" class="form-select form-select-sm">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Added newest</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Added oldest</option>
                        <option value="product" @selected($filters['sort'] === 'product')>Product A → Z</option>
                    </select>
                </div>
                <div class="col-xl-1 col-md-2">
                    <label class="form-label" for="per_page">Per page</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(\App\Http\Controllers\Admin\WishlistController::PER_PAGE_OPTIONS as $opt)
                            <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter me-1"></i> Apply</button>
                    <a href="{{ route('admin.wishlists.index', [], false) }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
                </div>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div class="wish-meta">
                Showing <strong>{{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }}</strong>
                of <strong>{{ $items->total() }}</strong> wishlist items
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
                        <th>Added on</th>
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
                                    'fallbackName' => $item->product_name,
                                ])
                            </td>
                            <td class="small text-nowrap">
                                {{ $item->created_at?->format('d M Y') }}<br>
                                <span class="text-muted">{{ $item->created_at?->format('h:i A') }}</span>
                            </td>
                            <td class="text-nowrap">
                                @if($item->user)
                                    @include('Admin.partials.row-actions', ['view' => route('admin.users.show', $item->user, false)])
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">Is filter se koi wishlist item nahi mila.</td></tr>
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
