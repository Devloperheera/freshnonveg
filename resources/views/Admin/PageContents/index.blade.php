@extends('Admin.Layout.master')

@section('title', 'Page Content — FreshNonVeg Admin')

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-file-alt me-2"></i> Page Content
        </h2>
        <a href="{{ route('admin.page-contents.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Page Content
        </a>
    </div>

    <p class="text-muted small mb-3">Page type <a href="{{ route('admin.pages.index') }}">Pages</a> list se aata hai — pehle wahan page bana lein.</p>

    <div id="table-section" class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Page Type</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contents as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $item->page?->name ?? '—' }}</span></td>
                            <td class="fw-semibold">{{ $item->title }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.page-contents.toggle-status', $item), 'active' => $item->isActive()])
                            </td>
                            <td><span class="small">{{ $item->updated_at?->format('d M Y, H:i') }}</span></td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['edit' => route('admin.page-contents.edit', $item), 'delete' => route('admin.page-contents.destroy', $item), 'confirm' => 'Delete this page content?'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No page content yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
