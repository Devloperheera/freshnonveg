@php
    $publicAssetBase = rtrim(request()->root(), '/');
    $scriptVersion = @filemtime(public_path('website_assets/js/script.js')) ?: time();
    $commerceVersion = @filemtime(public_path('website_assets/js/commerce.js')) ?: time();
    $toastVersion = @filemtime(public_path('website_assets/js/toasts.js')) ?: time();
    $errorBag = $errors ?? new \Illuminate\Support\ViewErrorBag();
    $flashError = session('error') ?: ($errorBag->any() ? $errorBag->first() : null);
@endphp
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    window.FNV_FLASH = {
        success: @json(session('success')),
        error: @json($flashError),
        warning: @json(session('warning')),
        info: @json(session('info') ?: session('status')),
    };
</script>
<script src="{{ $publicAssetBase }}/website_assets/js/toasts.js?v={{ $toastVersion }}"></script>
<script src="{{ $publicAssetBase }}/website_assets/js/script.js?v={{ $scriptVersion }}"></script>
<script src="{{ $publicAssetBase }}/website_assets/js/commerce.js?v={{ $commerceVersion }}"></script>
<script>
    $(function () {
        var $hero = $('#hero');
        if ($hero.length && typeof $.fn.owlCarousel === 'function' && !$hero.hasClass('owl-loaded')) {
            $hero.owlCarousel({
                items: 1,
                loop: true,
                autoplay: true,
                autoplayTimeout: 3500,
                autoplayHoverPause: true,
                nav: false,
                dots: true,
                smartSpeed: 700,
            });
        }

        $('.advantages-carousel').owlCarousel({
            loop: true,
            margin: 12,
            autoplay: true,
            autoplayTimeout: 4500,
            autoplayHoverPause: true,
            nav: false,
            dots: true,
            smartSpeed: 550,
            responsive: {
                0: { items: 1.5, stagePadding: 6 },
                576: { items: 1.5, stagePadding: 10 },
                768: { items: 2, stagePadding: 0 },
                992: { items: 4, stagePadding: 0 },
            },
        });

        $('.testimonial-carousel').owlCarousel({
            loop: true,
            margin: 14,
            autoplay: true,
            autoplayTimeout: 5000,
            nav: false,
            dots: true,
            responsive: {
                0: { items: 1 },
                700: { items: 2 },
                1000: { items: 3 },
            },
        });

        $('.plans-carousel').owlCarousel({
            loop: false,
            margin: 18,
            nav: true,
            dots: true,
            autoplay: false,
            navText: ['‹', '›'],
            responsive: {
                0: { items: 1 },
                576: { items: 1 },
                768: { items: 2 },
                992: { items: 3 },
                1200: { items: 4 },
            },
        });
    });
</script>
