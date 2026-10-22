<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.20/sweetalert2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet"/>


<link rel="stylesheet" href="{{ asset('admin_assets/css/style.css') }}">
<link rel="stylesheet" href="{{ asset('admin_assets/css/forms.css') }}">
<style>
:root{
  --bs-primary:#2BAE49;
  --bs-primary-rgb:43,174,73;
  --bs-link-color:#2BAE49;
  --bs-link-hover-color:#F57C00;
}
.btn-primary{--bs-btn-bg:#2BAE49;--bs-btn-border-color:#2BAE49;--bs-btn-hover-bg:#1E8E3E;--bs-btn-hover-border-color:#1E8E3E;--bs-btn-active-bg:#155724;--bs-btn-active-border-color:#155724}
.btn-outline-primary{--bs-btn-color:#2BAE49;--bs-btn-border-color:#2BAE49;--bs-btn-hover-bg:#2BAE49;--bs-btn-hover-border-color:#2BAE49;--bs-btn-active-bg:#155724;--bs-btn-active-border-color:#155724}
.btn-warning,.btn-secondary-theme{background:#F57C00!important;border-color:#F57C00!important;color:#fff!important}
.btn-warning:hover{background:#D32F2F!important;border-color:#D32F2F!important;color:#fff!important}
.bg-primary{background-color:#2BAE49!important}
.text-primary{color:#2BAE49!important}
.badge.bg-primary{background-color:#2BAE49!important}
.table-dark,.table thead.table-dark th{background-color:#1E8E3E!important}
.form-check-input:checked{background-color:#2BAE49;border-color:#2BAE49}
.form-control:focus,.form-select:focus{border-color:#F57C00;box-shadow:0 0 0 .2rem rgba(43,174,73,.2)}
.page-item.active .page-link{background-color:#2BAE49;border-color:#2BAE49}
.page-link{color:#2BAE49}
.page-link:hover{color:#F57C00}
.alert-success{border-left:4px solid #2BAE49}
a{color:#ffffff}
a:hover{color:#F57C00}

/* Compact admin tables */
.table{font-size:12px;color:#334155}
.table > :not(caption) > * > *{padding:.35rem .5rem;vertical-align:middle}
.table thead th,.table thead td{font-size:11px;font-weight:700;white-space:nowrap;letter-spacing:.02em}
.table tbody td{font-size:12px;line-height:1.35}
.table tbody td .small,.table tbody td small{font-size:10.5px!important}
.table .badge{font-size:10px;padding:.2em .45em;font-weight:600}
.table .btn-sm{font-size:11px;padding:.15rem .4rem;line-height:1.3}
.table img,.table .emp-avatar,.table .avatar-img{width:32px!important;height:32px!important;object-fit:cover}
.table code{font-size:10.5px}
.table .bi-arrow-down-up{font-size:9px!important}

/* Select2 — sirf .select-search par (opt-in) */
.select2-container{width:100%!important;font-size:12.5px;max-width:100%}
.select2-container--bootstrap-5 .select2-selection{min-height:34px;border-color:#c8e6c9;border-radius:.375rem}
.select2-container--bootstrap-5.select2-container--focus .select2-selection,
.select2-container--bootstrap-5.select2-container--open .select2-selection{border-color:#F57C00;box-shadow:0 0 0 .2rem rgba(43,174,73,.2)}
.select2-container--bootstrap-5 .select2-dropdown{border-color:#c8e6c9;font-size:12.5px;z-index:2055!important;padding:4px;border-radius:.375rem}
.select2-container--bootstrap-5 .select2-search--dropdown{padding:4px 6px}
.select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field{
    border-color:#c8e6c9;font-size:12.5px;padding:.25rem .5rem;min-height:30px;height:30px;line-height:1.2;box-shadow:none;border-radius:.25rem
}
.select2-container--bootstrap-5 .select2-results__options{max-height:220px}
.select2-container--bootstrap-5 .select2-results__option{padding:.35rem .6rem}
.select2-container--bootstrap-5 .select2-results__option--selected,
.select2-container--bootstrap-5 .select2-results__option--highlighted{background-color:#2BAE49!important;color:#fff!important}
.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered{line-height:32px;padding-left:.6rem;color:#334155}
.select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow{height:32px}
.form-select-sm.select-search + .select2-container .select2-selection,
.select2-container--bootstrap-5.select2-container--bootstrap-5-sm .select2-selection{min-height:30px;font-size:12px}
.form-select-sm.select-search + .select2-container .select2-selection--single .select2-selection__rendered{line-height:28px}
.form-select-sm.select-search + .select2-container .select2-selection--single .select2-selection__arrow{height:28px}
.modal .select2-container--bootstrap-5 .select2-dropdown{z-index:2060!important}
.table td .select2-container{min-width:140px}
.adm-switch{padding-left:2.5em;min-height:0}
.adm-switch .form-check-input{width:2.4em;height:1.25em;margin-left:-2.5em;cursor:pointer;border-color:#cbd5e1;box-shadow:none}
.adm-switch .form-check-input:checked{background-color:#1a73e8;border-color:#1a73e8}
.adm-switch .form-check-input:focus{box-shadow:0 0 0 .18rem rgba(26,115,232,.2)}
.adm-actions{display:inline-flex;align-items:center;gap:6px}
.adm-act{width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;background:#fff;border:1px solid;font-size:14px;line-height:1;padding:0;cursor:pointer;text-decoration:none;transition:background .15s,color .15s}
.adm-act-edit{color:#1a73e8;border-color:#1a73e8}
.adm-act-edit:hover{background:#1a73e8;color:#fff}
.adm-act-delete{color:#dc3545;border-color:#dc3545}
.adm-act-delete:hover{background:#dc3545;color:#fff}
.adm-act-view{color:#0d9488;border-color:#0d9488}
.adm-act-view:hover{background:#0d9488;color:#fff}
</style>

