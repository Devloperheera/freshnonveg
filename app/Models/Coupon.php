<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'title',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'date',
            'expires_at' => 'date',
            'status' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function isPercent(): bool
    {
        return $this->discount_type === 'percent';
    }

    public function rejectionReason(float $subtotal): ?string
    {
        if (! $this->isActive()) {
            return 'Ye coupon active nahi hai.';
        }

        if ($this->starts_at && now()->startOfDay()->lt($this->starts_at->copy()->startOfDay())) {
            return 'Ye coupon abhi start nahi hua.';
        }

        if ($this->expires_at && now()->startOfDay()->gt($this->expires_at->copy()->startOfDay())) {
            return 'Ye coupon expire ho chuka hai.';
        }

        if ($this->usage_limit !== null && (int) $this->used_count >= (int) $this->usage_limit) {
            return 'Ye coupon ki limit khatam ho gayi hai.';
        }

        if ($subtotal + 0.001 < (float) $this->min_order_amount) {
            return 'Is coupon ke liye minimum order ₹'.number_format((float) $this->min_order_amount, 0).' hona chahiye.';
        }

        return null;
    }

    public function discountFor(float $subtotal): float
    {
        if ($this->isPercent()) {
            $amount = round($subtotal * ((float) $this->discount_value / 100), 2);
            if ($this->max_discount !== null && (float) $this->max_discount > 0) {
                $amount = min($amount, (float) $this->max_discount);
            }
        } else {
            $amount = round((float) $this->discount_value, 2);
        }

        return max(0, min($amount, round($subtotal, 2)));
    }

    public function valueLabel(): string
    {
        if ($this->isPercent()) {
            $label = rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.').'% off';
            if ($this->max_discount !== null && (float) $this->max_discount > 0) {
                $label .= ' (max ₹'.number_format((float) $this->max_discount, 0).')';
            }

            return $label;
        }

        return '₹'.number_format((float) $this->discount_value, 0).' off';
    }

    public static function normalizeCode(?string $code): string
    {
        return strtoupper(trim((string) $code));
    }
}
