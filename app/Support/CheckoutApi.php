<?php

namespace App\Support;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\WishlistItem;
use App\Services\DeliveryZoneChecker;

class CheckoutApi
{
    /**
     * @return array<string, mixed>
     */
    public static function cartItem(CartItem $item): array
    {
        $item->loadMissing(['product.images']);

        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'weight_label' => $item->weight_label,
            'preference_note' => $item->preference_note,
            'quantity' => (int) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'line_total' => round($item->lineTotal(), 2),
            'image' => $item->imageUrl(),
            'product' => $item->product ? CatalogApi::productSummary($item->product) : null,
            'created_at' => $item->created_at?->toIso8601String(),
            'updated_at' => $item->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function wishlistItem(WishlistItem $item): array
    {
        $item->loadMissing(['product.images']);

        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'image' => $item->product?->images->first()?->url(),
            'product' => $item->product && $item->product->isActive()
                ? CatalogApi::productSummary($item->product)
                : null,
            'created_at' => $item->created_at?->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $quote
     * @return array<string, mixed>
     */
    public static function quote(array $quote): array
    {
        /** @var \Illuminate\Support\Collection<int, CartItem> $items */
        $items = $quote['items'];
        /** @var ?Coupon $coupon */
        $coupon = $quote['coupon'] ?? null;

        return [
            'is_empty' => (bool) $quote['is_empty'],
            'items' => $items->map(fn (CartItem $item) => self::cartItem($item))->values()->all(),
            'items_count' => $items->sum(fn (CartItem $item) => (int) $item->quantity),
            'subtotal' => (float) $quote['subtotal'],
            'tax' => (float) $quote['tax'],
            'shipping' => (float) $quote['shipping'],
            'shipping_label' => is_string($quote['shipping_label']) ? $quote['shipping_label'] : 'Free',
            'delivery_schedule_type' => $quote['delivery_schedule_type'],
            'delivery_fee' => (float) $quote['delivery_fee'],
            'instant_delivery_fee' => (float) $quote['instant_delivery_fee'],
            'discount' => (float) $quote['discount'],
            'coupon' => $coupon ? self::coupon($coupon, (float) $quote['discount']) : null,
            'wallet_balance' => (float) $quote['wallet_balance'],
            'use_wallet' => (bool) $quote['use_wallet'],
            'wallet_used' => (float) $quote['wallet_used'],
            'wallet_max_usable' => (float) $quote['wallet_max_usable'],
            'payable' => (float) $quote['payable'],
            'total' => (float) $quote['total'],
            'minimum_online_pay' => (float) $quote['minimum_online_pay'],
            'time_slots' => collect($quote['time_slots'])->map(fn ($label, $key) => [
                'value' => (string) $key,
                'label' => (string) $label,
            ])->values()->all(),
            'payment_method' => $quote['payment_method'],
            'payment_configured' => (bool) $quote['payment_configured'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function coupon(Coupon $coupon, float $discount): array
    {
        return [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'title' => $coupon->title,
            'discount_type' => $coupon->discount_type,
            'discount_value' => (float) $coupon->discount_value,
            'min_order_amount' => (float) $coupon->min_order_amount,
            'max_discount' => $coupon->max_discount !== null ? (float) $coupon->max_discount : null,
            'value_label' => $coupon->valueLabel(),
            'discount' => $discount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function customer(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'wallet_balance' => (float) $user->wallet_balance,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function invoice(Order $order): array
    {
        $order->loadMissing(['items.product.images', 'user']);
        $subtotal = round($order->items->sum(fn (OrderItem $line) => (float) $line->unit_price * (int) $line->quantity), 2);
        $discount = (float) $order->discount_amount;
        $delivery = (float) $order->delivery_fee;
        $wallet = (float) $order->wallet_amount;
        $tax = 0.0;
        $shipping = 0.0;
        $isPaid = $order->isPaid();
        $isCancelled = $order->isCancelled();
        $slot = $order->delivery_schedule_type === 'instant'
            ? 'Instant delivery'
            : trim(($order->delivery_date?->format('d M Y') ?? '').' '.CheckoutDelivery::labelFor($order->delivery_time));
        $paymentStamp = $isCancelled ? 'CANCELLED' : ($isPaid ? 'PAID' : strtoupper(str_replace('_', ' ', (string) $order->status)));
        $company = Company::billHeader();

        return [
            'invoice_title' => 'TAX INVOICE / FOOD BILL',
            'invoice_type' => 'tax_invoice',
            'stamp' => $paymentStamp,
            'company' => $company,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $isPaid ? 'Paid' : ($isCancelled ? 'Cancelled' : ucfirst(str_replace('_', ' ', (string) $order->status))),
                'created_at' => $order->created_at?->toIso8601String(),
                'created_at_label' => $order->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A'),
                'cancelled_at' => $order->cancelled_at?->toIso8601String(),
                'cancel_reason' => $order->cancel_reason,
            ],
            'customer' => [
                'id' => $order->user_id,
                'name' => $order->user?->name ?? 'Customer',
                'email' => $order->user?->email,
                'phone' => $order->phone,
            ],
            'delivery' => [
                'schedule_type' => $order->delivery_schedule_type,
                'date' => $order->delivery_date?->toDateString(),
                'time' => $order->delivery_time,
                'label' => $slot !== '' ? $slot : null,
                'fee' => $delivery,
                'address' => $order->delivery_address,
                'pincode' => $order->pincode,
                'city' => $order->city,
                'state' => $order->state,
                'post_office' => $order->post_office,
            ],
            'items' => $order->items->map(function (OrderItem $line) {
                $qty = (int) $line->quantity;
                $price = (float) $line->unit_price;

                return [
                    'id' => $line->id,
                    'product_id' => $line->product_id,
                    'product_name' => $line->product_name,
                    'weight_label' => $line->weight_label,
                    'preference_note' => $line->preference_note,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'line_total' => round($price * $qty, 2),
                    'image' => $line->imageUrl(),
                ];
            })->values()->all(),
            'totals' => [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'tax_label' => 'Inclusive of taxes as applicable',
                'shipping' => $shipping,
                'shipping_label' => $shipping > 0 ? '₹'.number_format($shipping, 2) : 'Free',
                'discount' => $discount,
                'coupon_code' => $order->coupon_code,
                'delivery_fee' => $delivery,
                'delivery_label' => $delivery > 0 ? '₹'.number_format($delivery, 2) : 'Free',
                'wallet_amount' => $wallet,
                'grand_total' => (float) $order->total,
                'refundable_amount' => $order->refundableAmount(),
            ],
            'payment' => [
                'method' => $order->payment_method,
                'method_label' => 'Online',
                'gateway' => 'cashfree',
                'status' => $order->status,
                'stamp' => $paymentStamp,
                'is_paid' => $isPaid,
                'is_cancelled' => $isCancelled,
                'cashfree_payment_id' => $order->cashfree_payment_id,
                'paid_online' => (float) $order->total,
                'wallet_used' => $wallet,
            ],
            'notes' => $order->customer_notes,
            'bill_emailed' => (bool) $order->bill_emailed_at,
            'bill_emailed_at' => $order->bill_emailed_at?->toIso8601String(),
            'footer' => [
                'This is a computer generated invoice of '.$company['legal_name'].'.',
                'GSTIN '.$company['gstin'].' · Prices inclusive of taxes as applicable.',
                'Dhanyavaad! FreshNonVeg — ghar tak fresh.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function deliveryCheck(array $zone): array
    {
        $model = $zone['zone'] ?? null;
        $unavailable = ($zone['deliverable'] ?? null) === false;

        return [
            'deliverable' => $zone['deliverable'] ?? null,
            'zone' => is_object($model) ? ($model->name ?? null) : $model,
            'message' => $zone['message'] ?? '',
            'message_hi' => $zone['message_hi'] ?? ($unavailable ? DeliveryZoneChecker::COMING_SOON_HI : null),
            'message_en' => $zone['message_en'] ?? ($unavailable ? DeliveryZoneChecker::COMING_SOON_EN : null),
        ];
    }
}
