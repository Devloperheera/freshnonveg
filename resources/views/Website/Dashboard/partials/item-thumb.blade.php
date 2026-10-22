@php $thumbUrl = $line->imageUrl(); @endphp
<span class="ud-thumb">
    @if($thumbUrl)
        <img src="{{ $thumbUrl }}" alt="{{ $line->product_name }}" loading="lazy">
    @else
        <i class="bi bi-image"></i>
    @endif
</span>
