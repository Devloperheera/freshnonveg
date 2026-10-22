<?php

namespace App\Http\Controllers\Api\UserDashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\CustomerAccountService;
use App\Services\OrderBillNotifier;
use App\Support\UserDashboardApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private CustomerAccountService $account) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $paginator = $user->orders()
            ->with('items.product.images')
            ->latest('id')
            ->paginate(min(max((int) $request->input('per_page', 10), 1), 50));

        $reviews = $user->productReviews()->get(['id', 'product_id', 'rating'])->keyBy('product_id');

        return response()->json([
            'success' => true,
            'data' => collect($paginator->items())
                ->map(function (Order $order) use ($reviews) {
                    $payload = UserDashboardApi::orderSummary($order);
                    $payload['items'] = collect($payload['items'])->map(function (array $item) use ($reviews, $order) {
                        $item['can_rate'] = ($item['product_id'] ?? null) && ! $reviews->has($item['product_id']) && $order->isPaid();
                        $item['my_rating'] = $reviews->get($item['product_id'] ?? 0)?->rating;

                        return $item;
                    })->values()->all();

                    return $payload;
                })
                ->values()
                ->all(),
            'meta' => UserDashboardApi::meta($paginator),
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless((int) $order->user_id === (int) $user->id, 404);

        $order->load(['items.product.images', 'user']);

        return response()->json([
            'success' => true,
            'data' => UserDashboardApi::orderDetail($order, $user),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless((int) $order->user_id === (int) $user->id, 404);

        $data = $request->validate([
            'cancel_reason' => ['required', 'string', 'max:500'],
        ]);

        $result = $this->account->cancelOrder($user, $order, $data['cancel_reason']);
        if (! $result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        $order->refresh()->load(['items.product.images', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Order cancel ho gaya. ₹'.number_format($result['refund'], 2).' aapke wallet me refund kar diye gaye.',
            'data' => UserDashboardApi::orderDetail($order, $user->fresh()),
        ]);
    }

    public function bill(Request $request, Order $order, OrderBillNotifier $bills): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless((int) $order->user_id === (int) $user->id, 404);

        if (! in_array($order->status, ['paid', 'confirmed', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Bill abhi available nahi hai.',
            ], 404);
        }

        $order->load(['items', 'user']);
        if ($order->isPaid()) {
            $bills->sendIfPaid($order);
            $order->refresh()->load(['items', 'user']);
        }

        return response()->json([
            'success' => true,
            'data' => UserDashboardApi::orderBill($order),
        ]);
    }
}
