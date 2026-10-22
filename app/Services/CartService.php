<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductWeight;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * @return Collection<int, CartItem>
     */
    public function items(User $user): Collection
    {
        return $user->cartItems()->with(['product.images'])->latest('id')->get();
    }

    /**
     * @return Collection<int, WishlistItem>
     */
    public function wishlistItems(User $user): Collection
    {
        return $user->wishlistItems()->with(['product.images'])->latest('id')->get();
    }

    /**
     * @param  array{product_weight_id: int, quantity: int, preference_note?: string|null}  $data
     */
    public function addToCart(User $user, Product $product, array $data): CartItem
    {
        if (! $product->isActive()) {
            throw ValidationException::withMessages([
                'product' => 'Product available nahi hai.',
            ]);
        }

        $weight = ProductWeight::query()
            ->where('product_id', $product->id)
            ->whereKey($data['product_weight_id'])
            ->first();

        if (! $weight) {
            throw ValidationException::withMessages([
                'product_weight_id' => 'Weight option galat hai.',
            ]);
        }

        $preference = isset($data['preference_note']) ? trim((string) $data['preference_note']) : null;
        $preference = $preference === '' ? null : $preference;
        $quantity = (int) $data['quantity'];

        $existing = CartItem::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('weight_label', $weight->weight_label)
            ->where('preference_note', $preference)
            ->first();

        if ($existing) {
            $existing->update([
                'quantity' => min(99, (int) $existing->quantity + $quantity),
                'unit_price' => $weight->price,
            ]);

            return $existing->fresh(['product.images']) ?? $existing;
        }

        return CartItem::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'weight_label' => $weight->weight_label,
            'preference_note' => $preference,
            'quantity' => $quantity,
            'unit_price' => $weight->price,
        ])->load(['product.images']);
    }

    public function updateQuantity(User $user, CartItem $cartItem, int $quantity): CartItem
    {
        $this->assertOwnsCartItem($user, $cartItem);
        $cartItem->update(['quantity' => $quantity]);

        return $cartItem->fresh(['product.images']) ?? $cartItem;
    }

    public function removeItem(User $user, CartItem $cartItem): void
    {
        $this->assertOwnsCartItem($user, $cartItem);
        $cartItem->delete();
    }

    public function clear(User $user): void
    {
        $user->cartItems()->delete();
    }

    public function addToWishlist(User $user, Product $product): WishlistItem
    {
        if (! $product->isActive()) {
            throw ValidationException::withMessages([
                'product' => 'Product available nahi hai.',
            ]);
        }

        return WishlistItem::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $product->id,
            ],
            [
                'product_name' => $product->name,
            ]
        )->load(['product.images']);
    }

    public function removeWishlistItem(User $user, WishlistItem $item): void
    {
        if ((int) $item->user_id !== (int) $user->id) {
            abort(403);
        }

        $item->delete();
    }

    public function removeWishlistProduct(User $user, Product $product): void
    {
        $user->wishlistItems()->where('product_id', $product->id)->delete();
    }

    private function assertOwnsCartItem(User $user, CartItem $cartItem): void
    {
        if ((int) $cartItem->user_id !== (int) $user->id) {
            abort(403);
        }
    }
}
