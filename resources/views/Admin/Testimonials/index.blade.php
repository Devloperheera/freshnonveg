@extends('Admin.Layout.master')

@section('title', 'Testimonials — FreshNonVeg Admin')

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if(session('success'))
        <div class="alert alert-success py-2 small">{{ session('success') }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-quote-left me-2"></i> Testimonials
        </h2>
        <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Add Testimonial
        </a>
    </div>

    <div id="table-section" class="table-container">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Star Rating</th>
                        <th>Short Description</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($testimonials as $testimonial)
                        <tr>
                            <td>{{ $testimonial->id }}</td>
                            <td class="fw-semibold">{{ $testimonial->name }}</td>
                            <td><span class="text-warning" title="{{ $testimonial->star_rating }}/5">{!! $testimonial->starsHtml() !!}</span></td>
                            <td class="small" style="max-width: 280px;">{{ Str::limit($testimonial->short_description, 120) }}</td>
                            <td>
                                @include('Admin.partials.status-toggle', ['action' => route('admin.testimonials.toggle-status', $testimonial), 'active' => $testimonial->isActive()])
                            </td>
                            <td><span class="small">{{ $testimonial->updated_at?->format('d M Y, H:i') }}</span></td>
                            <td class="text-nowrap">
                                @include('Admin.partials.row-actions', ['edit' => route('admin.testimonials.edit', $testimonial), 'delete' => route('admin.testimonials.destroy', $testimonial), 'confirm' => 'Delete this testimonial?'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No testimonials yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
