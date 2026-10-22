@extends('Admin.Layout.master')

@section('title', 'Coupons — FreshNonVeg Admin')

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-ticket-alt me-2"></i> Discount Coupons
        </h2>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add coupon
        </a>
    </div>

    <p class="small text-muted mb-3">Active coupon koi bhi customer checkout par laga sakta hai. Discount cart subtotal par lagega.</p>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount</th>
                        <th>Min order</th>
                        <th>Used</th>
                        <th>Validity</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $coupon->code }}</div>
                                @if($coupon->title)
                                    <div class="small text-muted">{{ $coupon->title }}</div>
                                @endif
                            </td>
                            <td>{{ $coupon->valueLabel() }}</td>
                            <td>₹{{ number_format((float) $coupon->min_order_amount, 0) }}</td>
                            <td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                            <td class="small">
                                {{ $coupon->starts_at?->format('d M Y') ?? 'Any time' }}
                                –
                                {{ $coupon->expires_at?->format('d M Y') ?? 'No expiry' }}
                            </td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.coupons.toggle-status', $coupon), 'active' => $coupon->isActive()])
                            </td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['edit' => route('admin.coupons.edit', $coupon), 'delete' => route('admin.coupons.destroy', $coupon), 'confirm' => 'Delete this coupon?'])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No coupons yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
