@php
    $catBackgrounds = ['#fde2e2', '#fff3c4', '#d6f5e3', '#dbeafe', '#fbd5e8', '#e9d5ff', '#ffedd5', '#ecfccb'];
@endphp
<section class="py-5" style="background:rgba(255,217,61,.18)">
    <div class="container text-center">
        <h2 class="t rv">Shop by <u>Category</u></h2>
        <p class="sub rv">केटेगरी चुनें 👇</p>
        <div class="row g-2 g-md-4 justify-content-center home-categories-row">
            @forelse($categories ?? [] as $category)
                <div class="col-4 col-md-4 col-lg-3 rv">
                    <a class="cat cat-with-img"
                       style="background:{{ $catBackgrounds[$loop->index % count($catBackgrounds)] }}"
                       href="{{ route('shop.index', ['category' => $category->id]) }}">
                        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" width="80" height="80" loading="lazy">
                        <span class="cat-name">{{ $category->name }}</span>
                    </a>
                </div>
            @empty
                <div class="col-12 rv">
                    <p class="text-muted mb-0">Abhi koi category available nahi hai.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
