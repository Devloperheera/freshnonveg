@extends('Admin.Layout.master')

@section('title', ($faq->exists ? 'Edit' : 'Add').' FAQ — FreshNonVeg Admin')

@section('content')
<div class="container mt-4 mb-4">

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-{{ $faq->exists ? 'edit' : 'plus-circle' }} me-2"></i>
            {{ $faq->exists ? 'Edit FAQ' : 'Add FAQ' }}
        </h2>
        <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

    <div id="form-section" class="form-section">
        <form method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">
            @csrf
            @if($faq->exists) @method('PUT') @endif

            <div class="mb-3">
                <label class="form-label">Question <span class="required-field">*</span></label>
                <input type="text" name="question" class="form-control @error('question') is-invalid @enderror"
                       value="{{ old('question', $faq->question) }}" required maxlength="500">
                @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Answer <span class="required-field">*</span></label>
                <textarea name="answer" rows="5" class="form-control @error('answer') is-invalid @enderror" required maxlength="5000">{{ old('answer', $faq->answer) }}</textarea>
                @error('answer')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sort order</label>
                    <input type="number" name="sort_order" min="0" max="9999" class="form-control"
                           value="{{ old('sort_order', $faq->sort_order) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="required-field">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" @selected(old('status', $faq->status) == 1)>Active</option>
                        <option value="0" @selected(old('status', $faq->status) == 0)>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>
                    {{ $faq->exists ? 'Update FAQ' : 'Save FAQ' }}
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
