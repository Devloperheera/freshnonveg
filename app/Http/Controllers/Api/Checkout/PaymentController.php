<?php

namespace App\Http\Controllers\Api\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\OrderBillNotifier;
use App\Support\CheckoutApi;
use App\Support\UserDashboardApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private CheckoutService $checkout) {}

    public function verify(Request $request, OrderBillNotifier $bills): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'order_id' => ['required_without:order_number', 'nullable', 'integer'],
            'order_number' => ['required_without:order_id', 'nullable', 'string', 'max:40'],
        ]);

        $order = $this->findOrder($user, $data['order_id'] ?? null, $data['order_number'] ?? null);
        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order nahi mili.',
            ], 404);
        }

        $paid = $this->checkout->verifyPaid($order);
        $order->refresh()->load(['items.product.images', 'user']);

        if (! $paid) {
            $this->checkout->markFailedIfUnpaid($order);
            $order->refresh();

            return response()->json([
                'success' => false,
                'message' => 'Payment complete nahi hui. Dubara checkout try karein.',
                'data' => [
                    'order' => UserDashboardApi::orderDetail($order, $user),
                ],
            ], 422);
        }

        $bills->sendIfPaid($order);
        $order->refresh()->load(['items.product.images', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Order place ho gaya! / Your order has been placed.',
            'data' => [
                'order' => UserDashboardApi::orderDetail($order, $user),
                'invoice' => CheckoutApi::invoice($order),
            ],
        ]);
    }

    public function status(Request $request, Order $order): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless((int) $order->user_id === (int) $user->id, 404);

        $this->checkout->verifyPaid($order);
        $order->refresh()->load(['items.product.images', 'user']);

        return response()->json([
            'success' => true,
            'data' => [
                'order' => UserDashboardApi::orderDetail($order, $user),
                'is_paid' => $order->isPaid(),
                'invoice' => $order->isPaid() || $order->isCancelled() ? CheckoutApi::invoice($order) : null,
            ],
        ]);
    }

    public function success(Request $request, Order $order, OrderBillNotifier $bills): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless((int) $order->user_id === (int) $user->id, 404);

        if ($order->payment_method === 'cashfree' && $order->status === 'awaiting_payment') {
            $this->checkout->verifyPaid($order);
            $order->refresh();
        }

        if ($order->payment_method === 'cashfree' && ! in_array($order->status, ['paid', 'confirmed'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Payment abhi pending hai.',
                'data' => [
                    'order' => UserDashboardApi::orderDetail($order->load(['items.product.images', 'user']), $user),
                ],
            ], 422);
        }

        $order->load(['items.product.images', 'user']);
        $bills->sendIfPaid($order);
        $order->refresh()->load(['items.product.images', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Order place ho gaya! / Your order has been placed.',
            'data' => [
                'order' => UserDashboardApi::orderDetail($order, $user),
                'invoice' => CheckoutApi::invoice($order),
            ],
        ]);
    }

    private function findOrder(User $user, mixed $id, mixed $number): ?Order
    {
        $query = Order::query()->where('user_id', $user->id);

        if (filled($id)) {
            return $query->whereKey($id)->first();
        }

        if (filled($number)) {
            return $query->where('order_number', (string) $number)->first();
        }

        return null;
    }
}
