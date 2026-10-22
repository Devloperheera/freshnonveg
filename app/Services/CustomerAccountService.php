<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CustomerAccountService
{
    public const PAID_STATUSES = ['paid', 'confirmed'];

    public function __construct(private WalletService $wallet) {}

    /**
     * @return array{orders: int, spent: float, wallet: float, open_complaints: int}
     */
    public function stats(User $user): array
    {
        $paidOrders = $user->orders()->whereIn('status', self::PAID_STATUSES);

        return [
            'orders' => (clone $paidOrders)->count(),
            'spent' => (float) (clone $paidOrders)->sum('total'),
            'wallet' => (float) $user->wallet_balance,
            'open_complaints' => $user->complaints()->whereIn('status', ['open', 'in_progress'])->count(),
        ];
    }

    /**
     * @return array{ok: true, refund: float}|array{ok: false, message: string}
     */
    public function cancelOrder(User $user, Order $order, string $reason): array
    {
        if ((int) $order->user_id !== (int) $user->id) {
            throw new RuntimeException('Order nahi mili.');
        }

        $refund = DB::transaction(function () use ($order, $reason) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (! $locked->canBeCancelled()) {
                return null;
            }

            $amount = $locked->refundableAmount();
            $locked->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            if ($locked->coupon_id) {
                Coupon::query()->whereKey($locked->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
            }

            if ($amount > 0) {
                $this->wallet->credit(
                    $locked->user,
                    $amount,
                    'Order '.$locked->order_number.' cancel — refund',
                    null,
                    $locked,
                    'refund'
                );
            }

            return $amount;
        });

        if ($refund === null) {
            return [
                'ok' => false,
                'message' => 'Cancel karne ka samay nikal gaya hai. Madad ke liye complaint karein.',
            ];
        }

        return ['ok' => true, 'refund' => (float) $refund];
    }

    /**
     * @param  array{order_id?: int|null, subject: string, message: string}  $data
     */
    public function storeComplaint(User $user, array $data): Complaint
    {
        return Complaint::query()->create([
            'user_id' => $user->id,
            'order_id' => $data['order_id'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'open',
        ]);
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function purchasedItems(User $user): Collection
    {
        return OrderItem::query()
            ->whereNotNull('product_id')
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->whereIn('status', self::PAID_STATUSES))
            ->with(['product.images', 'order:id,order_number,created_at'])
            ->latest('id')
            ->get()
            ->filter(fn (OrderItem $line) => $line->product !== null)
            ->unique('product_id')
            ->values();
    }

    /**
     * @param  array{rating: int, review?: string|null}  $data
     * @return array{ok: true, review: ProductReview}|array{ok: false, message: string}
     */
    public function storeRating(User $user, Product $product, array $data): array
    {
        $line = OrderItem::query()
            ->where('product_id', $product->id)
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->whereIn('status', self::PAID_STATUSES))
            ->latest('id')
            ->first();

        if (! $line) {
            return [
                'ok' => false,
                'message' => 'Rating sirf khareede hue products par de sakte hain.',
            ];
        }

        if ($user->productReviews()->where('product_id', $product->id)->exists()) {
            return [
                'ok' => false,
                'message' => 'Aap is product ko pehle hi rating de chuke hain.',
            ];
        }

        try {
            $review = ProductReview::query()->create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'order_id' => $line->order_id,
                'rating' => (int) $data['rating'],
                'review' => filled($data['review'] ?? null) ? trim((string) $data['review']) : null,
                'status' => 1,
            ]);
        } catch (UniqueConstraintViolationException) {
            return [
                'ok' => false,
                'message' => 'Aap is product ko pehle hi rating de chuke hain.',
            ];
        }

        return ['ok' => true, 'review' => $review];
    }
}
