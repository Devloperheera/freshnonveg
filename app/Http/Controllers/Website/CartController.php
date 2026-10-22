<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Support\CommerceResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CheckoutService $checkout,
    ) {}

    public function index(): View|RedirectResponse
    {
        if ($paid = $this->checkout->syncPendingPaid(auth()->user())) {
            return redirect()
                ->route('checkout.success', $paid)
                ->with('success', 'Order place ho gaya! / Your order has been placed.');
        }

        $items = auth()->user()
            ->cartItems()
            ->with(['product.images'])
            ->latest('id')
            ->get();

        $subtotal = $items->sum(fn (CartItem $item) => $item->lineTotal());
        $shipping = 0.0;
        $tax = 0.0;
        $total = $subtotal + $shipping + $tax;

        return view('Website.cart', compact('items', 'subtotal', 'shipping', 'tax', 'total'));
    }

    public function update(Request $request, CartItem $cartItem): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $this->cart->updateQuantity($request->user(), $cartItem, (int) $data['quantity']);

        return $this->cartResponse($request, 'Cart updated.');
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse|RedirectResponse
    {
        $this->cart->removeItem($request->user(), $cartItem);

        return $this->cartResponse($request, 'Item cart se hata diya gaya.');
    }

    public function clear(Request $request): JsonResponse|RedirectResponse
    {
        $this->cart->clear($request->user());

        return $this->cartResponse($request, 'Cart clear ho gaya.');
    }

    private function cartResponse(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return CommerceResponse::jsonCartState($request->user(), $message);
        }

        return back()->with('success', $message);
    }
}
