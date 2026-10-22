@extends('Admin.Layout.master')

@section('title', 'Delivery Zones — FreshNonVeg Admin')

@section('custom_css')
<style>
.zone-type-badge{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:800;padding:3px 10px;border-radius:999px}
.zone-type-radius{background:#e0f2fe;color:#0369a1}
.zone-type-pincode{background:#fde68a;color:#78350f}
.zone-type-polygon{background:#ede9fe;color:#5b21b6}
.zone-detail{font-size:12.5px;color:#334155}
.zone-detail i{color:#F57C00}
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-map-marked-alt me-2"></i> Delivery Zones
        </h2>
        <a href="{{ route('admin.delivery-zones.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> New Zone
        </a>
    </div>

    <p class="small text-muted mb-3">
        Checkout par customer ka pincode in zones se check hota hai. Koi active zone nahi hai to har jagah delivery allowed rahegi.
        Chhota sort number pehle check hota hai.
    </p>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Zone Name</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($zones as $zone)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">{{ $zone->name }}</div>
                                @if($zone->description)
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($zone->description, 60) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="zone-type-badge zone-type-{{ $zone->type }}">
                                    {{ \App\Models\DeliveryZone::TYPE_ICONS[$zone->type] ?? '' }} {{ ucfirst($zone->type) }}
                                </span>
                            </td>
                            <td class="zone-detail">
                                @if($zone->type === 'radius')
                                    <i class="fas fa-bullseye me-1"></i><strong>{{ rtrim(rtrim(number_format((float) $zone->radius_km, 2), '0'), '.') }} KM</strong>
                                    @if($zone->center_address) · {{ \Illuminate\Support\Str::limit($zone->center_address, 40) }} @endif
                                @elseif($zone->type === 'pincode')
                                    <i class="fas fa-list me-1"></i><strong>{{ $zone->pincodes_count }}</strong> {{ $zone->pincodes_count === 1 ? 'Pincode' : 'Pincodes' }}
                                @else
                                    <i class="fas fa-draw-polygon me-1"></i><strong>{{ count($zone->polygon ?? []) }}</strong> Points
                                @endif
                            </td>
                            <td>{{ $zone->sort_order }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.delivery-zones.toggle-status', $zone), 'active' => $zone->isActive()])
                            </td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', [
                                    'edit' => route('admin.delivery-zones.edit', $zone),
                                    'delete' => route('admin.delivery-zones.destroy', $zone),
                                    'confirm' => 'Delete zone "'.$zone->name.'"?',
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No delivery zones yet. "New Zone" se add karein.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
