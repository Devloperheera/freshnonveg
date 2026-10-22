@extends('Website.Layout.master')

@section('title', 'Register – FreshNonVeg')

@section('content')
@include('Website.Auth.partials.styles')
<div class="auth-wrap auth-wrap--compact">
    <div class="auth-card auth-card--compact">
        <h1>नया अकाउंट बनाएं 🎉</h1>
        <p class="sub">Fresh deals &amp; fast delivery — <strong>ताज़ा non-veg</strong> ab ghar par!</p>

        <form method="POST" action="{{ route('register.post') }}">
            @csrf
            <div class="mb-compact">
                <label class="form-label">पूरा नाम (Full Name)</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Aapka naam likhein" required autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-compact">
                <label class="form-label">मोबाइल नंबर (Phone)</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="98XXXXXXXX" required>
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-compact">
                <label class="form-label">ईमेल <span class="text-muted fw-normal">(वैकल्पिक / optional)</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="email@example.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-compact">
                <label class="form-label">पासवर्ड (Password)</label>
                <div class="pass-wrap">
                    <input type="password" name="password" id="regPassword" class="form-control @error('password') is-invalid @enderror" placeholder="Kam se kam 6 akshar" required>
                    <button type="button" class="pass-toggle" data-toggle-pass="regPassword" data-toggle-icon="regEyeIcon" aria-label="Show password"><i class="bi bi-eye" id="regEyeIcon"></i></button>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="mb-compact">
                <label class="form-label">पासवर्ड दोबारा (Confirm)</label>
                <div class="pass-wrap">
                    <input type="password" name="password_confirmation" id="regPasswordConfirm" class="form-control" placeholder="Wahi password phir se" required>
                    <button type="button" class="pass-toggle" data-toggle-pass="regPasswordConfirm" data-toggle-icon="regEyeIcon2" aria-label="Show password"><i class="bi bi-eye" id="regEyeIcon2"></i></button>
                </div>
            </div>
            <button type="submit" class="btn btn-brand mb-2">रजिस्टर करें (Sign Up)</button>
        </form>

        <a href="{{ $guestGoogleUrl ?? route('auth.google') }}" class="btn btn-google d-flex align-items-center justify-content-center gap-2">
            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18" alt=""> Google से Sign Up
        </a>

        <div class="auth-links">
            पहले से account hai? <a href="{{ $guestLoginUrl ?? route('login') }}">Login karein</a>
        </div>
    </div>
</div>
@include('Website.Auth.partials.pass-toggle-script')
@endsection
