<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use App\Support\CommerceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductCartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function addToCart(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        if (! $product->isActive()) {
            abort(404);
        }

        $data = $request->validate([
            'product_weight_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'preference_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $this->cart->addToCart($user, $product, $data);

        $message = 'Product cart mein add ho gaya.';

        if ($request->expectsJson()) {
            $payload = CommerceResponse::jsonCartState($user, $message)->getData(true);
            $payload['cart_url'] = route('cart');

            return response()->json($payload);
        }

        return redirect()
            ->route('cart')
            ->with('success', $message);
    }

    public function addToWishlist(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        if (! $product->isActive()) {
            abort(404);
        }

        $user = auth()->user();
        $this->cart->addToWishlist($user, $product);

        $message = 'Wishlist mein add ho gaya.';

        if ($request->expectsJson()) {
            return CommerceResponse::jsonWishlist($user, $message);
        }

        return back()->with('success', $message);
    }
}
