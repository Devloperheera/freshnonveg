@extends('Website.Layout.master')

@section('title', 'Forgot Password – FreshNonVeg')

@section('content')
@include('Website.Auth.partials.styles')
<div class="auth-wrap auth-wrap--compact">
    <div class="auth-card auth-card--compact">
        <h1>पासवर्ड भूल गए? 🔑</h1>
        <p class="sub">ईमेल या फोन par <strong>OTP</strong> aayega — phir naya password set karo, easy!</p>

        <form method="POST" action="{{ route('password.forgot.post') }}">
            @csrf
            <div class="mb-compact">
                <label class="form-label">ईमेल / मोबाइल (Email / Phone)</label>
                <input type="text" name="login" class="form-control @error('login') is-invalid @enderror" value="{{ old('login') }}" placeholder="email@example.com ya 98XXXXXXXX" required autofocus>
                @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-brand mb-2">OTP भेजें (Reset OTP)</button>
        </form>

        <div class="auth-links">
            <a href="{{ route('login') }}">← Login par wapas jao</a>
        </div>
    </div>
</div>
@endsection
