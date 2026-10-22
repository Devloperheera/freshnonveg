@if($advantageItems->isNotEmpty())
<section class="py-5 home-advantages-section"><div class="container text-center">
    @if($advantagesSection)
        <h2 class="t rv">{!! \App\Support\SafeHtml::display($advantagesSection->title) !!}</h2>
        @if($advantagesSection->subtitle)
            <p class="sub rv">{{ $advantagesSection->subtitle }}</p>
        @endif
    @else
        <h2 class="t rv">Fresh खाने के <u>फायदे!</u></h2>
        <p class="sub rv">ताज़ा गोश्त क्यों है बेहतर? 🤔</p>
    @endif
    <div class="owl-carousel owl-theme advantages-carousel rv">
      @foreach($advantageItems as $item)
      <div class="item home-advantage-col">
        <div class="fcard h-100">
          @if($item->icon)
            <div class="ic">{{ $item->icon }}</div>
          @endif
          <h4>{{ $item->title }}</h4>
          @if($item->description)
            <p>{{ $item->description }}</p>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div></section>
@endif
