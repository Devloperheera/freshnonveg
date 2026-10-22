@extends('Admin.Layout.master')

@section('title', 'Pages — FreshNonVeg Admin')

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-file me-2"></i> Pages
        </h2>
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Page
        </a>
    </div>

    <p class="text-muted small mb-3">Yahan sirf page ka naam aur status set hota hai. Content ke liye <a href="{{ route('admin.page-contents.index') }}">Page Content</a> use karein.</p>

    <div id="table-section" class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Page Name</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                        <tr>
                            <td>{{ $page->id }}</td>
                            <td class="fw-semibold">{{ $page->name }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.pages.toggle-status', $page), 'active' => $page->isActive()])
                            </td>
                            <td><span class="small">{{ $page->updated_at?->format('d M Y, H:i') }}</span></td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['edit' => route('admin.pages.edit', $page), 'delete' => route('admin.pages.destroy', $page), 'confirm' => 'Delete this page? Related page content bhi delete ho jayega.'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No pages yet. Pehle page add karein, phir Page Content mein detail bharein.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
