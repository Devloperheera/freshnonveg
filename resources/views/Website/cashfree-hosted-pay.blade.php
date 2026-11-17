@extends('Website.Layout.master')

@section('title', 'Pay securely — FreshNonVeg')

@section('content')
<section class="py-4 py-md-5">
    <div class="container" style="max-width:560px">
        <div class="fcard text-center p-3 p-md-4">
            <h1 class="h5 mb-2">Secure payment</h1>
            <p class="mb-1 small">Order <strong>{{ $order->order_number }}</strong></p>
            <p class="mb-3">Amount <strong>₹{{ number_format((float) $order->total, 2) }}</strong></p>
            <p class="small text-muted mb-3" id="cashfreePayStatus">Cashfree payment page khul rahi hai…</p>
            <button type="button" class="btn btn-fun btn-red w-100" id="cashfreePayBtn">Pay now</button>
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
            showError('Cashfree SDK load nahi hua. Internet check karke refresh karein.');
            return;
        }
        var cashfree = Cashfree({ mode: mode });
        if (statusEl) statusEl.textContent = 'Cashfree payment page khul rahi hai…';
        cashfree.checkout({
            paymentSessionId: sessionId,
            redirectTarget: '_self'
        }).then(function (result) {
            if (result && result.error) {
                showError(result.error.message || 'Cashfree checkout start nahi hua.');
                return;
            }
        }).catch(function (err) {
            showError((err && err.message) ? err.message : 'Checkout start nahi ho paya.');
        });
    }

    if (btn) btn.addEventListener('click', openCheckout);
    window.setTimeout(openCheckout, 300);
})();
</script>
@endpush