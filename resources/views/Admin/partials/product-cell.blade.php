@php
    $name = $product?->name ?? ($fallbackName ?? 'Product removed');
    $size = $size ?? 48;
@endphp
<div class="adm-prod-cell">
    @if($product)
        <a href="{{ route('admin.products.show', $product, false) }}" class="adm-prod-cell-link text-decoration-none">
            @include('Admin.partials.product-thumb', ['product' => $product, 'item' => $item ?? null, 'size' => $size])
            <div class="adm-prod-cell__body min-w-0">
                <span class="adm-prod-cell__name">{{ $name }}</span>
                <div class="adm-prod-cell__meta">
                    @if($product->category)
                        <span><i class="bi bi-grid"></i> {{ $product->category->name }}</span>
                    @endif
                    <span>ID #{{ $product->id }}</span>
                    @isset($weight)
                        <span><i class="bi bi-box"></i> {{ $weight }}</span>
                    @endisset
                    @isset($extra)
                        <span>{{ $extra }}</span>
                    @endisset
                </div>
            </div>
        </a>
    @else
        @include('Admin.partials.product-thumb', ['product' => null, 'item' => $item ?? null, 'size' => $size])
        <div class="adm-prod-cell__body min-w-0">
            <span class="adm-prod-cell__name">{{ $name }}</span>
        </div>
    @endif
</div>
