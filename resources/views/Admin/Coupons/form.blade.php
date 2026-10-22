@extends('Admin.Layout.master')

@section('title', ($coupon->exists ? 'Edit' : 'Add').' Coupon — FreshNonVeg Admin')

@section('content')
<div class="container mt-4 mb-4">

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-{{ $coupon->exists ? 'edit' : 'plus-circle' }} me-2"></i>
            {{ $coupon->exists ? 'Edit coupon' : 'Add discount coupon' }}
        </h2>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

    <div id="form-section" class="form-section">
        <form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}">
            @csrf
            @if($coupon->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Coupon code <span class="required-field">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                           value="{{ old('code', $coupon->code) }}" required maxlength="40" placeholder="WELCOME10" style="text-transform:uppercase">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <p class="small text-muted mb-0 mt-1">Customer checkout par yahi code type karega.</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" maxlength="120"
                           value="{{ old('title', $coupon->title) }}" placeholder="Welcome offer">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Discount type <span class="required-field">*</span></label>
                    <select name="discount_type" class="form-select" required>
                        <option value="percent" @selected(old('discount_type', $coupon->discount_type) === 'percent')>Percent (%)</option>
                        <option value="flat" @selected(old('discount_type', $coupon->discount_type) === 'flat')>Flat amount (₹)</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Discount value <span class="required-field">*</span></label>
                    <input type="number" name="discount_value" step="0.01" min="0.01" class="form-control @error('discount_value') is-invalid @enderror"
                           value="{{ old('discount_value', $coupon->discount_value) }}" required>
                    @error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Max discount (₹)</label>
                    <input type="number" name="max_discount" step="0.01" min="0" class="form-control"
                           value="{{ old('max_discount', $coupon->max_discount) }}" placeholder="Sirf percent ke liye">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Minimum order (₹)</label>
                    <input type="number" name="min_order_amount" step="0.01" min="0" class="form-control"
                           value="{{ old('min_order_amount', $coupon->min_order_amount ?? 0) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Usage limit</label>
                    <input type="number" name="usage_limit" min="1" class="form-control"
                           value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="Khali = unlimited">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status <span class="required-field">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" @selected(old('status', $coupon->status) == 1)>Active</option>
                        <option value="0" @selected(old('status', $coupon->status) == 0)>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Starts on</label>
                    <input type="date" name="starts_at" class="form-control"
                           value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Expires on</label>
                    <input type="date" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror"
                           value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d')) }}">
                    @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>
                    {{ $coupon->exists ? 'Update Coupon' : 'Save Coupon' }}
                </button>
                <button type="reset" class="btn btn-warning">
                    <i class="fas fa-undo me-2"></i>
                    Reset Form
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
