@extends('Website.Layout.master')

@section('title', 'Your cart — FreshNonVeg')

@section('content')
<section class="cart-page py-4 py-md-5" id="cartPage" data-clear-url="{{ route('cart.clear') }}" data-cart-base="{{ url('/cart') }}">
    <div class="container">
        <div class="cart-page-header mb-4">
            <h1 class="cart-page-title"><i class="bi bi-cart3 me-2" aria-hidden="true"></i>Your cart</h1>
            <p class="cart-page-sub">Review and manage your items</p>
            <div class="cart-page-header-line"></div>
        </div>

        @if($items->isEmpty())
            <div class="cart-empty card border-0 shadow-sm text-center py-5 px-3">
                <p class="fs-5 mb-2">Cart खाली है</p>
                <p class="text-muted mb-4">Shop se fresh products add karein.</p>
                <a href="{{ route('shop.index') }}" class="btn btn-fun btn-red px-4">Continue Shopping</a>
            </div>
        @else
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="cart-table-wrap" id="cartTableWrap">
                        <div class="cart-table-head d-none d-md-grid">
                            <span>Product</span>
                            <span class="text-center">Price</span>
                            <span class="text-center">Quantity</span>
                            <span class="text-end">Total</span>
                        </div>

                        @foreach($items as $item)
                            @php
                                $imgUrl = $item->imageUrl();
                                $productUrl = $item->product_id ? route('products.show', $item->product_id) : route('shop.index');
                            @endphp
                            <div class="cart-table-row" data-cart-row-id="{{ $item->id }}" data-cart-qty="{{ $item->quantity }}">
                                <div class="cart-product-cell">
                                    <a href="{{ $productUrl }}" class="cart-product-thumb">
                                        @if($imgUrl)
                                            <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" loading="lazy">
                                        @else
                                            <span class="cart-product-thumb-fallback">🥩</span>
                                        @endif
                                    </a>
                                    <div class="cart-product-meta">
                                        <a href="{{ $productUrl }}" class="cart-product-name fnv-product-name">
                                            @include('Website.partials.nonveg-mark', ['size' => 'xs'])
                                            <span>{{ $item->product_name }}</span>
                                        </a>
                                        @if($item->weight_label)
                                            <p class="cart-product-sku mb-0">Weight: {{ $item->weight_label }}</p>
                                        @endif
                                        @if($item->product_id)
                                            <p class="cart-product-sku mb-0">SKU: FNV-{{ $item->product_id }}</p>
                                        @endif
                                        @if($item->preference_note)
                                            <p class="cart-product-pref mb-0">Preference: {{ $item->preference_note }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="cart-price-cell">
                                    <span class="cart-mobile-label d-md-none">Price</span>
                                    <span>₹{{ number_format((float) $item->unit_price, 2) }}</span>
                                </div>

                                <div class="cart-qty-cell">
                                    <span class="cart-mobile-label d-md-none">Quantity</span>
                                    <div class="cart-qty-stepper">
                                        <button type="button" class="cart-qty-btn" data-cart-qty="dec" aria-label="Decrease quantity" @disabled($item->quantity <= 1)>−</button>
                                        <span class="cart-qty-value" data-cart-qty-display>{{ $item->quantity }}</span>
                                        <button type="button" class="cart-qty-btn" data-cart-qty="inc" aria-label="Increase quantity" @disabled($item->quantity >= 99)>+</button>
                                    </div>
                                </div>

                                <div class="cart-total-cell">
                                    <span class="cart-mobile-label d-md-none">Total</span>
                                    <span class="cart-line-total" data-cart-line-total>₹{{ number_format($item->lineTotal(), 2) }}</span>
                                    <button type="button" class="cart-remove-btn" data-cart-remove="{{ $item->id }}" aria-label="Remove item">&times;</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="cart-actions-row mt-3">
                        <a href="{{ route('shop.index') }}" class="btn cart-btn-outline">
                            <i class="bi bi-arrow-left me-1"></i> Continue Shopping
                        </a>
                        <button type="button" class="btn cart-btn-danger" data-cart-clear>
                            <i class="bi bi-trash me-1"></i> Clear Cart
                        </button>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="cart-summary card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h2 class="cart-summary-title h5 mb-3">Order Summary</h2>
                            <div class="cart-summary-line">
                                <span>Subtotal</span>
                                <span data-cart-subtotal>₹{{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="cart-summary-line">
                                <span>Shipping</span>
                                <span class="text-success fw-semibold">Free</span>
                            </div>
                            <div class="cart-summary-line">
                                <span>Tax</span>
                                <span data-cart-tax>₹{{ number_format($tax, 2) }}</span>
                            </div>
                            <div class="cart-summary-line cart-summary-total">
                                <span>Total</span>
                                <span data-cart-total>₹{{ number_format($total, 2) }}</span>
                            </div>
                            <a href="{{ route('checkout') }}" class="cart-checkout-btn w-100 mt-3 d-inline-flex align-items-center justify-content-center">
                                <i class="bi bi-lock-fill me-2"></i>Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
