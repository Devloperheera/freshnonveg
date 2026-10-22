$(document).ready(function () {

    // ══════════════════════════════
    //  SIDEBAR TOGGLE — ONLY HERE
    // ══════════════════════════════
    $('#menuToggle').off('click').on('click', function () {
        if (window.innerWidth <= 768) {
            $('#sidebar').toggleClass('mobile-active');
            $('#overlay').toggleClass('active');
        } else {
            const isCollapsed = $('#sidebar').hasClass('collapsed');

            if (isCollapsed) {
                $('#sidebar').removeClass('collapsed');
                $('#mainContent').removeClass('shifted');
                $('#menuIcon').attr('class', 'bi bi-list');
            } else {
                $('#sidebar').addClass('collapsed');
                $('#mainContent').addClass('shifted');
                $('#menuIcon').attr('class', 'bi bi-layout-sidebar');
                // Close submenus when collapsed
                $('.submenu').removeClass('open').addClass('collapsed');
                $('.menu-arrow').removeClass('rotated');
            }
        }
    });

    // ── Overlay click ──
    $('#overlay').click(function () {
        $('#sidebar').removeClass('mobile-active');
        $('#overlay').removeClass('active');
    });

    // ── Mobile nav item click ──
    $('[data-navigation="true"]').click(function () {
        if (window.innerWidth <= 768) {
            $('#sidebar').removeClass('mobile-active');
            $('#overlay').removeClass('active');
        }
    });

    // ── Submenu toggle ──
    $('[data-toggle="submenu"]').click(function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSubmenu(this);
    });

});

// ── Submenu ──
function toggleSubmenu(element) {
    if ($('#sidebar').hasClass('collapsed')) return;

    const submenu = $(element).next('.submenu');
    const arrow   = $(element).find('.menu-arrow');
    const isOpen  = submenu.hasClass('open');

    $('.submenu').not(submenu).removeClass('open').addClass('collapsed');
    $('.menu-arrow').not(arrow).removeClass('rotated');

    if (isOpen) {
        submenu.removeClass('open').addClass('collapsed');
        arrow.removeClass('rotated');
    } else {
        submenu.removeClass('collapsed').addClass('open');
        arrow.addClass('rotated');
    }
}
