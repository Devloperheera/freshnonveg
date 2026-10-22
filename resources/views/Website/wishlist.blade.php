@extends('Website.Layout.master')

@section('title', 'Wishlist — FreshNonVeg')

@section('content')
<section class="py-5">
    <div class="container">
        <h1 class="t mb-3">Wishlist <u>❤️</u></h1>
        <p class="sub">आपके पसंदीदा products</p>
        @forelse($items as $item)
            <div class="fcard mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h4 class="mb-0 fnv-product-name">
                    @include('Website.partials.nonveg-mark', ['size' => 'sm'])
                    <span>{{ $item->product_name }}</span>
                </h4>
                <span class="text-muted small">Added {{ $item->created_at?->format('d M Y') }}</span>
            </div>
        @empty
            <div class="fcard text-center py-4">
                <p class="mb-3">Wishlist खाली है। Shop से products add करें।</p>
                <a href="{{ route('home') }}#shop" class="btn btn-fun btn-red">Shop now</a>
            </div>
        @endforelse
    </div>
</section>
@endsection
