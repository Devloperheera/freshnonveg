@extends('Admin.Layout.master')

@section('title', 'Delivery Slots — FreshNonVeg Admin')

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-clock me-2"></i> Delivery Time Slots
        </h2>
        <a href="{{ route('admin.slots.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add slot
        </a>
    </div>

    <p class="small text-muted">Checkout par sirf <strong>Active</strong> slots dikhte hain. Window: subah 9:00 se shaam 9:00 tak, maximum 12 slots. Abhi {{ $slots->count() }} / 12.</p>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order</th>
                        <th>Slot</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($slots as $slot)
                        <tr>
                            <td>{{ $slot->id }}</td>
                            <td>{{ $slot->sort_order }}</td>
                            <td class="fw-semibold">{{ $slot->label }}</td>
                            <td>{{ $slot->startInput() }}</td>
                            <td>{{ $slot->endInput() }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.slots.toggle-status', $slot), 'active' => $slot->isActive()])
                            </td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['edit' => route('admin.slots.edit', $slot), 'delete' => route('admin.slots.destroy', $slot), 'confirm' => 'Delete this slot?'])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No delivery slots yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
