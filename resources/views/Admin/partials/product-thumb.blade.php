@php
    $url = null;
    if (isset($product) && $product?->relationLoaded('images') && $product->images->isNotEmpty()) {
        $url = $product->images->first()->url();
    } elseif (isset($item) && method_exists($item, 'imageUrl')) {
        $url = $item->imageUrl();
    }
    $size = $size ?? 36;
@endphp
@if($url)
    <img src="{{ $url }}" alt="" class="adm-prod-thumb" width="{{ $size }}" height="{{ $size }}" loading="lazy">
@else
    <span class="adm-prod-thumb adm-prod-thumb--empty" style="width:{{ $size }}px;height:{{ $size }}px">
        <i class="bi bi-image"></i>
    </span>
@endif
