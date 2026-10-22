@extends('Admin.Layout.master')

@section('title', 'Products — FreshNonVeg Admin')

@section('custom_css')
<style>
.prod-stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 1.25rem; }
@media (max-width: 768px) { .prod-stat-row { grid-template-columns: 1fr; } }
.prod-stat-card { background: #fff; border-radius: 10px; padding: 14px 16px; box-shadow: 0 4px 6px rgba(30,142,62,0.08); border-left: 4px solid #2BAE49; }
.prod-stat-card.orange { border-left-color: #F57C00; }
.prod-stat-card .label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; }
.prod-stat-card .value { font-size: 1.5rem; font-weight: 800; }
.filter-panel { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 1rem; }
.filter-panel .form-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; }
.list-meta { font-size: 12px; color: #64748b; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0"><i class="fas fa-box me-2"></i> Products</h2>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Add Product</a>
    </div>

    <div class="prod-stat-row">
        <div class="prod-stat-card"><div class="label">Total</div><div class="value">{{ number_format($stats['total']) }}</div></div>
        <div class="prod-stat-card"><div class="label">Active</div><div class="value">{{ number_format($stats['active']) }}</div></div>
        <div class="prod-stat-card orange"><div class="label">Featured</div><div class="value">{{ number_format($stats['featured']) }}</div></div>
    </div>

    <div class="table-container">
        <form method="GET" action="{{ route('admin.products.index') }}" class="filter-panel" id="productsFilterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3">
                    <label class="form-label" for="q">Search</label>
                    <input type="search" name="q" id="q" class="form-control form-control-sm" value="{{ $filters['q'] }}" placeholder="Product name, SEO...">
                </div>
                <div class="col-lg-2">
                    <label class="form-label" for="category_id">Category</label>
                    <select name="category_id" id="category_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected($filters['category_id'] == (string) $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label" for="tag_id">Tag</label>
                    <select name="tag_id" id="tag_id" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" @selected($filters['tag_id'] == (string) $tag->id)>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" @selected($filters['status'] === 'active')>Active</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label" for="featured">Featured</label>
                    <select name="featured" id="featured" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="yes" @selected($filters['featured'] === 'yes')>Yes</option>
                        <option value="no" @selected($filters['featured'] === 'no')>No</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label" for="sort">Sort</label>
                    <select name="sort" id="sort" class="form-select form-select-sm">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest</option>
                        <option value="name_asc" @selected($filters['sort'] === 'name_asc')>Name A–Z</option>
                        <option value="name_desc" @selected($filters['sort'] === 'name_desc')>Name Z–A</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label" for="per_page">Per page</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm">
                        @foreach($perPageOptions as $opt)
                            <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>

        <p class="list-meta mb-2">
            Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }}
        </p>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Banner</th>
                        <th>Weights / Price</th>
                        <th>Tags</th>
                        <th>Featured</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>
                                @if($product->images->first())
                                    <img src="{{ $product->images->first()->url() }}" alt="" class="rounded border"
                                         style="width:48px;height:48px;object-fit:cover;">
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $product->name }}</td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>{{ $product->banner?->name ?? '—' }}</td>
                            <td class="small">
                                @foreach($product->weights as $w)
                                    <div>{{ $w->weight_label }} — ₹{{ number_format((float) $w->price, 2) }}</div>
                                @endforeach
                            </td>
                            <td>
                                @forelse($product->tags as $tag)
                                    <span class="badge bg-primary">{{ $tag->name }}</span>
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td>
                                @if($product->is_featured)
                                    <span class="badge bg-warning text-dark">Featured</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.products.toggle-status', $product), 'active' => $product->isActive()])
                            </td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['view' => route('admin.products.show', $product), 'viewNewTab' => true, 'edit' => route('admin.products.edit', $product), 'delete' => route('admin.products.destroy', $product), 'confirm' => 'Delete product?'])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center py-4 text-muted">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
            <span class="list-meta">Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
document.getElementById('per_page')?.addEventListener('change', function () {
    document.getElementById('productsFilterForm').submit();
});
</script>
@endsection
