@extends('Admin.Layout.master')

@section('title', ($category->exists ? 'Edit' : 'Add').' Category — FreshNonVeg Admin')

@section('content')
<div class="container mt-4 mb-4">

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-{{ $category->exists ? 'edit' : 'plus-circle' }} me-2"></i>
            {{ $category->exists ? 'Edit Category' : 'Add New Category' }}
        </h2>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

    <div id="form-section" class="form-section">
        <form method="POST"
              action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}"
              enctype="multipart/form-data">
            @csrf
            @if($category->exists)
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">
                        Category Name <span class="required-field">*</span>
                    </label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $category->name) }}" required
                           placeholder="e.g. Chicken, Mutton, Fish">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">
                        Status <span class="required-field">*</span>
                    </label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="1" @selected(old('status', $category->status) == 1)>Active</option>
                        <option value="0" @selected(old('status', $category->status) == 0)>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-3">
                    <label for="image" class="form-label">
                        Category Image @if(! $category->exists)<span class="required-field">*</span>@endif
                    </label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                           id="image" name="image" accept="image/*" {{ $category->exists ? '' : 'required' }}>
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Max 5 MB · JPG, PNG, WebP, etc.</div>
                </div>
                @if($category->exists && $category->image)
                    <div class="col-12 mb-3">
                        <label class="form-label d-block">Current image</label>
                        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}"
                             class="rounded border" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                @endif
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>
                    {{ $category->exists ? 'Update Category' : 'Save Category' }}
                </button>
                <button type="reset" class="btn btn-warning">
                    <i class="fas fa-undo me-2"></i>
                    Reset Form
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom_js')
<script>
document.getElementById('image')?.addEventListener('change', function (e) {
    var file = e.target.files && e.target.files[0];
    if (!file) return;
    var preview = document.getElementById('imagePreview');
    if (!preview) {
        preview = document.createElement('img');
        preview.id = 'imagePreview';
        preview.className = 'rounded border mt-2';
        preview.style.width = '120px';
        preview.style.height = '120px';
        preview.style.objectFit = 'cover';
        e.target.parentElement.appendChild(preview);
    }
    preview.src = URL.createObjectURL(file);
});
</script>
@endsection
