@extends('Admin.Layout.master')

@section('title', ($product->exists ? 'Edit' : 'Add').' Product — FreshNonVeg Admin')

@section('custom_css')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
.form-block { background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(30,142,62,0.1); margin-bottom: 1.5rem; }
.form-block h3 { font-size: 13px; font-weight: 800; text-transform: uppercase; color: #2BAE49; margin-bottom: 14px; border-bottom: 2px solid #F57C00; padding-bottom: 8px; }
#descriptionEditor { min-height: 220px; background: #fff; }
.weight-row .btn-remove-weight { padding: 0.25rem 0.5rem; }
#imageSortList { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 12px; }
#imageSortList li {
    width: 120px; border: 2px solid #dee2e6; border-radius: 10px; padding: 8px; background: #fff;
    cursor: grab; position: relative; text-align: center;
}
#imageSortList li.sortable-ghost { opacity: 0.5; border-color: #2BAE49; }
#imageSortList img { width: 100%; height: 90px; object-fit: cover; border-radius: 6px; }
#imageSortList .img-handle { font-size: 11px; color: #64748b; margin-top: 4px; }
#imageSortList .btn-remove-img { position: absolute; top: 4px; right: 4px; padding: 2px 6px; line-height: 1; }
</style>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-4">

    @if($errors->any())
        <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h2 class="section-title mb-0 border-0 pb-0">
            <i class="fas fa-box me-2"></i> {{ $product->exists ? 'Edit Product' : 'Add Product' }}
        </h2>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to list
        </a>
    </div>

    <form method="POST"
          action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
          id="productForm" enctype="multipart/form-data">
        @csrf
        @if($product->exists)
            @method('PUT')
        @endif

        <div class="form-block">
            <h3><i class="fas fa-info-circle me-1"></i> Basic details</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product Name <span class="required-field">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $product->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Select Category</label>
                    <select name="category_id" class="form-select select-search @error('category_id') is-invalid @enderror">
                        <option value="">— None —</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}" @selected(old('category_id', $product->category_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Select Banner</label>
                    <select name="banner_id" class="form-select select-search @error('banner_id') is-invalid @enderror">
                        <option value="">— None —</option>
                        @foreach($banners as $id => $name)
                            <option value="{{ $id }}" @selected(old('banner_id', $product->banner_id) == $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('banner_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tags — select ya naya likhein</label>
                    @php
                        $selectedTagValues = old('tag_ids');
                        if ($selectedTagValues === null && $product->exists) {
                            $selectedTagValues = $product->tags->map(fn ($t) => (string) $t->id)->all();
                        }
                        $selectedTagValues = $selectedTagValues ?? [];
                        $existingNames = $tags->pluck('name')->map(fn ($n) => strtolower($n))->all();
                    @endphp
                    <select name="tag_ids[]" id="productTagsSelect" class="form-select" multiple
                            data-placeholder="Tag select karein ya naya type karke Enter dabayein">
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}"
                                @selected(in_array((string) $tag->id, $selectedTagValues, true) || in_array($tag->id, $selectedTagValues, true))>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                        @foreach($selectedTagValues as $val)
                            @if($val !== null && $val !== '' && ! ctype_digit((string) $val) && ! in_array(strtolower((string) $val), $existingNames, true))
                                <option value="{{ $val }}" selected>{{ $val }}</option>
                            @endif
                        @endforeach
                    </select>
                    <div class="form-text">List se choose karein, ya naya tag likh kar <strong>Enter</strong> / comma se add karein.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status <span class="required-field">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="1" @selected(old('status', $product->status) == 1)>Active</option>
                        <option value="0" @selected(old('status', $product->status) == 0)>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end pb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured"
                               @checked(old('is_featured', $product->is_featured))>
                        <label class="form-check-label fw-semibold" for="is_featured">Featured Product</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-block">
            <h3><i class="fas fa-images me-1"></i> Product Images</h3>
            <p class="small text-muted">Multiple images upload karein. <strong>Drag &amp; drop</strong> se order change karein.</p>
            <ul id="imageSortList">
                @if($product->exists)
                    @foreach($product->images as $img)
                        <li data-key="e-{{ $img->id }}">
                            <button type="button" class="btn btn-danger btn-sm btn-remove-img" title="Remove">&times;</button>
                            <img src="{{ $img->url() }}" alt="">
                            <div class="img-handle"><i class="fas fa-grip-vertical"></i> Drag</div>
                        </li>
                    @endforeach
                @endif
            </ul>
            <div id="imageSortHidden"></div>
            <input type="file" id="productImagesInput" class="form-control mt-3" accept="image/*" multiple>
            @error('new_images.*')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="form-block">
            <h3><i class="fas fa-video me-1"></i> Product Video</h3>
            @if($product->exists && $product->video_path)
                <div class="mb-2">
                    <video src="{{ $product->videoUrl() }}" controls style="max-width: 320px; max-height: 180px;" class="rounded border"></video>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="remove_video" value="1" id="remove_video">
                    <label class="form-check-label" for="remove_video">Remove current video</label>
                </div>
            @endif
            <label class="form-label">Upload video (optional)</label>
            <input type="file" name="video" class="form-control" accept="video/mp4,video/webm,video/quicktime">
            <div class="form-text">MP4 / WebM / MOV · max ~50 MB</div>
            @error('video')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="form-block">
            <h3><i class="fas fa-weight-hanging me-1"></i> Weight &amp; Price</h3>
            <p class="small text-muted mb-2">Har weight ke liye alag price — jitne chahiye utne rows add karein.</p>
            <div id="weightRows">
                @php
                    $rows = old('weights', $weightRows);
                @endphp
                @foreach($rows as $i => $row)
                    <div class="row g-2 align-items-end weight-row mb-2">
                        <div class="col-md-5">
                            <label class="form-label small">Weight</label>
                            <input type="text" name="weights[{{ $i }}][weight_label]" class="form-control"
                                   value="{{ $row['weight_label'] ?? '' }}" placeholder="e.g. 500g, 1 Kg" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Price (₹)</label>
                            <input type="number" name="weights[{{ $i }}][price]" class="form-control" step="0.01" min="0"
                                   value="{{ $row['price'] ?? '' }}" placeholder="600" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-weight w-100" @if($loop->first && count($rows) === 1) disabled @endif>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            @error('weights')<div class="text-danger small">{{ $message }}</div>@enderror
            @error('weights.*.weight_label')<div class="text-danger small">{{ $message }}</div>@enderror
            @error('weights.*.price')<div class="text-danger small">{{ $message }}</div>@enderror
            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addWeightRow">
                <i class="fas fa-plus me-1"></i> Add weight option
            </button>
        </div>

        <div class="form-block">
            <h3><i class="fas fa-align-left me-1"></i> Description</h3>
            <div id="descriptionEditor">{!! old('description', $product->description) !!}</div>
            <input type="hidden" name="description" id="descriptionInput" value="{{ old('description', $product->description) }}">
        </div>

        <div class="form-block">
            <h3><i class="fas fa-search me-1"></i> SEO</h3>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" maxlength="255"
                           value="{{ old('meta_title', $product->meta_title) }}" placeholder="SEO page title">
                </div>
                <div class="col-12">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2" maxlength="500"
                              placeholder="Short description for search engines">{{ old('meta_description', $product->meta_description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Meta Keywords</label>
                    <input type="text" name="meta_keywords" class="form-control" maxlength="255"
                           value="{{ old('meta_keywords', $product->meta_keywords) }}" placeholder="fresh chicken, mutton, noida">
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> {{ $product->exists ? 'Update Product' : 'Save Product' }}
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var imageList = document.getElementById('imageSortList');
    var imageInput = document.getElementById('productImagesInput');
    var sortHidden = document.getElementById('imageSortHidden');
    var pendingFiles = [];
    var nextNewIndex = 0;

    function syncSortHiddenInputs() {
        sortHidden.innerHTML = '';
        imageList.querySelectorAll('li[data-key]').forEach(function (li) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'image_sort[]';
            input.value = li.getAttribute('data-key');
            sortHidden.appendChild(input);
        });
    }

    if (imageList && window.Sortable) {
        new Sortable(imageList, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onSort: syncSortHiddenInputs
        });
    }
    syncSortHiddenInputs();

    imageList.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-remove-img');
        if (!btn) return;
        var li = btn.closest('li');
        var key = li.getAttribute('data-key');
        if (key && key.indexOf('n-') === 0) {
            var idx = parseInt(key.split('-')[1], 10);
            pendingFiles[idx] = null;
        }
        li.remove();
        syncSortHiddenInputs();
    });

    imageInput.addEventListener('change', function () {
        Array.from(imageInput.files || []).forEach(function (file) {
            var idx = nextNewIndex++;
            pendingFiles[idx] = file;
            var key = 'n-' + idx;
            var li = document.createElement('li');
            li.setAttribute('data-key', key);
            li.innerHTML = '<button type="button" class="btn btn-danger btn-sm btn-remove-img" title="Remove">&times;</button>' +
                '<img src="' + URL.createObjectURL(file) + '" alt="">' +
                '<div class="img-handle"><i class="fas fa-grip-vertical"></i> New</div>';
            imageList.appendChild(li);
        });
        imageInput.value = '';
        syncSortHiddenInputs();
    });

    var form = document.getElementById('productForm');
    var quill = new Quill('#descriptionEditor', {
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
    var hidden = document.getElementById('descriptionInput');

    form.addEventListener('submit', function (e) {
        hidden.value = quill.root.innerHTML;
        syncSortHiddenInputs();
        if (pendingFiles.some(function (f) { return f !== null; })) {
            e.preventDefault();
            var fd = new FormData(form);
            fd.delete('new_images[]');
            pendingFiles.forEach(function (file, idx) {
                if (file) fd.append('new_images[' + idx + ']', file);
            });
            var token = document.querySelector('input[name="_token"]');
            if (token) fd.set('_token', token.value);
            if (form.querySelector('input[name="_method"]')) {
                fd.set('_method', 'PUT');
            }
            fetch(form.action, { method: 'POST', body: fd, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) {
                    if (res.redirected) {
                        window.location.href = res.url;
                        return;
                    }
                    if (res.ok) {
                        window.location.href = '{{ route('admin.products.index') }}';
                        return;
                    }
                    return res.text().then(function (t) { throw new Error(t); });
                })
                .catch(function () {
                    form.submit();
                });
        }
    });

    if (window.jQuery && jQuery.fn.select2) {
        jQuery('#productTagsSelect').select2({
            theme: 'bootstrap-5',
            width: '100%',
            tags: true,
            tokenSeparators: [','],
            placeholder: jQuery('#productTagsSelect').data('placeholder') || 'Tags...',
            allowClear: true
        });
    }

    var weightContainer = document.getElementById('weightRows');
    var weightIndex = weightContainer.querySelectorAll('.weight-row').length;

    document.getElementById('addWeightRow').addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'row g-2 align-items-end weight-row mb-2';
        row.innerHTML =
            '<div class="col-md-5"><label class="form-label small">Weight</label>' +
            '<input type="text" name="weights[' + weightIndex + '][weight_label]" class="form-control" placeholder="e.g. 1 Kg" required></div>' +
            '<div class="col-md-5"><label class="form-label small">Price (₹)</label>' +
            '<input type="number" name="weights[' + weightIndex + '][price]" class="form-control" step="0.01" min="0" required></div>' +
            '<div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm btn-remove-weight w-100"><i class="fas fa-times"></i></button></div>';
        weightContainer.appendChild(row);
        weightIndex++;
        updateRemoveButtons();
    });

    weightContainer.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-remove-weight');
        if (!btn || btn.disabled) return;
        btn.closest('.weight-row').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        var rows = weightContainer.querySelectorAll('.weight-row');
        rows.forEach(function (row, idx) {
            var rm = row.querySelector('.btn-remove-weight');
            if (rm) rm.disabled = rows.length <= 1;
        });
    }
});
</script>
@endsection
