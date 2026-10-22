<?php

namespace App\Http\Controllers\Api\Checkout;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CheckoutService;
use App\Support\CheckoutApi;
use App\Support\UserDashboardApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkout) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $paid = $this->checkout->syncPendingPaid($user);
        if ($paid) {
            return response()->json([
                'success' => true,
                'message' => 'Order place ho gaya! / Your order has been placed.',
                'already_paid' => true,
                'data' => [
                    'order' => UserDashboardApi::orderDetail($paid, $user),
                    'invoice' => CheckoutApi::invoice($paid),
                ],
            ]);
        }

        $quote = $this->checkout->quote($user, [
            'delivery_schedule_type' => $request->query('delivery_schedule_type', 'scheduled'),
            'coupon_code' => $request->query('coupon_code'),
            'use_wallet' => $request->boolean('use_wallet'),
        ]);

        if ($quote['is_empty']) {
            return response()->json([
                'success' => false,
                'message' => 'Pehle cart mein items add karein.',
            ], 422);
        }

        if (! $quote['payment_configured']) {
            return response()->json([
                'success' => false,
                'message' => 'Online payment (Cashfree) configure nahi hai. Checkout abhi available nahi.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => CheckoutApi::customer($user),
                ...CheckoutApi::quote($quote),
            ],
        ]);
    }

    public function quote(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'delivery_schedule_type' => ['nullable', 'in:instant,scheduled'],
            'coupon_code' => ['nullable', 'string', 'max:40'],
            'use_wallet' => ['nullable', 'boolean'],
        ]);

        $quote = $this->checkout->quote($user, $data);

        return response()->json([
            'success' => true,
            'data' => CheckoutApi::quote($quote),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate($this->checkout->placeRules());

        $order = $this->checkout->placeOrder($user, $data);
        $session = $this->checkout->startCashfree($order, $user);
        $order->load(['items.product.images', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Payment start ho gaya. Cashfree se pay karein.',
            'data' => [
                'order' => UserDashboardApi::orderDetail($order, $user),
                'payment' => $this->checkout->paymentPayload($order, $session),
            ],
        ], 201);
    }
}
