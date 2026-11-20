<?php

namespace App\Support;

use App\Models\AppSetting;
use App\Models\DeliverySlot;
use Illuminate\Support\Carbon;

class CheckoutDelivery
{
    public const TYPE_INSTANT = 'instant';

    public const TYPE_SCHEDULED = 'scheduled';

    /**
     * Active slots from admin, keyed by slot_key.
     *
     * @return array<string, string>
     */
    public static function timeSlots(): array
    {
        return DeliverySlot::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->get()
            ->mapWithKeys(fn (DeliverySlot $slot) => [$slot->slot_key => $slot->label])
            ->all();
    }

    /** Delivery slots hamesha India time me chalte hain (app timezone UTC ho tab bhi). */
    public const TIMEZONE = 'Asia/Kolkata';

    public static function now(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }

    public static function todayDate(): string
    {
        return self::now()->toDateString();
    }

    /**
     * Active slots with start / end time (HH:MM).
     *
     * @return array<string, array{label: string, start: string, end: string}>
     */
    public static function slotDetails(): array
    {
        return DeliverySlot::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->get()
            ->mapWithKeys(fn (DeliverySlot $slot) => [$slot->slot_key => [
                'label' => (string) $slot->label,
                'start' => $slot->startInput(),
                'end' => $slot->endInput(),
            ]])
            ->all();
    }

    /** Aaj ki date par slot ka start time nikal gaya ho to false. Future date → hamesha true. */
    public static function slotAvailableOn(string $start, ?string $date): bool
    {
        if ($date === null || $date === '' || $date !== self::todayDate()) {
            return true;
        }

        if (! preg_match('/^(\d{2}):(\d{2})$/', $start, $m)) {
            return true;
        }

        $now = self::now();

        return ((int) $m[1] * 60 + (int) $m[2]) > ($now->hour * 60 + $now->minute);
    }

    /**
     * Slots jo is date par abhi book ho sakte hain (aaj → sirf aage wale).
     *
     * @return array<string, string>
     */
    public static function availableSlots(?string $date = null): array
    {
        $date ??= self::todayDate();

        return collect(self::slotDetails())
            ->filter(fn (array $slot) => self::slotAvailableOn($slot['start'], $date))
            ->map(fn (array $slot) => $slot['label'])
            ->all();
    }

    public static function labelFor(?string $key): string
    {
        if ($key === null || $key === '' || $key === 'instant') {
            return '';
        }

        $slot = DeliverySlot::query()->where('slot_key', $key)->first();

        return $slot?->label ?? $key;
    }

    /** Instant delivery extra — har order par (order value kuch bhi ho). Admin → Settings. */
    public static function instantFee(): float
    {
        return max(0.0, AppSetting::getFloat('instant_delivery_fee', (float) config('checkout.instant_delivery_fee', 100)));
    }

    /** Normal delivery charge jab order free-delivery limit se kam ho. Admin → Settings. */
    public static function deliveryCharge(): float
    {
        return max(0.0, AppSetting::getFloat('delivery_charge', 100));
    }

    /** Is amount (subtotal) ya usse upar → normal delivery free. 0 = hamesha free. */
    public static function freeDeliveryMinOrder(): float
    {
        return max(0.0, AppSetting::getFloat('free_delivery_min_order', 1100));
    }

    /** Normal delivery charge for a cart subtotal (coupon se pehle wala amount). */
    public static function shippingFor(float $subtotal): float
    {
        $min = self::freeDeliveryMinOrder();
        if ($subtotal <= 0 || $min <= 0 || $subtotal >= $min) {
            return 0.0;
        }

        return self::deliveryCharge();
    }

    /**
     * Delivery breakdown: shipping (order value par) + instant extra.
     *
     * @return array{shipping: float, instant: float, total: float, amount_for_free_delivery: float}
     */
    public static function breakdown(string $type, float $subtotal): array
    {
        $shipping = self::shippingFor($subtotal);
        $instant = $type === self::TYPE_INSTANT ? self::instantFee() : 0.0;
        $min = self::freeDeliveryMinOrder();

        return [
            'shipping' => $shipping,
            'instant' => $instant,
            'total' => round($shipping + $instant, 2),
            'amount_for_free_delivery' => $shipping > 0 ? round(max(0, $min - $subtotal), 2) : 0.0,
        ];
    }

    public static function feeForType(string $type, float $subtotal = 0.0): float
    {
        return self::breakdown($type, $subtotal)['total'];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function composeFullAddress(array $data): string
    {
        $parts = array_filter([
            trim((string) ($data['delivery_address'] ?? '')),
            trim((string) ($data['city'] ?? '')),
            trim((string) ($data['state'] ?? '')),
            trim((string) ($data['pincode'] ?? '')),
        ]);

        return implode(', ', $parts);
    }
}
