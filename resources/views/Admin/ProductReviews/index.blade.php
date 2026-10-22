@extends('Admin.Layout.master')

@section('title', 'Product Ratings — FreshNonVeg Admin')

@section('custom_css')
@include('Admin.partials.catalog-list-styles')
<style>
.rev-stars { color: #F57C00; letter-spacing: 1px; font-size: 13px; white-space: nowrap; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h2 class="section-title mb-0 border-0 pb-0">
                <i class="fas fa-star me-2"></i> Product Ratings
            </h2>
            <p class="small text-muted mb-0 mt-1">View, edit, delete, aur active/inactive. Off karne par product page se chhup jayegi.</p>
        </div>
    </div>

    <div class="table-container">
        <form method="GET" class="filter-panel mb-3" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Search</label>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Product, customer, review text">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Stars</label>
                    <select name="rating" class="form-select form-select-sm">
                        <option value="">All</option>
                        @for($s = 5; $s >= 1; $s--)
                            <option value="{{ $s }}" @selected($rating === $s)>{{ $s }} star</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-uppercase fw-bold text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Apply</button>
                    <a href="{{ route('admin.product-reviews.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0 adm-catalog-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td class="text-muted">{{ $review->id }}</td>
                            <td>
                                @include('Admin.partials.product-cell', [
                                    'product' => $review->product,
                                    'fallbackName' => $review->product?->name ?? 'Product removed',
                                    'size' => 40,
                                ])
                            </td>
                            <td>@include('Admin.partials.user-cell', ['user' => $review->user])</td>
                            <td class="rev-stars">{{ str_repeat('★', $review->rating) }}<span class="text-muted">{{ str_repeat('☆', 5 - $review->rating) }}</span></td>
                            <td class="small" style="max-width:240px;white-space:normal;">{{ \Illuminate\Support\Str::limit($review->review ?: '—', 80) }}</td>
                            <td class="small">
                                @if($review->order)
                                    <a href="{{ route('admin.orders.show', $review->order, false) }}">{{ $review->order->order_number }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="small text-nowrap">{{ $review->created_at?->format('d M Y') }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', [
                                    'action' => route('admin.product-reviews.toggle-status', $review),
                                    'active' => $review->isActive(),
                                ])
                            </td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', [
                                    'view' => route('admin.product-reviews.show', $review, false),
                                    'edit' => route('admin.product-reviews.edit', $review, false),
                                    'delete' => route('admin.product-reviews.destroy', $review, false),
                                    'confirm' => 'Is rating ko delete karein?',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">No ratings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($reviews->hasPages())
        <div class="mt-3">{{ $reviews->links() }}</div>
    @endif
</div>
@endsection
