@if(isset($faqs) && $faqs->isNotEmpty())
<section class="py-5" id="faq">
    <div class="container" style="max-width:800px">
        <h2 class="t text-center rv">अक्सर पूछे गए <u>सवाल</u> ❓</h2>
        <p class="sub text-center rv">FAQ</p>
        <div class="accordion rv" id="acc">
            @foreach($faqs as $faq)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq-{{ $faq->id }}"
                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                aria-controls="faq-{{ $faq->id }}">
                            {{ $faq->question }}
                        </button>
                    </h2>
                    <div id="faq-{{ $faq->id }}"
                         class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                         data-bs-parent="#acc">
                        <div class="accordion-body">{{ $faq->answer }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
