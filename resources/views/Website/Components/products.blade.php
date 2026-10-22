<section class="py-5" id="shop">
    <div class="container text-center">
        <h2 class="t rv">Featured <u>Products</u></h2>
        <p class="sub rv">सबसे ज्यादा बिकने वाले! 🔥</p>
        <div class="row g-2 g-md-4 text-start">
            @forelse($products ?? [] as $product)
                @include('Website.partials.product-card', ['product' => $product])
            @empty
                <div class="col-12 rv">
                    <div class="fcard text-center py-4">
                        <p class="mb-0 text-muted">Abhi koi product available nahi hai.</p>
                    </div>
                </div>
            @endforelse
        </div>
        @if(!empty($hasMoreProducts))
            <a class="btn btn-fun btn-red btn-lg mt-4 px-4 rv"
               href="{{ route('shop.index', request()->only(['category', 'tag', 'q', 'product'])) }}">
                VIEW ALL PRODUCTS →
            </a>
        @endif
    </div>
</section>
