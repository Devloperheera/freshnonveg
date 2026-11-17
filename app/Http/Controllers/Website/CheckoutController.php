<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\CashfreeService;
use App\Services\CheckoutService;
use App\Services\RazorpayService;
use App\Services\OrderBillNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function __construct(
        private CashfreeService $cashfree,
        private RazorpayService $razorpay,
        private CheckoutService $checkout,
    ) {}

    public function show(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($paid = $this->checkout->syncPendingPaid($user)) {
            return redirect()
                ->route('checkout.success', $paid)
                ->with('success', 'Order place ho gaya! / Your order has been placed.');
        }

        $quote = $this->checkout->quote($user);

        if ($quote['is_empty']) {
            return redirect()->route('cart')->with('success', 'Pehle cart mein items add karein.');
        }

        if (! $quote['payment_configured']) {
            return redirect()->route('cart')->withErrors([
                'payment' => 'Online payment configure nahi hai. Checkout abhi available nahi.',
            ]);
        }

        return view('Website.checkout', [
            'paymentGateway' => $quote['payment_gateway'],
            'paymentGatewayLabel' => $quote['payment_gateway_label'],
            'items' => $quote['items'],
            'subtotal' => $quote['subtotal'],
            'tax' => $quote['tax'],
            'deliveryFee' => $quote['delivery_fee'],
            'instantDeliveryFee' => $quote['instant_delivery_fee'],
            'total' => $quote['subtotal'] + $quote['tax'] + $quote['delivery_fee'],
            'user' => $user,
            'timeSlots' => $quote['time_slots'],
            'walletBalance' => $quote['wallet_balance'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validate($this->checkout->placeRules());

        try {
            $order = $this->checkout->placeOrder($user, $data);
            $session = $this->checkout->startPayment($order, $user);
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        if ($order->payment_method === PaymentGateway::RAZORPAY) {
            $request->session()->put('razorpay_checkout', ['order_id' => $order->id]);

            return redirect()->route('checkout.pay');
        }

        $request->session()->forget('razorpay_checkout');
        $paymentSessionId = (string) ($session['payment_session_id'] ?? '');
        if ($paymentSessionId === '') {
            return redirect()->away($this->cashfree->checkoutRedirectUrl($order->order_number, $session));
        }

        $request->session()->put('cashfree_checkout', [
            'order_id' => $order->id,
            'payment_session_id' => $paymentSessionId,
        ]);

        return redirect()->route('checkout.pay');
    }

    public function pay(Request $request): View|RedirectResponse
    {
        $razorpay = $request->session()->get('razorpay_checkout');
        if (is_array($razorpay) && ! empty($razorpay['order_id'])) {
            $order = Order::query()
                ->whereKey($razorpay['order_id'])
                ->where('user_id', auth()->id())
                ->where('payment_method', PaymentGateway::RAZORPAY)
                ->first();

            if ($order && $order->isPaid()) {
                $request->session()->forget('razorpay_checkout');

                return redirect()->route('checkout.success', $order);
            }

            if (! $order || $order->status !== 'awaiting_payment' || ! $order->razorpay_order_id) {
                $request->session()->forget('razorpay_checkout');

                return redirect()->route('checkout')->withErrors([
                    'payment' => 'Payment session expire ho gayi. Dubara Pay dabayein.',
                ]);
            }

            return view('Website.checkout-pay-razorpay', [
                'order' => $order,
                'options' => $this->razorpay->checkoutOptions($order, auth()->user()),
            ]);
        }

        $payload = $request->session()->get('cashfree_checkout');
        if (! is_array($payload) || empty($payload['payment_session_id']) || empty($payload['order_id'])) {
            return redirect()->route('checkout')->withErrors([
                'payment' => 'Payment session expire ho gayi. Dubara Pay dabayein.',
            ]);
        }

        $order = Order::query()
            ->whereKey($payload['order_id'])
            ->where('user_id', auth()->id())
            ->first();

        if (! $order) {
            $request->session()->forget('cashfree_checkout');

            return redirect()->route('checkout')->withErrors([
                'payment' => 'Order nahi mila. Dubara checkout karein.',
            ]);
        }

        return view('Website.checkout-pay', [
            'order' => $order,
            'paymentSessionId' => (string) $payload['payment_session_id'],
            'cashfreeMode' => $this->cashfree->checkoutMode(),
        ]);
    }

    public function cashfreeReturn(Request $request): RedirectResponse
    {
        $merchantOrderId = (string) ($request->query('order_id') ?? $request->query('orderId') ?? '');

        if ($merchantOrderId === '') {
            return redirect()->route('cart')->withErrors(['payment' => 'Invalid payment return.']);
        }

        $order = Order::query()
            ->where('order_number', $merchantOrderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($this->checkout->verifyPaid($order)) {
            return redirect()
                ->route('checkout.success', $order)
                ->with('success', 'Order place ho gaya! / Your order has been placed.');
        }

        $this->checkout->markFailedIfUnpaid($order);

        return redirect()
            ->route('checkout')
            ->withErrors(['payment' => 'Payment complete nahi hui. Dubara checkout try karein.']);
    }

    /**
     * Razorpay checkout.js handler se yaha POST aata hai.
     */
    public function razorpayCallback(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order' => ['required', 'integer'],
            'razorpay_payment_id' => ['nullable', 'string', 'max:80'],
            'razorpay_order_id' => ['nullable', 'string', 'max:80'],
            'razorpay_signature' => ['nullable', 'string', 'max:200'],
        ]);

        $order = Order::query()
            ->whereKey($data['order'])
            ->where('user_id', auth()->id())
            ->where('payment_method', PaymentGateway::RAZORPAY)
            ->firstOrFail();

        $signatureOk = $this->razorpay->signatureIsValid(
            (string) $order->razorpay_order_id,
            (string) ($data['razorpay_payment_id'] ?? ''),
            (string) ($data['razorpay_signature'] ?? '')
        );

        if (! $signatureOk) {
            Log::warning('Razorpay signature mismatch', ['order' => $order->order_number]);
        }

        // Signature ho ya na ho, final faisla Razorpay API se status dekh kar.
        if ($this->checkout->verifyPaid($order)) {
            $request->session()->forget('razorpay_checkout');

            return redirect()
                ->route('checkout.success', $order)
                ->with('success', 'Order place ho gaya! / Your order has been placed.');
        }

        return redirect()
            ->route('checkout.pay')
            ->withErrors(['payment' => 'Payment confirm nahi hui. Dubara try karein.']);
    }

    /**
     * Customer ne Razorpay payment cancel kiya.
     */
    public function razorpayCancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 404);

        $request->session()->forget('razorpay_checkout');

        if ($this->checkout->verifyPaid($order)) {
            return redirect()
                ->route('checkout.success', $order)
                ->with('success', 'Order place ho gaya! / Your order has been placed.');
        }

        $this->checkout->markFailedIfUnpaid($order);

        return redirect()
            ->route('checkout')
            ->withErrors(['payment' => 'Payment cancel ho gayi. Cart dobara bhar ke try karein.']);
    }

    public function razorpayWebhook(Request $request): \Illuminate\Http\Response
    {
        if (! $this->razorpay->webhookIsAuthentic($request)) {
            return response('unauthorized', 401);
        }

        $razorpayOrderId = (string) (
            $request->input('payload.payment.entity.order_id')
            ?? $request->input('payload.order.entity.id')
            ?? ''
        );

        if ($razorpayOrderId === '') {
            return response('ignored', 200);
        }

        $order = Order::query()->where('razorpay_order_id', $razorpayOrderId)->first();
        if (! $order) {
            return response('unknown order', 200);
        }

        try {
            $this->razorpay->syncLocalOrderPayment($order);
        } catch (RuntimeException $e) {
            Log::warning('Razorpay webhook sync failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);
        }

        return response('ok', 200);
    }

    public function cashfreeWebhook(Request $request): \Illuminate\Http\Response
    {
        if (! $this->cashfree->webhookIsAuthentic($request)) {
            return response('unauthorized', 401);
        }

        $payload = $request->all();
        $merchantOrderId = (string) ($payload['order_id'] ?? $payload['data']['order']['order_id'] ?? '');

        if ($merchantOrderId === '') {
            return response('ignored', 200);
        }

        $order = Order::query()->where('order_number', $merchantOrderId)->first();

        if (! $order) {
            return response('unknown order', 200);
        }

        try {
            $this->cashfree->syncLocalOrderPayment($order);
        } catch (RuntimeException $e) {
            Log::warning('Cashfree webhook sync failed', ['order' => $merchantOrderId, 'error' => $e->getMessage()]);
        }

        return response('ok', 200);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40'],
        ]);

        $subtotal = (float) $request->user()->cartItems()->get()->sum(fn (CartItem $item) => $item->lineTotal());
        if ($subtotal <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cart khali hai.',
            ], 422);
        }

        try {
            [$coupon, $discount] = $this->checkout->resolveCoupon($data['code'], $subtotal);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Coupon valid nahi hai.',
            ], 422);
        }

        if (! $coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon code daalein.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'code' => $coupon->code,
            'discount_type' => $coupon->discount_type,
            'discount_value' => (float) $coupon->discount_value,
            'max_discount' => $coupon->max_discount !== null ? (float) $coupon->max_discount : null,
            'discount' => $discount,
            'message' => $coupon->code.' lag gaya. '.$coupon->valueLabel(),
        ]);
    }

    public function success(Order $order): View|RedirectResponse
    {
        if ((int) $order->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $online = CheckoutService::isOnlineGateway($order->payment_method);

        if ($online && $order->status === 'awaiting_payment') {
            $this->checkout->verifyPaid($order);
            $order->refresh();
        }

        if ($online && ! in_array($order->status, ['paid', 'confirmed'], true)) {
            return redirect()
                ->route('checkout')
                ->withErrors(['payment' => 'Payment abhi pending hai.']);
        }

        $order->load(['items', 'user']);
        app(OrderBillNotifier::class)->sendIfPaid($order);
        $order->refresh();

        if (! session()->has('success')) {
            session()->now('success', 'Order place ho gaya! / Your order has been placed.');
        }

        return view('Website.checkout-success', compact('order'));
    }
}
