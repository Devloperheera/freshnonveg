@extends('Admin.Layout.master')

@section('title', 'Edit Rating #'.$review->id.' — Admin')

@section('content')
<div class="container mt-4 mb-4">

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-edit me-2"></i> Edit Rating #{{ $review->id }}
        </h2>
        <a href="{{ route('admin.product-reviews.show', $review) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="small text-muted mb-3">
        Customer: <strong>{{ $review->user?->name ?? '—' }}</strong>
        · Product: <strong>{{ $review->product?->name ?? '—' }}</strong>
        @if($review->order) · Order {{ $review->order->order_number }}@endif
    </div>

    <div id="form-section" class="form-section">
        <form method="POST" action="{{ route('admin.product-reviews.update', $review) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Rating <span class="required-field">*</span></label>
                <select name="rating" class="form-select" required>
                    @for($s = 5; $s >= 1; $s--)
                        <option value="{{ $s }}" @selected(old('rating', $review->rating) == $s)>{{ $s }} star{{ $s > 1 ? 's' : '' }}</option>
                    @endfor
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Review text</label>
                <textarea name="review" rows="5" class="form-control" maxlength="2000">{{ old('review', $review->review) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Status <span class="required-field">*</span></label>
                <select name="status" class="form-select" required>
                    <option value="1" @selected(old('status', $review->status) == 1)>Active (product page par dikhe)</option>
                    <option value="0" @selected(old('status', $review->status) == 0)>Inactive (chhupi rahe)</option>
                </select>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i> Update Rating
                </button>
                <button type="reset" class="btn btn-warning">
                    <i class="fas fa-undo me-2"></i> Reset Form
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
