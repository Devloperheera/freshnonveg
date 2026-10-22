@extends('Admin.Layout.master')

@section('title', 'Add wallet amount — FreshNonVeg Admin')

@section('custom_css')
@include('Admin.partials.catalog-list-styles')
<style>
.wal-credit-box { background: #fff; border: 1px solid #bbf7d0; border-radius: 14px; padding: 16px; margin-bottom: 16px; box-shadow: 0 6px 16px rgba(43,174,73,.08); }
.wal-selected { background: #f0fdf4; border: 1px solid #86efac; border-radius: 12px; padding: 10px 12px; }
.wal-hint { font-size: 12px; color: #64748b; }
.table-row-active { background: #ecfdf5 !important; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="section-title mb-0 border-0 pb-0"><i class="fas fa-hand-holding-dollar me-2"></i> Add wallet amount</h2>
            <p class="wal-hint mb-0 mt-1">Phone call / goodwill / kisi bhi customer ko — complaint ticket ki zaroorat nahi.</p>
        </div>
        <a href="{{ route('admin.wallet.index', [], false) }}" class="btn btn-outline-secondary btn-sm">← Wallet transactions</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="wal-credit-box" id="creditBox">
        @if($selected)
            <form method="POST" action="{{ route('admin.wallet.store', [], false) }}" onsubmit="return confirm('₹' + this.amount.value + ' is customer ke wallet me add karein?')">
                @csrf
                <input type="hidden" name="user_id" value="{{ $selected->id }}">
                <div class="wal-selected d-flex align-items-center gap-3 flex-wrap mb-3">
                    @include('Admin.partials.user-cell', ['user' => $selected])
                    <div class="ms-auto text-end">
                        <div class="small text-muted">Current wallet</div>
                        <div class="fw-bold text-success fs-5">₹{{ number_format((float) $selected->wallet_balance, 2) }}</div>
                    </div>
                </div>
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-uppercase">Amount (₹) *</label>
                        <input type="number" name="amount" min="1" step="0.01" class="form-control" value="{{ old('amount') }}" required placeholder="500" autofocus>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-uppercase">Reason / note</label>
                        <input type="text" name="note" maxlength="255" class="form-control" value="{{ old('note') }}" placeholder="Phone call — late delivery / missing item / goodwill">
                    </div>
                    <div class="col-md-4">
                        <input type="hidden" name="notify_customer" value="0">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="notify_customer" value="1" id="notify_customer" @checked(old('notify_customer', true))>
                            <label class="form-check-label small" for="notify_customer">Customer ko email bhejein @if($selected->email)( {{ $selected->email }} )@else(email nahi hai)@endif</label>
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="fas fa-wallet me-1"></i> Wallet me add karein</button>
                    </div>
                </div>
            </form>
        @else
            <div class="text-muted">Neeche list se customer <strong>Select</strong> karo — phone se search karke call wali complaint par bhi amount de sakte ho.</div>
        @endif
    </div>

    <div class="table-container">
        <form method="GET" action="{{ route('admin.wallet.credit', [], false) }}" class="mb-3 p-3" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;">
            @if($selected)<input type="hidden" name="user" value="{{ $selected->id }}">@endif
            <div class="row g-2 align-items-end">
                <div class="col-lg-4">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Search</label>
                    <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Naam, phone, email ya user ID" @if(! $selected) autofocus @endif
                </div>
                <div class="col-lg-2">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Wallet</label>
                    <select name="wallet" class="form-select form-select-sm">
                        <option value="">All users</option>
                        <option value="yes" @selected($wallet === 'yes')>Balance hai</option>
                        <option value="no" @selected($wallet === 'no')>Balance zero</option>
                    </select>
                </div>
                <div class="col-lg-1">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Per page</label>
                    <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach(\App\Http\Controllers\Admin\WalletController::PER_PAGE_OPTIONS as $opt)
                            <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i> Search</button>
                    <a href="{{ route('admin.wallet.credit', [], false) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>

        <div class="small text-muted mb-2">
            Showing <strong>{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}</strong> of <strong>{{ $users->total() }}</strong> customers
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0 adm-catalog-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Wallet</th>
                        <th>Orders</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="{{ $selected && $selected->id === $user->id ? 'table-row-active' : '' }}">
                            <td class="text-muted">{{ $user->id }}</td>
                            <td>@include('Admin.partials.user-cell', ['user' => $user])</td>
                            <td class="fw-semibold">{{ $user->phone ?: '—' }}</td>
                            <td class="{{ (float) $user->wallet_balance > 0 ? 'text-success fw-bold' : 'text-muted' }}">₹{{ number_format((float) $user->wallet_balance, 2) }}</td>
                            <td>{{ $user->orders_count }}</td>
                            <td>
                                @if($user->isActive())
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.wallet.credit', array_merge(request()->except('page'), ['user' => $user->id]), false) }}#creditBox"
                                   class="btn btn-sm {{ $selected && $selected->id === $user->id ? 'btn-success' : 'btn-outline-success' }} py-0">
                                    {{ $selected && $selected->id === $user->id ? 'Selected' : 'Select' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Koi customer nahi mila.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="mt-3">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection
