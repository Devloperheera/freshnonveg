@extends('Admin.Layout.master')

@section('title', ($siteStat->exists ? 'Edit' : 'Add').' Stat — FreshNonVeg Admin')

@section('content')
<div class="container mt-4 mb-4">

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-{{ $siteStat->exists ? 'edit' : 'plus-circle' }} me-2"></i>
            {{ $siteStat->exists ? 'Edit Stat' : 'Add Stat' }}
        </h2>
        <a href="{{ route('admin.site-stats.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

    <div id="form-section" class="form-section">
        <form method="POST" action="{{ $siteStat->exists ? route('admin.site-stats.update', $siteStat) : route('admin.site-stats.store') }}">
            @csrf
            @if($siteStat->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Counter value <span class="required-field">*</span></label>
                <input type="number" name="value" min="0" max="99999999" class="form-control @error('value') is-invalid @enderror"
                       value="{{ old('value', $siteStat->value) }}" required>
                @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Label <span class="required-field">*</span></label>
                <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
                       value="{{ old('label', $siteStat->label) }}" required maxlength="255">
                @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sort order</label>
                    <input type="number" name="sort_order" min="0" max="9999" class="form-control"
                           value="{{ old('sort_order', $siteStat->sort_order) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="required-field">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" @selected(old('status', $siteStat->status) == 1)>Active</option>
                        <option value="0" @selected(old('status', $siteStat->status) == 0)>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>
                    {{ $siteStat->exists ? 'Update Stat' : 'Save Stat' }}
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
