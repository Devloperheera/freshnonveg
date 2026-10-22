@if(isset($testimonials) && $testimonials->isNotEmpty())
<section class="py-5" style="background:rgba(236,72,153,.1)">
    <div class="container text-center">
        <h2 class="t rv">Customers <u>क्या कहते हैं</u> 💬</h2>
        <p class="sub rv">असली लोग, असली taste</p>
        <div class="owl-carousel owl-theme testimonial-carousel rv">
            @foreach($testimonials as $testimonial)
                <div class="item px-1">
                    <div class="fcard h-100 text-start">
                        <div class="stars" aria-label="{{ $testimonial->star_rating }} out of 5">{!! $testimonial->starsHtml() !!}</div>
                        <p class="text-dark mb-3">"{{ $testimonial->short_description }}"</p>
                        <h4 class="mb-0">– {{ $testimonial->name }}</h4>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
