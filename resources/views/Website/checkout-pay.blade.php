@extends('Website.Layout.master')

@section('title', 'Payment — FreshNonVeg')

@section('content')
<section class="py-5">
    <div class="container" style="max-width:560px">
        <div class="fcard text-center">
            <h1 class="h4 mb-2">Payment page khul rahi hai</h1>
            <p class="mb-1">Order <strong>{{ $order->order_number }}</strong></p>
            <p class="mb-3">Amount <strong>₹{{ number_format((float) $order->total, 2) }}</strong></p>
            <p class="small text-muted mb-3" id="cashfreePayStatus">Cashfree secure checkout start ho raha hai…</p>
            <button type="button" class="btn btn-fun btn-red" id="cashfreePayBtn">Pay now</button>
            <p class="small text-danger mt-3 mb-0 d-none" id="cashfreePayError"></p>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script>
(function () {
    var sessionId = @json($paymentSessionId);
    var mode = @json($cashfreeMode);
    var statusEl = document.getElementById('cashfreePayStatus');
    var errorEl = document.getElementById('cashfreePayError');
    var btn = document.getElementById('cashfreePayBtn');

    function showError(message) {
        if (statusEl) statusEl.textContent = 'Payment page nahi khul payi.';
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('d-none');
        }
    }

    function openCheckout() {
        if (typeof Cashfree !== 'function') {
            showError('Cashfree SDK load nahi hua. Page refresh karein.');
            return;
        }
        var cashfree = Cashfree({ mode: mode });
        if (statusEl) statusEl.textContent = 'Cashfree secure checkout start ho raha hai…';
        cashfree.checkout({
            paymentSessionId: sessionId,
            redirectTarget: '_self'
        }).then(function (result) {
            if (result && result.error) {
                showError(result.error.message || 'Cashfree ne checkout block kar diya.');
                return;
            }
            window.setTimeout(function () {
                showError('Payment page nahi khul payi. Page refresh karke Pay now dubara dabayein. Cashfree dashboard me is website ka address whitelist hona chahiye: ' + window.location.origin);
            }, 4000);
        }).catch(function (err) {
            showError((err && err.message) ? err.message : 'Checkout start nahi ho paya.');
        });
    }

    if (btn) btn.addEventListener('click', openCheckout);
    openCheckout();
})();
</script>
@endpush
