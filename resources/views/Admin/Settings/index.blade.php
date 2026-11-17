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

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="settings-kv"><strong>Site URL:</strong> <span>{{ $appUrl }}</span></div>
            <div class="settings-kv"><strong>Instant delivery fee:</strong> <span>₹{{ number_format($instantDeliveryFee, 0) }}</span></div>
            <div class="settings-kv"><strong>WhatsApp number:</strong> <span>{{ $whatsappNumber !== '' ? $whatsappNumber : '—' }}</span></div>
            <p class="small text-muted mb-0 mt-2">
                Fee badalne ke liye server <code>.env</code> me <code>INSTANT_DELIVERY_FEE</code> set karein, phir <code>php artisan config:cache</code>.
            </p>
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