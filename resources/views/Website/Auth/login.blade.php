@extends('Website.Layout.master')

@section('title', 'Login – FreshNonVeg')

@section('content')
@include('Website.Auth.partials.styles')
@php
    $mode = old('login_mode', session('login_mode', 'password'));
    $otpSent = session('otp_sent', false);
@endphp
<div class="auth-wrap auth-wrap--compact">
    <div class="auth-card auth-card--compact">
        <h1>फिर से स्वागत है 👋</h1>
        <p class="sub"><strong>Login</strong> karein — ईमेल या फोन से, <strong>Password</strong> ya <strong>OTP</strong> — dono chalega!</p>

        <div class="auth-tabs mb-compact">
            <button type="button" class="auth-tab {{ $mode === 'password' ? 'active' : '' }}" data-mode="password">Password</button>
            <button type="button" class="auth-tab {{ $mode === 'otp' ? 'active' : '' }}" data-mode="otp">OTP</button>
        </div>

        <div class="mb-compact">
            <label class="form-label">ईमेल / मोबाइल (Email / Phone)</label>
            <input type="text" id="loginIdentifier" class="form-control @error('login') is-invalid @enderror" value="{{ old('login') }}" placeholder="email@example.com ya 98XXXXXXXX" required autofocus>
            @error('login')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        <div id="panelPassword" class="{{ $mode === 'otp' ? 'd-none' : '' }}">
            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <input type="hidden" name="login" id="loginPasswordHidden" value="{{ old('login') }}">
                <div class="mb-compact">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0">पासवर्ड (Password)</label>
                        <a href="{{ route('password.forgot') }}" class="small fw-bold">पासवर्ड भूल गए?</a>
                    </div>
                    <div class="pass-wrap mt-1">
                        <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Apna password daalein" required>
                        <button type="button" class="pass-toggle" data-toggle-pass="passwordInput" data-toggle-icon="eyeIcon" aria-label="Show password"><i class="bi bi-eye" id="eyeIcon"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-brand mb-2">पासवर्ड से Login</button>
            </form>
        </div>

        <div id="panelOtp" class="{{ $mode === 'password' ? 'd-none' : '' }}">
            @if(! $otpSent)
                <p class="small text-muted mb-2">नंबर par SMS, ईमेल par mail — OTP jaldi aa jayega 📩</p>
                <form method="POST" action="{{ route('login.otp.send') }}">
                    @csrf
                    <input type="hidden" name="login" id="loginOtpSendHidden" value="{{ old('login') }}">
                    <button type="submit" class="btn btn-brand mb-2">OTP भेजें (Send OTP)</button>
                </form>
            @else
                <form method="POST" action="{{ route('login.otp.verify') }}">
                    @csrf
                    <input type="hidden" name="login" id="loginOtpVerifyHidden" value="{{ old('login') }}">
                    <label class="form-label">6 अंकों का OTP डालें</label>
                    <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" class="form-control mb-compact text-center @error('otp') is-invalid @enderror" placeholder="••••••" required autofocus>
                    @error('otp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <button type="submit" class="btn btn-brand mb-2">Verify करें & Login ✅</button>
                </form>
                <form method="POST" action="{{ route('login.otp.send') }}" class="text-center">
                    @csrf
                    <input type="hidden" name="login" id="loginOtpResendHidden" value="{{ old('login') }}">
                    <button type="submit" class="btn btn-link p-0 fw-bold small">OTP दोबारा भेजें</button>
                </form>
            @endif
        </div>

        <a href="{{ $guestGoogleUrl ?? route('auth.google') }}" class="btn btn-google d-flex align-items-center justify-content-center gap-2">
            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18" alt=""> Google से Login
        </a>

        <div class="auth-links">
            नए user ho? <a href="{{ $guestRegisterUrl ?? route('register') }}">रजिस्टर / Sign Up karein</a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const identifier = document.getElementById('loginIdentifier');
    const syncLogin = () => {
        const v = identifier.value.trim();
        ['loginPasswordHidden','loginOtpSendHidden','loginOtpVerifyHidden','loginOtpResendHidden'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = v;
        });
    };
    identifier.addEventListener('input', syncLogin);
    syncLogin();

    document.querySelectorAll('.auth-tab').forEach(btn => {
        btn.addEventListener('click', () => {
            const mode = btn.dataset.mode;
            document.querySelectorAll('.auth-tab').forEach(b => b.classList.toggle('active', b.dataset.mode === mode));
            document.getElementById('panelPassword').classList.toggle('d-none', mode !== 'password');
            document.getElementById('panelOtp').classList.toggle('d-none', mode !== 'otp');
        });
    });
});
</script>
@include('Website.Auth.partials.pass-toggle-script')
@endsection
