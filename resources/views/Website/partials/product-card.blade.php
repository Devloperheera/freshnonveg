@php
    $image = $product->images->first();
    $fromPrice = $product->weights->min('price');
    $tagLabel = $product->tags->first()?->name ?? ($product->is_featured ? 'Featured' : null);
    $excerpt = $product->meta_description ?: strip_tags((string) $product->description);
    $excerpt = \Illuminate\Support\Str::limit($excerpt, 72);
    $bgColors = ['#fde2e2', '#fff3c4', '#dbeafe', '#d6f5e3', '#fbd5e8', '#ffedd5'];
    $cardIndex = isset($loop) ? $loop->index : (int) $product->id;
    $bg = $bgColors[$cardIndex % count($bgColors)] ?? '#fde2e2';
@endphp
<div class="col-6 col-lg-3 rv product-card-col" id="product-{{ $product->id }}">
    <div class="fcard h-100">
        @if($tagLabel)
            <span class="tag">{{ $tagLabel }}</span>
        @endif
        <div class="pimg pimg-photo" style="background:{{ $bg }}">
            @if($image)
                <img src="{{ $image->url() }}" alt="{{ $product->name }}" loading="lazy">
            @else
                <span class="pimg-fallback">🥩</span>
            @endif
        </div>
        <h4>{{ $product->name }}</h4>
        @if($product->category)
            <p class="small text-muted mb-1">{{ $product->category->name }}</p>
        @endif
        <p>{{ $excerpt ?: 'Fresh quality, hygienically packed.' }}</p>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <span class="price">
                @if($fromPrice !== null)
                    ₹{{ number_format((float) $fromPrice, 0) }}
                @else
                    —
                @endif
            </span>
            <a class="btn btn-fun btn-red btn-sm px-3" href="{{ route('products.show', $product) }}">View</a>
        </div>
    </div>
</div>
