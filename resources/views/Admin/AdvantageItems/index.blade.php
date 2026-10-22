@extends('Admin.Layout.master')

@section('title', 'Fresh Advantages — FreshNonVeg Admin')

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-leaf me-2"></i> Fresh Advantages (Home)
        </h2>
        <a href="{{ route('admin.advantage-items.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Item
        </a>
    </div>

    <div class="form-block mb-4">
        <h3 class="small text-uppercase fw-bold text-success mb-3">Section heading</h3>
        <form method="POST" action="{{ route('admin.advantage-items.update-section') }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label">Title (HTML allowed)</label>
                <input type="text" name="title" class="form-control" required maxlength="255"
                       value="{{ old('title', $section?->title ?? 'Fresh खाने के <u>फायदे!</u>') }}">
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
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advantageItems as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->sort_order }}</td>
                            <td class="fs-4">{{ $item->icon ?: '—' }}</td>
                            <td class="fw-semibold">{{ $item->title }}</td>
                            <td class="small">{{ $item->description }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.advantage-items.toggle-status', $item), 'active' => $item->isActive()])
                            </td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['edit' => route('admin.advantage-items.edit', $item), 'delete' => route('admin.advantage-items.destroy', $item), 'confirm' => 'Delete this item?'])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No items yet. Run HomePageContentSeeder or add manually.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>.form-block{background:#fff;border:1px solid #dee2e6;border-radius:10px;padding:18px 20px;}</style>
@endsection
