@php
    $subtotal = $order->items->sum(fn ($line) => (float) $line->unit_price * (int) $line->quantity);
    $discount = (float) $order->discount_amount;
    $delivery = (float) $order->delivery_fee;
    $slot = $order->delivery_schedule_type === 'instant'
        ? 'Instant delivery'
        : trim(($order->delivery_date?->format('d M Y') ?? '').' '.\App\Support\CheckoutDelivery::labelFor($order->delivery_time));
    $co = \App\Support\Company::billHeader();
@endphp
<div class="food-bill" style="position:relative;overflow:hidden;max-width:420px;margin:0 auto;background:#fffef8;color:#1a1a1a;border:2px dashed #111827;border-radius:16px;font-family:'Courier New',Courier,monospace;">
    <div aria-hidden="true" style="position:absolute;inset:0;z-index:0;pointer-events:none;overflow:hidden;">
        <div style="position:absolute;left:-20%;top:18%;width:140%;text-align:center;font-size:34px;font-weight:900;letter-spacing:6px;color:rgba(17,24,39,.07);transform:rotate(-28deg);white-space:nowrap;">FRESH NON VEG</div>
        <div style="position:absolute;left:-20%;top:42%;width:140%;text-align:center;font-size:34px;font-weight:900;letter-spacing:6px;color:rgba(17,24,39,.07);transform:rotate(-28deg);white-space:nowrap;">FRESH NON VEG</div>
        <div style="position:absolute;left:-20%;top:66%;width:140%;text-align:center;font-size:34px;font-weight:900;letter-spacing:6px;color:rgba(17,24,39,.07);transform:rotate(-28deg);white-space:nowrap;">FRESH NON VEG</div>
        @if($order->status === 'cancelled')
            <div style="position:absolute;right:10px;top:84px;padding:6px 10px;border:3px solid rgba(107,114,128,.6);border-radius:8px;color:rgba(75,85,99,.8);font-weight:900;font-size:14px;transform:rotate(-18deg);letter-spacing:1px;">CANCELLED</div>
        @else
            <div style="position:absolute;right:18px;top:78px;width:78px;height:78px;border:3px solid rgba(220,38,38,.55);border-radius:50%;color:rgba(220,38,38,.7);font-weight:900;font-size:16px;line-height:72px;text-align:center;transform:rotate(-18deg);letter-spacing:1px;">PAID</div>
        @endif
    </div>

    <div style="position:relative;z-index:1;padding:22px 18px 18px;">
        <div style="text-align:center;">
            <img src="{{ asset('website_assets/image/logo.png') }}" alt="Fresh Non Veg" width="160" height="40" style="height:40px;width:auto;max-width:180px;object-fit:contain;">
            <div style="font-family:Georgia,'Times New Roman',serif;font-size:22px;font-weight:800;letter-spacing:1px;margin-top:6px;">{{ strtoupper($co['brand']) }}</div>
            <div style="font-size:12px;letter-spacing:2px;margin-top:2px;">FRESH MEAT · NEVER FROZEN</div>
            <div style="font-size:11px;margin-top:8px;line-height:1.45;color:#111827;">
                <div style="font-weight:700;">{{ $co['legal_name'] }}</div>
                <div>{{ $co['address'] }}</div>
                <div style="margin-top:3px;"><strong>GSTIN:</strong> {{ $co['gstin'] }} &nbsp;|&nbsp; <strong>PAN:</strong> {{ $co['pan'] }}</div>
                <div>State: {{ $co['state'] }} · {{ $co['registration_type'] }} registration</div>
            </div>
            <div style="margin:10px 0;border-top:1px dashed #111827;"></div>
            <div style="font-size:18px;font-weight:800;letter-spacing:3px;">TAX INVOICE / FOOD BILL</div>
            <div style="font-size:13px;margin-top:6px;">Order No. <strong>{{ $order->order_number }}</strong></div>
            <div style="font-size:12px;color:#374151;">{{ $order->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') }}</div>
        </div>

        <div style="margin:12px 0;border-top:1px dashed #111827;"></div>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;line-height:1.45;">
            <tr>
                <td style="width:92px;vertical-align:top;color:#4b5563;">Customer</td>
                <td style="font-weight:700;">{{ $order->user?->name ?? 'Customer' }}</td>
            </tr>
            <tr>
                <td style="vertical-align:top;color:#4b5563;">Phone</td>
                <td>{{ $order->phone ?: '—' }}</td>
            </tr>
            <tr>
                <td style="vertical-align:top;color:#4b5563;">Address</td>
                <td>{{ $order->delivery_address ?: '—' }}</td>
            </tr>
            <tr>
                <td style="vertical-align:top;color:#4b5563;">Delivery</td>
                <td>{{ $slot !== '' ? $slot : '—' }}</td>
            </tr>
        </table>

        <div style="margin:12px 0;border-top:1px dashed #111827;"></div>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
            <tr>
                <td style="font-weight:800;padding-bottom:6px;">ITEM</td>
                <td style="font-weight:800;text-align:center;width:36px;">QTY</td>
                <td style="font-weight:800;text-align:right;width:84px;">AMOUNT</td>
            </tr>
            @foreach($order->items as $line)
                @php $lineTotal = (float) $line->unit_price * (int) $line->quantity; @endphp
                <tr>
                    <td style="padding:6px 8px 6px 0;vertical-align:top;border-top:1px dotted #d1d5db;">
                        <div style="font-weight:700;">{{ $line->product_name }}</div>
                        @if($line->weight_label)
                            <div style="font-size:11px;color:#6b7280;">{{ $line->weight_label }}</div>
                        @endif
                        @if($line->preference_note)
                            <div style="font-size:11px;color:#6b7280;">{{ $line->preference_note }}</div>
                        @endif
                    </td>
                    <td style="text-align:center;vertical-align:top;padding-top:6px;border-top:1px dotted #d1d5db;">{{ $line->quantity }}</td>
                    <td style="text-align:right;vertical-align:top;padding-top:6px;border-top:1px dotted #d1d5db;">₹{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
        </table>

        <div style="margin:12px 0;border-top:1px dashed #111827;"></div>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;">
            <tr>
                <td style="padding:3px 0;">Subtotal</td>
                <td style="text-align:right;">₹{{ number_format($subtotal, 2) }}</td>
            </tr>
            @if($discount > 0)
                <tr>
                    <td style="padding:3px 0;">Coupon {{ $order->coupon_code }}</td>
                    <td style="text-align:right;">−₹{{ number_format($discount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding:3px 0;">Delivery</td>
                <td style="text-align:right;">{{ $delivery > 0 ? '₹'.number_format($delivery, 2) : 'Free' }}</td>
            </tr>
            @if((float) $order->wallet_amount > 0)
                <tr>
                    <td style="padding:3px 0;">Wallet used</td>
                    <td style="text-align:right;">−₹{{ number_format((float) $order->wallet_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding-top:8px;font-size:16px;font-weight:800;">GRAND TOTAL</td>
                <td style="padding-top:8px;text-align:right;font-size:16px;font-weight:800;">₹{{ number_format((float) $order->total, 2) }}</td>
            </tr>
        </table>

        <div style="margin:12px 0;border-top:1px dashed #111827;"></div>
        <div style="text-align:center;font-size:12px;letter-spacing:1px;">
            PAYMENT: <strong>{{ strtoupper($order->status === 'paid' || $order->status === 'confirmed' ? 'PAID' : str_replace('_', ' ', $order->status)) }}</strong>
            · ONLINE
        </div>
        @if($order->status === 'cancelled')
            <div style="text-align:center;font-size:12px;margin-top:6px;">
                Refund ₹{{ number_format($order->refundableAmount(), 2) }} → FreshNonVeg Wallet
            </div>
        @endif
        <div style="text-align:center;font-size:11px;margin-top:10px;line-height:1.5;">
            This is a computer generated invoice of {{ $co['legal_name'] }}.<br>
            GSTIN {{ $co['gstin'] }} · Prices inclusive of taxes as applicable.<br>
            Dhanyavaad! FreshNonVeg — ghar tak fresh.
        </div>
    </div>
</div>
