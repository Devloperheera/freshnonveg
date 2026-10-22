<?php

namespace App\Support;

use App\Models\DeliverySlot;

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

    public static function labelFor(?string $key): string
    {
        if ($key === null || $key === '' || $key === 'instant') {
            return '';
        }

        $slot = DeliverySlot::query()->where('slot_key', $key)->first();

        return $slot?->label ?? $key;
    }

    public static function instantFee(): float
    {
        return (float) config('checkout.instant_delivery_fee', 100);
    }

    public static function feeForType(string $type): float
    {
        return $type === self::TYPE_INSTANT ? self::instantFee() : 0.0;
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
