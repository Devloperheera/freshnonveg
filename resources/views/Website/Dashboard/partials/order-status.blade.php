@php
    $map = [
        'paid' => ['Paid', 'ok'],
        'confirmed' => ['Confirmed', 'ok'],
        'awaiting_payment' => ['Payment pending', 'warn'],
        'payment_failed' => ['Payment failed', 'bad'],
        'cancelled' => ['Cancelled', 'muted'],
    ];
    [$label, $tone] = $map[$status] ?? [ucfirst(str_replace('_', ' ', (string) $status)), 'muted'];
@endphp
<span class="ud-badge ud-badge-{{ $tone }}">{{ $label }}</span>
