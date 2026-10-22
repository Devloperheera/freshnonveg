@extends('Website.Dashboard.layout')

@section('title', 'My Orders — FreshNonVeg')
@section('dashboard_title', 'My orders')

@section('dashboard')
<div class="ud-card">
    @forelse($orders as $order)
        @php $isPaid = in_array($order->status, ['paid', 'confirmed'], true); @endphp
        <div class="ud-order">
            <div class="ud-order-top">
                <div>
                    <div class="ud-row-title">{{ $order->order_number }}</div>
                    <div class="ud-row-sub">{{ $order->created_at?->format('d M Y, h:i A') }}</div>
                </div>
                @include('Website.Dashboard.partials.order-status', ['status' => $order->status])
            </div>
            <div class="ud-order-lines">
                @foreach($order->items as $line)
                    <div class="ud-order-line">
                        @include('Website.Dashboard.partials.item-thumb', ['line' => $line])
                        <div class="ud-order-line-info">
                            <div class="ud-row-title">{{ $line->product_name }}</div>
                            <div class="ud-row-sub">
                                @if($line->weight_label){{ $line->weight_label }} · @endif Qty {{ $line->quantity }}
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="ud-row-amount">₹{{ number_format((float) $line->unit_price * (int) $line->quantity, 2) }}</div>
                            @if($isPaid && $line->product_id)
                                @if(isset($myReviews[$line->product_id]))
                                    @include('Website.Dashboard.partials.stars', ['value' => $myReviews[$line->product_id]->rating, 'class' => 'ud-stars-sm'])
                                @else
                                    <a href="{{ route('dashboard.ratings') }}#rate-{{ $line->product_id }}" class="ud-rate-link"><i class="bi bi-star"></i> Rate</a>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @if($order->canBeCancelled())
                <div class="ud-cancel-hint"><i class="bi bi-alarm"></i> {{ $order->cancelDeadline()->format('d M, h:i A') }} tak cancel kar sakte hain</div>
            @endif
            <div class="ud-order-foot">
                <strong>₹{{ number_format((float) $order->total, 2) }}</strong>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('dashboard.orders.show', $order) }}" class="ud-btn ud-btn-sm"><i class="bi bi-eye"></i> View order</a>
                    @if($isPaid || $order->isCancelled())
                        <a href="{{ route('orders.bill', $order) }}" class="ud-btn ud-btn-ghost ud-btn-sm"><i class="bi bi-receipt"></i> Food bill</a>
                    @endif
                    <a href="{{ route('dashboard.complaints', ['order' => $order->id]) }}" class="ud-btn ud-btn-ghost ud-btn-sm">Complaint</a>
                </div>
            </div>
        </div>
    @empty
        <div class="ud-empty">
            <i class="bi bi-bag"></i>
            <p>Abhi koi order nahi hai.</p>
            <a href="{{ route('shop.index') }}" class="ud-btn">Shop now</a>
        </div>
    @endforelse

    @if($orders->hasPages())
        <div class="mt-3">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
