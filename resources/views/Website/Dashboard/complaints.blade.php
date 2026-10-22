@extends('Website.Dashboard.layout')

@section('title', 'Complaints — FreshNonVeg')
@section('dashboard_title', 'Complaints')

@section('dashboard')
<div class="ud-grid ud-grid-wide">
    <div class="ud-card">
        <div class="ud-card-head">
            <h2>Nayi complaint</h2>
        </div>
        <form method="POST" action="{{ route('dashboard.complaints.store') }}" class="ud-form">
            @csrf
            <label class="form-label" for="complaintOrder">Order (optional)</label>
            <select name="order_id" id="complaintOrder" class="form-select mb-3">
                <option value="">General complaint</option>
                @foreach($orderOptions as $opt)
                    <option value="{{ $opt->id }}" @selected((int) old('order_id', $selectedOrder) === $opt->id)>
                        {{ $opt->order_number }} · ₹{{ number_format((float) $opt->total, 0) }} · {{ $opt->created_at?->format('d M') }}
                    </option>
                @endforeach
            </select>

            <label class="form-label" for="complaintSubject">Subject *</label>
            <input type="text" name="subject" id="complaintSubject" class="form-control mb-3" maxlength="150" required
                   value="{{ old('subject') }}" placeholder="Jaise: Item kam aaya, late delivery">

            <label class="form-label" for="complaintMessage">Details *</label>
            <textarea name="message" id="complaintMessage" rows="5" class="form-control mb-3" maxlength="3000" required
                      placeholder="Kya problem hui, detail me batayein">{{ old('message') }}</textarea>

            <button type="submit" class="ud-btn w-100">Submit complaint</button>
        </form>
    </div>

    <div class="ud-card">
        <div class="ud-card-head">
            <h2>Meri complaints</h2>
        </div>
        @forelse($complaints as $complaint)
            <div class="ud-complaint">
                <div class="ud-order-top">
                    <div>
                        <div class="ud-row-title">#{{ $complaint->id }} · {{ $complaint->subject }}</div>
                        <div class="ud-row-sub">
                            {{ $complaint->created_at?->format('d M Y') }}
                            @if($complaint->order) · {{ $complaint->order->order_number }} @endif
                        </div>
                    </div>
                    @php
                        $tone = match ($complaint->status) {
                            'resolved' => 'ok',
                            'rejected' => 'bad',
                            'in_progress' => 'warn',
                            default => 'muted',
                        };
                    @endphp
                    <span class="ud-badge ud-badge-{{ $tone }}">{{ $complaint->statusLabel() }}</span>
                </div>
                @if($complaint->order && $complaint->order->items->isNotEmpty())
                    <div class="ud-thumb-stack mt-2">
                        @foreach($complaint->order->items->take(4) as $line)
                            @include('Website.Dashboard.partials.item-thumb', ['line' => $line])
                        @endforeach
                        @if($complaint->order->items->count() > 4)
                            <span class="ud-thumb ud-thumb-more">+{{ $complaint->order->items->count() - 4 }}</span>
                        @endif
                    </div>
                @endif
                <p class="ud-complaint-msg">{{ $complaint->message }}</p>
                @if($complaint->admin_reply)
                    <div class="ud-reply"><strong>Team reply:</strong> {{ $complaint->admin_reply }}</div>
                @endif
                @if((float) $complaint->credited_amount > 0)
                    <div class="ud-credit"><i class="bi bi-wallet2 me-1"></i> ₹{{ number_format((float) $complaint->credited_amount, 2) }} wallet me credit hue</div>
                @endif
            </div>
        @empty
            <div class="ud-empty">
                <i class="bi bi-emoji-smile"></i>
                <p>Koi complaint nahi. Sab badhiya!</p>
            </div>
        @endforelse

        @if($complaints->hasPages())
            <div class="mt-3">{{ $complaints->links() }}</div>
        @endif
    </div>
</div>
@endsection
