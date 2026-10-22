@extends('Website.Dashboard.layout')

@section('title', 'Profile settings – FreshNonVeg')
@section('dashboard_title', 'Profile settings')

@section('dashboard')
@include('Website.Auth.partials.styles')
@php
    $hasPassword = filled($user->password);
@endphp

<div class="ud-grid ud-grid-wide">
    <div class="ud-card">
        <div class="ud-card-head">
            <h2>Update details</h2>
        </div>
        <form method="POST" action="{{ route('profile.update') }}" class="ud-form">
            @csrf
            @method('PUT')
            <div class="row g-2">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Full name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required>
                    @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-2">
                    <label class="form-label">Email <span class="text-muted fw-normal">— optional</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" placeholder="email@example.com">
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Delivery address</label>
                    <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="Flat, society, area, city...">{{ old('address', $user->address) }}</textarea>
                    @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
            <button type="submit" class="ud-btn">Save changes</button>
        </form>
    </div>

    <div class="ud-card">
        <div class="ud-card-head">
            <h2>{{ $hasPassword ? 'Change password' : 'Set password' }}</h2>
        </div>
        <form method="POST" action="{{ route('profile.password') }}" class="ud-form">
            @csrf
            @method('PUT')
            @if($hasPassword)
                <div class="mb-2 pass-wrap">
                    <label class="form-label">Current password</label>
                    <input type="password" name="current_password" id="profileCurrentPass" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                    <button type="button" class="pass-toggle" data-toggle-pass="profileCurrentPass" data-toggle-icon="profileCurrentEye" aria-label="Show password"><i class="bi bi-eye" id="profileCurrentEye"></i></button>
                    @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            @else
                <p class="small text-muted mb-2">Google se login? Yahan naya password set kar sakte ho taaki email/phone se bhi login ho sake.</p>
            @endif
            <div class="mb-2 pass-wrap">
                <label class="form-label">New password</label>
                <input type="password" name="password" id="profileNewPass" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="6" required>
                <button type="button" class="pass-toggle" data-toggle-pass="profileNewPass" data-toggle-icon="profileNewEye" aria-label="Show password"><i class="bi bi-eye" id="profileNewEye"></i></button>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3 pass-wrap">
                <label class="form-label">Confirm password</label>
                <input type="password" name="password_confirmation" id="profileConfirmPass" class="form-control" autocomplete="new-password" minlength="6" required>
                <button type="button" class="pass-toggle" data-toggle-pass="profileConfirmPass" data-toggle-icon="profileConfirmEye" aria-label="Show password"><i class="bi bi-eye" id="profileConfirmEye"></i></button>
            </div>
            <button type="submit" class="ud-btn">{{ $hasPassword ? 'Update password' : 'Set password' }}</button>
        </form>
    </div>
</div>

@include('Website.Auth.partials.pass-toggle-script')
@endsection
