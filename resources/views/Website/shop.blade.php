@extends('Website.Layout.master')

@section('title', 'Shop All Products — FreshNonVeg')

@section('content')
<section class="py-5" id="shop">
    <div class="container">
        <div class="text-center mb-4">
            @if($activeCategory ?? null)
                <h1 class="t">{{ $activeCategory->name }} <u>Products</u></h1>
                <p class="sub mb-0">Is category mein {{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }}</p>
            @elseif($activeTag ?? null)
                <h1 class="t"><u>{{ $activeTag->name }}</u></h1>
                <p class="sub mb-0">{{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }}</p>
            @elseif(request()->filled('q'))
                <h1 class="t">Search: <u>{{ request('q') }}</u></h1>
                <p class="sub mb-0">{{ $products->total() }} result{{ $products->total() === 1 ? '' : 's' }}</p>
            @else
                <h1 class="t">All <u>Products</u></h1>
                <p class="sub mb-0">Fresh non-veg — {{ $products->total() }} items</p>
            @endif
            @if(($activeCategory ?? null) || ($activeTag ?? null) || request()->filled('q'))
                <a href="{{ route('shop.index') }}" class="btn btn-outline-dark btn-sm mt-2">Saare products dikhao</a>
            @endif
        </div>
        <div class="row g-2 g-md-4">
            @forelse($products as $product)
                @include('Website.partials.product-card', ['product' => $product])
            @empty
                <div class="col-12">
                    <div class="fcard text-center py-5 text-muted">Koi product nahi mila.</div>
                </div>
            @endforelse
        </div>
        @if($products->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $products->links() }}
            </div>
        @endif
        <div class="text-center mt-4">
            <a href="{{ route('home') }}#shop" class="btn btn-fun btn-yel">← Back to Home</a>
        </div>
    </div>
</section>
@endsection

