<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CashfreeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashfreeHostedPayController extends Controller
{
    public function __construct(private CashfreeService $cashfree) {}

    public function show(Request $request, string $order_number): View|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Payment link expire ho gayi. App se dubara Pay try karein.');
        }

        $order = Order::query()
            ->where('order_number', $order_number)
            ->firstOrFail();

        if ($order->isPaid()) {
            return redirect()->route('checkout.success', $order);
        }

        if ($order->status !== 'awaiting_payment') {
            abort(404, 'Is order par payment nahi ho sakti.');
        }

        $sessionId = trim((string) $order->cashfree_payment_session_id);
        if ($sessionId === '') {
            abort(404, 'Payment session nahi mili. App/API se GET /checkout/orders/{id}/payment call karein.');
        }

        return view('Website.cashfree-hosted-pay', [
            'order' => $order,
            'paymentSessionId' => $sessionId,
            'cashfreeMode' => $this->cashfree->checkoutMode(),
        ]);
    }
}