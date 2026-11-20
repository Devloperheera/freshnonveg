@extends('Admin.Layout.master')

@section('title', 'Settings — FreshNonVeg Admin')

@section('custom_css')
<style>
    .settings-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 20px;
        height: 100%;
        transition: border-color .2s, box-shadow .2s;
    }
    .settings-card:hover {
        border-color: #2BAE49;
        box-shadow: 0 4px 14px rgba(43, 174, 73, .12);
    }
    .settings-card h3 {
        font-size: 15px;
        font-weight: 800;
        color: #1e8e3e;
        margin-bottom: 8px;
    }
    .settings-card p {
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 14px;
    }
    .settings-kv {
        font-size: 13px;
        margin-bottom: 6px;
    }
    .settings-kv strong { color: #2c3e50; }
    .settings-kv span { color: #495057; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="bi bi-gear-wide-connected me-2"></i> Settings
        </h2>
    </div>
    <p class="small text-muted mb-4">
        Store, delivery aur checkout related options yahan se manage karein.
    </p>

    @if(session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="settings-card">
                <h3><i class="bi bi-truck me-1"></i> Delivery charges</h3>
                <p>Checkout, cart aur mobile app — sab jagah yahi rules lagenge. Pehle se place hue orders par asar nahi.</p>

                <form method="POST" action="{{ route('admin.settings.delivery.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small" for="freeDeliveryMinOrder">Free delivery — minimum order (₹)</label>
                            <input type="number" step="0.01" min="0" name="free_delivery_min_order" id="freeDeliveryMinOrder"
                                   class="form-control @error('free_delivery_min_order') is-invalid @enderror"
                                   value="{{ old('free_delivery_min_order', $freeDeliveryMinOrder + 0) }}" required>
                            @error('free_delivery_min_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Is amount ya usse upar → delivery free. 0 = hamesha free.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small" for="deliveryCharge">Delivery charge (₹)</label>
                            <input type="number" step="0.01" min="0" name="delivery_charge" id="deliveryCharge"
                                   class="form-control @error('delivery_charge') is-invalid @enderror"
                                   value="{{ old('delivery_charge', $deliveryCharge + 0) }}" required>
                            @error('delivery_charge')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Minimum se kam order par lagega.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small" for="instantDeliveryFee">Instant delivery extra (₹)</label>
                            <input type="number" step="0.01" min="0" name="instant_delivery_fee" id="instantDeliveryFee"
                                   class="form-control @error('instant_delivery_fee') is-invalid @enderror"
                                   value="{{ old('instant_delivery_fee', $instantDeliveryFee + 0) }}" required>
                            @error('instant_delivery_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Har instant order par extra — order value kuch bhi ho.</div>
                        </div>
                    </div>

                    <div class="small mt-3 p-2 rounded" style="background:#f8fafc">
                        <strong>Abhi ka rule:</strong>
                        ₹{{ number_format($freeDeliveryMinOrder, 0) }}+ order → delivery <strong>Free</strong>;
                        usse kam → <strong>₹{{ number_format($deliveryCharge, 0) }}</strong>.
                        Instant delivery → upar se <strong>+₹{{ number_format($instantDeliveryFee, 0) }}</strong>.
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm mt-3"><i class="bi bi-save me-1"></i> Save</button>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="settings-kv"><strong>Site URL:</strong> <span>{{ $appUrl }}</span></div>
            <div class="settings-kv"><strong>WhatsApp number:</strong> <span>{{ $whatsappNumber !== '' ? $whatsappNumber : '—' }}</span></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <a href="{{ route('admin.delivery-zones.index') }}" class="text-decoration-none text-reset">
                <div class="settings-card">
                    <h3><i class="bi bi-map me-1"></i> Delivery Zones</h3>
                    <p>Pincode, radius ya polygon se delivery areas set karein.</p>
                    <span class="btn btn-primary btn-sm">Open</span>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.slots.index') }}" class="text-decoration-none text-reset">
                <div class="settings-card">
                    <h3><i class="bi bi-clock me-1"></i> Delivery Slots</h3>
                    <p>Checkout par available time slots manage karein.</p>
                    <span class="btn btn-primary btn-sm">Open</span>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.coupons.index') }}" class="text-decoration-none text-reset">
                <div class="settings-card">
                    <h3><i class="bi bi-ticket-perforated me-1"></i> Coupons</h3>
                    <p>Discount codes aur minimum order rules.</p>
                    <span class="btn btn-primary btn-sm">Open</span>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection