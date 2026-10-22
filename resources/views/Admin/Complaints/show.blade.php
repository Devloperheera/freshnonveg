@extends('Admin.Layout.master')

@section('title', 'Complaint #'.$complaint->id.' — FreshNonVeg Admin')

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-comment-dots me-2"></i> Complaint #{{ $complaint->id }}
        </h2>
        <a href="{{ route('admin.complaints.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-1">{{ $complaint->subject }}</h5>
                    <div class="small text-muted mb-3">{{ $complaint->created_at?->format('d M Y, h:i A') }} · {{ $complaint->statusLabel() }}</div>
                    <p class="mb-0" style="white-space:pre-line">{{ $complaint->message }}</p>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h6 class="fw-bold">Customer</h6>
                    <div>{{ $complaint->user?->name }}</div>
                    <div class="small text-muted">{{ $complaint->user?->phone }} · {{ $complaint->user?->email }}</div>
                    <div class="small mt-2">Current wallet balance: <strong>₹{{ number_format((float) $complaint->user?->wallet_balance, 2) }}</strong></div>
                </div>
            </div>

            @if($complaint->order)
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold">Order {{ $complaint->order->order_number }}</h6>
                        <div class="small text-muted mb-2">{{ $complaint->order->created_at?->format('d M Y, h:i A') }} · {{ ucfirst(str_replace('_', ' ', $complaint->order->status)) }}</div>
                        <ul class="small mb-2">
                            @foreach($complaint->order->items as $line)
                                <li>{{ $line->product_name }}@if($line->weight_label) ({{ $line->weight_label }})@endif × {{ $line->quantity }} — ₹{{ number_format((float) $line->unit_price * $line->quantity, 2) }}</li>
                            @endforeach
                        </ul>
                        <div class="fw-bold">Total: ₹{{ number_format((float) $complaint->order->total, 2) }}</div>
                    </div>
                </div>
            @endif

            @if($complaint->walletTransactions->isNotEmpty())
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold">Wallet credits</h6>
                        @foreach($complaint->walletTransactions as $txn)
                            <div class="small d-flex justify-content-between border-bottom py-1">
                                <span>{{ $txn->created_at?->format('d M Y, h:i A') }}</span>
                                <strong class="text-success">+₹{{ number_format((float) $txn->amount, 2) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div id="form-section" class="form-section">
                <div>
                    <h6 class="fw-bold mb-3"><i class="fas fa-reply me-2"></i>Resolve / reply</h6>
                    <form method="POST" action="{{ route('admin.complaints.update', $complaint) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(\App\Models\Complaint::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected(old('status', $complaint->status) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reply to customer</label>
                            <textarea name="admin_reply" rows="4" class="form-control" maxlength="3000">{{ old('admin_reply', $complaint->admin_reply) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Wallet credit (₹)</label>
                            <input type="number" name="credit_amount" step="0.01" min="0" class="form-control" value="{{ old('credit_amount') }}" placeholder="0">
                            <div class="form-text">
                                Already credited: ₹{{ number_format((float) $complaint->credited_amount, 2) }}. Yahan daala amount <strong>extra</strong> credit hoga.
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="notify_customer" value="0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="notify_customer" name="notify_customer" value="1" @checked(old('notify_customer', true))>
                                <label class="form-check-label" for="notify_customer">
                                    Reply customer ki email par bhejein
                                    <span class="text-muted small">({{ $complaint->user?->email ?: 'email nahi hai' }})</span>
                                </label>
                            </div>
                            @if($complaint->reply_emailed_at)
                                <div class="form-text text-success"><i class="fas fa-check-circle me-1"></i>Pichla reply email: {{ $complaint->reply_emailed_at->format('d M Y, h:i A') }}</div>
                            @endif
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary" onclick="var a=this.form.credit_amount.value; return !(parseFloat(a)>0) || confirm('₹'+a+' customer ke wallet me credit karein?');">
                                <i class="fas fa-save me-2"></i>
                                Update Complaint
                            </button>
                            <button type="reset" class="btn btn-warning">
                                <i class="fas fa-undo me-2"></i>
                                Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
