@extends('Website.Dashboard.layout')

@section('title', 'My Ratings — FreshNonVeg')
@section('dashboard_title', 'My ratings')

@section('dashboard')
<div class="ud-card ud-note">
    <i class="bi bi-star-fill"></i>
    <p class="mb-0">Jo products aapne khareede hain unhe 1 se 5 star dein. Har product par rating <strong>sirf ek baar</strong> di ja sakti hai — submit ke baad badli nahi ja sakti.</p>
</div>

<div class="ud-card">
    <div class="ud-card-head">
        <h2>Rating pending <span class="ud-count">{{ $pending->count() }}</span></h2>
    </div>
    @forelse($pending as $line)
        @php $product = $line->product; @endphp
        <div class="ud-rate-item" id="rate-{{ $product->id }}">
            <div class="ud-rate-product">
                @include('Website.Dashboard.partials.item-thumb', ['line' => $line])
                <div class="min-w-0">
                    <a href="{{ route('products.show', $product) }}" class="ud-row-title ud-link">{{ $product->name }}</a>
                    <div class="ud-row-sub">Order {{ $line->order?->order_number }} · {{ $line->order?->created_at?->format('d M Y') }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('dashboard.ratings.store', $product) }}" class="ud-rate-form">
                @csrf
                <div class="ud-star-input" role="radiogroup" aria-label="Rating">
                    @for($i = 5; $i >= 1; $i--)
                        <input type="radio" name="rating" value="{{ $i }}" id="star-{{ $product->id }}-{{ $i }}" required>
                        <label for="star-{{ $product->id }}-{{ $i }}" title="{{ $i }} star"><i class="bi bi-star-fill"></i></label>
                    @endfor
                </div>
                <textarea name="review" rows="2" maxlength="1000" class="form-control" placeholder="Apna experience likhein (optional)"></textarea>
                <button type="submit" class="ud-btn ud-btn-sm">Submit rating</button>
            </form>
        </div>
    @empty
        <div class="ud-empty">
            <i class="bi bi-star"></i>
            <p>{{ $rated->isEmpty() ? 'Order deliver hone ke baad yahan products rating ke liye aayenge.' : 'Sab products ko rating de di gayi hai. Shukriya!' }}</p>
            @if($rated->isEmpty())
                <a href="{{ route('shop.index') }}" class="ud-btn">Shop now</a>
            @endif
        </div>
    @endforelse
</div>

@if($rated->isNotEmpty())
    <div class="ud-card">
        <div class="ud-card-head">
            <h2>Meri di hui ratings <span class="ud-count">{{ $rated->count() }}</span></h2>
        </div>
        @foreach($rated as $line)
            @php $review = $reviews[$line->product_id]; @endphp
            <div class="ud-row align-items-start">
                <div class="ud-rate-product">
                    @include('Website.Dashboard.partials.item-thumb', ['line' => $line])
                    <div class="min-w-0">
                        <a href="{{ route('products.show', $line->product) }}" class="ud-row-title ud-link">{{ $line->product->name }}</a>
                        <div>@include('Website.Dashboard.partials.stars', ['value' => $review->rating])</div>
                        @if($review->review)
                            <p class="ud-review-text">{{ $review->review }}</p>
                        @endif
                    </div>
                </div>
                <span class="ud-row-sub text-nowrap">{{ $review->created_at?->format('d M Y') }}</span>
            </div>
        @endforeach
    </div>
@endif
@endsection
