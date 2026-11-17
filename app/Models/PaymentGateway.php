<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PaymentGateway extends Model
{
    public const CASHFREE = 'cashfree';

    public const RAZORPAY = 'razorpay';

    public const CODES = [self::CASHFREE, self::RAZORPAY];

    protected $fillable = [
        'code',
        'name',
        'is_active',
        'environment',
        'key_id',
        'key_secret',
        'webhook_secret',
    ];

    protected $hidden = ['key_secret', 'webhook_secret'];

    /** @var array<string, ?self> */
    private static array $memo = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'key_id' => 'encrypted',
            'key_secret' => 'encrypted',
            'webhook_secret' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => self::$memo = []);
        static::deleted(fn () => self::$memo = []);
    }

    public static function forCode(string $code): ?self
    {
        if (! array_key_exists($code, self::$memo)) {
            try {
                self::$memo[$code] = Schema::hasTable('payment_gateways')
                    ? self::query()->where('code', $code)->first()
                    : null;
            } catch (Throwable) {
                self::$memo[$code] = null;
            }
        }

        return self::$memo[$code];
    }

    public static function active(): ?self
    {
        foreach (self::CODES as $code) {
            $gateway = self::forCode($code);
            if ($gateway && $gateway->is_active && $gateway->isConfigured()) {
                return $gateway;
            }
        }

        return null;
    }

    public static function activeCode(): string
    {
        return self::active()?->code ?? self::CASHFREE;
    }

    public static function label(?string $code): string
    {
        return match ($code) {
            self::RAZORPAY => 'Razorpay',
            self::CASHFREE => 'Cashfree',
            default => ucfirst((string) $code),
        };
    }

    public function isConfigured(): bool
    {
        return filled($this->key_id) && filled($this->key_secret);
    }

    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    public static function mask(?string $value): string
    {
        $value = (string) $value;
        if ($value === '') {
            return '';
        }

        return strlen($value) <= 8
            ? str_repeat('•', strlen($value))
            : substr($value, 0, 4).str_repeat('•', 8).substr($value, -4);
    }
}
