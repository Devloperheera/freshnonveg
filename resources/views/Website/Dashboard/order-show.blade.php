@extends('Website.Dashboard.layout')

@section('title', 'Order '.$order->order_number.' — FreshNonVeg')
@section('dashboard_title', 'Order details')

@php
    $isPaid = $order->isPaid();
    $isCancelled = $order->isCancelled();
    $deadline = $order->cancelDeadline();
    $canCancel = $order->canBeCancelled();
    $startsAt = $order->deliveryStartsAt();
    $slotText = $order->delivery_schedule_type === 'instant'
        ? 'Instant delivery ⚡'
        : trim(($order->delivery_date?->format('D, d M Y') ?? '').' · '.\App\Support\CheckoutDelivery::labelFor($order->delivery_time));
@endphp

@section('dashboard_actions')
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('dashboard.orders') }}" class="ud-btn ud-btn-ghost ud-btn-sm"><i class="bi bi-arrow-left"></i> My orders</a>
        @if($isPaid || $isCancelled)
            <a href="{{ route('orders.bill', $order) }}" class="ud-btn ud-btn-sm" target="_blank" rel="noopener"><i class="bi bi-receipt"></i> Print bill</a>
        @endif
    </div>
@endsection

@section('dashboard')
<div class="ud-card ud-order-hero">
    <div>
        <div class="ud-eyebrow mb-1">Order number</div>
        <div class="ud-order-no">{{ $order->order_number }}</div>
        <div class="ud-row-sub">{{ $order->created_at?->format('d M Y, h:i A') }} · {{ $order->items->count() }} items</div>
    </div>
    <div class="text-end">
        @include('Website.Dashboard.partials.order-status', ['status' => $order->status])
        <div class="ud-order-total">₹{{ number_format((float) $order->total, 2) }}</div>
        <div class="ud-row-sub">Paid online{{ (float) $order->wallet_amount > 0 ? ' + ₹'.number_format((float) $order->wallet_amount, 2).' wallet' : '' }}</div>
    </div>
</div>

