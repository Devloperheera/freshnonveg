@extends('Admin.Layout.master')

@section('title', ($pageContent->exists ? 'Edit' : 'Add').' Page Content — FreshNonVeg Admin')

@section('custom_css')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
.form-block { background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(30,142,62,0.1); margin-bottom: 1.5rem; }
.form-block h3 { font-size: 13px; font-weight: 800; text-transform: uppercase; color: #2BAE49; margin-bottom: 14px; border-bottom: 2px solid #F57C00; padding-bottom: 8px; }
#contentEditor { min-height: 280px; background: #fff; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if($errors->any())
        <div class="alert alert-danger py-2 small">
            {{ $errors->first() }}
        </div>
    @endif

    @if(empty($pages))
        <div class="alert alert-warning py-2 small">
            Pehle <a href="{{ route('admin.pages.create') }}">Pages</a> mein kam se kam ek page add karein, phir yahan content bharein.
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-{{ $pageContent->exists ? 'edit' : 'plus-circle' }} me-2"></i>
            {{ $pageContent->exists ? 'Edit Page Content' : 'Add Page Content' }}
        </h2>
        <a href="{{ route('admin.page-contents.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

    <form method="POST" id="pageContentForm"
          action="{{ $pageContent->exists ? route('admin.page-contents.update', $pageContent) : route('admin.page-contents.store') }}">
        @csrf
        @if($pageContent->exists)
            @method('PUT')
        @endif

        <div class="form-block">
            <h3><i class="fas fa-file-alt me-1"></i> Page details</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="page_id" class="form-label">
                        Page Type <span class="required-field">*</span>
                    </label>
                    <select name="page_id" id="page_id" class="form-select select-search @error('page_id') is-invalid @enderror" required {{ empty($pages) ? 'disabled' : '' }}>
                        <option value="">— Select page —</option>
                        @foreach($pages as $id => $name)
                            <option value="{{ $id }}" @selected(old('page_id', $pageContent->page_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('page_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="title" class="form-label">
                        Title <span class="required-field">*</span>
                    </label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title', $pageContent->title) }}" required
                           placeholder="Page heading">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">
                        Status <span class="required-field">*</span>
                    </label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="1" @selected(old('status', $pageContent->status) == 1)>Active</option>
                        <option value="0" @selected(old('status', $pageContent->status) == 0)>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="form-block">
            <h3><i class="fas fa-align-left me-1"></i> Content</h3>
            <div id="contentEditor">{!! old('content', $pageContent->content) !!}</div>
            <input type="hidden" name="content" id="contentInput" value="">
        </div>

        <div class="form-block">
            <h3><i class="fas fa-search me-1"></i> SEO</h3>
            <div class="row g-3">
                <div class="col-12">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" id="meta_title"
                           class="form-control @error('meta_title') is-invalid @enderror" maxlength="255"
                           value="{{ old('meta_title', $pageContent->meta_title) }}" placeholder="SEO page title">
                    @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea name="meta_description" id="meta_description" rows="3" maxlength="500"
                              class="form-control @error('meta_description') is-invalid @enderror"
                              placeholder="Short description for search engines">{{ old('meta_description', $pageContent->meta_description) }}</textarea>
                    @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary" {{ empty($pages) ? 'disabled' : '' }}>
                <i class="fas fa-save me-2"></i>
                {{ $pageContent->exists ? 'Update Content' : 'Save Content' }}
            </button>
            <button type="reset" class="btn btn-warning">
                <i class="fas fa-undo me-2"></i>
                Reset Form
            </button>
        </div>
    </form>
</div>
@endsection

@section('custom_js')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('pageContentForm');
    var quill = new Quill('#contentEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean']
            ]
        }
    });
    var hidden = document.getElementById('contentInput');
    form.addEventListener('submit', function () {
        hidden.value = quill.root.innerHTML;
    });
});
</script>
@endsection
