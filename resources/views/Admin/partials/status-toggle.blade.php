<form method="POST" action="{{ $action }}" class="adm-switch-form m-0">
    @csrf
    @method('PATCH')
    <div class="form-check form-switch adm-switch m-0" title="{{ $active ? 'Active — click to deactivate' : 'Inactive — click to activate' }}">
        <input class="form-check-input" type="checkbox" role="switch" onchange="this.form.submit()" @checked($active)
               aria-label="{{ $active ? 'Active' : 'Inactive' }}">
    </div>
</form>
