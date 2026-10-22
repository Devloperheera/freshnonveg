<div class="adm-actions">
    @isset($view)
        <a href="{{ $view }}" class="adm-act adm-act-view" title="View" @if($viewNewTab ?? false) target="_blank" rel="noopener" @endif>
            <i class="bi bi-eye"></i>
        </a>
    @endisset
    @isset($edit)
        <a href="{{ $edit }}" class="adm-act adm-act-edit" title="Edit">
            <i class="bi bi-pencil-square"></i>
        </a>
    @endisset
    @isset($delete)
        <form method="POST" action="{{ $delete }}" class="m-0" data-confirm="{{ $confirm ?? 'Delete this item?' }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="adm-act adm-act-delete" title="Delete">
                <i class="bi bi-trash3"></i>
            </button>
        </form>
    @endisset
</div>
