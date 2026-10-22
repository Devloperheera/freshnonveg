<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    public const KEY_ORDER_WAY = 'order_way';

    public const KEY_DELIVERY = 'delivery';

    public const KEY_ADVANTAGES = 'advantages';

    protected $fillable = [
        'section_key',
        'title',
        'subtitle',
    ];

    public function getRouteKeyName(): string
    {
        return 'section_key';
    }

    public static function forKey(string $key): ?self
    {
        return static::query()->where('section_key', $key)->first();
    }
}
