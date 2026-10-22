<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\StorageUrl;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'banner_id',
        'name',
        'description',
        'video_path',
        'is_featured',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'status' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    public function weights(): HasMany
    {
        return $this->hasMany(ProductWeight::class)->orderBy('sort_order')->orderBy('id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function videoUrl(): ?string
    {
        if (! $this->video_path) {
            return null;
        }

        return StorageUrl::forPath($this->video_path);
    }

    protected static function booted(): void
    {
        static::deleting(function (Product $product): void {
            $product->images->each(function (ProductImage $image): void {
                $image->deleteFile();
            });
            if ($product->video_path && ! str_starts_with($product->video_path, 'http')) {
                Storage::disk('public')->delete($product->video_path);
            }
        });
    }
}
