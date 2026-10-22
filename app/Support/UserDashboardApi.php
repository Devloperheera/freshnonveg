<?php

namespace App\Support;

use App\Models\Complaint;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserDashboardApi
{
    /** @var array<string, array{0: string, 1: string}> */
    private const ORDER_STATUSES = [
        'paid' => ['Paid', 'ok'],
        'confirmed' => ['Confirmed', 'ok'],
        'awaiting_payment' => ['Payment pending', 'warn'],
        'payment_failed' => ['Payment failed', 'bad'],
        'cancelled' => ['Cancelled', 'muted'],
    ];

    /**
     * @return array<string, mixed>
     */
    public static function user(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'profile_image' => $user->profile_image,
            'address' => $user->address,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
            'wallet_balance' => (float) $user->wallet_balance,
            'status' => $user->status,
            'has_password' => filled($user->password),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function orderSummary(Order $order): array
    {
        $order->loadMissing(['items.product.images']);
        [$label, $tone] = self::ORDER_STATUSES[$order->status] ?? [ucfirst(str_replace('_', ' ', (string) $order->status)), 'muted'];

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $label,
            'status_tone' => $tone,
            'total' => (float) $order->total,
            'wallet_amount' => (float) $order->wallet_amount,
            'discount_amount' => (float) $order->discount_amount,
            'delivery_fee' => (float) $order->delivery_fee,
            'items_count' => $order->items->count(),
            'created_at' => $order->created_at?->toIso8601String(),
            'created_at_label' => $order->created_at?->format('d M Y, h:i A'),
            'can_cancel' => $order->canBeCancelled(),
            'items' => $order->items->take(3)->map(fn (OrderItem $line) => self::orderItem($line, false))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function orderDetail(Order $order, User $user): array
    {
        $order->loadMissing(['items.product.images']);
        $summary = self::orderSummary($order);
        $isPaid = $order->isPaid();
        $isCancelled = $order->isCancelled();
        $slotText = $order->delivery_schedule_type === 'instant'
            ? 'Instant delivery'
            : trim(($order->delivery_date?->format('D, d M Y') ?? '').' · '.CheckoutDelivery::labelFor($order->delivery_time));

        $reviews = $user->productReviews()
            ->whereIn('product_id', $order->items->pluck('product_id')->filter())
            ->get()
            ->keyBy('product_id');

        return array_merge($summary, [
            'phone' => $order->phone,
            'delivery_address' => $order->delivery_address,
            'pincode' => $order->pincode,
            'city' => $order->city,
            'state' => $order->state,
            'post_office' => $order->post_office,
            'delivery_schedule_type' => $order->delivery_schedule_type,
            'delivery_date' => $order->delivery_date?->toDateString(),
            'delivery_time' => $order->delivery_time,
            'delivery_label' => $slotText !== '' ? $slotText : null,
            'coupon_code' => $order->coupon_code,
            'payment_method' => $order->payment_method,
            'customer_notes' => $order->customer_notes,
            'cancelled_at' => $order->cancelled_at?->toIso8601String(),
            'cancel_reason' => $order->cancel_reason,
            'cancel_deadline' => $order->cancelDeadline()?->toIso8601String(),
            'cancel_policy' => $order->cancelPolicyText(),
            'refundable_amount' => $order->refundableAmount(),
            'is_paid' => $isPaid,
            'is_cancelled' => $isCancelled,
            'bill_available' => $isPaid || $isCancelled,
            'items' => $order->items->map(function (OrderItem $line) use ($reviews, $order) {
                $payload = self::orderItem($line, true);
                $payload['can_rate'] = $line->product_id && ! $reviews->has($line->product_id) && $order->isPaid();
                $payload['my_rating'] = $reviews->get($line->product_id)?->rating;

                return $payload;
            })->values()->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function orderItem(OrderItem $line, bool $detailed = false): array
    {
        $payload = [
            'id' => $line->id,
            'product_id' => $line->product_id,
            'product_name' => $line->product_name,
            'weight_label' => $line->weight_label,
            'quantity' => (int) $line->quantity,
            'unit_price' => (float) $line->unit_price,
            'line_total' => round((float) $line->unit_price * (int) $line->quantity, 2),
            'image' => $line->imageUrl(),
        ];

        if ($detailed) {
            $payload['preference_note'] = $line->preference_note;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public static function orderBill(Order $order): array
    {
        return CheckoutApi::invoice($order);
    }

    /**
     * @return array<string, mixed>
     */
    public static function transaction(WalletTransaction $txn): array
    {
        return [
            'id' => $txn->id,
            'type' => $txn->type,
            'is_credit' => $txn->isCredit(),
            'source' => $txn->source(),
            'source_label' => WalletTransaction::SOURCES[$txn->source()] ?? $txn->source(),
            'amount' => (float) $txn->amount,
            'balance_after' => (float) $txn->balance_after,
            'description' => $txn->description,
            'complaint_id' => $txn->complaint_id,
            'order_id' => $txn->order_id,
            'created_at' => $txn->created_at?->toIso8601String(),
            'created_at_label' => $txn->created_at?->format('d M Y, h:i A'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function complaint(Complaint $complaint): array
    {
        $complaint->loadMissing(['order:id,order_number,total,created_at']);

        return [
            'id' => $complaint->id,
            'subject' => $complaint->subject,
            'message' => $complaint->message,
            'status' => $complaint->status,
            'status_label' => $complaint->statusLabel(),
            'admin_reply' => $complaint->admin_reply,
            'credited_amount' => (float) $complaint->credited_amount,
            'order_id' => $complaint->order_id,
            'order_number' => $complaint->order?->order_number,
            'resolved_at' => $complaint->resolved_at?->toIso8601String(),
            'created_at' => $complaint->created_at?->toIso8601String(),
            'created_at_label' => $complaint->created_at?->format('d M Y, h:i A'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function review(ProductReview $review): array
    {
        return [
            'id' => $review->id,
            'product_id' => $review->product_id,
            'order_id' => $review->order_id,
            'rating' => (int) $review->rating,
            'review' => $review->review,
            'status' => (int) $review->status,
            'created_at' => $review->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function ratingItem(OrderItem $line, ?ProductReview $review): array
    {
        return [
            'product_id' => $line->product_id,
            'product_name' => $line->product?->name ?? $line->product_name,
            'image' => $line->imageUrl(),
            'order_id' => $line->order_id,
            'order_number' => $line->order?->order_number,
            'purchased_at' => $line->order?->created_at?->toIso8601String(),
            'has_rating' => $review !== null,
            'rating' => $review?->rating,
            'review' => $review?->review,
        ];
    }

    /**
     * @return array{current_page: int, last_page: int, per_page: int, total: int}
     */
    public static function meta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
