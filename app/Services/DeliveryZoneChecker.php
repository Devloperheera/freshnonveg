<?php

namespace App\Services;

use App\Models\DeliveryZone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeliveryZoneChecker
{
    public const COMING_SOON_HI = 'Abhi is location par delivery nahi ho rahi. Jaldi hi wahan bhi delivery hogi.';

    public const COMING_SOON_EN = 'Delivery is not available at this location yet. We will start delivering here soon too.';

    /**
     * deliverable: true = zone mila, false = kisi zone me nahi, null = location verify nahi ho payi.
     *
     * @return array{deliverable: ?bool, zone: ?DeliveryZone, message: string, message_hi?: string, message_en?: string}
     */
    public function checkPincode(string $pincode): array
    {
        $zones = DeliveryZone::query()
            ->where('status', 1)
            ->with('pincodes')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($zones->isEmpty()) {
            return ['deliverable' => true, 'zone' => null, 'message' => ''];
        }

        foreach ($zones as $zone) {
            if ($zone->coversPincode($pincode)) {
                return $this->available($zone);
            }
        }

        $geoZones = $zones->filter(fn (DeliveryZone $zone) => $zone->isGeo());
        if ($geoZones->isEmpty()) {
            return $this->unavailable();
        }

        $point = $this->geocodePincode($pincode);
        if (! $point) {
            return $this->unavailable();
        }

        foreach ($geoZones as $zone) {
            if ($zone->containsPoint($point[0], $point[1])) {
                return $this->available($zone);
            }
        }

        return $this->unavailable();
    }

    /**
     * @return array{0: float, 1: float}|null  [lat, lng]
     */
    public function geocodePincode(string $pincode): ?array
    {
        $cacheKey = 'pincode_geo:'.$pincode;
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'FreshNonVeg-Delivery/1.0 ('.config('app.url').')',
            ])
                ->withOptions(['version' => '1.1', 'force_ip_resolve' => 'v4'])
                ->connectTimeout(8)
                ->timeout(15)
                ->get('https://photon.komoot.io/api/', [
                    'q' => $pincode.' India',
                    'limit' => 10,
                    'lang' => 'en',
                    'bbox' => '68.1,6.5,97.4,35.7',
                ]);
        } catch (Throwable $e) {
            Log::warning('Pincode geocode failed', ['pincode' => $pincode, 'error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $features = collect($response->json('features') ?? []);
        $match = $features->first(fn ($f) => (string) ($f['properties']['postcode'] ?? '') === $pincode);
        if (! $match) {
            return null;
        }

        [$lng, $lat] = $match['geometry']['coordinates'] ?? [null, null];
        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        $point = [(float) $lat, (float) $lng];
        Cache::put($cacheKey, $point, now()->addDays(30));

        return $point;
    }

    /**
     * @return array{deliverable: bool, zone: DeliveryZone, message: string}
     */
    private function available(DeliveryZone $zone): array
    {
        return ['deliverable' => true, 'zone' => $zone, 'message' => 'Delivery available — '.$zone->name];
    }

    /**
     * @return array{deliverable: bool, zone: null, message: string, message_hi: string, message_en: string}
     */
    private function unavailable(): array
    {
        return [
            'deliverable' => false,
            'zone' => null,
            'message' => self::COMING_SOON_HI."\n".self::COMING_SOON_EN,
            'message_hi' => self::COMING_SOON_HI,
            'message_en' => self::COMING_SOON_EN,
        ];
    }
}
