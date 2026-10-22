@php
    $subtotal = $order->items->sum(fn ($l) => (float) $l->unit_price * (int) $l->quantity);
    $slot = $order->delivery_schedule_type === 'instant'
        ? 'Instant delivery'
        : trim(($order->delivery_date?->format('d M Y') ?? '').' · '.(\App\Support\CheckoutDelivery::labelFor($order->delivery_time) ?: $order->delivery_time));
    $logoPath = public_path('website_assets/image/logo.png');
    $logo = is_file($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
    $cancelled = $order->status === 'cancelled';
    $co = \App\Support\Company::billHeader();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Bill {{ $order->order_number }}</title>
<style>
    @page { margin: 28px 30px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
    .wm { position: fixed; top: 38%; left: -10%; width: 120%; text-align: center; font-size: 64px; font-weight: bold; color: rgba(17,24,39,.06); transform: rotate(-28deg); letter-spacing: 8px; }
    .stamp { position: fixed; top: 110px; right: 30px; border: 3px solid {{ $cancelled ? '#6b7280' : '#dc2626' }}; color: {{ $cancelled ? '#6b7280' : '#dc2626' }}; padding: 6px 14px; border-radius: 8px; font-size: 18px; font-weight: bold; transform: rotate(-16deg); opacity: .75; letter-spacing: 2px; }
    .head { border-bottom: 2px dashed #111827; padding-bottom: 10px; margin-bottom: 12px; }
    .brand { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
    .muted { color: #6b7280; }
    .title { font-size: 15px; font-weight: bold; letter-spacing: 3px; text-align: right; }
    table { width: 100%; border-collapse: collapse; }
    .box td { vertical-align: top; width: 50%; padding: 0 8px 0 0; }
    .box h4 { margin: 0 0 4px; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #2BAE49; }
    .items { margin-top: 14px; }
    .items th { background: #2BAE49; color: #fff; font-size: 10px; text-transform: uppercase; padding: 6px; text-align: left; }
    .items td { border-bottom: 1px dotted #d1d5db; padding: 6px; vertical-align: middle; }
    .items .num { text-align: right; }
    .thumb { width: 38px; height: 38px; border-radius: 4px; }
    .sum { width: 46%; margin-left: 54%; margin-top: 10px; }
    .sum td { padding: 3px 0; }
    .sum .grand td { border-top: 2px solid #111827; padding-top: 6px; font-size: 14px; font-weight: bold; }
    .foot { margin-top: 22px; border-top: 2px dashed #111827; padding-top: 8px; text-align: center; font-size: 10px; }
</style>
</head>
<body>
    <div class="wm">FRESH NON VEG</div>
    <div class="stamp">{{ $cancelled ? 'CANCELLED' : ($order->isPaid() ? 'PAID' : strtoupper(str_replace('_', ' ', $order->status))) }}</div>

    <table class="head">
        <tr>
            <td>
                @if($logo)<img src="{{ $logo }}" alt="" style="height:38px"><br>@endif
                <span class="brand">{{ strtoupper($co['brand']) }}</span><br>
                <span class="muted">Fresh meat · Never frozen</span><br>
                <span style="font-size:10px;font-weight:bold;">{{ $co['legal_name'] }}</span><br>
                <span class="muted" style="font-size:9px;">{{ $co['address'] }}</span><br>
                <span style="font-size:10px;"><strong>GSTIN:</strong> {{ $co['gstin'] }} &nbsp; <strong>PAN:</strong> {{ $co['pan'] }}</span>
            </td>
            <td class="title">
                TAX INVOICE<br>
                <span style="font-size:10px;letter-spacing:1px;font-weight:bold;">FOOD BILL</span><br>
                <span style="font-size:11px;letter-spacing:0;font-weight:normal">Order No. <strong>{{ $order->order_number }}</strong></span><br>
                <span class="muted" style="font-size:10px;letter-spacing:0;font-weight:normal">{{ $order->created_at?->format('d M Y, h:i A') }}</span>
            </td>
        </tr>
    </table>

    <table class="box">
        <tr>
            <td>
                <h4>Bill to</h4>
                <strong>{{ $order->user?->name ?? 'Customer' }}</strong><br>
                {{ $order->user?->email }}<br>
                Phone: {{ $order->user?->phone ?? $order->phone }}<br>
                Customer ID: #{{ $order->user_id ?? '—' }}
            </td>
            <td>
                <h4>Ship to</h4>
                {{ $order->delivery_address ?: '—' }}<br>
                {{ $order->post_office ? $order->post_office.', ' : '' }}{{ $order->city }}, {{ $order->state }} — {{ $order->pincode }}<br>
                Phone: {{ $order->phone ?: '—' }}<br>
                Delivery: {{ $slot ?: '—' }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:46px"></th>
                <th>Product</th>
                <th>Weight</th>
                <th class="num">Qty</th>
                <th class="num">Unit</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                @php $uri = $item->product?->images->first()?->dataUri(); @endphp
                <tr>
                    <td>@if($uri)<img src="{{ $uri }}" class="thumb" alt="">@endif</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->product?->category)<br><span class="muted">{{ $item->product->category->name }}</span>@endif
                        @if($item->preference_note)<br><span class="muted">Note: {{ $item->preference_note }}</span>@endif
                    </td>
                    <td>{{ $item->weight_label }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td class="num">₹{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="num"><strong>₹{{ number_format((float) $item->unit_price * $item->quantity, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sum">
        <tr><td>Subtotal</td><td class="num" style="text-align:right">₹{{ number_format($subtotal, 2) }}</td></tr>
        @if((float) $order->discount_amount > 0)
            <tr><td>Coupon {{ $order->coupon_code }}</td><td style="text-align:right">−₹{{ number_format((float) $order->discount_amount, 2) }}</td></tr>
        @endif
        <tr><td>Delivery</td><td style="text-align:right">{{ (float) $order->delivery_fee > 0 ? '₹'.number_format((float) $order->delivery_fee, 2) : 'Free' }}</td></tr>
        @if((float) $order->wallet_amount > 0)
            <tr><td>Wallet used</td><td style="text-align:right">−₹{{ number_format((float) $order->wallet_amount, 2) }}</td></tr>
        @endif
        <tr class="grand"><td>GRAND TOTAL</td><td style="text-align:right">₹{{ number_format((float) $order->total, 2) }}</td></tr>
    </table>

    <div class="foot">
        Payment: <strong>{{ strtoupper($order->payment_method) }}</strong>
        @if($order->cashfree_payment_id) · Ref {{ $order->cashfree_payment_id }}@endif
        @if($cancelled)<br>Order cancelled — ₹{{ number_format($order->refundableAmount(), 2) }} FreshNonVeg wallet me refund.@endif
        <br>Sold by <strong>{{ $co['legal_name'] }}</strong> · GSTIN {{ $co['gstin'] }} · {{ $co['registration_type'] }} · {{ $co['state'] }}
        <br>This is a computer generated tax invoice. Prices inclusive of taxes as applicable.
        <br>Dhanyavaad! FreshNonVeg — ghar tak fresh.
    </div>
</body>
</html>
