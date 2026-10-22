@extends('Admin.Layout.master')

@section('title', 'Customers / Users — FreshNonVeg Admin')

@section('custom_css')
<style>
.user-stat-row { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 1.25rem; }
@media (max-width: 1400px) { .user-stat-row { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 768px) { .user-stat-row { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.user-stat-card {
    background: #fff; border-radius: 10px; padding: 14px 16px;
    box-shadow: 0 4px 6px rgba(30,142,62,0.08); border-left: 4px solid var(--primary-color, #2BAE49);
}
.user-stat-card.orange { border-left-color: #F57C00; }
.user-stat-card.red { border-left-color: #D32F2F; }
.user-stat-card.blue { border-left-color: #1E8E3E; }
.user-stat-card .label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: .04em; }
.user-stat-card .value { font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1.2; margin-top: 4px; }
.user-stat-card .icon { float: right; font-size: 1.25rem; opacity: .35; }
.filter-panel {
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 1rem;
}
.filter-panel .form-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
.users-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; justify-content: space-between; margin-bottom: 12px; }
.users-meta { font-size: 12px; color: #64748b; }
.emp-avatar {
    width: 32px; height: 32px; border-radius: 50%; object-fit: cover; background: #F57C00; color: #fff;
    display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;
}
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-users me-2"></i> Customers / Users
        </h2>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('admin.users.export.excel') }}{{ $exportQuery ? '?'.$exportQuery : '' }}"
               class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Excel Export
            </a>
            <a href="{{ route('admin.users.export.csv') }}{{ $exportQuery ? '?'.$exportQuery : '' }}"
               class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-csv me-1"></i> CSV Export
            </a>
            <span class="users-meta ms-1">Current filters apply to export</span>
        </div>
    </div>

    {{-- Stats cards --}}
    <div class="user-stat-row">
        <div class="user-stat-card blue">
            <i class="fas fa-users icon"></i>
            <div class="label">Total Users</div>
            <div class="value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="user-stat-card">
            <i class="fas fa-circle-check icon"></i>
            <div class="label">Active</div>
            <div class="value">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="user-stat-card red">
            <i class="fas fa-ban icon"></i>
            <div class="label">Inactive</div>
            <div class="value">{{ number_format($stats['inactive']) }}</div>
        </div>
        <div class="user-stat-card orange">
            <i class="fas fa-bag-shopping icon"></i>
            <div class="label">Products Buy (Orders)</div>
            <div class="value">{{ number_format($stats['purchased']) }}</div>
        </div>
        <div class="user-stat-card">
            <i class="fas fa-cart-shopping icon"></i>
            <div class="label">Cart Mein Data</div>
            <div class="value">{{ number_format($stats['with_cart']) }}</div>
        </div>
        <div class="user-stat-card orange">
            <i class="fas fa-heart icon"></i>
            <div class="label">Wishlist</div>
            <div class="value">{{ number_format($stats['with_wishlist']) }}</div>
        </div>
    </div>

    <div id="table-section" class="table-container">

        {{-- Advanced filters --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="filter-panel" id="usersFilterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-3 col-md-4">
                    <label class="form-label" for="q">Search</label>
                    <input type="search" name="q" id="q" class="form-control form-control-sm"
                           value="{{ $filters['q'] }}" placeholder="Name, email, phone, address...">
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
                    <label class="form-label" for="source">Signup Source</label>
                    <select name="source" id="source" class="form-select form-select-sm">
                        <option value="">All</option>
                        @foreach($sources as $src)
                            <option value="{{ $src }}" @selected($filters['source'] === $src)>{{ $src }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="has_orders">Ordered?</label>
                    <select name="has_orders" id="has_orders" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="yes" @selected($filters['has_orders'] === 'yes')>Yes</option>
                        <option value="no" @selected($filters['has_orders'] === 'no')>No</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="has_cart">Cart?</label>
                    <select name="has_cart" id="has_cart" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="yes" @selected($filters['has_cart'] === 'yes')>Yes</option>
                        <option value="no" @selected($filters['has_cart'] === 'no')>No</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="has_wishlist">Wishlist?</label>
                    <select name="has_wishlist" id="has_wishlist" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="yes" @selected($filters['has_wishlist'] === 'yes')>Yes</option>
                        <option value="no" @selected($filters['has_wishlist'] === 'no')>No</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="app_installed">App Installed</label>
                    <select name="app_installed" id="app_installed" class="form-select form-select-sm">
                        <option value="">Any</option>
                        <option value="yes" @selected($filters['app_installed'] === 'yes')>Yes</option>
                        <option value="no" @selected($filters['app_installed'] === 'no')>No</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="registered_from">Registered From</label>
                    <input type="date" name="registered_from" id="registered_from" class="form-control form-control-sm"
                           value="{{ $filters['registered_from'] }}">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="registered_to">Registered To</label>
                    <input type="date" name="registered_to" id="registered_to" class="form-control form-control-sm"
                           value="{{ $filters['registered_to'] }}">
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label" for="sort">Sort</label>
                    <select name="sort" id="sort" class="form-select form-select-sm">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest first</option>
                        <option value="name_asc" @selected($filters['sort'] === 'name_asc')>Name A–Z</option>
                        <option value="name_desc" @selected($filters['sort'] === 'name_desc')>Name Z–A</option>
                        <option value="logins" @selected($filters['sort'] === 'logins')>Most logins</option>
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
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
                </div>
            </div>
        </form>

        <div class="users-toolbar">
            <div class="users-meta">
                Showing <strong>{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}</strong>
                of <strong>{{ $users->total() }}</strong> users
            </div>
        </div>

        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Source</th>
                        <th>Orders</th>
                        <th>Cart</th>
                        <th>Wishlist</th>
                        <th>Logins</th>
                        <th>Last Login</th>
                        <th>Web Login</th>
                        <th>App</th>
                        <th>Device</th>
                        <th>Address</th>
                        <th>Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($user->profile_image)
                                        <img src="{{ $user->profile_image }}" alt="" class="emp-avatar" width="32" height="32">
                                    @else
                                        <span class="emp-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    @endif
                                    <span class="fw-semibold">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email ?? '—' }}</td>
                            <td>{{ $user->phone ?? '—' }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.users.toggle-status', $user), 'active' => $user->isActive()])
                            </td>
                            <td><span class="badge bg-primary">{{ $user->registration_source ?? '—' }}</span></td>
                            <td><span class="badge bg-warning text-dark">{{ $user->orders_count }}</span></td>
                            <td><span class="badge bg-info text-dark">{{ $user->cart_items_count }}</span></td>
                            <td><span class="badge bg-danger">{{ $user->wishlist_items_count }}</span></td>
                            <td>{{ $user->login_count ?? 0 }}</td>
                            <td>
                                @if($user->last_login_at)
                                    <span class="small">{{ $user->last_login_at->format('d M Y H:i') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($user->web_last_login_at)
                                    <span class="small">{{ $user->web_last_login_at->format('d M Y') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($user->is_app_installed)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-light text-dark">No</span>
                                @endif
                            </td>
                            <td>
                                <span class="small d-block">{{ $user->device_type ?? '—' }}</span>
                                <span class="small text-muted">{{ $user->device_model ?? '' }}</span>
                            </td>
                            <td style="white-space: normal; max-width: 180px;">
                                <span class="small">{{ \Illuminate\Support\Str::limit($user->address, 60) ?: '—' }}</span>
                            </td>
                            <td><span class="small">{{ $user->created_at?->format('d M Y') }}</span></td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['view' => route('admin.users.show', $user), 'viewNewTab' => true])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="text-center py-4 text-muted">Koi user nahi mila / No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
            <div class="users-meta">
                Page {{ $users->currentPage() }} of {{ $users->lastPage() }}
            </div>
            {{ $users->links() }}
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
            document.getElementById('usersFilterForm').submit();
        });
    }
});
</script>
@endsection
