@extends('Admin.Layout.master')

@section('title', $product->name.' — Product')

@section('custom_css')
<style>
:root { --primary-color: #2BAE49; --primary-mid: #1E8E3E; --secondary-color: #F57C00; --light-bg: #FFFBF7; }
.product-hero {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-mid));
    border-radius: 12px; padding: 24px; color: #fff; margin-bottom: 20px;
}
.info-card { background: #fff; border-radius: 12px; border: 1px solid #dee2e6; margin-bottom: 20px; overflow: hidden; }
.info-card-header {
    background: var(--light-bg); padding: 12px 18px; font-size: 12px; font-weight: 800;
    color: var(--primary-color); text-transform: uppercase; border-bottom: 1px solid #dee2e6;
}
.info-row { display: flex; gap: 12px; padding: 10px 18px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
.info-row:last-child { border-bottom: none; }
.info-label { width: 35%; font-weight: 700; color: #64748b; font-size: 11px; text-transform: uppercase; }
.gallery-grid { display: flex; flex-wrap: wrap; gap: 10px; padding: 16px; }
.gallery-grid img { width: 140px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6; }
.product-desc { padding: 16px 18px; font-size: 14px; line-height: 1.6; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h4 class="fw-bold mb-0" style="color: var(--primary-color);"><i class="fas fa-box me-2"></i>{{ $product->name }}</h4>
            <small class="text-muted">Product ID #{{ $product->id }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> List</a>
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit me-1"></i> Edit</a>
        </div>
    </div>

    <div class="product-hero">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
            @if($product->isActive())<span class="badge bg-light text-dark">Active</span>@else<span class="badge bg-secondary">Inactive</span>@endif
            @if($product->is_featured)<span class="badge bg-warning text-dark">Featured</span>@endif
            @foreach($product->tags as $tag)<span class="badge bg-primary">{{ $tag->name }}</span>@endforeach
        </div>
        <div class="small opacity-90">
            {{ $product->category?->name ?? 'No category' }} · Banner: {{ $product->banner?->name ?? '—' }}
        </div>
    </div>

    @if($product->images->isNotEmpty())
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-images me-1"></i> Images (order)</div>
            <div class="gallery-grid">
                @foreach($product->images as $img)
                    <img src="{{ $img->url() }}" alt="{{ $product->name }}">
                @endforeach
            </div>
        </div>
    @endif

    @if($product->video_path)
        <div class="info-card">
            <div class="info-card-header"><i class="fas fa-video me-1"></i> Video</div>
            <div class="p-3">
                <video src="{{ $product->videoUrl() }}" controls style="max-width: 100%; max-height: 360px;" class="rounded border"></video>
            </div>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">Weights &amp; prices</div>
                @forelse($product->weights as $w)
                    <div class="info-row">
                        <span class="info-label">{{ $w->weight_label }}</span>
                        <span>₹{{ number_format((float) $w->price, 2) }}</span>
                    </div>
                @empty
                    <div class="info-row"><span class="text-muted">No weights</span></div>
                @endforelse
            </div>
        </div>
        <div class="col-lg-6">
            <div class="info-card">
                <div class="info-card-header">SEO</div>
                <div class="info-row"><span class="info-label">Meta title</span><span>{{ $product->meta_title ?? '—' }}</span></div>
                <div class="info-row"><span class="info-label">Meta description</span><span>{{ $product->meta_description ?? '—' }}</span></div>
                <div class="info-row"><span class="info-label">Keywords</span><span>{{ $product->meta_keywords ?? '—' }}</span></div>
            </div>
        </div>
    </div>

    @if($product->description)
        <div class="info-card">
            <div class="info-card-header">Description</div>
            <div class="product-desc">{!! \App\Support\SafeHtml::display($product->description) !!}</div>
        </div>
    @endif
</div>
@endsection
