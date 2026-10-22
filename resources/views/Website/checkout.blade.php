@extends('Website.Layout.master')

@section('title', 'Checkout — FreshNonVeg')

@section('content')
<section class="cart-page checkout-page py-4 py-md-5">
    <div class="container">
        <div class="cart-page-header mb-4">
            <h1 class="cart-page-title"><i class="bi bi-bag-check me-2"></i>Checkout</h1>
            <p class="cart-page-sub">Delivery schedule, address aur payment confirm karein</p>
            <div class="cart-page-header-line"></div>
        </div>

        <form method="POST" action="{{ route('checkout.store') }}" class="checkout-page-layout" id="checkoutForm"
              data-subtotal="{{ $subtotal }}"
              data-instant-fee="{{ $instantDeliveryFee }}"
              data-pincode-url="{{ route('checkout.pincode', ['pincode' => '__PINCODE__']) }}"
              data-address-suggest-url="{{ route('checkout.address-suggest') }}"
              data-coupon-url="{{ route('checkout.coupon') }}">
            @csrf
            <div class="checkout-main-col">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Delivery Schedule</h2>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="checkout-schedule-option @if(old('delivery_schedule_type', 'scheduled') === 'scheduled') active @endif">
                                    <input type="radio" name="delivery_schedule_type" id="scheduleScheduled" value="scheduled"
                                           @checked(old('delivery_schedule_type', 'scheduled') === 'scheduled')>
                                    <span class="fw-bold d-block">Scheduled delivery</span>
                                    <span class="small text-muted">Date &amp; time slot choose karein</span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="checkout-schedule-option @if(old('delivery_schedule_type') === 'instant') active @endif">
                                    <input type="radio" name="delivery_schedule_type" id="scheduleInstant" value="instant"
                                           @checked(old('delivery_schedule_type') === 'instant')>
                                    <span class="fw-bold d-block">Instant delivery ⚡</span>
                                    <span class="small text-muted">+ ₹{{ number_format($instantDeliveryFee, 0) }} extra charge</span>
                                </label>
                            </div>
                        </div>
                        @error('delivery_schedule_type')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                        <div id="scheduledFields" class="row g-3 mb-0">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="deliveryDate">Delivery date *</label>
                                <input type="date" name="delivery_date" id="deliveryDate" class="form-control @error('delivery_date') is-invalid @enderror"
                                       value="{{ old('delivery_date') }}">
                                @error('delivery_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="deliveryTime">Time slot *</label>
                                <select name="delivery_time" id="deliveryTime" class="form-select @error('delivery_time') is-invalid @enderror">
                                    <option value="">Slot choose karein</option>
                                    @foreach($timeSlots as $value => $label)
                                        <option value="{{ $value }}" @selected(old('delivery_time') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @if($timeSlots === [])
                                    <p class="small text-danger mb-0 mt-1">Abhi koi delivery slot active nahi. Admin se slot on karwayein.</p>
                                @endif
                                @error('delivery_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Delivery address</h2>
                        <div id="deliveryComingSoon" class="checkout-coming-soon" role="status" @unless($errors->has('pincode')) hidden @endunless>
                            <div class="checkout-coming-soon__icon" aria-hidden="true"><i class="bi bi-geo-alt-fill"></i></div>
                            <div>
                                <p class="checkout-coming-soon__hi mb-1">{{ \App\Services\DeliveryZoneChecker::COMING_SOON_HI }}</p>
                                <p class="checkout-coming-soon__en mb-0">{{ \App\Services\DeliveryZoneChecker::COMING_SOON_EN }}</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="checkoutPhone">Mobile / Phone *</label>
                            <input type="text" name="phone" id="checkoutPhone" class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}" required maxlength="20">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="checkoutPincode">Pincode *</label>
                                <input type="text" name="pincode" id="checkoutPincode" inputmode="numeric" maxlength="6"
                                       class="form-control @error('pincode') is-invalid @enderror"
                                       value="{{ old('pincode') }}" placeholder="6-digit" required>
                                <div id="pincodeStatus" class="small mt-1 text-muted"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="checkoutState">State *</label>
                                <input type="text" name="state" id="checkoutState" class="form-control @error('state') is-invalid @enderror"
                                       value="{{ old('state') }}" required>
                                @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold" for="checkoutCity">City / District *</label>
                                <input type="text" name="city" id="checkoutCity" class="form-control @error('city') is-invalid @enderror"
                                       value="{{ old('city') }}" required>
                                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3 checkout-address-wrap">
                            <label class="form-label fw-semibold" for="checkoutAddress">House / Street / Full address *</label>
                            <input type="text" name="delivery_address" id="checkoutAddress" autocomplete="off"
                                   class="form-control @error('delivery_address') is-invalid @enderror"
                                   value="{{ old('delivery_address', $user->address) }}" required maxlength="2000"
                                   placeholder="Type karein — free address suggestions aayenge">
                            <div id="addressSuggestDropdown" class="checkout-address-dropdown" hidden></div>
                            @error('delivery_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="orderNotes">Order notes (optional)</label>
                            <textarea name="order_notes" id="orderNotes" rows="2" class="form-control" maxlength="1000"
                                      placeholder="Gate code, call before delivery, etc.">{{ old('order_notes') }}</textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-semibold">Payment</label>
                            @error('payment_method')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                            @error('payment')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                            <input type="hidden" name="payment_method" value="cashfree">
                            <div class="fnv-field-box p-3">
                                <p class="fw-bold mb-1 mb-0"><i class="bi bi-shield-lock me-1"></i> Online payment only</p>
                                <p class="small text-muted mb-0">UPI, Card, Netbanking — <strong>Cashfree</strong> secure checkout. COD available nahi hai.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="{{ route('cart') }}" class="btn cart-btn-outline"><i class="bi bi-arrow-left me-1"></i> Back to cart</a>
            </div>

            <div class="checkout-summary-col">
                <div class="checkout-summary-spacer" aria-hidden="true" hidden></div>
                <div class="cart-summary card border-0 shadow-sm checkout-summary-pin">
                    <div class="card-body p-4">
                        <h2 class="cart-summary-title h5 mb-3">Your order ({{ $items->count() }} items)</h2>
                        <ul class="list-unstyled checkout-item-list mb-3">
                            @foreach($items as $item)
                                <li class="checkout-item-row d-flex gap-2 mb-2 pb-2 border-bottom">
                                    @php $img = $item->imageUrl(); @endphp
                                    @if($img)
                                        <img src="{{ $img }}" alt="" class="checkout-item-thumb" width="48" height="48">
                                    @endif
                                    <div class="flex-grow-1 small">
                                        <div class="fw-bold">{{ $item->product_name }}</div>
                                        @if($item->weight_label)
                                            <div class="text-muted">{{ $item->weight_label }} × {{ $item->quantity }}</div>
                                        @else
                                            <div class="text-muted">Qty {{ $item->quantity }}</div>
                                        @endif
                                    </div>
                                    <div class="fw-bold small">₹{{ number_format($item->lineTotal(), 2) }}</div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="cart-summary-line">
                            <span>Subtotal</span>
                            <span>₹{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="cart-summary-line" id="checkoutDeliveryFeeRow" hidden>
                            <span>Instant delivery</span>
                            <span id="checkoutDeliveryFee">₹0.00</span>
                        </div>
                        <div class="cart-summary-line">
                            <span>Shipping</span>
                            <span class="text-success fw-semibold">Free</span>
                        </div>
                        <div class="cart-summary-line">
                            <span>Tax</span>
                            <span>₹{{ number_format($tax, 2) }}</span>
                        </div>
                        <div class="checkout-coupon mt-3 mb-2">
                            <label class="form-label fw-semibold mb-1" for="checkoutCoupon">Discount coupon</label>
                            <div class="d-flex gap-2">
                                <input type="text" name="coupon_code" id="checkoutCoupon" class="form-control" maxlength="40" autocomplete="off"
                                       placeholder="Coupon code" value="{{ old('coupon_code') }}" style="text-transform:uppercase">
                                <button type="button" class="btn cart-btn-outline" id="checkoutCouponApply">Apply</button>
                            </div>
                            <div id="checkoutCouponMsg" class="small mt-1"></div>
                            @error('coupon_code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="cart-summary-line" id="checkoutDiscountRow" hidden>
                            <span>Discount</span>
                            <span id="checkoutDiscount" class="text-success fw-semibold">-₹0.00</span>
                        </div>
                        @if($walletBalance > 0)
                            <label class="checkout-wallet mt-2 mb-2" for="checkoutUseWallet">
                                <input type="checkbox" name="use_wallet" value="1" id="checkoutUseWallet"
                                       data-wallet-balance="{{ number_format($walletBalance, 2, '.', '') }}" @checked(old('use_wallet'))>
                                <span>
                                    <strong><i class="bi bi-wallet2 me-1"></i>Wallet use karein</strong>
                                    <small>Balance ₹{{ number_format($walletBalance, 2) }} · minimum ₹1 online pay hoga</small>
                                </span>
                            </label>
                            <div class="cart-summary-line" id="checkoutWalletRow" hidden>
                                <span>Wallet</span>
                                <span id="checkoutWallet" class="text-success fw-semibold">-₹0.00</span>
                            </div>
                        @endif
                        <div class="cart-summary-line cart-summary-total">
                            <span>Total</span>
                            <span id="checkoutGrandTotal">₹{{ number_format($total, 2) }}</span>
                        </div>
                        <button type="submit" class="cart-checkout-btn w-100 mt-3" id="checkoutPlaceOrderBtn">
                            <i class="bi bi-credit-card me-2"></i>Pay &amp; Place Order
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
@php
    $checkoutJsVer = @filemtime(public_path('website_assets/js/checkout.js')) ?: time();
@endphp
<script src="{{ rtrim(request()->root(), '/') }}/website_assets/js/checkout.js?v={{ $checkoutJsVer }}"></script>
@endpush
