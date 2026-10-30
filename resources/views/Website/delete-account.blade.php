@extends('Website.Layout.master')

@section('title', 'Delete Account – FreshNonVeg')

@section('content')
@include('Website.Auth.partials.styles')
<div class="auth-wrap auth-wrap--compact">
    <div class="auth-card auth-card--compact">
        @guest
            <h1>Account delete karein</h1>
            <p class="sub">Apna FreshNonVeg account hataane ke liye pehle <strong>login</strong> karein. Is page ka URL: <strong>/delete</strong></p>
            <a href="{{ $loginUrl }}" class="btn btn-brand mb-2 w-100">Login karke aage badhein</a>
            <div class="auth-links">
                <a href="{{ route('home') }}">← Home</a>
            </div>
        @else
            @php
                $hasPassword = filled($user->password);
                $walletBalance = (float) $user->wallet_balance;
            @endphp
            <h1>Account delete karein</h1>
            <p class="sub">Yeh action permanent hai. Login band ho jayega; profile, cart aur wishlist hata diye jayenge.</p>

            <ul class="small text-muted mb-3 ps-3">
                <li>Paid orders ka record company ke liye rahega (billing / support).</li>
                <li>Wallet balance ho to delete se pehle use karein ya support se baat karein.</li>
                <li>Open complaint ho to pehle resolve hone dein.</li>
            </ul>

            @if($walletBalance > 0)
                <div class="alert alert-warning small">
                    Wallet balance: <strong>₹{{ number_format($walletBalance, 2) }}</strong>. Is balance ke saath account delete nahi hoga.
                </div>
            @endif

            @error('account')
                <div class="alert alert-danger small">{{ $message }}</div>
            @enderror

            <form method="POST" action="{{ route('account.delete.destroy') }}">
                @csrf
                @method('DELETE')

                @if($hasPassword)
                    <div class="mb-compact pass-wrap">
                        <label class="form-label">Password confirm karein</label>
                        <input type="password" name="current_password" id="deleteAccountPass" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                        @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                @else
                    <p class="small text-muted mb-2">Aap Google se login karte ho — password ki jagah neeche phrase likhein.</p>
                @endif

                <div class="mb-compact">
                    <label class="form-label">Type karein: <code>DELETE MY ACCOUNT</code></label>
                    <input type="text" name="confirm_phrase" class="form-control @error('confirm_phrase') is-invalid @enderror" value="{{ old('confirm_phrase') }}" autocomplete="off" required>
                    @error('confirm_phrase')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input @error('confirm') is-invalid @enderror" type="checkbox" name="confirm" value="1" id="deleteConfirm" @checked(old('confirm')) required>
                    <label class="form-check-label small" for="deleteConfirm">
                        Main samajh gaya/gayi hoon ki mera account delete ho jayega aur dubara isi email/phone se login nahi kar paunga/paungi (naya register alag hoga).
                    </label>
                    @error('confirm')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-danger w-100 mb-2" @disabled($walletBalance > 0)>
                    Permanently delete my account
                </button>
            </form>

            <div class="auth-links">
                <a href="{{ route('profile') }}">← Profile par wapas</a>
            </div>
        @endguest
    </div>
</div>
@endsection
