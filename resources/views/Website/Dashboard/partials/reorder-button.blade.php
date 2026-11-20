{{-- Reorder / Pay again: purane order ke items cart me daal ke checkout par le jata hai --}}
@php
    $isFailed = $order->status === 'payment_failed';
    $isPending = $order->status === 'awaiting_payment';
@endphp
@unless($isPending)
    <form method="POST" action="{{ route('dashboard.orders.reorder', $order) }}" class="d-inline">
        @csrf
        <button type="submit" class="ud-btn ud-btn-sm {{ $isFailed ? '' : 'ud-btn-ghost' }} {{ $class ?? '' }}"
                title="{{ $isFailed ? 'Items cart me wapas daal ke dobara pay karein' : 'Same items dobara order karein' }}">
            @if($isFailed)
                <i class="bi bi-credit-card"></i> Pay again
            @else
                <i class="bi bi-arrow-repeat"></i> Reorder
            @endif
        </button>
    </form>
@endunless
