<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Services\CashfreeService;
use App\Services\CheckoutService;
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
                'payment' => 'Online payment (Cashfree) configure nahi hai. Checkout abhi available nahi.',
            ]);
        }

        return view('Website.checkout', [
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
            $session = $this->checkout->startCashfree($order, $user);
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()->away($this->cashfree->checkoutRedirectUrl($order->order_number, $session));
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

        if ($order->payment_method === 'cashfree' && $order->status === 'awaiting_payment') {
            $this->checkout->verifyPaid($order);
            $order->refresh();
        }

        if ($order->payment_method === 'cashfree' && ! in_array($order->status, ['paid', 'confirmed'], true)) {
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
