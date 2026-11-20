<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Simple key → value store for admin-managed settings (delivery charges etc.).
 */
class AppSetting extends Model
{
    private const CACHE_KEY = 'app_settings.all';

    protected $table = 'app_settings';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * @return array<string, string|null>
     */
    public static function allValues(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            try {
                if (! Schema::hasTable('app_settings')) {
                    return [];
                }

                return static::query()->pluck('value', 'key')->all();
            } catch (Throwable) {
                return [];
            }
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $values = static::allValues();

        return array_key_exists($key, $values) && $values[$key] !== null ? $values[$key] : $default;
    }

    public static function getFloat(string $key, float $default): float
    {
        $value = static::get($key);

        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * @param  array<string, scalar|null>  $values
     */
    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value === null ? null : (string) $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
