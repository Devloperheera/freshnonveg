(function () {
    'use strict';

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            })[char];
        });
    }

    function formatMessage(message) {
        return escapeHtml(message).replace(/\n/g, '<br>');
    }

    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            preventDuplicates: true,
            onclick: null,
            showDuration: 250,
            hideDuration: 250,
            timeOut: 4500,
            extendedTimeOut: 2000,
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
            escapeHtml: false,
        };
    }

    window.fnvToast = function (message, type) {
        if (!message) {
            return;
        }

        var kind = type || 'success';
        if (kind === 'ok') {
            kind = 'success';
        }
        if (kind === 'danger' || kind === 'fail') {
            kind = 'error';
        }
        if (kind === 'muted') {
            kind = 'info';
        }

        var html = formatMessage(message);

        if (typeof toastr !== 'undefined' && typeof toastr[kind] === 'function') {
            toastr[kind](html);
            return;
        }

        var el = document.getElementById('commerceToast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'commerceToast';
            el.setAttribute('role', 'status');
            document.body.appendChild(el);
        }
        el.textContent = String(message);
        el.className = 'commerce-toast commerce-toast--' + (kind === 'error' ? 'error' : 'success') + ' commerce-toast--show';
        clearTimeout(el._hideTimer);
        el._hideTimer = setTimeout(function () {
            el.classList.remove('commerce-toast--show');
        }, 3200);
    };

    var flash = window.FNV_FLASH || {};
    ['success', 'error', 'warning', 'info'].forEach(function (kind) {
        if (flash[kind]) {
            window.fnvToast(flash[kind], kind);
        }
    });
})();
