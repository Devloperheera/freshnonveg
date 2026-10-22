</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.20/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('admin_assets/js/logout.js') }}"></script>
<script src="{{ asset('admin_assets/js/sidebar.js') }}"></script>
<script src="{{ asset('admin_assets/js/searchable-select.js') }}"></script>
<script>
document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form.matches('form[data-confirm]') || form.dataset.confirmed === '1') return;
    e.preventDefault();
    var message = form.dataset.confirm;
    if (typeof Swal === 'undefined') {
        if (confirm(message)) { form.dataset.confirmed = '1'; form.submit(); }
        return;
    }
    Swal.fire({
        title: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        reverseButtons: true
    }).then(function (r) {
        if (r.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); }
    });
});
</script>
