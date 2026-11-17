@extends('Admin.Layout.master')

@section('title', 'Payment Gateways — FreshNonVeg Admin')

@section('custom_css')
<style>
    .pg-card { border: 2px solid #e5e7eb; border-radius: 14px; background: #fff; padding: 20px; height: 100%; }
    .pg-card.is-live { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
    .pg-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
    .pg-head h3 { font-size: 1.15rem; margin: 0; font-weight: 700; }
    .pg-badge { font-size: .75rem; padding: 4px 10px; border-radius: 999px; font-weight: 600; }
    .pg-badge.on { background: #dcfce7; color: #166534; }
    .pg-badge.off { background: #f3f4f6; color: #4b5563; }
    .pg-badge.warn { background: #fef3c7; color: #92400e; }
    .pg-saved { font-size: .78rem; color: #16a34a; }
    .pg-webhook { font-size: .8rem; word-break: break-all; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 8px; padding: 8px 10px; }
    .pg-help { font-size: .8rem; color: #6b7280; }
</style>
@endsection

@section('content')
<div class="container mt-4 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-credit-card me-2"></i> Payment Gateways
        </h2>
        <div class="small">
            Abhi active:
            @if($active)
                <span class="pg-badge on"><i class="bi bi-check-circle me-1"></i>{{ $active->name }} ({{ $active->environment === 'production' ? 'Live' : 'Test' }})</span>
            @else
                <span class="pg-badge warn"><i class="bi bi-exclamation-triangle me-1"></i>Koi nahi — checkout band hai</span>
            @endif
        </div>
    </div>

    <p class="pg-help mb-4">
        Ek samay par sirf <strong>ek gateway</strong> active rehta hai. Jis gateway ko active karoge, website aur mobile app ke saare naye payments usi se honge.
        Keys encrypted form me save hoti hain. Secret field khali chhodoge to purani saved value hi rahegi.
    </p>

    <div class="row g-4">
        @foreach($gateways as $code => $gw)
            @php
                $isRzp = $code === 'razorpay';
                $isLive = $active && $active->code === $code;
                $useOld = session('open_gateway') === $code;
                $envValue = $useOld ? old('environment', $gw->environment) : $gw->environment;
                $activeValue = $useOld ? (bool) old('is_active', $isLive) : $isLive;
            @endphp
            <div class="col-lg-6">
                <div class="pg-card {{ $isLive ? 'is-live' : '' }}">
                    <div class="pg-head">
                        <h3>
                            <i class="bi {{ $isRzp ? 'bi-lightning-charge' : 'bi-cash-coin' }} me-1"></i>
                            {{ $gw->name }}
                        </h3>
                        @if($isLive)
                            <span class="pg-badge on">Active</span>
                        @elseif($gw->isConfigured())
                            <span class="pg-badge off">Keys saved · Inactive</span>
                        @else
                            <span class="pg-badge warn">Keys nahi hain</span>
                        @endif
                    </div>

                    @foreach(['key_id', 'test', 'environment'] as $errKey)
                        @error($code.'.'.$errKey)<div class="alert alert-danger py-1 px-2 small">{{ $message }}</div>@enderror
                    @endforeach

                    <form method="POST" action="{{ route('admin.payment-gateways.update', $code) }}" autocomplete="off">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Environment <span class="required-field">*</span></label>
                            <select name="environment" class="form-select">
                                <option value="sandbox" @selected($envValue === 'sandbox')>Sandbox / Test</option>
                                <option value="production" @selected($envValue === 'production')>Production / Live</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ $isRzp ? 'Key ID' : 'App ID (Client ID)' }}</label>
                            <input type="text" name="key_id" class="form-control" maxlength="191"
                                   placeholder="{{ $gw->key_id ? \App\Models\PaymentGateway::mask($gw->key_id).' (saved — badalna ho to nayi daalein)' : ($isRzp ? 'rzp_test_xxxxxxxx / rzp_live_xxxxxxxx' : 'Cashfree App ID') }}">
                            @if($gw->key_id)<div class="pg-saved mt-1"><i class="bi bi-check2"></i> Saved: {{ \App\Models\PaymentGateway::mask($gw->key_id) }}</div>@endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ $isRzp ? 'Key Secret' : 'Secret Key (Client Secret)' }}</label>
                            <input type="password" name="key_secret" class="form-control" maxlength="255" autocomplete="new-password"
                                   placeholder="{{ $gw->key_secret ? '•••••••• (saved — khali chhodo to same rahega)' : 'Secret key daalein' }}">
                            @if($gw->key_secret)<div class="pg-saved mt-1"><i class="bi bi-check2"></i> Secret saved</div>@endif
                        </div>

                        @if($isRzp)
                            <div class="mb-3">
                                <label class="form-label">Webhook Secret <span class="text-muted small">(recommended)</span></label>
                                <input type="password" name="webhook_secret" class="form-control" maxlength="255" autocomplete="new-password"
                                       placeholder="{{ $gw->webhook_secret ? '•••••••• (saved)' : 'Razorpay dashboard me webhook banate waqt jo secret diya' }}">
                                @if($gw->webhook_secret)<div class="pg-saved mt-1"><i class="bi bi-check2"></i> Webhook secret saved</div>@endif
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label mb-1">Webhook URL <span class="text-muted small">({{ $gw->name }} dashboard me lagayein)</span></label>
                            <div class="pg-webhook"><code>{{ $webhooks[$code] }}</code></div>
                            <div class="pg-help mt-1">
                                @if($isRzp)
                                    Razorpay Dashboard → Settings → Webhooks → Add: events <code>payment.captured</code>, <code>order.paid</code>.
                                @else
                                    Cashfree Dashboard → Developers → Webhooks. Website ka HTTPS domain bhi whitelist karein.
                                @endif
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1"
                                   id="active_{{ $code }}" @checked($activeValue)>
                            <label class="form-check-label" for="active_{{ $code }}">
                                Is gateway ko <strong>active</strong> karo (dusra apne aap inactive ho jayega)
                            </label>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save {{ $gw->name }}
                            </button>
                        </div>
                    </form>

                    <hr>

                    <div class="d-flex gap-2 flex-wrap">
                        @if($gw->isConfigured())
                            <form method="POST" action="{{ route('admin.payment-gateways.test', $code) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-info btn-sm"><i class="bi bi-plug me-1"></i> Test connection</button>
                            </form>

                            @if($isLive)
                                <form method="POST" action="{{ route('admin.payment-gateways.deactivate', $code) }}"
                                      onsubmit="return confirm('{{ $gw->name }} inactive karne se checkout band ho jayega (jab tak dusra active na ho). Continue?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-outline-warning btn-sm"><i class="bi bi-pause-circle me-1"></i> Deactivate</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.payment-gateways.activate', $code) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-circle me-1"></i> Activate {{ $gw->name }}</button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.payment-gateways.update', $code) }}"
                                  onsubmit="return confirm('{{ $gw->name }} ki saari saved keys delete karein?')">
                                @csrf @method('PUT')
                                <input type="hidden" name="environment" value="{{ $gw->environment }}">
                                <input type="hidden" name="clear_keys" value="1">
                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i> Remove keys</button>
                            </form>
                        @else
                            <span class="pg-help">Keys save karne ke baad Test / Activate button dikhenge.</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
