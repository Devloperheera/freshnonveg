@extends('Admin.Layout.master')

@section('title', 'Rating #'.$review->id.' — Admin')

@section('custom_css')
@include('Admin.partials.catalog-list-styles')
<style>
.rev-stars { color: #F57C00; letter-spacing: 2px; font-size: 22px; }
.rev-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.rev-head { padding: 10px 14px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 11px; font-weight: 800; text-transform: uppercase; color: #2BAE49; }
.rev-body { padding: 14px; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-star me-2"></i> Rating #{{ $review->id }}
        </h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.product-reviews.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> List</a>
            <a href="{{ route('admin.product-reviews.edit', $review) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i> Edit</a>
            <form method="POST" action="{{ route('admin.product-reviews.destroy', $review) }}" class="d-inline" data-confirm="Is rating ko delete karein?">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash me-1"></i> Delete</button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="rev-card mb-3">
                <div class="rev-head">Review</div>
                <div class="rev-body">
                    <div class="rev-stars mb-2">{{ str_repeat('★', $review->rating) }}<span class="text-muted">{{ str_repeat('☆', 5 - $review->rating) }}</span>
                        <span class="fs-6 text-dark fw-bold ms-1">{{ $review->rating }}/5</span>
                    </div>
                    <p class="mb-3" style="white-space:pre-line">{{ $review->review ?: 'Customer ne sirf stars diye, text nahi likha.' }}</p>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div>
                            <div class="small text-muted text-uppercase fw-bold">Status</div>
                            @include('Admin.partials.status-toggle', [
                                'action' => route('admin.product-reviews.toggle-status', $review),
                                'active' => $review->isActive(),
                            ])
                            <div class="small text-muted">{{ $review->isActive() ? 'Product page par dikhegi' : 'Product page se chhupi hai' }}</div>
                        </div>
                        <div class="small text-muted">
                            Posted {{ $review->created_at?->format('d M Y, h:i A') }}<br>
                            Updated {{ $review->updated_at?->format('d M Y, h:i A') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="rev-card">
                <div class="rev-head">Product</div>
                <div class="rev-body">
                    @include('Admin.partials.product-cell', [
                        'product' => $review->product,
                        'fallbackName' => $review->product?->name ?? 'Product removed',
                        'size' => 72,
                    ])
                    @if($review->product)
                        <a href="{{ route('admin.products.show', $review->product, false) }}" class="btn btn-outline-success btn-sm mt-3">Admin product</a>
                        <a href="{{ route('products.show', $review->product, false) }}" target="_blank" class="btn btn-outline-secondary btn-sm mt-3">Website page</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="rev-card mb-3">
                <div class="rev-head">Customer</div>
                <div class="rev-body">
                    @include('Admin.partials.user-cell', ['user' => $review->user])
                    @if($review->user)
                        <a href="{{ route('admin.users.show', $review->user, false) }}" class="btn btn-outline-success btn-sm mt-3">User profile</a>
                    @endif
                </div>
            </div>
            <div class="rev-card">
                <div class="rev-head">Order</div>
                <div class="rev-body">
                    @if($review->order)
                        <div class="fw-bold">{{ $review->order->order_number }}</div>
                        <div class="small text-muted mb-2">{{ $review->order->created_at?->format('d M Y') }}</div>
                        <a href="{{ route('admin.orders.show', $review->order) }}" class="btn btn-outline-success btn-sm">View order</a>
                    @else
                        <span class="text-muted">Order link nahi hai.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
