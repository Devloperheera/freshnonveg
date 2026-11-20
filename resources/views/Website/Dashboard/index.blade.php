@extends('Website.Dashboard.layout')

@section('title', 'My Dashboard — FreshNonVeg')
@section('dashboard_title', 'Namaste, '.\Illuminate\Support\Str::of($user->name)->before(' '))

@section('dashboard')
@unless(filled($user->password))
    <div class="ud-card mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-left:4px solid #F57C00">
        <div>
            <strong><i class="bi bi-shield-lock me-1"></i> Password bana lijiye</strong>
            <div class="ud-row-sub">Agli baar mobile/email + password se seedha login — OTP ka wait nahi.</div>
        </div>
        <a href="{{ route('password.setup') }}" class="ud-btn ud-btn-sm">Password banayein</a>
    </div>
@endunless
<div class="ud-stats">
    <div class="ud-stat">
        <span class="ud-stat-icon"><i class="bi bi-bag-check"></i></span>
        <span class="ud-stat-label">Total orders</span>
        <strong class="ud-stat-value">{{ $stats['orders'] }}</strong>
    </div>
    <div class="ud-stat">
        <span class="ud-stat-icon"><i class="bi bi-currency-rupee"></i></span>
        <span class="ud-stat-label">Total spent</span>
        <strong class="ud-stat-value">₹{{ number_format($stats['spent'], 0) }}</strong>
    </div>
    <a href="{{ route('dashboard.wallet') }}" class="ud-stat ud-stat-gold">
        <span class="ud-stat-icon"><i class="bi bi-wallet2"></i></span>
        <span class="ud-stat-label">Wallet balance</span>
        <strong class="ud-stat-value">₹{{ number_format($stats['wallet'], 2) }}</strong>
    </a>
    <a href="{{ route('dashboard.complaints') }}" class="ud-stat">
        <span class="ud-stat-icon"><i class="bi bi-chat-left-dots"></i></span>
        <span class="ud-stat-label">Open complaints</span>
        <strong class="ud-stat-value">{{ $stats['open_complaints'] }}</strong>
    </a>
</div>

<div class="ud-grid">
    <div class="ud-card">
        <div class="ud-card-head">
            <h2>Recent orders</h2>
            <a href="{{ route('dashboard.orders') }}">View all</a>
        </div>
        @forelse($recentOrders as $order)
            <div class="ud-row">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="ud-thumb-stack">
                        @foreach($order->items->take(3) as $line)
                            @include('Website.Dashboard.partials.item-thumb', ['line' => $line])
                        @endforeach
                        @if($order->items->count() > 3)
                            <span class="ud-thumb ud-thumb-more">+{{ $order->items->count() - 3 }}</span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <a href="{{ route('dashboard.orders.show', $order) }}" class="ud-row-title ud-link">{{ $order->order_number }}</a>
                        <div class="ud-row-sub">{{ $order->created_at?->format('d M Y, h:i A') }} · {{ $order->items->count() }} items</div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="ud-row-amount">₹{{ number_format((float) $order->total, 2) }}</div>
                    @include('Website.Dashboard.partials.order-status', ['status' => $order->status])
                    <div class="mt-2">
                        @include('Website.Dashboard.partials.reorder-button', ['order' => $order])
                    </div>
                </div>
            </div>
        @empty
            <div class="ud-empty">
                <i class="bi bi-bag"></i>
                <p>Abhi koi order nahi hai.</p>
                <a href="{{ route('shop.index') }}" class="ud-btn">Shop now</a>
            </div>
        @endforelse
    </div>

    <div class="ud-card">
        <div class="ud-card-head">
            <h2>Wallet activity</h2>
            <a href="{{ route('dashboard.wallet') }}">Details</a>
        </div>
        @forelse($recentTransactions as $txn)
            <div class="ud-row">
                <div>
                    <div class="ud-row-title">{{ $txn->description }}</div>
                    <div class="ud-row-sub">{{ $txn->created_at?->format('d M Y') }}</div>
                </div>
                <div class="ud-row-amount {{ $txn->isCredit() ? 'ud-plus' : 'ud-minus' }}">
                    {{ $txn->isCredit() ? '+' : '−' }}₹{{ number_format((float) $txn->amount, 2) }}
                </div>
            </div>
        @empty
            <div class="ud-empty">
                <i class="bi bi-wallet2"></i>
                <p>Wallet me abhi koi activity nahi. Kisi order me problem ho to complaint karein.</p>
                <a href="{{ route('dashboard.complaints') }}" class="ud-btn">Raise complaint</a>
            </div>
        @endforelse
    </div>
</div>
@endsection
