@extends('Admin.Layout.master')

@section('title', 'Banners — FreshNonVeg Admin')

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-images me-2"></i> Banners
        </h2>
        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Banner
        </a>
    </div>

    <div class="alert alert-info py-2 small mb-3">
        <i class="fas fa-info-circle me-1"></i>
        Recommended banner size: <strong>1600 × 640 px</strong> (width × height). Aap koi bhi size upload kar sakte ho — koi size check nahi lagaya gaya.
    </div>

    <div id="table-section" class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Banner Name</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $banner)
                        <tr>
                            <td>{{ $banner->id }}</td>
                            <td>
                                <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->name }}"
                                     class="rounded border" style="max-width: 160px; max-height: 64px; object-fit: cover;">
                            </td>
                            <td class="fw-semibold">{{ $banner->name }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.banners.toggle-status', $banner), 'active' => $banner->isActive()])
                            </td>
                            <td><span class="small">{{ $banner->updated_at?->format('d M Y, H:i') }}</span></td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['edit' => route('admin.banners.edit', $banner), 'delete' => route('admin.banners.destroy', $banner), 'confirm' => 'Delete this banner?'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No banners yet. Add your first banner.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
