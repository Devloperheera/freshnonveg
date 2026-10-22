@extends('Admin.Layout.master')

@section('title', ($testimonial->exists ? 'Edit' : 'Add').' Testimonial — FreshNonVeg Admin')

@section('custom_css')
<style>
.star-picker { display: flex; gap: 6px; font-size: 28px; cursor: pointer; user-select: none; }
.star-picker span { color: #dee2e6; transition: color .15s; }
.star-picker span.on { color: #f59e0b; }
</style>
@endsection

@section('content')
<div class="container mt-4 mb-4">

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-{{ $testimonial->exists ? 'edit' : 'plus-circle' }} me-2"></i>
            {{ $testimonial->exists ? 'Edit Testimonial' : 'Add Testimonial' }}
        </h2>
        <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

    <div id="form-section" class="form-section">
        <form method="POST"
              action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}">
            @csrf
            @if($testimonial->exists)
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">
                        Name <span class="required-field">*</span>
                    </label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name" value="{{ old('name', $testimonial->name) }}" required
                           placeholder="e.g. Rahul, Noida">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label d-block">
                        Star Rating <span class="required-field">*</span>
                    </label>
                    <input type="hidden" name="star_rating" id="star_rating" value="{{ old('star_rating', $testimonial->star_rating ?? 5) }}">
                    <div class="star-picker" id="starPicker" role="group" aria-label="Star rating">
                        @for($i = 1; $i <= 5; $i++)
                            <span data-value="{{ $i }}" title="{{ $i }} star">★</span>
                        @endfor
                    </div>
                    @error('star_rating')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-3">
                    <label for="short_description" class="form-label">
                        Short Description <span class="required-field">*</span>
                    </label>
                    <textarea name="short_description" id="short_description" rows="4" maxlength="1000"
                              class="form-control @error('short_description') is-invalid @enderror" required
                              placeholder="Customer ka feedback — chhota sa quote">{{ old('short_description', $testimonial->short_description) }}</textarea>
                    @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">
                        Status <span class="required-field">*</span>
                    </label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="1" @selected(old('status', $testimonial->status) == 1)>Active</option>
                        <option value="0" @selected(old('status', $testimonial->status) == 0)>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>
                    {{ $testimonial->exists ? 'Update Testimonial' : 'Save Testimonial' }}
                </button>
                <button type="reset" class="btn btn-warning" id="resetBtn">
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
document.addEventListener('DOMContentLoaded', function () {
    var hidden = document.getElementById('star_rating');
    var picker = document.getElementById('starPicker');
    var stars = picker.querySelectorAll('span[data-value]');

    function paint(val) {
        val = parseInt(val, 10) || 5;
        stars.forEach(function (s) {
            s.classList.toggle('on', parseInt(s.getAttribute('data-value'), 10) <= val);
        });
        hidden.value = val;
    }

    paint(hidden.value);

    stars.forEach(function (s) {
        s.addEventListener('click', function () {
            paint(s.getAttribute('data-value'));
        });
    });

    document.getElementById('resetBtn')?.addEventListener('click', function () {
        setTimeout(function () { paint(5); }, 0);
    });
});
</script>
@endsection
