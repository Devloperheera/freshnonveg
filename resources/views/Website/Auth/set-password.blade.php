@extends('Website.Layout.master')

@section('title', 'Password banayein – FreshNonVeg')

@section('content')
@include('Website.Auth.partials.styles')
<div class="auth-wrap auth-wrap--compact">
    <div class="auth-card auth-card--compact">
        <h1>Password banayein 🔐</h1>
        <p class="sub">
            Namaste {{ $user->name }}! Ek password bana lijiye — agli baar <strong>{{ $user->phone ?: $user->email }}</strong>
            + password se seedha login, <strong>OTP ka wait nahi</strong>.
        </p>

        <form method="POST" action="{{ route('password.setup.store') }}">
            @csrf
            <div class="mb-compact">
                <label class="form-label" for="setupPassword">नया पासवर्ड (New password)</label>
                <div class="pass-wrap">
                    <input type="password" name="password" id="setupPassword" minlength="{{ $minLength }}" maxlength="100"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Kam se kam {{ $minLength }} akshar" autocomplete="new-password" required autofocus>
                    <button type="button" class="pass-toggle" data-toggle-pass="setupPassword" data-toggle-icon="setupEye" aria-label="Show password"><i class="bi bi-eye" id="setupEye"></i></button>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-compact">
                <label class="form-label" for="setupPasswordConfirm">पासवर्ड दोबारा (Confirm)</label>
                <div class="pass-wrap">
                    <input type="password" name="password_confirmation" id="setupPasswordConfirm" minlength="{{ $minLength }}" maxlength="100"
                           class="form-control" placeholder="Same password" autocomplete="new-password" required>
                    <button type="button" class="pass-toggle" data-toggle-pass="setupPasswordConfirm" data-toggle-icon="setupEye2" aria-label="Show password"><i class="bi bi-eye" id="setupEye2"></i></button>
                </div>
            </div>
            <button type="submit" class="btn btn-brand">Password save karein</button>
        </form>

        <form method="POST" action="{{ route('password.setup.skip') }}" class="auth-links mt-2">
            @csrf
            <button type="submit" class="btn btn-link p-0 small">Abhi nahi, baad me</button>
        </form>
    </div>
</div>
@include('Website.Auth.partials.pass-toggle-script')
@endsection
