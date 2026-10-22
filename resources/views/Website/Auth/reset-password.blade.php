@extends('Website.Layout.master')

@section('title', 'Reset Password – FreshNonVeg')

@section('content')
@include('Website.Auth.partials.styles')
<div class="auth-wrap auth-wrap--compact">
    <div class="auth-card auth-card--compact">
        <h1>नया पासवर्ड 🔒</h1>
        <p class="sub">OTP verify karo, phir <strong>strong password</strong> set karo — account safe rahega!</p>

        <form method="POST" action="{{ route('password.reset.post') }}">
            @csrf
            <input type="hidden" name="login" value="{{ $login }}">
            <div class="mb-compact">
                <label class="form-label">ईमेल / मोबाइल</label>
                <input type="text" class="form-control" value="{{ $login }}" readonly>
            </div>
            <div class="mb-compact">
                <label class="form-label">OTP</label>
                <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" class="form-control @error('otp') is-invalid @enderror" placeholder="6 अंकों का OTP" required autofocus>
                @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-compact">
                <label class="form-label">नया पासवर्ड (New Password)</label>
                <div class="pass-wrap">
                    <input type="password" name="password" id="newPassword" class="form-control @error('password') is-invalid @enderror" placeholder="Kam se kam 6 akshar" required>
                    <button type="button" class="pass-toggle" data-toggle-pass="newPassword" data-toggle-icon="newEyeIcon" aria-label="Show password"><i class="bi bi-eye" id="newEyeIcon"></i></button>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-compact">
                <label class="form-label">पासवर्ड दोबारा (Confirm)</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Same password" required>
            </div>
            <button type="submit" class="btn btn-brand">Password Update karein</button>
        </form>

        <div class="auth-links mt-2">
            <a href="{{ route('password.forgot') }}">OTP दोबारा भेजें</a>
        </div>
    </div>
</div>
@include('Website.Auth.partials.pass-toggle-script')
@endsection
