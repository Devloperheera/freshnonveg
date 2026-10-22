@extends('Admin.Layout.master')

@section('title', 'Order '.$order->order_number.' — Admin')

@section('custom_css')
<style>
.od { font-size: 12px; }
.od-hero { background: linear-gradient(135deg, #2BAE49, #1E8E3E); color: #fff; border-radius: 10px; padding: 14px 16px; margin-bottom: 12px; }
.od-hero .no { font-size: 18px; font-weight: 800; letter-spacing: .5px; }
.od-hero .total { font-size: 22px; font-weight: 800; }
.od-pill { display: inline-block; background: rgba(255,255,255,.18); padding: 2px 9px; border-radius: 999px; font-size: 10px; font-weight: 700; margin: 3px 4px 0 0; }
.od-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 12px; overflow: hidden; }
.od-card-head { padding: 8px 12px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #2BAE49; display: flex; justify-content: space-between; align-items: center; }
.od-card-head i { color: #F57C00; margin-right: 5px; }
.od-card-body { padding: 10px 12px; }
.od-kv { display: grid; grid-template-columns: 110px 1fr; gap: 5px 10px; margin: 0; }
.od-kv dt { color: #64748b; font-weight: 700; font-size: 10px; text-transform: uppercase; margin: 0; padding-top: 1px; }
.od-kv dd { margin: 0; color: #0f172a; word-break: break-word; }
.od-av { width: 46px; height: 46px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, #F57C00, #D32F2F); color: #fff; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; }
.od-av img { width: 100%; height: 100%; object-fit: cover; }
.od-items td, .od-items th { vertical-align: middle; }
.od-items th { font-size: 10px; text-transform: uppercase; color: #64748b; }
.od-thumb { width: 54px; height: 54px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; cursor: zoom-in; }
.od-thumb-empty { width: 54px; height: 54px; border-radius: 8px; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; }
.od-sum td { padding: 4px 0; }
.od-sum .grand td { border-top: 2px solid #0f172a; padding-top: 8px; font-size: 15px; font-weight: 800; }
.od-bill-wrap { background: #f8fafc; padding: 14px; border-radius: 10px; }
</style>
@endsection

@section('content')
@php
    $subtotal = $order->items->sum(fn ($l) => (float) $l->unit_price * (int) $l->quantity);
    $slot = $order->delivery_time ? \App\Support\CheckoutDelivery::labelFor($order->delivery_time) : '';
    $statusLabel = \App\Http\Controllers\Admin\OrderController::STATUS_LABELS[$order->status] ?? $order->status;
@endphp
<div class="container-fluid mt-3 mb-4 od">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <a href="{{ route('admin.orders.index', [], false) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> All orders</a>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.orders.bill.pdf', $order, false) }}" class="btn btn-danger btn-sm"><i class="fas fa-file-pdf me-1"></i> Bill PDF</a>
            <a href="{{ route('admin.orders.bill.excel', $order, false) }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel me-1"></i> Bill Excel</a>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i> Print</button>
        </div>
    </div>

    <div class="od-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="no">{{ $order->order_number }}</div>
            <div style="font-size:11px;opacity:.9">Placed {{ $order->created_at?->format('d M Y, h:i A') }} · ID #{{ $order->id }}</div>
            <span class="od-pill">{{ $statusLabel }}</span>
            <span class="od-pill text-capitalize">{{ $order->delivery_schedule_type }} delivery</span>
            <span class="od-pill text-capitalize">{{ $order->payment_method }}</span>
            <span class="od-pill">{{ $order->items->count() }} items · {{ $order->items->sum('quantity') }} qty</span>
        </div>
        <div class="text-end">
            <div style="font-size:10px;opacity:.85;text-transform:uppercase;font-weight:700">Grand total</div>
            <div class="total">₹{{ number_format((float) $order->total, 2) }}</div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="od-card h-100 mb-0">
                        <div class="od-card-head">
                            <span><i class="fas fa-user"></i> Customer</span>
                            @if($order->user)
                                <a href="{{ route('admin.users.show', $order->user, false) }}" class="btn btn-outline-success btn-sm py-0 px-2" style="font-size:10px">Profile</a>
                            @endif
                        </div>
                        <div class="od-card-body">
                            @if($order->user)
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <div class="od-av">
                                        @if($order->user->profile_image)<img src="{{ $order->user->profile_image }}" alt="">@else{{ strtoupper(substr($order->user->name, 0, 1)) }}@endif
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size:14px">{{ $order->user->name }}</div>
                                        <div class="text-muted">Customer ID #{{ $order->user->id }}</div>
                                    </div>
                                </div>
                            @endif
                            <dl class="od-kv">
                                <dt>Email</dt><dd>{{ $order->user?->email ?? '—' }}</dd>
                                <dt>Account phone</dt><dd>{{ $order->user?->phone ?? '—' }}</dd>
                                <dt>Wallet</dt><dd>₹{{ number_format((float) ($order->user?->wallet_balance ?? 0), 2) }}</dd>
                                @if($customerStats)
                                    <dt>Total orders</dt><dd>{{ $customerStats['orders'] }}</dd>
                                    <dt>Lifetime spend</dt><dd>₹{{ number_format($customerStats['spent'], 2) }}</dd>
                                @endif
                                <dt>Registered</dt><dd>{{ $order->user?->created_at?->format('d M Y') ?? '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="od-card h-100 mb-0">
                        <div class="od-card-head"><span><i class="fas fa-truck"></i> Shipping</span></div>
                        <div class="od-card-body">
                            <dl class="od-kv">
                                <dt>Deliver to</dt><dd class="fw-semibold">{{ $order->user?->name ?? 'Customer' }}</dd>
                                <dt>Phone</dt><dd>{{ $order->phone ?: '—' }}</dd>
                                <dt>Address</dt><dd>{{ $order->delivery_address ?: '—' }}</dd>
                                <dt>Post office</dt><dd>{{ $order->post_office ?: '—' }}</dd>
                                <dt>City / State</dt><dd>{{ $order->city ?: '—' }}, {{ $order->state ?: '—' }}</dd>
                                <dt>Pincode</dt><dd>{{ $order->pincode ?: '—' }}</dd>
                                <dt>Type</dt><dd class="text-capitalize">{{ $order->delivery_schedule_type }}</dd>
                                @if($order->delivery_schedule_type === 'scheduled')
                                    <dt>Date / slot</dt><dd>{{ $order->delivery_date?->format('d M Y') }} · {{ $slot ?: $order->delivery_time }}</dd>
                                @endif
                                <dt>Notes</dt><dd>{{ $order->customer_notes ?: '—' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="od-card mt-3">
                <div class="od-card-head"><span><i class="fas fa-box"></i> Products ({{ $order->items->count() }})</span></div>
                <div class="od-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 od-items">
                            <thead>
                                <tr><th class="ps-3">Product</th><th>Weight</th><th class="text-center">Qty</th><th class="text-end">Unit</th><th class="text-end pe-3">Total</th></tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    @php $img = $item->product?->images->first()?->url(); @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                @if($img)
                                                    <img src="{{ $img }}" alt="{{ $item->product_name }}" class="od-thumb js-img-zoom" data-full="{{ $img }}"
                                                         data-caption="{{ $item->product_name }} · {{ $item->weight_label }} × {{ $item->quantity }}">
                                                @else
                                                    <span class="od-thumb-empty"><i class="bi bi-image"></i></span>
                                                @endif
                                                <div>
                                                    @if($item->product)
                                                        <a href="{{ route('admin.products.show', $item->product, false) }}" class="fw-bold text-dark text-decoration-none">{{ $item->product_name }}</a>
                                                    @else
                                                        <span class="fw-bold">{{ $item->product_name }}</span>
                                                    @endif
                                                    <div class="text-muted" style="font-size:11px">
                                                        {{ $item->product?->category?->name ?? '—' }} · Product #{{ $item->product_id ?? '—' }}
                                                    </div>
                                                    @if($item->preference_note)<div class="text-muted" style="font-size:11px">Note: {{ $item->preference_note }}</div>@endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $item->weight_label }}</td>
                                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                        <td class="text-end">₹{{ number_format((float) $item->unit_price, 2) }}</td>
                                        <td class="text-end pe-3 fw-bold">₹{{ number_format((float) $item->unit_price * $item->quantity, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="od-card h-100 mb-0">
                        <div class="od-card-head"><span><i class="fas fa-credit-card"></i> Payment</span></div>
                        <div class="od-card-body">
                            <dl class="od-kv">
                                <dt>Method</dt><dd class="text-capitalize">{{ $order->payment_method }}</dd>
                                <dt>Payment ID</dt><dd><code>{{ $order->cashfree_payment_id ?: '—' }}</code></dd>
                                <dt>Status</dt><dd>{{ $statusLabel }}</dd>
                                <dt>Bill emailed</dt><dd>{{ $order->bill_emailed_at?->format('d M Y, h:i A') ?? 'No' }}</dd>
                                @if($order->isCancelled())
                                    <dt>Cancelled</dt><dd>{{ $order->cancelled_at?->format('d M Y, h:i A') ?? '—' }}</dd>
                                    <dt>Reason</dt><dd>{{ $order->cancel_reason ?: '—' }}</dd>
                                    <dt>Refund</dt><dd>₹{{ number_format($order->refundableAmount(), 2) }} → wallet</dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="od-card h-100 mb-0">
                        <div class="od-card-head"><span><i class="fas fa-receipt"></i> Bill summary</span></div>
                        <div class="od-card-body">
                            <table class="w-100 od-sum">
                                <tr><td>Subtotal</td><td class="text-end">₹{{ number_format($subtotal, 2) }}</td></tr>
                                @if((float) $order->discount_amount > 0)
                                    <tr><td>Coupon {{ $order->coupon_code }}</td><td class="text-end text-danger">−₹{{ number_format((float) $order->discount_amount, 2) }}</td></tr>
                                @endif
                                <tr><td>Delivery</td><td class="text-end">{{ (float) $order->delivery_fee > 0 ? '₹'.number_format((float) $order->delivery_fee, 2) : 'Free' }}</td></tr>
                                @if((float) $order->wallet_amount > 0)
                                    <tr><td>Wallet used</td><td class="text-end text-danger">−₹{{ number_format((float) $order->wallet_amount, 2) }}</td></tr>
                                @endif
                                <tr class="grand"><td>Grand total</td><td class="text-end">₹{{ number_format((float) $order->total, 2) }}</td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="od-card" style="position:sticky;top:80px">
                <div class="od-card-head"><span><i class="fas fa-file-invoice"></i> Client bill</span></div>
                <div class="od-card-body od-bill-wrap">
                    @include('Website.partials.food-bill', ['order' => $order])
                </div>
            </div>
        </div>
    </div>
</div>

@include('Admin.Orders.partials.image-modal')
@endsection