<div class="ud-grid ud-grid-wide">
    <div>
        {{-- Timeline --}}
        <div class="ud-card">
            <div class="ud-card-head"><h2>Order status</h2></div>
            <ol class="ud-timeline">
                <li class="done">
                    <span class="ud-tl-dot"><i class="bi bi-bag-check"></i></span>
                    <div><strong>Order placed</strong><span>{{ $order->created_at?->format('d M, h:i A') }}</span></div>
                </li>
                <li class="{{ $isPaid || $isCancelled ? 'done' : '' }}">
                    <span class="ud-tl-dot"><i class="bi bi-credit-card"></i></span>
                    <div><strong>Payment {{ $isPaid || $isCancelled ? 'successful' : 'pending' }}</strong><span>Online · Cashfree</span></div>
                </li>
                @if($isCancelled)
                    <li class="done cancelled">
                        <span class="ud-tl-dot"><i class="bi bi-x-lg"></i></span>
                        <div><strong>Order cancelled</strong><span>{{ $order->cancelled_at?->format('d M, h:i A') }}</span></div>
                    </li>
                    <li class="done">
                        <span class="ud-tl-dot"><i class="bi bi-wallet2"></i></span>
                        <div><strong>₹{{ number_format($order->refundableAmount(), 2) }} wallet me refund</strong><span>Agle order me use karein</span></div>
                    </li>
                @else
                    <li class="{{ $isPaid ? 'current' : '' }}">
                        <span class="ud-tl-dot"><i class="bi bi-box-seam"></i></span>
                        <div><strong>Packing fresh</strong><span>Order confirm hone ke baad</span></div>
                    </li>
                    <li>
                        <span class="ud-tl-dot"><i class="bi bi-truck"></i></span>
                        <div><strong>Delivery</strong><span>{{ $slotText }}</span></div>
                    </li>
                @endif
            </ol>
        </div>

        {{-- Cancellation --}}
        <div class="ud-card ud-cancel-card {{ $canCancel ? 'is-open' : '' }}">
            <div class="ud-card-head"><h2>Cancellation</h2></div>
            @if($isCancelled)
                <p class="mb-1"><strong>Ye order cancel ho chuka hai.</strong></p>
                @if($order->cancel_reason)
                    <p class="ud-row-sub mb-1">Reason: {{ $order->cancel_reason }}</p>
                @endif
                <p class="ud-row-sub mb-0">₹{{ number_format($order->refundableAmount(), 2) }} aapke <a href="{{ route('dashboard.wallet') }}">wallet</a> me refund ho gaye.</p>
            @elseif($canCancel)
                <div class="ud-cancel-deadline">
                    <i class="bi bi-alarm"></i>
                    <div>
                        <div>Is order ko <strong>{{ $deadline->format('d M Y, h:i A') }}</strong> tak cancel kar sakte hain</div>
                        <div class="ud-countdown" data-deadline="{{ $deadline->toIso8601String() }}">—</div>
                    </div>
                </div>
                <p class="ud-row-sub">{{ $order->cancelPolicyText() }} Cancel karne par ₹{{ number_format($order->refundableAmount(), 2) }} turant aapke wallet me aa jayenge.</p>
                <form method="POST" action="{{ route('dashboard.orders.cancel', $order) }}" class="ud-form" id="cancelOrderForm">
                    @csrf
                    <label class="form-label" for="cancelReason">Cancel reason *</label>
                    <select name="cancel_reason" id="cancelReason" class="form-select mb-2" required>
                        <option value="">Reason chunein</option>
                        <option>Galti se order ho gaya</option>
                        <option>Delivery time suit nahi kar raha</option>
                        <option>Address galat hai</option>
                        <option>Items badalne hain</option>
                        <option>Koi aur reason</option>
                    </select>
                    <button type="submit" class="ud-btn ud-btn-danger w-100"><i class="bi bi-x-circle"></i> Cancel order</button>
                </form>
            @elseif($isPaid && $deadline)
                <div class="ud-cancel-deadline closed">
                    <i class="bi bi-lock"></i>
                    <div>Cancel window <strong>{{ $deadline->format('d M Y, h:i A') }}</strong> ko band ho gaya.</div>
                </div>
                <p class="ud-row-sub mb-2">{{ $order->cancelPolicyText() }}</p>
                <a href="{{ route('dashboard.complaints', ['order' => $order->id]) }}" class="ud-btn ud-btn-ghost ud-btn-sm">Problem hai? Complaint karein</a>
            @else
                <p class="ud-row-sub mb-0">Payment complete hone ke baad hi cancel option milega.</p>
            @endif
        </div>

        {{-- Items + rating --}}
        <div class="ud-card">
            <div class="ud-card-head"><h2>Items &amp; rating</h2></div>
            @foreach($order->items as $line)
                @php $review = $line->product_id ? ($myReviews[$line->product_id] ?? null) : null; @endphp
                <div class="ud-show-item">
                    <div class="ud-order-line">
                        @include('Website.Dashboard.partials.item-thumb', ['line' => $line])
                        <div class="ud-order-line-info">
                            <div class="ud-row-title">{{ $line->product_name }}</div>
                            <div class="ud-row-sub">
                                @if($line->weight_label){{ $line->weight_label }} · @endif ₹{{ number_format((float) $line->unit_price, 2) }} × {{ $line->quantity }}
                            </div>
                            @if($line->preference_note)
                                <div class="ud-row-sub"><i class="bi bi-chat-square-text"></i> {{ $line->preference_note }}</div>
                            @endif
                        </div>
                        <div class="ud-row-amount">₹{{ number_format((float) $line->unit_price * (int) $line->quantity, 2) }}</div>
                    </div>

                    @if($isPaid && $line->product_id && $line->product)
                        @if($review)
                            <div class="ud-show-rated">
                                <span>Aapki rating</span>
                                @include('Website.Dashboard.partials.stars', ['value' => $review->rating])
                            </div>
                        @else
                            <form method="POST" action="{{ route('dashboard.ratings.store', $line->product) }}" class="ud-show-rate">
                                @csrf
                                <div class="ud-star-input" role="radiogroup" aria-label="Rating">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" name="rating" value="{{ $i }}" id="os-star-{{ $line->id }}-{{ $i }}" required>
                                        <label for="os-star-{{ $line->id }}-{{ $i }}" title="{{ $i }} star"><i class="bi bi-star-fill"></i></label>
                                    @endfor
                                </div>
                                <input type="text" name="review" maxlength="1000" class="form-control form-control-sm" placeholder="Review (optional)">
                                <button type="submit" class="ud-btn ud-btn-sm">Rate</button>
                            </form>
                            <div class="ud-row-sub mt-1">Har product par rating sirf ek baar di ja sakti hai.</div>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Delivery --}}
        <div class="ud-card">
            <div class="ud-card-head"><h2>Delivery details</h2></div>
            <div class="ud-detail-grid">
                <div><span>Delivery</span><strong>{{ $slotText }}</strong></div>
                <div><span>Phone</span><strong>{{ $order->phone ?: '—' }}</strong></div>
                <div class="full"><span>Address</span><strong>{{ $order->delivery_address ?: '—' }}</strong></div>
                @if($order->customer_notes)
                    <div class="full"><span>Notes</span><strong>{{ $order->customer_notes }}</strong></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Food bill --}}
    <div>
        <div class="ud-card ud-bill-card">
            <div class="ud-card-head">
                <h2>Food bill</h2>
                @if($isPaid || $isCancelled)
                    <a href="{{ route('orders.bill', $order) }}" target="_blank" rel="noopener"><i class="bi bi-printer"></i> Print</a>
                @endif
            </div>
            @if($isPaid || $isCancelled)
                @include('Website.partials.food-bill', ['order' => $order])
            @else
                <p class="ud-row-sub mb-0">Payment complete hone ke baad bill yahan dikhega.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var el = document.querySelector('.ud-countdown');
    if (el) {
        var deadline = new Date(el.dataset.deadline).getTime();
        var tick = function () {
            var diff = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
            if (diff <= 0) {
                el.textContent = 'Cancel window band ho gaya';
                var form = document.getElementById('cancelOrderForm');
                if (form) form.querySelector('button').disabled = true;
                return;
            }
            var d = Math.floor(diff / 86400), h = Math.floor(diff % 86400 / 3600), m = Math.floor(diff % 3600 / 60), s = diff % 60;
            el.textContent = (d ? d + 'd ' : '') + String(h).padStart(2, '0') + 'h ' + String(m).padStart(2, '0') + 'm ' + String(s).padStart(2, '0') + 's baaki';
            setTimeout(tick, 1000);
        };
        tick();
    }
    var cancelForm = document.getElementById('cancelOrderForm');
    if (cancelForm) {
        cancelForm.addEventListener('submit', function (e) {
            if (!confirm('Kya aap sach me ye order cancel karna chahte hain? Amount wallet me refund hoga.')) e.preventDefault();
        });
    }
})();
</script>
@endpush
