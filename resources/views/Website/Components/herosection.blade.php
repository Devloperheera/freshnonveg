<!-- HERO: Owl Carousel — admin banners. Banner par products hon to click → products -->
<section class="hero">
    @if(isset($banners) && $banners->isNotEmpty())
        <div class="owl-carousel owl-theme" id="hero">
            @foreach($banners as $banner)
                @php $bannerUrl = $banner->linkUrl(); @endphp
                <div class="hero-slide">
                    @if($bannerUrl)
                        <a href="{{ $bannerUrl }}" class="hero-slide-link" aria-label="{{ $banner->name }} — products dekhein">
                            <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->name }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                        </a>
                    @else
                        <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->name }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>
