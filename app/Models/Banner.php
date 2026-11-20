<?php

namespace App\Models;

use App\Support\StorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    protected $fillable = [
        'name',
        'image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    /** Banner par dikhne wale products (admin ne jis order me chune). */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('banner_product.sort_order');
    }

    /**
     * Sirf active products ki ids, admin wale order me.
     *
     * @return list<int>
     */
    public function activeProductIds(): array
    {
        return $this->products()->where('products.status', 1)->pluck('products.id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Banner click kahan jaye: 1 product → product page, kai products → shop (banner filter), koi nahi → null.
     */
    public function linkUrl(): ?string
    {
        $ids = $this->activeProductIds();

        return match (count($ids)) {
            0 => null,
            1 => route('products.show', $ids[0]),
            default => route('shop.index', ['banner' => $this->id]),
        };
    }

    public function imageUrl(): string
    {
        return StorageUrl::forPath($this->image) ?? '';
    }

    public function deleteImageFile(): void
    {
        if ($this->image && ! str_starts_with($this->image, 'http')) {
            Storage::disk('public')->delete($this->image);
        }
    }
}
