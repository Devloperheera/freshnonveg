<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'product_name',
        'weight_label',
        'preference_note',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lineTotal(): float
    {
        return (float) $this->unit_price * (int) $this->quantity;
    }

    public function imageUrl(): ?string
    {
        $product = $this->relationLoaded('product') ? $this->product : $this->product()->with('images')->first();

        if (! $product) {
            return null;
        }

        $image = $product->images->first();

        return $image?->url();
    }
}
