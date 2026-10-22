@if($orderSteps->isNotEmpty())
<section class="py-5" style="background:rgba(34,193,195,.12)"><div class="container text-center">
    @if($orderWaySection)
        <h2 class="t rv">{!! \App\Support\SafeHtml::display($orderWaySection->title) !!}</h2>
        @if($orderWaySection->subtitle)
            <p class="sub rv">{{ $orderWaySection->subtitle }}</p>
        @endif
    @else
        <h2 class="t rv">Order करना है <u>आसान</u></h2>
        <p class="sub rv">बस 4 steps में ताज़ा गोश्त आपके दरवाज़े पर 🚪</p>
    @endif
    <div class="row g-4">
      @foreach($orderSteps as $index => $step)
      <div class="col-6 col-md-3 rv">
        <div class="num">{{ $index + 1 }}</div>
        <h4>{{ $step->title }}</h4>
        @if($step->description)
            <p class="sub m-0">{{ $step->description }}</p>
        @endif
      </div>
      @endforeach
    </div>
  </div></section>
@endif
