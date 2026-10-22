@extends('Admin.Layout.master')

@section('title', 'FAQs — FreshNonVeg Admin')

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-question-circle me-2"></i> FAQs
        </h2>
        <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add FAQ
        </a>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order</th>
                        <th>Question</th>
                        <th>Answer</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $faq)
                        <tr>
                            <td>{{ $faq->id }}</td>
                            <td>{{ $faq->sort_order }}</td>
                            <td class="fw-semibold" style="max-width:240px;">{{ $faq->question }}</td>
                            <td class="small" style="max-width:320px;">{{ Str::limit($faq->answer, 120) }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.faqs.toggle-status', $faq), 'active' => $faq->isActive()])
                            </td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['edit' => route('admin.faqs.edit', $faq), 'delete' => route('admin.faqs.destroy', $faq), 'confirm' => 'Delete this FAQ?'])
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No FAQs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
