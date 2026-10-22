@extends('Website.Layout.master')

@section('title', ($product->meta_title ?: $product->name).' — FreshNonVeg')
@section('meta_description', $product->meta_description ?: Str::limit(strip_tags((string) $product->description), 160))
@section('meta_keywords', $product->meta_keywords)

@section('content')
@php
    $productImages = $product->images;
    $mainImage = $productImages->first();
    $defaultWeight = $product->weights->first();
    $shortDesc = $product->meta_description ?: Str::limit(strip_tags((string) $product->description), 120);
@endphp
<section class="product-detail-page">
    <div class="container product-detail-body py-3 py-md-4">
        <p class="mb-2 small">
            <a href="{{ route('home') }}" class="text-decoration-none fw-semibold">Home</a>
            <span class="text-muted"> / </span>
            <a href="{{ route('shop.index') }}" class="text-decoration-none fw-semibold">Shop</a>
        </p>

        <div class="row g-4 align-items-start">
            <div class="col-12 col-lg-6">
                <div class="product-gallery-wrap">
                    @include('Website.partials.nonveg-mark', ['size' => 'md'])
                    @if($productImages->isNotEmpty())
                        <div class="owl-carousel owl-theme product-image-carousel" id="productImageCarousel">
                            @foreach($productImages as $image)
                                <div class="item">
                                    <img src="{{ $image->url() }}" alt="{{ $product->name }} — photo {{ $loop->iteration }}" class="product-gallery-slide">
                                </div>
                            @endforeach
                        </div>
                        @if($productImages->count() > 1)
                            <div class="product-detail-thumbs-scroll mt-2">
                                <div class="product-detail-thumbs d-flex flex-nowrap gap-2">
                                    @foreach($productImages as $image)
                                        <button type="button" class="product-thumb-btn {{ $loop->first ? 'active' : '' }}"
                                                data-slide-index="{{ $loop->index }}"
                                                data-src="{{ $image->url() }}"
                                                aria-label="Photo {{ $loop->iteration }}">
                                            <img src="{{ $image->url() }}" alt="">
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <p class="small text-muted mt-1 mb-0">{{ $productImages->count() }} photos — thumbnail ya swipe se dekhein</p>
                        @endif
                    @else
                        <div class="product-gallery-slide product-detail-hero-fallback">🥩</div>
                    @endif
                </div>
                @if($product->videoUrl())
                    <div class="mt-3">
                        <video src="{{ $product->videoUrl() }}" controls class="w-100 rounded border" style="max-height:280px;"></video>
                    </div>
                @endif
            </div>

            <div class="col-12 col-lg-6 text-start product-detail-panel">
                @if($product->category)
                    <p class="product-detail-cat mb-1">{{ $product->category->name }}</p>
                @endif
                <div class="product-detail-title-row mb-2">
                    @include('Website.partials.nonveg-mark', ['size' => 'md'])
                    <h1 class="product-detail-title mb-0">{{ $product->name }}</h1>
                </div>
                @if($ratingSummary['count'] > 0)
                    <a href="#productReviews" class="product-rating-line mb-2">
                        @include('Website.Dashboard.partials.stars', ['value' => $ratingSummary['average']])
                        <strong>{{ number_format($ratingSummary['average'], 1) }}</strong>
                        <span>({{ $ratingSummary['count'] }} {{ $ratingSummary['count'] === 1 ? 'rating' : 'ratings' }})</span>
                    </a>
                @endif

                <div class="product-guarantee-badge mb-2">"Tazgi ka guarantee card!"</div>

                @if($shortDesc)
                    <p class="product-detail-lead mb-3">{{ $shortDesc }}</p>
                @endif

                @if($product->weights->isNotEmpty())
                    <p class="product-detail-label mb-2">Select Weight:</p>
                    <div class="product-weight-pills mb-2" id="productWeightPills" role="group" aria-label="Select weight">
                        @foreach($product->weights as $weight)
                            <button type="button"
                                    class="product-weight-pill {{ $loop->first ? 'active' : '' }}"
                                    data-weight-id="{{ $weight->id }}"
                                    data-label="{{ $weight->weight_label }}"
                                    data-price="{{ (float) $weight->price }}">
                                {{ $weight->weight_label }}
                            </button>
                        @endforeach
                    </div>
                    <div class="product-price-line mb-3">
                        <span class="product-price-amount" id="productPriceDisplay">₹{{ number_format((float) ($defaultWeight->price ?? 0), 0) }}</span>
                        <span class="product-price-unit text-muted" id="productPriceUnit">per {{ $defaultWeight->weight_label ?? '' }}</span>
                    </div>
                @else
                    <p class="price mb-3">Price on request</p>
                @endif

                <p class="product-detail-label mb-2">Quantity:</p>
                <div class="product-qty-control mb-3" id="productQtyControl">
                    <button type="button" class="product-qty-btn" id="productQtyMinus" aria-label="Decrease quantity">−</button>
                    <span class="product-qty-value" id="productQtyValue">1</span>
                    <button type="button" class="product-qty-btn" id="productQtyPlus" aria-label="Increase quantity">+</button>
                </div>

                <label class="product-detail-label d-block mb-1" for="preferenceNote">
                    Which part do you prefer? / आपको कौन सा हिस्सा पसंद है?
                </label>
                <p class="product-preference-hint mb-2">
                    (e.g., breast piece, leg piece, liver / जैसे: सीना, टांग, कलेजी, etc.)
                </p>

                @auth
                    @if($product->weights->isNotEmpty())
                        <form method="POST" action="{{ route('products.cart.add', $product) }}" id="productAddCartForm" class="mb-2" data-ajax-add-cart>
                            @csrf
                            <input type="hidden" name="product_weight_id" id="productWeightIdInput" value="{{ $defaultWeight?->id }}">
                            <input type="hidden" name="quantity" id="productQtyInput" value="1">
                            <textarea name="preference_note" id="preferenceNote" rows="4" class="form-control product-preference-input mb-3"
                                      placeholder="Tell us your preference (optional)" maxlength="1000"></textarea>
                            <button type="submit" class="btn btn-fun btn-red product-add-cart-btn w-100" id="productAddCartBtn">
                                🛒 ADD TO CART — ₹<span id="productCartTotal">{{ number_format((float) ($defaultWeight->price ?? 0), 0) }}</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('products.wishlist.add', $product) }}" class="mb-3" data-ajax-wishlist>
                            @csrf
                            <button type="submit" class="btn btn-fun btn-yel w-100">
                                <i class="bi bi-heart-fill me-1"></i> Add to Wishlist
                            </button>
                        </form>
                    @endif
                @else
                    <textarea id="preferenceNote" rows="4" class="form-control product-preference-input mb-3"
                              placeholder="Tell us your preference (optional — cart ke liye login karein)" maxlength="1000"></textarea>
                    <a href="{{ $guestLoginUrl ?? route('login') }}" class="btn btn-fun btn-red w-100 mb-2">Login / लॉगिन — Add to cart</a>
                    <a href="{{ $guestLoginUrl ?? route('login') }}" class="btn btn-fun btn-yel w-100"><i class="bi bi-heart-fill me-1"></i> Wishlist ke liye login</a>
                @endauth

                @if(filled(strip_tags((string) $product->description)))
                    <div class="product-detail-desc mt-4 pt-3 border-top">
                        <h2 class="product-detail-label mb-2">Details</h2>
                        <div class="small text-muted">{!! \App\Support\SafeHtml::display($product->description) !!}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="product-reviews mt-5" id="productReviews">
            <h2 class="product-reviews-title">Customer ratings</h2>
            @if($ratingSummary['count'] > 0)
                <div class="product-reviews-summary">
                    <div class="product-reviews-score">
                        <div class="product-reviews-avg">{{ number_format($ratingSummary['average'], 1) }}</div>
                        @include('Website.Dashboard.partials.stars', ['value' => $ratingSummary['average']])
                        <div class="small text-muted">{{ $ratingSummary['count'] }} {{ $ratingSummary['count'] === 1 ? 'rating' : 'ratings' }}</div>
                    </div>
                    <div class="product-reviews-bars">
                        @for($s = 5; $s >= 1; $s--)
                            @php
                                $n = (int) ($ratingSummary['breakdown'][$s] ?? 0);
                                $pct = $ratingSummary['count'] ? round($n / $ratingSummary['count'] * 100) : 0;
                            @endphp
                            <div class="product-reviews-bar">
                                <span>{{ $s }} <i class="bi bi-star-fill"></i></span>
                                <div class="product-reviews-track"><div style="width: {{ $pct }}%"></div></div>
                                <span class="text-muted">{{ $n }}</span>
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="product-reviews-list">
                    @foreach($reviews as $review)
                        <div class="product-review">
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="product-review-avatar">{{ mb_strtoupper(mb_substr($review->user?->name ?? 'C', 0, 1)) }}</span>
                                    <strong>{{ \Illuminate\Support\Str::of($review->user?->name ?? 'Customer')->before(' ') }}</strong>
                                    <span class="product-review-verified"><i class="bi bi-patch-check-fill"></i> Verified buyer</span>
                                </div>
                                <span class="small text-muted">{{ $review->created_at?->format('d M Y') }}</span>
                            </div>
                            @include('Website.Dashboard.partials.stars', ['value' => $review->rating])
                            @if($review->review)
                                <p class="mb-0 mt-1">{{ $review->review }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">Abhi koi rating nahi hai. Product khareedne ke baad dashboard se rating de sakte hain.</p>
            @endif
        </div>

        @if($relatedProducts->isNotEmpty())
            <div class="mt-5 text-center">
                <h2 class="t">Related <u>Products</u></h2>
                <p class="sub">Aur bhi fresh options</p>
                <div class="row g-2 g-md-4 text-start">
                    @foreach($relatedProducts as $related)
                        @include('Website.partials.product-card', ['product' => $related])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
.product-detail-page{background:#fff8f3}
.product-gallery-wrap{position:relative;z-index:1}
.product-gallery-slide{width:100%;display:block;min-height:min(72vw,420px);max-height:min(85vh,560px);object-fit:cover;object-position:center;border-radius:12px;border:3px solid var(--dark);box-shadow:6px 6px 0 var(--dark)}
.product-detail-hero-fallback{min-height:280px;display:flex;align-items:center;justify-content:center;font-size:4rem;background:#fde2e2;border-radius:12px}
.product-image-carousel .owl-nav button{position:absolute;top:50%;transform:translateY(-50%);width:40px;height:40px;border-radius:50%;background:#fff!important;border:2px solid var(--dark)!important;font-size:1.4rem;line-height:1;box-shadow:3px 3px 0 var(--dark)}
.product-image-carousel .owl-nav .owl-prev{left:8px}
.product-image-carousel .owl-nav .owl-next{right:8px}
.product-image-carousel .owl-stage-outer{border-radius:12px}
.product-image-carousel .owl-dots{margin-top:8px}
.product-image-carousel .owl-dot span{width:8px;height:8px;margin:0 3px;border:2px solid var(--dark);background:#fff}
.product-image-carousel .owl-dot.active span{background:var(--red);width:20px}
.product-detail-thumbs-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;padding-bottom:4px}
.product-detail-thumbs{min-width:min-content}
.product-thumb-btn{flex:0 0 auto;border:2px solid var(--dark);border-radius:10px;padding:0;background:#fff;overflow:hidden;width:64px;height:64px;cursor:pointer;box-shadow:2px 2px 0 var(--dark)}
.product-thumb-btn.active{outline:3px solid var(--red)}
.product-thumb-btn img{width:100%;height:100%;object-fit:cover;display:block}
.product-detail-panel{position:relative;z-index:2}
.product-detail-cat{font-size:.85rem;color:#6b7280;font-weight:600;margin:0}
.product-detail-title{font-size:clamp(1.75rem,5vw,2.35rem);font-weight:800;line-height:1.15;color:var(--dark);margin:0}
.product-guarantee-badge{display:inline-block;background:var(--yel);border:3px solid var(--dark);border-radius:14px;padding:8px 14px;font-weight:800;font-size:.95rem;box-shadow:4px 4px 0 var(--dark);transform:rotate(-1deg)}
.product-detail-lead{color:#6b7280;font-size:.95rem;margin:0}
.product-detail-label{font-weight:800;font-size:1rem;margin:0;color:var(--dark)}
.product-weight-pills{display:flex;flex-wrap:wrap;gap:10px}
.product-weight-pill{border:3px solid var(--dark);border-radius:999px;padding:8px 22px;font-weight:800;background:#fff;color:var(--dark);box-shadow:3px 3px 0 var(--dark);cursor:pointer}
.product-weight-pill.active{background:var(--red);color:#fff}
.product-price-amount{font-size:2rem;font-weight:800;color:var(--red);line-height:1}
.product-price-unit{font-size:.95rem;margin-left:6px}
.product-qty-control{display:inline-flex;align-items:center;gap:12px;border:3px solid var(--dark);border-radius:999px;padding:6px 14px;background:#fff;box-shadow:3px 3px 0 var(--dark)}
.product-qty-btn{width:36px;height:36px;border:0;border-radius:50%;background:#f3f4f6;font-size:1.35rem;font-weight:800;line-height:1;cursor:pointer;color:var(--dark)}
.product-qty-value{font-weight:800;font-size:1.1rem;min-width:1.5rem;text-align:center}
.product-preference-hint{font-size:.78rem;color:#6b7280;margin:0}
.product-detail-page textarea.product-preference-input{
  display:block;width:100%;min-height:96px!important;height:auto!important;
  border:2px solid #9ca3af!important;border-radius:12px!important;
  padding:10px 12px!important;font-size:16px!important;line-height:1.4!important;
  background:#fff!important;color:var(--dark)!important;
  pointer-events:auto!important;touch-action:manipulation;resize:vertical;
  -webkit-user-select:text;user-select:text;
}
.product-detail-page textarea.product-preference-input:focus{border-color:var(--grn)!important;outline:none!important;box-shadow:0 0 0 2px rgba(22,163,74,.25)!important}
.product-add-cart-btn{font-size:.95rem!important;padding:12px 16px!important}
.product-detail-desc img{max-width:100%;height:auto}
.product-rating-line{display:inline-flex;align-items:center;gap:6px;text-decoration:none;color:var(--dark);font-size:.95rem}
.product-rating-line span:last-child{color:#6b7280}
.product-reviews{background:#fff;border:3px solid var(--dark);border-radius:18px;box-shadow:5px 5px 0 var(--dark);padding:22px}
.product-reviews-title{font-weight:800;font-size:1.35rem;margin-bottom:16px}
.product-reviews-summary{display:grid;grid-template-columns:170px minmax(0,1fr);gap:24px;align-items:center;padding-bottom:18px;border-bottom:2px dashed #e5e7eb;margin-bottom:8px}
.product-reviews-score{text-align:center}
.product-reviews-avg{font-size:2.8rem;font-weight:800;line-height:1;color:var(--dark)}
.product-reviews-bar{display:grid;grid-template-columns:42px minmax(0,1fr) 30px;gap:10px;align-items:center;font-size:.85rem;font-weight:700;margin-bottom:4px}
.product-reviews-bar i{color:#F57C00;font-size:.75rem}
.product-reviews-track{height:8px;border-radius:999px;background:#f1f5f9;overflow:hidden}
.product-reviews-track div{height:100%;border-radius:999px;background:linear-gradient(90deg,#D32F2F,#F57C00,#2BAE49)}
.product-review{padding:14px 0;border-bottom:1px dashed #e5e7eb}
.product-review:last-child{border-bottom:0}
.product-review-avatar{width:32px;height:32px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#2BAE49;color:#fff;font-weight:800}
.product-review-verified{font-size:.75rem;font-weight:700;color:#1E8E3E}
@media (max-width:575.98px){.product-reviews-summary{grid-template-columns:1fr;gap:14px}}
</style>
@endpush

@push('scripts')
<script>
(function () {
    var unitPrice = {{ (float) ($defaultWeight->price ?? 0) }};
    var qty = 1;
    var carouselEl = document.getElementById('productImageCarousel');
    var carouselApi = null;

    if (carouselEl && typeof jQuery !== 'undefined' && jQuery.fn.owlCarousel) {
        carouselApi = jQuery(carouselEl).owlCarousel({
            items: 1,
            loop: {{ $productImages->count() > 1 ? 'true' : 'false' }},
            margin: 0,
            nav: true,
            navText: ['‹', '›'],
            dots: true,
            smartSpeed: 450,
        });

        carouselApi.on('changed.owl.carousel', function (e) {
            var count = {{ $productImages->count() }};
            if (count < 2) return;
            var idx = e.relatedTarget.relative(e.item.index);
            document.querySelectorAll('.product-thumb-btn').forEach(function (btn) {
                btn.classList.toggle('active', parseInt(btn.getAttribute('data-slide-index'), 10) === idx);
            });
        });
    }

    function formatInr(n) {
        return Math.round(n).toString();
    }

    function updatePriceUi() {
        var total = unitPrice * qty;
        var priceEl = document.getElementById('productPriceDisplay');
        var cartTotalEl = document.getElementById('productCartTotal');
        var qtyVal = document.getElementById('productQtyValue');
        var qtyInput = document.getElementById('productQtyInput');
        if (priceEl) priceEl.textContent = '₹' + formatInr(unitPrice);
        if (cartTotalEl) cartTotalEl.textContent = formatInr(total);
        if (qtyVal) qtyVal.textContent = String(qty);
        if (qtyInput) qtyInput.value = String(qty);
    }

    document.querySelectorAll('.product-weight-pill').forEach(function (pill) {
        pill.addEventListener('click', function () {
            document.querySelectorAll('.product-weight-pill').forEach(function (p) { p.classList.remove('active'); });
            pill.classList.add('active');
            unitPrice = parseFloat(pill.getAttribute('data-price')) || 0;
            var idInput = document.getElementById('productWeightIdInput');
            var unitLabel = document.getElementById('productPriceUnit');
            if (idInput) idInput.value = pill.getAttribute('data-weight-id') || '';
            if (unitLabel) unitLabel.textContent = 'per ' + (pill.getAttribute('data-label') || '');
            updatePriceUi();
        });
    });

    var minus = document.getElementById('productQtyMinus');
    var plus = document.getElementById('productQtyPlus');
    if (minus) minus.addEventListener('click', function () {
        qty = Math.max(1, qty - 1);
        updatePriceUi();
    });
    if (plus) plus.addEventListener('click', function () {
        qty = Math.min(99, qty + 1);
        updatePriceUi();
    });

    document.querySelectorAll('.product-thumb-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var index = parseInt(btn.getAttribute('data-slide-index'), 10);
            if (carouselApi && !isNaN(index)) {
                carouselApi.trigger('to.owl.carousel', [index, 300, true]);
            }
            document.querySelectorAll('.product-thumb-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
        });
    });
})();
</script>
@endpush
