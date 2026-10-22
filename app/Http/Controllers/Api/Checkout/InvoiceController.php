<?php

namespace App\Http\Controllers\Api\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderBillNotifier;
use App\Support\CheckoutApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Request $request, Order $order, OrderBillNotifier $bills): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless((int) $order->user_id === (int) $user->id, 404);

        if (! in_array($order->status, ['paid', 'confirmed', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice abhi available nahi hai. Pehle payment complete karein.',
            ], 404);
        }

        $order->load(['items.product.images', 'user']);
        if ($order->isPaid()) {
            $bills->sendIfPaid($order);
            $order->refresh()->load(['items.product.images', 'user']);
        }

        return response()->json([
            'success' => true,
            'data' => CheckoutApi::invoice($order),
        ]);
    }
}
