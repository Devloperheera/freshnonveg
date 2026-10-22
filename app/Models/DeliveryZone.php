<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    public const TYPE_RADIUS = 'radius';

    public const TYPE_PINCODE = 'pincode';

    public const TYPE_POLYGON = 'polygon';

    public const TYPES = [
        self::TYPE_RADIUS => 'Radius Based (KM)',
        self::TYPE_PINCODE => 'Pincode Based',
        self::TYPE_POLYGON => 'Polygon Based',
    ];

    public const TYPE_ICONS = [
        self::TYPE_RADIUS => '📍',
        self::TYPE_PINCODE => '📮',
        self::TYPE_POLYGON => '🗺️',
    ];

    protected $fillable = [
        'name',
        'type',
        'description',
        'center_lat',
        'center_lng',
        'radius_km',
        'center_address',
        'polygon',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'center_lat' => 'float',
            'center_lng' => 'float',
            'radius_km' => 'float',
            'polygon' => 'array',
            'sort_order' => 'integer',
            'status' => 'integer',
        ];
    }

    public function pincodes(): HasMany
    {
        return $this->hasMany(DeliveryZonePincode::class)->orderBy('id');
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    public function isGeo(): bool
    {
        return in_array($this->type, [self::TYPE_RADIUS, self::TYPE_POLYGON], true);
    }

    public function coversPincode(string $pincode): bool
    {
        if ($this->type !== self::TYPE_PINCODE) {
            return false;
        }

        $codes = $this->relationLoaded('pincodes')
            ? $this->pincodes->pluck('pincode')
            : $this->pincodes()->pluck('pincode');

        return $codes->contains($pincode);
    }

    public function containsPoint(float $lat, float $lng): bool
    {
        if ($this->type === self::TYPE_RADIUS) {
            if ($this->center_lat === null || $this->center_lng === null || ! $this->radius_km) {
                return false;
            }

            return self::distanceKm($this->center_lat, $this->center_lng, $lat, $lng) <= (float) $this->radius_km;
        }

        if ($this->type === self::TYPE_POLYGON) {
            return self::pointInPolygon($lat, $lng, $this->polygon ?? []);
        }

        return false;
    }

    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * @param  array<int, array{0: float|int|string, 1: float|int|string}>  $points  [[lat, lng], ...]
     */
    public static function pointInPolygon(float $lat, float $lng, array $points): bool
    {
        $count = count($points);
        if ($count < 3) {
            return false;
        }

        $inside = false;
        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $yi = (float) $points[$i][0];
            $xi = (float) $points[$i][1];
            $yj = (float) $points[$j][0];
            $xj = (float) $points[$j][1];

            if ((($yi > $lat) !== ($yj > $lat)) && ($lng < ($xj - $xi) * ($lat - $yi) / (($yj - $yi) ?: 1e-12) + $xi)) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
