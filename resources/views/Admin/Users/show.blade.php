@extends('Admin.Layout.master')

@section('title', 'User #'.$user->id.' — '.$user->name)

@section('custom_css')
<style>
:root { --primary-color: #2BAE49; --secondary-color: #F57C00; }
.usv-compact { font-size: 12px; }
.usv-hero {
    background: linear-gradient(135deg, #2BAE49, #1E8E3E);
    border-radius: 8px; padding: 12px 14px; color: #fff; margin-bottom: 12px;
}
.usv-av {
    width: 44px; height: 44px; border-radius: 50%; border: 2px solid rgba(255,255,255,.35);
    object-fit: cover; background: #F57C00; display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 800; flex-shrink: 0;
}
.usv-pill {
    display: inline-flex; align-items: center; gap: 4px; background: rgba(255,255,255,.18);
    padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 700; margin: 2px 4px 0 0;
}
.usv-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px; overflow: hidden;
}
.usv-card-head {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    padding: 7px 10px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #2BAE49;
}
.usv-card-head i { color: #F57C00; margin-right: 4px; }
.usv-card-body { padding: 8px 10px; }
.usv-row {
    display: flex; gap: 8px; align-items: flex-start; padding: 6px 0; border-bottom: 1px solid #f1f5f9;
    font-size: 11px; line-height: 1.35;
}
.usv-row:last-child { border-bottom: none; padding-bottom: 0; }
.usv-meta { color: #64748b; font-size: 10px; }
.usv-stars { color: #F57C00; font-size: 10px; letter-spacing: 1px; }
.adm-prod-thumb { width: 36px; height: 36px; border-radius: 6px; object-fit: cover; flex-shrink: 0; border: 1px solid #e2e8f0; }
.adm-prod-thumb--empty {
    display: inline-flex; align-items: center; justify-content: center; background: #f1f5f9; color: #94a3b8; font-size: 14px;
}
.usv-btn-all { font-size: 10px; font-weight: 700; padding: 2px 8px; }
.usv-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
@media (max-width: 992px) { .usv-grid-2 { grid-template-columns: 1fr; } }
.usv-kv { display: grid; grid-template-columns: 88px 1fr; gap: 4px 8px; font-size: 11px; }
.usv-kv dt { color: #64748b; font-weight: 700; font-size: 10px; text-transform: uppercase; margin: 0; }
.usv-kv dd { margin: 0; color: #0f172a; word-break: break-word; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-2 mb-3 usv-compact">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <div>
            <h5 class="mb-0 fw-bold text-success"><i class="fas fa-user me-1"></i> Customer #{{ $user->id }}</h5>
        </div>
        <div class="d-flex gap-1">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:11px">← List</a>
            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-sm py-0 px-2 {{ $user->isActive() ? 'btn-outline-danger' : 'btn-success' }}" style="font-size:11px">
                    {{ $user->isActive() ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success py-1 px-2 small mb-2">{{ session('success') }}</div>
    @endif

    <div class="usv-hero">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($user->profile_image)
                <img src="{{ $user->profile_image }}" alt="" class="usv-av">
            @else
                <div class="usv-av">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            @endif
            <div class="flex-grow-1 min-w-0">
                <div class="fw-bold" style="font-size:15px">{{ $user->name }}</div>
                <div class="opacity-90" style="font-size:11px">{{ $user->email ?? '—' }} · {{ $user->phone ?? '—' }}</div>
                <div>
                    <span class="usv-pill">{{ $user->isActive() ? 'Active' : 'Inactive' }}</span>
                    <span class="usv-pill">Wallet ₹{{ number_format((float) $user->wallet_balance, 2) }}</span>
                    <span class="usv-pill">Orders {{ $user->orders_count }}</span>
                    <span class="usv-pill">Ratings {{ $user->product_reviews_count }}</span>
                    <span class="usv-pill">Complaints {{ $user->complaints_count }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="usv-grid-2">
        <div class="usv-card">
            <div class="usv-card-head"><span><i class="fas fa-id-card"></i> Quick info</span></div>
            <div class="usv-card-body">
                <dl class="usv-kv mb-0">
                    <dt>Registered</dt><dd>{{ $user->created_at?->format('d M Y, h:i A') }}</dd>
                    <dt>Source</dt><dd>{{ $user->registration_source ?? '—' }}</dd>
                    <dt>Logins</dt><dd>{{ $user->login_count ?? 0 }} · last {{ $user->last_login_at?->format('d M Y') ?? '—' }}</dd>
                    <dt>Google</dt><dd>{{ $user->google_id ? 'Linked' : 'No' }}</dd>
                </dl>
            </div>
        </div>

        <div class="usv-card">
            <div class="usv-card-head">
                <span><i class="fas fa-location-dot"></i> Shipping addresses ({{ $shippingAddresses->count() }})</span>
            </div>
            <div class="usv-card-body">
                @forelse($shippingAddresses as $addr)
                    <div class="usv-row flex-column align-items-stretch gap-1">
                        <span class="badge bg-light text-dark border align-self-start" style="font-size:9px">{{ $addr['label'] }}</span>
                        <div>{{ $addr['line'] }}</div>
                        @if($addr['meta'])<div class="usv-meta">{{ $addr['meta'] }}</div>@endif
                        @if($addr['when'])<div class="usv-meta">Used {{ $addr['when']->format('d M Y') }}</div>@endif
                    </div>
                @empty
                    <p class="text-muted mb-0 small">Koi address save nahi.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="usv-card">
        <div class="usv-card-head">
            <span><i class="fas fa-bag-shopping"></i> Latest orders ({{ min(5, $user->orders_count) }}/{{ $user->orders_count }})</span>
            @if($user->orders_count > 5)
                <a href="{{ route('admin.orders.index', ['user_id' => $user->id]) }}" class="btn btn-outline-success usv-btn-all">View all</a>
            @endif
        </div>
        <div class="usv-card-body">
            @forelse($recentOrders as $order)
                <div class="usv-row flex-column align-items-stretch gap-1 mb-1">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                        <a href="{{ route('admin.orders.show', $order, false) }}" class="fw-semibold text-success text-decoration-none">{{ $order->order_number }}</a>
                        <span class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary text-capitalize" style="font-size:9px">{{ $order->status }}</span>
                            <span class="fw-bold">₹{{ number_format((float) $order->total, 2) }}</span>
                            <a href="{{ route('admin.orders.show', $order, false) }}" class="btn btn-outline-success btn-sm py-0 px-2 usv-btn-all">View</a>
                        </span>
                    </div>
                    <div class="usv-meta">{{ $order->created_at?->format('d M Y, h:i A') }} · {{ $order->pincode }}</div>
                    @foreach($order->items as $item)
                        <div class="d-flex align-items-center gap-2 mt-1">
                            @include('Admin.partials.product-thumb', ['product' => $item->product, 'size' => 32])
                            <div class="min-w-0">
                                <div>{{ $item->product_name }} <span class="text-muted">({{ $item->weight_label }})</span></div>
                                <div class="usv-meta">× {{ $item->quantity }} · ₹{{ number_format((float) $item->unit_price, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @empty
                <p class="text-muted mb-0">Abhi koi order nahi.</p>
            @endforelse
        </div>
    </div>

    <div class="usv-grid-2">
        <div class="usv-card">
            <div class="usv-card-head">
                <span><i class="fas fa-star"></i> Ratings ({{ $ratings->count() }})</span>
                @if($ratings->isNotEmpty())
                    <a href="{{ route('admin.product-reviews.index', ['user_id' => $user->id]) }}" class="btn btn-outline-success usv-btn-all">View all</a>
                @endif
            </div>
            <div class="usv-card-body">
                @forelse($ratings as $review)
                    <div class="usv-row">
                        @include('Admin.partials.product-thumb', ['product' => $review->product, 'size' => 34])
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-semibold">{{ $review->product?->name ?? 'Product' }}</div>
                            <div class="usv-stars">@for($s = 1; $s <= 5; $s++){{ $s <= $review->rating ? '★' : '☆' }}@endfor</div>
                            @if($review->review)<div class="usv-meta mt-1">{{ \Illuminate\Support\Str::limit($review->review, 120) }}</div>@endif
                            <div class="usv-meta">{{ $review->created_at?->format('d M Y') }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Koi rating nahi.</p>
                @endforelse
            </div>
        </div>

        <div class="usv-card">
            <div class="usv-card-head">
                <span><i class="fas fa-comment-dots"></i> Complaints ({{ $complaints->count() }})</span>
                @if($complaints->isNotEmpty())
                    <a href="{{ route('admin.complaints.index', ['user_id' => $user->id]) }}" class="btn btn-outline-success usv-btn-all">View all</a>
                @endif
            </div>
            <div class="usv-card-body">
                @forelse($complaints as $complaint)
                    <div class="usv-row flex-column align-items-stretch gap-1">
                        <div class="d-flex justify-content-between gap-2">
                            <a href="{{ route('admin.complaints.show', $complaint) }}" class="fw-semibold text-decoration-none">{{ \Illuminate\Support\Str::limit($complaint->subject, 48) }}</a>
                            <span class="badge bg-secondary text-capitalize" style="font-size:9px">{{ $complaint->statusLabel() }}</span>
                        </div>
                        <div class="usv-meta">{{ $complaint->created_at?->format('d M Y') }}
                            @if($complaint->order) · {{ $complaint->order->order_number }}@endif
                            @if((float) $complaint->credited_amount > 0) · ₹{{ number_format((float) $complaint->credited_amount, 2) }} credited @endif
                        </div>
                        <div>{{ \Illuminate\Support\Str::limit($complaint->message, 100) }}</div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Koi complaint nahi.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="usv-grid-2">
        <div class="usv-card">
            <div class="usv-card-head"><span><i class="fas fa-cart-shopping"></i> Cart ({{ $cartItems->count() }})</span></div>
            <div class="usv-card-body">
                @forelse($cartItems as $item)
                    <div class="usv-row">
                        @include('Admin.partials.product-thumb', ['item' => $item, 'size' => 32])
                        <div>
                            <div>{{ $item->product_name }}</div>
                            <div class="usv-meta">{{ $item->weight_label }} · × {{ $item->quantity }} · ₹{{ number_format($item->lineTotal(), 2) }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Cart khali.</p>
                @endforelse
            </div>
        </div>

        <div class="usv-card">
            <div class="usv-card-head"><span><i class="fas fa-heart"></i> Wishlist ({{ $wishlistItems->count() }})</span></div>
            <div class="usv-card-body">
                @forelse($wishlistItems as $item)
                    <div class="usv-row">
                        @include('Admin.partials.product-thumb', ['product' => $item->product, 'size' => 32])
                        <div>
                            <div>{{ $item->product_name }}</div>
                            <div class="usv-meta">{{ $item->created_at?->format('d M Y') }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Wishlist khali.</p>
                @endforelse
            </div>
        </div>
    </div>

    <details class="usv-card">
        <summary class="usv-card-head mb-0" style="cursor:pointer;list-style:none">
            <span><i class="fas fa-mobile-alt"></i> App & device (extra)</span>
        </summary>
        <div class="usv-card-body">
            <dl class="usv-kv mb-0">
                <dt>App</dt><dd>{{ $user->is_app_installed ? 'Installed' : 'No' }} · v{{ $user->app_version ?? '—' }}</dd>
                <dt>Device</dt><dd>{{ trim(($user->device_brand ?? '').' '.($user->device_model ?? '')) ?: '—' }}</dd>
                <dt>FCM</dt><dd><code style="font-size:10px">{{ $user->fcm_token ? \Illuminate\Support\Str::limit($user->fcm_token, 60) : '—' }}</code></dd>
                <dt>Last active</dt><dd>{{ $user->last_active_at?->format('d M Y, H:i') ?? '—' }}</dd>
            </dl>
        </div>
    </details>
</div>
@endsection
