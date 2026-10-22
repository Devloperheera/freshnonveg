@extends('Website.Layout.master')

@section('title', 'Complete Registration – FreshNonVeg')

@section('content')
@include('Website.Auth.partials.styles')
<div class="auth-wrap auth-wrap--compact">
    <div class="auth-card auth-card--compact">
        <h1>बस एक step बाकी 📱</h1>
        <p class="sub">Google login ho gaya — ab <strong>मोबाइल नंबर</strong> add karo, registration complete!</p>

        <form method="POST" action="{{ route('auth.google.phone.post') }}">
            @csrf
            <div class="mb-compact">
                <label class="form-label">नाम (Name)</label>
                <input type="text" class="form-control" value="{{ $name }}" readonly>
            </div>
            <div class="mb-compact">
                <label class="form-label">ईमेल (Email)</label>
                <input type="email" class="form-control" value="{{ $email }}" readonly>
            </div>
            <div class="mb-compact">
                <label class="form-label">मोबाइल नंबर (Phone)</label>
                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="98XXXXXXXX" required autofocus>
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-brand">रजिस्ट्रेशन पूरा करें</button>
        </form>
    </div>
</div>
@endsection
