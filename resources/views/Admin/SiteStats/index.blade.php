@extends('Admin.Layout.master')

@section('title', 'Home Stats — FreshNonVeg Admin')

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-chart-bar me-2"></i> Home Stats
        </h2>
        <a href="{{ route('admin.site-stats.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Stat
        </a>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order</th>
                        <th>Value</th>
                        <th>Label</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siteStats as $stat)
                        <tr>
                            <td>{{ $stat->id }}</td>
                            <td>{{ $stat->sort_order }}</td>
                            <td><strong>{{ number_format($stat->value) }}</strong></td>
                            <td>{{ $stat->label }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.site-stats.toggle-status', $stat), 'active' => $stat->isActive()])
                            </td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['edit' => route('admin.site-stats.edit', $stat), 'delete' => route('admin.site-stats.destroy', $stat), 'confirm' => 'Delete this stat?'])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No stats yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
