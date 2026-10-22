@extends('Website.Dashboard.layout')

@section('title', 'My Wallet — FreshNonVeg')
@section('dashboard_title', 'Wallet')

@section('dashboard')
<div class="ud-wallet-card">
    <div class="ud-wallet-glow" aria-hidden="true"></div>
    <div class="ud-wallet-top">
        <span>FRESHNONVEG WALLET</span>
        <i class="bi bi-wallet2"></i>
    </div>
    <div class="ud-wallet-balance">₹{{ number_format((float) $user->wallet_balance, 2) }}</div>
    <div class="ud-wallet-sub">Available balance · checkout par use karein</div>
    <div class="ud-wallet-meta">
        <div><span>Total credited</span><strong>₹{{ number_format($totalCredited, 2) }}</strong></div>
        <div><span>Total used</span><strong>₹{{ number_format($totalUsed, 2) }}</strong></div>
        <div><span>Member</span><strong>{{ $user->name }}</strong></div>
    </div>
</div>

<div class="ud-card ud-note">
    <i class="bi bi-info-circle"></i>
    <p class="mb-0">Kisi order me problem hui to <a href="{{ route('dashboard.complaints') }}">complaint</a> karein. Complaint sahi paayi gayi to team aapke wallet me <strong>coupon amount</strong> credit karegi, jo agle order ke checkout par lag jayega.</p>
</div>

<div class="ud-card">
    <div class="ud-card-head">
        <h2>Transactions</h2>
    </div>
    @forelse($transactions as $txn)
        <div class="ud-row">
            <div class="d-flex align-items-center gap-3">
                <span class="ud-txn-icon {{ $txn->isCredit() ? 'ud-plus-bg' : 'ud-minus-bg' }}">
                    <i class="bi {{ $txn->isCredit() ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                </span>
                <div>
                    <div class="ud-row-title">{{ $txn->description }}</div>
                    <div class="ud-row-sub">{{ $txn->created_at?->format('d M Y, h:i A') }} · Balance ₹{{ number_format((float) $txn->balance_after, 2) }}</div>
                </div>
            </div>
            <div class="ud-row-amount {{ $txn->isCredit() ? 'ud-plus' : 'ud-minus' }}">
                {{ $txn->isCredit() ? '+' : '−' }}₹{{ number_format((float) $txn->amount, 2) }}
            </div>
        </div>
    @empty
        <div class="ud-empty">
            <i class="bi bi-wallet2"></i>
            <p>Abhi koi transaction nahi.</p>
        </div>
    @endforelse

    @if($transactions->hasPages())
        <div class="mt-3">{{ $transactions->links() }}</div>
    @endif
</div>
@endsection
