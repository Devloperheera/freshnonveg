@extends('Website.Layout.master')

@section('title', 'Order Successful — FreshNonVeg')

@section('content')
<section class="cart-page order-success-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm text-center p-4 p-md-5 order-success-card">
                    <div class="order-success-icon" aria-hidden="true"><i class="bi bi-check-lg"></i></div>
                    <h1 class="h3 fw-bold mb-2">Order place ho gaya!</h1>
                    <p class="text-muted mb-1">Your order has been placed successfully.</p>
                    <p class="mb-4">Dhanyavaad {{ auth()->user()->name }} — hum jald delivery karenge.</p>

                    <div class="text-start bg-light rounded p-3 mb-4 small">
                        <p class="mb-1"><strong>Order #:</strong> {{ $order->order_number }}</p>
                        @if((float) $order->discount_amount > 0)
                            <p class="mb-1"><strong>Coupon:</strong> {{ $order->coupon_code }} (−₹{{ number_format((float) $order->discount_amount, 2) }})</p>
                        @endif
                        @if((float) $order->wallet_amount > 0)
                            <p class="mb-1"><strong>Wallet used:</strong> −₹{{ number_format((float) $order->wallet_amount, 2) }}</p>
                        @endif
                        <p class="mb-1"><strong>Total:</strong> ₹{{ number_format((float) $order->total, 2) }}
                            @if((float) $order->delivery_fee > 0)
                                <span class="text-muted">(incl. ₹{{ number_format((float) $order->delivery_fee, 0) }} instant delivery)</span>
                            @endif
                        </p>
                        <p class="mb-1"><strong>Delivery:</strong>
                            @if($order->delivery_schedule_type === 'instant')
                                Instant delivery ⚡
                            @else
                                Scheduled —
                                {{ $order->delivery_date?->format('d M Y') }}
                                {{ \App\Support\CheckoutDelivery::labelFor($order->delivery_time) }}
                            @endif
                        </p>
                        @if($order->pincode)
                            <p class="mb-1"><strong>Pincode:</strong> {{ $order->pincode }} · {{ $order->city }}, {{ $order->state }}</p>
                        @endif
                        <p class="mb-1"><strong>Payment:</strong> Online (Cashfree) — {{ $order->status === 'paid' ? 'Paid' : ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                        <p class="mb-1"><strong>Phone:</strong> {{ $order->phone }}</p>
                        <p class="mb-0"><strong>Address:</strong> {{ $order->delivery_address }}</p>
                    </div>

                    <ul class="list-unstyled text-start mb-4">
                        @foreach($order->items as $line)
                            <li class="d-flex justify-content-between border-bottom py-2 small">
                                <span>{{ $line->product_name }} @if($line->weight_label)({{ $line->weight_label }})@endif × {{ $line->quantity }}</span>
                                <span class="fw-bold">₹{{ number_format((float) $line->unit_price * (int) $line->quantity, 2) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="d-flex flex-wrap gap-2 justify-content-center order-success-actions mb-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-fun btn-red">
                            <i class="bi bi-grid-1x2 me-1"></i> Go to Dashboard
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-fun btn-yel">
                            <i class="bi bi-house-door me-1"></i> Website Home Page
                        </a>
                    </div>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <a href="{{ route('orders.bill', $order) }}" class="btn cart-btn-outline">Food bill dekhein</a>
                        <a href="{{ route('dashboard.orders') }}" class="btn cart-btn-outline">My orders</a>
                    </div>
                    @if($order->bill_emailed_at && $order->user?->email)
                        <p class="small text-muted mt-3 mb-0">Bill {{ $order->user->email }} par bhej di gayi hai.</p>
                    @elseif(! $order->user?->email)
                        <p class="small text-muted mt-3 mb-0">Email nahi hai, isliye bill sirf dashboard par hai. Profile mein email add kar dein.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
