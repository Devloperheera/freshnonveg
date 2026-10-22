@extends('Admin.Layout.master')

@section('title', 'Order Steps — FreshNonVeg Admin')

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-list-ol me-2"></i> Order Steps (Home)
        </h2>
        <a href="{{ route('admin.order-steps.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Step
        </a>
    </div>

    <div class="form-block mb-4">
        <h3 class="small text-uppercase fw-bold text-success mb-3">Section heading</h3>
        <form method="POST" action="{{ route('admin.order-steps.update-section') }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label">Title (HTML allowed, e.g. &lt;u&gt;highlight&lt;/u&gt;)</label>
                <input type="text" name="title" class="form-control" required maxlength="255"
                       value="{{ old('title', $section?->title ?? 'Order करना है <u>आसान</u>') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Subtitle</label>
                <input type="text" name="subtitle" class="form-control" maxlength="500"
                       value="{{ old('subtitle', $section?->subtitle) }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fas fa-save me-1"></i> Save heading</button>
            </div>
        </form>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orderSteps as $step)
                        <tr>
                            <td>{{ $step->id }}</td>
                            <td>{{ $step->sort_order }}</td>
                            <td class="fw-semibold">{{ $step->title }}</td>
                            <td class="small">{{ $step->description }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.order-steps.toggle-status', $step), 'active' => $step->isActive()])
                            </td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['edit' => route('admin.order-steps.edit', $step), 'delete' => route('admin.order-steps.destroy', $step), 'confirm' => 'Delete this step?'])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No steps yet. Run HomePageContentSeeder or add manually.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>.form-block{background:#fff;border:1px solid #dee2e6;border-radius:10px;padding:18px 20px;}</style>
@endsection
