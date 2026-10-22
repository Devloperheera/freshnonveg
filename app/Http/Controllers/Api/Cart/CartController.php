<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Support\CheckoutApi;
use App\Support\CommerceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CheckoutService $checkout,
    ) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $paid = $this->checkout->syncPendingPaid($user);
        $quote = $this->checkout->quote($user);

        return response()->json([
            'success' => true,
            'data' => [
                ...CheckoutApi::quote($quote),
                ...CommerceResponse::counts($user),
                'pending_paid_order' => $paid ? [
                    'id' => $paid->id,
                    'order_number' => $paid->order_number,
                ] : null,
            ],
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'product_weight_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'preference_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $item = $this->cart->addToCart($user, $product, $data);

        return $this->cartPayload($user, 'Product cart mein add ho gaya.', [
            'item' => CheckoutApi::cartItem($item),
        ], 201);
    }

    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $this->cart->updateQuantity($user, $cartItem, (int) $data['quantity']);

        return $this->cartPayload($user, 'Cart updated.');
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->cart->removeItem($user, $cartItem);

        return $this->cartPayload($user, 'Item cart se hata diya gaya.');
    }

    public function clear(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->cart->clear($user);

        return $this->cartPayload($user, 'Cart clear ho gaya.');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function cartPayload(User $user, string $message, array $extra = [], int $status = 200): JsonResponse
    {
        $quote = $this->checkout->quote($user);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                ...CheckoutApi::quote($quote),
                ...CommerceResponse::counts($user),
                ...$extra,
            ],
        ], $status);
    }
}
