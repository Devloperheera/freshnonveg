@php $starValue = max(0, min(5, (float) $value)); @endphp
<span class="ud-stars {{ $class ?? '' }}" aria-label="{{ round($starValue, 1) }} out of 5">
    @for($i = 1; $i <= 5; $i++)
        @if($starValue >= $i)
            <i class="bi bi-star-fill"></i>
        @elseif($starValue >= $i - 0.5)
            <i class="bi bi-star-half"></i>
        @else
            <i class="bi bi-star"></i>
        @endif
    @endfor
</span>
