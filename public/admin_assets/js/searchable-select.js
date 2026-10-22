/**
 * Admin searchable dropdowns (Select2) — OPT-IN only
 * Sirf .select-search ya data-search="true" wale <select> par lagta hai.
 * Chhote filter dropdowns (status, per_page) normal Bootstrap select rehte hain.
 */
(function ($) {
    'use strict';

    function shouldInit($el) {
        if (!$el.length || !$el.is('select')) return false;
        if ($el.data('no-search') !== undefined) return false;
        if ($el.attr('data-search') === 'false') return false;
        if ($el.hasClass('select2-hidden-accessible')) return false;
        if ($el.attr('data-select2-id')) return false;
        return $el.hasClass('select-search') || $el.attr('data-search') === 'true';
    }

    function select2Options($el) {
        var $modal = $el.closest('.modal');
        var opts = {
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: !$el.prop('required') && !$el.attr('multiple'),
            placeholder: $el.data('placeholder') || $el.find('option[value=""]').text() || 'Select...',
            minimumResultsForSearch: 0,
            dropdownAutoWidth: false,
            matcher: function (params, data) {
                if ($.trim(params.term) === '') return data;
                if (typeof data.text === 'undefined') return null;
                var term = params.term.toLowerCase();
                var text = data.text.toLowerCase();
                if (text.indexOf(term) > -1) return data;
                if (data.id && String(data.id).toLowerCase().indexOf(term) > -1) return data;
                return null;
            }
        };

        if ($modal.length) {
            opts.dropdownParent = $modal;
        }

        return opts;
    }

    window.initSearchableSelects = function (root) {
        var $root = root ? $(root) : $(document);
        $root.find('select.select-search, select[data-search="true"]').addBack('select.select-search, select[data-search="true"]').each(function () {
            var $el = $(this);
            if (!shouldInit($el)) return;
            $el.select2(select2Options($el));
        });
    };

    $(function () {
        initSearchableSelects(document);

        $(document).on('shown.bs.modal', '.modal', function () {
            initSearchableSelects(this);
        });
    });
})(jQuery);
