@extends('Admin.Layout.master')

@section('title', ($slot->exists ? 'Edit' : 'Add').' Delivery Slot — FreshNonVeg Admin')

@section('content')
<div class="container mt-4 mb-4">

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-{{ $slot->exists ? 'edit' : 'plus-circle' }} me-2"></i>
            {{ $slot->exists ? 'Edit delivery slot' : 'Add delivery slot' }}
        </h2>
        <a href="{{ route('admin.slots.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

    <div id="form-section" class="form-section">
        <form method="POST" action="{{ $slot->exists ? route('admin.slots.update', $slot) : route('admin.slots.store') }}">
            @csrf
            @if($slot->exists) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start time <span class="required-field">*</span></label>
                    <input type="time" name="start_time" min="09:00" max="20:00" step="60"
                           class="form-control @error('start_time') is-invalid @enderror"
                           value="{{ old('start_time', $slot->startInput()) }}" required>
                    @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">End time <span class="required-field">*</span></label>
                    <input type="time" name="end_time" min="10:00" max="21:00" step="60"
                           class="form-control @error('end_time') is-invalid @enderror"
                           value="{{ old('end_time', $slot->endInput()) }}" required>
                    @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Label</label>
                <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
                       value="{{ old('label', $slot->label) }}" maxlength="80" placeholder="Khali chhodo to time se ban jayega">
                @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <p class="small text-muted mb-0 mt-1">Example: 09:00 AM – 10:00 AM. Slot sirf 9:00 AM se 9:00 PM ke beech.</p>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sort order</label>
                    <input type="number" name="sort_order" min="0" max="9999" class="form-control"
                           value="{{ old('sort_order', $slot->sort_order) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="required-field">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" @selected(old('status', $slot->status) == 1)>Active</option>
                        <option value="0" @selected(old('status', $slot->status) == 0)>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>
                    {{ $slot->exists ? 'Update Slot' : 'Save Slot' }}
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
