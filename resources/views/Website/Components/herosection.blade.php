<!-- HERO: Owl Carousel — admin banners (image only) -->
<section class="hero">
    @if(isset($banners) && $banners->isNotEmpty())
        <div class="owl-carousel owl-theme" id="hero">
            @foreach($banners as $banner)
                <div class="hero-slide">
                    <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->name }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                </div>
            @endforeach
        </div>
    @endif
</section>
