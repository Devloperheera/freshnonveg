@extends('Website.Layout.master')

@section('title', 'Payment — FreshNonVeg')

@section('content')
<section class="py-5">
    <div class="container" style="max-width:560px">
        <div class="fcard text-center">
            <h1 class="h4 mb-2">Payment page khul rahi hai</h1>
            <p class="mb-1">Order <strong>{{ $order->order_number }}</strong></p>
            <p class="mb-3">Amount <strong>₹{{ number_format((float) $order->total, 2) }}</strong></p>

            @if($errors->any())
                <p class="small text-danger mb-3">{{ $errors->first() }}</p>
            @endif

            <p class="small text-muted mb-3" id="rzpPayStatus">{{ $order->paymentGatewayLabel() }} secure payment page khul rahi hai…</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <button type="button" class="btn btn-fun btn-red" id="rzpPayBtn">Pay now</button>
                <a href="{{ route('checkout.razorpay.cancel', $order) }}" class="btn btn-outline-secondary">Cancel payment</a>
            </div>
            <p class="small text-danger mt-3 mb-0 d-none" id="rzpPayError"></p>
        </div>
    </div>
</section>

<form id="rzpCallbackForm" method="POST" action="{{ route('checkout.razorpay.callback') }}" class="d-none">
    @csrf
    <input type="hidden" name="order" value="{{ $order->id }}">
    <input type="hidden" name="razorpay_payment_id" id="rzpPaymentId">
    <input type="hidden" name="razorpay_order_id" id="rzpOrderId">
    <input type="hidden" name="razorpay_signature" id="rzpSignature">
</form>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    var options = @json($options);
    var statusEl = document.getElementById('rzpPayStatus');
    var errorEl = document.getElementById('rzpPayError');
    var btn = document.getElementById('rzpPayBtn');
    var form = document.getElementById('rzpCallbackForm');
    var submitted = false;

    function showError(message) {
        if (statusEl) statusEl.textContent = 'Payment complete nahi hui.';
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('d-none');
        }
    }

    function submitResult(response) {
        if (submitted) return;
        submitted = true;
        if (statusEl) statusEl.textContent = 'Payment verify ho rahi hai, please wait…';
        if (btn) btn.disabled = true;
        document.getElementById('rzpPaymentId').value = response.razorpay_payment_id || '';
        document.getElementById('rzpOrderId').value = response.razorpay_order_id || '';
        document.getElementById('rzpSignature').value = response.razorpay_signature || '';
        form.submit();
    }

    function openCheckout() {
        if (typeof Razorpay !== 'function') {
            showError('Razorpay SDK load nahi hua. Page refresh karein.');
            return;
        }
        if (errorEl) errorEl.classList.add('d-none');
        if (statusEl) statusEl.textContent = @json($order->paymentGatewayLabel().' secure payment page khul rahi hai…');

        var rzp = new Razorpay(Object.assign({}, options, {
            handler: submitResult,
            modal: {
                ondismiss: function () {
                    if (!submitted) showError('Payment window band ho gayi. "Pay now" dabakar dubara try karein.');
                }
            }
        }));

        rzp.on('payment.failed', function (resp) {
            var msg = (resp && resp.error && resp.error.description) ? resp.error.description : 'Payment fail ho gayi.';
            showError(msg + ' Dubara try karein.');
        });

        rzp.open();
    }

    if (btn) btn.addEventListener('click', openCheckout);
    openCheckout();
})();
</script>
@endpush
