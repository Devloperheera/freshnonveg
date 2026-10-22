@if($siteStats->isNotEmpty())
<section class="stats py-5"><div class="container"><div class="row g-4 text-center">
    @foreach($siteStats as $stat)
    <div class="col-6 col-md-3">
        <h3 data-n="{{ $stat->value }}">0</h3>
        <p class="m-0 fw-semibold">{{ $stat->label }}</p>
    </div>
    @endforeach
  </div></div></section>
@endif
