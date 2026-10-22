<?php

namespace App\Http\Controllers\Api\Cart;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use App\Services\CartService;
use App\Support\CheckoutApi;
use App\Support\CommerceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $items = $this->cart->wishlistItems($user);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items->map(fn (WishlistItem $item) => CheckoutApi::wishlistItem($item))->values()->all(),
                ...CommerceResponse::counts($user),
            ],
        ]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $item = $this->cart->addToWishlist($user, $product);

        return response()->json([
            'success' => true,
            'message' => 'Wishlist mein add ho gaya.',
            'data' => [
                'item' => CheckoutApi::wishlistItem($item),
                ...CommerceResponse::counts($user),
            ],
        ], 201);
    }

    public function destroy(Request $request, WishlistItem $wishlistItem): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->cart->removeWishlistItem($user, $wishlistItem);

        return response()->json([
            'success' => true,
            'message' => 'Wishlist se hata diya gaya.',
            'data' => CommerceResponse::counts($user),
        ]);
    }

    public function destroyProduct(Request $request, Product $product): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->cart->removeWishlistProduct($user, $product);

        return response()->json([
            'success' => true,
            'message' => 'Wishlist se hata diya gaya.',
            'data' => CommerceResponse::counts($user),
        ]);
    }
}
