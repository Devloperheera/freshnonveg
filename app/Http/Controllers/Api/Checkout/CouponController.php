<?php

namespace App\Http\Controllers\Api\Checkout;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CheckoutService;
use App\Support\CheckoutApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function __construct(private CheckoutService $checkout) {}

    public function apply(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'code' => ['required', 'string', 'max:40'],
            'delivery_schedule_type' => ['nullable', 'in:instant,scheduled'],
            'use_wallet' => ['nullable', 'boolean'],
        ]);

        $quote = $this->checkout->quote($user, [
            'delivery_schedule_type' => $data['delivery_schedule_type'] ?? 'scheduled',
            'use_wallet' => $data['use_wallet'] ?? false,
        ]);

        if ($quote['subtotal'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cart khali hai.',
            ], 422);
        }

        [$coupon, $discount] = $this->checkout->resolveCoupon($data['code'], $quote['subtotal']);
        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Coupon code daalein.',
            ]);
        }

        $quoted = $this->checkout->quote($user, [
            'delivery_schedule_type' => $data['delivery_schedule_type'] ?? 'scheduled',
            'coupon_code' => $coupon->code,
            'use_wallet' => $data['use_wallet'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $coupon->code.' lag gaya. '.$coupon->valueLabel(),
            'data' => [
                'coupon' => CheckoutApi::coupon($coupon, $discount),
                ...CheckoutApi::quote($quoted),
            ],
        ]);
    }
}
