@extends('Admin.Layout.master')

@section('title', ($advantageItem->exists ? 'Edit' : 'Add').' Advantage — FreshNonVeg Admin')

@section('content')
<div class="container mt-4 mb-4">

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-{{ $advantageItem->exists ? 'edit' : 'plus-circle' }} me-2"></i>
            {{ $advantageItem->exists ? 'Edit Advantage' : 'Add Advantage' }}
        </h2>
        <a href="{{ route('admin.advantage-items.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

    <div id="form-section" class="form-section">
        <form method="POST" action="{{ $advantageItem->exists ? route('admin.advantage-items.update', $advantageItem) : route('admin.advantage-items.store') }}">
            @csrf
            @if($advantageItem->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Icon (emoji)</label>
                <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
                       value="{{ old('icon', $advantageItem->icon) }}" maxlength="32" placeholder="💪">
                @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Title <span class="required-field">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title', $advantageItem->title) }}" required maxlength="255">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" maxlength="500">{{ old('description', $advantageItem->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sort order</label>
                    <input type="number" name="sort_order" min="0" max="9999" class="form-control"
                           value="{{ old('sort_order', $advantageItem->sort_order) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="required-field">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" @selected(old('status', $advantageItem->status) == 1)>Active</option>
                        <option value="0" @selected(old('status', $advantageItem->status) == 0)>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>
                    {{ $advantageItem->exists ? 'Update Advantage' : 'Save Advantage' }}
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
