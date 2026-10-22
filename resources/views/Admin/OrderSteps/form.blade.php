@extends('Admin.Layout.master')

@section('title', ($orderStep->exists ? 'Edit' : 'Add').' Order Step — FreshNonVeg Admin')

@section('content')
<div class="container mt-4 mb-4">

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-{{ $orderStep->exists ? 'edit' : 'plus-circle' }} me-2"></i>
            {{ $orderStep->exists ? 'Edit Order Step' : 'Add Order Step' }}
        </h2>
        <a href="{{ route('admin.order-steps.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

    <div id="form-section" class="form-section">
        <form method="POST" action="{{ $orderStep->exists ? route('admin.order-steps.update', $orderStep) : route('admin.order-steps.store') }}">
            @csrf
            @if($orderStep->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Title <span class="required-field">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title', $orderStep->title) }}" required maxlength="255">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                       value="{{ old('description', $orderStep->description) }}" maxlength="500">
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sort order</label>
                    <input type="number" name="sort_order" min="0" max="9999" class="form-control"
                           value="{{ old('sort_order', $orderStep->sort_order) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="required-field">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" @selected(old('status', $orderStep->status) == 1)>Active</option>
                        <option value="0" @selected(old('status', $orderStep->status) == 0)>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>
                    {{ $orderStep->exists ? 'Update Order Step' : 'Save Order Step' }}
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
