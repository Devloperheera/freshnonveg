<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class CommerceResponse
{
    public static function counts(User $user): array
    {
        return [
            'cart_count' => (int) $user->cartItems()->sum('quantity'),
            'wishlist_count' => $user->wishlistItems()->count(),
        ];
    }

    public static function jsonCartState(User $user, string $message, int $status = 200): JsonResponse
    {
        $items = $user->cartItems()->latest('id')->get();
        $subtotal = $items->sum(fn ($item) => $item->lineTotal());
        $tax = 0.0;
        $total = $subtotal + $tax;

        return response()->json([
            'success' => true,
            'message' => $message,
            ...self::counts($user),
            'subtotal' => round((float) $subtotal, 2),
            'tax' => $tax,
            'total' => round((float) $total, 2),
            'items' => $items->map(fn ($item) => [
                'id' => $item->id,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => round($item->lineTotal(), 2),
            ])->values()->all(),
            'is_empty' => $items->isEmpty(),
        ], $status);
    }

    public static function jsonWishlist(User $user, string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            ...self::counts($user),
        ]);
    }
}
