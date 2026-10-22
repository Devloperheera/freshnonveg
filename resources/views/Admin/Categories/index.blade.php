@extends('Admin.Layout.master')

@section('title', 'Categories — FreshNonVeg Admin')

@section('custom_css')
<style>
.cat-stat-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 1.25rem; }
@media (max-width: 768px) { .cat-stat-row { grid-template-columns: 1fr; } }
.cat-stat-card {
    background: #fff; border-radius: 10px; padding: 14px 16px;
    box-shadow: 0 4px 6px rgba(30,142,62,0.08); border-left: 4px solid var(--primary-color, #2BAE49);
}
.cat-stat-card.red { border-left-color: #D32F2F; }
.cat-stat-card.blue { border-left-color: #1E8E3E; }
.cat-stat-card .label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; }
.cat-stat-card .value { font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-top: 4px; }
.cat-stat-card .icon { float: right; font-size: 1.25rem; opacity: .35; }
.filter-panel {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 1rem;
}
.filter-panel .form-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
.list-meta { font-size: 12px; color: #64748b; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-tags me-2"></i> Categories
        </h2>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Category
        </a>
    </div>

    <div class="cat-stat-row">
        <div class="cat-stat-card blue">
            <i class="fas fa-layer-group icon"></i>
            <div class="label">Total Categories</div>
            <div class="value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="cat-stat-card">
            <i class="fas fa-circle-check icon"></i>
            <div class="label">Active</div>
            <div class="value">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="cat-stat-card red">
            <i class="fas fa-ban icon"></i>
            <div class="label">Inactive</div>
            <div class="value">{{ number_format($stats['inactive']) }}</div>
        </div>
    </div>

    <div id="table-section" class="table-container">

        <form method="GET" action="{{ route('admin.categories.index') }}" class="filter-panel" id="categoriesFilterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-4">
                    <label class="form-label" for="q">Search</label>
                    <input type="search" name="q" id="q" class="form-control form-control-sm"
                           value="{{ $filters['q'] }}" placeholder="Category name...">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" @selected($filters['status'] === 'active')>Active</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="created_from">Created From</label>
                    <input type="date" name="created_from" id="created_from" class="form-control form-control-sm"
                           value="{{ $filters['created_from'] }}">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="created_to">Created To</label>
                    <input type="date" name="created_to" id="created_to" class="form-control form-control-sm"
                           value="{{ $filters['created_to'] }}">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="sort">Sort</label>
                    <select name="sort" id="sort" class="form-select form-select-sm">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest first</option>
                        <option value="name_asc" @selected($filters['sort'] === 'name_asc')>Name A–Z</option>
                        <option value="name_desc" @selected($filters['sort'] === 'name_desc')>Name Z–A</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="per_page">Per page</label>
                    <select name="per_page" id="per_page" class="form-select form-select-sm">
                        @foreach($perPageOptions as $opt)
                            <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-filter me-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
                </div>
            </div>
        </form>

        <div class="mb-2 list-meta">
            Showing <strong>{{ $categories->firstItem() ?? 0 }}–{{ $categories->lastItem() ?? 0 }}</strong>
            of <strong>{{ $categories->total() }}</strong> categories
            @if($filters['q'] || $filters['status'] || $filters['created_from'] || $filters['created_to'])
                <span class="text-muted">· filters applied</span>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Category Name</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>
                                <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}"
                                     class="rounded border" style="width: 56px; height: 56px; object-fit: cover;">
                            </td>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.categories.toggle-status', $category), 'active' => $category->isActive()])
                            </td>
                            <td><span class="small">{{ $category->created_at?->format('d M Y') }}</span></td>
                            <td><span class="small">{{ $category->updated_at?->format('d M Y, H:i') }}</span></td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['edit' => route('admin.categories.edit', $category), 'delete' => route('admin.categories.destroy', $category), 'confirm' => 'Delete this category?'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Koi category nahi mili / No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
            <div class="list-meta">
                Page {{ $categories->currentPage() }} of {{ $categories->lastPage() }}
            </div>
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var perPage = document.getElementById('per_page');
    if (perPage) {
        perPage.addEventListener('change', function () {
            document.getElementById('categoriesFilterForm').submit();
        });
    }
});
</script>
@endsection
